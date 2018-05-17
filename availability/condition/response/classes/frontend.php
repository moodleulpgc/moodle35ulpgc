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
 * Front-end class.
 *
 * @package availability_response
 * @copyright 2015 Enrique Castro @ ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_response;

defined('MOODLE_INTERNAL') || die();

/**
 * Front-end class.
 *
 * @package availability_response
 * @copyright 2015 Enrique Castro @ ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontend extends \core_availability\frontend {
    protected function get_javascript_strings() {
        return array('op_contains', 'op_doesnotcontain', 'op_endswith', 'op_isequalto', 'op_startswith',
                        'conditiontitle', 'label_operator', 'label_value');
    }

    protected function get_javascript_init_params($course, \cm_info $cm = null,
            \section_info $section = null) {
        global $DB;
        // Standard user fields.
        $choices = $DB->get_records_menu('choice', array('course'=>$course->id), 'name ASC', 'id,name');
        if($cm && ($cm->modname == 'choice')) {
            unset($choices[$cm->instance]);
        }
        // Make arrays into JavaScript format (non-associative, ordered) and return.
        return array(self::convert_associative_array_for_js($choices, 'field', 'display'));
    }
}