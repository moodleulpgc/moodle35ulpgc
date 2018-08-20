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
 * Displays different views of the supervision warnings.
 *
 * @package    report_supervision
 * @copyright  2012 Enrique Castro at ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/report/supervision/locallib.php');
require_once($CFG->dirroot.'/report/supervision/editwarningform.php');
require_once($CFG->dirroot.'/local/supervision/locallib.php');
//require_once($CFG->libdir.'/adminlib.php');

$id          = optional_param('id', SITEID, PARAM_INT);// Course ID
$scope       = optional_param('scope', 'category', PARAM_ALPHA); // category or department report
$item        = optional_param('item', 0, PARAM_INT);// category/department ID
$user        = optional_param('user', 0, PARAM_INT); // User to display
$fromdate    = optional_param('fromdate', -1, PARAM_INT);
$todate      = optional_param('todate', 0, PARAM_INT);
$warningtype = optional_param('warning', '', PARAM_ALPHANUMEXT);
$display     = optional_param('display', 'all', PARAM_ALPHANUM);
$sort        = optional_param('sort', 'delay', PARAM_ALPHANUM);

$page        = optional_param('page', '0', PARAM_INT);     // which page to show
$perpage     = optional_param('perpage', '100', PARAM_INT); // how many per page
$chooselog   = optional_param('chooselog', 0, PARAM_INT);
$logformat   = optional_param('logformat', 'showashtml', PARAM_ALPHA);

if(!$id) {
    $id = SITEID;
}

$course = local_ulpgccore_get_course_details($id);

require_login($course);

$context = context_course::instance($course->id);

if(!$canmanage = has_capability('local/supervision:manage', $context)) {

    if($scope == 'department') {
        $courseitem = $course->department;
    } else {
        $courseitem = $course->category;
    }
    // check if user has supervisor permission
    $items = supervision_get_supervised_items($USER->id, $scope);
    if($items && (in_array($courseitem, $items) || $course->id == SITEID) ) {
        // this is a supervisor viewing an allowed category/department
        if(!in_array($item, $items)) {
            //if set to all categories or category not accessible, set to allowed
            if($courseitem) {
                $item = $courseitem;
            } else {
                $item = reset($items);
            }
        }

        // now check course access

        $warnings = supervision_supervisor_warningtypes($USER->id);

    } else {
        //regular user teacher
        require_capability('report/supervision:view', $context);
        $item = $courseitem;
        $user = $USER->id;
    }

} else {
    // this is an admin, gets all
    require_capability('report/supervision:view', $context);
}


$params = array();
if ($id !== 0) {
    $params['id'] = $id;
}
if ($scope !== '') {
    $params['scope'] = $scope;
}
if ($item !== '') {
    $params['item'] = $item;
}
if ($user !== '') {
    $params['user'] = $user;
}
if ($fromdate !== '') {
    $params['fromdate'] = $fromdate;
}
if ($todate !== '') {
    $params['todate'] = $todate;
}
if ($warningtype !== 0) {
    $params['warning'] = $warningtype;
}
if ($display !== 0) {
    $params['display'] = $display;
}
if ($sort !== '0') {
    $params['sort'] = $sort;
}
if ($perpage !== '100') {
    $params['perpage'] = $perpage;
}
if ($chooselog !== 0) {
    $params['chooselog'] = $chooselog;
}
if ($logformat !== 'showashtml') {
    $params['logformat'] = $logformat;
}

$baseurl = new moodle_url( '/report/supervision/index.php', $params);

$PAGE->set_context($context);
$PAGE->set_url('/report/supervision/index.php', $params);
$PAGE->set_pagelayout('standard');
$PAGE->set_cacheable(true);
$title = get_string('pluginname', 'report_supervision');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title, $baseurl);
$PAGE->navbar->add($title, null);

//add_to_log($course->id, "course", "report supervision warnings", "report/supervision/index.php?id=$course->id", $course->id);

$strlogs = get_string('logs');
$stradministration = get_string('administration');
$strreports = get_string('reports');

$sesion_instance = new \core\session\manager();
$sesion_instance->write_close();

/// recalculate and other actions if needed
$action = optional_param('action', '', PARAM_ALPHA);
switch ($action) {
    case 'recalculate'  :
                        report_supervision_recalculate_warnings($course, $scope, $item,  $user, $fromdate, $todate, $warningtype, $display);
                        break;
    case 'fixall'       :
                        $fixtime = time();
                        report_supervision_fixall_warnings($fixtime,  $course, $scope, $item,  $user, $fromdate, $todate, $warningtype, $display);
                        break;
    case 'nullall'      :
                        $fixtime = -time();
                        report_supervision_fixall_warnings($fixtime, $course, $scope, $item,  $user, $fromdate, $todate, $warningtype, $display);
                        break;
}


/// create/edit supervision record
$edit = optional_param('edit', 0, PARAM_INT);
if($edit) {

    $mform = new supervision_editwarning_form(null, array('params' => $params, 'edit'=>$edit ));
    $warning = false;
    if($edit > 0) {
        if($warning = $DB->get_record('supervision_warnings', array('id' => $edit))) {
            unset($warning->id);
            $warning->wid = $edit;
            $mform->set_data($warning);
        }
    }

    if ($mform->is_cancelled()) {
        redirect($baseurl);
    } elseif ($formdata = $mform->get_data()) {
        /// process form & store permission in database
        if($warning->wid) {
            $warning->comment = $formdata->comment;
            $fixed = $formdata->fixnow * time();
            if($fixed != 0) {
                $warning->timefixed = $fixed;
            }
            $warning->id = $warning->wid;
            $DB->update_record('supervision_warnings', $warning);
        }
        redirect($baseurl, get_string('changessaved'));
    }

    $PAGE->navbar->add(get_string('editwarning', 'report_supervision'), null);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($title);

    $mform->display();

    echo $OUTPUT->footer();

    die;
}

if (!empty($chooselog)) {
    switch ($logformat) {
        case 'showashtml':
            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string('chooselogs') .':');
            report_supervision_print_selector_form($course, $scope, $item,  $user, $fromdate, $todate,
                                                    $warningtype, $display, $sort, $perpage, $logformat);

            report_supervision_print_warnings($course, $scope, $item,  $user, $fromdate, $todate, $warningtype, $display, $sort, $page, $perpage);
            break;
        case 'downloadascsv':
            if (!report_supervision_print_warnings_csv($course, $scope, $item,  $user, $fromdate, $todate, $warningtype, $display, $sort)) {
                echo $OUTPUT->notification("No logs found!");
                echo $OUTPUT->footer();
            }
            exit;
        case 'downloadasods':
            if (!report_supervision_print_warnings_ods($course, $scope, $item,  $user, $fromdate, $todate, $warningtype, $display, $sort)) {
                echo $OUTPUT->notification("No logs found!");
                echo $OUTPUT->footer();
            }
            exit;
        case 'downloadasexcel':
            if (!report_supervision_print_warnings_xls($course, $scope, $item,  $user, $fromdate, $todate, $warningtype, $display, $sort)) {
                echo $OUTPUT->notification("No logs found!");
                echo $OUTPUT->footer();
            }
            exit;
    }


} else {
    echo $OUTPUT->header();

    echo $OUTPUT->heading(get_string('chooselogs') .':');

    report_supervision_print_selector_form($course, $scope, $item,  $user, $fromdate, $todate,
                                                    $warningtype, $display, $sort, $perpage, $logformat);
}

// Trigger a report viewed event.
$event = \report_supervision\event\report_viewed::create(array('context' => $context));
$event->trigger();

echo $OUTPUT->footer();

