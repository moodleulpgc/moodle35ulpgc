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
 * Strings for the quizaccess_makeexamlock plugin.
 *
 * @package    quizaccess
 * @subpackage makeexamlock
 * @copyright  2016 Enrique Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


$string['makeexamlockingmsg'] = 'No attempts allowed. Use Edit quiz to compose a quiz and then go to Make Exam.';
$string['pluginname'] = 'Locking by MakeExam';
$string['gotomakeexam'] = 'Use Make Exam to generate an exam version';
$string['makeexamlock'] = 'Make Exam lock';
$string['makeexamlock_help'] = 'The Make Exam lock allows to prevent any student accesss to this quiz. 
Make Exam is a quiz report to generate Exam PDFs from Moodle (working with Exams registrer module). 

In activated, then no user attempts will be allowed. Only teachers could generate previews.';
$string['explainmakeexamlock'] = 'NO user attempts allowed. Quiz used only to generate Make Exam versions.';

