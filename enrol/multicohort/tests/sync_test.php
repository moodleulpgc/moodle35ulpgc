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
 * multicohort enrolment sync functional test.
 *
 * @package    enrol_multicohort
 * @category   phpunit
 * @copyright  2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/enrol/multicohort/locallib.php');
require_once($CFG->dirroot.'/multicohort/lib.php');
require_once($CFG->dirroot.'/group/lib.php');

class enrol_multicohort_testcase extends advanced_testcase {

    protected function enable_plugin() {
        $enabled = enrol_get_plugins(true);
        $enabled['multicohort'] = true;
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    protected function disable_plugin() {
        $enabled = enrol_get_plugins(true);
        unset($enabled['multicohort']);
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    public function test_handler_sync() {
        global $DB;

        $this->resetAfterTest();
        $trace = new null_progress_trace();

        // Setup a few courses and categories.

        $multicohortplugin = enrol_get_plugin('multicohort');
        $manualplugin = enrol_get_plugin('manual');

        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        $this->assertNotEmpty($studentrole);
        $teacherrole = $DB->get_record('role', array('shortname'=>'teacher'));
        $this->assertNotEmpty($teacherrole);
        $managerrole = $DB->get_record('role', array('shortname'=>'manager'));
        $this->assertNotEmpty($managerrole);

        $cat1 = $this->getDataGenerator()->create_category();
        $cat2 = $this->getDataGenerator()->create_category();

        $course1 = $this->getDataGenerator()->create_course(array('category'=>$cat1->id));
        $course2 = $this->getDataGenerator()->create_course(array('category'=>$cat1->id));
        $course3 = $this->getDataGenerator()->create_course(array('category'=>$cat2->id));
        $course4 = $this->getDataGenerator()->create_course(array('category'=>$cat2->id));
        $maninstance1 = $DB->get_record('enrol', array('courseid'=>$course1->id, 'enrol'=>'manual'), '*', MUST_EXIST);

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();

        $multicohort1 = $this->getDataGenerator()->create_multicohort(array('contextid'=>context_coursecat::instance($cat1->id)->id));
        $multicohort2 = $this->getDataGenerator()->create_multicohort(array('contextid'=>context_coursecat::instance($cat2->id)->id));
        $multicohort3 = $this->getDataGenerator()->create_multicohort();

        $this->enable_plugin();

        $manualplugin->enrol_user($maninstance1, $user4->id, $teacherrole->id);
        $manualplugin->enrol_user($maninstance1, $user3->id, $managerrole->id);

        $this->assertEquals(2, $DB->count_records('role_assignments', array()));
        $this->assertEquals(2, $DB->count_records('user_enrolments', array()));

        $id = $multicohortplugin->add_instance($course1, array('customint1'=>$multicohort1->id, 'roleid'=>$studentrole->id));
        $multicohortinstance1 = $DB->get_record('enrol', array('id'=>$id));

        $id = $multicohortplugin->add_instance($course1, array('customint1'=>$multicohort2->id, 'roleid'=>$teacherrole->id));
        $multicohortinstance2 = $DB->get_record('enrol', array('id'=>$id));

        $id = $multicohortplugin->add_instance($course2, array('customint1'=>$multicohort2->id, 'roleid'=>$studentrole->id));
        $multicohortinstance3 = $DB->get_record('enrol', array('id'=>$id));


        // Test multicohort member add event.

        multicohort_add_member($multicohort1->id, $user1->id);
        multicohort_add_member($multicohort1->id, $user2->id);
        multicohort_add_member($multicohort1->id, $user4->id);
        $this->assertEquals(5, $DB->count_records('user_enrolments', array()));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance1->id, 'userid'=>$user1->id)));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance1->id, 'userid'=>$user2->id)));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance1->id, 'userid'=>$user4->id)));
        $this->assertEquals(5, $DB->count_records('role_assignments', array()));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user1->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user2->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user4->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        multicohort_add_member($multicohort2->id, $user3->id);
        $this->assertEquals(7, $DB->count_records('user_enrolments', array()));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance2->id, 'userid'=>$user3->id)));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance3->id, 'userid'=>$user3->id)));
        $this->assertEquals(7, $DB->count_records('role_assignments', array()));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user3->id, 'roleid'=>$teacherrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance2->id)));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course2->id)->id, 'userid'=>$user3->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance3->id)));

        multicohort_add_member($multicohort3->id, $user3->id);
        $this->assertEquals(7, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(7, $DB->count_records('role_assignments', array()));

        // Test multicohort remove action.

        $this->assertEquals(ENROL_EXT_REMOVED_UNENROL, $multicohortplugin->get_config('unenrolaction'));
        $multicohortplugin->set_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);

        multicohort_remove_member($multicohort1->id, $user2->id);
        multicohort_remove_member($multicohort1->id, $user4->id);
        $this->assertEquals(7, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(5, $DB->count_records('role_assignments', array()));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user2->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user4->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        multicohort_add_member($multicohort1->id, $user2->id);
        multicohort_add_member($multicohort1->id, $user4->id);
        $this->assertEquals(7, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(7, $DB->count_records('role_assignments', array()));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user2->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user4->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        $multicohortplugin->set_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);
        multicohort_remove_member($multicohort1->id, $user2->id);
        multicohort_remove_member($multicohort1->id, $user4->id);
        $this->assertEquals(5, $DB->count_records('user_enrolments', array()));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance1->id, 'userid'=>$user2->id)));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance1->id, 'userid'=>$user4->id)));
        $this->assertEquals(5, $DB->count_records('role_assignments', array()));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user2->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user4->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        multicohort_remove_member($multicohort2->id, $user3->id);
        $this->assertEquals(3, $DB->count_records('user_enrolments', array()));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance2->id, 'userid'=>$user3->id)));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance3->id, 'userid'=>$user3->id)));
        $this->assertEquals(3, $DB->count_records('role_assignments', array()));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user3->id, 'roleid'=>$teacherrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance2->id)));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course2->id)->id, 'userid'=>$user3->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance3->id)));


        // Test multicohort deleting.

        multicohort_add_member($multicohort1->id, $user2->id);
        multicohort_add_member($multicohort1->id, $user4->id);
        multicohort_add_member($multicohort2->id, $user3->id);
        $this->assertEquals(7, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(7, $DB->count_records('role_assignments', array()));

        $multicohortplugin->set_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);
        multicohort_delete_multicohort($multicohort2);
        $this->assertEquals(7, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(5, $DB->count_records('role_assignments', array()));

        $multicohortinstance2 = $DB->get_record('enrol', array('id'=>$multicohortinstance2->id), '*', MUST_EXIST);
        $multicohortinstance3 = $DB->get_record('enrol', array('id'=>$multicohortinstance3->id), '*', MUST_EXIST);

        $this->assertEquals(ENROL_INSTANCE_DISABLED, $multicohortinstance2->status);
        $this->assertEquals(ENROL_INSTANCE_DISABLED, $multicohortinstance3->status);
        $this->assertFalse($DB->record_exists('role_assignments', array('component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance2->id)));
        $this->assertFalse($DB->record_exists('role_assignments', array('component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance3->id)));

        $multicohortplugin->set_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);
        multicohort_delete_multicohort($multicohort1);
        $this->assertEquals(4, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(2, $DB->count_records('role_assignments', array()));
        $this->assertFalse($DB->record_exists('enrol', array('id'=>$multicohortinstance1->id)));
        $this->assertFalse($DB->record_exists('role_assignments', array('component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        // Cleanup after previous test (remove the extra user_enrolment).
        enrol_multicohort_sync($trace, $course1->id);
        $this->assertEquals(3, $DB->count_records('user_enrolments', array()));

        // Test group sync.

        $id = groups_create_group((object)array('name'=>'Group 1', 'courseid'=>$course1->id));
        $group1 = $DB->get_record('groups', array('id'=>$id), '*', MUST_EXIST);
        $id = groups_create_group((object)array('name'=>'Group 2', 'courseid'=>$course1->id));
        $group2 = $DB->get_record('groups', array('id'=>$id), '*', MUST_EXIST);

        $multicohort1 = $this->getDataGenerator()->create_multicohort(array('contextid'=>context_coursecat::instance($cat1->id)->id));
        $id = $multicohortplugin->add_instance($course1, array('customint1'=>$multicohort1->id, 'roleid'=>$studentrole->id, 'customint2'=>$group1->id));
        $multicohortinstance1 = $DB->get_record('enrol', array('id'=>$id));

        $this->assertEquals(3, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(2, $DB->count_records('role_assignments', array()));

        $this->assertTrue(is_enrolled(context_course::instance($course1->id), $user4));
        $this->assertTrue(groups_add_member($group1, $user4));
        $this->assertTrue(groups_add_member($group2, $user4));

        $this->assertFalse(groups_is_member($group1->id, $user1->id));
        multicohort_add_member($multicohort1->id, $user1->id);
        $this->assertTrue(groups_is_member($group1->id, $user1->id));
        $this->assertTrue($DB->record_exists('groups_members', array('groupid'=>$group1->id, 'userid'=>$user1->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        multicohort_add_member($multicohort1->id, $user4->id);
        $this->assertTrue(groups_is_member($group1->id, $user4->id));
        $this->assertFalse($DB->record_exists('groups_members', array('groupid'=>$group1->id, 'userid'=>$user4->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        $multicohortplugin->set_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);

        multicohort_remove_member($multicohort1->id, $user1->id);
        $this->assertFalse(groups_is_member($group1->id, $user1->id));

        multicohort_remove_member($multicohort1->id, $user4->id);
        $this->assertTrue(groups_is_member($group1->id, $user4->id));
        $this->assertTrue(groups_is_member($group2->id, $user4->id));

        $multicohortplugin->set_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);
        multicohort_add_member($multicohort1->id, $user1->id);

        multicohort_remove_member($multicohort1->id, $user1->id);
        $this->assertTrue(groups_is_member($group1->id, $user1->id));


        // Test deleting of instances.

        multicohort_add_member($multicohort1->id, $user1->id);
        multicohort_add_member($multicohort1->id, $user2->id);
        multicohort_add_member($multicohort1->id, $user3->id);

        $this->assertEquals(6, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(5, $DB->count_records('role_assignments', array()));
        $this->assertEquals(3, $DB->count_records('role_assignments', array('component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
        $this->assertEquals(5, $DB->count_records('groups_members', array()));
        $this->assertEquals(3, $DB->count_records('groups_members', array('component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        $multicohortplugin->delete_instance($multicohortinstance1);

        $this->assertEquals(3, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(2, $DB->count_records('role_assignments', array()));
        $this->assertEquals(0, $DB->count_records('role_assignments', array('component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
        $this->assertEquals(2, $DB->count_records('groups_members', array()));
        $this->assertEquals(0, $DB->count_records('groups_members', array('component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
    }

    public function test_sync_course() {
        global $DB;
        $this->resetAfterTest();

        $trace = new null_progress_trace();

        // Setup a few courses and categories.

        $multicohortplugin = enrol_get_plugin('multicohort');
        $manualplugin = enrol_get_plugin('manual');

        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        $this->assertNotEmpty($studentrole);
        $teacherrole = $DB->get_record('role', array('shortname'=>'teacher'));
        $this->assertNotEmpty($teacherrole);
        $managerrole = $DB->get_record('role', array('shortname'=>'manager'));
        $this->assertNotEmpty($managerrole);

        $cat1 = $this->getDataGenerator()->create_category();
        $cat2 = $this->getDataGenerator()->create_category();

        $course1 = $this->getDataGenerator()->create_course(array('category'=>$cat1->id));
        $course2 = $this->getDataGenerator()->create_course(array('category'=>$cat1->id));
        $course3 = $this->getDataGenerator()->create_course(array('category'=>$cat2->id));
        $course4 = $this->getDataGenerator()->create_course(array('category'=>$cat2->id));
        $maninstance1 = $DB->get_record('enrol', array('courseid'=>$course1->id, 'enrol'=>'manual'), '*', MUST_EXIST);

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();

        $multicohort1 = $this->getDataGenerator()->create_multicohort(array('contextid'=>context_coursecat::instance($cat1->id)->id));
        $multicohort2 = $this->getDataGenerator()->create_multicohort(array('contextid'=>context_coursecat::instance($cat2->id)->id));
        $multicohort3 = $this->getDataGenerator()->create_multicohort();

        $this->disable_plugin(); // Prevents event sync.

        $manualplugin->enrol_user($maninstance1, $user4->id, $teacherrole->id);
        $manualplugin->enrol_user($maninstance1, $user3->id, $managerrole->id);

        $this->assertEquals(2, $DB->count_records('role_assignments', array()));
        $this->assertEquals(2, $DB->count_records('user_enrolments', array()));

        $id = $multicohortplugin->add_instance($course1, array('customint1'=>$multicohort1->id, 'roleid'=>$studentrole->id));
        $multicohortinstance1 = $DB->get_record('enrol', array('id'=>$id));

        $id = $multicohortplugin->add_instance($course1, array('customint1'=>$multicohort2->id, 'roleid'=>$teacherrole->id));
        $multicohortinstance2 = $DB->get_record('enrol', array('id'=>$id));

        $id = $multicohortplugin->add_instance($course2, array('customint1'=>$multicohort2->id, 'roleid'=>$studentrole->id));
        $multicohortinstance3 = $DB->get_record('enrol', array('id'=>$id));

        multicohort_add_member($multicohort1->id, $user1->id);
        multicohort_add_member($multicohort1->id, $user2->id);
        multicohort_add_member($multicohort1->id, $user4->id);
        multicohort_add_member($multicohort2->id, $user3->id);
        multicohort_add_member($multicohort3->id, $user3->id);

        $this->assertEquals(2, $DB->count_records('role_assignments', array()));
        $this->assertEquals(2, $DB->count_records('user_enrolments', array()));


        // Test sync of one course only.

        enrol_multicohort_sync($trace, $course1->id);
        $this->assertEquals(2, $DB->count_records('role_assignments', array()));
        $this->assertEquals(2, $DB->count_records('user_enrolments', array()));


        $this->enable_plugin();
        enrol_multicohort_sync($trace, $course2->id);
        $this->assertEquals(3, $DB->count_records('role_assignments', array()));
        $this->assertEquals(3, $DB->count_records('user_enrolments', array()));
        $DB->delete_records('multicohort_members', array('multicohortid'=>$multicohort3->id)); // Use low level DB api to prevent events!
        $DB->delete_records('multicohort', array('id'=>$multicohort3->id)); // Use low level DB api to prevent events!

        enrol_multicohort_sync($trace, $course1->id);
        $this->assertEquals(7, $DB->count_records('user_enrolments', array()));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance1->id, 'userid'=>$user1->id)));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance1->id, 'userid'=>$user2->id)));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance1->id, 'userid'=>$user4->id)));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance2->id, 'userid'=>$user3->id)));
        $this->assertEquals(7, $DB->count_records('role_assignments', array()));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user1->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user2->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user4->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user3->id, 'roleid'=>$teacherrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance2->id)));

        $multicohortplugin->set_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);
        $DB->delete_records('multicohort_members', array('multicohortid'=>$multicohort2->id, 'userid'=>$user3->id)); // Use low level DB api to prevent events!
        enrol_multicohort_sync($trace, $course1->id);
        $this->assertEquals(7, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(6, $DB->count_records('role_assignments', array()));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user3->id, 'roleid'=>$teacherrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance2->id)));

        $multicohortplugin->set_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);
        $DB->delete_records('multicohort_members', array('multicohortid'=>$multicohort1->id, 'userid'=>$user1->id)); // Use low level DB api to prevent events!
        enrol_multicohort_sync($trace, $course1->id);
        $this->assertEquals(5, $DB->count_records('user_enrolments', array()));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance2->id, 'userid'=>$user3->id)));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance1->id, 'userid'=>$user1->id)));
        $this->assertEquals(5, $DB->count_records('role_assignments', array()));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user3->id, 'roleid'=>$teacherrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance2->id)));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user1->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        $multicohortplugin->set_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);
        $DB->delete_records('multicohort_members', array('multicohortid'=>$multicohort1->id)); // Use low level DB api to prevent events!
        $DB->delete_records('multicohort', array('id'=>$multicohort1->id)); // Use low level DB api to prevent events!
        enrol_multicohort_sync($trace, $course1->id);
        $this->assertEquals(5, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(3, $DB->count_records('role_assignments', array()));

        $multicohortplugin->set_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);
        enrol_multicohort_sync($trace, $course1->id);
        $this->assertEquals(3, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(3, $DB->count_records('role_assignments', array()));


        // Test group sync.

        $this->disable_plugin(); // No event sync.

        // Trigger sync to remove left over role assignments.
        enrol_multicohort_sync($trace, $course1->id);
        $this->assertEquals(2, $DB->count_records('role_assignments', array()));

        $id = groups_create_group((object)array('name'=>'Group 1', 'courseid'=>$course1->id));
        $group1 = $DB->get_record('groups', array('id'=>$id), '*', MUST_EXIST);
        $id = groups_create_group((object)array('name'=>'Group 2', 'courseid'=>$course1->id));
        $group2 = $DB->get_record('groups', array('id'=>$id), '*', MUST_EXIST);

        $multicohort1 = $this->getDataGenerator()->create_multicohort(array('contextid'=>context_coursecat::instance($cat1->id)->id));
        $id = $multicohortplugin->add_instance($course1, array('customint1'=>$multicohort1->id, 'roleid'=>$studentrole->id, 'customint2'=>$group1->id));
        $multicohortinstance1 = $DB->get_record('enrol', array('id'=>$id));

        $this->assertTrue(is_enrolled(context_course::instance($course1->id), $user4));
        $this->assertTrue(groups_add_member($group1, $user4));
        $this->assertTrue(groups_add_member($group2, $user4));

        $this->enable_plugin(); // No event sync.

        $this->assertEquals(3, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(2, $DB->count_records('role_assignments', array()));

        $this->assertFalse(groups_is_member($group1->id, $user1->id));
        multicohort_add_member($multicohort1->id, $user1->id);
        multicohort_add_member($multicohort1->id, $user4->id);
        multicohort_add_member($multicohort2->id, $user4->id);

        enrol_multicohort_sync($trace, $course1->id);

        $this->assertEquals(7, $DB->count_records('user_enrolments', array()));
        // This used to be 7 - but now add_instance triggers an immediate sync.
        $this->assertEquals(6, $DB->count_records('role_assignments', array()));

        $this->assertTrue(groups_is_member($group1->id, $user1->id));
        $this->assertTrue($DB->record_exists('groups_members', array('groupid'=>$group1->id, 'userid'=>$user1->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        $this->assertTrue(groups_is_member($group1->id, $user4->id));
        $this->assertFalse($DB->record_exists('groups_members', array('groupid'=>$group1->id, 'userid'=>$user4->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        $multicohortinstance1->customint2 = $group2->id;
        $DB->update_record('enrol', $multicohortinstance1);

        enrol_multicohort_sync($trace, $course1->id);
        $this->assertFalse(groups_is_member($group1->id, $user1->id));
        $this->assertTrue(groups_is_member($group2->id, $user1->id));
        $this->assertTrue($DB->record_exists('groups_members', array('groupid'=>$group2->id, 'userid'=>$user1->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        $this->assertTrue(groups_is_member($group1->id, $user4->id));
        $this->assertTrue(groups_is_member($group2->id, $user4->id));
        $this->assertFalse($DB->record_exists('groups_members', array('groupid'=>$group1->id, 'userid'=>$user4->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
        $this->assertFalse($DB->record_exists('groups_members', array('groupid'=>$group2->id, 'userid'=>$user4->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        multicohort_remove_member($multicohort1->id, $user1->id);
        $this->assertFalse(groups_is_member($group1->id, $user1->id));

        multicohort_remove_member($multicohort1->id, $user4->id);
        $this->assertTrue(groups_is_member($group1->id, $user4->id));
        $this->assertTrue(groups_is_member($group2->id, $user4->id));
    }

    public function test_sync_all_courses() {
        global $DB;

        $this->resetAfterTest();

        $trace = new null_progress_trace();

        // Setup a few courses and categories.

        $multicohortplugin = enrol_get_plugin('multicohort');
        $manualplugin = enrol_get_plugin('manual');

        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        $this->assertNotEmpty($studentrole);
        $teacherrole = $DB->get_record('role', array('shortname'=>'teacher'));
        $this->assertNotEmpty($teacherrole);
        $managerrole = $DB->get_record('role', array('shortname'=>'manager'));
        $this->assertNotEmpty($managerrole);

        $cat1 = $this->getDataGenerator()->create_category();
        $cat2 = $this->getDataGenerator()->create_category();

        $course1 = $this->getDataGenerator()->create_course(array('category'=>$cat1->id));
        $course2 = $this->getDataGenerator()->create_course(array('category'=>$cat1->id));
        $course3 = $this->getDataGenerator()->create_course(array('category'=>$cat2->id));
        $course4 = $this->getDataGenerator()->create_course(array('category'=>$cat2->id));
        $maninstance1 = $DB->get_record('enrol', array('courseid'=>$course1->id, 'enrol'=>'manual'), '*', MUST_EXIST);

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();

        $multicohort1 = $this->getDataGenerator()->create_multicohort(array('contextid'=>context_coursecat::instance($cat1->id)->id));
        $multicohort2 = $this->getDataGenerator()->create_multicohort(array('contextid'=>context_coursecat::instance($cat2->id)->id));
        $multicohort3 = $this->getDataGenerator()->create_multicohort();

        $this->disable_plugin(); // Prevents event sync.

        $manualplugin->enrol_user($maninstance1, $user4->id, $teacherrole->id);
        $manualplugin->enrol_user($maninstance1, $user3->id, $managerrole->id);

        $this->assertEquals(2, $DB->count_records('role_assignments', array()));
        $this->assertEquals(2, $DB->count_records('user_enrolments', array()));

        $id = $multicohortplugin->add_instance($course1, array('customint1'=>$multicohort1->id, 'roleid'=>$studentrole->id));
        $multicohortinstance1 = $DB->get_record('enrol', array('id'=>$id));

        $id = $multicohortplugin->add_instance($course1, array('customint1'=>$multicohort2->id, 'roleid'=>$teacherrole->id));
        $multicohortinstance2 = $DB->get_record('enrol', array('id'=>$id));

        $id = $multicohortplugin->add_instance($course2, array('customint1'=>$multicohort2->id, 'roleid'=>$studentrole->id));
        $multicohortinstance3 = $DB->get_record('enrol', array('id'=>$id));

        multicohort_add_member($multicohort1->id, $user1->id);
        multicohort_add_member($multicohort1->id, $user2->id);
        multicohort_add_member($multicohort1->id, $user4->id);
        multicohort_add_member($multicohort2->id, $user3->id);
        multicohort_add_member($multicohort3->id, $user3->id);

        $this->assertEquals(2, $DB->count_records('role_assignments', array()));
        $this->assertEquals(2, $DB->count_records('user_enrolments', array()));


        // Test sync of one course only.

        enrol_multicohort_sync($trace, null);
        $this->assertEquals(2, $DB->count_records('role_assignments', array()));
        $this->assertEquals(2, $DB->count_records('user_enrolments', array()));


        $this->enable_plugin();
        enrol_multicohort_sync($trace, null);
        $this->assertEquals(7, $DB->count_records('user_enrolments', array()));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance1->id, 'userid'=>$user1->id)));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance1->id, 'userid'=>$user2->id)));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance1->id, 'userid'=>$user4->id)));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance2->id, 'userid'=>$user3->id)));
        $this->assertEquals(7, $DB->count_records('role_assignments', array()));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user1->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user2->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user4->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user3->id, 'roleid'=>$teacherrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance2->id)));

        $multicohortplugin->set_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);
        $DB->delete_records('multicohort_members', array('multicohortid'=>$multicohort2->id, 'userid'=>$user3->id)); // Use low level DB api to prevent events!
        enrol_multicohort_sync($trace, $course1->id);
        $this->assertEquals(7, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(6, $DB->count_records('role_assignments', array()));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user3->id, 'roleid'=>$teacherrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance2->id)));

        $multicohortplugin->set_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);
        $DB->delete_records('multicohort_members', array('multicohortid'=>$multicohort1->id, 'userid'=>$user1->id)); // Use low level DB api to prevent events!
        enrol_multicohort_sync($trace, $course1->id);
        $this->assertEquals(5, $DB->count_records('user_enrolments', array()));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance2->id, 'userid'=>$user3->id)));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$multicohortinstance1->id, 'userid'=>$user1->id)));
        $this->assertEquals(5, $DB->count_records('role_assignments', array()));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user3->id, 'roleid'=>$teacherrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance2->id)));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>context_course::instance($course1->id)->id, 'userid'=>$user1->id, 'roleid'=>$studentrole->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        $multicohortplugin->set_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);
        $DB->delete_records('multicohort_members', array('multicohortid'=>$multicohort1->id)); // Use low level DB api to prevent events!
        $DB->delete_records('multicohort', array('id'=>$multicohort1->id)); // Use low level DB api to prevent events!
        enrol_multicohort_sync($trace, $course1->id);
        $this->assertEquals(5, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(3, $DB->count_records('role_assignments', array()));

        $multicohortplugin->set_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);
        enrol_multicohort_sync($trace, $course1->id);
        $this->assertEquals(3, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(3, $DB->count_records('role_assignments', array()));


        // Test group sync.

        $this->disable_plugin(); // No event sync
        // Trigger sync to remove extra role assignments.
        enrol_multicohort_sync($trace, $course1->id);
        $this->assertEquals(2, $DB->count_records('role_assignments', array()));

        $id = groups_create_group((object)array('name'=>'Group 1', 'courseid'=>$course1->id));
        $group1 = $DB->get_record('groups', array('id'=>$id), '*', MUST_EXIST);
        $id = groups_create_group((object)array('name'=>'Group 2', 'courseid'=>$course1->id));
        $group2 = $DB->get_record('groups', array('id'=>$id), '*', MUST_EXIST);
        $id = groups_create_group((object)array('name'=>'Group 2', 'courseid'=>$course2->id));
        $group3 = $DB->get_record('groups', array('id'=>$id), '*', MUST_EXIST);

        $multicohort1 = $this->getDataGenerator()->create_multicohort(array('contextid'=>context_coursecat::instance($cat1->id)->id));
        $id = $multicohortplugin->add_instance($course1, array('customint1'=>$multicohort1->id, 'roleid'=>$studentrole->id, 'customint2'=>$group1->id));
        $multicohortinstance1 = $DB->get_record('enrol', array('id'=>$id));

        $this->assertTrue(groups_add_member($group1, $user4));
        $this->assertTrue(groups_add_member($group2, $user4));

        $this->assertEquals(3, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(2, $DB->count_records('role_assignments', array()));

        $this->assertFalse(groups_is_member($group1->id, $user1->id));
        multicohort_add_member($multicohort1->id, $user1->id);
        multicohort_add_member($multicohort1->id, $user4->id);
        multicohort_add_member($multicohort2->id, $user4->id);
        multicohort_add_member($multicohort2->id, $user3->id);

        $this->enable_plugin();

        enrol_multicohort_sync($trace, null);

        $this->assertEquals(8, $DB->count_records('user_enrolments', array()));
        $this->assertEquals(8, $DB->count_records('role_assignments', array()));

        $this->assertTrue(groups_is_member($group1->id, $user1->id));
        $this->assertTrue($DB->record_exists('groups_members', array('groupid'=>$group1->id, 'userid'=>$user1->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        $this->assertTrue(is_enrolled(context_course::instance($course1->id), $user4));
        $this->assertTrue(groups_is_member($group1->id, $user4->id));
        $this->assertFalse($DB->record_exists('groups_members', array('groupid'=>$group1->id, 'userid'=>$user4->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        $this->assertTrue(is_enrolled(context_course::instance($course2->id), $user3));
        $this->assertFalse(groups_is_member($group3->id, $user3->id));

        $multicohortinstance1->customint2 = $group2->id;
        $DB->update_record('enrol', $multicohortinstance1);
        $multicohortinstance3->customint2 = $group3->id;
        $DB->update_record('enrol', $multicohortinstance3);

        enrol_multicohort_sync($trace, null);
        $this->assertFalse(groups_is_member($group1->id, $user1->id));
        $this->assertTrue(groups_is_member($group2->id, $user1->id));
        $this->assertTrue($DB->record_exists('groups_members', array('groupid'=>$group2->id, 'userid'=>$user1->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        $this->assertTrue(groups_is_member($group1->id, $user4->id));
        $this->assertTrue(groups_is_member($group2->id, $user4->id));
        $this->assertFalse($DB->record_exists('groups_members', array('groupid'=>$group1->id, 'userid'=>$user4->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));
        $this->assertFalse($DB->record_exists('groups_members', array('groupid'=>$group2->id, 'userid'=>$user4->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance1->id)));

        $this->assertTrue(groups_is_member($group3->id, $user3->id));
        $this->assertTrue($DB->record_exists('groups_members', array('groupid'=>$group3->id, 'userid'=>$user3->id, 'component'=>'enrol_multicohort', 'itemid'=>$multicohortinstance3->id)));

        multicohort_remove_member($multicohort1->id, $user1->id);
        $this->assertFalse(groups_is_member($group1->id, $user1->id));

        multicohort_remove_member($multicohort1->id, $user4->id);
        $this->assertTrue(groups_is_member($group1->id, $user4->id));
        $this->assertTrue(groups_is_member($group2->id, $user4->id));
    }
}
