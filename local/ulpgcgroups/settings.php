<?php

/**
 * ULPGC specific customizations admin tree pages & settings
 *
 * @package    local
 * @subpackage ulpgcgroups
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $temp = new admin_settingpage('local_ulpgcgroups_settings', get_string('groupssettings','local_ulpgcgroups')); 

    $temp->add(new admin_setting_configcheckbox('local_ulpgcgroups/enabledadvancedgroups', get_string('enabledadvancedgroups','local_ulpgcgroups'), get_string('explainenabledadvancedgroups','local_ulpgcgroups'), 1));
    
    $temp->add(new admin_setting_configcheckbox('local_ulpgcgroups/forcerestrictedgroups', get_string('forcerestrictedgroups','local_ulpgcgroups'), get_string('explainforcerestrictedgroups','local_ulpgcgroups'), 0));

    $temp->add(new admin_setting_configcheckbox('local_ulpgcgroups/onlyactiveenrolments', get_string('onlyactiveenrolments','local_ulpgcgroups'), get_string('explainonlyactiveenrolments','local_ulpgcgroups'), 1));
    
    $temp->add(new admin_setting_configcolourpicker('local_ulpgcgroups/colorrestricted', get_string('colorrestricted','local_ulpgcgroups'), get_string('explaincolorrestricted','local_ulpgcgroups'), '#800000', null));
    
    $ADMIN->add('localplugins', $temp);

}

