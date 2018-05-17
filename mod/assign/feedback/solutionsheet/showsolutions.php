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
 * This file contains an action handler for showing/hiding solution sheets.
 *
 * @package   assignfeedback_solutionsheet
 * @copyright 2016 Henning Bostelmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');

$cmid    = required_param('cmid', PARAM_INT);    // Course module ID.
$showit  = required_param('show', PARAM_BOOL);   // Show solutions or hide them.

$cm = get_coursemodule_from_id('assign', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

$paras = array('assignment' => $cm->instance,
                'subtype' => 'assignfeedback',
                'plugin' => 'solutionsheet',
                'name' => 'showattype');
$conf = $DB->get_record('assign_plugin_config', $paras, '*', MUST_EXIST);

require_login($course->id, false, $cm);
require_sesskey();

$context = context_module::instance($cm->id);
require_capability('moodle/course:manageactivities', $context);

// OK, all security checks passed, now show/hide the solution sheet.

$conf->value = $showit ? 1 : 0;

$DB->update_record('assign_plugin_config', $conf);

redirect(new moodle_url('/mod/assign/view.php', array('id' => $cm->id)), get_string('changessaved'));
