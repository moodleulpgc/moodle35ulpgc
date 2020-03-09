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
 * A scheduled task to send custom messages with waitlist.
 *
 * @package   enrol_waitlist
 * @author    Enrique Castro @ ULPGC
 * @copyright Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace enrol_waitlist\task;

use coding_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * A scheduled task to send reminders to waitlist enrolments.
 *
 * @package   enrol_waitlist
 * @author    Enrique Castro @ ULPGC
 * @copyright Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_reminders extends \core\task\scheduled_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_name() {
        return get_string('task:enrolment_reminders', 'enrol_waitlist');
    }

    /**
     * Execute the task.
     *
     * @return bool true if everything is fine
     */
    public function execute() {
        global $CFG, $DB;
        
        $plugin = enrol_get_plugin('waitlist');

        if ($plugin === null){
            mtrace("plugin not active returning");
            return true;
        }

        mtrace("processing waitlist enrolment reminders ");
        
        $sql = "SELECT wif.*, wid.course_id,  wid.data, c.shortname AS courseshortname, c.fullname AS coursename, c.startdate  
                FROM {waitlist_info_field} wif 
                JOIN {waitlist_info_data} wid ON wid.fieldid = wif.id
                JOIN {course} c ON c.id = wid.course_id
                WHERE wif.shortname = :name AND (wid.data IS NOT NULL AND wid.data <> '') 
                        AND DATEDIFF(FROM_UNIXTIME(c.startdate), DATE_ADD(CURRENT_TIMESTAMP, INTERVAL wid.data day)) = 0
                ";
        
        if($instances = $DB->get_records_sql($sql, array('name'=>'reminder'))) {
            $noreplyuser = \core_user::get_noreply_user();
            $noreplyuser->firstname = get_string('noreplyname', 'enrol_waitlist');
            $message = new \core\message\message();
            $message->userfrom  = $noreplyuser;
            $message->name = 'reminder';
            $message->component = 'enrol_waitlist';
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->smallmessage     = '';
            foreach($instances as $enrol) {
                mtrace("   ... sending waitlist enrolment reminders for course {$enrol->courseshortname} ");
            
                $message->courseid         = $enrol->course_id;
                $message->contexturl       = $CFG->wwwroot.'/course/view.php?id='.$enrol->course_id;
                $message->contexturlname   = $enrol->coursename;    
                $context = \context_course::instance($enrol->course_id);
                $replaces = array(  '%%FULLNAME%%'  => \html_writer::link($message->contexturl, $enrol->coursename),
                                    '%%SHORTNAME%%' => \html_writer::link($message->contexturl, $enrol->courseshortname),
                                    '%%STARTDATE%%' => userdate($enrol->startdate, get_string('strftimedaydate', 'langconfig')),);
                
                // send reminders to students
                $count = 0;
                if($users = get_enrolled_users($context, 'mod/assign:submit', 0, 'u.*', null, 0, 0, true)) { 
                    $subject = $enrol->param1 ? $enrol->param1 : get_string('defaultremindersubject', 'enrol_waitlist');
                    $message->subject = $enrol->courseshortname.': '. $subject;
                    foreach($users as $user) {
                        $replaces['%%FIRSTNAME%%'] =  $user->firstname;
                        $replaces['%%LASTNAME%%'] =  $user->lastname;
                        $message->fullmessagehtml = str_replace(array_keys($replaces), $replaces, $enrol->param2);
                        $message->fullmessage = html_to_text($message->fullmessagehtml);
                        $message->userto = $user;
                        if(message_send($message)) {
                            $count++;
                        }
                    }
                }
                mtrace("   ... sent $count waitlist student reminders for course {$enrol->courseshortname} ");
                
                // send reminders to teachers
                $count = 0;
                if($users = get_enrolled_users($context, 'moodle/course:update', 0, 'u.*', null, 0, 0, true)) { 
                    $subject = $enrol->param3 ? $enrol->param3 : get_string('defaultremindersubject', 'enrol_waitlist');
                    $message->subject          = $enrol->courseshortname.': '. $subject;
                    foreach($users as $user) {
                        $replaces['%%FIRSTNAME%%'] =  $user->firstname;
                        $replaces['%%LASTNAME%%'] =  $user->lastname;
                        $message->fullmessagehtml = str_replace(array_keys($replaces), $replaces, $enrol->param4);
                        $message->fullmessage = html_to_text($message->fullmessagehtml);
                        $message->userto = $user;
                        if(message_send($message)) {
                            $count++;
                        }
                    }
                    mtrace("   ... sent $count waitlist teacher reminders for course {$enrol->courseshortname} ");
                }
            }
        }
        return true;
    }
}
