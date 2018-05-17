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
 * ulpgccore user export form
 *
 * @package    local
 * @subpackage ulpgccore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/local/ulpgccore/lib.php');
require_once($CFG->dirroot.'/lib/formslib.php');


class local_ulpgcgroups_exportgroups_form extends moodleform {

    function definition () {

        global $CFG, $USER, $OUTPUT;

        $mform = & $this->_form;
        $course = $this->_customdata['course'];

        // user selection
        $name = get_string('exportgroupselector', 'local_ulpgcgroups');
        $mform->addElement('header', 'userselector', $name);
        
        $groups = groups_get_all_groups($course->id, 0, 0, 'g.id, g.name');
        $groupchoices = array();
        $groupchoices[0] = get_string('all');
        $groupchoices[-1] = get_string('none');
        foreach ($groups as $group) {
            $groupchoices[$group->id] = $group->name;
        }
        unset($groups);
        $name = get_string('exportgroup', 'local_ulpgcgroups');
        $mform->addElement('select', 'exportgroupid', $name, $groupchoices);
        $mform->setDefault('exportgroupid', 0);
        $mform->addHelpButton('exportgroupid', 'exportgroup', 'local_ulpgcgroups');

        $groupings = groups_get_all_groupings($course->id);
        $groupingchoices = array();
        $groupingchoices[-1] = get_string('none');
        $groupingchoices[0] = get_string('any');
        foreach ($groupings as $grouping) {
            $groupingchoices[$grouping->id] = $grouping->name;
        }

        $name = get_string('exportgrouping', 'local_ulpgcgroups');
        $mform->addElement('select', 'exportgrouping', $name, $groupingchoices);
        $mform->setDefault('exportgrouping', 0, $groupchoices);
        $mform->addHelpButton('exportgrouping', 'exportgrouping', 'local_ulpgcgroups');

        // users for exportation
        $name = get_string('exportuserselector', 'local_ulpgcgroups');
        $mform->addElement('header', 'userselector', $name);

        $roles = get_all_roles();
        $options = role_fix_names($roles, null, ROLENAME_ORIGINAL, true);
        $defaultrole = get_config('enrol_manual', 'roleid');
        $name = get_string('exportuserroles', 'local_ulpgcgroups');
        $select = $mform->addElement('select', 'exportuserroles', $name, $options, array('size'=>8));        
        $mform->addHelpButton('exportuserroles', 'exportuserroles', 'local_ulpgcgroups');
        $select->setMultiple(true);
        $select->setSelected($defaultrole);
        $error = get_string('err_required', 'form');
        $mform->addRule('exportuserroles', $error, 'required', 'client');

        $options = array('firstname'=>get_string('firstname'), 'lastname'=>get_string('lastname'));
        $name = get_string('userorder', 'local_ulpgcgroups');
        $mform->addElement('select', 'exportnameformat', $name, $options);
        $mform->setDefault('exportnameformat', 'lastname');
        $mform->addHelpButton('exportnameformat', 'userorder', 'local_ulpgcgroups');

        // data for exportation
        $name = get_string('exportdataselector', 'local_ulpgcgroups');
        $mform->addElement('header', 'dataselector', $name);
        $mform->setExpanded('dataselector');
        
        $name = get_string('exportincludeuserroles', 'local_ulpgcgroups');
        $help = get_string('exportincludeuserroles_help', 'local_ulpgcgroups');
        $mform->addElement('checkbox', 'exportincludeuserroles', $name, $help);
        //$mform->addHelpButton('exportincludeuserroles', 'exportincludeuserroles', 'local_ulpgcgroups');

        $userdetails = local_ulpgccore_get_userfields();
        
        $userdetailsgrp = array();
        foreach($userdetails as $key => $name) {
            $userdetailsgrp[] = &$mform->createElement('checkbox', $key, '', $name);
            $mform->setDefault($key, 0);
        }

        $mform->addGroup($userdetailsgrp, 'userdetailsgrp', get_string('exportusersdetails', 'local_ulpgcgroups'), "<br>", false);
        $mform->addHelpButton('userdetailsgrp', 'exportusersdetails', 'local_ulpgcgroups');
        
        $name = get_string('exportextracolumns', 'local_ulpgcgroups');
        $mform->addElement('text', 'exportextracolumns', $name);
        $mform->addHelpButton('exportextracolumns', 'exportextracolumns', 'local_ulpgcgroups');
        $mform->setType('exportextracolumns', PARAM_TEXT);
        
        // dataformat selection
        $name = get_string('exportfileselector', 'local_ulpgccore');
        $mform->addElement('header', 'fileselector', $name);
        $mform->setExpanded('fileselector');
        
        $filename = $course->shortname.'-'.get_string('users');
        $name = get_string('exportfilename', 'local_ulpgccore');
        $mform->addElement('text', 'filename', $name, array('size'=>'30'));
        $mform->setType('filename', PARAM_FILE);
        $mform->setDefault('filename', $filename);
        $mform->addRule('filename', $error, 'required', 'client');
        $mform->addHelpButton('filename', 'exportfilename', 'local_ulpgccore');

        /*
        $formats = core_plugin_manager::instance()->get_plugins_of_type('dataformat');
        $options = array();
        foreach ($formats as $format) {
            if ($format->is_enabled()) {
                $options[$format->name] = get_string('dataformat', $format->component);
            }
        }    
        */
        //$options = array('ODText'=>'ODT Text', 'Word2007'=>'Word 2007 .docx');
        $options = array('ODText'=>'ODT Text');
        $name = get_string('exportformatselector', 'local_ulpgcgroups');
        $mform->addElement('select', 'dataformat', $name, $options);
        
        $mform->addElement('hidden', 'id', $course->id);
        $mform->setType('id', PARAM_INT);
        
        $this->add_action_buttons(true, get_string('exportdownload', 'local_ulpgcgroups'));
    }
    
    function validation($data, $files) {
        global $CFG, $USER, $DB;
        $errors = parent::validation($data, $files);
        
        if(empty($data['exportuserroles'])) {
            $errors['exportuserroles'] = get_string('err_required', 'form');
        }
        return $errors;
    }
}
