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
 * Manual examiner board exam asignation form.
 *
 * @package    mod_examboard
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class examboard_examinee_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $cmid  = $this->_customdata['cmid'];
        $examboard  = $this->_customdata['examboard'];
        $users = $this->_customdata['users'];
        $tutors = $this->_customdata['tutors'];
        $examid = $this->_customdata['examid'];
        
        $userid = '';
        $tutorid = '';
        $existing = $this->_customdata['existing'];
        
        if($tutor = reset($existing)) {
            $userid = $tutor->userid;
            if($tutor->main) {
                $tutorid = $tutor->tutorid;
            }
        }
        
        $options = array(                                                                                                           
            'multiple' => false,                                                                                                     
            'noselectionstring' => get_string('nothingselected', 'examboard'),   
            'placeholder' => get_string('searchprompt', 'examboard'), 
            'size' => 1,
            'ajax' => false,
        );         
        
        $users = array(0 => get_string('none')) + $users;
        $tutors = array(0 => get_string('none')) + $tutors;        
        $strunknown = get_string('unknowngrader', 'examboard');
        
        if($userid) {
            $options['noselectionstring'] = isset($users[$userid]) ? $users[$userid] : $strunknown;
        }
        $mform->addElement('autocomplete', 'examinee', $examboard->examinee, $users, $options);
        if($userid) {
            $mform->freeze('examinee');
            $mform->addElement('hidden', 'user', $userid);
            $mform->setType('user', PARAM_INT);
        } else {
            $mform->addRule('examinee', null, 'required', null, 'client');
        }
        
        if($examboard->usetutors) { 
            if($tutorid) {
                $options['noselectionstring'] = isset($tutors[$tutorid]) ? $tutors[$tutorid] : $strunknown;
            }
            $mform->addElement('autocomplete', 'tutor', $examboard->tutor, $tutors, $options);
            if($examboard->usetutors == EXAMBOARD_TUTORS_REQ) {
                $mform->addRule('tutor', null, 'required', null, 'client');
            }
            
            $options['noselectionstring'] = get_string('nothingselected', 'examboard');
            $options['multiple'] = true;
            $mform->addElement('autocomplete', 'others', get_string('othertutors', 'examboard'), $tutors, $options);
        }
        
        $mform->addElement('text', 'userlabel', get_string('userlabel', 'examboard'), array('size'=>30));
        $mform->setDefault('userlabel', '');
        $mform->setType('userlabel', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('userlabel', 'userlabel', 'examboard');
        
        $options = array(EXAMBOARD_ORDER_KEEP => get_string('orderkeepchosen', 'examboard'),
                         EXAMBOARD_ORDER_RANDOM => get_string('orderrandomize', 'examboard'),
                         EXAMBOARD_ORDER_ALPHA => get_string('orderalphabetic', 'examboard'),
                         );
        $mform->addElement('select', 'userorder', get_string('allocmemberorder', 'examboard'), $options);
        $mform->setDefault('userorder', EXAMBOARD_ORDER_KEEP);
        $mform->addHelpButton('userorder', 'allocmemberorder', 'examboard');
        
        $mform->addElement('advcheckbox', 'excluded', get_string('excluded', 'examboard'));
        $mform->setDefault('excluded', 0);
        $mform->addHelpButton('excluded', 'excluded', 'examboard');
        
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'exam', $examid);
        $mform->setType('exam', PARAM_INT);
        
        $mform->addElement('hidden', 'view', 'exam');
        $mform->setType('view', PARAM_ALPHAEXT);

        $mform->addElement('hidden', 'item', $examid);
        $mform->setType('item', PARAM_INT);

        $mform->addElement('hidden', 'action', 'updateuser');
        $mform->setType('action', PARAM_ALPHAEXT);

        // Add standard buttons.
        $this->add_action_buttons(true, get_string('saveuser', 'examboard'));
    }
    
    function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

        if(isset($data['tutor']) && isset($data['others']) && is_array($data['others'])) {
            if(in_array($data['tutor'], $data['others'])) {
                $errors['others'] = get_string('tutorasother', 'examboard'); 
            }
        }
        
        return $errors;
    }
    
}
