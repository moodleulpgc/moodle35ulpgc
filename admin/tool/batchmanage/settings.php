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
 * @package    tool_batchmanage
 * @copyright  2016 Enrique Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();




$ADMIN->add('courses', new admin_category('tool_batchmanage', new lang_string('batchmanage', 'tool_batchmanage')));

$ADMIN->add('modules', new admin_category('managejobsettings', new lang_string('managejobsettings', 'tool_batchmanage')));

$plugins = core_plugin_manager::instance()->get_plugins_of_type('managejob');


foreach ($plugins as $plugin) {
    if($plugin->is_enabled()) {
        $ADMIN->add('tool_batchmanage', new admin_externalpage('managejob_'.$plugin->name, $plugin->displayname,  
                    (new moodle_url('/admin/tool/batchmanage/index.php', array('job' => $plugin->name))), 
                    'tool/batchmanage:apply')); //  , !$plugin->is_enabled()
    }
}

if ($hassiteconfig) {
    $temp = new admin_settingpage('managejobs', new lang_string('managejobs', 'tool_batchmanage'));
    $temp->add(new tool_batchmanage_setting_managejobs());
    $ADMIN->add('managejobsettings', $temp);

    foreach ($plugins as $plugin) {
        /** @var \tool_batchmanage\plugininfo\managejob $plugin */
        $plugin->load_settings($ADMIN, 'managejobsettings', $hassiteconfig);
    }
}
