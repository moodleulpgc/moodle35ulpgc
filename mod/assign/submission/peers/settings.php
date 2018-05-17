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
 * This file defines the admin settings for this plugin
 *
 * @package assignsubmission_peers
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$settings->add(new admin_setting_configcheckbox('assignsubmission_peers/default',
                   new lang_string('default', 'assignsubmission_peers'),
                   new lang_string('default_help', 'assignsubmission_peers'), 0));

$options = array('final'=>get_string('limitbyfinal', 'assignsubmission_peers'),
                    'grade'=>get_string('limitbygrade', 'assignsubmission_peers'),
                    'time'=>get_string('limitbytime', 'assignsubmission_peers'),
                    'submit'=>get_string('limitbysubmission', 'assignsubmission_peers')
                    );
$settings->add(new admin_setting_configselect('assignsubmission_peers/viewpeerslimit',
                    get_string('viewpeerslimitdefault', 'assignsubmission_peers'),
                    get_string('configviewpeerslimitdefault', 'assignsubmission_peers'), 'final', $options));

