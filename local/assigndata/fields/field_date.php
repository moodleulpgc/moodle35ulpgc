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
 * Plugin local_assigndata class date
 *
 * @package     local_assigndata
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 
namespace local_assigndata;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot.'/mod/data/field/date/field.class.php');

/**
 * The field date class
 *
 * @package    local_assigndata
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_date extends \data_field_date {

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

        $mform->addElement('date_time_selector', $prefix.'content', $fieldname);
        $mform->setDefault($prefix.'content', $content->content);
        if($this->field->required) {
            $mform->addRule($prefix.'content', null, 'required', null, 'client');
        }
       
        return true;
    }

}
