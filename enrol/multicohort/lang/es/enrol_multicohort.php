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
 * Strings for component 'enrol_multicohort', language 'en'.
 *
 * @package    enrol_multicohort
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['addgroup'] = 'Agregar al grupo';
$string['addgroup_help'] = 'Si se habilita, permite agregar a los usuarios sincronizados como miembros de un grupo, además de matricularlos aquí.

Indicar el grupo al que añadir a los usuarios. Puede ser un curso existente o uno nuevo creado automáticamente:

La opción "Grupo de mentaenlace" creará un único grupo denominado como este método de matrícula e insertará en el mismo todos los usuarios.
La opción "Grupos por cohorte" creará varios grupos denominados como las cohortes de origen en "Miembro de alguna de" e insertará a cad usuario en uno oo varios de esos grupos según su pertenecia a cada cohorte..
';
$string['assignrole'] = 'Asignar rol';
$string['multicohort:config'] = 'Configurar instancias de Multicohorte';
$string['multicohort:unenrol'] = 'Desmatricular usuarios';
$string['defaultgroupnametext'] = '{$a->name} (multicohorte) {$a->increment}';
$string['instanceexists'] = 'Matrículas multicohorte ya sincronizadas con ese rol';
$string['pluginname'] = 'Metaenlace multicohorte';
$string['pluginname_desc'] = 'La extensión de matrícula Metaenlace a Categoría busca y sicroniza usuarios en una o varias cohortes y los enrola en este curso.';
$string['status'] = 'Activo';
$string['creategroup'] = 'Grupo de metaenlace';
$string['multiplegroup'] = 'Grupos por cohorte';
$string['keepgroups'] = 'Keep cohort synced groups';
$string['oranycohorts'] = 'Miembro de alguna de';
$string['oranycohorts_help'] = '
La sincronización afectara a los usuariso que sean miembros de al menos una, cualquiera, de las cohortes seleccionaads en esta caja.

Este campo es obligatorio, se debe especificar al menos una cohorte aquí';
$string['andallcohorts'] = 'Y en todas';
$string['andallcohorts_help'] = 'Opcionalmente, los usarios seleccionados también deberán ser moembrso de <strong>todas</strong> las cohortes indicadas aquí.';
$string['notcohorts'] = 'NO en ';
$string['notcohorts_help'] = 'Opcionalmente, se pueden especificar algunas cohortes de las que el usuario NO debe ser miembro (todas o al menos una según el parámetro siguiente).';
$string['notand'] = 'todas';
$string['notor'] = 'alguna';
$string['andornotcohorts'] = 'Combinación para Exclusión';
$string['andornotcohorts_help'] = 'Si "alguna", entonces un usuario que sea miembro de al menos una de las cohortes en "NO en" no será seleccionado para la sincronización.

If "todas", entonces sólo los usuarsio que sean miembros de todas las cohortes indicadas en "NO en" serán excluidos de la sicronización';
$string['assigngroupmode'] = 'Modo de asignación de enrol y Grupos';
$string['assigngroupmode_help'] = 'Cómo proceder con los usuarios afectados. 

 * Enrolar y asignar grupo: Los usuarios detectados son enrolados en el curso y se les asigna un grupo, en su caso. 
   Es el funcionamiento normal de un método de matriculación.
   
   Las otras opciones desactivan el enrolado de nuevo usuarios, este método NO enrolará a nadie nuevo que no estuviera ya enrolado por otras vías. 
 
 
 * Grupo para ya existentes: NO se enrola a nuevos usuarios. Se comprueba pertenecia a cohortes de ya enrolados (por otros métodos). 
 
   Se asigna grupo en función de la pertenecia a cohortes.
 * Grupo para rol especificado: NO se enrola a nuevos usuarios. Se comprueba pertenecia a cohortes de ya enrolados (por otros métodos).
 
   Se asigna grupo para los usuarios con el rol indicado en función de la pertenecia a cohortes.
 
';
$string['enrolgroups'] = 'Enrolar y asignar grupo';
$string['onlygroups'] = 'Grupo para ya existentes';
$string['rolegroups'] = 'Grupo para rol especificado';
$string['noenrolgroups'] = 'Se puede desactivar el enrolado de nuevos y usar sólo la asignación en grupos de usuariod ya enrolados.';

