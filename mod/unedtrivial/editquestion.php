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
 * Teacher edition of a question
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/mod/unedtrivial/question1form.php');
require_once($CFG->dirroot.'/mod/unedtrivial/question2form.php');
require_once($CFG->dirroot.'/mod/unedtrivial/question3form.php');

GLOBAL $DB;

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, 
$qtype = optional_param('qtype', 0, PARAM_INT);
$option = optional_param('option', 0, PARAM_INT);
$qu = optional_param('qu', 0, PARAM_INT);
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

$PAGE->set_url('/mod/unedtrivial/editquestion.php', array('id' => $cm->id));
$PAGE->set_title(format_string($newmodule->name));
$PAGE->set_heading(format_string($course->fullname));

//Students can't stay here
if (!has_capability('mod/unedtrivial:addinstance', $context)) {
    echo $OUTPUT->header();
    echo get_string('roleerror', 'unedtrivial');
    echo $OUTPUT->footer();
    die();
}
 
// Output starts here.
echo $OUTPUT->header();
if ($option == 3){
    //Delete question
    echo $OUTPUT->heading(get_string('questiondeleted', 'unedtrivial'), 2, null);
    $DB->delete_records('unedtrivial_questions',array('id'=>$qu));
    $DB->delete_records('unedtrivial_history',array('idunedtrivial' => $newmodule->id,'questionid' => $qu));
    redirect(new moodle_url('managequestions.php', array('id' => $id)));
}else{
    // Insert/modify question
    if ($qtype == 1){
        $mform = new question1form(new moodle_url('editquestion.php', array('id' => $id, 'qtype' => $qtype, 'option' => $option, 'qu' => $qu)),NULL);
        $fromform = $mform->get_data();
        $heading = get_string('editquestionsa', 'unedtrivial');
        if ($option == 2){
            //Load info from DB
            $reg = $DB->get_record('unedtrivial_questions', array('id' => $qu));
            $toform = new stdClass();
            $toform->question['text'] = $reg->question;
            $toform->option1 = $reg->option1;
            $toform->option2 = $reg->option2;
            $toform->option3 = $reg->option3;
            $toform->option4 = $reg->option4;
            $toform->istrue = $reg->answer;
            $toform->explanation['text'] = $reg->explanation;
            $toform->shuffle = $reg->shuffle;
            
            $mform->set_data($toform);
        }
    }else if ($qtype == 2){
        $mform = new question2form(new moodle_url('editquestion.php', array('id' => $id, 'qtype' => $qtype, 'option' => $option, 'qu' => $qu)),NULL);
        $fromform = $mform->get_data();
        $heading = get_string('editquestionma', 'unedtrivial');
        if ($option == 2){
            //Load info from DB
            $reg = $DB->get_record('unedtrivial_questions', array('id' => $qu));
            $toform = new stdClass();
            $toform->question['text'] = $reg->question;
            $toform->option1 = $reg->option1;
            $toform->option2 = $reg->option2;
            $toform->option3 = $reg->option3;
            $toform->option4 = $reg->option4;
            $toform->explanation['text'] = $reg->explanation;
            $toform->shuffle = $reg->shuffle;
            if(strpos($reg->answer,'1') !== false){
                $toform->istrue1 = true;
            }
            if(strpos($reg->answer,'2') !== false){
                $toform->istrue2 = true;
            }
            if(strpos($reg->answer,'3') !== false){
                $toform->istrue3 = true;
            }
            if(strpos($reg->answer,'4') !== false){
                $toform->istrue4 = true;
            }
            
            $mform->set_data($toform);
        }
    }else if ($qtype == 3){
        $mform = new question3form(new moodle_url('editquestion.php', array('id' => $id, 'qtype' => $qtype, 'option' => $option, 'qu' => $qu)),NULL);
        $fromform = $mform->get_data();
        $heading = get_string('editquestionsha', 'unedtrivial');
        if ($option == 2){
            //Load info from DB
            $reg = $DB->get_record('unedtrivial_questions', array('id' => $qu));
            $toform = new stdClass();
            $toform->question['text'] = $reg->question;
            $toform->option1 = $reg->option1;
            $toform->casesensitive = $reg->answer;
            $toform->explanation['text'] = $reg->explanation;
            $mform->set_data($toform);
        }
    }
    //Form processing and displaying is done here
    if ($mform->is_cancelled()){
        redirect(new moodle_url('managequestions.php', array('id' => $id)));
    }else if ($fromform != null && $fromform->question != '') {
        //In this case you process validated data. $mform->get_data() returns data posted in form.
        $record = new stdClass();
        $record->idunedtrivial = $cm->instance;
        $record->qtype = $qtype;
        $record->question = $mform->get_data()->question['text'];
        $record->option1 = $mform->get_data()->option1;
        $record->explanation = $mform->get_data()->explanation['text'];
        if ($qtype == 1){
            $record->option2 = $mform->get_data()->option2;
            $record->option3 = $mform->get_data()->option3;
            $record->option4 = $mform->get_data()->option4;
            $record->answer = $mform->get_data()->istrue;
            if (property_exists($mform->get_data(),'shuffle')){
                $record->shuffle = $mform->get_data()->shuffle;
            }else{
                $record->shuffle = 0;
            }
        }else if($qtype == 2){
            $record->option2 = $mform->get_data()->option2;
            $record->option3 = $mform->get_data()->option3;
            $record->option4 = $mform->get_data()->option4;
            if (property_exists($mform->get_data(),'shuffle')){
                $record->shuffle = $mform->get_data()->shuffle;
            }else{
                $record->shuffle = 0;
            }
            $record->answer = "";
            if (property_exists($mform->get_data(),'istrue1')){
                $record->answer = $record->answer . '1';
            }
            if (property_exists($mform->get_data(),'istrue2')){
                $record->answer = $record->answer . '2';
            }
            if (property_exists($mform->get_data(),'istrue3')){
                $record->answer = $record->answer . '3';
            }
            if (property_exists($mform->get_data(),'istrue4')){
                $record->answer = $record->answer . '4';
            }
        }else if ($qtype == 3){
            $record->option1 = question3form::normaliza($record->option1);
            if (property_exists($mform->get_data(),'casesensitive')){
                $record->answer = 1;
            }else{
                $record->answer = 0;
            }
            $record->option2 = '';
            $record->option3 = '';
            $record->option4 = '';
            $record->shuffle = 0;
        }
        if ($option == 1){
            $DB->insert_record('unedtrivial_questions', $record, false);
        }else if ($option == 2){
            $record->id = $qu;
            $DB->update_record('unedtrivial_questions', $record, false);
        }
        redirect(new moodle_url('managequestions.php', array('id' => $id)));
    } else {
        //displays the form
        if ($mform->e != ''){
            echo $OUTPUT->error_text('<b><font color="red">'.$mform->e.'</font></b>');
        }
        echo $OUTPUT->heading($heading, 2, null);
        $mform->display();
    }
}
// Finish the page.
echo $OUTPUT->footer();