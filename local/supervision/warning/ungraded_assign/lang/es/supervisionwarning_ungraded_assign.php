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
 * Strings for component 'supervisionwarning_ungraded_assign', language 'en'
 *
 * @package   supervisionwarning_ungraded_assign
 * @copyright  2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Tareas sin corregir';
$string['config_pluginname'] = 'Marcar para activar la detección de Entregas en tareas que permanecen sin calificar por los profesores de un curso después del periodo definido';
$string['threshold'] = 'Umbral de retraso';
$string['config_threshold'] = 'El periodo a partir del cual si continúa sin corregir se marcará la entrega como una incidencia de supervisión, en <strong>DÍAS</strong>.';
$string['grading'] = 'Tipo de calificación';
$string['config_grading'] = 'La comprobación se puede limitar a sólo las Tareas evaluadas, o bien las calificadas numéricamente, o mediante una escala';
$string['weekends'] = 'Excluir fines de semana';
$string['config_weekends'] = 'Si se activa, se excuirán los fines de semana en el chequeo del posible retraso a partir del umbral indicado.';

$string['graded'] = 'Calificada';
$string['gradenumeric'] = 'Calificación numérica';
$string['gradescale'] = 'Escala de calificación';
$string['countwarnings'] = '{$a->num} Tareas pendientes en {$a->coursename}';
