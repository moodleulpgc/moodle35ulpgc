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
 * Strings for component 'assignfeedback_copyset', language 'es'
 *
 * @package assignfeedback_copyset
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['default'] = 'Activado por defecto';
$string['default_help'] = 'Si se activa, esta herramienta estará activada de forma predeterminada para todas las nuevas Tareas que se creen.';
$string['enabled'] = 'Copiar/Asignar calificación';
$string['enabled_help'] = '
Permite copiar las calificaciones de otras Traeas o asignar una calificación predefinida
a todos los usuarios o bien a un subconjunto según el estado de sus envíos.';
$string['pluginname'] = 'Copiar/Asignar calificación';
$string['menuentry'] = 'Copiar/Asignar calificación predefinida';

$string['all'] = 'Cualquiera';
$string['submitted'] = 'Enviados';
$string['notsubmitted'] = 'No enviados';
$string['draft'] = 'Borrador';
$string['graded'] = 'Calificados';
$string['notgraded'] = 'No calificados';
$string['fail'] = 'Suspendidos';
$string['pass'] = 'Aprobados';
$string['override'] = 'Sobreescribir puntuaciones existentes?';

$string['bysubmission'] = 'Entregas anteriores';
$string['bysubmission_help'] = '
Modificar en los usuarios con o sin una entrega ya realizada.';
$string['bygrading'] = 'Calificación';
$string['bygrading_help'] = '
Modificar en los usuarios con o sin una calificación anterior.';
$string['bygrade'] = 'Puntuación del usuario';
$string['bygrade_help'] = '
Modificar en los usuarios según su estado aprobado/suspendido.';
$string['allgroups'] = 'Todos los grupos ';
$string['targetassign'] = 'Para los usuarios que reúnan las condiciones ...';
$string['confirmmsg'] = 'Ha solicitado {$a} según estos criterios: ';
$string['confirmusers'] = 'Los usuarios afectados son: ';
$string['confirmation'] = '¿Quiere proceder?';
$string['changed'] = 'Aplicados cambios a {$a} usuarios';

// events
$string['eventcopyset'] = 'Copiar/Asignar calificaciones';
$string['eventgradesset'] = 'Calificaciones asignadas a múltiples usuarios';
$string['eventgradescopied'] = 'Calificaciones copiadas a múltiples usuarios';
$string['eventextensionsgranted'] = 'Extensiones otorgadas a múltiples usuarios';

// set grades
$string['setgrade'] = '¿Qué calificaciones modificar?';
$string['setgrades'] = 'Establecer calificación ';
$string['gradevalue'] = 'Calificación a asignar';
$string['gradesset'] = 'Se ha asignado la calificación predefinida a {$a} usuarios.';
$string['advancedgradingmethod'] = 'Esta tarea usa un Método de Calificación Avanzada y por lo tanto no es adecuado fijar una puntuación única para todos los usuarios.';

// copy grades
$string['copygrade'] = '¿Qué calificaciones copiar?';
$string['copygrades'] = 'Copiar calificaciones de otra Tarea';
$string['copysource'] = 'Copiar de la Tarea';
$string['copiedgrades'] = 'Copiadas las puntuaciones de {$a} usuarios en esta Tarea';
$string['byothergrade'] = 'User grade';
$string['byothergrade_help'] = '
Processs for all students or only for users with a failing/passing grade.';

// set due extension
$string['dueextensions'] = 'Ampliar plazo de entregas';
$string['timevalue'] = 'Nuevo plazo ampliado';
$string['timevalue_help'] = '
El nuevo plazo ampliado debe ser una fecha posterior a las anteriores fechas de entrega/gracia.

Si se aplica esta herramienta con esta fecha <b>deshabilitada</b> y con la opción de sobre-escritura activada, se <b>eliminarán</b> las ampliaciones de plazo de entrega ya concedidas.';
$string['extensionsset'] = 'Se ha la ampliación del plazo de entrega a {$a} usuarios.';
$string['tfspecialperiod'] = 'Periodo especial TF';
$string['tfspecialperiod_help'] = '
Si se activa se otorgarán ampliaciones del plazo de entrega según la reglas del Periodo especial de entrega de la Estructura de Teleformación.

Todas las demás opciones serán deshabilitadas.';
$string['tfspecialperiod_config'] = '
Si se hablita, se mostrára en la extensión de ampliación de plazo la opción de aplicar las reglas del Periodo especial de entrega  de Teleformación';
$string['tfstrictrule'] = 'Usar regla TF estricta';
$string['tfstrictrule_help'] = 'Las Normas de evaluación de TF establecen que los estudiantes deben haber superado <strong>más</strong> de la mitad de las actividades para poder usar el Perido Especial de entregas.
Opcionalmente, si no se usa estrictamente y se relaja esta regla, se puede incluir a los estudiantes que han superado <strong>justo la mitad</strong> de las actividades 
entre aquellos a los que se otorgarán ampliaciones de plazo de entrega.';
$string['enabledhidden'] = 'Activado y oculto';
$string['enabledhidden_config'] = 'Si se habilita, entonces estas herramientas estarán activadas en todas las Tareas, 
sin necesidad de habilitarla en cada instancia, y la casilla para hbilitar/deshabilitar estará oculta.  

Las herramientas estarán disponibles según las opciones de calificación de cada Tarea (e.g. desactivadas si no se usan calificaciones)).';

$string['done'] = 'Ampliación de plazo otorgada para {$a} usuarios';

// set randommarkers
$string['randommarkers'] = 'Asignar evaluador al azar';
$string['removemarkers'] = 'Eliminar asignaciones previas';
$string['bywstate'] = 'Estado de Supervisión de evaluación';
$string['bywstate_help'] = '
Modificar sólo en los usuarios cuyas entregas se encuentren en el estado de Evaluación supervisada definido. <br />
O marque todas las opciones para indicar cualquier estado.';
$string['eventrandommarkersset'] = 'Asignación de evaluadores al azar realizada';
// importmarkers
$string['eventmarkersimported'] = 'Importados evaluadores';
$string['importmarkers'] = 'Importar evaluadores';
$string['removeexisting'] = 'Sobre-escribir existentes';
$string['removeexisting_help'] = 'Si activado, entonces el evaluador indicado en el archivo se asignará reemplazando al ya existente, si es el caso.';
$string['markersfile'] = 'Archivo de asignaciones';
$a = core_text::strtolower(get_string('user'));
$b = core_text::strtolower(get_string('marker', 'assign'));
$string['markersfile_helpr'] = 'El archivo a importar debe contener texto CSV con al menso dos columnas indentificando al usuario y evaluador. 
Por ejemplo: 

    '."{$a}, {$b}".'
    123456,     789321   
    256789,     456852

La primera línea contiene los identificadores de las columnas 
Los usuarios o evaluadores se identifican por su DNI.  

';
$string['headercolumns'] = 'La primera línea debe incluir los cabeceros de las columnas \'{$a->user}\' y \'{$a->marker}\'.';
$string['invalidimporter'] = 'Fallo de importación: el cabecero no contiene las columnas {$a} o el separador no es válido.';
$string['validmarkersassigns'] = 'Asignaciones de evaluador válidas';
$string['nonvalidmarkersassigns'] = 'Líneas no importadas del fichero';
