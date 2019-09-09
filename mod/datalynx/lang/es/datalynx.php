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
$string['allowaddoption'] = 'Permitir añadir opciones';
$string['alphabeticalorder'] = '¿Ordenar las opciones alfabéticamente al editar una entrada?';
$string['approval_none'] = 'No requerido';
$string['approval_required_new'] = 'Requerido solo para nuevas';
$string['approval_required_update'] = 'Requerido para nuevas y editadas';
$string['approved'] = 'aprobado';
$string['approvednot'] = 'no aprobado';
$string['ascending'] = 'Ascendente';
$string['authorinfo'] = 'Info. Autor';
$string['autocompletion'] = 'Autocompletado';
$string['autocompletion_help'] = 'Indique si el Autocompletado debe estar activo en el modo de edición.';
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
$string['configanonymousentries'] = 'Esta opción habilitará la disponibilidad de entradas invitadas/anónimas en todos los Registros de datos. 
Es preciso activar manualmente las entradas anónimas en cada Registro de datos.';
$string['configenablerssfeeds'] = 'Esta opción habilitará la disponibilidad de canales RSS en todos los Registros de datos. 
Es preciso activar manualmente los canales RSS en cada Registro de datos.';
$string['configmaxentries'] = 'Este parámetro determina el nº máximo de entradas que pueden añadirse al Registro de datos por un usuario.';
$string['configmaxfields'] = 'Este parámetro determina el nº máximo de campos que pueden añadirse al Registro de datos por un usuario.';
$string['configmaxfilters'] = 'Este parámetro determina el nº máximo de filtros que pueden añadirse al Registro de datos por un usuario.';
$string['configmaxviews'] = 'Este parámetro determina el nº máximo de vistas que pueden añadirse al Registro de datos por un usuario.';
$string['convert'] = 'Convertir';
$string['converttoeditor'] = 'Convertir a campo Editor';
$string['correct'] = 'Correcto';
$string['csscode'] = 'Código CSS';
$string['cssinclude'] = 'CSS';
$string['cssincludes'] = 'Incluir CSS externo';
$string['csssaved'] = 'CSS guardado';
$string['cssupload'] = 'Cargar archivos CSS';
$string['csvdelimiter'] = 'delimitador';
$string['csvenclosure'] = 'marcador de texto';
$string['csvfailed'] = 'No se puede leer el texto bruto del archivo CSV.';
$string['csvoutput'] = 'Salida CSV';
$string['csvsettings'] = 'Opciones CSV';
$string['csvwithselecteddelimiter'] = 'Texto <acronym title=\"Comma Separated Values\">CSV</acronym> con el delimitador seleccionado:';
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
$string['datalynx:notifyentryadded'] = 'Notificar al añadidas entrada';
$string['datalynx:notifyentryapproved'] = 'Notificar al aprobar entrada';
$string['datalynx:notifyentrydisapproved'] = 'Notificar al rechazar entrada';
$string['datalynx:notifyentryupdated'] = 'Notificar al actualizar entrada';
$string['datalynx:notifyentrydeleted'] = 'Notificar al borrar entrada';
$string['datalynx:notifycommentadded'] = 'Notificar al añadir comentario';
$string['datalynx:notifyratingadded'] = 'Notificar al añadir valoración';
$string['datalynx:notifyratingupdated'] = 'Notificar al actualizar valoración';
$string['datalynx:presetsviewall'] = 'Ver todos los paquetes';
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
$string['deletenotenrolled'] = 'Borrar entradas por usuarios no matriculados';
$string['descending'] = 'Descendente';
$string['dfintervalcount'] = 'Número de intervalos';
$string['dfintervalcount_help'] = 'Seleccione cuántos intervalos deben ser desbloqueados';
$string['dflateallow'] = 'Mensajes tardíos';
$string['dflateuse'] = 'Permitir mensajes tardíos';
$string['dfratingactivity'] = 'Evaluación de la actividad';
$string['dftimeavailable'] = 'Disponible desde';
$string['dftimedue'] = 'Plazo';
$string['dftimeinterval'] = 'Esperar hasta que las siguente entrada sea desbloqueada';
$string['dftimeinterval_help'] = 'Seleccionar un periodo hasta que los próxima entrada sea desbloqueada para el usuario';
$string['dfupdatefailed'] = '¡Fallo al actualizar Registro de datos!';
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
$string['entrieslefttoaddtoview'] = 'Debe añadir {$a} entrada/entradas más antes de que pueda acceder a las entradas de otros participantes.';
$string['entrieslefttoadd'] = 'Debe añadir {$a} entrada/entradas más para completar la actividad';
$string['entriesmax'] = 'Máximo de entradas';
$string['entriesmax_help'] = 'Número de máximo de entradas permitidas, -1 permite entradas sin límite';
$string['entriesnotsaved'] = 'Ninguna entrada guardada. Por favor, verifique el formato del archivo cargado.';
$string['entriespending'] = 'Pendiente';
$string['entriesrequired'] = 'Entradas requeridas';
$string['entriessaved'] = '{$a} entrada(s) guardadas';
$string['entriestoview'] = 'Entradas requeridas antes de dar acceso';
$string['entriesupdated'] = '{$a} entrada(s) actualizadas';
$string['entryaddmultinew'] = 'Añadir nuevas entradas';
$string['entryaddnew'] = 'Añadir una nueva entrada';
$string['entry'] = 'Entrada';
$string['entryinfo'] = 'Información';
$string['entrylockonapproval'] = 'Bloquear al aprobar';
$string['entrylockoncomentarios'] = 'Bloquear al comentar';
$string['entrylockonratings'] = 'Bloquear al valorar';
$string['entrylocks'] = 'Bloqueos en Entrada';
$string['entrynew'] = 'Nueva entrada';
$string['entrynoneforaction'] = 'No hay entradas para la acción rquerida';
$string['entrynoneindatalynx'] = 'No hay entradas en Registro de datos';
$string['entryrating'] = 'Valoración de Entrada';
$string['entrysaved'] = 'Se ha guardado';
$string['entrysettings'] = 'Opciones de Entrada';
$string['entrysettingsupdated'] = 'Opciones de Entrada actualizadas';
$string['entrytimelimit'] = 'Editando plazo límite (minutos)';
$string['entrytimelimit_help'] = 'Minutos disponibles hasta que la edición sea deshabilitada, -1 para edición sin límite';
$string['err_numeric'] = 'Debe introducir un número aquí. Ejemplo: 0.00 o 0.3 o 387';
$string['exportcontent'] = 'Exportar contenido';
$string['exportadd'] = 'Añadir una nueva vista de exportación';
$string['export'] = 'Exportar';
$string['exportall'] = 'Exportar todo';
$string['exportpage'] = 'Exportar página';
$string['exportnoneindatalynx'] = 'No hay exportaciones definidas en este Registro de datos.';
$string['fieldadd'] = 'Añadir un campo';
$string['fieldallowautolink'] = 'Permitir autoenlace';
$string['fieldattributes'] = 'Atributos del Campo';
$string['fieldcreate'] = 'Crear nuevo campo';
$string['fielddescription'] = 'Descripción del Campo';
$string['fieldeditable'] = 'Editable';
$string['fieldedit'] = 'Editando \'{$a}\'';
$string['fieldedits'] = 'Número de ediciones';
$string['field'] = 'campo';
$string['fieldids'] = 'Campo ids';
$string['fieldlabel'] = 'Etiqueta del Campo';
$string['fieldlabel_help'] = 'La etiqueta de campo permite definir un texto que se añade a la Vista con un códido como [[fieldname@]].
TEl código sigue la visibilidad del campo, no se muestra si el campo está oculto.
This field pattern observes the field visibility and is hidden if the field is set to be hidden. 
La etiqueta de campo también funciona como una plantilla de visualización. 
Por ejemplo, con un campo numérico denominado "Número" y una etiqueta de campo definida como "Ha obtenido [[Número]] créditos", 
en una entrada en la que el campo tiene el valor 47, el código [[Número@]] se mostrará como "Ha obtenido 47 créditos".';
$string['fieldmappings'] = 'Mapeo del Campo';
$string['fieldname'] = 'Nombre del Campo';
$string['fieldnew'] = 'Nuevo campo {$a}';
$string['fieldnoneforaction'] = 'No se encontraron campos para la acción requerida.';
$string['fieldnoneindatalynx'] = 'No hay campos definidos en este Registro de datos.';
$string['fieldnonematching'] = 'No hay campos coincidentes';
$string['fieldnotmatched'] = 'The following fields in your file are not known in this approval: {$a}';
$string['fieldoptionsdefault'] = 'Valores por defecto (una por línea)';
$string['fieldoptions'] = 'Opciones (una por línea)';
$string['fieldoptionsseparator'] = 'Separador de opciones';
$string['fieldrequired'] = 'Campo obligatorio; debe introducir un valor.';
$string['fieldrules'] = 'Reglas de edición del Campo';
$string['fieldsadded'] = 'Campos añadidos';
$string['fieldsconfirmdelete'] = 'Ha solicitado borrar {$a} campo(s). ¿Desea continuar?';
$string['fieldsconfirmduplicate'] = 'Ha solicitado duplicar {$a} campo(s). ¿Desea continuar?';
$string['fieldsdeleted'] = 'Campos borradas. Puede ser necesario actualizar las opciones de ordenamiento predefinidas.';
$string['fields'] = 'Campos';
$string['fieldsmax'] = 'Máximo de campos';
$string['fieldsnonedefined'] = 'No fields defined';
$string['fieldsupdated'] = 'Campos actualizados';
$string['fieldvisibility'] = 'Visible para';
$string['fieldvisibleall'] = 'Todos';
$string['fieldvisiblenone'] = 'Solo administradores';
$string['fieldvisibleowner'] = 'Propietarios y administradores';
$string['fieldwidth'] = 'Ancho';
$string['field_has_duplicate_entries'] = 'Hay entradas duplicadas, 
por lo tanto no es posible definir este campo como "Único" en este momento.';
$string['filemaxsize'] = 'Tamaño total de los archivos subidos';
$string['filesmax'] = 'Nº máximo de archivos subidos';
$string['filetypeany'] = 'Cualquier tipo de archivo';
$string['filetypeaudio'] = 'Archivos de Audio';
$string['filetypegif'] = 'Archivos gif';
$string['filetypehtml'] = 'Archivos Html';
$string['filetypeimage'] = 'Archivos de Imagen';
$string['filetypejpg'] = 'Archivos jpg';
$string['filetypepng'] = 'Archivos png';
$string['filetypes'] = 'Tipos de archivo aceptados';
// FILTER FORM.
$string['andor'] = 'y/o ...';
$string['and'] = 'Y';
$string['or'] = 'O';
$string['is'] = 'ES';
$string['not'] = 'NO';
// FILTER.
$string['filtersortfieldlabel'] = 'Ordenar por campo ';
$string['filtersearchfieldlabel'] = 'Buscar por campo ';
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
$string['filterincomplete'] = 'El patrón de búsqueda debe ser completado.';
$string['filtername'] = 'Autoenlazado de Registro de datos';
$string['filternew'] = 'Nuevo filtro';
$string['filternoneforaction'] = 'No hay filtros para la acción requerida';
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
$string['filtersmax'] = 'Máximo de filtros';
$string['filtersnonedefined'] = 'No hay filtros definidos';
$string['filtersnoneindatalynx'] = 'No hay filtros definidos en este Registro de datos.';
$string['filtersupdated'] = '{$a} filtro(s) actualizados';
$string['filterupdate'] = 'Actualizar un  filtro existente';
$string['filterurlquery'] = 'Búsqueda Url';
$string['filtermy'] = 'Mi filtro';
$string['filteruserreset'] = '** Reiniciar el  filtro';
$string['firstdayofweek'] = 'Lunes';
$string['first'] = 'Primero';
$string['formemptyadd'] = '¡No se ha rellenado ningún campo!';
$string['fromfile'] = 'Importar de archivo ZIP';
$string['generalactions'] = 'Acciones generales';
$string['getstarted'] = 'Esta Registro de datos parace ser nuevo o con configuración incompleta. 
Para inicializar el Registro de datos 
<ul><li>aplique un paquete en la pestaña {$a->presets}</li></ul> o 
<ul><li>añada campos en la pestaña {$a->fields}</li><li>añada Vistas pestaña {$a->views}</li></ul>';
$string['grade'] = 'Calificación';
$string['gradeinputtype'] = 'Grade input type';
$string['grading'] = 'Evaluación';
$string['gradingmethod'] = 'Método de calificación';
$string['gradingsettings'] = 'Opciones de evaluación de la actividad';
$string['groupentries'] = 'Entradas de Grupo';
$string['groupinfo'] = 'Información de Grupo';
$string['headercss'] = 'Custom CSS styles for todas las vistas';
$string['headerjs'] = 'Custom javascript for todas las vistas';
$string['horizontal'] = 'Horizontal';
$string['id'] = 'ID';
$string['importadd'] = 'Añadir una nueva vista de Importación';
$string['import'] = 'Importar';
$string['importnoneindatalynx'] = 'No hay importaciones definidas en este Registro de datos.';
$string['incorrect'] = 'Incorrecto';
$string['index'] = 'Índice';
$string['insufficiententries'] = 'Se necesitan más entradas para acceder a este Registro de datos';
$string['internal'] = 'Interno';
$string['intro'] = 'Introducción';
$string['invalidname'] = 'Por favor, escoja otro nombre para este {$a}';
$string['invalidrate'] = 'Valoración de Registro de datos  ({$a}) inválida';
$string['invalidurl'] = 'La URL introducida no es válida';
$string['jscode'] = 'Código Javascript';
$string['jsinclude'] = 'JS';
$string['jsincludes'] = 'Incluir javascript externo';
$string['jssaved'] = 'Javascript guardado';
$string['jsupload'] = 'Carcar archivos javascript';
$string['lock'] = 'Bloquear';
$string['manage'] = 'Gestionar';
$string['mappingwarning'] = 'Todos los campos antiguos no mapeados a un campo nuevo se perderán, 
junto con los datos almacenados.';
$string['max'] = 'Máximo';
$string['maxsize'] = 'Tamaño Máximo';
$string['mediafile'] = 'Archivo multimedia';
$string['reference'] = 'Referencia';
$string['min'] = 'Mínimo';
$string['modulename'] = 'Registro de datos';
$string['modulename_help'] = 'El módulo Registro de datos es una variación de una Base de datos. 
Permite al Profesor definir una plantilla personalizada para la entrada de datos por los usuarios. 
Esas entradas pueden ser editadas, clasificadas, valoradas y calificadas, por el profesor o de forma colaborativa. 

Las entradas pueden combinar una variedad de tipos de datos (e.g. textos, números, imágenes, archivos, URLs etc.) 
en formatos también definidos de forma personalizada. 
De esta forma, el módulo puede ser usado para crear un amplio espectro de actividades o recursos que impliquen la recolección de datos rellenados por los particpantes.';
$string['modulenameplural'] = 'Registros de datos';
$string['more'] = 'Más';
$string['movezipfailed'] = 'No se puede mover el ZIP';
$string['multiapprove'] = ' Aprobar ';
$string['multidelete'] = ' Borrar  ';
$string['multidownload'] = 'Descargar';
$string['multiduplicate'] = 'Duplicado';
$string['multiedit'] = '  Editar   ';
$string['multiexport'] = 'Exportar';
$string['multipletags'] = '¡Multiples etiquetas! Vista no guardada';
$string['multiselect'] = 'Multi-selección';
$string['multishare'] = 'Compartir';
$string['newvalueallow'] = 'Permitir nuevos valores';
$string['newvalue'] = 'Nuevo valor';
$string['noaccess'] = 'No tiene acceso a esta página';
$string['noautocompletion'] = 'No autocompletado';
$string['nocustomfilter'] = 'Error [nocustomfilter]. Contacte con el Administrador.';
$string['nodatalynxs'] = 'No se han encontrado Registros de datos';
$string['nomatch'] = '¡No hay extradas coincidentes!';
$string['nomaximum'] = 'No máximo';
$string['notapproved'] = 'Entrada no aprobada todavía.';
$string['notificationenable'] = 'Activar notificaciones para';
$string['notinjectivemap'] = 'Not an injective map';
$string['notopenyet'] = 'Esta actividad NO está disponible hasta el {$a}';
$string['numberrssarticles'] = 'Entradas RSS';
$string['numcharsallowed'] = 'Número de caracteres';
$string['optionaldescription'] = 'Descripción corta (opcional)';
$string['optionalfilename'] = 'Nombre de archivo (opcional)';
$string['other'] = 'Otro';
$string['overwrite'] = 'Sobrescribir';
$string['overwritesettings'] = 'Sobreescribir opciones actuales';
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
$string['presetinfo'] = 'Guardar como un paquete publicará esta Vista. 
Otros usuarios podrán usar estos ajustes en sus Registros de datos.';
$string['presetmap'] = 'map fields';
$string['presetnodata'] = 'sin datos de usuarios';
$string['presetnodefinedfields'] = '¡El nuevo paquete no tiene campos!';
$string['presetnodefinedviews'] = '¡El nuevo paquete no tiene vistas!';
$string['presetnoneavailable'] = 'No hay paquetes disponibles para mostrar';
$string['presetplugin'] = 'Plugin';
$string['presetrefreshlist'] = 'Actualizar lista';
$string['presetshare'] = 'Compartir';
$string['presetsharesuccess'] = 'Guardado adecuadament. Su paquete estará disponible en toda la plataforma.';
$string['presetsource'] = 'Origen del paquete';
$string['presets'] = 'Paquetes';
$string['presetusestandard'] = 'Usar un paquete';
$string['page-mod-datalynx-x'] = 'Cualquier página de Registro de datos';
$string['pagesize'] = 'Entradas por página';
$string['pagingbar'] = 'Páginas';
$string['pagingnextslide'] = 'Siguiente';
$string['pagingpreviousslide'] = 'Anterior';
$string['participants'] = 'Participantes';
$string['pleaseaddsome'] = 'Por favor, añada algunos datos debajo o {$a} para comenzar.';
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
$string['ratingpublic'] = '{$a} puede ver las valoraciones de todos';
$string['ratingpublicnot'] = '{$a} puede ver sólo sus propias valoraciones';
$string['rating'] = 'Valoración';
$string['ratingsaggregate'] = '{$a->value} ({$a->method} de {$a->count} valoraciones)';
$string['ratingsavg'] = 'Promedio de valoraciones';
$string['ratingscount'] = 'Número de valoraciones';
$string['ratingsmax'] = 'Mayor valoración';
$string['ratingsmin'] = 'Menor valoración';
$string['ratingsnone'] = '---';
$string['ratings'] = 'Valoraciones';
$string['ratingssaved'] = 'Valoraciones guardadas';
$string['ratingssum'] = 'Suma de valoraciones';
$string['ratingsviewrate'] = 'Ver y valorar';
$string['ratingsview'] = 'Ver valoraciones';
$string['ratingvalue'] = 'Valoración';
$string['reference'] = 'Referencia';
$string['requireapproval'] = '?Require aprobación?';
$string['requiredall'] = 'todas requeridas';
$string['requirednotall'] = 'no todas requeridas';
$string['resetsettings'] = 'Reiniciar filtros';
$string['returntoimport'] = 'Volver a Importar';
$string['rssglobaldisabled'] = 'Desactivada. Ver opciones de configuración globales.';
$string['rsshowmany'] = '(nº de entradas a mostrar, 0 para desactivar RSS)';
$string['rsstemplate'] = 'Plantilla RSS';
$string['rsstitletemplate'] = 'Título de Plantilla RSS';
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
$string['rule'] = 'regla';
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
$string['saveasstandardtags'] = 'Guardar etiquetas como estándard para ser sugeridas al añadir o editar una entrada?';
$string['search'] = 'Buscar';
$string['sendinratings'] = 'Enviar mis últimas valoraciones';
$string['separateentries'] = 'cada entrada en archivos separados';
$string['separateparticipants'] = 'Separate participants';
$string['settings'] = 'Opciones';
$string['showall'] = 'Mostrar todas las entradas';
$string['singleedit'] = 'E';
$string['singlemore'] = 'M';
$string['spreadsheettype'] = 'Tipo Hoja-de-cálculo';
$string['submissionsinpopup'] = 'Entregas en ventana emergente';
$string['submission'] = 'Entrega';
$string['submissionsview'] = 'Vista de Entregas';
$string['subplugintype_datalynxfield'] = 'Tipo de campo de Registro de datos';
$string['subplugintype_datalynxfield_plural'] = 'Tipos de campo de Registro de datos';
$string['subplugintype_datalynxrule'] = 'Tipo de regla de Registro de datos';
$string['subplugintype_datalynxrule_plural'] = 'Tipos de regla Registro de datos';
$string['subplugintype_datalynxtool'] = 'Tipo de herramienta de Registro de datos';
$string['subplugintype_datalynxtool_plural'] = 'Tipos de herramienta Registro de datos';
$string['subplugintype_datalynxview'] = 'Tipo de vista Registro de datos';
$string['subplugintype_datalynxview_plural'] = 'Tipos de vista Registro de datos';
$string['tagarea_datalynx_contents'] = 'Entradas de Registro de datos';
$string['tagcollection_datalynx'] = 'Etiquetas de Registro de datos';
$string['teachersandstudents'] = '{$a->teachers} y {$a->students}';
$string['textbox'] = 'Caja de texto';
$string['textfield'] = 'Campo de texto';
$string['textfield_help'] = 'Campo de texto para autocompletado.';
$string['textfieldvalues'] = 'Valores del campo de texto';
$string['timecreated'] = 'Fecha de creación';
$string['timemodified'] = 'Fecha de modificación';
$string['todatalynx'] = 'to este Registro de datos.';
$string['tools'] = 'Herramientas';
$string['trusttext'] = 'Texto de confianza';
$string['type'] = 'Tipo';
$string['unique'] = 'Único';
$string['unique_required'] = '¡Se requiere texto único! Este texto ya se ha utilizado.';
$string['unlock'] = 'Desbloquear';
$string['updatefield'] = 'Actualizar un campo existente';
$string['updateview'] = 'Actualizar una vista existente';
$string['userinfo'] = 'Info. de usuario';
$string['userpref'] = 'Preferencias de usuario';
$string['usersubmissionsinpopup'] = 'entregas en ventana emergente';
$string['usersubmissions'] = 'entregas de usuario';
$string['usersubmissionsview'] = 'vista de entrega de usuario';
$string['vertical'] = 'Vertical';
$string['viewadd'] = 'Añadir una vista';
$string['viewcharactertags'] = 'Etiquetas de Carácter';
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
$string['viewgeneraltags'] = 'Etiquetas genéricas';
$string['viewgroupby'] = 'Agrupar por';
$string['viewintervalsettings'] = 'Opciones de intervalo';
$string['viewinterval'] = 'Cuándo actualizar el contenido';
$string['entrytemplate'] = 'Plantilla de Entrada';
$string['entrytemplate_help'] = 'Plantilla de Entrada';
$string['viewlistfooter'] = 'Pie de lista';
$string['viewlistheader'] = 'Encabezado de lista';
$string['viewname'] = 'Nombre de la vista';
$string['viewnew'] = 'Nueva {$a} vista';
$string['viewnodefault'] = 'No se ha definido una vista por defecto. 
Seleccione una de las vistas en la lista {$a} como la vista pedefinida.';
$string['viewnoneforaction'] = 'No hay vistas seleccionadas para la acción requerida';
$string['viewnoneindatalynx'] = 'No hay vistas definidas en este Registro de datos.';
$string['viewrepeatedfields'] = 'No se puede usar el campo {$a} más de una vez.';
$string['viewmultiplefieldgroups'] = 'No puede usar más de un campos de grupo.';
$string['toolnoneindatalynx'] = 'No hay herramientas definidas en este Registro de datos.';
$string['toolrun'] = 'Ejecutar';
$string['viewoptions'] = 'Ver opciones';
$string['viewpagingfield'] = 'Campo de paginado';
$string['viewperpage'] = 'Nº por página';
$string['viewresettodefault'] = 'Volver al predefinido';
$string['viewreturntolist'] = 'Volver al listado';
$string['viewsadded'] = 'Ver añadidas';
$string['viewsconfirmdelete'] = 'Ha solicitado borrar {$a} vista(s). ¿Desea continuar?';
$string['viewsconfirmduplicate'] = 'Ha solicitado duplicar {$a} vista(s). ¿Desea continuar?';
$string['viewsdeleted'] = 'Ver borradas';
$string['viewtemplate'] = 'Ver Plantilla';
$string['viewtemplate_help'] = 'Ver Plantilla';
$string['viewgeneral'] = 'Ver opciones genéricas';
$string['viewgeneral_help'] = 'Ver opciones genéricas';
$string['viewsectionpos'] = 'Section position';
$string['viewslidepaging'] = 'Slide paging';
$string['viewsmax'] = 'Máximo vistas';
$string['viewsupdated'] = 'Ver actualizadas';
$string['views'] = 'Vistas';
$string['viewtodate'] = 'Accesible por';
$string['view'] = 'ver';
$string['viewvisibility'] = 'Visibilidad';
$string['wrongdataid'] = 'Se ha indicado una ID de Registro de datos incorrecta';

// Teammemberselect strings.

$string['teamsize'] = 'Máximo tamaño del grupo';
$string['teamsize_help'] = 'Especifique el máximo tamaño del grupoo. Debe ser un número entero positivo.';
$string['teamsize_error_required'] = 'Campos obligatorio';
$string['teamsize_error_value'] = 'El valor debe ser un número entero positivo';
$string['admissibleroles'] = 'Roles admitidos';
$string['admissibleroles_help'] = 'Los usuarios que tengan alguno de estos roles podrán incorporarse al grupo. 
Se debe seleccionar al menos un rol.';
$string['admissibleroles_error'] = 'Por favor, seleccione al menos un rol.';
$string['notifyteam'] = 'Regla de Notification';
$string['notifyteam_help'] = 'Seleccione la regla de notificación que será aplicada a todos las miembros del grupo especificado en este campo.';
$string['teammemberselectmultiple'] = 'Una persona solo puede ser seleccionada una vez como miembro del grupo.';
$string['listformat'] = 'Formato de lista';
$string['listformat_newline'] = 'Separado por líneas';
$string['listformat_space'] = 'Separado por espacios';
$string['listformat_comma'] = 'Separado por comas';
$string['listformat_commaspace'] = 'Separado por coma y espacios';
$string['listformat_ul'] = 'Lista no ordenada';
$string['teammembers'] = 'Miembros del grupo';
$string['status'] = 'Estado';
$string['status_notcreated'] = 'No creado';
$string['status_draft'] = 'Borrador';
$string['status_submission'] = 'Entregado';
$string['status_finalsubmission'] = 'Entrega final';
$string['completionentries'] = 'Número de entradas (aprobadas)';
$string['completionentriesgroup'] = 'Requiere entradas (aprobadas)';
$string['completionentriesgroup_help'] = '¡Verifique el estado de aprobación de las entradas de arriba!!<br />
Número de entradas (aprobadas): Entradas que debe completar el usuario. 
Si está activo \'Requiere aprobación\': Número de entradas es igual al número de entradas aprobadas entradas.';
$string['limitchoice'] = 'Limitar opciones a los usuarios';
$string['limitchoice_help'] = 'Activar esto para evitar que el usuario seleccione 
la misma opción más veces de las indicadas en entradas separadas.';
$string['limitchoice_error'] = 'Ya ha seleccionado la opción \'{$a}\' el número máximo de veces permitidas';
$string['redirectsettings'] = 'Opciones de redirección al entregar';
$string['redirectsettings_help'] = 'Use estos campos para indicar a qué vista debe rediriguirse el navegador al dejar la vista de edición.';
$string['redirectto'] = 'Vista de destino';
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
$string['fromto_error'] = 'La fecha \'Desde\' no puede ser posterior a la fecha \'Hasta\'';
$string['me'] = 'Yo';
$string['otheruser'] = 'Otro usuario';
$string['period'] = 'Periodo';
$string['ondate'] = 'En fecha';
$string['fromdate'] = 'Desde fecha';
$string['todate'] = 'Hasta fecha';
$string['alltime'] = 'Siempre';
$string['datalynx:editrestrictedfields'] = 'Editar campos restringidos';
$string['datalynx:viewdrafts'] = 'Ver borradores';
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
$string['numapprovedentries'] = 'Número de entradas aprobadas';
$string['numdeletedentries'] = 'Número de entradas borradas';
$string['numvisits'] = 'Número de visitas';
$string['modearray'] = 'Modo de visualización';
$string['modearray_help'] = '\'To\' fecha is always considered when available until 23:59:59.';
$string['time_field_required'] = '{$a} field is requeridas!';
$string['statusrequired'] = 'Status must be set!';
$string['fromaftertoday_error'] = '\'From\' fecha cannot be set after today\'s fecha!';
$string['editmode'] = 'Modo de Edición';
$string['managemode'] = 'Modo de Gestión';
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
$string['datalynx_entrydisapproved'] = 'Entradas rechazadas';
$string['datalynx_commentadded'] = 'Comentario añadido';
$string['datalynx_ratingadded'] = 'Valoración añadida';
$string['datalynx_ratingupdated'] = 'Valoración actualizada';
$string['datalynx_ratingdeleted'] = 'Valoración borrada';
$string['datalynx_memberadded'] = 'Miembro del grupo añadidas';
$string['datalynx_memberremoved'] = 'Miembro del grupo eliminado';
$string['blankfilter'] = 'Filter vacío';
$string['defaultfilterlabel'] = 'Filtro predefinido ({$a})';
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
$string['newbehavior'] = 'Nuevo comportamiento del campo';
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
$string['datalynx:notifyteamupdated'] = 'Notificar al team update';
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
