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
 * This file keeps track of upgrades to the examregistrar module
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute examregistrar upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_examregistrar_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    if ($oldversion < 2013122110) {

        // Define field assignplugincm to be added to forum.
        $table = new xmldb_table('examregistrar_exams');
        $field = new xmldb_field('assignplugincm', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'visible');

        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('examregistrar_bookings');
        $index = new xmldb_index('examid-userid', XMLDB_INDEX_UNIQUE, array('examid', 'userid'));
        // Conditionally launch drop index name
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        $index = new xmldb_index('examid-userid', XMLDB_INDEX_NOTUNIQUE, array('examid', 'userid'));
        // Conditionally launch add index examid-userid
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        $index = new xmldb_index('examid-userid-booked-bookedsite', XMLDB_INDEX_NOTUNIQUE, array('examid', 'userid', 'booked', 'bookedsite'));
        // Conditionally launch add index examid-userid-booked-bookedsite
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $table = new xmldb_table('examregistrar_examfiles');
        $field = new xmldb_field('reviewid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'attempt');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('idnumber', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'attempt');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'attempt');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('component', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        // Conditionally launch add field assignplugincm.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('taken', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'idnumber');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'taken');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('modifierid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'reviewerid');
        }

        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'reviewerid');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timeapproved', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timerejected', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timeapproved');
        // Conditionally launch add field timerejected.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('reviewid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'attempt');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }



        $table = new xmldb_table('examregistrar');
        $field = new xmldb_field('reviewmod', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'workmode');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        upgrade_mod_savepoint(true, 2013122110, 'examregistrar');
    }

    if ($oldversion < 2013122113) {
        $table = new xmldb_table('examregistrar_bookings');
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'modifierid');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);

            if ($dbman->field_exists($table, $field)) {
                $sql = "UPDATE {examregistrar_bookings}
                        SET timecreated = timemodified
                        WHERE timecreated = 0 " ;
                $DB->execute($sql);
            }

        }

        upgrade_mod_savepoint(true, 2013122113, 'examregistrar');
    }

    if ($oldversion < 2015041600) {
        $table = new xmldb_table('examregistrar_examfiles');
        $field = new xmldb_field('printmode', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'reviewid');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2015041600, 'examregistrar');
    }

    if ($oldversion < 2015041601) {
        $table = new xmldb_table('examregistrar_examfiles');

        $field = new xmldb_field('reviewerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'userid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_default($table, $field);
        }

        $field = new xmldb_field('reviewid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'reviewerid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_default($table, $field);
        }

        upgrade_mod_savepoint(true, 2015041601, 'examregistrar');
    }

    if ($oldversion < 2015101600) {

        $table = new xmldb_table('examregistrar_examfiles');

        $field = new xmldb_field('taken', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'idnumber');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('timerejected', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timeapproved');
        // Conditionally launch add field timerejected.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2015101600, 'examregistrar');
    }

    return true;
}
