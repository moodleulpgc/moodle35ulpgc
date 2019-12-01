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
 * Displays the page for viewing exams and booking if allowed
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// this file cannot be used alone, int must be included in a page-displaying script

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/mod/examregistrar/booking_form.php');

if(!$canbook) {
    throw new required_capability_exception($context, 'mod/examregistrar:book', 'nopermissions');
}

$tab = 'booking';
$baseurl = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cm->id,'tab'=>$tab));
if($cancel = optional_param('cancel', '', PARAM_ALPHANUM)) {
    $baseurl->param('tab', 'view');
    redirect($baseurl, '', 0);
}

$period   = optional_param('period', '', PARAM_INT);
$now = time();
//$now = strtotime('4 may 2014') + 3605;

$periodobj = '';
if(!$period) {
    $periods = examregistrar_current_periods($examregistrar, $now);
    if($periods) {
        $periodobj = reset($periods);
        $period = $periodobj->id;
    }
}
if(!$periodobj) {
    $periodobj = $DB->get_record('examregistrar_periods', array('id'=>$period), '*', MUST_EXIST);
}

$searchname = optional_param('searchname', '', PARAM_TEXT);
$searchid = optional_param('searchid', '', PARAM_INT);
$sort = optional_param('sorting', 'shortname', PARAM_ALPHANUM);
$order = optional_param('order', 'ASC', PARAM_ALPHANUM);
$baseparams = array('exreg' => $examregistrar, 'id'=>$cm->id, 'tab'=>$tab);
$bookingparams = array('period'=>$period,
                      'searchname'=>$searchname,
                      'searchid'=>$searchid,
                      'programme'=>$programme,
                      'sorting'=>$sort,
                      'order'=>$order,
                      'user'=>$userid);

$bookingurl = new moodle_url($baseurl, $bookingparams);

echo $output->box(get_string('bookinghelp1', 'examregistrar', $examregistrar), 'generalbox mod_introbox', 'examregistrarintro');

/// display user form, if allowed
$canbookothers = false;
if($canbookothers = has_capability('mod/examregistrar:bookothers',$context)) {
    print_collapsible_region_start('', 'showhideuserselector', get_string('searchoptions'),
                    'examregistrar_booking_userselector_collapsed', true, false);
    $options = array('context' => $context, 'examregid' => $examregistrar->id, 'extrafields'=>array('idnumber'));
    $userselector = new examregistrar_user_selector('user', $options);
    $userselector->set_multiselect(false);
    $userselector->set_rows(5);
    $userselector->nameformat = 'lastname fistname';
    $viewuser = $userselector->get_selected_user();
    // Show UI for choosing a user to report on.
    echo $output->box_start('generalbox boxwidthnormal boxaligncenter', 'chooseuser');
    echo '<form method="get" action="' . $CFG->wwwroot . '/mod/examregistrar/view.php" >';

    // Hidden fields.
    echo html_writer::input_hidden_params($bookingurl, array('user'));

    // User selector.
    echo $output->heading('<label for="user">' . get_string('selectuser','examregistrar') . '</label>', 4);
    $userselector->display();

    // Submit button and the end of the form.
    echo '<p id="chooseusersubmit"><input type="submit" value="' . get_string('showuserexams', 'examregistrar') . '" /></p>';
    echo '</form>';
    echo $output->box_end();
    print_collapsible_region_end(false);
}

$annuality =  examregistrar_get_annuality($examregistrar);
$canviewall = has_capability('mod/examregistrar:viewall', $context);

$config = examregistrar_get_instance_configdata($examregistrar);
$capabilities = array('bookothers'=>$canbookothers, 'manageexams'=>$canmanageexams);
$lagdays = examregistrar_set_lagdays($examregistrar, $config, $periodobj, $capabilities);

$courses = examregistrar_get_user_courses($examregistrar, $course, $bookingparams, array('mod/examregistrar:book', 'mod/examregistrar:bookothers'), $canviewall, true);

echo $output->exams_item_selection_form($examregistrar, $course, $bookingurl, $bookingparams);
if($canviewall) {
    echo $output->exams_courses_selector_form($examregistrar, $course, $bookingurl, $bookingparams);
}

$bookingurl->param('action', 'checkvoucher');
echo $output->box(html_writer::link($bookingurl, get_string('checkvoucher', 'examregistrar')), 'resettable mdl-right ');
$bookingurl->remove_params('action');

$examcourses = array();
$noexamcourses = array();
$excludespecials = '';
if(!$canbookothers) {
    $excludespecials = ' AND e.callnum > 0 ';
}

$params = array('examregid'=>$examregprimaryid);
$onlyvisible = '';
if(!$canmanageexams) {
    $onlyvisible = ' AND e.visible = 1 ';
    $params['visible'] = 1;
}

foreach($courses as $cid => $usercourse) {
    $usercourse->exams = '';
    $usercourse->noexam = '';
    $params['courseid'] = $cid;
    $sql = "SELECT e.*, s.examsession AS sessionelement, s.examdate, s.duration, s.timeslot,
                        se.name AS scopename, se.idnumber AS scopeidnumber,
                        es.name AS sessionname, es.idnumber AS sessionidnumber
            FROM {examregistrar_exams} e
            JOIN {examregistrar_examsessions} s ON e.examregid = s.examregid AND e.examsession = s.id
            JOIN {examregistrar_elements} se ON e.examregid = se.examregid AND se.type = 'scopeitem' AND e.examscope = se.id
            JOIN {examregistrar_elements} es ON s.examregid = es.examregid AND es.type = 'examsessionitem' AND s.examsession = es.id
            WHERE e.examregid = :examregid AND e.courseid = :courseid AND e.period = :period $excludespecials AND e.visible = 1
            ORDER BY s.examdate ASC, se.name ";

    if($exams = $DB->get_records_sql($sql, array('examregid'=>$examregprimaryid, 'courseid'=>$cid, 'period'=>$period ))) {
        $usercourse->exams = $exams;
    } elseif(!$DB->record_exists('examregistrar_exams', $params)) {
        $usercourse->noexam = 1;
    } elseif(!$DB->record_exists('examregistrar_exams', $params + array('annuality'=>$annuality))) {
        $usercourse->noexam = 2;
    } elseif(!$DB->record_exists('examregistrar_exams', $params + array('period'=>$period))) {
        $usercourse->noexam = 3;
    }
    
    if($usercourse->exams) {
        $examcourses[$cid] = clone $usercourse;
    }
    if($usercourse->noexam) {
        $noexamcourses[$cid] = clone $usercourse;
    }
}
unset($courses);

$bookings = array();
foreach($examcourses as $cid => $usercourse) {
    $booking = new stdClass;
    $examsbyscopes = array();
    // period exams re-ordered by examscope, only one booking by examscope and period even if there are several calls in a period
    foreach($usercourse->exams as $exam) {
        $examsbyscopes[$exam->examscope][$exam->id] = $exam;
    }
    unset($usercourse->exams);
    $booking->course = $usercourse;

    foreach($examsbyscopes as $examscope => $exams) {
        $booking->examperiod = $period;
        $booking->examscope = $examscope;
        $booking->exams = $exams;
        $booking->examid = 0;
        $booking->booked = -1;
        $booking->bookedsite = 0;
        $booking->voucher = '';
        $visible = false;
        // loop al exams in the period/examscope calls. If one exam, set as is,
        // if there are several calls, visibility is set if any one is visible.
        // Must be just one booking (booked=1), if any: last found is kept.
        $booking->numcalls = 0;
        foreach($exams as $exam) {
            if($exam->visible) {
                $visible = true;
                $booking->numcalls += 1;
            }
            if($userbooking = $DB->get_records('examregistrar_bookings', array('userid'=>$userid, 'examid'=>$exam->id), 'booked DESC, timemodified DESC', '*', 0, 1)) {
                $userbooking = reset($userbooking);
                $booking->examid = $exam->id;
                $booking->booked = $userbooking->booked;
                $booking->bookedsite = $userbooking->bookedsite;
                $booking->voucher = $DB->get_record('examregistrar_vouchers', array('examregid'=>$examregprimaryid, 'bookingid'=> $userbooking->id));
            }
        }
        $booking->visible = $visible;
        // needed for correct display in the form
        $booking->separator = true;
        $booking->displayname = true;
        if($visible || $canmanageexams) {
            $bookings[$cid.'-'.$examscope] = clone $booking;
        }
    }
}
unset($examcourses);

/// get period name & code
if($period) {
    list($periodname, $periodidnumber) = examregistrar_get_namecodefromid($period, 'periods', 'period');

}
echo $output->heading(get_string('examsforperiod', 'examregistrar', $periodname));
$session = examregistrar_next_sessionid($examregistrar, time(), true);
$info = new stdClass();
$info->lagdays = examregistrar_set_lagdays($examregistrar, $config, $periodobj, array());
$info->weekexamday = userdate($session->examdate, '%A');
$info->weekday = userdate(($session->examdate - DAYSECS*$info->lagdays - 3600*3), '%A');
echo $output->box(get_string('bookinghelp2', 'examregistrar', $info), 'generalbox mod_introbox', 'examregistrarintro');

$sessions = array();
foreach($bookings as $index => $booking) {
    if($booking->exams) {
        $params = array();
        list($insql, $params) = $DB->get_in_or_equal(array_keys($booking->exams), SQL_PARAMS_NAMED, 'exam');
        $select = " booked = 1 AND userid = :user AND examid $insql ";
        $params['user'] = $userid;
        $booked = false;
        if($booked = $DB->get_record_select('examregistrar_bookings', $select, $params)) {
            $booking->booked = 1;
            $booking->examid = $booked->examid;
            $booking->bookedsite = $booked->bookedsite;
            $session = $DB->get_field('examregistrar_exams', 'examsession', array('id'=>$booked->examid));
            $sessions[$session] = $booked->bookedsite;
        } else {
            $booking->booked = 0;
            $booking->bookedsite = 0;
        }
    }
}

$params = array('period'=>$period, 'programme'=>$programme, 'user'=>$userid, 'order'=>$order, 'sorting'=>$sort);
$mform = new examregistrar_booking_form(null, array('exreg' => $examregistrar, 'cmid'=>$cm->id, 'period'=>$periodobj,
                                                    'examcourses'=>$bookings, 'noexamcourses'=>$noexamcourses,
                                                    'params'=>$bookingparams, 'capabilities'=>$capabilities),
                                               'post', '', array('class'=>' bookingform ' ));

$message = array();

if($formdata = $mform->get_data()) {

// process bookings by student or bookothers
    $now = time();
    //$now = strtotime('4 may 2014') + 3605;
    $bookings = optional_param_array_array('booking', array(), PARAM_INT);
    
    $errors = array();
    $sites = array();
    foreach($bookings as $key => $booking) {
        if(!isset($booking['booked']) || !isset($booking['bookedsite']) || !isset($booking['examid'])) {
            unset($bookings[$key]);
        } elseif(!$booking['examid']) {
            unset($bookings[$key]);
            if(isset($booking['booked'])) {
                $booking['error'] = 'noexam';
                $errors[$key] = $booking;
            }
        } elseif(!$booking['bookedsite'] && $booking['booked']) {
                $booking['error'] = 'nosite';
                $errors[$key] = $booking;
                unset($bookings[$key]);
        } else {
            if(!isset($booking['session'])) {
                $booking['session'] = $DB->get_field('examregistrar_exams', 'examsession', array('id'=>$booking['examid']));
                $bookings[$key] = $booking;
            }
            
            if(!isset($sites[$booking['session']])) {
                $sites[$booking['session']] = array();
            }
            $sites[$booking['session']][] = $booking['bookedsite'];
        }
    }            
    
    foreach($sites as $key => $site) {
        $histo = array_count_values($site);
        asort($histo);
        $histo = array_keys($histo);
        $site = array_pop($histo);
        $sites[$key] = $site;
    }

    foreach($bookings as $key => $booking) {            
            if(!isset($sessions[$booking['session']]) && $booking['booked']) {
                $sessions[$booking['session']] = $sites[$booking['session']];   // $booking['bookedsite'];
            } elseif($booking['booked'] && isset($sessions[$booking['session']]) && ($booking['bookedsite'] != $sites[$booking['session']])) {
//                    $prev = $DB->get_field('examregistrar_bookings', 'examid', array('userid'=>$userid, 'booked'=>1, 'bookedsite'=>$sessions[$booking['session']])); 
  //                  if($prev && ($prev != $booking['examid'])) {    
                        $booking['error'] = 'twosites';
                        $errors[$key] = $booking;
                        unset($bookings[$key]);
    //                }
            }
    }
    // now only remain true bookings without errors
  
    if($bookings) {
        foreach($bookings as $key => $booking) {
            $newid = 0;
            // there mey be several non-booked records if changed booking many times
            $exam = $DB->get_record('examregistrar_exams', array('id'=>$booking['examid']));
            if($records = $DB->get_records('examregistrar_bookings', array('examid'=>$booking['examid'], 'userid'=>$userid,
                                                                            'booked'=>$booking['booked'], 'bookedsite'=>$booking['bookedsite'],
                                                                            'modifierid'=>$USER->id), 'timemodified DESC')) {
                $record = reset($records);
                $newid = $record->id;
                // recover exam voucher if record_exists
                $voucher = $DB->get_record('examregistrar_vouchers', array('examregid'=>$examregprimaryid, 'bookingid'=> $record->id));
                
            } else {
                // we must insert
                $record = new stdClass;
                $record->examid = $booking['examid'];
                $record->userid = $userid;
                $record->booked = $booking['booked'];
                $record->bookedsite = $booking['bookedsite'];
                $record->modifierid = $USER->id;
                $record->timecreated = $now;
                $record->timemodified = $now;

                $examdate = 0;
                if($exam) {
                    $examdate = $DB->get_field('examregistrar_examsessions', 'examdate', array('id'=>$exam->examsession, 'examregid'=>$exam->examregid, 'period'=>$exam->period));
                } else {
                    $booking['error'] = 'noexamid';
                    $errors[$key] = $booking;
                }
                if($exam && ($exam->period == $period) &&
                        !examregistrar_check_exam_in_past($now, $lagdays, $examdate) &&
                        (examregistrar_check_exam__within_period($now, $periodobj, $examdate, $config) OR $canmanageexams)) {
                    if($newid = $DB->insert_record('examregistrar_bookings', $record)) {
                        // we have a new booking, set voucher for it
                        $voucher = examregistrar_set_booking_voucher($examregprimaryid, $newid, $now);
                    }
                } else {
                    $booking['error'] = 'offbounds';
                    $errors[$key] = $booking;
                }
            }
            // all other bookings for examid user ide set booked = 0
            if($newid) {
                $record->id = $newid;
                // log the action
                $eventdata = array();
                $eventdata['objectid'] = $newid;
                $eventdata['context'] = $context;
                $eventdata['userid'] = $USER->id;
                $eventdata['relateduserid'] = $userid;
                $eventdata['other'] = array();
                $eventdata['other']['examregid'] = $examregistrar->id;
                $eventdata['other']['examid'] = $booking['examid'];
                $eventdata['other']['booked'] = $booking['booked'];
                $eventdata['other']['bookedsite'] = $booking['bookedsite'];

                // Booking is already stored in database, this is a clearing
                $select = " userid = :userid AND examid = :examid AND id <> :id AND booked <> 0 ";
                $params = array('id'=>$newid, 'examid'=>$booking['examid'], 'userid'=>$userid);
                // only clear if needed, avoid extra logging messsge
                if($DB->record_exists_select('examregistrar_bookings', $select, $params)) {
                    $DB->set_field_select('examregistrar_bookings', 'timemodified', $now, $select, $params);
                    if($DB->set_field_select('examregistrar_bookings', 'booked', 0, $select, $params)) {
                        $event = \mod_examregistrar\event\booking_unbooked::create($eventdata);
                        $event->trigger();
                    }
                }
                
                // set the log for active booking after clearing others (store was done before)
                $event = \mod_examregistrar\event\booking_submitted::create($eventdata);
                $event->add_record_snapshot('examregistrar_bookings', $record);
                $event->trigger();
                
                // return the message 
                list($examname, $notused) = examregistrar_get_namecodefromid($record->examid, 'exams');
                $attend = new stdClass();
                $attend->take = core_text::strtoupper($record->booked ?  get_string('yes') :  get_string('no'));
                list($attend->site, $notused) = examregistrar_get_namecodefromid($record->bookedsite, 'locations', 'location');
                $vouchername = '';
                if(isset($voucher->id) && $voucher->id) {
                    $icon = new pix_icon('t/download', get_string('voucherdownld', 'examregistrar'), 'core', null); 
                    $vouchernum = str_pad($voucher->examregid, 4, '0', STR_PAD_LEFT).'-'.$voucher->uniqueid;
                    $downloadurl = new moodle_url('/mod/examregistrar/download.php', array('id' => $cm->id, 'down'=>'voucher', 'v'=>$vouchernum));
                    $vouchernum = $OUTPUT->action_link($downloadurl, $vouchernum, null, array('class'=>'voucherdownload'), $icon);
                    $vouchername = get_string('vouchernum', 'examregistrar',  $vouchernum);
                
                }
//                print_object(base_convert(crc32('fsdfasdffsfsdf58961wfqfwd fsd sfsfs'.$voucher->id), 10, 36));
                
                $message[$newid] = get_string('exam', 'examregistrar').' '.$examname.' '.get_string('takeonsite', 'examregistrar', $attend).' '.$vouchername; 
                
            }
            // there must be only one booking in one call in case several calls in a period
            if($booking['numcalls'] > 1 && $booking['booked']) {
                if($exam) {
                    $select = " examregid = :examregid AND annuality = :annuality AND courseid = :courseid
                                AND  period = :period AND examscope = :examscope AND id <> :id ";
                    $params = array('examregid'=>$exam->examregid, 'annuality'=>$exam->annuality, 'courseid'=>$exam->courseid,
                                    'period'=>$exam->period, 'examscope'=>$exam->examscope, 'id'=>$exam->id);
                    if($others = $DB->get_fieldset_select('examregistrar_exams', 'id',  $select, $params)) {
                        list($insql, $params) = $DB->get_in_or_equal($others);
                        $select = " examid $insql AND userid = ? AND booked <> 0 ";
                        $params[] = $userid;
                        // only clear if needed, avoid extra logging messsge
                        if($DB->record_exists_select('examregistrar_bookings', $select, $params)) {
                            $DB->set_field_select('examregistrar_bookings', 'timemodified', $now, $select, $params);
                            if($DB->set_field_select('examregistrar_bookings', 'booked', 0, $select, $params)) {
                                // log the action
                                $eventdata = array();
                                $eventdata['objectid'] = $newid;
                                $eventdata['context'] = $context;
                                $eventdata['userid'] = $USER->id;
                                $eventdata['relateduserid'] = $userid;
                                $eventdata['other'] = array();
                                $eventdata['other']['examregid'] = $examregistrar->id;
                                $eventdata['other']['examid'] = $booking['examid'];
                                $event = \mod_examregistrar\event\booking_unbooked::create($eventdata);
                                $event->trigger();
                            }
                        }
                    }
                }
            }
        }
    }

    if($errors) {
        foreach($errors as $key => $error) {
            $shortname = $error['shortname'];
            $errors[$key] = html_writer::span(get_string('bookingerror_'.$error['error'], 'examregistrar', $shortname), 'errorbox alert-error');
            unset($formdata->booking[$key]);
        }
        $message[] = '<p>'.implode('<br />', $errors).'</p>';
    }
}

if($message) {
    echo $output->box(get_string('changessaved'), ' generalbox messagebox success ');
    foreach($message as $mes) {
        echo $output->box($mes, ' generalbox messagebox centerbox centeredbox error ');
    }
    $url = new moodle_url($baseurl, $bookingparams);
    echo $output->continue_button($url);

} else {
    $mform->display();
}
