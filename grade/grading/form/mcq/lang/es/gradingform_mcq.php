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
 * Plugin strings are defined here.
 *
 * @package     gradingform_mcq
 * @category    string
 * @copyright   2018 Enrique Castro ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['backtoediting'] = 'Atrás para editar';
$string['mcq:manage'] = 'Gestionar ajustes internos';
$string['pluginname'] = 'Calificación Test con negativos';
$string['definemarkingmcq'] = 'Definir parámetros de Formula';
$string['definemcqmarking'] = 'Calificación Test con negativos';
$string['gradeheading'] = 'Opciones de Fórmula de Test';
$string['gradingof'] = 'Calificación para {$a}';
$string['mcqgrading'] = 'Cómo funciona Calificación Test con negativos';
$string['mcqgrading_help'] = 'Con el método de calificación Fórmula Test con negativos los estudiantes 
reciben una calificación ajustada descontando el efecto del azar en un Test según la fórmula estándar para ello. 

Se registran las respuestas correctas y también las incorrectas (negativos). 
La puntuación total se reduce en Incorrectas / (opciones - 1) puntos, 
donde opciones es el nº de posibles opciones de respuesta en cada pregunta.  

Este método asume que el Examen está formado por preguntas tipo test 
que valen todas lo mismo (1 punto) y tienen todas el mismo número de opciones. 
Opcionalmente, se puede añadir una puntuación adicional de preguntas no tipo test. 
';
$string['mcqstatus'] = 'Estado actual de la Calificación Test con negativos';
$string['name'] = 'Nombre';
$string['choices'] = 'Nº Opciones';
$string['choices_help'] = 'Número de opciones en cada pregunta tipo test. 

La fórmula estándard reduce la puntuación total (negativos) en Incorrectas / (opciones - 1) puntos.';
$string['criterion'] = 'Nombre del Elemento';
$string['mcqmaxscore'] = 'Puntos tipo Test totales';
$string['mcqmaxscore_help'] = 'El número total de preguntas (= de puntos) de tipo Test (MCQ) del examen. 
Este método asume que el Examen está formado por preguntas tipo test 
que valen todas lo mismo (1 punto) y tienen todas el mismo número de opciones. 

Opcionalmente el Examen puede contener preguntas adicionales NO de tipo test;  
En ese caso la puntuación máxima total es la suma de ambas. 
';
$string['nonmcqmaxscore'] = 'Puntos NO-test totales';
$string['nonmcqmaxscore_help'] = 'El máximo número de puntos otorgables en preguntas NO tipo test. 
Es la puntuación máxima, no el número de preguntas.

Si tiene una pregunta que vale 5 puntos y 5 preguntas que valen 1 punto cada una, 
eso hace un máximo de 10 puntos. ';
$string['mcqscore'] = 'Test correctas: ';
$string['mcqfails'] = 'Test incorrectas';
$string['nonmcqscore'] = 'NO tipo test';
$string['save'] = 'Guardar';
$string['savemcq'] = 'Guardar Test con negativos para uso';
$string['savemcqdraft'] = 'Guardar como borrador';
$string['err_scoreinvalid'] = 'La puntuación introducida en \'{$a->criterianame}\' no es válida, el máximo es: {$a->maxscore}';
$string['err_scoreisnegative'] = 'La puntuación introducida en \'{$a->criterianame}\' no es válida, no se permiten valores negativos o vacíos';
$string['err_failsinvalid'] = 'La puntuación introducida en \'{$a->criterianame}\' no es válida. La suma de correctas e incorrectas no puede superar el total: {$a->maxscore}';
$string['fullmcqformula'] = '  Puntuación = Correctas - (Incorrectas / (Opciones - 1 )) + NO-test';
$string['mcqformula'] = '  Puntuación = Correctas - (Incorrectas / (Opciones - 1 ))';
$string['needregrademessage'] = 'La definición de la fórmula Test con negativos fue cambiada posteriormente a que este alumno fuera calificado. 
El alumno no verá la puntuación de este Test con negativos hasta que usted valide la fórmula y actualice la calificación.';
$string['regrademessage1'] = 'Está a punto de guardar cambios en una fórmula Test con negativos que ya ha sido utilizada para calificar. 
Por favor, indique si las calificaciones existentes deben ser revisadas. 
Si así lo establece, la puntuación de Test con negativos se ocultará a los estudiantes hasta que sean recalificados.';
$string['regrademessage5'] = 'Está a punto de guardar cambios en una fórmula Test con negativos que ya ha sido utilizada para calificar. 
La calificación guardada en el libro de calificaciones no se modificará, 
pero la puntuación de Test con negativos se ocultará a los estudiantes hasta que sean recalificados.';
$string['regradeoption0'] = 'No recalificar';
$string['regradeoption1'] = 'Recalificar';
$string['restoredfromdraft'] = 'NOTA: El último intento de calificación de este estudiante no se ha guardado correctamente, 
por lo que se han restaurado las calificaciones anteriores guardadas como borrador. 
Si desea cancelar estos cambios pulse sobre el botón "Cancelar"';
