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
class batchmanage_managejob_sectionconfig extends batchmanage_managejob_plugin {

    use batchmanage_section_selector_sql; 
    
    /**
     * Constructor for the abstract plugin type class
     *
     * @param assign $assignment
     * @param string $type
     */
    public final function __construct($name) {
        $this->name = $name;
        $this->firstform = 'section_selector';
        $this->nextmsg = get_string('section_config', 'managejob_sectionconfig');
        $this->path = core_component::get_plugin_directory('managejob', $name);  
    }

    
    public function process_section_selector($formdata) {
        $data = new stdClass();
        foreach($formdata as $key => $value) {
            $first = substr($key, 0, 7); 
            if($first == 'section') {
                $data->{$key} = $value;
            }
        }

        return json_encode($data);
    }

    
    public function process_section_config($formdata) {
        $data = new stdClass();
        foreach($formdata as $key => $value) {
            $last = substr($key, -6, 6);
            if($last == 'modify') {
                $first = substr($key, 0, -6);
                $data->{$first} = isset($formdata->{$first}) ? $formdata->{$first} : 0;
                if(is_array($data->{$first})) {
                    if(!isset($data->{$first}['enabled'])) {
                        $data->{$first}['enabled'] = 0;
                    }
                }
            }
        }
        return json_encode($data);
    }
   
    
    public function process_action($action, $formdata) {
        $next = '';
        if($action == 'section_selector') {
            $this->formsdata[$action] = $this->process_section_selector($formdata);
            $next = 'section_config';
            $this->nextmsg = get_string('courses_selector', 'tool_batchmanage');
        } elseif($action == 'section_config') {
            $this->formsdata[$action] = $this->process_section_config($formdata);
            $next = 'courses_selector';
            $this->nextmsg = get_string('reviewconfirm', 'tool_batchmanage');
        } else {
            return parent::process_action($action, $formdata);
        }
        
        return $next;
    }
    
    public function review_confirm_formsdata(& $mform, $action, $data, $innerform) {
        if($action == 'section_selector') {
            $this->process_non_grouped_innerform($mform, $action, $data, $innerform);
        }
        if($action == 'section_config') {
            $this->process_grouped_innerform($mform, $action, $data, $innerform);
        }
        parent::review_confirm_formsdata($mform, $action, $data, $innerform);
    }
    
    
    public function has_applicable_action() {
        $data = json_decode($this->formsdata['section_config']);

        foreach($data as $key => $value) {
            if(is_object($value) && (strpos($key, 'date') !== false)) {
                if(isset($value->enabled) && !$value->enabled) {
                    // if a date is not enabled, just skip it, delete from formdata
                    unset($data->$key);
                } else {
                    $value = get_object_vars($value) + array('year' => 1970, 'month' => 1, 'day' => 1, 'hour' => 0, 'minute' => 0, 'second' => 0);
                    $data->$key = make_timestamp($value['year'], $value['month'], $value['day'], $value['hour'], $value['minute'], $value['second']);
                }
            }
        }
        
        return $data;
    }
    
    
    public function combine_selectors_sql() {
        list($wheresection, $sparams) = $this->section_selector_sql();
        list($wherecourse, $coursejoin, $cparams) = $this->courses_selector_sql();
        
        $params = array_merge($sparams, $cparams);

        $sql = "SELECT cs.*, c.id AS courseid, c.shortname, c.category
                    FROM {course_sections} cs
                    JOIN {course} c ON cs.course = c.id
                    $coursejoin
                WHERE $wheresection  $wherecourse
                    ORDER BY c.category ASC, c.shortname ASC ";

        return array($sql, $params);
    }

    
    public function apply_job_on_item($section, $data) {
        global $CFG, $COURSE, $DB; 
        
        $newdata = new stdClass;
        $success = false;
        $extramsg = '';
        $oldsection = clone $section;
        
        // check there is something new in this particular course (faster if not updating with the existing data)
        foreach($data as $key => $value) {
            if(!isset($section->{$key}) || $section->{$key} != $value ) {
                $newdata->{$key} = $value;
            }
        }
        
        if(get_object_vars($newdata)) {
            $this->cleanup_course_cache($section->course);
            
            //$newdata->id = $section->id;
            if(isset($newdata->usedefaultname)) {
                $newdata->name = null;
            }
            if(isset($newdata->summary_editor)) {
                $newdata->summary = $newdata->summary_editor->text;
                $newdata->summaryformat = $newdata->summary_editor->format;
            }

            if(isset($newdata->availability) && (empty($newdata->availability) || core_text::strtoupper($newdata->availability) == 'NULL')) {
                $newdata->availability = null;
            }

            course_update_section($section->course, $oldsection, $newdata);
            $success = true;

            if(isset($newdata->setasmarker)) {
                course_set_marker($section->course, $section->section);
                $success = true;
            }

        }
    
        return array($success, $section->name, '');
    }
    
    
}
