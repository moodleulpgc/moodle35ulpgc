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
class batchmanage_managejob_gcatadd extends batchmanage_managejob_plugin {
    /**
     * Constructor for the abstract plugin type class
     *
     * @param assign $assignment
     * @param string $type
     */
    public final function __construct($name) {
        $this->name = $name;
        $this->firstform = 'gcat_selector';
        $this->nextmsg = get_string('courses_selector', 'tool_batchmanage');
        $this->path = core_component::get_plugin_directory('managejob', $name);  

    }
    
    public function process_gcat_selector($formdata) {
        $data = new stdClass();

        foreach($formdata as $key => $value) {
            $first = substr($key, 0, 4);
            if($first == 'gcat') {
                $data->{$key} = $formdata->{$key};
            }
        }
        return json_encode($data);
    }
    
    
    public function process_action($action, $formdata) {
        $next = '';
        if($action == 'gcat_selector') {
            $this->formsdata[$action] = $this->process_gcat_selector($formdata);
            $next = 'courses_selector';
            $this->nextmsg = get_string('reviewconfirm', 'tool_batchmanage');
        } else {
            return parent::process_action($action, $formdata);
        }
        
        return $next;
    }
    
    public function review_confirm_formsdata(& $mform, $action, $data, $innerform) {
        if($action == 'gcat_selector') {
            $this->process_non_grouped_innerform($mform, $action, $data, $innerform);
        }
        parent::review_confirm_formsdata($mform, $action, $data, $innerform);
    }
    
    
    public function has_applicable_action() {
        
        $data = json_decode($this->formsdata['gcat_selector']);
        

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

    
    public function get_gcat_list_from_csv($courseid, $data) {
        global $DB;
        
        $lines = explode("\n", $data->gcattemplate);
    
        if(!$data->gcattemplate || count($lines) < 2) {
            \core\notification::error(get_string('noinputdata', 'managejob_gcatadd'));
            return; 
        }

        $errors = false;
        
        $delimiter = array("|",",",";");
        $line = array_shift($lines);
        $replace = str_replace($delimiter, $delimiter[0], trim($line));
        $items = array_unique(explode($delimiter[0], $replace));
        $fields = array('fullname' => get_string('categoryname', 'grades'),
                        'grade_item_itemname' => get_string('categorytotalname', 'grades'),
                        'grade_item_iteminfo' => get_string('iteminfo', 'grades'),
                        'grade_item_idnumber' => get_string('idnumbermod'),
                        'aggregation'         => get_string('aggregation', 'grades'),
                        'parentcategory'      => get_string('parentcategory', 'grades'),
                        );
        foreach($items as $k => $item) {
            if($field = array_search(trim($item), $fields)) {
                $items[$k] = $field;
            } else {
                $errors = true; // if key not known is an error
            }
        }
        
        if($errors) {
            \core\notification::error(get_string('errorfieldcolumns', 'managejob_gcatadd'));
            return;
        }
        
        $categories = array();

        $grade_category = new grade_category(array('courseid'=>$courseid), false);
        $grade_category->apply_default_settings();
        $grade_category->apply_forced_settings();
        $grade_item = new grade_item(array('courseid'=>$courseid, 'itemtype'=>'category'), false);
        
        foreach($lines as $line) {
            $fields = array();
            $replace = str_replace($delimiter, $delimiter[0], trim($line));
            $parts = array_unique(explode($delimiter[0], $replace));
            foreach($parts as $k => $part) {
                $fields[$items[$k]] = trim($part);
            }
            if(!$fields['fullname']) {
                continue;
            }
            
            $category = (object)$fields;
            foreach($grade_category->get_record_data() as $key => $value) {
                $field = "gcat$key";
                if(!isset($category->$key) || ($category->$key == '')) {
                    if(isset($data->$field)) {
                        $category->$key = ($data->$field !== '') ? $data->$field : $value;
                    }
                }
            }
            
            // set parent category
            if(!isset($category->parentcategory) || !$category->parentcategory) {
                $category->parentcategory = $data->gcatparentcategory;
            }
            if($category->parentcategory == '?') {
                $category->parentcategory = '';
            }

            foreach($grade_item->get_record_data() as $key => $value) {
                $field = "grade_item_$key";
                $gfield = "gcat$field";
                if(!isset($category->$field) || ($category->$field == '')) {
                    if(isset($data->$gfield)) {
                        $category->$field = ($data->$gfield !== '') ? $data->$gfield : $value;
                    }
                }
            }
            
            if($category->fullname) {
                $category->courseid = $courseid;
                $categories[] = $category;
            }
        }
    
        return $categories;
    }
    
    public function get_grade_item_from_category($grade_category, $category) {
    
        // first set and fill itemdata 
        $itemdata = new stdClass();
        foreach ($category as $k => $v) {
            if (preg_match('/grade_item_(.*)/', $k, $matches)) {
                $itemdata->{$matches[1]} = $v;
            }
        }
    
        if (!isset($itemdata->aggregationcoef)) {
            $itemdata->aggregationcoef = 0;
        }

        if (!isset($itemdata->gradepass) || $itemdata->gradepass == '') {
            $itemdata->gradepass = 0;
        }

        if (!isset($itemdata->grademax) || $itemdata->grademax == '') {
            $itemdata->grademax = 0;
        }

        if (!isset($itemdata->grademin) || $itemdata->grademin == '') {
            $itemdata->grademin = 0;
        }
        
        $convert = array('grademax', 'grademin', 'gradepass', 'multfactor', 'plusfactor', 'aggregationcoef', 'aggregationcoef2');
        foreach ($convert as $param) {
            if (property_exists($itemdata, $param)) {
                $itemdata->$param = unformat_float($itemdata->$param);
            }
        }
        if (isset($itemdata->aggregationcoef2)) {
            $itemdata->aggregationcoef2 = $itemdata->aggregationcoef2 / 100.0;
        }
    
        //now we can get & process grade item
        $grade_item = $grade_category->load_grade_item();
        $grade_item_copy = fullclone($grade_item);
        grade_item::set_properties($grade_item, $itemdata);
        
        if (empty($grade_item->id)) {
            $grade_item->id = $grade_item_copy->id;
        }
        if (empty($grade_item->grademax) && $grade_item->grademax != '0') {
            $grade_item->grademax = $grade_item_copy->grademax;
        }
        if (empty($grade_item->grademin) && $grade_item->grademin != '0') {
            $grade_item->grademin = $grade_item_copy->grademin;
        }
        if (empty($grade_item->gradepass) && $grade_item->gradepass != '0') {
            $grade_item->gradepass = $grade_item_copy->gradepass;
        }
        if (empty($grade_item->aggregationcoef) && $grade_item->aggregationcoef != '0') {
            $grade_item->aggregationcoef = $grade_item_copy->aggregationcoef;
        }
        
        // Handle null decimals value - must be done before update!
        if (!property_exists($itemdata, 'decimals') or $itemdata->decimals < 0) {
            $grade_item->decimals = null;
        }
        // $grade_item->weightoverride = $itemdata->weightoverride; // not set override in form

        // $grade_item->outcomeid = null; // not used outcomes in form
        
        $parent_category = $grade_category->get_parent_category();
        if (!$parent_category) {
            // keep as is
        } else if ($parent_category->aggregation == GRADE_AGGREGATE_SUM or $parent_category->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2) {
            $grade_item->aggregationcoef = $grade_category->grade_item_aggregationcoef == 0 ? 0 : 1;
        } elseif(isset($grade_category->grade_item_aggregationcoef)) {
            $grade_item->aggregationcoef = format_float($grade_category->grade_item_aggregationcoef, 4);
        }
    
        return $grade_item;
    }
    
    
    public function apply_job_on_item($course, $data) {
        global $CFG, $DB, $USER; 
        
        $success = false;
        $extramsg = '';
        
        if($course && $data) {
            if($categories = $this->get_gcat_list_from_csv($course->id, $data)) {
                
                // if we are inserting before, the order will be reversed, take care of this
                if($data->gcatinsertfirst) {
                    $categories = array_reverse($categories);
                }
                
                $skipped = array();
                $added = array();
                
                foreach($categories as $category) {
                    $grade_category = new grade_category(array('courseid'=>$category->courseid), false);
                    $grade_category->apply_default_settings();
                    $grade_category->apply_forced_settings();                
                
                    // first check if this category name exists
                    if($DB->record_exists('grade_categories', array('courseid'=>$category->courseid, 
                                                                    'fullname'=>$category->fullname))) {
                        $skipped[] = $category->fullname.' already exists ';
                        continue;
                    }
                
                    // then check if grade idnumber already used 
                    if($category->grade_item_idnumber && $DB->record_exists('grade_items', array('courseid'=>$category->courseid, 
                                                                                        'idnumber'=>$category->grade_item_idnumber))) {
                        $skipped[] = $category->fullname.' IDnumber already exists ';
                        continue;
                    }
                
                    // Now check if parent exists
                    if($category->parentcategory) {
                        //we must check it exists
                        if(!$parent = $DB->get_field('grade_categories', 'id', array('courseid'=>$category->courseid, 
                                                                                    'fullname'=>$category->parentcategory))) {
                            $skipped[] = $category->fullname.' has no parent: '.$category->parentcategory;
                            continue;
                        } else {
                            $category->parentcategory = $parent;
                        }
                    } else {
                        // if empty means this category has course as parent
                        $select = " courseid = :courseid AND depth = 1 AND parent IS NULL ";
                        if(!$parent = $DB->get_field_select('grade_categories', 'id', $select, array('courseid'=>$category->courseid))) {
                            $skipped[] = ' course has no parent ';
                            continue;
                        } else {
                            $category->parentcategory = $parent;
                        }
                    }
                
                    // Set properties for grade category and insert/update as needed
                    grade_category::set_properties($grade_category, $category);
                        /// CATEGORY
                    if (empty($category->id)) {
                       $success = $grade_category->insert();

                    } else {
                       $success = $grade_category->update();
                    }
                    
                    // set parent if needed
                    if (isset($category->parentcategory)) {
                       $grade_category->set_parent($category->parentcategory, 'gradebook');
                    }

                    /// GRADE ITEM
                    // create the associated grade item 
                    $grade_item = $this->get_grade_item_from_category($grade_category, $category);
                    
                    $grade_item->update(); // We don't need to insert it, it's already created when the category is created
                    
                    if($data->gcatinsertfirst && $parent_category = $grade_category->get_parent_category()) {
                        $sortorder = $parent_category->get_sortorder();
                        $grade_category->move_after_sortorder($sortorder); 
                    }
                    
                    if($success) {
                        $added[] = $category->fullname;
                    }
                }
                if($added) {
                    $extramsg .= ' :: '.implode(', ', $added).' :: ';
                    $this->currentcourse = $course;
                }
                if($skipped) {
                    $extramsg .= ' skipped: '.implode(', ', $skipped);
                }
            } else {
                $extramsg = ' No input categories';
            }
        }

        return array($success, '', $extramsg);
    }
    
    public function after_execution_cleanup() {
        // just in case cleanup last course
        if(isset($this->currentcourse->id)) {
            grade_regrade_final_grades($this->currentcourse->id);
            parent::after_execution_cleanup();
        }
    }
    
}
