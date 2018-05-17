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
 * Supervision warning  subplugin info class.
 *
 * @package   local_supervision
 * @copyright 2014 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_supervision\plugininfo;

use core\plugininfo\base, core_plugin_manager, moodle_url;

defined('MOODLE_INTERNAL') || die();

//include_once($CFG->libdir.'/adminlib.php');

class supervisionwarning extends base {
    /**
     * Finds all enabled plugins, the result may include missing plugins.
     * @return array|null of enabled plugins $pluginname=>$pluginname, null means unknown
     */
    public static function get_enabled_plugins() {
        global $DB;

        $plugins = core_plugin_manager::instance()->get_installed_plugins('supervisionwarning');
        if (!$plugins) {
            return array();
        }
        $installed = array();
        foreach ($plugins as $plugin => $version) {
            $installed[] = 'supervisionwarning_'.$plugin;
        }

        list($installed, $params) = $DB->get_in_or_equal($installed, SQL_PARAMS_NAMED);
        $disabled = $DB->get_records_select('config_plugins', "plugin $installed AND name = 'disabled'", $params, 'plugin ASC');
        foreach ($disabled as $conf) {
            if (empty($conf->value)) {
                continue;
            }
            list($type, $name) = explode('_', $conf->plugin, 2);
            unset($plugins[$name]);
        }

        $enabled = array();
        foreach ($plugins as $plugin => $version) {
            $enabled[$plugin] = $plugin;
        }

        return $enabled;
    }


    public function is_enabled() {
        $enabled = get_config('local_supervision', 'enabled_warnings');
        if (!$enabled) {
            return false;
        }

        $enabled = array_flip(explode(',', $enabled));
        return isset($enabled[$this->name]);
    }

    public function get_settings_section_name() {
        return 'warningsettings_'.$this->name; 
    }
   
    public function load_settings(\part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE; // In case settings.php wants to refer to them.
        $ADMIN = $adminroot; // May be used in settings.php.
        $section = $this->get_settings_section_name();

        if (!$this->is_installed_and_upgraded()) {
            return;
        }

       
        if (!$hassiteconfig or !file_exists($this->full_path('settings.php'))) {
            return;
        }

        $settings = new \admin_settingpage($section, $this->displayname, 'local/supervision:manage', $this->is_enabled() === false);
        include($this->full_path('settings.php'));

        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }

    public static function get_manage_url() {
        return new moodle_url('/admin/settings.php', array('section' => 'supervisionwarnings'));
    }
    
    public function is_uninstall_allowed() {
        return true;
    }

    public function uninstall_cleanup() {
        $enabled = get_config('local_supervision', 'enabled_warnings');
        if ($enabled) {
            $enabled = array_flip(explode(',', $enabled));
            unset($enabled[$this->name]);
            $enabled = array_flip($enabled);
            set_config('enabled_warnings', implode(',', $enabled), 'local_supervision');
        }
        
        parent::uninstall_cleanup();
    }}
