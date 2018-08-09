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
 * The main mod_examboard configuration form.
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_examboard
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_examboard_mod_form extends moodleform_mod {

    public function disable_datetime($element, $condition, $value) {
        $mform = $this->_form;
        
        foreach(array('day', 'month', 'year', 'hour', 'minute') as $field) {
            $mform->disabledIf($element.'['.$field.']', $condition, 'neq', $value);
        }
    }

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('examboardname', 'examboard'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();
        
        $mform->addElement('header', 'examboardfieldset1', get_string('examboardfieldset', 'examboard'));

        $mform->addElement('text', 'maxboardsize', get_string('maxboardsize', 'examboard'), array('size'=>'10'));
        $mform->setType('maxboardsize', PARAM_INT);
        $mform->setDefault('maxboardsize', 3);
        $mform->addHelpButton('maxboardsize', 'maxboardsize', 'examboard');
        $mform->addRule('maxboardsize', null, 'required', null, 'client');
        $mform->addRule('maxboardsize', null, 'numeric', null, 'client');
        $mform->addRule('maxboardsize', null, 'nonzero', null, 'client');
        
        $options = array(EXAMBOARD_TUTORS_NO => get_string('tutoruseno', 'examboard'),
                        EXAMBOARD_TUTORS_YES => get_string('tutoruseyes', 'examboard'),
                        EXAMBOARD_TUTORS_REQ => get_string('tutoruserequired', 'examboard'),
                        );
        $mform->addElement('select', 'usetutors', get_string('usetutors', 'examboard'), $options);
        $mform->setDefault('usetutors', EXAMBOARD_TUTORS_YES);
        $mform->addHelpButton('usetutors', 'usetutors', 'examboard');
        
        $mform->addElement('header', 'distributionfieldset', get_string('distributionfieldset', 'examboard'));
        
        $options = array(EXAMBOARD_USERTYPE_NONE => get_string('allocmodenone', 'examboard'),
                        EXAMBOARD_USERTYPE_MEMBER => get_string('allocmodemember', 'examboard'),
                        EXAMBOARD_USERTYPE_TUTOR => get_string('allocmodetutor', 'examboard'),
                        );
        $mform->addElement('select', 'allocation', get_string('allocation', 'examboard'), $options);
        $mform->setDefault('allocation', EXAMBOARD_USERTYPE_MEMBER);
        $mform->addHelpButton('allocation', 'allocation', 'examboard');
        
        $mform->addElement('selectyesno', 'examgroups', get_string('examgroups', 'examboard'));
        $mform->setDefault('examgroups', 0);
        $mform->addHelpButton('examgroups', 'examgroups', 'examboard');

        $mform->addElement('text', 'groupingname', get_string('groupingname', 'examboard'), array('size'=>'30'));
        $mform->setType('groupingname', PARAM_ALPHANUMEXT);
        $mform->setDefault('groupingname', '');
        $mform->addHelpButton('groupingname', 'groupingname', 'examboard');
        $mform->disabledIf('groupingname', 'examgroups', 0);

        
        $mform->addElement('header', 'notifyfieldset', get_string('notifyfieldset', 'examboard'));
        
        $mform->addElement('selectyesno', 'requireconfirm', get_string('requireconfirm', 'examboard'));
        $mform->setDefault('requireconfirm', 1);
        $mform->addHelpButton('requireconfirm', 'requireconfirm', 'examboard');
        
        $mform->addElement('duration', 'confirmtime', get_string('confirmtime', 'examboard'));
        $mform->setDefault('confirmtime', DAYSECS);
        $mform->addHelpButton('confirmtime', 'confirmtime', 'examboard');
        
        $mform->addElement('selectyesno', 'notifyconfirm', get_string('notifyconfirm', 'examboard'));
        $mform->setDefault('notifyconfirm', 1);
        $mform->addHelpButton('notifyconfirm', 'notifyconfirm', 'examboard');
        
        $mform->addElement('selectyesno', 'confirmdefault', get_string('confirmdefault', 'examboard'));
        $mform->setDefault('confirmdefault', 1);
        $mform->addHelpButton('confirmdefault', 'confirmdefault', 'examboard');
        //$mform->disabledIf('confirmdefault', 'requireconfirm', '1');
      
        
        $options = array(EXAMBOARD_USERTYPE_NONE => get_string('usernone', 'examboard'),
                        EXAMBOARD_USERTYPE_USER => get_string('userexaminees', 'examboard'),
                        EXAMBOARD_USERTYPE_MEMBER => get_string('usermembers', 'examboard'),
                        EXAMBOARD_USERTYPE_TUTOR => get_string('usertutors', 'examboard'),
                        EXAMBOARD_USERTYPE_STAFF => get_string('userstaff', 'examboard'),
                        EXAMBOARD_USERTYPE_ALL => get_string('userall', 'examboard'),
                        );
        $mform->addElement('select', 'usewarnings', get_string('usewarnings', 'examboard'), $options);
        $mform->setDefault('usewarnings', EXAMBOARD_USERTYPE_NONE);
        $mform->addHelpButton('usewarnings', 'usewarnings', 'examboard');
        
        $mform->addElement('duration', 'warntime', get_string('warntime', 'examboard'));
        $mform->setDefault('warntime', 2*DAYSECS);
        $mform->addHelpButton('warntime', 'warntime', 'examboard');
        $mform->disabledIf('warntime', 'usewarnings', 'eq', EXAMBOARD_USERTYPE_NONE);
        
        $mform->addElement('header', 'publishfieldset', get_string('publishfieldset', 'examboard'));
        
        $options = array(EXAMBOARD_PUBLISH_NO   => get_string('no'),
                        EXAMBOARD_PUBLISH_YES   => get_string('yes'),
                        EXAMBOARD_PUBLISH_DATE  => get_string('publishondate', 'examboard'), 
                        );
        $mform->addElement('select', 'publishboard', get_string('publishboard', 'examboard'), $options);
        $mform->setDefault('publishboard', EXAMBOARD_PUBLISH_NO);
        $mform->addHelpButton('publishboard', 'publishboard', 'examboard');
        
        $mform->addElement('date_time_selector', 'publishboarddate', get_string('publishboarddate', 'examboard'));
        $mform->addHelpButton('publishboarddate', 'publishboarddate', 'examboard');
        $this->disable_datetime('publishboarddate', 'publishboard', EXAMBOARD_PUBLISH_DATE);
        
        $mform->addElement('select', 'publishgrade', get_string('publishgrade', 'examboard'), $options);
        $mform->setDefault('publishgrade', EXAMBOARD_PUBLISH_NO);
        $mform->addHelpButton('publishgrade', 'publishgrade', 'examboard');
        
        $mform->addElement('date_time_selector', 'publishgradedate', get_string('publishgradedate', 'examboard'));
        $mform->addHelpButton('publishgradedate', 'publishgradedate', 'examboard');
        $this->disable_datetime('publishgradedate', 'publishgrade', EXAMBOARD_PUBLISH_DATE);
        
        $mform->addElement('header', 'wordsfieldset', get_string('wordsfieldset', 'examboard'));
        
        foreach(array('chair','secretary', 'vocal', 'examinee', 'tutor') as $field) {
            $mform->addElement('text', $field, get_string('name'.$field, 'examboard'), array('size'=>'10'));
            $mform->setType($field, PARAM_ALPHANUMEXT);
            $mform->setDefault($field, get_string($field.'word', 'examboard'));
            $mform->addHelpButton($field, 'name'.$field, 'examboard');
            $mform->addRule($field, null, 'required', null, 'client');
        }
        
        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        $options = array(EXAMBOARD_GRADING_AVG => get_string('gradingaverage', 'examboard'),
                        EXAMBOARD_GRADING_MAX => get_string('gradingmax', 'examboard'),
                        EXAMBOARD_GRADING_MIN => get_string('gradingmin', 'examboard'),
                        );
                        
        $grouparr = array();
        $grouparr[] = $mform->createElement('select', 'grademode', '', $options);
        $grouparr[] = $mform->createElement('text', 'mingraders', '', array('size'=> 3));
        $mform->addGroup($grouparr, 'grading', get_string('grademode', 'examboard'), 
                                        array(' '.get_string('mingraders', 'examboard').' '), false);
        
        
        //$mform->addElement('select', 'grademode', get_string('grademode', 'examboard'), $options);
        $mform->setDefault('grademode', EXAMBOARD_GRADING_AVG);
        $mform->addHelpButton('grading', 'grademode', 'examboard');
        $mform->disabledIf('grademode', 'grade[modgrade_type]', 'eq', 'none');
        $mform->setType('mingraders', PARAM_INT);
        $mform->setDefault('mingraders', 1);
        $mform->disabledIf('mingraders', 'grade[modgrade_type]', 'eq', 'none');
        $mform->disabledIf('mingraders', 'grademode', 'neq', EXAMBOARD_GRADING_AVG);

        $course = $this->get_course();
        if($gradeables = examboard_get_gradeables($course)) {
            $mform->addElement('select', 'gradeable', get_string('gradeablemod', 'examboard'), $gradeables);
            $mform->addHelpButton('gradeable', 'gradeablemod', 'examboard');
            
            $mform->addElement('select', 'proposal', get_string('proposalmod', 'examboard'), $gradeables);
            $mform->addHelpButton('proposal', 'proposalmod', 'examboard');
            
            $mform->addElement('select', 'defense', get_string('defensemod', 'examboard'), $gradeables);
            $mform->addHelpButton('defense', 'defensemod', 'examboard'); 
        }
       
        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }
}
