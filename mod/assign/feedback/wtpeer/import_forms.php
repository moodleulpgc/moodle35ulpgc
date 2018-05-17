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
 * This file contains the form to define the predefined grade to set
 *
 * @package assignfeedback_wtpeer
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once ($CFG->libdir.'/formslib.php');


/**
 * assignfeedback wtpeer iport marker allocation  form
 *
 * @package assignfeedback_wtpeer
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_wtpeer_importmarkerallocs_form extends moodleform {

    function definition (){
        global $USER;
    
        $mform = $this->_form;
        $wtpeer = $this->_customdata['wtpeer'];
        $assignment = $wtpeer->get_assignment();

        $mform->addElement('header', 'general', get_string('importmarkers', 'assignfeedback_wtpeer'));        
        
        $fileoptions = array('subdirs'=>0,
                                'maxbytes'=>$assignment->get_course()->maxbytes,
                                'accepted_types'=>'csv',
                                'maxfiles'=>1,
                                'return_types'=>FILE_INTERNAL);

        $mform->addElement('filepicker', 'markersfile', get_string('uploadafile'), null, $fileoptions);
        $mform->addRule('markersfile', get_string('uploadnofilefound'), 'required', null, 'client');
        $mform->addHelpButton('markersfile', 'markersfile', 'assignfeedback_wtpeer');
        
        $a = new stdClass;
        $a->user = core_text::strtolower(get_string('user'));
        $a->marker = core_text::strtolower(get_string('marker', 'assign'));
        $a->item = core_text::strtolower(get_string('assessment', 'assignfeedback_wtpeer'));
        $a->itemnames = array();
        $weights = $wtpeer->get_assessment_weights();
        foreach($weights as $item => $weight) {
            if($weight) {
                $a->itemnames[]= core_text::strtolower(get_string('row'.$item, 'assignfeedback_wtpeer'));
            }
        }
        if($a->itemnames) {
            $a->itemnames = implode ('|', $a->itemnames);
        } else {
            $a->itemnames = '';
        }
        
        $mform->addElement('static', 'explain1', '', get_string('headercolumns', 'assignfeedback_wtpeer', $a));

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
        
        if ($assignment->get_instance()->teamsubmission) {
            $mform->addElement('selectyesno', 'applytoall', get_string('applytoteam', 'assign'));
            $mform->setDefault('applytoall', 1);
        } else {
            $mform->addElement('hidden', 'applytoall', 0);
            $mform->setType('applytoall', PARAM_INT);
        }
        
        $options = array(0  => get_string('no'),
                         1  => get_string('removeitemmarkers', 'assignfeedback_wtpeer'),
                         2  => get_string('removeallmarkers', 'assignfeedback_wtpeer'));
        $mform->addElement('select', 'removemarkers', get_string('removeexisting', 'assignfeedback_wtpeer'), $options);
        $mform->addHelpButton('removemarkers', 'removeexisting', 'assignfeedback_wtpeer');
        $mform->setType('removemarkers', PARAM_INT);
        $mform->setDefault('removemarkers', 0);

        $mform->addElement('hidden', 'confirmimport', 0);
        $mform->setType('confirmimport', PARAM_INT);

        $wtpeer->add_standard_form_items($mform);
        
        $this->add_action_buttons();
    
    }
    
    function validation($data, $files) {
        global $CFG, $USER, $DB;
        $errors = parent::validation($data, $files);
        
        return $errors;
    }
    
}  

/**
 * assignfeedback wtpeer iport marker allocation  form
 *
 * @package assignfeedback_wtpeer
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_wtpeer_importallocsconfirm_form extends moodleform {

    function definition (){
        global $USER;
    
        $mform = $this->_form;
        $wtpeer = $this->_customdata['wtpeer'];
        $confirm = $this->_customdata['confirm'];
        $assignment = $wtpeer->get_assignment();

        if($confirm) {
            // if we are confirming 
            // we only need the names for retrieving, data is not to be use here
            $mform->addElement('hidden', 'importid', 0);
            $mform->setType('importid', PARAM_INT);
            $mform->addElement('hidden', 'encoding', 'a');
            $mform->setType('encoding', PARAM_ALPHANUMEXT);
            $mform->addElement('hidden', 'separator', 'a');
            $mform->setType('separator', PARAM_ALPHA);
            $mform->addElement('hidden', 'draftid', 0);
            $mform->setType('draftid', PARAM_INT);
            $mform->addElement('hidden', 'removemarkers', 0);
            $mform->setType('removemarkers', PARAM_INT);
            $mform->addElement('hidden', 'applytoall', 0);
            $mform->setType('applytoall', PARAM_INT);
        } else {
            $mform->addElement('header', 'general', get_string('importmarkersconfirm', 'assignfeedback_wtpeer'));        
        
            $csvdata = $this->_customdata['csvdata'];
            $gradeimporter = $this->_customdata['gradeimporter'];
            $removemarkers = $this->_customdata['removemarkers'];
            $draftid = $this->_customdata['draftid'];
            $applytoall = $this->_customdata['applytoall'];

            if ($csvdata) {
                $gradeimporter->parsecsv($csvdata);
            }

            if (!$gradeimporter->init()) {
                $thisurl = new moodle_url('/mod/assign/view.php', array('action'=>'viewpluginpage',
                                                                        'pluginsubtype'=>'assignfeedback',
                                                                        'plugin'=>'wtpeer',
                                                                        'pluginaction'=>'importmarkers',
                                                                        'id'=>$assignment->get_course_module()->id));
                $struser = core_text::strtolower(get_string('user'));
                $strmarker = core_text::strtolower(get_string('marker', 'assign'));                                             
                $a = $struser. ' '. $strmarker;
                print_error('invalidimporter', 'assignfeedback_wtpeer', $thisurl, $a);
                return;
            }
            
            $skip = array();
            $valid = 0;
            while ($record = $gradeimporter->next()) {
                if(isset($record->skip)) {
                    $skip[] = $record->skip;
                } else {
                    $valid++;
                }
            }
            $gradeimporter->close(false);
            $mform->addElement('static', 'validmarkersassigns', get_string('validmarkersassigns', 'assignfeedback_wtpeer'), $valid);

            if($skip) {
                $skip = html_writer::alist($skip, array('class'=>'nonvalidmarkersassigns'));
                $mform->addElement('static', 'nonvalidmarkersassigns', get_string('nonvalidmarkersassigns', 'assignfeedback_wtpeer'), $skip);
            }
            
            $mform->addElement('hidden', 'importid', $gradeimporter->importid);
            $mform->setType('importid', PARAM_INT);

            $mform->addElement('hidden', 'encoding', $gradeimporter->get_encoding());
            $mform->setType('encoding', PARAM_ALPHANUMEXT);
            $mform->addElement('hidden', 'separator', $gradeimporter->get_separator());
            $mform->setType('separator', PARAM_ALPHA);
            $mform->addElement('hidden', 'draftid', $draftid);
            $mform->setType('draftid', PARAM_INT);
            $mform->addElement('hidden', 'removemarkers', $removemarkers);
            $mform->setType('removemarkers', PARAM_INT);
            $mform->addElement('hidden', 'applytoall', $applytoall);
            $mform->setType('applytoall', PARAM_INT);
        }
        
        
        $mform->addElement('hidden', 'confirmimport', 1);
        $mform->setType('confirmimport', PARAM_INT);

        $wtpeer->action = 'importallocsconfirm';
        $wtpeer->add_standard_form_items($mform);
        
        $this->add_action_buttons();
    }
    
}
