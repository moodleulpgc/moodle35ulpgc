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

function xmldb_dialogue_install() {
    global $CFG, $DB;

        $dbman = $DB->get_manager();
    /// Install modifications into core tables

    /// mods to dialogue table
        $table = new xmldb_table('dialogue');

        /// Changing type of field name. Just in case not managed by upgrade. 
        $field = new xmldb_field('alternatemode', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'usecoursegroups');
        // Conditionally launch add field displaywordcount.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        
}
