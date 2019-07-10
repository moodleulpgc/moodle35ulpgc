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
 * All the steps to restore mod_videolib are defined here.
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

/**
 * Defines the structure step to restore one mod_videolib activity.
 */
class restore_videolib_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines the structure to be restored.
     *
     * @return restore_path_element[].
     */
    protected function define_structure() {
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('videolib', '/activity/videolib');
        $paths[] = new restore_path_element('sourcekey', '/activity/videolib/sourcekeys/sourcekey');

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Processes the videolib restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_videolib($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // insert the resource record
        $newitemid = $DB->insert_record('videolib', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);

        return;
    }

    /**
     * Processes the videolib restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_sourcekey($data) {
        global $DB;

        $data = (object)$data;
        $data->cmids = null;
        $oldid = $data->id;
        unset($data->id);

        // insert the resource record
        $params = array('videolibkey'=>$data->videolibkey, 'annuality'=>$data->annuality, 'source'=>$data->source);
        if($rec = $DB->get_record('videolib_source_mapping', $params, 'id, videolibkey')) {
            $data->id = $rec->id;
        } else {
            $newitemid = $DB->insert_record('videolib_source_mapping', $data);
        }

        return;
    }
    
    
    
    /**
     * Defines post-execution actions.
     */
    protected function after_execute() {
        $this->add_related_files('mod_videolib', 'intro', null);
        return;
    }
}
