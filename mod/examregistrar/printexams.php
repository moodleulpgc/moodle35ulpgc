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

//print_object($_POST);
//print_object($_GET);



$SESSION->nameformat = 'lastname';
$period   = optional_param('period', 0, PARAM_INT);
$session   = optional_param('session', 0, PARAM_INT);
$bookedsite   = optional_param('venue', 0, PARAM_INT);
$programme   = optional_param('programme', '', PARAM_ALPHANUMEXT);
$courseid   = optional_param('course', '', PARAM_ALPHANUMEXT);

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

//////////////////////////////////////////////////////////////////////////////
// Process page actions
            // https://cv-etf.ulpgc.es/cv/ulpgctf18/mod/examregistrar/view.php?id=87&tab=session&session=11&venue=0&esort&rsort&action=response_files&examf=620
            // sesion responses
            //https://cv-etf.ulpgc.es/cv/ulpgctf18/mod/examregistrar/view.php?id=87&tab=session&session=11&venue=0&esort&rsort&action=session_files&area=sessionresponses
            // session control
            //https://cv-etf.ulpgc.es/cv/ulpgctf18/mod/examregistrar/view.php?id=87&tab=session&session=11&venue=0&esort&rsort&action=session_files&area=sessioncontrol

$room   = optional_param('room', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);  // complex action not managed by edit
$examfid = optional_param('examfile', 0,  PARAM_INT);

if($room) {
    list($roomname, $roomidnumber) = examregistrar_get_namecodefromid($room, 'locations', 'location');
}
$display = false;

if($action && $session) {
    require_once($CFG->dirroot."/mod/examregistrar/manage/manage_forms.php");
    
    $data = (object)$printurl->params();
    $data->bookedsite = $bookedsite;
    $data->room = $room;
    $data->action = $action;
    $data->canreview = has_capability('mod/examregistrar:reviewtaken', $context);
    
    /// prepare event log
    $eventdata = array();
    //$eventdata['objectid'] = $examregistrar->id;
    $eventdata['context'] = $context;
    $eventdata['userid'] = $USER->id;
    $eventdata['other'] = array();
    $eventdata['other']['tab'] = $tab;
    $eventdata['other']['examregid'] = $examregistrar->examregprimaryid;
    $eventdata['other']['session'] = $session;
    $eventdata['other']['bookedsite'] = $bookedsite;

    $options = array('subdirs'=>1, 'maxbytes'=>$CFG->maxbytes, 'maxfiles'=>-1, 'accepted_types'=>'.pdf');
    $fs = get_file_storage();

    
    if($examfid) {
        // check parameters with database items
        $examfile = $DB->get_record('examregistrar_examfiles', array('id'=>$examfid), '*', MUST_EXIST);
        $data->examfile = $examfid;
        $data->examid = $examfile->examid;
        $data->examtaken = $examfile->taken;
        $eventdata['other']['examid'] = $examfile->examid;
        
        $params = array('period'=>$period, 'session'=>$session, 'bookedsite'=>$bookedsite,
                    'programme'=>$programme);
        $data->courseid = $DB->get_field('examregistrar_exams', 'courseid', array('id'=>$examfile->examid, 'examregid'=>$examregistrar->id), MUST_EXIST);
        $params['courseid'] = $data->courseid;
        // get exam instance
        $allocations = examregistrar_get_examallocations_byexam($params, array($data->courseid));
        $exam = reset($allocations);

        $data->users = $exam->set_users($bookedsite);
        $data->rooms =  $exam->get_room_allocations($bookedsite, 0, true); // with responses data
        
        $ccontext = context_course::instance($data->courseid);
        
        $params = array('examsession'   => $data->session,
                        'examid'        => $data->examid, 
                        'roomid'        => $data->room, 
                        'examfile'      => $data->examfile, 
                        );
        $response = $DB->get_record('examregistrar_responses', $params);
        if(!isset($response->id)) {
            $data->responseid = $DB->insert_record('examregistrar_responses', $params);
            $response = (object)$params;
            $response->id = $data->responseid;
        } else {
            $data->responseid = $response->id;
        }
        
        $message = '';
        
        if($action == 'exam_responses_upload') {
            $data = file_prepare_standard_filemanager($data, 'files', $options, $ccontext, 'mod_examregistrar', 'examresponses', $data->responseid);
            $mform = new examregistrar_examresponses_form($printurl, array('data'=>$data, 'options'=>$options));
            $filenamager = new stdClass();
            $filenamager->files_filemanager = $data->files_filemanager;
            $mform->set_data($filenamager);
            if (!$mform->is_cancelled()) {
                if ($formdata = $mform->get_data()) {
 //                   print_object($formdata);
   //               print_object("------------- formdata ------------------");
                
                    // process form, do NOT display
                    $formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $ccontext, 'mod_examregistrar', 'examresponses', $data->responseid);
                    if($formdata->files) {
                        if($files = $fs->get_directory_files($ccontext->id, 'mod_examregistrar', 'examresponses', $formdata->responseid, '/', true, false)) {
                            foreach($files as $key => $file) {
                                $files[$key] = $file->get_filename(); 
                            }
                            $eventdata['other']['files'] = implode(', ', $files);
                            $eventdata['other']['room'] = $formdata->room;
                            $event = \mod_examregistrar\event\responses_uploaded::create($eventdata);
                            $event->trigger();

                            $formdata->files = count($files); 
                            $DB->set_field('examregistrar_responses', 'numfiles', $formdata->files, array('id'=>$formdata->responseid));
                            \core\notification::success(get_string('savedresponsefiles', 'examregistrar', $formdata->files));
                        }                    
                    }

                    if(isset($formdata->roomstatus) || isset($formdata->showing)) {
                        if($saved = examregistrar_save_attendance_responsedata($formdata, $ccontext->id, $eventdata)) {
                            \core\notification::success(get_string('savedroomsdata', 'examregistrar', $saved));
                        }
                    }
                    
                    if($formdata->loadattendance) {
                        if($saved = examregistrar_save_attendance_userdata($formdata)) {
                            \core\notification::success(get_string('saveduserdata', 'examregistrar', $saved));
                            
                            $eventdata['other']['users'] = $saved;
                            unset($eventdata['other']['room']);
                            $event = \mod_examregistrar\event\attendance_loaded::create($eventdata);
                            $event->trigger();
                        }
                    }
                } elseif(!$formdata) {
                    $display = true;
                }
            }
        }
        
        if($action == 'exam_responses_review') {
    
            if(!$room) {
                $response->responseid = $response->id;
                $response->name = get_string('globaldata', 'examregistrar');
                $data->rooms[0] = $response;
            }
        
            $mform = new examregistrar_confirmresponses_form($printurl, array('data'=>$data, 'options'=>$options));
        
            if (!$mform->is_cancelled()) {
                if ($formdata = $mform->get_data()) {
            //        print_object($formdata);
            //      print_object("------------- formdata ------------------");

                    
                    if($formdata->loadattendance) {
                        if($saved = examregistrar_confirm_attendance_userdata($formdata)) {
                        
                            \core\notification::success(get_string('confirmedusersdata', 'examregistrar', $saved));
                            
                            $eventdata['other']['users'] = $saved;
                            unset($eventdata['other']['room']);
                            $event = \mod_examregistrar\event\attendance_approved::create($eventdata);
                            $event->trigger();
                        }
                    }
                    
                    if(isset($formdata->roomdata)) {
                        // some rooms checked
                        if($saved = examregistrar_confirm_attendance_roomdata($formdata, $exam->shortname, $ccontext->id, $context->id, $eventdata)) {
                            \core\notification::success(get_string('confirmedusersdata', 'examregistrar', $saved));
                        }
                    }
                } elseif(!$formdata) {
                    $display = true;
                }
            }
        }
        
        if($display) {
            $examname = $exam->get_exam_name(false, true, true);   
            $display = $OUTPUT->heading($examname, 3, 'main');
            $display .= $OUTPUT->heading($roomname, 4, 'main');
        }
        
    } elseif($room) {
        if($action == 'room_responses_upload') {
            /*
            $params = array('examsession'   => $data->session,
                            'bookedsite'    => $data->bookedsite, 
                            );
                            
                            print_object($params);
                            print_object($DB->get_records('examregistrar_session_rooms', $params));
            $sessionroom  = $DB->get_field('examregistrar_session_rooms', 'id', $params, MUST_EXIST);
            */
            $sessionroom =  (int)"{$data->session}0000{$data->bookedsite}";   
            $options['subdirs'] = 0;
            $data = file_prepare_standard_filemanager($data, 'files', $options, $context, 'mod_examregistrar', 'roomresponses', $sessionroom);
            $mform = new examregistrar_roomresponses_form($printurl, array('data'=>$data, 'options'=>$options));
            
            if (!$mform->is_cancelled()) {
                if ($formdata = $mform->get_data()) {
                    // process user input
                    $formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $context, 'mod_examregistrar', 'roomresponses', $sessionroom);
//                    print_object($formdata);
  //                print_object("------------- formdata ------------------");

                    if($formdata->loadattendance) {
                        if($saved = examregistrar_save_attendance_userdata($formdata, true)) {
                            \core\notification::success(get_string('saveduserdata', 'examregistrar', $saved));
                            
                            $eventdata['other']['users'] = $saved;
                            unset($eventdata['other']['room']);
                            $event = \mod_examregistrar\event\attendance_loaded::create($eventdata);
                            $event->trigger();
                        }
                    }
                    
                    if(isset($formdata->examattendance)) {
                        if($saved = examregistrar_save_venue_attendance_files($formdata, $context->id, $eventdata)) {
                            \core\notification::success(get_string('savedexamsdata', 'examregistrar', $saved));
                        }
                    }
                  
                
                } else{
                    $display = $OUTPUT->heading($roomname, 3, 'main');
                }
            }
        }
    }
}


//////////////////////////////////////////////////////////////////////////////
// Start main output logic

/// get session name & code
list($periodname, $periodidnumber) = examregistrar_get_namecodefromid($period, 'periods', 'period');
list($sessionname, $sessionidnumber) = examregistrar_get_namecodefromid($session, 'examsessions', 'examsession');
$listname = " $sessionname ($sessionidnumber) [$periodidnumber] ";
if($bookedsite) {
    list($venuename, $venueidnumber) = examregistrar_get_namecodefromid($bookedsite, 'locations', 'location');
    $listname .= " in $venuename ($venueidnumber)";
}
    
if($display) {
    // display the forms, if needed
    echo $output->heading(get_string('examsforsession', 'examregistrar', $listname), 3, 'main');
    echo $output->container('', 'clearfix');
    echo $display; 
    echo $OUTPUT->box_start('generalbox foldertree');
    $mform->display();
    echo $OUTPUT->box_end();
} else { 
    // examlist display

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

            if($room) {
                echo $output->container_start(' clearfix ');
                //$url = new moodle_url('/mod/examregistrar/view.php', $baseurl->params(array()) + array('down'=>'printsingleroompdf', 'session'=>$session, 'venue'=>$bookedsite));
                $printurl->params(array('action' => 'room_responses_upload', 'room' => $room));
                echo $output->container($output->single_button($printurl, get_string('responsesupload', 'examregistrar'), 'post', array('class'=>' singlelinebutton ')), ' allocatedroomheaderright ');
                echo $output->container_end();
            } 

            /*
            echo $output->container_start(' clearfix ');
            $url->param('down', 'printsingleroomfaxpdf');
            echo $output->container($output->single_button($url, get_string('printbinderpdf', 'examregistrar'), 'post', array('class'=>' singlelinebutton ')), ' allocatedroomheaderright ');
            echo $output->container_end();
            */
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
}



























