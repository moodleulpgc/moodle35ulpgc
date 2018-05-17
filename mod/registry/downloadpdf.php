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
 * Process a course generatig a PDF file with tracked module content
 *
 * @package    mod
 * @subpackage registry
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot.'/mod/registry/locallib.php');
require_once($CFG->libdir.'/pdflib.php');

@set_time_limit(300);
raise_memory_limit(MEMORY_HUGE);

$reg  = required_param('reg', PARAM_INT);  // registry instance ID
$regcid  = required_param('c', PARAM_INT);  // registered course instance ID
$issueid  = required_param('i', PARAM_INT);  // registered course instance ID

$registry  = $DB->get_record('registry', array('id' => $reg), '*', MUST_EXIST);
$course     = $DB->get_record('course', array('id' => $registry->course), '*', MUST_EXIST);
$cm         = get_coursemodule_from_instance('registry', $registry->id, $course->id, false, MUST_EXIST);

$regcourse = $DB->get_record('course', array('id' => $regcid), '*', MUST_EXIST);
$issue = $DB->get_record('tracker_issue', array('id' => $issueid), '*', MUST_EXIST);

require_login($course, true, $cm);
$modcontext = context_module::instance($cm->id);
$coursecontext = context_course::instance($course->id);
$regcontext = context_course::instance($regcourse->id);

if(is_enrolled($regcontext, null, 'moodle/course:manageactivities', true)) {
    require_capability('mod/registry:submit', $modcontext);
}else {
    require_capability('mod/registry:review', $modcontext);
    require_capability('mod/tracker:develop', $coursecontext);
}

$PAGE->set_url('/mod/registry/submissions.php', array('id' => $cm->id));
$PAGE->set_title(format_string($registry->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modcontext);

$returnurl = new moodle_url('/mod/tracker/view.php',
                            array('id' => $cm->id, 'view'=>'view', 'screen'=>'viewanissue', 'issueid'=>$issueid));

$title = html_writer::tag('h1', $regcourse->shortname.' - '.format_string($regcourse->fullname));
$title .= '<br />';
$title .= html_writer::tag('p', $registry->issuename);
$title .= '<br />';

$mods = registry_get_coursemods_mod($registry, $regcid);
$modtable = $registry->regmodule;

$modcontents = array();

foreach($mods as $key=>$mod) {
    $modcm = get_coursemodule_from_id($modtable, $mod->cmid, 0, false, MUST_EXIST);
    $context = context_module::instance($modcm->id);

    $content = '<hr />';
    $content .= html_writer::tag('h2', format_string($mod->name));
    $content .= '<br />';
    $intro = file_rewrite_pluginfile_urls($mod->intro, 'pluginfile.php', $context->id, 'mod_'.$modtable, 'intro', false);
    $content .= html_writer::tag('div', format_text($intro, $mod->introformat));

    /// aquí va el contenido interno
    $content .= registry_print_modcontent($registry, $mod, $context);

    $modcontents[$key] = $content;
}

    $titlename = $regcourse->shortname.' - '.format_string($regcourse->fullname);
    $subject = format_string($registry->issuename);

    $pdf = new pdf();
    // set document information
    $pdf->SetCreator('Moodle mod_registry');
    $pdf->SetAuthor('Registry');
    $pdf->SetTitle($titlename);
    $pdf->SetSubject($subject);
    $pdf->SetKeywords('moodle, registry');

    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(true);

    // set default header data
    $pdf->SetHeaderData('', 0, $titlename, $subject);

    // set header and footer fonts
    $pdf->setHeaderFont(array('helvetica', '', 8));
    $pdf->setFooterFont(array('helvetica', '', 7));

    // set margins
    $topmargin = 20;
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
    $pdf->SetFont('times', '', 11);

    // add titlepage
    $pdf->AddPage('', '', true);

    $pdf->writeHTML($title, true, false, true, false, '');
    $pdf->Ln(10);
    $pdf->Ln(10);

    foreach($modcontents as $content) {
        $pdf->writeHTML($content, true, false, true, false, '');
        $pdf->Ln(10);
    }

    $pdf->Ln(10);

    $filename = clean_filename($regcourse->shortname.'-'.format_string($regcourse->fullname).'-'.$registry->issuename).'.pdf';
    $pdf->Output($filename, 'I');
