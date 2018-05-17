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
 * This page handles quiz attempt export options
 *
 * @package    mod_quiz
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/mod/quiz/lib.php');
require_once($CFG->dirroot.'/mod/quiz/locallib.php');
require_once($CFG->dirroot.'/local/ulpgcquiz/export_form.php');


$cmid = required_param('cmid', PARAM_INT);
$action = optional_param('action', 0, PARAM_INT); // 0 show form, 1 download
$attempt = optional_param('aid', 0, PARAM_INT);

if (! $cm = get_coursemodule_from_id('quiz', $cmid)) {
    print_error('invalidcoursemodule');
}
if (! $quiz = $DB->get_record('quiz', array('id' => $cm->instance))) {
    print_error('invalidcoursemodule');
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

$url = new moodle_url('/local/ulpgcquiz/export.php', array('cmid'=>$cm->id));
$returnurl = new moodle_url('/mod/quiz/view.php', array('id'=>$cm->id));

require_login($course, false, $cm);

$context = context_module::instance($cm->id);

$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($course->fullname);


// Check the user has the required capabilities to list overrides.
require_capability('mod/quiz:manage', $context);

if(!$attempt) {
    $attempt = $DB->get_field('quiz_attempts', 'id', array('quiz'=>$quiz->id, 'userid'=>$USER->id, 'preview'=>1, 'state'=>'finished'));
    if(!$attempt) {
        // no attempt, message user
        $pagetitle = get_string('exportquiz', 'local_ulpgcquiz');
        $PAGE->set_title($pagetitle);
        echo $OUTPUT->header();
        echo $OUTPUT->heading($pagetitle);
        echo $OUTPUT->notification(get_string('exportnoattempt', 'local_ulpgcquiz'));
        echo $OUTPUT->continue_button($returnurl);
        echo $OUTPUT->footer();
        die;
    }
}

$attempt = $DB->get_record('quiz_attempts', array('id' => $attempt), '*', MUST_EXIST);


// Setup the form.
$mform = new quiz_export_form(null, array('cmid'=>$cmid, 'attempt'=>$attempt->id));

if ($mform->is_cancelled()) {
    redirect($returnurl);

} else if (($fromform = $mform->get_data()) && $action ) {
    // Process the data and show download links.
    $fromform->quiz = $quiz->id;
    $pagetitle = get_string('exportdownload', 'local_ulpgcquiz');
    $PAGE->navbar->add($pagetitle);
    $PAGE->set_title($pagetitle);
    $PAGE->set_heading($course->fullname);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($pagetitle);
        $fromform->examdate = userdate($fromform->examdate, get_string('strftimedate'));
        foreach($fromform as $key=>$value) {
            if($key{0} == 'e') {
                // TODO add feedback properly and nicely formatted
                //echo get_string($key, 'quiz').': '.$value.'<br />';
            }
        }

        $url = new moodle_url('/mod/quiz/review.php', array('attempt'=>$attempt->id));
        $name = html_writer::link($url, get_string('preview', 'quiz'));

        echo $OUTPUT->container_start('quizexportlink');
        $answers = $fromform->examanswers;
        $fromform->examanswers = 0;
        if (confirm_sesskey()) {      // this button should trigger a download prompt
            echo $OUTPUT->single_button(new moodle_url('/local/ulpgcquiz/exportdump.php', get_object_vars($fromform)), get_string('downloadexam', 'local_ulpgcquiz'));

        } else {
            $paramstr = '';
            $sep = '?';
            foreach($params as $name=>$value) {
                $paramstr .= $sep.$name.'='.$value;
                $sep = '&';
            }

            $link = $CFG->wwwroot.'/mod/quiz//exportdump.php'.$paramstr.'&sesskey='.$USER->sesskey;

            echo get_string('downloadexam', 'local_ulpgcquiz').': ' . html_writer::link($link, $link);
        }

        $fromform->examanswers = $answers;
        if($fromform->examanswers) {
            if (confirm_sesskey()) {      // this button should trigger a download prompt
                echo $OUTPUT->single_button(new moodle_url('/local/ulpgcquiz/exportdump.php', get_object_vars($fromform)), get_string('downloadwithanswers', 'quiz'));

            } else {
                $paramstr = '';
                $sep = '?';
                foreach($params as $name=>$value) {
                    $paramstr .= $sep.$name.'='.$value;
                    $sep = '&';
                }

                $link = $CFG->wwwroot.'/mod/quiz//exportdump.php'.$paramstr.'&sesskey='.$USER->sesskey;

                echo get_string('downloadwithanswers', 'local_ulpgcquiz').': ' . html_writer::link($link, $link);
            }
        }

        echo $OUTPUT->container_end();

        echo $OUTPUT->continue_button($returnurl);

    echo $OUTPUT->footer();
    $info = $quiz->name;
    foreach($fromform as $key=>$field) {
        if(substr($key, 0, 1) == 'e') {
            $info .= ' ;  '.$key.' = '.$field;
        }
    }
    add_to_log($cm->course, 'quiz', 'export '.get_string($fromform->exporttype, 'local_ulpgcquiz'),
            "export.php?cmid=$fromform->cmid", $info, $cm->id);

    die;
}

// Print the form.
$pagetitle = get_string('exportquiz', 'local_ulpgcquiz');
$PAGE->set_title($pagetitle);

echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);

$mform->display();

echo $OUTPUT->footer();

