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
 * Version details
 *
 * @package format_topicgroup
 * @copyright 2015 E. Castro (ULPGC)
 * @author Enrique Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Provides the information to backup grid course format
 */
class backup_format_topicgroup_plugin extends backup_format_plugin {

    /**
     * Returns the format information to attach to course element
     */
    protected function define_course_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '/course/format', 'topicgroup');

        // Create one standard named plugin element (the visible container).
        // The courseid not required as populated on restore.
        // optional array any with course-depependent data related to format
        //$pluginwrapper = new backup_nested_element($this->get_recommended_name(), null, array('showsummary'));
        //$pluginwrapper = new backup_nested_element($this->get_recommended_name());
        //$plugin->add_child($pluginwrapper);

        // Set source to populate the data.
        //$pluginwrapper->set_source_table('format_xxxxxx', array('courseid' => backup::VAR_PARENTID));
        /// No data for this plugin

        // Don't need to annotate ids nor files.
        return $plugin;
    }


    /**
     * Returns the format information to attach to section element
     */
    protected function define_section_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, $this->get_format_condition(), 'topicgroup');

        // Create one standard named plugin element (the visible container).
        // The sectionid and courseid not required as populated on restore.
        $pluginwrapper = new backup_nested_element($this->get_recommended_name(),
                                                        null, array('groupingid', 'timecreated', 'timemodified'));
        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        // Set source to populate the data.
        $pluginwrapper->set_source_table('format_topicgroup_sections', array(
            'sectionid' => backup::VAR_SECTIONID));

        // Define id annotations
        $pluginwrapper->annotate_ids('grouping', 'groupingid');

        return $plugin;
    }

}