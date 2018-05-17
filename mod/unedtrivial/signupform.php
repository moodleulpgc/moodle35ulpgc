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
 * Little form to allow student to sing up current UNEDTrivial
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class signupform extends moodleform {
    public $e = '';
    //Add elements to form
    public function definition() {
        global $CFG;
        $mform = $this->_form; // Don't forget the underscore! 
        $group = array();
        $group[] =& $mform->createElement('text', 'email', get_string('email')); // Add elements to your form
        $mform->setType('email', PARAM_NOTAGS);                   //Set type of element
        $group[] =& $mform->createElement('submit', 'ok', get_string('ok', 'unedtrivial'));
        $mform->addGroup($group, 'group', "", ' ', false);
    }
    //Custom validation should be added here
    function validation($data, $files) {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
            $this->e = 'EMAIL ERROR';
            return array(1);
        }else{
            return array();
        }
    }
}