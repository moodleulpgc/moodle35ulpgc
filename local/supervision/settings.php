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
 * batchmanage settings and admin links.
 *
 * @package    local_supervision
 * @copyright  2016 Enrique Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
/*
    $settings = new admin_settingpage('local_supervision_settings', get_string('supervisionsettings','local_supervision')); 

    $settings->add(new admin_setting_configcheckbox('local_supervision/enablestats', get_string('enablestats', 'local_supervision'),
                    get_string('configenablestats', 'local_supervision'), 0, PARAM_INT));

    $settings->add(new admin_setting_configtime('local_supervision/statsruntimestarthour', 'statsruntimestartminute', get_string('statsruntimestart', 'admin'), get_string('configstatsruntimestart', 'admin'), array('h' => 3, 'm' => 0)));

    $roles = get_all_roles();
    $options = role_fix_names($roles, null, ROLENAME_ORIGINAL, true);
    list($usql, $params) = $DB->get_in_or_equal(array('editingteacher', 'teacher'));
    $defaultroles = $DB->get_records_select('role', " shortname $usql ", $params, '', 'id, name');

    $settings->add(new admin_setting_configmultiselect('local_supervision/checkedroles', get_string('checkedroles', 'local_supervision'), get_string('configcheckedroles', 'local_supervision'), array_keys($defaultroles), $options));

    $options = array('0' => get_string('choose')) + $options;
    $settings->add(new admin_setting_configselect('local_supervision/checkerrole', get_string('checkerrole', 'local_supervision'), get_string('configcheckerrole', 'local_supervision'), 0, $options));

    $categories =  make_categories_options();
    $settings->add(new admin_setting_configmultiselect('local_supervision/excludedcats', get_string('excludedcategories', 'local_supervision'), get_string('configexcludedcategories', 'local_supervision'), null, $categories));

    $dbman = $DB->get_manager();
    if($dbman->field_exists('course_categories', 'faculty')) {
        $settings->add(new admin_setting_configselect('local_supervision/enablefaculties', get_string('enablefaculties', 'local_supervision'), get_string('configenablefaculties', 'local_supervision'), 0,array(0 => get_string('no'), 1 => get_string('yes'))));
    }
    if($dbman->field_exists('course', 'department')) {
        $settings->add(new admin_setting_configselect('local_supervision/enabledepartments', get_string('enabledepartments', 'local_supervision'), get_string('configenabledepartments', 'local_supervision'), 0,array(0 => get_string('no'), 1 => get_string('yes'))));
    }

    $settings->add(new admin_setting_configtext('local_supervision/excludeshortnames', get_string('excludeshortnames', 'local_supervision'),
                    get_string('configexcludeshortnames', 'local_supervision'), '', PARAM_NOTAGS));

    $settings->add(new admin_setting_configcheckbox('local_supervision/excludecourses', get_string('excludecourses', 'local_supervision'),
                    get_string('configexcludecourses', 'local_supervision'), 0, PARAM_INT));

    $settings->add(new admin_setting_configtext('local_supervision/startdisplay',get_string('startdisplay','local_supervision'),
                    get_string('configstartdisplay', 'local_supervision'),'',PARAM_TEXT));
    $settings->add(new admin_setting_configcheckbox('local_supervision/enablemail', get_string('enablependingmail', 'local_supervision'),
                    get_string('configenablemail', 'local_supervision'), 0, PARAM_INT));

    $settings->add(new admin_setting_configselect("local_supervision/maildelay", get_string('maildelay', 'local_supervision'),
                    get_string('configmaildelay', 'local_supervision'), 1, array(0,1,2,3,4,5,6,7)));

    $settings->add(new admin_setting_configcheckbox('local_supervision/coordemail', get_string('enablecoordmail', 'local_supervision'),
                    get_string('configcoordemail', 'local_supervision'), 0, PARAM_INT));
    $settings->add(new admin_setting_configtext('local_supervision/email', get_string('pendingmail', 'local_supervision'),
                    get_string('configemail', 'local_supervision'), '', PARAM_NOTAGS));

$ADMIN->add('localplugins', $settings);

*/

$ADMIN->add('localplugins', new admin_category('managesupervisionwarnings', new lang_string('managewarningsettings', 'local_supervision')));

$plugins = core_plugin_manager::instance()->get_plugins_of_type('supervisionwarning');

    $temp = new admin_settingpage('supervisionwarnings', new lang_string('warnings', 'local_supervision'));
    $temp->add(new local_supervision_setting_warnings());

    $url = new moodle_url('/blocks/supervision/holidays.php', array('cid'=>$PAGE->course->id, 'type'=>$PAGE->pagetype));
    $ADMIN->add('managesupervisionwarnings', new admin_externalpage('local_supervision_holidays', 
                    get_string('editholidays', 'local_supervision'),  $url,'local/supervision:manage'));
    $url = new moodle_url('/blocks/supervision/supervisors.php', array('cid'=>$PAGE->course->id, 'type'=>$PAGE->pagetype));
    $ADMIN->add('managesupervisionwarnings', new admin_externalpage('local_supervision_supervisors', 
                    get_string('editpermissions', 'local_supervision'),  $url,'local/supervision:manage'));

    $ADMIN->add('managesupervisionwarnings', $temp);
    

    foreach ($plugins as $plugin) {
        /** @var \local_supervision\plugininfo\managejob $plugin */
        $plugin->load_settings($ADMIN, 'managesupervisionwarnings', $hassiteconfig);
    }
}
