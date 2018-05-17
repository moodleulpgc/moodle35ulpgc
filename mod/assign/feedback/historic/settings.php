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
 * This file defines the admin settings for this plugin
 *
 * @package   assignfeedback_historic
 * @copyright 2014 Enrique Castro, ecastro  @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//if (has_capability('tool/coursetemplate:apply', $systemcontext)) {

global $DB;

$settings->add(new admin_setting_configcheckbox('assignfeedback_historic/default',
                   new lang_string('default', 'assignfeedback_historic'),
                   new lang_string('default_help', 'assignfeedback_historic'), 0));

$settings->add(new admin_setting_configtext('assignfeedback_historic/annuality',
                   new lang_string('annuality', 'assignfeedback_historic'),
                   new lang_string('annuality_help', 'assignfeedback_historic'), '', PARAM_ALPHANUMEXT, 30));

$years = range(0, 12);
unset($years[0]);
$settings->add(new admin_setting_configselect('assignfeedback_historic/agespan',
                   new lang_string('agespan', 'assignfeedback_historic'),
                   new lang_string('agespan_help', 'assignfeedback_historic'), 2, $years));

$datatypes = $DB->get_records_menu('assignfeedback_historic_type', null, 'name ASC', 'id, name');
$settings->add(new admin_setting_configmultiselect('assignfeedback_historic/datatypes',
                   new lang_string('datatypes', 'assignfeedback_historic'),
                   new lang_string('datatypes_help', 'assignfeedback_historic'), array(1,2), $datatypes));

$url = new moodle_url('/mod/assign/feedback/historic/managetypes.php');
$settings->add(new admin_setting_configempty('assignfeedback_historic/managetypes',
                   new lang_string('managedatatypes', 'assignfeedback_historic'),
                   new lang_string('managedatatypes_help', 'assignfeedback_historic').
                                    '<br />'.html_writer::link($url, get_string('managedatatypes', 'assignfeedback_historic'))));

$url = new moodle_url('/mod/assign/feedback/historic/updatedata.php', array('do'=>'upload'));
$settings->add(new admin_setting_configempty('assignfeedback_historic/uploadlink',
                   new lang_string('uploadlink', 'assignfeedback_historic'),
                   new lang_string('uploadlink_help', 'assignfeedback_historic').
                                    '<br />'.html_writer::link($url, get_string('uploadlink', 'assignfeedback_historic'))));

$url = new moodle_url('/mod/assign/feedback/historic/updatedata.php', array('do'=>'update'));
$settings->add(new admin_setting_configempty('assignfeedback_historic/updatelink',
                   new lang_string('updatelink', 'assignfeedback_historic'),
                   new lang_string('updatelink_help', 'assignfeedback_historic').
                                    '<br />'.html_writer::link($url, get_string('updatelink', 'assignfeedback_historic'))));


