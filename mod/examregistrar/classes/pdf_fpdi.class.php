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
 * Main examregistrar PDF class
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../../config.php');
require_once($CFG->dirroot.'/lib/pdflib.php');
require_once($CFG->dirroot.'/mod/examregistrar/locallib.php');
require_once($CFG->dirroot.'/mod/examregistrar/fpdi/fpdi.php');


class examregistrar_pdf extends FPDI {
    /** @var array $replaces a collection of placeholders for %%name%% substitutions. */
    public $replaces = array();
    /** @var array $template a collection of template strings for %%name%% substitutions. */
    public $template = array();
    /** @var string $logoimage path/file string indicating location of the logo image file for header. */
    public $logoimage = 'mod/examregistrar/images/ET_ULPGC_logo.png';
    /** @var int $logowidth width parameter for header logo image. */
    public $logowidth = 55;


    public function Header() {
        if ($this->header_xobjid === false) {
            // start a new XObject Template
            $this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
            $headerfont = $this->getHeaderFont();
            $headerdata = $this->getHeaderData();
            $this->y = $this->header_margin;
            if ($this->rtl) {
                $this->x = $this->w - $this->original_rMargin;
            } else {
                $this->x = $this->original_lMargin;
            }
            if (($headerdata['logo']) AND ($headerdata['logo'] != K_BLANK_IMAGE)) {
                $imgtype = TCPDF_IMAGES::getImageFileType(K_PATH_IMAGES.$headerdata['logo']);
                if (($imgtype == 'eps') OR ($imgtype == 'ai')) {
                    $this->ImageEps(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
                } elseif ($imgtype == 'svg') {
                    $this->ImageSVG(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
                } else {
                    $this->Image(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
                }
                $imgy = $this->getImageRBY();
            } else {
                $imgy = $this->y;
            }
            $cell_height = round(($this->cell_height_ratio * $headerfont[2]) / $this->k, 2);
            // set starting margin for text data cell
            if ($this->getRTL()) {
                $header_x = $this->original_rMargin + ($headerdata['logo_width'] * 1.1);
            } else {
                $header_x = $this->original_lMargin + ($headerdata['logo_width'] * 1.1);
            }
            $cw = $this->w - $this->original_lMargin - $this->original_rMargin - ($headerdata['logo_width'] * 1.1);
            $this->SetTextColorArray($this->header_text_color);
            // header title
            $this->SetFont($headerfont[0], 'B', $headerfont[2] + 1);
            $this->SetX($header_x);
            $this->Cell($cw, $cell_height, $headerdata['title'], 0, 1, 'R', 0, '', 0);
            // header string
            $this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
            $this->SetX($header_x);
            $this->MultiCell($cw, $cell_height, $headerdata['string'], 0, 'R', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
            // print an ending header line
            $this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
            $this->SetY((2.835 / $this->k) + max($imgy, $this->y));
            if ($this->rtl) {
                $this->SetX($this->original_rMargin);
            } else {
                $this->SetX($this->original_lMargin);
            }
            $this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
            $this->endTemplate();
        }

        // print header template
        $x = 0;
        $dx = 0;
        if (!$this->header_xobj_autoreset AND $this->booklet AND (($this->page % 2) == 0)) {
            // adjust margins for booklet mode
            $dx = ($this->original_lMargin - $this->original_rMargin);
        }
        if ($this->rtl) {
            $x = $this->w + $dx;
        } else {
            $x = 0 + $dx;
        }
        $this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
        if ($this->header_xobj_autoreset) {
            // reset header xobject template at each page
            $this->header_xobjid = false;
        }
    }


    public function set_logo_path($logopath = '') {
        $this->logoimage = $logopath;
    }

    public function set_logo_width($width = 55) {
        $this->logowidth = $width;
    }

    /**
     * Adds a blank separator page to the PDF. The page may contain some text indicating items being separated
     *
     * @param string $content the information content to be displayed in the separator page
     * @return void
     */
    public function add_separator_page($content = '') {
        $separator = get_string('pageseparator', 'examregistrar');
        $line  = '<p style="text-align:right;" >   '.$separator.'  </p>';
        $content = '<div style="text-align:right;" >   '.$content.'  </div>';

        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        $topmargin = 20;
        $leftmargin = 15;
        $rightmargin = 15;
        $this->SetMargins($leftmargin, $topmargin, $rightmargin);
        $this->SetHeaderMargin(5);
        $this->SetFooterMargin(10);
        $this->startPageGroup();
        $this->AddPage('P', '', true);
        $this->Ln(24);
        $this->writeHTML($line, false, false, true, false, '');
        $this->Ln(24);
        $this->writeHTML($content, false, false, true, false, '');
        $this->Ln(24);
        $this->writeHTML($line, false, false, true, false, '');
    }


    /**
     * Set the template property for page content composition from printing options table
     *
     * @param object $examregistrar the object record from module table
     * @param string $type the type of template/PDF; any of room/exam/userlist/booking
     * @return void
     */
    public function set_template($examregistrar, $type) {
        global $DB;

        $examregid = examregistrar_get_primaryid($examregistrar);
        $template = array();
        if($template = $DB->get_records('examregistrar_printing', array('examregid'=>$examregid, 'page'=>$type), '', 'element, content, contentformat, visible')) {
            foreach($template as $key => $element) {
                $template[$key] = '';
                if($element->visible) {
                    if($element->contentformat) {
                        $template[$key] = format_text($element->content, $element->contentformat);
                    } else {
                        $template[$key] = format_string($element->content, $element->contentformat);
                    }
                }
            }
        } else {
            $template['header'] = ' %%period%% | %%session%%, %%date%% ';
            switch ($type) {
                case 'room' :
                                $template['roomtitle'] = '%%room%% (%%roomidnumber%%) ';
                                $template['examtitle'] = '%%programme%% - %%shortname%% - %%fullname%% ';
                                $template['listrow'] = ' | | ';
                                $template['additionals'] = '';
                                break;
                case 'exam' :
                                $template['examtitle'] = '%%programme%% - %%shortname%% - %%fullname%% ';
                                $template['venuesummary'] = '';
                                break;
                case 'userlist' :
                                $template['title'] = '%%period%%, %%session%% - %%date%% ';
                                break;
                case 'booking' :
                                $template['title'] = '%%period%%, %%session%% - %%date%% ';
                                break;
                case 'venue' :
                                $template['title'] = '%%period%%, %%session%% - %%date%% ';
                                break;
                case 'venuefax' :
                                $template['title'] = '%%period%%, %%session%% - %%date%% ';
                                break;
            }
        }
        $this->template = $template;
    }


    /**
     * Set the replaces property with initial data from imported params.
     *
     * @param object $examregistrar the object record from module table
     * @param array $params associative array of optional params
     * @param string $type the type of template/PDF; any of room/exam/userlist/booking
     * @return void
     */
    public function initialize_replaces($examregistrar, $params, $type='') {
        global $DB;
        $session = $DB->get_record('examregistrar_examsessions', array('id'=>$params['session']));
        $period  = $DB->get_record('examregistrar_periods', array('id'=>$session->period));

        $replaces = array('registrar'=>format_string($examregistrar->name),
                        'date'=>userdate($session->examdate, '%A, %d de %B de %Y'),
                        'time'=>$session->timeslot,
                        );

        $replaces['teacher'] = '';
        $replaces['room'] = '';
        $replaces['roomidnumber'] = '';
        $replaces['address'] = '';
        $replaces['seats'] = '';
        $replaces['seated'] = '';
        $replaces['numexams'] = '';
        $replaces['programme'] = '';
        $replaces['shortname'] = '';
        $replaces['fullname'] = '';
        $replaces['callnum'] = '';
        $replaces['examscope'] = '';
        $replaces['seated'] = '';
        $replaces['staff'] = '';
        $replaces['teacher'] = '';

        /// get session name & code
        if($period) {
            list($periodname, $periodidnumber) = examregistrar_get_namecodefromid($period->id, 'periods', 'period');
            $replaces['period'] = $periodname;
            $replaces['periodidnumber'] = $periodidnumber;
        }

        if($params['session']) {
            list($sessionname, $sessionidnumber) = examregistrar_get_namecodefromid($params['session'], 'examsessions', 'examsession');
            $replaces['session'] = $sessionname;
            $replaces['sessionidnumber'] = $sessionidnumber;

        }
        if($params['bookedsite']) {
            list($venuename, $venueidnumber) = examregistrar_get_namecodefromid($params['bookedsite'], 'locations', 'location');
            $replaces['venue'] = $venuename;
            $replaces['venueidnumber'] = $venueidnumber;
        }
        $roomname = '';
        if($params['room']) {
            list($roomname, $roomidnumber) = examregistrar_get_namecodefromid($params['room'], 'locations', 'location');
            $replaces['room'] = $roomname;
            $replaces['roomidnumber'] = $roomidnumber;
        }

        $replaces = array('registrar'=>format_string($examregistrar->name),
                        'period'=>$periodname, 'periodidnumber'=>$periodidnumber,
                        'session'=>$sessionname, 'sessionidnumber'=>$sessionidnumber,
                        'venue'=>$venuename, 'venueidnumber'=>$venueidnumber,
                        'date'=>userdate($session->examdate, '%A, %d de %B de %Y'),
                        'time'=>$session->timeslot,
                        );
        $this->replaces = $this->replaces + $replaces;

    }


    /**
     * Sets the initial & default values for PDF generation (metadata, margins, default font etc.)
     *
     * @param object $examregistrar the object record from module table
     * @param string $type the type of template/PDF; any of room/exam/userlist/booking
     * @return void
     */
    public function initialize_page_setup($examregistrar, $type) {
        global $CFG, $USER;

        // set document information

        $this->SetCreator('Moodle mod_examregistrar');
        $this->SetAuthor(fullname($USER));
        $this->SetTitle($examregistrar->name);
        $this->SetSubject("examregistrar $type PDF");
        if($this->replaces) {
            $this->SetSubject("Examregistrar $type PDF for: ".$this->replaces['period'].' / '.$this->replaces['session'].' at '. $this->replaces['venue']);
        } else {
            $this->SetSubject("Examregistrar $type PDF");
        }
        $this->SetKeywords("moodle examregistrar $type");

        //$this->setPrintHeader(false);
        //$this->setPrintFooter(false);

        // set default header data
        $string1 = $examregistrar->name;
        $string2 = get_string($type, 'examregistrar');
        if($this->replaces) {
            $string1 = $this->replaces['period'];
            $string2 = $this->replaces['session'].', '.$this->replaces['date'];
            if($type != 'exam') {
                $string2 .= ' / '.$this->replaces['venue'];
            }
        }

        $this->SetHeaderData($this->logoimage, $this->logowidth, $string1 , $string2);

        // set header and footer fonts
        $this->setHeaderFont(array('helvetica', '', 9));
        $this->setFooterFont(array('helvetica', '', 8));

        // set margins
        $topmargin = 20;
        $leftmargin = 15;
        $rightmargin = 15;
        $this->SetMargins($leftmargin, $topmargin, $rightmargin);
        $this->SetHeaderMargin(5);
        $this->SetFooterMargin(10);

        // set auto page breaks
        $this->SetAutoPageBreak(TRUE, 25);

        // set image scale factor
        $this->setImageScale(1.25);

        $this->setPrintHeader(true);
        $this->setPrintFooter(true);
        $this->SetFont('freeserif', '', 12);
    }


    /**
     * Adds room information pages to the PDF from roomallocation object
     *
     * @param object $room the roomallocation object to add to PDF
     * @param object $renderer the renderer to use
     * @param object $config the examregistrar config object
     * @return void
     */
    public function add_room_allocation($room, $renderer, $config) {
        global $CFG;

        $pdf = $this;
        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);
        $pdf->SetFont('freeserif', '', 12);
        $pdf->replaces['room'] = $room->name;
        $pdf->replaces['roomidnumber'] = $room->idnumber;
        $pdf->replaces['address'] = $room->address;
        $pdf->replaces['seats'] = $room->seats;
        $pdf->replaces['seated'] = $room->seated;
        $pdf->replaces['numexams'] = count($room->exams);

        if($room->parent) {
            list($pdf->replaces['parent'], $pdf->replaces['parentidnumber']) = examregistrar_get_namecodefromid($room->get_id(), 'locations', 'location');
        }

        $staffers = examregistrar_get_room_staffers($room->get_id(), $room->session);
        $users = array();
        foreach($staffers as $staff) {
            $name = fullname($staff);
            $role = ' ('.$staff->role.')';
            $users[] = $name.$role;
        }
        $pdf->replaces['staff'] = html_writer::alist($users);

        $header = explode('|', $pdf->template['header']);
        //print_object($header);
        $header = examregistrar_str_replace($pdf->replaces, $header);
        $examlist = '';
        if($room->exams) {
            $items = array();
            foreach($room->exams as $exam) {
                $head = $renderer->list_allocatedroomexam($exam, true);
                $items[] = $head;
            }
            $examlist = html_writer::alist($items, array('class'=>' roomexamlist '));
        }
        if($room->set_additionals()) {
            $i= 0;
            $items = array();
            foreach($room->additionals as $exam) {
                $head = $renderer->list_allocatedroomexam($exam, true);
                $items[] = $head;
            }
            $additionalslist = html_writer::alist($items, array('class'=>' roomexamlist '));
            $info = new stdClass;
            $info->users = $room->additionalusers;//count($room->additionals);
            $info->exams = count($room->additionals);
            $out = get_string('additionalusersexams', 'examregistrar', $info);
            $out .= $additionalslist;
            //$out .= html_writer::alist($items, array('class'=>' roomextraexamslist '));
            //$examlist .= html_writer::alist(array($out), array('class'=>' roomexamsnolist '));
        } else {
            $out = get_string('noadditionalexams', 'examregistrar');
        }
        $examlist .= html_writer::alist(array($out), array('class'=>' roomexamsnolist '));

        $pdf->replaces['examslist'] = $examlist;
        $pdf->replaces['additionals'] = 'additionals';

        $main = examregistrar_str_replace($pdf->replaces, $pdf->template['roomtitle']);

        // add titlepage for room
        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);
        $pdf->setHeaderTemplateAutoreset(true);
        $pdf->SetHeaderData($pdf->logoimage, $pdf->logowidth, $header[0] , $header[1]);
        //print_object($header[0]);
        $pdf->startPageGroup();
        $pdf->AddPage('', '', true);
        $pdf->Ln(10);
        $pdf->writeHTML($main, false, false, true, false, '');
        $pdf->Ln(10);

        // add room exams content one page for each main exam
        if($room->exams) {
            foreach($room->exams as $exam) {
                //print_object($exam);
                //print_object(" --- exam main room ");
                $pdf->replaces['programme'] = $exam->programme;
                $pdf->replaces['shortname'] = $exam->get_exam_name(false, true, false, false); //$exam->shortname;
                $pdf->replaces['fullname'] = $exam->fullname;
                $pdf->replaces['callnum'] = $exam->callnum;
                $pdf->replaces['examscope'] = $exam->examscope;
                $pdf->replaces['seated'] = $exam->seated;

                if($teachers = $exam->get_formatted_teachers()) {
                    $pdf->replaces['teacher'] = $teachers;
                }
                $main = examregistrar_str_replace($pdf->replaces, $pdf->template['examtitle']);

                $exam->set_users();

                $width = 100;
                $widths = explode('|', $pdf->template['colwidths']);
                $extraheads = explode('|',  examregistrar_str_replace($pdf->replaces, $pdf->template['listrow']));

//                 print_object($exam->users);
//                 print_object('Primera tabla main exam 1');

                $usertable = $renderer->print_exam_user_table($exam->users, $width, $widths, $extraheads);
//                 print_object('Primera tabla main exam 2');
                $pdf->AddPage('', '', true);
                $pdf->Ln(10);
                $pdf->writeHTML($main, false, false, true, false, '');
                $pdf->Ln(10);

                $examname = $exam->get_exam_name(true, true, true, false);
                $examname = $renderer->heading($examname); //$exam->programme.' - '.$exam->shortname.' - '.$exam->fullname);
                $pdf->writeHTML($examname, false, false, true, false, '');
                if($config->pdfwithteachers) {
                    $pdf->writeHTML($teachers, false, false, true, false, '');
                }
                $pdf->Ln(4);

                $margins = $pdf->getMargins();
                $y = $pdf->getY();
                $colwidth = ($pdf->getPageWidth() - $margins['right'] - $margins['left']) * $width/100;
                $x = ($pdf->getPageWidth() - $colwidth)/2;
                $pdf->writeHTMLCell(0, '', $x, $y, $usertable, 1, 1, false, true, 'C');
                $pdf->Ln(10);
            }

            // now adds additionals page
            if($room->additionals) {
                $main = examregistrar_str_replace($pdf->replaces, $pdf->template['additionals']);

                $pdf->AddPage('', '', true);
                $pdf->Ln(10);
                $pdf->writeHTML($main, false, false, true, false, '');
                $pdf->Ln(10);
                $additionals = new stdClass;
                $additionals->total = count($room->additionals);
                $index = 0;
                foreach($room->additionals as $exam) {
                    $index += 1;
                    $exam->set_users(true);
                    if($additionals->total > 1) {
                        $additionals->current = $index;
                        $additionalcount = get_string('additionalexam', 'examregistrar', $additionals);
                        if($index > 1) {
                            $pdf->AddPage('', '', true);
                            $pdf->Ln(10);
                            $pdf->Ln(10);
                            $pdf->Ln(10);
                        }
                        $pdf->writeHTML($additionalcount, true, false, true, false, '');
                    }

                    $width = 100;
                    $extraheads = explode('|',  examregistrar_str_replace($pdf->replaces, $pdf->template['listrow']));
                    $widths = explode('|', $pdf->template['colwidths']);
                    //print_object("tabla additional {$exam->shortname}");
                    $usertable = $renderer->print_exam_user_table($exam->users, $width, $widths, $extraheads, array(''=>''));

                    $examname = $exam->get_exam_name(true, true, true, false);
                    $examname = $renderer->heading($examname, 3); //$exam->programme.' - '.$exam->shortname.' - '.$exam->fullname, 3);



                    $pdf->writeHTML($examname, false, false, true, false, '');
                    if($config->pdfwithteachers) {
                        $teachers = $exam->get_formatted_teachers();
                        $pdf->writeHTML($teachers, false, false, true, false, '');
                    }
                    $pdf->Ln(4);
                    $margins = $pdf->getMargins();
                    $y = $pdf->getY();
                    $colwidth = ($pdf->getPageWidth() - $margins['right'] - $margins['left']) * $width/100;
                    $x = ($pdf->getPageWidth() - $colwidth)/2;
                    $pdf->writeHTMLCell(0, '', $x, $y, $usertable, 0, 1, false, true, 'C');
                    $pdf->Ln(24);
                }
            }

            // now add copies of exams to be taken in the room
            if($config->pdfaddexamcopy) {
                $examsdir = get_config('block_examsulpgc', 'examsdir');

                /// TODO eliminar TODO
                mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die ('Error de conexión: '. mysql_error());
                mysql_set_charset('utf8');
                mysql_select_db($CFG->dbname);

                $exams = $room->exams + $room->additionals;
                foreach($exams as $exam) {
                    $examname = $exam->get_exam_name(true, true, true, false); // $exam->programme.' - '-$exam->shortname.' - '.$exam->fullname;
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                    $pdf->startPageGroup();

                    $pdf->add_separator_page("<p> EXAMEN:  $examname </p>".
                                            '<p> Hacer '.$exam->seated.' copias de este examen </p>');

                    if($examrec = $DB->get_record('examregistrar_exams', array('id'=>$exam->get_id()))) {
                        $query = "SELECT fichero, fich_resp
                                    FROM examenes
                                    WHERE asig_id = {$examrec->shortname} AND aacada = {$examrec->annuality}  AND  conv = {$examrec->conv} AND parcial = {$examrec->parcial} AND estado = 5 ";
                                // print_object($query);
                        $result = mysql_query($query);
                        if($result) {
                            $row = mysql_fetch_row($result);
                            if($fileid = $row[0]) {
                                //print_object($fileid);
                                $parts = explode('-', $fileid);
                                $title = substr($parts[0], 1);
                                $shortname = $parts[1];
                                $conv = $parts[3];
                                $annual = substr($parts[4],0,6);
                                $pathname = $CFG->dataroot.$examsdir.'/'.$annual.$conv.'/'.$fileid;

                                // include a copy of external file
                                $pdf->startPageGroup();
                                $pagecount = $pdf->setSourceFile($pathname);
                                for ($i = 1; $i <= $pagecount; $i++) {
                                        $tplidx = $pdf->ImportPage($i);
                                        $s = $pdf->getTemplatesize($tplidx);
                                        $pdf->AddPage('P', array($s['w'], $s['h']));
                                        $pdf->useTemplate($tplidx);
                                }
                            } else {
                                $result = false;
                            }
                        }
                        if(!$result) {
                            $pdf->AddPage('', '', true);
                            $pdf->Ln(10);
                            $pdf->Ln(10);
                            $pdf->writeHTML('<p style="text-align:right;" >      Examen '.$examname.'  NO ENCONTRADO </p>', false, false, true, false, '');
                            $pdf->Ln(10);
                        }
                    }
                }
            }
        } // end of if room->exams
    }

}

