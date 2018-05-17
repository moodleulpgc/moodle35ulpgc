<?php // $Id: borrar_foro_zabbix.php, v 1.0 2009-10-09
/**
 * Script para borrar los post de un foro de prueba, enviados por el zabbix para evaluar el rendimiento de la plataforma
 * @author José Luis
 */

define ( 'CLI_SCRIPT', true );

require_once(__DIR__ . '/../../config.php');

if ($CFG->zabbix_post_discussion) {
 $DB->delete_records('forum_posts', array('discussion'=>$CFG->zabbix_post_discussion, 'parent'=>$CFG->zabbix_post_parent));
}
?>
