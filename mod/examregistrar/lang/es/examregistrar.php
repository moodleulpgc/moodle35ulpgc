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
 * English strings for examregistrar
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Registro de Exámenes';
$string['modulenameplural'] = 'Registros de Exámenes';
$string['modulename_help'] = '
El Registro de Exámenes permite definir y gestionar convocatorias y sesiones de examen.

Este módulo gestiona la base de datos de exámenes, con sus convocatorias y sesiones,
y todas los procesos y funciones administrativas asociadas a la realización de exámenes
(fechas de los mismos, inscripción a examen por los estudiantes, asignación de aulas etc.).

Los Gestores pueden establecer las convocatorias y sesiones de examen y sus fechas, así como asignar aulas para examen.

Los Profesores pueden consultar el estado de sus exámenes, nº de estudiantes inscritos y descargar los archivos de examen.

Los Estudiantes puede consultar las fechas de los exámenes e inscribirse para realizarlos. ';

$string['examregistrarfieldset'] = 'Custom example fieldset';
$string['examregistrarname'] = 'Nombre del Registro de Exámenes';
$string['examregistrarname_help'] = 'El nombre con el que aparecerá esta instancia de Registro de Exámenes en la página principal del curso.';
$string['examregistrar'] = 'Registro de Exámenes';
$string['examregistrarsettings'] = 'Opciones de funcionamiento';
$string['pluginadministration'] = 'Administración del Registro de Exámenes';
$string['pluginname'] = 'Registro de Exámenes';

$string['cronruntimestart'] = 'Hora de ejecución de cron';
$string['configcronruntimestart'] = 'La hora del día a la que las tareas de cron que se procesan sólo una vez diariamente serán ejecutadas.';
$string['areaexamfile'] = 'Área de archivos de Exámenes';
$string['areaexamresponses'] = 'Área de archivos de Respuestas';

$string['examregistrar:addinstance'] = 'Añadir un nuevo Registro de Exámenes';
$string['examregistrar:updateinstance'] = 'Actualizar la configuración de una instancia de Registro';
$string['examregistrar:view'] = 'Ver exámenes de Registro';
$string['examregistrar:viewall'] = 'Ver todos los exámenes';
$string['examregistrar:viewcats'] = 'Acceder a exámenes de la categoría';
$string['examregistrar:showvariants'] = 'Ver versiones de examen';
$string['examregistrar:download'] = 'Descargar archivos de examen';
$string['examregistrar:editelements'] = 'Editar elementos del Registro';
$string['examregistrar:manageperiods'] = 'Gestionar convocatorias y sesiones';
$string['examregistrar:managelocations'] = 'Gestionar ubicaciones';
$string['examregistrar:manageseats'] = 'Gestionar aulas y exámenes por sesión';
$string['examregistrar:manageexams'] = 'Crear y cambiar exámenes del Registro';
$string['examregistrar:beroomstaff'] = 'Participar como personal de aulas';
$string['examregistrar:submit'] = 'Enviar versiones de exámenes';
$string['examregistrar:review'] = 'Acceder a la revisión de versiones de examen';
$string['examregistrar:resolve'] = 'Aprobar o rechazar versiones de examen';
$string['examregistrar:delete'] = 'Borrar versiones de examen';
$string['examregistrar:book'] = 'Incribirse uno mismo para examen';
$string['examregistrar:bookothers'] = 'Inscribir a otros en exámenes';

/*
$string['elementtypes'] = 'Tipos de elementos';
$string['configelementtypes'] = 'A comma separated list of element type names.
Each name must be a string of up to 10 characters. The default list is:
period, scope, call, examdate, timeslot, location, role

* period: Ordinary, Extraordinary ...
* scope: midterm, partial, final ...
* call: each separate opportunity to take a given exam
* location: each type of places, city, venue, classroom etc.
* role: each role for a staffer in a location. Supervisor, Officer, ...

';
*/
$string['selectdays'] = 'Periodo de elección';
$string['configselectdays'] = 'Tiempo de antelación en días antes de la fecha de un examen
en el que un Estudiante puede inscribirse paar realizar un examen en una sede. ';
$string['cutoffdays'] = 'Plazo de selección';
$string['configcutoffdays'] = 'Periodo en el que NO se pueden realizar ya inscripciones a examen. Tiempo en días antes de la realización del examen.';
$string['extradays'] = 'Plazo extra adicional';
$string['configextradays'] = 'Días adicionales, a sumar a los anteriores, estableciendo el periodo en el que NO se pueden realizar ya inscripciones a examen, en el caso de Convocatorias Extraordinarias (más de un turno).';
$string['lockdays'] = 'Periodo de bloqueo';
$string['configlockdays'] = 'Periodo en el que un estudiante que se ha inscrito para un examen ya no puede modificar su inscripción.';
$string['printdays'] = 'Plazo de impresión';
$string['configprintdays'] = 'Días antes de un examen en que un examinados puede imprimir los exámenes de su sede.';
$string['responsessheeturl'] = 'Hoja de Respuestas cmID';
$string['printresponsessheet'] = 'Hoja de Respuestas para imprimir';
$string['configresponsessheeturl'] = 'El nº cmid del recurso Archivo conteniendo la hoja de respuestas.';
$string['responsesfolder'] = 'Carpeta para archivos de Respuestas';
$string['configresponsesfolder'] = 'Dirección relativa desde moodledata a la carpeta de archivos de respuesta de exámenes.';
$string['sessionsfolder'] = 'Directorio para sesión ZIP';
$string['configsessionsfolder'] = 'Relative OS filesystem path (starting from moodledata) to the Sessions files folder used to prepare ZIP archives.';
$string['distributedfolder'] = 'Carpeta de respuestas distribuidas';
$string['configdistributedfolder'] = 'Un nombre de carpeta al que mover los archivos de respuestas de examen una vez entregados en las asignaturas respectivas.';

$string['defaultregistrar'] = 'Registro de Exámenes predeterminado';
$string['configdefaultregistrar'] = 'Muchas de las funciones y procesos de un Registro de Exámenes son invocadas por otros módulos o bloque dependientes.
Si ese es el caso y existen varios Registros, aquí se define aquél módulo registro de Exámenes que funcionará como principal.';
$string['staffcategories'] = 'Categorías de curso de Personal';
$string['configstaffcategories'] = 'Si se selecciona alguna categoría de cursos entonces, además del curso base,
cuando se busque Personal para atender aulas de examen se considerarán también los usuarios matriculados en algún curso de esa categoría o categorías.
En cualquier caso se comprobará si los usuarios tienen otorgadas las capacidades para actual como Personal de aula.';
$string['excludecourses'] = 'Excluir cursos administrativos';
$string['configexcludecourses'] = 'Si se activa, la búsqueda anterior NO tendrá en consideración
los cursos que no tengan una carga académica en créditos. Cursos sociales y administrativos serán excluidos de la búsqueda de personal.';
$string['defaultrole'] = 'Rol predeterminado';
$string['configdefaultrole'] = 'Si se especifica, el rol con este IDnumber se usará de forma predefinida
cuando no se especifique otro rol explícitamente para el Personal.';
$string['venuelocationtype'] = 'Tipo de Ubicación de Sedes';
$string['configvenuelocationtype'] = 'Los "Ubicaciones" con este tipo serán utilizados como Sedes potenciales
en la inscripción de estudiantes a examen y en la asignación de aulas por sesión de examen.';
$string['useasprimary'] = 'Registro principal';
$string['useasprimary_help'] = '
Si se selecciona, la instancia indicada proporcionará el Registro principal de Exámenes.

Esto significa que el sistema usará los Exámenes, sesiones, aulas etc. definidos y gestionados por esa instancia de registro de Exámenes.';
$string['thisisprimary'] = 'Est ainstancia es un Registro Principal';
$string['primaryidnumber'] = 'Código ID del Registro principal';
$string['primaryidnumber_help'] = '
Si éste es un Registro principal, esta opción almacena una ID única que permite identificar dicho registro principal.

Debe ser una secuencia de caracteres alfanuméricos SIN espacios. Los caracteres "-" y "_" si son aceptados.';
$string['workmode'] = 'Modo de trabajo';
$string['workmode_help'] = 'Cada instancia de Registro de Exámenes puede configurarse en un modo de trabajo determinado
Each module instance can be configured to serve differente ussage modes or to show several different pieces on information.

* Modo Vista: sólo se puede acceder a los exámenes de la asignatura en la que está incluido el módulo.
No se pueden realizar modificaciones administrativas, sólo consultar los exámenes existentes e inscribirse  en ellos.

* Modo Revisión: Se pueden consultar todos los exámenes de una titulación completa (a los que el usuario tenga acceso).
Se gestiona la revisión de los exámenes por la Junta de Evaluación.

* Modo Registro: Se puede acceder a todo los exámenes de la base de datos, así como a las funciones administrativas y de gestión de los mismos.

';

$string['modeview'] = 'Vista';
$string['modebook'] = 'Inscripción';
$string['modeprint'] = 'Impresión';
$string['modereview'] = 'Revisión';
$string['moderegistrar'] = 'Registro';
$string['annuality'] = 'Anualidad';
$string['annuality_help'] = '
Un código de anualidad, por ejemplo 2014-15 o 201213. Si se especifica, entonces esta instancia de Registro sólo podrá acceder a exámenes de dicha anualidad.';
$string['course'] = 'Asignatura';
$string['programme'] = 'Titulación';
$string['programme_help'] = '
Si se especifica un código de titulación entonces esta instancia de Registro dará acceso sólo a exámenes de dicha Titulación.';
$string['shortname'] = 'Código de Asignatura ';
$string['shortname_help'] = '
A shortname is the code assignd to a specific course or class.
';

$string['lagdays'] = 'Plazo de selección';
$string['lagdays_help'] = 'Periodo en el que NO se pueden realizar ya inscripciones a examen. Tiempo en días antes de la realización del examen.';
$string['reviewmod'] = 'Módulo de Revisión';
$string['reviewmod_help'] = 'Identificación del Módulo de Revisión

La revisión de Exámenes por las Juntas de Evaluación se realiza mediante Incidencias de un Gestor de incidencias.
Aquí se puede especificar qué instancia de gestor de incidencias se encargará de gestionar las revisiones de estos exámenes.

Se debe indicar un "Código ID de calificación" de la instancia que funciona como gestor de revisiones.
Si existen varias con el mismo código se emplea aquella colocada en la misma asignatura o categoría que esta instancia de registro de Exámenes.';
$string['view'] = 'Agenda ';
$string['review'] = 'Revisión ';
$string['printexams'] = 'Exámenes ';
$string['printrooms'] = 'Aulas ';
$string['manage'] = 'Gestión del Registro &nbsp;';
$string['session'] = 'Gestión de Sesión ';

$string['delete_confirm'] = 'Ha solicitado eliminar el elemento {$a->type} denominado {$a->name}.
¿Desea continuar?';


$string['batch_confirm'] = 'Ha solicitado {$a->action} los ítems de tipo {$a->type} siguientes:

{$a->list}

¿Desea continuar?';
$string['unknownbatch'] = 'Acción por lotes desconocida';
$string['element'] = 'Elemento';
$string['elements'] = 'Elementos';
$string['addelement'] = 'Añadir Elemento';
$string['updateelement'] = 'Actualizar Elemento';
$string['degreetype'] = 'Tipo de titulación';
$string['editelement'] = 'Editar elemento';
$string['editelements'] = 'Editar elementos';
$string['editexamsessions'] = 'Editar Sesiones';
$string['examsessions'] = 'Sesiones de examen';
$string['editperiods'] = 'Editar Convocatorias';
$string['periods'] = 'Convicatorias';
$string['exams'] = 'Exámenes';
$string['editexams'] = 'Editar Exámenes';
$string['addexam'] = 'Añadir Examen';
$string['updateexam'] = 'Actualizar Examen';
$string['locations'] = 'Ubicaciones y Aulas';
$string['editlocations'] = 'Editar Ubicaciones';
$string['editstaffers'] = 'Editar personal';
$string['assignstaffers'] = 'Asignar Personal a Aulas';
$string['roomassignments'] = 'Aulas de Examen';
$string['seatassignments'] = 'Asignación de Aula';
$string['editsessionrooms'] = 'Editar aulas por sesión';
$string['assignsessionrooms'] = 'Asignar aulas por sesión';
$string['uploadcsvsessionrooms'] = 'Importar CSV aulas por sesión';
$string['assignseats'] = 'Asignar exámenes a Aulas';
$string['assignseats_venues'] = 'Asignar exámenes en Sedes';
$string['uploadcsvseats'] = 'Asignación de Aulas CSV';
$string['seatstudent'] = 'Ubicar estudiante';

$string['uploadsettings'] = 'Opciones de importación';
$string['uploadtype'] = 'Operación de importación';
$string['uploadtype_help'] = '
La importación puede consistir en una de:

* Cargar Elementos CSV
* Cargar Convocatorias CSV
* Cargar Sesiones CSV
* Cargar Ubicaciones CSV
* Cargar Personal CSV
* Cargar Aulas por sesión CSV
* Cargar Asignación de aula CSV

';
$string['uploadcsvelements'] = 'Importar Elementos CSV';
$string['uploadcsvperiods'] = 'Importar Convocatorias CSV';
$string['uploadcsvexamsessions'] = 'Importar Sesiones CSV';
$string['uploadcsvlocations'] = 'Importar Ubicaciones CSV';
$string['uploadcsvstaffers'] = 'Importar Personal CSV';
$string['uploadcsvsession_rooms'] = 'Importar Aulas por sesión CSV';
$string['uploadcsvassignseats'] = 'Importar Asignación de aula CSV';
$string['uploadcsvfile'] = 'Importar archivo CSV';
$string['uploadcsvfile_help'] = '
Por favor, seleccione un archivo conteniendo datos CSV adecuado para la importación.
La primera línea debe incluir el nombre de la columna. Algunas columnas son opcionales (pueden estar ausentes en el fichero), mientras que otras son un requisito obligatorio.
Las columnas imprescindibles son distintas según el tipo de datos importados, y se identifican por la marca *.
Por el momento los nombres de las columnas deben mantenerse literalmente, sin traducción.


* Importar Elementos CSV:
    type*, name*, idnumber*, value, visible

* Importar Convocatorias CSV:
    name*, idnumber*, annuality*, degreetype*, periodtype*, calls*, timestart*, timeend*, visible

* Importar Sesiones CSV:
    name*, idnumber*, periodidnumber*, annuality*, examdate, timeslot, visible

* Importar Ubicaciones CSV:
    name*, idnumber*, locationtype*, seats*, address, addressformat, parent, sortorder, visible

* Importar Personal CSV:
    examsession*, roomidnumber*, locationtype*, role*, useridnumber, info

* Importar Aulas por sesión CSV:
    examsession*, locationid*, available*

* Importar CAsignación de aula CSV:
    city*, num*, shortname*, fromoom*, toroom*

';
$string['ignoremodified'] = 'Ignorar actualizaciones';
$string['ignoremodified_help'] = '
Si se activa, entonces cuando el archivo CSV contenga datos sobre un elemento que ya existe almacenado en la base de datos,
esos datos serán ignorados y no se modificará la información existente y almacenada.

Si, por el contrario, desea actualizar los datos de elementos ya existentes, se debe ajustar esta opción a NO.';
$string['editidnumber'] = 'Añadir/Actualizar códigos ID';
$string['editidnumber_help'] = '
Esta opción únicamente tiene efecto si tiene el permiso para editar los elementos básicos del Registro de Exámenes.

Si está activo, entonces si una combinación Nomnre/Código ID es nueva y no está almacenada, se añadirá automáticamente un nuevo elemento a la base de datos,
y se procesará el resto de la fila CSV según ese nuevo elemento.

Si está inactivo, entonces una fila CSV que contenga un CódigoID desconocido será simplemnet ignorada y descartada.';
$string['elementitem'] = 'Elemento';
$string['csvuploadsuccess'] = 'Se han importado {$a} líneas de datos con éxito';
$string['uploadtableexplain'] = 'Esta es un previsualización de los primeras líneas del archivo CSV que se pretende importar.

Por favor, verifique que el sistema está interpretando correctamente la estructura del fichero y los datos.';
$string['uploadconfirm'] = '¿Desea proceder a la importación de datos CSV?';
$string['annualityitem'] = 'Anualidad';
$string['annualityitem_help'] = '
Esta opción identifica el curso académico a emplear. Seleccione un valor del menú restringido de ítems disponibles.';
$string['perioditem'] = 'Convocatoria';
$string['perioditem_help'] = '
Esta opción identifica la Convocatoria a emplear. Seleccione un valor del menú restringido de ítems disponibles.';
$string['periodtypeitem'] = 'Tipo de convocatoria';
$string['periodtypeitem_help'] = '
Esta opción identifica el tipo de convocatoria a emplear. Seleccione un valor del menú restringido de ítems disponibles.';
$string['examsessionitem'] = 'Sesión';
$string['examsessionitem_help'] = '
Esta opción identifica la sesión de examen a emplear. Seleccione un valor del menú restringido de ítems disponibles.';
$string['scopeitem'] = 'Tipo de examen';
$string['scopeitem_help'] = '
Esta opción identifica el tipo de examen a emplear. Seleccione un valor del menú restringido de ítems disponibles.';
$string['locationitem'] = 'Ubicación';
$string['locationitem_help'] = '
Esta opción identifica el Ubicación, sede o aula a emplear. Seleccione un valor del menú restringido de ítems disponibles.';
$string['locationtypeitem'] = 'Tipo de Ubicación';
$string['locationtypeitem_help'] = '
Esta opción identifica el Tipo de Ubicación a emplear. Seleccione un valor del menú restringido de ítems disponibles.';
$string['roleitem'] = 'Rol';
$string['roleitem_help'] = '
Esta opción identifica el Rol a asignar al personal. Seleccione un valor del menú restringido de ítems disponibles.';
$string['termitem'] = 'Semestre';
$string['termitem_help'] = '
Esta opción identifica el Semestre académico a emplear. Seleccione un valor del menú restringido de ítems disponibles.';

$string['elementtypeselect'] = 'Tipo de elemento mostrado';

$string['itemname'] = 'Nombre del Ítem';
$string['itemname_help'] = 'El nombre largo, visible, del ítem ';
$string['idnumber'] = 'Código ID';
$string['idnumber_help'] = 'Un código alfanumérico corto y ÚNICO que identifica al elemento. Debe ser una secuencia de menos de 20 caracteres SIN espacios.';
$string['elementtype'] = 'Tipo de Elemento';
$string['elementtype_help'] = 'El tipo de elemento determina su clase y propósito funcional, dónde se aplicará o s epodrá en uso el elemento.

Hay varios tipos posibles de ítem elementales:

* Anualidad: Nombre y código para identificadores de curso académico, p.ej. "2012", "201415" or "2013-14"
* Convocatoria: Nombre y código para cada convocatoria separa alo largo el año en la que se realizan exámenes, p.ej. "Exámenes Ordinarios" o "Convocatoria Especial"
* Tipo de Convocatoria: por ejemplo for instance Ordinaria, Extraordinaria, Especial, Tribunal.
* Sesión: Nombre y código para cada día y hora en la que se celebran exámenes dentro de un periodo de exámenes.
* Tipo de examen: Si se trata de un examen Final o Parcial, Obligatorio u Opcional.
* Ubicación:  Nombre y código para lugares y localizaciones, p. ej. "Aula 101", "Pabellón Auxiliar", "Oficinas centrales", "Toledo".
* Tipo de Ubicación: Nombre y código para cada tipo de ubicación, p. ej.  Aula, Edificio, Sede , Ciudad.
* Rol: Nombre y código para el papel desempeñado por un miembro del personal en una determinada ubicación, p. ej. Supervisor, Instructor, Director, Responsable de Aula, Bedel etc.

';
$string['elementvalue'] = 'Valor numérico';
$string['elementvalue_help'] = '
Algunos tipos de elemntos pueden tener asociado un valor numérico que se puede emplear para sincronizar con sistema externos.';
$string['sortorder'] = 'Orden';
$string['sortorder_help'] = 'Un número para indicar la precedencia en una lista de valores.
Cuando se muestren varios ítems de un tipo en una lista se presentarán ordenados según el valor de este parámetro.';
$string['save'] = 'Guardar';
$string['filter'] = 'Filtrar';
$string['clearfilter'] = 'Reiniciar';
$string['addperiod'] = 'Añadir Convocatoria';
$string['updateperiod'] = 'Actualizar Convocatoria';
$string['numcalls'] = 'Nº turnos';
$string['numcalls_help'] = '
En una convocatoria pueden ofrecerse varias fechas para realizar un examen en el periodo de evaluación.
Este parámetro indica el número turnos o tandas separadas para cada examen en esta convocatoria.

En cualquier caso, cada estudiante sólo podrá realizar el examen una vez en uno de lso turnos, a su elección.';
$string['timestart'] = 'Fecha de inicio';
$string['timestart_help'] = '
La fecha en la empieza el periodo de exámenes.';
$string['timeend'] = 'Fecha de fin';
$string['timeend_help'] = '
la fecha en la que termina el periodo de exámenes.';
$string['visibility'] = 'Visible';
$string['go'] = 'Ejecutar';
$string['withselecteddo'] = 'Con los ítems seleccionados: ';
$string['selectall'] = 'Marcar todos';
$string['selectnone'] = 'Desmarcar todos';
$string['addexamsession'] = 'Añadir sesión';
$string['updatesession'] = 'Actualizar sesión';
$string['examdate'] = 'Fecha del Examen';
$string['examdate_help'] = '
La fecha en la que esta sesión de examen particular tendrá lugar.';
$string['timeslot'] = 'Hora';
$string['timeslot_help'] = '
La hora del día a la que empezará esta sesión de examen.';
$string['setsession'] = 'Asignar sesión';
$string['setparent'] = 'Asignar jerarquía';
$string['resetparents'] = 'Re-establecer jerarquías';
$string['callnum'] = 'Turno';
$string['callnum_help'] = '
Si esta convocatoria incluye varias tandas o turnos, este ítem indica el nº de turno correspondiente a este examen en particular.';
$string['sessionrooms'] = 'Asignar aulas a la sesión';
$string['roomstaffers'] = 'Asignar personal al Aula';
$string['roomstaff'] = 'Personal asignado';
$string['addlocation'] = 'Añadir Ubicación';
$string['updatelocation'] = 'Actualizar Ubicación';
$string['seats'] = 'Puestos';
$string['seats_help'] = '
Capacidad del aula, el nº de estudiantes que pueden ser ubicados en un aula para un examen.';

$string['parent'] = 'Contenedor';
$string['parent_help'] = '
Ubicaciones, lugares y aulas se pueden organizar jerárquicamente: unos contienen o pertenecen a otros. Por ejemplo, los Edificios están en Ciudades y pueden tener Sedes, y las Aulas pertenecen a Edificios o Sedes.

Especificando un contenedor se puede construir una jerarquía de ubicaciones indicando, por ejemplo, qué aulas pertenecen a cada sede.';
$string['address'] = 'Dirección';
$string['staffers'] = 'Personal';
$string['stafferitem'] = 'Examinador';
$string['session_rooms'] = 'Aulas de la sesión';
$string['editsession_rooms'] = 'Aulas por sesión';
$string['assignedrooms'] = 'Aulas asignadas a la sesión';
$string['assignedroomsclearmessage'] = 'Esta lista contiene realmente TODAS las aulas existentes, asignadas o no a otras sesiones.<br />
¡Tenga cuidado! Esta operación borrará las asignaciones de aula existentes para esta sesión y las reemplazará por la sindicadas arriba.';
$string['sessionroomssettings'] = 'Opciones de asignación de aula';
$string['backto'] = 'Volver a {$a}';
$string['allocatedrooms'] = 'Aulas Asignadas';
$string['unallocatedexams'] = 'Exámenes NO asignados';
$string['room'] = 'Aula';
$string['rooms'] = 'Aulas';
$string['additionalexams'] = 'Exámenes adicionales';
$string['additionalexam'] = 'Examen adicional {$a->current} de {$a->total} en este Aula';
$string['moveusers'] = 'Mover ';
$string['fromexam'] = 'desde Examen  ';
$string['fromroom'] = 'desde Aula';
$string['toroom'] = 'hacia Aula';
$string['makeallocation'] = 'Realizar Distribución';
$string['allocateexam'] = 'Ubicar el Examen';
$string['unallocated'] = 'NO distribuidos';
$string['unallocatedbooking'] = '{$a} inscripciones no ubicadas.';
$string['unallocatedyet'] = 'Sin asignar aún';
$string['unallocate'] = 'Ítem sin ubicar';
$string['unallocateall'] = 'Descolocar todos';

$string['refreshallocation'] = 'Nueva distribución';
$string['withselectedtoroom'] = 'Ubicar seleccionados en ';

$string['freeseats'] = ' {$a} libres.';

$string['additionalusersexams'] = 'Exámenes adicionales: {$a->users} estudiantes con {$a->exams} exámenes.';
$string['noadditionalexams'] = 'Exámenes adicionales: ninguno';
$string['roomprintoptions'] = 'Opciones de PDF de Aula';
$string['examprintoptions'] = 'Opciones de PDF de Examen';
$string['userlistprintoptions'] = 'Opciones de PDF de Distribución';
$string['bookingprintoptions'] = 'Opciones de PDF de Inscripciones';
$string['venueprintoptions'] = 'Opciones de PDF de Sede';
$string['venuefaxprintoptions'] = 'Opciones de FAX de Sede';
$string['printingoptions'] = 'Opciones de Impresión';
$string['printingoptionsmessasge'] = 'Aquí puede dar forma a la información que se muestra en cada hoja de Aula.<br />
Los textos pueden contener varias líneas, listas y tablas, y se pueden añadir estilos y tipos usando el editor HTML.<br />
Dispone de unos %%comodines%% para incluir datos específicos en una localización concreta de la hoja de examen o aula.
Algunos de estos comodines son:

<ul>
<li>%%registrar%% : Nombre del módulo gestor de Registro . </li>
<li>%%period%% : Nombre de la Convocatoria. </li>
<li>%%session%% : Nombre de la Sesión. </li>
<li>%%venue%% : Nombre de la Sede/Edificio/Ciudad en la que se realizarán los exámenes(Una sede tiene varias aulas).  </li>
<li>%%date%% : Fecha del examen. </li>
<li>%%time%% : Hora estipulada.  </li>
<li>%%room%% : Nombre del Aula. </li>
<li>%%roomidnumber%% : Código ID del Aula. </li>
<li>%%address%% : Dirección de la Sede/Aula, si existe en la base de datos. </li>
<li>%%seats%% : Capacidad del aula, puestos disponibles. </li>
<li>%%seated%% : Nº de estudiantes ubicados en el aula (con cualquier examen). </li>
<li>%%numexams%% : Nº de exámenes (asignaturas) individuales, separados, ubicados en esta Aula. </li>
<li>%%programme%% : Titulación. </li>
<li>%%shortname%% : Código de la asignatura. </li>
<li>%%fullname%% : Nombre completo de la asignatura. </li>
<li>%%callnum%% : El nº de turno de este examen, si la convocatoria tiene varios turnos. </li>
<li>%%examscope%% : Tipo de examen (p. ej. Parcial, Final etc.) </li>
<li>%%teacher%% : Nombre del Tutor(es) de la asignatura correspondiente a este examen. </li>
</ul>
';

$string['printingbookingtitle'] = 'Sección de Título para la página de Inscripciones';
$string['printingbookingtitle_help'] = 'Esta sección estará colocada inmediatamente antes de la lista de usuarios inscritos y sirve como título e introducción a la misma.

Puede contener el título de la página y los datos de identificación de la sesión y sede o aulas. El texto puede incluir varas líneas, listas, tablas y estilos y formatos usando el editor HTML. Puede emplear %%comodines%% para incluir datos específicos en la posición adecuada del texto.

Debajo de esta sección Moodle incluirá la lista de todos los estudiantes inscritos para este examen en esta sesión.';
$string['printingheader'] = 'Cabecero';
$string['printingheader_help'] = 'Una línea que se empleará como cabecero de la página, en un tipo reducido.
Acepta marcas HTML explícitas para formatos.';
$string['printingfooter'] = 'Pie de página';
$string['printingfooter_help'] = 'Una línea que se empleará como pie de página, en un tipo reducido. ';

$string['printingroomtitle'] = 'Sección de Título de Aula';
$string['printingroomtitle_help'] = 'Esta sección estará colocada delante del resumen de exámenes del Aula.

Puede contener el título principal y los datos de identificación del Aula.
El texto puede incluir varas líneas, listas, tablas y estilos y formatos usando el editor HTML.
Puede emplear %%comodines%% para incluir datos específicos en la posición adecuada del texto.

Después de esta sección Moodle incluirá una lista resumen de de los exámenes (sólo títulos) ubicados en este aula.';
$string['printingexamtitle'] = 'Sección de Título de Examen';
$string['printingexamtitle_help'] = 'Cada examen principal asignado en este Aula tendrá una página separada
con una sección de resumen y una lista de los estudiantes inscritos en el examen.
Esta sección debe incluir la información resumen que desea que se muestre para cada examen.

El texto puede incluir varas líneas, listas, tablas y estilos y formatos usando el editor HTML.
Puede emplear %%comodines%% para incluir datos específicos en la posición adecuada del texto, tales como el nombre de la asignatura, código o Tutores de la misma.

Debajo de esta sección Moodle incluirá una tabla con la lista de estudiantes asignados a este aula para realizar este examen.';
$string['printingvenuesummary'] = 'Sección Resumen de Sede';
$string['printingvenuesummary_help'] = 'El Examen será realizado típicamente por muchos estudiantes repartidos en varias sedes.
Moodle imprimirá una tabla resumen de los estudiantes inscritos distribuidos por Sedes.

Aquí puede especificar cualquier texto o información adicional para ser usado como título y párrafo introductorio de dicha tabla.

El texto puede incluir varas líneas, listas, tablas y estilos y formatos usando el editor HTML.
Puede emplear %%comodines%% para incluir datos específicos en la posición adecuada del texto, tales como el nombre de la asignatura, código o Tutores de la misma.';
$string['printinglistrow'] = 'Campos Extra';
$string['printinglistrow_help'] = '
La página incluirá una lista de cada estudiante inscrito en un Examen y que ha sido ubicado en este Aula.
Cada línea incluye el nombre y DNI del estudiante y puede contener opcionalmente columnas adicionales, extra, con casillas que marcar.

En su caso, introduzca los títulos que servirán como cabecero de dichas columnas.
Deben ser palabras o expresiones cortas separadas por caracteres "|" para indicar la nueva columna.
Se aceptan marcas HTML para formato.. ';
$string['printingcolwidths'] = 'Ancho de la columnas';
$string['printingcolwidths_help'] = '
Puede especificar el ancho relativo de las columnas en la tabla de estudiantes inscritos.

Las anchuras se especifican en % y separados por caracteres "|" para indicar el cambio de columna.
Por ejemplo, 10%|20%|10%|30%|30%. Debe asegurarse de que la suma de todas las anchuras indicadas es el 100%,
o se producirán desajustes no deseados. ';
$string['printingadditionals'] = 'Exámenes adicionales';
$string['printingadditionals_help'] = '
Algunso estudiants del Aula pueden necesitar realizar otros exémenes suplementarios,
además del examen principal del Aula (estudiantes que realizan más de un examen en el aula).

En esta página Moodle presentará la información sobre esos exámenes adicionales:
qué estudiantes los necesitan y qué examen suplementario necesita cara uno.';
$string['printinguserlisttitle'] = 'Sección de título de la lista de Usuarios';
$string['printinguserlisttitle_help'] = 'Esta sección estará colocada inmediatamente
antes de la lista de usuarios inscritos y sirve como título e introducción a la misma.

Puede contener el título de la página y los datos de identificación de la sesión y sede o aulas.
El texto puede incluir varas líneas, listas, tablas y estilos y formatos usando el editor HTML.
Puede emplear %%comodines%% para incluir datos específicos en la posición adecuada del texto.

debajo de esta sección Moodle incluirá la lista de todos los estudiantes convocados a examen en esta sesión.';
$string['singleroommessage'] = 'Sede de aula única. Use "Asignar exámenes en Sedes" ';
$string['stafffromexam'] = 'Personal del Aula desde Examen';
$string['generateexams'] = 'Crear Exámenes por asignaturas';
$string['generateexamssettings'] = 'Opciones de generación';

$string['generatemode'] = 'Modo de Generación';
$string['generatemode_help'] = '
Criterios para decidir cuántos y qué exámenes se han de generar para cada asignatura.

 * Según asignaturas: simplemente un examen por asignatura, convocatoria y turno.
 * Instancias de Tarea Examen: según el número y configuración de instancias de Tarea en la asignatura que usen el plugin Examen.

';
$string['genexamcourse'] = 'Según asignaturas';
$string['genexamexam'] = 'Instancias de Tarea Examen';
$string['genforperiods'] = 'General para las Convocatorias';
$string['genforperiods_help'] = '
Se crearán entradas en la lista de exámenes para cada una de las Convocatorias seleccionadas, (según asignaturas o Tareas).
Se creará una entrada separada para cada turno en la Convocatoria, en caso de existir más de uno.

';
$string['genassignperiod'] = 'Asignación de Convocatoria';
$string['genassignperiod_help'] = '
Criterios para definir a que convocatoria se asociará cada nuevo examen generado para la asignatura.

 * Según selección: Se creará un examen separado para cada Convocatoria seleccionada en la opción anterior. sin más consideraciones.
 * Fecha de inicio de curso: la fecha de inicio del curso se contrastará con las fechas de inicio y fin de cada Convocatoria
y se generará un nuevo examen sólo si el curso está incluido dentro de esas fechas.
 * Semestre de la asignatura: se determinará el Semestre de la asignatura y sólo se generará un nuevo examen cuando coincidan los semestres de la Convocatoria y curso.

';
$string['periodselected'] = 'Según seelcción';
$string['periodfromstartdate'] = 'Fecha de inicio de curso';
$string['periodfromterm'] = 'Semestre de la asignatura';
$string['genassignprogramme'] = 'Asignación de Titulación';
$string['genassignprogramme_help'] = '
Criterios para definir a qué Titulación se asociará cada nuevo examen generado.

   * Nombre corto del curso: el código corto del curso se utilizará como código de Titulación.
   * Número ID del curso: el código de titulación se derivará de las partes de número ID del curso (ULPGC).
   * ID de categoría: el identificador de Titulación será la ID de la categoría del curso.
   * Número ID de categoría: el código de titulación se derivará de las partes de número ID de la categoría (ULPGC).
   * Campo "degree": el código de titulación será el contenido del campo "degree" de la categoría (ULPGC).

';
$string['courseshortname'] = 'Nombre corto del curso';
$string['courseidnumber'] = 'Número ID del curso';
$string['coursecatid'] = 'ID de categoría';
$string['coursecatidnumber'] = 'Número ID de categoría';
$string['coursecatdegree'] = 'Campo "degree"';
$string['gendeleteexams'] = 'Borrar exámenes no coincidentes';
$string['gendeleteexams_help'] = '
Si existen entradas en la lista de exámenes correspondientes a esta asignatura y convocatoria
que NO corresponden a los criterios actuales de generación (no habrían sido generados en esta acción), eliminar dichas entradas.';
$string['genupdateexams'] = 'Actualizar exámenes existentes';
$string['genupdateexams_help'] = '

Si se activa, las entradas ya existentes serán actualizadas en cuanto a la Titulación asignada, sesión o estado de visibilidad.

';
$string['genexamvisible'] = 'Visibilidad del Examen';
$string['genexamvisible_help'] = '
Cómo se establecerá el estado visible/oculto de cada examen generado.

  * Visible: todos los exámenes se crearán como visibles.
  * Oculto: todos los exámenes se crearán como ocultos.
  * Como el curso: el estado del examen será como el estado actual de visibilidad del curso al que pertenece.

';
$string['hidden'] = 'Oculto';
$string['synchvisible'] = 'Como el curso';
$string['generateunrecognized'] = '{$a} Convocatorias de examen no reconocidas.';
$string['generateunrecognizedexam'] = 'Curso: {$a->shortname}; Convocatoria: {$a->periodidnumber}; Tipo: {$a->scope} ';
$string['generatemodcount'] = 'Actualizados {$a->updated}, añadidos {$a->added} y borrados {$a->deleted} exámenes en {$a->courses} cursos.';
$string['student'] = 'Estudiante';
$string['venue'] = 'Sede';
$string['venue_help'] = 'La Sede asociada con una inscripción a examen o una asignación de aulas para una sesión de examen.';
$string['downloadroompdf'] = 'PDF del Aula';
$string['downloadexampdf'] = 'PDF del Examen';
$string['downloaduserlist'] = 'Lista de estudiantes';
$string['printexam'] = 'PDF de examen';
$string['printexamresponses'] = 'PDF de examen con respuestas correctas';
$string['printexamkey'] = 'PDF de examen con plantilla marcada';
$string['take'] = 'Me presento';
$string['takeat'] = 'en';
$string['taken'] = 'Examen realizado';
$string['notbooked'] = 'No inscrito';
$string['booked'] = 'Inscrito';
$string['booking'] = 'Inscripción';
$string['bookings'] = 'Inscripciones';
$string['allocated'] = 'Ubicados';
$string['booking_help'] = 'El procedimiento puede usarse para inscribir a un usuario en este turno o bien para anular la inscripción.';

$string['printroompdf'] = 'Aulas de Examen';
$string['printroomsummarypdf'] = 'Resumen de Aulas';
$string['printexampdf'] = 'Exámenes de la sesión';
$string['printuserspdf'] = 'Lista de convocados';
$string['pageseparator'] = '    ==========================================   PÁGINA DE SEPARACIÓN ==== ';
$string['newexam'] = '                              NUEVO EXAMEN ';
$string['newroom'] = '                              NUEVA AULA ';


$string['roomsinsession'] = '{$a} Aulas asignadas a la sesión';
$string['examsinsession'] = '{$a} Exámenes programados en la sesión';

$string['scheduledexams'] = 'Exámenes programados en esta sesión: {$a}. ';
$string['bookedexams'] = 'Exámenes con inscripciones para esta sesión: {$a}. ';
$string['allocatedexams'] = 'Exámenes distribuidos en esta sesión: {$a}. ';
$string['roomsinvenue'] = 'Aulas donde se realiza este examen en la sede de {$a}: ';
$string['userlist'] = "Lista de estudiantes";
$string['printbinderpdf'] = "Recogida de Exámenes (Fax)";
$string['binderprintoptions'] = "Opciones de Fax Binder";
$string['binder'] = "Fax Binder";
$string['taken'] = "Realizado";
$string['taking'] = "Inscrito";
$string['qualitycontrol'] = "Control de Calidad";
$string['printingbuttons'] = "Botones de Impresión";
$string['managesessionrooms'] = "Aulas de la Sesión";
$string['managesessionexams'] = "Exámenes de la Sesión";
$string['managesessionresponses'] = 'Hojas de Respuestas';
$string['managespecialexams'] = 'Agregar Exámenes Especiales';
$string['assignsessionresponses'] = 'Distribuir Hojas de Respuestas';
$string['loadsessioncontrol'] = 'Archivos de control de sesión';
$string['loadsessionresponses'] = 'Cargar archivos de Respuestas';
$string['responsefiles_help'] = 'Los nombres de los ficheros de respuesta <strong>deben</strong> comenzar por el código corto de la asignatura, <br />
seguido por un "-", y detrás opcionalmente cualquier otro texto identificativo.';
$string['deleteresponsefiles'] = 'Borrar archivos de respuestas';
$string['generateroomspdfs'] = 'Generar PDFs por aulas';
$string['roomspdfsgenerated'] = 'Archivos ZIP de Aula con exámenes generados para: <br> {$a}';
$string['printmode'] = 'Modo de impresión';
$string['printmode_help'] = '
Una señal que permite controlar la impresión en lotes de archivos. El modo predetrminado es "a doble cara". <br />
Si se esatblece en "a una cara" entonces de añadirá una etiqueta "a-una-cara" al nombre del fichero en los archivos ZIP de impresión por aulas.';
$string['printsingle'] = 'a una cara';
$string['printdouble'] = 'a doble cara';

$string['printroomwithexams'] = 'ZIP con PDF de Aula + exámenes';
$string['nonexistingexamfile'] = 'examen_no_existente';
$string['nonexistingmessage'] = '




    Aquí deberían ir {$a->seated} copias del examen {$a->programme}-{$a->shortname} de la asignatura


        {$a->fullname}



    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR


    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR


    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR
';

$string['specialexamsinsession'] = '{$a} exámenes especiales en la sesión';
$string['specialstudentsinsession'] = '{$a} estudiantes con exámenes especiales';
$string['specialfor'] = 'En asignatura';
$string['addspecial'] = 'Agregar Reserva';
$string['specialexam'] = 'Especial (Reserva)';
$string['specialexamfileexists'] = 'El archivo de examen de este Examen especial (Reserva) para esta sesión ya existe';
$string['distributedresponsefiles'] = '{$a} Hojas de Respuesta entregadas';
$string['pendingresponsefiles'] = '{$a} Hojas de Respuesta esperando';
$string['unknownresponsefiles'] = '{$a} Archivos de respuestas no identificados en sesión';
$string['qcbookingsnonallocated'] = 'Inscripciones no ubicadas';
$string['qcvenuesnonallocated'] = 'Problemas de ubicación por Sede';
$string['qcexamsnonallocated'] = 'Exámenes no distribuidos';
$string['qcroomsnonstaffed'] = 'Aulas sin personal';
$string['qcstaffnonallocated'] = 'Personal con examen y sin Aula';
$string['countbookingsnonallocated'] = 'Nº de inscripciones (estudiante-examen) para la sesión sin ubicar: {$a}';
$string['countexamsnonallocated'] = 'Nº de exámenes individuales inscritos pero no distribuidos: {$a}';
$string['countroomsnonstaffed'] = 'Aulas de la sesión sin personal asignado: {$a}';
$string['countstaffnonallocated'] = 'Tutores con examen en la sesión sin Aula asignada: {$a}';
$string['nonbookedexams'] = 'Exámenes sin inscripciones para esta sesión';
$string['sortby'] = 'Ordenar por: ';
$string['sortprogramme'] = 'Titulación-asignatura';
$string['sortfullname'] = 'Nombre de Asignatura';
$string['sortbooked'] = 'Más inscritos';
$string['sortroomname'] = 'Nombre del aula';
$string['sortseats'] = 'Puestos totales';
$string['sortfreeseats'] = 'Puestos libres';
$string['addextracall'] = 'Añadir turno extra oculto';
$string['extraexamcall'] = 'Turno extra oculto';
$string['extraexamcall_help'] = '
Un turno oculto es visible para los Tutores y puede incluirse en la distribución de exámenes por aulas,
pero los estudiantes NO pueden ver ni inscribirse en dicho examen directamente.
Un turno oculto extra es adecuado cuando un estudiante debe realizar un examen suplementario por causas de fuerza mayor o tasadas.';
$string['addstaffer'] = 'Añadir Personal';
$string['addstaffer_help'] = 'El procedimiento pude ser empleado para añadir o eliminar miembros del Personal según los exámenes asignados a cada aula. Los Tutores de los exámenes asignados al aula serán asignados a su vez como personal del aula. ';
$string['removestaffer'] = 'Eliminar Personal';
$string['bookinghelp1'] = 'Inscribirse para realizar un examen la fecha y sede seleccionadas es <strong>obligatorio</strong> para poder accder al Aula y realizar el examen.
Si no se inscribe en el plazo estipulado será rechazado y no será admitido al examen.
Puede inscribirse sólo en una única fecha o turno por Convocatoria, incluso si en la convocatoria hay más de un turno.';
$string['bookinghelp2'] = '<p>Para inscribirse en un examen debe indicar necesariamente la Sede en la que realizará dicho examen. Si cambia de opinión o ánimo puede indicar "NO" en el campo "Me presento" para anular la inscripción. <br /> 
<span class=" alert-success ">Si quiere cambiar de sede debe <strong>primero anular la inscripción previa</strong> poniendo "Me presento" = NO en la sede previa y <strong>solo después</strong> podrá inscribirse en otra nueva sede. </span>  <br />
</p>
<p>Puede inscribirse en un examen en cualquier momento hasta {$a->lagdays} días antes de la fecha del examen. Después de ese plazo ya no podrá inscribirse al examen. 

Esto es, pueden inscribirse para un examen <span class=" alert-error "><strong>hasta las 23.55 horas del {$a->weekday} anterior a cada {$a->weekexamday} de examen</strong></span>, no después. </p>
';
$string['bookingerror_noexam'] = '{$a} : No puede inscribirse en la Sede indicada debido a la ausencia se una Sesión o fecha de examen seleccionada.';
$string['bookingerror_nosite'] = '{$a} : No puede inscribirse en el examen {$a} debido a que no ha indicado la Sede.';
$string['bookingerror_twosites'] = '{$a} : No puede inscribirse en {$a} debido a que ya se ha inscrito para otro examen en la misma fecha en otra Sede.';
$string['bookingerror_noexamid'] = '{$a} : No puede inscribirse en {$a} debido a que la ID del examen es inválida.';
$string['bookingerror_offbounds'] = '{$a} : No puede inscribirse en {$a} debido a que está fuera del plazo de inscripción.';
$string['setbooking'] = 'Submit bookings';
$string['downloadassignseats'] = 'Exportar asignación de aulas';
$string['exambookedstudents'] = 'Estudiantes inscritos en este examen: {$a}.';
$string['totalseated'] = 'Estudiantes distribuidos: {$a}.';
$string['totalbooked'] = 'Estudiantes inscritos: {$a}.';
$string['occupancy'] = 'Ocupación';
$string['existingroom'] = 'Ya asignada previamente';
$string['teachers'] = 'Tutores';
$string['multiteachers'] = 'Tutores con varios exámenes';
$string['potentialusers'] = 'Usuarios potenciales';
$string['potentialusersmatching'] = 'Usuarios encontrados';
$string['examsforperiod'] = 'Exámenes programados en la Convocatoria: {$a}';
$string['examsforsession'] = 'Exámenes en la sesión : {$a}';
$string['examcourses'] = 'Asignaturas con examen';
$string['noexamcourses'] = 'Asignaturas sin examen';
$string['noexams'] = 'No se han encontrado exámenes';
$string['noexam_1'] = 'No se he encontrado ningún examen para esta asignatura';
$string['noexam_2'] = 'Esta asignatura no tiene exámenes programados en este curso académico';
$string['noexam_3'] = 'Esta asignatura no tiene exámenes programados en la Convocatoria seleccionada';
$string['noexam_4'] = 'Esta asignatura consta como superada en una Convocatoria anterior';
$string['selectuser'] = 'Seleccionar usuario';
$string['showuserexams'] = 'Mostrar exámenes del usuario';
$string['searchname'] = 'Asignatura ';
$string['asc'] = 'Creciente';
$string['desc'] = 'Decreciente';
$string['attempts'] = 'Versiones del Examen';
$string['attempt'] = 'Versión';
$string['attempt_help'] = '

El fichero cargado debe estar asociado a una versión de examen.

Puede bien añadir una nueva versión para contener este nuevo archivo cargado o bien
seleccionar una de las versiones existentes del examen y así sustituir el archivo del examen por el cargado aquí.

';

$string['attemptn'] = 'Versión nº {$a}';
$string['attemptname'] = 'Nombre de la versión';
$string['attemptname_help'] = '

Una versión puede tener un nombre o identificador. debe ser corto, idealmente un par de palabras o tres.

Si no se especifica, se usará \'Versión N\'.

';
$string['addreviewitem'] = 'Añadir el ítem de revisión';
$string['addattempt'] = 'Añadir una nueva versión';
$string['uploadexamfile'] = 'Añadir archivo a una versión';
$string['uploadexamfile_help'] = '

El archivo cargado se adjuntará el examen indicado como su Archivo de Examen correspondiente a la versión seleccionada.

';
$string['exam'] = 'Examen';
$string['examfile'] = 'Archivo de Examen';
$string['examfile_help'] = 'Archivo de Examen

El archivo que contiene las preguntas de examen en el formato adecuado, listo para ser presentado a los estudiantes.

';
$string['examfileanswers'] = 'Archivo de respuestas correctas';
$string['examfileanswers_help'] = 'Archivo de respuestas correctas

Un archivo conteniendo las preguntas de examen con sus respuestas correctas e información adicional.

';
$string['examresponsesfiles'] = 'Archivos de Respuestas al examen';
$string['examfileresponses'] = 'Archivo de Respuestas al examen';
$string['examfileresponses_help'] = 'Archivo de Respuestas al examen

Archivo o archivos conteniendo la hojas de respuesta rellenadas por los estudiantes que han realizado el examen.

';
$string['statereview'] = 'Revisión';
$string['status'] = 'Estado';
$string['status_help'] = 'Estado de revisión del Examen

 * Creado
 * Enviado
 * Aprobado
 * Rechazado

';
$string['send'] = 'Enviar Archivo para revisión';
$string['sent'] = 'Enviado para revisión';
$string['approve'] = 'Aprobar el Archivo de Examen';
$string['approved'] = 'Aprobado';
$string['create'] = 'Revisión del Archivo de Examen';
$string['addissues'] = 'Añadir items de Revisión';
$string['reject'] = 'Rechazar el Archivo de Examen';
$string['rejected'] = 'Rechazado';
$string['status_created'] = 'Creado';
$string['status_sent'] = 'Enviado';
$string['status_waiting'] = 'En espera';
$string['status_rejected'] = 'Rechazado';
$string['status_approved'] = 'Aprobado';
$string['status_validated'] = 'Validado';
$string['missingreview'] = '(No encontrado)';
$string['confirm_delete'] = 'Ha solicitado borrar el Archivo de Examen {$a->attempt} en estado {$a->status} correspondiente a: <br>
Asignatura: {$a->coursename} <br />
Convocatoria: {$a->period}, Tipo: {$a->examscope}, Turno: {$a->callnum}
<br />
¿Desea continuar?';
$string['confirm_status'] = 'Ha solicitado {$a->action} el estado correspondiente a: <br>
Asignatura: {$a->coursename} <br />
Convocatoria: {$a->period}, Tipo: {$a->examscope}, Turno: {$a->callnum}
<br />
¿Desea continuar?';
$string['status_synch'] = 'Sincronización global del estado de revisión ';
$string['confirm_synch'] = '
Ha solicitado sincronizar el estado {$a} de cada examen con su correspondiente Incidencia de revisión de Exámenes.
<br />
¿Desea continuar?';
$string['examresponses'] = 'Archivo de Respuestas al Examen';
$string['examresponsesdown'] = 'Descargar archivos de Respuestas al Examen';
$string['nottaken'] = 'Nadie presentado';
$string['nottakenyet'] = 'No realizado aún';
$string['notyet'] = 'Aún no';
$string['missingrole'] = 'Necesita seleccionar un Rol antes de poder asignar Personal a un aula.';
$string['missingvenue'] = 'Necesita seleccionar una Sede antes de poder asignar aulas a la Sesión (y sede).';
$string['missingbookedsite'] = 'Intento de inscripción/asignación a examen sin especificación de Sede.';
$string['extraexams'] = 'Turnos Extra';
$string['allsessions'] = 'Todas las sesiones';
$string['error_manyapproved'] = 'Más de un Archivo de examen aprobado';
$string['error_noneapproved'] = 'Sin archivo de examen aprobado';
$string['error_nonesent'] = 'Sin archivos de examen enviados';
$string['extensionanswers'] = 'Sufijo del archivo de Correctas';
$string['configextensionanswers'] = 'El sufijo a añadir al nombre del fichero de examen con las respuestas correctas incluidas.
Debe contener cualquier separador y puntuación, pero excluye la extensión real del archivo.';
$string['extensionkey'] = 'Sufijo del archivo de Claves';
$string['configextensionkey'] = 'El sufijo a añadir al nombre del fichero de examen con las plantilla rellenada.
Debe contener cualquier separador y puntuación, pero excluye la extensión real del archivo.';
$string['extensionresponses'] = 'Sufijo del archivo de Respuestas';
$string['configextensionresponses'] = 'El sufijo a añadir al nombre del fichero de examen con las hojas de respuesta rellenadas por los estudiantes.
Debe contener cualquier separador y puntuación, pero excluye la extensión real del archivo.';
$string['pdfaddexamcopy'] = 'Añadir copia de Exámenes';
$string['configpdfaddexamcopy'] = 'Si se activa, al PDF de una Aula se añadirán copias de los PDF de Examen a realizar en dicha Aula.';
$string['pdfwithteachers'] = 'Incluir Tutores';
$string['configpdfwithteachers'] = 'Si se activa, los PDFs generados para Examen y Aula
incluirán siempre la lista de Profesores/Tutores de la asignatura.';

$string['examitem'] = 'Examen';
$string['examsqc'] = 'Control de Calidad';
$string['selectperiod'] = 'Seleccione una Convocatoria de examen';
$string['genericqc'] = 'Comprobaciones genéricas de cursos';
$string['items'] = 'Ítems';
$string['deleteexams'] = 'Borrar exámenes listados';
$string['addexams'] = 'Añadir exámenes';
$string['examsqcnoexamcourses'] = 'Asignaturas sin examen: {$a}';
$string['examsgcnocourse'] = 'Exámenes con curso/titulación incorrectos: {$a}';
$string['periodqcnoexamcourses'] = 'Asignaturas sin examen en Convocatoria : {$a}';
$string['periodqcnocourse'] = 'Exámenes de la Convocatoria incorrectos: {$a}';
$string['periodqcwrongnumber'] = 'Exámenes con incorrecto nº de Turnos: {$a}';
$string['periodqcwrongsession'] = 'Exámenes en sesión incorrecta: {$a}';

$string['mailfrom'] = 'Registro de Exámenes de Teleformación';
$string['mailresponsessubject'] = 'Respuestas de examen en la asignatura {$a}';
$string['mailresponsestext'] = 'Se ha generado un archivo {$a->fname} con Respuestas de Examen correspondiente a la sesión {$a->session} en la asignatura {$a->course}.';
$string['mailsessioncontrolmailsessioncontrolmailsessioncontrol'] = 'Respuestas de examen de la sesión {$a}';
$string['mailsessioncontrol'] = 'Archivos de respuesta entregados en las asignaturas:
{$a}
';
$string['generateextracallef'] = 'Generar PDFs extra';

$string['resortbyshortname'] = 'Por nombre corto';
$string['resortbyfullname'] = 'Por nombre completo';
$string['resortbyidnumber'] = 'Por idnumber';

$string['eventmanageviewed'] = 'Gestión del registro vista';
