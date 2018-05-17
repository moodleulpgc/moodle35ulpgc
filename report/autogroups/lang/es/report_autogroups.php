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
 * Strings for component 'report_autogroups', language 'en'
 *
 * @package   report_autogroups
 * @copyright 2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


$string['autogroups'] = 'Autopoblar grupos';
$string['autogroups:edit'] = 'Editar reglas de Autopoblado de grupos';
$string['autogroups:view'] = 'Ver el informe Autopoblar grupos';
$string['eventreportviewed'] = 'Informe de Autopoblar grupos visto';
$string['page-report-editdates-index'] = 'Pertenencia automática a grupos';
$string['pluginname'] = 'Autopoblar grupos';

$string['targetgroup'] = 'Grupo de destino';
$string['targetgroup_help'] = '
El grupo donde serán incorporados los usuarios. Los usuarios seleccionados serán añadidos como miembros de este grupo.';
$string['searchterm'] = 'Patrón de búsqueda';
$string['searchterm_help'] = '
Este campo define un patrón de búsqueda para localizar cursos según un campo de la tabla de cursos.
Si se desea se pueden usar los comodines SQL % y _ . Al revés, para buscar esos caracteres debe usarse el símbolo \ para identificarlos.

Cualquier usuario enrolado en este curso y que también esté enrolado en esos cursos será agregado al grupo de destino en este curso .';
$string['searchfield'] = 'Campo de búsqueda';
$string['searchfield_help'] = '
El campo en la tabla de cursos donde se buscará en patrón de búsqueda.';
$string['sourceroles'] = 'Roles en cursos padre';
$string['sourceroles_help'] = '
Sólo los usuarios enrolados en lo curso padre con estos roles concretos serán seleccionados.';
$string['editsync'] = 'Editar una regla de autopoblado de grupo';
$string['newsync'] = 'Añadir una nueva regla de autopoblado de grupo';
$string['deletesync'] = 'Borrar una regla de autopoblado de grupo';
$string['deletedsync'] = 'Borrado de regla de autopoblado de grupos';
$string['deletesyncconfirm'] = 'Ha solicitado el borrado de una regla de autopoblado de grupo consistente en:: <br />
<br />
Grupo de destino: {$a->target} <br />
Término de búsqueda    : {$a->search} <br />
<br />
¿Desea continuar con el borrado? ';
$string['visible'] = 'Visible';
$string['visible_help'] = 'Si es visible, la regla de autopoblado de grupo estará activa. Si esta oculta no tendrá lugar la combinación.';