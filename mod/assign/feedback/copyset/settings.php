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
 * @package assingfeedback_copyset
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// Note this is on by default
$settings->add(new admin_setting_configcheckbox('assignfeedback_copyset/enabled',
                   new lang_string('default', 'assignfeedback_copyset'),
                   new lang_string('default_help', 'assignfeedback_copyset'), 1));

$settings->add(new admin_setting_configcheckbox('assignfeedback_copyset/enabledhidden',
                   new lang_string('enabledhidden', 'assignfeedback_copyset'),
                   new lang_string('enabledhidden_config', 'assignfeedback_copyset'), 0)
                );

                   
$settings->add(new admin_setting_configcheckbox('assignfeedback_copyset/tfspecialperiod',
                   new lang_string('tfspecialperiod', 'assignfeedback_copyset'),
                   new lang_string('tfspecialperiod_config', 'assignfeedback_copyset'), 0)
                );
