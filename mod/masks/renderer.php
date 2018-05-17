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
 * mod_masks - renderer class
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class mod_masks_renderer extends plugin_renderer_base {

    /**
     * Render the teachers' view
     *
     * @param object $docData The description of the document to be rendered (array of page records with image names, etc)
     * @return string The HTML to display.
     */
    public function renderTeacherView( $id, $docData, $navData ) {
        global $OUTPUT;
        $result = '';

        // open the main page area
        $result .= $this->openPage();

        // render the header bar (with menus and page nav)
        $result .= $this->openHeader();
        $result .= $this->renderMenuBar( $id, $docData, true, $navData );
        $result .= $this->closeHeader();

        // add notifications
        $stralertMsg = get_string( 'alert_uploadnofile', 'mod_masks' );
        $result .= $OUTPUT->notification( $stralertMsg, 'notifysuccess');

        // render the main page area
        $result .= $this->openBody();
        $result .= $this->renderPageView( $docData, true );
        $result .= $this->closeBody();

        // close the main page area
        $result .= $this->closePage();

        // return output
        return $result;
    }

    /**
     * Render the students' view
     *
     * @param object $docData The description of the document to be rendered (array of page records with image names, etc)
     * @return string The HTML to display.
     */
    public function renderStudentView( $id, $docData ) {
        global $OUTPUT;
        $result = '';

        // open the main page area
        $result .= $this->openPage();

        // render the header bar (with menus and page nav)
        $result .= $this->openHeader();
        $result .= $this->renderMenuBar( $id, $docData, false );
        $result .= $this->closeHeader();

        // add notifications
        $result .= $OUTPUT->notification('This is the STUDENT view', 'notifysuccess');

        // render the main page area
        $result .= $this->openBody();
        $result .= $this->renderPageView( $docData , false);
        $result .= $this->closeBody();

        // close the main page area
        $result .= $this->closePage();

        // return output
        return $result;
    }

    /**
     * Render an empty window with a 'document not ready' message
     *
     * @return string The HTML to display.
     */
    public function renderNotReadyMessage() {
        global $OUTPUT;
        $result = '';

        // add notifications
        $strNotification = get_string( 'notReadyMsg', 'mod_masks' );
        $result .= $OUTPUT->notification($strNotification, 'notifyproblem');

        // return output
        return $result;
    }


    // -----------------------------------------------------------------------------------
    // Private utilities - page wrapper

    private function openPage(){
        $result = '';

        // open the page
        $rootClasses = 'hide-mask-actions-group hide-layout-save-group hide-mask-style-note hide-mask-style-question';
        $result .= html_writer::start_tag( 'div', array( 'id' => 'masks', 'class' => $rootClasses ) );

        // add a little dummy div that can be used to record a scroll position and returned to as an anchor
        $result .= html_writer::tag( 'div', '', array( 'id' => 'masks-scroll-ref' ) );

        // open the overlay space
        $result .= html_writer::start_tag( 'div', array( 'id' => 'page-overlay' ) );
        $result .= html_writer::tag( 'div', '', array( 'id' => 'page-mask' ) );

        // add iframe overlay
        $result .= html_writer::start_tag( 'div', array( 'id' => 'popup-parent-iframe' ) );
        $result .= html_writer::tag( 'iframe', '', array( 'id' => 'popup-mdl-frame' ) );
        $result .= html_writer::end_tag( 'div' );

        // close the overlay space
        $result .= html_writer::end_tag( 'div' );

        return $result;
    }

    private function closePage(){
        $result = '';

        // close the page
        $result .= html_writer::end_tag( 'div' );

        return $result;
    }


    // -----------------------------------------------------------------------------------
    // Private utilities - header components

    private function openHeader(){
        $result = '';
        $result .= html_writer::start_div('masks-header');
        return $result;
    }

    private function closeHeader(){
        $result = '';
        $result .= html_writer::end_div();
        return $result;
    }

    private function renderPageNavWidget( $numPages = null, $pages = null ){
        $result = '';

        // open page nav area tags
        $result .= html_writer::start_div('page-nav');

        // buttons for navigating left
        $result .= html_writer::span( '<<', 'nav-button nav-left', array( 'click-action' => 'nav-to-left-end' ) );
        $result .= html_writer::span( '<', 'nav-button nav-left', array( 'click-action' => 'nav-to-left' ) );

        // page selection drop-down menu
        $result .= $this->openMenuFromButton( 'page-select', $this->renderPageName( '', 'masks-page-num' ) );
        if(!$pages){
            // student view
            for( $i = 0; ( $i == 0 ) || ( $i < $numPages ); ++$i ){
                $attributes = array( 'click-action' => 'goto-page', 'page' => $i );
                $pageName = $this->renderPageName( $i+1, 'page-name-'.$i );
                $result .= $this->renderMenuEntry( $pageName, $attributes );
            }
        } else {
            // teacher view
            foreach($pages as $page){
                $classes = '';
                if( $page->docpage == 0 ){
                    // false page
                    $classes .= ' false-page ';
                } else if(( $page->flags & \mod_masks\PAGE_FLAG_HIDDEN )!= 0) {
                    // hidden page
                    $classes .= ' hidden-page';
                }

                if ( $page->docpage > 0 ) {
                    $attributes = array( 'click-action' => 'goto-page', 'page' => $page->orderkey , 'class' => $classes );
                }else{
                    $attributes = array( 'class' => $classes );
                }
                $pageName = $this->renderPageName( $page->orderkey+1, 'page-name-'.$page->orderkey );
                $result .= $this->renderMenuEntry( $pageName, $attributes );
            }
        }

        $result .= $this->closeMenu();

        // buttons for navigating right
        $result .= html_writer::span( '>', 'nav-button nav-right', array( 'click-action' => 'nav-to-right' ) );
        $result .= html_writer::span( '>>', 'nav-button nav-right', array( 'click-action' => 'nav-to-right-end' ) );

        // close page nav area tags
        $result .= html_writer::end_div();

        return $result;
    }

    private function renderMenuBar( $id, $docData, $includeTeacherOptions , $navData = null){
        global $OUTPUT;
        $result = '';

        // lookup module instance configuration
        require_once( __DIR__ . '/locallib.php' );
        $config = \mod_masks\getConfig( $id );

        // open menu bar
        $menuBarAttributes = array();
        if(!$includeTeacherOptions){
            $menuBarAttributes['id'] = 'student-menu-bar';
        }
        $result .= html_writer::start_div('menu-bar', $menuBarAttributes);

        // open the group that is to float left
        $result .= $this->openMenuBarGroup('left-group');

        // add Add Mask menu
        if ($includeTeacherOptions){
            $strAddMaskMenu  = get_string( 'add-mask-menu', 'mod_masks' );
            $spanAddMaskMenu = html_writer::span( $strAddMaskMenu, 'bold' );
            $result .= $this->openMenuFromButton('add-mask', $spanAddMaskMenu , 'standard-button normal-button' );
            require_once(dirname(__FILE__).'/mask_types_manager.class.php');
            $typeNames = \mod_masks\mask_types_manager::getTypeNames();
            foreach($typeNames as $typeName){
                $configFieldName = 'disable_'.$typeName;
                if (isset($config->$configFieldName) && $config->$configFieldName != 0 ){
                    continue;
                }
                $family = \mod_masks\mask_types_manager::getTypeFamily($typeName);
                $icon = $OUTPUT->pix_url('create_' . $family, 'mod_masks');
                $result .= $this->renderActionMenuEntry( 'add-mask', $icon , '', array( 'masktype' => $typeName ) );
            }
            $result .= $this->closeMenu();
        }else{ // if student, add grade and completion container
            $result .= html_writer::start_div( 'grade-container' );
            // Correct Answers
            $result .= html_writer::start_tag( 'div', array( 'id' => 'correct-answers-container' ) );
            $correctAnswersStr = get_string('gradeNamePass', 'mod_masks');
            $result .= $this->renderCircleValue(0, $correctAnswersStr, 'correct-answers-circle' );
            $result .= html_writer::end_div();

            // Questions Remaining
            $result .= html_writer::start_tag( 'div', array( 'id' => 'questions-remaining-container' ) );
            $questionRemainingStr = get_string('gradeNameToGo', 'mod_masks');
            $result .= $this->renderCircleValue(0, $questionRemainingStr, 'questions-remaining-circle' );
            $result .= html_writer::end_div();

            // Congratulation
            $result .= html_writer::start_tag( 'div', array( 'id' => 'congratulation-container' ) );
            $congratulationStr = get_string('header_congratulations_text', 'mod_masks');
            $result .= $congratulationStr;
            $result .= html_writer::end_div();

            $result .= html_writer::end_div();
        }

        // add mask move / resize save-confirmation button
        if ($includeTeacherOptions){
            $result .= $this->openMenuBarGroup('layout-save-group');
            $saveIcon = $OUTPUT->pix_url('t/backup');
            $result .= $this->renderActionButton( 'save-layout', $saveIcon , 'save-layout-button standard-button normal-button' , true );
            $result .= $this->closeMenuBarGroup();
        }

        // close the basic menu button group
        $result .= $this->closeMenuBarGroup();

        // open the group that is to float right
        $result .= $this->openMenuBarGroup('right-group');

        // add the options menu
        $result .= $this->openMenuFromIcon( 'options', $OUTPUT->pix_url('reglage', 'mod_masks') );
        if ( $includeTeacherOptions === true ){
            $result .= $this->renderToggleMenuEntry( 'page-hidden' );
            $icon = $OUTPUT->pix_url('reupload', 'mod_masks');
            $result .= $this->renderActionMenuEntry( 'reupload', $icon , 'reupload-entry' );
            $rightIcon = $OUTPUT->pix_url('shiftright', 'mod_masks');
            $result .= $this->renderActionMenuEntry( 'masks-shift-right', $rightIcon , 'masks-shift-right-entry' );
            $leftIcon = $OUTPUT->pix_url('shiftleft', 'mod_masks');
            $result .= $this->renderActionMenuEntry( 'masks-shift-left', $leftIcon , 'masks-shift-left-entry' );
            $retrieveIcon = $OUTPUT->pix_url('retrieve_masks', 'mod_masks');
            $result .= $this->renderActionMenuEntry( 'masks-retrieve-masks', $retrieveIcon , 'masks-retrieve-masks-entry' );
        } else {
            $icon = $OUTPUT->pix_url('reshow_masks', 'mod_masks');
            $result .= $this->renderActionMenuEntry( 'reshow-masks', $icon , 'reshow_masks-entry' );
            $icon = $OUTPUT->pix_url('rehide_masks', 'mod_masks');
            $result .= $this->renderActionMenuEntry( 'rehide-masks', $icon , 'rehide_masks-entry' );
        }
        $result .= $this->closeMenu();

        // add the page nav here
        if ( $includeTeacherOptions === true ){
            $result .= $this->renderPageNavWidget( null, $navData );
        } else {
            $result .= $this->renderPageNavWidget( count( $docData->pages ) );
        }

        // close the cog menu button group
        $result .= $this->closeMenuBarGroup();

        // close menu bar
        $result .= html_writer::end_div();

        return $result;
    }

    private function renderPageName( $pageNumber, $pageNameId ){
        // lookup loca strings
        $strPage = get_string( 'page', 'mod_masks' );

        $attributes = array( 'id' => $pageNameId );
        $result = '';
        $result .= html_writer::start_div( 'nav-page-name', $attributes );
        $result .= html_writer::start_span( 'nav-page-word' );
        $result .= $strPage.' ';
        $result .= html_writer::span( $pageNumber, 'nav-num-word' );
        $result .= html_writer::end_span();
        $result .= html_writer::end_div();

        return $result;
    }


    // -----------------------------------------------------------------------------------
    // Private utilities - body components

    private function openBody(){
        $result = '';
        $result .= html_writer::start_div('masks-body');
        return $result;
    }

    private function closeBody(){
        $result = '';
        $result .= html_writer::end_div();
        return $result;
    }

    private function renderPageView( $docData, $includeTeacherOptions ){
        // calculate the max page width
        $pageWidth = 0;
        foreach( $docData->pages as $page ){
            $pageWidth = max( $pageWidth, $page->w );
        }

        // setup alternative image-width styles for controlling the size of the page image
        $styles = '';
        $styles .= '#masks-page-space{width:100%}';
        $styles .= '#masks.full-size #masks-page-space{width:'.$pageWidth.'px}';

        // render the result
        $result = '';
        $result .= html_writer::tag( 'style', $styles );
        $result .= html_writer::start_tag( 'div', array( 'id' => 'masks-page-space' ) );
        $result .= html_writer::start_div( 'img-parent' );
        $result .= html_writer::empty_tag( 'img' );
        $result .= html_writer::end_div();
        $result .= html_writer::tag( 'div', '', array( 'id' => 'masks-masks' ) );
        $result .= $this->renderContextMenu( $includeTeacherOptions );
        $result .= html_writer::end_tag('div');
        return $result;
    }

    private function renderContextMenu( $includeTeacherOptions ){
        require_once(dirname(__FILE__).'/mask_families_manager.class.php');
        global $OUTPUT;

        // for student view there is currently no context menu
        if ( $includeTeacherOptions !== true ){
            return;
        }

        // open the context menu
        $result = '';
        $result .= html_writer::start_tag( 'div', array( 'id' => 'masks-context-menu', 'class' => 'hidden' ) );
        $result .= html_writer::start_div( 'context-menu-pane', array( 'click-action' => 'mask-action' ) );

        // add menu buttons
        $editIcon = $OUTPUT->pix_url( 'edit', 'mod_masks' );
        $result .= $this->renderActionButton( 'edit-question', $editIcon  );
        $result .= $this->renderToggleButton( 'mask-hidden' );
        $result .= $this->renderToggleButton( 'mask-deleted' );

        $families = \mod_masks\mask_families_manager::getFamilies();
        foreach($families as $family){
            $styleSetForMaskFamily = $family->getMasksStyles();
            // open a div for the first (and potentially only) line of styles
            $result .= html_writer::start_div('context-menu-row ' . $family->getStyleClass());
            // iterate over the styles defined for this mask familty
            foreach ( $styleSetForMaskFamily as $styleId ){
                // treat a '-1' as a separator
                if ( $styleId == - 1 ){
                    $result .= '<br>';
                    continue;
                }
                // render the style button
                $result .= $this->renderStyleContextMenuEntry( $styleId , $family->getFamilyName() );
            }
             // end styles
            $result .= html_writer::end_div();
        }


        // close the context menu
        $result .= html_writer::end_div();
        $result .= html_writer::end_tag('div');
        return $result;
    }

    private function renderCircleValue($value , $label , $classes = '' ){
        $result = '';
        $result .= html_writer::start_div( 'circle-value', array('class' => $classes) );
        $result .= $value;
        $result .= html_writer::end_div();
        $result .= html_writer::start_div( 'circle-label', array('class' => $classes) );
        $result .= $label;
        $result .= html_writer::end_div();
        return $result;
    }


    // -----------------------------------------------------------------------------------
    // Private utilities - menu-bar menus components

    private function openMenuBarGroup( $groupId = '' ){
        $result = '';

        // open the div
        $result .= html_writer::start_div( 'menu-bar-group ' . $groupId );

        // add a title
        $strTitle = empty( $groupId ) ? '' : get_string( $groupId, 'mod_masks' );
        if ( ! empty( $strTitle ) ){
            $result .= html_writer::div( $strTitle, 'menu-bar-group-title' );
        }

        return $result;
    }

    private function closeMenuBarGroup(){
        return html_writer::end_div();
    }

    private function renderActionButton( $clickAction, $icon, $classes = '' , $bold = false ){
        global $OUTPUT;
        $result = '';

        $strButtonText = get_string( $clickAction, 'mod_masks' );
        $attributes = array( 'click-action' => $clickAction , 'class' => $classes);

        $result .= html_writer::start_div( 'action-button ', $attributes );
        $result .= html_writer::empty_tag( 'img', array( 'src' => $icon ) );
        $result .= html_writer::span( $strButtonText, $bold ? 'bold' : '' );
        $result .= html_writer::end_div();

        return $result;
    }

    private function renderToggleButton( $toggleName ){
        global $OUTPUT;
        $result = '';

        $strButtonText = get_string( $toggleName, 'mod_masks' );
        $attributes = array(
            'click-action' => 'toggle',
            'arg' => $toggleName,
            'id' => 'masks-toggle-'.$toggleName );

        $result .= html_writer::start_div( 'toggle-button', $attributes );
        $result .= html_writer::empty_tag( 'img', array( 'class' => 'toggle-off', 'src' => $OUTPUT->pix_url( 'checkbox_off', 'mod_masks' ) ) );
        $result .= html_writer::empty_tag( 'img', array( 'class' => 'toggle-on', 'src' => $OUTPUT->pix_url( 'checkbox_on', 'mod_masks' ) ) );
        $result .= html_writer::span( $strButtonText );
        $result .= html_writer::end_div();

        return $result;
    }

    // -----------------------------------------------------------------------------------
    // Private utilities - menu-bar drop-down menus components

    private function openMenuFromButton( $menuName, $buttonContent , $classes = '' ){
        global $OUTPUT;
        $result = '';

        // open wrapper
        $result .= html_writer::start_div( 'menu-wrapper', array( 'id' => 'masks-menu-'.$menuName ) );

        // add menu button
        $result .= html_writer::start_div( 'menu-button', array( 'click-action' => 'show-menu', 'menu' => $menuName , 'class' => $classes) );
        $result .= $buttonContent;
        $result .= html_writer::empty_tag( 'img', array( 'src' => $OUTPUT->pix_url( 't/expanded' ) ) );
        $result .= html_writer::end_div();

        // open menu body
        $result .= html_writer::start_div( 'menu-popup', array( 'id' => 'drop-down-'.$menuName) );

        return $result;
    }

    private function openMenuFromIcon( $menuName, $icon ){
        global $OUTPUT;
        $result = '';

        // open wrapper
        $result .= html_writer::start_div( 'menu-wrapper', array( 'id' => 'masks-menu-'.$menuName ) );

        // add menu button
        $result .= html_writer::start_div( 'menu-button', array( 'click-action' => 'show-menu', 'menu' => $menuName ) );
        $result .= html_writer::empty_tag( 'img', array( 'src' => $icon ) );
        $result .= html_writer::end_div();

        // open menu body
        $result .= html_writer::start_div( 'menu-popup', array( 'id' => 'drop-down-'.$menuName) );

        return $result;
    }

    private function closeMenu(){
        $result = '';

        // close menu body
        $result .= html_writer::end_div();

        // close wrapper
        $result .= html_writer::end_div();

        return $result;
    }

    private function renderMenuEntry( $content, $attributes ){
        $result = '';

        $result .= html_writer::start_div( 'menu-entry', $attributes );
        $result .= $content;
        $result .= html_writer::end_div();

        return $result;
    }

    private function renderActionMenuEntry( $clickAction, $menuIcon, $classes = '', $extraAttributes = array() ){
        global $OUTPUT;
        $result = '';

        $strMenuTextId = $clickAction;
        foreach( $extraAttributes as $attribValue ){
            $strMenuTextId .= '-' . $attribValue;
        }
        $strMenuText = get_string( $strMenuTextId, 'mod_masks' );
        $attributes = $extraAttributes;
        $attributes[ 'click-action' ] = $clickAction;
        $attributes[ 'class' ] = $classes;

        $result .= html_writer::start_div( 'menu-entry', $attributes );
        $result .= html_writer::empty_tag( 'img', array( 'src' => $menuIcon ) );
        $result .= html_writer::span( $strMenuText );
        $result .= html_writer::end_div();

        return $result;
    }

    private function renderStyleMenuEntry( $idx ){
        global $OUTPUT;
        $result = '';

        $attributes = array( 'click-action' => 'set-mask-style', 'mask-style' => $idx );

        $result .= html_writer::start_div( 'menu-entry', $attributes );
        $result .= html_writer::start_div( 'mask-root mask-style-'.$idx );
        $result .= html_writer::div( '', 'mask-back' );
        $result .= html_writer::div( '', 'mask-main' );
        $result .= html_writer::div( '', 'mask-front' );
        $result .= html_writer::end_div();
        $result .= html_writer::end_div();

        return $result;
    }

    private function renderStyleContextMenuEntry( $idx , $familyName ){
        global $OUTPUT;
        $result = '';

        $attributes = array( 'click-action' => 'set-mask-style', 'mask-style' => $idx , 'family-name' => $familyName);

        $result .= html_writer::start_div( 'context-menu-style', $attributes );
        $result .= html_writer::start_div( 'mask-root mask-style-' . $familyName . '-'.$idx.' mask-family-' . $familyName );
        $result .= html_writer::div( '', 'mask-back' );
        $result .= html_writer::div( '', 'mask-main' );
        $result .= html_writer::div( '', 'mask-front' );
        $result .= html_writer::end_div();
        $result .= html_writer::end_div();

        return $result;
    }

    private function renderToggleMenuEntry( $toggleName ){
        global $OUTPUT;
        $result = '';

        $strMenuText = get_string( $toggleName, 'mod_masks' );
        $attributes = array(
            'click-action' => 'toggle',
            'arg' => $toggleName,
            'id' => 'masks-toggle-'.$toggleName );

        $result .= html_writer::start_div( 'menu-entry', $attributes );
        $result .= html_writer::empty_tag( 'img', array( 'class' => 'toggle-off', 'src' => $OUTPUT->pix_url( 'header_checkbox_off', 'mod_masks' ) ) );
        $result .= html_writer::empty_tag( 'img', array( 'class' => 'toggle-on', 'src' => $OUTPUT->pix_url( 'header_checkbox_on', 'mod_masks' ) ) );
        $result .= html_writer::span( $strMenuText );
        $result .= html_writer::end_div();

        return $result;
    }
}

