<?php
// This file is part of the data submission sub plugin - http://elearningstudio.co.uk
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
 * This file contains the definition for the library class for data submission plugin
 *
 * This class provides all the functionality for the new assign module.
 *
 * @package   assignsubmission_data
 * @copyright 2017 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * library class for data submission plugin extending submission plugin base class
 *
 */
class assign_submission_data extends assign_submission_plugin {

    /** @var array fields collection of field classes */
    protected $fields = false; 
    /** @var array mapping fields id - name */
    protected $fieldnames = false;


    /**
     * Get the name of the data submission plugin
     * @return string
     */
    public function get_name($name = '') {
        if($name) {
            return $name;
        }
        return get_string('data', 'assignsubmission_data');
    }

    
    /**
     * Get data fields objects from the database
     *
     * @return array
     */
    private function load_data_fields($submission = false) {    
        global $CFG, $DB, $USER;
        
        include_once($CFG->dirroot.'/local/assigndata/lib.php');
    
        if (empty($submission->groupid) && !empty($submission->userid)) {
            // this is a user submission
            $submission->groupid = 0;
        }
        
        $fields = array();
        $fieldnames = array();
        if ($fields = $DB->get_records('local_assigndata_fields', 
                        array('course' => $this->assignment->get_course()->id, 'assignment' => $this->assignment->get_instance()->id), 'sortorder ASC')) {
            foreach ($fields  as $key => $field) {
                    $fieldobj = local_assigndata_get_field($field, $this->assignment->get_instance()->id);
                    if($submission) {
                        $dataid = $DB->get_field('local_assigndata_submission', 'id', 
                                                        array('userid' => $submission->userid, 
                                                            'assignment' => $this->assignment->get_instance()->id,
                                                            'attemptnumber' => $submission->attemptnumber,
                                                            'groupid' => $submission->groupid,  
                                                            'fieldid' => $key));
                        $fieldobj->submissionid = $dataid;
                        $fieldobj->get_content($dataid);
                    }
                    $fields[$key] = $fieldobj;
                    $fieldnames[$key] = $field->name;
            }
        }
        $this->fields = $fields; 
        $this->fieldnames = $fieldnames;
        return $fields;
    }
    

    /**
     * Get any additional fields for the submission form for this assignment.
     *
     * @param mixed $submissionorgrade submission|grade - For submission plugins this is the submission data,
     *                                                    for feedback plugins it is the grade data
     * @param MoodleQuickForm $mform - This is the form
     * @param stdClass $data - This is the form data that can be modified for example by a filemanager element
     * @param int $userid - This is the userid for the current submission.
     *                      This is passed separately as there may not yet be a submission or grade.
     * @return boolean - true if we added anything to the form
     */
    public function get_form_elements_for_user($submission, MoodleQuickForm $mform, stdClass $data, $userid) {

        $this->load_data_fields($submission);
        
        $mform->addElement('header', 'assignsummision_data', get_string('data', 'assignsubmission_data'));
        
        foreach($this->fields as $field) {
            $data = $field->content;
            if(empty($field->content)) {
                $data = $field->get_empty_content();
            }
            $field->add_submission_form_elements($mform, $data);   
        }

        $name = '';
        $plugins = $this->assignment->get_submission_plugins();
        foreach($plugins as $key => $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible() && !$plugin->is_empty($submission)) {
                $name = $plugin->get_name();
            }
        }

        if($name != $this->get_name()) {
            $mform->addElement('header', 'assignsummision_data_close', get_string('othersubmittable', 'assignsubmission_data'));        
        }
        
        return true;
    }

    /**
     * Save data to the database
     *
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $DB, $USER;

        $this->load_data_fields($submission);

        
        //prepare data for events
        $params = array(
            'context' => context_module::instance($this->assignment->get_course_module()->id),
            'courseid' => $this->assignment->get_course()->id,
            'objectid' => $submission->id,
            'other' => array(),
        );
        if (!empty($submission->userid) && ($submission->userid != $USER->id)) {
            $params['relateduserid'] = $submission->userid;
        }
        $groupname = null;
        $groupid = 0;
        // Get the group name as other fields are not transcribed in the logs and this information is important.
        if (empty($submission->userid) && !empty($submission->groupid)) {
            $groupname = $DB->get_field('groups', 'name', array('id' => $submission->groupid), '*', MUST_EXIST);
            $groupid = $submission->groupid;
            // if group submission, store real submitting user in assigndata
            //$submission->userid = -$submission->groupid ;
            
        } else {
            $params['relateduserid'] = $submission->userid;
        }
        $params['other'] = array(
                'submissionid'      => $submission->id,
                'submissionattempt' => $submission->attemptnumber,
                'submissionstatus'  => $submission->status,
                'groupid'           => $groupid,
                'groupname'         => $groupname
        );

        foreach($this->fields as $fid => $field) {
            $content = new stdClass();
            $content->userid = $submission->userid;
            $content->attemptnumber = $submission->attemptnumber;
            $content->groupid = $submission->groupid;
            
            $contentname = "field_{$fid}_content";
            foreach(array('', 1, 2, 3, 4) as $i) {
                if(isset($data->{$contentname.$i})) {
                    $content->{"content$i"} = $data->{$contentname.$i};
                }
            }
            
            $success = $field->update_content($field->submissionid, $content);

            $params['objectid'] = $success;
            if($success > 0) {
                $event = \assignsubmission_data\event\submission_created::create($params);
            } else {
                $event = \assignsubmission_data\event\submission_updated::create($params);
            }
            $event->set_assign($this->assignment);
            $event->trigger();
        }

        return true;
    }

    /**
     * Return a list of the text fields that can be imported/exported by this plugin
     *
     * @return array An array of field names and descriptions. (name=>description, ...)
     */
    public function get_editor_fields() {
        global $DB;
        
        //return array();
        
        $fields = $DB->get_records_menu('local_assigndata_fields', 
                        array('course' => $this->assignment->get_course()->id, 'assignment' => $this->assignment->get_instance()->id),
                        'sortorder ASC', 'name, description');  
    
        return $fields;
    }

    /**
     * Get the saved text content from the editor
     *
     * @param string $name
     * @param int $submissionid
     * @return string
     */
    public function get_editor_text($name, $submissionid) {
        global $DB;
        if($field =  array_search($name, $this->fieldnames)) {
            $field = $this->fields[$field];
            $content=  $field->get_content($field->submissionid);
            return $field->export_text_value($content);
        }

        return '';
    }

    /**
     * Display data values in short form 
     *
     * @param stdClass $submission
     * @param bool $showviewlink - If the summary has been truncated set this to true
     * @return string
     */
    public function view_summary(stdClass $submission, & $showviewlink) {
        $this->load_data_fields($submission);

        $content = '';
        foreach($this->fields as $fid => $field) {
            $content .= $field->get_summary_content($field->submissionid). '<br />';
        }
        
        if ($content) {
            $showviewlink = true;
            $shorttext = shorten_text($content, 140);
            if ($content != $shorttext) {
                return $shorttext . get_string('numwords', 'assignsubmission_data', count_words($content));
            } else {
                return $shorttext;
            }
        }
        return '';
    }

    /**
     * Display the saved text content from the editor in the view table
     *
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission) {
        $result = '';
        $this->load_data_fields($submission);

        $content = array();
        foreach($this->fields as $fid => $field) {
            $name =  html_writer::div($field->get_formatted_fieldname(), 'dataname'); ;
            $value =  html_writer::div($field->get_formatted_content($field->submissionid), 'datacontent'); ;
            $content[] = html_writer::div($name.$value, 'datafield');
        }
        
        $result = html_writer::div(implode("\n", $content), 'assignsubmissiondata');
        
        return $result;
    }

    
    /**
     * Formatting for log info
     *
     * @param stdClass $submission The new submission
     * @return string
     */
    public function format_for_log(stdClass $submission) {
        // format the info for each submission plugin add_to_log
        $this->load_data_fields($submission);

        $content = 0;
        foreach($this->fields as $fid => $field) {
            if($field->get_content($field->submissionid)) {
                $content ++;
            }
        }

        return get_string('numfieldsforlog', 'assignsubmission_data', $content);
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        // will throw exception on failure
        $DB->delete_records('local_assigndata_submission', array('assignment' => $this->assignment->get_instance()->id));
        $DB->delete_records('local_assigndata_fields', array('assignment' => $this->assignment->get_instance()->id));

        return true;
    }

    /**
     * No text is set for this plugin
     *
     * @param stdClass $submission
     * @return bool
     */
    public function is_empty(stdClass $submission) {
        return false;
    
        $this->load_data_fields($submission);
        
        $empty = true;
        foreach($this->fields as $field) {
            $content = $field->get_content($field->submissionid);
            if(!empty($content->content)) {
                $empty = false;
                break;
            }
        }
        return $empty;
    }

    
    /**
     * Allows hiding this plugin from the submission/feedback screen if it is not enabled.
     *
     * @return bool - if false - this plugin will not accept submissions / feedback
     */
    public function is_enabled() {
        if(!get_config('local_assigndata', 'enabledassigndata')) {
            $this->enabledcache = false;
        } else {
            $this->enabledcache = parent::is_enabled();
        }
        return $this->enabledcache;
    }
    
    /**
     * If this plugin should not include a column in the grading table or a row on the summary page
     * then return false
     *
     * @return bool
     */
    public function has_user_summary() {
        if(!$this->fields) {
            $this->load_data_fields();
        }
        return !empty($this->fields);
    }
    
    
    /**
     * Copy the student's submission from a previous submission. Used when a student opts to base their resubmission
     * on the last submission.
     * @param stdClass $sourcesubmission
     * @param stdClass $destsubmission
     */
    public function copy_submission(stdClass $sourcesubmission, stdClass $destsubmission) {
        global $DB;

        // Copy the assignsubmission_data records.
        $datacontents = $DB->get_records('local_assigndata_submission', array('assignment' => $this->assignment->get_instance()->id,
                                                                                'userid'=> $sourcesubmission->userid,
                                                                                'attemptnumber' => $sourcesubmission->attemptnumber), MUST_EXIST);
        if ($datacontents) {
            foreach($datacontents as $datasub) {
                $oldsub = $datasub->id;
                unset($datasub->id);
                $datasub->attemptnumber = $destsubmission->attemptnumber;
                $newsub = $DB->insert_record('local_assigndata_submission', $datasub);
            }
        }
        return true;
    }
    
     
    /**
     * This allows a plugin to render an introductory section which is displayed
     * right below the activity's "intro" section on the main assignment page.
     *
     * @return string
     */
    public function view_header() {
        global $DB, $OUTPUT;
        
        $context = $this->assignment->get_context();
        if(has_capability('local/assigndata:manage', $context)) {
            if(!$fields = $DB->count_records('local_assigndata_fields', 
                            array('assignment' => $this->assignment->get_instance()->id))) {

                $url = new moodle_url('/local/assigndata/view.php', array('id' => $this->assignment->get_course_module()->id));
                $text = get_string('nofields', 'assignsubmission_data');
                $text .= '<br /> &nbsp; &nbsp;'.html_writer::link($url, get_string('managemetadata', 'local_assigndata'));
                return $OUTPUT->box($text, 'box generalbox boxaligncenter assignsubmission_data alert-error');  
            }
        }
        
        return '';
    }
    
}

