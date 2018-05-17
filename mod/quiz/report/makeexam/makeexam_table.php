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
 * Quiz makeexam report, table for showing makeexam of each question in the quiz.
 *
 * @package   quiz_makeexam
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');

/**
 * This table has one row for each question in the quiz, with sub-rows when
 * random questions appear. There are columns for the various makeexam.
 *
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_makeexam_table extends flexible_table {

    /** @var integer the quiz course_module id. */
    protected $cmid;

    /** @var moodle_url the URL of this report. */
    protected $reporturl;

    /** @var object the quiz settings. */
    protected $quiz;

    /** @var context the quiz context. */
    protected $context;

    /** @var bool whether to include the column with checkboxes to select each attempt. */
    protected $includecheckboxes;


    /**
     * Constructor
     * @param string $uniqueid
     * @param object $quiz
     * @param context $context
     * @param moodle_url $reporturl
     */
    public function __construct($quiz, $context, $reporturl, $includecheckboxes = false) {
        parent::__construct('mod-quiz-report-makeexam-report');
        $this->quiz = $quiz;
        $this->context = $context;
        $this->reporturl = $reporturl;
        $this->cmid = $context->instanceid;
        $this->includecheckboxes = $includecheckboxes;
    }


    /**
     * Set up the columns and headers and other properties of the table and then
     * call flexible_table::setup() method.
     *
     * @param object $quiz the quiz settings
     * @param int $cmid the quiz course_module id
     * @param moodle_url $reporturl the URL to redisplay this report.
     * @param int $s number of attempts included in the makeexam.
     */
    public function makeexam_setup($quiz, $cmid, $reporturl, $s) {
        $this->quiz = $quiz;
        $this->cmid = $cmid;

        // Define the table columns.
        $columns = array();
        $headers = array();

        $columns[] = 'number';
        $headers[] = get_string('questionnumber', 'quiz_makeexam');

        if (!$this->is_downloading()) {
            $columns[] = 'icon';
            $headers[] = '';
            $columns[] = 'actions';
            $headers[] = '';
        } else {
            $columns[] = 'qtype';
            $headers[] = get_string('questiontype', 'quiz_makeexam');
        }

        $columns[] = 'name';
        $headers[] = get_string('questionname', 'quiz');

        $columns[] = 's';
        $headers[] = get_string('attempts', 'quiz_makeexam');

        if ($s > 1) {
            $columns[] = 'facility';
            $headers[] = get_string('facility', 'quiz_makeexam');

            $columns[] = 'sd';
            $headers[] = get_string('standarddeviationq', 'quiz_makeexam');
        }

        $columns[] = 'random_guess_score';
        $headers[] = get_string('random_guess_score', 'quiz_makeexam');

        $columns[] = 'intended_weight';
        $headers[] = get_string('intended_weight', 'quiz_makeexam');

        $columns[] = 'effective_weight';
        $headers[] = get_string('effective_weight', 'quiz_makeexam');

        $columns[] = 'discrimination_index';
        $headers[] = get_string('discrimination_index', 'quiz_makeexam');

        $columns[] = 'discriminative_efficiency';
        $headers[] = get_string('discriminative_efficiency', 'quiz_makeexam');

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->sortable(false);

        $this->column_class('s', 'numcol');
        $this->column_class('facility', 'numcol');
        $this->column_class('sd', 'numcol');
        $this->column_class('random_guess_score', 'numcol');
        $this->column_class('intended_weight', 'numcol');
        $this->column_class('effective_weight', 'numcol');
        $this->column_class('discrimination_index', 'numcol');
        $this->column_class('discriminative_efficiency', 'numcol');

        // Set up the table.
        $this->define_baseurl($reporturl->out());

        $this->collapsible(true);

        $this->set_attribute('id', 'questionmakeexam');
        $this->set_attribute('class', 'generaltable generalbox boxaligncenter');

        parent::setup();
    }

    /**
     * Generate the display of the checkbox column.
     * @param object $attempt the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_checkbox($attempt) {
        if ($attempt->attempt) {
            return '<input type="checkbox" name="attemptid[]" value="'.$attempt->attempt.'" />';
        } else {
            return '';
        }
    }

    /**
     * Generate the display of the user's full name column.
     * @param object $attempt the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_fullname($attempt) {
        $html = parent::col_fullname($attempt);
        if ($this->is_downloading() || empty($attempt->attempt)) {
            return $html;
        }

        return $html . html_writer::empty_tag('br') . html_writer::link(
                new moodle_url('/mod/quiz/review.php', array('attempt' => $attempt->attempt)),
                get_string('reviewattempt', 'quiz'), array('class' => 'reviewlink'));
    }

    /**
     * Generate the display of the attempt state column.
     * @param object $attempt the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_state($attempt) {
        if (!is_null($attempt->attempt)) {
            return quiz_attempt::state_name($attempt->state);
        } else {
            return  '-';
        }
    }

    /**
     * Generate the display of the start time column.
     * @param object $attempt the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_timestart($attempt) {
        if ($attempt->attempt) {
            return userdate($attempt->timestart, $this->strtimeformat);
        } else {
            return  '-';
        }
    }

    /**
     * Make a link to review an individual question in a popup window.
     *
     * @param string $data HTML fragment. The text to make into the link.
     * @param object $attempt data for the row of the table being output.
     * @param int $slot the number used to identify this question within this usage.
     */
    public function make_review_link($data, $attempt, $slot) {
        global $OUTPUT;

        $stepdata = $this->lateststeps[$attempt->usageid][$slot];
        $state = question_state::get($stepdata->state);

        $flag = '';
        if ($stepdata->flagged) {
            $flag = $OUTPUT->pix_icon('i/flagged', get_string('flagged', 'question'),
                    'moodle', array('class' => 'questionflag'));
        }

        $feedbackimg = '';
        if ($state->is_finished() && $state != question_state::$needsgrading) {
            $feedbackimg = $this->icon_for_fraction($stepdata->fraction);
        }

        $output = html_writer::tag('span', $feedbackimg . html_writer::tag('span',
                $data, array('class' => $state->get_state_class(true))) . $flag, array('class' => 'que'));

        $url = new moodle_url('/mod/quiz/reviewquestion.php',
                array('attempt' => $attempt->attempt, 'slot' => $slot));
        $output = $OUTPUT->action_link($url, $output,
                new popup_action('click', $url, 'reviewquestion',
                        array('height' => 450, 'width' => 650)),
                array('title' => get_string('reviewresponse', 'quiz')));

        return $output;
    }

    /**
     * Return an appropriate icon (green tick, red cross, etc.) for a grade.
     * @param float $fraction grade on a scale 0..1.
     * @return string html fragment.
     */
    protected function icon_for_fraction($fraction) {
        global $OUTPUT;

        $feedbackclass = question_state::graded_state_for_fraction($fraction)->get_feedback_class();
        return $OUTPUT->pix_icon('i/grade_' . $feedbackclass, get_string($feedbackclass, 'question'),
                'moodle', array('class' => 'icon'));
    }


    public function wrap_html_start() {
        if ($this->is_downloading() || !$this->includecheckboxes) {
            return;
        }

        $url = $this->options->get_url();
        $url->param('sesskey', sesskey());

        echo html_writer::start_tag('div', array('id' => 'tablecontainer',
                'class' => 'makeexam-tablecontainer'));

        echo html_writer::start_tag('form', array('id' => 'makeexamattemptsform',
                'method'=>'post', 'action'=>$url->out_omit_querystring()));

        echo html_writer::input_hidden_params($url);
        html_writer::end_tag('div');

    }

    public function wrap_html_finish() {
        if ($this->is_downloading() || !$this->includecheckboxes) {
            return;
        }

        $html  = '<a href="javascript:select_all_in(\'DIV\', null, \'tablecontainer\');">' .
                    get_string('selectall', 'quiz') . '</a> / ';
        $html .= '<a href="javascript:deselect_all_in(\'DIV\', null, \'tablecontainer\');">' .
                    get_string('selectnone', 'quiz') . '</a> ';
        $html .= '&nbsp;&nbsp;'.$this->submit_buttons();
        html_writer::tag('div', $html, array('id'=>'commands') );

        // Close the form.
        echo '</div>';
        echo '</form></div>';
    }

    /**
     * Output any submit buttons required by the $this->includecheckboxes form.
     */
    protected function submit_buttons() {
        global $PAGE;
        if (has_capability('mod/quiz:deleteattempts', $this->context)) {
            $PAGE->requires->event_handler('#deleteattemptsbutton', 'click', 'M.util.show_confirm_dialog',
                    array('message' => get_string('deleteattemptcheck', 'quiz')));
            return '<input type="submit" id="deleteattemptsbutton" name="delete" value="' .
                    get_string('deleteselected', 'quiz_overview') . '"/>';
        }
    }




}
