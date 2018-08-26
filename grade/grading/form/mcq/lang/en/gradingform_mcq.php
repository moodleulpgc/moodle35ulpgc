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
 * @package     gradingform_mcq
 * @category    string
 * @copyright   2018 Enrique Castro ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['backtoediting'] = 'Back to editing';
$string['mcq:manage'] = 'Manage instance settings';
$string['pluginname'] = 'MCQ marking guide';
$string['definemarkingmcq'] = 'Define MCQ Formula parameters';
$string['definemcqmarking'] = 'MCQ marking guide';
$string['gradeheading'] = 'MCQ Formula editing';
$string['gradingof'] = '{$a} grading';
$string['mcqgrading'] = 'How MCQ Formula grading works';
$string['mcqgrading_help'] = 'With MCQ Formula grading students get an score curbed according to standard MCQ formula randomness calculation. 

In addition to correct responses, failures (but not blank responses) are counted and total score is reduced in failures/(choices - 1) points.

Optionally the score of non-MCQ questions is added.

';
$string['mcqstatus'] = 'Current MCQ formula marking status';
$string['name'] = 'Name';
$string['choices'] = 'Choices: ';
$string['choices_help'] = 'Number of choices in each MCQ question. 

Standard MCQ marking formula will reduce scores in a way proportional to failures/(choices - 1)';
$string['criterion'] = 'Criterion name';
$string['mcqmaxscore'] = 'Maximum MCQ mark: ';
$string['mcqmaxscore_help'] = 'The total number of questions of type MCQ. 
Assumes all questions have the same mark, each one point.

The total mark of the quiz is the sum of MCQ and non-MCQ marks. 
';
$string['nonmcqmaxscore'] = 'Maximum non-MCQ mark: ';
$string['nonmcqmaxscore_help'] = 'Maximum attainable mark in non-MCQ questions. 

This is the total mark, not the number or questions. 

If you have a question with 5 points and 5 questions with 1 point each, that makes 10 points in total. ';

$string['mcqscore'] = 'MCQ correct: ';
$string['mcqfails'] = 'MCQ fails: ';
$string['nonmcqscore'] = 'non-MCQ marks: ';

$string['save'] = 'Save';
$string['savemcq'] = 'Save MCQ formula and make it ready';
$string['savemcqdraft'] = 'Save as draft';
$string['err_scoreinvalid'] = 'The score given to \'{$a->criterianame}\' is not valid, the max score is: {$a->maxscore}';
$string['err_scoreisnegative'] = 'The score given to \'{$a->criterianame}\' is not valid, empty or negative values are not allowed';
$string['err_failsinvalid'] = 'The score given to \'{$a->criterianame}\' is not valid. The sum of correct plus fail cannot be more than: {$a->maxscore}';
$string['fullmcqformula'] = ' Correct - (Fails / (Choices - 1 )) + non-MCQ';
$string['mcqformula'] = 'Correct - (Fails / (Choices - 1 ))';
$string['needregrademessage'] = 'The MCQ marking guide definition was changed after this student had been graded. The student can not see this MCQ marking guide until you check the MCQ marking guide and update the grade.';
$string['regrademessage1'] = 'You are about to save changes to a MCQ marking guide that has already been used for grading. Please indicate if existing grades need to be reviewed. If you set this then the MCQ marking guide will be hidden from students until their item is regraded.';
$string['regrademessage5'] = 'You are about to save significant changes to a MCQ marking guide that has already been used for grading. The gradebook value will be unchanged, but the MCQ marking guide will be hidden from students until their item is regraded.';
$string['regradeoption0'] = 'Do not mark for regrade';
$string['regradeoption1'] = 'Mark for regrade';
$string['restoredfromdraft'] = 'NOTE: The last attempt to grade this person was not saved properly so draft grades have been restored. If you want to cancel these changes use the \'Cancel\' button below.';

