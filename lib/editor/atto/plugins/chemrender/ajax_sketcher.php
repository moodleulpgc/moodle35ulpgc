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
 * Returns rendered ChemDoodle Sketcher previews using the active ChemRender filter.
 *
 * Compression of the preview text is required to avoid the "Request-URI Too Large" error for very large molecules.
 *
 * @package    atto_chemrender
 * @copyright  2016 UC Regents
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../../../../config.php');

global $CFG;
$wwwroot = $CFG->wwwroot;

$contextid = required_param('contextid', PARAM_INT);

list($context, $course, $cm) = get_context_info_array($contextid);
$PAGE->set_url($wwwroot . '/lib/editor/atto/plugins/chemrender/ajax_sketcher.php');
$PAGE->set_context($context);

require_login($course, false, $cm);
require_sesskey();

$action = required_param('action', PARAM_ALPHA);

if ($action === 'filtertext') {
    $paramstring = required_param('paramstring', PARAM_RAW);
    $sketcheroutput = required_param('sketcheroutput', PARAM_RAW);
    $compressed = base64_encode(gzencode($sketcheroutput));
    $text = '<a href="' . $paramstring . '&sketcheroutput=true">' . $compressed . '</a>';
    $result = filter_manager::instance()->filter_text($text, $context);
    echo $OUTPUT->header();
    echo $result;
    die();
}

print_error('invalidarguments');
