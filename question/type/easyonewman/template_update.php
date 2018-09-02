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
 * Defines the editing form for the easyonewman question type.
 *
 * @package    qtype
 * @subpackage easyonewman
 * @copyright  2014 onwards Carl LeBlond
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../config.php');
require_login(0, false);
global $OUTPUT;
$stagoreclip = required_param('stagoreclip', PARAM_TEXT);
if ($stagoreclip == 1) {
    $result = html_writer::start_tag('div',
                    array('id' => 'divneweclip', 'style' => 'background-image: url(\'type/easyonewman/pix/eclip.png\');'));
    $result .= html_writer::div('', 'dropablediv', array('id' => 'epos0'));
    $result .= html_writer::div('', 'dropablediv flipable', array('id' => 'epos1'));
    $result .= html_writer::div('', 'dropablediv', array('id' => 'epos2'));
    $result .= html_writer::div('', 'dropablediv', array('id' => 'epos3'));
    $result .= html_writer::div('', 'dropablediv flipable', array('id' => 'epos4'));
    $result .= html_writer::div('', 'dropablediv flipable', array('id' => 'epos5'));
    $result .= html_writer::end_tag('div');  // End divnew!
    $easyonewmanbuildstring = $result;
} else {

    $result = html_writer::start_tag('div',
                array('id' => 'divnew', 'style' => 'background-image: url(\'type/easyonewman/pix/stag.png\');'));
    $result .= html_writer::div('', 'dropablediv flipable', array('id' => 'pos0'));
    $result .= html_writer::div('', 'dropablediv', array('id' => 'pos1'));
    $result .= html_writer::div('', 'dropablediv', array('id' => 'pos2'));
    $result .= html_writer::div('', 'dropablediv', array('id' => 'pos3'));
    $result .= html_writer::div('', 'dropablediv', array('id' => 'pos4'));
    $result .= html_writer::div('', 'dropablediv flipable', array('id' => 'pos5'));
    $result .= html_writer::end_tag('div');  // End divnew!
    $easyonewmanbuildstring = $result;
}

// Add in the dragable div now and echo out.

$temp = file_get_contents('newman_dragable.html');
$temp = str_replace("moodleroot", $CFG->wwwroot, $temp);
$easyonewmanbuildstring .= $temp;

echo $easyonewmanbuildstring;
