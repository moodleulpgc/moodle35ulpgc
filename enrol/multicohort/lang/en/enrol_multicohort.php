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
 * Strings for component 'enrol_multicohort', language 'en'.
 *
 * @package    enrol_multicohort
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['addgroup'] = 'Add to group';
$string['addgroup_help'] = 'Select a group to add users to. It may be an existing group or a new one.

Option "Create new group" will create a new group named after this enrol method.

Option "Create new group for cohort" will create a new group for each of the separate cohorts in the "Any cohorts" group.
';
$string['assignrole'] = 'Assign role';
$string['multicohort:config'] = 'Configure multicohort instances';
$string['multicohort:unenrol'] = 'Unenrol suspended users';
$string['defaultgroupnametext'] = '{$a->name} (multicohort) {$a->increment}';
$string['instanceexists'] = 'Multicohort is already synchronised with selected role';
$string['pluginname'] = 'Multicohort sync';
$string['pluginname_desc'] = 'Multicohort enrolment plugin synchronises cohort members selected among multiple cohorts with course participants.';
$string['status'] = 'Active';
$string['creategroup'] = 'Create new group';
$string['multiplegroup'] = 'Create new group for each cohort';
$string['keepgroups'] = 'Keep cohort synced groups';
$string['oranycohorts'] = 'Member of any of';
$string['oranycohorts_help'] = '
The synchronization will affect to users that are memberes of any one of the cohorts selected in this group.
This field is mandatory, you must select at leat one cohort here';
$string['andallcohorts'] = 'And all in';
$string['andallcohorts_help'] = 'Optionally, selected users must be members of ALL cohorts in this group, must be assigned to each one of these.';
$string['notcohorts'] = 'And not in';
$string['notcohorts_help'] = 'Optionally, you may indicated a group of cohorts the user must be NOT a member of. 
The user should NOT be a member of any or all of these cohorts, controlled by the next option.
';
$string['notand'] = 'all of them';
$string['notor'] = 'any of them';
$string['andornotcohorts'] = 'Exclude combination';
$string['andornotcohorts_help'] = 'If "any", then a user that is a member of any one of the cohorts in the NOT group will not be selected for this synchronization.
If "all", then only users that are members of all, each one, of the cohorts in NOT groups will be exluded';
$string['assigngroupmode'] = 'Enrol & group assign mode';
$string['assigngroupmode_help'] = 'How to process enrolmment & group membership. 

 * Enrol users & Groups: matching users are enroled in this course and added to aplicable group.
 * Groups for enrolled users: No new users are enroled. Cohort membership is checked for existing users.
 
   Existing users matching cohort membership are synchronized with aplicable group membership.
 * Groups for role users: No new users are enroled.  Cohort membership is checked for existing users.
 
   Existing users with specified role and matching cohort membership are synchronized with aplicable group membership.
';
$string['enrolgroups'] = 'Enrol users & Groups';
$string['onlygroups'] = 'Groups for enrolled users';
$string['rolegroups'] = 'Groups for role users';
$string['noenrolgroups'] = 'New enrolments can be deactivated and used only to assign groups.';
