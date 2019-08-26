<?php
/**
 * Strings for component 'tool_batchmanage', language 'en', branch 'MOODLE_22_STABLE'
 *
 * @package    tool
 * @subpackage batchmanage
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Batch management tools';
$string['batchmanage'] = 'Batch management tools';
$string['managejobsettings'] = 'Batch management tool settings ';
$string['managejobs'] = 'Manage batch jobs';

$string['batchmanage:apply'] = 'Apply batch management job';
$string['batchmanage:manage'] = 'Manage batch management jobs';



$string['errorpluginnotfound'] = 'Manage job plugin named "{$a}" not found';

$string['coursesettings'] = 'Course selection criteria';
$string['coursecategories'] = 'Into courses of categories: ';
$string['coursecategories_help'] = '
Course categories defined in the system. You may select several or ALL categories by marking them.';
$string['coursevisible'] = 'Course visibility: ';
$string['hidden'] = 'Hidden';
$string['department'] = 'Department';
$string['term'] = 'Semester';
$string['term00'] = 'Anual';
$string['term01'] = 'First Semester';
$string['term02'] = 'Second Semester';
$string['ctype'] = 'Course type';
$string['coursetoshortnames'] = 'Specific shortnames';
$string['coursetoshortnames_help'] = '
Comma separated list of course shortnames, without spaces.';
$string['courseidnumber'] = 'Course IDnumber pattern';
$string['courseidnumber_help'] = '
You can specify a pattern to match course IDnumber field.

The search will use SQL LIKE features, you must include wilcards in the pattern if you want to use them.';
$string['coursefullname'] = 'Course fullname pattern';
$string['coursefullname_help'] = '
You can specify a pattern to match course fullname field.

The search will use SQL LIKE features, you must include wilcards in the pattern if you want to use them.';
$string['nonzero'] = 'non zero';
$string['credit'] = 'Courses with credits';
$string['reviewconfirm'] = 'Review & confirm';

$string['courses_selector'] = 'Select courses to operate';
$string['scheduledtask'] = 'Set the job to execute at this time';
$string['scheduledat'] = 'Job scheduled to execute on {$a}';
$string['eventjobdone'] = 'Management job executed';
$string['modify'] = 'Modificar ajuste';
$string['actbatchmanageshdr'] = 'Available management jobs';
$string['configbatchmanageplugins'] = 'Enable/Disable and organize the plugins as desired';

$string['adminincluded'] = 'Include';
$string['adminexcluded'] = 'Exclude';
$string['adminonly'] = 'Admin only';

$string['applymodconfig'] = 'Apply module config';
$string['applymodconfig_help'] = '
Allows to specify module configuration settings in a form and then apply those setting values to modules containes in courses selected in a second form.

Course selection based on category, visibility and other properties';
$string['modselectorsettings'] = 'Module selection details';

$string['modconfigsettings'] = 'Settings to apply';
$string['modname'] = 'Module name: ';
$string['instancename'] = 'Instance name: ';
$string['instancename_help'] = 'Name of the module instance you want to modify <br />(verbatim, including HTML tags).<br />
May use SQL LIKE wildcards if next option checked. You need to explicty include "%" or "_" wildcards. ';
$string['uselike'] = 'Use SQL LIKE for name search';
$string['uselike_help'] = '
If enabled, then the above term will allow SQL search wildcards like "%" and "_".';

$string['instanceid'] = 'Module instance IDs ';
$string['instanceid_help'] = '
Comma separated list of module instance ID values as existing in prefix_module DB table';
$string['modinstanceid'] = 'Module instance IDs ';
$string['modinstanceid_help'] = '
Comma separated list of module instance ID values as existing in prefix_module DB table';
$string['modcoursemoduleid'] = 'Course module IDs ';
$string['modcoursemoduleid_help'] = '
Comma separated list of course module ID values as existing in prefix_course_modules DB table and shown in url addresses (...view.php?id=xxx).';
$string['modvisible'] = 'Instance visibility';
$string['modidnumber'] = 'Instance grade ID number';
$string['insection'] = 'Course section containing the instance';
$string['modindent'] = 'Instance indentation';
$string['adminrestricted'] = 'Select admin-restricted instances';
$string['adminrestricted_help'] = 'How admin restriction options are used.

* Include: those modules are included in the processing, as well as non-restricted modules.
* Exclude: those modules are excluded, only non-restricted modules are considered.
* Admin only: Only admin restricted (any restriction) modules are considered, non-restricted modules are excluded.
';

$string['sectionsettings'] = 'Section selection options';
$string['sectionname'] = 'Section name';
$string['sectionname_help'] = 'Name of the course section you want to modify <br />
(verbatim, including HTML tags).<br />
May use SQL LIKE wildcards if next option checked. You need to explicty include "%" or "_" wildcards. <br />
If you target sections which name is empty, please specify the word "null".
';
$string['sectioninstanceid'] = 'Course Section IDs ';
$string['sectioninstanceid_help'] = '
Comma separated list of course sections ID values as existing in prefix_cousre_sections DB table';
$string['sectioninsection'] = 'Section number within course';
$string['selectsectionconfig'] = 'Define section config';
$string['setasmarker'] = 'Set as current section';
$string['emptyform'] = 'Form empty. Need some specified data to operate';
$string['notset'] = 'Not set';
$string['referencecourse'] = 'Reference course idnumber';
$string['configreferencecourse'] = 'IDnumber of an existing course that may be used as reference or template.';
$string['notallowedwords'] = 'You have included a NON allowed word in an SQL query';
$string['nosemicolon'] = 'You have included a semicolon ";" in an SQL query';
$string['nomodule'] = 'You must select a module name';

/*
$string['eventtemplateupdated'] = 'Batch apply course template';
$string['eventcourseconfigupdated'] = 'Batch apply course config';
$string['eventsectionconfigupdated'] = 'Batch apply section config';
$string['eventmodconfigupdated'] = 'Batch apply module config';
$string['eventmoddeleted'] = 'Batch module deleted';
$string['eventsectiondeleted'] = 'Batch section deleted';
$string['eventquestionsupdated'] = 'Batch question modifications';
$string['eventdueextensionset'] = 'Batch TF due extensions set';
$string['eventgradesupdated'] = 'Batch grades updated';

$string['adminincluded'] = 'Include';
$string['adminexcluded'] = 'Exclude';
$string['adminonly'] = 'Admin only';
$string['unhideableonly'] = 'Un-hideable only';
$string['unerasableonly'] = 'Un-erasable only';
$string['adminbothonly'] = 'Both restrictions';
$string['coursecategorieshelp'] = 'You may select several or ALL categories ';
$string['applycoursetemplate'] = 'Apply course template';
$string['applycoursetemplate_help'] = '
Allows to select a backup file as template and then to remanagejob it into several courses at once.
You can specify how the backup will by applied (adding, deleting before etc.)

Course selection based on category, visibility and other properties. ';
$string['applytemplate'] = 'Apply template';
$string['applytemplate_help'] = '
Gets a backupfile and remanagejobs it onto selected courses either deleting or adding content.';
$string['applyextension'] = 'Apply duedate extension';
$string['applycourseconfig'] = 'Apply course config';
$string['applycourseconfig_help'] = '
Allows to specify course settings in a form and then apply those setting values to courses selected in a second form.

Course selection based on category, visibility and other properties';
$string['courseconfigsource'] = 'Configuration source';
$string['selectcourses'] = 'Select courses';
$string['courses'] = 'Select courses';

$string['courseconfigtemplate'] = 'Apply settings to courses';
$string['coursetosql'] = 'SQL where snippet';
$string['coursetosqlhelp'] = 'A short SQL statement tu add to WHERE clause. Should use fields names of <i>course</i> table with <i>c.</i> prefix';
$string['applysectionconfig'] = 'Apply section config';
$string['applysectionconfig_help'] = '
Allows to specify course section static properties such as name, summary or visibility.';
$string['applymodconfig'] = 'Apply module config';
$string['applymodconfig_help'] = '
Allows to specify module configuration settings in a form and then apply those setting values to modules containes in courses selected in a second form.

Course selection based on category, visibility and other properties';

$string['selectconfig'] = 'Define mod config';
$string['modconfigsettings'] = 'Settings to apply';
$string['coursessettings'] = 'Apply settings to courses';

$string['coursetemplatecourses'] = 'Courses to apply template into';
$string['coursetemplatesettings'] = 'Options when restoring the template';

$string['coursetemplatesource'] = 'Template MBZ file to use';
$string['applytemplatesource'] = 'Template MBZ file to use';
$string['applytemplatesettings'] = 'Apply template options';
$string['coursevisible'] = 'Course visibility: ';
$string['hidden'] = 'Hidden';
$string['department'] = 'Department';
$string['reviewconfirm'] = 'Review & confirm';


$string['forcedelete'] = 'Delete section modules';
$string['forcedelete_help'] = 'If set to NO, only empty sections (without modules) will be deleted. <br />
When set to YES then ALL modules in the affected sections will be permanently erased without asking further confirmation.';
$string['deletesection'] = 'Delete course sections';
$string['deletesection_help'] = '
Allows to specify a single section instance by name and/or section and delete in from a list of courses.

Course selection based on category, visibility and other properties';
$string['applysectiondelete'] = 'Apply delete sections';
$string['deletemod'] = 'Delete module instances';
$string['deletemod_help'] = '
Allows to specify a single module instance by name and/or section and delete in from a list os courses.

Course selection based on category, visibility and other properties';

$string['applymoddelete'] = 'Apply mod delete';
$string['modsettings'] = 'Module selection options';
$string['modname'] = 'Module name: ';
$string['instancename'] = 'Instance name: ';
$string['instancename_help'] = 'Name of the module instance you want to modify <br />(verbatim, including HTML tags).<br />
May use SQL LIKE wildcards if next option checked. You need to explicty include "%" or "_" wildcards. ';
$string['uselike'] = 'Use SQL LIKE for name search';
$string['uselike_help'] = '
If enabled, then the above term will allow SQL search wildcards like "%" and "_".';

$string['instanceid'] = 'Module instance IDs ';
$string['instanceid_help'] = '
Comma separated list of module instance ID values as existing in prefix_module DB table';
$string['modinstanceid'] = 'Module instance IDs ';
$string['modinstanceid_help'] = '
Comma separated list of module instance ID values as existing in prefix_module DB table';
$string['modcoursemoduleid'] = 'Course module IDs ';
$string['modcoursemoduleid_help'] = '
Comma separated list of course module ID values as existing in prefix_course_modules DB table and shown in url addresses (...view.php?id=xxx).';
$string['modvisible'] = 'Instance visibility';
$string['modidnumber'] = 'Instance grade ID number';
$string['insection'] = 'Course section containing the instance';
$string['modindent'] = 'Instance indentation';
$string['adminrestricted'] = 'Select admin-restricted instances';
$string['adminrestricted_help'] = 'How admin restriction options are used.

* Include: those modules are included in the processing, as well as non-restricted modules.
* Exclude: those modules are excluded, only non-restricted modules are considered.
* Admin only: Only admin restricted (any restriction) modules are considered, non-restricted modules are excluded.
* Unhideable only: Only un-hideable set modules are considered, other restricted or non-restricted modules are excluded.
* Unerasable only: Only un-erasable  set modules are considered, other restricted or non-restricted modules are excluded.
* Both restrictions: Only both un-hideable and un-erasable restricted modules are considered, other restricted or non-restricted modules are excluded.
';

$string['nodatachanged'] = 'No setting selected for modification';
$string['nocoursesyet'] = 'There are no courses selected';
$string['nomodulesselected'] = 'There are no modules/courses to delete';

$string['template'] = 'Template backup file: ';

$string['remanagejobgroups'] = 'Remanagejob groups';
$string['remanagejobgroupings'] = 'Remanagejob groupings';
$string['remanagejobblocks'] = 'Remanagejob blocks';
$string['remanagejobfilters'] = 'Remanagejob filters';
$string['remanagejobadminmods'] = 'Remanagejob admin restricted modules ';
$string['remanagejobkeepgroups'] = 'Keep groups and groupings ';
$string['remanagejobkeeproles'] = 'Keep roles and enrolments ';
$string['remanagejobnullmodinfo'] = 'Only into empty courses (without modules)';
$string['notemplate'] = 'No template file defined';

$string['gradecatname'] = 'Assignments of Grade category';
$string['gradecatnamehelp'] = 'Name of the Grade category aggregation the Assignments is included into<br />(verbatim, including HTML tags).';
$string['assigntfdueextension'] = 'Assignment duedate extension: TF special period ';
$string['assigntfdueextension_help'] = 'Allows to calculate and set duedate extension for assignments with TF special period rules.';
$string['assigntfdueextensionsettings'] = 'Assignment duedate extension settings ';
$string['onlyadmin'] = 'Only admin restricted items';
$string['setdueextension'] = 'Set duedate extension';

$string['updategrades'] = 'Update gradebook items';
$string['updategrades_help'] = '
Triggers module _update_grades() function, to eliminate errors due to un-updated gradebooks items .

Course selection based on category, visibility and other properties';
$string['updategradessettings'] = 'Module selection options';
$string['updategradescourses'] = 'Courses to apply update in';

$string['enabled'] = 'Enabled';
$string['disabled'] = 'Disabled';

$string['noconfigsettings'] = 'There are NO module configuration settings to apply.';

$string['releasequestions'] = 'Validate / Release questions';
$string['releasequestions_help'] = '
Allow to specify some questions and to change settings on them, on selected courses.
Questions can be marked visible/hidden, validated or remanagejobd userid from idnumbers.

';
$string['questionssettings'] = 'Questions selection options';
$string['categoryname'] = 'Questions category name';
$string['categoryname_help'] = 'Name of the question category you want to modify <br />
(verbatim, including HTML tags).<br />
May use SQL LIKE wildcards if next option checked. You need to explicty include "%" or "_" wildcards. <br />
If you target sections which name is empty, please specify the word "null".
';
$string['questionid'] = 'Questions IDs ';
$string['questionid_help'] = '
Comma separated list of question ID values as existing in prefix_question DB table';
$string['categoryparent'] = 'Category level';
$string['categorycontext'] = 'Category context';
$string['questionvisibility'] = 'Question visibility';
$string['questionhidden'] = 'Hidden question';
$string['topcategory'] = 'Only top categories';
$string['subcategory'] = 'Only sub categories';
$string['coursecategory'] = 'Contextos de curso';
$string['modcategory'] = 'Contextos de módulo';
$string['selectquestionconfig'] = 'Define question changes';
$string['questionvalidated'] = 'Validate questions';
$string['usersave'] = 'Save real user as idnumber';
$string['userremanagejob'] = 'Remanagejob real user from idnumber';
$string['questionuserdata'] = 'Manage question authors identities';


*/

