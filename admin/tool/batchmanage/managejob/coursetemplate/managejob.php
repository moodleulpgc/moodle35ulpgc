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
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

/**
 * Abstract class for feedback_plugin inherited from assign_plugin abstract class.
 *
 * @package   tool_batchmanage
 * @copyright 2016 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class batchmanage_managejob_coursetemplate extends batchmanage_managejob_plugin {
    /**
     * Constructor for the abstract plugin type class
     *
     * @param assign $assignment
     * @param string $type
     */
    public final function __construct($name) {
        $this->name = $name;
        $this->firstform = 'course_template';
        $this->nextmsg = get_string('courses_selector', 'tool_batchmanage');
        $this->path = core_component::get_plugin_directory('managejob', $name);  

    }
    
    public function process_course_template($formdata) {
        $data = new stdClass();

        $mform = $this->get_display_form('course_template');
        $path = make_temp_directory($formdata->restoretemplatefile); 
        $path .= '/'.$mform->get_new_filename('restoretemplatefile');
        if($success = $mform->save_file('restoretemplatefile', $path, true)) {
            $data->restoretemplatefile = $path;
            unset($formdata->restoretemplatefile);
        }

        foreach($formdata as $key => $value) {
            $first = substr($key, 0, 7);
            if($first == 'restore') {
                $data->{$key} = $formdata->{$key};
            }
        }
        return json_encode($data);
    }
    
    
    public function process_action($action, $formdata) {
        $next = '';
        if($action == 'course_template') {
            $this->formsdata[$action] = $this->process_course_template($formdata);
            $next = 'courses_selector';
            $this->nextmsg = get_string('reviewconfirm', 'tool_batchmanage');
        } else {
            return parent::process_action($action, $formdata);
        }
        
        return $next;
    }
    
    public function review_confirm_formsdata(& $mform, $action, $data, $innerform) {
        if($action == 'course_template') {
            $this->process_non_grouped_innerform($mform, $action, $data, $innerform);
        }
        parent::review_confirm_formsdata($mform, $action, $data, $innerform);
    }
    
    
    public function has_applicable_action() {
        $data = new stdClass();
        $formdata = json_decode($this->formsdata['course_template']);
        
        $data->restoretemplatefile = $formdata->restoretemplatefile;    
        $restore_target =  backup::TARGET_EXISTING_ADDING;
        if(isset($formdata->restoreemptyfirst) && $formdata->restoreemptyfirst) {
            $restore_target = backup::TARGET_EXISTING_DELETING;
        }
        $data->restore_target = $restore_target;

        $data->users = 0;
        $data->activities = 1;
                if(isset($formdata->restoreusers) && $formdata->restoreusers) {
            $data->users = 1;
        }
        $data->groups = 0;
        if(isset($formdata->restoregroups) && $formdata->restoregroups) {
            $data->groups = 1;
        }
        $data->blocks = 0;
        if(isset($formdata->restoreblocks) && $formdata->restoreblocks) {
            $data->blocks = 1;
        }
        $data->filters = 0;
        if(isset($formdata->restorefilters) && $formdata->restorefilters) {
            $data->filters = 1;
        }
        $data->adminmods = 0;
        if(isset($formdata->restoreadminmods) && $formdata->restoreadminmods) {
            $data->adminmods = 1;
        }

        if($restore_target == backup::TARGET_EXISTING_DELETING) {
            $data->keep_groups_and_groupings = 0;
            if(isset($formdata->restorekeepgroups) && $formdata->restorekeepgroups) {
                $data->keep_groups_and_groupings = 1;
            }
            $data->keep_roles_and_enrolments = 0;
            if(isset($formdata->restorekeeproles) && $formdata->restorekeeproles) {
                $data->keep_roles_and_enrolments = 1;
            }
        }
        return $data;
    }
   
    public function combine_selectors_sql() {
        list($wherecourse, $join, $params) = $this->courses_selector_sql();
        $formdata = json_decode($this->formsdata['course_template']);
        if(isset($formdata->restorenullmodinfo) and $formdata->restorenullmodinfo) {
            $wherecourse .= ' AND cm.course IS NULL';
        }
        
        $sql = "SELECT c.id as courseid, c.*
                FROM {course} c
                $join
            WHERE 1 $wherecourse
                ORDER BY c.category ASC, c.shortname ASC ";
        return array($sql, $params);
    }

    public function apply_job_on_item($course, $data) {
        global $CFG, $DB, $USER; 
        
        $success = false;
        $extramsg = '';
        $restoresettings = array();
        
        // check there is something new in this particular course (faster if not updating with the existing data)
        if($data->restoretemplatefile) {
       
       
            $pathparts = pathinfo($data->restoretemplatefile);
            $tempdir = $CFG->dataroot.'/temp/backup/'. $pathparts['filename'];
            $fb = get_file_packer('application/vnd.moodle.backup');
            $fb->extract_to_pathname($data->restoretemplatefile, $tempdir);
            
            $restoresettings = get_object_vars($data);
            unset($restoresettings['restoretemplatefile']);
            unset($restoresettings['restore_target']);
            $newdata = check_dir_exists($tempdir, false);
            $tempdir = $pathparts['filename'];
        }
        
        $strcourse = get_string('course');
        
        if($newdata && $restoresettings && $tempdir) {
            $controller = new restore_controller($tempdir, $course->id,
                    backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $USER->id,
                    $data->restore_target);
            $errors = array();
            try {
                $controller->get_logger()->set_next(new output_indented_logger(backup::LOG_INFO, false, true));
                $controller->execute_precheck();
                $plan = $controller->get_plan();
                foreach($restoresettings as $key=>$value) {
                    if ($plan->setting_exists($key) and $plan->get_setting($key)->get_status() == base_setting::NOT_LOCKED) {
                        $plan->get_setting($key)->set_value($value);
                    } else {
                        $errors[] = $key;
                    }
                }
                if($data->restore_target == backup::TARGET_EXISTING_DELETING) {
                    restore_dbops::delete_course_content($controller->get_courseid(), $restoresettings);
                }
                $controller->execute_plan();
                if($errors) {
                    $extramsg .= '  (skipped settings: '.implode(', ', $errors).')';
                }
                $controller->log($strcourse.': '.$course->shortname, backup::LOG_INFO, $extramsg);
                $success = true;
            } catch (backup_exception $e) {
                $extramsg = '  << ERROR '.$e->errorcode;
                $controller->log($strcourse.': '.$course->shortname, backup::LOG_WARNING, $error);
                $success = false;
            }
            $controller->destroy();
            unset($controler);  
        } else {
            $extramsg = ' - unchanged ';
        }
    
        return array($success, '', $extramsg);
    }
    
    public function after_execution_cleanup() {
        parent::after_execution_cleanup();
        $data = json_decode($this->formsdata['course_template']);
        $pathparts = pathinfo($data->restoretemplatefile);
        remove_dir($pathparts['dirname']);
    }
}
