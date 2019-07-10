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

$string['chooseavariable'] = 'Escoja una variable...';
$string['configdisplayoptions'] = 'Seleccione todas las opciones que deben estar disponibles en la configuración de instancias. 
Los ajuste ya existentes no son modificados. Mantenga presionaad la tecla CTRL para seleccionar múltiples ítems.';
$string['configrolesinparams'] = 'Marcar si se quiere incluir los nombres de roles (traducidos) en la lista de parámetros disponibles.';
$string['configsecretphrase'] = 'Esta frase secreta se usa para generar un código encriptado que puede ser enviado a algunso servidores como un parámetro más.
El código encriptado corresponde a la función MD5 de la dirección IP del usuario actual concatenada con esta frase secreta, ie. code = md5(IP.secretphrase).
Por favor, tenga en cuenta que esto no es muy fiable ya la IP puede cambiar o ser compartida por variso ordenadores.';
$string['display'] = 'Mostrar';
$string['displaymode'] = 'Modo de visualización';
$string['displaymode_help'] = 'Cómo s emuetran los ítems de la Biblioteca encontrados. Puede ser uno de:

 * Archivo: el contenido de un único documento, el primero encontrado que coincide con el patrón.
 * Carpeta: una lista de ficheros contenidos en la carpeta que coincide con el patrón. Cada uno descargable.
 * Árbol: una vista en árbol ramimicado de la carpeta localizada pro el patrón..

';
$string['displayoptions'] = 'Available display options';
$string['displayselect'] = 'Display';
$string['displayselect_help'] = 'This setting, together with the URL file type and whether the browser allows embedding, determines how the URL is displayed. Options may include:

 * Automatic - The best display option for the URL is selected automatically
 * Embed - The URL is displayed within the page below the navigation bar together with the URL description and any blocks
 * Open - Only the URL is displayed in the browser window
 * In pop-up - The URL is displayed in a new browser window without menus or an address bar
 * In frame - The URL is displayed within a frame below the navigation bar and URL description
 * New window - The URL is displayed in a new browser window with menus and an address bar 
 
 ';
$string['displayselectexplain'] = 'Select display type.';
$string['filenotfound'] = 'There is no item matching the specified pattern in the Document Library';
$string['idnumbercat'] = 'Category idnumber';
$string['library:view'] = 'View Library instance content';
$string['library:addinstance'] = 'Add a new Library instance';
$string['library:addfiles'] = 'Use local files in Library instance';
$string['library:manage'] = 'Manage Library instance configuration options';
$string['libraryname'] = 'Name of library document';
$string['librarysourceplugins'] = 'Library source plugins';
$string['managelibrarysources'] = 'Manage Library sources';
$string['modetree'] = 'Tree';
$string['modulename'] = 'Document Library';
$string['modulename_help'] = 'The Document library module enables a teacher to provide a file stored in a library as a course resource. 
Where possible, the file will be displayed within the course interface; 

A Document library may be used to share institutional manuals or textbooks used in class';
$string['modulename_link'] = 'mod/library/view';
$string['modulenameplural'] = 'Document Libraries';
$string['page-mod-videolib-x'] = 'Any Videlo library module page';
$string['parameterinfo'] = 'parameter=variable';
$string['parametersheader'] = 'Parameters';
$string['parametersheader_help'] = 'Some internal Moodle variables may be used in the pattern search.';
$string['pathname'] = 'Path';
$string['pathname_help'] = 'Path to documents, if several folders in the repository';
$string['pluginname'] = 'Document Library';
$string['pluginadministration'] = 'Video library administration';
$string['popupheight'] = 'Pop-up height (in pixels)';
$string['popupheightexplain'] = 'Specifies default height of popup windows.';
$string['popupwidth'] = 'Pop-up width (in pixels)';
$string['popupwidthexplain'] = 'Specifies default width of popup windows.';
$string['printheading'] = 'Display page name';
$string['printheadingexplain'] = 'Display page name above content?';
$string['printintro'] = 'Display page description';
$string['printintroexplain'] = 'Display page description above content?';
$string['privacy:metadata'] = 'The Document Library plugin does not store any personal data.';
$string['repository'] = 'Repository type';
$string['repository_help'] = 'The source Repository type for the documents in this Library';
$string['repositoryheader'] = 'Document source';
$string['repositoryname'] = 'Repository name';
$string['repositoryname_help'] = 'The the name of a particular instance of the repository, if several available';
$string['rolesinparams'] = 'Include role names in parameters';
$string['searchpattern'] = 'File name pattern';
$string['searchpattern_help'] = 'A pattern to be matched by files or folders in the Document Library';
$string['separator'] = 'Separator';
$string['separatorexplain'] = 'A character that encloses the variable parameter name, for instance #shortname#';
$string['serverurl'] = 'Server url';
$string['settings'] = 'General settings';
