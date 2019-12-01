<?php
/**
 * ULPGC specific customizations
 *
 * @package    local
 * @subpackage ulpgcgroups
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// This file keeps track of upgrades to
// the ulpgccore plugin
//


function xmldb_local_ulpgcgroups_upgrade($oldversion) {

    global $CFG, $DB;

    $dbman = $DB->get_manager();

    /// just a mockup
    if ($oldversion < 0) {
        throw new upgrade_exception('local_ulpgcgroups', $oldversion, 'Can not upgrade such an old plugin');
    }

    if ($oldversion < 2016020100) {
    
    // create new groups helper table
        $table = new xmldb_table('local_ulpgcgroups');
        // Conditionally launch create table for local_ulpgcgroups
        if (!$dbman->table_exists($table)) {
            // Adding fields to local_ulpgcgroups.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('component', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
            $table->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

            // Adding keys to table local_ulpgcgroups.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('groupid', XMLDB_KEY_FOREIGN, array('groupid'), 'groups', array('id'));

            $dbman->create_table($table);
        }
    
    /// Revert groups table 
    /// Define table groups to be modified
        $table = new xmldb_table('groups');

        // Define field component added to groups
        $field = new xmldb_field('component', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);

        // copy existing fields to new table
        if ($dbman->field_exists($table, $field)) {
            $groups = $DB->get_recordset('groups', null, '', 'id AS groupid, component, itemid');
            if($groups->valid()) {
                foreach($groups as $group) {
                    if(!$old = $DB->get_record('local_ulpgcgroups', array('groupid'=>$group->groupid))) {
                        $DB->insert_record('local_ulpgcgroups', $group);
                    } else {
                        $group->id = $old->id;
                        $DB->update_record('local_ulpgcgroups', $group);
                    }
                }
            }
            $groups->close();
        }
        
        // Conditionally launch drop field component
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field itemid added to groups
        $field = new xmldb_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Conditionally launch drop field itemid
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

    
    
         upgrade_plugin_savepoint(true, 2016020100, 'local', 'ulpgcgroups');
    }

    
    
    if ($oldversion < 2019102500) {
        $tables = array('groups' => ['enrol', 'cod_grupo', 'component', 'itemid'],
                        'groups_members' => ['enrol'],
                    );
    
        foreach($tables as $table => $fields) {
            // load table
            $xmldbtable = new xmldb_table($table); 
            foreach($fields as $field) {
                $xmldbfield = new xmldb_field($field);
                if ($dbman->field_exists($xmldbtable, $xmldbfield)) {
                    $dbman->drop_field($xmldbtable, $xmldbfield);
                }
            }
        }
    
         upgrade_plugin_savepoint(true, 2019102500, 'local', 'ulpgcgroups');
    }
    
    return true;
}
