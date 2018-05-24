<?php

/**
 * Controller for all teacher-related views.
 *
 * @package    mod
 * @subpackage scheduler
 * @copyright  2011 Henning Bostelmann and others (see README.txt)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


function scheduler_action_doaddsession($scheduler, $formdata) {

    global $DB, $output;

    $data = (object) $formdata;

    $fordays = 0;
    if ($data->rangeend > 0){
        $fordays = ($data->rangeend - $data->rangestart) / DAYSECS;
    }

    // Create as many slots of $duration as will fit between $starttime and $endtime and that do not conflict.
    $countslots = 0;
    $couldnotcreateslots = '';
    $startfrom = $data->rangestart+($data->starthour*60+$data->startminute)*60;
    $endat = $data->rangestart+($data->endhour*60+$data->endminute)*60;
    $slot = new stdClass();
    $slot->schedulerid = $scheduler->id;
    $slot->teacherid = $data->teacherid;
    $slot->appointmentlocation = $data->appointmentlocation;
    $slot->exclusivity = $data->exclusivityenable ? $data->exclusivity : 0;
    if($data->divide) {
        $slot->duration = $data->duration;
    } else {
        $slot->duration = $data->endhour*60+$data->endminute-$data->starthour*60-$data->startminute;
    };
    $slot->notes = '';
    $slot->notesformat = FORMAT_HTML;
    $slot->timemodified = time();
    if(isset($data->shared)) { // ecastro ULPGC
        $slot->shared = $data->shared;
    }
/*
    if(isset($data->etutor)) {
        $slot->etutor = $data->etutor;
    }
*/
    //for ($d = 0; $d <= $fordays; $d ++) {
        //$starttime = $startfrom + ($d * DAYSECS);
    // ecastro ULPGC time calculations with DateTime. DAYSECS and fordays is unrealiable around DST boundaries
    $daydatetime = new DateTime('now', new DateTimeZone(get_user_timezone())); // ecastro ULPGC
    $daydatetime->setTimestamp($startfrom);
    $starttime = $startfrom;
    $endtime = ($data->rangeend) ? $data->rangeend : $data->rangestart;
    $endtime = $endtime + ($data->starthour*60+$data->startminute)*60;

    while($starttime <= $endtime) {
        $eventdate = usergetdate($starttime);
        $dayofweek = $eventdate['wday'];
        if ((($dayofweek == 1) && ($data->monday == 1)) ||
        (($dayofweek == 2) && ($data->tuesday == 1)) ||
        (($dayofweek == 3) && ($data->wednesday == 1)) ||
        (($dayofweek == 4) && ($data->thursday == 1)) ||
        (($dayofweek == 5) && ($data->friday == 1)) ||
        (($dayofweek == 6) && ($data->saturday == 1)) ||
        (($dayofweek == 0) && ($data->sunday == 1))) {
            $slot->starttime = make_timestamp($eventdate['year'], $eventdate['mon'], $eventdate['mday'], $data->starthour, $data->startminute);
            $data->timestart = $slot->starttime;
            $data->timeend = make_timestamp($eventdate['year'], $eventdate['mon'], $eventdate['mday'], $data->endhour, $data->endminute);

            // this corrects around midnight bug
            if ($data->timestart > $data->timeend) {
                $data->timeend += DAYSECS;
            }
            if ($data->hideuntilrel == 0) {
                $slot->hideuntil = time();
            } else {
                $slot->hideuntil = make_timestamp($eventdate['year'], $eventdate['mon'], $eventdate['mday'], 6, 0) - $data->hideuntilrel;
            }
            if ($data->emaildaterel == -1) {
                $slot->emaildate = 0;
            } else {
                $slot->emaildate = make_timestamp($eventdate['year'], $eventdate['mon'], $eventdate['mday'], 0, 0) - $data->emaildaterel;
            }
            while ($slot->starttime <= $data->timeend - $slot->duration * 60) {
                $conflicts = scheduler_get_conflicts($scheduler->id, $data->timestart, $data->timestart + $slot->duration * 60, $data->teacherid, 0, SCHEDULER_ALL, false);
                if ($conflicts) {
                    if (!$data->forcewhenoverlap) {
                        print_string('conflictingslots', 'scheduler');
                        echo '<ul>';
                        foreach ($conflicts as $aconflict) {
                            $sql = 'SELECT c.fullname, c.shortname, s.name as schedname, sl.starttime '
                                    .'FROM {course} c, {scheduler} s, {scheduler_slots} sl '
                                    .'WHERE s.course = c.id AND sl.schedulerid = s.id AND sl.id = :conflictid';
                            $conflictinfo = $DB->get_record_sql($sql, array('conflictid' => $aconflict->id));
                            $msg = $output->userdate($conflictinfo->starttime) . ', ' . $output->usertime($conflictinfo->starttime) . ': ';
                            $msg .= s($conflictinfo->schedname). ' '.get_string('incourse', 'scheduler') . ' ';
                            $msg .= $conflictinfo->shortname . ' - ' . $conflictinfo->fullname;
                            echo html_writer::tag('li', $msg);
                        }
                        echo '</ul><br/>';
                    } else { // we force, so delete all conflicting before inserting
                        $deleted = 0;
                        foreach ($conflicts as $conflict) {
                            // ecastro ULPGC
                            $sched = ($conflict->schedulerid == $scheduler->id) ? $scheduler : scheduler_instance::load_by_id($conflict->schedulerid);
                            $conflictslot = scheduler_slot::load_by_id($conflict->id, $sched);
                            $n = $conflictslot->get_appointment_count();
                            if(!$n ) {
                                \mod_scheduler\event\slot_deleted::create_from_slot($conflictslot, 'addsession-conflict')->trigger();
                                $conflictslot->delete();
                                $deleted += 1;
                            }
                        }
                        if($deleted) { // ecastro ULPGC
                            echo $output->action_message(get_string('deletedslots', 'scheduler', $deleted));
                        }
                    }
                }
                if (!$conflicts || $data->forcewhenoverlap) {
                    $slotid = $DB->insert_record('scheduler_slots', $slot, true, true);
                    $slotobj = $scheduler->get_slot($slotid);
                    \mod_scheduler\event\slot_added::create_from_slot($slotobj)->trigger();

                    $countslots++;
                }
                $slot->starttime += ($slot->duration + $data->break) * 60;
                $data->timestart += ($slot->duration + $data->break) * 60;
            }
        }
        $daydatetime->modify('+ 1 day'); // ecastro ULPGC enforce use dateTime on time calculations
        $starttime = $daydatetime->getTimestamp();
    }
    echo $output->action_message(get_string('slotsadded', 'scheduler', $countslots));
}


// Require valid session key for all actions.
require_sesskey();

$actionmessage = ''; //ecastro ULPGC

// We first have to check whether some action needs to be performed
switch ($action) {
    /************************************ Deleting a slot ***********************************************/
    case 'deleteslot': {
        $slotid = required_param('slotid', PARAM_INT);
        $slot = $scheduler->get_slot($slotid);
        if(!$slot->is_attended() || has_capability('mod/scheduler:manageallappointments', $context)) { // ecastro ULPGC security, do not delete attended slots
            \mod_scheduler\event\slot_deleted::create_from_slot($slot, 'delete-slot')->trigger();
            $slot->delete();
        } else {
            $actionmessage = $output->notification(get_string('attendednondeletable', 'scheduler', $slot->id));
        }
        break;
    }
    /************************************ Deleting multiple slots ***********************************************/
    case 'deleteslots': {
        $slots = array();
        if($slotids = required_param('items', PARAM_SEQUENCE)) { // ecastro ULPGC avoid fatal error if nothing selected
            $slots = explode(",", $slotids);
        }
        $caneditall = has_capability('mod/scheduler:manageallappointments', $context); 
        $keep = array();
        foreach ($slots as $slotid) {
            $slot = $scheduler->get_slot($slotid);
            if(!$slot->is_attended() || $caneditall) { // ecastro ULPGC
                $slot->delete();
            } else {
                $keep[] = $slot->id;
            }
        }
        if($keep) {
            $actionmessage = $output->notification(get_string('attendednondeletable', 'scheduler', implode(', ', $keep)));
        }
        break;
    }
    /************************************ Students were seen ***************************************************/
    case 'saveseen': {
        // get required param
        $slotid = required_param('slotid', PARAM_INT);
        $slot = $scheduler->get_slot($slotid);
        $seen = optional_param_array('seen', array(), PARAM_INT);

        if (is_array($seen)) {
            foreach ($slot->get_appointments() as $app) {
                $app->attended = (in_array($app->id, $seen)) ? 1 : 0 ;
                $app->timemodified = time();
            }
        }
        $slot->save();
        break;
    }
    /************************************ Revoking all appointments to a slot ***************************************/
    case 'revokeall': {
        $slotid = required_param('slotid', PARAM_INT);
        $slot = $scheduler->get_slot($slotid);

        $oldstudents = array();
        foreach ($slot->get_appointments() as $app) {
            $oldstudents[] = $app->studentid;
            $slot->remove_appointment($app);
            $slot->appointmentnote = '';  // ecastro ULPGC
        }
        // notify student
        if ($scheduler->allownotifications) {
            include_once($CFG->dirroot.'/mod/scheduler/mailtemplatelib.php');
            foreach ($oldstudents as $oldstudent) {
                $student = $DB->get_record('user', array('id' => $oldstudent));
                $teacher = $DB->get_record('user', array('id' => $slot->teacherid));

                $vars = scheduler_get_mail_variables($scheduler, $slot, $teacher, $student, $COURSE, $student);
                scheduler_send_email_from_template($student, $teacher, $COURSE, 'cancelledbyteacher', 'teachercancelled', $vars, 'scheduler');
            }
        }

        $slot->save();
        break;
    }

    /************************************ Mark seen all appointments to a slot ***************************************/
    case 'seenall': { // ecastro ULPGC
        // get required param
        $slotid = required_param('slotid', PARAM_INT);
        $slot = $scheduler->get_slot($slotid);

        $now = time();
        foreach ($slot->get_appointments() as $app) {
            $app->attended = 1 ;
            $app->timemodified = $now;
        }
        $slot->save();
        break;
    }


    /************************************ Toggling to unlimited group ***************************************/
    case 'allowgroup':{
        $slotid = required_param('slotid', PARAM_INT);
        $slot = new stdClass();
        $slot->id = $slotid;
        $slot->exclusivity = 0;
        $DB->update_record('scheduler_slots', $slot);
        break;
    }

    /************************************ Toggling to single student ******************************************/
    case 'forbidgroup':{
        $slotid = required_param('slotid', PARAM_INT);
        $slot = new stdClass();
        $slot->id = $slotid;
        $slot->exclusivity = 1;
        $DB->update_record('scheduler_slots', $slot);
        break;
    }

    /************************************ Deleting all slots ***************************************************/
    case 'deleteall':{
        require_capability('mod/scheduler:manageallappointments', $context);
        foreach ($scheduler->get_all_slots() as $slot) {
            $slot->delete();
        }
        break;
    }
    /************************************ Deleting unused slots *************************************************/
    // MUST STAY HERE, JUST BEFORE deleteallunused
    case 'deleteunused':{
        $teacherClause = " AND s.teacherid = {$USER->id} ";
    }
    /************************************ Deleting unused slots (all teachers) ************************************/
    case 'deleteallunused': {
        if (!isset($teacherClause)) $teacherClause = '';
        if (has_capability('mod/scheduler:manageallappointments', $context)) {
            $sql = "
            SELECT
            s.id,
            s.id
            FROM
            {scheduler_slots} s
            LEFT JOIN
            {scheduler_appointment} a
            ON
            s.id = a.slotid
            WHERE
            s.schedulerid = ? AND a.studentid IS NULL
            {$teacherClause}
            ";
            if ($unappointed = $DB->get_records_sql($sql, array($scheduler->id))) {
                list($usql, $params) = $DB->get_in_or_equal(array_keys($unappointed));
                $DB->delete_records_select('scheduler_slots', "schedulerid = $cm->instance AND id $usql ", $params);
            }
        }
        break;
    }
    /************************************ Deleting current teacher's slots ***************************************/
    case 'deleteonlymine': {
        $caneditall = has_capability('mod/scheduler:manageallappointments', $context); 
        $keep = array();
        foreach ($scheduler->get_slots_for_teacher($USER->id) as $slot) {
            if(!$slot->is_attended() || $caneditall) { // ecastro ULPGC
                $slot->delete();
            } else {
                $keep[] = $slot->id;
            }
        }
        if($keep) {
            $actionmessage = $output->notification(get_string('attendednondeletable', 'scheduler', implode(', ', $keep)));
        }
        break;
    }
    /************************************ Mark as seen now *******************************************************/
    case 'markasseennow': {
        $slot = new stdClass();
        $slot->schedulerid = $scheduler->id;
        $slot->teacherid = $USER->id;
        $slot->starttime = time();
        $slot->duration = $scheduler->defaultslotduration;
        $slot->exclusivity = 1;
        $slot->notes = '';
        $slot->notesformat = FORMAT_HTML;
        $slot->hideuntil = time();
        $slot->appointmentlocation = '';
        $slot->emaildate = 0;
        $slot->timemodified = time();
        $slotid = $DB->insert_record('scheduler_slots', $slot);

        $appointment = new stdClass();
        $appointment->slotid = $slotid;
        $appointment->studentid = required_param('studentid', PARAM_INT);
        $appointment->attended = 1;
        $appointment->appointmentnote = '';
        $appointment->appointmentnoteformat = FORMAT_HTML;
        $appointment->timecreated = time();
        $appointment->timemodified = time();
        $DB->insert_record('scheduler_appointment', $appointment);

        $slot = $scheduler->get_slot($slotid);
        \mod_scheduler\event\slot_added::create_from_slot($slot)->trigger();

        break;
    }
}

/*************************************************************************************************************/
