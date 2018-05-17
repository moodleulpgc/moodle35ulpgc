<?php

defined('MOODLE_INTERNAL') || die;
//include_once('lib.php');
if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configcheckbox('block_supervision/enablestats', get_string('enablestats', 'block_supervision'),
                    get_string('configenablestats', 'block_supervision'), 0, PARAM_INT));

    $settings->add(new admin_setting_configtime('block_supervision/statsruntimestarthour', 'statsruntimestartminute', get_string('statsruntimestart', 'admin'), get_string('configstatsruntimestart', 'admin'), array('h' => 3, 'm' => 0)));

    $roles = get_all_roles();
    $options = role_fix_names($roles, null, ROLENAME_ORIGINAL, true);
    list($usql, $params) = $DB->get_in_or_equal(array('editingteacher', 'teacher'));
    $defaultroles = $DB->get_records_select('role', " shortname $usql ", $params, '', 'id, name');

    $settings->add(new admin_setting_configmultiselect('block_supervision/checkedroles', get_string('checkedroles', 'block_supervision'), get_string('configcheckedroles', 'block_supervision'), array_keys($defaultroles), $options));

    $options = array('0' => get_string('choose')) + $options;
    $settings->add(new admin_setting_configselect('block_supervision/checkerrole', get_string('checkerrole', 'block_supervision'), get_string('configcheckerrole', 'block_supervision'), 0, $options));

    $categories =  make_categories_options();
    $settings->add(new admin_setting_configmultiselect('block_supervision/excludedcats', get_string('excludedcategories', 'block_supervision'), get_string('configexcludedcategories', 'block_supervision'), null, $categories));

    $dbman = $DB->get_manager();
    if($dbman->field_exists('course_categories', 'faculty')) {
        $settings->add(new admin_setting_configselect('block_supervision/enablefaculties', get_string('enablefaculties', 'block_supervision'), get_string('configenablefaculties', 'block_supervision'), 0,array(0 => get_string('no'), 1 => get_string('yes'))));
    }
    if($dbman->field_exists('course', 'department')) {
        $settings->add(new admin_setting_configselect('block_supervision/enabledepartments', get_string('enabledepartments', 'block_supervision'), get_string('configenabledepartments', 'block_supervision'), 0,array(0 => get_string('no'), 1 => get_string('yes'))));
    }

    $settings->add(new admin_setting_configtext('block_supervision/excludeshortnames', get_string('excludeshortnames', 'block_supervision'),
                    get_string('configexcludeshortnames', 'block_supervision'), '', PARAM_NOTAGS));

    $settings->add(new admin_setting_configcheckbox('block_supervision/excludecourses', get_string('excludecourses', 'block_supervision'),
                    get_string('configexcludecourses', 'block_supervision'), 0, PARAM_INT));

    $settings->add(new admin_setting_configtext('block_supervision/startdisplay',get_string('startdisplay','block_supervision'),
                    get_string('configstartdisplay', 'block_supervision'),'',PARAM_TEXT));
    $settings->add(new admin_setting_configcheckbox('block_supervision/enablemail', get_string('enablependingmail', 'block_supervision'),
                    get_string('configenablemail', 'block_supervision'), 0, PARAM_INT));

    $settings->add(new admin_setting_configselect("block_supervision/maildelay", get_string('maildelay', 'block_supervision'),
                    get_string('configmaildelay', 'block_supervision'), 1, array(0,1,2,3,4,5,6,7)));

    $settings->add(new admin_setting_configcheckbox('block_supervision/coordemail', get_string('enablecoordmail', 'block_supervision'),
                    get_string('configcoordemail', 'block_supervision'), 0, PARAM_INT));
    $settings->add(new admin_setting_configtext('block_supervision/email', get_string('pendingmail', 'block_supervision'),
                    get_string('configemail', 'block_supervision'), '', PARAM_NOTAGS));

}


