<?php
/**
 * Cadenas de texto de la extensión de sincronización de la ULPGC
 *
 * @package local_ulpgcgroups
 * @copyright  2016 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Grupos ULPGC';
$string['[ulpgcgroups:manage'] = 'Gestionar grupos ULPGC';

// settings
$string['groupssettings'] = 'Grupos ULPGC';
$string['enabledadvancedgroups'] = 'Habilitar interfaz avanzado';
$string['explainenabledadvancedgroups'] = 'Si se activa entonces las páginas de grupos usarán un interfaz con herramientas adicionales.';
$string['forcerestrictedgroups'] = 'Habilitar restricciones en grupos';
$string['explainforcerestrictedgroups'] = 'Si se activa, entonces se usaran las restricciones de borrado y marcado de ULPGC';
$string['onlyactiveenrolments'] = 'Solo matrículas activas';
$string['explainonlyactiveenrolments'] = 'si se activa, entonces solo los usuarios con una matrícula activa serán listados en las páginas de grupos.';
$string['colorrestricted'] = 'Color para restricciones';
$string['explaincolorrestricted'] = 'Los nombre de grupos y miembros de grupo estarán resaltados en este color en los listados';
$string['managegroups'] = 'Gestion de Grupos';
$string['exportgroups'] = 'Exportar grupos';
$string['controlledgroups'] = 'Los grupos y usuarios en color son gestionados centralizadamente. NO pueden ser borrados.';
$string['anygrouping'] = 'Cualquier agrupamiento';
$string['groupingmenu'] = 'Grupos en';
$string['groupingmenu_help'] = 'Si se selecciona un agrupamiento particular, 
sólo se listarán los grupos pertenecientes a dicho agrupamiento.';
$string['exclusivegroupingconflict'] = 'Usuarios duplicados en grupos: {$a} ';
$string['exclusivegroupingconflict_help'] = 'Este agrupamiento se ha configurado para asignar a cada usuario en un único grupo del agrupamiento.
No obstante, sucede que los grupos indicados contienen a uno o más usuarios en común, que son miembros de varios grupos del agrupamiento.';

$string['userorder'] = 'Nombres de usuario por';
$string['userorder_help'] = 'Indica cómo se presentarán los nombres de usuario en los listados: <br />
Ordenados por apellidos o por nombre de pila del usuario.';
$string['emptygroup'] = 'Vaciar este grupo';
$string['emptygroupconfirm'] = 'Ha solicitado eliminar todos los miembros del grupo "{$a}". <br />
¿Desea ejecutar esta acción?';
$string['removeuser'] = 'Quitar este miembro';
$string['removenotallowed'] = 'Grupo gestionado por rutina externa. No está permitido quitar a este usuario.';
$string['deletenotallowed'] = 'Este grupo está gestionado por una rutina externa. El borrado no está permitido.';
$string['sourcegroup'] = 'Mostrar únicamente miembros del grupo';
$string['sourcegroup_help'] = 'Permite seleccionar miembros potenciales solo de un grupo padre especificado. 
De esa forma el grupo formado será estrictamente un subgrupo de este grupo padre.';
$string['forceexclusive'] = 'Forzar pertenencia a un único grupo en el Agrupamiento:';
$string['forceexclusive_help'] = 'Permite identificar y marcar a aquellos usuarios que pertenecen a variso grupos dentro del agrupamiento indicado.';
$string['controlledgroupalert'] = 'Grupo gestionado por una rutina externa. Los miembros con nombre en color no pueden ser borrados. Puede añadir y eliminar otros miembros.';
$string['singlegroupmembership'] = 'Forzar perneencia a un único grupo';
$string['singlegroupmembership_help'] = 'En muchas actividades los usuariso deberían estar distribuidos de forma que cada uno pertenezca a solo un grupo como máximo. 
Esta opción permite establecer una señal para indicar que se debe activar la comprobación de pertenecia múltiples grupos, para identificar y resaltar esos casos. ';
$string['explainsinglegroupmembership'] = 'Los usuarios deben pertenecer a un único grupo del Agrupamiento. Habilita el chequeo activo.';

$string['exportgroupselector'] = 'Indicar grupos a exportar';
$string['exportdataselector'] = 'Indicar datos a listar para cada miembro de grupo';
$string['exportuserselector'] = 'Indicar usuarios a incluir en cada listado de grupo';
$string['exportgroup'] = 'Grupos';
$string['exportgroup_help'] = 'Si se especifica, solo el grupo indicado será exportado. 
Hay dos opciones especiales:

 * Todos: se exportan los grupos de cualquier agrupamiento, sin limitaciones. 
 * Ninguno: solo se exportan los grupos que NO pertenecen al agrupamiento indicado debajo.
';
$string['exportgrouping'] = 'Grupos del agrupamiento';
$string['exportgrouping_help'] = 'Si se especifica, entonces solo se exportarán los grupos que pertenezcan al agrupamiento indicado. 
Hay dos opciones especiales:

 * Ninguno: Se exportan solo los grupos que NO pertenecen a ningún agrupamiento.
 * Cualquiera: Se exportar todos los grupos, sin limitación por agrupamiento.
';
$string['exportuserroles'] = 'Usuarios con roles';
$string['exportuserroles_help'] = 'Solo usuarios matriculados con uno de estos roles serán incluidos en el listado de cada grupo.';
$string['exportincludeuserroles'] = 'Incluir roles del usuario';
$string['exportincludeuserroles_help'] = 'Si se marca, una columna contendrá los roles del usuario en el curso.';
$string['exportusersdetails'] = 'Campos adicionales';
$string['exportusersdetails_help'] = 'Información adicional de cada usuario a incluir en el listado. 

El nombre, apellidos y DNI de cada usuario siempre son exportados. Adicionalmente se pueden marcar estos campos para ser incluidos como columnas extra.';
$string['exportextracolumns'] = 'Columnas extra';
$string['exportextracolumns_help'] = 'Una lista de nombres de columna separados por comas. 

Si se especifica, entonces se añadiran al listado tantas columnas vacías como se indique, con el nombre indicado, para contener datos adicionales una vez exportado.

Por ejemplo: 
Puesto, Asistencia, Calificación
';

$string['exportformatselector'] = 'Formato de exportación';
$string['exportdownload'] = 'Descargar';
$string['groupmembershipexists'] = 'Ya es miembro del grupo';
$string['notenrolledincourse'] = 'Usuario no enrolado en el curso, no agregado';
$string['groupmembershipfailed'] = 'No agregado debido a fallo en la inscripción en grupo';
$string['groupmembershipadded'] = 'Usuario agregado como miembro del grupo';
$string['usernotfoundskip'] = 'Usuario no encontrado, desestimado';
$string['enclosure'] = 'Carácter circundante';
$string['enclosure_help'] = 'Carácter circundante de cada campo en texto y ficheros CSV. 

En ficheros CSV cada campo multipalabra puede estar circundado por un carácter que marca el inicio y final del campo. 
Es un detalle opcional. Si se utiliza, debe ser un único carácter, no un conjunto de letras, usualmente ["] o [\'].';
$string['task_rolesyncgroups'] = 'Sincronizar grupos de Portada por rol';
$string['task_cohortsyncgroups'] = 'Sincronizar grupos de Portada por cohorte';
$string['enablefpgroupsfromcohort'] = 'Habilitar sincronización de grupos por cohorte';
$string['explainenablefpgroupsfromcohort'] = 'Si se habilita, puede seleccionar unas cohortes y sus miembros se asignaran como miembros de un grupo de la página principal con el mismo código de identificación.';
$string['fpgroupscohorts'] = 'Cohortes a sincronizar con grupos de Portada';
$string['explainfpgroupscohorts'] = 'Se creará un grupo en la Portada (curso de sitio) para cada cohorte, con sus miembros sincronizados. 
Los usuarios añadidos manualmente podrán ser eliminados también manualmente.';
$string['enrolmentkey'] = 'Sincronización de grupos por rol';
$string['explainenrolmentkey'] = 'Una clave de matriculación para identificar los grupos de Portada que serán poblados a partir de asignaciones de rol de los usuarios. 
Dejar vacío para deshabilitar y no usar roles como mecanismo de pertenencia a grupos de Portada.';
$string['grouproles'] = 'Roles para el grupo {$a}';
$string['explaingrouproles'] = 'The users with the selected roles, in any context, will be synched as group members in group {$a}. 
Leave empty to disable and not use roles as frontpage group assignment mechanism.';

