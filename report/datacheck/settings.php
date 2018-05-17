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
 * Plugin administration pages are defined here.
 *
 * @package     report_datacheck
 * @category    admin
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


// site wide report settings.
$settings = new admin_settingpage('report_datacheck_settings', get_string('pluginname','report_datacheck')); 

$settings->add(new \admin_setting_configcheckbox('report_datacheck/enabledcheck', get_string('enabledcheck','report_datacheck'), get_string('explainenabledcheck','report_datacheck'), '1'));

$settings->add(new \admin_setting_configcheckbox('report_datacheck/enableddown', get_string('enableddown','report_datacheck'), get_string('explainenableddown','report_datacheck'), '1'));


$options = array('shortname'    => get_string('shortname', 'report_datacheck'),
                    'fullname'     => get_string('fullname', 'report_datacheck'),
                    'category'     => get_string('category', 'report_datacheck'),
                    'short-full'   => get_string('short-full', 'report_datacheck'),
                    'useridnumber' => get_string('useridnumber', 'report_datacheck'),
                    'userfull'     => get_string('userfull', 'report_datacheck'),
                    'userfullrev'     => get_string('userfullrev', 'report_datacheck'),);
$settings->add(new \admin_setting_configselect('report_datacheck/parsemode', new lang_string('parsemode', 'report_datacheck'),
                                        new lang_string('explainparsemode', 'report_datacheck'), 'short-full', $options)); 

$settings->add(new \admin_setting_pickroles('report_datacheck/courseroles', get_string('courseroles','report_datacheck'), get_string('explaincourseroles','report_datacheck'), array()));
$settings->add(new \admin_setting_pickroles('report_datacheck/categoryroles', get_string('categoryroles','report_datacheck'), get_string('explaincategoryroles','report_datacheck'), array()));
