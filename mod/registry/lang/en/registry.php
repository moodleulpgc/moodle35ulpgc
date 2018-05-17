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
 * English strings for registry
 *
 * @package    mod_registry
 * @subpackage registry
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['registry:addinstance'] = 'Add a registry activity';
$string['registry:review'] = 'Review registered submissions';
$string['registry:submit'] = 'Submit course items to registry';
$string['registry:submitany'] = 'Submit items of any courses to registry';

$string['eventitemsubmitted'] = 'Item submitted to Registry';
$string['eventcoursemodulereviewed'] = 'Course module reviewed';

$string['modulename'] = 'Registry';
$string['modulenameplural'] = 'Registries';
$string['modulename_help'] = 'The Registry module allows users to submit registration for course-based items.';
$string['modulename_link'] = 'mod/registry/view';
$string['registryfieldset'] = 'Custom example fieldset';
$string['registryname'] = 'Registry name';
$string['registryname_help'] = 'This is the content of the help tooltip associated with the registryname field. Markdown syntax is supported.';
$string['registry'] = 'Registry';
$string['pluginadministration'] = 'Registry administration';
$string['pluginname'] = 'Registry';

$string['timedue'] = 'Registry due date';
$string['timedue_help'] = 'Registering due date. All items submitted for registration after this date will be marked as delayed.';
$string['timeduemsg'] = 'Register due date: ';
$string['modconfig'] = 'Registry configuration';
$string['trackerconfig'] = 'Tracker configuration';
$string['regmodule'] = 'Module tracked by Registry';
$string['regmodule_help'] = 'The module which instances can be registered here. The system will look for instances of this module in controled courses (only in defined section).';
$string['regsection'] = 'Course section tracked';
$string['regsection_help'] = 'The course section that holds the module instances tracked by this Registry.
The system will look for instances of tracked module only in this section of each course.';
$string['category'] = 'Course category';
$string['category_help'] = '
The course category where courses with items will be looked for.

Category can be defined to be fetched from the current course
(where this instance reside) idnumber paramenter or directý current course category.
';
$string['catfromidnumber'] = 'Use category from this course idnumber';
$string['catfromcourse'] = 'Use category from course';
$string['visibility'] = 'Visibility';
$string['visibility_help'] = '
Determines which modules are included in the tracking, based on their visibility on the course page.
There are three options:

* Include any items: all modules disregarding visibility setting.
* Only visible items: Only currently visible items will be selected.
* Exclude visible items: Only currently hidden items will be selected.
';
$string['visibleall'] = 'include any items';
$string['visibleonly'] = 'only visible items';
$string['visiblenot'] = 'exclude visible items';

$string['adminmod'] = 'Admin restricted modules';
$string['adminmod_help'] = '
There are three options:

* Include any items: all modules disregarding restriction setting.
* Only restricted items: Only currently admin restricted items will be selected.
* Exclude restricted items: Only currently unrestricted items will be selected.
';
$string['adminmodall'] = 'include any items';
$string['adminmodonly'] = 'only restricted items';
$string['adminmodnot'] = 'exclude restricted items';
$string['trackerid'] = 'Tracker used';
$string['trackerid_help'] = '
The Tracker instance associated with this Ragistry.
When a course is registered by an user a new Tracker issue is generated. This option allow to specify in wich Tracker instance in this course wll those issues be available fro review.
Conversely, the "Status" field for this registry will be taken from issues of the specified Traker instance.';
$string['issuename'] = 'Issue identifier';
$string['issuename_help'] = '
When a course is submitted a new tracker issue is creatde for that course content.
This text will be appended to the course name and shortname in the issue summary field.

This text helps to identify issue origins when a single Tracker is used for several Registry instances.';
$string['syncroles'] = 'Reviewers to category';
$string['syncroles_help'] = 'If activated, the system will look for users with the registry reviewer roles defined in module settings and then will add them with course reviewer role at the indicated category.';

$string['enabletracking'] = 'Tracking of mod registry';
$string['configenabletracking'] = '
If enabled the system will look into courses to track changes in registered modules.';
$string['checkedroles'] = 'Roles to check';
$string['configcheckedroles'] = '
Only the selected roles will be tracked as potential subjects of <strong>teacher</strong> register activity.';
$string['excludecourses'] = 'Exclude admin courses';
$string['configexcludecourses'] = 'If set, courses without a credits count or a proper IDnumber will be excluded from module registration tracking.';
$string['rolesreviewers'] = 'Roles of reviewers roles';
$string['configrolesreviewers'] = 'People with these roles in the Registry course will be added as course reviewers at category level.';
$string['reviewerrole'] = 'Reviewer role';
$string['configreviewerrole'] = 'People with these roles will be added as course reviewers at category level';

$string['registrysummary'] = 'Registry summary';
$string['nodata'] = 'There are no courses to register';
$string['lastsubmitted'] = 'Last registered';
$string['lastgraded'] = 'Last reviewed';
$string['status'] = 'Status';
$string['items'] = 'Items';
$string['saveregistrations'] = 'Register courses';
$string['submitconfirm'] = 'You are about to Register the content of these courses: <br/> {$a} ';
$string['downloadpdf'] = 'Download  PDF';
$string['attachments'] = 'Attachments';
$string['statuslink'] = 'View issue';
$string['status_posted'] = 'Posted';
$string['status_open'] = 'Open';
$string['status_resolving'] = 'Resolving';
$string['status_waiting'] = 'Waiting';
$string['status_testing'] = 'Testing';
$string['status_resolved'] = 'Resolved';
$string['status_abandonned'] = 'Abandonned';
$string['status_transfered'] = 'Transferred';
$string['status_published'] = 'Published';
$string['reviewlink'] = 'View registrer status for all courses';
$string['userlink'] = 'View registrer status for user courses';
$string['shortname'] = 'Shortname';
$string['fullname'] = 'Course';
$string['term'] = 'Semester:';
