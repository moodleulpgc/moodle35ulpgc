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
 * Plugin administration pages are defined here.
 *
 * @package     report_datacheck
 * @category    admin
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/report/datacheck/check_forms.php');

$id = required_param('id', PARAM_INT);    // module ID
$action = optional_param('action', '', PARAM_ALPHA);

if ($id) {
    if (! $cm = get_coursemodule_from_id('data', $id)) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record('course', array('id'=>$cm->course))) {
        print_error('coursemisconf');
    }
    if (! $data = $DB->get_record('data', array('id'=>$cm->instance))) {
        print_error('invalidcoursemodule');
    }
} else {
    print_error('invalidcoursemodule');
}

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('report/datacheck:view', $context);


$baseurl = new moodle_url('/report/datacheck/index.php', array('id' => $cm->id));
if($action == 'download') {
    $baseurl->param('action', 'download');
}
$returnurl = new moodle_url('/mod/data/view.php', array('id' => $cm->id));

$PAGE->set_context($context);
$PAGE->set_url($baseurl);
$PAGE->set_heading(format_string($course->shortname.': '.$data->name, true, array('context' => $context)));

// standard groups support
$groupmode = groups_get_activity_groupmode($cm);
$groupid = '';
if ($groupmode) {
    $groupid = groups_get_activity_group($cm, true);
}

//// What to do. Action parameter signals which form is loaded 
if($action == 'download') {

    $straction = get_string('downloadfiles', 'report_datacheck');
    $actionhelp = 'downloadfiles';
    $filefields = array();
    $otherfields = array();
    $fieldrecords = $DB->get_records('data_fields', array('dataid'=>$data->id), 'name ASC', 'id, type, name, description');
    foreach($fieldrecords as $field) {
        if(($field->type == 'file') || ($field->type == 'picture')) {
            $filefields[$field->id] = $field;
        } else {
            $otherfields[$field->id] = $field;
        }
    }
    $mform = new report_datacheck_download_form(null, array('cmid'=>$id, 'dataid'=>$data->id, 'groupid'=>$groupid, 'filefields'=>$filefields, 'fields'=>$otherfields ));
    
} elseif($action == 'checked') {
    $straction = get_string('checkedcompliance', 'report_datacheck');
    $actionhelp = 'checkedcompliance';
    if($records = optional_param_array('records', array(), PARAM_TEXT)) {
        $records = array_flip($records);
        unset($records[0]);
    }
    $mform = new report_datacheck_compliance_form(null, array('cmid'=>$id, 'dataid'=>$data->id, 'groupid'=>$groupid, 'fields'=>$records, 'formdata'=>''));
} else {

    $straction = get_string('checkcompliance', 'report_datacheck');
    $actionhelp = 'checkcompliance';

    $fieldrecords = $DB->get_records('data_fields', array('dataid'=>$data->id), 'name ASC', 'id, type, name, description');
    $mform = new report_datacheck_checking_form(null, array('cmid'=>$id, 'dataid'=>$data->id, 'groupid'=>$groupid, 'fields'=>$fieldrecords));
    
}

$PAGE->set_title(format_string($data->name, true, array('context' => $context)).': '. $straction);

// If a file has been uploaded, then process it
if ($mform->is_cancelled()) {
    redirect($returnurl);

} else if ($formdata = $mform->get_data()) {

    if($action == 'download') {

        $message = report_datacheck_download_files($course, $data, $formdata);
        
        redirect($returnurl, $message);

    } elseif($action == 'checked') {

        if(isset($formdata->sendmessage) && $formdata->sendmessage) {
            $message = report_datacheck_email_to_users($course, $data, $formdata);
            // Trigger a report event.
            $event = \report_datacheck\event\report_sent::create(array('context' => $context));
            $event->trigger();

        } elseif(isset($formdata->setvalue) && $formdata->setvalue) {
            $message = report_datacheck_setvalue($course, $data, $formdata);
            // Trigger a report event.
            $event = \report_datacheck\event\report_updated::create(array('context' => $context));
            $event->trigger();
        
        } elseif(isset($formdata->submitbutton) && $formdata->submitbutton) {
            // pressed return to data module
            $message = '';
        }
        
        redirect($returnurl, $message);
    
    } elseif($action == '') {
        // OK
        $straction = get_string('checkedcompliance', 'report_datacheck');
        $actionhelp = 'checkedcompliance';

        $records = report_datacheck_compliance_list($data, $formdata);
        
        $mform = new report_datacheck_compliance_form(null, array('cmid'=>$id, 'dataid'=>$data->id, 'groupid'=>$groupid, 'fields'=>$records, 'formdata'=>$formdata));
    }
}

// Trigger a report viewed event.
$event = \report_datacheck\event\report_viewed::create(array('context' => $context));
$event->trigger();

/// Print the form
echo $OUTPUT->header();
echo $OUTPUT->heading_with_help($straction, $actionhelp, 'report_datacheck');
$mform ->display();
echo $OUTPUT->footer();


