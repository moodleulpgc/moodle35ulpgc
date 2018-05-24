<?php

/**
 * Contains various sub-screens that a teacher can see.
 *
 * @package    mod
 * @subpackage scheduler
 * @copyright  2014 Henning Bostelmann and others (see README.txt)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function scheduler_prepare_formdata(scheduler_slot $slot) {

    $data = $slot->get_data();
    $data->exclusivityenable = ($data->exclusivity > 0);

    $data->notes = array();
    $data->notes['text'] = $slot->notes;
    $data->notes['format'] = $slot->notesformat;
    $data->slotappointmentnote = $slot->appointmentnote; //ecastro ULPGC
    
    if ($slot->emaildate < 0) {
        $data->emaildate = 0;
    }


    $i = 0;
     $data->appointmentnote = array(); // ecastro ULPGC, to be used for atudent appointmentnotes
    foreach ($slot->get_appointments() as $appointment) {
        $data->studentid[$i] = $appointment->studentid;
        $data->attended[$i] = $appointment->attended;
        $data->groupid[$i] = $appointment->groupid; // ecastro ULPGC
        $data->appointmentnote[$i]['text'] = $appointment->appointmentnote;
        $data->appointmentnote[$i]['format'] = $appointment->appointmentnoteformat;
        $data->grade[$i] = $appointment->grade;
        $i++;
    }
    return $data;
}

function scheduler_save_slotform(scheduler_instance $scheduler, $course, $slotid, $data) {
    global $DB, $output;

    if ($slotid) {
        $slot = scheduler_slot::load_by_id($slotid, $scheduler);
    } else {
        $slot = new scheduler_slot($scheduler);
    }
    
    // Set data fields from input form.
    $slot->starttime = $data->starttime;
    $slot->duration = $data->duration;
    $slot->exclusivity = $data->exclusivityenable ? $data->exclusivity : 0;
    $slot->teacherid = $data->teacherid;
    $slot->notes = $data->notes['text'];
    $slot->notesformat = $data->notes['format'];
    $slot->appointmentlocation = $data->appointmentlocation;
    $slot->appointmentnote = '';
    $slot->data->reuse = 0;
    $slot->data->etutor = 0;
    $slot->reuse = 0;
    $slot->etutor = 0;
    if(isset($data->slotappointmentnote)) {
        $slot->appointmentnote = $data->slotappointmentnote; // ecastro ULPGC
    }
    $slot->hideuntil = $data->hideuntil;
    $slot->emaildate = $data->emaildate;
    if(isset($data->shared)) { // ecastro ULPGC
        $slot->shared = $data->shared;
    }
/*
    if(isset($data->etutor)) {
        $slot->etutor = $data->etutor;
    }
*/
    $slot->timemodified = time();

    $currentapps = $slot->get_appointments();

    if(isset($data->groupbook) && $data->groupbook) { // ecastro ULPGC
        if($members = groups_get_members($data->groupbook)) {
            $i = 0;
            foreach ($members as $member) {
                $data->studentid[$i] = $member->id;
                $data->groupid[$i] = $data->groupbook;
                $i++;
            }
            $data->appointment_repeats = count($members);
        }
    }

    $processedstuds = array();
    for ($i = 0; $i < $data->appointment_repeats; $i++) {
        if ($data->studentid[$i] > 0) {
            $app = null;
            foreach ($currentapps as $currentapp) {
                if ($currentapp->studentid == $data->studentid[$i]) {
                    $app = $currentapp;
                    $processedstuds[] = $currentapp->studentid;
                }
            }
            if ($app == null) {
                $app = $slot->create_appointment();
                $app->studentid = $data->studentid[$i];
                $app->groupid = $data->groupid[$i]; // ecastro ULPGC
            }
            $app->attended = isset($data->attended[$i]);
            $app->appointmentnote = ''; // ecastro ULPGC
            $app->appointmentnoteformat = FORMAT_MOODLE;
            if(isset($data->appointmentnote[$i])) {
                $app->appointmentnote = $data->appointmentnote[$i]['text'];
                $app->appointmentnoteformat = $data->appointmentnote[$i]['format'];
            }
            
            if (isset($data->grade)) {
                $selgrade = $data->grade[$i];
                $app->grade = ($selgrade >= 0) ? $selgrade : null;
            }
        }
    }
    if($data->appointment_repeats) {
        foreach ($currentapps as $currentapp) {
            if (!in_array($currentapp->studentid, $processedstuds)) {
                $slot->remove_appointment($currentapp);
            }
        }
    }

    $slot->save();

    // slot is updated, now take care of conflicting slots
    if(isset($data->ignoreconflicts) && $data->ignoreconflicts) {
        $deleted = 0;
        if($conflicts = scheduler_get_conflicts($scheduler->id, $slot->starttime, $slot->starttime+$slot->duration, $slot->teacherid)) {
            unset($conflicts[$slotid]);
            foreach($conflicts as $recid => $record) {
                $conflictslot = scheduler_slot::load_by_id($recid, $scheduler);
                $n = $conflictslot->get_appointment_count();
                if(!$n ) {
                    $conflictslot->delete();
                    $deleted += 1;
                }
            }
        }
        if($deleted) {
            echo $output->action_message(get_string('deletedslots', 'scheduler', $deleted));
        }
    }

    return $slot;
}


function scheduler_print_schedulebox(scheduler_instance $scheduler, $studentid, $groupid = 0) {
    global $output;

    $availableslots = $scheduler->get_slots_available_to_student($studentid);

    $startdatemem = '';
    $starttimemem = '';
    $availableslotsmenu = array();
    $url = new moodle_url('/mod/scheduler/view.php',
                    array('id' => $scheduler->cmid, 'sesskey' => sesskey()));

    foreach ($availableslots as $slot) {
        $startdatecnv = $output->userdate($slot->starttime);
        $starttimecnv = $output->usertime($slot->starttime);

        $startdatestr = ($startdatemem != '' and $startdatemem == $startdatecnv) ? "-----------------" : $startdatecnv;
        $starttimestr = ($starttimemem != '' and $starttimemem == $starttimecnv) ? '' : $starttimecnv;

        $startdatemem = $startdatecnv;
        $starttimemem = $starttimecnv;
        
        $url->param('slotid', $slot->id);
        if ($groupid) {
            $url->param('what', 'schedule'); //$url->param('what', 'schedulegroup');
            $url->param('subaction', 'dochooseslot');
            $url->param('groupid', $groupid);
        } else {
            $url->param('what', 'schedule');
            $url->param('subaction', 'dochooseslot');
            $url->param('studentid', $studentid);
        }
        $availableslotsmenu[$url->out()] = "$startdatestr $starttimestr";
    }

    $chooser = new url_select($availableslotsmenu);

    if ($availableslots) {
        echo $output->box_start();
        echo $output->heading(get_string('chooseexisting', 'scheduler'), 3);
        echo $output->render($chooser);
        echo $output->box_end();
    }
}

// Load group restrictions.
$groupmode = groups_get_activity_groupmode($cm);
$currentgroup = false;
if ($groupmode) {
    $currentgroup = groups_get_activity_group($cm, true);
}

// All group arrays in the following are in the format used by groups_get_all_groups.
// The special value '' (empty string) is used to signal "all groups" (no restrictions)

// Find groups which the current teacher can see ($groupsicansee).
$userfilter = $USER->id;
$canseeall = false; // ecastro ULPGC
if (has_capability('moodle/site:accessallgroups', $context)) {
    $userfilter = 0;
    $canseeall = true;
}
$groupsicansee = '';
if ($groupmode) {
    if ($currentgroup) {
        if ($userfilter && !groups_is_member($currentgroup, $userfilter)) {
            $groupsicansee = array();
        } else {
            $cgobj = groups_get_group($currentgroup);
            $groupsicansee = array($currentgroup => $cgobj);
        }
    } else if ($userfilter) {
        $groupsicansee = groups_get_all_groups($COURSE->id, $userfilter, $cm->groupingid);
    }
}

// Find groups which the current teacher can schedule as a group ($groupsicanschedule).
$groupsicanschedule = array();
if ($scheduler->is_group_scheduling_enabled()) {
    $groupsicanschedule = groups_get_all_groups($COURSE->id, $userfilter, $cm->groupingid); // $scheduler->bookingrouping); // ecastro ULPGC, now grouping on cm
}

// Find groups which can book an appointment with the current teacher ($groupsthatcanseeme).

$groupsthatcanseeme = '';
if ($groupmode) {

    $groupsthatcanseeme = groups_get_all_groups($COURSE->id, $userfilter, $cm->groupingid);
}

if ($action != 'view') {
    require_once($CFG->dirroot.'/mod/scheduler/slotforms.php');
    include($CFG->dirroot.'/mod/scheduler/teacherview.controller.php');
}

echo $output->header();

/************************************ View : New single slot form ****************************************/
if ($action == 'addslot') {
    $actionurl = new moodle_url('/mod/scheduler/view.php', array('what' => 'addslot', 'subpage' => $subpage, 'id' => $cm->id));
    $returnurl = new moodle_url('/mod/scheduler/view.php', array('what' => 'view', 'subpage' => $subpage, 'id' => $cm->id));

    if (!scheduler_has_teachers($context)) {
        print_error('needteachers', 'scheduler', $returnurl);
    }

    $mform = new scheduler_editslot_form($actionurl, $scheduler, $cm, $groupsicansee, array('adding'=>true));

    if ($mform->is_cancelled()) {
        redirect($returnurl);
    } else if ($formdata = $mform->get_data()) {
        $slot = scheduler_save_slotform ($scheduler, $course, 0, $formdata);
        \mod_scheduler\event\slot_added::create_from_slot($slot)->trigger();
        echo $output->action_message(get_string('oneslotadded', 'scheduler'));
    } else {
        echo $output->heading(get_string('addsingleslot', 'scheduler'));
        $mform->display();
        echo $output->footer($course);
        die;
    }
}
/************************************ View : Update single slot form ****************************************/
if ($action == 'updateslot') {

    $slotid = required_param('slotid', PARAM_INT);
    $slot = $scheduler->get_slot($slotid);
    $data = scheduler_prepare_formdata($slot);

    $actionurl = new moodle_url('/mod/scheduler/view.php',
                    array('what' => 'updateslot', 'id' => $cm->id, 'slotid' => $slotid, 'subpage' => $subpage, 'offset' => $offset));
    $returnurl = new moodle_url('/mod/scheduler/view.php',
                    array('what' => 'view', 'id' => $cm->id, 'subpage' => $subpage, 'offset' => $offset));
                    
    $groupid = 0;
    if($scheduler->bookingrouping > 0) {
        $groupid = -1;
        if($groups = $slot->get_group_appointments()) {
            reset($groups);
            $groupid = key($groups);
        }
    }
    
    $mform = new scheduler_editslot_form($actionurl, $scheduler, $cm, $groupsicansee, array('slotid' => $slotid, 'update'=>true, 'groupbook'=>$groupid));
    $mform->set_data($data);

    if ($mform->is_cancelled()) {
        redirect($returnurl);
    } else if ($formdata = $mform->get_data()) {
        scheduler_save_slotform($scheduler, $course, $slotid, $formdata);
        echo $output->action_message(get_string('slotupdated', 'scheduler'));
    } else {
        echo $output->heading(get_string('updatesingleslot', 'scheduler'));
        $mform->display();
        echo $output->footer($course);
        die;
    }

}
/************************************ Add session multiple slots form ****************************************/
if ($action == 'addsession') {

    $actionurl = new moodle_url('/mod/scheduler/view.php',
                    array('what' => 'addsession', 'id' => $cm->id, 'subpage' => $subpage));
    $returnurl = new moodle_url('/mod/scheduler/view.php',
                    array('what' => 'view', 'id' => $cm->id, 'subpage' => $subpage));

    if (!scheduler_has_teachers($context)) {
        print_error('needteachers', 'scheduler', $returnurl);
    }

    $mform = new scheduler_addsession_form($actionurl, $scheduler, $cm, $groupsicansee);

    if ($mform->is_cancelled()) {
        redirect($returnurl);
    } else if ($formdata = $mform->get_data()) {
        scheduler_action_doaddsession($scheduler, $formdata);
    } else {
        echo $output->heading(get_string('addsession', 'scheduler'));
        $mform->display();
        echo $output->footer();
        die;
    }
}

/************************************ Schedule a student form ***********************************************/
if ($action == 'schedule') {
    if ($subaction == 'doschedule') {
        $slotid = optional_param('slotid', 0, PARAM_INT);
        if($slotid) {
            $slot = $scheduler->get_slot($slotid);
            $data = scheduler_prepare_formdata($slot);
            $data->slotappointmentnote = optional_param('slotappointmentnote', '', PARAM_TEXT);
        }
        $returnurl = new moodle_url('/mod/scheduler/view.php', array('what' => 'view', 'id' => $cm->id));

        $groupid = 0;
        if($groupid = optional_param('groupbook', 0, PARAM_INT)) {
        }
        
        $i = 0;
        while (isset($data->studentid[$i])) {
            $i++;
        }

        $mform = new scheduler_editslot_form($returnurl, $scheduler, $cm, $groupsicansee, array('slotid' => $slotid, 'repeats' => $i, 'adding'=>true, 'groupbook'=>$groupid));
        if($slotid) {
            $mform->set_data($data);
        }
        
        if ($mform->is_cancelled()) {
            redirect($returnurl);
        } else if ($formdata = $mform->get_data()) {
            scheduler_save_slotform($scheduler, $course, $slotid, $formdata);
        }
        redirect($returnurl);
    }
    if ($subaction == 'dochooseslot') {
        $slotid = required_param('slotid', PARAM_INT);
        $actionurl = new moodle_url('/mod/scheduler/view.php', array('what' => 'schedule', 'subaction'=>'doschedule', 'id' => $cm->id, 'slotid' => $slotid));
        $returnurl = new moodle_url('/mod/scheduler/view.php', array('what' => 'view', 'id' => $cm->id));
        
        $groupid = 0;
        if($studentid = optional_param('studentid', 0, PARAM_INT)) {
            $student = $DB->get_record('user', array('id' => $studentid), '*', MUST_EXIST);
            $name = fullname($student);
        } elseif($groupid = optional_param('groupid', 0, PARAM_INT)) {
            $group = $DB->get_record('groups', array('id' => $groupid), '*', MUST_EXIST);
            $members = groups_get_members($groupid);
            $name = $group->name;
        } else {
            print_error('nogrouporstudent', 'scheduler', $returnurl);
        }

        $data = scheduler_prepare_formdata($scheduler->get_slot($slotid));
        $i = 0;
        while (isset($data->studentid[$i])) {
            $i++;
        }
        $data->studentid[$i] = $studentid;
        $i++;

        $mform = new scheduler_editslot_form($actionurl, $scheduler, $cm, $groupsicansee, array('slotid' => $slotid, 'repeats' => $i, 'schedule'=>$studentid, 'groupbook'=>$groupid));
        $mform->set_data($data);

        echo $output->heading(get_string('scheduleappointment', 'scheduler', $name));
        $mform->display();

    } else if (empty($subaction)) {
        $actionurl = new moodle_url('/mod/scheduler/view.php', array('what' => 'schedule', 'subaction'=>'doschedule', 'id' => $cm->id));
        $returnurl = new moodle_url('/mod/scheduler/view.php', array('what' => 'view', 'id' => $cm->id));

        $groupid = 0;
        if($studentid = optional_param('studentid', 0, PARAM_INT)) {
            $student = $DB->get_record('user', array('id' => $studentid), '*', MUST_EXIST);
            $name = fullname($student);
        } elseif($groupid = optional_param('groupid', 0, PARAM_INT)) {
            $group = $DB->get_record('groups', array('id' => $groupid), '*', MUST_EXIST);
            $members = groups_get_members($groupid);
            $name = $group->name;
        } else {
            print_error('nogrouporstudent', 'scheduler', $returnurl);
        }

        $mform = new scheduler_editslot_form($actionurl, $scheduler, $cm, $groupsicansee, array('schedule'=>$studentid, 'groupbook'=>$groupid));

        $data = array();
        $data['studentid'][0] = $studentid;
        $mform->set_data($data);
        echo $output->heading(get_string('scheduleappointment', 'scheduler', $name));

        scheduler_print_schedulebox($scheduler, $studentid, $groupid);

        echo $output->box_start();
        echo $output->heading(get_string('orscheduleinnew', 'scheduler'), 3);
        $mform->display();
        echo $output->box_end();

    }

    echo $output->footer();
    die();
}
/************************************ Schedule a whole group in form ***********************************************/
/*
if ($action == 'schedulegroup') {

    $groupid = required_param('groupid', PARAM_INT);
    $group = $DB->get_record('groups', array('id' => $groupid), '*', MUST_EXIST);
    $members = groups_get_members($groupid);

    if ($subaction == 'dochooseslot') {

        $slotid = required_param('slotid', PARAM_INT);
        $groupid = required_param('groupid', PARAM_INT);

        $actionurl = new moodle_url('/mod/scheduler/view.php', array('what' => 'updateslot', 'id' => $cm->id, 'slotid' => $slotid));
        $returnurl = new moodle_url('/mod/scheduler/view.php', array('what' => 'view', 'id' => $cm->id));

        $data = scheduler_prepare_formdata($scheduler->get_slot($slotid));
        $i = 0;
        while (isset($data->studentid[$i])) {
            $i++;
        }
        foreach ($members as $member) {
            $data->studentid[$i] = $member->id;
            $data->groupid[$i] = $groupid; // ecastro ULPGC
            $i++;
        }

        $mform = new scheduler_editslot_form($actionurl, $scheduler, $cm, $groupsicansee,
                        array('slotid' => $slotid, 'repeats' => $i, 'groupbook' => $groupid )); // ecastro ULPGC
        $mform->set_data($data);

        echo $output->heading(get_string('scheduleappointment', 'scheduler', $group->name));
        $mform->display();

    } else if (empty($subaction)) {

        $actionurl = new moodle_url('/mod/scheduler/view.php', array('what' => 'addslot', 'id' => $cm->id));
        $returnurl = new moodle_url('/mod/scheduler/view.php', array('what' => 'view', 'id' => $cm->id));

        $data = array();
        $i = 0;
        foreach ($members as $member) {
            $data['studentid'][$i] = $member->id;
            $data['groupid'][$i] = $groupid; // ecastro ULPGC
            $i++;
        }
        if($scheduler->bookingrouping < 1) { // ecastro ULPGC
            $data['exclusivity'] = $i;
        } else {
            $data['exclusivity'] = 1;
        }

        $mform = new scheduler_editslot_form($actionurl, $scheduler, $cm, $groupsicansee, array('repeats' => $i, 'groupbook' => $groupid)); // ecastro ULPGC
        $mform->set_data($data);

        echo $output->heading(get_string('scheduleappointment', 'scheduler', $group->name));

        scheduler_print_schedulebox($scheduler, 0, $groupid);

        echo $output->box_start();
        echo $output->heading(get_string('orscheduleinnew', 'scheduler'), 3);
        $mform->display();
        echo $output->box_end();

    }
    echo $output->footer();
    die();
}
*/
//****************** Standard view ***********************************************//

// Clean all late slots (for everybody).
$scheduler->free_late_unused_slots();

// Trigger view event.
\mod_scheduler\event\appointment_list_viewed::create_from_scheduler($scheduler)->trigger();


// Print top tabs.
$slottype = optional_param('stype', 3, PARAM_INT); // ecastro ULPGC, to add slot type filtering to table
$taburl = new moodle_url('/mod/scheduler/view.php', array('id' => $scheduler->cmid, 'what' => 'view', 'subpage' => $subpage, 'stype'=>$slottype));
$actionurl = new moodle_url($taburl, array('offset' => $offset, 'sesskey' => sesskey()));

$inactive = array();
if ($DB->count_records('scheduler_slots', array('schedulerid' => $scheduler->id)) <=
         $DB->count_records('scheduler_slots', array('schedulerid' => $scheduler->id, 'teacherid' => $USER->id)) ) {
    // We are alone in this scheduler.
    $inactive[] = 'allappointments';
    if ($subpage == 'allappointments') {
        $subpage = 'myappointments';
    }
}

echo $output->teacherview_tabs($scheduler, $taburl, $subpage, $inactive);
if ($groupmode) { 
    groups_print_activity_menu($cm, $taburl); // ecastro ULPGC allways show menu if groupmode 
    if ($subpage == 'myappointments' && !$canseeall) {
        /*
        $a = new stdClass();
        $a->groupmode = get_string($groupmode == VISIBLEGROUPS ? 'groupsvisible' : 'groupsseparate');
        $groupnames = array();
        foreach ($groupsthatcanseeme as $id => $group) {
            $groupnames[] = $group->name;
        }
        $a->grouplist = implode(', ', $groupnames);
        */

        $messagekey = $groupsthatcanseeme ? 'groupmodeyourgroups' : 'groupmodeyourgroupsempty';
        //$message = get_string($messagekey, 'scheduler', $a);
        $message = get_string($messagekey, 'scheduler'); // ecastro ULPGC
        echo html_writer::div($message, 'groupmodeyourgroups');
    }
}

// Print intro.
echo $output->mod_intro($scheduler);


if($subpage=='myappointments' || $subpage=='allappointments') { // ecastro ULPGC separate pages
    // this is the slots list page

    if ($subpage == 'allappointments') {
        $teacherid = 0;
    } else {
        $teacherid = $USER->id;
        $subpage = 'myappointments';
    }
    $sqlcount = $scheduler->count_slots_for_teacher($teacherid, $currentgroup, false, $slottype);

    $pagesize = optional_param('perpage', 25, PARAM_INT); // ecastro ULPGC, to allow page size customization // 25; 
    if ($offset == -1) {
        if ($sqlcount > $pagesize) {
            $offsetcount = $scheduler->count_slots_for_teacher($teacherid, $currentgroup, true, $slottype);
            $offset = floor($offsetcount / $pagesize);
        } else {
            $offset = 0;
        }
    }
    // 2.9.2
    if ($offset * $pagesize >= $sqlcount && $sqlcount > 0) {
        $offset = floor(($sqlcount-1) / $pagesize);
    }

    $slots = $scheduler->get_slots_for_teacher($teacherid, $currentgroup, $offset * $pagesize, $pagesize, $slottype);

    echo $output->heading(get_string('slots', 'scheduler'));

    // Print instructions and button for creating slots.
    $key = ($slots) ? 'addslot' : 'welcomenewteacher';
    echo html_writer::div(get_string($key, 'scheduler'));


    $commandbar = new scheduler_command_bar();
    $commandbar->title = get_string('actions', 'scheduler');

    $addbuttons = array();
    $addbuttons[] = $commandbar->action_link(new moodle_url($actionurl, array('what' => 'addsession')), 'addsession', 't/add');
    $addbuttons[] = $commandbar->action_link(new moodle_url($actionurl, array('what' => 'addslot')), 'addsingleslot', 't/add');
    $commandbar->add_group(get_string('addcommands', 'scheduler'), $addbuttons);

    // If slots already exist, also show delete buttons.
    if ($slots) {
        $delbuttons = array();

        $delselectedurl = new moodle_url($actionurl, array('what' => 'deleteslots'));
        $PAGE->requires->yui_module('moodle-mod_scheduler-delselected', 'M.mod_scheduler.delselected.init',
                                    array($delselectedurl->out(false)) );
        $delselected = $commandbar->action_link($delselectedurl, 'deleteselection', 't/delete', 'confirmdelete', 'delselected');
        $delselected->formid = 'delselected';
        $delbuttons[] = $delselected;

        if (has_capability('mod/scheduler:manageallappointments', $context) && $subpage == 'allappointments') {
            $delbuttons[] = $commandbar->action_link(
                            new moodle_url($actionurl, array('what' => 'deleteall')),
                            'deleteallslots', 't/delete', 'confirmdelete');
            $delbuttons[] = $commandbar->action_link(
                            new moodle_url($actionurl, array('what' => 'deleteallunused')),
                            'deleteallunusedslots', 't/delete', 'confirmdelete');
        }
        $delbuttons[] = $commandbar->action_link(
                        new moodle_url($actionurl, array('what' => 'deleteunused')),
                        'deleteunusedslots', 't/delete', 'confirmdelete');
        $delbuttons[] = $commandbar->action_link(
                        new moodle_url($actionurl, array('what' => 'deleteonlymine')),
                        'deletemyslots', 't/delete', 'confirmdelete');

        $commandbar->add_group(get_string('deletecommands', 'scheduler'), $delbuttons);
    }

    echo $output->render($commandbar);

    // ecastro ULPGC
    $options = array('0' => get_string('appointedslots', 'scheduler'),
                        '1'=>get_string('attendedslots', 'scheduler'),
                        '2'=>get_string('notusedslots', 'scheduler'),
                        '3'=>get_string('availableslots', 'scheduler'),
                        '4'=>get_string('availableslotsall', 'scheduler'));

    $select = new single_select($actionurl, 'stype', $options, $slottype, array());
    $select->label = get_string('slottype' ,'scheduler');
    $select->formid = 'selectslottype';
    echo $OUTPUT->box($OUTPUT->render($select), 'slottypeselector');

    if(isset($actionmessage) &&  $actionmessage) {
        echo $actionmessage;
    }
    // ecastro ULPGC

    // Some slots already exist - prepare the table of slots.
    if ($slots) {

        $slotman = new scheduler_slot_manager($scheduler, $actionurl);
        $slotman->showteacher = ($subpage == 'allappointments');

        $schedulerid = $scheduler->get_id(); // ecastro ULPGC

        foreach ($slots as $slot) {
            

            $editable = (($USER->id == $slot->teacherid || has_capability('mod/scheduler:manageallappointments', $context)) &&
                           ($slot->schedulerid == $schedulerid));
            $studlist = new scheduler_student_list($slotman->scheduler);
            $studlist->expandable = false;
            $studlist->expanded = true;
            $studlist->editable = $editable;
            $studlist->linkappointment = true;
            $studlist->checkboxname = 'seen[]';
            $studlist->buttontext = get_string('saveseen', 'scheduler');
            $studlist->actionurl = new moodle_url($actionurl, array('what' => 'saveseen', 'slotid' => $slot->id));
            foreach ($slot->get_appointments() as $app) {
                $studlist->add_student($app, false, $app->is_attended());
            }

            $slotman->add_slot($slot, $studlist, $editable);
        }

    // ecastro ULPGC duplicate paging bar
        // Instruction for teacher to click Seen box after appointment.
        echo html_writer::div(get_string('markseen', 'scheduler'));
        if ($sqlcount > $pagesize) {
            echo $output->paging_bar($sqlcount, $offset, $pagesize, $actionurl, 'offset');
        }
    // ecastro ULPGC duplicate paging bar

        echo $output->render($slotman);

        if ($sqlcount > $pagesize) {
            echo $output->paging_bar($sqlcount, $offset, $pagesize, $actionurl, 'offset');
        }

        // Instruction for teacher to click Seen box after appointment.
        echo html_writer::div(get_string('markseen', 'scheduler'));

    } else {
        echo $output->notification(get_string('nothingtodisplay'));
    }
} // ecastro ULPGC end submage allappointments


if($subpage=='schedulestudents') {

    // this is the page for scheduling students
    if($scheduler->bookingrouping < 1) { // ecastro ULPGC only print single student booked if not forced only groups
        $groupfilter = ($subpage == 'myappointments') ? $groupsthatcanseeme : $groupsicansee;
        $maxlistsize = get_config('mod_scheduler', 'maxstudentlistsize');
        $students = array();
        if ($groupfilter === '') {
            $students = $scheduler->get_students_for_scheduling('', $maxlistsize);
        } else if (count($groupfilter) > 0) {
            $students = $scheduler->get_students_for_scheduling(array_keys($groupfilter), $maxlistsize);
        }

        if ($students === 0) {
            $nostudentstr = get_string('noexistingstudents', 'scheduler');
            if ($COURSE->id == SITEID) {
                $nostudentstr .= '<br/>'.get_string('howtoaddstudents', 'scheduler');
            }
            echo $output->notification($nostudentstr, 'notifyproblem');
        } else if (is_integer($students)) {
            // There are too many students who still have to make appointments, don't display a list.
            $toomanystr = get_string('missingstudentsmany', 'scheduler', $students);
            echo $output->notification($toomanystr, 'notifymessage');

        } else if (count($students) > 0) {

            $maillist = array();
            foreach ($students as $student) {
                $maillist[] = trim($student->email);
            }

            $mailto = 'mailto:'.s(implode($maillist, ',%20'));

            $subject = get_string('invitation', 'scheduler'). ': ' . $scheduler->name;
            $body = $subject."\n\n";
            $body .= get_string('invitationtext', 'scheduler');
            $body .= "\n\n{$CFG->wwwroot}/mod/scheduler/view.php?id={$cm->id}";
            $invitationurl = new moodle_url($mailto, array('subject' => $subject, 'body' => $body));

            $subject = get_string('reminder', 'scheduler'). ': ' . $scheduler->name;
            $body = $subject."\n\n";
            $body .= get_string('remindertext', 'scheduler');
            $body .= "\n\n{$CFG->wwwroot}/mod/scheduler/view.php?id={$cm->id}";
            $reminderurl = new moodle_url($mailto, array('subject' => $subject, 'body' => $body));

            $maildisplay = '';
            if (get_config('mod_scheduler', 'showemailplain')) {
                $maildisplay .= html_writer::div(implode(', ', $maillist));
            }
            $maildisplay .= get_string('composeemail', 'scheduler').' ';
            $maildisplay .= html_writer::link($invitationurl, get_string('invitation', 'scheduler'));
            $maildisplay .= ' &mdash; ';
            $maildisplay .= html_writer::link($reminderurl, get_string('reminder', 'scheduler'));

            echo $output->box_start('maildisplay');
            // Print number of students who still have to make an appointment.
            echo $output->heading(get_string('missingstudents', 'scheduler', count($students)), 3);
            // Print e-mail addresses and mailto links.
            echo $maildisplay;
            echo $output->box_end();


            $userfields = scheduler_get_user_fields(null);
            $fieldtitles = array();
            foreach ($userfields as $f) {
                $fieldtitles[] = $f->title;
            }
            $studtable = new scheduler_scheduling_list($scheduler, $fieldtitles);
            $studtable->id = 'studentstoschedule';

            foreach ($students as $student) {
                $picture = $output->user_picture($student);
                $name = $output->user_profile_link($scheduler, $student);
                $actions = array();
                $actions[] = new action_menu_link_secondary(
                                new moodle_url($actionurl, array('what' => 'schedule', 'studentid' => $student->id)),
                                new pix_icon('e/insert_date', '', 'moodle'),
                                get_string('scheduleinslot', 'scheduler') );
                $actions[] = new action_menu_link_secondary(
                                new moodle_url($actionurl, array('what' => 'markasseennow', 'studentid' => $student->id)),
                                new pix_icon('t/approve', '', 'moodle'),
                                get_string('markasseennow', 'scheduler') );

                $userfields = scheduler_get_user_fields($student);
                $fieldvals = array();
                foreach ($userfields as $f) {
                    $fieldvals[] = $f->value;
                }
                $studtable->add_line($picture, $name, $fieldvals, $actions);
            }

            $divclass = 'schedulelist '.($scheduler->is_group_scheduling_enabled() ? 'halfsize' : 'fullsize');
            echo html_writer::start_div($divclass);
            echo $output->heading(get_string('schedulestudents', 'scheduler'), 3);

            // Print table of students who still have to make appointments.
            echo $output->render($studtable);
            echo html_writer::end_div();
        } else {
            echo $output->notification(get_string('nostudents', 'scheduler'));
        }

    } //end single user tables

    if ($scheduler->is_group_scheduling_enabled()) {
        // Print list of groups that can be scheduled.

        $halfsize = ($scheduler->bookingrouping == 1 ) ? '' : 'halfsize'; // ecastro ULPGC
        echo html_writer::start_div("schedulelist $halfsize");
        echo $output->heading(get_string('schedulegroups', 'scheduler'), 3);

        if (empty($groupsicanschedule)) {
            echo $output->notification(get_string('nogroups', 'scheduler'));
        } else {
            $grouptable = new scheduler_scheduling_list($scheduler, array());
            $grouptable->id = 'groupstoschedule';

            $groupcnt = 0;
            $coursecontext = $context->get_course_context();
            $groupurl = new moodle_url('/user/index.php', array('contextid'=>$coursecontext->id));
            foreach ($groupsicanschedule as $group) {
                $members = groups_get_members($group->id, user_picture::fields('u'), 'u.lastname, u.firstname');
                if (empty($members)) {
                    continue;
                }
                // TODO refactor query
                if (!scheduler_has_slot(implode(',', array_keys($members)), $scheduler, true, $scheduler->schedulermode == 'onetime')) {
                    $picture = print_group_picture($group, $course->id, false, true, true);
                    $groupurl->param('group', $group->id);
                    $name = html_writer::link($groupurl, $groupsicanschedule[$group->id]->name); // ecastro ULPGC
                    $groupmembers = array();
                    foreach ($members as $member) {
                        $groupmembers[] = fullname($member);
                    }
                    $members = implode(', ', $groupmembers);
                    $name = print_collapsible_region($members, ' groupuserlist ', 'groupuserlist_'.$group->id , $name, '', true, true); // ecastro ULPGC
                    $actions = array();
                    $actions[] = new action_menu_link_secondary(
                                    //new moodle_url($actionurl, array('what' => 'schedulegroup', 'groupid' => $group->id)),
                                    new moodle_url($actionurl, array('what' => 'schedule', 'groupid' => $group->id)),
                                    new pix_icon('e/insert_date', '', 'moodle'),
                                    get_string('scheduleinslot', 'scheduler') );

                    $grouptable->add_line($picture, $name, array(), $actions);
                    $groupcnt++;
                }
            }
            // Print table of groups that still need to make appointments.
            if ($groupcnt > 0) {
                echo $output->render($grouptable);
            } else {
                echo $output->notification(get_string('nogroups', 'scheduler'));
            }
        }
        echo html_writer::end_div();
    }

} // ecastro ULPGC end schedulestudents

echo $output->footer();
