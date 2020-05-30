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
 * @package   qtype_varnumunit
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/varnumericset/backup/moodle2/backup_qtype_varnumericset_plugin.class.php');


/**
 * Provides the information to backup varnumunit questions.
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_qtype_varnumunit_plugin extends backup_qtype_plugin {

    /**
     * Returns the qtype information to attach to question element.
     */
    protected function define_question_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '../../qtype', 'varnumunit');

        // Create one standard named plugin element (the visible container).
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        // This qtype uses standard question_answers, add them here
        // to the tree before any other information that will use them.
        $this->add_question_question_answers($pluginwrapper);

        // Extra answer fields for varnumunit question type.
        $this->add_question_qtype_varnumunit_answers($pluginwrapper);

        $this->add_question_qtype_varnumunit_vars($pluginwrapper);

        $this->add_question_qtype_varnumunit_units($pluginwrapper);

        // Now create the qtype own structures.
        $varnumunit = new backup_nested_element('varnumunit', array('id'), array(
            'randomseed', 'recalculateeverytime', 'requirescinotation', 'unitfraction'));

        // Now the own qtype tree.
        $pluginwrapper->add_child($varnumunit);

        // Set source to populate the data.
        $varnumunit->set_source_table('qtype_varnumunit',
                array('questionid' => backup::VAR_PARENTID));

        // Don't need to annotate ids nor files.

        return $plugin;
    }

    protected function add_question_qtype_varnumunit_vars($element) {
        // Check $element is one nested_backup_element.
        if (! $element instanceof backup_nested_element) {
            throw new backup_step_exception('qtype_varnumunit_vars_bad_parent_element', $element);
        }

        // Define the elements.
        $vars = new backup_nested_element('vars');
        $var = new backup_nested_element('var', array('id'),
                                                array('varno', 'nameorassignment'));

        $this->add_question_qtype_varnumunit_variants($var);

        // Build the tree.
        $element->add_child($vars);
        $vars->add_child($var);

        // Set source to populate the data.
        $var->set_source_table('qtype_varnumunit_vars',
                                                array('questionid' => backup::VAR_PARENTID));
    }

    protected function add_question_qtype_varnumunit_variants($element) {
        // Check $element is one nested_backup_element.
        if (! $element instanceof backup_nested_element) {
            throw new backup_step_exception('qtype_varnumunit_variants_bad_parent_element',
                                                                                        $element);
        }

        // Define the elements.
        $variants = new backup_nested_element('variants');
        $variant = new backup_nested_element('variant', array('id'),
                                                array('varid', 'variantno', 'value'));

        // Build the tree.
        $element->add_child($variants);
        $variants->add_child($variant);

        // Set source to populate the data.
        $variant->set_source_table('qtype_varnumunit_variants',
                                                array('varid' => backup::VAR_PARENTID));
    }

    protected function add_question_qtype_varnumunit_answers($element) {
        // Check $element is one nested_backup_element.
        if (! $element instanceof backup_nested_element) {
            throw new backup_step_exception('question_varnumunit_answers_bad_parent_element',
                                                $element);
        }

        // Define the elements.
        $answers = new backup_nested_element('varnumunit_answers');
        $answer = new backup_nested_element('varnumunit_answer', array('id'), array(
            'answerid', 'error', 'sigfigs', 'checknumerical', 'checkscinotation',
            'checkpowerof10', 'checkrounding', 'syserrorpenalty'));

        // Build the tree.
        $element->add_child($answers);
        $answers->add_child($answer);

        // Set the sources.
        $answer->set_source_sql('
                SELECT vans.*
                  FROM {qtype_varnumunit_answers} vans
                  JOIN {question_answers} ans ON ans.id = vans.answerid
                 WHERE ans.question = :question
              ORDER BY id',
                array('question' => backup::VAR_PARENTID));
        // Don't need to annotate ids or files.
    }

    protected function add_question_qtype_varnumunit_units($element) {
        // Check $element is one nested_backup_element.
        if (! $element instanceof backup_nested_element) {
            throw new backup_step_exception('qtype_varnumunit_units_bad_parent_element', $element);
        }

        // Define the elements.
        $units = new backup_nested_element('units');
        $unit = new backup_nested_element('unit', ['id'],
            ['unit', 'spaceinunit', 'spacingfeedback', 'spacingfeedbackformat', 'replacedash', 'fraction',
                    'feedback', 'feedbackformat']);

        // Build the tree.
        $element->add_child($units);
        $units->add_child($unit);

        // Set source to populate the data.
        $unit->set_source_table('qtype_varnumunit_units',
                array('questionid' => backup::VAR_PARENTID), 'id ASC');
    }

    /**
     * Returns one array with filearea => mappingname elements for the qtype.
     *
     * Used by {@link get_components_and_fileareas} to know about all the qtype
     * files to be processed both in backup and restore.
     */
    public static function get_qtype_fileareas() {
        return array('unitsfeedback' => 'qtype_varnumunit_unit');
    }
}
