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
 * @package availability_timeelapsed
 * @copyright 2015 Enrique Castro @ ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_timeelapsed;

defined('MOODLE_INTERNAL') || die();

/**
 * Front-end class.
 *
 * @package availability_timeelapsed
 * @copyright 2015 Enrique Castro @ ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontend extends \core_availability\frontend {
    protected function get_javascript_strings() {
        return array('op_greater', 'op_atleast', 'op_equal', 'op_less', 'op_atmost',
                        'conditiontitle', 'label_operator', 'label_value', 'days');
    }

    protected function get_javascript_init_params($course, \cm_info $cm = null,
            \section_info $section = null) {
        // Standard user fields.
        $standardfields = array(
            'coursestart' => get_string('coursestart'),
            'firstaccess' => get_string('firstsiteaccess'),
            'lastaccess' => get_string('lastsiteaccess'),
            'lastcourseaccess' => get_string('lastcourseaccess', 'availability_timeelapsed'),
            'currentcourseaccess' => get_string('currentcourseaccess', 'availability_timeelapsed'),
            'lastlogin' => get_string('lastlogin', 'availability_timeelapsed'),
            'currentlogin' => get_string('currentlogin', 'availability_timeelapsed'),
        );
        \core_collator::asort($standardfields);

        // Make arrays into JavaScript format (non-associative, ordered) and return.
        return array(self::convert_associative_array_for_js($standardfields, 'field', 'display'));
    }
}