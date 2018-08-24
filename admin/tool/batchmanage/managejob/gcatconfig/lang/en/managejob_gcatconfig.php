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
 * @package    managejob_gcatconfig
 * @copyright  2013 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['pluginname'] = 'Config Grade category';
$string['pluginname_desc'] = 'Allows to update config data for selected Grade categories in a list os courses.

Course selection based on category, visibility and other properties';
$string['gcat_config'] = 'Grade category config data';
$string['gcat_selector'] = 'Category to update';
$string['applygcatconfig'] = 'Apply Grade category config';
$string['applygcatconfig_help'] = '
Allows to specify a course Grade Category, select fields to modify in a form and then updates the Grade category configuration in specified courses selected in a second form.

Course selection based on category, visibility and other properties';
$string['gcatsettings'] = 'Grade category to update';
$string['gcatdepth'] = 'Grade category depth';
$string['gcatdepth_help'] = 'Nesting level for the category. Course category is level 1. 

Categories directly hanging from course have level 2.';
$string['gcathidden'] = 'Grade category visibility';
$string['gcathidden_help'] = 'The visibility state for the categories that will be selected';
$string['categoryname_help'] = 'The full name of the parent category, not the grade item name';
$string['parentcategory'] = 'Parent category name';
$string['parentcategory_help'] = 'The full name of the parent category, not the grade item name';
$string['gcatparentidnumber'] = 'Parent category IDnumber';
$string['gcatparentidnumber_help'] = 'The IDnumber of the parent category grade item.';
$string['referencecourse'] = 'Reference course shortname';
$string['configreferencecourse'] = 'Existing course with Grade categories that cam be used as template for grade category configuration.';

