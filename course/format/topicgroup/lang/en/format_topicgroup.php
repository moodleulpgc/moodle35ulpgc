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
 * Strings for component 'format_topicgroup', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   format_topicgroup
 * @copyright 2013 onwards E. Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['currentsection'] = 'This topic';
$string['currentgrouping'] = 'Current restriction';
$string['sectionname'] = 'Topic';
$string['pluginname'] = 'Topics by Grouping format';
$string['section0name'] = 'General';
$string['page-course-view-topicgroup'] = 'Any course main page in topicgroup format';
$string['page-course-view-topicgroup-x'] = 'Any course page in topicgroup format';
$string['hidefromothers'] = 'Hide topic';
$string['showfromothers'] = 'Show topic';
$string['setsettings'] = 'Restriction';

$string['topicgroup:manage'] = 'Manage course format';
$string['topicgroup:manageall'] = 'Manage course groupings & all settings';
$string['topicgroup:viewhidden'] = 'View hidden topics';

$string['defaultcoursedisplay'] = 'Course display default';
$string['defaultcoursedisplay_desc'] = "Either show all the sections on a single page or section zero and the chosen section on page.";

$string['editingroles'] = 'Editing roles';
$string['editingroles_desc'] = 'Roles that will have format editing capabilities and access all groups.';

$string['restrictedroles'] = 'Restricted roles';
$string['restrictedroles_desc'] = 'Users with these roles cannot change course format and has capabilities restricted, for instance cannot access all groups.';

$string['restrictsection'] = 'Restrict section to grouping';
$string['editrestrictsection'] = 'Edit section grouping restriction';
$string['unrestrictsection'] = 'Unlock section from grouping';
$string['restrictedsectionlbl'] = 'Accesible only to {$a} ';

$string['managerestrictions'] = 'Manage section restrictions';

$string['setgrouping'] = 'Set grouping restriction';
$string['setgroupingerror'] = 'An error ocurred when saving section grouping restriction for section {$a}.';
$string['unsetgrouping'] = 'Unset grouping restriction';

$string['grouping'] = 'Grouping with access';
$string['applyother'] = 'Change in other sections';
$string['applyother_help'] = 'If set, then all other sections that are currently restricted to the same grouping as this one will be changed as this one.';

$string['groupmode'] = 'Change group mode';
$string['groupmode_help'] = 'If set, this and all other elements in this section will be changed to this group mode.';
$string['keepgroupmode'] = 'Do not change';

$string['applyall'] = 'Unset other sections';
$string['applyall_help'] = 'If set, then all other sections that are currently restricted to the same grouping as this one will be unset as this one.';
