<?PHP
      // block_examswarnings.php - created  © E. Castro 2008

$string['pluginname'] = 'Avisos de Exámenes';
$string['blockname'] = 'Exámenes Teleformación';
$string['blocktitle'] = 'Exámenes Teleformación';

$string['primaryreg'] = 'Registro de Exámenes principal';
$string['explainprimaryreg'] = 'Registro de Exámenes principal';
$string['examswarnings:addinstance'] = 'Añadir un bloque Exámenes TF';
$string['examswarnings:myaddinstance'] = 'Añadir un bloque Exámenes TF a la página personal';
$string['examswarnings:manage'] = 'Gestionar Exámenes Teleformación';
$string['examswarnings:view'] = 'Ver Exámenes Teleformación';
$string['examswarnings:select'] = 'Seleccionar una fecha de examen';
$string['examswarnings:supervise'] = 'Supervisar exámenes';

$string['examlocations'] = 'Lugares';
$string['examcall'] = 'Convocatoria';
$string['examcalls'] = 'Convocatorias';
$string['examdates'] = 'Fechas';
$string['examadd'] = 'Añadir examen';
$string['exam_management'] = 'Gestión de exámenes';
$string['assigndate'] = 'Asignar examen';

// error messages
$string['onlyforteachers'] = 'Sólo los profesores pueden usar esta página';

// cadenas de admin_ulpgc (admin settings)

$string['repoexams'] = 'Exams';
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

$string['examsupdate'] = 'Update Exams glossaries';
$string['explainexamsupdate'] = 'Id enabled, cron will include a routine to search for newly generated exam archives and create entries into Exams glossaries. ';
$string['examsglossary'] = 'ID for Exams Glossaries';
$string['explainexamsglossary'] = 'A search string to identify Exams Glossaries. This string must appear in cm.idnumber for the glossary.';
$string['annuality'] = 'Annuality';
$string['explainannuality'] = 'The annual period for exams, biannual, in short form e.g. 201011 ';
$string['validcategories'] = 'Valid categories';
$string['explainvalidcategories'] = 'Only courses in the the selected categories will be included in Exams processing';

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
$string['warningduedate'] = '¡Apuntarse a examen!';
$string['warningupcoming'] = 'Próximos exámenes';
$string['roomcallupcoming'] = 'Room Staff';
$string['warningduedate'] = 'Book {$a} exams!';
$string['warningupcoming'] = '{$a} Upcoming exams';
$string['roomcallupcoming'] = '{$a} Rooms with exam';

$string['enablereminders'] = 'Enable exam reminders';
$string['configenablereminders'] = 'If active, an email message will be sent to all teachers of courses with an exam scheduled in next days.';
$string['reminderdays'] = 'Days behorehand';
$string['configreminderdays'] = 'How many days before the scheduled exam date the reminder will be issued';
$string['reminderroles'] = 'Reminder roles';
$string['configreminderroles'] = 'Roles of users to send teacher reminders.';
$string['remindermessage'] = 'Reminder message';
$string['configremindermessage'] = 'Text of the email message. The placeholders %%roomname%%, %%roomidnumber%, %%date%%, %%examlist%% may be used to substitute for actual info ';
$string['examremindersubject'] = 'TF Exam reminder. Course: {$a}. ';

$string['enableroomcalls'] = 'Enable room staff reminders';
$string['configenableroomcalls'] = 'If active, an email message will be sent to all Staff assigned to a room in an exam session scheduled in next days.';
$string['roomcalldays'] = 'Days behorehand';
$string['configroomcalldays'] = 'How many days before the scheduled exam session date the Room reminder will be issued';
$string['roomcallroles'] = 'Staff Reminder roles';
$string['configroomcallroles'] = 'Roles of users to send room staff reminders.';
$string['roomcallmessage'] = 'Staff Reminder message';
$string['configroomcallmessage'] = 'Text of the email message. The placeholders %%course%% and %%date%% may be used to substitute for actual info ';
$string['roomcallsubject'] = 'TF Staff reminder. Room: {$a}. ';

$string['enablewarnings'] = 'Enable exam warnings';
$string['configenablewarnings'] = 'If active, an email message will be sent to all students of courses with an exam scheduled in next days.';
$string['warningdays'] = 'Days behorehand for warnings';
$string['configwarningdays'] = 'How many days before the scheduled exam date the warning will be issued';
$string['warningdaysextra'] = 'Days behorehand for Extra';
$string['configwarningdays'] = 'How many days before the scheduled exam date the warning will be issued, in case on Extraordinary calls';
$string['examconfirmdays'] = 'Days behorehand for reminders';
$string['configexamconfirmdays'] = 'How many days before the scheduled exam date the reminder for users with booked exams will be issued';
$string['warningroles'] = 'Warnings roles';
$string['configwarningroles'] = 'Roles of users to send student warnings.';



$string['warningsubject'] = 'TF Exam warning: {$a}. ';
$string['warningmessage'] = 'Warning message';
$string['configwarningmessage'] = 'Text of the email message. The placeholders %%course%% and %%date%% may be used to substitute for actual info ';
$string['confirmsubject'] = 'TF Exam confirmation: {$a}. ';
$string['confirmmessage'] = 'Warning message';
$string['configconfirmmessage'] = 'Text of the email message. The placeholders %%course%%, %%place%%, %%registered%% and %%date%% may be used to substitute for actual info ';

$string['extrarules'] = 'Enable Extra rules';
$string['configextrarules'] = 'If activated, extra rules will be applied to select exams and users. ';
$string['examreminderfrom'] = 'TF Exams reminder';
$string['remindersenderror'] = 'Fail';
$string['controlemail'] = 'Control email';
$string['configcontrolemail'] = 'If set, an email message will be sent to this address with a list of all reminders issued to teachers.';
$string['controlmailsubject'] = 'TF Exam reminder for {$a} ';
$string['controlmailtxt'] = '{$a->num} Exam reminders for the exams scheduled for {$a->date} have been sent.';
$string['controlmailhtml'] = '{$a->num} Exam reminders for the exams scheduled for {$a->date} have been sent.';
$string['sendstudentreminders'] = 'Send confirmation to students with exams';
$string['sendstudentwarnings'] = 'Send warnings to students with non-booked exams';
$string['sendstaffreminders'] = 'Send reminders to Staff with exams';
$string['sendteacherreminders'] = 'Send reminders to Teachers with exams';
$string['noemail'] = 'No e-mail';
$string['confignoemail'] = 'Do NOT send e-mails, only testing';
