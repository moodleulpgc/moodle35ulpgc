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

require_once(dirname(__FILE__).'/../../../config.php');
require_once('../locallib.php');

$systemcontext = context_system::instance();

require_login();
require_capability('report/deviceanalytics:viewdashboard', $systemcontext);

$pagetitle = get_string('dashboard_title', 'report_deviceanalytics');
$PAGE->set_context($systemcontext);
$PAGE->set_url('/report/deviceanalytics/admin/dashboard.php');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagelayout('admin');
$PAGE->set_cacheable(false);
$PAGE->requires->css('/report/deviceanalytics/css/dashboard_css.css');
$PAGE->requires->js('/report/deviceanalytics/libs/jquery-1.12.2.min.js', true);
$PAGE->requires->js('/report/deviceanalytics/libs/Chart.min.js', true);
$PAGE->requires->js('/report/deviceanalytics/js/dashboardcharts.js', false);

$timeform = new deviceanalytics_dashboard_time_form();
if ($fromform = $timeform->get_data()) {
    $timeform->set_data($fromform);
    $analyticsdata = report_deviceanalytics_load_datas($fromform->timestart, $fromform->timefinish);
} else {
    $analyticsdata = report_deviceanalytics_load_datas();
}

$PAGE->requires->js_function_call('createCharts', array($analyticsdata), true);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('dashboard_name', 'report_deviceanalytics'));
echo '<noscript>';
echo $OUTPUT->error_text(get_string('dashboard_nojs_error_message', 'report_deviceanalytics'));
echo '</noscript>';
if ((is_null($analyticsdata))||(empty($analyticsdata))) {
    echo $OUTPUT->error_text(get_string('dashboard_no_data_error', 'report_deviceanalytics'));
} else {
    echo $OUTPUT->heading(get_string('dashboard_time_title', 'report_deviceanalytics'), 4);
    $timeform->display();
    echo $OUTPUT->container_start(null, 'datas');
    $vtables = report_deviceanalytics_create_data_tables($analyticsdata);
    $chartout = report_deviceanalytics_create_charts();
    $out = report_deviceanalytics_create_containers($chartout, $vtables);
    foreach ($out as $wrap) {
        echo $wrap;
    }
    echo $OUTPUT->container_end();
}
echo $OUTPUT->footer();