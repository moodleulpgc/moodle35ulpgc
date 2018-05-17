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
 * Strings for component 'enrol_metacat', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    enrol
 * @subpackage metacat
 * @copyright  2012 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['metacat:config'] = 'Configurar instancias de metacat';
$string['metacat:selectaslinked'] = 'Selecionar como meta enlazado';
$string['metacat:unenrol'] = 'Desmatricular usuarios';
$string['nosyncroleids'] = 'Roles que no se sincronizan';
$string['nosyncroleids_desc'] = 'By default all course level role assignments are synchronised from parent to child courses. Roles that are selected here will not be included in the synchronisation process. The roles available for synchronisation will be updated in the next cron execution.';
$string['pluginname'] = 'Metaenlace a Categoría';
$string['pluginname_desc'] = 'La extensión de matrícula Metaenlace a Categoría busca y sicroniza usuarios en cualesquiera cursos de una categoría dada y los incluye en el curso actual.';
$string['linkedcategories'] = 'Categoría enlazada';
$string['linkedcategories_help'] = 'Los usuarios matriculados en cualquier curso perteneciente a la categoría indicada serán sincronizados. 

La sincronización es unidireccional: desde los cursos origen hacia este. Los usuarios matriculados en este curso NO serán matriculados en los cursos de la categoría.';
$string['catfromcourse'] = 'la categoría del curso';
$string['autocategory'] = '[auto]';
$string['refreshautocategory'] = 'Actualizar categoría desde el curso';
$string['refreshautocategory_help'] = 'Si se habilita, entonces la categoría enlazada se actualizará automáticamente si este curso se mueve a otra categoría. 

En caso contrario, la categoría enlazada seguirá siendo la misma cuando este curso de mueva a otras categorías.';

$string['syncroles'] = 'Roles a sincronizar';
$string['syncroles_help'] = 'Solo los usuarios con uno de estos roles (en cualquiera de los cursos de la categoría) se seleccionarán para ser matriculados aquí.';
$string['enrolledas'] = 'Matricular como';
$string['enrolledas_help'] = 'El rol con el que los usuarios de otros cursos origen serán matriculados en éste.

Si se establece "Sincronizado" entonces cada usuario será enrolado aquí con el mismso rol que tenía en su curso de origen.

Si se especifica un rol concreto entonces todos los usuarios serán matriculados aquí con ese rol, independientemente del rol que tuvieran en su curso de origen.';
$string['synchronize'] = 'Sincronizado';
$string['syncgroup'] = 'Agregar al grupo';
$string['syncgroup_help'] = 'Si se habilita, además de matriculado, cada usuario será agregado como miembro de un grupo en este curso. 

Si se especifica un grupo concreto entonces todos los usuarios se añadiran a ese grupo. 

Otras opciones son:

 * Grupo según categoría: el grupo es determinado por el nombre de la categoría enlazada (group.idnumber = category.name)
 * Grupo según código ID: el grupo es determinado por el código ID de la categoría enlazada (group.idnumber = category.idnumber)
 * Grupo según ID : el grupo es determinado por el número ID de la categoría enlazada (group.idnumber = category.id)
 * Grupo según facultad : el grupo es determinado por el código de Facultad de la categoría enlazada (group.idnumber = category.faculty)
 * Grupo según titulación : el grupo es determinado por el código de Titulación de la categoría enlazada (group.idnumber = category.degree)


Si el grupo con el nombre requerido no existe se creará automáticamente al sincronizar las matrículas.';
$string['gsyncbyfaculty'] = 'Grupo según Facultad';
$string['gsyncbydegree'] = 'Grupo según Titulación';
$string['gsyncbyid'] = 'Grupo según ID';
$string['gsyncbyname'] = 'Grupo según categoría';
$string['gsyncbyidnumber'] = 'Grupo según código ID';
$string['auto'] = 'auto';
