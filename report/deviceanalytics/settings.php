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
 * Defines the dashboard and settings page. put them into the nav-tree
 *
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
if (has_capability('report/deviceanalytics:managesettings', context_system::instance())) {
    $ADMIN->add('reports',
        new admin_category(
            'report_deviceanalytics',
            get_string('pluginname', 'report_deviceanalytics')
        )
    );
    $ADMIN->add('report_deviceanalytics',
        new admin_externalpage(
            'report_deviceanalytics_dashboard',
            get_string('dashboard_name', 'report_deviceanalytics'),
            new moodle_url('/report/deviceanalytics/admin/dashboard.php')
        )
    );
    $ADMIN->add('report_deviceanalytics',
        new admin_externalpage(
            'report_deviceanalytics_settings',
            get_string('settings_name', 'report_deviceanalytics'),
            new moodle_url('/report/deviceanalytics/admin/settings.php')
        )
    );
}
$settings = null;