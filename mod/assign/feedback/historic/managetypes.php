<?php
/**
 * Manage site wide historic data types
 *
 * @package   assignfeedback_historic
 * @copyright 2014 Enrique Castro, ecastro  @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../../config.php');
//require_once($CFG->libdir.'/adminlib.php');
//require_once($CFG->libdir.'/formslib.php');
//require_once($CFG->libdir.'/gradelib.php');
//require_once($CFG->libdir.'/plagiarismlib.php');
/** Include locallib.php */
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/assign/feedback/historic/historicforms.php');
//require_once($CFG->dirroot.'/mod/assign/feedback/historic/locallib.php');
//require_once('selectionforms.php');




///////////////////////////////////////////////////////////////////////////////////


require_login();

$context = context_system::instance();

require_capability('assignfeedback/historic:manage', $context);

if (!$site = get_site()) {
    print_error("Could not find site-level course");
}

if (!$adminuser = get_admin()) {
    print_error("Could not find site admin");
}

$baseurl = new moodle_url('/mod/assign/feedback/historic/managetypes.php');
$returnurl  = new moodle_url('/admin/settings.php', array('section'=>'assignfeedback_historic'));

$perpage  = optional_param('perpage', 100, PARAM_INT);

$strplugin = get_string('pluginname', 'assignfeedback_historic');
$strtitle = get_string('managedatatypes', 'assignfeedback_historic');

$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_title($strtitle);
$PAGE->set_heading($strplugin);

$PAGE->set_pagelayout('admin');
$PAGE->navbar->add(get_string('administrationsite'));
$PAGE->navbar->add(get_string('plugins', 'admin'));
$PAGE->navbar->add(get_string('assignmentplugins', 'assign'));
$PAGE->navbar->add(get_string('feedbackplugins', 'assign'));
$PAGE->navbar->add($strplugin, $returnurl);
$PAGE->navbar->add($strtitle);

//////////////////////////////////////////////////////////////////////////////
// process form actions

$itemid   = optional_param('item', 0, PARAM_INT); // add/update items
$delete   = optional_param('del', 0, PARAM_INT); // delete items

if($itemid) {
    $mform = new assignfeedback_historic_datatype_form(null, array('item'=>$itemid));
    $element = false;
    if($itemid > 0) {
        // we are updating an existing element
        if($element = $DB->get_record('assignfeedback_historic_type', array('id' => $itemid))) {
            $mform->set_data($element);
        }
        $heading = get_string('datatypeupdate', 'assignfeedback_historic');
    } else {
        $heading = get_string('datatypeadd', 'assignfeedback_historic');
    }

    if ($mform->is_cancelled()) {
        $itemid = 0;
    } elseif ($formdata = $mform->get_data()) {
        /// process form & store element in database
        if($element) { // this means itemid > 0 and record exists, over-write & update
            $data->id = $element->id;
            $element->type = clean_param($formdata->type, PARAM_ALPHANUMEXT);
            $element->name = $formdata->name;
            if($success = $DB->update_record('assignfeedback_historic_type', $element)) {
                //add_to_log($course->id, 'examregistrar', 'update '.$itemname, "manage.php?id={$cm->id}&edit=$edit", $data->display, $cm->id);
            }

        } else {
            ////print_object('UPDATE item');
            if($formdata->id = $DB->insert_record('assignfeedback_historic_type', $formdata)) {
                //add_to_log($course->id, 'examregistrar', 'add '.$itemname, "manage.php?id={$cm->id}&edit=$edit", $data->display, $cm->id);
            }
        }
        $itemid = 0;
        //redirect($baseurl, get_string('changessaved'), $delay);
    }
    if($itemid) {
        echo $OUTPUT->header();
        echo $OUTPUT->heading($heading);
        $mform->display();
        echo $OUTPUT->footer();
        die;
    }
}

if($delete) {
    $delete = $DB->get_record('assignfeedback_historic_type', array('id'=>$delete));
    if($delete) {
        $confirm = optional_param('confirm', 0, PARAM_BOOL);
        if(!$confirm) {
            $PAGE->navbar->add(get_string('delete'));
            $confirmurl = new moodle_url($baseurl, array('del' => $delete->id, 'confirm' => 1));
            $message = get_string('delete_confirm', 'assignfeedback_historic', $delete->name);
            echo $OUTPUT->header();
            echo $OUTPUT->confirm($message, $confirmurl, $baseurl);
            echo $OUTPUT->footer();
            die;
        } else if(confirm_sesskey()){
            /// confirmed, proceed with deletion
            if ($DB->delete_records('assignfeedback_historic_type', array('id'=>$delete->id))) {
                //add_to_log($course->id, 'examregistrar', 'delete '.$itemname, "manage.php?id={$cm->id}&edit=$edit", $delete->name, $cm->id);
                //redirect($baseurl, get_string('changessaved'), $delay);
                $delete = 0;
            }
        }
    }
}






//////////////////////////////////////////////////////////////////////////////

//add_to_log($course->id, 'examregistrar', "manage edit $edit", "manage.php?id={$cm->id}&edit=$edit", $examregistrar->name, $cm->id);

/// Print the header
echo $OUTPUT->header();
echo $OUTPUT->heading_with_help($strtitle, 'managedatatypes', 'assignfeedback_historic');

    $url = new moodle_url($baseurl, array('item'=>-1));
    echo $OUTPUT->heading(html_writer::link($url, get_string('datatypeadd', 'assignfeedback_historic')));

$tableid = 'assignfeedback_historic-manage-types_'.html_writer::random_id();
$table = new flexible_table($tableid);
$tablecolumns = array('checkbox', 'type', 'name', 'action');
$tableheaders = array('&nbsp;',
                        get_string('datatype', 'assignfeedback_historic'),
                        get_string('name'),
                        get_string('action'),
                        );
$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl($baseurl->out(false));
//$table->set_wrapformurl($baseurl);

$table->sortable(true, 'name', SORT_ASC);
$table->no_sorting('checkbox');
$table->no_sorting('action');

$table->set_attribute('id', $tableid);
$table->set_attribute('cellspacing', '0');
$table->set_attribute('class', 'flexible generaltable historicdatatypetable ');

$table->setup();

$totalcount = $DB->count_records('assignfeedback_historic_type');

$table->initialbars(false);
$table->pagesize($perpage, $totalcount);

if ($table->get_sql_sort()) {
    $sort = $table->get_sql_sort();
} else {
    $sort = '';
}

$stredit   = get_string('edit');
$strdelete = get_string('delete');

if($datatypes = $DB->get_records('assignfeedback_historic_type', null)) {
    foreach($datatypes as $datatype) {
        $data = array();
        $data[] = '';
        $data[] = $datatype->type;
        $data[] = $datatype->name;
        $action = '';
        $buttons = array();
        $url = new moodle_url($baseurl, array('item'=>$datatype->id));
        $buttons[] = html_writer::link($url, html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'), 'alt'=>$stredit, 'class'=>'iconsmall')), array('title'=>$stredit));
        $url = new moodle_url($baseurl, array('del'=>$datatype->id));
        $buttons[] = html_writer::link($url, html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'), 'alt'=>$strdelete, 'class'=>'iconsmall')), array('title'=>$strdelete));
        $action = implode('&nbsp;&nbsp;', $buttons);
        $data[] = $action;
        $table->add_data($data);
    }
    $table->finish_output();
} else {
    echo $OUTPUT->heading(get_string('nothingtodisplay'));
}

echo $OUTPUT->single_button($returnurl, get_string('back'));

echo $OUTPUT->footer();
