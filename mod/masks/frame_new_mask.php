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
$pageId         = required_param('pageid', PARAM_INT);
$maskTypeName   = required_param('masktype', PARAM_TEXT);


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
// looking up the mask type handler

require_once(dirname(__FILE__).'/mask_types_manager.class.php');
$handler      = \mod_masks\mask_types_manager::getTypeHandler( $maskTypeName );
if ( $handler  == null ){
    $typeList = json_encode( \mod_masks\mask_types_manager::getTypeNames() );
    throw new \Exception( 'Unknown mask type: '. $maskType. ' not in ' . $typeList );
}


// ------------------------------------------------------------------------------
// pass control off to the handler

$handler->onNewMask( $id, $pageId );

