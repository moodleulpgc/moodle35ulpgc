<?php

/**
 * @package    mod
 * @subpackage registry
 * @copyright  2011 Henning Bostelmann and others (see README.txt)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Define all the backup steps that will be used by the backup_registry_activity_task
 */

/**
 * Define the complete registry structure for backup, with file and id annotations
 */
class backup_registry_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $registry = new backup_nested_element('registry', array('id'), array(
            'name', 'intro', 'introformat', 'category', 'timedue', 'regmodule', 'regsection',
            'visibility', 'adminmod', 'tracker', 'issuename', 'itemname', 'scale', 'timecreated','timemodified'));

        $regsubs = new backup_nested_element('submissions');

        $regsub = new backup_nested_element('submission', array('id'), array(
            'registryid', 'regcourse', 'userid', 'itemhash', 'issueid', 'grade', 'timegraded', 'timecreated', 'timemodified'));

        // Build the tree

        $registry->add_child($regsubs);
        $regsubs->add_child($regsub);

        // Define sources
        $registry->set_source_table('registry', array('id' => backup::VAR_ACTIVITYID));

        // Include appointments only if we back up user information
        if ($userinfo) {
            $regsub->set_source_table('registry_submissions', array('registryid' => backup::VAR_PARENTID));
        }

        // Define id annotations
        $registry->annotate_ids('scale', 'scale');

        $regsub->annotate_ids('user', 'userid');

        // Define file annotations
        $registry->annotate_files('mod_registry', 'intro', null); // This file area has no itemid

        // Return the root element (registry), wrapped into standard activity structure
        return $this->prepare_activity_structure($registry);
    }
}
