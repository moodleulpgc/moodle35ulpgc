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
 * Prints an instance of mod_videolib.
 *
 * @package     mod_videolib
 * @copyright   2018 Enrique Castro @ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
//require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot.'/mod/videolib/locallib.php');
require_once($CFG->dirroot.'/mod/videolib/manage_forms.php');

// Course_module ID, or
$id = required_param('id', PARAM_INT);
// ... action to perform.
$action  = optional_param('a', 'view', PARAM_ALPHANUMEXT);

if ($id) {
    list($course, $cm) = get_course_and_cm_from_cmid($id, '');
    $videolib = $DB->get_record('videolib', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid'));
}

require_course_login($course, true, $cm);

$perpage = optional_param('p', 50, PARAM_INT);
$filter = new stdClass();
if($annuality = optional_param('an', '', PARAM_ALPHANUMEXT)) {
    $filter->annuality = $annuality; 
}
if($source = optional_param('so', '', PARAM_ALPHANUMEXT)) {
    $filter->source = $source; 
}
if($videolibkey = optional_param('vi', '', PARAM_ALPHANUMEXT)) {
    $filter->videolibkey = $videolibkey; 
}

$params = array('id'=>$cm->id, 'a' => $action, 'p'=>$perpage);
foreach($filter as $field => $value) { 
    $params[substr($field,0,2)] = $value;
}

$baseurl = new moodle_url('/mod/videolib/manage.php', $params);
$PAGE->set_url($baseurl);
$PAGE->set_title(get_string('manage', 'videolib'));
$PAGE->set_heading(get_string('manage'.$action, 'videolib'));

$PAGE->navbar->add(get_string('manage', 'videolib'), new moodle_url($baseurl, array('a'=>'view')));
$PAGE->navbar->add(get_string($action, 'videolib'), $baseurl);

$params['a'] = 'view';
$returnurl = new moodle_url('/mod/videolib/manage.php', $params);

$data = null;
$capability = 'mod/videolib:manage';
switch($action) {
    case 'export' : $actionform = 'mod_videolib_export_form';
                    $capability = 'mod/videolib:download';
                    break;
                    
    case 'import' : $actionform = 'mod_videolib_import_form';
                    break;
                    
    case 'update' : if($item = optional_param('item', 0, PARAM_INT)) {
                        $data = $DB->get_record('videolib_source_mapping', array('id'=>$item));
                        $data->itemid = $data->id;
                        unset($data->id);
                    }
    case 'add'    : $actionform = 'mod_videolib_update_form';
                    $action = 'update';
                    break;
                    
    case 'del'    : $actionform = 'mod_videolib_delete_form';
                    if($item = optional_param('item', 0, PARAM_INT)) {
                        $data = $DB->get_record('videolib_source_mapping', array('id'=>$item));
                        $data->itemid = $data->id;
                        $data->confirm = 1;
                        $data->count = 1;
                        unset($data->id);
                    } elseif($confirm = optional_param('confirm', 0, PARAM_INT)) {
                        $data = data_submitted();
                        //die();
                        if($data->confirm && $data->confirmed == 0 && isset($data->source_confirmed)){ 
                            redirect($returnurl);
                        }
                        list($where, $params) = videolib_sql_search_mapping($data);
                        $data->count = $DB->count_records_select('videolib_source_mapping', $where, $params);
                    }
                    break;
                    
    default :       $actionform = 'mod_videolib_view_form';    
}

$context = context_module::instance($cm->id);
require_capability($capability, $context);

// to be used forward
$eventdata = array('context' => $context, 'objectid' => $videolib->id, 'other' => array('action' => $action));

$mform = new $actionform(null, array('cmid'=>$id, 'a'=>$action, 'item'=>$data));

if($data) {
    $mform->set_data($data);
}

// If a file has been uploaded, then process it
if ($mform->is_cancelled()) {
    redirect($returnurl);

} else if ($fromform = $mform->get_data()) {
    $message = '';

    
    
    //print_object($fromform);
    if($action == 'update') {
        if($success = videolib_saveupdate_mapping($fromform)) {
            $eventdata['other']['count'] = 1;
            $eventdata['other']['key'] = $fromform->videolibkey;
            $event = \mod_videolib\event\manage_updated::create($eventdata);
            $event->trigger();
        }
        redirect($returnurl);
    } elseif($action == 'del') {
        if($fromform->confirmed == 1) {
            if($count = videolib_delete_mapping($fromform)) {
                $eventdata['other']['count'] = $count;
                $eventdata['other']['key'] = $fromform->videolibkey;
                $event = \mod_videolib\event\manage_deleted::create($eventdata);
                $event->trigger();
            }
            redirect($returnurl);
        } 
    } elseif($action == 'export') {
        if(!$message = videolib_export_mapping($fromform)) {
            $event = \mod_videolib\event\manage_downloaded::create($eventdata);
            $event->trigger();
            die;
        } 
        
    } elseif($action == 'import') {    
            require_once($CFG->libdir.'/csvlib.class.php');     
            // Large files are likely to take their time and memory. Let PHP know
            // that we'll take longer, and that the process should be recycled soon
            // to free up memory.
            core_php_time_limit::raise();
            raise_memory_limit(MEMORY_EXTRA);

            $iid = csv_import_reader::get_new_iid('mod_videolib_import_mapping');
            $cir = new csv_import_reader($iid, 'mod_videolib_import_mapping');

            $filecontent = $mform->get_file_content('recordsfile');
            $readcount = $cir->load_csv_content($filecontent, $fromform->encoding, $fromform->separator);
            
            $message = '';
            if(!$columns = $cir->get_columns()) {
                $message = get_string('cannotreadtmpfile', 'error');
            } elseif(array_diff(array('source', 'annuality', 'videolibkey', 'remoteid'), $columns)) {
                $message = get_string('missingcsvcolumns', 'videolib');
            }
            
            if (empty($readcount) OR $message) {
                $message = $message ? $message.'<br /><br />' : '';
                //show meaningful error notice
                $line = strstr($filecontent, "\n", true);
                $line2 = '';
                if($p = strpos($filecontent, "\n", (strlen($line) + 2))) {
                    $line2 = substr($filecontent, strlen($line) + 1,  $p - strlen($line));
                }
                $line = $OUTPUT->box($line.'<br /><br />'.$line2.'<br />', 'csverror alert-error');
                unset($filecontent);
                
                notice($line.$cir->get_error(), $returnurl);
                
            } else {
                unset($filecontent);
                $count = videolib_import_mapping($cir, $fromform);
                $eventdata['other']['count'] = $count;
                $event = \mod_videolib\event\mapping_updated::create($eventdata);
                $event->trigger();
                redirect($returnurl);
            }
    }
    
    //redirect($returnurl);
}

// Trigger a report viewed event.
$event = \mod_videolib\event\manage_viewed::create($eventdata);
$event->trigger();


/// Print the form
$straction = get_string('manage'.$action, 'videolib');
echo $OUTPUT->header();
echo $OUTPUT->heading_with_help($straction, 'manage'.$action, 'videolib');

if($action == 'view') {
    $returnurl->param('a','add');
    echo html_writer::link($returnurl, get_string('addmappinglink', 'videolib'), array('class'=>'addmappinglink'));
    $returnurl->param('a','del');
    $returnurl->param('itemid',0);
    echo '&nbsp;    &nbsp;'.html_writer::link($returnurl, get_string('delmappinglink', 'videolib'), array('class'=>'addmappinglink'));
    
    
    
    videolib_get_mapping_table($id, $filter, $perpage);
   
    echo $OUTPUT->single_button(new moodle_url('/mod/videolib/view.php', array('id'=>$cm->id)), get_string('backtovideo', 'videolib')); 
}

$mform ->display();

echo $OUTPUT->footer();
