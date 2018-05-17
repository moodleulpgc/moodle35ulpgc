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
 * Funciones necesarias para la sincronización con base de datos externa
 *
 * Este archivo contiene las funciones necesarias para conectarse con una base de datos
 * externa y obtener información de la misma.
 *
 * @package local_sinculpgc
 * @copyright 2014 Victor Deniz
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once ($CFG->libdir . '/adodb/adodb.inc.php');

/**
 * Codifica en UTF-8 las cadenas recibidas.
 * Acepta una cadena, un array o un objeto. En los dos últimos casos convierte todos
 * las cadenas del elemento pasado por parámetro.
 *
 * @param mixed $input
 *            Elemento cuyas cadenas se codificarán en UTF-8
 */
function utf8_encode_deep(&$input)
{
    if (is_string($input)) {
        $input = utf8_encode($input);
    } else
        if (is_array($input)) {
            foreach ($input as &$value) {
                utf8_encode_deep($value);
            }

            unset($value);
        } else
            if (is_object($input)) {
                $vars = array_keys(get_object_vars($input));

                foreach ($vars as $var) {
                    utf8_encode_deep($input->$var);
                }
            }
}

/**
 * Establece conexión con la base de datos externa.
 *
 * @return null|ADONewConnection se establece la conexión, devuelve un objeto que representa la conexión
 */
function db_init()
{
    global $CFG;

    // Conecta con la base de datos externa (fuerza una nueva conexión)
    $dbconexion = ADONewConnection($CFG->ulpgcdbtype);

    $dbconexion->charSet = $CFG->ulpgccharset;
    $dbconexion->Connect($CFG->ulpgcdbhost, $CFG->ulpgcdbuser, $CFG->ulpgcdbpass, $CFG->ulpgcdbname, true);

    $dbconexion->SetFetchMode(ADODB_FETCH_ASSOC);

    return $dbconexion;
}

/**
 * Ejecuta una sentencia en la base de datos externa. No devuelve registros.
 * Orientada a operaciones de inserción o actualización.
 *
 * @param ADONewConnection $database
 *            Objeto que representa la conexión a la base de datos externa
 * @param string $consulta
 *            Sentencia a ejecutar en la base de datos externa
 * @return boolean Devuelve true si se ejecuta con éxito, false en caso contrario
 */
function execute_external_db($database, $consulta)
{
    global $CFG;

    $rs = $database->Execute($consulta);

    if (! $rs) {
        print_error('auth_dbcantconnect', 'auth_db');
        return false;
    }

    return true;
}

/**
 * Devuelve los registros obtenidos al realizar una consulta en la base de datos externa.
 *
 * @param ADONewConnection $conexion Objeto que representa la conexión a la base de datos externa
 * @param string $consulta Consulta a realizar en la base de datos externa
 * @param string $id Columna que hace las veces de identificador único
 * @return object[] Array de objetos con los registros devueltos por la consulta
 */
function get_rows_external_db($conexion, $consulta, $id)
{
    global $CFG;

    $result = array();

    $rs = $conexion->Execute($consulta);

    if (! $rs) {
        print_error('auth_dbcantconnect', 'auth_db');
    } else
        if (! $rs->EOF) {
            while ($rec = $rs->FetchRow()) {
                $rec = (object) array_change_key_case((array) $rec, CASE_LOWER);
                utf8_encode_deep($rec);
                $result [$rec->$id]= $rec;
            }
        }

    return $result;
}

/**
 * Cierra la conexión con la base de datos externa
 *
 * @param ADONewConnection $conexion Objeto que representa la conexión a la base de datos
 * @return null
 */
function db_close($conexion)
{
    $conexion->Close();
    return;
}
?>
