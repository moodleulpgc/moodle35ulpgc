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
 * Privacy provider tests.
 *
 * @package    mod_qcreate
 * @copyright  2018 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_privacy\local\metadata\collection;
use core_privacy\tests\provider_testcase;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;
use mod_qcreate\privacy\provider;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Data provider testcase class.
 *
 * @package    mod_qcreate
 * @copyright  2018 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_qcreate_privacy_testcase extends provider_testcase {
    /** @var stdClass The student object. */
    protected $student;

    /** @var stdClass The teacher object. */
    protected $teacher;

    /** @var stdClass The qcreate object. */
    protected $qcreate;

    /** @var stdClass The course object. */
    protected $course;

    /** @var stdClass The question object. */
    protected $question;

    protected function setUp() {
        $this->resetAfterTest();
        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a student and a teacher.
        $user = $this->getDataGenerator()->create_user();
        $teacher = $this->getDataGenerator()->create_user();;
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        // Make a qcreate activity.
        $this->setUser($teacher);
        $qcreate = $this->create_test_qcreate($course);

        // The qcreate_process_local_grade needs cmidnumber set.
        list($course, $cm) = get_course_and_cm_from_cmid($qcreate->cmid, 'qcreate');
        $qcreate->cmidnumber = $cm->id;

        // Create a question.
        $q1 = $this->qcreate_add_qcreate_question($user, $qcreate, 'shortanswer');

        // Grade question.
        $this->setUser($teacher);
        qcreate_process_local_grade($qcreate, $q1, false, false, 90, 'Good job.');

        $this->student = $user;
        $this->teacher = $teacher;
        $this->qcreate = $qcreate;
        $this->course = $course;
        $this->question = $q1;
    }

    /**
     * Test for provider::get_metadata().
     */
    public function test_get_metadata() {
        $collection = new collection('mod_qcreate');
        $newcollection = provider::get_metadata($collection);
        $itemcollection = $newcollection->get_collection();

        $this->assertCount(2, $itemcollection);

        $table = reset($itemcollection);
        $this->assertEquals('qcreate_grades', $table->get_name());

        $privacyfields = $table->get_privacy_fields();
        $this->assertArrayHasKey('qcreateid', $privacyfields);
        $this->assertArrayHasKey('questionid', $privacyfields);
        $this->assertArrayHasKey('grade', $privacyfields);
        $this->assertArrayHasKey('gradecomment', $privacyfields);
        $this->assertArrayHasKey('teacher', $privacyfields);
        $this->assertArrayHasKey('timemarked', $privacyfields);

        $this->assertEquals('privacy:metadata:qcreate_grades', $table->get_summary());
    }
    /**
     * Test that a user who has no data gets no contexts
     */
    public function test_get_contexts_for_userid_no_data() {
        global $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $contextlist = provider::get_contexts_for_userid($USER->id);
        $this->assertEmpty($contextlist);
    }

    /**
     * The export function should handle an empty contextlist properly.
     */
    public function test_export_user_data_no_data() {
        global $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $approvedcontextlist = new \core_privacy\tests\request\approved_contextlist(
            \core_user::get_user($USER->id),
            'mod_qcreate',
            []
        );

        provider::export_user_data($approvedcontextlist);
        $this->assertDebuggingNotCalled();

        // No data should have been exported.
        $writer = \core_privacy\local\request\writer::with_context(\context_system::instance());
        $this->assertFalse($writer->has_any_data_in_any_context());
    }

    /**
     * The delete function should handle an empty contextlist properly.
     */
    public function test_delete_data_for_user_no_data() {
        global $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $approvedcontextlist = new \core_privacy\tests\request\approved_contextlist(
            \core_user::get_user($USER->id),
            'mod_qcreate',
            []
        );

        provider::delete_data_for_user($approvedcontextlist);
        $this->assertDebuggingNotCalled();
    }

    /**
     * Export + Delete qcreate data for a user who has made a single question.
     */
    public function test_user_with_data() {
        global $DB;
        $cm = get_coursemodule_from_instance('qcreate', $this->qcreate->id);
        $context = context_module::instance($cm->id);

        // Fetch the contexts - only one context should be returned.
        $contextlist = provider::get_contexts_for_userid($this->student->id);
        $this->assertCount(1, $contextlist);
        $this->assertEquals($context, $contextlist->current());
        // Verify that there is one local grade.
        $count = $DB->count_records('qcreate_grades', ['qcreateid' => $this->qcreate->id]);
        $this->assertEquals(1, $count);

        // Perform the export and check the data.
        $approvedcontextlist = new \core_privacy\tests\request\approved_contextlist(
            \core_user::get_user($this->student->id),
            'mod_qcreate',
            $contextlist->get_contextids()
        );
        provider::export_user_data($approvedcontextlist);

        // Ensure that the qcreate data was exported correctly.
        $writer = writer::with_context($context);
        $this->assertTrue($writer->has_any_data());

        $qcreatedata = $writer->get_data([]);
        $this->assertEquals($this->qcreate->name, $qcreatedata->name);

        // Every module has an intro.
        $this->assertTrue(isset($qcreatedata->intro));

        // Delete the data and check it is removed.
        $this->setUser();
        provider::delete_data_for_user($approvedcontextlist);
        // Verify that there is no local grade.
        $count = $DB->count_records('qcreate_grades', ['qcreateid' => $this->qcreate->id]);
        $this->assertEquals(0, $count);
    }

    /**
     * Export + Delete qcreate data for a user who has made a single attempt.
     */
    public function test_delete_data_for_all_users_in_context() {
        global $DB;
        $cm = get_coursemodule_from_instance('qcreate', $this->qcreate->id);
        $context = context_module::instance($cm->id);

        $otheruser = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($otheruser->id, $this->course->id, 'student');

        $q1 = $this->question;
        // Create another question.
        $q2 = $this->qcreate_add_qcreate_question($otheruser, $this->qcreate, 'shortanswer');

        // Grade question.
        $this->setUser($this->teacher);
        qcreate_process_local_grade($this->qcreate, $q2, false, false, 70, 'Not too bad.');

        // Create another qcreate.
        $this->setUser();
        $otherqcreate = $this->create_test_qcreate($this->course);

        // The qcreate_process_local_grade needs cmidnumber set.
        list($course, $cm) = get_course_and_cm_from_cmid($otherqcreate->cmid, 'qcreate');
        $otherqcreate->cmidnumber = $cm->id;

        // Create questions.
        $q3 = $this->qcreate_add_qcreate_question($this->student, $otherqcreate, 'shortanswer');
        $q4 = $this->qcreate_add_qcreate_question($otheruser, $otherqcreate, 'shortanswer');

        // Grade the questions.
        $this->setUser($this->teacher);
        qcreate_process_local_grade($otherqcreate, $q3, false, false, 50, 'You can do better.');
        qcreate_process_local_grade($otherqcreate, $q4, false, false, 40, 'Poor job.');

        $count = $DB->count_records('qcreate_grades', ['qcreateid' => $this->qcreate->id]);
        $this->assertEquals(2, $count);

        // Delete all data for all users in the context under test.
        $this->setUser();
        provider::delete_data_for_all_users_in_context($context);
        // Verify that there is only grades for second qcreate.
        $count = $DB->count_records('qcreate_grades', ['qcreateid' => $this->qcreate->id]);
        $this->assertEquals(0, $count);
        $count = $DB->count_records('qcreate_grades', ['qcreateid' => $otherqcreate->id]);
        $this->assertEquals(2, $count);

        // Delete data only for first student.
        $contextlist = provider::get_contexts_for_userid($this->student->id);
        $approvedcontextlist = new \core_privacy\tests\request\approved_contextlist(
            \core_user::get_user($this->student->id),
            'mod_qcreate',
            $contextlist->get_contextids()
        );
        $this->setUser();
        provider::delete_data_for_user($approvedcontextlist);
        // Verify that there is 1 local grade.
        $count = $DB->count_records('qcreate_grades', ['qcreateid' => $otherqcreate->id]);
        $this->assertEquals(1, $count);

    }

    /**
     * Export + Delete qcreate data for a teacher who has both made and graded question.
     */
    public function test_teacher_with_data() {
        global $DB;
        $cm = get_coursemodule_from_instance('qcreate', $this->qcreate->id);
        $context = context_module::instance($cm->id);

        // Create another question.
        $q2 = $this->qcreate_add_qcreate_question($this->teacher, $this->qcreate, 'shortanswer');

        $otherteacher = $this->getDataGenerator()->create_user();;
        $this->getDataGenerator()->enrol_user($otherteacher->id, $this->course->id, 'editingteacher');
        // Grade question.
        $this->setUser($otherteacher);
        qcreate_process_local_grade($this->qcreate, $q2, false, false, 95, 'Very good.');

        // Fetch the contexts - only one context should be returned.
        $contextlist = provider::get_contexts_for_userid($this->teacher->id);
        $this->assertCount(1, $contextlist);
        $this->assertEquals($context, $contextlist->current());

        // Verify that there is 2 local grades.
        $count = $DB->count_records('qcreate_grades', ['qcreateid' => $this->qcreate->id]);
        $this->assertEquals(2, $count);

        // Perform the export and check the data.
        $approvedcontextlist = new \core_privacy\tests\request\approved_contextlist(
            \core_user::get_user($this->teacher->id),
            'mod_qcreate',
            $contextlist->get_contextids()
        );
        $grade = $DB->get_record('qcreate_grades', array('questionid' => $this->question->id));

        // Delete the data and check it is removed.
        $this->setUser();
        provider::delete_data_for_user($approvedcontextlist);

        // Verify that teacher's grade was deleted.
        $count = $DB->count_records('qcreate_grades', ['qcreateid' => $this->qcreate->id]);
        $this->assertEquals(1, $count);

        // Verify that question graded has been anonymized.
        $grade = $DB->get_record('qcreate_grades', array('questionid' => $this->question->id));
        $this->assertEquals($grade->teacher, 0);
        $this->assertEquals($grade->gradecomment, '');
    }

    /**
     * Create a test qcreate for the specified course.
     *
     * @param   \stdClass $course
     * @return  array
     */
    protected function create_test_qcreate($course) {
        global $DB;

        $qcreategenerator = $this->getDataGenerator()->get_plugin_generator('mod_qcreate');

        $qcreate = $qcreategenerator->create_instance(['course' => $course->id]);
        return $qcreate;
    }

    protected function qcreate_add_qcreate_question($user, $qcreate, $qtype) {
        $this->setUser($user);
        $cm = get_coursemodule_from_instance('qcreate', $qcreate->id);
        $context = context_module::instance($cm->id);
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = question_get_default_category($context->id);
        $q = $questiongenerator->create_question($qtype, null,
                array('category' => $cat->id));
        return $q;
    }
}
