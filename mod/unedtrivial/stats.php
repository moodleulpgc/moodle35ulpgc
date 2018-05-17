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
 * Analytics page
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once ($CFG->libdir . '/csvlib.class.php');
require_once($CFG->dirroot.'/mod/unedtrivial/activitylevelform.php');
        
global $DB;

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... newmodule instance ID - it should be named as the first character of the module.
$download = optional_param('download', 0, PARAM_INT); //Download of CSV stats file
$option = optional_param('option', 0, PARAM_INT); //Download of CSV stats file
$period = optional_param('period', 1, PARAM_INT); //Period (for Activity level tab)
$sort = optional_param('sort',0,PARAM_INT); //Sort column

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

//Students can't stay here
if (!has_capability('mod/unedtrivial:addinstance', $context)) {
    echo $OUTPUT->header();
    echo get_string('roleerror', 'unedtrivial');
    echo $OUTPUT->footer();
    die();
}

if ($download){
    $downloadfilename = clean_filename("unedtrivial_history");
    $csvexport = new csv_export_writer('semicolon');
    $csvexport->set_filename($downloadfilename);
    $regs = $DB->get_records_sql('SELECT h.id,u.firstname,u.lastname,q.question,h.questionstate,'
            . '                          h.questionscore,h.questiondate '
            . '                     FROM {unedtrivial_history} h,'
            . '                          {user} u,'
            . '                          {unedtrivial_questions} q'
            . '                    WHERE h.idunedtrivial = ? AND'
            . '                          u.id = h.userid AND'
            . '                          q.id = h.questionid'
            . '                 ORDER BY h.id', array($newmodule->id));
    
    $userdata = array();
    foreach($regs as $reg){
        //Windows friendly format by default
        $reg->questiondate = date('d-m-Y',$reg->questiondate);
        $userdata[] = array(iconv("UTF-8", "ISO-8859-1",$reg->firstname . " " . $reg->lastname),
                            iconv("UTF-8", "ISO-8859-1", filter_var($reg->question, FILTER_SANITIZE_STRING)),
                            $reg->questionstate,
                            $reg->questionscore, 
                            $reg->questiondate);
    }

    // Print names of all the fields
    $fieldnames = array (
                    'user',
                    'question',
                    'question state',
                    'question score',
                    'question date'
    );

    $exporttitle = array ();
    foreach ($fieldnames as $field){
        $exporttitle [] = $field;
    }

    // add the header line to the data
    $csvexport->add_data($exporttitle);

    // Print all the lines of data.
    foreach ($userdata as $userline){
        $csvexport->add_data($userline);
    }

    // let him serve the csv-file
    $csvexport->download_file();
}
//Section tabs
$tabs = array(array(
    new tabobject('overview', new moodle_url('stats.php', array('id' => $id, 'option' => 0)),
            get_string('overviewtab','unedtrivial')),
    new tabobject('activitylevel', new moodle_url('stats.php', array('id' => $id, 'option' => 3)),
            get_string('activityleveltab','unedtrivial')),
    new tabobject('participants', new moodle_url('stats.php', array('id' => $id, 'option' => 1)),
            get_string('participantstab','unedtrivial')),
    new tabobject('questions', new moodle_url('stats.php', array('id' => $id, 'option' => 2)), 
            get_string('questionstab','unedtrivial')),
));
if ($option == 0){
    $activetab = 'overview';
}else if ($option == 1){
    $activetab = 'participants';
}else if ($option == 2){
    $activetab = 'questions';
}else if ($option == 3){
    $activetab = 'activitylevel';
}

// Output starts here.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('teacheroption4', 'unedtrivial'),2,null);
echo $OUTPUT->single_button(new moodle_url('stats.php', array('id' => $id, 'download' => '1')),
            get_string('downloadcsv', 'unedtrivial'), 'get');
print_tabs($tabs, $activetab);
if ($option == 0){
    //////////////TAB OVERVIEW
    $history = $DB->get_records_sql('SELECT h.questiondate'
            . '                        FROM {unedtrivial_history} h'
            . '                       WHERE h.idunedtrivial = ? AND'
            . '                             h.questionid > 0'
            . '                       LIMIT 1', array($newmodule->id));
    $origin = unedtrivial_get_activity_origin($history,$newmodule->timecreated);
    echo get_string('overview1','unedtrivial')." ". date("d-m-Y", $origin) . "<br>";
    echo get_string('overview2','unedtrivial')." ". unedtrivial_get_participants($newmodule->id) . "<br>";
    echo get_string('overview3','unedtrivial')." ". unedtrivial_get_questions($newmodule->id) . "<br>";
    echo get_string('overview4','unedtrivial')." ". unedtrivial_get_answers($newmodule->id) . "<br>";
    echo get_string('overview5','unedtrivial')." ". unedtrivial_get_difficulty($newmodule->id) . "% " .
         get_string('overview5_1','unedtrivial')."<br>";
    
    unedtrivial_chart_overview_progress($newmodule,$chart1,$table1,$cm->id);
    echo $OUTPUT->heading(get_string('overviewchart1','unedtrivial'),4,null);
    if ($chart1 != null) echo $OUTPUT->render_chart($chart1,false);
    echo html_writer::table($table1);
    echo "<br>";
    $index = 0;
    unedtrivial_chart_overview_knowledge($newmodule,$chart2,$table2,$index);
    echo $OUTPUT->heading(get_string('overviewchart2','unedtrivial'),4,null);
    if ($chart2 != null) echo $OUTPUT->render_chart($chart2,false);
    echo html_writer::table($table2);
    echo "<b>" . get_string('improvementindex','unedtrivial') . $index . "%</b><br>";
}else if ($option == 1){
    //////////////TAB PARTICIPANTS
    $table = unedtrivial_table_participants($newmodule,$cm->id,$sort);
    echo html_writer::table($table);
}else if ($option == 2){
    //////////////TAB QUESTIONS
    $table = unedtrivial_table_questions($newmodule,$id,$sort);
    echo html_writer::table($table);
    echo "<i>".get_string('questionsexpl2','unedtrivial')."</i><br>";
    echo "<i>".get_string('questionsexpl3','unedtrivial')."</i><br>";
    echo "<i>".get_string('questionsexpl3b','unedtrivial')."</i><br>";
    echo "<i>".get_string('questionsexpl4','unedtrivial')."</i><br>";
    echo "<i>".get_string('questionsexpl5','unedtrivial')."</i><br>";
    echo "<i>".get_string('questionsexpl6','unedtrivial')."</i><br>";
    echo "<i>".get_string('questionsexpl7','unedtrivial')."</i><br>";
}else if ($option == 3){
    /////////////TAB ACTIVITY LEVEL
    $toform = new stdClass();
    $toform->period = $period;
    $mform = new activitylevelform(new moodle_url('stats.php',array('id' => $id,'option' => $option)),null);
    $mform->set_data($toform);
    if ($fromform = $mform->get_data()){
        echo $OUTPUT->heading(get_string('searching', 'unedtrivial'),4,null);
        redirect(new moodle_url('stats.php',array('id'=>$id,'option'=>$option,'period'=>$fromform->period)));
    }else{
        $mform->display();
    }
    $today = strtotime(date("Ymd",time()));
    $history = $DB->get_records_sql('SELECT h.questiondate'
            . '                        FROM {unedtrivial_history} h'
            . '                       WHERE h.idunedtrivial = ? AND'
            . '                             h.questionid > 0'
            . '                       LIMIT 1', array($newmodule->id));
    $origin = unedtrivial_get_activity_origin($history,$newmodule->timecreated);
    switch ($period) {
        case 1:
            $initime = $origin; break;
        case 2:
            $initime = $today - 7*86400; break;
        case 3:
            $initime = $today - 15*86400; break;
        case 4:
            $initime = $today - 21*86400; break;
        case 5:
            $initime = $today - 30*86400; break;
    }
    $initime = max($initime,$origin);
    $notstarted = $giveuprisk = $progressing = $completed = 0;
    unedtrivial_get_users_progress($newmodule,$notstarted,$giveuprisk,$progressing,$completed);
    $questionsinperiod = floor(($today-$initime)/86400) * $newmodule->questionsperday
                         * ($giveuprisk+$progressing);
    if ($questionsinperiod == 0) $questionsinperiod = $newmodule->questionsperday;
    echo get_string('activity2', 'unedtrivial') . $questionsinperiod . "<br>";
    $questionsanswered = unedtrivial_get_answers($newmodule->id, $initime, time());
    echo get_string('activity3', 'unedtrivial') . $questionsanswered . "<br>";
    $rate = round($questionsanswered/$questionsinperiod*100);
    echo get_string('activity4', 'unedtrivial') . $rate . "%<br><br>";
    unedtrivial_chart_activity($newmodule,$chart3,$table3,$initime,$cm->id,$period);
    echo $OUTPUT->heading(get_string('activityleveltab','unedtrivial'),4,null);
    if ($chart3 != null) echo $OUTPUT->render_chart($chart3,false);
    echo html_writer::table($table3);
}
echo "<br>";
echo $OUTPUT->single_button(new moodle_url('view.php', array('id' => $id)),
            get_string('back', 'unedtrivial'), 'get');
// Finish the page.
echo $OUTPUT->footer();