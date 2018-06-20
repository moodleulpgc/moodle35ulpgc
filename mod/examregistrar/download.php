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
 * Process download request to serve PDF or other files
 *
 * Display will depend on format parameter and user capabilities.
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/lib/pdflib.php');
require_once(__DIR__.'/locallib.php');

require_once($CFG->dirroot.'/mod/examregistrar/classes/pdf_fpdi.class.php');

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

$PAGE->set_url('/mod/examregistrar/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($examregistrar->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$output = $PAGE->get_renderer('mod_examregistrar');

$period   = optional_param('period', '', PARAM_INT);
$session   = optional_param('session', '', PARAM_INT);
$bookedsite   = optional_param('venue', '', PARAM_INT);
$room   = optional_param('room', '', PARAM_INT);
$exam   = optional_param('exam', '', PARAM_INT);
$programme   = optional_param('programme', '', PARAM_ALPHANUMEXT);
$shortname   = optional_param('shortname', '', PARAM_ALPHANUMEXT);
$download = optional_param('down', '', PARAM_ALPHANUMEXT);
$rsort = optional_param('rsort', '', PARAM_ALPHANUMEXT);
$esort = optional_param('esort', '', PARAM_ALPHANUMEXT);

require_capability('mod/examregistrar:download', $context);

$SESSION->nameformat = 'lastname';

@set_time_limit(60*30); // 30 min should be enough, if not, stops
raise_memory_limit(MEMORY_HUGE);  //     @raise_memory_limit('512M');
if (function_exists('apache_child_terminate')) {
    // if we are running from Apache, give httpd a hint that
    // it can recycle the process after it's done. Apache's
    // memory management is truly awful but we can help it.
    @apache_child_terminate();
}


function examregistrar_venuezips($examregistrar, $allocations, $params, $output) {
    global $CFG, $DB, $USER;

    $session = $params['session'];
    $bookedsite = $params['bookedsite'];
    $config = get_config('examregistrar');

    $cm = get_coursemodule_from_instance('examregistrar', $examregistrar->id, $examregistrar->course, false, MUST_EXIST);
    $zipfilename = 'session_'.$session.'_venue_'.$bookedsite;
    $path = $CFG->dataroot.'/'.trim($config->sessionsfolder);
    $path .= '/session_'.$session.'/venue_'.$bookedsite;
    make_writable_directory($path);

    $context = context_module::instance($cm->id);
    $fs = get_file_storage();

    $fileszipped = array();
    $fs->delete_area_files($context->id, 'mod_examregistrar','sessionrooms', $session);

    foreach($allocations as $room) {
        $filesforzipping = array();
        $dir = 'room_'.$room->idnumber;
        make_writable_directory($path.'/'.$dir);
        remove_dir($path.'/'.$dir, true);

        // generate room PDF

        $pdf = new examregistrar_pdf();
        $pdf->initialize_replaces($examregistrar, $params, 'room');
        $pdf->set_template($examregistrar, 'room');
        $pdf->initialize_page_setup($examregistrar, 'room');

        $pdf->add_room_allocation($room, $output, $config);

        $pdf->Ln(10);

        $filename = clean_filename('#room_'.$pdf->replaces['sessionidnumber'].'_'.$pdf->replaces['venueidnumber'].'_room_'.$pdf->replaces['roomidnumber']).
                '_'.get_string('printsingle', 'examregistrar').'.pdf';
        $pdf->Output($path.'/'.$dir.'/'.$filename, 'F');

        $filesforzipping[$dir.'/'.$filename] = $path.'/'.$dir.'/'.$filename;


//         print_object($room->exams);
//         print_object($room->additionals);
//         die;
        // add copies of room exams
        $exams = $room->exams ; //+ $room->additionals;
        foreach($room->additionals as $index => $exam) {
            if(isset($exams[$index])) {
                $exams[$index]->seated += $exam->seated;
            } else {
                $exams[$index] = $exam;
            }
        }

        foreach($exams as $exam) {
            if($file = $exam->get_examfile_file()) {
                $printmode = '';
                if($exam->get_print_mode() != 0 ) {
                    $printmode = '_'.clean_filename(get_string('printsingle', 'examregistrar'));
                }
                $fileinfo = pathinfo($file->get_filename());
                $filename =  $fileinfo['filename'];
                $ext = $fileinfo['extension'];
                if($ext) {
                    $ext = '.'.$ext;
                }
                $max = strlen($exam->seated);
                for($i=1; $i <= $exam->seated; $i++) {
                    $num = str_pad($i, $max, '0', STR_PAD_LEFT);
                    $fname = $filename.$printmode.'_'.$num.$ext;
                    $pathname = $path.'/'.$dir.'/'.$fname;
                    if($file->copy_content_to($pathname)) {
                        $filesforzipping[$dir.'/'.$fname] = $pathname;
                    }
                }
            } else {
                $filename = $exam->get_exam_name(true, true, false, false);
                $filename = clean_filename($filename.'-'.get_string('nonexistingexamfile', 'examregistrar').'.txt');
                //$filename = clean_filename($exam->programme.'-'.$exam->shortname.'-'.get_string('nonexistingexamfile', 'examregistrar').'.txt');
                $pathname = $path.'/'.$dir.'/'.$filename;
                $handle = fopen($pathname, 'w');
                fwrite($handle, get_string('nonexistingmessage', 'examregistrar', $exam));
                fclose($handle);
                $filesforzipping[$dir.'/'.$filename] = $pathname;
            }
        }

        // here room allocation is processed. Store file
        $zipfile = '';
        if($filesforzipping) {
            $zipfile = get_roomzip_filename($session, $bookedsite, $room); //$zipfilename.'_room_'.$room->idnumber.'.zip';
            $zipper = new zip_packer();
            if(!$zipper->archive_to_pathname($filesforzipping, $path.'/'.$zipfile)) {
                $zipfile = '';
            }
            if($zipper->archive_to_storage($filesforzipping, $context->id, 'mod_examregistrar',
                                        'sessionrooms', $session, '/', $zipfile)) {
                $fileszipped[] = $zipfile;
            }
        }
    }

    $url = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cm->id, 'tab'=>'session', 'session'=>$session, 'venue'=>$bookedsite));
    $message = get_string('roomspdfsgenerated', 'examregistrar', html_writer::alist($fileszipped));
    redirect($url, $message, 5);

//     $zipfile = '';
//     if($filesforzipping) {
//         $zipfile = tempnam($path.'/', $zipfilename);
//          $zipper = new zip_packer();
//          if(!$zipper->archive_to_pathname($filesforzipping, $zipfile)) {
//             $zipfile = '';
//          }
//     }
//
//
//     if($zipfile) {
//     /*
//         $addtolog = $this->add_to_log('download all submissions', get_string('downloadall', 'assign'), '', true);
//         $params = array(
//             'context' => $this->context,
//             'objectid' => $this->get_instance()->id
//         );
//         $event = \mod_assign\event\all_submissions_downloaded::create($params);
//         $event->set_legacy_logdata($addtolog);
//         $event->trigger();
//         */
//         // Send file and delete after sending.
//         send_temp_file($zipfile, $zipfilename.'.zip');
//         // We will not get here - send_temp_file calls exit.
//     }

    // if we are here, there is nothing zipped


}

function examregistrar_roomallocations_printpdf($examregistrar, $allocations, $params, $renderer, $filedest='I') {
    global $CFG, $DB, $USER;
    //require_once($CFG->libdir.'/pdflib.php');

    $pdf = new examregistrar_pdf();

    $pdf->initialize_replaces($examregistrar, $params, 'room');

    $pdf->set_template($examregistrar, 'room');

    $pdf->initialize_page_setup($examregistrar, 'room');
    // ---------------------------------------------------------

    $config = get_config('examregistrar');
    $itemcount = 0;
    $totalitems = count($allocations) ;
    foreach($allocations as $room) {
        $pdf->add_room_allocation($room, $renderer, $config);
        // if there are several rooms, add a separator, if single room, stop here
        $itemcount +=1;
        if($totalitems > 1 && $itemcount < $totalitems) {
            $pdf->add_separator_page(get_string('newroom', 'examregistrar'));
        }
    }

    $pdf->Ln(10);

    $roomsuffix = 'rooms';
    if($params['room']) {
        // we are printing a single room pdf
        $roomsuffix = 'room_'.$pdf->replaces['roomidnumber'];
    } else {
        $roomsuffix = 'rooms';
    }
    $filename = clean_filename('roomlist_'.$pdf->replaces['sessionidnumber'].'_'.$pdf->replaces['venueidnumber'].'_'.$roomsuffix).'.pdf';
    $pdf->Output($filename, $filedest);
}

function examregistrar_roomsummary_printpdf($examregistrar, $allocations, $params, $renderer, $filedest='I') {
    global $CFG, $DB, $USER;
    //require_once($CFG->libdir.'/pdflib.php');

    $pdf = new examregistrar_pdf();

    $pdf->initialize_replaces($examregistrar, $params, 'userlist');
    $pdf->set_template($examregistrar, 'userlist');
    $pdf->initialize_page_setup($examregistrar, 'userlist');
    // ---------------------------------------------------------

    $config = get_config('examregistrar');

    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(true);
    $pdf->SetFont('freeserif', '', 12);
    $header = explode('|', $pdf->template['header']);
    $header = examregistrar_str_replace($pdf->replaces, $header);
    $pdf->SetHeaderData($pdf->logoimage, $pdf->logowidth, $header[0] , $header[1]);
    $pdf->startPageGroup();
    $pdf->AddPage('', '', true);
    $main = examregistrar_str_replace($pdf->replaces, $pdf->template['title']);
    $pdf->writeHTML($main, false, false, true, false, '');

    foreach($allocations as $room) {
        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);
        $pdf->SetFont('freeserif', '', 12);
        $pdf->replaces['room'] = $room->name;
        $pdf->replaces['roomidnumber'] = $room->idnumber;
        $pdf->replaces['address'] = $room->address;
        $pdf->replaces['seats'] = $room->seats;
        $pdf->replaces['seated'] = $room->seated;
        $pdf->replaces['numexams'] = count($room->exams);

        if($room->parent) {
            list($pdf->replaces['parent'], $pdf->replaces['parentidnumber']) = examregistrar_get_namecodefromid($room->get_id(), 'locations', 'location');
        }

        $staffers = examregistrar_get_room_staffers($room->get_id(), $room->session);
        $users = array();
        foreach($staffers as $staff) {
            $name = fullname($staff);
            $role = ' ('.$staff->role.')';
            $users[] = $name.$role;
        }
        $pdf->replaces['staff'] = html_writer::alist($users);

        $pdf->writeHTML($renderer->heading($room->name, 4), true, false, true, false, '');
        $pdf->writeHTML(get_string('occupancy', 'examregistrar').":   {$room->seated} / {$room->seats} ", false, false, true, false, '');
        $pdf->writeHTML(get_string('staffers', 'examregistrar').html_writer::alist($users), false, false, true, false, '');
        $pdf->Ln(4);
        $table = new html_table();
        $table->attributes = array('style'=>'border:1px solid black;border-collapse:collapse;');
        $table->width = "100%";
        $index = 0;
        foreach($room->exams as $exam) {
            $cell1 = new html_table_cell();
            $cell1->text = $exam->get_exam_name(true, true, true, false); //$exam->programme.' - '.$exam->shortname.' - '.$exam->fullname;
            $cell2 = new html_table_cell();
            $cell2->text = $teachers = $exam->get_formatted_teachers();
            $row = new html_table_row();
            $row->cells = array($cell1, $cell2);
            $row->style = 'border:1px solid black;';
            if($index % 2 == 1) {
                    $row->style = 'border:1px solid black;border-collapse:collapse; background-color:lightgray;';
            }
            $table->data[] = $row;
            $index += 1;
        }
        $pdf->writeHTML( html_writer::table($table), true, false, true, false, '');

        if($room->set_additionals()) {
            $info = new stdClass;
            $info->users = $room->additionalusers;//count($room->additionals);
            $info->exams = count($room->additionals);
            $pdf->writeHTML(get_string('additionalusersexams', 'examregistrar', $info), true, false, true, false, '');
            $table = new html_table();
            $table->attributes = array('style'=>'border:1px solid black;border-collapse:collapse;');
            $width = 99;
            $table->width = "$width%";
            $index = 0;
            foreach($room->additionals as $exam) {
                $cell1 = new html_table_cell();
                $cell1->text =   $exam->get_exam_name(true, true, true, false); // $exam->programme.' - '.$exam->shortname.' - '.$exam->fullname;
                $cell2 = new html_table_cell();
                $cell2->text = $teachers = $exam->get_formatted_teachers();
                $row = new html_table_row();
                $row->cells = array($cell1, $cell2);
                $row->style = 'border:1px solid black;border-collapse:collapse;';
                if($index % 2 == 1) {
                        $row->style = 'border:1px solid black;border-collapse:collapse; background-color:lightgray;';
                }
                $table->data[] = $row;
                $index += 1;
            }
            $margins = $pdf->getMargins();
            $y = $pdf->getY();
            $colwidth = ($pdf->getPageWidth() - $margins['right'] - $margins['left']) * $width/100;
            $x = ($pdf->getPageWidth() - $colwidth)/2+$margins['left'];
            $pdf->writeHTMLCell(0, '', $x, $y, html_writer::table($table), 0, 1, false, true, 'L');
            $pdf->Ln(4);
            //$pdf->writeHTML( html_writer::table($table), true, false, true, false, '');
        }
        $pdf->writeHTML( html_writer::empty_tag('hr'), true, false, true, false, '');
        $pdf->Ln(4);
    }

    $pdf->Ln(10);
    $filename = clean_filename('roomsummary_'.$pdf->replaces['sessionidnumber'].'_'.$pdf->replaces['venueidnumber'].'_summary').'.pdf';
    $pdf->Output($filename, $filedest);

}

function examregistrar_examallocations_printpdf($examregistrar, $allocations, $params, $renderer, $filedest='I') {
    global $CFG, $DB, $USER;
    //require_once($CFG->libdir.'/pdflib.php');

    $pdf = new examregistrar_pdf();

    $pdf->initialize_replaces($examregistrar, $params, 'exam');

    $pdf->set_template($examregistrar, 'exam');

    $pdf->initialize_page_setup($examregistrar, 'exam');
    // ---------------------------------------------------------

    $config = get_config('examregistrar');
    $itemcount = 0;
    $totalitems = count($allocations) ;
    foreach($allocations as $exam) {
        $pdf->replaces['programme'] = $exam->programme;
        $pdf->replaces['shortname'] = $exam->get_exam_name(false, true, false, false); //$exam->shortname;
        $pdf->replaces['fullname'] = $exam->fullname;
        $pdf->replaces['callnum'] = $exam->callnum;
        $pdf->replaces['examscope'] = $exam->examscope;
        $pdf->replaces['seated'] = $exam->seated;
        $pdf->replaces['teacher'] = '';

        $coursecontext = context_course::instance($exam->courseid);
        $names = get_all_user_name_fields(true, 'u');
        if($users = get_enrolled_users($coursecontext, 'moodle/course:manageactivities', 0, 'u.id, u.idnumber, u.picture, '.$names, ' u.lastname ASC ')){
            $list = array();
            foreach($users as $user) {
                $list[] = fullname($user) ;
            }
            $pdf->replaces['teacher'] = html_writer::alist($list);
        }

        $header = explode('|', $pdf->template['header']);
        $header = examregistrar_str_replace($pdf->replaces, $header);
        $main = examregistrar_str_replace($pdf->replaces, $pdf->template['examtitle']);

        // add titlepage for exam
        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);
        $pdf->setHeaderTemplateAutoreset(true);
        $pdf->SetHeaderData($pdf->logoimage, $pdf->logowidth, $header[0] , $header[1]);
        $pdf->SetFont('freeserif', '', 12);
        $pdf->startPageGroup();
        $pdf->AddPage('', '', true);
        $pdf->Ln(10);
        $pdf->writeHTML($main, false, false, true, false, '');
        $pdf->Ln(10);

        // add venue summary for exam
        if($bookings = $exam->get_venue_bookings()) {
            $main = examregistrar_str_replace($pdf->replaces, $pdf->template['venuesummary']);
            $width = 70;
            $table = new html_table();
            $table->attributes = array('style'=>'border:1px solid black;border-collapse:collapse;');
            $table->width = "$width%";
            $heads = array();
            $cell = new html_table_cell(get_string('venue', 'examregistrar'));
            $cell->style = 'text-align:left;width:60%;';
            $heads[] = $cell;
            $cell = new html_table_cell(get_string('booked', 'examregistrar'));
            $cell->style = 'text-align:center;width:20%;';
            $heads[] = $cell;
            $cell = new html_table_cell(get_string('allocated', 'examregistrar'));
            $cell->style = 'text-align:center;width:20%;';
            $heads[] = $cell;
            $table->head = $heads;

            $index = 1;
            foreach($bookings as $booking) {
                $row = new html_table_row();
                if($index % 2 == 0) {
                    $row->style = 'border:1px solid black;border-collapse:collapse; background-color:lightgray;';
                }
                $cell1 = new html_table_cell($booking->venuename);
                $cell1->style = 'text-align:left;width:60%;';
                $cell2 = new html_table_cell($booking->booked);
                $cell2->style = 'text-align:center;width:20%;';
                $cell3 = new html_table_cell($booking->allocated);
                $cell3->style = 'text-align:center;width:20%;';
                $row->cells = array($cell1, $cell2, $cell3);
                $table->data[] = $row;
                $index += 1;
            }
            $venuetable = html_writer::table($table);

            $pdf->Ln(10);
            $pdf->writeHTML($main, false, false, true, false, '');
            $pdf->Ln(10);
            $margins = $pdf->getMargins();
            $y = $pdf->getY();
            $colwidth = ($pdf->getPageWidth() - $margins['right'] - $margins['left']) * $width/100;
            $x = ($pdf->getPageWidth() - $colwidth)/2;
            $pdf->writeHTMLCell(0, '', $x, $y, $venuetable, 0, 1, false, true, 'C');
            $pdf->Ln(10);
        }

        // add venue / room allocation table for this exam
        if($rooms = $exam->get_room_allocations()) {
            $roomlist = $renderer->list_allocatedrooms($rooms, $exam->session);
            $pdf->writeHTML(get_string('roomsinvenue', 'examregistrar', $pdf->replaces['venue']), true, false, true, false, '');
            $pdf->writeHTML($roomlist, false, false, true, false, '');
            $pdf->Ln(10);
        }

        // add venue / room allocation table for this exam
        $order = ' lastname ASC';
        $users = $exam->get_formatted_user_allocations();

        if($users) {  //= $exam->get_formatted_user_allocations($order)) {
            $width = 100;
            $widths = explode('|', $pdf->template['colwidths']);
            $usertable = $renderer->print_exam_user_table($users, $width, $widths, array(get_string('venue', 'examregistrar'), get_string('room', 'examregistrar')), array(''=>''), array('venuename'=>'text-align:left;', 'roomname'=>'text-align:left;'));

            $pdf->AddPage('P', '', true);
            $pdf->Ln(10);
            $examname = $exam->get_exam_name(true, true, true, false);
            $examname = $renderer->heading($examname, 4);   //$exam->programme.' - '.$exam->shortname.' - '.$exam->fullname, 4);
            $pdf->writeHTML($examname, false, false, true, false, '');
            $pdf->Ln(4);
            $margins = $pdf->getMargins();
            $y = $pdf->getY();
            $colwidth = ($pdf->getPageWidth() - $margins['right'] - $margins['left']) * $width/100;
            $x = ($pdf->getPageWidth() - $colwidth)/2;
            $pdf->writeHTMLCell(0, '', $x, $y, $usertable, 1, 1, false, true, 'C');
            $pdf->Ln(10);
        }
        // if there are several rooms, add a separator, if single room, stop here
        $itemcount +=1;
        if($totalitems > 1 && $itemcount < $totalitems) {
            $pdf->add_separator_page(get_string('newexam', 'examregistrar'));
        }
    }

    $pdf->Ln(10);

    $examsuffix = 'exams';
    if(isset($params['exam']) && $params['exam']) {
        // we are printing a single room pdf
        $main = $exam->get_exam_name(true, true, true, false); // $exam->programme.'-'.$exam->shortname.'-'.$exam->fullname;
        $examsuffix = 'exam_'.$main;
    } else {
        $examsuffix = 'exams';
    }
    $filename = clean_filename('examlist_'.$pdf->replaces['sessionidnumber'].'_'.$pdf->replaces['venueidnumber'].'_'.$examsuffix).'.pdf';
    $pdf->Output($filename, $filedest);
}

function examregistrar_examallocations_binderpdf($examregistrar, $allocations, $params, $renderer, $filedest='I') {
    global $CFG, $DB, $USER;
    $pdf = new examregistrar_pdf();

    $pdf->initialize_replaces($examregistrar, $params, 'binder');

    $pdf->set_template($examregistrar, 'binder');

    $pdf->initialize_page_setup($examregistrar, 'binder');
    // ---------------------------------------------------------

    $config = get_config('examregistrar');
    $itemcount = 0;
    $totalitems = count($allocations) ;
    foreach($allocations as $exam) {
        $pdf->replaces['programme'] = $exam->programme;
        $pdf->replaces['shortname'] = $exam->get_exam_name(false, true, false, false); // $exam->shortname;
        $pdf->replaces['fullname'] = $exam->fullname;
        $pdf->replaces['callnum'] = $exam->callnum;
        $pdf->replaces['examscope'] = $exam->examscope;
        $pdf->replaces['seated'] = $exam->seated;
        $pdf->replaces['teacher'] = '';

        $coursecontext = context_course::instance($exam->courseid);
        $names = get_all_user_name_fields(true, 'u');
        if($users = get_enrolled_users($coursecontext, 'moodle/course:manageactivities', 0, 'u.id, u.idnumber, u.picture, '.$names, ' u.lastname ASC ')){
            $list = array();
            foreach($users as $user) {
                $list[] = fullname($user) ;
            }
            $pdf->replaces['teacher'] = html_writer::alist($list);
        }

        $main = examregistrar_str_replace($pdf->replaces, $pdf->template['examtitle']);

        // add titlepage for exam
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setPageOrientation('L', true);
        $pdf->SetMargins(15, 20, 15, true);
        $pdf->SetFont('freeserif', '', 12);
        $pdf->startPageGroup();
        $pdf->AddPage('L', '', false);
        $margins = $pdf->getMargins();
        $colwidth = ($pdf->getPageWidth() - $margins['right'] - $margins['left'] - 30)/2;
        $pdf->Line($pdf->getPageWidth()/2, $margins['top'], $pdf->getPageWidth()/2, $pdf->getPageHeight()-$margins['top']);
        $pdf->resetColumns();
        $pdf->setEqualColumns(2, $colwidth);
        $pdf->selectColumn(0);
        $pdf->Write(24, '');
        $pdf->Ln(24);
        $pdf->writeHTML($main, false, false, true, false, '');
        $pdf->Ln(4);
        $teachers = $exam->get_formatted_teachers();
        $pdf->writeHTML($teachers, false, false, true, false, '');

        // add venue summary for exam
        if($bookings = $exam->get_venue_bookings()) {
            $main = examregistrar_str_replace($pdf->replaces, $pdf->template['venuesummary']);
            $width = 80;
            $table = new html_table();
            $table->attributes = array('style'=>'border:1px solid black;border-collapse:collapse;');
            $table->width = "$width%";
            $heads = array();
            $cell = new html_table_cell(get_string('venue', 'examregistrar'));
            $cell->style = 'text-align:left;border-bottom:1px solid black;border-collapse:collapse;width:40%;';
            $heads[] = $cell;
            $cell = new html_table_cell(get_string('booked', 'examregistrar'));
            $cell->style = 'text-align:center;border-bottom:1px solid black;border-collapse:collapse;width:20%;';
            $heads[] = $cell;
            $cell = new html_table_cell(get_string('allocated', 'examregistrar'));
            $cell->style = 'text-align:center;border-bottom:1px solid black;border-collapse:collapse;width:20%;';
            $heads[] = $cell;
            $cell = new html_table_cell(get_string('taken', 'examregistrar'));
            $cell->style = 'text-align:center;border-bottom:1px solid black;border-collapse:collapse;width:20%;';
            $heads[] = $cell;

            $table->head = $heads;

            $index = 1;
            foreach($bookings as $booking) {
                $row = new html_table_row();
                if($index % 2 == 1) {
                    $row->style = 'border:1px solid black;border-collapse:collapse; background-color:lightgray;';
                }
                $cell1 = new html_table_cell($booking->venuename);
                $cell1->style = 'text-align:left;width:40%;';
                $cell2 = new html_table_cell($booking->booked);
                $cell2->style = 'text-align:center;width:20%;';
                $cell3 = new html_table_cell($booking->allocated);
                $cell3->style = 'text-align:center;width:20%;';
                $cell4 = new html_table_cell('');
                $cell4->style = 'text-align:center;width:20%;border:1px solid black;border-collapse:collapse;';
                $row->cells = array($cell1, $cell2, $cell3, $cell4);
                $table->data[] = $row;
                $index += 1;
            }
            $venuetable = html_writer::table($table);

            $main = examregistrar_str_replace($pdf->replaces, $pdf->template['venuesummary']);
            $pdf->selectColumn(1);
            $pdf->Write(12, '');
            $pdf->Ln(12);
            $pdf->writeHTML($main, false, false, true, false, '');
            $pdf->Ln(4);
            $pdf->writeHTML($teachers, false, false, true, false, '');
            $pdf->Ln(10);
            $pdf->writeHTML($venuetable, true, false, true, false, 'R');
            $pdf->Ln(10);
        }

    }
    $filename = clean_filename('exambinders_'.$pdf->replaces['sessionidnumber'].'_'.$pdf->replaces['venueidnumber'].'_binders').'.pdf';
    $pdf->Output($filename, $filedest);
}

function examregistrar_userallocations_printpdf($examregistrar, $params, $renderer, $filedest='I') {
    global $CFG, $DB, $USER;
    require_once($CFG->libdir.'/pdflib.php');

    $pdf = new examregistrar_pdf();

    $pdf->initialize_replaces($examregistrar, $params, 'userlist');

    $pdf->set_template($examregistrar, 'userlist');

    $pdf->initialize_page_setup($examregistrar, 'userlist');
    // ---------------------------------------------------------

    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(true);
    $pdf->SetFont('freeserif', '', 12);

    $names = get_all_user_name_fields(true, 'u');
    $sql = "SELECT  b.id AS bid, ss.roomid, ss.additional, u.id, u.username, u.idnumber, $names,
                    el.name AS venuename, el.idnumber AS venueidnumber,
                    el2.name AS roomname, el2.idnumber AS roomidnumber, COUNT(b.id) AS numadditionals
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON b.examid = e.id AND  e.examsession = :session
            JOIN {examregistrar_locations} l ON l.id = b.bookedsite
            JOIN {examregistrar_elements} el ON l.examregid = el.examregid AND el.type = 'locationitem' AND l.location = el.id
            JOIN {user} u ON b.userid = u.id
            LEFT JOIN {examregistrar_session_seats} ss ON b.userid = ss.userid AND b.examid = ss.examid
            LEFT JOIN {examregistrar_locations} l2 ON l2.id = ss.roomid
            LEFT JOIN {examregistrar_elements} el2 ON l2.examregid = el2.examregid AND el2.type = 'locationitem' AND l2.location = el2.id
            WHERE b.bookedsite = :bookedsite AND b.booked = 1
            GROUP BY ss.userid
            ORDER BY u.lastname ASC, u.firstname ASC, u.idnumber ASC ";

    $sqlparams = array('session'=>$params['session'], 'bookedsite'=>$params['bookedsite']);

    // add venue / room allocation table for this exam
    if($users = $DB->get_records_sql($sql, $sqlparams)) {
        $width = 100;
        $widths = explode('|', $pdf->template['colwidths']);
        $usertable = $renderer->print_exam_user_table($users, $width, $widths, array(get_string('venue', 'examregistrar'), get_string('room', 'examregistrar')), array('numadditionals'=>'*'), array('venuename'=>'text-align:left;', 'roomname'=>'text-align:left;'));

        // add titlepage for userlist
        $header = explode('|', $pdf->template['header']);
        $header = examregistrar_str_replace($pdf->replaces, $header);
        $pdf->SetHeaderData($pdf->logoimage, $pdf->logowidth, $header[0] , $header[1]);
        $pdf->startPageGroup();
        $pdf->AddPage('', '', true);

        $main = examregistrar_str_replace($pdf->replaces, $pdf->template['title']);
        $pdf->writeHTML($main, false, false, true, false, '');
        $pdf->Ln(10);
        $pdf->Ln(10);
        $margins = $pdf->getMargins();
        $y = $pdf->getY();
        $colwidth = ($pdf->getPageWidth() - $margins['right'] - $margins['left']) * $width/100;
        $x = ($pdf->getPageWidth() - $colwidth)/2;
        $pdf->writeHTMLCell(0, '', $x, $y, $usertable, 1, 1, false, true, 'C');
        $pdf->Ln(10);
    }

    $pdf->Ln(10);
    $filename = clean_filename('examlist_'.$pdf->replaces['sessionidnumber'].'_'.$pdf->replaces['venueidnumber'].'_users').'.pdf';
    $pdf->Output($filename, $filedest);
}


function examregistrar_venueallocations_printpdf($examregistrar, $params, $renderer, $filedest='I') {
    global $CFG, $DB, $USER;
    require_once($CFG->libdir.'/pdflib.php');

    $pdf = new examregistrar_pdf();

    $pdf->initialize_replaces($examregistrar, $params, 'userlist');

    $pdf->set_template($examregistrar, 'venue');

    $pdf->initialize_page_setup($examregistrar, 'venue');
    // ---------------------------------------------------------

    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(true);
    $pdf->SetFont('freeserif', '', 12);

    // add titlepage for userlist
    $header = explode('|', $pdf->template['header']);
    $header = examregistrar_str_replace($pdf->replaces, $header);
    $pdf->SetHeaderData($pdf->logoimage, $pdf->logowidth, $header[0] , $header[1]);
    $pdf->startPageGroup();
    $pdf->AddPage('', '', true);

    $main = examregistrar_str_replace($pdf->replaces, $pdf->template['title']);
    $pdf->writeHTML($main, false, false, true, false, '');
    $pdf->Ln(10);
    $pdf->Ln(10);

    /*
    // get data for usertable
    $names = get_all_user_name_fields(true, 'u');
    $sql = "SELECT  b.id AS bid,  b.userid, b.examid, c.shortname, c.fullname, ss.roomid, u.username, u.idnumber, $names,
                    (SELECT COUNT(b2.examid)  FROM {examregistrar_bookings} b2
                                              JOIN {examregistrar_exams} e2 ON b2.examid = e2.id
                                                WHERE b2.userid = b.userid AND b2.bookedsite = b.bookedsite AND b2.booked = 1
                                                AND  e2.examsession = e.examsession
                                                GROUP BY b2.userid ) AS numexams
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON b.examid = e.id AND  e.examsession = :session
            JOIN {user} u ON b.userid = u.id
            JOIN {course} c ON c.id = e.courseid
            LEFT JOIN {examregistrar_session_seats} ss ON  b.userid = ss.userid AND b.examid = ss.examid AND b.bookedsite = ss.bookedsite
            WHERE b.bookedsite = :bookedsite AND b.booked = 1
            GROUP BY b.userid, b.examid
            ORDER BY u.lastname ASC, u.firstname ASC, u.idnumber ASC, c.shortname ASC ";

    $sqlparams = array('session'=>$params['session'], 'bookedsite'=>$params['bookedsite']);
    if($users = $DB->get_records_sql($sql, $sqlparams)) {
    */
    
    if($users = examregistrar_get_session_venue_users($params['session'], $params['bookedsite'])) {
    
        $width = 100;
        $widths = explode('|', $pdf->template['colwidths']);
        $usertable = $renderer->print_venue_users_table($users, $width, $widths, array(get_string('venue', 'examregistrar'), get_string('room', 'examregistrar')), array('numadditionals'=>'*'), array('venuename'=>'text-align:left;', 'roomname'=>'text-align:left;'));

        $margins = $pdf->getMargins();
        $y = $pdf->getY();
        $colwidth = ($pdf->getPageWidth() - $margins['right'] - $margins['left']) * $width/100;
        $x = ($pdf->getPageWidth() - $colwidth)/2;
        $pdf->SetFont('freeserif', '', 10);
        $pdf->writeHTMLCell(0, '', $x, $y, $usertable, 1, 1, false, true, 'C');
        $pdf->Ln(10);
    }

    $pdf->Ln(10);
    $filename = clean_filename('examlist_'.$pdf->replaces['sessionidnumber'].'_'.$pdf->replaces['venueidnumber'].'_users').'.pdf';
    $pdf->Output($filename, $filedest);
}


function examregistrar_venue_fax_binder_printpdf($examregistrar, $params, $renderer, $filedest='I') {
    global $CFG, $DB, $USER;
    $pdf = new examregistrar_pdf();

    $pdf->initialize_replaces($examregistrar, $params, 'venuefax');

    $pdf->set_template($examregistrar, 'venuefax');

    $pdf->initialize_page_setup($examregistrar, 'binder');
    // ---------------------------------------------------------

    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(true);
    $pdf->SetFont('freeserif', '', 12);

    // add titlepage for userlist
    $header = explode('|', $pdf->template['header']);
    $header = examregistrar_str_replace($pdf->replaces, $header);
    $pdf->SetHeaderData($pdf->logoimage, $pdf->logowidth, $header[0] , $header[1]);
    $pdf->startPageGroup();
    $pdf->AddPage('', '', true);

    //$main = examregistrar_str_replace($pdf->replaces, $pdf->template['title']);
    //$pdf->writeHTML($main, false, false, true, false, '');
    //$pdf->Ln(10);
    $main = examregistrar_str_replace($pdf->replaces, $pdf->template['venuesummary']);
    $pdf->writeHTML($main, false, false, true, false, '');
    $pdf->Ln(10);

    $sql = "SELECT  b.id AS bid,  b.examid, c.shortname, c.fullname, COUNT(b.userid) AS bookings,
                    (SELECT {examregistrar_session_seats} ss      ) AS allocated
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON b.examid = e.id AND  e.examsession = :session
            JOIN {course} c ON c.id = e.courseid
            LEFT JOIN {examregistrar_session_seats} ss ON  b.userid = ss.userid AND b.examid = ss.examid AND b.bookedsite = ss.bookedsite
            WHERE b.bookedsite = :bookedsite AND b.booked = 1
            GROUP BY b.examid
            ORDER BY c.shortname ASC ";



    $sql = "SELECT  b.id AS bid,  b.examid, e.annuality, e.examsession, e.examscope, e.callnum, e.courseid,   c.shortname, c.fullname, COUNT(b.userid) AS bookings,
                    (SELECT COUNT(ss.userid)
                        FROM {examregistrar_session_seats} ss
                        WHERE ss.userid = b.userid AND b.examid = ss.examid AND b.bookedsite = ss.bookedsite AND ss.roomid > 0
                        GROUP BY ss.examid ) AS allocated
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON b.examid = e.id AND  e.examsession = :session
            JOIN {course} c ON c.id = e.courseid
            WHERE b.bookedsite = :bookedsite AND b.booked = 1
            GROUP BY b.examid
            ORDER BY c.shortname ASC ";

    $sqlparams = array('session'=>$params['session'], 'bookedsite'=>$params['bookedsite']);

    if($exams = $DB->get_records_sql($sql, $sqlparams)) {
        $width = 100;
        $table = new html_table();
        $table->attributes = array('style'=>'border:1px solid black;border-collapse:collapse;');
        $table->width = "$width%";
        $heads = array();
        $cell = new html_table_cell(get_string('exam', 'examregistrar'));
        $cell->style = 'text-align:left;border-bottom:1px solid black;border-collapse:collapse;width:40%;';
        $heads[] = $cell;
        $cell = new html_table_cell(get_string('booked', 'examregistrar'));
        $cell->style = 'text-align:center;border-bottom:1px solid black;border-collapse:collapse;width:20%;';
        $heads[] = $cell;
        $cell = new html_table_cell(get_string('allocated', 'examregistrar'));
        $cell->style = 'text-align:center;border-bottom:1px solid black;border-collapse:collapse;width:20%;';
        $heads[] = $cell;
        $cell = new html_table_cell(get_string('taken', 'examregistrar'));
        $cell->style = 'text-align:center;border-bottom:1px solid black;border-collapse:collapse;width:20%;';
        $heads[] = $cell;

        $table->head = $heads;

        $index = 1;
        foreach($exams as $exam) {

            $row = new html_table_row();
            if($index % 2 == 1) {
                $row->style = 'border:1px solid black;border-collapse:collapse; background-color:lightgray;';
            }
            $examclass = new examregistrar_exam($exam);
            $examname = $examclass->get_exam_name(false, true, true, false);
            $cell1 = new html_table_cell($examname);
            //$cell1 = new html_table_cell($exam->shortname.'-'.$exam->fullname);
            $cell1->style = 'text-align:left;width:40%;';
            $cell2 = new html_table_cell($exam->bookings);
            $cell2->style = 'text-align:center;width:20%;';
            $cell3 = new html_table_cell($exam->allocated);
            $cell3->style = 'text-align:center;width:20%;';
            $cell4 = new html_table_cell('');
            $cell4->style = 'text-align:center;width:20%;border:1px solid black;border-collapse:collapse;';
            $row->cells = array($cell1, $cell2, $cell3, $cell4);
            $table->data[] = $row;
            $index += 1;
        }
        $venuetable = html_writer::table($table);
        $pdf->Ln(10);
        $pdf->writeHTML($venuetable, true, false, true, false, 'R');
        $pdf->Ln(10);
    }

    $filename = clean_filename('exambinders_'.$pdf->replaces['sessionidnumber'].'_'.$pdf->replaces['venueidnumber'].'_binders').'.pdf';
    $pdf->Output($filename, $filedest);
}


function examregistrar_roomallocations_download($examregistrar, $allocations, $params, $renderer){
    global $CFG, $DB, $USER;

    $session = $DB->get_record('examregistrar_examsessions', array('id'=>$params['session']));
    $period  = $DB->get_record('examregistrar_periods', array('id'=>$session->period));

    /// get session name & code
    if($period) {
        list($periodname, $periodidnumber) = examregistrar_get_namecodefromid($period->id, 'periods', 'period');
        $replaces['period'] = $periodname;
        $replaces['periodidnumber'] = $periodidnumber;
    }

    if($params['session']) {
        list($sessionname, $sessionidnumber) = examregistrar_get_namecodefromid($params['session'], 'examsessions', 'examsession');
        $replaces['session'] = $sessionname;
        $replaces['sessionidnumber'] = $sessionidnumber;

    }
    if($params['bookedsite']) {
        list($venuename, $venueidnumber) = examregistrar_get_namecodefromid($params['bookedsite'], 'locations', 'location');
        $replaces['venue'] = $venuename;
        $replaces['venueidnumber'] = $venueidnumber;
    }

    $format = 'ods';

    $filename = clean_filename('roomlist_'.$format.'_'.$sessionidnumber.'_'.$venueidnumber.'_users').'.'.$format;

    $headers = array('venue' => 'city',
                        'num' => 'num',
                        'fromroom' => 'fromroom',
                        'toroom' => 'toroom',
                        'shortname' => 'shortname',
                    );


    /// Creating a workbook
        if($format == 'xls') {
            require_once($CFG->dirroot.'/lib/excellib.class.php');
            $workbook = new MoodleExcelWorkbook("-");
        } else {
            require_once($CFG->dirroot.'/lib/odslib.class.php');
            $workbook = new MoodleODSWorkbook("-");
        }
    /// Sending HTTP headers
        $workbook->send($filename);
    /// Adding the worksheet
        $myxls =& $workbook->add_worksheet('assignseats');

    /// Print names of all the fields
        $column = 0;
        foreach($headers as $field) {
            $myxls->write_string(0,$column,$field);
            $column +=1;
        }
        $row = 1;
        foreach($allocations as $room) {
            if($room->exams) {
                foreach($room->exams as $exam) {
                    $exam->set_users();
                    $myxls->write_string($row,0,$venueidnumber);
                    $myxls->write_string($row,1, count($exam->users));
                    $myxls->write_string($row,2, '');
                    $myxls->write_string($row,3, $room->idnumber);
                    $myxls->write_string($row,4, $exam->shortname);
                    $row +=1;
                }
            }
        }
    /// Close the workbook
        $workbook->close();
        exit;
}



//  print_object($_GET);
//  print_object("_GET -----------------");
//  print_object($_POST);
//  print_object("_POST -----------------");
/// process requests
if($download) {
    if($download == 'printroompdf') {
        $params = array( 'session'=>$session, 'bookedsite'=>$bookedsite,
                        'room'=>$room, 'programme'=>$programme);
        $allocations = examregistrar_get_roomallocations_byroom($params);
        examregistrar_roomallocations_printpdf($examregistrar, $allocations, $params, $output);
    } elseif($download == 'printroomsumarypdf') {
        $params = array( 'session'=>$session, 'bookedsite'=>$bookedsite,
                        'room'=>$room, 'programme'=>$programme, 'sort'=>$rsort);
        $allocations = examregistrar_get_roomallocations_byroom($params);
        examregistrar_roomsummary_printpdf($examregistrar, $allocations, $params, $output);
    } elseif($download == 'printexampdf') {
        $params = array( 'session'=>$session, 'bookedsite'=>$bookedsite,
                        'room'=>$room, 'programme'=>$programme, 'exam'=>$exam);
        $allocations = examregistrar_get_examallocations_byexam($params);
        examregistrar_examallocations_printpdf($examregistrar, $allocations, $params, $output);
    } elseif($download == 'printbinderpdf') {
        $params = array( 'session'=>$session, 'bookedsite'=>null,
                        'room'=>$room, 'programme'=>$programme, 'exam'=>$exam);
        $allocations = examregistrar_get_examallocations_byexam($params);
        $params['bookedsite'] = $bookedsite; // separate to allow faxbinder for ALL exams in all venues
        examregistrar_examallocations_binderpdf($examregistrar, $allocations, $params, $output);
    } elseif($download == 'printuserspdf') {
        $params = array( 'session'=>$session, 'bookedsite'=>$bookedsite,
                        'room'=>$room, 'programme'=>$programme, 'exam'=>$exam);
        examregistrar_userallocations_printpdf($examregistrar, $params, $output);
    } elseif($download == 'assignseats') {
        $params = array( 'session'=>$session, 'bookedsite'=>$bookedsite,
                        'room'=>$room, 'programme'=>$programme);
        $allocations = examregistrar_get_roomallocations_byroom($params);
        examregistrar_roomallocations_download($examregistrar, $allocations, $params, $output);
    } elseif($download == 'printsingleroompdf') {
        $params = array( 'session'=>$session, 'bookedsite'=>$bookedsite,
                        'room'=>$room, 'programme'=>$programme);
        examregistrar_venueallocations_printpdf($examregistrar, $params, $output);
    } elseif($download == 'printsingleroomfaxpdf') {
        $params = array( 'session'=>$session, 'bookedsite'=>$bookedsite,
                        'room'=>$room, 'programme'=>$programme);
        examregistrar_venue_fax_binder_printpdf($examregistrar, $params, $output);
    } elseif($download == 'genvenuezips') {
        $params = array( 'session'=>$session, 'bookedsite'=>$bookedsite,
                        'room'=>$room, 'programme'=>$programme, 'sort'=>$rsort);
        $allocations = examregistrar_get_roomallocations_byroom($params);
        examregistrar_venuezips($examregistrar, $allocations, $params, $output);
    }
}

