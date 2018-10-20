<?php
/**
 * Cadenas de texto de la extensión de sincronización de la ULPGC
 *
 * @package report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Herramientas de Comprobación';
$string['contenttools'] = 'Contenido (Importar/Exportar)';
$string['checktools'] = 'Herramientas de Comprobación';
$string['trackertools:report'] = 'Comprobar incidencias';
$string['trackertools:exportport'] = 'Exportar incidencias';
$string['trackertools:import'] = 'Importar incidencias';
$string['trackertools:download'] = 'Exportar ficheros';
$string['trackertools:manage'] = 'Gestionar campos ';
$string['trackertools:warning'] = 'Realizar advertencias';

// settings
$string['settings'] = 'Opciones de Herramientas de incidencias';
$string['enabled'] = 'Habilitar Herramientas ';
$string['explainenabled'] = 'Si activo, el panel de Administración de los Gestores de Incidencias mostrará herramientas y conductas adicionales.';
$string['checkfield'] = 'Comprobar Cumplimiento';
$string['import'] = 'Importar entradas';
$string['import_help'] = 'Permite crear nuevas entradas en un Gestor a partir de un archivo con datos en texto CSV.

El archivo debe contener una primera fila con los identificadores de cada columna. 

Los usuarios son especificados por su código identificador (DNI, username o id, más abajo) no por su nombre y apellidos. 
Las fechas deben introducirse en formato DD-MM-AAA o AAAA-MMM-DD.

En los campos con multiples opciones (casillas, radio, menús) se debe especificar el <strong>nombre</strong> de la opción, no su descripción. 
La identificación es INsensible a las mayusculas, no se tiene en cuenta. "Opción 1" y "opción 1" se consideran iguales. 

Se puede especificar que los usuarios afectados reciban un mensaje de email sobre la nueva entrada.';
$string['export'] = 'Exportar entradas';
$string['export_help'] = 'Permite exportar algunas o todas la entradas en el Gestor como un archivo del formato indicado, y las columnas especificadas.';
$string['download'] = 'Descargar ficheros';
$string['send'] = 'Crear y enviar';
$string['loadoptions'] = 'Cargar opciones en campo';
$string['loadoptions_help'] = 'Un método para la carga masiva en bloque de muchas opciones en un campo, en lugar de una por una.

Cada opción debe ocupar una línea separada en el texto inferior.
dentro de cada línea el nombre y descripción de cada opción se separan con "|", como en
<br>opción1|descripción
<br>opción2|descripción
<br>opción3|descripción

El "nombre" de la opción es discrecional. Si no está presente se utiliza un nombre predefinido.

Se puede usar para borrar multiples opciones en masa si se seleccona el Modo de carga adecuado.

';
$string['fieldoptions'] = 'Opciones del campo';
$string['fieldoptions_help'] = 'Cada opción ocupa una línea separada. Dos posibles ítems por línea, separador "|"

Cada entrada escrita como nombre|descripción. 

El nombre es usado internamente y es opcional. La Descripción es el texto visto por los usuarios. ';
$string['loadmode'] = 'Modo de carga';
$string['loadmode_help'] = 'Comportamiento de las líneas cargadas. 

* Agregar como nuevo: Cada línea de la caja se añade como una nueva entrada. Puede duplicar las opciones existentes.
* Actualizar existentes: Actualiza las opciones con el mismo nombre (o posición). 
* Borrar primero y Agregar nuevo: Primero borra las entradas ya existentes (salvo si están en uso). Después añade cada línea como una nueva entrada. 
';
$string['loadupdate'] = 'Actualizar existentes';
$string['loadadd'] = 'Agregar como nuevo';
$string['loaddelete'] = 'Borrar primero y Agregar nuevo';
$string['optionname'] = 'Opción ';
$string['loadoptionssaved'] = '{$a->updated} Opciones actualizadas y {$a->added} nuevas agregadas.';

$string['issuesearch'] = 'Entradas';
$string['issuesearch_help'] = 'Qué entradas manejar en esta operación

 * Todas: todas las entradas existentes, en cualquier estado. 
 * Abiertas: Todas las entradas en la lista de Abiertas.
 * Cerradas: Todas las entradas en la lista de Cerradas.
 * Búsqueda: aquellas entradas localizaads en una búsqueda activa.
 
';
$string['assigntasktable'] = 'Asignar gestores';
$string['assigntasktable_help'] = 'La herramienta puede ejecutar una rutina para 
asignar automáticamente un gestor a entradas seccionadas.

Las entradas que serán asignada a un gestor <strong>deben</strong> ser definidas, previamente, como una búsqueda guardada en el Gestor de Incidencias.';
$string['assignedtasks'] = 'Asignaciones automatizadas de Gestores';
$string['assignquery'] = 'Busqueda';
$string['assignuser'] = 'Gestor';
$string['addassigntask'] = 'Agregar una asignación de Gestor';
$string['deletetaskconfirmed'] = 'Delete assignation';
$string['deletetask'] = 'Borrar una asignación de Gestor';
$string['deletetask_help'] = 'Borrar una asignación en la tabla de asignaciones de Gestor automatizadas. 

Las entradas que ya hubieran sido asignadas a un gestor permenecerán asignadas al mismo, NO se revierten esas acciones.
';
$string['confirmtaskdelete_message'] = 'Ha solicitado eliminar la asignación automática del Gestor "{$a->user}" 
a las entradas seleccionaad por la búsqueda  "{$a->query}". <br /> 

¿Desea borrar efectivamente esta asignación en el futuro? ';

$string['all'] = 'Todas';
$string['allopen'] = 'Abiertas';
$string['allclosed'] = 'Cerradas';
$string['search'] = 'Búsqueda';
$string['exportfields'] = 'Datos a exportar';
$string['fixedfields'] = 'Campos obligatorios';
$string['fixedfields_help'] = 'Estos campos serán exportados incondicionalmnte en cada entrada. 

Puede considerar incluir el DNI del usuario además de su nombre completo para aquellos campos que hacen referencia a usuarios.
';
$string['useridnumber'] = 'Incluir DNI';
$string['optionalfields'] = 'Campos opcionales';
$string['customfields'] = 'Campos personalizados';
$string['usermodified'] = 'Fecha de modificación';
$string['exportcomments'] = 'Incluir comentarios';
$string['exportcomments_help'] = 'Procede a buscar todos los comentarios a cada entrada realizados 
por el tipo de usuario considerado y los añade secuencialmente aarchivo exportado.
';
$string['exportfiles'] = 'Incluir archivos';
$string['exportfiles_help'] = 'Procede a buscar todos los archivos añadidos en comentarios a cada entrada realizados 
por el tipo de usuario considerado y añade sus nombres secuencialmente al archivo exportado.

Solo se especifican los nombres de archivo. Para descargar realmente el contenido debe usarse la herramienta "Descargar ficheros".
';
$string['exportfileselector'] = 'Archivo a generar';
$string['exportsort'] = 'Ordenar por';
$string['exportsort_help'] = 'Cómo se ordenan las entradas en el archivo generado. 

Además del campo indicado, siempre se ordena a los usuarios por apellidos. 
';
$string['exportfilename'] = 'Nombre del archivo (sin ext.) ';
$string['exportformatselector'] = 'Formato de exportación';
$string['reportedbyidnumber'] = 'DNI del estudiante';
$string['assignedtoidnumber'] = 'DNI del gestor';
$string['commentuser'] = 'Comentarios del usuario';
$string['commentdev'] = 'Comentarios del gestor';
$string['fileuser'] = 'Archivos del usuario';
$string['filedev'] = 'Archivos del gestor';
$string['contentadded'] = '[Contenido añadido el {$a}]';
$string['importedissues'] = 'Importadas {$a} entradas';

$string['comply'] = 'Comprobar actividad';
$string['comply_help'] = 'Esta herramienta permite a un Gestor realizar varias comprobaciones sobre la activida de los usuariso y el cumplimiento de las tareas en este módulo.

Por ejemplo, se puede comprobar si los usuarios o gestores han realizado comentarios (o no), o si han incluido algún archivo, 
o si el campo de Resolución de cada entrada está debidamente rellenado (o no).
';

$string['create'] = 'Generar entradas';
$string['create_help'] = 'Esta herramienta permite a un Gestor generar automáticamente múltiples entradas, una para cada usuario especificado.  

La entrada generada contiene los mismos datos, constantes, para todos los usuarios, según los especificado en el formulario de entrada.

Si existen archivos en una carpeta pre-definida se pueden adjuntar archivos personalizados a cada entrada.
';

$string['warning'] = 'Notificar a usuarios';
$string['warning_help'] = 'Esta herramienta permite a un Gestor realizar notificaciones por e-mail a los usuarios.

Se puede especificar si se notifica a los usuarios o los gestores, o ambos, de las entradas especificadas.
El asunto y el texto del mensaje se especifican en cada ocasión. 
';
$string['setfield'] = 'Rellenar campos privados';
$string['setfield_help'] = 'Esta herramienta permite a un Gestor rellenar el valor de los campos privados de una serie de entradas. 

Se actualizarán todas las entradas seleccionadas, estableciendo el valor especificado (constante para todas las entradas) para cada campo privado existente en este módulo.

';




$string['inserterror'] = 'Error de inserción en BD';
$string['userid'] = 'ID de usuario';
$string['selectattachmentdir'] = 'Selección de Carpeta con archivos para usuarios';
$string['userattachmentsdir'] = 'Carpeta con adjuntos para usuarios';
$string['nouserattachmentsdir'] = 'NO se ha definido una carpeta con adjuntos para usuarios';
$string['userfilename_help'] = 'El nombre de cada archivo debe conformarse al patrón <code>{prefijo}<strong>{usuario}</strong>{sufijo}</code>, incluyendo la extensión';
$string['fileprefix'] = 'Prefijo ';
$string['fileprefix_help'] = 'El nombre del archivo de usuario puede contener una parte inicial COMÚN.

Aquí puede indicar esa parte común, el prefijo de los nombres de archivo. Debe indicar también cualquier símbolo de separación (e.g. - o _).

Recuerde que en la web los nombre de fichero son sensibles a mayúsculas.';
$string['filesuffix'] = 'Sufijos ';
$string['filesuffix_help'] = 'El nombre del archivo de usuario puede contener una parte final COMÚN.

Aquí puede indicar esa parte común, el sufijo de los nombres de archivo. Debe indicar también cualquier símbolo de separación (e.g. - o _).

Se pueden indicar varios sufijos simplemente separando con una barra "/". Por ejemplo, si se indica como sufijos "-A/-B"
entonces se utilizarán todos los archivos que acaben en -A y también los que acaben en -B

Recuerde que en la web los nombre de fichero son sensibles a mayúsculas.
';
$string['fileext'] = 'Extension ';
$string['fileext_help'] = 'La extensión del archivo. Hay que indicar también el punto separador.

Recuerde que en la web los nombre de fichero son sensibles a mayúsculas.';
$string['userfield'] = 'identificador de cada usuario';
$string['needuserfile'] = 'Sólo con archivo';
$string['needuserfile_help'] = '(Requiere el archivo en esta carpeta para ser procesado).';
$string['filerequiredabsent'] = 'Archivo requerido ausente';
$string['nouserfile'] = 'Sin archivo coincidente';
$string['usersfound'] = 'Usuarios no encontrados';
$string['usersnotfound'] = 'Usuarios no encontrados';
$string['filesnotfound'] = 'Sin archivo coincidente';
$string['explainmake'] = 'Los códigos de identificación del campo de entrada se han cotejado con los parcipantes del curso. 

Si aparecen aquí es que han sido correctamente identificados y existen como particpates de este curso.
Los posibles errores aparecen debajo. ';
$string['confirmmake'] = '¿Desea proceder con la generación automática de entradas para estos usuarios? <br />
Esta acción carace de opción "Deshacer". ';
$string['make'] = 'Generar entradas';
$string['createdissues'] = '{$a} nuevas entradas se han generado.';
$string['notmakingnofile'] = 'NO se generarán entradas para estos usuarios, al carecer del archivo requerido.';
$string['makingnofile'] = 'SE generarán entradas para estos usuarios, sin archivo, ya que éste no es obligatorio.';
$string['issuestooperate'] = 'Entradas a considerar';
$string['downloadtype'] = 'Opciones de descarga';
$string['downfield'] = 'Archivos a descargar';
$string['downfield_help'] = 'Qué archivos incluir en el fichero ZIP generado. 
Las opciones son:

 * Todos: cualquier achivo, ya sea en un campo de la entrada (fr tipo "Archivo " o "Imagen")
 o en comentarios qu epertenezvan a esa entrada.
 * Archivos de estudiante: archivos contenidos en comentarios realizados por un estudiante en sus entradas.
 * Archivos de gestor: archivos contenidos en comentarios realizados por un gestor en sus entradas.
 * Nombre de Campo: O puede especificar un campo de tipo "Archivo" o "Imagen" usado en este módulo  para descargar solo esos archivos. 
 
';
$string['allfiles'] = 'Todos';
$string['userfiles'] = 'Archivos de estudiante';
$string['devfiles'] = 'Archivos de gestor ';
$string['groupfield'] = 'Crear carpetas';
$string['groupfield_help'] = 'Cómo se organizarán los archivso en carpetas dentro del ZIP. 

 * NO: No hay carpetas, todos los archivos en una lista única. 
 * Por entrada: El ZIP contendrá una carpeta separada para cada entrada diferente. En esa carpeta estarán almacenados juntos todos los archivos, de cualquier usuario, relacionados con esa entrada.
 * Por estudiante: El ZIP contendrá una carpeta separada para cada estudiante. Todos los archivos pertenecientes a ese studiante, incluso de entradas diferentes se almacenarán juntos en esa carpeta. 
 * Por gestor: Igual que por estudiantes, pero en esta caso considerando los gestores de cada entrada. 
 
 Si hay una coincidencia de nombre en archivso distintsio se utilizará como prefijo en nº de entrada.
';
$string['zipbyissue'] = 'Por entrada';
$string['zipbyuser'] = 'por Estudiante';
$string['zipbydev'] = 'Por Gestor';

$string['usercompliance'] = 'Actividad a comprobar';
$string['hascomments'] = 'Comentarios en entrada';
$string['commentsby'] = 'Realizados por';
$string['hasfiles'] = 'Archivos en entrada';
$string['filesby'] = 'Agregados por';
$string['hasresolution'] = 'Resolución cumplimentada';
$string['indifferent'] = 'No comprobado';
$string['noempty'] = 'Presente';
$string['empty'] = 'Ausente';
$string['last'] = 'Último';
$string['any'] = 'cualquiera';
$string['bothusers'] = 'ambos';
$string['warningoptions'] = 'Opciones para notificación';
$string['warningmailto'] = 'Aviso para';
$string['warningmailto_help'] = 'Se enviarán mensajes de aviso o recordatorio 
a los estudiantes o gestores (o ambos) relacionadso con cada entrada.
 
Los mensajes incluyen automáticamente un enlace a la entrada relevante para cada usuario. 
 ';
$string['messagesubject'] = 'Asunto';
$string['messagebody'] = 'Cuerpo del mensaje';
$string['defaultsubject'] = 'Aviso de actividad en Gestor de Incidencias';
$string['defaultbody'] = 'Este mensaje se refiere a algunas actividades requeridas en un módulo Gestor de Incidencias.';
$string['mailerror'] = 'erro en email';
$string['aboutissue'] = ' Este mesaje se refiere a la entrada {$a}';
$string['warnedissues'] = 'Enviadas {$a} notificaciones por e-mail a usuarios';
$string['controlemailsubject'] = 'Notificaciones emitidas en gestor de Incidencias';
$string['controlemailbody'] = 'Los siguientes usuarios han sido notificados por e-mail acerca de la actividad en el módulo Gestor de Incidencias. ';
$string['complyissues'] = 'Entradas concordantes';
$string['noissues'] = 'Ninguna entrada';
$string['sendalert'] = 'Enviar notificación';
$string['checked'] = 'Alerta';
$string['ignoremodified'] = 'Forzar importación';
$string['ignoremodified_help'] = 'Cómo se comportará la importación cuando los datos importados coinciden con entradas existentes.

La opción predefinida (sin marcar) es preservar el contenido ya existente y NO actualizar, ignorando los datos del archivo importado.
Si se marca esta opción, los datos presentes en el archivo importado sobre-escribirán a los existentes previamente en la misma entrada.
';
$string['ignoremodifiedexplain'] = ' desmarcado para ignorar datos importados si ya existen.';
$string['addoptions'] = 'Nuevas opciones en campos';
$string['addoptions_help'] = 'Cómo se comportará la importación cuando existan nuevos valores no reconocidos, no existentenetes previamente, para un determinado campo. 
Esto es de aplicación en campos de casilla o menús, en los que existen varias opciones predefinidas que el usuario puede marcar. 

La opción predefinida (sin marcar) es ignorar la snuevas opciones no reconocidas, conservando las opciones prexistentes para ese campo.
Si se marca esta opción, los datos existentes en el archivo de exportación y no reconocidos serán agregados como nuevas opciones para el campo en cuestión.';
$string['addoptionsexplain'] = ' desmarcado par ignorar opciones de campo no reconocidas.';
$string['userencoding'] = 'Identificador de usuario';
$string['userencoding_help'] = 'El parámetro usado en el archivo de importación para especificar a cada usuario. Puede ser uno de:
 
  * Moodle ID
  * DNI
  * nombre de usuario

El nombre y apellidos NO pueden ser usados. Los valores importados se cotejarán con los valores correspondientes 
almacenados en las tablas de usuarios para identificar ala persona en cuestión.
';
$string['importmailto'] = 'Notificación por e-mail';
$string['importmailto_help'] = 'Si se marca, los usuarios referidos en un entrada importada, bien estudiantes o gestores, 
recibirán un aviso por e-mail sobre la entrada relevante.
';
$string['eventreportviewed'] = 'Herramienta Trackertools vista';
$string['eventreportdownload'] = 'Trackertools descargar/exportar entradas';
$string['eventreportsent'] = 'Trackertools enviadas alertas';
$string['eventreportcreated'] = 'Trackertools creadas/importadas entradas en bloque';
$string['eventreportupdated'] = 'Trackertools actualizadas entradas en bloque';
$string['eventreportloadoptions'] = 'Trackertools cargadas opciones para campo';
$string['eventreporttaskassign'] = 'Trackertools asignación automática de Gestor';
$string['eventreporttaskremove'] = 'Trackertools borrado de asignación de Gestor';
$string['setmodify'] = 'Modificar';
$string['setissuefields'] = 'Ítems genéricos';
$string['setcustomfields'] = 'ítems personalizados';
$string['csvnocolumns'] = 'No hay columnas de datos reconocibles en el archivo CSV. Po rfavor, revise la primera línea y el carácter separador.';
$string['csvmissingcolumns'] = 'Estas columnas obligatorias NO se encuentran en el archivo CSV: {$a} ';
$string['setprefs'] = 'Establecer preferencias';
$string['mailoptions'] = 'Preferencias de usuarios';
$string['mailoptions_help'] = 'Establece las preferenecias de los usuarios especificados según lo indicado en esta página.';
$string['usertype'] = 'Usuarios afectados';
$string['usertype_help'] = 'Se establecerán como preferencias de usuario los valores indicados más arriba para todos los usuarios del tipop indicado aquí.';
$string['forceupdate'] = 'Actualización';
$string['forceupdate_explain'] = 'Si se deja desmarcado NO se modificarán las preferencias de aquellos usuarios que ya hubieran establecido unas.';
$string['saveduserprefs'] = 'Preferencias guardadas para {$a} usuarios';
$string['errorcannotsaveprefs'] = 'Preferencias NO guardadas para usuarios con IDs: {$a}.';
$string['nofiles'] = 'No hay ficheros del tipo indicado en las entradas seleccionadas';
$string['confirmsearch'] = 'Confirmación de entradas a considerar';
$string['confirmsearch_help'] = 'Un parámetro para confirmar el conjunto de las entradas a considerar.  

Debe tener el mismo valor que el menú inicial para   .';
$string['confirmsearcherror'] = 'Las entradas a considerar deben ser las mismas en ambos parámetros';
$string['taskassigned'] = 'Creada una asignación automática de gestor';
$string['taskdeleted'] = 'Eliminada una asignación automática de gestor';

$string['checkcompliance'] = 'Comprobar';
$string['checkedfield'] = 'Campo a comprobar';
$string['fieldcompliance'] = 'Comprobación de opciones';
$string['fieldcomply'] = 'Comprobar opciones completadas';
$string['fieldcomply_help'] = '
Comprueba si existen (o no) entradas correspondientes a todas y cada una de la sopciones de un menú.

Por ejemplo, si uno delos campos es un menú de asignaturas, indica si existen entradas para todas las asignaturas, 
o bien hay alguna asignatura para la cual NO se ha creado ninguna entrada. 
';
$string['fillstatus'] = 'Comprobar ausencia';
$string['fillstatusexplain'] = 'Si está marcado, la herramienta indicará aquellas opciones del menu que 
NO están utilizadas en ninguna entrada de las indicads';
$string['usercomply'] = 'Comprobar confirmación por usuarios';
$string['menutype'] = 'Tipo de datos';
$string['menutype_help'] = 'El tipo de datos existentes en el menú. Puede ser uno de:

 * Usuarios: cada línea en el menú es un nombre de usuario, usualmente identificado internamente por su DNI.
 * Cursos: cada línea en el menú es una asignatura, identificada internamente por su código, aunque se muestre el nombre completo.
 * Otros: Otro tipo cualqiera de datos sin vinculación especial.
';
