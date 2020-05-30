<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtext('block_course_termlist/excluded', get_string('excluded', 'block_course_termlist'),
                       get_string('configexcluded', 'block_course_termlist'), ''));

    $settings->add(new admin_setting_configcheckbox('block_course_termlist/useallowedcats', get_string('useallowedcats', 'block_course_termlist'),
                       get_string('configuseallowedcats', 'block_course_termlist'), 0));

    $settings->add(new admin_setting_configcheckbox('block_course_termlist/onlyactiveenrol', get_string('onlyactiveenrol', 'block_course_termlist'),
                       get_string('configonlyactiveenrol', 'block_course_termlist'), 0));
                       
                       
    $settings->add(new admin_setting_configcheckbox('block_course_termlist/showcategorieslink', get_string('showcategorieslink', 'block_course_termlist'),
                       get_string('configshowcategorieslink', 'block_course_termlist'), 0));
    $settings->add(new admin_setting_configcheckbox('block_course_termlist/showdepartmentslink', get_string('showdepartmentslink', 'block_course_termlist'),
                       get_string('configshowdepartmentslink', 'block_course_termlist'), 0));
    $settings->add(new admin_setting_configcheckbox('block_course_termlist/hideallcourseslink', get_string('hideallcourseslink', 'block_course_termlist'),
                       get_string('confighideallcourseslink', 'block_course_termlist'), 0));
}


