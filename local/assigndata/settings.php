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
 * Plugin administration pages are defined here.
 *
 * @package     local_assigndata
 * @category    admin
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $temp = new \admin_settingpage('local_assigndata_settings', get_string('settings','local_assigndata')); 

    $temp->add(new \admin_setting_configcheckbox('local_assigndata/enabledassigndata', get_string('enabled','local_assigndata'), get_string('enabled_details','local_assigndata'), 0));
    
    $temp->add(new \admin_setting_configtext('local_assigndata/maxfields', get_string('maxfields','local_assigndata'), get_string('maxfields_details', 'local_assigndata'), '10'));
    
  
    $ADMIN->add('localplugins', $temp);

}
