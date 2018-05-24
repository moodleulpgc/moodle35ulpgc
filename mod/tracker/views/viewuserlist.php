<?PHP

/**
* A view of owned issues
* @package mod-tracker
* @category mod
* @author Clifford Thamm, Valery Fremaux > 1.8
* @date 02/12/2007
*
* Print Bug List
*/

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from view.php in mod/tracker
}

include_once $CFG->libdir.'/tablelib.php';

$viewuser = optional_param('user', 0, PARAM_INT);

$STATUSKEYS = array(POSTED => tracker_getstring('posted', 'tracker'),
                    OPEN => tracker_getstring('open', 'tracker'),
                    RESOLVING => tracker_getstring('resolving', 'tracker'),
                    WAITING => tracker_getstring('waiting', 'tracker'),
                    TESTING => tracker_getstring('testing', 'tracker'),
                    PUBLISHED => tracker_getstring('published', 'tracker'),
                    RESOLVED => tracker_getstring('resolved', 'tracker'),
                    ABANDONNED => tracker_getstring('abandonned', 'tracker'),
                    TRANSFERED => tracker_getstring('transfered', 'tracker'));

/// get search engine related information
// fields can come from a stored query,or from the current query in the user's client environement cookie
if (!isset($fields)){
    $fields = tracker_extractsearchcookies();
}
if (!empty($fields)){
    $searchqueries = tracker_constructsearchqueries($tracker->id, $fields, true);
}

$limit = 20;
$page = optional_param('page', 1, PARAM_INT);

if ($page <= 0){
    $page = 1;
}

$resolved = false;
if (isset($searchqueries)){
    /* SEARCH DEBUG
    $strsql = str_replace("\n", "<br/>", $searchqueries->count);
    $strsql = str_replace("\t", "&nbsp;&nbsp;&nbsp;", $strsql);
    echo "<div align=\"left\"> <b>count using :</b> ".$strsql." <br/>";
    $strsql = str_replace("\n", "<br/>", $searchqueries->search);
    $strsql = str_replace("\t", "&nbsp;&nbsp;&nbsp;", $strsql);
    echo " <b>search using :</b> ".str_replace("\n", "<br/>", $strsql)." <br/></div>";
    */
    $sql = $searchqueries->search;
    $numrecords = $DB->count_records_sql($searchqueries->count);
} else {
    // ecastro ULPGC remove resolvedclause limit in user search
    $resolvedclause = '';
    $sql = "
        SELECT
            i.id,
            i.trackerid,
            i.summary,
            i.datereported,
            i.assignedto,
            i.status,
            i.resolution,
            i.resolvermodified,
            i. usermodified, i.userlastseen,
            GREATEST(i.usermodified, i.resolvermodified) AS timemodified,
            COUNT(ic.issueid) as watches,
            i.resolutionpriority
        FROM
            {tracker_issue} i
        LEFT JOIN
            {tracker_issuecc} ic
        ON
            ic.issueid = i.id
        WHERE
            i.reportedby = ? AND i.trackerid = ?
            $resolvedclause
        GROUP BY
            i.id,
            i.summary,
            i.datereported,
            i.assignedto,
            i.status,
            i.resolutionpriority
    ";

    $sqlcount = "
        SELECT
            COUNT(DISTINCT i.id)
        FROM
            {tracker_issue} i
        LEFT JOIN
            {tracker_issuecc} ic
        ON
            ic.issueid = i.id
        WHERE
            i.reportedby = ? AND i.trackerid = ?
            $resolvedclause
        ";
    $numrecords = $DB->count_records_sql($sqlcount, array($viewuser, $tracker->id));  // set from COUNT(*) to i.id by ecastro
}

    $canviewpriority = has_capability('mod/tracker:viewpriority', $context);


/// display user form

$options = array('context' => $context, 'trakerid' => $tracker->id);
$userselector = new tracker_user_selector('user', $options);
$userselector->set_multiselect(false);
$userselector->set_rows(5);
$selecteduser = $userselector->get_selected_user();

// Show UI for choosing a user to report on.
echo $OUTPUT->box_start('generalbox boxwidthnormal boxaligncenter', 'chooseuser');
echo '<form method="get" action="' . $CFG->wwwroot . '/mod/tracker/view.php" >';

// Hidden fields.
echo '<input type="hidden" name="id" value="' . $cm->id . '" />';
echo '<input type="hidden" name="view" value="view" />';
echo '<input type="hidden" name="screen" value="user" />';


// User selector.
echo $OUTPUT->heading('<label for="user">' . tracker_getstring('selectuser','tracker') . '</label>', 3);
$userselector->display();

// Submit button and the end of the form.
echo '<p id="chooseusersubmit"><input type="submit" value="' . tracker_getstring('showuserissues', 'tracker') . '" /></p>';
echo '</form>';
echo $OUTPUT->box_end();
/// display list of my issues
?>
<center>
<table border="1" width="100%">
<?php
if (isset($searchqueries)){
?>
    <tr>
        <td colspan="2">
            <?php print_string('searchresults', 'tracker') ?>: <?php echo $numrecords ?> <br/>
        </td>
        <td colspan="2" align="right">
                <a href="view.php?id=<?php p($cm->id) ?>&amp;what=clearsearch"><?php print_string('clearsearch', 'tracker') ?></a>
        </td>
    </tr>
<?php
}
?>
</table>
</center>
<form name="manageform" action="view.php" method="post">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="what" value="updatelist" />
<?php

/// define table object
$prioritystr = tracker_getstring('priority', 'tracker');
$issuenumberstr = tracker_getstring('issuenumber', 'tracker');
$summarystr = tracker_getstring('summary', 'tracker');
$dateupdatedstr = tracker_getstring('dateupdated', 'tracker');
$datereportedstr = tracker_getstring('datereported', 'tracker');
$assignedtostr = tracker_getstring('assignedto', 'tracker');
$statusstr = tracker_getstring('status', 'tracker');
$watchesstr = tracker_getstring('watches', 'tracker');
$actionstr = '';

if(!empty($tracker->parent)){
    $transferstr = tracker_getstring('transfer', 'tracker');
    if ($canviewpriority && !$resolved){
        $tablecolumns = array('resolutionpriority', 'id', 'summary', 'usermodified', 'datereported', 'assignedto', 'status', 'watches', 'transfered', 'action');
        $tableheaders = array("<b>$prioritystr</b>", "<b>$issuenumberstr</b>", "<b>$summarystr</b>", "<b>$dateupdatedstr</b>", "<b>$datereportedstr</b>", "<b>$assignedtostr</b>", "<b>$statusstr</b>", "<b>$watchesstr</b>", "<b>$transferstr</b>", "<b>$actionstr</b>");
    } else {
        $tablecolumns = array('id', 'summary', 'usermodified', 'datereported', 'assignedto', 'status', 'watches', 'transfered', 'action');
        $tableheaders = array("<b>$issuenumberstr</b>", "<b>$summarystr</b>", "<b>$datereportedstr</b>", "<b>$dateupdatedstr</b>", "<b>$assignedtostr</b>", "<b>$statusstr</b>", "<b>$watchesstr</b>", "<b>$transferstr</b>", "<b>$actionstr</b>");
    }
} else {
    if ($canviewpriority && !$resolved){
        $tablecolumns = array('resolutionpriority', 'id', 'summary', 'usermodified', 'datereported', 'assignedto', 'status', 'watches',  'action');
        $tableheaders = array("<b>$prioritystr</b>", '', "<b>$summarystr</b>", "<b>$dateupdatedstr</b>", "<b>$datereportedstr</b>", "<b>$assignedtostr</b>", "<b>$statusstr</b>", "<b>$watchesstr</b>", "<b>$actionstr</b>");
    } else {
        $tablecolumns = array('id', 'summary', 'usermodified', 'datereported', 'assignedto', 'status', 'watches',  'action');
        $tableheaders = array("<b>$issuenumberstr</b>", "<b>$summarystr</b>", "<b>$dateupdatedstr</b>", "<b>$datereportedstr</b>", "<b>$assignedtostr</b>", "<b>$statusstr</b>", "<b>$watchesstr</b>", "<b>$actionstr</b>");
    }
}

$table = new flexible_table('mod-tracker-issuelist');
$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);

$table->define_baseurl($CFG->wwwroot.'/mod/tracker/view.php?id='.$cm->id.'&view='.$view.'&screen='.$screen);

$table->sortable(true, 'datereported', SORT_DESC); //sorted by datereported by default
$table->collapsible(true);
$table->initialbars(true);

// allow column hiding
// $table->column_suppress('reportedby');
// $table->column_suppress('watches');

$table->set_attribute('cellspacing', '0');
$table->set_attribute('id', 'issues');
$table->set_attribute('class', 'issuelist');
$table->set_attribute('width', '100%');

$table->column_class('resolutionpriority', 'list_priority');
$table->column_class('id', 'list_issuenumber');
$table->column_class('summary', 'list_summary');
$table->column_class('usermodified', 'timelabel');
$table->column_class('datereported', 'timelabel');
$table->column_class('assignedto', 'list_assignedto');
$table->column_class('watches', 'list_watches');
$table->column_class('status', 'list_status');
$table->column_class('action', 'list_action');
if (!empty($tracker->parent)){
    $table->column_class('transfered', 'list_transfered');
}
$table->column_style_all('vertical-align', 'middle');

$table->setup();


/// set list length limits
/*
if ($limit > $numrecords){
    $offset = 0;
}
else{
    $offset = $limit * ($page - 1);
}
$sql = $sql . ' LIMIT ' . $limit . ' OFFSET ' . $offset;
*/

/// get extra query parameters from flexible_table behaviour
$where = $table->get_sql_where();
$sort = $table->get_sql_sort();
$table->pagesize($limit, $numrecords);

if (!empty($sort)){
    $sql .= " ORDER BY $sort, timemodified DESC ";
} else {
    //$sql .= " ORDER BY i.usermodified DESC, i.resolutionpriority ASC";
    $sql .= " ORDER BY i.resolutionpriority DESC, timemodified DESC ";
}

$issues = $DB->get_records_sql($sql, array($viewuser, $tracker->id), $table->get_page_start(), $table->get_page_size());
$maxpriority = $DB->get_field_select('tracker_issue', 'MAX(resolutionpriority)', ' trackerid = ? GROUP BY trackerid ', array($tracker->id));

$canmanage = tracker_can_edit($tracker, $context);
$canresolve = tracker_can_workon($tracker, $context);

if (!empty($issues)){
    /// product data for table
    $developersmenu = array();
    $fields = get_all_user_name_fields(true, 'u');
    foreach ($issues as $issue){
        $issuenumber = "<a href=\"view.php?id={$cm->id}&amp;issueid={$issue->id}\">{$tracker->ticketprefix}{$issue->id}</a>&nbsp;";
        $summary = "<a href=\"view.php?id={$cm->id}&amp;view=view&amp;screen=viewanissue&amp;issueid={$issue->id}\">".format_string($issue->summary).'</a>';
        $datereported = userdate($issue->datereported);  //date('Y/m/d h:i', $issue->datereported);
        $dateupdated = userdate($issue->usermodified);
        //$user = $DB->get_record('user', array('id' => $issue->assignedto));
        $assignedto = fullname($selecteduser);
        if ($canmanage){ // managers can assign bugs
        	$status = html_writer::select($STATUSKEYS, "status{$issue->id}", $issue->status, array('' => 'choose'), array('onchange' => "document.forms['manageform'].schanged{$issue->id}.value = 1;"));
            $developers = get_users_by_capability($context, 'mod/tracker:develop', 'u.id, u.idnumber, '.$fields, 'lastname');
            foreach($developers as $developer){
                $developersmenu[$developer->id] = fullname($developer);
            }
            $assignedto = html_writer::select($developersmenu, "assignedto{$issue->id}", $issue->assignedto, array('' => tracker_getstring('unassigned', 'tracker')), array('onchange' => "document.forms['manageform'].changed{$issue->id}.value = 1;"));
        } elseif (has_capability('mod/tracker:resolve', $context)){ // resolvers can give a bug back to managers
        	$status = html_writer::select($STATUSKEYS, "status{$issue->id}", $issue->status, array('' => 'choose'), array('onchange' => "document.forms['manageform'].schanged{$issue->id}.value = 1;"));
            $managers = get_users_by_capability($context, 'mod/tracker:manage', 'u.id, u.idnumber, '.$fields, 'lastname');
            foreach($managers as $manager){
                $managersmenu[$manager->id] = fullname($manager);
            }
            $managersmenu[$USER->id] = fullname($USER);
            $assignedto = html_writer::select($developersmenu, "assignedto{$issue->id}", $issue->assignedto, array('' => tracker_getstring('unassigned', 'tracker')), array('onchange' => "document.forms['manageform'].changed{$issue->id}.value = 1;"));
        } else {
            $status = $STATUSKEYS[0 + $issue->status];
            $assignedto = fullname($selecteduser);
        }
        $status = '<div class="status_'.$STATUSCODES[$issue->status].'" style="width: 110%; height: 105%; text-align:center">'.$status.'</div>';
        $hassolution = !empty($issue->resolution);
        $solution = ($hassolution) ? "&nbsp;<img src=\"{$CFG->wwwroot}/mod/tracker/pix/solution.gif\" height=\"15\" alt=\"".tracker_getstring('hassolution','tracker')."\" />" : '' ;
        $hascomment = tracker_userlastcomment($issue, $context);
        $lastcomment = ($hascomment) ? "&nbsp;<img src=\"".$OUTPUT->pix_url($hascomment, 'mod_tracker')."\" height=\"15\" alt=\"".tracker_getstring('lastcomment','tracker')."\" />" : '' ;
        $haslastseen = tracker_userlastseen($issue, $context);
        $lastseen = ($haslastseen) ? "&nbsp;<img src=\"".$OUTPUT->pix_url($haslastseen, 'mod_tracker')."\" height=\"15\" border=\"0\" alt=\"".tracker_getstring('userlastseen','tracker')."\" />" : '' ;
/*
        $lastseen = '';
        if($issue->userlastseen >= $issue->usermodified) {
            $lastseen = "&nbsp;<img src=\"{$CFG->wwwroot}/mod/tracker/pix/seen.gif\" height=\"15\" alt=\"".tracker_getstring('userlastseen','tracker')."\" />";
        }*/
        $lastcomment .= $lastseen;
        $actions = '';
        if ($canmanage || $canresolve){
            $actions = "<a href=\"view.php?id={$cm->id}&amp;issueid={$issue->id}&screen=editanissue\" title=\"".tracker_getstring('update')."\" ><img src=\"".$OUTPUT->pix_url('t/edit', 'core')."\" border=\"0\" /></a>";
        }
        if ($canmanage){
            $actions .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;issueid={$issue->id}&what=delete\" title=\"".tracker_getstring('delete')."\" ><img src=\"".$OUTPUT->pix_url('t/delete', 'core')."\" border=\"0\" /></a>";
        }
        $actions .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;view=profile&amp;screen=mywatches&amp;issueid={$issue->id}&what=register\" title=\"".tracker_getstring('register', 'tracker')."\" ><img src=\"".$OUTPUT->pix_url('register', 'mod_tracker')."\" border=\"0\" /></a>";
        if ($issue->resolutionpriority < $maxpriority && has_capability('mod/tracker:viewpriority', $context) && !has_capability('mod/tracker:managepriority', $context)){
            $actions .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;issueid={$issue->id}&amp;what=askraise\" title=\"".tracker_getstring('askraise', 'tracker')."\" ><img src=\"".$OUTPUT->pix_url('askraise', 'mod_tracker')."\" border=\"0\" /></a>";
        }
        if (!empty($tracker->parent)){
            $transfer = ($issue->status == TRANSFERED) ? tracker_print_transfer_link($tracker, $issue) : '' ;
            if ($canviewpriority && !$resolved){
                $ticketpriority = ($issue->status < RESOLVED || $issue->status == TESTING) ? round(($issue->resolutionpriority / DAYSECS), 2)*100 : '' ;
                $dataset = array($ticketpriority, $issuenumber.$lastcomment.$solution, $summary, $datereported, $assignedto, $status, 0 + $issue->watches, $transfer, $actions);
            } else {
                $dataset = array($issuenumber.$lastcomment.$solution, $summary, $dateupdated, $datereported, $assignedto, $status, 0 + $issue->watches, $transfer, $actions);
            }
        } else {
            if (has_capability('mod/tracker:viewpriority', $context) && !$resolved){
                $ticketpriority = ($issue->status < RESOLVED || $issue->status == TESTING) ? round(($issue->resolutionpriority / DAYSECS), 2)*100 : '' ;
                $dataset = array($ticketpriority, $issuenumber.$lastcomment.$solution, $summary, $dateupdated, $datereported, $assignedto, $status, 0 + $issue->watches, $actions);
            } else {
                $dataset = array($issuenumber.$lastcomment.$solution, $summary, $dateupdated, $datereported, $assignedto, $status, 0 + $issue->watches, $actions);
            }
        }
        $table->add_data($dataset);
    }
    $table->print_html();
} else {
    notice(tracker_getstring('notickets', 'tracker'), "view.php?id=$cm->id");
}

if (!empty($issues) && ($canmanage || $canresolve)){
?>
<center>
    <p><input type="submit" name="go_btn" value="<?php print_string('savechanges') ?>" /></p>
</center>
</form>
<?php

$nohtmleditorneeded = true;
}
echo '<br />';
echo '<br />';
