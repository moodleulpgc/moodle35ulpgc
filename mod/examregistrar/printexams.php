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
 * Displays the interface for download & printing exams (indicating rooms)
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// this file cannot be used alone, int must be included in a page-displaying script

defined('MOODLE_INTERNAL') || die;

require_capability('mod/examregistrar:download',$context);

$baseurl = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cm->id,'tab'=>'printexams'));
$tab = 'printexams';

/*
    Lista de sedes/aulas por examsession : menu for selecting session default = next session

    filtrar por degree, examen, sede

    sedes aulas: user is assigned as staffer in a room show those rooms ,
    user is coordinator: show all rooms of degree, show appendix of additional exams


*/
/*
print_object($_POST);
print_object($_GET);
*/



$period   = optional_param('period', 0, PARAM_INT);
$session   = optional_param('session', 0, PARAM_INT);
$bookedsite   = optional_param('venue', 0, PARAM_INT);
$programme   = optional_param('programme', '', PARAM_ALPHANUMEXT);
$courseid   = optional_param('course', '', PARAM_ALPHANUMEXT);
$room   = optional_param('room', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);  // complex action not managed by edit
$examfid = optional_param('examf', 0,  PARAM_INT);

$now = time();
//$now = strtotime('4 may 2014') + 3605;

if(!$period) {
    $periods = examregistrar_current_periods($examregistrar, $now);
    if($periods) {
        $period = reset($periods);
        $period = $period->id;
    }
}

if(!$session) {
    $session = examregistrar_next_sessionid($examregistrar, $now, false, $period);
}

if(!$bookedsite) {
    $bookedsite = examregistrar_user_venueid($examregistrar, $USER->id);
}

$term   = optional_param('term', 0, PARAM_INT);
$searchname = optional_param('searchname', '', PARAM_TEXT);
$searchid = optional_param('searchid', '', PARAM_INT);
$sort = optional_param('sorting', 'shortname', PARAM_ALPHANUM);
$order = optional_param('order', 'ASC', PARAM_ALPHANUM);
$baseparams = array('id'=>$cm->id, 'tab'=>$tab);
$printparams = array('period'=>$period,
                        'session'=>$session,
                        'venue'=>$bookedsite,
                        'term'=>$term,
                        'searchname'=>$searchname,
                        'searchid'=>$searchid,
                        'programme'=>$programme,
                        'sorting'=>$sort,
                        'order'=>$order,
                        'user'=>$userid);

$printurl = new moodle_url($baseurl, $printparams);

$annuality =  examregistrar_get_annuality($examregistrar);

// check permissions
$canviewall = has_capability('mod/examregistrar:viewall', $context);

/// get session name & code
list($periodname, $periodidnumber) = examregistrar_get_namecodefromid($period, 'periods', 'period');
list($sessionname, $sessionidnumber) = examregistrar_get_namecodefromid($session, 'examsessions', 'examsession');
$listname = " $sessionname ($sessionidnumber) [$periodidnumber] ";
if($bookedsite) {
    list($venuename, $venueidnumber) = examregistrar_get_namecodefromid($bookedsite, 'locations', 'location');
    $listname .= " in $venuename ($venueidnumber)";
}

//////////////////////////////////////////////////////////////////////////////
// Process page actions
            // https://cv-etf.ulpgc.es/cv/ulpgctf18/mod/examregistrar/view.php?id=87&tab=session&session=11&venue=0&esort&rsort&action=response_files&examf=620
            // sesion responses
            //https://cv-etf.ulpgc.es/cv/ulpgctf18/mod/examregistrar/view.php?id=87&tab=session&session=11&venue=0&esort&rsort&action=session_files&area=sessionresponses
            // session control
            //https://cv-etf.ulpgc.es/cv/ulpgctf18/mod/examregistrar/view.php?id=87&tab=session&session=11&venue=0&esort&rsort&action=session_files&area=sessioncontrol

/*
print_object("action = $action");
print_object("session = $session");            
print_object("bookedsite = $bookedsite");            
print_object("examfid = $examfid");            
*/            
            
if($action && $session && $examfid) {
    require_once($CFG->dirroot."/mod/examregistrar/manage/manage_forms.php");

    // check parameters with database items
    $examfile = $DB->get_record('examregistrar_examfiles', array('id'=>$examfid), '*', MUST_EXIST);
    $params = array('period'=>$period, 'session'=>$session, 'bookedsite'=>$bookedsite,
                'programme'=>$programme);
    $courseid = $DB->get_field('examregistrar_exams', 'courseid', array('id'=>$examfile->examid, 'examregid'=>$examregistrar->id), MUST_EXIST);
    $params['courseid'] = $courseid;
    // get exam instance
    $allocations = examregistrar_get_examallocations_byexam($params, array($courseid));
    $exam = reset($allocations);
    
    $data = new stdClass();
    $data->id = $cm->id;
    $data->tab = 'printexams';
    $data->courseid = $courseid;
    $data->session = $session;
    $data->period = $period;
    $data->bookedsite = $bookedsite;
    $data->room = $room;
    $data->action = $action;
    $data->examfile = $examfid;
    $data->examid = $examfile->examid;
    $data->taken = $examfile->taken;
    $data->users = $exam->set_users($bookedsite);
    $data->rooms =  $exam->get_room_allocations($bookedsite);
    
    $ccontext = context_course::instance($cm->course);
    $options = array('subdirs'=>2, 'maxbytes'=>$CFG->maxbytes, 'maxfiles'=>-1, 'accepted_types'=>'*');
    $display = false;
    $event = false;
    
    /// prepare event log
    $eventdata = array();
    $eventdata['objectid'] = $examregistrar->id;
    $eventdata['context'] = $context;
    $eventdata['userid'] = $USER->id;
    $eventdata['other'] = array();
    $eventdata['other']['tab'] = $tab;
    $eventdata['other']['examregid'] = $examregistrar->examregprimaryid;
    $eventdata['other']['examid'] = $examfile->examid;
    $eventdata['other']['bookedsite'] = $bookedsite;
    $eventdata['other']['room'] = $room;
    $eventdata['other']['files'] = array();
    $fs = get_file_storage();
    
    if($action == 'exam_responses_upload') {
        file_prepare_standard_filemanager($data, 'files', $options, $ccontext, 'mod_examregistrar', 'examresponses', $examfid);
        $data->canreview = has_capability('mod/examregistrar:reviewtaken',$context);
        $mform = new examregistrar_examresponses_form(null, array('data'=>$data, 'options'=>$options));
        //$mform->set_data();
        if (!$mform->is_cancelled()) {
            if ($formdata = $mform->get_data()) {
                // process form, do NOT display
                $formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $ccontext, 'mod_examregistrar', 'examresponses', $data->examfile);
                if($formdata->files) {
                    $message[] = examregistrar_save_attendance_answers($formdata, $options, $ccontext->id, $eventdata); 
                }
                
                
                if($formdata->userdata) {
                    $message[] = examregistrar_save_attendance_userdata($formdata, $eventdata); 
                    
                
                }
            } elseif(!$formdata) {
                $display = true;
            }
        }
    }
    
    if($action == 'exam_responses_accepted') {
    
        $mform = new examregistrar_confirmresponses_form(null, array('data'=>$data, 'options'=>$options));
    
        if (!$mform->is_cancelled()) {
            if ($formdata = $mform->get_data()) {
                // process form, do NOT display
                
                if($formdata->acceptfiles) {
                    // move files to new area
                    $filename = $exam->shortname.'_'.$venueidnumber;
                    examregistrar_confirm_attendance_files($formdata, $filename, $contextid, $eventdata);
                }
                if($formdata->completed) {
                    $record = $DB->get_record('examregistrar_responses', array(), 'id, completed', MUST_EXIST);
                    $record->completed = $formdata->completed;
                    $record->acceptingid = $USER->id;
                    $DB->update_record('examregistrar_responses', $record);
                }
                
                
            } elseif(!$formdata) {
                $display = true;
            }
        }
    
    
    }
    
   
    // display de forms, if needed
    if($display) {
        echo $output->heading(get_string('examsforsession', 'examregistrar', $listname), 3, 'main');
        echo $output->container('', 'clearfix');
        $examname = $exam->get_exam_name(false, true, true); 
        echo $OUTPUT->heading($examname, 3, 'main');
        echo $OUTPUT->box_start('generalbox foldertree');
        $mform->display();
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        die();
    }
}

//////////////////////////////////////////////////////////////////////////////
// Start main output logic

$courses = examregistrar_get_user_courses($examregistrar, $course, $printparams, array('mod/examregistrar:submit', 'mod/examregistrar:download'), $canviewall);

echo $output->exams_item_selection_form($examregistrar, $course, $printurl, $printparams, 'period, session, venue');
if($canviewall) {
    echo $output->exams_courses_selector_form($examregistrar, $course, $printurl, $printparams);
}

echo $output->heading(get_string('examsforsession', 'examregistrar', $listname));

if($exams = examregistrar_get_session_exams($session, $bookedsite, '', true, true)) {

    $booked = 0;
    $allocated = 0;
    foreach($exams as $exam) {
        if($exam->booked) {
            $booked += 1;
        }
        if($exam->allocated) {
            $allocated += 1;
        }
    }

    // check single room venue
    if($bookedsite && $booked && $canviewall && $room = examregistrar_is_venue_single_room($bookedsite)) {
        echo $output->container_start(' clearfix ');
        $url = new moodle_url('/mod/examregistrar/download.php', $baseurl->params(array()) + array('down'=>'printsingleroompdf', 'session'=>$session, 'venue'=>$bookedsite));
        echo $output->container($output->single_button($url, get_string('printuserspdf', 'examregistrar'), 'post', array('class'=>' singlelinebutton ')), ' allocatedroomheaderright ');
        echo $output->container_end();
        echo $output->container_start(' clearfix ');
        $url->param('down', 'printsingleroomfaxpdf');
        echo $output->container($output->single_button($url, get_string('printbinderpdf', 'examregistrar'), 'post', array('class'=>' singlelinebutton ')), ' allocatedroomheaderright ');
        echo $output->container_end();
    }

    $info = get_string('scheduledexams', 'examregistrar', count($exams)).'<br />';
    $info .= get_string('bookedexams', 'examregistrar', $booked).'<br />';
    $info .= get_string('allocatedexams', 'examregistrar', $allocated).'<br />';

    echo $output->box($info, 'generalbox');
}


$params = array('period'=>$period, 'session'=>$session, 'bookedsite'=>$bookedsite,
                'programme'=>$programme, 'course'=>$courseid);
               
$allocations = examregistrar_get_examallocations_byexam($params, array_keys($courses));

/// print button for download all
if(count($allocations) > 1) {

}

/// now print the list of rooms and exams
foreach($allocations as $allocexam) {
    echo $output->listdisplay_allocatedexam($allocexam, $course, $baseurl, $bookedsite);
}




























