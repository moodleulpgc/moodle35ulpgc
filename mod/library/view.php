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

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$l  = optional_param('l', 0, PARAM_INT);

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
$PAGE->set_title(format_string($library->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

//echo $OUTPUT->header();
//echo $OUTPUT->footer();

$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'mod_library', 'content', 0, 'sortorder DESC, id ASC', false); // TODO: this is not very efficient!!
if (count($files) < 1) {
    library_print_filenotfound($library, $cm, $course);
    die;
} else {
    $file = reset($files);
    unset($files);
}

$library->mainfile = $file->get_filename();
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
    $path = '/'.$context->id.'/mod_library/content/'.$library->revision.$file->get_filepath().$file->get_filename();
    $fullurl = moodle_url::make_file_url('/pluginfile.php', $path, $displaytype == RESOURCELIB_DISPLAY_DOWNLOAD);
    redirect($fullurl);
}

switch ($displaytype) {
    case RESOURCELIB_DISPLAY_EMBED:
        library_display_embed($library, $cm, $course, $file);
        break;
    case RESOURCELIB_DISPLAY_FRAME:
        library_display_frame($library, $cm, $course, $file);
        break;
    default:
        library_print_workaround($library, $cm, $course, $file);
        break;
}



