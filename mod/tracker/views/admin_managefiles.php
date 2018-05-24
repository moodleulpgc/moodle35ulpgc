<?PHP

/**
* A file manager for Tracker
* @package mod-tracker
* @category mod
* @author Enrique Castro
*
*/

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from view.php in mod/tracker
}

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/repository/lib.php');

class tracker_files_edit_form extends moodleform {
    function definition() {
        $mform =& $this->_form;
        $contextid = $this->_customdata['contextid'];
        list($context, $course, $cm) = get_context_info_array($contextid);
        $options = array('subdirs'=>1, 'maxfiles'=>-1, 'accepted_types'=>'*', 'return_types'=>FILE_INTERNAL | FILE_REFERENCE);
        $mform->addElement('filemanager', 'files_filemanager', tracker_getstring('files'), null, $options);
        $mform->addElement('hidden', 'contextid', $this->_customdata['contextid']);
        $mform->setType('contextid', PARAM_INT);
        $mform->addElement('hidden', 'currentcontext', $context->id);
        $mform->setType('currentcontext', PARAM_INT);
        $mform->addElement('hidden', 'filearea', $this->_customdata['filearea']);
                $mform->setType('filearea', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'component', $this->_customdata['component']);
                $mform->setType('component', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'returnurl', $this->_customdata['returnurl']);
                $mform->setType('returnurl', PARAM_URL);
        $mform->addElement('hidden', 'id', $cm->id);
                $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'view', 'admin');
                $mform->setType('view', PARAM_ALPHA);
        $mform->addElement('hidden', 'screen', 'managefiles');
        $mform->setType('screen', PARAM_ALPHA);

        $this->add_action_buttons(true, tracker_getstring('savechanges'));
        $this->set_data($this->_customdata['data']);
    }
}


$returnurl = new moodle_url('/mod/tracker/view.php', array('id'=>$cm->id, 'view'=>'admin', 'screen'=>'managefiles'));

$filecontext = context::instance_by_id($context->id);
$component = 'mod_tracker';
$filearea = 'bulk_useractions';


$browser = get_file_browser();

$data = new stdClass();
$options = array('subdirs'=>1, 'maxfiles'=>-1, 'accepted_types'=>'*', 'return_types'=>FILE_INTERNAL);
file_prepare_standard_filemanager($data, 'files', $options, $filecontext, $component, $filearea, 0);
$form = new tracker_files_edit_form(null, array('data'=>$data, 'contextid'=>$context->id, 'currentcontext'=>$context->id,
                                        'filearea'=>$filearea, 'component'=>$component, 'returnurl'=>$returnurl));

if ($form->is_cancelled()) {
    redirect($returnurl);
}

$data = $form->get_data();
if ($data) {
    $formdata = file_postupdate_standard_filemanager($data, 'files', $options, $filecontext, $component, $filearea, 0);
    redirect($returnurl);
}

echo $OUTPUT->container_start();
$form->display();
echo $OUTPUT->container_end();

echo '<br />';
echo '<br />';
