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
                        $USER->id => advuserbulk_get_string('asuser','bulkuseractions_preferences'),);
        $mform->addElement('select', 'display_prefs', advuserbulk_get_string('display_prefs', 'bulkuseractions_preferences'), $options);

        
        $options = array(0 => advuserbulk_get_string('nonchanged', 'bulkuseractions_preferences'),
                        -1 => advuserbulk_get_string('standard','bulkuseractions_preferences'),
                        -2 => advuserbulk_get_string('show','bulkuseractions_preferences'),
                        -3 => advuserbulk_get_string('delete','bulkuseractions_preferences'),
                        $USER->id => advuserbulk_get_string('asuser','bulkuseractions_preferences'),);

        $mform->addElement('select', 'forum_prefs', advuserbulk_get_string('forum_prefs', 'bulkuseractions_preferences'), $options);

        $editors = editors_get_enabled();

        if (count($editors) > 1) {
            $choices = array(0 => advuserbulk_get_string('nonchanged', 'bulkuseractions_preferences'),
                            '' => get_string('defaulteditor'));
            $firsteditor = '';
            foreach (array_keys($editors) as $editor) {
                if (!$firsteditor) {
                    $firsteditor = $editor;
                }
                $choices[$editor] = get_string('pluginname', 'editor_' . $editor);
            }
            $choices[-$USER->id] = advuserbulk_get_string('asuser','bulkuseractions_preferences');
            
            $mform->addElement('select', 'preference_htmleditor', get_string('textediting'), $choices);
            $mform->addHelpButton('preference_htmleditor', 'textediting');
            $mform->setDefault('preference_htmleditor', 0);
        }
/*
        $user = $USER;
        $processors = get_message_processors();
        
        
        print_object($processors);
        $providers = get_message_providers();
        print_object($providers);
        
        $preferences = \core_message\api::get_all_message_preferences($processors, $providers, $user);
        $notificationlistoutput = new \core_message\output\preferences\notification_list($processors, $providers,$preferences, $user);

        //print_object($preferences);
        //print_object($notificationlistoutput);

        */
        $providers = array();
        foreach(get_message_providers() as $key => $provider) {
            $newkey = 'message_provider_'.$provider->component.'_'.$provider->name;
            $providers[$newkey] = get_string('messageprovider:'.$provider->name, $provider->component);
        }
        $select = $mform->addElement('select', 'preference_messages', get_string('asuser','bulkuseractions_preferences'), $providers, array('size'=>10));
        $select->setMultiple(true);
        $mform->addHelpButton('preference_messages', 'textediting', 'bulkuseractions_preferences');


        $processors = array(0 => advuserbulk_get_string('nonchanged', 'bulkuseractions_preferences'),
                            'none' => advuserbulk_get_string('none','bulkuseractions_preferences'),);
        foreach(get_message_processors() as $key => $processor) {
            if($processor->enabled && $processor->configured && $processor->available) {
                $processors[$key] = $key;
            }
        }
        $processors['del'] = advuserbulk_get_string('delete','bulkuseractions_preferences');
        $processors[$USER->id] = advuserbulk_get_string('asuser','bulkuseractions_preferences');
          
        $mform->addElement('select', 'message_loggedin', advuserbulk_get_string('display_loggedin', 'bulkuseractions_preferences'), $processors);
        
        $mform->addElement('select', 'message_loggedoff', advuserbulk_get_string('display_loggedof', 'bulkuseractions_preferences'), $processors);

    
       
       
        $this->add_action_buttons();
    }
    
    
    function freeze_all() {
        $mform =& $this->_form;

        $mform->addElement('hidden', 'confirm', 1);
        $mform->setType('confirm', PARAM_INT);

    
        $mform->freeze(array('display_prefs'));
        
    }
    
}
