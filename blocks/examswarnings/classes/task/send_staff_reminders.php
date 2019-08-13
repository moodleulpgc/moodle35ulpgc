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
class send_staff_reminders extends base {

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
        
        if(!$configs = $this->get_configs()) {
            return true;
        }

    /// checks for once a day except if in debugging mode 
        if(!debugging('', DEBUG_DEVELOPER)) {   
            if(self::get_last_run_time() < strtotime("+1 day", usergetmidnight(time()))) { 
                return true;
            }
        }
        
        require_once($CFG->dirroot.'/blocks/examswarnings/locallib.php');
        
        foreach($configs as $config) {
            /// gets session data & sending day    
            list($period, $session, $sendingday) = $this->get_session_days($config, 'roomcalldays');        
        
        /// email reminders to room staff with exam
            if($config->enableroomcalls && $sendingday) {
                mtrace("...doing reminders for room staff.");
                mtrace("...config->roomcalldays ". $config->roomcalldays);
                mtrace(' ... room staff reminders for session '. date('Y-m-d', $session->examdate));
            
                list($inrolesql, $inparams) = $DB->get_in_or_equal($config->roomcallroles, SQL_PARAMS_NAMED, 'role');
                $params = array_merge($params, $inparams);

                $sql = "SELECT s.id as sid, l.*, e.name AS name, e.idnumber AS idnumber, r.course AS courseid
                        FROM {examregistrar_staffers} s
                        JOIN {examregistrar_locations} l ON l.id = s.locationid
                        JOIN {examregistrar_elements} e ON e.examregid = l.examregid AND e.type='locationitem' AND e.id = l.location
                        JOIN {examregistrar_session_rooms} sr ON sr.examsession = s.examsession AND sr.roomid = s.locationid AND sr.available = 1
                        JOIN {examregistrar} r ON e.examregid = = r.id
                        WHERE  s.userid > 0 AND s.examsession = :session AND s.visible = 1
                                AND s.role $inrolesql
                                AND EXISTS (SELECT 1 FROM {examregistrar_session_seats} ss WHERE ss.examsession = s.examsession AND ss.roomid = s.locationid )
                        GROUP BY s.locationid ";

                if($rooms = $DB->get_records_sql($sql, $params)) {
                
                    // Prepare the message class.
                    $msgdata = examswarnings_prepare_message('exam_staff_reminders');
                    $staff = \core_user::get_noreply_user();
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
                                                u.id, u.email, u.mailformat, u.username, u.maildisplay, $names
                                    FROM {examregistrar_staffers} s
                                    JOIN {user} u ON u.id = s.userid
                                    JOIN {examregistrar_elements} e ON e.type = 'roleitem' AND e.id = s.role
                                    WHERE s.examsession = :session AND s.locationid = :room AND s.visible = 1
                                    GROUP BY s.userid ";

                        $users = $DB->get_records_sql($sql, array('session'=>$session->id, 'room'=>$room->id));
                        if($users) {
                            mtrace("...Entrando en users.");
                            foreach($users as $user) {
                                $message = $config->roomcallmessage['text'];
                                $replaces = array('%%roomname%%' => $room->name, '%%roomidnumber%%' => $room->idnumber,
                                                    '%%rolename%%' => $user->rolename, '%%roleidnumber%%' => $user->roleidnumber,
                                                '%%date%%' => userdate($session->examdate, '%A %d de %B de %Y'),
                                                '%%examlist%%'=>$examnames,
                                                );
                                foreach($replaces as $search => $replace) {
                                    $message = str_replace($search, $replace, $message);
                                }
                                
                                $staff = username_load_fields_from_object($staff, $user, null, array('id', 'idnumber', 'email', 'mailformat', 'maildisplay'));
                                $staff->emailstop = 0;
                                
                                $msgdata->userto = $staff;
                                $msgdata->courseid = $room->courseid;
                                $msgdata->subject = get_string('roomcallsubject', 'block_examswarnings', $room->idnumber);
                                $msgdata->fullmessagehtml = $message;
                                $msgdata->fullmessage = html_to_text($message, 75, false);
                                $msgdata->fullmessageformat = FORMAT_HTML;
                                
                                $flag = '';
                                if(!$config->noemail) {
                                    if(!message_send($msgdata)) {
                                        $flag = ' - '.get_string('remindersenderror', 'block_examswarnings');
                                    }
                                }
                                $sent[] = $room->name.': '.fullname($user).$flag;
                            }
                        }
                    }
                    $this->send_control_email($config, $session, $sent);
                } // end if rooms
            }
        }
    }
}
