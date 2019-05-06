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
 * This file contains the definition for the library class for file feedback plugin
 *
 *
 * @package   assignfeedback_archive
 * @copyright 2016 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//require_once($CFG->dirroot.'/grade/grading/lib.php');

/**
 * library class for file feedback plugin extending feedback plugin base class
 *
 * @package   assignfeedback_archive
 * @copyright 2016 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_feedback_archive extends assign_feedback_plugin {

    /** @var boolean|null $enabledcache Cached lookup of the is_enabled function */
    private $enabledcache = null;

    /**
     * Get the name of the file feedback plugin
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_archive');
    }

    /**
     * Get form elements for grading form
     *
     * @param stdClass $grade
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return bool true if elements were added to the form
     */
    public function get_form_elements($grade, MoodleQuickForm $mform, stdClass $data) {
        return false;
    }

    /**
     * Print a sub page in this plugin
     *
     * @param string $action - The plugin action
     * @return string The response html
     */
    public function view_page($action) {
        if ($action == 'addattempt') {
            return $this->add_attempt();
        } else if ($action == 'none') {
            return '';
        }

        return '';
    }

    public function add_attempt() {
        global $USER;
        require_sesskey();
        
        $returnurl = new moodle_url('/mod/assign/view.php',
                                    array('id'=>$this->assignment->get_course_module()->id));
        $userid = $USER->id;
        $assign = $this->assignment; // $assign = original $this 
        
        if ($assign->get_instance()->attemptreopenmethod == ASSIGN_ATTEMPT_REOPEN_METHOD_NONE) {
            redirect($returnurl);
        }

        if ($assign->get_instance()->teamsubmission) {
            $oldsubmission = $assign->get_group_submission($userid, 0, false);
        } else {
            $oldsubmission = $assign->get_user_submission($userid, false);
        }

        if (!$oldsubmission) {
            redirect($returnurl);
        }

        // No more than max attempts allowed.
        if ($assign->get_instance()->maxattempts != ASSIGN_UNLIMITED_ATTEMPTS &&
            $oldsubmission->attemptnumber >= ($assign->get_instance()->maxattempts - 1)) {
            redirect($returnurl);
        }

        // Create the new submission record for the group/user.
        if ($assign->get_instance()->teamsubmission) {
            $newsubmission = $assign->get_group_submission($userid, 0, true, $oldsubmission->attemptnumber + 1);
        } else {
            $newsubmission = $assign->get_user_submission($userid, true, $oldsubmission->attemptnumber + 1);
        }

        // Set the status of the new attempt to reopened.
        $newsubmission->status = ASSIGN_SUBMISSION_STATUS_REOPENED;

        // Give each submission plugin a chance to process the add_attempt.
        $plugins = $assign->get_submission_plugins();
        foreach ($plugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                $plugin->add_attempt($oldsubmission, $newsubmission);
            }
        }

        //$assign->update_submission($newsubmission, $userid, false, $assign->get_instance()->teamsubmission);
        $r = new ReflectionMethod('assign', 'update_submission');
        $r->setAccessible(true);
        $r->invoke($assign, $newsubmission, $userid, false, $assign->get_instance()->teamsubmission);

        
        $flags = $assign->get_user_flags($userid, false);
        if (isset($flags->locked) && $flags->locked) { // May not exist.
            $assign->unlock_submission($userid);
        }

        $params = array(
            'context' => context_module::instance($assign->get_course_module()->id),
            'courseid' => $assign->get_course()->id,
            'objectid' => $newsubmission->id,
            'relateduserid'=>$newsubmission->userid,);
        
        $params['other'] = array(
            'submissionid' => $newsubmission->id,
            'submissionattempt' => $newsubmission->attemptnumber,
            'submissionstatus' => $newsubmission->status,
            'groupid' => $newsubmission->groupid,
        );
        
        $event = \assignfeedback_archive\event\submission_archived::create($params);
        $event->set_assign($this->assignment);
        $event->trigger();
        
        redirect($returnurl);        
        return;
    }
    
    /**
     * Return a list of the grading actions performed by this plugin
     * This plugin supports upload zip
     *
     * @return array The list of grading actions
     */
    public function get_grading_actions() {
        return array();
    }

    /**
     * Allows hiding this plugin from the submission/feedback screen if it is not enabled.
     * If automatic reopen, then this is  disabled    
     *
     * @return bool - if false - this plugin will not accept submissions / feedback
     */
    public function is_enabled() {    
        $instance = $this->assignment->get_instance();
        if (!($instance->submissiondrafts ) ||  ($instance->attemptreopenmethod == ASSIGN_ATTEMPT_REOPEN_METHOD_UNTILPASS)) {
            $this->enabledcache = false;
        } else {
            $this->enabledcache = parent::is_enabled();
        }
        
        return $this->enabledcache;
    }

    /**
     * Check to see if the grade feedback has been modified from a form input in this plugin.
     *
     * @param stdClass $grade Grade object.
     * @param stdClass $data Data from the form submission (not used).
     * @return boolean True if the pdf has been modified, else false.
     */
    public function is_feedback_modified(stdClass $grade, stdClass $data) {
        return false;
    }
    
    /**
     * Is this assignment open for submissions?
     *
     * Check the due date,
     * prevent late submissions,
     * has this person already submitted,
     * is the assignment locked?
     *
     * @param int $userid - Optional userid so we can see if a different user can submit
     * @param stdClass $submission - Pre-fetched submission record (or false to fetch it)
     * @param stdClass $flags - Pre-fetched user flags record (or false to fetch it)
     * @return bool
     */
    public function submissions_open($userid = 0, $submission = false, $flags = false) {
        global $DB, $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        $time = time();
        $dateopen = true;
        $finaldate = false;
        if ($this->assignment->get_instance()->cutoffdate) {
            $finaldate = $this->assignment->get_instance()->cutoffdate;
        }

        if ($flags === false) {
            $flags = $this->assignment->get_user_flags($userid, false);
        }

        // User extensions.
        if ($finaldate) {
            if ($flags && $flags->extensionduedate) {
                // Extension can be before cut off date.
                if ($flags->extensionduedate > $finaldate) {
                    $finaldate = $flags->extensionduedate;
                }
            }
        }

        if ($finaldate) {
            $dateopen = ($this->assignment->get_instance()->allowsubmissionsfromdate <= $time && $time <= $finaldate);
        } else {
            $dateopen = ($this->assignment->get_instance()->allowsubmissionsfromdate <= $time);
        }

        if (!$dateopen) {
            return false;
        }

        // Note you can pass null for submission and it will not be fetched.
        if ($submission === false) {
            if ($this->assignment->get_instance()->teamsubmission) {
                $submission = $this->assignment->get_group_submission($userid, 0, false);
            } else {
                $submission = $this->assignment->get_user_submission($userid, false);
            }
        }
        if ($submission) {

            if ($this->assignment->get_instance()->submissiondrafts && $submission->status != ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                // Drafts are tracked and the student has submitted the assignment.
                if($submission->status == ASSIGN_SUBMISSION_STATUS_NEW OR $submission->status == ASSIGN_SUBMISSION_STATUS_DRAFT ) {
                    if(get_config($this->get_subtype() . '_' . $this->get_type(), 'updategraded')) {
                        if($DB->set_field('assign_submission', 'status', ASSIGN_SUBMISSION_STATUS_SUBMITTED, array('id'=>$submission->id))) {
                            \mod_assign\event\submission_status_updated::create_from_submission($this->assignment, $submission)->trigger();
                            redirect(new moodle_url('/mod/assign/view.php', array('id'=>$this->assignment->get_course_module()->id)));
                        }
                    }
                }
                return false;
            }
        }
        return true;
    }
    
    
    /**
     * Is this assignment open for submissions?
     *
     * is the assignment locked?
     *
     * @param int $userid - Optional userid so we can see if a different user can submit
     * @param bool $skipenrolled - Skip enrollment checks (because they have been done already)
     * @param stdClass $flags - Pre-fetched user flags record (or false to fetch it)
     * @param stdClass $gradinginfo - Pre-fetched user gradinginfo record (or false to fetch it)
     * @return bool
     */
    public function submissions_prevented($userid = 0,
                                     $skipenrolled = false, $flags = false,
                                     $gradinginfo = false) {
        global $USER;

        if (!$userid) {
            $userid = $USER->id;
        }


        // Now check if this user has already submitted etc.
        if (!$skipenrolled && !is_enrolled($this->assignment->get_course_context(), $userid)) {
            return true;
        }

        if ($flags === false) {
            $flags = $this->assignment->get_user_flags($userid, false);
        }
        if ($flags && $flags->locked) {
            return true;
        }

        // See if this user grade is locked in the gradebook.
        if ($gradinginfo === false) {
            $gradinginfo = grade_get_grades($this->assignment->get_course()->id,
                                            'mod',
                                            'assign',
                                            $this->assignment->get_instance()->id,
                                            array($userid));
        }
        if ($gradinginfo &&
                isset($gradinginfo->items[0]->grades[$userid]) &&
                $gradinginfo->items[0]->grades[$userid]->locked) {
            return true;
        }

        return false;
    }
    
    
    /**
     * Return true if there are no grade for current submission
     * @param stdClass $grade
     */
    public function is_empty(stdClass $grade) {
    
    
        //si fuera de plazo (antes, después), o si no submission empty
        // only true if there is a submission && (not graded / graded)
        if(empty($grade) || !has_capability('assignfeedback/archive:store', $this->assignment->get_context())) {
            return true;
        }
        
        $lastgrade = $this->assignment->get_user_grade($grade->userid, false);
        if($isold = ($grade->attemptnumber < $lastgrade->attemptnumber)) {
            return true;
        }

        if($prevent = $this->submissions_prevented($grade->userid)) {
            return true;
        }

        return !$this->submissions_open($grade->userid); 
    }
    
    /**
     * Do not show this plugin in the grading table or on the front page
     *
     * @return bool
     */
    public function has_user_summary() {
        global $PAGE, $USER;
        
        // depend on permissos y del plazo    
                    //si fuera de plazo NO pintar nada 
            
        $grade = $this->assignment->get_user_grade(0, false);
        
        $context = $this->assignment->get_context();
       
        if(!$grade || !has_capability('assignfeedback/archive:store', $context)) {
            return false;
        }
        
        $cangrade = has_capability('mod/assign:grade', $context);
        $cansubmit = has_capability('mod/assign:submit', $context);
        
        if($cansubmit && !$cangrade) {
            return true;
        } 
        
        if($cansubmit && $cangrade) {
            $referer = new moodle_url(qualified_me());
            if(strpos($referer->get_path(), 'mod/assign/view.php') &&  $referer->get_param('action') != 'grading') {
                return true;
            }
        } 
        
        return false;
    }

    /**
     * Display the comment in the feedback table.
     *
     * @param stdClass $grade
     * @param bool $showviewlink Set to true to show a link to view the full feedback
     * @return string
     */
    public function view_summary(stdClass $grade, & $showviewlink) {
            $instance = $this->assignment->get_instance();
            
            $output = $this->assignment->get_renderer();
            
            $submission = $this->assignment->get_user_submission($grade->userid, false);
            
            $maxattemptsreached = !empty($submission) &&
                              $submission->attemptnumber >= ($instance->maxattempts - 1) &&
                              $instance->maxattempts != ASSIGN_UNLIMITED_ATTEMPTS;
            if($instance->attemptreopenmethod == ASSIGN_ATTEMPT_REOPEN_METHOD_NONE) {
                $text = get_string('noarchiveallowed', 'assignfeedback_archive');
            } elseif($maxattemptsreached) {
                $text = get_string('maxattemptsreached', 'assignfeedback_archive');
            } elseif(($grade->grade !== null && $grade->grade >= 0) || (($instance->grade == 0) && $this->check_turnitin($submission))) {
                $thisurl = new moodle_url('/mod/assign/view.php', array('action'=>'viewpluginpage',
                                                                     'pluginsubtype'=>'assignfeedback',
                                                                     'plugin'=>'archive',
                                                                     'pluginaction'=>'addattempt',
                                                                     'id'=>$this->assignment->get_course_module()->id,
                                                                     'sesskey'=>sesskey()) );
                $button = new single_button($thisurl, get_string('reopen', 'assignfeedback_archive'));
                $button->add_confirm_action(get_string('reopenconfirm', 'assignfeedback_archive'));
                $text = $output->render($button);
                $text .= html_writer::div(get_string('reopen_help', 'assignfeedback_archive'), 'text-info');
            } else {
                $text = get_string('waitgrading',  'assignfeedback_archive');
            }
            
            return $text;
    }

    /**
     * Is this assignment submission plagiarism checked?
     *
     * @param stdClass $submission - Pre-fetched submission record (or false to fetch it)
     * @return bool
     */
    public function check_turnitin($submission) {
        global $CFG, $DB;
        
        if(!empty($CFG->enableplagiarism) && $submission  && ($plagiarism = get_config('plagiarism'))) { 
            if($plagiarism->turnitin_use && $plagiarism->turnitin_use_mod_assign && get_config('assignfeedback_archive','checked_turnitin')) {
                $select = "cm = :cm AND userid = :userid AND itemid = :itemid AND similarityscore IS NOT NULL ";
                $params = array('cm' => $this->assignment->get_course_module()->id, 
                                'userid' => $submission->userid,
                                'itemid' => $submission->id );
                return $DB->record_exists_select('plagiarism_turnitin_files', $select, $params);
            }
        }
         
        return false;
    }
    
    /**
     * Display the comment in the feedback table.
     *
     * @param stdClass $grade
     * @return string
     */
    public function view(stdClass $grade) {
            return ' eee ';
    }
    
    /**        /*
        // this way using reflection to avoid protected method && USER hack to avoid ghas_capability checking
        $userid = $USER->id;
        $admin = get_admin(); 
        $USER->id = $admin->id;
        
        $assignment = new assign ($this->assignment->get_context(), $this->assignment->get_course_module(), $this->assignment->get_course());
        $r = new ReflectionMethod('assign', 'add_attempt');
        $r->setAccessible(true);
        $r->invoke($assignment, $userid);
        $USER->id = $userid;
        unset($assignment);
        */
        * Run cron for this plugin
        */
    public static function cron_task() {
        global $CFG, $DB;
        
        $config = get_config('assignfeedback_archive');
        
        if(!$config->updategraded) {
            return true;
        }
        
        include_once($CFG->dirroot.'/mod/assign/locallib.php');
        
        if($config->updategraded) {
            $time = time();    
            $sql = "SELECT s.id, g.grade
                    FROM {assign} a 
                    JOIN {assign_plugin_config} pc ON pc.assignment = a.id AND pc.plugin = 'archive' AND pc.subtype = 'assignfeedback' AND pc.name = 'enabled' AND pc.value = 1
                    JOIN {assign_submission} s ON a.id = s.assignment
                    LEFT JOIN {assign_user_flags} f ON f.assignment = s.assignment AND f.userid = s.userid
                    LEFT JOIN {assign_grades} g ON g.assignment = s.assignment AND g.userid = s.userid AND g.attemptnumber = s.attemptnumber
                    
                    WHERE a.submissiondrafts = 1 AND a.attemptreopenmethod <> :reopen
                    
                    AND (s.status = :draft) AND (s.timemodified < a.duedate OR (s.timemodified < a.cutoffdate) AND (f.extensionduedate IS NULL OR s.timemodified < f.extensionduedate))
                    AND (a.duedate < :time1 OR (a.cutoffdate > 0 AND a.cutoffdate < :time2) AND (f.extensionduedate IS NULL OR f.extensionduedate < :time3 ))
                    ";
            $leftover = $DB->get_records_sql_menu($sql, array('reopen'=>ASSIGN_ATTEMPT_REOPEN_METHOD_UNTILPASS, 
                                                                'draft'=>ASSIGN_SUBMISSION_STATUS_DRAFT,
                                                                'time1'=>$time,
                                                                'time2'=>$time,
                                                                'time3'=>$time));
            
            $ungraded = array();
            foreach($leftover as $key => $value) {
                if(!isset($leftover[$key]) || $value < 0 || $value == '' || $value == null) {
                    $ungraded[] = $key;
                }
            }
            
            $leftover = array_keys($leftover);
            $chunks = array_chunk($leftover, 500);
            foreach($chunks as $chunk) {
                list($insql, $params) = $DB->get_in_or_equal($chunk);
                $select = "id $insql";
                $DB->set_field_select('assign_submission', 'status', ASSIGN_SUBMISSION_STATUS_SUBMITTED, $select, $params);
                // TODO add event
            }
            
            $chunks = array_chunk($ungraded, 500);
            foreach($chunks as $chunk) {
                list($insql, $params) = $DB->get_in_or_equal($chunk);
                $select = "id $insql";
                $DB->set_field_select('assign_submission', 'timemodified', $time, $select, $params);
                // TODO add event
            }
        }
        
        return true;
    } 
    
}
