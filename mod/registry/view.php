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
 * Prints a particular instance of registry module
 *
 * @package    mod
 * @subpackage registry
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot.'/mod/registry/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$r  = optional_param('r', 0, PARAM_INT);  // registry instance ID
$review  = optional_param('review', 0, PARAM_INT);  // registry instance ID

if ($id) {
    $cm         = get_coursemodule_from_id('registry', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $registry  = $DB->get_record('registry', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($r) {
    $registry  = $DB->get_record('registry', array('id' => $r), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $registry->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('registry', $registry->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

/// Print the page header

$PAGE->set_url('/mod/registry/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($registry->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);


// Output starts here
echo $OUTPUT->header();

/// find out current groups mode
    groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/forum/view.php?id=' . $cm->id);
    $currentgroup = groups_get_activity_group($cm);
    $groupmode = groups_get_activity_groupmode($cm);


if ($registry->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('registry', $registry, $cm->id), 'generalbox mod_introbox', 'registryintro');
    $timeclass = $registry->timedue <= time() ? '' : 'late';
    $date = html_writer::tag('span', userdate($registry->timedue), array('class'=>$timeclass));
    echo $OUTPUT->box(get_string('timeduemsg', 'registry').$date, 'generalbox noticebox', 'registrytimedue');
}
echo '<br />';

// Replace the following lines with you own code
echo $OUTPUT->heading(get_string('registrysummary', 'registry'));

$canreview = has_capability('mod/registry:review', $context);
if(!$canreview) {
    $review = 0;
}

registry_view_user_registerings($cm, $course, $registry, $USER->id, $review);

//add_to_log($course->id, 'registry', 'view', "view.php?id={$cm->id}", $registry->name, $cm->id);
$eventdata = array();
$eventdata['objectid'] = $registry->id;
$eventdata['context'] = $context;
if($review) {
    $event = \mod_registry\event\course_module_reviewed::create($eventdata);
} else {
    $event = \mod_registry\event\course_module_viewed::create($eventdata);
}
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('registry', $registry);
$event->trigger();

// Finish the page
echo $OUTPUT->footer();
