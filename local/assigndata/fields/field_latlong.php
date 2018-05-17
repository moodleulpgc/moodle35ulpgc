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
 * Plugin local_assigndata class latlong
 *
 * @package     local_assigndata
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 
namespace local_assigndata;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot.'/mod/data/field/latlong/field.class.php');

/**
 * The field latlong class
 *
 * @package    local_assigndata
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_latlong extends \data_field_latlong {

use field_base;

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

        $attributes = array('size'=>'12'); 
        $grouparr = array();
        $grouparr[] = $mform->createElement('static', $prefix.'precontent', '', get_string('latitude', 'data'));  
        $grouparr[] = $mform->createElement('text', $prefix.'content', '', $attributes);
        $grouparr[] = $mform->createElement('static', $prefix.'precontent1', '', get_string('longitude', 'data'));  
        $grouparr[] = $mform->createElement('text', $prefix.'content1', '', $attributes);
        $grouparr[] = $mform->createElement('static', $prefix.'aftercontent1', '', '');  
        $mform->addGroup($grouparr, $prefix.'gcontent', $fieldname, array('  ', '°N  &nbsp;&nbsp; ', '', '°E'), false);  
        $mform->addGroupRule($prefix.'gcontent', array($prefix.'content' => array(array(null, 'numeric', null, 'client')),
                                               $prefix.'content1' => array(array(null, 'numeric', null, 'client')),));
        $mform->setDefault($prefix.'content', $content->content);
        $mform->setType($prefix.'content', PARAM_TEXT);
        $mform->setDefault($prefix.'content1', $content->content1);
        $mform->setType($prefix.'content1', PARAM_TEXT);
        
        if($this->field->required) {
            $mform->addGroupRule($prefix.'gcontent', array($prefix.'content' => array(array(null, 'required', null, 'client')),
                                                    $prefix.'content1' => array(array(null, 'required', null, 'client')),));
           $mform->addRule($prefix.'gcontent', null, 'required', null, 'client');
        }
        
        return true;
    }
    
    /**
     * Get the prettyfied content of the field.
     *
     * @return boolean - true if we added anything to the form
     */
    function get_formatted_content($submissionid) {   
        global $CFG, $DB;

        $str = '';
        
        if($content = $this->get_content($submissionid)) {

            $this->linkoutservices['Google Earth'] = "@wwwroot@/mod/assign/submission/data/kml.php?id=@cmid@&fid=@fieldid@&sid=@subid@";
            $content->submission = $DB->get_field('assign_submission', 'id', array('assignment' =>$content->assignment, 
                                                                                    'userid' => $content->userid, 
                                                                                    'attemptnumber' => $content->attemptnumber,
                                                                                    'groupid' => $content->groupid));
    
            // code copied from data field 
            $lat = $content->content;
            if (strlen($lat) < 1) {
                return false;
            }
            $long = $content->content1;
            if (strlen($long) < 1) {
                return false;
            }
            // We use format_float to display in the regional format.
            if($lat < 0) {
                $compasslat = format_float(-$lat, 4) . '°S';
            } else {
                $compasslat = format_float($lat, 4) . '°N';
            }
            if($long < 0) {
                $compasslong = format_float(-$long, 4) . '°W';
            } else {
                $compasslong = format_float($long, 4) . '°E';
            }

            // Now let's create the jump-to-services link
            $servicesshown = explode(',', $this->field->param1);

            // These are the different things that can be magically inserted into URL schemes
            $urlreplacements = array(
                '@lat@'=> $lat,
                '@long@'=> $long,
                '@wwwroot@'=> $CFG->wwwroot,
                '@contentid@'=> $content->id,
                '@cmid@'=> $this->cm->id,
                '@courseid@'=> $this->courseid,
                '@fieldid@'=> $content->fieldid,
                '@subid@'=> $content->submission,
            );

            if(sizeof($servicesshown)==1 && $servicesshown[0]) {
                $str = " <a href='"
                          . str_replace(array_keys($urlreplacements), array_values($urlreplacements), $this->linkoutservices[$servicesshown[0]])
                          ."' title='$servicesshown[0]'>$compasslat $compasslong</a>";
            } elseif (sizeof($servicesshown)>1) {
                $str = '<form id="latlongfieldbrowse">';
                $str .= "$compasslat, $compasslong\n";
                $str .= ' &nbsp;  ';
                $str .= "<label class='accesshide' for='jumpto'>". get_string('jumpto') ."</label>";
                $str .= "<select id='jumpto' name='jumpto'>";
                foreach($servicesshown as $servicename){
                    // Add a link to a service
                    $str .= "\n  <option value='"
                               . str_replace(array_keys($urlreplacements), array_values($urlreplacements), $this->linkoutservices[$servicename])
                               . "'>".htmlspecialchars($servicename)."</option>";
                }
                // NB! If you are editing this, make sure you don't break the javascript reference "previousSibling"
                //   which allows the "Go" button to refer to the drop-down selector.
                $str .= "\n</select><input type='button' value='" . get_string('go') . "' onclick='if(previousSibling.value){self.location=previousSibling.value}'/>";
                $str .= '</form>';
            } else {
                $str = "$compasslat, $compasslong";
            }
        }   
        return $str;
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
            $str = $content->content.'N, ';
            $str .= $content->content1.'E';
        }
        return $str; 
    }
    
    
}
