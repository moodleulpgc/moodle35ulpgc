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
 * Matching question renderer class.
 *
 * @package   qtype_match
 * @copyright 2009 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Generates the output for matching questions.
 *
 * @copyright 2009 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_match_renderer extends qtype_with_combined_feedback_renderer {

    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        $question = $qa->get_question();
        $stemorder = $question->get_stem_order();
        $response = $qa->get_last_qt_data();

        $choices = $this->format_choices($question);

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa),
                array('class' => 'qtext'));

        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= html_writer::start_tag('table', array('class' => 'answer'));
        $result .= html_writer::start_tag('tbody');

        $parity = 0;
        $i = 1;
        foreach ($stemorder as $key => $stemid) {

            $result .= html_writer::start_tag('tr', array('class' => 'r' . $parity));
            $fieldname = 'sub' . $key;

            $result .= html_writer::tag('td', $this->format_stem_text($qa, $stemid),
                    array('class' => 'text'));

            $classes = 'control';
            $feedbackimage = '';

            if (array_key_exists($fieldname, $response)) {
                $selected = $response[$fieldname];
            } else {
                $selected = 0;
            }

            $fraction = (int) ($selected && $selected == $question->get_right_choice_for($stemid));

            if ($options->correctness && $selected) {
                $classes .= ' ' . $this->feedback_class($fraction);
                $feedbackimage = $this->feedback_image($fraction);
            }

            $result .= html_writer::tag('td',
                    html_writer::label(get_string('answer', 'qtype_match', $i),
                            'menu' . $qa->get_qt_field_name('sub' . $key), false,
                            array('class' => 'accesshide')) .
                    html_writer::select($choices, $qa->get_qt_field_name('sub' . $key), $selected,
                            array('0' => 'choose'), array('disabled' => $options->readonly, 'class' => 'custom-select m-l-1')) .
                    ' ' . $feedbackimage, array('class' => $classes));

            $result .= html_writer::end_tag('tr');
            $parity = 1 - $parity;
            $i++;
        }
        $result .= html_writer::end_tag('tbody');
        $result .= html_writer::end_tag('table');

        $result .= html_writer::end_tag('div'); // Closes <div class="ablock">.

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                    $question->get_validation_error($response),
                    array('class' => 'validationerror'));
        }

        return $result;
    }

    /**
     * Generate the HTML export snippet of the formulation part of the question.
     *
     * @copyright  2013 Enrique Castro @ ULPGC
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return string HTML fragment.
     */
    public function formulation_export(question_attempt $qa, question_display_options $options) { // ecastro ULPGC
        $question = $qa->get_question();
        $stemorder = $question->get_stem_order();

        $questiontext = html_writer::tag('div', $question->format_questiontext($qa),
                array('class' => 'qtext'));

        $rchoices = $this->format_choices($question);

        $lchoices = array();
        foreach ($stemorder as $key => $stemid) {
            $lchoices[] = $question->format_text($question->stems[$stemid], $question->stemformat[$stemid],
                            $qa, 'qtype_match', 'subquestion', $stemid);
        }

        $choicestext = '';
        $choicestext .= html_writer::start_tag('table', array('class' => 'answer'));
        $choicestext .= html_writer::start_tag('tr');
            $choicestext .= html_writer::tag('td', html_writer::alist($lchoices));
            $choicestext .= html_writer::tag('td', html_writer::alist($rchoices));
        $choicestext .= html_writer::end_tag('tr');
        $choicestext .= html_writer::end_tag('table');

        $answer = '';
        $rightanswer = '';
        if($options->rightanswer) {
            $rightanswer = $qa->get_question()->get_right_answer_summary();
            $answer = '<div class="rightanswer">'.get_string('answer', 'question').': '.$rightanswer.'</div>';
        }
        return $questiontext.$choicestext.$answer;
    }

    public function specific_feedback(question_attempt $qa) {
        return $this->combined_feedback($qa);
    }

    /**
     * Format each question stem. Overwritten by randomsamatch renderer.
     *
     * @param question_attempt $qa
     * @param integer $stemid stem index
     * @return string
     */
    public function format_stem_text($qa, $stemid) {
        $question = $qa->get_question();
        return $question->format_text(
                    $question->stems[$stemid], $question->stemformat[$stemid],
                    $qa, 'qtype_match', 'subquestion', $stemid);
    }

    protected function format_choices($question) {
        $choices = array();
        foreach ($question->get_choice_order() as $key => $choiceid) {
            $choices[$key] = format_string($question->choices[$choiceid], true,
                    array('context' => $question->contextid));
        }
        return $choices;
    }

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        $stemorder = $question->get_stem_order();

        $choices = $this->format_choices($question);
        $right = array();
        foreach ($stemorder as $key => $stemid) {
            if (!isset($choices[$question->get_right_choice_for($stemid)])) {
                continue;
            }
            $right[] = $question->make_html_inline($this->format_stem_text($qa, $stemid)) . ' &#x2192; ' .
                    $choices[$question->get_right_choice_for($stemid)];
        }

        if (!empty($right)) {
            return get_string('correctansweris', 'qtype_match', implode(', ', $right));
        }
    }
}
