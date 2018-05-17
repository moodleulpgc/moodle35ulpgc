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
 * @package   assignfeedback_wtpeer
 * @copyright 2016 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

/**
 * Assignment grading options form
 *
 * @package   assignfeedback_wtpeer
 * @copyright 2016 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_wtpeer_grading_options_form extends moodleform {
    /**
     * Define this form - called from the parent constructor.
     */
    public function definition() {
        $mform = $this->_form;
        $instance = $this->_customdata;
        $dirtyclass = array('class' => 'ignoredirty');
        
        $ulpgc = get_config('local_ulpgcassign', 'enabledadvancedassign');

        $mform->addElement('header', 'general', get_string('gradingoptions', 'assign'));
        // Visible elements.
        $options = array(-1 => get_string('all'), 10 => '10', 20 => '20', 50 => '50', 100 => '100');
        $maxperpage = get_config('assign', 'maxperpage');
        if (isset($maxperpage) && $maxperpage != -1) {
            unset($options[-1]);
            foreach ($options as $val) {
                if ($val > $maxperpage) {
                    unset($options[$val]);
                }
            }
        }
        $mform->addElement('select', 'perpage', get_string('assignmentsperpage', 'assign'), $options, $dirtyclass);
        $options = array('' => get_string('filternone', 'assign'),
                         ASSIGN_FILTER_NOT_SUBMITTED => get_string('filternotsubmitted', 'assign'),
                         ASSIGN_FILTER_SUBMITTED => get_string('filtersubmitted', 'assign'));
        if($instance['cangrade']) {
            $options[ASSIGN_FILTER_REQUIRE_GRADING] = get_string('filterrequiregrading', 'assign');
        }
        if ($instance['submissionsenabled']) {
            if($ulpgc) {
                if($instance['cangrade']) {
                    $options = local_ulpgcassign_filter_menu();
                }
                $mform->addElement('static', 'filtermsg', '', '' ); // ecastro ULPGC filtering out users message warning
            }
            $mform->addElement('select', 'filter', get_string('filter', 'assign'), $options, $dirtyclass);
        }
        if (!empty($instance['markingallocationopt'])) {
            $markingfilter = get_string('markerfilter', 'assign');
            $mform->addElement('select', 'markerfilter', $markingfilter, $instance['markingallocationopt'], $dirtyclass);
        }
        
        // Show active/suspended user option.
        if ($instance['showonlyactiveenrolopt']) {
            $mform->addElement('checkbox', 'showonlyactiveenrol', get_string('showonlyactiveenrol', 'grades'), '', $dirtyclass);
            $mform->addHelpButton('showonlyactiveenrol', 'showonlyactiveenrol', 'grades');
            $mform->setDefault('showonlyactiveenrol', $instance['showonlyactiveenrol']);
        }

        // Hidden params.
        $mform->addElement('hidden', 'contextid', $instance['contextid']);
        $mform->setType('contextid', PARAM_INT);
        $mform->addElement('hidden', 'id', $instance['cm']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'userid', $instance['userid']);
        $mform->setType('userid', PARAM_INT);
        
        $mform->addElement('hidden', 'plugin', 'wtpeer');
        $mform->setType('plugin', PARAM_ALPHA);
        $mform->addElement('hidden', 'pluginsubtype', 'assignfeedback');
        $mform->setType('pluginsubtype', PARAM_ALPHA);
        $mform->addElement('hidden', 'action', 'viewpluginpage');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'pluginaction', 'saveoptions');
        $mform->setType('pluginaction', PARAM_ALPHA);   

        // Buttons.
        $this->add_action_buttons(false, get_string('updatetable', 'assign'));
    }
}

