<?php
require_once('../../../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/message/lib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/advuserbulk/lib.php');
require_once('user_prefs_form.php');

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
        list($in, $params) = $DB->get_in_or_equal($SESSION->bulk_users);
        if($formdata->display_prefs) {
            $drawer = null;
            $blocks = null;
            if($formdata->display_prefs > 0) {
                $drawer = get_user_preferences('drawer-open-nav', null, $formdata->display_prefs);
                $blocks = get_user_preferences('sidepre-open', null, $formdata->display_prefs); 
            } elseif($formdata->display_prefs == -1) {
                $drawer = 0;
                $blocks = 0;
            } elseif($formdata->display_prefs == -2) {
                $drawer = 1;
                $blocks = 1;
            } elseif($formdata->display_prefs == -3) {
                if ($rs = $DB->get_recordset_select('user', "id $in", $params, '', 'id, username, idnumber')) {
                    foreach ($rs as $user) {
                        unset_user_preference('drawer-open-nav', $user->id);
                        unset_user_preference('sidepre-open', $user->id);
                    }
                    $rs->close();
                }
            }
            
            $prefs = array();
            if($drawer !== null) {
                $prefs['drawer-open-nav'] = $drawer;
            }
            if($blocks !== null) {
                $prefs['sidepre-open'] = $blocks;
            }
            if($prefs) {
                if ($rs = $DB->get_recordset_select('user', "id $in", $params, '', 'id, username, idnumber')) {
                    foreach ($rs as $user) {
                        set_user_preferences($prefs, $user->id);
                    }
                    $rs->close();
                }
            }
        
        }
        redirect($return);
    }
    $mform->freeze_all();
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
