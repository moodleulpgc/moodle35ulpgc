<?php

/**
 * A class for representing a scheduler slot.
 *
 * @package    mod
 * @subpackage scheduler
 * @copyright  2011 Henning Bostelmann and others (see README.txt)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * A class for representing a scheduler slot.
 *
 * @copyright  2014 Henning Bostelmann and others (see README.txt)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
class scheduler_slot extends mvc_child_record_model {

    protected $appointments;

    protected function get_table() {
        return 'scheduler_slots';
    }

    public function __construct(scheduler_instance $scheduler) {
        parent::__construct();
        $this->data = new stdClass();
        $this->data->id = 0;
        $this->set_parent($scheduler);
        $this->data->schedulerid = $scheduler->get_id();
        $this->appointments = new mvc_child_list($this, 'scheduler_appointment', 'slotid',
                        new scheduler_appointment_factory($this));
    }

    /**
     * Create a scheduler slot from the database.
     */
    public static function load_by_id($id, scheduler_instance $scheduler) {
        $slot = new scheduler_slot($scheduler);
        $slot->load($id);
        return $slot;
    }

    /**
     * Create a scheduler slot from an already loaded record
     */
    /*  public static function load_from_record(stdClass $record, scheduler_instance $scheduler) {
     $slot = new scheduler_slot($scheduler);
    $slot->data = $record;
    return $slot;
    }

    */
    /**
     * Save any changes to the database
     */
    public function save() {
        $this->data->schedulerid = $this->get_parent()->get_id();
        parent::save();
        $this->appointments->save_children();
        $this->update_calendar();
    }


    public function get_scheduler() {
        return $this->get_parent();
    }

    /**
     * Return the teacher object
     */
    public function get_teacher() {
        global $DB;
        if ($this->data->teacherid) {
            return $DB->get_record('user', array('id' => $this->data->teacherid), '*', MUST_EXIST);
        } else {
            return new stdClass();
        }
    }


    /**
     * Return the end time of the slot
     */
    public function get_endtime() {
        return $this->data->starttime + $this->data->duration*MINSECS;
    }

    /**
     * Is this slot bookable in its bookable period for students.
     * This checks for the availability time of the slot and for the "guard time" restriction,
     * but not for the number of actualy booked appointments.
     */
    public function is_in_bookable_period() {
        $available = $this->hideuntil <= time();
        $beforeguardtime = $this->starttime > time() + $this->parent->guardtime;
        return $available && $beforeguardtime;
    }

    /**
     * Is this a group slot (i.e., more than one student is permitted)
     */
    public function is_groupslot() {
        return (boolean) !($this->data->exclusivity == 1);
    }


    public function get_appointment_count() {
        return $this->appointments->get_child_count();
    }

    /**
     * Get the appointment in this slot for a specific student, or null if the student doesn't have one.
     *
     * @param int $studentid the id number of the student in question
     * @return scheduler_appointment the appointment for the specified student
     */
    public function get_student_appointment($studentid) {
        $studapp = null;
        foreach ($this->get_appointments() as $app) {
            if ($app->studentid == $studentid) {
                $studapp = $app;
                break;
            }
        }
        return $studapp;
    }


    /**
     * Has the slot been attended?
     */
    public function is_attended() {
        $isattended = false;
        foreach ($this->appointments->get_children() as $app) {
            $isattended = $isattended || $app->attended;
        }
        return $isattended;
    }

    /**
     * Has the slot been booked by a specific student?
     */
    public function is_booked_by_student($studentid) {
        $result = false;
        foreach ($this->get_appointments() as $appointment) {
            $result = $result || $appointment->studentid == $studentid;
        }
        return $result;
    }

    public function count_remaining_appointments() {
        if ($this->exclusivity == 0) {
            return -1;
        } else {
            $scheduler = $this->get_parent();
            if($scheduler->bookingrouping == 1) { // ecastro ULPGC if only groups, count groups, noy single students
                $rem = $this->exclusivity - $this->get_group_appointment_count();
            } else {
                $rem = $this->exclusivity - $this->get_appointment_count();
            }
            if ($rem < 0) {
                $rem = 0;
            }
            return $rem;
        }
    }

    /**
     *  Get the number of different groups that haves group-appointment in this scheduler
     */
    public function get_group_appointment_count() { // ecastro ULPGC
        $groups = array();
        $appointments = $this->appointments->get_children();
        foreach($appointments as $appointment) {
            if($appointment->groupid) {
                $groups[$appointment->groupid] = $appointment->groupid;
            }
        }
        return count($groups);
    }

    /**
     *  Get an appointment by ID
     */
    public function get_appointment($id) {
        return $this->appointments->get_child_by_id($id);
    }

    /**
     *  Get an array of all appointments
     */
    public function get_appointments($userfilter = null) {
        $apps = $this->appointments->get_children();
        if ($userfilter) {
            foreach ($apps as $key => $app) {
                if (!in_array($app->studentid, $userfilter)) {
                    unset($apps[$key]);
                }
            }
        }
        return array_values($apps);
    }

    /**
     *  Get an array of group appointments
     */
    public function get_group_appointments() { // ecastro ULPGC
        global $DB;
        $groups = array();
        $appointments = $this->appointments->get_children();
        foreach($appointments as $appointment) {
            if($appointment->groupid) {
                $groups[$appointment->groupid] = $appointment->groupid;
            }
        }

        if($groups) {
            list($insql, $params) = $DB->get_in_or_equal(array_keys($groups));
            $groups = $DB->get_records_select_menu('groups', "id $insql", $params,  ' name ASC', 'id, name' );
        }
        return $groups;
    }

    /**
     * Create a new appointment relating to this slot.
     */
    public function create_appointment() {
        return $this->appointments->create_child();
    }

    /**
     * Remove an appointment from this slot.
     */
    public function remove_appointment($app) {
        $this->appointments->remove_child($app);
        $this->clear_calendar($app); // ecastro ULPGC
    }

    public function delete() {
        $this->appointments->delete_children();
        $this->clear_calendar();
        parent::delete();
    }


    /* The event code is SSstu (for a student event) or SSsup (for a teacher event).
     * then, the id of the scheduler slot that it belongs to.
    * finally, the courseID (legacy reasons -- not really used),
    * all in a colon delimited string. This will run into problems when the IDs of slots and courses
    * are bigger than 7 digits in length...
    */

    private function get_teacher_eventtype() {
        $slotid = $this->get_id();
        $courseid = $this->get_parent()->get_courseid();
        return "SSsup:{$slotid}:{$courseid}";
    }


    private function get_student_eventtype() {
        $slotid = $this->get_id();
        $courseid = $this->get_parent()->get_courseid();
        return "SSstu:{$slotid}:{$courseid}";
    }

    private function get_group_eventtype($groupid = 0) { // ecastro ULPGC
        $slotid = $this->get_id();
        $courseid = $this->get_parent()->get_courseid();
        return "group:$groupid:{$slotid}:{$courseid}";
    }

    private function clear_calendar($app = false) {
        global $DB;
        $slotid = $this->get_id();
        $courseid = $this->get_parent()->get_courseid();
        $instanceid = $this->get_parent_id();
        $eventtype = "%:{$slotid}:{$courseid}";
        $select = $DB->sql_like('eventtype', '?').' AND instance = ? AND modulename = ? ';
        $params = array($eventtype, $instanceid, 'scheduler');
        if($app) {
            if($app->groupid) {
                $select .= ' AND groupid = ? ';
                $params[] = $app->groupid;
            } else {
                $user = ' userid = ? ';
                $params[] = $app->studentid;
                $apps = $this->get_appointment_count();
                if($apps == 1) {
                    $user .= " OR userid = ? ";
                    $params[] =  $this->data->teacherid;
                }
                $select .= " AND ( $user ) ";
            }
        }

        $DB->delete_records_select('event', $select, $params);
    }

    private function update_calendar() {

        global $DB;

        $scheduler = $this->get_parent();

        $myappointments = $this->appointments->get_children();

        $studentids = array();
        $groupids = array(); // ecastro ULPGC
        foreach ($myappointments as $appointment) {
            if (!$appointment->is_attended()) {
                //$studentids[] = $appointment->studentid;
                if($appointment->groupid) { // ecastro ULPGC
                    $groupids[$appointment->groupid] = $appointment->groupid; // ecastro avoid duplicates
                } else {
                    $studentids[$appointment->studentid] = $appointment->studentid; // ecastro avoid duplicates
                }
            }
        }

        $teacher = $DB->get_record('user', array('id' => $this->teacherid));
        $students = $DB->get_records_list('user', 'id', $studentids);
        $studentnames = array();
        foreach ($students as $student) {
            $studentnames[$student->id] = fullname($student);
        }

        $groupnames = array();
        $groups = $DB->get_records_list('groups', 'id', $groupids); // ecastro ULPGC to allow group events
        foreach ($groups as $group) {
            $groupnames[$group->id] = format_string($group->name);
        }

        $schedulername = $scheduler->get_name(true);
        $schedulerdescription = $scheduler->get_intro();

        $slotid = $this->get_id();
        $courseid = $scheduler->get_courseid();

        $baseevent = new stdClass();
        $baseevent->description = "$schedulername<br/><br/>{$this->notes}<br/><br/>{$this->appointmentnote}   "; // ecastro ULPGC
        $baseevent->format = 1;
        $baseevent->modulename = 'scheduler';
        $baseevent->courseid = 0;
        $baseevent->instance = $this->get_parent_id();
        $baseevent->timestart = $this->starttime;
        $baseevent->timeduration = $this->duration * MINSECS;
        $baseevent->visible = 1;

        // Update student events.

        $studentevent = clone($baseevent);
        $studenteventname = get_string('meetingwith', 'scheduler').' '.$scheduler->get_teacher_name().', '.fullname($teacher);
        $studentevent->name = shorten_text($studenteventname, 200);

        $this->update_calendar_events( $this->get_student_eventtype(), $studentids, $studentevent);

        // Update teacher events.

        $teacherids = array();
        $teacherevent = clone($baseevent);
        $teachereventname = get_string('meetingwith', 'scheduler'); // ecastro ULPGC
        if (count($studentids) > 0) {
            $teacherids[] = $teacher->id;
            if (count($studentids) > 1) {
                $teachereventname = get_string('meetingwithplural', 'scheduler').' '.get_string('students', 'scheduler');
                $teacherevent->description .= '<br/><br/>'.get_string('students', 'scheduler').', '.implode(', ', $studentnames);
            } else {
                $teachereventname = get_string('meetingwith', 'scheduler').' '.
                                get_string('student', 'scheduler').', '.reset($studentnames);
            }
            $teacherevent->name = shorten_text($teachereventname, 200);
        }

        if($studentids) { 
        $this->update_calendar_events( $this->get_teacher_eventtype(), $teacherids, $teacherevent);
        }

        $this->update_calendar_events($this->get_group_eventtype(), array(), $teacherevent, $groupnames); // ecastro ULPGC

    }

    private function update_calendar_events($eventtype, array $userids, $eventdata, $groupnames = array()) { // ecastro ULPGC allow group events

        global $CFG, $DB;
        require_once($CFG->dirroot.'/calendar/lib.php');

        $eventdata->eventtype = $eventtype;

        $existingevents = $DB->get_records('event', array('modulename' => 'scheduler', 'eventtype' => $eventtype));
        $handledevents = array();
        $handledusers = array();
        $handledgroups = array(); // ecastro ULPGC

        // Update existing calendar events.
        foreach ($existingevents as $eventid => $existingdata) {
            if (in_array($existingdata->userid, $userids)) {
                $eventdata->userid = $existingdata->userid;
                $calendarevent = calendar_event::load($existingdata);
                $calendarevent->update($eventdata, false);
                $handledevents[] = $eventid;
                $handledusers[] = $existingdata->userid;
            }
        }

        // Add new calendar events.
        foreach ($userids as $userid) {
            if (!in_array($userid, $handledusers)) {
                $thisevent = clone($eventdata);
                $thisevent->userid = $userid;
                calendar_event::create($thisevent, false);
            }
        }

    // ecastro ULPGC
        $groupevents = array();
        foreach ($groupnames as $groupid => $groupname) { // ecastro ULPGC
            $eventtype = $this->get_group_eventtype($groupid);
            $groupevents = $groupevents + $DB->get_records('event', array('modulename' => 'scheduler', 'eventtype' => $eventtype));
        }

        $existingevents = $existingevents + $groupevents;
        $scheduler = $this->get_parent();
        $courseid = $scheduler->get_courseid();
        // Update existing calendar GROUP events.
        foreach ($groupevents as $eventid => $existingdata) {
            if (in_array($existingdata->groupid, array_keys($groupnames))) {
                $eventdata->groupid = $existingdata->groupid;
                $eventdata->courseid = $courseid;
                $calendarevent = calendar_event::load($existingdata);
                $calendarevent->update($eventdata, false);
                $handledevents[] = $eventid;
                $handledgroups[] = $existingdata->groupid;
            }
        }

        // Add new calendar GROUP events.
        $context = context_course::instance($courseid);
        $url = new moodle_url('/user/index.php', array('contextid'=>$context->id, 'group'=>0));
        ///user/index.php?contextid=1400&roleid=0&id=5&perpage=20&accesssince=0&search=&group=209
        foreach ($groupnames as $groupid => $groupname) { // ecastro ULPGC
            if (!in_array($groupid, $handledgroups)) {
                $thisevent = clone($eventdata);
                $thisevent->name =  get_string('meetingwith', 'scheduler').' '.get_string('group').' '.$groupname;
                $eventdata->eventtype = $this->get_group_eventtype($groupid);
                $url->param('group', $groupid);
                $thisevent->description .= '<br/><br/>'.html_writer::link($url, $groupname);
                $thisevent->userid = 0;
                $thisevent->groupid = $groupid;
                $thisevent->courseid = $courseid;
                calendar_event::create($thisevent, false);
            }
        }
    // ecastro ULPGC

        // Remove old, obsolete calendar events.
        foreach ($existingevents as $eventid => $existingdata) {
            if (!in_array($eventid, $handledevents)) {
                $calendarevent = calendar_event::load($existingdata);
                $calendarevent->delete();
            }
        }

    }


}

class scheduler_slot_factory extends mvc_child_model_factory {
    public function create_child(mvc_record_model $parent) {
        return new scheduler_slot($parent);
    }
}

