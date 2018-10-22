<?php
/**
 * This file contains block_supervision main local library functions
 *
 * @package   block_examswarnings
 * @copyright 2013 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/mod/examregistrar/locallib.php');
require_once($CFG->dirroot.'/mod/examregistrar/booking_form.php'); // lagdays functions


function examswarnings_get_sessiondata($config) {
    global $DB;

    $regconfig = get_config('examregistrar');

    $examregistrar = $DB->get_record('examregistrar', array('id'=> $config->primaryreg));
    $periods = examregistrar_current_periods($examregistrar);
    $period = reset($periods);
    
    if(!$period) {
        return array();
    }
    
    $extra = examregistrar_is_extra_period($examregistrar, $period);

    $now = time();
    $days = $extra ? $config->warningdaysextra : $config->warningdays;
    $check = strtotime("+ $days days  ", $now) + 10;
    $session = examregistrar_next_sessionid($examregistrar, $now, true);

    $lagdays = examregistrar_set_lagdays($examregistrar, $regconfig, $period, array());

    return array($period, $session, $extra, $check, $lagdays);
}

function examswarnings_get_session_period($config) {
    global $DB;

    $examregistrar = $DB->get_record('examregistrar', array('id'=> $config->primaryreg));
    $periods = examregistrar_current_periods($examregistrar);
    $period = reset($periods);
    $session = examregistrar_next_sessionid($examregistrar, time(), true);
    return array($period, $session);
}


function examswarnings_get_controlemail($config) {
    $controluser = array();
    if(isset($config->controlemail) && $config->controlemail) {
        if($emails = explode(',', $config->controlemail)) {
            foreach($emails as $email) {
                $user = core_user::get_support_user();
                $user->email = trim($email);
                $user->mailformat = 1;
                $user->id = 1;
                $controluser[] = clone $user;
            }
        }
    }
    return $controluser;
}


/**
    * Checks if the specified or current user has pending bookings for exams in session
    *
    * @param object $period exam period record
    * @param object $session exam session record
    * @param bool   $extra if the period is an extra one
    * @param int    $userid the ID of user
    * @param bool   $strictness if true the only NON existing bookings are returned, not booking with booked = 0
    * @return array of exams needing appointment in session
    */
function examswarnings_notappointedexams($period, $session, $extra, $userid = 0, $strictness=true) {
    global $DB, $USER;

    if(!$userid) {
        $userid = $USER->id;
    }

    if(!$ucourses = get_user_capability_course('mod/examregistrar:book', $userid, false)) {
        return 0;
    }
    foreach($ucourses as $c) {
        $courses[$c->id] = $c->id;
    }
    unset($ucourses);

    if(!$courses) {
        return 0;
    }

    $strictwhere = '';
    if(!$strictness) {
        $strictwhere = ' OR b.booked = 0 ';
    }

    list($incourses, $params) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'course');
    $params['user1'] = $userid;
    $params['user2'] = $userid;
    $params['user3'] = $userid;
    $params['user4'] = $userid;
    $params['period'] = $period->id;
    $params['session'] = $session->id;
    $params['examassign'] = get_config('block_examswarnings', 'examidnumber');
    $extrawhere = '';
    if($extra) {
        $extrawhere = " AND NOT EXISTS (SELECT b3.id FROM {examregistrar_bookings} b3
                                                        JOIN {examregistrar_exams} e2 ON b3.examid = e2.id AND e2.period = :period2
                                                        WHERE b3.booked = 1 AND b3.userid = :user5 AND e2.courseid = e.courseid )";
        $params['user5'] = $userid;
        $params['period2'] = $period->id;
    }
    $sql = "SELECT e.id, e.programme, e.courseid, c.shortname, c.category, b.booked
                FROM {examregistrar_exams} e
                JOIN {course} c ON e.courseid = c.id
                JOIN {grade_items} gi ON c.id = gi.courseid AND gi.itemtype = 'course'
                LEFT JOIN {grade_grades} gg ON gi.id = gg.itemid AND gg.userid = :user1
                LEFT JOIN {grade_items} ge ON c.id = ge.courseid AND  ge.idnumber LIKE :examassign
                LEFT JOIN {grade_grades} gge ON ge.id = gge.itemid AND gge.userid = :user2
                LEFT JOIN {examregistrar_bookings} b ON e.id = b.examid AND b.userid = :user3
                WHERE e.period = :period AND e.examsession = :session AND e.visible = 1 AND e.callnum > 0
                    AND c.id $incourses AND (b.id IS NULL $strictwhere)
                    AND NOT EXISTS (SELECT b2.id FROM {examregistrar_bookings} b2 WHERE e.id = b2.examid AND b2.userid = :user4 AND b2.booked = 1)
                    $extrawhere
                    AND NOT ((gg.finalgrade >= gi.gradepass AND gg.finalgrade IS NOT NULL) OR 
                                (gge.finalgrade >= ge.gradepass AND gge.finalgrade IS NOT NULL))
                GROUP BY e.id
                ORDER BY c.shortname ";
    $exams = $DB->get_records_sql($sql, $params);
    return $exams;
}


/**
    * Checks and returns if the specified or current user has standing bookings for exams in session
    *
    * @param object $period exam period record
    * @param object $session exam session record
    * @param int    $userid the ID of user
    * @return array of exams needing appointment in session
    */
function examswarnings_upcomingexams($period, $session, $userid = 0) {
    global $DB, $USER;

    if(!$userid) {
        $userid = $USER->id;
    }

    if(!$ucourses = get_user_capability_course('mod/examregistrar:book', $userid, false)) {
        return 0;
    }
    foreach($ucourses as $c) {
        $courses[$c->id] = $c->id;
    }
    unset($ucourses);

    list($incourses, $params) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'course');
    $params['user'] = $userid;
    $params['period'] = $period->id;
    $params['session'] = $session->id;

    $sql = "SELECT b.id, e.courseid, e.programme, c.shortname, c.category
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON b.examid = e.id AND e.period = :period AND e.examsession = :session AND e.visible = 1
            JOIN {course} c ON c.id = e.courseid
            WHERE b.booked = 1 AND b.userid = :user AND e.courseid $incourses ";

    $exams = $DB->get_records_sql($sql, $params);

    return $exams;
}


/**
    * Checks and returns if the specified or current user is teacher in an exam in session
    *
    * @param object $period exam period record
    * @param object $session exam session record
    * @param int    $userid the ID of user
    * @return array of exams needing appointment in session
    */
function examswarnings_reminder_upcomingexams($period, $session, $userid = 0) {
    global $DB, $USER;

    if(!$userid) {
        $userid = $USER->id;
    }

    if(!$ucourses = get_user_capability_course('mod/examregistrar:submit', $userid, false)) {
        return 0;
    }
    foreach($ucourses as $c) {
        $courses[$c->id] = $c->id;
    }
    unset($ucourses);

    list($incourses, $params) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'course');
    $params['period'] = $period->id;
    $params['session'] = $session->id;

    $sql = "SELECT e.id, e.courseid, e.programme, c.shortname, c.category
            FROM {examregistrar_exams} e
            JOIN {course} c ON c.id = e.courseid
            WHERE  e.period = :period AND e.examsession = :session AND e.visible = 1 AND e.callnum > 0
                    AND e.courseid $incourses ";

    $exams = $DB->get_records_sql($sql, $params);

    return $exams;
}

/**
    * Checks and returns if the specified or current user is staff in a room in an exam session
    *
    * @param object $period exam period record
    * @param object $session exam session record
    * @param int    $userid the ID of user
    * @return array of exams needing appointment in session
    */
function examswarnings_roomcall_upcomingexams($period, $session, $userid = 0) {
    global $DB, $USER;

    if(!$userid) {
        $userid = $USER->id;
    }

    $config = get_config('block_examswarnings');

    $staffroles = explode(',', $config->roomcallroles);
    list($inroles, $params) = $DB->get_in_or_equal($staffroles, SQL_PARAMS_NAMED, 'role');
    $params['user'] = $userid;
    $params['session'] = $session->id;

    $sql = "SELECT s.id as sid, s.info, s.role, l.*, e.name AS roomname, e.idnumber AS roomidnumber
            FROM {examregistrar_staffers} s
            JOIN {examregistrar_locations} l ON l.id = s.locationid
            JOIN {examregistrar_elements} e ON e.examregid = l.examregid AND e.type='locationitem' AND e.id = l.location
            JOIN {examregistrar_session_rooms} sr ON sr.examsession = s.examsession AND sr.roomid = s.locationid AND sr.available = 1
            WHERE  s.userid = :user AND s.examsession = :session AND s.visible = 1
                    AND s.role $inroles
                    AND EXISTS (SELECT 1 FROM {examregistrar_session_seats} ss WHERE ss.examsession = s.examsession AND ss.roomid = s.locationid )
            GROUP BY s.locationid ";

    $rooms = $DB->get_records_sql($sql, $params);

    return $rooms;
}

/**
 * creates a message class instance fro mailing notifications
 *
 * @param string $name name of the notification type
 * @return class a message class instance
 */
function examswarnings_prepare_message($name) {
    $msgdata = new \core\message\message();
    $msgdata->component         = 'block_examswarnings';
    $msgdata->name              = $name;
    $msgdata->notification      = 1;
    $msgdata->userfrom = core_user::get_noreply_user(); 
    $msgdata->userfrom->lastname = get_string('examreminderfrom',  'block_examswarnings');
}
