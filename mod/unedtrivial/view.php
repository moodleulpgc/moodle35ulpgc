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
 * This page prints a particular instance of UNEDTrivial
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/mod/unedtrivial/signupform.php');
        
GLOBAL $DB;

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... newmodule instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('unedtrivial', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $newmodule  = $DB->get_record('unedtrivial', array('id' => $cm->instance), '*', MUST_EXIST);
    $context = context_module::instance($cm->id);
} else if ($n) {
    $newmodule  = $DB->get_record('unedtrivial', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $newmodule->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('unedtrivial', $newmodule->id, $course->id, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
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

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('newmodule-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();

// PAGE
if (!has_capability('mod/unedtrivial:addinstance', $context)){
    //Check if user is a participant
    $query1 = $DB->get_record_sql('SELECT u.mail'
        . '                          FROM {unedtrivial_mails} u'
        . '                         WHERE u.idunedtrivial = ? '
        . '                               AND u.userid = ?', array($cm->instance, $USER->id));
    if ($query1 == null){
        $mform = new signupform('view.php?id='.$id,null);
        $fromform = $mform->get_data();
        if ($fromform != null && $fromform->email != '') {
            //If param email is not null and is not duplicated, insert into database
            $countquery1 = $DB->count_records_sql('SELECT COUNT(*)'
                . '                      	     FROM {unedtrivial_mails} u'
                . '                     	    WHERE u.idunedtrivial = ? AND'
                . '                                       u.mail = ? ', array($cm->instance, $fromform->email));
            if ($countquery1 == 0){
                //Insert mail
                $record = new stdClass();
                $record->idunedtrivial = $cm->instance;
                $record->userid = $USER->id;
                $record->mail = $fromform->email;
                $DB->insert_record('unedtrivial_mails', $record, false);
                //We save first record to set score to zero
                $record = new stdClass();
                $record->idunedtrivial = $newmodule->id;
                $record->userid = $USER->id;
                $record->questionid = -1;
                $record->questionscore = 0;
                $record->questionstate = 0;
                $record->questiondate = 0;
                $DB->insert_record('unedtrivial_history', $record);
                
                redirect(new moodle_url('view.php', array('id' => $id)));
                
            }else{
                //Mail duplicated --> show error
                echo $OUTPUT->error_text('<font color="red">'. get_string('errormail', 'unedtrivial') . '</font></b>');
                echo "<br /><br />";
                echo html_writer::tag('button', get_string('back', 'unedtrivial'), 
                        array('class'=>'myclass', 'type' => 'button','onclick'=>'window.history.back();'));
            }
        } else {
            //displays the form
            echo $OUTPUT->heading(format_string(get_string('welcomestudent1', 'unedtrivial')), 2, null);
            echo get_string('welcomestudent2', 'unedtrivial')."<br>";
            echo get_string('welcomestudent3', 'unedtrivial')."<br>";
            echo get_string('welcomestudent4', 'unedtrivial')."<br><br>";
            $mform->display();
            if ($mform->e != ''){
                echo $OUTPUT->error_text('<b><font color="red">'.$mform->e.'</font></b>');
            }
        }
    }else{
        echo $OUTPUT->heading(get_string('participantmenu','unedtrivial') . $newmodule->name,2,null);
        if ($newmodule->enddate == 60){
            echo "<b>" . get_string('finalization1', 'unedtrivial') . "</b> ".
                 get_string('none','unedtrivial')."<br>";
        }else if(time() < $newmodule->enddate){
            echo "<b>" . get_string('finalization1', 'unedtrivial') . "</b> " . date("d-m-Y",$newmodule->enddate) . "<br>";
        }else{
            echo "<b>" . get_string('finalization2', 'unedtrivial') . "</b><br>";
        }
        $today = strtotime(date("Ymd",time()));
        $totalqu = unedtrivial_get_questions($newmodule->id);
        $closedqu = unedtrivial_get_user_closed_questions($newmodule, $USER->id);
        if ($totalqu == 0){
            $success = 0;
        }else{
            $success = round($closedqu/$totalqu*100);
        }
        echo "<b>". get_string('participantstable3', 'unedtrivial') . ": </b>";
        echo $closedqu . " " . get_string('of', 'unedtrivial') . " " . $totalqu . " " . 
             get_string('closedquestions', 'unedtrivial') . "<br><br>";
        
        $btn = new single_button(new moodle_url('maildestiny.php', 
            array('unedid' => $newmodule->id, 'date' => $today)),
            get_string('gotoquestions', 'unedtrivial'), 'get');
        $questionsfortoday = unedtrivial_locate_questions($newmodule,$USER->id,$today);
        if ($questionsfortoday[0] == -1 || ($newmodule->enddate != 60 && $today >= $newmodule->enddate)){
            $btn->disabled = true;
        }
        echo $OUTPUT->render($btn);
        echo $OUTPUT->single_button(new moodle_url('ranking.php', array('id' => $id)),
            get_string('teacheroption2', 'unedtrivial'), 'get');
        $bDelete = new single_button(new moodle_url('deletemail.php', array('id' => $id)),
                get_string('deletemail','unedtrivial').": ".$query1->mail);
        $bDelete->add_action(new confirm_action(get_string('sure2', 'unedtrivial'), null,get_string('ok', 'unedtrivial')));
        echo $OUTPUT->render($bDelete);
    }
}else{
    echo $OUTPUT->heading(get_string('teachermenu','unedtrivial') . $newmodule->name,2,null);
    echo $OUTPUT->single_button(new moodle_url('teacheroptions.php', array('id' => $id, 'option' => '1')),
            get_string('teacheroption1', 'unedtrivial'), 'get');
    echo $OUTPUT->single_button(new moodle_url('teacheroptions.php', array('id' => $id, 'option' => '2')),
            get_string('teacheroption2', 'unedtrivial'), 'get');
    echo $OUTPUT->single_button(new moodle_url('teacheroptions.php', array('id' => $id, 'option' => '3')),
            get_string('teacheroption3', 'unedtrivial'), 'get');
    echo $OUTPUT->single_button(new moodle_url('stats.php', array('id' => $id)),
            get_string('teacheroption4', 'unedtrivial'), 'get');
}

// Finish the page.
echo $OUTPUT->footer();
