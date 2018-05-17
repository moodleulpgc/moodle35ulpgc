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
 * Sincronización de unidades en base de datos externa
 *
 * En Moodle no existe el concepto de unidades, po lo que es necesario crear
 * una nueva tabla (ver fichero db/install.xml en esta misma extensión).
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

global $DB;

// Obtención de registros en BBDD externa
$sqlunidadesulpgc = " SELECT codigo                                AS idnumber ,
    denominacion                              AS name     ,
    tipo,
    PACKUSUARIOS_API.IDAUSUARIO( directorid ) AS director ,
    PACKUSUARIOS_API.IDAUSUARIO( directorid ) AS secretary,
    estado
   FROM tmounidades u
  WHERE estado='I'";
$extdb = db_init();
$unidadesulpgc = get_rows_external_db($extdb, $sqlunidadesulpgc, 'idnumber');
db_close($extdb);

// Obtención de registros en Campus Virtual
$sqlunidadescv = "SELECT idnumber
                   FROM {local_sinculpgc_units}";
$unidadescv = $DB->get_fieldset_sql($sqlunidadescv);

// Registros a tratar (Insertar o Eliminar)
$unidadeskeys = array_diff(array_keys($unidadesulpgc), $unidadescv);
$unidades = array_intersect_key($unidadesulpgc, array_flip($unidadeskeys));

// Si no hay unidades a tratar finaliza el script
if ((! isset($unidades)) or (count($unidades) == 0)) {
    return;
} else {

    // Tratamiento de cada registro
    foreach ($unidades as $unidad) {
        $unidad->type = $unidad->tipo;
        // Registro a insertar
        if ($unidad->estado == 'I') {

            $DB->insert_record('local_sinculpgc_units', $unidad);
        }  // Registro a eliminar
else
            if ($unidad->estado == 'D') {
                $conditions = array(
                    'idnumber' => $unidad->idnumber,
                    'type' => $unidad->tipo
                );
                $DB->delete_records('local_sinculpgc_units', $conditions);
            }
    }
}

?>