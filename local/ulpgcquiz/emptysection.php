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
 * Page to edit quizzes
 *
 * This page generally has two columns:
 * The right column lists all available questions in a chosen category and
 * allows them to be edited or more to be added. This column is only there if
 * the quiz does not already have student attempts
 * The left column lists all questions that have been added to the current quiz.
 * The lecturer can add questions from the right hand list to the quiz or remove them
 *
 * The script also processes a number of actions:
 * Actions affecting a quiz:
 * up and down  Changes the order of questions and page breaks
 * addquestion  Adds a single question to the quiz
 * add          Adds several selected questions to the quiz
 * addrandom    Adds a certain number of random questions to the quiz
 * repaginate   Re-paginates the quiz
 * delete       Removes a question from the quiz
 * savechanges  Saves the order and grades for questions in the quiz
 *
 * @package    mod_quiz
 * @copyright  2016 Enrqiue Castrom, from quiz/edit.php 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/question/editlib.php');
require_once($CFG->dirroot . '/local/ulpgcquiz/lib.php');

// These params are only passed from page request to request while we stay on
// this page otherwise they would go in question_edit_setup.
$scrollpos = optional_param('scrollpos', '', PARAM_INT);

list($thispageurl, $contexts, $cmid, $cm, $quiz, $pagevars) =
        question_edit_setup('editq', '/mod/quiz/edit.php', true);

$PAGE->set_url($thispageurl); // let's fake this is the original quiz/edit.php page

// Get the course object and related bits.
$course = $DB->get_record('course', array('id' => $quiz->course), '*', MUST_EXIST);
$quizobj = new quiz($quiz, $cm, $course);
$structure = $quizobj->get_structure();

// You need mod/quiz:manage in addition to question capabilities to access this page.
require_capability('mod/quiz:manage', $contexts->lowest());

// Process commands ============================================================.

$afteractionurl = new moodle_url($thispageurl);
if ($scrollpos) {
    $afteractionurl->param('scrollpos', $scrollpos);
}

$count = false;

if (($sectionid = optional_param('emptysection', 0, PARAM_INT)) && confirm_sesskey()) { // ecastro ULPGC to allow massive deleting of questions in a section
    // remove questions but last from section
    $structure->check_can_be_edited();
    if($count = local_ulpgcquiz_empty_section($structure, $sectionid)) {
        quiz_delete_previews($quiz);
        quiz_update_sumgrades($quiz);
        
        // Log this visit.
        $params = array(
            'courseid' => $course->id,
            'context' => $contexts->lowest(),
            'other' => array(
                'quizid' => $quiz->id,
                'sectionid' => $sectionid,
            )
        );
        $event = \local_ulpgcquiz\event\section_emptied::create($params);
        $event->trigger();
    }
}

redirect($afteractionurl, get_string('sectionemptied', 'local_ulpgcquiz', $count));
