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
 * A form for tracker field options bulk import.
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/tracker/locallib.php');

/**
 * Tracker field options import form class
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_trackertools_fieldoptions_form extends moodleform {

    /**
     * Form definition
     */
    function definition() {
        $mform =& $this->_form;
        $cmid  = $this->_customdata['cmid'];
        $tracker = $this->_customdata['tracker'];
        $element = $this->_customdata['element'];
        $context = context_module::instance($cmid);



        $mode = array(0 => get_string('loadadd', 'report_trackertools'),
                      1 => get_string('loadupdate', 'report_trackertools'),
                      2 => get_string('loaddelete', 'report_trackertools'),);
        $mform->addElement('select', 'loadmode', get_string('loadmode', 'report_trackertools'), $mode);
        $defaultmode = $element->hasoptions() ? 1 : 0;
        $mform->setDefault('loadmode', $defaultmode);
        $mform->addHelpButton('loadmode', 'loadmode', 'report_trackertools');
        
        $options = array();
        foreach($element->options as $option) {
            $options[] = $option->name.'|'.$option->description;
        }
        $text = implode("\n", $options);

        $mform->addElement('textarea', 'fieldoptions', get_string('fieldoptions', 'report_trackertools'), array('wrap'=>'virtual', 'rows'=>10, 'cols'=>5));
        $mform->setDefault('fieldoptions', $text);
        $mform->addHelpButton('fieldoptions', 'fieldoptions', 'report_trackertools');
        
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'eid', $element->id);
        $mform->setType('eid', PARAM_INT);        
        
        $this->add_action_buttons(true, get_string('loadoptions', 'report_trackertools'));
    }

}

