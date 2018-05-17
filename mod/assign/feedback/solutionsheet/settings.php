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
 * This file defines the admin settings for this plugin
 *
 * @package   assignfeedback_solutionsheet
 * @copyright 2016 Henning Bostelmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Is Solution sheet feedback enabled.
$settings->add(new admin_setting_configcheckbox(
                   'assignfeedback_solutionsheet/default',
                   new lang_string('default', 'assignfeedback_solutionsheet'),
                   new lang_string('default_help', 'assignfeedback_solutionsheet'),
                   1
               ));

// Is "Yes, from now on" option available at assignment creation.
$settings->add(new admin_setting_configcheckbox(
        'assignfeedback_solutionsheet/fromnowon',
        new lang_string('fromnowon', 'assignfeedback_solutionsheet'),
        new lang_string('fromnowon_help', 'assignfeedback_solutionsheet'),
        1 // Default to on.
        ));
