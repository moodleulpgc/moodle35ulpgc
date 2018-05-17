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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for the quizaccess_makeexamlock plugin.
 *
 * @package    quizaccess
 * @subpackage makeexamlock
 * @copyright  2016 Enrique Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


$string['makeexamlockingmsg'] = 'No se permiten intentos por usuarios. Use "<strong>Editar cuestionario</strong>" para añadir preguntas y componer un cuestionario. Luego vaya a "<strong>Crear Examen</strong>". ';
$string['pluginname'] = 'Bloqueo por Crear Examen';
$string['gotomakeexam'] = 'Use <strong>Crear Examen</strong> para generar una versión de examen';
$string['makeexamlock'] = 'Bloqueo Crear Examen';
$string['makeexamlock_help'] = 'El bloqueo Crear Examen permite prevenir cualquier acceso de estudiantes a este cuestionario y sus preguntas.
Crear Examen es un ayudanet para generar PDFs de Examen (trabajando con el módulo Registro de Exámenes).

Si se activa, entonces no se permitirá ningún acceso por estudiantes. Sólo los Profesores podrán acceder para añadir y editar preguntas y componer exámenes.';
$string['explainmakeexamlock'] = 'NO se permiten intentos por estudiantes. Cuestionario usado sólo para generar exámenes con Crear Examen.';

