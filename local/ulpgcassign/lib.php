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
 * Funciones necesarias para la personalización del interfaz de assign
 *
 * @package local_ulpgcassign
 * @copyright  2016 Enrique Castro @ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined ( 'MOODLE_INTERNAL' ) || die ();


// Search filters for grading page.
define('ASSIGN_FILTER_DRAFT', 'draft');
define('ASSIGN_FILTER_GRADED', 'graded');
define('ASSIGN_FILTER_NOTGRADED', 'notgraded');
//define('ASSIGN_FILTER_DUEEXTENDED', 'dueextended');
define('ASSIGN_FILTER_BLOCKED', 'blocked');
define('ASSIGN_FILTER_SUBMITTED_LATE', 'submitted_late');



function local_ulpgcassign_extend_settings_navigation(settings_navigation $nav, context $context) {
    global $CFG, $PAGE;
}

/**
 * Sets dynamic information about a course module
 *
 * This function is called from cm_info when displaying the module
 * mod_folder can be displayed inline on course page and therefore have no course link
 *
 * @param cm_info $cm
 */
function local_ulpgcassign_cm_info_dynamic(cm_info $cm) { // ecastro ULPGC
    global $CFG, $OUTPUT;

    $icon = '';

    $plugins = core_component::get_plugin_list('assignsubmission');
    foreach ($plugins as $name => $plugin) {
        $disabled = get_config('assignsubmission_' . $name, 'disabled');
        if (!$disabled) {
            $function = 'assignsubmission_' . $name . '_dynamic_icon';
            $pluginlibfile = $CFG->dirroot . '/mod/assign/submission/' . $name . '/lib.php';
            if(file_exists($pluginlibfile)) {
                include_once($CFG->dirroot . '/mod/assign/submission/' . $name . '/lib.php');
                if(function_exists($function)) {
                    if($icon = $function($cm)) {
                        break;
                    }
                }
            }
        }
    }

    if(!$icon) {
        $plugins = core_component::get_plugin_list('assignfeedback');
        foreach ($plugins as $name => $plugin) {
            $disabled = get_config('assignfeedback_' . $name, 'disabled');
            if (!$disabled) {
                $function = 'assignfeedback_' . $name . '_dynamic_icon';
                $pluginlibfile = $CFG->dirroot . '/mod/assign/feedback/' . $name . '/lib.php';
                if(file_exists($pluginlibfile)) {
                    include_once($CFG->dirroot . '/mod/assign/feedback/' . $name . '/lib.php');
                    if(function_exists($function)) {
                        if($icon = $function($cm)) {
                            break;
                        }
                    }
                }
            }
        }
    }

    if($icon) {
        $cm->set_icon_url($icon);
    }
}

/**
 * Returns an array as submissions filter options 
 *
 * @return array filter, name
 */
function local_ulpgcassign_filter_menu() {
    $options = array('' => get_string('filternone', 'assign'),
                    ASSIGN_FILTER_REQUIRE_GRADING => get_string('filterrequiregrading', 'assign'),
                    ASSIGN_FILTER_GRADED=> get_string('filtergraded', 'local_ulpgcassign'),
                    ASSIGN_FILTER_NOTGRADED=> get_string('filternotgraded', 'local_ulpgcassign'),
                    ASSIGN_FILTER_DRAFT => get_string('filterdraft', 'local_ulpgcassign'),
                    ASSIGN_FILTER_SUBMITTED => get_string('filtersubmitted', 'assign'),
                    ASSIGN_FILTER_NOT_SUBMITTED => get_string('filternotsubmitted', 'assign'),
                    ASSIGN_FILTER_SUBMITTED_LATE => get_string('filtersubmittedlate', 'local_ulpgcassign'),
                    //ASSIGN_FILTER_DUEEXTENDED => get_string('filterdueextended', 'local_ulpgcassign'),
                    ASSIGN_FILTER_BLOCKED => get_string('filterblocked', 'local_ulpgcassign')
                    );
    return $options;
}


/**
 * Returns an array as submissions filter options 
 *
 * @param $filter the used fiter constant
 * @param $renderer class the mod_assign renderer
 * @return array filter, name
 */
function local_ulpgcassign_filtered_warning($filter, $renderer) {
    $filterstrings = local_ulpgcassign_filter_menu();
    unset($filterstrings['']);
    return $renderer->container(get_string('filtered', 'local_ulpgcassign').' '.
            html_writer::nonempty_tag('span', $filterstrings[$filter], array('class'=>'gradingtablefiltermsg')), 'initialbar');
}


/**
 * Adds SQL for sorting grading table usin group name
 *
 * @param stdClass $instance the assign record from database
 * @param bool $teamsubmissions indicates if sorting is by group
 * @param string $fields field names to retrieve from DB
 * @param string $from JOIN snippet for querying groups 
 * @return none, effect on refereneced arguments
 */
function local_ulpgcassign_gradingtable_group_sql($instance, & $teamsubmissions, & $fields, & $from) {
        $teamsubmissions = false; // ecastro ULPGC to allow ordering by group
        if ($instance->teamsubmission && $instance->teamsubmissiongroupingid) {  // ecastro ULPGC
            $groupingid = $instance->teamsubmissiongroupingid;
            $grouplist = ' ( 0 ) ';
            if ($groups = array_keys(groups_get_all_groups($instance->course, 0, $groupingid, 'g.id'))) {
                $grouplist = '(0, '. implode(',', $groups). ' ) ' ;
            }
            $fields .= ', s.groupid, gg.name as team ';
            $from .= " LEFT JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid IN $grouplist " ;
            $from .= ' LEFT JOIN {groups} gg ON gm.groupid = gg.id ' ;
            $teamsubmissions = true;
        }
}

/**
 * Adds SQL for advanced filtering of submissions
 *
 * @param stdClass $instance the assign record from database
 * @param int $filter current filtering option
 * @param string $where SQL where statement for filtering
 * @param array $params parameters to be used in DB query
 * @return array ($where, $params)
 */
function local_ulpgcassign_gradingtable_filter_sql($instance, $filter, $where, $params) {
    if ($filter == ASSIGN_FILTER_DRAFT) {
        $where .= ' AND (s.timemodified IS NOT NULL AND
                            s.status = :submitted) ';
        $params['submitted'] = ASSIGN_SUBMISSION_STATUS_DRAFT;
    }
    if ($filter == ASSIGN_FILTER_SUBMITTED_LATE) {
        $where .= ' AND s.timemodified > :duedate ';
        $params['duedate'] = $instance->duedate;
    }
    if ($filter == ASSIGN_FILTER_GRADED) {
        $where .= ' AND g.grade > -1 ';
    }
    if ($filter == ASSIGN_FILTER_NOTGRADED) {
        $where .= ' AND ( g.grade = -1  OR g.grade IS NULL) ';
    }
    if ($filter == ASSIGN_FILTER_BLOCKED) {
        $where .= ' AND uf.locked = 1 ';
    }
    /*
    if ($filter == ASSIGN_FILTER_DUEEXTENDED) {
        $where .= ' AND uf.extensionduedate > 0 ';
    }
    */
    
    return array($where, $params);
}

/**
 * Sets $SESSION for fullname naming formatting
 *
 * @param array $sortcolums those colums used in the table 
 * @return string nemeformat param
 */
function local_ulpgcassign_nameformat($sortcolumns) {
    $sorting = 'firstname';
    if($sortcolumns) {
        $first = 9999;
        $last= 99999;
        $sorting = implode(' ',  $sortcolumns);
        if(false !== $p = strpos($sorting, 'lastname')) {
            $last = $p;
        }
        if(false !== $p = strpos($sorting, 'firstname')) {
            $first = $p;
        }
        if($last < $first) {
            $sorting = 'lastname';
        } else {
            $sorting = 'firstname';
        }
    }
    return $sorting;
}


/**
 * Checks if team sumbissions has been modified after this user's submission 
 * If so, generates a table row object to insert when rendering student summary
 *
 * @param stdClass $instance the assign record from database
 * @param stdClass $submission object
 * @param stdClass $teamsubmission object
 * @return mixed  html_table_row/false
 */
function local_ulpgcassign_teamsubmission_after_warning($instance, $submission, $teamsubmission) {
    $row = false;
    
    if($teamsubmission && $submission) {
        if($instance->requireallteammemberssubmit && $teamsubmission->groupid &&
                                ($submission->status != ASSIGN_SUBMISSION_STATUS_NEW) && 
                                ($teamsubmission->timemodified > $submission->timemodified)) {
            $row = new html_table_row();
            $cell1 = new html_table_cell(get_string('membertimemodified', 'local_ulpgcassign'));
            $message = html_writer::div(get_string('modifiedaftermsg', 'local_ulpgcassign'), 'submissionmodifiedlater');
            $cell2 = new html_table_cell(userdate($submission->timemodified).$message);
            //$cell2->attributes['class'] = 'submissionmodifiedlater text-warning';
            $row->cells = array($cell1, $cell2);                                    }
    } 
            
    return $row;
}

/**
 * Checks if team sumbissions has been modified after this user's submission 
 * If so, generates a table row object to insert when rendering student summary
 *
 * @param stdClass $instance the assign record from database
 * @return int count of extensions
 */
function local_ulpgcassign_get_due_extensions($instance) { // not used any more
    global $DB;
    
    $count = 0;
    
    
    return $count;
}
