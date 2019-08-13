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
 * Backup steps for mod_examboard are defined here.
 *
 * @package     mod_examboard
 * @category    backup
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// For more information about the backup and restore process, please visit:
// https://docs.moodle.org/dev/Backup_2.0_for_developers
// https://docs.moodle.org/dev/Restore_2.0_for_developers

/**
 * Define the complete structure for backup, with file and id annotations.
 */
class backup_examboard_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the structure of the resulting xml file.
     *
     * @return backup_nested_element The structure wrapped by the common 'activity' element.
     */
    protected function define_structure() {
        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');
        // To know if we are including groups and groupings.
        
        $groupinfo = $this->setting_exists('groups') ? $this->get_setting_value('groups') : '';

        // Define each element separately.
        $examboard = new backup_nested_element('examboard',  array('id'),
                                                array('name',
                                                        'intro',
                                                        'introformat',
                                                        'maxboardsize',
                                                        'grademode',
                                                        'gradeable',
                                                        'proposal',
                                                        'defense',
                                                        'mingraders',
                                                        'allocation',
                                                        'usetutors',
                                                        'examgroups',
                                                        'groupingname',
                                                        'requireconfirm',
                                                        'notifyconfirm',
                                                        'confirmtime',
                                                        'confirmdefault',
                                                        'warntime',
                                                        'grade',
                                                        'publishboard',
                                                        'publishboarddate',
                                                        'publishgrade',
                                                        'publishgradedate',
                                                        'chair',
                                                        'secretary',
                                                        'vocal',
                                                        'examinee',
                                                        'tutor',
                                                        'timemodified'));

                                                
                                                
        $boards = new backup_nested_element('boards');

        $board = new backup_nested_element('board', array('id'), 
                                                array('title',
                                                        'idnumber',
                                                        'name',
                                                        'groupid',
                                                        'active',
                                                        'timemodified'));

        $exams = new backup_nested_element('exams');

        $exam = new backup_nested_element('exam', array('id'), 
                                                array('boardid',
                                                        'examperiod',
                                                        'sessionname',
                                                        'venue',
                                                        'examdate',
                                                        'duration',
                                                        'active',
                                                        'timemodified'));

                                                        
        $members = new backup_nested_element('members');

        $member = new backup_nested_element('member', array('id'), 
                                                array('userid',
                                                        'sortorder',
                                                        'role',
                                                        'deputy',
                                                        'timecreated',
                                                        'timemodified'));

                                                        
        $examinees = new backup_nested_element('examinees');

        $examinee = new backup_nested_element('examinee', array('id'), 
                                                array('userid',
                                                        'sortorder',
                                                        'excluded',
                                                        'userlabel',
                                                        'timecreated',
                                                        'timeexcluded',
                                                        'timemodified'));

        $tutors = new backup_nested_element('tutors');

        $tutor = new backup_nested_element('tutor', array('id'), 
                                                array('userid',
                                                        'tutorid',
                                                        'main',
                                                        'approved',
                                                        'timecreated',
                                                        'timemodified'));

        $grades = new backup_nested_element('grades');

        $grade = new backup_nested_element('grade', array('id'), 
                                                array('userid',
                                                        'grader',
                                                        'grade',
                                                        'timecreated',
                                                        'timemodified'));

        $confirmations = new backup_nested_element('confirmations');

        $confirmation = new backup_nested_element('confirmation', array('id'), 
                                                array('userid',
                                                        'confirmed',
                                                        'discharge',
                                                        'dischargetext',
                                                        'dischargeformat',
                                                        'available',
                                                        'exemption',
                                                        'timecreated',
                                                        'timeconfirmed',
                                                        'timeunconfirmed'));                                                        

        $notifications = new backup_nested_element('notifications');

        $notification = new backup_nested_element('notification', array('id'), 
                                                array('userid',
                                                        'managerid',
                                                        'role',
                                                        'timeissued')); 

        // Build the tree with thesmembere elements with $examboard as the root of the backup tree.
        $examboard->add_child($boards);
        $boards->add_child($board);
        $examboard->add_child($exams);
        $exams->add_child($exam);
        
        $board->add_child($members);
        $members->add_child($member);
        
        $exam->add_child($examinees);
        $examinees->add_child($examinee);

        $exam->add_child($tutors);
        $tutors->add_child($tutor);

        $exam->add_child($grades);
        $grades->add_child($grade);

        $exam->add_child($confirmations);
        $confirmations->add_child($confirmation);
        
        $exam->add_child($notifications);
        $notifications->add_child($notification);

        // Define the source tables for the elements.
        $examboard->set_source_table('examboard', array('id' => backup::VAR_ACTIVITYID));
        
        if ($groupinfo) {
            $board->set_source_table('examboard_board', array('examboardid' => backup::VAR_PARENTID), 'id ASC');
            
            $exam->set_source_table('examboard_exam', array('examboardid' => backup::VAR_PARENTID), 'id ASC');
        }

        if ($userinfo && $groupinfo) {
            $member->set_source_table('examboard_member', array('boardid' => backup::VAR_PARENTID), 'id ASC');
            
            $examinee->set_source_table('examboard_examinee', array('examid' => backup::VAR_PARENTID), 'id ASC');
            $tutor->set_source_table('examboard_tutor', array('examid' => backup::VAR_PARENTID), 'id ASC');
            $grade->set_source_table('examboard_grades', array('examid' => backup::VAR_PARENTID), 'id ASC');
            $confirmation->set_source_table('examboard_confirmation', array('examid' => backup::VAR_PARENTID), 'id ASC');
            $notification->set_source_table('examboard_notification', array('examid' => backup::VAR_PARENTID), 'id ASC');
        }
        
        // Define id annotations.
        $board->annotate_ids('group', 'groupid');
        $member->annotate_ids('user', 'userid');
        $examinee->annotate_ids('user', 'userid');
        $tutor->annotate_ids('user', 'userid');
        $tutor->annotate_ids('user', 'tutorid');
        $grade->annotate_ids('user', 'userid');
        $grade->annotate_ids('user', 'grader');
        
        $confirmation->annotate_ids('user', 'userid');
        $notification->annotate_ids('user', 'userid');
        $notification->annotate_ids('user', 'managerid');
        
        // Define file annotations.
        $examboard->annotate_files('mod_examboard', 'intro', null);
        $notification->annotate_files('mod_examboard', 'notification', 'id');
        
        return $this->prepare_activity_structure($examboard);
    }
}
