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
 * This file contains the moodle hooks for the examboard module.
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('EXAMBOARD_GRADING_AVG', 1);
define('EXAMBOARD_GRADING_MAX', 2);
define('EXAMBOARD_GRADING_MIN', 3);

define('EXAMBOARD_USERTYPE_NONE',   0);
define('EXAMBOARD_USERTYPE_USER',  -1); // examinees
define('EXAMBOARD_USERTYPE_MEMBER',-2);
define('EXAMBOARD_USERTYPE_TUTOR', -3);
define('EXAMBOARD_USERTYPE_STAFF', -4);  // tutor + board members
define('EXAMBOARD_USERTYPE_ALL',   -5);

define('EXAMBOARD_TUTORS_NO',  0);
define('EXAMBOARD_TUTORS_YES', 1);
define('EXAMBOARD_TUTORS_REQ', 2);

define('EXAMBOARD_PUBLISH_NO',  0);
define('EXAMBOARD_PUBLISH_YES', 1);
define('EXAMBOARD_PUBLISH_DATE',2);

define('EXAMBOARD_ORDER_KEEP',  0);
define('EXAMBOARD_ORDER_RANDOM',1);
define('EXAMBOARD_ORDER_ALPHA', 2);


/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function examboard_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_ADVANCED_GRADING:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the examboard into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $examboard An object from the form.
 * @param examboard_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function examboard_add_instance($examboard, $mform = null) {
    global $DB;

    $examboard->timemodified = time();
    
    $examboard->id = $DB->insert_record('examboard', $examboard);
    
       // Update related grade item.
    examboard_grade_item_update($examboard);

    return $examboard->id;
}

/**
 * Updates an instance of the examboard in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $examboard An object from the form in mod_form.php.
 * @param examboard_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function examboard_update_instance($examboard, $mform = null) {
    global $DB;

    $examboard->timemodified = time();
    $examboard->id = $examboard->instance;

    // Get the current value, so we can see what changed.
    $oldeb = $DB->get_record('examboard', array('id' => $examboard->instance));
    
    // Update the database.
    $examboard->id = $examboard->instance;
    $DB->update_record('examboard', $examboard);
    
    // Do the processing required after an add or an update.
    //examboard_grade_item_update($examboard);
    
    if ($oldeb->grademode != $examboard->grademode) {
        //examboard_update_all_final_grades($examboard);
        //examboard_update_grades($examboard);
    }
    
    return true;
}

/**
 * Removes an instance of the examboard from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function examboard_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('examboard', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('examboard', array('id' => $id));

    return true;
}

/**
 * Is a given scale used by the instance of examboard?
 *
 * This function returns if a scale is being used by one examboard
 * if it has support for grading and scales.
 *
 * @param int $examboardid ID of an instance of this module.
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by the given examboard instance.
 */
function examboard_scale_used($examboardid, $scaleid) {
    global $DB;

    if ($scaleid && $DB->record_exists('examboard', array('id' => $examboardid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of examboard.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by any examboard instance.
 */
function examboard_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('examboard', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}


/**
 * Get the primary grade item for this assign instance.
 *
 * @param int $examboardid the module instance ID
 * @param int $courseid the course ID
 * @return grade_item The grade_item record
*/
function examboard_get_grade_item($examboardid, $courseid) {
    $params = array('itemtype' => 'mod',
                    'itemmodule' => 'examboard',
                    'iteminstance' => $examboardid,
                    'courseid' => $courseid,
                    'itemnumber' => 0);
    $gradeitem = grade_item::fetch($params);
    if (!$gradeitem) {
        throw new coding_exception('Improper use of the examboard module. ' .
                                    'Cannot load the grade item.');
    }
    return $gradeitem;
}

/**
 * Get the grade scale used in thi smodule
 *
 * @param int $grade the garde values stored in instance 
 * @return array indexed by scale items
*/
function examboard_get_scale($grade) {
    global $DB;
    if($scale = $DB->get_record('scale', array('id'=>-($grade)))) {
        return make_menu_from_list($scale->scale);
    }
    return false;
}

/**
 * Lists all gradable areas for the advanced grading methods gramework
 *
 * @return array('string'=>'string') An array with area names as keys and descriptions as values
 */
function examboard_grading_areas_list() {
    return array('grades'=>get_string('grades', 'examboard'));
}

 function examboard_get_gradeables() {
    global $PAGE;
    
    $options = array(0=>get_string('choose'));
    
    $instances = get_all_instances_in_course('assign', $PAGE->course);
    foreach($instances as $instance) {
        $options[$instance->coursemodule] = format_string($instance->name);
    }
    
    return $options;
 }

/**
 * Creates or updates grade item for the given examboard instance.
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $examboard Instance object with extra cmidnumber and modname property.
 * @param bool $reset Reset grades in the gradebook.
 * @return void.
 */
function examboard_grade_item_update($examboard, $reset=false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($examboard->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($examboard->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $examboard->grade;
        $item['grademin']  = 0;
    } else if ($examboard->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$examboard->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }
    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('mod/examboard', $examboard->course, 'mod', 'examboard', $examboard->id, 0, null, $item);
}

/**
 * Delete grade item for given examboard instance.
 *
 * @param stdClass $examboard Instance object.
 * @return grade_item.
 */
function examboard_grade_item_delete($examboard) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('/mod/examboard', $examboard->course, 'mod', 'examboard',
                        $examboard->id, 0, null, array('deleted' => 1));
}

/**
 * Update examboard grades in the gradebook.
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $examboard Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of specific user only, 0 means all participants.
 */
function examboard_update_grades($examboard, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if ($examboard->grade == 0) {
        examboard_grade_item_update($examboard);

    } else if ($grades = examboard_get_user_grades($examboard, $userid)) {
        examboard_grade_item_update($examboard, $grades);

    } else if ($userid && $nullifnone) {
        $grade = new stdClass();
        $grade->userid = $userid;
        $grade->rawgrade = null;
        examboard_grade_item_update($examboard, $grade);

    } else {
        examboard_grade_item_update($examboard);
    }
}

/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}.
 *
 * @package     mod_examboard
 * @category    files
 *
 * @param stdClass $course.
 * @param stdClass $cm.
 * @param stdClass $context.
 * @return string[].
 */
function examboard_get_file_areas($course, $cm, $context) {

    $areas = array();

    return $areas;
}

/**
 * File browsing support for examboard file areas.
 *
 * @package     mod_examboard
 * @category    files
 *
 * @param file_browser $browser.
 * @param array $areas.
 * @param stdClass $course.
 * @param stdClass $cm.
 * @param stdClass $context.
 * @param string $filearea.
 * @param int $itemid.
 * @param string $filepath.
 * @param string $filename.
 * @return file_info Instance or null if not found.
 */
function examboard_get_file_info($browser,
                                     $areas,
                                     $course,
                                     $cm,
                                     $context,
                                     $filearea,
                                     $itemid,
                                     $filepath,
                                     $filename) {
    global $CFG;
    
    if ($context->contextlevel != CONTEXT_MODULE) {
        return null;
    }

    $urlbase = $CFG->wwwroot.'/pluginfile.php';
    $fs = get_file_storage();
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;
    
    return null;
}

/**
 * Serves the files from the examboard file areas.
 *
 * @package     mod_examboard
 * @category    files
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The examboard's context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 */
function examboard_pluginfile($course,
                                    $cm,
                                    context $context,
                                    $filearea,
                                    $args,
                                    $forcedownload,
                                    array $options=array()) {
    global $DB, $CFG, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);
    
    if (!has_capability('mod/examboard:view', $context)) {
        return false;
    }
    
    $itemid = (int)array_shift($args);
    $canmanage = has_capability('mod/examboard:manage', $context);
    
    
    if($filearea == 'notification') {
        if(!$notification = $DB->get_record('examboard_notification', array('id' => $itemid), 'id, userid')) {
            return false;
        }
        if(!has_capability('mod/examboard:grade', $context) || (($notification->userid != $USER->id) && !$canmanage)) { 
            return false;
        }
    } elseif($filearea == 'submission') {
        return false;
    
    }
    
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_examboard/$filearea/$itemid/$relativepath";

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
        send_file_not_found();
    }
    
    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * Extends the global navigation tree by adding examboard nodes if there is a relevant content.
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $examboardnode An object representing the navigation tree node.
 * @param stdClass $course.
 * @param stdClass $module.
 * @param cm_info $cm.
 */
function examboard_extend_navigation($examboardnode, $course, $module, $cm) {
}

/**
 * Extends the settings navigation with the examboard settings.
 *
 * This function is called when the context for the page is a examboard module.
 * This is not called by AJAX so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $examboardnode {@link navigation_node}
 */
function examboard_extend_settings_navigation(settings_navigation $settings, navigation_node $navref) {
    global $CFG, $PAGE;
    
    if (!$PAGE->cm) {
        return;
    }

    if (!$PAGE->course) {
        return;
    }
    
    $link = new moodle_url('/mod/examboard/edit.php', array('id'=>$PAGE->cm->id));
    
    if (has_capability('mod/examboard:allocate', $PAGE->cm->context)) {
        $allocnode = $navref->add(get_string('manageallocation', 'examboard'), '', navigation_node::TYPE_CONTAINER, null, 'examboardallocations');

        $link->param('action', 'userassign');
        $node = $allocnode->add(get_string('userassign', 'examboard'), clone $link, navigation_node::TYPE_SETTING);

        $link->param('action', 'allocateboard');
        $node = $allocnode->add(get_string('boardallocation', 'examboard'), clone $link, navigation_node::TYPE_SETTING);
        
        $link->param('action', 'allocateusers');
        $node = $allocnode->add(get_string('userallocation', 'examboard'), clone $link, navigation_node::TYPE_SETTING);
    }

    if (has_capability('mod/examboard:manage', $PAGE->cm->context)) {
        $link->param('action', 'import');
        $node = $navref->add(get_string('import', 'examboard'), clone $link, navigation_node::TYPE_SETTING, null, 'examboardimport', new pix_icon('i/import', ''));

        $link->param('action', 'export');
        $node = $navref->add(get_string('export', 'examboard'), clone $link, navigation_node::TYPE_SETTING, null, 'examboardexport', new pix_icon('i/export', ''));
    }
    
    
    if (has_capability('mod/examboard:notify', $PAGE->cm->context)) {
        $link->param('action', 'notify');
        $node = $navref->add(get_string('notify', 'examboard'), clone $link, navigation_node::TYPE_SETTING, null, 'examboardnotify', new pix_icon('t/email', ''));
    }
   
}


/**
 * Returns all other capabilities used by this module.
 * @return array Array of capability strings
 */
function examboard_get_extra_capabilities() {
    return array('moodle/grade:viewall',
                 'moodle/site:viewfullnames',
                 'moodle/site:accessallgroups');
}


/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function examboard_get_view_actions() {
    return array('view');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function examboard_get_post_actions() {
    return array('upload', 'submit', 'grade', 'confirm');
}


/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $examboard     examboard object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function examboard_view($examboard, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $examboard->id
    );

    $event = \mod_examboard\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('examboard', $examboard);
    $event->trigger();

    // Completion.
    //$completion = new completion_info($course);
    //$completion->set_module_viewed($cm);
}


/**
 * Call cron on the examboard module.
 */
function examboard_cron() {
    global $CFG;
    
    return true;
}
