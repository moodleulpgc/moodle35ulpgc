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
 * Plugin upgrade steps are defined here.
 *
 * @package     local_assigndata
 * @category    upgrade
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute local_assigndata upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_assigndata_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2017090100) {

        $table = new xmldb_table('local_assigndata_submission');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'temp_assigndata_submission');
        }
        
        // this is neccessary because old submission key is not deleteable
        if (!$dbman->table_exists('local_assigndata_submission')) {
            $table = new xmldb_table('local_assigndata_submission');
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('assignment', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('attemptnumber', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('fieldid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('content', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
            $table->add_field('content1', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
            $table->add_field('content2', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
            $table->add_field('content3', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
            $table->add_field('content4', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('assignment', XMLDB_KEY_FOREIGN, array('id'), 'assign', array('id'));
            $table->add_key('fieldid', XMLDB_KEY_FOREIGN, array('fieldid'), 'local_assigndata_fields', array('id'));
            $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
            $dbman->create_table($table);
        }
        
        $sql = "SELECT ads.*, s.userid, s.attemptnumber 
                FROM {temp_assigndata_submission} ads 
                JOIN {assign_submission} s ON s.assignment = ads.assignment AND s.id = ads.submission
                WHERE 1 
                ORDER BY ads.id ASC";
        $submissions = $DB->get_records_sql($sql, null);
        foreach($submissions as $submission) {
            $DB->insert_record('local_assigndata_submission', $submission);
        }

        $table = new xmldb_table('temp_assigndata_submission');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        upgrade_plugin_savepoint(true, 2017090100, 'local', 'assigndata');
    }
    
    
    return true;
}
