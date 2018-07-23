<?php

require_once($CFG->libdir.'/formslib.php');

class advuserbulk_user_preferences_form extends moodleform {

    function definition() {
        global $USER;
        $mform =& $this->_form;
        $mform->addElement('header', 'general', advuserbulk_get_string('pluginname', 'bulkuseractions_preferences'));


        $options = array(0 => advuserbulk_get_string('nonchanged', 'bulkuseractions_preferences'),
                        -1 => advuserbulk_get_string('hide','bulkuseractions_preferences'),
                        -2 => advuserbulk_get_string('show','bulkuseractions_preferences'),
                        -3 => advuserbulk_get_string('delete','bulkuseractions_preferences'),
                        $USER->id => advuserbulk_get_string('asuser','bulkuseractions_preferences'),
        
        );
        $mform->addElement('select', 'display_prefs', advuserbulk_get_string('display_prefs', 'bulkuseractions_preferences'), $options);

       
        $this->add_action_buttons();
    }
    
    
    function freeze_all() {
        $mform =& $this->_form;

        $mform->addElement('hidden', 'confirm', 1);
        $mform->setType('confirm', PARAM_INT);

    
        $mform->freeze(array('display_prefs'));
        
    }
    
}
