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
 * @package    managejob_qcatdelete
 * @copyright  2013 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['pluginname'] = 'Delete Question Categories';
$string['pluginname_desc'] = 'Allows to specify a Question category by name and/or number and delete it from a list os courses.

Course selection based on category, visibility and other properties';
$string['qcategory_selector'] = 'Category to delete';
$string['applyqcatdelete'] = 'Apply Question Category deletion';
$string['applyqcatdelete_help'] = '
Allows to specify a course Question Category in a form and then remove the section and its contents in specified courses selected in a second form.

Course selection based on category, visibility and other properties';
$string['forcedelete'] = 'Force delete';
$string['forcedelete_help'] = 'If set to YES, then children categories will be moved to grandparent and questions moved to specified category. <br />
If set to NO and there are children, not deleted.';
$string['qcategoryname'] = 'Question category name';
$string['qcategoryname_help'] = 'The exact name, or a text including SQL wildcards.';
$string['qcategorysettings'] = 'Question category to delete';
$string['qcategorysaved'] = 'Move to category';
$string['qcategorysaved_help'] = 'Category to move existing questions into. Created new if not existing.';
