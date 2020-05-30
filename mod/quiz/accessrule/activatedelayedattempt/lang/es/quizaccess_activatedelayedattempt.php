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
 * Strings for the quizaccess_activatedelayedattempt plugin.
 * Based on quizaccess_activateattempt https://github.com/IITBombayWeb/moodle-quizaccess_activatedelayedattempt/tree/v1.0.3
 *
 * @package   quizaccess_activatedelayedattempt
 * @author    Juan Pablo de Castro <juan.pablo.de.castro@gmail.com>
 * @copyright 2020 Juan Pablo de Castro @University of Valladolid, Spain
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$string['attemptquiz'] = 'Realizar el cuestionario ahora';
$string['pleasewait'] = 'Por favor, espere en esta página.';
$string['quizwillstartinabout'] = 'Podrá empezar a completar el cuestionario en ';
$string['quizwillstartinless'] = 'Podrá empezar a completar el cuestionario en menos de un minuto';
$string['quizaccess_activatedelayedattempt_enabled'] = 'Entrada retardada a los cuestionarios activada';
$string['quizaccess_activatedelayedattempt_allowdisable'] = 'A los profesores se les permite desactivar la regla';
$string['quizaccess_activatedelayedattempt_enabledbydefault'] = 'Los nuevos exámenes usarán esta regla por defecto';
$string['quizaccess_activatedelayedattempt_showdangerousquznotice'] = 'Muestra al profesor un aviso si su cuestionario consume recursos intensamente';
$string['quizaccess_activatedelayedattempt_startrate'] = 'Tasa de entrada (alumnos por minuto)';
$string['quizaccess_activatedelayedattempt_maxdelay'] = 'Máximo retardo (minutos)';
$string['quizaccess_activatedelayedattempt_notice'] = 'Aviso para los estudiantes';
$string['quizaccess_activatedelayedattempt_countertype'] = 'Tipo de contador';
$string['quizaccess_activatedelayedattempt_teachernotice'] = 'Este cuestionario aplicará un control de entrada gradual, 
que hará que los estudiantes entren aleatoriamente con hasta {$a} minutos de retardo.';
$string['noscriptwarning'] = 'Este cuestionario necesita un navegador que soporte JavaScript. Si tiene un bloqueador de Javascript necesitará desactivarlo.';
$string['pluginname_desc'] = 'Auto activa el botón de inicio de cuestionario con un retardo aleatorio.';
$string['pluginname'] = 'Entrada con retardo aleatorio al cuestionario.';
$string['delayedattemptlock'] = 'Entrada gradual al cuestionario';
$string['delayedattemptlock_help'] = 'Si se activa, al acceder a la página antes del momento de apertura del cuestionario se inhabilita transitoriamente el botón de comienzo.

Se activa un contador de tiempo con un plazo aleatorio para cada estudiante (hasta 10 min). Solo cuando ese plazo termina el estudiante puede usar el botón de comienzo del cuestionario. ';
$string['explaindelayedattempt'] = 'Establece una demora aleatoria para comenzar';
$string['tooshortpagesadvice'] = 'El cuestionario tiene {$a->pages} páginas demasiado cortas. Esto aumenta la carga sobre el servidor gravemente. Considere poner más preguntas en cada página.';
$string['tooshorttimeguardadvice'] = 'Un tiempo de disponibilidad de {$a->timespan} es demasiado ajustado. Tenga en cuenta que se aplicará a algunos estudiantes un retardo de espera de hasta {$a->maxdelay}, tiene {$a->timelimit} para realizar la prueba y conviene dejar un márgen de seguridad para otros retardos en el inicio del cuestionario.';
