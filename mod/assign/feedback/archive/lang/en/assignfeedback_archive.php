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
 * Strings for component 'feedback_archive', language 'en'
 *
 * @package   assignfeedback_archive
 * @copyright 2016 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['default'] = 'Enabled by default';
$string['default_help'] = 'If set, Archive tool will be enabled by default for all new assignments.';
$string['enabled'] = 'Archive submission';
$string['enabled_help'] = 'If enabled, the student will be able to archive the submission after been graded, 
in order to reopen and resubmit a new version after previous assessment.

Only applies if submission confirmation is required. 
If automatic re-submission until passing is activated then this tool gets disabled automatically.';
$string['pluginname'] = 'Archive submission';
$string['archive:store'] = 'Archive submission';
$string['maxattemptsreached'] = 'You have reached the limit of attempts. No more are possible.';
$string['reopen'] = 'Archive for reopening';
$string['reopen_help'] = 'You need to archive this submission in order to be able to re-submit an improved version.';
$string['reopenconfirm'] = 'Archive this submission and open for re-submitting';
$string['updategraded'] = 'Automatic submit upon grading';
$string['updategraded_help'] = 'If enabled, then if a user get a grade without previous submission, 
a SUBMITTED status is applied automatically when the student reads the grade, preventing edition of submission after grading. ';
$string['updategraded'] = 'Automatic submit upon grading';
$string['crontask'] = 'Draft closing after duedate job';
$string['eventsubmissionarchived'] = 'Submission archived';
$string['noarchiveallowed'] = 'No more attempts allowed';
$string['waitgrading'] = 'Submission must be graded for archiving.';
$string['checked_turnitin'] = 'Archive on Turnitin';
$string['checked_turnitin_help'] = 'If enabled, then file submission that has been checked by Turnitin will be set as archivable automatically. <br /> 
This setting onñy works for Assignments without grading, without assessment.';
