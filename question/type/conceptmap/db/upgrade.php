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
 * Conceptmap question type upgrade code.
 *
 * @package    qtype
 * @subpackage conceptmap
 * @copyright  2011 Jorge Villalon based on the work of The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Upgrade code for the essay question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_conceptmap_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2011111904) {
        // Define table qtype_essay_options to be created
        $table = new xmldb_table('qtype_conceptmap_options');

        // Adding fields to table qtype_essay_options
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('questionid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null);
        $table->add_field('graderinfo', XMLDB_TYPE_TEXT, 'small', null,
                null, null, null);
        $table->add_field('graderinfoformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, '0');

        // Adding keys to table qtype_essay_options
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('questionid', XMLDB_KEY_FOREIGN_UNIQUE,
                array('questionid'), 'question', array('id'));

        // Conditionally launch create table for qtype_essay_options
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // conceptmap savepoint reached
        upgrade_plugin_savepoint(true, 2011111904, 'qtype', 'conceptmap');
    }

    if ($oldversion < 2011111905) {
        // Insert a row into the qtype_essay_options table for each existing conceptmap question.
        $DB->execute("
                INSERT INTO {qtype_conceptmap_options} (questionid, graderinfo, graderinfoformat)
                SELECT q.id, '', " . FORMAT_MOODLE . "
                FROM {question} q
                WHERE q.qtype = 'conceptmap'
                AND NOT EXISTS (
                    SELECT 'x'
                    FROM {qtype_conceptmap_options} qeo
                    WHERE qeo.questionid = q.id)");

        // conceptmap savepoint reached
        upgrade_plugin_savepoint(true, 2011111905, 'qtype', 'conceptmap');
    }

    // Moodle v2.1.0 release upgrade line
    // Put any upgrade step following this

    return true;
}
