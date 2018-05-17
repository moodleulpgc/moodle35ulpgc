<?php

function xmldb_block_supervision_uninstall() {
    global $DB;

    $select = $DB->sql_like('plugin', ':warning');
    $params = array('warning'=>'supervisionwarning_%');

    $DB->delete_records_select('config_plugins', $select, $params);

    return true;

}

