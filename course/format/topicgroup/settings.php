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
 * Topicgroups general settings
 *
 * @package format_topicgroup
 * @copyright 2013 E. Castro
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */



defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    /* Default course display.
     * Course display default, can be either one of:
     * COURSE_DISPLAY_SINGLEPAGE or - All sections on one page.
     * COURSE_DISPLAY_MULTIPAGE     - One section per page.
     * as defined in moodlelib.php.
     */

    $name = 'format_topicgroup/defaultcoursedisplay';
    $title = get_string('defaultcoursedisplay', 'format_topicgroup');
    $description = get_string('defaultcoursedisplay_desc', 'format_topicgroup');
    $default = COURSE_DISPLAY_SINGLEPAGE;
    $choices = array(
        COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single'),
        COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi')
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    $roles = get_all_roles();
    $options=array();
    foreach($roles as $role) {
        $options[$role->id] = $role->name;
    }

    list($usql, $params) = $DB->get_in_or_equal(array('editingcoordteacher'));
    $defaulteditroles = $DB->get_records_select('role', " shortname $usql ", $params, '', 'id, name');

    $settings->add(new admin_setting_configmultiselect('format_topicgroup/editingroles', get_string('editingroles', 'format_topicgroup'), get_string('editingroles_desc', 'format_topicgroup'), array_keys($defaulteditroles), $options));

    $like = $DB->sql_like('shortname', '?');
    $defaultroles = $DB->get_records_select('role', " $like ", array('%teacher%'), '', 'id, name');
    $defaultroles = array_diff_key($defaultroles, $defaulteditroles);

    $settings->add(new admin_setting_configmultiselect('format_topicgroup/restrictedroles', get_string('restrictedroles', 'format_topicgroup'), get_string('restrictedroles_desc', 'format_topicgroup'), array_keys($defaultroles), $options));

}