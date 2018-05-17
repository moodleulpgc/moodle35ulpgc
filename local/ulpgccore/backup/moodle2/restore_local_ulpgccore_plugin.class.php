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
 * Restore plugin class that provides the necessary information
 * needed to restore ulpgccore helper tables.
 */
class restore_local_ulpgccore_plugin extends restore_local_plugin {

    /**
     * Returns the paths to be handled by the plugin at course level.
     */
    protected function define_course_plugin_structure() {
    
        $paths = array();
        $elename = 'plugin_local_ulpgccore_course'; // This defines the postfix of 'process_*' below.
        $elepath = $this->get_pathfor('/');
        $paths[] = new restore_path_element($elename, $elepath);
        
        return $paths; // And we return the interesting paths.
    }

    /**
     * Process the course element.
     */
    public function process_plugin_local_ulpgccore_course($data) {
        global $DB;
        $data = (object)$data;
        $data->courseid = $this->task->get_courseid(); //$this->get_courseid();
        $this->task->log("procesado plugin_local_ulpgccore_course", backup::LOG_DEBUG);
        
        if($old = $DB->get_record('local_ulpgccore_course', array('courseid'=>$data->courseid))) {
            $data->id = $old->id;
            $DB->update_record('local_ulpgccore_course', $data);
        } else {
             $DB->insert_record('local_ulpgccore_course', $data);
        }

    }

    /**
     * after_restore method for tasks upon course restore completion
     */
    public function after_restore_course() {
        global $DB;
                // ecastro ULPGC
        // this is a hack to eliminate all adminmodules not desired if root setting adminnmods is set to NO (process_modules will set score=127)
        // Need to be eliminated when a better method, avoiding restoring to then delete, is developed
        if(!$adminmods = $this->task->get_setting_value('adminmods') && $mods = $DB->get_records('course_modules', array('score'=>-1))) {
            foreach($mods as $cm) {
                course_delete_module($cm->id);
            }
            rebuild_course_cache($this->task->get_courseid());
        }
    }
    
    /**
     * after_restore method for tasks upon module restore completion
     */
    public function after_restore_module() {
    }

    /**
     * Returns the paths to be handled by the plugin at question level.
     */
    protected function define_question_plugin_structure() {
        $paths = array();
        $elename = 'plugin_local_ulpgccore_question'; // This defines the postfix of 'process_*' below.
        $elepath = $this->get_pathfor('/');
        $paths[] = new restore_path_element($elename, $elepath);
        return $paths; // And we return the interesting paths.
    }
    
    /**
     * Process the question element.
     */
    public function process_plugin_local_ulpgccore_question($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->questionid;
        $data->questionid = $this->get_mappingid('question', $data->questionid);
        if ($data->questionid) {
            if(!$DB->record_exists('local_ulpgccore_questions', array('questionid'=>$data->questionid))) {
                $DB->insert_record('local_ulpgccore_questions', $data);
            }
        } else {
            $this->set_mapping('local_ulpgccore_questions', $this->get_old_parentid('question'), $this->get_new_parentid('question'));
        }
    } 
    
    /**
     * Returns the paths to be handled by the plugin at question level.
     */
    protected function define_module_plugin_structure() {
        $paths = array();
        $elename = 'plugin_local_ulpgccore_module'; // This defines the postfix of 'process_*' below.
        $elepath = $this->get_pathfor('/');
        $paths[] = new restore_path_element($elename, $elepath);
        return $paths; // And we return the interesting paths.
    }
    
    /**
     * Process the module element.
     */
    public function process_plugin_local_ulpgccore_module($data) {
        global $DB;
        // hack, course modules with score = -1 will be deleted afterwards
        $data = (object)$data;
        if(isset($data->score) && $data->score && !$adminmods = $this->task->get_setting_value('adminmods')) {
            $DB->set_field('course_modules', 'score', -1, array('course'=>$this->task->get_courseid(), 'id'=>$this->task->get_moduleid()));
        }
    }
    
}