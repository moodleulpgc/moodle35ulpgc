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
 * Email links always redirects to this page (including mandatory parameter 'key')
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_unedtrivial_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $PAGE, $DB;
        if ($PAGE->cm != null){
            $sql = "SELECT COUNT(*) FROM {unedtrivial_history} WHERE "
                    . "idunedtrivial = " . $PAGE->cm->instance;
            $lock = ($DB->count_records_sql($sql,array()) > 0);
        }else{
           $lock = false;
        }

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('newmodulename', 'unedtrivial'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'newmodulename', 'unedtrivial');
		
        // Adding the standard "intro" and "introformat" fields.
		if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }
		
        // Adding the rest of newmodule settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        //$mform->addElement('text', 'enddate', get_string('enddate', 'unedtrivial'), array('size' => '64'));

        $mform->addElement('header', 'unedtrivialoptions', get_string('unedtrivialoptions','unedtrivial'));
        $group = array();
        $group[] =& $mform->createElement('date_time_selector', 'enddate', get_string('enddate','unedtrivial'), array('optional' => false, 'step' => 1));
        $group[] =& $mform->createElement('checkbox', 'allquestions', '', get_string('answerall','unedtrivial'));
        $mform->addGroup($group, 'group', get_string('unedtrivialfinalization','unedtrivial'), ' ', false);
        $mform->disabledIf('group', 'allquestions', 'checked');
        //Lock certain options if this activity has been started before
        if ($lock){
            $mform->addElement('static', 'questionsperday', get_string('questionsperday','unedtrivial'));
            $mform->setType('questionsperday', PARAM_TEXT);
            $mform->setDefault('questionsperday', 3);
            $mform->addElement('static', 'timestocomplete', get_string('timestocomplete','unedtrivial'));
            $mform->setType('timestocomplete', PARAM_TEXT);
            $mform->setDefault('timestocomplete', 2);
            $mform->addElement('static', 'retryerror', get_string('retryerror','unedtrivial'));
            $mform->setType('retryerror', PARAM_TEXT);
            $mform->setDefault('retryerror', 7);
            $mform->addElement('static', 'retrysuccess', get_string('retrysuccess','unedtrivial'));
            $mform->setType('retrysuccess', PARAM_TEXT);
            $mform->setDefault('retrysuccess', 14);
            $mform->addElement('static', 'scoreerror', get_string('scoreerror','unedtrivial'));
            $mform->setType('scoreerror', PARAM_TEXT);
            $mform->setDefault('scoreerror', 2);
            $mform->addElement('static', 'scoresuccess', get_string('scoresuccess','unedtrivial'));
            $mform->setType('scoresuccess', PARAM_TEXT);
            $mform->setDefault('scoresuccess', 10);
        }else{
            $mform->addElement('text', 'questionsperday', get_string('questionsperday','unedtrivial'));
            $mform->setType('questionsperday', PARAM_TEXT);
            $mform->setDefault('questionsperday', 3);
            $mform->addElement('text', 'timestocomplete', get_string('timestocomplete','unedtrivial'));
            $mform->setType('timestocomplete', PARAM_TEXT);
            $mform->setDefault('timestocomplete', 2);
            $mform->addElement('text', 'retryerror', get_string('retryerror','unedtrivial'));
            $mform->setType('retryerror', PARAM_TEXT);
            $mform->setDefault('retryerror', 7);
            $mform->addElement('text', 'retrysuccess', get_string('retrysuccess','unedtrivial'));
            $mform->setType('retrysuccess', PARAM_TEXT);
            $mform->setDefault('retrysuccess', 14);
            $mform->addElement('text', 'scoreerror', get_string('scoreerror','unedtrivial'));
            $mform->setType('scoreerror', PARAM_TEXT);
            $mform->setDefault('scoreerror', 2);
            $mform->addElement('text', 'scoresuccess', get_string('scoresuccess','unedtrivial'));
            $mform->setType('scoresuccess', PARAM_TEXT);
            $mform->setDefault('scoresuccess', 10);
        }
        $mform->addElement('textarea', 'teachermails', get_string('teachermails','unedtrivial'),
                           'wrap="virtual" rows="1" cols="60"');
        $mform->setType('teachermails', PARAM_TEXT);
        
        //$mform->addRule('enddate', null, 'required', null, 'client');
        //$mform->addElement('static', 'label2', 'newmodulesetting2', 'Your unedtrivial fields go here. Replace me!');

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
    
    public function definition_after_data() {
        parent::definition_after_data();        
        $mform =& $this->_form;
        if ($mform->exportValues()['enddate'] == 60){
            $mform->setDefaults(array('allquestions' => 1));
        }
    }
    
    function add_completion_rules() {
        $mform =& $this->_form;

        $mform->addElement('checkbox', 'completionsubmit', '', get_string('completionsubmit','unedtrivial'));
        // Enable this completion rule by default.
        $mform->setDefault('completionsubmit', 1);
        return array('completionsubmit');
    }

    function completion_rule_enabled($data) {
        return !empty($data['completionsubmit']);
    }
}
