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
 * Strings for component 'report_syncgroups', language 'en'
 *
 * @package   report_syncgroups
 * @copyright 2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


$string['syncgroups'] = 'Combinar grupos';
$string['syncgroups:view'] = 'Ver el informe Combinar grupos';
$string['eventreportviewed'] = 'Informe de Sincronización de grupos visto';
$string['page-report-editdates-index'] = 'Combinar pertenencia a grupos';
$string['pluginname'] = 'Combinar grupos';

$string['targetgroup'] = 'Grupo de destino';
$string['targetgroup_help'] = '
Los usuarios que sean miembros de los grupos fuente serán añadidos como miembros de este grupo.';
$string['parentgroups'] = 'Grupos padre (fuentes)';
$string['parentgroups_help'] = '
Grupos fuente en los que comprobar la pertenencia. Aquellos usuarios que sean miembros de uno de estos grupos padre serán añadidos al grupo de destino.';
$string['editsync'] = 'Editar una Combinación de grupos';
$string['newsync'] = 'Añadir una nueva combinación';
$string['deletesync'] = 'Borrar una combinación de grupos';
$string['deletedsync'] = 'Borrado de Combinación de grupos';
$string['deletesyncconfirm'] = 'Ha solicitado el borrado de una combinación de grupos consistente en:: <br />
<br />
Grupo de destino: {$a->target} <br />
Grupos padre    : {$a->parents} <br />
<br />
¿Desea continuar con el borrado? ';
$string['visible'] = 'Visible';
$string['visible_help'] = 'Si es visible, la Combinación de grupos estará activa. Si esta oculta no tendrá lugar la combinación.';