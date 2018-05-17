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
 * Strings for component 'block_supervision', language 'en'
 *
 * @package    block_supervision
 * @copyright  2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['pluginname'] = 'Supervisión';

$string['supervision:manage'] = 'GEstionar supervisores y opciones de supervisión';
$string['supervision:editwarnings'] = 'Editar avisos de supervisión';
$string['supervision:viewwarnings'] = 'Ver avisos de supervisión';

$string['admininstances'] = 'Admin restricted instances';
$string['assignrolesfaculty'] = 'Manage Faculty role assignments';
$string['assignrolesdepts'] = 'Manage Department role assignments';

$string['blockname'] = 'Bloque Supervisión';
$string['blocktitle'] = 'Supervisión de actividades';
$string['checkedroles'] = 'Roles supervisados';
$string['checkerrole'] = 'Rol Supervisor';

$string['supervisor'] = 'Supervisor';
$string['coursesreview'] = 'Supervisión de Titulaciones';
$string['tasksreview'] = 'Tareas de Supervisión';
$string['departmentsreview'] = 'Supervisión de Departamentos';
$string['nowarnings'] = 'No hay avisos';

$string['management'] = 'Gestión de Supervisión';

$string['configcheckedroles'] = 'Sólo los roles seleccionados se procesarán como sujetos potenciales de retrasos en actividades del <strong>Profesor</strong>.';
$string['configcheckerrole'] = 'Sólo el Rol indicado será asignado como Supervisor';
$string['configenablemail'] = 'Si se activa, cuando el sistema detecte un retraso mayor de lo especificado se enviará un aviso por e-mail al profesor(es), además de registrar la incidencia en la base de datos.';
$string['configenablestats'] = 'Si se activa, el sistema detectará, registrará y almacenará las actividades en las que sería esperable una respuesta por parte de un profesor y tal respuesta no se ha producido en plazo estipulado.
Esto son los "avisos de actividad pendiente". ' ;
$string['configexcludedcategories'] = 'Las categorías de cursos seleccionadas serán excluidas de la supervisión de actividades pendientes.';
$string['excludecourses'] = 'Excluir cursos administrativos';
$string['configexcludecourses'] = 'Si se activa, los cursos sin créditos serán excluidos de la supervisión de actividades pendientes.';

$string['enablependingmail'] = 'Activar correos de avisos de supervisión';
$string['enablestats'] = 'Activar supervisión de actividades';
$string['editconfig'] = 'Configuración';
$string['editholidays'] = 'Editar la tabla de Festivos';
$string['editsupervisor'] = 'Definición del permiso de supervisión';
$string['errorolddate'] = 'Dates before tomorrow are not allowed';
$string['excludedcategories'] = 'Categorías excluidas';

$string['holidayduration'] = 'Extensión del Festivo (días))';
$string['holidayname'] = 'Nombre del Festivo';
$string['holidayscope'] = 'Ámbito del festivo';
$string['holidays'] = 'Festivos';
$string['holidaystable'] = 'Tabla de Festivos';
$string['insertholiday'] = 'Agregar un Festivo';
$string['deleteholiday'] = 'Borrar festivo';
$string['deleteholidayconfirm'] = 'Ha solictado borrar el festivo denominado  "{$a}"';
$string['deletedholiday'] = 'Borrada la entrada denominada  "{$a}"';
$string['type'] = 'Tipo';

$string['startdisplay'] = 'Fecha de referencia';
$string['configstartdisplay'] = 'Sólo los registros creados después de esta fecha de referencia serán mostrados en los informes de supervisión<br />El formato de la fecha es ISO 8601:  año-més-día (o cualquier otro formato válido como argumento de la función strtotime() de PHP)';
$string['enablecoordmail'] = 'Activar correos a Supervisores';
$string['configcoordemail'] = 'Si se activa (junto con <i>enablemail</i>), entonces se enviará una copia de los correos de avisos de supervisión a los Supervisores de cada categoría';
$string['pendingmail'] = 'Buzón de copia';
$string['configemail'] = 'Esta dirección recibirá una copia de todos los correos de avisos de supervisión.';
$string['supervisionwarnings'] = 'Avisos de Supervisión';
$string['review'] = 'Revisar cursos';
$string['assigner'] = 'Otorgante';
$string['enabledepartments'] = 'Activar supervisión por Departamentos';
$string['configenabledepartments'] = '
Activar esta opción automatizará la asignación de supervisores por Departamentos usando la tabla "departamentos". <br />
Además de agrupar los cursos por Categorías se permitirá el agrupamiento por Departamentos. Los Supervisores departamentales podrán controlar los cursos del departamentos en diferentes categorías.
Los Departamentos son una personalización de la ULPGC. ';
$string['enablefaculties'] = 'Activar supervisión por Facultades';
$string['configenablefaculties'] = '
Activar esta opción automatizará la asignación de supervisores por Facultades usando la tabla "centros". <br />.
Los cursos se agruparán por facultades usando el campo "faculty" de la tabla "cousre_categories". Los Supervisores de facultad podrán controlar todos los cursos en una categoría.
Las Facultades son una personalización de la ULPGC.';

$string['save'] = 'Guardar cambios';

$string['bycategory'] = 'Supervisores por categoria';
$string['bydepartment'] = 'Supervisores por departamento';
$string['itemname'] = 'Item supervisado';
$string['addpermission'] = 'Agregar permiso de supervisión';
$string['addusersetting'] = '¿Permitir que este usuario agregue otros supervisores?';
$string['adduser'] = 'Añadir';
$string['editpermissions'] = 'Permisos de Supervisión';
$string['itemscope'] = 'Ámbito de Supervisión';
$string['itemfilter'] = 'Filtro por Item';
$string['permissionexists'] = 'Este usario ya dispone de un permiso de supervisión en este Item. < br/>Por favor, actualice los permisos más que añadir nuevos. ';
$string['deletepermission'] = 'Borrar un permisos de supervisión';
$string['deletepermission_confirm'] = 'Ha solicitado borrar el permisos de supervisión del usuario {$a->user} en el item {$a->name}. ';
$string['maildelay'] = 'Demora en avisos';
$string['configmaildelay'] = '
Una demora entre en momento en que se detecta un aviso de incidencia y el comienzo del envío automático de correos de aviso a los usuarios, en <b>DÍAS</b>.';

$string['warningemailtxt'] = 'Estimado profesor:
Se ha detectado un aviso de incidencia en el curso {$a->coursename}

Este aviso ha sido generado en la actividad {$a->activity} {$a->student}

Puede ver más detalles en el Informe de Supervisión de actividades pendientes
{$a->reporturl}

Por favor, recuerde los plazos establecidos.
Este es un mensaje automático que continuará enviándose mientras persista la situación. No responda este mensaje.
';
$string['warningemailhtml'] = 'Estimado profesor: <br />
Se ha detectado un aviso de incidencia en el curso <a href="{$a->courseurl}">{$a->coursename}</a><br /><br />

Este aviso ha sido generado en la actividad <a href="{$a->itemlink}">{$a->activity}</a> {$a->student} <br />

Puede ver más detalles en el <a href="{$a->reporturl}">Informe de Supervisión de actividades pendientes</a><br /><br />

Por favor, recuerde los plazos establecidos. <br />
Este es un mensaje automático que continuará enviándose mientras persista la situación. No responda este mensaje.
';
$string['warningmailsubject'] = '[{$a}]: Aviso de actividad pendiente ';
$string['warningautomatic'] = 'Monitorización de actividades pendientes';
$string['emailstudent'] = '  con respecto al estudiante {$a->fullname} con DNI {$a->idnumber} ';
