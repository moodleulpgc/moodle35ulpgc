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
 * This file keeps track of upgrades to the registry module
 *
 * @package    mod
 * @subpackage registry
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute registry upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_registry_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    if ($oldversion < 2013082600) {

        // Define field course to be added to registry
        $table = new xmldb_table('registry');
        $field = new xmldb_field('syncroles', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'itemname');

        // Add field course
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2013082600, 'registry');
    }

    if ($oldversion < 2013090200) {

        // Define field grader to be added to registry
        $table = new xmldb_table('registry_submissions');
        $field = new xmldb_field('grader', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'grade');

        // Add field grader
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2013090200, 'registry');
    }


    if ($oldversion < 2013090202) {
                      
        // Define field grader to be added to registry
        $table = new xmldb_table('registry_submissions');
        $field = new xmldb_field('grade', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0.00', 'issueid');

        // Add field grader
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } else {
            $dbman->change_field_type($table, $field);
        }

        upgrade_mod_savepoint(true, 2013090202, 'registry');
    }

    


    return true;
}
