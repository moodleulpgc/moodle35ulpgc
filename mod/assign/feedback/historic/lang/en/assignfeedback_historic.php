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
 * Strings for component 'assignfeedback_historic', language 'en'
 *
 * @package   assignfeedback_historic
 * @copyright 2014 Enrique Castro, ecastro  @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['default'] = 'Enabled by default';
$string['default_help'] = 'If set, this feedback method will be enabled by default for all new assignments.';
$string['enabled'] = 'Feedback Historic';
$string['enabled_help'] = 'If enabled, the marker can leave feedback Historic for each submission. ';
$string['pluginname'] = 'Feedback Historic';
$string['historic:manage'] = 'Gestionar plugin Historic';
$string['historic:view'] = 'View Históric data';
$string['historic:submit'] = 'Submit Historic data';

$string['exams'] = 'Exams';
$string['activities'] = 'Activities';
$string['practice'] = 'Practice';
$string['attendance'] = 'Attendance';

$string['agespan'] = 'Previous annualities';
$string['agespan_help'] = 'Num. of previous annualities that will be displayed in plugin summary,
in addition to current one.';

$string['managedatatypes'] = 'Manage grade types';
$string['managedatatypes_help'] = '
A Grade type is each different kind of grade item that Historic plugin may store.
You can add/delete/edit new Grade types in the table.';
$string['delete_confirm'] = 'You are about to delete Grade type {$a} <br />
Do you want to proceed?';
$string['annuality'] = 'Annuality';
$string['annuality_help'] = 'The current annuality. This must be a two-year code like any of 201314, 2013-14, 2013/14, 2013-2014';
$string['grade'] = 'Grade';
$string['comment'] = 'Comment';
$string['datatype'] = 'Grade type';
$string['datatype_help'] = '
A Grade type is each different kind of grade item that Historic plugin may store.

For each Grade type you need to introduce an internal codename (alphanunmeric single word, 30 chars max.)
and a display name that will be shown to users in interface tables.';
$string['datatypeadd'] = 'Add new Grade type';
$string['datatypeupdate'] = 'Update Grade type';
$string['maxlengtherror'] = 'The input should be less that 255 chard in length.';
$string['datatypes'] = 'Grade types';
$string['datatypes_help'] = 'Actual grade types used/stored by this plugin. This setting is sitewide. <br />You may add/edit/delet new Grade type entries using the link ';
$string['export'] = 'Export Historic';
$string['downloadexport'] = 'Download Historic data file';
$string['exportfile'] = 'Grade Historic';
$string['import'] = 'Import Historic';
$string['importhistoric'] = 'Import to Historic';
$string['copyfrom'] = 'Copy grades as Historic';
$string['copyto'] = 'Copy historic to other grades';
$string['batchoperationconfirmcopyfrom'] = 'Copy grades from other assignment as current Historic?';
$string['batchoperationconfirmcopyto'] = 'Copy data in this Historic into grades of other assignment?';
$string['batchcopyfrom'] = 'Copy assign grades as Historic for multiple users';
$string['batchcopyfromforusers'] = 'Copy assign grades as Historic for {$a} selected user(s).';
$string['selectedusers'] = 'Selected users';
$string['copygrade'] = 'Copy grades';
$string['pass'] = 'Pass';
$string['fail'] = 'Fail';
$string['override'] = 'Override';
$string['override_help'] = 'If active, then copied/imported data will overwrite currently existing data.';
$string['withcomment'] = 'Copy feedback comment too. ';
$string['copyfromcopied'] = 'Copied assessments as Historic data for {$a} users.';
$string['uploadcsvfile'] = 'Upload CSV file';
$string['uploadcsvfile_help'] = 'Upload CSV file.

Data can be imported to the Grade Historic by uploading a CSV file.
Required CSV columns are user "idnumber", "datatype" and grade.

The best way to be certain about file structure is by first downloading an Export file from Historic,
filling data as appropiate and then uploading the file.

Please, relize that any importing takes place on <strong>current annuality</strong>.
Stored data corresponding to any other annualities are not modified.
Actually any column indicating annuality is <strong>NOT</strong> read by the importer,
all data in the file are considered related to current annuality.
';
$string['uploadtableexplain'] = 'This is a preview of the first records in the CSV file you are about to upload.
Please, check if the system is interpreting correctly the file structure and data.';
$string['uploadconfirm'] = 'Do you want to proceed with CVS uploading?';
$string['numimported'] = 'Uploaded {$a} data rows from file';
$string['setdefault'] = 'Set Historic done';
$string['setdefaultconfirm'] = '
This action will set a record in the database indicating that the Historic has been completed for this course.
';

$string['uploadlink'] = 'Upload Historic data';
$string['uploadlink_help'] = 'Data on Historic table can be updated from an external CSV source file. <br />
Imported data must contain annuality code, course and user idnumbers data type and grade/comment for historic.';
$string['updatedata'] = 'Update Historic';
$string['updatecsvfile'] = 'The first row in file must contain the column header identifiers. The valid and required fields are
(with this exact wording): <br />
<ul>
    <li>"annuality"      : Annuality code.</li>
    <li>"courseidnumber" : course IDnumber.</li>
    <li>"useridnumber"   : user IDnumber.</li>
    <li>"datatype"       : the code name of existing data types</li>
    <li>"grade"          : a grade number, will be read as a float.</li>
    <li>"comment"        : a short text.</li>
</ul>
"Comment" field is optional and can be omitted. All other fields are required. Any other column will be ignored.
';

$string['updatelink'] = 'Update Historic assignments from Historic table';
$string['updatelink_help'] = 'When data on Historic data table is loaded by other ways, this routine will update Historic assignmenst to use new data in Historic table.';
$string['numupdated'] = 'Updated historic for {$a} users';
$string['useridnumber'] = 'User ID number';
$string['courseidnumber'] = 'course ID number';
$string['nodata'] = 'No user data to collect or download';

// events
$string['eventhistoric'] = 'Feedback Historic tool';
$string['eventhistoricset'] = 'Historic set';
$string['eventgradescopied'] = 'Historic grades copied for multiple users';
$string['eventhistoricimported'] = 'Historic grades imported';
$string['eventhistoricexported'] = 'Historic grades exported';
