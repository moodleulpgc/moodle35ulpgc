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
        require_once($CFG->dirroot."/local/supervision/supervisionwarning.php");
        $this->warningplugins = array();
        if($plugins = core_component::get_plugin_list_with_file('supervisionwarning', 'locallib.php', true)) {
            ksort($plugins);
            foreach($plugins as $name => $path ) {
                if($enabled = get_config('supervisionwarning_'.$name, 'enabled')) {
                    $pluginclass = 'supervisionwarning_' . $name;
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
        if (!has_capability('block/supervision:viewwarnings', $page->context)) {
            return false;
        }

        return parent::user_can_addto($page);
    }

    protected function formatted_contents($output) {

        $content = '';
        if($content = parent::formatted_contents($output)) {
            $canmanage = has_capability('block/supervision:manage', $this->page->context);
            $canedit = has_capability('block/supervision:editwarnings', $this->page->context);
            $enrolled = false;
            if($ccontext = $this->page->context->get_course_context(false)) {
                $enrolled = is_enrolled($ccontext);
            }
            if(!$canmanage && !($canedit && !$enrolled)) {
                $messageclass = ' warning_block ';
                if(strpos($content, get_string('nowarnings', 'block_supervision')) !== false) {
                    $messageclass = ' warning_none ';
                }
                $content = html_writer::div($content, $messageclass);
            }
        }
        return $content;
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
        require_once($CFG->dirroot."/blocks/supervision/locallib.php");

        $pagetype = $this->page->pagetype;
        //$course = $this->page->course;
        
        $course = local_ulpgccore_get_course_details($this->page->course);
        
        
        $systemcontext = context_system::instance();
        $context = $this->page->context;

        $canviewreports = has_capability('block/supervision:viewwarnings', $context);

        $cansupervise = false;
        if($showwarnings = supervision_supervisor_warningtypes($USER->id)) {
            $cansupervise = true;
        }

        if(!$canviewreports AND !$showwarnings) {
            return $this->content = ''; // students & others fast abort
        }

        $canmanage = has_capability('block/supervision:manage', $context);

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
                $url = new moodle_url('blocks/supervision/department.php', array('id'=>$deptid));
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
            $url = new moodle_url('/blocks/supervision/supervisors.php', array('cid'=>$course->id, 'type'=>$pagetype));
            $this->content->items[] = $OUTPUT->action_link($url, get_string('editpermissions', 'block_supervision'));
            $this->content->icons[] = $OUTPUT->pix_icon('i/checkpermissions', '').'&nbsp;';

            $url = new moodle_url('/blocks/supervision/holidays.php', array('cid'=>$course->id, 'type'=>$pagetype));
            $this->content->items[] = $OUTPUT->action_link($url, get_string('editholidays', 'block_supervision'));
            $this->content->icons[] = $OUTPUT->pix_icon('i/calendar', '').'&nbsp;';
            
            $url = new moodle_url('/admin/settings.php', array('section'=>'blocksettingsupervision'));
            $this->content->items[] = $OUTPUT->action_link($url, get_string('editconfig', 'block_supervision'));
            $this->content->icons[] = $OUTPUT->pix_icon('t/edit', '').'&nbsp;';
            
        }

        return $this->content;
    }


    // cron function, used to collect supervision warnings
    function cron() {
        global $CFG;
    /// We are going to measure execution times
        $starttime =  microtime();

    /// And we have one initial $status
        $status = true;

    /// We require some stuff
        require_once('locallib.php');
        require_once($CFG->libdir .'/statslib.php');
        $config = get_config('block_supervision');
        
        $config->enabledepartments = 0;
        $config->enablefaculties = 0;


        if (!empty($config->enablestats) and empty($CFG->disablestatsprocessing)) {
            // check we're not before our runtime
            // calculate scheduled time

            $timetocheck  = time()-1;

            /// checks for once a day except if in debugging mode
            if(!debugging('', DEBUG_ALL)) {
                $timetocheck  = stats_get_base_daily() + $config->statsruntimestarthour*60*60 + $config->statsruntimestartminute*60;
                // Note: This will work fine for sites running cron each 4 hours or less (hoppefully, 99.99% of sites). MDL-16709
                // check to make sure we're due to run, at least 20 hours after last run
                if (isset($config->lastexecution) && ((time() - 20*60*60) < $config->lastexecution)) {
                    mtrace("...preventing stats to run, last execution was less than 20 hours ago.");
                    return false;
                // also check that we are a max of 4 hours after scheduled time, stats won't run after that
                } else if (time() > $timetocheck + 4*60*60) {
                    mtrace("...preventing stats to run, more than 4 hours since scheduled time.");
                    return false;
                }
            }

            mtrace(" ... time to check ".userdate($timetocheck). '    '.$timetocheck  );
            mtrace(' ... last execution '.userdate($config->lastexecution). ' '. $config->lastexecution  );

            // if it's not our first run, just return the most recent.
            if(isset($config->lastexecution)) {
                $lastexecution = $config->lastexecution;
                if ($lastexecution >= $timetocheck) {
                    // if already run, do not repeat again
                    return false;
                }
            } else {
                // first time, we need a starttime: a month ago
                $lastexecution = $timetocheck - 2*30*24*60*60;
            }

            if (time() > $timetocheck) {

                // we have ULPGC tables & data
                if(get_config('local_ulpgccore', 'version')){
                    if($config->enabledepartments) {
                        mtrace(" assigning supervisor roles at departments");
                        supervision_ulpgc_update_supervisors('department');
                    }
                    if($config->enablefaculties) {
                        mtrace(" assigning supervisor roles at faculties");
                        supervision_ulpgc_update_supervisors('category');
                    }
                }

                mtrace("  ... starting supervision stats");
                foreach($this->warningplugins as $name => $warning) {
                    $pluginconfig = get_config('supervisionwarning_'.$name);
                    if(!empty($pluginconfig->enabled)) {
                        mtrace('Starting supervision stats '.$name);
                        $warning->collect_stats($timetocheck, $lastexecution);
                    }
                }
                mtrace('Mailing supervision warnings ...');
                if(!empty($config->enablemail)) {
                    supervision_warnings_mailing($timetocheck, $lastexecution);
                }
                mtrace('Collecting supervision stats took  '. microtime_diff($starttime, microtime()) . ' seconds');

                set_config('lastexecution', $timetocheck, 'block_supervision'); /// Grab this execution as last one
            }
        }

      /// And return $status
          return $status;
    }

}


