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
 * Prints the interface for creating seating rules
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/mod/examregistrar/locallib.php');

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


 //print_object($_GET);
 //print_object("_GET -----------------");
 //print_object($_POST);
 //print_object("_POST -----------------");



$edit   = optional_param('edit', '', PARAM_ALPHANUMEXT);  // list/edit items
$action   = optional_param('action', '', PARAM_ALPHANUMEXT);
$session   = optional_param('session', 0, PARAM_INT);
$bookedsite   = optional_param('venue', '', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$baseurl = new moodle_url('/mod/examregistrar/manage.php', array('id' => $cm->id, 'edit'=>$edit));
$params =  array('id' => $cm->id, 'edit'=>$edit, 'session'=>$session, 'venue'=>$bookedsite);
$actionurl = new moodle_url('/mod/examregistrar/manage/seatingrules.php', $params);

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
    $PAGE->navbar->add(get_string('seatingrules', 'examregistrar'), null);

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

if($action) {
    if(!$confirm) {
        /// ask confirmation
        $actionstr = get_string($action, 'examregistrar');
        $title = get_string('confirm'.$itemname, 'examregistrar');
        $navlinks[] = array('name' => $title, 'link' => null, 'type' => 'title');
        $navigation = build_navigation($navlinks);
        $confirmurl = new moodle_url($actionurl, array('action'=>$action, 'confirm' => 1));
        $message = get_string('allocateconfirm', 'examregistrar', $action);
        echo $OUTPUT->header();
        echo $OUTPUT->confirm($message, $confirmurl, $actionurl);
        echo $OUTPUT->footer();
        die;
    } elseif(confirm_sesskey()) {
        /// do action
        $allocation = data_submitted();
        //print_object($allocation);
        //print_object("  -- allocation ----");
        if($action == 'unallocateall') {
            $DB->set_field('examregistrar_session_seats', 'locationid', 0, array('examsession'=>$session, 'venue'=>$bookedsite));
        } elseif($action == 'refreshallocation') {
           examregistrar_refresh_sessionvenue_allocation($session, $venue);

        } elseif($action == 'assignseats') {
            if($allocation->numusers && $allocation->fromexam && ($allocation->fromroom != $allocation->toroom)) {
                $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite,
                                'examid'=>$allocation->fromexam, 'locationid'=>$allocation->fromroom);
                $sort = ($allocation->fromroom) ? ' id DESC ' : ' id ASC';
                if($users = $DB->get_records_menu('examregistrar_session_seats', $params, $sort, 'id, userid', 0, $allocation->numusers)) {
                    list($insql, $inparams) = $DB->get_in_or_equal(array_keys($users));
                    $select = " id $insql ";
                    $DB->set_field_select('examregistrar_session_seats', 'locationid', $allocation->toroom, $select, $inparams);
                }
            }
        } else {
            $room = optional_param('room', 0, PARAM_INT);
            $exam = optional_param('exam', 0, PARAM_INT);
            if($action == 'emptyroom') {
                if($room) {
                    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite,
                                        'locationid'=>$room);
                    $DB->set_field('examregistrar_session_seats', 'locationid', 0, $params);
                }
            } elseif($action == 'emptyexam') {
                if($exam) {
                    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite,
                                        'examid'=>$exam);
                    $DB->set_field('examregistrar_session_seats', 'locationid', 0, $params);
                }
            } elseif($action == 'allocateexam') {
                if($room && $exam) {
                    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite,
                                        'locationid'=>0, 'examid'=>$exam);
                    $DB->set_field('examregistrar_session_seats', 'locationid', $room, $params);
                }
            } elseif($action == 'allocateexams') {
                if($room && $exams = optional_array_param('exams', array(), PARAM_INT)) {
                    foreach($exams as $examid) {
                    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite,
                                        'locationid'=>0, 'examid'=>$examid);
                    $DB->set_field('examregistrar_session_seats', 'locationid', $room, $params);
                    }
                }
            } elseif($action == 'unallocateexam') {
                if($room && $exam) {
                    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite,
                                        'locationid'=>$room, 'examid'=>$exam);
                    $DB->set_field('examregistrar_session_seats', 'locationid', 0, $params);
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

if($session && $bookedsite) {
    $userbooks = 0;
    if(!$max = $DB->get_records('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite),
                                     ' timemodified DESC ', '*', 0, 1)) {
        examregistrar_session_seats_makeallocation($session, $bookedsite);
    } else {
        $lasttime = reset($max)->timemodified;
        examregistrar_session_seats_newbookings($session, $bookedsite, $lasttime);
    }

    //check bookings & compare allocations.
/*
        $userbooks = 0;
        select b.*, COUNT(id) as numexams, (SELECT COUNT(userid) booking b2  where b2.examid=b.examid AND b2.locationid = b.locationid AND booked = 1 GROUP by b2.examid ) AS partners
        examregistrar_bookings b
        JOIN examregistrar_exams e ON b.examid = e.id
        WHERE e.examsession = session AND b.locationid = bookedsite AND booked = 1
        ORDER BY b.userid ASC, partners ASC
        GROUP BY userid

        esto son unique users

        select b.userid, COUNT(id) as numexams
        examregistrar_bookings b
        JOIN examregistrar_exams e ON b.examid = e.id
        WHERE e.examsession = session AND b.locationid = bookedsite  AND booked = 1  AND numexams > 1
        ORDER BY b.userid ASC
        GROUP BY userid

        esto también , pero nos da  aquellos con  numexams > 1 los que van a extras



        if no allocations : print button new allocations

        if allocations <> bookings print message add allocations

*/
/*
    /// rooms
    $sql = "SELECT l.id, el.name, l.idnumber,  l.seats, COUNT(ss.id) AS booked
                FROM {examregistrar_locations} l
                JOIN {examregistrar_elements} el ON l.examregid = el.examregid AND el.type = 'locationitem' AND l.idnumber = el.idnumber
                JOIN {examregistrar_session_rooms} sr ON l.id = sr.locationid AND sr.examsession = :examsession1
                LEFT JOIN {examregistrar_session_seats} ss ON l.id = ss.locationid AND ss.examsession = :examsession2 AND ss.bookedsite = :bookedsite1
                WHERE l.seats > 0
                GROUP BY l.id ";
    $allocatedrooms = $DB->get_records_sql($sql, array('examsession1'=>$session, 'examsession2'=>$session, 'examsession3'=>$session,
                                                       'bookedsite1'=>$bookedsite, 'bookedsite2'=>$bookedsite));

    $sql = "SELECT ss.examid, e.programme, e.shortname, COUNT(ss.userid) AS booked
                FROM {examregistrar_session_seats} ss
                JOIN {examregistrar_exams} e ON ss.examid = e.id
                WHERE ss.examsession = :examsession AND ss.bookedsite = :bookedsite AND ss.locationid = 0
                GROUP BY ss.examid ";
    $unallocatedexams = $DB->get_records_sql($sql, array('examsession'=>$session, 'bookedsite'=>$bookedsite));

    $sql = "SELECT ss.examid, e.programme, e.shortname, COUNT(ss.userid) AS booked
                FROM {examregistrar_session_seats} ss
                JOIN {examregistrar_exams} e ON ss.examid = e.id
                WHERE ss.examsession = :examsession AND ss.bookedsite = :bookedsite AND ss.locationid > 0
                GROUP BY ss.examid ";
    $allocatedexams = $DB->get_records_sql($sql, array('examsession'=>$session, 'bookedsite'=>$bookedsite));


    foreach($unallocatedexams as $eid => $exam) {
        $allocated = 0;
        $exam = $unallocatedexams[$eid];
        if(isset($allocatedexams[$eid])) {
            $allocated = $allocatedexams[$eid]->booked;
        }
        $exam->allocated = $allocated;
        $unallocatedexams[$eid] = $exam;
    }
*/
} // end of building data

////////////////////////////////////////////////////////////////////////////////

add_to_log($course->id, 'examregistrar', 'manage assignseats', "manage.php?id={$cm->id}&edit=assignseats", $examregistrar->name, $cm->id);

/// Print the page header, Output starts here
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('assignseats', 'examregistrar'));


    echo $OUTPUT->container_start('examregistrarmanagefilterform clearfix ');
        echo $OUTPUT->single_button($baseurl, get_string('clearfilter', 'examregistrar'), 'get', array('class'=>' clearfix '));

        echo '<form id="examregistrarperiodsform" action="'.$CFG->wwwroot.'/mod/examregistrar/manage/assignseats.php" method="post">'."\n";
        echo '<input type="hidden" name="edit" value="'.$edit.'" />'."\n";
        echo '<input type="hidden" name="id" value="'.$cm->id.'" />'."\n";

        $sessionmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'sessions', 'sessionitem', $examregprimaryid, 'choose');
        echo html_writer::label(get_string('sessionitem', 'examregistrar').': ', 'session');
        echo html_writer::select($sessionmenu, "session", $session);
        echo ' &nbsp; ';

        $parentmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'locations', 'locationitem', $examregprimaryid, 'choose');
        echo html_writer::label(get_string('parent', 'examregistrar').': ', 'venue');
        echo html_writer::select($parentmenu, "venue", $bookedsite);
        echo ' &nbsp; ';

        echo '<input type="submit" value="'.get_string('filter', 'examregistrar').'" />'."\n";
        echo '</form>'."\n";
    echo $OUTPUT->container_end();




echo $OUTPUT->container_start('examregallocation clearfix ');
echo html_writer::empty_tag('hr');
echo $OUTPUT->container_start('examregallocationcontrolsleft  ');

echo $OUTPUT->container_end();
echo $OUTPUT->container_start('examregallocationcontrolsright  ');
    //if($session && $bookedsite) {
        $actionurl->param('action', 'refreshallocation');
        echo $OUTPUT->single_button($actionurl, get_string('refreshallocation', 'examregistrar'), 'get', array('class'=>' clearfix '));
        $actionurl->param('action', 'unallocateall');
        echo $OUTPUT->single_button($actionurl, get_string('unallocateall', 'examregistrar'), 'get', array('class'=>' clearfix '));
        $uploadurl = new moodle_url('/mod/examregistrar/manage/upload.php', array('id' => $cm->id, 'edit'=>'assignseats'));
        $uploadurl->param('csv', 'assignseats');
        echo $OUTPUT->single_button($actionurl, get_string('uploadcsvassignseats', 'examregistrar'), 'get', array('class'=>' clearfix '));
    //}
echo $OUTPUT->container_end();
echo $OUTPUT->container('', ' clearfix ');
echo html_writer::empty_tag('hr');
echo $OUTPUT->container_end();







$strunallocate = get_string('unallocate', 'examregistrar');

echo $OUTPUT->container_start('examregallocation clearfix ');

/// the left part containing room list with assigned exams in them
echo $OUTPUT->container_start('examregroomallocatedseats ');
echo $OUTPUT->heading(get_string('allocatedrooms', 'examregistrar'));

$params = array('confirm'=>1, 'sesskey'=>sesskey());
$allocurl = new moodle_url($actionurl, $params);

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
        $freealloc = '&nbsp;'.html_writer::span(get_string('free', 'examregistrar', $free), ' freealloc ');
    }
    $icon = '';
    if($allocatedseats > 0) {
        $allocurl->params(array('action'=>'emptyroom', 'room'=>$room->id));
        $icon = '&nbsp;&nbsp;'.html_writer::link($allocurl, $OUTPUT->pix_icon('t/delete', $strunallocate, 'moodle', array('class'=>'iconsmall', 'title'=>$strunallocate)));
    }
    $roomname .= $busyalloc.$freealloc.$icon;

    $sql = "SELECT ss.examid, e.programme, e.shortname, COUNT(ss.userid) AS booked
                FROM {examregistrar_session_seats} ss
                JOIN {examregistrar_exams} e ON ss.examid = e.id
                WHERE ss.examsession = :examsession AND ss.bookedsite = :bookedsite AND ss.locationid = :location
                GROUP BY ss.examid ";
    $roomexams = $DB->get_records_sql($sql, array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'location'=>$room->id));

    echo $OUTPUT->heading($roomname, 3, ' leftalign ');
    if($roomexams) {
        $numexams = count($roomexams);
        echo get_string('exams', 'examregistrar').': '.$numexams;
        $examitems = array();
        foreach ($roomexams as $exam) {
            $allocurl->params(array('action'=>'unallocateexam', 'exam'=>$exam->examid));
            $icon = html_writer::link($allocurl, $OUTPUT->pix_icon('t/delete', $strunallocate, 'moodle', array('class'=>'iconsmall', 'title'=>$strunallocate)));
            $examitems[] = "({$exam->booked}) {$exam->programme}-{$exam->shortname} $icon";
        }
        echo html_writer::alist($examitems);
        $extrausers = array();
        $extraexams = array();
        $select = " examsession = :examsession AND bookedsite = :bookedsite AND locationid = :location AND extraexams IS NOT NULL ";
        if($additionalexams = $DB->get_records_select('examregistrar_session_seats', $select, array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'location'=>$room->id))) {
            foreach($additionalexams as $extra) {
                $exams = explode(',',$extra->extraexams);
                $users[$extra->userid] = $extra->userid;
                $extraexams = $extraexams + $exams;
            }
            $extraexams = array_unique($extraexams);
            $info = new stdClass;
            $info->users = count($users);
            $info->exams = count($exams);
            echo html_writer::empty_tag('br');
            //echo get_string('additionalexams', 'examregistrar');
            //echo html_writer::alist($additionalexams); // (items, array $attributes = null, $tag = 'ul')
            echo get_string('additionalusersexams', 'examregistrar', $info);
            echo html_writer::empty_tag('hr');

        }
        if($additionalexams) {
            echo html_writer::empty_tag('hr');
            echo get_string('additionalexams', 'examregistrar');

            //echo html_writer::alist($additionalexams); // (items, array $attributes = null, $tag = 'ul')
            echo html_writer::empty_tag('hr');
        }
    }
}




echo $OUTPUT->container_end();


/// the rigth part containing unallocated exams
echo $OUTPUT->container_start(' examregunallocatedexams ');
echo $OUTPUT->heading(get_string('unallocatedexams', 'examregistrar'));

if($unallocatedexams) {

        echo '<form id="examregistrarassigseatsform" action="'.$CFG->wwwroot.'/mod/examregistrar/manage/assignseats.php" method="post">'."\n";
        echo '<input type="hidden" name="edit" value="'.$edit.'" />'."\n";
        echo '<input type="hidden" name="id" value="'.$cm->id.'" />'."\n";
        echo '<input type="hidden" name="action" value="allocateexams" />'."\n";
        echo '<input type="hidden" name="session" value="'.$session.'" />'."\n";
        echo '<input type="hidden" name="venue" value="'.$bookedsite.'" />'."\n";
        echo '<input type="hidden" name="confirm" value="1" />'."\n";
        echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />'."\n";

    $examitems = array();
    foreach($unallocatedexams as $exam) {
        $icon = '';
        if($exam->allocated) {
            $allocurl->params(array('action'=>'emptyexam', 'exam'=>$exam->examid));
            $icon = html_writer::link($allocurl, $OUTPUT->pix_icon('t/delete', $strunallocate, 'moodle', array('class'=>'iconsmall', 'title'=>$strunallocate)));
        }
        $checkbox = html_writer::checkbox('exams['.$exam->examid.']', $exam->examid, false);
        $examitems[] = $checkbox."  ({$exam->booked} / {$exam->allocated}) {$exam->programme}-{$exam->shortname}&nbsp;".$icon;
    }
    echo html_writer::alist($examitems); // (items, array $attributes = null, $tag = 'ul')
    $params = array('examsession'=>$session, 'available'=>1);
    $menu = examregistrar_get_referenced_roomsmenu($examregistrar, 'session_rooms', $params, $examregprimaryid);
    echo html_writer::label(get_string('toroom', 'examregistrar'), 'room', false, array('class' => 'accesshidexx'));
    echo html_writer::select($menu, "room");
    echo '<input type="submit" name="allocateexams" value="'.get_string('allocateexam', 'examregistrar').'" />'."\n";
    echo '</form>'."\n";
}

echo $OUTPUT->container_end();

echo $OUTPUT->container_end(); // this is examregallocation end


echo $OUTPUT->container_start('clearfix ');
echo html_writer::empty_tag('hr');
echo $OUTPUT->container_end();


echo $OUTPUT->footer();
