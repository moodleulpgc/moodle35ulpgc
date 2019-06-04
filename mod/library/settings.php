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
 * @package    mod_library
 * @copyright  2019 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('modsettings', new admin_category('modlibraryfolder', new lang_string('pluginname', 'mod_library'), $module->is_enabled() === false));

$settings = new admin_settingpage($section, get_string('settings', 'library'), 'moodle/site:config', $module->is_enabled() === false);

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
    $settings->add(new admin_setting_configmultiselect('library/displayoptions',
        get_string('displayoptions', 'library'), get_string('configdisplayoptions', 'library'),
        $defaultdisplayoptions, $displayoptions));
    $settings->add(new admin_setting_configpasswordunmask('library/secretphrase', get_string('password'),
        get_string('configsecretphrase', 'library'), ''));
    $settings->add(new admin_setting_configcheckbox('library/rolesinparams',
        get_string('rolesinparams', 'library'), get_string('configrolesinparams', 'library'), false));
        
    $settings->add(new admin_setting_configselect('library/separator',
        get_string('separator', 'library'), get_string('separatorexplain', 'library'), '#', $separators));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('librarymodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('library/printheading',
        get_string('printheading', 'library'), get_string('printintroexplain', 'library'), 1));
    $settings->add(new admin_setting_configcheckbox('library/printintro',
        get_string('printintro', 'library'), get_string('printintroexplain', 'library'), 1));
    $settings->add(new admin_setting_configselect('library/display',
        get_string('displayselect', 'library'), get_string('displayselectexplain', 'library'), RESOURCELIB_DISPLAY_AUTO, $displayoptions));
    $settings->add(new admin_setting_configtext('library/popupwidth',
        get_string('popupwidth', 'library'), get_string('popupwidthexplain', 'library'), 620, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('library/popupheight',
        get_string('popupheight', 'library'), get_string('popupheightexplain', 'library'), 450, PARAM_INT, 7));
}

$ADMIN->add('modlibraryfolder', $settings);
// Tell core we already added the settings structure.
$settings = null;

$ADMIN->add('modlibraryfolder', new admin_category('librarysourceplugins',
    new lang_string('librarysourceplugins', 'library'), !$module->is_enabled()));
$ADMIN->add('librarysourceplugins', new admin_externalpage('managelibrarysources', get_string('managelibrarysources', 'library'), 
                                                            new moodle_url('/mod/library/adminmanageplugins.php', array('subtype'=>'librarysource'))));

foreach (core_plugin_manager::instance()->get_plugins_of_type('librarysource') as $plugin) {
    /** @var \mod_library\plugininfo\videlolibsource $plugin */
    $plugin->load_settings($ADMIN, 'librarysourceplugins', $hassiteconfig);
}

