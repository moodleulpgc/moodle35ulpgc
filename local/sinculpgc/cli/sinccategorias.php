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
 * Sincronización de categorías en base de datos externa
 *
 * En base a los registros de una base de datos externa, se crean aquellas categorías
 * que no existen en Moodle y se eliminan (ocultan) aquellas que ya no existan en la
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
require_once ($CFG->dirroot . '/course/lib.php');
require_once ('../locallib.php');

global $USER;
global $DB;

// Obtención de registros en BBDD externa
$extdb = db_init();
$sqlcategoriasulpgc = "SELECT denominacion AS name, idnumber, superior, estado
               FROM tmocategorias ca, tmoplataformasactivas p
              WHERE p.id = ca.plataformaid
                    AND p.plataforma = '{$CFG->plataforma}'
           ORDER BY nivel ASC";
$categoriasulpgc = get_rows_external_db($extdb, $sqlcategoriasulpgc, 'idnumber');
db_close($extdb);

// Obtención de registros en Campus Virtual
$sqlcategoriascv = "SELECT idnumber
                      FROM {$CFG->prefix}course_categories
                     WHERE idnumber IS NOT NULL";
$categoriascv = $DB->get_fieldset_sql($sqlcategoriascv);

/* Registros a tratar (Insertar o Eliminar) */
// Registros que están en la bbdd externa y no en Moodle
$categoriasadd = array_diff(array_keys(array_filter($categoriasulpgc, function ($obj) {
    if ($obj->estado == 'I')
        return true;
})), $categoriascv);

// Registros a eliminar en la bbdd externa que están en Moodle
$categoriasdel = array_intersect(array_keys(array_filter($categoriasulpgc, function ($obj) {
    if ($obj->estado == 'D')
        return true;
})), $categoriascv);

// Combinación de registros
$categoriaskeys = array_merge($categoriasadd, $categoriasdel);
$categorias = array_intersect_key($categoriasulpgc, array_flip($categoriaskeys));

if ((! isset($categorias)) or (count($categorias) == 0)) {
    // add_to_log(SITEID, 'categories', 'sync', '', 'No hay categorias definidas', 0, $USER->id);
} else {
    // add_to_log(SITEID, 'categories', 'sync', '', 'Categorías a tratar: ' . count($categorias), 0, $USER->id);

    foreach ($categorias as $categoria) {
        // ¿Existe la categoria?
        $conditions = array(
            "idnumber" => $categoria->idnumber
        );
        $categoriaencv = $DB->get_record('course_categories', $conditions);

        // Categoría a añadir
        if ($categoria->estado == 'I') {
            // Si la categoría existe y está oculta se hace visible, junto con los
            // cursos que contiene
            if ($categoriaencv) {
                if ($categoriaencv) {
                    if (! $categoriaencv->visible == 1) {
                        if (! $DB->set_field('course_categories', 'visible', '1', array(
                            'id' => $categoriaencv->id
                        ))) {
                            continue; // No se hacen visibles los cursos de la categoría
                        }
                        if (! $DB->set_field('course', 'visible', '1', array(
                            'category' => $categoriaencv->id
                        ))) {}
                    }
                }
                // Si la categoría no existe, se crea
            } else {
                // Si no es una categoría de primer nivel, verificar que existe la categoría a la que pertenece
                if (! $categoria->superior == 0) {
                    $conditions = array(
                        'idnumber' => $categoria->superior
                    );
                    if (! $categoria->parent = $DB->get_field('course_categories', 'id', $conditions)) {
                        continue;
                    }
                }
                $categoria->sortorder = 999;

                try {
                    $categoria->timemodified = time();
                    $categoria->id = $DB->insert_record('course_categories', $categoria);
                    if (strpos($categoria->idnumber, '_')) {
                        list ($faculty, $degree) = explode('_', $categoria->idnumber);
                        $categoriaulpgc = new stdClass();
                        $categoriaulpgc->faculty = $faculty;
                        $categoriaulpgc->degree = $degree;
                        $categoriaulpgc->categoryid = $categoria->id;
                        $categoriaulpgc->id = $DB->insert_record('local_ulpgccore_categories', $categoriaulpgc);
                    }
                } catch (Exception $e) {
                    continue;
                }
                $categoria->context = context_coursecat::instance($categoria->id);
                $categoria->context->mark_dirty();
                fix_course_sortorder(); // Required to build course_categories.depth and .path.
            }
        }

        // Categoría a ocultar (eliminar). No se eliminan para evitar pérdidas de información
        else
            if ($categoria->estado == 'D') {
                // Si existe la categoria se ocultan la categoria y sus cursos
                // Ocultar categoría y cursos
                if ($categoriaencv) {
                    $conditions = array(
                        'id' => $categoriaencv->id
                    );
                    if (! $DB->set_field('course_categories', 'visible', '0', $conditions)) {}
                    $conditions = array(
                        'category' => $categoriaencv->id
                    );
                    if (! $DB->set_field('course', 'visible', '0', $conditions)) {}
                }
            }
    }
}
?>
