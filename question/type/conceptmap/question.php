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
 * Represents a conceptmap question.
 *
 * @package question
 * @subpackage conceptmap
 * @copyright  2011 Jorge Villalon based on the essay question type by The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();



class qtype_conceptmap_question extends question_with_responses {
    public $graderinfo;
    public $graderinfoformat;

    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        question_engine::load_behaviour_class('manualgraded');
        return new qbehaviour_manualgraded($qa, $preferredbehaviour);
    }

    /**
     * @param moodle_page the page we are outputting to.
     * @return qtype_conceptmap_format_renderer_base the response-format-specific renderer.
     */
    public function get_format_renderer(moodle_page $page) {
        return $page->get_renderer('qtype_conceptmap', 'format_plain');
    }

    public function get_expected_data() {
        $expecteddata = array('answer' => PARAM_TEXT);
        return $expecteddata;
    }
	
    public function summarise_response(array $response) {
       if (isset($response['answer'])) {
            return question_utils::to_plain_text($response['answer'],
                    FORMAT_PLAIN, array('para' => false));
        } else {
            return null;
        }
    }
	
    public function get_correct_response() {
        return null;
    }

    public function is_complete_response(array $response) {
        return !empty($response['answer']);
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'answer');
    }

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
		if ($component == 'qtype_conceptmap' && $filearea == 'graderinfo') {
            return $options->manualcomment;

        } else {
            return parent::check_file_access($qa, $options, $component,
                    $filearea, $args, $forcedownload);
        }
    }
}
