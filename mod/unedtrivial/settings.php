<?php
/**
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig){
    require_once($CFG->dirroot.'/admin/settings/top.php');
    require_once($CFG->dirroot.'/admin/settings/plugins.php');
    $settings = new admin_settingpage('unedtrivial', new lang_string('pluginname', 'unedtrivial'));
    //$ADMIN->add('localplugins', $settings);
    $options = array();
    $options[1] = get_string('settingso1','unedtrivial');
    $options[2] = get_string('settingso2','unedtrivial');
    $options[3] = get_string('settingso3','unedtrivial');
    $settings->add(new admin_setting_configselect('mod_unedtrivial/mailsendtype', 
                            get_string('settingspar1','unedtrivial'),
                            get_string('settingsexpl1','unedtrivial'), 1, $options));
    $settings->add(new admin_setting_heading('mod_unedtrivial/smtp_options', 
                        get_string('settingshead1','unedtrivial'), ''));
    $settings->add(new admin_setting_configtext('mod_unedtrivial/smtp_host', 
                        get_string('settingspar2','unedtrivial'), 
                        get_string('settingsexpl2','unedtrivial'), ""));
    $settings->add(new admin_setting_configtext('mod_unedtrivial/smtp_username', 
                        get_string('settingspar3','unedtrivial'),
                        get_string('settingsexpl3','unedtrivial'), ""));
    $settings->add(new admin_setting_configtext('mod_unedtrivial/smtp_password', 
                        get_string('settingspar4','unedtrivial'),
                        get_string('settingsexpl4','unedtrivial'), ""));
    $settings->add(new admin_setting_configtext('mod_unedtrivial/smtp_port', 
                        get_string('settingspar5','unedtrivial'),
                        get_string('settingsexpl5','unedtrivial'), ""));
    $settings->add(new admin_setting_configtext('mod_unedtrivial/smtp_from', 
                        get_string('settingspar6','unedtrivial'),
                        get_string('settingsexpl6','unedtrivial'), ""));
    $settings->add(new admin_setting_configtext('mod_unedtrivial/smtp_fromname', 
                        get_string('settingspar7','unedtrivial'),
                        get_string('settingsexpl7','unedtrivial'), ""));
}