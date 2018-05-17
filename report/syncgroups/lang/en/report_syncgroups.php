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
 * Strings for component 'report_syncgroups', language 'en'
 *
 * @package   report_syncgroups
 * @copyright 2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


$string['syncgroups'] = 'Pool Groups';
$string['syncgroups:view'] = 'View Pool groups course report';
$string['eventreportviewed'] = 'Syncgroups report viewed';
$string['page-report-editdates-index'] = 'Pool course groups membership';
$string['pluginname'] = 'Pool Groups';

$string['targetgroup'] = 'Target group';
$string['targetgroup_help'] = '
The destination group, users being members of parent groups will be added as members of this group.';
$string['parentgroups'] = 'Parent groups';
$string['parentgroups_help'] = '
Source groups to check membership. Users being members of these groups will be added to target group.';
$string['editsync'] = 'Edit a group pool';
$string['newsync'] = 'Add a new group pool';
$string['deletesync'] = 'Delete a group pool';
$string['deletedsync'] = 'Group pool deleted';
$string['deletesyncconfirm'] = 'You are about to delete a group pool consisting in: <br />
<br />
Target group:  {$a->target} <br />
Parent groups: {$a->parents} <br />
<br />
Do you want to continue? ';
$string['visible'] = 'Visible';
$string['visible_help'] = 'If visible, the group members pooling is active. If hidden, no pooling will take place.';