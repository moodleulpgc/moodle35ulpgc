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
 * Prints the interface for Quality control in seat/room assignment for each exam in a session
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/mod/examregistrar/locallib.php');
//require_once($CFG->dirroot."/mod/examregistrar/manage/action_forms.php");
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



$edit   = optional_param('edit', '', PARAM_ALPHANUMEXT);  // list/edit items
$action   = optional_param('action', '', PARAM_ALPHANUMEXT);
$session   = optional_param('session', 0, PARAM_INT);
$bookedsite   = optional_param('venue', '', PARAM_INT);


$baseurl = new moodle_url('/mod/examregistrar/manage.php', array('id' => $cm->id, 'edit'=>$edit));
$params =  array('id' => $cm->id, 'edit'=>$edit, 'session'=>$session, 'venue'=>$bookedsite);
$allocurl = new moodle_url('/mod/examregistrar/manage/assignseats.php', $params);
$actionurl = new moodle_url('/mod/examregistrar/manage/action.php', $params);
$actionurl->param('examsession', $session);

/// Set the page header
$PAGE->set_url($baseurl);
$PAGE->set_title(format_string($examregistrar->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
if($edit) {
    $baseurl->param('edit', $edit);
    $PAGE->navbar->add(get_string($edit, 'examregistrar'), $baseurl);
} else {
    $PAGE->navbar->add(get_string('manage', 'examregistrar'), $baseurl);
}
    $PAGE->navbar->add(get_string('assignseats', 'examregistrar'), null);

$examregprimaryid = examregistrar_get_primaryid($examregistrar);

require_capability('mod/examregistrar:manageseats',$context);

/// check permissions
$caneditelements = has_capability('mod/examregistrar:editelements',$context);
$canmanageperiods = has_capability('mod/examregistrar:manageperiods',$context);
$canmanageexams = has_capability('mod/examregistrar:manageexams',$context);
$canmanagelocations = has_capability('mod/examregistrar:managelocations',$context);
$canmanageseats = has_capability('mod/examregistrar:manageseats',$context);
$canmanage = $caneditelements || $canmanageperiods || $canmanageexams || $canmanagelocations || $canmanageseats;


///////////////////////////////////////////////////////////////////////////////
/// process forms actions




////////////////////////////////////////////////////////////////////////////////



////////////////////////////////////////////////////////////////////////////////

add_to_log($course->id, 'examregistrar', 'manage quality control', "manage.php?id={$cm->id}&edit=qualitycontrol", $examregistrar->name, $cm->id);

/// Print the page header, Output starts here
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('qualitycontrol', 'examregistrar'));


    echo $OUTPUT->container_start('examregistrarmanagefilterform clearfix ');
        echo $OUTPUT->single_button($baseurl, get_string('clearfilter', 'examregistrar'), 'get', array('class'=>' clearfix '));

        echo '<form id="examregistrarperiodsform" action="'.$CFG->wwwroot.'/mod/examregistrar/manage/qualitycontrol.php" method="post">'."\n";
        echo '<input type="hidden" name="edit" value="'.$edit.'" />'."\n";
        echo '<input type="hidden" name="id" value="'.$cm->id.'" />'."\n";

        $sessionmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'sessions', 'sessionitem', $examregprimaryid, 'choose');
        echo html_writer::label(get_string('sessionitem', 'examregistrar').': ', 'session');
        echo html_writer::select($sessionmenu, "session", $session);
        echo ' &nbsp; ';

        $parentmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'locations', 'locationitem', $examregprimaryid, 'choose', '', array('locationtype'=>'CITY'));
        echo html_writer::label(get_string('parent', 'examregistrar').': ', 'venue');
        echo html_writer::select($parentmenu, "venue", $bookedsite);
        echo ' &nbsp; ';

        echo '<input type="submit" value="'.get_string('filter', 'examregistrar').'" />'."\n";
        echo '</form>'."\n";
    echo $OUTPUT->container_end();


$strunallocate = get_string('unallocate', 'examregistrar');


echo $OUTPUT->container_start('examregallocation clearfix ');

    echo $OUTPUT->heading(get_string('qcbookingsnonallocated', 'examregistrar'));



    $sql = "SELECT COUNT(le.id)
                FROM {examregistrar_exams} e
                JOIN lugar_examenes le ON le.codas=e.shortname AND le.titulacion=e.programme
                                            AND le.aacada=e.annuality AND le.conv=e.conv AND le.parcial=e.parcial
                WHERE e.examsession = :examsession AND le.lugar = 'Gran Canaria' AND registered = 1 AND e.callnum > 0
            ";
    $params = array('examsession'=>$session);
    $totalbooked1 = $DB->count_records_sql($sql, $params);

    $sql = "SELECT COUNT(b.id)
                FROM {examregistrar_bookings} b
                JOIN {examregistrar_exams} e ON e.id = b.examid AND e.examsession = :examsession
                WHERE b.booked = 1 AND b.locationid = :bookedsite ";
    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite );
    $totalbooked = $DB->count_records_sql($sql, $params);

    $select = " examsession = :examsession AND  bookedsite = :bookedsite AND locationid > 0";
    $totalseated = $DB->count_records_select('examregistrar_session_seats', $select, $params);

    $class = ($totalbooked1 > $totalseated) ? ' error notify ' : '';
    echo html_writer::span(get_string('totalseated', 'examregistrar', $totalseated).
                           ' / '.get_string('totalbooked', 'examregistrar', $totalbooked).
                           ' / '.get_string('totalbooked', 'examregistrar', $totalbooked1), $class);
    echo '<br />';

    $sql = "SELECT b.id as bid, b.userid, b.locationid, b.booked, u.firstname, u.lastname, u.idnumber, e.*
                FROM {examregistrar_bookings} b
                JOIN {examregistrar_exams} e ON e.id = b.examid AND e.examsession = :session1
                JOIN {user} u ON u.id = b.userid
                WHERE b.booked = 1 AND b.locationid = :bookedsite1
                AND NOT EXISTS (SELECT 1
                                    FROM {examregistrar_session_seats} ss
                                    WHERE ss.userid = b.userid AND ss.examid = b.examid AND ss.bookedsite = b.locationid
                                    AND ss.examsession = :session2 AND ss.bookedsite = :bookedsite2  AND ss.locationid > 0 )
                ";
    $params = array('session1'=>$session, 'session2'=>$session, 'bookedsite1'=>$bookedsite, 'bookedsite2'=>$bookedsite);
    $bookinsgnotallocated = $DB->get_records_sql($sql, $params);
    $class = $bookinsgnotallocated ? ' error notify ' : '';
    echo html_writer::span(get_string('countbookingsnonallocated', 'examregistrar', count($bookinsgnotallocated)), $class);

    foreach($bookinsgnotallocated as $bid => $booking) {
        $bookinsgnotallocated[$bid] = fullname($booking).' - '.$booking->idnumber.',  - '.$booking->shortname.'  - '.$booking->id;
    }
    echo html_writer::alist($bookinsgnotallocated);




    echo $OUTPUT->heading(get_string('qcexamsnonallocated', 'examregistrar'));

    $sql = "SELECT DISTINCT(e.id) as eid, e.programme, c.*
                FROM {examregistrar_exams} e
                JOIN {course} c ON c.id = e.courseid
                LEFT JOIN {examregistrar_bookings} b ON b.examid = e.id
                WHERE  e.examsession = :session1 AND b.booked = 1 AND b.locationid = :bookedsite1
                AND NOT EXISTS (SELECT 1
                                    FROM {examregistrar_session_seats} ss
                                    WHERE ss.userid = b.userid AND ss.examid = e.id AND ss.bookedsite = b.locationid
                                    AND ss.examsession = :session2 AND ss.bookedsite = :bookedsite2 AND ss.locationid > 0 )
                ";
    //$examsnotallocated = $DB->count_records_sql($sql, $params);
    $examsnotallocated = $DB->get_records_sql($sql, $params);
    $class = $examsnotallocated ? ' error notify ' : '';
    echo html_writer::span(get_string('countexamsnonallocated', 'examregistrar', count($examsnotallocated)), $class);
    foreach($examsnotallocated as $eid => $exam) {
        $examsnotallocated[$eid] = $exam->programme.' - '.$exam->shortname;
    }
    echo html_writer::alist($examsnotallocated);


    $url = new moodle_url($baseurl, array('id' => $cm->id, 'edit'=>'exams', 'ssession'=>$session, 'venue'=>$bookedsite, 'sbooked'=>'notbooked'));
    echo html_writer::link($url, get_string('nonbookedexams', 'examregistrar'));


    echo $OUTPUT->heading(get_string('qcroomsnonstaffed', 'examregistrar'));

    $sql = "SELECT DISTINCT(sr.id) as srid, l.idnumber
                FROM {examregistrar_session_rooms} sr
                JOIN {examregistrar_locations} l ON sr.locationid = l.id
                WHERE sr.examsession = :session
                AND EXISTS (SELECT 1
                                FROM {examregistrar_session_seats} ss
                                WHERE ss.examsession = sr.examsession AND ss.locationid = sr.locationid AND sr.available = 1)

                AND NOT EXISTS (SELECT 1
                                    FROM {examregistrar_staffers} s
                                    WHERE s.examsession = sr.examsession AND s.locationid = sr.locationid AND s.visible = 1 AND sr.available = 1)
                ";
    $params = array('session'=>$session);
    $roomsnonstaffed = $DB->get_records_sql_menu($sql, $params);
    $class = $roomsnonstaffed ? ' error notify ' : '';
    echo html_writer::span(get_string('countroomsnonstaffed', 'examregistrar', count($roomsnonstaffed)), $class);
    echo html_writer::alist($roomsnonstaffed);

    $url = new moodle_url($baseurl, array('id' => $cm->id, 'edit'=>'session_rooms', 'ssession'=>$session, 'venue'=>$bookedsite));
    echo html_writer::link($url, get_string('session_rooms', 'examregistrar'));

    echo $OUTPUT->heading(get_string('qcstaffnonallocated', 'examregistrar'));

    $courseids = $DB->get_fieldset_select('examregistrar_exams', 'courseid', ' courseid <> 0 AND  examsession = ? ', array($session));
    $users = array();
    foreach($courseids as $courseid) {
        $coursecontext = context_course::instance($courseid);
        $managers = get_enrolled_users($coursecontext, 'moodle/course:manageactivities', 0, 'u.id, u.firstname, u.lastname, u.idnumber, u.picture', ' u.lastname ASC ');
        foreach($managers as $uid => $user) {
            if(!isset($users[$uid]) && !$DB->record_exists('examregistrar_staffers', array('examsession'=>$session, 'userid'=>$uid, 'visible'=>1))) {
                $users[$uid] = $user;
            }
        }
    }

    $staffnonallocated = count($users);
    $class = $staffnonallocated ? ' error notify ' : '';
    echo html_writer::span(get_string('countstaffnonallocated', 'examregistrar', $staffnonallocated), $class);
    foreach($users as $uid => $user) {
        $users[$uid] = fullname($user);
    }
    echo html_writer::alist($users);


echo $OUTPUT->container_end();






echo $OUTPUT->footer();
