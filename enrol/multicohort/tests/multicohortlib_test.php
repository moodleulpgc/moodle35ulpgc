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
 * @category   test
 * @copyright  2015 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/multicohort/lib.php');
require_once($CFG->dirroot.'/group/lib.php');

/**
 * Contains tests for the multicohort library.
 *
 * @package   enrol_multicohort
 * @copyright 2015 Adrian Greeve <adrian@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_multicohort_lib_testcase extends advanced_testcase {

    /**
     * Test that a new group with the name of the multicohort is created.
     */
    public function test_enrol_multicohort_create_new_group() {
        global $DB;
        $this->resetAfterTest();
        // Create a category.
        $category = $this->getDataGenerator()->create_category();
        // Create two courses.
        $course = $this->getDataGenerator()->create_course(array('category' => $category->id));
        $course2 = $this->getDataGenerator()->create_course(array('category' => $category->id));
        // Create a multicohort.
        $multicohort = $this->getDataGenerator()->create_multicohort(array('context' => context_coursecat::instance($category->id)->id));
        // Run the function.
        $groupid = enrol_multicohort_create_new_group($course->id, $multicohort->id);
        // Check the results.
        $group = $DB->get_record('groups', array('id' => $groupid));
        // The group name should match the multicohort name.
        $this->assertEquals($multicohort->name . ' multicohort', $group->name);
        // Group course id should match the course id.
        $this->assertEquals($course->id, $group->courseid);

        // Create a group that will have the same name as the multicohort.
        $groupdata = new stdClass();
        $groupdata->courseid = $course2->id;
        $groupdata->name = $multicohort->name . ' multicohort';
        groups_create_group($groupdata);
        // Create a group for the multicohort in course 2.
        $groupid = enrol_multicohort_create_new_group($course2->id, $multicohort->id);
        $groupinfo = $DB->get_record('groups', array('id' => $groupid));
        // Check that the group name has been changed.
        $this->assertEquals($multicohort->name . ' multicohort (2)', $groupinfo->name);

        // Create another group that will have the same name as a generated multicohort.
        $groupdata = new stdClass();
        $groupdata->courseid = $course2->id;
        $groupdata->name = $multicohort->name . ' multicohort (2)';
        groups_create_group($groupdata);
        // Create a group for the multicohort in course 2.
        $groupid = enrol_multicohort_create_new_group($course2->id, $multicohort->id);
        $groupinfo = $DB->get_record('groups', array('id' => $groupid));
        // Check that the group name has been changed.
        $this->assertEquals($multicohort->name . ' multicohort (3)', $groupinfo->name);

    }
}
