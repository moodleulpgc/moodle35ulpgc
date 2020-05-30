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
 * Question type class for the short answer question type.
 *
 * @package    qtype_varnumunit
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/varnumunit/calculator.php');
require_once($CFG->dirroot . '/question/type/varnumericset/questiontypebase.php');


/**
 * The variable numeric with units question type.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_varnumunit extends qtype_varnumeric_base {

    const SUPERSCRIPT_SCINOTATION_REQUIRED = 2;
    const SUPERSCRIPT_ALLOWED = 1;
    const SUPERSCRIPT_NONE = 0;

    const SPACEINUNIT_REMOVE_ALL_SPACE = 1;
    const SPACEINUNIT_PRESERVE_SPACE_NOT_REQUIRE = 0;
    const SPACEINUNIT_PRESERVE_SPACE_REQUIRE = 2;

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->requirescinotation = ($questiondata->options->requirescinotation == self::SUPERSCRIPT_SCINOTATION_REQUIRED);
        $question->usesupeditor = $questiondata->options->requirescinotation == self::SUPERSCRIPT_SCINOTATION_REQUIRED ||
                        $questiondata->options->requirescinotation == self::SUPERSCRIPT_ALLOWED;
    }

    public function recalculate_every_time() {
        return false;
    }

    public function db_table_prefix() {
        return 'qtype_varnumunit';
    }

    public function extra_question_fields() {
        return array($this->db_table_prefix(), 'randomseed', 'requirescinotation', 'unitfraction');
    }

    protected function delete_files_in_units($questionid, $contextid) {
        global $DB;
        $fs = get_file_storage();

        $tablename = $this->db_table_prefix().'_units';
        $unitids = $DB->get_records_menu($tablename, array('questionid' => $questionid), 'id', 'id,1');
        foreach ($unitids as $unitid => $notused) {
            $fs->delete_area_files($contextid, $this->db_table_prefix(), 'unitsfeedback', $unitid);
        }
    }

    protected function move_files_in_units($questionid, $oldcontextid, $newcontextid) {
        global $DB;
        $fs = get_file_storage();
        $tablename = $this->db_table_prefix().'_units';
        $unitids = $DB->get_records_menu($tablename, array('questionid' => $questionid), 'id', 'id,1');
        foreach ($unitids as $unitid => $notused) {
            $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, $this->db_table_prefix(), 'unitsfeedback', $unitid);
        }
    }

    public function delete_question($questionid, $contextid) {
        global $DB;
        $tablename = $this->db_table_prefix().'_units';
        $DB->delete_records($tablename, array('questionid' => $questionid));
        parent::delete_question($questionid, $contextid);
    }

    public function save_units($formdata) {
        global $DB;
        $context = $formdata->context;
        $table = $this->db_table_prefix().'_units';
        $oldunits = $DB->get_records($table, array('questionid' => $formdata->id), 'id ASC');
        if (empty($oldunits)) {
            $oldunits = array();
        }

        if (!empty($formdata->units)) {
            $numunits = max(array_keys($formdata->units)) + 1;
        } else {
            $numunits = 0;
        }

        for ($i = 0; $i < $numunits; $i += 1) {
            if (empty($formdata->units[$i])) {
                continue;
            }
            if (html_is_blank($formdata->unitsfeedback[$i]['text'])) {
                $formdata->unitsfeedback[$i]['text'] = '';
            }
            if (html_is_blank($formdata->spacesfeedback[$i]['text'])) {
                $formdata->spacesfeedback[$i]['text'] = '';
            }
            $this->save_unit($table,
                            $context,
                            $formdata->id,
                            $oldunits,
                            $formdata->units[$i],
                            $formdata->unitsfeedback[$i],
                            $formdata->unitsfraction[$i],
                            $formdata->spaceinunit[$i],
                            $formdata->spacesfeedback[$i],
                            !empty($formdata->replacedash[$i]));

        }

        if (!html_is_blank($formdata->otherunitfeedback['text'])) {
            $this->save_unit($table,
                            $context,
                            $formdata->id,
                            $oldunits,
                            '*',
                            $formdata->otherunitfeedback,
                            0,
                            false,
                            ['text' => '', 'format' => FORMAT_HTML],
                            false);
        }
        // Delete any remaining old units.
        $fs = get_file_storage();
        foreach ($oldunits as $oldunit) {
            $fs->delete_area_files($context->id, $this->db_table_prefix(), 'unitsfeedback', $oldunit->id);
            $fs->delete_area_files($context->id, $this->db_table_prefix(), 'spacesfeedback', $oldunit->id);
            $DB->delete_records($table, array('id' => $oldunit->id));
        }
    }

    public function save_unit($table, $context, $questionid, &$oldunits, $unit, $feedback, $fraction, $spaceinunit,
                              $spacingfeedback, $replacedash) {
        global $DB;
        // Update an existing unit if possible.
        $oldunit = array_shift($oldunits);
        if ($oldunit === null) {
            $unitobj = new stdClass();
            $unitobj->questionid = $questionid;
            $unitobj->unit = '';
            $unitobj->feedback = '';
            $unitobj->spacingfeedback = '';
            $unitobj->id = $DB->insert_record($table, $unitobj);
        } else {
            $unitobj = new stdClass();
            $unitobj->questionid = $questionid;
            $unitobj->unit = '';
            $unitobj->feedback = '';
            $unitobj->spacingfeedback = '';
            $unitobj->id = $oldunit->id;
        }

        $unitobj->unit = $unit;
        $unitobj->spaceinunit = $spaceinunit;
        $unitobj->replacedash = $replacedash;
        $unitobj->fraction = $fraction;
        $unitobj->feedback =
                        $this->import_or_save_files($feedback, $context, $this->db_table_prefix(), 'unitsfeedback', $unitobj->id);
        $unitobj->feedbackformat = $feedback['format'];
        $unitobj->spacingfeedback = $this->import_or_save_files($spacingfeedback, $context, $this->db_table_prefix(),
                'spacesfeedback', $unitobj->id);
        $unitobj->spacingfeedbackformat = $spacingfeedback['format'];

        $DB->update_record($table, $unitobj);
    }

    public function save_question_options($form) {
        $parentresult = parent::save_question_options($form);
        if ($parentresult !== null) {
            // Parent function returns null if all is OK.
            return $parentresult;
        }
        $this->save_units($form);
        return null;
    }

    public function get_question_options($question) {
        parent::get_question_options($question);
        $this->load_units($question);
    }

    public function load_units($question) {
        global $DB;
        $units = $DB->get_records($this->db_table_prefix().'_units', array('questionid' => $question->id), 'id ASC');
        if ($units) {
            foreach ($units as $unitid => $unit) {
                $question->options->units[$unitid] = new qtype_varnumunit_unit(
                    $unit->id,
                    $unit->unit,
                    $unit->spaceinunit,
                    $unit->spacingfeedback,
                    $unit->spacingfeedbackformat,
                    $unit->replacedash,
                    $unit->fraction,
                    $unit->feedback,
                    $unit->feedbackformat);
            }

        } else {
            $question->options->units = array();
        }

    }

    public function get_possible_responses($questiondata) {
        $parentresponses = parent::get_possible_responses($questiondata);
        $numericresponses = $parentresponses[$questiondata->id];

        $matchall = false;
        $unitresponses = array();
        foreach ($questiondata->options->units as $unitid => $unit) {
            if ('*' === $unit->unit) {
                $matchall = true;
            }
            $unitresponses[$unit->unit] = new question_possible_response($unit->unit, $unit->fraction);
        }
        if (!$matchall) {
            $unitresponses[0] = new question_possible_response($unit->unit, $unit->fraction);
        }
        $unitresponses[null] = question_possible_response::no_response();

        return array("unitpart" => $unitresponses,
                     "numericpart" => $numericresponses);
    }

    public function get_random_guess_score($questiondata) {
        foreach ($questiondata->options->answers as $aid => $answer) {
            if ('*' == trim($answer->answer)) {
                return (1 - $questiondata->options->unitfraction) * $answer->fraction;
            }
        }
        return 0;
    }

    // IMPORT/EXPORT FUNCTIONS.

    /*
     * Imports question from the Moodle XML format
     *
     * Imports question using information from extra_question_fields function
     * If some of you fields contains id's you'll need to reimplement this
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {
        $qo = parent::import_from_xml($data, $question, $format, $extra);
        if (!$qo) {
            return false;
        }

        if (isset($data['#']['unit'])) {
            $units = $data['#']['unit'];
            $unitno = 0;
            foreach ($units as $unit) {
                $unitname = $format->getpath($unit, array('#', 'units', 0, '#'), '', true);
                if ('*' !== $unitname) {
                    $qo->units[$unitno] = $unitname;
                    $qo->unitsfeedback[$unitno] = $this->import_html($format, $unit['#']['unitsfeedback'][0],
                        $qo->questiontextformat);
                    // Check for removespace if import from version 2014111200.
                    $removespace = $format->getpath($unit, array('#', 'removespace', 0, '#'), false);
                    if ($removespace !== false) {
                        $qo->spaceinunit[$unitno] = $removespace;
                        $qo->spacesfeedback[$unitno] = $this->import_html($format, '',
                                FORMAT_HTML);
                    } else {
                        $qo->spaceinunit[$unitno] = $format->getpath($unit, array('#', 'spaceinunit', 0, '#'), false);
                        $qo->spacesfeedback[$unitno] = $this->import_html($format, $unit['#']['spacesfeedback'][0],
                                $qo->questiontextformat);
                    }
                    $qo->replacedash[$unitno] = $format->getpath($unit, array('#', 'replacedash', 0, '#'), false);
                    $qo->unitsfraction[$unitno] = $format->getpath($unit, array('#', 'unitsfraction', 0, '#'), 0, true);
                    $unitno++;
                } else {
                    $qo->otherunitfeedback = $this->import_html($format, $unit['#']['unitsfeedback'][0], $qo->questiontextformat);
                }
            }
            if (!isset($qo->otherunitfeedback)) {
                $qo->otherunitfeedback = array('text' => '', 'format' => $qo->questiontextformat);
            }
        }
        return $qo;
    }

    protected function import_html(qformat_xml $format, $data, $defaultformat) {
        $text = array();
        $text['text'] = $format->getpath($data, array('#', 'text', 0, '#'), '', true);
        $text['format'] = $format->trans_format($format->getpath($data, array('@', 'format'),
                                                $format->get_format($defaultformat)));
        $text['files'] = $format->import_files($format->getpath($data, array('#', 'file'), array(), false));

        return $text;
    }

    public function export_to_xml($question, qformat_xml $format, $extra=null) {
        $expout = parent::export_to_xml($question, $format, $extra);
        $units = $question->options->units;
        foreach ($units as $unit) {
            $expout .= "    <unit>\n";
            $fields = array(
                'units'         => 'unit',
                'unitsfraction' => 'fraction',
                'spaceinunit'   => 'spaceinunit',
                'replacedash'   => 'replacedash',
            );
            foreach ($fields as $xmlfield => $dbfield) {
                $exportedvalue = $format->xml_escape($unit->$dbfield);
                $expout .= "      <$xmlfield>{$exportedvalue}</$xmlfield>\n";
            }
            $expout .= $this->export_html($format, 'qtype_varnumunit', 'unitsfeedback', $question->contextid,
                                            $unit, 'feedback', 'unitsfeedback');
            $expout .= $this->export_html($format, 'qtype_varnumunit', 'spacesfeedback', $question->contextid,
                                            $unit, 'spacingfeedback', 'spacesfeedback');
            $expout .= "    </unit>\n";
        }
        return $expout;
    }

    public function export_html($format, $component, $filearea, $contextid, $rec, $dbfield, $xmlfield) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, $component, $filearea, $rec->id);
        $formatfield = $dbfield.'format';

        $output = '';
        $output .= "    <{$xmlfield} {$format->format($rec->$formatfield)}>\n";
        $output .= '      '.$format->writetext($rec->$dbfield);
        $output .= $format->write_files($files);
        $output .= "    </{$xmlfield}>\n";
        return $output;
    }

    /**
     * Get spaceinunit options.
     *
     * @return array
     */
    public static function spaceinunit_options() {
        return [
            self::SPACEINUNIT_REMOVE_ALL_SPACE => get_string('removeallspace', 'qtype_varnumunit'),
            self::SPACEINUNIT_PRESERVE_SPACE_NOT_REQUIRE => get_string('preservespacenotrequire', 'qtype_varnumunit'),
            self::SPACEINUNIT_PRESERVE_SPACE_REQUIRE => get_string('preservespacerequire', 'qtype_varnumunit'),
        ];
    }
}


/**
 * Class to represent a varnumunit question unit, loaded from the qtype_varnumunit_units table
 * in the database.
 *
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_varnumunit_unit {

    public $id;
    /**
     * @var string pmatch expression
     */
    public $unit;
    public $spaceinunit;
    public $spacingfeedback;
    public $spacingfeedbackformat;
    public $replacedash;
    public $fraction;
    public $feedback;
    public $feedbackformat;

    public function __construct($id, $unit, $spaceinunit, $spacingfeedback, $spacingfeedbackformat, $replacedash,
                                $fraction, $feedback, $feedbackformat) {
        $this->id = $id;
        $this->unit = $unit;
        $this->spaceinunit = $spaceinunit;
        $this->spacingfeedback = $spacingfeedback;
        $this->spacingfeedbackformat = $spacingfeedbackformat;
        $this->replacedash = $replacedash;
        $this->fraction = $fraction;
        $this->feedback = $feedback;
        $this->feedbackformat = $feedbackformat;
    }
}
