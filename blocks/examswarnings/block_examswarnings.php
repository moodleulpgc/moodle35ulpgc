<?php
/**
 * This file contains block_examswarnings class
 *
 * @package   block_examswarnings
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//require_once('locallib.php');


class block_examswarnings extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_examswarnings');
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
     * Serialize and store config data
     */
    function instance_config_save($data, $nolongerused = false) {
        global $DB;
        
        $config = empty($data) ? null : clone($data);
        
        parent::instance_config_save($config, $nolongerused);
    }

    function instance_delete() {
        global $DB;
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_examswarnings');
        return true;
    }
    
    /**
     * The navigation block cannot be hidden by default as it is integral to
     * the navigation of Moodle.
     *
     * @return false
     */
    function  instance_can_be_hidden() {
        return false;
    }

    function user_can_addto($page) {
        // Don't allow people to add the block if they can't even use it
        if (!has_capability('block/examswarnings:view', $page->context)) {
            return false;
        }

        return parent::user_can_addto($page);
    }



    function get_content() {
        global $CFG, $USER, $DB, $OUTPUT;
        

        if($this->content !== NULL) {
            return $this->content;
        }

        
        //print_object($this->config);        
        
        require_once($CFG->dirroot.'/blocks/examswarnings/locallib.php');

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (empty($this->instance)) {
           return $this->content = '';
        }
        // if there is no examregistrar, return empty
        if(!$moduleid = $DB->get_field('modules', 'id', array('name'=>'examregistrar', 'visible'=>1)) || 
            !$DB->record_exists('examregistrar_elements', null)) {
            return $this->content;
        }
        
        $pagetype = $this->page->pagetype;
        $course = $this->page->course;
        $systemcontext = context_system::instance();
        $context = $this->page->context;

        $canbook = has_capability('mod/examregistrar:book', $context);
        $cansubmit = has_capability('mod/examregistrar:submit', $context);
        $canmanage = has_capability('block/examswarnings:manage', $context);
        
        $config = empty($this->config) ? get_config('block_examswarnings') : $this->config;
        
        if(!isset($config->primaryreg)) {
            return $this->content = '';
        }
        
        list($period, $session, $extra, $check, $lagdays) = examswarnings_get_sessiondata($config);
        
        if(!$period) {
            return $this->content = '';
        }
        
        $now = time();

        $warnings = array();
        $upcomings = array();
        $reminders = array();
        $roomcalls = array();
        
        if(is_siteadmin()) {
            if(debugging('', DEBUG_DEVELOPER)) {
                $o = new stdClass();
                $o->category = 6;
                $o->programme = '4036';
                $o->shortname = '46051';
                $o->roomidnumber = 'A-27';
                $warnings = array($o);
                $upcomings = array($o);
                $reminders = array($o);
                $roomcalls = array($o);
            }
        } else {
            if($canbook && (strtotime(" - $lagdays days ",  $session->examdate) > $now) &&
            ($session->examdate <  $check)) {
                $warnings = examswarnings_notappointedexams($period, $session, $extra, $this->config); // for students
            }

            if(($session->examdate > $now) && ($session->examdate <  $check)) {
                if($canbook) {
                    $upcomings = examswarnings_upcomingexams($period, $session); // for students
                }
                if($cansubmit) {
                    $reminders = examswarnings_reminder_upcomingexams($period, $session); // for teachers
                    $roomcalls = examswarnings_roomcall_upcomingexams($period, $session, $this->config); // for tecahers/room staff
                }
            }
        }

        if($warnings) {
            $this->content->items[] = html_writer::span(get_string('warningduedate', 'block_examswarnings', count($warnings)), 'warning examwarning');
            $this->content->icons[] = '';

            foreach($warnings as $warning) {
                $boardid = $DB->get_field('course', 'id', array('category'=>$warning->category, 'shortname'=>'Coord-'.$warning->programme));
                if($cmid = $DB->get_field('course_modules', 'id', array('course'=>$boardid, 'module'=>$moduleid, 'idnumber'=>'examreg'))) {
                    $url = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cmid, 'tab'=>'booking'));
                    $this->content->items[] = html_writer::link($url, $warning->shortname, array('class'=>'warning'));
                } else {
                    $this->content->items[] = html_writer::span($warning->shortname, 'warning');
                }
                $this->content->icons[] = $OUTPUT->pix_icon('i/risk_xss', '', 'moodle', array('class'=>'icon'));
            }
        }

        $icon = $OUTPUT->pix_icon('i/risk_personal', '', 'moodle', array('class'=>'iconsmall'));
        if($upcomings) {
            $this->content->items[] = html_writer::span(get_string('warningupcoming', 'block_examswarnings', count($upcomings)), 'warning examwarning');
            $this->content->icons[] = '';

            foreach($upcomings as $warning) {
                $boardid = $DB->get_field('course', 'id', array('category'=>$warning->category, 'shortname'=>'Coord-'.$warning->programme));
                if($cmid = $DB->get_field('course_modules', 'id', array('course'=>$boardid, 'module'=>$moduleid, 'idnumber'=>'examreg'))) {
                    $url = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cmid, 'tab'=>'booking'));
                    $this->content->items[] = html_writer::link($url, $warning->shortname, array('class'=>'warning'));
                } else {
                    $this->content->items[] = html_writer::span($warning->shortname, 'warning');
                }
                $this->content->icons[] = $icon;
            }
        }

        if($reminders) {
            $this->content->items[] = html_writer::span(get_string('warningupcoming', 'block_examswarnings', count($reminders)), 'warning examwarning');
            $this->content->icons[] = '';

            foreach($reminders as $warning) {
                $boardid = $DB->get_field('course', 'id', array('category'=>$warning->category, 'shortname'=>'JEval-'.$warning->programme));
                if($cmid = $DB->get_field('course_modules', 'id', array('course'=>$boardid, 'module'=>$moduleid, 'idnumber'=>'examreg'))) {
                    $url = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cmid));
                    $this->content->items[] = html_writer::link($url, $warning->shortname, array('class'=>'warning'));
                } else {
                    $this->content->items[] = html_writer::span($warning->shortname, 'warning');
                }
                $this->content->icons[] = $icon;
            }
        }

        if($roomcalls) {
            $this->content->items[] = html_writer::span(get_string('roomcallupcoming', 'block_examswarnings', count($roomcalls)), 'warning examwarning');
            $this->content->icons[] = '';

            foreach($roomcalls as $roomcall) {
                $this->content->items[] = html_writer::span($roomcall->roomidnumber, 'warning');

                $this->content->icons[] = $icon;
            }
        }
        
        if($canmanage) {
            $icon = $OUTPUT->pix_icon('i/settings', '', 'moodle', array('class'=>'iconsmall'));
            $url = clone $this->page->url;
            $url->params(array('sesskey'=>sesskey(), 'edit'=>'on', 'bui_editid'=>$this->instance->id));
            $this->content->items[] = html_writer::link($url, get_string('configurewarnings', 'block_examswarnings'));
            $this->content->icons[] = $icon;
        }
        
        if(!$this->content->items) {
            return '';
        }
        return $this->content;
    }



    // cron function, used to synchronize exams data
    function cron() {
        global $CFG, $DB, $OUTPUT;
        
        return true; // ecastro ULPGC disable cron for tasks
    }
    
}


