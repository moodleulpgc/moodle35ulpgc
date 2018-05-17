<?php

/**
 * ULPGC specific customizations admin tree pages & settings
 *
 * @package    local
 * @subpackage ulpgcassign
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $temp = new admin_settingpage('local_ulpgcassign_settings', get_string('assignsettings','local_ulpgcassign')); 

    $temp->add(new admin_setting_configcheckbox('local_ulpgcassign/enabledadvancedassign', get_string('advancedassigns','local_ulpgcassign'), get_string('explainadvancedassigns','local_ulpgcassign'), 1));

    $temp->add(new admin_setting_configcolourpicker('local_ulpgcassign/colorsubmitted', get_string('colorsubmitted','local_ulpgcassign'), get_string('explaincolorsubmitted','local_ulpgcassign'), '#efcfcf', null));
    $temp->add(new admin_setting_configcolourpicker('local_ulpgcassign/colorsubmitted_graded', get_string('colorsubmitted_graded','local_ulpgcassign'), get_string('explaincolorsubmitted_graded','local_ulpgcassign'), '#dfffdf', null));
    $temp->add(new admin_setting_configcolourpicker('local_ulpgcassign/colorgraded', get_string('colorgraded','local_ulpgcassign'), get_string('explaincolorgraded','local_ulpgcassign'), '#bfffbf', null));

    $plugins = core_component::get_plugin_list('assignsubmission');
    foreach ($plugins as $name => $path) {
        $plugins[$name] = get_string('pluginname', 'assignsubmission_'.$name);
    }
    
    $temp->add(new admin_setting_configmultiselect('local_ulpgcassign/allownewui', get_string('allownewui', 'local_ulpgcassign'), get_string('configallownewui', 'local_ulpgcassign'), array(), $plugins));
 
    $ADMIN->add('localplugins', $temp);

}
