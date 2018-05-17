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
 * Upgrade script for the quiz makeexam report.
 *
 * @package   quiz_makeexam
 * @copyright 2014 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Quiz makeexam report upgrade code.
 */
function xmldb_quiz_makeexam_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

      // Moodle v2.6.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2014010300) {
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2014010300, 'quiz', 'makeexam');
    }

    if ($oldversion < 2014083002) {

        $config = get_config('quiz_makeexam');

        // Define field currentattempt to be added to makeexam_attempts.
        $table = new xmldb_table('quiz_makeexam_attempts');
        $field = new xmldb_field('currentattempt', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'attemptid');

        // Conditionally launch add field currentattempt.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2014083002, 'quiz', 'makeexam');
    }

    if ($oldversion < 2015081100) {

        /// Transform existing quiz_makeexam_qinstances table
        // rename fields in existing table
        $table = new xmldb_table('quiz_makeexam_qinstances');
        if($dbman->table_exists($table)) {
            $field = new xmldb_field('quiz', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
            $key = new xmldb_key('quiz', XMLDB_KEY_FOREIGN, array('quiz'), 'quiz', array('id'));
            // Launch drop key quiz.
            $dbman->drop_key($table, $key);
            // Launch rename field quiz.
            $dbman->rename_field($table, $field, 'quizid');
            $key = new xmldb_key('quizid', XMLDB_KEY_FOREIGN, array('quizid'), 'quiz', array('id'));
            // Launch add key quizid.
            $dbman->add_key($table, $key);

            $field = new xmldb_field('attemptid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
            $key = new xmldb_key('attemptid', XMLDB_KEY_FOREIGN, array('attemptid'), 'quiz_makeexam_attempts', array('id'));
            // Launch drop key quiz.
            $dbman->drop_key($table, $key);
            // Launch rename field quiz.
            $dbman->rename_field($table, $field, 'mkattempt');
            $key = new xmldb_key('mkattempt', XMLDB_KEY_FOREIGN, array('mkattempt'), 'quiz_makeexam_attempts', array('id'));
            // Launch add key quizid.
            $dbman->add_key($table, $key);

            $field = new xmldb_field('question', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'quiz');
            $key = new xmldb_key('question', XMLDB_KEY_FOREIGN, array('question'), 'question', array('id'));
            // Launch drop key question.
            $dbman->drop_key($table, $key);
            // Launch rename field question.
            $dbman->rename_field($table, $field, 'questionid');
            $key = new xmldb_key('questionid', XMLDB_KEY_FOREIGN, array('questionid'), 'question', array('id'));
            // Launch add key questionid.
            $dbman->add_key($table, $key);

            $field = new xmldb_field('grade', XMLDB_TYPE_NUMBER, '12, 7', null, XMLDB_NOTNULL, null, '0', 'question');
            // Launch rename field grade.
            $dbman->rename_field($table, $field, 'maxmark');

            // now rename table
            $dbman->rename_table($table,'quiz_makeexam_slots');
        }

        // reaload table definition & add new fields
        $table = new xmldb_table('quiz_makeexam_slots');

        $field = new xmldb_field('slot', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'quizid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('page', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'slot');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('requireprevious', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'page');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        /// add new table quiz_makeexam_sections
        // Define table quiz_sections to be created.
        $table = new xmldb_table('quiz_makeexam_sections');

        // Adding fields to table quiz_sections.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('quizid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('mkattempt', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('inuse', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('firstslot', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('heading', XMLDB_TYPE_CHAR, '1333', null, null, null, null);
        $table->add_field('shufflequestions', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table quiz_sections.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('quizid', XMLDB_KEY_FOREIGN, array('quizid'), 'quiz', array('id'));
        $table->add_key('mkattempt', XMLDB_KEY_FOREIGN, array('mkattempt'), 'quiz_makeexam_attempts', array('id'));

        // Conditionally launch create table for quiz_makeexam_sections.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        } else {
            $field = new xmldb_field('inuse', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'mkattempt');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2015081100, 'quiz', 'makeexam');
    }

    return true;
}

