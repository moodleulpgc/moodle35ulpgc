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
 * Defines the editing form for the variable numeric with units question type.
 *
 * @package    qtype_varnumunit
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/varnumericset/edit_varnumericset_form_base.php');
require_once($CFG->dirroot.'/question/type/pmatch/pmatchlib.php');
require_once($CFG->dirroot . '/question/type/varnumunit/questiontype.php');

/**
 * The variable numeric with units question editing form definition.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_varnumunit_edit_form extends qtype_varnumeric_edit_form_base {

    public function qtype() {
        return 'varnumunit';
    }

    /**
     * Get list of form elements to repeat for each 'units' block.
     * @param object $mform the form being built.
     * @param $label the label to use for the header.
     * @param $gradeoptions the possible grades for each answer.
     * @param $repeatedoptions reference to array of repeated options to fill
     * @param $unitoption reference to return the name of $question->options
     *                       field holding an array of units
     * @return array of form fields.
     */
    protected function get_per_unit_fields($mform, $label, $gradeoptions) {
        $repeated = array();
        $repeated[] = $mform->createElement('textarea', 'units', $label,
            array('rows' => '2', 'cols' => '60', 'class' => 'textareamonospace'));

        $spaceinunitoptions = qtype_varnumunit::spaceinunit_options();
        $repeated[] = $mform->createElement('select', 'spaceinunit',
            get_string('spaceinunit', 'qtype_varnumunit'), $spaceinunitoptions);
        $repeated[] = $mform->createElement('editor', 'spacesfeedback',
            get_string('spacingfeedback', 'qtype_varnumunit'),
            ['rows' => 5], $this->editoroptions);
        $repeated[] = $mform->createElement('selectyesno', 'replacedash', get_string('replacedash', 'qtype_varnumunit'));
        $repeated[] = $mform->createElement('select', 'unitsfraction',
            get_string('grade'), $gradeoptions);
        $repeated[] = $mform->createElement('editor', 'unitsfeedback',
            get_string('feedback', 'question'),
            array('rows' => 5), $this->editoroptions);
        return $repeated;
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        return $this->data_preprocessing_units($question);
    }

    /**
     * Perform the necessary preprocessing for the unit fields.
     * @param object $question the data being passed to the form.
     * @return object $question the modified data.
     */
    protected function data_preprocessing_units($question) {
        if (empty($question->options)) {
            return $question;
        }

        $question->units = array();
        $question->unitsfraction = array();
        $question->spaceinunit = array();
        $question->spacesfeedback = [];
        $question->replacedash = array();

        $key = 0;
        foreach ($question->options->units as $unitid => $unit) {
            if ($unit->unit != '*') {
                $question->units[$key] = $unit->unit;
                $question->spaceinunit[$key] = $unit->spaceinunit;
                $question->replacedash[$key] = $unit->replacedash;
                $question->unitsfraction[$key] = 0 + $unit->fraction;

                $question->unitsfeedback[$key] = $this->unit_feedback_html_element_preprocess('unitsfeedback['.$key.']',
                                                                                $unitid,
                                                                                $unit->feedback,
                                                                                $unit->feedbackformat,
                                                                                'unitsfeedback');

                $question->spacesfeedback[$key] = $this->unit_feedback_html_element_preprocess('spacesfeedback['.$key.']',
                                                                                $unitid,
                                                                                $unit->spacingfeedback,
                                                                                $unit->spacingfeedbackformat,
                                                                                'spacesfeedback');
                // Unset default value spacesfeedback incase update question.
                unset($this->_form->_defaultValues["spacesfeedback[$key]"]);
                $key++;
            } else {
                $question->otherunitfeedback = $this->unit_feedback_html_element_preprocess('otherunitfeedback',
                                                                                $unitid,
                                                                                $unit->feedback,
                                                                                $unit->feedbackformat,
                                                                                'unitsfeedback');

            }
        }

        return $question;
    }

    protected function unit_feedback_html_element_preprocess($draftitemidkey, $unitid, $feedback, $feedbackformat, $filearea) {
        // Feedback field and attached files.
        $formelementdata = array();
        $draftitemid = file_get_submitted_draft_itemid($draftitemidkey);
        $formelementdata['text'] = file_prepare_draft_area(
            $draftitemid,
            $this->context->id,
            $this->db_table_prefix(),
            $filearea,
            (!empty($unitid) ? (int) $unitid : null),
            $this->fileoptions,
            $feedback
        );
        $formelementdata['itemid'] = $draftitemid;
        $formelementdata['format'] = $feedbackformat;
        return $formelementdata;

    }

    /**
     * Add a set of form fields, obtained from get_per_answer_fields, to the form,
     * one for each existing answer, with some blanks for some new ones.
     * @param object $mform the form being built.
     * @param $label the label to use for each option.
     * @param $gradeoptions the possible grades for each answer.
     * @param int|\the $minoptions the minimum number of answer blanks to display.
     *      Default QUESTION_NUMANS_START.
     * @param int|\the $addoptions the number of answer blanks to add. Default QUESTION_NUMANS_ADD.
     * @return void
     */
    protected function add_per_unit_fields(&$mform, $label, $gradeoptions,
                                             $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
        global $DB;
        $repeated = $this->get_per_unit_fields($mform, $label, $gradeoptions);

        if (isset($this->question->options)) {
            $countanswers = count($this->question->options->units);
            foreach ($this->question->options->units as $unit) {
                if ($unit->unit == '*') {
                    $countanswers--;
                    break;
                }
            }
        } else {
            $countanswers = 0;
        }
        if ($countanswers) {
            $repeatsatstart = $countanswers;
        } else {
            $repeatsatstart = $minoptions;
        }

        $repeatedoptions = array();
        $repeatedoptions['units']['type'] = PARAM_RAW_TRIMMED;
        $repeatedoptions["units"]['helpbutton'] = array('units', 'qtype_varnumunit');
        $repeatedoptions["spacesfeedback"]['helpbutton'] = ['spacingfeedback', 'qtype_varnumunit'];
        // Wating for #257559 Mform disableif does not work on editor element [MDL-29701]. Once this merged, this should work.
        $repeatedoptions["spacesfeedback"]['disabledif'] = ['spaceinunit', 'neq',
                qtype_varnumunit::SPACEINUNIT_PRESERVE_SPACE_REQUIRE];
        $repeatedoptions["spacesfeedback"]['default'] = [
                'text' => get_string('spacingfeedback_default', 'qtype_varnumunit')
        ];
        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions,
            'noanswers', 'addunits', $addoptions,
            get_string('addmoreunits', 'qtype_varnumunit'), true);
    }

    /**
     * Add answer options for any other (wrong) answer.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    protected function add_other_unit_fields($mform) {
         /*
         * Adding a field element so we can style other unit grade properly. Not what we want.
         * Couldn't find a way to identify the other unit gradefield using css.
         *
         * Need an element with an id to work with. Hidden fields have no id and are inserted at
         * the start of the form.
         */
        $mform->addElement('textarea', 'otherunitlabel',
            get_string('anyotherunit', 'qtype_varnumunit'));
        $mform->addElement('static', 'otherunitfraction', get_string('grade'), '0%');
        $mform->addElement('editor', 'otherunitfeedback', get_string('feedback', 'question'),
            array('rows' => 5), $this->editoroptions);
    }

    protected function add_answer_form_part($mform) {
        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_varnumericset', '{no}'),
                                                                    question_bank::fraction_options(), 2, 1);
        $mform->addElement('header', 'unithdr', get_string('units', 'qtype_varnumunit'));
        $this->add_per_unit_fields($mform, get_string('unitno', 'qtype_varnumunit', '{no}'),
                                                                    question_bank::fraction_options(), 2, 1);
        $this->add_other_unit_fields($mform);
    }

    protected function definition() {
        parent::definition();
        $mform = $this->_form;
        question_bank::fraction_options();
        $mform->insertElementBefore(
            $mform->createElement('select',
                                    'unitfraction',
                                    get_string('unitweighting', 'qtype_varnumunit'),
                                    $this->grade_weighting()),
            'generalfeedback'
        );
        $mform->setDefault('unitfraction', '0.1000000');

        $mform->removeElement('requirescinotation');

        $requirescinotationoptions = array(
           qtype_varnumunit::SUPERSCRIPT_SCINOTATION_REQUIRED => get_string('superscriptscinotationrequired',  'qtype_varnumunit'),
           qtype_varnumunit::SUPERSCRIPT_ALLOWED => get_string('superscriptallowed', 'qtype_varnumunit'),
           qtype_varnumunit::SUPERSCRIPT_NONE => get_string('superscriptnone', 'qtype_varnumunit')
        );

        $requirescinotationel = $mform->createElement('select',
                                                        'requirescinotation',
                                                        get_string('superscripts', 'qtype_varnumunit'),
                                                        $requirescinotationoptions);

        $mform->insertElementBefore($requirescinotationel, 'answersinstruct');
    }

    protected function definition_inner($mform) {
        parent::definition_inner($mform);

        // Add a button to add more form fields for variants.
        $mform->registerNoSubmitButton('addvariants');
        $addvariantel = $mform->createElement('submit', 'addvariants',
                                        get_string('addmorevariants', 'qtype_varnumericset', 2));
        $mform->insertElementBefore($addvariantel, 'vartype[1]');
    }

    protected static function grade_weighting() {
        // Define basic array of grades. This list comprises all fractions of the form:
        // a. p/q for q <= 6, 0 <= p <= q
        // b. p/10 for 0 <= p <= 10
        // c. 1/q for 1 <= q <= 10
        // d. 1/20.
        $rawfractions = array(
            1.0000000,
            0.9000000,
            0.8333333,
            0.8000000,
            0.7500000,
            0.7000000,
            0.6666667,
            0.6000000,
            0.5000000,
            0.4000000,
            0.3333333,
            0.3000000,
            0.2500000,
            0.2000000,
            0.1666667,
            0.1428571,
            0.1250000,
            0.1111111,
            0.1000000,
            0.0500000,

        );

        $fractionoptions = array();

        foreach ($rawfractions as $fraction) {
            $a = new stdClass();
            $unitfraction = (1 - $fraction);
            $a->unit = (100 * $unitfraction) . '%';
            $a->num = (100 * $fraction). '%';
            $fractionoptions["$unitfraction"] = get_string('percentgradefornumandunit', 'qtype_varnumunit', $a);
        }
        return $fractionoptions;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $units = $data['units'];
        $unitcount = 0;
        $maxgrade = false;
        $trimmedunits = array();
        foreach ($units as $key => $unit) {
            $trimmedunit = trim($unit);
            if ($trimmedunit !== '') {
                $expression = new pmatch_expression($trimmedunit);
                $existingunitmatchkey = array_search($trimmedunit, $trimmedunits);
                if (!$expression->is_valid()) {
                    $errors["units[$key]"] = $expression->get_parse_error();
                } else if (false !== $existingunitmatchkey) {
                    $errors["units[$existingunitmatchkey]"] = get_string('unitduplicate', 'qtype_varnumunit');
                    $errors["units[$key]"] = get_string('unitduplicate', 'qtype_varnumunit');
                } else {
                    $trimmedunits[$key] = $trimmedunit;
                }
                $unitcount++;
                if ($data['unitsfraction'][$key] == 1) {
                    $maxgrade = true;
                }
            } else if ($data['unitsfraction'][$key] != 0 ||
                !html_is_blank($data['unitsfeedback'][$key]['text'])) {
                $errors["units[$key]"] = get_string('unitmustbegiven', 'qtype_varnumunit');
                $unitcount++;
            }
            if ($data['spaceinunit'][$key] == qtype_varnumunit::SPACEINUNIT_PRESERVE_SPACE_REQUIRE &&
                html_is_blank($data['spacesfeedback'][$key]['text'])) {
                $errors["spacesfeedback[$key]"] = get_string('spacesfeedbackmustbegiven', 'qtype_varnumunit');;
                $unitcount++;
            }
        }
        if ($unitcount === 0) {
            $errors['units[0]'] = get_string('notenoughunits', 'qtype_varnumunit');
        }
        if ($maxgrade === false) {
            $errors['unitsfraction[0]'] = get_string('unitsfractionsnomax', 'qtype_varnumunit');
        }

        return $errors;
    }
}
