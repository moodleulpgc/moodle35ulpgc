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
 * The form to add / update exams in examboard module
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add an exam form.
 *
 * @package    mod_examboard
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class examboard_addexam_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $cm  = $this->_customdata['cm'];
        $existingboards = $this->_customdata['boards'];
        // if there is an examid, we are updating. If zero, we ar adding
        $examid = $this->_customdata['examid'];
        
        $mform->addElement('header', 'boardfieldset', get_string('boarddata', 'examboard'));

        $strnew = ($examid) ? 'updateboard' : 'newboard';
        $action = ($examid) ? 'updateexam' : 'addexam';
        
        $options = array(0 => get_string($strnew, 'examboard')) + $existingboards ;
        
        $mform->addElement('select', 'boardid', get_string('existingboard', 'examboard'), $options);
        $mform->setDefault('boardid', 0);
        $mform->addHelpButton('boardid', 'existingboard', 'examboard');

        $mform->addElement('text', 'title', get_string('boardtitle', 'examboard'), array('size'=>'20'));
        $mform->setType('title', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('title', 'boardtitle', 'examboard');
        $mform->disabledIf('title', 'boardid', 'neq', 0);
        
        $mform->addElement('text', 'idnumber', get_string('boardidnumber', 'examboard'), array('size'=>'20'));
        $mform->setType('idnumber', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('idnumber', 'boardidnumber', 'examboard');
        $mform->disabledIf('idnumber', 'boardid', 'neq', 0);
        
        $mform->addElement('text', 'name', get_string('boardname', 'examboard'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addHelpButton('name', 'boardname', 'examboard');
        $mform->disabledIf('name', 'boardid', 'neq', 0);    
        
        if(groups_get_activity_groupmode($cm)) {
            $groups = array(0 => get_string('allparticipants')) + groups_list_to_menu(groups_get_activity_allowed_groups($cm));
            $mform->addElement('select', 'groupid', get_string('accessgroup', 'examboard'), $groups);
            $mform->setDefault('groupid', 0);
            $mform->addHelpButton('groupid', 'accessgroup', 'examboard');
        } else {
            $mform->addElement('hidden', 'groupid', 0);
            $mform->setType('groupid', PARAM_INT);
        }

        $activestr = get_string('visibility_explain', 'examboard');
        $mform->addElement('advcheckbox', 'boardactive', get_string('boardactive', 'examboard'), $activestr);
        $mform->setDefault('boardactive', 1);
        $mform->disabledIf('boardactive', 'boardid', 'neq', 0);
        
        $mform->addElement('header', 'examfieldset', get_string('examdata', 'examboard'));

        $mform->addElement('text', 'sessionname', get_string('examsession', 'examboard'), array('size'=>'64'));
        $mform->setType('sessionname', PARAM_TEXT);
        $mform->addHelpButton('sessionname', 'examsession', 'examboard');
        $mform->addRule('sessionname', null, 'required', null, 'client');
        
        $mform->addElement('text', 'venue', get_string('examvenue', 'examboard'), array('size'=>'64'));
        $mform->setType('venue', PARAM_TEXT);
        $mform->addHelpButton('venue', 'examvenue', 'examboard');

        $mform->addElement('date_time_selector', 'examdate', get_string('examdate', 'examboard'));
        $mform->addHelpButton('examdate', 'examdate', 'examboard');
        //$mform->addRule('examdate', null, 'required', null, 'client');

        $mform->addElement('duration', 'duration', get_string('examduration', 'examboard'));
        $mform->setDefault('duration', HOURSECS);
        $mform->addHelpButton('duration', 'examduration', 'examboard');
        
        $mform->addElement('advcheckbox', 'examactive', get_string('examactive', 'examboard'), $activestr);
        $mform->setDefault('examactive', 1);
        
        if($action == 'addexam') {
            $mform->addElement('header', 'bulkfieldset', get_string('bulkaddexam', 'examboard'));
            
            $grouparr = array();
            $grouparr[] = $mform->createElement('text', 'bulkaddnum', '', array('size'=>'6'));
            $grouparr[] = $mform->createElement('text', 'bulkaddstart', '', array('size'=>'6'));
            $grouparr[] = $mform->createElement('submit', 'submitbulkadd', get_string('submitbulkaddexam', 'examboard'));
            $mform->addGroup($grouparr, 'groupbulkadd', get_string('bulkaddnum', 'examboard'), 
                                array('  &nbsp;&nbsp;&nbsp;&nbsp;   '.get_string('bulkaddstart', 'examboard'),'  &nbsp;&nbsp;&nbsp;&nbsp;   ' ), false);
            $mform->setType('bulkaddnum', PARAM_INT);
            $mform->setType('bulkaddstart', PARAM_INT);
            $mform->setDefault('bulkaddnum', 1);
            $mform->setDefault('bulkaddstart', count($existingboards) + 1);
            $mform->addHelpButton('groupbulkadd', 'bulkaddnum', 'examboard');
            $mform->disabledIf('groupbulkadd', 'boardid', 'neq', 0);
        }
        

        $mform->addElement('hidden', 'id', $cm->id);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'exam', $examid);
        $mform->setType('exam', PARAM_INT);
        
        $mform->addElement('hidden', 'updateboardid', 0);
        $mform->setType('updateboardid', PARAM_INT);
        
        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_ALPHAEXT);

        // Add standard buttons.
        $this->add_action_buttons(true, get_string($action, 'examboard'));
    }
    
    
    function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

        if($data['boardid'] === 0) {
            if(empty(trim($data['idnumber']))) {
                $errors['idnumber'] = get_string('err_required', 'form'); 
            }
            if(empty(trim($data['title']))) {
                $errors['title'] = get_string('err_required', 'form'); 
            }
        }
        
        return $errors;
    }
    
    
}
