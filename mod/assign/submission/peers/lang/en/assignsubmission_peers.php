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
 * Strings for component 'submission_peers', language 'en'
 *
 * @package assignsubmission_peers
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['default'] = 'Enabled by default';
$string['default_help'] = 'If set, this submission method will be enabled by default for all new assignments.';
$string['enabled'] = 'View peer submissions';
$string['enabled_help'] = '
If enabled, students will have a link to see peers submissions by other students.
A user will see the submissions by peers only after a submission have been recorded for that user.
';
$string['pluginname'] = 'Peer submissions';
$string['view'] = 'Peer submissions';
$string['table'] = 'Peer submissions';
$string['viewother'] = 'Peer submission';

$string['limitbymode'] = 'Show peer submissions';
$string['limitbymode_help'] = '
This option determine when are peers submissions showed to a given student.
A required condition is to have submitted oneself. After that the peers submissions lists may by displayed:

* Right after the student has subbmited his own work in final form (no more submissions allowed)
* Only after a teacher grade the submission by the student
* Right after the assignment deadline (without checking grades or submission status)

';
$string['limitbyfinal'] = 'after own final submission';
$string['limitbygrade'] = 'after been graded';
$string['limitbytime'] = 'after assignment deadline';
$string['limitbysubmission'] = 'after own submission (any)';
$string['viewpeersno'] = 'Peer submissions visible only {$a} ';
$string['viewpeerslink'] = 'View peer submissions table';

$string['viewpeerslimitdefault'] = 'When to to show peer submissions';
$string['configviewpeerslimitdefault'] = 'Will be used as default option in any new Assignment activity Settings form';
$string['eventpeerstableviewed'] = 'Peer submissions table viewed';
$string['eventpeerssubmissionviewed'] = 'Peer submission viewed';
