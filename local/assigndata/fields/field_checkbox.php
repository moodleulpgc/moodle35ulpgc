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
 * Plugin local_assigndata class checkbox.
 *
 * @package     local_assigndata
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
namespace local_assigndata;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot.'/mod/data/field/checkbox/field.class.php');


/**
 * The field checkbox class.
 *
 * @package    local_assigndata
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_checkbox extends \data_field_checkbox {

use field_base {
    update_content as base_update_content;
}

    /**
     * Get any additional fields for the submission form for this assignment.
     *
     * @param MoodleQuickForm $mform - This is the form
     * @param stdClass $content - This is the field  content record 
     * @return boolean - true if we added anything to the form
     */
    function add_submission_form_elements($mform, $content = '') {

        // add hidden and common elements 
        list($prefix, $fieldname) = $this->add_common_form_elements($mform);

        $options = explode("\n",$this->field->param1);
        foreach($options as $i => $option) {
            $options[$i] = trim($option);
        }
        
        $grouparr = array();
        $separator = array();
        $checked = explode('##', $content->content);
        foreach($checked as $i => $option) {
            $checked[$i] = trim($option);
        }
        
        foreach($options as $idx => $checkbox) {
            $element = $mform->createElement('advcheckbox', 'checkbox_'.$idx, '', $checkbox,
                                                    array('group' => $prefix), array(0, $checkbox));
            $grouparr[] = $element;
            $separator[] = ' <br />';
        }
        $mform->addGroup($grouparr, $prefix.'content', $fieldname, $separator);
        
        foreach($checked as $check) {
            $k = array_search($check, $options);
            if($k !== false) {
                $mform->setDefault($prefix.'content[checkbox_'.$k.']', $check);
            }
        }
        
        if($this->field->required) {
            $mform->addRule($prefix.'content', null, 'required', null, 'client');
        }
        
        return true;
    }
    
    /**
     * Update the content of one data field in the data_content table
     * @global object
     * @param int $submissionid
     * @param stdClass $value expected to hav all content specific properties
     * @return bool/int
     */
    function update_content($submissionid, $value, $name=''){
        if(is_array($value->content)) {
            foreach($value->content as $key => $option) {
                if($option == '0') {
                    unset($value->content[$key]);
                }
            }
            
            if(is_array($value->content)) {
                $value->content = implode('##', $value->content);
            } else {
                $value->content = '';
            }
        }
        
        return $this->base_update_content($submissionid, $value, $name=''); 
    }

    
}
