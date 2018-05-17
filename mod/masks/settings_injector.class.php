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
 * masks module admin settings injector class
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masks;

defined('MOODLE_INTERNAL') || die;

class settingsinjector{
    private $settings   = null;
    private $pluginname = null;

    // constructor
    // record the name of the plugin and the settings container object, both for later use
    public function __construct( $settings, $pluginname ){
        $this->settings     = $settings;
        $this->pluginname   = $pluginname;
    }

    // add a section heading to the settings
    function addheading( $name ){
        $heading = new \admin_setting_heading( $this->pluginname.'/settinghead_'.$name, get_string('settinghead_'.$name, $this->pluginname), "" );
        $this->settings->add($heading);
    }

    function addsetting( $name, $defaultvalue, $settingtype = 'ADMIN_SETTING_TYPE_TEXT', $data=null ){
        $uniquename  = $this->pluginname.'/'.$name;
        $displayname = get_string("settingname_".$name, $this->pluginname);
        $displayinfo = /*get_string("settingdesc_".$name, $this->pluginname)*/ "";
        switch ($settingtype){

            case 'ADMIN_SETTING_TYPE_CHECKBOX':
                $setting = new \admin_setting_configcheckbox( $uniquename, $displayname, $displayinfo, $defaultvalue );
                break;

            case 'ADMIN_SETTING_TYPE_SELECT':
                $setting = new \admin_setting_configselect( $uniquename, $displayname, $displayinfo, $defaultvalue, $data );
                break;

            case 'ADMIN_SETTING_TYPE_TEXT':
                // drop through to default clause
            default:
                $setting = new \admin_setting_configtext( $uniquename, $displayname, $displayinfo, $defaultvalue );
        }
        $this->settings->add( $setting );
    }
}

