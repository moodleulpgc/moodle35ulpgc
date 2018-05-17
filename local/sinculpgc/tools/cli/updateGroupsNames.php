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
 * Actualización de nombres de grupos acorde a base de datos externa
 *
 * En base a los registros de una base de datos externa, se modifican los nombres
 * de los grupos en Moodle que tengan el mismo código en el mismo curso
 *
 * @package local_sinculpgc
 * @copyright 2016 Victor Deniz
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
if (! defined('CLI_SCRIPT')) {
    define('CLI_SCRIPT', true);
}

require_once (__DIR__ . '/../../../../config.php');
require_once ('../../locallib.php');
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
                    and p.aacada = '{$CFG->aacada}'
                    and g.estado = 'I'";
$gruposulpgc = get_rows_external_db($extdb, $sqlgruposulpgc, 'idgrupo');
db_close($extdb);

// Obtención de registros en Campus Virtual
$sqlgruposcv = "SELECT concat(c.idnumber, '|', g.idnumber) AS idgrupo
                  FROM {$CFG->prefix}groups g, {$CFG->prefix}course c, {$CFG->prefix}local_ulpgcgroups ug
                 WHERE g.courseid = c.id
                       AND ug.groupid = g.id
                       AND ug.component = 'enrol_sinculpgc'";

$gruposcv = $DB->get_fieldset_sql($sqlgruposcv);

// Registros a eliminar en la bbdd externa que están en Moodle
$gruposupd = array_intersect(array_keys($gruposulpgc), $gruposcv);

$grupos = array_intersect_key($gruposulpgc, array_flip($gruposupd));


foreach ($grupos as $grupo) {
    $cursoid = $DB->get_field('course', 'id', array(
        'idnumber' => $grupo->courseidnumber
    ));
    $grupocv = $DB->get_record('groups', array(
        'courseid' => $cursoid,
        'idnumber' => $grupo->idnumber
    ));

    // Solo Grado y Posgrado: añadir caracter # a los nombres de los
    // grupos de teoría
    if ($CFG->plataforma == 'tp' && ! strncasecmp('teor', $grupo->name, 4)) {
        $grupo->name = '#' . $grupo->name;
    }

    if  (strcmp($grupocv->name, $grupo->name)!== 0) {
        $gruponuevonombre = new stdClass();
        $gruponuevonombre = $grupocv;
        $gruponuevonombre->name = $grupo->name;
        $gruponuevonombre->description = $grupo->description;
        $DB->update_record('groups', $gruponuevonombre);
    } else {
        continue;
    }
}
?>