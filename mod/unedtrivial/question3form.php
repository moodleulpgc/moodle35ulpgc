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
 * Question 1 form (to create/edit/delete SHORT answer question)
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class question3form extends moodleform {
	public $e = '';
    //Add elements to form
    public function definition() {
        $mform = $this->_form; // Don't forget the underscore! 
        $mform->addElement('editor', 'question', get_string('questiontext', 'unedtrivial'), '');
        $mform->setType('question', PARAM_RAW);
        $mform->addRule('question', null, 'required', null, 'client');
        $mform->addElement('text', 'option1', get_string('answertext', 'unedtrivial'), '');
        $mform->setType('option1', PARAM_RAW);
        $mform->addRule('option1', null, 'required', null, 'client');
        $mform->addElement('checkbox', 'casesensitive', get_string('casesensitive', 'unedtrivial'), '');
        
        $mform->addElement('editor', 'explanation', get_string('explanation', 'unedtrivial'), '');
        $mform->setType('explanation', PARAM_RAW);
        $mform->addRule('explanation', null, 'required', null, 'client');
        
        $this->add_action_buttons(true,get_string('ok', 'unedtrivial'));
    }
    //Custom validation should be added here
    function validation($data, $files) {
	return $this->e;
    }
	
    public static function normaliza ($cadena){
        $ori = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðòóôõöøùúûýýþÿŔŕ';
        $mod = 'aaaaaaaceeeeiiiidoooooouuuuybsaaaaaaaceeeeiiiidoooooouuuyybyRr';
        $cadena = utf8_decode($cadena);
        $cadena = strtr($cadena, utf8_decode($ori), $mod);
        return utf8_encode($cadena);
    }
}