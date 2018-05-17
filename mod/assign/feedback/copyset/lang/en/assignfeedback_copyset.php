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
 * Strings for component 'aassignfeedback_copyset', language 'en'
 *
 * @package assignfeedback_copyset
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['default'] = 'Enabled by default';
$string['default_help'] = 'If set, this tool plugin will be enabled by default for all new assignments.';
$string['enabled'] = 'Copy/Set grades';
$string['enabled_help'] = '
Allows to copy or set a grade for all users or a selecction of users depending on submision status. <br />
Manage and copy here raw grades for users from other assignment in this course.
Raw grades means that the value copied is the score before any gradebook calculation. If calculations differ for two assignments the same raw score wil result in a different final grade.
';
$string['pluginname'] = 'Copy/Set grades';
$string['menuentry'] = 'Copy/Set grades';

$string['all'] = 'Any';
$string['submitted'] = 'Submitted';
$string['notsubmitted'] = 'Not submitted';
$string['draft'] = 'Draft';
$string['graded'] = 'Graded';
$string['notgraded'] = 'Not graded';
$string['fail'] = 'Failed';
$string['pass'] = 'Passing';
$string['override'] = 'Override existing grades?';

$string['bysubmission'] = 'Submission status';
$string['bysubmission_help'] = '
Process for all students or only for users with or without a submission.';
$string['bygrading'] = 'Grading status';
$string['bygrading_help'] = '
Process for all students or only for users with or without a grade already granted.';
$string['bygrade'] = 'User grade';
$string['bygrade_help'] = '
Processs for all students or only for users with a failing/passing grade.';
$string['allgroups'] = 'All my groups';
$string['targetassign'] = 'For users of this assignment having ...';
$string['confirmmsg'] = 'You are about to {$a} for users that meet these conditions: ';
$string['confirmusers'] = 'Affected users are: ';
$string['confirmation'] = 'Do you want to proceed?';
$string['changed'] = 'Applyed changes to {$a} users';

// events
$string['eventcopyset'] = 'Copy/Set grades tool';
$string['eventgradesset'] = 'Grades set for multiple users';
$string['eventgradescopied'] = 'Grades copied for multiple users';
$string['eventextensionsgranted'] = 'Extensions granted for multiple users';

// set grades
$string['setgrade'] = '¿Which grades to set?';
$string['setgrades'] = 'Set grades in this Assignment';
$string['gradevalue'] = 'Grade to be set';
$string['gradesset'] = 'Grades for {$a} users has been set';
$string['advancedgradingmethod'] = 'This assignment is using an Advanced Grading Method and hence a default grade value should not be set  in advance for all users.';

// copy grades
$string['sourceassign'] = 'Copy from selected Source assignment';
$string['copygrade'] = 'Which grades to copy?';
$string['copygrades'] = 'Copy grades from other Assignment';
$string['copysource'] = 'Copy from Assignment';
$string['copiedgrades'] = 'Grades for {$a} users copied to this assignment';
$string['byothergrade'] = 'Calificaciones';
$string['byothergrade_help'] = '
Copiar solo las calificaciones indicadas a partir de la Tarea de origen.';

// set due extension
$string['dueextensions'] = 'Grant due date extensions';
$string['timevalue'] = 'New deadline date';
$string['timevalue_help'] = '
The new extended deadline should be a date after due or cutoff date.

If this field is applied in <b>disabled state</b> and overriding option is set, then all previously granted extensions will be <b>removed</b>.';
$string['extensionsset'] = 'Date extension has been set  for {$a} users';
$string['tfspecialperiod'] = 'ULPGC TF Special period';
$string['tfspecialperiod_help'] = '
Use it to grant duedate extensions according to rules of ULPGC Special submitting period.
If set, all other options will be disabled.';
$string['tfspecialperiod_config'] = '
If set, the tool will show settings for granting extensions according to ULPGC TF Special period submitting rules';
$string['tfstrictrule'] = 'Use TF strict rule';
$string['tfstrictrule_help'] = 'TF Special period strict rule states that students must have passes strictly MORE than half activities. 
If not enforcing strictly the user having passed JUST half the activities will be granted an extension.';
$string['done'] = 'Due extension set for {$a} users';
$string['enabledhidden'] = 'Enabled and hidden';
$string['enabledhidden_config'] = 'If activated, then the Copy and Set grades tool will be available for all Assignments,
without needing to enable/disable in each instance. In fact the checkmark in the configuration form will be hidden. 
Tools will be available according to the grading settings of the assignment (e.g. no tool if configured for not usisn grades).';

// set randommarkers
$string['randommarkers'] = 'Set random markers';
$string['removemarkers'] = 'Remove previous markers';
$string['bywstate'] = 'Maring workflow state';
$string['bywstate_help'] = '
Process only for students whose submission has a definite workflow state. Or mark all for any state.';
$string['eventrandommarkersset'] = 'Random markers set';
$string['eventmarkersimported'] = 'Markers imported';
$string['importmarkers'] = 'Import markers';
$string['removeexisting'] = 'Overwrite existing';
$string['removeexisting_help'] = 'If enabled, then the marker specified in the file will overwrite that assigned previously for a given user.';
$string['markersfile'] = 'Markers file';
$a = core_text::strtolower(get_string('user'));
$b = core_text::strtolower(get_string('marker', 'assign'));
$string['markersfile_help'] = 'The file to import must be a CSV text file with at least two colums. 
For instance: 

    '."{$a}, {$b}".'
    123456, 789321   
    256789, 456852

The first line contains de column identifiers 
The codes identifying the user are the IDnumbers of each user.  

';
$string['headercolumns'] = 'Header must include columns \'{$a->user}\' and \'{$a->marker}\'.';
$string['invalidimporter'] = 'Invalid importer: the header dot not contain {$a} columns or separator is invalid.';
$string['validmarkersassigns'] = 'Valid marker assignations found';
$string['nonvalidmarkersassigns'] = 'Lines not imported from uploaded file';
