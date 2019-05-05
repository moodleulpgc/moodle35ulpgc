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
 * videolib of interface functions and constants.
 *
 * @package     mod_videolib
 * @copyright   2018 Enrique Castro @ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function videolib_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function videolib_reset_userdata($data) {

    // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
    // See MDL-9367.

    return array();
}

/**
 * Saves a new instance of the mod_videolib into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $data An object from the form.
 * @param mod_videolib_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function videolib_add_instance($data, $mform = null) {
    global $DB;

    $cmid = $data->coursemodule;
    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    
    $data->displayoptions = videolib_set_displayoptions($data);
    
    $data->parameters = videolib_set_parameters($data);

    $data->id = $DB->insert_record('videolib', $data);

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($cmid, 'videolib', $data->id, $completiontimeexpected);
    
    return $data->id;
}

/**
 * Updates an instance of the mod_videolib in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $data An object from the form in mod_form.php.
 * @param mod_videolib_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function videolib_update_instance($data, $mform = null) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    $data->displayoptions = videolib_set_displayoptions($data);
    
    $data->parameters = videolib_set_parameters($data);

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($data->coursemodule, 'videolib', $data->id, $completiontimeexpected);

    return $DB->update_record('videolib', $data);
}

/**
 * Encodes display options based on form input.
 *
 * Shared code used by videolib_add_instance and videolib_update_instance.
 *
 * @param object $data Data object
 */
function videolib_set_displayoptions($data) {
    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (!empty($data->printheading)) {
        $displayoptions['printheading']   = (int)!empty($data->printheading);
    }
    if (!empty($data->printintro)) {
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    
    return serialize($displayoptions);
}

/**
 * Encodes parameters options based on form input.
 *
 * Shared code used by videolib_add_instance and videolib_update_instance.
 *
 * @param object $data Data object
 */
function videolib_set_parameters($data) {
    $parameters = array();
    for ($i=0; $i < 5; $i++) {
        $parameter = "parameter_$i";
        $variable  = "variable_$i";
        if (empty($data->$parameter) or empty($data->$variable)) {
            continue;
        }
        $parameters[$data->$parameter] = $data->$variable;
    }
    return serialize($parameters);
}


/**
 * Removes an instance of the mod_videolib from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function videolib_delete_instance($id) {
    global $DB;

    if (!$videolib = $DB->get_record('videolib', array('id'=>$id))) {
        return false;
    }
    
    $cm = get_coursemodule_from_instance('videolib', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'videolib', $id, null);

    // note: all context files are deleted automatically

    $DB->delete_records('videolib', array('id' => $id));

    return true;
}

/**
 * Called when viewing course page. Shows extra details after the link if
 * enabled.
 *
 * @param cm_info $cm Course module information
 */
function xxx_videolib_cm_info_view(cm_info $cm) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/videolib/locallib.php');

    $videolib = (object)array('displayoptions' => $cm->customdata);
    $details = videolib_get_optional_details($videolib, $cm);
    if ($details) {
        $cm->set_after_link(' ' . html_writer::tag('span', $details,
                array('class' => 'videoliblinkdetails')));
    }
}

/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}.
 *
 * @package     mod_videolib
 * @category    files
 *
 * @param stdClass $course.
 * @param stdClass $cm.
 * @param stdClass $context.
 * @return string[].
 */
function videolib_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for mod_videolib file areas.
 *
 * @package     mod_videolib
 * @category    files
 *
 * @param file_browser $browser.
 * @param array $areas.
 * @param stdClass $course.
 * @param stdClass $cm.
 * @param stdClass $context.
 * @param string $filearea.
 * @param int $itemid.
 * @param string $filepath.
 * @param string $filename.
 * @return file_info Instance or null if not found.
 */
function videolib_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function videolib_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-videolib-*'=>get_string('page-mod-videolib-x', 'videolib'));
    return $module_pagetype;
}

/**
 * Serves the files from the mod_videolib file areas.
 *
 * @package     mod_videolib
 * @category    files
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The mod_videolib's context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 */
function videolib_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);
    send_file_not_found();
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $videolib   videolib object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function videolib_view($videolib, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $videolib->id
    );

    $event = \mod_videolib\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('videolib', $videolib);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

/**
 * Check if the module has any update that affects the current user since a given time.
 *
 * @param  cm_info $cm course module data
 * @param  int $from the time to check updates from
 * @param  array $filter  if we need to check only specific updates
 * @return stdClass an object with the different type of areas indicating if they were updated or not
 * @since Moodle 3.2
 */
function videolib_check_updates_since(cm_info $cm, $from, $filter = array()) {
    $updates = course_check_module_updates_since($cm, $from, array('content'), $filter);
    return $updates;
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function videolib_core_calendar_provide_event_action(calendar_event $event,
                                                      \core_calendar\action_factory $factory, $userid = 0) {

    global $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    $cm = get_fast_modinfo($event->courseid, $userid)->instances['videolib'][$event->instance];

    $completion = new \completion_info($cm->get_course());

    $completiondata = $completion->get_data($cm, false, $userid);

    if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
        return null;
    }

    return $factory->create_instance(
        get_string('view'),
        new \moodle_url('/mod/videolib/view.php', ['id' => $cm->id]),
        1,
        true
    );
}

/**
 * Extends the global navigation tree by adding mod_videolib nodes if there is a relevant content.
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $videolibnode An object representing the navigation tree node.
 * @param stdClass $course.
 * @param stdClass $module.
 * @param cm_info $cm.
 */
function videolib_extend_navigation($videolibnode, $course, $module, $cm) {
}

/**
 * Extends the settings navigation with the mod_videolib settings.
 *
 * This function is called when the context for the page is a mod_videolib module.
 * This is not called by AJAX so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $videolibnode {@link navigation_node}
 */
function videolib_extend_settings_navigation($settingsnav, $videolibnode = null) {
}
