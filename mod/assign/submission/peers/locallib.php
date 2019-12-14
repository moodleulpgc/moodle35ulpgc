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
 * This file contains the definition for the library class for view peers submission plugin
 *
 * @package assignsubmission_peers
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

 /** Include submissionplugin.php */
 require_once($CFG->dirroot . '/mod/assign/submissionplugin.php');
 require_once($CFG->dirroot . '/mod/assign/submission/peers/classes/peerstable.php');

/**
 * This file contains the definition for the library class for view peers submission plugin
 *
 * @package assignsubmission_peers
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_peers extends assign_submission_plugin {

   /**
    * get the name of the view peers submission plugin
    * @return string
    */
    public function get_name() {
        return get_string('pluginname', 'assignsubmission_peers');
    }


    /**
     * Get the default setting for peer submission plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE;

        $defaultlimitmode = $this->get_config('viewpeerslimit');
        if(!$defaultlimitmode) {
            $defaultlimitmode = get_config('assignsubmission_peers', 'viewpeerslimit');
        }

        $options = array('final'=>get_string('limitbyfinal', 'assignsubmission_peers'),
                            'grade'=>get_string('limitbygrade', 'assignsubmission_peers'),
                            'time'=>get_string('limitbytime', 'assignsubmission_peers'),
                            'submit'=>get_string('limitbysubmission', 'assignsubmission_peers')
                            );
        $mform->addElement('select', 'assignsubmission_peers_viewpeerslimit', get_string('limitbymode', 'assignsubmission_peers'), $options);
        $mform->addHelpButton('assignsubmission_peers_viewpeerslimit', 'limitbymode', 'assignsubmission_peers');
        $mform->setDefault('assignsubmission_peers_viewpeerslimit', $defaultlimitmode);
        $mform->disabledIf('assignsubmission_peers_viewpeerslimit', 'assignsubmission_peers_enabled', 'notchecked');
    }

    /**
     * Save the settings for peer submission plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        $this->set_config('viewpeerslimit', $data->assignsubmission_peers_viewpeerslimit);
        return true;
    }

   /**
    * display AJAX based comment in the submission status table
    *
    * @param stdClass $submission
    * @param bool $showviewlink - If the comments are long this is set to true so they can be shown in a separate page
    * @return string
    */
   public function view_summary(stdClass $submission, & $showviewlink) {
        global $CFG, $DB, $USER, $OUTPUT;
        // never show a link to view full submission
        $showviewlink = false;
        // need to used this init() otherwise it shows up undefined !
        // require js for commenting
       // comment::init();

//         $options = new stdClass();
//         $options->area    = 'submission_peers';
//         $options->course    = $this->assignment->get_course();
//         $options->context = $this->assignment->get_context();
//         $options->itemid  = $submission->id;
//         $options->component = 'assignsubmission_peers';
//         $options->showcount = true;
//         $options->displaycancel = true;
//
//         $comment = new comment($options);
//         $comment->set_view_permission(true);

        $instance = $this->assignment->get_instance();
        $link = '';


        if ($this->assignment->is_any_submission_plugin_enabled()) {
            $submission = $this->assignment->get_user_submission($USER->id, false);
            if($submission) { //= $DB->get_record('assign_submission', array('assignment'=>$instance->id, 'userid'=>$USER->id))) {
                $is_open = false;
                $mode = $this->get_config('viewpeerslimit');
                switch ($mode) {
                    case 'grade' :  $is_open = !$this->is_graded($USER->id);
                                    $link= get_string('viewpeersno', 'assignsubmission_peers', get_string('limitbygrade', 'assignsubmission_peers'));
                                    break;
                    case 'final':  $is_open = $this->submissions_open($submission);
                                    $link= get_string('viewpeersno', 'assignsubmission_peers', get_string('limitbyfinal', 'assignsubmission_peers'));
                                    break;
                    case 'time'  :  $is_open = (time() <= $instance->duedate);
                                    $link= get_string('viewpeersno', 'assignsubmission_peers', get_string('limitbytime', 'assignsubmission_peers'));
                                    break;
                    case 'submit'  :  $is_open = false;
                                    $link= get_string('viewpeersno', 'assignsubmission_peers', get_string('limitbysubmission', 'assignsubmission_peers'));
                                    break;

                }
                if ($submission->status == ASSIGN_SUBMISSION_STATUS_SUBMITTED && !$is_open) {
                    // drafts are tracked and the student has submitted the assignment
                    //moodle_url('/mod/assign/view.php', $params);
                    $link = get_string('viewpeerslink', 'assignsubmission_peers');
                    $params = array('id'=>$this->assignment->get_course_module()->id,
                                    'action'=>'viewpluginpage',
                                    'plugin'=>'peers',
                                    'pluginsubtype'=>'assignsubmission',
                                    'pluginaction'=>'table',
                                    );
                    //return $OUTPUT->single_button(new moodle_url($CFG->wwwroot.'/mod/assign/submission/peers/index.php', $params), $link, 'get');
                    return $OUTPUT->single_button(new moodle_url($CFG->wwwroot.'/mod/assign/view.php', $params), $link, 'post');
                }
            }
        }

        return $link; //$comment->output(true);

    }

    /**
     * Always return false because only active if exist submission
     * @param stdClass $submission
     * @return bool
     */
    public function is_empty(stdClass $submission) {
        return false;
    }


    /**
     * The submission view peers plugin has no submission component per se so should not be counted
     * when determining whether to show the edit submission link.
     * @return boolean
     */
    public function allow_submissions() {
        return false;
    }

    /**
     * If this plugin should not include a column in the grading table or a row on the summary page
     * then return false
     *
     * @return bool
     */
    public function has_user_summary() {
        global $USER;
        $context = $this->assignment->get_context();
        if($cangrade = has_capability('mod/assign:grade', $context)) {
            return false;
        }
        return true;
    }


    /**
     * Check if the submission plugin is open for submissions by a user
     * @param int $userid user ID
     * @return bool|string 'true' if OK to proceed with submission, otherwise a
     *                        a message to display to the user
     */
    public function is_open($userid=0) {
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
     * @return bool
     */
    private function submissions_open(stdClass $submission) {
        global $DB, $USER;

        $time = time();
        $dateopen = true;
        $instance = $this->assignment->get_instance();
        $finaldate = $instance->duedate;
        if ($instance->cutoffdate) {
            $finaldate = $instance->cutoffdate;
        }

        // user extensions
        $flags = $this->assignment->get_user_flags($USER->id, false);

        if ($flags && $flags->locked) {
            return false;
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
            $dateopen = ($instance->allowsubmissionsfromdate <= $time && $time <= $finaldate);
        } else {
            $dateopen = ($instance->allowsubmissionsfromdate <= $time);
        }

        if (!$dateopen) {
            return false;
        }

        if ($this->assignment->grading_disabled($submission->userid)) {
            return false;
        }

        return true;
    }



    private function is_graded($userid) {
        global $DB;

        $instance = $this->assignment->get_instance();
        $grade = $DB->get_record('assign_grades', array('assignment'=>$instance->id, 'userid'=>$userid));
        if ($grade) {
            return ($grade->grade !== NULL && $grade->grade >= 0);
        }
        return false;

    }

    public function view_peers_restricted($userid=0) {
        global $DB, $USER;

        if(!$userid) {
            $userid = $USER->id;
        }
        $instance = $this->assignment->get_instance();
        $is_open = true;
        if ($this->assignment->is_any_submission_plugin_enabled()) {
            if($submission = $DB->get_record('assign_submission', array('assignment'=>$instance->id, 'userid'=>$userid))) {
                $mode = $this->get_config('viewpeerslimit');
                switch ($mode) {
                    case 'grade' :  $is_open = !$this->is_graded($USER->id);
                                    $link= get_string('viewpeersno', 'assignsubmission_peers', get_string('limitbygrade', 'assignsubmission_peers'));
                                    break;
                    case 'final':  $is_open = $this->submissions_open($submission);
                                    $link= get_string('viewpeersno', 'assignsubmission_peers', get_string('limitbyfinal', 'assignsubmission_peers'));
                                    break;
                    case 'time'  :  $is_open = (time() <= $instance->duedate);
                                    $link= get_string('viewpeersno', 'assignsubmission_peers', get_string('limitbytime', 'assignsubmission_peers'));
                                    break;
                    case 'submit'  :  $is_open = false;
                                    break;
                }
            }
        }
        
        $restricted = $is_open ? $link : false;
        return $restricted;
    }

    /**
     * Prints a subpage for this plugin
     *
     * @param string $action - The plugin action
     * @return string The response html
     */
    public function view_page($action) {
        global $DB, $USER, $PAGE;
        
        $renderer = $PAGE->get_renderer('assignsubmission_peers');
        $header = $renderer->render(new assign_header($this->assignment->get_instance(),
                                                        $this->assignment->get_context(),
                                                        false,
                                                        $this->assignment->get_course_module()->id,
                                                        get_string($action, 'assignsubmission_peers')));
        $title = $renderer->heading(get_string($action, 'assignsubmission_peers'), 3);                                                        
        //$footer = $renderer->render_footer();                                                        
        $footer = $this->assignment->get_renderer()->render_footer();                                                        
        $groups = '';
        $content = '';
        
        switch($action) {
            case 'table' : list($groups, $content) = $this->view_peers_table();
                        break;
            case 'viewother' : $content = $this->show_other_content();
                        break;
        }

        return $header.$groups.$title.$content.$footer;
    }
    
    
    /**
     * Display the content of a submission plugin
     *
     * @return string
     */
    public function view_peers_table() {    
        global $PAGE; 
        
        $groups = '';
        $content = '';
    
        if(!$content = $this->view_peers_restricted()) {
            $cm = $this->assignment->get_course_module();
            $renderer = $this->assignment->get_renderer('assignsubmission_peers');
            $renderer = $PAGE->get_renderer('assignsubmission_peers');
            
            $params = array('id'=>$cm->id,
                    'action'=>'viewpluginpage',
                    'plugin'=>'peers',
                    'pluginsubtype'=>'assignsubmission',
                    'pluginaction'=>'table',
                    );
            $groups = groups_print_activity_menu($cm, new moodle_url('/mod/assign/view.php', $params), true);            
            //onchange='this.form.submit() 
            $groups = str_replace('name="group"', ' onchange="this.form.submit()"  name="group"', $groups);

            $perpage = get_user_preferences('assign_perpage', 200);
            $filter = ''; //$assign->get_user_filter_preferences(); //get_user_preferences('assign_filter', '');
          
            $table = new assignsubmission_peers_table($this->assignment, $perpage, $filter, 0, false);
            $content = $renderer->render($table);
            $content = str_replace('assignsubmission_file/submission_files', 'assignsubmission_peers/submission_files', $content);
            $content = str_replace('mod/assign/view.php?id=', 'mod/assign/submission/peers/view.php?id=', $content);
            $event = \assignsubmission_peers\event\peers_table_viewed::create_from_assign($this->assignment);
            $event->trigger();

            $content .= $renderer->continue_button(new moodle_url('/mod/assign/view.php', array('id' => $cm->id)));
        }
        
        return array($groups, $content);
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
     * Display the content of a submission plugin
     *
     * @return string
     */
    public function show_other_content() {
    
        $content = '';

        if(!$content = $this->view_peers_restricted()) {
        
            $submissionid = optional_param('sid', 0, PARAM_INT);
            $plugintype = required_param('alien', PARAM_TEXT);
            $item = null;
            $plugin = $this->assignment->get_submission_plugin_by_type($plugintype);
            if ($submissionid <= 0) {
                throw new coding_exception('Submission id should not be 0');
            }
            $item = $this->get_submission($submissionid);

            $content = $this->assignment->get_renderer()->render(new assign_submission_plugin_submission($plugin,
                                                                $item,
                                                                assign_submission_plugin_submission::FULL,
                                                                $this->assignment->get_course_module()->id,
                                                                '',
                                                                array()));
                                                                //$this->assignment->get_return_action(),
                                                                //$this->assignment->get_return_params()));
            // Trigger event for viewing a submission.
            \mod_assign\event\submission_viewed::create_from_submission($this->assignment, $item)->trigger();
        }

        $params = array('id'=>$this->assignment->get_course_module()->id,
                        'plugin'=>$this->get_type(), 'pluginsubtype'=>$this->get_subtype(),
                        'action'=>'viewpluginpage', 'pluginaction'=>'table');
        $url = new moodle_url('/mod/assign/view.php', $params);
        $url->set_anchor('selectuser_'.$item->userid);
        $content .= $this->assignment->get_renderer()->single_button($url, get_string('back'), 'get');
        
        return $content;
    }
    
}
