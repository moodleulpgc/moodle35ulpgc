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
 * Event observer for course format topicgroup plugin.
 *
 * @package    format_topicgroup
 * @copyright  2016 Enrique Castro @ ULPGC 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Event observer course format topicgroup.
 *
 * @package    format_topicgroup
 * @copyright  2016 Enrique Castro @ ULPGC 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_topicgroup_observer {

    /**
     * Triggered via course_module_created event.
     *
     * @param \core\event\user_enrolment_created $event
     * @return bool true on success.
     */
    public static function module_created(\core\event\base $event) {
        global $DB;
        // handle event
        $format = course_get_format($event->courseid)->get_format();
        if($format == 'topicgroup') {
            $section = $DB->get_field('course_modules', 'section', array('id'=>$event->objectid, 'course'=>$event->courseid));
            $groupingid = $DB->get_field('format_topicgroup_sections', 'groupingid', array('id'=>$section, 'course'=>$event->courseid));
            if($section && $groupingid) {
                require_once($CFG->dirroot.'/course/format/topicgroup/lib.php');
                format_topicgroup_mod_restrictions($section);
            }
        }
   
        return true;
    }

    /**
     * Triggered via course_module_updated event.
     *
     * @param \core\event\course_module_updated $event
     * @return bool true on success.
     */
    public static function module_updated(\core\event\base $event) {
    
        self::module_created($event);

        return true;
    }

}
