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
 * Event observers used in ulpgcassign.
 *
 * @package    local_ulpgcassign
 * @copyright  2016 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for mod_forum.
 */
class local_ulpgcassign_observer {
    
    /**
     * Observer for module assign updated
     *
     * @param \core\event\course_module_updated $event
     * @return void
     */
    public static function assign_updated(\core\event\course_module_updated $event) {
        global $CFG, $DB;
        
        if($event->other['modulename'] == 'assign') {
            // an assign instance has been updated, process
        
        }
    }
    
}
