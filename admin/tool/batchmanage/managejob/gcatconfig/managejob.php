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

/**
 * Abstract class for feedback_plugin inherited from assign_plugin abstract class.
 *
 * @package   tool_batchmanage
 * @copyright 2016 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class batchmanage_managejob_gcatconfig extends batchmanage_managejob_plugin {

    /**
     * Constructor for the abstract plugin type class
     *
     * @param assign $assignment
     * @param string $type
     */
    public final function __construct($name) {
        $this->name = $name;
        $this->firstform = 'gcat_selector';
        $this->nextmsg = get_string('gcat_config', 'managejob_gcatconfig');
        $this->path = core_component::get_plugin_directory('managejob', $name);  
    }

    
    public function process_gcat_selector($formdata) {
        $data = new stdClass();
        foreach($formdata as $key => $value) {
            $first = substr($key, 0, 4); 
            if($first == 'gcat') {
                $data->{$key} = $value;
            }
        }

        return json_encode($data);
    }

    public function process_gcat_config($formdata) {
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
        if($action == 'gcat_selector') {
            $this->formsdata[$action] = $this->process_gcat_selector($formdata);
            $next = 'gcat_config';
            $this->nextmsg = get_string('courses_selector', 'tool_batchmanage');
        } elseif($action == 'gcat_config') {
            $this->formsdata[$action] = $this->process_gcat_config($formdata);
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
        if($action == 'gcat_config') {
            $this->process_grouped_innerform($mform, $action, $data, $innerform);
        }
        parent::review_confirm_formsdata($mform, $action, $data, $innerform);
    }
    

    public function gcat_selector_sql() {
        global $DB;
        
        $formdata = json_decode($this->formsdata['gcat_selector']);
        $params = array();    
        $where = ' 1 ';
        
        if(isset($formdata->gcatname) && $formdata->gcatname) {
            if ($formdata->gcatuselike) {
                $where .= ' AND '.$DB->sql_like('gc.fullname', '?');
            } else {
                $where .= " AND gc.fullname = ? ";
            }
            $params[] =  $formdata->gcatname;
        }
        
        if(isset($formdata->gcatidnumber) && $formdata->gcatidnumber) {
            $where .= " AND gi.idnumber = ? ";
            $params[] = $formdata->gcatidnumber;
        }

        if(isset($formdata->gcatparentname) && $formdata->gcatparentname) {
            $where .= " AND gp.fullname = ? ";
            $params[] = $formdata->gcatparentname;
        }

        if(isset($formdata->gcatparentidnumber) && $formdata->gcatparentidnumber) {
            $where .= " AND gpi.idnumber = ? ";
            $params[] = $formdata->gcatparentidnumber;
        }
        
        if(isset($formdata->gcataggregation) && $formdata->gcataggregation) {
            $where .= " AND gc.aggregation = ? ";
            $params[] = $formdata->gcataggregation;
        }
        
        if(isset($formdata->gcatdepth) && $formdata->gcatdepth) {
            $where .= " AND gc.depth = ? ";
            $params[] = $formdata->gcatdepth;
        }

        if(isset($formdata->gcataggregateonlygraded) && $formdata->gcataggregateonlygraded) {
            $where .= " AND gc.aggregateonlygraded = ? ";
            $params[] = ($formdata->gcataggregateonlygraded == -1) ? 0 : 1;
        }
        
        if(isset($formdata->gcathidden) && $formdata->gcathidden) {
            $where .= " AND gc.hidden = ? ";
            $params[] = ($formdata->gcathidden == -1) ? 0 : 1;
        }

        return array($where, $params);
    }
    
    public function has_applicable_action() {
        
        $data = json_decode($this->formsdata['gcat_config']);
        
        foreach($data as $key => $value) {
            if(is_object($value) && ((strpos($key, 'time') !== false) || (strpos($key, 'until') !== false))) {
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
        list($wheregrades, $gparams) = $this->gcat_selector_sql();
        list($wherecourse, $coursejoin, $cparams) = $this->courses_selector_sql();
        
        $params = array_merge($gparams, $cparams);

        $sql = "SELECT gc.*, gi.itemname, gi.idnumber, c.shortname, c.category
                    FROM {grade_categories} gc
                    JOIN {grade_items} gi ON gi.courseid = gc.courseid AND gi.itemtype = 'category' AND gi.iteminstance = gc.id
                    LEFT JOIN {grade_categories} gp ON gp.courseid = gc.courseid AND gp.id = gc.parent
                    LEFT JOIN {grade_items} gpi ON gpi.courseid = gc.courseid AND gpi.itemtype = 'category' AND gpi.iteminstance = gp.id
                    JOIN {course} c ON gi.courseid = c.id
                    $coursejoin
                WHERE $wheregrades $wherecourse  
                    ORDER BY c.category ASC, c.shortname ASC ";
                    
        return array($sql, $params);
    }

    public function get_updated_grade_item($grade_category, $data) {
        $grade_item = false;
    
        // grade item data saved with prefix "grade_item_"
        $itemdata = new stdClass();
        foreach ($data as $k => $v) {
            if (preg_match('/grade_item_(.*)/', $k, $matches)) {
                $itemdata->{$matches[1]} = $v;
            }
        }

        $hiddden = null;
        $hiddenuntil = null;
        if(isset($itemdata->hidden)) {
            $hidden = empty($itemdata->hidden) ? 0: $itemdata->hidden;
        }
        if(isset($itemdata->hiddenuntil)) {
            $hiddenuntil = empty($itemdata->hiddenuntil) ? 0: $itemdata->hiddenuntil;
        }
        unset($itemdata->hidden);
        unset($itemdata->hiddenuntil);

        $locked = null;
        $locktime = null;
        if(isset($itemdata->locked)) {
            $locked   = empty($itemdata->locked) ? 0: $itemdata->locked;
        }
        if(isset($itemdata->locktime)) {
            $locktime = empty($itemdata->locktime) ? 0: $itemdata->locktime;
        }
        unset($itemdata->locked);
        unset($itemdata->locktime);

        $convert = array('grademax', 'grademin', 'gradepass', 'multfactor', 'plusfactor', 'aggregationcoef', 'aggregationcoef2');
        foreach ($convert as $param) {
            if (property_exists($itemdata, $param)) {
                $itemdata->$param = unformat_float($itemdata->$param);
            }
        }
        if (isset($itemdata->aggregationcoef2)) {
            $itemdata->aggregationcoef2 = $itemdata->aggregationcoef2 / 100.0;
        }

        // When creating a new category, a number of grade item fields are filled out automatically, and are required.
        // If the user leaves these fields empty during creation of a category, we let the default values take effect
        // Otherwise, we let the user-entered grade item values take effect
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

        // Change weightoverride flag. Check if the value is set, because it is not when the checkbox is not ticked.
        $itemdata->weightoverride = isset($itemdata->weightoverride) ? $itemdata->weightoverride : 0;
        if ($grade_item->weightoverride != $itemdata->weightoverride && $grade_category->aggregation == GRADE_AGGREGATE_SUM) {
            // If we are using natural weight and the weight has been un-overriden, force parent category to recalculate weights.
            $grade_category->force_regrading();
        }
        $grade_item->weightoverride = $itemdata->weightoverride;

        $grade_item->outcomeid = null;

        if (!empty($data->grade_item_rescalegrades) && $data->grade_item_rescalegrades == 'yes') {
            $grade_item->rescale_grades_keep_percentage($grade_item_copy->grademin, $grade_item_copy->grademax, $grade_item->grademin,
                    $grade_item->grademax, 'gradebook');
        }

        // update hiding flag
        if ($hiddenuntil) {
            $grade_item->set_hidden($hiddenuntil, false);
        } else {
            if(isset($hidden)) {
                $grade_item->set_hidden($hidden, false);
            }
        }

        if(isset($locktime)) {
            $grade_item->set_locktime($locktime); // locktime first - it might be removed when unlocking
        }
        if(isset($locked)) {
            $grade_item->set_locked($locked, false, true);
        }
    
        return $grade_item;
    }
    
    
    public function apply_job_on_item($gcat, $data) {
        global $CFG, $COURSE, $DB; 
        
        $success = false;
        $extramsg = '';
        
        if($data && $gcat) {
            if (!$grade_category = grade_category::fetch(array('id'=>$gcat->id, 'courseid'=>$gcat->courseid))) {
                return array(false, $gcat->fullname, ' Category not existing');
            }
            $grade_category->apply_forced_settings();
            grade_category::set_properties($grade_category, $data);
            
            /// CATEGORY
            $success = $grade_category->update(); 

            /// GRADE IETM
            $grade_item = $this->get_updated_grade_item($grade_category, $data);

            if(!$grade_item->update()) {
                $extramsg .= ' Grade item NOT updated; ';
            }
        
            // set parent if needed
            if(isset($data->parentcategory)) {
                $data->parentcategory = trim($data->parentcategory);
                $parent = 0;
                if(($data->parentcategory != '') AND ($data->parentcategory != '?')) {
                    //we must check it exists
                    $parent = $DB->get_field('grade_categories', 'id', array('courseid'=>$gcat->courseid, 
                                                                                'fullname'=>$data->parentcategory));
                } else {
                    // empty or ? means the course category
                    $select = " courseid = :courseid AND depth = 1 AND parent IS NULL ";
                    $parent = $DB->get_field_select('grade_categories', 'id', $select, array('courseid'=>$gcat->courseid));
                }
                if($parent) {
                    if(!$grade_category->set_parent($parent, 'gradebook')) {
                        $extramsg .= ' Parent not set; ';
                    }
                } else {
                    $extramsg .= ' Non existing parent: '.$data->parentcategory;
                }
            }

            if(isset($data->insertfirst) && $data->insertfirst && $parent_category = $grade_category->get_parent_category()) {
                $sortorder = $parent_category->get_sortorder();
              //  $grade_category->move_after_sortorder($sortorder); 
            }
        
        
        }
    
        return array($success, $gcat->fullname, $extramsg);
    }
    
    public function after_execution_cleanup() {
        // just in case cleanup last course
        if(isset($this->currentcourse->id)) {
            grade_regrade_final_grades($this->currentcourse->id);
            parent::after_execution_cleanup();
        }
    }

}
