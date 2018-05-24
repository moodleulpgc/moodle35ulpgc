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
require_once($CFG->dirroot.'/mod/tracker/locallib.php');
require_once($CFG->dirroot.'/repository/lib.php');


class tracker_managewords_form extends moodleform {
    function definition() {
        $mform =& $this->_form;
        $cm = $this->_customdata['cm'];

        $mform->addElement('header', 'managewords', get_string('managewords', 'tracker'));

        $lang = $mform->addElement('select', 'forcedlang', get_string('forcedlang', 'tracker'), get_string_manager()->get_list_of_translations());
        $lang->setSelected(current_language());
        
        $mform->addElement('static', 'issueword_explain', '', get_string('issueword_explain', 'tracker'));
        $word = get_string('issueid', 'tracker');
        $mform->addElement('text', 'issueword', get_string('wordfor', 'tracker', $word), array('size'=>'40'));
        $mform->addHelpButton('issueword', 'issueword', 'tracker');
        $mform->setType('issueword', PARAM_TEXT);

        $word = get_string('assignedto', 'tracker');
        $mform->addElement('text', 'assignedtoword', get_string('wordfor', 'tracker', $word), array('size'=>'20'));
        $mform->setType('assignedtoword', PARAM_TEXT);

        $word = get_string('summary', 'tracker');
        $mform->addElement('text', 'summaryword', get_string('wordfor', 'tracker', $word), array('size'=>'20'));
        $mform->setType('summaryword', PARAM_TEXT);

        $word = get_string('description', 'tracker');
        $mform->addElement('text', 'descriptionword', get_string('wordfor', 'tracker', $word), array('size'=>'20'));
        $mform->setType('descriptionword', PARAM_TEXT);

        $mform->addElement('static', 'statuswords_explain', '', get_string('statuswords_explain', 'tracker'));
        $mform->addElement('text', 'statuswords', get_string('statuswords', 'tracker'), array('size'=>'80'));
        $mform->addHelpButton('statuswords', 'statuswords', 'tracker');
        $mform->setType('statuswords', PARAM_TEXT);



        $mform->addElement('hidden', 'id', $cm->id);
                $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'view', 'admin');
                $mform->setType('view', PARAM_ALPHA);
        $mform->addElement('hidden', 'screen', 'managewords');
        $mform->setType('screen', PARAM_ALPHA);

        $this->add_action_buttons(true, get_string('savechanges'));

    }
}

/////////////////////////////////////////////////////////////////////////////////////////////////

$id = optional_param('id', 0, PARAM_INT); // Course Module ID

if(!$cm) {
    if ($id) {
        if (! $cm = get_coursemodule_from_id('tracker', $id)) {

        }
        if (! $tracker = $DB->get_record('tracker', array('id' => $cm->instance))) {
            print_error('errormoduleincorrect', 'tracker');
        }
    } else {
        print_error('errorcoursemodid', 'tracker');
    }
}

$returnurl = new moodle_url('/mod/tracker/view.php', array('id'=>$cm->id, 'view'=>'admin', 'screen'=>'summary'));
$baseurl = new moodle_url('/mod/tracker/view.php', array('id'=>$cm->id, 'view'=>'admin', 'screen'=>'managewords'));

$browser = get_file_browser();

$mform = new tracker_managewords_form(null, array('cm'=>$cm));
if($translation = $DB->get_record('tracker_translation', array('trackerid'=>$tracker->id))) {
    $tid = $translation->id;
    $translation->id = $cm->id;
    $mform->set_data($translation);
    $translation->id = $tid;
}

if ($mform->is_cancelled()) {
    redirect($returnurl);
}

if($data = $mform->get_data()) {
    // process form
    // check enough developers/issues
    if($translation) { // = $DB->get_record('tracker_translate', array('trackerid'=>$tracker->id)) {
        $translation->issueword = $data->issueword;
        $translation->assignedtoword = $data->assignedtoword;
        $translation->summaryword = $data->summaryword;
        $translation->descriptionword = $data->descriptionword;
        $translation->statuswords = $data->statuswords;
        $translation->forcedlang = $data->forcedlang;
        $success = $DB->update_record('tracker_translation', $translation);
    } else {
        $translation = new stdclass;
        $translation->trackerid = $tracker->id;
        $translation->issueword = $data->issueword;
        $translation->assignedtoword = $data->assignedtoword;
        $translation->summaryword = $data->summaryword;
        $translation->descriptionword = $data->descriptionword;
        $translation->statuswords = $data->statuswords;
        $translation->forcedlang = $data->forcedlang;
        $success = $DB->insert_record('tracker_translation', $translation);
    }

    if($success) {
        $n = new \core\output\notification(get_string('changessaved'), \core\output\notification::NOTIFY_INFO);
        echo $OUTPUT->render($n);
    }
}

if($translation) {
    if($translation->statuswords) {
        $translation->statuswords = explode(',', $translation->statuswords);
    }
    $SESSION->tracker_current_translation[$tracker->id] = $translation;
    $SESSION->tracker_current_id = $tracker->id;
}

echo $OUTPUT->container_start();
$mform->display();
echo $OUTPUT->container_end();

echo '<br />';

