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
 * Version details
 *
 * @package    mod
 * @subpackage bootstrapelements
 * @copyright  2014 Birmingham City University <michael.grant@bcu.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_bootstrapelements_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2015010600) {

        // Define field bootstrapstyle to be added to bootstrapelements.
        $table = new xmldb_table('bootstrapelements');
        $field = new xmldb_field('bootstrapicon', XMLDB_TYPE_TEXT, null, null, null, null, null, 'bootstraptype');

        // Conditionally launch add field bootstrapstyle.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Bootstrap savepoint reached.
        upgrade_mod_savepoint(true, 2015010600, 'bootstrapelements');
    }
    return true;
}


