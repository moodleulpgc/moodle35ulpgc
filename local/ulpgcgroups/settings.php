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

    $temp->add(new \admin_setting_configcheckbox('local_ulpgcgroups/enabledadvancedgroups', 
                            get_string('enabledadvancedgroups','local_ulpgcgroups'), 
                            get_string('explainenabledadvancedgroups','local_ulpgcgroups'), 1));
    
    $temp->add(new \admin_setting_configcheckbox('local_ulpgcgroups/forcerestrictedgroups', 
                            get_string('forcerestrictedgroups','local_ulpgcgroups'), 
                            get_string('explainforcerestrictedgroups','local_ulpgcgroups'), 0));

    $temp->add(new \admin_setting_configcheckbox('local_ulpgcgroups/onlyactiveenrolments', get_string('onlyactiveenrolments','local_ulpgcgroups'), get_string('explainonlyactiveenrolments','local_ulpgcgroups'), 1));
    
    $temp->add(new \admin_setting_configcolourpicker('local_ulpgcgroups/colorrestricted', 
                            get_string('colorrestricted','local_ulpgcgroups'), 
                            get_string('explaincolorrestricted','local_ulpgcgroups'), '#800000', null));
    
    
    $temp->add(new \admin_setting_configcheckbox('local_ulpgcgroups/enablefpgroupsfromcohort', 
                            get_string('enablefpgroupsfromcohort','local_ulpgcgroups'), 
                            get_string('explainenablefpgroupsfromcohort','local_ulpgcgroups'), 0));    
    
    $cohorts = $DB->get_records_menu('cohort', array('contextid'=>1), 'name', 'id,name');
    
    $temp->add(new \admin_setting_configmultiselect('local_ulpgcgroups/fpgroupscohorts', 
                            new lang_string('fpgroupscohorts','local_ulpgcgroups'),
                            new lang_string('explainfpgroupscohorts', 'local_ulpgcgroups'), array(), $cohorts)); 
    
    $temp->add(new admin_setting_configtext('local_ulpgcgroups/fpgroupsenrolmentkey',get_string('enrolmentkey','local_ulpgcgroups'),
                    get_string('explainenrolmentkey', 'local_ulpgcgroups'), '', PARAM_TEXT));

    if($enrolmentkey = get_config('local_ulpgcgroups', 'fpgroupsenrolmentkey')) {
        $select = " courseid = :courseid AND ".$DB->sql_like('enrolmentkey', ':enrolmentkey');
        if($groups = $DB->get_records_select('groups', $select, array('courseid'=>SITEID, 'enrolmentkey'=>$enrolmentkey.'%'))) {
            $roles = get_all_roles();
            $options = role_fix_names($roles, null, ROLENAME_ORIGINAL, true);
            foreach($groups as $group) {
                $field = 'roles_'.$group->idnumber;
                $name = format_string($group->name);
                $temp->add(new admin_setting_configmultiselect('local_ulpgcgroups/'.$field, 
                            get_string('grouproles', 'local_ulpgcgroups', $name), 
                            get_string('explaingrouproles', 'local_ulpgcgroups', $name), array(), $options));
            }
        }    
    }
    
    $ADMIN->add('localplugins', $temp);

}

