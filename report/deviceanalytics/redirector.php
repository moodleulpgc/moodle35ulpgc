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
 * redirect to the helper page
 *
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * redirect class
 *
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deviceanalytics_redirector{
    /**
     * init function is directly called from events.php
     *
     * @param observers $eventdata
     */
    public static function init($eventdata) {
        global $CFG;
        global $DB;
        $dbman = $DB->get_manager();
        if (! $dbman->table_exists('report_deviceanalytics')) {
            return 0;
        }

        if (isloggedin()) {
            if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
                $url = preg_replace("/^http:/i", "https:", $CFG->wwwroot);
                redirect($url.'/report/deviceanalytics/storage_helper_page.php');
            } else {
                redirect($CFG->wwwroot.'/report/deviceanalytics/storage_helper_page.php');
            }
        }
    }
}