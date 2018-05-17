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
 * Email links always redirects to this page (including mandatory parameter 'key')
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/message/lib.php');

$unedid = required_param('unedid', PARAM_INT); // Course.
$date = required_param('date', PARAM_INT); // UNIX date

$newmodule  = $DB->get_record('unedtrivial', array('id' => $unedid), '*', MUST_EXIST);
$course     = $DB->get_record('course', array('id' => $newmodule->course), '*', MUST_EXIST);
$cm         = get_coursemodule_from_instance('unedtrivial', $newmodule->id, $course->id, false, MUST_EXIST);

require_course_login($course);
//
//$event = \mod_unedtrivial\event\course_module_viewed::create(array(
//    'objectid' => $PAGE->cm->instance,
//    'context' => $PAGE->context,
//));
//$event->add_record_snapshot('course', $PAGE->course);
//$event->add_record_snapshot($PAGE->cm->modname, $newmodule);
//$event->trigger();

$strname = get_string('modulenameplural', 'mod_unedtrivial');
$PAGE->set_url('/mod/unedtrivial/maildestiny.php', array('unedid' => $unedid, 'date' => $date));
$PAGE->navbar->add($strname);
$PAGE->set_title("$course->shortname: $strname");
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

$email = $DB->record_exists('unedtrivial_mails', array('idunedtrivial' => $unedid, 'userid' => $USER->id));

echo $OUTPUT->header();

if (($newmodule->enddate == 60 || $newmodule->enddate > time() && date("Ymd",time()) == date("Ymd",$date)) 
        && $email){
    //Date is correct
    echo $OUTPUT->heading(get_string('yourquestions', 'unedtrivial'), 2, null);
    echo "<br>";
    $selected = unedtrivial_locate_questions($newmodule,$USER->id,$date);
    $selected = array_values($selected);
    for($i=0;$i<$newmodule->questionsperday;$i++){
        if ($selected[$i] != -1){
            $key = unedtrivial_encrypt($cm->id."-".$date."-".$selected[$i]);
            echo '<p><a href="'.new moodle_url("goquestion.php", array('key' => $key)).'" target="_blank">'
                    . get_string('question', 'unedtrivial'). " ". ($i+1) .'</a></p>';
        }else{
            echo '<p><font color="gray">'.get_string('questionclosed2', 'unedtrivial').'</font></p>';
        }
    }
}else if (!$email){
    //Email not registered in this UNEDTrivial
    echo $OUTPUT->error_text('<font color="red">'. get_string('notanuser', 'unedtrivial') .'</font><br>');
    echo html_writer::tag('button', get_string('close', 'unedtrivial'), 
                    array('class'=>'myclass', 'type' => 'button','onclick'=>"window.open('', '_self', ''); window.close();"));
}else{
    //Date is incorrect
    echo $OUTPUT->error_text('<font color="red">'. get_string('linkexpired', 'unedtrivial') .'</font><br>');
    echo html_writer::tag('button', get_string('close', 'unedtrivial'), 
                    array('class'=>'myclass', 'type' => 'button','onclick'=>"window.open('', '_self', ''); window.close();"));
}
echo "<br>";
echo $OUTPUT->single_button(new moodle_url('view.php', array('id' => $cm->id)),
            get_string('gotoparticipantmenu', 'unedtrivial'), 'get');
echo $OUTPUT->footer();
