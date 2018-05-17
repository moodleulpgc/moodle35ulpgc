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
require_once('../config.php');
require_once('lib.php');

$courseid = required_param('id', PARAM_INT);
$groupid  = optional_param('group', false, PARAM_INT);
$userid   = optional_param('user', false, PARAM_INT);

$action   = groups_param_action();
// Support either single group= parameter, or array groups[]
if ($groupid) {
    $groupids = array($groupid);
} else {
    $groupids = optional_param_array('groups', array(), PARAM_INT);
}
$singlegroup = (count($groupids) == 1);

$returnurl = $CFG->wwwroot.'/group/index.php?id='.$courseid;

// Get the course information so we can print the header and
// check the course id is valid

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

$url = new moodle_url('/group/index.php', array('id'=>$courseid));
navigation_node::override_active_url($url);
if ($userid) {
    $url->param('user', $userid);
}
if ($groupid) {
    $url->param('group', $groupid);
}


$controlledgroups = get_config('local_ulpgcgroups', 'forcerestrictedgroups');
if($ulpgcgroups = get_config('local_ulpgcgroups', 'enabledadvancedgroups')) {  // ecastro ULPGC customize groups page
    $groupingid  = optional_param('grouping', 0, PARAM_INT); 
    $userorder = optional_param('order', 1, PARAM_INT); 

    if ($groupingid) {
        $url->param('grouping', $groupingid);
    }
    if ($userorder) {
        $url->param('order', $userorder);
    }
    
    $ulpgcparams = array('grouping'=>$groupingid, 'order'=>$userorder);
    
    $returnurl = $url;
    
    $nameformat = '';
    if(!$userorder) {
        $nameformat = 'firstname';
    } else {
        $nameformat = 'lastname';
    }
    $SESSION->nameformat = $nameformat;
    
    
    $exclusive = 0;
    $conflictgroups = array();
    if($grouping = $DB->get_record('groupings', array('id' => $groupingid))) {
        $configdata = json_decode($grouping->configdata);
        if (isset($configdata->exclusive) && $configdata->exclusive){
            $grouping->exclusive = 1;
            $exclusive = 1;
            $conflictgroups = local_ulpgcgroups_multiplegroups_grouping($courseid, $groupingid);
        }
    }
    $colorrestricted = get_config('local_ulpgcgroups', 'colorrestricted'); 
} else {
    $ulpgcparams = array();
}

$PAGE->set_url($url);

// Make sure that the user has permissions to manage groups.
require_login($course);

$context = context_course::instance($course->id);
require_capability('moodle/course:managegroups', $context);

$PAGE->requires->js('/group/clientlib.js', true);
$PAGE->requires->js('/group/module.js', true);

// Check for multiple/no group errors
if (!$singlegroup) {
    switch($action) {
        case 'ajax_getmembersingroup':
        case 'showgroupsettingsform':
        case 'showaddmembersform':
        case 'updatemembers':
        case 'emptygroup': // ecastro ULPGC
        case 'removeuser': // ecastro ULPGC
            print_error('errorselectone', 'group', $returnurl);
    }
}

switch ($action) {
    case false: //OK, display form.
        break;

    case 'ajax_getmembersingroup':
        $roles = array();
        $restrictionfields = ($controlledgroups) ? 'gm.component, gm.itemid, ' : ''; // ecastro ULPGC
        if ($groupmemberroles = groups_get_members_by_role($groupids[0], $courseid, "u.id, $restrictionfields " . get_all_user_name_fields(true, 'u'))) { // ecastro ULPGC enforce use of user memebership component
            foreach($groupmemberroles as $roleid=>$roledata) {
                $shortroledata = new stdClass();
                $shortroledata->name = $roledata->name;
                $shortroledata->users = array();
                foreach($roledata->users as $member) {
                    $shortmember = new stdClass();
                    $shortmember->id = $member->id;
                    // ecastro ULPGC this change requires modification in clientlib.js to use style value, otherwise erro and not showing users dinamically
                    $shortmember->style = groups_index_member_style($member, $colorrestricted); // ecastro ULPGC decorate user member name by component
                    $shortmember->name = fullname($member, true);
                    $shortroledata->users[] = $shortmember;
                }
                $roles[] = $shortroledata;
            }
        }

        echo json_encode($roles);
        die;  // Client side JavaScript takes it from here.

    case 'deletegroup':
        if (count($groupids) == 0) {
            print_error('errorselectsome','group',$returnurl);
        }
        $groupidlist = implode(',', $groupids);
        redirect(new moodle_url('/group/delete.php', array('courseid'=>$courseid, 'groups'=>$groupidlist) + $ulpgcparams));
        break;

    case 'showcreateorphangroupform':
        redirect(new moodle_url('/group/group.php', array('courseid'=>$courseid) + $ulpgcparams));
        break;

    case 'showautocreategroupsform':
        redirect(new moodle_url('/group/autogroup.php', array('courseid'=>$courseid) + $ulpgcparams));
        break;

    case 'showimportgroups':
        redirect(new moodle_url('/group/import.php', array('id'=>$courseid) + $ulpgcparams));
        break;

    case 'showgroupsettingsform':
        redirect(new moodle_url('/group/group.php', array('courseid'=>$courseid, 'id'=>$groupids[0]) + $ulpgcparams));
        break;

    case 'updategroups': //Currently reloading.
        break;

    case 'removemembers':
        break;

    case 'showaddmembersform':
        redirect(new moodle_url('/group/members.php', array('group'=>$groupids[0]) + $ulpgcparams));
        break;

    case 'updatemembers': //Currently reloading.
        break;

    case 'emptygroup': // ecastro ULPGC action emptygroup
        redirect(new moodle_url('/local/ulpgcgroups/empty.php', array('courseid'=>$courseid, 'group'=>$groupids[0], 'grouping'=>$groupingid, 'order'=>$userorder)));
        break;

    case 'removeuser': // ecastro ULPGC action remove group user
        redirect(new moodle_url('/local/ulpgcgroups/member.php', array('courseid'=>$courseid, 'id'=>$groupids[0], 'user'=>$userid, 'grouping'=>$groupingid, 'order'=>$userorder)));
        break;

    default: //ERROR.
        print_error('unknowaction', '', $returnurl);
        break;
}

// Print the page and form
$strgroups = get_string('groups');
$strparticipants = get_string('participants');

/// Print header
$PAGE->set_title($strgroups);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('standard');
navigation_node::override_active_url(new moodle_url('/group/index.php', array('id'=>$courseid))); // ecastro ULPGC better navigation
echo $OUTPUT->header();

// Add tabs
$currenttab = 'groups';
require('tabs.php');

echo $OUTPUT->heading(format_string($course->shortname, true, array('context' => $context)) .' '.$strgroups, 3);

if($ulpgcgroups) {
    echo local_ulpgcgroups_grouping_menu($returnurl, true, $conflictgroups);
    echo local_ulpgcgroups_nameformat_menu($returnurl);
    $groups = groups_get_all_groups($courseid, 0, $groupingid, 'g.id, g.*');
} else {
    $groups = groups_get_all_groups($courseid);
}
$selectedname = null;
$preventgroupremoval = array();

// Get list of groups to render.
$groupoptions = array();
if ($groups) {
    foreach ($groups as $group) {
        $selected = false;
        $usercount = $DB->count_records('groups_members', array('groupid' => $group->id));
        $groupname = format_string($group->name) . ' (' . $usercount . ')';
        $style = !$ulpgcgroups ? '': local_ulpgcgroups_group_style($group, $groupingid, $exclusive, $conflictgroups, $colorrestricted, $controlledgroups);
        if (in_array($group->id, $groupids)) {
            $selected = true;
            if ($singlegroup) {
                // Only keep selected name if there is one group selected.
                $selectedname = $groupname;
            }
        }
        if (!empty($group->idnumber) && !has_capability('moodle/course:changeidnumber', $context) || $style) { // ecastro prevent deleting componenet group
            $preventgroupremoval[$group->id] = true;
        }

        $groupoptions[] = (object) [
            'style' => $style, // ecastro ULPGC use style
            'value' => $group->id,
            'selected' => $selected,
            'text' => $groupname
        ];
    }
}

// Get list of group members to render if there is a single selected group.
$members = array();
if ($singlegroup) {
    $usernamefields = get_all_user_name_fields(true, 'u');
    if($controlledgroups) { //ecastro ULPGC to enforce use of membership component
        $usernamefields .= ', gm.component, gm.itemid ';
    }
    if ($groupmemberroles = groups_get_members_by_role(reset($groupids), $courseid, 'u.id, ' . $usernamefields)) {
        foreach ($groupmemberroles as $roleid => $roledata) {
            $users = array();
            foreach ($roledata->users as $member) {
                $style = groups_index_member_style($member, $colorrestricted); //ecastro ULPGC to decorate usr name onn of membership component
                $users[] = (object)[
                    'style' => $style, // ecastro ULPGC use style
                    'value' => $member->id,
                    'text' => fullname($member, true)
                ];
            }
            $members[] = (object)[
                'role' => s($roledata->name),
                'rolemembers' => $users
            ];
        }
    }
}

$disableaddedit = !$singlegroup;
$disabledelete = !empty($groupids);
$renderable = new \core_group\output\index_page($courseid, $groupoptions, $selectedname, $members, $disableaddedit, $disabledelete,
        $preventgroupremoval, $ulpgcgroups, $groupingid, $userorder);
$output = $PAGE->get_renderer('core_group');
echo $output->render($renderable);

$SESSION->nameformat = ''; // ecastro ULPGC remove naming format
echo $OUTPUT->footer();

/**
 * Returns the first button action with the given prefix, taken from
 * POST or GET, otherwise returns false.
 * @see /lib/moodlelib.php function optional_param().
 * @param string $prefix 'act_' as in 'action'.
 * @return string The action without the prefix, or false if no action found.
 */
function groups_param_action($prefix = 'act_') {
    $action = false;
//($_SERVER['QUERY_STRING'] && preg_match("/$prefix(.+?)=(.+)/", $_SERVER['QUERY_STRING'], $matches)) { //b_(.*?)[&;]{0,1}/

    if ($_POST) {
        $form_vars = $_POST;
    }
    elseif ($_GET) {
        $form_vars = $_GET;
    }
    if ($form_vars) {
        foreach ($form_vars as $key => $value) {
            if (preg_match("/$prefix(.+)/", $key, $matches)) {
                $action = $matches[1];
                break;
            }
        }
    }
    if ($action && !preg_match('/^\w+$/', $action)) {
        $action = false;
        print_error('unknowaction');
    }
    ///if (debugging()) echo 'Debug: '.$action;
    return $action;
}

/**
 * Returns name & style for user member considering memebership component
 * This function MUST live in this script, cannot be moved to other files (e.g. a library)
 */
function groups_index_member_style($member, $color = '', $recover = false) { //ecastro ULPGC to decorate user name on membership component
    global $DB;

    if($recover && !isset($member->component)) {
        $member->component = $DB->get_field('groups_members', 'component', array('groupid'=>$member->groupid));
    }
    
    $style = '';
    if($member->component && $color) {
        $style = 'color:'.$color;
    }
   
    return $style; 
}
