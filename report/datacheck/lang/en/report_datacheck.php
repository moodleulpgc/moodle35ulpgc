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
 * Plugin strings are defined here.
 *
 * @package     report_datacheck
 * @category    string
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['datacheck:download'] = 'Download mod data files with datacheck report';
$string['datacheck:remind'] = 'Send reminders from datacheck report';
$string['datacheck:setvalue'] = 'Bulk Set value by datacheck report';
$string['datacheck:view'] = 'View report Check data field compliance';

$string['pluginname'] = 'Data field check compliance';
$string['checkcompliance'] = 'Check compliance';
$string['checkcompliance_help'] = 'Check compliance XXX  XXXXXXXXXXX XXXXXX';
$string['downloadfiles'] = 'Download files';
$string['downloadfiles_help'] = 'Allows to download a ZIP file containing files collected from 
file or picture type fields. The ZIP may be ordered in folders by user or by a field content.';

$string['enabledcheck'] = 'Enable compliance checking';
$string['explainenabledcheck'] = 'If enabled, a link to Compliance checking tool will be showed in module settings.';
$string['enableddown'] = 'Enable files download';
$string['explainenableddown'] = 'If enabled, a link to Doenload files tool will be showed in module settings.';
$string['parsemode'] = 'User search mode';
$string['explainparsemode'] = 'How to search for users when checking compliance by field. 
The field text may be read as a course shortname/fullname or category name, or a user property ';
$string['courseroles'] = 'Course roles';
$string['explaincourseroles'] = 'Users of these roles in courses will be searched as above. 
The first user with any of these roles on exact context will be selected';
$string['categoryroles'] = 'Category roles';
$string['explaincategoryroles'] = 'Users of these roles will be searched as above. But here searched in category contex. 
The first user with any of these roles on exact context will be selected';
$string['shortname'] = 'Course shortname';
$string['fullname'] = 'Course fullname';
$string['category'] = 'Category name';
$string['short-full'] = 'Shortname-fullname combination';
$string['useridnumber'] = 'User idnumber';
$string['userfull'] = 'User fullname (firstname lastname)';
$string['userfullrev'] = 'User fullname (lastname, firstname)';

$string['checkedfieldoptions'] = 'Check records by';
$string['byuser'] = 'By user';
$string['checkby'] = 'Check by';
$string['checkby_help'] = 'How the compliance will be tested. 

    *   By user: it is expected a record (or several) for each user in the course.
    *   By a field: The dataBase should contain a record for each of the options existing in a menu/checkbox/radio data field.

';
$string['isempty'] = 'is empty';
$string['noempty'] = 'is not empty';
$string['contain'] = 'contains';
$string['checkedfield'] = 'Data field to chek';
$string['checkedfield_help'] = 'Checks if every record has a given values/status in this data field.';
$string['approved'] = 'Approved';
$string['datafield'] = 'Containing Data field';
$string['datafield_help'] = 'The above checking will only consider records with this setting.';
$string['whatrecords'] = 'Check only records ... ';
$string['userparsemode'] = 'User search mode';
$string['userparsemode_help'] = 'User search mode';
$string['complymode'] = 'Compliance';
$string['complymode_help'] = 'Which records to select and display after compliance checking.

    *   Comply: those records that DO comply with searched values.
    *   Non Comply: those records that DO NOT comply with searched values.
    *   Duplicates: those records that DO NOT comply with searched values.

';
$string['comply'] = 'Comply records';
$string['noncomply'] = 'Non comply records';
$string['duplicates'] = 'Duplicate records';
$string['checkbyerror'] = 'This option needs a selection, cannot be "none"';
$string['checkedfielderror'] = 'The checked field must be specified, cannot be "any" ';
$string['nofiles'] = 'No files to download';
$string['nofilefields'] = 'No fields of type file or picture in tha Data activity';
$string['downloadtype'] = 'What files download & how organize';
$string['downfield'] = 'Files on field';
$string['downfield_help'] = 'Files contained in the selected field for each record will be downloaded.';
$string['allfiles'] = 'Any file type';
$string['groupfield'] = 'Set in folders by';
$string['groupfield_help'] = 'The ZIP archive will organize in folders. 

One folder for each user or each option of a field used. 
All collected files will be stored in the corresponding folder. ';
$string['groups'] = 'Belonging to group';
$string['successsetvalue'] = 'Value set successfully in {$a} records.';
$string['successemail'] = '{$a->sent} reminder emails sent and {$a->errors} errors.';
$string['aboutoption'] = 'Related to {$a}. ';
$string['inrecord'] = 'On {$a}';
$string['checkedcompliance'] = 'Compliance checking results';
$string['checkedcompliance_help'] = 'The results of the compliance checking in tabular form.

You may send warning/compliment email to users or just set a fixed value in another field for all concerned records.

Just check the users/records yo u wnat to operate on and expand one of the tools below.
';
$string['returntomod'] = 'Return to activity';
$string['norecords'] = 'No results found';
$string['recordslist'] = 'Results list';
$string['sendmessage'] = 'Send message';
$string['defaultsubject'] = 'Warning about fullfilling duties in Data activity ';
$string['defaultbody'] = 'There is some record or data field input that you sould have completed and is missing.'; 
$string['checkedrecordsheader'] = 'Field content | Related users';
$string['setfield'] = 'Field to set value';
$string['setfield_help'] = 'The field selected will be updated with the specified value in all recordsa above.';
$string['setvalue'] = 'Set field value';
$string['messagesubjectbody'] = 'Message Subject & body';
$string['valueset'] = 'Value';
$string['mailfrom'] = 'Data activity check compliance';
$string['mailerror'] = 'Mail error';
$string['eventreportviewed'] = 'Datacheck report viewed';
$string['eventreportviewed'] = 'Datacheck report viewed';
$string['eventreportdownload'] = 'Downloaded files with  Datacheck report';
$string['eventreportupdated'] = 'Field value set with Datacheck report';
$string['eventreportsent'] = 'Alerts sent with Datacheck report';




































