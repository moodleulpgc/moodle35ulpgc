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
 * Prints the management interface of an instance of examregistrar
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/mod/examregistrar/locallib.php');
require_once($CFG->dirroot."/mod/examregistrar/manage/manage_forms.php");
require_once($CFG->dirroot."/mod/examregistrar/manage/manage_table.php");

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$e  = optional_param('e', 0, PARAM_INT);  // examregistrar instance ID - it should be named as the first character of the module
$examcm  = optional_param('ex', 0, PARAM_INT);  //

if($examcm) {
        $cm         = get_coursemodule_from_id('exam', $examcm, 0, false, MUST_EXIST);
        $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $defaulter  = get_config('examregistrar', 'defaultregistrar');
        $examregistrar  = $DB->get_record('examregistrar', array('id' => $defaulter->instance), '*', MUST_EXIST);
} else {
    if ($id) {
        $cm         = get_coursemodule_from_id('examregistrar', $id, 0, false, MUST_EXIST);
        $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $examregistrar  = $DB->get_record('examregistrar', array('id' => $cm->instance), '*', MUST_EXIST);
    } elseif ($e) {
        $examregistrar  = $DB->get_record('examregistrar', array('id' => $e), '*', MUST_EXIST);
        $course     = $DB->get_record('course', array('id' => $examregistrar->course), '*', MUST_EXIST);
        $cm         = get_coursemodule_from_instance('examregistrar', $examregistrar->id, $course->id, false, MUST_EXIST);
    } else {
        error('You must specify a course_module ID or an instance ID');
    }
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$edit   = optional_param('edit', '', PARAM_ALPHANUMEXT);  // list/edit items
$action = optional_param('action', '', PARAM_ALPHANUMEXT);  // complex action not managed by edit
$upload = optional_param('csv', '', PARAM_ALPHANUMEXT);  // upload CSV file

$itemid   = optional_param('item', 0, PARAM_INT); // add/update items
$delete   = optional_param('del', 0, PARAM_INT); // delete items
$show     = optional_param('show', 0, PARAM_INT); // control visibility of items
$batch    = optional_param('batch', '', PARAM_ALPHANUMEXT);  // repetitive action on selected table items
$tbitems  = optional_param_array('items', array(), PARAM_INT);  // repetitive action on selected table items
$download = optional_param('download', '', PARAM_ALPHA);
$perpage  = optional_param('perpage', 100, PARAM_INT);

$baseurl = new moodle_url('/mod/examregistrar/manage.php', array('id' => $cm->id, 'edit' => $edit));

$includefile = '';

$examregprimaryid = examregistrar_get_primaryid($examregistrar);
$examregistrar->config = examregistrar_get_instance_configdata($examregistrar);

/// Set the page header
$PAGE->set_url($baseurl);
$PAGE->set_title(format_string($examregistrar->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_activity_record($examregistrar);

if($edit) {
    $baseurl->param('edit', $edit);
    $PAGE->navbar->add(get_string($edit, 'examregistrar'), $baseurl);
} else {
    $PAGE->navbar->add(get_string('manage', 'examregistrar'), $baseurl);
}

$output = $PAGE->get_renderer('mod_examregistrar');

/// check permissions
$canview = has_any_capability(array('mod/examregistrar:view', 'mod/examregistrar:viewall'), $context);
$canbook = has_any_capability(array('mod/examregistrar:book', 'mod/examregistrar:bookothers'), $context);
$canreview = has_any_capability(array('mod/examregistrar:submit', 'mod/examregistrar:review'), $context);
$canprintexams = has_any_capability(array('mod/examregistrar:download', 'mod/examregistrar:beroomstaff'), $context);
$canprintrooms = has_any_capability(array('mod/examregistrar:download', 'mod/examregistrar:beroomstaff'), $context);

$caneditelements = has_capability('mod/examregistrar:editelements',$context);
$canmanageperiods = has_capability('mod/examregistrar:manageperiods',$context);
$canmanageexams = has_capability('mod/examregistrar:manageexams',$context);
$canmanagelocations = has_capability('mod/examregistrar:managelocations',$context);
$canmanageseats = has_capability('mod/examregistrar:manageseats',$context);
$canmanage = $caneditelements || $canmanageperiods || $canmanageexams || $canmanagelocations || $canmanageseats;

if(!$canmanage) {
    $url = new moodle_url('/mod/examregistrar/view.php', array('id' => $cm->id));
    redirect($url, get_string('notamanager', 'examregistrar'));
}

$tab = 'manage';

$eventdata = array();
//$eventdata['objecttable'] = 'examregistrar_'.$edit;
$eventdata['context'] = $context;
$eventdata['other'] = array();
$eventdata['other']['edit'] = $edit;


///////////////////////////////////////////////////////////////////////////////

//  print_object($_GET);
//  print_object("_GET -----------------");
//  print_object($_POST);
//  print_object("_POST -----------------");

/// process forms actions

/// upload actions
if($upload) {
    $confirm = optional_param('confirm', 0, PARAM_BOOL);
    $session = optional_param('examsession', 0, PARAM_INT);
    $bookedsite   = optional_param('venue', '', PARAM_INT);
    $heading = get_string('uploadcsv'.$upload, 'examregistrar');

    $message = '';
    $delay = 0;

    $mform = new examregistrar_uploadcsv_form(null, array('exreg' => $examregistrar, 'csv'=>$upload, 'id'=>$cm->id, 'edit'=>$edit,
                                                        'session'=>$session, 'venue'=>$bookedsite));

    $importid = optional_param('importid', 0, PARAM_INT);
    $draftid = optional_param('draftid', 0, PARAM_INT);
    $ignoremodified = optional_param('ignoremodified', 0, PARAM_BOOL);
    $editidnumber = optional_param('editidnumber', 0, PARAM_BOOL);
    $examsession = optional_param('examsession', 0, PARAM_INT);
    $encoding = optional_param('encoding', 'utf-8', PARAM_ALPHANUMEXT);
    $delimiter = optional_param('delimiter', 'comma', PARAM_ALPHANUMEXT);

    if ($mform->is_cancelled() ) {
        redirect($baseurl);
    } else if (($data = $mform->get_data()) && ($csvdata = $mform->get_file_content('uploadfile'))) {
        /// get confirmation
        $importid = csv_import_reader::get_new_iid('examregistrar_upload_'.$upload);
        // File exists and was valid.
        $ignoremodified = !empty($data->ignoremodified);
        $draftid = $data->uploadfile;
        $mform = new examregistrar_uploadcsv_confirm_form(null, array('examregid' => $examregprimaryid,
                                                                    'edit'=>$edit, 'csv'=>$upload, 'id'=>$cm->id,
                                                                        'importid' => $importid,
                                                                        'draftid' => $draftid,
                                                                        'examsession' => $examsession,
                                                                        'ignoremodified'=> $ignoremodified,
                                                                        'editidnumber' => $editidnumber,
                                                                        'encoding' => $encoding,
                                                                        'delimiter' => $delimiter ));
        echo $output->header();
        echo $output->heading($heading);
        $mform->display();
        echo $output->footer();
        die;
    } else if($confirm && confirm_sesskey()) {
        $data = data_submitted();
        if (isset($data->cancel) && $data->cancel) {
            redirect($baseurl);
        }
        $message = '';
        /// process form & store element in database
        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftid, 'id DESC', false)) {
            redirect(new moodle_url('view.php',
                                array('id'=>$this->assignment->get_course_module()->id,
                                    'action'=>'grading')));
        }
        $file = reset($files);

        $csvdata = $file->get_content();

//         //print_object($csvdata);
//         //print_object(" csvdata ON MAIN -----");


        $columns = '';
        if ($csvdata) {
            $csvreader = new csv_import_reader($importid, 'examregistrar_upload_'.$upload);
            $csvreader->load_csv_content($csvdata, $encoding, $delimiter);
            $csvreader->init();
            $columns = $csvreader->get_columns();
        }


        if (!$columns) {
            print_error('invaliduploadcsvimport', 'examregistrar', $baseurl);
            die;
        }

        if($upload == 'assignseats') {
            /// assignseats do not simply modify a table, but need separate processing
            require_capability('mod/examregistrar:manageseats',$context);
            $requiredfields = array('city', 'num','shortname','fromoom','toroom');
            if($error = array_diff($requiredfields, $columns)) {
                print_error('invaliduploadcsvimport', 'examregistrar', $baseurl);
                redirect($baseurl, $message, $delay);
            }
            $seatassigns = array();
            while ($record = $csvreader->next()) {
                $seatassigns[] = array_combine($columns, $record);
            }
            $message = examregistrar_loadcsv_roomallocations($examregistrar, $data->examsession, $seatassigns, $ignoremodified);
        } else {
            $updatecount = 0;
            $insertcount = 0;
            
            switch($upload) {
                case 'elements'  : $requiredfields = array('name', 'idnumber', 'type');
                                    require_capability('mod/examregistrar:editelements',$context);
                                    break;
                case 'periods'  : $requiredfields = array('name', 'idnumber', 'annuality', 'periodtype', 'term');
                                    require_capability('mod/examregistrar:manageperiods',$context);
                                    break;
                case 'examsessions'  : $requiredfields = array('name', 'idnumber', 'period', 'annuality');
                                    require_capability('mod/examregistrar:manageperiods',$context);
                                    break;
                case 'locations' : $requiredfields = array('name', 'idnumber', 'locationtype', 'seats');
                                    require_capability('mod/examregistrar:managelocations',$context);
                                    break;
                case 'staffers' : $requiredfields = array('room', 'locationtype', 'role');
                                    require_capability('mod/examregistrar:managelocations',$context);
                                    break;
                default          : $requiredfields = array('name', 'idnumber', 'type');
            }
            if($error = array_diff($requiredfields, $columns)) {
                print_error('invaliduploadcsvimport', 'examregistrar', $baseurl);
                redirect($baseurl, $message, $delay);
            }
            $messagelist = array();
            $insertcount = 0;
            while ($record = $csvreader->next()) {

                switch($upload) {
                    case 'elements'  : $success = examregistrar_loadcsv_elements($examregistrar, array_combine($columns, $record), $ignoremodified);
                                        break;
                    case 'periods' : $success = examregistrar_loadcsv_periods($examregistrar, array_combine($columns, $record), $ignoremodified, $editidnumber);
                                        break;
                    case 'examsessions' : $success = examregistrar_loadcsv_sessions($examregistrar, array_combine($columns, $record), $ignoremodified, $editidnumber);
                                        break;
                    case 'locations' : $success = examregistrar_loadcsv_locations($examregistrar, array_combine($columns, $record), $ignoremodified, $editidnumber);
                                        break;
                    case 'staffers' : $success = examregistrar_loadcsv_staffers($examregistrar, $data->examsession, array_combine($columns, $record), $ignoremodified, $editidnumber);
                                        break;
                    default          :  redirect($baseurl, $message, $delay);
                }

                if($success) {
                    if(is_int($success) && $item = (int)$success) {
                        $eventdata['objectid'] = abs($item);
                        $event = ($item > 0) ? \mod_examregistrar\event\manage_created::created($eventdata, 'examregistrar_'.$upload) :
                                                    \mod_examregistrar\event\manage_updated::created($eventdata, 'examregistrar_'.$upload);
                        $event->trigger();
                        $insertcount +=1;
                    } elseif(is_string($success)) {
                        $messagelist[] = $success;
                    }
                }
            }
            $message = get_string('csvuploadsuccess', 'examregistrar', $insertcount).' <br /> '.implode('<br />', $messagelist);
        }

        if(!$message) {
            $message = get_string('changessaved');
        }
        redirect($baseurl, $message, $delay);

    }

    /// Print the upload page header

    $PAGE->navbar->add($heading);
    echo $output->header();
    echo $output->heading($heading);
    $mform->display();
    echo $output->footer();
    die;

} elseif($edit == 'exams' && $action == 'generate') {
    /// generate exams table from existing courses
    /// if data && confirmed, do it
    $formdata = data_submitted();
    if(isset($formdata->cancel)) {
        redirect($baseurl);
    }

    if ($formdata && confirm_sesskey() && isset($formdata->confirmed) && $formdata->confirmed) {
        /// DO generate exams
        $data = new stdClass;
        foreach($formdata as $key => $value) {
            if(substr($key, 0,2) == '__') {
                $k = substr($key, 2);
                $data->$k = $value;
            }
        }
        $message = examregistrar_generateexams_fromcourses($examregistrar, $data);
        if($message) {
            $delay = 5;
            redirect($baseurl, $message, $delay);
        }
    } else {
        /// if no data && not confirmed, present interface
        $items = optional_param('courses', '', PARAM_TEXT);
        $heading = get_string('generateexams', 'examregistrar');
        $mform = new examregistrar_generateexams_form(null, array('exreg' => $examregistrar, 'cmid'=>$cm->id, 'edit'=>$edit, 'items'=>$items));
        if ($mform->is_cancelled()) {
            redirect($baseurl);
        } elseif ($formdata = $mform->get_data()) {
            unset($formdata->submitbutton);
            $mform = new examregistrar_generateexams_confirm_form(null, array('exreg' => $examregistrar, 'cmid'=>$cm->id, 'edit'=>$edit, 'items'=>$items,  'confirm'=>get_object_vars($formdata)));
            $mform->set_data($formdata);
            if ($mform->is_cancelled()) {
                redirect($baseurl);
            }
        }
        /// Print the generate exams interface
        $PAGE->navbar->add($heading);
        echo $output->header();
        echo $output->heading($heading);
        $mform->display();
        echo $output->footer();
        die;
    }

} elseif($edit == 'exams' && $action == 'qc') {
    $includefile = "manage/examsqc.php";
} elseif($edit) {
    /// edit actions, manage elements in tables
    $itemid   = optional_param('item', 0, PARAM_INT); // add/update items
    $delete   = optional_param('del', 0, PARAM_INT); // delete items
    $show     = optional_param('show', 0, PARAM_INT); // control visibility of items
    $batch    = optional_param('batch', '', PARAM_ALPHANUMEXT);  // repetitive action on selected table items
    $download = optional_param('download', '', PARAM_ALPHA);

    
    $itemname = substr($edit, 0, -1);
    $itemtable = 'examregistrar_'.$edit;
    if($edit == 'staffers') {
        $includefile = 'manage/locations.php';
        $editurl = new moodle_url($baseurl, array('id' => $cm->id, 'edit'=>'locations'));
    } else {
        $includefile = "manage/$edit.php";
        $editurl = new moodle_url($baseurl, array('id' => $cm->id, 'edit'=>$edit));
    }
    
    $delay = 5;
    if($edit && $itemid) {
            $formclass = 'examregistrar_'.$itemname.'_form';
            $mform = new $formclass(null, array('exreg' => $examregistrar, 'item'=>$itemid, 'cmid'=>$cm->id));
            $element = false;
            if($itemid > 0) {
                // we are updating an existing element
                if($element = $DB->get_record($itemtable, array('id' => $itemid))) {
                    if($edit == 'exams') {
                        if($element->callnum < 0) {
                            $element->callnum = abs($element->callnum);
                            $element->additional = 1;
                        }
                    }
                    $mform->set_data($element);
                }
                $heading = get_string('update'.$itemname, 'examregistrar');
            } else {
                $heading = get_string('add'.$itemname, 'examregistrar');
            }

            if ($mform->is_cancelled()) {
                //redirect($baseurl);
                $itemid = 0;
            } elseif ($formdata = $mform->get_data()) {
                /// process form & store element in database
                $data = examregistrar_extract_edititem_formdata($edit, $formdata);

                unset($eventdata['other']['name']);
                if($element) { // this means itemid > 0 and record exists, over-write & update
                    $data->id = $element->id;
                    if($success = $DB->update_record($itemtable, $data)) {
                        $eventdata['objectid'] = $data->id;
                        $event = \mod_examregistrar\event\manage_updated::created($eventdata, $itemtable);
                        $event->trigger();
                    }

                } else {
                    if($data->id = $DB->insert_record($itemtable, $data)) {
                        $eventdata['objectid'] = $data->id;
                        $event = \mod_examregistrar\event\manage_created::created($eventdata, $itemtable);
                        $event->trigger();
                    }
                }
                
                if($data->id && $itemtable == 'examregistrar_locations') {
                    examregistrar_set_location_tree($data->id);
                }

                $itemid = 0;
                //redirect($baseurl, get_string('changessaved'), $delay);
            }
            if($itemid) {
                echo $output->header();
                echo $output->heading($heading);
                $mform->display();
                echo $output->footer();
                die;
            }
    }

    /// confirm delete examregistrar element record
    if($edit && $delete) {
        $delete = optional_param('del', 0, PARAM_INT);
        $delete = $DB->get_record($itemtable, array('id'=>$delete));
        if(isset($delete->id) && $delete->id) {
            $info = new stdClass;
            $info->type = get_string($itemname.'item', 'examregistrar');
            if($edit == 'exams') {
                list($info->name,  $info->idnumber) = examregistrar_get_namecodefromid($delete->id, 'exams');
            } else {
                list($info->name,  $info->idnumber) = examregistrar_item_getelement($delete, $itemname);
            }
            $confirm = optional_param('confirm', 0, PARAM_BOOL);
            if(!$confirm) {
                $PAGE->navbar->add(get_string('delete'));
                $confirmurl = new moodle_url($baseurl, array('edit'=>$edit, 'del' => $delete->id, 'confirm' => 1));
                /// TODO check dependencies /// TODO check dependencies /// TODO check dependencies
                $message = get_string('delete_confirm', 'examregistrar', $info);
                echo $output->header();
                echo $output->confirm($message, $confirmurl, $baseurl);
                echo $output->footer();
                die;
            } else if(confirm_sesskey()){
                /// confirmed, proceed with deletion
                /// TODO check dependencies /// TODO check dependencies /// TODO check dependencies

                if ($DB->delete_records($itemtable, array('id'=>$delete->id))) {
                    $name = isset($info->name) ? $info->name : $delete->id; 
                    $eventdata['objectid'] = $delete->id;
                    $eventdata['other']['name'] = $name;
                    $event = \mod_examregistrar\event\manage_deleted::created($eventdata, $itemtable);
                    $event->trigger();
                    $delete = 0;
                }
            }
        }
    }

    /// change visibility of item
    if($edit && $show) {
        $visible = ($show > 0) ? 1 : 0;
        $DB->set_field($itemtable, 'visible', $visible, array('id'=>abs($show)));
    }

    $items  = optional_param_array('items', array(), PARAM_INT);  // repetitive action on selected table items
    if($edit && $batch && $items) {
        $batchaction = optional_param($batch, '', PARAM_ALPHANUMEXT);
        $confirm = optional_param('confirm', 0, PARAM_BOOL);
        if(!$confirm) {
            $label = in_array($batch, array('show', 'hide', 'delete')) ? get_string($batch) : get_string($batch, 'examregistrar');
            $PAGE->navbar->add($label);
            $confirmurl = new moodle_url($baseurl, array('edit'=>$edit, 'batch' => $batch, $batch=>$batchaction, 'confirm' => 1));
            foreach($items as $item) {
                $confirmurl->param("items[$item]", $item);
            }
            $info = new stdClass;
            $info->action = $label;
            $info->type = get_string($edit, 'examregistrar');
            foreach($items as $key => $item) {
                list($info->name,  $info->idnumber) = examregistrar_get_namecodefromid($item, $edit, $itemname);
                $items[$key] = $info->name;
            }
            $info->list = html_writer::alist($items);
            $message = get_string('batch_confirm', 'examregistrar', $info);
            echo $output->header();
            echo $output->confirm($message, $confirmurl, $baseurl);
            echo $output->footer();
            die;
        } elseif(confirm_sesskey()){
            /// confirmed, proceed with action
            unset($eventdata['other']['name']); 
            if($batch == 'hide' || $batch == 'show') {
                $visible = ($batch == 'show') ? 1 : 0;
                list($insql, $inparams) = $DB->get_in_or_equal($items);
                $DB->set_field_select($itemtable, 'visible', $visible, " id $insql ", $inparams);
            } elseif($batch == 'delete') {
                $DB->delete_records_list($itemtable, 'id', $items);
                    foreach($items as $i) {
                        $eventdata['objectid'] = $i;
                        $event = \mod_examregistrar\event\manage_deleted::created($eventdata, $itemtable);
                        $event->trigger();         
                    }       
            } elseif($batch == 'setsession') {
                list($insql, $inparams) = $DB->get_in_or_equal($items);
                $DB->set_field_select($itemtable, 'examsession', $batchaction, " id $insql ", $inparams);
                    foreach($items as $i) {
                        $eventdata['objectid'] = $i;
                        $event = \mod_examregistrar\event\manage_updated::created($eventdata,$itemtable);
                        $event->trigger();         
                    }                
            } elseif($batch == 'setparent') {
                list($insql, $inparams) = $DB->get_in_or_equal($items);
                $DB->set_field_select($itemtable, 'parent', $batchaction, " id $insql ", $inparams);
                foreach($items as $item) {
                    examregistrar_set_location_tree($item);
                }
                /// TODO  build location depth & tree path /// TODO  build location depth & tree path
                /// TODO  build location depth & tree path /// TODO  build location depth & tree path
            } else {
                redirect($baseurl, get_string('unknownbatch', 'examregistrar'), $delay);
            }
        }
    }

    if($edit && $action == 'resetparents') {
        $parent = new stdClass();
        $parent->id = 0;
        $parent->path = '';
        examregistrar_rebuild_location_paths($parent);
    }
}


////////////////////////////////////////////////////////////////////////////////

$event = \mod_examregistrar\event\manage_viewed::created($eventdata);
$event->add_record_snapshot('course_modules', $cm);
$event->trigger();

$table = new examregistrar_management_table('examregistrar-manage-edit-'.$edit.$examregistrar->id);

$filename = clean_filename('examregistrar_table_'.$edit.'_'.userdate(time(), '%Y%m%d-%H%M'));
$table->is_downloading($download, $filename, $edit);

/// Print the page header, Output starts here


 if (!$table->is_downloading()) {
    echo $output->header();

    // Add tabs, if needed
    include_once('tabs.php');
    /*
    if($canmanage) {
        $currenttab = 'manage';
        if(!$itemid) {
            examregistrar_print_tabs($id, $currenttab);
        }
    } else {
        $url = new moodle_url('/mod/examregistrar/view.php', array('id' => $cm->id));
        redirect($url, get_string('notamanager', 'examregistrar'));
    }*/

    echo $output->container_start(' examregistrarmanagelinks ');
    echo html_writer::empty_tag('hr');
        $editurl = new moodle_url($baseurl);
        $uploadurl = new moodle_url($baseurl);
        $actionurl = new moodle_url('/mod/examregistrar/manage/action.php', array('id' => $cm->id, 'edit'=>$edit));
        if($caneditelements) {
            $text = array();
            echo html_writer::nonempty_tag('span', get_string('elements', 'examregistrar').': ' , array('class'=>'examregistrarmanageheaders'));
            $editurl->param('edit', 'elements');
            $text[] = html_writer::link($editurl, get_string('editelements', 'examregistrar'));
            $uploadurl->param('csv', 'elements');
            $uploadurl->param('edit', 'elements');
            $text[] = html_writer::link($uploadurl, get_string('uploadcsvelements', 'examregistrar'));
            $actionurl->param('action', 'configparams');
            $text[] = html_writer::span(html_writer::link($actionurl, get_string('configparams', 'examregistrar')), 'configparams');            
            echo implode(',&nbsp;&nbsp;',$text).'<br />';
        }
        if($canmanageperiods) {
            $text = array();
            echo html_writer::nonempty_tag('span', get_string('periods', 'examregistrar').': ' , array('class'=>'examregistrarmanageheaders'));
            $editurl->param('edit', 'periods');
            $text[] = html_writer::link($editurl, get_string('editperiods', 'examregistrar'));
            $editurl->param('edit', 'examsessions');
            $text[] = html_writer::link($editurl, get_string('editexamsessions', 'examregistrar'));
            $uploadurl->param('csv', 'periods');
            $uploadurl->param('edit', 'periods');
            $text[] = html_writer::link($uploadurl, get_string('uploadcsvperiods', 'examregistrar'));
            $uploadurl->param('csv', 'examsessions');
            $uploadurl->param('edit', 'examsessions');
            $text[] = html_writer::link($uploadurl, get_string('uploadcsvexamsessions', 'examregistrar'));
            echo implode(',&nbsp;&nbsp;',$text).'<br />';
        }
        if($canmanageexams) {
            $text = array();
            echo html_writer::nonempty_tag('span', get_string('exams', 'examregistrar').': ' , array('class'=>'examregistrarmanageheaders'));
            $editurl->param('edit', 'exams');
            $text[] = html_writer::link($editurl, get_string('editexams', 'examregistrar'));
            $editurl->param('edit', 'exams');
            $editurl->param('action', 'generate');
            $text[] = html_writer::link($editurl, get_string('generateexams', 'examregistrar'));
            $uploadurl->param('csv', 'exams');
            $uploadurl->param('edit', 'exams');
            //$text[] = html_writer::link($uploadurl, get_string('uploadcsvexams', 'examregistrar'));
            $editurl->param('edit', 'exams');
            $editurl->param('action', 'qc');
            $text[] = html_writer::link($editurl, get_string('examsqc', 'examregistrar'));

            echo implode(',&nbsp;&nbsp;',$text).'<br />';
            
        }
        if($canmanagelocations) {
            $editurl->remove_params('action');
            $text = array();
            echo html_writer::nonempty_tag('span', get_string('locations', 'examregistrar').': ' , array('class'=>'examregistrarmanageheaders'));
            $editurl->param('edit', 'locations');
            $text[] = html_writer::link($editurl, get_string('editlocations', 'examregistrar'));
            $editurl->param('edit', 'locations');
            $editurl->param('action', 'resetparents');
            $text[] = html_writer::link($editurl, get_string('resetparents', 'examregistrar'));

            $editurl->param('edit', 'staffers');
            $editurl->param('item', -1);
            $text[] = html_writer::link($editurl, get_string('editstaffers', 'examregistrar'));
            $editurl->remove_params('item');
            $actionurl->param('action', 'assigntaffers');
            $text[] = html_writer::link($actionurl, get_string('assignstaffers', 'examregistrar'));
            $uploadurl->param('csv', 'locations');
            $uploadurl->param('edit', 'locations');
            $text[] = html_writer::link($uploadurl, get_string('uploadcsvlocations', 'examregistrar'));
            $uploadurl->param('csv', 'staffers');
            $uploadurl->param('edit', 'locations');
            $text[] = html_writer::link($uploadurl, get_string('uploadcsvstaffers', 'examregistrar'));
            echo implode(',&nbsp;&nbsp;',$text).'<br />';
        }

        if($canmanageseats) {
            echo html_writer::nonempty_tag('span', get_string('seatassignments', 'examregistrar').': ' , array('class'=>'examregistrarmanageheaders'));
            $text = array();
            $editurl->param('edit', 'session_rooms');
            $text[] = html_writer::link($editurl, get_string('editsessionrooms', 'examregistrar'));
            $actionurl->param('action', 'sessionrooms');
            $text[] = html_writer::link($actionurl, get_string('assignsessionrooms', 'examregistrar'));
            $uploadurl->param('csv', 'session_rooms');
            $uploadurl->param('edit', 'session_rooms');
            $text[] = html_writer::link($uploadurl, get_string('uploadcsvsession_rooms', 'examregistrar'));
            //$actionurl->param('action', 'assignseats');
            //$editurl->param('edit', 'assignseats');
            $url = new moodle_url('/mod/examregistrar/manage/assignseats.php', array('id'=>$cm->id, 'edit'=>'session_rooms'));
            $text[] = html_writer::link($url, get_string('assignseats', 'examregistrar'));
            $uploadurl->param('csv', 'assignseats');
            $uploadurl->param('edit', 'session_rooms');
            $text[] = html_writer::link($uploadurl, get_string('uploadcsvassignseats', 'examregistrar'));

            $actionurl->param('action', 'stafffromexam');
            $text[] = html_writer::link($actionurl, get_string('stafffromexam', 'examregistrar'));

            echo implode(',&nbsp;&nbsp;',$text).'<br />';

            echo html_writer::nonempty_tag('span', get_string('printingoptions', 'examregistrar').': ' , array('class'=>'examregistrarmanageheaders'));
            $text = array();
            $actionurl->param('action', 'roomprintoptions');
            $text[] = html_writer::link($actionurl, get_string('roomprintoptions', 'examregistrar'));
            $actionurl->param('action', 'examprintoptions');
            $text[] = html_writer::link($actionurl, get_string('examprintoptions', 'examregistrar'));
            $actionurl->param('action', 'binderprintoptions');
            $text[] = html_writer::link($actionurl, get_string('binderprintoptions', 'examregistrar'));
            $actionurl->param('action', 'userlistprintoptions');
            $text[] = html_writer::link($actionurl, get_string('userlistprintoptions', 'examregistrar'));
            $actionurl->param('action', 'bookingprintoptions');
            $text[] = html_writer::link($actionurl, get_string('bookingprintoptions', 'examregistrar'));


            echo implode(',&nbsp;&nbsp;',$text).'<br />';
        }

    echo html_writer::empty_tag('hr');
    echo $output->container_end();

}

/// Now include the remaining part and print footer

if($includefile) {
    include($includefile);
}


// Finish the page
 if (!$table->is_downloading()) {
 $PAGE->requires->js_init_call('M.mod_examregistrar.init_manage_table', array());
echo $output->footer();
}
