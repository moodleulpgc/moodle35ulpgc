<?php

/**
 * @package    mod
 * @subpackage registry
 * @copyright  2011 Henning Bostelmann and others (see README.txt)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/mod/registry/backup/moodle2/backup_registry_stepslib.php'); // Because it exists (must)

/**
 * registry backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_registry_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // registry only has one structure step
        $this->add_step(new backup_registry_activity_structure_step('registry_structure', 'registry.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        // Link to the list of registrys
        $search="/(".$base."\/mod\/registry\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@registryINDEX*$2@$', $content);

        // Link to registry view by coursemoduleid
        $search="/(".$base."\/mod\/registry\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@registryVIEWBYID*$2@$', $content);

        return $content;
    }
}
