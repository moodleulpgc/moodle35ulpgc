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
 * supervision warning management.
 *
 * @package    local_supervision
 * @copyright  2016 Enriue Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$action = required_param('action', PARAM_ALPHANUMEXT);
$warning = required_param('warning', PARAM_PLUGIN);

$PAGE->set_url('/local/supervision/warnings.php');
$PAGE->set_context(context_system::instance());

require_login();
require_capability('local/supervision:manage', context_system::instance());
require_sesskey();

$all = \core_plugin_manager::instance()->get_installed_plugins('supervisionwarning');
$enabled = get_config('local_supervision', 'enabled_warnings');
if (!$enabled) {
    $enabled = array();
} else {
    $enabled = array_flip(explode(',', $enabled));
}

$return = new moodle_url('/admin/settings.php', array('section' => 'supervisionwarnings'));

$syscontext = context_system::instance();

switch ($action) {
    case 'disable':
        unset($enabled[$warning]);
        set_config('enabled_warnings', implode(',', array_keys($enabled)), 'local_supervision');
        break;

    case 'enable':
        if (!isset($all[$warning])) {
            break;
        }
        $enabled = array_keys($enabled);
        $enabled[] = $warning;
        set_config('enabled_warnings', implode(',', $enabled), 'local_supervision');
        break;

    case 'up':
        if (!isset($enabled[$warning])) {
            break;
        }
        $enabled = array_keys($enabled);
        $enabled = array_flip($enabled);
        $current = $enabled[$warning];
        if ($current == 0) {
            break; // Already at the top.
        }
        $enabled = array_flip($enabled);
        $enabled[$current] = $enabled[$current - 1];
        $enabled[$current - 1] = $warning;
        set_config('enabled_warnings', implode(',', $enabled), 'local_supervision');
        break;

    case 'down':
        if (!isset($enabled[$warning])) {
            break;
        }
        $enabled = array_keys($enabled);
        $enabled = array_flip($enabled);
        $current = $enabled[$warning];
        if ($current == count($enabled) - 1) {
            break; // Already at the end.
        }
        $enabled = array_flip($enabled);
        $enabled[$current] = $enabled[$current + 1];
        $enabled[$current + 1] = $warning;
        set_config('enabled_warnings', implode(',', $enabled), 'local_supervision');
        break;
}

redirect($return);
