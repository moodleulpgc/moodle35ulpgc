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
 * masks module admin settings and defaults
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


if ($ADMIN->fulltree) {
    require_once(dirname(__FILE__).'/mask_type.class.php');
    require_once(dirname(__FILE__).'/mask_types_manager.class.php');
    require_once(dirname(__FILE__).'/settings_injector.class.php');
    require_once($CFG->libdir.'/resourcelib.php');
    require_once($CFG->libdir.'/moodlelib.php');

    // instantiate a settings injector object to simplify settings definition
    $settingsinjector   = new \mod_masks\settingsinjector($settings, 'mod_masks');

    // Basic settings
    $settingsinjector->addheading('basics');
    $settingsinjector->addsetting('cmdline_pdf2svg', 'pdf2svg');

    // Begin Main configuration settings
    $settingsinjector->addheading('configuration');

    // Add settings to select the feedback options to make available to students
    $maskEditOptions = array(
        \mod_masks\FIELDS_NONE  => get_string('setting_fields_none', 'mod_masks'),
        \mod_masks\FIELDS_H     => get_string('setting_fields_h', 'mod_masks'),
        \mod_masks\FIELDS_HF    => get_string('setting_fields_hf', 'mod_masks'),
    );
    $settingsinjector->addsetting('maskedit', \mod_masks\FIELDS_NONE, 'ADMIN_SETTING_TYPE_SELECT', $maskEditOptions);

    // Add settings for each of the mask types
    $typeNames    = \mod_masks\mask_types_manager::getTypeNames();
    $defaultTypes = array_flip( \mod_masks\mask_types_manager::getDefaultTypeNames() );
    foreach($typeNames as $typeName){
        $defaultValue = array_key_exists( $typeName, $defaultTypes )? 0: 1;
        $settingsinjector->addsetting('disable_'.$typeName, $defaultValue, 'ADMIN_SETTING_TYPE_CHECKBOX');
    }

    // add checkbox for activiating or deactivating ghosts for cleared masks
    $settingsinjector->addsetting('showghosts', 1, 'ADMIN_SETTING_TYPE_CHECKBOX');

    // add debug settings
    $settingsinjector->addheading('advanced');
    $settingsinjector->addsetting('debug', 0, 'ADMIN_SETTING_TYPE_CHECKBOX');
}

