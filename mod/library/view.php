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
 * Prints an instance of mod_library.
 *
 * @package     mod_library
 * @copyright   2019 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/mod/library/locallib.php');
//require_once($CFG->libdir.'/completionlib.php');

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$l  = optional_param('l', 0, PARAM_INT);

$redirect = optional_param('redirect', 0, PARAM_BOOL);
$forceview = optional_param('forceview', 0, PARAM_BOOL);


if ($id) {
    list($course, $cm) = get_course_and_cm_from_cmid($id, 'library');
    $library = $DB->get_record('library', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($l) {
    $library = $DB->get_record('library', array('id' => $n), '*', MUST_EXIST);
    list($course, $cm) = get_course_and_cm_from_instance($library, 'library');
} else {
    print_error(get_string('missingidandcmid', mod_library));
}


require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/library:view', $context);

// Completion and trigger events.
library_view($library, $course, $cm, $context);

$PAGE->set_url('/mod/library/view.php', array('id' => $cm->id));
$PAGE->set_title($course->shortname.': '.$library->name);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_activity_record($library);

$target = null;
$parameters = library_parameter_value_mapping($library, $cm, $course);
$source = library_get_source_plugin($library, $parameters);
$source->get_processed_searchpattern($parameters);
$target = $source->get_source_content($context->id, $library->id);

//print_object($target);

/*
if($repository = library_get_repository($library)) {
    $parameters = library_parameter_value_mapping($library, $cm, $course);
    $target = library_get_source_content($library, $repository, $parameters, $context->id);
}
*/
$output = $PAGE->get_renderer('mod_library');

if (!$target) {
    //$filename = ($library->pathname) ? $library->pathname.'/' : '';
    //$filename = ($library->pathname) ? $library->pathname.'/' : '';
    //$filename .= str_replace(array_keys($parameters),array_values($parameters), $library->searchpattern);
    $filename = ($source->pathname) ? $source->pathname.'/' : '';
    $filename .= $source->searchpattern;
    
    $output->print_filenotfound($filename);
    die;
}

if($library->displaymode == LIBRARY_DISPLAYMODE_FILE) {
    $library->mainfile = $target->get_filename();
    $displaytype = library_get_final_display_type($library);
    if ($displaytype == RESOURCELIB_DISPLAY_OPEN || $displaytype == RESOURCELIB_DISPLAY_DOWNLOAD) {
        $redirect = true;
    }

    // Don't redirect teachers, otherwise they can not access course or module settings.
    if ($redirect && !course_get_format($course)->has_view_page() &&
            (has_capability('moodle/course:manageactivities', $context) ||
            has_capability('moodle/course:update', context_course::instance($course->id)))) {
        $redirect = false;
    }

    if ($redirect && !$forceview) {
        // coming from course page or url index page
        // this redirect trick solves caching problems when tracking views ;-)
        $path = '/'.$context->id.'/mod_library/content/'.$library->id.$target->get_filepath().$target->get_filename();
        $fullurl = moodle_url::make_file_url('/pluginfile.php', $path, $displaytype == RESOURCELIB_DISPLAY_DOWNLOAD);
        redirect($fullurl);
    }

    switch ($displaytype) {
        case RESOURCELIB_DISPLAY_EMBED:
            $output->print_embed($target);
            break;
        case RESOURCELIB_DISPLAY_FRAME:
            $output->print_in_frame($target);
            break;
        default:
            $output->print_workaround($target);
            break;
    }
} else {
    $output->print_folder($target);
}



