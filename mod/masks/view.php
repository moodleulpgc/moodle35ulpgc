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
 * Display masks course elements
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This is view.php - add all view routines here (for generating output for author, instructor & student)


// ------------------------------------------------------------------------------
// includes

require_once('../../config.php');
require_once(dirname(__FILE__).'/locallib.php');
// require_once($CFG->libdir . '/completionlib.php');


// ------------------------------------------------------------------------------
// _GET / _POST parameters

$id         = optional_param( 'id', 0, PARAM_INT );           // Course module ID
$docPage    = optional_param( 'docpage', 0, PARAM_INT );      // the page of the document that the user shouls start on


// ------------------------------------------------------------------------------
// Data from moodle

$cm         = get_coursemodule_from_id( 'masks', $id, 0, false, MUST_EXIST );
$instance   = $DB->get_record( 'masks', array('id' => $cm->instance), '*', MUST_EXIST );
$course     = $DB->get_record( 'course', array('id' => $cm->course), '*', MUST_EXIST );


// ------------------------------------------------------------------------------
// Sanity tests

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/masks:view', $context);


// ------------------------------------------------------------------------------
// Prime the page object

// Setup PAGE properties
$PAGE->set_url('/mod/masks/view.php', array('id' => $cm->id));
$PAGE->set_title($course->fullname);
$PAGE->set_heading($instance->name);
$PAGE->requires->jquery();
$PAGE->requires->js_init_call('M.mod_masks.init');


// ------------------------------------------------------------------------------
// Include family css file

require_once('./mask_families_manager.class.php');
$families =  \mod_masks\mask_families_manager::getFamilies();
foreach($families as $family){
    $PAGE->requires->css($family->getCssFile());
}


// ------------------------------------------------------------------------------
// Include other css file

$PAGE->requires->css('/mod/masks/skin_enit.css');


// ------------------------------------------------------------------------------
// Moodle event logging & state update

$params = array(
    'context' => $context,
    'objectid' => $instance->id
);
$event = \mod_masks\event\course_module_viewed::create($params);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('masks', $instance);
$event->trigger();


// ------------------------------------------------------------------------------
// Fetch data from the database

require_once('database_interface.class.php');
$model = new mod_masks\database_interface;
$docData = $model->fetchDocData( $cm->id );
$isTeacher = has_capability('moodle/course:manageactivities', $context);
// if user is teacher get all masks, otherwise check masks userstate
$maskData = $model->fetchMaskData( $cm->id , $isTeacher );


// ------------------------------------------------------------------------------
// Render the page body to a string

// start by getting hold of a renderer object to work with
$renderer = $PAGE->get_renderer('mod_masks');

// if we're a teacher then render the teacher's view otherwise render the student's view
if ($isTeacher){
    $navPages = $model->getPages($cm->id);
    $pageBody = $renderer->renderTeacherView( $id, $docData, $navPages );
}else{
    // start by cleaning up and indexing the set of document pages
    $cleanerDocPages = array();
    $docPageIndex = array();
    for ($i = 0; $i < count($docData->pages); ++$i){
        $docPageIndex[$docData->pages[$i]->id] = $docData->pages[$i];
        if ( ( $docData->pages[$i]->flags & \mod_masks\PAGE_FLAG_HIDDEN ) == 0 ){
            $cleanerDocPages[] = $docData->pages[$i];
        }
    }
    // construct cleaned mask data, filtering out invisible masks
    $maskCount = 0;
    $cleanerMaskPages = array();
    foreach( $maskData->pages AS $pageId => $masks ){
        // only take masks from non-hidden pages
        if ( ( $docPageIndex[$pageId]->flags & \mod_masks\PAGE_FLAG_HIDDEN ) == 0 ){
            // look for non-hidden masks for the page
            $cleanerMaskPages[$pageId] = array();
            foreach( $masks AS $mask ){
                if ( ( $mask->flags & mod_masks\MASK_FLAG_HIDDEN ) == 0 ){
                    $cleanerMaskPages[$pageId][] = $mask;
                    ++$maskCount;
                }
            }
        }
    }

    // apply new cleaner mask data
    $maskData->pages = $cleanerMaskPages;
    $maskData->count = $maskCount;
    // apply new recleaner mask data
    $docData->pages  = $cleanerDocPages;

    // render the view
    $pageBody = $docData->isInitialised ?
        $renderer->renderStudentView( $id, $docData ) :
        $renderer->renderNotReadyMessage();
}


// ------------------------------------------------------------------------------
// Encode data and suchlike to be passed to the client as raw scipt

$jsPagesScript = \mod_masks\generateMasksJSPageData( $docData, 'M.mod_masks_pages');
$jsMasksScript = \mod_masks\generateMasksJSMaskData( $maskData, 'M.mod_masks_masks');

// write 'constant' information to the javascript
require_once( __DIR__ . '/locallib.php' );
$config     = \mod_masks\getConfig( $id );
$showGhosts = $config->showghosts;
$stateType  = $isTeacher ? 1 : 0;
$rawScript  = '';
$rawScript  .= 'M.mod_masks_state = { cmid: '.$id.', type: '.$stateType.', showGhosts: '.$config->showghosts.' };';
$rawScript  .= 'M.mod_masks_texts = '.json_encode( array(
    'pageExitPrompt'    => get_string( 'navigateaway', 'mod_masks' ),
    'uploadSuccess'     => get_string( 'alert_uploadsuccess', 'mod_masks' ),
    'reuploadSuccess'   => get_string( 'alert_reuploadsuccess', 'mod_masks' ),
    'uploadFail'        => get_string( 'alert_uploadfailed', 'mod_masks' ),
    'firstMaskAdded'    => get_string( 'alert_firstMaskAdded', 'mod_masks' ),
    'questionSaved'     => get_string( 'alert_questionSaved', 'mod_masks' ),
    'changesSaved'      => get_string( 'alert_changesSaved', 'mod_masks' ),
    'saveStyleChange'   => get_string( 'alert_saveStyleChange', 'mod_masks' ),
    'savePageHidden'    => get_string( 'alert_savePageHidden', 'mod_masks' ),
    'saveMaskHidden'    => get_string( 'alert_saveMaskHidden', 'mod_masks' ),
    'saveDeletion'      => get_string( 'alert_saveDeletion', 'mod_masks' ),
    'saveChanges'       => get_string( 'alert_saveChanges', 'mod_masks' ),
    'studentGradePass'  => get_string( 'alert_studentGradePass', 'mod_masks' ),
    'studentGradeDone'  => get_string( 'alert_studentGradeDone', 'mod_masks' ),
    'studentGradeFail'  => get_string( 'alert_studentGradeFail', 'mod_masks' ),
    'gradeNamePass'     => get_string( 'alert_gradeNamePass', 'mod_masks' ),
    'gradeNameToGo'     => get_string( 'alert_gradeNameToGo', 'mod_masks' ),
));
$jsStateScript = html_writer::script( $rawScript );


// ------------------------------------------------------------------------------
// Encode frame urls to be passed to the client as raw scipt

// write the doc data to the javascript
$attributes                 = array( 'id' => $id );
$frameUrls                  = array();
$frameUrls['add-mask']      = strval( new moodle_url( '/mod/masks/frame_new_mask.php', $attributes ) );
$frameUrls['upload']        = strval( new moodle_url( '/mod/masks/frame_upload.php', $attributes ) );
$frameUrls['reupload']      = $frameUrls['upload'];
$frameUrls['edit-question'] = strval( new moodle_url( '/mod/masks/frame_edit_mask.php', $attributes ) );
$frameUrls['click-mask']    = strval( new moodle_url( '/mod/masks/frame_click_mask.php', $attributes ) );
$frameUrls['save-layout']   = strval( new moodle_url( '/mod/masks/frame_save_layout.php', $attributes ) );
$frameUrls['shift-masks']   = strval( new moodle_url( '/mod/masks/frame_shift_masks.php', $attributes ) );
$rawScript                  = 'M.mod_masks_frames = ' . json_encode( $frameUrls );
$jsFramesScript             = html_writer::script( $rawScript );


// ------------------------------------------------------------------------------
// Output everything

echo $OUTPUT->header();

echo $jsStateScript;
echo $jsFramesScript;
echo $jsPagesScript;
echo $jsMasksScript;

echo $pageBody;

echo $OUTPUT->footer();

