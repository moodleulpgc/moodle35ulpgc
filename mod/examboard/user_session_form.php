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
 * The form to move users between exam sessions in examboard module
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
class examboard_change_user_session_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $cmid  = $this->_customdata['cmid'];
        // if there is an examid, we are updating. If zero, we ar adding
        $exam = $this->_customdata['exam'];
        $users = $this->_customdata['users'];
        $targets = $this->_customdata['targets'];

        $name = $targets[$exam->id];
        unset($targets[$exam->id]);

        $mform->addElement('static', 'source', get_string('examdata', 'examboard'), $name);
        
        $select = $mform->addElement('select', 'users', get_string('examinees', 'examboard'), $users, array('size'=>6));
        $select->setMultiple(true);
        $mform->addRule('users', null, 'required', '', 'client');
        
        $mform->addElement('select', 'targetexam', get_string('movetoexam', 'examboard'), $targets);
        $mform->setDefault('targetexam', '');
        $mform->addHelpButton('targetexam', 'movetoexam', 'examboard');
        $mform->addRule('targetexam', null, 'required', '', 'client');

        $options = array('keep' => get_string('movetokeep', 'examboard'),
                         'new'  => get_string('movetonew', 'examboard'));
        $mform->addElement('select', 'movetoreturn', get_string('movetoreturn', 'examboard'), $options);
        $mform->setDefault('movetoreturn', 'keep');
        $mform->addHelpButton('movetoreturn', 'movetoreturn', 'examboard');

        
        /*
        $mform->addElement('header', 'boardfieldset', get_string('boarddata', 'examboard'));

        $strnew = ($examid) ? 'updateboard' : 'newboard';
        $action = ($examid) ? 'updateexam' : 'addexam';
        
        $options = array(0 => get_string($strnew, 'examboard')) + $existingboards ;
        
        $mform->addElement('select', 'boardid', get_string('existingboard', 'examboard'), $options);
        $mform->setDefault('boardid', 0);
        $mform->addHelpButton('boardid', 'existingboard', 'examboard');

        $mform->addElement('text', 'title', get_string('boardtitle', 'examboard'), array('size'=>'20'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addHelpButton('title', 'boardtitle', 'examboard');
        $mform->addRule('title', null, 'required', null, 'client');
        //$mform->disabledIf('title', 'boardid', 'neq', 0);
        
        $mform->addElement('text', 'idnumber', get_string('boardidnumber', 'examboard'), array('size'=>'20'));
        $mform->setType('idnumber', PARAM_TEXT);
        $mform->addHelpButton('idnumber', 'boardidnumber', 'examboard');
        $mform->addRule('idnumber', null, 'required', null, 'client');
        //$mform->disabledIf('idnumber', 'boardid', 'neq', 0);
        
        
        $mform->addElement('text', 'name', get_string('boardname', 'examboard'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addHelpButton('name', 'boardname', 'examboard');
        //$mform->disabledIf('name', 'boardid', 'neq', 0);    
        
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
        //$mform->disabledIf('boardactive', 'boardid', 'neq', 0);
        
        $mform->addElement('header', 'examfieldset', get_string('examdata', 'examboard'));

        $options = get_config('examboard', 'examperiods');
        $examperiods = array();
        foreach(explode("\n", $options) as $conv) {
            $key = strstr(trim($conv), ':', true);
            $examperiods[$key] = ltrim(strstr($conv, ':'), ':');
        }
        $mform->addElement('select', 'examperiod', get_string('examperiod', 'examboard'), $examperiods);
        $mform->setDefault('examperiod', 'ord');
        $mform->addHelpButton('examperiod', 'examperiod', 'examboard');
        $mform->addRule('examperiod', null, 'required', null, 'client');
        
        $mform->addElement('text', 'sessionname', get_string('examsession', 'examboard'), array('size'=>'64'));
        $mform->setType('sessionname', PARAM_TEXT);
        $mform->addHelpButton('sessionname', 'examsession', 'examboard');
        
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
            $grouparr[] = $mform->createElement('text', 'bulkaddnum', '', array('size'=>'4'));
            $grouparr[] = $mform->createElement('text', 'bulkaddstart', '', array('size'=>'4'));
            $grouparr[] = $mform->createElement('text', 'bulkaddreplace', '', array('size'=>'4'));
            $grouparr[] = $mform->createElement('submit', 'submitbulkadd', get_string('submitbulkaddexam', 'examboard'));
            $mform->addGroup($grouparr, 'groupbulkadd', get_string('bulkaddnum', 'examboard'), 
                                array('&nbsp;&nbsp;&nbsp;&nbsp; '.get_string('bulkaddstart', 'examboard').'&nbsp;', 
                                      '&nbsp;&nbsp;&nbsp;&nbsp; '.get_string('bulkaddreplace', 'examboard').'&nbsp;',  
                                      '&nbsp;&nbsp;&nbsp;&nbsp; '), false);
            $mform->setType('bulkaddnum', PARAM_INT);
            $mform->setType('bulkaddstart', PARAM_INT);
            $mform->setType('bulkaddreplace', PARAM_TEXT);
            $mform->setDefault('bulkaddnum', 1);
            $mform->setDefault('bulkaddstart', count($existingboards) + 1);
            $mform->setDefault('bulkaddreplace', '#');
            $mform->addHelpButton('groupbulkadd', 'bulkaddnum', 'examboard');
            $mform->disabledIf('groupbulkadd', 'boardid', 'neq', 0);
        }
        */
        
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'exam', $exam->id);
        $mform->setType('exam', PARAM_INT);
        
        $mform->addElement('hidden', 'item', $exam->id);
        $mform->setType('item', PARAM_INT);
        
        $mform->addElement('hidden', 'view', 'exam');
        $mform->setType('view', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'action', 'moveusers');
        $mform->setType('action', PARAM_ALPHAEXT);

        // Add standard buttons.
        $this->add_action_buttons(true, get_string('moveto', 'examboard'));
    }
    
    
    function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

       
        return $errors;
    }
    
    
}
