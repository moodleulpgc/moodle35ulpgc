<?PHP
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
 * This file contains the moodle hooks for the submission comments plugin
 *
 * @package   assignsubmission_peers
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/** config.php */
//require_once('../../../../config.php');
/** Include locallib.php */
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/assign/submission/peers/locallib.php');


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
function assignsubmission_peers_pluginfile($course, $cm, context $context, $filearea, $args, $forcedownload) {
    global $USER, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, false, $cm);
    $itemid = (int)array_shift($args);
    $record = $DB->get_record('assign_submission', array('id'=>$itemid), 'userid, assignment', MUST_EXIST);
    $userid = $record->userid;

    if (!$assign = $DB->get_record('assign', array('id'=>$cm->instance))) {
        return false;
    }

    if ($assign->id != $record->assignment) {
        return false;
    }
    $assign = new assign($context,$cm,$course);
    $plugin = new assign_submission_peers($assign, 'peers'); //$assign->get_plugin_by_type('assignsubmission', 'peers');
    $restricted = $plugin->view_peers_restricted($USER->id);

    // check is users submission or has grading permission
    if ($USER->id != $userid and !has_capability('mod/assign:grade', $context) and $restricted) {
        return false;
    }

    $relativepath = implode('/', $args);

    $fullpath = "/{$context->id}/assignsubmission_file/$filearea/$itemid/$relativepath";

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    send_stored_file($file, 0, 0, true); // download MUST be forced - security!
}







/**
 *
 * Callback method for data validation---- required method for AJAXmoodle based comment API
 *
 * @param stdClass $options
 * @return bool
 */

 /*
function assignsubmission_peers_comment_validate(stdClass $options) {

    return true;
}

/**
 * Permission control method for submission plugin ---- required method for AJAXmoodle based comment API
 *
 * @param stdClass $options
 * @return array
 */

 /*
function assignsubmission_peers_comment_permissions(stdClass $options) {

    return array('post' => true, 'view' => true);
}

// /**
//  * Callback to force the userid for all comments to be the userid of the submission and NOT the global $USER->id. This
//  * is required by the upgrade code. Note the comment area is used to identify upgrades.
//  *
//  * @param stdClass $comment
//  * @param stdClass $param
//  */
// function assignsubmission_peers_comment_add(stdClass $comment, stdClass $param) {
//
//     global $DB;
//     if ($comment->commentarea == 'submission_peers_upgrade') {
//         $submissionid = $comment->itemid;
//         $submission = $DB->get_record('assign_submission', array('id' => $submissionid));
//
//         $comment->userid = $submission->userid;
//         $comment->commentarea = 'submission_peers';
//     }
// }

