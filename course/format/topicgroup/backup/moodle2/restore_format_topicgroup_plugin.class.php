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

class restore_format_topicgroup_plugin extends restore_format_plugin {

    protected function define_course_plugin_structure() {

        $paths = array();

        $elename = 'topicgroup';

        /*
         * This is defines the nested tag within 'plugin_format_topicgroup_course' to allow '/course/plugin_format_topicgroup_course' in
         * the path therefore as a path structure representing the levels in section.xml in the backup file.
         */
        $elepath = $this->get_pathfor('/');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    /**
     * process restore of posts.
     */
    public function process_topicgroup($data) {
        global $DB;

        $data = (object) $data;

        // ... remember oldid for mapping.
        $oldid = $data->id;

        // nothing to do here
    }

    /**
     * Returns the paths to be handled by the plugin at section level
     */
    protected function define_section_plugin_structure() {

        $paths = array();

        // Add own format stuff.
        $elename = 'tpsection'; // This defines the postfix of 'process_*' below.
        /* This is defines the nested tag within 'plugin_format_topicgroup_section' to allow '/section/plugin_format_topicgroup_section' in
         * the path therefore as a path structure representing the levels in section.xml in the backup file.
         */
        $elepath = $this->get_pathfor('/');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.
    }

    /**
     * Process the 'plugin_format_topicgroup_section' element within the 'section' element in the 'section.xml' file in the
     * '/sections/section_sectionid' folder of the zipped backup 'mbz' file.
     */
    public function process_tpsection($data) {
        global $DB;

        $data = (object) $data;

        /* We only process this information if the course we are restoring to
          has 'topicgroup' format (target format can change depending of restore options). */
        $format = $DB->get_field('course', 'format', array('id' => $this->task->get_courseid()));
        if ($format != 'topicgroup') {
            return;
        }

        $data->course = $this->task->get_courseid();
        $data->sectionid = $this->task->get_sectionid();
        $data->groupingid = $this->get_mappingid('grouping', $data->groupingid);


        if (!$DB->record_exists('format_topicgroup_sections', array('course' => $data->course, 'sectionid' => $data->sectionid))) {
            $now = time();
            $data->timecreated = $now;
            $data->timemodified = $now;
            if (!$DB->insert_record('format_topicgroup_sections', $data, true)) {
                throw new moodle_exception('invalidrecordid', 'format_topicgroup', '',
                'Could not insert section. Table format_topicgroup_sections is not ready. An administrator must visit the notifications section.');
            }
        } else {
            $old = $DB->get_record('format_topicgroup_sections', array('course' => $data->course, 'sectionid' => $data->sectionid));
            // Always update missing groupingids during restore / import, noting merge into existing course currently doesn't restore the topicgroup sections.
            if (!$old->groupingid) {
                $old->groupingid = $data->groupingid;
                $old->timemodified = time();
                if (!$DB->update_record('format_topicgroup_sections', $old)) {
                    throw new moodle_exception('invalidrecordid', 'format_topicgroup', '',
                    'Could not update section. Table format_topicgroup_sections is not ready. An administrator must visit the notifications section.');
                }
            }
        }

        // No need to annotate anything here.
    }


}