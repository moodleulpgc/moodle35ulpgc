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
class batchmanage_managejob_qcatdelete extends batchmanage_managejob_plugin {

    /**
     * Constructor for the abstract plugin type class
     *
     * @param assign $assignment
     * @param string $type
     */
    public final function __construct($name) {
        $this->name = $name;
        $this->firstform = 'qcategory_selector';
        $this->nextmsg = get_string('courses_selector', 'tool_batchmanage');
        $this->path = core_component::get_plugin_directory('managejob', $name);  
    }

    
    public function process_qcategory_selector($formdata) {
        $data = new stdClass();
        foreach($formdata as $key => $value) {
            $first = substr($key, 0, 9); 
            if($first == 'qcategory') {
                $data->{$key} = $value;
            }
        }

        return json_encode($data);
    }

    
    
    public function process_action($action, $formdata) {
        $next = '';
        if($action == 'qcategory_selector') {
            $this->formsdata[$action] = $this->process_qcategory_selector($formdata);
            $next = 'courses_selector';
            $this->nextmsg = get_string('reviewconfirm', 'tool_batchmanage');
        } else {
            return parent::process_action($action, $formdata);
        }
        
        return $next;
    }
    
    public function review_confirm_formsdata(& $mform, $action, $data, $innerform) {
        if($action == 'qcategory_selector') {
            $this->process_non_grouped_innerform($mform, $action, $data, $innerform);
        }
        parent::review_confirm_formsdata($mform, $action, $data, $innerform);
    }
    
    
    public function has_applicable_action() {
        $data = json_decode($this->formsdata['qcategory_selector']);

        return $data;
    }

    public function qcategory_selector_sql() {
        global $DB;
        
        $formdata = json_decode($this->formsdata['qcategory_selector']);
        $params = array();    
        
        $where = ' 1 ';
        if(isset($formdata->qcategoryname) && $formdata->qcategoryname) {
            if(strtolower($formdata->qcategoryname) == 'null') {
                $where .= " AND qc.name IS NULL ";
            } else {
                if ($formdata->qcategoryuselike) {
                    $where .= ' AND '.$DB->sql_like('qc.name', '?');
                } else {
                    $where .= " AND qc.name = ? ";
                }
                $params[] =  $formdata->qcategoryname;
            }
        }

        
        return array($where, $params);
    }
    
    public function combine_selectors_sql() {
        list($whereqcategory, $qparams) = $this->qcategory_selector_sql();
        list($wherecourse, $coursejoin, $cparams) = $this->courses_selector_sql();
        
        $params = array(CONTEXT_COURSE);
        $params = array_merge($params, $qparams, $cparams);
        

        $sql = "SELECT qc.*, c.shortname, c.category
                    FROM {course} c
                    JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = ?
                    JOIN {question_categories} qc ON qc.contextid = ctx.id
                    
                    $coursejoin
                WHERE $whereqcategory $wherecourse 
                    ORDER BY c.category ASC, c.shortname ASC ";
                    
        return array($sql, $params);
    }

    public function apply_job_on_item($category, $data) {
        global $CFG, $COURSE, $DB; 
        
        $success = false;
        
        if($data && $category) {
            $questionids = $DB->get_records_select_menu('question',
                                        'category = ? AND (parent = 0 OR parent = id)', array($category->id), '', 'id,1');
            $childs = $DB_>record_exists('question_categories', array("parent" => $category->id));
            
            question_remove_stale_questions_from_category($category->id);
                                        
            if($questionids || $childs) {
                if($data->qcategoryforcedelete) {
                    if(!$savecat = $DB->get_field('question_categories', 'id', array('name'=>$data->qcategorysaved, 'contextid'=>$category->contextid))) {
                        $cat = new stdClass();
                        $cat->parent = 0;
                        $cat->contextid = $category->contextid;
                        $cat->name = $data->qcategorysaved;
                        $cat->info = '';
                        $cat->infoformat = FORMAT_MOODLE;
                        $cat->sortorder = 999;
                        $cat->stamp = make_unique_id_code();
                        $savecat = $DB->insert_record("question_categories", $cat);
                    }
                    if($savecat) {
                
                        question_move_questions_to_category(array_keys($questionids), $savecat);
                            
                        /// Send the children categories to live with their grandparent
                        $DB->set_field('question_categories', "parent", $category->parent, array("parent" => $category->id));

                        /// Finally delete the category itself
                        $success = $DB->delete_records("question_categories", array("id" => $category->id));
                    }
                }
            } else {
                $success = $DB->delete_records("question_categories", array("id" => $category->id));
            }
        }
    
        return array($success, $category->name, '');
    }
    
}
