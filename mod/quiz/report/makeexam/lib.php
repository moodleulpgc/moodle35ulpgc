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
 * Standard plugin entry points of the quiz makeexam report.
 *
 * @package   quiz_makeexam
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Serve questiontext files in the question text when they are displayed in this report.
 *
 * @package  quiz_makeexam
 * @category files
 * @param context $previewcontext the quiz context
 * @param int $questionid the question id.
 * @param context $filecontext the file (question) context
 * @param string $filecomponent the component the file belongs to.
 * @param string $filearea the file area.
 * @param array $args remaining file args.
 * @param bool $forcedownload.
 * @param array $options additional options affecting the file serving.
 */
function quiz_makeexam_question_preview_pluginfile($previewcontext, $questionid,
        $filecontext, $filecomponent, $filearea, $args, $forcedownload, $options = array()) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');

    list($context, $course, $cm) = get_context_info_array($previewcontext->id);
    require_login($course, false, $cm);

    // Assume only trusted people can see this report. There is no real way to
    // validate questionid, becuase of the complexity of random quetsions.
    require_capability('quiz/makeexam:view', $context);

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/{$filecontext->id}/{$filecomponent}/{$filearea}/{$relativepath}";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        send_file_not_found();
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * Quiz makeexam report cron code. Deletes cached data more than a certain age.
 */
function quiz_makeexam_cron() {
    global $DB;

    //mtrace("\n  Cleaning up old quiz makeexam cache records...", '');

    //$expiretime = time() - 5*HOURSECS;
    //$DB->delete_records_select('quiz_makeexam', 'timemodified < ?', array($expiretime));

    return true;
}


/**
 * Releases questions hidden by makeexam when examfile is deleted or rejected
 *
 * @param int $examfileid the examfile id rejected, questions must be released.
 * @param context $filecontext the file (question) context
 */
function quiz_makeexam_release_questions($examfileid) {
    global $DB;

    $unhide = array();
    if($attempts = $DB->get_records('quiz_makeexam_attempts', array('examfileid'=>$examfileid))) {
        foreach($attempts as $aid => $attempt) {
            $DB->set_field('quiz_makeexam_slots', 'inuse', 0, array('mkattempt'=>$aid));
            $DB->set_field('quiz_makeexam_sections', 'inuse', 0, array('mkattempt'=>$aid));
            $questions = $DB->get_records_menu('quiz_makeexam_slots', array('mkattempt'=>$attempt->id), '', 'id,questionid');
            foreach($questions as $qid) {
                if(!$DB->record_exists('quiz_makeexam_slots', array('questionid'=>$qid, 'inuse'=>1))) {
                    $unhide[] = $qid;
                }
            }
        }
    }

    // unhide used questions. Checked first if used in other attempts
    if($unhide) {
        list($insql, $params) = $DB->get_in_or_equal($unhide);
        $DB->set_field_select('question', 'hidden', 0, " id $insql ", $params);
    }

    return true;
}


/**
 * Validates questions when an exam attempt is approved
 *
 * @param int $examfileid the examfile id approved, questions must be validates.
 * @param context $filecontext the file (question) context
 */
function quiz_makeexam_validate_questions($examfileid) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/tag/lib.php');

    $reviewerid = $DB->get_field('examregistrar_examfiles', 'reviewerid', array('id'=>$examfileid));

    $validate = array();
    if($attempts = $DB->get_records('quiz_makeexam_attempts', array('examfileid'=>$examfileid))) {
        foreach($attempts as $aid => $attempt) {
            $DB->set_field('quiz_makeexam_slots', 'inuse', 1, array('mkattempt'=>$aid));
            $DB->set_field('quiz_makeexam_sections', 'inuse', 1, array('mkattempt'=>$aid));
            $questions = $DB->get_records_menu('quiz_makeexam_slots', array('mkattempt'=>$attempt->id), '', 'id,questionid');
            $validate = $validate + $questions;
            $context = context_course::instance($attempt->course);
        }
    }

    $tagvalidated = tag_get_id(get_string('tagvalidated', 'quiz_makeexam'));
    $tagrejected  = tag_get_id(get_string('tagrejected', 'quiz_makeexam'));
    $tagnoreview  = tag_get_id(get_string('tagunvalidated', 'quiz_makeexam'));

    if($validate && $tagvalidated) {
        foreach($validate as $qid) {
            if($tagrejected) {
                tag_delete_instance('question', $qid, $tagrejected);
            }
            if($tagnoreview) {
                tag_delete_instance('question', $qid, $tagnoreview);
            }
            tag_assign('question', $qid, $tagvalidated, 0, $reviewerid, 'core_question',  $context->id);
        }
    }

    // questions in approved exam must be hidden. Ensure here (just in case aprove-reject-approve cycle)
    if($validate) {
        list($insql, $params) = $DB->get_in_or_equal($validate);
        $DB->set_field_select('question', 'hidden', 1, " id $insql ", $params);
    }

    return true;
}

/**
 * Get
 *
 * @param object $quiz the quiz settings record
 * @return array (id, questionid) for questions already used in other attempts
 */
function quiz_makeexam_quiz_used_questionids($quiz) {
    global $DB;

    $questions = array();
    $currentattempt = $DB->get_record('quiz_makeexam_attempts', array('quizid'=>$quiz->id, 'currentattempt'=>1));
    if(!$currentattempt) {
        $attempts = $DB->get_records_menu('quiz_makeexam_attempts', array('quizid'=>$quiz->id, 'status'=>1), 'attempt', 'id,attemptid');
    } else {
        $select = ' quizid = :quizid AND examid <> :examid AND status = 1';
        $attempts = $DB->get_records_select_menu('quiz_makeexam_attempts', $select, array('quizid'=>$quiz->id, 'examid'=>$currentattempt->examid), 'attempt', 'id,attemptid');
    }

    if($attempts) {
        list($insql, $params) = $DB->get_in_or_equal(array_keys($attempts), SQL_PARAMS_NAMED, 'at_');
        $select = "quizid = :quizid AND inuse = 1 AND mkattempt $insql";
        $params['quizid'] = $quiz->id;
        $questions = $DB->get_records_select_menu('quiz_makeexam_slots', $select, $params, 'questionid', 'id, questionid');
    }

    if($questions) {
        $questions = array_unique($questions);
    }
    return $questions;
}

/**
 * Checks if exists a makeexam currents atetmpt and displays its name if any
 *
 * @param object $quiz the quiz object to check
 * @return string
 */
function quiz_makeexam_quiz_current_attempt($quiz) {
    global $DB;

    $name = '';
    if($makeexamattempt = $DB->get_record('quiz_makeexam_attempts', array('quizid'=>$quiz->id, 'currentattempt'=>1))) {
        if($exam = $DB->get_record('examregistrar_exams', array('id'=>$makeexamattempt->examid))) {
            $name = quiz_makeexam_examname($exam);
        } else {
            $DB->set_field('quiz_makeexam_attempts', 'currentattempt', 0, array('id'=>$makeexamattempt->id, 'quizid'=>$quiz->id, 'currentattempt'=>1));
            $name = get_string('noexamid', 'examregistrar', $makeexamattempt->examid);
        }
    }

    if($name) {
        $name = html_writer::div($name, ' makeexamversion error addpage');
    }

    return $name;
}


/**
 * Builds a display name for the exam
 *
 * @param object $exam the exam entry in examregistrar table
 */
function quiz_makeexam_examname($exam) {
    global $CFG;

        require_once($CFG->dirroot . '/mod/examregistrar/locallib.php');
        $items = array();
        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->period, 'periods');
        $items[] = $name.' ('.$idnumber.')';

        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examscope);
        $items[] = $name.' ('.$idnumber.')';

        $items[] = get_string('callnum', 'examregistrar').': '.$exam->callnum;

        list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examsession, 'examsessions');
        $items[] = $name.' ('.$idnumber.')';

        return implode('; ', $items);
}

function quiz_makeexam_install_official_tags() {
    global $CFG;

    // install official tags
    require_once($CFG->dirroot . '/tag/lib.php');
    $tags[] = get_string('tagvalidated', 'quiz_makeexam');
    $tags[] = get_string('tagrejected', 'quiz_makeexam');
    $tags[] = get_string('tagunvalidated', 'quiz_makeexam');
    foreach($tags as $tag) {
        $tag = trim($tag);
        if(!$tagid = tag_get_id($tag)) {
            $ids = tag_add($tag, 'official');
        } else {
            tag_type_set($tagid, 'official');
        }
    }
}