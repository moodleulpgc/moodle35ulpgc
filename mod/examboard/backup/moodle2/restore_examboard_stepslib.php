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
 * All the steps to restore mod_examboard are defined here.
 *
 * @package     mod_examboard
 * @category    restore
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// For more information about the backup and restore process, please visit:
// https://docs.moodle.org/dev/Backup_2.0_for_developers
// https://docs.moodle.org/dev/Restore_2.0_for_developers

/**
 * Defines the structure step to restore one mod_examboard activity.
 */
class restore_examboard_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines the structure to be restored.
     *
     * @return restore_path_element[].
     */
    protected function define_structure() {
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('examboard', '/activity/examboard');
        $paths[] = new restore_path_element('board', '/activity/examboard/boards/board');
        $paths[] = new restore_path_element('member', '/activity/examboard/members/member');
        $paths[] = new restore_path_element('examinee', '/activity/examboard/examinees/examinee');
        $paths[] = new restore_path_element('tutor', '/activity/examboard/tutors/tutor');
        $paths[] = new restore_path_element('grades', '/activity/examboard/grades/grade');

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Processes the examboard restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_examboard($data) {
        return;
    }

    /**
     * Processes the board restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_board($data) {
        return;
    }

    /**
     * Processes the member restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_member($data) {
        return;
    }

    /**
     * Processes the examinee restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_examinee($data) {
        return;
    }

    /**
     * Processes the tutor restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_tutor($data) {
        return;
    }

    /**
     * Processes the grades restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_grades($data) {
        return;
    }

    /**
     * Defines post-execution actions.
     */
    protected function after_execute() {
        return;
    }
}
