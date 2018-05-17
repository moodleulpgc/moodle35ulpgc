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
 * Student form for question type 1 (SINGLE)
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class goquestion1form extends moodleform {
	public $e = '';
        public $o1;
        public $o2;
        public $o3;
        public $o4;
        public $check = false;
        public $nooption;
        
    //Add elements to form
    public function definition() {
        $mform = $this->_form; // Don't forget the underscore! 
        $mform->addElement('static', 'questiontext', '','');
        $mform->setType('questiontext', PARAM_RAW);
        $mform->addElement('hidden', 'answer1', '-1');
        $mform->setType('answer1', PARAM_TEXT);
        $mform->addElement('hidden', 'answer2', '-1');
        $mform->setType('answer2', PARAM_TEXT);
        $mform->addElement('hidden', 'answer3', '-1');
        $mform->setType('answer3', PARAM_TEXT);
        $mform->addElement('hidden', 'answer4', '-1');
        $mform->setType('answer4', PARAM_TEXT);
        $mform->addElement('radio', 'istrue', '',"",1,null);
        $mform->addElement('radio', 'istrue', '', "",2,null);
        $mform->addElement('radio', 'istrue', '', "",3,null);
        $mform->addElement('radio', 'istrue', '', "",4,null);
        $mform->addElement('submit', 'ok', get_string('send', 'unedtrivial'));
    }
    
    public function definition_after_data() {
        parent::definition_after_data();        
        $mform =& $this->_form;
        if ($this->check){
            $mform->_elements[5]->_text = $this->o1;
            $mform->_elements[6]->_text = $this->o2;
            if ($this->o3 == null){
                unset($mform->_elements[7]);
            }else{
                $mform->_elements[7]->_text = $this->o3;
            }
            if ($this->o4 == null){
                unset($mform->_elements[8]);
            }else{
                $mform->_elements[8]->_text = $this->o4;
            }
            $this->check = false;
        }
    }
    
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}