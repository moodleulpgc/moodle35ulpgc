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
 * Quiz makeexam report class.
 *
 * @package   quiz_makeexam
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/makeexam/makeexam_form.php');
require_once($CFG->dirroot . '/mod/quiz/report/makeexam/makeexam_table.php');
require_once($CFG->dirroot . '/mod/examregistrar/locallib.php');

/**
 * The quiz makeexam report provides summary information about each question in
 * a quiz, compared to the whole quiz. It also provides a drill-down to more
 * detailed information about each question.
 *
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_makeexam_report extends quiz_default_report {

    /** @var string the mode this report is. */
    protected $mode;

    /** @var object the quiz context. */
    protected $context;

    /** @var object the course object. */
    protected $course;

    /** @var object the course module object. */
    protected $cm;

/*
    /** @var quiz_makeexam_table instance of table class used for main questions stats table.
    protected $table;
*/
    /** @var array of examregistrar_exams table records. These are the exams managed by this report in this course  */
    protected $exams;

    /** @var int ID of current attempt, if any, its questions are set as quiz questions */
    protected $currentattempt;


    /**
     *  Initialise various aspects of this report.
     *
     * @param string $mode
     * @param object $quiz
     * @param object $cm
     * @param object $course
     */
    protected function init($mode, $quiz, $cm, $course) {
        $this->mode = $mode;

        $this->context = context_module::instance($cm->id);

        $this->course = $course;

        $this->cm = $cm;

        $reporturl = $this->get_base_url();

        require_capability('quiz/makeexam:view', $this->context);

        //$includecheckboxes = false;
        //$this->table = new quiz_makeexam_table($quiz, $this->context, $reporturl, $includecheckboxes);

        return;
    }

    /**
     * Get the base URL for this report.
     * @return moodle_url the URL.
     */
    protected function get_base_url() {
        return new moodle_url('/mod/quiz/report.php',
                array('id' => $this->context->instanceid, 'mode' => $this->mode));
    }


    /**
     * Get the examregistrar instance to use for accessing examregistrar tables
     *
     * @param object $cm the course module object of this quiz
     * @param object $course the course settings object.
     * @return object examregistrar instance with cmid.
     */
    protected function get_examregistrar_instance($cm, $course) {
        global $DB;

        $exregcm = '';
        $examregistrar = '';
        $moduleid = $DB->get_field('modules', 'id', array('name'=>'examregistrar'), MUST_EXIST);

        $params = array('course'=>$course->id, 'module'=>$moduleid);
        //first check an instance in the same section, then any on course, if not, default
        if($cms = $DB->get_records('course_modules', $params + array('section'=>$cm->section))) {
            $exregcm = reset($cms);
        } elseif($cms = $DB->get_records('course_modules', $params)) {
            $exregcm = reset($cms);
        }

        if($exregcm) {
            $examregistrar = $DB->get_record('examregistrar', array('id'=>$exregcm->instance), '*', MUST_EXIST);
            $examregistrar->cmid = $exregcm->id;
        }else {
            $idnumber = get_config('quiz_makeexam', 'examregistrar');
            $examregistrar = $DB->get_record('examregistrar', array('primaryidnumber'=>$idnumber), '*', MUST_EXIST);
        }

        return $examregistrar;
    }

    /**
     * Get an array of exams from Examregistrar correponding to this course's exams
     * Reads examregistrar_exams to know about needed exams.
     *
     * @param object $cm the course module object of this quiz
     * @param object $course the course settings object.
     * @param int $period optional, get exams from only this period
     * @return array of attempts, including empty ones.
     */
    protected function load_exams($cm, $course, $period = 0) {
        global $CFG, $DB;

        $this->exams = array();

        $examregistrar = $this->get_examregistrar_instance($cm, $course);

        $exregid = examregistrar_get_primaryid($examregistrar);

        $annuality = examregistrar_get_annuality($examregistrar);

        $params = array('examregid'=>$exregid, 'courseid'=>$course->id);
        if($annuality) {
            $params['annuality'] = $annuality;
        }
        if($period) {
            $params['period'] = $period;
        }

        $exams = array();
        if($exams = $DB->get_records('examregistrar_exams', $params)) {
            $this->exams = $exams;
            $this->reload_attempts();
        }

        return $this->exams;
    }


    /**
     * Given an array of exams from Examregistrar correponding to this course's exams
     * Reads examregistrar examfiles and makeexam_attempts to update stored info
     *
     * @param object $cm the course module object of this quiz
     * @param object $course the course settings object.
     * @param int $period optional, get exams from only this period
     * @return array of attempts, including empty ones.
     */
    protected function reload_attempts() {
        global $CFG, $DB;

        $update = array();
        foreach($this->exams as $eid => $exam) {
            $examfiles = $DB->get_records('examregistrar_examfiles', array('examid'=>$eid));
            $exam->examfiles = $examfiles;

            $attempts = $DB->get_records('quiz_makeexam_attempts', array('course'=>$this->course->id, 'examid'=>$eid));

            // check integrity of attempt examfile (may have been deleted)
            foreach($attempts as $aid => $attempt) {
                if($attempt->examfileid && !isset($exam->examfiles[$attempt->examfileid])) {
                    //this id has been deleted deleted, update
                    $update[] = $aid;
                    $attempt->examfileid = 0;
                    $attempt->status = 0;
                    $attempts[$aid] = $attempt;
                }
            }
            $exam->attempts = $attempts;
            $this->exams[$eid] = $exam;
        }

        if($update) {
            list($insql, $params) = $DB->get_in_or_equal($update);
            $DB->set_field_select('quiz_makeexam_attempts', 'examfileid', 0, " id $insql ", $params);
            $DB->set_field_select('quiz_makeexam_attempts', 'status', 0, " id $insql ", $params);
        }

        return $this->exams;
    }


    protected function data_submitted() {

        $data = false;
        if (!empty($_POST)) {
            $data = $_POST;
        } elseif(!empty($_GET)) {
            $data = $_GET;
        }

        if($data) {
            $return = false;
            foreach($data as $key => $value) {
                if(($key != 'mode') && ($key != 'id') && ($key != 'sesskey')) {
                    $return = true;
                    break;
                }
            }
            if($return) {
                return (object)fix_utf8($data);
            }
        }

        return false;
    }


    /**
     * Process any submitted actions.
     * @param object $quiz the quiz settings.
     * @param object $cm the cm object for the quiz.
     * @param int $currentgroup the currently selected group.
     * @param object $mform the settings form
     */
    protected function process_actions($quiz, $cm, $course, $currentgroup, $mform) {
        global $CFG, $DB, $PAGE, $USER, $OUTPUT;

//         if(debugging() )  {
//             print_object($_GET);
//             print_object("-------- GET GET --------------");
//             print_object($_POST);
//             print_object("-------- POST POST --------------");
//         }

        $now = time();
        $reporturl = $this->get_base_url();
        $reviewurl = new moodle_url('/mod/quiz/review.php',  array('id' => $this->context->instanceid, 'mode' => $this->mode));

        if (empty($currentgroup)) {
            if (optional_param('delete', 0, PARAM_BOOL) && confirm_sesskey()) {
                if ($attemptids = optional_param_array('attemptid', array(), PARAM_INT)) {
                    require_capability('mod/quiz:deleteattempts', $this->context);
                    $this->delete_selected_attempts($quiz, $cm, $attemptids, $allowed);
                    redirect($redirecturl);
                }
            }
        }

        /// We have a form, a new preview/review has been requested
        if(($fromform = $mform->get_data()) && (isset($fromform->action) && ($fromform->action == 'newattempt'))) {
            if($fromform->attemptn) {
                if(!$examattempt = $DB->get_record('quiz_makeexam_attempts', array('quizid'=>$quiz->id, 'examid'=>$fromform->examid, 'attempt'=>$fromform->attemptn))) {
                    // does not exist this attempt for this exam
                    $PAGE->set_title($quiz->name);
                    $PAGE->set_heading($course->fullname);
                    $output = $PAGE->get_renderer('mod_quiz');
                    echo $output->header();
                    echo $output->notification(get_string('errornoattempt', 'quiz_makeexam', $fromform->attemptn));
                    echo $output->continue_button($reporturl);
                    echo $output->footer();
                    die;
                }
                $this->restore_quiz_from_attempt($quiz, $examattempt);
            }
            $warnings = 0;
            if(!has_capability('quiz/makeexam:anyquestions', $this->context)) {
                $quiz->questions = $this->get_quiz_questions_ids($quiz->id);
                $questions = $DB->get_records_list('question', 'id', $quiz->questions);
                $warnings = $this->check_attempt_valid_questions($questions);
            }
            if($warnings) {
                $PAGE->set_title($quiz->name);
                $PAGE->set_heading($course->fullname);
                $output = $PAGE->get_renderer('mod_quiz');
                $editurl = new moodle_url('/mod/quiz/edit.php', array('cmid'=>$cm->id));
                echo $output->header();
                echo $output->notification(get_string('generate_errors', 'quiz_makeexam').'<br />'.
                                            get_string('errorinvalidquestions', 'quiz_makeexam', $warnings));
                echo $output->continue_button($editurl);
                echo $output->footer();
                die;
            } else {
                $newattemptid = $this->start_new_attempt($quiz);
                $url = new moodle_url($reviewurl,  array('action'=>'newattempt', 'examid'=>$fromform->examid,
                                                            'name'=>$fromform->name, 'attempt'=>$newattemptid));
                if($fromform->currentattempt) {
                      $url->param('action', 'continueattempt');
                      $url->param('currentattempt', $fromform->currentattempt);
                }
                redirect($url);
            }

        } elseif(($fromform = $this->data_submitted()) && confirm_sesskey()) {
            $info = new stdClass;
            if(isset($fromform->examid) && $fromform->examid) {
                $exam = $this->exams[$fromform->examid];
                $items = array();
                list($name, $idnumber) = examregistrar_get_namecodefromid($exam->period, 'periods');
                $items[] = $idnumber;

                list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examscope);
                $items[] = $idnumber;

                $items[] = get_string('callnum', 'examregistrar').': '.$exam->callnum;

                list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examsession, 'examsessions');
                $items[] = ' ('.$idnumber.')';

                $info->exam = implode(', ', $items);
            }

            if(isset($fromform->delete) && $fromform->delete) {
                $attempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$fromform->delete), '*', MUST_EXIST);
                $info->name = $attempt->name;
                $info->num = $attempt->attempt;
                $message = get_string('delete_confirm', 'quiz_makeexam', $info);
                $strnav = get_string('deleteattempt', 'quiz_makeexam');
            }

            if(isset($fromform->submit) && $fromform->submit) {
                $attempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$fromform->submit), '*', MUST_EXIST);
                $info->name = $attempt->name;
                $info->num = $attempt->attempt;
                $message = get_string('submit_confirm', 'quiz_makeexam', $info);
                $strnav = get_string('submitattempt', 'quiz_makeexam');
            }

            if(isset($fromform->unsend) && $fromform->unsend) {
                $attempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$fromform->unsend), '*', MUST_EXIST);
                $info->name = $attempt->name;
                $info->num = $attempt->attempt;
                $message = get_string('unsend_confirm', 'quiz_makeexam', $info);
                $strnav = get_string('unsendattempt', 'quiz_makeexam');
            }

            if(isset($fromform->newattempt) && $fromform->newattempt &&
               (isset($fromform->action) && ($fromform->action == 'continueattempt')) &&
               (isset($fromform->currentattempt) && $fromform->currentattempt)) {
                $fromform->confirm = 1;
            }

            $error = false;
            if(isset($fromform->newattempt) && $fromform->newattempt &&
               ((isset($fromform->action) && ($fromform->action == 'newattempt')) || $fromform->attemptn)) {
                $message = get_string('generate_confirm', 'quiz_makeexam', $info);
                $strnav = get_string('generateexam', 'quiz_makeexam');
                //$error = $this->check_attempt_questions($quiz);
            }

            if(!isset($fromform->confirm) || !$fromform->confirm) {
                $PAGE->set_title($quiz->name);
                $PAGE->set_heading($course->fullname);
                $PAGE->navbar->add($strnav);

                $output = $PAGE->get_renderer('mod_quiz');
                $params = get_object_vars($fromform);
                $confirmurl = new moodle_url($reporturl, $params + array('confirm' => 1));
                echo $output->header();
                if($error) {
                    echo $output->notification($message, 'notifysuccess');
                    echo $output->notification($error);
                    echo $output->continue_button($reporturl);
                } else {
                    echo $output->confirm($message, $confirmurl, $reporturl);
                }
                echo $output->footer();
                die;
            } elseif(confirm_sesskey()){
                // confirmed, perform real actions

                $message = '';
                // delete
                if(isset($fromform->delete) &&  $fromform->delete) {
                    $this->delete_attempt($quiz, $fromform->delete);
                }

                // review
                if(isset($fromform->review) &&  $fromform->review) {
                    // start a new quiz attempt from stored one
                    $quizattemptid = $this->restore_saved_attempt($quiz, $fromform->review);
                    // Redirect to the attempt page.
                    $url = new moodle_url('/mod/quiz/review.php', array('id'=>$this->context->instanceid, 'mode'=>$this->mode,
                                                                        'attempt' => $quizattemptid, 'review'=>$fromform->review, 'showall'=>1));
                    redirect($url);
                }

                // setquestions
                if(isset($fromform->setquestions) &&  $fromform->setquestions) {
                    //  restore quiz questions from stored ones
                    $examattempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$fromform->setquestions), '*', MUST_EXIST);

                    // change quiz state, questions, from stored makeexam
                    $this->restore_quiz_from_attempt($quiz, $examattempt);

                    $quizattemptid = $this->restore_saved_attempt($quiz, $fromform->setquestions);

                    $eventdata = array();
                    $eventdata['objectid'] = $examattempt->id;
                    $eventdata['context'] = $this->context;
                    $eventdata['userid'] = $USER->id;
                    $eventdata['other'] = array();
                    $eventdata['other']['quizid'] = $quiz->id;
                    $eventdata['other']['examid'] = $examattempt->examid;
                    $event = \quiz_makeexam\event\exam_recalled::create($eventdata);
                    $event->trigger();

                    // Redirect to the edit page.
                    $url = new moodle_url('/mod/quiz/edit.php', array('cmid'=>$this->context->instanceid, 'mode'=>$this->mode,
                                                                        'qbanktool' => 1));
                    redirect($url);
                }

                // pdfpreview
                if(isset($fromform->pdf) &&  $fromform->pdf) {
                    // start a new quiz attempt from stored one
                    $quizattemptid = $this->restore_saved_attempt($quiz, $fromform->pdf);
                    $message = $this->generate_pdf($quiz, $quizattemptid,  $fromform->examid, $fromform->pdf, 'exam', false);
                }

                // submit
                if(isset($fromform->submit) &&  $fromform->submit) {
                    // start a new quiz attempt from stored one
                    require_capability('quiz/makeexam:submit', context_module::instance($cm->id));
                    $quizattemptid = $this->restore_saved_attempt($quiz, $fromform->submit);
                    $message = $this->submit_attempt($cm, $quiz, $course, $quizattemptid, $fromform->examid, $fromform->submit);

                    $eventdata = array();
                    $eventdata['objectid'] = $fromform->submit;
                    $eventdata['context'] = $this->context;
                    $eventdata['userid'] = $USER->id;
                    $eventdata['other'] = array();
                    $eventdata['other']['quizid'] = $quiz->id;
                    $eventdata['other']['examid'] = $fromform->examid;
                    $eventdata['other']['message'] = $message;
                    $event = \quiz_makeexam\event\exam_recalled::create($eventdata);
                    $event->trigger();
                }

                if(isset($fromform->unsend) && $fromform->unsend) {
                    // reset status to unsend
                    require_capability('mod/examregistrar:manageexams', context_course::instance($this->course->id));
                    $DB->set_field('quiz_makeexam_attempts', 'status', 0, array('id'=>$fromform->unsend));
                    $DB->set_field('quiz_makeexam_attempts', 'examfileid', 0, array('id'=>$fromform->unsend));
                }

                // generate/continue new exam attempt
                if(isset($fromform->newattempt) && $fromform->newattempt && $fromform->examid) {
                    if(!isset($fromform->currentattempt)) {
                        $fromform->currentattempt = 0;
                    }
                    $nodelete = $fromform->action == 'continueattempt' ? true : false;
                    $attemptid = $this->make_new_attempt($quiz, $fromform->examid, $fromform->name, $fromform->newattempt, $fromform->currentattempt, $nodelete);

                    $eventdata = array();
                    $eventdata['objectid'] = $attemptid;
                    $eventdata['context'] = $this->context;
                    $eventdata['userid'] = $USER->id;
                    $eventdata['other'] = array();
                    $eventdata['other']['quizid'] = $quiz->id;
                    $eventdata['other']['examid'] = $fromform->examid;
                    $eventdata['other']['continue'] = $nodelete;
                    $event = \quiz_makeexam\event\exam_created::create($eventdata);
                    $event->trigger();
                }

                if(isset($fromform->clearquiz) && $fromform->clearquiz == 1 ) {
                    $this->clear_quiz($quiz);
                    $eventdata = array();
                    $eventdata['objectid'] = $quiz->id;
                    $eventdata['context'] = $this->context;
                    $eventdata['userid'] = $USER->id;
                    $event = \quiz_makeexam\event\exam_cleared::create($eventdata);
                    $event->trigger();
                    $editurl = new moodle_url('/mod/quiz/edit.php', array('cmid'=>$cm->id));
                    redirect($editurl, get_string('cleared', 'quiz_makeexam'), 5);
                }

                if(isset($fromform->copyold) && $fromform->copyold && !optional_param('cancel', '', PARAM_ALPHA)) {
                    require_once($CFG->dirroot . '/mod/quiz/report/makeexam/copyold_form.php');
                    $mform = new quiz_makeexam_copyold_form(null, array('cmid'=>$cm->id, 'quiz'=>$quiz));
                    $strnav = get_string('copyold', 'quiz_makeexam');
                    $PAGE->set_title($quiz->name);
                    $PAGE->set_heading($course->fullname);
                    $PAGE->navbar->add($strnav);
                    $output = $PAGE->get_renderer('mod_quiz');
                    echo $output->header();

                    if(($formdata = $mform->get_data()) && confirm_sesskey()) {
                        $this->import_old_questions($formdata->copysource, $formdata->copystatus);
                        echo $output->continue_button($reporturl);
                    } else {
                        $mform->display();
                    }
                    echo $output->footer();
                    die;
                }
                // other actions
                if($message) {
                    redirect($reporturl, $message, 5);
                }
                $this->reload_attempts();
            }
        }
    }


    protected function delete_attempt($quiz, $attemptid, $updateattempt = null) {
        global $DB, $USER;

        if(is_object($updateattempt)) {
            $examattempt = clone $updateattempt;
        } else {
            $examattempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$attemptid), '*', MUST_EXIST);
        }

        $quizobj = quiz::create($quiz->id, $examattempt->userid);
        $this->delete_existing_previews($quizobj, $examattempt->userid);

        if($quizattempt = $DB->get_record('quiz_attempts', array('id'=>$examattempt->attemptid))) {
            $quizattempt->quiz = $quiz->id;
            $quizattempt->uniqueid = -($quizattempt->uniqueid);
            $DB->update_record('quiz_attempts', $quizattempt);
            quiz_delete_attempt($quizattempt, $quiz, true); // true means unconditional delete, no check for negative
        }

        //delete  makeexam qinstances
        $questions = $DB->get_records_menu('quiz_makeexam_slots', array('mkattempt'=>$attemptid), '', 'id,questionid');
        $DB->delete_records('quiz_makeexam_slots', array('mkattempt'=>$attemptid));
        $DB->delete_records('quiz_makeexam_sections', array('mkattempt'=>$attemptid));

        // unhide used questions. Check first if used in other attempts
        $unhide = array();
        foreach($questions as $qid) {
            if(!$DB->record_exists('quiz_makeexam_slots', array('questionid'=>$qid, 'inuse'=>1))) {
                $unhide[] = $qid;
            }
        }
        if($unhide) {
            list($insql, $params) = $DB->get_in_or_equal($unhide);
            $DB->set_field_select('question', 'hidden', 0, " id $insql ", $params);
        }

        $success = true;
        if(!is_object($updateattempt)) {
            if($success = $DB->delete_records('quiz_makeexam_attempts', array('id'=>$attemptid))) {
                $eventdata = array();
                $eventdata['objectid'] = $attemptid;
                $eventdata['context'] = $this->context;
                $eventdata['other'] = array();
                $eventdata['other']['quizid'] = $quiz->id;
                $eventdata['other']['examid'] = $examattempt->id;
                $event = \quiz_makeexam\event\exam_recalled::create($eventdata);
                $event->trigger();
            }
        }


        return $success;

    }

    /**
     * Delete the quiz attempts
     * @param object $quiz the quiz settings. Attempts that don't belong to
     * this quiz are not deleted.
     * @param object $cm the course_module object.
     * @param array $attemptids the list of attempt ids to delete.
     * @param array $allowed This list of userids that are visible in the report.
     *      Users can only delete attempts that they are allowed to see in the report.
     *      Empty means all users.
     */
    protected function delete_selected_attempts($quiz, $cm, $attemptids, $allowed) {
        global $DB;

        foreach ($attemptids as $attemptid) {
            $attempt = $DB->get_record('quiz_attempts', array('id' => $attemptid));
            if (!$attempt || $attempt->quiz != $quiz->id || $attempt->preview != 0) {
                // Ensure the attempt exists, and belongs to this quiz. If not skip.
                continue;
            }
            if ($allowed && !in_array($attempt->userid, $allowed)) {
                // Ensure the attempt belongs to a student included in the report. If not skip.
                continue;
            }

            $this->delete_attempt($quiz, $attemptid);
        }
    }


    /**
     * Initialise some parts of $PAGE and start output.
     *
     * @param object $cm the course_module information.
     * @param object $coures the course settings.
     * @param object $quiz the quiz settings.
     * @param string $reportmode the report name.
     */
    public function print_header_and_tabs($cm, $course, $quiz, $reportmode = 'makeexam') {
        global $PAGE, $OUTPUT;

        // Print the page header.
        $PAGE->set_title($quiz->name);
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();
        $context = context_module::instance($cm->id);
        //echo $OUTPUT->heading(format_string($quiz->name, true, array('context' => $context)));
        echo $OUTPUT->heading(get_string('createexams', 'quiz_makeexam'));
    }

    /**
     * Display the report.
     */
    public function display($quiz, $cm, $course) {
        global $CFG, $DB, $OUTPUT, $PAGE;

        $this->init('makeexam', $quiz, $cm, $course);

        $reporturl = $this->get_base_url();

        // Find out current groups mode.
        $currentgroup = $this->get_current_group($cm, $course, $this->context);

        // load data
        $this->load_exams($cm, $course);
        $questions = $this->get_quiz_questions_ids($quiz->id);
        unset($quizobj);

        $mform = new quiz_makeexam_settings_form($reporturl, array('exams'=>$this->exams,
                                                                   'quiz'=>$quiz,
                                                                   'questions'=>$questions,
                                                                   'current'=>$this->get_current_attempt($quiz)));

        $this->install_official_tags(); // just in case not installed yet

        // Process any submitted actions in the report.
        //Any optional params & action goes inside
        $this->process_actions($quiz, $cm, $course, $currentgroup, $mform);

        $output = $PAGE->get_renderer('mod_quiz');
        $PAGE->set_pagelayout('incourse');

        // Now starts output
        $this->print_header_and_tabs($cm, $course, $quiz, 'makeexam');

        if(!$this->exams) {
            echo $output->heading(get_string('nothingtodisplay'));
            return true;
        }

        if (groups_get_activity_groupmode($cm)) {
            groups_print_activity_menu($cm, $reporturl->out());
        }


        // On-screen display of report.
        $mform->display();

        // new quiz reset button
        echo $this->print_clearquiz_button($quiz, $cm, $course);

        // new copy questions button
        echo $this->print_old_questions_form($cm);

        echo $output->container('', 'clearfix');

        /// TODO here can be placed options
        /// TODO options = period select form

        foreach($this->exams as $exam) {
            echo $output->container_start(' examcoursereview'  );
                echo $this->print_exam_header($exam);
                echo $this->print_exam_attempts($cm, $quiz, $exam);
            echo $output->container_end();
        }

        return true;
    }


    /**
     * Prints header with exam identification data: Period, scope, call, session,
     *
     * @param object $exam the exam (single period, scope, call) being printed
     * @param object $course the course settings object.
     */
    protected function print_exam_header($exam) {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_quiz');

        $items = array();
/*
        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->annuality);
        $items[] = get_string('annualityitem', 'examregistrar').': '.$name.' ('.$idnumber.')';

        $items[] = get_string('programme', 'examregistrar').': '.$exam->programme;
*/
        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->period, 'periods');
        $items[] = get_string('perioditem', 'examregistrar').': '.$name.' ('.$idnumber.')';

        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examscope);
        $items[] = get_string('scopeitem', 'examregistrar').': '.$name.' ('.$idnumber.')';

        $items[] = get_string('callnum', 'examregistrar').': '.$exam->callnum;

        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examsession, 'examsessions');
        $items[] = get_string('examsessionitem', 'examregistrar').': '.$name.' ('.$idnumber.')';

        echo $output->heading(implode('; ', $items), 3, ' makeexam_examheader'  );
    }

    /**
     * Prints the table of exam attempts existing for this exam
     *
     * @param object $cm the course module object of this quiz
     * @param object $quiz the quiz instance this Make Exam is called from
     * @param object $exam the exam (single period, scope, call) being printed
     */
    protected function print_exam_attempts($cm, $quiz, $exam) {
        global $CFG, $DB, $PAGE;

        $output = $PAGE->get_renderer('mod_quiz');
        $reporturl = $this->get_base_url();

        if($exam->attempts) {
            $table = new html_table();
            $table->attributes = array('class'=>'flexible makeexamreviewtable' );
            $tableheaders = array(get_string('attempt', 'quiz_makeexam'),
                                  get_string('generatinguser', 'quiz_makeexam'),
                                  get_string('questions', 'quiz'),
                                    get_string('status', 'quiz_makeexam'),
                                    get_string('attempts', 'examregistrar'),
                                    get_string('statereview', 'examregistrar'),
                                    get_string('action'),

                                    );
            $table->head = $tableheaders;
            $table->colclasses = array('colattempt', 'colgeneratinguser', 'colquestions', 'colstatus', 'colattempts', 'colstatereview', 'colqaction');

            $strdelete  = get_string('delete');
            $strsent    = get_string('sent', 'quiz_makeexam');
            $strunsent  = get_string('unsent', 'quiz_makeexam');
            $strunsend  = get_string('unsend', 'quiz_makeexam');
            $strsubmit  = get_string('submit', 'quiz_makeexam');
            $strnew     = get_string('newattempt', 'quiz_makeexam');
            $strpdf     = get_string('pdfpreview', 'quiz_makeexam');
            $strgoexreg = get_string('gotoexamreg', 'quiz_makeexam');
            $strgoattempt = get_string('setquestions', 'quiz_makeexam');

            $strapproved = get_string('approved', 'examregistrar');
            $strrejected = get_string('rejected', 'examregistrar');
            $strexamfile = get_string('examfile', 'examregistrar');

            $cansubmit = has_capability('quiz/makeexam:submit', context_module::instance($cm->id));
            $candelete = has_capability('quiz/makeexam:delete', context_module::instance($cm->id));
            $nochecklimit = has_capability('quiz/makeexam:nochecklimit', context_module::instance($cm->id));
            $canuseany = has_capability('quiz/makeexam:anyquestions', context_module::instance($cm->id));
            $canmanage = has_capability('mod/examregistrar:manageexams', context_course::instance($this->course->id));

            foreach($exam->attempts as $attempt) {
                $celln = $attempt->attempt;
                $user = $DB->get_record('user', array('id'=>$attempt->userid), get_all_user_name_fields(true));
                $url = new moodle_url('/user/view.php', array('id'=>$attempt->userid, 'course'=>$this->course->id));
                $cellgeneratedby = html_writer::link($url, fullname($user));
                $cellgeneratedby .= '<br />'. userdate($attempt->timemodified, get_string('strftimerecent'));

                list($numquestions, $invalid, $errors) = $this->check_attempt_questions($quiz, $attempt);
                $cellquestions = $numquestions;
                if($invalid OR $errors) {
                    $content = '';
                    if($invalid) {
                        $content .= $invalid;
                    }
                    if($errors) {
                        if($content) {
                            $content .= '<br />';
                        }
                        $content .= $errors;
                    }
                    $cellquestions .= '<br />'.print_collapsible_region($content, ' error ', 'showhideerror_'.$attempt->id,
                                                                        get_string('generate_errors','quiz_makeexam'), 'examattempterror_'.$attempt->id, true, true);
                }

                if($attempt->status) {
                    $icon = $output->pix_icon('i/completion-manual-enabled', $strsent, 'moodle', array('class'=>'icon', 'title'=>$strsent));
                } else {
                    $icon = $output->pix_icon('i/completion-manual-n', $strunsent, 'moodle', array('class'=>'icon', 'title'=>$strunsent));
                }
                $cellstatus = $icon;

                $url = new moodle_url($reporturl,  array('review'=>$attempt->id, 'confirm'=>1, 'sesskey'=>sesskey()));
                $name = html_writer::link($url, $attempt->name.' ('.
                                          userdate($attempt->timecreated, get_string('strftimerecent')).') ');
                $url = new moodle_url($reporturl,  array('examid'=>$attempt->examid, 'pdf'=>$attempt->id, 'confirm'=>1, 'sesskey'=>sesskey()));
                $icon = new pix_icon('f/pdf-32', $strpdf, 'moodle', array('class'=>'iconlarge', 'title'=>$strpdf));
                $cellattempt = $name.'&nbsp;   &nbsp;'.$output->action_icon($url, $icon);
                if($attempt->timesubmitted) {
                    $cellattempt .= '<br />'.get_string('sent', 'examregistrar').': '.userdate($attempt->timesubmitted, get_string('strftimedaydatetime'));
                }

                // if ANY examfile for this exmid is approved, or sent without resolution then no more exam submitting allowed
                foreach($exam->examfiles as $item) {
                    if(($item->status != EXAM_STATUS_CREATED) && ($item->status != EXAM_STATUS_REJECTED) ) {
                        $cansubmit = false;
                    }
                }

                $attempt->reviewstatus = $attempt->examfileid ? $exam->examfiles[$attempt->examfileid]->status : 0;

                $icon = '';
                switch($attempt->reviewstatus) {
                    case EXAM_STATUS_SENT       : $icon = $output->pix_icon('sent', $strsent, 'mod_examregistrar', array('class'=>'iconlarge', 'title'=>$strsent));
                                                    break;
                    case EXAM_STATUS_WAITING    : $icon = $output->pix_icon('waiting', $strsent, 'mod_examregistrar', array('class'=>'iconlarge', 'title'=>$strsent));
                                                    break;
                    case EXAM_STATUS_REJECTED   : $icon = $output->pix_icon('rejected', $strrejected, 'mod_examregistrar', array('class'=>'icon', 'title'=>$strrejected));
                                                  $time = $exam->examfiles[$attempt->examfileid]->timerejected;
                                                  $cellattempt .= '<br />'.get_string('rejected', 'examregistrar').': '.userdate($time, get_string('strftimedaydatetime'));
                                                    break;
                    case EXAM_STATUS_APPROVED   :
                    case EXAM_STATUS_VALIDATED  : $icon = $output->pix_icon('approved', $strapproved, 'mod_examregistrar', array('class'=>'iconlarge', 'title'=>$strapproved));
                                                  $time = $exam->examfiles[$attempt->examfileid]->timeapproved;
                                                  $cellattempt .= '<br />'.get_string('approved', 'examregistrar').': '.userdate($time, get_string('strftimedaydatetime'));
                                                    break;
                }
                $cellstatereview = $icon;

                $actions = array();
                if($candelete && !$attempt->reviewstatus >= EXAM_STATUS_APPROVED) {
                    $icon = new pix_icon('t/delete', $strdelete, 'moodle', array('class'=>'iconsmall', 'title'=>$strdelete));
                    $url = new moodle_url($reporturl, array('delete'=>$attempt->id, 'examid'=>$attempt->examid, 'sesskey'=>sesskey()));
                    $actions[] = $output->action_icon($url, $icon);
                }
                if($cansubmit && $attempt->status == 0 && (!$invalid || $canuseany) && (!$errors || $nochecklimit) ) {
                    $icon = new pix_icon('i/completion-auto-pass', $strsubmit, 'moodle', array('class'=>'iconlarge', 'title'=>$strsubmit));
                    $url = new moodle_url($reporturl, array('submit'=>$attempt->id, 'examid'=>$attempt->examid, 'sesskey'=>sesskey()));
                    $actions[] = $output->action_icon($url, $icon);
                }

                $examregistrar = $this->get_examregistrar_instance($cm, $this->course);
                if($attempt->examfileid && isset($examregistrar->cmid)) {
                    $icon = new pix_icon('t/contextmenu', $strgoexreg, 'moodle', array('class'=>'icon', 'title'=>$strgoexreg));
                    $url = new moodle_url('/mod/examregistrar/view.php', array('id'=>$examregistrar->cmid, 'tab'=>'review', 'period'=>$exam->period, 'sesskey'=>sesskey()));
                    $actions[] = $output->action_icon($url, $icon);
                }

                $icon = new pix_icon('i/reload', $strgoattempt, 'moodle', array('class'=>'icon', 'title'=>$strgoattempt));
                $url = new moodle_url($reporturl, array('setquestions'=>$attempt->id, 'confirm'=>1, 'sesskey'=>sesskey()));
                $actions[] = $output->action_icon($url, $icon);

                if($canmanage && $attempt->status) {
                    $icon = new pix_icon('i/completion-manual-n', $strunsend, 'moodle', array('class'=>'iconsmall', 'title'=>$strunsend));
                    $url = new moodle_url($reporturl, array('unsend'=>$attempt->id, 'examid'=>$attempt->examid, 'sesskey'=>sesskey()));
                    $actions[] = $output->action_icon($url, $icon);
                }

                $cellaction = implode('&nbsp;  ', $actions);

                $row = new html_table_row(array($celln, $cellgeneratedby, $cellquestions, $cellstatus, $cellattempt, $cellstatereview, $cellaction));
                $table->data[] = $row;
            }
            echo html_writer::table($table);
        }
    }



    /**
     * Creates a new attempt objecto to use as Exam preview
     *
     * @param object $quizobj quiz Class object
     * @param int $userid the ID of the user the attempts belong to
     */
    protected function delete_existing_previews(quiz $quizobj, $userid = 0) {
        global $DB, $USER;

        if(!$userid) {
            $userid = $USER->id;
        }

        // To force the creation of a new preview, we mark the current attempt (if any)
        // as finished. It will then automatically be deleted below.
        $DB->set_field('quiz_attempts', 'state', quiz_attempt::FINISHED,
                array('quiz' => $quizobj->get_quizid(), 'userid' => $userid));

        // Look for an existing attempt.
        $attempts = quiz_get_user_attempts($quizobj->get_quizid(), $userid, 'all', true);
        $lastattempt = end($attempts);
        while ($lastattempt && $lastattempt->preview) {
            // check if existing preview is a normal one or a makeexam preview
            if($DB->record_exists('quiz_attempts', array('uniqueid'=>-$lastattempt->uniqueid))) {
                //this is a makeexam preview, just delete it
                $DB->delete_records('quiz_attempts', array('uniqueid'=>$lastattempt->uniqueid));
            }
            $lastattempt = array_pop($attempts);
        }

        // Delete any previous preview attempts belonging to this user.
        // This deletes question_usages
        quiz_delete_previews($quizobj->get_quiz(), $userid);

        return $lastattempt;
    }


    /**
     * Creates a new attempt objecto to use as Exam preview
     *
     * @param object $cm the course module object of this quiz
     */
    protected function start_new_attempt($quiz, $userid = 0) {
        global $CFG, $DB, $PAGE, $USER;

        if(!$userid) {
            $userid = $USER->id;
        }
        $quizobj = quiz::create($quiz->id, $userid);
        // This script should only ever be posted to, so set page URL to the view page.
        $PAGE->set_url($quizobj->view_url());

        // Check login and sesskey.
        require_login($this->course, false, $quizobj->get_cm());
        $PAGE->set_heading($this->course->fullname);

        // If no questions have been set up yet redirect to edit.php or display an error.
        if (!$quizobj->has_questions()) {
            if ($quizobj->has_capability('mod/quiz:manage')) {
                redirect($quizobj->edit_url());
            } else {
                print_error('cannotstartnoquestions', 'quiz', $quizobj->view_url());
            }
        }

        // Check capabilities.
        if (!$quizobj->is_preview_user()) {
            $quizobj->require_capability('mod/quiz:manage');
        } else {
            $quizobj->require_capability('quiz/makeexam:submit');
        }

        $lastattempt = $this->delete_existing_previews($quizobj);
        $attemptnumber = 1;
        if($lastattempt) {
            $attemptnumber = $lastattempt->attempt + 1;
        }
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        $timenow = time(); // Update time now, in case the server is running really slowly.
        $attempt = quiz_create_attempt($quizobj, $attemptnumber, $lastattempt, $timenow, $quizobj->is_preview_user());
        $attempt = quiz_start_new_attempt($quizobj, $quba, $attempt, $attemptnumber, $timenow);
        $attempt->preview = 1;
        $attempt = quiz_attempt_save_started($quizobj, $quba, $attempt);
        $attemptobj = quiz_attempt::create($attempt->id);
        $attemptobj->process_finish($timenow+1, true);
        return $attempt->id;
    }


    protected function check_attempt_valid_questions($questions) {
        $config = get_config('quiz_makeexam');

        $validquestions = $config->validquestions;
        if($validquestions) {
            $validquestions = explode(',', $validquestions);
        } else {
            $validquestions = array();
        }

        $warnings = 0;
        foreach($questions as $qid => $question) {
            if(!in_array($question->qtype, $validquestions)) {
                $warnings +=1;
            }
        }

        return $warnings;
    }

    protected function check_attempt_questions($quiz, $attempt) {
        global $DB, $USER;

        $config = get_config('quiz_makeexam');

        $success = false;
        $info = new stdClass;

        $errors = array();
        $invalid = false;

        $questions = $DB->get_records_list('question', 'id', explode(',', $attempt->questions));

        $quizobj = quiz::create($quiz->id, $USER->id);

        $warning = $this->check_attempt_valid_questions($questions);

        // Only consider true questions, not descriptions or other non graded
        foreach($questions as $qid => $question) {
            if($question->defaultmark == 0) {
                unset($questions[$qid]);
            }
        }

        if($warning) {
            $invalid = get_string('error_invalidquestions', 'quiz_makeexam', $warning);
        }

        $numquestions = count($questions);
        if($config->numquestions && ($config->numquestions != $numquestions)) {
            $info->confignum = $config->numquestions;
            $info->num = $numquestions;
            $errors[] = get_string('error_numquestions', 'quiz_makeexam', $info);
        }

        if($config->categorysearch) {
            switch($config->contextlevel) {
                case CONTEXT_SYSTEM     : $context = context_system::instance();
                                            break;
                case CONTEXT_COURSECAT  : $context = context_coursecat::instance($quizobj->get_course()->category);
                                            break;
                case CONTEXT_COURSE     : $context = context_course::instance($quiz->course);
                                            break;
                case CONTEXT_MODULE     : $context = $quizobj->get_context();
            }
            $select = ' contextid = :contextid AND '.$DB->sql_like('name', ':pattern');
            if($config->excludesubcats) {
                $select .= ' AND parent = 0 ';
            }
            if($categories = $DB->get_records_select('question_categories', $select, array('contextid'=>$context->id, 'pattern'=>$config->categorysearch))) {
                $categorysums = array_combine(array_keys($categories), array_fill(0, count($categories), 0));
                $othercategories = array();
                foreach($questions as $question) {
                    if(array_key_exists($question->category, $categorysums)) {
                        $categorysums[$question->category] += 1;
                    } else {
                        if(!isset($othercategories[$question->category])) {
                            $othercategories[$question->category] = 0;
                        }
                        $othercategories[$question->category] += 1;
                    }
                }
                foreach($categorysums as $catid => $num) {
                    $used = true;
                    if($config->excludeunused) {
                        $used = $DB->record_exists_select('question', ' category = ? AND (length > 0) ', array($catid));
                    }
                    if($used && $config->questionspercategory && ($num < $config->questionspercategory)) {
                        $info->confignum = $config->questionspercategory;
                        $info->num = $num;
                        $info->name = $categories[$catid]->name;
                        $errors[] = get_string('error_percategory', 'quiz_makeexam', $info);
                    }
                }
                if($othercategories) {
                    $num = array_sum($othercategories);
                    $errors[] = get_string('error_othercategories', 'quiz_makeexam', $num);
                }
            }
        }

        if($errors) {
            $success = get_string('generate_errors','quiz_makeexam').'<br />'.implode('<br />', $errors);
        }
        return array($numquestions, $invalid, $success);
    }


    protected function make_new_attempt($quiz, $examid, $name, $newattemptid, $currentattempt = 0, $nodelete = false) {
        global $DB, $USER;

        $now = time();
        $maxattempt = 0;
        $newid = 0;
        $oldattempt = 0;
        if($attempts = $DB->get_records_menu('quiz_makeexam_attempts', array('quizid'=>$quiz->id, 'examid'=>$examid), ' attempt DESC', 'id, attempt', 0, 1)) {
            $maxattempt= reset($attempts);
        }
        $quizattempt = $DB->get_record('quiz_attempts', array('id'=>$newattemptid), '*', MUST_EXIST);

        // prevents bug by having no questions in quiz_attempt
        if(!$questions = $this->attempt_real_questions($quiz, $quizattempt)) {
            print_error('noquestionsinquiz', 'quiz', $this->get_base_url());
            return false;
        }
        if($currentattempt) {
            $examattempt = $DB->get_record('quiz_makeexam_attempts', array('quizid'=>$quiz->id, 'id'=>$currentattempt), '*', MUST_EXIST);
            $oldattempt = clone $examattempt;
            $examattempt->attemptid = $quizattempt->id;
            $examattempt->questions = $questions; //$this->attempt_real_questions($quiz, $quizattempt); // $quiz->questions;
            $examattempt->timemodified = $now;
            $examattempt->userid = $USER->id;
            if($DB->update_record('quiz_makeexam_attempts', $examattempt)) {
                $newid = $examattempt->id;
                if(!$nodelete) {
                    $this->delete_attempt($quiz, $currentattempt, $oldattempt);
                }
            }
        } else {
            $examattempt = new stdClass;
            $examattempt->course = $quiz->course;
            $examattempt->quizid = $quiz->id;
            $examattempt->attemptid = $quizattempt->id;
            $examattempt->questions = $questions; //$this->attempt_real_questions($quiz, $quizattempt); // $quiz->questions;
            $examattempt->attempt = $maxattempt + 1;
            if(!$name) {
                $name = get_string('attemptn', 'quiz_makeexam', $examattempt->attempt);
            }
            $examattempt->name = $name;
            $examattempt->userid = $USER->id;
            $examattempt->status = 0;
            $examattempt->examid = $examid;
            $examattempt->examfileid = 0;
            $examattempt->timecreated = $now;
            $examattempt->timesubmitted = 0;
            $examattempt->timemodified = $now;
            $newid = $DB->insert_record('quiz_makeexam_attempts', $examattempt);
        }

        if($newid) {
            // save quiz question instances
            $this->save_quiz_sections_slots($quiz, $quizattempt, $newid);
            // now update quiz_attempts
            $quizattempt->quiz = -abs($quiz->id);
            $quizattempt->uniqueid = -abs($quizattempt->uniqueid);
            $quizattempt->attempt = $newid;
            // check if previous versions and delete
            $select = " id <> :id AND quiz = :quiz AND userid = :user AND attempt = :attempt  ";
            if($quizattempts = $DB->get_records_select('quiz_attempts', $select, array('id'=>$quizattempt->id, 'quiz'=>$quizattempt->quiz,
                                                                                       'user'=>$quizattempt->userid, 'attempt'=>$quizattempt->attempt))) {
                foreach($quizattempts as $qattempt) {
                    $qattempt->quiz = abs($qattempt->quiz);
                    $qattempt->uniqueid = abs($qattempt->uniqueid);
                    $DB->update_record('quiz_attempts', $qattempt);
                    quiz_delete_attempt($qattempt, $quiz, true); // true means unconditional delete, no check for negative
                }

            }
            $success = $DB->update_record('quiz_attempts', $quizattempt);

            // now we can set questions as used, HIDDEN
            list($insql, $params) = $DB->get_in_or_equal(explode(',',$examattempt->questions));
            $DB->set_field_select('question', 'hidden', 1, " id $insql ", $params);

            // now we delete quiz questions to prevent teachers to repeat the exam inadvertently
            $trans = $DB->start_delegated_transaction();
            $DB->delete_records('quiz_slots', array('quizid'=>$quiz->id));
            $DB->delete_records('quiz_sections', array('quizid'=>$quiz->id));
            $firstsection = new stdClass();
            $firstsection->quizid = $quiz->id;
            $firstsection->firstslot = 1;
            $firstsection->shufflequestions = 0;
            $DB->insert_record('quiz_sections', $firstsection);
            $trans->allow_commit();
            quiz_update_sumgrades($quiz);
        }
        return $newid;
    }


    public function get_quiz_questions_ids($quizid) {
        global $DB;

        return $DB->get_records_menu('quiz_slots', array('quizid' => $quizid), 'slot ASC', 'slot, questionid');

    }


    protected function attempt_real_questions($quiz, $quizattempt) {
        global $DB;

        $quizquestions = $this->get_quiz_questions_ids($quiz->id);  //explode(',',$quiz->questions);

        $questions = $DB->get_records_menu('question_attempts', array('questionusageid'=>abs($quizattempt->uniqueid)), 'slot ASC ', 'slot,questionid');

        $newquestions = array();
        $slot = 0;
        foreach($quizquestions as $qid) {
            if(!$qid) {
                $newquestions[] = $qid;
            } else {
                $slot += 1;
                $newquestions[] = $questions[$slot];
            }
        }

        return implode(',', $newquestions);
    }


    protected function save_quiz_sections_slots($quiz, $quizattempt, $examattemptid) {
        global $DB;

        $quizquestions = $this->get_quiz_questions_ids($quiz->id); //$quizquestions = explode(',',$quiz->questions);

        $questions = $DB->get_records_menu('question_attempts', array('questionusageid'=>$quizattempt->uniqueid), 'slot ASC ', 'slot,questionid');

        $newquestions = array();
        $slot = 0;
        foreach($quizquestions as $qid) {
            if($qid) {
                $slot += 1;
                $newquestions[$qid] = $questions[$slot];
            }
        }
        if($slots = $DB->get_records('quiz_slots', array('quizid'=>$quiz->id))) {
            $mkslots = $DB->get_records_menu('quiz_makeexam_slots', array('quizid'=>$quiz->id, 'mkattempt'=>$examattemptid), 'slot', 'id,slot');
            foreach($slots as $slot) {
                if(isset($newquestions[$slot->questionid])) {
                    $slot->questionid = $newquestions[$slot->questionid];
                    $slot->mkattempt = $examattemptid;
                    if(!$mkslot = $DB->get_record('quiz_makeexam_slots', array('quizid'=>$quiz->id, 'mkattempt'=>$examattemptid, 'questionid'=>$slot->questionid))) {
                        unset($slot->id);
                        $newid = $DB->insert_record('quiz_makeexam_slots', $slot);
                    } else {
                        $mkslot->inuse = 1;
                        $mkslot->slot = $slot->slot;
                        $DB->update_record('quiz_makeexam_slots', $mkslot);
                        unset($mkslots[$mkslot->id]);
                    }
                }
            }
            if($mkslots) {
                $DB->delete_records_list('quiz_makeexam_slots', 'id', array_keys($mkslots));
            }
        }
        if($sections = $DB->get_records('quiz_sections', array('quizid'=>$quiz->id))) {
            $mksections = $DB->get_records_menu('quiz_makeexam_sections', array('quizid'=>$quiz->id, 'mkattempt'=>$examattemptid), 'firstslot', 'id,firstslot');
            foreach($sections as $section) {
                $section->mkattempt = $examattemptid;
                if(!$mksection = $DB->get_record('quiz_makeexam_sections', array('quizid'=>$quiz->id, 'mkattempt'=>$examattemptid, 'firstslot'=>$section->firstslot))) {
                    unset($section->id);
                    $newid = $DB->insert_record('quiz_makeexam_sections', $section);
                } else {
                    $mksection->inuse = 1;
                    $mksection->firstslot = $section->firstslot;
                    $mksection->heading = $section->heading;
                    $mksection->shufflequestions = $section->shufflequestions;
                    $DB->update_record('quiz_makeexam_sections', $mksection);
                    unset($mksections[$mksection->id]);
                }
            }
            if($mksections) {
                $DB->delete_records_list('quiz_makeexam_ssections', 'id', array_keys($mksections));
            }
        }
    }

    protected function install_official_tags() {
        global $CFG;

        // install official tags
        require_once($CFG->dirroot . '/tag/lib.php');
        $tags[] = get_string('tagvalidated', 'quiz_makeexam');
        $tags[] = get_string('tagrejected', 'quiz_makeexam');
        $tags[] = get_string('tagunvalidated', 'quiz_makeexam');
        
        $tags = core_tag_tag::create_if_missing(1, $tags, true);
    }

    protected function clear_quiz($quiz) {
        global $DB;

        if($attempts = $DB->get_records('quiz_makeexam_attempts', array('quizid'=>$quiz->id,'currentattempt'=>1))) {
            foreach($attempts as $attempt) {
                $quizobj = quiz::create($quiz->id, $attempt->userid);
                $this->delete_existing_previews($quizobj, $attempt->userid);
            }
            $DB->set_field('quiz_makeexam_attempts', 'currentattempt', 0, array('quizid'=>$quiz->id));
            $this->currentattempt = 0;
        }
        // now we delete quiz questions to prevent teachers to repeat the exam inadvertently
        $trans = $DB->start_delegated_transaction();
        $DB->delete_records('quiz_slots', array('quizid'=>$quiz->id));
        $DB->delete_records('quiz_sections', array('quizid'=>$quiz->id));
        $firstsection = new stdClass();
        $firstsection->quizid = $quiz->id;
        $firstsection->firstslot = 1;
        $firstsection->shufflequestions = 0;
        $DB->insert_record('quiz_sections', $firstsection);
        $trans->allow_commit();
        quiz_update_sumgrades($quiz);
    }

    protected function set_current_attempt($quiz, $attemptid) {
        global $DB;

        $id = 0;
        $DB->set_field('quiz_makeexam_attempts', 'currentattempt', 0, array('quizid'=>$quiz->id));
        $DB->set_field('quiz_makeexam_attempts', 'currentattempt', 1, array('quizid'=>$quiz->id, 'id'=>$attemptid));
        if($current = $DB->get_record('quiz_makeexam_attempts', array('quizid'=>$quiz->id, 'currentattempt'=> 1))) {
            if(!is_array($current)) {
                $id = $current->id;
            } else {
                $DB->set_field('quiz_makeexam_attempts', 'currentattempt', 0, array('quizid'=>$quiz->id));
            }
        }
        $this->currentattempt = $id;
        return $this->currentattempt;
    }

    protected function get_current_attempt($quiz) {
        global $DB;

        $id = 0;
        if($current = $DB->get_record('quiz_makeexam_attempts', array('quizid'=>$quiz->id, 'currentattempt'=> 1))) {
            $id = $current->id;
        }
        $this->currentattempt = $id;
        return $this->currentattempt;
    }

    protected function restore_quiz_from_attempt($quiz, $examattempt) {
        global $DB;

        if(!is_object($examattempt)) {
            $examattempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$examattempt), '*', MUST_EXIST);
        }

        // eliminate existing previews in current quiz state
        $quizobj = quiz::create($quiz->id, $examattempt->userid);
        $this->delete_existing_previews($quizobj, $examattempt->userid);

        // change quiz state, questions, from stored makeexam
        $questions = explode(',', $examattempt->questions);


        $trans = $DB->start_delegated_transaction();
        $slotnum = 1;
        $slots = $DB->get_records('quiz_makeexam_slots', array('quizid'=>$quiz->id, 'mkattempt'=>$examattempt->id), 'slot ASC');
        $slotobj = new stdClass();
        $slotobj->quiz = $quiz->id;
        $slotobj->mkattempt = $examattempt->id;
        $slotobj->inuse = 1;
        $slotobj->page = 1;
        $slotobj->requireprevious = 0;
        $slotobj->maxmark = 1.00000;
        $page = 1;

        foreach($questions as $qid) {
            if($slot = $DB->get_record('quiz_makeexam_slots', array('quizid'=>$quiz->id, 'mkattempt'=>$examattempt->id, 'questionid'=>$qid))) {
                $slot->slot = $slotnum;
                $DB->update_record('quiz_makeexam_slots', $slot);
                unset($slots[$slot->id]);
                $page = $slot->page;
            } else {
                $slotobj->slot = $slotnum;
                $slotobj->page = $page;
                $slotobj->questionid = $qid;
                $slotid = $DB->insert_record('quiz_makeexam_slots', $slotobj);
            }
            $slotnum += 1;
        }
        if($slots) {
            $DB->delete_records_list('quiz_makeexam_slots', 'id', array_keys($slots));
        }
        $trans->allow_commit();


        $deletes = array();
        $trans = $DB->start_delegated_transaction();
        $DB->delete_records('quiz_slots', array('quizid'=>$quiz->id));
        if($slots = $DB->get_records('quiz_makeexam_slots', array('quizid'=>$quiz->id, 'mkattempt'=>$examattempt->id), 'slot ASC')) {
            foreach($slots as $slot) {
                if(in_array($slot->questionid, $questions)) {
                    unset($slot->id);
                    $newid = $DB->insert_record('quiz_slots', $slot);
                } else {
                    $deletes[$slot->id] = $slot->id;
                }
            }
        }
        if($deletes) {
            $DB->delete_records_list('quiz_makeexam_slots', 'id', $deletes);
        }

        $DB->delete_records('quiz_sections', array('quizid'=>$quiz->id));
        if($sections = $DB->get_records('quiz_makeexam_sections', array('quizid'=>$quiz->id, 'mkattempt'=>$examattempt->id), 'firstslot ASC')) {
            foreach($sections as $section) {
                unset($section->id);
                $newid = $DB->insert_record('quiz_sections', $section);
            }
        }
        $trans->allow_commit();

        quiz_update_sumgrades($quiz);
        $this->set_current_attempt($quiz, $examattempt->id);
    }


    protected function restore_saved_attempt($quiz, $examattemptid) {
        global $DB;

        $examattempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$examattemptid), '*', MUST_EXIST);
        $attempt = $DB->get_record('quiz_attempts', array('id'=>$examattempt->attemptid));
        if(!$attempt) {
            $newattemptid = $this->start_new_attempt($quiz);
            $this->make_new_attempt($quiz, $examattempt->examid, $examattempt->name, $newattemptid, $examattempt->id, true);
            $attempt = $DB->get_record('quiz_attempts', array('id'=>$newattemptid));
        }

        // change quiz state, questions, from stored makeexam
        $this->restore_quiz_from_attempt($quiz, $examattempt);

        // We prepare a preview from stored attempt and restore quiz questions
        $attempt->quiz = $examattempt->quizid;
        $attempt->uniqueid = -($attempt->uniqueid);
        $oldattemptid = $attempt->id;
        $attempt->id = null;
        $attemptid = $DB->insert_record('quiz_attempts', $attempt);

        return $attemptid;
    }


    /**
     * Generate a PDF file from current attempt. Sends/store as indicated
     * @param object $quiz the quiz settings.
     * @param int $quizattemptid ID of quiz_attempt table entry
     * @param int $examid ID of Exam_ table entry
     * @param int $examattemptid ID of quiz_makeexam_attempt table entry
     * @param string $type the content of the PDF generated should included questions && correct answers & feedback or keys
     * @param bool $store If the PDF is sent to browser or stored in moodle files
     * @return mixed true or files as string
     */
    protected function generate_pdf($quiz, $quizattemptid, $examid , $examattemptid, $type = 'exam', $store = false) {
        global $CFG, $DB, $PAGE, $USER;
        require_once($CFG->dirroot.'/tag/lib.php');
        require_once($CFG->dirroot.'/local/ulpgccore/lib.php');
        require_once('pdf.class.php');

        //$examattempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$examattemptid), '*', MUST_EXIST);
        //$exam = $DB->get_record('examregistrar_exams', array('id'=>$examattempt->examid), '*', MUST_EXIST);

        $exam = $this->exams[$examid];
        $examattempt = $this->exams[$examid]->attempts[$examattemptid];

        if(!$exam || !$examattempt) {
            return get_string('noexamorattempt', 'quiz_makeexam');
        }

        $attemptobj = quiz_attempt::create($quizattemptid);
        $page = 0;

        // Check login.& capabilities
        require_login($attemptobj->get_course(), false, $attemptobj->get_cm());
        $attemptobj->require_capability('mod/quiz:manage');

        // Check the access rules.
        $accessmanager = $attemptobj->get_access_manager(time());
        $accessmanager->setup_attempt_page($PAGE);
        $output = $PAGE->get_renderer('mod_quiz');

        // Get the list of questions needed by this page. // first done with all
        $slots = $attemptobj->get_slots('all');

        // Check.
        if (empty($slots)) {
            throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'noquestionsfound');
        }

        // get exam data
        //$examcourse = $DB->get_record('course', array('id'=>$exam->courseid), 'id, shortname, fullname, idnumber');
        $examcourses = local_ulpgccore_load_courses_details(array($exam->courseid), 'c.id, c.fullname, c.idnumber, c.shortname, uc.department, uc.credits, uc.term');
        $examcourse = reset($examcourses);
        unset($examcourses);
        $categoryid = $DB->get_field('local_ulpgccore_categories', 'categoryid', array('degree'=>$exam->programme));
        $filename = $examcourse->shortname.'-';
        $programme = $exam->programme.' - '.$DB->get_field('course_categories', 'name', array('id'=>$categoryid));
        $coursename = $examcourse->shortname.' - '.$examcourse->fullname;
        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->period, 'periods');
        $period = $name.' ('.$idnumber.')';
        $filename .= $idnumber;
        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examscope);
        $scope = $name;
        $filename .= '-'.$idnumber.'-'.$exam->callnum;
        $callnum = get_string('callnum', 'examregistrar').': '.$exam->callnum;
        $scope .= ', '.$callnum;
        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->annuality);
        $annuality = $name;
        $examname = $period.', '.$scope.' ('.$annuality.')';

        $headstyle = ' style="text-align:right; font-weight: bold;"';
        $headalign = ' style="vertical-align:middle;  line-height: 8.0em; border: 1px solid black;" ';
        $header = '<table cellspacing="0" cellpadding="4" border="1"  width:100%;  style="  border: 1px solid black; border-collapse: collapse; table-layout:fixed; ">';
        $header .= "<tr $headalign ><td $headstyle  width=\"15%\" >".get_string('programme', 'examregistrar').'</td><td colspan="5">'.$programme.'</td></tr>';
        $header .= "<tr $headalign ><td $headstyle  >".get_string('course', 'examregistrar').'</td><td colspan="5">'.$coursename.'</td></tr>';
        $headalign = ' style="vertical-align:middle;  line-height: 10.0em;" ';
        $header .= "<tr $headalign ><td $headstyle  >".get_string('perioditem', 'examregistrar').'</td><td width="29%" colspan="1">'.$period.'</td>'.
                   "<td $headstyle colspan=\"1\"> ".get_string('scopeitem', 'examregistrar').' </td><td width="17%" colspan="1">'.$scope.'</td>';

        $headstyle = ' style="text-align:right; font-weight: bold;" width="12%"';
        $header .= "<td $headstyle colspan=\"1\"> ".get_string('annualityitem', 'examregistrar').' </td><td  width="8.7%" >'.$annuality.'</td></tr>';

        //$headstyle = ' style="text-align:right; font-weight: bold;" width="15%"';
        //$header .= "<td $headstyle colspan=\"1\"> ".get_string('annualityitem', 'examregistrar').' </td><td width="13.3%" >'.$annuality.'</td></tr>';


        $headstyle = ' style="text-align:right; font-weight: bold;" ';
        $headalign = ' style="vertical-align:middle;  line-height: 18.0em:  border: 1px solid black;" ';
        $header .= "<tr $headalign ><td $headstyle >".get_string('lastname').'</td><td colspan="5"></td></tr>';
        $header .= "<tr $headalign ><td $headstyle ".'   >'.get_string('firstname').'</td><td colspan="2"></td>'.
                   "<td $headstyle >".get_string('idnumber').'</td><td colspan="2"></td></tr>';
        $header .= '</table>';


        //echo $header;

        //$header = ' cabecero ';

        // PDF title section
        $pdf = new makeexam_pdf();

        // set document information
        $pdf->SetCreator('Moodle mod_quiz');
        $pdf->SetAuthor(fullname($USER));
        $pdf->SetTitle($examname);
        $pdf->SetSubject($coursename);
        $pdf->SetKeywords('moodle, quiz');

        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);

        // set default header data
        //$pdf->SetHeaderData('', 25, $coursename, '');
        $pdf->SetHeaderData('', 0, $coursename, '');

        // set header and footer fonts
        $pdf->setHeaderFont(array('helvetica', '', 8));
        $pdf->setFooterFont(array('helvetica', '', 8));

        // set margins
        $topmargin = 10;
        $leftmargin = 15;
        $rightmargin = 15;
        $pdf->SetMargins($leftmargin, $topmargin, $rightmargin);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 25);

        // set image scale factor
        $pdf->setImageScale(1.25);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('helvetica', '', 10);

        // add titlepage
        $pdf->AddPage('', '', true);
        $pdf->Ln(1);
        $pdf->writeHTML($header, false, false, true, false, '');
        $pdf->Ln(12);

//         $text = " &#8594;  &rarr;  &#9829; &hearts;  x&nbsp;x    &#8694;  &#8649;  &#9745; &#9872; &#9873; &#169;";
//         $pdf->writeHTML($text, false, false, true, false, '');
//         $pdf->Ln(12);

        // set font
        $pdf->SetFont('helvetica', '', 9);

        if($type == 'key') {
            $pdf->SetFont('helvetica', '', 10);

        }




        // PDF questions
        $lastpage = 0;
        $options = $attemptobj->get_display_options(false);
        $options->rightanswer = ($type == 'answers') ? 1 : 0;
        $number = 1;
        foreach ($slots as $slot) {
            $qa = $attemptobj->get_question_attempt($slot);
            $question = $qa->get_question();
            //print_object($question);
            if($type == 'key') {

            } else {

                $page = $attemptobj->get_question_page($slot);
                if($page != $lastpage) {
                    $pdf->AddPage('', '', true);
                    $lastpage = $page;
                }

                $qtoutput = $question->get_renderer($PAGE);
                $html = $qtoutput->formulation_export($qa, $options);
                if(strpos($html, '/filter/tex/pix.php/') !== false) {
                    //$html .= '</p> TeX TeX TeX TeX TeX TeX TeX TeX TeX TeX TeX TeX TeX TeX TeX TeX </p>';
                                   //print_object($html);
                }
//              format_text($text, $format = FORMAT_MOODLE, $options = null, $courseiddonotuse = null)
                //$html = format_text($html, FORMAT_HTML, array('nocache'=>true, 'noclean'=>true, 'trusted'=>true));
                if($html{0} === '<') {
                    $p = strpos($html, '>');
                    $html = substr_replace($html, '>'.$number.'. ', $p,1);
                } else {
                    $html = $number.'. '.$html;
                }
                $pdf->writeHTML($html, false, false, true, false, 'J');
                if($type == 'answers' && !is_a($question, 'qtype_description_question')) {
                    //$right = $question->get_right_answer_summary();
                    $feedback = '';
                    if(method_exists($question, 'format_generalfeedback')) {
                        $feedback = $question->format_generalfeedback($qa);
                    }
                    //$tags = tag_get_tags_csv('question', $question->id, TAG_RETURN_HTML, 'official');
                    $tags = core_tag_tag::get_item_tags_array('', 'question', $question->id, core_tag_tag::STANDARD_ONLY);
                    
                    $category = format_string($DB->get_field('question_categories', 'name', array('id'=>$question->category)));
                    $info = get_string('feedback', 'quiz_makeexam').strip_tags($feedback, '<a><sup><sub><strong><b><i><em><small>').' / '.
                                get_string('category', 'quiz_makeexam').$category.' / '.
                                get_string('tags', 'quiz_makeexam').implode(', ', $tags);
                    $html = $output->container($info, 'questioninfo');
                    $pdf->writeHTML($html, false, false, true, false, 'J');
                }
            }
            //$export[] = $html;
            //print_object($html);
            //echo $html;
//            $pdf->writeHTML($html, false, false, true, false, 'J');
            $pdf->Ln(8);
            $number++;
        }

        $pdf->Ln(10);

        $filename = clean_filename($filename).'.pdf';
        //die;
        if(!$store) {
            $pdf->Output($filename, 'I');
            die;
        } else {
            return $pdf->Output($filename, 'S');
        }
    }


    /**
     * Generate a PDF file from current attempt. Sends/store as indicated
     * @param object $cm the course module object
     * @param object $quiz the quiz settings.
     * @param object $course the course record
     * @param int $quizattemptid ID of quiz_attempt table entry
     * @param int $examid ID of the exam table entry
     * @param int $examattemptid ID of quiz_makeexam_attempt table entry
     * @return string error message, if any
     */
    protected function submit_attempt($cm, $quiz, $course, $quizattemptid, $examid, $examattemptid) {
        global $CFG, $DB, $PAGE, $USER;

        $message = get_string('sent', 'quiz_makeexam');
        $exam = $this->exams[$examid];
//        $examattempt = $this->exams[$examid]->attempts[$examattemptid];
        $examattempt = $DB->get_record('quiz_makeexam_attempts', array('id'=>$examattemptid, 'examid'=>$examid, 'quizid'=>$quiz->id), '*', MUST_EXIST);

        if(!$exam || !$examattempt) {
            return get_string('noexamorattempt', 'quiz_makeexam');
        }

        //$registrarattempts = $DB->get_records('examregistrar_examfiles', array('examid'=>$exam), ' attempt ASC ');
        $registrarattempts = $this->exams[$examid]->examfiles;
        $examcourse = $DB->get_record('course', array('id'=>$exam->courseid), 'id, fullname, shortname, idnumber', MUST_EXIST);

        require_capability('mod/examregistrar:submit', context_course::instance($examcourse->id));
        // check we can indeed submit a new
        if($examattempt->status && !has_capability('mod/examregistrar:manageexams', $this->context)) {
            return get_string('alreadysent', 'quiz_makeexam');
        }
        //if($examfiles = $DB->get_records('examregistrar_examfiles', array('examid'=>$exam->id))) {
        $numattempts = 0;
        if($exam->examfiles) {
            foreach($exam->examfiles as $item) {
                if($item->status >= EXAM_STATUS_APPROVED) {
                    $cansubmit = false;
                    return get_string('alreadyapproved', 'quiz_makeexam');
                }
                if($item->attempt > $numattempts) {
                    $numattempts = $item->attempt;
                }
            }
        }


        // no attempt, we are adding
        $now = time();
        $examfile = new stdClass;
        $examfile->examid = $exam->id;
        $examfile->status = EXAM_STATUS_SENT;
        $examfile->attempt = $registrarattempts ? max(count($registrarattempts), $numattempts) + 1 : 1; // ensure allways greater number even if deleted any examfile
        $examfile->name = $examattempt->name;
        $examfile->idnumber = examregistrar_examfile_idnumber($exam, $examcourse->idnumber);
        $examfile->userid = $USER->id;
        $examfile->printmode = 0;
        $examfile->timecreated = $now;
        $examfile->timemodified = $now;

        $newid = $DB->insert_record('examregistrar_examfiles', $examfile);

        if($newid) {
            $examfile->id = $newid;
            // update examattemp
            $examattempt->status = 1;
            $examattempt->examfileid = $newid;
            $examattempt->timesubmitted = $now;
            $DB->update_record('quiz_makeexam_attempts', $examattempt);

            // now we can generate & store exam PDF  files
            $fs = get_file_storage();
            $filecontext = context_course::instance($examcourse->id);
            $fileinfo = array(
                'contextid' => $filecontext->id, // ID of exam course context
                'component' => 'mod_examregistrar',
                'filearea' => 'exam',
                'itemid' => $newid,               // the id of the new examfile entry
                'filepath' => '/',
                'filename' => examregistrar_file_set_nameextension($examfile->idnumber, 'exam'));

            // Create file containing no responses
            $fs->create_file_from_string($fileinfo, $this->generate_pdf($quiz, $quizattemptid, $examid, $examattempt->id, 'exam', true));
            // Create file containing  correct answers
            $fileinfo['filepath'] = '/answers/';
            $fileinfo['filename'] = examregistrar_file_set_nameextension($examfile->idnumber, 'answers');
            $fs->create_file_from_string($fileinfo, $this->generate_pdf($quiz, $quizattemptid, $examid, $examattempt->id, 'answers', true));
            // Create file containing  correct answers
            $fileinfo['filepath'] = '/key/';
            $fileinfo['filename'] = examregistrar_file_set_nameextension($examfile->idnumber, 'key');
            //$fs->create_file_from_string($fileinfo, $this->generate_pdf($quiz, $quizattemptid, $examid, $examattempt->id, 'key', true));

            $eventdata = array();
            $eventdata['objectid'] = $attemptid;
            $eventdata['context'] = $this->context;
            $eventdata['other'] = array();
            $eventdata['other']['quizid'] = $quiz->id;
            $eventdata['other']['examid'] = $examattempt->id;
            $eventdata['other']['examfileid'] = $examattempt->examfileid;
            $eventdata['other']['idnumber'] = $examfile->idnumber;
            $event = \quiz_makeexam\event\exam_submitted::create($eventdata);
            $event->trigger();

            
            
            // now create tracker issue for examfile
            $examregistrar = $this->get_examregistrar_instance($cm, $course);
            $issueid = examregistrar_review_addissue($examregistrar, $course, $examfile);
            if($issueid == 0 ) {
                $message = get_string('noreviewmod', 'quiz_makeexam');
            } elseif($issueid == -1 ) {
                $message = get_string('notracker', 'quiz_makeexam');
            }
            if(warning_questions_used($examfile)) {
                $examfile->printmode = 1;
                $DB->set_field('examregistrar_examfiles', 'printmode', 1, array('id'=>$newid));
            }

            $this->clear_quiz($quiz);
        }
        return $message;
    }


    protected function import_old_questions($oldcode = '', $status = '') {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot.'/question/engine/bank.php');
        require_once($CFG->dirroot . '/tag/lib.php');

        @set_time_limit(3600); // 1 hour should be enough
        raise_memory_limit(MEMORY_HUGE);
        $now = time();

        $params = array();
        $sourcecode = '';
        if($oldcode != '') {
            $sourcecode = " AND codigo = ? ";
            $params[] = $oldcode;
        }
        $sourcestatus = '';
        if($status != '') {
            $sourcestatus = " AND questionid $status ";
        }

        $oldquestions = $DB->get_recordset_sql("SELECT * FROM preguntas_prof WHERE 1 $sourcecode $sourcestatus ", $params);

        $this->install_official_tags(); // just in case not installed yet
        $tag = get_string('tagvalidated', 'quiz_makeexam');

        foreach($oldquestions as $oldq) {
            if(!$oldq->codigo || !$oldq->pregunta) {
                continue;
            }

            if($oldcode) {
                $courses = array($this->course);
            } else {
                $select = $DB->sql_like('shortname', ':pattern');
                $courses = $DB->get_records_select('course', $select, array('pattern'=>$oldq->codigo.'%'), '', 'id, shortname, idnumber');
            }
            if($courses) {
                foreach($courses as $course) {
                    $context = context_course::instance($course->id);
                    $question = $this->defaultquestion();
                    $question->qtype = 'multichoice';
                    $question->name = false;
                    $question->questiontext = $oldq->pregunta;
                    // Set question name if not already set.
                    if ($question->name === false) {
                        $question->name = $this->create_default_question_name($question->questiontext, get_string('questionname', 'question'));
                    }
                    $question->single = 1;
                    $question = $this->add_blank_combined_feedback($question);

                    $question->answer = array();
                    $question->fraction = array();
                    $question->feedback = array();
                    $answers = array('A'=> $oldq->respa, 'B'=> $oldq->respb, 'C'=> $oldq->respc, 'D'=> $oldq->respd);
                    foreach($answers as $key => $answer) {
                        $answerweight = ($key == $oldq->solucion) ? 1 : 0;
                        list($question->answer[$key], $question->feedback[$key]) =
                                $this->commentparser($answer, $question->questiontextformat);
                        $question->fraction[$key] = $answerweight;
                    }
                    $question->generalfeedback = 'pg. '.$oldq->paglibro;

                    if($oldq->revision && $tag) {
                        $question->tags = array($tag);
                    }

                    $question->context = $context;

                    $categoryname = 'Unidad '.$oldq->modulo;
                    if(!$categoryid = $DB->get_field('question_categories', 'id', array('contextid'=>$context->id, 'parent'=>0, 'name'=>$categoryname))) {
/*
                        $categories = $DB->get_fieldset_select('question_categories', 'id', ' contextid = :contextid AND parent = :parent ', array('contextid'=>$context->id, 'parent'=>0));
                        $categoryid = reset($categories);
                        if(!$categoryid) {
                            continue;
                        }
*/
                        $cat = new stdClass();
                        $cat->parent = 0;
                        $cat->contextid = $context->id;
                        $cat->name = $categoryname;
                        $cat->info = '';;
                        $cat->infoformat = 1;
                        $cat->sortorder = 999;
                        $cat->stamp = make_unique_id_code();
                        if(!$categoryid = $DB->insert_record("question_categories", $cat)) {
                            $categories = $DB->get_fieldset_select('question_categories', 'id', ' contextid = :contextid AND parent = :parent ', array('contextid'=>$context->id, 'parent'=>0));
                            $categoryid = reset($categories);
                            if(!$categoryid) {
                                continue;
                            }
                        }
                    }
                    $question->category = $categoryid;
                    $question->stamp = make_unique_id_code();  // Set the unique code (not to be changed)
                    if(!$userid = $DB->get_field('user', 'id', array('idnumber'=>$oldq->dni))) {
                        $userid = $USER->id;
                    }
                    $question->createdby = $userid;
                    $question->timecreated = $now;
                    $question->modifiedby = $userid;
                    $question->timemodified = $now;

                    mtrace(" {$course->shortname} |  {$question->name} <br />\n");

                    if($question->id = $DB->insert_record('question', $question)) {
                        $result = question_bank::get_qtype($question->qtype)->save_question_options($question);

                        if (!empty($CFG->usetags) && isset($question->tags)) {
                            core_tag_tag::set_item_tags('core_question', 'question', $question->id, $context, $question->tags);
                        }
                        // Give the question a unique version stamp determined by question_hash()
                        $DB->set_field('question', 'version', question_hash($question),
                                array('id' => $question->id));

                        if(!$oldcode) {
                            $sql = "UPDATE preguntas_prof
                                    SET questionid = {$question->id}, timecopied = $now
                                    WHERE id = {$oldq->id}";
                            $DB->execute($sql);
                        }
                    }

                }
            }
        }
        $oldquestions->close();
        //die;
    }

    protected function defaultquestion() {
        global $CFG;

        $question = new stdClass();
        $question->shuffleanswers = 0;
        $question->defaultmark = 1;
        $question->image = "";
        $question->usecase = 0;
        $question->parent = 0;
        $question->multiplier = array();
        $question->questiontextformat = FORMAT_HTML;
        $question->generalfeedback = '';
        $question->generalfeedbackformat = FORMAT_HTML;
        $question->correctfeedback = '';
        $question->partiallycorrectfeedback = '';
        $question->incorrectfeedback = '';
        $question->answernumbering = 'ABCD';
        $question->penalty = 0.3333333;
        $question->length = 1;

        // this option in case the questiontypes class wants
        // to know where the data came from
        $question->export_process = true;
        $question->import_process = true;

        return $question;
    }

    public function create_default_question_name($questiontext, $default) {
        $name = $this->clean_question_name(shorten_text($questiontext, 80));
        if ($name) {
            return $name;
        } else {
            return $default;
        }
    }

    public function clean_question_name($name) {
        $name = clean_param($name, PARAM_TEXT); // Matches what the question editing form does.
        $name = trim($name);
        $trimlength = 251;
        while (core_text::strlen($name) > 255 && $trimlength > 0) {
            $name = shorten_text($name, $trimlength);
            $trimlength -= 10;
        }
        return $name;
    }

    protected function add_blank_combined_feedback($question) {
        $question->correctfeedback['text'] = '';
        $question->correctfeedback['format'] = $question->questiontextformat;
        $question->correctfeedback['files'] = array();
        $question->partiallycorrectfeedback['text'] = '';
        $question->partiallycorrectfeedback['format'] = $question->questiontextformat;
        $question->partiallycorrectfeedback['files'] = array();
        $question->incorrectfeedback['text'] = '';
        $question->incorrectfeedback['format'] = $question->questiontextformat;
        $question->incorrectfeedback['files'] = array();
        return $question;
    }


    protected function parse_text_with_format($text, $defaultformat = FORMAT_MOODLE) {
        $result = array(
            'text' => $text,
            'format' => $defaultformat,
            'files' => array(),
        );
        if (strpos($text, '[') === 0) {
            $formatend = strpos($text, ']');
            $result['format'] = $this->format_name_to_const(substr($text, 1, $formatend - 1));
            if ($result['format'] == -1) {
                $result['format'] = $defaultformat;
            } else {
                $result['text'] = substr($text, $formatend + 1);
            }
        }
        $result['text'] = trim($this->escapedchar_post($result['text']));
        return $result;
    }

    protected function commentparser($answer, $defaultformat) {
        $bits = explode('#', $answer, 2);
        $ans = $this->parse_text_with_format(trim($bits[0]), $defaultformat);
        if (count($bits) > 1) {
            $feedback = $this->parse_text_with_format(trim($bits[1]), $defaultformat);
        } else {
            $feedback = array('text' => '', 'format' => $defaultformat, 'files' => array());
        }
        return array($ans, $feedback);
    }

    protected function format_name_to_const($format) {
        if ($format == 'moodle') {
            return FORMAT_MOODLE;
        } else if ($format == 'html') {
            return FORMAT_HTML;
        } else if ($format == 'plain') {
            return FORMAT_PLAIN;
        } else if ($format == 'markdown') {
            return FORMAT_MARKDOWN;
        } else {
            return -1;
        }
    }

    protected function escapedchar_post($string) {
        // Replaces placeholders with corresponding character AFTER processing is done.
        $placeholders = array("&&058;", "&&035;", "&&061;", "&&123;", "&&125;", "&&126;", "&&010");
        $characters   = array(":",     "#",      "=",      "{",      "}",      "~",      "\n"  );
        $string = str_replace($placeholders, $characters, $string);
        return $string;
    }


    /**
     * Prints the table of exam attempts existing for this exam
     *
     * @param object $cm the course module object of this quiz
     * @param object $quiz the quiz instance this Make Exam is called from
     * @param object $exam the exam (single period, scope, call) being printed
     */
    protected function print_old_questions_form($cm) {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_quiz');

        if(!has_capability('moodle/site:config',  context_system::instance())) {
            return;
        }

        $reporturl = $this->get_base_url();

        $url = new moodle_url($reporturl, array('confirm'=>1, 'sessley'=>sesskey(), 'copyold'=>1));
        $button = new single_button($url, get_string('copyold', 'quiz_makeexam'));
        $button->class = 'makeexambutton';
        $button->add_confirm_action(get_string('copyold_confirm', 'quiz_makeexam'));
        echo $output->container($output->render($button), ' makeexambuttonform clearfix ');

        echo $output->container('', 'clearfix');
    }

    /**
     * Clears the quiz removing listed questions
     *
     * @param object $quiz the quiz instance this Make Exam is called from
     * @param object $cm the course module object of this quiz
     * @param object $coures the course settings.
     */
    protected function print_clearquiz_button($quiz, $cm, $course) {
        global $PAGE;

        if(!has_capability('quiz/makeexam:submit', context_module::instance($cm->id))) {
            return;
        }

        $output = $PAGE->get_renderer('mod_quiz');

        $reporturl = $this->get_base_url();

        $url = new moodle_url($reporturl, array('confirm'=>1, 'sessley'=>sesskey(), 'clearquiz'=>1));
        $button = new single_button($url, get_string('clearattempts', 'quiz_makeexam'));
        $button->class = 'makeexambutton';

        $questions = $this->get_quiz_questions_ids($quiz->id);
        if(!$questions && !$this->currentattempt) {
            $button->disabled = 'disabled';
        }
        $button->add_confirm_action(get_string('clear_confirm', 'quiz_makeexam'));
        echo $output->container($output->render($button), ' makeexambuttonform clearfix ');
        echo $output->container('', 'clearfix');
    }


}
