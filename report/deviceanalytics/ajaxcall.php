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
 * Saves screen data from user device into the current record / if possible
 *
 * @see        storage_helper_page
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('lib.php');
require_sesskey();

$insertid = required_param('insertid', PARAM_INT);
$devicedisplaysizex = required_param('device_display_size_x', PARAM_INT);
$devicedisplaysizey = required_param('device_display_size_y', PARAM_INT);
$devicewindowsizex = required_param('device_window_size_x', PARAM_INT);
$devicewindowsizey = required_param('device_window_size_y', PARAM_INT);

$datastorage = new deviceanalytics_data_storage();
$datastorage->report_deviceanalytics_update_screensize(
    $insertid,
    $devicedisplaysizex,
    $devicedisplaysizey,
    $devicewindowsizex,
    $devicewindowsizey
);