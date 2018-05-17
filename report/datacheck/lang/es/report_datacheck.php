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
 * Plugin strings are defined here.
 *
 * @package     report_datacheck
 * @category    string
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['datacheck:download'] = 'Descargar archivos del módulo Base de datos';
$string['datacheck:remind'] = 'Enviar recordatorios desde informe Comprobar cumplimiento';
$string['datacheck:setvalue'] = 'Establecer valor en lotes con informe de Cumplimiento';
$string['datacheck:view'] = 'ver informe de Cumplimiento por campos de BD';

$string['pluginname'] = 'Comprobar cumplimiento en BD';
$string['checkcompliance'] = 'Comprobar Cumplimiento';
$string['downloadfiles'] = 'Descargar archivos';
$string['downloadfiles_help'] = 'Permite descargar un archivo ZIP conteniendo los archivos 
correspondientes a los campos de tipo Archivo o Imagen. 
El archivo ZIP puede ser organizado en carpetas por nombre de usuario o según el campo especificado.';
$string['enabledcheck'] = 'Habilitar la Comprobación de cumplimiento';
$string['explainenabledcheck'] = 'Si se habilita, se mostrará un enlace a la herramienta Comprobación de Cumplimiento 
entre las opciones de Administración del módulo Base de datos.';
$string['enableddown'] = 'Habilitar descarga de ficheros';
$string['explainenableddown'] = 'Si se activa, se mostrará un enlace a Descargar archivos entre las opciones de Administración del módulo Base de datos.';
$string['parsemode'] = 'Modo de búsqueda de usuarios';
$string['explainparsemode'] = 'Cómo realizar la búsqueda de usuarios cuando se comprueba el cumplimiento en campos específicos. 
El texto del campo puede ser interpretado como un nobre de curso, o de categoría o una propiedad del usuario.';
$string['courseroles'] = 'Roles en curso';
$string['explaincourseroles'] = 'Los usuarios con estos roles en sus asignaturas serán buscado como se indica arriba. 
El primer usuario con uno de estos roles será seleccionado. ';
$string['categoryroles'] = 'Roles en Categoría';
$string['explaincategoryroles'] = 'Los usuarios con estos roles  serán buscados como se indica arriba, 
pero aquí comprobando roles en categorías, no en asignaturas. 
El primer usuario con uno de estos roles será seleccionado.';
$string['shortname'] = 'Código de asignatura';
$string['fullname'] = 'Nombre de asignatura';
$string['category'] = 'Nombre de Categoría';
$string['short-full'] = 'Combinación código-nombre de asignatura';
$string['useridnumber'] = 'DNI';
$string['userfull'] = 'Nombre completo';
$string['userfullrev'] = 'Nombre completo (por apellido)';

$string['checkedfieldoptions'] = 'Comprobación de entradas';
$string['byuser'] = 'Por usuario';
$string['checkby'] = 'Comprobar según';
$string['checkby_help'] = 'Cómo se comprobará el cumplimiento

    *   Según usuario: se espera una entrada (o varias) para cada usuario en la asignatura.
    *   Según un campo: La base de datos debe contener una entrada para cada opción existente en un campo de tipo menú/casilla/radio.

';
$string['isempty'] = 'está vacío';
$string['noempty'] = 'no está vacío';
$string['contain'] = 'contiene';
$string['checkedfield'] = 'Campo a comprobar';
$string['checkedfield_help'] = 'Verifica si cada entrada en la Base de datos contiene un valor/estado indicado en este campo.';
$string['approved'] = 'Aprobado';
$string['datafield'] = 'Conteniendo el campo';
$string['datafield_help'] = 'La comprobación anterior solo tendrán en consideración entradas con esta opción.';
$string['whatrecords'] = 'Verificar solo entradas ... ';
$string['userparsemode'] = 'Modo de búsqueda de usuarios';
$string['userparsemode_help'] = 'User search mode';
$string['complymode'] = 'Cumplimiento';
$string['complymode_help'] = 'Qué entradas seleccionar y mostrar después de la comprobación de cumplimiento.

    *   Concuerdan: aquellas entradss que cumplen los parámetros especificados.
    *   No concuerdan: aquellas entradas que NO cumplen los parámetros especificados.
    *   Duplicados: entradas con valores duplicados para los parámetros indicados.

';
$string['comply'] = 'Entradas concordantes';
$string['noncomply'] = 'Entradas NO concordantes';
$string['duplicates'] = 'Entradas duplicadas';
$string['checkbyerror'] = 'Esta opción necesita una selección; nopuede ser "ninguno"';
$string['checkedfielderror'] = 'El campo comprobado debe ser especificado, no puede ser "cualquiera". ';
$string['nofiles'] = 'No hay archivos que descargar';
$string['nofilefields'] = 'No hay campos de tipo Archivo o Imagen en la actividad Base de datos.';
$string['downloadtype'] = 'Qué archivos descargar y cómo organizarlos';
$string['downfield'] = 'Archivos en campo';
$string['downfield_help'] = 'Archivos contenidos en el campo indicado para cada entrada serán descargados.';
$string['allfiles'] = 'Cualquier tipo de archivo';
$string['groupfield'] = 'Organizar en carpetas por';
$string['groupfield_help'] = 'El archivo ZIP se organizará en carpetas.

Una carpeta para cada usuario o para cada opción distinta en el campo empleado.
Todos los archivos recopilados se almacenarán en la carpeta correspondiente. ';
$string['groups'] = 'Perteneciente al grupo';
$string['successsetvalue'] = 'Valor establecido con éxito en {$a} entradas.';
$string['successemail'] = '{$a->sent} recordatorios enviados y {$a->errors} errores.';
$string['aboutoption'] = 'Relativo a {$a}. ';
$string['inrecord'] = 'En {$a}';
$string['checkedcompliance'] = 'Resultados de la Comprobación de Cumplimiento.';
$string['checkedcompliance_help'] = 'Los resultados de la Comprobación de Cumplimiento en forma tabular.

Puede enviar recordatorios/avisos o felicitaciones a los usuarios o 
establecer un valor determinado en un campo específico en todas las entradas concernidas.

Simplemente marque los usuarios/entradas en los que desea operar y despliegue una de ls herramientas de abajo.';
$string['returntomod'] = 'Volver a la actividad';
$string['norecords'] = 'No se encuentran entradas';
$string['recordslist'] = 'Lista de resultados';
$string['sendmessage'] = 'Enviar mensaje';
$string['defaultsubject'] = 'Recordatorio sobre tareas pendientes en la actividad Base de datos.';
$string['defaultbody'] = 'Hay alguna entrada o campo que debería haber rellenado y permenece pendiente.'; 
$string['checkedrecordsheader'] = 'Contenido del campo | Usuarios relacionados';
$string['setfield'] = 'Campo en el que establecer valor';
$string['setfield_help'] = 'El campo seleccionado se actualizará con el valor especificado en todas las entradas de arriba.';
$string['setvalue'] = 'Establecer valor en campo';
$string['messagesubjectbody'] = 'Componentes del Mensaje';
$string['valueset'] = 'Valor';
$string['mailfrom'] = 'Comprobación de Cumplimiento en Base de datos';
$string['mailerror'] = 'Error en correo';
$string['eventreportviewed'] = 'Visto informe de Comprobación de cumplimiento';
$string['eventreportdownload'] = 'Descargados archivos con informe de Cumplimiento';
$string['eventreportupdated'] = 'Establecido valor de campo con informe de Cumplimiento';
$string['eventreportsent'] = 'Enviados avisos con el informe de Cumplimiento';
