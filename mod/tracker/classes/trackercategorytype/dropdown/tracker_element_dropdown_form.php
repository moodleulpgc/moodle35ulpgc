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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

/**
 * @package mod_tracker
 * @category mod
 * @author Valery Fremaux / 1.8
 * @date 06/08/2015
 */
require_once($CFG->dirroot.'/mod/tracker/classes/trackercategorytype/tracker_element_form.php');

class tracker_element_dropdown_form extends tracker_moodle_form {

    function definition() {
        $this->start_form();
        
        $mform = $this->_form;
        $mform->addElement('advcheckbox', 'paramint1', get_string('menumultiple', 'tracker'));
        
        $autofills = array('' => get_string('none'),
                            'courses' => get_string('courses'),
                            'categories' => get_string('categories'),
                            'users_role' => get_string('autofillusersrole', 'tracker'),
                            'users_group' => get_string('autofillusersgroup', 'tracker'),
                            'users_grouping' => get_string('autofillusersgrouping', 'tracker'),
                            );
        $mform->addElement('select', 'paramchar1', get_string('autofilltype', 'tracker'), $autofills);
        $mform->addHelpButton('paramchar1', 'autofilltype', 'tracker');
        $mform->setDefault('paramchar1', '');
        
        $mform->addElement('text', 'paramchar2', get_string('autofillidnumber', 'tracker'));
        $mform->addHelpButton('paramchar2', 'autofillidnumber', 'tracker');
        $mform->setType('paramchar2', PARAM_ALPHANUMEXT);
        $mform->disabledIf('paramchar2', 'paramchar1', 'eq', '');
        
        $options = array(0 => get_string('no'), 
                         1 => get_string('addascced', 'tracker'),
                         2 => get_string('addasassigned', 'tracker'));
        $mform->addElement('select', 'paramint2', get_string('adduserwatch', 'tracker'), $options);
        $mform->addHelpButton('paramint2', 'adduserwatch', 'tracker');
        
        $mform->disabledIf('paramint2', 'paramchar1', 'eq', '');
        $mform->disabledIf('paramint2', 'paramchar1', 'eq', 'courses');
        $mform->disabledIf('paramint2', 'paramchar1', 'eq', 'categories');
        
        
        
        
        $this->end_form();
    }

    function validation($data, $files) {
        return parent::validation($data, $files);
    }
}
