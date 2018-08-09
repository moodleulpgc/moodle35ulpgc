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
 * This file contains the forms to create and edit an instance of this module
 *
 * @package   mod_examboard
 * @copyright 2018 Enrique Castro ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


require_once($CFG->libdir.'/formslib.php');
require_once('HTML/QuickForm/input.php');
//require_once($CFG->dirroot . '/mod/examboard/locallib.php');

/**
 * Examboard user grading form
 *
 * @package   mod_examboard
 * @copyright 2018 Enrique Castro ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_examboard_grade_form extends moodleform {

    /** @var object the examboard record . */
    public $examboard;


    /**
     * Define the form - called by parent constructor.
     */
    public function definition() {
        $mform = $this->_form;

        /*
        list($examboardment, $data, $params) = $this->_customdata;
        // Visible elements.
        $this->examboardment = $examboardment;
        $examboardment->add_grade_form_elements($mform, $data, $params);

        if ($data) {
            $this->set_data($data);
        }
        */
        $userid = $this->_customdata['userid'];
        $gradingdisabled = $this->_customdata['gradingdisabled'];
        $gradinginstance = $this->_customdata['gradinginstance'];
        $examboard = $this->_customdata['examboard'];
        $this->examboard = $examboard;
        $currentgrade = $this->_customdata['currentgrade'];
        $gradeitem = examboard_get_grade_item($examboard->id, $examboard->course);
        $grade = isset($currentgrade->grade) ? format_float($currentgrade->grade, $gradeitem->get_decimals()) : '';
        
        $mform->addElement('header', 'gradeheader', get_string('grade'));
        if ($gradinginstance) {
            $gradingelement = $mform->addElement('grading',
                                                 'advancedgrading',
                                                 get_string('grade').':',
                                                 array('gradinginstance' => $gradinginstance));
            if ($gradingdisabled) {
                $gradingelement->freeze();
            } else {
                $mform->addElement('hidden', 'advancedgradinginstanceid', $gradinginstance->get_id());
                $mform->setType('advancedgradinginstanceid', PARAM_INT);
            }
        } else {
            // Use simple direct grading.
            if ($examboard->grade > 0) {
                $name = get_string('gradeoutof', 'examboard', $examboard->grade);
                if (!$gradingdisabled) {
                    $gradingelement = $mform->addElement('text', 'grade', $name);
                    $mform->addHelpButton('grade', 'gradeoutofhelp', 'assign');
                    $mform->setType('grade', PARAM_RAW);
                    $mform->setDefault('grade', $grade);
                } else {
                    $strgradelocked = get_string('gradelocked', 'assign');
                    $mform->addElement('static', 'gradedisabled', $name, $strgradelocked);
                    $mform->addHelpButton('gradedisabled', 'gradeoutofhelp', 'assign');
                }
            } else {
                $grademenu = array(-1 => get_string("nograde")) + make_grades_menu($examboard->grade);
                if (count($grademenu) > 1) {
                    $gradingelement = $mform->addElement('select', 'grade', get_string('grade') . ':', $grademenu);

                    // The grade is already formatted with format_float so it needs to be converted back to an integer.
                    if (!empty($data->grade)) {
                        $data->grade = (int)unformat_float($data->grade);
                    }
                    $mform->setType('grade', PARAM_INT);
                    if ($gradingdisabled) {
                        $gradingelement->freeze();
                    }
                }
            }
        }
        
        // Hidden params.
        $mform->addElement('hidden', 'id', $examboard->cmid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'item', optional_param('item', 0, PARAM_INT));
        $mform->setType('item', PARAM_INT);
        $mform->addElement('hidden', 'view', 'exam');
        $mform->setType('view', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'user', optional_param('user', 0, PARAM_INT));
        $mform->setType('user', PARAM_INT);
        $mform->addElement('hidden', 'action', 'submitgrade');
        $mform->setType('action', PARAM_ALPHANUM);

        // Add standard buttons.
        $this->add_action_buttons(true, get_string('grade', 'examboard'));
        
        // The grading form does not work well with shortforms.
        $mform->setDisableShortforms();
    }


    /**
     * Perform minimal validation on the grade form
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        $instance = $this->examboard;

        // Advanced grading.
        if (!array_key_exists('grade', $data)) {
            return $errors;
        }

        if ($instance->grade > 0) {
            if (unformat_float($data['grade'], true) === false && (!empty($data['grade']))) {
                $errors['grade'] = get_string('invalidfloatforgrade', 'examboard', $data['grade']);
            } else if (unformat_float($data['grade']) > $instance->grade) {
                $errors['grade'] = get_string('gradeabovemaximum', 'examboard', $instance->grade);
            } else if (unformat_float($data['grade']) < 0) {
                $errors['grade'] = get_string('gradebelowzero', 'examboard');
            }
        } else {
            // This is a scale.
            if ($scale = $DB->get_record('scale', array('id'=>-($instance->grade)))) {
                $scaleoptions = make_menu_from_list($scale->scale);
                if ((int)$data['grade'] !== -1 && !array_key_exists((int)$data['grade'], $scaleoptions)) {
                    $errors['grade'] = get_string('invalidgradeforscale', 'examboard');
                }
            }
        }
        return $errors;
    }
}
