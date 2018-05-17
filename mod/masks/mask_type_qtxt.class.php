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


namespace mod_masks;

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__).'/mask_type.class.php');

class mask_type_qtxt extends mask_type{
    // -------------------------------------------------------------------------
    // data

    private $maskType       = 'qtxt';
    private $dbInterface    = null;
    private $fields         = null;


    // -------------------------------------------------------------------------
    // basics

    public function __construct(){
        // Establish database connection
        require_once(dirname(__FILE__).'/database_interface.class.php');
        $this->dbInterface = new database_interface;

        // Define the fields that are to appear in the question editing form
        $this->fields = array(
            'question'          => FIELD_BIGTEXTAREA + FIELD_REQUIRED,
            'answer'            => FIELD_BIGTEXTAREA + FIELD_REQUIRED,
            'goodanswerhint'    => FIELD_TEXTAREA + FIELD_FEEDBACK,
            'badanswerhint'     => FIELD_TEXTAREA + FIELD_FEEDBACK,
            'userhint'          => FIELD_TEXTAREA + FIELD_HINT,
        );
    }


    // -------------------------------------------------------------------------
    // mask_type API

    public function onNewMask( $id, $pageId ){
        // delegate work to generic method in base class
        $this->doNewMask( $id, $pageId, $this->maskType, $this->fields, $this->dbInterface, MASK_FLAGS_QUESTION );
    }

    public function onEditMask( $id, $maskId, $questionId, $questionData ){
        // delegate work to generic method in base class
        $this->doEditMask( $id, $maskId, $questionId, $questionData, $this->maskType, $this->fields, $this->dbInterface );
    }

    public function onClickMask( $questionId, $questionData, $hiddenFields, $isLastQuestion ){

        // has the user submitted an answer?
        if ( ! array_key_exists( 'response', $_GET ) ){
            // no, so we need to render the question

            // generate the answer html
            require_once(dirname(__FILE__).'/form_writer.class.php');
            ob_start();
            $formWriter = new \mod_masks\form_writer();
            $formWriter->addTextField( 'response', true );
            $answerHTML = ob_get_contents();
            ob_end_clean();

            // render the page (updating database etc as we do)
            $hintText       = property_exists( $questionData, 'userhint' ) ? $questionData->userhint : '';
            $questionText   = $questionData->question;
            $this->renderQuestionPage( $hintText, $questionText, $answerHTML, $hiddenFields, $this->dbInterface, $questionId );

            // return false as we don't have a result to evaluate yet
            return false;
        } else {
            // generate an array of possible answers
            $rawAnswers = $questionData->answer;
            $rawAnswers = strtolower( $rawAnswers );
            $rawAnswers = html_entity_decode( $rawAnswers );
            $rawAnswers = preg_replace( "/\r/", "\n", $rawAnswers );    // Normalise { \n\r, \n, \r } line endings
            $rawAnswers = preg_replace( "/\t/", " ", $rawAnswers );     // TAB => SPACE
            $rawAnswers = preg_replace( '/  +/', ' ', $rawAnswers );    // Multiple SPACE => single SPACE
            $rawAnswers = preg_replace( "/ \n/", "\n", $rawAnswers );   // Remove SPACE at end of line
            $rawAnswers = preg_replace( "/\n /", "\n", $rawAnswers );   // Remove SPACE at start of line
            $rawAnswers = preg_replace( '/. /', '.', $rawAnswers );     // Remove SPACE after a full stop
            $rawAnswers = preg_replace( "/\n\n+/", "\n", $rawAnswers ); // Multiple \n => single \n
            $rawAnswers = preg_replace( "/\n$/", '', $rawAnswers );     // Remove \n at end of text
            $rawAnswers = preg_replace( "/^\n/", '', $rawAnswers );     // Remove \n at start of text
            $options = explode("\n", $rawAnswers);

            // get hold of and clean up the user's answer
            $answer = $_GET[ 'response' ];
            $answer = trim( $answer );
            $answer = strtolower( $answer );
            $answer = preg_replace( '/  +/', ' ', $answer );
            $answer = preg_replace( '/. /', '.', $answer );

            // compare the answer to the correct answer
            $answerIsCorrect    = false;
            foreach( $options as $option ){
                $answerIsCorrect = ( $answerIsCorrect === true ) || ( $answer === $option );
            }

            // render the response page (updating database etc as we do)
            $goodAnswerResponse = ( property_exists( $questionData, 'goodanswerhint' ) ) ? $questionData->goodanswerhint : '';
            $badAnswerResponse  = ( property_exists( $questionData, 'badanswerhint' ) ) ? $questionData->badanswerhint : '';
            $hintText           = ( property_exists( $questionData, 'userhint' ) ) ? $questionData->userhint : '';
            if ( $badAnswerResponse == '' ){
                $badAnswerResponse  = $hintText;
                $hintText           = '';
            }
            $this->renderAnswerResponsePage( $answerIsCorrect, $goodAnswerResponse, $badAnswerResponse, $hintText, $this->dbInterface, $questionId, $isLastQuestion );

            // return true or false to represent 'question passed' of not
            return $answerIsCorrect;
        }
    }
}

