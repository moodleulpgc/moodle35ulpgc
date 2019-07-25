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
 * Handle removal requests for the demostudent block.
 *
 * @package block_demostudent
 * @author Dominik Royko royko@ualberta.ca
 **/

require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/demostudent/locallib.php');
require_once($CFG->dirroot.'/lib/enrollib.php');

global $USER, $DB, $PAGE, $OUTPUT;

$viewrole = required_param('viewrole', PARAM_NOTAGS);
$courseid = required_param('courseid', PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);
$url = new moodle_url('/course/view.php', array('id' => $courseid));
$userid = $USER->id;
$username = $USER->username;
$sesskey = $USER->sesskey;
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_sesskey();
require_login($courseid);

$PAGE->set_url('/blocks/demostudent/remove.php',
    array('viewrole' => $viewrole, 'courseid' => $courseid, 'confirm' => $confirm));
$coursecontext = context_course::instance($courseid);


if ($viewrole != 'instructor') {
    print get_string('errorremovenotinstructor', 'block_demostudent');
    require_logout();
    redirect(new moodle_url($url, array('redirect' => 1)));
}

// If we do not have the capability to add this block, we likely landed here by accident or malice.
if (!has_capability('block/demostudent:addinstance', $coursecontext)) {
    // Test this as above, but start from a student account instead of DemoStudent.
    print get_string('errormissingaddinstancecapability', 'block_demostudent');
    require_logout();
    redirect(new moodle_url($url, array('redirect' => 1)));
}

$demostudentusername = generate_demostudent_name($username);
$demostudentuser = get_complete_user_data('username', $demostudentusername);
if (!$demostudentuser) {
    redirect(new moodle_url($url, array('redirect' => 1)));
}

if ($confirm) {
    // Unenrol DemoStudent from the course.
    if (!enrol_is_enabled('manual')) {
        redirect(new moodle_url($url, array('redirect' => 1)));
    }
    if (!$enrol = enrol_get_plugin('manual')) {
        redirect(new moodle_url($url, array('redirect' => 1)));
    }
    if (!$instances = $DB->get_records('enrol', array('enrol' => 'manual', 'courseid' => $courseid,
                                                       'status' => ENROL_INSTANCE_ENABLED), 'sortorder,id ASC')) {
        redirect(new moodle_url($url, array('redirect' => 1)));
    }

    $instance = reset($instances);
    $enrol->unenrol_user($instance, $demostudentuser->id);
    redirect(new moodle_url($url, array('redirect' => 1)));
}



$yesurl = new moodle_url($PAGE->url, array('confirm' => 1, 'sesskey' => sesskey(), 'url' => $url,
                                            'viewrole' => $viewrole, 'courseid' => $courseid));
$message = get_string('unenrolconfirm', 'core_enrol', array('user' => fullname($demostudentuser, true),
                      'course' => format_string($course->fullname),
                      'enrolinstancename' => get_string('pluginname', 'block_demostudent')));
$fullname = fullname($demostudentuser);
$title = get_string('unenrol', 'core_enrol');

$PAGE->set_pagelayout('admin');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title);
$PAGE->navbar->add($fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($fullname);
echo $OUTPUT->confirm($message, $yesurl, $url);
echo $OUTPUT->footer();


