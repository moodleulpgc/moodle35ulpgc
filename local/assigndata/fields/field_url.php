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
 * Plugin local_assigndata class url
 *
 * @package     local_assigndata
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 
namespace local_assigndata;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot.'/mod/data/field/url/field.class.php');

/**
 * The field url class
 *
 * @package    local_assigndata
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_url extends \data_field_url {

use field_base;

    /**
     * Get any additional fields for the submission form for this assignment.
     *
     * @param MoodleQuickForm $mform - This is the form
     * @param stdClass $content - This is the field  content record 
     * @return boolean - true if we added anything to the form
     */
    function add_submission_form_elements($mform, $content = '') {
        global $CFG, $PAGE;

        require_once($CFG->dirroot. '/repository/lib.php'); // necessary for the constants used in args

        $args = new \stdClass();
        $args->accepted_types = '*';
        $args->return_types = FILE_EXTERNAL;
        $args->context = $this->context;
        $args->env = 'url';
        $fp = new \file_picker($args);
        $options = $fp->options;
    
        // add hidden and common elements 
        list($prefix, $fieldname) = $this->add_common_form_elements($mform);
        
        $autolinkable = !empty($this->field->param1) && empty($this->field->param2);
        
        $grouparr = array();
            $grouparr[] = $mform->createElement('text', $prefix.'content', '', array('size'=>60));
            if($autolinkable) {
                $grouparr[] = $mform->createElement('text', $prefix.'content1', '', array('size'=>60));
            }
        $mform->addGroup($grouparr, $prefix.'gcontent', $fieldname, array(' <br />  '.get_string('urltext', 'local_assigndata')), false);        
        

        $mform->setType($prefix.'content', PARAM_URL);
        $mform->setDefault($prefix.'content', $content->content);
        if($this->field->required) {
            $mform->addGroupRule($prefix.'gcontent', array($prefix.'content' => array(array(null, 'required', null, 'client'))));
            $mform->addRule($prefix.'gcontent', null, 'required', null, 'client');
        }
        if (count($options->repositories) > 0) {
            // ecastro ULPGC,  do not allo to do anythig really useful
            /*
            $mform->addElement('button', $prefix.'button', get_string('choosealink', 'repository'), 
                                    array('id'=> 'filepicker-button-'.$options->client_id, 
                                            'class' => 'visibleifjs'));
            
            $module = array('name'=>'data_urlpicker', 'fullpath'=>'/mod/data/data.js', 'requires'=>array('core_filepicker'));
            $PAGE->requires->js_init_call('M.data_urlpicker.init', array($options), true, $module);
            */
        }
        
        if($autolinkable) {
            $mform->setType($prefix.'content1', PARAM_TEXT);
            $mform->setDefault($prefix.'content1', $content->content1);
            if($this->field->required) {
                $mform->addRule($prefix.'content1', null, 'required', null, 'client');
            }
        }
        
        return true;
    }
    
    /**
     * Get the prettyfied content of the field.
     *
     * @return boolean - true if we added anything to the form
     */
    function get_formatted_content($submissionid) {   
        $str = ''; 
        if($this->get_content($submissionid)) {
        
            $content = $this->content->content;
            $url = empty($this->content->content)? '' : $this->content->content;
            $text = empty($this->content->content1)? '' : $this->content->content1;

            // this is copied from data/url field
            if (empty($url) or ($url == 'http://')) {
                return '';
            }
            if (!empty($this->field->param2)) {
                // param2 forces the text to something
                $text = $this->field->param2;
            }
            if ($this->field->param1) {
                // param1 defines whether we want to autolink the url.
                $attributes = array();
                if ($this->field->param3) {
                    // param3 defines whether this URL should open in a new window.
                    $attributes['target'] = '_blank';
                    $attributes['rel'] = 'noreferrer';
                }

                if (empty($text)) {
                    $text = $url;
                }

                $str = \html_writer::link($url, $text, $attributes);
            } else {
                $str = $url;
            }
        }
        return $str; 
    }
    
}
