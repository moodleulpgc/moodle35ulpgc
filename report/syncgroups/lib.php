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
 * Library code for the synchronize groups report
 *
 * @package   report_syncgroups
 * @copyright 2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_syncgroups_extend_navigation_course($navigation, $course, $context) {
    global $CFG, $OUTPUT;
    if (has_capability('report/syncgroups:view', $context)) {
        $url = new moodle_url('/report/syncgroups/index.php', array('id'=>$course->id));
        $navigation->add(get_string( 'syncgroups', 'report_syncgroups' ),
             $url, navigation_node::TYPE_SETTING,
                null, 'report_syncgroups', new pix_icon('i/report', ''));
    }
}

/**
* Return a list of page types
* @param string $pagetype current page type
* @param stdClass $parentcontext Block's parent context
* @param stdClass $currentcontext Current context of block
* @return array
*/
function report_syncgroups_page_type_list($pagetype, $parentcontext, $currentcontext) {
    return array(
        '*'                       => get_string('page-x', 'pagetype'),
        'report-*'                => get_string('page-report-x', 'pagetype'),
        'report-syncgroups-index' => get_string('page-report-syncgroups-index',  'report_syncgroups'),
    );
}

function report_syncgroups_cron() {
    global $CFG;
    include_once($CFG->dirroot.'/report/syncgroups/locallib.php');
    syncgroups_sync();

    mtrace(" report_syncgroups_cron ...");
}
