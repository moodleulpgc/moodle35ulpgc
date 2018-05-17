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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Sincronización de asignación de cohortes en base de datos externa
 *
 * En base a los registros de una base de datos externa,
 * se asignan o desasignan grupos a los usuarios.
 *
 * IMPRESCINDIBLE ANTES DE EJECUTAR QUE TODOS LOS GRUPOS TENGAN DEFINIDO EL CAMPO IDNUMBER
 * SOLO SE MODIFICAN LOS GRUPO CON CAMPO ENROL='sinculpgc'
 *
 * @package local_sinculpgc
 * @copyright 2016 Victor Deniz
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 *
 */
if (! defined('CLI_SCRIPT')) {
    define('CLI_SCRIPT', true);
}
require_once (__DIR__ . '/../../../config.php');
require_once ('../locallib.php');
require_once ($CFG->dirroot . '/cohort/lib.php');

$extdb = db_init();

$sqlcohortesulpgc = "SELECT co.*
                  FROM tmocohortes co, tmoplataformasactivas p
                 WHERE co.plataformaid = p.id
                       AND p.plataforma = '{$CFG->plataforma}'
                       AND p.aacada     = '$CFG->aacada'";
$cohortesulpgc = get_rows_external_db($extdb, $sqlcohortesulpgc, 'id');

foreach ($cohortesulpgc as $cohorteulpgc) {
    $sqlasignacionesulpgc = "SELECT u.USERNAME || '|' || co.IDNUMBER  as asignacion, u.USERNAME, co.IDNUMBER, cu.ESTADO
                               FROM tmocohortesusuarios cu,
                                    tmocohortes co,
                                    tmousuarios u
                              WHERE co.id      = cu.cohorteid
                                    AND co.id = $cohorteulpgc->id
                                    AND u.id = cu.usuarioid";

    $asignacionesulpgc = get_rows_external_db($extdb, $sqlasignacionesulpgc, 'asignacion');

    $sqlasignacionesmoodle = "SELECT concat(u.username, '|', co.idnumber)
                                FROM {cohort_members} cm
                                     JOIN {cohort} co ON co.id = cm.cohortid
                                     JOIN {user} u ON u.id = cm.userid
                               WHERE co.idnumber = '$cohorteulpgc->idnumber'";

    $asignacionesmoodle = $DB->get_fieldset_sql($sqlasignacionesmoodle);
    $asignacionesadd = array_diff(array_keys(array_filter($asignacionesulpgc, function ($obj) {

        if ($obj->estado != 'D')
            return true;
    })), $asignacionesmoodle);

    $asignacionesdel = array_intersect(array_keys(array_filter($asignacionesulpgc, function ($obj) {
        if ($obj->estado == 'D')
            return true;
    })), $asignacionesmoodle);

    $asignacioneskeys = array_merge($asignacionesadd, $asignacionesdel);
    $asignaciones = array_intersect_key($asignacionesulpgc, array_flip($asignacioneskeys));

    if ((! isset($asignaciones)) or (count($asignaciones) == 0)) {} else {}
    foreach ($asignaciones as $asignacion) {

        // Verificar existencia de alumno
        $conditions = array(
            "username" => $asignacion->username
        );
        if (! $user = $DB->get_record("user", $conditions, "*")) {
            continue;
        }

        $conditions = array(
            "idnumber" => $asignacion->idnumber
        );
        if (! $cohorteid = $DB->get_field("cohort", "id", $conditions)) {
            continue;
        }

        // Campo ESTADO = I indica una asignación a cohorte existente
        if ($asignacion->estado == 'I') {
            $conditions = array(
                "cohortid" => $cohorteid,
                "userid" => $user->id
            );
            $miembro = $DB->get_record('cohort_members', $conditions, "*");
            if ($miembro) {} else {
                // Añadir el valor de los parámetros component y itemid
                if (! cohort_add_member($cohorteid, $user->id)) {}
            }
        }  // Fin de la asignación de cohorte
          // Desasignar cohorte
        else
            if ($asignacion->estado == 'D') {
                cohort_remove_member($cohorteid, $user->id);
            } // Fin de la desasignación de cohorte
    }
}
?>
