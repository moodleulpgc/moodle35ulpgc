<?php
/**
 * ULPGC specific customizations
 *
 * @package    local
 * @subpackage ulpgcgroups
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// This file manages backup of helper tables in the ulpgcgroups plugin


defined('MOODLE_INTERNAL') || die();

/**
 * Backup plugin class that provides the necessary information
 * needed to backup ulpgcgroups helper tables.
 */
class backup_local_ulpgcgroups_plugin extends backup_local_plugin {
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // not working ??
    }
 
    protected function define_course_plugin_structure() {
        
        if(!$groupinfo = $this->get_setting_value('groups')) {
            return;
        }
        
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, null, null);

        // Create one standard named plugin element (the visible container).
        // The courseid not required as populated on restore.
        $pluginwrapper = new backup_nested_element('plugin_local_ulpgcgroups_group', array('id'), array('groupid', 'component', 'itemid'));

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        // Set source to populate the data.
        $pluginwrapper->set_source_sql("SELECT lg.id, lg.groupid, lg.component, lg.itemid
                                    FROM {groups} g
                                    JOIN {local_ulpgcgroups} lg ON g.id = lg.groupid
                                    WHERE g.courseid = ?", array(backup::VAR_PARENTID));
        $pluginwrapper->annotate_ids('group', 'groupid');

        return $plugin;
    }
       
}