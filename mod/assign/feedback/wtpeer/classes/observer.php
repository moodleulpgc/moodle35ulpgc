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
 * Event observers used in assignfeedback_wtpeer.
 *
 * @package    assignfeedback_wtpeer
 * @copyright  2016 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for mod_forum.
 */
class assignfeedback_wtpeer_observer {
    
    /**
     * Observer for group_deleted event.
     *
     * @param \core\event\group_deleted $event
     * @return void
     */
    public static function submission_done(\core\event\group_deleted $event) {
        global $CFG, $DB;

       
        $submission = $DB->get_record('assign_submission', array('id'=>$event->objectid), '*', MUST_EXISTS);
        if($submission->userid) {
            $grade = $event->assign->get_user_grade($submission->userid, true);
        }
       
    }
    
}
