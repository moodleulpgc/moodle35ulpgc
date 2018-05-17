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
 * Sincronización de grupos en base de datos externa
 *
 * En base a los registros de una base de datos externa, se crean aquellos grupos
 * que no existen en Moodle y se eliminan aquellos que ya no existan en la
 * base de datos externa
 *
 * @package local_sinculpgc
 * @copyright 2016 Victor Deniz
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
if (! defined('CLI_SCRIPT')) {
    define('CLI_SCRIPT', true);
}

require_once (__DIR__ . '/../../../config.php');
require_once ('../locallib.php');
require_once ($CFG->libdir . '/grouplib.php');
require_once ($CFG->dirroot . '/group/lib.php');

global $USER;
global $DB;

// Obtención de registros en BBDD externa
$extdb = db_init();
$sqlgruposulpgc = "SELECT c.idnumber||'|'||cod_grupo AS idgrupo, c.idnumber AS courseidnumber, g.cod_grupo AS idnumber, g.nombre as name, g.desc_grupo AS description, g.estado
                     FROM tmogrupos g, tmocursos c, tmocategorias ca, tmoplataformasactivas p
                    WHERE g.CURSOID = c.id
                    and ca.id = c.CATEGORIAID
                    and p.id = ca.PLATAFORMAID
                    and p.plataforma = '{$CFG->plataforma}'
                    and p.aacada = '{$CFG->aacada}'";
$gruposulpgc = get_rows_external_db($extdb, $sqlgruposulpgc, 'idgrupo');
db_close($extdb);

// Obtención de registros en Campus Virtual
$sqlgruposcv = "SELECT concat(c.idnumber, '|', g.idnumber) AS idgrupo
                  FROM {$CFG->prefix}groups g, {$CFG->prefix}course c, {$CFG->prefix}local_ulpgcgroups ug
                 WHERE g.courseid = c.id
                       AND ug.groupid = g.id
                       AND ug.component = 'enrol_sinculpgc'";

$gruposcv = $DB->get_fieldset_sql($sqlgruposcv);

/* Registros a tratar (Insertar o Eliminar) */
// Registros que están en la bbdd externa y no en Moodle
$gruposadd = array_diff(array_keys(array_filter($gruposulpgc, function ($obj) {
    if ($obj->estado == 'I')
        return true;
})), $gruposcv);

// Registros a eliminar en la bbdd externa que están en Moodle
$gruposdel = array_intersect(array_keys(array_filter($gruposulpgc, function ($obj) {
    if ($obj->estado == 'D')
        return true;
})), $gruposcv);

// Combinación de registros
$gruposkeys = array_merge($gruposadd, $gruposdel);
$grupos = array_intersect_key($gruposulpgc, array_flip($gruposkeys));

if ((! isset($grupos)) or (count($grupos) == 0)) {} else {

    foreach ($grupos as $grupo) {

        // buscar el id del curso
        $conditions = array(
            'idnumber' => $grupo->courseidnumber
        );
        if (! $courseid = $DB->get_field('course', 'id', $conditions)) {
            continue;
        }

        $conditions = array(
            'courseid' => $courseid,
            'idnumber' => $grupo->idnumber
        );
        $group = $DB->get_record('groups', $conditions, "*");

        // / Get/creates the enrol_miulpgc instance for this course
        $enrol_instance = $DB->get_record('enrol', array(
            'courseid' => $courseid,
            'enrol' => 'sinculpgc'
        ));
        if (empty($enrol_instance)) {
            // Only add an enrol instance to the course if non-existent
            $course = new stdClass();
            $course->id = $courseid;
            $newenrol = enrol_get_plugin('sinculpgc');
            $enrolid = $newenrol->add_instance($course);
            $enrol_instance = $DB->get_record('enrol', array(
                'id' => $enrolid
            ));
        }
        $instance = $enrol_instance->id;
        $component = 'enrol_sinculpgc';

        // Crear grupo
        if ($grupo->estado == 'I') {

            // verificar que no existe un grupo con el mismo identificador en el
            // curso
            if (! $group) {
                $courseidnumber = $grupo->courseidnumber;
                // crear el grupo
                unset($grupo->action);
                unset($grupo->courseidnumber);
                $grupo->courseid = $courseid;

                // Solo Grado y Posgrado: añadir caracter # a los nombres de los
                // grupos de teoría
                if ($CFG->plataforma == 'tp' && ! strncasecmp('teor', $grupo->name, 4)) {
                    $grupo->name = '#' . $grupo->name;
                }

                $newgroup = new stdClass();
                $newgroup->groupid = groups_create_group($grupo, false, false, $component, $instance);
                $newgroup->component = $component;
                $newgroup->itemid = $instance;
                $newgroup->id = $DB->insert_record('local_ulpgcgroups', $newgroup);

                // si el campo enrol tiene el valor "manual" se cambia por "miulpgc"
            } else {
                // Comprobar. Si llega aquí, siempre tendrá valor en el campo idnumber
                if (is_null($group->idnumber)) {
                    $group->idnumber = $grupo->idnumber;
                    groups_update_group($group, false, false);
                }
                $conditions = array(
                    'groupid' => $group->id
                );
                $groupulpgc = $DB->get_record('local_ulpgcgroups', $conditions, "*");

                if (! $groupulpgc) {
                    $newgroup = new stdClass();
                    $newgroup->groupid = $group->id;
                    $newgroup->component = $component;
                    $newgroup->itemid = $instance;
                    $newgroup->id = $DB->insert_record('local_ulpgcgroups', $newgroup);
                } else {
                    if (($groupulpgc->component != $component) || ($groupulpgc->itemid != $instance)) {
                        $groupulpgc->component = $component;
                        $groupulpgc->itemid = $instance;
                        groups_update_group($group, false, false);
                    }
               }
            }
        }  // Fin crear grupo
          // Eliminar grupo
        else
            if ($grupo->estado == 'D') {
                if ($group) {
                    if (! groups_delete_group($group->id, $component, $instance)) {

                    } else {
                        $conditions = array(
                            'groupid' => $group->id
                        );
                        $DB->delete_records('local_ulpgcgroups', $conditions);
                    }
                } else {
                }
            } // Fin eliminar grupo
    }
}
?>
