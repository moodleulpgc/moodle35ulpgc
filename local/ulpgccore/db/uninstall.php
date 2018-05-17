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

function xmldb_local_ulpgccore_uninstall() {
    global $CFG, $DB;

        $dbman = $DB->get_manager();
    /// Uninstall modifications into core tables
    $select = $DB->sql_like('name', '?');  
    
    // hack to ensure module level capabilities deleted even if changed component field
    $DB->delete_records_select('capabilities', $select, array('%ulpgccore%'));
    

}
