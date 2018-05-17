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
 * This file contains the version information for the comments feedback plugin
 *
 * @package assignfeedback_wtpeer
 * @copyright  2016 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Serves assignment submissions and other files.
 *
 * @param mixed $course course or id of the course
 * @param mixed $cm course module or id of the course module
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool false if file not found, does not return if found - just send the file
 */
function assignfeedback_wtpeer_pluginfile($course,
                                          $cm,
                                          context $context,
                                          $filearea,
                                          $args,
                                          $forcedownload) {
    global $DB, $CFG, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, false, $cm);
    $itemid = (int)array_shift($args);
    $record = $DB->get_record('assign_submission',
                              array('id'=>$itemid),
                              'userid, assignment, groupid, attemptnumber',
                              MUST_EXIST);
    $userid = $record->userid;
    $groupid = $record->groupid;
    
    require_once($CFG->dirroot . '/mod/assign/locallib.php');

    $assign = new assign($context, $cm, $course);

    
    if ($assign->get_instance()->id != $record->assignment) {
        return false;
    }

    if ($assign->get_instance()->teamsubmission &&
        !$assign->can_view_group_submission($groupid)) {
            $members = $assign->get_submission_group_members($groupid, true);
            foreach($members as $key => $user) {
                $members[$key] = $user->id;
            }
            
            list($insql, $params) = $DB->get_in_or_equal($members);
            $params[] = $record->attemptnumber;
            $params[] = $record->assignment;
            $params[] = $USER->id;
            
            $sql = "SELECT wta.id 
                    FROM {assign_submission} s 
                    JOIN {assignfeedback_wtpeer_allocs} wta ON wta.submission = s.id AND wta.userid = s.userid 
                    WHERE s.userid $insql AND s.attemptnumber = ? AND s.assignment = ? AND wta.grader = ?";
            $isgrader = $DB->record_exists_sql($sql, $params);
            
            if(!$isgrader) {
                return false;
            }
    }

    if (!$assign->get_instance()->teamsubmission &&
        !$assign->can_view_submission($userid)) {
            $params = array('submission'=>$itemid, 'grader'=>$USER->id);
            if(!$DB->record_exists('assignfeedback_wtpeer_allocs', $params)) {
                return false;
        }
    }

    $relativepath = implode('/', $args);

    $fullpath = "/{$context->id}/assignsubmission_file/$filearea/$itemid/$relativepath";

    $fs = get_file_storage();
    if (!($file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
        return false;
    }

    // Download MUST be forced - security!
    send_stored_file($file, 0, 0, true);
}
