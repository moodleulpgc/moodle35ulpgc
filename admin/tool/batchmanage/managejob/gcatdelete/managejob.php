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
class batchmanage_managejob_gcatdelete extends batchmanage_managejob_plugin {

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
                $data->{$key} = $value;
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
        
        $data = true; //json_decode($this->formsdata['gcat_config']);
        

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

    public function apply_job_on_item($gcat, $data) {
        global $CFG, $COURSE, $DB; 
        
        $success = false;
        
        if($gcat && $data) {
            $this->activecourseid = $gcat->courseid;
        
             require_once($CFG->dirroot.'/grade/lib.php');
            // security checks 
            $gtree = new grade_tree($gcat->courseid, false, false);
            //extra security check - the grade item must be in this tree
            if (!$element = $gtree->locate_element('cg'.$gcat->id)) {
                return array(false, $gcat->fullname, ' No item ');
            }
            $object = $element['object'];
            if($success = $object->delete('grade/report/grader/category')) {
                if(!isset($this->currentcourse)) {
                    $this->currentcourse = new stdClass();
                    $this->currentcourse->id = $gcat->courseid;
                }
            }
        }
    
        return array($success, $gcat->fullname, '');
    }
    
    public function after_execution_cleanup() {
        // just in case cleanup last course
        if(isset($this->currentcourse->id)) {
            grade_regrade_final_grades($this->currentcourse->id);
            parent::after_execution_cleanup();
        }
    }
    
}
