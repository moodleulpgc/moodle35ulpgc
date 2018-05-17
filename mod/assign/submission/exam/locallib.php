<?php
// This exam is part of Moodle - http://moodle.org/
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
 * This file contains the definition for the library class for exam submission plugin
 *
 * This class provides all the functionality for the new assign module.
 *
 * @package    assignsubmission_exam
 * @copyright 2014 Enrique Castro, ecastro  @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/eventslib.php');

defined('MOODLE_INTERNAL') || die();

// exam areas for exam submission assignment.
define('ASSIGNSUBMISSION_EXAM_MAXEXAMS', 20);
define('ASSIGNSUBMISSION_EXAM_MAXSUMMARYEXAMS', 5);
define('ASSIGNSUBMISSION_EXAM_FILEAREA', 'submission_exams');

/**
 * Library class for exam submission plugin extending submission plugin base class
 *
 * @package    assignsubmission_exam
 * @copyright 2014 Enrique Castro, ecastro  @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_exam extends assign_submission_plugin {

    /**
     * Get the name of the exam submission plugin
     * @return string
     */
    public function get_name() {
        return get_string('exam', 'assignsubmission_exam');
    }

    /**
     * Get exam submission information from the database
     *
     * @param int $submissionid
     * @return mixed
     */
    private function get_exam_submission($submissionid) {
        global $DB;
        return $DB->get_record('assignsubmission_exam', array('submission'=>$submissionid));
    }

    /**
     * Get the default setting for exam submission plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE;

        return;

        $defaultmaxfilesubmissions = $this->get_config('maxfilesubmissions');
        $defaultmaxsubmissionsizebytes = $this->get_config('maxsubmissionsizebytes');

        $settings = array();
        $options = array();
        for ($i = 1; $i <= ASSIGNSUBMISSION_EXAM_MAXEXAMS; $i++) {
            $options[$i] = $i;
        }

        $name = get_string('maxfilesubmission', 'assignsubmission_exam');


/*

        $mform->addElement('select', 'ASSIGNSUBMISSION_EXAM_MAXEXAMS', $name, $options);
        $mform->addHelpButton('ASSIGNSUBMISSION_EXAM_MAXEXAMS',
                              'maxfilesubmission',
                              'assignsubmission_exam');
        $mform->setDefault('ASSIGNSUBMISSION_EXAM_MAXEXAMS', $defaultmaxfilesubmissions);
        $mform->disabledIf('ASSIGNSUBMISSION_EXAM_MAXEXAMS', 'assignsubmission_exam_enabled', 'notchecked');

        $choices = get_max_upload_sizes($CFG->maxbytes,
                                        $COURSE->maxbytes,
                                        get_config('assignsubmission_exam', 'maxbytes'));

        $settings[] = array('type' => 'select',
                            'name' => 'maxsubmissionsizebytes',
                            'description' => get_string('maximumsubmissionsize', 'assignsubmission_exam'),
                            'options'=> $choices,
                            'default'=> $defaultmaxsubmissionsizebytes);

        $name = get_string('maximumsubmissionsize', 'assignsubmission_exam');
        $mform->addElement('select', 'assignsubmission_exam_maxsizebytes', $name, $choices);
        $mform->addHelpButton('assignsubmission_exam_maxsizebytes',
                              'maximumsubmissionsize',
                              'assignsubmission_exam');
        $mform->setDefault('assignsubmission_exam_maxsizebytes', $defaultmaxsubmissionsizebytes);
        $mform->disabledIf('assignsubmission_exam_maxsizebytes',
                           'assignsubmission_exam_enabled',
                           'notchecked');

        $mform->addElement('select', 'assignsubmission_exam_examregidnumber', $name, $choices);
        $mform->disabledIf('assignsubmission_exam_examregidnumber', 'assignsubmission_exam_enabled', 'notchecked');

        $mform->addElement('select', 'assignsubmission_exam_annuality', $name, $choices);
        $mform->disabledIf('assignsubmission_exam_annuality', 'assignsubmission_exam_enabled', 'notchecked');

        $mform->addElement('select', 'assignsubmission_exam_period', $name, $choices);
        $mform->disabledIf('assignsubmission_exam_period', 'assignsubmission_exam_enabled', 'notchecked');

        $mform->addElement('select', 'assignsubmission_exam_examscope', $name, $choices);
        $mform->disabledIf('assignsubmission_exam_examscope', 'assignsubmission_exam_enabled', 'notchecked');

        $mform->addElement('select', 'assignsubmission_exam_examcall', $name, $choices);
        $mform->disabledIf('assignsubmission_exam_examcall', 'assignsubmission_exam_enabled', 'notchecked');
*/
        return;
    }

    /**
     * Save the settings for exam submission plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        //$this->set_config('maxfilesubmissions', $data->ASSIGNSUBMISSION_EXAM_MAXEXAMS);
        //$this->set_config('maxsubmissionsizebytes', $data->assignsubmission_exam_maxsizebytes);
        return true;
    }

    /**
     * exam format options
     *
     * @return array
     */
    private function get_file_options() {
        $fileoptions = array('subdirs'=>1,
                                'maxbytes'=>$this->get_config('maxsubmissionsizebytes'),
                                'maxfiles'=>$this->get_config('maxfilesubmissions'),
                                'accepted_types'=>'*',
                                'return_types'=>FILE_INTERNAL);
        return $fileoptions;
    }

    /**
     * Add elements to submission form
     *
     * @param mixed $submission stdClass|null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return bool
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {

        if ($this->get_config('maxfilessubmissions') <= 0) {
            return false;
        }

        $fileoptions = $this->get_file_options();
        $submissionid = $submission ? $submission->id : 0;

        $data = file_prepare_standard_filemanager($data,
                                                  'exams',
                                                  $fileoptions,
                                                  $this->assignment->get_context(),
                                                  'assignsubmission_exam',
                                                  ASSIGNSUBMISSION_EXAM_FILEAREA,
                                                  $submissionid);
        $mform->addElement('filemanager', 'exams_filemanager', html_writer::tag('span', $this->get_name(),
            array('class' => 'accesshide')), null, $examoptions);

        return true;
    }

    /**
     * Count the number of exams
     *
     * @param int $submissionid
     * @param string $area
     * @return int
     */
    private function count_files($submissionid, $area) {

        $fs = get_file_storage();
        $files = $fs->get_area_files($this->assignment->get_context()->id,
                                     'assignsubmission_exam',
                                     $area,
                                     $submissionid,
                                     'id',
                                     false);

        return count($files);
    }

    /**
     * Save the exams and trigger plagiarism plugin, if enabled,
     * to scan the uploaded exams via events trigger
     *
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $USER, $DB;

        $fileoptions = $this->get_file_options();

        $data = file_postupdate_standard_filemanager($data,
                                                     'exams',
                                                     $fileoptions,
                                                     $this->assignment->get_context(),
                                                     'assignsubmission_exam',
                                                     ASSIGNSUBMISSION_EXAM_FILEAREA,
                                                     $submission->id);

        $examsubmission = $this->get_exam_submission($submission->id);

        // Plagiarism code event trigger when exams are uploaded.

        $fs = get_file_storage();
        $files = $fs->get_area_files($this->assignment->get_context()->id,
                                     'assignsubmission_exam',
                                     ASSIGNSUBMISSION_EXAM_FILEAREA,
                                     $submission->id,
                                     'id',
                                     false);

        $count = $this->count_files($submission->id, ASSIGNSUBMISSION_EXAM_FILEAREA);

        $params = array(
            'context' => context_module::instance($this->assignment->get_course_module()->id),
            'objectid' => $submission->id,
            'other' => array(
                'content' => '',
                'pathnamehashes' => array_keys($files)
            )
        );
        $event = \assignsubmission_exam\event\assessable_uploaded::create($params);
        $event->set_legacy_exams($files);
        $event->trigger();

        if ($examsubmission) {
            $examsubmission->numfiles = $this->count_files($submission->id,
                                                           ASSIGNSUBMISSION_EXAM_FILEAREA);
            return $DB->update_record('assignsubmission_exam', $examsubmission);
        } else {
            $examsubmission = new stdClass();
            $examsubmission->numfiles = $this->count_files($submission->id,
                                                           ASSIGNSUBMISSION_EXAM_FILEAREA);
            $examsubmission->submission = $submission->id;
            $examsubmission->assignment = $this->assignment->get_instance()->id;
            return $DB->insert_record('assignsubmission_exam', $examsubmission) > 0;
        }
    }

    /**
     * Produce a list of exams suitable for export that represent this feedback or submission
     *
     * @param stdClass $submission The submission
     * @param stdClass $user The user record - unused
     * @return array - return an array of exams indexed by examname
     */
    public function get_files(stdClass $submission, stdClass $user) {
        $result = array();
        $fs = get_file_storage();

        $files = $fs->get_area_files($this->assignment->get_context()->id,
                                     'assignsubmission_exam',
                                     ASSIGNSUBMISSION_EXAM_FILEAREA,
                                     $submission->id,
                                     'timemodified',
                                     false);

        foreach ($files as $file) {
            $result[$file->get_filename()] = $file;
        }
        return $result;
    }

    /**
     * Display the list of exams  in the submission status table
     *
     * @param stdClass $submission
     * @param bool $showviewlink Set this to true if the list of exams is long
     * @return string
     */
    public function view_summary(stdClass $submission, & $showviewlink) {
        $count = $this->count_files($submission->id, ASSIGNSUBMISSION_EXAM_FILEAREA);

        // Show we show a link to view all exams for this plugin?
        $showviewlink = $count > ASSIGNSUBMISSION_EXAM_MAXSUMMARYEXAMS;
        if ($count <= ASSIGNSUBMISSION_EXAM_MAXSUMMARYEXAMS) {
            return $this->assignment->render_area_files('assignsubmission_exam',
                                                        ASSIGNSUBMISSION_EXAM_FILEAREA,
                                                        $submission->id).'files XXX TTT';
        } else {
            return get_string('countfiles', 'assignsubmission_exam', $count).'XXXXX';
        }
    }

    /**
     * No full submission view - the summary contains the list of exams and that is the whole submission
     *
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission) {
        return $this->assignment->render_area_files('assignsubmission_exam',
                                                    ASSIGNSUBMISSION_EXAM_FILEAREA,
                                                    $submission->id);
    }



    /**
     * Return true if this plugin can upgrade an old Moodle 2.2 assignment of this type
     * and version.
     *
     * @param string $type
     * @param int $version
     * @return bool True if upgrade is possible
     */
    public function can_upgrade($type, $version) {

        $uploadsingletype ='uploadsingle';
        $uploadtype ='upload';

        if (($type == $uploadsingletype || $type == $uploadtype) && $version >= 2011112900) {
            return true;
        }
        return false;
    }


    /**
     * Upgrade the settings from the old assignment
     * to the new plugin based one
     *
     * @param context $oldcontext - the old assignment context
     * @param stdClass $oldassignment - the old assignment data record
     * @param string $log record log events here
     * @return bool Was it a success? (false will trigger rollback)
     */
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, & $log) {
        global $DB;

        if ($oldassignment->assignmenttype == 'uploadsingle') {
            $this->set_config('maxfilesubmissions', 1);
            $this->set_config('maxsubmissionsizebytes', $oldassignment->maxbytes);
            return true;
        } else if ($oldassignment->assignmenttype == 'upload') {
            $this->set_config('maxfilesubmissions', $oldassignment->var1);
            $this->set_config('maxsubmissionsizebytes', $oldassignment->maxbytes);

            // Advanced exam upload uses a different setting to do the same thing.
            $DB->set_field('assign',
                           'submissiondrafts',
                           $oldassignment->var4,
                           array('id'=>$this->assignment->get_instance()->id));

            // Convert advanced exam upload "hide description before due date" setting.
            $alwaysshow = 0;
            if (!$oldassignment->var3) {
                $alwaysshow = 1;
            }
            $DB->set_field('assign',
                           'alwaysshowdescription',
                           $alwaysshow,
                           array('id'=>$this->assignment->get_instance()->id));
            return true;
        }
    }

    /**
     * Upgrade the submission from the old assignment to the new one
     *
     * @param context $oldcontext The context of the old assignment
     * @param stdClass $oldassignment The data record for the old oldassignment
     * @param stdClass $oldsubmission The data record for the old submission
     * @param stdClass $submission The data record for the new submission
     * @param string $log Record upgrade messages in the log
     * @return bool true or false - false will trigger a rollback
     */
    public function upgrade(context $oldcontext,
                            stdClass $oldassignment,
                            stdClass $oldsubmission,
                            stdClass $submission,
                            & $log) {
        global $DB;

        $examsubmission = new stdClass();

        $examsubmission->numfiles = $oldsubmission->numfiles;
        $examsubmission->submission = $submission->id;
        $examsubmission->assignment = $this->assignment->get_instance()->id;

        if (!$DB->insert_record('assignsubmission_exam', $examsubmission) > 0) {
            $log .= get_string('couldnotconvertsubmission', 'mod_assign', $submission->userid);
            return false;
        }

        // Now copy the area exams.
        $this->assignment->copy_area_files_for_upgrade($oldcontext->id,
                                                        'mod_assignment',
                                                        'submission',
                                                        $oldsubmission->id,
                                                        $this->assignment->get_context()->id,
                                                        'assignsubmission_exam',
                                                        ASSIGNSUBMISSION_EXAM_FILEAREA,
                                                        $submission->id);

        return true;
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        // Will throw exception on failure.
        $DB->delete_records('assignsubmission_exam',
                            array('assignment'=>$this->assignment->get_instance()->id));

        return true;
    }

    /**
     * Formatting for log info
     *
     * @param stdClass $submission The submission
     * @return string
     */
    public function format_for_log(stdClass $submission) {
        // Format the info for each submission plugin (will be added to log).
        $examcount = $this->count_files($submission->id, ASSIGNSUBMISSION_EXAM_FILEAREA);

        return get_string('numfilesforlog', 'assignsubmission_exam', $examcount);
    }

    /**
     * Return true if there are no submission exams
     * @param stdClass $submission
     */
    public function is_empty(stdClass $submission) {
        //return $this->count_files($submission->id, ASSIGNSUBMISSION_EXAM_FILEAREA) == 0;
        return true;
    }

    /**
     * Get exam areas returns a list of areas this plugin stores exams
     * @return array - An array of examareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        return array(ASSIGNSUBMISSION_EXAM_FILEAREA=>$this->get_name());
    }

    /**
     * Copy the student's submission from a previous submission. Used when a student opts to base their resubmission
     * on the last submission.
     * @param stdClass $sourcesubmission
     * @param stdClass $destsubmission
     */
    public function copy_submission(stdClass $sourcesubmission, stdClass $destsubmission) {
        global $DB;

        // Copy the exams across.
        $contextid = $this->assignment->get_context()->id;
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid,
                                     'assignsubmission_exam',
                                     ASSIGNSUBMISSION_EXAM_FILEAREA,
                                     $sourcesubmission->id,
                                     'id',
                                     false);
        foreach ($files as $file) {
            $fieldupdates = array('itemid' => $destsubmission->id);
            $fs->create_file_from_storedfile($fieldupdates, $file);
        }

        // Copy the assignsubmission_exam record.
        if ($examsubmission = $this->get_exam_submission($sourcesubmission->id)) {
            unset($examsubmission->id);
            $examsubmission->submission = $destsubmission->id;
            $DB->insert_record('assignsubmission_exam', $examsubmission);
        }
        return true;
    }

    /**
     * Return a description of external params suitable for uploading a exam submission from a webservice.
     *
     * @return external_description|null
     */
    public function get_external_parameters() {
        return array(
            'exams_filemanager' => new external_value(
                PARAM_INT,
                'The id of a draft area containing files for this submission.'
            )
        );
    }


    /**
     * The submission exam plugin has no submission component per se so should not be counted
     * when determining whether to show the edit submission link.
     * @return boolean
     */
    public function allow_submissions() {
        $context = $this->assignment->get_context();
        if($cangrade = has_capability('mod/assign:grade', $context)) {
            return true;
        }
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
        global $DB;
        return false;
    }

}
