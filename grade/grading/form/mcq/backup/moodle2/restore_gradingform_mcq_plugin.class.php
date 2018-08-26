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
 * Support for restore API
 *
 * @package    gradingform_mcq
 * @copyright  2014 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
/**
 * Used when restoring a course backup
 * @package    gradingform_mcq
 * @copyright  2018 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_gradingform_mcq_plugin extends restore_gradingform_plugin {

    /**
     * Declares the marking mcq XML paths attached to the form definition element
     *
     * @return array of {@link restore_path_element}
     */
    protected function define_definition_plugin_structure() {

        $paths = array();

        $paths[] = new restore_path_element('gradingform_mcq_criterion',
            $this->get_pathfor('/mcqcriteria/mcqcriterion'));

        return $paths;
    }

    /**
     * Declares the marking mcq XML paths attached to the form instance element
     *
     * @return array of {@link restore_path_element}
     */
    protected function define_instance_plugin_structure() {

        $paths = array();

        $paths[] = new restore_path_element('gradinform_mcq_filling',
            $this->get_pathfor('/fillings/filling'));

        return $paths;
    }

    /**
     * Processes criterion element data
     *
     * Sets the mapping 'gradingform_mcq_criterion' to be used later by
     * {@link self::process_gradinform_mcq_filling()}
     *
     * @param array|stdClass $data
     */
    public function process_gradingform_mcq_criterion($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->definitionid = $this->get_new_parentid('grading_definition');

        $newid = $DB->insert_record('gradingform_mcq_criteria', $data);
        $this->set_mapping('gradingform_mcq_criterion', $oldid, $newid);
    }

    /**
     * Processes filling element data
     *
     * @param array|stdClass $data The data to insert as a filling
     */
    public function process_gradinform_mcq_filling($data) {
        global $DB;

        $data = (object)$data;
        $data->instanceid = $this->get_new_parentid('grading_instance');
        $data->criterionid = $this->get_mappingid('gradingform_mcq_criterion', $data->criterionid);

        $DB->insert_record('gradingform_mcq_fillings', $data);
    }
}
