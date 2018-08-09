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
        $groupinfo = $this->get_setting_value('groupinfo');

        $paths[] = new restore_path_element('examboard', '/activity/examboard');
        
        if($groupinfo) {
            $paths[] = new restore_path_element('examboard_board', '/activity/examboard/boards/board');
            $paths[] = new restore_path_element('examboard_exam', '/activity/examboard/exams/exam');
        }
        
        if($groupinfo && $userinfo) {
        
            
            $paths[] = new restore_path_element('examboard_member', '/activity/examboard/boards/board/members/member');
            $paths[] = new restore_path_element('examboard_examinee', '/activity/examboard/exams/exam/examinees/examinee');
            $paths[] = new restore_path_element('examboard_tutor', '/activity/examboard/exams/exam/tutors/tutor');
            $paths[] = new restore_path_element('examboard_grade', '/activity/examboard/exams/exam/grades/grade');
            $paths[] = new restore_path_element('examboard_confirmation', '/activity/examboard/exams/exam/confirmations/confirmation');
            $paths[] = new restore_path_element('examboard_notification', '/activity/examboard/exams/exam/notifications/notification');
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Processes the examboard restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_examboard($data) {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        
        $data->publishboarddate = $this->apply_date_offset($data->publishboarddate);
        $data->publishgradedate = $this->apply_date_offset($data->publishgradedate);
        
        // insert the record
        $newitemid = $DB->insert_record('examboard', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Processes the board restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_examboard_board($data) {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;

        $data->examboardid = $this->get_new_parentid('examboard');
        if (!empty($data->groupid)) {
            $data->groupid = $this->get_mappingid('group', $data->groupid);
        } else {
            $data->groupid = 0;
        }
 
        $newitemid = $DB->insert_record('examboard_board', $data);
        $this->set_mapping('examboard_board', $oldid, $newitemid);
    }

    /**
     * Processes the exam restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_examboard_exam($data) {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;

        $data->examboardid = $this->get_new_parentid('examboard');
        $data->boardid = $this->get_mappingid('examboard_board', $data->boardid);
        $data->examdate = $this->apply_date_offset($data->examdate);
 
        $newitemid = $DB->insert_record('examboard_exam', $data);
        $this->set_mapping('examboard_exam', $oldid, $newitemid);
    }

    
    
    /**
     * Processes the member restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_member($data) {
        global $DB;

        $data = (object)$data;

        $data->boardid = $this->get_new_parentid('examboard_board');
        $data->userid = $this->get_mappingid('user', $data->userid);
        
        $newitemid = $DB->insert_record('examboard_member', $data);
    }

    /**
     * Processes the examinee restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_examboard_examinee($data) {
        global $DB;

        $data = (object)$data;

        $data->examid = $this->get_new_parentid('examboard_exam');
        $data->userid = $this->get_mappingid('user', $data->userid);
        
        $newitemid = $DB->insert_record('examboard_examinee', $data);
    }

    /**
     * Processes the tutor restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_examboard_tutor($data) {
        global $DB;
        
        $data = (object)$data;

        $data->examid = $this->get_new_parentid('examboard_exam');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->tutorid = $this->get_mappingid('user', $data->tutorid);
        
        $newitemid = $DB->insert_record('examboard_tutor', $data);
    }

    /**
     * Processes the grades restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_examboard_grade($data) {
        global $DB;
        
        $data = (object)$data;

        $data->examid = $this->get_new_parentid('examboard_exam');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->grader = $this->get_mappingid('user', $data->grader);
        
        $newitemid = $DB->insert_record('examboard_grade', $data);
    }

    
    /**
     * Processes the confirmations restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_examboard_confirmation($data) {
        global $DB;
        
        $data = (object)$data;

        $data->examid = $this->get_new_parentid('examboard_exam');
        $data->userid = $this->get_mappingid('user', $data->userid);
        
        $newitemid = $DB->insert_record('examboard_confirmation', $data);
    }
    

    /**
     * Processes the notifications restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_examboard_notification($data) {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;

        $data->examid = $this->get_new_parentid('examboard_exam');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->managerid = $this->get_mappingid('user', $data->managerid);
        
        $newitemid = $DB->insert_record('examboard_notification', $data);
        $this->set_mapping('examboard_notification', $oldid, $newitemid);
    }

    
    /**
     * Defines post-execution actions.
     */
    protected function after_execute() {
        $this->add_related_files('mod_examboard', 'intro', null);
        
        $this->add_related_files('mod_examboard', 'notification', 'examboard_notification');
    }
    
    /**
     * For all submissions in this assignment, either set the
     * submission->latest field to 1 for the latest attempts
     * or create a new submission record for grades with no submission.
     *
     * @return void
     */
    protected function synchronize_groups_gradeables() {
        global $DB, $CFG;

        // Required for constants.
        require_once($CFG->dirroot . '/mod/examboard/locallib.php');

        $examboardid = $this->get_new_parentid('examboard');
        
        $examboard = $DB->get_record('examboard', array('id' => $examboardid));
        $examboard->cmid = $this->task->get_moduleid();
        
        // just make sure there are groups for exam
        if($examboard->examgroups) {
            examboard_synchronize_groups($examboard);
        }

        // make sure proper configuration for working with other complementary modules
        if($examboard->gradeable || $examboard->proposal || $examboard->defense) {
            examboard_synchronize_gradeables($examboard);
        }
}
