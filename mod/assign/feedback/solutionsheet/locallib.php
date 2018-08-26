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
 * This file contains the definition for the library class for the solution sheet feedback plugin
 *
 * @package   assignfeedback_solutionsheet
 * @copyright 2016 Henning Bostelmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('ASSIGNFEEDBACK_SOLUTIONSHEET_FILEAREA', 'solutionsheet');

/**
 * Library class for solutionsheet feedback plugin extending feedback plugin base class.
 *
 * @package   assignfeedback_solutionsheet
 * @copyright 2016 Henning Bostelmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_feedback_solutionsheet extends assign_feedback_plugin {

    /**
     * Get the name of this plugin.
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_solutionsheet');
    }

    /**
     * Get a list of file areas associated with the plugin configuration.
     * This is used for backup/restore.
     *
     * @return array names of the fileareas, can be an empty array
     */
    public function get_config_file_areas() {
        return array(ASSIGNFEEDBACK_SOLUTIONSHEET_FILEAREA);
    }

    /**
     * Get file areas returns a list of areas this plugin stores files
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        return array(ASSIGNFEEDBACK_SOLUTIONSHEET_FILEAREA => $this->get_name());
    }

    /**
     * Count the number of solution sheets.
     *
     * @return int
     */
    private function count_files() {

        $fs = get_file_storage();
        $files = $fs->get_area_files($this->assignment->get_context()->id,
                        'assignfeedback_solutionsheet', ASSIGNFEEDBACK_SOLUTIONSHEET_FILEAREA,
                        0, 'id', false);

        return count($files);
    }

    /**
     * Get the settings for the solutionsheet plugin in the "edit module" form;
     * that is, provide a means of uploading a solution sheet
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $PAGE;

        $defaultshowattype = $this->get_config('showattype');
        $defaultshowattime = $this->get_config('showattime');
        $defaultrequiresubmission = $this->get_config('requiresubmission');
        $defaulthideafter = $this->get_config('hideafter');

        $mform->addElement('filemanager', 'assignfeedback_solutionsheet_upload',
                        get_string('uploadsolutionsheets', 'assignfeedback_solutionsheet'),
                        null, array('subdirs' => 0) );
        $mform->hideIf('assignfeedback_solutionsheet_upload', 'assignfeedback_solutionsheet_enabled', 'notchecked');

        $showatgroup = array();
        $showatgroup[] = $mform->createElement('radio', 'assignfeedback_solutionsheet_showattype', null, get_string('no'), 0);

        if ($this->should_display_yesimmediate()) {
            $PAGE->requires->js_call_amd('assignfeedback_solutionsheet/settings_form', 'init');
            $showatgroup[] = $mform->createElement('radio', 'assignfeedback_solutionsheet_showattype', null,
                get_string('yesimmediate', 'assignfeedback_solutionsheet'), 1);
        }

        $showatgroup[] = $mform->createElement('radio', 'assignfeedback_solutionsheet_showattype', null,
                                                get_string('yesfromprefix', 'assignfeedback_solutionsheet'), 2);
        $showatgroup[] = $mform->createElement('duration', 'assignfeedback_solutionsheet_showattime', '');
        $showatgroup[] = $mform->createElement('static', 'assignfeedback_solutionsheet_showatpost', '',
                                                get_string('yesfromsuffix', 'assignfeedback_solutionsheet'));
        $mform->addGroup($showatgroup, 'showatgroup',
                         get_string('showsolutions', 'assignfeedback_solutionsheet'), '&nbsp;&nbsp;', false);

        $mform->setDefault('assignfeedback_solutionsheet_showattype', $defaultshowattype);
        $mform->setDefault('assignfeedback_solutionsheet_showattime', $defaultshowattime);
        $mform->hideIf('showatgroup',
                'assignfeedback_solutionsheet_enabled', 'notchecked');
        $mform->disabledIf('assignfeedback_solutionsheet_showattime[number]',
                           'assignfeedback_solutionsheet_showattype', 'neq', '2');
        $mform->disabledIf('assignfeedback_solutionsheet_showattime[timeunit]',
                           'assignfeedback_solutionsheet_showattype', 'neq', '2');

        $mform->addElement('advcheckbox', 'assignfeedback_solutionsheet_requiresubmission',
                        get_string('requiresubmission', 'assignfeedback_solutionsheet'));
        $mform->addHelpButton('assignfeedback_solutionsheet_requiresubmission',
                              'requiresubmission', 'assignfeedback_solutionsheet');
        $mform->setDefault('assignfeedback_solutionsheet_requiresubmission', $defaultrequiresubmission);
        $mform->hideIf('assignfeedback_solutionsheet_requiresubmission',
                       'assignfeedback_solutionsheet_enabled', 'notchecked');

        $mform->addElement('date_time_selector', 'assignfeedback_solutionsheet_hideafter',
                        get_string('hidesolutionsafter', 'assignfeedback_solutionsheet'),
                        array ('optional' => true) );
        $mform->setDefault('assignfeedback_solutionsheet_hideafter', $defaulthideafter);
        $mform->hideIf('assignfeedback_solutionsheet_hideafter',
                'assignfeedback_solutionsheet_enabled', 'notchecked');
    }

    /**
     * Check if we should display "yesimmediate" radio option.
     *
     * @return bool|mixed
     * @throws \dml_exception
     */
    protected function should_display_yesimmediate() {
        if ($this->is_updating_assignment()) {
            return true;
        }

        return get_config('assignfeedback_solutionsheet', 'fromnowon');
    }

    /**
     * Check whether students must make a submission before viewing the solutions.
     *
     * @return bool
     */
    protected function require_submission_to_view() {
        return $this->get_config('requiresubmission');
    }

    /**
     * Check if we are updating an assignment.
     *
     * @return bool
     */
    protected function is_updating_assignment() {
        return $this->assignment->has_instance();
    }

    /**
     * Allows the plugin to update the defaultvalues passed in to
     * the settings form (needed to set up draft areas for editor
     * and filemanager elements)
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        $ctx = $this->assignment->get_context();
        $ctxid = $ctx ? $ctx->id : 0;
        $draftitemid = file_get_submitted_draft_itemid('assignfeedback_solutionsheet_upload');
        file_prepare_draft_area($draftitemid, $ctxid, 'assignfeedback_solutionsheet',
                                ASSIGNFEEDBACK_SOLUTIONSHEET_FILEAREA, 0, array('subdirs' => 0));
        $defaultvalues['assignfeedback_solutionsheet_upload'] = $draftitemid;
    }

    /**
     * The assignment subtype is responsible for saving it's own settings as the database table for the
     * standard type cannot be modified.
     *
     * @param stdClass $formdata - the data submitted from the form
     * @return bool - on error the subtype should call set_error and return false.
     */
    public function save_settings(stdClass $formdata) {
        file_save_draft_area_files($formdata->assignfeedback_solutionsheet_upload, $this->assignment->get_context()->id,
        'assignfeedback_solutionsheet', ASSIGNFEEDBACK_SOLUTIONSHEET_FILEAREA, 0);
        $this->set_config('showattype', $formdata->assignfeedback_solutionsheet_showattype);
        $this->set_config('showattime', $formdata->assignfeedback_solutionsheet_showattime);
        $this->set_config('requiresubmission', $formdata->assignfeedback_solutionsheet_requiresubmission);
        $this->set_config('hideafter', $formdata->assignfeedback_solutionsheet_hideafter);
        return true;
    }

    /**
     * Display the list of solution sheets.
     *
     * @return string
     */
    public function view_header() {
        $o = '';
        $renderer = $this->assignment->get_renderer();
        $context = $this->assignment->get_context();

        if ($this->count_files() > 0) {
            $o .= $renderer->heading(get_string('solutions', 'assignfeedback_solutionsheet'), 3);
            $o .= $renderer->box_start();
            $canview = $this->can_view_solutions();
            $canviewanytime = has_capability('assignfeedback/solutionsheet:viewsolutionanytime', $context);
            if ($canview) {
                // Print links to the solution sheets.
                $s = $this->assignment->render_area_files('assignfeedback_solutionsheet',
                                ASSIGNFEEDBACK_SOLUTIONSHEET_FILEAREA, 0);
                $classes = 'solutionsheet';
                if (!$this->can_students_view_solutions()) {
                    $classes .= ' greyedout';
                }
                $o .= html_writer::div($s, $classes);
            }
            if ($this->can_students_view_solutions()) {
                // If students can see the solutions, we may want to hide them.
                if (has_capability('assignfeedback/solutionsheet:releasesolution', $context)) {
                    $o .= html_writer::div(
                                $renderer->render($this->get_solutions_showhide_link(false)),
                                'solutionshowhide');
                }
            } else {
                // If students can't see the solutions, print a notice to teachers, and possibly a "show" link.
                if ($canview && !$this->is_solution_hidden_again()) {
                    $s = get_string('solutionsnotforstudents', 'assignfeedback_solutionsheet');
                    if (has_capability('assignfeedback/solutionsheet:releasesolution', $context)) {
                        $s .= $renderer->render($this->get_solutions_showhide_link(true));
                    }
                    $o .= html_writer::div($s, 'solutionshowhide');
                }
            }
            // Print a notice to students as to when solutions will be available.
            $msg = '';
            if ($this->is_solution_hidden_again()) {
                $msg = get_string('solutionsnolonger', 'assignfeedback_solutionsheet');
            } else {
                $avail = $this->get_solution_availability_time();
                if ($avail == -1) {
                    $msg = get_string('solutionsnotyet', 'assignfeedback_solutionsheet');
                } else if ($avail > 0) {
                    $availtext = userdate($avail);
                    $msg = get_string('solutionsfrom', 'assignfeedback_solutionsheet', $availtext);
                }
                if ($this->require_submission_to_view() && (!$canview || $canviewanytime)) {
                    $msg .= ' ' . get_string('requiresubmissionmsg', 'assignfeedback_solutionsheet');
                }
            }
            if ($msg) {
                $o .= html_writer::tag('p', $msg);
            }
            $o .= $renderer->box_end();
        }

        return $o;
    }

    /**
     * Determine whether the current user can view solution sheets in the current context.
     *
     * @return boolean whether the current user can view solution sheets in the current context
     */
    public function can_view_solutions() {
        global $USER;

        $context = $this->assignment->get_context();
        $canview = false;
        if (has_capability('assignfeedback/solutionsheet:viewsolutionanytime', $context)) {
            $canview = true;
        } else if (has_capability('assignfeedback/solutionsheet:viewsolution', $context)) {
            $canview = $this->can_students_view_solutions();
            if ($canview && $this->require_submission_to_view()) {
                $uinfo = $this->assignment->get_participant($USER->id);
                $canview = $uinfo && $uinfo->submitted;
            }
        }
        return $canview;
    }

    /**
     * Determine whether students can view the solution sheet.
     *
     * This is the case if the availability date has passed,
     * but the "hide after" date is not yet passed.
     *
     * @return boolean whether students can view the solution sheet.
     */
    protected function can_students_view_solutions() {
        $canview = $this->is_solution_already_available() && !$this->is_solution_hidden_again();
        return $canview;
    }

    /**
     * Get the time at which the solution sheet for this assignment will be available.
     *
     * The function returns a unix timestamp.
     * As special values, "0" means immediate availability,
     * and "-1" means that the solutions will never be available.
     *
     * @return int availablility time (unix timestamp)
     */
    protected function get_solution_availability_time() {

        $type = (int) $this->get_config('showattype');
        $time = (int) $this->get_config('showattime');
        // If in doubt, hide.
        $availtime = -1;

        if ($type == 0) {
            // Solutions are invisible forever.
            $availtime = -1;
        } else if ($type == 1) {
            // Solutions are available immediately.
            $availtime = 0;
        } else if ($type == 2) {
            // Time counting from the deadline.
            $assignrec = $this->assignment->get_instance();
            if ($assignrec) {
                $deadline = $assignrec->duedate;
                if ($deadline > 0) {
                    // TODO Do extensions need to be taken into account?
                    $availtime = $deadline + $time;
                }
            }
        }
        return $availtime;
    }


    /**
     * Determines whether the solution availability time has passed.
     * (This does _not_ account for the "hide solution after" date.)
     *
     * @return boolean whether the solution is already available.
     */
    protected function is_solution_already_available() {
        $availtime = $this->get_solution_availability_time();
        $result = false;
        if ($availtime >= 0) {
            $result = ($availtime < time());
        }
        return $result;
    }

    /**
     * Determine whether the solution "hide after" time has passed.
     *
     * @return boolean whether the solution is hidden again.
     */
    protected function is_solution_hidden_again() {
        $hidetime = $this->get_config('hideafter');
        $result = false;
        if ($hidetime > 0) {
            $result = ($hidetime < time());
        }
        return $result;
    }

    /**
     * Generate show / hide link for sultions.
     *
     * @param bool $showit Determine which link to generate.
     * @return moodle_url The show/ hide link.
     */
    private function get_solutions_showhide_link ($showit) {
        $params = array('cmid' => $this->assignment->get_course_module()->id,
                        'show' => $showit,
                        'sesskey' => sesskey() );
        $url = new moodle_url('/mod/assign/feedback/solutionsheet/showsolutions.php', $params);

        if ($showit) {
            $stringid = 'doshowsolutions';
            $confirmid = 'confirmshowsolutions';
        } else {
            $stringid = 'dohidesolutions';
            $confirmid = 'confirmhidesolutions';
        }

        $text = get_string($stringid, 'assignfeedback_solutionsheet');
        $action = new confirm_action(get_string($confirmid, 'assignfeedback_solutionsheet'));

        return new action_link($url, $text, $action);
    }

    /**
     * Do not show this plugin in the grading table or on the front page.
     *
     * @return bool
     */
    public function has_user_summary() {
        return false;
    }
    
    
    /**
     * Has the feedback file been modified?
     *
     * @param stdClass $grade Grade object.
     * @param stdClass $data Form data.
     * @return boolean True if the file area has been modified, else false.
     */
    public function is_feedback_modified(stdClass $grade, stdClass $data) { // ecastro ULPGC
        global $USER;
        return false;
    }
}

