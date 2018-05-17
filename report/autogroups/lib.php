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
 * @package   report_autogroups
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
function report_autogroups_extend_navigation_course($navigation, $course, $context) {
    global $CFG, $OUTPUT;
    if (has_capability('report/autogroups:view', $context)) {
        $url = new moodle_url('/report/autogroups/index.php', array('id'=>$course->id));
        $navigation->add(get_string( 'autogroups', 'report_autogroups' ),
             $url, navigation_node::TYPE_SETTING,
                null, 'report_autogroups', new pix_icon('i/report', ''));
    }
}

/**
* Return a list of page types
* @param string $pagetype current page type
* @param stdClass $parentcontext Block's parent context
* @param stdClass $currentcontext Current context of block
* @return array
*/
function report_autogroups_page_type_list($pagetype, $parentcontext, $currentcontext) {
    return array(
        '*'                       => get_string('page-x', 'pagetype'),
        'report-*'                => get_string('page-report-x', 'pagetype'),
        'report-autogroups-index' => get_string('page-report-autogroups-index',  'report_autogroups'),
    );
}

function report_autogroups_cron() {
    global $CFG;
    include_once($CFG->dirroot.'/report/autogroups/locallib.php');
    autogroups_sync();

    mtrace(" report_autogroups_cron ...");
}

/**
    * Called whenever anybody tries (from the normal interface) to remove a group
    * member which is registered as being created by this component. (Not called
    * when deleting an entire group or course at once.)
    * @param int $itemid Item ID that was stored in the group_members entry
    * @param int $groupid Group ID
    * @param int $userid User ID being removed from group
    * @return bool True if the remove is permitted, false to give an error
    */
function report_autogroups_allow_group_member_remove($itemid, $groupid, $userid) {
    return false;
}

