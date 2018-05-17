<?php
if (! defined('CLI_SCRIPT')) {
    define('CLI_SCRIPT', true);
}
require_once (__DIR__ . '/../../../config.php');

//$inicio = date('H:i:s');
//$inicio = time();
$enrol = enrol_get_plugin('sinculpgc');
$num = $enrol->sync_enrolments();
//$fin = date('H:i:s');
//$fin = time();
//$output = array ('Matrículas', date('H:i:s', $inicio), date('H:i:s', $fin), $fin-$inicio, $num);
//echo json_encode($output);
?>
