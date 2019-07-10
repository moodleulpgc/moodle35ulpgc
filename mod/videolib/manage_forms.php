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
require_once($CFG->libdir.'/formslib.php');


class mod_videolib_view_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
    
        // Add standard buttons.
        //$this->add_action_buttons(true, null);    
    }
}

/**
 * Tracker tools export issues form class
 *
 * @package    mod_videolib
 * @copyright  2019 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_videolib_export_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        
        $cmid =  $this->_customdata['cmid'];
        $action =  $this->_customdata['a'];
        
        $options = array('' => get_string('any'));
        foreach (core_plugin_manager::instance()->get_plugins_of_type('videolibsource') as $plugin) {
//          $options[$source->name] = get_string('source', $source->component);
            $options[$plugin->name] = $plugin->displayname;
        }
        $mform->addElement('select', 'source', get_string('source', 'videolib'), $options);
        $mform->setDefault('source', '');
        
        $mform->addElement('text', 'annuality', get_string('annuality', 'videolib'), array('size'=>'40'));
        $mform->setType('annuality', PARAM_ALPHANUMEXT);
        $mform->setDefault('annuality', '');

        $mform->addElement('text', 'videolibkey', get_string('videolibkey', 'videolib'), array('size'=>'40'));
        $mform->setType('videolibkey', PARAM_FILE);
        $mform->setDefault('videolibkey', '');
        
        $filename = clean_filename(get_string('defaultfilename', 'videolib'));
        $name = get_string('exportfilename', 'videolib');
        $mform->addElement('text', 'filename', $name, array('size'=>'40'));
        $mform->setType('filename', PARAM_FILE);
        $mform->setDefault('filename', $filename);
        $mform->addRule('filename', null, 'required', null, 'client');
        
        $formats = core_plugin_manager::instance()->get_plugins_of_type('dataformat');
        $options = array();
        foreach ($formats as $format) {
            if ($format->is_enabled()) {
                $options[$format->name] = get_string('dataformat', $format->component);
            }
        }        
        $name = get_string('exportformatselector', 'videolib');
        $mform->addElement('select', 'dataformat', $name, $options);

        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'a', $action);
        $mform->setType('a', PARAM_ALPHANUMEXT);

        // Add standard buttons.
        $this->add_action_buttons(true, get_string('export', 'videolib'));
    }
}


/**
 * Tracker tools import issues form class
 *
 * @package    mod_videolib
 * @copyright  2019 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_videolib_import_form extends moodleform {        
        
    /**
     * Form definition
     */
    function definition() {
        global $CFG;
        
        $mform = $this->_form;
        
        $cmid =  $this->_customdata['cmid'];
        $action =  $this->_customdata['a'];

        $filepickeroptions = array();
        $filepickeroptions['filetypes'] = array('.csv', '.txt', 'text/plain', 'text/csv') ;
        $filepickeroptions['maxbytes'] = get_max_upload_file_size();
        $mform->addElement('filepicker', 'recordsfile', get_string('import'), null, $filepickeroptions);

        $encodings = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'grades'), $encodings);
        $mform->addHelpButton('encoding', 'encoding', 'grades');

        $radio = array();
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('septab', 'grades'), 'tab');
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepcomma', 'grades'), 'comma');
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepcolon', 'grades'), 'colon');
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepsemicolon', 'grades'), 'semicolon');
        $mform->addGroup($radio, 'separator', get_string('separator', 'grades'), ' ', false);
        $mform->addHelpButton('separator', 'separator', 'grades');
        $mform->setDefault('separator', 'comma');

        $mform->addElement('advcheckbox', 'updateonimport', get_string('updateonimport', 'videolib'), get_string('updateonimportexplain', 'videolib'));
        $mform->addHelpButton('updateonimport', 'updateonimport', 'videolib');
        
        if(has_capability('moodle/site:config', context_system::instance())) {
            $mform->addElement('advcheckbox', 'removebefore', get_string('removebefore', 'videolib'), get_string('removebeforeexplain', 'videolib'));
            $mform->addHelpButton('removebefore', 'removebefore', 'videolib');
        }
        
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'a', $action);
        $mform->setType('a', PARAM_ALPHANUMEXT);

        // Add standard buttons.
        $this->add_action_buttons(true, get_string('import', 'videolib'));        
    }
}

/**
 * Tracker tools export issues form class
 *
 * @package    mod_videolib
 * @copyright  2019 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_videolib_update_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        
        $cmid =  $this->_customdata['cmid'];
        $action =  $this->_customdata['a'];
        
        foreach (core_plugin_manager::instance()->get_plugins_of_type('videolibsource') as $plugin) {
//          $options[$source->name] = get_string('source', $source->component);
            $options[$plugin->name] = $plugin->displayname;
        }
        $mform->addElement('select', 'source', get_string('source', 'videolib'), $options);
        $mform->setDefault('source', '');
        
        $mform->addElement('text', 'annuality', get_string('annuality', 'videolib'), array('size'=>'40'));
        $mform->setType('annuality', PARAM_ALPHANUMEXT);
        $mform->setDefault('annuality', '');

        $mform->addElement('text', 'videolibkey', get_string('videolibkey', 'videolib'), array('size'=>'40'));
        $mform->setType('videolibkey', PARAM_FILE);
        $mform->setDefault('videolibkey', '');

        $mform->addElement('text', 'remoteid', get_string('remoteid', 'videolib'), array('size'=>'40'));
        $mform->setType('remoteid', PARAM_FILE);
        $mform->setDefault('remoteid', '');

        $mform->addElement('hidden', 'itemid', '');
        $mform->setType('itemid', PARAM_INT);
        
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'a', $action);
        $mform->setType('a', PARAM_ALPHANUMEXT);

        // Add standard buttons.
        $this->add_action_buttons(true, get_string($action, 'videolib'));
        
    }
}

/**
 * Tracker tools export issues form class
 *
 * @package    mod_videolib
 * @copyright  2019 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_videolib_delete_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        
        $cmid =  $this->_customdata['cmid'];
        $action =  $this->_customdata['a'];
        $item =  $this->_customdata['item'];
        
        $confirm = '';
        if(isset($item->confirm) && $item->confirm) {
            $mform->addElement('static', 'conf', '', get_string('confirmdelmessage', 'videolib', $item->count));
            $confirm = 1;
        }
        
        // we want to batch delete, present search fields
        foreach (core_plugin_manager::instance()->get_plugins_of_type('videolibsource') as $plugin) {
          //$options[$source->name] = get_string('source', $source->component);
            $options[$plugin->name] = $plugin->displayname;
        }
        $mform->addElement('select', 'source', get_string('source', 'videolib'), $options);
        $mform->setDefault('source', '');
        
        $mform->addElement('text', 'annuality', get_string('annuality', 'videolib'), array('size'=>'40'));
        $mform->setType('annuality', PARAM_FILE);
        $mform->setDefault('annuality', '');

        $mform->addElement('text', 'videolibkey', get_string('videolibkey', 'videolib'), array('size'=>'40'));
        $mform->setType('videolibkey', PARAM_FILE);
        $mform->setDefault('videolibkey', '');

        $mform->addElement('text', 'remoteid', get_string('remoteid', 'videolib'), array('size'=>'40'));
        $mform->setType('remoteid', PARAM_FILE);
        $mform->setDefault('remoteid', '');

        
        $mform->addElement('hidden', 'confirm', 1);
        $mform->setType('confirm', PARAM_INT);
        
        $mform->addElement('hidden', 'itemid', '');
        $mform->setType('itemid', PARAM_INT);
        
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'a', $action);
        $mform->setType('a', PARAM_ALPHANUMEXT);

        if($confirm) {
            $confirmed = (isset($item->confirmed) && $item->confirmed) ? '_confirmed' : '';
            $elements = array('source', 'annuality', 'videolibkey', 'remoteid');
            foreach($elements as $element) {
                $mform->getElement($element)->setPersistantFreeze(true);
                //$mform->freeze($element);
                $mform->disabledIf($element, 'itemid', 'neq', 0);
                $mform->disabledIf($element, 'confirm', 'neq', '');
                $mform->addElement('hidden', $element.'_confirmed', $item->{$element.$confirmed});
                $mform->setType($element.'_confirmed', PARAM_TEXT);
            }

            $mform->addElement('hidden', 'count', $item->count);
            $mform->setType('count', PARAM_INT);

            $mform->addElement('selectyesno', 'confirmed', get_string('confirmdelete', 'videolib'));
        } else {
            $mform->addElement('hidden', 'confirmed', 0);
            $mform->setType('confirmed', PARAM_INT);
        }
        
        // Add standard buttons.
        $this->add_action_buttons(true, get_string($action, 'videolib'));
    }
    
}


