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
 * Question 2 form (to create/edit/delete MULTIPLE answer question)
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class question2form extends moodleform {
	public $e = '';
	
    //Add elements to form
    public function definition() {
        $mform = $this->_form; // Don't forget the underscore! 
        $mform->addElement('editor', 'question', get_string('questiontext', 'unedtrivial'), '');
        $mform->setType('question', PARAM_RAW);
        $mform->addRule('question', null, 'required', null, 'client');

        $group = array();
        $group[] =& $mform->createElement('checkbox', 'istrue1', '');
        $group[] =& $mform->createElement('textarea', 'option1', '', 'wrap="virtual" rows="2" cols="60"');
        $mform->setType('option1', PARAM_TEXT);
        $mform->addGroup($group, 'group', "", ' ', false);
        $group = array();
        $group[] =& $mform->createElement('checkbox', 'istrue2', '');
        $group[] =& $mform->createElement('textarea', 'option2', '', 'wrap="virtual" rows="2" cols="60"');
        $mform->setType('option2', PARAM_TEXT);
        $mform->addGroup($group, 'group', "", ' ', false);
        $group = array();
        $group[] =& $mform->createElement('checkbox', 'istrue3', '');
        $group[] =& $mform->createElement('textarea', 'option3', '', 'wrap="virtual" rows="2" cols="60"');
        $mform->setType('option3', PARAM_TEXT);
        $mform->addGroup($group, 'group', "", ' ', false);
        $group = array();
        $group[] =& $mform->createElement('checkbox', 'istrue4', '');
        $group[] =& $mform->createElement('textarea', 'option4', '', 'wrap="virtual" rows="2" cols="60"');
        $mform->setType('option4', PARAM_TEXT);
        $mform->addGroup($group, 'group', "", ' ', false);
        
        $mform->addElement('checkbox', 'shuffle', "Shuffle answers", '');
        $mform->addElement('editor', 'explanation', get_string('explanation', 'unedtrivial'), '');
        $mform->setType('explanation', PARAM_RAW);
        $mform->addRule('explanation', null, 'required', null, 'client');
        
        $this->add_action_buttons(true,get_string('ok', 'unedtrivial'));
    }
    //Custom validation should be added here
    function validation($data, $files) {
        $this->e = array();
        $empty = 0;
        if ($data['option1'] == ''){
            $empty += 1;
        }
        if ($data['option2'] == ''){
            $empty += 1;
        }
        if ($data['option3'] == ''){
            $empty += 1;
        }
        if ($data['option4'] == ''){
            $empty += 1;
        }
        if ($empty > 2){
            $this->e = get_string('error1question1form', 'unedtrivial');
			return array(1);
        }
        if (!array_key_exists ('istrue1',$data) && !array_key_exists ('istrue2',$data) && 
            !array_key_exists ('istrue3', $data) && !array_key_exists ( 'istrue4', $data)){
            $this->e = get_string('error1question2form', 'unedtrivial');
			return array(1);
        }
        if (array_key_exists ('istrue1',$data) && $data['option1'] == '' || array_key_exists ('istrue2',$data) && $data['option2'] == '' ||
			array_key_exists ('istrue3',$data) && $data['option3'] == '' || array_key_exists ('istrue4',$data) && $data['option4'] == '' ){
            $this->e = get_string('error2question2form', 'unedtrivial');
            return array(1);
        }
        return array();
    }
}