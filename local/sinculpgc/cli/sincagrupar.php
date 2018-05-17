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
 * Sincronización de asignación de grupos en base de datos externa
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
*/
if (! defined('CLI_SCRIPT')) {
    define('CLI_SCRIPT', true);
}
require_once (__DIR__ . '/../../../config.php');
require_once ('../locallib.php');
require_once ($CFG->dirroot . '/group/lib.php');


/*
 * El número de movimientos es considerable, por lo que la consulta a la base de
 * datos externa provoca fallo de memoria en ocasiones.
 * Para evitarlo, se tratan los movimientos de grupo por categoría.
 */
// Obtención de categorías
$extdb = db_init();
$sqlcategorias = "SELECT idnumber AS categoria
                    FROM tmocategorias ca, tmoplataformasactivas p
                   WHERE p.plataforma = '{$CFG->plataforma}'
                         AND p.aacada = '{$CFG->aacada}'
                         AND p.id = ca.plataformaid";
$categorias = array_keys(get_rows_external_db($extdb, $sqlcategorias, 'categoria'));

foreach ($categorias as $categoria) {
    $category = $DB->get_field('course_categories', 'id', array(
        'idnumber' => $categoria
    ));


    $sqlasignacionesulpgc = "SELECT lower(u.username) || '|' || c.idnumber || '|' || g.cod_grupo AS asignacion, u.username, c.idnumber, g.cod_grupo, gu.estado
                               FROM tmoplataformasactivas p, tmocursos c, tmocategorias ca, tmogrupos g, tmogruposusuarios gu, tmousuarios u
                              WHERE p.aacada = '$CFG->aacada'
                                    AND p.plataforma = '$CFG->plataforma'
                                    AND ca.idnumber = '$categoria'
                                    and ca.plataformaid = p.id
                                    and c.categoriaid = ca.id
                                    and g.cursoid = c.id
                                    and gu.grupoid = g.id
                                    and u.id = gu.usuarioid
                             ORDER BY 1";

    $asignacionesulpgc = get_rows_external_db($extdb, $sqlasignacionesulpgc, 'asignacion');

    $sqlasignacionesmoodle = "SELECT concat(u.username, '|', c.idnumber, '|', g.idnumber)
                                FROM {groups_members} gm
                                     JOIN {groups} g ON g.id = gm.groupid
                                     JOIN {user} u ON u.id = gm.userid
                                     JOIN {course} c ON g.courseid = c.id
                               WHERE c.category = '$category' AND gm.component='enrol_sinculpgc'";

    $asignacionesmoodle = $DB->get_fieldset_sql($sqlasignacionesmoodle);
    $asignacionesadd = array_diff(array_keys(array_filter($asignacionesulpgc, function ($obj)
    {

        if ($obj->estado == 'I')
            return true;
    })), $asignacionesmoodle);

    $asignacionesdel = array_intersect(array_keys(array_filter($asignacionesulpgc, function ($obj)
    {
        if ($obj->estado == 'D')
            return true;
    })), $asignacionesmoodle);

    $asignacioneskeys = array_merge($asignacionesadd, $asignacionesdel);
    $asignaciones = array_intersect_key($asignacionesulpgc, array_flip($asignacioneskeys));

    if ((! isset($asignaciones)) or (count($asignaciones) == 0)) {
        continue;
    } else {
    }
    foreach ($asignaciones as $asignacion) {

        // Verificar existencia de alumno y curso
        $conditions = array(
            "username" => $asignacion->username
        );
        if (! $user = $DB->get_record("user", $conditions, "*")) {
            continue;
        }

        $conditions = array(
            "idnumber" => $asignacion->idnumber
        );
        if (! $course = $DB->get_record("course", $conditions, "*")) {
            continue;
        }

        // Get the enrol_sinculpgc instance for this course
        $enrol_instance = $DB->get_record('enrol', array(
            'courseid' => $course->id,
            'enrol' => 'sinculpgc'
        ));

        // Crea/recupera un objeto de contexto
        //
        if (! $context = context_course::instance($course->id)) {
            continue;
        }

        $conditions = array(
            "courseid" => $course->id,
            "idnumber" => $asignacion->cod_grupo
        );
        if (! $grupoid = $DB->get_field("groups", "id", $conditions)) {
            continue;
        }

        // Campo ESTADO = I indica una asignación a grupo existente
        if ($asignacion->estado == 'I') {

            // Al intentar añadir, puede que el usuario esté asignado al
            // grupo con enrol 'manual'.
            // Actualizar a enrol sinculpgc'.
            $conditions = array(
                "groupid" => $grupoid,
                "userid" => $user->id
            );
            $miembro = $DB->get_record('groups_members', $conditions, "*");
            if ($miembro) {
                // Si ya está asignado con enrol manual, cambiar a
                // sinculpgc
                if ($miembro->component != 'enrol_sinculpgc') {
                    $conditions = array(
                        'id' => $miembro->id
                    );
                    if (! $DB->set_field('groups_members', 'component', 'enrol_sinculpgc', $conditions)) {
                        continue;
                    } else {
                        continue;
                    }
                } else {
                    continue;
                }
            } else {
            // Añadir el valor de los parámetros component y itemid
                if (! groups_add_member($grupoid, $user->id, 'enrol_sinculpgc')) {
                }
            }
        }  // Fin de la asignación de grupo
          // Desasignar grupo
        else
            if ($asignacion->estado == 'D') {
                groups_remove_member($grupoid, $user->id, true);// ecastro ULPGC to avoid removal blockade by component
            } // Fin de la desasignación de grupos
    }
}
?>
