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
 * Define all the backup steps that will be used by the backup_unedtrivial_activity_task
 *
 * @package    mod_unedtrivial
 * @category   backup 
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die;


class backup_unedtrivial_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the backup structure of the module
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // Get know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define the root element describing the unedtrivial instance.
        $unedtrivial = new backup_nested_element('unedtrivial', array('id'), array(
            'name', 'intro', 'introformat', 'grade', 'enddate', 'questionsperday', 'retryerror',
            'retrysuccess', 'timestocomplete', 'scoreerror', 'scoresuccess', 'teachermails',
            'completionsubmit'));
        
        // If we had more elements, we would build the tree here.
        $question = new backup_nested_element('question', array('id'), array(
            'qtype', 'question', 'option1', 'option2', 'option3', 'option4', 'answer', 'explanation',
            'shuffle'
        ));

        // Now that we have all of the elements created we've got to put them
        // together correctly.
        $unedtrivial->add_child($question);
        
        // Define data sources.
        $unedtrivial->set_source_table('unedtrivial', array('id' => backup::VAR_ACTIVITYID));
        
        $question->set_source_table('unedtrivial_questions', array('idunedtrivial' => backup::VAR_PARENTID));

        // If we were referring to other tables, we would annotate the relation
        // with the element's annotate_ids() method.

        // Define file annotations (we do not use itemid in this example).
        $unedtrivial->annotate_files('mod_unedtrivial', 'intro', null);

        // Return the root element (unedtrivial), wrapped into standard activity structure.
        return $this->prepare_activity_structure($unedtrivial);
    }
}
