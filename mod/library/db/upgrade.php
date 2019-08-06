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
 * @package     mod_library
 * @category    upgrade
 * @copyright   2019 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute mod_library upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_library_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019073000) {
    
        // Capabilities cleanup
        $select = 'capability = :cap';
        $params = array('cap'=>'mod/library:addfiles');
        $DB->set_field_select('role_capabilities', 'capability', 'mod/library:edit', $select, $params);  
        
        //Uninstall unused plugins
        $DB->delete_records('config_plugins', array('plugin'=>'librarysource_onedrive'));
    
    
        // Library savepoint reached.
        upgrade_mod_savepoint(true, 2019073000, 'library');
    }

    return true;
}
