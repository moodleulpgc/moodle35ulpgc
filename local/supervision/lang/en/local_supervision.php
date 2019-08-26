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
 * Strings for component 'local_supervision', language 'en'
 *
 * @package    local_supervision
 * @copyright  2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Supervision';
$string['supervision:manage'] = 'Manage supervision settings and supervisors';
$string['supervision:editwarnings'] = 'Edit supervision warnings';
$string['supervision:viewwarnings'] = 'View supervision warnings';
$string['supervisionsettings'] = 'Supervision settings';
$string['managewarningsettings'] = 'Supervison warnings settings';
$string['warnings'] = 'Manage warnings';
$string['actwarningshdr'] = 'Supervision warnings';
$string['configsupervisionplugins'] = 'Enable/Disable and organize the plugins as desired';

$string['admininstances'] = 'Admin restricted instances';
$string['assignrolesfaculty'] = 'Manage Faculty role assignments';
$string['assignrolesdepts'] = 'Manage Department role assignments';

$string['checkedroles'] = 'Roles to check';
$string['checkerrole'] = 'Supervising Role';

$string['supervisor'] = 'Supervisor';
$string['coursesreview'] = 'Degree supervision';
$string['tasksreview'] = 'Supervision of Tasks';
$string['departmentsreview'] = 'Department Supervision';
$string['nowarnings'] = 'No warnings filed';


$string['checkedroles_help'] = 'Only the selected roles will be processed as potential subjects of <strong>teacher</strong> pending activity';
$string['checkerrole_help'] = 'Only the selected role will be assigned as supervisor';
$string['enablemail_help'] = 'If activated, when the cron job detects a delay longer then expected it will send an e-mail to the teacher(s) as warning, in addition to recording and storing the failure';
$string['enablestats_help'] = 'If enabled, the cronjob will detect, flag and store some conditions where a response is expected from teachers. That is \"pending activity\". This may be useful to elaborate logs of tasks achievement by teachers' ;
$string['excludedcategories_help'] = 'Selected course categories will be excluded form supervision data collection';
$string['excludecourses'] = 'Exclude admin courses';
$string['excludecourses_help'] = 'If set, courses without a credits count will be excluded form supervision';
$string['excludeshortnames'] = 'Exclude courses';
$string['excludeshortnames_help'] = 'If set, courses with a shortname existing in the list will be excluded form supervision.
Accepts a coma separated list of course shortnames';

$string['enablemail'] = 'Enable mailing of detected pending failures';
$string['enablestats'] = 'Enable pending stats';
$string['editholidays'] = 'Manage holidays';
$string['editsupervisor'] = 'Define Supervisor\'s permissions';
$string['errorolddate'] = 'Dates before tomorrow are not allowed';
$string['excludedcategories'] = 'Excluded categories';

$string['duration'] = 'Days of vacation';
$string['holidaysedit'] = 'Edit a vacation';
$string['holidayduration'] = 'Vacation duration (days)';
$string['holidayname'] = 'Vacation name';
$string['holidayscope'] = 'Scope of vacation';
$string['holidays'] = 'Holidays';
$string['holidaystable'] = 'Holidays Table';
$string['insertholiday'] = 'Insert vacation dates';
$string['deleteholiday'] = 'Delete vacation entry';
$string['deleteholidayconfirm'] = 'You are about to delete vacation entry named "{$a}"';
$string['deletedholiday'] = 'Deleted entry named "{$a}"';
$string['type'] = 'Type';

$string['startdisplay'] = 'Default visibility date';
$string['startdisplay_help'] = 'Only records created after this date will be showed initially<br />Date format is ISO 8601 format year-month-day (or any valid strtotime() input)';
$string['coordemail'] = 'Enable mailing to supervisor addresses';
$string['coordemail_help'] = 'If enabled, in addition to <i>enablemail</i>, then mail mesasges will be sent to supervisors in addition to user';
$string['pendingmail'] = 'Copy address';
$string['pendingmail_help'] = 'This address will receive a copy of all pending duties warning messages';
$string['supervisionwarnings'] = 'Supervision warnings';
$string['review'] = 'Review';
$string['assigner'] = 'Assigner';
$string['enabledepartments'] = 'Enable supervision by Departments';
$string['configenabledepartments'] = '
In addition to group courses by category, grouping by course department will be allowed. Department supervisors will look after departamental courses across categories.
Course departments are a custom addition at ULPGC, do NOT enable if there is no "department" field in course table. <br />
Activating this setting will automatically look for supervisors at Departments table.';
$string['enablefaculties'] = 'Enable supervision by Faculties';
$string['configenablefaculties'] = '
Activating this setting will automatically look for supervisors at Centres table. <br />
Courses will be grouped by categories using Faculty field on course_categories table. Faculty supervisors will look after all courses in the category.
Faculty is a custom addition at ULPGC, do NOT enable if there is no "faculty" field in course_categories table.';

$string['save'] = 'Save changes';
$string['bycategory'] = 'Supervisors by category';
$string['bydepartment'] = 'Supervisors by department';
$string['itemname'] = 'Supervised item';
$string['addpermission'] = 'Add supervising permission';
$string['addusersetting'] = 'Allow this user to add new supervisors?';
$string['adduser'] = 'Can add';
$string['supervisors'] = 'Supervisors';
$string['itemscope'] = 'Supervision scope';
$string['itemfilter'] = 'Item filter';
$string['permissionexists'] = 'This user already has assigned supervision permissions on this item. < br/>Please, update existing permissions rather than adding a new ones. ';
$string['deletepermission'] = 'Delete supervision permission';
$string['deletepermission_confirm'] = 'You are about to delete the supervision premissions of {$a->user} on item {$a->name}. ';
$string['maildelay'] = 'Mail delay';
$string['maildelay_help'] = '
A delay between raising supervision flag  and the start of sending warning mails to users, in <b>DAYS</b>.';

$string['warningemailtxt'] = 'Estimado profesor:
Se ha detectado un aviso de incidencia en el curso {$a->coursename}

Este aviso ha sido generado en la actividad {$a->activity} {$a->student}

Puede ver más detalles en el Informe de Supervisión de actividades pendientes
{$a->reporturl}

Por favor, recuerde los plazos establecidos.
Este es un mensaje automático que continuará enviándose mientras persista la situación. No responda este mensaje.
';
$string['warningemailhtml'] = 'Estimado profesor: <br />
Se ha detectado un aviso de incidencia en el curso <a href="{$a->courseurl}">{$a->coursename}</a><br /><br />

Este aviso ha sido generado en la actividad <a href="{$a->itemlink}">{$a->activity}</a> {$a->student} <br />

Puede ver más detalles en el <a href="{$a->reporturl}">Informe de Supervisión de actividades pendientes</a><br /><br />

Por favor, recuerde los plazos establecidos. <br />
Este es un mensaje automático que continuará enviándose mientras persista la situación. No responda este mensaje.
';
$string['warningsmalltxt'] = 'Supervision alerts on {$a}';
$string['warningmailsubject'] = '[{$a}]: Aviso de actividad pendiente ';
$string['warningautomatic'] = 'Monitorización de actividades pendientes';
$string['emailstudent'] = '  con respecto al estudiante {$a->fullname} con DNI {$a->idnumber} ';
$string['mailstats'] = 'Mails pending supervision warnings';
$string['updatesupervisors'] = 'Updates supervisors from sinculpgc';
$string['synchsupervisors'] = 'Update supervisors';
$string['synchsupervisors_help'] = 'If enabled, supervisors will be synchronized from sinculpgc units table.';
$string['maildebug'] = 'Mail debug copy';
$string['maildebug_help'] = 'When enabled, the email below will receive a copy of all messages.';
$string['warningdigestsubject'] = 'Resumen de Monitorización de actividades pendientes';
$string['warningdigesttxt'] = 'Notificaciones remitidas por usuario / asignatura.';
$string['errorsubject'] = 'Errores en Monitorización de actividades pendientes';
$string['failuresubject'] = 'Fallos en envío de Notificaciones de actividades pendientes.';
$string['messageprovider:supervision_warning'] = 'Recordatorios de Supervisión de actividades pendientes';
