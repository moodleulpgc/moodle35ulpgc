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
 * @package    tool
 * @subpackage bakuprestore
 * @copyright  2015 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_tool_backuprestore_install() {
    global $CFG, $OUTPUT, $DB;

    $dbman = $DB->get_manager();

    // Define field creatoridnumber to be added to question
    $table = new xmldb_table('question');
    $field = new xmldb_field('creatoridnumber', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    // Conditionally launch add field.
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    // Define field modifierdnumber to be added to question
    $field = new xmldb_field('modifieridnumber', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    // Conditionally launch add field.
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    return true;

}


