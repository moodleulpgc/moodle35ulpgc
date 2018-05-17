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
 * masks masked pdf activity Library for creating forms in embedded frames
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masks;

defined('MOODLE_INTERNAL') || die;

class form_writer
{
    private $refData0           = null;
    private $refData1           = null;
    private $lastHideHeading    = false;
    private $firstElement       = true;

    public function __construct($refData0 = array(), $refData1 = array()){
        $this->refData0 = $refData0;
        $this->refData1 = $refData1;
    }

    public function openForm($target, $hiddenFields){
        echo \html_writer::start_tag( 'form', array( 'action' => new \moodle_url('/mod/masks/'.$target), 'id' => 'frame-form' ) );
        foreach( $hiddenFields as $key => $val ){
            $this->addHidden( $key, $val );
        }
    }

    public function closeForm($includeButtons = true){
        if ( $includeButtons === true ){
            echo \html_writer::start_div( 'frame-footer' );
            $this->addCancelButton();
            $strSubmit = get_string( 'label_submit', 'mod_masks' );
            echo \html_writer::tag( 'button', $strSubmit, array( 'type' => 'submit', 'class' => 'standard-button normal-button' ) );
            echo \html_writer::end_div();
        }
        echo \html_writer::end_tag( 'form' );
    }

    public function addHidden($key, $val){
        echo \html_writer::tag( 'input', '', array( 'type' => 'hidden', 'name' => $key, 'id' => 'formprop-'.$key, 'value' => $val ) );
    }

    public function addTextField($propName, $requiredField, $hideHeading = null){
        $data   = $this->lookupRefData( $propName );
        $args   = array( 'type' => 'text', 'name' => $propName, 'value' => $data );
        if ( $this->firstElement == true ){
            $args['autofocus'] = 1;
            $this->firstElement = false;
        }
        if ($requiredField){
            $args['required'] = 1;
        }
        $widget = \html_writer::tag( 'input', '', $args );
        echo $this->wrapWidget( $widget, $propName , $requiredField, $hideHeading );
    }

    public function addTextArea($propName, $requiredField, $rows){
        $data   = $this->lookupRefData( $propName );
        $args   = array( 'name' => $propName, 'rows' => $rows, 'cols' => 80 );
        if ( $this->firstElement == true ){
            $args['autofocus'] = 1;
            $this->firstElement = false;
        }
        if ($requiredField){
            $args['required'] = 1;
        }
        $widget = \html_writer::tag( 'textarea', $data, $args );
        echo $this->wrapWidget( $widget, $propName , $requiredField);
    }

    public function addCancelButton(){
        $strCancel  = get_string( 'label_cancel', 'mod_masks' );
        $args       = array( 'onclick' => 'parent.M.mod_masks.closeFrame()', 'type' => 'button', 'class' => 'cancel-button', 'tabindex' => '100' );
        echo \html_writer::tag( 'button', $strCancel, $args );
    }

    private function lookupRefData($propName){
        if ( array_key_exists( $propName, $this->refData0 ) ){
            return \htmlentities( $this->refData0[ $propName ] );
        }
        if ( array_key_exists( $propName, $this->refData1 ) ){
            return \htmlentities( $this->refData1[ $propName ] );
        }
        return '';
    }

    private function wrapWidget($widget, $propName, $required = null, $hideHeading = null){
        $label  = get_string( 'label_'.$propName, 'mod_masks' );
        if ( $required ){
            // consider adding code here to modify the appearance of labels for required fields
        }
        if ( $hideHeading && ! $this->lastHideHeading ){
            echo \html_writer::div('', 'pre-noheading');
        }
        if ( $hideHeading !== true ){
            echo \html_writer::start_div('form-field with-heading');
            echo \html_writer::tag( 'label', $label );
            echo '<br>';
        } else {
            echo \html_writer::start_div('form-field no-heading');
        }
        echo $widget;
        echo \html_writer::end_div();
        if ( $this->lastHideHeading && !$hideHeading ){
            echo \html_writer::div('', 'post-noheading');
        }
        $this->lastHideHeading = $hideHeading;
    }
}
