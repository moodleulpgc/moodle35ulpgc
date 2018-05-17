<?PHP
      // block_admin_ulpgc.php - created  © E. Castro 2008

$string['usermanagement:addinstance'] = 'Add a new User management block';
$string['usermanagement:myaddinstance'] = 'Add a new User management block to My home';
$string['pluginname'] = 'User management';
$string['alphabet'] = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
$string['addmanual'] = 'Add manual';
$string['blockname'] = 'User management';
$string['blocktitle'] = 'User management';
$string['usermanagement:manage'] = 'Manage user management block';
$string['usermanagement:view'] = 'View user management block';

$string['management'] = 'Administration';

$string['usermanagement'] = 'User Management';
$string['frontpagegroups'] = 'Frontpage Groups';
$string['checkedroles'] = 'Checked roles';
$string['configcheckedroles'] = 'Checked roles';
$string['checkerroles'] = 'Checker roles';
$string['configcheckerroles'] = 'Checker roles';

$string['grouproles'] = 'Roles for group {$a} ';
$string['configgrouproles'] = 'Users with any of these roles un any course will be added to this group:  {$a} ';
$string['groupssync'] = 'Enable course groups sync';
$string['configgroupssync'] = 'If enabled, users will be checked for roles in courses and added to groups below';
$string['syncedgroups'] = 'Frontpage Groups to synchronize';
$string['configsyncedgroups'] = 'Those Front page groups which idnumber field starts by this string will be populated by
course-role based synchronization, if enabled';
$string['enrolmentkey'] = 'Group enrolment key';
$string['configenrolmentkey'] = 'The groups to be synchronized are identified by this enrolmentkey.
Only front page grupos with this enroment key will be considered for automatic synchronization.';
$string['hidefield'] = 'Hide field ';
$string['showfield'] = 'Show field ';
$string['n_a'] = 'n/a';
$string['auth'] = 'Auth';
$string['confirmed'] = 'Confirmed';
$string['mnethostname'] = 'Host';

$string['nouserselected'] = 'No users selected';
$string['noactionselected'] = 'No actions selected';
$string['excludedcategories'] = 'Excluded categories';

$string['usereports'] = 'Usage data';

$string['getusereports'] = 'Get reports';
$string['outputhtml'] = 'Web interface';
$string['outputpdf'] = 'PDF file';
$string['outputxls'] = 'Excel xls spreadsheet';
$string['outputodf'] = 'Calc ODS spreadsheet';
$string['outputcsv'] = 'CSV text file';

$string['reportallcats'] = 'Teachers in any categories';
$string['reportallteachers'] = 'Report for all teachers';
$string['reportoutput'] = 'Report output format';
$string['reportoutputsettings'] = 'Report output type';
$string['reporttemplate'] = 'Report PDF template ';
$string['reportcategories'] = 'Teachers in courses within categories';
$string['singleuser'] = 'Report for single teacher: ';

$string['departments'] = 'Departments';
$string['faculties'] = 'Faculties';
$string['updateulpgc'] = 'Update ULPGC data';
$string['coursesdetected'] = 'Department courses';
$string['coursesassigned'] = 'Courses supervised';
$string['director'] = 'Director';
$string['secretary'] = 'Secretary';

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
$string['actionenrol'] = 'Enrolar en múltiples cursos';
$string['actionunenrol'] = 'Desenrolar de múltiples cursos';
$string['actionuserconfirm'] = 'Confirmar cuentas de usuario';
$string['actionuserdelete'] = 'Marcar cuentas de usuario para borrar';
$string['actionassignrole'] = 'Asignar rol sin enrolar';
$string['actionunassignrole'] = 'Des-asignar rol';
$string['actionforcepassword'] = 'Forzar cambio de contraseña';
$string['purge'] = 'Eliminar actividad de usuarios';

$string['reportsusernotfound'] = 'User {$a} NOT found';


/**
 * Cadenas de gestión de morosos
 */
$string['actiontodefaulter'] = 'Move to slow payers list';
$string['actionfromdefaulter'] = 'Move to payed students list';
$string['actiondeletedefaulter'] = 'Slow payers total remove';
$string['actionupdatedefaulter'] = "Update defaulter's course";
$string['isdefaulter'] = 'is a slow payer student!';
$string['notisdefaulter'] = 'is not a slow payer student!';
$string['notisstudent'] = 'is not a student!';
$string['selecteddefaulters'] = '<strong>Warning: </strong><i>following selected users are slow payer students or are not students:</i>';
$string['notselecteddefaulters'] = '<strong>Warning: </strong><i>following selected users are not slow payer students:</i>';
$string['createdefcourse'] = 'Create course for slow payers students:';
$string['mustfillfullname'] = 'Please, fill in with the full course name';
$string['mustfillshortname'] = 'Please, fill in with the short course name';
$string['fullname'] = 'Full Name';
$string['shortname'] = 'Short Name';
$string['userselection'] = 'Selection:';
$string['usertodefaultertitle'] = 'MOVE STUDENTS TO SLOW PAYER STUDENTS';
$string['defaultertopaidtitle'] = 'REMOVE SLOW PAYER STUDENTS FROM LIST';
$string['totaldeletedefaultertitle'] = 'SLOW PAYERS TOTAL REMOVE';
$string['updatedefaultertitle'] = "UPDATING SLOW PAYER'S COURSE";
$string['usertodefaulterbody'] = 'Students that not payed some course are moved to slow payers students list';
$string['defaultertopaidbody'] = 'Students that payed are removed from slow payers students list';
$string['totaldeletedefaulterbody'] = 'Slow payers are totally removed from system';
$string['notifytotaldelete'] = '<strong>[Students removed]</strong> <i>Please, wait...</i>';
$string['notifyusertodefaulter'] = '<strong>[Students to slow payers list]</strong> <i>Please, wait...</i>';
$string['notifydefaultertopaid'] = '<strong>[Students removed from slow payers list]</strong> <i>Please, wait...</i>';


// cadenas de sendtracker

$string['pluginname'] = 'Crear una Incidencia (Tracker)';
$string['confirm'] = '¿Está seguro de que desea enviar la incidencia a TODOS estos usuarios?';

$string['inserterror'] = 'Error de inserción en BD';
$string['senderrors'] = 'No se han abierto incidencias para estos usuarios: ';
$string['sendsuccess'] = 'Incidencia abierta para los usuarios: ';
$string['nouserfile'] = 'No existe fichero';

$string['sendingtotracker'] = 'Abrir incidencias en el Gestor: ';
$string['trackerselect'] = 'Seleccionar una instancia del Gestor de incidencias';

$string['userid'] = 'ID de usuario';
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
