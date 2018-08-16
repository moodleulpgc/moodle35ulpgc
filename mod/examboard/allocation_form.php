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
 * The form to manage automatic random board members  / users allocation 
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Ramdom board/user allocation form.
 *
 * @package    mod_examboard
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class examboard_allocation_form extends moodleform {

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
        $allocationmode = $this->_customdata['allocationmode'];
        

        $mform->addElement('header', 'examsfieldset', get_string('examsallocated', 'examboard'));
        
        $exams = examboard_get_user_exams($examboard, true, 0, $groupid, 'idnumber ASC');
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
        $mform->addRule('allocatedexams', null, 'required', '', 'client');

        $mform->addElement('header', 'allocfieldset', get_string('allocationsettings', 'examboard'));

        if($examboard->usetutors) {
            if($allocationmode == 'allocateboard') {
                $warning = get_string('allocatewarningboard', 'examboard');
            } else {
                $warning = get_string('allocatewarningusers', 'examboard');
            }
            $mform->addElement('static', 'warning1', '', $warning);
        }
        
        if($allocationmode == 'allocateboard') {
            foreach(range(0,$examboard->maxboardsize -1) as $i) {
                $select = $mform->addElement('select', "choosegroup[$i]", get_string('choosegroup', 'examboard', $i+1) , $groups, array('size'=>8));
                $select->setMultiple(true);
                $mform->addRule("choosegroup[$i]", null, 'required', '', 'client');
            }
        } else {
            $select = $mform->addElement('select', 'sourcegroups', get_string('sourcegroups', 'examboard'), $groups, array('size'=>8));
            $mform->addRule('sourcegroups', null, 'required', '', 'client');
            $select->setMultiple(true);
        }
        
        $mform->addElement('static', 'chooseexplain', '', get_string($allocationmode.'_help', 'examboard'));
        
        if($allocationmode == 'allocateboard') {
            $mform->addElement('advcheckbox', 'repeatable', get_string('allocrepeatable', 'examboard'), get_string('allocrepeatable_help', 'examboard'));
            $mform->addElement('advcheckbox', 'deputy', get_string('allocdeputy', 'examboard'), get_string('allocdeputy_help', 'examboard'));
            $mform->addElement('advcheckbox', 'delexisting', get_string('allocprevious', 'examboard'), get_string('allocprevious_help', 'examboard'));
        } else {
            $options = array(0 => get_string('nolimit', 'examboard'));
            foreach(range(1,25) as $i) {
                $options[$i] = $i;
            }
            $mform->addElement('select', 'usersperexam', get_string('usersperexam', 'examboard'), $options);
            $mform->addHelpButton('usersperexam', 'usersperexam', 'examboard');
        }
        
        
        $options = array(EXAMBOARD_ORDER_KEEP => get_string('orderkeepchosen', 'examboard'),
                         EXAMBOARD_ORDER_RANDOM => get_string('orderrandomize', 'examboard'),
                         EXAMBOARD_ORDER_ALPHA => get_string('orderalphabetic', 'examboard'),);
        $mform->addElement('select', 'userorder', get_string('allocmemberorder', 'examboard'), $options);
        $mform->setDefault('userorder', 0);
        $mform->addHelpButton('userorder', 'allocmemberorder', 'examboard');
        
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'action', $allocationmode);
        $mform->setType('action', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'groupid', $groupid);
        $mform->setType('groupid', PARAM_INT);
        
        // Add standard buttons.
        $this->add_action_buttons(true, get_string($allocationmode, 'examboard'));
    }
    
}
