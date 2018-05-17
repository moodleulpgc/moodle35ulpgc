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
 * The form to manage deletion of items in examboardmodule
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
class examboard_delete_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $cmid  = $this->_customdata['cmid'];
        $message= $this->_customdata['warning'];
        $action = $this->_customdata['confirmed'];
        $examid   = $this->_customdata['exam'];
        $userid   = $this->_customdata['user'];
        $additionals   = $this->_customdata['additionals'];

        $mform->addElement('static', 'warning', '', $message);
        
        if($additionals) {
            foreach($additionals as $field  => $name) {
                $mform->addElement('advcheckbox', $field, $name);
            }
        }
        
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'exam', $examid);
        $mform->setType('exam', PARAM_INT);
        
        if($userid) {
            $mform->addElement('hidden', 'user', $userid);
            $mform->setType('user', PARAM_INT);

            $mform->addElement('hidden', 'item', $examid);
            $mform->setType('item', PARAM_INT);

            $mform->addElement('hidden', 'view', 'exam');
            $mform->setType('view', PARAM_ALPHAEXT);
        }
        
        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_ALPHAEXT);
        
        $mform->addElement('hidden', 'confirmed', $action);
        $mform->setType('confirmed', PARAM_ALPHAEXT);


        // Add standard buttons.
        $this->add_action_buttons(true, get_string($action, 'examboard'));
    }
    
}
