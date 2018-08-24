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
 * gcatadd managejob lang strings.
 *
 * @package    managejob_gcatadd
 * @copyright  2013 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['pluginname'] = 'Add Grade category';
$string['pluginname_desc'] = 'Adds Grade categories from a template (CSV text) into selected courses .';
$string['applygcatsettings'] = 'Add Grade category';
$string['applygcatadd'] = 'Add Grade category';
$string['applygcatadd_help'] = '
Allows to specify Grade categories as a CSV text and applicate it into courses selected in a second form.

Course selection based on category, visibility and other properties';
$string['gcat_selector'] = 'Grade category(s) to add';
$string['gcattemplate'] = 'Template Grade categories';
$string['gcattemplate_help'] = 'A CSV text with a grade category specification per line. 

You can use as separators any combination of ",", "|" or \tab characters, but no spaces
';
$string['templateexplain'] = 'Each category in a line. Parent category must be specified by fullname. 
If a field is not specified, thet the value in the form below is used. <br /> 
Aggregation types: <br /> 
{$a} 
';
$string['parentcategory'] = 'Parent cateory';
$string['parentcategory_help'] = 'Used if NOT specified a parent in the text above. 

MUST be the full name of an existing category in each course. 

If left blank then the course category will be the parent.
';
$string['gcatinsertfirst'] = 'Insertion point';
$string['gcatinsertfirst_help'] = 'Whether the Grade category will be inserted before or after the existing items in the parent grade category.';
$string['before'] = 'Before';
$string['after'] = 'After';
