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
class batchmanage_managejob_courseconfig extends batchmanage_managejob_plugin {
    /**
     * Constructor for the abstract plugin type class
     *
     * @param assign $assignment
     * @param string $type
     */
    public final function __construct($name) {
        $this->name = $name;
        $this->firstform = 'course_config';
        $this->nextmsg = get_string('courses_selector', 'tool_batchmanage');
        $this->path = core_component::get_plugin_directory('managejob', $name);  

    }
    
    public function process_course_config($formdata) {
        $data = new stdClass();
        foreach($formdata as $key => $value) {
            $last = substr($key, -6, 6);
            if($last == 'modify') {
                $first = substr($key, 0, -6);
                $data->{$first} = $formdata->{$first};
                /*
                if(is_array($data->{$first}) && (strpos($first, 'date') !== false)) {
                    $value = $data->{$first} + array('year' => 1970, 'month' => 1, 'day' => 1, 'hour' => 0, 'minute' => 0, 'second' => 0);
                    $data->{$first} = make_timestamp($value['year'], $value['month'], $value['day'], $value['hour'], $value['minute'], $value['second']);
                }
                */
            }
        }
        return json_encode($data);
    }
    
    
    public function process_action($action, $formdata) {
        $next = '';
        if($action == 'course_config') {
            $this->formsdata[$action] = $this->process_course_config($formdata);
            $next = 'courses_selector';
            $this->nextmsg = get_string('reviewconfirm', 'tool_batchmanage');
        } else {
            return parent::process_action($action, $formdata);
        }
        
        return $next;
    }
    
    public function review_confirm_formsdata(& $mform, $action, $data, $innerform) {
        if($action == 'course_config') {
            $this->process_grouped_innerform($mform, $action, $data, $innerform);
        }
        parent::review_confirm_formsdata($mform, $action, $data, $innerform);
    }
    
    
    public function has_applicable_action() {
        $data = json_decode($this->formsdata['course_config']);

        foreach($data as $key => $value) {
            if(is_object($value) && (strpos($key, 'date') !== false)) {
                $value = get_object_vars($value) + array('year' => 1970, 'month' => 1, 'day' => 1, 'hour' => 0, 'minute' => 0, 'second' => 0);
                $data->$key = make_timestamp($value['year'], $value['month'], $value['day'], $value['hour'], $value['minute'], $value['second']);
            }
        }
        
        return $data;
    }

    
    public function combine_selectors_sql() {
        list($wherecourse, $join, $params) = $this->courses_selector_sql();
        $sql = "SELECT c.id as courseid, c.*
                FROM {course} c
                $join
            WHERE 1 $wherecourse
                ORDER BY c.category ASC, c.shortname ASC ";
        return array($sql, $params);
    }

    public function apply_job_on_item($course, $data) {
        global $CFG, $DB; 
        
        $newdata = false;
        $success = false;
        $extramsg = '';
        
        // chek there is somthing new in this particular course (faster i not updating with the existing data)
        foreach($data as $key => $value) {
            if(!isset($course->$key) || $course->$key != $value ) {
                $newdata = true;
            }
        }
        
        if($newdata) {
            $data->id = $course->courseid;
            if($success = $DB->update_record('course', $data)) {
                // now update course format, if needed
                $oldcourse = 0;
                $format = isset($data->format) ? $data->format : $course->format;
                if(in_array($format, array('weeks', 'topics', 'topicgroup', 'topcoll'))) {
                    foreach(array('numsections', 'hiddensections', 'coursedisplay') as $field) {
                        if(isset($data->{$field})) {
                            $DB->set_field('course_format_options', 'numsections', $data->{$field}, array('courseid'=>$course->id, 'format'=>$format, 'name'=>$field));
                        }
                    }
                }
                
                if(isset($data->category)) {
                    $course = $DB->get_record('course', array('id'=>$data->id));                
                    $newparent = context_coursecat::instance($course->category);
                    //$context->update_moved($newparent);
                }
                rebuild_course_cache($data->id);
                
                // Test for and remove blocks which aren't appropriate anymore
                blocks_remove_inappropriate($course);
                // Save any custom role names.
                save_local_role_names($data->id, $data);
                // Trigger a course updated event.
                $event = \core\event\course_updated::create(array(
                    'objectid' => $course->id,
                    'context' => context_course::instance($data->id),
                    'other' => array('shortname' => $course->shortname,
                                        'fullname' => $course->fullname)
                ));

                $event->set_legacy_logdata(array($course->id, 'course', 'update', 'edit.php?id=' . $course->id, $course->id));
                $event->trigger();            
            }
        } else {
            $extramsg = ' - unchanged ';
        }
    
        return array($success, '', $extramsg);
    }
    
}
