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
 * This file contains the definition for the library class for Historic feedback plugin
 *
 * @package   assignfeedback_historic
 * @copyright 2014 Enrique Castro, ecastro  @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Library class for historic feedback plugin extending feedback plugin base class.
 *
 * @package   assignfeedback_historic
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_feedback_historic extends assign_feedback_plugin {

    /**
     * Get the name of the online historic feedback plugin.
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_historic');
    }

    /**
     * Get the codename of the datatypes used in historic feedback plugin.
     * @return string
     */
    public function get_datatypes() {
        global $DB;

        $config = get_config('assignfeedback_historic');
        list($insql, $params) = $DB->get_in_or_equal(explode(',',$config->datatypes));
        $select = " id $insql ";
        return $DB->get_fieldset_select('assignfeedback_historic_type', 'type', $select, $params);
    }

    /**
     * Get the codename of the datatypes used in historic feedback plugin.
     * @return string
     */
    public function get_datatypes_names() {
        global $DB;

        $config = get_config('assignfeedback_historic');
        list($insql, $params) = $DB->get_in_or_equal(explode(',',$config->datatypes));
        $select = " id $insql ";
        return $DB->get_records_select_menu('assignfeedback_historic_type', $select, $params, 'type ASC', 'type, name');
    }



    /**
     * Get the current historic annuality from database or next/prev
     *
     * @param int $current 0 for default in config
     * @param int $next positive for next annuality, negative for previous
     * @return string annuality
     */
    public function get_annuality($current=0, $next=0) {

        if(!$current) {
            $current = get_config('assignfeedback_historic', 'annuality');
        }
        if(!$current) {
            $current = getdate(time())['year'];
            $current = $current.(substr($current, -2, 2) + 1);
        }
        
        $annuality = $current;

        if($next != 0) {
            $first = substr($annuality, 0, 4);
            $last = substr($annuality, -2);
            /*
            if($next > 0) {
                $first += 1;
                $last += 1;
            } elseif($next < 0) {
                $first += -1;
                $last += -1;
            }
            */
            $first += $next;
            $last += $next;

            $annuality = substr_replace($annuality, $last, -2);
            $annuality = substr_replace($annuality, $first, 0, 4);
        }
        return $annuality;
    }


    /**
     * Get the live annualities from default admin settings
     *
     * @param int $gradeid
     * @return array annualities array
     */
    public function get_current_annualities() {
        $annualities = array();
        $annuality = 0;
        $agespan = get_config('assignfeedback_historic', 'agespan');
        for($annindex = 0; $annindex <= $agespan; $annindex++) {
            $ann = $this->get_annuality($annuality, -$annindex);
            $annualities[$ann] = $ann;
        }
        return $annualities;
    }


    /**
     * Get the feedback historic from the database.
     *
     * @param int $gradeid
     * @return stdClass|false The feedback historic for the given grade if it exists.
     *                        False if it doesn't.
     */
    public function get_feedback_historic($gradeid, $annuality='', $create=false) {
        global $DB;
        $historic = $DB->get_record('assignfeedback_historic', array('grade'=>$gradeid));

        $datatypes = $this->get_datatypes();
        $course = $this->assignment->get_course();

        if(!$historic && !$create) {
            return $historic;
        }

        if(!$historic ) {
            $historic = new stdClass;
            $historic->grade = $gradeid;
            $historic->assignment = $this->assignment->get_instance()->id;
            $historic->historic = array();
            $userid = $DB->get_field('assign_grades', 'userid', array('id'=>$gradeid));
            $historic->useridnumber = $DB->get_field('user', 'idnumber', array('id'=>$userid));
        } else {
            $historic->historic = array();

            list($insql, $params) = $DB->get_in_or_equal($datatypes, SQL_PARAMS_NAMED, 'dt');
            $select = " datatype $insql AND useridnumber = :useridnumber AND courseidnumber = :courseidnumber AND annuality = :annuality ";
            $params['useridnumber'] = $historic->useridnumber;
            $params['courseidnumber'] = $course->shortname;

            $annualities = array();
            if(!$annuality ) {
                $annuality = $this->get_annuality();
            } elseif($annuality == 'all') {
                $sql = "SELECT id, annuality
                            FROM {assignfeedback_historic_data}
                            WHERE courseidnumber = ?
                            GROUP BY annuality
                            ORDER BY annuality DESC ";
                $annualities = $DB->get_records_sql_menu($sql, array($params['courseidnumber']));
            } elseif($annuality) {
                $annualities = array($annuality);
            }

            foreach($annualities as $annuality) {
                $params['annuality'] = $annuality;
                $historic->historic[$annuality] = $DB->get_records_select('assignfeedback_historic_data', $select, $params);
                $types = $datatypes;
                foreach($historic->historic[$annuality] as $history) {
                    $key = array_search($history->datatype, $types);
                    if($key !== false) {
                        unset($types[$key]);
                    }
                }
                if($types) {
                    foreach($types as $type) {
                        $record = new stdClass;
                        $record->annuality = $annuality;
                        $record->datatype = $type;
                        $record->grade = '';
                        $record->comment = '';
                        $record->useridnumber = $params['useridnumber'];
                        $record->courseidnumber = $params['courseidnumber'];
                        $historic->historic[$annuality][$type] = $record;
                    }
                }
            }


        }

        $annuality = $this->get_annuality();
        if(!isset($historic->historic[$annuality])) {
            foreach($datatypes as $type) {
                $record = new stdClass;
                $record->datatype = $type;
                $record->grade = '';
                $record->comment = '';
                $record->annuality = $annuality;
                $historic->historic[$annuality][] = $record;
            }
        }
        
        krsort($historic->historic);

        return $historic;
    }

    /**
     * Override to indicate a plugin supports quickgrading.
     *
     * @return boolean - True if the plugin supports quickgrading
     */
    public function supports_quickgrading() {
        return false;
    }

    /**
     * Get quickgrading form elements as html.
     *
     * @param int $userid The user id in the table this quickgrading element relates to
     * @param mixed $grade grade or null - The grade data.
     *                     May be null if there are no grades for this user (yet)
     * @return mixed - A html string containing the html form elements required for
     *                 quickgrading or false to indicate this plugin does not support quickgrading
     */
    public function get_quickgrading_html($userid, $grade) {
        return false;
    }

    /**
     * Has the plugin quickgrading form element been modified in the current form submission?
     *
     * @param int $userid The user id in the table this quickgrading element relates to
     * @param stdClass $grade The grade
     * @return boolean - true if the quickgrading form element has been modified
     */
    public function is_quickgrading_modified($userid, $grade) {
        return false;
    }

    /**
     * Save quickgrading changes.
     *
     * @param int $userid The user id in the table this quickgrading element relates to
     * @param stdClass $grade The grade
     * @return boolean - true if the grade changes were saved correctly
     */
    public function save_quickgrading_changes($userid, $grade) {
        return false;
    }

    /**
     * Return a list of the text fields that can be imported/exported by this plugin.
     *
     * @return array An array of field names and descriptions. (name=>description, ...)
     */
    public function get_editor_fields() {
        return array('historic' => get_string('pluginname', 'assignfeedback_historic'));
    }

    /**
     * Get the saved text content from the editor.
     *
     * @param string $name
     * @param int $gradeid
     * @return string
     */
    public function get_editor_text($name, $gradeid) {
        if ($name == 'historic') {
            $feedbackhistoric = $this->get_feedback_historic($gradeid);
            if ($feedbackhistoric) {
                return $feedbackhistoric->historictext;
            }
        }

        return '';
    }

    /**
     * Get the saved text content from the editor.
     *
     * @param string $name
     * @param string $value
     * @param int $gradeid
     * @return string
     */
    public function set_editor_text($name, $value, $gradeid) {
        global $DB;

        if ($name == 'historic') {
            $feedbackhistoric = $this->get_feedback_historic($gradeid);
            if ($feedbackhistoric) {
                $feedbackhistoric->historictext = $value;
                return $DB->update_record('assignfeedback_historic', $feedbackhistoric);
            } else {
                $feedbackhistoric = new stdClass();
                $feedbackhistoric->historictext = $value;
                $feedbackhistoric->historicformat = FORMAT_HTML;
                $feedbackhistoric->grade = $gradeid;
                $feedbackhistoric->assignment = $this->assignment->get_instance()->id;
                return $DB->insert_record('assignfeedback_historic', $feedbackhistoric) > 0;
            }
        }

        return false;
    }


    /**
     * Get form elements for the grading page
     *
     * @param stdClass|null $grade
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return bool true if elements were added to the form
     */
    public function get_form_elements_for_user($grade, MoodleQuickForm $mform, stdClass $data, $userid) {   //($grade, MoodleQuickForm $mform, stdClass $data) {
        global $DB;

        if (isset($grade->id) && $grade->id) {  // ecastro ULPGC
            $fbhistoric = $this->get_feedback_historic($grade->id, 'all', true);
        } else {
            $fbhistoric = $this->get_feedback_historic(0, 'all', true);
        }
        if(!$fbhistoric->useridnumber) {
            $fbhistoric->useridnumber = $DB->get_field('user', 'idnumber', array('id'=>$userid));
        }

        $datatypes = $this->get_datatypes();
        $datatypenames = $this->get_datatypes_names();
        $course = $this->assignment->get_course();
        $agespan = get_config('assignfeedback_historic', 'agespan');
        $datatypes = $this->get_datatypes();
        $currentannuality = $this->get_annuality();
        $annuality = $currentannuality;

        for($annindex = 0; $annindex <= $agespan; $annindex++) {
            $annuality = $this->get_annuality($currentannuality, -$annindex);
            if(isset($fbhistoric->historic[$annuality]) && $historics = $fbhistoric->historic[$annuality]) {
                $mform->addElement('static', "annuality[$annuality]", get_string('annuality', 'assignfeedback_historic'), $annuality);
                if($currentannuality == $annuality) {
                    foreach($historics as $historic) {
                        $type = $historic->datatype;
                        $mform->addElement('text', "hgrade[$type]", $datatypenames[$type], array('size'=>'10'));
                        $mform->setType("hgrade[$type]", PARAM_RAW);
                        $mform->setDefault("hgrade[$type]", $this->format_grade($historic->grade, 5)); //format_float($historic->grade, 5, true, true));
                        $mform->addElement('text', "hcomment[$type]", get_string('comment', 'assignfeedback_historic'), array('size'=>'60'));
                        $mform->setType("hcomment[$type]", PARAM_RAW);
                        $mform->setDefault("hcomment[$type]", $historic->comment);
                        $mform->addRule("hcomment[$type]", get_string('maxlengtherror', 'assignfeedback_historic'), 'maxlength', 255);
                    }
                } else {
                    foreach($historics as $historic) {
                        $mform->addElement('static', "hgrade{$annuality}[$type]", $datatypenames[$type], $this->format_grade($historic->grade, 5));
                        $mform->addElement('static', "hcomment{$annuality}[$type]", get_string('comment', 'assignfeedback_historic'), $historic->comment);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Saving the historic content into database.
     *
     * @param stdClass $grade
     * @param stdClass $data
     * @param bool $update, if update existing info is allowed
     * @return bool
     */
    public function save(stdClass $grade, stdClass $data, $update=true) {
        global $DB;

        $annuality = $this->get_annuality();
        $feedbackhistoric = $this->get_feedback_historic($grade->id, $annuality);
        $course = $this->assignment->get_course();
        $useridnumber = $DB->get_field('user', 'idnumber', array('id'=>$grade->userid));
        
        if(!$useridnumber) {
            $this->set_error('noidnumber');
            return false;
        }

        if (!$feedbackhistoric) {
            $feedbackhistoric = new stdClass();
            $feedbackhistoric->grade = $grade->id;
            $feedbackhistoric->assignment = $this->assignment->get_instance()->id;
            $feedbackhistoric->useridnumber = $useridnumber;
            $feedbackhistoric->id = $DB->insert_record('assignfeedback_historic', $feedbackhistoric);
        }

       return $this->set_update_historic($feedbackhistoric, $data, $update);
    }


    /**
     * Saving the historic content into database.
     *
     * @param stdClass $fbhistoric record object from table table assignfeedback_historic
     * @param stdClass $data, from user grade form
     * @param bool $update, if update existing info is allowed
     * @return bool
     */
    public function set_update_historic(stdClass $fbhistoric, stdClass $data, $update=true) {
        global $DB;

        $success = 0;
        $course = $this->assignment->get_course();
        $record = new stdClass;
        $record->annuality = $this->get_annuality();
        $record->courseidnumber = $course->shortname;
        $record->useridnumber = $fbhistoric->useridnumber;

        $params = array('annuality'=>$record->annuality, 'useridnumber'=>$record->useridnumber, 'courseidnumber'=>$record->courseidnumber);

        $datatypes = $this->get_datatypes();
        foreach($datatypes as $type) {
            if(isset($data->hgrade[$type])) {
                if(!is_numeric($data->hgrade[$type]) && (!$data->hgrade[$type] || trim($data->hgrade[$type]) == '-' ) ) {
                    $data->hgrade[$type] = -1;
                }
                if($historicdata = $DB->get_record('assignfeedback_historic_data', $params+array('datatype'=>$type))) {
                    if($update) {
                        //update
                        $historicdata->grade = grade_floatval(unformat_float($data->hgrade[$type]));
                        $historicdata->comment = $data->hcomment[$type];
                        $success = $DB->update_record('assignfeedback_historic_data', $historicdata);
                    } else {
                        $success = true;
                    }
                } else {
                    //insert
                    $record->datatype = $type;
                    $record->grade = grade_floatval(unformat_float($data->hgrade[$type]));
                    $record->comment = $data->hcomment[$type];
                    $success = ($DB->insert_record('assignfeedback_historic_data', $record) > 0) ;
                }
            }
        }
        return $success;
    }


    /**
     * Display the historic in the feedback table.
     *
     * @param stdClass $grade
     * @param bool $showviewlink Set to true to show a link to view the full feedback
     * @return string
     */
    public function view_summary(stdClass $grade, & $showviewlink) {
        $feedbackhistoric = $this->get_feedback_historic($grade->id, 'all');

        if ($feedbackhistoric) {
            $datatypenames = $this->get_datatypes_names();
            $annualities = array();
            $showviewlink = false;
            foreach($feedbackhistoric->historic as $annuality => $historics) {
                    $list = array();
                    foreach($historics as $historic) {
                        $type = $historic->datatype;
                        $comment = ($historic->comment) ? '<br />'.$historic->comment : '';
                        $short = shorten_text($comment, 56);
                        $showviewlink = ($showviewlink || ($short != $comment));
                        $list[] = $datatypenames[$type].': '.$this->format_grade($historic->grade, 1).$short;
                    }
                    $annualities[] = $annuality.html_writer::alist($list, array('class'=>' gradingtable historiclist '));
            }
            $out = html_writer::alist($annualities, array('class'=>' gradingtable annualitylist '));

            $text = format_text($out,
                                FORMAT_MOODLE,
                                array('context' => $this->assignment->get_context()));
            return $text;
        }
        return '';
    }

    /**
     * Display the historic in the feedback table.
     *
     * @param stdClass $grade
     * @return string
     */
    public function view(stdClass $grade) {
    /*
        $feedbackhistoric = $this->get_feedback_historic($grade->id, 'all');
        if ($feedbackhistoric) {
            $datatypenames = $this->get_datatypes_names();
            $annualities = array();
            foreach($feedbackhistoric->historic as $annuality => $historics) {
                    $list = array();
                    foreach($historics as $historic) {
                        $type = $historic->datatype;
                        $comment = ($historic->comment) ? '<br />&nbsp;&nbsp;&nbsp;'.$historic->comment : '';
                        $list[] = $datatypenames[$type].': '.format_float($historic->grade, 1).$comment;
                    }
                    $annualities[] = $annuality.html_writer::alist($list, array('class'=>' gradingtable historiclist '));
            }
            $out = html_writer::alist($annualities, array('class'=>' gradingtable annualitylist '));

            $text = format_text($out,
                                FORMAT_MOODLE,
                                array('context' => $this->assignment->get_context()));
            return $text;
        }
        */
        return '';
    }

    /**
     * Return true if this plugin can upgrade an old Moodle 2.2 assignment of this type
     * and version.
     *
     * @param string $type old assignment subtype
     * @param int $version old assignment version
     * @return bool True if upgrade is possible
     */
    public function can_upgrade($type, $version) {
        if (($type == 'upload' || $type == 'uploadsingle' ||
             $type == 'online' || $type == 'offline') && $version >= 2011112900) {
            return true;
        }
        return false;
    }

    /**
     * Upgrade the settings from the old assignment to the new plugin based one
     *
     * @param context $oldcontext - the context for the old assignment
     * @param stdClass $oldassignment - the data for the old assignment
     * @param string $log - can be appended to by the upgrade
     * @return bool was it a success? (false will trigger a rollback)
     */
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, & $log) {
        return true;
    }

    /**
     * Upgrade the feedback from the old assignment to the new one
     *
     * @param context $oldcontext - the database for the old assignment context
     * @param stdClass $oldassignment The data record for the old assignment
     * @param stdClass $oldsubmission The data record for the old submission
     * @param stdClass $grade The data record for the new grade
     * @param string $log Record upgrade messages in the log
     * @return bool true or false - false will trigger a rollback
     */
    public function upgrade(context $oldcontext,
                            stdClass $oldassignment,
                            stdClass $oldsubmission,
                            stdClass $grade,
                            & $log) {
        global $DB;

        $feedbackhistoric = new stdClass();
        $feedbackhistoric->historictext = $oldsubmission->submissionhistoric;
        $feedbackhistoric->historicformat = FORMAT_HTML;

        $feedbackhistoric->grade = $grade->id;
        $feedbackhistoric->assignment = $this->assignment->get_instance()->id;
        if (!$DB->insert_record('assignfeedback_historic', $feedbackhistoric) > 0) {
            $log .= get_string('couldnotconvertgrade', 'mod_assign', $grade->userid);
            return false;
        }

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
        $DB->delete_records('assignfeedback_historic',
                            array('assignment'=>$this->assignment->get_instance()->id));
        return true;
    }

    /**
     * Returns true if there are no feedback historic for the given grade.
     *
     * @param stdClass $grade
     * @return bool
     */
    public function is_empty(stdClass $grade) {
        return $this->view($grade) == '';
    }

    /**
     * Check to see if the grade feedback has been modified from a form input in this plugin.
     *
     * @param stdClass $grade Grade object.
     * @param stdClass $data Data from the form submission (not used).
     * @return boolean True if the pdf has been modified, else false.
     */
    public function is_feedback_modified(stdClass $grade, stdClass $data) {
    
        $annuality = $this->get_annuality();
        $feedbackhistoric = $this->get_feedback_historic($grade->id, $annuality);
        if(isset($feedbackhistoric->historic[$annuality])) {
            $hgrade = array();
            $hcomment = array();
            foreach($feedbackhistoric->historic[$annuality] as $item) {
                $hgrade[$item->datatype] = $item->grade;
                $hcomment[$item->datatype] = $item->comment;
            }

            if(($data->hgrade != $hgrade) || ($data->hcomment != $hcomment)) {
                return true;
            }
        } else {
            return true;
        }
        
        return false;
    }
  
    
    /**
     * This is a hack to avoid usage of historic in module config form;
     * transform form elements to prevent use by non-allowed users
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @param array $pluginsenabled A list of form elements to be added to a group.
     *                              These are the enabledplugins.
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform, & $pluginsenabled = null) {
        if($pluginsenabled && !has_capability('assignfeedback/historic:manage', $this->assignment->get_context())) {
            $name = $this->get_subtype() . '_' . $this->get_type() . '_enabled';
            foreach($pluginsenabled as $i => $element) {
                if($element->getName() == $name) {
                    if(!$value = $this->get_config('enabled')) {
                        $pluginsenabled[$i] = $mform->createElement('hidden', $name, 0);
                        $mform->setType($name, PARAM_BOOL);
                        unset($pluginsenabled[$i+1]);
                    } else {
                        $element->freeze();
                    }
                }
            }
        }
    }

    /**
     * Return a list of the batch grading operations performed by this plugin.
     * This plugin supports batch copy to/from other Assign grades.
     *
     * @return array The list of batch grading operations
     */
    public function get_grading_batch_operations() {
        return array('copyfrom'=>get_string('copyfrom', 'assignfeedback_historic'),
                     'copyto'=>get_string('copyto', 'assignfeedback_historic'),

                     );
    }


    /**
     * User has chosen a custom grading batch operation and selected some users.
     *
     * @param string $action - The chosen action
     * @param array $users - An array of user ids
     * @return string - The response html
     */
    public function grading_batch_operation($action, $users) {

        if ($action == 'copyfrom') {
            return $this->view_batch_copyfrom($users);
        } elseif($action == 'copyto') {
            return $this->view_batch_copyto($users);
        }
        return '';
    }


    /**
     * Return a list of the grading actions performed by this plugin.
     * This plugin supports import/export.
     *
     * @return array The list of grading actions
     */
    public function get_grading_actions() {
        return array('import'=>get_string('import', 'assignfeedback_historic'),
                     'export'=>get_string('export', 'assignfeedback_historic'),
                     'setdefault'=>get_string('setdefault', 'assignfeedback_historic'),
                     );
            // if manager, then add g
        }


    /**
     * Called by the assignment module when someone chooses something from the
     * grading navigation or batch operations list.
     *
     * @param string $action - The page to view
     * @return string - The html response
     */
    public function view_page($action) {
        if ($action == 'copyfrom') {
            $users = required_param('selectedusers', PARAM_SEQUENCE);
            return $this->view_batch_copyfrom(explode(',', $users));
        }
        if ($action == 'copyto') {
            $users = required_param('selectedusers', PARAM_SEQUENCE);
            return $this->view_batch_copyto(explode(',', $users));
        }

        if ($action == 'import') {
            return $this->import_historic();
        }

        if ($action == 'export') {
            return $this->download_historic();
        }

        if ($action == 'setdefault') {
            return $this->set_default();
        }

        return '';
    }



    /**
     * Touches database to set record as modified. Allow to differentiate non-activity from not done
     *
     */
    public function set_default() {
        global $CFG, $DB, $USER;

        $context = $this->assignment->get_context();

        require_capability('assignfeedback/historic:submit', $context);
        require_once($CFG->dirroot . '/mod/assign/feedback/historic/historicforms.php');
        require_once($CFG->dirroot . '/mod/assign/renderable.php');

        $formparams = array('cm'=>$this->assignment->get_course_module()->id,
                            'context'=>$context);

        $mform = new assignfeedback_historic_setdefault_form(null, array('assignment'=>$this->assignment,
                                                                        'params'=>$formparams));

        if ($mform->is_cancelled()) {
            redirect(new moodle_url('view.php',
                                    array('id'=>$this->assignment->get_course_module()->id,
                                          'action'=>'grading')));
            return;
        } else if ($data = $mform->get_data()) {

            $feedbackhistoric = new stdClass();
            $feedbackhistoric->grade = 0;
            $feedbackhistoric->assignment = $this->assignment->get_instance()->id;
            $feedbackhistoric->useridnumber = $USER->idnumber;
            if(!$DB->record_exists('assignfeedback_historic', get_object_vars($feedbackhistoric))) {
                $feedbackhistoric->id = $DB->insert_record('assignfeedback_historic', $feedbackhistoric);
            }

            $success = false;
            $course = $this->assignment->get_course();
            $record = new stdClass;
            $record->annuality = $this->get_annuality();
            $record->courseidnumber = $course->shortname;
            $record->useridnumber = $USER->idnumber;
            $record->datatype = '';
            $record->grade = null;
            $record->comment = '';
            if(!$DB->record_exists('assignfeedback_historic_data', get_object_vars($record))) {
                $success = $DB->insert_record('assignfeedback_historic_data', $record);
            }

            $message = '';
            if($success) {
                $message = get_string('changessaved');
                $event = \assignfeedback_historic\event\historic_set::create_from_assign($this->assignment);
                $event->trigger();
            }
            redirect(new moodle_url('view.php',
                                    array('id'=>$this->assignment->get_course_module()->id,
                                          'action'=>'grading')), $message, -1);
        } else {
            $header = new assign_header($this->assignment->get_instance(),
                                        $context,
                                        false,
                                        $this->assignment->get_course_module()->id,
                                        get_string('setdefault', 'assignfeedback_historic'));
            $o = '';
            $o .= $this->assignment->get_renderer()->render($header);
            $o .= $this->assignment->get_renderer()->render(new assign_form('batchsetdefault', $mform));
            $o .= $this->assignment->get_renderer()->render_footer();
        }

        return $o;

    }


    /**
     * Copy grades & comments from other assignmets into current annuality in historic
     *
     * @param array $users - An array of user ids
     * @return string - The response html
     */
    public function view_batch_copyfrom($users) {
        global $CFG, $DB, $USER;

        $assignmoduleid = $DB->get_field('modules', 'id', array('name'=>'assign'));
        $context = $this->assignment->get_context();
        require_capability('assignfeedback/historic:submit', $context);
        require_once($CFG->dirroot . '/mod/assign/feedback/historic/historicforms.php');
        require_once($CFG->dirroot . '/mod/assign/renderable.php');

        $formparams = array('cm'=>$this->assignment->get_course_module()->id,
                            'users'=>$users,
                            'context'=>$context);

        $usershtml = '';
        $usercount = 0;
        foreach ($users as $userid) {
            if ($usercount >= ASSIGNFEEDBACK_FILE_MAXSUMMARYUSERS) {
                $moreuserscount = count($users) - ASSIGNFEEDBACK_FILE_MAXSUMMARYUSERS;
                $usershtml .= get_string('moreusers', 'assignfeedback_historic', $moreuserscount);
                break;
            }
            $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);

            $usersummary = new assign_user_summary($user,
                                                   $this->assignment->get_course()->id,
                                                   has_capability('moodle/site:viewfullnames',
                                                   $this->assignment->get_course_context()),
                                                   $this->assignment->is_blind_marking(),
                                                   $this->assignment->get_uniqueid_for_user($user->id),
                                                   get_extra_user_fields($context));
            $usershtml .= $this->assignment->get_renderer()->render($usersummary);
            $usercount += 1;
        }

        $formparams['usershtml'] = $usershtml;

        $datatypes = $this->get_datatypes_names();

        $mform = new assignfeedback_historic_batch_copyfrom_form(null, array('assignment'=>$this->assignment,
                                                                             'params'=>$formparams,
                                                                             'datatypes'=>$datatypes));

        if ($mform->is_cancelled()) {
            redirect(new moodle_url('view.php',
                                    array('id'=>$this->assignment->get_course_module()->id,
                                          'action'=>'grading')));
            return;
        } else if ($data = $mform->get_data()) {
            // this is the action area: perform the asked action

            $copied = 0;
            $copiedassignments = array();
            // Now copy each of these files to the users feedback file area.
            foreach ($users as $userid) {
                $grade = $this->assignment->get_user_grade($userid, true);
                //$this->assignment->notify_grade_modified($grade);

                $savedata = new stdclass;
                $savedata->hgrade = array();
                $savedata->hcomment = array();

                foreach($datatypes as $type => $name) {
                    if(isset($data->source[$type]) && $instanceid = $data->source[$type]) {

                        $gradinginfo = grade_get_grades($this->assignment->get_course()->id,
                                                            'mod',
                                                            'assign',
                                                            $instanceid,
                                                            $users);
                        $gradingitem = $gradinginfo->items[0];
                        $usergrade = $gradingitem->grades[$userid];
                        $gradebookgrade = $usergrade->grade;
                        $gradepass = $gradingitem->gradepass;
                        unset($gradingitem);
                        unset($gradinginfo);

                        $copy = false;
                        switch ($data->copygrades) {
                        case 'pass' :
                            if($gradebookgrade >= $gradepass) {
                                $copy = true;
                            }
                            break;
                        case 'fail' :
                            if($gradebookgrade >= $gradepass) {
                                $copy = true;
                            }
                            break;
                        default :
                            $copy = true;
                        }

                        if($copy) {
                            $savedata->hgrade[$type] = $gradebookgrade ; // get grade
                            $savedata->hcomment[$type] = '';
                            if($data->withcomment[$type] && $usergrade->str_feedback) {
                                $savedata->hcomment[$type] = shorten_text($usergrade->str_feedback, 254);
                            }
                        }
                    }
                }
                if($this->save($grade, $savedata, $data->override)) {
                    $copiedassignments[$instanceid] = $DB->get_field('course_modules', 'id', array('course'=>$this->assignment->get_course()->id,
                                                                                            'module'=>$assignmoduleid,
                                                                                            'instance'=>$instanceid));
                    $copied += 1;
                }

            }
            if($copiedassignments) {
                foreach($copiedassignments as $cmid) {
                $event = \assignfeedback_historic\event\historic_copiedfrom::create_from_assign($this->assignment, $cmid);
                $event->trigger();

                }
            }

            $message = get_string('copyfromcopied', 'assignfeedback_historic', $copied);
            redirect(new moodle_url('view.php',
                                    array('id'=>$this->assignment->get_course_module()->id,
                                          'action'=>'grading')), $message, -1);
            return;
        } else {

            $header = new assign_header($this->assignment->get_instance(),
                                        $context,
                                        false,
                                        $this->assignment->get_course_module()->id,
                                        get_string('batchcopyfrom', 'assignfeedback_historic'));
            $o = '';
            $o .= $this->assignment->get_renderer()->render($header);
            $o .= $this->assignment->get_renderer()->render(new assign_form('batchcopyfrom', $mform));
            $o .= $this->assignment->get_renderer()->render_footer();
        }

        return $o;
    }


    /**
     * Copy grades & comments from an annuality in historic to other assignment
     *
     * @param array $users - An array of user ids
     * @return string - The response html
     */
    public function view_batch_copyto($users) {
        global $CFG, $DB, $USER;

        $context = $this->assignment->get_context();

        require_capability('mod/assign:grade', $context);
        require_capability('assignfeedback/historic:submit', $context);
        require_once($CFG->dirroot . '/mod/assign/feedback/historic/historicforms.php');
        require_once($CFG->dirroot . '/mod/assign/renderable.php');

        $formparams = array('cm'=>$this->assignment->get_course_module()->id,
                            'users'=>$users,
                            'context'=>$context);

        $usershtml = '';
        $usercount = 0;
        foreach ($users as $userid) {
            if ($usercount >= ASSIGNFEEDBACK_FILE_MAXSUMMARYUSERS) {
                $moreuserscount = count($users) - ASSIGNFEEDBACK_FILE_MAXSUMMARYUSERS;
                $usershtml .= get_string('moreusers', 'assignfeedback_historic', $moreuserscount);
                break;
            }
            $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);

            $usersummary = new assign_user_summary($user,
                                                   $this->assignment->get_course()->id,
                                                   has_capability('moodle/site:viewfullnames',
                                                   $this->assignment->get_course_context()),
                                                   $this->assignment->is_blind_marking(),
                                                   $this->assignment->get_uniqueid_for_user($user->id),
                                                   get_extra_user_fields($context));
            $usershtml .= $this->assignment->get_renderer()->render($usersummary);
            $usercount += 1;
        }

        $formparams['usershtml'] = $usershtml;

        $datatypes = $this->get_datatypes_names();

        $annualities = $this->get_current_annualities();

        $mform = new assignfeedback_historic_batch_copyto_form(null, array('assignment'=>$this->assignment,
                                                                             'params'=>$formparams,
                                                                             'datatypes'=>$datatypes,
                                                                             'annualities'=> $annualities));

        if ($mform->is_cancelled()) {
            redirect(new moodle_url('view.php',
                                    array('id'=>$this->assignment->get_course_module()->id,
                                          'action'=>'grading')));
            return;
        } else if ($data = $mform->get_data()) {
            // this is the action area: perform the asked action
            $now = time();
            $sources = $data->source;
            $override = $data->override;
            $annuality = $data->annuality;
            $course = $this->assignment->get_course();
            $copied = array();
            foreach($sources as $type => $assignind) {
                $savecoments = false;
                list($course, $cm) = get_course_and_cm_from_instance($assignind, 'assign', $course);
                $targetcontext = context_module::instance($cm->id);
                $targetassignment = new assign($targetcontext, $cm, $course);
                if(isset($data->withcomment[$type]) && $data->withcomment[$type] &&
                    $commentplugin = $targetassignment->get_feedback_plugin_by_type('comments')) {
                    if ($commentplugin->is_enabled() && $commentplugin->is_visible()) {
                        $savecoments = true;
                    }
                }
                $modified = false;
                foreach ($users as $userid) {
                    $historic = '';
                    if($hgrade = $this->assignment->get_user_grade($userid, false)) {
                        $historic = get_feedback_historic($hgrade->id, $data->annuality);
                    }
                    if(!$historic) {
                        continue;
                    }
                    $hrecord = $historic->historic[$annuality][$type];
                    if(!isset($hrecord->grade)) {
                        continue;
                    }
                    $gradevalue = $hrecord->grade;
                    $grade = $targetassignment->get_user_grade($userid, true);
                    if(!$targetassignment->grading_disabled($userid) && ($override || !isset($grade->grade) || $grade->grade < 0 )) {
                        $grade->grader = $USER->id;
                        $grade->grade = (float)$gradevalue;
                        $grade->timemodified = $now;
                        if($success = $this->assignment->update_grade($grade)) {
                            $modified = true;
                            $copied[$grade->userid] = 1;

                        }
                        // now store comment
                        if(!empty($hrecord->comment) && $savecomments) {
                            $comment = new stdClass;
                            $comment->assignfeedbackcomments_editor['text'] = $hrecord->comment;
                            $comment->assignfeedbackcomments_editor['format'] = FORMAT_MOODLE;
                            $commentplugin->save($grade, $data);
                        }
                    }
                }
                if($modified) {
                    $targetassignment->update_gradebook(true, $cm->id);
                    $instance = $targetassignment->get_instance();
                    $instance->cmid = $cm->id;
                    $instance->cmidnumber = $cm->idnumber;
                    assign_update_grades($instance);
                    $event = \assignfeedback_historic\event\historic_copiedto::create_from_assign($this->assignment, $cm->id);
                    $event->trigger();

                }
            }
            $message = get_string('copytocopied', 'assignfeedback_historic', count($copied));

            redirect(new moodle_url('view.php',
                                    array('id'=>$this->assignment->get_course_module()->id,
                                          'action'=>'grading')), $message, -1);
            return;
        } else {

            $header = new assign_header($this->assignment->get_instance(),
                                        $context,
                                        false,
                                        $this->assignment->get_course_module()->id,
                                        get_string('batchcopyfrom', 'assignfeedback_historic'));
            $o = '';
            $o .= $this->assignment->get_renderer()->render($header);
            $o .= $this->assignment->get_renderer()->render(new assign_form('batchcopyfrom', $mform));
            $o .= $this->assignment->get_renderer()->render_footer();
        }

        return $o;
    }



    /**
     * Generates a file with users and historic data
     *
     * @return string - The response html
     */
    public function download_historic($format='csv') {
        global $CFG, $DB, $USER;

        $course = $this->assignment->get_course();
        $context = $this->assignment->get_context();

        require_capability('mod/assign:grade', $context);
        require_capability('assignfeedback/historic:view', $context);
        require_once($CFG->dirroot . '/mod/assign/feedback/historic/historicforms.php');
        require_once($CFG->dirroot . '/mod/assign/renderable.php');

        $formparams = array('cm'=>$this->assignment->get_course_module()->id,
                            'context'=>$context);

        $mform = new assignfeedback_historic_export_form(null, array('assignment'=>$this->assignment,
                                                                             'params'=>$formparams));

        if ($mform->is_cancelled()) {
            redirect(new moodle_url('view.php',
                                    array('id'=>$this->assignment->get_course_module()->id,
                                          'action'=>'grading')));
            return;
        } else if ($data = $mform->get_data()) {
            $groupmode = groups_get_activity_groupmode($this->assignment->get_course_module());
            // All users.
            $groupid = 0;
            $groupname = '';
            if ($groupmode) {
                $groupid = groups_get_activity_group($this->assignment->get_course_module(), true);
                $groupname = groups_get_group_name($groupid) . '-';
            }

            if($users = get_enrolled_users($context, 'mod/assign:submit', $groupid, 'u.id, u.username, u.idnumber, u.lastname, u.firstname, u.email', 'u.lastname ASC, u.firstname ASC')) {


                $typenames = $this->get_datatypes_names();
                $separator = $data->delimiter;

                $userparams = array();
                if($data->annuality) {
                    $userparams['annuality'] = $data->annuality;
                }
                if($data->datatype) {
                    $userparams['datatype'] = $data->datatype;
                }

                $filename = clean_filename(get_string('exportfile', 'assignfeedback_historic') . '-' .
                                        $this->assignment->get_course()->shortname . '-' .
                                        $this->assignment->get_instance()->name . '-' .
                                        $groupname .
                                        $this->assignment->get_course_module()->id);
                $filename = str_replace(' ', '_', $filename); // ecastro ULPGC
                $csvexport = new csv_export_writer($separator);
                $csvexport->set_filename($filename);

                $columns = array();
                $cols = explode(',', $CFG->grade_export_userprofilefields);
                foreach($cols as $col) {
                    $col = trim($col);
                    $columns[$col] = strtr(get_string($col), ' ', '_');
                }
                $usercols = array_keys($columns);
                $cols = array('annuality','datatype','grade','comment');
                foreach($cols as $col) {
                    $columns[$col] = strtr(get_string($col, 'assignfeedback_historic'), ' ', '_');
                }

                $csvexport->add_data($columns);

                foreach($users as $user) {
                    $row = array();
                    foreach($usercols as $col) {
                        $row[$col] = $user->$col;
                    }
                    if($historics = $DB->get_records('assignfeedback_historic_data', array('courseidnumber'=>$course->shortname, 'useridnumber'=>$user->idnumber)+$userparams, 'annuality ASC, datatype ASC')) {
                        foreach($historics as $historic) {
                            $row['annuality'] = $historic->annuality;
                            $row['datatype'] = $typenames[$historic->datatype];
                            $row['grade'] = $historic->grade;
                            $row['comment'] = $historic->comment;
                            $csvexport->add_data($row);
                        }
                    } else {
                        $row['annuality'] = '';
                        $row['datatype'] = '';
                        $row['grade'] = '';
                        $row['comment'] = '';
                        $csvexport->add_data($row);
                    }
                }
                $event = \assignfeedback_historic\event\historic_exported::create_from_assign($this->assignment);
                $event->trigger();

                $csvexport->download_file();
                exit;
            }
        } else {
            $header = new assign_header($this->assignment->get_instance(),
                                        $context,
                                        false,
                                        $this->assignment->get_course_module()->id,
                                        get_string('export', 'assignfeedback_historic'));
            $o = '';
            $o .= $this->assignment->get_renderer()->render($header);
            $o .= $this->assignment->get_renderer()->render(new assign_form('exporthistoric', $mform));
            $o .= $this->assignment->get_renderer()->render_footer();
        }

        return $o;
    }



    /**
     * Reads a CSV file and applies data to current annuality of historic info
     *
     * @return string - The response html
     */
    public function import_historic() {
        global $CFG, $DB, $USER;

        $course = $this->assignment->get_course();
        $context = $this->assignment->get_context();
        $baseurl = new moodle_url('view.php', array('id'=>$this->assignment->get_course_module()->id,
                                                    'action'=>'grading'));
        $confirm = optional_param('confirm', 0, PARAM_INT);

        require_capability('assignfeedback/historic:submit', $context);
        require_once($CFG->dirroot . '/mod/assign/feedback/historic/historicforms.php');
        require_once($CFG->dirroot . '/mod/assign/renderable.php');

        $o = '';

        $formparams = array('cm'=>$this->assignment->get_course_module()->id,
                            'context'=>$context);

        $mform = new assignfeedback_historic_import_form(null, array('assignment'=>$this->assignment,
                                                                             'params'=>$formparams));

        if ($mform->is_cancelled()) {
            redirect($baseurl);
            return;
        } elseif(($data = $mform->get_data()) && ($csvdata = $mform->get_file_content('uploadfile'))) {
            /// get confirmation
            $importid = csv_import_reader::get_new_iid('assignfeedback_historic_'.$formparams['cm']);
            // File exists and was valid.
            $mform = new assignfeedback_historic_import_confirm_form(null, array('assignment'=>$this->assignment,
                                                                            'params'=>$formparams,
                                                                            'importid' => $importid,
                                                                            'draftid' => $data->uploadfile,
                                                                            'override'=> !empty($data->override),
                                                                            'encoding' => $data->encoding,
                                                                            'delimiter' => $data->delimiter ));
            $header = new assign_header($this->assignment->get_instance(),
                                        $context,
                                        false,
                                        $this->assignment->get_course_module()->id,
                                        get_string('importhistoric', 'assignfeedback_historic'));
            $o = '';
            $o .= $this->assignment->get_renderer()->render($header);
            $o .= $this->assignment->get_renderer()->render(new assign_form('importhistoric', $mform));
            $o .= $this->assignment->get_renderer()->render_footer();
        } else if($confirm && confirm_sesskey()) {
            if ($mform->is_cancelled()) {
                redirect($baseurl);
                return;
            }
            $data = data_submitted();
            if (isset($data->cancel) && $data->cancel) {
                redirect($baseurl);
            }


        /// process form & store data in database
            $usercontext = context_user::instance($USER->id);
            $fs = get_file_storage();
            if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data->draftid, 'id DESC', false)) {
                redirect($baseurl);
            }
            $file = reset($files);

            $csvdata = $file->get_content();

            $columns = '';
            if ($csvdata) {
                $csvreader = new csv_import_reader($data->importid, 'assignfeedback_historic_'.$formparams['cm']);
                $csvreader->load_csv_content($csvdata, $data->encoding, $data->delimiter);
                $csvreader->init();
                $datacolumns = $csvreader->get_columns();
            }

            $columns = array();
            $cols = explode(',', $CFG->grade_export_userprofilefields);
            foreach($cols as $col) {
                $col = trim($col);
                $columns[$col] = strtr(get_string($col), ' ', '_');
            }
            $usercols = array_keys($columns);
            $cols = array('datatype','grade','comment');
            foreach($cols as $col) {
                $columns[$col] = strtr(get_string($col, 'assignfeedback_historic'), ' ', '_');
            }
            $cols = array();
            foreach($datacolumns as $key => $col) {
                $c = array_search($col, $columns);
                if($c != false) {
                    $cols[$c] = $key;
                }
            }
            $requiredfields = array('idnumber', 'datatype');
            if (!$cols || $error = array_diff($requiredfields, array_keys($cols))) {
                print_error('invaliduploadcsvimport', 'assignfeedback_historic', $baseurl);
                die;
            }
            $datatypes = $this->get_datatypes_names();

            $savedata = new stdclass;
            $imported = 0;
            while ($record = $csvreader->next()) {
                if($userid = $DB->get_field('user', 'id', array('idnumber'=>$record[$cols['idnumber']]))) {
                    $grade = $this->assignment->get_user_grade($userid, true);
                    $type = array_search($record[$cols['datatype']], $datatypes);
                    $savedata->hgrade = array();
                    $savedata->hcomment = array();
                    $savedata->hgrade[$type] = $record[$cols['grade']];
                    $savedata->hcomment[$type] = $record[$cols['comment']];
                    if($this->save($grade, $savedata, $data->override)) {
                        $imported +=1;
                    }
                }
            }
            $event = \assignfeedback_historic\event\historic_imported::create_from_assign($this->assignment);
            $event->trigger();

            $message = get_string('numimported', 'assignfeedback_historic', $imported);
            redirect($baseurl, $message, -1);
        } else {

            $header = new assign_header($this->assignment->get_instance(),
                                        $context,
                                        false,
                                        $this->assignment->get_course_module()->id,
                                        get_string('importhistoric', 'assignfeedback_historic'));
            $o = '';
            $o .= $this->assignment->get_renderer()->render($header);
            $o .= $this->assignment->get_renderer()->render(new assign_form('importhistoric', $mform));
            $o .= $this->assignment->get_renderer()->render_footer();
        }

        return $o;

    }



    /**
     * Reads a CSV file and applies data to current annuality of historic info
     *
     * @return string - The gradestring
     */
    public function format_grade($value, $decimals) {
        if(!is_numeric($value) || $value == -1) {
            return '-';
        }

        return format_float($value, $decimals, true, true);
    }

}
