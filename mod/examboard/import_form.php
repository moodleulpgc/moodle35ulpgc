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
 * The form to bulk import examinations in examboard module
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
class examboard_import_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $cmid  = $this->_customdata['cmid'];
        $mandatory =  $this->_customdata['mandatory'];
        $optional =  $this->_customdata['optional'];
        
        $mform->addElement('static', 'fixed', get_string('fixedfields', 'examboard'),
                                                implode(', ', $mandatory));
        $mform->addElement('static', 'optional', get_string('optionalfields', 'examboard'),
                                                implode(', ', $optional));

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

        $mform->addElement('advcheckbox', 'ignoremodified', get_string('ignoremodified', 'examboard'), get_string('ignoremodifiedexplain', 'examboard'));
        $mform->addHelpButton('ignoremodified', 'ignoremodified', 'examboard');
        
        $userencodings = array('id' => get_string('userid', 'examboard'), 
                                'idnumber' => get_string('idnumber'),
                                'username' => get_string('username'));
        $mform->addElement('select', 'userencoding', get_string('userencoding', 'examboard'), $userencodings);
        $mform->setDefault('userencoding', 'idnumber');
        $mform->addHelpButton('userencoding', 'userencoding', 'examboard');

        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'action', 'import');
        $mform->setType('action', PARAM_ALPHAEXT);

        // Add standard buttons.
        $this->add_action_buttons(true, get_string('import', 'examboard'));
    }
    
}
