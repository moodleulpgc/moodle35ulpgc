<?php
/**
 * ULPGC specific customizations
 *
 * @package    local
 * @subpackage ulpgcquiz
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// This file keeps track of upgrades to
// the ulpgccore plugin
//


function xmldb_local_ulpgcquiz_upgrade($oldversion) {

    global $CFG, $DB;

    $dbman = $DB->get_manager();

    /// just a mockup
    if ($oldversion < 0) {
        throw new upgrade_exception('local_ulpgcquiz', $oldversion, 'Can not upgrade such an old plugin');
    }

    if ($oldversion < 2016020100) {
    
    
    
         upgrade_plugin_savepoint(true, 2016020100, 'local', 'ulpgcquiz');
    }

    return true;
}
