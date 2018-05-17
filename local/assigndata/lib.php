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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * System wide hooks for local assigndata plugin
 *
 * @package local_assigndata
 * @copyright  2017 Enrique Castro @ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined ( 'MOODLE_INTERNAL' ) || die ();


function local_assigndata_extend_settings_navigation(settings_navigation $nav, context $context) {
    global $PAGE;
    
    if(get_config('local_assigndata', 'enabledassigndata') &&  
                                        (strpos($PAGE->pagetype, '-assign') !== false)) {
        if ($settingsnode = $nav->find('modulesettings', navigation_node::TYPE_SETTING)) {
            if (has_capability('local/assigndata:manage', $context)) { 
                $node = navigation_node::create(get_string('managemetadata', 'local_assigndata'),
                        new moodle_url('/local/assigndata/view.php', array('id'=>$PAGE->cm->id)),
                        navigation_node::TYPE_SETTING, null, 'assigndata_managefields',
                        new pix_icon('i/withsubcat', ''));
                $settingsnode->add_node($node);
            }
        }
    }
}


/**
 * given a field id
 * this function creates an instance of the particular subfield class
 *
 * @global object
 * @param string $type
 * @param int $assignid
 * @return object
 */
function local_assigndata_get_field_new($type, $assignid) {
    global $CFG;
    require_once($CFG->dirroot.'/mod/data/field/'.$type.'/field.class.php');
    include_once($CFG->dirroot.'/local/assigndata/fields/field_'.$type.'.php');

    $newfield = '\local_assigndata\field_'.$type;

    $newfield = new $newfield(0, $assignid);
    return $newfield;
}

/**
 * returns a subclass field object given a record of the field, used to
 * invoke plugin methods
 * input: $param $field - record from db
 *
 * @global object
 * @param object $field
 * @param int $assignid
 * @param object $cm
 * @return object
 */
function local_assigndata_get_field($field, $assignid, $cm=null) {
    global $CFG;

    if ($field) {
        require_once($CFG->dirroot.'/mod/data/lib.php');
    
        require_once($CFG->dirroot.'/mod/data/field/'.$field->type.'/field.class.php');

        include_once($CFG->dirroot.'/local/assigndata/fields/field_'.$field->type.'.php');

        $newfield = '\local_assigndata\field_'.$field->type;
        $newfield = new $newfield($field, $assignid, $cm);
        return $newfield;
    }
}

/**
 * given a field id
 * this function creates an instance of the particular subfield class
 *
 * @global object
 * @param int $fieldid
 * @param object $data
 * @return bool|object
 */
function local_assigndata_get_field_from_id($fieldid, $assignid){
    global $DB;

    $field = $DB->get_record('local_assigndata_fields', array('id'=>$fieldid, 'assignment'=>$assignid));

    if ($field) {
        return local_assigndata_get_field($field, $assignid);
    } else {
        return false;
    }
}
