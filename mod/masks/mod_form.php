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
 * Instance configuration formula for setting up new instances of the module
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_masks_mod_form extends moodleform_mod {

    public function __construct( $current, $section, $cm, $course ) {
        // store away properties that we may need later
        $this->cm = $cm;
        $this->course = $course;
        // delegate to parent
        parent::__construct( $current, $section, $cm, $course );
    }

    private function addTextField( $fieldName, $maxlen, $defaultValue=null, $locaPrefix='' ){
        $strFieldName = get_string( $locaPrefix . $fieldName, 'mod_masks');
        $mform = $this->_form;
        $mform->addElement('text', $fieldName, $strFieldName, array('size' => '60'));
        $mform->setType( $fieldName, PARAM_TEXT );
        $mform->addRule( $fieldName, null, 'required', null, 'client');
        $mform->addRule( $fieldName, get_string('maximumchars', '', $maxlen), 'maxlength', $maxlen, 'client');
        if ( $defaultValue ){
            $mform->setDefault( $fieldName, $defaultValue );
        }
    }

    private function addCheckbox( $fieldName, $defaultValue=null, $locaPrefix='' ){
        $strFieldName = get_string( $locaPrefix . $fieldName, 'mod_masks');
        $mform = $this->_form;
        $mform->addElement( 'checkbox', $fieldName, $strFieldName );
        $mform->setType( $fieldName, PARAM_TEXT );
        if ( $defaultValue ){
            $mform->setDefault( $fieldName, $defaultValue );
        }
    }

    private function addSelect( $fieldName, $options, $defaultValue=null, $locaPrefix='' ){
        $strFieldName = get_string( $locaPrefix . $fieldName, 'mod_masks' );
        $mform = $this->_form;
        $mform->addElement( 'select', $fieldName, $strFieldName, $options );
        $mform->setType( $fieldName, PARAM_TEXT );
        if ( $defaultValue ){
            $mform->setDefault( $fieldName, $defaultValue );
        }
    }

    function definition() {
        $mform = $this->_form;

        // basics
        $mform->addElement( 'header', 'general', get_string( 'general', 'form' ) );
        $this->addTextField( 'name', 255 );

        // lookup module config to get hold of default values for everything
        require_once( __DIR__ . '/mask_type.class.php' );
        require_once( __DIR__ . '/mask_types_manager.class.php' );
        $moduleConfig = \get_config( 'mod_masks' );

        // Add settings for overriding site defaults for module parameters
        $mform->addElement( 'header', 'configuration', get_string( 'settinghead_configuration', 'mod_masks' ) );
        $maskEditOptions = array(
            \mod_masks\FIELDS_NONE  => get_string( 'setting_fields_none' , 'mod_masks' ),
            \mod_masks\FIELDS_H     => get_string( 'setting_fields_h'    , 'mod_masks' ),
            \mod_masks\FIELDS_HF    => get_string( 'setting_fields_hf'   , 'mod_masks' ),
        );
        $this->addSelect( 'maskedit', $maskEditOptions, $moduleConfig->maskedit, 'settingname_' );
        $typeNames = \mod_masks\mask_types_manager::getTypeNames();
        foreach($typeNames as $typeName){
            $configElement = 'disable_' . $typeName;
            $this->addCheckbox( $configElement, $moduleConfig->$configElement, 'settingname_' );
        }
        $this->addCheckbox( 'showghosts', $moduleConfig->showghosts, 'settingname_' );

        // standard moodle elements
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    function validation( $data, $files ) {
        // delegate to parent class
        $errors = parent::validation( $data, $files );
        return $errors;
    }

    function set_data( $data ) {
        // if we have an encoded json config blob in our data then decode it into the core data
        if ( property_exists( $data, 'config' ) ){
            $decodedConfig = json_decode( $data->config, true );
            if ( $decodedConfig ){
                foreach ( $decodedConfig as $key => $val ){
                    $data->$key = $val;
                }
            }
        }

        return parent::set_data($data);
    }

    function get_data() {
        // fetch data from parent and drop out if none found as this implies that the form hasn't been displayed yet
        $data = parent::get_data();
        if (!$data) {
            return false;
        }

        // prime the container of data properties with the list of fixed properties in our form
        $configData = [];
        $prop = 'maskedit';   $configData[ $prop ] = property_exists( $data, $prop )? $data->$prop: '';
        $prop = 'showghosts'; $configData[ $prop ] = property_exists( $data, $prop )? $data->$prop: 0;

        // encode properties that we recognise into a json config blob
        require_once( __DIR__ . '/mask_types_manager.class.php' );
        $typeNames = \mod_masks\mask_types_manager::getTypeNames();
        foreach( $typeNames as $typeName ){
            $prop = 'disable_' . $typeName;
            $configData[ $prop ] = property_exists( $data, $prop )? $data->$prop: 0;
        }
        $data->config = json_encode( $configData );

        return $data;
    }
}

