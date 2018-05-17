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
 * This page downloads quit attempt exported
 *
 * @package    mod_quiz
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/mod/quiz/lib.php');
require_once($CFG->dirroot.'/mod/quiz/locallib.php');


function quiz_export_PDF($options, $titlepage, $export) {
    global $CFG, $USER;
    require_once($CFG->libdir.'/pdflib.php');
    //require_once($CFG->libdir.'/tcpdf/config/tcpdf_config.php');

    $pdf = new pdf();

    // set document information
    $pdf->SetCreator('Moodle mod_quiz');
    $pdf->SetAuthor(fullname($USER));
    $pdf->SetTitle($options->examname);
    $pdf->SetSubject($options->coursename);
    $pdf->SetKeywords('moodle, quiz');

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // set default header data
    $pdf->SetHeaderData('', 0, $options->examname.' ('.$options->examdate.')' , $options->coursename);

    // set header and footer fonts
    $pdf->setHeaderFont(array('helvetica', '', 8));
    $pdf->setFooterFont(array('helvetica', '', 7));

    // set margins
    $topmargin = 10;
    $leftmargin = 15;
    $rightmargin = 15;
    $pdf->SetMargins($leftmargin, $topmargin, $rightmargin);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 25);

    // set image scale factor
    $pdf->setImageScale(1.25);

    // ---------------------------------------------------------

    // set font
    $pdf->SetFont('times', '', 12);

    // add titlepage
    $pdf->AddPage('', '', true);

    $pdf->writeHTML($titlepage['header'], false, false, true, false, '');

    if($options->examissue) {
        $pdf->writeHTML($titlepage['issue'], true, false, true, false, '');
    }
    if($options->examgrid) {
        $pdf->SetFont('freeserif', '', 10);
        $pdf->Ln(4);
        $y = $pdf->getY();
        $colwidth = ($pdf->getPageWidth() - $rightmargin - $leftmargin) * (0.25*$titlepage['gridcols']);
        $x = ($pdf->getPageWidth() - $colwidth)/2;
        $pdf->writeHTMLCell($colwidth, '', $x, $y, $titlepage['grid'], 0, 1, false, true, 'C');
    }

    $pdf->Ln(4);
    $pdf->AddPage('', '', true);
    $pdf->Ln(4);

    // add questionslist
    $topmargin = 20;
    $leftmargin = 10;
    $rightmargin = 10;
    $pdf->SetMargins($leftmargin, $topmargin, $rightmargin);

    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(true);
    $pdf->SetFont('freeserif', '', 9);

    $pdf->AddPage('', '', true);
    $pdf->resetColumns();
    if($options->exportcolumns > 1) {
        // $dim = $pdf->getPageDimensions();
        $width = ($pdf->getPageWidth() - 10 - $rightmargin - $leftmargin) / $options->exportcolumns;
        $pdf->setEqualColumns($options->exportcolumns, $width);
    }

    $number = 1;
    foreach($export as $question) {
        if($question{0} === '<') {
            $p = strpos($question, '>');
            $question = substr_replace($question, '>'.$number.'. ', $p,1);
        } else {
            $question = $number.'. '.$question;
        }
        $pdf->writeHTML($question, true, false, true, false, 'J');
        $pdf->Ln(4);
        $number++;
    }
    $pdf->Ln(10);

    $filename = clean_filename($options->coursename.'_'.$options->examname).'.pdf';
    $pdf->Output($filename, 'I');
}


function quiz_export_DOCX($options, $titlepage, $export) {



}


function quiz_export_HTML($options, $titlepage, $export) {
    global $CFG, $USER;

    $contenttype = 'text/html';

    switch($options->exporttype) {
        case 'html' : $contenttype = 'text/html'; break;
        case 'doc'  : $contenttype = 'application/msword'; break;
        case 'odt'  : $contenttype = 'application/vnd.oasis.opendocument.text'; break;
    }

    $filename = clean_filename($options->coursename.'_'.$options->examname).'.'.$options->exporttype;

    //header('Content-Description: File Transfer');
    header('Content-Type: '.$contenttype);
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    //header('Content-Length: ' . filesize($file));

    echo '<html>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
    echo '<body>';

    echo $titlepage['header'];

    if($options->examissue) {
        echo '<p>&nbsp;</p>';
        echo $titlepage['issue'];
    }
    if($options->examgrid) {
        echo '<p>&nbsp;</p>';
        echo $titlepage['grid'];
        echo '<p>&nbsp;</p>';
    }
    echo '<p>&nbsp;</p>';
    echo '<div style="page-break-before: always">&nbsp;</div>';

    echo '<ol start="1" type="1">';
    foreach($export as $question) {
        echo '<li>'.$question.'</li>';
        echo '<p class="separator">&nbsp;</p>';
    }
    echo '</ol>';
    echo '<p>&nbsp;</p>';

    echo '</body>';
    echo '</html>';
}


///////////////////////////////////////////////////////////////////////////////

@set_time_limit(300);
raise_memory_limit(MEMORY_HUGE);

$cmid = required_param('cmid', PARAM_INT);
$aid = required_param('aid', PARAM_INT);
$action = optional_param('action', 0, PARAM_INT); // 0 show form, 1 download

if (! $cm = get_coursemodule_from_id('quiz', $cmid)) {
    print_error('invalidcoursemodule');
}
if (! $quiz = $DB->get_record('quiz', array('id' => $cm->instance))) {
    print_error('invalidcoursemodule');
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

$attempt = $DB->get_record('quiz_attempts', array('id' => $aid), '*', MUST_EXIST);

$url = new moodle_url('/mod/quiz/export.php', array('cmid'=>$cm->id));
$returnurl = new moodle_url('/mod/quiz/view.php', array('id'=>$cm->id));

$attemptobj = quiz_attempt::create($aid);
$page = 0;
$page = $attemptobj->force_page_number_into_range($page);
$PAGE->set_url($attemptobj->attempt_url(null, $page));

$context = context_module::instance($cm->id);

// Check login.
require_login($attemptobj->get_course(), false, $attemptobj->get_cm());

// Check capabilities and block settings.
$attemptobj->require_capability('mod/quiz:manage');


// Check that this attempt belongs to this user.
if ($attemptobj->get_userid() != $USER->id) {
    if ($attemptobj->has_capability('mod/quiz:viewreports')) {
        redirect($attemptobj->review_url(null, $page));
    } else {
        throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'notyourattempt');
    }
}

// Check the access rules.
$accessmanager = $attemptobj->get_access_manager(time());
$accessmanager->setup_attempt_page($PAGE);
$output = $PAGE->get_renderer('mod_quiz');
$messages = $accessmanager->prevent_access();
if (!$attemptobj->is_preview_user() && $messages) {
    print_error('attempterror', 'quiz', $attemptobj->view_url(),
            $output->access_messages($messages));
}


/// export quiz header

$examdata = data_submitted();

$examdata->coursename = $course->shortname.' - '.$course->fullname;
//$examdata->examdate = userdate($examdata->examdate, get_string('strftimedate'));

$html = '<table cellspacing="0" cellpadding="4" border="1"  width:100%; >';
$html .= '<tr><td style="text-align:right;" width="14%"><p><br />'.get_string('lastname').'<br /></p></td><td colspan="3" width="86%"></td></tr>';
$html .= '<tr><td style="text-align:right;"><p><br />'.get_string('firstname').'<br /></p></td><td colspan="3"></td></tr>';
$html .= '<tr style="vertical-align:bottom;" ><td style="text-align:right;" width="14%">'.get_string('examname', 'local_ulpgcquiz').'</td><td width="45%"><strong>'.$examdata->examname.'</strong></td>'.
            '<td style="text-align:right;" width="%14"> '.get_string('examdate', 'local_ulpgcquiz').' </td><td width="27%"><strong>'.$examdata->examdate.'</strong></td></tr>';
$html .= '<tr><td style="text-align:right;">'.get_string('examcourse', 'local_ulpgcquiz').'</td><td><strong>'.$examdata->coursename.'</strong></td>'.
            '<td style="text-align:right;" >'.get_string('examdegree', 'local_ulpgcquiz').'</td><td><strong>'.$examdata->examdegree.'</strong></td></tr></table>';

$titlepage = array();
$titlepage['header'] = $html;
//$titlepage['group'] = '<p style="position:relative;">  <span style="border-style:solid; border-width:1px; text-align: right; position:absolute; right:0; bottom:0; width:3em;">'.$examdata->examgroup.'</span></p>';
$titlepage['issue'] = '<p style="position:relative;"><span style="border-style:solid; font-size:4em; border-width:1px; text-align: right; position:absolute; right:-5px; bottom:0; width:1.3em;"><strong>'.$examdata->examissue.'</strong></span></p>';
$titlepage['grid'] = '';


/// export quiz questions
// Get the list of questions needed by this page. // first done with all
$slots = $attemptobj->get_slots('all');

// Check.
if (empty($slots)) {
    throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'noquestionsfound');
}


$options = $attemptobj->get_display_options(false);
if($examdata->examanswers) {
    $options->rightanswer = 1;
}
/*
foreach($options as $key=>$value) {
    $options->$key = 0;
}
*/
/// TODO change $options as needed

$export = array();
$rightanswers = array();
$rowarray = array_fill(0, $examdata->examgridrows, '&#9744;');
foreach ($slots as $slot) {
    $qa = $attemptobj->get_question_attempt($slot);
    $question = $qa->get_question();
    $qtoutput = $question->get_renderer($PAGE);
    $html = $qtoutput->formulation_export($qa, $options);
    $export[] = $html;
    $type = $question->get_type_name();
    if(in_array($type, array('multichoice', 'multianswer'))) {
        if($examdata->examanswers) {
            $rowarray = array_fill(0, $examdata->examgridrows, '&#9744;');
            $rights = $question->get_correct_response();
            foreach($rights as $key=>$answer) {
                $rowarray[$answer] = '&#9746;';
            }
        }
        $rightanswers[] = $rowarray;
    } else {
        if($examdata->examanswers) {
            $rightanswers[] = $question->get_right_answer_summary(); // $question->get_correct_response();
        } else {
            $rightanswers[] = '';
        }
    }
}

foreach($rightanswers as $key => $value) {
    if(is_array($value)) {
        $rightanswers[$key] = '<span style="font-size: 1.5em;"  >'.implode('&nbsp;&nbsp;', $value).'</span>';
    }
}
$i = ord('a');
$rowheader = range('a', chr($i+$examdata->examgridrows-1));
$rowheader = '<span style="font-size: 1.25em;"  >'.implode('&nbsp;&nbsp;&nbsp;&nbsp;', $rowheader).'</span>';


if($examdata->examgrid) {
    $table = '';
    $numq = count($export);
    $cols = floor($numq/25) + (($numq % 25) > 0);

    $cols = 0;
    for ($i = 1; $i <= 4; $i++) {
        $rows = floor($numq/$i);
        if($rows <= 25 && (($numq % $i) == 0)) {
            $cols = $i;
            break;
        }
    }
    if(!$cols) {
        $cols = floor($numq/25) + (($numq % 25) > 0);
        $rows = 25;
    }

    $width1 = 20/$cols.'%';
    $width2 = 80/$cols.'%';

    $html = '<div style="margin: 0 auto;"><table cellspacing="0" cellpadding="2" border="1" align="center" >';
    $html .= '<tr>';
                for($col = 1; $col <= $cols; $col++) {
                    $html .= '<td width="'.$width1.'">&nbsp;</td><td width="'.$width2.'" style="text-align:center;">'.$rowheader.'</td>';
                }
    $html .= '</tr>';
    for($row = 1; $row <= $rows; $row++) {
        $html .= '<tr>';
                for($col = 1; $col <= $cols; $col++) {
                    $num = $rows*($col-1) + $row;
                    $html .= '<td style="text-align:right;">'.$num.'</td><td style="text-align:center;">'.$rightanswers[$num-1].'</td>';
                }
        $html .= '</tr>';
    }
    $html .= '</table></div>';

    $titlepage['grid'] = $html;
    $titlepage['gridcols'] = $cols;
    $titlepage['gridrows'] = $rows;
}

//print_object($rightanswers);

//echo $html;

switch ($examdata->exporttype) {
    case 'pdf' :    quiz_export_PDF($examdata, $titlepage, $export);
                    break;
    case 'docx' :   quiz_export_DOCX($examdata, $titlepage, $export);
                    break;

    default     :   quiz_export_HTML($examdata, $titlepage, $export);
}
