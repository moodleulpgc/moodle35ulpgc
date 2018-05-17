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
 * This file contains the function for feedback_plugin abstract class
 *
 * @package   tool_batchmanage
 * @copyright 2016 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//include_once($CFG->dirroot.'/tool/batchmanage/managejob_forms.php');


/**
 * Abstract class for feedback_plugin inherited from assign_plugin abstract class.
 *
 * @package   tool_batchmanage
 * @copyright 2016 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class batchmanage_managejob_modgradeitem extends batchmanage_managejob_plugin {

    use batchmanage_mod_selector_sql; 
    
    /**
     * Constructor for the abstract plugin type class
     *
     * @param assign $assignment
     * @param string $type
     */
    public final function __construct($name) {
        $this->name = $name;
        $this->firstform = 'gradeitem_selector';
        $this->nextmsg = get_string('courses_selector', 'tool_batchmanage');
        $this->path = core_component::get_plugin_directory('managejob', $name);  
    }

    
    public function process_gradeitem_selector($formdata) {
        $data = new stdClass();
        $usable = array('module', 'instancename', 'uselike', 'instanceid', 'coursemoduleid', 'insection', 
                            'indent', 'visible', 'groupmode', 'cmidnumber', 'adminrestricted');
        $data = new stdClass();
        foreach($formdata as $key => $value) {
            if(in_array($key, $usable)) {
                $data->{$key} = $value;
            }
        }

        return json_encode($data);
    }

    
    
    public function process_action($action, $formdata) {
        $next = '';
        if($action == 'gradeitem_selector') {
            $this->formsdata[$action] = $this->process_gradeitem_selector($formdata);
            $next = 'courses_selector';
            $this->nextmsg = get_string('reviewconfirm', 'tool_batchmanage');
        } else {
            return parent::process_action($action, $formdata);
        }
        
        return $next;
    }
    
    public function review_confirm_formsdata(& $mform, $action, $data, $innerform) {
        if($action == 'gradeitem_selector') {
            $this->process_non_grouped_innerform($mform, $action, $data, $innerform);
        }
        parent::review_confirm_formsdata($mform, $action, $data, $innerform);
    }
    
    
    public function has_applicable_action() {
        $data = json_decode($this->formsdata['gradeitem_selector']);

        return $data;
    }

    public function combine_selectors_sql() {
        list($wheremod, $mparams) = $this->mod_selector_sql();
        list($wherecourse, $coursejoin, $cparams) = $this->courses_selector_sql();
        $modselector = json_decode($this->formsdata['mod_selector']);
        $moduletable = '{'.$modselector->module.'}';
        
        $params = array_merge($mparams, $cparams);

        $sql = "SELECT md.*, cm.id AS cmid, s.id AS sectid, s.section AS cwsection, c.id AS courseid, c.shortname, c.category
                    FROM $moduletable md
                    JOIN {course_modules} cm ON md.id = cm.instance AND md.course = cm.course AND cm.module = ?
                    JOIN {course_sections} s ON cm.section = s.id AND cm.course = s.course
                    JOIN {course} c ON cm.course = c.id
                    $coursejoin
                WHERE $wheremod  $wherecourse
                    ORDER BY c.category ASC, c.shortname ASC, md.name ASC ";

        return array($sql, $params);
    }

    public function apply_job_on_item($mod, $data) {
        global $CFG, $COURSE, $DB; 
        
        //include_once($CFG->dirroot.'/course/modlib.php');
        $success = false;
        
        if($data->modmodule) {
            $this->cleanup_course_cache($mod->courseid);
            $updatefunction = $data->module."_update_grades";
            $updatefunction($mod);
            $success = true;
        }
    
        return array($success, $mod->name, '');
    }

    public function cleanup_course_cache($courseid) {
        if(isset($this->currentcourse->id)) {
            // if set, we have a previous currentcourse different from this, just cleanup previous
            grade_regrade_final_grades($this->currentcourse->id);
        }
        
        return parent::cleanup_course_cache($courseid);
    }
    
    public function after_execution_cleanup() {
        // just in case cleanup last course
        if(isset($this->currentcourse->id)) {
            grade_regrade_final_grades($this->currentcourse->id);
            parent::after_execution_cleanup();
        }
    }
    
}
