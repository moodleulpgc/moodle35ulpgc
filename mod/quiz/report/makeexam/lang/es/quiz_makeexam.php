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
 * Strings for component 'quiz_makeexam', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   quiz_makeexam
 * @copyright 2014 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Generar Examen';
$string['makeexam'] = 'Crear Examen';
$string['makeexam:componentname'] = 'Quiz makeexam report';
$string['makeexamreport'] = 'Generar Examen';
$string['makeexam:view'] = 'View Make exam report';
$string['makeexam:submit'] = 'Crear y enviar versiones de Examen';
$string['makeexam:delete'] = 'Borrar versiones de Examen';
$string['makeexam:anyquestions'] = 'Usar preguntas de cualquier tipo';
$string['makeexam:nochecklimit'] = 'No restringido por nº limite de preguntas';
//$string['coursename'] = 'Course name';
//$string['allattempts'] = 'todas las versiones';
//$string['attemptsall'] = 'all attempts';
$string['attempt'] = 'Versión';
$string['attemptn'] = 'Versión {$a}';
$string['attempts'] = 'Versiones';
$string['errornoattempt'] = 'No existe la versión nº {$a} para el Examen seleccionado.<br />
Por favor, asegúrese que el nº de versión es correcto para la convocatoria que desea generar.';
$string['registrarinuse'] = 'Registro de Exámenes predeterminado';
$string['configregistrarinuse'] ='Los exámenes creados por este plugin se almacenan en un Registro de Exámenes.
Esta es la instancia de Registro de Exámenes que contendrá los exámenes generados por este plugin. Identificada por su Número ID Primario<br />
En un principio "Generar Examen" buscará instancias de Registro de Exámenes localizadas en la misma sección y curso y la usará si la encuentra. <br />
Este es el valor predefinido que se usará si no se encuentra antes otra instancia de Registro de Exámenes apropiada..
';
$string['numquestions'] = 'Nº de preguntas por examen';
$string['confignumquestions'] = '
Generar Examen puede forzar la inclusión de un número fijo de preguntas en cada examen. Deje en blanco para anular cualquier límite.';
$string['questionspercategory'] = 'Preguntas por Categoría';
$string['configquestionspercategory'] = '
Generar Examen puede forzar la inclusión de un número mínimo de preguntas de cada categoría del curso en cada examen.Deje en blanco para anular cualquier límite.';
$string['categorysearch'] = 'Patrón de búsqueda de Categorías';
$string['configcategorysearch'] = 'Un patrón para identificar las categorías de preguntas
en las cuales establecer el límite de contenido mínimo anterior.<br />
El parámetro será usado como patrón SQL en una expresión de tipo LIKE. Se pueden usar so comodines SQL, no expresiones regulares';
$string['contextlevel'] = 'Contexto de las categorías';
$string['configcontextlevel'] = '

Las categorías de preguntas están asociadas a un contexto.
Este parámetro permite indicar el tipo de contexto en el que se buscarán categorías de preguntas para aplicar el límite anterior.

';
$string['excludesubcats'] = 'Excluir sub-categorías';
$string['configexcludesubcats'] = '

Si se activa, sólo se incluirán las categorías superiores (sin padre) en la búsqueda de categorías.
';
$string['excludeunused'] = 'Excluir categorías vacías';
$string['configexcludeunused'] = '

Si se activa, sólo se incluirán las categorías CON pregustas en el chequeo por categorías, evitando las vacías que no contienen preguntas.
';

$string['createexams'] = 'Crear y gestionar versiones de examen';
$string['newattempt'] = 'Generar nueva Vista previa como versión';
$string['quizeditinghelp'] = 'Cada versión de examen se genera a partir de una <strong>"Vista previa"</strong> del cuestionario.
Una vez revisada la<strong>"Vista previa"</strong> debe usar el botón <strong>"Generar versión de Examen"</strong> para realmente crear la versión y los PDFs asociados. <br />
Tenga en cuenta que las preguntas presentadas de "Editar Cuestionario" irán cambiando cuando seleccione diferentes convocatorias o versiones del examen.';
$string['reportsettings'] = 'Opciones de generación de examen';
$string['exam'] = 'Examen';
$string['exam_help'] = '
El examen para el que es está generando una versión.

Identificado por la Convocatoria, tipo, turno y sesión en la que se realiza.

';
$string['attemptquestions'] = 'Preguntas de la versión';
$string['attemptquestions_help'] = '

Las preguntas que se emplearán para generar esta versión del examen.
Pueden ser las preguntas actualmente presentes en Cuestionario (Editar cuestionario) o las usadas en una versión previa de este mismo examen.

Si está generando una versión completamente nueva del Examen será apropiado usar las preguntas existentes en "Editar cuestionario".

Si está creando una versión mejorada corrigiendo errores de una versión anterior entonces debe indicar esa versión base aquí.

';
$string['currentquestions'] = 'Preguntas actuales del Cuestionario';
$string['submittedby'] = 'Enviado por';
$string['generatinguser'] = 'Generado por';
$string['status'] = 'Enviado';
$string['sent'] = 'Enviado al Registro';
$string['unsent'] = 'NO enviado';
$string['cleared'] = 'Cuestionario vaciado';
$string['submit'] = 'Enviar al Registro';
$string['submitted'] = 'Enviado';
$string['reviewstatus'] = 'Estado de Revisión';
$string['generateexam'] = 'Generar versión de Examen';
$string['generate_confirm'] = 'Ha solicitado generar una versión del Examen {$a->exam} a partir de esta Vista previa';
$string['errorinvalidquestions'] = 'Esta propuesta de examen contiene {$a} preguntas de tipos inválidos, no permitidos.';
$string['generate_errors'] = 'Problemas:';
$string['error_invalidquestions'] = 'Inválidas: {$a}.';
$string['error_numquestions'] = 'Nº total: {$a->num} / {$a->confignum}.';
$string['error_percategory'] = '{$a->name}: {$a->num} / {$a->confignum}.';
$string['error_othercategories'] = '{$a} preguntas de categorías no aprobadas.';
$string['returnmakeexam'] = 'Volver a Versiones';
$string['delete_confirm'] = 'Ha solicitado borrar la versión {$a->num}, {$a->name} <br />
correspondiente al Examen {$a->exam} <br />

¿Quiere continuar? ';
$string['deleteattempt'] = 'Borrar versión';
$string['pdfpreview'] = 'Vista previa en PDF';
$string['feedback'] = 'Manual: ';
$string['category'] = 'Categoría: ';
$string['tags'] = 'Etiquetas: ';
$string['clear_confirm'] = 'Ha solicitado VACIAR las lista de preguntas de este cuestionario<br />

¿Quiere continuar?  ';
$string['copyold_confirm'] = 'Ha solicitado COPIAR las preguntas de otra asignatura<br />

¿Quiere continuar?  ';
$string['submit_confirm'] = 'Ha solicitado Enviar al Registro la versión {$a->num}, {$a->name} <br />
correspondiente al Examen {$a->exam} <br />

¿Desea continuar? ';
$string['submitattempt'] = 'Enviar versión';
$string['noexamid'] = 'NO existe entrada con la ID {$a} en el Registro de Exámenes.';
$string['noexamorattempt'] = 'No se puede proceder por falta de indicación de Examen o versión.';
$string['noreviewmod'] = 'Generados los archivos de Examen, pero NO se ha especificado una instancia de Revisión por la Junta.';
$string['notracker'] = 'Generados los archivos de Examen, pero NO existe la instancia de Revisión por la Junta.';
$string['alreadyapproved'] = 'No se puede enviar pues ya existe una versión Aprobada de este Examen.';
$string['alreadysent'] = 'No se puede enviar pues ya existe una versión Enviada de este Examen.';
$string['gotoexamreg'] = 'Ir a la Revisión del estado';
$string['setquestions'] = 'Recargar preguntas de esta versión y editar';
$string['questiontags'] = 'Etiquetas de validación de la Junta';
$string['configquestiontags'] = 'Las etiquetas que serán usadas cuando se apruebe o rechace un examen por la Junta de Evaluación.
Tan sólo DOS etiquetas, separadas por una coma (sin espacio). Primero la etiqueta de validación, luego la de rechazo.';
$string['tagvalidated'] = 'Validado por la Junta';
$string['tagrejected'] = 'Rechazado por la Junta';
$string['tagunvalidated'] = 'No revisado por la Junta';
$string['tagremove'] = 'Eliminar cualquier validación por "Tags"';
$string['taginvalidationmsg'] = 'Esta pregunta contiene etiquetas oficiales de revisión por la Junta de Evaluación. <br />
Si edita o cambia algún elemento o usa el botón de "Guardar cambios" al final de la página, dichas etiquetas serán eliminadas.';
$string['clearattempts'] = 'Reiniciar cuestionario';
$string['continueattempt'] = 'Continuar versión previa';
$string['validquestions'] = 'Tipos de preguntas no supervisados';
$string['configvalidquestions'] = 'Estos tipos de preguntas no generan un aviso cuando se usan en un Examen.
Cualquier otro tipo de preguntas usadas por un profesor en un Examen generará un aviso de advertencia.';
$string['validformats'] = 'Formatos para importación';
$string['configvalidformats'] = 'Si se activa, sólo estos formatos serán permitidos a la hora de importar archivos de preguntas externos.';
$string['setpermissions'] = 'Otorgar privilegios adicionales ';
$string['setcourses'] = 'Asignaturas';
$string['setcourses_help'] = 'Aquellas asignaturas en las que hay que establecer permisos extra para sus Tutores';
$string['setroles'] = 'Roles a cambiar';
$string['setroles_help'] = 'Los roles que se modificarán con permisos adicionales. Se deben seleccionar los roles de Tutor.
Los roles listados son aquellos con la capacidad de ver la herramienta Crear Exámenes';
$string['extracapabilities'] = 'Otorgar permisos adicionales para Crear Exámenes';
$string['setcapabilities'] = 'Permisos extra';
$string['setcapabilities_help'] = 'Los permisos adicionales, extra, que serán modificados en los roles y asignaturas indicadas.';
$string['assigncapabilities'] = 'Acción sobre permisos';
$string['assigncapabilities_help'] = 'Indicar si los permisos adicionales van a ser otorgados o retirados para los roles relevantes.';
$string['permissionsset'] = 'Modificados {$a->caps} permisos en {$a->roles} roles de {$a->count} asignaturas.';
$string['copyold'] = 'Copiar preguntas antiguas';
$string['copyoldquestions'] = 'Copiar preguntas de aplicación antigua';
$string['copysource'] = 'Código de asignatura de origen';
$string['copysource_help'] = 'El código de la asignatura origen a la que estaban asociadas las preguntas.

Si se deja en cualquiera se copiarán TODAS las preguntas de todas las asignaturas.';
$string['copystatus'] = 'Estado del campo questionid';
$string['copystatus_help'] = 'Si se ajusta en " = 0 " se evitan duplicados de todas las preguntas ';
$string['unsend'] = 'Revertir a estado de No Enviado';
$string['unsend_confirm'] = 'Ha solicitado Revertir a NO Enviado la versión {$a->num}, {$a->name} <br />
correspondiente al Examen {$a->exam} <br />

¿Desea continuar? ';
$string['unsendattempt'] = 'Revertir estado';
$string['tex_density'] = 'Densidad TeX';
$string['configtex_density'] = 'La densida de puntos a emplear cuando se convierten expresiones TeX a imágenes para insertar en PDFs. Cuanto mayor, más grande la imagen generada.';
$string['tex_imagescale'] = 'Factor de escala TeX';
$string['configtex_imagescale'] = 'Factor reductor de escala aplicado tras generar una imagen a partir de una expresión TeX. Cuanto mayor, más pequeña la imagen mostrada en el PDF.';
$string['enabled'] = 'Usar Generar Examen';
$string['configenabled'] = 'Si se activa entonces las diversas funciones de Generar Examen estarán disponibles en el interfaz de Cuestionarios';
$string['uniquequestions'] = 'Previene uso duplicado de preguntas';
$string['configuniquequestions'] = 'Si se activa, cuando una pregunta se ha añadido a una versión de un examen entonces
ya no podrá ser añadida a otras versiones de exámenes de la misma asignatura. Previene uso de l amisma pregunta en diferentes exámenes del mismo curso.';
$string['eventexamcleared'] = 'Lista de preguntas vaciada en Generar Examen';
$string['eventexamcreated'] = 'Versión de Examen generada';
$string['eventexamdeleted'] = 'Versión de Examen borrada';
$string['eventexamrecalled'] = 'Preguntas de Versión de Examen recolocadas en lista de preguntas';
$string['eventexamsubmitted'] = 'Versión de Examen enviada al Registro';
