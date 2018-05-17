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
 * masks masked pdf activity Base class for mask types
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masks;

defined('MOODLE_INTERNAL') || die;

// use bit mask to compose field type
define( 'mod_masks\FIELD_OPTIONAL'      , 0 );
define( 'mod_masks\FIELD_REQUIRED'      , 1 );
define( 'mod_masks\FIELD_TEXT'          , 2 );
define( 'mod_masks\FIELD_TEXTAREA'      , 4 );
define( 'mod_masks\FIELD_BIGTEXTAREA'   , 8 );
define( 'mod_masks\FIELD_NOHEADING'     , 0x100 );
define( 'mod_masks\FIELD_FEEDBACK'      , 0x1000 );
define( 'mod_masks\FIELD_HINT'          , 0x2000 );

// constants defining bit sets
define('mod_masks\ALL_FIELD_TYPES'  , FIELD_FEEDBACK | FIELD_HINT );
define('mod_masks\ALL_INPUT_TYPES'  , FIELD_TEXT | FIELD_TEXTAREA | FIELD_BIGTEXTAREA );


// constants that define which set of fields to NOT present in question editing forms
define('mod_masks\FIELDS_NONE'      , FIELD_FEEDBACK | FIELD_HINT );
define('mod_masks\FIELDS_H'         , FIELD_FEEDBACK );
define('mod_masks\FIELDS_HF'        , 0 );

abstract class mask_type{

    // -------------------------------------------------------------------------
    // Protected Data

    // moodle execution environment (determined at the start of the page and stored here for use as required)
    protected $course           = null;
    protected $cm               = null;
    protected $masksInstance    = null;
    protected $activeMask       = null;

    // the mask family - by default this is a question but for notes and suchlike the field can be overridden
    protected $maskTypeFamily   = 'question';


    // -------------------------------------------------------------------------
    // Public API

    public function applyMoodleEnvironment( $course, $cm, $masksInstance ){
        $this->course       = $course;
        $this->cm           = $cm;
        $this->masksInstance = $masksInstance;
    }

    public function setActiveMask( $maskId ){
        $this->activeMask   = $maskId;
    }

    public function getMaskTypeFamily(){
        return $this->maskTypeFamily;
    }


    // -------------------------------------------------------------------------
    // Abstract Public API

    /* Method used to process a 'new mask' page
     * @param integer $id a course module instance id ($cm->id)
     * @param integer $pageId the masks_page db row to which the new mask should be assigned
     */
    abstract function onNewMask( $id, $pageId );

    /* Method used to process an 'edit mask' page
     * @param integer $id a course module instance id ($cm->id)
     * @param integer $maskId the masks_mask db row to be editted
     * @param integer $questionId the masks_question db row corresponding to $maskId
     * @param object $questionData the form data retrieved from the database
     */
    abstract function onEditMask( $id, $maskId, $questionId, $questionData );

    /* Method used to process a studen't interaction with a mask (the 'click on mask' page)
     * @param integer $questionId the masks_question db row corresponding to $maskId
     * @param object $questionData the form data retrieved from the database
     * @param array $hiddenFields the set of hidden parameters that need to be posted back with form submission
     * @param boolean $isLastQuestion a value that is true if student is attempting to answer the last question that needs answering
     * @return boolean true if the action results in mask being closed else false
     */
    abstract function onClickMask( $questionId, $questionData, $hiddenFields, $isLastQuestion );


    // -------------------------------------------------------------------------
    // Protected helper functions

    protected function doNewMask( $id, $pageId, $maskType, $fields, $dbInterface, $flags ){
        // do we have a complete data set?
        $haveData = $this->haveData( $fields );

        // if the required parameters weren't found then just resubmit the form
        if ( $haveData !== true ){
            // render the form
            $hiddenFields = array( 'id' => $id, 'pageid' => $pageId );
            $this->renderEditForm( $id, 'new_'.$maskType, $maskType, 'frame_new_mask.php', $fields, $_GET, array(), $hiddenFields );
        } else {
            // fetch the data that has been submitted and pack it into a json record for storage
            $newData        = $this->fetchSubmittedData( $fields );
            $jsonData       = json_encode( $newData );

            // write new record to database and retrieve updated full data snapshot
            $resultData = new \stdClass;
            $resultData->newMask = $dbInterface->addMask( $id, $pageId, $maskType , $jsonData, $flags );
            $resultData->maskData = $dbInterface->fetchMaskData( $id, true );

            // encode the result data and the script to apply it
            $jsData = 'var masksData =' . json_encode( $resultData );
            echo \html_writer::script( $jsData );
            $jsAction = '';
            $jsAction .= 'parent.M.mod_masks.applyMaskData(masksData.maskData);';
            $jsAction .= 'parent.M.mod_masks.selectMask(masksData.newMask);';
            $jsAction .= 'parent.M.mod_masks.contextMenuShow();';
            $jsAction .= 'parent.M.mod_masks.closeFrame();';
            if ($resultData->maskData->count == 1){
                $jsAction .= 'parent.M.mod_masks.setAlertSuccess("firstMaskAdded");';
            }
            echo \html_writer::script( $jsAction );
        }
    }

    protected function doEditMask( $id, $maskId, $questionId, $questionData, $maskType, $fields, $dbInterface ){
        // do we have a complete data set?
        $haveData = $this->haveData( $fields );

        // if the required parameters weren't found then just resubmit the form
        if ( $haveData !== true ){
            // render the form
            $hiddenFields = array( 'id' => $id, 'mid' => $maskId, 'qid' => $questionId );
            $this->renderEditForm( $id, 'edit_'.$maskType, $maskType, 'frame_edit_mask.php', $fields, $_GET, (array)$questionData, $hiddenFields );
        } else {
            // fetch the data that has been submitted and pack it into a json record for storage
            $newData = $this->fetchSubmittedData( $fields );

            // write modified record to database
            $resultData = new \stdClass;
            $dbInterface->updateQuestion( $questionId, $newData );

            // encode the result data and the script to apply it
            $jsData = 'var masksData =' . json_encode( $resultData );
            echo \html_writer::script( $jsData );
            $jsAction = '';
            $jsAction .= 'parent.M.mod_masks.setAlertSuccess("questionSaved");';
            $jsAction .= 'parent.M.mod_masks.closeFrame();';
            echo \html_writer::script( $jsAction );
        }
    }

    protected function fetchSubmittedData( $fields ){
        // encode question data
        $newData = new \stdClass;
        foreach( $fields as $field => $flags ){
            $fieldValue = htmlentities( $_GET[ $field ] );
            // The following 2 lines have been commented for now as the result caused display bugs on firefox relating to the size of the iframe tag
            // $regex = array('/&lt;(\/?)(b|i|strong|strike|em|li|ul|ol|p|h1|h2|h3|h4|h5|br|hr)\s*(\/?)&gt;/');
            // $fieldValue = preg_replace( $regex, '<\1\2\3>', $fieldValue );
            $newData->$field = $fieldValue;
        }

        return $newData;
    }

    protected function renderEditForm( $id, $contextName, $maskType, $target, $fields, $refData0, $refData1, $hiddenFields ){
        // setup the form writer helper object
        require_once('./form_writer.class.php');
        $formWriter = new \mod_masks\form_writer( $refData0, $refData1 );

        // include stylesheets etc
        require_once('./locallib.php');
        \mod_masks\beginFrameOutput();

        // fetch config parameters and build a bitmask to use as a filter for eliminating fields that are not desired....
        require_once( __DIR__ . '/locallib.php' );
        $config         = \mod_masks\getConfig( $id );
        $fieldFilter    = ~( $config->maskedit & ALL_FIELD_TYPES );

        // open page root tag
        $family = \mod_masks\mask_types_manager::getTypeFamily($maskType);
        echo \html_writer::start_tag( 'div', array( 'id' => 'masks-frame', 'class' => 'mask-edit-form mask-edit-'.$family ) );

        // add page header
        $strTitle = get_string( 'title_'.$contextName, 'mod_masks' );
        echo \html_writer::start_div( 'frame-header' );
        echo \html_writer::div( $strTitle, 'frame-title' );
        echo \html_writer::end_div();

        // open a frame body tag to contain all of our question content
        $formWriter->openForm($target, $hiddenFields);
        $formWriter->addHidden('masktype', $maskType);
        echo \html_writer::start_div( 'frame-body' );

        // construct the help text
        $helpText = get_string( 'edithelp_'.$maskType, 'mod_masks' );
        $helpMask = $fieldFilter;
        foreach( $fields as $field => $flags ){
            // filter down the flags to the ones that related to potential help text
            $fieldHelpFlags = $flags & ALL_FIELD_TYPES;
            // if we don't yet have a help text for this field type then add one
            if ( ( $helpMask & $fieldHelpFlags ) != 0 ){
                switch ( $fieldHelpFlags ){
                    case FIELD_FEEDBACK:
                        $helpText = $helpText . '<br><br>' . get_string( 'edithelpfeedback', 'mod_masks' );
                        break;

                    case FIELD_HINT;
                        $helpText = $helpText . '<br><br>' . get_string( 'edithelphint', 'mod_masks' );
                        break;

                    default:
                        throw new \Exception( "unrecognised conceptual type for field: $fieldHelpFlags > $field" );
                }
                // register that we have included help text for this field type
                $helpMask = $helpMask & ~$fieldHelpFlags;
            }
        }

        // display a help section with a toggle button and a hideable text zone
        echo \html_writer::start_div( 'frame-section hint-section frame-text-look' );
        echo \html_writer::start_div( 'frame-sub-section text-sub-section' );
        echo \html_writer::div( $helpText, 'hint frame-text' );
        echo \html_writer::end_div();
        echo \html_writer::end_div();

        // open the question section
        echo \html_writer::start_div( 'frame-section question-section' );

        // add the visible form fields
        foreach( $fields as $field => $flags ){
            // filter out fields based on site config options
            if ( ( $flags & $fieldFilter ) != $flags ){
                continue;
            }
            $noHeadingFlag  = ( $flags & FIELD_NOHEADING ) != 0;
            $requiredFlag   = ( $flags & FIELD_REQUIRED ) != 0;
            $fieldType      = $flags & ALL_INPUT_TYPES;
            switch ( $fieldType ){
                case FIELD_TEXT:
                    $formWriter->addTextField( $field, $requiredFlag, $noHeadingFlag );
                    break;

                case FIELD_TEXTAREA;
                    $formWriter->addTextArea( $field, $requiredFlag, 2 );
                    break;

                case FIELD_BIGTEXTAREA;
                    $formWriter->addTextArea( $field, $requiredFlag, 5 );
                    break;

                default:
                    throw new \Exception( "unrecognised form field type for field: $flags > $fieldType > $field" );
            }
        }

        // close the question section
        echo \html_writer::end_div();

        // close the form and frame body, adding submit buttons and suchlike
        echo \html_writer::end_div();
        $clickScript = 'document.getElementById("masks-frame").classList.toggle("show-hint");document.getElementById("toggle-help").classList.toggle("show-help");';
        $strShowHelp = get_string( 'label_showhelp', 'mod_masks' );
        $strHideHelp = get_string( 'label_hidehelp', 'mod_masks' );
        $buttonTxt   = \html_writer::span( $strShowHelp, 'btn-hint-show btn-hint' ) . \html_writer::span( $strHideHelp, 'btn-hint-hide btn-hint' );
        echo \html_writer::start_div( 'frame-sub-section button-sub-section' );
        echo \html_writer::tag( 'button', $buttonTxt, array( 'type' => 'button', 'class' => 'hide-toggle', 'onclick' => $clickScript , 'id' => 'toggle-help', 'tabindex' => 99 ) );
        echo \html_writer::end_div();
        $formWriter->closeForm();

        // close page root tag
        echo \html_writer::end_tag( 'div' );

        // terminate the output, closing body tag
        \mod_masks\endFrameOutput();
    }

    protected function renderInfoPage( $title, $body, $footer, $cssClass, $buttonCode ){
        // include stylesheets etc
        require_once('./locallib.php');
        \mod_masks\beginFrameOutput();

        // open root tag
        echo \html_writer::start_tag( 'div', array( 'id' => 'masks-frame', 'class' => $cssClass ) );

        // add page header
        echo \html_writer::start_div( 'frame-header' );
        echo \html_writer::div( $title, 'frame-title' );
        echo \html_writer::end_div();

        // add page body
        echo \html_writer::start_div( 'frame-body' );
        echo \html_writer::div( $this->renderBodyText( $body ), 'frame-text frame-text-look' );
        $strClose = get_string( 'label_close', 'mod_masks' );
        echo \html_writer::tag( 'button', $strClose, array( 'onclick' => $buttonCode , 'class' => 'standard-button close-button' ) );
        echo \html_writer::end_div();

        // add page footer
        if ( $footer != '' ){
            echo \html_writer::start_div( 'frame-footer' );
            echo \html_writer::div( $footer, 'frame-footer-text' );
            echo \html_writer::end_div();
        }

        // close root tag
        echo \html_writer::end_tag( 'div' );

        // terminate the output, closing body tag
        \mod_masks\endFrameOutput();
    }

    protected function renderQuestionPage( $hintText, $questionText, $answerHTML, $hiddenFields, $dbInterface, $questionId ){
        global $USER;

        // update the database to inform it that we have seen the question
        $isFirstAttempt = $dbInterface->isFirstQuestionAttempt( $USER->id, $questionId );
        $updatedGrades  = $dbInterface->updateUserQuestionState( $this->cm, $USER->id, $questionId, 'VIEW' );
        echo \html_writer::script( $this->getGradeUpdateScript( $updatedGrades ) );

        // include stylesheets etc
        require_once('./locallib.php');
        \mod_masks\beginFrameOutput();

        // open root tag
        echo \html_writer::start_tag( 'div', array( 'id' => 'masks-frame', 'class' => 'question once' ) );

        // add page header containing question text
        echo \html_writer::start_div( 'frame-header' );
        echo \html_writer::div( '', 'question-icon' );
        echo \html_writer::div( $this->renderBodyText( $questionText ), 'question-text question-text-look' );
        echo \html_writer::end_div();

        // open a frame body tag to contain the answer space
        echo \html_writer::start_div( 'frame-body' );

        // setup the form writer helper object
        require_once('./form_writer.class.php');
        $formWriter = new \mod_masks\form_writer();

        // open the form and add hidden fields
        $formWriter->openForm('frame_click_mask.php', $hiddenFields);

        // add answer field
        echo \html_writer::start_div( 'answer-section' );
        echo \html_writer::start_div( 'answer' );
        echo $answerHTML;
        echo \html_writer::end_div();
        echo \html_writer::end_div();

        // close the form, adding submit buttons and suchlike
        $formWriter->closeForm();

        // close body tag
        echo \html_writer::end_div();

        // add page footer with the hint text
        if ( ! empty( $hintText ) && !$isFirstAttempt ){
            echo \html_writer::start_div( 'frame-footer hint-section' );
            echo \html_writer::div( $hintText, 'hint-look hint' );
            echo \html_writer::end_div();
        }

        // close root tag
        echo \html_writer::end_tag( 'div' );

        // terminate the output, closing body tag
        \mod_masks\endFrameOutput();
    }

    protected function renderAnswerResponsePage( $answerIsCorrect, $goodAnswerResponse, $badAnswerResponse, $hintText, $dbInterface, $questionId, $isLastQuestion ){
        // inform the database interface of the student's good or bad response
        global $USER;

        if ( $answerIsCorrect ){
            // update the database to inform it that we have passed the question
            $updatedGrades      = $dbInterface->updateUserQuestionState( $this->cm, $USER->id, $questionId, 'PASS' );
            $gradeUpdateScript  = $this->getGradeUpdateScript( $updatedGrades );

            // display a congratulations message and show a button to close the window and dismiss the mask
            if ( $isLastQuestion ){
                $grade              = $dbInterface->gradeUser( $this->cm, $USER->id );
                $responseType       = ( $grade == 100.0 ) ? 'perfect' : 'final';
                $strResponseTitle   = get_string( $responseType . 'answer_title', 'mod_masks' );
                $strResponseText    = get_string( $responseType . 'answer_text', 'mod_masks' );
                $strText            = ( ! empty( $goodAnswerResponse ) ) ? ( $goodAnswerResponse . '<br><br>' . $strResponseText ) : $strResponseText;
            } else {
                $isPerfectAnswer    = $dbInterface->isFirstQuestionAttempt( $USER->id, $questionId );
                $responseType       = ( $isPerfectAnswer === true ) ? 'good' : 'pass';
                $strResponseTitle   = get_string( $responseType . 'answer_title', 'mod_masks' );
                $strResponseText    = get_string( $responseType . 'answer_text', 'mod_masks' );
                $strText            = ( ! empty( $goodAnswerResponse ) ) ? $goodAnswerResponse : $strResponseText;
            }
            $this->renderInfoPage( $strResponseTitle, $strText, '', $responseType.'-answer correct-answer answer', $gradeUpdateScript.'parent.M.mod_masks.closeMask(); parent.M.mod_masks.closeFrame();' );
        } else {
            $isFirstAttempt     = $dbInterface->isFirstQuestionAttempt( $USER->id, $questionId );

            // update the database to inform it that we have failed the question
            $updatedGrades      = $dbInterface->updateUserQuestionState( $this->cm, $USER->id, $questionId, 'FAIL' );
            $gradeUpdateScript  = $this->getGradeUpdateScript( $updatedGrades );

            // display a wrong answer message and show a button to close the window without dismissing the mask
            $responseType       = ( $isFirstAttempt === true ) ? 'wrong' : 'bad';
            $strBadAnswerTitle  = get_string( $responseType . 'answer_title', 'mod_masks' );
            $strBadAnswerText   = get_string( $responseType . 'answer_text', 'mod_masks' );
            $strText            = ( ! empty( $badAnswerResponse ) ) ? $badAnswerResponse : $strBadAnswerText;
            $this->renderInfoPage( $strBadAnswerTitle, $strText, $hintText, $responseType.'-answer incorrect-answer answer', $gradeUpdateScript.'parent.M.mod_masks.closeFrame();' );
        }
    }

    protected function renderBodyText( $txt ){
        $txt = str_replace("\r", "<br>", $txt);
        return "<span>$txt\n</span>";
    }

    protected function getGradeUpdateScript( $updatedGrades ){
        $result = ( $updatedGrades == 0 ) ? '' : "parent.M.mod_masks.setMaskState( $this->activeMask, $updatedGrades );";

        if ( ( $updatedGrades & MASKS_STATE_PASS ) == MASKS_STATE_PASS ){
            $result .= 'parent.M.mod_masks.onMaskPass();';
        }
        else if ( ( $updatedGrades & MASKS_STATE_DONE + MASKS_STATE_FAIL ) == MASKS_STATE_DONE + MASKS_STATE_FAIL ){
            $result .= 'parent.M.mod_masks.onMaskDoneAfterFail();';
        }
        else if ( ( $updatedGrades & MASKS_STATE_FAIL ) == MASKS_STATE_FAIL ){
            $result .= 'parent.M.mod_masks.onMaskFail();';
        }
        else if ( ( $updatedGrades & MASKS_STATE_DONE ) == MASKS_STATE_DONE ){
            $result .= 'parent.M.mod_masks.onMaskDone();';
        }

        return $result;
    }

    protected function haveData( $fields ){
        $haveData = true;
        foreach( $fields as $field => $flags ){
            $required = ( $flags & FIELD_REQUIRED ) != 0;
            $haveData = $haveData && ( $required == false ) || ( array_key_exists( $field, $_GET ) && !empty( $_GET[$field] ) );
        }
        return $haveData;
    }
}

