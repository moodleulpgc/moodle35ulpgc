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

defined('MOODLE_INTERNAL') || die();

/**
 * @package mod_tracker
 * @category mod
 * @author Clifford Tham, Valery Fremaux > 1.8
 * @date 02/12/2007
 */

    switch ($view) { // ecastro ULPGC moved upwards to set screen before totalissuses calculation
        case 'view' :
            if (!preg_match("/mytickets|mywork|browse|user|search|viewanissue|editanissue|assigns/", $screen)) $screen = 'mytickets'; // ecastro ULPGC
            break;
        case 'resolved' :
            if (!preg_match("/mytickets|browse|viewanissue|mywork/", $screen)) $screen = 'mytickets';
        break;
        case 'profile':
            if (!preg_match("/myprofile|mypreferences|mywatches|myqueries/", $screen)) $screen = 'myprofile';
        break;
        case 'reports':
            if (!preg_match("/status|evolution|print/", $screen)) $screen = 'status';
        break;
        case 'admin':
            if (!preg_match("/summary|manageelements|managefiles|managewords|managenetwork/", $screen)) $screen = 'summary';
    }

    if ($screen == 'mytickets') {
        $totalissues = $DB->count_records_select('tracker_issue', "trackerid = ? AND (status <> ".RESOLVED." AND status <> ".ABANDONNED.") AND reportedby = ? ", array($tracker->id, $USER->id));
        $totalresolvedissues = $DB->count_records_select('tracker_issue', "trackerid = ? AND (status = ".RESOLVED." OR status = ".ABANDONNED.") AND reportedby = ? ", array($tracker->id, $USER->id));
    } elseif ($screen == 'mywork') {
        $totalissues = $DB->count_records_select('tracker_issue', "trackerid = ? AND status <> ".RESOLVED." AND status <> ".ABANDONNED." AND assignedto = ? ", array($tracker->id, $USER->id));
        $totalresolvedissues = $DB->count_records_select('tracker_issue', "trackerid = ? AND (status = ".RESOLVED." OR status = ".ABANDONNED.") AND assignedto = ? ", array($tracker->id, $USER->id));
    } else {
        $userselect = '';
        $params = array($tracker->id);
        if(!$canviewall && (($tracker->supportmode == 'usersupport') || ($tracker->supportmode == 'boardreview'))) {
            $userselect = ' AND reportedby = ? ';
            $params[] = $USER->id;
        }
        $totalissues = $DB->count_records_select('tracker_issue', "trackerid = ? AND status <> ".RESOLVED." AND status <> ".ABANDONNED.$userselect , $params); // ecastro ULPGC improve tab total issues count
        $totalresolvedissues = $DB->count_records_select('tracker_issue', "trackerid = ? AND (status = ".RESOLVED." OR status = ".ABANDONNED.")".$userselect , $params);
    }

    // Print tabs with options for user
    if (has_capability('mod/tracker:report', $context)) {
        $rows[0][] = new tabobject('reportanissue', "reportissue.php?id={$cm->id}", tracker_getstring('newissue', 'tracker').' ');
    }

    $rows[0][] = new tabobject('view', "view.php?id={$cm->id}&amp;view=view", tracker_getstring('view', 'tracker').' ('.$totalissues.' '.tracker_getstring('issues','tracker').') ');

    $rows[0][] = new tabobject('resolved', "view.php?id={$cm->id}&amp;view=resolved", tracker_getstring('resolvedplural', 'tracker').' ('.$totalresolvedissues.' '.tracker_getstring('issues','tracker').')  ');

    $rows[0][] = new tabobject('profile', "view.php?id={$cm->id}&amp;view=profile", tracker_getstring('profile', 'tracker').' ');

    if (has_capability('mod/tracker:viewreports', $context)) {
        $rows[0][] = new tabobject('reports', "view.php?id={$cm->id}&amp;view=reports", tracker_getstring('reports', 'tracker').' ');
    }

    if (has_capability('mod/tracker:configure', $context)) {
        $rows[0][] = new tabobject('admin', "view.php?id={$cm->id}&amp;view=admin", tracker_getstring('administration', 'tracker').'  &nbsp;');
    }

    $myticketsstr = ($tracker->supportmode != 'taskspread') ? tracker_getstring('mytickets', 'tracker') : tracker_getstring('mytasks', 'tracker');

    $ulpgckey = false;
    $canviewall = false;
    $canmanage = false;
    if(($tracker->supportmode == 'usersupport') || ($tracker->supportmode == 'boardreview')) {
        $ulpgckey = true;
        $canviewall = has_capability('mod/tracker:viewallissues', $context);
        $canmanage = has_capability('mod/tracker:manage', $context);
    }


    // submenus
    $selected = null;
    $activated = null;
    switch ($view) {
        case 'view' :
            if (!preg_match("/mytickets|mywork|browse|user|search|viewanissue|editanissue|assigns/", $screen)) $screen = 'mytickets'; // ecastro ULPGC
            if (has_capability('mod/tracker:report', $context)) {
                $rows[1][] = new tabobject('mytickets', "view.php?id={$cm->id}&amp;view=view&amp;screen=mytickets", $myticketsstr);
            }
            if (tracker_has_assigned($tracker, false)) {
                $rows[1][] = new tabobject('mywork', "view.php?id={$cm->id}&amp;view=view&amp;screen=mywork", tracker_getstring('mywork', 'tracker'));
            }
            if (has_capability('mod/tracker:viewallissues', $context) || $tracker->supportmode == 'bugtracker') {
                $rows[1][] = new tabobject('browse', "view.php?id={$cm->id}&amp;view=view&amp;screen=browse", tracker_getstring('browse', 'tracker'));
                if($ulpgckey) {
                    $rows[1][] = new tabobject('user', "view.php?id={$cm->id}&amp;view=view&amp;screen=user", tracker_getstring('userview', 'tracker'));
                }
            }
            if ($tracker->supportmode == 'bugtracker' || ($canviewall && (($tracker->supportmode == 'usersupport') || ($tracker->supportmode == 'boardreview')))) { // ecastro ULPGC
                $rows[1][] = new tabobject('search', "view.php?id={$cm->id}&amp;view=view&amp;screen=search", tracker_getstring('search', 'tracker'));
            }
            if ($canmanage) {
                $rows[1][] = new tabobject('assigns', "view.php?id={$cm->id}&amp;view=view&amp;screen=assigns", tracker_getstring('assigns', 'tracker'));
            }
            break;
        case 'resolved' :
            if (!preg_match("/mytickets|browse|viewanissue|mywork/", $screen)) $screen = 'mytickets';
            if (has_capability('mod/tracker:report', $context)) {
                $rows[1][] = new tabobject('mytickets', "view.php?id={$cm->id}&amp;view=resolved&amp;screen=mytickets", $myticketsstr);
            }
            if (tracker_has_assigned($tracker, true)) {
                $rows[1][] = new tabobject('mywork', "view.php?id={$cm->id}&amp;view=view&amp;screen=mywork", tracker_getstring('mywork', 'tracker'));
            }
            if (has_capability('mod/tracker:viewallissues', $context) || $tracker->supportmode == 'bugtracker') {
                $rows[1][] = new tabobject('browse', "view.php?id={$cm->id}&amp;view=resolved&amp;screen=browse", tracker_getstring('browse', 'tracker'));
            }
        break;
        case 'profile':
            if (!preg_match("/myprofile|mypreferences|mywatches|myqueries/", $screen)) $screen = 'myprofile';
            $rows[1][] = new tabobject('myprofile', "view.php?id={$cm->id}&amp;view=profile&amp;screen=myprofile", tracker_getstring('myprofile', 'tracker'));
            $rows[1][] = new tabobject('mypreferences', "view.php?id={$cm->id}&amp;view=profile&amp;screen=mypreferences", tracker_getstring('mypreferences', 'tracker'));
            $rows[1][] = new tabobject('mywatches', "view.php?id={$cm->id}&amp;view=profile&amp;screen=mywatches", tracker_getstring('mywatches', 'tracker'));
            //if ($tracker->supportmode == 'bugtracker') {
            if ($tracker->supportmode == 'bugtracker' || ($canviewall && (($tracker->supportmode == 'usersupport') || ($tracker->supportmode == 'boardreview')))) { // ecastro ULPGC
                $rows[1][] = new tabobject('myqueries', "view.php?id={$cm->id}&amp;view=profile&amp;screen=myqueries", tracker_getstring('myqueries', 'tracker'));
            }
        break;
        case 'reports':
            if (!preg_match("/status|evolution|print/", $screen)) $screen = 'status';
            $rows[1][] = new tabobject('status', "view.php?id={$cm->id}&amp;view=reports&amp;screen=status", tracker_getstring('status', 'tracker'));
            $rows[1][] = new tabobject('evolution', "view.php?id={$cm->id}&amp;view=reports&amp;screen=evolution", tracker_getstring('evolution', 'tracker'));
            $rows[1][] = new tabobject('print', "view.php?id={$cm->id}&amp;view=reports&amp;screen=print", tracker_getstring('print', 'tracker'));
        break;
        case 'admin':
            if (!preg_match("/summary|manageelements|managefiles|managewords|managenetwork/", $screen)) $screen = 'summary';
            $rows[1][] = new tabobject('summary', "view.php?id={$cm->id}&amp;view=admin&amp;screen=summary", get_string('summaryadmin', 'tracker'));
            $rows[1][] = new tabobject('manageelements', "view.php?id={$cm->id}&amp;view=admin&amp;screen=manageelements", tracker_getstring('manageelements', 'tracker'));
            if($canmanage) {
                $rows[1][] = new tabobject('managefiles', "view.php?id={$cm->id}&amp;view=admin&amp;screen=managefiles", tracker_getstring('managefiles', 'tracker'));
                $rows[1][] = new tabobject('managewords', "view.php?id={$cm->id}&amp;view=admin&amp;screen=managewords", tracker_getstring('managewords', 'tracker'));
            }
            if (has_capability('mod/tracker:configurenetwork', $context)) {
                $rows[1][] = new tabobject('managenetwork', "view.php?id={$cm->id}&amp;view=admin&amp;screen=managenetwork", tracker_getstring('managenetwork', 'tracker'));
            }
            break;
        default:
    }
    if (!empty($screen)) {
        $selected = $screen;
        $activated = array($view);
    } else {
        $selected = $view;
    }
    echo $OUTPUT->container_start('mod-header');
    print_tabs($rows, $selected, '', $activated);
    echo '<br/>';
    echo $OUTPUT->container_end();

