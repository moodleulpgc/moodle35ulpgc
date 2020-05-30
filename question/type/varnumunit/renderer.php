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
 * Variable numeric question renderer class.
 *
 * @package    qtype
 * @subpackage varnumunit
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/varnumericset/rendererbase.php');


/**
 * Generates the output for variable numeric with units question type.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_varnumunit_renderer extends qtype_varnumeric_renderer_base {

    public function specific_feedback(question_attempt $qa) {
        $parentfeedback = parent::specific_feedback($qa);

        $question = $qa->get_question();

        $unit = $question->get_matching_unit(array('answer' => $qa->get_last_qt_var('answer')));
        if (!$unit || !$unit->feedback) {
            return $parentfeedback.'';
        }

        return $parentfeedback . $question->format_text($unit->feedback, $unit->feedbackformat,
            $qa, 'question', 'answerfeedback', $unit->id);
    }

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();

        $answer = $question->get_correct_answer();
        if (!$answer) {
            return '';
        }

        return get_string('correctansweris', 'qtype_varnumunit', $answer->answer);
    }
}
