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
 * English strings for registry
 *
 * @package    mod
 * @subpackage registry
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['registry:addistance'] = 'Añadir un nuevo Registro';
$string['registry:review'] = 'Revisar ítems del registro';
$string['registry:submit'] = 'Enviar ítems al Registro';
$string['registry:submitany'] = 'Enviar al Registro ítems de cualquier curso';

$string['eventitemsubmitted'] = 'Item enviado al Registro';
$string['eventcoursemodulereviewed'] = 'Revisados todos los cursos del Registro';
$string['modulename'] = 'Registro';
$string['modulenameplural'] = 'Registros';
$string['modulename_help'] = 'El módulo Registro permite a los usuarios confirmar la realización de tareas administrativas en módulos de actividad.

Cuando las descripciones/introducciones de los módulos de actividad están completas, se usa este módulo para <em>registrar</em> dicha realización,
y así permitir la revisión de esos ítems por los gestores correspondientes.

Este módulo genera partes de incidencia en un <strong>Gestor de incidencias</strong> asociado.';
$string['modulename_link'] = 'mod/registry/view';
$string['registryname'] = 'Nombre del Registro';
$string['registry'] = 'Registro';
$string['pluginadministration'] = 'Administración del Registro';
$string['pluginname'] = 'Registro';

$string['timedue'] = 'Plazo de entrega: ';
$string['timedue_help'] = 'Plazo para el Registro. Todos los ítems registrados después de esa fecha se consideran retrasado o fuera de plazo y pueden ser marcados así.';
$string['timeduemsg'] = 'Fecha de vencimiento: ';
$string['modconfig'] = 'Configuración del Registro';
$string['trackerconfig'] = 'Configuración del Gestor de incidencias';
$string['regmodule'] = 'Módulo controlado por el Registro';
$string['regmodule_help'] = 'El Registro controla el contenido de un tipo de módulo en los cursos. Esta opción permite especificar dicho tipo.
El sistema buscará las instancias concretas de este tipo de módulo en cada curso. Otras opciones permiten especificar qué instancias concretas son seleccionadas.';
$string['regsection'] = 'Sección del curso controlada';
$string['regsection_help'] = 'La sección que contiene los ítems controlados.
Si se especifica una sección, sólo aquellos ítems del tipo indicado que residan en esa sección de cada curso serán controladas por el Registro.';
$string['category'] = 'Categoría de los cursos';
$string['category_help'] = '
La categoría donde se buscarán cursos con ítems controlados.

Se puede especificar de dos formas distintas:
* A partir de la categoría de este curso: se buscarán cursos que residan en la misma categoría que el curso actual.
* A partir de código de este curso: se buscarán cursos en una categoría definida por el código largo (idnumber) de este curso. ';
$string['catfromidnumber'] = 'categoría a partir de código';
$string['catfromcourse'] = 'categoría de este curso';
$string['visibility'] = 'Visibilidad';
$string['visibility_help'] = '
Permite seleccionar los ítems controlados según su visibilidad en la página del curso.
Existen tres opciones:

* Incluir cualquier ítem: se seleccionan todos los ítems, sin importar su visibilidad.
* Sólo ítems visibles: Se seleccionarán únicamente los ítems visibles en la página del curso.
* Excluir ítems visibles: Se seleccionarán únicamente los ítems actualmente ocultos en la página del curso.
';
$string['visibleall'] = 'incluir cualquier ítem';
$string['visibleonly'] = 'sólo ítems visibles';
$string['visiblenot'] = 'excluir ítems visibles';

$string['adminmod'] = 'Módulos restringidos';
$string['adminmod_help'] = '
Permite seleccionar los ítems controlados según sus ajustes de restricciones en la página del curso.
There are three options:

Existen tres opciones:

* Incluir cualquier ítem: se seleccionan todos los ítems, sin importar sus ajustes de restricción (no ocultable / no borrable).
* Sólo ítems restringidos: Se seleccionarán únicamente los ítems restringidos en la página del curso.
* Excluir ítems restringidos: Se seleccionarán únicamente los ítems sin restricción en la página del curso.

';
$string['adminmodall'] = 'incluir cualquier ítem';
$string['adminmodonly'] = 'sólo ítems restringidos';
$string['adminmodnot'] = 'excluir ítems restringidos';
$string['trackerid'] = 'Gestor de Incidencias asociado';
$string['trackerid_help'] = '
El Gestor de Incidencias asociado a este Registro.
Cuando se registran los ítems de un curso de un usuario se crea automáticamente una entrada para ese curso e ítems en un Gestor de Incidencias.
Esta opción permite especificar qué gestor se utilizará por este Registro. Debe ser una instancia residente en el mismo curso, y con un ID de calificación no vacío.

Cuando un usuario registre algún ítem/curso se generará automáticamente una entrada en ese Gestor de Incidencias.
Allí el usuario y los revisores podrán seguir el trámite administrativo de aprobación/rechazo de dicho registro.
El campo "Estado" de este registro se tomará de las entradas de ese Gestor de incidencias.';
$string['issuename'] = 'Identificador de tema';
$string['issuename_help'] = '
En el mismo curso pueden existir varios asuntos o temas que necesiten un Registro separado.
Esta opción permite identificar temas o asuntos independientes.

El texto que se indica aquí será añadido a la entrada del gestor de Incidencias asociada al mismo,
y de esa forma ayuda a identificar el origen (tema o asunto) de dicha entrada en el Gestor.';
$string['syncroles'] = 'Revisores a categoría';
$string['syncroles_help'] = 'Si se activa, el sistema buscará aquellas personas con el rol de revisores del registro en este curso
y las añadirá como revisoras de curso a nivel de la categoría indicada en el registro.';
$string['enabletracking'] = 'Seguimiento de cambios';
$string['configenabletracking'] = '
Si se activa el sistema controlará si se realizan cambios en los ítems registrados posteriores a la fecha de aprobación.';
$string['checkedroles'] = 'Roles controlados';
$string['configcheckedroles'] = '
Roles que deben tener los usuarios en un curso para considerarlo controlado pro el Registro. Típicamente los roles de <strong>Profesor</strong> y simialres.';
$string['excludecourses'] = 'Excluir cursos administrativos';
$string['configexcludecourses'] = 'Si se activa, los cursos sin créditos y sin un campo idnumber propio serán excluidos del control de registro.';

$string['registrysummary'] = 'Resumen de Ítems registrados';
$string['nodata'] = 'No hay ítems que registrar';
$string['lastsubmitted'] = 'Fecha de registro';
$string['lastgraded'] = 'Fecha de revisión';
$string['status'] = 'Estado';
$string['items'] = 'Ítems';
$string['saveregistrations'] = 'Registrar ítems';
$string['submitconfirm'] = 'Ha solicitado el Registro del contenido de estos cursos: <br/> {$a} ';
$string['downloadpdf'] = 'Descargar entrada como PDF';
$string['attachments'] = 'Adjuntos';
$string['statuslink'] = 'Ver revisión';
$string['status_posted'] = 'Enviado';
$string['status_open'] = 'Abierto';
$string['status_resolving'] = 'En trámite';
$string['status_waiting'] = 'En espera';
$string['status_testing'] = 'Contestado';
$string['status_resolved'] = 'Resuelto';
$string['status_abandonned'] = 'Cerrado';
$string['status_transfered'] = 'Transferido';
$string['status_published'] = 'Publicado';
$string['reviewlink'] = 'Ver estado del Registro para todos los cursos';
$string['userlink'] = 'Ver estado del Registro del usuario';
$string['shortname'] = 'Código';
$string['fullname'] = 'Curso';
$string['term'] = 'Semestre:';

