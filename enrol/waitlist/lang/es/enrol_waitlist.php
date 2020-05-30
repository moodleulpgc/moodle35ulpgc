<?php
/**
 * *************************************************************************
 * *                  Waitlist Enrol                                      **
 * *************************************************************************
 * @copyright   emeneo.com                                                **
 * @link        emeneo.com                                                **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  **
 * *************************************************************************
 * ************************************************************************
*/
$string['customwelcomemessage'] = 'Messaje de bienvenida personalizado';
$string['defaultrole'] = 'Rol predeterminado';
$string['defaultrole_desc'] = 'Seleccione el rol que será asignado a los usuarios al utilizar este método de matrícula';
$string['enrolenddate'] = 'Fecha de terminación';
$string['enrolenddate_help'] = 'Fin del plazo de matricula. Si se activa, los estudiantes solo se pueden matricular por si mismos hasta esta fecha.';
$string['enrolenddaterror'] = 'El fin del plazo no puede ser anterior al inicio del plazo de matrícula.';
$string['enrolme'] = 'Matricularme';
$string['enrolperiod'] = 'Duración de la matrícula';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid (in seconds). If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Duración del periodo de tiempo durante el cual es válida esta matrícula, 
empezando a partir del momento en que el usuario se automatricula. Si se desactiva, la duración será ilimitada.';
$string['enrolstartdate'] = 'Fecha de inicio';
$string['enrolstartdate_help'] = 'Inicio del plazo de matricula. Si se activa los estudiantes se puede automatricular solo a partir de esta fecha, no antes.';
$string['groupkey'] = 'Usar clave grupal de matrícula';
$string['groupkey_desc'] = 'Use group enrolment keys by default.';
$string['groupkey_help'] = 'Si se especifica una, se pedirá a los estudiantes que introduzcan esta clave para poder matricularse. 

Además, si configura uno o varios grupos en el curso, cada uno con una clave de matrícula, 
cuando cada estduianet se matricule se inscribirá automáticamenet en uno de esos grupos según la clave que use para matricularse. 

Para usar la clave de matrícula grapal debe especificar las claves adecuaads en los ajustes de cada grupo.';
$string['longtimenosee'] = 'Desmatricular inactivos';
$string['longtimenosee_help'] = 'Si los estudiantes NO han accedido al curso durante un largo periodo pueden considerarse inactivos y candidatos a su desmatriculación. 
Este parámetro establece el tiempo mínimo para considerarlos inactivos y desmatricularlos automáticamente..';
$string['maxenrolled'] = 'Máximo nº de estudiantes';
$string['maxenrolled_help'] = 'Especifica el número máximo de estudiantes que pueden matricularse usando este método. 0 significa sin límite.';
$string['maxenrolledreached'] = 'Maximum number of users allowed to waitlist-enrol was already reached.';
$string['password'] = 'Clave de acceso';
$string['password_help'] = 'Una clave de acceso permite limitar el accesio al curso solo aquellos estudiantes que conozcan y escriban la clave al matricularse.
An enrolment key enables access to the course to be restricted to only those who know the key.

Si se deja en blanco cualquier usuario de la plataforma podrá matricularse.

Si se especifica una clave, cuando el usuario trate de automatricularse por este método se le pedirá que introduzca esta clave para realizar una automatrícula válida. Solo se requiere la clave para la matrícula inicial, posteriormente no se volverá asolicitar esta clave.';
$string['passwordinvalid'] = 'Clave de acceso incorrecta, por favor escríbala otra vez';
$string['passwordinvalidhint'] = 'Clave de acceso incorrecta, por favor escríbala otra vez<br />
(Esta es una pista - empieza por \'{$a}\')';
$string['pluginname'] = 'Automatrícula con cupo';
$string['pluginname_desc'] = 'La extensión "Automatrícula con cupo" permite a los usuarios automatricularse a cursos pero con un cupo limitado de admisiones.

El curso puede estar protegido adicionalmente por una clave de acceso. 

Internamente esta extensión usa la mátricula manual, que debe estar activada en el mismo curso.';
$string['requirepassword'] = 'Requiere clave de acceso';
$string['requirepassword_desc'] = 'Require enrolment key in new courses and prevent removing of enrolment key from existing courses.';
$string['role'] = 'Asignar rol';
$string['waitlist:config'] = 'Configure waitlist enrol instances';
$string['waitlist:manage'] = 'Manage enrolled users';
$string['waitlist:unenrol'] = 'Unenrol users from course';
$string['waitlist:unenrolwaitlist'] = 'Unenrol waitlist from the course';
$string['sendcoursewelcomemessage'] = 'Enviar mensaje de bienvenida';
$string['sendcoursewelcomemessage_help'] = 'Si se activa, los usuarios recibirán un mensaje de confirmación y binevenida al curso cuando se automatriculan válidamente en el mismo.';
$string['showhint'] = 'Mostra pista';
$string['showhint_desc'] = 'Mustra l aprimera letra de la clave de acceso.';
$string['status'] = 'Permite automatrículas';
$string['status_desc'] = 'Allow users to waitlist enrol into course by default.';
$string['status_help'] = 'Esta parámetro determina si un usuario puede automatricularse (y desmatricularse, si tiene los permisos adecuados) en este curso usando este método de matrícula.';
$string['unenrolwaitlistconfirm'] = 'Quiere desmatricularse del curso "{$a}"?';
$string['usepasswordpolicy'] = 'Emplear la política de contraseñas';
$string['usepasswordpolicy_desc'] = 'Emplear la política stándard de moodle sobre los caracteres válidos y seguridad en contraseñas.';
$string['welcometocourse'] = 'Bienvenido al curso {$a}';
$string['welcometocoursetext'] = '<p>¡Bienvenido a "{$a->coursename}"!</p>



<p>Comienzo: {$a->startdate}</p>

<p>Descripción: </p>
	
	<p>{$a->summary}</p>
  ';
$string['confirmation'] = 'Este es el método de Automatriculación con cupo. <br/> Si sigue adelante se automatriculará en este curso como estudiante.
<br/> <br/>
¿Esta seguro que desea automatricularse en este curso? ';
$string['confirmationfull'] = '<strong>Este curso está completo actualmente.</strong> Si continúa será colocado en una lista de espera, y se matriculará automáticamente en caso de que se produzca alguna vanante.
<br>
<br>
No podemos garantizar que realmente se produzcan vacantes ni cuándo. <br>';
$string['confirmation_yes'] = 'Si';
$string['confirmation_no'] = 'No';
$string['waitlistinfo'] = '<b>Este curso está completo actualmente. NO acepta más automatrículas</b>. <br/><br/>Gracia spor su solicitud. Ha sido añadido a la lista de espera. Será matriculado e informado por correo electrónico en caso de que se produzca alguna vacante.';
$string['waitlist:unenrolself'] = 'Unenrol self from the course';
$string['lineinfo'] = '<br>Número de personas delante de usted en la lista de espera: ';
$string['lineconfirm'] = '<br>¿Estás seguro de que quiere seguir adelante?';
$string['confirmation_cancel'] = 'Cancelar';
$string['enrolusers'] = 'Matricular usuarios';
$string['disable'] = 'NO puede automatricularse en este curso.';
$string['continue'] = 'Continuar';
$string['users_on_waitlist'] = 'Usuarios en lista de espera';
$string['waitlisted_users'] = 'Usuarios en espera';

$string['faculty'] = 'Faculty';

// ecastro ULPGC 
$string['usewaitlist'] = 'Usar lista de espera';
$string['usewaitlist_help'] = 'Si se activa, se ponen en uso las funcionalidades de la lista de espera. 
Si no, no habrá lista de espera y cualquier intento de matrícula después de alcanzado el cupo máximo será rechazado sin dar opción a entrar en la lista de espera.';
$string['confirmationfullno'] = '<strong>Este curso está completo actualmente. NO acepta más automatrículas.</strong> 
<br>
<br>
Por favor, consult eotro curso de estas categorías. <br>';
$string['maxcourses'] = 'Cupo de automatrículas';
$string['maxcourses_help'] = 'Si este parámetro es diferente de 0, entonces establece el límite maximo de cursos en los que el usuario puede automatricularse usando este método.

El límite es calculado para cada categoría de cursos. Este límite solo s eaoplica a cursos de la misma categoría que éste.';
$string['maxcoursesfull'] = 'Este es el método de Automatriculación con cupo. <br/>Usted ya está matriculado del máximo de {$a} cursos automatriculables por este método en esta categoría.<br />
Lo sentimos pero NO puede automatricularse en este curso usando este método.';
$string['profiledescription1'] = 'Parámetro 1 para el campo';
$string['profiledescription3'] = 'Parámetro 3 para el campo';
$string['profiledescription4'] = 'Parámetro 4 para el campo';
$string['profiledescription5'] = 'Parámetro 5 para el campo';
$string['defaultremindersubject'] = 'Recordatorio de Inscripción en curso';
$string['enroladdusers'] = '{$a->waitlist} en {$a->instancename}';
$string['noreplyname'] = 'Recordatorio automático de matriculación';
$string['oranycohorts'] = 'Miembro de alguna de';
$string['oranycohorts_help'] = '
La sincronización afectara a los usuariso que sean miembros de al menos una, cualquiera, de las cohortes seleccionaads en esta caja.

Este campo es obligatorio, se debe especificar al menos una cohorte aquí';
$string['andallcohorts'] = 'Y en todas';
$string['andallcohorts_help'] = 'Opcionalmente, los usarios seleccionados también deberán ser moembrso de <strong>todas</strong> las cohortes indicadas aquí.';
$string['notcohorts'] = 'NO en ';
$string['notcohorts_help'] = 'Opcionalmente, se pueden especificar algunas cohortes de las que el usuario NO debe ser miembro (todas o al menos una según el parámetro siguiente).';
$string['noenrolgroups'] = 'Se puede enrolar y además asignar a un grupo.';
$string['enrolgroup'] = 'Agregar al grupo';
$string['enrolgroup_help'] = 'Si se habilita, permite agregar a los usuarios sincronizados como miembros de un grupo, además de matricularlos aquí.

Indicar el grupo al que añadir a los usuarios. Puede ser un curso existente o uno nuevo creado automáticamente:

La opción "Grupo de mentaenlace" creará un único grupo denominado como este método de matrícula e insertará en el mismo todos los usuarios.
La opción "Grupos por cohorte" creará varios grupos denominados como las cohortes de origen en "Miembro de alguna de" e insertará a cad usuario en uno oo varios de esos grupos según su pertenecia a cada cohorte..
';
$string['creategroup'] = 'Grupo de metaenlace';
$string['multiplegroup'] = 'Grupos por cohorte';
$string['assigngroup'] = 'Asignación a grupos';
$string['assigngroup_help'] = 'Si se activa, además de ser enrolado, 
el usuario será asignado a un grupo ogrupso segun se especifica en la opción de debajo.';
$string['headerallowedusers'] = 'Usuarios matriculables';
