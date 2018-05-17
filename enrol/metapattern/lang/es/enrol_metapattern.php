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
 * Strings for component 'enrol_metapattern', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    enrol
 * @subpackage metapattern
 * @copyright  2012 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['metapattern:config'] = 'Configurar instancias de meta patrón';
$string['metapattern:selectaslinked'] = 'Selecionar como meta enlazado';
$string['metapattern:unenrol'] = 'Desmatricular usuarios';
$string['nosyncroleids'] = 'Roles que no se sincronizan';
$string['nosyncroleids_desc'] = 'By default all course level role assignments are synchronised from parent to child courses. Roles that are selected here will not be included in the synchronisation process. The roles available for synchronisation will be updated in the next cron execution.';
$string['pluginname'] = 'Metaenlace por patrón';
$string['pluginname_desc'] = 'La extensión de matrícula Metaenlace por patrón busca y sicroniza usuarios en cualesquiera cursos que coincidan con un cierto patrón y los incluye en el curso actual.';


$string['linkedfield'] = 'Campo del curso enlazado';
$string['linkedfield_help'] = 'Los cursos de origen se seleccionaran buscando un patrón en cierto campo de esos cursos.
Este parámetro especifica el campo que se usará para contrastar el patrón especificado y seleccionar así los cursos de origen.';
$string['linkedpattern'] = 'Patrón a buscar';
$string['linkedpattern_help'] = 'Los usuarios matriculados en cualquier curso que contenga en patrón buscado serán matriculados aquí.

Los cursos origen son aquellos en lso que el campo indicado coincide con el patrón especificado.

La sincronización es unidireccional: desde los cursos origen hacia este. Los usuarios matriculados en este curso NO serán matriculados en los cursos de origen.';
$string['syncroles'] = 'Roles a sincronizar';
$string['syncroles_help'] = 'Solo los usuarios con uno de estos roles (en cualquiera de los cursos origen) se seleccionarán para ser matriculados aquí.';
$string['enrolledas'] = 'Matricular como';
$string['enrolledas_help'] = 'El rol con el que los usuarios de otros cursos origen serán matriculados en éste.

Si se establece "Sincronizado" entonces cada usuario será enrolado aquí con el mismso rol que tenía en su curso de origen.

Si se especifica un rol concreto entonces todos los usuarios serán matriculados aquí con ese rol, independientemente del rol que tuvieran en su curso de origen.';
$string['synchronize'] = 'Sincronizado';
$string['syncgroup'] = 'Agregar al grupo';
$string['syncgroup_help'] = 'Si se habilita, además de matriculado, cada usuario será agregado como miembro de un grupo en este curso. 

Si se especifica un grupo concreto entonces todos los usuarios se añadiran a ese grupo. 

Otras opciones son:

 * Grupo según código de asignatura: el grupo es determinado por el código de la asignatura origen (group.idnumber = course.shortname)
 * Grupo según código ID del curso: el grupo es determinado por el código ID del curso origen (group.idnumber = course.idnumber) 
 * Grupo según semestre del curso: el grupo es determinado por el semestre del curso origen (group.idnumber = course.term) 
 * Grupo según tipo del curso: el grupo es determinado por el tipo del curso origen (group.idnumber = course.ctype) 
 * Grupo según código ID de la categoría: el grupo es determinado por el código ID de la categoría enlazada (group.idnumber = category.idnumber)
 * Grupo según facultad : el grupo es determinado por el código de Facultad de la categoría enlazada (group.idnumber = category.faculty)
 * Grupo según titulación : el grupo es determinado por el código de Titulación de la categoría enlazada (group.idnumber = category.degree)


Si el grupo con el nombre requerido no existe se creará automáticamente al sincronizar las matrículas.';
$string['gsyncbyctype'] = 'Grupo según tipo del curso';
$string['gsyncbyterm'] = 'Grupo según semestre del curso';
$string['gsyncbyshortname'] = 'Grupo según código de asignatura';
$string['gsyncbyidnumber'] = 'Grupo según código ID del curso';
$string['gsyncbyfaculty'] = 'Grupo según Facultad';
$string['gsyncbydegree'] = 'Grupo según titulación';
$string['gsyncbycatidnumber'] = 'Grupo según código ID de categoría';
$string['ctype'] = 'Tipo de curso';
