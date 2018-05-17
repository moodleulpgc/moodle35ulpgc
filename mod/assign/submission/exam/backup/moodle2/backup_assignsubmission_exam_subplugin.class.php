<?php
// This exam is part of Moodle - http://moodle.org/
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
 * This exam contains the class for backup of this submission plugin
 *
 * @package   assignsubmission_exam
 * @copyright 2014 Enrique Castro, ecastro  @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the information to backup submission exams
 *
 * This just adds its examarea to the annotations and records the number of exams
 *
 * @package assignsubmission_exam
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_assignsubmission_exam_subplugin extends backup_subplugin {

    /**
     * Returns the subplugin information to attach to submission element
     * @return backup_subplugin_element
     */
    protected function define_submission_subplugin_structure() {

        // Create XML elements.
        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelement = new backup_nested_element('submission_exam',
                                                      null,
                                                      array('numexams', 'submission', 'examid', 'status'));
        /// TODO annotate examid  TODO ///
        /// TODO annotate examid  TODO ///

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

        // Set source to populate the data.
        $subpluginelement->set_source_table('assignsubmission_exam',
                                            array('submission' => backup::VAR_PARENTID));

        // The parent is the submission.
        $subpluginelement->annotate_files('assignsubmission_exam',
                                          'submission_exam',
                                          'submission');
        /// TODO annotate examid  TODO ///
        /// TODO annotate examid  TODO ///
        return $subplugin;
    }

}
