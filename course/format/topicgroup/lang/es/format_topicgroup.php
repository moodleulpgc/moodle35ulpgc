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
 * Strings for component 'format_topicgroup', language 'es', branch 'MOODLE_20_STABLE'
 *
 * @package   format_topicgroup
 * @copyright 2013 onwards E. Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 $string['accessallgroups'] = 'Restringir acceso a todos los grupos';
$string['accessallgroups_help'] = 'Si se activa, los profesores NO coordinadores perderán la capacidad de acceder a todos los grupos: 
deberán ser miembros del grupo, explícitamente, paar poder participar en el mismo.'; 
$string['accessallgroups_default'] = 'Restringir acceso a todos los grupos';
$string['accessallgroups_desc'] = 'Valor inicial de esta parámetro en cada formulario de configuración de curso';
$string['currentsection'] = 'Tema actual';
$string['currentgrouping'] = 'Restringido actualmente a';
$string['sectionname'] = 'Tema';
$string['pluginname'] = 'Secciones para agrupamientos';
$string['section0name'] = 'General';
$string['page-course-view-topicgroup'] = 'Cualquier página principal de curso en el formato Secciones para agrupamientos';
$string['page-course-view-topicgroup-x'] = 'Cualquier página de curso en el formato Secciones para agrupamientos';
$string['hidefromothers'] = 'Ocultar tema';
$string['showfromothers'] = 'Mostrar tema';
$string['setsettings'] = 'Restricción';

$string['defaultcoursedisplay'] = 'Modo de visualización';
$string['defaultcoursedisplay_desc'] = "Permite bien mostrar todos los Temas (secciones) en una única página, uno debajo de otro,
o bien separar en varias páginas, mostrando en cada página sólo las sección cero (común) y un único Tema (sección) seleccionado. ";

$string['editingroles'] = 'Roles editores';
$string['editingroles_desc'] = 'Roles que mantendrán preservadas su capacidades de edición en el curso y podrán acceder a todos los grupos.';

$string['restrictedroles'] = 'Roles restringidos';
$string['restrictedroles_desc'] = 'Los usuarios con estos roles no pueden acceder a todos los grupos.';

$string['restrictsection'] = 'Restringir la sección al agrupamiento';
$string['editrestrictsection'] = 'Modificar la restricción por agrupamientos';
$string['unrestrictsection'] = 'Eliminar restricción al agrupamiento ';
$string['restrictedsectionlbl'] = 'Accesible solo por miembros de {$a} ';
$string['manageactivities'] = 'Restringir gestión de actividades';
$string['manageactivities_help'] = 'Si se activa, entonces los profesores NO coordinadores perderán varias capacidades relativas al menejo y gestión de actividades. 

 * Gestión de actividades, editar y borrar elementos del curso.
 * Gestión y posicionameinto de secciones.
 * Gestión del libro de calificaciones.

';
$string['manageactivities_desc'] = 'Default value of this capability parameter in each course config form.';
$string['cap_keep'] = 'Do not change';
$string['cap_prevent'] = 'Yes, prevent';
$string['cap_allow'] = 'No, allow';
$string['managerestrictions'] = 'Gestionar restricciones de sección';

$string['setgrouping'] = 'Establecer una restricción por Agrupamiento';
$string['setgroupingerror'] = 'Ha ocurrido un error al guardar los datos de restricción para la sección {$a}.';
$string['unsetgrouping'] = 'Eliminar una restricción por Agrupamiento';

$string['grouping'] = 'Agrupamiento con acceso';
$string['applyother'] = 'Cambiar en otras secciones';
$string['applyother_help'] = 'Si se activa, entonces todas las otras secciones  (Temas) que también tengan actualmente una restricción al mismo agrupamiento que la sección actual
también verán cambiada su restricción al agrupamiento establecido aquí para la sección actual.';

$string['groupmode'] = 'Cambiar el modo de grupo';
$string['groupmode_help'] = 'Si se especifica, éste y todos los demás elementos de esta sección (Tema) adquirirán el modo de grupo indicado.';
$string['keepgroupmode'] = 'Sin cambios';

$string['applyall'] = 'Liberar otras secciones';
$string['applyall_help'] = 'Si se activa, entonces todas las demás secciones (Temas) que también tengan actualmente una restricción al mismo agrupamiento que la sección actual
también verán eliminada la restricción como la sección actual.';
