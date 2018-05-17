<?php
/**
 * ULPGC specific customizations
 *
 * @package    local
 * @subpackage assigndata
 * @copyright  2017 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// This file manages backup of helper tables in the assigndata plugin


defined('MOODLE_INTERNAL') || die();

/**
 * Backup plugin class that provides the necessary information
 * needed to backup assigndata helper tables.
 */
class backup_local_assigndata_plugin extends backup_local_plugin {
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // not working ??
    }
 
    protected function define_course_plugin_structure() {
        
        // Define the virtual plugin element with the condition to fulfill.
        //$plugin = $this->get_plugin_element(null, null, null);

        // Create one standard named plugin element (the visible container).
        // The courseid not required as populated on restore.
        /*
        $pluginwrapper = new backup_nested_element($this->get_recommended_name(), null, array('term', 'credits', 'department', 'ctype'));
        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        // Set source to populate the data.
        $pluginwrapper->set_source_table('local_assigndata_course', array(
            'courseid' => backup::VAR_PARENTID));
        */

        return '';
    }
    
    protected function define_module_plugin_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');
        
        $plugin = '';
        
        if('assign' == $this->task->get_modulename()) {  
            // Define the virtual plugin element without conditions as the global class checks already.
            $plugin = $this->get_plugin_element();

            // Create one standard named plugin element (the visible container).
            $pluginwrapper = new backup_nested_element($this->get_recommended_name());

            // Connect the visible container ASAP.
            $plugin->add_child($pluginwrapper);

            $fields = new backup_nested_element('assigndata_fields');

            $field = new backup_nested_element('field', array('id'), array(
                'type', 'name', 'description', 'required', 'sortorder', 
                'param1', 'param2', 'param3', 'param4', 'param5', 
                'param6', 'param7', 'param8', 'param9', 'param10'));
                
            $field->set_source_table('local_assigndata_fields', array('course'=>backup::VAR_COURSEID, 'assignment'=>backup::VAR_ACTIVITYID));            
            
            $submissions = new backup_nested_element('submissions');

            $submission = new backup_nested_element('assigndata_submission', array('id'),
                                                    array('userid', 'attemptnumber', 'groupid',
                                                        'fieldid',
                                                        'content',
                                                        'content1',
                                                        'content2',
                                                        'content3',
                                                        'content4'));
            // Build the tree
            $pluginwrapper->add_child($fields);
            $fields->add_child($field);
            
            $field->add_child($submissions);
            $submissions->add_child($submission);
            $submission->annotate_ids('user', 'userid');
            $submission->annotate_ids('group', 'groupid');
                        
            if($userinfo) {
                $submission->set_source_table('local_assigndata_submission',
                                array('assignment' =>backup::VAR_ACTIVITYID, 'fieldid' => backup::VAR_PARENTID));
            }
        }
        return $plugin;
    }
    
}
