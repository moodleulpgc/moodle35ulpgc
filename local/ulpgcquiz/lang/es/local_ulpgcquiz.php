<?php
/**
 * Cadenas de texto de la extensión de sincronización de la ULPGC
 *
 * @package local_ulpgcquiz
 * @copyright  2016 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Cuestionarios ULPGC';
$string['[ulpgcquiz:manage'] = 'Gestionar Cuestionarios ULPGC';

// settings
$string['quizsettings'] = 'Cuestionarios ULPGC';
$string['advancedquizs'] = 'Habilitar interfaz avanzado';
$string['explainadvancedquizs'] = 'Si se activa, las páginas de Cuestionarios mostrarán herramientas y comportamientos adicionales.';

$string['sectionempty'] = 'Vaciar sección';
$string['confirmsectionempty'] =  'Ha solicitado vaciar la sección \'{$a}\'. 
Esto eliminará todas las preguntas de la misma salvo la primera. <br />
¿Desea proseguir con esta acción?';
$string['sectionemptied'] = 'Seccion vaciada. Eliminadas {$a} preguntas';
$string['eventsectionemptied'] = 'Sección de cuestionario vaciada';

// export releted strings
$string['exportdownload'] = 'Descarga de la Exportación';
$string['exportoptions'] = 'Opciones de exportación';
$string['exportquiz'] = 'Exportar cuestionario';
$string['exporttype'] = 'Tipo de exportación';
$string['exporttype_help'] = '
Tipo de documento que se desea exportar.

El formato PDF preserva las imágenes y los enlaces, pero no es editable.

La exportación a MS-Word doc y a OpenDocument odt está basada en una conversión desde HTML
y puede no ser exacta y necesitar ajustes posteriores, incluyendo la revisión de las imágenes, que se enlazan, no se incluyen.';
$string['exporthtml'] = 'como HTML';
$string['exportpdf'] = 'como PDF';
$string['exportdocx'] = 'como DOCX';
$string['exportdoc'] = 'como DOC (html)';
$string['exportodt'] = 'como ODT (html)';
$string['examname'] = 'Examen';
$string['examname_help'] = '
El nombre de este examen en concreto.

Por ejemplo "Parcial adelantado" o "Examen Final, Convocatoria Ordinaria". ';
$string['examdate'] = 'Fecha del examen';
$string['examissue'] = 'Versión del examen';
$string['examissue_help'] = '
Moodle puede generar varias vesiones aleatorias del mismo examen (barajando las preguntas y dentro de las opciones).
En este caso, aquí puede introducir una letra o código que identificará esta versión a efectos de la plantilla para su corrección.';
$string['exportcolumns'] = 'Columnas';
$string['exportcolumns_help'] = '
El número de columnas de texto en la lista de preguntas.

Esta opción sólo se aplica en el caso de exportación a PDF.
';
$string['examdegree'] = 'Titulación';
$string['examdegree_help'] = '
La titulación y el curso al que corresponde este examen.
Por ejemplo "1º de Enfermería" o "3º de Ingeniería Industrial" ';
$string['examcourse'] = 'Asignatura';
$string['examcourse_help'] = '
El curso o asignatura correspondiente a este examen';
$string['examgrid'] = 'Incluir hoja de respuestas';
$string['examgrid_help'] = '
La hoja de respuestas es una tabla con los números de las preguntas y casillas para marcar las opciones respondidas.';
$string['examgridrows'] = 'Número de opciones';
$string['examgridrows_help'] = '
En máximo número de opciones (casillas) que se incluirán en la hoja de respuestas para cada pregunta.';
$string['examanswers'] = 'Incluir las respuestas correctas';
$string['examanswers_help'] = '
Si se activa esta opción la respuesta correcta para cada pregunta será incluida a continuación del enunciado de la misma, de forma separada y distintiva.

Si la hoja de respuestas también está activa, se generará una plantilla que incluya las casillas marcadas correctamente para cada pregunta.';
$string['exportnoattempt'] = 'No hay una Vista previa que exportar. <br />
Debe realizar una Vista previa del cuestionario, y validarla mediante su finalización, para poder exportar dicha Vista previa.';
$string['downloadexam'] = 'Descargar el examen';
$string['downloadwithanswers'] = 'Descargar examen con respuestas';
$string['bestavgn'] = 'Promedio de {$a} mejores';
