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
 * The form to manage user notifications to exam board participants
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add an exam form.
 *
 * @package    mod_examboard
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class examboard_notify_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $cmid  = $this->_customdata['cmid'];
        $user  = $this->_customdata['user'];
        $exams  = $this->_customdata['exams'];

        $size = min(12, count($exams));
        
        $select = $mform->addElement('select', 'exams', get_string('notifiedexams', 'examboard'), 
                                        $exams, array('size' => $size ));
        $select->setMultiple(true);
        $mform->addHelpButton('exams', 'notifiedexams', 'examboard');
        $mform->addRule('exams', null, 'required', null, 'client');

        $rawoptions = array(EXAMBOARD_USERTYPE_USER    => get_string('userexaminees', 'examboard'),
                        EXAMBOARD_USERTYPE_MEMBER   => get_string('usermembers', 'examboard'),
                        EXAMBOARD_USERTYPE_TUTOR    => get_string('usertutors', 'examboard'),
                        EXAMBOARD_USERTYPE_STAFF    => get_string('userstaff', 'examboard'),
                        EXAMBOARD_USERTYPE_ALL      => get_string('userall', 'examboard'),
                        );
        if(!is_int($user)) {
            $options[$user->id] = fullname($user);
        } elseif($user < 0) {
            $options[$user] = $rawoptions[$user];
        } else {
            $options = $rawoptions;
        }
                        
        $mform->addElement('select', 'usertype', get_string('notifiedusers', 'examboard'), $options);
        $mform->setDefault('usertype', EXAMBOARD_USERTYPE_MEMBER);
        $mform->addHelpButton('usertype', 'notifiedusers', 'examboard');
        
        $mform->addElement('selectyesno', 'includedeputy', get_string('includedeputy', 'examboard'));
        $mform->setDefault('includedeputy', 0);
        $mform->addHelpButton('includedeputy', 'includedeputy', 'examboard');
        $mform->disabledIf('includedeputy', 'usertype', 'eq', EXAMBOARD_USERTYPE_USER);
        
        $mform->addElement('selectyesno', 'includepdf', get_string('includepdf', 'examboard'));
        $mform->setDefault('includepdf', 1);
        $mform->addHelpButton('includepdf', 'includepdf', 'examboard');
        
        $mform->addElement('text', 'attachname', get_string('attachname', 'examboard'), array('size'=>'50'));
        $mform->setDefault('attachname', get_string('attachment', 'examboard'));
        $mform->setType('attachname', PARAM_FILE); 
        $mform->disabledIf('attachname', 'includepdf', 'eq', 0);       
        $mform->addHelpButton('attachname', 'attachname', 'examboard');
        
        $mform->addElement('text', 'messagesubject', get_string('messagesubject', 'examboard'), array('size'=>'64'));
        $mform->setDefault('messagesubject', get_string('defaultsubject', 'examboard'));
        $mform->setType('messagesubject', PARAM_TEXT); 
        $mform->addHelpButton('messagesubject', 'messagesubject', 'examboard');
        $mform->addRule('messagesubject', null, 'required', null, 'client');

        $editoroptions = array('subdirs'=>0,
                                'maxbytes'=>0,
                                'maxfiles'=>0,
                                'changeformat'=>0,
                                'context'=>null,
                                'noclean'=>0,
                                'trusttext'=>0,
                                'enable_filemanagement' => false);
        $mform->addElement('editor', 'messagebody', get_string('messagebody', 'examboard'), $editoroptions);
        $mform->setType('messagebody', PARAM_RAW);
        $mform->setDefault('messagebody', get_string('defaultbody', 'examboard'));
        $mform->addHelpButton('messagebody', 'messagebody', 'examboard');
        $mform->addRule('messagebody', null, 'required', null, 'client');
        $mform->addElement('static', 'messagebodyhelp', '', get_string('defaultbody', 'examboard'));
        
        $mform->addElement('filepicker', 'logofile', get_string('logofile', 'examboard'), null, array('maxbytes' => $CFG->maxbytes, 'accepted_types' => 'image'));
        $mform->addHelpButton('logofile', 'logofile', 'examboard');

        $mform->addElement('text', 'logowidth', get_string('logowidth', 'examboard'), array('size'=>'4'));
        $mform->setDefault('logowidth', '20');
        $mform->setType('logowidth', PARAM_FLOAT); 
        $mform->addRule('logowidth', null, 'numeric', null, 'client');
        $mform->addHelpButton('logowidth', 'logowidth', 'examboard');
        
        $mform->addElement('text', 'messagesender', get_string('messagesender', 'examboard'), array('size'=>'64'));
        $mform->setDefault('messagesender', '');
        $mform->setType('messagesender', PARAM_TEXT); 
        $mform->addHelpButton('messagesender', 'messagesender', 'examboard');

        $mform->addElement('filepicker', 'signaturefile', get_string('signaturefile', 'examboard'), null, array('maxbytes' => $CFG->maxbytes, 'accepted_types' => 'image'));
        $mform->addHelpButton('signaturefile', 'signaturefile', 'examboard');

        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'action', 'notify');
        $mform->setType('action', PARAM_ALPHAEXT);

        // Add standard buttons.
        $this->add_action_buttons(true, get_string('notify', 'examboard'));
    }
    
}
