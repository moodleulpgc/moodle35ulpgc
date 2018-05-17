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
 * Concept Map question renderer class.
 *
 * @package    qtype
 * @subpackage conceptmap
 * @copyright  2011 Jorge Villalon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Generates the output for conceptmap questions.
 *
 * @copyright  2011 Jorge Villalon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
class qtype_conceptmap_renderer extends qtype_renderer {
	public function formulation_and_controls(question_attempt $qa,
			question_display_options $options) {

		$question = $qa->get_question();
		$responseoutput = $question->get_format_renderer($this->page);

		// Answer field.
		$step = $qa->get_last_step_with_qt_var('answer');
		if (empty($options->readonly)) {
			$answer = $responseoutput->response_area_input('answer', $qa,
					$step, 12, $options->context);

		} else {
			$answer = $responseoutput->response_area_read_only('answer', $qa,
					$step, 12, $options->context);
		}

		$result = '';
		$result .= html_writer::tag('div', $question->format_questiontext($qa),
				array('class' => 'qtext'));

		$result .= html_writer::start_tag('div', array('class' => 'ablock'));
		$result .= html_writer::tag('div', $answer, array('class' => 'answer'));
		$result .= html_writer::end_tag('div');

		return $result;
	}

	public function manual_comment(question_attempt $qa, question_display_options $options) {
		if ($options->manualcomment != question_display_options::EDITABLE) {
			return '';
		}

		$question = $qa->get_question();
		return html_writer::nonempty_tag('div', $question->format_text(
				$question->graderinfo, $question->graderinfo, $qa, 'qtype_conceptmap',
				'graderinfo', $question->id), array('class' => 'graderinfo'));
	}
}


/**
 * A base class to abstract out the differences between different type of
 * response format.
 *
 * @copyright  2011 Jorge Villalon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class qtype_conceptmap_format_renderer_base extends plugin_renderer_base {
	/**
	 * Render the students respone when the question is in read-only mode.
	 * @param string $name the variable name this input edits.
	 * @param question_attempt $qa the question attempt being display.
	 * @param question_attempt_step $step the current step.
	 * @param int $lines approximate size of input box to display.
	 * @param object $context the context teh output belongs to.
	 * @return string html to display the response.
	 */
	public abstract function response_area_read_only($name, question_attempt $qa,
			question_attempt_step $step, $lines, $context);

	/**
	 * Render the students respone when the question is in read-only mode.
	 * @param string $name the variable name this input edits.
	 * @param question_attempt $qa the question attempt being display.
	 * @param question_attempt_step $step the current step.
	 * @param int $lines approximate size of input box to display.
	 * @param object $context the context teh output belongs to.
	 * @return string html to display the response for editing.
	*/
	public abstract function response_area_input($name, question_attempt $qa,
			question_attempt_step $step, $lines, $context);

	/**
	 * @return string specific class name to add to the input element.
	*/
	protected abstract function class_name();
}

/**
 * An essay format renderer for essays where the student should use a plain
 * input box, but with a normal, proportional font.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_conceptmap_format_plain_renderer extends plugin_renderer_base {
	/**
	 * @return string the HTML for the concept map editor.
	 */
	protected function cmapdiv($id, $name, $response, $readonly) {
		global $CFG, $USER;
		 
		$lang = $USER->lang;
		$parts = explode("_",$lang);
		if(count($parts)>1) {
			$lang = $parts[0] .'_'.strtoupper($parts[1]);
		}
		$answer = '<meta name="gwt:property" content="locale='.$lang.'">';

		$answer .= '<link type="text/css" rel="stylesheet" href="'
				.$CFG->wwwroot.'/question/type/conceptmap/cmapweb/CmapWeb.css">'
						.'<script type="text/javascript" language="javascript" src="'
								.$CFG->wwwroot.'/question/type/conceptmap/cmapweb/cmapweb/cmapweb.nocache.js"></script>'
										.'<input type="hidden" id="'.$id
										.'" name="'.$name.'" value="'.$response.'">';
		 
			
		if($readonly) {
			$answer .= '<div id="conceptmap" style="background-color:white" width="640" height="480" readonly="true" input="'.$id.'"></div>';
		} else {
			$answer .= '<div id="conceptmap" style="background-color:white" width="640" height="480" readonly="false" input="'.$id.'"></div>';
		}
		return $answer;
	}

	protected function class_name() {
		return 'qtype_conceptmap_plain';
	}

	public function response_area_read_only($name, $qa, $step, $lines, $context) {
		$inputname = $qa->get_qt_field_name($name);
		$id = $inputname . '_id';

		return $this->cmapdiv($id, $inputname, $step->get_qt_var($name), true);
	}

	public function response_area_input($name, $qa, $step, $lines, $context) {
		$inputname = $qa->get_qt_field_name($name);
		$id = $inputname . '_id';

		return $this->cmapdiv($id, $inputname, $step->get_qt_var($name), false);
	}
}

