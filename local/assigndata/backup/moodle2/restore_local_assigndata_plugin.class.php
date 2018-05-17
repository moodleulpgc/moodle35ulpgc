<?php
/**
 * ULPGC specific customizations
 *
 * @package    local
 * @subpackage assigndata
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// This file manages backup of helper tables in the assigndata plugin


defined('MOODLE_INTERNAL') || die();

/**
 * Restore plugin class that provides the necessary information
 * needed to restore assigndata helper tables.
 */
class restore_local_assigndata_plugin extends restore_local_plugin {

    /**
     * Returns the paths to be handled by the plugin at course level.
     */
    protected function define_course_plugin_structure() { // removed for backup
        
        return ''; // And we return the interesting paths.
    }

    
    /**
     * Returns the paths to be handled by the plugin at question level.
     */
    protected function define_module_plugin_structure() {
        $paths = array();
        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');
        
        $elename = 'plugin_local_assigndata_module'; // This defines the postfix of 'process_*' below.
        //$elepath = $this->get_pathfor('/');
        $elepath = $this->get_pathfor('/assigndata_fields/field');
        $paths[] = new restore_path_element($elename, $elepath);
        if ($userinfo) {
            $paths[] = new restore_path_element('assigndata_submission',
                                                   '/module/plugin_local_assigndata_module/assigndata_fields/field/submissions/assigndata_submission');
        }
        
        return $paths; // And we return the interesting paths.
    }
    
    /**
     * Process the module element.
     */
    public function process_plugin_local_assigndata_module($data) {
        global $DB;
        // hack, course modules with score = -1 will be deleted afterwards
        $data = (object)$data;
        $oldid = $data->id;

        $cmid = $this->task->get_moduleid();
        $data->assignment = -$cmid;
        $data->course = $this->task->get_courseid(); 
        $newfieldid = $DB->insert_record('local_assigndata_fields', $data);
        $this->set_mapping('assigndata_field', $oldid, $newfieldid);

    }

    /**
     * Process the module element.
     */
    public function process_assigndata_submission($data) {
        global $DB;
        // hack, course modules with score = -1 will be deleted afterwards
        $data = (object)$data;
        $cmid = $this->task->get_moduleid();

        $data->assignment = -$cmid;
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        $data->fieldid = $this->get_mappingid('assigndata_field', $data->fieldid);
        $DB->insert_record('local_assigndata_submission', $data);
    }
    
    
    /**
     * after_restore method for tasks upon module restore completion
     */
    public function after_restore_module() {
        global $DB;
        
        $cmid = $this->task->get_moduleid();
        
        $instance = $DB->get_field('course_modules', 'instance', array('id' => $cmid));
        
        $DB->set_field('local_assigndata_fields', 'assignment', $instance, array('assignment' => -$cmid)); 
        
        $DB->set_field('local_assigndata_submission', 'assignment', $instance, array('assignment' => -$cmid));
    }

}
