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
 * Form for editing HTML block instances.
 *
 * @package   block_examswarnings
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form for editing HTML block instances.
 *
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_examswarnings_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG, $DB;
        
        // only configurable system context instances    
        if($this->block->instance->parentcontextid != context_system::instance()->id) {
            return false;
        }
        
        $settings = get_config('block_examswarnings');

        // Fields for editing exam reminders to teachers.
        $mform->addElement('header', 'headerreminders', get_string('headerreminders', 'block_examswarnings'));

        $mform->addElement('advcheckbox', 'config_enablereminders', get_string('enablereminders', 'block_examswarnings'), '');
        $mform->setDefault('config_enablereminders', $settings->enablereminders);
        $mform->setType('config_enablereminders', PARAM_INT);
        $mform->addHelpButton('config_enablereminders', 'enablereminders', 'block_examswarnings');
        
        //$roles = get_all_roles();
        $roles = role_fix_names(get_all_roles(), null, ROLENAME_ORIGINAL, true);
        list($usql, $params) = $DB->get_in_or_equal(array('editingteacher', 'teacher'));
        $defaultroles = $DB->get_records_select('role', " shortname $usql ", $params, '', 'id, name');

        $select = $mform->addElement('select', 'config_reminderroles', get_string('reminderroles', 'block_examswarnings'), $roles);
        $select->setMultiple(true);
        $select->setSelected(explode(',', $settings->reminderroles));
        $mform->addHelpButton('config_reminderroles', 'reminderroles', 'block_examswarnings');
        
        $days = array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,15=>15,21=>21,28=>28,30=>30,35=>35,42=>42,49=>49,56=>56,60=>60,90=>90);
        $mform->addElement('select', 'config_reminderdays', get_string('reminderdays', 'block_examswarnings'), $days);
        $mform->setDefault('config_reminderdays', $settings->reminderdays);
        $mform->addHelpButton('config_reminderdays', 'reminderdays', 'block_examswarnings');

        $editoroptions = array('maxfiles' => 0, 'noclean'=>true, 'context'=>$this->block->context);
        $mform->addElement('editor', 'config_remindermessage', get_string('remindermessage', 'block_examswarnings'), null, $editoroptions);
        $mform->setType('config_remindermessage', PARAM_TEXT); // XSS is prevented when printing the block contents and serving files
        $mform->addHelpButton('config_remindermessage', 'remindermessage', 'block_examswarnings');
        
        $mform->setExpanded('headerreminders', false);
        
        // Fields for editing exam reminders to teachers.
        $mform->addElement('header', 'headerroomcalls', get_string('headerroomcalls', 'block_examswarnings'));
        
        $mform->addElement('advcheckbox', 'config_enableroomcalls', get_string('enableroomcalls', 'block_examswarnings'), '');
        $mform->setDefault('config_enableroomcalls', $settings->enableroomcalls);
        $mform->setType('config_enableroomcalls', PARAM_INT);
        $mform->addHelpButton('config_enableroomcalls', 'enableroomcalls', 'block_examswarnings');

        if(!$staffroles = $DB->get_records_menu('examregistrar_elements', array('type'=>'roleitem', 'visible'=>1, 'examregid'=>$settings->primaryreg), 'id ASC', 'id, name')) {
            $staffroles = array(0 => get_string('none'));
        }
        //$defaultroles = $DB->get_records('examregistrar_elements', array('type'=>'roleitem', 'idnumber'=>get_config('examregistrar','defaultrole')));
        $select = $mform->addElement('select', 'config_roomcallroles', get_string('roomcallroles', 'block_examswarnings'), $staffroles);
        $select->setMultiple(true);
        $select->setSelected(explode(',', $settings->roomcallroles));
        $mform->addHelpButton('config_roomcallroles', 'roomcallroles', 'block_examswarnings');

        $mform->addElement('select', 'config_roomcalldays', get_string('roomcalldays', 'block_examswarnings'), $days);
        $mform->setDefault('config_roomcalldays', $settings->roomcalldays);
        $mform->addHelpButton('config_roomcalldays', 'roomcalldays', 'block_examswarnings');

        $mform->addElement('editor', 'config_roomcallmessage', get_string('roomcallmessage', 'block_examswarnings'), null, $editoroptions);
        $mform->setType('config_roomcallmessage', PARAM_TEXT); // XSS is prevented when printing the block contents and serving files
        $mform->addHelpButton('config_roomcallmessage', 'roomcallmessage', 'block_examswarnings');
        
        $mform->setExpanded('headerroomcalls', false);
        
        // Fields for editing exam reminders to teachers.
        $mform->addElement('header', 'headerwarnings', get_string('headerwarnings', 'block_examswarnings'));
        
        $mform->addElement('advcheckbox', 'config_enablewarnings', get_string('enablewarnings', 'block_examswarnings'), '');
        $mform->setDefault('config_enablewarnings', $settings->enablewarnings);
        $mform->setType('config_enablewarnings', PARAM_INT);
        $mform->addHelpButton('config_enablewarnings', 'enablewarnings', 'block_examswarnings');

        $select = $mform->addElement('select', 'config_warningroles', get_string('warningroles', 'block_examswarnings'), $roles);
        $select->setMultiple(true);
        $select->setSelected(explode(',', $settings->warningroles));
        $mform->addHelpButton('config_warningroles', 'warningroles', 'block_examswarnings');
        
        $mform->addElement('select', 'config_warningdays', get_string('warningdays', 'block_examswarnings'), $days);
        $mform->setDefault('config_warningdays', $settings->warningdays);
        $mform->addHelpButton('config_warningdays', 'warningdays', 'block_examswarnings');
        
        $mform->addElement('select', 'config_warningdaysextra', get_string('warningdaysextra', 'block_examswarnings'), $days);
        $mform->setDefault('config_warningdaysextra', $settings->warningdaysextra);
        $mform->addHelpButton('config_warningdaysextra', 'warningdaysextra', 'block_examswarnings');
        
        $mform->addElement('select', 'config_examconfirmdays', get_string('examconfirmdays', 'block_examswarnings'), $days);
        $mform->setDefault('config_examconfirmdays', $settings->examconfirmdays);
        $mform->addHelpButton('config_examconfirmdays', 'examconfirmdays', 'block_examswarnings');
        
        $mform->addElement('text', 'config_examidnumber', get_string('examidnumber', 'block_examswarnings'));
        $mform->setType('config_examidnumber', PARAM_TEXT);
        $mform->setDefault('config_examidnumber', $settings->examidnumber);
        $mform->addHelpButton('config_examidnumber', 'examidnumber', 'block_examswarnings');
        
        $mform->addElement('editor', 'config_warningmessage', get_string('warningmessage', 'block_examswarnings'), null, $editoroptions);
        $mform->setType('config_warningmessage', PARAM_TEXT); // XSS is prevented when printing the block contents and serving files
        $mform->addHelpButton('config_warningmessage', 'warningmessage', 'block_examswarnings');
        
        $mform->addElement('editor', 'config_confirmmessage', get_string('confirmmessage', 'block_examswarnings'), null, $editoroptions);
        $mform->setType('config_confirmmessage', PARAM_TEXT); // XSS is prevented when printing the block contents and serving files
        $mform->addHelpButton('config_confirmmessage', 'confirmmessage', 'block_examswarnings');
        
        $mform->setExpanded('headerwarnings', false);
        
        // Fields for editing exam reminders to teachers.
        $mform->addElement('header', 'headercontrol', get_string('headercontrol', 'block_examswarnings'));

        if(!$examregs = $DB->get_records_select_menu('examregistrar', " primaryidnumber <> ''  ", null , 'name', 'id, name')) {
            $examregs = array(0 => get_string('none'));
        }
        $mform->addElement('select', 'config_primaryreg', get_string('primaryreg', 'block_examswarnings'), $examregs);
        $mform->setDefault('config_primaryreg', $settings->primaryreg);
        $mform->addHelpButton('config_primaryreg', 'primaryreg', 'block_examswarnings');

        $mform->addElement('text', 'config_annuality', get_string('annuality', 'block_examswarnings'));
        $mform->setType('config_annuality', PARAM_INT);
        $mform->setDefault('config_annuality', $settings->annuality);
        $mform->addHelpButton('config_annuality', 'annuality', 'block_examswarnings');
        
        $mform->addElement('text', 'config_controlemail', get_string('controlemail', 'block_examswarnings'));
        $mform->setType('config_controlemail', PARAM_TAGLIST);
        $mform->setDefault('config_controlemail', $settings->controlemail);
        $mform->addHelpButton('config_controlemail', 'controlemail', 'block_examswarnings');
        
        $mform->addElement('advcheckbox', 'config_noemail', get_string('noemail', 'block_examswarnings'), '');
        $mform->setDefault('config_noemail', $settings->noemail);
        $mform->setType('config_noemail', PARAM_INT);
        $mform->addHelpButton('config_noemail', 'noemail', 'block_examswarnings');
        
        $mform->setExpanded('headercontrol', false);
  
    }
}
