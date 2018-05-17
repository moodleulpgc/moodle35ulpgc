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
 * This file contains the forms to create and edit an instance of this module
 *
 * @package   mod_dialogue
 * @copyright 2013
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once ($CFG->dirroot.'/mod/dialogue/locallib.php');
require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_dialogue_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $COURSE, $DB;

        $mform    = $this->_form;

        $pluginconfig = get_config('dialogue');

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('dialoguename', 'dialogue'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        moodleform_mod::standard_intro_elements();

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes, $pluginconfig->maxbytes);

        $mform->addElement('select', 'maxbytes', get_string('maxattachmentsize', 'dialogue'), $choices);
        $mform->addHelpButton('maxbytes', 'maxattachmentsize', 'dialogue');
        $mform->setDefault('maxbytes', $pluginconfig->maxbytes);

        $choices = range(0, $pluginconfig->maxattachments);
        $choices[0] = get_string('uploadnotallowed');
        $mform->addElement('select', 'maxattachments', get_string('maxattachments', 'dialogue'), $choices);
        $mform->addHelpButton('maxattachments', 'maxattachments', 'dialogue');
        $mform->setDefault('maxattachments', $pluginconfig->maxattachments);

        $mform->addElement('checkbox', 'usecoursegroups', get_string('usecoursegroups', 'dialogue'));
        $mform->addHelpButton('usecoursegroups', 'usecoursegroups', 'dialogue');
        $mform->setDefault('usecoursegroups', 0);

        $mform->addElement('selectyesno', 'alternatemode', get_string('alternatemode', 'dialogue')); // ecastro ULPGC to allow alternative teacher/student mode
        $mform->addHelpButton('alternatemode', 'alternatemode', 'dialogue');
        $mform->setDefault('alternatemode', 0);

        $mform->addElement('selectyesno', 'multipleconversations', get_string('multipleconversations', 'dialogue')); // ecastro ULPGC to allow alternative teacher/student mode
        $mform->addHelpButton('multipleconversations', 'multipleconversations', 'dialogue');
        $mform->setDefault('multipleconversations', 1);

        $mform->addElement('selectyesno', 'notifications', get_string('notifications', 'dialogue')); // ecastro ULPGC to allow alternative teacher/student mode
        $mform->addHelpButton('notifications', 'notifications', 'dialogue');
        $mform->setDefault('notifications', 0);

        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values) { //ecastro ULPGC to use completion
        parent::data_preprocessing($default_values);

        // Set up the completion checkboxes which aren't part of standard data.
        // We also make the default value (if you turn on the checkbox) for those
        // numbers to be 1, this will not apply unless checkbox is ticked.
        $default_values['completionconversationsenabled']=
            !empty($default_values['completionconversations']) ? 1 : 0;
        if (empty($default_values['completionconversations'])) {
            $default_values['completionconversations']=1;
        }
        $default_values['completionrepliesenabled']=
            !empty($default_values['completionreplies']) ? 1 : 0;
        if (empty($default_values['completionreplies'])) {
            $default_values['completionreplies']=1;
        }
        $default_values['completionpostsenabled']=
            !empty($default_values['completionposts']) ? 1 : 0;
        if (empty($default_values['completionposts'])) {
            $default_values['completionposts']=1;
        }
    }

      function add_completion_rules() {
        $mform =& $this->_form;

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completionpostsenabled', '', get_string('completionposts','dialogue'));
        $group[] =& $mform->createElement('text', 'completionposts', '', array('size'=>3));
        $mform->setType('completionposts',PARAM_INT);
        $mform->addGroup($group, 'completionpostsgroup', get_string('completionpostsgroup','dialogue'), array(' '), false);
        $mform->disabledIf('completionposts','completionpostsenabled','notchecked');

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completionconversationsenabled', '', get_string('completionconversations','dialogue'));
        $group[] =& $mform->createElement('text', 'completionconversations', '', array('size'=>3));
        $mform->setType('completionconversations',PARAM_INT);
        $mform->addGroup($group, 'completionconversationsgroup', get_string('completionconversationsgroup','dialogue'), array(' '), false);
        $mform->disabledIf('completionconversations','completionconversationsenabled','notchecked');

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completionrepliesenabled', '', get_string('completionreplies','dialogue'));
        $group[] =& $mform->createElement('text', 'completionreplies', '', array('size'=>3));
        $mform->setType('completionreplies',PARAM_INT);
        $mform->addGroup($group, 'completionrepliesgroup', get_string('completionrepliesgroup','dialogue'), array(' '), false);
        $mform->disabledIf('completionreplies','completionrepliesenabled','notchecked');

        return array('completionconversationsgroup','completionrepliesgroup','completionpostsgroup');
    }

    function completion_rule_enabled($data) {
        return (!empty($data['completionconversationsenabled']) && $data['completionconversations']!=0) ||
            (!empty($data['completionrepliesenabled']) && $data['completionreplies']!=0) ||
            (!empty($data['completionpostsenabled']) && $data['completionposts']!=0);
    }
     function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        if (!isset($data->usecoursegroups)) {
            $data->usecoursegroups = 0;
        }

        // Turn off completion settings if the checkboxes aren't ticked // ecastro ULPGC
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion==COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionconversationsenabled) || !$autocompletion) {
                $data->completionconversations = 0;
            }
            if (empty($data->completionrepliesenabled) || !$autocompletion) {
                $data->completionreplies = 0;
            }
            if (empty($data->completionpostsenabled) || !$autocompletion) {
                $data->completionposts = 0;
            }
        }
        return $data;
     }
}
