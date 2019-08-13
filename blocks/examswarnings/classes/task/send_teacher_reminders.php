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
class send_teacher_reminders extends base {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('sendteacherreminders', 'block_examswarnings');
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
            list($period, $session, $sendingday) = $this->get_session_days($config, 'remindersdays');        
             
        /// checks & execute    
            if($config->enablereminders && $sendingday) {
                mtrace("...doing reminders for teachers.");
                mtrace("...config->reminderdays ". $config->reminderdays);
                mtrace(' ... reminders for session '. date('Y-m-d', $session->examdate));

                // e.callnum > 0  exclude special call exams
                $sql = "SELECT e.id, e.courseid
                        FROM {examregistrar_exams} e
                        WHERE e.examregid = :examregid AND e.examsession = :session AND e.visible = 1 AND e.callnum > 0
                                AND EXISTS (SELECT 1 FROM {examregistrar_bookings} b WHERE b.examid = e.id AND b.booked = 1) ";
                if($exams = $DB->get_records_sql_menu($sql, array('examregid'=>$config->primaryreg, 'session'=>$session->id ))) {
                    $params = array();
                    list($inrolesql, $inparams) = $DB->get_in_or_equal($config->reminderroles);
                    $params = array_merge($params, $inparams);

                    list($incoursesql,$inparams) = $DB->get_in_or_equal($exams);
                    $params = array_merge($params, $inparams);
                    $names = get_all_user_name_fields(true, 'u');

                    $sql = "SELECT DISTINCT ra.id as rid, c.id AS courseid, c.shortname, c.fullname, u.id, u.email, u.mailformat, u.username, u.mailformat, $names
                            FROM {user} u
                                JOIN {role_assignments} ra ON u.id = ra.userid
                                JOIN {context} ctx ON ra.contextid = ctx.id
                                JOIN {course} c ON ctx.instanceid = c.id AND c.visible = 1
                            WHERE ra.roleid $inrolesql AND c.id $incoursesql ";

                    //mtrace("...sql  ".$sql );
                    $users = $DB->get_records_sql($sql, $params);
                    if($users) {
                        mtrace("...Entrando en users.");
                        
                        // Prepare the message class.
                        $msgdata = examswarnings_prepare_message('exam_teacher_reminders');
                        $staff = \core_user::get_noreply_user();
                        
                        $sent = array();
                        
                        foreach($users as $user) {
                            $message = $config->remindermessage['text'];
                            $replaces = array('%%course%%' => $user->shortname.'-'.$user->fullname,
                                            '%%date%%' => userdate($session->examdate, '%A %d de %B de %Y'),
                                            );
                            foreach($replaces as $search => $replace) {
                                $message = str_replace($search, $replace, $message);
                            }
                            
                            $staff = username_load_fields_from_object($staff, $user, null, array('id', 'idnumber', 'email', 'mailformat', 'maildisplay'));
                            $staff->emailstop = 0;
                            
                            $msgdata->userto = $staff;
                            $msgdata->courseid = $user->courseid;
                            $msgdata->subject =  $subject = get_string('examremindersubject', 'block_examswarnings', $user->shortname);
                            $msgdata->fullmessagehtml = $message;
                            $msgdata->fullmessage = html_to_text($message, 75, false);
                            $msgdata->fullmessageformat = FORMAT_HTML;
                            
                            $flag = '';
                            if(!$config->noemail) {
                                if(!message_send($msgdata)) {
                                    $flag = ' - '.get_string('remindersenderror', 'block_examswarnings');
                                }
                            }
                            $sent[] = $user->shortname.': '.fullname($user, false, 'lastname firstname').$flag;
                        }
                        $this->send_control_email($config, $session, $sent);
                    }
                } // end of if exams
            }
        }
    }
}
