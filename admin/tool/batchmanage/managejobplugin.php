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

include_once($CFG->dirroot.'/admin/tool/batchmanage/managejob_forms.php');
include_once($CFG->dirroot.'/admin/tool/batchmanage/managejob_traits.php');

/**
 * Abstract class for feedback_plugin inherited from assign_plugin abstract class.
 *
 * @package   tool_batchmanage
 * @copyright 2016 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class batchmanage_managejob_plugin  {
    /** @var string $name plugin name */
    public $name = '';
    /** @var string $path fullpath to  */
    public $path = '';
   
    /** @var string $firstform  */
    public $firstform = '';

    /** @var string $nextmsg to be displayed in form */
    public $nextmsg = '';
    
    /** @var array of objects json serielize data of previous forms    */
    public $formsdata = array();
    
    /** @var object the current course operating on    */
    public $currentcourse = null;
    
   
   
    public static function create($name) {
        $plugin = null;
        $path = core_component::get_plugin_directory('managejob', $name); 
        if (file_exists($path . '/managejob.php')) {
                require_once($path . '/managejob.php');

                $pluginclass = 'batchmanage_managejob_' . $name;

                $plugin = new $pluginclass($name);
        } else {
            print_error('errorpluginnotfound', 'tool_batchmanage', '', $name);
        }
    
        return $plugin;
    }
  
   
    public function get_display_form($action) {
        if(!$action) {
            $action = $this->firstform;
        }
    
        if(!$action) {
            return false;
        }
    
        if (file_exists($this->path . '/managejob_forms.php')) {
                require_once($this->path . '/managejob_forms.php');
        }
        
        $formclass = 'batchmanage_'.$action.'_form';
        
        $mform = new $formclass(null, array('action'=>$action, 'managejob'=>$this));
        
        return $mform;
    }

    public function process_courses_selector($formdata) {
        $usable = array('coursecategories', 'visible', 'format', 'coursetoshortnames', 'idnumber', 'fullname', 
                            'term', 'credit', 'department', 'ctype');
        $data = new stdClass();
        foreach($formdata as $key => $value) {
            if(in_array($key, $usable)) {
                $data->{$key} = $value;
            }
        }
        return json_encode($data);
    }
    
    public function process_formsdata() {
        if (($formdata = data_submitted()) && confirm_sesskey()) {
            foreach($formdata as $key => $value) {
                if(substr($key, 0, 11) == 'formsdata__') {
                    $action = substr($key, 11);
                    $this->formsdata[$action] = $value;
                }
            }
        }
    }

    public function process_non_grouped_innerform(&$mform, $action, $data, $innerform) {
        foreach($innerform->_elementIndex as $key =>$index) {
            if(isset($data->{$key})) {
                $element = $innerform->getElement($key);
                $name = $action.'_'.$key;
                $element->setName($name);
                $element->setvalue($data->{$key});
                $mform->addElement($element);
                $type = $element->getType();
                if($type == 'text') {
                    $mform->setType($name, PARAM_RAW);
                }
                if(($type == 'checkbox' || $type =='advcheckbox') && $data->{$key}) {
                    $mform->setDefault($name, 1);
                }  
                
                $mform->freeze($name);
            }
        }
    }
    
    public function process_grouped_innerform(&$mform, $action, $data, $innerform) {
        foreach($innerform->_elementIndex as $keygroup =>$index) {
            if(substr($keygroup, -5, 5) == 'group') {
                $group = $innerform->getElement($keygroup);
                $elements = $group->getElements();
                foreach($elements as $element) {
                    $name = $element->getName();
                    if(isset($data->{$name})) {
                        $key = $action.'_'.$name;
                        $key = $name;
                        $element->setName($key);
                        $type = $element->getType();
                        if(strpos($type, 'date') !== false) {
                            $selector = $data->{$name};
                            $enabled = 0;
                            if(is_object($selector)) {
                                $value = get_object_vars($selector) + array('year' => 1970, 'month' => 1, 'day' => 1, 'hour' => 0, 'minute' => 0, 'second' => 0);
                                $timestamp = make_timestamp($value['year'], $value['month'], $value['day'], $value['hour'], $value['minute'], $value['second']);
                                $enabled = $selector->enabled;
                            } else {
                                $timestamp = $selector;
                                $enabled = $selector;
                            }
                            $inners = $element->getElements();
                            
                            foreach($inners as $ikey => $inner) {
                                if($inner->getType() == 'checkbox' && !$enabled) {
                                    $label = $inner->getText();
                                    $inners[$ikey] = $mform->createElement('checkbox', $name.'[enabled]', '', $label);
                                }
                            }
                            $element->setElements($inners);
                            $mform->addElement($element);
                            $mform->setDefault($name, $timestamp);
                        } else {
                            if($type == 'select' && $element->getMultiple()) {
                                $values = get_object_vars($data->$name);
                                $element->setSelected($values);
                            } else {
                                $element->setValue($data->{$name});
                            }
                            $mform->addElement($element);
                        }
                        
                        if($type == 'checkbox' && $data->{$name}) { 
                            $mform->setDefault($key, 'checked');
                        }
                        
                        if($type == 'text') {
                            $mform->setType($key, PARAM_RAW);
                        }
                        
                        $mform->hardFreeze($key);
                    }
                }
            } else {
                $item = $innerform->getElement($keygroup);
                if($item->getType() == 'header') {
                    $mform->addElement('static',  $item->getName(), $item->_text, '-');
                }
            }
        }
    }
    
    
    public function review_confirm_formsdata(&$mform, $action, $data, $innerform) {
        if($action == 'courses_selector' ) {
            $this->process_non_grouped_innerform($mform, $action, $data, $innerform);
        }
    }
   
    public function process_action($action, $formdata) {
        $next = '';
        //$this->process_formsdata($formdata);
        if($action == 'courses_selector') {
            $this->formsdata[$action] = $this->process_courses_selector($formdata);
            //$this->nextmsg = get_string('reviewconfirm', 'tool_batchmanage');
            $this->nextmsg = get_string('apply'.$this->name, 'managejob_'.$this->name);
            $next = 'confirm';
        } elseif($action == 'confirm') {
            if(isset($formdata->scheduledtask) && $formdata->scheduledtask > time()) {
                return $this->schedule_a_task($action, $formdata); 
            }
            return 'execute';
        }
        return $next;
    }

    public function schedule_a_task($action, $formdata) {
        global $DB;
        // here we need to save data for scheduling
        //$value = ($formdata->scheduledtask) + array('year' => 1970, 'month' => 1, 'day' => 1, 'hour' => 0, 'minute' => 0, 'second' => 0);
        //$nextruntime = make_timestamp($value['year'], $value['month'], $value['day'], $value['hour'], $value['minute'], $value['second']);
        
        $nextruntime = $formdata->scheduledtask;

        // create the instance
        $task = new tool_batchmanage\task\stored_managementjob_task();
        // set blocking if required (it probably isn't)
        // $domination->set_blocking(true);
        // add custom data
        $task->set_component('tool_batchmanage');
        $task->set_custom_data(array(
            'managejob' => $this->name,
            'jobdata'   => $this->formsdata,
        ));
        $task->set_next_run_time($nextruntime); 

        // queue it
        $taskid = \core\task\manager::queue_adhoc_task($task);
        // queue_adhoc_task sets nextrumtime to inmediatly by default
        $DB->set_field('task_adhoc', 'nextruntime', $nextruntime, array('id'=>$taskid));

        return 'done';
    }
    
    
    public abstract function has_applicable_action();

    public function get_batchmanage_event() {
        global $USER;
        $event = \tool_batchmanage\event\managementjob_done::create(array(
            'userid' => $USER->id,
            'other' => array('managejob'=>$this->name,
                                'params'=>$this->formsdata),
        ));
        return $event;
    }
    
    public function courses_selector_sql() {
        global $DB;
        
        $params = array();
        $wherecourse = '';
        $join = '';
                   
        $formdata = json_decode($this->formsdata['courses_selector']);
    
        if(isset($formdata->visible) && $formdata->visible != -1) {
            $wherecourse .= " AND c.visible = ? ";
            $params[] = $formdata->visible;
        }
        if(isset($formdata->format) &&  $formdata->format !='all') {
            $wherecourse .= " AND c.format = ? ";
            $params[] = $formdata->courseformat;
        }

        if(isset($formdata->coursecategories) &&  $formdata->coursecategories) {
            //if($cats = explode(',', $formdata->coursecategories)) {
                list($insql, $inparams) = $DB->get_in_or_equal($formdata->coursecategories);
                $wherecourse .= " AND c.category $insql ";
                $params = array_merge($params, $inparams);
            //}
        }

        if(isset($formdata->coursetoshortnames) && trim($formdata->coursetoshortnames) != '') {
            if($names = explode(',' , addslashes($formdata->coursetoshortnames))) {
                foreach($names as $key => $name) {
                    $names[$key] = trim($name);
                }
                list($insql, $inparams) = $DB->get_in_or_equal($names);
                $wherecourse .= " AND c.shortname $insql ";
                $params = array_merge($params, $inparams);
            }
        }

        if (isset($formdata->idnumber) && $formdata->idnumber) {
            $wherecourse .= " AND ".$DB->sql_like('c.idnumber', '?');
            $params[] = $formdata->idnumber;
        }

        if (isset($formdata->fullname) && $formdata->fullname) {
            $wherecourse .= " AND ".$DB->sql_like('c.fullname', '?');
            $params[] = $formdata->fullname;
        }
        
        if(get_config('local_ulpgccore', 'version')) {
            // very specific 
            $join = " LEFT JOIN {local_ulpgccore_course} cu ON c.id = cu.courseid ";
        
            if(isset($formdata->term) &&  $formdata->term != -1 ) {
                $wherecourse .= " AND cu.term = ? ";
                $params[] = $formdata->term;
            }
            if(isset($formdata->credit) &&  $formdata->credit != -1) {
                if($formdata->credit == -2) {
                    $wherecourse .= " AND cu.credits > 0 ";
                } else {
                    $wherecourse .= " AND cu.credits = ? ";
                    $params[] = $formdata->credit;
                }
            }
            if(isset($formdata->department) &&  $formdata->department != -1) {
                $wherecourse .= " AND cu.department = ? ";
                $params[] = $formdata->department;
            }
            if(isset($formdata->ctype) &&  $formdata->ctype !='all') {
                $wherecourse .= " AND cu.ctype = ? ";
                $params[] = $formdata->ctype;
            }
        }
    
        return array($wherecourse, $join, $params);
    }

 
    
    public abstract function combine_selectors_sql();
    
    public abstract function apply_job_on_item($item, $applicable);
    
    
    public function cleanup_course_cache($courseid) {
        global $DB;
        if(isset($this->currentcourse) && $this->currentcourse->id == $courseid) {
            $course = $this->currentcourse;
        } else {
            if(isset($this->currentcourse->id)) {
                // if set, we have a previous currentcourse different from this, just cleanup previous
                rebuild_course_cache($this->currentcourse->id);
            }
            // $this->currentcourse is not set, get it 
            $this->currentcourse = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
        }
        return $this->currentcourse;
    }
    
    public function after_execution_cleanup() {
        // just in case cleanup last course
        if($this->currentcourse) {
            // if set, we have a previous currentcourse  just cleanup
            rebuild_course_cache($this->currentcourse->id);
        }
    }
    
    public function execute($verbose = true) {
        global $DB, $OUTPUT;
    
        if(!$applicable = $this->has_applicable_action()) {
            return 'noapplicableaction';
        }
              
        @set_time_limit(600); 
        raise_memory_limit(MEMORY_HUGE);
              
        list($sql, $params) = $this->combine_selectors_sql();
        
        $rs_items = $DB->get_recordset_sql($sql, $params);

        /// now apply those settings to each module instance (and course_modules)
        $oldcourseid = 0;
        $oldcategoryid = 0;
        $strcategory = get_string('category');
        $strcourse = get_string('course');

        if($rs_items->valid()) {
            foreach($rs_items as $item) {
                $success = false;
                $anysuccess = false;
                if($item->category != $oldcategoryid) {
                    $category = $DB->get_field('course_categories', 'name', array('id' => $item->category));
                    echo $OUTPUT->heading($strcategory.': '.$category, 4);
                }

                list($success, $itemmsg, $extramsg) = $this->apply_job_on_item($item, $applicable);
                
                $message = $item->shortname;
                if($itemmsg) { 
                    $message .= ' : '. $itemmsg;
                }
                if($success) {
                    $anysuccess = true;
                    $message .= ' - OK ' ;
                } else {
                    $message .= ' - fail ' ;
                }
            
                if($verbose) {
                    echo $message.' '.$extramsg;
                    echo '<br />';
                } else {
                    mtrace($message.' '.$extramsg);
                }

                $oldcourseid = $item->courseid;
                $oldcategoryid = $item->category;
            }
            
            if($anysuccess) {
                // event admin tool
                $event = $this->get_batchmanage_event();
                $event->trigger();
            }
            
            $this->after_execution_cleanup();
            
        }
        $rs_items->close();
        
        return 'done';
    }
}
