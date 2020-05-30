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
 * @package     mod_examboard
 * @category    upgrade
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute mod_examboard upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_examboard_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2017082501) {

        // Define table examboard_member to be modified
        $table = new xmldb_table('examboard_examinee');
        $field = new xmldb_field('userlabel', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'sortorder');
        if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
        }
    
        // Define table examboard_confirmation to be created.
        $table = new xmldb_table('examboard_confirmation');

        // Adding fields to table lesson_overrides.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('examid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('confirmed', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('exemption', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timeconfirmed', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timeunconfirmed', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table lesson_overrides.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('examid', XMLDB_KEY_FOREIGN, array('examid'), 'examboad_exam', array('id'));

        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));

        // Conditionally launch create table for lesson_overrides.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table examboard_member to be modified
        $table = new xmldb_table('examboard_member');

        foreach(array('confirmed', 'exemption', 'timeconfirmed') as $field) {
            $oldfield = new xmldb_field($field);
            if ($dbman->field_exists($table, $oldfield)) {
                $dbman->drop_field($table, $oldfield);
            }
        }
    
        // Examboard savepoint reached.
        upgrade_mod_savepoint(true, 2017082501, 'examboard');
    }
    
     if ($oldversion < 2018032501) {
    
        // Define table examboard to be modified
        $table = new xmldb_table('examboard');
        $field = new xmldb_field('gradeable', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'grademode');
        if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
        }
    
        // Examboard savepoint reached.
        upgrade_mod_savepoint(true, 2018032501, 'examboard');   
    }

     if ($oldversion < 2018032502) {
    
        // Define table examboard to be modified
        $table = new xmldb_table('examboard');
        $field = new xmldb_field('confirmdefault', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'confirmtime');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define table examboard_confirmation to be modified
        $table = new xmldb_table('examboard_confirmation');
        
        $field = new xmldb_field('available', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'confirmed');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('dischargeformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'confirmed');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('dischargetext', XMLDB_TYPE_TEXT, null, null, null, null, null, 'confirmed');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('discharge', XMLDB_TYPE_CHAR, '30', null, null, null, '', 'confirmed');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Examboard savepoint reached.
        upgrade_mod_savepoint(true, 2018032502, 'examboard');   
    }
        
     if ($oldversion < 2018032504) {
        // ensure there is a diretory for PDF header images
        $imagedir = $CFG->dirroot.'/mod/examboard/pix/temp';
        if(check_dir_exists($imagedir, false)) {
            remove_dir($imagedir);
        }
        make_writable_directory($imagedir);
    
        // Examboard savepoint reached.
        upgrade_mod_savepoint(true, 2018032504, 'examboard');   
    }
    
    if ($oldversion < 2018080600) {
        // Define table examboard to be modified
        $table = new xmldb_table('examboard');
        
        $field = new xmldb_field('gradeable', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        // Launch change of type for field gradeable.
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field);
        }
        
        $field = new xmldb_field('defense', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'gradeable');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('proposal', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'gradeable');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        $field = new xmldb_field('examgroups', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'usetutors');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('groupingname', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'examgroups');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        upgrade_mod_savepoint(true, 2018080600, 'examboard');   
    }

    if ($oldversion < 2019073002) {
        // Define table examboard to be modified
        $table = new xmldb_table('examboard_exam');
        $field = new xmldb_field('examperiod', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, '-', 'boardid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        //now update the table content (Customize for this table and moment   )
        
        $DB->set_field_select('examboard_exam', 'examperiod', 'ord', "sessionname LIKE '%Conv. Ordinaria%' ", null);
        $DB->set_field_select('examboard_exam', 'examperiod', 'ext', "sessionname LIKE '%Conv. Extra%' ", null);
        $DB->set_field_select('examboard_exam', 'examperiod', '-', "examperiod IS NULL OR examperiod = '' ", null);
    
        $DB->set_field_select('examboard_exam', 'sessionname', 'Sesión 1', "sessionname LIKE '%Sesión 1%'", null);
        $DB->set_field_select('examboard_exam', 'sessionname', 'Sesión 2', "sessionname LIKE '%Sesión 2%'", null);
        $DB->set_field_select('examboard_exam', 'sessionname', 'Sesión 3', "sessionname LIKE '%Sesión 3%'", null);
        $DB->set_field_select('examboard_exam', 'sessionname', 'Sesión 4', "sessionname LIKE '%Sesión 4%'", null);
        $DB->set_field_select('examboard_exam', 'sessionname', '', "sessionname LIKE '%Conv%'", null);    
    
    
        upgrade_mod_savepoint(true, 2019073002, 'examboard');   
    }    
    
    if ($oldversion < 2020052100) {
        // Define table examboard to be modified
        $table = new xmldb_table('examboard_exam');
        $field = new xmldb_field('accessurl', XMLDB_TYPE_CHAR, '500', null, XMLDB_NOTNULL, null, null, 'venue');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2020052100, 'examboard');   
    }
    
    
    
    return true;
}
