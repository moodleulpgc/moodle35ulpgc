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
 * Strings for component 'assignfeedback_wtpeer', language 'en'
 *
 * @package   assignfeedback_wtpeer
 * @copyright 2016 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['default'] = 'Enabled by default';
$string['default_help'] = 'If set, Weighted Peer grading will be enabled by default for all new assignments.';
$string['wtpeer:autoalloc'] = 'Auto allocate for grading';
$string['wtpeer:autograde'] = 'Grade as autoassessment';
$string['wtpeer:grade'] = 'Grade others';
$string['wtpeer:graderalloc'] = 'Allocate graders to users';
$string['wtpeer:gradergrade'] = 'Grade as teacher';
$string['wtpeer:manage'] = 'Manage allocations & gradings';
$string['wtpeer:manageallocations'] = 'Manage any allocations';
$string['wtpeer:peeralloc'] = 'Allocate other users';
$string['wtpeer:peergrade'] = 'Grade as peer';
$string['wtpeer:tutoralloc'] = 'Allocate tutors';
$string['wtpeer:tutorgrade'] = 'Grade as tutor';
$string['wtpeer:view'] = 'View peer gradings';
$string['wtpeer:viewothergrades'] = 'View grades from other users';
$string['wtpeer:viewotherallocs'] = 'View allocations of other users';



$string['enabled'] = 'Weighted peer grading';
$string['enabled_help'] = 'If enabled, each submission might be graded in any of four levels or categories (auto, peer, tutor, grader). 
The final grade will be a wtpeer average of those categories. 

The teacher will be able to allow other people (teachers, students) to access and grade submissions in any of the categories. 
There may be several graders and several separate grades for each level. ';
$string['pluginname'] = 'Weighted peer grading';
$string['pluginnameplural'] = 'Weighted peer gradings';
$string['wtpeer'] = 'Weighted peer grading';
$string['wtpeer_help'] = 'If enabled, each submission might be graded in any of four items or categories (auto, peer, tutor, grader). 
The final grade will be a weighted average of those categories. 

The teacher will be able to allow other people (teachers, students) to access and grade submissions in any of the categories. 
There may be several graders and several separate grades for each level. ';

$string['crontask'] = 'Weigthted peer grading cron task';

$string['rowauto'] = 'Self';
$string['rowpeer'] = 'Peer';
$string['rowtutor'] = 'Tutor';
$string['rowgrader'] = 'Grader';
$string['reviewtable'] = 'Assessment table';
$string['reviewassessments'] = 'Review and Grade in Weighted Peer Assessments';
$string['manageconfig'] = 'Manage Config';
$string['manageallocations'] = 'Manage Allocations';
$string['showallocations'] = 'Show Allocations';

$string['weightselector'] = 'Assessment weights';
$string['weight_auto'] = 'Weight (%) for Self-assessment';
$string['weight_auto_help'] = 'The weight in % of total grade that is desired for marks coming from self-assesment. 

Set to zero (0) to disable, if you do not want students to self assess their submissions.';
$string['weight_peer'] = 'Weight (%) for Peer-assessment';
$string['weight_peer_help'] = 'The weight in % of total grade that is desired for marks coming form peer assessment. 

Marks granted by peers are averaged and the mean grade weigthed by this valued and added to final grade.
Set to zero (0) to disable, if you do not want students assess each other submissions.';
$string['weight_tutor'] = 'Weight (%) for Tutor assessment';
$string['weight_tutor_help'] = 'The weight in % of total grade that is desired for marks coming form tutor assessment. 

Marks granted by tutors are averaged and the mean grade weigthed by this valued and added to final grade.
Set to zero (0) to disable, if you do not want Tutors to assess student submissions.';

$string['weightinfo'] = 'Final grade for each submission is calculated from marks coming form Self-assessment, Peer assessment, Tutor assessment and Grader assessment as a weighted sum:

     <p><center> Final = &Sigma; ( weight · mean-mark ) </center></p>

Grader marks weight is just whats left from 100% in other items. Set all to zero to have 100% marking by teachers, not students.  
';

$string['dateselector'] = 'Assessment dates configuration ';
$string['peeraccessmode'] = 'Access to assessment';
$string['peeraccessmode_help'] = 'This setting controls when users can proceed to assess submissions. 

 * By date: the specified dates apply to each item. No other requirement.
 * On submission: the students are required to make a submission (may by a draft) to be able to access to auto o peer assesments. After that submission, the specified dates control the access.
 * After final submission: In addition to specified dates, the students are required to make a submission (final, unchangeable), pior to access assesments for self or others.  
';
$string['accessbydate'] = 'By date';
$string['accessaftersubmission'] = 'On submission';
$string['accessafterfinal'] = 'After final submission';
$string['startgrading_auto'] = 'Start Self assessment';
$string['endgrading_auto'] = 'End Self assessment';
$string['startgrading_peer'] = 'Start Peer assessment';
$string['endgrading_peer'] = 'End Peer assessment';
$string['startgrading_tutor'] = 'Start Tutor assessment';
$string['endgrading_tutor'] = 'End Tutor assessment';
$string['startgrading_grader'] = 'Start Teacher assessment';
$string['endgrading_grader'] = 'End Teacher assessment';

$string['publishselector'] = 'Results publication configuration';
$string['publishassessment'] = 'Assessment publication';
$string['publishassessment_help'] = 'This settings controls when the users will be able to see the results of the cross-assesment. 
By default students cannot see the marks granted by others in each of the four categories (self, peer, tutor, grader).

 * No: the teacher must set the results as published by manually changing the status, otherwise the cross-assesement results remain hidden for students.   
 * Yes: students may access to their marks automatically, once there are assesments.
 * On date: the students may access to their marks after the specified date.
';
$string['publishno'] = 'No';
$string['publishyes'] = 'Yes';
$string['publishmanual'] = 'Manual';
$string['publishauto'] = 'Automatic';
$string['publishondate'] = 'On date';
$string['publishassessments'] = 'Publish assessments';
$string['publishassessmentdate'] = 'Assessments publication date';
$string['publishgrade'] = 'Grade publication';
$string['publishgrade_help'] = 'This settings controls when the results coming for cross-assessment will be translated into a final grade in the activity.
By default results are kept.

 * Manual: the teacher must trigger the calculation of final grades and set the Grades as published by manually changing the status, otherwise the student grades remain empty.   
 * Automatic: Grades are calculated automatically once there are marks in all used (weight non-zero) assessment items (self, peer, tutor, grader).
 * On date: Grades are calculated and published on the time specified. After that you may overwrite grades manually using the the standard grading interface of the assignment module. 
 
 As a teacher, you can always change the grade for each submission/student using the standard grading interface of the assignment module. 
 But if this mode is set to "Automatic" the grade will be replaced for that calculated from weighted assessment.  
';
$string['publishgradedate'] = 'Grade calculation date';
$string['publishgrades'] = 'Calculate & Publish grades';
$string['publishmarkers'] = 'Marker\'s names';
$string['publishmarkers_help'] = 'Allows to specify if the names of the markers in each category (self, peer, tutor, grader) should be visible for students or not. 
This may be controlled by role capabilities, additionally.';
$string['assessmentstatus'] = 'Status';

$string['gradeauto'] = 'Self assessment';
$string['gradepeer'] = 'Assess as Peer';
$string['gradetutor'] = 'Assess as Tutor';
$string['gradegrader'] = 'Assess as Teacher';
$string['allocate'] = 'Assign markers';
$string['calculate'] = 'Calculate grade';
$string['allocatedmarkersgrades'] = '{$a->marker}: {$a->grade}';
$string['manualallocate'] = 'Allocate markers for single user';
$string['gradertype'] = 'Type of assessment';
$string['marker'] = 'Marker';
$string['markers'] = 'Markers ({$a})';
$string['assessallocstatus'] = '{$a->item}: {$a->grades}/{$a->allocs}';
$string['usersselector'] = 'Allocate to sumbissions from users in';
$string['markersselector'] = 'Get markers from ';
$string['assessmode'] = 'Assessment type';
$string['assessmode_help'] = 'One of the Assessment categories: Peer-, Tutor- or Garder-assessment. 
Only the available (i.e. non-zero weight) assessment modes are visible. Self-assessments are allocated only to submitters below.';
$string['selectfromrole'] = 'Select users with role';
$string['selectfromrole_help'] = 'By default the allocated users will be selected according to their assessement capabilities. 
For instance, users with Assess as Peer capability can be allocated to assess a submission as peers. 

Here you can establish and additional role requirement.
';
$string['selectfromgrouping'] = 'Select members of grouping';
$string['selectfromgroup'] = 'Select members of group';
$string['includeonlyactiveenrol'] = 'Include only active enrolments';
$string['includeonlyactiveenrol_help'] = 'If enabled, suspended users will not be considered in random allocations.';
$string['allocationsettings'] = 'Allocation settings';
$string['numperauthor'] = 'per Submission';
$string['numperreviewer'] = 'per Marker';
$string['numofreviews'] = 'Number of assessments';
$string['numofreviews_help'] = 'Number of assessments';
$string['excludesamegroup'] = 'Exclude users assessing own group';
$string['excludesamegroup_help'] = 'Enable to avoid users could be allocated as marker of their own assignemnts as Peer or Tutor or Grader.';
$string['excludeotheralloc'] = 'Exclude other markers';
$string['excludeotheralloc_help'] = 'Exclude users that already are allocated to assess a submission in other mode (e.g. as Peer or Self).';
$string['currentallocs'] = 'Existing allocations';
$string['currentallocs_help'] = 'What to to when a submission/marker already have some assessments allocated. 

    Keep : will keep existing allocations and add the new ones defined above.
    Keep up to N: will keep existing allocations and add new ones completing the quota up to the number defined above.
    Remove: will eliminate the existing allocations (not the assessments, if existing) and add new ones. 
';
$string['allocsremove'] = 'Remove allocated';
$string['allocskeep'] = 'Keep and add N new';
$string['allocskeepmax'] = 'Keep and add new ones up to N';
$string['addautoalloc'] = 'Add self-assessment';
$string['selfassessmentdisabled'] = 'Self-assessment disabled';
$string['showitemresult'] = '{$a->item}: {$a->grade} ({$a->grades}/{$a->allocs})';
$string['alertungradedallocs'] = '{$a} items requiring assessments';
$string['gradingclosed'] = 'Grading closed on {$a}';
$string['gradingupto'] = 'Grading up to {$a}';
$string['gradingstarton'] = 'Grading start on {$a}';
$string['viewassessmentsdate'] = 'Assessments published from {$a}';
$string['viewassessmentsno'] = 'Results not published yet';
$string['viewgradedate'] = 'Grade published from {$a}';
$string['viewgradeno'] = 'Grade not published yet';
$string['allocated'] = 'Allocated';
$string['graded'] = 'Graded';
$string['gradingdate'] = 'Grading date';
$string['assessment'] = 'Assessment';
$string['allocsummary'] = 'Allocation summary';
$string['userallocations'] = 'Your grading allocations';
$string['importmarkerallocs'] = 'Upload marking allocations';
$string['importmarkers'] = 'Import markers';
$string['removeexisting'] = 'Remove current markers';
$string['removeexisting_help'] = 'What to to when the file contains a user that has some markers already assigned. The posible options are: 

    * No: Keep existing markers and add the new ones from the imported file.
    * Same assessment: Remove markers currently assigned for teh same type of assessment as included in file for each user.
    * All markers: Remove al markers currently assigned for a user, for any type of assignment.

';
$string['removeitemmarkers'] = 'Same assessment';
$string['removeallmarkers'] = 'All user markers';
$string['markersfile'] = 'Markers file';
$a = core_text::strtolower(get_string('user'));
$b = core_text::strtolower(get_string('marker', 'assign'));
$string['markersfile_help'] = 'The file to import must be a CSV text file with at least three colums. 
For instance: 

    '."{$a}, {$b}".', assessment
    123456, 789321 auto  
    256789, 456852 peer

The first line contains de column identifiers. 
The codes identifying the user are the IDnumbers of each user. The third column indicated the type of assessment assigned. 

';
$string['headercolumns'] = 'Header must include columns \'{$a->user}\' , \'{$a->marker}\' and \'{$a->item}\'.  <br />
The assessment type must be one of [{$a->itemnames}].';
$string['invalidimporter'] = 'Invalid importer: the header dot not contain the right columns, or assessment types, or separator is invalid.';
$string['validmarkersassigns'] = 'Valid marker assignations found';
$string['nonvalidmarkersassigns'] = 'Lines not imported from uploaded file';
$string['gradeanalysis'] = 'View details';
$string['assessmentexplain'] = 'Detailed assessment criteria';
$string['assessexplainlink'] = 'Assessment details';
$string['toggleexplain'] = 'Toggle details ';
$string['downloadassess'] = 'Download assessments';
$string['showassess'] = 'View assessments';
$string['showexplain'] = 'View assessment details';
$string['showaliensub'] = 'View user submission';
$string['sortedby'] = 'Sorted by: {$a} ';
$string['sortlastname'] = 'Last name ';
$string['sortfirstname'] = 'First name ';
$string['sorttimegraded'] = 'Date graded ';
$string['sortgrade'] = 'Grade ';
$string['titleauto'] = 'Self-Assessment ({$a})';
$string['titlepeer'] = 'Peer Assessment ({$a})';
$string['titletutor'] = 'Tutor Assessment ({$a})';
$string['titlegrader'] = 'Teacher Assessment ({$a})';
$string['batchoperationconfirmcalculateselected'] = 'Calculate final grade for selected';
$string['batchoperationconfirmdownloadselected'] = 'Download assessments for selected';
$string['calculatedngrades'] = 'Calculated final grade for {$a} users.';
$string['noaction'] = 'No action specified';
$string['needconfiguration'] = 'Require Configuration';
$string['applytoall'] = 'Apply to other team members';
