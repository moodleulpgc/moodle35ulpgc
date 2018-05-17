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
 * The form to bulk export examinations in examboard module
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Import examinations form.
 *
 * @package    mod_examboard
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class examboard_export_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $COURSE;

        $mform = $this->_form;
        $cmid  = $this->_customdata['cmid'];
        $mandatory  = $this->_customdata['mandatory'];
        $optional   = $this->_customdata['optional'];
        $exams      = $this->_customdata['exams'];
        
        
        $mform->addElement('header', 'exportexams', get_string('exportexams', 'examboard'));
        
        $size = min(10, count($exams));
        $select = $mform->addElement('select', 'exportedexams', get_string('exportedexams', 'examboard'), $exams, array('size' => $size));
        $select->setMultiple(true);
        $mform->addHelpButton('exportedexams', 'exportedexams', 'examboard');
        $mform->addRule('exportedexams', null, 'required', null, 'client');
        
        
        $options = array(EXAMBOARD_USERTYPE_NONE    => get_string('listbyexam', 'examboard'),
                        EXAMBOARD_USERTYPE_USER     => get_string('listbyuser', 'examboard'),
                        EXAMBOARD_USERTYPE_MEMBER   => get_string('listbymember', 'examboard'),);   
        $mform->addElement('select', 'exportlistby', get_string('exportlistby', 'examboard'), $options);
        $mform->setDefault('exportlistby', EXAMBOARD_USERTYPE_USER);
        $mform->addHelpButton('exportlistby', 'exportlistby', 'examboard');
        
        $mform->addElement('selectyesno', 'includedeputy', get_string('includedeputy', 'examboard'));
        $mform->setDefault('includedeputy', 0);
        $mform->addHelpButton('includedeputy', 'includedeputy', 'examboard');
        
        $mform->addElement('header', 'exportfields', get_string('exportfields', 'examboard'));
        
        $grouparray = array();
            $grouparray[] =& $mform->createElement('static','fixed', '', implode(', ', $mandatory));  
            $grouparray[] =& $mform->createElement('advcheckbox','useridnumber', '', get_string('useridnumber', 'examboard'));
        $group = $mform->addGroup($grouparray, 'fixedfielsdgroup', get_string('fixedfields', 'examboard'), array('  '), false);
        $mform->addHelpButton('fixedfielsdgroup', 'fixedfields', 'examboard');

        $grouparray = array();
        foreach($optional as $key => $field) {
            $grouparray[] =& $mform->createElement('advcheckbox', $key, ' ', $field, array('group' => 1));
        }
        $group = $mform->addGroup($grouparray, 'optionalfieldgroup', get_string('optionalfields', 'examboard'), array('  '), false);
        //$this->add_checkbox_controller(1);

        //$mform->disableIf('', 'exportlistby', 'eq', EXAMBOARD_USERTYPE_USER);
        
        // dataformat selection
        $name = get_string('exportfileselector', 'examboard');
        $mform->addElement('header', 'fileselector', $name);
        $mform->setExpanded('fileselector');

        $name = get_string('exportfilename', 'examboard');
        $mform->addElement('text', 'filename', $name, array('size'=>'40'));
        $mform->setType('filename', PARAM_FILE);
        $mform->addRule('filename', null, 'required', null, 'client');
        
        $formats = core_plugin_manager::instance()->get_plugins_of_type('dataformat');
        $options = array();
        foreach ($formats as $format) {
            if ($format->is_enabled()) {
                $options[$format->name] = get_string('dataformat', $format->component);
            }
        }        
        $name = get_string('exportformatselector', 'examboard');
        $mform->addElement('select', 'dataformat', $name, $options);

        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'action', 'export');
        $mform->setType('action', PARAM_ALPHAEXT);

        // Add standard buttons.
        $this->add_action_buttons(true, get_string('export', 'examboard'));
    }
   
}
