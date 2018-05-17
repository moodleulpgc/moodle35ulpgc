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
 * Prints a complete history of a student for current UNEDTrivial
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/mod/unedtrivial/signupform.php');
        
global $DB;

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... newmodule instance ID - it should be named as the first character of the module.
$instance = optional_param('instance', 0, PARAM_INT);
$student = optional_param('student', 0, PARAM_INT);

if ($id) {
    $cm         = get_coursemodule_from_id('unedtrivial', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $newmodule  = $DB->get_record('unedtrivial', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $newmodule  = $DB->get_record('unedtrivial', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $newmodule->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('unedtrivial', $newmodule->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$event = \mod_unedtrivial\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $newmodule);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/unedtrivial/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($newmodule->name));
$PAGE->set_heading(format_string($course->fullname));

// Output starts here.
echo $OUTPUT->header();

//See highscores
$query1 = $DB->get_record_sql('SELECT u.firstname,u.lastname '
        . '                       FROM {user} u'
        . '                      WHERE u.id = ? ', array($student));
echo $OUTPUT->heading(format_string($query1->firstname." ".$query1->lastname), 2, null);

$query2 = $DB->get_records_sql('SELECT u.id,u.questiondate,q.question,u.questionid,'
        . '                            u.questionstate,u.questionscore '
        . '                       FROM {unedtrivial_history} u'
		. '               JOIN {unedtrivial_questions} q ON q.id = u.questionid'
        . '                      WHERE u.idunedtrivial = ? AND u.userid = ?' 
                                , array($instance, $student));
$table = new html_table();
$table->head = array(get_string('studentinfocol0', 'unedtrivial'),
                     get_string('studentinfocol1', 'unedtrivial'),
                     get_string('studentinfocol2', 'unedtrivial'),
                     get_string('studentinfocol3', 'unedtrivial'));
foreach($query2 as $row) {   
    //question = -1 is the first reg, discard it
    if ($row->questionid == -1){
        continue;
    }
    if ($row->questionstate == -1){
        $state = get_string('studentinfoquestionstate1', 'unedtrivial');
    }else if ($row->questionstate < $newmodule->timestocomplete){
        $state = get_string('studentinfoquestionstate2', 'unedtrivial').
                ' ('.$row->questionstate.')';
    }else{
        $state = get_string('studentinfoquestionstate3', 'unedtrivial');
    }
    $questionplain = filter_var($row->question, FILTER_SANITIZE_STRING);
    if (strlen($questionplain) > 60){
        $questionplain = mb_substr($questionplain,0,60,'UTF-8') . "...";
    }
    $date = date("d-m-Y",$row->questiondate);
    if ($row->questionstate == -1){
        $table->data[] = array($date,$questionplain,'<font color="red">'.$state."</font>",
            $row->questionscore);
    }else if ($row->questionstate < $newmodule->timestocomplete){
        $table->data[] = array($date,$questionplain,'<font color="green">'.$state."</font>",
            $row->questionscore);
    }else{
        $table->data[] = array($date,$questionplain,'<b><font color="blue">'.$state."</font></b>",
            $row->questionscore);
    }
}
echo html_writer::table($table);

echo html_writer::tag('button', get_string('back', 'unedtrivial'), 
        array('class'=>'myclass', 'type' => 'button','onclick'=>'window.history.back();'));

// Finish the page.
echo $OUTPUT->footer();