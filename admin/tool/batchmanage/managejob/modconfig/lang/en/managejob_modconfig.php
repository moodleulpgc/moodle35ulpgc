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
 * @package    managejob_modconfig
 * @copyright  2013 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['pluginname'] = 'Module configuration';
$string['pluginname_desc'] = 'Applies changes in module config, i.e. fields in course_modules DB table & the modname table.';
$string['applycourseconfig'] = 'Apply Module configuration';
$string['applycourseconfig_help'] = '
Allows to specify module settings in a form and then apply those setting values to all courses selected in a second form.

Course selection based on category, visibility and other properties';

$string['referencecourse'] = 'Reference course shortname';
$string['configreferencecourse'] = 'Existing course with modules that cam be used as template for module configuration.';

$string['mod_selector'] = 'Module selection';
$string['mod_configurator'] = 'Reference selection';
$string['referencedmod'] = 'Reference module';
$string['referencedmod_help'] = 'Reference configuration';
$string['mod_config'] = 'Module configuration';

$string['applymodconfig'] = 'Apply module config';
$string['applymodconfig_help'] = '
Allows to specify module configuration settings in a form and then apply those setting values to modules containes in courses selected in a second form.

Course selection based on category, visibility and other properties';
$string['referencemod'] = 'Reference module';
$string['referencemod_help'] = 'A module belonging to the site-configured reference course that contains default template options for module configuration. 
If empty then the nex form will contain only site-defautl values for all settings.';
$string['capabilitysettings'] = 'Roles & capabilities options';