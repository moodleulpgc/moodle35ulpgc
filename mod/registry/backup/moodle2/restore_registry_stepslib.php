<?php

/**
 * @package    mod
 * @subpackage registry
 * @copyright  2011 Henning Bostelmann and others (see README.txt)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Define all the restore steps that will be used by the restore_registry_activity_task
 */

/**
 * Structure step to restore one registry activity
 */
class restore_registry_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $registry = new restore_path_element('registry', '/activity/registry');
        $paths[] = $registry;

        if ($userinfo) {
            $submission = new restore_path_element('registry_submission', '/activity/registry/submissions/submission');
            $paths[] = $submission;
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_registry($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        if ($data->scale < 0) { // scale found, get mapping
            $data->scale = -($this->get_mappingid('scale', abs($data->scale)));
        }
        if(!isset($data->intro)) {
            $data->intro = '';
        }

        // insert the registry record
        $newitemid = $DB->insert_record('registry', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_registry_submission($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->registryid = $this->get_new_parentid('registry');

        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timegraded = $this->apply_date_offset($data->timegraded);

        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('registry_submissions', $data);
        $this->set_mapping('registry_submission', $oldid, $newitemid, true);
        // Apply only once we have files in the submission
    }

    protected function after_execute() {
        // Add registry related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_registry', 'intro', null);
    }
}
