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
 * This file contains block_supervison class
 *
 * @package   block_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_supervision extends block_list {

    function init() {
        global $CFG;
        $this->title = get_string('pluginname', 'block_supervision');
        $this->version = 2012081700;

        //load and instantiate all warning plugins
        //require_once($CFG->dirroot."/local/supervision/supervisionwarning.php");
        $this->warningplugins = array();
        if($plugins = core_component::get_plugin_list_with_file('supervisionwarning', 'locallib.php', true)) {
            ksort($plugins);
            foreach($plugins as $name => $path ) {
                if($enabled = get_config('supervisionwarning_'.$name, 'enabled')) {
                    $pluginclass = '\local_supervision\warning_' . $name;
                    $this->warningplugins[$name] = new $pluginclass();
                }
            }
        }
    }

    function has_config() {
        return true;
    }

    /**
     * All multiple instances of this block
     * @return bool Returns false
     */
    function instance_allow_multiple() {
        return false;
    }

    /**
     * Set the applicable formats for this block to all
     * @return array
     */
    function applicable_formats() {
        return array('site-index' => true, 'my'=>true, 'course' => true);
    }

    /**
     * Allow the user to configure a block instance
     * @return bool Returns true
     */
    function instance_allow_config() {
        return false;
    }

    /**
     * The navigation block cannot be hidden by default as it is integral to
     * the navigation of Moodle.
     *
     * @return false
     */
    function  instance_can_be_hidden() {
        return true;
    }

    function user_can_addto($page) {
        // Don't allow people to add the block if they can't even use it
        if (!has_capability('local/supervision:viewwarnings', $page->context)) {
            return false;
        }

        return parent::user_can_addto($page);
    }
    
    
    function get_content() {
        global $CFG, $USER, $DB, $OUTPUT;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content = '';
        }

        include_once($CFG->dirroot."/local/sinculpgc/lib.php"); 
        include_once($CFG->dirroot."/local/ulpgccore/lib.php"); 
        require_once($CFG->dirroot."/local/supervision/locallib.php");

        $pagetype = $this->page->pagetype;
        
        $course = local_ulpgccore_get_course_details($this->page->course);
        
        
        $systemcontext = context_system::instance();
        $context = $this->page->context;

        $canviewreports = has_capability('local/supervision:viewwarnings', $context);

        $cansupervise = false;
        if($showwarnings = supervision_supervisor_warningtypes($USER->id)) {
            $cansupervise = true;
        }

        if(!$canviewreports AND !$showwarnings) {
            return $this->content = ''; // students & others fast abort
        }

        $canmanage = has_capability('local/supervision:manage', $context);

        $icon  = $OUTPUT->pix_icon('i/course', '').'&nbsp;';

        $categories = supervision_get_reviewed_itemnames($USER->id, 'category');
        if($categories) {
            $this->content->items[] = get_string('coursesreview', 'block_supervision');
            $this->content->icons[] = '';
            foreach($categories as $catid => $catname) {
                $url = new moodle_url('/course/index.php', array('categoryid'=>$catid));
                $this->content->items[] = $OUTPUT->action_link($url, $catname);
                $this->content->icons[] = $icon;
            }
        }

        $departments = supervision_get_reviewed_itemnames($USER->id, 'department');
        if($departments) {
            $this->content->items[] = get_string('departmentreview', 'block_supervision');
            $this->content->icons[] = '';
            foreach($departments as $deptid => $deptname) {
                $url = new moodle_url('local/supervision/department.php', array('id'=>$deptid));
                $this->content->items[] = $OUTPUT->action_link($url, $deptname);
                $this->content->icons[] = $icon;
            }
        }
       
        if($canmanage) {
            $showwarnings = array_keys($this->warningplugins);
        } elseif($cansupervise) {
            // gets warning types from permissions table
            //$showwarnings for supervisers already got
        } else {
            // gets warning types from warnings table
            $thiscourse = 0;
            if((strpos($pagetype, 'course-view') !== false) && $course->credits) {
                $thiscourse = $course->id;
            }
            $showwarnings = supervision_user_haswarnings($USER->id, $thiscourse);
        }
     
        if($showwarnings) {
            $this->content->items[] = get_string('tasksreview', 'block_supervision');
            $this->content->icons[] = '';
            foreach($showwarnings as $key => $warningname) {
                $warning = $this->warningplugins[$warningname];
                $url = new moodle_url('/report/supervision/index.php', array('id'=>$course->id, 'warning'=>$warningname, 'logformat'=>'showashtml', 'chooselog'=>1));
                $this->content->items[] = $OUTPUT->action_link($url, get_string('pluginname', 'supervisionwarning_'.$warningname));
                $this->content->icons[] = $warning->get_icon().'&nbsp;';
            }
        } else {
            $this->content->items[] = get_string('nowarnings', 'block_supervision');
            $this->content->icons[] = '&nbsp;&nbsp;&nbsp;';
        }
        if($canmanage) {
            $this->content->items[] = '<hr />'.get_string('management', 'block_supervision');
            $this->content->icons[] = '';
            $url = new moodle_url('/local/supervision/supervisors.php', array('cid'=>$course->id));
            $this->content->items[] = $OUTPUT->action_link($url, get_string('editpermissions', 'local_supervision'));
            $this->content->icons[] = $OUTPUT->pix_icon('i/checkpermissions', '').'&nbsp;';

            $url = new moodle_url('/local/supervision/holidays.php', array('cid'=>$course->id));
            $this->content->items[] = $OUTPUT->action_link($url, get_string('editholidays', 'local_supervision'));
            $this->content->icons[] = $OUTPUT->pix_icon('i/calendar', '').'&nbsp;';
            
            $url = new moodle_url('/admin/settings.php', array('section'=>'local_supervision_settings'));
            $this->content->items[] = $OUTPUT->action_link($url, get_string('supervisionsettings', 'local_supervision'));
            $this->content->icons[] = $OUTPUT->pix_icon('t/edit', '').'&nbsp;';
        }

        return $this->content;
    }


    // cron function 
    function cron() {
        global $CFG;
    
    }

}


