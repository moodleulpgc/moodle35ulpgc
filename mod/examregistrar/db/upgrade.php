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
    
    if ($oldversion < 2018051800) {

        $table = new xmldb_table('examregistrar_session_seats');
        $field = new xmldb_field('reviewerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'seat');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'seat');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('certified', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'seat');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('taken', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'seat');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('showing', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'seat');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
                // Define table messageinbound_datakeys to be created.
        $table = new xmldb_table('examregistrar_responses');
        // Adding fields to table session responses.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examsession', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('examid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('roomid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('additional', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('examfile', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('numfiles', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('showing', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('taken', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('modifierid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('reviewerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timefiles', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timeuserdata', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        
        // Adding keys to table session responses.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('examsession', XMLDB_KEY_FOREIGN, array('examsession'), 'examregistrar_sessions', array('id'));
        $table->add_key('examid', XMLDB_KEY_FOREIGN, array('examid'), 'examregistrar_exams', array('id'));
        $table->add_key('roomid', XMLDB_KEY_FOREIGN, array('roomid'), 'examregistrar_locations', array('id'));
        $table->add_key('examfile', XMLDB_KEY_FOREIGN, array('examfile'), 'examregistrar_examfile', array('id'));

        // Adding indexes to table session responses.
        $table->add_index('examsession-examid-roomid-examfile', XMLDB_INDEX_UNIQUE, array('examsession', 'examid', 'roomid', 'examfile'));

        // Conditionally launch create table for exam student response files.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    
        upgrade_mod_savepoint(true, 2018051800, 'examregistrar');
    }
    
    
    if ($oldversion < 2018051803) {
        $table = new xmldb_table('examregistrar_responses');
        
        foreach(array('numfiles', 'showing', 'taken') as $name) {
            $field = new xmldb_field($name, XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '0');
            if ($dbman->field_exists($table, $field)) {
                $dbman->change_field_default($table, $field);
            }
        }
        
        $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_default($table, $field);
        }
        
        foreach(array('additional', 'modifierid', 'reviewerid', 'timefiles', 'timeuserdata', 'timemodified') as $name) {
            $field = new xmldb_field($name, XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            if ($dbman->field_exists($table, $field)) {
                $dbman->change_field_default($table, $field);
            }
        }

        $index = new xmldb_index('examsession-bookedsite-examid-roomid-examfile', XMLDB_INDEX_UNIQUE, array('examsession', 'bookedsite', 'examid', 'roomid', 'examfile'));
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        $index = new xmldb_index('examsession-examid-roomid-examfile', XMLDB_INDEX_UNIQUE, array('examsession', 'examid', 'roomid', 'examfile'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        $key = new xmldb_key('bookedsite', XMLDB_KEY_FOREIGN, array('bookedsite'), 'examregistrar_locations', array('id'));
        $dbman->drop_key($table, $key);

        $field = new xmldb_field('bookedsite', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        $table = new xmldb_table('examregistrar_session_seats');
        $field = new xmldb_field('timereviewed', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2018051803, 'examregistrar');
    }

    if ($oldversion < 2019080100) {    
    
                // Define table messageinbound_datakeys to be created.
        $table = new xmldb_table('examregistrar_vouchers');
        // Adding fields to table session vouchers.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examregid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('bookingid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('uniqueid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        
        // Adding keys to table session vouchers.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('bookingid', XMLDB_KEY_FOREIGN, array('bookingid'), 'examregistrar_sessions', array('id'));

        // Adding indexes to table session vouchers.
        $table->add_index('examregid-uniqueid', XMLDB_INDEX_UNIQUE, array('examregid, uniqueid'));    
    
        // Conditionally launch create table for exam student response files.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    
        upgrade_mod_savepoint(true, 2019080100, 'examregistrar');
    }
    
    if ($oldversion < 2019080200) {    
    
        // Define field assignplugincm to be added to forum.
        $table = new xmldb_table('examregistrar');
        $field = new xmldb_field('configdata', XMLDB_TYPE_TEXT, null, null, null, null, null, 'lagdays');

        // Conditionally launch add field assignplugincm.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        upgrade_mod_savepoint(true, 2019080200, 'examregistrar');
    }
    

    if ($oldversion < 2019102500) {
        /*
        $table = new xmldb_table('examregistrar_examsessions');
        // Conditionally launch create table for exam student response files.
        if (!$dbman->table_exists($table)) {
            // Adding fields to table session responses.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('examregid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('examsession', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('period', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('examdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('duration', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
            $table->add_field('timeslot', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('visible', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 1);
            $table->add_field('component', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
            $table->add_field('modifierid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
            
            // Adding keys to table session responses.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('examregid', XMLDB_KEY_FOREIGN, array('examregid'), 'examregistrar', array('id'));
            $table->add_key('examsession', XMLDB_KEY_FOREIGN, array('examsession'), 'examregistrar_elements', array('id'));
            $table->add_key('period', XMLDB_KEY_FOREIGN, array('period'), 'examregistrar_elements', array('id'));
            
            // Adding indexes to table session responses.
            $table->add_index('examdate', XMLDB_INDEX_NOTUNIQUE, array('examdate'));
            $table->add_index('timeslot', XMLDB_INDEX_NOTUNIQUE, array('timeslot'));
            $table->add_index('examregid-examsession-period', XMLDB_INDEX_UNIQUE, array('examregid', 'examsession', 'period'));

            $dbman->create_table($table);
        }
        
        $tables = array('examregistrar_exams' => ['shortname', 'aacada', 'conv', 'parcial'],
                        'examregistrar_locations' => ['idnumber'],
                        'examregistrar_periods' => ['idnumber'],
                        'examregistrar_seating_rules' => ['locationid'],
                        'examregistrar_session_rooms' => ['locationid'],
                        'examregistrar_session_seats' => ['locationid'],
                    );
    
        foreach($tables as $table => $fields) {
            // load table
            $xmldbtable = new xmldb_table($table); 
            
            foreach($fields as $field) {
                $xmldbfield = new xmldb_field($field);
                if ($dbman->field_exists($xmldbtable, $xmldbfield)) {
                    $dbman->drop_field($xmldbtable, $xmldbfield);
                }
            }
        }        
        
        $table = new xmldb_table('examregistrar_elements');        
        $field = new xmldb_field('modifierid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 
        
        $table = new xmldb_table('examregistrar_locationss');        
        if ($dbman->table_exists($table)) {
            $fields = array('location'=>'examregid', 'modifierid'=>null);
            foreach($fields as $name => $after) {
                $field = new xmldb_field($name, XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, $after);
                if (!$dbman->field_exists($table, $field)) {
                    $dbman->add_field($table, $field);
                }        
            }
            
            $field = new xmldb_field('component', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '', 'sortorder');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            
            // Adding keys to table 
            foreach(array('location', 'locationtype') as $name) {
                $key = new xmldb_key($name, XMLDB_KEY_FOREIGN, array($name), 'examregistrar_elements', array('id'));
                $dbman->add_key($table, $key);
            }
            
            $index = new xmldb_index('examregid-location-locationtype', XMLDB_INDEX_UNIQUE, array('examregid','location','locationtype'));
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
        }
        
        $table = new xmldb_table('examregistrar_periods');        
        if ($dbman->table_exists($table)) {
            $fields = array('period'=>'examregid', 'annuality'=>'period', 'periodtype'=>'annuality',
                            'term'=>'periodtype', 'modifierid'=>null, 'timemodified'=>null);
            foreach($fields as $name => $after) {
                $field = new xmldb_field($name, XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, $after);
                if (!$dbman->field_exists($table, $field)) {
                    $dbman->add_field($table, $field);
                }        
            }
            
            $field = new xmldb_field('component', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '', 'visible');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            
            // Adding keys to table 
            foreach(array('period', 'annuality', 'periodtype') as $name) {
                $key = new xmldb_key($name, XMLDB_KEY_FOREIGN, array($name), 'examregistrar_elements', array('id'));
                $dbman->add_key($table, $key);
            }
            
            $index = new xmldb_index('examregid-period-annuality', XMLDB_INDEX_UNIQUE, array('examregid','period','annuality'));
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
        }
        
        $table = new xmldb_table('examregistrar_responses');        
        $field = new xmldb_field('timereviewed', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 
        
        $table = new xmldb_table('examregistrar_seating_rules');        
        $field = new xmldb_field('roomid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'examid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 
        $key = new xmldb_key('roomid', XMLDB_KEY_FOREIGN, array('roomid'), 'examregistrar_locations', array('id'));
        $dbman->add_key($table, $key);
        
        $table = new xmldb_table('examregistrar_session_seats');        
        $field = new xmldb_field('roomid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'examid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 
        $key = new xmldb_key('roomid', XMLDB_KEY_FOREIGN, array('roomid'), 'examregistrar_locations', array('id'));
        $dbman->add_key($table, $key);
        
        $table = new xmldb_table('examregistrar_staffers');        
        $field = new xmldb_field('component', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '', 'visible');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        } 

        $table = new xmldb_table('examregistrar_session_rooms');        
        if ($dbman->table_exists($table)) {
            $fields = array('bookedsite'=>'examsession', 'roomid'=>'bookedsite', 
                            'modifierid'=>null, 'timemodified'=>null);
            foreach($fields as $name => $after) {
                $field = new xmldb_field($name, XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, $after);
                if (!$dbman->field_exists($table, $field)) {
                    $dbman->add_field($table, $field);
                }        
            }
            
            // Adding keys to table 
            foreach(array('bookedsite', 'roomid' ) as $name) {
                $key = new xmldb_key($name, XMLDB_KEY_FOREIGN, array($name), 'examregistrar_locations', array('id'));
                $dbman->add_key($table, $key);
            }
            $index = new xmldb_index('examsession-roomid', XMLDB_INDEX_UNIQUE, array('examsession', 'roomid'));
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
        }
        */
        upgrade_mod_savepoint(true, 2019102500, 'examregistrar');
    }    

    return true;
}
