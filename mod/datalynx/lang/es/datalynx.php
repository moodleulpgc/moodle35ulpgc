<?php
// This file is part de mod_approval for Moodle - http://moodle.org/
//
// It is free software: you can redistribute it and/or modify
// it under the terms de the GNU General Public License as published by
// the Free Software Foundation, either version 3 de the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy de the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package mod_approval
 * @copyright 2013 onwards Ivan Šakić, Thomas Niedermaier, Philipp Hager, Michael Pollak, David Bogner
 * @copyright spanish tranlation Enrique Castro @ ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
$string['actions'] = 'Acciones';
$string['alignment'] = 'Alineación';
$string['allowaddoption'] = 'Peritir añadir opciones';
$string['alphabeticalorder'] = '¿Ordenar las opciones alfabéticamente al editar una entrada?';
$string['approval_none'] = 'No requerido';
$string['approval_required_new'] = 'Requerido solo para nuevas';
$string['approval_required_update'] = 'Requerido para nuevas y editadas';
$string['approved'] = 'aprobado';
$string['approvednot'] = 'no aprobado';
$string['ascending'] = 'Ascendente';
$string['authorinfo'] = 'Info. Autor';
$string['autocompletion'] = 'Autocompletado';
$string['autocompletion_help'] = 'Indique si el Autocompletado deb estra activo en el modo de edición.';
$string['autocompletion_textfield'] = 'Campo de Texto';
$string['autocompletion_textfield_help'] = 'Seleccione el módulo y campo de texto para devolver datos de autocompletado.';
$string['browse'] = 'Revisar';
$string['columns'] = 'columnas';
$string['comentarioadd'] = 'Añadir comentario';
$string['comentariobynameondate'] = 'por {$a->name} - {$a->date}';
$string['comentario'] = 'Comentario';
$string['comentariodelete'] = '¿Desea borrar este comentario?';
$string['comentariodeleted'] = 'Comentario borrado';
$string['comentarioedit'] = 'Editarar comentario';
$string['comentarioempty'] = 'Comentario vacío';
$string['comentarioinputtype'] = 'Tipo de comentario';
$string['comentariosallow'] = '¿Permitir comentarios?';
$string['comentarioguardado'] = 'Comentario guardado';
$string['comentarios'] = 'Comentarios';
$string['comentariosn'] = '{$a} comentarios';
$string['comentariosnone'] = 'Sin comentarios';
$string['configanonymousentries'] = 'This switch will enable the possibility de guest/anónimas entradas for todas las  aprobacións. You will still need to turn anónimas on manually in the opciones for each  aprobación.';
$string['configenablerssfeeds'] = 'This switch will enable the possibility de RSS feeds for todas las  aprobacións. You will still need to turn feeds on manually in the opciones for each  aprobación.';
$string['configmaxentries'] = 'This value determines the máximo number de entradas that may be añadidas to a  aprobación activity.';
$string['configmaxfields'] = 'This value determines the máximo number de fields that may be añadidas to a  aprobación activity.';
$string['configmaxfilters'] = 'This value determines the máximo number de filters that may be añadidas to a  aprobación activity.';
$string['configmaxviews'] = 'This value determines the máximo number de vistas that may be añadidas to a  aprobación activity.';
$string['convert'] = 'Convertir';
$string['converttoeditor'] = 'Convertir to editor field';
$string['correct'] = 'Correcto';
$string['csscode'] = 'CSS code';
$string['cssinclude'] = 'CSS';
$string['cssincludes'] = 'Include external CSS';
$string['cssguardado'] = 'CSS guardado';
$string['cssupload'] = 'Upload CSS archivos';
$string['csvdelimiter'] = 'delimitador';
$string['csvenclosure'] = 'marcador de texto';
$string['csvfailed'] = 'Unable to read the raw data from the CSV file';
$string['csvoutput'] = 'CSV output';
$string['csvsettings'] = 'CSV opciones';
$string['csvwithselecteddelimiter'] = '<acronym title=\"Comma Separated Values\">CSV</acronym> text with selected delimiter:';
$string['customfilter'] = 'Filtro a medida';
$string['customfilteradd'] = 'Añadir un filtro a medida';
$string['customfilternew'] = 'Nuevo filtro a medida';
$string['customfilters'] = 'Filtros a medida';
$string['customfiltersnoneindatalynx'] = 'No hay filtros a medida definidos en este  aprobación.';
// Capability strings.
$string['approval:addinstance'] = 'Añadir un nuevo  aprobación';
$string['approval:approve'] = 'Aprobar entradas pendientes';
$string['approval:comentario'] = 'Escribir comentarios';
$string['approval:exportallentries'] = 'Exportar todas las entradas';
$string['approval:exportentry'] = 'Exportar entrada';
$string['approval:exportownentry'] = 'Exportar own entradas';
$string['approval:managecomentarios'] = 'Gestionar comentarios';
$string['approval:manageentries'] = 'Gestionar entradas';
$string['approval:managepresets'] = 'Gestionar presets';
$string['approval:manageratings'] = 'Gestionar valoraciones';
$string['approval:managetemplates'] = 'Gestionar Plantillas';
$string['approval:notifyentryadded'] = 'Notificado al añadidas entrada';
$string['approval:notifyentryapproved'] = 'Notificado al aprobar entrada';
$string['approval:notifyentrydisapproved'] = 'Notificado al rechazar entrada';
$string['approval:notifyentryupdated'] = 'Notificado al actualizar entrada';
$string['approval:notifyentrydeleted'] = 'Notificado al borrar entrada';
$string['approval:notifycomentarioadded'] = 'Notificado al añadir comentario';
$string['approval:notifyratingadded'] = 'Notificado al añadir valoración';
$string['approval:notifyratingupdated'] = 'Notificado al actualizar valoración';
$string['approval:presetsviewall'] = 'Ver presets from todas las users';
$string['approval:rate'] = 'Valorar entradas';
$string['approval:ratingsviewall'] = 'Ver todas las valoraciones';
$string['approval:ratingsviewany'] = 'Ver cualquier valoración';
$string['approval:ratingsview'] = 'Ver valoraciones';
$string['approval:viewanonymousentry'] = 'Ver entradas anónimas';
$string['approval:viewentry'] = 'Ver entradas';
$string['approval:viewindex'] = 'Ver índice';
$string['approval:writeentry'] = 'Escribir entradas';
// End Capability strings.
$string['defaultview'] = 'D';
$string['deletenotenrolled'] = 'Borrar entradas by users not enrolled';
$string['descending'] = 'Descendente';
$string['dfintervalcount'] = 'Número de intervalos';
$string['dfintervalcount_help'] = 'Select how many intervals should be unlocked';
$string['dflateallow'] = 'Mensajes tardíos';
$string['dflateuse'] = 'Permitir mensajes tardíos';
$string['dfratingactivity'] = 'Evaluación de la actividad';
$string['dftimeavailable'] = 'Disponible desde';
$string['dftimedue'] = 'Plazo';
$string['dftimeinterval'] = 'Pause until next entrada is unlocked';
$string['dftimeinterval_help'] = 'Select a time interval until next entrada is unlocked for the user';
$string['dfupdatefailed'] = 'Failed to actualizar  aprobación!';
$string['disapproved'] = 'No aprobadas';
$string['documenttype'] = 'Tipo de documento';
$string['dots'] = '...';
$string['download'] = 'Descargar';
$string['editordisable'] = 'Desactivar editor';
$string['editorenable'] = 'Activar editor';
$string['embed'] = 'Incrustar';
$string['enabled'] = 'Habilidato';
$string['entriesadded'] = '{$a} entrada(s) añadidas';
$string['entriesanonymous'] = 'Permitir anónimas entradas';
$string['entriesappended'] = '{$a} entrada(s) añadidas';
$string['entriesapproved'] = '{$a} entrada(s) aprobadas';
$string['entriesconfirmadd'] = 'Ha solicitado duplicar {$a} entrada(s). ¿Desea continuar?';
$string['entriesconfirmapprove'] = 'Ha solicitado aprobar {$a} entrada(s). ¿Desea continuar?';
$string['entriesconfirmduplicate'] = 'Ha solicitado duplicar {$a} entrada(s). ¿Desea continuar?';
$string['entriesconfirmdelete'] = 'Ha solicitado borrar {$a} entrada(s). ¿Desea continuar?';
$string['entriesconfirmupdate'] = 'Ha solicitado actualizar {$a} entrada(s). ¿Desea continuar?';
$string['entriescount'] = '{$a} entrada(s)';
$string['entriesdeleteall'] = 'Borrar todas las entradas';
$string['entriesdeleted'] = '{$a} entrada(s) borradas';
$string['entriesdisapproved'] = '{$a} entrada(s) rechazadas';
$string['entriesduplicated'] = '{$a} entrada(s) duplicadas';
$string['entries'] = 'Entradas';
$string['entriesfound'] = 'Encontradas {$a} entrada(s)';
$string['entriesimport'] = 'Importar entradas';
$string['entrieslefttoaddtoview'] = 'You must add {$a} more entrada/entradas before you can vista other participants\' entradas.';
$string['entrieslefttoadd'] = 'You must add {$a} more entrada/entradas in order to complete this activity';
$string['entriesmax'] = 'Máximo de entradas';
$string['entriesmax_help'] = 'Número de entradas that are allowed, -1 allows unlimited entradas';
$string['entriesnotguardado'] = 'No entrada was guardado. Please check the format de the uploaded file.';
$string['entriespending'] = 'Pendiente';
$string['entriesrequired'] = 'Entradas requeridas';
$string['entriesguardado'] = '{$a} entrada(s) guardadas';
$string['entriestoview'] = 'Entradas requeridas before vistaing';
$string['entriesupdated'] = '{$a} entrada(s) actualizadas';
$string['entryaddmultinew'] = 'Add nuevo entradas';
$string['entryaddnew'] = 'Añadir un nuevo entrada';
$string['entry'] = 'Entrada';
$string['entryinfo'] = 'Entrada info';
$string['entrylockonapproval'] = 'Bloquear al aprobar';
$string['entrylockoncomentarios'] = 'Bloquear al comentar';
$string['entrylockonratings'] = 'Bloquear al valorar';
$string['entrylocks'] = 'Entrada locks';
$string['entrynew'] = 'Nueva entrada';
$string['entrynoneforaction'] = 'No hay entradas were found for the requested action';
$string['entrynoneindatalynx'] = 'No hay entradas in  aprobación';
$string['entryrating'] = 'Entrada valoración';
$string['entryguardado'] = 'Your entrada has been guardado';
$string['entrysettings'] = 'Entrada opciones';
$string['entrysettingsupdated'] = 'Entrada opciones actualizadas';
$string['entrytimelimit'] = 'Editando time limit (minutes)';
$string['entrytimelimit_help'] = 'Minutes until editing is disabled, -1 sets no limit';
$string['err_numeric'] = 'You must enter a number here. Example: 0.00 or 0.3 or 387';
$string['exportcontent'] = 'Exportar content';
$string['exportadd'] = 'Añadir un nuevo Exportar vista';
$string['export'] = 'Export';
$string['exportall'] = 'Exportar all';
$string['exportpage'] = 'Exportar page';
$string['exportnoneindatalynx'] = 'There are no exports defined for this  aprobación.';
$string['fieldadd'] = 'Añadir un field';
$string['fieldallowautolink'] = 'Permitir autolink';
$string['fieldattributes'] = 'Campo attributes';
$string['fieldcreate'] = 'Crear a nuevo field';
$string['fielddescription'] = 'Campo descripción';
$string['fieldeditable'] = 'Editarable';
$string['fieldedit'] = 'Editando \'{$a}\'';
$string['fieldedits'] = 'Número de edits';
$string['field'] = 'field';
$string['fieldids'] = 'Campo ids';
$string['fieldlabel'] = 'Campo label';
$string['fieldlabel_help'] = 'The field label allows to specify a designated field label that can be añadidas to the vista by means de the [[fieldname@]] field pattern. This field pattern observes the field visibility and is hidden if the field is set to be hidden. The field label can also serve as a field display Plantilla and it interprets patterns de that field if included in the label. For example, with a number field called Número and the field label defined as \'You have earned [[Número]] credits.\' and an entrada where the number value is 47 the pattern [[Número@]] would be displayed as \'You have earned 47 credits.\'';
$string['fieldmappings'] = 'Campo Mappings';
$string['fieldname'] = 'Campo nombre';
$string['fieldnew'] = 'Nuevo {$a} field';
$string['fieldnoneforaction'] = 'No fields were found for the requested action';
$string['fieldnoneindatalynx'] = 'There are no fields defined for this  aprobación.';
$string['fieldnonematching'] = 'No matching fields found';
$string['fieldnotmatched'] = 'The following fields in your file are not known in this approval: {$a}';
$string['fieldoptionsdefault'] = 'Default values (one per line)';
$string['fieldoptions'] = 'Options (one per line)';
$string['fieldoptionsseparator'] = 'Options separator';
$string['fieldrequired'] = 'You must supply a value here.';
$string['fieldrules'] = 'Campo edit reglas';
$string['fieldsadded'] = 'Campos añadidas';
$string['fieldsconfirmdelete'] = 'Ha solicitado borrar {$a} field(s). ¿Desea continuar?';
$string['fieldsconfirmduplicate'] = 'Ha solicitado duplicar {$a} field(s). ¿Desea continuar?';
$string['fieldsdeleted'] = 'Campos borradas. You may need to actualizar the default sort opciones.';
$string['fields'] = 'Campos';
$string['fieldsmax'] = 'Máximo fields';
$string['fieldsnonedefined'] = 'No fields defined';
$string['fieldsupdated'] = 'Campos actualizadas';
$string['fieldvisibility'] = 'Visibile to';
$string['fieldvisibleall'] = 'Everyone';
$string['fieldvisiblenone'] = 'Managers only';
$string['fieldvisibleowner'] = 'Owner and managers';
$string['fieldwidth'] = 'Width';
$string['field_has_duplicate_entries'] = 'There are duplicar entradas, therefore it\'s not possible to set this field to Unique:"Yes" at the moment!';
$string['filemaxsize'] = 'Total size de uploded archivos';
$string['archivosmax'] = 'Max number de uploaded archivos';
$string['filetypeany'] = 'Any file type';
$string['filetypeaudio'] = 'Audio archivos';
$string['filetypegif'] = 'gif archivos';
$string['filetypehtml'] = 'Html archivos';
$string['filetypeimage'] = 'Image archivos';
$string['filetypejpg'] = 'jpg archivos';
$string['filetypepng'] = 'png archivos';
$string['filetypes'] = 'Accepted file types';
// FILTER FORM.
$string['andor'] = 'and/or ...';
$string['and'] = 'AND';
$string['or'] = 'OR';
$string['is'] = 'IS';
$string['not'] = 'NOT';
// FILTER.
$string['filtersortfieldlabel'] = 'Sort field ';
$string['filtersearchfieldlabel'] = 'Buscar field ';
$string['filteradvanced'] = 'Filtro a medida';
$string['filteradd'] = 'Añadir un filter';
$string['filterbypage'] = 'By page';
$string['filtercancel'] = 'Cancelar filter';
$string['filtercreate'] = 'Crear a nuevo filter';
$string['filtercurrent'] = 'Current filter';
$string['filtercustomsearch'] = 'Buscar options';
$string['filtercustomsort'] = 'Sort options';
$string['filterdescription'] = 'Filtro descripción';
$string['filteredit'] = 'Editando \'{$a}\'';
$string['filter'] = 'Filtro';
$string['filtergroupby'] = 'Group by';
$string['filterincomplete'] = 'Buscar condition must be completed.';
$string['filtername'] = 'Registro de datos auto-linking';
$string['filternew'] = 'Nuevo filter';
$string['filternoneforaction'] = 'No filters were found for the requested action';
$string['filterperpage'] = 'Per page';
$string['filtersadded'] = '{$a} filter(s) añadidas';
$string['filtersave'] = 'Guardar filter';
$string['filtersconfirmdelete'] = 'Ha solicitado borrar {$a} filter(s). ¿Desea continuar?';
$string['filtersconfirmduplicate'] = 'Ha solicitado duplicar {$a} filter(s). ¿Desea continuar?';
$string['filtersdeleted'] = '{$a} filter(s) borradas';
$string['filtersduplicated'] = '{$a} filter(s) duplicadas';
$string['filterselection'] = 'Selection';
$string['filters'] = 'Filtros';
$string['filtersimplesearch'] = 'Simple search';
$string['filtersmax'] = 'Máximo filters';
$string['filtersnonedefined'] = 'No filters defined';
$string['filtersnoneindatalynx'] = 'There are no filters defined for this  aprobación.';
$string['filtersupdated'] = '{$a} filter(s) actualizadas';
$string['filterupdate'] = 'Actualizar un existentes filter';
$string['filterurlquery'] = 'Url query';
$string['filtermy'] = 'My filter';
$string['filteruserreset'] = '** Reset my filter';
$string['firstdayofweek'] = 'Monday';
$string['first'] = 'First';
$string['formemptyadd'] = 'You did not fill out any fields!';
$string['fromfile'] = 'Importar from zip file';
$string['generalactions'] = 'General actions';
$string['getstarted'] = 'This  aprobación appears to be nuevo or with incomplete setup. To get the  aprobación started <ul><li>apply a preset in the {$a->presets} section</li></ul> or <ul><li>add fields in the {$a->fields} section</li><li>add vistas in the {$a->views} section</li></ul>';
$string['grade'] = 'Grade';
$string['gradeinputtype'] = 'Grade input type';
$string['grading'] = 'Grading';
$string['gradingmethod'] = 'Grading method';
$string['gradingsettings'] = 'Activity grading opciones';
$string['groupentries'] = 'Group entradas';
$string['groupinfo'] = 'Group info';
$string['headercss'] = 'Custom CSS styles for todas las vistas';
$string['headerjs'] = 'Custom javascript for todas las vistas';
$string['horizontal'] = 'Horizontal';
$string['id'] = 'ID';
$string['importadd'] = 'Añadir un nuevo Importar vista';
$string['import'] = 'Import';
$string['importnoneindatalynx'] = 'There are no imports defined for this  aprobación.';
$string['incorrect'] = 'Incorrect';
$string['index'] = 'Index';
$string['insufficiententries'] = 'more entradas needed to vista this  aprobación';
$string['internal'] = 'Internal';
$string['intro'] = 'Introduction';
$string['invalidname'] = 'Please choose another nombre for this {$a}';
$string['invalidrate'] = 'Invalid  aprobación rate ({$a})';
$string['invalidurl'] = 'The URL you just entered is not valid';
$string['jscode'] = 'Javascript code';
$string['jsinclude'] = 'JS';
$string['jsincludes'] = 'Include external javascript';
$string['jsguardado'] = 'Javascript guardado';
$string['jsupload'] = 'Upload javascript archivos';
$string['lock'] = 'Bloquear';
$string['manage'] = 'Gestionar';
$string['mappingwarning'] = 'All old fields not mapped to a nuevo field will be lost and todas las data in that field will be eliminado.';
$string['max'] = 'Máximo';
$string['maxsize'] = 'Tamaño Máximo';
$string['mediafile'] = 'Archivo multimedia';
$string['reference'] = 'Referencia';
$string['min'] = 'Mínimo';
$string['modulename'] = ' aprobación';
$string['modulename_help'] = 'The  aprobación module may be used for creating a wide range de activities/resources by allowing the instructor/manager to design and create a custom content form from various input elements (e.g.  texts, numbers, images, archivos, urls, etc.), and participants to submit content and vista submitted content.';
$string['modulenameplural'] = 'Registros de datos';
$string['more'] = 'Más';
$string['movezipfailed'] = 'Can\'t move zip';
$string['multiapprove'] = ' Aprobar ';
$string['multidelete'] = ' Borrar  ';
$string['multidownload'] = 'Descargar';
$string['multiduplicate'] = 'Duplicado';
$string['multiedit'] = '  Editarar   ';
$string['multiexport'] = 'Exportar';
$string['multipletags'] = 'Multiple etiquetas found! Ver not guardado';
$string['multiselect'] = 'Multi-select';
$string['multishare'] = 'Compartir';
$string['newvalueallow'] = 'Permitir nuevos valores';
$string['newvalue'] = 'Nuevo valor';
$string['noaccess'] = 'No tiene acceso a esta página';
$string['noautocompletion'] = 'No autocompletion';
$string['nocustomfilter'] = 'Programming error [nocustomfilter]. Please contact your support.';
$string['no aprobacións'] = 'No Registro de datos modules found';
$string['nomatch'] = 'No matching entradas found!';
$string['nomaximum'] = 'No máximo';
$string['notapproved'] = 'Entrada is not aprobadas yet.';
$string['notificationenable'] = 'Activar notifications for';
$string['notinjectivemap'] = 'Not an injective map';
$string['notopenyet'] = 'Sorry, this activity is not available until {$a}';
$string['numberrssarticles'] = 'RSS articles';
$string['numcharsallowed'] = 'Número de characters';
$string['optionaldescription'] = 'Short descripción (optional)';
$string['optionalfilename'] = 'Filename (optional)';
$string['other'] = 'Other';
$string['overwrite'] = 'Overwrite';
$string['overwritesettings'] = 'Overwrite current opciones';
$string['presetadd'] = 'Add presets';
$string['presetapply'] = 'Apply';
$string['presetavailableincourse'] = 'Course presets';
$string['presetavailableinsite'] = 'Site presets';
$string['presetchoose'] = 'choose a predfined preset';
$string['presetdataanon'] = 'with user data anonymized';
$string['presetdata'] = 'with user data';
$string['presetfaileddelete'] = 'Error deleting a preset!';
$string['presetfromdatalynx'] = 'Make a preset de this  aprobación';
$string['presetfromfile'] = 'Upload preset from file';
$string['presetimportsuccess'] = 'The preset has been successfully applied.';
$string['presetinfo'] = 'Saving as a preset will publish this vista. Other users may be able to use it in their  aprobacións.';
$string['presetmap'] = 'map fields';
$string['presetnodata'] = 'without user data';
$string['presetnodefinedfields'] = 'Nuevo preset has no defined fields!';
$string['presetnodefinedviews'] = 'Nuevo preset has no defined vistas!';
$string['presetnoneavailable'] = 'No available presets to display';
$string['presetplugin'] = 'Plug in';
$string['presetrefreshlist'] = 'Refresh list';
$string['presetshare'] = 'Share';
$string['presetsharesuccess'] = 'Saved successfully. Your preset will now be available across the site.';
$string['presetsource'] = 'Preset source';
$string['presets'] = 'Presets';
$string['presetusestandard'] = 'Use a preset';
$string['page-mod- aprobación-x'] = 'Any  aprobación activity module page';
$string['pagesize'] = 'Entradas per page';
$string['pagingbar'] = 'Paging bar';
$string['pagingnextslide'] = 'Next slide';
$string['pagingpreviousslide'] = 'Previous slide';
$string['participants'] = 'Participants';
$string['pleaseaddsome'] = 'Please create some below or {$a} to get started.';
$string['pluginadministration'] = 'Registro de datos activity administration';
$string['pluginname'] = ' aprobación';
$string['porttypeblank'] = 'Entradas vacías';
$string['porttypecsv'] = 'CSV';
$string['randomone'] = 'Una al azar';
$string['random'] = 'Aleatorio';
$string['range'] = 'Intervalo';
$string['rate'] = 'Valorar';
$string['ratingmanual'] = 'Manual';
$string['ratingmethod'] = 'Método de valoración';
$string['ratingno'] = 'Sin valoraciones';
$string['ratingpublic'] = '{$a} can see everyone\'s valoraciones';
$string['ratingpublicnot'] = '{$a} can only see their own valoraciones';
$string['rating'] = 'Valoración';
$string['ratingsaggregate'] = '{$a->value} ({$a->method} de {$a->count} valoraciones)';
$string['ratingsavg'] = 'Promedio de valoraciones';
$string['ratingscount'] = 'Número de valoraciones';
$string['ratingsmax'] = 'Highest valoración';
$string['ratingsmin'] = 'Lowest valoración';
$string['ratingsnone'] = '---';
$string['ratings'] = 'Valoraciones';
$string['ratingsguardado'] = 'Valoraciones guardadas';
$string['ratingssum'] = 'Suma de valoraciones';
$string['ratingsviewrate'] = 'Ver y valorar';
$string['ratingsview'] = 'Ver valoraciones';
$string['ratingvalue'] = 'Valoración';
$string['reference'] = 'Referencia';
$string['requireapproval'] = 'Require aprobación?';
$string['requiredall'] = 'todas requeridas';
$string['requirednotall'] = 'no todas requeridas';
$string['resetsettings'] = 'Reset filters';
$string['returntoimport'] = 'Return to import';
$string['rssglobaldisabled'] = 'Desactivada. Ver opciones de configuración globales.';
$string['rsshowmany'] = '(number de latest entradas to show, 0 to disable RSS)';
$string['rsstemplate'] = 'RSS Plantilla';
$string['rsstitletemplate'] = 'RSS title Plantilla';
$string['ruleaction'] = 'Rule action';
$string['ruleadd'] = 'Añadir una regla';
$string['rulecancel'] = 'Cancelar regla';
$string['rulecondition'] = 'Condición';
$string['rulecreate'] = 'Crear nueva regla';
$string['ruledenydelete'] = 'Impedir borrado de entrada';
$string['ruledenyedit'] = 'Impedir edición de entrada';
$string['ruledenyviewbyother'] = 'Hide entrada from everyone but owner';
$string['ruledenyview'] = 'Hide entrada from everyone';
$string['ruledescription'] = 'Rule descripción';
$string['ruleedit'] = 'Editando \'{$a}\'';
$string['rulename'] = 'Auto-enlazado de Registro de datos';
$string['rulenew'] = 'Nueva {$a} regla';
$string['rulenoneforaction'] = 'No se encontraron reglas para la acción requerida';
$string['rule'] = 'rule';
$string['rulesadded'] = '{$a} regla(s) añadidas';
$string['rulesave'] = 'Guardar regla';
$string['rulesconfirmdelete'] = 'Ha solicitado borrar {$a} regla(s). ¿Desea continuar?';
$string['rulesconfirmduplicate'] = 'Ha solicitado duplicar {$a} regla(s). ¿Desea continuar?';
$string['rulesdeleted'] = '{$a} regla(s) borradas';
$string['rulesduplicated'] = '{$a} regla(s) duplicadas';
$string['rulesmax'] = 'Máximo reglas';
$string['rulesnonedefined'] = 'No hay reglas definidas';
$string['rulesnoneindatalynx'] = 'There are no reglas defined for this  aprobación.';
$string['rules'] = 'Reglas';
$string['rulesupdated'] = '{$a} regla(s) actualizadas';
$string['ruleupdate'] = 'Actualizar un existentes regla';
$string['ruleenabled'] = 'Habilitada';

$string['author'] = 'Autor';
$string['email'] = 'Email';

$string['savecontinue'] = 'Guardar y continuar';
$string['saveasstandardtags'] = 'Guardar etiquetas as standard-tags in order to suggest them when adding or updating an entrada?';
$string['search'] = 'Buscar';
$string['sendinratings'] = 'Send in my latest valoraciones';
$string['separateentries'] = 'cada entrada en archivos separados';
$string['separateparticipants'] = 'Separate participants';
$string['settings'] = 'Opciones';
$string['showall'] = 'Mostrar todas las entradas';
$string['singleedit'] = 'E';
$string['singlemore'] = 'M';
$string['spreadsheettype'] = 'Spreadsheet type';
$string['submissionsinpopup'] = 'Submissions in popup';
$string['submission'] = 'Submission';
$string['submissionsview'] = 'Submissions vista';
$string['subplugintype_approvalfield'] = 'Registro de datos field type';
$string['subplugintype_approvalfield_plural'] = 'Registro de datos field types';
$string['subplugintype_approvalrule'] = 'Registro de datos regla type';
$string['subplugintype_approvalrule_plural'] = 'Registro de datos regla types';
$string['subplugintype_approvaltool'] = 'Registro de datos tool type';
$string['subplugintype_approvaltool_plural'] = 'Registro de datos tool types';
$string['subplugintype_approvalview'] = 'Registro de datos vista type';
$string['subplugintype_approvalview_plural'] = 'Registro de datos vista types';
$string['tagarea_approval_contents'] = 'Registro de datos entradas';
$string['tagcollection_datalynx'] = 'Registro de datos etiquetas';
$string['teachersandstudents'] = '{$a->teachers} and {$a->students}';
$string['textbox'] = 'Text box';
$string['textfield'] = 'Textfield';
$string['textfield_help'] = 'The Textfield to retrieve the autocompletion data of.';
$string['textfieldvalues'] = 'Textfield values';
$string['timecreated'] = 'Time created';
$string['timemodified'] = 'Time modified';
$string['todatalynx'] = 'to this  aprobación.';
$string['tools'] = 'Herramientas';
$string['trusttext'] = 'Trust text';
$string['type'] = 'Type';
$string['unique'] = 'Unique';
$string['unique_required'] = 'Unique text requeridas! This text was already used!';
$string['unlock'] = 'Desbloquear';
$string['updatefield'] = 'Actualizar un campo existente';
$string['updateview'] = 'Actualizar una vista existente';
$string['userinfo'] = 'Info. de usuario';
$string['userpref'] = 'Preferencias de usuario';
$string['usersubmissionsinpopup'] = 'de usuariosubmissions in popup';
$string['usersubmissions'] = 'de usuariosubmissions';
$string['usersubmissionsview'] = 'de usuariosubmissions vista';
$string['vertical'] = 'Vertical';
$string['viewadd'] = 'Añadir una vista';
$string['viewcharactertags'] = 'Character etiquetas';
$string['viewcreate'] = 'Crear nueva vista';
$string['viewcurrent'] = 'Vista actual';
$string['viewcustomdays'] = 'Intervalo de actualización: días';
$string['viewcustomhours'] = 'Intervalo de actualización: horas';
$string['viewcustomminutes'] = 'Intervalo de actualización: minutos';
$string['viewdescription'] = 'Ver descripción';
$string['viewedit'] = 'Editando \'{$a}\'';
$string['vieweditthis'] = 'Editar vista';
$string['viewfieldtags'] = 'Campo etiquetas';
$string['viewfilter'] = 'Filtro';
$string['viewforedit'] = 'Ver for \'edit\'
$string['viewformore'] = 'Ver for \'more\'';
$string['viewfromdate'] = 'Ver desde';
$string['viewgeneraltags'] = 'General etiquetas';
$string['viewgroupby'] = 'Agrupar por';
$string['viewintervalsettings'] = 'Opciones de intervalo';
$string['viewinterval'] = 'When to refresh vista content';
$string['entrytemplate'] = 'Plantilla de Entrada Plantilla';
$string['entrytemplate_help'] = 'Plantilla de Entrada';
$string['viewlistfooter'] = 'List footer';
$string['viewlistheader'] = 'List header';
$string['viewname'] = 'Ver nombre';
$string['viewnew'] = 'Nueva {$a} vista';
$string['viewnodefault'] = 'Default vista is not set. Choose one de the vistas in the {$a} list as the default vista.';
$string['viewnoneforaction'] = 'No vistas were found for the requested action';
$string['viewnoneindatalynx'] = 'There are no vistas defined for this  aprobación.';
$string['viewrepeatedfields'] = 'You can not use the field {$a} more than once.';
$string['viewmultiplefieldgroups'] = 'You can not use more than one fieldgroup.';
$string['toolnoneindatalynx'] = 'There are no tools defined for this  aprobación.';
$string['toolrun'] = 'Run';
$string['viewoptions'] = 'Ver options';
$string['viewpagingfield'] = 'Paging field';
$string['viewperpage'] = 'Per page';
$string['viewresettodefault'] = 'Reset to default';
$string['viewreturntolist'] = 'Return to list';
$string['viewsadded'] = 'Ver añadidas';
$string['viewsconfirmdelete'] = 'Ha solicitado borrar {$a} vista(s). ¿Desea continuar?';
$string['viewsconfirmduplicate'] = 'Ha solicitado duplicar {$a} vista(s). ¿Desea continuar?';
$string['viewsdeleted'] = 'Ver borradas';
$string['viewtemplate'] = 'Ver Plantilla';
$string['viewtemplate_help'] = 'Ver Plantilla';
$string['viewgeneral'] = 'Ver general opciones';
$string['viewgeneral_help'] = 'Ver general opciones';
$string['viewsectionpos'] = 'Section position';
$string['viewslidepaging'] = 'Slide paging';
$string['viewsmax'] = 'Máximo vistas';
$string['viewsupdated'] = 'Ver actualizadas';
$string['views'] = 'Views';
$string['viewtodate'] = 'Viewable to';
$string['view'] = 'view';
$string['viewvisibility'] = 'Visibility';
$string['wrongdataid'] = 'Wrong  aprobación id provided';

// Teammemberselect strings.

$string['teamsize'] = 'Máximo tamaño del grupo';
$string['teamsize_help'] = 'Specify the máximo size de the team. It must be a positive integer.';
$string['teamsize_error_required'] = 'This field is requeridas!';
$string['teamsize_error_value'] = 'The value must be a positive integer!';
$string['admissibleroles'] = 'Admissible roles';
$string['admissibleroles_help'] = 'Users possessing any de the selected roles will be admissible to the team. At least one role must be selected.';
$string['admissibleroles_error'] = 'Please select at least one role!';
$string['notifyteam'] = 'Notification regla';
$string['notifyteam_help'] = 'Select notification regla to be applied to todas las miembros del grupo selected in this field.';
$string['teammemberselectmultiple'] = 'A single person can be selected only once as a team member!';
$string['listformat'] = 'List format';
$string['listformat_newline'] = 'Nuevoline separated';
$string['listformat_space'] = 'Space separated';
$string['listformat_comma'] = 'Comma separated';
$string['listformat_commaspace'] = 'Comma separated with space';
$string['listformat_ul'] = 'Unordered list';
$string['teammembers'] = 'Miembro del grupos';
$string['status'] = 'Status';
$string['status_notcreated'] = 'Not set';
$string['status_draft'] = 'Draft';
$string['status_submission'] = 'Submission';
$string['status_finalsubmission'] = 'Final entrega';
$string['completionentries'] = 'Número de (approved) entradas';
$string['completionentriesgroup'] = 'Require (approved) entradas';
$string['completionentriesgroup_help'] = 'Make sure you enable aprobación for entradas above!<br />
Número de (approved) entradas: Entradas a user has to make. If \'Require aprobación\' is set: Número de entradas equals number de aprobadas entradas only.';
$string['limitchoice'] = 'Limit choices for users';
$string['limitchoice_help'] = 'Activar this to prevent a user from choosing the same option more than the chosen number in separate entradas.';
$string['limitchoice_error'] = 'You have already selected option \'{$a}\' the máximo allowed number de times!';
$string['redirectsettings'] = 'Redirect on submit options';
$string['redirectsettings_help'] = 'Use this fields to specify which vista should the browser redirect to upon leaving the edit vista.';
$string['redirectto'] = 'Target vista';
$string['targetview_this_new'] = 'Esta vista (Nueva)';
$string['targetview_this'] = '(Esta vista)';
$string['targetview_default'] = '(Por defecto)';
$string['targetview_edit'] = '(Editar)';
$string['targetview_more'] = '(Más)';
$string['visibleto'] = 'Visible para';
$string['visible_1'] = 'Administrador';
$string['visible_2'] = 'Profesor';
$string['visible_4'] = 'Estudiante';
$string['visible_8'] = 'Invitado';
$string['statistics'] = 'Estadísticas';
$string['iamteammember'] = 'Soy miembro del grupo';
$string['useristeammember'] = 'El usuario es miembro del grupo';
$string['fromto_error'] = '\'From\' fecha cannot be set after \'To\' fecha!';
$string['me'] = 'Yo';
$string['otheruser'] = 'Otro usuario';
$string['period'] = 'Periodo';
$string['ondate'] = 'En fecha';
$string['fromdate'] = 'Desde fecha';
$string['todate'] = 'Hasta fecha';
$string['alltime'] = 'All time';
$string['approval:editrestrictedfields'] = 'Editar restricted fields';
$string['approval:viewdrafts'] = 'Ver drafts';
$string['approval:viewprivilegeguest'] = 'Guest vista access privilege';
$string['approval:viewprivilegemanager'] = 'Manager vista access privilege';
$string['approval:viewprivilegestudent'] = 'Student vista access privilege';
$string['approval:viewprivilegeteacher'] = 'Teacher vista access privilege';
$string['approval:viewstatistics'] = 'Ver estadísticas';
$string['statisticsfor'] = 'Estadísticas de \'{$a}\'';
$string['timestring0'] = 'de {$a->from} a {$a->to}';
$string['timestring1'] = 'en {$a->from}';
$string['timestring2'] = 'hasta {$a->to}';
$string['timestring3'] = 'de {$a->from} a ahora ({$a->now})';
$string['timestring4'] = 'hasta ahora ({$a->now})';
$string['numtotalentries'] = 'Número de created entradas';
$string['numapprovedentries'] = 'Número de aprobadas entradas';
$string['numdeletedentries'] = 'Número de borradas entradas';
$string['numvisits'] = 'Número de visitas';
$string['modearray'] = 'Display mode';
$string['modearray_help'] = '\'To\' fecha is always considered when available until 23:59:59.';
$string['time_field_required'] = '{$a} field is requeridas!';
$string['statusrequired'] = 'Status must be set!';
$string['fromaftertoday_error'] = '\'From\' fecha cannot be set after today\'s fecha!';
$string['editmode'] = 'Editar mode';
$string['managemode'] = 'Gestionar mode';
$string['maxteamsize_error_form'] = 'You can select only a máximo de {$a} miembros del grupo!';
$string['minteamsize'] = 'Minimum tamaño del grupo';
$string['minteamsize_help'] = 'Enter the miminum allowed number de miembros del grupo here.';
$string['minteamsize_error_value'] = 'Minimum tamaño del grupo cannot be greater than the máximo tamaño del grupo!';
$string['minteamsize_error_form'] = 'You must select at least {$a} miembros del grupo!';
$string['teamfield'] = 'Campo de Grupo';
$string['teamfield_help'] = 'Check this box to designate this field as a team field. When approving an entrada with a specified team that entrada will be copied and assigned to every team member. Only one field per Registro de datos instance may be designated as a team field.';
$string['referencefield'] = 'Campo de Referencia';
$string['referencefield_help'] = 'Select a field to serve as a duplicar prevention field. This will skip creating entradas for users who already have an aprobadas entrada with the same field value as the one being aprobadas.';
$string['linktoentry'] = 'Enlace a entrada';
$string['notifyteammembers'] = 'Notificar a miembros del grupo';
$string['notifyteammembers_help'] = 'Select this option to inform miembros del grupo de their membership status change.';
$string['noentries'] = 'No hay entradas que mostrar.';
$string['nosuchentries'] = 'No hay entradas disponibles.';
$string['nomatchingentries'] = 'No hay entradas coincidentes con el filtro seleccionado.';
$string['nopermission'] = 'You do not have the permission to vista specified entradas.';
$string['approval:notifymemberadded'] = 'Inform users about being añadidas as a team member';
$string['approval:notifymemberremoved'] = 'Inform users about being eliminado as a team member';
$string['approval:viewprivilegeadmin'] = 'Administrator vista access privilege';

$string['eventsettings'] = 'Event opciones';
$string['triggeringevent'] = 'Triggering event';
$string['approval_entryadded'] = 'Entrada añadidas;
$string['approval_entryupdated'] = 'Entradas actualizadas';
$string['approval_entrydeleted'] = 'Entradas borradas';
$string['approval_entryapproved'] = 'Entradas aprobadas';
$string['approval_entrydisapproved'] = 'Entradas rechazada';
$string['approval_comentarioadded'] = 'Comentario añadido';
$string['approval_ratingadded'] = 'Valoración añadida';
$string['approval_ratingupdated'] = 'Valoración actualizada';
$string['approval_ratingdeleted'] = 'Valoración borrada';
$string['approval_memberadded'] = 'Miembro del grupo añadidas';
$string['approval_memberremoved'] = 'Miembro del grupo eliminado';
$string['blankfilter'] = 'Filter vacío';
$string['defaultfilterlabel'] = 'Filtro predefinidofilter ({$a})';
$string['urlclass'] = 'CSS classes';
$string['urltarget'] = '\'target\' attribute';
$string['addoptions'] = 'Añadir opciones';
$string['existingoptions'] = 'Editar opciones existentes';
$string['option'] = 'Opción';
$string['deleteoption'] = '¿Borrar?';
$string['renameoption'] = 'Renombrar a:';
$string['moreresults'] = '({$a}resultados más...)';

$string['setdefault'] = 'Establecer como vista predefinida';
$string['setedit'] = 'Establecer como vista de edición';
$string['setmore'] = 'Establecer como vista detallada';

$string['isdefault'] = 'Vista predfinida';
$string['isedit'] = 'Editar vista';
$string['ismore'] = 'Vista detallada';
$string['nooptions'] = '¡Debe especificar al menos una opción!';

$string['visibility'] = 'Visibilidad';
$string['editing'] = 'Editando';
$string['editable'] = 'Editarable';
$string['required'] = 'Required';
$string['editableby'] = 'Editarable by';
$string['entryauthor'] = 'Entrada author';

$string['behavior'] = 'Behavior';
$string['behaviors'] = 'Behaviors';
$string['behavioradd'] = 'Add behavior';
$string['defaultbehavior'] = 'Default behavior';

$string['renderers'] = 'Renderers';
$string['copyof'] = 'Copy de {$a}';
$string['mentor'] = 'Mentor';
$string['notrequired'] = 'Not requeridas';
$string['newbehavior'] = 'Nuevo field behavior';
$string['editingbehavior'] = 'Editando field behavior "{$a}"';
$string['deletingbehavior'] = 'Deleting field behavior "{$a}"';
$string['duplicatingbehavior'] = 'Duplicating field behavior "{$a}"';
$string['confirmbehaviorduplicate'] = 'Ha solicitado duplicar this field behavior!';
$string['confirmbehaviordelete'] = 'Ha solicitado borrar this field behavior!';
$string['deletingrenderer'] = 'Deleting field renderer "{$a}"';
$string['confirmrendererdelete'] = 'Ha solicitado borrar this field renderer!';
$string['duplicatename'] = 'This nombre already exists. Please choose another one.';
$string['duplicatingrenderer'] = 'Duplicating field renderer "{$a}"';
$string['confirmrendererduplicate'] = 'Ha solicitado duplicar this field renderer!';

$string['notvisible'] = 'When not visible';
$string['novalue'] = 'When empty';
$string['noteditable'] = 'When not editable';
$string['custom'] = 'Custom Plantilla';
$string['displaytemplate'] = 'Display Plantilla';
$string['edittemplate'] = 'Editar Plantilla';
$string['rendereradd'] = 'Add renderer';
$string['asdisplay'] = 'Use display Plantilla';
$string['notemplate'] = 'No Plantilla';
$string['shownothing'] = 'Display nothing';
$string['disabled'] = 'Display disabled elements';
$string['newrenderer'] = 'Nuevo field renderer';
$string['editingrenderer'] = 'Editando field renderer "{$a}"';
$string['required'] = 'Required';
$string['hidden'] = 'Hidden';
$string['noedit'] = 'Not editable';
$string['label'] = 'Label';
$string['defaultrenderer'] = 'Default renderer';
$string['renderer'] = 'Renderer';
$string['fieldname'] = 'Nombre del campo';
$string['fieldtype'] = 'Tipo del campo';
$string['deletetag'] = 'Borrar etiqueta';
$string['action'] = 'Acción';
$string['field'] = 'Campo';
$string['tagproperties'] = '{$a->tagtype} propiedades de etiqueta: {$a->tagname}';
$string['teams'] = 'Grupos';

$string['event_entry_created'] = 'Entrada creada';
$string['event_entry_updated'] = 'Entrada actualizada';
$string['event_entry_deleted'] = 'Entrada borrada';
$string['event_entry_approved'] = 'Entrada aprobada';
$string['event_entry_disapproved'] = 'Entrada rechazada';
$string['event_comentario_created'] = 'Comentario creado';
$string['event_rating_added'] = 'Valoración añadida';
$string['event_rating_updated'] = 'Valoración actualizada';
$string['event_rating_deleted'] = 'Valoración borrada';
$string['event_team_updated'] = 'Grupo actualizads';

// Message strings.
$string['message_entry_created'] = 'Hello {$a->fullname},

the content in {$a-> aprobaciónlink} has been modified by {$a->senderprofilelink}.

The following entrada has been created: {$a->viewlink}.';

$string['message_entry_updated'] = 'Hello {$a->fullname},

the content in {$a-> aprobaciónlink} has been modified by {$a->senderprofilelink}.

The following entrada has been actualizadas: {$a->viewlink}.';

$string['message_entry_deleted'] = 'Hello {$a->fullname},

the content in {$a-> aprobaciónlink} has been modified by {$a->senderprofilelink}.

An entrada has been borradas.';

$string['message_entry_approved'] = 'Hello {$a->fullname},

the content in {$a-> aprobaciónlink} has been aprobadas by {$a->senderprofilelink}.

The following entrada has been aprobadas: {$a->viewlink}.';

$string['message_entry_disapproved'] = 'Hello {$a->fullname},

the content in {$a-> aprobaciónlink} has been modified by {$a->senderprofilelink}.

The following entrada has been deactivated: {$a->viewlink}.';

$string['message_comentario_created'] = 'Hello {$a->fullname},

a comentario to one de your entradas was añadidas by {$a->senderprofilelink}.

The following entrada has been comentarioed on: {$a->viewlink}.';
$string['message_rating_added'] = 'Registro de datos valoración añadidas';
$string['message_rating_updated'] = 'Registro de datos valoración actualizadas';

$string['message_team_updated'] = 'Dear {$a->fullname},

{$a->fieldname} membership has been changed by {$a->senderprofilelink}. Please go to {$a->viewlink} for more details.';
// End Message strings.

// Message provider strings.
$string['messageprovider:event_entry_created'] = 'Registro de datos entrada created';
$string['messageprovider:event_entry_updated'] = 'Registro de datos entrada actualizadas';
$string['messageprovider:event_entry_deleted'] = 'Registro de datos entrada borradas';
$string['messageprovider:event_entry_approved'] = 'Registro de datos entrada aprobadas';
$string['messageprovider:event_entry_disapproved'] = 'Registro de datos entrada disapproved';
$string['messageprovider:event_comentario_created'] = 'Registro de datos comentario created';
$string['messageprovider:event_rating_added'] = 'Registro de datos valoración añadidas';
$string['messageprovider:event_rating_updated'] = 'Registro de datos valoración actualizadas';
$string['messageprovider:event_team_updated'] = 'Registro de datos entrada team actualizadas';
// End Message provider strings event_team_updated.

$string['filterforms'] = 'Formulario de Filtro';
$string['filterformadd'] = 'Añadido Formulario de Filtro';
$string['newfilterform'] = 'Nuevo Formulario de Filtro';

// FILTER.
$string['avoidaddanddeletesimultaneously'] = 'You must not add and borrar options in one step. First borrar the options and save, then rename the options and save again.';
$string['empty'] = 'vacío';
$string['equal'] = 'igual';
$string['between'] = 'entre';
$string['contains'] = 'contiene';
$string['in'] = 'en';
$string['anyof'] = 'alguno de';
$string['allof'] = 'todos';
$string['exactly'] = 'exactamente';
$string['greater_than'] = 'mayor que';
$string['greater_equal'] = 'mayor o igual';
$string['less_than'] = 'menor de';
$string['less_equal'] = 'menor o igual';
$string['before'] = 'delante';
$string['after'] = 'después';

$string['gradeitem'] = 'Ítem de Calificación';
$string['user_can_add_self'] = 'de usuariocan add him/herself';
$string['linksettings'] = 'Message link opciones';
$string['admin'] = 'Administrador';
$string['manager'] = 'Gestor';
$string['teacher'] = 'Profesor';
$string['student'] = 'Estudiante';
$string['guest'] = 'Invitado';
$string['targetviewforroles'] = 'Link target vistas for roles';

$string['subscribe'] = 'Subscribir';
$string['unsubscribe'] = 'Desubscribir';
$string['allowsubscription'] = 'Permitir subscripción manual';
$string['allowunsubscription'] = 'Permitir desubscripción manual';
$string['selectuser'] = 'Select user...';
$string['allowsubscription_help'] = 'Check this option to enable users to add themselves to teams created by other people. This is facilitated via :subscribe etiqueta extension, e.g. [[&lt;fieldname&gt;:subscribe]], which modifies the field to display an additional link in browse mode. By clicking on this link user can add themselves to the particular team, if they are able and allowed to by the field setup.';
$string['allowunsubscription_help'] = 'Check this option to enable users to unsubscribe themselves from teams de other users in a manner similar to the \'Permitir manual subscription option\'. If disabled, users on a team can only be eliminado by the user who created that team.';
$string['user_can_add_self_help'] = 'Check this option to allow the user who owns the entrada to add themselves to the team in this field.';
$string['check_enable'] = 'You must mark \'enable\' checkbox to confirm the validity de the selected value.';
$string['deletefieldfilterwarning'] = 'Warning! You are attempting to borrar following fields:{$a->fieldlist}However, filters listed below are still using some de these fields:{$a->filterlist}You will have to borrar these filters manually first before you may proceed.';
$string['noviewsavailable'] = 'No vistas available';

$string['approval_team_updated'] = 'Grupo actualizado';
$string['approval:editprivilegeadmin'] = 'Admin edit access privilege';
$string['approval:editprivilegeguest'] = 'Guest edit access privilege';
$string['approval:editprivilegemanager'] = 'Manager edit access privilege';
$string['approval:editprivilegestudent'] = 'Student edit access privilege';
$string['approval:editprivilegeteacher'] = 'Teacher edit access privilege';
$string['approval:notifyteamupdated'] = 'Notificado al team update';
$string['approval:teamsubscribe'] = 'Subscribe to/join teams';

$string['approval_cssguardado'] = 'Custom CSS guardado';
$string['approval_jsguardado'] = 'Custom JavaScript guardado';

$string['displaytemplate_help'] = 'Specify HTML Plantilla to replace the field etiqueta in browse mode. To specify the position de the actual value, use #value etiqueta within the Plantilla.';
$string['edittemplate_help'] = 'Specify HTML Plantilla to replace the field etiqueta in edit mode. To specify the position de the actual input element, use #input etiqueta within the Plantilla.';

$string['notallowedtoeditentry'] = 'It\'s not allowed to edit this entrada.';
$string['thisdatalynx'] = 'This  aprobación instance';
$string['thisfield'] = 'Este campo';

$string['fulltextsearch'] = 'Fulltextsearch';
$string['fieldlist'] = 'Campos de búsqueda';
$string['userfields'] = 'de usuariodefined fields';
$string['sortable'] = 'ordenable';
$string['activate'] = 'activar';
$string['fileexist'] = 'existe';
$string['filemissing'] = 'no existe';

$string['fieldsimportsettings'] = 'Importsettings';
$string['uploadfile'] = 'Archivo a importar';
$string['uploadtext'] = 'Texto a importar';
$string['updateexisting'] = 'Actualizar existentes';

// Privacy API
$string['privacy:metadata: approval_entries'] = 'Represent entradas in a  aprobación instance.';
$string['privacy:metadata: approval_entries:userid'] = 'de usuariowho created the record';
$string['privacy:metadata: approval_entries:groupid'] = 'Group';
$string['privacy:metadata: approval_entries:timecreated'] = 'Time when record was created';
$string['privacy:metadata: approval_entries:timemodified'] = 'Time when record was last modified';
$string['privacy:metadata: approval_entries:approved'] = 'Approval status';
$string['privacy:metadata: approval_entries:status'] = 'Status de this entrada';
$string['privacy:metadata: approval_entries:assessed'] = 'Mostrar if entrada was assessed';
$string['privacy:metadata: approval_contents'] = 'Represents content de one field that was written in a  aprobación instance.';
$string['privacy:metadata: approval_contents:fieldid'] = 'Campo definition ID';
$string['privacy:metadata: approval_contents:content'] = 'Content';
$string['privacy:metadata: approval_contents:content1'] = 'Additional content 1';
$string['privacy:metadata: approval_contents:content2'] = 'Additional content 2';
$string['privacy:metadata: approval_contents:content3'] = 'Additional content 3';
$string['privacy:metadata: approval_contents:content4'] = 'Additional content 4';
$string['privacy:metadata:filepurpose'] = 'File or picture attached to a  aprobación instance.';

// Campogroups
$string['fieldgroups'] = 'Campogroups';
$string['fieldgroupfields'] = 'Campogroupfields';
$string['fieldgroupfields_help'] = 'Campos that are repeated as a group. The order de the fields is alphabetically so in order to have the fields ordered according to your preferences nombre them appropriatly';
$string['fieldgroupsadd'] = 'Add fieldgroups';
$string['newfieldgroup'] = 'Nuevo fieldgroup';
$string['editingfieldgroup'] = 'Editando fieldgroup "{$a}"';
$string['deletingfieldgroup'] = 'Deleting fieldgroup "{$a}"';
$string['duplicatingfieldgroup'] = 'Duplicating fieldgroup "{$a}"';
$string['confirmfieldgroupduplicate'] = 'Ha solicitado duplicar this fieldgroup!';
$string['confirmfieldgroupdelete'] = 'Ha solicitado borrar this fieldgroup!';
$string['line'] = 'Line';
$string['addline'] = 'Add {$a}';
$string['hideline'] = 'Hide the last line';
