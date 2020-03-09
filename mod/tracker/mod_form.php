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

defined('MOODLE_INTERNAL') || die();

/**
 * This view allows checking deck states
 *
 * @package mod_tracker
 * @category mod
 * @author Valery Fremaux
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * overrides moodleform for test setup
 */
class mod_tracker_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $COURSE, $DB;

        $mform    =& $this->_form;
        //-------------------------------------------------------------------------------

	  	$mform->addElement('header', 'general', tracker_getstring('general', 'form'));

	  	$mform->addElement('text', 'name', tracker_getstring('name'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements(tracker_getstring('intro', 'tracker'));

        // $mform->addRule('summary', get_string('required'), 'required', null, 'client');
      	$modeoptions['bugtracker'] = tracker_getstring('mode_bugtracker', 'tracker');
      	$modeoptions['ticketting'] = tracker_getstring('mode_ticketting', 'tracker');
      	$modeoptions['taskspread'] = tracker_getstring('mode_taskspread', 'tracker');
      	// ecastro ULPGC
      	$modeoptions['usersupport'] = tracker_getstring('mode_usersupport', 'tracker');
      	$modeoptions['boardreview'] = tracker_getstring('mode_boardreview', 'tracker');
      	$modeoptions['tutoring'] = tracker_getstring('mode_tutoring', 'tracker');
      	
      	$modeoptions['customized'] = tracker_getstring('mode_customized', 'tracker');
	  	$mform->addElement('select', 'supportmode', tracker_getstring('supportmode', 'tracker'), $modeoptions);
        $mform->addHelpButton('supportmode', 'supportmode', 'tracker');

	  	$mform->addElement('text', 'ticketprefix', tracker_getstring('ticketprefix', 'tracker'), array('size' => 5));
        $mform->setType('ticketprefix', PARAM_TEXT);
        $mform->setAdvanced('ticketprefix');

        $stateprofileopts = array(
			ENABLED_OPEN => tracker_getstring('open', 'tracker'),
			ENABLED_RESOLVING => tracker_getstring('resolving', 'tracker'),
			ENABLED_WAITING => tracker_getstring('waiting', 'tracker'),
			ENABLED_RESOLVED => tracker_getstring('resolved', 'tracker'),
			ENABLED_ABANDONNED => tracker_getstring('abandonned', 'tracker'),
			ENABLED_TESTING => tracker_getstring('testing', 'tracker'),
			ENABLED_PUBLISHED => tracker_getstring('published', 'tracker'),
			ENABLED_VALIDATED => tracker_getstring('validated', 'tracker'),
            ENABLED_TRANSFERED => tracker_getstring('transfered', 'tracker')
        );
      	$select = &$mform->addElement('select', 'stateprofile', tracker_getstring('stateprofile', 'tracker'), $stateprofileopts);
        $mform->setType('stateprofile', PARAM_INT);
        $mform->disabledIf('stateprofile', 'supportmode', 'neq', 'customized');
        $select->setMultiple(true);
        $mform->setAdvanced('stateprofile');

      	$mform->addElement('textarea', 'thanksmessage', tracker_getstring('thanksmessage', 'tracker'), array('cols' => 60, 'rows' => 10));
        $mform->disabledIf('thanksmessage', 'supportmode', 'neq', 'customized');
        $mform->setType('thanksmessage', PARAM_TEXT);
        $mform->setAdvanced('thanksmessage');

	  	$mform->addElement('advcheckbox', 'enablecomments', tracker_getstring('enablecomments', 'tracker'));
        $mform->addHelpButton('enablecomments', 'enablecomments', 'tracker');

	  	$mform->addElement('advcheckbox', 'allownotifications', tracker_getstring('notifications', 'tracker'));
        $mform->addHelpButton('allownotifications', 'notifications', 'tracker');

	  	$mform->addElement('advcheckbox', 'strictworkflow', tracker_getstring('strictworkflow', 'tracker'));
        $mform->addHelpButton('strictworkflow', 'strictworkflow', 'tracker');
        $mform->setAdvanced('strictworkflow');

        $name = get_string('allowsubmissionsfromdate', 'tracker');
        $options = array('optional'=>true);
        $mform->addElement('date_time_selector', 'allowsubmissionsfromdate', $name, $options);
        $mform->addHelpButton('allowsubmissionsfromdate', 'allowsubmissionsfromdate', 'tracker');
        $mform->disabledIf('allowsubmissionsfromdate', 'supportmode', 'eq', 'usersupport');
        $name = get_string('duedate', 'tracker');
        $mform->addElement('date_time_selector', 'duedate', $name, $options);
        $mform->addHelpButton('duedate', 'duedate', 'tracker');
        $mform->disabledIf('duedate', 'supportmode', 'eq', 'usersupport');

        $stateprofileopts = array(
            POSTED => tracker_getstring('posted', 'tracker'),
			OPEN => tracker_getstring('open', 'tracker'),
			RESOLVING => tracker_getstring('resolving', 'tracker'),
			WAITING => tracker_getstring('waiting', 'tracker'),
			RESOLVED => tracker_getstring('resolved', 'tracker'),
			ABANDONNED => tracker_getstring('abandonned', 'tracker'),
			TESTING => tracker_getstring('testing', 'tracker'),
			PUBLISHED => tracker_getstring('published', 'tracker'),
			VALIDATED => tracker_getstring('validated', 'tracker'),
            TRANSFERED => tracker_getstring('transfered', 'tracker')
        );
      	$select = &$mform->addElement('select', 'statenonrepeat', tracker_getstring('statenonrepeat', 'tracker'), $stateprofileopts);
        $mform->setType('statenonrepeat', PARAM_INT);
        $mform->addHelpButton('statenonrepeat', 'statenonrepeat', 'tracker');
        $mform->disabledIf('statenonrepeat', 'supportmode', 'eq', 'usersupport');
        $select->setMultiple(true);
        $mform->setAdvanced('statenonrepeat');

        
        if (isset($this->_cm->id) && $assignableusers = get_users_by_capability(context_module::instance($this->_cm->id), 'mod/tracker:resolve', 'u.id,'.get_all_user_name_fields(true, 'u'), 'lastname,firstname')) {
            $useropts[0] = get_string('none');
            foreach ($assignableusers as $assignable) {
                $useropts[$assignable->id] = fullname($assignable);
            }
		    $mform->addElement('select', 'defaultassignee', tracker_getstring('defaultassignee', 'tracker'), $useropts);
            $mform->addHelpButton('defaultassignee', 'defaultassignee', 'tracker');
            $mform->disabledIf('defaultassignee', 'supportmode', 'eq', 'taskspread');
            $mform->setAdvanced('defaultassignee');
        } else {
            $mform->addElement('hidden', 'defaultassignee', 0);
        }
        $mform->setType('defaultassignee', PARAM_INT);

        if ($subtrackers = $DB->get_records_select('tracker', " id != 0 " )) {
            $trackermoduleid = $DB->get_field('modules', 'id', array('name' => 'tracker'));
            $subtrackersopts = array();
            foreach ($subtrackers as $st) {
                if ($targetcm = $DB->get_record('course_modules', array('instance' => $st->id, 'module' => $trackermoduleid))) {
                    $targetcontext = context_module::instance($targetcm->id);
                    if (has_any_capability(array('mod/tracker:manage', 'mod/tracker:develop', 'mod/tracker:resolve'), $targetcontext)) {
                        $trackercourseshort = $DB->get_field('course', 'shortname', array('id' => $st->course));
                        $subtrackersopts[$st->id] = $trackercourseshort.' - '.$st->name;
                    }
                }
            }
            if (!empty($subtrackersopts)) {
		      	$select = &$mform->addElement('select', 'subtrackers', tracker_getstring('subtrackers', 'tracker'), $subtrackersopts);
                $mform->setType('subtrackers', PARAM_INT);
                $mform->setAdvanced('subtrackers');
                $select->setMultiple(true);
            }
        }

        if ($CFG->mnet_dispatcher_mode == 'strict') {
            $mform->addElement('advcheckbox', 'networkable', get_string('networkable', 'tracker'), get_string('yes'), 0);
            $mform->addHelpButton('networkable', 'networkable', 'tracker');
            $mform->setAdvanced('networkable');
        }


/*
        $mform->addElement('text', 'failovertrackerurl', get_string('failovertrackerurl', 'tracker'), array('size' => 80));  // ecastro ULPGC removed, not used
        $mform->setType('failovertrackerurl', PARAM_URL);
        $mform->setAdvanced('failovertrackerurl');
*/
        $options['idnumber'] = true;
        $options['groups'] = false;
        $options['groupings'] = false;
        $options['gradecat'] = false;
        $this->standard_coursemodule_elements($options);
        $this->add_action_buttons();
    }

    function set_data($defaults) {

        if (property_exists($defaults, 'statenonrepeat')) {
            if(isset($defaults->statenonrepeat) && $defaults->statenonrepeat) {
                $defaults->statenonrepeat = explode(',', $defaults->statenonrepeat);
            }
        }
    
        if (!property_exists($defaults, 'enabledstates')) {
            $defaults->stateprofile = array();

            $defaults->stateprofile[] = ENABLED_OPEN; // state when opened by the assigned
            $defaults->stateprofile[] = ENABLED_RESOLVING; // state when asigned tells he starts processing
            // $defaults->stateprofile[] = ENABLED_WAITING; // state when ticket is blocked by an external cause
            $defaults->stateprofile[] = ENABLED_RESOLVED; // state when issue has an identified solution provided by assignee
            $defaults->stateprofile[] = ENABLED_ABANDONNED; // state when issue is no more relevant by external cause
            // $defaults->stateprofile[] = ENABLED_TESTING; // state when assignee submits issue to requirer and needs acknowledge
            // $defaults->stateprofile[] = ENABLED_PUBLISHED; // state when solution is realy published in production (not testing)
            // $defaults->stateprofile[] = ENABLED_VALIDATED; // state when all is clear and acknowledge from requirer in production
        } else {
            $defaults->stateprofile = array();
            if ($defaults->enabledstates & ENABLED_OPEN) $defaults->stateprofile[] = ENABLED_OPEN;
            if ($defaults->enabledstates & ENABLED_RESOLVING) $defaults->stateprofile[] = ENABLED_RESOLVING;
            if ($defaults->enabledstates & ENABLED_WAITING) $defaults->stateprofile[] = ENABLED_WAITING;
            if ($defaults->enabledstates & ENABLED_RESOLVED) $defaults->stateprofile[] = ENABLED_RESOLVED;
            if ($defaults->enabledstates & ENABLED_ABANDONNED) $defaults->stateprofile[] = ENABLED_ABANDONNED;
            if ($defaults->enabledstates & ENABLED_TESTING) $defaults->stateprofile[] = ENABLED_TESTING;
            if ($defaults->enabledstates & ENABLED_PUBLISHED) $defaults->stateprofile[] = ENABLED_PUBLISHED;
            if ($defaults->enabledstates & ENABLED_VALIDATED) $defaults->stateprofile[] = ENABLED_VALIDATED;
        }

        parent::set_data($defaults);

    }

    function definition_after_data() {
      $mform    =& $this->_form;
    }

    function validation($data, $files = null) {
        $errors = array();
        return $errors;
    }

}
