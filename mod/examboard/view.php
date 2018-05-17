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
 * Prints an instance of mod_examboard.
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('locallib.php');


$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$e  = optional_param('e', 0, PARAM_INT); // ... module instance id.

$view   = optional_param('view', '', PARAM_ALPHA);  // ... viewing list, board, exam
$itemid = optional_param('item', 0, PARAM_INT);     // ... item to view

$groupid = optional_param('group', 0, PARAM_INT);
$sort = optional_param('tsort', '', PARAM_ALPHANUMEXT);
$order = optional_param('order', '', PARAM_ALPHANUMEXT);
$userorder = optional_param('uorder', 1, PARAM_INT); 

if ($id) {
    list ($course, $cm) = get_course_and_cm_from_cmid($id, 'examboard');
    $examboard = $DB->get_record('examboard', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($e) {
    $examboard = $DB->get_record('examboard', array('id' => $e), '*', MUST_EXIST);
    list ($course, $cm) = get_course_and_cm_from_instance($e, 'examboard'); 
} else {
    print_error(get_string('missingidandcmid', 'examboard'));
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

// set url params 
$urlparams = array('id' => $cm->id);
if($sort) {
    //$urlparams['tsort'] = $sort;
}
if($order) {
   // $urlparams['order'] = $order;
}
if(!$userorder) {
    $urlparams['uorder'] = $userorder;
}

/// Check to see if groups are being used in this examboard
$groupmode = groups_get_activity_groupmode($cm);
if ($groupmode) {
    $groupid = groups_get_activity_group($cm, true);
}
if($groupid) {
    $urlparams['group'] = $groupid;
}

$url = new moodle_url('/mod/examboard/view.php', $urlparams);
$editurl = new moodle_url('/mod/examboard/edit.php', $urlparams);

$PAGE->set_url($url);
$PAGE->set_title(format_string($examboard->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// add navbar
if($view) {
    $url->param('view', $view);
    $url->param('item', $itemid);
    $PAGE->navbar->add(get_string('view'.$view, 'examboard'), $url);
}

if($ulpgc = get_config('local_ulpgccore')) {
    $nameformat = '';
    if(!$userorder) {
        $nameformat = 'firstname';
    } else {
        $nameformat = 'lastname';
    }
    $SESSION->nameformat = $nameformat;
}

/// Process any submitted data, if there is any, or redirections, before headers






// Completion and trigger events.
examboard_view($examboard, $course, $cm, $context);

$canviewallgroups = has_capability('moodle/site:accessallgroups', $context);
$canmanage = has_capability('mod/examboard:manage', $context);
$cangrade = has_capability('mod/examboard:grade', $context);
$canviewall = has_capability('mod/examboard:viewall', $context);


$renderer = $PAGE->get_renderer('mod_examboard');

echo $renderer->header();

$strnorallowed = get_string('nopermissiontoviewpage', 'error');


if($view == 'board' && ($cangrade || $canmanage)) {

    $board = $DB->get_record('examboard_board', array('examboardid' => $examboard->id, 'id' => $itemid), '*', MUST_EXIST); 
    $members = examboard_get_board_members($board->id,  null,  true);
    
    $committee = new \mod_examboard\output\committee($board->id, $board->active, $members, 
                        $examboard->requireconfirm, $examboard->confirmdefault, $examboard->chair, $examboard->secretary, $examboard->vocal);
    $committee->canmanage = $canmanage;
    list($assignedexams, $otherexams) = examboard_get_board_exams($board->id, $examboard->id, $examboard->usetutors);
    $committee->assignedexams = $assignedexams;
    $committee->notifications = examboard_get_board_notifications($board->id);
    $committee->confirmations = examboard_get_board_confirmations($board->id);

    $url->remove_params('view', 'item');
    echo $renderer->view_board($board, $url, $committee, $otherexams);   

} elseif($view == 'exam' && ($cangrade || $canmanage)) {
    $examination = \mod_examboard\examination::get_from_id($itemid);

    // regular users should acces only their own exam   
    if($canviewall || $canmanage || $examination->is_grader($USER->id)) {
        $examinees_table = new \mod_examboard\output\examinees_table($url, $examination, $examboard);
        $examinees_table->editurl = $editurl;
        $examinees_table->canmanage = $canmanage;
        echo $renderer->render($examinees_table);   
    } else {
        echo $OUTPUT->heading($strnorallowed, 4, ' alert-info');
        $url->remove_params('view', 'item');
        echo $OUTPUT->continue_button($url);
        //echo $OUTPUT->notice($strnorallowed, $url, $course); 
    }

} else {
    //$examinations = examboard_get_user_exams($examboard, $currentgroup, $canviewall); 

    //$exams_list_viewer = new examboard_exams_list_viewer($url, $examboard);
    $exams_list_viewer = new \mod_examboard\output\exams_table($url, $examboard);
    $exams_list_viewer->canmanage = $canmanage;
    $exams_list_viewer->cansubmit = has_capability('mod/examboard:submit', $context);
    $exams_list_viewer->cangrade = $examboard->grade &&  $cangrade;
    $exams_list_viewer->editurl = $editurl;
    $exams_list_viewer->canviewall = $canviewall || $canmanage;
    //$exams_list_viewer->examinations = examboard_get_user_exams($examboard, $canviewall, $sort, $order, $groupid, true); 

    echo $renderer->view_exams($examboard, $cm, $exams_list_viewer);   
}

$SESSION->nameformat = ''; // ecastro ULPGC remove naming format
echo $OUTPUT->footer();
