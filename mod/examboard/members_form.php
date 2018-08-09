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
class examboard_members_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $cmid  = $this->_customdata['cmid'];
        $examboard  = $this->_customdata['examboard'];
        $users = $this->_customdata['users'];
        $members = $this->_customdata['members'];
        $assigned = $this->_customdata['assigned'];
        $other = $this->_customdata['other'];
        $boardid = $this->_customdata['boardid'];
        
        $strnothingselected = get_string('nothingselected', 'examboard');
        $options = array(                                                                                                           
            'multiple' => false,                                                                                                     
            'noselectionstring' => $strnothingselected,   
            'placeholder' => get_string('searchprompt', 'examboard'), 
            'size' => 1,
            'ajax' => false,
        );         
        
        $users = array(0 => get_string('none')) + $users;
        
        // complete member names from known graders
        $strunknown = get_string('unknowngrader', 'examboard');
        //$knownmembers = array_fill(0, $examboard->maxboardsize - 1, array($strunknown,$strunknown));
        foreach($members as $member) {
            $userid = $member->userid;
            $knownmembers[$member->sortorder][$member->deputy] = isset($users[$userid]) ? $users[$userid] : $strunknown;
        }
        
        $strdeputy = get_string('deputy', 'examboard');
        
        $grouparr = array();
        $options['noselectionstring'] = isset($knownmembers[0][0]) ? $knownmembers[0][0] : $strnothingselected;
        $grouparr[] =& $mform->createElement('autocomplete', "memberids[0]", '', $users, $options);
        //$grouparr[] =& $mform->createElement('advcheckbox', "member[0]", '', 'exampted'); // use if exempted is put in use
        $options['noselectionstring'] = isset($knownmembers[0][1]) ? $knownmembers[0][1] : $strnothingselected;
        $grouparr[] =& $mform->createElement('autocomplete', "deputyids[0]", '', $users, $options);
        //
        $mform->addGroup($grouparr, 'usergroup0', $examboard->chair, array($strdeputy, ''), false);

        $grouparr = array();
        $grouparr[] =& $mform->createElement('autocomplete', "memberids[1]", '', $users, $options);
        $grouparr[] =& $mform->createElement('autocomplete', "deputyids[1]", '', $users, $options);
        $mform->addGroup($grouparr, 'usergroup1', $examboard->secretary, array($strdeputy, ''), false);
        
        foreach(range(2, $examboard->maxboardsize - 1) as $n) {
            $grouparr = array();
            $grouparr[] =& $mform->createElement('autocomplete', "memberids[$n]", '', $users, $options);
            $grouparr[] =& $mform->createElement('autocomplete', "deputyids[$n]", '', $users, $options);
            $mform->addGroup($grouparr, "usergroup$n", $examboard->vocal.' '.($n-1), array($strdeputy, ''), false);
        }
        
        //assign exams
        $exams = array();
        foreach(($assigned + $other) as  $exam) {
            $exams[$exam->id] = $exam->idnumber;
            if(isset($exam->sessionname) && $exam->sessionname) {
                $exams[$exam->id] .= ' ('.$exam->sessionname.')';    
            }
        }
        
        $select = $mform->addElement('select', 'assignedexams', get_string('assignedexams', 'examboard'), $exams, array('size'=>5));
        $select->setMultiple(true);
        $select->setSelected(array_keys($assigned));
        $mform->addHelpbutton('assignedexams', 'assignedexams', 'examboard');
        $mform->addRule('assignedexams', null, 'required', null, 'client');
        
        
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'board', $boardid);
        $mform->setType('board', PARAM_INT);

        $mform->addElement('hidden', 'view', 'board');
        $mform->setType('view', PARAM_ALPHAEXT);
        
        $mform->addElement('hidden', 'item', $boardid);
        $mform->setType('item', PARAM_INT);
        
        $mform->addElement('hidden', 'action', 'editmembers');
        $mform->setType('action', PARAM_ALPHAEXT);

        // Add standard buttons.
        $this->add_action_buttons(true, get_string('savemembers', 'examboard'));
    }
    
    function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

        if(isset($data['memberids']) && is_array($data['memberids']) && 
                        (count($data['memberids']) - count(array_unique($data['memberids'])))) {
            $errors['memberids'] = get_string('memberduplicatedname', 'examboard'); 
        }
        
        if(isset($data['deputyids']) && is_array($data['deputyids']) && 
                        (count($data['memberids']) - count(array_unique($data['memberids'])))) {
            $errors['deputyids'] = get_string('deputyduplicatedname', 'examboard'); 
        }

        if(isset($data['memberids']) && is_array($data['memberids']) &&
                                    isset($data['deputyids']) && is_array($data['deputyids'])) {
            foreach($data['memberids'] as $k => $user) {
                if(in_array($user, $data['deputyids'])) {
                    $errors['memberids'] = get_string('memberasdeputy', 'examboard'); 
                }
            }
        }
        
        return $errors;
    }
    
}
