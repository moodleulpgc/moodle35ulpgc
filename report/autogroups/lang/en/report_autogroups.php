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
 * Strings for component 'report_autogroups', language 'en'
 *
 * @package   report_autogroups
 * @copyright 2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


$string['autogroups'] = 'Autopopulate Groups';
$string['autogroups:edit'] = 'Edit Autopopulate groups rules';
$string['autogroups:view'] = 'View Autopopulate groups course report';
$string['eventreportviewed'] = 'Autopopulate groups report viewed';
$string['page-report-editdates-index'] = 'Pool course groups membership';
$string['pluginname'] = 'Autopopulate Groups';

$string['targetgroup'] = 'Target group';
$string['targetgroup_help'] = '
The destination group, users selected users will be added as members of this group.';
$string['searchterm'] = 'Source courses search term';
$string['searchterm_help'] = '
This field defines a search term to look for courses. Any users enrolled in those couses will be added to target group in this course.';
$string['searchfield'] = 'Search field';
$string['searchfield_help'] = '
The field in the course table where to look for the above search term.';
$string['sourceroles'] = 'Roles in source courses';
$string['sourceroles_help'] = '
Only the the users enrolled with one of these roles in source courses will be selected.';

$string['editsync'] = 'Edit a group rule';
$string['newsync'] = 'Add a new group rule';
$string['deletesync'] = 'Delete a group rule';
$string['deletedsync'] = 'Group rule deleted';
$string['deletesyncconfirm'] = 'You are about to delete a group rule consisting in: <br />
<br />
Target group:  {$a->target} <br />
Search term: {$a->search} <br />
<br />
Do you want to continue? ';
$string['visible'] = 'Visible';
$string['visible_help'] = 'If visible, the group members pooling is active. If hidden, no pooling will take place.';