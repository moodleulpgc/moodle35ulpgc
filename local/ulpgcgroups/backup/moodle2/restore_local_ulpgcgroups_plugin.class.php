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
 * Restore plugin class that provides the necessary information
 * needed to restore ulpgcgroups helper tables.
 */
class restore_local_ulpgcgroups_plugin extends restore_local_plugin {

    /**
     * Returns the paths to be handled by the plugin at course level.
     */
    protected function define_course_plugin_structure() {
        $paths = array();
        $elename = 'plugin_local_ulpgcgroups_group'; // This defines the postfix of 'process_*' below.
        $elepath = $this->get_pathfor('/');
        $paths[] = new restore_path_element($elename, $elepath);
       
        return $paths; // And we return the interesting paths.
    }

    /**
     * Process the course element.
     */
    public function process_plugin_local_ulpgcgroups_group($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->groupid;
        $data->courseid = $this->task->get_courseid(); //$this->get_courseid();
        
         if($groups = $this->task->get_setting_value('groups') && $data->groupid = $this->get_mappingid('group', $data->groupid)) {
            if(!$DB->record_exists('groups', array('id'=>$data->groupid, 'courseid'=>$this->task->get_courseid()))) {
                return;
            }
            // OK, we have a group, process component data 
            if ((strpos($data->component, 'enrol_') === 0)) { //first check enrol plugins, the common components
                // Deal with enrolment groups - ignore the component and just find out the instance via new id,
                // it is possible that enrolment was restored using different plugin type.
                $type = substr($data->component, 6);
                $enrols = enrol_get_plugins(true);
                if ($enrolid = $this->get_mappingid('enrol', $data->itemid)) {
                    if ($instance = $DB->get_record('enrol', array('id'=>$enrolid, 'enrol'=>type))) {
                        $data->itemdid = $enrolid;
                    } else {
                        $data->component = '';
                    }
                }
            } elseif($data->component) { // check other components
                $dir = core_component::get_component_directory($data->component);
                if ($dir and is_dir($dir)) {
                    if ($itemid = $this->get_mappingid($data->component, $data->itemid)) {
                        $data->itemdid = $itemid;
                    } elseif (component_callback($data->component, 'restore_ulpgc_group', array($this, $data), true)) {
                        return;
                    } else {
                        $data->component = '';
                    }
                }
            }
            if($data->component) {
                if($old = $DB->get_record('local_ulpgcgroups', array('groupid'=>$data->groupid))) {
                    $data->id = $old->id;
                    $DB->update_record('local_ulpgcgroups', $data);
                } else {
                    $DB->insert_record('local_ulpgcgroups', $data);
                }
            } else {
                $message = "Restore of '{$data->component}/{$data->itemid}' group dependency is not possible, leaving group unrestricted. ";
                $this->log($message, backup::LOG_WARNING);
            }
        }
    }
    
    
}
