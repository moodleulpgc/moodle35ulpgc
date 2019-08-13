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
 * Prints the interface for seat/room assignment for each exam in a session
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
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$usort = optional_param('usort', '', PARAM_ALPHANUMEXT);
$rsort = optional_param('rsort', '', PARAM_ALPHANUMEXT);

$baseurl = new moodle_url('/mod/examregistrar/manage.php', array('id' => $cm->id, 'edit'=>$edit));
$baseurl = new moodle_url('/mod/examregistrar/manage.php', array('id' => $cm->id, 'tab'=>'session'));
$params =  array('id' => $cm->id, 'edit'=>$edit, 'session'=>$session, 'venue'=>$bookedsite);
$allocurl = new moodle_url('/mod/examregistrar/manage/assignseats.php', $params);
$actionurl = new moodle_url('/mod/examregistrar/manage/action.php', $params);
$actionurl->param('examsession', $session);

$courseurl = new moodle_url('/course/search.php');

$examregprimaryid = examregistrar_get_primaryid($examregistrar);
$examregistrar->config = examregistrar_get_instance_configdata($examregistrar);

/// Set the page header
$PAGE->set_url($baseurl);
$PAGE->set_title(format_string($examregistrar->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_activity_record($examregistrar);

$output = $PAGE->get_renderer('mod_examregistrar');
if($edit) {
    //$baseurl->param('edit', $edit);
    $PAGE->navbar->add(get_string($edit, 'examregistrar'), $baseurl);
} else {
    $PAGE->navbar->add(get_string('managesession', 'examregistrar'), $baseurl);
}
    $PAGE->navbar->add(get_string('assignseats', 'examregistrar'), null);

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

if($action) {
    if(!$confirm) {
        /// ask confirmation
        $PAGE->navbar->add(get_string('confirm_'.$action, 'examregistrar'), null);
        $confirmurl = new moodle_url($allocurl, array('action'=>$action, 'confirm' => 1));
        $cancelurl = new moodle_url($allocurl, array('action'=>'', 'confirm' => 0));
        $message = get_string('allocateconfirm', 'examregistrar', $action);
        echo $output->header();
        echo $output->confirm($message, $confirmurl, $cancelurl);
        echo $output->footer();
        die;
    } elseif(confirm_sesskey()) {
        /// do action
        $allocation = data_submitted();
        ////print_object($allocation);
        //print_object("  -- allocation ----");
        if($action == 'unallocateall') {
            $DB->set_field('examregistrar_session_seats', 'roomid', 0, array('examsession'=>$session, 'bookedsite'=>$bookedsite));
        } elseif($action == 'refreshallocation') {
           examregistrar_session_seats_makeallocation($session, $bookedsite);
        } elseif($action == 'assignseats') {
            if($allocation->numusers && $allocation->fromexam && ($allocation->fromroom != $allocation->toroom)) {
                $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite,
                                'examid'=>$allocation->fromexam, 'roomid'=>$allocation->fromroom, 'additional'=>0 );
                $sort = ($allocation->fromroom) ? ' id DESC ' : ' id ASC';
                examregistrar_update_usersallocations($session, $bookedsite, $params, $allocation->toroom, $sort, 0, $allocation->numusers);
                /*
                if($users = $DB->get_records_menu('examregistrar_session_seats', $params, $sort, 'id, userid', 0, $allocation->numusers)) {
                    list($insql, $inparams) = $DB->get_in_or_equal(array_keys($users));
                    $select = " id $insql ";
                    $DB->set_field_select('examregistrar_session_seats', 'roomid', $allocation->toroom, $select, $inparams);
                }
                    /// TODO TODO check for new extras al cambiar de room
                    */
            }
        } else {
            $room = optional_param('room', 0, PARAM_INT);
            $exam = optional_param('exam', 0, PARAM_INT);
            if($action == 'emptyroom') {
                if($room) {
                    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite,
                                        'roomid'=>$room);
                    examregistrar_update_usersallocations($session, $bookedsite, $params, 0);
                    //$DB->set_field('examregistrar_session_seats', 'roomid', 0, $params);
                }
            } elseif($action == 'emptyexam') {
                if($exam) {
                    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite,
                                        'examid'=>$exam, 'additional'=>0);
                    examregistrar_update_usersallocations($session, $bookedsite, $params, 0);
                    //$DB->set_field('examregistrar_session_seats', 'roomid', 0, $params);
                }
            } elseif($action == 'allocateexam') {
                if($room && $exam) {
                    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite,
                                        'roomid'=>0, 'examid'=>$exam, 'additional'=>0);
                    examregistrar_update_usersallocations($session, $bookedsite, $params, $room);
                    /*
                    $sort = ' id ASC ';
                    if($users = $DB->get_records_menu('examregistrar_session_seats', $params, $sort, 'id, userid')) {
                        list($insql, $inparams) = $DB->get_in_or_equal(array_keys($users));
                        $select = " id $insql ";
                        $DB->set_field_select('examregistrar_session_seats', 'roomid', $room, $select, $inparams);
                    }*/
                    //$DB->set_field('examregistrar_session_seats', 'roomid', $room, $params);
                }
            } elseif($action == 'allocateexams') {
                if($room && $exams = optional_param_array('exams', array(), PARAM_INT)) {
                    foreach($exams as $examid) {
                        if($room == -1) {
                            $sql = "SELECT roomid, (l.seats - COUNT(userid)) as freeseats
                                    FROM {examregistrar_session_seats} ss
                                    JOIN {examregistrar_locations} l ON l.id = ss.roomid
                                    WHERE ss.examsession = :examsession AND ss.bookedsite = :bookedsite AND ss.examid = :examid AND ss.additional = 0
                                    GROUP BY roomid
                                    HAVING freeseats > -2 ";
                            $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'examid'=>$examid);
                            if($rooms = $DB->get_records_sql_menu($sql, $params)) {
                                natcasesort($rooms);
                                end($rooms);
                                $room = key($rooms);
                            }
                        }
                        if($room > 0) {
                            $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite,
                                                'roomid'=>0, 'examid'=>$examid, 'additional'=>0);
                            examregistrar_update_usersallocations($session, $bookedsite, $params, $room);
                        }
                        /*
                        $sort = ' id ASC ';
                        if($users = $DB->get_records_menu('examregistrar_session_seats', $params, $sort, 'id, userid')) {
                            list($insql, $inparams) = $DB->get_in_or_equal(array_keys($users));
                            $select = " id $insql ";
                            $DB->set_field_select('examregistrar_session_seats', 'roomid', $room, $select, $inparams);
                        }*/
                    //$DB->set_field('examregistrar_session_seats', 'locationid', $room, $params);
                    }
                }
            } elseif($action == 'unallocateexam') {
                if($room && $exam) {
                    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite,
                                        'roomid'=>$room, 'examid'=>$exam);
                    examregistrar_update_usersallocations($session, $bookedsite, $params, 0);
                    //$DB->set_field('examregistrar_session_seats', 'locationid', 0, $params);
                }
            }
        }
    }
}





////////////////////////////////////////////////////////////////////////////////


/// build working data

$allocatedrooms = array();
$unallocatedexams = array();
$roomexams = array();
$teachers = array();
$examteachers = array();
$teachernames = array();

if($session && $bookedsite) {
    $userbooks = 0;
    if(!$max = $DB->get_records('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite),
                                     ' timemodified DESC ', '*', 0, 1)) {
        examregistrar_session_seats_makeallocation($session, $bookedsite);
    } else {
        $lasttime = reset($max)->timecreated;
        examregistrar_session_seats_newbookings($session, $bookedsite, $lasttime+1);
    }

    /// rooms
    $sort = '';
    if($rsort) {
        $sort = " $rsort "; // seats/booked/free
        if($rsort == 'freeseats' || $rsort == 'seats') {
            $sort .= ' DESC';
        }
        $sort .= ', ';
    }
//     $sql = "SELECT l.id, el.name, el.idnumber,  l.seats, COUNT(ss.id) AS booked, (l.seats - COUNT(ss.id)) AS freeseats
//                 FROM {examregistrar_locations} l
//                 JOIN {examregistrar_elements} el ON l.examregid = el.examregid AND el.type = 'locationitem' AND l.location = el.id
//                 JOIN {examregistrar_session_rooms} sr ON l.id = sr.roomid AND sr.examsession = :examsession1
//                 LEFT JOIN {examregistrar_session_seats} ss ON l.id = ss.roomid AND ss.examsession = :examsession2 AND ss.bookedsite = :bookedsite1 AND ss.additional = 0
//                 WHERE l.seats > 0
//                 GROUP BY l.id
//                 ORDER BY $sort name ASC ";

    $sql = "SELECT l.id, el.name, el.idnumber,  l.seats, COUNT(ss.id) AS booked, (l.seats - COUNT(ss.id)) AS freeseats
                FROM {examregistrar_locations} l
                JOIN {examregistrar_elements} el ON l.examregid = el.examregid AND el.type = 'locationitem' AND l.location = el.id
                JOIN {examregistrar_session_rooms} sr ON l.id = sr.roomid AND sr.examsession = :examsession AND sr.available = 1
                LEFT JOIN {examregistrar_session_seats} ss ON sr.roomid = ss.roomid AND ss.examsession = sr.examsession AND ss.bookedsite = sr.bookedsite AND ss.additional = 0
                WHERE l.seats > 0 AND sr.bookedsite = :bookedsite
                GROUP BY l.id
                ORDER BY $sort name ASC ";
//     $allocatedrooms = $DB->get_records_sql($sql, array('examsession1'=>$session, 'examsession2'=>$session, 'examsession3'=>$session,
//                                                        'bookedsite1'=>$bookedsite, 'bookedsite2'=>$bookedsite));
    $allocatedrooms = $DB->get_records_sql($sql, array('examsession'=>$session, 'bookedsite'=>$bookedsite));


    $sort = '';
    if($usort) {
        $sort = " $usort ";
        if($usort == 'booked') {
            $sort .= ' DESC';
        }
        $sort .= ', ';
    }
    $sql = "SELECT ss.examid, e.programme, e.courseid, c.shortname, c.fullname, e.callnum, COUNT(ss.userid) AS booked,
                    (SELECT COUNT(b.userid)
                      FROM {examregistrar_bookings} b
                      WHERE b.examid = ss.examid AND b.bookedsite = ss.bookedsite AND b.booked = 1
                      GROUP BY b.examid
                    ) AS totalbooked
                FROM {examregistrar_session_seats} ss
                JOIN {examregistrar_exams} e ON ss.examid = e.id
                JOIN {course} c ON e.courseid = c.id
                WHERE ss.examsession = :examsession AND ss.bookedsite = :bookedsite AND ss.roomid = 0 AND ss.additional = 0
                GROUP BY ss.examid
                ORDER BY  $sort e.programme, c.shortname";

    $unallocatedexams = $DB->get_records_sql($sql, array('examsession'=>$session, 'bookedsite'=>$bookedsite));

    $sql = "SELECT ss.examid, e.programme, c.shortname, c.fullname, COUNT(ss.userid) AS booked
                FROM {examregistrar_session_seats} ss
                JOIN {examregistrar_exams} e ON ss.examid = e.id
                JOIN {course} c ON e.courseid = c.id
                WHERE ss.examsession = :examsession AND ss.bookedsite = :bookedsite AND ss.roomid > 0 AND ss.additional = 0
                GROUP BY ss.examid ";
    $allocatedexams = $DB->get_records_sql($sql, array('examsession'=>$session, 'bookedsite'=>$bookedsite));

    foreach($unallocatedexams as $eid => $exam) {
        $allocated = 0;
        $exam = $unallocatedexams[$eid];
        if(isset($allocatedexams[$eid])) {
            $allocated = $allocatedexams[$eid]->booked;
        }
        $exam->allocated = $allocated;
        $exam->teachers = examregistrar_get_teachers($exam->courseid);
        foreach($exam->teachers as $userid => $name) {
            if(!isset($examteachers[$userid])) {
                $examteachers[$userid] = array();
            }
            $examteachers[$userid][] = $eid;
            $teachernames[$userid] = $name;
        }
        $unallocatedexams[$eid] = $exam;
    }
    //unset($allocatedexams);
} // end of building data

////////////////////////////////////////////////////////////////////////////////

add_to_log($course->id, 'examregistrar', 'manage assignseats', "manage.php?id={$cm->id}&edit=assignseats", $examregistrar->name, $cm->id);

/// Print the page header, Output starts here
echo $output->header();
echo $output->heading(get_string('assignseats', 'examregistrar'));

    echo $output->container_start('examregistrarmanagefilterform clearfix ');
        echo $output->single_button($baseurl, get_string('clearfilter', 'examregistrar'), 'get', array('class'=>' clearfix '));

        echo '<form id="examregistrarperiodsform" action="'.$CFG->wwwroot.'/mod/examregistrar/manage/assignseats.php" method="post">'."\n";
        echo '<input type="hidden" name="edit" value="'.$edit.'" />'."\n";
        echo '<input type="hidden" name="id" value="'.$cm->id.'" />'."\n";
        echo '<input type="hidden" name="rsort" value="'.$rsort.'" />'."\n";
        echo '<input type="hidden" name="usort" value="'.$usort.'" />'."\n";

        $sessionmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'examsessions', 'examsessionitem', $examregprimaryid, 'choose');
        echo html_writer::label(get_string('examsessionitem', 'examregistrar').': ', 'session');
        echo html_writer::select($sessionmenu, "session", $session);
        echo ' &nbsp; ';

        $venueelement = examregistrar_get_venue_element($examregistrar);
        $venuemenu = examregistrar_get_referenced_namesmenu($examregistrar, 'locations', 'locationitem', $examregprimaryid, 'choose', '', array('locationtype'=>$venueelement));
        echo html_writer::label(get_string('venue', 'examregistrar').': ', 'venue');
        echo html_writer::select($venuemenu, "venue", $bookedsite);
        echo ' &nbsp; ';

        echo '<input type="submit" value="'.get_string('filter', 'examregistrar').'" />'."\n";
        echo '</form>'."\n";
    echo $output->container_end();


echo html_writer::empty_tag('hr');

if($roomid = examregistrar_is_venue_single_room($bookedsite)) {
    echo $output->box(get_string('singleroommessage', 'examregistrar'), 'generalbox error', 'notice');
    echo $output->footer();
    die;
    //notice('singleroommessage', 'examregistrar');
    /// this functions dies script. Ends here
}

/// If we are here then bookedsite is not a single room venue
echo $output->container_start('examregallocation clearfix ');

echo $output->container_start('examregallocationcontrolsleft  ');

    echo '<form id="examregistrarassigseatsform" action="'.$CFG->wwwroot.'/mod/examregistrar/manage/assignseats.php" method="post">'."\n";
    echo '<input type="hidden" name="edit" value="'.$edit.'" />'."\n";
    echo '<input type="hidden" name="id" value="'.$cm->id.'" />'."\n";
    echo '<input type="hidden" name="action" value="assignseats" />'."\n";
    echo '<input type="hidden" name="session" value="'.$session.'" />'."\n";
    echo '<input type="hidden" name="venue" value="'.$bookedsite.'" />'."\n";
    echo '<input type="hidden" name="rsort" value="'.$rsort.'" />'."\n";
    echo '<input type="hidden" name="usort" value="'.$usort.'" />'."\n";
    echo '<input type="hidden" name="confirm" value="1" />'."\n";
    echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />'."\n";

    $numusers = array(0,1,2,3);
    echo html_writer::label(get_string('moveusers', 'examregistrar'), 'numusers', false, array('class' => 'accesshidexx'));
    //echo html_writer::select($numusers, "numusers");
    //echo html_writer::select($numusers, "numusers");
    echo html_writer::empty_tag('input', array('name'=>'numusers', 'type'=>'text', 'value'=>1, 'size'=>3));
    echo get_string('students').'      ';

    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite);
    $menu = examregistrar_get_referenced_examsmenu($examregistrar, 'session_seats', $params, $examregprimaryid);
    echo html_writer::label(get_string('fromexam', 'examregistrar'), 'fromexam', false, array('class' => 'accesshidexx'));
    echo html_writer::select($menu, "fromexam");
    echo "<br / >\n";

    $menu = examregistrar_get_referenced_roomsmenu($examregistrar, 'session_seats', $params, $examregprimaryid, 'unallocated');
    echo html_writer::label(get_string('fromroom', 'examregistrar'), 'fromroom', false, array('class' => 'accesshidexx'));
    echo html_writer::select($menu, "fromroom");
    echo '      ';

    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'available'=>1);
    $menu = examregistrar_get_referenced_roomsmenu($examregistrar, 'session_rooms', $params, $examregprimaryid, 'unallocated');
    echo html_writer::label(get_string('toroom', 'examregistrar'), 'toroom', false, array('class' => 'accesshidexx'));
    echo html_writer::select($menu, "toroom");

    echo "<br / >\n";

    echo '<input type="submit" value="'.get_string('makeallocation', 'examregistrar').'" />'."\n";
    echo '</form>'."\n";

echo $output->container_end();

echo $output->container_start('examregallocationcontrolsright  ');

        $allocurl->param('action', 'refreshallocation');
        echo $output->single_button($allocurl, get_string('refreshallocation', 'examregistrar'), 'post', array('class'=>' clearfix '));
        $allocurl->param('action', 'unallocateall');
        echo $output->single_button($allocurl, get_string('unallocateall', 'examregistrar'), 'post', array('class'=>' clearfix '));
        $uploadurl = new moodle_url('/mod/examregistrar/manage/upload.php', array('id' => $cm->id, 'edit'=>'assignseats'));
        //$actionurl = new moodle_url('/mod/examregistrar/manage/action.php', array('id' => $cm->id, 'edit'=>$edit, 'examsession'=>$session, 'venue'=>$bookedsite));
        $actionurl->param('action', 'stafffromexam');
        echo $output->single_button($actionurl, get_string('stafffromexam', 'examregistrar'), 'post', array('class'=>' clearfix '));
        $actionurl->param('action', 'seatstudent');
        echo $output->single_button($actionurl, get_string('seatstudent', 'examregistrar'), 'post', array('class'=>' clearfix '));

echo $output->container_end();

echo $output->container_start('examregallocationcontrolsright  ');

    $uploadurl = new moodle_url('/mod/examregistrar/manage/upload.php', array('id' => $cm->id, 'edit'=>'assignseats'));
    $uploadurl->param('csv', 'assignseats');
    echo $output->single_button($uploadurl, get_string('uploadcsvassignseats', 'examregistrar'), 'post', array('class'=>' clearfix '));

    $downloadurl = new moodle_url('/mod/examregistrar/download.php', array('id' => $cm->id, 'edit'=>'assignseats',
                                                                            'session'=>$session, 'venue'=>$bookedsite));
    $downloadurl->param('down', 'assignseats');
    echo $output->single_button($downloadurl, get_string('downloadassignseats', 'examregistrar'), 'post', array('class'=>' clearfix '));

echo $output->container_end();

echo $output->container('', ' clearfix ');
echo html_writer::empty_tag('hr');
echo $output->container_end();

$strunallocate = get_string('unallocate', 'examregistrar');
$strteachers = get_string('teachers', 'examregistrar');


echo $output->container_start('examregallocation clearfix ');

/// the left part containing room list with assigned exams in them
echo $output->container_start('examregroomallocatedseats ');
echo $output->heading(get_string('allocatedrooms', 'examregistrar'));
$params =  array('id' => $cm->id, 'edit'=>$edit, 'session'=>$session, 'venue'=>$bookedsite, 'confirm'=>1, 'sesskey'=>sesskey());
$allocurl = new moodle_url('/mod/examregistrar/manage/assignseats.php', $params);

$totalroomseated = 0;

if($allocatedrooms) {

    // form for ordering
    $sorting = array(''=>get_string('sortroomname', 'examregistrar'),
                     'seats'=>get_string('sortseats', 'examregistrar'),
                     'freeseats'=>get_string('sortfreeseats', 'examregistrar'),
                     'booked'=>get_string('sortbooked', 'examregistrar'));
    $allocurl->param('usort', $usort);
    $select = new single_select($allocurl, 'rsort', $sorting, $rsort, '');
    $select->set_label(get_string('sortby', 'examregistrar'));
    echo $output->render($select);



    foreach($allocatedrooms as $room) {

        $roomname = $room->name.'('.$room->idnumber.')';  ;
        $totalseats = $room->seats; // examregistrar_get_roomseats()
        $allocatedseats = $room->booked; // // examregistrar_get_allocatedseats()
        /// TODO add formatting to numbers red if over
        $free = $totalseats - $allocatedseats;
        $busyalloc = "&nbsp;&nbsp;  $allocatedseats / $totalseats ";
        if($free < 1 ) {
            $busyalloc = html_writer::span($busyalloc, ' busyalloc ');
            $freealloc = '';
        } else {
            $freealloc = '&nbsp;'.html_writer::span(get_string('freeseats', 'examregistrar', $free), ' freealloc ');
        }
        $icon = '';
        if($allocatedseats > 0) {
            $allocurl->params(array('action'=>'emptyroom', 'room'=>$room->id));
            $icon = '&nbsp;&nbsp;'.html_writer::link($allocurl, $output->pix_icon('t/delete', $strunallocate, 'moodle', array('class'=>'iconsmall', 'title'=>$strunallocate)));
        }
        $roomname .= $busyalloc.$freealloc.$icon;

        $sql = "SELECT ss.examid, e.programme, e.courseid, c.shortname, c.fullname, e.callnum, COUNT(ss.userid) AS booked,
                        (SELECT COUNT(b.userid)
                        FROM {examregistrar_bookings} b
                        WHERE b.examid = ss.examid AND b.bookedsite = ss.bookedsite  AND b.booked = 1
                        GROUP BY b.examid
                        ) AS totalbooked
                    FROM {examregistrar_session_seats} ss
                    JOIN {examregistrar_exams} e ON ss.examid = e.id
                    JOIN {course} c ON e.courseid = c.id
                    WHERE ss.examsession = :examsession AND ss.bookedsite = :bookedsite AND ss.roomid = :roomid AND ss.additional = 0
                    GROUP BY ss.examid
                    ORDER BY e.programme, c.shortname ";
        $roomexams = $DB->get_records_sql($sql, array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'roomid'=>$room->id));

        if($roomexams) {
            $staff = '';
            if($staffers = examregistrar_get_room_staffers($room->id, $session)) {
                $stafferstr = format_string(examregistrar_format_room_staffers($staffers, $baseurl, $examregprimaryid, true));
                $stafferstr = trim(format_string(trim($stafferstr)));
                $stafficon = 'users';
            } else {
                $stafferstr = get_string('roomstaffers', 'examregistrar');
                $stafficon = 'assignroles';
            }
            $url = new moodle_url('/mod/examregistrar/manage/assignroomstaffers.php',
                            array('id'=>$cm->id, 'action'=>'roomstaffers', 'edit'=>$edit, 'session'=>$session, 'room'=>$room->id));
            $staff = '&nbsp;&nbsp;'.html_writer::link($url, $output->pix_icon('i/'.$stafficon, $strunallocate, 'moodle', array('class'=>'iconmedium', 'title'=>$stafferstr)));
            $roomname .= $staff;
        }

        echo html_writer::empty_tag('hr');
        echo $output->heading($roomname, 3, ' leftalign ');
        if($roomexams) {
            $numexams = count($roomexams);
            echo get_string('exams', 'examregistrar').': '.$numexams;
            $examitems = array();
            foreach ($roomexams as $exam) {
                $star = ($exam->callnum < 0) ? '**' : '';
                $allocurl->params(array('action'=>'unallocateexam', 'exam'=>$exam->examid));
                $icon = html_writer::link($allocurl, $output->pix_icon('t/delete', $strunallocate, 'moodle', array('class'=>'iconsmall', 'title'=>$strunallocate)));
                $exam->teachers = examregistrar_get_teachers($exam->courseid);
                foreach($exam->teachers as $userid => $name) {
                    if(!isset($examteachers[$userid])) {
                        $examteachers[$userid] = array();
                    }
                    $examteachers[$userid][] = $exam->examid;
                    $teachernames[$userid] = $name;
                }
                $teachers = $output->pix_icon('userchecked', $strteachers, 'mod_examregistrar' , array('title'=>implode(" \n", $exam->teachers),'class'=>'iconmedium'));
                $courseurl->param('search', $exam->shortname);
                $examname = html_writer::link($courseurl,"{$exam->programme}-{$exam->shortname}-{$exam->fullname}");
                $examitems[] = "({$exam->booked}/[{$exam->totalbooked}]) ".$teachers."$star{$examname}  $icon";
                $totalroomseated += $exam->booked;
            }
            echo html_writer::alist($examitems, array('class'=>'roomexamlist'));

            $sql = "SELECT ss.id, ss.examid, ss.userid, e.programme, e.courseid, c.shortname, c.fullname, e.callnum
                        FROM {examregistrar_session_seats} ss
                        JOIN {examregistrar_exams} e ON ss.examid = e.id
                        JOIN {course} c ON e.courseid = c.id
                        WHERE ss.examsession = :examsession AND ss.bookedsite = :bookedsite AND ss.roomid = :roomid AND ss.additional > 0
                        ORDER BY e.programme, c.shortname ";
            if($additionalexams = $DB->get_records_sql($sql, array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'roomid'=>$room->id))) {
                //print_object($additionalexams);
                $users = array();
                $exams = array();
                $booked = array();
                foreach($additionalexams as $exam) {
                    if(!isset($users[$exam->userid])) {
                        $users[$exam->userid] = 1;
                    }
                    if(!isset($exams[$exam->examid])) {
                        $booked[$exam->examid] = 1;
                        $star = ($exam->callnum < 0) ? '**' : '';
                        $courseurl->param('search', $exam->shortname);
                        $exam->teachers = examregistrar_get_teachers($exam->courseid);
                        $teachers = $output->pix_icon('userchecked', $strteachers, 'mod_examregistrar', array('title'=>implode(" \n", $exam->teachers), 'class'=>'iconmedium'));
                        $examname = html_writer::link($courseurl,"{$exam->programme}-{$exam->shortname}-{$exam->fullname}");
                        $exams[$exam->examid] = $teachers."$star{$examname}";
                    } else {
                        $booked[$exam->examid] += 1;
                    }
                }
                foreach($exams as $eid => $exam) {
                    $exams[$eid] = "({$booked[$eid]}) ".$exam;
                }
                $info = new stdClass;
                $info->users = count($users);
                $info->exams = count($exams);
                echo get_string('additionalusersexams', 'examregistrar', $info);
                echo html_writer::alist($exams, array('class'=>'additionalexam')); // (items, array $attributes = null, $tag = 'ul')

            }
            echo html_writer::empty_tag('hr');
        }
    }
}


echo $output->container_end();


/// the rigth part containing unallocated exams
echo $output->container_start(' examregunallocatedexams ');
echo $output->heading(get_string('unallocatedexams', 'examregistrar'));

$totalbooked = 0;
$totalseated = 0;

if($unallocatedexams) {

    // form for ordering
    $allocurl->param('rsort', $rsort);
    $sorting = array(''=>get_string('sortprogramme', 'examregistrar'),
                     'fullname'=>get_string('sortfullname', 'examregistrar'),
                     'booked'=>get_string('sortbooked', 'examregistrar'));
    $select = new single_select($allocurl, 'usort', $sorting, $usort, '');
    $select->set_label(get_string('sortby', 'examregistrar'));
    echo $output->render($select);

    echo '<form id="examregistrarassigseatsform" action="'.$CFG->wwwroot.'/mod/examregistrar/manage/assignseats.php" method="post">'."\n";
    echo '<input type="hidden" name="edit" value="'.$edit.'" />'."\n";
    echo '<input type="hidden" name="id" value="'.$cm->id.'" />'."\n";
    echo '<input type="hidden" name="action" value="allocateexams" />'."\n";
    echo '<input type="hidden" name="session" value="'.$session.'" />'."\n";
    echo '<input type="hidden" name="venue" value="'.$bookedsite.'" />'."\n";
    echo '<input type="hidden" name="rsort" value="'.$rsort.'" />'."\n";
    echo '<input type="hidden" name="usort" value="'.$usort.'" />'."\n";
    echo '<input type="hidden" name="confirm" value="1" />'."\n";
    echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />'."\n";

    $examitems = array();
    foreach($unallocatedexams as $exam) {
        $icon = '';
        if($exam->allocated) {
            $allocurl->params(array('action'=>'emptyexam', 'exam'=>$exam->examid));
            $icon = html_writer::link($allocurl, $output->pix_icon('t/delete', $strunallocate, 'moodle', array('class'=>'iconsmall', 'title'=>$strunallocate)));
        }
        $checkbox = html_writer::checkbox('exams['.$exam->examid.']', $exam->examid, false);
        $courseurl->param('search', $exam->shortname);
        $examname = html_writer::link($courseurl,"{$exam->programme}-{$exam->shortname}-{$exam->fullname}");
        $star = ($exam->callnum < 0) ? '**' : '';
        $teachers = $output->pix_icon('userchecked', $strteachers, 'examregistrar', array('title'=>implode(" \n", $exam->teachers), 'class'=>'iconmedium'));
        /// TODO use allocatedexam class and print_collapsible_region of get_formatted teachers() with
        //teachers = print_collapsible_region(get_formmated_teachers, '', 'exam'.$exam->examid, $teachers (icon), '', true, true);
        $examitems[] = $checkbox." ({$exam->allocated} / {$exam->booked} / [{$exam->totalbooked}]) ".$teachers."$star{$examname}$star &nbsp;".$icon;
        $totalseated += $exam->booked;
    }
    echo html_writer::alist($examitems);
    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'available'=>1);
    $menu = examregistrar_get_referenced_roomsmenu($examregistrar, 'session_rooms', $params, $examregprimaryid);
    //$menu = array_merge(array(-1=>get_string('existingroom', 'examregistrar')), $menu);
    $menu = array(-1=>get_string('existingroom', 'examregistrar')) + $menu;
    echo html_writer::label(get_string('withselectedtoroom', 'examregistrar'), 'room', false, array('class' => 'accesshidexx'));
    echo html_writer::select($menu, "room");
    echo '<input type="submit" name="allocateexams" value="'.get_string('allocateexam', 'examregistrar').'" />'."\n";
    echo '</form>'."\n";
}

if($session && $bookedsite) {
    foreach($examteachers as $userid => $exams) {
        $examteachers[$userid] = array_unique($exams);
        if(count($examteachers[$userid]) < 2 ) {
            unset($examteachers[$userid]);
            unset($teachernames[$userid]);
        }
    }
    if($examteachers) {
        echo $output->heading(get_string('multiteachers', 'examregistrar'), 5);
        foreach($examteachers as $userid => $exams) {
            $list = array();
            foreach($exams as $key => $examid) {
                $name = $teachernames[$userid];
                $examname = $examid;
                if(isset($allocatedexams[$examid])) {
                    $exam = $allocatedexams[$examid];
                } elseif(isset($unallocatedexams[$examid])) {
                    $exam = $unallocatedexams[$examid];
                }
                $exams[$key] = "{$exam->programme}-{$exam->shortname}-{$exam->fullname}";
            }
            echo $name.html_writer::alist($exams);
        }
    }

//     if($allocatedexams) {
//         $params =  array('id' => $cm->id, 'edit'=>$edit, 'session'=>$session, 'venue'=>$bookedsite);
//         $url = new moodle_url('/mod/examregistrar/manage/qualitycontrol.php', $params);
//         echo html_writer::link($url, get_string('qualitycontrol', 'examregistrar'));
//     }
}

echo $output->container_end();

echo $output->container_end(); // this is examregallocation end


echo $output->container_start('clearfix ');
echo html_writer::empty_tag('hr');

    $select = " examsession = :examsession AND bookedsite = :bookedsite AND additional > 0 AND roomid > 0 ";
    $totaladditionals = $DB->count_records_select('examregistrar_session_seats', $select, array('examsession'=>$session, 'bookedsite'=>$bookedsite));

    echo $output->container_start('examregroomallocatedseats ');
        echo " Seated as main = $totalroomseated / Seated as additional =  $totaladditionals  ";
    echo $output->container_end();

    echo $output->container_start(' examregunallocatedexams ');
        $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite );
        $select = " examsession = :examsession AND bookedsite = :bookedsite AND additional > 0 AND roomid = 0 ";
        $toseatadditional = $DB->count_records_select('examregistrar_session_seats', $select, $params);
        echo " Main unseated = $totalseated / Additional unseated: $toseatadditional  ";
    echo $output->container_end();

echo $output->container_end();

echo $output->container_start('clearfix ');
echo html_writer::empty_tag('hr');

    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite );
    $select = " examsession = :examsession AND bookedsite = :bookedsite ";
    $toseatmain = $DB->count_records_select('examregistrar_session_seats', $select.' AND additional = 0 ', $params);
    $toseatadditional = $DB->count_records_select('examregistrar_session_seats', $select.' AND additional > 0 ', $params);
    $toseat = $DB->count_records('examregistrar_session_seats', $params);
    $sql = "SELECT COUNT(b.id)
                FROM {examregistrar_bookings} b
                JOIN {examregistrar_exams} e ON e.id = b.examid AND e.examsession = :examsession
                WHERE b.booked = 1 AND b.bookedsite = :bookedsite ";
    $totalbooked = $DB->count_records_sql($sql, $params);

    echo " To seat as main: $toseatmain / To seat as additional:  $toseatadditional / Total to seat: $toseat / Total booked: $totalbooked ";

echo html_writer::empty_tag('hr');

echo $output->container_end();


echo $output->footer();
