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
 * Library of interface functions includes and constants
 *
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*LOADING MOODLE*/
require_once(dirname(__FILE__).'/../../config.php');

/*GLOBAL REQUIREMENTS*/
global $CFG;
global $PAGE;
global $OUTPUT;
global $DB;
global $USER;
global $ADMIN;

/*HELPER*/
$timeformat = 'd-m-Y G:i:s';

/*TABLE*/
$dbtables = array();
$dbtables['deviceanalytics'] = 'report_deviceanalytics';
$dbtables['deviceanalytics_data'] = 'report_deviceanalytics_data';

/*EXTERN LIBS*/
require_once('libs/Browscap.php');

/*INCLUDES*/
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/formslib.php');
require_once('admin/dashboard_time_form.php');
require_once('admin/settings_form.php');

require_once('redirector.php');
require_once('classes/deviceanalytics_data_storage_class.php');
require_once('classes/deviceanalytics_data_object_class.php');
require_once('locallib.php');