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
 * @package     mod_library
 * @category    string
 * @copyright   2019 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addfiles'] = 'Add files to repository {$a}';
$string['addfiles_help'] = 'The files added to the box will be uploaded to the repository in the folder specified above. 

You may add folders in the box, they will be added relative to the above main path.';
$string['addfilesdone'] = '{$a} files added to repository';
$string['chooseavariable'] = 'Choose a variable...';
$string['clicktodownload'] = 'Click {$a} link to download the file.';
$string['clicktoopen'] = 'Click {$a} link to view the file.';
$string['configdisplayoptions'] = 'Select all options that should be available, existing settings are not modified. Hold CTRL key to select multiple fields.';
$string['configrolesinparams'] = 'Enable if you want to include localized role names in list of available parameter variables.';
$string['configsecretphrase'] = 'This secret phrase is used to produce encrypted code value that can be sent to some servers as a parameter.  The encrypted code is produced by an md5 value of the current user IP address concatenated with your secret phrase. ie code = md5(IP.secretphrase). Please note that this is not reliable because IP address may change and is often shared by different computers.';
$string['delfiles'] = 'Delete files from repository {$a}';
$string['delfiles_help'] = 'Selected files will we removed from the repository. 

Select the files you may want to delete by picking them to the box using the appropiate repository. 
You may add several files, from different folders. 

The box contains a flat list but files keep record of the original folder where they where placed, and deleted there.';
$string['delfilesdone'] = '{$a} files removed from repository';
$string['display'] = 'Display mode';
$string['displayheader'] = 'Document Display';
$string['displaymode'] = 'Library show mode';
$string['displaymode_help'] = 'How the documents in the library will be located and served. may be one of:

 * File: a single file will be located and showed.
 * Folder: a single folder will be located and all files inside showed.
 * Tree: a tree of single folder will be showed.

';
$string['displayoptions'] = 'Available display options';
$string['displayselect'] = 'Display';
$string['displayselect_help'] = 'This setting, together with the URL file type and whether the browser allows embedding, determines how the URL is displayed. Options may include:

 * Automatic - The best display option for the URL is selected automatically
 * Embed - The URL is displayed within the page below the navigation bar together with the URL description and any blocks
 * Open - Only the URL is displayed in the browser window
 * In pop-up - The URL is displayed in a new browser window without menus or an address bar
 * In frame - The URL is displayed within a frame below the navigation bar and URL description
 * New window - The URL is displayed in a new browser window with menus and an address bar 
 
 ';
$string['displayselectexplain'] = 'Select display type.';
$string['enabled'] = 'Enabled';
$string['enabled_help'] = 'If checked, the plugin will be used as a Document Library source.';
$string['eventlibrarymanaged'] = 'Library manage page viewed';
$string['eventlibraryfilesadded'] = 'Library Repository files added';
$string['eventlibraryfilesdeleted'] = 'Library Repository files deleted';
$string['filenotfound'] = 'There is no item matching the specified pattern ({$a}) in the Document Library';
$string['idnumbercat'] = 'Category idnumber';
$string['insertpath'] = 'Adding folder';
$string['insertpath_help'] = 'Path within the repository where the files will be added. 
The input may contain other folder paths, all relative to this one.';
$string['library:view'] = 'View Library instance content';
$string['library:addinstance'] = 'Add a new Library instance';
$string['library:edit'] = 'Manage Library instance configuration options';
$string['library:manage'] = 'Manage Library sources';
$string['libraryname'] = 'Name of library document';
$string['librarysourceplugins'] = 'Library source plugins';
$string['managelibrarysources'] = 'Manage Library sources';
$string['managelibrary'] = 'Manage Library';
$string['managefiles'] = 'Add files';
$string['manageconfig'] = 'Global settings';
$string['modetree'] = 'Tree';
$string['modulename'] = 'Document Library';
$string['modulename_help'] = 'The Document library module enables a teacher to provide a file stored in a library as a course resource. 
Where possible, the file will be displayed within the course interface; 

A Document library may be used to share institutional manuals or textbooks used in class';
$string['modulename_link'] = 'mod/library/view';
$string['modulenameplural'] = 'Document Libraries';
$string['page-mod-library-x'] = 'Any Library module page';
$string['parameterinfo'] = 'parameter=variable';
$string['parametersheader_help'] = 'Some internal Moodle variables may be used in the pattern search.';
$string['pathname'] = 'Path';
$string['pathname_help'] = 'Path to documents, if several folders in the repository';
$string['pluginname'] = 'Document Library';
$string['pluginadministration'] = 'Library administration';
$string['popupheight'] = 'Pop-up height (in pixels)';
$string['popupheight_help'] = 'Specifies default height of popup windows.';
$string['popupwidth'] = 'Pop-up width (in pixels)';
$string['popupwidth_help'] = 'Specifies default width of popup windows.';
$string['printintro'] = 'Display page description';
$string['printintro_help'] = 'If checked, the activity description is displayed before the content files.';
$string['printintroexplain'] = 'Display page description above content?';
$string['privacy:metadata'] = 'The Document Library plugin does not store any personal data.';
$string['renameold'] = 'Rename existing file';
$string['renamenew'] = 'Rename new file';
$string['repository'] = 'Repository type';
$string['repository_help'] = 'The source Repository type for the documents in this Library';
$string['repositoryheader'] = 'Document source';
$string['repositoryname'] = 'Repository name';
$string['repositoryname_help'] = 'The the name of a particular instance of the repository, if several available';
$string['rolesinparams'] = 'Include role names in parameters';
$string['searchpattern'] = 'File name pattern';
$string['searchpattern_help'] = 'A pattern to be matched by files or folders in the Document Library';
$string['search:activity'] = 'Choice - activity information';
$string['separator'] = 'Separator';
$string['separatorexplain'] = 'A character that encloses the variable parameter name, for instance #shortname#';
$string['serverurl'] = 'Server url';
$string['settings'] = 'General settings';
$string['updatemode'] = 'Updating mode';
$string['updatemode_help'] = 'Behavior if the added file already exists in the repository. 

It may be one of four options: 

* Overwrite existing file: new added file wil replace the existing one. 
* Rename old file: The existing file will be renamed and the new one added with the current name. 
* Rename new file: The existing file will be kept with the same name and the new one added with a new name. 
* Keep file, No updating: the existing file is kept and new one NOT added to the repository.
';
$string['update'] = 'Overwrite existing file';
$string['updateno'] = 'Keep file, No updating';
