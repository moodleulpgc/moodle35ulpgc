<?php

/**
 * This file contains a local_supervision page
 *
 * @package   local_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/course/lib.php');

class supervision_editsettings_form extends moodleform {

    function definition() {

        global $CFG, $DB;
        
        $config = get_config('local_supervision');

        $mform =& $this->_form;

        $mform->addElement('advcheckbox', 'enablestats', get_string('enablestats', 'local_supervision'), '&nbsp;');
        $mform->setType('enablestats', PARAM_INT);
        $mform->setDefault('enablestats', $config->enablestats);
        $mform->addHelpButton('enablestats', 'enablestats', 'local_supervision');
        
        $roles = get_all_roles();
        $options = role_fix_names($roles, null, ROLENAME_ORIGINAL, true);
        list($usql, $params) = $DB->get_in_or_equal(array('editingteacher', 'teacher'));
        $select = $mform->addElement('select', 'checkedroles', get_string('checkedroles', 'local_supervision'), $options, array('size'=>7));
        $select->setMultiple(true);
        $select->setSelected(explode(',', $config->checkedroles));
        $mform->addHelpButton('checkedroles', 'checkedroles', 'local_supervision');

        $options = array('0' => get_string('choose')) + $options;
        $mform->addElement('select', 'checkerrole', get_string('checkerrole', 'local_supervision'), $options, array('size'=>7));
        $mform->setDefault('checkerrole', $config->checkerrole);
        $mform->addHelpButton('checkerrole', 'checkerrole', 'local_supervision');
        
        $categories =  make_categories_options();
        $select = $mform->addElement('select', 'excludedcats', get_string('excludedcategories', 'local_supervision'), $categories, array('size'=>7));
        $select->setMultiple(true);
        $select->setSelected(explode(',', $config->excludedcats));
        $mform->addHelpButton('excludedcats', 'excludedcategories', 'local_supervision');
        
        $mform->addElement('text', 'excludeshortnames', get_string('excludeshortnames', 'local_supervision'));
        $mform->setType('excludeshortnames', PARAM_NOTAGS);
        $mform->setDefault('excludeshortnames', $config->excludeshortnames);
        $mform->addHelpButton('excludeshortnames', 'excludeshortnames', 'local_supervision');
        
        $mform->addElement('advcheckbox', 'excludecourses', get_string('excludecourses', 'local_supervision'), '&nbsp;');
        $mform->setType('excludecourses', PARAM_INT);
        $mform->setDefault('excludecourses', $config->excludecourses);
        $mform->addHelpButton('excludecourses', 'excludecourses', 'local_supervision');

        $mform->addElement('text', 'startdisplay', get_string('startdisplay', 'local_supervision'));
        $mform->setType('startdisplay', PARAM_TEXT);
        $mform->setDefault('startdisplay', $config->startdisplay);
        $mform->addHelpButton('startdisplay', 'startdisplay', 'local_supervision');
        
        $mform->addElement('advcheckbox', 'enablemail', get_string('enablemail', 'local_supervision'), '&nbsp;');
        $mform->setType('enablemail', PARAM_INT);
        $mform->setDefault('enablemail', $config->enablemail);
        $mform->addHelpButton('enablemail', 'enablemail', 'local_supervision');

        $mform->addElement('select', 'maildelay', get_string('maildelay', 'local_supervision'), 
                array(0,1,2,3,4,5,6,7,10,14,15));
        $mform->setDefault('maildelay', $config->maildelay);
        $mform->addHelpButton('maildelay', 'maildelay', 'local_supervision');
        
        $mform->addElement('advcheckbox', 'coordemail', get_string('coordemail', 'local_supervision'), '&nbsp;');
        $mform->setType('coordemail', PARAM_INT);
        $mform->setDefault('coordemail', $config->coordemail);
        $mform->addHelpButton('coordemail', 'coordemail', 'local_supervision');
        
        $mform->addElement('advcheckbox', 'maildebug', get_string('maildebug', 'local_supervision'), '&nbsp;');
        $mform->setType('maildebug', PARAM_INT);
        $mform->setDefault('maildebug', $config->maildebug);
        $mform->addHelpButton('maildebug', 'maildebug', 'local_supervision');
        
        $mform->addElement('text', 'email', get_string('pendingmail', 'local_supervision'));
        $mform->setType('email', PARAM_NOTAGS);
        $mform->setDefault('email', $config->email);
        $mform->addHelpButton('email', 'pendingmail', 'local_supervision');

        $mform->addElement('advcheckbox', 'synchsupervisors', get_string('synchsupervisors', 'local_supervision'), '&nbsp;');
        $mform->setType('synchsupervisors', PARAM_INT);
        $mform->setDefault('synchsupervisors', $config->synchsupervisors);
        $mform->addHelpButton('synchsupervisors', 'synchsupervisors', 'local_supervision');

        $this->add_action_buttons(true, get_string('save', 'local_supervision'));
    }
}

