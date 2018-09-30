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
 * Report Trackertools adds some tools to the Tracker module to operate on bulk issues
 *
 * @copyright 2017 Enrique Castro @ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package report_trackertools
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/report/trackertools/locallib.php');
require_once($CFG->dirroot.'/report/trackertools/tools_forms.php');

$id = required_param('id', PARAM_INT);    // module ID
$action = required_param('a', PARAM_ALPHA);    // action flag

if (! $cm = get_coursemodule_from_id('tracker', $id)) {
    print_error('errorcoursemodid', 'tracker');
}

if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('errorcoursemisconfigured', 'tracker');
}
if (! $tracker = $DB->get_record('tracker', array('id' => $cm->instance))) {
    print_error('errormoduleincorrect', 'tracker');
}

$context = context_module::instance($cm->id);
$baseurl = new moodle_url('/report/trackertools/index.php', array('id' => $cm->id, 'a' => $action));
$returnurl = new moodle_url('/mod/tracker/view.php', array('id' => $cm->id));
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_heading(format_string($tracker->name));
$straction = get_string($action, 'report_trackertools');
$PAGE->set_title(format_string($straction.': '.$tracker->name));
//$PAGE->navbar->add($straction);

// Security.
require_course_login($course->id, true, $cm);

$capability = '';
switch($action) {
    case 'comply' :
                    $capability = 'report/trackertools:report';
                    $actionform = 'report_trackertools_comply_form';
                    break;
    case 'fieldcomply' :
                    $capability = 'report/trackertools:report';
                    $actionform = 'report_trackertools_fieldcomply_form';
                    break;
    case 'usercomply' :
                    $capability = 'report/trackertools:report';
                    $actionform = 'report_trackertools_usercomply_form';
                    break;
    case 'checked' :
                    $capability = 'report/trackertools:report';
                    $actionform = 'report_trackertools_checked_form';
                    break;
    case 'create' :
                    $capability = 'report/trackertools:import';
                    $actionform = 'report_trackertools_create_form';
                    break;
                    
    case 'make' :
                    $capability = 'report/trackertools:import';
                    $actionform = 'report_trackertools_make_form';
                    break;
    case 'download' :
                    $capability = 'report/trackertools:export';
                    $actionform = 'report_trackertools_download_form';
                    break;
    case 'export' :
                    $capability = 'report/trackertools:export';
                    $actionform = 'report_trackertools_export_form';
                    break;
    case 'import' :
                    $capability = 'report/trackertools:import';
                    $actionform = 'report_trackertools_import_form';
                    break;
    case 'assigntasktable' :
                    $capability = 'report/trackertools:manage';
                    $actionform = 'report_trackertools_assigntask_form';
                    break;
    case 'deletetask' :
                    $capability = 'report/trackertools:manage';
                    $actionform = 'report_trackertools_deletetask_form';
                    break;
    case 'mailoptions' :
                    $capability = 'report/trackertools:manage';
                    $actionform = 'report_trackertools_mailoptions_form';
                    break;
    case 'setfield' :
                    $capability = 'report/trackertools:manage';
                    $actionform = 'report_trackertools_setfield_form';
                    break;
    case 'warning' :
                    $capability = 'report/trackertools:warning';
                    $actionform = 'report_trackertools_warning_form';
                    break;
}

require_capability($capability, $context);

// to be used forward
$eventdata = array('context' => $context, 'objectid' => $tracker->id, 'other' => array('action' => $action));

$mform = new $actionform(null, array('cmid'=>$id, 'tracker'=>$tracker));

// If a file has been uploaded, then process it
if ($mform->is_cancelled()) {
    redirect($returnurl);

} else if ($fromform = $mform->get_data()) {

    if($action == 'comply') {
        $records = report_trackertools_compliance_list($tracker, $fromform);
        $mform = new report_trackertools_checked_form(null, array('cmid'=>$id, 'tracker'=>$tracker, 'issues'=>$records));

    } elseif($action == 'fieldcomply') {
        $records = report_trackertools_field_compliance_list($tracker, $fromform);
        
        print_object($records);
        
        $complianceform = $fromform->fillstatus ? 'report_trackertools_noncompliant_form' : 'report_trackertools_checked_form';
        $mform = new $complianceform(null, array('cmid'=>$id, 'tracker'=>$tracker, 'issues'=>$records));
        
    } elseif($action == 'usercomply') {        
        $records = report_trackertools_user_compliance_list($tracker, $fromform);
        $complianceform = $fromform->fillstatus ? 'report_trackertools_noncompliant_form' : 'report_trackertools_checked_form';
        $mform = new $complianceform(null, array('cmid'=>$id, 'tracker'=>$tracker, 'issues'=>$records));
        
    } elseif($action == 'checked') {
        $message = '';
        if($fromform->mailtouser || $fromform->mailtodev) {
            $issues = array();
            $issuedata = data_submitted()->issues;

            foreach($issuedata as $i => $v) {
                if(!$v) {
                    unset($issuedata[$i]);
                }
            }
            if($issuedata) {
                $issues = $DB->get_records_list('tracker_issue', 'id', array_keys($issuedata), 'summary', 'id, reportedby, assignedto, summary');
            }
            $count = report_trackertools_warning_issues($course, $tracker, $fromform, $issues);
            // Trigger a report event.
            $eventdata['other']['count'] = $count;
            $event = \report_trackertools\event\report_sent::create($eventdata);
            $event->trigger();
            
            $message = get_string('warnedissues', 'report_trackertools', $count);
            if(!$count) {
                redirect($returnurl, $message, null, \core\output\notification::NOTIFY_ERROR);
            } 
        }
        redirect($returnurl, $message);        

    } elseif($action == 'create') {
        $mform = new report_trackertools_make_form(null, array('cmid'=>$id, 'tracker'=>$tracker));
        
    } elseif($action == 'make') { 
        if($fromform->confirm) {
            $count = report_trackertools_create_issues($course, $tracker, $fromform);
            // Trigger a report event.
            $eventdata['other']['count'] = $count;
            $event = \report_trackertools\event\report_created::create($eventdata);
            $event->trigger();

            $message = get_string('createdissues', 'report_trackertools', $count);
            if(!$count) {
                redirect($returnurl, $message, null, \core\output\notification::NOTIFY_ERROR);
            }
        }
        redirect($returnurl, $message);
        
    } elseif($action == 'download') { 
        // Process the data and show download links.
        $message = report_trackertools_download_files($course, $tracker, $fromform);
        redirect($returnurl, $message);
        
    } elseif($action == 'export') {
        $message = report_trackertools_export_issues($course, $tracker, $fromform);
        // Trigger a report event.
        $event = \report_trackertools\event\report_download::create($eventdata);
        $event->trigger();
        die;        
        
    } elseif($action == 'import') {
        require_once($CFG->libdir.'/csvlib.class.php');     
        // Large files are likely to take their time and memory. Let PHP know
        // that we'll take longer, and that the process should be recycled soon
        // to free up memory.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);

        $iid = csv_import_reader::get_new_iid('report_trackertools_import_issues');
        $cir = new csv_import_reader($iid, 'report_trackertools_import_issues');

        $filecontent = $mform->get_file_content('recordsfile');
        $readcount = $cir->load_csv_content($filecontent, $fromform->encoding, $fromform->separator);
        if (empty($readcount)) {
            //show meaningful error notice
            $line = strstr($filecontent, "\n", true);
            $line2 = '';
            if($p = strpos($filecontent, "\n", (strlen($line) + 2))) {
                $line2 = substr($filecontent, strlen($line) + 1,  $p - strlen($line));
            }
            $line = $OUTPUT->box($line.'<br /><br />'.$line2.'<br />', 'csverror alert-error');
            unset($filecontent);
            notice($line.$cir->get_error(), $returnurl);
        } else {
            unset($filecontent);
            
            list($fixed, $optional) = $mform->get_import_export_columns();
            
            if(!$message = report_trackertools_import_check_columns($tracker, $fixed, $cir)) {
            
                $count = report_trackertools_import_issues($course, $tracker, $fromform, $cir, ($fixed + $optional));
                $message = get_string('importedissues', 'report_trackertools', $count);
                // Trigger a report event.
                $eventdata['other']['count'] = $count;
                $event = \report_trackertools\event\report_created::create($eventdata);
                $event->trigger();
            }
        }
        redirect($returnurl, $message);

    } elseif($action == 'assigntasktable') {
        $result = report_trackertools_assigntask($tracker, $fromform);
        if($result > 0) {
            // Trigger a report event.
            $eventdata['other']['query'] = $fromform->query;
            $eventdata['other']['userid'] = $fromform->user;
            $event = \report_trackertools\event\report_taskassign::create($eventdata);
            $event->trigger();
            $message = get_string('taskassigned', 'report_trackertools');
            report_trackertools_issue_assignation($tracker, $fromform->query, $fromform->user);
        
            $baseurl->param('a', 'assigntasktable');
            redirect($baseurl, $message);  
        }
    } elseif($action == 'deletetask') {
        if($fromform->confirm) {
            $message = '';
            if($DB->delete_records('report_trackertools_devq', array('id'=>$fromform->confirm))) {
                $eventdata['other']['query'] = $fromform->query;
                $eventdata['other']['userid'] = $fromform->user;
                $event = \report_trackertools\event\report_taskremove::create($eventdata);
                $event->trigger();
                $message = get_string('taskdeleted', 'report_trackertools');
            }
            $baseurl->param('a', 'assigntasktable');
            redirect($baseurl, $message);
        }
    } elseif($action == 'mailoptions') {
        $message = report_trackertools_mailoptions($course, $tracker, $fromform);
        // Trigger a report event.
        $eventdata['other']['count'] = 'users';
        $event = \report_trackertools\event\report_updated::create($eventdata);
        $event->trigger();
        
        redirect($returnurl, $message);
        
    } elseif($action == 'setfield') {
        $message = report_trackertools_setfield_issues($course, $tracker, $fromform);
        // Trigger a report event.
        $eventdata['other']['count'] = 'N fields';
        $event = \report_trackertools\event\report_updated::create($eventdata);
        $event->trigger();
        
        redirect($returnurl, $message);

    } elseif($action == 'warning') {
        list($issuewhere, $params) = report_trackertools_issue_where_sql($tracker->id, $fromform, '');
        $issues = $DB->get_records_select('tracker_issue', $issuewhere, $params, 'summary', 'id, reportedby, assignedto, summary, status');
        $count = report_trackertools_warning_issues($course, $tracker, $fromform, $issues);
        
        // Trigger a report event.
        $eventdata['other']['count'] = $count;
        $event = \report_trackertools\event\report_sent::create($eventdata);
        $event->trigger();
        
        $message = get_string('warnedissues', 'report_trackertools', $count);
        if(!$count) {
            redirect($returnurl, $message, null, \core\output\notification::NOTIFY_ERROR);
        }
        
        redirect($returnurl, $message);
    }

}

// Trigger a report viewed event.
$event = \report_trackertools\event\report_viewed::create($eventdata);
$event->trigger();



/// Print the form
echo $OUTPUT->header();
echo $OUTPUT->heading_with_help($straction, $action, 'report_trackertools');
$mform ->display();
echo $OUTPUT->footer();
