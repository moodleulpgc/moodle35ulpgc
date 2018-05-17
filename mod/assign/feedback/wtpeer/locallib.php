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
 * This file contains the definition for the library class for PDF feedback plugin
 *
 *
 * @package   assignfeedback_wtpeer
 * @copyright 2016 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// File areas for file feedback assignment.
define('ASSIGNFEEDBACK_WTPEER_ITEM_AUTO', 'auto');
define('ASSIGNFEEDBACK_WTPEER_ITEM_PEER', 'peer');
define('ASSIGNFEEDBACK_WTPEER_ITEM_TUTOR', 'tutor');
define('ASSIGNFEEDBACK_WTPEER_ITEM_GRADER', 'grader');

define('ASSIGNFEEDBACK_WTPEER_ITEMS', 'auto,peer,tutor,grader');


/**
 * library class for wtpeer feedback plugin extending feedback plugin base class
 *
 * @package   assignfeedback_wtpeer
 * @copyright 2016 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_feedback_wtpeer extends assign_feedback_plugin {

    /** @var boolean|null $enabledcache Cached lookup of the is_enabled function */
    private $enabledcache = null;
    
    /** @var string $action the current pluginaction */
    public $action = '';

    /** @var string $returnaction next/returning action */
    public $returnaction = '';


    
    /**
     * Get the name of the file feedback plugin
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_wtpeer');
    }

    /**
     * Get form elements for grading form. 
     * ADD new elements to the  single page grading form called from grading table, each user row 
     *
     * @param stdClass $grade
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @param int $userid
     * @return bool true if elements were added to the form
     */
    public function get_form_elements_for_user($grade, MoodleQuickForm $mform, stdClass $data, $userid) {
        global $PAGE;

        
        if(!$grade) {
            $grade = $this->assignment->get_user_grade($userid, true);
        }
        $summary = $this->view($grade);
        
        $mform->addElement('static', 'pluginname', get_string('wtpeer', 'assignfeedback_wtpeer'), $summary);
    }

    /**
     * Check to see if the grade feedback for the pdf has been modified.
     *
     * @param stdClass $grade Grade object.
     * @param stdClass $data Data from the form submission (not used).
     * @return boolean True if the feedback has been modified, else false.
     */
    public function is_feedback_modified(stdClass $grade, stdClass $data) {
        global $USER;
        // TODO only activate when passing grades from multi weight to the assign proper grade
        
        return true;
    }


    /**
     * Print a sub page in this plugin
     *
     * @param string $action - The plugin action
     * @return string The response html
     */
    public function view_page($action) {
        global $CFG, $PAGE, $USER;
        
        if(!$this->is_enabled()) {
            throw new invalid_parameter_exception("Attempt to access wtpeer plugin page on assigment with id {$this->assignment->get_course_module()->id} that does not have wtpeer plugin enabled ");
        }
        
        if($action == 'reviewtable' || $action == 'showassess' || $action == 'showexplain' ||  
                    $action == 'downloadassess' || $action == 'saveoptions' || $action == 'showaliensub'  ) {
            $capability = 'assignfeedback/wtpeer:view';
        } elseif($action == 'manageconfig' || $action == 'publishassessments' || 
                        $action == 'publishgrades' || $action == 'calculate' || $action == 'batchoperation' ) {
           $capability = 'assignfeedback/wtpeer:manage';
        } elseif(substr($action, 0, 5) == 'grade' ) {
            $item = substr($action, 5);
            $capability = 'assignfeedback/wtpeer:'.$item.'grade';
        } elseif($action == 'manageallocations' || $action == 'allocate' || 
                        $action == 'importmarkerallocs' || $action == 'importallocsconfirm' || $action == 'showallocations') {
            $capability = 'assignfeedback/wtpeer:manageallocations';
        } elseif($action == 'showallocations') {
            $capability = 'assignfeedback/wtpeer:viewotherallocs';
        } else {
            throw new coding_exception("Action '$action' is unknown in assign wtpeer module plugin.");
        }
        
        $context = $this->assignment->get_context();
        require_capability($capability, $context);

        $return = optional_param('r', '', PARAM_ALPHA);
        if($return) {
            $returnurl = $this->plugin_action_url($return);
        } else {
            $returnurl = new moodle_url('/mod/assign/view.php', array('id'=>$this->assignment->get_course_module()->id));
            $referer = get_local_referer();
            if(strpos($referer, 'action=grading') !== false) {
                $return = 'grading';
            }
        }

        $renderer = $PAGE->get_renderer('assignfeedback_wtpeer');
        $this->action = $action;        
        $this->actionurl = $this->plugin_action_url($action); 
        $this->renderer = $renderer;
        $this->context = $context;
        $this->returnurl = $returnurl;
        $this->returnaction = $return;
        
        $content = '';
        $pagetitle = get_string($action, 'assignfeedback_wtpeer');
        if($action == 'grading') {
            $url =  new moodle_url('/mod/assign/view.php', array('id'=>$this->assignment->get_course_module()->id, 'action'=>$grading));
            redirect($url);
        } elseif($action == 'reviewtable' || $action == 'saveoptions') {
            if($action == 'saveoptions') {
                $this->process_save_grading_options();
            }
            $pagetitle = get_string('reviewassessments', 'assignfeedback_wtpeer');
            $content = $this->view_review_table();
        } elseif($action == 'allocate') {
            $rowuserparams = $this->get_row_user_params();
            $pagetitle = get_string('manualallocate', 'assignfeedback_wtpeer');
            $content = $this->user_allocate_markers($rowuserparams['userid'], $rowuserparams['submissionid']);
        } elseif($action == 'calculate') {
            $rowuserparams = $this->get_row_user_params();
            $content = $this->save_finalgrade_to_assign($rowuserparams['userid'], $rowuserparams['submissionid']);
        } elseif(substr($action, 0, 6) == 'manage') {
            include_once($CFG->dirroot.'/mod/assign/feedback/wtpeer/manage_forms.php');
            $actionformname = "assignfeedback_wtpeer_{$action}_form";
            $mform = new $actionformname(null, array('wtpeer'=>$this));
            if($action == 'manageconfig') {
                $config = $this->get_config();
                $data = new StdClass;
                foreach($config as $key=>$value) {
                    $data->{'config_'.$key} = $value;
                }
                $mform->set_data($data);
            }
            if ($mform->is_cancelled()) {
                redirect($returnurl);
                return;
            } elseif($fromform = $mform->get_data()) {
                if($action == 'manageconfig') {
                    if($this->save_settings($fromform)) {
                        $message = get_string('changessaved');
                    }
                } else {
                    $method = 'process_'.$action;
                    $message = $this->{$method}($fromform);
                }
                redirect($returnurl, $message);
                return;
            }
            $content = $this->renderer->render(new assign_form('wtpeermanage'.$action, $mform));
        } elseif(substr($action, 0, 5) == 'grade') {
            include_once($CFG->dirroot.'/mod/assign/feedback/wtpeer/grade_form.php');
            $rowuserparams = $this->get_row_user_params();
            $userid = $rowuserparams['userid'];
            $submissionid = $rowuserparams['submissionid'];

            $returnurl = $this->plugin_action_url('reviewtable');
            $returnurl->set_anchor('selectuser_'.$userid);

            $mform = new assignfeedback_wtpeer_grade_form(null, array('wtpeer'=>$this) + $rowuserparams,
                                                                        'post',
                                                                        '',
                                                                        array('class'=>'gradeform'));
            if ($mform->is_cancelled()) {
                redirect($returnurl);
                return;
            } elseif($fromform = $mform->get_data()) {
                $message = $this->save_user_item_grade($fromform) ? get_string('changessaved') : '';
                redirect($returnurl, $message);
                return;
            }
            $content .= $this->show_user_grading_page($rowuserparams, $mform, $action);
        } elseif(substr($action, 0, 6) == 'import') {
            include_once($CFG->dirroot.'/mod/assign/feedback/wtpeer/import_forms.php');
            $actionformname = "assignfeedback_wtpeer_{$action}_form";
            $confirm = optional_param('confirmimport', 0, PARAM_BOOL);
            $mform = new $actionformname(null, array('wtpeer'=>$this, 'confirm'=>$confirm));
            if ($mform->is_cancelled()) {
                redirect($returnurl);
                return;
            } elseif(($fromform = $mform->get_data()) && ($csvdata = $mform->get_file_content('markersfile'))) {
                include_once($CFG->dirroot.'/mod/assign/feedback/wtpeer/importlib.php');
                /*
                print_object($_POST);
                print_object($fromform);
                print_object($actionformname);
                */
                $importid = csv_import_reader::get_new_iid('assignfeedback_wtpeer');
                $gradeimporter = new assignfeedback_wtpeer_marker_importer($importid, $this, $fromform->encoding, $fromform->separator);
                $mform = new assignfeedback_wtpeer_importallocsconfirm_form(null, array('wtpeer'=>$this,
                                                                            'confirm' => false,
                                                                            'csvdata'=>$csvdata,
                                                                            'gradeimporter'=>$gradeimporter,
                                                                            'removemarkers'=>$fromform->removemarkers,
                                                                            'applytoall'=>$fromform->applytoall,
                                                                            'draftid'=>$fromform->markersfile));
            } elseif($fromform && $fromform->confirmimport && $fromform->importid) {
                $message = $this->process_import_allocations($fromform->draftid, $fromform->importid, 
                                                                $fromform->applytoall, $fromform->removemarkers, $fromform->encoding, $fromform->separator);
                redirect($returnurl, $message);
                return;
            } 
            $content = $this->renderer->render(new assign_form('wtpeermanage'.$action, $mform));
        } elseif(substr($action, 0, 4) == 'show') {
            $item = optional_param('type', '', PARAM_ALPHA);
            $submissionid = optional_param('s', 0, PARAM_INT);
            if($action == 'showassess') {
                $sort = optional_param('sort', '', PARAM_TEXT);
                $content = $this->show_item_assessments($submissionid, $item, $sort);
            } elseif($action == 'showexplain') {
                $marker = optional_param('m', 0, PARAM_INT);
                $content = $this->show_assessment_explain($submissionid, $marker, $item);
            } elseif($action == 'showallocations') {
                $content = $this->show_markers_allocations();
            } elseif($action == 'showaliensub') {
                $content = $this->show_other_content();
            }
        } elseif($action == 'downloadassess') {
            $item = optional_param('type', '', PARAM_ALPHA);
            $submissionid = optional_param('s', 0, PARAM_INT);
            $marker = optional_param('m', 0, PARAM_INT);
            $content = $this->download_assessment_explain($submissionid, $item, $marker);
        
        } elseif($action == 'batchoperation') {
            $users = explode(',', optional_param('selectedusers', '', PARAM_TEXT));
            $oper = optional_param('operation', '', PARAM_ALPHA);
            $oper = str_replace('plugingradingbatchoperation_wtpeer_', '', $oper);
            if($oper == 'calculateselected') {
                $count = $this->process_calculate_final_grades($users);
                $message = get_string('calculatedngrades', 'assignfeedback_wtpeer', $count);
                redirect($returnurl, $message);            
            } elseif($oper == 'downloadselected') { 
                $this->download_assessment_explain(0, '', 0, $users);
                print_object($_POST);
                die;
            }
        } elseif($action == 'publishgrades') {
            $count = $this->process_calculate_final_grades();
            $message = get_string('calculatedngrades', 'assignfeedback_wtpeer', $count);
            redirect($returnurl, $message);
        } else {
            $message = get_string('noaction', 'assignfeedback_wtpeer');
            redirect($returnurl, $message);
        }

        $header = new assign_header($this->assignment->get_instance(),
                                    $this->assignment->get_context(),
                                    false,
                                    $this->assignment->get_course_module()->id,
                                    get_string($action, 'assignfeedback_wtpeer'),
                                    $pagetitle);
        $o = '';       
        $o .= $this->renderer->render($header);
        $o .= $content;
        $o .= $this->renderer->render_footer();
        
        return $o;
    }
    
    
    /**
     * This allows a plugin to render an introductory section which is displayed
     * right below the activity's "intro" section on the main assignment page.
     *
     * @return string
     */
    public function view_header() {
        global $PAGE, $USER;
        
        $this->renderer = $PAGE->get_renderer('assignfeedback_wtpeer');
        $context = $this->assignment->get_context();
        $cangrade = has_capability('assignfeedback/wtpeer:grade', $context);
        $canmanage = has_capability('assignfeedback/wtpeer:manage', $context);
        
        $o = '';
        
        // allocations in whole assignment, for general summary
        $allocationsummary = '';
        $hasungradedallocs = 0;
        if($canmanage) {
            $userallocations = $this->marker_allocations_grading();
            $allocations = array();
            $grades = array();
            $hasungradedallocs = 0;
            $weights = $this->get_assessment_weights();
            foreach($weights as $item => $weight) {
                if($weight) {
                    $allocations[$item] = 0;
                    $grades[$item] = 0;
                }
            }
            foreach($userallocations as $alloc) {
                if(isset($allocations[$alloc->gradertype])) {
                    $allocations[$alloc->gradertype] += 1;
                } else {
                    $allocations[$alloc->gradertype] = 1;
                    $grades[$alloc->gradertype] = 0;
                }
                if(isset($alloc->grade) && $alloc->grade >= 0) {
                    if(isset($grades[$alloc->gradertype])) {
                        $grades[$alloc->gradertype] += 1;
                    } else {
                        $grades[$alloc->gradertype] = 1;
                    }
                }
            }
            unset($userallocations);
            
            $peeraccessmode = $this->get_config('peeraccessmode');
            foreach(array_keys($allocations) as $item) {
                $dates[$item]['start'] = $this->get_config('startgrading_'.$item);
                $dates[$item]['end'] = $this->get_config('endgrading_'.$item);
            }
        
            $allocationinfo = new assignfeedback_wtpeer_allocationinfo($this->assignment->get_course_module()->id, // coursemoduleid
                                                                        $this->assignment->get_instance()->id, //instanceid
                                                                        'allocsummary',
                                                                        $allocations, 
                                                                        $grades, 
                                                                        $dates, 
                                                                        $peeraccessmode, 
                                                                        0);

            $allocationsummary = html_writer::div($this->renderer->render($allocationinfo), ' allocationinfo ');
        }
        
        // allocations of the single user as marker
        $allocation = '';
        if($userallocations = $this->marker_allocations_grading($USER->id)) {
            $allocations = array();
            $grades = array();
            $hasungradedallocs = 0;
            foreach($userallocations as $alloc) {
                if(isset($allocations[$alloc->gradertype])) {
                    $allocations[$alloc->gradertype] += 1;
                } else {
                    $allocations[$alloc->gradertype] = 1;
                    $grades[$alloc->gradertype] = 0;
                }
                if(isset($alloc->grade) && $alloc->grade >= 0) {
                    if(isset($grades[$alloc->gradertype])) {
                        $grades[$alloc->gradertype] += 1;
                    } else {
                        $grades[$alloc->gradertype] = 1;
                    }
                } else {
                    $hasungradedallocs += 1;
                }
            }
            unset($userallocations);
            
            if(!$cangrade) {
                $peeraccessmode = $this->get_config('peeraccessmode');
                foreach(array_keys($allocations) as $item) {
                    $dates[$item]['start'] = $this->get_config('startgrading_'.$item);
                    $dates[$item]['end'] = $this->get_config('endgrading_'.$item);
                }
            } else {
                $dates = array();
            }
       
            $allocationinfo = new assignfeedback_wtpeer_allocationinfo($this->assignment->get_course_module()->id, // coursemoduleid
                                                                        $this->assignment->get_instance()->id, //instanceid
                                                                        'userallocations',
                                                                        $allocations, 
                                                                        $grades, 
                                                                        $dates, 
                                                                        $peeraccessmode, 
                                                                        $hasungradedallocs);
            $allocation = html_writer::div($this->renderer->render($allocationinfo), ' allocationinfo ');
            unset($allocationinfo);
        }
        
        $separator = ($allocationsummary && $allocation) ? '&nbsp;' : '';
        $allocation = html_writer::div($allocationsummary.$separator.$allocation, ' intro allocationinfo ');
        $title = html_writer::div($this->renderer->heading(get_string('pluginname', 'assignfeedback_wtpeer'), 4), ' clearfix ');

        
        $commands = '';
        if(!$this->is_configured()) {
            $commands .= $this->renderer->show_unconfigured_alert($this->assignment->get_course_module()->id, $cangrade);
        } elseif(($links = $this->get_grading_actions()) ) {
            $url = $this->plugin_action_url('');
            foreach($links as $action => $name) {
                $url->param('pluginaction', $action);
                $links[$action] = html_writer::link($url, $name);
            }
            
            $commands .= html_writer::alist($links, array('class'=>'commandslist'));
        }
        if($hasungradedallocs) {
            $commands .= $this->renderer->show_ungraded_allocs_alert($this->assignment->get_course_module()->id, $hasungradedallocs);
        }
        
        $commands = html_writer::div($commands, ' intro commands');

        
        
        return $this->renderer->box($title.$allocation.$commands, 'generalbox wtpeer assessmentintro clearfix');  
    }

    
    /**
     * Display the list of files in the feedback status table.
     *
     * @param stdClass $grade
     * @param bool $showviewlink (Always set to false).
     * @return string
     */
    public function view_summary(stdClass $grade, & $showviewlink) {
        $showviewlink = false;
        if(!$grade || !isset($grade->attemptnumber)) {
            return '';
        }
        return $this->view($grade);
    }
    

    /**
     * Display the list of files in the feedback status table.
     *
     * @param stdClass $grade
     * @return string
     */
    public function view(stdClass $grade) {
        global $PAGE, $USER;

        $o = '';
        $this->renderer = $PAGE->get_renderer('assignfeedback_wtpeer');
        $this->context = $this->assignment->get_context();
        list($canviewresults, $whenviewresults) = $this->can_view_assessments($grade->userid);
        list($canviewgrade, $whenviewgrade) = $this->can_view_grade($grade->userid);
        $hasungradedallocs = false;

        /*
        if(!isset($grade->attemptnumber)) {
            $grade->attemptnumber = -1;
        }
        */
        $submission = $this->assignment->get_user_submission($grade->userid, true, $grade->attemptnumber);
        $assessment = $this->get_user_assessments($grade->userid, $submission->id, true);
        if($assessment && $canviewresults) {
            $weights = $this->get_assessment_weights();
            $sum = 0;
            foreach($assessment->grades as $item => $result) {
                $assessment->grades[$item] = $this->assignment->display_grade($result, false, $grade->userid);
                $sum += $weights[$item]/100 * $result;
            }
            $assessment->grades['final'] = $this->assignment->display_grade($sum, false, $grade->userid);
        }
        
        if($hasungradedallocs = $this->marker_allocations_grading($USER->id, $submission->id, true)) {
            $hasungradedallocs = count($hasungradedallocs);
        }

        $showexplain = false;
        if(!$gradingdisabled = $this->assignment->grading_disabled($grade->userid)) {
            if($gradingmanager = get_grading_manager($this->assignment->get_context(), 'assignfeedback_wtpeer', 'assessments')) {
                if ($controller = $gradingmanager->get_active_controller()) {
                    $showexplain = true; 
                }
            }
        }
        
        if($assessment || $hasungradedallocs) {
            $assessment = new assignfeedback_wtpeer_summary($this->assignment->get_course_module()->id, // coursemoduleid
                                                            $this->assignment->get_instance()->id, //instanceid
                                                            $assessment, 
                                                            $canviewresults, $whenviewresults,
                                                            $canviewgrade, $whenviewgrade,
                                                            $showexplain,
                                                            $hasungradedallocs);
            $o .= $this->renderer->render($assessment);
        }
        
        return $o;
    }


    /**
     * Display the list users and submissions with weighted assessment item  colums
     *
     * @return string
     */
    public function get_row_user_params() {
        global $SESSION;    
            
        // If userid is passed - we are only grading a single student.
        $rownum = optional_param('rownum', 0, PARAM_INT);
        $useridlistid = optional_param('useridlistid', $this->assignment->get_useridlist_key_id(), PARAM_ALPHANUM);
        $userid = optional_param('userid', 0, PARAM_INT);
        $subid = optional_param('subid', 0, PARAM_INT);
        $attemptnumber = optional_param('attemptnumber', -1, PARAM_INT);

        if (!$userid) {
            $useridlistkey = $this->assignment->get_useridlist_key($useridlistid);
            if (empty($SESSION->mod_assign_useridlist[$useridlistkey])) {
                $SESSION->mod_assign_useridlist[$useridlistkey] = $this->assignment->get_grading_userid_list();
            }
            $useridlist = $SESSION->mod_assign_useridlist[$useridlistkey];
        } else {
            $rownum = 0;
            $useridlistid = 0;
            $useridlist = array($userid);
        }

        if ($rownum < 0 || $rownum > count($useridlist)) {
            throw new coding_exception('Row is out of bounds for the current grading table: ' . $rownum);
        }
        
        return array('rownum' => $rownum,
                        'useridlistid' => $useridlistid,
                        'userid' => $userid,
                        'submissionid' => $subid,
                        'attemptnumber' => -1);    
    }    
    

    /**
     * Display the list users and submissions with weighted assessment item  colums
     *
     * @return string
     */
    public function show_user_grading_page($rowuserparams, $mform, $action) {
        global $DB, $USER;

        $o = '';    
        
        // some shortcuts
        $userid = $rowuserparams['userid'];
        $subid = $rowuserparams['submissionid'];
        $attemptnumber = $rowuserparams['attemptnumber'];
        $instance = $this->assignment->get_instance();
        $viewfullnames = has_capability('moodle/site:viewfullnames', $this->assignment->get_course_context());
        
        $o .= $this->show_user_summary($userid, $viewfullnames);
        
        if($subid) {
            $submission = $this->get_submission($subid);
        } else {
            $submission = $this->assignment->get_user_submission($userid, false, $attemptnumber);
        }
        
        // check if status should be visible 
        $content = $this->renderer->show_submission_status($this->assignment, $submission, $viewfullnames);
        $o .= str_replace('assignsubmission_file/submission_files', 'assignfeedback_wtpeer/submission_files', $content);
        unset($content);
        
        $item = substr($action, 5);
        $data = new StdClass;
        $data->grade = '';
        if($grade = $this->get_user_item_grade($submission, $USER->id, $item)) {
            $data->grade = $grade->grade;
        }
        $mform->set_data($data);
        
//        $o .= $this->renderer->heading(get_string($action, 'assignfeedback_wtpeer'), 3);
        $o .= $this->renderer->render(new assign_form('gradingform', $mform));
        
        return $o;
    }
    
    /**
     * Display the list users and submissions with weighted assessment item  colums
     *
     * @return string
     */
    public function view_review_table() {
        global $CFG, $USER;

        include_once($CFG->dirroot.'/mod/assign/feedback/wtpeer/assesstable.php');
        require_once($CFG->dirroot . '/mod/assign/feedback/wtpeer/assesstableoptionsform.php');
        require_once($CFG->dirroot . '/mod/assign/feedback/wtpeer/assesstablebatchform.php');

        $o = '';
        $cmid = $this->assignment->get_course_module()->id;

        $links = array();
        if (has_capability('gradereport/grader:view', $this->assignment->get_course_context()) &&
                has_capability('moodle/grade:viewall', $this->assignment->get_course_context())) {
            $gradebookurl = '/grade/report/grader/index.php?id=' . $this->assignment->get_course()->id;
            $links[$gradebookurl] = get_string('viewgradebook', 'assign');
        }
        if ($this->assignment->is_blind_marking() &&
                has_capability('mod/assign:revealidentities', $this->context)) {
            $revealidentitiesurl = '/mod/assign/view.php?id=' . $cmid . '&action=revealidentities';
            $links[$revealidentitiesurl] = get_string('revealidentities', 'assign');
        }
        $currenturl = $this->plugin_action_url('reviewtable');
        $currenturl->param('r', 'reviewtable');
        foreach ($this->get_grading_actions() as $action => $description) {
            if($action == 'reviewtable') {
                continue;
            }
            $currenturl->param('pluginaction', $action);
            $links[$currenturl->out()] = $description;
        }
        $currenturl->remove_params('r');

        // Sort links alphabetically based on the link description.
        core_collator::asort($links);

        $gradingactions = new url_select($links);
        $gradingactions->set_label(get_string('choosegradingaction', 'assign'));
        
        $currenturl->param('pluginaction', 'reviewtable');
        $o .=  $this->renderer->render($gradingactions);
        $o .= groups_print_activity_menu($this->assignment->get_course_module(), $currenturl, true);
        
        $perpage = (int) get_user_preferences('assignfeedback_wtpeer_perpage', '');
        if(!$perpage) {
            $perpage = $this->assignment->get_assign_perpage();
            set_user_preference('assignfeedback_wtpeer_perpage', $perpage);
        }

        $filter = get_user_preferences('assignfeedback_wtpeer_filter', '');
        $markerfilter = (int) get_user_preferences('assignfeedback_wtpeer_markerfilter', 0);
        $config = $this->get_config();
        $cangrade = false;
        
        $assessmenttable = new assignfeedback_wtpeer_assessment_table($this, $perpage, $filter, $markerfilter, 0, false, $config);
        
        $o .=  $this->renderer->render($assessmenttable);
        
        $batchformparams = array('cm'=>$cmid,
                                    'context'=>$this->context);
        $classoptions = array('class'=>'gradingbatchoperationsform');
        $gradingbatchoperationsform = new assignfeedback_wtpeer_batch_operations_form(null,
                                                                                $batchformparams,
                                                                                'post',
                                                                                '',
                                                                                $classoptions);
        $currentgroup = groups_get_activity_group($this->assignment->get_course_module(), true);
        $users = array_keys($this->assignment->list_participants($currentgroup, true));
        if (count($users) != 0 && $this->assignment->can_grade()) {
            // If no enrolled user in a course then don't display the batch operations feature.
            $assignform = new assign_form('gradingbatchoperationsform', $gradingbatchoperationsform);
            $o .= $this->renderer->render($assignform);
            $cangrade = true;
        }
        
        $markingallocationoptions = array();
        if (has_capability('assignfeedback/wtpeer:manageallocations', $this->context)) {
            list($sort, $params) = users_order_by_sql();
            $markers = get_users_by_capability($this->context, 'assignfeedback/wtpeer:grade', '', $sort);
            $markingallocationoptions[''] = get_string('filternone', 'assign');
            $markingallocationoptions[ASSIGN_MARKER_FILTER_NO_MARKER] = get_string('markerfilternomarker', 'assign');
            foreach ($markers as $marker) {
                $markingallocationoptions[$marker->id] = fullname($marker);
            }
        }
        // Print options for changing the filter and changing the number of results per page.
        $assessoptionsformparams = array('cm'=>$cmid,
                                        'contextid'=>$this->context->id,
                                        'userid'=>$USER->id,
                                        'submissionsenabled'=>$this->assignment->is_any_submission_plugin_enabled(),
                                        'cangrade'=> $cangrade,
                                        'markingallocationopt'=>$markingallocationoptions,
                                        'showonlyactiveenrolopt'=>has_capability('moodle/course:viewsuspendedusers', $this->context),
                                        'showonlyactiveenrol'=>$this->assignment->show_only_active_users());

        $classoptions = array('class'=>'gradingoptionsform');
        $assessoptionsform = new assignfeedback_wtpeer_grading_options_form(null,
                                                                            $assessoptionsformparams,
                                                                            'post',
                                                                            '',
                                                                            $classoptions);
        $assessoptionsdata = new stdClass();
        $assessoptionsdata->perpage = $perpage;
        $assessoptionsdata->filter = $filter;
        $assessoptionsdata->markerfilter = $markerfilter;
        $assessoptionsdata->filtermsg = '';
        if($filter) { 
            $tableusers = count($assessmenttable->rawdata);
            $filteredout = 0;
            $filteredout = max(0,count($users) - $tableusers);
            $assessoptionsdata->filtermsg = ($filteredout) ? html_writer::span(get_string('filteredout', 'local_ulpgcassign', $filteredout), 'gradingtablefiltermsg') : null;
        }
        $assessoptionsform->set_data($assessoptionsdata);

        $assignform = new assign_form('gradingoptionsform',
                                    $assessoptionsform,
                                    'M.mod_assign.init_grading_options');
        $o .= $this->renderer->render($assignform);
        
        return $o;
    }
    
    
    
    
    /**
     * Save the settings for wtpeer feedback plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        $config = array();
        foreach($data as $key => $value) {
            if(substr($key, 0, 7) == 'config_') {
                $key = substr($key, 7);
                $config[$key] = $value;
            }
        }
    
        if(isset($config['weight_auto'])) {
            foreach(array('auto', 'peer', 'tutor', ) as $type) {
                $element = 'weight_'.$type;
                $config[$element] =  unformat_float($config[$element]);
            }
        }
        if(isset($config['publishmarkers']) && is_array($config['publishmarkers'])) {
            $config['publishmarkers'] = implode(',', $config['publishmarkers']);
        }
        // important for cron task, if automatic, no date 
        if(isset($config['publishgrade']) && $config['publishgrade'] == 1) {
            $config['publishgradedate'] = 0;
        }
        
        foreach($config as $key=>$value) {
            $this->set_config($key, $value);
        }
        return true;
    }


    /**
     * Save the settings for wtpeer feedback plugin
     *
     * @param stdClass $submission user submission data
     * @param int $marker the grader ID
     * @param string $item the gradetype item (auto, peer, tutor, grader)
     * @return mixed grade record or false if not found
     */
    public function get_user_item_grade($submission, $marker, $item) {
        global $DB;
        
        if(!$submission) {
            return false;
        }

        if($grades = $DB->get_records('assignfeedback_wtpeer_grades', array('submission'=>$submission->id,
                                                                        'userid'=>$submission->userid,
                                                                        'grader'=>$marker,
                                                                        'gradertype'=>$item), 'timemodified DESC', '*', 0, 1)) {
            return reset($grades);
        }                                                                
        
        return false;
    }
    

    /**
     * Save single grade for wtpeer feedback plugin
     *
     * @param stdClass $data from grade form
     * @return bool success
     */
    public function save_user_item_grade(stdClass $data) {
        global $DB;
    /*
        print_object($_GET);
        print_object($_POST);
    
        print_object($data);
        */
        //die;
        $userid = $data->userid; 
        $wtsubmission = $this->get_submission($data->subid);
        
        if($wtsubmission->userid != $userid) {
            throw new invalid_parameter_exception("Attempt to assess an user submission where grader user ID and submission user ID do not match.");
        }
        $attemptnumber = $wtsubmission->attemptnumber;
        
        
        $instance = $this->assignment->get_instance();
        $submission = null;
        if ($instance->teamsubmission) {
            $submission = $this->assignment->get_group_submission($userid, 0, false, $attemptnumber);
        } else {
            $submission = $this->assignment->get_user_submission($userid, false, $attemptnumber);
        }

        $members = array();
        if ($instance->teamsubmission && !empty($data->applytoall)) {
            $groupid = 0;
            if ($group = $this->assignment->get_submission_group($userid)) {
                $groupid = $group->id;
            }
            $members = $this->assignment->get_submission_group_members($groupid, true, $this->assignment->show_only_active_users());
        } else {
            $user = new stdClass;
            $user->id = $userid;
            $members[] = $user;
        }
        
        $success = false;
        if($members) {
            $success = true;
            foreach($members as $user) {
                $success = $success && $this->apply_item_grade_to_user($data, $user->id);
            }
        }
        
        return $success;
    }

    /**
     * Manually allocate markers for a single user
     *
     * @param int $userid ID of user (author of submission to wich markers will be allocated)
     * @param int $submissionid ID of submission that will be assessed
     * @param string $item the gradetype item (auto, peer, tutor, grader)
     * @return string page content
     */
    public function user_allocate_markers($userid, $submissionid, $item = '') {
        global $DB, $USER;

        // process assignations
        $add = optional_param_array('addselect', array(), PARAM_INT);
        $remove = optional_param_array('removeselect', array(), PARAM_INT);
        $applytoall = optional_param('applytoall', 0, PARAM_INT);
        
        if($add || $remove) {
            $fields = 'u.id, '.get_all_user_name_fields(true, 'u');
            if ($applytoall && $this->assignment->get_instance()->teamsubmission) {
                $submissiongroup = $this->assignment->get_submission_group($userid);
                $submissionusers = get_users_by_capability($this->context, 'mod/assign:submit', $fields, 'lastname ASC',
                                                            '', '', $submissiongroup->id);
                foreach($submissionusers as $key => $member) {
                    $sub = $this->assignment->get_user_submission($member->id, true);
                    $member->submissionid = $sub->id;
                    $submissionusers[$key] = $member;
                }
            } else {
                $member = new stdClass;
                $member->id = $userid;
                $member->submissionid = $submissionid;
                $submissionusers[$userid] = $member;
            }
        
            $item = required_param('type', PARAM_ALPHA);
            foreach($add as $marker) {
                foreach($submissionusers as $member) {
                    if(!$DB->record_exists('assignfeedback_wtpeer_allocs', array('submission'=>$member->submissionid, 'userid'=>$member->id, 
                                                                                    'gradertype'=>$item, 'grader'=>$marker))) {
                        $alloc = new stdClass;
                        $alloc->submission = $member->submissionid;
                        $alloc->userid = $member->id;
                        $alloc->grader = $marker;
                        $alloc->gradertype = $item;
                        $alloc->allocator = $USER->id;
                        $alloc->timemodified = time();
                        $DB->insert_record('assignfeedback_wtpeer_allocs', $alloc);
                    }
                }
            }
            foreach($remove as $marker) {
                foreach($submissionusers as $member) {
                    $DB->delete_records('assignfeedback_wtpeer_allocs', array('submission'=>$member->submissionid, 'userid'=>$member->id, 
                                                                                'gradertype'=>$item, 'grader'=>$marker));
                }
            }
        }

        $reload = false;
        if($submissionid) {
            $submission = $this->get_submission($submissionid);
        } else {
            $submission = $this->assignment->get_user_submission($userid, true);
            $submissionid = $submission->id;
            $reload = true;
        }

        $url = new moodle_url('/mod/assign/view.php', array('id'=>$this->assignment->get_course_module()->id,
                                                        'plugin'=>'wtpeer',
                                                        'pluginsubtype'=>'assignfeedback',
                                                        'action'=>'viewpluginpage',
                                                        'pluginaction'=>'allocate',
                                                        'userid'=>$userid,
                                                        'subid'=>$submissionid));
        $select = $this->get_item_menu($url, $item);
        $item = $select->selected;
        $url->param('type', $item);
        if($reload) {
            redirect($url);
        }

        $returnurl = new moodle_url($url, array('pluginaction'=>'reviewtable'));
        $returnurl->remove_params('userid', 'subid');
        $returnurl->set_anchor('selectuser_'.$userid);

        $o = '';
        
        $o .= $this->show_user_summary($userid);

        $o .= $this->renderer->render($select);
        

        
        $currentmembers = array();
        $potentialmembers  = array();
        $submissionusers = array();
        
        $fields = 'u.id, '.get_all_user_name_fields(true, 'u');
        $users = get_users_by_capability($this->context, 'assignfeedback/wtpeer:'.$item.'grade', $fields, 'lastname ASC');

        // if auto, then universe is only users in this submission
        if($item == 'auto') {
            if ($this->assignment->get_instance()->teamsubmission) {
                $submissiongroup = $this->assignment->get_submission_group($userid);
                $submissionusers = get_users_by_capability($this->context, 'assignfeedback/wtpeer:'.$item.'grade', $fields, 'lastname ASC',
                                                            '', '', $submissiongroup->id);
            } else {
                $fields = 'id, idnumber, '.get_all_user_name_fields(true);
                $submissionusers[$userid] = $DB->get_record('user', array('id'=>$userid), $fields, MUST_EXIST);
            }
            $users = array_intersect_key($users, $submissionusers);
        }
        
        if ($users) {
            if ($assigned = $DB->get_records('assignfeedback_wtpeer_allocs', array('submission'=>$submission->id, 'userid'=>$userid, 'gradertype'=>$item))) {
                foreach ($assigned as $grader) {
                    if(isset($users[$grader->grader])) {
                        $user = clone $users[$grader->grader];
                        $currentmembers[$grader->grader] = $user; 
                    }
                }
            }

            foreach($currentmembers as $id => $user) {
                unset($users[$user->id]);
            }
            $potentialmembers = $users;
        }

        // remove this user (or group) from the potentialmembers 
        if($item != 'auto') {
            $potentialmembers = array_diff_key($potentialmembers, $submissionusers);
        }
    
        $currentmembersoptions = '';
        $currentmemberscount = 0;
        $collator = new \Collator('root');
        if ($currentmembers) {
            $sortedmembers = array();
            foreach($currentmembers as $user) {
                if(!isset($sortedmembers[$user->id])) {
                    $sortedmembers[$user->id] = fullname($user);
                }
            }
            $collator->asort($sortedmembers);

            foreach($sortedmembers as $id => $username) {
                $currentmembersoptions .= '<option value="'.$id.'.">'.$username.'</option>';
                $currentmemberscount ++;
            }
        } else {
            $currentmembersoptions .= '<option>&nbsp;</option>';
            $sortedmembers[] = '&nbsp';
        }

        $potentialmemberscount = 0;
        $collator = new \Collator('root');
        if ($potentialmembers) {
            $potentialmembersoptions = array();
            foreach($potentialmembers as $user) {
                //$potentialmembersoptions[$user->id] = fullname($user, false, 'lastname').'</option>';
                $potentialmembersoptions[$user->id] = fullname($user, false, 'lastname');
            }
            $collator->asort($potentialmembersoptions);
            foreach($potentialmembersoptions as $key => $name) {
            //$potentialmembersoptions[$key] = '<option value="'.$key.'">'.$name;
            }
            $potentialmemberscount = count($potentialmembersoptions);
            //$potentialmembersoptions = implode("\n", $potentialmembersoptions);
        } else {
            //$potentialmembersoptions .= '<option>&nbsp;</option>';
            $potentialmembersoptions[] = '&nbsp';
        }    
   
        $table = new html_table();
        $table->attributes = array('class'=>'flexible wtpeer_assigmarkers ');
        $table->colclasses = array();
        
        $onfocus = "document.getElementById('assignform').add.disabled=true;
                    document.getElementById('assignform').remove.disabled=false;
                    document.getElementById('assignform').addselect.selectedIndex=-1;";
        $cell1 = html_writer::label(get_string('existingmembers', 'group', $currentmemberscount), 'removeselect');
        $cell1 .= html_writer::select($sortedmembers, 'removeselect[]', '', null, 
                                    array('size'=>20, 'id'=>'removeselect', 'multiple'=>'multiple', 'onfocus'=>$onfocus));
        
        $content = html_writer::empty_tag('input', array('name'=>'add', 'id'=>'add', 'type'=>'submit', 'title'=>get_string('add'),
                                                            //'value'=>'&nbsp;'.$this->renderer->larrow().' &nbsp; &nbsp; '.get_string('add')));
                                                            'value'=>' '.$this->renderer->larrow().'  '.get_string('add'))); 
        //$content .= '<br />';
        $content .= html_writer::empty_tag('input', array('name'=>'remove', 'id'=>'remove', 'type'=>'submit', 'title'=>get_string('remove'),
                                                            //'value'=>'&nbsp;'.$this->renderer->rarrow().' &nbsp; &nbsp; '.get_string('remove'))); 
                                                            'value'=>' '.$this->renderer->rarrow().'  '.get_string('remove'))); 
        $cell2 = html_writer::tag('p', $content, array('class'=>'arrow_button'));
        
        $onfocus = "document.getElementById('assignform').add.disabled=false;
                    document.getElementById('assignform').remove.disabled=true;
                    document.getElementById('assignform').removeselect.selectedIndex=-1;";
        $cell3 = html_writer::label(get_string('potentialmembers', 'group', $potentialmemberscount), 'addselect');
        $cell3 .= html_writer::select($potentialmembersoptions, 'addselect[]', '', null, 
                                    array('size'=>20, 'id'=>'addselect', 'multiple'=>'multiple', 'onfocus'=>$onfocus));
        
        
        $row = new html_table_row(array($cell1, $cell2, $cell3));
        $table->data[] = $row; 
        
        $cell2 = html_writer::empty_tag('input', array('name'=>'cancel', 'type'=>'submit', 'value'=>get_string('remove'))); 
        $row = new html_table_row(array('', $cell2, ''));
        //$table->data[] = $row; 
        
        $content = html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=>sesskey()));
        if ($this->assignment->get_instance()->teamsubmission) {
            $content .= html_writer::div(html_writer::checkbox('applytoall', 1, true, get_string('applytoall', 'assignfeedback_wtpeer')), ' wtpeer clearfix');
        }
        $content .= html_writer::table($table);
        $content = html_writer::div($content);
        http://localhost/moodle31ulpgc/mod/assign/view.php?id=314&plugin=wtpeer&pluginsubtype=assignfeedback&action=viewpluginpage&pluginaction=allocate&userid=95&subid=464&type=peer
        $content = html_writer::tag('form', $content, array('id'=>'assignform', 'method'=>'post', 'action'=>''));
        $o .= html_writer::div($content, ' center ', array('id'=>'assignmarkersform'));

        $o .= $this->show_back_buttons($userid);
    
        return $o;
    }
    

    /**
     * Apply a grade from a grading form to a user (may be called multiple times for a group submission).
     *
     * @param stdClass $formdata - the data from the form
     * @param int $userid - the user to apply the grade to
     * @return bool success
     */
    protected function apply_item_grade_to_user($formdata, $userid) {
        global $USER, $CFG, $DB;

        $submissionid = $formdata->subid;
        $item = substr($formdata->pluginaction, 5);
        
        $grade = $this->get_user_single_assessment($userid, $submissionid, $item, true);
        $originalgrade = $grade->grade;
        $gradingdisabled = $this->assignment->grading_disabled($userid);
        $gradinginstance = $this->get_grading_instance($userid, $grade, $gradingdisabled);
        if (!$gradingdisabled) {
            if ($gradinginstance) {
                $grade->grade = $gradinginstance->submit_and_get_grade($formdata->advancedgrading,
                                                                       $grade->id);
            } else {
                // Handle the case when grade is set to No Grade.
                if (isset($formdata->grade)) {
                    $grade->grade = grade_floatval(unformat_float($formdata->grade));
                }
            }
        }
        $grade->grader= $USER->id;

        // We do not want to update the timemodified if no grade was added.
        if (($originalgrade !== null && $originalgrade != -1) || ($grade->grade !== null && $grade->grade != -1)) {
            if ($grade->grade && $grade->grade != -1) {
                if ($this->assignment->get_instance()->grade > 0) {
                    if (!is_numeric($grade->grade)) {
                        return false;
                    } else if ($grade->grade > $this->assignment->get_instance()->grade) {
                        return false;
                    } else if ($grade->grade < 0) {
                        return false;
                    }
                } else {
                    // This is a scale.
                    if ($scale = $DB->get_record('scale', array('id' => -($this->assignment->get_instance()->grade)))) {
                        $scaleoptions = make_menu_from_list($scale->scale);
                        if (!array_key_exists((int) $grade->grade, $scaleoptions)) {
                            return false;
                        }
                    }
                }
            }
            
            $grade->timemodified = time();
            if($success = $DB->update_record('assignfeedback_wtpeer_grades', $grade)) { 
                if($this->can_save_finalgrades($grade->userid, $grade->submission)) {
                    $this->save_finalgrade_to_assign($grade->userid, $grade->submission);
                }
                return $success;
            }
            
        }
        return false;
    }    
    
    /**
     * Check if the instance settings allow calculating weighted grades and inserting into assignment gradebook
     *
     * @param int $userid - the user to be checked
     * @param int $submissionid - The submission concerned.
     * @return bool 
     */
    public function can_save_finalgrades($userid = 0, $submissionid = 0) {
        global $DB;
        
        // check if instance allows
        if(!$publishgrade = $this->get_config('publishgrade')) {
            return false;
        } elseif($publishgrade == 2) {
            $publishgradedate = $this->get_config('publishgradedate');
            if($publishgradedate > time()) {
                return false;
            }
        }
        // if we are here is either automatic or publishgradedate is in the past
        $success = true;
        
        // check if user has grades in all used items   
        if($userid && $submissionid) {
            $weights = $this->get_assessment_weights();
            foreach($weights as $item => $weight) {
                if($weight) {
                    $success = $DB->record_exists('assignfeedback_wtpeer_grades', array('userid'=>$userid, 
                                                                                        'submission'=>$submissionid,
                                                                                        'item'=>$item));
                    if(!$success) {
                        return false;
                    }
                }
            }
        }
        return $success;
    }
    
    /**
     * Check if the instance settings allow calculating weighted grades and inserting into assignment gradebook
     *
     * @param int $userid - the user to be checked
     * @param int $submissionid - The submission concerned.
     * @return bool 
     */
    public function save_finalgrade_to_assign($userid, $submissionid) {
        global $DB;
    
        $submission = $this->get_submission($submissionid);
        if($submission->userid != $userid) {
            throw new invalid_parameter_exception("Attempt to grade an user submission where user ID and submission user ID do not match.");
        }
        $attemptnumber = $submission->attemptnumber;
    
        $grade = $this->assignment->get_user_grade($userid, true, $attemptnumber);
        $originalgrade = $grade->grade;
        
        $grade->grade = $this->calculate_user_weighted_grade($userid, $submissionid);
        
        return $this->update_assign_grade($grade, $originalgrade);
    }
    
    /**
     * Check if the instance settings allow calculating weighted grades and inserting into assignment gradebook
     *
     * @param int $userid - the user to be checked
     * @param int $submissionid - The submission concerned.
     * @return bool 
     */
    public function update_assign_grade($grade, $originalgrade = null) {
        $instance = $this->assignment->get_instance();
        $success = false;
        // We do not want to update the timemodified if no grade was added.
        if (($originalgrade !== null && $originalgrade != -1) ||
                ($grade->grade !== null && $grade->grade != -1)) {
            $isautomatic = $instance->attemptreopenmethod == ASSIGN_ATTEMPT_REOPEN_METHOD_UNTILPASS;
            $isunlimited = $instance->maxattempts == ASSIGN_UNLIMITED_ATTEMPTS;
            $islessthanmaxattempts = $grade && ($grade->attemptnumber < ($instance->maxattempts-1));
            $submission = ($grade->userid) ? $this->assignment->get_user_submission($grade->userid, false) : false;
            $addattempt = $isautomatic && $submission && ($isunlimited || $islessthanmaxattempts);
            $success = $this->assignment->update_grade($grade, $addattempt);

            // Note the default if not provided for this option is true (e.g. webservices).
            // This is for backwards compatibility.
            if ($instance->sendstudentnotifications) {
                $this->assignment->notify_grade_modified($grade, true);
            }
        }
        return $success;
    }    
    
    /**
     * Calculates weighted mean grade for a user
     *
     * @param int $userid - the user to be checked
     * @param int $submissionid - The submission concerned.
     * @return bool 
     */
    public function calculate_user_weighted_grade($userid, $submissionid) {  
        global $DB;

        $weights = $this->get_assessment_weights();

        $sql = "SELECT AVG(g.grade)
                    FROM {assignfeedback_wtpeer_grades} g 
                    WHERE g.userid = :userid AND g.submission = :sid AND g.gradertype = :item ";
        $params = array('userid'=>$userid, 'sid'=>$submissionid);
        
        $sum = null;
        foreach($weights as $item => $weight) {
            if($weight) {
                $params['item'] = $item;
                $value = $DB->execute($sql, $params); 
                if($value === false) {
                    return null;
                }
                $sum += $weight/100 * $value;
            }
        }
        return $sum;
    }
    
    
    /**
     * Calculates weighted mean grade for all users & submissions and inserts them into assign grades
     *
     * @return bool 
     */
    public function process_calculate_final_grades($users = array()) {  
        global $DB;
        
        $userwhere = '';
        $params = array('assignment'=>$this->assignment->get_instance()->id);
        if($users) {
            list($insql, $inparams) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'u');
            $userwhere = " AND s.userid $insql ";
            $params = array_merge($params, $inparams);
        }
    
        $fields = "s.id, s.userid, s.id AS submissionid, ";
        foreach(array('auto', 'peer', 'tutor', 'grader') as $item) { 
            $fields .= "(SELECT AVG($item.grade)
                FROM {assignfeedback_wtpeer_grades} $item 
                WHERE $item.userid = s.userid AND $item.submission = s.id AND $item.gradertype = '$item') AS $item, ";
        }        
        $fields .= "s.attemptnumber ";            

        $sql = "SELECT $fields 
                    FROM {assign_submission} s  
                WHERE s.assignment = :assignment AND s.latest = 1 $userwhere ";
                
        $count = 0;
        if($submissions = $DB->get_records_sql($sql, $params)) {
            $weights = $this->get_assessment_weights();
            foreach($submissions as $submission) {
//                $attemptnumber = $submission->attemptnumber;

                $grade = $this->assignment->get_user_grade($submission->userid, true, $submission->attemptnumber);
                $originalgrade = $grade->grade;
                
                $sum = null;
                foreach($weights as $item => $weight) {
                    if($weight) {
                        if(!isset($submission->{$item})) {
                            $sum = null;
                            break;
                        } else {
                            $sum += $weight/100 * $submission->{$item};
                        }
                    }
                }
                
                $grade->grade = $sum;

                if($success = $this->update_assign_grade($grade, $originalgrade)) {
                    $count += 1; 
                }
            }
        }
        return $count;
    }
    

    /**
     * This will retrieve a grade object from the db, optionally creating it if required.
     *
     * @param int $userid The user we are grading
     * @param int $submissionid The submission we are grading
     * @param string $item the gradertype item (auto, peer, tutor, grader)
     * @param bool $create If true the grade will be created if it does not exist
     * @param int $marker ID of the grader, leave 0 to use current user.
     * @return stdClass The grade record
     */
    public function get_user_single_assessment($userid, $submissionid, $item, $create = false, $marker = 0) {
        global $DB, $USER;

        // If the userid is not null then use userid.
        if (!$marker) {
            $marker = $USER->id;
        }
        
        if(!$submissionid) {
            //return false;
        }
        $submission = $this->get_submission($submissionid);

        $params = array('userid'=>$userid, 'submission'=>$submissionid, 'gradertype'=>$item, 'grader'=>$marker);
        $grades = $DB->get_records('assignfeedback_wtpeer_grades', $params, 'timemodified DESC', '*', 0, 1);
        if ($grades) {
            return reset($grades);
        }
        
        if ($create) {
            $grade = new stdClass();
            $grade->submission   = $submissionid;
            $grade->userid       = $userid;
            $grade->timecreated = time();
            // If we are "auto-creating" a grade - and there is a submission
            // the new grade should not have a more recent timemodified value
            // than the submission.
            if ($submission) {
                $grade->timemodified = $submission->timemodified;
            } else {
                $grade->timemodified = $grade->timecreated;
            }
            $grade->grade = -1;
            $grade->grader = $marker;
            $grade->gradertype = $item;

            $gid = $DB->insert_record('assignfeedback_wtpeer_grades', $grade);
            $grade->id = $gid;
            return $grade;

            }
        return false;
    }
    

    /**
     * Get an instance of a grading form if advanced grading is enabled.
     * This is specific to the assignment, marker and student.
     *
     * @param int $userid - The student userid
     * @param stdClass|false $grade - The grade record
     * @param bool $gradingdisabled
     * @return mixed gradingform_instance|null $gradinginstance
     */
    public function check_grading_method() {
        global $DB;

        // check if grading is used & exists
        $gradingmanager = get_grading_manager($this->assignment->get_context(), 'mod_assign', 'submissions');
        if ($gradingmethod = $gradingmanager->get_active_method()) {
            // so the assign has an advanced grading method, we need to ensure this one also has
            $sourcecontroller = $gradingmanager->get_controller($gradingmethod);
            $definition = $sourcecontroller->get_definition();
            
            $copy = false;
            $gradingmanager = get_grading_manager($this->assignment->get_context(), 'assignfeedback_wtpeer', 'assessments');
            /*
            $targetmethod = $gradingmanager->get_active_method();
            if ($gradingmethod != $targetmethod) {
                // these mean ther is no grading method
                $gradingmanager->set_active_method($gradingmethod);
                $copy = true;
            }
            */
            $copy = $gradingmanager->set_active_method($gradingmethod);
            $targetcontroller = $gradingmanager->get_controller($gradingmethod);
            $targetdefinition = $targetcontroller->get_definition();
            
            if($copy || $definition->timemodified > $targetdefinition->timemodified) {
                $targetcontroller->update_definition($sourcecontroller->get_definition_copy($targetcontroller));
                $DB->set_field('grading_definitions', 'timecopied', time(), array('id' => $definition->id));
            }
        }
    }

    /**
     * Get an instance of a grading form if advanced grading is enabled.
     * This is specific to the assignment, marker and student.
     *
     * @param int $userid - The student userid
     * @param stdClass|false $grade - The grade record
     * @param bool $gradingdisabled
     * @return mixed gradingform_instance|null $gradinginstance
     */
    public function get_grading_instance($userid, $grade, $gradingdisabled) {
        global $CFG, $USER;

        $grademenu = make_grades_menu($this->assignment->get_instance()->grade);
        $allowgradedecimals = $this->assignment->get_instance()->grade > 0;

        // check if grading is used & exists
        //$this->check_grading_method();
        
        // Now use it if exists
        $advancedgradingwarning = false;
        $gradingmanager = get_grading_manager($this->assignment->get_context(), 'assignfeedback_wtpeer', 'assessments');
        $gradinginstance = null;
        if ($gradingmethod = $gradingmanager->get_active_method()) {
            $controller = $gradingmanager->get_controller($gradingmethod);
            if ($controller->is_form_available()) {
                $itemid = null;
                if ($grade) {
                    $itemid = $grade->id;
                }
                if ($gradingdisabled && $itemid) {
                    $gradinginstance = $controller->get_current_instance($USER->id, $itemid);
                } else if (!$gradingdisabled) {
                    $instanceid = optional_param('advancedgradinginstanceid', 0, PARAM_INT);
                    $gradinginstance = $controller->get_or_create_instance($instanceid,
                                                                           $USER->id,
                                                                           $itemid);
                }
            } else {
                $advancedgradingwarning = $controller->form_unavailable_notification();
            }
        }
        if ($gradinginstance) {
            $gradinginstance->get_controller()->set_grade_range($grademenu, $allowgradedecimals);
        }
        return $gradinginstance;
    }    
    
    
    /**
     * Process random allocation form, allocates markers to submissions
     *
     * @param object $formdata the data from the manageallocations_form
     * @return select form item
     */
    public function process_manageallocations($formdata) {        
        global $DB, $USER;
    
        $this->context = $this->assignment->get_context();
        $item = $formdata->alloctype;
        $setgroup = $formdata->subgroupid;

        $markers = get_users_by_capability($this->context, 'assignfeedback/wtpeer:'.$item.'grade', 'u.id, u.idnumber', '', '', '', $formdata->groupid, '', '', '', true);
        
        if($formdata->groupingid) {
            $groups = groups_get_all_groups($this->assignment->get_course()->id, 0, $formdata->groupingid, 'g.id, g.id AS groupid');  
            $groupingusers = get_users_by_capability($this->context, 'assignfeedback/wtpeer:'.$item.'grade', 'u.id, u.idnumber', '', '', '', array_keys($groups), '', '', '', true);
            $markers = array_intersect_key($markers, $groupingusers);
            unset($groupingusers);

        }
        
        if($formdata->roleid) {
            $roleusers = get_role_users($formdata->roleid, $this->context, true, 'u.id, u.idnumber', 'u.id', false, $formdata->groupid);
            $markers = array_intersect_key($markers, $roleusers);
            unset($roleusers);

        }
        
        if($formdata->currentallocs == 'remove') {
            if($submissions = $this->get_submissions($setgroup)) {
                $select = 'submission = ? AND userid = ? and gradertype = ? ';
                foreach($submissions as $sub) {
                    $DB->delete_records_select('assignfeedback_wtpeer_allocs', $select, array($sub->submissionid, $sub->userid, $item));
                }
            }
        }
    
        $now = time();
    
    
        // first allocate self-assessments, thus, these will be avoided if required later
        if(isset($formdata->addautoalloc) && $formdata->addautoalloc) {
            $submissions = $this->get_submissions($setgroup);
            foreach($submissions as $submission) {
                $members = array();
                if($this->assignment->get_instance()->teamsubmission) {
                    $group = $this->assignment->get_submission_group($submission->userid);
                    $members = $this->assignment->get_submission_group_members($group->id, true);
                } else {
                    $user = new stdClass;
                    $user->id = $submission->userid;
                    $members[] = $user;
                }
                foreach($members as $user) {
                    $usersub = $this->assignment->get_user_submission($user->id, true, $submission->attemptnumber);
                    $alloc = new stdClass;
                    $alloc->submission = $usersub->id;
                    $alloc->userid = $usersub->userid;
                    $alloc->gradertype = 'auto';
                    $alloc->grader = $user->id;
                    $params = get_object_vars($alloc);
                    $alloc->allocator = $USER->id;
                    $alloc->timemodified = $now;
                    if($oldalloc = $DB->get_record('assignfeedback_wtpeer_allocs', $params)) {
                        $oldalloc->allocator = $USER->id;
                        $oldalloc->timemodified = $now;
                        $DB->update_record('assignfeedback_wtpeer_allocs', $oldalloc);
                    } else {
                        $DB->insert_record('assignfeedback_wtpeer_allocs', $alloc);
                    }

                }
            }
        }
    
        
        // now allocations of others
        if($formdata->numper == 'sub') {
            // allocate N reviewers per submission
            $numallocations = min(count($markers), $formdata->numofreviews);
            $submissions = $this->get_submissions($setgroup, true);
            foreach($submissions as $submission) {
                $newallocs = $numallocations;
                if($formdata->currentallocs = 'keepmax') {
                    $newallocs = $numallocations - $submission->{$item.'allocs'};
                    if($newallocs < 1) {
                        continue;
                    }
                }
                
                $smarkers = $this->get_potential_markers($submission, $item, $markers);
                srand($formdata->seed);
                shuffle($smarkers);
                uasort($smarkers, 'wtpeer_list_compare');
                $smarkers = array_slice($smarkers, 0, $newallocs);
                foreach($smarkers as $marker) {
                    $alloc = new stdClass;
                    $alloc->submission = $submission->submissionid;
                    $alloc->userid = $submission->userid;
                    $alloc->gradertype = $item;
                    $alloc->grader = $marker->id;
                    $params = get_object_vars($alloc);
                    $alloc->allocator = $USER->id;
                    $alloc->timemodified = $now;
                    if($oldalloc = $DB->get_record('assignfeedback_wtpeer_allocs', $params)) {
                        $oldalloc->allocator = $USER->id;
                        $oldalloc->timemodified = $now;
                        $DB->update_record('assignfeedback_wtpeer_allocs', $oldalloc);
                    } else {
                        $DB->insert_record('assignfeedback_wtpeer_allocs', $alloc);
                    }
                }
            }
        } elseif($formdata->numper == 'marker') {
            // allocate N submissions per reviewer
            $numallocations = min(count($submissions), $formdata->numofreviews);
            $submissions = $this->get_submissions($setgroup);
            
            foreach($markers as $marker) {  
                $newallocs = $numallocations;
                if($formdata->currentallocs = 'keepmax') {
                    $newallocs = $numallocations - $this->count_user_allocations($marker->id, $item, $submissions);
                    if($newallocs < 1) {
                        continue;
                    }
                }
            
                $msubs = $this->get_potential_submissions($marker->id, $item, $submissions);
                srand($formdata->seed);
                shuffle($msubs);
                uasort($msubs, 'wtpeer_list_compare');
                $msubs = array_slice($msubs, 0, $newallocs);
                foreach($msubs as $sub) {
                    $alloc = new stdClass;
                    $alloc->submission = $sub->id;
                    $alloc->userid = $sub->userid;
                    $alloc->gradertype = $item;
                    $alloc->grader = $marker->id;
                    $params = get_object_vars($alloc);
                    $alloc->allocator = $USER->id;
                    $alloc->timemodified = $now;
                    if($oldalloc = $DB->get_record('assignfeedback_wtpeer_allocs', $params)) {
                        $oldalloc->allocator = $USER->id;
                        $oldalloc->timemodified = $now;
                        $DB->update_record('assignfeedback_wtpeer_allocs', $oldalloc);
                    } else {
                        $DB->insert_record('assignfeedback_wtpeer_allocs', $alloc);
                    }
                }
            }
        }
    }

    
    /**
     * Get users that can potentially serve ar markers excluding undesired ones
     *
     * @param object $submission a submission object containing alt least submission ID and userid
     * @param string $item the gradetype item (auto, peer, tutor, grader)
     * @param array $markers universe of users serving as potential markers
     * @return select form item
     */
    protected function get_potential_markers($submission, $item, $markers, $excludeany = false) { 
        global $DB; 
        
        $members = array();
        if($this->assignment->get_instance()->teamsubmission) {
            $group = $this->assignment->get_submission_group($submission->userid);
            $members = $this->assignment->get_submission_group_members($group->id, true);
        }
        if(array_key_exists($submission->userid, $markers)) {
            unset($markers[$submission->userid]);
        }
        foreach($members as $user) {
            if(array_key_exists($user->id, $markers)) {
                unset($markers[$user->id]);
            }
        }

        $params = array('sub' => $submission->submissionid, 'sub2' => $submission->submissionid,  
                        'userid' => $submission->userid, 'userid2' => $submission->userid,
                        'item' => $item);

        $excludeanygrader = '';
        if(!$excludeany) {
            $excludeanygrader = ' AND a2.gradertype = :item2 ';
            $params['item2'] = $item;
        }
    
        list($insql, $inparams) = $DB->get_in_or_equal(array_keys($markers), SQL_PARAMS_NAMED, 'm');
       
        $sql = "SELECT u.id, u.idnumber, COUNT(a.userid) AS current
                FROM {user} u
                LEFT JOIN {assignfeedback_wtpeer_allocs} a ON a.submission = :sub AND a.userid = :userid AND a.gradertype = :item 
                                                            AND a.grader = u.id
                WHERE u.id $insql
                AND NOT EXISTS(SELECT 1 FROM {assignfeedback_wtpeer_allocs} a2
                                WHERE a2.submission = :sub2 AND a2.userid = :userid2 AND a2.grader = u.id $excludeanygrader )
                GROUP BY u.id ";
        $markers = $DB->get_records_sql($sql, $params + $inparams);
        
        
        srand($submission->submissionid * time());
        shuffle($markers);
    
        return $markers;
    }

    
    /**
     * Get submissions that can be graded by a marker, excluding undesired ones
     *
     * @param object $submission a submission object containing alt least submission ID and userid
     * @param string $item the gradetype item (auto, peer, tutor, grader)
     * @param array $markers universe of users serving as potential markers
     * @return select form item
     */
    protected function get_potential_submissions($marker, $item, $allsubmissions, $excludeany = false) { 
        global $DB; 

        $submissions = array();
        foreach($allsubmissions as $sub) {
            $submissions[$sub->submissionid] = $sub->userid;
        }

        $members = array();
        if($this->assignment->get_instance()->teamsubmission) {
            if($group = $this->assignment->get_submission_group($marker)) {
                $members = $this->assignment->get_submission_group_members($group->id, true);
            }
        }
        foreach($members as $user) {
            if($key = array_search($user->id, $submissions)) {
                unset($submissions[$key]);
            }
        }

        list($insql, $params) = $DB->get_in_or_equal(array_keys($submissions), SQL_PARAMS_NAMED, 'm');
        $params['item'] = $item;
        $excludeanygrader = '';
        if(!$excludeany) {
            $excludeanygrader = ' AND a2.gradertype = :item2 ';
            $params['item2'] = $item;
        }
        
        
        $sql = "SELECT s.id, s.userid, COUNT(a.grader) AS current
                FROM {assign_submission} s
                LEFT JOIN {assignfeedback_wtpeer_allocs} a ON a.submission = s.id AND a.userid = s.userid AND a.gradertype = :item 
                WHERE s.id $insql
                AND NOT EXISTS(SELECT 1 FROM {assignfeedback_wtpeer_allocs} a2
                                WHERE a2.submission = s.id AND a2.userid = s.userid AND a2.grader = s.userid $excludeanygrader )
                GROUP BY s.id ";
        $markers = $DB->get_records_sql($sql, $params);
        
        
        srand($marker * time());
        shuffle($markers);
    
        return $markers;
    }
 
    
    /**
     * Get the submissions affected by an instance of this plugin
     *
     * @param object $submission a submission object containing alt least submission ID and userid
     * @param string $item the gradetype item (auto, peer, tutor, grader)
     * @param array $markers universe of users serving as potential markers
     * @return select form item
     */
    public function get_submissions($currentgroup = 0, $withallocs = false, $withgraders = false) { 
        global $DB;
        
        $users = array_keys( $this->assignment->list_participants($currentgroup, true));
        if (count($users) == 0) {
            // Insert a record that will never match to the sql is still valid.
            $users[] = -1;
        }
    
        $fields = 'u.id as userid, ';
        $fields .= 's.status AS status, ';
        $fields .= 's.id as submissionid, ';
        
        $weights = $this->get_assessment_weights();
        foreach($weights as $item => $weight) {
            if($weight) {
                if($withallocs) {
                    $count = "(SELECT COUNT(a.grader) FROM {assignfeedback_wtpeer_allocs} a
                                WHERE a.submission = s.id AND a.userid = s.userid AND a.gradertype = '$item' ) ";
                    $fields .= " $count as {$item}allocs, ";
                }
                if($withgraders) {
                    $count = "(SELECT COUNT(g.grader) FROM {assignfeedback_wtpeer_grades} g
                                WHERE g.submission = s.id AND g.userid = s.userid AND g.gradertype = '$item') ";
                    $fields .= " $count as {$item}graders, ";
                }
            }
        }
        $fields .= 's.attemptnumber AS attemptnumber';
        
        $params = array('assignmentid1'=>$this->assignment->get_instance()->id);

        list($userwhere, $userparams) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'user');
        $where = 'u.id ' . $userwhere;
        $params = array_merge($params, $userparams);


        $sql = "SELECT $fields 
                FROM {user} u
                LEFT JOIN {assign_submission} s ON u.id = s.userid AND s.assignment = :assignmentid1 AND s.latest = 1
                WHERE u.id  $userwhere ";
    
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Return the number of allocations of this user as marker of others
     *
     * @param int $markerid de ID of the grader
     * @param string $item the gradetype item (auto, peer, tutor, grader)
     * @return int count of allocations
     */
    public function count_user_allocations($markerid, $item = '') { 
        global $DB;

        $params = array('assignment' => $this->assignment->get_instance()->id,
                        'marker' => $markerid);

        $itemwhere = '';
        if($item) {
            $itemwhere = ' AND a.gradertype = :item ';
            $params['item'] = $item;
        }

        $sql = "SELECT COUNT(a.grader)   
                FROM {assignfeedback_wtpeer_allocs} a
                JOIN {assign_submission} s ON s.id = a.submission AND s.userid = a.userid AND s.assignment = :assignment
                WHERE a.grader = :marker $itemwhere ";
                
        return $DB->count_records_sql($sql, $params);
    }
    
    /**
     * Return the number of grades made by this user as marker of others
     *
     * @param int $markerid de ID of the grader
     * @param string $item the gradetype item (auto, peer, tutor, grader)
     * @return int count of allocations
     */
    public function count_user_assessments($markerid, $item = '') { 
        global $DB;
   
        $params = array('assignment' => $this->assignment->get_instance()->id,
                        'marker' => $markerid);
   
        $itemwhere = '';
        if($item) {
            $itemwhere = ' AND g.gradertype = :item ';
            $params['item'] = $item; 
        }

        $sql = "SELECT COUNT(g.grade)   
                FROM {assignfeedback_wtpeer_grades} g
                JOIN {assign_submission} s ON s.id = g.submission AND s.userid = g.userid AND s.assignment = :assignment
                WHERE g.grader = :marker $itemwhere ";
                
        return $DB->count_records_sql($sql, $params);
    }
    
    /**
     * Return the results of multi assessment for a user submission
     *
     * @param int $userid de ID of the user
     * @param int $submissionid the submission in wtpeer
     * @param bool $withallocations if the number of grading allocations for this submission must be returned as well
     * @param string $item the gradetype item (auto, peer, tutor, grader) leave blank for all
     * @return stdclass assessments results
     */
    public function get_user_assessments($userid, $submissionid, $withallocations = false, $item = '') { 
        global $DB;

        $assessment = null;
        
        $params = array('userid' => $userid, 'subid'=>$submissionid);
   
        $fields = "s.userid,  ";
        
        $weights = $this->get_assessment_weights();
        if($item) {
            $weights = array($item=>$weights[$item]);
        }
        foreach($weights as $type => $weight) {
            if($weight) {
                $fields .= "(SELECT AVG($type.grade)
                            FROM {assignfeedback_wtpeer_grades} $type 
                            WHERE $type.userid = s.userid AND $type.submission = s.id AND $type.gradertype = '$type' ) AS $type, ";
                $fields .= "(SELECT COUNT(n$type.grader)
                            FROM {assignfeedback_wtpeer_grades} n$type 
                            WHERE n$type.userid = s.userid AND n$type.submission = s.id AND n$type.gradertype = '$type' ) AS ng$type, ";
                if($withallocations) {
                    $fields .= "(SELECT COUNT(a$type.grader)
                                FROM {assignfeedback_wtpeer_allocs} a$type 
                                WHERE a$type.userid = s.userid AND a$type.submission = s.id AND a$type.gradertype = '$type' ) AS na$type, ";
                }
            }
        }
        $fields .= "s.attemptnumber ";
        
        $sql = "SELECT $fields 
                FROM {assign_submission} s 
                WHERE s.userid = :userid AND s.id = :subid AND s.latest = 1
                GROUP BY s.userid ";
        
        if($result = $DB->get_record_sql($sql, $params)) {
            $assessment = new stdClass;
            $assessment->userid = $result->userid;
            $assessment->submissionid = $submissionid;
            $assessment->grades = array();
            $assessment->countgrades = array();
            $assessment->allocations = array();
            foreach($weights as $type => $weight) {
                if($weight) {
                    if(isset($result->{$type})) {
                        $assessment->grades[$type] = $result->{$type};
                    }
                    if(isset($result->{'ng'.$type})) {
                        $assessment->countgrades[$type] = $result->{'ng'.$type};
                    }
                    if(isset($result->{'na'.$type})) {
                        $assessment->allocations[$type] = $result->{'na'.$type};
                    }
                }
            
            }
        }
        
        return $assessment;
    }    
    
    /**
     * Return the grading allocation and grades submitted for a given marker in this assignment
     *
     * @param int $userid de ID of the marker
     * @param int $submissionid the submission in wtpeer
     * @param bool $onlyungraded return only allocations that do not have a correponding grade
     * @param string $item the gradetype item (auto, peer, tutor, grader) leave blank for all
     * @return int count of allocations
     */
    public function marker_allocations_grading($userid = 0, $submissionid = '', $onlyungraded = false, $item = '') { 
        global $DB;    

        $params = array('assignment' => $this->assignment->get_instance()->id);
        
        $markerwhere = '';
        if($userid) {
            $markerwhere = ' AND a.grader = :marker';
            $params['marker'] = $userid; 
        }
        
        $submissionwhere = '';
        if($submissionid) {
            $submissionwhere = ' AND a.submission = :subid';
            $params['subid'] = $submissionid; 
        }
                        
        $itemwhere = '';
        if($item) {
            $itemwhere = ' AND g.gradertype = :item ';
            $params['item'] = $item; 
        }
        $ungradedwhere = '';
        if($onlyungraded) {
            $ungradedwhere = ' AND (g.grade IS NULL or g.grade < 0)';
        }
    
        $sql = "SELECT a.*, g.grade  
                FROM {assignfeedback_wtpeer_allocs} a 
                JOIN {assign_submission} s ON s.id = a.submission AND s.userid = a.userid AND s.assignment = :assignment
                LEFT JOIN {assignfeedback_wtpeer_grades} g ON g.userid = a.userid AND g.submission = a.submission AND g.gradertype = a.gradertype AND g.grader = a.grader
                WHERE 1 $markerwhere $itemwhere $submissionwhere $ungradedwhere";

        return $DB->get_records_sql($sql, $params); 
    }    
    

    /**
     * Process import allocation form data, allocates markers to submissions
     *
     * @param object $formdata the data from the manageallocations_form
     * @return select form item
     */
    public function process_import_allocations($draftid, $importid, $applytoall, $removemarkers, $encoding, $separator, $groupid = 0) {       
        global $DB, $USER;
    
        require_sesskey();
        require_capability('mod/assign:manageallocations', $this->assignment->get_context());

        require_once($CFG->dirroot . '/mod/assign/feedback/wtpeer/importlib.php');

        $gradeimporter = new assignfeedback_wtpeer_marker_importer($importid, $this->assignment, $encoding, $separator);

        $context = context_user::instance($USER->id);
        $fs = get_file_storage();
        if (!$files = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id DESC', false)) {
            redirect(new moodle_url('view.php',
                                array('id'=>$this->assignment->get_course_module()->id,
                                      'action'=>'grading')));
            return;
        }
        $file = reset($files);

        $csvdata = $file->get_content();

        if ($csvdata) {
            $gradeimporter->parsecsv($csvdata);
        }
        if (!$gradeimporter->init()) {
            $thisurl = new moodle_url('/mod/assign/view.php', array('action'=>'viewpluginpage',
                                                                     'pluginsubtype'=>'assignfeedback',
                                                                     'plugin'=>'wtpeer',
                                                                     'pluginaction'=>'importmarkerallocs',
                                                                     'id' => $this->assignment->get_course_module()->id));
            print_error('invalidimporter', 'assignfeedback_wtpeer', $thisurl);
            return;
        }

        $users = $this->assignment->list_participants($groupid, true);
        
        $teamsubmission = $this->assignment->get_instance()->teamsubmission && $applytoall;
        
        $now = time();
        while ($record = $gradeimporter->next()) {
            $success = false;
            if(isset($record->user) && array_key_exists($record->user, $users)) {
                $members = array();
                if($teamsubmission) {
                    $groupid = 0;
                    if ($group = $this->assignment->get_submission_group($record->user)) {
                        $groupid = $group->id;
                    }
                    $members = $this->assignment->get_submission_group_members($groupid, true, $this->assignment->show_only_active_users());
                } else {
                    $user = new stdClass;
                    $user->id = $record->user;
                    $members[] = $user;
                }
                foreach($members as $member) {
                    $submission = $this->assignment->get_user_submission($member->id, true);
                    if(($removemarkers == 0) && ($alloc = $DB->get_record('assignfeedback_wtpeer_allocs', array('userid'=>$member->id, 'submision'=>$submission->id,
                                                                                    'grader'=>$record->marker,  'gradertype'=>$record->item)))) {
                        $alloc->allocator = $USER->id;
                        $alloc->timemodified = $now;
                        $success  = $DB->update_record('assignfeedback_wtpeer_allocs', $alloc);
                    } else {
                        array('userid'=>$member->id, 'submision'=>$submission->id, 'grader'=>$record->marker);
                        if($removemarkers == 1) {
                            $params['gradertype'] = $record->item;
                        } 
                        if($removemarkers) {
                            $DB->delete_records('assignfeedback_wtpeer_allocs', $params);
                        }
                        $alloc = new stdClass;
                        $alloc->userid = $member->id;
                        $alloc->submission = $submission->id;
                        $alloc->grader = $record->marker;
                        $alloc->gradertype = $record->item;
                        $alloc->allocator = $USER->id;
                        $alloc->timemodified = $now;
                        $success = $DB->insert_record('assignfeedback_wtpeer_allocs', $alloc);
                    }
                    if($success) {
                        $count += 1;
                    }
                }
            }
        }
        $gradeimporter->close(true);
        
        return $count;
    }


    /**
     * Creates an assign_feedback_status renderable .
     *
     * @param stdClass $grade the wtpeer grade record for a userid & submission
     * @return assign_feedback_status renderable object
     */
    public function get_grading_feedback_status_renderable($userid, $submissionid, $markerid, $item) {
        global $CFG, $DB, $PAGE;

        $grade = $this->get_user_single_assessment($userid, $submissionid, $item, false, $markerid);
        
        if(!$grade) {
            return false;
        }

        require_once($CFG->libdir.'/gradelib.php');
        require_once($CFG->dirroot.'/grade/grading/lib.php');
        
        $instance = $this->assignment->get_instance();
        $cangrade = false; // has_capability('mod/assign:grade', $this->get_context());

        $gradefordisplay = null;
        $gradingitem = null;
        $gradeddate = null;
        $grader = null;
        $gradingmanager = get_grading_manager($this->context, 'assignfeedback_wtpeer', 'assessments');

        if ($controller = $gradingmanager->get_active_controller()) {
            $menu = make_grades_menu($instance->grade);
            $controller->set_grade_range($menu, $instance->grade > 0);
            $gradefordisplay = $controller->render_grade($PAGE,
                                                            $grade->id,
                                                            $gradingitem,
                                                            '', //$gradebookgrade->str_long_grade,
                                                            $cangrade);
        } else {
            $gradefordisplay = $this->assignment->display_grade($grade->grade, false);
        }
        $gradeddate = $grade->timemodified;
        $canviewmarkers = $this->can_view_markers();
        if (isset($grade->grader) && $canviewmarkers[$item]) {
            $grader = $DB->get_record('user', array('id' => $grade->grader));
        }
        

        $feedbackstatus = new assign_feedback_status($gradefordisplay,
                                                        $gradeddate,
                                                        $grader,
                                                        array(),
                                                        false,
                                                        $this->assignment->get_course_module()->id,
                                                        false, //$this->get_return_action(),
                                                        false //$this->get_return_params()
                                                        );
        return $feedbackstatus;
    }

    /**
     * Shows page with list of markers & asessments results for a given user submission & item.
     *
     * @param int $submissionid the submission in wtpeer
     * @param string $item the gradetype item (auto, peer, tutor, grader) 
     * @param int $marker the grader of teh submission, leave 0 for anyone
     * @return int count of allocations

     */
    public function show_item_assessments($submissionid, $item, $sort = '', $marker = 0) {
        global $DB, $PAGE, $USER;
        
        $submission = $this->get_submission($submissionid);
        $userid = $submission->userid;
        
        $o = '';

        if($userid != $USER->id) {
            $o .= $this->show_user_summary($userid);
        }

        $canviewmarkers = $this->can_view_markers();
        $fields = '';
        $join = '';
        
        $sort = optional_param('sort', 'timegraded', PARAM_ALPHA);
        $direction = optional_param('dir', 'asc', PARAM_ALPHA);
        switch ($sort) {
            case 'lastname' : $sqlsort = " u.lastname $direction, u.firstname $direction "; 
                                break;
            case 'firstname ':$sqlsort = " u.firstname $direction, u.lastname $direction "; 
                                break;
            case 'timegraded'   : $sqlsort = " g.timemodified $direction ";                     
                                break;
            case 'grade'   : $sqlsort = " g.grade $direction ";                     
                                break;
        }
        $sortoptions = array(   'timegraded' => array(
                                    'directional' => true,
                                    'type' => 'numeric',
                                    'default' => 'asc'),
                                'grade' => array(
                                    'directional' => true,
                                    'type' => 'numeric',
                                    'default' => 'asc'),
                                );
        
        if($canviewmarkers[$item]) {
            $fields = ', u.idnumber, '.get_all_user_name_fields(true, 'u');  
            $join = 'JOIN {user} u ON u.id = g.grader';
            $sortoptions += array(   'lastname' => array(
                                        'directional' => true,
                                        'type' => PARAM_ALPHA,
                                        'default' => 'asc'),
                                    'firstname' => array(
                                        'directional' => true,
                                        'type' => PARAM_ALPHA,
                                        'default' => 'asc'),
                                    );
        } elseif(strpos($sort, 'name') !==  false) {
            $sqlsort = '';
        }
        if(!$sqlsort) {
            $sqlsort = 'g.timemodified ASC';
            $sort = 'timegraded';
            $direction = 'ASC';
        }
        
        $actionurl = $this->plugin_action_url('showassess');
        $actionurl->params(array('s'=>$submissionid, 'sort'=>$sort, 'dir'=>$direction));
        $select = $this->get_item_menu($actionurl, $item);
        $item = $select->selected;
        $actionurl->param('type', $item);
        $o .= $this->renderer->render($select);
        
        $params = array('userid'=>$userid, 'submission'=>$submissionid, 'gradertype'=>$item);
        $markerwhere = '';
        if($marker) {
            $markerwhere = ' AND g.grader = :marker';
            $params['marker'] = $marker;
        }
      
        $sql = "SELECT g.* $fields
                FROM {assignfeedback_wtpeer_grades} g 
                $join 
                WHERE g.userid = :userid AND g.submission = :submission AND gradertype = :gradertype $markerwhere  
                ORDER BY $sqlsort ";
                
        if($grades = $DB->get_records_sql($sql, $params)) {
            foreach($grades as $grade) {
                $grade->grade = $this->assignment->display_grade($grade->grade, false, $grade->userid);
                if($canviewmarkers[$item]) {
                    $fields = get_all_user_name_fields(true);  
                    $marker = $DB->get_record('user', array('id'=>$grade->grader), 'id, idnumber, '.$fields);
                    $grade->fullname = $this->assignment->fullname($marker);
                }
            }
            $showlong = true;
            $showexplain = 0;
            
            if(!$gradingdisabled = $this->assignment->grading_disabled($userid)) {
                if($gradingmanager = get_grading_manager($this->context, 'assignfeedback_wtpeer', 'assessments')) {
                    if ($controller = $gradingmanager->get_active_controller()) {
                        $showexplain = 2; // means full
                        $gradingmethod = $gradingmanager->get_active_method(); 
                    }
                }
            }

            $o .=  $this->renderer->list_sortby($sortoptions, $actionurl, $sort, $direction);   
            
            if($showexplain) { // conditions for download: any advanced grading 
                $o .= $this->renderer->download_assess_link($this->assignment->get_course_module()->id, $submissionid, $item);
            }
            
            $assessment = new assignfeedback_wtpeer_item_assessments($this->assignment->get_course_module()->id, // coursemoduleid
                                                            $this, //plugin
                                                            $actionurl,
                                                            $grades,
                                                            $canviewmarkers,
                                                            $showexplain, $gradingmethod,
                                                            $showlong);
            $o .= $this->renderer->render($assessment);


        
        } else {
            $o .= $this->renderer->heading(get_string('nothingtodisplay'));
        
        }
        
        $o .= $this->show_back_buttons($userid);

        return $o;
    }


    /**
     * Shows page with list of markers & asessments results for a given user submission & item.
     *
     * @param int $submissionid the submission in wtpeer
     * @param string $item the gradetype item (auto, peer, tutor, grader) 
     * @param int $marker the grader of teh submission, leave 0 for anyone
     * @return int count of allocations

     */
    public function show_assessment_explain($submissionid, $marker, $item) {
        global $DB, $PAGE, $USER;
        
        $submission = $this->get_submission($submissionid);
        $userid = $submission->userid;
        $o = '';

        if($userid != $USER->id) {
            $o .= $this->show_user_summary($userid);
        }
        
        $explain = $this->get_grading_feedback_status_renderable($userid, $submissionid, $marker, $item);
        $o .= html_writer::div($this->renderer->render($explain), ' wtpeer showassessment ');
        
        $o .= $this->show_back_buttons($userid);

        return $o;
    }

    /**
     * Shows page with list of markers & asessments results for a given user submission & item.
     *
     * @param int $submissionid the submission in wtpeer
     * @param string $item the gradetype item (auto, peer, tutor, grader) 
     * @param int $marker the grader of teh submission, leave 0 for anyone
     * @return int count of allocations

     */
    public function download_assessment_explain($submissionid, $item = '', $marker = 0, $users = array()) {
        global $CFG, $DB, $USER;

        $weights = $this->get_assessment_weights();
        $params = array();
        if($item) {
            $weights = array($item=>$weights[$item]);
            $params['gradertype'] = $item;
            $itemwhere = " AND g.gradertype = $item"; 
        }

        $submissions = array();
        if($users && !$submissionid) {
            list($insql, $inparams) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'user');
            $sql = "SELECT g.id, g.submissionid
                    FROM {assign_submission} s
                    JOIN {assignfeedback_wtpeer_grades} g ON s.userid = g.userid AND s.id = g.submission $itemwhere
                    JOIN {user} u ON u.id = s.userid
                    WHERE s.assignment = :assigment  AND s.latest = 1 AND s.userid $insql 
                    GROUP BY g.userid 
                    ORDER BY u.lastname ASC, firstname ASC ";
            $params['assignment'] = $this->assignment->get_instance()->id;
            $params = array_merge($params, $inparams);
            $submissions = $DB->get_records_sql_menu($sql, $params);
        } else {
            $params = array('submission' => $submissionid);
            if($DB->record_exists('assignfeedback_wtpeer_grades', $params)) {
                $submissions = array($submissionid);
            }
        }
        
        if(!$submissions) {
            return get_string('nothingtodisplay');
        }
        
        $wtpeerstr = get_string('pluginnameplural', 'assignfeedback_wtpeer');
        $assessstr =  get_string('assessment', 'assignfeedback_wtpeer');
        $assignname = format_string($this->assignment->get_instance()->name, false, array('context' => $this->context));
        $showassessstr =  get_string('showassess', 'assignfeedback_wtpeer');
        
        require_once($CFG->libdir . '/pdflib.php');
        $fontfamily = 'freeserif';
        $doc = new pdf();

        $doc->SetTitle("$wtpeerstr: $assignname");
        $doc->SetAuthor('Moodle ' . $CFG->release);
        $doc->SetCreator($wtpeerstr);
        $doc->SetKeywords("Moodle, $wtpeerstr, $assessstr");
        $doc->SetSubject("$wtpeerstr: $assessstr");
        $doc->SetMargins(15, 30);
        $doc->setPrintHeader(true);
        $doc->setHeaderMargin(10);
        $doc->setHeaderFont(array($fontfamily, 'b', 10));
        $doc->setHeaderData('pix/moodlelogo-med-white.gif', 40, $assignname, get_string('pluginname', 'assignfeedback_wtpeer') );

        $doc->setPrintFooter(true);
        $doc->setFooterMargin(10);
        $doc->setFooterFont(array($fontfamily, '', 8));

        $doc->setFont($fontfamily, '', 10);
        
        $styles = "<style>.hidden {display: none;visibility: hidden;}  td.lastcol{width:80%;} td.c0{width:19%;} td.descriptionreadonly{width:25%;} td.remark{width:65%;} td.score{width:10%;}  td{border-top: 1px solid #000000;border-bottom: 1px solid #000000;padding: 6px;} </style>";
        //$styles = '<style>'.$styles.file_get_contents($CFG->dirroot.'/mod/assign/feedback/wtpeer/styles.css').'</style>';
        //$styles = '';
        
        foreach($submissions as $submissionid) {
        
            $submission = $this->get_submission($submissionid);
            $userid = $submission->userid;
            $user = $DB->get_record('user', array('id'=>$userid), 'id, username, idnumber, email'); 
        
            $doc->AddPage();
            
            $title = $this->renderer->heading($assignname, 2);
            $title .= $this->renderer->heading($wtpeerstr.': '.$showassessstr, 3);
            $doc->writeHTML($title);
            $doc->Ln(10);
            $doc->writeHTML($styles.$this->show_user_summary($userid));
            $doc->Ln(10);
            $doc->writeHTML($styles.$this->renderer->show_submission_status($this->assignment, $submission));
            
            foreach($weights as $item => $weight) {
                // add page header for item
                $doc->AddPage();
                $doc->writeHTML($this->renderer->heading(get_string('title'.$item, 'assignfeedback_wtpeer', $weight.'%'), 3));
                
                $params = array('userid'=>$userid, 'submission'=>$submissionid, 'gradertype'=>$item);
                if($marker) {
                    $params['grader'] = $marker;
                }
                $index = 0;
                if($grades = $DB->get_records('assignfeedback_wtpeer_grades', $params)) {
                    foreach($grades as $grade) {
                        $explain = $this->get_grading_feedback_status_renderable($userid, $submissionid, $grade->grader, $item);
                        //$explain = html_writer::div($this->renderer->render($explain), ' wtpeer showassessment ');
                        if($index) { 
                            $doc->AddPage();
                        }
                        $search = array('<br></br>', '<br />');
                        $replace = array('', '');
                        $doc->writeHTML($styles.str_replace($search, $replace, $this->renderer->render($explain)));
                        $index += 1;
                        // add explain
                        // new page
                    }
                
                } else {
                    $doc->writeHTML(get_string('nothingtodisplay'));
                }
            }
        }
        
        $filename = str_replace(' ', '_', $wtpeerstr);
        if($item) {
            $filename .= '_'.get_string('row'.$item, 'assignfeedback_wtpeer');
        }
        if(!$users) {
            if($user->idnumber) {
                $filename .= '_'.$user->idnumber;
            } else {
                $filename .= '_'.$userid;
            }
        }
        $filename = clean_filename(core_text::strtolower($filename).'.pdf');
        
        ob_clean();
        $doc->Output($filename, 'D');
        exit();
    }


    /**
     * Display the list users and submissions with weighted assessment item  colums
     *
     * @return string
     */
    public function show_markers_allocations() {
        global $CFG, $USER;

        include_once($CFG->dirroot.'/mod/assign/feedback/wtpeer/markerstable.php');
        require_once($CFG->dirroot . '/mod/assign/feedback/wtpeer/assesstableoptionsform.php');

        $o = '';
        $cmid = $this->assignment->get_course_module()->id;

        $currenturl = $this->plugin_action_url('showallocations');
        $o .= groups_print_activity_menu($this->assignment->get_course_module(), $currenturl, true);
        
        $perpage = (int) get_user_preferences('assignfeedback_wtpeer_perpage', '');
        if(!$perpage) {
            $perpage = $this->assignment->get_assign_perpage();
            set_user_preference('assignfeedback_wtpeer_perpage', $perpage);
        }

        $filter = get_user_preferences('assignfeedback_wtpeer_filter', '');
        $markerfilter = (int) get_user_preferences('assignfeedback_wtpeer_markerfilter', 0);
        $config = $this->get_config();
        $cangrade = false;
        
        $markerstable = new assignfeedback_wtpeer_markers_table($this, $perpage, $filter, $markerfilter, 0, false, $config);
        
        $o .=  $this->renderer->render($markerstable);


        
        $markingallocationoptions = array();
        if (has_capability('assignfeedback/wtpeer:manageallocations', $this->context)) {
            list($sort, $params) = users_order_by_sql();
            $markers = get_users_by_capability($this->context, 'assignfeedback/wtpeer:grade', '', $sort);
            $markingallocationoptions[''] = get_string('filternone', 'assign');
            $markingallocationoptions[ASSIGN_MARKER_FILTER_NO_MARKER] = get_string('markerfilternomarker', 'assign');
            foreach ($markers as $marker) {
                $markingallocationoptions[$marker->id] = fullname($marker);
            }
        }
        // Print options for changing the filter and changing the number of results per page.
        $assessoptionsformparams = array('cm'=>$cmid,
                                        'contextid'=>$this->context->id,
                                        'userid'=>$USER->id,
                                        'submissionsenabled'=>$this->assignment->is_any_submission_plugin_enabled(),
                                        'cangrade'=> $cangrade,
                                        'markingallocationopt'=>$markingallocationoptions,
                                        'showonlyactiveenrolopt'=>has_capability('moodle/course:viewsuspendedusers', $this->context),
                                        'showonlyactiveenrol'=>$this->assignment->show_only_active_users());

        $classoptions = array('class'=>'gradingoptionsform');
        $assessoptionsform = new assignfeedback_wtpeer_grading_options_form(null,
                                                                            $assessoptionsformparams,
                                                                            'post',
                                                                            '',
                                                                            $classoptions);
        $assessoptionsdata = new stdClass();
        $assessoptionsdata->perpage = $perpage;
        $assessoptionsdata->filter = $filter;
        $assessoptionsdata->markerfilter = $markerfilter;
        $assessoptionsdata->filtermsg = '';
        if($filter) { 
            $tableusers = count($assessmenttable->rawdata);
            $filteredout = 0;
            $filteredout = max(0,count($users) - $tableusers);
            $assessoptionsdata->filtermsg = ($filteredout) ? html_writer::span(get_string('filteredout', 'local_ulpgcassign', $filteredout), 'gradingtablefiltermsg') : null;
        }
        $assessoptionsform->set_data($assessoptionsdata);

        $assignform = new assign_form('gradingoptionsform',
                                    $assessoptionsform,
                                    'M.mod_assign.init_grading_options');
        $o .= $this->renderer->render($assignform);
        
        return $o;
    }



    /**
     * Display the content of a submission plugin
     *
     * @return string
     */
    public function show_other_content() {

        $o = '';
        
        $submissionid = optional_param('sid', 0, PARAM_INT);
        $plugintype = required_param('alien', PARAM_TEXT);
        $item = null;
        $plugin = $this->assignment->get_submission_plugin_by_type($plugintype);
        if ($submissionid <= 0) {
            throw new coding_exception('Submission id should not be 0');
        }
        $item = $this->get_submission($submissionid);

        // Check permissions.
        if(!$this->is_submission_marker($submissionid)) { 
            if($item->userid == 0 &&  $item->groupid && $this->get_instance()->teamsubmission) { 
                $this->assignment->require_view_group_submission($item->groupid);
            } else {
                $this->assignment->require_view_submission($item->userid);
            }
        }
        
        $content = $this->assignment->get_renderer()->render(new assign_submission_plugin_submission($plugin,
                                                            $item,
                                                            assign_submission_plugin_submission::FULL,
                                                            $this->assignment->get_course_module()->id,
                                                            $this->assignment->get_return_action(),
                                                            $this->assignment->get_return_params()));
        $o .= str_replace('assignsubmission_file/submission_files', 'assignfeedback_wtpeer/submission_files', $content);
        unset($content);
                                                            
        // Trigger event for viewing a submission.
        \mod_assign\event\submission_viewed::create_from_submission($this->assignment, $item)->trigger();
        
        //$o .= $this->assignment->view_return_links();

        $params = array('id'=>$this->assignment->get_course_module()->id,
                        'plugin'=>$this->get_type(), 'pluginsubtype'=>'assignfeedback',
                        'action'=>'viewpluginpage', 'pluginaction'=>'reviewtable');
        $url = new moodle_url('/mod/assign/view.php', $params);
        $url->set_anchor('selectuser_'.$item->userid);
        $o .= $this->assignment->get_renderer()->single_button($url, get_string('back'), 'get');
        
        return $o;
    }
    
    
    
    
    
    
    
    
    
    














   
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * Checks if the user (defaults to USER) is allocated as marker for this submission
     *
     * @return boolean
     */
    public function is_submission_marker($subid, $gradertype = '', $marker = '') {    
        global $DB, $USER;
        
        if(!$marker) {
            $marker = $USER->id;
        }
       
        $params = array('submission'=>$subid, 'grader'=>$marker);
        
        if($gradertype) {
            $params['gradertype'] = $gradertype;
        }
        
        return $DB->record_exists('assignfeedback_wtpeer_allocs', $params);
    }
 
 
     /**
     * Save grading options.
     *
     * @return void
     */
    protected function process_save_grading_options() {
        global $USER, $CFG;

        // Include grading options form.
        require_once($CFG->dirroot . '/mod/assign/feedback/wtpeer/assesstableoptionsform.php');

        require_sesskey();
        
        if (!is_null($this->context)) {
            $showonlyactiveenrolopt = has_capability('moodle/course:viewsuspendedusers', $this->context);
        } else {
            $showonlyactiveenrolopt = false;
        }

        // Get markers to use in drop lists.
        $markingallocationoptions = array();
        if (has_capability('assignfeedback/wtpeer:manageallocations', $this->context)) {
            $markingallocationoptions[''] = get_string('filternone', 'assign');
            $markingallocationoptions[ASSIGN_MARKER_FILTER_NO_MARKER] = get_string('markerfilternomarker', 'assign');
            list($sort, $params) = users_order_by_sql();
            $markers = get_users_by_capability($this->context, 'assignfeedback/wtpeer:grade', '', $sort);
            foreach ($markers as $marker) {
                $markingallocationoptions[$marker->id] = fullname($marker);
            }
        }

        $gradingoptionsparams = array('cm'=>$this->assignment->get_course_module()->id,
                                      'contextid'=>$this->context->id,
                                      'userid'=>$USER->id,
                                      'submissionsenabled'=>$this->assignment->is_any_submission_plugin_enabled(),
                                      'markingallocationopt' => $markingallocationoptions,
                                      'showonlyactiveenrolopt'=>$showonlyactiveenrolopt,
                                      'showonlyactiveenrol'=>$this->assignment->show_only_active_users());

        $mform = new assignfeedback_wtpeer_grading_options_form(null, $gradingoptionsparams);
        if ($formdata = $mform->get_data()) {
            set_user_preference('assignfeedback_wtpeer_perpage', $formdata->perpage);
            if (isset($formdata->filter)) {
                set_user_preference('assignfeedback_wtpeer_filter', $formdata->filter);
            }
            if (isset($formdata->markerfilter)) {
                set_user_preference('assignfeedback_wtpeer_markerfilter', $formdata->markerfilter);
            }
            if (!empty($showonlyactiveenrolopt)) {
                $showonlyactiveenrol = isset($formdata->showonlyactiveenrol);
                set_user_preference('grade_report_showonlyactiveenrol', $showonlyactiveenrol);
                $this->showonlyactiveenrol = $showonlyactiveenrol;
            }
        }
    }

    /**
     * Take a grade object and print a short summary for the log file.
     * The size limit for the log file is 255 characters, so be careful not
     * to include too much information.
     *
     * @deprecated since 2.7
     *
     * @param stdClass $grade
     * @return string
     */
    public function format_grade_for_log(stdClass $grade) {
        global $DB;

        $user = $DB->get_record('user', array('id' => $grade->userid), '*', MUST_EXIST);

        $info = get_string('gradestudent', 'assign', array('id'=>$user->id, 'fullname'=>fullname($user)));
        if ($grade->grade != '') {
            $info .= get_string('grade') . ': ' . $this->display_grade($grade->grade, false) . '. ';
        } else {
            $info .= get_string('nograde', 'assign');
        }
        return $info;
    }

 
 
    /**
     * Checks if current user has capability to view assessment of userid
     *
     * @param int  $userid chech i
     * @return bool is has capability or not 
     */
    public function can_view_assessments($userid = 0) {       
        global $USER;
        
        if(!$userid) {
            $userid = $USER->id;
        }
        
        $cangrade = has_any_capability(array('mod/assign:grade', 
                                                'assignfeedback/wtpeer:manage',
                                                'assignfeedback/wtpeer:viewothergrades'), $this->context);
        
        if(($userid != $USER->id) && !$cangrade) {
            return false;
        }
    
        $publishassessment = $this->get_config('publishassessment');
        $publishassessmentdate = $this->get_config('publishassessmentdate');
    
        $view = false;
        if($publishassessment == 0) {
            $view = false;
        } elseif($publishassessment == 1) {
            $view = true;
        } elseif($publishassessment == 2) {
            $view = (time() > $publishassessmentdate) ? true: false;
        }
        $view = ($cangrade || $view);
        $publishassessmentdate = (!$view && $publishassessment == 2) ? $publishassessmentdate : 0;
        
        return array($view, $publishassessmentdate);    
    }
    
    /**
     * Checks if current user has capability to view assessment of userid
     *
     * @param int  $userid chech i
     * @return bool is has capability or not 
     */
    public function can_view_grade($userid = 0) {       
        global $USER;
        
        if(!$userid) {
            $userid = $USER->id;
        }
    
        $cangrade = has_any_capability(array('mod/assign:grade', 
                                                'assignfeedback/wtpeer:manage',
                                                'assignfeedback/wtpeer:viewothergrades'), $this->context);
        
        if(($userid != $USER->id) && !$cangrade) { 
            return false;
        }
    
        $publishgrade = $this->get_config('publishgrade');
        $publishgradedate = $this->get_config('publishgradedate');
    
        $view = false;
        if($publishgrade == 0) {
            $view = false;
        } elseif($publishgrade == 1) {
            $view = true;
        } elseif($publishgrade == 2) {
            $view = (time() > $publishgradedate) ? true: false;
        }
        $view = ($cangrade || $view);
        $publishgradedate = (!$view && $publishgrade == 2) ? $publishgradedate : 0;
        
        return array($view, $publishgradedate);    
    }
    
    /**
     * Checks if current user has capability to view assessment of userid
     *
     * @param int  $userid chech i
     * @return bool is has capability or not 
     */
    public function can_view_markers($userid = 0) {     
        global $USER;
        
        if(!$userid) {
            $userid = $USER->id;
        }

        $canmanage = has_capability('assignfeedback/wtpeer:manage', $this->context, $userid);

        $canviewmarkers = array('auto'=>$canmanage,'peer'=>$canmanage,'tutor'=>$canmanage,'grader'=>$canmanage);
        if($publishmarkers = explode(',', $this->get_config('publishmarkers'))) {
            foreach($publishmarkers as $item) {
                $canviewmarkers[$item] = true;
            }
        }
        return $canviewmarkers;
    }

    /**
     * Displays buttons to go back to other pages
     *
     * @param int $userid to allow 
     * @return string HTML snippet
     */
    public function show_back_buttons($userid = 0) {
    
        $name = get_string('pluginname', 'assign');
        $button1 = $this->renderer->single_button($this->returnurl, get_string('backto', 'moodle', $name)); 
        
        $button2 = '';
        if(has_capability('assignfeedback/wtpeer:manage', $this->context)) {
            $name = get_string('reviewtable', 'assignfeedback_wtpeer');
            $url = $this->plugin_action_url('reviewtable');
            $url->set_anchor('selectuser_'.$userid);
            $button2 = $this->renderer->single_button($url, get_string('backto', 'moodle', $name), 'get'); 
        }
        
        $button3 = '';
        if(has_capability('mod/assign:grade', $this->context)) {
            $name = get_string('grading', 'assign');
            $url = new moodle_url('/mod/assign/view.php', array('id'=>$this->assignment->get_course_module()->id,
                                                                    'action'=>'grading'));
            $url->set_anchor('selectuser_'.$userid);
            $button3 = $this->renderer->single_button($url, get_string('backto', 'moodle', $name), 'get'); 
        }
        $buttons = html_writer::div($button1.$button2.$button3, ' wtpeer backbuttons ' );
        
        return html_writer::div($buttons, ' wtpeer clearfix ' );
    }
    
    /**
     * Displays user name & photo
     *
     * @param int  $userid
     * @param bool $viewfullnames capability, check if not passed
     * @return string HTML snippet
     */
    public function show_user_summary($userid, $viewfullnames = null) {
        global $DB; 
        if(!$userid) { 
            return '';
        }
        $o = '';
        $user = $DB->get_record('user', array('id' => $userid));
        if ($user) {
            if(is_null($viewfullnames)) {
                $viewfullnames = has_capability('moodle/site:viewfullnames', $this->assignment->get_course_context());
            }
            $usersummary = new assign_user_summary($user,
                                                $this->assignment->get_course()->id,
                                                $viewfullnames,
                                                $this->assignment->is_blind_marking(),
                                                $this->assignment->get_uniqueid_for_user($user->id),
                                                get_extra_user_fields($this->context),
                                                !$this->assignment->is_active_user($userid));
            $o = $this->renderer->render($usersummary);
        }
        return $o;
    }
    
    /**
     * Construct a menu to select assessment mode, gradertype for assessments  
     *
     * @param object $url moodle_url for form
     * @param string $item the gradetype item (auto, peer, tutor, grader)
     * @return select form item
     */
    protected function get_item_menu($url, $item = '') {    

        if(!$item) {
            $item = optional_param('type', 'peer', PARAM_ALPHA);
        }

        $itemmenu = array('auto' => get_string('gradeauto', 'assignfeedback_wtpeer'),
                            'peer' => get_string('gradepeer', 'assignfeedback_wtpeer'),
                            'tutor' => get_string('gradetutor', 'assignfeedback_wtpeer'),
                            'grader' => get_string('gradegrader', 'assignfeedback_wtpeer'));

        $config = $this->get_config();
        $config->weight_grader = 100 - ($config->weight_auto + $config->weight_peer +$config->weight_tutor);
        foreach(array_keys($itemmenu) as $i) {
            if(!$config->{'weight_'.$i}) {
                unset($itemmenu[$i]);
            }
        }
        
        if(!isset($itemmenu[$item])) {
            reset($itemmenu);
            $item = key($itemmenu);
        }
                        
        $select = new single_select($url, 'type', $itemmenu, $item, null);
        $select->set_label(get_string('gradertype', 'assignfeedback_wtpeer'));
        $select->formid = 'itemselector'.time();
        $select->class .= ' wtpeeritemmenu ';
    
        return $select;
    }    
    
    /**
        * Run cron for this plugin
        */
    public static function cron_task() {
        global $CFG, $DB;
        // get assignments needing re-calculation as recordset (my be many, process)
        $sql = "SELECT g.id, g.userid, g.submission, p.assignment, p.value AS enabled, pub.value AS publishgrade, pd.value AS publishgradedate, pc.value AS lastcalcdate
                FROM {assign_plugin_config} p 
                JOIN {assign_plugin_config} pub ON p.assignment = pub.assignment AND p.plugin = pub.plugin AND p.subtype = pub.subtype 
                                                    AND pub.name = 'publishgrade'
                JOIN {assign_plugin_config} pd ON p.assignment = pd.assignment AND p.plugin = pd.plugin AND p.subtype = pd.subtype 
                                                    AND pd.name = 'publishgradedate'
                LEFT JOIN {assign_plugin_config} pc ON p.assignment = pc.assignment AND p.plugin = pc.plugin AND p.subtype = pc.subtype
                                                    AND pd.name = 'lastcalcdate'
                JOIN {assign_submission} s ON s.assignment = p.assignment AND s.latest = 1 AND s.userid <> 0   
                JOIN {assignfeedback_wtpeer_grades} g ON g.submission = s.id AND g.userid = s.userid AND g.timemodified > pd.value                
                LEFT JOIN {assign_grades} ag ON ag.assignment = s.assignment AND ag.userid = g.userid AND ag.attemptnumber = s.attemptnumber                                 
                                 
                    WHERE p.plugin = 'wtpeer' AND p.subtype = 'assignfeedback' AND p.name = 'enabled' AND p.value = 1 
                        AND pub.value > 0 AND (pd.value < :now1  ) AND (pc.value IS NULL OR (pc.value < :now2 AND g.timemodified > pc.value))
                        AND (g.timemodified > ag.timemodified OR ag.timemodified IS NULL)
                GROUP BY g.userid, g.submission 
                ORDER BY p.assignment ";
        
        $now = time();
        $waiting = $DB->get_recordset_sql($sql, array('now1'=>$now, 'now2'=>$now));
        
        if($waiting->valid()) {
            $oldassignid = 0;
            $now = time();
            foreach($waiting as $grade) {
                // get the wt grades that are newer than last calculation for this assignment
                if($grade->assignment != $oldassignid) {
                    $wtpeer->set_config('lastcalcdate', time());
                    list($course, $cm) = get_course_and_cm_from_instance($grade->assignment, 'assign');
                    $assign = new assign(context_module::instance($cm->id), $cm, $course);
                    $wtpeer = $assign->get_feedback_plugin_by_type('wtpeer');
                    $oldassignid = $grade->assignment;
                }
                $wtpeer->save_finalgrade_to_assign($grade->userid, $grade->submission);
            }
        }
        $waiting->close();
    }    
    
    /**
     * Return the inner assign $assignmenmt class
     *
     * @param class $mform The form being modified
     
     */
    public function add_standard_form_items(& $mform, $userid = false, $subid = false) {

        $mform->addElement('hidden', 'id', $this->assignment->get_course_module()->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'plugin', 'wtpeer');
        $mform->setType('plugin', PARAM_ALPHA);
        $mform->addElement('hidden', 'pluginsubtype', 'assignfeedback');
        $mform->setType('pluginsubtype', PARAM_ALPHA);
        $mform->addElement('hidden', 'action', 'viewpluginpage');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'pluginaction', $this->action);
        $mform->setType('pluginaction', PARAM_ALPHA);   
        if(isset($this->returnaction) && $this->returnaction) {
            $mform->addElement('hidden', 'r', $this->returnaction);
            $mform->setType('r', PARAM_ALPHA);   
        }
        
        if($userid !== false) {
            $mform->addElement('hidden', 'userid', $userid);
            $mform->setType('userid', PARAM_INT);   

        }
        
        if($subid !== false) {
            $mform->addElement('hidden', 'subid', $subid);
            $mform->setType('subid', PARAM_INT);   

        }
    }
    
    /**
     * Get a configuration value for this plugin, if all calculated weight_grader
     *
     * @param mixed $setting The config key (string) or null
     * @return mixed string | false
     */
    public function get_assessment_weights() {
        $weight = array();
        foreach(array('auto', 'peer', 'tutor') as $type) {
            $weight[$type] = (float)$this->get_config('weight_'.$type);
        }
        
        $weight['grader'] = 100 - ($weight['auto'] + $weight['peer'] +$weight['tutor']);
        return $weight;
    }
    
    /**
     * Return the inner assign $assignmenmt class
     *
     * @param string $submissionid The id of the submission we want
     * @return moodle_url
     */
    public function plugin_action_url($action = '') {
        $url = new moodle_url('/mod/assign/view.php', array('id'=>$this->assignment->get_course_module()->id,
                                                    'plugin'=>'wtpeer',
                                                    'pluginsubtype'=>'assignfeedback',
                                                    'action'=>'viewpluginpage',
                                                    'pluginaction'=>$action));
        return $url;
    }

    /**
     * Return the inner assign $assignmenmt class
     *
     * @return assign class
     */
    public function is_configured() {
        $config = $this->get_config();
        return ($config->enabled && isset($config->peeraccessmode) && isset($config->publishassessment) && isset($config->publishgrade)  );
    }
    
    /**
     * Return the inner assign $assignmenmt class
     *
     * @return assign class
     */
    public function get_assignment() {
        return $this->assignment;
    }
    
    /**
     * Load the submission object from it's id.
     *
     * @param int $submissionid The id of the submission we want
     * @return stdClass The submission
     */
    protected function get_submission($submissionid) {
        global $DB;

        $params = array('assignment'=>$this->assignment->get_instance()->id, 'id'=>$submissionid);
        return $DB->get_record('assign_submission', $params, '*', MUST_EXIST);
    }
    
    
    /**
     * Return true if there are no released comments/annotations.
     *
     * @param stdClass $grade
     */
    public function is_empty(stdClass $grade) {
        global $DB;
        
        return false;
    }

    /**
     * If this plugin should not include a column in the grading table or a row on the summary page
     * then return false
     *
     * @return bool
     */
    public function has_user_summary() {
        return true;
    }
    
    /**
     * The assignment has been deleted - remove the plugin specific data
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        /*
        $grades = $DB->get_records('assign_grades', array('assignment'=>$this->assignment->get_instance()->id), '', 'id');
        if ($grades) {
            list($gradeids, $params) = $DB->get_in_or_equal(array_keys($grades), SQL_PARAMS_NAMED);
            $DB->delete_records_select('assignfeedback_wtpeer_annot', 'gradeid ' . $gradeids, $params);
            $DB->delete_records_select('assignfeedback_wtpeer_cmnt', 'gradeid ' . $gradeids, $params);
        }
        */
        return true;
    }

    
    /**
     * If true, the plugin will appear on the module settings page and can be
     * enabled/disabled per assignment instance.
     *
     * @return bool false
     */
    public function is_configurable() {
        return true;
    }
    
    
    /**
     * Grading & batch actions are needed to move grades from wtpeer to assig grade
     */     
    
    
    /**
     * Return a list of the batch grading operations supported by this plugin.
     *
     * @return array - An array of action and description strings.
     *                 The action will be passed to grading_batch_operation.
     */
    public function get_grading_batch_operations() {
        return array();
    }

    /**
     * Return a list of the grading actions supported by this plugin.
     *
     * A grading action is a page that is not specific to a user but to the whole assignment.
     * @return array - An array of action and description strings.
     *                 The action will be passed to grading_action.
     */
    public function get_grading_actions() {
        $actions = array();
        $context = $this->assignment->get_context();
    
        if(has_capability('assignfeedback/wtpeer:manage', $context)) {
            $actions['manageconfig'] = get_string('manageconfig', 'assignfeedback_wtpeer');
            if($this->get_config('publishgrade') == 0) { // this is manual publish
                $actions['publishgrades'] = get_string('publishgrades', 'assignfeedback_wtpeer');
            }
        }
        if(has_capability('assignfeedback/wtpeer:grade', $context)) {
            $actions['reviewtable'] = get_string('reviewtable', 'assignfeedback_wtpeer');
        }

        if(has_capability('assignfeedback/wtpeer:manageallocations', $context)) {
            $actions['manageallocations'] = get_string('manageallocations', 'assignfeedback_wtpeer');
            $actions['importmarkerallocs'] = get_string('importmarkerallocs', 'assignfeedback_wtpeer');
        }

        if(has_capability('assignfeedback/wtpeer:viewotherallocs', $context)) {
            $actions['showallocations'] = get_string('showallocations', 'assignfeedback_wtpeer');
        }
        
        return $actions;
    }

    /**
     * Show a grading action form
     *
     * @param string $gradingaction The action chosen from the grading actions menu
     * @return string The page containing the form
     */
    public function grading_action($gradingaction) {
        return '';
    }

    /**
     * Show a batch operations form
     *
     * @param string $action The action chosen from the batch operations menu
     * @param array $users The list of selected userids
     * @return string The page containing the form
     */
    public function grading_batch_operation($action, $users) {
        return '';
    }
    
    
    
}

function wtpeer_list_compare($a, $b) {
    $a = $a->current;
    $b = $b->current;

    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}
