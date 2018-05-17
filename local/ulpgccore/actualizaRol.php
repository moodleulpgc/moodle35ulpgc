<?php

/**
 * Actualiza al rol XX de los usuarios del curso YY
 * Este script se hizo a medida para solucionar una incidencia.
 * Se enrolaron usuarios con un rol que no se había definido, por lo que estaban enrolados sin ningún rol específico.
 */

if (! defined ( 'CLI_SCRIPT' )) {
	define ( 'CLI_SCRIPT', true );
}
require_once (dirname ( __FILE__ ) . '/../../config.php');

$roleid = 14; // Id del rol centrestaff

$sql = "SELECT ue.userid, x.id
FROM mdl_user_enrolments ue, mdl_enrol e, mdl_course c, mdl_context x
WHERE ue.enrolid = e.id AND e.courseid = c.id AND c.shortname LIKE '555_%' AND e.enrol = 'miulpgc' AND x.instanceid = c.id AND x.contextlevel = '50'
and not exists (select id from mdl_role_assignments where userid = ue.userid and contextid = x.id)";

$users = $DB->get_records_sql ( $sql );

foreach ( $users as $user ) {
	role_assign ( $roleid, $user->userid, $user->id, 'enrol_miulpgc');
}
?>