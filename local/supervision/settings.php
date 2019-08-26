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

$ADMIN->add('localplugins', new admin_category('managesupervisionwarnings', new lang_string('managewarningsettings', 'local_supervision')));

$plugins = core_plugin_manager::instance()->get_plugins_of_type('supervisionwarning');

    $temp = new admin_settingpage('supervisionwarnings', new lang_string('warnings', 'local_supervision'));
    $temp->add(new \local_supervision\setting_warnings());
    $ADMIN->add('managesupervisionwarnings', $temp);
    
    $settings = new admin_settingpage('local_supervision_settings', get_string('supervisionsettings','local_supervision')); 

    $settings->add(new admin_setting_configcheckbox('local_supervision/enablestats', 
                    get_string('enablestats', 'local_supervision'),
                    get_string('enablestats_help', 'local_supervision'), 0));

    $roles = get_all_roles();
    $options = role_fix_names($roles, null, ROLENAME_ORIGINAL, true);
    list($usql, $params) = $DB->get_in_or_equal(array('editingteacher', 'teacher'));
    $defaultroles = $DB->get_records_select('role', " shortname $usql ", $params, '', 'id, name');

    $settings->add(new admin_setting_configmultiselect('local_supervision/checkedroles', 
                    get_string('checkedroles', 'local_supervision'), 
                    get_string('checkedroles_help', 'local_supervision'), array_keys($defaultroles), $options));

    $options = array('0' => get_string('choose')) + $options;
    $settings->add(new admin_setting_configselect('local_supervision/checkerrole', 
                    get_string('checkerrole', 'local_supervision'), 
                    get_string('checkerrole_help', 'local_supervision'), 0, $options));

    $categories =  make_categories_options();
    $settings->add(new admin_setting_configmultiselect('local_supervision/excludedcats', 
                    get_string('excludedcategories', 'local_supervision'), 
                    get_string('excludedcategories_help', 'local_supervision'), null, $categories));

    $dbman = $DB->get_manager();
    if($dbman->field_exists('course_categories', 'faculty')) {
        $settings->add(new admin_setting_configselect('local_supervision/enablefaculties', 
                    get_string('enablefaculties', 'local_supervision'), 
                    get_string('enablefaculties_help', 'local_supervision'), 0,array(0 => get_string('no'), 1 => get_string('yes'))));
    }
    if($dbman->field_exists('course', 'department')) {
        $settings->add(new admin_setting_configselect('local_supervision/enabledepartments', 
                    get_string('enabledepartments', 'local_supervision'), 
                    get_string('enabledepartments_help', 'local_supervision'), 0,array(0 => get_string('no'), 1 => get_string('yes'))));
    }

    $settings->add(new admin_setting_configtext('local_supervision/excludeshortnames', 
                    get_string('excludeshortnames', 'local_supervision'),
                    get_string('excludeshortnames_help', 'local_supervision'), '', PARAM_NOTAGS));

    $settings->add(new admin_setting_configcheckbox('local_supervision/excludecourses', 
                    get_string('excludecourses', 'local_supervision'),
                    get_string('excludecourses_help', 'local_supervision'), 0));

    $settings->add(new admin_setting_configtext('local_supervision/startdisplay', 
                    get_string('startdisplay','local_supervision'),
                    get_string('startdisplay_help', 'local_supervision'),'',PARAM_TEXT));
                    
    $settings->add(new admin_setting_configcheckbox('local_supervision/enablemail', 
                    get_string('enablemail', 'local_supervision'),
                    get_string('enablemail_help', 'local_supervision'), 0));

    $settings->add(new admin_setting_configselect("local_supervision/maildelay", 
                    get_string('maildelay', 'local_supervision'),
                    get_string('maildelay_help', 'local_supervision'), 1, array(0,1,2,3,4,5,6,7,10,14,15)));

    $settings->add(new admin_setting_configcheckbox('local_supervision/coordemail', 
                    get_string('coordemail', 'local_supervision'),
                    get_string('coordemail_help', 'local_supervision'), 0));

    $settings->add(new admin_setting_configcheckbox('local_supervision/maildebug', 
                    get_string('maildebug', 'local_supervision'),
                    get_string('maildebug_help', 'local_supervision'), 0));

    $settings->add(new admin_setting_configtext('local_supervision/email', 
                    get_string('pendingmail', 'local_supervision'),
                    get_string('pendingmail_help', 'local_supervision'), '', PARAM_NOTAGS));
                    
    $settings->add(new admin_setting_configcheckbox('local_supervision/synchsupervisors', 
                    get_string('synchsupervisors', 'local_supervision'),
                    get_string('synchsupervisors_help', 'local_supervision'), 0));

    $ADMIN->add('managesupervisionwarnings', $settings);

    foreach ($plugins as $plugin) {
        /** @var \local_supervision\plugininfo\managejob $plugin */
        $plugin->load_settings($ADMIN, 'managesupervisionwarnings', $hassiteconfig);
    }

    $url = new moodle_url('/local/supervision/holidays.php', array());
    $ADMIN->add('managesupervisionwarnings', new admin_externalpage('local_supervision_holidays', 
                    get_string('editholidays', 'local_supervision'),  $url,'local/supervision:manage'));
    $url = new moodle_url('/local/supervision/supervisors.php', array());
    $ADMIN->add('managesupervisionwarnings', new admin_externalpage('local_supervision_supervisors', 
                    get_string('supervisors', 'local_supervision'),  $url,'local/supervision:manage'));


}
