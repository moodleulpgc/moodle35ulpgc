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
class send_student_reminders extends base {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('sendstudentreminders', 'block_examswarnings');
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
            list($period, $session, $sendingday) = $this->get_session_days($config, 'examconfirmdays');        
                
        /// email reminders to students with exam
            if($config->enablewarnings && $sendingday) {
                mtrace("...doing students reminders & warnings.");
                mtrace("...config->examconfirmdays ". $config->examconfirmdays);
                $names = get_all_user_name_fields(true, 'u');
                
                $sql = "SELECT b.id AS bid, b.userid, b.booked, b.bookedsite, e.courseid, c.fullname, c.shortname, 
                                u.id, u.username, u.email, u.mailformat, u.idnumber, u.maildisplay, $names
                        FROM {examregistrar_bookings} b
                        JOIN {examregistrar_exams} e ON b.examid = e.id
                        JOIN {course} c ON e.courseid = c.id AND c.visible = 1
                        JOIN {user} u ON u.id = b.userid
                        WHERE e.examregid = :examregid AND e.examsession = :session AND e.visible = 1 AND b.booked = 1
                        GROUP BY b.examid, b.userid
                        ORDER BY b.userid ";
                        // changed to booked = 1, not sending reminders if not booked. 
                        // this simplifies if booked several times, many entries on table.
                        // may add a repeated query with booked = 0 (and not exists booked = 1) to add explicitly unbooked, but not neccesary now  
                if($users = $DB->get_records_sql($sql, array('examregid'=>$config->primaryreg, 'session'=>$session->id ))) {
                    mtrace("    ... doing reserved exam reminders.");
                    
                    // Prepare the message class.
                    $msgdata = examswarnings_prepare_message('exam_student_reminders');
                    $student = \core_user::get_noreply_user();
                    $sent = array();
                    
                    $yesno = array(0=>get_string('no'), 1=>get_string('yes'));
                    $examdate = userdate($session->examdate, '%A %d de %B de %Y');
                    foreach($users as $user) {
                        $message = $config->confirmmessage['text'];
                        list($name, $idnumber) = examregistrar_get_namecodefromid($user->bookedsite, 'locations');
                        $replaces = array('%%course%%' => $user->shortname.'-'.$user->fullname,
                                        '%%date%%' => $examdate,
                                        '%%place%%' => $name,
                                        '%%registered%%' => $yesno[$user->booked],
                                        );
                        foreach($replaces as $search => $replace) {
                            $message = str_replace($search, $replace, $message);
                        }
                        
                        $student = username_load_fields_from_object($student, $user, null, array('id', 'idnumber', 'email', 'mailformat', 'maildisplay'));
                        $student->emailstop = 0;
                        
                        $msgdata->userto = $student;
                        $msgdata->courseid = $user->courseid;
                        $msgdata->subject = get_string('confirmsubject', 'block_examswarnings', $user->shortname);
                        $msgdata->fullmessagehtml = $message;
                        $msgdata->fullmessage = html_to_text($message, 75, false);
                        $msgdata->fullmessageformat = FORMAT_HTML;
                        
                        $flag = '';
                        if(!$config->noemail) {
                            if(!message_send($msgdata)) {
                                $flag = ' - '.get_string('remindersenderror', 'block_examswarnings');
                            }
                        }
                        $sent[] = $user->shortname.': '.fullname($user).$flag;
                    }
                    $this->send_control_email($config, $session, $sent);
                } // end if users
            }
        }
    }
}
