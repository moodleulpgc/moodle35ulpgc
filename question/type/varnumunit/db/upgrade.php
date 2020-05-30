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
 * The varnumunit question type upgrade code.
 *
 * @package    qtype
 * @subpackage varnumunitt
 * @copyright  2018 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_qtype_varnumunit_upgrade($oldversion) {
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2018050200) {

        $table = new xmldb_table('qtype_varnumunit_units');

        $removespace = new xmldb_field('removespace', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'unit');
        if ($dbman->field_exists($table, $removespace)) {
            $dbman->rename_field($table, $removespace, 'spaceinunit');
        }

        $spacingfeedback = new xmldb_field('spacingfeedback', XMLDB_TYPE_TEXT, null, null, null, null, null, 'spaceinunit');
        if (!$dbman->field_exists($table, $spacingfeedback)) {
            $dbman->add_field($table, $spacingfeedback, 'instanceid');
        }

        $spacingfeedbackformat = new xmldb_field('spacingfeedbackformat', XMLDB_TYPE_INTEGER, 4, null,
                XMLDB_NOTNULL, null, 1, 'spacingfeedback');
        if (!$dbman->field_exists($table, $spacingfeedbackformat)) {
            $dbman->add_field($table, $spacingfeedbackformat, 'instanceid');
        }

        // Data savepoint reached.
        upgrade_plugin_savepoint(true, 2018050200, 'qtype', 'varnumunit');
    }

    return true;
}
