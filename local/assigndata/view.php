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
 * This file is the entry point to the local assigndata plugin. 
 * Displays the list of fields. Is modelled after mod_data/field.php page 
 *
 * @package local_assigndata
 * @copyright  2017 Enrique Castro @ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/assigndata/lib.php');
require_once($CFG->dirroot . '/local/assigndata/locallib.php');
require_once($CFG->dirroot . '/mod/data/lib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');


$id = required_param('id', PARAM_INT);                           // course module id
$a              = optional_param('a', 0, PARAM_INT);             // assignment id
$fid            = optional_param('fid', 0 , PARAM_INT);          // update field id
$newtype        = optional_param('newtype','',PARAM_ALPHA);      // type of the new field
$mode           = optional_param('mode','',PARAM_ALPHA);
$cancel         = optional_param('cancel', 0, PARAM_BOOL);

if ($cancel) {
    $mode = 'list';
}
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'assign');

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

require_capability('local/assigndata:view', $context);

$assign = new assign($context, $cm, $course);
$instance = $assign->get_instance();
$assignid = $instance->id;

$urlparams = array('id' => $id);
if ($fid !== 0) {
    $urlparams['fid'] = $fid;
}
if ($newtype !== '') {
    $urlparams['newtype'] = $newtype;
}
if ($mode !== '') {
    $urlparams['mode'] = $mode;
}
if ($cancel !== 0) {
    $urlparams['cancel'] = $cancel;
}


$url = new moodle_url('/local/assigndata/view.php', $urlparams);
$PAGE->set_url($url);

$PAGE->set_title($instance->name);
$PAGE->set_heading($course->fullname);


/************************************
 *        Data Processing           *
 ***********************************/

switch ($mode) {

    case 'add':    ///add a new field
        if (confirm_sesskey() and $fieldinput = data_submitted()){

            //$fieldinput->name = data_clean_field_name($fieldinput->name);

        /// Only store this new field if it doesn't already exist.
            if (($fieldinput->name == '') or data_fieldname_exists($fieldinput->name, $assignid)) {

                $displaynoticebad = get_string('invalidfieldname','data');

            } else {

            /// Check for arrays and convert to a comma-delimited string
                data_convert_arrays_to_strings($fieldinput);

            /// Create a field object to collect and store the data safely
                $type = required_param('type', PARAM_FILE);
                $field = local_assigndata_get_field_new($type, $assignid);

                $field->define_field($fieldinput);
                $field->insert_field();

                $displaynoticegood = get_string('fieldadded','data');
            }
        }
        break;


    case 'update':    ///update a field
        if (confirm_sesskey() and $fieldinput = data_submitted()){

            //$fieldinput->name = data_clean_field_name($fieldinput->name);

            if (($fieldinput->name == '') or local_assigndata_fieldname_exists($fieldinput->name, $assignid, $fieldinput->fid)) {

                $displaynoticebad = get_string('invalidfieldname','data');

            } else {
            /// Check for arrays and convert to a comma-delimited string
                data_convert_arrays_to_strings($fieldinput);

            /// Create a field object to collect and store the data safely
                $field = local_assigndata_get_field_from_id($fid, $assignid);
                $oldfieldname = $field->field->name;

                $field->field->name = $fieldinput->name;
                $field->field->description = $fieldinput->description;
                $field->field->required = !empty($fieldinput->required) ? 1 : 0;

                for ($i=1; $i<=10; $i++) {
                    if (isset($fieldinput->{'param'.$i})) {
                        $field->field->{'param'.$i} = $fieldinput->{'param'.$i};
                    } else {
                        $field->field->{'param'.$i} = '';
                    }
                }

                $field->update_field();

                $displaynoticegood = get_string('fieldupdated','data');
            }
        }
        break;


    case 'delete':    // Delete a field
        if (confirm_sesskey()){

            if ($confirm = optional_param('confirm', 0, PARAM_INT)) {


                // Delete the field completely
                if ($field = local_assigndata_get_field_from_id($fid, $assignid)) {
                    $field->delete_field();

                    $displaynoticegood = get_string('fielddeleted', 'data');
                }

            } else {

                local_assigndata_print_header($course,$cm,$instance, false);

                // Print confirmation message.
                $field = local_assigndata_get_field_from_id($fid, $assignid);

                echo $OUTPUT->confirm('<strong>'.$field->name().': '.$field->field->name.'</strong><br /><br />'. get_string('confirmdeletefield','data'),
                             'view.php?id='.$id.'&mode=delete&fid='.$fid.'&confirm=1',
                             'view.php?id='.$id);

                echo $OUTPUT->footer();
                exit;
            }
        }
        break;

    case 'moveup':    // Up one position
        $sortorder = $DB->get_field('local_assigndata_fields', 'sortorder', array('assignment' => $assignid, 'id' => $fid));
        $fup = $DB->get_field('local_assigndata_fields', 'id', array('assignment' => $assignid, 'sortorder' => ($sortorder-1)));
        $DB->set_field('local_assigndata_fields', 'sortorder', $sortorder-1, array('assignment' => $assignid, 'id' => $fid));
        $DB->set_field('local_assigndata_fields', 'sortorder', $sortorder, array('assignment' => $assignid, 'id' => $fup));
        break;

    case 'movedown':    // Up one position
        $sortorder = $DB->get_field('local_assigndata_fields', 'sortorder', array('assignment' => $assignid, 'id' => $fid));
        $fdown = $DB->get_field('local_assigndata_fields', 'id', array('assignment' => $assignid, 'sortorder' => ($sortorder+1)));
        $DB->set_field('local_assigndata_fields', 'sortorder', $sortorder+1, array('assignment' => $assignid, 'id' => $fid));
        $DB->set_field('local_assigndata_fields', 'sortorder', $sortorder, array('assignment' => $assignid, 'id' => $fdown));
        break;


    case 'sort':    // Set the default sort parameters
        if (confirm_sesskey()) {
            redirect($CFG->wwwroot.'/local/assigndata/view.php?id='.$id->id, get_string('changessaved'), 2);
            exit;
        }
        break;

    default:
        break;
}



/// Print the browsing interface

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('fieldsfor', 'local_assigndata', $instance->name));

///get the list of possible fields (plugins)
$plugins = core_component::get_plugin_list('datafield');
$menufield = array();
$usedplugins = get_directory_list($CFG->dirroot.'/local/assigndata/fields/', '.', false);
foreach($usedplugins as $key => $plugin) {
    $plugin = strstr($plugin, '.php', true);
    if(substr($plugin, 0, 6) == 'field_') {
        $usedplugins[$key] = substr($plugin, 6);
    } else {
        unset($usedplugins[$key]);
    }
}

foreach ($plugins as $plugin=>$fulldir){
    if(in_array($plugin, $usedplugins)) {
        $menufield[$plugin] = get_string('pluginname', 'datafield_'.$plugin);    //get from language files
    }
}
asort($menufield);    //sort in alphabetical order



if (($mode == 'new') && (!empty($newtype)) && confirm_sesskey()) {          ///  Adding a new field
    $field = local_assigndata_get_field_new($newtype, $assignid);
    $field->display_edit_field();

} else if ($mode == 'display' && confirm_sesskey()) { /// Display/edit existing field

    $field = local_assigndata_get_field_from_id($fid, $assignid);
    $field->display_edit_field();

} else {                                              /// Display the main listing of all fields

    // Trigger module viewed event.
    $event = \local_assigndata\event\list_viewed::create(array('context' => $context,
                                                               'courseid' => $course->id,
                                                               'other' => array(
                                                                    'assignment' => $assignid
                                                               )
                                                            ));
    $event->trigger();

    $max = 0;
    
    if (!$DB->record_exists('local_assigndata_fields', array('course'=>$course->id,  'assignment'=>$assignid))) {
        echo $OUTPUT->notification(get_string('nofieldindatabase','data'));  // nothing in database

    } else {    //else print quiz style list of fields

        $table = new html_table();
        $table->head = array(
            get_string('fieldname', 'data'),
            get_string('type', 'data'),
            get_string('required', 'data'),
            get_string('fielddescription', 'data'),
            get_string('action', 'data'),
        );
        $table->align = array('left','left','left', 'center');
        $table->wrap = array(false,false,false,false);


        if ($fff = $DB->get_records('local_assigndata_fields', array('course'=>$course->id,  'assignment'=>$assignid), 'sortorder ASC')) {
            $idx = 0;
            $max = count($fff);
            foreach ($fff as $ff) {

                $field = local_assigndata_get_field($ff, $assignid);

                $baseurl = new moodle_url('/local/assigndata/view.php', array(
                    'id'         => $id,
                    'fid'       => $field->field->id,
                    'sesskey'   => sesskey(),
                ));

                $displayurl = new moodle_url($baseurl, array(
                    'mode'      => 'display',
                ));

                $deleteurl = new moodle_url($baseurl, array(
                    'mode'      => 'delete',
                ));

                $action = html_writer::link($displayurl, $OUTPUT->pix_icon('t/edit', get_string('edit'))) .
                        '&nbsp;' .
                        html_writer::link($deleteurl, $OUTPUT->pix_icon('t/delete', get_string('delete')));
                        
                $movelinks = '';
                if (!$idx == 0) {
                    $alt = get_string('up');
                    $movelinks .= $OUTPUT->action_icon(new moodle_url($baseurl,
                                        array('mode' => 'moveup', 'sesskey' => sesskey())),
                                        new pix_icon('t/up', $alt, 'moodle', array('title' => $alt)),
                                        null, array('title' => $alt));
                } else {
                    $movelinks .= $OUTPUT->spacer(array('width'=>16));
                }
                if ($idx != ($max - 1)) {
                    $alt = get_string('down');
                    $movelinks .= $OUTPUT->action_icon(new moodle_url($baseurl,
                                        array('mode' => 'movedown', 'sesskey' => sesskey())),
                                        new pix_icon('t/down', $alt, 'moodle', array('title' => $alt)),
                                        null, array('title' => $alt));
                }

                
                $table->data[] = array(
                    html_writer::link($displayurl, $field->field->name),
                    $field->image() . '&nbsp;' . $field->name(),
                    $field->field->required ? get_string('yes') : get_string('no'),
                    shorten_text($field->field->description, 30), 
                    $action.' '.$movelinks,
                );
                $idx++;
            }
        }
        echo html_writer::table($table);
    }


    echo '<div class="fieldadd">';
    if($max <= get_config('local_assigndata', 'maxfields')) {
        $popupurl = $CFG->wwwroot.'/local/assigndata/view.php?id='.$id.'&mode=new&sesskey='.  sesskey();
        echo $OUTPUT->single_select(new moodle_url($popupurl), 'newtype', $menufield, null, array('' => 'choosedots'),
            'fieldform', array('label' => get_string('newfield', 'data') . $OUTPUT->help_icon('newfield', 'data')));
    } else {
        echo $OUTPUT->heading(get_string('maxfieldsreached', 'local_assigndata'), 5,  ' alert-info  ');
    }
    echo '</div>';

}





/// Finish the page
echo $OUTPUT->footer();
