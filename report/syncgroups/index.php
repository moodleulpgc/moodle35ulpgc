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
 * Script for synchronizing group membership in a course.
 *
 * @package   report_syncgroups
 * @copyright 2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/grouplib.php');
require_once('locallib.php');

$id = required_param('id', PARAM_INT);       // course id
$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
$context = context_course::instance($course->id);

// needed to setup proper $COURSE
require_login($course);
//setting page url
$PAGE->set_url('/report/syncgroups/index.php', array('id' => $id));
//setting page layout to report
$PAGE->set_pagelayout('report');
//coursecontext instance
$coursecontext = context_course::instance($course->id);
//checking if user is capable of viewing this report in $coursecontext
require_capability('report/syncgroups:view', $coursecontext);
//strings
$strgroupreport = get_string('syncgroups' , 'report_syncgroups');

//setting page title and page heading
$PAGE->set_title($course->shortname .': '. $strgroupreport);
$PAGE->set_heading($course->fullname);
//Displaying header and heading
echo $OUTPUT->header();
echo $OUTPUT->heading($strgroupreport);

$strtargetgroup  = get_string('targetgroup', 'report_syncgroups');
$strparentgroups = get_string('parentgroups', 'report_syncgroups');
$srtnewsynczing  = get_string('newsync', 'report_syncgroups');
$strusers        = get_string('users');
$stredit         = get_string('edit');
$strdelete       = get_string('delete');
$strvisible      = get_string('show');

$table = new html_table();
$table->head  = array($strtargetgroup, $strparentgroups, $strusers, $stredit);
$table->size  = array('30%', '55%', '5%','10%');
$table->align = array('left', 'left', 'center', 'center');
$table->width = '90%';

$data = array();

syncgroups_sync($course->id); // perfom all group membership synchronizations on loading page

$sql = "SELECT gs.*, g.name AS targetname
            FROM {groups_syncgroups} gs
            JOIN {groups} g ON (g.id = gs.targetgroup AND g.courseid = gs.course)
        WHERE gs.course = :course
        ORDER BY targetname ASC";
$groupsynczings = $DB->get_records_sql($sql, array('course'=>$course->id));

$groupurl = '/group/members.php';
foreach($groupsynczings as $sid => $synczing) {
        $attribute = array();
        if(!$synczing->visible) {
            $attribute['class'] = 'dimmed';
        }
        $line = array();
        $name = format_string($synczing->targetname);
        $link = new moodle_url($groupurl, array('id'=>$course->id, 'group'=>$synczing->targetgroup));
        $line[0] = html_writer::link($link, $name, $attribute);

        $parents = explode(',', $synczing->parentgroups);
        list($ingroups, $params) = $DB->get_in_or_equal($parents);
        $params[] = $course->id;
        $select = " id $ingroups AND courseid = ?";
        $parents = $DB->get_records_select_menu('groups', $select, $params, 'name ASC', 'id, name');
        foreach($parents as $key => $name) {
            $name = format_string($name);
            $link = new moodle_url($groupurl, array('id'=>$course->id, 'group'=>$key));
            $parents[$key] = html_writer::link($link, $name, $attribute);
        }
        $line[1] = implode(', ', $parents);

        if($members = groups_get_members($synczing->targetgroup, 'u.id')) {
            $members = count($members);
        } else {
            $members = 0;
        }

        $line[2] = $members;

        $visicon = 'hide';
        $visible = 0;
        if(!$synczing->visible) {
            $visible = 1;
            $visicon = 'show';
            $strvisible = get_string('show');
        }
        $url = new moodle_url('/report/syncgroups/edit.php', array('cid' => $course->id, 'sid'=>$sid, 'action'=>$visicon, 'sesskey'=>sesskey()));
        $buttons  = html_writer::link($url, $OUTPUT->pix_icon('t/'.$visicon, $strvisible, 'core',
                array('class' => 'iconsmall')), array('title' => $stredit));
        $url = new moodle_url('/report/syncgroups/edit.php', array('cid' => $course->id, 'sid'=>$sid, 'sesskey'=>sesskey()));
        $buttons  .= html_writer::link($url, $OUTPUT->pix_icon('t/edit', $stredit, 'core',
                array('class' => 'iconsmall')), array('title' => $stredit));
        $buttons .= $OUTPUT->spacer();
        $url = new moodle_url('/report/syncgroups/edit.php', array('cid' => $course->id, 'sid'=>$sid, 'action'=>'del', 'sesskey'=>sesskey()));
        $buttons .= html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete, 'core',
                array('class' => 'iconsmall')), array('title' => $strdelete));
        $line[3] = $buttons;
        $data[] = $line;
}

$table->data  = $data;
if($data) {
    echo html_writer::table($table);
} else {
    echo $OUTPUT->heading(get_string('nothingtodisplay'));
}

echo $OUTPUT->container_start('buttons');
echo $OUTPUT->single_button(new moodle_url('edit.php', array('cid'=>$course->id)), $srtnewsynczing);
echo $OUTPUT->container_end();

//making log entry
//add_to_log($course->id, 'course', 'report edit groups', "report/syncgroups/index.php?id=$course->id", $course->id);
// Trigger a report viewed event.
$event = \report_syncgroups\event\report_viewed::create(array('context' => $context));
$event->trigger();

//display page footer
echo $OUTPUT->footer();
