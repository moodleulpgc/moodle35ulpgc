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
 * This file contains main class for the course format Topic
 *
 * @since     2.0
 * @package   format_topicgroup
 * @copyright 2013 E. Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. '/course/format/topics/lib.php');

/**
 * Main class for the Topics course format
 *
 * @package    format_topicgroup
  * @copyright 2013 E. Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_topicgroup extends format_topics {

    /**
     * Returns the display name of the given section that the course prefers.
     *
     * Use section name is specified by user. Otherwise use default ("Topic #")
     *
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string Display name that the course format prefers, e.g. "Topic 2"
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ((string)$section->name !== '') {
            return format_string($section->name, true,
                    array('context' => context_course::instance($this->courseid)));
        } else if ($section->section == 0) {
            return get_string('section0name', 'format_topicgroup');
        } else {
            return get_string('topic').' '.$section->section;
        }
    }


    /**
     * Definitions of the additional options that this course format uses for course
     *
     * Topics format uses the following options:
     * - coursedisplay
     * - numsections
     * - hiddensections
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        $courseformatoptions = parent:: course_format_options($foreditform);

        static $topicgroupformatoptions = false;
        if ($foreditform ) {
            $courseformatoptionsedit = array();
            // include here optional

            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * Adds format options elements to the course/section edit form.
     *
     * This function is called from {@link course_edit_form::definition_after_data()}.
     *
     * @param MoodleQuickForm $mform form the elements are added to.
     * @param bool $forsection 'true' if this is a section edit form, 'false' if this is course edit form.
     * @return array array of references to the added form elements.
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        $elements = parent::create_edit_form_elements($mform, $forsection);

        // include here custom elements
        if (!$forsection) {
        }
        return $elements;
    }

    /**
     * Updates format options for a course
     *
     * In case if course format was changed to 'topicgroup', we try to copy options
     * 'coursedisplay', 'numsections' and 'hiddensections' from the previous format.
     * If previous course format did not have 'numsections' option, we populate it with the
     * current number of sections
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data
     * @param stdClass $oldcourse if this function is called from {@link update_course()}
     *     this object contains information about the course before update
     * @return bool whether there were any changes to the options values
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB;

        $changed = parent::update_course_format_options($data, $oldcourse);

        /// check & assert users permissions
        $context = context_course::instance($this->courseid);
        $config = get_config('format_topicgroup');
        $editingroles = explode(',', $config->editingroles);
        if($restrictedroles = explode(',', $config->restrictedroles)) {
            foreach($restrictedroles as $role) {
                //role_change_permission($role, $context, 'moodle/site:accessallgroups', CAP_PREVENT);
//                print_object( "role_change_permission $role  moodle/site:accessallgroups " );
                role_change_permission($role, $context, 'moodle/course:setcurrentsection', CAP_PREVENT);
                //print_object( "role_change_permission $role  moodle/course:setcurrentsection " );
            }
        }

        /// enforce section settings, but as a general rule do not touch here non-grouping-restricted sections 
        /// this allow having grouping-restricted modules in non restrited sections
        $sql = "SELECT tg.*, cs.section AS section
                    FROM {format_topicgroup_sections} tg
                    JOIN {course_sections} cs ON cs.id = tg.sectionid AND cs.course = tg.course
                WHERE tg.course = :course AND tg.sectionid > 0 AND tg.groupingid > 0 ";
        if($tgsections = $DB->get_records_sql($sql, array('course'=>$this->courseid))) {
            foreach($tgsections as $tgsection) {
                format_topicgroup_section_restrictions($tgsection);
            }
        }
        return $changed;
    }

}


/**
    * Updates a section or section_info object to inlcude grouping from format_topicgroup_sections
    *
    * @param stdClass $section the object to be updated
    * @param bool $create if set, then a new entry will be inserted in table
    * @return bool whether there were any changes to the options values
    */
function format_topicgroup_getset_grouping(&$section, $create = false) {
    global $DB;
    $tgsection = '';
    if(!isset($section->groupingid) || $create) {
        $grouping = 0;
        if(!$tgsection = $DB->get_record('format_topicgroup_sections', array('course'=>$section->course, 'sectionid'=>$section->id))) {
            if($create) {
                $tgsection = new stdClass;
                $tgsection->course = $section->course;
                $tgsection->sectionid = $section->id;
                $tgsection->section = $section->section;
                $tgsection->groupingid = 0;
                $tgsection->timecreated = time();
                $tgsection->timemodified = $tgsection->timecreated;
                $DB->insert_record('format_topicgroup_sections', $tgsection);
            }
        } else {
            $tgsection->section = $section->section;
            $grouping = $tgsection->groupingid;
        }
        $section->groupingid = $grouping;
    }

    return $tgsection;
}

/**
    * Updates course_module & course sections tables enforcing section restrictions & availavility
    *
    * Section id is expected in $data->id (or $data['id'])
    * If $data does not contain property with the option name, the option will not be updated
    *
    * @param stdClass $section section object or ID from format_topicgroup_sections containing "section" field from course_sections
    *                             or the same, record from course_sections with sectionid field
    * @param bool $groupmode to be set. default, false means not touchig groupmode
    * @return bool whether there were any changes applied
    */
function format_topicgroup_section_restrictions($section, $groupmode=false) {
    global $DB;

    $changes = false;

    $success = format_topicgroup_mod_restrictions($section, $groupmode);
    $changes = ($changes || $success);

    // set new restricted availability on course section
    $changes = (format_topicgroup_section_availability($section->course, $section->section, $section->groupingid) || $changes);

    if($changes) {
        rebuild_course_cache($section->course, true);
    }
    return $changes;
}


/**
    * Updates course_module table enforcing section restrictions
    *
    * Section id is expected in $data->id (or $data['id'])
    * If $data does not contain property with the option name, the option will not be updated
    *
    * @param stdClass $section section object or ID from format_topicgroup_sections containing "section" field from course_sections
    *                             or the same, record from course_sections with sectionid field
    * @param bool $groupmode to be set. default, false means not touchig groupmode
    * @return bool whether there were any changes applied
    */
function format_topicgroup_mod_restrictions($section, $groupmode=false) {
    global $DB;

    $changes = false;
    // set groupmode/grouping for course modules
    $select = " course = :course AND section = :section AND  groupingid <> :groupingid ";
    $params = array('course'=>$section->course, 'section'=>$section->sectionid, 'groupingid'=>$section->groupingid);
    $success = $DB->set_field_select('course_modules', 'groupingid', $section->groupingid, $select, $params);
    $changes = ($changes || $success);

    $modes = array(NOGROUPS,VISIBLEGROUPS,SEPARATEGROUPS);
    if($groupmode !== false && in_array($groupmode, $modes)) {
        $select = " course = :course AND section = :section AND  groupmode <> :groupmode ";
        $params = array('course'=>$section->course, 'section'=>$section->sectionid, 'groupmode'=>$groupmode);
        $success = $DB->set_field_select('course_modules', 'groupmode', $groupmode, $select, $params);
        $changes = ($changes || $success);
    }

    if($changes) {
        rebuild_course_cache($section->course, true);
    }
    return $changes;
}


/**
    * Updates course_module table enforcing section restrictions
    *
    * Section id is expected in $data->id (or $data['id'])
    * If $data does not contain property with the option name, the option will not be updated
    *
    * @param int $courseid for this course
    * @param stdClass|int $section section object or ID from format_topicgroup_sections
    * @param int $groupingid for this section
    * @param string $table type of ID for section ID from either course_modules (cm, relativ eorder)  or course_sections (cs, absolute)
    * @param bool $groupmode to be set. default, false means not touchig groupmode
    * @return bool whether there were any changes to the options values
    */
function format_topicgroup_section_availability($courseid, $sectionnum, $groupingid) {
    global $DB;

    $changes = false;
    $modinfo = get_fast_modinfo($courseid);
    $sectioninfo = $modinfo->get_section_info($sectionnum);
    $availability_info = new \core_availability\info_section($sectioninfo);

    $newjson = '';
    $delete = false;

    if($groupingid) {
        // we are adding/editing a grouping. SET availability conditions
        $groupingjson = \availability_grouping\condition::get_json($groupingid); // the new condition to add;
        if(!$sectioninfo->availability) {
            // NO condition yet, add grouping condition
            $newjson = \core_availability\tree::get_root_json(array($groupingjson),
                                    \core_availability\tree::OP_AND, array(true));
        } else {
            // we have some conditions
            $treejson = json_decode($sectioninfo->availability);
            $tree = $availability_info->get_availability_tree();
            $conditions = $tree->get_all_children('\core_availability\condition');
            $firstcondition = reset($conditions);
            if(($treejson->op != \core_availability\tree::OP_AND) ||
                                (get_class($firstcondition) != 'availability_grouping\condition') ) {
                // add grouping condition on top, then the rest or the tree
                $newjson = \core_availability\tree::get_root_json(array($groupingjson, $treejson),
                                    \core_availability\tree::OP_AND, array(true, true));
            } else {
                // see alternative on https://moodle.org/mod/forum/discuss.php?d=282288
                // The first element is an availability_grouping\condition, just make sure right groupingid
                if($treejson->c[0]->type == 'grouping') {
                    if($treejson->c[0]->id != $groupingid) {
                        $treejson->c[0]->id = $groupingid;
                        $newjson = $treejson;
                    }
                }
            }
        }
    } else {
        // if groupingid = 0 we are REMOVING restrictions, if any
        if($sectioninfo->availability) {
            $treejson = json_decode($sectioninfo->availability);
            $tree = $availability_info->get_availability_tree();
            $conditions = $tree->get_all_children('\core_availability\condition');
            $firstcondition = reset($conditions);
            if(get_class($firstcondition) == 'availability_grouping\condition') {
                // remove first, move up all rest
                $children = $treejson->c;
                $showc = $treejson->showc;
                array_shift($children); // array shift renumber the array
                array_shift($showc);
                if(count($children) < 1) {
                    // no more conditions, remove
                    $delete = true;
                } else {
                    $newjson = new stdClass;
                    $newjson->op = $treejson->op;
                    $newjson->c = $children;
                    $newjson->showc = $showc;
                }

            } else {
                // weird, some conditions before , remove all conditions
                $delete = true;
            }
        }
    }
    if($newjson || $delete) {
        $newvalue = $delete ? null :  json_encode($newjson);
        $changes = $DB->set_field('course_sections', 'availability', $newvalue, array('course'=>$courseid, 'id'=>$sectioninfo->id));
    }

    return $changes;
}
