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
 * Strings for component 'enrol_metapattern', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    enrol
 * @subpackage metapattern
 * @copyright  2012 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['addgroup'] = 'Add to group';
$string['metapattern:config'] = 'Configure meta enrol instances';
$string['metapattern:selectaslinked'] = 'Select course as meta linked';
$string['metapattern:unenrol'] = 'Unenrol suspended users';
$string['nosyncroleids'] = 'Roles that are not synchronised';
$string['nosyncroleids_desc'] = 'By default all course level role assignments are synchronised from parent to child courses. Roles that are selected here will not be included in the synchronisation process. The roles available for synchronisation will be updated in the next cron execution.';
$string['pluginname'] = 'Pattern meta link';
$string['pluginname_desc'] = 'Course pattern meta link enrolment plugin synchronises enrolments and roles in a course from courses matching a given pattern';


$string['linkedfield'] = 'Course field linked';
$string['linkedfield_help'] = 'Parent courses will be selected by matching pattern to a course field.
Here you can specify which field will be probed for matching the desired pattern.';
$string['linkedpattern'] = 'Course pattern linked';
$string['linkedpattern_help'] = 'Users enrolled in any courses with the linked pattern will be synchronised.

Parent courses are those that happen to match this pattern (as an SQL pattern) in the selected course field


Synchronization is only one way: from parent courses into this one. Users enrolled in this course will not be enrolled in parent courses.
';
$string['syncroles'] = 'Roles to be synchronised';
$string['syncroles_help'] = 'Only users with these roles in any course of matching pattern will be synchronised.';
$string['enrolledas'] = 'Enroll as';
$string['enrolledas_help'] = 'If set to Synchronized, each user will get in this course the same role(s) that had in parent courses.

If a role is specified, then all users will be enrolled in this course with that role, disregarding the original one(s).';
$string['synchronize'] = 'Synchronized enrol';
$string['syncgroup'] = 'Add to group';
$string['syncgroup_help'] = 'If enabled, in addition to enrollment, users will be added to the selected group.

If a group is specified, then all users will be adedd to that group.
Other options are selecting a group based on parent course of adding users. Groups are synchonized based on group idnumber matching with parent course.


 * Synced by shortname : the group is determined by parent course shortname (group.idnumber = course.shortname)
 * Synced by idnumber : the group is determined by parent course idnumber (group.idnumber = course.idnumber)
 * Synced by ctype : the group is determined by parent course ctype (group.idnumber = course.ctype)
 * Synced by term : the group is determined by parent course term (group.idnumber = course.term)
 * Synced by catidnumber : the group is determined by parent category idnumber (group.idnumber = category.idnumber)
 * Synced by faculty : the group is determined by parent category faculty (group.idnumber = category.faculty)
 * Synced by degree : the group is determined by parent category degree (group.idnumber = category.degree)

 
If the required group doesn\'t exist, it is created automatically.
';
$string['gsyncbyctype'] = 'Synced by course type';
$string['gsyncbyterm'] = 'Synced by course term';
$string['gsyncbyshortname'] = 'Synced by course shortname';
$string['gsyncbyidnumber'] = 'Synced by course idnumber';
$string['gsyncbyfaculty'] = 'Synced by faculty';
$string['gsyncbydegree'] = 'Synced by degree';
$string['gsyncbycatidnumber'] = 'Synced by category idnumber';
$string['ctype'] = 'Course type';
