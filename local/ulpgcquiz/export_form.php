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
 * Settings form for ODF/PDF export in the quiz module.
 *
 * @package    mod_quiz
 * @subpackage export
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');


/**
 * Form for quiz ODF/PDF export.
 *
 * @copyright  2013 Enrique Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_export_form extends moodleform {

    protected function definition() {
        global $CFG, $DB;

        $mform = $this->_form;
        $cmid = $this->_customdata['cmid'];
        $aid = $this->_customdata['attempt'];

        $mform->addElement('header', 'export', get_string('exportoptions', 'local_ulpgcquiz'));

        $url = new moodle_url('/mod/quiz/review.php', array('attempt'=>$aid));
        $name = html_writer::link($url, get_string('preview', 'quiz'));
        $mform->addElement('static', 'attempt', get_string('attempt', 'quiz', ''), $name);

        $options = array('html'=>get_string('exporthtml', 'local_ulpgcquiz'),
                            'pdf'=>get_string('exportpdf', 'local_ulpgcquiz'),
                            //'docx'=>get_string('exportdocx', 'quiz'),
                            'doc'=>get_string('exportdoc', 'local_ulpgcquiz'),
                            'odt'=>get_string('exportodt', 'local_ulpgcquiz'));
        $mform->addElement('select', 'exporttype', get_string('exporttype', 'local_ulpgcquiz'), $options);
        $mform->setType('exporttype', PARAM_TEXT);
        $mform->setDefault('exporttype', 'pdf');
        $mform->addHelpButton('exporttype', 'exporttype', 'local_ulpgcquiz');

        $columns = array(1=>1, 2=>2, 3=>3, 4=>4);
        $mform->addElement('select', 'exportcolumns', get_string('exportcolumns', 'local_ulpgcquiz'), $columns);
        $mform->setType('exportcolumns', PARAM_INT);
        $mform->setDefault('exportcolumns', '2');
        $mform->disabledIf('exportcolumns', 'exporttype', 'neq', 'pdf');
        $mform->addHelpButton('exportcolumns', 'exportcolumns', 'local_ulpgcquiz');

        $mform->addElement('text', 'examname', get_string('examname', 'local_ulpgcquiz'), 'size="40"');
        $mform->setType('examname', PARAM_TEXT);
        $mform->addHelpButton('examname', 'examname', 'local_ulpgcquiz');

        $mform->addElement('date_selector', 'examdate', get_string('examdate', 'local_ulpgcquiz'));

        $mform->addElement('text', 'examdegree', get_string('examdegree', 'local_ulpgcquiz'));
        $mform->setType('examdegree', PARAM_TEXT);
        $mform->addHelpButton('examdegree', 'examdegree', 'local_ulpgcquiz');

        $mform->addElement('text', 'examissue', get_string('examissue', 'local_ulpgcquiz'), 'size="3"');
        $mform->setType('examissue', PARAM_TEXT);
        $mform->addHelpButton('examissue', 'examissue', 'local_ulpgcquiz');

        $mform->addElement('selectyesno', 'examgrid', get_string('examgrid', 'local_ulpgcquiz'));
        $mform->setType('examgrid', PARAM_INT);
        $mform->addHelpButton('examgrid', 'examgrid', 'local_ulpgcquiz');

        $rows = array(2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7, 8=>8, 9=>9, 10=>10);
        $mform->addElement('select', 'examgridrows', get_string('examgridrows', 'local_ulpgcquiz'), $rows);
        $mform->setType('examgridrows', PARAM_INT);
        $mform->setDefault('examgridrows', '5');
        $mform->disabledIf('examgridrows', 'examgrid', 'eq', 0);
        $mform->addHelpButton('examgridrows', 'examgridrows', 'local_ulpgcquiz');

        $mform->addElement('selectyesno', 'examanswers', get_string('examanswers', 'local_ulpgcquiz'));
        $mform->setType('examanswers', PARAM_INT);
        $mform->addHelpButton('examanswers', 'examanswers', 'local_ulpgcquiz');

        $mform->addElement('hidden', 'cmid', $cmid);
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden', 'aid', $aid);
        $mform->setType('aid', PARAM_INT);


        $mform->addElement('hidden', 'action', 1);
        $mform->setType('action', PARAM_INT);

        $this->add_action_buttons(true, get_string('exportquiz', 'local_ulpgcquiz'));

    }
}
