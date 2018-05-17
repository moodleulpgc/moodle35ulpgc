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

class local_ulpgccore_exportusers_form extends moodleform {

    function definition () {

        global $CFG, $USER, $OUTPUT;

        $mform = & $this->_form;
        $course = $this->_customdata['course'];

        // user selection
        $name = get_string('exportuserselector', 'local_ulpgccore');
        $mform->addElement('header', 'userselector', $name);
        
        $groups = groups_get_all_groups($course->id, 0, 0, 'g.id, g.name');
        $groupchoices = array();
        $groupchoices[0] = get_string('any');
        $groupchoices[-1] = get_string('exportusergroupmember', 'local_ulpgccore');
        $groupchoices[-2] = get_string('none');
        foreach ($groups as $group) {
            $groupchoices[$group->id] = $group->name;
        }
        unset($groups);
        $name = get_string('exportusersingroup', 'local_ulpgccore');
        $mform->addElement('select', 'exportgroupid', $name, $groupchoices);
        $mform->addHelpButton('exportgroupid', 'exportusersingroup', 'local_ulpgccore');

        $groupings = groups_get_all_groupings($course->id);
        $groupingchoices = array();
        $groupingchoices[-1] = get_string('none');
        $groupingchoices[0] = get_string('any');
        foreach ($groupings as $grouping) {
            $groupingchoices[$grouping->id] = $grouping->name;
        }

        $name = get_string('exportgroupsgrouping', 'local_ulpgccore');
        $mform->addElement('select', 'exportgroupsgrouping', $name, $groupingchoices);
        $mform->setDefault('exportgroupsgrouping', 0);
        $mform->addHelpButton('exportgroupsgrouping', 'exportgroupsgrouping', 'local_ulpgccore');
        $mform->disabledIf('exportgroupsgrouping', 'exportgroupid', 'eq', -2);

        $roles = get_all_roles();
        $options = role_fix_names($roles, null, ROLENAME_ORIGINAL, true);
        $defaultrole = get_config('enrol_manual', 'roleid');
        $name = get_string('exportuserroles', 'local_ulpgccore');
        $select = $mform->addElement('select', 'exportuserroles', $name, $options, array('size'=>8));        
        $mform->addHelpButton('exportuserroles', 'exportuserroles', 'local_ulpgccore');
        $select->setMultiple(true);
        $select->setSelected($defaultrole);
        $error = get_string('err_required', 'form');
        $mform->addRule('exportuserroles', $error, 'required', 'client');
        
        // data for exportation
        $name = get_string('exportdataselector', 'local_ulpgccore');
        $mform->addElement('header', 'dataselector', $name);
        $mform->setExpanded('dataselector');
        
        $name = get_string('exportincludeuserroles', 'local_ulpgccore');
        $help = get_string('exportincludeuserroles_help', 'local_ulpgccore');
        $mform->addElement('checkbox', 'exportincludeuserroles', $name, $help);
        //$mform->addHelpButton('exportincludeuserroles', 'exportincludeuserroles', 'local_ulpgccore');

        $name = get_string('exportincludeusergroups', 'local_ulpgccore');
        $help = get_string('exportincludeusergroups_help', 'local_ulpgccore');
        $mform->addElement('checkbox', 'exportincludeusergroups', $name, $help);
        //$mform->addHelpButton('exportincludeusergroups', 'exportincludeusergroups', 'local_ulpgccore');

        $groupingchoices[-1] = get_string('groupingsameabove', 'local_ulpgccore');
        $name = get_string('exportonlygrouping', 'local_ulpgccore');
        $mform->addElement('select', 'exportonlygrouping', $name, $groupingchoices);
        $mform->setDefault('exportonlygrouping', -1);
        $mform->addHelpButton('exportonlygrouping', 'exportonlygrouping', 'local_ulpgccore');
        $mform->disabledIf('exportonlygrouping', 'exportincludeusergroups', 'notchecked');
        
        $userdetails = local_ulpgccore_get_userfields();
        
        $userdetailsgrp = array();
        foreach($userdetails as $key => $name) {
            $userdetailsgrp[] = &$mform->createElement('checkbox', $key, '', $name);
            $mform->setDefault($key, 0);
        }
        $mform->addGroup($userdetailsgrp, 'userdetailsgrp', get_string('exportusersdetails', 'local_ulpgccore'), "<br>", false);
        $mform->addHelpButton('userdetailsgrp', 'exportusersdetails', 'local_ulpgccore');
        
        
        $options = array('firstname'=>get_string('firstname'), 
                        'lastname'=>get_string('lastname'),
                        'idnumber'=>get_string('idnumber')) + $userdetails;
        $name = get_string('exportsort', 'local_ulpgccore');
        $mform->addElement('select', 'exportsort', $name, $options);
        $mform->setDefault('exportsort', 'lastname');
        $mform->addHelpButton('exportsort', 'exportsort', 'local_ulpgccore');
        
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
        
        $formats = core_plugin_manager::instance()->get_plugins_of_type('dataformat');
        $options = array();
        foreach ($formats as $format) {
            if ($format->is_enabled()) {
                $options[$format->name] = get_string('dataformat', $format->component);
            }
        }        
        $name = get_string('exportformatselector', 'local_ulpgccore');
        $mform->addElement('select', 'dataformat', $name, $options);
        
        $mform->addElement('hidden', 'id', $course->id);
        $mform->setType('id', PARAM_INT);
        
        $this->add_action_buttons(true, get_string('exportdownload', 'local_ulpgccore'));
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
