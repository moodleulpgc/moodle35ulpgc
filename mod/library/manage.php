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
 * Prints an instance of mod_library.
 *
 * @package     mod_library
 * @copyright   2019 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/mod/library/locallib.php');
require_once($CFG->dirroot.'/mod/library/files_form.php');

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);
// ... module instance id.
$l  = optional_param('l', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);


if ($id) {
    list($course, $cm) = get_course_and_cm_from_cmid($id, 'library');
    $library = $DB->get_record('library', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($l) {
    $library = $DB->get_record('library', array('id' => $n), '*', MUST_EXIST);
    list($course, $cm) = get_course_and_cm_from_instance($library, 'library');
} else {
    print_error(get_string('missingidandcmid', mod_library));
}

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/library:manage', $context);

$baseurl = new moodle_url('/mod/library/manage.php', array('id' => $cm->id, 'action'=>$action));
$PAGE->set_url($baseurl);
$PAGE->set_title($course->shortname.': '.$library->name);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_activity_record($library);

$returnurl = new moodle_url('/mod/library/view.php', array('id' => $cm->id));

$source = library_get_source_plugin($library);
$folders = $source->get_folders();

$mform = new mod_library_files_form(null, array('id'=>$id, 'action'=> $action, 'folders'=>$folders));

$data = new stdClass();
$data->insertpath = $source->get_absolute_path();
$mform->set_data($data);

// prepare event to be set below
$eventdata = array();
$eventdata['objectid'] = $library->id;
$eventdata['context'] = $context;
$eventdata['userid'] = $USER->id;
$eventdata['other'] = array();
$eventdata['other']['reponame'] = $library->reponame;

if ($mform->is_cancelled()) {
    redirect($returnurl);
} elseif ($formdata = $mform->get_data()) {
    //// ACTIONS SECTION
    $fs = get_file_storage();
    $draftitemid = $formdata->files;
    $usercontext = context_user::instance($USER->id);
    $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
    
    if($formdata->action == 'add') {
        $count = $source->save_uploaded_files($draftfiles, $formdata->insertpath, $formdata->updatemode);
        $event = \mod_library\event\files_added::create($eventdata);
        $event->trigger();
        
    } elseif($formdata->action == 'del') {
        $count = $source->delete_selected_files($draftfiles);
        $event = \mod_library\event\files_deleted::create($eventdata);
        $event->trigger();
    }   
    
    if(isset($count)) {
        \core\notification::add(get_string($action.'filesdone', 'library', $count), \core\output\notification::NOTIFY_SUCCESS);
    }
    redirect($returnurl);
}

$output = $PAGE->get_renderer('mod_library');

$eventdata['other']['action'] = $action;
$event = \mod_library\event\manage_viewed::create($eventdata);
$event->trigger();

/// Print the page header, Output starts here
echo $output->header();
echo $output->heading(get_string($action.'files', 'library', $library->reponame));
$mform->display();
echo $output->footer();
