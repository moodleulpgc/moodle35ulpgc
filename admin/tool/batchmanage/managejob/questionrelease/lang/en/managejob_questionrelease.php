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
 * questionrelease managejob lang strings.
 *
 * @package    managejob_questionrelease
 * @copyright  2013 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['pluginname'] = 'Validate/Release questions';
$string['pluginname_desc'] = 'Allow to specify some questions and to change settings on them, on selected courses.
Questions can be marked visible/hidden, validated or restored userid from idnumbers.';
$string['applyquestionrelease'] = 'Apply Validate/Release questions';
$string['applyquestionrelease_help'] = '
Allow to specify some questions and to change settings on them, on selected courses.
Questions can be marked visible/hidden, validated or restored userid from idnumbers.

Course selection based on category, visibility and other properties';

$string['question_selector'] = 'Questions to change';
$string['question_config'] = 'Question configuration';
$string['questionssettings'] = 'Questions selection options';
$string['categoryname'] = 'Questions category name';
$string['categoryname_help'] = 'Name of the question category you want to modify <br />
(verbatim, including HTML tags).<br />
May use SQL LIKE wildcards if next option checked. You need to explicty include "%" or "_" wildcards. <br />
If you target sections which name is empty, please specify the word "null".
';
$string['questionid'] = 'Questions IDs ';
$string['questionid_help'] = '
Comma separated list of question ID values as existing in prefix_question DB table';
$string['categoryparent'] = 'Category level';
$string['categorycontext'] = 'Category context';
$string['questionvisibility'] = 'Question visibility';
$string['questionhidden'] = 'Hidden question';
$string['topcategory'] = 'Only top categories';
$string['subcategory'] = 'Only sub categories';
$string['coursecategory'] = 'Contextos de curso';
$string['modcategory'] = 'Contextos de módulo';
$string['selectquestionconfig'] = 'Define question changes';
$string['questionvalidated'] = 'Validate questions';
$string['usersave'] = 'Save real user as idnumber';
$string['userrestore'] = 'Restore real user from idnumber';
$string['questionuserdata'] = 'Manage question authors identities';
$string['hidden'] = 'Hidden';
$string['uselike'] = 'Use SQL LIKE for name search';
$string['uselike_help'] = '
If enabled, then the above term will allow SQL search wildcards like "%" and "_".';