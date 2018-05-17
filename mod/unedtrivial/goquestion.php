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
 * Go to concrete question (to be answered)
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/mod/unedtrivial/goquestion1form.php');
require_once($CFG->dirroot.'/mod/unedtrivial/goquestion2form.php');
require_once($CFG->dirroot.'/mod/unedtrivial/goquestion3form.php');
require_once($CFG->dirroot.'/mod/unedtrivial/question3form.php');
        
GLOBAL $DB;

$key = optional_param('key', 0, PARAM_TEXT); //Encrypted key

//Decrypt the key
$cadena = unedtrivial_decrypt($key);
if ($cadena != false){
    $id = (int)strtok($cadena, "-");
    $qdate = (int)strtok("-");
    $qu = (int)strtok("-");
}else{
    echo "Invalid key";
    die();
}

if ($id) {
    $cm         = get_coursemodule_from_id('unedtrivial', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $newmodule  = $DB->get_record('unedtrivial', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('Instance error');
}

require_login($course, true, $cm);

$event = \mod_unedtrivial\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $newmodule);
$event->trigger();

$reg = $DB->get_record('unedtrivial_questions', array('id' => $qu));
// Print the page header.

$PAGE->set_url('/mod/unedtrivial/goquestion.php', array('key' => $key));
$PAGE->set_title(format_string($newmodule->name));
$PAGE->set_heading(format_string($course->fullname));

// Output starts here.
echo $OUTPUT->header();

if (!$reg){
    //First check: Is a real question number?
    echo $OUTPUT->error_text('<b><font color="red">'.
            get_string('errorgoquestion1', 'unedtrivial').$qu." not found".
            '</font></b><br>');
    echo html_writer::tag('button', get_string('close', 'unedtrivial'), 
                    array('class'=>'myclass', 'type' => 'button','onclick'=>"window.open('', '_self', ''); window.close();"));
}else if (date('Ymd',$qdate) != date('Ymd',time())){
    //Second check: This question is scheduled for today?
    echo '<b><font color="red">'.get_string('errorgoquestion2', 'unedtrivial').'</font></b><br>';
    echo html_writer::tag('button', get_string('close', 'unedtrivial'), 
                    array('class'=>'myclass', 'type' => 'button','onclick'=>"window.open('', '_self', ''); window.close();"));
}else if ($res = unedtrivial_question_valid($newmodule,$USER->id,$qu,$qdate)){
    //Third check: Can be used this question for this user?
    if ($res == 1){
        $msg = get_string('errorgoquestion3', 'unedtrivial');
    }else if ($res == 2){
        $msg = get_string('errorgoquestion4', 'unedtrivial');
    }else if ($res == 3){
        $msg = get_string('errorgoquestion5', 'unedtrivial');
    }
    echo '<b><font color="red">'.$msg.'</font></b><br>';
    echo html_writer::tag('button', get_string('close', 'unedtrivial'), 
                    array('class'=>'myclass', 'type' => 'button','onclick'=>"window.open('', '_self', ''); window.close();"));
}else if ($reg->qtype == 1){
    $mform = new goquestion1form(new moodle_url('goquestion.php', array('key' => $key)),NULL);
    if ($fromform = $mform->get_data()) {
        if (property_exists($mform->get_data(),'istrue') && 
                ($mform->get_data()->istrue == 1 && $mform->get_data()->answer1 == '1' ||
                 $mform->get_data()->istrue == 2 && $mform->get_data()->answer2 == '1' ||
                 $mform->get_data()->istrue == 3 && $mform->get_data()->answer3 == '1' ||
                 $mform->get_data()->istrue == 4 && $mform->get_data()->answer4 == '1')){
            //We save positive points into history of student
            $record = new stdClass();
            $record->idunedtrivial = $cm->instance;
            $record->userid = $USER->id;
            $record->questionid = $qu;
            $record->questionscore = $newmodule->scoresuccess;
            $lastest = unedtrivial_check_question_history($cm->instance, $qu, $USER->id);
            $record->questionstate = max(1,$lastest+1);
            if ($record->questionstate == $newmodule->timestocomplete){
                //Add bonus
                $record->questionscore += unedtrivial_get_bonus($newmodule,$qu,$USER->id,1);
            }
            $record->questiondate = $qdate;
            $DB->insert_record('unedtrivial_history', $record);
            unedtrivial_show_results('success',$record->questionstate,$reg,$newmodule,$cm,$course);
        }else{
            //We save negative points into history of student
            $record = new stdClass();
            $record->idunedtrivial = $cm->instance;
            $record->userid = $USER->id;
            $record->questionid = $qu;
            $record->questionstate = -1;
            $record->questionscore = $newmodule->scoreerror;
            $record->questiondate = $qdate;
            $DB->insert_record('unedtrivial_history', $record);
            unedtrivial_show_results('failure',$record->questionstate,$reg,$newmodule,$cm,$course);
        }
    }else{
        $options = array();
        $answers = array();
        $seed = time();
        if ($reg->option1 != ''){
            $options[] = $reg->option1;
            strpos($reg->answer, '1') !== false ? $answers[] = '1' : $answers[] = '0';
        }
        if ($reg->option2 != ''){
            $options[] = $reg->option2;
            strpos($reg->answer, '2') !== false ? $answers[] = '1' : $answers[] = '0';
        }
        if ($reg->option3 != ''){
            $options[] = $reg->option3;
            strpos($reg->answer, '3') !== false ? $answers[] = '1' : $answers[] = '0';
        }
        if ($reg->option4 != ''){
            $options[] = $reg->option4;
            strpos($reg->answer, '4') !== false ? $answers[] = '1' : $answers[] = '0';
        }
        if ($reg->shuffle){
            $options = unedtrivial_shuffleseed($options, $seed);
            $answers = unedtrivial_shuffleseed($answers, $seed);
        }
        $toform = new stdClass();
        $toform->questiontext = $reg->question;
        for($i=0;$i<count($options);$i++){
            if ($i==0) $mform->o1 = $options[0];
            if ($i==1) $mform->o2 = $options[1];
            if ($i==2) $mform->o3 = $options[2];
            if ($i==3) $mform->o4 = $options[3];
        }
        for($i=0;$i<count($answers);$i++){
            if ($i==0) $toform->answer1 = $answers[0];
            if ($i==1) $toform->answer2 = $answers[1];
            if ($i==2) $toform->answer3 = $answers[2];
            if ($i==3) $toform->answer4 = $answers[3];
        }
        $mform->check = true;
        $mform->set_data($toform);
        echo $OUTPUT->heading(get_string('singleanswer2', 'unedtrivial'), 2, null);
        $mform->display();
    }
}else if ($reg->qtype == 2){
    $mform = new goquestion2form(new moodle_url('goquestion.php', array('key' => $key)),NULL);
    $fromform = $mform->get_data();
    if ($fromform = $mform->get_data()) {
        $percentage = 0;
        //Get number of questions and correct questions of form
        $numans = 0;
        if ($reg->option1 != '') $numans++;
        if ($reg->option2 != '') $numans++;
        if ($reg->option3 != '') $numans++;
        if ($reg->option4 != '') $numans++;
        
        $numcorrect = strlen($reg->answer);
        //Check student answers        
        if(property_exists($mform->get_data(),'istrue1') && $mform->get_data()->answer1 == '1'){
            $percentage += unedtrivial_multichoice($numans, $numcorrect,'SUCCESS');
        }else if (property_exists($mform->get_data(),'istrue1') && $mform->get_data()->answer1 == '0'){
            $percentage += unedtrivial_multichoice($numans, $numcorrect,'FAILURE');
        }
        if(property_exists($mform->get_data(),'istrue2') && $mform->get_data()->answer2 == '1'){
            $percentage += unedtrivial_multichoice($numans, $numcorrect,'SUCCESS');
        }else if(property_exists($mform->get_data(),'istrue2') && $mform->get_data()->answer2 == '0'){
            $percentage += unedtrivial_multichoice($numans, $numcorrect,'FAILURE');
        }
        if(property_exists($mform->get_data(),'istrue3') && $mform->get_data()->answer3 == '1'){
            $percentage += unedtrivial_multichoice($numans, $numcorrect,'SUCCESS');
        }else if(property_exists($mform->get_data(),'istrue3') && $mform->get_data()->answer3 == '0'){
            $percentage += unedtrivial_multichoice($numans, $numcorrect,'FAILURE');
        }
        if(property_exists($mform->get_data(),'istrue4') && $mform->get_data()->answer4 == '1'){
            $percentage += unedtrivial_multichoice($numans, $numcorrect,'SUCCESS');
        }else if(property_exists($mform->get_data(),'istrue4') && $mform->get_data()->answer4 == '0'){
            $percentage += unedtrivial_multichoice($numans, $numcorrect,'FAILURE');
        }
        
        if ($percentage > 0.9){
            $record = new stdClass();
            $record->idunedtrivial = $cm->instance;
            $record->userid = $USER->id;
            $record->questionid = $qu;
            $record->questionscore = $newmodule->scoresuccess;
            $lastest = unedtrivial_check_question_history($cm->instance, $qu, $USER->id);
            $record->questionstate = max(1,$lastest+1);
            if ($record->questionstate == $newmodule->timestocomplete){
                //Add bonus
                $record->questionscore += unedtrivial_get_bonus($newmodule,$qu,$USER->id,1);
            }
            $record->questiondate = $qdate;
            $DB->insert_record('unedtrivial_history', $record);
            unedtrivial_show_results('success',$record->questionstate,$reg,$newmodule,$cm,$course);
        }else{
            $record = new stdClass();
            $record->idunedtrivial = $cm->instance;
            $record->userid = $USER->id;
            $record->questionid = $qu;
            $record->questionscore = $newmodule->scoreerror;
            $record->questionstate = -1;
            $record->questiondate = $qdate;
            $DB->insert_record('unedtrivial_history', $record);
            unedtrivial_show_results('failure',$record->questionstate, $reg,$newmodule,$cm,$course);
        }
    }else{
        $options = array();
        $answers = array();
        $seed = time();
        if ($reg->option1 != ''){
            $options[] = $reg->option1;
            strpos($reg->answer, '1') !== false ? $answers[] = '1' : $answers[] = '0';
        }
        if ($reg->option2 != ''){
            $options[] = $reg->option2;
            strpos($reg->answer, '2') !== false ? $answers[] = '1' : $answers[] = '0';
        }
        if ($reg->option3 != ''){
            $options[] = $reg->option3;
            strpos($reg->answer, '3') !== false ? $answers[] = '1' : $answers[] = '0';
        }
        if ($reg->option4 != ''){
            $options[] = $reg->option4;
            strpos($reg->answer, '4') !== false ? $answers[] = '1' : $answers[] = '0';
        }
        
        if ($reg->shuffle){
            $options = unedtrivial_shuffleseed($options, $seed);
            $answers = unedtrivial_shuffleseed($answers, $seed);
        }
        
        $toform = new stdClass();
        $toform->questiontext = $reg->question;
        for($i=0;$i<count($options);$i++){
            if ($i==0) $mform->o1 = $options[0];
            if ($i==1) $mform->o2 = $options[1];
            if ($i==2) $mform->o3 = $options[2];
            if ($i==3) $mform->o4 = $options[3];
        }
        for($i=0;$i<count($answers);$i++){
            if ($i==0) $toform->answer1 = $answers[0];
            if ($i==1) $toform->answer2 = $answers[1];
            if ($i==2) $toform->answer3 = $answers[2];
            if ($i==3) $toform->answer4 = $answers[3];
        }
        $mform->check = true;
        $mform->set_data($toform);
        echo $OUTPUT->heading(get_string('multipleanswer2', 'unedtrivial'), 2, null);
        $mform->display();
    }
}else if ($reg->qtype == 3){
    $mform = new goquestion3form(new moodle_url('goquestion.php', array('key' => $key)),NULL);
    $fromform = $mform->get_data();
    if ($fromform = $mform->get_data()) {
        $record = new stdClass();
        $record->idunedtrivial = $cm->instance;
        $record->userid = $USER->id;
        $record->questionid = $qu;
        $record->questiondate = $qdate;
        
        $answer_norm = question3form::normaliza($fromform->answer);
        if ($fromform->casesensitive == '0' && strcasecmp($answer_norm, $fromform->solution) == 0 ||
                $fromform->casesensitive == '1' && $answer_norm == $fromform->solution){
            $record->questionscore = $newmodule->scoresuccess;
            $lastest = unedtrivial_check_question_history($cm->instance, $qu, $USER->id);
            $record->questionstate = max(1,$lastest+1);
            if ($record->questionstate == $newmodule->timestocomplete){
                //Add bonus
                $record->questionscore += unedtrivial_get_bonus($newmodule,$qu,$USER->id,1);
            }
            $DB->insert_record('unedtrivial_history', $record);
            unedtrivial_show_results('success',$record->questionstate,$reg,$newmodule,$cm,$course);
        }else{
            $record->questionscore = $newmodule->scoreerror;
            $record->questionstate = -1;
            $DB->insert_record('unedtrivial_history', $record);
            unedtrivial_show_results('failure',$record->questionstate, $reg,$newmodule,$cm,$course);
        }
    }else{
        $toform = new stdClass();
        $toform->questiontext = $reg->question;
        $toform->solution = $reg->option1;
        $toform->casesensitive = $reg->answer;
        $mform->set_data($toform);
        echo $OUTPUT->heading(get_string('shortanswer2', 'unedtrivial'), 2, null);
        $mform->display();
    }
}

// Finish the page.
echo $OUTPUT->footer();
