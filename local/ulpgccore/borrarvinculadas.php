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
 * Eliminar asignaturas vinculadas que se hayan creado en el Campus Virtual
 *
 * En el Campus Virtual solo deben existir asignaturas maestras, aquellas que
 * tienen docencia asignadas. En ocasiones, por error en el proceso de
 * sincronización o porque las vinculaciones se definen con posterioridad a
 * la creación del curso en la plataforma, los cuross de las asignaturas vinculadas
 * se crean en la plataforma
 *
 * @package local_sinculpgc
 * @copyright 2016 Victor Deniz
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
if (! defined('CLI_SCRIPT')) {
    define('CLI_SCRIPT', true);
}

require_once (__DIR__ . '/../../config.php');
require_once ($CFG->dirroot . '/course/lib.php');
require_once ('sinculpgc/locallib.php');

global $DB;

// Obtención de registros en BBDD externa
$extdb = db_init();
$sqlcursosulpgc = "SELECT c.idnumber
                     FROM tmocursos c, tmocategorias ca, tmoplataformasactivas p, tmounidades u
                    WHERE ca.id = c.categoriaid
                      AND p.id = ca.plataformaid
                      AND u.id(+) = c.departamentoid
                      AND u.tipo(+) = 'departamento'
                      AND p.plataforma = '{$CFG->plataforma}'
                      AND vinculo IS NOT NULL";
$cursosulpgc = get_rows_external_db($extdb, $sqlcursosulpgc, 'idnumber');
db_close($extdb);

$cursos = $cursosulpgc;

if ((! isset($cursos)) or (count($cursos) == 0)) {} else {
    $where = 'shortname like \'%teacher%\'';
    $rolesteacher = $DB->get_records_select('role', $where);
    $rolesteacher = array_keys($rolesteacher);

    foreach ($cursos as $curso) {

        $cursoencv = $DB->get_record('course', array(
            'idnumber' => $curso->idnumber
        ));

        // Curso a eliminar (se oculta para evitar pérdidas de información)
        if ($cursoencv) {
            $context = context_course::instance($cursoencv->id);
            if (! $context) {
                continue;
            }
            if (count_role_users($rolesteacher, $context) == 0) {
               echo $cursoencv->idnumber.':';
               echo delete_course($cursoencv->id, true);
               echo "\n";
            }
        }
    }
}
?>
