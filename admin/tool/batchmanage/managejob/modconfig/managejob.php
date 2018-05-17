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
class batchmanage_managejob_modconfig extends batchmanage_managejob_plugin {

    use batchmanage_mod_selector_sql; 
    
    /**
     * Constructor for the abstract plugin type class
     *
     * @param assign $assignment
     * @param string $type
     */
    public final function __construct($name) {
        $this->name = $name;
        $this->firstform = 'mod_selector';
        $this->nextmsg = get_string('mod_configurator', 'managejob_modconfig');
        $this->path = core_component::get_plugin_directory('managejob', $name);  
    }

    public function get_display_form($action) {
        $mform = parent::get_display_form($action);
        if($action == 'mod_config' && $this->refmod_cmid) {
            // get default data from reference module and set in this form 
            $mod_selector = json_decode($this->formsdata['mod_selector']);
            $cm = get_coursemodule_from_id($mod_selector->module, $this->refmod_cmid);
            $modinfo = $this->get_modinfodata($cm);
            if($modinfo) {
                $mform->set_data($modinfo);
            }
        }
        return $mform;
    }
    
    public function process_mod_selector($formdata) {
        $data = new stdClass();
        $usable = array('module', 'instancename', 'uselike', 'instanceid', 'coursemoduleid', 'insection', 
                            'indent', 'visible', 'groupmode', 'cmidnumber', 'adminrestricted');
        $data = new stdClass();
        foreach($formdata as $key => $value) {
            if(in_array($key, $usable)) {
                $data->{$key} = $value;
            }
        }

        return json_encode($data);
    }

    
    public function process_mod_config($formdata) {
        $data = new stdClass();
        foreach($formdata as $key => $value) {
            $last = substr($key, -6, 6);
            if($last == 'modify') {
                $first = substr($key, 0, -6);
                if($first == 'grade') {
                    $type = $formdata->modgrade_type;
                    if($type != 'none') { 
                        $data->modgrade_type = $formdata->modgrade_type;
                        $mode = 'modgrade_'.$type;
                        $data->$mode = $formdata->$mode;
                    }
                
                /*
                    if($data->modgrade_type == 'point') { 
                        $data->modgrade_type = $formdata->modgrade_type;
                        $data->modgrade_point = $formdata->modgrade_point;
                    } elseif ($data->modgrade_type == 'scale') { 
                        $data->modgrade_type = $formdata->modgrade_type;
                        $data->modgrade_scale = $formdata->modgrade_scale;
                    }
                
                */
                } else {
                    $data->{$first} = isset($formdata->{$first}) ? $formdata->{$first} : 0;
                    if(is_array($data->{$first})) {
                        if(!isset($data->{$first}['enabled'])) {
                            $data->{$first}['enabled'] = ($first != 'roles' && $first != 'capabilities') ? 0 : 1;
                        }
                    }
                }
            }
        }
        return json_encode($data);
    }
    
   
    
    public function process_action($action, $formdata) {
        $next = '';
        if(isset($formdata->formsdata__mod_configurator)) {
            $this->refmod_cmid = json_decode($formdata->formsdata__mod_configurator);
        }
        if($action == 'mod_selector') {
            $this->formsdata[$action] = $this->process_mod_selector($formdata);
            $next = 'mod_configurator';
            $this->nextmsg = get_string('mod_config', 'managejob_modconfig');
        } elseif($action == 'mod_configurator') {
            $this->refmod_cmid = $formdata->refmod_cmid;
            $this->formsdata['mod_configurator'] = json_encode($this->refmod_cmid);
            $next = 'mod_config';
            $this->nextmsg = get_string('courses_selector', 'tool_batchmanage');
        } elseif($action == 'mod_config') {
            $this->formsdata[$action] = $this->process_mod_config($formdata);
            $next = 'courses_selector';
            $this->nextmsg = get_string('reviewconfirm', 'tool_batchmanage');
        } else {
            return parent::process_action($action, $formdata);
        }
        
        return $next;
    }
    
    public function review_confirm_formsdata(& $mform, $action, $data, $innerform) {
        if($action == 'mod_selector') {
            $this->process_non_grouped_innerform($mform, $action, $data, $innerform);
        }
        if($action == 'mod_configurator') {
            $this->process_non_grouped_innerform($mform, $action, $data, $innerform);
        }

        if($action == 'mod_config') {
            $this->process_grouped_innerform($mform, $action, $data, $innerform);
        }
        parent::review_confirm_formsdata($mform, $action, $data, $innerform);
    }
    
    
    public function has_applicable_action() {
        $data = json_decode($this->formsdata['mod_config']);

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
        
        if(isset($data->modgrade_type) ||  isset($data->modgrade_point) || isset($data->modgrade_scale)) {
            $data->grade = array();
            if(isset($data->modgrade_type)) {
                $data->grade['modgrade_type'] = $data->modgrade_type;
            }
            if(isset($data->modgrade_type)) {
                $data->grade['modgrade_point'] = $data->modgrade_point;
            }
            if(isset($data->modgrade_scale)) {
                $data->grade['modgrade_scale'] = $data->modgrade_scale;
            }
        }
        

        if(isset($data->permission) && isset($data->capabilities) && isset($data->roles)) {
            if(is_object($data->capabilities)) {
                $data->capabilities = get_object_vars($data->capabilities);
                if(isset($data->capabilities['enabled'])) {
                    unset($data->capabilities['enabled']) ;
                }
            }
            if(is_object($data->roles)) {
                $data->roles = get_object_vars($data->roles);
                if(isset($data->roles['enabled'])) {
                    unset($data->roles['enabled']) ;
                }
            }
        }

        return $data;
    }
    
    
    public function combine_selectors_sql() {
        list($wheremod, $mparams) = $this->mod_selector_sql();
        list($wherecourse, $coursejoin, $cparams) = $this->courses_selector_sql();
        $modselector = json_decode($this->formsdata['mod_selector']);
        $moduletable = '{'.$modselector->module.'}';
        
        $params = array_merge($mparams, $cparams);

        $sql = "SELECT md.*, cm.id AS cmid, s.id AS sectid, s.section AS cwsection, c.id AS courseid, c.shortname, c.category
                    FROM $moduletable md
                    JOIN {course_modules} cm ON md.id = cm.instance AND md.course = cm.course AND cm.module = ?
                    JOIN {course_sections} s ON cm.section = s.id AND cm.course = s.course
                    JOIN {course} c ON cm.course = c.id
                    $coursejoin
                WHERE $wheremod  $wherecourse
                    ORDER BY c.category ASC, c.shortname ASC, md.name ASC ";

        return array($sql, $params);
    }

    public function cleanup_course_cache($courseid) {
        if(isset($this->currentcourse->id)) {
            // if set, we have a previous currentcourse different from this, just cleanup previous
            grade_regrade_final_grades($this->currentcourse->id);
        }
        
        return parent::cleanup_course_cache($courseid);
    }
    
    public function after_execution_cleanup() {
        // just in case cleanup last course
        if(isset($this->currentcourse->id)) {
            grade_regrade_final_grades($this->currentcourse->id);
            parent::after_execution_cleanup();
        }
    }
    
    public function apply_job_on_item($mod, $data) {
        global $CFG, $COURSE, $DB; 
        
        include_once($CFG->dirroot.'/course/modlib.php');
        
        $newdata = false;
        $newcaps = false;
        $success = false;
        $extramsg = '';
        
        
        // check there is somthing new in this particular course (faster if not updating with the existing data)
        foreach($data as $key => $value) {
            if(!isset($mod->$key) || $mod->$key != $value ) {
                $newdata = true;
            }
        }
        
        if(isset($data->permission) && 
                isset($data->capabilities) && $data->capabilities &&
                isset($data->roles) && $data->roles) {
            $newcaps = true;
        }
        
        $modselector = json_decode($this->formsdata['mod_selector']);
        if($newdata || $newcaps) {
            $course = $this->cleanup_course_cache($mod->courseid);

            // here we apply the configured data
            $cm = get_coursemodule_from_id($modselector->module, $mod->cmid, $course->id, false, MUST_EXIST);
            $moduleinfo = $this->get_modinfodata($cm);
            
            foreach($data as $key => $value) {
                if(isset($moduleinfo->$key) && is_array($moduleinfo->$key)) {
                    $value = get_object_vars($value);
                }
                $moduleinfo->$key = $value;
            }
            if(isset($moduleinfo->assignsubmission_file_enabled) && $moduleinfo->assignsubmission_file_enabled) {
                if(!isset($moduleinfo->assignsubmission_file_maxfiles)) {
                    $moduleinfo->assignsubmission_file_maxfiles = get_config('assignsubmission_file', 'maxfiles');
                }
                if(!isset($moduleinfo->assignsubmission_file_maxsizebytes)) {
                    $moduleinfo->assignsubmission_file_maxsizebytes = get_config('assignsubmission_file', 'maxbytes');
                }
            }
            
            if (isset($moduleinfo->gradepass)) {
                $moduleinfo->gradepass = unformat_float($moduleinfo->gradepass);
            }
            
            $mformclassname = 'mod_'.$modselector->module.'_mod_form';
            $mform = new $mformclassname($moduleinfo, $mod->cwsection, null, $course);
            $mform->set_data($moduleinfo);

            // adds fields in morm back to moduleinfo
            $rp = new ReflectionProperty($mformclassname, '_form');
            $rp->setAccessible(true);
            $innerform = $rp->getValue($mform);

            // add grade elements
            if ($innerform->elementExists('grade')) {
                $formgrade = $innerform->getElement('grade');
                if(get_object_vars($formgrade)) {
                    $modgrade = $formgrade->exportValue($data->modgrade);
                    $moduleinfo->grade = $modgrade['grade'];
                    $moduleinfo->grade_rescalegrades = $modgrade['grade_rescalegrades'];
                }
            } else {
                unset($moduleinfo->grade);
            }
            
            if($newdata) {
                list($newcm, $modinfo) = update_moduleinfo($cm, $moduleinfo, $course, $mform);
            }
            if($newcaps) {
                $success = $this->update_mod_capabilities($moduleinfo, $data->permission, $data->capabilities, $data->roles); 
            }
            
            if($newcm) {
                $success = true;
            }
        }
    
        return array($success, $mod->name, '');
    }
    
    
    function update_mod_capabilities($modinfo, $permission, $capabilities, $roles) {

        $context = $modinfo->context;
        $success = false;
        foreach($capabilities as $capability) {
            list($neededroles, $forbiddenroles) = get_roles_with_cap_in_context($context, $capability);
            foreach($roles as $roleid) {
                if(($permission == CAP_ALLOW) && in_array($roleid, $neededroles)) {
                    continue;
                }
                if(($permission == CAP_PROHIBIT) && in_array($roleid, $forbiddenroles)) {
                    // no need to add
                    continue;
                }
                $success = assign_capability($capability, $permission, $roleid, $context->id, true);
                $context->mark_dirty();
            }
            
        }
        return $success;
    }
    
    
    /**
    * Creates a the course_module object for defined mod. Error if not possible
    * @param object $cm a cm record form course_modules DB table
    * @return cm_info like object
    */
    function get_modinfodata($cm) {
        global $CFG, $DB;

        $mod_selector = json_decode($this->formsdata['mod_selector']);
        $context = context_module::instance($cm->id);
        // Check the moduleinfo exists.
        $data = $DB->get_record($mod_selector->module, array('id'=>$cm->instance), '*', MUST_EXIST);
        
        $section = ($mod_selector->insection >= 0) ?  $mod_selector->insection : $DB->get_field('course_sections', 'section', array('id'=>$cm->section));
        
        //list($cm, $context, $module, $data, $cw) = can_update_moduleinfo($cm);

        $data->coursemodule       = $cm->id;
        $data->section            = $section;  // The section number itself - relative!!! (section column in course_sections)
        $data->visible            = $cm->visible; //??  $cw->visible ? $cm->visible : 0; // section hiding overrides
        $data->cmidnumber         = $cm->idnumber;          // The cm IDnumber
        $data->groupmode          = groups_get_activity_groupmode($cm); // locked later if forced
        $data->groupingid         = $cm->groupingid;
        $data->course             = $cm->course;
        $data->module             = $cm->module;
        $data->modulename         = $mod_selector->module;
        $data->instance           = $cm->instance;
        $data->update             = true;
        $data->completion         = $cm->completion;
        $data->completionview     = $cm->completionview;
        $data->completionexpected = $cm->completionexpected;
        $data->completionusegrade = is_null($cm->completiongradeitemnumber) ? 0 : 1;
        $data->showdescription    = $cm->showdescription;
        $data->tags               = core_tag_tag::get_item_tags_array('core', 'course_modules', $cm->id);
        $data->context            = $context;
        
        if (!empty($CFG->enableavailability)) {
            $data->availabilityconditionsjson = $cm->availability;
        }

        if (plugin_supports('mod', $data->modulename, FEATURE_MOD_INTRO, true)) {
            $draftid_editor = file_get_submitted_draft_itemid('introeditor');
            $currentintro = file_prepare_draft_area($draftid_editor, $context->id, 'mod_'.$data->modulename, 'intro', 0, array('subdirs'=>true), $data->intro);
            $data->introeditor = array('text'=>$currentintro, 'format'=>$data->introformat, 'itemid'=>$draftid_editor);
        }
        
        if (plugin_supports('mod', $data->modulename, FEATURE_ADVANCED_GRADING, false)
                and has_capability('moodle/grade:managegradingforms', $context)) {
            require_once($CFG->dirroot.'/grade/grading/lib.php');
            $gradingman = get_grading_manager($context, 'mod_'.$data->modulename);
            $data->_advancedgradingdata['methods'] = $gradingman->get_available_methods();
            $areas = $gradingman->get_available_areas();

            foreach ($areas as $areaname => $areatitle) {
                $gradingman->set_area($areaname);
                $method = $gradingman->get_active_method();
                $data->_advancedgradingdata['areas'][$areaname] = array(
                    'title'  => $areatitle,
                    'method' => $method,
                );
                $formfield = 'advancedgradingmethod_'.$areaname;
                $data->{$formfield} = $method;
            }
        }

        if ($items = grade_item::fetch_all(array('itemtype'=>'mod', 'itemmodule'=>$data->modulename,
                                                'iteminstance'=>$data->instance, 'courseid'=>$cm->course))) {
            // Add existing outcomes.
            foreach ($items as $item) {
                if (!empty($item->outcomeid)) {
                    $data->{'outcome_' . $item->outcomeid} = 1;
                } else if (!empty($item->gradepass)) {
                    $decimalpoints = $item->get_decimals();
                    $data->gradepass = format_float($item->gradepass, $decimalpoints);
                }
            }

            // set category if present
            $gradecat = false;
            foreach ($items as $item) {
                if ($gradecat === false) {
                    $gradecat = $item->categoryid;
                    continue;
                }
                if ($gradecat != $item->categoryid) {
                    //mixed categories
                    $gradecat = false;
                    break;
                }
            }
            if ($gradecat !== false) {
                // do not set if mixed categories present
                $data->gradecat = $gradecat;
            }
        }    

        // If and assignment, load plugin config
        if($data->modulename == 'assign') {
            $select = "assignment = ? AND name = 'enabled' AND " . 
                        $DB->sql_compare_text('value') . " = 1 ";
            if($enabled = $DB->get_records_select('assign_plugin_config', $select, array('assignment'=>$cm->instance))) {
                foreach($enabled as $plugin) {
                    if($configs = $DB->get_records('assign_plugin_config', array('assignment'=>$cm->instance, 
                                                                                'plugin' => $plugin->plugin,
                                                                                'subtype' => $plugin->subtype))) {
                        foreach($configs as $config) {
                            $key = $config->subtype.'_'.$config->plugin.'_'.$config->name;
                            $data->$key = $config->value;
                        }
                    }
                }
                
            }
        }
        
        return $data;
    }
    
    
    
}
