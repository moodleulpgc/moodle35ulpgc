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
class batchmanage_managejob_gitemmove extends batchmanage_managejob_plugin {

    /**
     * Constructor for the abstract plugin type class
     *
     * @param assign $assignment
     * @param string $type
     */
    public final function __construct($name) {
        $this->name = $name;
        $this->firstform = 'gitem_selector';
        $this->nextmsg = get_string('target_selector', 'managejob_gitemmove');
        $this->path = core_component::get_plugin_directory('managejob', $name);  
    }

    
    public function process_gitem_selector($formdata) {
        $data = new stdClass();
        foreach($formdata as $key => $value) {
            $first = substr($key, 0, 5); 
            if($first == 'gitem') {
                $data->{$key} = $value;
            }
        }

        return json_encode($data);
    }

    public function process_target_selector($formdata) {
    
    
        $data = new stdClass();
        foreach($formdata as $key => $value) {
            $first = substr($key, 0, 6); 
            if($first == 'target') {
                $data->{$key} = $value;
            }
        }

        return json_encode($data);
    
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
    /*
        if(!$formdata) {
            $url = new moodle_url('/admin/tool/batchmanage/index.php', array('job'=>'gitemmove'));
            redirect($url, get_string('emptyform', 'tool_batchmanage'));
        }
    */
        $next = '';
        if($action == 'gitem_selector') {
            $this->formsdata[$action] = $this->process_gitem_selector($formdata);
            $next = 'target_selector';
            $this->nextmsg = get_string('courses_selector', 'tool_batchmanage');
        } elseif($action == 'target_selector') {
            $this->formsdata[$action] = $this->process_target_selector($formdata);
            $next = 'courses_selector';
            $this->nextmsg = get_string('reviewconfirm', 'tool_batchmanage');
        } else {
            return parent::process_action($action, $formdata);
        }
        
        return $next;
    }
    
    public function review_confirm_formsdata(& $mform, $action, $data, $innerform) {
        if($action == 'gitem_selector') {
            $this->process_non_grouped_innerform($mform, $action, $data, $innerform);
        }
        if($action == 'target_selector') {
            $this->process_non_grouped_innerform($mform, $action, $data, $innerform);
        }
        parent::review_confirm_formsdata($mform, $action, $data, $innerform);
    }
    
    
    public function has_applicable_action() {
        $data = json_decode($this->formsdata['target_selector']);

        return $data;
    }

    public function gitem_selector_sql() {
        global $DB;
        
        $formdata = json_decode($this->formsdata['gitem_selector']);
        $params = array();  
        $where = '';
        
        if(isset($formdata->gitemname) && $formdata->gitemname) {
            if ($formdata->gitemuselike) {
                $where .= ' AND '.$DB->sql_like('gi.itemname', '?');
            } else {
                $where .= " AND gi.itemname = ? ";
            }
            $params[] =  $formdata->gitemname;
        }
        
        
        if(isset($formdata->gitemidnumbers) && $formdata->gitemidnumbers) {
            $formdata->gitemidnumbers = str_replace(array(' ', '|', "\t"), ',', trim($formdata->gitemidnumbers));
            if($idnumbers = explode(',', $formdata->gitemidnumbers)) {
                foreach($idnumbers as $key => $value) {
                    if(!trim($value)) {
                        unset($idnumbers[$key]);
                    }
                }
                if($idnumbers) {
                    list($insql, $inparams) = $DB->get_in_or_equal($idnumbers);
                    $where .= " AND gi.idnumber $insql ";
                    $params = array_merge($params, $inparams);
                }
            }
        }

        if(isset($formdata->gitemparentname) && $formdata->gitemparentname) {
            $where .= " AND gc.fullname = ? ";
            $params[] =  $formdata->gitemparentname;
        }

        if(isset($formdata->gitemparentidnumber) && $formdata->gitemparentidnumber) {
            $where .= " AND gci.idnumber = ? ";
            $params[] =  $formdata->gitemparentidnumber;
        }

        if(isset($formdata->gitemmodule) && $formdata->gitemmodule) {
            $where .= " AND gi.itemmodule = ? ";
            $params[] = $formdata->gitemmodule;
        }
        
        if(isset($formdata->gitemhidden) && $formdata->gitemhidden) {
            $where .= " AND gi.hidden = ? ";
            $params[] =  ($formdata->gitemhidden == -1) ? 0 : 1;
        }
        
        if(isset($formdata->gitemnoncat) && $formdata->gitemnoncat) {
            $where .= " AND gi.categoryid = gcourse.iteminstance ";
        }
        return array($where, $params);
    }
    
    
    public function gcat_selector_sql() {
        global $DB;
        
        $formdata = json_decode($this->formsdata['target_selector']);
        $params = array();    
        $where = '';
        
        if(isset($formdata->targetgcfullname) && $formdata->targetgcfullname) {
            $where .= " AND gc.fullname = ? ";
            $params[] =  $formdata->targetgcfullname;
        }

        if(isset($formdata->targetgitemidnumber) && $formdata->targetgitemidnumber) {
            $where .= " AND gi.idnumber = ? ";
            $params[] =  $formdata->targetgitemidnumber;
        }

        if(isset($formdata->targetgitemname) && $formdata->targetgitemname) {
            $where .= " AND gi.itemname = ? ";
            $params[] = $formdata->targetgitemname;
        }
        

        return array($where, $params);
    }
    
    
    public function combine_selectors_sql() {
        list($wheregrades, $gparams) = $this->gitem_selector_sql();
        list($wherecourse, $coursejoin, $cparams) = $this->courses_selector_sql();

        $gcatdata = json_decode($this->formsdata['target_selector']);
        $sortdir = $gcatdata->targetinsertlast ? 'ASC' : 'DESC';
        
        $params = array_merge($gparams, $cparams);

        $sql = "SELECT gi.*, gc.fullname, gci.iteminstance AS parentitem, gcourse.iteminstance AS gicourse,  c.shortname, c.category
                    FROM {grade_items} gi
                    JOIN {grade_categories} gc ON gi.courseid = gc.courseid AND gc.id = gi.categoryid
                    JOIN {grade_items} gci ON gi.courseid = gci.courseid AND ((gci.itemtype = 'category') OR (gci.itemtype = 'course')) AND gci.iteminstance = gi.categoryid
                    JOIN {grade_items} gcourse ON gcourse.courseid  = gi.courseid AND gcourse.itemtype = 'course'
                    JOIN {course} c ON gi.courseid = c.id
                    $coursejoin
                WHERE  ((gi.itemtype = 'mod') OR (gi.itemtype = 'manual')) $wheregrades $wherecourse  
                    ORDER BY c.category ASC, c.shortname ASC, gi.sortorder $sortdir ";
                    
        return array($sql, $params);
    }

    public function get_target_grade_category($gtree, $data) {
        global $DB;     
        
        list($wheregrades, $params) = $this->gcat_selector_sql();
        $params = array_merge(array($gtree->courseid), $params); 
        $sql = "SELECT gc.id, gc.fullname, gi.itemname, gi.idnumber
                    FROM {grade_categories} gc
                    JOIN {grade_items} gi ON gc.courseid = gi.courseid AND gi.itemtype = 'category' AND gi.iteminstance = gc.id
                WHERE gc.courseid = ? $wheregrades 
                    ORDER BY gc.depth ASC, gi.sortorder ASC";
        
        $gcat = $DB->get_record_sql($sql, $params);
        
        if(!$gcat || !$element = $gtree->locate_element('cg'.$gcat->id)) {
            return false;
        }

        // TODO // set an insert point in the middle
        $after = $element['object'];
        $after->insertpoint = $after->get_sortorder();
        if($data->targetinsertlast) {
            if($children = $after->get_children()) {
                $last = end($children)['object'];
                $after->insertpoint = $last->get_sortorder();
            }
        }
        
        return $after; 
    }
    
    
    public function apply_job_on_item($gitem, $data) {
        global $CFG;
        
        $success = false;
        
        require_once($CFG->dirroot.'/grade/lib.php');
        
        // security checks 
        $gtree = new grade_tree($gitem->courseid, false, false);
        //extra security check - the grade item must be in this tree
        if (!$element = $gtree->locate_element('ig'.$gitem->id)) {
            return array(false, $gitem->itemname, ' No item ');
        }
        $gitemobj = $element['object'];
        if(!$target = $this->get_target_grade_category($gtree, $data)) {
            return array(false, $gitem->itemname, ' No target cat');
        }
        
        $tname = '';
        if($gitemobj && $target) {
            $success = $gitemobj->set_parent($target->id);
            $tname = $success ? $target->get_name() : ' Cannot set parent ';

            if($success)  {
                $gitemobj->move_after_sortorder($target->insertpoint);
            }
        }
    
        return array($success, $gitem->itemname, $tname);
    }
    
    public function after_execution_cleanup() {
        // just in case cleanup last course
        if(isset($this->currentcourse->id)) {
            grade_regrade_final_grades($this->currentcourse->id);
            parent::after_execution_cleanup();
        }
    }
    
}
