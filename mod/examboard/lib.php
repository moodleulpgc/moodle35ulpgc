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
    
    // update related grouping name
    if($examboard->groupingname) {
        if(!$grouping = groups_get_grouping_by_idnumber($examboard->course, $examboard->groupingname)) {
            $grouping = new stdClass();
            $grouping->courseid = $examboard->course;
            $grouping->name = $examboard->groupingname;
            $grouping->idnumber = $examboard->groupingname;
            
            $grouping->id = groups_create_grouping($grouping); 
        }
    }

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
    global $CFG, $DB;

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
    
    // update associated grouping
    if($examboard->examgroups) {
        if($examboard->groupingname && ($examboard->groupingname != $oldeb->groupingname)) {
            $grouping = false;
            if($oldeb->groupingname) {
                if($grouping = groups_get_grouping_by_idnumber($examboard->course, $oldeb->groupingname)) {
                    $grouping->name = $examboard->groupingname;
                    $grouping->idnumber = $examboard->groupingname;
                    groups_update_grouping($grouping);
                }
            }
            if(!$grouping) {
                $grouping = new stdClass();
                $grouping->courseid = $examboard->course;
                $grouping->name = $examboard->groupingname;
                $grouping->idnumber = $examboard->groupingname;
                
                $grouping->id = groups_create_grouping($grouping); 
            }
        }
        include_once($CFG->dirroot.'/mod/examboard/locallib.php');
        examboard_synchronize_groups($examboard);
        examboard_synchronize_gradeables($examboard);
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

    $examboard = $DB->get_record('examboard', array('id' => $id));
    if (!$examboard) {
        return false;
    }


    if($exams = $DB->get_records_menu('examboard_exam', array('examboardid'=>$id), 'id,boardid')) {
        $exams = array_keys($exams);
        
        foreach(array('examinee', 'tutor', 'grade', 'confirmation', 'notification') as $field) {
            $DB->delete_records_list('examboard_'.$field, 'examid', $exams);
        }
    }

    if($boards = $DB->get_records_menu('examboard_board', array('examboardid'=>$examboard->id), 'id,idnumber')) {
        $boards = array_keys($boards);
        $DB->delete_records_list('examboard_member', 'boardid', $boards);
        
    }

    $DB->delete_records('examboard_exam', array('examboardid' => $id));
    $DB->delete_records('examboard_board', array('examboardid' => $id));
    
    $DB->delete_records('examboard', array('id' => $id));
    
    // delete associated grouping & groups 
    if($examboard->groupingname) {
        if($grouping = groups_get_grouping_by_idnumber($examboard->course, $examboard->groupingname)) {
            if($groups = groups_get_all_groups($examboard->course, 0, $grouping->id)) {
                foreach($groups as $group) {
                    groups_delete_group($group);
                }
            }
            groups_delete_grouping($grouping); 
        }
    }
    
    examboard_grade_item_delete($examboard);
    
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
    return array('usergrades'=>get_string('usergrades', 'examboard'));
}


/**
 * Determine if this users grade can be edited.
 *
 * @param object $examboard record from DB with module instance information
 * @param int $userid - The student userid
 * @return bool $gradingdisabled
 */
function examboard_grading_disabled($examboard, $userid) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');
    
    $gradinginfo = grade_get_grades($examboard->course,
                                    'mod',
                                    'examboard',
                                    $examboard->id,
                                    array($userid));
    if (!$gradinginfo) {
        return false;
    }

    if (!isset($gradinginfo->items[0]->grades[$userid])) {
        return false;
    }
    $gradingdisabled = $gradinginfo->items[0]->grades[$userid]->locked ||
                        $gradinginfo->items[0]->grades[$userid]->overridden;
    return $gradingdisabled;
}

/**
 * Get an instance of a grading form if advanced grading is enabled.
 * This is specific to the assignment, marker and student.
 *
 * @param object $examboard record from DB with module instance information
 * @param int $userid - The student userid
 * @param stdClass|false $grade - The grade record
 * @param bool $gradingdisabled
 * @return mixed gradingform_instance|null $gradinginstance
*/
function examboard_get_grading_instance($examboard, $userid, $grade, $gradingdisabled) {
    global $CFG, $USER;
    //require_once($CFG->libdir . '/gradelib.php');
    require_once($CFG->dirroot . '/grade/grading/lib.php');
    
    
    $grademenu = make_grades_menu($examboard->grade);
    $allowgradedecimals = $examboard->grade > 0;

    $advancedgradingwarning = false;
    
    $context = context_module::instance($examboard->cmid);
    
    $gradingmanager = get_grading_manager($context, 'mod_examboard', 'usergrades');
    $gradinginstance = null;
    if ($gradingmethod = $gradingmanager->get_active_method()) {
        $controller = $gradingmanager->get_controller($gradingmethod);
        if ($controller->is_form_available()) {
            $itemid = null;
            if ($grade) {
                $itemid = $grade->id;
            }
            if ($gradingdisabled && $itemid) {
                $gradinginstance = $controller->get_current_instance($USER->id, $itemid);
            } else if (!$gradingdisabled) {
                $instanceid = optional_param('advancedgradinginstanceid', 0, PARAM_INT);
                $gradinginstance = $controller->get_or_create_instance($instanceid,
                                                                        $USER->id,
                                                                        $itemid);
            }
        } else {
            $advancedgradingwarning = $controller->form_unavailable_notification();
        }
    }
    if ($gradinginstance) {
        $gradinginstance->get_controller()->set_grade_range($grademenu, $allowgradedecimals);
    }
    return $gradinginstance;
}


function examboard_get_gradeable_cm($courseorid, $idnumber) {
    $mods = get_fast_modinfo($courseorid)->get_cms();
    $cm = false;
    foreach($mods as $cmid => $cm) {
        if($cm->idnumber ==  $idnumber) {
            return $cm;
        }
    }
}


function examboard_get_gradeables($courseorid) {
    
    $options = array();
    
    $gradeables = explode(',', get_config('examboard','gradeables'));
    
    //$gradeables = get_config('examboard','gradeables');
    
    if(!$gradeables) {
        return $options;
    }
    
    $mods = get_fast_modinfo($courseorid)->get_cms();

    foreach($mods as $cmid => $cm) {
        if (!in_array($cm->module, $gradeables) || !$cm->idnumber || !$cm->uservisible) {
            continue;
        }
        $options[$cm->idnumber] = format_string($cm->name);
    }
    
    if($options) {
        $options = array('' => get_string('none')) + $options;
    }
    
    return $options;
}


function examboard_get_board_members($boardid,  $deputy = null,  $names = false ) {
    global $DB;
    
    $params = array('boardid'=>$boardid);
    $search = '';
    if(isset($deputy)) {
        $params['deputy'] = (int)boolval($deputy);
        $search .= ' AND m.deputy = :deputy ';
    }
    
    if($names) {
        $names = get_all_user_name_fields(true, 'u');
        $sql = "SELECT m.userid AS uid, m.id AS mid, m.*, u.id, u.idnumber, u.picture, u.imagealt, u.email, u.mailformat, $names
                FROM {examboard_member} m 
                JOIN {user} u ON m.userid = u.id
                WHERE m.boardid = :boardid $search 
                ORDER BY m.deputy ASC, m.sortorder ASC ";
        return $DB->get_records_sql($sql, $params);
    }
    
    return $DB->get_records('examboard_member', $params, 'deputy ASC, sortorder ASC');
}

/**
 * Creates or updates grade item for the given examboard instance.
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $examboard Instance object with extra cmidnumber property.
 * @param array $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return void.
 */
function examboard_grade_item_update($examboard, $grades=false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($examboard->name, PARAM_NOTAGS);
    $item['idnumber'] = $examboard->cmidnumber;

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
    
    if ($grades  === 'reset') {
        $item['reset'] = true;
        $grades = null;
    }
    
    grade_update('mod/examboard', $examboard->course, 'mod', 'examboard', $examboard->id, 0, $grades, $item);
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
 * Return grade for given user set by a grader
 *
 * @param int $examid the examination ID
 * @param int $userid user id
 * @param bool $create flag if create grade if not existing
 * @param int $graderid user id, if 0, the current user
 * @return array array of grades, false if none
 */
function examboard_get_grader_grade($examid, $userid, $create = false, $graderid = 0) {
    global $DB, $USER;

    if(!$graderid) {
        $graderid = $USER->id;
    }
    
    $grade = $DB->get_record('examboard_grades', array('examid'=>$examid, 'userid'=>$userid, 'grader'=>$graderid));
    
    if($grade) {
        return $grade;
    }
    
    if($create) {
        $grade = new stdClass();
        $grade->examid       = $examid;
        $grade->userid       = $userid;
        $grade->grader       = $graderid;
        $grade->timecreated  = time();
        $grade->timemodified = $grade->timecreated;
        $grade->grade = -1;
        $gid = $DB->insert_record('examboard_grades', $grade);
        $grade->id = $gid;
        return $grade;
    }
    
    return $DB->get_record('examboard_grades', array('examid'=>$examid, 'userid'=>'userid', 'grader'=>$graderid));
}
    
/**
 * Return grade for given user or all users.
 *
 * @param stdClass $examboard record of examboard with an additional cmidnumber
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function examboard_get_user_grades($examboard, $userid=0) {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/mod/examboard/locallib.php');

    // When the gradebook asks us for grades - only return the last graded exam for each user.
    $grades = array();
    
    $params = array('examboardid' => $examboard->id);
    
    $userwhere = '';
    if($userid) {
        $userwhere = ' AND g.userid = :userid';
        $params['userid'] = $userid;
    }
    
    $sql = "SELECT e.*
            FROM {examboard_exams}  e 
            JOIN {examboard_grades} g ON g.examid = e.id
            WHERE e.examboardid = :examboardid  AND e.active = 1
            AND EXISTS (SELECT 1 FROM {examboard_grades} g WHERE g.examid = e.id $userwhere)
            ORDER BY e.examdate DESC ";
    
    $members = array();
    if($exam = $DB->get_records_sql($sql, $params, 0, 1)) {
        $exam = reset($exam);
        $members = array_keys(examboard_get_board_members($exam->boardid));
    } 
    
    if(!$exam || !$members) {
        return $grades;
    }
    
    list($insql, $params) = $DB->get_in_or_equal($members, SQL_PARAMS_NAMED, 'mem_');
    $params['examid'] = $exam->id;
    $userwhere = '';
    if($userid) {
        $userwhere = ' AND userid = :userid';
        $params['userid'] = $userid;
    }
    
    $select = " examid = :examid AND userid $insql $userwhere ";
    
    $graderesults = $DB->get_recordset_select('examboard_grades', $select, $params);
    foreach ($graderesults as $result) {
        $grades[$result->userid][$result->id] = $result;
    }
    $graderesults->close();
    
    foreach($grades as $userid => $rawgrades) {
        $grades[$userid] = examboard_calculate_grades($examboard->grademode, $examboard->mingraders, $rawgrades);
    }
    
    return $grades;
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

    if ($examboard->save_gradegrade == 0) {
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
    return array(
        'notification' => get_string('areanotification', 'examboard'),
    );
}

/**
 * File browsing supsave_gradeport for examboard file areas.
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
        
        $link->param('action', 'synchusers');
        $node = $allocnode->add(get_string('synchusers', 'examboard'), clone $link, navigation_node::TYPE_SETTING);
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
