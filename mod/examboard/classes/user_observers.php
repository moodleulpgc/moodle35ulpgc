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
 * Group observers.
 *
 * @package    mod_examboard
 * @copyright  2018 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_examboard;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/examboard/locallib.php');

/**
 * Group observers class.
 *
 * @package    mod_examboard
 * @copyright  2018 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_observers {

    /**
     * A user has been assigned/unassigned as tutor or student
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function synch_exam($event) {
        global $DB;
        $examboard = $DB->get_record('examboard', array('id' => $event->other['examboardid']));
        $exam = examboard_get_exam_with_board($event->objectid); 
        if($examboard && $exam) {
            examboard_synchronize_groups($examboard, $exam);
            examboard_synchronize_gradeables($examboard, $exam, false); 
        }
    }

    /**
     * A user has been assigned as board member
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function synch_board($event) {
        global $DB;
        $examboard = $DB->get_record('examboard', array('id' => $event->other['examboardid']));
        if($examboard && $exams = examboard_get_board_exams($event->objectid, $event->other['examboardid'], false)) {
            $exams = reset($exams);
            foreach($exams as $exam) {
                examboard_synchronize_groups($examboard, $exam);
                examboard_synchronize_gradeables($examboard, $exam, false); 
            }
        }
    }
}
