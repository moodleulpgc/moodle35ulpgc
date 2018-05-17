<?php
/**
 * Cadenas de texto de la extensión de sincronización de la ULPGC
 *
 * @package local_ulpgcquiz
 * @copyright  2016 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'ULPGC quiz';
$string['ulpgcquiz:manage'] = 'Manage ULPGC quizs';

// settings
$string['quizsettings'] = 'ULPGC quiz settings';
$string['advancedquizs'] = 'Enable advanced quiz interface';
$string['explainadvancedquizs'] = 'If active the quizs will show additional tools & behaviors.';

$string['sectionempty'] = 'Empty section';
$string['confirmsectionempty'] =  'Are you sure you want to empty the \'{$a}\' section? This will remove all questions from list.';
$string['sectionemptied'] = 'Section emptied. Deleted {$a} questions';
$string['eventsectionemptied'] = 'Quiz section emptied';


// export related strings 
$string['exportdownload'] = 'Export download links';
$string['exportoptions'] = 'Export options';
$string['exportquiz'] = 'Export quiz';
$string['exporttype'] = 'Export file type';
$string['exporttype_help'] = '
Document type for file exported.

PDF format preserve images and links.

MS-Word doc and OpenDocument odt formats are based on HTML conversion and may need further adjusting after download,
including reviewing embeded image links.';
$string['exporthtml'] = 'as HTML';
$string['exportpdf'] = 'as PDF';
$string['exportdocx'] = 'as DOCX';
$string['exportdoc'] = 'as DOC (html)';
$string['exportodt'] = 'as ODT (html)';
$string['examname'] = 'Exam call';
$string['examname_help'] = '
The printed name for this examination paper';
$string['examdate'] = 'Exam date';
$string['examissue'] = 'Exam letter';
$string['examissue_help'] = '
Moodle can generate several random issues of a quiz.
In that case this lettering will identify the issue for marking purposes.';
$string['exportcolumns'] = 'Exam columns';
$string['exportcolumns_help'] = '
The number of columns in the question list.
This setting is only applicable to PDF document export.
In other export documents such formatting have to be applyed by editing the exported file.';
$string['examdegree'] = 'Exam degree';
$string['examdegree_help'] = '
The degree and year of the course corresponding to this exam paper.';
$string['examcourse'] = 'Exam course';
$string['examcourse_help'] = '
The course this exam paper belongs to';
$string['examgrid'] = 'Include response grid';
$string['examgrid_help'] = '
The response grid is a table with question numbers and letter choices for recording answers.';
$string['examgridrows'] = 'Number of choices';
$string['examgridrows_help'] = '
The maximun number of checkbox choices included in each question row.';
$string['examanswers'] = 'Include right answers';
$string['examanswers_help'] = '
If the right answers are included the document will shor clearly the correct response for each question after the question text.

If response grid is also active, a marked grid, with correct options checked, will be generated.';
$string['exportnoattempt'] = 'There isn\'t any preview attempt to export. <br />
You have to start and finish a preview attempt of this quiz to be a ble to export it.';
$string['downloadexam'] = 'Download exam';
$string['downloadwithanswers'] = 'Download exam with answers';
$string['bestavgn'] = 'Average of {$a} best';
