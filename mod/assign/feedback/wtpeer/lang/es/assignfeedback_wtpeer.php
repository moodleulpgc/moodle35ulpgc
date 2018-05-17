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
 * Strings for component 'assignfeedback_wtpeer', language 'en'
 *
 * @package   assignfeedback_wtpeer
 * @copyright 2016 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['default'] = 'Activado por defecto';
$string['default_help'] = 'Si se habilita, la herramienta de Multievaluación ponderada estará activa de forma predeterminada en todas las nuevas Tareas agregadas.';
$string['wtpeer:autoalloc'] = 'Asignar como Auto-evaluador';
$string['wtpeer:autograde'] = 'Auto-Evaluar';
$string['wtpeer:grade'] = 'Evaluar a otros';
$string['wtpeer:gradergrade'] = 'Evaluar como Profesor';
$string['wtpeer:graderalloc'] = 'Asignar como Profesor evaluador ';
$string['wtpeer:manage'] = 'Gestionar Multievaluaciones ponderadas';
$string['wtpeer:manageallocations'] = 'Asignar evaluadores';
$string['wtpeer:peeralloc'] = 'Asignar como Par evaluador';
$string['wtpeer:peergrade'] = 'Evaluar como Par';
$string['wtpeer:tutoralloc'] = 'Asignar tutores';
$string['wtpeer:tutorgrade'] = 'Evaluar como Tutor';
$string['wtpeer:view'] = 'Ver Multievaluación ponderada';
$string['wtpeer:viewothergrades'] = 'Ver evaluaciones de otros';
$string['wtpeer:viewotherallocs'] = 'Ver asignaciones de otros';
$string['enabled'] = 'Multievaluación ponderada';
$string['enabled_help'] = 'Si se activa, cada entrega de la Tarea puede ser evaluada en cualquiera de tres niveles o categorías: 
 * Auto:    Auto-evaluación
 * Par:     Evaluación por Pares
 * Tutor:   Evaluación por Tutor
 * Prof:    Evaluación por Profesor
La puntuación final será una media ponderada de la nota recibida en estas categorías.

El profesor puede asignar a otras personas (estudiantes u otros profesores) como evaluadores de otras entregas en cada una de esas categorías. 
Pueden existir varios evaluadores en cada nivel o categoría.';
$string['pluginname'] = 'Multievaluación ponderada';
$string['pluginnameplural'] = 'Multievaluaciones ponderadas';
$string['wtpeer'] = 'Multievaluación ponderada';
$string['wtpeer_help'] = 'Si se activa, cada entrega de la Tarea puede ser evaluada en cualquiera de tres niveles o categorías: 
 * Auto:    Auto-evaluación
 * Par:     Evaluación por Pares
 * Tutor:   Evaluación por Tutor
 * Prof:    Evaluación por Profesor
La puntuación final será una media ponderada de la nota recibida en estas categorías.

El profesor puede asignar a otras personas (estudiantes u otros profesores) como evaluadores de otras entregas en cada una de esas categorías. 
Pueden existir varios evaluadores en cada nivel o categoría.';
$string['crontask'] = 'Weigthted peer grading cron task';

$string['rowauto'] = 'Auto';
$string['rowpeer'] = 'Pares';
$string['rowtutor'] = 'Tutor';
$string['rowgrader'] = 'Profesor';
$string['reviewtable'] = 'Multievaluaciones';
$string['reviewassessments'] = 'Revisar y Evaluar por criterios de Multievaluaciones ponderadas';
$string['manageconfig'] = 'Configurar Multievaluación';
$string['manageallocations'] = 'Asignar evaluadores';
$string['showallocations'] = 'Mostrar evaluadores';
$string['weightselector'] = 'Ponderación de los elementos';
$string['weight_auto'] = 'Peso (%) para Auto-evaluación';
$string['weight_auto_help'] = 'El peso en la calificación final, en %, que tendrán las puntuaciones obtenidas en la categoría de Auto-evaluación. 

Establer como cero, 0, si no desea que los usuarios puedan auto-evaluar sus propias entregas.';
$string['weight_peer'] = 'Peso (%) para evaluación por Pares';
$string['weight_peer_help'] = 'El peso en la calificación final, en %, que tendrán las puntuaciones obtenidas en la categoría de Evaluación por Pares. 

Las puntuaciones otogadas por los compañeros se promedian y la nota media, ponderada según este valor, se añade a la calificación final del usuario.

Establer como cero, 0, si no desea que los usuarios puedan evaluar las entregas de otros compañeros.';
$string['weight_tutor'] = 'Peso (%) para evaluación por Tutor';
$string['weight_tutor_help'] = 'El peso en la calificación final, en %, que tendrán las puntuaciones obtenidas en la categoría de Evaluación por Tutores. 

Las puntuaciones otogadas por los Tutores se promedian y la nota media, ponderada según este valor, se añade a la calificación final del usuario.

Establer como cero, 0, si no desea que los usuarios puedan evaluar las entregas de otros actuando como Tutores.';
$string['weightinfo'] = 'La Evaluación por Profesor es simplemente lo que resta después de sumar las otras categorías. 
Si las tres se configuran en cero, 0, entonces el 100% de la calificación procederá de puntuaciones de Profesores. <br />

La calificación final de cada entrega se calcula a partir de los promedios obtenidos en cada categoría de 
Auto-Evaluación, Evaluación por Pares, Evaluación por Tutor y Evaluación por Profesor como una suma ponderada de las cuatro categorías: 

     <p><center> Final = &Sigma; ( Peso/100 · promedio )<sub>i</sub> </center></p>

';

$string['dateselector'] = 'Fechas para las evaluaciones';
$string['peeraccessmode'] = 'Acceso a la evaluación';
$string['peeraccessmode_help'] = 'Este parámetro controla cuándo los usuarios pueden acceder a realizar sus valoraciones como evaluadores. 
En todo caso, para evaluar una entrega los usuarios deben ser asignados como evaluadores de esa entrega (y disponer de los permisos del sistema adecuados). 
Este parámetro solo controla cuándo podrán realizar la evaluación, no qué ni cómo.

 * Por fecha: Se aplican las fechas indicadas abajo sin otro requerimiento específico (salvo la asignación como evaluador).
 * Tras entrega: Los estudiantes deben realizar una entrega previa de su propio trabajo (pueed ser un borrador) antes de poder acceder evaluar a otros. Después de la entrega, se aplican las fechas indicadas.
 * Entrega definitiva: Además de las fechas indicadas, los estudiantes deben haber entregado y enviado para evaluar su propiop trabajo definitivo.  
 
';
$string['accessbydate'] = 'Por fecha';
$string['accessaftersubmission'] = 'Tras entrega';
$string['accessafterfinal'] = 'Entrega definitiva';
$string['startgrading_auto'] = 'Comienzo de Auto-evaluación';
$string['endgrading_auto'] = 'Final de Auto-evaluación';
$string['startgrading_peer'] = 'Comienzo de evaluación por Pares';
$string['endgrading_peer'] = 'Final de evaluación por Pares';
$string['startgrading_tutor'] = 'Comienzo de evaluación por Tutores';
$string['endgrading_tutor'] = 'Final de evaluación por Tutores';
$string['startgrading_grader'] = 'Comienzo de evaluación por Profesores';
$string['endgrading_grader'] = 'Final de evaluación por Profesores';
$string['publishselector'] = 'Acceso a Resultados ';
$string['publishassessment'] = 'Publicación de evaluaciones';
$string['publishassessment_help'] = 'Este parámetro controla cuándo los usuarios podrán ver los resultados de la muti-evaluación.
En principio los usuarios no pueden ver las puntuaciones realizadas por otros, en ninguna de las cuatro categorias (auto, por pares, po tutor o profesor). 
Las opciones son:

This settings controls when the users will be able to see the results of the cross-assesment. 
By default students cannot see the marks granted by others in each of the four categories (self, peer, tutor, grader).

 * No: No se publican resultados de evaluaciones. Los estudiantes NO pueden ver el promedio de sus evaluaciones.  
 * Yes: Los estudiante pueden ver los resultados de la evaluación cuando tienen puntuaciones en todas las categorías usadas (no nulas).
 * En fecha: Los estudiantes pueden ver las evaluaciones recibidas de otros a partir de la fecha definida debajo.
';
$string['publishno'] = 'No';
$string['publishyes'] = 'Si';
$string['publishmanual'] = 'Manual';
$string['publishauto'] = 'Automático';
$string['publishondate'] = 'En fecha';
$string['publishassessments'] = 'Publicación de multi-evaluaciones';
$string['publishassessmentdate'] = 'Fecha de publicación de multi-evaluaciones';
$string['publishgrade'] = 'Publicación de Calificación final';
$string['publishgrade_help'] = 'Este parámetro controla cuándo el restuda final de las multievaluacioes será calculado y transferido a la calificación de la Tarea.
En principio las puntuaciones de multievaluación NO se traducen en una Calificación de la Tarea. Las opciones para hacerlo son:

 * Manual: El profesor debe ejecutar manualemente la herramienta de re-cálculo y publicación de Calificaciones.   
 * Automática: Las calificaciones se calculan automáticamente, para cada estudiante, una vez haya puntuaciones en todas las categorías utilizadas (con peso no nulo).
 * En fecha: Las Calificaciones se calculan automáticamente a partir de la fecha definida debajo. 
 
Como profesor siempre puede usar el interfaz estándar de Tarea para calificar a cada estudiante, sorteando el cálculo realizado por la multievaluación. 
No obstante, en las opciones Automática, o después de la fecha de publicación si se produce algún cambio en las puntuaciones de muntievaluación ese cambio sobre-escribirá las calificaciones finales de la Tarea.
Si desea evitar cualquier cambio posterior configure el modo Manual.  
';
$string['publishgradedate'] = 'Fecha de cálculo de calificaciones';
$string['publishgrades'] = 'Calcular Calificaciones.';
$string['publishmarkers'] = 'Nombres de los evaluadores';
$string['publishmarkers_help'] = 'Permite especificar si los usuarios podrán ver los nombres de sus evaluadores en cada categoría o no.
Esta capacidad también está controlada por los permisos del sistema.';
$string['assessmentstatus'] = 'Estado de  Evaluación';

$string['gradeauto'] = 'Auto-evaluación';
$string['gradepeer'] = 'Evaluación por Pares';
$string['gradetutor'] = 'Evaluación por Tutor';
$string['gradegrader'] = 'Evaluación por Profesor';
$string['allocate'] = 'Asignar evaluadores';
$string['calculate'] = 'Calcular calificación';
$string['allocatedmarkersgrades'] = '{$a->marker}: {$a->grade}';
$string['manualallocate'] = 'Asignar evaluadores al usuario:';
$string['gradertype'] = 'Tipo de evaluación';
$string['marker'] = 'Evaluador';
$string['markers'] = 'Evaluadores. ({$a})';
$string['assessallocstatus'] = '{$a->item}: {$a->grades}/{$a->allocs}';
$string['usersselector'] = 'Asignar a entregas de usuarios de';
$string['markersselector'] = 'Seleccionar evaluadores de entre';
$string['assessmode'] = 'Tipo de Evaluación';
$string['assessmode_help'] = 'Una de las categorías de evaluación: por Pares, Tutor, o Profesor. 
Sólo se muestran las categorías en uso (esto es, con un peso no nulo). Las Autoevaluaciones se asignan sólo a  los usuarios que han realizado una entrega, más abajo.';
$string['selectfromrole'] = 'Usuarios con rol';
$string['selectfromrole_help'] = 'En principio los usuarios se seleccionan según sus permisos en el sistema. 
Por ejemplo, los usuarios con la capacidad para Evaluar como Pares son los qu epueden ser seleccionados aquí para ser asignados como evaluadores en la categoría de evaluación por Pares. 

Con esta opción puede establecer un requisito adicional, de forma que los cadidatos deben ostentar, además el rol indicado.';
$string['selectfromgrouping'] = 'Miembros del agrupamiento';
$string['selectfromgroup'] = 'Miembros del grupo';
$string['includeonlyactiveenrol'] = 'Incluir solo matrículas activas';
$string['includeonlyactiveenrol_help'] = 'Si se activa (predefifinido), los usuariso con una matrícula suspendida NO serán considerados como candidatos en las asignaciones masivas.';
$string['allocationsettings'] = 'Opciones de asignación';
$string['numperauthor'] = 'por Entrega';
$string['numperreviewer'] = 'por Evaluador';
$string['numofreviews'] = 'Nº de evaluaciones';
$string['numofreviews_help'] = 'El número de evaluaciones asignadas puede establecerse de do maneras:

 * Por Evaluador: El parámetro se refiere al nº de Entregas diferentes que serán asignadas a cada evaluador para su valoración. 
 * Por Entrega: El parámetro se refiere al nº de Evaluadores diferentes que serán asignadas a cada entrega para su valoración.

Si el número de entregas o evaluadores totales es inferior al parámetro se reducirá éset hasta el máximo posible sin duplicaciones. 
 
';
$string['excludesamegroup'] = 'Excluir como evaluador de la propia entrega';
$string['excludesamegroup_help'] = 'Activar si NO desea que un usuario pueda ser asignado como Evaluador por Par a su propia entrega, por ejemplo.';
$string['excludeotheralloc'] = 'Excluir evaluadores en otras categorías';
$string['excludeotheralloc_help'] = 'Activar si quiere evitar que un usuario ya asignado como evaluado en uan categoría (e.g. Tutor) también sea asignado como evaluador en otra categoría (e.g. Par o Profesor).';
$string['currentallocs'] = 'Asignaciones existentes';
$string['currentallocs_help'] = 'Cómo gestionar las asignaciones ya existentes al ejecutar una asignación masiva aleatorizada. 

    Conservar : Se conservan las ya existentes y se añaden la N nuevas especificadas aquí.
    Mantener hasta máximo N: Se conservan las ya existentes y se añaden taantas nuevas como necesarias para alcanzar la N definidas aquí..
    Eliminar: Elimina las asignaciones como evaluador ya existentes antes de proceder a la asignación masiva aleatorizada. 
';
$string['allocsremove'] = 'Eliminar';
$string['allocskeep'] = 'Conservar';
$string['allocskeepmax'] = 'Mantener hasta máximo N';
$string['addautoalloc'] = 'Agregar Auto-evaluaciones';
$string['selfassessmentdisabled'] = 'Auto-evaluaciones desactivadas';
$string['showitemresult'] = '{$a->item}: {$a->grade} ({$a->grades}/{$a->allocs})';
$string['alertungradedallocs'] = '{$a} evaluaciones pendientes';
$string['gradingclosed'] = 'Periodo terminado el {$a}';
$string['gradingupto'] = 'Evaluar hasta el {$a}';
$string['gradingstarton'] = 'Periodo empieza el {$a}';
$string['viewassessmentsdate'] = 'Resultados publicados a  partir del {$a}';
$string['viewassessmentsno'] = 'Resultados no publicados todavía';
$string['viewgradedate'] = 'Calificación publicada a partir del {$a}';
$string['viewgradeno'] = 'Calificación no publicada todavía';
$string['allocated'] = 'Asignados';
$string['graded'] = 'Evaluados';
$string['gradingdate'] = 'Fecha de evaluación';
$string['assessment'] = 'Evaluación';
$string['allocsummary'] = 'Resumen de asignaciones de evaluación';
$string['userallocations'] = 'Evaluaciones asignadas';
$string['importmarkerallocs'] = 'Importar asignación de evaluadores';
$string['importmarkers'] = 'Importar evaluadores';
$string['removeexisting'] = 'Eliminar existentes';
$string['removeexisting_help'] = 'Qué hacer cuando el archivo contiene un usuario al que ya se le han asignado evaluadores.
Las posible sopciones son:

    * No: Mantener los evaluadores existentes y añadir los nuevos importados del archivo.
    * Mismo tipo: Eliminar los evaluadores asignados previamente a este usuario en mismo tipo de evaluación que los indicados en el archivo.
    * Todos: Eliminar todos los evaluadores asigados previamente a este usuario y reemplazar por los indicados en el archivo importado.

';
$string['removeitemmarkers'] = 'Mismo tipo';
$string['removeallmarkers'] = 'Todos los del usuario';
$string['markersfile'] = 'Archivo de evaluadores';
$a = core_text::strtolower(get_string('user'));
$b = core_text::strtolower(get_string('marker', 'assign'));
$string['markersfile_help'] = 'El archivo a importar debe contener texto CSV con la menos tres columnas. Por ejemplo:
The file to import must be a CSV text file with at least three colums. 

    '."{$a}, {$b}".', evaluación
    123456, 789321 auto  
    256789, 456852 pares

La primera línea contiene los identificadores de columnas.
Los usuarios y evaluadores se especifican por su DNI. La tercera columna indica el tipo de evaluación que realizará el evaluador sobre la Tarea del usuario.. 

';
$string['headercolumns'] = 'El cabecero debe contener las etiquetas \'{$a->user}\' , \'{$a->marker}\' y \'{$a->item}\'.  <br />
La evaluación debe ser una de las palabras [{$a->itemnames}].';
$string['invalidimporter'] = 'Importación inválida: el cabecero no contiene las columnas correctas, o los tipos de evaluación o el separador son incorrectos.';
$string['validmarkersassigns'] = 'Asignaciones de evaluación válidas ';
$string['nonvalidmarkersassigns'] = 'Líneas no importadas del archivo';
$string['gradeanalysis'] = 'Ver detalles';
$string['assessmentexplain'] = 'Criterios de evaluación detallados';
$string['assessexplainlink'] = 'Criterios de evaluación';
$string['toggleexplain'] = 'Desplegar detalles ';
$string['downloadassess'] = 'Descargar evaluación detallada';
$string['showassess'] = 'Ver detalles de evaluaciones';
$string['showexplain'] = 'Ver valoración por criterios';
$string['showaliensub'] = 'Ver entrega de usuario';
$string['sortedby'] = 'Orden: {$a} ';
$string['sortlastname'] = 'Apellidos ';
$string['sortfirstname'] = 'Nombre de pila ';
$string['sorttimegraded'] = 'Fecha de evaluación';
$string['sortgrade'] = 'Calificación ';
$string['titleauto'] = 'Auto-Evaluación ({$a})';
$string['titlepeer'] = 'Evaluación por Pares ({$a})';
$string['titletutor'] = 'Evaluación por Tutor ({$a})';
$string['titlegrader'] = 'Evaluación por Profesores ({$a})';
$string['batchoperationconfirmcalculateselected'] = 'Calcular Calificación final para seleccionados';
$string['batchoperationconfirmdownloadselected'] = 'Descargar Evaluaciones detalladas para seleccionados';
$string['calculatedngrades'] = 'Calculada Calificación final para {$a} estudiantes.';
$string['noaction'] = 'Ninguna acción especificada';
$string['needconfiguration'] = 'Requiere Configuración';
