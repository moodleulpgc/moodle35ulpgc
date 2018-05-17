<?php


/**
 * Strings for component 'enrol_miulpgc', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    enrol
 * @subpackage sinculpgc
 * @copyright  2014 Víctor Déniz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Sincronización ULPGC';
$string['pluginname_desc'] = 'Sincroniza las matrículas de los usuarios del Campus virtual de la ULPGC con la base de datos corporativa';
$string['dbencoding'] = 'Database encoding';
$string['dbhost'] = 'Database host';
$string['dbhost_desc'] = 'Type database server IP address or host name';
$string['dbname'] = 'Database name';
$string['dbpass'] = 'Database password';
$string['dbsybasequoting'] = 'Use sybase quotes';
$string['dbsybasequoting_desc'] = 'Sybase style single quote escaping - needed for Oracle, MS SQL and some other databases. Do not use for MySQL!';
$string['dbtype'] = 'Database driver';
$string['dbtype_desc'] = 'ADOdb database driver name, type of the external database engine.';
$string['dbuser'] = 'Database user';
$string['remoteenroltable'] = 'Remote user enrolment table';
$string['remoteenroltable_desc'] = 'Specify the name of the table that contains list of user enrolments. Empty means no user enrolment sync.';
$string['settingsheaderdb'] = 'Conexión con base de datos externa';

$string['location'] = 'File location';
$string['location_help'] = 'Path to enrol file location. This must be subdirectory of moodledata. Give de path from <i>dataroot/</i>. Please, ensure that this directory is writable by moodle. ';
$string['pattern'] = 'File pattern';
$string['pattern_help'] = 'Pattern to match files in the given location,  as shell/gclib rules. For instance, <i>\.txt</i> or <i>tt\o.csv</i> ';
$string['executeat_help'] = 'The  time in day that the cron will start to synchronize enrolments';
$string['mapping'] = 'MiULPGC file enrol mapping';
$string['filelocked'] = 'The text file you are using for file-based enrolments ({$a}) can not be deleted by the cron process.  This usually means the permissions are wrong on it.  Please fix the permissions so that Moodle can delete the file, otherwise it might be processed repeatedly.';

$string['sinculpgc:config'] = 'Configure miulpgc enrol instances';
$string['sinculpgc:enrol'] = 'Enrol users';
$string['sinculpgc:manage'] = 'Manage user enrolments';
$string['sinculpgc:unenrol'] = 'Unenrol users from the course';
$string['sinculpgc:unenrolself'] = 'Unenrol self from the course';
