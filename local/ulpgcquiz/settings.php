<?php

/**
 * ULPGC specific customizations admin tree pages & settings
 *
 * @package    local
 * @subpackage ulpgcquiz
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $temp = new admin_settingpage('local_ulpgcquiz_settings', get_string('quizsettings','local_ulpgcquiz')); 

    $temp->add(new admin_setting_configcheckbox('local_ulpgcquiz/enabledadvancedquiz', get_string('advancedquizs','local_ulpgcquiz'), get_string('explainadvancedquizs','local_ulpgcquiz'), 1));

    /*
    $temp->add(new admin_setting_configcolourpicker('local_ulpgcquiz/colorsubmitted', get_string('colorsubmitted','local_ulpgcquiz'), get_string('explaincolorsubmitted','local_ulpgcquiz'), '#efcfcf', null));
    $temp->add(new admin_setting_configcolourpicker('local_ulpgcquiz/colorsubmitted_graded', get_string('colorsubmitted_graded','local_ulpgcquiz'), get_string('explaincolorsubmitted_graded','local_ulpgcquiz'), '#dfffdf', null));
    $temp->add(new admin_setting_configcolourpicker('local_ulpgcquiz/colorgraded', get_string('colorgraded','local_ulpgcquiz'), get_string('explaincolorgraded','local_ulpgcquiz'), '#bfffbf', null));
    */
  
    $ADMIN->add('localplugins', $temp);

}

