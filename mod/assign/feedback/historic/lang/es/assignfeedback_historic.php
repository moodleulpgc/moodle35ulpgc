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
 * Strings for component 'assignfeedback_historic', language 'es'
 *
 * @package   assignfeedback_historic
 * @copyright 2014 Enrique Castro, ecastro  @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['default'] = 'Habilitado por defecto';
$string['default_help'] = 'Si se activa la opción, los Históricos estarán habilitado por omisión para todas las tareas nuevas.';
$string['enabled'] = 'Histórico de Calificaciones';
$string['enabled_help'] = 'Si se activa, el calificador podrá almacenar y consultar un Histórico de calificaciones de años anteriores. ';
$string['historic:manage'] = 'Gestionar plugin Historico';
$string['historic:view'] = 'Ver el Histórico';
$string['historic:submit'] = 'Escribir en Historico';
$string['historic'] = 'Histórico de Calificaciones';
$string['pluginname'] = 'Histórico de Calificaciones';
$string['exams'] = 'Exámenes';
$string['activities'] = 'Actividades';
$string['practice'] = 'Prácticas';
$string['attendance'] = 'Asistencia';

$string['agespan'] = 'Anualidades anteriores';
$string['agespan_help'] = 'Nº de anualidades previas que se mostrarán en el resumen de la extensión, además de la anualidad actual.';
$string['managedatatypes'] = 'Gestión de tipos de calificación';
$string['managedatatypes_help'] = '
Un Tipo de Calificación es cada item puntuable por separado que se quiera guardar en el Histórico.

Se puede añadir, editar o borrar los Tipos de Calificación disponibles.';
$string['delete_confirm'] = 'Ha solicitado borrar el Tipo de Calificación denominado {$a} <br />
¿Confirma que quiere proceder con el borrado?';
$string['annuality'] = 'Anualidad';
$string['annuality_help'] = 'La anualidad actual. Debe consistir en un código de dos años sucesivos tal como 201314, 2013-14, 2013/14, 2013-2014';
$string['grade'] = 'Calificación';
$string['comment'] = 'Comentario';
$string['datatype'] = 'Ítem de Calificación';
$string['datatype_help'] = '
Un Tipo o Ítem de Calificación es cada item puntuable por separado que se quiera guardar en el Histórico.

Para cada Ítem de Calificación debe introducir un nombre o código interno (una plabra con hasta 30 caracteres alfanuméricos sin espacios ni puntuación)
y un nombre que será visible en las tablas del Histórico.';
$string['datatypeadd'] = 'Agregar un Ítem de Calificación';
$string['datatypeupdate'] = 'Modificar un Ítem de Calificación';
$string['maxlengtherror'] = 'El texto introducido debe ser menor de 255 caracteres.';
$string['datatypes'] = 'Ítems de Calificación';
$string['datatypes_help'] = 'Los ítems de calificación realmente puestos en uso por este Histórico, de los disponibles.';
$string['export'] = 'Exportar el Histórico';
$string['downloadexport'] = 'Descargar Histórico como archivo';
$string['exportfile'] = 'Histórico';
$string['import'] = 'Importar al Histórico';
$string['importhistoric'] = 'Importar al Histórico';
$string['copyfrom'] = 'Copiar de otras calificaciones al Histórico';
$string['copyto'] = 'Copiar Histórico a otras calificaciones';
$string['batchoperationconfirmcopyfrom'] = '¿Desea copiar las calificaciones de otra Tarea al Histórico actual?';
$string['batchoperationconfirmcopyto'] = 'Desea copiar los datos de este Histórico a las calificaciones de otra Tarea?';
$string['batchcopyfrom'] = 'Copiar calificaciones al Histórico para múltiples estudiantes.';
$string['batchcopyfromforusers'] = 'Copiar calificaciones al Histórico para {$a} usuarios seleccionados.';
$string['selectedusers'] = 'Estudiantes seleccionados';
$string['copygrade'] = 'Copiar calificaciones';
$string['pass'] = 'Aprobados';
$string['fail'] = 'Suspendidos';
$string['override'] = 'Actualizar';
$string['override_help'] = 'Si se activa, entonces los datos copiados o importados se sobre-escribirán a cualquier dato anterior anterior existente (para la anualidad actual, no las anteriores).';
$string['withcomment'] = 'Copiar también los comentarios de corrección. ';
$string['copyfromcopied'] = 'Copiadas las calificaciones al Histórico para {$a} estudiantes.';
$string['uploadcsvfile'] = 'Cargar archivo CSV';
$string['uploadcsvfile_help'] = 'Cargar archivo CSV.

Se pueden introducir datos en el Histórico (sólo anualidad actual) cargando un archivo de texto CSV.
Las columnas de datos obligatorias son el DNI y el Ítem de Calificación. El resto son opcionales.

La mejor manera de garantizar el uso de columnas de datos correctas consiste en primero <i>descargar</i> un archivo de exportación desde el Histórico,
para luego rellenar los datos apropiados en ese archivo y finalmente cargar el archivo así generado.

Por favor, tenga en cuenta que la importación sólo modifica los datos de la anualidad actual, nunca de anualidades anteriores.
De hecho el importador <strong>ignorará</strong> cualquier columna indicadora de anualidad y tratará de escribir cualquier dato importado sobre la anualidad actual..
';
$string['uploadtableexplain'] = 'Esta es una previsualización de los primeros registros en el archivo CSV que trata de importar.
Por favor, compruebe que el sistema está interpretando correctamente la estructura y datos del archivos.';
$string['uploadconfirm'] = '¿Desea proceder con la carga del archivo CSV?';
$string['numimported'] = 'Cargadas {$a} filas de datos desde el archivo CSV.';
$string['setdefault'] = 'Establecer Histórico';
$string['setdefaultconfirm'] = '
Esta acción establecerá un registro en la base de datos que indica que se ha completado el Histórico de calificaciones de esta asignatura. <br />
En cualquier caso, puede añadir nuevas calificaciones en cualquier momento, también después de esta acción.
';
$string['uploadlink'] = 'Importar/Actualizar datos de Histórico';
$string['uploadlink_help'] = 'Los datos en la tabla de Histórico se pueden importar o actualizar desde un fichero CSV externo. <br />
Los datos importado deben incluir campos para la anualidad, código identificador de asoigantura y usuario (DNI)
así como el tipo de dato y la calificación y/o comentario histórico correspondiente.';
$string['updatedata'] = 'Importar/Actualizar Histórico';
$string['updatelink'] = 'Actualizar tareas Histórico desde la tabla Histórico';
$string['updatelink_help'] = 'Cuando los datos de la tabla Histórico se cargan de forma interna
este enlace permite actualizar las instancias de tarea de tipo Histórico que sean afectadas por los nuevos datos (usuarios o cursos). <br />
Esto puede llevar bastante tiempo.';
$string['numupdated'] = 'Actualizado histórico de {$a} estudiantes';
$string['useridnumber'] = 'DNI';
$string['courseidnumber'] = 'Código asignatura';
