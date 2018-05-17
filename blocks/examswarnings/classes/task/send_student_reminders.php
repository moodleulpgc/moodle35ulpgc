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
class send_student_reminders extends \core\task\scheduled_task {

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

    /// We require some stuff
        $config = get_config('block_examswarnings');
        if(!$config->enablewarnings) {
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
        $sendingday = strtotime("-{$config->examconfirmdays} days", $session->examdate + 60*60*$session->timeslot);
        $sendingday = (($today < $sendingday) && ($sendingday < strtotime("+1 day", $today))) ? true : false;  
        
    /// email reminders to students with exam
        if($config->enablewarnings && $sendingday) {
            mtrace("...doing students reminders & warnings.");
            mtrace("...config->examconfirmdays ". $config->examconfirmdays);
            $names = get_all_user_name_fields(true, 'u');
            
            $sql = "SELECT b.id AS bid, b.userid, b.booked, MIN(b.bookedsite) AS bookedsite, e.courseid, c.fullname, c.shortname, 
                            u.id, u.username, u.email, u.mailformat, u.idnumber, $names
                    FROM {examregistrar_bookings} b
                    JOIN {examregistrar_exams} e ON b.examid = e.id
                    JOIN {course} c ON e.courseid = c.id AND c.visible = 1
                    JOIN {user} u ON u.id = b.userid
                    WHERE e.examregid = :examregid AND e.examsession = :session AND e.visible = 1
                    GROUP BY b.examid, b.userid, b.booked
                    ORDER BY b.userid ";
            if($users = $DB->get_records_sql_menu($sql, array('examregid'=>$config->primaryreg, 'session'=>$session->id ))) {
                mtrace("    ... doing reserved exam reminders.");
                
                $sent = array();
                $from = get_string('examreminderfrom',  'block_examswarnings');
                $yesno = array(0=>get_string('no'), 1=>get_string('yes'));
                $examdate = userdate($session->examdate, '%A %d de %B de %Y');
                foreach($users as $user) {
                    $subject = get_string('confirmsubject', 'block_examswarnings', $user->shortname);
                    $message = $config->confirmmessage;
                    list($name, $idnumber) = examregistrar_get_namecodefromid($user->bookedsite, 'locations');
                    $replaces = array('%%course%%' => $user->shortname.'-'.$user->fullname,
                                    '%%date%%' => $examdate,
                                    '%%place%%' => $name,
                                    '%%registered%%' => $yesno[$user->booked],
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
                    $sent[] = $user->shortname.': '.fullname($user).$flag;
                }
                if($controluser = examswarnings_get_controlemail($config)) {
                    $info = new \stdClass;
                    $info->num = count($sent);
                    $info->date = userdate($session->examdate, '%A %d de %B de %Y');
                    list($sessionname, $idnumber) = examregistrar_item_getelement($session, 'examsession');
                    $subject = get_string('confirmsubject', 'block_examswarnings', "$sessionanme ($idnumber)");
                    $text = get_string('controlmailtxt',  'block_examswarnings', $info )."\n\n".implode("\n", $sent);
                    foreach($controluser as $cu) {
                        $html = ($cu->mailformat == 1) ? get_string('controlmailhtml',  'block_examswarnings', $info ).'<br />'.implode(' <br />', $sent) : '';
                        email_to_user($cu, $from, $subject, $text, $html);
                    }
                    mtrace("    ... sent {$info->num} reserved exam reminders.");
                }
            }
        }
    }
}
