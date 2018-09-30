<?php
/**
 *
 * @package   block_supervision
 * @copyright  2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @global moodle_database $DB
 * @param int $oldversion
 * @param object $block
 */
function xmldb_block_supervision_upgrade($oldversion, $block) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2012081700) {

        upgrade_block_savepoint(true, 2012081700, 'supervision');
    }

    if ($oldversion < 2018081806) {
        
        $fields = array('block/supervision:manage', 'block/supervision:viewwarnings', 'block/supervision:editwarnings');
        $DB->delete_records_list('capabilities', 'name', $fields);
    
        upgrade_block_savepoint(true, 2018081806, 'supervision');
    }

    
    return true;
}
