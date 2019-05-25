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
 * The main mod_videolib configuration form.
 *
 * @package     mod_videolib
 * @copyright   2018 Enrique Castro @ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

include_once($CFG->dirroot.'/mod/videolib/locallib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_videolib
 * @copyright  2018 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_videolib_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        
        //print_object($this->current);
        //print_object($this->_course);
        //print_object($this->_cm);
        //print_object($this->context);
        

        
        $config = get_config('videolib');

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        $mform->addElement('header', 'sourcesection', get_string('sourceheader', 'videolib'));
        
        $options = array();
        foreach (core_plugin_manager::instance()->get_plugins_of_type('videolibsource') as $plugin) {
            $options[$plugin->name] = $plugin->displayname;
        }

        $mform->addElement('select', 'source', get_string('source', 'videolib'), $options);
        $mform->setDefault('source', 0);
        $mform->addHelpButton('source', 'source', 'videolib');

        $options = array(0 => get_string('searchtype_id', 'videolib'), 
                         1 => get_string('searchtype_pattern', 'videolib'),);
        $mform->addElement('select', 'searchtype', get_string('searchtype', 'videolib'), $options);
        $mform->setDefault('searchtype', 0);
        $mform->addHelpButton('searchtype', 'searchtype', 'videolib');

        $mform->addElement('text', 'searchpattern', get_string('searchpattern', 'videolib'), array('size'=>'60'));
        $mform->setType('searchpattern', PARAM_TEXT);
        $mform->addHelpButton('searchpattern', 'searchpattern', 'videolib');
        $mform->addRule('searchpattern', null, 'required', null, 'client');
        if(!$canmanage = has_capability('mod/videolib:manage', $this->context)) {
            $mform->disabledIf('searchpattern', 'searchtype', 'eq', 1);
        }
        
        

        $mform->addElement('header', 'parameterssection', get_string('parametersheader', 'videolib'));
        $mform->addElement('static', 'parametersinfo', '', get_string('parametersheader_help', 'videolib'));

        $parcount = 5;
        
        $options = videolib_get_variable_options($config);
        for ($i=0; $i < $parcount; $i++) {
            $parameter = "parameter_$i";
            $variable  = "variable_$i";
            $pargroup = "pargoup_$i";
            $group = array(
                $mform->createElement('text', $parameter, '', array('size'=>'12')),
                $mform->createElement('selectgroups', $variable, '', $options),
            );
            $mform->addGroup($group, $pargroup, get_string('parameterinfo', 'videolib'), ' ', false);
            $mform->setType($parameter, PARAM_RAW);
            $mform->disabledIf($pargroup, 'searchtype', 'eq', 0);
        }

        $mform->addElement('header', 'optionssection', get_string('appearance'));
        
        $mform->addElement('advcheckbox', 'printheading', get_string('printheading', 'videolib'));
        $mform->setDefault('printheading', $config->printheading);
        $mform->addElement('advcheckbox', 'printintro', get_string('printintro', 'videolib'));
        $mform->setDefault('printintro', $config->printintro);

        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }

        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'videolib'), $options);
            $mform->setDefault('display', $config->display);
            $mform->addHelpButton('display', 'displayselect', 'videolib');
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'videolib'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'videolib'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
        }

        
        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }
    
    function data_preprocessing(&$default_values) {
        if (!empty($default_values['displayoptions'])) {
            $displayoptions = unserialize($default_values['displayoptions']);
            if (isset($displayoptions['printheading'])) {
                $default_values['printheading'] = $displayoptions['printheading'];
            }
            if (isset($displayoptions['printintro'])) {
                $default_values['printintro'] = $displayoptions['printintro'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $default_values['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $default_values['popupheight'] = $displayoptions['popupheight'];
            }
        }
        if (!empty($default_values['parameters'])) {
            $parameters = unserialize($default_values['parameters']);
            $i = 0;
            foreach ($parameters as $parameter=>$variable) {
                $default_values['parameter_'.$i] = $parameter;
                $default_values['variable_'.$i]  = $variable;
                $i++;
            }
        }
    }

    function definition_after_data() {
        parent::definition_after_data();
        $mform    =& $this->_form;
        $canmanage = has_capability('mod/videolib:manage', $this->context);
        
        if(!$canmanage) {
            $parcount = 5;
            $elements = array();
            for ($i=0; $i < $parcount; $i++) {
                $elements[] = "parameter_$i";
                $elements[]  = "variable_$i";
                $elements[] = "pargoup_$i";
            }
            $elements[] = 'searchtype';
                        
            foreach($elements as $name) {
                if($mform->elementExists($name)) {
                    $el =& $mform->getElement($name);
                    $el->freeze();
                    $el->setPersistantFreeze(true);
                }
            }
            
            $mform->disabledIf('searchpattern', 'searchtype', 'noteq', 0);
            
        }


    }
    
}
