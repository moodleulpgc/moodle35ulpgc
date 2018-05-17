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
 * report_autogroups form definition.
 *
 * @package   report_autogroups
 * @copyright 2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/grouplib.php');
require_once($CFG->libdir.'/ddllib.php');
require_once($CFG->libdir.'/xmlize.php');


/**
 * The form for editing the group settings.
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_autogroups_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $COURSE;

        $mform =& $this->_form;
        $course = $this->_customdata['course'];
        $sid = $this->_customdata['sid'];

        $context = context_course::instance($course->id);
        $aag = has_capability('moodle/site:accessallgroups', $context);
        if ($course->groupmode == VISIBLEGROUPS or $aag) {
            $allowedgroups = groups_get_all_groups($course->id, 0, $course->defaultgroupingid);
        } else {
            $allowedgroups = groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
        }

        $mform->addElement('text', 'searchterm', get_string('searchterm', 'report_autogroups'), 'size = "60"');
        $mform->setDefault('searchterm', '%a%');
        $mform->addHelpButton('searchterm', 'searchterm', 'report_autogroups');
        $mform->setType('searchterm', PARAM_TEXT);
        $mform->addRule('searchterm', null, 'required');

        $tablefields = array();
        $tablecolumns = $DB->get_columns('course');
        foreach($tablecolumns as $key => $column) {
            $tablefields[$key] = $key;
        }
        $mform->addElement('select', 'searchfield', get_string('searchfield', 'report_autogroups'), $tablefields);
        $mform->setDefault('searchfield', 'idnumber');
        $mform->setType('searchfield', PARAM_TEXT);
        $mform->addHelpButton('searchfield', 'searchfield', 'report_autogroups');
        $mform->addRule('searchfield', null, 'required');

        $allroles = array();
        foreach (get_all_roles() as $role) {
                $rolename = strip_tags(format_string($role->name)) . ' ('. $role->shortname . ')';
                $allroles[$role->id] = $rolename;
        }

        $rolemenu = &$mform->addElement('select', 'sourceroles', get_string('sourceroles', 'report_autogroups'), $allroles, 'size="5"');
        $rolemenu->setMultiple(true);
        $mform->addHelpButton('sourceroles', 'sourceroles', 'report_autogroups');
        $mform->addRule('sourceroles', null, 'required');

        $groupsmenu = array(0=>get_string('choose'));
        if ($allowedgroups) {
            foreach ($allowedgroups as $group) {
                $groupsmenu[$group->id] = format_string($group->name);
            }
        }
        natcasesort($groupsmenu);
        $groupsmenu = array(0=>get_string('choose')) + $groupsmenu;
        $mform->addElement('select', 'targetgroup', get_string('targetgroup', 'report_autogroups'), $groupsmenu);
        $mform->addHelpButton('targetgroup', 'targetgroup', 'report_autogroups');
        $mform->addRule('targetgroup', null, 'required');

        $options = array(0=>get_string('no'), '1'=>get_string('yes'));
        $mform->addElement('selectyesno', 'visible', get_string('visible', 'report_autogroups'), $options);
        $mform->setDefault('visible', 1);

        $mform->addElement('hidden', 'cid', $course->id);
        $mform->setType('cid', PARAM_INT);
        $mform->addElement('hidden', 'sid', $sid);
        $mform->setType('sid', PARAM_INT);

        $this->add_action_buttons();
    }
}
