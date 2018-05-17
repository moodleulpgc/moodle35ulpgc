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

$action = optional_param('do', '', PARAM_ALPHA);
if(!$action) {
    redirect($returnurl);
}

$baseurl = new moodle_url('/mod/assign/feedback/historic/updatedata.php', array('do'=>$action));
$returnurl  = new moodle_url('/admin/settings.php', array('section'=>'assignfeedback_historic'));

$perpage  = optional_param('perpage', 100, PARAM_INT);

$strplugin = get_string('pluginname', 'assignfeedback_historic');


$strtitle = get_string($action.'link', 'assignfeedback_historic');

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

$confirm = optional_param('confirm', 0, PARAM_INT);


$formparams = array('cm'=>'',
                    'context'=>$context);

if($action == 'upload') {
    $mform = new assignfeedback_historic_import_form(null, array('assignment'=>'',
                                                                    'params'=>$formparams));
} elseif($action == 'update') {
    $mform = new assignfeedback_historic_update_form(null);
} else {
   redirect($returnurl);
}

if ($mform->is_cancelled()) {
    redirect($returnurl);
    return;
} elseif(($data = $mform->get_data()) && ($action == 'upload') && ($csvdata = $mform->get_file_content('uploadfile'))) {
    /// get confirmation
    $importid = csv_import_reader::get_new_iid('assignfeedback_historic_'.$formparams['cm']);
    // File exists and was valid.
    $mform = new assignfeedback_historic_import_confirm_form(null, array('assignment'=>'',
                                                                    'params'=>$formparams,
                                                                    'importid' => $importid,
                                                                    'draftid' => $data->uploadfile,
                                                                    'override'=> !empty($data->override),
                                                                    'encoding' => $data->encoding,
                                                                    'delimiter' => $data->delimiter ));
} elseif($confirm && confirm_sesskey()) {
    //process form and redirect
    if ($mform->is_cancelled()) {
        redirect($baseurl);
        return;
    }
    $data = data_submitted();
    if (isset($data->cancel) && $data->cancel) {
        redirect($baseurl);
    }

    if($action == 'upload') {
        $message = import_update_historic($data, $baseurl);
    } elseif($action == 'update') {
        $message = update_historic_from_db();
    } else {
        redirect($returnurl);
    }
    redirect($returnurl, $message, -1);

}

echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);
$mform->display();
echo $OUTPUT->footer();