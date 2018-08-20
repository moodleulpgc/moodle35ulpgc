<?PHP
      // block_examswarnings.php - created  © E. Castro 2008

$string['pluginname'] = 'Avisos de Examen';
$string['blockname'] = 'Avisos de Examen';
$string['blocktitle'] = 'Avisos de Examen';
$string['privacy:metadata'] = 'El bloque Avisos de Examen sólo muestrra datos almacenados en otros plugins.';
$string['primaryreg'] = 'Registro de Exámenes principal';
$string['explainprimaryreg'] = 'Registro de Exámenes principal';

$string['examlocations'] = 'Lugares';
$string['examcalls'] = 'Convocatorias';
$string['examdates'] = 'Fechas';
$string['examadd'] = 'Añadir examen';
$string['exam_management'] = 'Gestión de exámenes';


// cadenas de admin_ulpgc (admin settings)

$string['repoexams'] = 'Exams Dir';
$string['explainrepoexams'] = 'Directory to store exams PDFs (used by TF exams application)';
$string['examsettings'] = 'Exams settings ';
$string['explainexamsettings'] = 'Diverse settings that configure TF exams application behavior';
$string['examiners'] = 'Examiners course';
$string['explainexaminers'] = 'courseid for Sala de Examninadores course';
//exams
$string['examssitesmessage'] = 'Texto a mostrar en la pantalla de Selecci&oacute;n de Ex&aacute;menes';
$string['examssitesselect'] = 'D&iacute;as seleccionar';
$string['examssitesbloqueo'] = 'D&iacute;as bloqueo';
$string['examssiteswarning'] = 'D&iacute;as avisos';
$string['explainexamssitesselect'] = 'Se puede elegir el lugar y fecha del examen hasta estos d&iacute;as antes';
$string['explainexamssitesbloqueo'] = 'Estos d&iacute;as antes del examen, si est&aacute; elegido no se puede cambiar';
$string['examssitesextra1dia'] = 'Dia';
$string['explainexamssitesextra1dia'] = 'Fecha limite selección examen Extra-1: dia';
$string['examssitesextra1mes'] = 'Mes';
$string['explainexamssitesextra1mes'] = 'Fecha limite selección examen Extra-1: mes';

$string['examsupdate'] = 'Actualizar Glosario de exámenes';
$string['explainexamsupdate'] = 'Si se activa, se incluyen rutinas en el cron para generar entradas en el Glosario de Exámenes a partir de los archivos generados por la aplicación de exámenes. ';
$string['examsglossary'] = 'Identificador de los Glosarios de Exámenes';
$string['explainexamsglossary'] = 'Cadena de búsqueda para identificar los glosarios de exámenes. Esta cadena debe aparecer en el cm.idnumber del glosario. El formato es ID_titulacion_anualidad';
$string['annuality'] = 'Anualidad';
$string['explainannuality'] = 'La anualidad en curso, en formato corto, por ejemplo: 201011 ';
$string['validcategories'] = 'Categorías válidas';
$string['explainvalidcategories'] = 'Sólo los cursos pertenecientes a estas categorías serán procesados.';

$string['convo-0'] = 'Primer semestre';
$string['convo-1'] = 'Primer semestre';
$string['convo-2'] = 'Segundo semestre';
$string['convo-3'] = 'Extraordinaria 1';
$string['convo-4'] = 'Extraordinaria 2';
$string['convo-5'] = 'Extraordinaria 3';

$string['term'] = 'Semestre';
$string['term-a'] = 'Anual';
$string['term-c1'] = 'Primer semestre';
$string['term-c2'] = 'Segundo semestre';
$string['term-c3'] = 'Ambos semestres';
$string['term-c4'] = 'Ambos semestres';
$string['term03'] = 'Ambos semestres';
$string['term04'] = 'Cuarto semestre';
$string['warnings'] = 'Avisos de Exámenes';
$string['warningduedate'] = '¡Apuntarse a {$a} exámenes!';
$string['warningupcoming'] = '{$a} Exámenes próximos';
$string['roomcallupcoming'] = '{$a} Aulas con examen';

$string['enablereminders'] = 'Habilitar recordatorios de examen';
$string['configenablereminders'] = 'Si se activa, se enviarán mensajes de recordatorio por e-mail a todos los profesores de asignaturas con examen programado en los próximos días';
$string['reminderdays'] = 'Días de antelación';
$string['configreminderdays'] = 'Con cuántos días de antelación respecto a la fecha del examen se enviarán los mensajes de recordatorio';
$string['remindermessage'] = 'Texto del mensaje recordatorio';
$string['configremindermessage'] = 'El contenido del mensaje recordatorio de examen. Se pueden utilizar los comodines %%course%% y %%date%% para emplazar los datos reales.';
$string['examremindersubject'] = 'Recordatorio de examen de la asignatura {$a}.';

$string['enableroomcalls'] = 'Habilitar recordatorios de examen para personal de aulas';
$string['configenableroomcalls'] = 'Si se activa, se enviarán mensajes de recordatorio por e-mail a todo el personal de aulas con examen programado en los próximos días.';
$string['roomcalldays'] = 'Días de antelación';
$string['configroomcalldays'] = 'Con cuántos días de antelación respecto a la fecha de la sesión de examen se enviarán los mensajes de recordatorio al personal de aulas.';
$string['roomcallroles'] = 'Roles de personal';
$string['configroomcallroles'] = 'Los roles de personal de aulas usados en los recordatorios de exámenes en aulas.';
$string['roomcallmessage'] = 'Texto del mensaje recordatorio para el aula';
$string['configroomcallmessage'] = 'El contenido del mensaje recordatorio de examen en Aula. Se pueden utilizar los comodines %%course%% y %%date%% para emplazar los datos reales.';
$string['roomcallsubject'] = 'Recordatorio de exámenes en el Aula {$a}. ';

$string['enablewarnings'] = 'Habilitar alertas de examen';
$string['configenablewarnings'] = 'Si se activa, se enviarán mensajes de alerta por e-mail a todos los estudiantes de asignaturas con examen programado en los próximos días.

Se enviarán dos tipos de alertas:
1) Alerta si el estudiante NO ha reservado un examen próximo.
2) Confirmación de los exámenes reservados próximamente.';
$string['warningdays'] = 'Días de antelación';
$string['configwarningdays'] = 'Con cuántos días de antelación respecto a la fecha del examen se enviarán los mensajes de alerta';
$string['examconfirmdays'] = 'Días de antelación de recordatorios';
$string['configexamconfirmdays'] = 'Con cuantos días de antelación se enviarán los recordatorios de examen inscrito';

$string['warningsubject'] = 'Aviso de examen no reservado: {$a}.';
$string['warningmessage'] = 'Texto del mensaje de alerta';
$string['configwarningmessage'] = 'El contenido del mensaje de alerta de examen no reservado. Se pueden utilizar los comodines %%course%% y %%date%% para emplazar los datos reales.';
$string['confirmsubject'] = 'Aviso de examen próximo: {$a}.';
$string['confirmmessage'] = 'Texto del mensaje de confirmación';
$string['configconfirmmessage'] = 'El contenido del mensaje recordatorio de examen. Se pueden utilizar los comodines %%course%%, %%place%%, %%registered%% y %%date%% para emplazar los datos reales.';

$string['extrarules'] = 'Reglas Conv. Extra';
$string['configextrarules'] = 'Si activado, se emplearán las reglas propias de la Convocatoria Extraordinaria para seleccionar asignaturas y usuarios con exámenes próximos.';
$string['examreminderfrom'] = 'Sistema de Avisos de exámenes de Teleformación';
$string['remindersenderror'] = 'Error';
$string['controlemail'] = 'Email de Control';
$string['configcontrolemail'] = 'Si se especifica, se enviará un mensaje de control al esta dirección conteniendo una lista de todos los profesores a los que se ha enviado un recordatorio.';
$string['controlmailsubject'] = 'Recordatorios de examen del {$a} ';
$string['controlmailtxt'] = 'Se han enviado {$a->num} recordatorios de examen para la fecha programada del {$a->date}.';
$string['controlmailhtml'] = 'Se han enviado {$a->num} recordatorios de examen para la fecha programada del {$a->date}.';
$string['sendstudentreminders'] = 'Correo de confirmación a estudiantes con examen';
$string['sendstudentwarnings'] = 'Correo de alerta a estudiantes SIN reservas de examen';
$string['sendstaffreminders'] = 'Correo de recordatorio a Personal de Aulas con examen';
$string['sendteacherreminders'] = 'Correo de recordatorio a Tutores con examen';
$string['noemail'] = 'NO e-mail';
$string['confignoemail'] = 'NO enviar e-mails, solo pruebas.';
