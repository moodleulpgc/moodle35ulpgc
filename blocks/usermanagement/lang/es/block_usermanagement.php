<?PHP
      // block_admin_ulpgc.php - created  © E. Castro 2008

$string['pluginname'] = 'Gestión de usuarios';
$string['alphabet'] = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,Ñ,O,P,Q,R,S,T,U,V,W,X,Y,Z';
$string['addmanual'] = 'Añadir manual';
$string['blockname'] = 'Gestión de usuarios';
$string['blocktitle'] = 'Gestión de usuarios';

$string['coursesreview'] = 'Supervisión de cursos/tareas';
$string['coursemanagement'] = 'Administración de cursos';
$string['management'] = 'Gestión de Usuarios';
$string['usermanagement'] = 'Gestiones con usuarios';
$string['frontpagegroups'] = 'Grupos de página principal';
$string['checkedroles'] = 'Checked roles';
$string['configcheckedroles'] = 'Checked roles';
$string['checkerroles'] = 'Checker roles';
$string['configcheckerroles'] = 'Checker roles';


$string['grouproles'] = 'Roles para grupo {$a} ';
$string['configgrouproles'] = 'Los usuarios con cualquiera de estos roles en cualquier curso serán añadidos al grupo: {$a} ';
$string['groupssync'] = 'Habilitar sincronización de grupos y roles';
$string['configgroupssync'] = 'Si se habilita se comprobará el rol de cada usuario y se añadirá a los grupos de más abajo según proceda.';
$string['syncedgroups'] = 'Grupos que se sincronizarán';
$string['configsyncedgroups'] = 'La sincronización de miembros basada en roles de curso se realizará
en aquellos grupos dela página principal cuyo campo IDNUMBER empiece de esta forma ';
$string['enrolmentkey'] = 'Clave de matrícula de los grupos';
$string['configenrolmentkey'] = 'Usado paar identificar los grupos que se sincronizarán.
Sólo los grupos de la página principal con esta clave de matrícula aquí definida serán considerados en la sincronización automática.';

$string['hidefield'] = 'Ocultar campo ';
$string['showfield'] = 'Mostrar campo ';
$string['n_a'] = 'nd';
$string['auth'] = 'Mat.';
$string['confirmed'] = 'Confirmado';
$string['mnethostname'] = 'Servidor';

$string['nouserselected'] = 'No hay usuarios selecionados';
$string['noactionselected'] = 'No hay acciones seleccionadas';

$string['excludedcategories'] = 'Categorías excluídas';
$string['save'] = 'guardar';
$string['usereports'] = 'Certificados de uso';

$string['getusereports'] = 'Obtener certificados';
$string['outputhtml'] = 'En el navegador Web';
$string['outputpdf'] = 'Archivo PDF';
$string['outputxls'] = 'Hoja Excel xls';
$string['outputodf'] = 'Hola Calc ODS';
$string['outputcsv'] = 'Archivo de texto CSV';

$string['reportallcats'] = 'Profesores en todas las categorías';
$string['reportallteachers'] = 'Certificados para TODOS los profesores';
$string['reportoutput'] = 'Formato del certificado';
$string['reportoutputsettings'] = 'Tipos de certificados';
$string['reporttemplate'] = 'Plantilla para certificado PDF ';
$string['reportcategories'] = 'Profesores de cursos en las categorías';
$string['singleuser'] = 'Certificado individual para: ';

$string['updateulpgc'] = 'Recargar datos ULPGC';
$string['coursesdetected'] = 'Cursos del Departamento';
$string['coursesassigned'] = 'Cursos supervisados';
$string['director'] = 'Director';
$string['secretary'] = 'Secretario';

$string['addmarkedtoselection'] = 'Añadir marcados a selección';
$string['addalltoselection'] = 'Añadir TODOS ({$a}) a selección';
$string['delmarkedfromselection'] = 'Borrar marcados de selección';
$string['delallfromselection'] = 'Borrar TODOS ({$a}) de selección';
$string['delselection'] = 'Borrar la selección';
$string['viewselectionusers'] = 'Ver los {$a} usuarios seleccionados';
$string['viewfilterusers'] = 'Volver a filtro de usuarios';
$string['goaction'] = 'Ejecutar en TODOS los seleccionados';
$string['selecteduserswith'] = 'Con los {$a} usuarios selccionados: ';
$string['noselectedusers'] = 'No hay usuarios seleccionados';

$string['tablehead'] =  'Viendo {$a}';
$string['tablesubhead'] =  'Almacenados:  {$a}';
$string['usersontotalusers'] = ' {$a->searchcount} usuarios ENCONTRADOS de {$a->totalcount}';
$string['selectedusers'] = '{$a} usuarios actualmente SELECCIONADOS';
$string['inthispage'] = '{$a} en esta página';
$string['userperpage'] = 'Usuarios por página  ';
$string['checkall'] = 'marcar todos';
$string['uncheckall'] = 'desmarcar todos';

$string['actionsendtracker'] = 'Abrir una Incidencia (Tracker)';
$string['actionsenddialogue'] = 'Enviar un Diálogo';
$string['sendimmessage'] = 'Enviar un Mensaje instantáneo';
$string['actionview'] = 'Ver usuarios en pantalla';
$string['actiondownload'] = 'Descargar usuarios en un archivo';
$string['getactivityinfo'] = 'Obtener informes de actividad';
$string['actiontogglemail'] = 'Activar/desactivar envío de correo';
$string['actionuserattributes'] = 'Cambiar atributos del perfil de usuario';
$string['actionenrol'] = 'Enrolar en múltiples';
$string['actionunenrol'] = 'Desenrolar de múltiples cursos';
$string['actionuserconfirm'] = 'Confirmar cuentas de usuario';
$string['actionuserdelete'] = 'Marcar cuentas de usuario para borrar';

$string['selectattachmentdir'] = 'Selección de Carpeta con archivos para usuarios';
$string['userattachmentsdir'] = 'Carpeta con adjuntos para usuarios';
$string['nouserattachmentsdir'] = 'NO se ha definido una carpeta con adjuntos para usuarios';
$string['userfilenamehelp'] = 'El nombre de cada archivo debe conformarse al patrón <code>{prefijo}<strong>{usuario}</strong>{sufijo}</code>, incluyendo la extensión';
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
$string['nouserfile'] = 'Sin archivo coincidente';
$string['noinstance'] = 'No hay una instancia del módulo \"{$a}\" seleccionada';


$string['assesments'] = 'Correcciones';
$string['messages'] = 'Mensajes';
$string['exportusedata'] = 'Exportar {$a} informes de uso';
$string['datafromcategories'] = 'Categorías de curso: <br />{$a}';
$string['reportsusernotfound'] = 'El usuario de DNI {$a} NO se encuentra en la tabla de personal ULPGC';

$string['startdisplay'] = 'Fecha predeterminada de visibilidad';
$string['configstartdisplay'] = 'Inicialmente sólo las incidencias registradas después de esta fecha serán presentadas en la tabla. <br />El formato debe ser el ISO 8601  año-mes-día (o cualquier entrada válida de strtotime )';
$string['enablecoordmail'] = 'Activar envío a coordinadores';
$string['configcoordemail'] = 'Cuando está activado, junto con <i>enablemail</i>, se enviarán copias por e-mail de los avisos a los supervisores, además de a los usuarios';
$string['pendingmail'] = 'Dirección de copia de control';
$string['configemail'] = 'Esta dirección recibirá una copia de TODOS los avisos de actividad pendeinet enviados a los usuarios';

/**
 * Cadenas de gestión de morosos
 */
$string['actiontodefaulter'] = 'Incluir en listado de morosos';
$string['actionfromdefaulter'] = 'Salir de listado de morosos';
$string['actiondeletedefaulter'] = 'Baja definitiva de morosos';
$string['actionupdatedefaulter'] = 'Actualizar curso de morosos';
$string['isdefaulter'] = 'es estudiante moroso!';
$string['notisdefaulter'] = 'no es estudiante moroso!';
$string['notisstudent'] = 'no es estudiante!';
$string['selecteddefaulters'] = '<strong>Aviso: </strong><i>los siguientes usuarios seleccionados son estudiantes morosos o no son usuarios estudiantes:</i>';
$string['notselecteddefaulters'] = '<strong>Aviso: </strong><i>los siguientes usuarios seleccionados no son estudiantes morosos:</i>';
$string['createdefcourse'] = 'Crear curso de morosos:';
$string['mustfillfullname'] = 'Debe rellenar el nombre completo';
$string['mustfillshortname'] = 'Debe rellenar el nombre corto';
$string['fullname'] = 'Nombre Completo';
$string['shortname'] = 'Nombre Corto';
$string['userselection'] = 'Selección:';
$string['usertodefaultertitle'] = 'PASAR ESTUDIANTES A MOROSOS';
$string['defaultertopaidtitle'] = 'PASAR ESTUDIANTES MOROSOS A PAGADOS';
$string['totaldeletedefaultertitle'] = 'BAJA DEFINITIVA';
$string['updatedefaultertitle'] = 'ACTUALIZAR CURSO DE MOROSOS';
$string['usertodefaulterbody'] = 'Estudiantes impagados pasan a ser morosos';
$string['defaultertopaidbody'] = 'Estudiantes que han pagado dejan de ser morosos';
$string['totaldeletedefaulterbody'] = 'Dar de baja definitiva a estudiantes morosos';
$string['notifytotaldelete'] = '<strong>[Estudiantes eliminados]</strong> <i>Espere por favor...</i>';
$string['notifyusertodefaulter'] = '<strong>[Estudiantes a Morosos]</strong> <i>Espere por favor...</i>';
$string['notifydefaultertopaid'] = '<strong>[Morosos a Estudiantes Pagados]</strong> <i>Espere por favor...</i>';

?>
