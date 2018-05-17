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
 * Event observer for metacat enrolment plugin.
 *
 * @package    enrol_metacat
 * @copyright  2016 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/enrol/metacat/locallib.php');

/**
 * Event observer for enrol_metacat.
 *
 * @package    enrol_metacat
 * @copyright  2016 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_metacat_observer extends enrol_metacat_handler {

    


    /**
     * Triggered via user_enrolment_created event.
     *
     * @param \core\event\user_enrolment_created $event
     * @return bool true on success.
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event) {
        if (!enrol_is_enabled('metacat')) {
            // No more enrolments for disabled plugins.
            return true;
        }

        if ($event->other['enrol'] === 'metacat') {
            // Prevent circular dependencies - we can not sync meta enrolments recursively.
            return true;
        }

        self::sync_course_instances($event->courseid, $event->relateduserid);
        return true;
    }

    /**
     * Triggered via user_enrolment_deleted event.
     *
     * @param \core\event\user_enrolment_deleted $event
     * @return bool true on success.
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        if (!enrol_is_enabled('metacat')) {
            // This is slow, let enrol_meta_sync() deal with disabled plugin.
            return true;
        }

        if ($event->other['enrol'] === 'metacat') {
            // Prevent circular dependencies - we can not sync meta enrolments recursively.
            return true;
        }

        self::sync_course_instances($event->courseid, $event->relateduserid);

        return true;
    }

    /**
     * Triggered via user_enrolment_updated event.
     *
     * @param \core\event\user_enrolment_updated $event
     * @return bool true on success
     */
    public static function user_enrolment_updated(\core\event\user_enrolment_updated $event) {
        if (!enrol_is_enabled('metacat')) {
            // No modifications if plugin disabled.
            return true;
        }

        if ($event->other['enrol'] === 'metacat') {
            // Prevent circular dependencies - we can not sync meta enrolments recursively.
            return true;
        }

        self::sync_course_instances($event->courseid, $event->relateduserid);

        return true;
    }

    /**
     * Triggered via role_assigned event.
     *
     * @param \core\event\role_assigned $event
     * @return bool true on success.
     */
    public static function role_assigned(\core\event\role_assigned $event) {
        if (!enrol_is_enabled('metacat')) {
            return true;
        }

        // Prevent circular dependencies - we can not sync meta roles recursively.
        if ($event->other['component'] === 'enrol_metacat') {
            return true;
        }

        // Only course level roles are interesting.
        if (!$parentcontext = context::instance_by_id($event->contextid, IGNORE_MISSING)) {
            return true;
        }
        if ($parentcontext->contextlevel != CONTEXT_COURSE) {
            return true;
        }

        self::sync_course_instances($parentcontext->instanceid, $event->relateduserid);

        return true;
    }

    /**
     * Triggered via role_unassigned event.
     *
     * @param \core\event\role_unassigned $event
     * @return bool true on success
     */
    public static function role_unassigned(\core\event\role_unassigned $event) {
        if (!enrol_is_enabled('metacat')) {
            // All roles are removed via cron automatically.
            return true;
        }

        // Prevent circular dependencies - we can not sync meta roles recursively.
        if ($event->other['component'] === 'enrol_metacat') {
            return true;
        }

        // Only course level roles are interesting.
        if (!$parentcontext = context::instance_by_id($event->contextid, IGNORE_MISSING)) {
            return true;
        }
        if ($parentcontext->contextlevel != CONTEXT_COURSE) {
            return true;
        }

        self::sync_course_instances($parentcontext->instanceid, $event->relateduserid);

        return true;
    }

    /**
     * Triggered via course_created or course_restored or course_updated event.
     * Checks if course has metacat with autoupdated category
     *
     * @param \core\event\course_deleted $event
     * @return bool true on success
     */
    public static function course_changed(\core\event\base $event) {
        global $DB;
        if (!enrol_is_enabled('metacat')) {
            // This is slow, let enrol_meta_sync() deal with disabled plugin.
            return true;
        }

        // Check if changed course has metacat enrol with autocategory 
        $sql = "SELECT e.*, c.category 
            FROM {enrol} e 
            JOIN {course} c ON c.id = e.courseid
            WHERE e.enrol = 'metacat' AND e.customint4 = 1 AND e.courseid = :courseid AND e.customint1 <> c.category ";
        $params = array('courseid'=>$event->objectid);
        if(!$instances = $DB->get_records_sql($sql, $params)) {
            return true;
        }
        
        // if we are here, the course has changed metacat category source just sync
        $trace = new text_progress_trace();
        foreach($instances as $instance) {
            $instance->customint1 = $instance->category;
            enrol_metacat_sync($trace, $instance->courseid, $instance->category);
        }
        $trace->finished();

        return true;
    }

    
     /**
     * Triggered via category_deleted event.
     * @static
     * @param stdClass $category
     * @return bool success
     */
    public static function course_category_deleted(\core\event\course_category_deleted $event){
        global $DB;

        if (!enrol_is_enabled('metacat')) {
            // This is slow, let enrol_meta_sync() deal with disabled plugin.
            return true;
        }
        
        // does anything want to sync with this parent?
        if (!$enrols = $DB->get_records('enrol', array('customint1'=>$event->objectid, 'enrol'=>'metacat'), 'courseid ASC, id ASC')) {
            return true;
        }

        $plugin = enrol_get_plugin('metacat');
        $unenrolaction = $plugin->get_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);

        if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
            // Simple, just delete this instance which purges all enrolments,
            // admins were warned that this is risky setting!
            foreach ($enrols as $enrol) {
                $plugin->delete_instance($enrol);
            }
            return true;
        }

        foreach ($enrols as $enrol) {
            if ($unenrolaction == ENROL_EXT_REMOVED_SUSPEND or $unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                // This makes all enrolments suspended very quickly.
                $plugin->update_status($enrol, ENROL_INSTANCE_DISABLED);
            }
            if ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                $context = context_course::instance($enrol->courseid);
                role_unassign_all(array('contextid'=>$context->id, 'component'=>'enrol_meta', 'itemid'=>$enrol->id));
            }
        }

        return true;
    }   
    
}
