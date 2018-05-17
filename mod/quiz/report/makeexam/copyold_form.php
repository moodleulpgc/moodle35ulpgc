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
 * Quiz makeexam copy old questions form definition.
 *
 * @package   quiz_makeexam
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * This is the copy old questions form for the quiz makeexam report.
 *
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_makeexam_copyold_form extends moodleform {
    protected function definition() {
        global $DB;

        $mform = $this->_form;

        $cmid = $this->_customdata['cmid'];
        $quiz = $this->_customdata['quiz'];

        $mform->addElement('header', 'copyoldquestions', get_string('copyoldquestions', 'quiz_makeexam'));

        $sql = "SELECT DISTINCT(codigo), codigo AS shortname
                FROM preguntas_prof
                WHERE 1
                ORDER BY codigo ASC";
        $oldcodes = $DB->get_records_sql_menu($sql, null);
        $oldcodes = array(''=>get_string('any')) +  $oldcodes;

        $mform->addElement('select', 'copysource', get_string('copysource', 'quiz_makeexam'), $oldcodes, '');
        $mform->addHelpButton('copysource', 'copysource', 'quiz_makeexam');

        $options = array('' => get_string('any'),
                         '=0' => ' = 0 ',
                         '>0' => ' > 0 ',
                         '<0' => ' < 0 ',
                         '!=0' => ' != 0 ',
                         '!=-1' => ' != -1 ',
                        );

        $mform->addElement('select', 'copystatus', get_string('copystatus', 'quiz_makeexam'), $options, '');
        $mform->addHelpButton('copystatus', 'copystatus', 'quiz_makeexam');

        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'mode', 'makeexam');
        $mform->setType('mode', PARAM_ALPHA);

        $mform->addElement('hidden', 'copyold', 'makeexam');
        $mform->setType('copyold', PARAM_ALPHA);

        $mform->addElement('hidden', 'confirm', 1);
        $mform->setType('confirm', PARAM_INT);

        $this->add_action_buttons(true, get_string('copyold', 'quiz_makeexam'));
    }
}
