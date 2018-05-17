<?php
/**
 * ULPGC specific customizations (changes to core tables)
 *
 * @package    local
 * @subpackage ulpgccore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_local_ulpgccore_install() {
    global $CFG, $DB;

        $dbman = $DB->get_manager();
    /// Install modifications into core tables

    /// mods to user table
    /// Define table course to be modified
        $table = new xmldb_table('user');

        /// Changing type of field name on table user_info_field to text
        $field = new xmldb_field('institution');
        $field->set_attributes(XMLDB_TYPE_CHAR, '80', null, null, null, '');
    /// Launch change of existing field
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field);
        }

        $field = new xmldb_field('department');
        $field->set_attributes(XMLDB_TYPE_CHAR, '80', null, null, null, '');
    /// Launch change of existing field
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field);
        }      
}
