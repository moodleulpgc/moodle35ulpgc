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
$string['browse'] = 'Ver';
$string['columns'] = 'columnas';
$string['commentadd'] = 'Añadir comentario';
$string['commentbynameondate'] = 'por {$a->name} - {$a->date}';
$string['comment'] = 'Comentario';
$string['commentdelete'] = '¿Desea borrar este comentario?';
$string['commentdeleted'] = 'Comentario borrado';
$string['commentedit'] = 'Editar comentario';
$string['commentempty'] = 'Comentario vacío';
$string['commentinputtype'] = 'Tipo de comentario';
$string['commentsallow'] = '¿Permitir comentarios?';
$string['commentsaved'] = 'Comentario guardado';
$string['comments'] = 'Comentarios';
$string['commentsn'] = '{$a} comentarios';
$string['commentsnone'] = 'Sin comentarios';
$string['configanonymousentries'] = 'This switch will enable the possibility de guest/anónimas entradas for todas las Registro de datos. You will still need to turn anónimas on manually in the opciones for each Registro de datos.';
$string['configenablerssfeeds'] = 'This switch will enable the possibility de RSS feeds for todas las Registro de datos. You will still need to turn feeds on manually in the opciones for each Registro de datos.';
$string['configmaxentries'] = 'This value determines the máximo number de entradas that may be añadidas to a Registro de datos activity.';
$string['configmaxfields'] = 'This value determines the máximo number de fields that may be añadidas to a Registro de datos activity.';
$string['configmaxfilters'] = 'This value determines the máximo number de filtros that may be añadidas to a Registro de datos activity.';
$string['configmaxviews'] = 'This value determines the máximo number de vistas that may be añadidas to a Registro de datos activity.';
$string['convert'] = 'Convertir';
$string['converttoeditor'] = 'Convertir to editor field';
$string['correct'] = 'Correcto';
$string['csscode'] = 'CSS code';
$string['cssinclude'] = 'CSS';
$string['cssincludes'] = 'Include external CSS';
$string['csssaved'] = 'CSS guardado';
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
$string['customfiltersnoneindatalynx'] = 'No hay filtros a medida definidos en este Registro de datos.';
// Capability strings.
$string['datalynx:addinstance'] = 'Añadir un nuevo Registro de datos';
$string['datalynx:approve'] = 'Aprobar entradas pendientes';
$string['datalynx:comment'] = 'Escribir comentarios';
$string['datalynx:exportallentries'] = 'Exportar todas las entradas';
$string['datalynx:exportentry'] = 'Exportar entrada';
$string['datalynx:exportownentry'] = 'Exportar own entradas';
$string['datalynx:managecomments'] = 'Gestionar comentarios';
$string['datalynx:manageentries'] = 'Gestionar entradas';
$string['datalynx:managepresets'] = 'Gestionar paquetes';
$string['datalynx:manageratings'] = 'Gestionar valoraciones';
$string['datalynx:managetemplates'] = 'Gestionar Plantillas';
$string['datalynx:notifyentryadded'] = 'Notificado al añadidas entrada';
$string['datalynx:notifyentryapproved'] = 'Notificado al aprobar entrada';
$string['datalynx:notifyentrydisapproved'] = 'Notificado al rechazar entrada';
$string['datalynx:notifyentryupdated'] = 'Notificado al actualizar entrada';
$string['datalynx:notifyentrydeleted'] = 'Notificado al borrar entrada';
$string['datalynx:notifycommentadded'] = 'Notificado al añadir comentario';
$string['datalynx:notifyratingadded'] = 'Notificado al añadir valoración';
$string['datalynx:notifyratingupdated'] = 'Notificado al actualizar valoración';
$string['datalynx:presetsviewall'] = 'Ver paquetes from todas las users';
$string['datalynx:rate'] = 'Valorar entradas';
$string['datalynx:ratingsviewall'] = 'Ver todas las valoraciones';
$string['datalynx:ratingsviewany'] = 'Ver cualquier valoración';
$string['datalynx:ratingsview'] = 'Ver valoraciones';
$string['datalynx:viewanonymousentry'] = 'Ver entradas anónimas';
$string['datalynx:viewentry'] = 'Ver entradas';
$string['datalynx:viewindex'] = 'Ver índice';
$string['datalynx:writeentry'] = 'Escribir entradas';
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
$string['dfupdatefailed'] = 'Failed to actualizar Registro de datos!';
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
$string['entriesimport'] = 'Importarar entradas';
$string['entrieslefttoaddtoview'] = 'You must add {$a} more entrada/entradas before you can vista other participants\' entradas.';
$string['entrieslefttoadd'] = 'You must add {$a} more entrada/entradas in order to complete this activity';
$string['entriesmax'] = 'Máximo de entradas';
$string['entriesmax_help'] = 'Número de entradas that are allowed, -1 allows unlimited entradas';
$string['entriesnotsaved'] = 'No entrada was guardado. Please check the format de the uploaded file.';
$string['entriespending'] = 'Pendiente';
$string['entriesrequired'] = 'Entradas requeridas';
$string['entriessaved'] = '{$a} entrada(s) guardadas';
$string['entriestoview'] = 'Entradas requeridas before vistaing';
$string['entriesupdated'] = '{$a} entrada(s) actualizadas';
$string['entryaddmultinew'] = 'Add nuevo entradas';
$string['entryaddnew'] = 'Añadir una nueva entrada';
$string['entry'] = 'Entrada';
$string['entryinfo'] = 'Entrada info';
$string['entrylockonapproval'] = 'Bloquear al aprobar';
$string['entrylockoncomentarios'] = 'Bloquear al comentar';
$string['entrylockonratings'] = 'Bloquear al valorar';
$string['entrylocks'] = 'Entrada locks';
$string['entrynew'] = 'Nueva entrada';
$string['entrynoneforaction'] = 'No hay entradas were found for the requested action';
$string['entrynoneindatalynx'] = 'No hay entradas in Registro de datos';
$string['entryrating'] = 'Entrada valoración';
$string['entrysaved'] = 'Your entrada has been guardado';
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
$string['exportnoneindatalynx'] = 'No hay exports definidas en este Registro de datos.';
$string['fieldadd'] = 'Añadir un field';
$string['fieldallowautolink'] = 'Permitir autolink';
$string['fieldattributes'] = 'Campo attributes';
$string['fieldcreate'] = 'Crear a nuevo field';
$string['fielddescription'] = 'Campo descripción';
$string['fieldeditable'] = 'Editable';
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
$string['fieldnoneindatalynx'] = 'No hay fields definidas en este Registro de datos.';
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
$string['filesmax'] = 'Max number de uploaded archivos';
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
$string['filteradd'] = 'Añadir un filtro';
$string['filterbypage'] = 'Por página';
$string['filtercancel'] = 'Cancelar filtro';
$string['filtercreate'] = 'Crear un nuevo filtro';
$string['filtercurrent'] = 'Fitro actual';
$string['filtercustomsearch'] = 'Opciones de búsqueda';
$string['filtercustomsort'] = 'Ordenado';
$string['filterdescription'] = 'Descripción del filtro';
$string['filteredit'] = 'Editando \'{$a}\'';
$string['filter'] = 'Filtro';
$string['filtergroupby'] = 'Agrupar por';
$string['filterincomplete'] = 'Buscar condition must be completed.';
$string['filtername'] = 'Autoenlazado de Registro de datos';
$string['filternew'] = 'Nuevo filtro';
$string['filternoneforaction'] = 'No filtros were found for the requested action';
$string['filterperpage'] = 'Nº por página';
$string['filtersadded'] = '{$a} filtro(s) añadidos';
$string['filtersave'] = 'Guardar filtro';
$string['filtersconfirmdelete'] = 'Ha solicitado borrar {$a} filtro(s). ¿Desea continuar?';
$string['filtersconfirmduplicate'] = 'Ha solicitado duplicar {$a} filtro(s). ¿Desea continuar?';
$string['filtersdeleted'] = '{$a} filtro(s) borrados';
$string['filtersduplicated'] = '{$a} filtro(s) duplicados';
$string['filterselection'] = 'Selección';
$string['filters'] = 'Filtros';
$string['filtersimplesearch'] = 'Búsqueda simple';
$string['filtersmax'] = 'Máximo filtros';
$string['filtersnonedefined'] = 'No filtros defined';
$string['filtersnoneindatalynx'] = 'No hay filtros definidas en este Registro de datos.';
$string['filtersupdated'] = '{$a} filtro(s) actualizadas';
$string['filterupdate'] = 'Actualizar un  filtro existente';
$string['filterurlquery'] = 'Url query';
$string['filtermy'] = 'Mi filtro';
$string['filteruserreset'] = '** Reiniciar el  filtro';
$string['firstdayofweek'] = 'Lunes';
$string['first'] = 'Primero';
$string['formemptyadd'] = 'You did not fill out any fields!';
$string['fromfile'] = 'Importarar from zip file';
$string['generalactions'] = 'General actions';
$string['getstarted'] = 'This Registro de datos appears to be nuevo or with incomplete setup. To get the Registro de datos started <ul><li>apply a paquete in the {$a->presets} section</li></ul> or <ul><li>add fields in the {$a->fields} section</li><li>add vistas in the {$a->views} section</li></ul>';
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
$string['importadd'] = 'Añadir un nuevo Importarar vista';
$string['import'] = 'Importar';
$string['importnoneindatalynx'] = 'No hay imports definidas en este Registro de datos.';
$string['incorrect'] = 'Incorrect';
$string['index'] = 'Index';
$string['insufficiententries'] = 'more entradas needed to vista este Registro de datos';
$string['internal'] = 'Internal';
$string['intro'] = 'Introduction';
$string['invalidname'] = 'Please choose another nombre for this {$a}';
$string['invalidrate'] = 'Invalid Registro de datos rate ({$a})';
$string['invalidurl'] = 'The URL you just entered is not valid';
$string['jscode'] = 'Javascript code';
$string['jsinclude'] = 'JS';
$string['jsincludes'] = 'Include external javascript';
$string['jssaved'] = 'Javascript guardado';
$string['jsupload'] = 'Upload javascript archivos';
$string['lock'] = 'Bloquear';
$string['manage'] = 'Gestionar';
$string['mappingwarning'] = 'All old fields not mapped to a nuevo field will be lost and todas las data in that field will be eliminado.';
$string['max'] = 'Máximo';
$string['maxsize'] = 'Tamaño Máximo';
$string['mediafile'] = 'Archivo multimedia';
$string['reference'] = 'Referencia';
$string['min'] = 'Mínimo';
$string['modulename'] = 'Registro de datos';
$string['modulename_help'] = 'The Registro de datos module may be used for creating a wide range de activities/resources by allowing the instructor/manager to design and create a custom content form from various input elements (e.g.  texts, numbers, images, archivos, urls, etc.), and participants to submit content and vista submitted content.';
$string['modulenameplural'] = 'Registros de datos';
$string['more'] = 'Más';
$string['movezipfailed'] = 'Can\'t move zip';
$string['multiapprove'] = ' Aprobar ';
$string['multidelete'] = ' Borrar  ';
$string['multidownload'] = 'Descargar';
$string['multiduplicate'] = 'Duplicado';
$string['multiedit'] = '  Editar   ';
$string['multiexport'] = 'Exportar';
$string['multipletags'] = 'Multiple etiquetas found! Ver not guardado';
$string['multiselect'] = 'Multi-selección';
$string['multishare'] = 'Compartir';
$string['newvalueallow'] = 'Permitir nuevos valores';
$string['newvalue'] = 'Nuevo valor';
$string['noaccess'] = 'No tiene acceso a esta página';
$string['noautocompletion'] = 'No autocompletion';
$string['nocustomfilter'] = 'Programming error [nocustomfilter]. Please contact your support.';
$string['nodatalynxs'] = 'No Registro de datos modules found';
$string['nomatch'] = 'No matching entradas found!';
$string['nomaximum'] = 'No máximo';
$string['notapproved'] = 'Entrada is not aprobadas yet.';
$string['notificationenable'] = 'Activar notifications for';
$string['notinjectivemap'] = 'Not an injective map';
$string['notopenyet'] = 'Sorry, this activity is not available until {$a}';
$string['numberrssarticles'] = 'RSS articles';
$string['numcharsallowed'] = 'Número de characters';
$string['optionaldescription'] = 'Short descripción (optional)';
$string['optionalfilename'] = 'Nombre de archivo (opcional)';
$string['other'] = 'Otro';
$string['overwrite'] = 'Sobrescribir';
$string['overwritesettings'] = 'Overwrite current opciones';
$string['presetadd'] = 'Añadir paquetes';
$string['presetapply'] = 'Aplicar';
$string['presetavailableincourse'] = 'Paquetes del curso';
$string['presetavailableinsite'] = 'Paquetes del Sitio';
$string['presetchoose'] = 'seleccione un paquete prededinido';
$string['presetdataanon'] = 'con datos de usuario anonimizados';
$string['presetdata'] = 'con datos de usuarios';
$string['presetfaileddelete'] = '¡Error al borrar un paquete!';
$string['presetfromdatalynx'] = 'Hacer un paquete de este Registro de datos';
$string['presetfromfile'] = 'Cargar paquete desde archivo';
$string['presetimportsuccess'] = 'El paquete de ajustes se ha aplicado con éxito.';
$string['presetinfo'] = 'Saving as a paquete will publish this vista. Other users may be able to use it in their Registro de datos.';
$string['presetmap'] = 'map fields';
$string['presetnodata'] = 'sin datos de usuarios';
$string['presetnodefinedfields'] = '¡El nuevo paquete no tiene campos!';
$string['presetnodefinedviews'] = '¡El nuevo paquete no tiene vistas!';
$string['presetnoneavailable'] = 'No hay paquetes disponibles para mostrar';
$string['presetplugin'] = 'Plug in';
$string['presetrefreshlist'] = 'Actualizar lista';
$string['presetshare'] = 'Compartir';
$string['presetsharesuccess'] = 'Saved successfully. Your paquete will now be available across the site.';
$string['presetsource'] = 'Origen del paquete';
$string['presets'] = 'Paquetes';
$string['presetusestandard'] = 'Usar un paquete';
$string['page-mod-datalynx-x'] = 'Cualquier página de Registro de datos';
$string['pagesize'] = 'Entradas por página';
$string['pagingbar'] = 'Páginas';
$string['pagingnextslide'] = 'Siguiente';
$string['pagingpreviousslide'] = 'Anterior';
$string['participants'] = 'Participantes';
$string['pleaseaddsome'] = 'Please create some below or {$a} to get started.';
$string['pluginadministration'] = 'Administración de Registro de datos';
$string['pluginname'] = 'Registro de datos';
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
$string['ratingssaved'] = 'Valoraciones guardadas';
$string['ratingssum'] = 'Suma de valoraciones';
$string['ratingsviewrate'] = 'Ver y valorar';
$string['ratingsview'] = 'Ver valoraciones';
$string['ratingvalue'] = 'Valoración';
$string['reference'] = 'Referencia';
$string['requireapproval'] = 'Require aprobación?';
$string['requiredall'] = 'todas requeridas';
$string['requirednotall'] = 'no todas requeridas';
$string['resetsettings'] = 'Reset filtros';
$string['returntoimport'] = 'Volver a Importar';
$string['rssglobaldisabled'] = 'Desactivada. Ver opciones de configuración globales.';
$string['rsshowmany'] = '(number de latest entradas to show, 0 to disable RSS)';
$string['rsstemplate'] = 'RSS Plantilla';
$string['rsstitletemplate'] = 'RSS title Plantilla';
$string['ruleaction'] = 'Acción';
$string['ruleadd'] = 'Añadir una regla';
$string['rulecancel'] = 'Cancelar regla';
$string['rulecondition'] = 'Condición';
$string['rulecreate'] = 'Crear nueva regla';
$string['ruledenydelete'] = 'Impedir borrado de entrada';
$string['ruledenyedit'] = 'Impedir edición de entrada';
$string['ruledenyviewbyother'] = 'Ocultar la entrada a todos salvo autor';
$string['ruledenyview'] = 'Ocultar la entrada a todos';
$string['ruledescription'] = 'Descripción';
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
$string['rulesnoneindatalynx'] = 'No hay reglas definidas en este Registro de datos.';
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
$string['submissionsinpopup'] = 'Entregas en ventana emergente';
$string['submission'] = 'Entrega';
$string['submissionsview'] = 'Vista de Entregas';
$string['subplugintype_datalynxfield'] = 'Registro de datos field type';
$string['subplugintype_datalynxfield_plural'] = 'Registro de datos field types';
$string['subplugintype_datalynxrule'] = 'Registro de datos regla type';
$string['subplugintype_datalynxrule_plural'] = 'Registro de datos regla types';
$string['subplugintype_datalynxtool'] = 'Registro de datos tool type';
$string['subplugintype_datalynxtool_plural'] = 'Registro de datos tool types';
$string['subplugintype_datalynxview'] = 'Registro de datos vista type';
$string['subplugintype_datalynxview_plural'] = 'Registro de datos vista types';
$string['tagarea_datalynx_contents'] = 'Registro de datos entradas';
$string['tagcollection_datalynx'] = 'Registro de datos etiquetas';
$string['teachersandstudents'] = '{$a->teachers} y {$a->students}';
$string['textbox'] = 'Text box';
$string['textfield'] = 'Textfield';
$string['textfield_help'] = 'The Textfield to retrieve the autocompletion data of.';
$string['textfieldvalues'] = 'Textfield values';
$string['timecreated'] = 'Time created';
$string['timemodified'] = 'Time modified';
$string['todatalynx'] = 'to este Registro de datos.';
$string['tools'] = 'Herramientas';
$string['trusttext'] = 'Trust text';
$string['type'] = 'Tipo';
$string['unique'] = 'Único';
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
$string['viewforedit'] = 'Ver for \'edit\'';
$string['viewformore'] = 'Ver for \'more\'';
$string['viewfromdate'] = 'Ver desde';
$string['viewgeneraltags'] = 'General etiquetas';
$string['viewgroupby'] = 'Agrupar por';
$string['viewintervalsettings'] = 'Opciones de intervalo';
$string['viewinterval'] = 'When to refresh vista content';
$string['entrytemplate'] = 'Plantilla de Entrada Plantilla';
$string['entrytemplate_help'] = 'Plantilla de Entrada';
$string['viewlistfooter'] = 'Pie de lista';
$string['viewlistheader'] = 'Encabezado de lista';
$string['viewname'] = 'Nombre de la vista';
$string['viewnew'] = 'Nueva {$a} vista';
$string['viewnodefault'] = 'Default vista is not set. Choose one de the vistas in the {$a} list as the default vista.';
$string['viewnoneforaction'] = 'No vistas were found for the requested action';
$string['viewnoneindatalynx'] = 'No hay vistas definidas en este Registro de datos.';
$string['viewrepeatedfields'] = 'You can not use the field {$a} more than once.';
$string['viewmultiplefieldgroups'] = 'You can not use more than one fieldgroup.';
$string['toolnoneindatalynx'] = 'No hay herramientas definidas en este Registro de datos.';
$string['toolrun'] = 'Ejecutar';
$string['viewoptions'] = 'Ver opciones';
$string['viewpagingfield'] = 'Paging field';
$string['viewperpage'] = 'Nº por página';
$string['viewresettodefault'] = 'Volver al predefinido';
$string['viewreturntolist'] = 'Volver al listado';
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
$string['views'] = 'Vistas';
$string['viewtodate'] = 'Viewable to';
$string['view'] = 'ver';
$string['viewvisibility'] = 'Visibilidad';
$string['wrongdataid'] = 'Wrong Registro de datos id provided';

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
$string['listformat_newline'] = 'Newline separated';
$string['listformat_space'] = 'Space separated';
$string['listformat_comma'] = 'Comma separated';
$string['listformat_commaspace'] = 'Comma separated with space';
$string['listformat_ul'] = 'Unordered list';
$string['teammembers'] = 'Miembros del grupo';
$string['status'] = 'Estado';
$string['status_notcreated'] = 'No creado';
$string['status_draft'] = 'Borrador';
$string['status_submission'] = 'Entregado';
$string['status_finalsubmission'] = 'Entrega final';
$string['completionentries'] = 'Número de entradas (aprobadas)';
$string['completionentriesgroup'] = 'Requiere entradas (aprobadas)';
$string['completionentriesgroup_help'] = 'Make sure you enable aprobación for entradas above!<br />
Número de (approved) entradas: Entradas a user has to make. If \'Require aprobación\' is set: Número de entradas equals number de aprobadas entradas only.';
$string['limitchoice'] = 'Limit choices for users';
$string['limitchoice_help'] = 'Activar this to prevent a user from choosing the same option more than the chosen number in separate entradas.';
$string['limitchoice_error'] = 'You have already selected option \'{$a}\' the máximo allowed number de times!';
$string['redirectsettings'] = 'Redirect on submit opciones';
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
$string['datalynx:editrestrictedfields'] = 'Editar restricted fields';
$string['datalynx:viewdrafts'] = 'Ver drafts';
$string['datalynx:viewprivilegeguest'] = 'Guest vista access privilege';
$string['datalynx:viewprivilegemanager'] = 'Manager vista access privilege';
$string['datalynx:viewprivilegestudent'] = 'Student vista access privilege';
$string['datalynx:viewprivilegeteacher'] = 'Teacher vista access privilege';
$string['datalynx:viewstatistics'] = 'Ver estadísticas';
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
$string['datalynx:notifymemberadded'] = 'Inform users about being añadidas as a team member';
$string['datalynx:notifymemberremoved'] = 'Inform users about being eliminado as a team member';
$string['datalynx:viewprivilegeadmin'] = 'Administrator vista access privilege';

$string['eventsettings'] = 'Event opciones';
$string['triggeringevent'] = 'Triggering event';
$string['datalynx_entryadded'] = 'Entrada añadidas';
$string['datalynx_entryupdated'] = 'Entradas actualizadas';
$string['datalynx_entrydeleted'] = 'Entradas borradas';
$string['datalynx_entryapproved'] = 'Entradas aprobadas';
$string['datalynx_entrydisapproved'] = 'Entradas rechazada';
$string['datalynx_commentadded'] = 'Comentario añadido';
$string['datalynx_ratingadded'] = 'Valoración añadida';
$string['datalynx_ratingupdated'] = 'Valoración actualizada';
$string['datalynx_ratingdeleted'] = 'Valoración borrada';
$string['datalynx_memberadded'] = 'Miembro del grupo añadidas';
$string['datalynx_memberremoved'] = 'Miembro del grupo eliminado';
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
$string['editable'] = 'Editable';
$string['required'] = 'Required';
$string['editableby'] = 'Editable por';
$string['entryauthor'] = 'Entrada author';

$string['behavior'] = 'Comportamiento';
$string['behaviors'] = 'Comportamientos';
$string['behavioradd'] = 'Añadir comportamiento';
$string['defaultbehavior'] = 'Comportamiento predefinido';

$string['renderers'] = 'Visualizadores';
$string['copyof'] = 'Copia de {$a}';
$string['mentor'] = 'Tutor';
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
$string['event_comment_created'] = 'Comentario creado';
$string['event_rating_added'] = 'Valoración añadida';
$string['event_rating_updated'] = 'Valoración actualizada';
$string['event_rating_deleted'] = 'Valoración borrada';
$string['event_team_updated'] = 'Grupo actualizads';

// Message strings.
$string['message_entry_created'] = 'Hello {$a->fullname},

the content in {$a->datalynxlink} has been modified by {$a->senderprofilelink}.

The following entrada has been created: {$a->viewlink}.';

$string['message_entry_updated'] = 'Hello {$a->fullname},

the content in {$a->datalynxlink} has been modified by {$a->senderprofilelink}.

The following entrada has been actualizadas: {$a->viewlink}.';

$string['message_entry_deleted'] = 'Hello {$a->fullname},

the content in {$a->datalynxlink} has been modified by {$a->senderprofilelink}.

An entrada has been borradas.';

$string['message_entry_approved'] = 'Hello {$a->fullname},

the content in {$a->datalynxlink} has been aprobadas by {$a->senderprofilelink}.

The following entrada has been aprobadas: {$a->viewlink}.';

$string['message_entry_disapproved'] = 'Hello {$a->fullname},

the content in {$a->datalynxlink} has been modified by {$a->senderprofilelink}.

The following entrada has been deactivated: {$a->viewlink}.';

$string['message_comment_created'] = 'Hello {$a->fullname},

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
$string['avoidaddanddeletesimultaneously'] = 'You must not add and borrar opciones in one step. First borrar the opciones and save, then rename the opciones and save again.';
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
$string['deletefieldfilterwarning'] = 'Warning! You are attempting to borrar following fields:{$a->fieldlist}However, filtros listed below are still using some de these fields:{$a->filterlist}You will have to borrar these filtros manually first before you may proceed.';
$string['noviewsavailable'] = 'No vistas available';

$string['datalynx_team_updated'] = 'Grupo actualizado';
$string['datalynx:editprivilegeadmin'] = 'Admin edit access privilege';
$string['datalynx:editprivilegeguest'] = 'Guest edit access privilege';
$string['datalynx:editprivilegemanager'] = 'Manager edit access privilege';
$string['datalynx:editprivilegestudent'] = 'Student edit access privilege';
$string['datalynx:editprivilegeteacher'] = 'Teacher edit access privilege';
$string['datalynx:notifyteamupdated'] = 'Notificado al team update';
$string['datalynx:teamsubscribe'] = 'Subscribe to/join teams';

$string['datalynx_csssaved'] = 'CSS personalizado guardado';
$string['datalynx_jssaved'] = 'JavaScript personalizado guardado';

$string['displaytemplate_help'] = 'Specify HTML Plantilla to replace the field etiqueta in browse mode. To specify the position de the actual value, use #value etiqueta within the Plantilla.';
$string['edittemplate_help'] = 'Specify HTML Plantilla to replace the field etiqueta in edit mode. To specify the position de the actual input element, use #input etiqueta within the Plantilla.';

$string['notallowedtoeditentry'] = 'No está permitido editar esta entrada.';
$string['thisdatalynx'] = 'Esta instancia  Registro de datos instance';
$string['thisfield'] = 'Este campo';

$string['fulltextsearch'] = 'Búsqueda de texto completo';
$string['fieldlist'] = 'Campos de búsqueda';
$string['userfields'] = 'Campos definidos por usuario';
$string['sortable'] = 'ordenable';
$string['activate'] = 'activar';
$string['fileexist'] = 'existe';
$string['filemissing'] = 'no existe';

$string['fieldsimportsettings'] = 'Importarsettings';
$string['uploadfile'] = 'Archivo a importar';
$string['uploadtext'] = 'Texto a importar';
$string['updateexisting'] = 'Actualizar existentes';

// Privacy API
$string['privacy:metadata:datalynx_entries'] = 'Represent entradas in a Registro de datos instance.';
$string['privacy:metadata:datalynx_entries:userid'] = 'de usuariowho created the record';
$string['privacy:metadata:datalynx_entries:groupid'] = 'Group';
$string['privacy:metadata:datalynx_entries:timecreated'] = 'Time when record was created';
$string['privacy:metadata:datalynx_entries:timemodified'] = 'Time when record was last modified';
$string['privacy:metadata:datalynx_entries:approved'] = 'Approval status';
$string['privacy:metadata:datalynx_entries:status'] = 'Status de this entrada';
$string['privacy:metadata:datalynx_entries:assessed'] = 'Mostrar if entrada was assessed';
$string['privacy:metadata:datalynx_contents'] = 'Represents content de one field that was written in a Registro de datos instance.';
$string['privacy:metadata:datalynx_contents:fieldid'] = 'Campo definition ID';
$string['privacy:metadata:datalynx_contents:content'] = 'Content';
$string['privacy:metadata:datalynx_contents:content1'] = 'Additional content 1';
$string['privacy:metadata:datalynx_contents:content2'] = 'Additional content 2';
$string['privacy:metadata:datalynx_contents:content3'] = 'Additional content 3';
$string['privacy:metadata:datalynx_contents:content4'] = 'Additional content 4';
$string['privacy:metadata:filepurpose'] = 'File or picture attached to a Registro de datos instance.';

// Campogroups
$string['fieldgroups'] = 'Conjuntos de campos';
$string['fieldgroupfields'] = 'Campos del conjunto';
$string['fieldgroupfields_help'] = 'Campos que se repiten juntos como un conjunto. 
El orden de los campos es siempre alfabético, así que dele los nombre apropiados para mantener el orden deseado';
$string['fieldgroupsadd'] = 'Añadir conjunto';
$string['newfieldgroup'] = 'Nuevo conjunto';
$string['editingfieldgroup'] = 'Editando conjunto de campos "{$a}"';
$string['deletingfieldgroup'] = 'Borrando conjunto de campos "{$a}"';
$string['duplicatingfieldgroup'] = 'Duplicando conjunto de campos "{$a}"';
$string['confirmfieldgroupduplicate'] = '¡Ha solicitado duplicar este conjunto!';
$string['confirmfieldgroupdelete'] = '¡Ha solicitado borrar este conjunto!';
$string['line'] = 'Línea';
$string['addline'] = 'Añadir {$a}';
$string['hideline'] = 'Ocultar la última línea';
