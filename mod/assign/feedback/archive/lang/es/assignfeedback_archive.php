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
 * Strings for component 'feedback_archive', language 'en'
 *
 * @package   assignfeedback_archive
 * @copyright 2016 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['default'] = 'Activado por defecto';
$string['default_help'] = 'Si se habilita, la herramienta de Archivo estará activa de forma predeterminada en todas las nuevas Tareas agregadas.';
$string['enabled'] = 'Reapertura';
$string['enabled_help'] = 'Si se habilita, los estudiantes podrán archivar su entrega después de ser calificada, 
para así poder re-enviar otra vez nuevas versiones después de la entrega previa. Sólo se aplica en caso de envíos con confirmación explícita. 

Si se activa el re-envío hasta aprobar se desactiva esta herramienta automáticamente.';
$string['pluginname'] = 'Archivo y Reapertura';
$string['archive:store'] = 'Archivar una entrega';
$string['maxattemptsreached'] = 'Se ha alcanzado el máximo de entregas. Ya no es posible re-enviar nuevos intentos.';
$string['reopen'] = 'Archivar y reabrir';
$string['reopen_help'] = 'Necesita archivar esta entrega previa de forma que pueda re-abrir la Tarea para una nueva entrega.';
$string['reopenconfirm'] = 'Archive esta entrega y reabra para nueva entrega';
$string['updategraded'] = 'Confirmación automática tras calificación';
$string['updategraded_help'] = 'Si se habilita, entonces en aquellos casos en los que un estudiante haya recibido una calificación antes de haber confirmado una entrega
(calificado en estado borrador) cuando el estudiante revise su calificación automáticamente se confirmará su entrega borrado o fantasma.';
$string['crontask'] = 'Enviar borradores después de plazo de entrega';
$string['eventsubmissionarchived'] = 'Entrega archivada';
$string['noarchiveallowed'] = 'No se permiten más re-entregas.';
$string['waitgrading'] = 'La entrega debe estar calificada para poder ser archivada.';
