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
 * Event observers used in forum.
 *
 * @package    mod_forum
 * @copyright  2013 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for mod_forum.
 */
class local_ulpgccore_observer {

    /**
     * Triggered via course_deleted event.
     *
     * @param core\event\course_deleted $event
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;

        // NOTE: this has to be as fast as possible.
        $DB->delete_records('local_ulpgccore_course', array('courseid'=>$event->objectid));
    }

    /**
     * Triggered via course_category_deleted event.
     *
     * @param \core\event\course_category_deleted $event
     */
    public static function course_category_deleted(\core\event\course_category_deleted $event) {
        global $DB;

        // NOTE: this has to be as fast as possible.
        $DB->delete_records('local_ulpgccore_categories', array('categoryid'=>$event->objectid));
    }
    
    
    /**
     * Observer for group_deleted event.
     *
     * @param \core\event\group_deleted $event
     * @return void
     */
    public static function group_deleted(\core\event\group_deleted $event) {
        global $CFG, $DB;

        $DB->delete_records('local_ulpgcgroups', array('groupid'=>$event->objectid));
    }

    /**
     * Observer for course_module created/updatred event.
     *
     * @param \core\event\group_deleted $event
     * @return void
     */
    public static function course_module_adminmod($event) {
        global $CFG, $DB;
        // if the module is set as admin restricted, remove delete permission
        if(get_config('local_ulpgccore', 'enabledadminmods') && $score = $DB->get_field('course_modules', 'score', array('id'=>$event->objectid, 'course'=>$event->courseid))) {
            $context = context_module::instance($event->objectid);
            list($neededroles, $forbiddenroles) = get_roles_with_cap_in_context($context, 'local/ulpgccore:moddelete');
            foreach($neededroles as $roleid) {
                if($neededroles[$roleid] && (!isset($forbiddenroles[$roleid]) || !$forbiddenroles[$roleid])) { 
                    assign_capability('local/ulpgccore:moddelete', CAP_PREVENT, $roleid, $context->id, true);
                    $context->mark_dirty();
                }
            }
        }
    }

    
}
