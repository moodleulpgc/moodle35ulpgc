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
 * This file contains overall tests of varnumunit questions.
 *
 * @package   qtype_varnumunit
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/varnumunit/tests/helper.php');


/**
 * Walk through Unit tests for varnumunit questions.
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group qtype_varnumunit
 */
class qtype_varnumunit_walkthrough_test extends qbehaviour_walkthrough_test_base {
    protected function setUp() {
        global $PAGE;
        parent::setUp();
        $PAGE->set_url('/');
    }

    public function test_validation_and_interactive_with_m_unit_submission_with_no_unit() {

        // Create a varnumunit question.
        $q = test_question_maker::make_question('varnumunit', '3_sig_figs_with_m_unit');
        $q->hints = array(
            new question_hint(1, 'This is the first hint.', FORMAT_HTML),
            new question_hint(2, 'This is the second hint.', FORMAT_HTML),
        );
        $this->start_attempt_at_question($q, 'interactive', 100);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_tries_remaining_expectation(3),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());

        // Submit blank.
        $this->process_submission(array('-submit' => 1, 'answer' => ''));

        $this->check_current_state(question_state::$invalid);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_contains_validation_error_expectation(),
            new question_pattern_expectation('/' .
                preg_quote(get_string('pleaseenterananswer', 'qtype_varnumericset') . '/')),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());

        // Submit something that does not look like a number.
        $this->process_submission(array('-submit' => 1, 'answer' => 'newt'));
        $this->check_current_state(question_state::$invalid);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_contains_validation_error_expectation(),
            new question_pattern_expectation('/' .
                preg_quote(get_string('notvalidnumber', 'qtype_varnumericset') . '/')),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());

        // Submit without unit.
        $this->process_submission(array('-submit' => 1, 'answer' => '12300'));

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_contains_hint_expectation('This is the first hint.'));
        $a = new stdClass();
        $a->numeric = '12300';
        $a->unit = '';
        $this->assertEquals(get_string('summarise_response', 'qtype_varnumunit', $a),
                            $this->quba->get_response_summary($this->slot));

        // Submit all and finish without unit.
        $this->process_submission(array('-finish' => 1));

        $this->check_current_state(question_state::$gradedpartial);
        $this->check_current_mark(75);
        $this->check_current_output(
            $this->get_contains_mark_summary(75),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_no_hint_visible_expectation());
        $a = new stdClass();
        $a->numeric = '12300';
        $a->unit = '';
        $this->assertEquals(get_string('summarise_response', 'qtype_varnumunit', $a),
            $this->quba->get_response_summary($this->slot));
    }

    public function test_validation_and_interactive_with_m_unit_submission_with_no_unit_then_with_unit() {

        // Create a varnumunit question.
        $q = test_question_maker::make_question('varnumunit', '3_sig_figs_with_m_unit');
        $q->hints = array(
            new question_hint(1, 'This is the first hint.', FORMAT_HTML),
            new question_hint(2, 'This is the second hint.', FORMAT_HTML),
        );
        $this->start_attempt_at_question($q, 'interactive', 100);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_tries_remaining_expectation(3),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());

        // Submit without unit.
        $this->process_submission(array('-submit' => 1, 'answer' => '12300'));

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_contains_hint_expectation('This is the first hint.'));
        $a = new stdClass();
        $a->numeric = '12300';
        $a->unit = '';
        $this->assertEquals(get_string('summarise_response', 'qtype_varnumunit', $a),
            $this->quba->get_response_summary($this->slot));

        // Do try again.
        $this->process_submission(array('-tryagain' => 1));

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_does_not_contain_try_again_button_expectation());

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);

        // Now get it right.
        $this->process_submission(array('-submit' => 1, 'answer' => '12300m'));

        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(91.6666675);
        $this->check_current_output(
            $this->get_contains_mark_summary(91.6666675),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_no_hint_visible_expectation());
        $a = new stdClass();
        $a->numeric = '12300';
        $a->unit = 'm';
        $this->assertEquals(get_string('summarise_response', 'qtype_varnumunit', $a),
            $this->quba->get_response_summary($this->slot));
    }

    public function test_validation_and_interactive_with_m_unit_submission_with_wrong_unit_and_partially_correct_number() {

        // Create a varnumunit question.
        $q = test_question_maker::make_question('varnumunit', '3_sig_figs_with_m_unit');
        $q->hints = array(
            new question_hint_with_parts(1, 'This is the first hint.', FORMAT_HTML, '0', '1'),
            new question_hint_with_parts(2, 'This is the second hint.', FORMAT_HTML, '0', '1'),
        );
        $this->start_attempt_at_question($q, 'interactive', 100);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_tries_remaining_expectation(3),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());

        // Submit with wrong unit and number off by factor of 10.
        $this->process_submission(array('-submit' => 1, 'answer' => '12.300s'));

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_no_hint_visible_expectation());
        $a = new stdClass();
        $a->numeric = '12.300';
        $a->unit = 's';
        $this->assertEquals(get_string('summarise_response', 'qtype_varnumunit', $a),
            $this->quba->get_response_summary($this->slot));

        // Do try again.
        $this->process_submission(array('-tryagain' => 1));

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_does_not_contain_try_again_button_expectation());

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);

        // Now get it right.
        $this->process_submission(array('-submit' => 1, 'answer' => '12300m'));

        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(84.1666675);
        $this->check_current_output(
            $this->get_contains_mark_summary(84.1666675),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_no_hint_visible_expectation());
        $a = new stdClass();
        $a->numeric = '12300';
        $a->unit = 'm';
        $this->assertEquals(get_string('summarise_response', 'qtype_varnumunit', $a),
            $this->quba->get_response_summary($this->slot));
    }

    public function test_validation_and_interactive_with_simple_1_m_question() {

        // Create a varnumunit question.
        $q = test_question_maker::make_question('varnumunit', 'simple_1_m');
        $q->hints = array(
            new question_hint_with_parts(1, 'This is the first hint.', FORMAT_HTML, '0', '1'),
            new question_hint_with_parts(2, 'This is the second hint.', FORMAT_HTML, '0', '1'),
        );
        $this->start_attempt_at_question($q, 'interactive', 100);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_tries_remaining_expectation(3),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());

        // Submit with wrong unit.
        $this->process_submission(array('-submit' => 1, 'answer' => '1 x'));

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_contains_hint_expectation('This is the first hint.'));
        $a = new stdClass();
        $a->numeric = '1';
        $a->unit = ' x';
        $this->assertEquals(get_string('summarise_response', 'qtype_varnumunit', $a),
            $this->quba->get_response_summary($this->slot));

        // Do try again.
        $this->process_submission(array('-tryagain' => 1));

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());

        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);

        // Now get it right.
        $this->process_submission(array('-submit' => 1, 'answer' => '1m'));

        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(98);
        $this->check_current_output(
            $this->get_contains_mark_summary(98),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_no_hint_visible_expectation());
        $a = new stdClass();
        $a->numeric = '1';
        $a->unit = 'm';
        $this->assertEquals(get_string('summarise_response', 'qtype_varnumunit', $a),
            $this->quba->get_response_summary($this->slot));
    }

    public function test_deferred_feedback_with_wrong_unit_and_wrong_number() {

        // Create a varnumunit question.
        $q = test_question_maker::make_question('varnumunit', '3_sig_figs_with_units_meters_per_second');

        $this->start_attempt_at_question($q, 'deferredfeedback', 100);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());

        // Save with wrong unit and number.
        $this->process_submission(array('answer' => '5e3 ms '));

        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());
        $this->assertEquals(null, $this->quba->get_response_summary($this->slot));

        // Finish.
        $this->process_submission(array('-finish' => 1));

        $this->check_current_state(question_state::$gradedwrong);
        $this->check_current_mark(0);
        $this->check_current_output(
            $this->get_contains_mark_summary(0),
            $this->get_contains_general_feedback_expectation($q),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());
        $a = new stdClass();
        $a->numeric = '5e3';
        $a->unit = ' ms';
        $this->assertEquals(get_string('summarise_response', 'qtype_varnumunit', $a),
            $this->quba->get_response_summary($this->slot));
    }
    public function test_deferred_feedback_with_wrong_unit_but_correct_number() {

        // Create a varnumunit question.
        $q = test_question_maker::make_question('varnumunit', '3_sig_figs_with_units_meters_per_second');

        $this->start_attempt_at_question($q, 'deferredfeedback', 100);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());

        // Save with wrong unit and number.
        $this->process_submission(array('answer' => '4.000e3 g'));

        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());
        $this->assertEquals(null, $this->quba->get_response_summary($this->slot));

        // Finish.
        $this->process_submission(array('-finish' => 1));

        $this->check_current_state(question_state::$gradedpartial);
        $this->check_current_mark(90);
        $this->check_current_output(
            $this->get_contains_mark_summary(90),
            $this->get_contains_general_feedback_expectation($q),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());
        $a = new stdClass();
        $a->numeric = '4.000e3';
        $a->unit = ' g';
        $this->assertEquals(get_string('summarise_response', 'qtype_varnumunit', $a),
            $this->quba->get_response_summary($this->slot));
    }

    public function test_deferred_feedback_with_correct_unit_but_wrong_number() {
        // Create a varnumunit question.
        $q = test_question_maker::make_question('varnumunit', '3_sig_figs_with_units_meters_per_second');

        $this->start_attempt_at_question($q, 'deferredfeedback', 100);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());

        // Save with wrong unit and number.
        $this->process_submission(array('answer' => '5.000e3 m/s'));

        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());
        $this->assertEquals(null, $this->quba->get_response_summary($this->slot));

        // Finish.
        $this->process_submission(array('-finish' => 1));

        $this->check_current_state(question_state::$gradedpartial);
        $this->check_current_mark(10);
        $this->check_current_output(
            $this->get_contains_mark_summary(10),
            $this->get_contains_general_feedback_expectation($q),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());
        $a = new stdClass();
        $a->numeric = '5.000e3';
        $a->unit = ' m/s';
        $this->assertEquals(get_string('summarise_response', 'qtype_varnumunit', $a),
            $this->quba->get_response_summary($this->slot));
    }

    public function test_deferred_feedback_with_correct_unit_and_correct_number() {
        // Create a varnumunit question.
        $q = test_question_maker::make_question('varnumunit', '3_sig_figs_with_units_meters_per_second');

        $this->start_attempt_at_question($q, 'deferredfeedback', 100);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());

        // Save with wrong unit and number.
        $this->process_submission(array('answer' => '4.000e3 ms<sup>-1</sup>'));

        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());
        $this->assertEquals(null, $this->quba->get_response_summary($this->slot));

        // Finish.
        $this->process_submission(array('-finish' => 1));

        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(100);
        $this->check_current_output(
            $this->get_contains_mark_summary(100),
            $this->get_contains_general_feedback_expectation($q),
            $this->get_does_not_contain_validation_error_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_no_hint_visible_expectation());
        $a = new stdClass();
        $a->numeric = '4.000e3';
        $a->unit = ' ms<sup>-1</sup>';
        $this->assertEquals(get_string('summarise_response', 'qtype_varnumunit', $a),
            $this->quba->get_response_summary($this->slot));
    }
}
