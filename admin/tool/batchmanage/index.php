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
 * batchmanageging managejob management.
 *
 * @package    tool_batchmanage
 * @copyright  2016 Enriue Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/admin/tool/batchmanage/managejobplugin.php');

$job = required_param('job', PARAM_PLUGIN);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);

$returnurl = new moodle_url('/admin/tool/batchmanage/index.php', array('job'=>$job));

admin_externalpage_setup('managejob_'.$job);

require_login();
require_capability('tool/batchmanage:apply', context_system::instance());

$PAGE->set_url($returnurl, array('job'=>$job, 'action'=>$action));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
// Print the header.

$managejob = batchmanage_managejob_plugin::create($job);

//print_object($_POST);

// redirect must be before header call 
$nextstep = '';
$formdata = '';
if($action && confirm_sesskey()) {
    $managejob->process_formsdata();
    $mform = $managejob->get_display_form($action);
    if($mform->is_cancelled()) {
        redirect($returnurl); 
    }
    $formdata = $mform->get_data();
    //print_object($formdata);
    $nextstep = $managejob->process_action($action, $formdata);
}    

//print_object("nextstep = $nextstep");

/// Output starts here
echo $OUTPUT->header();
echo $OUTPUT->heading_with_help(get_string('apply'.$job, 'managejob_'.$job), 'apply'.$job, 'managejob_'.$job);

// we execute after heading to allow printing line by line, not all in a block
if($nextstep == 'execute') {
    $nextstep = $managejob->execute();
}

// adter execution, check if done or other cases
if($nextstep == 'done') {
    if(isset($formdata->scheduledtask) && $formdata->scheduledtask > 0) {
        echo get_string('scheduledat', 'tool_batchmanage', userdate($formdata->scheduledtask));
    }
    echo $OUTPUT->continue_button($returnurl);
} else {
    $mform = $managejob->get_display_form($nextstep);
    $mform->display();
}

echo $OUTPUT->footer();
