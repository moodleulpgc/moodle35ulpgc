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
 * Moodle interface library for masks
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// this is lib.php - add code here for interfacing this module to Moodle internals

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in masks module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function masks_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return false;
        case FEATURE_MOD_INTRO:               return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return false;

        default: return null;
    }
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function masks_reset_userdata($data) {
    global $DB;
    // Delete records from user_state
    $subSelect = "SELECT q.id FROM {course_modules} cm JOIN {masks_question} q ON q.parentcm=cm.id WHERE cm.course = ?";
    $DB->delete_records_select('masks_user_state', "question IN ($subSelect)", array($data->courseid));
    return array();
}

/**
 * Add masks instance.
 * @param object $data
 * @param object $mform
 * @return int new url instance id
 */
function masks_add_instance($data, $mform) {
    global $CFG, $DB;
    // write new record to the database
    $data->id = $DB->insert_record('masks', $data);

    // add the 2 grading columns to the grade book
    require_once($CFG->libdir.'/gradelib.php');
    $graderesult = grade_update('mod/masks', $data->course, 'mod', 'masks', $data->id, 0, null, array( 'itemname' => $data->name ) );
    if ( $graderesult != GRADE_UPDATE_OK ){
        throw new \moodle_exception('Failed to set gradebook meta data: for new masks instance');
    }

    return $data->id;
}

/**
 * Update masks instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function masks_update_instance($data, $mform) {
    global $CFG, $DB;

    $parameters = array();
    for ($i = 0; $i < 100; $i++) {
        $parameter = "parameter_$i";
        $variable  = "variable_$i";
        if (empty($data->$parameter) or empty($data->$variable)) {
            continue;
        }
        $parameters[$data->$parameter] = $data->$variable;
    }
    $data->parameters = serialize($parameters);

    $data->timemodified = time();
    $data->id           = $data->instance;

    $DB->update_record('masks', $data);

    // update the 2 grading columns for the grade book
    require_once($CFG->libdir.'/gradelib.php');
    $graderesult = grade_update('mod/masks', $data->course, 'mod', 'masks', $data->id, 0, null, array( 'itemname' => $data->name ) );
    if ( $graderesult != GRADE_UPDATE_OK ){
        echo "Grade Result: $graderesult\n";
        echo "Data:\n";
        print_r($data);
        throw new \moodle_exception('Failed to set gradebook meta data: for new masks instance');
    }

    return true;
}

/**
 * Delete masks instance.
 * @param int $id
 * @return bool true
 */
function masks_delete_instance($id) {
    global $DB;

    if (!$instance = $DB->get_record('masks', array('id' => $id))) {
        return false;
    }

    if (!$cm = get_coursemodule_from_instance('masks', $id)) {
        return false;
    }

    // Delete records from question, mask and user_state
    $subSelect = "SELECT id FROM {masks_question} WHERE parentcm = ?";
    $DB->delete_records_select('masks_user_state', "question IN ($subSelect)", array($cm->id));
    $DB->delete_records_select('masks_mask', "question IN ($subSelect)", array($cm->id));
    $DB->delete_records('masks_question', array('parentcm' => $cm->id));

    // Delete records from doc_page, page and doc
    $subSelect = "SELECT id FROM {masks_doc} WHERE parentcm = ?";
    $DB->delete_records('masks_page', array('parentcm' => $cm->id));
    $DB->delete_records_select('masks_doc_page', "doc IN ($subSelect)", array($cm->id));
    $DB->delete_records('masks_doc', array('parentcm' => $cm->id));

    // note: all context files are deleted automatically
    $DB->delete_records('masks', array('id' => $id));

    return true;
}

/**
 * Serve image files
 *
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function mod_masks_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login( $course, true, $cm );

    $relativepath = implode( '/', $args );
    $fullpath = "/$context->id/mod_masks/masks_doc_page/$relativepath";

    $fs = get_file_storage();
    $file = $fs->get_file_by_hash( sha1( $fullpath ) );
    if ( !$file or $file->is_directory() ) {
        if ($filearea === 'content') { //return file not found straight away to improve performance.
            send_header_404();
            die;
        }
        return false;
    }

    // finally send the file
    $lifetime = null;
    send_stored_file($file, $lifetime, 0, false, $options);
}

