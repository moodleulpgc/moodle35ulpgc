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
 * @package     mod_examboard
 * @category    string
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['areanotification'] = 'Notifications';
$string['board'] = 'Exam board';
$string['member'] = 'Board member';
$string['exemption'] = 'Exemption';
$string['exempted'] = 'Exempted';
$string['exempted_help'] = 'Check if this user has been exempted of duties as board member';
$string['excluded'] = 'Excluded';
$string['approved'] = 'Approved';
$string['examboard:addinstance'] = 'Add an instance of Examboard';
$string['examboard:view'] = 'View exam boards';
$string['examboard:viewall'] = 'View all boards, exams and sessions';
$string['examboard:viewothers'] = 'View other paricipantas in an exam session';
$string['examboard:submit'] = 'Submit assesables to and exam board';
$string['examboard:grade'] = 'Grade exam board submissions.';
$string['examboard:viewgrades'] = 'View user grades';
$string['examboard:releasegrades'] = 'Release grades to be seen by participants';
$string['examboard:allocate'] = 'Allocate teachers to boards and students to exams';
$string['examboard:manage'] = 'Manage exam boards, allocation and members';
$string['examboard:notify'] = 'Send notifications to assigned users';
$string['examboard:tutorize'] = 'Tutorize students in exams';
$string['messageprovider:examboard_notify'] = 'Message';
$string['modulename'] = 'Exam Board';
$string['modulenameplural'] = 'Exam Boards';
$string['partialgrading'] = 'Incomplete grading';
$string['pluginname'] = 'Exam Board';
$string['pluginadministration'] = 'Exam Board Administration';
$string['manageallocation'] = 'Manage allocations';
$string['notify'] = 'Notify';
$string['notify_help'] = 'Allows to issue notification messages to participants in an Examination.';
$string['addexam'] = 'Add examination';
$string['submit'] = 'Submit';
$string['grade'] = 'Grade';
$string['groupingname'] = 'Grouping name';
$string['groupingname_help'] = 'If groups creation is enabled, the groups can be assigned automatically to this grouping. 

If a grouping with this idnumber exists, it is used, if not it is created to hold exam groups.';
$string['boarddata'] = 'Board data';
$string['existingboard'] = 'Board to assign';
$string['existingboard_help'] = 'The exam may be assigned to an existing Board, 
selecting from the menu. Or just create a new one from newly data.';
$string['newboard'] = 'New board';
$string['boardidnumber'] = 'Board idnumber';
$string['boardidnumber_help'] = 'The new board needs a short code name, to use on listings.
Idnumber must be a single alpha-numeric code, something like T01 or B-101.';
$string['boardname'] = 'Board name';
$string['boardname_help'] = 'A name to identify the board as a team. 
You may use a phrase, several words.';
$string['boardactive'] = 'Visibility';
$string['boardactive_help'] = 'If the board will be visible by non-managing users. 
You may create inactive boards to hold a reserve, or keep inactive until all names are agreed on.';
$string['examdata'] = 'Examination data';
$string['examgroups'] = 'Exam groups';
$string['examgroups_help'] = 'If enabled, then a group will be created for each separate examination 
and populated with board members as well as tutors and examinees. 

These groups will be automatically updated when users are assigned o de-assigned as board members, tutors or examinees.';
$string['examvenue'] = 'Exam venue';
$string['examvenue_help'] = 'The venue or classroom where the examination will take place.';
$string['examdate'] = 'Exam date';
$string['examdate_help'] = 'When the examination will take place.';
$string['examduration'] = 'Exam duration';
$string['examduration_help'] = 'Period scheduled for the examination';
$string['examactive'] = 'Visibility';
$string['examactive_help'] = 'If the Exam will be visible by non-managing users. 
You may create inactive exams to hold a reserve, or keep inactive until all names are agreed on.';
$string['mandatoryifnew'] = 'Data cannot be empty';

$string['userassign'] = 'Examinee/tutor assignation';
$string['userassign_help'] = 'Allows to assign students (and optionally their tutors) to an Exam Committee. 

Each student (and their tutors) will be added to all exams assiged to the committee specified.
';
$string['userassignation'] = 'Assignation list';
$string['userassignation_help'] = 'Examinee/tutor assignation list. 
This is a text list for asignation of students to a Committee. Each row is a separate user assignation. 
Tutor data is optional, depending of the configuration settings.

Mandatory data are: 

   * User ID: an unique ID for each student, imterpreted by the field defined below
   * Committee ID: the committee ID code defined previously. This code must exists previously. 

The first line MUST contain field names, as depicted.     
Each data in a row may be separared by characters "|", ",", ";". 
If there are several other tutors, each one ID must be separated by spaces from neighbours, 
and the whole list separated form other data by the above characters. 

e.g. 

studentID| CommitteeID, tutorID, othertutorID1  othertutorID2 othertutorID3

';
$string['tutorcheck'] = 'Check conflicting tutors';
$string['tutorcheck_help'] = 'If enabled then the tutors will be checked against Committee members. 
If there are any coincidences then the assignation is cancelled.  ';
/*
$string['useridfield'] = 'User ID code';
$string['useridfield_help'] = 'How to interpret the user ID values in the user assignation list. 
Which user field to match to that values to perfoem user identification.';
*/
$string['userallocation'] = 'Examinee allocation';
$string['boardallocation'] = 'Board members allocation';
$string['chooseexam'] = 'With exam: ';
$string['chooseusertype'] = 'With users: ';
$string['addexam'] = 'Add exam';
$string['addexam_help'] = 'Here you can add the basic data for a new Examination. 
Both the exam session name (and venue, date and session specific data) and the Committee in charge of the Examnination.

A new Examination must have a Committee responsible for the assessments and an exam sesion name and specific data.

You can use an existing Committee, in fact assigning a new exam session to those teachers, or you can create a new Committee.
Each Committee is identified by an essential idnumber. You may specify also a title and a name if convenient.';
$string['updateexam'] = 'Update exam';
$string['updateexam_help'] = 'Here you can update basic data for an Examination. 
Both the exam session name (and venue, date and session specific data) and the Committee in charge of the Examnination.
';
$string['addmembers'] = 'Add board members';
$string['editmembers'] = 'Update board members';
$string['editmembers_help'] = 'Allows to edit and change the teachers that form the Committee for some examinations. 

Changing or removing members affects to all Examinations where this Committee is in charge of assessment. 

To remove a member you must select "none" in the desired place. Deputy members are optional. 
';
$string['assignexam'] = 'Assign to this board';
$string['boardconfirm'] = 'Confirmation status';
$string['boardconfirm_help'] = 'Here you can confirm (or not) you participation in the Examination Committee';
$string['toggleconfirm'] = 'Toggle confirmation';
$string['confirmation'] = 'Confirm status';
$string['confirmation_help'] = 'Check to confirm and approve your participation as a Board member. Uncheck otherwise.';
$string['confirmexam'] = 'Confirmed participation as Board member';
$string['unconfirmexam'] = 'UnConfirmed  participation as Board member';
$string['noconfirmsave'] = 'Not saved Board confirmation due to database error';
$string['memberrole'] = 'Role';
$string['membername'] = 'Name';
$string['boardstatus'] = 'Confirmation status';
$string['boardnotify'] = 'Notifications';
$string['deputymembers'] = 'Deputy members';
$string['sessionlabel'] = 'Session : {$a}';
$string['order'] = 'Order no.';
$string['userlabel'] = 'Label ID';
$string['userlabel_help'] = 'A text field to further identify the examinee.';
$string['maintutor'] = 'Main tutor';
$string['othertutors'] = 'Other tutors';
$string['updateuser'] = 'Update user';
$string['updateuser_help'] = 'Here you can add an examinee to this exam, or update their data. 

Tutors can be assigned or removed, accordingly to the module settings with repect to Tutor participation (allowed, required or not).

You can specify a new sorting order to arrange the list of examinees within this exam.

';
$string['userdeleteconfirm'] = 'Removing an user is permanent. <br />
Do you want to proceed? ';
$string['deleteallconfirm'] = 'Removing all users is permanent. <br />
Do you want to proceed? ';
$string['examdeleteconfirm'] = 'Removing an exam is permanent. <br />
Do you want to proceed? ';
$string['deleteexam'] = 'Remove exam';
$string['deleteexam_help'] = 'Remove an Examination session. Once removed an exam cannot be restored, you will need to create it again from scratch.

When removing an exam session you may remove the Committee in charge of that Examination or just keep it to assign other Examinations.<br />
In any case, a Committee that has other Examination sessions assigned will not be removed. 
';
$string['deleteuser'] = 'Remove user';
$string['deleteuser_help'] = 'Removing an student from an Exam will remove also the association with their tutors in that Exam.
The student can be assigned again to this or other Exams, in other moment.

In any case, a graded student will NOT be removed';
$string['deleteall'] = 'Remove all';
$string['deleteall_help'] = 'This action will remove <strong>ALL</strong> students from an examination.';
$string['adduser'] = 'Add user';
$string['saveuser'] = 'Save user';
$string['reorder'] = 'Reorder';
$string['orderkeepchosen'] = 'Keep as shown';
$string['orderrandomize'] = 'Randomize';
$string['orderalphabetic'] = 'Alphabetic';
$string['codename'] = 'Label';
$string['session'] = 'Exam session';
$string['examinees'] = 'Examinees';
$string['tobepublishedafter'] = 'To be published after {$a}';
$string['viewboard'] = 'Board committee';
$string['viewusers'] = 'Examinees';
$string['viewexam'] = 'Examination';
$string['returntoexam'] = 'Return to exam';
$string['returntoexams'] = 'Return to exams';
$string['examboardname'] = 'Name on page';
$string['examboardfieldset'] = 'Exam Board options';
$string['distributionfieldset'] = 'Distribution options';
$string['notifyfieldset'] = 'Notifications';
$string['publishfieldset'] = 'Publish dates';
$string['maxboardsize'] = 'Max. number board members';
$string['maxboardsize_help'] = 'The largest number of members in an exam committee in this examboard. 
Lower number of members are allowed, but not larger.';
$string['usetutors'] = 'Tutor participation';
$string['usetutors_help'] = 'If students tutors has any role this exam board. If participating a tutor cannot be an examiner for theri own students.
The options are: 

 * No: This examboard does not manage student tutors (neither pairing with students nor examiner restrictions).
 * Yes: This examboard allows participation of students tutors. Manages tutor/student asignations and coincidences of examiner and tutor roles.
 * Required: Students must have a Tutor, cannot assign an student tu an exam without assigning their tutor.

';
$string['tutoruseno'] = 'No';
$string['tutoruseyes'] = 'Yes';
$string['tutoruserequired'] = 'Required';
$string['allocation'] = 'Automatic Allocation';
$string['allocation_help'] = 'Users may be allocated to boards and exam in a semi-automatic and randomized way. 
This parameter controls the allocation strategy. 

 * Allocate board members after students and their tutors have been assigned to exams. 
    Examiner allocation takes care to not assign a teacher as examiner of their tutorized students.

 * Allocate students after examiners have been assigned to exams. 
    Student allocation takes care to not assign a student in an exam whose committee includes their tutors.

';
$string['allocmodenone'] = 'No automatic allocation';
$string['allocmodemember'] = 'Allocate board members given students';
$string['allocmodeuser'] = 'Allocate students given board members';
$string['allocmodetutor'] = 'Allocate students given board members';
$string['allocnumusers'] = 'Allocated {$a} examinees';
$string['allocnumexams'] = 'Assigned Board members in {$a} exams';
$string['allocemptied'] = 'No more users to allocate';
$string['allocprevious'] = 'Erase previous';
$string['allocprevious_help'] = 'If enabled, existing assignations will be deleted previously to random allocation.';
$string['allocdeputy'] = 'Allocate deputies';
$string['allocdeputy_help'] = 'If enabled, in addition to regular members other users will be allocated as deputy members.';
$string['allocrepeatable'] = 'Allow repetitions';
$string['allocrepeatable_help'] = 'If enabled, allocated users can be selected again in a separate exam.';
$string['allocateboard'] = 'Allocate board members';
$string['allocateboard_help'] = 'Users to fill each position in a Board will be selected from the designated groups. 
You may repeat a group for several positions, if desired.';
$string['allocateusers'] = 'Allocate examinees';
$string['allocateusers_help'] = 'Users from the selected groups will be randomly assigned as examinees in the target exams.';
$string['allocationsettings'] = 'Allocation settings';
$string['allocatedexams'] = 'Target Exams';
$string['allocatedexams_help'] = 'The available users will be distributed randomly into the selected exams.';
$string['examsallocated'] = 'Exams allocated ';
$string['choosegroup'] = 'Groups for position {$a}';
$string['allocatewarningboard'] = 'Students may have tutors. 
Provisions are taken to avoid assigning the same person as tutor and board member in the same exam '; 
$string['allocatewarningusers'] = 'Students may have tutors. 
Users with grading capability in a group will be considered as Tutors of eachs student in a group. 
The tool expects to deal with separate groups that contain only a tutor (and co-tutors) and their tutorized students. 

Provisions will be taken to avoid assigning an student to a board wher any of the students tutors may serve a board member role. 
'; 
$string['sourcegroups'] = 'Groups with examinees';
$string['usersperexam'] = 'Examinees per exam';
$string['usersperexam_help'] = 'The number of distinct examinees allocated to each exam. 
The already assigned users will be taken into account.  ';
$string['nolimit'] = 'as needed';

$string['requireconfirm'] = 'Require confirmation';
$string['requireconfirm_help'] = 'Controls if Committee members must confirm their participation as examimners. 
If active then extra checks and warnings are issued to examboard managers.';
$string['confirmtime'] = 'Confirmation deadline';
$string['confirmtime_help'] = 'Time beforehand the exam session the examiner can change their confirmation status.';
$string['notifyconfirm'] = 'Notify confirmations';
$string['notifyconfirm_help'] = 'If the board managers will receive notifications when committee members confirm or unconfirm their participation.';
$string['confirmdefault'] = 'Default confirmation status';
$string['confirmdefault_help'] = 'If confirmations are used, defines the default confirmation status for examiners. 

If default status is "Yes" then it is assumed that all board members are confirmed and any of the may <strong>un-confirm</strong> their participation.

If default status is "NO" then it is expected each board members will confirm (or never do it) their participation separately.
';
$string['usewarnings'] = 'Issue warnings';
$string['usewarnings_help'] = 'If the module will issue automatic reminder messages about an exam and to whom. 
Once a date of an exam session is reaching, the module can issue e-mail messages to users as reminder of the exam session. 
Recipients may be:

 * No one, not used reminders.
 * Examinees: only students will be reminded an examination is coming.
 * Examiners: committee members  will be reminded an examination is coming.
 * Tutors: student tutors will be reminded an examination is coming.
 * Staff: committee members and student tutors, both will be reminded an examination session is coming.
 * All: all participating users, whatever role, students, examiners or tutors will receive the reminder message.

';
$string['usernone'] = 'Do not issue any reminder';
$string['userexaminees'] = 'Examinees';
$string['usermembers'] = 'Examiners';
$string['usertutors'] = 'Tutors ';
$string['userstaff'] = 'Teachers (examiners & tutors)';
$string['userall'] = 'Every participant';
$string['warntime'] = 'Reminder anticipation';
$string['warntime_help'] = 'How long behorehand the exam session the reminder messages will be issued';
$string['inactive'] = 'Hide';
$string['active'] = 'Show';
$string['assignedexams'] = 'Assigned Exams';
$string['assignedexams_help'] = 'You may select here to which exam sessions this Committee wil be adscribed, i.e. they will acta as examiners.';
$string['publishboard'] = 'Board publication';
$string['publishboard_help'] = 'If or when the examiners Committee wil be shown to users. It may be visible/hidden always, or made visible from a defined date on.';
$string['publishondate'] = 'From date';
$string['publishboarddate'] = 'Board publish date';
$string['publishboarddate_help'] = 'Stores the date deadline for Committee publication. If used, after this date, the members of the committee will be visible by students.';
$string['publishgrade'] = 'Grade publication';
$string['publishgrade_help'] = 'If, or when, the grades of each students will be visible.';
$string['publishgradedate_help'] = 'Stores the date dealine for grade publication. If used, after this date all grades will be publicly available to users.';
$string['publishgradedate'] = 'Grades publish date';
$string['wordsfieldset'] = 'Words for roles';
$string['namechair'] = 'Committee Chairman';
$string['namechair_help'] = 'The person presiding and responsible for the Committe.';
$string['namesecretary'] = 'Committee Secretary';
$string['namesecretary_help'] = 'The person in charge of keeping records for the committe';
$string['namevocal'] = 'Committee Member';
$string['namevocal_help'] = 'Other Committee members that act as examiners.';
$string['nameexaminee'] = 'Examinee';
$string['nameexaminee_help'] = 'The students being examined.';
$string['nametutor'] = 'Student\'s tutor';
$string['nametutor_help'] = 'Teachers that tutorize, supervise or help students to prepare the examination.';
$string['chairword'] = 'Chairman';
$string['secretaryword'] = 'Secretary';
$string['vocalword'] = 'Member';
$string['examineeword'] = 'Examinee';
$string['tutorword'] = 'Tutor';
$string['gradeablemod'] = 'Graded activity';
$string['gradeablemod_help'] = 'This module is a grading helper for another graded activity. 
This setting allows to specify which other activity holds the submissions that are graded here.

This must be an existing activity in the same course with a non-blank grade IDnumber.
';
$string['gradeable'] = 'Submission';
$string['gradeablemods'] = 'Companion activities';
$string['gradeablemods_help'] = 'This module is a grading helper for another graded activity. 
This setting allows to specify which other modules holds complementary data needen by Examination board members to perform their task. 
These may include the activity(ies) holding the actual paper submission, or other complementary data as Project Proposal or Defense Requets. 
';
$string['proposal'] = 'Proposal';
$string['proposalmod'] = 'Proposal submission';
$string['proposalmod_help'] = 'This module is a grading helper for other course activities. 
This setting allows to specify which other activity holds complementary data, in this case Project proposal. 
If examinations on this module do not need a previous Project Proposal step, just select none here.

If used, this must be an existing activity in the same course with a non-blank grade IDnumber.

May be coincident with graded or Defense items.
';
$string['defense'] = 'Defense';
$string['defensemod'] = 'Defense submission';
$string['defensemod_help'] = 'This module is a grading helper for other course activities. 
This setting allows to specify which other activity holds complementary data, in this case Project defense request. 
If examinations on this module do not need a previous Defense request approval step, just select none here.

If used, this must be an existing activity in the same course with a non-blank grade IDnumber.

May be coincident with graded or Proposal items.
';
$string['grademode'] = 'Grade calculation';
$string['grademode_help'] = 'How the final grade is computed from each separate assessment grade by each Committee member. 
The possible ways are:

 * Average: the final grade is the arithmetic mean of the grades issued by assessing examiners.
 * Highest: the final grade is the highest one of those issued by assessing examiners.
 * Lowest: the final grade is the lowest one of those issued by assessing examiners.

In addition, a minimun can be set for the number of issued grades. 
If some student has not been assesed by at least that number of examiners, with te respective grade, 
then final grade is not calculated, the student remains ungraded.

';
$string['grades'] = 'Examboard Grades';
$string['gradinguser'] = 'Grading user in {$a}';
$string['gradingaverage'] = 'Average mean';
$string['gradingmax'] = 'Highest grade';
$string['gradingmin'] = 'Lowest grade';
$string['mingraders'] = 'Minimun number of graders';
$string['notifiedexams'] = 'Notified exams';
$string['notifiedexams_help'] = 'The participants in these examinations will receive the notification. You may select several or all items in the list.

The user role, the type of participation that gets the notification, is defined below.';
$string['notifiedusers'] = 'Participants notified';
$string['notifiedusers_help'] = 'The type of participants, their role in de Examinatiom, that will receive the notification.

Recipients may be:

 * Examinees: only students will be notified about the Examination.
 * Examiners: committee members  will be notified about the Examination.
 * Tutors: student tutors will be notified about the Examination.
 * Staff: committee members and student tutors, both, will receive the notification.
 * All: all participating users, whatever role, students, examiners or tutors will receive the notification.

';
$string['includedeputy'] = 'Include deputy members';
$string['includedeputy_help'] = 'If included, then not only the proper Committee members will be notified, 
but also the susbtitute or deputy members.';
$string['includepdf'] = 'Generate PDF attachment';
$string['includepdf_help'] = 'If set to "yes", then the email message wil include as attachment a PDF file with the same text in a formal dressing';
$string['attachname'] = 'Name for attached PDF';
$string['attachname_help'] = 'The name that the generated file will show in the email. 

Must be a valid filename without the extension. ".pdf" extension will be added automatically.
';
$string['attachment'] = 'Attachment';
$string['messagesubject'] = 'Subject';
$string['messagesubject_help'] = 'The customized subject for the notification message. 

The text will be prefixed automatically with the course shortname of this instance.

';
$string['messagebody'] = 'Message body';
$string['messagebody_explain'] = '<p>The message will automatically include 
a link to this instance of the Exam Board module. </p>
<p>You may customize the message text with some %%data%% substituted with actual values for each user or exam. 
You may find the data keys in the Help button.';
$string['messagebody_help'] = 'The main text of the notification message. 
The message will automatically include a link to this instance of the Exam Board module. 

You may customize the message text with some data substituted with actual values for each user or exam. 
The case must be maintained.

 * <strong>%%FIRSTNAME%%</strong>: User first name. 
 * <strong>%%LASTNAME%%</strong>: User last name. 
 * <strong>%%NAME%%</strong>: User full name. 
 
 * <strong>%%ROLE%%</strong>: The role or participation type of the user, either examinee, examiner, tutor etc. 
 * <strong>%%IDNUMBER%%</strong>: The identification label or code for the Examination. 
 * <strong>%%SESSION%%</strong>: The session name for a particular Examination.  
 * <strong>%%DATE%%</strong>: The date the examination will take place.
 * <strong>%%TIME%%</strong>: The time of day (hour:minute) the examination will take place.
 * <strong>%%DATETIME%%</strong>: The date, including time, the examination will take place.
 
 * <strong>%%DURATION%%</strong>: The specified duration of the examination.
 * <strong>%%VENUE%%</strong>: The venue, classroom or other place where the examination will take place.
 * <strong>%%TODAY%%</strong>: Todays date. 
  
 * <strong>%%STUDENTS%%</strong>: The list of students to be assessed in this Examination. 
 * <strong>%%COMMITTEE%%</strong>: The list of examiners, tha Committee members.
 
';
$string['replace_firstname'] = 'FIRSTNAME';
$string['replace_lastname'] = 'LASTNAME';
$string['replace_fullname'] = 'NAME';
$string['replace_role'] = 'ROLE';
$string['replace_idnumber'] = 'IDNUMBER';
$string['replace_sessionname'] = 'SESSION';
$string['replace_examdate'] = 'DATE';
$string['replace_examtime'] = 'TIME';
$string['replace_examdatetime'] = 'DATETIME';
$string['replace_venue'] = 'VENUE';
$string['replace_duration'] = 'DURATION';
$string['replace_students'] = 'STUDENTS';
$string['replace_committee'] = 'COMMITTEE';
$string['replace_date'] = 'TODAY';
$string['logofile'] = 'Logo file';
$string['logofile_help'] = 'A logo or stamp file to include in the upper left corner of the document.

Must be an image file.

';
$string['logowidth'] = 'Logo width';
$string['logowidth_help'] = 'The desired width of the logo image in the header, in mm.';
$string['messagesender'] = 'Formal closing';
$string['messagesender_help'] = 'A closing line, after main text (and the automatically included link to the activity instance),
and before the signature. Should include the title of the notifying manager. 

If the graphical signature below is not used, you may include here a typed signature.

';
$string['signaturefile'] = 'Signature file';
$string['signaturefile_help'] = 'An image file to be included below the closing, may be a graphic signature or official stamp.';
$string['defaultsubject'] = 'Committee appointment';
$string['defaultbody'] = '

You may customize the message text with some data substituted with actual values for each user or exam. 
The case must be maintained.

 * <strong>%%FIRSTNAME%%</strong>: User first name. 
 * <strong>%%LASTNAME%%</strong>: User last name. 
 * <strong>%%NAME%%</strong>: User full name. 
 * <strong>%%ROLE%%</strong>: The role or participation type of the user, either examinee, examiner, tutor etc. 
 * <strong>%%IDNUMBER%%</strong>: The identification label or code for the Examination. 
 * <strong>%%SESSION%%</strong>: The session name for a particular Examination.  
 * <strong>%%DATE%%</strong>: The date the examination will take place.
 * <strong>%%DURATION%%</strong>: The specified duration of the examination.
 * <strong>%%VENUE%%</strong>: The venue, classroom or other place where the examination will take place.
 * <strong>%%STUDENTS%%</strong>: The list of students to be assessed in this Examination. 
 * <strong>%%COMMITTEE%%</strong>: The list of examiners, tha Committee members.

';
$string['searchprompt'] = 'Type or click arrow';
$string['nothingselected'] = 'Nothing selected';
$string['deputy'] = 'deputy';
$string['deputytag'] = ' [Deputy]';
$string['roletag'] = ' ({$a})';
$string['tutortag'] = ' (Tutors: {$a})';
$string['unknowngrader'] = 'Unknown grader';
$string['savemembers'] = 'Save board members';
$string['cronruntimestart'] = 'Mailing cron time';
$string['configcronruntimestart'] = 'The hour in the day when automatic mailing of warning messages will be processed.';
$string['confirmdeleteexam'] = 'You have asked to remove the Examination <strong> {$a} </strong>. <br /> 
Removing an exam is permanent, there is no undo.  <br /> <br />
<p>You may delete just this exam session, keeping the Committee members to be assigned other exams or completely remove the Committee and all its members.</p>
';
$string['deleteexamboard'] = 'Remove Committee';
$string['confirmdeleteuser'] = 'You has asked to remove from exam <strong>{$a->exam}</strong> the student <strong>{$a->name}</strong>.
<br />Removing an user fron an exam is permanent, there is no undo, but you may add the student again.<br />
Removing an student removes also the link with their tutors in this Examination (not in others).
<br />

<p>In any case a graded student will not be removed.</p>';
$string['confirmdeleteallexaminees'] = 'You has asked to remove all students from exam <strong>{$a}</strong>.
<br />Removing an user fron an exam is permanent, there is no undo, but you may add the student again.<br />
Removing an student removes also the link with their tutors in this Examination (not in others).
<br />

<p>In any case a graded student will not be removed.</p>';
$string['examhasgrades'] = 'The Examination includes students already graded.';
$string['deletedexam'] = 'Exam has been deleted, with {$a->users} students and {$a->tutors} tutors. ';
$string['deletedexaminees'] = 'Removed {$a} examinees & tutors from this examination.';
$string['deletedboard'] = 'Committee has been deleted and its {$a} members.';
$string['examplacedate'] = 'Venue & Date';
$string['updateboard'] = 'Update committee';
$string['boardtitle'] = 'Title';
$string['boardtitle_help'] = 'Committee title is the word user to refer formally to them in this context';
$string['accessgroup'] = 'Group';
$string['accessgroup_help'] = 'If a group is set then only users (students, teachers) of that group would access to this exam. ';
$string['examsession'] = 'Session';
$string['examsession_help'] = 'The name of the Examination session. 

Each Committee can assemble in different sessions to examine the same or different students. 
Each gathering session must have a distintive name. ';
$string['import'] = 'Import';
$string['import_help'] = 'Allows to import Examination data in a CSV file. 
Data to import may include Exam and Committee names, dates and users (teachers examines, students or even tutors.

The imported file must contain a first row with titles defining data contained in that column.
Column names must match exactly with those indicated below in this page.

Dates must be specified in an ISO 8601 o RFC standar format parseable by "strtotime". 
For instance  02/01/2018 10:00 is Feb 01.

Duration must be specified in hours, optionally with minutes ansd seconds, e.g. 2:30:05.
';
$string['importedrecords'] = 'Processed {$a} records for import/update.';
$string['export'] = 'Export';
$string['export_help'] = 'Allows to export all or some data about examinations stored in Examboard modeule.';
$string['fixedfields'] = 'Mandatory items';
$string['fixedfields_help'] = 'These data will be exported invariably for each record. 

You may choose to include user ID number in addition to user name for those fields concerning users.
';
$string['optionalfields'] = 'Optional items';
$string['ignoremodified'] = 'Force update';
$string['ignoremodified_help'] = 'How to behave when data in imported file applies to an existing Committee or Examnination. 

Default option (unchecked) is to preserve existing data, ignoring potential update from imported file.  
If checked, then data present in the imported file will overwrite existing data for the same Examination.
';
$string['ignoremodifiedexplain'] = ' uncheck to ignore if data already exists.';
$string['userencoding'] = 'User ID';
$string['userencoding_help'] = 'The parameter used to identify users in the imported file. May be one of:
 
  * Moodle ID
  * User idnumber
  * User username

The imported values will be matched with stored values for the specified ID field.
';
$string['exportfields'] = 'Fields to export';
$string['userid'] = 'User ID';
$string['useridnumber'] = 'Include user ID number';
$string['useridnumbercsv'] = 'User ID number';
$string['exportfileselector'] = 'File to generate';
$string['exportfilename'] = 'Exported file name';
$string['exportformatselector'] = 'Data format';
$string['exportedexams'] = 'Exams to export';
$string['exportedexams_help'] = 'Which Examinations to export. You must select at least one.';
$string['exportlistby'] = 'Export list mode';
$string['exportlistby_help'] = 'How the exported exam listing will be organized. 
The are several posibilities:

 * By exam: each Examination in a single separate row.
 * By Examinee: there will be a separate row for each examinee in each Examination.
 * By Committee members: in each Examination, there will be a separte row for each member (if examinees also exported, this will effectively duplicate those lines for each member).

';
$string['listbyexam'] = 'By examination';
$string['listbyuser'] = 'By Emaninee';
$string['listbymember'] = 'By Committee member';
$string['allocmemberorder'] = 'Examinee sorting';
$string['allocmemberorder_help'] = 'How to sort the list of examinees and assign an order number to each one.';
$string['tutorasother'] = 'Cannot duplicate the main tutor as other tutor';
$string['choosereorder'] = 'Examinee order';
$string['noinputdata'] = 'NO input data';
$string['errorfieldcolumns'] = 'Data columns not identified. Missing mandatory data.';
$string['skippedlines'] = 'These lines were NOT processed due to errors or conflicts.';
$string['assignednusers'] = 'Processed {$a->count} assignments of {$a->users} students in {$a->exams} exams';
$string['exportexams'] = 'Exams to export';
$string['notification_moreinfo'] = 'You can check details in {$a}';
$string['controlemailsubject'] = '[{$a->shortname}]: Notifications summary from {$a->modname} for {$a->usertype} ';
$string['controlemailbody'] = 'Have been issued {$a->count} notifications to {$a->usertype} in {$a->modname}';
$string['examiners'] = 'Examiners';
$string['tutors'] = 'Tutors';
$string['staff'] = 'Staff';
$string['allusers'] = 'All';
$string['downloadfile'] = 'Download file {$a}';
$string['gradeusers'] = 'Grade examinees';
$string['discharge'] = 'Discharge motive';
$string['discharge_help'] = 'Examiners must indicate a reason or justification for their non-participation in the Exam.';
$string['dischargeexplain'] = 'Allegations';
$string['dischargeexplain_help'] = 'Additional allegations to justify the refusal to confirm aa a Board member.';
$string['confirmavailable'] = 'Available in other sessions';
$string['confirmavailable_help'] = 'Whether the user may be available to be a Board member in other session, other day, 
or if not available at all.';
$string['discharges'] = 'Default discharge motive';
$string['discharges_help'] = 'The posible motives a potential Board member may indicate to NOT confirm as examiner.';
$string['discharge_holidays'] = 'Holidays';
$string['discharge_illness'] = 'Illness leave';
$string['discharge_study'] = 'Study leave';
$string['discharge_maternal'] = 'Parental leave';
$string['discharge_congress'] = 'Conflicting Congress';
$string['discharge_service'] = 'Conflicting service';
$string['discharge_leave'] = 'Other leave';
$string['discharge_other'] = 'Other';
$string['discharge_other1'] = 'Other';
$string['discharge_other2'] = 'Other';
$string['discharge_other3'] = 'Other';
$string['confirm'] = 'Confirm';
$string['unconfirm'] = 'Decline';
$string['remindertask'] = 'Exam reminder emails for Exam board examinations';
$string['remindername'] = 'Dear {$a}: <br />';
$string['remindersubject'] = '{$a->shortname}: Exam reminder {$a->shortname}';
$string['reminderas'] = ' (as {$a})';
$string['reminderbody'] = 'This is an automatic message to remind you your participation in a Board Examination. <br />
<p>
Exam: {$a->title} {$a->idnumber} <br />
Role: {$a->role} <br />
Date: {$a->examdate} <br />
Venue: {$a->venue} <br />
</p>
<p>
You may check the details in the activity {$a->link}.
</p>
';
$string['remindercontrolsubject'] = '[{$a->shortname}]: Reminders summary for {$a->modname}';
$string['remindercontrolbody'] = 'Have been issued {$a->count} reminders to {$a->usertype} in {$a->modname}';
$string['bulkaddexam'] = 'Add Exam series';
$string['bulkaddnum'] = 'Number exams to add';
$string['bulkaddnum_help'] = 'This tool will add a series of numbered exams with correlative numbers. 

Total number of exams to add and the number of the first added exam need to be indicated.
';
$string['bulkaddstart'] = 'Starting no';
$string['submitbulkaddexam'] = 'Add series';
$string['submissionstatus'] = 'Submission & complementary items';
$string['viewgraded'] = 'Grading details';
$string['viewgrading'] = 'Grading';
$string['visibility_explain'] = 'Hidden items are inactive, visible only for managers.';
$string['viewgradingdetails'] = 'Click to view grading details by criteria.';
$string['usergrades'] = 'User grades';
$string['synchusers'] = 'Update groups & access';
$string['foruser'] = 'Participant';
$string['event_board_viewed'] = 'Board viewed';
$string['event_board_viewed_desc'] = 'The user with id \'{$a->userid}\' viewed the Board panel 
with id \'{$a->objectid}\' in activity with cm id \'{$a->cmid}\'.';
$string['event_exam_viewed'] = 'Board Exam viewed';
$string['event_exam_viewed_desc'] = 'The user with id \'{$a->userid}\' viewed the Exam page 
with id \'{$a->objectid}\' in activity with cm id \'{$a->cmid}\'.';
$string['event_member_updated'] = 'Board member updated';
$string['event_member_updated_desc'] = 'The user with id \'{$a->userid}\' updated Board member \'{$a->relateduserid}\' in 
Board with id \'{$a->objectid}\'  as sortorder {$a->other_sortorder} and deputy {$a->other_deputy} in activity with cm id \'{$a->cmid}\'.';
$string['event_tutor_updated'] = 'Tutor updated';
$string['event_tutor_updated_desc'] = 'The user with id \'{$a->userid}\' updated Tutor \'{$a->relateduserid}\' in 
Exam with id \'{$a->objectid}\' for examinee {$a->other_examinee} in activity with cm id \'{$a->cmid}\'.';
$string['event_examinee_removed'] = 'Tutor updated';
$string['event_examinee_removed_desc'] = 'The user with id \'{$a->userid}\' removed examinee \'{$a->relateduserid}\' in 
Exam with id \'{$a->objectid}\' in activity with cm id \'{$a->cmid}\'.';
$string['event_examinee_updated'] = 'Examinee updated';
$string['event_examinee_updated_desc'] = 'The user with id \'{$a->userid}\' has updated examinee \'{$a->relateduserid}\' in 
Exam with id \'{$a->objectid}\' in activity with cm id \'{$a->cmid}\'.';
$string['event_user_graded'] = 'Student graded';
$string['event_user_graded_desc'] = 'The user with id \'{$a->userid}\' has graded student \'{$a->relateduserid}\' in 
Exam with id \'{$a->other_exam}\' in activity with cm id \'{$a->cmid}\'.';
$string['event_member_confirmed'] = 'Board member confirmed';
$string['event_member_confirmed_desc'] = 'The user with id \'{$a->userid}\' confirmed participation on Exam with 
id \'{$a->objectid}\'  in activity with cm id \'{$a->cmid}\'.';











