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

$id             = required_param('id', PARAM_INT);
$maskUpdates    = optional_param('masks', '{}', PARAM_TEXT);
$pageUpdates    = optional_param('pages', '{}', PARAM_TEXT);
$nextFrame      = optional_param('nextframe', '', PARAM_TEXT);
$requireConfirm = optional_param('confirm', 0, PARAM_INT);

// determine whether we have data or not (if we don't then we need to display the form)
$haveData       = array_key_exists( 'masks', $_GET );


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
// page rendering

// construct the 'move on to the next thing' js code to execute when we're all done
$jsCloseFrame       = 'parent.M.mod_masks.closeFrame();';
$jsGotoNextFrame    = 'window.location = "'.$nextFrame.'";';
$jsFinalise         = empty( $nextFrame ) ? $jsCloseFrame : $jsGotoNextFrame;

// if the required parameters weren't found then just resubmit the form
if ( $haveData !== true ){
    $hiddenFields = array( 'id' => $id, 'masks' => '', 'pages' => '', 'nextframe' => $nextFrame );

    // create the frame head including required libs and stylesheets
    require_once('./locallib.php');
    \mod_masks\beginFrameOutput();

    // construct the script to get the parent page to fill us in and submit the form
    $jsAutoSubmit = 'function masksAutoSubmit(){';
    $jsAutoSubmit .= 'document.getElementById("formprop-masks").value = parent.M.mod_masks.getMaskChanges();';
    $jsAutoSubmit .= 'document.getElementById("formprop-pages").value = parent.M.mod_masks.getPageChanges();';
    $jsAutoSubmit .= 'document.forms["frame-form"].submit();';
    $jsAutoSubmit .= '}';
    echo html_writer::script( $jsAutoSubmit );

    // construct save and nosave code chunks
    $saveCode   = 'masksAutoSubmit();';
    $noSaveCode = $jsFinalise;

    // initalise rendering helper, rendering an invisible form to hold the data to upload
    require_once('./form_writer.class.php');
    $formWriter = new \mod_masks\form_writer();
    $formWriter->openForm('frame_save_layout.php', $hiddenFields);
    $formWriter->closeForm(false);

    if ( $requireConfirm == 1 ){
        // construct a little page...

        // open root tag
        echo \html_writer::start_tag( 'div', array( 'id' => 'masks-frame', 'class' => 'auto-save-confirm' ) );

        // add page header
        $title = get_string('save-confirm-title', 'mod_masks');
        echo \html_writer::start_div( 'frame-header' );
        echo \html_writer::div( $title, 'frame-title' );
        echo \html_writer::end_div();

        // add page body
        $body = get_string('save-confirm-text', 'mod_masks');
        echo \html_writer::start_div( 'frame-body' );
        echo \html_writer::div( $body, 'frame-text frame-text-look' );
        echo \html_writer::end_div();

        // add page footer
        echo \html_writer::start_div( 'frame-footer' );
        $strCancel  = get_string( 'label_cancel', 'mod_masks' );
        $strNoSave  = get_string( 'label_nosave', 'mod_masks' );
        $strSave    = get_string( 'label_save', 'mod_masks' );
        echo \html_writer::tag( 'button', $strCancel, array( 'onclick' => $jsCloseFrame , 'class' => 'cancel-button' ) );
        echo \html_writer::tag( 'button', $strNoSave, array( 'onclick' => $noSaveCode , 'class' => 'danger-button standard-button' ) );
        echo \html_writer::tag( 'button', $strSave, array( 'onclick' => $saveCode , 'class' => 'confirmsavebutton standard-button normal-button' ) );
        echo \html_writer::end_div();

        // close root tag
        echo \html_writer::end_tag( 'div' );
    } else {
        // This is more or less a background upload - theer's no need to display anything in the form - we just want to run the auto submit asap
        echo html_writer::script( $saveCode );
    }

    // terminate output, appending call that will resize the iframe, etc
    \mod_masks\endFrameOutput();

    // stop execution here (as we're all done)
    die();
}

// ------------------------------------------------------------------------------
// form data processing

// Establish database connection
require_once('./database_interface.class.php');
$databaseInterface = new \mod_masks\database_interface;

// iterate over mask updates to store them away
$decodedMasks = json_decode( $maskUpdates );
foreach( $decodedMasks as $mask ){
    $dbRecord           = new \stdClass;
    $dbRecord->id       = $mask->id;
    $dbRecord->page     = $mask->page;
    $dbRecord->x        = $mask->x;
    $dbRecord->y        = $mask->y;
    $dbRecord->w        = $mask->w;
    $dbRecord->h        = $mask->h;
    $dbRecord->style    = $mask->style;
    $dbRecord->flags    = $mask->flags;
    $DB->update_record( 'masks_mask', $dbRecord );
}

// iterate over page updates to store them away
$decodedPages = json_decode( $pageUpdates );
foreach( $decodedPages as $page ){
    $dbRecord           = new \stdClass;
    $dbRecord->id       = $page->id;
    $dbRecord->flags    = $page->flags;
    $DB->update_record( 'masks_page', $dbRecord );
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
$navPages          = $databaseInterface->getPages($cm->id);
$encodednavData    = json_encode( $navPages );
$jsNavData         = 'var navData =' . $encodednavData . ';';
echo html_writer::script( $jsNavData );

// generate a bit of script to apply the changes to the server, clear out the change lists and close the iframe
$jsAction = '';
$jsAction .= 'parent.M.mod_masks.applyMaskData(maskData);';
$jsAction .= 'parent.M.mod_masks.applyPageData(pageData,navData);';
$jsAction .= 'parent.M.mod_masks.clearChangeLists();';
$jsAction .= 'parent.M.mod_masks.setAlertSuccess("changesSaved");';
$jsAction .= $jsFinalise;
echo html_writer::script( $jsAction );

