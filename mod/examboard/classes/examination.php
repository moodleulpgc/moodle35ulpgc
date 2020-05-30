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
 * Class definition for mod_examboard examination 
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_examboard;
 
defined('MOODLE_INTERNAL') || die();

/**
 * The Examination class holds data to get and manipulate an exam instance. 
 * keeps track of examiners, examinees, venues, dates etc for an examination event
 *
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class examination {

    /** @var int the id of exam . */
    public $id;
    
    /** @var int the id of the examboardmodule this data belongs to. */
    public $examboardid;
    
    /** @var int the id of the board assigned to this examination. */
    public $boardid = 0;
    
    /** @var int the id of the group used by board. */
    public $groupid = 0;
    
    /** @var array examiners board assigned to this examination. */
    public $members = false;

    /** @var string the board title word. */
    public $title;
    
    /** @var string the board idnumber codename. */
    public $idnumber;
    
    /** @var string the board descriptive name. */
    public $name;
    
    /** @var int flag to set the examination visible / invisible to regular users. */
    public $boardactive = 1;
    
    /** @var int grading strategy from examboard instance. */
    public $grademode = '';

    /** @var string the examperiod name  */
    public $examperiod = '';
    
    /** @var string the exam session name  */
    public $sessionname = '';
    
    /** @var string the classroom name in which this examination will take place. */
    public $venue;

    /** @var string the classroom name in which this examination will take place. */
    public $accessurl;
    
    /** @var int the date when this thsi examinatioc wll happen. */
    public $examdate;

    /** @var int the time period scheduled for this examination. */
    public $duration;
    
    /** @var int flag to set the examination visible / invisible to regular users. */
    public $active = 1;
    
    /** @var array the list of examinee objects corresponding to students taking the exam. Indexed by examineeid. */
    public $examinees = false;

    /** @var array the list of tutors objects corresponding to examinees. Indexed by examineeid. */
    public $tutors = false;

    /** @var array the list of grade records corresponding to examinees. Indexed by examineeid. */
    public $grades = false;
    
    
    /**
     * Constructor.
     * @param stdClass a DB record joining examboard_exam & examboard_board tables
     */
    public function __construct($examrec) {
        $this->id = $examrec->id;
        $this->examboardid = $examrec->examboardid;
        $this->boardid = $examrec->boardid;
        $this->groupid = $examrec->groupid;
        $this->title = $examrec->title;
        $this->idnumber = $examrec->idnumber;
        $this->name = $examrec->name;
        $this->boardactive = $examrec->boardactive;
        
        $options = get_config('examboard', 'examperiods');
        $examperiods = array();
        foreach(explode("\n", $options) as $conv) {
            $key = strstr(trim($conv), ':', true);
            $examperiods[$key] = ltrim(strstr($conv, ':'), ':');
        }
        if(array_key_exists($examrec->examperiod, $examperiods)) {
            $this->examperiod = $examperiods[$examrec->examperiod];
        } else {
            $this->examperiod = get_string('none');
        }
        
        $this->examperiod = $examrec->examperiod;
        
        
        
        $this->sessionname = $examrec->sessionname;
        $this->venue = $examrec->venue;
        $this->accessurl  = $examrec->accessurl;
        $this->examdate = $examrec->examdate;
        $this->duration = $examrec->duration;
        $this->active = $examrec->examactive;
        
        $this->grademode = '';
        $this->members = array();
        $this->examinees = array();
        $this->tutors = array();
    }
    
    
    public static function get_from_id($examid) {
        global $DB;
        
        $params = array('examid' => $examid);
        $sql = "SELECT e.*, e.active AS examactive, b.id AS boardid, b.title, b.groupid, b.name, b.idnumber, b.active AS boardactive
                FROM {examboard_exam} e
                JOIN {examboard_board} b ON e.examboardid = b.examboardid AND e.boardid = b.id
                WHERE e.id = :examid ";
        if($examrec = $DB->get_record_sql($sql, $params)) {
            return new examination($examrec);
        }
        
        return false;
    }
    
    
    public function load_board_members($search = '') {
        global $DB;
        
        if($search) {
            $search = ' AND '. $search;
        }
        
        $names = get_all_user_name_fields(true, 'u');
        
        $params = array('boardid' => $this->boardid, 'examid' => $this->id);
        $sql = "SELECT m.userid AS uid, m.id AS mid, m.*, c.confirmed, c.exemption,  u.id, u.idnumber, u.picture, u.imagealt, u.email, u.mailformat, $names
                FROM {examboard_member} m 
                JOIN {user} u ON m.userid = u.id
                LEFT JOIN {examboard_confirmation} c ON c.examid = :examid AND c.userid = m.userid 
                WHERE m.boardid = :boardid $search 
                ORDER BY m.sortorder ASC";
        
        $this->members = $DB->get_records_sql($sql, $params);
        return $this->members;
    }
    
    public function load_examinees($search='') {
        global $DB;
    
        if($search) {
            $search = ' AND '. $search;
        }
        
        $names = get_all_user_name_fields(true, 'u');
        
        $params = array('examid' => $this->id);
        $sql = "SELECT e.userid AS uid, e.id AS eid, e.*, u.id, u.idnumber, u.picture, u.imagealt, u.email, u.mailformat, $names
                FROM {examboard_examinee} e 
                JOIN {user} u ON e.userid = u.id
                WHERE e.examid = :examid $search 
                ORDER BY e.sortorder ASC";
        
        $this->examinees = $DB->get_records_sql($sql, $params);
        return $this->examinees;
    }
    
    
    public function load_examinees_with_tutors($search='', $sortorder = '', $limitfrom = 0, $limitnum = 0) {
        global $DB;
    
        if($search) {
            $search = ' AND '. $search;
        }
        
        if($sortorder) {
            $sortorder = $sortorder.', ';
        }
        
        $names = get_all_user_name_fields(true, 'u');
        
        $grades = '';
        if($this->grademode == EXAMBOARD_GRADING_AVG) {
            $grades = ' AVG(g.grade) AS grade, ';
        } elseif($this->grademode == EXAMBOARD_GRADING_MAX) {
            $grades = ' MAX(g.grade) AS grade, ';
        } elseif($this->grademode == EXAMBOARD_GRADING_MIN) {
            $grades = ' MIN(g.grade) AS grade, ';
        }
        
        $params = array('examid' => $this->id);
        $sql = "SELECT e.userid AS uid, e.id AS eid, e.*, t.tutorid, tu.lastname AS tutor, u.lastname AS examinee, $grades
                        u.id, u.idnumber, u.picture, u.imagealt, u.email, u.mailformat, $names
                FROM {examboard_examinee} e 
                JOIN {user} u ON e.userid = u.id
                LEFT JOIN {examboard_tutor} t ON e.examid = t.examid AND e.userid = t.userid AND t.main = 1
                LEFT JOIN {examboard_grades} g ON g.examid = e.examid AND e.userid = g.userid
                LEFT JOIN {user} tu ON t.tutorid = tu.id
                
                WHERE e.examid = :examid $search 
                GROUP BY e.userid
                ORDER BY $sortorder e.sortorder ASC";
        
        $this->examinees = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
        return $this->examinees;
    }
    
    
    
    public function count_examinees($search='') {
        global $DB;
        
        $params = array('examid' => $this->id);
        $select = ' examid = :examid ';
        if($search) {
            $select .= ' AND '.$search;
        }
        return $DB->count_records_select('examboard_examinee', $select, $params);
    }
    
    public function load_tutors($search='') {
        global $DB;
        
        if($search) {
            $search = ' AND '. $search;
        }
    
        $names = get_all_user_name_fields(true, 'u');
        $params = array('examid' => $this->id);
        $sql = "SELECT t.id AS tid, t.userid AS uid, t.*, u.id, u.idnumber, u.picture, u.imagealt, u.email, u.mailformat, $names
                FROM {examboard_tutor} t
                JOIN {user} u ON t.tutorid = u.id
                WHERE   t.examid = :examid $search 
                ORDER BY t.main DESC, u.lastname ASC, u.firstname ";
        $tutors =  $DB->get_records_sql($sql, $params);
        
        $this->tutors = array();
        foreach($tutors as $tid => $tutor) {
            if(!isset($this->tutors[$tutor->userid])) {
                $this->tutors[$tutor->userid] = array();
            }
            $this->tutors[$tutor->userid][$tutor->tutorid] = $tutor;
        }
        return $this->tutors;
    }
    
    
    public function load_grades($userid = 0) {
        global $DB;
        
        $params = array('examid'=>$this->id);
        $usersearch = '';
        if($userid) {
            $usersearch = ' AND g.userid = :userid ';
            $params['userid'] = $userid;
        }
        
        $sql = "SELECT g.*, m.boardid, m.sortorder, m.deputy, c.exemption 
                    FROM {examboard_grades} g
                    JOIN {examboard_exam} e ON e.id = g.examid
                    JOIN {examboard_member} m ON e.boardid = m.boardid AND g.grader = m.userid
                    LEFT JOIN {examboard_confirmation} c ON c.examid = g.examid AND c.userid = g.grader
                WHERE g.examid = :examid $usersearch
                ORDER BY g.userid ASC, m.sortorder ASC     
                ";
        $rawgrades = $DB->get_records_sql($sql, $params);
        $grades = array();
        foreach($rawgrades as $gid => $grade) {
            $grades[$grade->userid][$gid] = $grade;
        }
        
        if(!$this->grades || !$userid) {
            $this->grades = $grades;
        } elseif($userid) {
            $this->grades[$userid] = $grades;    
        } 
        return $this->grades;
    }

    public function is_grader($userid = 0, $wodeputies = false) {
        global $DB, $USER;
        
        if(!$userid) {
            $userid = $USER->id;
        }
        
        $params = array('boardid' => $this->boardid, 'userid'=>$userid);
        if($wodeputies) {
            $params['deputy'] = 0; 
        }
        
        return $DB->record_exists('examboard_member', $params);
    }
    
    public function is_active_member($userid = 0) {
        global $DB, $USER;
        
        if(!$userid) {
            $userid = $USER->id;
        }
        
        $params = array('boardid' => $this->boardid, 'userid'=>$userid, 'deputy' => 0 );
        $select = ' boardid = :boardid AND userid = :userid AND deputy = :deputy 
                    AND sortorder <= 1 AND sortorder >= 0 ';
                    
        return $DB->record_exists_select('examboard_member', $select, $params);
    }
    
    
}
