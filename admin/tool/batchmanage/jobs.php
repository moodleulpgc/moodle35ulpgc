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
 * batchmanageging managejob management.
 *
 * @package    tool_batchmanage
 * @copyright  2016 Enriue Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$action = required_param('action', PARAM_ALPHANUMEXT);
$managejob = required_param('managejob', PARAM_PLUGIN);

$PAGE->set_url('/admin/tool/batchmanage/jobs.php');
$PAGE->set_context(context_system::instance());

require_login();
require_capability('tool/batchmanage:manage', context_system::instance());
require_sesskey();

$all = \core_plugin_manager::instance()->get_installed_plugins('managejob');
$enabled = get_config('tool_batchmanage', 'enabled_managejobs');
if (!$enabled) {
    $enabled = array();
} else {
    $enabled = array_flip(explode(',', $enabled));
}

$return = new moodle_url('/admin/settings.php', array('section' => 'managejobs'));

$syscontext = context_system::instance();

switch ($action) {
    case 'disable':
        unset($enabled[$managejob]);
        set_config('enabled_managejobs', implode(',', array_keys($enabled)), 'tool_batchmanage');
        break;

    case 'enable':
        if (!isset($all[$managejob])) {
            break;
        }
        $enabled = array_keys($enabled);
        $enabled[] = $managejob;
        set_config('enabled_managejobs', implode(',', $enabled), 'tool_batchmanage');
        break;

    case 'up':
        if (!isset($enabled[$managejob])) {
            break;
        }
        $enabled = array_keys($enabled);
        $enabled = array_flip($enabled);
        $current = $enabled[$managejob];
        if ($current == 0) {
            break; // Already at the top.
        }
        $enabled = array_flip($enabled);
        $enabled[$current] = $enabled[$current - 1];
        $enabled[$current - 1] = $managejob;
        set_config('enabled_managejobs', implode(',', $enabled), 'tool_batchmanage');
        break;

    case 'down':
        if (!isset($enabled[$managejob])) {
            break;
        }
        $enabled = array_keys($enabled);
        $enabled = array_flip($enabled);
        $current = $enabled[$managejob];
        if ($current == count($enabled) - 1) {
            break; // Already at the end.
        }
        $enabled = array_flip($enabled);
        $enabled[$current] = $enabled[$current + 1];
        $enabled[$current + 1] = $managejob;
        set_config('enabled_managejobs', implode(',', $enabled), 'tool_batchmanage');
        break;
}

redirect($return);
