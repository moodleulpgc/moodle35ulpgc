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
 * Quiz makeexam permissions exemptions form definition.
 *
 * @package   quiz_makeexam
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * This is the exemptions form for the quiz makeexam report.
 *
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_makeexam_exemptions_form extends moodleform {

    function definition() {

        $mform =& $this->_form;
        $cmid = $this->_customdata['id'];
        $tab = $this->_customdata['tab'];
        $examreg = $this->_customdata['exreg'];
        $baseparams = $this->_customdata['reviewparams'];
        $exemption = $this->_customdata['exemption'];
        $courses = $this->_customdata['courses'];

        $context = context_module::instance($cmid);

        $mform->addElement('header', 'setpermissions', get_string('extracapabilities', 'quiz_makeexam'));

        $coursemenu = array();
        foreach($courses as $cid => $course) {
            $coursemenu[$cid] = $course->shortname.'-'.$course->fullname;
        }
        $coursemenu = &$mform->addElement('select', 'courses', get_string('setcourses', 'quiz_makeexam'), $coursemenu, 'size="8"');
        $coursemenu->setMultiple(true);
        $mform->addRule('courses', null, 'required');
        $mform->addHelpButton('courses', 'setcourses', 'quiz_makeexam');

        $roles = get_role_names_with_caps_in_context($context, array('quiz/makeexam:view'));
        $managers = get_roles_with_caps_in_context($context, array('moodle/question:config'));
        foreach($managers as $key=>$role) {
            unset($roles[$key]);
        }
        $rolemenu = &$mform->addElement('select', 'roles', get_string('setroles', 'quiz_makeexam'), $roles);
        $rolemenu->setMultiple(true);
        $mform->addRule('roles', null, 'required');
        $mform->addHelpButton('roles', 'setroles', 'quiz_makeexam');

        $capabilities = array('quiz/makeexam:anyquestions'=>get_string('makeexam:anyquestions', 'quiz_makeexam'),
                              'quiz/makeexam:nochecklimit'=>get_string('makeexam:nochecklimit', 'quiz_makeexam'));
        $capmenu = &$mform->addElement('select', 'capabilities', get_string('setcapabilities', 'quiz_makeexam'), $capabilities, 'size="2"');
        $capmenu->setMultiple(true);
        $mform->addRule('capabilities', null, 'required');
        $mform->addHelpButton('capabilities', 'setcapabilities', 'quiz_makeexam');

        $assignmenu = array(0=>get_string('remove'), 1=>get_string('add'));
        $mform->addElement('select', 'assign', get_string('assigncapabilities', 'quiz_makeexam'), $assignmenu);
        $mform->addHelpButton('assign', 'assigncapabilities', 'quiz_makeexam');
        $mform->setDefault('assign', 0);

        foreach($baseparams as $param => $value) {
            $mform->addElement('hidden', $param, $value);
            $mform->setType($param, PARAM_ALPHANUMEXT);
        }

        $mform->addElement('hidden', 'exemption', $exemption);
        $mform->setType('exemption', PARAM_INT);

        $mform->addElement('hidden', 'tab', $tab);
        $mform->setType('tab', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('setpermissions', 'quiz_makeexam'));
    }
}
