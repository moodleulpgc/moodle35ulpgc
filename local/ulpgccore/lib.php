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
 * ulpgccore lib
 *
 * @package    local
 * @subpackage ulpgccore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


/**
 * Add editing nodes to flat navigation in Nav Drawer.
 *
 * @param global_navigation $navigation
 */
function local_ulpgccore_extend_navigation(global_navigation $navigation) {
    global $PAGE;
    
    $keys = $navigation->get_children_key_list();
    //print_object($keys);
        
    $coursehomenode = $PAGE->navigation->find($PAGE->course->id, navigation_node::TYPE_COURSE);
    
    //$beforekey = $coursehomenode ?  $coursehomenode->key : 'home';
    $beforekey = $coursehomenode ?  '1' : 'home';
    //home 1 myprofile currentcourse mycourses courses users
    
    // Check if admin wants us to insert custom nodes for users in Boost's nav drawer.
        // Fetch config.
    $customnodes = get_config('local_ulpgccore', 'customnavnodes');
    if (isset($customnodes) && !empty($customnodes)) {
        // If yes, do it.
        /*
        $customnodesret = local_ulpgccore_build_custom_nav_nodes($customnodes, $navigation, $beforekey,
                'localulpgccorecustomnav', true, true, true);
                */
/*
        // Check if admin wanted us to also collapse the custom nodes for users.
        if ($config->collapsecustomnodesusers == true) {
            // Remember the collapsible node for JavaScript.
            $collapsenodesforjs = array_merge($collapsenodesforjs, $customnodesret);
        }
        */
        if($node1 = $navigation->find('1', global_navigation::TYPE_COURSE)) {
        // Create course edit node.
        $edit = $PAGE->user_is_editing() ? 'off' : 'on';
        $editingnode = navigation_node::create(' XXXX ',
                new moodle_url('/course/view.php', array('id' => $PAGE->course->id, 
                                                            'sesskey'=>sesskey(), 'edit'=>$edit)),
                navigation_node::TYPE_SETTING,
                null,
                'ulpgccorenode1',
                null);
        // Add the course edit node.
        $editingnode->icon = new pix_icon('i/edit', '');
        $editingnode->showinflatnavigation = true;
        $node1->add_node($editingnode, 'frontpageloaded');
        }
        
        
    }
    
    
    
    $keys = $navigation->get_children_key_list();
    //print_object($keys);
    foreach($keys as $key) {
        if($root = $navigation->find($key, null)) {
            //print_object("rootkey = $key");
            //print_object(local_ulpgccore_boostnav_get_all_childrenkeys($root));
        }
    }
    

    //print_object("==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  ==  "); 
    
    
    
    if($coursehomenode) { 
        // Only proceed if we are inside a course and we are _not_ on the frontpage.
        if ($context = $PAGE->context->get_course_context(false)) {
            if(($context->contextlevel == CONTEXT_COURSE) && $PAGE->course->id != SITEID && 
                ((strpos($PAGE->pagetype, 'course-view') === 0) || (strpos($PAGE->pagetype, 'mod-') === 0))) {
                if($PAGE->user_allowed_editing()) {
                    // Create course edit node.
                    $edit = $PAGE->user_is_editing() ? 'off' : 'on';
                    $editingnode = navigation_node::create(get_string('courseedit'.$edit, 'local_ulpgccore'),
                            new moodle_url('/course/view.php', array('id' => $PAGE->course->id, 
                                                                        'sesskey'=>sesskey(), 'edit'=>$edit)),
                            navigation_node::TYPE_SETTING,
                            null,
                            'flateditcourse',
                            null);
                    // Add the course edit node.
                    $editingnode->icon = new pix_icon('i/edit', '');
                    $coursehomenode->add_node($editingnode, 'participants');
                }
    
                $adminoptions = course_get_user_administration_options($PAGE->course, $context);
                if($adminoptions->update) {
                    // Create coursesettings node.
                    $editingnode = navigation_node::create(get_string('editsettings', 'local_ulpgccore', get_string('course')),
                            new moodle_url('/course/edit.php', array('id' => $PAGE->course->id)),
                            navigation_node::TYPE_SETTING,
                            null,
                            'flatcoursesettings',
                            null);
                    // Add the coursesettings node.
                    $editingnode->icon = new pix_icon('t/edit', '');
                    $coursehomenode->add_node($editingnode, 'participants');
                }
            }

            if (($PAGE->context->contextlevel == CONTEXT_MODULE && 
                has_all_capabilities(array('local/ulpgccore:modedit', 'moodle/course:manageactivities'), $context))) {
                // Create modedit node.
                $mod = get_string('pluginname', $PAGE->activityname);
                $editingnode = navigation_node::create(get_string('editsettings', 'local_ulpgccore', $mod),
                        new moodle_url('/course/modedit.php', array('update' => $PAGE->cm->id, 'return'=>1)),
                        navigation_node::TYPE_SETTING,
                        null,
                        'flatmodedit',
                        null);
                // Add the modedit node.
                $editingnode->icon = new pix_icon('t/edit', '');
                $coursehomenode->add_node($editingnode, 'participants');
            }
        }
    }
}

/**
 * Custom course navigation
 *
 * @param navigation_node $navigation
 */
function local_ulpgccore_extend_navigation_course(navigation_node $navigation) {
    global $PAGE;

    // make module administration accesible from module inner pages
    if(strpos($PAGE->pagetype, 'mod-') !== false) {
        $PAGE->force_settings_menu(true);
    }
}    
    
function local_ulpgccore_extend_settings_navigation(settings_navigation $nav, context $context) {
    global $CFG, $PAGE;
    
    // modifications in course pages
    if ($PAGE->course && $PAGE->course->id != 1) {
        
    }
   if ($settingsnode = $nav->find('users', navigation_node::TYPE_CONTAINER)) {
        $url = new moodle_url('/local/ulpgccore/exportusers.php', array('id'=>$PAGE->course->id));
        $node = $settingsnode->create(get_string('exportusers', 'local_ulpgccore'), $url, navigation_node::TYPE_SETTING, null, 'exportusers', new pix_icon('i/export', ''));
        $key = 'manageinstances';
        if(!in_array($key, $settingsnode->get_children_key_list())) {
            $key = null;
        }
        $settingsnode->add_node($node, $key);
   } 
   
    // modifications in module pages
    if ($PAGE->cm) {
        if($PAGE->cm->score && get_config('local_ulpgccore', 'enabledadminmods')) {
            if ($settingsnode = $nav->find('modedit', navigation_node::TYPE_SETTING)) {
                if(!has_capability('local/ulpgccore:modedit', $context)) {
                    $settingsnode->hide();
                }
            }
            if ($settingsnode = $nav->find('roleassign', navigation_node::TYPE_SETTING)) {
                if(!has_capability('local/ulpgccore:modroles', $context)) {
                    $settingsnode->hide();
                }
            }
            if ($settingsnode = $nav->find('roleoverride', navigation_node::TYPE_SETTING)) {
                if(!has_capability('local/ulpgccore:modpermissions', $context)) {
                    $settingsnode->hide();
                }
            }          
        }
    }

}



/**
 * This function takes the plugin's custom nodes setting, builds the custom nodes and adds them to the given navigation_node.
 * Based on local_boostnavigation 
 *
 * @param string $customnodes
 * @param navigation_node $node
 * @param string $beforekey 
 * @param string $keyprefix
 * @param bool $showinflatnavigation
 * @param bool $collapse
 * @param bool $collapsedefault
 * @return array
 */
function local_ulpgccore_build_custom_nav_nodes($customnodes, navigation_node $node, $beforekey=null,
        $keyprefix='localulpgccorecustomnav', $showinflatnavigation=true, $collapse=false,
        $collapsedefault=false) {
    global $USER;

    // Initialize counter which is later used for the node IDs.
    $nodecount = 0;

    // Initialize variables for remembering the last parent node.
    $lastparentnode = null;
    $lastparentnodevisible = false;

    // Initialize variables for remembering the node keys for collapsing.
    $collapsenodesforjs = array();
    $collapselastparentprepared = false;

    // Make a new array on delimiter "new line".
    $lines = explode("\n", $customnodes);

    // Parse node settings.
    foreach ($lines as $line) {

        // Trim setting lines.
        $line = trim($line);

        // Skip empty lines.
        if (strlen($line) == 0) {
            continue;
        }

        // Initialize node variables.
        $nodeurl = null;
        $nodetitle = null;
        $nodevisible = false;
        $nodeischild = false;
        $nodekey = null;

        // Make a new array on delimiter "|".
        $settings = explode('|', $line);

        // Check for the mandatory conditions first.
        // If array contains too less or too many settings, do not proceed and therefore do not create the node.
        // Furthermore check it at least the first two mandatory params are not an empty string.
        if (count($settings) >= 2 && count($settings) <= 4 && $settings[0] !== '' && $settings[1] !== '') {
            foreach ($settings as $i => $setting) {
                $setting = trim($setting);
                if (!empty($setting)) {
                    switch ($i) {
                        // Check for the mandatory first param: title.
                        case 0:
                            // Check if this is a child node and get the node title.
                            if (substr($setting, 0, 1) == '-') {
                                $nodeischild = true;
                                $nodetitle = substr($setting, 1);
                            } else {
                                $nodeischild = false;
                                $nodetitle = $setting;
                            }

                            // Set the node to be basically visible.
                            $nodevisible = true;

                            break;
                        // Check for the mandatory second param: URL.
                        case 1:
                            // Get the URL.
                            try {
                                $nodeurl = new moodle_url($setting);
                                $nodevisible = true;
                            } catch (moodle_exception $exception) {
                                // We're not actually worried about this, we don't want to mess up the navigation
                                // just for a wrongly entered URL. We just don't create a node in this case.
                                $nodeurl = null;
                                $nodevisible = false;
                            }

                            break;
                        // Check for the optional third param: language support.
                        case 2:
                            // Only proceed if something is entered here. This parameter is optional.
                            // If no language is given the node will be added to the navigation by default.
                            $nodelanguages = array_map('trim', explode(',', $setting));
                            $nodevisible &= in_array(current_language(), $nodelanguages);
                    }

                    // Support for inheritance of the parent node's visibility to his child notes.
                    if ($nodeischild == false) {
                        // To inherit the parent node's visibility to his child nodes later, we have to remember
                        // this visibility now.
                        $lastparentnodevisible = $nodevisible;
                    } else {
                        // Inherit the parent node's visibility. This overrules the child node's visibility.
                        $nodevisible &= $lastparentnodevisible;
                    }

                }
            }
        }

        // Add a custom node to the given navigation_node.
        // This is if all mandatory params are set and the node matches the optional given language setting.
        if ($nodevisible) {

            // Generate node key.
            $nodekey = $keyprefix.++$nodecount;

            // Create custom node.
            $customnode = navigation_node::create($nodetitle,
                    $nodeurl,
                    global_navigation::TYPE_CUSTOM,
                    null,
                    $nodekey,
                    null);

            // Show the custom node in Boost's nav drawer if requested.
            if ($showinflatnavigation) {
                $customnode->showinflatnavigation = true;
            }

            // If it's a parent node.
            if (!$nodeischild) {
                // If the nodes should be collapsed and collapsing hasn't been prepared yet, prepare collapsing of the parent node.
                if ($collapse) {
                    // Remember that we haven't prepared collapsing yet for this parent node.
                    $collapselastparentprepared = false;

                    // If the node shouldn't be collapsed, set some node attributes to avoid side effects with the CSS styles
                    // which ship with this plugin.
                } else {
                    // Change the isexpandable attribute for the parent node to false
                    // (it's the default in Moodle core, just to be safe).
                    $customnode->isexpandable = false;
                }

                // Add the custom node to the given navigation_node.
                $node->add_node($customnode, $beforekey);

                // Remember the node as a potential parent node for the next node.
                $lastparentnode = $customnode;

                // Get the user preference for the collapse state of this custom node and set the collapse attribute accordingly.
                $userprefcustomnode = get_user_preferences('local_ulpgccorenav-collapse_'.$nodekey.'node', $collapsedefault);
                if ($userprefcustomnode == 1) {
                    $customnode->collapse = true;
                } else {
                    $customnode->collapse = false;
                }

                // Otherwise, if it's a child node.
            } else {
                // If the nodes should be collapsed and collapsing hasn't been prepared yet, prepare collapsing of the parent node.
                // This is done here (in the first child node and not in the parent node) because parent nodes without any child
                // node shouldn't be collapsible.
                if ($collapse && !$collapselastparentprepared) {
                    // Remember the node key for collapsing.
                    $collapsenodesforjs[] = $lastparentnode->key;

                    // Change the isexpandable attribute for the parent node to true.
                    $lastparentnode->isexpandable = true;
                    // Remember that we have prepared collapsing now.
                    $collapselastparentprepared = true;
                }

                // For some crazy reason, if we add the child node directly to the parent node, it is not shown in the
                // course navigation section.
                // Thus, add the custom node to the given navigation_node.
                $node->add_node($customnode, $beforekey);
                // And change the parent node directly afterwards.
                $customnode->set_parent($lastparentnode);

                // Get the user preference for the collapse state of the last parent node and set the hidden attribute accordingly.
                $userprefcustomnode = get_user_preferences('local_ulpgccorenav-collapse_'.$lastparentnode->key.'node',
                        $collapsedefault);
                if ($userprefcustomnode == 1) {
                    $customnode->hidden = true;
                } else {
                    $customnode->hidden = false;
                }
            }
        }
    }

    // Return the node keys for collapsing.
    return $collapsenodesforjs;
}

/**
 * Moodle core does not have a built-in functionality to get all keys of all children of a navigation node,
 * so we need to get these ourselves.
 *
 * @param navigation_node $navigationnode
 * @return array
 */
function local_ulpgccore_boostnav_get_all_childrenkeys(navigation_node $navigationnode) {
    // Empty array to hold all children.
    $allchildren = array();

    // No, this node does not have children anymore.
    if (count($navigationnode->children) == 0) {
        return array();

        // Yes, this node has children.
    } else {
        // Get own own children keys.
        $childrennodeskeys = $navigationnode->get_children_key_list();
        // Get all children keys of our children recursively.
        foreach ($childrennodeskeys as $ck) {
            $allchildren = array_merge($allchildren, local_ulpgccore_boostnav_get_all_childrenkeys($navigationnode->get($ck)));
        }
        // And add our own children keys to the result.
        $allchildren = array_merge($allchildren, $childrennodeskeys);

        // Return everything.
        return $allchildren;
    }
}


function local_ulpgccore_cron() {
    global $SITE;
    
    $now = time();

    if($emails = get_config('local_ulpgccore', 'cronchekemail')) {
        $lastcron = get_config('local_ulpgccore', 'lastcron');
        $croncheck = get_config('local_ulpgccore', 'croncheck');
        if($croncheck && (($now - $lastcron)/3600 > $croncheck)) {        
            $user = core_user::get_support_user();
            $user->mailformat = 1;
            $user->id = 1;
            foreach(explode(',', $emails) as $email) {
                $user->email = trim($email);           
                $taxet = $html = $subject = $SITE->shortname. '  cron delayed > '. $croncheck;
                email_to_user($user, 'local_ulpgccore_croncheck', $subject, $text, $html);
            }
        }
    }
    
    return true;
}

/**
 * Returns an array of user fields
 * @return stdclass course object with fields from ULPGC extra table
 */
function local_ulpgccore_get_userfields() {
    return array(
        'email'       => get_string('email'),
        'phone1'      => get_string('phone'),
        'phone2'      => get_string('phone2'),
        'url'      => get_string('webpage'),
        'icq'      => get_string('icqnumber'),
        'skype'      => get_string('skypeid'),
        'yahoo'      => get_string('yahooid'),
        'msn'      => get_string('msnid'),
        'department'  => get_string('department'),
        'institution' => get_string('institution'),
        'city' => get_string('city'),
        'address'      => get_string('address'),
        'aim' => get_string('aimid'),
        'country' => get_string('country'),
        'lang' => get_string('language'),
        'timezone'      => get_string('timezone'),
    );
}
    

/**
 * Checks if extra course details exist for given courses and add them to course object
 * @param int/object $course object or ID 
 * @return stdclass course object with fields from ULPGC extra table
 */
function local_ulpgccore_get_course_details($courseorid) {
    global $DB;
    
    if(is_int($courseorid) || ctype_digit($courseorid)) {
        $course = $DB->get_record('course', array('id'=>$courseorid), '*', MUST_EXIST);
    } else {
        $course = $courseorid;
    }
    if($extra = $DB->get_record('local_ulpgccore_course', array('courseid'=>$course->id))) {
        $extra = get_object_vars($extra);
    } else {
        $extra = $DB->get_columns('local_ulpgccore_course');
        $extra = array_fill_keys(array_keys($extra), null);
    }
    unset($extra['id']);

    foreach($extra as $field => $value) {
        $course->{$field} = $value; 
    }
    
    return $course;
}


/**
 * Checks if extra course details exist for given courses and add them to course object
 * @param int/object $course object or ID 
 * @return stdclass course object with fields from ULPGC extra table
 */
function local_ulpgccore_get_category_details($categoryorid) {
    global $DB;
    
    if(is_int($categoryorid) || ctype_digit($categoryorid)) {
        $category = $DB->get_record('course_categories', array('id'=>$categoryorid), '*', MUST_EXIST);
    } else {
        $category = $categoryorid;
    }
    if($extra = $DB->get_record('local_ulpgccore_categories', array('categoryid'=>$category->id))) {
        $extra = get_object_vars($extra);
    } else {
        $extra = $DB->get_columns('local_ulpgccore_categories');
        $extra = array_fill_keys(array_keys($extra), null);
    }
    unset($extra['id']);

    foreach($extra as $field => $value) {
        $category->{$field} = $value; 
    }
    
    return $category;
}

/**
 * Checks if extra course details exist for given courses and add them to course object
 * @param array $courseids an array of course IDs to select full objects
 * @param string $fields names of the fields we want to include in each returned object
 * @param string $sort valid ORDER statement for query
 * @return array Array of course objects with fields from ULPGC extra table
 */
function local_ulpgccore_load_courses_details($courseids, $fields, $sort= '') {
    global $DB;

    if(!$courseids) {
        return array();
    }
    
    if(!$sort) {
        $sort = 'sortorder ASC';
    }
    
    list($insql, $params) = $DB->get_in_or_equal($courseids);
    $sql = "SELECT $fields 
            FROM {course} c 
            LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid 
            WHERE c.id $insql 
            ORDER BY $sort ";
    return $DB->get_records_sql($sql, $params); 
}


/**
 * Checks if extra course details exist for given courses and add them to course object
 * @param array $catids an array of course category IDs to select full objects
 * @param string $fields names of the fields we want to include in each returned object
 * @param string $sort valid ORDER statement for query
 * @return array Array of course objects with fields from ULPGC extra table
 */
function local_ulpgccore_load_categories_details($catids, $fields, $sort = '') {
    global $DB;

    if(!$catids) {
        return array();
    }
    
    if(!$sort) {
        $sort = 'sortorder ASC';
    }
    
    list($insql, $params) = $DB->get_in_or_equal($catids);
    $sql = "SELECT $fields 
            FROM {course_categories} c 
            LEFT JOIN {local_ulpgccore_categories} uc ON c.id = uc.categoryid 
            WHERE c.id $insql 
            ORDER BY $sort ";
    return $DB->get_records_sql($sql, $params); 
}


function local_ulpgccore_restricted_icons(cm_info $mod, $renderer) {
    $modicons = '';
    $imgalt = '';
    if (!empty($mod->availableinfo)) {
        $formattedinfo = \core_availability\info::format_info(
            $mod->availableinfo, $mod->get_course());
        $imgalt = format_string($formattedinfo);
    }

    if(!$imgalt && has_capability('moodle/course:viewhiddenactivities', $mod->context)) {
        $imgalt = get_string('accessrestrictions', 'availability');
    }
    if($imgalt) {
        $lockedicon = new pix_icon('t/locked', $imgalt, '',
                array('title' => $imgalt));
        $modicons .= html_writer::tag('span', $renderer->render($lockedicon),
                array('class' => ' restriction'));
    }
    return $modicons;
}

function local_ulpgccore_custom_profile_form($mform) {

        $element = $mform->createElement('static', 'info1', '', get_string('userformwarning', 'local_ulpgccore'));
        $mform->insertElementBefore($element, 'moodle'); 
        $mform->setType('info1', PARAM_NOTAGS);
        unset($element);
    
        if ($mform->elementExists('url')) {
            $element =  $mform->createElement('static', 'userformpublic', '', get_string('userformpublic', 'local_ulpgccore'));
            $mform->insertElementBefore($element, 'url'); 
            $mform->setType('userformpublic', PARAM_NOTAGS);
            unset($element);
        }

        if ($mform->elementExists('idnumber')) {
            if ($mform->elementExists('phone1')) {
                $element = $mform->getElement('idnumber');
                $mform->removeElement('idnumber');
                $mform->insertElementBefore($element, 'phone1'); 
                unset($element);
            }

            $element =  $mform->createElement('static', 'userformhidden', '', get_string('userformhidden', 'local_ulpgccore'));
            $mform->insertElementBefore($element, 'idnumber'); 
            $mform->setType('userformhidden', PARAM_NOTAGS);
            unset($element);
        }

        if ($mform->elementExists('aim')) {
            if ($mform->elementExists('address')) {
                $element = $mform->getElement('aim');
                $mform->removeElement('aim');
                $mform->insertElementBefore($element, 'address'); 
                unset($element);
            }
        }

        $mform->addElement('static', 'info2', '', get_string('userformwarning', 'local_ulpgccore'));
        $mform->setType('info2', PARAM_NOTAGS);
}



/**
 * Get sql statement & params to find all relevant course users & data for exportation
 *
 * @uses $DB
 * @param int $courseid Course ID
 * @param object $context course context object
 * @param object $fromform data from user export form
 * @return array ($sql, $params, $columns) for use out
 */
function local_ulpgccore_exportuser_getsql($courseid, $context, $fromform) {
    global $DB;

    $columns = array('firstname' => get_string('firstname'), 
                        'lastname' => get_string('lastname'), 
                        'idnumber' => get_string('idnumber'));
    $userdetails = local_ulpgccore_get_userfields();
    foreach($userdetails as $field => $name) {
        if(isset($fromform->{$field}) && $fromform->{$field}) {
        $columns[$field] = $name;
        }
    }
    $userfields = implode(', u.', array_keys($columns));
    
    $usergroups = '';
    $params = array('courseid1' => $courseid, 'courseid2' => $courseid, 'ctxid'=>$context->id,
                'now1'=>time(), 'now2'=>time());

    $grouping = ($fromform->exportgroupsgrouping > 0) ? $fromform->exportgroupsgrouping : 0;           
    $coursegids = array_keys(groups_get_all_groups($courseid, 0, $grouping, 'g.id'));             
    if($fromform->exportgroupid || $fromform->exportgroupsgrouping) {
        $groupingwhere = '';
        if($fromform->exportgroupsgrouping) {
            if($fromform->exportgroupsgrouping < 0) {
                // non-grouping: select groups that are NOT members of any grouping
                $sql = "SELECT DISTINCT(g.id), g.id AS gid
                            FROM {groups} g
                            LEFT JOIN {groupings_groups} gg ON gg.groupid = g.id
                            WHERE g.courseid = ? AND gg.id IS NULL ";
                $coursegids = $DB->get_records_sql_menu($sql, array($courseid));
            }
            list($ingroupingsql, $inparams) = $DB->get_in_or_equal($coursegids, SQL_PARAMS_NAMED, 'g');
            $groupingwhere = " AND gm.groupid $ingroupingsql ";
            $params = array_merge($params, $inparams);
        }
       
        if($fromform->exportgroupid == -2) {
            $usergroups = " AND NOT EXISTS(SELECT 1
                                FROM {groups_members} gm 
                                JOIN {groups} g ON g.id = gm.groupid
                                WHERE g.courseid = er.courseid AND gm.userid = uer.userid )";
        } elseif($fromform->exportgroupid == -1) {
            $usergroups = " AND EXISTS(SELECT 1
                                FROM {groups_members} gm 
                                JOIN {groups} g ON g.id = gm.groupid
                                WHERE g.courseid = er.courseid AND gm.userid = uer.userid
                                $groupingwhere)";
        } elseif($fromform->exportgroupid > 0) {
            $usergroups = " AND EXISTS(SELECT 1
                                FROM {groups_members} gm 
                                WHERE gm.userid = uer.userid 
                                AND gm.groupid = :groupid 
                                $groupingwhere)";
            $params['groupid'] = $fromform->exportgroupid;
        } else {
            $usergroups = " AND EXISTS(SELECT 1
                                FROM {groups_members} gm 
                                WHERE gm.userid = uer.userid 
                                $groupingwhere)";
        }
    }

    $rolesfields = '';
    $groupsfields = '';
    $maxroles = 0;
    if(isset($fromform->exportincludeuserroles) && $fromform->exportincludeuserroles) {
        $rolesfields = ",
                        (SELECT GROUP_CONCAT(DISTINCT rra.roleid  ORDER BY rra.roleid ASC SEPARATOR ',')
                        FROM {role_assignments} rra
                        WHERE rra.userid = uer.userid  AND rra.contextid = :rcontextid
                        GROUP BY rra.userid 
                        ORDER BY rra.roleid ) AS roles";
        $params['rcontextid'] = $context->id;
        $maxroles = 0;

        $sql = "SELECT  uer.userid, COUNT(DISTINCT(ra.roleid)) AS rolecount
                FROM {user_enrolments} uer 
                JOIN {enrol} er ON uer.enrolid = er.id  AND er.status = 0 AND er.courseid = :courseid1 
                JOIN {role_assignments} ra ON ra.userid = uer.userid AND ra.contextid = :ctxid
                WHERE er.courseid = :courseid2 AND uer.status = 0 AND uer.timestart < :now1 AND (uer.timeend = 0 OR uer.timeend > :now2)
                    $usergroups 
                GROUP BY uer.userid
                ORDER BY rolecount DESC";
        if($maxroles = $DB->get_records_sql($sql, $params, 0, 1)) {
            $maxroles = reset($maxroles);
            $maxroles = $maxroles->rolecount;
        }
    }
    for($i = 1; $i <= $maxroles; $i++) {
        $columns['role'.$i] = get_string('role').$i;
    }

    $maxgroups = 0;
    if(isset($fromform->exportincludeusergroups) && $fromform->exportincludeusergroups) {
        $listgroupingjoin = '';
        if(isset($fromform->exportonlygrouping)) {
            if($fromform->exportonlygrouping == -1) {
                if($fromform->exportgroupsgrouping > 0) {
                    $fromform->exportonlygrouping = $fromform->exportgroupsgrouping;
                } else {
                    $fromform->exportonlygrouping = 0;
                }
            }
            if($fromform->exportonlygrouping) {
                $listgroupingjoin = "JOIN {groupings_groups} ugg ON ugg.groupid = ugm.groupid AND ugg.groupingid = :listgroupingid";
                $params['listgroupingid'] = $fromform->exportonlygrouping;
            }
        }
    
        $groupsfields = ",
                        (SELECT GROUP_CONCAT(DISTINCT ug.name  ORDER BY ug.name ASC SEPARATOR ',')
                        FROM {groups_members} ugm 
                        JOIN {groups} ug ON ug.id = ugm.groupid
                        $listgroupingjoin
                        WHERE ugm.userid = uer.userid AND ug.courseid = er.courseid
                        GROUP BY ugm.userid 
                        ORDER BY ug.name)  AS groups";
        $maxgroups = 0;
        
        
        if($fromform->exportonlygrouping) {
            $coursegids = array_keys(groups_get_all_groups($courseid, 0, $fromform->exportonlygrouping, 'g.id, g.idnumber')); 
        }
        list($ingroupsql, $inparams) = $DB->get_in_or_equal($coursegids, SQL_PARAMS_NAMED, 'gmg', true, 0);
        $sql = "SELECT uer.userid, COUNT(DISTINCT(ugm.groupid)) AS groupcount
                FROM {user_enrolments} uer 
                JOIN {enrol} er ON uer.enrolid = er.id  AND er.status = 0 AND er.courseid = :courseid1 
                JOIN {role_assignments} ra ON ra.userid = uer.userid AND ra.contextid = :ctxid
                LEFT JOIN {groups_members} ugm ON ugm.userid = uer.userid AND ugm.groupid $ingroupsql
                WHERE uer.status = 0 AND uer.timestart < :now1 AND (uer.timeend = 0 OR uer.timeend > :now2)
                    $usergroups 
                GROUP BY uer.userid, ra.roleid
                ORDER BY groupcount DESC";
        if($maxgroups = $DB->get_records_sql($sql, array_merge($params, $inparams), 0, 1)) {
            $maxgroups = reset($maxgroups);
            $maxgroups = $maxgroups->groupcount;
        }
    }
    for($i = 1; $i <= $maxgroups; $i++) {
        $columns['group'.$i] = get_string('group').$i;
    }

    list($inrolesql, $inparams) = $DB->get_in_or_equal($fromform->exportuserroles, SQL_PARAMS_NAMED, 'r');
    $params = array_merge($params, $inparams);
   

    $sortorder = array('lastname', 'firstname', 'idnumber');
    $key = array_search($fromform->exportsort, $sortorder);
    if($key!==false){
        unset($sortorder[$key]);
    }
    $sortorder = array($fromform->exportsort) + $sortorder;
    $sortorder = implode(' ASC, u.', $sortorder);
    
    $sql = "SELECT  DISTINCT(uer.userid), u.$userfields  
                $rolesfields 
                $groupsfields
            FROM {user_enrolments} uer 
            JOIN {enrol} er ON uer.enrolid = er.id  AND er.status = 0 AND er.courseid = :courseid1 
            JOIN {user} u ON u.id = uer.userid 
            JOIN {role_assignments} ra ON ra.userid = uer.userid AND ra.contextid = :ctxid
            WHERE er.courseid = :courseid2 AND uer.status = 0 AND uer.timestart < :now1 AND (uer.timeend = 0 OR uer.timeend > :now2) 
                    AND ra.roleid $inrolesql
                $usergroups
            ORDER BY u.$sortorder ASC "; 
    return array($sql, $params, $columns);
}

/**
 * Processes a user row from raw SQL to dataformat export format
 *
 * @uses $SESSION
 * @param object $row user data to export
 * @return object data for exportation
 */
function local_ulpgccore_exportuser_row($row) {
    global $SESSION;

    $columns = $SESSION->local_ulpgccore_export_columns;
    $rolenames = $SESSION->local_ulpgccore_role_names;

    if(isset($row->roles) && $rolenames) {
        if($roles = explode(',', $row->roles)) {
            $i = 1;
            $name = get_string('role');
            foreach($roles as $rid) {
                $col = 'role'.$i;    
                $row->{$col}  = $rolenames[$rid];
                $i++;
            }
        
        }
    }
    
    if(isset($row->groups)) {
        if($groups = explode(',', $row->groups)) {
            $i = 1;
            $name = get_string('group');
            foreach($groups as $gname) {
                $col = 'group'.$i;    
                $row->{$col}  = $gname;
                $i++;
            }
        }
    }

/*    
    $newrow = new StdClass();
    foreach($columns as $col) {
        if(isset($row->{$col})) {
            $newrow->{$col} = $row->{$col};
        } else {
            $newrow->{$col} = '';
        }
    }
 */   
    $newrow = array();
    foreach($columns as $col) {
        if(isset($row->{$col})) {
            $newrow["$col"] = $row->{$col};
        } else {
            $newrow["$col"] = '';
        }
    }

 
    return $newrow;
}


/**
 * Allows to know if there is something changed, or un-attended activity (posts, assignments)
 *
 * @uses $CFG, $USER
 * @param object $course Course object we want to query
 * @return bool true if activity exists
 */
function local_ulpgccore_course_recent_activity($course) {
    global $CFG, $DB, $USER;

    if(!isset($USER->ulpgclastactivity)) {
        $USER->ulpgclastactivity = array();
        $USER->ulpgclastactivity[$course->id] = 0;
    }
    if(!isset($USER->ulpgclastactivity[$course->id])) {
        $USER->ulpgclastactivity[$course->id] = 0;
    }
    if(!isset($USER->ulpgcrecentactivity)) {
        $USER->ulpgcrecentactivity = array();
        $USER->ulpgcrecentactivity[$course->id] = false;
    }
    if(!isset($USER->ulpgcrecentactivity[$course->id])) {
        $USER->ulpgcrecentactivity[$course->id] = false;
    }

    $now = time();
    $delay = 60;
    if(isset($USER->ulpgcrecentactivity[$course->id]) && ($USER->ulpgclastactivity[$course->id] > (time() - $delay))) {
        return $USER->ulpgcrecentactivity[$course->id];
    }

    $context = context_course::instance($course->id);

    if(!is_enrolled($context, $USER)) {
        $USER->ulpgclastactivity[$course->id] = $now;
        $USER->ulpgcrecentactivity[$course->id] = false;
        return false;
    }

    if(isset($USER->lastlogin)) {
        $timestart = $USER->lastlogin;
    } else {
        $timestart = time() - 172800;  // define('COURSE_MAX_RECENT_PERIOD', 172800);
    }
    if (!empty($USER->lastcourseaccess[$course->id])) {
        if ($USER->lastcourseaccess[$course->id] > $timestart) {
            $timestart = $USER->lastcourseaccess[$course->id];
        }
    }

    $USER->lastcourseaccess[$course->id] = time();

    /// TODO students test for changes in GRADES

    /// TODO teacher test for changes in GRADES

    $checkedmodules = array('assign', 'dialogue', 'forum', 'data', 'glossary', 'scheduler', 'quiz', 'tracker');

    $courseinfo = get_fast_modinfo($course);
    $mods = $courseinfo->get_cms();
    foreach($mods as $mod) {
        if (empty($mod->modname) || !in_array($mod->modname, $checkedmodules) || !$mod->uservisible) {
            continue;
        }
        $news = local_ulpgccore_mod_recent_activity($mod, $timestart);
        if (!empty($news)) {
            $USER->ulpgclastactivity[$course->id] = $now;
            $USER->ulpgcrecentactivity[$course->id] = true;
            return $news;
        }
    }

/*
    /// Now display new things from each module
    if($rawmods = get_course_mods($course->id)) {
        foreach($rawmods as $mod) {    // Index the mods
            if (empty($mod->modname) || !in_array($mod->modname, $checkedmodules)) {
                continue;
            }
            $tempmod = new StdClass;
            $tempmod->course = $course->id;
            $tempmod->id = $mod->instance;
            $modvisible = instance_is_visible($mod->modname,$tempmod);
            if ($modvisible) {
                $news = false;
                $news = ulpgc_mod_recent_activity($course, $mod->modname, $mod->id, $mod, $timestart);
                if (!empty($news)) {
                    $USER->ulpgclastactivity[$course->id] = $now;
                    $USER->ulpgcrecentactivity[$course->id] = true;
                    return $news;
                }
            }
        }
    }
*/
    return false;
}


/**
 * Allows to know if there is something changed, or un-attended activity (posts, assignments)
 * much code is borrowed from print_recent_activity
 *
 * @uses $CFG, $USER, $QTYPES
 * @param int/object $courseorid Course ID
 * @param string $module module name
 * @param int $moduleid moduleid for tested instance
 * @param int $timestart timestamp to check activity after this time
  * @return midex bool false if NO activity detected or either true or string with url to see
 */

function local_ulpgccore_mod_recent_activity($cm, $timestart=0) {
    global $CFG, $DB, $USER, $QTYPES;
  
    $news = false;
    
    $course = $cm->get_course();
    $courseid = $course->id;
    $module = $cm->modname;

    if(!isset($USER->ulpgcrecentmodactivity)) {
        $USER->ulpgcrecentmodactivity = array();
        $USER->ulpgcrecentmodactivity[$cm->id] = false;
    }
    if(!isset($USER->ulpgcrecentmodactivity[$cm->id])) {
        $USER->ulpgcrecentmodactivity[$cm->id] = false;
    }

    if(!isset($USER->ulpgclastmodactivity)) {
        $USER->ulpgclastmodactivity = array();
        $USER->ulpgclastmodactivity[$cm->id] = 0;
    }
    if(!isset($USER->ulpgclastmodactivity[$cm->id])) {
        $USER->ulpgclastmodactivity[$cm->id] = false;
    }
    
    $delay = 60;
    $now = time();
    if(isset($USER->ulpgcrecentmodactivity[$cm->id]) && ($USER->ulpgclastmodactivity[$cm->id] > (time() - $delay))  ) {
        return $USER->ulpgcrecentmodactivity[$cm->id];
    }

    if(!$timestart) {
        if(isset($USER->lastlogin)) {
            $timestart = $USER->lastlogin;
        } else {
            $timestart = time() - 172800;  // define('COURSE_MAX_RECENT_PERIOD', 172800);;
        }
        if (!empty($USER->lastcourseaccess[$courseid])) {
            if ($USER->lastcourseaccess[$courseid] > $timestart) {
                $timestart = $USER->lastcourseaccess[$courseid];
            }
        }
    }

    if (!$cm->uservisible) {
        $USER->ulpgclastmodactivity[$cm->id] = $now;
        $USER->ulpgcrecentmodactivity[$cm->id] = false;
        return false;
    }

    $modcontext = $cm->context;

    // some shortcuts
    $userid = $USER->id;

    $news = false;
    $url = '';
    $linktext = '';
    $actions = array();

    /// TODO
    /// TODO  ONLY SEARCH FOR ACTIVITY RELEVANT FOR $USER->id
    /// TODO

    switch ($module) {
    case 'assign' :
        if($cangrade = has_capability('mod/assign:grade', $modcontext, $userid, false)) {
            $params = array();
            if($users = ulpgc_get_activity_users($cm, $modcontext, $userid, 'mod/assign:submit')) {
                list($insql, $params) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'u');
            } else {
                break;
            }
            include_once($CFG->dirroot . '/mod/assign/locallib.php');
            $params['assignid'] = $cm->instance;
            $params['submitted'] = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
            $sql = "SELECT COUNT(s.userid)
                    FROM {assign_submission} s
                    JOIN {assign} a ON s.assignment = a.id AND a.grade != 0 
                    LEFT JOIN {assign_grades} g ON
                            s.assignment = g.assignment AND
                            s.userid = g.userid AND
                            g.attemptnumber = s.attemptnumber
                    WHERE   s.latest = 1 AND
                            s.assignment = :assignid AND
                            s.timemodified IS NOT NULL AND
                            s.status = :submitted AND
                            s.userid $insql AND
                            (s.timemodified > g.timemodified OR g.timemodified IS NULL OR g.grade IS NULL OR g.grade < 0)";
            $news = $DB->count_records_sql($sql, $params);
            if($news) {
                $linktext = get_string('ungradedactivity', 'local_ulpgccore', $news);
                $actions = array('action'=>'grading');
            }
        }
        break;

    case 'dialogue' :
        include_once($CFG->dirroot . '/mod/dialogue/locallib.php');
        $news = dialogue_cm_unreplied_total($cm);
        break;

    case 'forum' :
        include_once($CFG->dirroot . '/mod/forum/lib.php');
        if(!$course) {
            $course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
        }
        $news = forum_tp_count_forum_unread_posts($cm, $course);
        break;

    case 'scheduler' :
        $now = time();
        $params = array('scheduler'=>$cm->instance, 'now'=>$now);
        $sql = "SELECT COUNT(ss.id)
                FROM {scheduler_slots} ss
                JOIN {scheduler_appointment} sa ON ss.id = sa.slotid AND sa.slotid IS NOT NULL
                WHERE schedulerid = :scheduler  AND sa.attended = 0 AND ss.starttime >= :now " ;

        $teacher = '';
        $student = '';
        if($canattend = has_capability('mod/scheduler:attend', $modcontext, $userid, false)) {
            $teacher = " AND ss.teacherid = :teacher ";
            $params['teacher'] = $userid;
        } elseif($canappoint = has_capability('mod/scheduler:appoint', $modcontext, $userid, false)) {
            $student = " AND sa.studentid = :student ";
            $params['student'] = $userid;
        }

        $sql = "SELECT COUNT(ss.id)
                FROM {scheduler_slots} ss
                WHERE schedulerid = :scheduler  AND ss.starttime >= :now
                AND EXISTS (SELECT sa.slotid FROM {scheduler_appointment} sa
                            WHERE sa.slotid = ss.id AND sa.attended = 0 $student ) $teacher";

        $news = $DB->count_records_sql($sql, $params);
        break;

    case 'choice' :
        break;

    case 'data' :
        $canapprove = has_capability('mod/data:approve', $modcontext, $userid, false);
        if($canapprove) {
            $params = array();
            if($users = ulpgc_get_activity_users($cm, $modcontext, $userid, 'mod/data:writeentry')) {
                list($insql, $params) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'u');
            } else {
                break;
            }
            $select = " dataid = :data AND approved = 0 AND userid $insql
                        AND timemodified > :start  AND userid <> :user ";
            $params['data'] = $cm->instance;
            $params['start'] = $timestart;
            $params['user'] = $userid;
            $news = $DB->count_records_select('data_records', $select, $params);
        }
        break;

    case 'glossary' :
        $canapprove = has_capability('mod/glossary:approve', $modcontext, $userid, false);
        if($canapprove) {
            $params = array();

            if($users = ulpgc_get_activity_users($cm, $modcontext, $userid, 'mod/glossary:write')) {
                list($insql, $params) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'u');
            } else {
                break;
            }
            $select = " glossaryid = :glossary AND approved = 0 AND teacherentry = 0 AND userid $insql
                        AND timemodified > :start  AND userid <> :user ";
            $params['glossary'] = $cm->instance;
            $params['start'] = $timestart;
            $params['user'] = $userid;
            $news = $DB->count_records_select('glossary_entries', $select, $params);
        }
        break;

    case 'quiz' :
        if($cangrade = has_capability('mod/quiz:grade', $modcontext, $userid, false)) {
            if($users = ulpgc_get_activity_users($cm, $modcontext, $userid, 'mod/quiz:attempt')) {
                list($insql, $params) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'u');
            } else {
                break;
            }
            $params['quiz'] = $cm->instance;
            $sql = "SELECT COUNT(qa.userid)
                    FROM {quiz_attempts} qa
                    WHERE qa.preview = 0 AND qa.state = 'finished' AND qa.sumgrades IS NULL
                        AND qa.quiz = :quiz
                        AND userid $insql ";
            $news = $DB->count_records_sql($sql, $params);
            if($news) {
                $linktext = get_string('ungradedactivity', 'local_ulpgccore', $news);
                $url = new moodle_url('/mod/'.$module.'/report.php', array('id'=>$cm->id, 'mode'=>'grading'));
            }
        }
        break;

    case 'wiki' :
        break;

    case 'tracker' :
        break;
        include_once($CFG->dirroot . '/mod/tracker/locallib.php');
        $now = time();
        $params = array('tracker'=>$cm->instance, 'status'=>RESOLVED, 'answered'=>TESTING,
                            'user1'=>$userid, 'user2'=>$userid, 'user3'=>$userid);

        $viewallclause = '';
        $cananswer = has_capability('mod/tracker:develop', $modcontext, $userid, false);

        if($cananswer) {
            $canviewall = has_capability('mod/tracker:viewallissues', $modcontext, $userid, false);
            if($canviewall) {
                $viewallclause = " OR ( i.assignedto = 0  ) ";
            }
            $select = "( ((i.reportedby = :user1)  OR (i.assignedto = :user3 )  $viewallclause )
                        AND ((i.usermodified >= i.resolvermodified) AND (i.userlastseen <= i.usermodified)) )";

        } else {
            $select = "(((i.reportedby = :user1)  OR (i.assignedto = :user3))
                         AND ((i.resolvermodified > i.userlastseen) OR (i.status = :answered)) )";
        }

        $sql = "SELECT COUNT(i.id)
                FROM {tracker_issue} i
                WHERE
                    $select
                    AND i.status < :status AND i.trackerid = :tracker  ";

        $news = $DB->count_records_sql($sql, $params);
        break;
    }

    $USER->ulpgcrecentmodactivity[$cm->id] = false;

    if($news) {
        if(!$url) {
            $url = new moodle_url('/mod/'.$module.'/view.php', array('id'=>$cm->id)+$actions);
        }
        if(!$linktext) {
            $linktext = get_string('newactivity', 'local_ulpgccore', $news);
        }
        $link = html_writer::link($url, $linktext);
        $news = '&nbsp;' . html_writer::tag('span', $link, array('class' => 'unread'));
        $USER->ulpgcrecentmodactivity[$cm->id] = $news;
        $USER->ulpgclastmodactivity[$cm->id] = $now;
    }
    return $news;
}




/**
 * 
 *
 * @uses $CFG, $USER, $QTYPES
 * @param object $user user object
 * @param string $message additional string to include in LDAP update comments
 */

function ulpgc_get_activity_users($cm, $modcontext, $userid, $capability) {

    $canviewall = has_capability('moodle/site:accessallgroups', $modcontext, $userid, false);
    $groups = array();
    if(!$canviewall) {
        if($groups = groups_get_activity_allowed_groups($cm,$userid)) {
            $groups = array_keys($groups);
        }
    } else {
        $groups = array(0);
    }
    $users = array();
    foreach($groups as $group) {
        $users = $users + get_enrolled_users($modcontext, $capability, $group, 'u.id, u.idnumber');
    }
    if($users) {
        $users = array_unique(array_keys($users));
    }

    return $users;
}



/**
 * Allows to update ULPGC's LDAP with data forwarded from moodle into LDAP
 *
 * @uses $CFG, $USER, $QTYPES
 * @param object $user user object
 * @param string $message additional string to include in LDAP update comments
 */

function local_ulpgccore_update_ldap($user, $message = '') {
    global $CFG, $DB, $USER, $QTYPES;

    $config = get_config('local_ulpgccore');
    if(!$message) {
        $message = 'Alta desde Campus virtual';
    }
    
    if(isset($config->enableupdateldap) && $config->enableupdateldap) {
        try {
            $cliente = new SoapClient ( null, array (
                    'location' => 'https://webservices.ulpgc.es/',
                    'uri' => 'urn:LDAPwsdl'
            ) );
            $parametros = array (
                    "usuario" => $user->username,
                    "rama" => "Todos",
                    "inicio" => 0
            );
            $resultado = $cliente->__soapCall ( 'BusquedaUsuarios', $parametros );
        } catch ( SoapFault $fault ) {
            trigger_error ( "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR );
        }
        if ($resultado -> TotalResult == '0') {
            $usuario['dni'] = $user->username;
            $usuario['credencial'] = $user->username;
            $usuario['nombre'] =$user->firstname;
            $usuario['apellidos'] = $user->lastname;
            $usuario['mail'] = $user->email;
            $usuario['rama'] = "Externos";
            $usuario['comentario'] = "$message, plataforma $CFG->plataforma";
            try {
                $parametros = array (
                        "usuario" => $usuario,
                        'binddn' => 'cn=PermisosAdmin,dc=ulpgc,dc=es',
                        'bindpw' => 'ucppmb,aeylf'
                );
                $crea_usuario = $cliente->__soapCall ( "AltaUsuario", $parametros );
            } catch ( SoapFault $fault ) {
                trigger_error ( "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR );
            }
        }
    }
}


/**
 * Makes shorter titles & descriptions of courses & categories
 *
 * Uses langs strings with several lines, each containing search & replace items separated by commas
 *  e.g.  Master,M.
 *
 *
 * @param string $text text to be shorten
 * @param array lang strings containing de 
 * @return string text shorten
 */

function local_ulpgccore_shorten_titles($text, $replaces = array('shortenitems')) {

    $lines = array();
    foreach($replaces as $tokens) {
        $tokens = get_string($tokens, 'local_ulpgccore');
        $tokens = preg_split("/((\r?\n)|(\r\n?))/", $tokens);
        $lines = array_merge($lines, $tokens);
    }
    $search = array();    
    $replace = array();    
    foreach($lines as $line) {
        $searchreplace = explode(',', $line);
        if(count($searchreplace) == 2) {
            $search[] = $searchreplace[0];    
            $replace[] = $searchreplace[1];    
        }
    } 
    
    $text = str_replace($search, $replace, $text);

    return shorten_text($text, 50);
}
