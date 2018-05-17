<?php
/**
 * ULPGC specific customizations
 *
 * @package    local
 * @subpackage ulpgccore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// This file manages backup of helper tables in the ulpgccore plugin


defined('MOODLE_INTERNAL') || die();

/**
 * Backup plugin class that provides the necessary information
 * needed to backup ulpgccore helper tables.
 */
class backup_local_ulpgccore_plugin extends backup_local_plugin {
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // not working ??
    }
 
    protected function define_course_plugin_structure() { // remove for backup
        
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, null, null);

        // Create one standard named plugin element (the visible container).
        // The courseid not required as populated on restore.
        /*
        $pluginwrapper = new backup_nested_element($this->get_recommended_name(), null, array('term', 'credits', 'department', 'ctype'));

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        // Set source to populate the data.
        $pluginwrapper->set_source_table('local_ulpgccore_course', array(
            'courseid' => backup::VAR_PARENTID));
        */
        return $plugin;
    }
    
    
    
    protected function define_question_plugin_structure() {
        $plugin = $this->get_plugin_element(null, null, null);
        $pluginwrapper = new backup_nested_element($this->get_recommended_name(), null, array('questionid', 'qsource', 'sourceqid', 'creatoridnumber', 'modifieridnumber'));
        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        // Set source to populate the data.
        $pluginwrapper->set_source_table('local_ulpgccore_questions',array('questionid' => backup::VAR_PARENTID));
                
        return $plugin;
    }
    
    protected function define_module_plugin_structure() {
        $plugin = $this->get_plugin_element(null, null, null);
        $pluginwrapper = new backup_nested_element($this->get_recommended_name(), null, array('score'));
        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        // Set source to populate the data.
        $pluginwrapper->set_source_sql("SELECT score 
                                        FROM {course_modules} 
                                        WHERE id = :id AND score > 0", 
                                        array('id' => backup::VAR_PARENTID));
        return $plugin;
    }
    
}
