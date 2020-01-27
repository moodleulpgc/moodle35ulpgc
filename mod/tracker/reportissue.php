<?php
// This file is part of Moodle - http://moodle.org/
// // Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// // Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// // You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package mod_tracker
 * @category mod
 * @author Clifford Tham, Valery Fremaux > 1.8
 * @date 02/12/2007
 */
require('../../config.php');
require_once($CFG->dirroot."/mod/tracker/lib.php");
require_once($CFG->dirroot."/mod/tracker/locallib.php");
require_once $CFG->dirroot.'/mod/tracker/forms/reportissue_form.php';

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // tracker ID

if ($id) {
    if (! $cm = get_coursemodule_from_id('tracker', $id)) {
        print_error('errorcoursemodid', 'tracker');
    }

    if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('errorcoursemisconfigured', 'tracker');
    }

    if (! $tracker = $DB->get_record('tracker', array('id' => $cm->instance))) {
        print_error('errormoduleincorrect', 'tracker');
    }
} else {

    if (! $tracker = $DB->get_record('tracker', array('id' => $a))) {
        print_error('errormoduleincorrect', 'tracker');
    }

    if (! $course = $DB->get_record('course', array('id' => $tracker->course))) {
        print_error('errorcoursemisconfigured', 'tracker');
    }
    if (! $cm = get_coursemodule_from_instance("tracker", $tracker->id, $course->id)) {
        print_error('errorcoursemodid', 'tracker');
    }
}

$screen = tracker_resolve_screen($tracker, $cm);
$view = tracker_resolve_view($tracker, $cm);

// Security.

$context = context_module::instance($cm->id);
require_course_login($course->id, false, $cm);
require_capability('mod/tracker:report', $context);

$canviewall = has_capability('mod/tracker:viewallissues', $context);
// setting page
$url = new moodle_url('/mod/tracker/reportissue.php', array('id' => $id));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(format_string($tracker->name));
$PAGE->set_heading(format_string($tracker->name));
//$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'tracker'));

$form = new TrackerIssueForm(new moodle_url('/mod/tracker/reportissue.php'), array('tracker' => $tracker, 'cmid' => $id));

if (!$form->is_cancelled()) {
    if ($data = $form->get_data()) {

        if (!$issue = tracker_submitanissue($tracker, $data)) {
            print_error('errorcannotsubmitticket', 'tracker');
        }

        $event = \mod_tracker\event\tracker_issuereported::create_from_issue($tracker, $issue->id);
        $event->trigger();

        // stores files
        $data = file_postupdate_standard_editor($data, 'description', $form->editoroptions, $context, 'mod_tracker', 'issuedescription', $data->issueid);
        // update back reencoded field text content
        $DB->set_field('tracker_issue', 'description', $data->description, array('id' => $issue->id));

        // log state change
        $stc = new StdClass;
        $stc->userid = $USER->id;
        $stc->issueid = $issue->id;
        $stc->trackerid = $tracker->id;
        $stc->timechange = time();
        $stc->statusfrom = POSTED;
        $stc->statusto = POSTED;
        $DB->insert_record('tracker_state_change', $stc);
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox', 'tracker-acknowledge');
        echo (empty($tracker->thanksmessage)) ? tracker_getstring('thanksdefault', 'tracker') : format_string($tracker->thanksmessage) ;
        echo $OUTPUT->box_end();
        $url = new moodle_url('/mod/tracker/view.php', array('id'=>$cm->id, 'view'=>'view')); // ecastro ULPGC
        echo $OUTPUT->continue_button($url);
        echo $OUTPUT->footer();

        // notify all admins
        if ($tracker->allownotifications) {
            tracker_notify_submission($issue, $cm, $tracker);
            if ($issue->assignedto) {
                tracker_notifyccs_changeownership($issue->id, $tracker);
            }
        }
        die;
    }
}

echo $OUTPUT->header();

$view = 'reportanissue';
include_once($CFG->dirroot.'/mod/tracker/menus.php');

echo $OUTPUT->box(format_text($tracker->intro, $tracker->introformat), 'box generalbox', 'intro'); // ecastro ULPGC
$now = time();
$existing = 0;
$norepeatstates = explode(',', $tracker->statenonrepeat);
$message = array();
$isopen = true;
if($tracker->allowsubmissionsfromdate > 0) {
    $time = userdate($tracker->allowsubmissionsfromdate);
    $label = ($tracker->allowsubmissionsfromdate >= $now) ? 'reportwillopenon' : 'reportopenedon';
    $isopen = $isopen && ($tracker->allowsubmissionsfromdate < $now);
    $message[] = get_string($label, 'tracker', $time); 
} 
if($tracker->duedate > 0){
    $time = userdate($tracker->duedate);
    $label = ($tracker->duedate >= $now) ? 'reportwillcloseon' : 'reportclosedon';
    $isopen = $isopen && ($tracker->duedate >= $now);
    $message[] = get_string($label, 'tracker', $time); 
} 
if(is_array($norepeatstates) && $norepeatstates) {
    list($insql, $params) = $DB->get_in_or_equal($norepeatstates, SQL_PARAMS_NAMED, 's');
    $select = " trackerid = :tid AND reportedby = :userid AND status $insql ";
    $params['tid'] = $tracker->id;
    $params['userid'] = $USER->id;
    $existing = $DB->count_records_select('tracker_issue', $select, $params);
    if($existing){
        $message[] = get_string('reportsactive', 'tracker', $existing); 
    }
}

echo $OUTPUT->container(implode('<br />', $message) , 'box centerpara'); // ecastro ULPGC

if(($isopen && !$existing) || has_capability('mod/tracker:reportpastdue', $context)) {
    $form->display();
} elseif($existing){
    echo $OUTPUT->box(get_string('reportnotallowed', 'tracker', $existing), 'box centerpara  alert-warning text-danger');
}

echo $OUTPUT->footer();
