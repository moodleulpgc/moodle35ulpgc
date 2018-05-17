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
 * report_syncgroups form definition.
 *
 * @package   report_syncgroups
 * @copyright 2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/grouplib.php');


/**
 * The form for editing the group settings.
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_syncgroups_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $COURSE, $USER;

        $mform =& $this->_form;
        $course = $this->_customdata['course'];
        $sid = 0;

        $context = context_course::instance($course->id);
        $aag = has_capability('moodle/site:accessallgroups', $context);
        if ($course->groupmode == VISIBLEGROUPS or $aag) {
            $allowedgroups = groups_get_all_groups($course->id, 0, $course->defaultgroupingid);
        } else {
            $allowedgroups = groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
        }
        $groupsmenu = array();
        if ($allowedgroups) {
            foreach ($allowedgroups as $group) {
                $groupsmenu[$group->id] = format_string($group->name);
            }
        }
        natcasesort($groupsmenu);

        $parentgroups = &$mform->addElement('select', 'parentgroups', get_string('parentgroups', 'report_syncgroups'), $groupsmenu, 'size="10"');
        $parentgroups->setMultiple(true);
        $mform->addHelpButton('parentgroups', 'parentgroups', 'report_syncgroups');
        $mform->addRule('parentgroups', null, 'required');

        // parents are excluded from potential targets to avoid circular references
        $courseparents = array();
        $parents = $DB->get_fieldset_select('groups_syncgroups', 'parentgroups', ' course = ? ', array($course->id));
        foreach($parents as $parent) {
            $spars = explode(',', $parent);
            $courseparents = array_merge($courseparents, $spars);
        }
        $courseparents = array_unique($courseparents);

        $groupsmenu = array(0=>get_string('choose'));
        if ($allowedgroups) {
            foreach ($allowedgroups as $group) {
                if(!in_array($group->id, $courseparents)) {
                    $groupsmenu[$group->id] = format_string($group->name);
                }
            }
        }
        natcasesort($groupsmenu);
        $groupsmenu = array(0=>get_string('choose')) + $groupsmenu;
        $mform->addElement('select', 'targetgroup', get_string('targetgroup', 'report_syncgroups'), $groupsmenu);
        $mform->addHelpButton('targetgroup', 'targetgroup', 'report_syncgroups');
        $mform->addRule('targetgroup', null, 'required');

        $options = array(0=>get_string('no'), '1'=>get_string('yes'));
        $mform->addElement('selectyesno', 'visible', get_string('visible', 'report_syncgroups'), $options);
        $mform->setDefault('visible', 1);

        $mform->addElement('hidden', 'cid', $course->id);
        $mform->setType('cid', PARAM_INT);
        $mform->addElement('hidden', 'sid', $sid);
        $mform->setType('sid', PARAM_INT);

        $this->add_action_buttons();
    }
}
