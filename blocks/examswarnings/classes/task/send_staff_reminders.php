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

namespace block_examswarnings\task;

/**
 * Simple task to run the cron.
 */
class send_staff_reminders extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('sendstaffreminders', 'block_examswarnings');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $DB;

    /// We require some stuff
        $config = get_config('block_examswarnings');
        if(!$config->enableroomcalls) {
            return true;
        }
        
        $now = time();
        $today = usergetmidnight($now);  
        
    /// checks for once a day except if in debugging mode 
        if(!debugging('', DEBUG_DEVELOPER)) {   
            if(self::get_last_run_time() < strtotime("+1 day", $today)) { 
                return true;
            }
        }

        require_once($CFG->dirroot.'/blocks/examswarnings/locallib.php');
        
    /// gets sending day    
        list($period, $session) = examswarnings_get_session_period($config);
        $sendingday = strtotime("-{$config->roomcalldays} days", $session->examdate + 60*60*$session->timeslot);
        $sendingday = (($today < $sendingday) && ($sendingday < strtotime("+1 day", $today))) ? true : false;  
        
    /// email reminders to room staff with exam
        if($config->enableroomcalls && $sendingday) {
            mtrace("...doing reminders for room staff.");
            mtrace("...config->roomcalldays ". $config->roomcalldays);
            mtrace(' ... room staff reminders for session '. date('Y-m-d', $session->examdate));
        
            $checkedroles = explode(',', $config->roomcallroles);
            list($inrolesql, $inparams) = $DB->get_in_or_equal($checkedroles, SQL_PARAMS_NAMED, 'role');
            $params = array_merge($params, $inparams);

            $sql = "SELECT s.id as sid, l.*, e.name AS name, e.idnumber AS idnumber
                    FROM {examregistrar_staffers} s
                    JOIN {examregistrar_locations} l ON l.id = s.locationid
                    JOIN {examregistrar_elements} e ON e.examregid = l.examregid AND e.type='locationitem' AND e.id = l.location
                    JOIN {examregistrar_session_rooms} sr ON sr.examsession = s.examsession AND sr.roomid = s.locationid AND sr.available = 1
                    WHERE  s.userid > 0 AND s.examsession = :session AND s.visible = 1
                            AND s.role $inrolesql
                            AND EXISTS (SELECT 1 FROM {examregistrar_session_seats} ss WHERE ss.examsession = s.examsession AND ss.roomid = s.locationid )
                    GROUP BY s.locationid ";

            if($rooms = $DB->get_records_sql($sql, $params)) {
                $sent = array();
                foreach($rooms as $room) {

                    $sql = "SELECT e.*, ss.bookedsite
                            FROM {examregistrar_session_seats} ss
                            JOIN {examregistrar_exams} e ON ss.examid = e.id
                            JOIN {course} c ON c.id = e.courseid
                            WHERE ss.examsession = :session AND ss.roomid = :room
                            GROUP BY ss.examid
                            ORDER BY c.shortname ASC ";
                    $exams = $DB->get_records_sql($sql, array('session'=>$session->id, 'room'=>$room->id));
                    $examnames = array();
                    foreach($exams as $exam) {
                        $examnames[$exam->id] = $exam->shortname.' - '.$exam->fullname.'<br />';
                    }
                    $names = get_all_user_name_fields(true, 'u');
                    
                    $sql = "SELECT s.id AS sid, s.info, s.role, e.name AS rolename, e.idnumber AS roleidnumber,
                                            u.id, u.email, u.mailformat, u.username, $names
                                FROM {examregistrar_staffers} s
                                JOIN {user} u ON u.id = s.userid
                                JOIN {examregistrar_elements} e ON e.type = 'roleitem' AND e.id = s.role
                                WHERE s.examsession = :session AND s.locationid = :room AND s.visible = 1
                                GROUP BY s.userid ";

                    $users = $DB->get_records_sql($sql, array('session'=>$session->id, 'room'=>$room->id));
                    if($users) {
                        mtrace("...Entrando en users.");
                        $from = get_string('examreminderfrom',  'block_examswarnings');
                        foreach($users as $user) {
                            $subject = get_string('roomcallsubject', 'block_examswarnings', $room->idnumber);
                            $message = $config->roomcallmessage;
                            $replaces = array('%%roomname%%' => $room->name, '%%roomidnumber%%' => $room->idnumber,
                                                '%%rolename%%' => $user->rolename, '%%roleidnumber%%' => $user->roleidnumber,
                                            '%%date%%' => userdate($session->examdate, '%A %d de %B de %Y'),
                                            '%%examlist%%'=>$examnames,
                                            );
                            foreach($replaces as $search => $replace) {
                                $message = str_replace($search, $replace, $message);
                            }
                            $text = html_to_text($message, 75, false);
                            $html = ($user->mailformat == 1) ? format_text($message, FORMAT_HTML) : '';
                            $flag = '';
                            if(!$config->noemail) {
                                if(!email_to_user($user, $from, $subject, $text, $html)) {
                                    $flag = ' - '.get_string('remindersenderror', 'block_examswarnings');
                                }
                            }
                            $sent[] = $room->name.': '.fullname($user).$flag;
                        }
                    }
                }
                if($controluser = examswarnings_get_controlemail($config)) {
                    $info = new \stdClass;
                    $info->num = count($sent);
                    $info->date = userdate($session->examdate, '%A %d de %B de %Y');
                    list($sessionname, $idnumber) = examregistrar_item_getelement($session, 'examsession');
                    $subject = get_string('controlmailsubject', 'block_examswarnings', "$sessionanme ($idnumber)");
                    $text = get_string('controlmailtxt',  'block_examswarnings', $info )."\n\n".implode("\n", $sent);
                    foreach($controluser as $cu) {
                        $html = ($cu->mailformat == 1) ? get_string('controlmailhtml',  'block_examswarnings', $info ).'<br />'.implode(' <br />', $sent) : '';
                        email_to_user($cu, $from, $subject, $text, $html);
                    }
                    mtrace('    ... sent '.count($sent).' staff exam reminders.');
                }
            } // end if rooms
        }
    }

}
