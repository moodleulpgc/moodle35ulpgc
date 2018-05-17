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
 * Strings for component 'submission_peers', language 'en'
 *
 * @package assignsubmission_peers
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['default'] = 'Enabled by default';
$string['default_help'] = 'If set, this submission method will be enabled by default for all new assignments.';
$string['enabled'] = 'Ver envíos de compañeros';
$string['enabled_help'] = '
Si se activa, los estudiantes tendrán un enlace para acceder a las entregas realizadas por otros compañeros.
Un usuario sólo podrá ver las entregas de compañeros después de haber enviado su propio trabajo y, eventualmente, haber sido calificado.
';
$string['pluginname'] = 'Entregas de compañeros';
$string['view'] = 'Entregas de compañeros';
$string['table'] = 'Entregas de compañeros';
$string['viewother'] = 'Entrega de compañero';


$string['limitbymode'] = 'Mostrar entregas de compañeros';
$string['limitbymode_help'] = '
This option determine when are peers submissions showed to a given student.
A required condition is to have submitted oneself. After that the peers submissions lists may by displayed:

* Right after the student has subbmited his own work in final form (no more submissions allowed)
* Only after a teacher grade the submission by the student
* Right after the assignment deadline (without checking grades or submission status)

';
$string['limitbyfinal'] = 'después de la entrega final propia';
$string['limitbygrade'] = 'después de ser calificado';
$string['limitbytime'] = 'después del fin del plazo de entrega';
$string['limitbysubmission'] = 'después de la entrega propia';
$string['viewpeersno'] = 'Entregas de compañeros visibles sólo {$a} ';
$string['viewpeerslink'] = 'Ver la tabla de entregas de compañeros';

$string['viewpeerslimitdefault'] = 'Cuando mostrar las entregas e comapñeros';
$string['configviewpeerslimitdefault'] = 'Will be used as default option in any new Assignment activity Settings form';
$string['eventpeerstableviewed'] = 'Visualizada la tabla de entregas de compañeros';
$string['eventpeerssubmissionviewed'] = 'Visualizada entrega de compañero';
