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
 * @package    managejob_gitemmove
 * @copyright  2013 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['pluginname'] = 'Move Grade item';
$string['pluginname_desc'] = 'Allows to specify som grade items by name and/or idnumber and move them to a selected Grade category in a list os courses.

Course selection based on category, visibility and other properties';
$string['applygitemmove'] = 'Apply Move to category';
$string['applygitemmove_help'] = '
Allows to specify a course Grade item in a form and then move the grade items to specified Grade category in courses selected in a second form.

Course selection based on category, visibility and other properties';
$string['gitem_selector'] = 'Grade items to move';
$string['target_selector'] = 'Select Target category';
$string['gitemname'] = 'Grade item name';
$string['gitemname_help'] = 'The exact name, or a text including SQL wildcards.';
$string['gitemsettings'] = 'Grade item to move';
$string['gitemidnumbers'] = 'Items idnumbers';
$string['gitemidnumbers_help'] = 'A single grade idnumber or list of grade idmubers separated by either spaces, ",", "|" or \tabs.';
$string['gitemparentname'] = 'Parent category name';
$string['gitemparentname_help'] = 'Only items that belong to this Grade category (with this exact name) will be selected';
$string['gitemparentidnumber'] = 'Parent category IDnumber';
$string['gitemparentidnumber_help'] = 'Only items that belong to this Grade category (with this idnumber) will be selected';
$string['gitemhidden'] = 'Visibility';
$string['hidden'] = 'Hidden';
$string['gitemhidden_help'] = 'Visibility of the selected Grade items';
$string['gitemnoncat'] = 'Non-categorized items';
$string['gitemnoncat_help'] = 'If enabled, then items with course as direct parent will be selected.';

$string['targetgcfullname'] = 'Grade category name';
$string['targetgcfullname_help'] = 'The primary name for the desired target category, in exact spelling.';
$string['targetgitemname'] = 'Grade category item name';
$string['targetgitemname_help'] = 'The name of the grade item corresponding to the desired target category, in exact spelling.';
$string['targetgitemidnumber'] = 'Grade category IDnumber';
$string['targetgitemidnumber_help'] = 'The IDnumber of the grade item corresponding to the desired target category, in exact spelling.';
$string['targetinsertlast'] = 'Insertion point';
$string['targetinsertlast_help'] = 'Whether the gradeitems will be inserted before or after the existing items in the target grade category.';
$string['targetexplain'] = 'You must supply al least one of these items. 
If several filled, the selected category must comply to all items. 

If several categories found, used the higher order one.';
$string['before'] = 'Before';
$string['after'] = 'After';
