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
 * Quiz makeexam settings form definition.
 *
 * @package   quiz_makeexam
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * This is the settings form for the quiz makeexam report.
 *
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_makeexam_settings_form extends moodleform {
    protected function definition() {
        global $DB;

        $mform = $this->_form;

        $exams = $this->_customdata['exams'];
        $quiz = $this->_customdata['quiz'];
        $hasquestions = $this->_customdata['questions'];
        $canmanage = has_capability('mod/examregistrar:manageexams', context_course::instance($quiz->course));
        $currentattempt = $this->_customdata['current'];
        $current = 0;
        if($currentattempt) {
            $examattempt = $DB->get_record('quiz_makeexam_attempts', array('quizid'=>$quiz->id, 'id'=>$currentattempt));
            $current = $examattempt->examid; // $DB->get_field('quiz_makeexam_attempts', 'examid', array('id'=> $currentattempt,
                                                //                                                      'quizid'=>$quiz->id));
        }

        $mform->addElement('header', 'preferencespage', get_string('reportsettings', 'quiz_makeexam'));

            /// TODO usable for selecting exam period / convocatoria   /// TODO
            /// TODO usable for selecting exam period / convocatoria   /// TODO
            /// TODO usable for selecting exam period / convocatoria   /// TODO
            /// TODO usable for selecting exam period / convocatoria   /// TODO

        $mform->addElement('static', 'help1', '', get_string('quizeditinghelp', 'quiz_makeexam'));

        $examselect = $mform->createElement('select');
        $examselect->setName('examid');
        $examselect->setLabel(get_string('exam', 'quiz_makeexam'));
        $examselect->addOption(get_string('choose'), '');

        $attempts = 0;
        $disabledexams = array();
        $now = time();
        $config = get_config('examregistrar');
        foreach($exams as $exam) {
            $examdate = $DB->get_field('examregistrar_examsessions', 'examdate', array('id'=>$exam->examsession, 'period'=>$exam->period));
            $maxattempt = 0;
            foreach($exam->attempts as $attempt) {
                if($attempt->attempt > $maxattempt) {
                    $maxattempt = $attempt->attempt;
                }
            }
            $items = array();
            list($name, $idnumber) = examregistrar_get_namecodefromid($exam->period, 'periods');
            $items[] = $idnumber;

            list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examscope);
            $items[] = $idnumber;

            $items[] = get_string('callnum', 'examregistrar').': '.$exam->callnum;

            list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examsession, 'examsessions');
            $items[] = ' ('.$idnumber.')';

            $cansubmit = true;
            if($exam->attempts) {
                $cansubmit = false;
                $rejected = true;
                // if ANY examfile for this exmid is approved, or sent without resolution then no more exam submitting allowed
                foreach($exam->examfiles as $item) {
                    if(($item->status != EXAM_STATUS_CREATED) && ($item->status != EXAM_STATUS_REJECTED) ) {
                        $rejected = false;
                    }
                }
                $cansubmit = $cansubmit || $rejected || $canmanage;
            }

            $enabled = (!$current
                        || ($exam->id == $current)
                        || !($now > strftime("- {$config->approvalcutoff} days ",  $examdate)));
            $disabled = ($enabled && $cansubmit)  ? '' : array('disabled'=>'disabled');
            $examselect->addOption(implode(', ', $items), $exam->id, $disabled);
            $count = (!$current || ($exam->id == $current)) ? $maxattempt : 0 ;
            if($count > $attempts) {
                $attempts = $count;
            }
            if($disabled) {
                $disabledexams[$exam->id] = $exam->id;
            }
        }

        $mform->addElement($examselect);
        $mform->addRule('examid', null, 'required', null, 'client');
        $mform->addHelpButton('examid', 'exam', 'quiz_makeexam');
        $mform->setDefault('examid', $current);

        $options = array(0 => get_string('currentquestions', 'quiz_makeexam'));
        if($current) {
            $attempts = $DB->get_records('quiz_makeexam_attempts', array('quizid'=>$quiz->id, 'examid'=>$current), ' attempt ASC ', 'id, name, attempt');
        } else {
            $attempts = range(0, $attempts);
            unset($attempts[0]);
        }
        foreach($attempts as $key=>$option) {
            if(is_numeric($option)) {
                $options[$key] = get_string('attemptn', 'quiz_makeexam', $option);
            } else {
                $options[$key] = $option->name;
            }
        }
        $mform->addElement('select', 'attemptn', get_string('attemptquestions', 'quiz_makeexam'), $options, 0);
        $mform->addHelpButton('attemptn', 'attemptquestions', 'quiz_makeexam');

        $name = $currentattempt ? $attempts[$currentattempt]->name : '';
        $mform->addElement('advcheckbox', 'currentattempt', get_string('continueattempt', 'quiz_makeexam'), $name, array('group' => 1), array(0, $currentattempt));
        $mform->setType('currentattempt', PARAM_INT);
        $mform->setDefault('currentattempt', $currentattempt);
        //$mform->disabledIf('currentattempt', 'attemptn', 'neq', 0);
        if(!($current && !$examattempt->status && !$examattempt->examfileid)) {
            //$mform->freeze('currentattempt');
        }

        $mform->addElement('text', 'name', get_string('name'), array('size' => 30));
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', '');
        $mform->addHelpButton('name', 'attemptquestions', 'quiz_makeexam');
        $mform->disabledIf('name', 'currentattempt', 'neq', 0);

        $mform->addElement('hidden', 'action', 'newattempt');
        $mform->setType('action', PARAM_ALPHANUMEXT);


        $disabled = $hasquestions ? null : array('disabled'=>'disabled');
        $mform->addElement('submit', 'submitbutton', get_string('newattempt', 'quiz_makeexam'), $disabled);
        if(!$disabled && $disabledexams) {
            foreach($disabledexams as $examid) {
                $mform->disabledIf('submitbutton', 'examid', 'eq', $examid);
            }
        }

    }
}
