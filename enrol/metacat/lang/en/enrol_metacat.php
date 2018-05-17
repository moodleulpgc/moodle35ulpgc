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
 * Strings for component 'enrol_metacat', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    enrol
 * @subpackage metacat
 * @copyright  2012 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['metacat:config'] = 'Configure meta enrol instances';
$string['metacat:selectaslinked'] = 'Select course as meta linked';
$string['metacat:unenrol'] = 'Unenrol suspended users';
$string['nosyncroleids'] = 'Roles that are not synchronised';
$string['nosyncroleids_desc'] = 'By default all course level role assignments are synchronised from parent to child courses. Roles that are selected here will not be included in the synchronisation process. The roles available for synchronisation will be updated in the next cron execution.';
$string['pluginname'] = 'Category meta link';
$string['pluginname_desc'] = 'Category meta link enrolment plugin synchronises enrolments and roles in a course from courses in a given Category';


$string['linkedcategories'] = 'Category linked';
$string['linkedcategories_help'] = 'Users enrolled in any courses belonging to selected category will be synchronised.

Synchronization is only one way: from parent courses into this one. Users enrolled in this course will not be enrolled in parent courses.
';
$string['catfromcourse'] = 'this course\'s category';
$string['autocategory'] = '[auto]';
$string['refreshautocategory'] = 'Refresh category from course';
$string['refreshautocategory_help'] = 'If enabled, the parent category will be updated if this course is moved to a new category.

If not set, the parent category will remain fixed even when the course is moved to a different categry.';

$string['syncroles'] = 'Roles to be synchronised';
$string['syncroles_help'] = 'Only users with these roles in any course of selected categories will be synchronised.';
$string['enrolledas'] = 'Enroll as';
$string['enrolledas_help'] = 'If set to Synchronized, each user will get in this course the same role(s) that had in parent courses.

If a role is specified, then all users will be enrolled in this course with that role, disregarding the original one(s).';
$string['synchronize'] = 'Synchronized enrol';
$string['syncgroup'] = 'Add to group';
$string['syncgroup_help'] = 'If enabled, in addition to enrollment, users will be added to the selected group.

If a group is specified, then all users will be adedd to that group. Other options are:

 * Synced by name : the group is determined by parent category name (group.idnumber = category.name)
 * Synced by idnumber : the group is determined by parent category idnumber (group.idnumber = category.idnumber)
 * Synced by id : the group is determined by parent category id (group.idnumber = category.id)
 * Synced by faculty : the group is determined by parent category faculty (group.idnumber = category.faculty)
 * Synced by degree : the group is determined by parent category degree (group.idnumber = category.degree)


If the required group doesn\'t exist, then it will be created automatically.
';
$string['gsyncbyfaculty'] = 'Synced by faculty';
$string['gsyncbydegree'] = 'Synced by degree';
$string['gsyncbyid'] = 'Synced by id';
$string['gsyncbyname'] = 'Synced by name';
$string['gsyncbyidnumber'] = 'Synced by idnumber';
$string['auto'] = 'auto';
