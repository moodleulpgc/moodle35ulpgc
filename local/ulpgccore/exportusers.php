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
 * The main group management user interface.
 *
 * @copyright 2006 The Open University, N.D.Freear AT open.ac.uk, J.White AT open.ac.uk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   core_group
 */
require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot . '/local/ulpgccore/lib.php');
require_once($CFG->dirroot . '/local/ulpgccore/user_export_form.php');
require_once($CFG->libdir . '/dataformatlib.php');

$courseid = required_param('id', PARAM_INT);

// Get the course information so we can print the header and
// check the course id is valid

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

$baseurl = new moodle_url('/local/ulpgccore/exportusers.php', array('id'=>$courseid));
$returnurl = $CFG->wwwroot.'/course/view.php?id='.$courseid;

$PAGE->set_url($baseurl);

// Make sure that the user has permissions to manage groups.
require_login($course);

$context = context_course::instance($course->id);
$PAGE->set_context($context);

require_capability('moodle/course:viewparticipants', $context);
require_capability('gradereport/grader:view', $context);
require_capability('moodle/user:viewhiddendetails', $context);

$strexportusers = get_string('exportusers', 'local_ulpgccore');
$pagetitle = "$course->shortname: $strexportusers";
$PAGE->set_pagelayout('incourse');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);

// Setup the form.
$mform = new local_ulpgccore_exportusers_form(null, array('course'=>$course));

if ($mform->is_cancelled()) {
    redirect($returnurl);

} elseif ($fromform = $mform->get_data()) {
    // Process the data and show download links.
    
    //print_object($fromform);
    
    $classname = 'dataformat_' . $fromform->dataformat . '\writer';
    if (!class_exists($classname)) {
        throw new coding_exception("Unable to locate dataformat/{$fromform->dataformat}/classes/writer.php");
    }
    $dataformat = new $classname;
    
    list($sql, $params, $columns) = local_ulpgccore_exportuser_getsql($courseid, $context, $fromform);
    
    //$users = $DB->get_records_sql($sql, $params);
    //print_object($users);
    
    
    $filename = clean_filename($fromform->filename);
    
    $rs_users = $DB->get_recordset_sql($sql, $params); 
    
    $SESSION->local_ulpgccore_export_columns = array_keys($columns);
    $SESSION->local_ulpgccore_role_names = '';
    if(isset($fromform->exportincludeuserroles) && $fromform->exportincludeuserroles) {
        $roles = get_all_roles();
        $SESSION->local_ulpgccore_role_names = role_fix_names($roles, $context, ROLENAME_ALIAS, true);
    }
  
    if($rs_users->valid() && $columns) {
        if (!headers_sent() && error_get_last()==NULL ) {
            download_as_dataformat($filename, $fromform->dataformat, $columns, $rs_users, 'local_ulpgccore_exportuser_row');
        //download_as_dataformat($filename, $dataformat, $columns, $iterator, $callback = null);
        } else {
            notice(get_string('errorheaderssent', 'local_ulpgccore'), $baseurl);
        }
    }
    $rs_users->close();
    $SESSION->local_ulpgccore_export_columns = '';
    $SESSION->local_ulpgccore_role_names = '';

    die;
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strexportusers, 3);

//print_object($_POST);

$mform->display();

echo $OUTPUT->footer();




