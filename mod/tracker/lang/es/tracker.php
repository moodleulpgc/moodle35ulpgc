<?php // $Id: tracker.php,v 1.1.10.5 2009/05/31 18:23:10 diml Exp $
      // tracker.php - created with Moodle 1.2 development (2003111400)

$string['pluginname'] = 'Gestor de Incidencias';
$string['pluginadministration'] = 'Gestión de Tracker';
$string['AND'] = ' Y <br />';
$string['IN'] = ' = ';
$string['abandonned'] = 'Cerrada';
$string['action'] = 'Acción';
$string['activeplural'] = 'Activos';
$string['addacomment'] = 'Agregar un comentario';
$string['addanoption'] = 'Añadir una opción';
$string['addaquerytomemo'] = 'Agregar esta consulta a \"Mis búsquedas\"';
$string['addawatcher'] = 'Añadir un observador';
$string['addtothetracker'] = 'Incorporar al Tracker';
$string['administration'] = 'Administración';
$string['administrators'] = 'Administradores';
$string['alltracks'] = 'Mostrar actividad en todos los Gestores';
$string['any'] = 'Cualquiera';
$string['askraise'] = 'Petición de elevar prioridad';
$string['assignedto'] = 'Gestor encargado';
$string['assignee'] = 'Asignado';
$string['assigns'] = 'Asignar';
$string['assignmethod'] = 'Método de asignación';
$string['assignmethod_help'] = '
Cómo se va a realizar la asignación al azar.

* <strong>Por revisor:</strong> Se asignan N entradas a cada revisor, empezando por los que tengan menos ya asignadas.
* <strong>Por entrada:</strong> Se asignan N revisores a cada entrada, empezando por los que tengan menos ya asignadas.

El algoritmo cuida de asignar primero a los revisores/entradas con menor atención, de forma que todos acaben con uan carga similar.
Ningún usuario se asignará como revisor a sus propias entradas.

';
$string['assigndeveloper'] = 'por revisor';
$string['assignissue'] = 'por entrada';
$string['removeassigns'] = 'Borrar asignaciones previas';
$string['randomassignsdone'] = 'Realizadas {$a} asignaciones aleatorias';


$string['attachment'] = 'Archivo adjunto al comentario';
$string['attributes'] = 'Atributos';
$string['autoresponse'] = 'Respuesta automática';
$string['browse'] = 'Todas las incidencias';
$string['browser'] = 'Tabla de incidencias';
$string['build'] = 'Versión';
$string['by'] = '<i>Asignado a</i>';
$string['cascade'] = 'Promocionar al nivel superior';
$string['cascadedticket'] = 'Transferido de: ';
$string['cced'] = 'Participantes';
$string['ccs'] = 'Copias';
$string['checkbox'] = 'Checkbox'; // @DYNA
$string['checkboxhoriz'] = 'Checkbox horizontal'; // @DYNA
$string['chooselocal'] = 'Escoja un tracker local como padre';
$string['chooseremote'] = 'Escoja un servidor remoto';
$string['chooseremoteparent'] = 'Escoja una instancia remota como padre';
$string['clearsearch'] = 'Borrar los criterios de búsqueda';
$string['closeissue'] = 'Dar por cerrada';
$string['comment'] = 'Comentario';
$string['comments'] = 'Comentarios';
$string['component'] = 'Componente';
$string['createnewelement'] = 'Crear un nuevo elemento';
$string['currentbinding'] = 'Casacada actual';
$string['database'] = 'base de datos';
$string['dateupdated'] = 'Fecha de cambio';
$string['datereported'] = 'Fecha de registro';
$string['defaultassignee'] = 'Gestor predeterminado';
$string['defaultassignee_help'] = '
Puede requerir que las incidencias entrantes sean asignadas a uno de los Administrativos potenciales.
Aquí puede indicar cuál.';
$string['dateinterval'] = 'Margen de tiempo';
$string['days'] = 'días';
$string['deleteattachedfile'] = 'Borrar el adjunto';
$string['deleteotherfiles'] = 'Borrar todos los adjuntos salvo el indicado';
$string['dependancies'] = 'Dependencias';
$string['dependson'] = 'Depende de ';
$string['descriptionisempty'] = 'La descripción está vacía';
$string['doaddelementcheckbox'] = 'Agregar un elemento checkbox'; // @DYNA
$string['doaddelementcheckboxhoriz'] = 'Agregar un elemento checkbox'; // @DYNA
$string['doaddelementdropdown'] = 'Agregar un menú desplegable'; // @DYNA
$string['doaddelementradio'] = 'Agregar un elemento radiobutton'; // @DYNA
$string['doaddelementradiohoriz'] = 'Agregar un elemento radiobutton horizontal'; // @DYNA
$string['doaddelementtext'] = 'Agregar un campo de texto'; // @DYNA
$string['doaddelementtextarea'] = 'Agregar un área de texto'; // @DYNA
$string['doupdateelementcheckbox'] = 'Actualizar un elemento checkbox'; // @DYNA
$string['doupdateelementcheckboxhoriz'] = 'Actualizar un elemento checkbox'; // @DYNA
$string['doupdateelementdropdown'] = 'Actualizar un menú desplegable';// @DYNA
$string['doupdateelementradio'] = 'Actualizar un elemento radiobutton'; // @DYNA
$string['doupdateelementradiohoriz'] = 'Actualizar un elemento radiobutton'; // @DYNA
$string['doupdateelementtext'] = 'Actualizar un campo de texto'; // @DYNA
$string['doupdateelementtextarea'] = 'Actualizar un área de texto'; // @DYNA
$string['dropdown'] = 'Menú desplegable';
$string['editoptions'] = 'Editar opciones';
$string['editproperties'] = 'Editar propiedades';
$string['editquery'] = 'Cambiar una consulta almacenada';
$string['editwatch'] = 'Cambiar un observador';
$string['elements'] = 'Elementos disponibles';
$string['elementsused'] = 'Elementos usados';
$string['emailoptions'] = 'Opciones de correo';
$string['emergency'] = 'Consulta urgente';
$string['emptydefinition'] = 'El Tracker de destino no tiene definiciones.';
$string['emptyfields'] = 'Hay campos obligatorios sin completar.<br />
Por favor, revise los campos indicados en esta lista y asegúrese de que están especificados.';
$string['enablecomments'] = 'Permitir comentarios';
$string['file'] = 'Archivo adjunto';
$string['follow'] = 'Seguir';
$string['hassolution'] = 'Exiset una solución publicada para este problema';
$string['hideccs'] = 'Ocultar observadores';
$string['hidecomments'] = 'Ocultar comentarios';
$string['hidedependancies'] = 'Ocultar dependencias';
$string['hidehistory'] = 'Ocultar historia';
$string['history'] = 'Historial';
$string['iamadeveloper'] = 'Puedo solucionar temas';
$string['iamnotadeveloper'] = 'No puedo solucionar temas';
$string['icanmanage'] = 'Puedo gestionar las entradas';
$string['icannotmanage'] = 'No puedo gestionar las entradas';
$string['icannotreport'] = 'NO puedo abrir incidencias';
$string['icannotresolve'] = 'No soy un gestor';
$string['icanreport'] = 'Puedo abir incidencias';
$string['icanresolve'] = 'Estoy asignado a algunas incidencias';
$string['id'] = 'Incidencia';
$string['issueid'] = 'Incidencia';
$string['issuename'] = 'Etiqueta de incidencia ';
$string['issuenumber'] = 'Incidencia';
$string['issues'] = 'entradas';
$string['knownelements'] = 'Elementos reconocidos del Tracker';
$string['listissues'] = 'Vista como Lista';
$string['local'] = 'Local';
$string['lowerpriority'] = 'Menor prioridad';
$string['lowertobottom'] = 'Enviar al fondo';
$string['manageelements'] = 'Gestionar campos';
$string['managenetwork'] = 'Configuración de red y transferencias';
$string['manager'] = 'Gestor';
$string['me'] = 'Mi perfil';
$string['menumultiple'] = 'Permitir selección múltiple';
$string['message_bugtracker'] = 'Gracias por su contribución y ayuda en mejorar este servicio.';
$string['message_taskspread'] = 'Acaba de definir una Tarea. Por favor, no olvide asignarla a algún participantes en las siguientes pantallas para distribuirla.';
$string['message_ticketting'] = 'Se ha registrado su incidencia. Se ha asignado a {$a}.';
$string['message_ticketting_preassigned'] = 'Se ha registrado su incidencia. Será atendida por un Gestor lo antes posible.';
$string['mode_bugtracker'] = 'Seguimiento comunitario de errores (bugtracker) ';
$string['mode_customized'] = 'Gestor personalizado';
$string['mode_taskspread'] = 'Distribuidor de Tareas';
$string['mode_ticketting'] = 'Gestión de incidencias de usuario';
$string['modulename'] = 'Gestor de incidencias';
$string['modulenameplural'] = 'Gestores de incidencias';
$string['month'] = 'Mes';
$string['myassignees'] = 'Gestores que he asignado';
$string['myissues'] = 'Incidencias que resuelvo';
$string['mypreferences'] = 'Mis preferencias';
$string['myprofile'] = 'Mi perfil';
$string['myqueries'] = 'Mis búsquedas';
$string['mytasks'] = 'Mis Tareas';
$string['mytickets'] = 'Mis incidencias';
$string['mywatches'] = 'Mis observadores';
$string['mywork'] = 'Mi gestión';
$string['name'] = 'Nombre';
$string['namecannotbeblank'] = 'El nombre no puede estar en blanco';
$string['newissue'] = 'Nueva incidencia';
$string['noassignedtickets'] = 'Sin incidencias asignadas';
$string['noassignees'] = 'Sin gestor';
$string['nochange'] = 'No cambiar';
$string['nocomments'] = 'Sin comentarios';
$string['nodata'] = 'No hay datos que mostrar.';
$string['nodevelopers'] = 'Sin gestores';
$string['noelements'] = 'Sin campos';
$string['noelementscreated'] = 'No se han creado campos';
$string['nofile'] = 'Sin fichero adjunto';
$string['nofileloaded'] = 'No hay archivos cargados.';
$string['noissuesreported'] = 'No hay incidencias';
$string['noissuesresolved'] = 'No hay incidencias resueltas';
$string['nolocalcandidate'] = 'No hay un candidato local para encadenar';
$string['nomnet'] = 'Moodle network desconectada';
$string['nooptions'] = 'Sin opciones';
$string['noqueryssaved'] = 'No hay búsquedas almacenadas';
$string['noremotehosts'] = 'No hay ningún servidor disponible';
$string['noremotetrackers'] = 'No hay Gestores remotos disponibles';
$string['noreporters'] = 'No hay usuarios, probablemente no se han abierto incidencias.';
$string['noresolvers'] = 'No hay gestores';
$string['noresolvingissue'] = 'Sin incidencias asignadas';
$string['notickets'] = 'Sin incidencias ';
$string['noticketsorassignation'] = 'Sin incidencias o asignaciones';
$string['notifications'] = 'Notificaciones por e-mail';
$string['notificationsdisabled'] = 'Notificaciones desactivadas';
$string['notrackeradmins'] = 'Sin administradores';
$string['nowatches'] = 'Sin observadores';
$string['numberofissues'] = 'Contaje de Incidencias';
$string['observers'] = 'Observadores';
$string['open'] = 'Abierta';
$string['option'] = 'Opción ';
$string['optionisused'] = 'Esta opción ya está en uso para este campo';
$string['options'] = 'Opciones';
$string['order'] = 'Orden';
$string['pages'] = 'Páginas';
$string['posted'] = 'Enviado';
$string['potentialresolvers'] = 'Gestores posibles';
$string['preferences'] = 'Preferencias';
$string['prefsnote'] = 'Las Preferencias configuran qué notificaciones predeterminadas recibirá cuando cambie el estado de una incidencia';
$string['print'] = 'Imprimir';
$string['priority'] = 'Prioridad';
$string['profile'] = 'Opciones de usuario';
$string['published'] = 'Publicado';
$string['queries'] = 'Busquedas';
$string['query'] = 'Búsqueda';
$string['queryname'] = 'Etiqueta de búsqueda';
$string['radio'] = 'Radio buttons'; // @DYNA
$string['radiohoriz'] = 'Radio buttons horizontales'; // @DYNA
$string['raisepriority'] = 'Mayor prioridad';
$string['raiserequestcaption'] = 'Solicitud de aumento de prioridad de una incidencia';
$string['raiserequesttitle'] = 'Petición de aumento de prioridad';
$string['raisetotop'] = 'Enviar al principio';
$string['reason'] = 'Motivos';
$string['register'] = 'Observar esta incidencia';
$string['reportanissue'] = 'Enviar una incidencia';
$string['reportedby'] = 'Usuario';
$string['reporter'] = 'Usuario';
$string['reports'] = 'Informes';
$string['required'] = 'Obligatorio';
$string['requiredelement'] = 'Campo obligatorio que no se puede dejar en blanco';
$string['resolution'] = 'Solución';
$string['resolved'] = 'Resuelta';
$string['resolvedplural'] = 'Incidencias Resueltas';
$string['resolver'] = 'Mis incidencias';
$string['resolvers'] = 'Gestores';
$string['resolving'] = 'En trámite';
$string['saveasquery'] = 'Almacenar una búsqueda';
$string['savequery'] = 'Almacenar la búsqueda';
$string['search'] = 'Buscar';
$string['searchbyid'] = 'Búsqueda por ID';
$string['searchcriteria'] = 'Criterios de búsqueda';
$string['searchresults'] = 'Resultados de la búsqueda';
$string['searchwiththat'] = 'realizar la búsqueda de nuevo';
$string['selectparent'] = 'Seleccionar un padre';
$string['sendrequest'] = 'Enviar petición';
$string['setwhenopens'] = 'No avisarme cuando se abra';
$string['setwhenresolves'] = 'No avisarme cuando se resuelva';
$string['unsetwhentesting'] = 'No avisarme cuando se pruebe';
$string['setwhenthrown'] = 'No avisarme cuando se cierre';
$string['setwhenwaits'] = 'No avisarme cuando se coloque en espera';
$string['setwhenworks'] = 'No avisarme cuando se colque en trámite';
$string['setwhentesting'] = 'No avisarme cuando se conteste';
$string['setoncomment'] = 'Enviarme los comentarios';
$string['sharethiselement'] = 'Disponible en todo el sistema';
$string['sharing'] = 'Publicación';
$string['showccs'] = 'Mostrar observadores';
$string['showcomments'] = 'Mostrar comentarios';
$string['showdependancies'] = 'Mostrar dependencias';
$string['showhistory'] = 'Mostrar historial';
$string['site'] = 'Sitio';
$string['solution'] = 'Solución';
$string['sortorder'] = 'orden';
$string['standalone'] = 'Tracker padre (nivel máximo).';
$string['statehistory'] = 'Estados';
$string['stateprofile'] = 'Perfil de Estados';
$string['status'] = 'Estado';
$string['strictworkflow'] = 'Dinámica de trabajo estricta';
$string['strictworkflow_help'] = '
Si se activa, entonces cada rol de trabajo específico del Gestor (reporter, developer, resolvers, manager)
   sólo podrá acceder a unos estados predefinidos según el rol.  ';
$string['submission'] = '{$a->shortname}: Una nueva incidencia se ha registrado en el gestor "{$a->name}".';
$string['submitbug'] = 'Registre la incidencia';
$string['sum_opened'] = 'Abiertas';
$string['sum_posted'] = 'En espera';
$string['sum_reported'] = 'Abiertas';
$string['sum_resolved'] = 'Resueltas';
$string['summaryadmin'] = 'Resumen';
$string['summary'] = 'Asunto';
$string['supportmode'] = 'Modo de soporte';
$string['testing'] = 'Contestada';
$string['text'] = 'Campo de texto'; // @DYNA
$string['textarea'] = 'Área de texto'; // @DYNA
$string['thanks'] = 'Muchas gracias por usar este servicio. Esperamos darle una respuesta en un plazo breve';
$string['thanksmessage'] = 'Mensaje de agradecimiento';
$string['ticketprefix'] = 'Prefijo de incidencias';
$string['tracker-levelaccess'] = 'Mis permisos en este Tracker';
$string['tracker:addinstance'] = 'Añadir un nuevo Gestor de Incidencias';
$string['tracker:canbecced'] = 'Observar sin asignación';
$string['tracker:comment'] = 'Comentar incidencias';
$string['tracker:configure'] = 'Configurar opciones del Gestor';
$string['tracker:configurenetwork'] = 'Configurar opciones de Red Mnet';
$string['tracker:develop'] = 'Contestar y trabajar incidencias';
$string['tracker:manage'] = 'Editar incidencias';
$string['tracker:managepriority'] = 'Gestionar prioridad de la incidencia';
$string['tracker:managewatches'] = 'Gestionar observadores de la incidencia';
$string['tracker:report'] = 'Crear incidencias';
$string['tracker:resolve'] = 'Resolver incidencias';
$string['tracker:seeissues'] = 'Ver el contenido de la incidencia';
$string['tracker:shareelements'] = 'Compartir elementos en todo el sitio';
$string['tracker:viewallissues'] = 'Ver todas las incidencias';
$string['tracker:viewpriority'] = 'Ver prioridad de mis incidencias';
$string['tracker:viewreports'] = 'Ver informes estadísticos de la gestión';
$string['tracker_cascade_description'] = '<p>Si publica este servicio permite que los Trackers de $a encadenen las incidencias en un tracker local.</p>
<ul><li><i>Depende de</i>: Tiene que subscribir  $a a eset servicio.</li></ul>
<p>La subscripción a este servicio permite a los trackers locales enviar incidencias a otro Tracker en $a.</p>
<ul><li><i>Depende de</i>: Tiene que publicatr el servicio en $a.</li></ul>';
$string['tracker_cascade_name'] = 'Tranferencia de incidencias (Tracker)';
$string['trackerelements'] = 'Definición de campso del Tracker';
$string['trackereventchanged'] = '{$a->shortname}: El estado de la incidencia ha cambiado en el Gestor "{$a->name}"';
$string['trackerhost'] = 'Servidro padre para el Tracker';
$string['trackername'] = 'Nombre del tracker';
$string['transfer'] = 'Transferencia';
$string['transfered'] = 'Transferida';
$string['transferservice'] = 'Transferencia de incidencias';
$string['turneditingoff'] = 'Desactivar edición';
$string['turneditingon'] = 'Activar edición';
$string['type'] = 'Tipo';
$string['unassigned'] = 'sin asignar' ;
$string['unbind'] = 'Elimina encadenado';
$string['unmatchingelements'] = 'Las definiciones de campos de ambos Trackers no se corresponden. Esto puede resultar en fenónemos inesperados si se encadena la tranferencia de incidencias.';
$string['unregisterall'] = 'Eliminar de todos' ;
$string['unsetoncomment'] = 'Avisarme cuando sea comentado ';
$string['unsetwhenopens'] = 'Avisarme cuando sea visto ';
$string['unsetwhenresolves'] = 'Avisarme cuando se resuelva';
$string['unsetwhentesting'] = 'Avisarme cuando esté en pruebas';
$string['unsetwhenthrown'] = 'Avisarme cuando se cierre';
$string['unsetwhenwaits'] = 'Avisarme cuando se coloque en espera';
$string['unsetwhenworks'] = 'Avisarme cuando se empiece a tramitar';
$string['unsetwhentesting'] = 'Avisarme cuando se conteste';
$string['urgentraiserequestcaption'] = 'Un usuario ha solicitado una elevación de priorida urgente';
$string['urgentsignal'] = 'URGENTE';
$string['view'] = 'Incidencias Abiertas';
$string['vieworiginal'] = 'Ver original';
$string['voter'] = 'Vote';
$string['waiting'] = 'En espera';
$string['watches'] = 'Vistas';
$string['youneedanaccount'] = 'Necesita una cuenta autorizada para registrar una incidencia';


// ULPGC strings
$string['mode_tutoring'] = 'Seguimiento de tutorización';
$string['mode_usersupport'] = 'Soporte a usuarios';
$string['mode_boardreview'] = 'Revisión por Junta';
$string['message_tutoring'] = 'Se ha creado una entrada para el seguimienti y revisión de sus planes de tutorización.';
$string['message_usersupport'] = 'Se ha registrado su incidencia. Se atenderá lo antes posible. <br />
Por favor, no abra otras incidencias sobre el mismo tema. En su caso, añada comentarios a ésta.';
$string['message_boardreview'] = 'Su envío ha quedado registrado. Por favor, aguarde a la revisión por la Junta.';
$string['warninguser'] = 'Administración de Teleformación';
$string['warningemailtxt'] = 'Estimado estudiante:
Se ha creado la incidencia [ $a->code ], que le atañe, en la Administración de Teleformación. Puede ver más detalles en el Gestor de incidencias correspondiente
$a->url

Este es un mensaje automático. No responda este mensaje, regístrese en Teleformación y visite la Administración.';
$string['warningemailhtml'] = 'Estimado estudiante: <br />
Se ha creado la incidencia [ $a->code ], que le atañe, en la Administración de Teleformación. Puede ver más detalles en el <a href=\"$a->url\">Gestor de incidencias</a> correspondiente<br />
<br />
<br />
Este es un mensaje automático. No responda este mensaje, regístrese en Teleformación y visite la Administración.';
$string['warningsubject'] = 'Aviso de la Administración de Teleformación';
$string['sendemail'] = 'Aviso automático por e-mail';
$string['userlastseen'] = 'Visto por usuario';
$string['userview'] = 'Otras incidencias';
$string['dateupdated'] = 'Fecha actualizada';
$string['potusers'] = 'Usuarios potenciales';
$string['potusersmatching'] = 'Usuarios filtrados por  \'{$a}\'';
$string['showuserissues'] = 'Mostrar incidencias del usuario';
$string['userissues'] = 'Otras Incidencias';
$string['selectuser'] = 'Seleccionar usuario';
$string['sendtracker'] = 'Instancia del Gestor para Acciones Masivas';
$string['configsendtracker'] = 'Si se especifica, indica la instancia del Gestor de Incidencias
que se empleará automáticamente en las rutinas de "Acciones Masivas de Usuarios" para la reación de Incidencias masiva y su remisión por correo.';
$string['cronruntimestart'] = 'Hora de ejecución';
$string['configcronruntimestart'] = 'A qué hora debe ejecutarse el cronjob que realiza el procesado de prioridades.';
$string['managefiles'] = 'Gestionar carpetas';
$string['reportmaxfiles'] = 'Nº de archivos para usuarios';
$string['configreportmaxfiles'] = 'El nº máximo de archivos que puede adjuntar a una incidencia o comentario un usuario normal.';
$string['developmaxfiles'] = 'Nº de archivos para Gestores';
$string['configdevelopmaxfiles'] = 'El nº máximo de archivos que puede adjuntar a una incidencia o comentario un usuario con la capacidad de Resolver .';
$string['lastcomment'] = 'Comentarios de revisión';
$string['description'] = 'Descripción';
$string['managewords'] = 'Palabras';
$string['wordfor'] = 'Palabra para "{$a}"';
$string['issueword'] = 'Palabra para "issue"';
$string['issueword_help'] = 'Se buscará cualquier aparición de la palabra y se remplazará por otra.
Debe definir aquí el término de búsqueda y la palabra de reemplazo en formato buscar:reemplazar,
separados por comas, primero en plural luego en singular, todo en minúscula.  ';
$string['issueword_explain'] = 'Textos modificados con buscar y reemplazar en formato  buscar:reemplazar ';
$string['issueword'] = 'Palabra para "assignto"';
$string['summaryword'] = 'Palabra para "summary"';
$string['descriptionword'] = 'Palabra para "description"';
$string['statuswords'] = 'Palabras para códigos de estado';
$string['statuswords_help'] = 'Una lista de palabras separadas por comas. El orden es de la máxima importancia, se traducirán según el orden definido en la línea superior.  <br />

POSTED, <br />
OPEN, <br />
RESOLVING, <br />
WAITING, <br />
RESOLVED, <br />
ABANDONNED, <br />
TRANSFERED, <br />
TESTING, <br />
PUBLISHED, <br />
VALIDATED <br />
';
$string['statuswords_explain'] = 'POSTED, OPEN, RESOLVING, WAITING, RESOLVED, ABANDONNED, TRANSFERED, TESTING, PUBLISHED, VALIDATED';
$string['issuedeleteconfirm'] = 'Ha solicitado borrar la entrada con ID {$a} <br />
¿Está seguro de que desea contionuar con el borrado permanente de esta entrada?';
$string['forcedlang'] = 'Lenguaje local'; 
$string['staffupdated'] = 'Modificado por gestor';
$string['lastcomment'] = 'último comentario';
$string['allopen'] = 'Estados abiertos';
$string['allclosed'] = 'Estados cerrados';
$string['trackerissuereported'] = '{$a->shortname}: Creada y asignada una entrada en gestor "{$a->name}".';
$string['trackerissuecommented'] = '{$a->shortname}: Añadido comentario a una entrada en Gestor "{$a->name}".';
$string['adduserwatch'] = 'Agregar usuariso como observadores en las entradas en las que son seleccionados.';
$string['autofilltype'] = 'Auto rellenado de opciones';
$string['autofilltype_help'] = 'Establece un tipo de auto-rellenado dinámico de opciones del campo. Las posibilidades de genera opciones son:

 * Cursos: Las opciones serán nombres de cursos en una categoría especificada (abajo).
 * Categorías: Las opciones serán nombres de categorías de cursosdentro de una categoría padre especificada (abajo). 
 * Usuarios por rol: Las opciones serán nombres de usuarios matriculados con un rol especificado (abajo).
 * Usuarios por grupo: Las opciones serán nombres de usuarios matriculados y pertenecientes a un grupo dado (abajo).
 * Usuarios por agrupamiento: Las opciones serán nombres de usuarios matriculados y pertenecientes a un agrupamiento dado (abajo). 

En todos los casos los nobres internos son los códigos/DNI de las asignaturas o usuarios, y sus descripciones, los nombres visibles.
';
$string['autofillusersrole'] = 'Usuarios por rol';
$string['autofillusersgroup'] = 'Usuarios por grupo';
$string['autofillusersgrouping'] = 'Usuarios por agrupamiento';
$string['autofillidnumber'] = 'Identificador de auto-rellenado';
$string['autofillidnumber_help'] = 'Si se habilita la opción de auto-rellenado, este parámetro define qué ítems serán seleccionados para incluirse como opciones del campo. 
Se interpreta como el "idnumber" de la categoría, curso o usuario diana.

Debe introducir el valor de "idnumber" de la categorái de curso, el grupo o agrupamiento o el DNI del usuario. 

Deje el camp en blanco para indicar que todos los valores (usuarios, cursos) sean incluidos. ';
