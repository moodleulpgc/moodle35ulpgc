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
 * The form to manage confirmations of items by board members in examboard module
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Deleet an item form.
 *
 * @package    mod_examboard
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class examboard_board_confirmation_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $cmid  = $this->_customdata['cmid'];
        $exam   = $this->_customdata['exam'];
        $board   = $this->_customdata['board'];
        $userid   = $this->_customdata['user'];
        $canmanage   = $this->_customdata['canmanage'];
        
        $mform->addElement('static', 'boardname', get_string('board', 'examboard'), $board->title.' '.$board->idnumber);
        $mform->addElement('static', 'examperiod', get_string('examperiod', 'examboard'), $exam->examperiod);
        $mform->addElement('static', 'sessionname', get_string('session', 'examboard'), $exam->sessionname);
        $separator = '';
        if($exam->venue && $exam->examdate) {
            $separator = get_string('listsep', 'langconfig').' ';
        }
        $output = $exam->venue.$separator.userdate($exam->examdate);
        $mform->addElement('static', 'examplacedate', get_string('examplacedate', 'examboard'), $output);

        $mform->addElement('advcheckbox', 'confirmed', get_string('confirmation', 'examboard'), get_string('confirmation_help', 'examboard'));
        
        $discharges = array();
        foreach(explode(',',get_config('examboard', 'discharges')) as $motive) {
            $discharges[$motive] = get_string('discharge_'.$motive, 'examboard');
        }
        
        
        $mform->addElement('select', 'discharge', get_string('discharge', 'examboard'), $discharges);
        $mform->addHelpButton('discharge', 'discharge', 'examboard');
        $mform->disabledIf('discharge', 'confirmed', 'checked');

        $editoroptions = array('subdirs'=>0,
                                'maxbytes'=>0,
                                'maxfiles'=>0,
                                'changeformat'=>0,
                                'context'=>null,
                                'noclean'=>0,
                                'trusttext'=>0,
                                'enable_filemanagement' => false);
        $mform->addElement('editor', 'discharge_editor', get_string('dischargeexplain', 'examboard'), $editoroptions);
        $mform->setType('discharge_editor', PARAM_RAW);
        $mform->disabledIf('discharge_editor', 'confirmed', 'checked');
        $mform->addHelpButton('discharge_editor', 'dischargeexplain', 'examboard');
        
        $mform->addElement('selectyesno', 'available', get_string('confirmavailable', 'examboard'));
        $mform->setDefault('available', 1);
        $mform->disabledIf('available', 'confirmed', 'checked');
        $mform->addHelpButton('available', 'confirmavailable', 'examboard');

        
        if($canmanage) {
            $mform->addElement('advcheckbox', 'exemption', get_string('exemption', 'examboard'), get_string('exempted_help', 'examboard'));        
        } else {
            $mform->addElement('hidden', 'exemption', 0);
            $mform->setType('exemption', PARAM_INT);
        }
        
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'exam', $exam->id);
        $mform->setType('exam', PARAM_INT);

        $mform->addElement('hidden', 'user', $userid);
        $mform->setType('user', PARAM_INT);

        $mform->addElement('hidden', 'board', $board->id);
        $mform->setType('board', PARAM_INT);

        $mform->addElement('hidden', 'view', 'board');
        $mform->setType('view', PARAM_ALPHAEXT);

        $mform->addElement('hidden', 'action', 'boardconfirm');
        $mform->setType('action', PARAM_ALPHAEXT);
        
        // Add standard buttons.
        $this->add_action_buttons(true, get_string('toggleconfirm', 'examboard'));
    }
    
    /**
     * Form validation
     *
     * @param array $data data from the form.
     * @param array $files files uploaded.
     * @return array of errors.
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (($data['confirmed'] == 0) && $data['discharge_editor']['text'] == '') {
            $errors['discharge_editor'] = get_string('discharge_editor_error', 'forum');
        }
        return $errors;
    }
    
}
