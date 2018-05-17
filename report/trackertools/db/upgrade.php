<?php
/**
 * ULPGC specific mod tracker customizations
 *
 * @package    report
 * @subpackage trackertools
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// This file keeps track of upgrades to
// the ulpgccore plugin
//


function xmldb_report_trackertools_upgrade($oldversion) {

    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2018032000) {
    
        // Define table report_trackertools_devq to be created.
        $table = new xmldb_table('report_trackertools_devq');

        // Adding fields to table report_trackertools_devq.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('trackerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('queryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('visible', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');

        // Adding keys to table report_trackertools_devq.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('trackerkey', XMLDB_KEY_FOREIGN, array('trackerid'), 'tracker', array('id'));
        $table->add_key('querykey', XMLDB_KEY_FOREIGN, array('queryid'), 'tracker_query', array('id'));
        $table->add_key('userkey', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        // Adding indexes to table 
        $table->add_index('tracker-user', XMLDB_INDEX_NOTUNIQUE, array('trackerid, userid'));
        
        // Conditionally launch create table for report_trackertools_devq.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    
         upgrade_plugin_savepoint(true, 2018032000, 'report', 'trackertools');
    }

    return true;
}
