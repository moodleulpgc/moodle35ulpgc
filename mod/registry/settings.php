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
 * @package mod_registry
 * @copyright 2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configcheckbox('registry/enabletracking', get_string('enabletracking', 'registry'),
                    get_string('configenabletracking', 'registry'), 0, PARAM_INT));

    $roles = get_all_roles();
    $options=array();
    foreach($roles as $role) {
        $name = ( $role->name ) ? $role->name : $role->shortname;
        $options[$role->id] = $name;
    }

    list($usql, $params) = $DB->get_in_or_equal(array('editingteacher', 'teacher'));
    $defaultroles = $DB->get_records_select('role', " shortname $usql ", $params, '', 'id, name');

    $settings->add(new admin_setting_configmultiselect('registry/checkedroles', get_string('checkedroles', 'registry'), get_string('configcheckedroles', 'registry'), array_keys($defaultroles), $options));

    $settings->add(new admin_setting_configcheckbox('registry/excludecourses', get_string('excludecourses', 'registry'),
                    get_string('configexcludecourses', 'registry'), 0, PARAM_INT));

    $settings->add(new admin_setting_configmultiselect('registry/rolesreviewers', get_string('rolesreviewers', 'registry'), get_string('configrolesreviewers', 'registry'), array_keys($defaultroles), $options));

    $default = $DB->get_field('role', 'id', array('shortname'=>'staff'));
    $settings->add(new admin_setting_configselect('registry/reviewerrole', get_string('reviewerrole', 'registry'), get_string('configreviewerrole', 'registry'), $default, $options));

}


