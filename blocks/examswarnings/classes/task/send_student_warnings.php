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
class send_student_warnings extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('sendstudentwarnings', 'block_examswarnings');
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
        list($period, $session, $extra, $check, $lagdays) = examswarnings_get_sessiondata($config);
        $days = $extra ? $config->warningdaysextra : $config->warningdays;
        mtrace("...config->warningdays ". $days);

        $sendingday = strtotime("-{$config->examconfirmdays} days", $session->examdate + 60*60*$session->timeslot);
        $sendingday = (($today < $sendingday) && ($sendingday < strtotime("+1 day", $today))) ? true : false;  
        
    /// email warnings to students without exam
        $start = strtotime("+{$days} days", $today) - DAYSECS;
        $end = strtotime("+{$days} days", $today) + 10;
        $lagtime = strtotime(" - $lagdays days ", $session->examdate);
        mtrace("    ... NOT doing examwarnings after ".userdate($lagtime)." ".$lagtime);
        if($config->enablewarnings && ($now < $lagtime) && ($session->examdate >= $start) && ($session->examdate < $end)) {
            $examdate = userdate($session->examdate, '%A %d de %B de %Y');
            if($exams = $DB->get_records_menu('examregistrar_exams', array('examregid'=>$config->primaryreg, 'examsession'=>$session->id), '', 'id, courseid')) {
                mtrace('    ... there are '.count($exams).' exams on '.$examdate);
                $params = array();
                
                $checkedroles = explode(',', $config->warningroles);
                list($inrolesql, $inparams) = $DB->get_in_or_equal($checkedroles, SQL_PARAMS_NAMED, 'role');
                $params = ($params + $inparams);

                if(!$exams) {
                    $exams = array(0);
                }
                list($incoursesql,$inparams) = $DB->get_in_or_equal($exams, SQL_PARAMS_NAMED, 'exam');
                $params = ($params + $inparams);

                $extrawhere = '';
                if($extra) {
                    $extrawhere = " AND NOT EXISTS (SELECT b2.id FROM {examregistrar_bookings} b2
                                                                JOIN {examregistrar_exams} e2 ON b2.examid = e2.id 
                                                                WHERE b2.booked = 1 AND b2.userid = u.id AND e2.period = e.period AND e2.courseid = e.courseid )";
                }

                $names = get_all_user_name_fields(true, 'u');
                
                $sql = "SELECT DISTINCT ra.id as rid, c.shortname, c.fullname, u.id, u.email, u.mailformat, u.username, u.idnumber, $names
                        FROM {user} u
                            JOIN {role_assignments} ra ON u.id = ra.userid
                            JOIN {context} ctx ON ra.contextid = ctx.id
                            JOIN {course} c ON ctx.instanceid = c.id AND c.visible = 1 AND c.id $incoursesql
                            JOIN {grade_items} gi ON c.id = gi.courseid AND gi.itemtype = 'course'
                            LEFT JOIN {grade_grades} gg ON gi.id = gg.itemid AND u.id = gg.userid
                            JOIN {examregistrar_exams} e ON e.courseid = c.id AND e.examsession = :session AND e.examregid = :examregid AND e.callnum > 0
                        WHERE ra.roleid $inrolesql AND (gg.finalgrade < gi.gradepass OR gg.finalgrade IS NULL)
                            AND NOT EXISTS (SELECT 1 FROM {examregistrar_bookings} b WHERE b.userid = u.id AND b.examid = e.id)
                            $extrawhere
                        GROUP BY u.id, e.id";
                $params['examregid'] = $config->primaryreg;
                $params['session'] = $session->id;

                $users = $DB->get_records_sql($sql, $params);
                if($users) {
                    mtrace("    ... doing NON-reserved exam warnings.");
                    $sent = array();
                    $from = get_string('examreminderfrom',  'block_examswarnings');
                    foreach($users as $user) {
                        $subject = get_string('warningsubject', 'block_examswarnings', $user->shortname);
                        $message = $config->warningmessage;
                        $replaces = array('%%course%%' => $user->shortname.'-'.$user->fullname,
                                        '%%date%%' => $examdate,
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
                        $info->date = $examdate;
                        list($sessionname, $idnumber) = examregistrar_item_getelement($session, 'examsession');
                        $subject = get_string('warningsubject', 'block_examswarnings', "$sessionname ($idnumber)");
                        $text = get_string('controlmailtxt',  'block_examswarnings', $info)."\n\n".implode("\n", $sent);
                        foreach($controluser as $cu) {
                            $html = ($cu->mailformat == 1) ? get_string('controlmailhtml',  'block_examswarnings', $info ).'<br />'.implode(' <br />', $sent) : '';
                            email_to_user($cu, $from, $subject, $text, $html);
                        }
                        mtrace("    ... sent {$info->num} non-reserved exam warnings.");
                    }
                }
            }
        }
    }

}
