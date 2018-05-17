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
 * Plugin common class methods, modifying  mod_data classes
 *
 * @package     local_assigndata
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
namespace local_assigndata;
defined('MOODLE_INTERNAL') || die();

/**
 * The field_base trait with common modified methods
 *
 * @package    local_assigndata
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait field_base {

    /** @var int The assignment instance that this field belongs to */
    var $assignid = NULL;
    
    /** @var int The course instance that this field belongs to */
    var $courseid = NULL;

    /** @var object DB record with actual content of each field in the record processed */
    var $content = NULL;

    /** @var int the ID if the content stored in local_assigndata_submission */
    var $submissionid = NULL;

    
    /**
     * Constructor function
     *
     * @global object
     * @uses CONTEXT_MODULE
     * @param int $field
     * @param int $data
     * @param int $cm
     */
    function __construct($field=0, $assignid=0, $cm=0) {   // Field or data or both, each can be id or object
        global $DB;

        if (empty($field) && empty($assignid)) {
            print_error('missingfield', 'data');
        }

        $this->assignid = $assignid;
        $this->data = new \stdclass();
        
        if (!empty($field)) {
            if (is_object($field)) {
                $this->field = $field;  // Programmer knows what they are doing, we hope
            } else if (!$this->field = $DB->get_record('local_assigndata_fields', array('id'=>$field))) {
                print_error('invalidfieldid', 'data');
            }
            if (empty($assignid)) {
                if (!$this->data->id = $DB->get_field('assign', 'id', array('id'=>$this->field->dataid))) {
                    print_error('invalidid', 'data');
                }
            }
        }

        if (empty($this->data)) {         // We need to define this properly
            if (!empty($assignid)) {
                $this->data = new \stdClass();
                if (!$this->data->id = $DB->get_field('assign', 'id', array('id'=>$assignid))) { // it's a way to confirm exists
                    print_error('invalidid', 'data');
                }
            } else {                      // No way to define it!
                print_error('missingdata', 'data');
            }
        }

        $this->data->id = $assignid; // this is more or less a hack, but allows to reuse most mode
        if ($cm) {
            $this->cm = $cm;
        } else {
            $this->cm = get_coursemodule_from_instance('assign', $this->data->id);
        }

        if (empty($this->field)) {         // We need to define some default values
            $this->define_default_field();
        }

        $this->courseid = $this->cm->course;
        
        $this->context = \context_module::instance($this->cm->id);
    }
    
    
    /**
     * This field just sets up a default field object
     *
     * @return bool
     */
    function define_default_field() {
        parent::define_default_field(); 
        global $OUTPUT;
        if (empty($this->data->id)) {
            echo $OUTPUT->notification('Programmer error: dataid not defined in field class');
        }
        $this->field->dataid = $this->assignid;
        $this->field->course = $this->courseid;
        $this->field->assignment = $this->assignid;

        return true;
    }
    
    /**
     * Set up the field object according to data in an object.  Now is the time to clean it!
     *
     * @return bool
     */
    function define_field($data) {
        parent::define_field($data); 
        $this->field->dataid = $this->assignid;
        $this->field->course = $this->courseid;
        $this->field->assignment = $this->assignid;
    }
    
    /**
     * Insert a new field in the database
     * We assume the field object is already defined as $this->field
     *
     * @global object
     * @return bool
     */
    function insert_field() {
        global $DB, $OUTPUT;

        if (empty($this->field)) {
            echo $OUTPUT->notification('Programmer error: Field has not been defined yet!  See define_field()');
            return false;
        }

        if($max = $DB->get_records_menu('local_assigndata_fields', array('assignment'=>$this->assignid, 'course'=>$this->courseid),
                                    'sortorder DESC', 'id,sortorder', 0,1)) {
            $max = reset($max) + 1;
        } else {
            $max = 0;
        }
        $this->field->sortorder = $max; 
                                    
        
        $this->field->id = $DB->insert_record('local_assigndata_fields',$this->field);

        // Trigger an event for creating this field.
        $event = event\field_created::create(array( 
            'objectid' => $this->field->id,
            'context' => $this->context,
            'other' => array(
                'fieldname' => $this->field->name,
                'assignment' => $this->data->id
            )
        ));
        $event->trigger();

        return true;
    }
    
    /**
     * Update a field in the database
     *
     * @global object
     * @return bool
     */
    function update_field() {
        global $DB;

        $DB->update_record('local_assigndata_fields', $this->field);

        // Trigger an event for updating this field.
        $event = event\field_updated::create(array(
            'objectid' => $this->field->id,
            'context' => $this->context,
            'other' => array(
                'fieldname' => $this->field->name,
                'assignment' => $this->data->id
            )
        ));
        $event->trigger();

        return true;
    }

    /**
     * Delete a field completely
     *
     * @global object
     * @return bool
     */
    function delete_field() {
        global $DB;

        if (!empty($this->field->id)) {
            // Get the field before we delete it.
            $field = $DB->get_record('local_assigndata_fields', array('id' => $this->field->id));

            $this->delete_content();
            $DB->delete_records('local_assigndata_fields', array('id'=>$this->field->id));

            // Trigger an event for deleting this field.
            $event = event\field_deleted::create(array(
                'objectid' => $this->field->id,
                'context' => $this->context,
                'other' => array(
                    'fieldname' => $this->field->name,
                    'assignment' => $this->data->id
                 )
            ));
            $event->add_record_snapshot('local_assigndata_fields', $field);
            $event->trigger();
        }

        return true;
    }    

    
    /**
     * Print the relevant form element to define the attributes for this field
     * viewable by teachers only.
     *
     * @global object
     * @global object
     * @return void Output is echo'd
     */
    function display_edit_field() {
        global $CFG, $DB, $OUTPUT;

        if (empty($this->field)) {   // No field has been defined yet, try and make one
            $this->define_default_field();
        }
        echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');

        echo '<form id="editfield" action="'.$CFG->wwwroot.'/local/assigndata/view.php" method="post">'."\n";
        echo '<input type="hidden" name="a" value="'.$this->data->id.'" />'."\n";
        echo '<input type="hidden" name="id" value="'.$this->cm->id.'" />'."\n";
        if (empty($this->field->id)) {
            echo '<input type="hidden" name="mode" value="add" />'."\n";
            $savebutton = get_string('add');
        } else {
            echo '<input type="hidden" name="fid" value="'.$this->field->id.'" />'."\n";
            echo '<input type="hidden" name="mode" value="update" />'."\n";
            $savebutton = get_string('savechanges');
        }
        echo '<input type="hidden" name="type" value="'.$this->type.'" />'."\n";
        echo '<input name="sesskey" value="'.sesskey().'" type="hidden" />'."\n";

        echo $OUTPUT->heading($this->name(), 3);

        require_once($CFG->dirroot.'/mod/data/field/'.$this->type.'/mod.html');

        echo '<div class="mdl-align">';
        echo '<input type="submit" value="'.$savebutton.'" />'."\n";
        echo '<input type="submit" name="cancel" value="'.get_string('cancel').'" />'."\n";
        echo '</div>';

        echo '</form>';

        echo $OUTPUT->box_end();
    }

    
    /**
     * Ccreated an object with predefined fields
     * 
     * @return mixed, false or object record from database
     */
    function get_empty_content() {     
        $content = new \stdClass();
        $content->fieldid = 0;
        $content->assignment = $this->assignid;
        $content->content = '';
        for($i = 1; $i < 5; $i++) {
            $content->{"content$i"} = null;
        }
        return $content;
    }
    
    /**
     * Collects the raw data content for this field from DB
     *
     * @global DB
     * @param int $submissionid
     * 
     * @return mixed, false or object record from database
     */
    function get_content($submissionid) { 
        global $DB;
        $this->content = $DB->get_record('local_assigndata_submission', array('assignment' => $this->assignid,
                                                                'id' => $submissionid,
                                                                'fieldid'=>$this->field->id));
        return $this->content;
    }
    
    
    /**
     * Update the content of one data field in the data_content table
     * @global object
     * @param int $submissionid
     * @param stdClass $value expected to hav all content specific properties
     * @return bool/int
     */
    function update_content($submissionid, $value, $name=''){
        global $DB;

        $content = new \stdClass();
        $content->fieldid = $this->field->id;
        $content->assignment = $this->assignid;
        $content->userid = $value->userid;
        $content->attemptnumber = $value->attemptnumber;
        $content->groupid = $value->groupid;
        
        foreach(array('', 1, 2, 3, 4) as $i) {
            if(isset($value->{"content$i"})) {
                $content->{"content$i"} = $value->{"content$i"};
            }
        }
        
        if ($oldcontent = $DB->get_record('local_assigndata_submission', 
                                    array('assignment' => $this->assignid, 'fieldid'=>$this->field->id, 'id'=>$submissionid))) {
            $content->id = $oldcontent->id;
            if($DB->update_record('local_assigndata_submission', $content)) {
                return -$content->id;
            }
        } else {
            return $DB->insert_record('local_assigndata_submission', $content);
        }
        return false;
    }

    /**
     * Delete all content associated with the field
     *
     * @global object
     * @param int $submissionid
     * @return bool
     */
    function delete_content($submissionid=0) {
        global $DB;

        $conditions = array('assignment' => $this->assignid,'fieldid'=>$this->field->id);
        if ($submissionid) {
            $conditions['id'] = $submissionid;
        }
        return $DB->delete_records('local_assigndata_submission', $conditions);
    }


    /**
     * Get the prettyfied name of the field.
     *
     * @return boolean - true if we added anything to the form
     */
    function get_formatted_fieldname() {
        $fieldname = $this->field->name;
        if($this->field->description) {
            $inner = '<br />('. $this->field->description.')';
            $fieldname .= \html_writer::span($inner, ' form-shortname');
        }
        return $fieldname;
    }
    
    
    /**
     * Get any additional fields for the submission form for this assignment.
     *
     * @param MoodleQuickForm $mform - This is the form
     * @param stdClass $content - This is the field  content record 
     * @return boolean - true if we added anything to the form
     */
    function add_common_form_elements($mform) {
    
        $prefix = 'field_'.$this->field->id.'_';
        
        $fieldname = $this->get_formatted_fieldname();
        
        $mform->addElement('hidden', 'field_'.$this->field->id , $this->field->id);
        $mform->setType('field_'.$this->field->id, PARAM_INT);
        
        return array($prefix, $fieldname);
    }
    

    /**
     * Get the prettyfied content of the field.
     *
     * @return string representation of field data
     */
    function get_formatted_content($submissionid) {   
        $content = ''; 
        if($this->get_content($submissionid)) {
            if(is_string($this->content->content)) {
                $content = str_replace('##', "<br />\n", $this->content->content);
            }
        }
        return $content; 
    }
    
    /**
     * Get the prettyfied content of the field.
     *
     * @return boolean - true if we added anything to the form
     */
    function get_summary_content($submissionid) {   
        $content = ''; 
        if($this->get_content($submissionid)) {
            $content = $this->field->name;
            $content .= ': ';
            $content .=$this->export_text_value($this->content);
        }
        return $content; 
    }
    
    
    /**
     * Per default, return the record's text value only from the "content" field.
     * Override this in fields class if necesarry.
     *
     * @param string $content
     * @return string
     */
    function export_text_value($content) {
        $str = '';
        if ($this->text_export_supported() && $content) {
            $str = str_replace('##', ", ", $content->content);
            if($content->content1 && $this->type != 'url') {
                $str .= ', '.$content->content1;
            }
        }
        return $str; 
    }
    
}
