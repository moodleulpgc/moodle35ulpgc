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
 * Url module admin settings and defaults
 *
 * @package    mod_videolib
 * @copyright  2019 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('modsettings', new admin_category('modvideolibfolder', new lang_string('pluginname', 'mod_videolib'), $module->is_enabled() === false));

$settings = new admin_settingpage($section, get_string('settings', 'videolib'), 'moodle/site:config', $module->is_enabled() === false);

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_AUTO,
                                                           RESOURCELIB_DISPLAY_EMBED,
                                                           RESOURCELIB_DISPLAY_FRAME,
                                                           RESOURCELIB_DISPLAY_OPEN,
                                                           RESOURCELIB_DISPLAY_NEW,
                                                           RESOURCELIB_DISPLAY_POPUP,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_AUTO,
                                   RESOURCELIB_DISPLAY_EMBED,
                                   RESOURCELIB_DISPLAY_OPEN,
                                   RESOURCELIB_DISPLAY_POPUP,
                                  );
                                  
    $separators = array('|' => '|', '@' => '@', '#' => '#', '~' => '~', '$' => '$', '%' => '%', '&' => '&', '=' => '=',);

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configmultiselect('videolib/displayoptions',
        get_string('displayoptions', 'videolib'), get_string('configdisplayoptions', 'videolib'),
        $defaultdisplayoptions, $displayoptions));
    $settings->add(new admin_setting_configpasswordunmask('videolib/secretphrase', get_string('password'),
        get_string('configsecretphrase', 'videolib'), ''));
    $settings->add(new admin_setting_configcheckbox('videolib/rolesinparams',
        get_string('rolesinparams', 'videolib'), get_string('configrolesinparams', 'videolib'), false));
        
    $settings->add(new admin_setting_configselect('videolib/separator',
        get_string('separator', 'videolib'), get_string('separatorexplain', 'videolib'), '#', $separators));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('videolibmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('videolib/printheading',
        get_string('printheading', 'videolib'), get_string('printintroexplain', 'videolib'), 1));
    $settings->add(new admin_setting_configcheckbox('videolib/printintro',
        get_string('printintro', 'videolib'), get_string('printintroexplain', 'videolib'), 1));
    $settings->add(new admin_setting_configselect('videolib/display',
        get_string('displayselect', 'videolib'), get_string('displayselectexplain', 'videolib'), RESOURCELIB_DISPLAY_AUTO, $displayoptions));
    $settings->add(new admin_setting_configtext('videolib/popupwidth',
        get_string('popupwidth', 'videolib'), get_string('popupwidthexplain', 'videolib'), 620, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('videolib/popupheight',
        get_string('popupheight', 'videolib'), get_string('popupheightexplain', 'videolib'), 450, PARAM_INT, 7));
}

$ADMIN->add('modvideolibfolder', $settings);
// Tell core we already added the settings structure.
$settings = null;

$ADMIN->add('modvideolibfolder', new admin_category('videolibsourceplugins',
    new lang_string('videolibsourceplugins', 'videolib'), !$module->is_enabled()));
$ADMIN->add('videolibsourceplugins', new admin_externalpage('managevideolibsources', get_string('managevideolibsources', 'videolib'), 
                                                            new moodle_url('/mod/videolib/adminmanageplugins.php', array('subtype'=>'videlibsource'))));

foreach (core_plugin_manager::instance()->get_plugins_of_type('videolibsource') as $plugin) {
    /** @var \mod_videolib\plugininfo\videlolibsource $plugin */
    $plugin->load_settings($ADMIN, 'videolibsourceplugins', $hassiteconfig);
}

