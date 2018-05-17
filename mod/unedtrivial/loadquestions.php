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
 * Load questions from an external file to UNEDTrivial
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/mod/unedtrivial/loadform.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot.'/mod/unedtrivial/question3form.php');
require_once($CFG->dirroot.'/mod/unedtrivial/Encoding.php');

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

$PAGE->set_url('/mod/unedtrivial/loadquestions.php', array('id' => $id));
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

// PAGE
$mform = new loadform(new moodle_url('loadquestions.php', array('id' => $id)),NULL);
$fromform = $mform->get_data();
if ($mform->is_cancelled()){
    redirect(new moodle_url('managequestions.php', array('id' => $id)));
}else if ($fromform != null) {
    $name = $mform->get_new_filename('userfile');
    if (stripos($name, ".xml")){
        //XML files are considered Moodle XML format files
        $xml = simplexml_load_string($mform->get_file_content('userfile'));
        $preguntas = $xml->quiz;
        $records = array();
        foreach($xml->question as $qu){
            //Categories are not used
            if ($qu['type'] != 'category'){
                $record = new stdClass();
                $record->idunedtrivial = $cm->instance;
                $record->qtype = "";
                $record->question = "";
                $record->option1 = "";
                $record->option2 = "";
                $record->option3 = "";
                $record->option4 = "";
                $record->answer = "";
                $record->explanation = "";
                $record->shuffle = 0;
                if ($qu['type'] == 'shortanswer'){
                    //SHORT ANSWER
                    $record->qtype = 3;
                    $record->question = (string)$qu->questiontext->text[0];
                    $record->option1 = filter_var($qu->answer->text[0], FILTER_SANITIZE_STRING);
                    if ($qu->usecase == '1'){
                        $record->answer = 1;
                    }
                    $record->explanation = (string)$qu->generalfeedback->text[0];
                    $records[] = $record;
                }else if ($qu['type'] == 'multichoice'){
                    if ($qu->single == "true"){
                        //SINGLE CHOICE ANSWER
                        $record->qtype = 1;
                        $record->question = (string)$qu->questiontext->text[0];
                        $i = 1;
                        foreach ($qu->answer as $answer){
                            if ($i == 1){
                                $record->option1 = filter_var($answer->text[0], FILTER_SANITIZE_STRING);
                            }else if ($i == 2){
                                $record->option2 = filter_var($answer->text[0], FILTER_SANITIZE_STRING);
                            }else if ($i == 3){
                                $record->option3 = filter_var($answer->text[0], FILTER_SANITIZE_STRING);
                            }else if ($i == 4){
                                $record->option4 = filter_var($answer->text[0], FILTER_SANITIZE_STRING);
                            }
                            if ($answer['fraction'] == "100"){
                                $record->answer = $i;
                            }
                            $i++;
                        }
                        if ($qu->shuffleanswers == "true"){
                            $record->shuffle = 1;
                        }
                        $record->explanation = (string)$qu->generalfeedback->text[0];
                        $records[] = $record;
                    }else{
                        //MULTIPLE CHOICE ANSWER
                        $record->qtype = 2;
                        $record->question = (string)$qu->questiontext->text[0];
                        $i = 1;
                        $record->answer = "";
                        foreach ($qu->answer as $answer){
                            if ($i == 1){
                                $record->option1 = filter_var($answer->text[0], FILTER_SANITIZE_STRING);
                            }else if ($i == 2){
                                $record->option2 = filter_var($answer->text[0], FILTER_SANITIZE_STRING);
                            }else if ($i == 3){
                                $record->option3 = filter_var($answer->text[0], FILTER_SANITIZE_STRING);
                            }else if ($i == 4){
                                $record->option4 = filter_var($answer->text[0], FILTER_SANITIZE_STRING);
                            }
                            if ($answer['fraction'] > 0){
                                $record->answer = $record->answer . $i;
                            }
                            $i++;
                        }
                        if ($qu->shuffleanswers == "true"){
                            $record->shuffle = 1;
                        }
                        $record->explanation = (string)$qu->generalfeedback->text[0];
                        $records[] = $record;
                    }
                }
            }
        }
        $DB->insert_records('unedtrivial_questions', $records);
        echo get_string('fileloadsuccess1', 'unedtrivial') . "<br>";
        echo get_string('fileloadsuccess2', 'unedtrivial') . ":" . count($records) . "<br><br>";
        echo $OUTPUT->single_button(new moodle_url('managequestions.php', array('id' => $id)),
            get_string('back', 'unedtrivial'), 'get');
    }else{
        //In other case, the file is considered as internal UNEDTrivial format
        $content = $mform->get_file_content('userfile');
        $content = Encoding::toUTF8($content);
        $content = preg_replace('/[^\P{C}\n]+/u', '', $content);
        $tok = strtok($content, "\n");
        $error = false;
        $eof = !($tok !== false);
        $records = array();
        while (!$eof && !$error) {
            $record = new stdClass();
            $record->idunedtrivial = $cm->instance;
            if (strcasecmp(substr($tok,0,2), "SI") == 0){
                //Single answer question
                $record->qtype = 1;
                $record->question = trim(substr($tok,2));
                $tok = strtok("\n");
                if(strcasecmp(substr($tok,0,1), "1") == 0){
                    $record->option1 = trim(substr($tok,2));
                }else{
                    $error = true; break;
                }
                $tok = strtok("\n");
                if(strcasecmp(substr($tok,0,1), "2") == 0){
                    $record->option2 = trim(substr($tok,2));
                }else{
                    $error = true; break;
                }
                $tok = strtok("\n");
                if(strcasecmp(substr($tok,0,1), "3") == 0){
                    $record->option3 = trim(substr($tok,2));
                    $tok = strtok("\n");
                }else{
                    $record->option3 = '';
                }
                if(strcasecmp(substr($tok,0,1), "4") == 0){
                    $record->option4 = trim(substr($tok,2));
                    $tok = strtok("\n");
                }else{
                    $record->option4 = '';
                }
                if(strcasecmp(substr($tok,0,3), "SHU") == 0){
                    $record->shuffle = 1;
                    $tok = strtok("\n");
                }else{
                    $record->shuffle = 0;
                }
                if(strcasecmp(substr($tok,0,2), "AN") == 0){
                    $record->answer = trim(substr($tok,2));
                    if ($record->answer != '1' && $record->answer != '2' && $record->answer != '3' &&
                        $record->answer != '4'){
                        $error = true; break;
                    }
                }else{
                    $error = true; break;
                }
                $tok = strtok("\n");
                if(strcasecmp(substr($tok,0,2), "EX") == 0){
                    $record->explanation = trim(substr($tok,2));
                    //Question ready --> insert into questions array
                    $records[] = $record;
                }else{
                    $error = true; break;
                }
            }else if (strcasecmp(substr($tok,0,2), "MU") == 0){
                //Multiple answer question
                $record->qtype = 2;
                $record->question = trim(substr($tok,2));
                $tok = strtok("\n");
                if(strcasecmp(substr($tok,0,1), "1") == 0){
                    $record->option1 = trim(substr($tok,2));
                }else{
                    $error = true; break;
                }
                $tok = strtok("\n");
                if(strcasecmp(substr($tok,0,1), "2") == 0){
                    $record->option2 = trim(substr($tok,2));
                }else{
                     $error = true; break;
                }
                $tok = strtok("\n");
                if(strcasecmp(substr($tok,0,1), "3") == 0){
                    $record->option3 = trim(substr($tok,2));
                    $tok = strtok("\n");
                }else{
                    $record->option3 = '';
                }
                if(strcasecmp(substr($tok,0,1), "4") == 0){
                    $record->option4 = trim(substr($tok,2));
                    $tok = strtok("\n");
                }else{
                    $record->option4 = '';
                }
                if(strcasecmp(substr($tok,0,3), "SHU") == 0){
                    $record->shuffle = 1;    
                    $tok = strtok("\n");
                }else{
                    $record->shuffle = 0;
                }
                if(strcasecmp(substr($tok,0,2), "AN") == 0){
                    $record->answer = trim(substr($tok,2));
                    if ($record->answer == false){
                        $error = true; break;
                    }else{
                        for($i=0;$i<strlen($record->answer);$i++){
                            if ($record->answer{$i} != '1' && $record->answer{$i} != '2' && 
                                $record->answer{$i} != '3' && $record->answer{$i} != '4'){
                                $error = true; break;
                            }
                        }
                    }
                }else{
                    $error = true; break;
                }
                $tok = strtok("\n");
                if(strcasecmp(substr($tok,0,2), "EX") == 0){
                    $record->explanation = trim(substr($tok,2));
                    //Question ready --> insert into questions array
                    $records[] = $record;
                }else{
                    $error = true; break;
                }
            }else if (strcasecmp(substr($tok,0,2), "SH") == 0){
                //Short answer question
                $record->qtype = 3;
                $record->question = trim(substr($tok,2));
                $tok = strtok("\n");
                if (strcasecmp(substr($tok,0,1), "1") == 0){
                    $noblanks = trim(substr($tok,2));
                    $record->option1 = question3form::normaliza($noblanks);
                    $record->option2 = '';
                    $record->option3 = '';
                    $record->option4 = '';
                }else{
                    $error = true; break;
                }
                $record->shuffle = 0;
                $tok = strtok("\n");
                if(strcasecmp(substr($tok,0,2), "AN") == 0){
                    $casesen = trim(substr($tok,2));
                    if (strcasecmp($casesen, "X") == 0){
                        $record->answer = 1;
                    }else{
                        $record->answer = 0;
                    }
                }else{
                    $error = true; break;
                }
                $tok = strtok("\n");
                if(strcasecmp(substr($tok,0,2), "EX") == 0){
                    $record->explanation = trim(substr($tok,2));
                    //Question ready --> insert into questions array
                    $records[] = $record;
                }else{
                    $error = true; break;
                }
            }else{
                $error = true; break;
            }
            $tok = strtok("\n");
            $eof = !($tok !== false);
            while(strlen($tok) <= 2 && !$eof){
                $tok = strtok("\n");
                $eof = !($tok !== false);
            }
        }
        if ($error){
            echo get_string('fileloaderror', 'unedtrivial') ." ". count($records)+1 ."<br>";
            echo "Token: ".$tok;
            echo $OUTPUT->single_button(new moodle_url('loadquestions.php', array('id' => $id)),
                get_string('back', 'unedtrivial'), 'get');
        }else{
            $DB->insert_records('unedtrivial_questions', $records);
            echo get_string('fileloadsuccess1', 'unedtrivial') . "<br>";
            echo get_string('fileloadsuccess2', 'unedtrivial') . ":" . count($records) . "<br><br>";
            echo $OUTPUT->single_button(new moodle_url('managequestions.php', array('id' => $id)),
                get_string('back', 'unedtrivial'), 'get');
        }
    }
}else{
    //displays the form
    echo $OUTPUT->heading(get_string('fileload','unedtrivial'), 2, null);
    echo get_string('uploadinstr1','unedtrivial') . "<br>"; 
    echo get_string('uploadinstr2','unedtrivial') . "<br>";
    if (current_language() == 'es'){
        $link = new moodle_url('/mod/unedtrivial/docs/guia_esp.pdf');
        echo get_string('uploadinstr3','unedtrivial'). " " . '<a href="' . $link . '">' . 
                "aqu&iacute;" . '</a><br><br>'; 
    }else{
        $link = new moodle_url('/mod/unedtrivial/docs/guide_en.pdf');
        echo get_string('uploadinstr3','unedtrivial'). " " . '<a href="' . $link . '">' . 
                "here" . '</a><br><br>'; 
    }
            
    $mform->display();
}

// Finish the page.
echo $OUTPUT->footer();
