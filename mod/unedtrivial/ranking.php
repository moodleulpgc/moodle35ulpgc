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
 * Students' ranking
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

GLOBAL $DB;

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... newmodule instance ID - it should be named as the first character of the module.
		
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

$PAGE->set_url('/mod/unedtrivial/ranking.php', array('id' => $cm->id));
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
//See highscores
echo $OUTPUT->heading(get_string('leaderboard', 'unedtrivial'), 2, null);
$allrows = $DB->get_records_sql('SELECT u.id,u.firstname,u.lastname,SUM(n.questionscore) AS sum '
                . '                        FROM {user} u'
                . '                        JOIN {unedtrivial_history} n ON n.userid = u.id '
                . '                       WHERE n.idunedtrivial = ? '
                . '                    GROUP BY u.id,u.firstname,u.lastname'
                . '                    ORDER BY sum DESC', array($cm->instance));
$table = new html_table();
$table->head = array(get_string('leaderboardcol1', 'unedtrivial'),
                     get_string('leaderboardcol2', 'unedtrivial'),
                     get_string('leaderboardcol3', 'unedtrivial'));
$i = 0;
foreach($allrows as $row) {   
    $i = $i + 1;
    if ($row->id == $USER->id){
            $table->data[] = array($i, 
                                   html_writer::link(new moodle_url('studentinfo.php', 
                                                     array('id'=>$id,'instance'=>$cm->instance,'student'=>$row->id)), 
                                                     $row->firstname . " " . $row->lastname), 
                                   $row->sum);
    }else{
            $table->data[] = array($i, $row->firstname . " " . $row->lastname, $row->sum);
    }
}
if (count($allrows) == 0){
    echo "<br><i>".get_string('notableresults','unedtrivial')."</i>";
}else{
    echo get_string('leaderboardinstr', 'unedtrivial');
    echo html_writer::table($table);
}
echo $OUTPUT->single_button(new moodle_url('view.php', array('id' => $id)),
            get_string('back', 'unedtrivial'), 'get');
// Finish the page.
echo $OUTPUT->footer();
