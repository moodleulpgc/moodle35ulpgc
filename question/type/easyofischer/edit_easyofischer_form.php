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
 * Defines the editing form for the easyofischer question type.
 *
 * @package    qtype
 * @subpackage easyofischer
 * @copyright  2014 onwards Carl LeBlond
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/shortanswer/edit_shortanswer_form.php');
class qtype_easyofischer_edit_form extends qtype_shortanswer_edit_form {
    protected function definition_inner($mform) {
        global $PAGE, $CFG, $question, $DB, $numofstereo;
        $PAGE->requires->css('/question/type/easyofischer/styles.css');
        if (isset($question->id)) {
            $record      = $DB->get_record('question_easyofischer', array(
                'question' => $question->id
            ));
            $numofstereo = $record->numofstereo;
        } else {
            $numofstereo = 1;
        }
        $mform->addElement('static', 'answersinstruct',
            get_string('correctanswers', 'qtype_easyofischer'), get_string('filloutoneanswer', 'qtype_easyofischer'));
        $mform->closeHeaderBefore('answersinstruct');
        $menu = array(
            '0' => 'False',
            '1' => 'True'
        );
        $mform->addElement('html', '<strong>'.get_string('rotationmore', 'qtype_easyofischer').'</strong>');
        $mform->addElement('select', 'strictfischer', get_string('rotationallowed', 'qtype_easyofischer'), $menu);
        $menu = array(
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4'
        );

        $mform->addElement('html', '<strong>'.get_string('numofstereomore', 'qtype_easyofischer').'</strong>');
        $mform->addElement('select', 'numofstereo', get_string('numofstereo', 'qtype_easyofischer'), $menu);
        $mform->addElement('html', '<strong>'.get_string('fischerinstruct', 'qtype_easyofischer').'</strong>');
        $easyofischerbuildstring = file_get_contents('type/easyofischer/edit_fischer' . $numofstereo . '.html');
        $temp                    = file_get_contents('type/easyofischer/fischer_dragable.html');
        $temp = str_replace("moodleroot", $CFG->wwwroot, $temp);
        $easyofischerbuildstring = $easyofischerbuildstring . $temp;
        $mform->addElement('html', $easyofischerbuildstring);
        $htmlid   = 1;
        $url      = $CFG->wwwroot . '/question/type/easyofischer/template_update.php?numofstereo=';
        $PAGE->requires->js_init_call('M.qtype_easyofischer.init_reload', array(
            $url,
            $htmlid
        ), true);
        $PAGE->requires->js_init_call('M.qtype_easyofischer.insert_structure_into_applet', array(
            $numofstereo
        ), true);

        $PAGE->requires->js_init_call('M.qtype_easyofischer.dragndrop', array('1'), true);
        $PAGE->requires->js_init_call('M.qtype_easyofischer.init_getanswerstring', array($numofstereo));
        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_easyofischer', '{no}'),
            question_bank::fraction_options());
        $this->add_interactive_settings();
    }
    protected function get_per_answer_fields($mform, $label, $gradeoptions, &$repeatedoptions, &$answersoption) {
        global $numofstereo;
        $repeated      = parent::get_per_answer_fields($mform, $label, $gradeoptions, $repeatedoptions, $answersoption);
        $scriptattrs = 'class = id_insert';
        $insertbutton = $mform->createElement('button', 'insert', get_string('insertfromeditor',
        'qtype_easyonewman'), $scriptattrs);
        array_splice($repeated, 2, 0, array($insertbutton));
        return $repeated;
    }
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        return $question;
    }
    public function qtype() {
        return 'easyofischer';
    }
}
