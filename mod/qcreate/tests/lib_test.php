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
 * Unit tests for (some of) mod/qcreate/lib.php.
 *
 * @package    mod_qcreate
 * @category   phpunit
 * @copyright  2014 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/qcreate/lib.php');
require_once($CFG->dirroot . '/mod/qcreate/locallib.php');
require_once($CFG->dirroot . '/mod/qcreate/tests/base_test.php');

/**
 * Unit tests for (some of) mod/qcreate/lib.php.
 *
 * @copyright  2014 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_qcreate_lib_testcase extends mod_qcreate_base_testcase {

    protected function setUp() {
        parent::setUp();

        // Add additional default data.

    }

    public function test_qcreate_print_overview() {
        global $DB;
        $courses = $DB->get_records('course', array('id' => $this->course->id));
        $qcreate = $this->create_instance();

        // Create a question as student0.
        $this->setUser($this->students[0]);
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $standardq = $questiongenerator->create_question('shortanswer', null,
                array('category' => $qcreate->get_question_category()->id));

        // Check the overview as the different users.
        $overview = array();
        qcreate_print_overview($courses, $overview);
        $this->assertDebuggingCalledCount(1);
        $this->assertEquals(count($overview), 1);

        $this->setUser($this->teachers[0]);
        $overview = array();
        qcreate_print_overview($courses, $overview);
        $this->assertDebuggingCalledCount(1);
        $this->assertEquals(count($overview), 1);

        $this->setUser($this->editingteachers[0]);
        $overview = array();
        qcreate_print_overview($courses, $overview);
        $this->assertDebuggingCalledCount(1);
        $this->assertEquals(1, count($overview));
    }

    public function test_print_recent_activity() {
        $this->setUser($this->editingteachers[0]);
        $qcreate = $this->create_instance();

        // Create a question as student0.
        $this->setUser($this->students[0]);
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $standardq = $questiongenerator->create_question('shortanswer', null,
                array('category' => $qcreate->get_question_category()->id));

        $this->expectOutputRegex('/New questions created:/');
        qcreate_print_recent_activity($this->course, true, time() - 3600);
    }

    public function test_qcreate_get_recent_mod_activity() {
        $this->setUser($this->editingteachers[0]);
        $qcreate = $this->create_instance();

        // Create a question as student0.
        $this->setUser($this->students[0]);
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $standardq = $questiongenerator->create_question('shortanswer', null,
                array('category' => $qcreate->get_question_category()->id));

        $activities = array();
        $index = 0;

        $activity = new stdClass();
        $activity->type    = 'activity';

        $activity->cmid    = $qcreate->get_course_module()->id;
        $activities[$index++] = $activity;

        qcreate_get_recent_mod_activity( $activities,
                                        $index,
                                        time() - 3600,
                                        $this->course->id,
                                        $qcreate->get_course_module()->id);

        $this->assertEquals("qcreate", $activities[1]->type);
    }

    public function test_qcreate_user_complete() {
        global $PAGE;

        $this->setUser($this->editingteachers[0]);
        $qcreate = $this->create_instance();

        $PAGE->set_url(new moodle_url('/mod/qcreate/view.php', array('id' => $qcreate->get_course_module()->id)));

        $this->expectOutputRegex('/Grade: -/');
        qcreate_user_complete($this->course, $this->students[0], $qcreate->get_course_module(), $qcreate->get_instance());

        // Create a question as student0.
        $this->setUser($this->students[0]);
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $standardq = $questiongenerator->create_question('shortanswer', null,
                array('category' => $qcreate->get_question_category()->id));

        $this->expectOutputRegex('/Grade: -/');

        qcreate_user_complete($this->course, $this->students[0], $qcreate->get_course_module(), $qcreate->get_instance());
    }

    public function test_qcreate_get_completion_state() {
        $qcreate = $this->create_instance();

        $this->setUser($this->students[0]);
        $result = qcreate_get_completion_state($this->course, $qcreate->get_course_module(), $this->students[0]->id, false);
        $this->assertFalse($result);

        // Create a question as student0.
        $this->setUser($this->students[0]);
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $standardq = $questiongenerator->create_question('shortanswer', null,
                array('category' => $qcreate->get_question_category()->id));

        $result = qcreate_get_completion_state($this->course, $qcreate->get_course_module(), $this->students[0]->id, false);

        $this->assertTrue($result);
    }

    /**
     * Tests for mod_qcreate_refresh_events.
     */
    public function test_qcreate_refresh_events() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $timeopen = time();
        // 7 days duration.
        $timeclose = time() + DAYSECS * 7;
        $newtimeclose = $timeclose + DAYSECS;

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a qcreate.
        $qcreate = $this->getDataGenerator()->create_module('qcreate', array('course' => $course->id,
            'timeopen' => $timeopen, 'timeclose' => $timeclose));

        // Make sure the calendar events for qcreate 1 matches the initial parameters.
        $this->assertTrue(qcreate_refresh_events($course->id));
        $eventparams = array('modulename' => 'qcreate', 'instance' => $qcreate->id, 'eventtype' => 'open');
        $openevent = $DB->get_record('event', $eventparams, '*', MUST_EXIST);
        $this->assertEquals($openevent->timestart, $timeopen);

        $eventparams = array('modulename' => 'qcreate', 'instance' => $qcreate->id, 'eventtype' => 'close');
        $closeevent = $DB->get_record('event', $eventparams, '*', MUST_EXIST);
        $this->assertEquals($closeevent->timestart, $timeclose);

        // In case the course ID is passed as a numeric string.
        $this->assertTrue(qcreate_refresh_events('' . $course->id));
        // Course ID not provided.
        $this->assertTrue(qcreate_refresh_events());
        $eventparams = array('modulename' => 'qcreate');
        $events = $DB->get_records('event', $eventparams);
        foreach ($events as $event) {
            if ($event->modulename === 'qcreate' && $event->instance === $qcreate->id && $event->eventtype === 'open') {
                $this->assertEquals($event->timestart, $timeopen);
            }
            if ($event->modulename === 'qcreate' && $event->instance === $qcreate->id && $event->eventtype === 'close') {
                $this->assertEquals($event->timestart, $timeclose);
            }
        }
        // Manually update qcreate 1's close time.
        $DB->update_record('qcreate', (object)['id' => $qcreate->id, 'timeclose' => $newtimeclose]);

        // Then refresh the qcreate events of qcreate 1's course.
        $this->assertTrue(qcreate_refresh_events($course->id));

        // Confirm that the qcreate 1's close date event now has the new close date after refresh.
        $eventparams = array('modulename' => 'qcreate', 'instance' => $qcreate->id, 'eventtype' => 'close');
        $eventtime = $DB->get_field('event', 'timestart', $eventparams, MUST_EXIST);
        $this->assertEquals($eventtime, $newtimeclose);

        // Create a second course and qcreate.
        $course2 = $this->getDataGenerator()->create_course();
        $qcreate2 = $this->getDataGenerator()->create_module('qcreate', array('course' => $course2->id,
            'timeopen' => $timeopen, 'timeclose' => $timeclose));

        // Manually update qcreate 1 and 2's close dates.
        $newtimeclose += DAYSECS;
        $DB->update_record('qcreate', (object)['id' => $qcreate->id, 'timeclose' => $newtimeclose]);
        $DB->update_record('qcreate', (object)['id' => $qcreate2->id, 'timeclose' => $newtimeclose]);

        // Refresh events of all courses.
        $this->assertTrue(qcreate_refresh_events());

        // Check the due date calendar event for qcreate 1.
        $eventtime = $DB->get_field('event', 'timestart', $eventparams, MUST_EXIST);
        $this->assertEquals($eventtime, $newtimeclose);

        // Check the due date calendar event for qcreate 2.
        $eventparams['instance'] = $qcreate2->id;
        $eventtime = $DB->get_field('event', 'timestart', $eventparams, MUST_EXIST);
        $this->assertEquals($eventtime, $newtimeclose);
    }

    public function test_qcreate_core_calendar_provide_event_action_open() {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a qcreate.
        $qcreate = $this->getDataGenerator()->create_module('qcreate', array('course' => $course->id,
            'timeopen' => time() - DAYSECS, 'timeclose' => time() + DAYSECS));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $qcreate->id, QCREATE_EVENT_TYPE_OPEN);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $actionevent = mod_qcreate_core_calendar_provide_event_action($event, $factory);

        // Confirm the event was decorated.
        $this->assertInstanceOf('\core_calendar\local\event\value_objects\action', $actionevent);
        $this->assertEquals(get_string('attemptqcreatenow', 'qcreate'), $actionevent->get_name());
        $this->assertInstanceOf('moodle_url', $actionevent->get_url());
        $this->assertEquals(1, $actionevent->get_item_count());
        $this->assertTrue($actionevent->is_actionable());
    }

    public function test_qcreate_core_calendar_provide_event_action_closed() {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a qcreate.
        $qcreate = $this->getDataGenerator()->create_module('qcreate', array('course' => $course->id,
            'timeclose' => time() - DAYSECS));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $qcreate->id, QCREATE_EVENT_TYPE_CLOSE);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $actionevent = mod_qcreate_core_calendar_provide_event_action($event, $factory);

        // No event on the dashboard if module is closed.
        $this->assertNull($actionevent);
    }

    public function test_qcreate_core_calendar_provide_event_action_open_in_future() {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a qcreate.
        $qcreate = $this->getDataGenerator()->create_module('qcreate', array('course' => $course->id,
            'timeopen' => time() + DAYSECS));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $qcreate->id, QCREATE_EVENT_TYPE_OPEN);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $actionevent = mod_qcreate_core_calendar_provide_event_action($event, $factory);

        // Confirm the event was decorated.
        $this->assertInstanceOf('\core_calendar\local\event\value_objects\action', $actionevent);
        $this->assertEquals(get_string('attemptqcreatenow', 'qcreate'), $actionevent->get_name());
        $this->assertInstanceOf('moodle_url', $actionevent->get_url());
        $this->assertEquals(1, $actionevent->get_item_count());
        $this->assertFalse($actionevent->is_actionable());
    }

    public function test_qcreate_core_calendar_provide_event_action_no_capability() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a student.
        $student = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

        // Enrol student.
        $this->assertTrue($this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id));

        // Create a qcreate.
        $qcreate = $this->getDataGenerator()->create_module('qcreate', array('course' => $course->id));

        // Remove the permission to submit the qcreate for the student role.
        $coursecontext = context_course::instance($course->id);
        assign_capability('mod/qcreate:view', CAP_PROHIBIT, $studentrole->id, $coursecontext);
        assign_capability('mod/qcreate:submit', CAP_PROHIBIT, $studentrole->id, $coursecontext);

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $qcreate->id, QCREATE_EVENT_TYPE_OPEN);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Set current user to the student.
        $this->setUser($student);

        // Confirm null is returned.
        $this->assertNull(mod_qcreate_core_calendar_provide_event_action($event, $factory));
    }

    /**
     * Creates an action event.
     *
     * @param int $courseid
     * @param int $instanceid The qcreate id.
     * @param string $eventtype The event type. eg. QCREATE_EVENT_TYPE_OPEN.
     * @return bool|calendar_event
     */
    private function create_action_event($courseid, $instanceid, $eventtype) {
        $event = new stdClass();
        $event->name = 'Calendar event';
        $event->modulename  = 'qcreate';
        $event->courseid = $courseid;
        $event->instance = $instanceid;
        $event->type = CALENDAR_EVENT_TYPE_ACTION;
        $event->eventtype = $eventtype;
        $event->timestart = time();

        return calendar_event::create($event);
    }

    /**
     * Test the callback responsible for returning the completion rule descriptions.
     * This function should work given either an instance of the module (cm_info), such as when checking the active rules,
     * or if passed a stdClass of similar structure, such as when checking the the default completion settings for a mod type.
     */
    public function test_mod_qcreate_completion_get_active_rule_descriptions() {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Two activities, both with automatic completion. One has the 'completionquestions' rule, one doesn't.
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 2]);
        $qcreate1 = $this->getDataGenerator()->create_module('qcreate', [
            'course' => $course->id,
            'completion' => 2,
            'completionquestions' => 3
        ]);
        $qcreate2 = $this->getDataGenerator()->create_module('qcreate', [
            'course' => $course->id,
            'completion' => 2,
            'completionquestions' => 0
        ]);
        $cm1 = cm_info::create(get_coursemodule_from_instance('qcreate', $qcreate1->id));
        $cm2 = cm_info::create(get_coursemodule_from_instance('qcreate', $qcreate2->id));

        // Data for the stdClass input type.
        // This type of input would occur when checking the default completion rules for an activity type, where we don't have
        // any access to cm_info, rather the input is a stdClass containing completion and customdata attributes, just like cm_info.
        $moddefaults = new stdClass();
        $moddefaults->customdata = ['customcompletionrules' => [
            'completionquestions' => 3,
        ]];
        $moddefaults->completion = 2;

        $activeruledescriptions = [
            get_string('completionquestionsdesc', 'qcreate', 3)
        ];

        $this->assertEquals(mod_qcreate_get_completion_active_rule_descriptions($cm1), $activeruledescriptions);
        $this->assertEquals(mod_qcreate_get_completion_active_rule_descriptions($cm2), []);
        $this->assertEquals(mod_qcreate_get_completion_active_rule_descriptions($moddefaults), $activeruledescriptions);
        $this->assertEquals(mod_qcreate_get_completion_active_rule_descriptions(new stdClass()), []);
    }

    /**
     * An unkown event type should not change the qcreate instance.
     */
    public function test_mod_qcreate_core_calendar_event_timestart_updated_unknown_event() {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $qcreategenerator = $generator->get_plugin_generator('mod_qcreate');
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $qcreate = $qcreategenerator->create_instance(['course' => $course->id]);
        $qcreate->timeopen = $timeopen;
        $qcreate->timeclose = $timeclose;
        $DB->update_record('qcreate', $qcreate);

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'qcreate',
            'instance' => $qcreate->id,
            'eventtype' => QCREATE_EVENT_TYPE_OPEN . "SOMETHING ELSE",
            'timestart' => 1,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        mod_qcreate_core_calendar_event_timestart_updated($event, $qcreate);

        $qcreate = $DB->get_record('qcreate', ['id' => $qcreate->id]);
        $this->assertEquals($timeopen, $qcreate->timeopen);
        $this->assertEquals($timeclose, $qcreate->timeclose);
    }

    /**
     * A QREATE_EVENT_TYPE_OPEN event should update the timeopen property of
     * the qcreate activity.
     */
    public function test_mod_qcreate_core_calendar_event_timestart_updated_open_event() {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $qcreategenerator = $generator->get_plugin_generator('mod_qcreate');
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $timemodified = 1;
        $newtimeopen = $timeopen - DAYSECS;
        $qcreate = $qcreategenerator->create_instance(['course' => $course->id]);
        $qcreate->timeopen = $timeopen;
        $qcreate->timeclose = $timeclose;
        $qcreate->timemodified = $timemodified;
        $DB->update_record('qcreate', $qcreate);

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'qcreate',
            'instance' => $qcreate->id,
            'eventtype' => QCREATE_EVENT_TYPE_OPEN,
            'timestart' => $newtimeopen,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();

        mod_qcreate_core_calendar_event_timestart_updated($event, $qcreate);

        $triggeredevents = $sink->get_events();
        $moduleupdatedevents = array_filter($triggeredevents, function($e) {
            return is_a($e, 'core\event\course_module_updated');
        });

        $qcreate = $DB->get_record('qcreate', ['id' => $qcreate->id]);
        // Ensure the timeopen property matches the event timestart.
        $this->assertEquals($newtimeopen, $qcreate->timeopen);
        // Ensure the timeclose isn't changed.
        $this->assertEquals($timeclose, $qcreate->timeclose);
        // Ensure the timemodified property has been changed.
        $this->assertNotEquals($timemodified, $qcreate->timemodified);
        // Confirm that a module updated event is fired when the module
        // is changed.
        $this->assertNotEmpty($moduleupdatedevents);
    }

    /**
     * A QCREATE_EVENT_TYPE_CLOSE event should update the timeclose property of
     * the qcreate activity.
     */
    public function test_mod_qcreate_core_calendar_event_timestart_updated_close_event() {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $qcreategenerator = $generator->get_plugin_generator('mod_qcreate');
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $timemodified = 1;
        $newtimeclose = $timeclose + DAYSECS;
        $qcreate = $qcreategenerator->create_instance(['course' => $course->id]);
        $qcreate->timeopen = $timeopen;
        $qcreate->timeclose = $timeclose;
        $qcreate->timemodified = $timemodified;
        $DB->update_record('qcreate', $qcreate);

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'qcreate',
            'instance' => $qcreate->id,
            'eventtype' => QCREATE_EVENT_TYPE_CLOSE,
            'timestart' => $newtimeclose,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        // Trigger and capture the event when adding a contact.
        $sink = $this->redirectEvents();

        mod_qcreate_core_calendar_event_timestart_updated($event, $qcreate);

        $triggeredevents = $sink->get_events();
        $moduleupdatedevents = array_filter($triggeredevents, function($e) {
            return is_a($e, 'core\event\course_module_updated');
        });

        $qcreate = $DB->get_record('qcreate', ['id' => $qcreate->id]);
        // Ensure the timeclose property matches the event timestart.
        $this->assertEquals($newtimeclose, $qcreate->timeclose);
        // Ensure the timeopen isn't changed.
        $this->assertEquals($timeopen, $qcreate->timeopen);
        // Ensure the timemodified property has been changed.
        $this->assertNotEquals($timemodified, $qcreate->timemodified);
        // Confirm that a module updated event is fired when the module
        // is changed.
        $this->assertNotEmpty($moduleupdatedevents);
    }

    /**
     * An unkown event type should not have any limits
     */
    public function test_mod_qcreate_core_calendar_get_valid_event_timestart_range_unknown_event() {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $qcreate = new \stdClass();
        $qcreate->timeopen = $timeopen;
        $qcreate->timeclose = $timeclose;

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'qcreate',
            'instance' => 1,
            'eventtype' => QCREATE_EVENT_TYPE_OPEN . "SOMETHING ELSE",
            'timestart' => 1,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        list ($min, $max) = mod_qcreate_core_calendar_get_valid_event_timestart_range($event, $qcreate);
        $this->assertNull($min);
        $this->assertNull($max);
    }

    /**
     * The open event should be limited by the qcreate's timeclose property, if it's set.
     */
    public function test_mod_qcreate_core_calendar_get_valid_event_timestart_range_open_event() {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $qcreate = new \stdClass();
        $qcreate->timeopen = $timeopen;
        $qcreate->timeclose = $timeclose;

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'qcreate',
            'instance' => 1,
            'eventtype' => QCREATE_EVENT_TYPE_OPEN,
            'timestart' => 1,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        // The max limit should be bounded by the timeclose value.
        list ($min, $max) = mod_qcreate_core_calendar_get_valid_event_timestart_range($event, $qcreate);

        $this->assertNull($min);
        $this->assertEquals($timeclose, $max[0]);

        // No timeclose value should result in no upper limit.
        $qcreate->timeclose = 0;
        list ($min, $max) = mod_qcreate_core_calendar_get_valid_event_timestart_range($event, $qcreate);

        $this->assertNull($min);
        $this->assertNull($max);
    }

    /**
     * The close event should be limited by the qcreate's timeopen property, if it's set.
     */
    public function test_mod_qcreate_core_calendar_get_valid_event_timestart_range_close_event() {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $qcreate = new \stdClass();
        $qcreate->timeopen = $timeopen;
        $qcreate->timeclose = $timeclose;

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'qcreate',
            'instance' => 1,
            'eventtype' => CHOICE_EVENT_TYPE_CLOSE,
            'timestart' => 1,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        // The max limit should be bounded by the timeclose value.
        list ($min, $max) = mod_qcreate_core_calendar_get_valid_event_timestart_range($event, $qcreate);

        $this->assertEquals($timeopen, $min[0]);
        $this->assertNull($max);

        // No timeclose value should result in no upper limit.
        $qcreate->timeopen = 0;
        list ($min, $max) = mod_qcreate_core_calendar_get_valid_event_timestart_range($event, $qcreate);

        $this->assertNull($min);
        $this->assertNull($max);
    }

    /**
     * Test check_updates_since callback.
     */
    public function test_check_updates_since() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();

        $this->setCurrentTimeStart();
        $record = array(
            'course' => $course->id,
            'custom' => 0,
            'feedback' => 1,
        );
        $this->setUser($this->students[0]);
        $qcreate = $this->create_instance();
        $cm    = $qcreate->get_course_module();
        $cm = cm_info::create($cm);

        // Check that upon creation, the updates are only about the new configuration created.
        $onehourago = time() - HOURSECS;
        $updates = qcreate_check_updates_since($cm, $onehourago);
        foreach ($updates as $el => $val) {
            if ($el == 'configuration') {
                $this->assertTrue($val->updated);
                $this->assertTimeCurrent($val->timeupdated);
            } else {
                $this->assertFalse($val->updated);
            }
        }

        // Create a question as student0.
        $this->setUser($this->students[0]);
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $q1 = $questiongenerator->create_question('shortanswer', null,
                array('category' => $qcreate->get_question_category()->id));

        // Create another question as student0.
        $q2 = $questiongenerator->create_question('shortanswer', null,
                array('category' => $qcreate->get_question_category()->id));

        $submittedgrade = 80;
        $submitcomment = 'Good job.';
        // Create a local grade without notification.
        $instance = $qcreate->get_instance();
        // The qcreate_process_local_grade needs cmidnumber set.
        $instance->cmidnumber = $cm->id;

        // Grade first question.
        qcreate_process_local_grade($instance, $q1, false, false, $submittedgrade, $submitcomment);
        // Check now for updates.
        $updates = qcreate_check_updates_since($cm, $onehourago);
        $this->assertTrue($updates->questions->updated);
        $this->assertCount(2, $updates->questions->itemids);
        $this->assertEquals([$q1->id, $q2->id], $updates->questions->itemids, '', 0, 10, true);
        $this->assertTrue($updates->grades->updated);
        $this->assertCount(1, $updates->grades->itemids);

        // Other student should see no update.
        $this->setUser($this->students[1]);
        $updates = qcreate_check_updates_since($cm, $onehourago);
        $this->assertFalse($updates->questions->updated);
        $this->assertFalse($updates->grades->updated);

    }
}
