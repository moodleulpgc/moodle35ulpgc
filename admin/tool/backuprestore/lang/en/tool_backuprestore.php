<?php
/**
 * Strings for component 'tool_backuprestore', language 'en', branch 'MOODLE_22_STABLE'
 *
 * @package    tool
 * @subpackage coursetemplating
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Site Backup/Restore';

$string['backuprestore:manage'] = 'Manage site Backup/Restore';
$string['backupsettings'] = 'Multi Backup settings ';
$string['restoresettings'] = 'Multi Restore settings ';
$string['backupgroups'] = 'Include groups & groupings';
$string['backupgroups_desc'] = 'Include groups ';

$string['restoreadminmods'] = 'Restore admin modules ';
$string['restoreadminmods_desc'] = 'Restore admin modules if existing or NOT, only user custom modules';

$string['coordinatorrole'] = 'Coordinator role ';
$string['coordinatorrole_desc'] = 'Role of the teacher with prime responsabilitity on course.
Used to try to identify a single teacher(id possible) where several available.';

$string['restoremode'] = 'Restore mode ';
$string['restoremode_desc'] = 'Restore mode ';
$string['mode_general'] = 'Mode general';
$string['mode_import'] = 'Mode Import';
$string['mode_hub'] = 'Mode Hub';
$string['mode_samesite'] = 'Mode Same site';
$string['mode_automated'] = 'Mode Automated';
$string['mode_converted'] = 'Mode Converted';


$string['restoretarget'] = 'Restore target ';
$string['restoretarget_desc'] = 'Restore target ';
$string['target_new_course'] = 'New course';
$string['target_existing_deleting'] = 'Existing deleting';
$string['target_existing_adding'] = 'Existing adding';

$string['restorecategory'] = 'Restore into category: ';

$string['backupdir'] = 'Backup directory';
$string['backupdir_desc'] = 'Directory where the tool will store the backup files. Give a path relative to moodledata dir.';
$string['restoredir'] = 'Restore directory';
$string['restoredir_desc'] = 'Directory where the tool will lookup for files to restore. Give a path relative to moodledata dir';

$string['multibackup'] = 'Multibackup';
$string['multibackup_help'] = '
Multibackup will allow you to select a bunch of courses based on categories and other criteria,
and backup them to .mbz files stored in a directory.

The directory name and the settings to perform the backup operation are stored separately
in the page <i>Multi backup settings</i> and used in the process triggered here.';
$string['multirestore'] = 'Multirestore';
$string['multirestore_help'] = '
Multirestore will restore multiple courses automatically from backup archive files
(zip, mbz) stored in a defined directory in moodledata.

The directory name and the settings to perform the restore operation are stored separately
in the page <i>Multi restore settings</i> and used in the process triggered here.

There is a <i>\'testing\'</i> mode to be able to check the behaviour before proceed
with the real restore, that may be very long.';

$string['multirestoresettings'] = 'Multirestore select sources';
$string['includefiles'] = 'Include files: ';
$string['includefiles_help'] = 'A pattern match like \'*.zip\' as read by fnmatch function ';
$string['excludefiles'] = 'Exclude files: ';
$string['excludefiles_help'] = 'A pattern match like \'*.zip\' as read by fnmatch function ';
$string['maxfilesize'] = 'Max file size: ';
$string['maxfilesize_help'] = ' Enter the size in <b>Mega bytes, without units</b>. Files larger than this setting will not be processed. The will be moved apart.';
$string['filenamereplace'] = 'File name replace: ';
$string['filenamereplace_help'] = 'A replacement utility for course/file names. The target course will be selected <i>after</i> replacement.
<br>The syntax is <i>found</i>//<i>replaced</i>. ';

$string['restorecheck'] = 'Check restore';

$string['multirestorecheck'] = 'Check restore settings';
$string['multibackupcheck'] = 'Check backup settings';
$string['backupproceed'] = 'Start backing up courses';

$string['restoretesting'] = 'Testing mode: ';
$string['restoretesting_help'] = ' in testing mode the script will proceed trough all files and inform of what files would be restored or skipped. ';
$string['restoreproceed'] = 'Start courses restoring';
$string['restorenottesting'] = 'Redo without testing';
$string['restorekeepgroups_desc'] = 'Applicable only if restore target is DELETING first an existing course';
$string['restorekeeproles_desc'] = 'Applicable only if restore target is DELETING first an existing course';
$string['restoreoverwriteconf_desc'] = 'Applicable only if restore target is DELETING first an existing course';

$string['dirinfo'] = 'Directory information: ';
$string['dirinformation'] = ' {$a->sourcedir} <br />
<b>{$a->total}</b> files in directory <br />
<b>{$a->included}</b> files included in restore. <br />
<b>{$a->excluded}</b> files excluded by pattern. <br />
<b>{$a->toobig}</b> files too large for restore. <br />
<b>{$a->nonmatch}</b> files in directory not matched by incude pattern.';

$string['dirinfoduplicates'] = 'Duplicates: ';


$string['categorieshelp'] = 'Select one or several categories of courses';
$string['coursesincat'] = '<strong>{$a->courses}</strong> in category {$a->category}';
$string['backupdirnotwritable'] = 'Back up dir is not writable';


$string['applyconfig_help'] = '
Allows to specify course settings in a form and then apply those setting values to courses selected in a second form.

Course selection based on category, visibility and other properties';
$string['applycourseconfig'] = 'Apply course config';
$string['applycourseconfig_help'] = '
Allows to specify course settings in a form and then apply those setting values to courses selected in a second form.

Course selection based on category, visibility and other properties';
$string['applyconfigsource'] = 'Configuration source';
$string['applyconfigtemplate'] = 'Select courses';
$string['applyconfigftemplate'] = 'Apply settings to courses';
$string['applytosql'] = 'SQL where snippet';
$string['applytosqlhelp'] = 'A short SQL statement tu add to WHERE clause. Should use fields names of <i>course</i> table with <i>c.</i> prefix';

$string['fileexluding'] = 'Skip: Excluding file {$a} ';
$string['filetoobig'] = 'Skip: Too big file {$a} ';
$string['filenonmatch'] = 'Skip: Nonmatch file {$a} ';
$string['filenotfound'] = 'Warning: Course idnumber not found in DB for file {$a} ';
$string['fileduplicated'] = 'Warning: Duplicated, already restored course idnumber for file {$a} ';
$string['filerestored'] = 'Success: Restored file {$a} ';
$string['filenotrestored'] = 'Error: Not restored file {$a} ';

$string['configsettings'] = 'Settings to apply';
$string['selectcourses'] = 'Apply settings to courses';

$string['backupfromcourses'] = 'Courses to backup';
$string['applytemplatesettings'] = 'Options when restoring the template';
$string['applytoshortnames'] = 'Specific shortnames: ';
$string['applytoshortnameshelp'] = 'Comma separated list of course shortnames';
$string['applytemplatesource'] = 'Template MBZ file to use';
$string['applyvisible'] = 'Course visibility: ';
$string['hidden'] = 'Hidden';
$string['department'] = 'Departament';
$string['deletemod'] = 'Delete module instances';
$string['deletemod_help'] = '
Allows to specify a single module instance by name and/or section and delete in from a list os courses.

Course selection based on category, visibility and other properties';

$string['deletemodsettings'] = 'Delete module options';
$string['deletemodcourses'] = 'Courses to apply delete';
$string['delmodule'] = 'Module name: ';
$string['delinstancename'] = 'Instance name: ';
$string['instancenamehelp'] = 'Name of the instance you want to delete <br />(verbatim, including HTML tags).<br />
May use SQL LIKE wildcards if option checked ';
$string['deluselike'] = 'Use SQL LIKE for name search';
$string['delinsection'] = 'Course section containing the instance';
$string['delexcludeadmin'] = 'Do NOT delete admin-restricted instances';

$string['nodatachanged'] = 'No setting selected for modification';
$string['nocoursesyet'] = 'There are no courses selected';
$string['nomodulesselected'] = 'There are no modules/courses to delete';
$string['credit'] = 'Courses with credits';
$string['template'] = 'Template backup file: ';
$string['term'] = 'Semester';
$string['term00'] = 'Anual';
$string['term01'] = 'First Semester';
$string['term02'] = 'Second Semester';
$string['ctype'] = 'Course type';

$string['restoregroups'] = 'Restore groups';
$string['restoreblocks'] = 'Restore blocks';
$string['restorefilters'] = 'Restore filters';
$string['restoreadminmods'] = 'Restore admin restricted modules ';

$string['users'] = 'Include users';
$string['role_assignments'] = 'Include role assignments';
$string['activities'] = 'Include activities';
$string['blocks'] = 'Include blocks';
$string['filters'] = 'Include filters';
$string['comments'] = 'Include comments';
$string['completion_information'] = 'Include completion';
$string['logs'] = 'Include logs';
$string['histories'] = 'Include histories';
$string['groups'] = 'Include groups & groupings';
$string['badges'] = 'Include badges';
$string['questionbank'] = 'Include Questions Bank';

$string['prebackup'] = 'Pre-backup Cleanup';
$string['prebackup_help'] = 'Pre-backup Cleanup <br />
 * Deletes old & missing resources; <br />
 * Cleans groups tables; <br />
 * Fixes wrong discrete scales used as numeric by true numeric ones; <br />
 * Fixes sections >1000 created by old subsection module.
';
$string['prebackupactions'] = 'Pre-backup actions';
$string['prebackupgo'] = 'Perform cleanup';
$string['prebk_tables'] = 'Groups Cleanup';
$string['prebk_scales'] = 'Scales Cleanup';
$string['prebk_resources'] = 'Resources Cleanup';
$string['prebk_sections'] = 'Sections Cleanup';
$string['prebk_topicgroupsections'] = 'Format topicgroup Sections building';
$string['prebk_questionusers'] = 'Store question teacher\'s idnumbers';

$string['postrestore'] = 'Post-restore Cleanup';
$string['postrestore_help'] = 'Post-restore Cleanup <br />
 * Cleans groups tables; <br />
';
$string['postrestoreactions'] = 'Post-restore actions';
$string['postrestorego'] = 'Perform cleanup';
$string['postrst_groups'] = 'Groups tables Cleanup';
$string['postrst_questionusers'] = 'Map question teacher\'s idnumbers to ids';
$string['postrst_forced'] = 'Force update ';
$string['postrst_notforced'] = 'Keep if existing';
$string['postrst_question_tags'] = 'Update questions tags with NULL contextid';
$string['postrst_question_categories'] = 'Clean up duplicate course question categories';



