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
        $mform->setDefault('display_prefs', 0);

        
        $options = array(0 => advuserbulk_get_string('nonchanged', 'bulkuseractions_preferences'),
                        -1 => advuserbulk_get_string('hide','bulkuseractions_preferences'),
                        -2 => advuserbulk_get_string('show','bulkuseractions_preferences'),
                        -3 => advuserbulk_get_string('standard','bulkuseractions_preferences'),
                        $USER->id => advuserbulk_get_string('asuser','bulkuseractions_preferences'),);

        $mform->addElement('select', 'forum_prefs', get_string('forumpreferences'), $options);
        $mform->setDefault('forum_prefs', 0);

        $editors = editors_get_enabled();

        if (count($editors) > 1) {
            $choices = array(0 => advuserbulk_get_string('nonchanged', 'bulkuseractions_preferences'),
                            'delete' => get_string('defaulteditor'));
            $firsteditor = '';
            foreach (array_keys($editors) as $editor) {
                if (!$firsteditor) {
                    $firsteditor = $editor;
                }
                $choices[$editor] = get_string('pluginname', 'editor_' . $editor);
            }
            $choices[$USER->id] = advuserbulk_get_string('asuser','bulkuseractions_preferences');
            
            $mform->addElement('select', 'htmleditor_prefs', get_string('editorpreferences'), $choices);
            $mform->setDefault('htmleditor_prefs', '0');
        }

        $providers = array();
        foreach(get_message_providers() as $key => $provider) {
            $newkey = 'message_provider_'.$provider->component.'_'.$provider->name;
            $providers[$newkey] = get_string('messageprovider:'.$provider->name, $provider->component);
        }
        $select = $mform->addElement('select', 'messages_prefs', get_string('notificationpreferences', 'message'), $providers, array('size'=>10));
        $select->setMultiple(true);

        $processors = array(0 => advuserbulk_get_string('nonchanged', 'bulkuseractions_preferences'),
                            'none' => get_string('none'),);
        foreach(get_message_processors() as $key => $processor) {
            if($processor->enabled && $processor->configured && $processor->available) {
                $processors[$key] = $key;
            }
        }
        $processors['delete'] = advuserbulk_get_string('delete','bulkuseractions_preferences');
        $processors[$USER->id] = advuserbulk_get_string('asuser','bulkuseractions_preferences');
          
        $mform->addElement('select', 'message_loggedin', get_string('loggedin', 'message'), $processors);
        
        $mform->addElement('select', 'message_loggedoff', get_string('loggedoff', 'message'), $processors);

        $this->add_action_buttons();
    }
    
    
    function freeze_all() {
        $mform =& $this->_form;

        $mform->addElement('hidden', 'confirm', 1);
        $mform->setType('confirm', PARAM_INT);

    
        $mform->freeze(array('display_prefs'));
        
    }
    
}
