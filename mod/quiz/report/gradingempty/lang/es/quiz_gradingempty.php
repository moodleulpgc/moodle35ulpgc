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
 * Strings for component 'quiz_gradingempty', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   quiz_gradingempty
 * @copyright 2018 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['alreadygraded'] = 'Ya calificado';
$string['cannotloadquestioninfo'] = 'No se ha podido cargar la información sobre el tipo de pregunta específica';
$string['cannotgradethisattempt'] = 'No se puede calificar este intento.';
$string['essayonly'] = 'Las siguientes preguntas necesitan calificarse manualmente';
$string['invalidquestionid'] = 'No se encuentr apegunta calificable con ID {$a}';
$string['invalidattemptid'] = 'No existe tal ID de Intento';
$string['grade'] = 'calificar';
$string['gradeall'] = 'calificar todos';
$string['gradeattemptsall'] = 'Todos ({$a})';
$string['graded'] = '(calificado)';
$string['gradingempty'] = 'Autocalificar ensayos vacíos';
$string['grading:componentname'] = 'Autocalificar ensayos vacíos';
$string['gradingattempt'] = 'Intento número {$a->attempt} de {$a->fullname}.';
$string['gradingnotallowed'] = 'No tiene permiso para calificar manualmente las respuestas de este cuestionario';
$string['gradingquestionx'] = 'Calificación de la pregunta {$a->number}: {$a->questionname}';
$string['gradingreport'] = 'Informe de calificación manual';
$string['gradingungraded'] = '{$a} intentos no calificados';
$string['inprogress'] = 'En curso';
$string['nothingfound'] = 'Nada que mostrar';
$string['options'] = 'Opciones';
$string['pluginname'] = 'Autocalificar ensayos vacíos';
$string['qno'] = 'Q #';
$string['questionname'] = 'Nombre de la pregunta';
$string['questionsthatneedgrading'] = 'Preguntas que requieren calificación';
$string['questiontitle'] = 'Pregunta {$a->number} : "{$a->name}" ({$a->openspan}{$a->gradedattempts}{$a->closespan} / {$a->totalattempts} intentos {$a->openspan}graded{$a->closespan}).';
$string['statecount'] = '{$a->counts} / {$a->empties} vacías ';
$string['tograde'] = 'Para calificar';
$string['total'] = 'Total';
$string['unknownquestion'] = 'Preguntas desconocidas';
$string['updategrade'] = 'actualizar calificaciones';
