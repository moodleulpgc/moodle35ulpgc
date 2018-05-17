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
 * This file contains the form to define the predefined grade to set
 *
 * @package assignfeedback_wtpeer
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once ($CFG->libdir.'/formslib.php');

/**
 * assignfeedback wtpeer assessment options form
 *
 * @package assignfeedback_wtpeer
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_wtpeer_manageconfig_form extends moodleform {

    function definition (){
        global $USER;

        $mform = $this->_form;
        $wtpeer = $this->_customdata['wtpeer'];
        $assignment = $wtpeer->get_assignment();
        $prefix = 'config_';

        $name = get_string('weightselector', 'assignfeedback_wtpeer');
        $mform->addElement('header', 'weightselector', $name);
        $mform->setExpanded('weightselector');
        
        $options = array('size'=> 6);
        $error = get_string('err_numeric', 'form');
        foreach(array('auto', 'peer', 'tutor', ) as $type) {
            $element = 'weight_'.$type;
            $name = get_string($element, 'assignfeedback_wtpeer');
            $mform->addElement('text', $prefix.$element, $name, $options);
            $mform->setType($prefix.$element, PARAM_TEXT);
            $mform->setDefault($prefix.$element, 0);
            $mform->addRule($prefix.$element, $error, 'numeric', 'client');
            $mform->addHelpButton($prefix.$element, $element, 'assignfeedback_wtpeer');
        }
        $mform->addElement('static', 'weightinfo', '', get_string('weightinfo', 'assignfeedback_wtpeer'));

        $name = get_string('dateselector', 'assignfeedback_wtpeer');
        $mform->addElement('header', 'dateselector', $name);
        $mform->setExpanded('dateselector');
        
        $options = array(0 => get_string('accessbydate', 'assignfeedback_wtpeer'),
                        1 => get_string('accessaftersubmission', 'assignfeedback_wtpeer'),
                        2 => get_string('accessafterfinal', 'assignfeedback_wtpeer'));
        $name = get_string('peeraccessmode', 'assignfeedback_wtpeer');
        $mform->addElement('select', $prefix.'peeraccessmode', $name, $options);
        $mform->addHelpButton($prefix.'peeraccessmode', 'peeraccessmode', 'assignfeedback_wtpeer');
        
        $options = array('optional'=>true);
        foreach(array('auto', 'peer', 'tutor', 'grader') as $type) {
            $element = 'startgrading_'.$type;
            $name = get_string($element, 'assignfeedback_wtpeer');
            $mform->addElement('date_time_selector', $prefix.$element, $name, $options);
            //$mform->addHelpButton($element, $element, 'assignfeedback_assign');

            $element = 'endgrading_'.$type;
            $name = get_string($element, 'assignfeedback_wtpeer');
            $mform->addElement('date_time_selector', $prefix.$element, $name, $options);
            //$mform->addHelpButton($element, $element, 'assignfeedback_wtpeer');
        }
        
        $name = get_string('publishselector', 'assignfeedback_wtpeer');
        $mform->addElement('header', 'publishselector', $name);
        $mform->setExpanded('publishselector');
        
        $publish = array(0 => get_string('publishno', 'assignfeedback_wtpeer'),
                        1 => get_string('publishyes', 'assignfeedback_wtpeer'),
                        2 => get_string('publishondate', 'assignfeedback_wtpeer'));
        $name = get_string('publishassessment', 'assignfeedback_wtpeer');
        $mform->addElement('select', $prefix.'publishassessment', $name, $publish);
        $mform->addHelpButton($prefix.'publishassessment', 'publishassessment', 'assignfeedback_wtpeer');

        $name = get_string('publishassessmentdate', 'assignfeedback_wtpeer');
        $mform->addElement('date_time_selector', $prefix.'publishassessmentdate', $name);
        $mform->disabledIf($prefix.'publishassessmentdate', $prefix.'publishassessment', 'neq', 2);

        $options = array('auto'=>get_string('rowauto', 'assignfeedback_wtpeer'), 
                        'peer'=>get_string('rowpeer', 'assignfeedback_wtpeer'), 
                        'tutor'=>get_string('rowtutor', 'assignfeedback_wtpeer'), 
                        'grader'=>get_string('rowgrader', 'assignfeedback_wtpeer'));
        $name = get_string('publishmarkers', 'assignfeedback_wtpeer');
        $select = $mform->addElement('select', $prefix.'publishmarkers', $name, $options);
        $select->setMultiple(true);
        $select->setSelected(array('grader'));
        $mform->addHelpButton($prefix.'publishmarkers', 'publishmarkers', 'assignfeedback_wtpeer');
        
        $publish = array(0 => get_string('publishmanual', 'assignfeedback_wtpeer'),
                        1 => get_string('publishauto', 'assignfeedback_wtpeer'),
                        2 => get_string('publishondate', 'assignfeedback_wtpeer'));
        $name = get_string('publishgrade', 'assignfeedback_wtpeer');
        $mform->addElement('select', $prefix.'publishgrade', $name, $publish);
        $mform->addHelpButton($prefix.'publishgrade', 'publishgrade', 'assignfeedback_wtpeer');

        $name = get_string('publishgradedate', 'assignfeedback_wtpeer');
        $mform->addElement('date_time_selector', $prefix.'publishgradedate', $name);
        $mform->disabledIf($prefix.'publishgradedate', $prefix.'publishgrade', 'neq', 2);

        $wtpeer->add_standard_form_items($mform);
        
        $this->add_action_buttons(true);

    }


    function validation($data, $files) {
        global $CFG, $USER, $DB;
        $errors = parent::validation($data, $files);
        $prefix = 'config_';

        $sum = 0;
        foreach(array('auto', 'peer', 'tutor') as $type) {
            $element = $prefix.'weight_'.$type;
            if($data[$element] < 0 || $data[$element] > 100) {
                $errors[$element] = get_string('invalidweightout', 'assignfeedback_wtpeer');
            }
            $sum += $data[$element];
        }
        if($sum > 100) {
            $errors['weightinfo'] = get_string('invalidweightsum', 'assignfeedback_wtpeer');
        }
        return $errors;
    }

}



/**
 * assignfeedback wtpeer assessment options form
 *
 * @package assignfeedback_wtpeer
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_wtpeer_manageallocations_form extends moodleform {

    function definition (){
        global $USER;
    
        $mform = $this->_form;
        $wtpeer = $this->_customdata['wtpeer'];
        $assignment = $wtpeer->get_assignment();
        $course = $assignment->get_course();

        $groups = groups_get_all_groups($course->id);
        $context = $assignment->get_context();

        if ($groups) {
            $mform->addElement('header', 'usersselector', get_string('usersselector', 'assignfeedback_wtpeer'));
            $options = array();
            $options[0] = get_string('any');
            foreach ($groups as $group) {
                $options[$group->id] = format_string($group->name);
            }
            $mform->addElement('select', 'subgroupid', get_string('selectfromgroup', 'assignfeedback_wtpeer'), $options);
            $mform->setDefault('subgroupid', 0);
        } else {
            $mform->addElement('hidden', 'subgroupid');
            $mform->setType('subgroupid', PARAM_INT);
            $mform->setConstant('subgroupid', 0);
        }

    
        $mform->addElement('header', 'markersselector', get_string('markersselector', 'assignfeedback_wtpeer'));

        /// Get applicable roles - used in menus etc later on
        $options = array(0=>get_string('all'));
        $options += role_fix_names(get_profile_roles($context), $context, ROLENAME_ALIAS, true);
        $mform->addElement('select', 'roleid', get_string('selectfromrole', 'assignfeedback_wtpeer'), $options);
        $mform->addHelpButton('roleid', 'selectfromrole', 'assignfeedback_wtpeer');
        $student = get_archetype_roles('student');
        $student = reset($student);

        if ($student and array_key_exists($student->id, $options)) {
            $mform->setDefault('roleid', $student->id);
        }    
    
        if ($groupings = groups_get_all_groupings($course->id)) {
            $options = array();
            $options[0] = get_string('any');
            foreach ($groupings as $grouping) {
                $options[$grouping->id] = format_string($grouping->name);
            }
            $mform->addElement('select', 'groupingid', get_string('selectfromgrouping', 'assignfeedback_wtpeer'), $options);
            $mform->setDefault('groupingid', 0);
        } else {
            $mform->addElement('hidden', 'groupingid');
            $mform->setType('groupingid', PARAM_INT);
            $mform->setConstant('groupingid', 0);
        }

        if ($groups) {
            $options = array();
            $options[0] = get_string('any');
            foreach ($groups as $group) {
                $options[$group->id] = format_string($group->name);
            }
            $mform->addElement('select', 'groupid', get_string('selectfromgroup', 'assignfeedback_wtpeer'), $options);
            $mform->setDefault('groupid', 0);
        } else {
            $mform->addElement('hidden', 'groupid');
            $mform->setType('groupid', PARAM_INT);
            $mform->setConstant('groupid', 0);
        }
        
        $coursecontext = $assignment->get_course_context();
        
        /*
        if (has_capability('moodle/course:viewsuspendedusers', $coursecontext)) {
            $mform->addElement('checkbox', 'includeonlyactiveenrol', get_string('includeonlyactiveenrol', 'group'), '');
            $mform->addHelpButton('includeonlyactiveenrol', 'includeonlyactiveenrol', 'group');
            $mform->setDefault('includeonlyactiveenrol', true);
        }
        */
        
        $mform->addElement('header', 'randomallocationsettings', get_string('allocationsettings', 'assignfeedback_wtpeer'));

        $options = array();
        $weights = $wtpeer->get_assessment_weights();
        foreach($weights as $item => $weight) {
            if($weight && $item != 'auto') {
                $options[$item] = get_string('row'.$item, 'assignfeedback_wtpeer');
            }
        }
    
        $name = get_string('assessmode', 'assignfeedback_wtpeer');
        $mform->addElement('select', 'alloctype', $name, $options);
        $mform->addHelpButton('alloctype', 'assessmode', 'assignfeedback_wtpeer');

        $options_numper = array(
            'sub' => get_string('numperauthor', 'assignfeedback_wtpeer'),
            'marker'   => get_string('numperreviewer', 'assignfeedback_wtpeer')
        );
        $grpnumofreviews = array();
        $nums = range(0,25);
        $grpnumofreviews[] = $mform->createElement('select', 'numofreviews', '',
                $nums);
        $mform->setDefault('numofreviews', 5);
        $grpnumofreviews[] = $mform->createElement('select', 'numper', '', $options_numper);
        $mform->setDefault('numper', 'marker');
        $mform->addGroup($grpnumofreviews, 'grpnumofreviews', get_string('numofreviews', 'assignfeedback_wtpeer'),
                array(' '), false);
        $mform->addHelpButton('grpnumofreviews', 'numofreviews', 'assignfeedback_wtpeer');
                
        if ($assignment->get_instance()->teamsubmission) {
            $mform->addElement('checkbox', 'excludesamegroup', get_string('excludesamegroup', 'assignfeedback_wtpeer'));
            $mform->setDefault('excludesamegroup', 1);
            $mform->addHelpButton('excludesamegroup', 'excludesamegroup', 'assignfeedback_wtpeer');
        } else {
            $mform->addElement('hidden', 'excludesamegroup', 1);
            $mform->setType('excludesamegroup', PARAM_BOOL);
        }

        $mform->addElement('advcheckbox', 'excludeotheralloc', get_string('excludeotheralloc', 'assignfeedback_wtpeer'));
        $mform->setDefault('excludeotheralloc', 1);
        $mform->addHelpButton('excludeotheralloc', 'excludeotheralloc', 'assignfeedback_wtpeer');

        
        $options = array('keep'=>get_string('allocskeep', 'assignfeedback_wtpeer'),
                        'keepmax'=>get_string('allocskeepmax', 'assignfeedback_wtpeer'),
                        'remove'=>get_string('allocsremove', 'assignfeedback_wtpeer'),);
        $mform->addElement('select', 'currentallocs', get_string('currentallocs', 'assignfeedback_wtpeer'), $options);
        $mform->setDefault('currentallocs', 'keepmax');
        $mform->addHelpButton('currentallocs', 'currentallocs', 'assignfeedback_wtpeer');

        if ($weights['auto']) {
            $mform->addElement('checkbox', 'addautoalloc', get_string('addautoalloc', 'assignfeedback_wtpeer'));

        } else {
            $mform->addElement('static', 'addautoallocinfo', get_string('addautoalloc', 'assignfeedback_wtpeer'),
                                                                 get_string('selfassessmentdisabled', 'assignfeedback_wtpeer'));
        }

        $mform->addElement('hidden','seed');
        $mform->setType('seed', PARAM_INT);
        
        $wtpeer->add_standard_form_items($mform);
        
        $mform->setExpanded('markersselector');
        $mform->setExpanded('randomallocationsettings');
        
        $this->add_action_buttons();
    
    }
    
    function validation($data, $files) {
        global $CFG, $USER, $DB;
        $errors = parent::validation($data, $files);
        
        
        /*
        $prefix = 'config_';

        $sum = 0;
        foreach(array('auto', 'peer', 'tutor') as $type) {
            $element = $prefix.'weight_'.$type;
            if($data[$element] < 0 || $data[$element] > 100) {
                $errors[$element] = get_string('invalidweightout', 'assignfeedback_wtpeer');
            }
            $sum += $data[$element];
        }
        if($sum > 100) {
            $errors['weightinfo'] = get_string('invalidweightsum', 'assignfeedback_wtpeer');
        }
        
        */
        return $errors;
    }
    
}  
