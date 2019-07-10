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
 * @package     mod_videolib
 * @category    string
 * @copyright   2018 Enrique Castro @ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addmappinglink'] = 'Add new entry';
$string['annuality'] = 'Annuality';
$string['backtovideo'] = 'Back to video';
$string['chooseavariable'] = 'Choose a variable...';
$string['configdisplayoptions'] = 'Select all options that should be available, existing settings are not modified. Hold CTRL key to select multiple fields.';
$string['configrolesinparams'] = 'Enable if you want to include localized role names in list of available parameter variables.';
$string['configsecretphrase'] = 'This secret phrase is used to produce encrypted code value that can be sent to some servers as a parameter.  The encrypted code is produced by an md5 value of the current user IP address concatenated with your secret phrase. ie code = md5(IP.secretphrase). Please note that this is not reliable because IP address may change and is often shared by different computers.';
$string['confirmdelmessage'] = 'You are about to delete {$a} entries located by these parameters';
$string['confirmdelete'] = 'Confirm deletion?';
$string['defaultfilename'] = 'videolib_mapping';
$string['del'] = 'Delete';
$string['delmappinglink'] = 'Delete entries';
$string['displayoptions'] = 'Available display options';
$string['displayselect'] = 'Display';
$string['displayselect_help'] = 'This setting, together with the URL file type and whether the browser allows embedding, determines how the URL is displayed. Options may include:

* Automatic - The best display option for the URL is selected automatically
* Embed - The URL is displayed within the page below the navigation bar together with the URL description and any blocks
* Open - Only the URL is displayed in the browser window
* In pop-up - The URL is displayed in a new browser window without menus or an address bar
* In frame - The URL is displayed within a frame below the navigation bar and URL description
* New window - The URL is displayed in a new browser window with menus and an address bar';
$string['displayselectexplain'] = 'Select display type.';
$string['entryadded'] = 'Added one video key entry';
$string['entryupdated'] = 'Updated video key entry';
$string['dberror'] = 'Video key operation NOT performed due to database error.';
$string['eventmappingdeleted'] = 'Deleted source mapping';
$string['eventmappingdownloaded'] = 'Downloaded source mapping';
$string['eventmappingmapped'] = 'Mapped source mapping';
$string['eventmanageviewed'] = 'Viewed source mapping table';
$string['export'] = 'Export';
$string['exportfilename'] = 'Downloaded file name';
$string['exportformatselector'] = 'Data format';
$string['idnumbercat'] = 'Category idnumber';
$string['import'] = 'Import';
$string['manage'] = 'Manage Video sources';
$string['managevideolibsources'] = 'Manage Video sources';
$string['managedel'] = 'Delete video source entries';
$string['managedel_help'] = 'You can enter search terms to find and delete multiple video sources';
$string['manageexport'] = 'Export Video sources mapping';
$string['manageexport_help'] = 'Allow to download a file copy of selected records from the Video sources mapping table';
$string['manageimport'] = 'Import Video sources mapping';
$string['manageimport_help'] = 'Allow to upload a CSV file with records corresponding to a mapping of keys and remote IDs.

The file must have a first line with 4 colums headers videolibkey,source,annuality,remoteid 

';

$string['manageview'] = 'Remote videos ID mapping';
$string['manageview_help'] = 'Manage the remote videos ID mapping table';
$string['mapping'] = 'Instance mapping';
$string['mappingdeleted'] = 'Deleted {$a} entries in video source mapping table';
$string['modulename'] = 'Video library';
$string['modulename_help'] = 'The Video library module enables a teacher to provide a video stored in a library  as a course resource. 
Where possible, the file will be displayed within the course interface; 

A Video library may be used to share presentations given in class';
$string['modulename_link'] = 'mod/videolib/view';
$string['modulenameplural'] = 'Video libraries';
$string['page-mod-videolib-x'] = 'Any Videlo library module page';
$string['parameterinfo'] = 'parameter=variable';
$string['parametersheader'] = 'Parameters';
$string['parametersheader_help'] = 'Some internal Moodle variables may be used in the pattern search.';
$string['pluginadministration'] = 'Video library administration';
$string['pluginname'] = 'Video Library';
$string['popupheight'] = 'Pop-up height (in pixels)';
$string['popupheightexplain'] = 'Specifies default height of popup windows.';
$string['popupwidth'] = 'Pop-up width (in pixels)';
$string['popupwidthexplain'] = 'Specifies default width of popup windows.';
$string['printheading'] = 'Display page name';
$string['printheadingexplain'] = 'Display page name above content?';
$string['printintro'] = 'Display page description';
$string['printintroexplain'] = 'Display page description above content?';
$string['privacy:metadata'] = 'The Video Library plugin does not store any personal data.';
$string['remoteid'] = 'Remote ID';
$string['removebefore'] = 'Remove all before importing';
$string['removebefore_help'] = 'If checked then all entries will be deleted prior to importing new ones ';
$string['removebeforeexplain'] = 'Check to delete all before importing';
$string['rolesinparams'] = 'Include role names in parameters';
$string['rownum'] = '#';
$string['searchpattern'] = 'Search pattern';
$string['searchpattern_help'] = 'Search pattern';
$string['searchtype'] = 'Search type';
$string['searchtype_help'] = 'How the video will be located within the library. 
May be one of:

 * Instance ID: a single number or code that uniquely identifies the video in the library.

 * Pattern: a pattern constructed with some variables than take values form course paramenters below.

';
$string['searchtype_id'] = 'Instance ID';
$string['searchtype_pattern'] = 'Pattern';

$string['separator'] = 'Separator';
$string['separatorexplain'] = 'A character that encloses the variable parameter name, for instance #shortname#';
$string['serverurl'] = 'Server url';
$string['settings'] = 'General settings';
$string['source'] = 'Video library';
$string['source_help'] = 'Video library';
$string['sourceheader'] = 'Video source';
$string['updateonimport'] = 'Update on import';
$string['updateonimport_help'] = 'What to do when the imported data already exists in the video mapping. 

If update is checked then the remote ID in the CSV fil will replace the existing one. 

Uncheck to ignore new values ad keep existing remote IDs
';
$string['updateonimportexplain'] = 'Check to update existing elements';

$string['videolibsourceplugins'] = 'Video source plugins';
$string['videolib:addinstance'] = 'Add a new Video library instance';
$string['videolib:view'] = 'View a Video library resource';
$string['videolib:edit'] = 'Edit Video library module options';
$string['videolib:manage'] = 'Manage Video library source mapping';
$string['videolib:download'] = 'Download Video library source mapping';
$string['videolibkey'] = 'Video pattern key';
$string['view'] = 'Manage';
