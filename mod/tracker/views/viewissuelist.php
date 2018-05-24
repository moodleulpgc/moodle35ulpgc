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
* @package mod-tracker
* @category mod
* @author Clifford Thamm, Valery Fremaux > 1.8
* @date 02/12/2007
*
* Print Bug List
*/

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from view.php in mod/tracker
}

require_once($CFG->libdir.'/tablelib.php');

$FULLSTATUSKEYS = tracker_get_statuskeys($tracker);
$STATUSKEYS = tracker_get_statuskeys($tracker, $cm);
$STATUSKEYS[0] = tracker_getstring('nochange', 'tracker');

// get search engine related information
// fields can come from a stored query,or from the current query in the user's client environement cookie
if (!isset($fields)) {
    $fields = tracker_extractsearchcookies();
}

if (!empty($fields)) {
    $searchqueries = tracker_constructsearchqueries($tracker->id, $fields);
}

$limit = 20;
$page = optional_param('page', 1, PARAM_INT);

if ($page <= 0) {
    $page = 1;
}

$additionalfields = '';
if(($tracker->supportmode == 'usersupport') || 
        ($tracker->supportmode == 'boardreview') || 
            ($tracker->supportmode == 'tutoring')) {
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
    // check we display only resolved tickets or working
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

    // ecastro ULPGC {user} us ON i.assignedto = us.id && lastmane to allow sorting by user names
    $sql = "
        SELECT
            i.id,
            i.summary,
            i.datereported,
            i.reportedby,
            i.assignedto,
            i.status,
            i.resolutionpriority, $additionalfields
            u.firstname AS ufirstname, us.firstname AS sfirstname,
            u.lastname AS ulastname, us.lastname AS slastname,
            COUNT(ic.issueid) watches
        FROM
            {tracker_issue} i
        LEFT JOIN
            {tracker_issuecc} ic
        ON
            ic.issueid = i.id
        LEFT JOIN
            {user} u ON i.reportedby = u.id
            
        LEFT JOIN
            {user} us ON i.assignedto = us.id

        WHERE
            i.reportedby = u.id AND
            i.trackerid = {$tracker->id}
            $resolvedclause
        GROUP BY
            i.id,
            i.summary,
            i.datereported,
            i.reportedby,
            i.assignedto,
            i.status,
            i.resolutionpriority,
            u.firstname,
            u.lastname
    ";

    $sqlcount = "
        SELECT
            COUNT(*)
        FROM
            {tracker_issue} i,
            {user} u
        WHERE
            i.reportedby = u.id AND
            i.trackerid = {$tracker->id}
            $resolvedclause
    ";
    $numrecords = $DB->count_records_sql($sqlcount);
}

// Display list of issues.
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
                <a href="view.php?id=<?php p($cm->id) ?>&amp;view=view&amp;screen=browse&amp;what=clearsearch"><?php print_string('clearsearch', 'tracker') ?></a>
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
<input type="hidden" name="view" value="view" />
<input type="hidden" name="screen" value="browse" />
<?php

// define table object
$prioritystr = tracker_getstring('priority', 'tracker');
$issuenumberstr = tracker_getstring('issuenumber', 'tracker');
$summarystr = tracker_getstring('summary', 'tracker');
$datereportedstr = tracker_getstring('datereported', 'tracker');
$reportedbystr = tracker_getstring('reportedby', 'tracker');
$assignedtostr = tracker_getstring('assignedto', 'tracker');
$statusstr = tracker_getstring('status', 'tracker');
$watchesstr = tracker_getstring('watches', 'tracker');
$actionstr = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
if ($resolved) {
    if (!empty($tracker->parent)) {
        $transferstr = tracker_getstring('transfer', 'tracker');
        $tablecolumns = array('id', 'summary', 'datereported', 'reportedby', 'assignedto', 'status', 'watches', 'transfered', 'action');
        $tableheaders = array("<b>$issuenumberstr</b>", "<b>$summarystr</b>", "<b>$datereportedstr</b>", "<b>$reportedbystr</b>", "<b>$assignedtostr</b>", "<b>$statusstr</b>", "<b>$watchesstr</b>", "<b>$transferstr</b>", "<b>$actionstr</b>");
    } else {
        $tablecolumns = array('id', 'summary', 'datereported', 'reportedby', 'assignedto', 'status', 'watches', 'action');
        $tableheaders = array("<b>$issuenumberstr</b>", "<b>$summarystr</b>", "<b>$datereportedstr</b>", "<b>$reportedbystr</b>", "<b>$assignedtostr</b>", "<b>$statusstr</b>", "<b>$watchesstr</b>", "<b>$actionstr</b>");
    }
} else {
    if (!empty($tracker->parent)) {
        $transferstr = tracker_getstring('transfer', 'tracker');
        $tablecolumns = array('resolutionpriority', 'id', 'summary', 'datereported', 'reportedby', 'assignedto', 'status', 'watches', 'transfered', 'action');
        $tableheaders = array("<b>$prioritystr</b>", "<b>$issuenumberstr</b>", "<b>$summarystr</b>", "<b>$datereportedstr</b>", "<b>$reportedbystr</b>", "<b>$assignedtostr</b>", "<b>$statusstr</b>", "<b>$watchesstr</b>", "<b>$transferstr</b>", "<b>$actionstr</b>");
    } else {
        $tablecolumns = array('resolutionpriority', 'id', 'summary', 'datereported', 'reportedby', 'assignedto', 'status', 'watches', 'action');
        $tableheaders = array("<b>$prioritystr</b>", "<b>$issuenumberstr</b>", "<b>$summarystr</b>", "<b>$datereportedstr</b>", "<b>$reportedbystr</b>", "<b>$assignedtostr</b>", "<b>$statusstr</b>", "<b>$watchesstr</b>", "<b>$actionstr</b>");
    }
}

$ulpgckey = false;
$canviewpriority = false;
$dateupdatedstr = tracker_getstring('dateupdated', 'tracker');
if(($tracker->supportmode == 'usersupport') || 
        ($tracker->supportmode == 'boardreview') || 
            ($tracker->supportmode == 'tutoring')) {
    $canviewpriority = has_capability('mod/tracker:viewpriority', $context);
    $ulpgckey = array_search('datereported', $tablecolumns);
    array_splice($tablecolumns, $ulpgckey, 0, 'usermodified');
    array_splice($tableheaders, $ulpgckey, 0, $dateupdatedstr);
}

$table = new flexible_table('mod-tracker-issuelist');
$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);

$table->define_baseurl(new moodle_url('/mod/tracker/view.php', array('id' => $cm->id, 'view' => $view, 'screen' => $screen)));

$table->sortable(true, 'resolutionpriority', SORT_ASC); //sorted by priority by default
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
$table->column_class('reportedby', 'list_reportedby');
$table->column_class('assignedto', 'list_assignedto');
$table->column_class('watches', 'list_watches');
$table->column_class('status', 'list_status');
$table->column_class('action', 'list_action');

if (!empty($tracker->parent)) {
    $table->column_class('transfered', 'list_transfered');
}

if($ulpgckey !== false) {
    $table->column_class('usermodified', 'timelabel');
    //$table->sortable(true, 'resolutionpriority', SORT_DESC); //sorted by priority by default
}

$table->setup();

// Get extra query parameters from flexible_table behaviour.
$where = $table->get_sql_where();
$sort = $table->get_sql_sort();
$table->pagesize($limit, $numrecords);

if (!empty($sort)) {
    // ecastro ULPGC to allow sorting by user name
    if(strpos($sort, 'reportedby') !== false) {
        $sort = str_replace('reportedby', "CONCAT_WS(',', u.lastname, u.firstname) ", $sort);
    }
    if(strpos($sort, 'assignedto') !== false) {
        $sort = str_replace('assignedto', "CONCAT_WS(',', us.lastname, us.firstname) ", $sort);
    }
    
    $sql .= " ORDER BY $sort";
    if($ulpgckey !== false) {
        $sql .= ", timemodified DESC ";
    }
} elseif($ulpgckey !== false) {
    $sql .= " ORDER BY i.resolutionpriority DESC, timemodified DESC ";
} else {
    $sql .= " ORDER BY resolutionpriority ASC, timemodified DESC";
}

// Set list length limits.
/*
if ($limit > $numrecords) {
    $offset = 0;
} else {
    $offset = $limit * ($page - 1);
}
$sql = $sql . ' LIMIT ' . $limit . ',' . $offset;
*/
//

$issues = $DB->get_records_sql($sql, null, $table->get_page_start(), $table->get_page_size());

$maxpriority = $DB->get_field_select('tracker_issue', 'MAX(resolutionpriority)', " trackerid = $tracker->id GROUP BY trackerid ");

if (!empty($issues)) {
    // Product data for table.
    foreach ($issues as $issue) {
        $issuenumber = "<a href=\"view.php?id={$cm->id}&amp;view={$view}&amp;issueid={$issue->id}\">{$tracker->ticketprefix}{$issue->id}</a>";
        $summary = "<a href=\"view.php?id={$cm->id}&amp;view={$view}&amp;screen=viewanissue&amp;issueid={$issue->id}\">".format_string($issue->summary).'</a>';
        //$datereported = date('Y/m/d h:i', $issue->datereported);
        $datereported = userdate($issue->datereported);
        $user = $DB->get_record('user', array('id' => $issue->reportedby));
        $reportedby = fullname($user);
        $assignedto = '';
        $user = $DB->get_record('user', array('id' => $issue->assignedto));
        
        
        $status = $FULLSTATUSKEYS[0 + $issue->status].'<br/>'.html_writer::select($STATUSKEYS, "status{$issue->id}", 0, array('' => 'choose'), array('onchange' => "document.forms['manageform'].schanged{$issue->id}.value = 1;")). "<input type=\"hidden\" name=\"schanged{$issue->id}\" value=\"0\" />";
        $developersmenu = array();
        $managersmenu = array();
        $assignedto = '';
        if (has_capability('mod/tracker:manage', $context)) { // managers can assign bugs
//            $status = $FULLSTATUSKEYS[0 + $issue->status].'<br/>'.html_writer::select($STATUSKEYS, "status{$issue->id}", 0, array('' => 'choose'), array('onchange' => "document.forms['manageform'].schanged{$issue->id}.value = 1;")). "<input type=\"hidden\" name=\"schanged{$issue->id}\" value=\"0\" />";
            $developers = tracker_getdevelopers($context);
            if($issue->assignedto && !isset($developers[$issue->assignedto])) { // ecastro ULPGC
                $developers[$issue->assignedto] = $user;
            }
            if (!empty($developers)) {
                foreach ($developers as $developer) {
                    $developersmenu[$developer->id] = fullname($developer);
                }
                //$assignedto = html_writer::select($developersmenu, "assignedto{$issue->id}", $issue->assignedto, array('' => tracker_getstring('unassigned', 'tracker')), array('onchange' => "document.forms['manageform'].changed{$issue->id}.value = 1;")) . "<input type=\"hidden\" name=\"changed{$issue->id}\" value=\"0\" />";
            }
        } elseif (has_capability('mod/tracker:resolve', $context)) {
            // Resolvers can give a bug back to managers.
  //          $status = $FULLSTATUSKEYS[0 + $issue->status].'<br/>'.html_writer::select($STATUSKEYS, "status{$issue->id}", 0, array('' => 'choose'), array('onchange' => "document.forms['manageform'].schanged{$issue->id}.value = 1;")) . "<input type=\"hidden\" name=\"schanged{$issue->id}\" value=\"0\" />";
            $managers = tracker_getadministrators($context);
            if($issue->assignedto && !isset($managers[$issue->assignedto])) { // ecastro ULPGC
                $managers[$issue->assignedto] = $user;
            }
            
            if (!empty($managers)) {
                foreach ($managers as $manager) {
                    $managersmenu[$manager->id] = fullname($manager);
                }
                $managersmenu[$USER->id] = fullname($USER);
//                $assignedto = html_writer::select($managersmenu, "assignedto{$issue->id}", $issue->assignedto, $unsassigned, array('onchange' => "document.forms['manageform'].changed{$issue->id}.value = 1;")) . "<input type=\"hidden\" name=\"changed{$issue->id}\" value=\"0\" />";
            }
        } else {
            $status = $FULLSTATUSKEYS[0 + $issue->status];
            $assignedto = fullname($user);
        }

        if(!$assignedto && ($developersmenu || $managersmenu)) { // ecastro ULPGC
            $managersmenu = $developersmenu + $managersmenu;
            $unsassigned = ''; // ecastro ULPGC
            if(!$issue->assignedto) {
                $unsassigned = array('' => tracker_getstring('unassigned', 'tracker'));
            }
            $assignedto = html_writer::select($managersmenu, "assignedto{$issue->id}", $issue->assignedto, $unsassigned, array('onchange' => "document.forms['manageform'].changed{$issue->id}.value = 1;")) . "<input type=\"hidden\" name=\"changed{$issue->id}\" value=\"0\" />";
        }
        
        $status = '<div class="status_'.$STATUSCODES[$issue->status].'" style="width: 110%; height: 105%; text-align:center">'.$status.'</div>';
        $hassolution = $issue->status == RESOLVED && !empty($issue->resolution);
        $solution = ($hassolution) ? "<img src=\"".$OUTPUT->pix_url('solution', 'tracker').'" height="15" alt="'.tracker_getstring('hassolution','tracker')."\" />" : '' ;
        $actions = '';

        if (has_capability('mod/tracker:manage', $context) || has_capability('mod/tracker:resolve', $context)) {
            $actions = "<a href=\"view.php?id={$cm->id}&amp;view=view&amp;issueid={$issue->id}&screen=editanissue\" title=\"".tracker_getstring('update')."\" ><img src=\"".$OUTPUT->pix_url('t/edit', 'core')."\" border=\"0\" /></a>";
        }

        if (has_capability('mod/tracker:manage', $context)) {
            $actions .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;issueid={$issue->id}&what=delete\" title=\"".tracker_getstring('delete')."\" ><img src=\"".$OUTPUT->pix_url('t/delete', 'core')."\" border=\"0\" /></a>";
        }

        if (!$DB->get_record('tracker_issuecc', array('userid' => $USER->id, 'issueid' => $issue->id))) {
            $actions .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;view=profile&amp;screen={$screen}&amp;issueid={$issue->id}&what=register\" title=\"".tracker_getstring('register', 'tracker')."\" ><img src=\"".$OUTPUT->pix_url('register', 'mod_tracker')."\" border=\"0\" /></a>";
        }

        if (preg_match('/^resolutionpriority/', $sort) && has_capability('mod/tracker:managepriority', $context)) {

            if ($issue->resolutionpriority < $maxpriority) {
                $actions .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;issueid={$issue->id}&what=raisetotop\" title=\"".tracker_getstring('raisetotop', 'tracker')."\" ><img src=\"".$OUTPUT->pix_url('totop', 'mod_tracker')."\" border=\"0\" /></a>";
                $actions .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;issueid={$issue->id}&what=raisepriority\" title=\"".tracker_getstring('raisepriority', 'tracker')."\" ><img src=\"".$OUTPUT->pix_url('up', 'mod_tracker')."\" border=\"0\" /></a>";
            } else {
                $actions .= "&nbsp;<img src=\"".$OUTPUT->pix_url('up_shadow', 'mod_tracker')."\" border=\"0\" />";
                $actions .= "&nbsp;<img src=\"".$OUTPUT->pix_url('totop_shadow', 'mod_tracker')."\" border=\"0\" />";
            }

            if ($issue->resolutionpriority > 1) {
                $actions .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;issueid={$issue->id}&what=lowerpriority\" title=\"".tracker_getstring('lowerpriority', 'tracker')."\" ><img src=\"".$OUTPUT->pix_url('down', 'mod_tracker')."\" border=\"0\" /></a>";
                $actions .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;issueid={$issue->id}&what=lowertobottom\" title=\"".tracker_getstring('lowertobottom', 'tracker')."\" ><img src=\"".$OUTPUT->pix_url('tobottom', 'mod_tracker')."\" border=\"0\" /></a>";
            } else {
                $actions .= "&nbsp;<img src=\"".$OUTPUT->pix_url('down_shadow', 'mod_tracker')."\" border=\"0\" />";
                $actions .= "&nbsp;<img src=\"".$OUTPUT->pix_url('tobottom_shadow', 'mod_tracker')."\" border=\"0\" />";
            }
        }

        if ($resolved) {
            if (!empty($tracker->parent)) {
                $transfer = ($issue->status == TRANSFERED) ? tracker_print_transfer_link($tracker, $issue) : '' ;
                $dataset = array($issuenumber, $summary.' '.$solution, $datereported, $reportedby, $assignedto, $status, 0 + $issue->watches, $transfer, $actions);
            } else {
                $dataset = array($issuenumber, $summary.' '.$solution, $datereported, $reportedby, $assignedto, $status, 0 + $issue->watches, $actions);
            }
        } else {
            if (!empty($tracker->parent)) {
                $transfer = ($issue->status == TRANSFERED) ? tracker_print_transfer_link($tracker, $issue) : '' ;
                $dataset = array($maxpriority - $issue->resolutionpriority + 1, $issuenumber, $summary.' '.$solution, $datereported, $reportedby, $assignedto, $status, 0 + $issue->watches, $transfer, $actions);
            } else {
                $dataset = array($maxpriority - $issue->resolutionpriority + 1, $issuenumber, $summary.' '.$solution, $datereported, $reportedby, $assignedto, $status, 0 + $issue->watches, $actions);
            }
        }
        if($ulpgckey !== false) {
            $hascomment = tracker_userlastcomment($issue, $tracker, $context);
            $lastcomment = ($hascomment) ? "&nbsp;<img src=\"".$OUTPUT->pix_url($hascomment, 'mod_tracker')."\" height=\"15\" alt=\"".tracker_getstring('lastcomment','tracker')."\" />" : '' ;
            $haslastseen = tracker_userlastseen($issue, $tracker, $context);
            $lastseen = ($haslastseen) ? "&nbsp;<img src=\"".$OUTPUT->pix_url($haslastseen, 'mod_tracker')."\" height=\"15\" border=\"0\" alt=\"".tracker_getstring('userlastseen','tracker')."\" />" : '' ;
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
    echo '<br/>';
    if (tracker_can_workon($tracker, $context)) {
        echo '<center>';
        echo '<p><input type="submit" name="go_btn" value="'.tracker_getstring('savechanges').'" /></p>';
        echo '</center>';
    }
} else {
    if (!$resolved) {
        echo '<br/>';
        echo '<br/>';
        echo $OUTPUT->notification(tracker_getstring('noissuesreported', 'tracker'), 'box generalbox', 'notice');
    } else {
        echo '<br/>';
        echo '<br/>';
        echo $OUTPUT->notification(tracker_getstring('noissuesresolved', 'tracker'), 'box generalbox', 'notice');
    }
}

echo '</form>';
