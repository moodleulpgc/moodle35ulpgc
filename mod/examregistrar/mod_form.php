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
 * The main examregistrar configuration form
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_examregistrar_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('examregistrarname', 'examregistrar'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'examregistrarname', 'examregistrar');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();

        $examregistrarsmenu = array('' => get_string('thisisprimary', 'examregistrar'));
        $examregistrarsmenu = $examregistrarsmenu + examregistrar_get_primary_registrars();

        $mform->addElement('select', 'primaryreg', get_string('useasprimary', 'examregistrar'), $examregistrarsmenu);
        $mform->addHelpButton('primaryreg', 'useasprimary', 'examregistrar');
        $mform->setDefault('useasprimary', '');

        $mform->addElement('text', 'primaryidnumber', get_string('primaryidnumber', 'examregistrar'), array('size'=>'50'));
        $mform->setType('primaryidnumber', PARAM_ALPHANUMEXT);
        $mform->setDefault('primaryidnumber', '');
        $mform->addHelpButton('primaryidnumber', 'primaryidnumber', 'examregistrar');
        $mform->disabledIf('primaryidnumber', 'primaryreg', 'neq', '');

        $formats = examregistrar_get_workmodes();
        $mform->addElement('select', 'workmode', get_string('workmode', 'examregistrar'), $formats);
        $mform->addHelpButton('workmode', 'workmode', 'examregistrar');
        $mform->setDefault('workmode', EXAMREGISTRAR_MODE_VIEW);

        //-------------------------------------------------------------------------------
        // Adding the rest of examregistrar settings
        $mform->addElement('header', 'examregistrarsettings', get_string('examregistrarsettings', 'examregistrar'));

        $mform->addElement('text', 'annuality', get_string('annuality', 'examregistrar'));
        $mform->setType('annuality', PARAM_ALPHANUMEXT);
        $mform->setDefault('annuality', '');
        $mform->addHelpButton('annuality', 'annuality', 'examregistrar');

        $mform->addElement('text', 'programme', get_string('programme', 'examregistrar'));
        $mform->setType('programme', PARAM_ALPHANUMEXT);
        $mform->setDefault('programme', '');
        $mform->addHelpButton('programme', 'programme', 'examregistrar');

        $days = array(0,1,2,3,4,5,6,7,8,15,21,30);
        $mform->addElement('select', 'lagdays', get_string('lagdays', 'examregistrar'), $days);
        $mform->addHelpButton('lagdays', 'lagdays', 'examregistrar');
        $mform->setDefault('lagdays', 0);

        $mform->addElement('text', 'reviewmod', get_string('reviewmod', 'examregistrar'));
        $mform->setType('reviewmod', PARAM_ALPHANUMEXT);
        $mform->setDefault('reviewmod', '');
        $mform->addHelpButton('reviewmod', 'reviewmod', 'examregistrar');


        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
}
