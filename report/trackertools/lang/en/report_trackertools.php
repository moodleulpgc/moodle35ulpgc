<?php
/**
 * Cadenas de texto de la extensión de sincronización de la ULPGC
 *
 * @package report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Tracker tools';
$string['trackertools:report'] = 'Check issues';
$string['trackertools:import'] = 'Upload issues';
$string['trackertools:export'] = 'Export issues';
$string['trackertools:download'] = 'Download issue files';
$string['trackertools:manage'] = 'Manage issue data';
$string['trackertools:warning'] = 'Issue warnings to users';

// settings
$string['settings'] = 'Tracker tools settings';
$string['enabled'] = 'Enable Tracker tools interface';
$string['explainenabled'] = 'If active the Trackers will show additional tools & behaviors.';
$string['import'] = 'Import issues';
$string['import_help'] = 'Allows to create Tracker issues form a CSV text file with appropiate columns.';
$string['export'] = 'Export issues';
$string['export_help'] = 'Allow to export all or some of the issues in the Tracker as a file of defined format. ';
$string['download'] = 'Download files';
$string['download_help'] = 'Collects files contained in specified Tickets in a ZIP archive to download';

$string['loadoptions'] = 'Load options';
$string['loadoptions_help'] = 'A way to load masive ammount of options in a field.
Each option must fill a separate line.
Within a line option name & description are separated by "|", like 

<br>option1|description
<br>option2|description
<br>option3|description

Option name is optional, if not present a default name (Option) is used.

Can be used to delete multiple options if the appropiate Load mode is selected.
';

$string['fieldoptions'] = 'Field options';
$string['fieldoptions_help'] = 'Write each option in a separate line.

Each option as name|description

Name is used internally and is optional. Description is the text viewed by users. ';
$string['loadmode'] = 'Load mode';
$string['loadmode_help'] = 'Behavior of loaded lines. 

* Add new: each line in the box is added as a new entry. May duplicate existing options.
* Update existing: Updates options with the same name. 
* Delete & Add: First delete all existing options (except those used in isssues), then load as new ones. 
';
$string['loadupdate'] = 'Update existing';
$string['loadadd'] = 'Add new';
$string['loaddelete'] = 'Delete existing & Add';
$string['optionname'] = 'Option ';
$string['loadoptionssaved'] = '{$a->updated} Options updated and {$a->added} new ones added.';
$string['issuesearch'] = 'Issues';
$string['issuesearch_help'] = 'Which issues to export

 * All: all issues on this Tracker, of any status. 
 * All open: All issues in the open list
 * All open: All issues in the closed list
 * Search: those issues selected in a live search
 
';

$string['assigntasktable'] = 'Assign Developers';
$string['assigntasktable_help'] = 'The report can run a task to automatically assign a developer to selected issues. 

The issues that will be assigned to a developer <strong>must</strong> be defined, previously, as a saved tracker query';
$string['assignedtasks'] = 'Automated Developer assignations';
$string['assignquery'] = 'Search query';
$string['assignuser'] = 'Developer';
$string['addassigntask'] = 'Add new automated assignation';
$string['deletetaskconfirmed'] = 'Delete assignation';
$string['deletetask'] = 'Delete an automated assignation';
$string['deletetask_help'] = 'Delete an entry in the automated assignations table';
$string['confirmtaskdelete_message'] = 'You have asked to remove the automated assignation of user "{$a->user}" 
to issues selected by query "{$a->query}". <br /> 
Do you want to proceed? ';

$string['all'] = 'All';
$string['allopen'] = 'All Open';
$string['allclosed'] = 'All closed';
$string['search'] = 'Search';
$string['exportfields'] = 'Fields to export';
$string['fixedfields'] = 'Mandatory data';
$string['fixedfields_help'] = 'These data will be exported invariably for each record. 

You may choose to include user ID number in addition to user name for those fields concerning users.
';
$string['useridnumber'] = 'Include user ID number';
$string['optionalfields'] = 'Optional data';
$string['customfields'] = 'Optional data';
$string['usermodified'] = 'Fecha de modificación';
$string['exportcomments'] = 'Include comments';
$string['exportcomments_help'] = 'Search all text comments added by the checked user type and add data to export file.';
$string['exportfiles'] = 'Include files';
$string['exportfiles_help'] = 'Search all files in comments added by the checked user type and add the filenames to the export file.

To actually download user files use the Download tool.
';
$string['exportfileselector'] = 'File to generate';
$string['exportsort'] = 'Sorted by';
$string['exportsort_help'] = 'how the issues in the exported file will be sorted. 

In addition to the selected field, users are always ordered by last name. 
';
$string['exportfilename'] = 'Exported file name';
$string['exportformatselector'] = 'Data format';
$string['reportedbyidnumber'] = 'User ID number';
$string['assignedtoidnumber'] = 'Developer IDnumber';
$string['commentuser'] = 'User comments';
$string['commentdev'] = 'Staff comments';
$string['fileuser'] = 'User files';
$string['filedev'] = 'Staff files';
$string['contentadded'] = '[Content added on {$a}]';
$string['importedissues'] = '{$a} tickets imported';


$string['comply'] = 'Check compliance';
$string['comply_help'] = 'This tool allows an staff user yo perfom some checkings for user interaction with the ticked.
For intance, you may test if users o developers has added (o not) some comments, or files, 
or if the Ticket resolution field is fulfilled or not.
';
$string['create'] = 'Bulk issue tickets';
$string['create_help'] = 'With this tool an staff user may generate automatically multiple Tickets, each for a different user as specified..  

The bulk generated Ticket is loaded with pre-defined data, as in the regular ticket input form. 
All data is constant for all generated tickets but the "Reported by" field, that is changed for each user. ';
$string['warning'] = 'Issue alerts';
$string['warning_help'] = 'This tools allows an staff user to generate alert or warning e-mails for users.

It is possible to alert separately to reporting users or staff assigned to a ticket, o both, for each all selected Tickets.
Message subject & main text are customizable for each alert.
';
$string['setfield'] = 'Set private data';
$string['setfield_help'] = 'This tool allows an staff user to fill private Ticket fields with the specified values.

All selected tickets will be updated in bulk, setting the value for each field as specified.

';
$string['makeforusers'] = 'Ticket created for users';
$string['makeforusers_help'] = 'This box contains the ID codes identifying the users for whom an issue will be generated.

You may introduce any text, IDcode separated by any combinations of 
white space, colons ",", semicolons ";", or bars "|", in one or multiple lines. All white space will be trimmed. 

The matching field used is specified below.
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
$string['filerequiredabsent'] = 'Required file absent';
$string['nouserfile'] = 'Sin archivo coincidente';
$string['usersfound'] = 'Users found';
$string['usersnotfound'] = 'Users not found';
$string['filesnotfound'] = 'Users w/o matching file';
$string['explainmake'] = 'User IDs in input field has been matched to course users. If reported here, the user ID has been accepted as valid.';
$string['confirmmake'] = 'Do you want to proceed with bulk Ticket generation? <br />
This action has no "undo".  ';
$string['make'] = 'Issue tickets';
$string['createdissues'] = '{$a} new Tickets have been generated';
$string['notmakingnofile'] = 'Tickets will NOT be created for these users due to absence of required file.';
$string['makingnofile'] = 'Tickets will BE created for these users since file is not mandatory.';
$string['issuestooperate'] = 'Tickets to operate on';
$string['downloadtype'] = 'Download mode';
$string['downfield'] = 'Files to download';
$string['downfield_help'] = 'What files to include in de ZIP archive. 
The options are:

 * All files: any files either in the Ticket itself (fields of type "File" or "Picture") 
 or in comments belonging to that Ticket.
 * User files: files contained in comments by a user in his reported Ticket.
 * Staff files: files contained in comments by a staff in his assigned Ticket.
 * Field name: Or you may specify one of the fields of type "File" or "Picture" used in this module to download only those files. 
 
';
$string['allfiles'] = 'All files';
$string['userfiles'] = 'User files';
$string['devfiles'] = 'Staff files';
$string['groupfield'] = 'Store in Folders';
$string['groupfield_help'] = 'How files will be organized in folders within the ZIP archive. 

 * NO: No folders, all files in a single container.
 * By ticket: The ZIP will contain a folder for each ticket, with  all files related to tha ticket inside that folder.
 * By user: The ZIP will contain a folder for each separate user. All files belongin to that user, even of different tickets are stored within that folder.  
 * By staff: The same as users, but considering Staff members. 
 
';
$string['zipbyissue'] = 'By ticket';
$string['zipbyuser'] = 'By user';
$string['zipbydev'] = 'By staff';
$string['usercompliance'] = 'User activity checked';
$string['hascomments'] = 'Comments in ticket';
$string['commentsby'] = 'Added by';
$string['hasfiles'] = 'Files in ticket';
$string['filesby'] = 'Added by';
$string['hasresolution'] = 'Resolution filled';
$string['indifferent'] = 'Not checked';
$string['noempty'] = 'Present';
$string['empty'] = 'None';
$string['last'] = 'Last';
$string['any'] = 'Any one';
$string['bothusers'] = 'Both';
$string['warningoptions'] = 'Alert options';
$string['warningmailto'] = 'Alerts for';
$string['warningmailto_help'] = 'Alert or reminder e-mail messages may be issued for either reporting users or Staff members.
 
 Messages are always appended automatically with a link to the relevant user Ticket. 
 ';
$string['messagesubject'] = 'Subject';
$string['messagebody'] = 'Mesage body';
$string['defaultsubject'] = 'Alert message from Tracker activity';
$string['defaultbody'] = 'This message is related to some activities that are required in Tracker activity.';
$string['mailerror'] = 'email error';
$string['aboutissue'] = ' This message concern Ticket {$a}';
$string['warnedissues'] = '{$a} alert emails issued to users';
$string['controlemailsubject'] = 'Alerts issued in Tracker activity';
$string['controlemailbody'] = 'The following users has been notified with an email alert about activity in the Tracker module instance  ';
$string['complyissues'] = 'Complying Tickets';
$string['noissues'] = 'No issues to show';
$string['sendalert'] = 'Send alert';
$string['checked'] = 'Alert';
$string['ignoremodified'] = 'Force update';
$string['ignoremodified_help'] = 'How to behave when data in imported file applies to an existing Ticket. 

Default option (unchecked) is to preserve existing data, ignoring potential update from imported file.  
If checked, then data present in the imported file will overwrite existing data for the same ticket.
';
$string['ignoremodifiedexplain'] = ' uncheck to ignore if data already exists.';
$string['addoptions'] = 'New field options';
$string['addoptions_help'] = 'How to behave when imported data has new values for a field option, values not existing previously in the Tracker.
This is relevant to checkboxes, menus and radio buttons. Fields that can have a value from a predefined set. 

Default option (unchecked) is to ignore new field options, keeping just those existing previously.
If checked, then not-recognized values will be added to the possible options for that field.';
$string['addoptionsexplain'] = ' uncheck to ignore fields options not recognized.';
$string['userencoding'] = 'User ID';
$string['userencoding_help'] = 'The parameter used to identify users in the imported file. May be one of:
 
  * Moodle ID
  * User idnumber
  * User username

The imported values will be matched with stored values for the specified ID field.
';
$string['importmailto'] = 'Email alert';
$string['importmailto_help'] = 'If checked, then users concerned about an imported ticket, 
either students or staff members, will receive an e-mail alert about the relevant ticket.';
$string['eventreportviewed'] = 'Trackertools report viewed';
$string['eventreportdownload'] = 'Trackertools download/export issues';
$string['eventreportsent'] = 'Trackertools alerts sent';
$string['eventreportcreated'] = 'Trackertools bulk issues created/imported';
$string['eventreportupdated'] = 'Trackertools bulk update issues';
$string['eventreportloadoptions'] = 'Trackertools loaded field options';
$string['eventreporttaskassign'] = 'Trackertools automated developer assignation';
$string['eventreporttaskremove'] = 'Trackertools removal of automated dev assignation';
$string['setmodify'] = 'Modify';
$string['setissuefields'] = 'Generic items';
$string['setcustomfields'] = 'Custom items';
$string['csvnocolumns'] = 'There are no detectable data columns in the CSV file. Please revise the first row and check the separator character.';
$string['csvmissingcolumns'] = 'These mandatory columns are not in the CSV file: {$a} ';
$string['setprefs'] = 'Set preferences';
$string['mailoptions'] = 'Bulk user preferences';
$string['mailoptions_help'] = 'Set user preferences in bulk for all specified users';
$string['usertype'] = 'Users affected';
$string['usertype_help'] = 'The above user prefefrences will be set in bulk for all users of one of these groups.';
$string['forceupdate'] = 'Forced update';
$string['forceupdate_explain'] = 'If unchecked, it will NOT modify users that have already specified their own preferences.';
$string['saveduserprefs'] = 'Saved preferences for {$a} users';
$string['errorcannotsaveprefs'] = 'NOT saved preferences for users: {$a}.';
$string['nofiles'] = 'There are no files of the specified class in selected issues';
$string['confirmsearch'] = 'Confirm issue scope';
$string['confirmsearch_help'] = 'A parameter to confirm the issues the changes will be applied into. Must have the same value in both menus.';
$string['confirmsearcherror'] = 'The issues to apply changes to must be coincident';
$string['taskassigned'] = 'Created an automated developer assignation';
$string['taskdeleted'] = 'Removed an automated developer assignation';

$string['checkcompliance'] = 'Check compliance';
$string['checkedfield'] = 'Checked field';
$string['fieldcompliance'] = 'Field compliance';
$string['fieldcomply'] = 'Check Options filled';
$string['fieldcomply_help'] = '
Checks if there are entries submitted corresponding (or not) to every option in a menu field.
';
$string['fillstatus'] = 'Check absence';
$string['fillstatusexplain'] = 'If checked, the toll will find those options that DO NOT have an issue';
$string['usercomply'] = 'Check user compliance';
$string['menutype'] = 'Menu type';
$string['menutype_help'] = 'The type of data holded in teh dropdown menu. May be either:

 * Users: each line in the menu is a user, a participant (usually identified internally by ID or IDnumber).
 * Course: each line in the menu is a course (usually identified by shortname, even wehn fullname displayed).
 * Other: Other type of data without special meaning.
';

