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
 * Manages complex examregistrar management actions
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/mod/examregistrar/locallib.php');
require_once($CFG->dirroot."/mod/examregistrar/manage/action_forms.php");
//require_once($CFG->dirroot."/mod/examregistrar/manage/manage_forms.php");
//require_once($CFG->dirroot."/mod/examregistrar/manage/manage_table.php");

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


//  print_object($_GET);
//  print_object("_GET -----------------");
//  print_object($_POST);
//  print_object("_POST -----------------");



$tab   = optional_param('tab', '', PARAM_ALPHANUMEXT);
$edit   = optional_param('edit', '', PARAM_ALPHANUMEXT);  // list/edit items
$action = required_param('action', PARAM_ALPHANUMEXT);  // complex action not managed by edit

if($edit) {
    $baseurl = new moodle_url('/mod/examregistrar/manage.php', array('id' => $cm->id, 'edit'=>$edit));
    $tab = 'manage';
} else {
    $baseurl = new moodle_url('/mod/examregistrar/view.php', array('id' => $cm->id, 'tab'=>'session'));
    $tab = 'session';
}
$actionurl = new moodle_url('/mod/examregistrar/manage/action.php', array('id' => $cm->id, 'edit'=>$edit, 'action'=>$action));


/// Set the page header
$PAGE->set_url($baseurl);
$PAGE->set_title(format_string($examregistrar->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
if($edit) {
    $PAGE->navbar->add(get_string($edit, 'examregistrar'), $baseurl);
} else {
    $PAGE->navbar->add(get_string($tab, 'examregistrar'), $baseurl);
}
    $PAGE->navbar->add(get_string($action, 'examregistrar'), null);

$examregprimaryid = examregistrar_get_primaryid($examregistrar);

/// check permissions
$caneditelements = has_capability('mod/examregistrar:editelements',$context);
$canmanageperiods = has_capability('mod/examregistrar:manageperiods',$context);
$canmanageexams = has_capability('mod/examregistrar:manageexams',$context);
$canmanagelocations = has_capability('mod/examregistrar:managelocations',$context);
$canmanageseats = has_capability('mod/examregistrar:manageseats',$context);
$canmanage = $caneditelements || $canmanageperiods || $canmanageexams || $canmanagelocations || $canmanageseats;

///////////////////////////////////////////////////////////////////////////////

/// redirects
$session = optional_param('examsession', 0, PARAM_INT);
if(!$session) {
    $session = optional_param('session', 0, PARAM_INT);
}
$bookedsite   = optional_param('venue', '', PARAM_INT);

$params = array('id'=>$cm->id, 'action' => $action, 'tab'=>$tab, 'session'=>$session, 'venue'=>$bookedsite);

if($action == 'sessionrooms' || $action == 'roomstaffers') {
    $url = new moodle_url('/mod/examregistrar/manage/assign'.$action.'.php', $params);  
    redirect($url);
}

/// define forms
    $examid   = optional_param('exam', '', PARAM_INT);
    $short   = optional_param('examshort', '', PARAM_ALPHANUMEXT);
    $formclass = 'examregistrar_'.$action.'_actionform';
    $mform = new $formclass(null, array('exreg' => $examregistrar, 'action'=>$action, 'id'=>$cm->id, 'edit'=>$edit, 'tab'=>$tab,
                                        'session'=>$session, 'venue'=>$bookedsite, 'exam'=>$examid, 'shortname'=>$short));

/// set forms data
    if($page = strstr($action, 'printoptions', true)) {
        $formdata = new stdClass;
        if($elements = $DB->get_records('examregistrar_printing', array('examregid'=>$examregprimaryid, 'page'=>$page))) {
            foreach($elements as $element) {
                $fieldname = $element->element;
                if($element->contentformat) {
                    $formdata->{'page_'.$fieldname}['text'] = $element->content;
                    $formdata->{'page_'.$fieldname}['format'] = $element->contentformat;
                } else {
                    $formdata->{'page_'.$fieldname} = $element->content;
                }
                $formdata->{$fieldname.'_visible'} = $element->visible;
            }
        }
        $mform->set_data($formdata);
    }
    if($action == 'addexamcall') {
        // we are adding based on existing element
        if($element = $DB->get_record('examregistrar_exams', array('id' => $examid))) {

            $select = 'SELECT MAX($callnum) ';
            $element->additional = 0;
            $element->bookedsite = $bookedsite;
            if($element->callnum < 0) {
                $element->callnum = abs($element->callnum);
                $element->additional = 1;
                $select = 'SELECT MIN($callnum) ';
            }

            $sql = "FROM {examregistrar_exams}
                    WHERE examregid = ? AND annuality = ? AND programme = ?
                            AND courseid = ? AND period = ? AND examscope ? ";
            $params = array($element->examregid, $element->annuality,
                            $element->programme, $element->courseid,
                            $element->period, $element->examscope);
            $callnum = $DB->get_field_sql($select.$sql, $params=null);
            if($element->additional) {
                $callnum -= 1;
            } else {
                $callnum += 1;
            }
            $element->callnum = $callnum;
            unset($element->id);
            $mform->set_data($element);
        }
    } elseif($action == 'configparams') {
        $config = null;
        $config = $DB->get_field('examregistrar', 'configdata', array('id'=>$examregprimaryid));
        $mform->set_data(unserialize(base64_decode($config)));
    }

/// process forms actions

    if ($mform->is_cancelled()) {
        redirect($baseurl);
    } elseif ($formdata = $mform->get_data()) {
        /// do action
        $message = '';
        if($page) {
            $element = new stdClass;
            $element->examregid = $examregprimaryid;
            $element->page = $page;
            $element->modifierid = $USER->id;
            $element->timemodified = time();
            foreach($formdata as $key => $data) {
                $fields = explode('_', $key);
                if($fields[0] == 'page' ) {
                    if(is_array($data)) {
                        $content = $data['text'];
                        $format =  $data['format'];
                    } else {
                        $content = $data;
                        $format = 0;
                    }
                    $element->element =  $fields[1];
                    $visible = 1;
                    if(isset($formdata->{$fields[1].'_visible'})) {
                        $visible = $formdata->{$fields[1].'_visible'};
                    }
                    if($record = $DB->get_record('examregistrar_printing', array('examregid'=>$examregprimaryid,
                                                                                'page'=>$page,
                                                                                'element'=>$element->element))) {
                        // elements exists, we are updating
                        $record->content = $content;
                        $record->contentformat = $format;
                        $record->visible = $visible;
                        $record->modifierid = $USER->id;
                        $record->timemodified = $element->timemodified;
                        $DB->update_record('examregistrar_printing', $record);
                    } else {
                        // element not exists, insert
                        $element->content = $content;
                        $element->contentformat = $format;
                        $element->visible = $visible;
                        $DB->insert_record('examregistrar_printing', $element);
                    }
                }
            }
        } elseif ($action == 'sessionrooms') {
            $DB->set_field('examregistrar_session_rooms', 'available', 0, array('examsession' => $formdata->examsession, 'bookedsite'=>$formdata->bookedsite));
            $record = new stdClass;
            $record->examsession = $formdata->examsession;
            $record->bookedsite = $formdata->bookedsite;
            $record->available = 1;
            foreach($formdata->assignedrooms as $roomid) {
                examregistrar_addupdate_sessionroom($formdata->examsession, $roomid, $formdata->bookedsite);
            }
        } elseif($action == 'stafffromexam') {
           $examsessions = optional_param_array('examsessions', 0, PARAM_INT);
           $remove = optional_param('remove', false, PARAM_BOOL);
           $role = optional_param('role', 0, PARAM_ALPHANUMEXT);
           $message = examregistrar_assignroomstaff_fromexam($examsessions, $bookedsite, $role, $remove);
        } elseif($action == 'seatstudent') {
            $room = optional_param('room', '', PARAM_INT);
            $userid = optional_param('userid', '', PARAM_INT);
            if($room && $userid) {
                $DB->set_field('examregistrar_session_seats', 'roomid', $room, array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'userid'=>$userid));
                examregistrar_update_additional_allocations($session, $bookedsite, $userid, $room);
            }
        } elseif($action == 'configparams') {
            $config = clone $formdata;
            $config = examregistrar_clean_config($config);
            if(!empty($config)) {
                $DB->set_field('examregistrar', 'configdata', base64_encode(serialize($config)), array('id' =>$examregprimaryid));
            }
        } elseif($action == 'addextracall' || $action == 'addextrasessioncall') {
            $examid = $formdata->exam;
            // we are adding based on existing exam
            if($exam = $DB->get_record('examregistrar_exams', array('id' => $examid))) {
                $sql = "FROM {examregistrar_exams}
                        WHERE examregid = ? AND annuality = ? AND programme = ?
                                AND courseid = ? AND period = ? AND examscope = ? ";
                $params = array($exam->examregid, $exam->annuality,
                                $exam->programme, $exam->courseid,
                                $exam->period, $exam->examscope);
                // first check if already exist an extracall for this session, and use it
                if(!$extraexamid = $DB->get_field_sql('SELECT id '.$sql.' AND examsession = ? AND callnum < 0 ',
                                                array_merge($params, array($formdata->examsession)))) {
                    /// insert new examid for new examcall in this session,
                    $callnum = $DB->get_field_sql('SELECT MIN(callnum) '.$sql, $params);
                    $callnum -= 1;
                    if($callnum == 0) {
                        $callnum = -1;
                    }

                    $exam->callnum = $callnum;
                    $exam->examsession = $formdata->examsession;
                    unset($exam->id);
                    $exam->component = '';
                    $exam->modifierid = $USER->id;
                    $exam->timemodified = time();
                    if($extraexamid = $DB->insert_record('examregistrar_exams', $exam)) {
                        $eventdata = array();
                        $eventdata['objectid'] = $extraexamid;
                        $eventdata['context'] = $context;
                        $eventdata['other'] = array();
                        $eventdata['other']['edit'] = 'exams';
                        \mod_examregistrar\event\manage_created::created($eventdata, 'examregistrar_'.$upload);
                        $event->trigger();
                    }
                    
                    
                    
                } else {
                    $exam = $exam = $DB->get_record('examregistrar_exams', array('id' => $extraexamid));
                }
                if($extraexamid) {
                    // if added extracall, need an  examfile
                    if(isset($formdata->generateexamfile) && $formdata->generateexamfile && isset($formdata->examfile) && $formdata->examfile) {
                        $examcourse = $DB->get_record('course', array('id'=>$exam->courseid), 'id, fullname, shortname, idnumber, category', MUST_EXIST);
                        $sourceef = $DB->get_record('examregistrar_examfiles', array('id'=>$formdata->examfile), '*', MUST_EXIST);
                        $examfile = new stdClass;
                        $now = time();
                        $examfile->examid = $extraexamid;
                        $examfile->status = EXAM_STATUS_APPROVED;
                        $examfile->attempt = 1;
                        $examfile->name =  get_string('specialexam', 'examregistrar').' '.abs($exam->callnum); //$formdata->name ? $formdata->name : get_string('attempt', 'examregistrar').'&nbsp;'.$examfile->attempt;
                        $examfile->idnumber = examregistrar_examfile_idnumber($exam, $examcourse->idnumber);
                        $examfile->userid = $USER->id;
                        $examfile->timecreated = $now;
                        $examfile->timeapproved = $now;
                        $examfile->timemodified = $now;

                        if(!$newid = $DB->get_field('examregistrar_examfiles', 'id', array('examid'=>$examfile->examid, 'status'=>$examfile->status, 'attempt'=>$examfile->attempt))) {
                            if($newid = $DB->insert_record('examregistrar_examfiles', $examfile)) {
                                $eventdata = array();
                                $eventdata['objectid'] = $newid;
                                $eventdata['context'] = $context;
                                $eventdata['other'] = array();
                                $eventdata['other']['examregid'] = $examregistrar->id;
                                $eventdata['other']['examid'] = $examfile->examid;
                                $eventdata['other']['attempt'] = $examfile->attempt;
                                $eventdata['other']['idnumber'] = $examfile->idnumber;
                                $eventdata['other']['status'] = $examfile->status;
                                $event = \mod_examregistrar\event\examfile_created::create($eventdata);
                                $event->trigger();                            
                            }
                        }
                        if($newid) {
                            // add PDF files
                            foreach(array('/', '/answers/') as $filepath) {
                                $fs = get_file_storage();
                                $fcontext = context_course::instance($examcourse->id);
                                $filerecord = array('contextid' => $fcontext->id,
                                                    'component' => 'mod_examregistrar',
                                                    'filearea'  => 'exam',
                                                    'filepath'  => $filepath,
                                                    'itemid'    =>  $sourceef->id,
                                                    );
                                $files = $fs->get_directory_files($filerecord['contextid'], $filerecord['component'],
                                                                $filerecord['filearea'], $filerecord['itemid'], $filerecord['filepath'],
                                                                false, false);
                                $file = reset($files);
                                // examregistrar_generate_extracall_examfile($exam, $file, $filepath, $examfile, $examcourse)
                                
                                $template = $file->copy_content_to_temp();
                                $newfile = $template.'_new';

                                $programme = $exam->programme.' - '.$DB->get_field('course_categories', 'name', array('id'=>$examcourse->category));
                                $coursename = $examcourse->shortname.' - '. $examcourse->fullname;
                                list($name, $idnumber) = examregistrar_get_namecodefromid($exam->period, 'periods');
                                $period = " Conv. RESERVA ($idnumber) ";
                                list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examscope);
                                $scope = $name;
                                list($name, $idnumber) = examregistrar_get_namecodefromid($exam->annuality);
                                $annuality = $name;
                                $examname = $period.', '.$scope.' ('.$annuality.')';

                                require_once($CFG->dirroot.'/mod/examregistrar/classes/pdf_fpdi.class.php');

                                // initiate FPDI
                                $pdf = new examregistrar_pdf();
                                $pdf->SetCreator('Moodle examregistrar');
                                $pdf->SetAuthor(fullname($USER));
                                $pdf->SetTitle($examname);
                                $pdf->SetSubject($coursename);
                                $pdf->SetKeywords('moodle, quiz');
                                $pathname = $CFG->dataroot.'/temp/';
                                $pagecount = $pdf->setSourceFile($template);
                                $tplidx = $pdf->ImportPage(1);
                                $s = $pdf->getTemplatesize($tplidx);
                                $pdf->AddPage('P', array($s['w'], $s['h']));
                                $pdf->useTemplate($tplidx);

                                $topmargin = 10;
                                $leftmargin = 15;
                                $rightmargin = 15;
                                $pdf->SetMargins($leftmargin, $topmargin, $rightmargin);
                                $pdf->SetHeaderMargin(5);
                                $pdf->SetFooterMargin(10);
                                $pdf->setHeaderFont(array('helvetica', '', 8));
                                $pdf->setFooterFont(array('helvetica', '', 8));
                                $pdf->setPrintHeader(false);
                                $pdf->setPrintFooter(false);
                                $pdf->SetFont('helvetica', '', 10);
                                //$pdf->SetFillColor(5,5,5,5, false, 'white');
                                //$pdf->setOverprint(true, true, 0);
                                $pdf->AddSpotColor('MyWhite', 0, 0, 0, 10);
                                $pdf->AddSpotColor('MyBlack', 0, 0, 0, 100);
                                                    // overwrite

                                $headstyle = ' style="text-align:right; font-weight: bold;" width="15%"';
                                $headalign = ' style="vertical-align:middle;  line-height: 2.0em;   border: 1px solid black;" ';
                                $header = '<table cellspacing="0" cellpadding="4" border="1"  width:100%;  style="border: 1px solid black; border-color:black; border-collapse: collapse; table-layout:fixed; background-color:pink; ">';
                                $header .= "<tr $headalign ><td $headstyle >".get_string('programme', 'examregistrar').'</td><td colspan="5">'.$programme.'</td></tr>';
                                $header .= "<tr $headalign ><td $headstyle >".get_string('course', 'examregistrar').'</td><td colspan="5">'.$coursename.'</td></tr>';
                                $headalign = ' style="vertical-align:middle;  line-height: 1.5em;" ';
                                $header .= "<tr $headalign ><td $headstyle >".get_string('perioditem', 'examregistrar').'</td><td width="30%" colspan="1" >'.$period.'</td>'.
                                        "<td $headstyle colspan=\"1\"> ".get_string('scopeitem', 'examregistrar').' </td><td width="10%" colspan="1">'.$scope.'</td>';
                                $headstyle = ' style="text-align:right; font-weight: bold;" width="15%"';
                                $header .= "<td $headstyle colspan=\"1\"> ".get_string('annualityitem', 'examregistrar').' </td><td width="13.3%" >'.$annuality.'</td></tr>';
                                $headstyle = ' style="text-align:right; font-weight: bold;" ';
                                $headalign = ' style="vertical-align:middle;  line-height: 2.0em:  border: 1px solid black;" ';
                                $header .= "<tr $headalign ><td $headstyle >".get_string('lastname').'</td><td colspan="5"></td></tr>';
                                $header .= "<tr $headalign ><td $headstyle ".'   >'.get_string('firstname').'</td><td colspan="3"></td>'.
                                        "<td $headstyle >".get_string('idnumber').'</td><td ></td></tr>';
                                $header .= '</table>';

                                $pdf->Ln(1);
                                $x = $pdf->GetX();
                                $y = $pdf->GetY();
                                $pdf->SetFillSpotColor('MyWhite', 100);
                                $pdf->writeHTML($header, false, true, false, false, '');
                                $lx = $pdf->GetX();
                                $ly = $pdf->GetY();
                                $width = $pdf->getPageWidth() - $rightmargin -$leftmargin - 1 ;
                                $pdf->Rect($x, $y, $width, $ly-$y, 'F');
                                $pdf->SetX($x);
                                $pdf->SetY($y);
                                $pdf->SetFillSpotColor('MyBlack', 100);
                                $pdf->writeHTML($header, false, true, false, false, '');

                                // next pages
                                for ($i = 2; $i <= $pagecount; $i++) {
                                        $tplidx = $pdf->ImportPage($i);
                                        $s = $pdf->getTemplatesize($tplidx);
                                        $pdf->AddPage('P', array($s['w'], $s['h']));
                                        $pdf->useTemplate($tplidx);
                                }


                                $pdf->Output($newfile, 'F');
                                $filerecord['itemid'] = $newid;
                                $type = ($filepath == '/answers/') ? 'answers' : 'exam';
                                $filerecord['filename'] = examregistrar_file_set_nameextension($examregistrar, $examfile->idnumber, $type);
                                if($oldfile = $fs->get_file($filerecord['contextid'], $filerecord['component'], $filerecord['filearea'],
                                                            $filerecord['itemid'], $filerecord['filepath'], $filerecord['filename'])) {
                                    $oldfile->delete();
                                }
                                $fs->create_file_from_pathname($filerecord, $newfile);
                                @unlink($template);
                                @unlink($newfile);
                            }
                        }
                    }
                }
                /// now book student for new exam call
                if($extraexamid && $formdata->userid && $formdata->bookedsite) {
                    if($booking = $DB->get_record('examregistrar_bookings', array('userid'=>$formdata->userid, 'examid'=>$extraexamid))) {
                        $booking->bookedsite = $formdata->bookedsite;
                        $booking->booked = $formdata->booked;
                        $booking->component = '';
                        $booking->modifierid = $USER->id;
                        $booking->timemodified = time();
                        $DB->update_record('examregistrar_bookings', $booking);
                    } else {
                        $booking = new stdClass;
                        $booking->userid = $formdata->userid;
                        $booking->examid = $extraexamid;
                        $booking->bookedsite = $formdata->bookedsite;
                        $booking->booked = $formdata->booked;
                        $booking->component = '';
                        $booking->modifierid = $USER->id;
                        $booking->timemodified = time();
                        $booking->id = $DB->insert_record('examregistrar_bookings', $booking);
                        $voucher = examregistrar_set_booking_voucher($examregprimaryid, $booking->id, $booking->timemodified);
                    }
                    $eventdata = array();
                    $eventdata['objectid'] = $booking->id;
                    $eventdata['context'] = $context;
                    $eventdata['relateduserid'] = $booking->userid;
                    $eventdata['other'] = array();
                    $eventdata['other']['examregid'] = $examregistrar->id;
                    $eventdata['other']['examid'] = $booking->examid;
                    $eventdata['other']['booked'] = $booking->booked;
                    $eventdata['other']['bookedsite'] = $booking->bookedsite;
                    $event = \mod_examregistrar\event\booking_submitted::create($eventdata);
                    $event->add_record_snapshot('examregistrar_bookings', $record);
                    $event->trigger();
                }
            }
        }
        $delay = 10;
        if(!$message) {
            $message = get_string('changessaved');
            $delay = -1;
        }
        redirect($baseurl, $message, $delay);
    }
    /// if we are here, display the form


/// Print the page header, Output starts here
echo $OUTPUT->header();


echo $OUTPUT->heading(get_string($action, 'examregistrar'));
$mform->display();
echo $OUTPUT->footer();
