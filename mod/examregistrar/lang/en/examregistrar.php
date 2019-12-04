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
 * English strings for examregistrar
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Exams registrar';
$string['modulenameplural'] = 'Exams registrars';
$string['modulename_help'] = 'The Exams registrar module allows to define and manage examination periods and exams.
This module holds all admnistrative functions and processes associated with examination procedures.

Managers can define examination periods and exam dates, assingning a location for exach exam.

Users can book a seat for each exam date (students) or can print the actual exam papers (teachers, staff). ';
$string['examregistrarfieldset'] = 'Custom example fieldset';
$string['examregistrarname'] = 'Exams registrar name';
$string['examregistrarname_help'] = 'The Exams registrar module allows to define and manage examination periods and exams.
This module holds all admnistrative functions and processes associated with examination procedures.

Managers can define examination periods and exam dates, assingning a location for exach exam.

Users can book a seat for each exam date (students) or can print the actual exam papers (teachers, staff). ';
$string['examregistrar'] = 'Exams registrar';
$string['examregistrarsettings'] = 'Exams registrar settings';
$string['pluginadministration'] = 'Exams registrar administration';
$string['pluginname'] = 'Exams registrar';

$string['cronruntimestart'] = 'Cron Run time';
$string['configcronruntimestart'] = 'The time in the day when the once-daily tasks will be processed.';
$string['areaexamfile'] = 'Exam files area';
$string['areaexamresponses'] = 'Exam responses file area';

$string['examregistrar:addinstance'] = 'Add a new Exams Registrar';
$string['examregistrar:updateinstance'] = 'Update an instance of Exams Registrar';
$string['examregistrar:view'] = 'Vew exams in the Registrar';
$string['examregistrar:viewall'] = 'View all exams in the Registrar';
$string['examregistrar:viewcats'] = 'Access all exams in category';
$string['examregistrar:showvariants'] = 'View Exam attempts';
$string['examregistrar:download'] = 'Download Exam files from Registrar';
$string['examregistrar:upload'] = 'Upload Exam files to Registrar';
$string['examregistrar:editelements'] = 'Edit basic elements in the Registrar';
$string['examregistrar:manageperiods'] = 'Manage periods and sessions';
$string['examregistrar:managelocations'] = 'Manage locations';
$string['examregistrar:manageseats'] = 'Manage session allocations';
$string['examregistrar:manageexams'] = 'Manage, create and edit exams in the Registrar';
$string['examregistrar:beroomstaff'] = 'Participate as room/venue staff';
$string['examregistrar:submit'] = 'Submit exam attempts for review';
$string['examregistrar:review'] = 'Review exam attempts';
$string['examregistrar:resolve'] = 'Approve/reject exam attempts';
$string['examregistrar:delete'] = 'Delete exam attempts';
$string['examregistrar:book'] = 'Book an exam for oneself';
$string['examregistrar:bookothers'] = 'Book an exam for others';
$string['examregistrar:reviewtaken'] = 'Review exam response data';
$string['examregistrar:confirmresponses'] = 'Confirm exam response files';
$string['examregistrar:uploadresponses'] = 'Upload exam response files';
$string['examregistrar:reviewtaken'] = 'Review exam response data';


$string['elementtypes'] = 'Element types';
$string['configelementtypes'] = 'A comma separated list of element type names.
Each name must be a string of up to 10 characters. The default list is:
period, scope, call, examdate, timeslot, location, role

* period: Ordinary, Extraordinary ...
* scope: midterm, partial, final ...
* call: each separate opportunity to take a given exam
* location: each type of places, city, venue, classroom etc.
* role: each role for a staffer in a location. Supervisor, Officer, ...

';

$string['configparams'] = 'Configure default params';
$string['defaultsettings'] = 'Initial values for instance config parameters';
$string['defaultsettings_help'] = 'New instances of Exam registrar that are a Primary Registrar require to set congiguration options. 
The initial default values wil be taken from here, and later may be modified from Registrar  Management.';
$string['headerdeadlines'] = 'Selection periods';
$string['headerallocation'] = 'Staff allocation';
$string['headerfilesuffix'] = 'Exam archives suffixes';
$string['headerprinting'] = 'Printing options';
$string['selectdays'] = 'Select period';
$string['selectdays_help'] = 'Days before an exam date a student can select a seat.';
$string['cutoffdays'] = 'Select deadline';
$string['cutoffdays_help'] = 'Days before an exam date a student cannot select a seat for exam at a given location.';
$string['extradays'] = 'Select extra additional';
$string['extradays_help'] = 'Additional days (plus cutoff) before an exam date a student cannot select a seat for exam at a given location in an Extra convocatory (one with many calls).';
$string['lockdays'] = 'Blockade period';
$string['lockdays_help'] = 'Days before an exam date a student cannot modify a selection for an exam seat.';
$string['approvalcutoff'] = 'Select approval deadline';
$string['approvalcutoff_help'] = 'Days before an exam date a superviser can no longer approve or reject an exam.';
$string['printdays'] = 'Print days';
$string['printdays_help'] = 'Days before an exam date an examiner can download an exam.';
$string['defaultregistrar'] = 'Default instance';
$string['configdefaultregistrar'] = 'Many functions in Exam registrar are called from other dependent modules or blocks.
Here a default course module ID must be defined for use as fallback when no other module can be defined. ';
$string['staffcategories'] = 'Staff course categories';
$string['staffcategories_help'] = 'If selected any, then when looking for potential staff members, in addition to current course,
the users enrolled on any course of those categories are checked for the appropiate capabilities.';
$string['excludecourses'] = 'Exclude admin courses';
$string['excludecourses_help'] = 'If set, the above search wil exclude users enrolled in courses without an academic charge.
Administrative and social courses are not considered.';
$string['responsesfolder'] = 'Response files folder';
$string['configresponsesfolder'] = 'Relative OS filesystem path (starting from moodledata) to the Response files folder.';
$string['sessionsfolder'] = 'Session files folder';
$string['configsessionsfolder'] = 'Relative OS filesystem path (starting from moodledata) to the Sessions files folder used to prepare ZIP archives.';
$string['distributedfolder'] = 'Distributed responses folder';
$string['configdistributedfolder'] = 'A folder name to hold exam response files once distributed to courses.';
$string['responsessheeturl'] = 'Response sheet cmID';
$string['configresponsessheeturl'] = 'If a model Responses sheet is used an exists in a course as a PDF, here can be stated the CM id for that file.';
$string['printresponsessheet'] = 'Standard Responses Sheet';
$string['defaultrole'] = 'Default role';
$string['defaultrole_help'] = 'If set, the role with this idnumber will be used when no other role were specified for staff.';
$string['venuelocationtype'] = 'Location type for Venues';
$string['venuelocationtype_help'] = 'Locations of this type will be selected as posible venues:
sites where students can book exams and rooms belong to. ';
$string['questionwarning'] = 'Exam uses un-regulated questions';

$string['useasprimary'] = 'Use as primary registrar';
$string['useasprimary_help'] = '
If set, indicates that the selected instance wil provide the primary registrar.

This means that the system will use that ID to look for  Exam elements, sessions, rooms etc. defined previously. ';
$string['thisisprimary'] = 'This instance is primary registrar';
$string['primaryidnumber'] = 'Primary ID codename';
$string['primaryidnumber_help'] = '
If this is a primray registrar, this setting will store the unique ID code that will identify the registrar.

It must be an alphanumeric code without spaces. "-" and "_" are allowed.';
$string['workmode'] = 'Use/Display mode';
$string['workmode_help'] = 'Each module instance can be configured to serve differente ussage modes or to show several different pieces on information.

* View mode:

* Booking mode:

* Print mode:

* Review mode:

';

$string['modeview'] = 'View mode';
$string['modebook'] = 'Booking mode';
$string['modeprint'] = 'Print mode';
$string['modereview'] = 'Review mode';
$string['moderegistrar'] = 'Registrar mode';
$string['annuality'] = 'Annuality';
$string['annuality_help'] = '
The course annuality code. For instance 2014-15 or 201213.';
$string['course'] = 'Course';
$string['programme'] = 'Programme';
$string['programme_help'] = '
A programme is the name of a bunch or courses that towards a degree. This option allow to indicate the code o shortname of an instituion programme. ';
$string['shortname'] = 'Course Shortname ';
$string['shortname_help'] = '
A shortname is the code assignd to a specific course or class.
';

$string['lagdays'] = 'Holding days';
$string['lagdays_help'] = 'Access to Exams my be prevented,
for instance only allowing to print exams on the day before. This setting controls the waiwer period';
$string['reviewmod'] = 'Reviewer module identification ';
$string['reviewmod_help'] = 'Reviewer module identification

Exam reviews are managed by Tracker issues. Here you can specify wich Tracker
instance will process exam reviews for this Registrar instance.

You need to enter the idnumber of course module that serves as review manager.
If there are several ones with the same ID, the module in the same course or category will be used.


';



$string['view'] = 'View exams ';
$string['review'] = 'Review status ';
$string['printexams'] = 'Print by exam ';
$string['printrooms'] = 'Print by room ';
$string['manage'] = 'Manage registrar ';
$string['session'] = 'Manage session ';

$string['delete_confirm'] = 'You are about to remove {$a->type} named {$a->name}.
Would you like to proceed?';


$string['batch_confirm'] = 'You are about to {$a->action} the following {$a->type} items:

{$a->list}

Would you like to proceed?';
$string['unknownbatch'] = 'Unknown batch action';

$string['element'] = 'Element';
$string['elements'] = 'Field Elements';
$string['addelement'] = 'Add element';
$string['updateelement'] = 'Update element';
$string['degreetype'] = 'Degree type';
$string['editelement'] = 'Edit element';
$string['editelements'] = 'Edit elements';
/*
$string['period'] = 'Exam period';
$string['periods'] = 'Exam periods & sessions';
$string['editperiods'] = 'Edit periods';
$string['editsessions'] = 'Edit sessions';
*/


$string['editexamsessions'] = 'Edit sessions';
$string['updateexamsession'] = 'Update exam session';
$string['duration'] = 'Duration';
$string['duration_help'] = 'The allocated time span for this exam session';
$string['examsessions'] = 'Exam sessions';
$string['editperiods'] = 'Edit periods';

$string['periods'] = 'Exam periods';


$string['exams'] = 'Exams';
$string['editexams'] = 'Edit exams';
$string['addexam'] = 'Add exam';
$string['updateexam'] = 'Update exam';
$string['locations'] = 'Places & Rooms';
$string['editlocations'] = 'Edit locations';
$string['editstaffers'] = 'Edit staffers';
$string['assignstaffers'] = 'Assign staffers to Room';
$string['roomassignments'] = 'Exam Rooms';
$string['seatassignments'] = 'Seat Assignment';
$string['editsessionrooms'] = 'Edit sessions rooms';
$string['assignsessionrooms'] = 'Assign session rooms';
$string['uploadcsvsessionrooms'] = 'Upload CSV session rooms';
$string['assignseats'] = 'Assign exams to rooms';
$string['assignseats_venues'] = 'Assign exams to Venues';
$string['uploadcsvseats'] = 'CSV room assignments';
$string['seatstudent'] = 'Seat student';

$string['uploadsettings'] = 'Upload settings';
$string['uploadtype'] = 'Upload operation';
$string['uploadtype_help'] = '
Upload operation may be one of

* Load CSV Elements
* Load CSV Periods
* Load CSV Sessions
* Load CSV Locations
* Load CSV Staffers
* Load CSV Session rooms
* Load CSV room assignment

';
$string['uploadcsvelements'] = 'Load CSV Elements';
$string['uploadcsvperiods'] = 'Load CSV Periods';
$string['uploadcsvexamsessions'] = 'Load CSV Sessions';
$string['uploadcsvlocations'] = 'Load CSV Locations';
$string['uploadcsvstaffers'] = 'Load CSV Staffers';
$string['uploadcsvsession_rooms'] = 'Load CSV session rooms';
$string['uploadcsvassignseats'] = 'Load CSV room assignment';
$string['uploadcsvfile'] = 'Upload CSV file';
$string['uploadcsvfile_help'] = '
Please, select a file containing CSV data suitable for importing. The first line must include the column name. Some columns are optional (may not exists in the file) while other are required.

The columns that are required depends on  upload type and are marked by *:

* Load CSV Elements:
    type*, name*, idnumber*, value, visible

* Load CSV Periods:
    name*, idnumber*, annuality*, degreetype*, periodtype*, calls*, timestart*, timeend*, visible

* Load CSV Sessions:
    name*, idnumber*, periodidnumber*, annuality*, examdate, timeslot, visible

* Load CSV Locations:
    name*, idnumber*, locationtype*, seats*, address, addressformat, parent, sortorder, visible

* Load CSV Staffers:
    examsession*, roomidnumber*, locationtype*, role*, useridnumber, info

* Load CSV session rooms:
    examsession*, locationid*, available*

* Load CSV room assignment:
    city*, num*, shortname*, fromoom*, toroom*

';
$string['ignoremodified'] = 'Ignore updates';
$string['ignoremodified_help'] = '
If set to Yes, then if the CSV contains data that already exists stored in Moodle, the changes will be discarded,
the proposed update will be ignored and the stored data remain unchanged.

If you want to effectively update stored data with newly uploaded ones, set this option to NO.';
$string['editidnumber'] = 'Add/Change idnumbers';
$string['editidnumber_help'] = '
This option is only aplicable if you have permissions to edit the registrar basic elements and labels.

If set to Yes then if a Name/IDnumber combination do not exists, it is automatically added to the database,
and the row in the CSV file containing it is further processed.

If set to NO, then if an IDnumber is not recognized, the CSV row containing it will be ignored, not uploaded at all.';

$string['elementitem'] = 'Element';
$string['csvuploadsuccess'] = 'Succesfully loaded {$a} data rows';
$string['uploadtableexplain'] = 'This is a preview of the first records in the CSV file you are about to upload.
Please, check if the system is interpreting correctly the file structure and data.';
$string['uploadconfirm'] = 'Do you want to proceed with CSV uploading?';

$string['annualityitem'] = 'Annuality';
$string['annualityitem_help'] = '
This option identifies the academic annuality to use. Choose from a menu of restricted values.';
$string['perioditem'] = 'Period';
$string['perioditem_help'] = '
This option identifies the academic Exam period to use. Choose from a menu of values set by Admins.';
$string['periodtypeitem'] = 'Period type';
$string['periodtypeitem_help'] = '
This option identifies the academic Exam period to use. Choose from a menu of values set by Admins.';
$string['examsessionitem'] = 'Session';
$string['examsessionitem_help'] = '
This option identifies the academic Exam session (day, hour) to use. Choose from a menu of values set by Admins.';
$string['scopeitem'] = 'Exam Scope';
$string['scopeitem_help'] = '
This option identifies the academic Exam scope to use. Choose from a menu of values set by Admins.';
$string['locationitem'] = 'Location';
$string['locationitem_help'] = '
This option identifies the place, venue or room to use. Choose from a menu of places set by Admins.';
$string['locationtypeitem'] = 'Location type';
$string['locationtypeitem_help'] = '
This option identifies the Site type of the assigned location. Choose from a menu of types set by Admins.';
$string['roleitem'] = 'Staff role';
$string['roleitem_help'] = '
This option identifies the Role to assign in this place to the staff user. Choose from a menu of roles set by Admins.';
$string['termitem'] = 'Term';
$string['termitem_help'] = '
This option identifies the academic Term to use. Choose from a menu of values set by Admins.';

$string['elementtypeselect'] = 'Element type to show';

$string['itemname'] = 'Item name';
$string['itemname_help'] = 'The visible name for the item';
$string['idnumber'] = 'ID code';
$string['idnumber_help'] = 'A short and UNIQUE alphanumeric ID codename for the element. Must be a string of less than 20 characters.';
$string['elementtype'] = 'Element type';
$string['elementtype_help'] = 'The element item defines the kind and purpose, where the element will be applied or used

There are several possible element items:

* Annuality: names and shortnames for the yearly identifiers, e.g "2012", "201415" or "2013-14"
* Period: names and shortnames for each separate period along the years where exams take place e.g. "Winter exams", "Summer exams"
* Period type: for instance Ordinary, Extraordinary, Remedial.
* Session: names and shortnames for each separate exam session (day & hour) within an exam period.
* Scope: names and shortnames for the exam type, e.g. "midterm", "final", "partial".
* Location: names and shortnames for related items, e.g. "Room 101", "Central Hall", "Library Building", "Toledo Office".
* Location type: names and shortnames for site types e.g. cities, venues, halls, rooms.
* Role: names and shortnames for people\'s roles associated with a location, for instance Supervisor, Instructor, Director etc.

';

$string['elementvalue'] = 'Element value';
$string['elementvalue_help'] = '
Some element types may have an associated value used to search and synchronize with codes ID codes in external systems.';
$string['sortorder'] = 'Sort order';
$string['sortorder_help'] = 'A number to indicate precedence or sorting order.
When several items of a type are displayed, they will be listed in an order determined by this sort order parameter.';


$string['save'] = 'Save';
$string['filter'] = 'Filter';
$string['clearfilter'] = 'Clear';

$string['addperiod'] = 'Add period';
$string['updateperiod'] = 'Update Period';

$string['numcalls'] = 'N calls';
$string['numcalls_help'] = '
The number of separate exam calls for each exam in this period. Must be number.

A given student must take just ONE of the calls. This is a way to allow separate exam sessions for the same course/class.';
$string['timestart'] = 'Start date';
$string['timestart_help'] = '
The date for the start of the period of exams.';
$string['timeend'] = 'End date';
$string['timeend_help'] = '
The date for the end of the period of exams.';
$string['visibility'] = 'Visible';

$string['go'] = 'Do action';
$string['withselecteddo'] = 'With selected items: ';
$string['selectall'] = 'check all';
$string['selectnone'] = 'uncheck all';

$string['addexamsession'] = 'Add session';
$string['updatesession'] = 'Update session';
$string['examdate'] = 'Exam date';
$string['examdate_help'] = '
The date this particular exam session will take place.';

$string['timeslot'] = 'Time slot';
$string['timeslot_help'] = '
The hour in tha day at witch this exam session will start.';

$string['setsession'] = 'Set session';
$string['setparent'] = 'Set parent';
$string['resetparents'] = 'Reset parent/child hierarchy';
$string['callnum'] = 'Call';
$string['callnum_help'] = '
If this Period support multiple calls, this item indicated the call nº correponding to this exam';

$string['sessionrooms'] = 'Assign rooms to session';
$string['roomstaffers'] = 'Assign staffers to room';
$string['roomstaff'] = 'Assigned staff';

$string['addlocation'] = 'Add location';
$string['updatelocation'] = 'Update location';

$string['seats'] = 'Seats';
$string['seats_help'] = '
The number of students that can be seated/allocated in the romm for an exam.';

$string['parent'] = 'Parent site';
$string['parent_help'] = '
Places and rooms may be organized hierarchically: Buildings are in cities and have venues, and venues have rooms inside.

By setting a parent a hierarchy may be created, specifiying wich rooms belong to each place.';
$string['address'] = 'Address';
$string['staffers'] = 'Staff';
$string['stafferitem'] = 'Staffer';
$string['session_rooms'] = 'Session rooms';
$string['editsession_rooms'] = 'Rooms by session';
$string['assignedrooms'] = 'Assigned Rooms for session';
$string['assignedrooms'] = 'Assigned Rooms for session';
$string['assignedroomsclearmessage'] = 'This is actually a list of ALL available rooms.<br>Be careful! This operation will delete currently
assigned rooms for this session and will be replaced by those rooms selected above.';
$string['sessionroomssettings'] = 'Session rooms settings';
$string['backto'] = 'Back to {$a}';


$string['allocatedrooms'] = 'Allocated Rooms';
$string['unallocatedexams'] = 'Unallocated Exams';

$string['room'] = 'Room';
$string['rooms'] = 'Rooms';
$string['additionalexams'] = 'Additional Exams';
$string['additionalexam'] = 'Additional Exam {$a->current} of {$a->total}';

$string['moveusers'] = 'Move ';
$string['fromexam'] = 'from Exam  ';
$string['fromroom'] = 'from room';
$string['toroom'] = 'to room';
$string['makeallocation'] = 'Make Allocation';

$string['allocateexam'] = 'Allocate exam';
$string['unallocated'] = 'Unallocated';
$string['unallocatedbooking'] = '{$a} Unallocated bookings';
$string['unallocatedyet'] = 'Unallocated yet';
$string['unallocate'] = 'Unallocate item';
$string['unallocateall'] = 'Unallocate All';

$string['refreshallocation'] = 'Start new allocation';

$string['withselectedtoroom'] = 'Allocate selected to room ';

$string['freeseats'] = ' {$a} free.';

$string['additionalusersexams'] = 'Additional exams: {$a->users} students with {$a->exams} exams.';
$string['noadditionalexams'] = 'Additional exams: none';

$string['roomprintoptions'] = 'Room PDF options';
$string['examprintoptions'] = 'Exam PDF options';
$string['userlistprintoptions'] = 'User list PDF options';
$string['bookingprintoptions'] = 'Booking PDF options';
$string['venueprintoptions'] = 'Venue PDF option';
$string['venuefaxprintoptions'] = 'Venue FAX options';
$string['printingoptions'] = 'Printing Options';
$string['printingoptionsmessasge'] = 'Here you can format the information to be showed for each room sheet.<br />
The texts may contain several lines, lists and tables, can be rich formatted using the editor.<br />
You may use %%placeholders%% to include specific data for this room or exams. Some placeholders  are:

<ul>
<li>%%registrar%% : Name of the managing module. </li>
<li>%%period%% : Exam period name. </li>
<li>%%session%% : Name of this exam session. </li>
<li>%%venue%% : Name for the venue/hall/city where the exams are allocated (A venue have several exam rooms).  </li>
<li>%%date%% : The date of the examination. </li>
<li>%%time%% : The time scheduled.  </li>
<li>%%room%% : substituted with the Room name stored. </li>
<li>%%roomidnumber%% : substituted with the Room idnumber. </li>
<li>%%address%% : Room address, if stored in the database. </li>
<li>%%seats%% : Nominal number of seats in the room. </li>
<li>%%seated%% : Number of students seated in the room (for any exam). </li>
<li>%%numexams%% : Number of separate exams (courses) allocated to this room.  </li>

<li>%%programe%% : Exam programme. </li>
<li>%%shortname%% : Course shortname for this exam. </li>
<li>%%fullname%% : Course fullname. </li>
<li>%%callnum%% : If this exam has several calls, the particular call number for thsi session. </li>
<li>%%examscope%% : Exam scope (e.g. Midterm, Final etc.) </li>
<li>%%seated%% : Number of students that booked thsi exam are are allocated in this room. </li>
<li>%%teacher%% : Name of the Teacher(s) in the course corresponding to thsi exam. </li>
</ul>
';

$string['printingbookingtitle'] = 'Booking title section';
$string['printingbookingtitle_help'] = 'This section will be located before the booking list for this exam';
$string['printingheader'] = 'Page header';
$string['printingheader_help'] = 'A simple line that will be used as page footer in small print.
Accepts explicit HTML tags for formatting.';
$string['printingfooter'] = 'Page footer';
$string['printingfooter_help'] = 'A simple line that will be used as page footer in small print. ';

$string['printingroomtitle'] = 'Room title section';
$string['printingroomtitle_help'] = 'This section will be located before the summary of room exams.

It may contain page main title and identification data for this particular room.
The text may contain several lines, lists and tables, can be rich formatted using the editor.
You may use %%placeholders%% to include specific data for this room or exams.

Below this section Moodle will include a summary list of the exams (only titles) that are allocated to this room.';
$string['printingexamtitle'] = 'Exam title section';
$string['printingexamtitle_help'] = 'Each main exam allocated to this room will have
a separate page with a summary section and a list of the student that booked to take that exam.
This box should contain the infosmation you like to show on that summary section for each particular exam.

The text may contain several lines, lists and tables, can be rich formatted using the editor.
You may use %%placeholders%% to include specific data for this exam, as course title and IDcode, teachers etc.

Below this section Moodle will include a table with the list of students
allowed to seat in this room to take this exam.';

$string['printingvenuesummary'] = 'Venue summary section';
$string['printingvenuesummary_help'] = 'The exam will be taked by many students at several locations.
Moodle will print a table summary with allocated students in each venue.

Here you can specify any additional information or text to be used as title for this summary table.

The text may contain several lines, lists and tables, can be rich formatted using the editor.
You may use %%placeholders%% to include specific data for this exam, as course title and IDcode, teachers etc.';


$string['printinglistrow'] = 'Extra fields';
$string['printinglistrow_help'] = '
The page will include a list of each student than booked an exam and has been allocated to this room. Each row will habe the student name and IDnumber,
and may content additional columns with checkboxes to tick.

Introdude the headers for each addition checkbox column, as plaintext words separated by "|" characters.
Accepts explicit HTML tags for formatting. ';
$string['printingcolwidths'] = 'Column widths';
$string['printingcolwidths_help'] = '
You may specify the relative widths for columns in the student table.
Widths are indicated as % and separated by "|" characters.
You must ensure that the relative widths add up to 100%.For instance 10%|50%|10%|30%|30% .
You must include 3 fixed colums and any additional columns defined above. ';
$string['printingadditionals'] = 'Additional exams';
$string['printingadditionals_help'] = '
Some students may need to take some extra exams, in addition to the main exam(s) allocated to teh room.
In this page Moodle will present the information for those additional exams: which students and what supplementary exams ';

$string['printinguserlisttitle'] = 'User list title section';
$string['printinguserlisttitle_help'] = 'This section will be located before the list of students allocated for exam.

It may contain page main title and identification data for this particular room.
The text may contain several lines, lists and tables, can be rich formatted using the editor.
You may use %%placeholders%% to include specific data for this room or exams.

Below this section Moodle will include a list of all users called for exam.';

$string['singleroommessage'] = 'This venue has a single room. Use "Assign exams to Venues"';
$string['stafffromexam'] = 'Room staff from exam';
$string['copyexams'] = 'Copy exams from OLD TF';
$string['generateexams'] = 'Exams from courses';
$string['generateexamssettings'] = 'Generation settings';

$string['generatemode'] = 'Generation mode';
$string['generatemode_help'] = '
How exams will be generated for each course.

 * Course based: just one per course per period
 * Assign exam instances: taking into account the instances of Exam assign plugin existing in the course.

';
$string['genexamcourse'] = 'Course based';
$string['genexamexam'] = 'Assign exam instances';

$string['genforperiods'] = 'Generate for periods';
$string['genforperiods_help'] = '
Exam instances will be added for each of the selected exam periods (one exam for each call)

';
$string['genassignperiod'] = 'Period assignment';
$string['genassignperiod_help'] = '
How an exam generated for a course will be assigned to an exam period.

 * As selected: an exam will be generated for each Period selected in the above setting.
 * Using course start date: the course start date will be aligned with Period dates and an exam generated for each selected Period the start date fits in.
 * Using course term: the course term will be determined and and an exam generated for each selected Period that corresponde to the same term.

';
$string['periodselected'] = 'Period as selected';
$string['periodfromstartdate'] = 'Using course start date';
$string['periodfromterm'] = 'Using course term';
$string['genassignprogramme'] = 'Programme assignment';
$string['genassignprogramme_help'] = '
How the exams generated will be associated to a given programme.

   * Course shortname: shortname will be used as programme.
   * Course idnumber: programme will be derived from idnumber codes.
   * Category ID: the course category ID will be used as programme.
   * Category idnumber: programme will be derived from course category idnumber codes.
   * Category degree: programme will be derived from course category degree code (ULPGC only).

';
$string['courseshortname'] = 'from course shortname';
$string['courseidnumber'] = 'from course idnumber';
$string['coursecatid'] = 'from course category ID';
$string['coursecatidnumber'] = 'from course category idnumber';
$string['coursecatdegree'] = 'from course category degree';
$string['gendeleteexams'] = 'Delete non-conforming exams';
$string['gendeleteexams_help'] = '
If there are existing exams for the course/periods selected that do not conform
to the actual generation settings, those exam instances could be deleted if appropiate.

';
$string['genupdateexams'] = 'Update existing exams';
$string['genupdateexams_help'] = '

If set, then existing entries will be updated for programme, visibility etc.

';
$string['genexamvisible'] = 'Exam visibility';
$string['genexamvisible_help'] = '
How the visibility of generated exams will be set.

Synchronize will set the exam visibility setting according to course visibility status.

';
$string['hidden'] = 'Hidden';
$string['synchvisible'] = 'Syncronize to course';

$string['generateunrecognized'] = '{$a} Exam periods not recognized.';
$string['generateunrecognizedexam'] = 'Course: {$a->shortname}; Period: {$a->periodidnumber}; Scope: {$a->scope} ';
$string['generatemodcount'] = 'Updated {$a->updated}, added {$a->added} and deleted {$a->deleted} exams in {$a->courses} courses.';

$string['student'] = 'Student';
$string['venue'] = 'Venue';
$string['venue_help'] = 'The venue associated with a session room assignation or booking';

$string['downloadroompdf'] = 'Download Room PDF';
$string['downloadexampdf'] = 'Download Exam PDF';
$string['downloaduserlist'] = 'Download student list';
$string['printexam'] = 'Exam PDF';
$string['printexamresponses'] = 'Exam PDF with answers';
$string['printexamkey'] = 'Checked response sheet';
$string['take'] = 'Taking exam';
$string['takeat'] = 'at';
$string['takeonsite'] = 'Taking exam set to <strong>{$a->take}</strong> at {$a->site}';
$string['checkvoucher'] = 'Verify exam voucher';
$string['vouchernum'] = 'Exam voucher nº [{$a}]: ';
$string['voucherdownld'] = ' Download the Exam booking voucher as PDF ';
$string['vouchercrc'] = ' Verification code:  {$a} ';
$string['voucherqr'] = ' You may use the QRCode to check readily the Exam voucher ';
$string['vouchergenerated'] = ' Voucher generated on {$a} ';
$string['voucherissued'] = ' Voucher issued on {$a} ';
$string['voucheruser'] = ' The student {$a->firstname} {$a->lastname} with ID:{$a->idnumber} has a booking in this exam as: ';
$string['voucherdisclaimer'] = 'This inscription voucher MUST be validated using the above codes.';
$string['bookingdate'] = ' Exam booking reserved on {$a}. ';
$string['taken'] = 'Exam Taken';
$string['notbooked'] = 'Not Booked';
$string['booked'] = 'Booked';
$string['booking'] = 'Exam booking';
$string['bookings'] = 'Exam bookings';
$string['allocated'] = 'Allocated';
$string['booking_help'] = 'The procedure can be used for booking an user in the exam call or cancelling a previous booking.';
$string['printroompdf'] = 'Print rooms PDF';
$string['printroomsummarypdf'] = 'Rooms summary PDF';
$string['printexampdf'] = 'Print exams PDF';
$string['printuserspdf'] = 'Print users list PDF';
$string['pageseparator'] = '    ==========================================   PAGE SEPARATOR ==== ';
$string['newexam'] = '                              NEW EXAM ';
$string['newroom'] = '                              NEW ROOM ';


$string['roomsinsession'] = '{$a} Rooms assigned to session';
$string['examsinsession'] = '{$a} Exams assigned to session';

$string['scheduledexams'] = 'Exams scheduled to this session: {$a}. ';
$string['bookedexams'] = 'Exams with bookings in this session: {$a}. ';
$string['allocatedexams'] = 'Exams allocated in this session: {$a}. ';

$string['roomsinvenue'] = 'Rooms having this exam in {$a} venue: ';
$string['userlist'] = "Student's list";

$string['printbinderpdf'] = "Print Fax Binder PDF";
$string['binderprintoptions'] = "Fax Binder PDF options";
$string['binder'] = "Fax Binder";
$string['taken'] = "Taken";
$string['taking'] = "Booked";

$string['qualitycontrol'] = 'Quality control';
$string['printingbuttons'] = "Printing buttons";
$string['managesessionrooms'] = 'Session Rooms';
$string['managesessionexams'] = 'Session Exams';
$string['managesessionresponses'] = 'Session Response files';
$string['managespecialexams'] = 'Add Special Exams';
$string['assignsessionresponses'] = 'Distribute Response files';
$string['loadsessioncontrol'] = 'Session control files';
$string['loadsessionresponses'] = 'Load Response files';
$string['responsefiles_help'] = 'Response files names <strong>must</strong> start with course shortname code followed by a "-", then any other itentificative text.';
$string['deleteresponsefiles'] = 'Delete response files';
$string['examresponsefiles'] = 'Load/Edit Exam response files';
$string['generateroomspdfs'] = 'Generate rooms PDFs';
$string['roomspdfsgenerated'] = 'ZIP files for Rooms generated as: <br> {$a}';
$string['printmode'] = 'Printing mode';
$string['printmode_help'] = '
A flag can be raised setting the printing mode for the file when in batch mode. <br />
Default mode in double-sided. If set to single-sided then a label "single-sided" will be added to the firl name in ZIP printing archives.';
$string['printsingle'] = 'single sided';
$string['printdouble'] = 'double sided';
$string['printroomwithexams'] = 'ZIP with Room PDF + exams';
$string['nonexistingexamfile'] = 'non_existsing_exam_file';
$string['nonexistingmessage'] = '




Here ther must be {$a->seated} copies of exam {$a->programme}-{$a->shortname} from course


{$a->fullname}



    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR


    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR    ERROR
';

$string['specialexamsinsession'] = '{$a} special exams in session';
$string['specialstudentsinsession'] = '{$a} students with special exams';
$string['specialfor'] = 'To course';
$string['addspecial'] = 'Add extra call';
$string['specialexam'] = 'Special Exam';
$string['specialexamfileexists'] = 'Special Exam file already exists';
$string['distributedresponsefiles'] = '{$a} Response files distributed';
$string['pendingresponsefiles'] = '{$a} Response files waiting distribution';
$string['unknownresponsefiles'] = '{$a} Unrecognized Response files';
$string['loadresponsesconfirm'] = '{$a->delivered} Response files delivered <br />
{$a->fail} files not identified. ';

$string['qcbookingsnonallocated'] = 'Bookings not allocated';
$string['qcvenuesnonallocated'] = 'Allocation problems by venue';
$string['qcexamsnonallocated'] = 'Exams non allocated';
$string['qcroomsnonstaffed'] = 'Rooms without Staff';
$string['qcstaffnonallocated'] = 'Staff with Exam & without Room';

$string['countbookingsnonallocated'] = 'Number of separate student/exam bookings for this session but non allocated: {$a}';
$string['countexamsnonallocated'] = 'Number of different Exams booked for this session but non allocated: {$a}';
$string['countroomsnonstaffed'] = 'Rooms allocated to this session that have not Staff assigned: {$a}';
$string['countstaffnonallocated'] = 'Teachers of an exam booked for this session that are not assigned to a room: {$a}';
$string['nonbookedexams'] = 'Exams without any booking in this session';

$string['sortby'] = 'Sort by: ';
$string['sortprogramme'] = 'Programme-Shortname';
$string['sortfullname'] = 'Course fullname';
$string['sortbooked'] = 'Most booked';
$string['sortroomname'] = 'Room name';
$string['sortseats'] = 'Total seats';
$string['sortfreeseats'] = 'Free seats';

$string['addextracall'] = 'Add hidden extra call';
$string['addextrasessioncall'] = 'Add session extra call';
$string['extraexamcall'] = 'Hidden extra call';
$string['extraexamcall_help'] = '
A hidden call is visible for teachers and can be used for exam allocation, but students cannot see neither book directly.
A hidden extra call may be appropiate if a student has to take a supplementary exam a result of some unavoidable contingency.';

$string['addstaffer'] = 'Add staff';
$string['addstaffer_help'] = 'The procedure can be used for both adding or removing (undo) Staff people based on courses (exams) assigned to each room.';
$string['removestaffer'] = 'Remove staff';

$string['bookinghelp1'] = 'Booking an exam in a specific date and venue is <strong>required</strong> to be allowed to take the exam. <br />
You may book just ONE exam date for each period, even if there are several calls, ie. just one of the calls.  ';
$string['bookinghelp2'] = 'To book an exam you must indicate the venue where you will take the exam.
If you change your mind, you can set NO on "Taking exam" field to cancel the booking. <br />

You may book an exam up to {$a->lagdays} days before the exam date, not later.';
$string['bookingerror_noexam'] = '{$a} : Cannot book on venue due to lack of session/date selected.';
$string['bookingerror_nosite'] = '{$a} : Cannot book exam {$a} due to lack of venue selected.';
$string['bookingerror_twosites'] = '{$a} : Cannot book exam {$a} due to already booked for the same date in a different venue.';
$string['bookingerror_noexamid'] = '{$a} : Cannot book due to invalid exam ID.';
$string['bookingerror_offbounds'] = '{$a} : Cannot book due date out of bounds.';
$string['setbooking'] = 'Submit bookings';
$string['downloadassignseats'] = 'Download Rooms assignments';
$string['exambookedstudents'] = 'Students booking this exam: {$a}.';
$string['totalseated'] = 'Students allocated: {$a}.';
$string['totalbooked'] = 'Students booked: {$a}.';
$string['occupancy'] = 'Occupancy';
$string['existingroom'] = 'Previously allocated';
$string['teachers'] = 'Teachers';
$string['multiteachers'] = 'Teachers with several exams';


$string['potentialusers'] = 'Potential users';
$string['potentialusersmatching'] = 'Found users';

$string['examsforperiod'] = 'Exams scheduled on period: {$a}';
$string['examsforsession'] = 'Exams on session: {$a}';
$string['examcourses'] = 'Courses with exam';
$string['noexamcourses'] = 'Courses without exam';
$string['noexams'] = 'No exams found';
$string['noexam_1'] = 'No exam found for this course';
$string['noexam_2'] = 'This course has\'n an exam scheduled on current annuality';
$string['noexam_3'] = 'This course has\'n an exam scheduled on selected period';
$string['noexam_4'] = 'This course has been passed in a previous call';

$string['selectuser'] = 'Select user';
$string['showuserexams'] = 'Show user exams';

$string['searchname'] = 'Course name';
$string['asc'] = 'Ascending';
$string['desc'] = 'Descending';
$string['attempts'] = 'Exam attempts';
$string['attempt'] = 'Attempt';
$string['attempt_help'] = '

The uploaded file must be associated to an exam attempt.

Either add a fresh new attemtp to hold this uplaoded file or
select one of the existing attempts for this exam to hold the file.

';

$string['attemptn'] = 'Attempt no {$a}';
$string['attemptname'] = 'Attempt name';
$string['attemptname_help'] = '

An attempt may have a name or identifier, it must be short, ideally one or two words

If not supplied, \'Attempt N\' will be used.

';
$string['addreviewitem'] = 'Add a new review item';
$string['addattempt'] = 'Add a new attempt';
$string['uploadexamfile'] = 'Add file to an attempt';
$string['uploadexamfile_help'] = '

The file uploaded will be attached to the designated exam as exam file corresponding to selected attempt.

';
$string['exam'] = 'Exam';
$string['examfile'] = 'Exam file';
$string['examfile_help'] = 'Exam file

The file containing the exam formatted exam, ready to be taken by students.

';
$string['examfileanswers'] = 'Exam file with answers';
$string['examfileanswers_help'] = 'Exam file with answers

A file contaning the exam questions with correct answers marked and additonal information.

';
$string['examresponsesfiles'] = 'Exam responses files';

$string['examfileresponses'] = 'Exam responses file';
$string['examfileresponses_help'] = 'Exam responses file

File or files containing response sheets filled by students taking this exam

';
$string['responsesupload'] = 'Upload exam responses files';
$string['response_unsent'] = 'Not loaded responses yet';
$string['response_sent'] = 'Responses loaded';
$string['response_waiting'] = 'Responses loaded, waiting approval';
$string['response_approved'] = 'Responses loaded and approved';
$string['response_rejected'] = 'Responses rejected';
$string['responsestatus'] = 'Status'; 
$string['responsestatus_help'] = 'The possible status for response data or files uploaded. 

 * Sent: Some data or files loaded but not approved yet.
 * Waiting: Some data or files loaded but not completed and expecting to upload more dada or files.
 * Completed: Data & files uploadad are regarded as completed for all users taking the exam.
 * Validated: Data reviewed and approved. May bye set only by staff with appropiate capabilities.

';


$string['statereview'] = 'Status review';
$string['status'] = 'State';
$string['status_help'] = 'Exam status

 * Created
 * Sent
 * Approved
 * Rejected

';

$string['send'] = 'Send Exam for review';
$string['sent'] = 'Sent for review';
$string['approve'] = 'Approve Exam file';
$string['approved'] = 'Approved';
$string['create'] = 'Create Review instance for Exam File';
$string['delete'] = 'Delete spurious Review instance items';
$string['addissues'] = 'Add Exam Review items';
$string['delissues'] = 'Delete Exam Review items';
$string['reject'] = 'Reject Exam file';
$string['rejected'] = 'Rejected';
$string['status_created'] = 'Created';
$string['status_sent'] = 'Sent';
$string['status_waiting'] = 'Waiting';
$string['status_rejected'] = 'Rejected';
$string['status_approved'] = 'Approved';
$string['status_validated'] = 'Validated';
$string['status_completed'] = 'Completed';
$string['missingreview'] = '(Missing)';

$string['confirm_delete'] = 'You have requested to delete the Exam attempt {$a->attempt} on state {$a->status} correspondig to: <br>
Course: {$a->coursename} <br>
Period: {$a->period}, Scope: {$a->examscope}, Call: {$a->callnum}
<br>
Do you want to continue?';


$string['confirm_status'] = 'You have requested to {$a->action} correspondig to: <br>
Course: {$a->coursename} <br>
Period: {$a->period}, Scope: {$a->examscope}, Call: {$a->callnum}
<br>
Do you want to continue?';
$string['status_synch'] = 'Global issue status synchronization ';
$string['confirm_synch'] = '
You have requested to synchronize {$a} all exams from correspondig review issue.

<br>
Do you want to continue?';

$string['examresponses'] = 'Exam filled responses file';
$string['examresponsesdown'] = 'Download exam filled responses files';
$string['nottaken'] = 'No one took';
$string['nottakenyet'] = 'Not administered yet';
$string['notyet'] = 'Not yet';

$string['missingrole'] = 'You need to select a role before been able to assign staff to the room.';
$string['missingvenue'] = 'You need to select a venue before been able to assign rooms to the session (and venue).';
$string['missingbookedsite'] = 'Attempt to book/assign without a venue setting (no booked site).';
$string['extraexams'] = 'Extra Exam calls.';
$string['allsessions'] = 'all sessions';
$string['error_manyapproved'] = 'More than one exam file approved';
$string['error_noneapproved'] = 'No exam file approved';
$string['error_nonesent'] = 'No exam file sent';
$string['error_nonzero'] = 'The value must be different form 0';
$string['error_lessthan'] = 'The value must be less than or equal {$a}';
$string['error_novoucher'] = 'There is NO Exam voucher with that ID';
$string['error_nobooking'] = 'There isn\'t an exam booking matching that Exam voucher';
$string['error_crccode'] = 'The validation code doesn\'t match correct for that Exam voucher. Inscription cannot be verified.';
$string['error_latervoucher'] = 'Voucher invalidated due to {$a->count} later bookings. Last {$a->last}.';
$string['error_latervoucher'] = 'Voucher invalidated due to {$a->count} later bookings. Last {$a->last}.';
$string['error_voucheruser'] = 'You are not allowed to access booking data from other participants ';
$string['extensionanswers'] = 'Correct filename suffix';
$string['extensionanswers_help'] = 'The file name suffix for the file with right answers.
Must include any conection character or puntuation, but exclude the real filename extension.';
$string['extensionkey'] = 'Key filename suffix';
$string['extensionkey_help'] = 'The file name suffix for the keys file.
Must include any conection character or puntuation, but exclude the real filename extension.';
$string['extensionresponses'] = 'Responses filename suffix';
$string['extensionresponses_help'] = 'The file name suffix for the responses file, that with response sheets filled by students that took the exam.
Must include any conection character or puntuation, but exclude the real filename extension.';
$string['pdfaddexamcopy'] = 'Copy Exams files to printing job';
$string['pdfaddexamcopy_help'] = 'If set, then the appropiate number of copies of the Exam will be adad to be printed.';
$string['pdfwithteachers'] = 'Print with teachers';
$string['pdfwithteachers_help'] = 'Id set, each printed exam will include the course teachers names';

$string['examitem'] = 'Exam';
$string['examsqc'] = 'Exams Quality Control';
$string['selectperiod'] = 'Select an exam period';
$string['genericqc'] = 'Generic checks';
$string['items'] = 'items';
$string['deleteexams'] = 'Delete listed exams';
$string['addexams'] = 'Add exams';
$string['examsqcnoexamcourses'] = 'Courses without exams: {$a}';
$string['examsgcnocourse'] = 'Exams with wrong course or programme: {$a}';
$string['periodqcnoexamcourses'] = 'Courses without exams in period: {$a}';
$string['periodqcnocourse'] = 'Exams in period without course: {$a}';
$string['periodqcwrongnumber'] = 'Exams with wrong num of calls: {$a}';
$string['periodqcwrongsession'] = 'Exams with wrong session: {$a}';

$string['mailfrom'] = 'Exams Registrar';
$string['mailresponsessubject'] = 'Users Responses file delivered in course {$a}';
$string['mailresponsestext'] = 'Users Responses file {$a->fname} delivered in course {$a->course} for session {$a->session}';
$string['mailsessionsubject'] = 'Users Responses files delivered for session {$a}';
$string['mailsessioncontrol'] = 'Users Responses files delieverd into courses:
{$a}
';
$string['generateextracallef'] = 'Generate  extra call PDFs';

$string['resortbyshortname'] = 'By shortname';
$string['resortbyfullname'] = 'By fullname';
$string['resortbyidnumber'] = 'By idnumber';

$string['eventattendanceapproved'] = 'Exam attendance data loaded';
$string['eventattendanceloaded'] = 'Exam attendance data approved';
$string['eventmanageviewed'] = 'Register management viewed';
$string['eventexamfilecreated'] = 'Examfile attempt submitted';
$string['eventexamfileupdated'] = 'Examfile attempt updated';
$string['eventexamfiledeleted'] = 'Examfile attempt deleted';
$string['eventexamfilereviewed'] = 'Examfile attempt status changed';
$string['eventexamfileprintmodeset'] = 'Examfile print mode set';
$string['eventexamfilessynced'] = 'Examfiles reviewed synchronized from Tracker issues';
$string['eventexamfilesubmitted'] = 'Examfile attempt submitted from MakeExam';
$string['eventcapabilitiesupdated'] = 'MakeExam composing capabilities updated';
$string['eventbookingsubmitted'] = 'Exam booking submitted';
$string['eventbookingunbooked'] = 'Exam booking removed by new booking';
$string['eventresponsesapproved'] = 'Exam Response files approved';
$string['eventresponsesdeleted'] = 'Exam Response files deleted';
$string['eventresponsesdistributed'] = 'Exam Response files distributed';
$string['eventresponsesuploaded'] = 'Exam Response files uploaded';
$string['eventmanageaction'] = 'Action executed in registrar';
$string['eventmanage'] = 'Registrar element item {$a}';
$string['eventmanageviewed'] = 'Registrar table/page viewed';
$string['eventfiles'] = 'Registrar files {$a}';



$string['headeruserdata'] = 'General User Attendance data';
$string['headerroomsdata'] = 'Attendance data by room';
$string['headerresponsefiles'] = 'Response files';
$string['loadattendance'] = 'Load user data';
$string['loadattendance_explain'] = 'If checked then detailed data for each user will be set';
$string['usershowing'] = 'Showing';
$string['usershowing_help'] = 'The  number of students that have been accepted to the exam room';
$string['useradd'] = 'Add';
$string['usertaken'] = 'Taken';
$string['usertaken_help'] = 'The number of exams collected, students that have taken the exam';
$string['usercertified'] = 'Certified';
$string['loadroomattendance'] = 'Room to load';
$string['loadsitedata'] = 'Load site attendance';
$string['loadsitedata_explain'] = 'If checked then load global attendance data for exam';
$string['reviewresponses'] = 'Confirm responses';
$string['numsuffix'] = ' ({$a}) ';
$string['excessshowing'] = 'Students showing greater than {$a} allocated.';
$string['excesstaken'] = 'Students taking greater than {$a} allocated.';
$string['excessshowingtaken'] = 'Students taking greater than {$a} showing.';
$string['roomerror'] = 'Error processing data, not saved for room {$a}.';
$string['savedresponsefiles'] = '{$a} files saved for this room/site.';
$string['savedroomsdata'] = 'Saved attendance data for {$a} rooms/sites.';
$string['saveduserdata'] = 'Saved attendance data for {$a} students.';
$string['savedexamsdata'] = 'Saved attendance data for {$a} exams.';
$string['globaldata'] = 'Site data';
$string['hierachyerror'] = 'Wrong hierarchy';
$string['venueerror'] = 'Single room venue error';
