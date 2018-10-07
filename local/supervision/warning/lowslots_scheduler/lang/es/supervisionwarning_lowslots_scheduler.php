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
 * Strings for component 'supervisionwarning_lowslots_scheduler', language 'en'
 *
 * @package   supervisionwarning_lowslots_scheduler
 * @copyright  2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Tutorías insuficientes';
$string['config_pluginname'] = 'Marcar para activar la detección de Reuniones con horas de tutoría insuficientes según lo especificado en el módulo Reunión.';
$string['threshold'] = 'Umbral semanal';
$string['config_threshold'] = 'El nº de horas mínimo que debe ser ofertado semanalmente en franjas de Reuniones. Si la suma de horas entre las diferentes franjas ofertadas en la semana actual es inferior, se creará un avisos de supervisión. El ajuste representa <strong>HORAS</strong> semanales.';
$string['countwarnings'] = '{$a->num} Franjas necesarias en {$a->coursename}';
