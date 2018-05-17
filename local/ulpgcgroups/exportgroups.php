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
 * The main group management user interface.
 *
 * @copyright 2006 The Open University, N.D.Freear AT open.ac.uk, J.White AT open.ac.uk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   core_group
 */
require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/local/ulpgccore/lib.php');
require_once($CFG->dirroot . '/local/ulpgcgroups/lib.php');
require_once($CFG->dirroot . '/local/ulpgcgroups/groups_export_form.php');

$courseid = required_param('id', PARAM_INT);

// Get the course information so we can print the header and
// check the course id is valid

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

$baseurl = new moodle_url('/local/ulpgcgroups/exportgroups.php', array('id'=>$courseid));
$returnurl = $CFG->wwwroot.'/course/view.php?id='.$courseid;

$PAGE->set_url($baseurl);

// Make sure that the user has permissions to manage groups.
require_login($course);

$context = context_course::instance($course->id);
$PAGE->set_context($context);
require_capability('moodle/course:viewparticipants', $context);
require_capability('moodle/course:managegroups', $context);
require_capability('moodle/user:viewhiddendetails', $context);
$canviewall = has_capability('moodle/site:accessallgroups', $context);

$strexportgroups = get_string('exportgroups', 'local_ulpgcgroups');
$pagetitle = "$course->shortname: $strexportgroups";
$PAGE->set_pagelayout('incourse');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);

// Setup the form.
$mform = new local_ulpgcgroups_exportgroups_form(null, array('course'=>$course));

if ($mform->is_cancelled()) {
    redirect($returnurl);

} elseif ($fromform = $mform->get_data()) {
    // Process the data and show download links.

    $params['courseid'] = $courseid;    
    
    $groupwhere = '';
    if($fromform->exportgroupid > 0) {
        // we have a group, select it
        $groupwhere = ' AND g.id = :gid ';
        $params['gid'] = $fromform->exportgroupid;
    } elseif($fromform->exportgroupid == -1) {
        // -1 means groups NOT belonging to grouping
        $groupwhere = ' AND gg.id IS NULL ';
    }
    
    $groupingwhere = '';
    $groupingjoin = '';
    if($fromform->exportgrouping > 0) {
        $groupingjoin = ' AND gg.groupingid = :groupingid ';
        $params['groupingid'] = $fromform->exportgrouping;
        $groupingwhere = ' AND gg.id IS NOT NULL ';
    } elseif($fromform->exportgrouping == -1) {
        $groupingwhere = ' AND gg.id IS NULL ';
    }
    
    $memberjoin = '';
    if(!$canviewall) {
        $memberjoin = 'JOIN {groups_members} gm ON gm.groupid = g.id AND gm.userid = :userid '; 
        $params['userid'] = $USER->id;
    }
    
    $sql = "SELECT DISTINCT(g.id), g.name, g.idnumber 
            FROM {groups} g
            $memberjoin
            LEFT JOIN {groupings_groups} gg ON gg.groupid = g.id $groupingjoin
            WHERE g.courseid = :courseid $groupwhere $groupingwhere
            ORDER BY g.name ASC ";
            
    $rs_groups = $DB->get_recordset_sql($sql, $params);
    
    
    $columns = array();
    $userdetails = local_ulpgccore_get_userfields();
    foreach($userdetails as $field => $name) {
        if(isset($fromform->{$field}) && $fromform->{$field}) {
            $columns[$field] = $name;
        }
    }
    
    if($rs_groups->valid()) {
        $fromform->ctxid = $context->id;
        local_ulpgcgroups_do_exportgroups($rs_groups, $fromform, $columns); 
    }
    $rs_groups->close();
    die;
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strexportgroups, 3);

$mform->display();

echo $OUTPUT->footer();
