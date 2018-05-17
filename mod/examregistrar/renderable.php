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
 * Examregistrar module renderer
 *
 * @package    mod
 * @subpackage examregistrar
 * @copyright  2014 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir. '/coursecatlib.php');

class examregistrar_room implements renderable {
    /** @var int $roomid the id of the room, must correspond to an ID on locations table */
    protected $roomid;
    /** @var string $name the name of the room */
    public $name = '';
    /** @var string $idnumber the idnumber code of the room */
    public $idnumber = '';
    /** @var string $locationtype as string name  */
    public $locationtype = '';
    /** @var string $address formatted address  */
    public $address = '';
    /** @var int $seats number of seats in room  */
    public $seats = 0;
    /** @var int $parent ID for parent room  */
    public $parent;
    /** @var int $depth parent tree depth  */
    public $depth;
    /** @var string $path tree path to room  */
    public $path;
    /** @var int $sortorder room ordering  */
    public $ortorder;
    /** @var bool $visible   */
    public $visible = true;

    public function __construct($room, $field='id') {
        $this->roomid = $room->$field;

        $params = array('name', 'idnumber', 'address', 'seats', 'parent', 'depth', 'path', 'sortorder', 'visible');
        foreach($params as $param) {
            if(isset($room->$param)) {
                $this->$param = $room->$param;
            }
        }

        if(isset($room->addressformat)) {
            $this->address = format_text($this->address, $room->addressformat, array('filter'=>false, 'para'=>false));
        }
        if(isset($room->locationtype)) {
            if(is_int($room->locationtype)) {
            } elseif(is_string($room->locationtype)) {
                $this->locationtype = $room->locationtype;
            }
        }
    }

    public function get_id() {
        return $this->roomid;
    }

    public function formatted_itemname($options=array()) {
        global $PAGE;
        $output = $PAGE->get_renderer('mod_examregistrar');
        return $output->formatted_itemname($this->name, $this->idnumber, $options);
    }

}


class examregistrar_exam implements renderable {
    /** @var int $examid the id of the exam, must correspond to an ID on exams table */
    protected $examid;
    /** @var int $courseid the ID of the course this exam is realated to, must correspond to an ID on course table */
    public $courseid;
    /** @var string $annuality the annuality this exam belongs to*/
    public $annuality = '';
    /** @var string $programme the programme/category of the course/exam */
    public $programme = '';
    /** @var string $shortname the shortname of the course */
    public $shortname = '';
    /** @var string $fullname the fullname of the course */
    public $fullname = '';
    /** @var int $period the ID of the period this exam is realated to, must correspond to an ID on period table */
    public $period = 0;
    /** @var int $callnum the N this call makes in total call for this exam & period */
    public $callnum;
    /** @var string $scope the name of examscope type for this exam */
    public $examscope = '';
    /** @var string $examsession the name of examssession type for this exam */
    public $examsession = '';
    /** @var int $examfile the row ID for an entry in examfiles table   */
    public $examfile;

    /** @var bool $visible   */
    public $visible = true;

    public function __construct($exam, $field='id') {
        $this->examid = $exam->$field;

        $params = array('annuality', 'courseid', 'programme', 'shortname', 'fullname', 'period', 'callnum', 'examscope', 'examsession', 'visible');
        foreach($params as $param) {
            if(isset($exam->$param)) {
                $this->$param = $exam->$param;
            }
        }
        $this->examfile = false;

    }

    public function get_id() {
        return $this->examid;
    }

    public function get_exam_name($addprogramme=false, $addscope=false, $addfullname=false, $linkname=false) {
        global $DB;
        $examname = $addprogramme ? $this->programme.'-'.$this->shortname : $this->shortname;
        $space = $addfullname ? ' ' : '';
        if($addscope) {
            if($DB->count_records('examregistrar_exams', array('courseid'=>$this->courseid, 'examsession'=>$this->examsession, 'callnum'=>$this->callnum)) > 1 ) {
                $scope = $DB->get_field('examregistrar_elements', 'idnumber', array('id'=>$this->examscope));
                $examname .= $space."($scope)";
            }
        }
        if($addfullname) {
            $name = $this->fullname;
            if($linkname && $cmid = examregistrar_get_course_instance($this)) {
                $url = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cmid, 'tab'=>'view'));
                $name = html_writer::link($url, $name);
            }
            $examname .= ' - '.$name;
        }
        return $examname;
    }


    public function set_valid_file() {
        global $DB;

        $this->examfile = false;
        $message = false;
        $examfile = '';
        if($examfiles = $DB->get_records('examregistrar_examfiles', array('examid'=>$this->examid, 'status'=>EXAM_STATUS_APPROVED),'timeapproved DESC, attempt DESC')) {
            if(count($examfiles) > 1) {
                // error, more than one attempt approved
                $message = get_string('error_manyapproved', 'examregistrar');
            }
            $examfile = reset($examfiles);
            $this->examfile = $examfile->id;
        } elseif($examfiles = $DB->get_records('examregistrar_examfiles', array('examid'=>$this->examid, 'status'=>EXAM_STATUS_SENT),'timeapproved DESC, attempt DESC')) {
            if(count($examfiles) > 1) {
                // error, more than one attempt approved
                $message = get_string('error_manysent', 'examregistrar');
            }
            $examfile = reset($examfiles);
            $this->examfile = $examfile->id;
            $message = get_string('error_noneapproved', 'examregistrar');
        } else {
            $message = get_string('error_nonesent', 'examregistrar');
        }

        return $message;
    }

    public function get_examfile_file() {
        $context = context_course::instance($this->courseid);
        $file = false;
        $this->set_valid_file();
        if($this->examfile) {
            list($area, $path) = examregistrar_file_decode_type('exam');
            $fs = get_file_storage();
            if($files = $fs->get_directory_files($context->id, 'mod_examregistrar', $area, $this->examfile, $path, false, false, "filepath, filename")) {
                $file = reset($files);
            }
        }
        return $file;
    }

    public function get_formatted_teachers() {
        global $DB;

        $content = '';

        $select = ", " . context_helper::get_preload_record_columns_sql('ctx');
        $sql = "SELECT c.* $select
                FROM {course} c
                LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)
                WHERE c.id = :id ";
        $course = $DB->get_record_sql($sql, array('id'=>$this->courseid, 'contextlevel'=>CONTEXT_COURSE), MUST_EXIST);
        $course = new course_in_list($course);
        if($contacts = $course->get_course_contacts()) {
            $content = html_writer::start_tag('ul', array('class' => 'teachers'));
            foreach ($contacts as $userid => $coursecontact) {
                $name = $coursecontact['rolename'].': '.$coursecontact['username'];
                $content .= html_writer::tag('li', $name);
            }
            $content .= html_writer::end_tag('ul'); // .teachers
        }
        return $content;
    }

    public function get_teachers() {
        global $DB;

        $users = array();

        list($select, $join) =  context_instance_preload_sql('c.id', CONTEXT_COURSE, 'ctx');
        $sql = "SELECT c.* $select
                FROM {course} c
                $join
                WHERE c.id = :id ";
        $course = $DB->get_record_sql($sql, array('id'=>$this->courseid), MUST_EXIST);
        $course = new course_in_list($course);
        if($contacts = $course->get_course_contacts()) {
            foreach ($contacts as $userid => $coursecontact) {
                $name = $coursecontact['rolename'].': '.$coursecontact['username'];
                $users[$userid] = $name;
            }
        }
        return $users;
    }

    public function get_examdate() {
        global $DB;
        return $DB->get_field('examregistrar_examsessions', 'examdate', array('id'=>$this->examsession));
    }

    public function get_print_mode() {
        global $DB;
        return $DB->get_field('examregistrar_examfiles', 'printmode', array('id'=>$this->examfile, 'examid'=>$this->examid));
    }
}

class examregistrar_roomexam extends examregistrar_exam implements renderable {
    /** @var int $session examsession ID for this allocation  */
    public $session;
    /** @var int $bookedsite ID for this allocation  */
    public $venue;
    /** @var int $roomid ID for this allocation  */
    public $roomid;
    /** @var int $seated number of students allocated to this room  */
    public $seated = 0;
    /** @var array $users students allocated to this exam  */
    public $users = array();
    /** @var array $users students allocated to this exam  */
    public $bookednotallocated = array();

    public function __construct($session, $venue, $roomid, $exam, $field='id') {
        $this->session = $session;
        $this->venue = $venue;
        $this->roomid = $roomid;
        $this->users = false;
        $this->bookednotallocated = false;
        parent::__construct($exam, $field);
        if(isset($exam->seated)) {
            $this->seated = $exam->seated;
        }
    }

    public function set_users($onlyadditionals=false) {
        global $DB;
        $this->users = array();

        $users = array();

//         print_object('set users  roomexam');

        $fields = get_all_user_name_fields(true, 'u');
        $sql = "SELECT u.id, u.username, u.idnumber, u.picture, $fields, ss.id AS allocid, 0 AS additional
                FROM {examregistrar_session_seats} ss
                JOIN {user} u ON ss.userid = u.id
                WHERE ss.examsession = :session AND ss.bookedsite = :bookedsite AND ss.examid = :exam AND ss.roomid = :room ";
        $order = ' ORDER BY u.lastname, u.firstname, u.idnumber ';

        if(!$onlyadditionals) {
            // users with this as main exam, then additional = 0
            $params = array('session'=>$this->session, 'bookedsite'=>$this->venue, 'room'=>$this->roomid, 'exam'=>$this->examid);
            $users = $DB->get_records_sql($sql.' AND ss.additional = 0 '.$order, $params);
        } else {
            // if only additionals, additionals != 0
            $params = array('session'=>$this->session, 'bookedsite'=>$this->venue, 'room'=>$this->roomid, 'exam'=>$this->examid);
            $users = $DB->get_records_sql($sql.' AND ss.additional = ss.examid '.$order, $params);
        }

//         print_object($users);
//         print_object(" --  users  inside ");

        $sql = "SELECT ss.userid, COUNT(ss.additional) AS additional
                FROM {examregistrar_session_seats} ss
                WHERE ss.examsession = :session AND ss.bookedsite = :bookedsite AND ss.roomid = :room AND ss.additional > 0
                GROUP BY ss.userid ";
        $params = array('session'=>$this->session, 'bookedsite'=>$this->venue, 'room'=>$this->roomid);
        if($additionals = $DB->get_records_sql_menu($sql, $params)) {
            foreach($additionals as $uid => $additional) {
                if(isset($users[$uid])) {
                    $user = $users[$uid];
                    $user->additional =  $additional;
                    $users[$uid] = $user;
                }
            }
        }

        $this->users = $users;
        $this->seated = count($users);

        if(!$onlyadditionals) {
            $fields = get_all_user_name_fields(true, 'u');
            $sql = "SELECT  b.*, b.id AS bid, u.id, u.username, u.idnumber, $fields
                    FROM {examregistrar_bookings} b
                    JOIN {user} u ON b.userid = u.id
                    LEFT JOIN {examregistrar_session_seats} ss ON b.userid = ss.userid AND b.examid = ss.examid
                    WHERE b.examid = :exam AND b.bookedsite = :venue AND b.booked = 1 AND  ss.examid IS NULL
                    ORDER BY u.lastname, u.firstname, u.idnumber ";
            $params = array('exam'=>$this->examid, 'venue'=>$this->venue);
            $users = $DB->get_records_sql($sql, $params);
            $this->bookednotallocated = $users;
        }

        return $this->users;
    }

}

class examregistrar_allocatedroom extends examregistrar_room implements renderable {
    /** @var int $session examsession ID for this allocation  */
    public $session;
    /** @var int $bookedsite ID for this allocation  */
    public $venue;
    /** @var int $seated number of students allocated to this room  */
    public $seated = 0;
    /** @var array $exams colection of roomexams items  */
    public $exams = array();
    /** @var array $additionals colection of roomexams items  */
    public $additionals = array();
    /** @var int $additionalusers number of distinct users with additional exams in this room  */
    public $additionalusers = 0;

    public function __construct($session, $venue, $room, $field='id') {
        $this->session = $session;
        $this->venue = $venue;
        parent::__construct($room, $field);
        if(isset($room->seated)) {
            $this->seated = $room->seated;
        }
    }

    public function add_exam_fromrow($row, $field='id') {
        $exam = new examregistrar_roomexam($this->session, $this->venue, $this->roomid, $row, $field);
        $examid = $exam->get_id();
        $this->exams[$examid] = $exam;
        $this->seated += $exam->seated;
    }

    public function refresh_seated() {
        $seated = 0;
        foreach($this->exams as $exam) {
            $seated += $exam->seated;
        }
        $this->seated = $seated;
        return $this->seated;
    }

    public function set_additionals() {
        global $DB;
        $this->additionals = array();

        $sql = "SELECT ss.id AS ssid, ss.examid, ss.userid, c.shortname, c.fullname, e.*
                    FROM {examregistrar_session_seats} ss
                    JOIN {examregistrar_exams} e ON ss.examid = e.id
                    JOIN {course} c ON e.courseid = c.id
                    WHERE ss.examsession = :examsession AND ss.bookedsite = :bookedsite AND ss.roomid = :roomid AND ss.additional > 0
                    ORDER BY e.programme, c.shortname ";
        if($additionalexams = $DB->get_records_sql($sql, array('examsession'=>$this->session, 'bookedsite'=>$this->venue, 'roomid'=>$this->roomid))) {
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
                    $exams[$exam->examid] = $exam;
                } else {
                    $booked[$exam->examid] += 1;
                }
            }

            foreach($exams as $eid => $additional) {
                $additional->seated = $booked[$additional->examid];
                $exam = new examregistrar_roomexam($this->session, $this->venue, $this->roomid, $additional, 'examid');
                $this->additionals[$eid] = $exam;
            }
            $this->additionalusers = count($users);
        }

        return $this->additionals;
    }

    public function lastallocated() {
        global $DB;
        $time = $DB->get_records_menu('examregistrar_session_seats', array('examsession'=>$this->session, 'bookedsite'=>$this->venue, 'roomid'=>$this->roomid),
                                                            'timemodified DESC', 'id, timemodified', 0, 1);
        if($time) {
            return reset($time);
        }
        return false;
    }
}


class examregistrar_allocatedexam extends examregistrar_exam implements renderable {
    /** @var int $session examsession ID for this allocation  */
    public $session;
    /** @var int $bookedsite ID for this allocation  */
    public $venue;
    /** @var int $seated number of students allocated to this room  */
    public $seated;
    /** @var array $users students allocated to this exam  */
    public $users = array();
    /** @var array $users students allocated to this exam  */
    public $bookednotallocated = array();

    public function __construct($session, $venue, $exam, $field='id') {
        $this->session = $session;
        $this->venue = $venue;
        $this->users = false;
        $this->bookednotallocated = false;
        parent::__construct($exam, $field);
        if(isset($exam->seated)) {
            $this->seated = $exam->seated;
        }
    }

    public function set_users($venue='') {
        global $DB;
        $this->users = array();

        $params = array('session'=>$this->session, 'exam'=>$this->examid);
        $where = '';
        if($venue) {
            $where = ' AND b.bookedsite = :venue ';
            $params['venue'] = $venue;
        }
        $fields = get_all_user_name_fields(true, 'u');
        $sql = "SELECT  b.id AS bid, ss.*,  u.id, u.username, u.idnumber, $fields
                FROM {examregistrar_bookings} b
                JOIN {examregistrar_exams} e ON b.examid = e.id AND  e.examsession = :session
                JOIN {user} u ON b.userid = u.id
                LEFT JOIN {examregistrar_session_seats} ss ON b.userid = ss.userid AND b.examid = ss.examid
                WHERE b.examid = :exam AND b.booked = 1 $where
                ORDER BY u.lastname, u.firstname, u.idnumber ";

        $users = $DB->get_records_sql($sql, $params);
        $this->users = $users;
        $this->seated = count($users);
        return $this->users;
    }

    public function get_formatted_user_allocations($venue='') {
        global $DB;

        $params = array('session'=>$this->session, 'exam'=>$this->examid);
        $where = '';
        if($venue) {
            $where = ' AND b.bookedsite = :venue ';
            $params['venue'] = $venue;
        }
        $fields = get_all_user_name_fields(true, 'u');
        $sql = "SELECT  b.id AS bid, ss.roomid, ss.additional, u.id, u.username, u.idnumber, $fields,
                        el.name AS venuename, el.idnumber AS venueidnumber,
                        el2.name AS roomname, el2.idnumber AS roomidnumber
                FROM {examregistrar_bookings} b
                JOIN {examregistrar_exams} e ON b.examid = e.id AND  e.examsession = :session
                JOIN {examregistrar_locations} l ON l.id = b.bookedsite
                JOIN {examregistrar_elements} el ON l.examregid = el.examregid AND el.type = 'locationitem' AND l.location = el.id
                JOIN {user} u ON b.userid = u.id
                LEFT JOIN {examregistrar_session_seats} ss ON b.userid = ss.userid AND b.examid = ss.examid
                LEFT JOIN {examregistrar_locations} l2 ON l2.id = ss.roomid
                LEFT JOIN {examregistrar_elements} el2 ON l2.examregid = el2.examregid AND el2.type = 'locationitem' AND l2.location = el2.id
                WHERE b.examid = :exam AND b.booked = 1 $where
                ORDER BY u.lastname, u.firstname, u.idnumber ";

        $users = $DB->get_records_sql($sql, $params);
        return $users;
    }

    public function get_venue_bookings($venue='') {
        global $DB;

        $params = array('session'=>$this->session, 'exam'=>$this->examid);
        $where = '';
        if($venue) {
            $where = ' AND b.bookedsite = :venue ';
            $params['venue'] = $venue;
        }
        $sql = "SELECT  b.bookedsite, el.name AS venuename, COUNT(b.userid) AS booked, COUNT(ss.userid) AS allocated
                FROM {examregistrar_bookings} b
                JOIN {examregistrar_locations} l ON l.id = b.bookedsite
                JOIN {examregistrar_elements} el ON l.examregid = el.examregid AND el.type = 'locationitem' AND l.location = el.id
                JOIN {examregistrar_exams} e ON b.examid = e.id AND  e.examsession = :session
                JOIN {user} u ON b.userid = u.id
                LEFT JOIN {examregistrar_session_seats} ss ON b.userid = ss.userid AND b.examid = ss.examid
                WHERE b.examid = :exam AND b.booked = 1 $where
                GROUP BY b.bookedsite
                ORDER BY el.name ";

        $venues = $DB->get_records_sql($sql, $params);
        return $venues;
    }

    public function get_room_allocations($venue=-1) {
        global $DB;

        if($venue < 0) {
            $venue = $this->venue;
        }

        $params = array('session'=>$this->session, 'exam'=>$this->examid);
        $where = '';
        if($venue) {
            $where = ' AND ss.bookedsite = :venue ';
            $params['venue'] = $venue;
        }
        $sql = "SELECT  ss.roomid, el.name AS name, ss. examid, COUNT(ss.userid) AS allocated
                FROM {examregistrar_session_seats} ss
                JOIN {examregistrar_locations} l ON l.id = ss.roomid
                JOIN {examregistrar_elements} el ON l.examregid = el.examregid AND el.type = 'locationitem' AND l.location = el.id
                WHERE ss.examid = :exam $where
                GROUP BY ss.roomid
                ORDER BY el.name ";


        $rooms = $DB->get_records_sql($sql, $params);
        return $rooms;
    }
}



class examregistrar_exams_base implements renderable {
    /** @var int $courseid ID of the course the exam belongs to  */
    public $courseid;
    /** @var object $course object of the course the exam belongs to  */
    public $course;
    /** @var int $annuality ID for exams in this review  */
    public $annuality;
    /** @var int $periodid exam period ID for this review  */
    public $periodid;
    /** @var object $period exam period object for this review  */
    public $period;
    /** @var int $examreggistrar examgergistrar instance record */
    public $examregistrar;
    /** @var array $exams collection of exam objects existing for this courseid and period */
    public $exams;
    /** @var object $url moodle_url object for action icons and links */
    public $url;
    /** @var bool $single whether the course is unique or belong to a collection display   */
    public $single;

    public function __construct($examregistrar, $course, $period, $annuality, $baseurl, $single = false) {
        global $DB;
        if(!is_object($course)) {
            $course = $DB->get_record('course', array('id'=>$course), '*', MUST_EXIST);
        }
        if(!is_object($period)) {
            $this->period = null;
            $this->periodid = 0;
            if($period > 0) {
                $period = $DB->get_record('examregistrar_periods', array('id'=>$period), '*', MUST_EXIST);
            }
        }
        $this->examregistrar = $examregistrar;
        $this->courseid = $course->id;
        $this->course = $course;
        $this->period = $period;
        if($period) {
            $this->periodid = $period->id;
        }
        $this->exams = array();
        $this->annuality = $annuality;
        $this->url = $baseurl;
        $this->single = $single;
    }

    protected function preload_exams() {
        global $DB;
        $params = array('courseid'=>$this->courseid);
        if($this->annuality) {
            $params['annuality'] = $this->annuality;
        }
        if($this->period) {
            $params['period'] = $this->period->id;
        }
        $params['visible'] = 1;
        if($exams = $DB->get_records('examregistrar_exams', $params, 'period ASC, examscope ASC, callnum ASC')) {
            $this->exams = $exams;
        }
        return $this->exams;
    }

    public function set_exams() {
        global $DB;

        $this->preload_exams();

        return $this->exams;
    }

}


class examregistrar_exams_course extends examregistrar_exams_base implements renderable {
    /** @var array $examfiles collection indexed by examid, only one (approved) examfile per exam  */
    public $examfiles;

    /** @var array $conflicts collection indexed by examsession, several sites in one session */
    public $conflicts;

    
    public function __construct($examregistrar, $course, $period, $annuality, $baseurl, $single = false) {
        parent::__construct($examregistrar, $course, $period, $annuality, $baseurl, $single);
        $this->examfiles = array();
        $this->conflicts = array();
    }

    public function get_approved_exam($exam) {
        global $DB;

        $message = '';
        $examfile = '';
        if($examfiles = $DB->get_records('examregistrar_examfiles', array('examid'=>$exam->id, 'status'=>EXAM_STATUS_APPROVED),'timeapproved DESC, attempt DESC')) {
            if(count($examfiles) > 1) {
                // error, more than one attempt approved
                $message = get_string('error_manyapproved', 'examregistrar');
            }
            $examfile = reset($examfiles);
        }
        $this->examfiles[$exam->id] = $examfile;
        return $message;
    }

    public function get_exam_bookings($examid, $sort = '') {
        global $DB;
        $bookings = array();
        $allnames = get_all_user_name_fields(true, 'u');
        if(!$sort) {
            $sort = ' u.lastname ASC ';
        } else {
            $sort .= ' , u.lastname ASC ';
        }
        $sql = "SELECT b.id AS bid, b.userid, b.bookedsite, e.name AS sitename, e.idnumber AS siteidnumber, $allnames
                FROM {examregistrar_bookings} b
                JOIN {user} u ON b.userid = u.id
                JOIN {examregistrar_locations} l ON b.bookedsite = l.id
                LEFT JOIN {examregistrar_elements} e ON l.location = e.id AND e.type = 'locationitem'
                WHERE b.examid = :examid AND b.booked = 1
                ORDER BY $sort ";
        $bookings = $DB->get_records_sql($sql, array('examid'=>$examid));

        return $bookings;
    }

    public function set_exams() {
        global $DB, $USER;

        $this->preload_exams();

        foreach($this->exams as $eid => $exam) {
            $examsession = $DB->get_record('examregistrar_examsessions', array('id'=>$exam->examsession), 'id,examdate, duration, timeslot');
            if(!$examsession) {
                $examsession = new stdclass();
                $examsession->examdate = '';
                $examsession->duration= '';
                $examsession->timeslot = '';
            }
            $exam->examdate = $examsession->examdate;
            $exam->duration = $examsession->duration;
            $exam->timeslot = $examsession->timeslot;
            $exam->ownbook = $DB->get_field('examregistrar_bookings', 'bookedsite', array('examid'=>$exam->id, 'userid'=>$USER->id, 'booked'=>1));
            if($exam->ownbook) {
                $exam->ownroom = $DB->get_field('examregistrar_session_seats', 'roomid', array('examsession'=>$exam->examsession, 'examid'=>$exam->id, 'userid'=>$USER->id, 'bookedsite'=>$exam->ownbook));
            } else {
                $exam->ownroom = 0;
            }
            $exam->bookings = $DB->count_records('examregistrar_bookings', array('examid'=>$exam->id, 'booked'=>1));
            $this->exams[$eid] = $exam;
        }

        return $this->exams;
    }

    public function check_booked_exams() {
        global $DB, $USER;
        
        foreach($this->exams as $eid => $exam) {
            if($exam->ownbook) {
                if($othersinsession = $DB->get_records_menu('examregistrar_exams', array('examsession'=>$exam->examsession, 'visible'=>1), 'id,courseid')) {

                    list($insql, $params) = $DB->get_in_or_equal(array_keys($othersinsession), SQL_PARAMS_NAMED, 'sess'); 
                    $select = " examid $insql AND userid = :user AND booked = 1 AND bookedsite <> :bookedsite ";
                    $params['user'] = $USER->id;
                    $params['bookedsite'] = $exam->ownbook;
                    if($DB->record_exists_select('examregistrar_bookings', $select,  $params)) {
                        $this->conflicts[$exam->examsession] = 1;
                    }
                }
            }
        }
    }
}



class examregistrar_exam_attemptsreview implements renderable {
    /** @var int $course ID of course this exam belongs to   */
    public $courseid;
    /** @var int $examid ID of the exam that is reviewed  */
    public $examid;
    /** @var object $exam object of the exam these attemps belongs to  */
    public $exam;
    /** @var array $attempts collection of exam attempts for this exam */
    public $attempts;


    public function __construct($exam) {
        global $DB;
        if(!is_object($exam)) {
            $exam = $DB->get_record('examregistrar_exams', array('id'=>$exam), '*', MUST_EXIST);
        }
        $this->courseid = $exam->courseid;
        $this->examid = $exam->id;
        $this->exam = $exam;
        $this->attempts = array();
    }

    public function set_attempts() {
        global $DB;
        if(!$attempts = $DB->get_records('examregistrar_examfiles', array('examid'=>$this->examid), ' attempt ASC ')) {
            $attempts = array();
            $attempt = new stdClass;
            $attempt->id = 0;
            $attempt->examid = 0;
            $attempt->status = 0;
            $attempt->attempt = 0;
            $attempts[] = $attempt;
        }
        $this->attempts = $attempts;
        return $this->attempts;
    }

    // checks if exists approved or sent
    public function can_send() {
        $cansubmit = false;
        $this->set_attempts();
        if($this->attempts) {
            $cansubmit = true;
            foreach($this->attempts as $attempt) {
                if($attempt->status == 1 || $attempt->status == 1) {
                    $cansubmit = false;
                    return $cansubmit;
                }
            }
        }

        return $cansubmit;
    }

    // checks tracker issue state associated to this exam
    public function get_review($examfile) {
        global $CFG, $DB, $STATUSCODES;
        include_once($CFG->dirroot.'/mod/tracker/lib.php');
        $review = '';
        if($examfile->reviewid) {
            if($issue = $DB->get_record('tracker_issue', array('id'=>$examfile->reviewid))) {
                $moduleid = $DB->get_field('modules', 'id', array('name'=>'tracker'), MUST_EXIST);
                $courseid = $DB->get_field('tracker', 'course', array('id'=>$issue->trackerid), MUST_EXIST);
                $tcm = $DB->get_record('course_modules', array('course'=>$courseid, 'module'=>$moduleid, 'instance'=>$issue->trackerid), '*', MUST_EXIST);
                $status = $issue->status;

                $statusmsg = html_writer::tag('span', '&nbsp;'.get_string('status_'.$STATUSCODES[$status], 'registry').'&nbsp;', array('class'=>'status_'.$STATUSCODES[$status]));

                $trackerurl = new moodle_url('/mod/tracker/view.php', array('id'=>$tcm->id, 'issueid'=>$examfile->reviewid, 'view'=>'view', 'screen'=>'viewanissue'));
                $review = html_writer::link($trackerurl, $statusmsg);
            } else {
                $review = html_writer::span(get_string('missingreview', 'examregistrar'), ' error ');
            }
        }

        return $review;
    }

    // gets exam date from session
    public function get_examdate($exam) {
        global $DB;
        return $DB->get_field('examregistrar_examsessions', 'examdate', array('id'=>$exam->examsession, 'period'=>$exam->period));
    }

    // check exam file origin for special questions usage
//     public function warning_questions_used($examfile) {
//         global $DB;
//
//         $validquestions = get_config('quiz_makeexam', 'validquestions');
//         if($validquestions) {
//             $validquestions = explode(',', $validquestions);
//         } else {
//             return false;
//         }
//
//         if(!$validquestions) {
//             $validquestions = array();
//         }
//
//         $warning = false;
//         if($qme_attempt = $DB->get_record('quiz_makeexam_attempts', array('examid' =>$examfile->examid, 'examfileid'=>$examfile->id, 'status'=>1))) {
//             $qids = explode(',', $qme_attempt->questions);
//             if($usedquestions = $DB->get_records_list('question', 'id', $qids, '', 'id, name, qtype')) {
//                 foreach($usedquestions as $question) {
//                     if(!in_array($question->qtype, $validquestions)) {
//                         $warning = true;
//                         break;
//                     }
//                 }
//             }
//         }
//
//         return $warning;
//     }
}


class examregistrar_exams_coursereview extends examregistrar_exams_base implements renderable {

    public function set_exams() {
        global $DB;

        $this->preload_exams();
        foreach($this->exams as $id => $exam) {
            $this->exams[$id] = new examregistrar_exam_attemptsreview($exam);
        }

        return $this->exams;
    }
}
