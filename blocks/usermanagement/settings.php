<?php

defined('MOODLE_INTERNAL') || die;
//include_once('lib.php');
if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configcheckbox('block_usermanagement/enablegroupssync', get_string('groupssync', 'block_usermanagement'),
                get_string('configgroupssync', 'block_usermanagement'), 0, PARAM_INT));

    $settings->add(new admin_setting_configtime('block_usermanagement/runtimestarthour', 'runtimestartminute', get_string('statsruntimestart', 'admin'), get_string('configstatsruntimestart', 'admin'), array('h' => 3, 'm' => 0)));

    $enrolmentkey = 'block_usermanagement_!"·$%&1Q';
    $settings->add(new admin_setting_configtext('block_usermanagement/enrolmentkey',get_string('enrolmentkey','block_usermanagement'),
                    get_string('configenrolmentkey', 'block_usermanagement'), $enrolmentkey, PARAM_TEXT));


    list($usql, $params) = $DB->get_in_or_equal(array('editingteacher', 'teacher'));
    $defaultroles = $DB->get_records_select('role', " shortname $usql ", $params, '', 'id, name');

    //$settings->add(new admin_setting_configmultiselect('block_usermanagement/checkedroles', get_string('checkedroles', 'block_usermanagement'), get_string('configcheckedroles', 'block_usermanagement'), array_keys($defaultroles), $options));

    // $options = array('0' => get_string('choose')) + $options;
    //$settings->add(new admin_setting_configselect('block_usermanagement/checkerrole', get_string('checkerrole', 'block_usermanagement'), get_string('configcheckerrole', 'block_usermanagement'), 0, $options));

    $config = get_config('block_usermanagement');
    if(isset($config->enrolmentkey)) {
        $enrolmentkey = $config->enrolmentkey;
    }

    $select = " courseid = :courseid AND ".$DB->sql_like('enrolmentkey', ':enrolmentkey');
    if($groups = $DB->get_records_select('groups', $select, array('courseid'=>SITEID, 'enrolmentkey'=>$enrolmentkey.'%'))) {
        $roles = get_all_roles();
        $options = role_fix_names($roles, null, ROLENAME_ORIGINAL, true);
        foreach($groups as $group) {
            $field = 'roles_'.$group->idnumber;
            $name = format_string($group->name);
            $settings->add(new admin_setting_configmultiselect('block_usermanagement/'.$field, get_string('grouproles', 'block_usermanagement', $name), get_string('configgrouproles', 'block_usermanagement', $name), array(), $options));
        }
    }

}

