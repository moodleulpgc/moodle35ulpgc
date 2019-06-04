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
 * Base class for library source plugins.
 *
 * @package   mod_library
 * @copyright 2019 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Base class for library source plugins.
 *
 * Doesn't do anything on it's own -- it needs to be extended.
 * This class displays library sources.  Because it is called from
 * within /mod/library/source.php you can assume that the page header
 * and footer are taken care of.
 *
 * This file can refer to itself as source.php to pass variables
 * to itself - all these will also be globally available.  You must
 * pass "id=$cm->id" or q=$library->id", and "mode=sourcename".
 *
 * @copyright 2019 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class library_source_base {
    const NO_GROUPS_ALLOWED = -2;

    /**
     * Override this function to displays the source.
     * @param $cm the course-module for this library.
     * @param $course the coures we are in.
     * @param $library this library.
     */
    public abstract function display($cm, $course, $library);

    /**
     * Initialise some parts of $PAGE and start output.
     *
     * @param object $cm the course_module information.
     * @param object $coures the course settings.
     * @param object $library the library settings.
     * @param string $sourcemode the source name.
     */
    public function print_header_and_tabs($cm, $course, $library, $sourcemode = 'overview') {
        global $PAGE, $OUTPUT;

        // Print the page header.
        $PAGE->set_title($library->name);
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();
        $context = context_module::instance($cm->id);
        echo $OUTPUT->heading(format_string($library->name, true, array('context' => $context)));
    }

    /**
     * Get the current group for the user user looking at the source.
     *
     * @param object $cm the course_module information.
     * @param object $coures the course settings.
     * @param context $context the library context.
     * @return int the current group id, if applicable. 0 for all users,
     *      NO_GROUPS_ALLOWED if the user cannot see any group.
     */
    public function get_current_group($cm, $course, $context) {
        $groupmode = groups_get_activity_groupmode($cm, $course);
        $currentgroup = groups_get_activity_group($cm, true);

        if ($groupmode == SEPARATEGROUPS && !$currentgroup && !has_capability('moodle/site:accessallgroups', $context)) {
            $currentgroup = self::NO_GROUPS_ALLOWED;
        }

        return $currentgroup;
    }
}
