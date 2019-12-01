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
 * This file defines the quiz manual grading report class.
 *
 * @package   quiz_gradingempty
 * @copyright 2018 Enrique Castro ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/grading/gradingsettings_form.php');


/**
 * Quiz report to help teachers manually grade questions that need it.
 *
 * This report basically provides two screens:
 * - List question that might need manual grading (or optionally all questions).
 * - Provide an efficient UI to grade all attempts at a particular question.
 *
 * @copyright 2018 Enrique Castro ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_gradingempty_report extends quiz_default_report {
    const DEFAULT_PAGE_SIZE = 5;
    const DEFAULT_ORDER = 'random';

    protected $viewoptions = array();
    protected $questions;
    protected $cm;
    protected $quiz;
    protected $context;

    public function display($quiz, $cm, $course) {

        $this->quiz = $quiz;
        $this->cm = $cm;
        $this->course = $course;

        // Get the URL options.
        $slot = optional_param('slot', null, PARAM_INT);
        $grade = optional_param('grade', false, PARAM_BOOL);

        $pagesize = optional_param('pagesize', self::DEFAULT_PAGE_SIZE, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $groupid = optional_param('group', 0, PARAM_INT);

        // Assemble the options requried to reload this page.
        if($page) {
            $this->viewoptions['page'] = $page;
        }
        if ($pagesize != self::DEFAULT_PAGE_SIZE) {
            $this->viewoptions['pagesize'] = $pagesize;
        }
        if ($groupid) {
            $this->viewoptions['group'] = $groupid;
        }

        // Check permissions.
        $this->context = context_module::instance($cm->id);
        require_capability('mod/quiz:grade', $this->context);

        $page = 0;
        

        // Get the list of questions in this quiz.
        $this->questions = quiz_report_get_significant_questions($quiz);
        if ($slot && !array_key_exists($slot, $this->questions)) {
            throw new moodle_exception('unknownquestion', 'quiz_gradingempty');
        }
        
        // Process any submitted data.
        if ($data = data_submitted() && confirm_sesskey() ) {
            $this->process_submitted_data($data);
            $slot = null;
            //redirect($this->grade_question_url($slot, $questionid, $grade, $page + 1));
        }

        // Get the group, and the list of significant users.
        $this->currentgroup = $this->get_current_group($cm, $course, $this->context);
        if ($this->currentgroup == self::NO_GROUPS_ALLOWED) {
            $this->userssql = array();
        } else {
            $this->userssql = get_enrolled_sql($this->context,
                    array('mod/quiz:reviewmyattempts', 'mod/quiz:attempt'), $this->currentgroup);
        }

        $hasquestions = quiz_has_questions($quiz->id);
        /*
        $counts = null;
        if ($slot && $hasquestions) {
            // Make sure there is something to do.
            $statecounts = $this->get_question_state_summary(array($slot));
            foreach ($statecounts as $record) {
                if ($record->questionid == $questionid) {
                    $counts = $record;
                    break;
                }
            }
            // If not, redirect back to the list.
            if (!$counts || $counts->$grade == 0) {
                //redirect($this->list_questions_url(), get_string('alldoneredirecting', 'quiz_gradingempty'));
            }
        }
        */
        // Start output.
        $this->print_header_and_tabs($cm, $course, $quiz, 'grading');

        // What sort of page to display?
        if (!$hasquestions) {
            echo quiz_no_questions_message($quiz, $cm, $this->context);

        } else {
            $this->display_index();

        }
        return true;
    }

    protected function get_qubaids_condition() {

        $where = "quiza.quiz = :mangrquizid AND
                quiza.preview = 0 AND
                quiza.state = :statefinished";
        $params = array('mangrquizid' => $this->cm->instance, 'statefinished' => quiz_attempt::FINISHED);

        $usersjoin = '';
        $currentgroup = groups_get_activity_group($this->cm, true);
        $enrolleduserscount = count_enrolled_users($this->context,
                array('mod/quiz:reviewmyattempts', 'mod/quiz:attempt'), $currentgroup);
        if ($currentgroup) {
            $userssql = get_enrolled_sql($this->context,
                    array('mod/quiz:reviewmyattempts', 'mod/quiz:attempt'), $currentgroup);
            if ($enrolleduserscount < 1) {
                $where .= ' AND quiza.userid = 0';
            } else {
                $usersjoin = "JOIN ({$userssql[0]}) AS enr ON quiza.userid = enr.id";
                $params += $userssql[1];
            }
        }

        return new qubaid_join("{quiz_attempts} quiza $usersjoin ", 'quiza.uniqueid', $where, $params);
    }

    protected function load_attempts_by_usage_ids($qubaids) {
        global $DB;

        list($asql, $params) = $DB->get_in_or_equal($qubaids);
        $params[] = quiz_attempt::FINISHED;
        $params[] = $this->quiz->id;

        $attemptsbyid = $DB->get_records_sql("
                SELECT quiza.*
                FROM {quiz_attempts} quiza
                WHERE quiza.uniqueid $asql AND quiza.state = ? AND quiza.quiz = ?",
                $params);

        $attempts = array();
        foreach ($attemptsbyid as $attempt) {
            $attempts[$attempt->uniqueid] = $attempt;
        }
        return $attempts;
    }

    /**
     * Get the URL of the front page of the report that lists all the questions.
     * @param $includeauto if not given, use the current setting, otherwise,
     *      force a paricular value of includeauto in the URL.
     * @return string the URL.
     */
    protected function base_url() {
        return new moodle_url('/mod/quiz/report.php',
                array('id' => $this->cm->id, 'mode' => 'gradingempty'));
    }

    /**
     * Get the URL of the front page of the report that lists all the questions.
     * @param $includeauto if not given, use the current setting, otherwise,
     *      force a paricular value of includeauto in the URL.
     * @return string the URL.
     */
    protected function list_questions_url() {
        $url = $this->base_url();

        $url->params($this->viewoptions);
        
        return $url;
    }

    protected function format_count_for_table($counts, $empties, $state) {
        $result = $counts->$state;
        if ($counts->$state > 0) {
            $a = new stdClass();
            $a->counts = $counts->$state;
            $a->empties = $empties->$state;
            $result = get_string('statecount', 'quiz_gradingempty', $a);
        }
        return $result;
    }

    protected function display_index() {
        global $OUTPUT, $PAGE;

        if ($groupmode = groups_get_activity_groupmode($this->cm)) {
            // Groups is being used.
            groups_print_activity_menu($this->cm, $this->list_questions_url());
        }

        echo $OUTPUT->heading(get_string('questionsthatneedgrading', 'quiz_gradingempty'), 3);
        
        //$statecounts = $this->get_question_state_summary(array_keys($this->questions));
        // ecastro ULPGC, only essay questions listed
        $statecounts = array();
        foreach($this->questions as $key => $question) {
            if(strpos($question->qtype, 'essay') !== false) {
                $statecounts[] = $key;
            }
        }
        
        if($statecounts) {
            $statecounts = $this->get_question_state_summary($statecounts);
        }
        // end ecastro
        
        $url = $this->list_questions_url();
        $url->param('grade', true);
        
        $strgrade = '  '.get_string('grade', 'quiz_gradingempty');

        $data = array();
        foreach ($statecounts as $counts) {
            if ($counts->all == 0) {
                continue;
            }
            
            $empties = $this->get_empty_counts($counts); 
            
            $row = array();

            $row[] = $this->questions[$counts->slot]->number;

            $row[] = $PAGE->get_renderer('question', 'bank')->qtype_icon($this->questions[$counts->slot]->type);

            $row[] = format_string($counts->name);

            $row[] = $this->format_count_for_table($counts, $empties, 'needsgrading');

            $row[] = $this->format_count_for_table($counts, $empties, 'manuallygraded');

            $row[] = $this->format_count_for_table($counts, $empties, 'autograded');

            $url->param('slot', $counts->slot);
            $row[] = $this->format_count_for_table($counts, $empties, 'all'). html_writer::link($url, $strgrade, array('class' => 'gradetheselink'));

            $data[] = $row;
        }

        if (empty($data)) {
            echo $OUTPUT->notification(get_string('nothingfound', 'quiz_gradingempty'));
            return;
        }

        $table = new html_table();
        $table->class = 'generaltable';
        $table->id = 'questionstograde';

        $table->head[] = get_string('qno', 'quiz_gradingempty');
        $table->head[] = get_string('qtypeveryshort', 'question');
        $table->head[] = get_string('questionname', 'quiz_gradingempty');
        $table->head[] = get_string('tograde', 'quiz_gradingempty');
        $table->head[] = get_string('alreadygraded', 'quiz_gradingempty');
        $table->head[] = get_string('automaticallygraded', 'quiz_gradingempty');
        $table->head[] = get_string('total', 'quiz_gradingempty');

        $table->data = $data;
        echo html_writer::table($table);
        
        $url = $this->list_questions_url();
        $url->remove_params('slot');
        echo $OUTPUT->single_button($url, get_string('gradingempty', 'quiz_gradingempty'));
        
    }

    protected function process_submitted_data($data) {
        global $DB;
        
        if(isset($data->slot) && $data->slot) {
            $slots = array($slot);
        } else {
            $slots = $this->questions;
        }

        $transaction = $DB->start_delegated_transaction();  
        $events = array();
        foreach($slots as $slot => $q) {
        
            list($qubaids, $count) = $this->get_usage_ids_where_question_in_state($slot, null, 'needsgrading'); 
            $attempts = $this->load_attempts_by_usage_ids($qubaids);

            $count = 0;
          
            foreach($qubaids as $qubaid) {
                $quba = question_engine::load_questions_usage_by_activity($qubaid);
                $question = $quba->get_question($slot);
                $responsetemplate = trim(html_to_text($question->responsetemplate, 0, false)); 
                $response =  trim(html_to_text($quba->get_response_summary($slot), 0, false));
                
                if(!$response || ($response == $responsetemplate)) {
                    $quba->manual_grade($slot, '',  0, FORMAT_MOODLE);
                    question_engine::save_questions_usage_by_activity($quba);
                }

                // Add the event we will trigger later.
                $params = array(
                    'objectid' => $q->id,
                    'courseid' => $this->course->id,
                    'context' => context_module::instance($this->cm->id),
                    'other' => array(
                        'quizid' => $this->quiz->id,
                        'attemptid' => $attempts[$qubaid]->id,
                        'slot' => $slot,
                    )
                );
                $events[] = \mod_quiz\event\question_manually_graded::create($params);
            }
        }
        $transaction->allow_commit();

        // Trigger events for all the questions we manually marked.
        foreach ($events as $event) {
            $event->trigger();
        }
    }

    /**
     * Load information about the number of attempts at various questions in each
     * summarystate.
     *
     * The results are returned as an two dimensional array $qubaid => $slot => $dataobject
     *
     * @param array $slots A list of slots for the questions you want to konw about.
     * @return array The array keys are slot,qestionid. The values are objects with
     * fields $slot, $questionid, $inprogress, $name, $needsgrading, $autograded,
     * $manuallygraded and $all.
     */
    protected function get_question_state_summary($slots) {
        $dm = new question_engine_data_mapper();
        return $dm->load_questions_usages_question_state_summary(
                            $this->get_qubaids_condition(), $slots);
    }

    
    protected function get_empty_counts($statecount) {
        $emptycount = new stdClass();
        
        foreach(array('all', 'needsgrading', 'autograded','manuallygraded') as $state) {
            list($qubaids, $count) = $this->get_usage_ids_where_question_in_state($statecount->slot, $statecount->questionid, $state); 
            
            $count = 0;
            foreach($qubaids as $qubaid) {
                $quba = question_engine::load_questions_usage_by_activity($qubaid);
                $question = $quba->get_question($statecount->slot);
                $responsetemplate = trim(html_to_text($question->responsetemplate, 0, false)); 
                $response =  trim(html_to_text($quba->get_response_summary($statecount->slot), 0, false));
                
                if(!$response || ($response == $responsetemplate)) {
                    $count++;
                }
            }
            $emptycount->$state = $count;
    
        }
        return $emptycount;                     
    }
    
    
    /**
     * Get a list of usage ids where the question with slot $slot, and optionally
     * also with question id $questionid, is in summary state $summarystate. Also
     * return the total count of such states.
     *
     * Only a subset of the ids can be returned by using $orderby, $limitfrom and
     * $limitnum. A special value 'random' can be passed as $orderby, in which case
     * $limitfrom is ignored.
     *
     * @param int $slot The slot for the questions you want to konw about.
     * @param int $questionid (optional) Only return attempts that were of this specific question.
     * @param string $summarystate 'all', 'needsgrading', 'autograded' or 'manuallygraded'.
     * @param int $pagesize implements paging of the results. null = all.
     */
    protected function get_usage_ids_where_question_in_state($slot, 
            $questionid = null, $summarystate, $pagesize = null) {
        global $CFG, $DB;
        $dm = new question_engine_data_mapper();

        $qubaids = $this->get_qubaids_condition();

        return $dm->load_questions_usages_where_question_in_state($qubaids, $summarystate,
                $slot, $questionid, 'random', array(), 0, $pagesize);
    }
}
