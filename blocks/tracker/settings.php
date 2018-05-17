<?php
require_once($CFG->dirroot.'/mod/tracker/lib.php');


$STATUSKEYS = array(POSTED => get_string('posted', 'tracker'),
                    OPEN => get_string('open', 'tracker'),
                    RESOLVING => get_string('resolving', 'tracker'),
                    WAITING => get_string('waiting', 'tracker'),
                    TESTING => get_string('testing', 'tracker'),
                    RESOLVED => get_string('resolved', 'tracker'),
                    ABANDONNED => get_string('abandonned', 'tracker'),
                    TRANSFERED => get_string('transfered', 'tracker'));

$settings->add(new admin_setting_configmultiselect('block_tracker/status', get_string('status', 'block_tracker'),
                                                   get_string('configstatus', 'block_tracker'), array(POSTED, OPEN, RESOLVING, WAITING, RESOLVED, TESTING), $STATUSKEYS));

$options = array(0=>get_string('none'));

// required during installation
$dbman = $DB->get_manager();
$table = new xmldb_table('tracker');
if($dbman->table_exists($table)) {
    if($trackers = $DB->get_records_menu('tracker', null, '', 'id, name')) {
        $options = $options + $trackers;
    }
}

$settings->add(new admin_setting_configselect('block_tracker/tracker', get_string('tracker', 'block_tracker'),
                   get_string('configtracker', 'block_tracker'), 0, $options));

?>
