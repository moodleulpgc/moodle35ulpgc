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
 * Prints a particular instance of examregistrar
 *
 * Display will depend on format parameter and user capabilities.
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$e  = optional_param('e', 0, PARAM_INT);  // examregistrar instance ID - it should be named as the first character of the module
$examcm  = optional_param('ex', 0, PARAM_INT);  //

if($examcm) {
        $cm         = get_coursemodule_from_id('exam', $examcm, 0, false, MUST_EXIST);
        $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $defaulter  = get_config('examregistrar', 'defaultregistrar');
        $examregistrar  = $DB->get_record('examregistrar', array('id' => $defaulter->instance), '*', MUST_EXIST);
} else {
    if ($id) {
        $cm         = get_coursemodule_from_id('examregistrar', $id, 0, false, MUST_EXIST);
        $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $examregistrar  = $DB->get_record('examregistrar', array('id' => $cm->instance), '*', MUST_EXIST);
    } elseif ($e) {
        $examregistrar  = $DB->get_record('examregistrar', array('id' => $e), '*', MUST_EXIST);
        $course     = $DB->get_record('course', array('id' => $examregistrar->course), '*', MUST_EXIST);
        $cm         = get_coursemodule_from_instance('examregistrar', $examregistrar->id, $course->id, false, MUST_EXIST);
    } else {
        error('You must specify a course_module ID or an instance ID');
    }
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
$baseurl = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cm->id,'tab'=>'view'));

$examregprimaryid = examregistrar_get_primaryid($examregistrar);
$examregistrar->context = $context;
$examregistrar->examregprimaryid = $examregprimaryid;
/// Print the page header

$PAGE->set_url('/mod/examregistrar/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($examregistrar->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
$output = $PAGE->get_renderer('mod_examregistrar');
// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('examregistrar-'.$somevar);


/// check permissions

$canview = has_any_capability(array('mod/examregistrar:view', 'mod/examregistrar:viewall'), $context);
$canbook = has_any_capability(array('mod/examregistrar:book', 'mod/examregistrar:bookothers'), $context);
$canreview = has_any_capability(array('mod/examregistrar:submit', 'mod/examregistrar:review'), $context);
$canprintexams = has_any_capability(array('mod/examregistrar:download', 'mod/examregistrar:beroomstaff'), $context);
$canprintrooms = has_any_capability(array('mod/examregistrar:download', 'mod/examregistrar:beroomstaff'), $context);

$caneditelements = has_capability('mod/examregistrar:editelements',$context);
$canmanageperiods = has_capability('mod/examregistrar:manageperiods',$context);
$canmanageexams = has_capability('mod/examregistrar:manageexams',$context);
$canmanagelocations = has_capability('mod/examregistrar:managelocations',$context);
$canmanageseats = has_capability('mod/examregistrar:manageseats',$context);
$canmanage = $caneditelements || $canmanageperiods || $canmanageexams || $canmanagelocations;

$tab  = optional_param('tab', '', PARAM_ALPHANUMEXT);
// calculate default tab if not set
if(!$tab) {
    $tab = 'view';
    if(($examregistrar->workmode == EXAMREGISTRAR_MODE_REVIEW) && $canreview) {
        if($canreview) {
            $tab = 'review';
        } elseif($canbook) {
            $tab = 'booking';
        }
    } elseif($examregistrar->workmode == EXAMREGISTRAR_MODE_PRINT){
        if($canmanageseats) {
            $tab = 'session';
        } else  {
            $tab='printexams';
        }
    } elseif($examregistrar->workmode == EXAMREGISTRAR_MODE_REGISTRAR){
        if($canmanageseats) {
            $tab = 'session';
        } elseif($canmanage) {
            $tab = 'manage';
        } else {
        $tab='printexams';
        }
    }
}

$baseurl = new moodle_url('/mod/examregistrar/view.php', array('id' => $cm->id, 'tab'=>$tab));
if($tab != 'view') {
    $PAGE->navbar->add(get_string($tab, 'examregistrar'), $baseurl);
}

///////////////////////////////////////////////////////////////////////////////////
    /// process forms data

    $period   = optional_param('period', '', PARAM_INT);
    $session   = optional_param('session', '', PARAM_INT);
    $programme   = optional_param('programme', '', PARAM_ALPHANUMEXT);
    $courseid   = optional_param('course', 0, PARAM_INT);
    $userid   = optional_param('user', $USER->id, PARAM_INT);

//      print_object($_GET);
//      print_object($_POST);
//      print_object("  ---- GET POST data ---- ");


    $action = optional_param('action', '',  PARAM_ALPHANUMEXT);
    $examfid = optional_param('exam', '',  PARAM_INT);

if($action == 'response_files' && $examfid) {
    require_once($CFG->dirroot."/mod/examregistrar/manage/manage_forms.php");
    $data = new stdClass();
    $data->id = $cm->id;
    $data->tab = 'view';
    $data->session = $session;
    $data->period = $period;
    $data->action = $action;
    $data->area = 'responses';
    $data->examfile = $examfid;
    $ccontext = context_course::instance($cm->course);
    $options = array('subdirs'=>0, 'maxbytes'=>$CFG->maxbytes, 'maxfiles'=>-1, 'accepted_types'=>'*');
    file_prepare_standard_filemanager($data, 'files', $options, $ccontext, 'mod_examregistrar', $data->area, $examfid);
    $mform = new examregistrar_files_form(null, array('data'=>$data, 'options'=>$options));

    if (!$mform->is_cancelled()) {
        if ($formdata = $mform->get_data()) {
            $formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $ccontext, 'mod_examregistrar', $data->area, $examfid);
                print_object($formdata);
            if(isset($formdata->files) && $formdata->files) {
                $DB->set_field('examregistrar_examfiles', 'taken', 1, array('id'=>$examfid));
            }
            add_to_log($course->id, 'examregistrar', 'edit exam response files', 'view.php?id='.$cm->id, $examregistrar->name, $cm->id);
        } elseif(!$formdata) {
            echo $output->header();
            include_once('tabs.php');

            $examname = '';
            if($examfid) {
                $sql = "SELECT ef.id, c.shortname, c.fullname, e.programme, e.examsession, es.name, es.idnumber, ep.name AS periodname, ep.idnumber AS periodidnumber
                        FROM {examregistrar_examfiles} ef
                        JOIN {examregistrar_exams} e ON e.id = ef.examid AND e.visible = 1
                        JOIN {course} c ON c.id = e.courseid
                        JOIN {examregistrar_examsessions} s ON e.examregid = s.examregid AND e.examsession = s.id
                        JOIN {examregistrar_elements} es ON es.examregid = s.examregid AND es.type = 'examsessionitem' AND s.examsession = es.id
                        JOIN {examregistrar_periods} p ON e.examregid = p.examregid AND e.period = p.id
                        JOIN {examregistrar_elements} ep ON ep.examregid = p.examregid AND ep.type = 'perioditem' AND p.period = ep.id
                        WHERE ef.id = :id ";

                $exam = $DB->get_record_sql($sql, array('id'=>$examfid), MUST_EXIST);
                $periodname = $output->formatted_name($exam->periodname, $exam->periodidnumber);
                $sessionname = $output->formatted_name($exam->name, $exam->idnumber);
                $examname = $exam->programme.'-'.$exam->shortname.' '.$periodname.'; '.$sessionname;
            }


            echo $output->heading($examname, 3, 'main');
            echo $output->container('', 'clearfix');
            echo $OUTPUT->heading(get_string('examresponsefiles',  'examregistrar'), 4, 'main');
            echo $OUTPUT->box_start('generalbox foldertree');
            $mform->display();
            echo $OUTPUT->box_end();
            echo $OUTPUT->footer();
            die();
        }
    }
}
//////////////////////////////////////////////////////////////////////////////////


// Output starts here
echo $output->header();
include_once('tabs.php');


if($tab == 'view') {
    echo $output->print_exams_courses_view($examregistrar, $cm, $course, $context, $baseurl);
} elseif($tab == 'booking') {
    include_once('booking.php');
} elseif($tab == 'review') {
    include_once('review.php');
} elseif($tab == 'printexams') {
    include_once('printexams.php');
} elseif($tab == 'printrooms') {
    include_once('printrooms.php');
} elseif($tab == 'session') {
    include_once('session.php');
}

/// Display the examregistrar
$eventdata = array();
$eventdata['objectid'] = $examregistrar->id;
$eventdata['context'] = $context;
$eventdata['userid'] = $USER->id;
$eventdata['other'] = array();
$eventdata['other']['tab'] = $tab;

$event = \mod_examregistrar\event\course_module_viewed::create($eventdata);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->trigger();

// Finish the page
echo $output->footer();