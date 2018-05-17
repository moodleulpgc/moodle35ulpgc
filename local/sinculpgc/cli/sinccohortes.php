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
 * Sincronización de cohortes en base de datos externa
 *
 * En base a los registros de una base de datos externa, se crean aquellas cohortes
 * que no existen en Moodle y se eliminan aquellas que ya no existan en la
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
require_once ($CFG->dirroot . '/cohort/lib.php');

global $USER;
global $DB;

// Obtención de registros en BBDD externa
$plataforma = ($CFG->plataforma=='so')?'tp':$CFG->plataforma;
$extdb = db_init();
$sqlcohortesulpgc = "SELECT co.idnumber, co.description, co.nombre as name, co.estado
                     FROM tmocohortes co, tmoplataformasactivas p
                    WHERE co.plataformaid = p.id
                          AND p.plataforma = '$plataforma'
                          AND p.aacada = '{$CFG->aacada}'";
$cohortesulpgc = get_rows_external_db($extdb, $sqlcohortesulpgc, 'idnumber');
db_close($extdb);

// Obtención de registros en Campus Virtual
$sqlcohortescv = "SELECT co.idnumber
                  FROM {$CFG->prefix}cohort co";
$cohortescv = $DB->get_fieldset_sql($sqlcohortescv);

/* Registros a tratar (Insertar o Eliminar) */
// Registros que están en la bbdd externa y no en Moodle
$cohortesadd = array_diff(array_keys(array_filter($cohortesulpgc, function ($obj) {
    if ($obj->estado == 'I')
        return true;
})), $cohortescv);

// Registros a eliminar en la bbdd externa que están en Moodle
$cohortesdel = array_intersect(array_keys(array_filter($cohortesulpgc, function ($obj) {
    if ($obj->estado == 'D')
        return true;
})), $cohortescv);

// Combinación de registros
$cohorteskeys = array_merge($cohortesadd, $cohortesdel);
$cohortes = array_intersect_key($cohortesulpgc, array_flip($cohorteskeys));

if ((! isset($cohortes)) or (count($cohortes) == 0)) {} else {

    $component = 'enrol_sinculpgc';
    // Las cohortes se crean a nivel de sitio
    $contextid = 1;

    foreach ($cohortes as $cohorte) {

        $conditions = array(
            'idnumber' => $cohorte->idnumber
        );
        $cohort = $DB->get_record('cohort', $conditions, "*");



        // Crear cohorte
        if ($cohorte->estado == 'I') {

            // verificar que no existe una cohorte con el mismo identificador
            if (! $cohort) {
                // crear la cohorte
                unset($cohorte->estado);
                $cohorte->component = $component;
                $cohorte->contextid = $contextid;

                $newcohorte = new stdClass();
                $newcohorte->id = cohort_add_cohort($cohorte);

                // si el campo component no tiene el valor "manual" se cambia por "miulpgc"
            } else {
                if ($cohort->component == '') {
                    $cohort->component = $component;
                    cohort_update_cohort($cohort);
                }
            }
        }  // Fin crear cohorte
          // Eliminar cohorte
        else
            if ($cohorte->estado == 'D') {
                if ($cohort) {
                    cohort_delete_cohort($cohort);
                } // Fin eliminar cohorte
            }
    }
}
?>
