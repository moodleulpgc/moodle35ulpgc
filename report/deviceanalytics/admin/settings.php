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
 * Defines the version and other meta-info about the plugin
 *
 * Setting the $plugin->version to 0 prevents the plugin from being installed.
 * See https://docs.moodle.org/dev/version.php for more info.
 *
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../lib.php');
$systemcontext = context_system::instance();
require_login();
require_capability('report/deviceanalytics:managesettings', $systemcontext);
$pagename = 'report_deviceanalytics_settings';
$PAGE->set_context($systemcontext);
$PAGE->set_url('/report/deviceanalytics/admin/settings.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('settings_title', 'report_deviceanalytics'));
$PAGE->set_heading(get_string('settings_title', 'report_deviceanalytics'), 3);
admin_externalpage_setup($pagename);
$settingsform = new deviceanalytics_settings_form();
if ($settingsform->is_cancelled()) {
    echo $OUTPUT->header();
        echo $OUTPUT->container(get_string('settings_cancelled', 'report_deviceanalytics'), 'important', 'notice');
        echo $OUTPUT->continue_button(new moodle_url('/report/deviceanalytics/admin/settings.php', array()));
    echo $OUTPUT->footer();
} else if ($fromform = $settingsform->get_data()) {
    $DB->update_record('report_deviceanalytics', $fromform);
    echo $OUTPUT->header();
        echo $OUTPUT->container(get_string('settings_updated', 'report_deviceanalytics'), 'important', 'notice');
        echo $OUTPUT->continue_button(new moodle_url('/report/deviceanalytics/admin/settings.php', array()));
    echo $OUTPUT->footer();
} else {
    global $timeformat;
    $dbdata = $DB->get_record('report_deviceanalytics', array(), '*');
    $settingsform->set_data($dbdata);
    echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('settings_title', 'report_deviceanalytics'));
        $settingsform->display();
    echo $OUTPUT->footer();
}