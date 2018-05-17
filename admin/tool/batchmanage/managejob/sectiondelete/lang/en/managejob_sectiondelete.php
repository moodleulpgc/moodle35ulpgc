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
 * courseconfig managejob lang strings.
 *
 * @package    managejob_sectiondelete
 * @copyright  2013 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['pluginname'] = 'Delete course sections';
$string['pluginname_desc'] = 'Allows to specify a single course section by name and/or number and delete it from a list os courses.

Course selection based on category, visibility and other properties';
$string['section_selector'] = 'Section to delete';
$string['applysectiondelete'] = 'Apply section deletion';
$string['applysectiondelete_help'] = '
Allows to specify a course section in a form and then remove the section and its contents in specified courses selected in a second form.

Course selection based on category, visibility and other properties';
$string['forcedelete'] = 'Delete section modules';
$string['forcedelete_help'] = 'If set to NO, only empty sections (without modules) will be deleted. <br />
When set to YES then ALL modules in the affected sections will be permanently erased without asking further confirmation.';
