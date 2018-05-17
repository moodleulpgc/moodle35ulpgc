<?php
if (! defined('CLI_SCRIPT')) {
    define('CLI_SCRIPT', true);
}
require_once (__DIR__ . '/../../../config.php');

//$inicio = date('H:i:s');
//$inicio = time();
$casulpgcauth = get_auth_plugin('casulpgc');
$num = $casulpgcauth->sync_users();
//$fin = date('H:i:s');
//$fin = time();
//$output = array ('Usuarios', date('H:i:s', $inicio), date('H:i:s', $fin), $fin-$inicio, $num);
//echo json_encode($output);
?>
