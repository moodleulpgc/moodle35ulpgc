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
 * Strings for component 'supervisionwarning_lowslots_scheduler', language 'en'
 *
 * @package   supervisionwarning_lowslots_scheduler
 * @copyright  2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Low nº scheduler slots';
$string['config_pluginname'] = 'Check to activate detection of Schedulers with nº slots offered weekly below the minimum (set a module Scheduler). The detection takes place adding all slots offered by a teacher in the current week';
$string['threshold'] = 'Hour threshold for scheduler slots';
$string['config_threshold'] = 'The minimun period that should be offered weekly in schedulers slots. If the sum of hours in slots in a week is lower, then the scheduler will be flagged as a supervision warning. The setting represents <strong>HOURS</strong> in a week.';
$string['collectstats'] = 'Collect supervision stats about Low nº scheduler slots';
$string['countwarnings'] = '{$a->num} Insufficent slots in {$a->coursename}';

