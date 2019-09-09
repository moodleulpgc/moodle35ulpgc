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
 * @package     mod_vídeolib
 * @category    string
 * @copyright   2018 Enrique Castro @ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addmappinglink'] = 'Agregar nueva entrada';
$string['annuality'] = 'Anualidad';
$string['backtovídeo'] = 'Volver al vídeo';
$string['chooseavariable'] = 'Escoja una variable...';
$string['configdisplayoptions'] = 'Seleccione todas las opciones que deban estar disponibles; Los ajustes ya existentes no se modifican.
Presione la tecla CTRL para seleccionar múltiples campos.';
$string['configrolesinparams'] = 'Activar si se desea incluir los roles en la lista de parámetros variables.';
$string['configsecretphrase'] = 'Esta frase secreta se usa para cifrar los valores enviados a algunos servidores externos como parámetros.  
En cifrado se realiza por un md5 de la dirección IP del usuario y la frase secreta. ie code = md5(IP.secretphrase). 
Por favor, tenga en cuenta que este mecanismo NO es fiable, pues la dirección IP puede cambiar o puede ser compartida por varios ordenadores. ';
$string['confirmdelmessage'] = 'Se van a borrar {$a} entradas correspondientes a estos parámetros.';
$string['confirmdelete'] = '¿Confirma borrado?';
$string['defaultfilename'] = 'vídeolib_mapping';
$string['del'] = 'Borrar';
$string['delmappinglink'] = 'Borrar entradas';
$string['displayoptions'] = 'Opciones de visualización disponibles';
$string['displayselect'] = 'Mostrar';
$string['displayselectexplain'] = 'Elegir tipo (desafortunadamente no todos los tipos funcionan en todos los archivos).';
$string['displayselect_help'] = 'Este ajuste, junto con el tipo de archivo, y siempre que el navegador permita incrustar código, determina cómo se muestra el archivo.
Las opciones pueden incluir:

* Automático - Se selecciona de forma automática la mejor opción para visualizar el archivo
* Incrustar - el fichero se muestra dentro de la página debajo de la barra de navegación junto con la descripción y cualquier otro bloque
* Forzar descarga - Se le pregunta al usuario si desea descargar el fichero
* Abrir - Sólo se muestra la dirección en la ventana del navegador
* En ventana emergente - La URL se muestra en una ventana nueva del navegador sin menús y sin barra de direcciones
';
$string['entryadded'] = 'Añadida una entrada de clave de vídeo';
$string['entryupdated'] = 'Actualizada entrada de clave de vídeo';
$string['dberror'] = 'Operación clave de vídeo no realizada por un erroroen la base de datos.';
$string['eventmappingdeleted'] = 'Borrado mapeo de fuente';
$string['eventmappingdownloaded'] = 'Descargado mapeo de fuente';
$string['eventmappingmapped'] = 'Mapeo de fuente establecido';
$string['eventmanageviewed'] = 'Visualizada tabla de mapeo de fuente';
$string['export'] = 'Exportar';
$string['exportfilename'] = 'Nombre del fichero de exportación';
$string['exportformatselector'] = 'Formato de datos';
$string['idnumbercat'] = 'IDnumber de categoría';
$string['import'] = 'Importar';
$string['manage'] = 'Gestionar fuentes de vídeo';
$string['managevídeolibsources'] = 'Gestionar fuentes de vídeo';
$string['managedel'] = 'Borrar entradas de fuentes de vídeo';
$string['managedel_help'] = 'Puede indicar términos de búsqueda para encontrar y borrar múltiples fuentes de video';
$string['manageexport'] = 'Exportar mapeo de fuentes de video';
$string['manageexport_help'] = 'Allow to download a file copy of selected records from the Video sources mapping table';
$string['manageimport'] = 'Import Video sources mapping';
$string['manageimport_help'] = 'Allow to upload a CSV file with records corresponding to a mapping of keys and remote IDs.

The file must have a first line with 4 colums headers vídeolibkey,source,annuality,remoteid 

';
$string['manageview'] = 'Remote vídeos ID mapping';
$string['manageview_help'] = 'Manage the remote vídeos ID mapping table';
$string['mapping'] = 'Instance mapping';
$string['mappingdeleted'] = 'Deleted {$a} entries in vídeo mapeo de fuente table';
$string['modulename'] = 'Videoteca';
$string['modulename_help'] = 'El módulo Vídeoteca permite presentar fácilmente en el curso vídeos almacenados en un repositorio institucional. 
El vídeo se presentará preferenetemente dentro del interfaz del curso.';
$string['modulename_link'] = 'mod/vídeolib/view';
$string['modulenameplural'] = 'Videotecas';
$string['page-mod-vídeolib-x'] = 'Any Video library module page';
$string['parameterinfo'] = 'parámetro=variable';
$string['parametersheader'] = 'Parámetros';
$string['parametersheader_help'] = 'Algunas variables internas de Moodle pueden ser añadidas automáticamente al la URL. Escriba el nombre del parámetro en cada caja de texto y seleccione la variable correspondiente.';
$string['pluginadministration'] = 'Administración de Videoteca';
$string['pluginname'] = 'Videoteca';
$string['popupheight'] = 'Altura (en píxels) de la ventana emergente';
$string['popupheightexplain'] = 'Especifica la altura por defecto de las ventanas emergentes.';
$string['popupresource'] = 'Este recurso debe aparecer en una ventana emergente';
$string['popupwidth'] = 'Anchura (en píxels) de la ventana emergente';
$string['popupwidthexplain'] = 'Especifica la anchura por defecto de las ventanas emergentes.';
$string['printintro'] = 'Mostrar descripción del recurso';
$string['printintroexplain'] = '¿Mostrar la descripción del recurso debajo del contenido? Algunos tipos de visualización pueden no mostrar la descripción incluso aunque esté activada esa opción.';
$string['printheadingexplain'] = '¿Mostra el nombre de la página encima del contenido?';
$string['privacy:metadata'] = 'El módulo Videoteca no almacena ningún dato personal de usuarios.';
$string['remoteid'] = 'ID remota';
$string['removebefore'] = 'Borrar todo antes de importar';
$string['removebefore_help'] = 'Si se marca, todas las entradas existentes serán borradas antes de importar las nuevas. ';
$string['removebeforeexplain'] = 'Marcar para borrar todo antes de importar';
$string['rolesinparams'] = 'Incluir roles en los parámetros';
$string['rownum'] = '#';
$string['searchpattern'] = 'Patrón de búsqueda';
$string['searchpattern_help'] = 'Un texto o patrón, incluyendo parámetros variables usado para encontrar el vídeo en la biblioteca. 
Se presentará el primer vídeo cuya clave coincida con el patrón especificado.';
$string['searchtype'] = 'Tipo de búsqueda';
$string['searchtype_help'] = 'Cómo se localizará el video a mostra dentro de la biblioteca.  
Puede ser uno de:

 * Identificador: un número o código alfanumérico único que identifica el vídeo en la bibliteca.

 * Patrón: un patrón construido con algunas variables que toman valores de los parámetros de debajo.

';
$string['searchtype_id'] = 'Identificador';
$string['searchtype_pattern'] = 'Patrón';
$string['separator'] = 'Separador';
$string['separatorexplain'] = 'Un carácter que enmarca el nombre de la variable; por ejemplo, #shortname#';
$string['serverurl'] = 'Server url';
$string['settings'] = 'Opciones genéricas';
$string['source'] = 'Videoteca';
$string['source_help'] = 'Repositorio conteniendo los vídeos';
$string['sourceheader'] = 'Fuente de vídeo';
$string['updateonimport'] = 'Actualizar al importar';
$string['updateonimport_help'] = 'Qué hacer cuando los datos importados ya existen en el mapeo de vídeos.. 

Si se marca entonces la ID remota existente en el fichero CSV para una clave reemplazará a la ID existente. 

Desmarcar para ignorar los nuevos valores y mantener las ID remotas existentes para las claves existentes.
';
$string['updateonimportexplain'] = 'Marcar para actualizar elementos existentes';
$string['vídeolibsourceplugins'] = 'Video source plugins';
$string['vídeolib:addinstance'] = 'Add a new Video library instance';
$string['vídeolib:view'] = 'View a Video library resource';
$string['vídeolib:edit'] = 'Edit Video library module options';
$string['vídeolib:manage'] = 'Manage Video library mapeo de fuente';
$string['vídeolib:download'] = 'Download Video library mapeo de fuente';
$string['vídeolibkey'] = 'Clave de video';
$string['view'] = 'Gestionar';
