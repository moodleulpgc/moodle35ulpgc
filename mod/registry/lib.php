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
 * Library of interface functions and constants for module registry
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle are placed here.
 * All the registry specific functions, needed to implement all the module
 * logic, are on locallib.php.
 *
 * @package    mod
 * @subpackage registry
  * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

include_once($CFG->dirroot.'/mod/registry/locallib.php');

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function registry_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return true;
        case FEATURE_RATE:                    return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        default:                        return null;
    }
}

/**
 * Saves a new instance of the registry into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $registry An object from the form in mod_form.php
 * @param mod_registry_mod_form $mform
 * @return int The id of the newly inserted registry record
 */
function registry_add_instance(stdClass $registry, mod_registry_mod_form $mform = null) {
    global $DB;

    $registry->timecreated = time();
    $registry->timemodified = $registry->timecreated;

    $registry->id = $DB->insert_record('registry', $registry);

    # You may have to add extra stuff in here #

    /// TODO update grade items
    /// TODO update calendar events

    return $registry->id;
}

/**
 * Updates an instance of the registry in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $registry An object from the form in mod_form.php
 * @param mod_registry_mod_form $mform
 * @return boolean Success/Fail
 */
function registry_update_instance(stdClass $registry, mod_registry_mod_form $mform = null) {
    global $DB;

    $registry->timemodified = time();
    $registry->id = $registry->instance;

    $oldregistry = $DB->get_record('registry', array('id'=>$registry->id));

    $DB->update_record('registry', $registry);

    # You may have to add extra stuff in here #

    /// TODO update grade items
    /// TODO update calendar events

    return true;
}

/**
 * Removes an instance of the registry from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function registry_delete_instance($id) {
    global $DB;

    if (! $registry = $DB->get_record('registry', array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records('registry_submissions', array('registryid' => $registry->id));

    # Finally delete the
    $DB->delete_records('registry', array('id' => $registry->id));

    /// TODO delete grade items
    /// TODO delete calendar events

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function registry_user_outline($course, $user, $mod, $registry) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $registry the module instance record
 * @return void, is supposed to echp directly
 */
function registry_user_complete($course, $user, $mod, $registry) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in registry activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function registry_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link registry_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function registry_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see registry_get_recent_mod_activity()}

 * @return void
 */
function registry_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function registry_cron () {
    global $DB;

    if($registries = $DB->get_records('registry', array('syncroles'=>1))) {
        foreach($registries as $registry) {
            $category = registry_get_coursecategory($registry);
            $catcontext = context_coursecat::instance($category);
            $coursecontext = context_course::instance($registry->course);
            $config = get_config('registry');
            $roles = explode(',', $config->rolesreviewers);
            if($users = get_role_users($roles, $coursecontext, false, 'u.id ', null, false)) {
                if($assigned = get_role_users($config->reviewerrole, $catcontext, false, 'u.id ', null, true)) {
                    foreach($assigned as $user) {
                        unset($users[$user->id]);
                    }
                }
                $now = time();
                foreach($users as $user) {
                    role_assign($config->reviewerrole, $user->id, $catcontext, '', 0, $now);
                }
            }
        }
    }

    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function registry_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of registry?
 *
 * This function returns if a scale is being used by one registry
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $registryid ID of an instance of this module
 * @return bool true if the scale is used by the given registry instance
 */
function registry_scale_used($registryid, $scaleid) {
    global $DB;
 
    if ($scaleid and $DB->record_exists('registry', array('id' => $registryid, 'scale' => -$scaleid))) {
        return true;
    } else {
        return false;
    }

}
 
/**
 * Checks if scale is being used by any instance of registry.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any registry instance
 */
function registry_scale_used_anywhere($scaleid) {
    global $DB;
 
    return false;
 
    if ($scaleid and $DB->record_exists('registry', array('scale' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Create/update grade item for given registry instance
 *
 * @category grade
 * @uses GRADE_TYPE_NONE
 * @uses GRADE_TYPE_VALUE
 * @uses GRADE_TYPE_SCALE
 * @param stdClass $registry Forum object with extra cmidnumber
 * @param mixed $grades Optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok
 */
function registry_grade_item_update($registry, $grades=NULL) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    // grade item identification
    $params = array('courseid'=>$registry->course, 'itemtype'=>'mod', 'itemmodule'=>'registry', 'iteminstance'=>$registry->id, 'itemnumber'=>0);

    $assessed = false;
    if ($grade_items = grade_item::fetch_all($params)) {
        $grade_item = reset($grade_items);
        unset($grade_items); //release memory
        if($grade_item->gradetype > GRADE_TYPE_NONE) {
            $assessed = true;
        }
    }
    
    $params = array('itemname'=>$registry->name, 'idnumber'=>$registry->cmidnumber);

    if (!$assessed or $registry->scale == 0) {
        $params['gradetype'] = GRADE_TYPE_NONE;
    } else if ($registry->scale > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $registry->scale;
        $params['grademin']  = 0;

    } else if ($registry->scale < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$registry->scale;
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = NULL;
    }

    return grade_update('mod/registry', $registry->course, 'mod', 'registry', $registry->id, 0, $grades, $params);
}


/**
 * Delete grade item for given registry
 *
 * @category grade
 * @param stdClass $registry Forum object
 * @return grade_item
 */
function registry_grade_item_delete($registry) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/registry', $registry->course, 'mod', 'registry', $registry->id, 0, NULL, array('deleted'=>1));
}


/**
 * Update activity grades
 *
 * @category grade
 * @param object $registry
 * @param int $userid specific user only, 0 means all
 * @param boolean $nullifnone return null if grade does not exist
 * @return void
 */
function registry_update_grades($registry, $userid=0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');
   
    if ($grades = registry_get_user_grades($registry, $userid)) {
        registry_grade_item_update($registry, $grades);
    } else if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = NULL;
        registry_grade_item_update($registry, $grade);
    } else {
        registry_grade_item_update($registry);
    }
}

/**
 * Return grade for given user or all users.
 *
 * @param stdClass $registry record of registry with an additional cmidnumber
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function registry_get_user_grades($registry, $userid=0) {
    global $DB;

    $grades = array();

    if ($userid) {
        $where = ' s.userid = :userid ';
    } else {
        $where = ' s.userid != :userid ';
    }
    $params = array('rid'=>$registry->id, 'userid'=>$userid);

    $sql = "SELECT  s.userid AS id, s.userid,
                    s.timemodified as datesubmitted,
                    s.grade as rawgrade,
                    s.timegraded as dategraded,
                    s.grader as usermodified
            FROM {registry_submissions} s
            WHERE  s.registryid = :rid AND $where 
            GROUP BY s.userid, s.grade, s.timemodified";

    $grades = $DB->get_records_sql($sql, $params);
    return $grades;
}






////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function registry_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for registry file areas
 *
 * @package mod_registry
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function registry_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the registry file areas
 *
 * @package mod_registry
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the registry's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function registry_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $CFG, $DB, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    $canreview = has_capability('mod/registry:review', $context);

    if($filearea == 'othermodcontent') {
        $contextid = (int)array_shift($args);
        $component = clean_param(array_shift($args), PARAM_COMPONENT);
        $filearea  = clean_param(array_shift($args), PARAM_AREA);
        $itemid  = clean_param(array_shift($args), PARAM_INT);
        $filename = array_pop($args);
        if($args) {
            $filepath = implode('/', $args);
        } else {
            $filepath = '/';
        }

        $fs = get_file_storage();
        if (!($file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename)) || $file->is_directory()) {
            return false;
        }

        $userid = $file->get_userid();
        if($userid == $USER->id || $canreview) {
            // Download MUST be forced - security!
            send_stored_file($file, 0, 0, true);
        } else {
            return false;
        }
    }

    send_file_not_found();
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding registry nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the registry module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function registry_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the registry settings
 *
 * This function is called when the context for the page is a registry module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $registrynode {@link navigation_node}
 */
function registry_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $registrynode=null) {
}
