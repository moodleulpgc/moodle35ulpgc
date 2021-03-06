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
 * The task that provides a complete restore of mod_videolib is defined here.
 *
 * @package     mod_videolib
 * @category    restore
 * @copyright   2018 Enrique Castro @ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// For more information about the backup and restore process, please visit:
// https://docs.moodle.org/dev/Backup_2.0_for_developers
// https://docs.moodle.org/dev/Restore_2.0_for_developers

require_once($CFG->dirroot.'//mod/videolib/backup/moodle2/restore_videolib_stepslib.php');

/**
 * Restore task for mod_videolib.
 */
class restore_videolib_activity_task extends restore_activity_task {

    /**
     * Defines particular settings that this activity can have.
     */
    protected function define_my_settings() {
        return;
    }

    /**
     * Defines particular steps that this activity can have.
     *
     * @return base_step.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_videolib_activity_structure_step('videolib_structure', 'videolib.xml'));
    }

    /**
     * Defines the contents in the activity that must be processed by the link decoder.
     *
     * @return array.
     */
    static public function define_decode_contents() {
        $contents = array();

        // Define the contents.
        $contents[] = new restore_decode_content('videolib', array('intro'), 'videolib');

        return $contents;
    }

    /**
     * Defines the decoding rules for links belonging to the activity to be executed by the link decoder.
     *
     * @return array.
     */
    static public function define_decode_rules() {
        $rules = array();

        // Define the rules.

        return $rules;
        $rules[] = new restore_decode_rule('VIDEOLIBVIEWBYID', '/mod/videolib/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('VIDEOLIBINDEX', '/mod/videolib/index.php?id=$1', 'course');

    }

    /**
     * Defines the restore log rules that will be applied by the
     * {@link restore_logs_processor} when restoring mod_videolib logs. It
     * must return one array of {@link restore_log_rule} objects.
     *
     * @return array.
     */
    static public function define_restore_log_rules() {
        $rules = array();

        // Define the rules.
        $rules[] = new restore_log_rule('videolib', 'add', 'view.php?id={course_module}', '{videolib}');
        $rules[] = new restore_log_rule('videolib', 'update', 'view.php?id={course_module}', '{videolib}');
        $rules[] = new restore_log_rule('videolib', 'view', 'view.php?id={course_module}', '{videolib}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('videolib', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
