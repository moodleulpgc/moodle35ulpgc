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

require_capability('mod/examregistrar:manageseats', $context);

$config = examregistrar_get_instance_configdata($examregistrar);

///////////////////////////////////////////////////////////////////////////////assign_
$defaultrole = $DB->get_field('examregistrar_elements', 'id', array('examregid'=>$examregprimaryid, 'type'=>'roleitem', 'idnumber'=>$config->defaultrole)));

$session   = optional_param('session', 0, PARAM_INT);
$roomid   = optional_param('room', 0, PARAM_INT);
$role   = optional_param('role', $defaultrole, PARAM_INT);

$baseurl->params(array('session'=>$session, 'room'=>$roomid, 'role'=>$role));
$actionurl = new moodle_url('/mod/examregistrar/manage/assignroomstaffers.php', $baseurl->params() + array('action'=>$action, 'edit'=>$edit));

/// process form actions
if ($frm = data_submitted() and confirm_sesskey()) {

    //print_object($frm);
    //print_object("   frm    frm    frm    frm    frm    frm    frm    frm   ");

    if (isset($frm->cancel)) {
        redirect($baseurl);
    } else if (isset($frm->add) and !empty($frm->addselect)) {
        if(!$role) {
            $role = examregistrar_get_default_role($examregistrar);
        }
        if($role) {
            foreach ($frm->addselect as $userid) {
                examregistrar_addupdate_roomstaffer($session, $roomid, $userid, $role, '', 1);
            }
        }

    } else if (isset($frm->remove) and !empty($frm->removeselect)) {
        foreach ($frm->removeselect as $userid) {
            examregistrar_remove_roomstaffers($session, $roomid, $userid, $role);
        }
    }
}

$currentmembers = array();
$potentialmembers  = array();


/// TODO   TODO TODO  look for users in ALL courses that use this examregid

$fields = 'u.id, '.get_all_user_name_fields(true, 'u');
$users = get_users_by_capability($context, 'mod/examregistrar:beroomstaff', $fields, 'lastname ASC');
if($categories = explode(',', $config->staffcats)) {
    $ulpgc = get_config('local_ulpgccore', 'version');
    foreach($categories as $category) {
        $joinulpgc = ($ulpgc) ? " LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid " : '';
        $sql = "SELECT c.id, c.fullname, c.shortname, c.idnumber
                FROM {course} c 
                $joinulpgc
                WHERE c.category = :category AND c.visible = 1 ";
        if($ulpgc & $config->excludecourses) {
            $sql .= ' AND uc.credits > 0 ';
        }
                
        if($courses = $DB->get_records_sql($sql, array('category'=>$category))) {
            foreach($courses as $course) {
                $coursecontext = context_course::instance($course->id);
                $courseusers = get_users_by_capability($coursecontext, 'mod/examregistrar:beroomstaff', $fields, 'lastname ASC');
                $users =  $users + $courseusers;
            }
        }
    }
}

if ($users) {
    if ($assigned = $DB->get_records('examregistrar_staffers', array('examsession'=>$session, 'locationid'=>$roomid, 'visible'=>1))) {
        foreach ($assigned as $ass) {
            if(isset($users[$ass->userid])) {
                $user = clone $users[$ass->userid];
                $user->role = $DB->get_field('examregistrar_elements', 'idnumber', array('id'=>$ass->role));
                $user->roleid = $ass->role;
                $currentmembers[$ass->id] = $user; //$users[$ass->userid];
            }
        }
    }
    //print_object($currentmembers);
    foreach($currentmembers as $id => $user) {
                if(!$role || ($role && $user->roleid == $role)) {
                    unset($users[$user->id]);
                }
    }
    $potentialmembers = $users;
}

//print_object($currentmembers);

$currentmembersoptions = '';
$currentmemberscount = 0;
$collator = new \Collator('root');
if ($currentmembers) {
    $sortedmembers = array();
    $roles = array();
    foreach($currentmembers as $user) {
        if(!isset($sortedmembers[$user->id])) {
            $sortedmembers[$user->id] = fullname($user);
            $roles[$user->id] = array($user->role);
        } else {
            $roles[$user->id][] = $user->role;
        }
    }
    $collator->asort($sortedmembers);
    //print_object($roles);
    foreach($sortedmembers as $id=>$name) {
        $userroles = $roles[$id];
        $collator->asort($userroles);
        $rolestr = implode(',', $userroles);
        $sortedmembers[$id] = $name.' ['.$rolestr.']';
    }

    //foreach($currentmembers as $user) {
    foreach($sortedmembers as $id => $username) {
        //$currentmembersoptions .= '<option value="'.$user->id.'.">'.fullname($user).' ('.$user->role.')'.'</option>';
        $currentmembersoptions .= '<option value="'.$id.'.">'.$username.'</option>';
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

$potentialmemberscount = 0;
$collator = new \Collator('root');
if ($potentialmembers) {
    $potentialmembersoptions = array();
    foreach($potentialmembers as $user) {
        $potentialmembersoptions[$user->id] = fullname($user, false, 'lastname firstname').'</option>';
    }
    $collator->asort($potentialmembersoptions);
    foreach($potentialmembersoptions as $key => $name) {
      $potentialmembersoptions[$key] = '<option value="'.$key.'">'.$name;
    }
    $potentialmemberscount = count($potentialmembersoptions);
    $potentialmembersoptions = implode("\n", $potentialmembersoptions);
} else {
    $potentialmembersoptions = '<option>&nbsp;</option>';
}

echo $output->header();

$roomname = '';
if($roomid) {
    $sql = "SELECT l.id, l.location, el.idnumber, el.name
            FROM {examregistrar_locations} l
            JOIN {examregistrar_elements} el ON el.examregid = l.examregid AND el.type = 'locationitem' AND l.location = el.id
            WHERE l.id = :id ";
    $room = $DB->get_record_sql($sql, array('id'=>$roomid), MUST_EXIST);
    $roomname = format_string($room->name);
}




if($session) {
    $sql = "SELECT s.id, s.examsession, es.name, es.idnumber, s.examdate, ep.name AS periodname, ep.idnumber AS periodidnumber
            FROM {examregistrar_examsessions} s
            JOIN {examregistrar_elements} es ON es.examregid = s.examregid AND es.type = 'examsessionitem' AND s.examsession = es.id
            JOIN {examregistrar_periods} p ON s.examregid = p.examregid AND s.period = p.id
            JOIN {examregistrar_elements} ep ON ep.examregid = p.examregid AND ep.type = 'perioditem' AND p.period = ep.id
            WHERE s.id = :id ";
    $examsession = $DB->get_record_sql($sql, array('id'=>$session), MUST_EXIST);
    $sessionname = '['.$examsession->periodidnumber.'] '.$examsession->idnumber;
} else {
    $sessionname = get_string('allsessions', 'examregistrar');
}


echo $output->heading(get_string('roomstaffers', 'examregistrar').': '.$roomname, 3, 'main');
echo $output->heading(get_string('examsessionitem', 'examregistrar').': '.$sessionname, 4, 'main');

        $rolemenu = examregistrar_elements_getvaluesmenu($examregistrar, 'roleitem', $examregprimaryid);
        $select = new single_select(new moodle_url($actionurl), 'role', $rolemenu, $role);
        $select->set_label(get_string('roleitem', 'examregistrar'));
        $select->formid = 'roleselector'.time();
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
            <?php if($role) { ?>
            <input name="add" id="add" type="submit" value="<?php echo '&nbsp;'.$OUTPUT->larrow().' &nbsp; &nbsp; '.get_string('add'); ?>" title="<?php print_string('add'); ?>" />
            <br />
            <br />
            <input name="remove" id="remove" type="submit" value="<?php echo '&nbsp; '.$OUTPUT->rarrow().' &nbsp; &nbsp; '.get_string('remove'); ?>" title="<?php print_string('remove'); ?>" />
            <?php } else {
                echo get_string('missingrole', 'examregistrar');
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
            <input type="submit" name="cancel" value="<?php print_string('backto', 'examregistrar', get_string($edit, 'examregistrar')); ?>" />
        <?php } else { ?>
        <input type="submit" name="cancel" value="<?php print_string('backto', 'examregistrar', get_string('session', 'examregistrar')); ?>" />
    <?php } ?>
    </td><td></td></tr>
    </table>
    </div>
    </form>
</div>

<?php
    echo $OUTPUT->footer();
