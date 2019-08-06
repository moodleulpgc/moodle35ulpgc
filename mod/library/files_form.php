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
 * The repository upload form in mod_library
 *
 * @package     mod_library
 * @copyright   2019 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//include_once($CFG->dirroot.'/mod/library/locallib.php');
require_once($CFG->libdir.'/formslib.php');
//require_once($CFG->dirroot.'/mod/library/lib.php');

/**
 * Module instance settings form.
 *
 * @package    mod_library
 * @copyright  2019 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_library_files_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        
        $cmid = $this->_customdata['id']; // course_module id
        $action = $this->_customdata['action'];
        
        $return_types = FILE_REFERENCE;
        $subdirs = 0;
        
        if($action == 'add') {
            $mform->addElement('select', 'insertpath', get_string('insertpath', 'library'), $this->_customdata['folders']);
            $mform->addHelpButton('insertpath', 'insertpath', 'library');

            $options = array(LIBRARY_FILEUPDATE_UPDATE  => get_string('update', 'library'),
                LIBRARY_FILEUPDATE_REOLD   => get_string('renameold', 'library'),
                LIBRARY_FILEUPDATE_RENEW   => get_string('renamenew', 'library'),        
                LIBRARY_FILEUPDATE_NO      => get_string('updateno', 'library'),
            );
            $mform->addElement('select', 'updatemode', get_string('updatemode', 'library'), $options);
            $mform->setDefault('updatemode', LIBRARY_FILEUPDATE_UPDATE);
            $mform->addHelpButton('updatemode', 'updatemode', 'library');
        
            $return_types = FILE_INTERNAL | FILE_EXTERNAL;
            $subdirs = 1;
        }
        
        $mform->addElement('filemanager', 'files', get_string('files'), null, 
                            array(  'subdirs'=>$subdirs, 
                                    'accepted_types'=>'*', 
                                    'return_types'=> $return_types)
                            );
        $mform->addHelpButton('files', $action.'files', 'library');
                            
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_ALPHANUMEXT);
        
        // Add standard buttons.
        $this->add_action_buttons();
    }
    

}
