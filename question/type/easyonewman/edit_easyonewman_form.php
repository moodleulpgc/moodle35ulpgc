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
 * Defines the editing form for the easyonewman question type.
 *
 * @package    qtype
 * @subpackage easyonewman
 * @copyright  2014 onwards Carl LeBlond
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot . '/question/type/shortanswer/edit_shortanswer_form.php');

class qtype_easyonewman_edit_form extends qtype_shortanswer_edit_form {

    protected function definition_inner($mform) {
        global $PAGE, $CFG, $question, $DB;

        //$PAGE->requires->js('/question/type/easyonewman/easyonewman_script.js');
        $PAGE->requires->css('/question/type/easyonewman/styles.css');

        if (isset($question->id)) {
            $record = $DB->get_record('question_easyonewman', array('question' => $question->id ));
            $stagoreclip = $record->stagoreclip;
        } else {
                $stagoreclip = 0;
        }
        $mform->addElement('static', 'answersinstruct',
                get_string('instructions', 'qtype_easyonewman'),
                get_string('filloutoneanswer', 'qtype_easyonewman'));
        $mform->closeHeaderBefore('answersinstruct');

        $menu = array(
            get_string('staggered', 'qtype_easyonewman'),
            get_string('eclipsed', 'qtype_easyonewman')
        );

        $mform->addElement('html', '<strong>'.get_string('stagoreclipmore', 'qtype_easyonewman').'</strong>');
        $mform->addElement('select', 'stagoreclip',
                get_string('casestagoreclip', 'qtype_easyonewman'), $menu);

        $menu = array(
            get_string('caseconformfalse', 'qtype_easyonewman'),
            get_string('caseconformtrue', 'qtype_easyonewman')
        );
        $mform->addElement('html', '<strong>'.get_string('conformimportantmore', 'qtype_easyonewman').'</strong>');
        $mform->addElement('select', 'conformimportant',
                get_string('caseconformimportant', 'qtype_easyonewman'), $menu);

        $menu = array(
            get_string('caseorientfalse', 'qtype_easyonewman'),
            get_string('caseorienttrue', 'qtype_easyonewman')
        );
        $mform->addElement('html', '<strong>'.get_string('orientimportantmore', 'qtype_easyonewman').'</strong>');
        $mform->addElement('select', 'orientimportant',
                get_string('caseorientimportant', 'qtype_easyonewman'), $menu);

        $mform->addElement('html', '<strong>'.get_string('newmaninstruct', 'qtype_easyonewman').'</strong>');

        $result = html_writer::start_tag('div', array('id' => 'newman_template'));
        if ($stagoreclip == 1) {
            $result .= html_writer::start_tag('div',
                    array('id' => 'divneweclip', 'style' => 'background-image: url(\'type/easyonewman/pix/eclip.png\');'));
            $result .= html_writer::div('', 'dropablediv', array('id' => 'epos0'));
            $result .= html_writer::div('', 'dropablediv flipable', array('id' => 'epos1'));
            $result .= html_writer::div('', 'dropablediv', array('id' => 'epos2'));
            $result .= html_writer::div('', 'dropablediv', array('id' => 'epos3'));
            $result .= html_writer::div('', 'dropablediv flipable', array('id' => 'epos4'));
            $result .= html_writer::div('', 'dropablediv flipable', array('id' => 'epos5'));
            $result .= html_writer::end_tag('div');  // End divnew!
        } else {
            $result .= html_writer::start_tag('div',
                array('id' => 'divnew', 'style' => 'background-image: url(\'type/easyonewman/pix/stag.png\');'));
            $result .= html_writer::div('', 'dropablediv flipable', array('id' => 'pos0'));
            $result .= html_writer::div('', 'dropablediv', array('id' => 'pos1'));
            $result .= html_writer::div('', 'dropablediv', array('id' => 'pos2'));
            $result .= html_writer::div('', 'dropablediv', array('id' => 'pos3'));
            $result .= html_writer::div('', 'dropablediv', array('id' => 'pos4'));
            $result .= html_writer::div('', 'dropablediv flipable', array('id' => 'pos5'));
            $result .= html_writer::end_tag('div');  // End divnew!
        }
        // Add the dragable div.
        
            $temp = file_get_contents('type/easyonewman/newman_dragable.html');
            $temp = str_replace("moodleroot", $CFG->wwwroot, $temp);
            $result .= $temp;
            $result .= html_writer::end_tag('div');  // End newman_template!
        // Add in the hidden inputs to hold answers.
            $result .= html_writer::empty_tag('input', array('id' => 'apos0', 'type' => 'hidden'));
            $result .= html_writer::empty_tag('input', array('id' => 'apos1', 'type' => 'hidden'));
            $result .= html_writer::empty_tag('input', array('id' => 'apos2', 'type' => 'hidden'));
            $result .= html_writer::empty_tag('input', array('id' => 'apos3', 'type' => 'hidden'));
            $result .= html_writer::empty_tag('input', array('id' => 'apos4', 'type' => 'hidden'));
            $result .= html_writer::empty_tag('input', array('id' => 'apos5', 'type' => 'hidden'));

            $easyonewmanbuildstring = $result;
            $mform->addElement('html', $easyonewmanbuildstring);
    /*        $jsmodule = array(
                            'name'     => 'qtype_easyonewman',
                            'fullpath' => '/question/type/easyonewman/easyonewman_script.js',
                            'requires' => array(),
                            'strings' => array(
                                array('enablejava', 'qtype_easyonewman')
                            )
                        ); */
            $htmlid = 1;
            $url = $CFG->wwwroot . '/question/type/easyonewman/template_update.php?stagoreclip=';
            $PAGE->requires->js_init_call('M.qtype_easyonewman.init_reload', array($url, $htmlid),
                                      true);

 /*           $jsmodule = array(
                            'name'     => 'qtype_easyonewman',
                            'fullpath' => '/question/type/easyonewman/easyonewman_script.js',
                            'requires' => array(),
                            'strings' => array(
                                array('enablejava', 'qtype_easyonewman')
                            )
                        ); */

            $PAGE->requires->js_init_call('M.qtype_easyonewman.insert_structure_into_applet',
                                      array($stagoreclip),
                                      true);

            $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_easyonewman', '{no}'),
                question_bank::fraction_options());
            $PAGE->requires->js_init_call('M.qtype_easyonewman.dragndrop', array('1'), true);
            $PAGE->requires->js_init_call('M.qtype_easyonewman.init_getanswerstring', array($stagoreclip));
            $this->add_interactive_settings();
    }

    protected function get_per_answer_fields($mform, $label, $gradeoptions,
            &$repeatedoptions, &$answersoption) {

        $repeated = parent::get_per_answer_fields($mform, $label, $gradeoptions,
                $repeatedoptions, $answersoption);
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
        return 'easyonewman';
    }
}
