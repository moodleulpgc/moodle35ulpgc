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
 * Version details
 *
 * @package    mod
 * @subpackage bootstrapelements
 * @copyright  2014 Birmingham City University <michael.grant@bcu.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $bootstrapelements
 * @return bool|int
 */
function bootstrapelements_add_instance($bootstrapelements) {
    global $DB;

    $bootstrapelements->timemodified = time();
    return $DB->insert_record("bootstrapelements", $bootstrapelements);
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $bootstrapelements
 * @return bool
 */
function bootstrapelements_update_instance($bootstrapelements) {
    global $DB;

    $bootstrapelements->timemodified = time();
    $bootstrapelements->id = $bootstrapelements->instance;

    return $DB->update_record("bootstrapelements", $bootstrapelements);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function bootstrapelements_delete_instance($id) {
    global $DB;

    if (! $bootstrapelements = $DB->get_record("bootstrapelements", array("id" => $id))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("bootstrapelements", array("id" => $bootstrapelements->id))) {
        $result = false;
    }

    return $result;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 * See get_array_of_activities() in course/lib.php
 *
 * @global object
 * @param object $coursemodule
 * @return cached_cm_info|null
 */
function bootstrapelements_get_coursemodule_info($coursemodule) {
    global $DB;

    if ($bootstrapelements = $DB->get_record('bootstrapelements', array('id' => $coursemodule->instance),
            'id, name, intro, introformat, title, bootstraptype, bootstrapicon')) {
        if (!$bootstrapelements->name || $bootstrapelements->name == 'bootstrapelements') {
            $bootstrapelements->name = "bootstrapelements".$bootstrapelements->id;
            $DB->set_field('bootstrapelements', 'name', $bootstrapelements->name, array('id' => $bootstrapelements->id));
        }

        $info = new cached_cm_info();
        // No filtering hre because this info is cached and filtered later.

        switch($bootstrapelements->bootstraptype) {
            case 0:
                $info->content = bootstrapelements_modal_outline($bootstrapelements->name, $bootstrapelements->title,
                        format_module_intro('bootstrapelements', $bootstrapelements, $coursemodule->id, false),
                        $bootstrapelements->bootstrapicon).
                        bootstrapelements_modal_button($bootstrapelements->name, $bootstrapelements->title,
                        $bootstrapelements->bootstrapicon);
            break;

            case 1:
                $info->content = bootstrapelements_toggle_outline($bootstrapelements->name, $bootstrapelements->title,
                        format_module_intro('bootstrapelements', $bootstrapelements, $coursemodule->id, false),
                        $bootstrapelements->bootstrapicon);
            break;

            case 2:
                $info->content = bootstrapelements_standard($bootstrapelements->name, $bootstrapelements->title,
                        format_module_intro('bootstrapelements', $bootstrapelements, $coursemodule->id, false),
                        $bootstrapelements->bootstrapicon);
            break;

            case 3:
                $info->content = bootstrapelements_blockquote($bootstrapelements->name, $bootstrapelements->title,
                        format_module_intro('bootstrapelements', $bootstrapelements, $coursemodule->id, false),
                        $bootstrapelements->bootstrapicon);
            break;
        }

        $info->name  = $bootstrapelements->name;
        return $info;
    } else {
        return null;
    }
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 *
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function bootstrapelements_reset_userdata($data) {
    return array();
}

/**
 * Returns all other caps used in module
 *
 * @return array
 */
function bootstrapelements_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * @uses FEATURE_IDNUMBER
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return bool|null True if module supports feature, false if not, null if doesn't know
 */
function bootstrapelements_supports($feature) {
    switch($feature) {
        case FEATURE_IDNUMBER: {
            return false;
        }
        case FEATURE_GROUPS: {
            return false;
        }
        case FEATURE_GROUPINGS: {
            return false;
        }
        case FEATURE_GROUPMEMBERSONLY: {
            return true;
        }
        case FEATURE_MOD_INTRO: {
            return true;
        }
        case FEATURE_COMPLETION_TRACKS_VIEWS: {
            return false;
        }
        case FEATURE_GRADE_HAS_GRADE: {
            return false;
        }
        case FEATURE_GRADE_OUTCOMES: {
            return false;
        }
        case FEATURE_MOD_ARCHETYPE: {
            return MOD_ARCHETYPE_RESOURCE;
        }
        case FEATURE_BACKUP_MOODLE2: {
            return true;
        }
        case FEATURE_NO_VIEW_LINK: {
            return true;
        }

        default: {
            return null;
        }
    }
}

function bootstrapelements_standard($name, $title, $content, $icon) {
    $output = html_writer::start_tag('div');

    $output .= html_writer::tag('h4', '<i class="fa '.$icon.'"></i>'.$title);

    $output .= html_writer::tag('div', $content);

    $output .= html_writer::end_tag('div');

    return $output;
}

function bootstrapelements_toggle_outline($togglename, $toggletitle, $togglecontent, $icon) {
    $output = html_writer::start_tag('div', array(
        'class' => 'mod-bootstrapelements-toggle'
    ));

    $output .= html_writer::start_tag('div', array(
        'class' => 'panel-heading'
    ));

    $output .= html_writer::start_tag('h4', array(
        'class' => 'panel-title'
    ));

    $output .= html_writer::tag('a', '<i class="fa '.$icon.'"></i>'.$toggletitle, array(
        'data-toggle' => 'collapse',
        'class' => 'accordion-toggle collapsed',
        'href' => '#'.$togglename
    ));

    $output .= html_writer::end_tag('h4');

    $output .= html_writer::end_tag('div');

    $output .= html_writer::start_tag('div', array(
        'id' => $togglename,
        'class' => 'panel-collapse collapse'
    ));

    $output .= html_writer::tag('div', $togglecontent, array(
        'class' => 'panel-body'
    ));

    $output .= html_writer::end_tag('div');

    $output .= html_writer::end_tag('div');

    return $output;
}

function bootstrapelements_modal_outline($modalname, $modaltitle, $modalcontent, $icon) {
    $output = html_writer::start_tag('div', array(
        'id' => $modalname,
        'class' => 'modal hide fade',
        'role' => 'dialog',
        'aria-labelledby' => 'myModalLabel',
        'aria-hidden' => 'true'
    ));

    $output .= html_writer::start_tag('div', array(
        'class' => 'modal-dialog'
    ));

    $output .= html_writer::start_tag('div', array(
        'class' => 'modal-content'
    ));

    $output .= html_writer::start_tag('div', array(
        'class' => 'modal-header'
    ));

    $output .= html_writer::start_tag('h4', array(
        'class' => 'modal-title'
    ));

    $output .= '<i class="fa '.$icon.'"></i>';

    $output .= $modaltitle;

    $output .= html_writer::end_tag('h4');

    $output .= html_writer::end_tag('div');

    $output .= html_writer::start_tag('div', array(
        'class' => 'modal-body'
    ));

    $output .= $modalcontent;

    $output .= html_writer::end_tag('div');

    $output .= html_writer::start_tag('div', array(
        'class' => 'modal-footer'
    ));

    $output .= html_writer::start_tag('button', array(
        'type' => 'button',
        'class' => 'btn btn-default',
        'data-dismiss' => 'modal'
    ));

    $output .= 'Close';

    $output .= html_writer::end_tag('button');

    $output .= html_writer::end_tag('div');

    $output .= html_writer::end_tag('div');

    $output .= html_writer::end_tag('div');

    $output .= html_writer::end_tag('div');

    $output .= html_writer::start_tag('div', array(
        'class' => 'text-left'
    ));

    return $output;
}

function bootstrapelements_modal_button($modalname, $modaltitle, $icon) {
    $output = html_writer::start_tag('button', array(
        'class' => 'btn btn-primary btn-lg',
        'data-toggle' => 'modal',
        'data-target' => '#'.$modalname
    ));
    $output .= '<i class="fa '.$icon.'"></i>';
    $output .= $modaltitle;
    $output .= html_writer::end_tag('button');
    $output .= html_writer::end_tag('div');
    return $output;
}

function bootstrapelements_blockquote($name, $title, $content, $icon) {
    $output = html_writer::start_tag('blockquote');

    $output .= html_writer::tag('h4', '<i class="fa '.$icon.'"></i>'.$title);

    $output .= $content;

    $output .= html_writer::end_tag('blockquote');
    return $output;
}