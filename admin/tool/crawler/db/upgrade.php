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
 * Upgrade script
 *
 * @package    tool_crawler
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script
 *
 * @param integer $oldversion a version no
 */
function xmldb_tool_crawler_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019022000) {

        core_php_time_limit::raise();

        $tablename = 'tool_crawler_url';
        $urlcolumn = $DB->get_columns($tablename)['url'];
        $DB->replace_all_text($tablename, $urlcolumn, '&amp;', '&');

        upgrade_plugin_savepoint(true, 2019022000, 'tool', 'crawler');
    }

    if ($oldversion < 2019022200) {
        $table = new xmldb_table('tool_crawler_url');
        $field = new xmldb_field('errormsg', XMLDB_TYPE_TEXT, null, null, false, false, null, 'httpmsg');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2019022200, 'tool', 'crawler');
    }

    return true;
}
