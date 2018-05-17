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
$maskId         = required_param('mid', PARAM_INT);
$questionId     = required_param('qid', PARAM_INT);
$isLastQuestion = required_param('islast', PARAM_INT);

// store away these parameters in order to be able to post them back with form data
$hiddenFields   = array( 'id' => $id, 'mid' => $maskId, 'qid' => $questionId, 'islast' => $isLastQuestion );


// ------------------------------------------------------------------------------
// Data from moodle

$cm         = get_coursemodule_from_id('masks', $id, 0, false, MUST_EXIST);
$instance   = $DB->get_record('masks', array('id' => $cm->instance), '*', MUST_EXIST);
$course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);


// ------------------------------------------------------------------------------
// Sanity tests

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/masks:view', $context);


// ------------------------------------------------------------------------------
// fetch the record to be editted from the database

require_once('./database_interface.class.php');
$dbInterface  = new \mod_masks\database_interface;
$questionData = $dbInterface->fetchQuestionData( $questionId );
$maskTypeName = $dbInterface->fetchQuestionType( $questionId );


// ------------------------------------------------------------------------------
// looking up and instantiate the mask type handler

require_once(dirname(__FILE__).'/mask_types_manager.class.php');
$handler      = \mod_masks\mask_types_manager::getTypeHandler( $maskTypeName );
if ( $handler  == null ){
    $typeList = json_encode( \mod_masks\mask_types_manager::getTypeNames() );
    throw new \Exception( 'Unknown mask type: "'. $maskTypeName. '" not in ' . $typeList );
}


// ------------------------------------------------------------------------------
// give the handler basic environment paramaters

$handler->applyMoodleEnvironment( $course, $cm, $instance );
$handler->setActiveMask( $maskId );


// ------------------------------------------------------------------------------
// pass control off to the handler

$passed = $handler->onClickMask( $questionId, $questionData, $hiddenFields, ($isLastQuestion == 1) );


