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
 * @package     mod_library
 * @category    string
 * @copyright   2019 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addfiles'] = 'Agregar archivos al repositorio {$a}';
$string['addfiles_help'] = 'Los archivos añadidos a la caja serán subidos y almacenado sen el repositorio remoto.
Se usará la carpeta indicada arriba como punto de inserción. 

Puede añadir otras carpetas a la caja de archivos. Todas esas carpetas y archivos serán añadidos 
como subcarpetas relativas a partir de la ruta idicada por la carpeta de arriba.';
$string['addfilesdone'] = 'Se han añadido {$a} archivos al repositorio';
$string['chooseavariable'] = 'Escoja una variable...';
$string['configdisplayoptions'] = 'Seleccione todas las opciones que deben estar disponibles en la configuración de instancias. 
Los ajuste ya existentes no son modificados. Mantenga presionaad la tecla CTRL para seleccionar múltiples ítems.';
$string['configrolesinparams'] = 'Marcar si se quiere incluir los nombres de roles (traducidos) en la lista de parámetros disponibles.';
$string['configsecretphrase'] = 'Esta frase secreta se usa para generar un código encriptado que puede ser enviado a algunso servidores como un parámetro más.
El código encriptado corresponde a la función MD5 de la dirección IP del usuario actual concatenada con esta frase secreta, ie. code = md5(IP.secretphrase).
Por favor, tenga en cuenta que esto no es muy fiable ya la IP puede cambiar o ser compartida por variso ordenadores.';
$string['display'] = 'Mostrar';
$string['displayheader'] = 'Doumento mostrado';
$string['displaymode'] = 'Modo de visualización';
$string['displaymode_help'] = 'Cómo se muetran los ítems de la Biblioteca encontrados. Puede ser uno de:

 * Archivo: el contenido de un único documento, el primero encontrado que coincide con el patrón.
 * Carpeta: una lista de ficheros contenidos en la carpeta que coincide con el patrón. Cada uno descargable.
 * Árbol: una vista en árbol ramimicado de la carpeta localizada pro el patrón..

';
$string['displayoptions'] = 'Opciones para mostrar disponibles';
$string['displayselect'] = 'Mostrar';
$string['displayselect_help'] = 'Este ajuste, junto con el tipo de archivo, y siempre que el navegador permita incrustar código, determina cómo se muestra el archivo.
Las opciones pueden incluir:

* Automático - Se selecciona de forma automática la mejor opción para visualizar el archivo
* Incrustar - El archivo se muestra dentro de la página debajo de la barra de navegación junto con la descripción y cualquier otro bloque
* Forzar descarga - Se le pregunta al usuario si desea descargar el fichero
* Abrir - Sólo se muestra la dirección en la ventana del navegador
* En ventana emergente - El archivo se muestra en una ventana nueva del navegador sin menús y sin barra de direcciones
* En nueva ventana- El archivoL se muestra en una ventana nueva del navegador, completa con menús y con barra de direcciones

 ';
$string['displayselectexplain'] = 'Elegir tipo (desafortunadamente no todos los tipos funcionan en todos los archivos).';
$string['enabled'] = 'Disponible';
$string['enabled_help'] = 'Si se activa, el plugin será puesto en uso como una Fuente de la Biblioteca de documentos.';
$string['eventlibrarymanaged'] = 'Vista la página de gestión de archivos de Biblioteca documental';
$string['eventlibraryfilesadded'] = 'Agregados archivos a la Biblioteca documental';
$string['eventlibraryfilesdeleted'] = 'Borrados archivos de la Biblioteca documental';
$string['filenotfound'] = 'No existe ningún ítem que corresponda al patrón especificado ({$a}) en la Biblioteca documental';
$string['idnumbercat'] = 'Category idnumber';
$string['insertpath'] = 'Carpeta de inicio';
$string['insertpath_help'] = 'La ruta dentro del repositorio en al que se insertaran los archivos subidos. 

La entrada puede especificar carpetas y subcarpetas, todas se considerarán relativas a esta inicial.';
$string['library:view'] = 'Ver el contendido de un documento de Biblioteca';
$string['library:addinstance'] = 'Agregar una nueva instancia de Bibliteca documental';
$string['library:edit'] = 'Configurar opciones de una instancia';
$string['library:manage'] = 'Gestionar Bibliotecas y fuentes';
$string['libraryname'] = 'Nombre de la Biblioteca documental';
$string['librarysourceplugins'] = 'Plugins de Fuente de Bibliteca';
$string['managelibrarysources'] = 'Gestionar Fuentes de Biblioteca';
$string['managelibrary'] = 'Gestionar Biblioteca';
$string['managefiles'] = 'Agregar archivos';
$string['manageconfig'] = 'Opciones Globales';
$string['modetree'] = 'Árbol';
$string['modulename'] = 'Biblioteca documental';
$string['modulename_help'] = 'El módulo Biblioteca documental permite dar acceso a archivos almacenados en repositorios institucionales dentro del curso.  

Los respositorios disponibles se establecen de forma centralizada para toda la plataforma. 
Los archivos pueden ser localizadso por su nombre o siguiendo un patrón a partir de los datos del asignatura (parámetros variables).  

Una Biblioteca documental puede utilizarse para compartir libros y manuales institucionales en una asignatura.';
$string['modulename_link'] = 'mod/library/view';
$string['modulenameplural'] = 'Bibliotecas documentales';
$string['page-mod-library-x'] = 'Cualquier página de Biblioteca documental';
$string['parameterinfo'] = 'parámetro = variable';
$string['parametersheader_help'] = 'Variables internas que pueden usarse para definir patrones de búsqueda.';
$string['pathname'] = 'Carpeta';
$string['pathname_help'] = 'Carpeta que contiene los documentos, si existen varias en la Biblioteca. 

Puede ser un texto definido, o bien contener parámetros variables definidos más abajo.
';
$string['pluginname'] = 'Biblioteca documental';
$string['pluginadministration'] = 'Administración de Biblioteca documental';
$string['popupheight'] = 'Altura (en píxels) de la ventana emergente';
$string['popupheight_help'] = 'Especifica la altura por defecto de las ventanas emergentes.';
$string['popupwidth'] = 'Anchura (en píxels) de la ventana emergente';
$string['popupwidth_help'] = 'Especifica la anchura por defecto de las ventanas emergentes.';
$string['printintro'] = 'Mostrar descripción del recurso';
$string['printintro_help'] = 'Si se marca, la descripción de la actividad se muestra antes del contenido de la Biblioteca documental.';
$string['printintroexplain'] = 'Muestra la descripción de la actividad delante del contenido';
$string['privacy:metadata'] = 'La Biblioteca documental no almacena datos privados de los usuarios.';
$string['renameold'] = 'Renombrar archivo existente';
$string['renamenew'] = 'Renombrar archivo subido';
$string['repository'] = 'Tipo de repositorio';
$string['repository_help'] = 'El tipo de repositorio fuente donde se almacenan y buscarán los docuemntos de esta Biblioteca';
$string['repositoryheader'] = 'Fuente de documentos';
$string['repositoryname'] = 'Nombre del repositorio';
$string['repositoryname_help'] = 'El nombre de una instancia concreta de repositorio, si varias disponibles, paar ser usada en esta Biblioteca.';
$string['rolesinparams'] = 'Incluir roles como parámetros';
$string['searchpattern'] = 'Documento';
$string['searchpattern_help'] = 'Un texto que identifica el docuemneto en la Biblioteca. 

Puede ser un nombre de archivo o bien contener parámetros variables definidos más abajo.

Los parámetros se identrifican por el marcador #. 
Por ejemplo, en "Manual-#cod#.pdf" el literal será #cod# sustituido por la variable que se especifique en los parámetros, por ejemplo el código de la asignatura.';
$string['separator'] = 'Separador';
$string['separatorexplain'] = 'Un carácter que enmarca los nombres de variables. Por ejemplo, #shortname#';
$string['updatemode'] = 'Modo de actualización';
$string['updatemode_help'] = 'Qué hacer cuando el nombre de un archivo subido ya existe en el repositorio. 

Puede ser una de cuatro opciones: 

* Reemplazar el archivo existente: el nuevo fichero subido reemplazará al existente, sobre-escribiendo. 
* Renombrar archivo existente: El archivo existente será renombrado y el nuevo añadido con el nombre en uso. 
* Renombrar archivo subido: El archivo existente será conservado con su nombre y el archivo subido tomará un nuevo nombre.
* Conservar, no reemplazar: the existing file is kept and new one NOT added to the repository.

Cuando se renombra, se añade un numeral consecutivo al nombre del archivo existente.

';
$string['update'] = 'Reemplazar el archivo existente';
$string['updateno'] = 'Conservar, no reemplazar';
