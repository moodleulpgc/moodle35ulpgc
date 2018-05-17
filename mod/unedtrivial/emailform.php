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
 * Email form
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class emailform extends moodleform {
    //Add elements to form
    public function definition() {
        $mform = $this->_form; // Don't forget the underscore! 
        $mform->addElement('select', 'destiny', get_string('emaildestiny','unedtrivial'), 
                           array('' => '',
                                 '1' => get_string('emailall','unedtrivial'),
                                 '2' => get_string('overviewchart1o1','unedtrivial'),
                                 '3' => get_string('overviewchart1o2','unedtrivial'),
                                 '4' => get_string('overviewchart1o3','unedtrivial'),
                                 '5' => get_string('overviewchart1o4','unedtrivial')), null);
        $mform->addElement('hidden','special','0');
        $mform->setType('special', PARAM_INT);
        $mform->disabledIf('destiny', 'special', 'neq', '0');
        $mform->addElement('text', 'subject', get_string('emailsubject', 'unedtrivial'), array('size'=>'60'));
        $mform->setType('subject', PARAM_TEXT);
        $mform->addElement('editor', 'email', get_string('emailtext', 'unedtrivial'));
        $mform->setType('email', PARAM_RAW);
        $this->add_action_buttons(true,get_string('ok', 'unedtrivial'));
    }
    //Custom validation should be added here
    function validation($data, $files) {
//        if ($data['email'] == '' || (!isset($data['destiny']) && $data['special'] == '0')){
//            return array(1);
//        }else{
            return array();
//        }
    }
}