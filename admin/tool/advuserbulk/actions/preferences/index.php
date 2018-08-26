<?php
require_once('../../../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/message/lib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/advuserbulk/lib.php');
require_once('user_prefs_form.php');
require_once('locallib.php');

$confirm = optional_param('confirm', 0, PARAM_INT);

$return = $CFG->wwwroot.'/'.$CFG->admin.'/tool/advuserbulk/user_bulk.php';


if (empty($SESSION->bulk_users)) {
    redirect($return);
}

check_action_capabilities('preferences', true);
admin_externalpage_setup('tooladvuserbulk');


$mform = new advuserbulk_user_preferences_form('index.php');


if ($mform->is_cancelled()) {
    redirect($return);
} else if ($formdata = $mform->get_data()) {

    if($confirm) { 
        // process form 
        foreach(array('display_prefs', 'forum_prefs', 'htmleditor_prefs', 'messages_prefs') as $pref) {
            $actionfunction = 'advuserbulk_preferences_'.$pref;
            $actionfunction($formdata->$pref);
        }
        redirect($return);
    }
    $mform->freeze_all();
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
