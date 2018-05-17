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
 * Display masks plugin frame
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../config.php');


// ------------------------------------------------------------------------------
// _GET / _POST parameters

$id                 = required_param('id', PARAM_INT);
$currentOrderKey    = required_param('currentorderkey', PARAM_INT);
$toRight            = optional_param('roright', true, PARAM_BOOL);
$retrieveMasks      = optional_param('retrievemasks', false, PARAM_BOOL);

// ------------------------------------------------------------------------------
// Data from moodle

$cm         = get_coursemodule_from_id('masks', $id, 0, false, MUST_EXIST);
$instance   = $DB->get_record('masks', array('id' => $cm->instance), '*', MUST_EXIST);
$course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);


// ------------------------------------------------------------------------------
// Sanity tests

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/masks:addinstance', $context);


// ------------------------------------------------------------------------------
// Data processing

// Establish database connection
require_once('./database_interface.class.php');
$databaseInterface = new \mod_masks\database_interface;

if($retrieveMasks){
    $toRight = false;
    $shiftResult = $databaseInterface->retrieveMasks($cm->id, $currentOrderKey);
}else{
    $shiftResult = $databaseInterface->shiftPageMasks($cm->id, $currentOrderKey, $toRight);
}

// ------------------------------------------------------------------------------
// output generation

// encode and output the updated mask data
$newMaskData        = $databaseInterface->fetchMaskData($id, true);
$encodedMaskData    = json_encode( $newMaskData );
$jsMaskData         = 'var maskData =' . $encodedMaskData . ';';
echo html_writer::script( $jsMaskData );

// encode and output the updated page data
$newPageData        = $databaseInterface->fetchDocData($id)->pages;
$encodedPageData    = json_encode( $newPageData );
$jsPageData         = 'var pageData =' . $encodedPageData . ';';
echo html_writer::script( $jsPageData );

// encode and output the updated nav data
$navPages = $databaseInterface->getPages($cm->id);
$encodednavData    = json_encode( $navPages );
$jsNavData         = 'var navData =' . $encodednavData . ';';
echo html_writer::script( $jsNavData );

// Alert message
if($toRight){
    $alertMessage = get_string('alert_shiftRight','mod_masks');
    if($shiftResult == true){
        $alertMessage .= '<br>'. get_string('alert_falsePage','mod_masks');
    }
}else{
    $alertMessage = get_string('alert_shiftLeft','mod_masks');
}

// generate a bit of script to apply the changes to the server, clear out the change lists and close the iframe
$jsAction = '';
$jsAction .= 'parent.M.mod_masks.applyMaskData(maskData);';
$jsAction .= 'parent.M.mod_masks.applyPageData(pageData, navData);';
$jsAction .= 'parent.M.mod_masks.clearChangeLists();';
if($toRight){
    $jsAction .= 'parent.M.mod_masks.gotoPage('.($currentOrderKey+1).');';
}else if(!$toRight && $shiftResult){
    $jsAction .= 'parent.M.mod_masks.gotoPage('.($currentOrderKey-1).');';
}
$jsAction .= 'parent.M.mod_masks.setAlertSuccess("'.$alertMessage.'");';
$jsAction .= 'parent.M.mod_masks.closeFrame();';
echo html_writer::script( $jsAction );
