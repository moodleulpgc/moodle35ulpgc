<?php

/**
 * This file contains a local_supervision page
 *
 * @package   local_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/formslib.php');

class supervision_editholidays_form extends moodleform {

    function definition() {

        global $CFG;

        $mform =& $this->_form;

        $mform->addElement('text', 'name', get_string('holidayname', 'local_supervision'), array('size'=>'32'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');

        $zone = $CFG->timezone;
        $mform->addElement('date_selector', 'datestart', get_string('date'), array('timezone' => $zone, 'optional'  => false));

        $mform->addElement('text', 'timeduration', get_string('holidayduration', 'local_supervision'), array('size'=>'4'));
        $mform->setType('timeduration', PARAM_INT);
        $mform->setDefault('timeduration', '1');

        $mform->addElement('text', 'scope', get_string('holidayscope', 'local_supervision'), array('size'=>'10'));
        $mform->setType('scope', PARAM_ALPHA);
        $mform->setDefault('scope', 'N');

        foreach($this->_customdata as $param => $value) {
            $mform->addElement('hidden', $param, $value);
            $mform->setType($param, PARAM_RAW);
        }

        $this->add_action_buttons(true, get_string('save', 'local_supervision'));
    }
}

