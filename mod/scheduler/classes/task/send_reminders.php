<?php

/**
 * Scheduled background task for sending automated appointment reminders
 *
 * @package    mod_scheduler
 * @copyright  2016 Henning Bostelmann and others (see README.txt)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_scheduler\task;

require_once(dirname(__FILE__).'/../../model/scheduler_instance.php');
require_once(dirname(__FILE__).'/../../model/scheduler_slot.php');
require_once(dirname(__FILE__).'/../../model/scheduler_appointment.php');
require_once(dirname(__FILE__).'/../../mailtemplatelib.php');

class send_reminders extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('sendreminders', 'mod_scheduler');
    }

    public function execute() {

        global $DB;

        $date = make_timestamp(date('Y'), date('m'), date('d'), date('H'), date('i'));

        // Find relevant slots in all schedulers.
        $sql = "SELECT s.id, s.schedulerid, s.teacherid, s.starttime, s.emaildate
                FROM {scheduler_slots} s 
                WHERE s.emaildate > 0 AND s.emaildate <= ? AND s.starttime > ?
                AND EXISTS(SELECT a.id FROM {scheduler_appointment} a WHERE a.slotid = s.id AND a.attended = 0)
        ";
        
        $select = 'emaildate > 0 AND emaildate <= ? AND starttime > ? ';
        //$slots = $DB->get_records_sql('scheduler_slots', $select, array($date, $date), 'starttime');
        
        $slots = $DB->get_records_sql($sql, array($date, $date));
        
        mtrace('Processing mod_scheduler slots mailing. Slots to mail: '.count($slots));
        $mailed = 0;
        foreach ($slots as $slot) {
            mtrace('Processing slot ' . $slot->id);
            // Get teacher record.
            $teacher = $DB->get_record('user', array('id' => $slot->teacherid));

            // Get scheduler, slot and course.
            $scheduler = \scheduler_instance::load_by_id($slot->schedulerid);
            $slotm = $scheduler->get_slot($slot->id);
            $course = $scheduler->get_courserec();
            
            $student = null;
            $appointment = null;
            // Send reminder to all students in the slot.
            foreach ($slotm->get_appointments() as $appointment) {
                $student = $DB->get_record('user', array('id' => $appointment->studentid));
                //cron_setup_user($student, $course);
                $vars = scheduler_get_mail_variables($scheduler, $slotm, $teacher, $student, $course, $student); // ecastro ULPGC // old way
                
                if(!scheduler_send_email_from_template($student, $teacher, $course, 'remindtitle', 'reminder', $vars, 'scheduler')) { // ecastro ULPGC
                    $result  = 'failed. ';
                } else {
                    $result  = 'successful. ';
                }
                mtrace("    .... Slot {$slot->id} mail to user {$student->id} $result ");
            }
            // send remainder to teacher
            if(isset($appointment) && $appointment->groupid) {
                $student = $DB->get_record('groups', array('id' => $appointment->groupid));
            }
            
            $vars = scheduler_get_mail_variables($scheduler, $slotm, $teacher, $student, $course, $teacher); // ecastro ULPGC // old way
                
            if(!scheduler_send_email_from_template($teacher, $teacher, $course, 'remindtitle', 'teacherreminder', $vars, 'scheduler')) { // ecastro ULPGC
                $result  = 'failed. ';
            } else {
                $result  = 'successful. ';
                // Mark as sent. (Do this first for safe fallback in case of an exception.)
                $slot->emaildate = -$date;
                $DB->update_record('scheduler_slots', $slot);
                $mailed ++;
            }
            mtrace("    .... Slot {$slot->id} mail to teacher {$slot->teacherid} $result ");            
            
        }
        mtrace("End of mod_scheduler slots mailing. Sent $mailed messages");
    }

}
