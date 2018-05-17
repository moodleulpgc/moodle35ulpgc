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
 * Theme block settings file
 *
 * @package   theme_boost_training
 * @copyright 2017 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

// This is used for performance, we don't need to know about these settings on every page in Moodle, only when
// we are looking at the admin settings pages.
if ($ADMIN->fulltree) {
    $settings = new theme_boost_admin_settingspage_tabs('themesettingboost_training',
        get_string('configtitle', 'theme_boost_training'));
    $page = new admin_settingpage('theme_boost_training_general', get_string('generalsettings', 'theme_boost_training'));

    // Logo file setting.
    $name = 'theme_boost_training/logo1';
    $title = get_string('logo1', 'theme_boost_training');
    $description = get_string('logo1desc', 'theme_boost_training');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo1', 0,
        array('maxfiles' => 1, 'accepted_types' => array('png', 'jpg', 'svg')));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_boost_training/logo2';
    $title = get_string('logo2', 'theme_boost_training');
    $description = get_string('logo2desc', 'theme_boost_training');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo2', 0,
        array('maxfiles' => 1, 'accepted_types' => array('png', 'jpg', 'svg')));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_boost_training/favicon';
    $title = get_string('favicon', 'theme_boost_training');
    $description = get_string('favicondesc', 'theme_boost_training');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'favicon', 0,
        array('maxfiles' => 1, 'accepted_types' => array('png', 'jpg', 'ico')));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Menu Color setting.
    $name = 'theme_boost_training/menucolor';
    $title = get_string('menucolor', 'theme_boost_training');
    $description = get_string('menucolor_desc', 'theme_boost_training');
    $default = '#31373f';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, null, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_boost_training/menutext';
    $title = get_string('menutext', 'theme_boost_training');
    $description = get_string('menutext_desc', 'theme_boost_training');
    $default = '#999999';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, null, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_boost_training/menutexttitle';
    $title = get_string('menutexttitle', 'theme_boost_training');
    $description = get_string('menutexttitle_desc', 'theme_boost_training');
    $default = '#e7e7e7';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, null, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_boost_training/menutextselect';
    $title = get_string('menutextselect', 'theme_boost_training');
    $description = get_string('menutextselect_desc', 'theme_boost_training');
    $default = '#D1D1D2';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, null, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);


    // Header Color setting.
    $name = 'theme_boost_training/headercolor';
    $title = get_string('headercolor', 'theme_boost_training');
    $description = get_string('headercolor_desc', 'theme_boost_training');
    $default = '#2196f3';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, null, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Text Color setting.
    $name = 'theme_boost_training/textcolor';
    $title = get_string('textcolor', 'theme_boost_training');
    $description = get_string('textcolor_desc', 'theme_boost_training');
    $default = '#FFFFFF';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, null, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);


    // Add tab home.
    $settings->add($page);

    // Home.
    $page = new admin_settingpage('theme_boost_training_home', get_string('home', 'theme_boost_training'));

    $name = 'theme_boost_training/topo_banner';
    $title = get_string('topo_banner', 'theme_boost_training');
    $description = get_string('topo_bannerdesc', 'theme_boost_training');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'topo_banner', 0,
        array('maxfiles' => 1, 'accepted_types' => array('png', 'jpg')));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);


    $listacores = array(
        '', '#fd6420', '#f1b22f', '#98b446', '#3aadaa',
    );

    for ($i = 1; $i <= 4; $i++) {
        $title = get_string("block{$i}_heading", 'theme_boost_training');
        $setting = new admin_setting_heading("additionalhtml_heading_{$i}", $title, '');
        $page->add($setting);

        // Header Color setting.
        $name = "theme_boost_training/blockcolor_{$i}";
        $title = get_string('blockcolor', 'theme_boost_training');
        $description = get_string('blockcolor_desc', 'theme_boost_training', $i);
        $default = $listacores[$i];
        $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, null, false);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        // Text Color setting.
        $name = "theme_boost_training/blockicon_{$i}";
        $title = get_string('blockicon', 'theme_boost_training');
        $description = get_string('blockicon_desc', 'theme_boost_training', $i);
        $setting = new admin_setting_configstoredfile($name, $title, $description, "blockicon_{$i}", 0,
            array('maxfiles' => 1, 'accepted_types' => array('png')));
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        // Text Color setting.
        $name = "theme_boost_training/blocktitle_{$i}";
        $title = get_string('blocktitle', 'theme_boost_training');
        $description = get_string('blocktitle_desc', 'theme_boost_training', $i);
        $setting = new admin_setting_configtext($name, $title, $description, '');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        // Text Color setting.
        $name = "theme_boost_training/blocktext_{$i}";
        $title = get_string('blocktext', 'theme_boost_training');
        $description = get_string('blocktext_desc', 'theme_boost_training', $i);
        $setting = new admin_setting_configtextarea($name, $title, $description, '');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        // Text Color setting.
        $name = "theme_boost_training/blocklink_{$i}";
        $title = get_string('blocklink', 'theme_boost_training');
        $description = get_string('blocklink_desc', 'theme_boost_training', $i);
        $setting = new admin_setting_configtext($name, $title, $description, '');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
    }


    // Add tab icons.
    $settings->add($page);

    // Icons.
    $page = new admin_settingpage('theme_boost_training_icons', get_string('icons', 'theme_boost_training'));

    $icons = [
        'android' => "Google Play Store",
        'apple' => 'Apple App Store',
        'youtube' => 'YouTube',
        'pinterest' => 'Pinterest',
        'linkedin' => 'LinkedIn',
        'instagram' => 'Instagram',
        'flickr' => 'Flickr',
        'twitter' => 'Twitter',
        'facebook' => 'Facebook',
        'website' => 'Website',
    ];

    foreach ($icons as $icon => $iconname) {
        $name = "theme_boost_training/icon_{$icon}";
        $title = get_string("icon", 'theme_boost_training', $iconname);
        $description = get_string('icondesc', 'theme_boost_training', $iconname);
        $setting = new admin_setting_configtext($name, $title, $description, '');
        $page->add($setting);
    }


    // Must add the page after definiting all the settings!
    $settings->add($page);

    // Advanced settings.
    $page = new admin_settingpage('theme_boost_training_advanced', get_string('advancedsettings', 'theme_boost_training'));

    // Raw SCSS to include before the content.
    $setting = new admin_setting_scsscode('theme_boost_training/scsspre',
        get_string('rawscsspre', 'theme_boost_training'), get_string('rawscsspre_desc', 'theme_boost_training'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Raw SCSS to include after the content.
    $setting = new admin_setting_scsscode('theme_boost_training/scss', get_string('rawscss', 'theme_boost_training'),
        get_string('rawscss_desc', 'theme_boost_training'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);
}
