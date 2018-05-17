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
 * MASKS PDF plugin Local library
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masks;

defined('MOODLE_INTERNAL') || die;


// ---------------------------------------------------------------------------------------------
// Utility routines

function generateMasksJSPageData( $docData, $jsVarName){
    $jsPages = array();
    foreach( $docData->pages as $page ){
        $jsPages[]          = $page;
    }
    $rawScript  = $jsVarName . ' = ' . json_encode( $jsPages );
    return \html_writer::script( $rawScript );
}

function generateMasksJSMaskData( $maskData, $jsVarName ){
    // write the doc data to the javascript
    $rawScript  = $jsVarName . ' = ' . json_encode( $maskData );
    return \html_writer::script( $rawScript );
}

function beginFrameOutput(){
    global $CFG;
    require_once("$CFG->libdir/formslib.php");
    echo \html_writer::start_tag( 'html' );
    echo \html_writer::start_tag( 'head' );
    echo \html_writer::tag( 'link', '', array( 'rel' => 'stylesheet', 'type' => 'text/css', 'href' => 'frame_styles.css' ) );
    echo \html_writer::tag( 'link', '', array( 'rel' => 'stylesheet', 'type' => 'text/css', 'href' => 'skin_enit.css' ) );

    // This isn't a stylesheet so including it here is a little out of place, but I need to be able to get the height
    // of a div reliably and this looks like the simplest solution right now
    echo \html_writer::tag( 'script', '', array( 'src' => 'https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js' ) );
    echo \html_writer::end_tag( 'head' );
    echo \html_writer::start_tag( 'body' );
}

function endFrameOutput(){
    // execute a bit of js directly to resize the div's parent
    $jsCode =
        'parent.M.mod_masks.iframeUpdateHeight($("#masks-frame").height());'.
            '$("html, body").animate({'.
                'scrollTop: $("#masks-frame").offset().top'.
            '}, 1);';
    echo \html_writer::script( $jsCode );
    echo \html_writer::end_tag( 'body' );
    echo \html_writer::end_tag( 'html' );
}

function getConfig( $cmid ){
    global $DB;

    // start by grabbing the global configuration
    $result = \get_config( 'mod_masks' );

    // look to see if we have any instance-specific configuration to add
    try{
        $cm             = get_coursemodule_from_id( 'masks', $cmid, 0, false, MUST_EXIST );
        $encodedConfig  = $DB->get_field( 'masks', 'config', array( 'id' => $cm->instance ) );
        $config         = json_decode( $encodedConfig, true );
        $result         = (object)array_merge( (array)$result, (array)$config );
    } catch( \Exception $e ){
    }

    // return the result
    return $result;
}
