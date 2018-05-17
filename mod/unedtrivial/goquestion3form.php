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
 * Student form for question type 3 (SHORT)
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class goquestion3form extends moodleform {
	public $e = '';
        
    //Add elements to form
    public function definition() {
        $mform = $this->_form; // Don't forget the underscore! 
        $mform->addElement('static', 'questiontext', '','');
        $mform->setType('questiontext', PARAM_RAW);
        $mform->addElement('hidden', 'solution', '0');
        $mform->setType('solution', PARAM_TEXT);
        $mform->addElement('hidden', 'casesensitive', '0');
        $mform->setType('casesensitive', PARAM_TEXT);
        $mform->addElement('text', 'answer', get_string('answer', 'unedtrivial'));
        $mform->setType('answer', PARAM_TEXT);
        $mform->addElement('submit', 'ok', get_string('send', 'unedtrivial'));
    }
    
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}