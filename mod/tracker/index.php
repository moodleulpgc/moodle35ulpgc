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
 *
 * This page lists all the instances of tracker in a particular course
 * Replace tracker with the name of your module
 */
require('../../config.php');
require_once($CFG->dirroot.'/mod/tracker/lib.php');

$id = required_param('id', PARAM_INT);   // course

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}

// Security.

require_login($course->id);

$PAGE->set_url('/mod/tracker/index.php', array('id'=>$id));
$PAGE->set_pagelayout('incourse');
$context = context_course::instance($course->id);

// Trigger instances list viewed event.
$event = \mod_tracker\event\course_module_instance_list_viewed::create(array('context' => $context));
$event->add_record_snapshot('course', $course);
$event->trigger();

// Get all required strings

$strtrackers = tracker_getstring('modulenameplural', 'tracker');
$strtracker  = tracker_getstring('modulename', 'tracker');

// Print the header.

$PAGE->set_title($strtrackers);
$PAGE->set_heading($strtrackers);
$PAGE->navbar->add($strtrackers, null);
echo $OUTPUT->header();

// Get all the appropriate data.

if (! $trackers = get_all_instances_in_course('tracker', $course)) {
echo $OUTPUT->notification(tracker_getstring('notrackers', 'tracker'), new moodle_url('course/view.php', array('id' => $course->id)));
    die;
}

// Print the list of instances (your module will probably extend this).

$timenow = time();
$strname  = tracker_getstring('name');
$strweek  = tracker_getstring('week');
$strtopic  = tracker_getstring('topic');

$table = new html_table();

if ($course->format == 'weeks') {
    $table->head  = array ($strweek, $strname);
    $table->align = array ('center', 'left');
} elseif ($course->format == 'topics') {
    $table->head  = array ($strtopic, $strname);
    $table->align = array ('center', 'left', 'left', 'left');
} else {
    $table->head  = array ($strname);
    $table->align = array ('left', 'left', 'left');
}

foreach ($trackers as $tracker) {
    $trackername = format_string($tracker->name);
    $linkurl = new moodle_url('/mod/tracker/view.php', array('id' => $tracker->coursemodule));
    if (!$tracker->visible) {
        //Show dimmed if the mod is hidden
        $link = '<a class="dimmed" href="'.$linkurl.'">'.$trackername.'</a>';
    } else {
        // Show normal if the mod is visible.
        $link = '<a href="'.$linkurl.'">'.$trackername.'</a>';
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array ($tracker->section, $link);
    } else {
        $table->data[] = array ($link);
    }
}

echo '<br />';

echo html_writer::table($table);

// Finish the page.

echo $OUTPUT->footer($course);
