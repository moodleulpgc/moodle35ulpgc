<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
* A view of owned issues
* @package mod-tracker
* @category mod
* @author Valery Fremaux
* @date 02/12/2007
* @version Moodle 2.0
*
* Print Bug List
*/

defined('MOODLE_INTERNAL') || die();

include_once $CFG->libdir.'/tablelib.php';

// get search engine related information
// fields can come from a stored query,or from the current query in the user's client environement cookie
if (!isset($fields)) {
    $fields = tracker_extractsearchcookies();
}
if (!empty($fields)) {
    $searchqueries = tracker_constructsearchqueries($tracker->id, $fields, true);
}

$limit = 20;
$page = optional_param('page', 1, PARAM_INT);
$alltracks = optional_param('alltracks', false, PARAM_BOOL);

if ($page <= 0) {
    $page = 1;
}

$additionalfields = '';
if(($tracker->supportmode == 'usersupport') || ($tracker->supportmode == 'boardreview')) {
    $limit = 25;
    $additionalfields = 'i.trackerid, i.resolution, i.userlastseen, i.usermodified, i.resolvermodified, ';
    $additionalfields .= ' GREATEST(i.usermodified, i.resolvermodified) AS timemodified,  ';
}

if (isset($searchqueries)) {
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
    $singletrackerclause = (empty($alltracks)) ? " AND i.trackerid = {$tracker->id} " : '' ;

    if ($resolved) {
        $resolvedclause = " AND
           (status = ".RESOLVED." OR
           status = ".ABANDONNED.")
        ";
    } else {
        $resolvedclause = " AND
           status <> ".RESOLVED." AND
           status <> ".ABANDONNED."
        ";
    }

    $sql = "
        SELECT
            i.id,
            i.summary,
            i.datereported,
            i.reportedby,
            i.status,
            t.name,
            t.ticketprefix,
            i.resolutionpriority, $additionalfields
            COUNT(ic.issueid) AS watches
        FROM
            {tracker_issue} i
        JOIN
            {tracker} t
        ON
            t.id = i.trackerid
        LEFT JOIN
            {tracker_issuecc} ic
        ON
            ic.issueid = i.id
        WHERE
            i.assignedto = ?
            {$singletrackerclause}
            $resolvedclause
        GROUP BY
            i.id,
            i.summary,
            i.datereported,
            i.reportedby,
            i.status,
            i.resolutionpriority
    ";

    $sqlcount = "
        SELECT
            COUNT(*)
        FROM
            {tracker_issue} i
        WHERE
            i.assignedto = ?
            {$singletrackerclause}
            $resolvedclause
    ";
    $numrecords = $DB->count_records_sql($sqlcount, array($USER->id));
}

// display list of my issues
?>
<center>
<table border="1" width="100%">
<?php
if (isset($searchqueries)) {
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
<input type="checkbox" name="alltracks" value="1" <?php if ($alltracks) echo "checked=\"checked\"" ?> /> <?php echo tracker_getstring('alltracks', 'tracker') ?>
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="what" value="updatelist" />
<?php

// define table object
$prioritystr = tracker_getstring('priorityid', 'tracker');
$issuenumberstr = tracker_getstring('issuenumber', 'tracker');
$summarystr = tracker_getstring('summary', 'tracker');
$datereportedstr = tracker_getstring('datereported', 'tracker');
$reporterstr = tracker_getstring('reportedby', 'tracker');
$statusstr = tracker_getstring('status', 'tracker');
$watchesstr = tracker_getstring('watches', 'tracker');
$actionstr = '';

if (!empty($tracker->parent)) {
    $transferstr = tracker_getstring('transfer', 'tracker');
    $tablecolumns = array('resolutionpriority', 'id', 'summary', 'datereported', 'reportedby', 'status', 'watches', 'transfered', 'action');
    $tableheaders = array("<b>$prioritystr</b>", "<b>$issuenumberstr</b>", "<b>$summarystr</b>", "<b>$datereportedstr</b>", "<b>$reporterstr</b>", "<b>$statusstr</b>", "<b>$watchesstr</b>", "<b>$transferstr</b>", "<b>$actionstr</b>");
} else {
    $tablecolumns = array('resolutionpriority', 'id', 'summary', 'datereported', 'reportedby', 'status', 'watches',  'action');
    $tableheaders = array("<b>$prioritystr</b>", "<b>$issuenumberstr</b>", "<b>$summarystr</b>", "<b>$datereportedstr</b>", "<b>$reporterstr</b>", "<b>$statusstr</b>", "<b>$watchesstr</b>", "<b>$actionstr</b>");
}

$ulpgckey = false;
$canviewpriority = false;
$dateupdatedstr = tracker_getstring('dateupdated', 'tracker'); // ecastro ULPGC
if(($tracker->supportmode == 'usersupport') || ($tracker->supportmode == 'boardreview')) {
    $canviewpriority = has_capability('mod/tracker:viewpriority', $context);
    $ulpgckey = array_search('datereported', $tablecolumns);
    array_splice($tablecolumns, $ulpgckey, 0, 'usermodified');
    array_splice($tableheaders, $ulpgckey, 0, $dateupdatedstr);
    $key = ($canviewpriority && !$resolved) ? 1 : 0;
    $tableheaders[$key] = $issuenumberstr;
}

$table = new flexible_table('mod-tracker-issuelist');
$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);

$table->define_baseurl(new moodle_url('/mod/tracker/view.php', array('id' => $cm->id, 'view' => $view, 'screen' => $screen, 'alltracks' => $alltracks)));

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
$table->column_class('datereported', 'timelabel');
$table->column_class('reporter', 'list_reporter');
$table->column_class('watches', 'list_watches');
$table->column_class('status', 'list_status');
$table->column_class('action', 'list_action');
if (!empty($tracker->parent)) {
    $table->column_class('transfered', 'list_transfered');
}

if($ulpgckey !== false) {
    $table->column_class('usermodified', 'timelabel');
    $table->sortable(true, 'resolutionpriority', SORT_DESC); //sorted by priority by default
}

$table->setup();

// set list length limits
/*
if ($limit > $numrecords) {
    $offset = 0;
}
else{
    $offset = $limit * ($page - 1);
}
$sql = $sql . ' LIMIT ' . $limit . ' OFFSET ' . $offset;
*/

// get extra query parameters from flexible_table behaviour
$where = $table->get_sql_where();
$sort = $table->get_sql_sort();
$table->pagesize($limit, $numrecords);

if (!empty($sort)) {
    $sql .= " ORDER BY $sort";
    if($ulpgckey !== false) {
        $sql .= ", timemodified DESC ";
    }
} elseif($ulpgckey !== false) {
    $sql .= " ORDER BY i.resolutionpriority DESC, timemodified DESC ";
}

$issues = $DB->get_records_sql($sql, array($USER->id), $table->get_page_start(), $table->get_page_size());
$maxpriority = $DB->get_field_select('tracker_issue', 'MAX(resolutionpriority)', " trackerid = ? GROUP BY trackerid ", array($tracker->id));

$FULLSTATUSKEYS = tracker_get_statuskeys($tracker);
$STATUSKEYS = tracker_get_statuskeys($tracker, $cm);
$STATUSKEYS[0] = tracker_getstring('nochange', 'tracker');

if (!empty($issues)) {
    // product data for table
    $developersmenu = array();
    $fields = get_all_user_name_fields(true, 'u');
    foreach ($issues as $issue) {
        $issuenumber = "<a href=\"view.php?id={$cm->id}&amp;issueid={$issue->id}\">{$tracker->ticketprefix}{$issue->id}</a>";
        $summary = "<a href=\"view.php?id={$cm->id}&amp;view={$view}&amp;screen=viewanissue&amp;issueid={$issue->id}\">".format_string($issue->summary).'</a>';
        //$datereported = date('Y/m/d h:i', $issue->datereported);
        $datereported = userdate($issue->datereported);
        if (has_capability('mod/tracker:manage', $context)) { // managers can assign bugs
            $status = html_writer::select($STATUSKEYS, "status{$issue->id}", $issue->status, array(), array('onchange' => "document.forms['manageform'].schanged{$issue->id}.value = 1;")) . "<input type=\"hidden\" name=\"schanged{$issue->id}\" value=\"0\" />";
            $developers = get_users_by_capability($context, 'mod/tracker:develop', 'u.id, u.idnumber, '.$fields, 'lastname');
            foreach ($developers as $developer) {
                $developersmenu[$developer->id] = fullname($developer);
            }
        } elseif (has_capability('mod/tracker:resolve', $context)) { // resolvers can give a bug back to managers
            $status = $FULLSTATUSKEYS[0 + $issue->status].'<br/>'.html_writer::select($STATUSKEYS, "status{$issue->id}", 0, array(), array('onchange' => "document.forms['manageform'].schanged{$issue->id}.value = 1;")) . "<input type=\"hidden\" name=\"schanged{$issue->id}\" value=\"0\" />";
            $managers = get_users_by_capability($context, 'mod/tracker:manage', 'u.id, u.idnumber, '.$fields, 'lastname');
            foreach ($managers as $manager) {
                $managersmenu[$manager->id] = fullname($manager);
            }
            $managersmenu[$USER->id] = fullname($USER);
        } elseif (has_capability('mod/tracker:develop', $context) && ($tracker->supportmode != 'tutoring')) { // resolvers can give a bug back to managers
            $status = $FULLSTATUSKEYS[0 + $issue->status].'<br/>'.html_writer::select($STATUSKEYS, "status{$issue->id}", 0, array(), array('onchange' => "document.forms['manageform'].schanged{$issue->id}.value = 1;")) . "<input type=\"hidden\" name=\"schanged{$issue->id}\" value=\"0\" />";
        } else {
            $status = $FULLSTATUSKEYS[0 + $issue->status];
        }
        $status = '<div class="status_'.$STATUSCODES[$issue->status].'" style="width: 110%; height: 105%; text-align:center">'.$status.'</div>';
        $reporteruser = $DB->get_record('user', array('id' => $issue->reportedby));
        $reporter = fullname($reporteruser);
        $hassolution = $issue->status == RESOLVED && !empty($issue->resolution);
        $solution = ($hassolution) ? "<img src=\"{$CFG->wwwroot}/mod/tracker/pix/solution.gif\" height=\"15\" alt=\"".tracker_getstring('hassolution','tracker')."\" />" : '' ;
        $actions = '';
        if (has_capability('mod/tracker:manage', $context) || has_capability('mod/tracker:resolve', $context)) {
            $actions = "<a href=\"view.php?id={$cm->id}&amp;issueid={$issue->id}&screen=editanissue\" title=\"".tracker_getstring('update')."\" >".$OUTPUT->pix_icon('t/edit', '', 'moodle', array('border'=>0))."</a>";
        }
        if (has_capability('mod/tracker:manage', $context)) {
            $actions .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;issueid={$issue->id}&what=delete\" title=\"".tracker_getstring('delete')."\" >".$OUTPUT->pix_icon('t/delete', '', 'moodle', array('border'=>0))."</a>";
        }
        // Ergo Report I3 2012 => self list displays owned tickets. Already registered
        // $actions .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;view=profile&amp;screen=mywatches&amp;issueid={$issue->id}&what=register\" title=\"".tracker_getstring('register', 'tracker')."\" >".$OUTPUT->pix_icon('register', 'tracker')."\" border=\"0\" /></a>";
        if (($issue->resolutionpriority < $maxpriority) && has_capability('mod/tracker:viewpriority', $context) && !has_capability('mod/tracker:managepriority', $context)) {
            $actions .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;issueid={$issue->id}&amp;what=askraise\" title=\"".tracker_getstring('askraise', 'tracker')."\" >".$OUTPUT->pix_icon('askraise', '', 'mod_tracker', array('border'=>0))."</a>";
        }
        if (!empty($tracker->parent)) {
            $transfer = ($issue->status == TRANSFERED) ? tracker_print_transfer_link($tracker, $issue) : '' ;
            $dataset = array($issue->resolutionpriority, $issuenumber, $summary.' '.$solution, $datereported, $reporter, $status, 0 + $issue->watches, $transfer, $actions);
        } else {
            $dataset = array($issue->resolutionpriority, $issuenumber, $summary.' '.$solution, $datereported, $reporter, $status, 0 + $issue->watches, $actions);
        }
        if($ulpgckey !== false) {
            $hascomment = tracker_userlastcomment($issue, $tracker, $context);
            $lastcomment = ($hascomment) ? "&nbsp;".$OUTPUT->pix_icon($hascomment, tracker_getstring('lastcomment','tracker'), 'mod_tracker', array('height'=>16)) : '' ;
            $haslastseen = tracker_userlastseen($issue, $tracker, $context);
            $lastseen = ($haslastseen) ? "&nbsp;".$OUTPUT->pix_icon($haslastseen, tracker_getstring('userlastseen','tracker'), 'mod_tracker', array('height'=>16)) : '' ;
            $lastcomment .= $lastseen;
            if ($canviewpriority && !$resolved){
                $ticketpriority = ($issue->status < RESOLVED || $issue->status == TESTING) ? round(($issue->resolutionpriority / DAYSECS), 2)*100 : '' ;
                $dataset[0] = $ticketpriority;
                $dataset[1] = $issuenumber.$lastcomment.$solution;
            } else {
                $dataset[0] = $issuenumber.$lastcomment.$solution;
            }
            $dateupdated = userdate(max($issue->usermodified, $issue->resolvermodified));
            array_splice($dataset, $ulpgckey, 0, $dateupdated);
        }
        $table->add_data($dataset);
    }
    $table->print_html();

    if (tracker_can_workon($tracker, $context)) {
        echo '<center>';
        echo '<p><input type="submit" name="go_btn" value="'.tracker_getstring('savechanges').'" /></p>';
        echo '</center>';
    }
} else {
    echo '<br/>';
    echo '<br/>';
    echo '<br/>';
    echo $OUTPUT->notification(tracker_getstring('noassignedtickets', 'tracker'), 'box generalbox', 'notice');
}

echo '</form>';
