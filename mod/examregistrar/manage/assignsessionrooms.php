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
 * Add/remove staffers from rooms
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/mod/examregistrar/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$e  = optional_param('e', 0, PARAM_INT);  // examregistrar instance ID - it should be named as the first character of the module
$examcm  = optional_param('ex', 0, PARAM_INT);  //

if($examcm) {
        $cm         = get_coursemodule_from_id('exam', $examcm, 0, false, MUST_EXIST);
        $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $defaulter  = get_config('examregistrar', 'defaultregistrar');
        $examregistrar  = $DB->get_record('examregistrar', array('id' => $defaulter->instance), '*', MUST_EXIST);
} else {
    if ($id) {
        $cm         = get_coursemodule_from_id('examregistrar', $id, 0, false, MUST_EXIST);
        $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $examregistrar  = $DB->get_record('examregistrar', array('id' => $cm->instance), '*', MUST_EXIST);
    } elseif ($e) {
        $examregistrar  = $DB->get_record('examregistrar', array('id' => $e), '*', MUST_EXIST);
        $course     = $DB->get_record('course', array('id' => $examregistrar->course), '*', MUST_EXIST);
        $cm         = get_coursemodule_from_instance('examregistrar', $examregistrar->id, $course->id, false, MUST_EXIST);
    } else {
        error('You must specify a course_module ID or an instance ID');
    }
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);


 //print_object($_GET);
 //print_object("_GET -----------------");
 //print_object($_POST);
 //print_object("_POST -----------------");

$action = required_param('action', PARAM_ALPHANUMEXT);  // complex action not managed by edit
$edit   = optional_param('edit', '', PARAM_ALPHANUMEXT);

if($edit) {
    $baseurl = new moodle_url('/mod/examregistrar/manage.php', array('id' => $cm->id, 'edit'=>$edit));
        $tab = 'manage';
} else {
    $baseurl = new moodle_url('/mod/examregistrar/view.php', array('id' => $cm->id, 'tab'=>'session'));
    $tab = 'session';
}

$examregprimaryid = examregistrar_get_primaryid($examregistrar);
$examregistrar->config = examregistrar_get_instance_configdata($examregistrar);

/// Set the page header
$PAGE->set_url($baseurl);
$PAGE->set_title(format_string($examregistrar->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_activity_record($examregistrar);

if($edit) {
    $PAGE->navbar->add(get_string($edit, 'examregistrar'), $baseurl);
} else {
    $PAGE->navbar->add(get_string($tab, 'examregistrar'), $baseurl);
}
$PAGE->navbar->add(get_string($action, 'examregistrar'), null);

$output = $PAGE->get_renderer('mod_examregistrar');

/// check permissions
$caneditelements = has_capability('mod/examregistrar:editelements',$context);
$canmanageperiods = has_capability('mod/examregistrar:manageperiods',$context);
$canmanageexams = has_capability('mod/examregistrar:manageexams',$context);
$canmanagelocations = has_capability('mod/examregistrar:managelocations',$context);
$canmanageseats = has_capability('mod/examregistrar:manageseats',$context);
$canmanage = $caneditelements || $canmanageperiods || $canmanageexams || $canmanagelocations || $canmanageseats;

require_capability('mod/examregistrar:manageseats',$context);

///////////////////////////////////////////////////////////////////////////////assign_

$session   = optional_param('session', 0, PARAM_INT);
$bookedsite   = optional_param('venue', 0, PARAM_INT);

$baseurl->params(array('session'=>$session, 'venue'=>$bookedsite));
$actionurl = new moodle_url('/mod/examregistrar/manage/assignsessionrooms.php', $baseurl->params() + array('action'=>$action, 'edit'=>$edit));


/// process form actions
if ($frm = data_submitted() and confirm_sesskey()) {

    //print_object($frm);
    //print_object("   frm    frm    frm    frm    frm    frm    frm    frm   ");

    if (isset($frm->cancel_examsessions)) {
        $url = new moodle_url('/mod/examregistrar/manage.php', array('id' => $cm->id, 'edit'=>$edit));
        redirect($url);
    } elseif (isset($frm->cancel_session)) {
        redirect($baseurl);

    } elseif (isset($frm->add) and !empty($frm->addselect)) {
        foreach ($frm->addselect as $roomid) {
            examregistrar_addupdate_sessionroom($session, $roomid, $bookedsite);
        }

    } elseif (isset($frm->remove) and !empty($frm->removeselect)) {
        foreach ($frm->removeselect as $roomid) {
            examregistrar_remove_sessionroom($session, $roomid);
        }
    }
}

$currentmembers = array();
$potentialmembers  = array();


/// TODO   TODO TODO  look for users in ALL courses that use this examregid

//if ($users = get_users_by_capability($context, 'mod/examregistrar:beroomstaff', 'u.id, u.username, u.idnumber, u.firstname, u.lastname', 'lastname ASC')) {
$sql = "SELECT l.id, l.location, el.name, el.idnumber, l.seats, l.parent
        FROM {examregistrar_locations} l
        JOIN {examregistrar_elements} el ON el.examregid = l.examregid AND el.type = 'locationitem' AND l.location = el.id
        WHERE l.examregid =:examregid AND l.seats > :seats AND l.visible = 1 ";
$sort = ' ORDER by el.name ';

if ($rooms = $DB->get_records_sql($sql.$sort, array('examregid'=>$examregprimaryid, 'seats'=>0))) {
    if ($assigned = $DB->get_records('examregistrar_session_rooms', array('examsession'=>$session, 'available'=>1))) {
        foreach ($assigned as $ass) {
            if($ass->bookedsite == $bookedsite) {
                $currentmembers[$ass->roomid] = $rooms[$ass->roomid];
            }
            unset($rooms[$ass->roomid]);
        }
    }
    $potentialmembers = $rooms;
}

$currentmembersoptions = '';
$currentmemberscount = 0;
if ($currentmembers) {
    $sortedmembers = array();
    foreach($currentmembers as $room) {
        $sortedmembers[$room->id] = format_text($room->name).'['.$room->idnumber.'] ('.$room->seats.')';
    }
    natsort($sortedmembers);
    //foreach($currentmembers as $room) {
    foreach($sortedmembers as $id=>$roomdescription) {
        $currentmembersoptions .= '<option value="'.$id.'.">'.$roomdescription.'</option>';
        $currentmemberscount ++;
    }
/*
    // Get course managers so they can be highlighted in the list
    if ($managerroles = get_config('', 'coursecontact')) {
        $coursecontactroles = explode(',', $managerroles);
        foreach ($coursecontactroles as $roleid) {
            $role = $DB->get_record('role', array('id'=>$roleid));
            $managers = get_role_users($roleid, $context, true, 'u.id', 'u.id ASC');
        }
    }
    */
} else {
    $currentmembersoptions .= '<option>&nbsp;</option>';
}

$potentialmembersoptions = '';
$potentialmemberscount = 0;
if ($potentialmembers) {
    foreach($potentialmembers as $room) {
        $potentialmembersoptions .= '<option value="'.$room->id.'.">'.format_text($room->name).'['.$room->idnumber.'] ('.$room->seats.')'.'</option>';
        $potentialmemberscount ++;
    }
} else {
    $potentialmembersoptions .= '<option>&nbsp;</option>';
}


/*
$groupingname = format_string($grouping->name);

navigation_node::override_active_url(new moodle_url('/group/index.php', array('id'=>$course->id)));
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add($strparticipants, new moodle_url('/user/index.php', array('id'=>$courseid)));
$PAGE->navbar->add($strgroups, new moodle_url('/group/index.php', array('id'=>$courseid)));
$PAGE->navbar->add($straddgroupstogroupings);

/// Print header
$PAGE->set_title("$course->shortname: $strgroups");
$PAGE->set_heading($course->fullname);
*/


echo $OUTPUT->header();

$sessionname = '';
if($session) {
    $sql = "SELECT s.id, s.examsession, es.name, es.idnumber, s.examdate, ep.name AS periodname, ep.idnumber AS periodidnumber
            FROM {examregistrar_examsessions} s
            JOIN {examregistrar_elements} es ON es.examregid = s.examregid AND es.type = 'examsessionitem' AND s.examsession = es.id
            JOIN {examregistrar_periods} p ON s.examregid = p.examregid AND s.period = p.id
            JOIN {examregistrar_elements} ep ON ep.examregid = p.examregid AND ep.type = 'perioditem' AND p.period = ep.id
            WHERE s.id = :id ";
    $examsession = $DB->get_record_sql($sql, array('id'=>$session), MUST_EXIST);
    $sessionname = '['.$output->formatted_name('', $examsession->periodidnumber).'] ';
    $sessionname .= $output->formatted_name($examsession->name, $examsession->idnumber).',  '. userdate($examsession->examdate, get_string('strftimedaydate'));
}

echo $output->heading(get_string('sessionrooms', 'examregistrar').': '.$sessionname, 3, 'main');


        $venueelement = examregistrar_get_venue_element($examregistrar);
        $venuemenu = examregistrar_get_referenced_namesmenu($examregistrar, 'locations', 'locationitem', $examregprimaryid, 'choose', '', array('locationtype'=>$venueelement));
        //natcasesort($venuemenu);
        $select = new single_select(new moodle_url($actionurl), 'venue', $venuemenu, $bookedsite);
        $select->set_label(get_string('venue', 'examregistrar'));
        $select->formid = 'venueselector'.time();
        $select->class .= ' center ';
    echo $output->render($select);

?>
<div id="addmembersform">
    <form id="assignform" method="post" action="">
    <div>
    <input type="hidden" name="sesskey" value="<?php p(sesskey()); ?>" />

    <table summary="" cellpadding="5" cellspacing="0">
    <tr>
      <td valign="top">
          <label for="removeselect"><?php print_string('existingmembers', 'group', $currentmemberscount); ?></label>
          <br />
          <select name="removeselect[]" size="20" id="removeselect" multiple="multiple"
                  onfocus="document.getElementById('assignform').add.disabled=true;
                           document.getElementById('assignform').remove.disabled=false;
                           document.getElementById('assignform').addselect.selectedIndex=-1;">
          <?php echo $currentmembersoptions ?>
          </select></td>
      <td valign="top">

        <p class="arrow_button">
            <?php if($bookedsite) { ?>
            <input name="add" id="add" type="submit" value="<?php echo '&nbsp;'.$OUTPUT->larrow().' &nbsp; &nbsp; '.get_string('add'); ?>" title="<?php print_string('add'); ?>" />
            <br />
            <br />
            <input name="remove" id="remove" type="submit" value="<?php echo '&nbsp; '.$OUTPUT->rarrow().' &nbsp; &nbsp; '.get_string('remove'); ?>" title="<?php print_string('remove'); ?>" />
            <?php } else {
                echo get_string('missingvenue', 'examregistrar');
            } ?>
        </p>
      </td>
      <td valign="top">
          <label for="addselect"><?php print_string('potentialmembers', 'group', $potentialmemberscount); ?></label>
          <br />
          <select name="addselect[]" size="20" id="addselect" multiple="multiple"
                  onfocus="document.getElementById('assignform').add.disabled=false;
                           document.getElementById('assignform').remove.disabled=true;
                           document.getElementById('assignform').removeselect.selectedIndex=-1;">
         <?php echo $potentialmembersoptions ?>
         </select>
         <br />
       </td>
    </tr>
    <tr><td></td><td>
    <?php if($edit) { ?>
        <input type="submit" name="cancel_examsessions" value="<?php print_string('backto', 'examregistrar', get_string('examsessions', 'examregistrar')); ?>" />
    <?php } else { ?>
        <input type="submit" name="cancel_session" value="<?php print_string('backto', 'examregistrar', get_string('session', 'examregistrar')); ?>" />
    <?php } ?>
    </td><td></td></tr>
    </table>
    </div>
    </form>
</div>

<?php
    echo $OUTPUT->footer();
