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
 * Teacher page to manage all questions of current UNEDTrivial
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/mod/unedtrivial/searchform.php');
        
global $DB;
const ICON_SI = '<i class="fa fa-dot-circle-o" aria-hidden="true"></i>';
const ICON_MU = '<i class="fa fa-check-square" aria-hidden="true"></i>';
const ICON_SH = '<i class="fa fa-font" aria-hidden="true"></i>';      

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... newmodule instance ID - it should be named as the first character of the module.
$stext = optional_param('stext','',PARAM_TEXT);

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

//Students can't stay here
if (!has_capability('mod/unedtrivial:addinstance', $context)) {
    echo $OUTPUT->header();
    echo get_string('roleerror', 'unedtrivial');
    echo $OUTPUT->footer();
    die();
}

// Output starts here.
echo $OUTPUT->header();

//Look for unedtrivial questions
if ($stext == ''){
    $query1 = $DB->get_records_sql('SELECT u.id,u.qtype,u.question '
            . '                      FROM {unedtrivial_questions} u'
            . '                     WHERE u.idunedtrivial = ?', array($cm->instance));
}else{
    $sql = "SELECT u.id,u.qtype,u.question"
          . " FROM {unedtrivial_questions} u"
          . "  WHERE u.idunedtrivial = ? AND"
          . "        u.question LIKE '%".$stext."%'";
    $query1 = $DB->get_records_sql($sql, array($cm->instance));
}
echo $OUTPUT->heading(get_string('teacheroption1', 'unedtrivial'),2,null);
echo "<br />";
$toform = new stdClass();
$toform->searchtext = $stext;
$mform = new searchform(new moodle_url('managequestions.php',array('id'=>$id)));
$mform->set_data($toform);
if ($fromform = $mform->get_data()) {
    echo $OUTPUT->heading(get_string('searching', 'unedtrivial'),4,null);
    redirect(new moodle_url('managequestions.php',
            array('id'=>$id,'stext'=>$fromform->searchtext)));
}else{
    $mform->display();
}

$table = new html_table();
$table->head = array(get_string('managequestionscol1', 'unedtrivial'),
    get_string('managequestionscol2', 'unedtrivial') ,'','');

foreach($query1 as $row) {   
    $bEdit = new single_button(new moodle_url('editquestion.php', array('id' => $id, 'qtype' => $row->qtype, 'option' => '2', 'qu' => $row->id)),
            get_string('edit', 'unedtrivial'));
    $bDelete = new single_button(new moodle_url('editquestion.php', array('id' => $id, 'qtype' => $row->qtype, 'option' => '3', 'qu' => $row->id)),
            get_string('delete', 'unedtrivial'));
    $bDelete->add_action(new confirm_action(get_string('sure', 'unedtrivial'), null,get_string('ok', 'unedtrivial')));
    $questionplain = filter_var($row->question, FILTER_SANITIZE_STRING);
    if (strlen($questionplain) > 60){
        $questionplain = mb_substr($questionplain,0,60,'UTF-8') . "...";
    }
    if ($row->qtype == '1'){
        //$row->qtype = get_string('singleanswer', 'unedtrivial');
        $row->qtype = '<center>'.ICON_SI.'</center>';
    }else if ($row->qtype == '2'){
        //$row->qtype = get_string('multipleanswer', 'unedtrivial');
         $row->qtype = '<center>'.ICON_MU.'</center>';
    }else if ($row->qtype == '3'){
        //$row->qtype = get_string('shortanswer', 'unedtrivial');
        $row->qtype = '<center>'.ICON_SH.'</center>';
    }
    $table->data[] = array($row->qtype,$questionplain,$OUTPUT->render($bEdit),$OUTPUT->render($bDelete));
}
echo html_writer::table($table);
echo $OUTPUT->heading(get_string('addquestion', 'unedtrivial'),4,null);
$table2 = new html_table();
$table2->data[] = array($OUTPUT->render(new single_button(new moodle_url('editquestion.php', array('id' => $id, 'qtype' => '1', 'option' => '1')),
            get_string('singleanswer', 'unedtrivial'), 'get')),
            $OUTPUT->render(new single_button(new moodle_url('editquestion.php', array('id' => $id, 'qtype' => '2', 'option' => '1')),
            get_string('multipleanswer', 'unedtrivial'), 'get')),
            $OUTPUT->render(new single_button(new moodle_url('editquestion.php', array('id' => $id, 'qtype' => '3', 'option' => '1')),
            get_string('shortanswer', 'unedtrivial'), 'get')));

echo html_writer::table($table2);
echo $OUTPUT->heading(get_string('fileload', 'unedtrivial'),4,null);
echo $OUTPUT->single_button(new moodle_url('loadquestions.php', array('id' => $id)),
            get_string('file', 'unedtrivial'), 'get');

echo "<br /><br />";
echo $OUTPUT->single_button(new moodle_url('view.php', array('id' => $id)),
            get_string('back', 'unedtrivial'), 'get');

// Finish the page.
echo $OUTPUT->footer();