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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Funciones necesarias para la personalización del interfaz de quiz
 *
 * @package local_ulpgcquiz
 * @copyright  2016 Enrique Castro @ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined ( 'MOODLE_INTERNAL' ) || die ();

function local_ulpgcquiz_extend_settings_navigation(settings_navigation $nav, context $context) {
    global $CFG, $PAGE;
    
    if(strpos($PAGE->pagetype, 'mod-quiz') !== false) {
        if(!get_config('quiz_makeexam', 'enabled')) {
            if (has_all_capabilities(array('mod/quiz:manage', 'mod/quiz:manageoverrides'), $context)) { // ecastro ULPGC add quiz export ODF/PDF
                if ($settingsnode = $nav->find('modulesettings', navigation_node::TYPE_SETTING)) {
                    $node = navigation_node::create(get_string('exportquiz', 'local_ulpgcquiz'),
                            new moodle_url('/local/ulpgcquiz/export.php', array('cmid'=>$PAGE->cm->id)),
                            navigation_node::TYPE_SETTING, null, 'mod_quiz_export',
                            new pix_icon('t/print', ''));
                    $settingsnode->add_node($node);
                }
            }
        }
        
        if ($settingsnode = $nav->find('quiz_report_makeexam', navigation_node::TYPE_SETTING)) {
            if ($rootnode = $nav->find('modulesettings', navigation_node::TYPE_SETTING)) {
                $settingsnode->remove();
                $key = 'mod_quiz_preview';
                if(!in_array($key, $rootnode->get_children_key_list())) {
                    $key = null;
                }
                $rootnode->add_node($settingsnode, $key);
            }
        }
    }
}


/**
 * Returns a list of all questions (= slots) used in a quiz instance. Only ids
 *
 * @param $quiz object, the record for quiz instance from database
 * @param $others bool indicate if checking should extend to other quizzes, storez as Exam versions in module Exam registrer
 * @return array of unique ids
 */
function local_ulpgcquiz_get_all_questionids($quiz, $others) {
    global $CFG, $DB;

    $questions = $DB->get_records_menu('quiz_slots', array('quizid' => $quiz->id), 'slot', 'slot, questionid');

    if($others && $makeexam = get_config('quiz_makeexam')) {
        if($makeexam->enabled && $makeexam->uniquequestions) {
            include_once($CFG->dirroot.'/mod/quiz/report/makeexam/lib.php');
            if($others = quiz_makeexam_quiz_used_questionids($quiz)) {
                foreach($others as $key=> $qid) {
                    $questions["o$key"] = $qid;
                }
            }
        }
    }

    if($questions) {
        $questions = array_unique($questions);
    }
    return $questions;
}

/**
 * Returns an icon to display in interfacle, allow to remove all questions in a section
 *
 * @param $structure object, quiz structure object corresponding to this instance
 * @param object $section the section to display this icon
 * @return int num of removed slots
 */

function local_ulpgcquiz_section_empty_icon($structure, $section) {
        global $OUTPUT;
        if(!$structure->can_be_edited()) {
            return '';
        }
        if(count($structure->get_slots_in_section($section->id)) <=1 ) {
            return;
        }
        $title = get_string('sectionempty', 'local_ulpgcquiz', $section->heading);
        $url = new \moodle_url('/local/ulpgcquiz/emptysection.php',
                array('sesskey' => sesskey(), 'cmid'=>$structure->get_cmid(), 'emptysection'=>$section->id));
        $image = $OUTPUT->pix_icon('i/enrolmentsuspended', $title);
        $confirm = new \confirm_action(get_string('confirmsectionempty', 'local_ulpgcquiz',  $section->heading));
        return $OUTPUT->action_link($url, $image, $confirm, array(
                'class' => 'cm-edit-action editing_delete', 'data-action' => 'emptysection'));
}

/**
 * Remove ALL the questions (but first) in the section with the given id
 *
 * @param $structure object, quiz structure object corresponding to this instance
 * @param int $sectionid the section to remove.
 * @return int num of removed slots
 */
function local_ulpgcquiz_empty_section($structure, $sectionid) {
    global $CFG, $DB;
        global $DB;

        $section = $DB->get_record('quiz_sections', array('id' => $sectionid), '*', MUST_EXIST);

        $sections = $structure->get_sections();

        $slots = $structure->get_slots_in_section($sectionid);
        if(count($sections) > 1) {
            array_shift($slots);
        }
        $slots = array_reverse($slots);
        $count = 0;
        foreach($slots as $slot) {
            $structure->remove_slot($slot);
            $count += 1;
        }
        return $count;

}

