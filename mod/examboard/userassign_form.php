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
 * The form to manage manual bulk user (student) assignation to exams, with tutors, if used  
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Mnual user exam asignation form.
 *
 * @package    mod_examboard
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class examboard_userassign_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $cmid  = $this->_customdata['cmid'];
        $examboard  = $this->_customdata['examboard'];

        $mform->addElement('textarea', 'userassignation', get_string('userassignation', 'examboard'), 
                                    array('wrap'=>'virtual', 'rows'=>10, 'cols'=>10) );
        $mform->addHelpButton('userassignation', 'userassignation', 'examboard');
        $default = get_string('userid', 'examboard').',  '.get_string('boardidnumber', 'examboard');
        if($examboard->usetutors) {
            $default .=  ',  '.get_string('maintutor', 'examboard');
            $default .=  ',  '.get_string('othertutors', 'examboard');
        }
        $mform->setDefault('userassignation', $default);
        $mform->setType('userassignation', PARAM_TEXT);
        $mform->addRule('userassignation', null, 'required', null, 'client');
    
        $fields = array('id' => get_string('userid', 'examboard'),
                        'idnumber' => get_string('idnumber'),
                        'username' => get_string('username'),
                        );
                        //'fullname' => get_string('fullname'));

        $mform->addElement('select', 'uidfield', get_string('userencoding', 'examboard'), $fields);
        $mform->addHelpButton('uidfield', 'userencoding', 'examboard');
        $mform->setDefault('uidfield', 'idnumber');

        $options = array(EXAMBOARD_ORDER_KEEP   => get_string('orderkeepchosen', 'examboard'),
                         EXAMBOARD_ORDER_RANDOM => get_string('orderrandomize', 'examboard'),
                         EXAMBOARD_ORDER_ALPHA  => get_string('orderalphabetic', 'examboard'),
                         EXAMBOARD_ORDER_TUTOR => get_string('orderalphatutor', 'examboard'),
                         EXAMBOARD_ORDER_LABEL => get_string('orderalphalabel', 'examboard'),
                         );
        $mform->addElement('select', 'userorder', get_string('allocmemberorder', 'examboard'), $options);
        $mform->setDefault('userorder', EXAMBOARD_ORDER_ALPHA);
        $mform->addHelpButton('userorder', 'allocmemberorder', 'examboard');

        if($examboard->usetutors) {
            $mform->addElement('selectyesno', 'tutorcheck', get_string('tutorcheck', 'examboard'));
            $mform->setDefault('tutorcheck', 0);
            $mform->addHelpButton('tutorcheck', 'tutorcheck', 'examboard');
        }

        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'action', 'userassign');
        $mform->setType('action', PARAM_ALPHAEXT);

        // Add standard buttons.
        $this->add_action_buttons(true, get_string('userassign', 'examboard'));
    }
    
}
