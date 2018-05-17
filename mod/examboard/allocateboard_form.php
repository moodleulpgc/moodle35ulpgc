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
 * The form to manage automatic random board members allocation 
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Ramdom board allocation form.
 *
 * @package    mod_examboard
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class examboard_allocateboard_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $cmid  = $this->_customdata['cmid'];
        $examboard  = $this->_customdata['examboard'];
        $groupid = $this->_customdata['groupid'];
        $groups = $this->_customdata['groups'];

        $mform->addElement('header', 'examsfieldset', get_string('examsallocated', 'examboard'));
        
        if($examboard->usetutors && ($examboard->allocation == EXAMBOARD_USERTYPE_MEMBER)) {
            $warning = get_string('examsallocated', 'examboard');
        } else {
            $warning = get_string('examsallocated', 'examboard');
        }
        
        $mform->addElement('static', 'warning1', '', $warning);

        $exams = examboard_get_user_exams($examboard, true, 'idnumber', 'ASC', $groupid);
        $boards = array();
        foreach($exams as $key => $exam) {
            if(array_key_exists($exam->boardid, $boards)) {
                //this insures that a board team with multiple exam sessions is allocated only once
                unset($exams[$key]);
                continue;
            }
            $exams[$key] = $exam->idnumber.' - '.$exam->name ;
            $boards[$exam->boardid] = $exam->boardid;    
        }
        $select = $mform->addElement('select', 'allocatedexams', get_string('allocatedexams', 'examboard'), $exams, array('size'=>8));
        $mform->addHelpButton('allocatedexams', 'allocatedexams', 'examboard');
        $select->setMultiple(true);

        $mform->addElement('header', 'allocfieldset', get_string('allocationsettings', 'examboard'));
        
        foreach(range(1,$examboard->maxboardsize) as $i) {
            $select = $mform->addElement('select', 'choosegroup'.$i, get_string('choosegroup', 'examboard', $i) , $groups, array('size'=>8));
            $select->setMultiple(true);
        }
        
        $mform->addElement('static', 'chooseexplain', '', get_string('chooseexplain', 'examboard'));
        
        $options = array(0 => get_string('orderkeepchosen', 'examboard'),
                         1 => get_string('orderrandomize', 'examboard'));
        $mform->addElement('select', 'userorder', get_string('allocmemberorder', 'examboard'), $options);
        $mform->setDefault('userorder', 0);
        $mform->addHelpButton('userorder', 'allocmemberorder', 'examboard');
        

        $options = array(EXAMBOARD_USERTYPE_NONE => get_string('allocmoderandom', 'examboard'),
                        EXAMBOARD_USERTYPE_MEMBER => get_string('allocmodemember', 'examboard'),
                        EXAMBOARD_USERTYPE_TUTOR => get_string('allocmodetutor', 'examboard'),
                        );

        if($examboard->allocation) {
            $options = array($examboard->allocation => $options[$examboard->allocation]);
        } elseif(!$examboard->usetutors) {
            $options = array(EXAMBOARD_USERTYPE_NONE => get_string('allocmoderandom', 'examboard'));
        }
       
        $mform->addElement('select', 'allocationmode', get_string('allocationmode', 'examboard'), $options);
        $mform->setDefault('allocationmode', $examboard->allocation);
        $mform->addHelpButton('allocationmode', 'allocationmode', 'examboard');

        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'groupid', $groupid);
        $mform->setType('groupid', PARAM_INT);

        
        $mform->addElement('hidden', 'action', 'do_allocateboard');
        $mform->setType('action', PARAM_ALPHAEXT);

        // Add standard buttons.
        $this->add_action_buttons(true, get_string('allocateboard', 'examboard'));
    }
    
}
