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
 * Process Registry submissions
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

$PAGE->set_url('/mod/registry/submissions.php', array('id' => $cm->id));
$PAGE->set_title(format_string($registry->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$returnurl = new moodle_url('/mod/registry/view.php', array('id' => $cm->id));

if(($formdata = data_submitted()) && confirm_sesskey()) {

    $courses = array();
    foreach($formdata as $key=>$value) {
        if(substr($key, 0, 11) == 'coursesend_') {
            $courses[$value] = $value;
        }
    }

    if(isset($formdata->confirmed) && $formdata->confirmed && confirm_sesskey()) {

        foreach($courses as $c) {
            $regsub = registry_get_course_submission($registry, $c, true);
            $regsub->userid = $USER->id;
            $regsub->timemodified = time();
            if(!$DB->record_exists('tracker_issue', array('id'=>$regsub->issueid))) {
                $regsub->issueid = registry_create_tracker_issue($registry, $c, $USER->id);
            }
            $hash = registry_get_course_contenthash($registry, $c);
            $regsub->itemhash = $hash;
            $status = 0;
            $DB->update_record('registry_submissions', $regsub);
            registry_update_tracker_issue($registry, $regsub, $status);

            // new logging data
            $eventdata = array();
            $eventdata['context'] = $context;
            $eventdata['objectid'] = $regsub->id;
            $eventdata['userid'] = $USER->id;
            $eventdata['courseid'] = $course->id;
            $eventdata['other'] = array();
            $eventdata['other']['registryid'] = $registry->id;
            $eventdata['other']['regcourse'] = $c;
            $event = \mod_registry\event\item_submitted::create($eventdata);
            $event->add_record_snapshot('registry_submissions', $regsub);
            $event->trigger();
        }

        //add_to_log($course->id, 'registry', 'submit', "submissions.php?id={$cm->id}", $registry->name, $cm->id);

        redirect($returnurl, get_string('changessaved'));
    }

    /// Prints confirm meggage
    echo $OUTPUT->header();

    if($courses) {
        list($insql, $params) = $DB->get_in_or_equal($courses);
        $courses = $DB->get_records_select('course', " id $insql ", $params, 'fullname ASC', 'id, fullname, shortname');
        $coursenames = array();
        foreach($courses as $c) {
            $coursenames[] = $c->shortname.' - '.format_string($c->fullname);
        }
        $names = implode('<br />', $coursenames);
        $params = get_object_vars($formdata);
        $params['confirmed'] = 1;
        $confirmurl = new moodle_url('/mod/registry/submissions.php', $params);
        $message = get_string('submitconfirm', 'registry', $names);
        echo $OUTPUT->confirm($message, $confirmurl, $returnurl);
    } else {
        echo $OUTPUT->notification(get_string('nodata', 'registry'));
        echo $OUTPUT->continue_button($returnurl);
    }
    echo $OUTPUT->footer();
    die;
}

redirect($returnurl);
