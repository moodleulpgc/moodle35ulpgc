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
 * Provides code to be executed during the installation
 *
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Inserting the current time into the config tale
 */
function xmldb_report_deviceanalytics_install() {
    global $DB;
    $installrecord = new stdClass();
    $installrecord->starttime = time();
    $installrecord->status = 1;
    $installrecord->anonymous = 0;
    $installrecord->adminlog = 0;
    $DB->insert_record('report_deviceanalytics', $installrecord);
}

/**
 * Nothing yet
 */
function xmldb_report_deviceanalytics_install_recovery() {
}