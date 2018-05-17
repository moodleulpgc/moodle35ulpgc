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
 * coursetemplate managejob lang strings.
 *
 * @package    managejob_coursetemplate
 * @copyright  2013 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['pluginname'] = 'Course template';
$string['pluginname_desc'] = 'Applies a template (from a backup MBZ) into selected courses .';
$string['applycoursetemplate'] = 'Apply Course template';
$string['applycoursetemplate_help'] = '
Allows to specify a course templete as a backup MBZ file and applicate it into courses selected in a second form.
You can specify how the backup will by applied (adding, deleting before etc.)

Course selection based on category, visibility and other properties';
$string['applytemplatesource'] = 'Template MBZ file to use';
$string['applytemplatesettings'] = 'Apply template options';
$string['course_template'] = 'Course template';
$string['applytemplate'] = 'Apply template';
$string['applytemplate_help'] = '
Gets a backupfile and restores it onto selected courses either deleting or adding content.';
$string['template'] = 'Template backup file: ';
$string['restoreusers'] = 'Restore users';
$string['restoregroups'] = 'Restore groups';
$string['restoregroupings'] = 'Restore groupings';
$string['restoreblocks'] = 'Restore blocks';
$string['restorefilters'] = 'Restore filters';
$string['restoreadminmods'] = 'Restore admin restricted modules ';
$string['restorekeepgroups'] = 'Keep groups and groupings ';
$string['restorekeeproles'] = 'Keep roles and enrolments ';
$string['restorenullmodinfo'] = 'Only into empty courses (without modules)';
$string['notemplate'] = 'No template file defined';
