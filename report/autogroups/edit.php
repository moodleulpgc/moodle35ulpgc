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
 * Script for synchronizing group membership in a course.
 *
 * @package   report_autogroups
 * @copyright 2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
//require_once($CFG->dirroot . '/course/lib.php');
require_once(__DIR__ . '/form.php');
require_once $CFG->dirroot.'/group/lib.php';
require_once(__DIR__ . '/locallib.php');

$cid = required_param('cid', PARAM_INT);       // course id
$course = $DB->get_record('course', array('id' => $cid), '*', MUST_EXIST);

$syncid = optional_param('sid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

// needed to setup proper $COURSE
require_login($course);
//setting page url
$PAGE->set_url('/report/autogroups/index.php', array('id' => $cid));
//setting page layout to report
$PAGE->set_pagelayout('report');
//coursecontext instance
$coursecontext = context_course::instance($course->id);
//checking if user is capable of viewing this report in $coursecontext
require_capability('report/autogroups:view', $coursecontext);
//strings
$strgroupreport = get_string('autogroups' , 'report_autogroups');

//setting page title and page heading
$PAGE->set_title($course->shortname .': '. $strgroupreport);
$PAGE->set_heading($course->fullname);

$returnurl = new moodle_url('index.php', array('id' => $cid));

// process actions first deleting of sync data first
if($action && confirm_sesskey()) {
    $msg = '';
    $sync = $DB->get_record('groups_autogroups', array('id'=>$syncid, 'course'=>$course->id), '*', MUST_EXIST);
    switch($action) {
        case 'del'  :
                    //ask confirmation
                    $confirm = optional_param('confirm', 0, PARAM_BOOL);
                    if (!$confirm) {
                        $strdelete = get_string('deletesync', 'report_autogroups');
                        $PAGE->set_title($strdelete);
                        $PAGE->set_heading($course->fullname. ': '. $strdelete);
                        $PAGE->navbar->add($strdelete, null);
                        echo $OUTPUT->header();
                        $optionsyes = array('id'=>$cid, 'action'=>'del', 'cid'=>$course->id, 'sid'=>$syncid, 'sesskey'=>sesskey(), 'confirm'=>1);
                        $optionsno  = array('id'=>$cid);
                        $formcontinue = new single_button(new moodle_url('/report/autogroups/edit.php', $optionsyes), get_string('yes'), 'get');
                        $formcancel = new single_button(new moodle_url('/report/autogroups/index.php', $optionsno), get_string('no'), 'get');

                        $names = new stdClass;
                        $names->target = $DB->get_field('groups', 'name', array('id'=>$sync->targetgroup));
                        $names->search = "{$sync->searchterm} ({$sync->searchfield})";
                        echo $OUTPUT->confirm(get_string('deletesyncconfirm', 'report_autogroups', $names), $formcontinue, $formcancel);
                        echo $OUTPUT->footer();
                        die;
                    } else {
                        autogroups_delete_sync($course->id, $syncid);
                        $msg = get_string('deletedsync', 'report_autogroups');
                    }
                    break;
        case 'show' :
                    $sync->visible = 1;
                    $DB->update_record('groups_autogroups', $sync);
                    break;
        case 'hide' :
                    $sync->visible = 0;
                    $DB->update_record('groups_autogroups', $sync);
                    break;
    }
    redirect($returnurl, $msg);
}

//creating form instance, passed course id as parameter to action url
$mform = new report_autogroups_form(new moodle_url('edit.php', array('cid' => $cid)),
            array('course' => $course, 'sid'=>$syncid));


if ($mform->is_cancelled()) {            //check if form is cancelled
    //redirect to course view page if form is cancelled
    redirect($returnurl);
} else if ($mform->is_submitted()) {        //check if form is submitted
    $data = $mform->get_data();
    $syncid = autogroups_save_sync($course->id, $data->sid, $data);
    redirect($returnurl, get_string('changessaved'));
}

if($syncid) {
    $sync = $DB->get_record('groups_autogroups', array('course'=>$course->id, 'id'=>$syncid));
    $sync->sid = $syncid;
    $sync->cid = $course->id;
    $mform->set_data($sync);
}

//Displaying header and heading
$stredit = get_string('editsync', 'report_autogroups');
$PAGE->navbar->add($stredit, null);
echo $OUTPUT->header();
echo $OUTPUT->heading($stredit);

//display form
$mform->display();

//display page footer
echo $OUTPUT->footer();
