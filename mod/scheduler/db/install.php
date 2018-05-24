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
 * Post installation and migration code.
 *
 * This file replaces:
 *   - STATEMENTS section in db/install.xml
 *   - lib.php/modulename_install() post installation hook
 *   - partially defaults.php
 *
 * @package    mod_scheduler
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_scheduler_install() {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
    
    if ($ulpgc = get_config('local_ulpgccore')) {

        $table = new xmldb_table('scheduler');
        $field = new xmldb_field('usesharedslots', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0, 'bookingrouping');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $index = new xmldb_index('course', XMLDB_INDEX_NOTUNIQUE, array('course'));
        // Conditionally add index studentid.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $table = new xmldb_table('scheduler_slots');
        /*
        $field = new xmldb_field('etutor', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'hideuntil');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        */
        $field = new xmldb_field('shared', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'etutor');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('scheduler_appointment');
        /*
        $field = new xmldb_field('etutor', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'appointmentnoteformat');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        */
        $field = new xmldb_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'etutor');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define index groupid (not unique) to be added to scheduler_appointment.
        $index = new xmldb_index('groupid', XMLDB_INDEX_NOTUNIQUE, array('groupid'));
        // Conditionally add index studentid.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
    }
    
}
