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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Funciones necesarias para la gestión avanzada de grupos 
 *
 * @package local_ulpgcgroups
 * @copyright  2016 Enrique Castro @ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

function local_ulpgcgroups_extend_settings_navigation(settings_navigation $nav, context $context) {
    global $CFG, $PAGE;

    $course = $PAGE->course;
    // modifications in course pages

    if (($course->groupmode || !$course->groupmodeforce) && has_capability('moodle/course:managegroups', $context)) {
        if($coursenode =  $nav->find('courseadmin', navigation_node::TYPE_COURSE)) {
            if ($settingsnode = $nav->find('groups', navigation_node::TYPE_SETTING)) {
                $url = new moodle_url('/group/index.php', array('id'=>$course->id));
                $groupsnode = $coursenode->create(get_string('managegroups', 'local_ulpgcgroups'), $url, navigation_node::TYPE_CONTAINER, null, 'groupscontainer');
                $coursenode->add_node($groupsnode, 'users');
                $settingsnode->remove();
                $groupsnode->add_node($settingsnode);

                $url = new moodle_url('/group/groupings.php', array('id'=>$course->id));
                $groupsnode->add(get_string('groupings', 'core_group'), $url, navigation_node::TYPE_SETTING, null, 'groupings', new pix_icon('i/withsubcat', ''));
                
                $url = new moodle_url('/group/overview.php', array('id'=>$course->id));
                $groupsnode->add(get_string('overview', 'core_group'), $url, navigation_node::TYPE_SETTING, null, 'groupsoverview', new pix_icon('i/report', ''));
                
                $url = new moodle_url('/local/ulpgcgroups/exportgroups.php', array('id'=>$course->id));
                $groupsnode->add(get_string('exportgroups', 'local_ulpgcgroups'), $url, navigation_node::TYPE_SETTING, null, 'exportgroups', new pix_icon('i/export', ''));
                $url = new moodle_url('/group/import.php', array('id'=>$course->id));
                $groupsnode->add(get_string('importgroups', 'core_group'), $url, navigation_node::TYPE_SETTING, null, 'importgroups', new pix_icon('i/import', ''));
                
                ///TODO find and add  ulpgc report groups
                $settings = array('report_autogroups', 'report_syncgroups');
                foreach($settings as $setting) {
                    if ($settingsnode = $nav->find($setting, navigation_node::TYPE_SETTING)) {
                        $settingsnode->remove();
                        $groupsnode->add_node($settingsnode);                   
                    }
                }
            }
        }
    }

}

/**
 * Returns the user name & applicable decoration if membership depends on component
 *
 * @param $member user object with groups_members fields
 * @param $color string name of color format of user name display
 * @param $recover bool indicate if membership component should be retrived fron DB
 * @return array name, inline style
 */
function local_ulpgcgroups_member_style($member, $color = '', $recover = false) {
    global $DB;

    if($recover && !isset($member->component)) {
        $member->component = $DB->get_field('groups_members', 'component', array('groupid'=>$member->groupid));
    }
    
    $style = '';
    if($member->component && $color) {
        $style = 'style="color:'.$color.';"';
    }
   
    return $style; 
}

/**
 * Returns the group name & applicable decoration if group depends on component or set exclusive grouping membership
 *
 * @param $member user object with groups_members fields
 * @param $groupingid int the grouping id for single group membersip  restrictions
 * @param $exclusive int indicating exclusive, single group membersip in grouping, or not
 * @param $conflictgroups array of group ids for groups of this user in this grouping
 * @param $recover bool indicate if group creator component should be retrived fron DB
 * @return string HTML snippet with group name in styled span tag
 */
function local_ulpgcgroups_group_style($group, $groupingid = 0, $exclusive = 0, $conflictgroups = array(), $color = '', $recover = false) {
    global $DB;

    $prepend = '';
    if($exclusive && $groupingid && array_key_exists($group->id, $conflictgroups)){
        $prepend = '* ';
    }
   
    if($recover && !isset($group->component)) {
        $group->component = $DB->get_field('local_ulpgcgroups', 'component', array('groupid'=>$group->id));
    }

    $style = '';
    if($group->component && $color) {
        $style = 'style="color:'.$color.';"';
    }
   
    return $style; 
}

/**
 * Returns the a menu of allowed groupings in the course
 *
 * @param $returnurl moodle_url with fully qualified parameters for group/index.php forms
 * @param $hascontrolled bool indicates if a warning message should be printed or not
 * @param $conflictgroups array of group ids for groups of this user in this grouping
 * @return string HTML select form 
 */
function local_ulpgcgroups_grouping_menu($returnurl, $hascontrolled = false, $conflictgroups = array()) {
    global $OUTPUT;
    
    $output = '';
    $returnurl = clone $returnurl;
    $params = $returnurl->params();
    $courseid = $returnurl->get_param('id');
    $groupingid = $returnurl->get_param('grouping');
    $returnurl->remove_params('grouping'); 
    
    $options = array();
    $options[0] = get_string('anygrouping', 'local_ulpgcgroups');
    if ($groupings = groups_get_all_groupings($courseid)) {
        foreach ($groupings as $grouping) {
            $options[$grouping->id] = format_string($grouping->name);
        }
    }
    
    if($hascontrolled) {
        $output .= html_writer::div(get_string('controlledgroups', 'local_ulpgcgroups'), 'groupingmenu controlledmsg');
    }
    $output .= html_writer::start_div('groupingmenu');
        $select = new single_select(new moodle_url($returnurl), 'grouping', $options, $groupingid, array());
        $select->label = get_string('groupingmenu', 'local_ulpgcgroups');
        $select->formid = 'ulpgcgroups_groupingmenu';
        $select->set_help_icon('groupingmenu', 'local_ulpgcgroups');
        $output .= $OUTPUT->render($select); 
        //echo $OUTPUT->render($select);
        
        if($conflictgroups) {
            $list = array();
            foreach($conflictgroups as $g) {
                $list[] = $g->name;
            }
            $output .= html_writer::tag('p', '&nbsp;&nbsp;'.get_string('exclusivegroupingconflict', 'local_ulpgcgroups').
                                            '&nbsp '.implode(', ', $list).
                                            '&nbsp '.$OUTPUT->help_icon('exclusivegroupingconflict', 'local_ulpgcgroups'));
        }
    $output .= html_writer::end_div();
    $output .= html_writer::div(' ', 'clearfix');
    
    return $output;
}

/**
 * Returns a menu for specifying user name formatting
 *
 * @param $returnurl moodle_url with fully qualified parameters for group/index.php forms
 * @return string HTML select form 
 */
function local_ulpgcgroups_nameformat_menu($returnurl) {
    global $OUTPUT;
    $output = '';
    
    $returnurl = clone $returnurl;
    $userorder = $returnurl->get_param('order');
    $returnurl->remove_params('order');
    $output .= html_writer::start_div('namingmenu');
        $options = array(get_string('firstname'), get_string('lastname'));
        $select = new single_select(new moodle_url($returnurl), 'order', $options, $userorder, array());
        $select->label = get_string('userorder', 'local_ulpgcgroups');
        $select->formid = 'selectorder-24';
        $select->setid = 'selectorder-order';
        $select->set_help_icon('userorder', 'local_ulpgcgroups');
        $output .= $OUTPUT->render($select);
    $output .= html_writer::end_div();
    $output .= html_writer::div(' ', 'clearfix');
    
    return $output;
}


/**
 * Returns a menu for specifying source group for user
 *
 * @param $returnurl moodle_url with fully qualified parameters for group/index.php forms
 * @return string HTML select form 
 */
function local_ulpgcgroups_sourcegroup_menu($returnurl) {
    global $OUTPUT;
    $output = '';
    
    $courseid = $returnurl->get_param('id');
    $sourcegroup = $returnurl->get_param('source');
    $userorder = $returnurl->get_param('order');
    $returnurl = clone $returnurl;
    $returnurl->remove_params('source');

        /// Prepare the source group form
    $options = array();
    $options[0] = get_string('all');
    if ($groups = groups_get_all_groups($courseid)) {
        foreach ($groups as $g) {
            $options[$g->id] = format_string($g->name);
        }
    }
    
    $output .= html_writer::start_div('sourcemenu');
        $select = new single_select(new moodle_url($returnurl), 'source', $options, $sourcegroup, array());
        $select->label = get_string('sourcegroup', 'local_ulpgcgroups');
        $select->formid = 'selectsourcegroup';
        $select->set_help_icon('sourcegroup', 'local_ulpgcgroups');
        $output .= $OUTPUT->render($select);
    $output .= html_writer::end_div();
    $output .= html_writer::div(' ', 'clearfix');
    
    return $output;
}


/**
 * Returns a menu for enforcing exclusive single group
 *
 * @param $returnurl moodle_url with fully qualified parameters for group/index.php forms
 * @return string HTML select form 
 */
function local_ulpgcgroups_exclusive_menu($returnurl) {
    global $OUTPUT;
    $output = '';
    
    $exclusive = $returnurl->get_param('exclusive');
    $url = clone $returnurl;
    $url->remove_params('exclusive');
    
    $output .= html_writer::start_div('sourcemenu');
        $options = array (0=>get_string('no'), 1=>get_string('yes'));
        $select = new single_select($url, 'exclusive', $options, $exclusive, array());
        $select->label = get_string('forceexclusive', 'local_ulpgcgroups');
        $select->formid = 'selectexclusive';

        $output .= $OUTPUT->render($select);
    
    $output .= html_writer::end_div();
    $output .= html_writer::div(' ', 'clearfix');
    
    return $output;
}

/**
 * Returns a menu for enforcing exclusive grouping
 *
 * @param $returnurl moodle_url with fully qualified parameters for group/index.php forms
 * @return string HTML select form 
 */
function local_ulpgcgroups_exclusivegrouping_menu($returnurl) {
    global $DB, $OUTPUT;
    $output = '';
    
    $courseid = $returnurl->get_param('id');
    $groupid = $returnurl->get_param('group');
    $sourcegroup = $returnurl->get_param('source');
    
    $exclusive = $returnurl->get_param('exclusive');
    $returnurl = clone $returnurl;
    $returnurl->remove_params('exclusive');

    // ULPGC ecastro get grouping & exclusive mode
    $groupgroupings = array('0' => get_string('none'));
    $sql = "SELECT g.*
                        FROM {groupings} g
                        LEFT JOIN  {groupings_groups} gg ON g.id = gg.groupingid
                        WHERE gg.groupid = ? ORDER BY g.name ASC";
    if($groupings = $DB->get_records_sql($sql, array($groupid))) {
        foreach($groupings as $key =>$grouping){
            $groupgroupings[$key] = format_string($grouping->name);
        }
    }
    
    if (count($groupgroupings) > 1){
        $output .= html_writer::start_div('exclusivegroupingmenu');
            $select = new single_select(new moodle_url($returnurl), 'exclusive', $groupgroupings, $exclusive, array());
            $select->label = get_string('forceexclusive', 'local_ulpgcgroups');
            $select->formid = 'selectexclusive';
            $select->set_help_icon('forceexclusive', 'local_ulpgcgroups');
            $output .= $OUTPUT->render($select);
        $output .= html_writer::end_div();
        $output .= html_writer::div(' ', 'clearfix');
    }
    
    return $output;
}


/**
 * Returns a div with a warning message
 *
 * @param $group groups object
 * @return string HTML select form 
 */
function local_ulpgcgroups_controlledalert($group, $recover = true) {
    global $DB;
    $output = '';

    if($recover && !isset($group->component)) {
        $group->component = $DB->get_field('local_ulpgcgroups', 'component', array('groupid'=>$group->id));
    }
    if($group->component) {
        $output .=  html_writer::div(get_string('controlledgroupalert', 'local_ulpgcgroups'), 'ulpgc_dependency');
    }

    return $output;
}

/**
 * Process importing of user group membership. groups_add_member
 * with concepts from  Kirill Astashov
 * 
 * @param $adduser bool indicates if adding is required
 * @param $newgroup group object with user membership
 * @return notification message, if any
 */
function local_ulpgcgroups_import_group_users($adduser, $newgroup, $context = null) {
    global $DB, $OUTPUT;
    $message = '';

    if ($adduser && isset($newgroup->member)) {
        // ecastro ULPGC
        if(!$newmember = $DB->get_record( 'user',array('username'=>$newgroup->member))) {
            $newmember = $DB->get_record( 'user',array('idnumber'=>$newgroup->member));
        }
        if(!$newmember) {
            return;
        }
        
        if(!$context) {
            $context = context_course::instance($newgroup->courseid);
        }

        $gid = groups_get_group_by_name($newgroup->courseid, $newgroup->name);
        $groups = groups_get_user_groups($newgroup->courseid, $newmember->id);
        //Permissions check
        if(!has_capability('moodle/course:managegroups', $context)) {
            $message .= $OUTPUT->notification(get_string('nopermission'));
        } else if(in_array($gid,$groups['0'])) {
            $message .= $OUTPUT->notification(get_string('groupmembershipexists', 'group', array('name'=>$newgroup->name,'member'=>$newgroup->member) ));
        } else if(!groups_add_member($gid, $newmember->id)) {
            if(!is_enrolled($context, $newmember->id)) {
                $message .= $OUTPUT->notification(get_string('notenrolledincourse', 'group', array('name'=>$newgroup->name,'member'=>$newgroup->member) ));
            } else {
                $message .= $OUTPUT->notification(get_string('groupmembershipfailed', 'group', array('name'=>$newgroup->name,'member'=>$newgroup->member) ));
            }
        } else {
            $message .= $OUTPUT->notification(get_string('groupmembershipadded', 'group', array('name'=>$newgroup->name,'member'=>$newgroup->member)), 'notifysuccess' );
        }
    } else {
        if(isset($newgroup->member)) { // ecastro ULPGC
            $message .= $OUTPUT->notification(get_string('usernotfoundskip', 'group', array('name'=>$newgroup->name,'member'=>$newgroup->member)));
        }
    }
    
    return $message;
}


/**
 * Add supplementary data to users found (arra keyed by user role)
 * to allow enforcing of single group membership in a grouping.
 * 
 * @param $roles int ID for managed group 
 * @param $groupid int ID for managed group 
 * @param $single in ID for grouping, meaning single group membership in this groupong: 
 *                exclude users already in a group in this grouping
 * @return array($conditions,params) SQL fragments
 */
function local_ulpgcgroups_group_selector_roles($roles, $groupid = 0, $single = 0) {
    global $DB;

    if($single AND $groupid AND $roles) { // ecastro ULPGC to enforce single group membership
        foreach ($roles as $id => $grouped) {
            $users = $grouped->users;
            foreach($users as $key => $user) {
                $sql = "SELECT gm.id, gm.userid, gm.groupid
                            FROM {groups_members} gm
                            INNER JOIN {groupings_groups} gg ON gm.groupid = gg.groupid
                        WHERE  gg.groupingid = :groupingid AND userid = :userid ";
                $params = array('groupingid'=>$single, 'userid'=>$user->id);
                $memberships = $DB->get_records_sql($sql, $params);
                if(count($memberships) > 1) {
                    $user->nonsingle = 1;
                    $users[$key] = $user;
                }
            }
            $grouped->users = $users;
            $roles[$id] = $grouped;
        }
    }

    return $roles;
}    
    
/**
 * Add supplementary conditions to group_non_members_selector 
 * to limit potential users found based on source group
 * 
 * @param $groupid int ID for managed group 
 * @param $source int ID for source group, the parent group taking potential users from
 * @param $single in ID for grouping, meaning single group membership in this groupong: 
 *                exclude users already in a group in this grouping
 * @return array($conditions,params) SQL fragments
 */
function local_ulpgcgroups_nongroup_selector($groupid = 0, $source = 0, $single = 0) {
    global $DB;
    
    $conditions = '';
    $params = array();
    
    $sourcecondition = '';
    $sourceparams = array();
    // ecastro ULPGC to allow user selection from parent group
    if($source) {
        $sourcecondition = ' AND u.id IN (SELECT userid
                                        FROM {groups_members}
                                        WHERE groupid = :source) ';
        $sourceparams['source'] = $source;
    }

    $excluded = '';
    $excludeparams = array();
    if($single) { 
        $excluded = " AND u.id NOT IN (SELECT userid
                                            FROM {groups_members} gm
                                            INNER JOIN {groupings_groups} gg ON gm.groupid = gg.groupid
                                        WHERE  gg.groupingid = :groupingid ) ";
        $excludeparams['groupingid'] = $single;

    } else {
        $excluded = " AND u.id NOT IN (SELECT userid
                                        FROM {groups_members}
                                        WHERE groupid = :groupindex) ";
        $excludeparams['groupindex'] = $groupid;
    }

    $conditions = $sourcecondition.$excluded;
    $params = $sourceparams + $excludeparams;

    return array($conditions, $params);
}

/**
 * Find multiple membership to groups in grouping
 *
 * @param int $courseid
 * @param int $groupingid
 * @param mixed $excludedcaps array of string capabilities, users with capability are not duplicates
  * @return mixed false or array with duplicates by userid
 */
function local_ulpgcgroups_multiplegroups_grouping($courseid, $groupingid, $excludedcaps=false) {
    global $CFG, $DB;


    //first detect any duplicates
    $sql = "SELECT gm.userid, gg.groupingid
                FROM {groups}_members gm
                LEFT JOIN {groupings_groups} gg ON gg.groupid = gm.groupid
                WHERE  gg.groupingid = ?
                GROUP BY gm.userid
                HAVING ( count(gm.userid) > 1 ) ";

    $capabilities = array('moodle/course:managegroups', 'gradereport/grader:view');

    if(is_array($excludedcaps)) {
        $capabilities= $capabilities+$excludedcaps;
    }
    if(is_string($excludedcaps)) {
        $capabilities[]= $excludedcaps;
    }

    if($conflicts = $DB->get_records_sql($sql, array($groupingid))) {
        $users = array();
        $context = context_course::instance($courseid);
        foreach($conflicts as $userid=>$user){
            if (has_any_capability($capabilities, $context, $userid)) {
                continue;
            }
            $users[] = $userid;
        }
        if($users) {
            $params = array($courseid, $groupingid);
            list($insql, $inparams) = $DB->get_in_or_equal($users);
            $sql = "SELECT g.id, g.name, gm.userid, gg.groupingid
                        FROM {groups} g
                        LEFT JOIN {groups_members} gm ON g.id = gm.groupid AND g.courseid = ?
                        LEFT JOIN {groupings_groups} gg ON gg.groupid = gm.groupid
                        WHERE  gg.groupingid = ? AND gm.userid $insql
                        GROUP BY g.id  ";
            if($groups = $DB->get_records_sql($sql, array_merge($params, $inparams))) {
                return $groups;
            }
        }
    }
    return false;
}


/**
 * Checks whether the current user is permitted (using the normal UI) to
 * remove a specific group assuming that they have access to remove
 * groups in general.
 *
 * This checks if all members are allowed to be removed, first
 *
 * For automatically-created group member entries, this checks with the
 * relevant plugin to see whether it is permitted. The default, if the plugin
 * doesn't provide a function, is true.
 *
 * For other entries (and any which have already been deleted/don't exist) it
 * just returns true.
 *
 * @param mixed $grouporid  The group id or group object
 * @param string $component Optional component name e.g. 'enrol_imsenterprise'
 * @param int $itemid Optional itemid associated with component
 * @return bool True if user added successfully or the user is already a
 * member of the group, false otherwise.
 */
function local_ulpgcgroups_delete_group_allowed($grouporid, $component=null, $itemid=null) { 
    global $DB;

    $entry = '';
    if (is_object($grouporid)) {
        $groupid = $grouporid->id;
    } else {
        $groupid = $grouporid;
    }
    
    // Get entry
    if ((!$entry = $DB->get_record('local_ulpgcgroups', array('groupid' => $groupid), '*', IGNORE_MISSING)) || 
                    empty($entry->component)) {
        // If the entry does not exist, they are allowed to remove it (this
        // is consistent with groups_remove_member below).
        return true;
    }
  
    if($component && isset($itemid) && $entry) { // isset is esential, itemid maybe 0 legally
        if($entry->component == $component && $entry->itemid == $itemid) {
            return true;
        }
    }

    if($users = $DB->get_records('groups_members', array('groupid'=>$groupid))) {
        $allowed = true;
        foreach($users as $user) {
            $allowed = groups_remove_member_allowed($groupid, $user);
            if(!$allowed) {
                break;
            }
        }
        if(!$allowed) {
            return false;
        }
    }

    // It has a component value, so we need to call a plugin function (if it
    // exists); the default is to allow delete
    return component_callback($entry->component, 'allow_group_delete',
            array($entry->itemid, $entry->id), false);
}

/**
 * Add a new group
 *
 * @param stdClass $data group properties
 * @param stdClass $editform
 * @param array $editoroptions
 * @param string $component Optional component name e.g. 'enrol_imsenterprise'
 * @param int $itemid Optional itemid associated with component
 * @return id of group or false if error
 */
function local_ulpgcgroups_create_group($data, $component=null, $itemid=0, $editform = false, $editoroptions = false) { 
    global $CFG, $DB;

    $newid = groups_create_group($data, $editform, $editoroptions);
    
    $addid = local_ulpgcgroups_update_group_component($newid, $component, $itemid);
    
    return $newid;
}


/**
 * Add or update components details for a group
 *
 * @param int $groupid the ID for the grouo adding or uodating
 * @param string $component Optional component name e.g. 'enrol_imsenterprise'
 * @param int $itemid Optional itemid associated with component
 * @return id of group or false if error
 */
function local_ulpgcgroups_update_group_component($groupid, $component=null, $itemid=0) { 
    global $CFG, $DB;

    $data = new stdClass();
    $data->groupid = $groupid;
    $data->id = null;

    // Check the component exists if specified
    if (!empty($component)) {
        $dir = get_component_directory($component);
        if ($dir && is_dir($dir)) {
            // Component exists and can be used
            $data->component = $component;
            $data->itemid = $itemid;
        } else {
            throw new coding_exception('Invalid call to groups_create_group(). An invalid component was specified');
        }
    }
   
    if ($itemid !== 0 && empty($data->component)) {
        // An itemid can only be specified if a valid component was found
        throw new coding_exception('Invalid call to groups_create_group(). A component must be specified if an itemid is given');
    }

    if($data->component) {
        if($old = $DB->get_record('local_ulpgcgroups', array('groupid'=>$groupid))) {
            $data->id = $old->id;
            $DB->update_record('local_ulpgcgroups', $data);
        } else {
            $data->id = $DB->insert_record('local_ulpgcgroups', $data);
        }
    }
    return $data->id;
}


/**
 * Perform groups export to a file
 *
 * @param array/object $groups and iterable element containing groups ids & names to export
 * @param object $fromform data settings from UI
 * @return file to browser
 */
function local_ulpgcgroups_do_exportgroups($groups, $fromform, $columns) {
    global $CFG, $DB, $SESSION;
    include_once($CFG->libdir.'/phpword/Common/Autoloader.php');
    //require_once '/path/to/src/PhpWord/Autoloader.php';
    \PhpOffice\Common\Autoloader::register();
    //include_once($CFG->libdir.'/phpword/PhpWord/PhpWord.php');
    
    include_once($CFG->libdir.'/phpword/PhpWord/Autoloader.php');
    \PhpOffice\PhpWord\Autoloader::register();
    
    $phpWord = new \PhpOffice\PhpWord\PhpWord();
/*    
    $properties = $phpWord->getDocInfo();
    $properties->setCreator('My name');
    $properties->setCompany('My factory');
    $properties->setTitle('My title');
    $properties->setDescription('My description');
    $properties->setCategory('My category');
    $properties->setLastModifiedBy('My name');
    $properties->setCreated(mktime(0, 0, 0, 3, 12, 2014));
    $properties->setModified(mktime(0, 0, 0, 3, 14, 2014));
    $properties->setSubject('My subject');

  */  

    $headers = array('id'=>'N', 'fullname'=>get_string('fullnameuser'));
    
    $userfields = implode(', u.', array_merge(array('firstname', 'lastname', 'idnumber'), array_keys($columns)));
    $sortorder = 'u.firstname ASC, u.lastname ASC ';
    if($fromform->exportnameformat == 'lastname') {
        $sortorder = 'u.lastname ASC, u.firstname ASC ';
    }
    $SESSION->nameformat = $fromform->exportnameformat;
    
    $params = array('courseid1' => $fromform->id, 'courseid2' => $fromform->id, 'ctxid'=>$fromform->ctxid,
                    'now1'=>time(), 'now2'=>time());
    
    $rolesfields = '';
    if(isset($fromform->exportincludeuserroles) && $fromform->exportincludeuserroles) {
        $rolesfields = ",
                        (SELECT GROUP_CONCAT(DISTINCT rra.roleid  ORDER BY rra.roleid ASC SEPARATOR ',')
                        FROM {role_assignments} rra
                        WHERE rra.userid = uer.userid  AND rra.contextid = :rcontextid
                        GROUP BY rra.userid 
                        ORDER BY rra.roleid ) AS roles";
        $params['rcontextid'] = $fromform->ctxid;
        $headers['roles'] = get_string('roles');
        $roles = get_all_roles();
        $context = context_helper::instance_by_id($fromform->ctxid);
        $rolenames = role_fix_names($roles, $context, ROLENAME_ALIAS, true);
    }
    
    list($inrolesql, $inparams) = $DB->get_in_or_equal($fromform->exportuserroles, SQL_PARAMS_NAMED, 'r');
    $params = array_merge($params, $inparams);
    $names = get_all_user_name_fields(true, 'u');
    $sql = "SELECT  DISTINCT(uer.userid), u.$userfields, $names $rolesfields  
            FROM {user_enrolments} uer 
            JOIN {enrol} er ON uer.enrolid = er.id  AND er.status = 0 AND er.courseid = :courseid1 
            JOIN {user} u ON u.id = uer.userid 
            JOIN {role_assignments} ra ON ra.userid = uer.userid AND ra.contextid = :ctxid
            JOIN {groups_members} gm ON gm.userid = uer.userid AND gm.groupid = :gid
            WHERE er.courseid = :courseid2 AND uer.status = 0 AND uer.timestart < :now1 AND (uer.timeend = 0 OR uer.timeend > :now2)
                  AND ra.roleid $inrolesql
            ORDER BY $sortorder, u.idnumber ASC  "; 
    
    
    $headers = $headers + $columns;
    $extras = array();
    if($fromform->exportextracolumns) {
        if($extras = explode(',', $fromform->exportextracolumns)) {
            $i = 1;
            foreach($extras as $extra) {
                $headers['extra'.$i] = trim($extra);
                $i++;
            }
        }
    }
    
    
    
    $section = $phpWord->addSection();

    //print_object($columns);
    //print_object($sql);
   
    foreach($groups as $gid => $group) {
        $section->addText($group->name, array('bold'=>true, 'size'=>18));
        $section->addTextBreak();
    
        $TableStyleName = 'users';
        $TableStyle = array('borderSize' => 1, 'borderColor' => '000000', 'cellMargin' => 80);
        $TableFirstRowStyle = array('borderBottomSize' => 2, 'bgColor' => 'DDDDDD');
        $TableFontStyle = array('bold' => true);
        $phpWord->addTableStyle($TableStyleName, $TableStyle, $TableFirstRowStyle);    

        //$users = get_enrolled_users($context, $withcapability = '', $groupid = 0, $userfields = 'u.*', $orderby = null,$limitfrom = 0, $limitnum = 0, $onlyactive = false)  
        $params['gid'] = $gid;
        if($users = $DB->get_records_sql($sql, $params)) {
            $table = $section->addTable($TableStyleName);
            $table->addRow();
            foreach($headers as $header) {
                $table->addCell()->addText($header);
            }
            $i = 1;
            foreach($users as $user) {
            //print_object($user);
                $table->addRow();
                $table->addCell()->addText($i);
                $table->addCell()->addText(fullname($user, true));
                if(isset($headers['roles'])) {
                    $rolestr = '';
                    if($roles = explode(',', $user->roles)) {
                        foreach($roles as $k => $rid) {
                            $roles[$k] = $rolenames[$rid];
                        }
                        $rolestr = implode(',', $roles);
                    }
                    $table->addCell()->addText($rolestr);
                }
                foreach($columns as $key => $name) {
                    $table->addCell()->addText($user->{$key});
                }
                foreach($extras as $key => $name) {
                    $table->addCell()->addText('');
                }
                $i++;
            }
        } else {
            $section->addText(get_string('nousersfound'));
        }
        $section->addTextBreak();
        $section->addTextBreak();
        $section->addPageBreak(true);
    }

        // Saving the document as ODF file...
        
    //$phpWord->save('test.docx', 'Word2007', true);
    //$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'ODText');
    //$objWriter->save('helloWorld.odt');

    $file = clean_filename($fromform->filename.'.odt');
    $format = $fromform->dataformat;
    
    header("Content-Description: File Transfer");
    header('Content-Disposition: attachment; filename="' . $file . '"');
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, $format);
    $xmlWriter->save("php://output");
    
    
    $SESSION->nameformat = ''; 
    //send_http_headers();


    //write_footer($columns);
    
}



