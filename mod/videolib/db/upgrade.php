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
 * @package     mod_videolib
 * @category    upgrade
 * @copyright   2018 Enrique Castro @ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute mod_videolib upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_videolib_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019061700) {

        // Define table videolib_source_mapping to be created.
        $table = new xmldb_table('videolib_source_mapping');

        // Adding fields to table videolib_source_mapping.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('videolibkey', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('source', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('annuality', XMLDB_TYPE_CHAR, '20', null, null, null, null);
        $table->add_field('remoteid', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('cmids', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table videolib_source_mapping.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        
        // Define indexes 
        $table->add_index('videolibkey', XMLDB_INDEX_NOTUNIQUE, array('videolibkey'));
        $table->add_index('keysourceannuality', XMLDB_INDEX_UNIQUE, array('videolibkey,source,annuality'));
        
        // Conditionally launch create table for videolib_source_mapping.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Assign savepoint reached.
        upgrade_mod_savepoint(true, 2019061700, 'videolib');
    }


    return true;
}
