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
 * The main mod_library configuration form.
 *
 * @package     mod_library
 * @copyright   2019 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

include_once($CFG->dirroot.'/mod/library/locallib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_library
 * @copyright  2019 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_library_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        
        $config = get_config('library');

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('libraryname', 'library'), array('size' => '48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        //$mform->addHelpButton('name', 'name', 'library');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        $mform->addElement('header', 'repositorysection', get_string('repositoryheader', 'library'));

        $options = array(LIBRARY_DISPLAYMODE_FILE => get_string('file'),
                            LIBRARY_DISPLAYMODE_FOLDER => get_string('folder'),
                            LIBRARY_DISPLAYMODE_TREE => get_string('modetree', 'library'),
                        );
        $mform->addElement('select', 'displaymode', get_string('displaymode', 'library'), $options);
        $mform->setDefault('displaymode', 0);
        $mform->addHelpButton('displaymode', 'repository', 'library');

        $options = array();
        foreach (core_plugin_manager::instance()->get_plugins_of_type('librarysource') as $plugin) {
            $options[$plugin->name] = $plugin->displayname;
        }
        $mform->addElement('select', 'source', get_string('repository', 'library'), $options);
        $mform->setDefault('source', 0);
        $mform->addHelpButton('source', 'repository', 'library');

        $mform->addElement('text', 'reponame', get_string('repositoryname', 'library'), array('size'=>'60'));
        $mform->setType('reponame', PARAM_TEXT);
        $mform->addHelpButton('reponame', 'repositoryname', 'library');
        
        $mform->addElement('text', 'pathname', get_string('pathname', 'library'), array('size'=>'60'));
        $mform->setType('pathname', PARAM_PATH);
        $mform->addHelpButton('pathname', 'pathname', 'library');

        
        $mform->addElement('header', 'parameterssection', get_string('parametersheader', 'library'));
        $mform->addElement('static', 'parametersinfo', '', get_string('parametersheader_help', 'library'));

        $mform->addElement('text', 'searchpattern', get_string('searchpattern', 'library'), array('size'=>'60'));
        $mform->setType('searchpattern', PARAM_TEXT);
        $mform->addHelpButton('searchpattern', 'searchpattern', 'library');
        //$mform->addRule('searchpattern', null, 'required', null, 'client');
        
        $parcount = 5;
        $options = library_get_variable_options($config);
        for ($i=0; $i < $parcount; $i++) {
            $parameter = "parameter_$i";
            $variable  = "variable_$i";
            $pargroup = "pargoup_$i";
            $group = array(
                $mform->createElement('text', $parameter, '', array('size'=>'12$config')),
                $mform->createElement('selectgroups', $variable, '', $options),
            );
            $mform->addGroup($group, $pargroup, get_string('parameterinfo', 'library'), ' ', false);
            $mform->setType($parameter, PARAM_RAW);
        }
        
        /*
        $options = array('0' => get_string('none'), '1' => get_string('allfiles'), '2' => get_string('htmlfilesonly'));
        $mform->addElement('select', 'filterfiles', get_string('filterfiles', 'library'), $options);
        $mform->setDefault('filterfiles', $config->filterfiles);
        $mform->setAdvanced('filterfiles', true);
        */
        
        /*
        $mform->addElement('static', 'label1', 'librarysettings', get_string('librarysettings', 'library'));
        $mform->addElement('header', 'optionssection', get_string('appearance'));
        */

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
            $mform->addElement('select', 'display', get_string('displayselect', 'library'), $options);
            $mform->setDefault('display', $config->display);
            $mform->addHelpButton('display', 'displayselect', 'library');
        }
        
        $mform->addElement('hidden', 'revision');
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);
        
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
        $canmanage = has_capability('mod/library:manage', $this->context);
        
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
