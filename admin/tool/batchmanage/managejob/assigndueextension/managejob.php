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

global $CFG;
require_once($CFG->dirroot . '/mod/assign/locallib.php');

/**
 * Abstract class for feedback_plugin inherited from assign_plugin abstract class.
 *
 * @package   tool_batchmanage
 * @copyright 2016 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class batchmanage_managejob_assigndueextension extends batchmanage_managejob_plugin {
    /**
     * Constructor for the abstract plugin type class
     *
     * @param assign $assignment
     * @param string $type
     */
    public final function __construct($name) {
        $this->name = $name;
        $this->firstform = 'extension_config';
        $this->nextmsg = get_string('courses_selector', 'tool_batchmanage');
        $this->path = core_component::get_plugin_directory('managejob', $name);  

    }
    
    public function process_extension_config($formdata) {
        $data = new stdClass();
        foreach($formdata as $key => $value) {
            $first = substr($key, 0, 3);
            if($first == 'mod') {
                $data->{$key} = $formdata->{$key};
            }
        }
        return json_encode($data);
    }
    
    
    public function process_action($action, $formdata) {
        $next = '';
        if($action == 'extension_config') {
            $this->formsdata[$action] = $this->process_extension_config($formdata);
            $next = 'courses_selector';
            $this->nextmsg = get_string('reviewconfirm', 'tool_batchmanage');
        } else {
            return parent::process_action($action, $formdata);
        }
        
        return $next;
    }
    
    public function review_confirm_formsdata(& $mform, $action, $data, $innerform) {
        if($action == 'extension_config') {
            $this->process_non_grouped_innerform($mform, $action, $data, $innerform);
        }
        parent::review_confirm_formsdata($mform, $action, $data, $innerform);
    }
    
    
    public function has_applicable_action() {
        $data = json_decode($this->formsdata['extension_config']);
        
        return $data;
    }

    
    public function combine_selectors_sql() {
        global $DB;
        
        list($wherecourse, $join, $cparams) = $this->courses_selector_sql();
        
        $formdata = json_decode($this->formsdata['extension_config']);
        
        $moduleid = $DB->get_field('modules', 'id', array('name' => 'assign'));
        $params = array();
        $params[] = $moduleid;

        $wheregradecategory = " gc.fullname = ? ";
        $params[] = $formdata->modgradecatname;

        $whereunerasable = '';
        if (!empty($formdata->modonlyadmin)) {
            $whereunerasable = " AND cm.score <> 0 ";
        }

        $wheresection = '';
        if(isset($formdata->modinsection) &&  $formdata->modinsection > -1) {
            $wheresection =  " AND s.section = ? ";
            $params[] = $formdata->modinsection;
        }

        $params = array_merge($params, $cparams);                

        $sql = "SELECT md.id, md.name, md.duedate, md.cutoffdate, cm.id AS cmid, s.id AS sectid, c.id AS courseid, c.shortname, c.category, gi.categoryid AS gcategory
                    FROM {assign} md
                    JOIN {course_modules} cm ON md.id = cm.instance AND md.course = cm.course AND cm.module = ? AND visible = 1
                    JOIN {course_sections} s ON cm.section = s.id AND cm.course = s.course
                    JOIN {grade_items} gi ON md.course = gi.courseid AND md.id = gi.iteminstance AND gi.itemtype = 'mod' AND  gi.itemmodule = 'assign'
                    JOIN {grade_categories} gc ON gi.categoryid = gc.id AND md.course = gc.courseid
                    JOIN {course} c ON cm.course = c.id
                    $join
                WHERE $wheregradecategory $whereunerasable  $wheresection $wherecourse
                    ORDER BY c.category ASC, c.shortname ASC ";
                
        return array($sql, $params);
    }

    public function apply_job_on_item($mod, $data) {
        global $CFG, $DB; 
        
        $success = false;
        $extramsg = '';
        
        // check there is something new in this particular course (faster i not updating with the existing data)
        $finaldate = max($mod->duedate, $mod->cutoffdate);
        if($data->moddatetimevalue > $finaldate) {
            if(isset($this->currentcourse) && $this->currentcourse->id == $mod->courseid) {
                $course = $this->currentcourse;
            } elseif(isset($this->currentcourse->id)) {
                // if set, we have a previous currentcourse different from this, just cleanup
                rebuild_course_cache($this->currentcourse->id);
                grade_regrade_final_grades($this->currentcourse->id);
            } else {
                // $this->currentcourse is not set, get it 
                $course = $DB->get_record('course', array('id'=>$mod->courseid), '*', MUST_EXIST);
                $this->currentcourse = $course;
            }
            
            // get the request parameters
            list ($course, $cm) = get_course_and_cm_from_cmid($mod->cmid, 'assign');
            $context = context_module::instance($cm->id);
            $assign = new assign($context,$cm,$course);
            $users = array_keys($assign->list_participants(0, true));
            // get plugin
            $plugin = $assign->get_plugin_by_type('assignfeedback', 'copyset');
            $users = $plugin->get_users_specialperiod(array_combine($users, $users));
            $applied = $plugin->process_dueextensions($users, $data->moddatetimevalue);
            if($applied) {
                $extramsg = " : $applied extended";
                $success = true;
            }
        } else {
            $extramsg = ' - extension < duedate ';
        }
    
        return array($success, $mod->name, $extramsg);
    }
   
}
