<?php
/**
* script for bulk user delete operations
*/

require_once('../../../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/advuserbulk/lib.php');

$confirm = optional_param('confirm', 0, PARAM_BOOL);

admin_externalpage_setup('tooladvuserbulk');
check_action_capabilities('delete', true);

$return = $CFG->wwwroot.'/'.$CFG->admin.'/tool/advuserbulk/user_bulk.php';

if (empty($SESSION->bulk_users)) {
    redirect($return);
}

//TODO: add support for large number of users

if ($confirm and confirm_sesskey()) {
    foreach(array_chunk($SESSION->bulk_users, 1000) as $chunk) {
        list($in, $params) = $DB->get_in_or_equal($chunk);
        $DB->set_field_select('user', 'suspended', 1, "id $in AND deleted = 0 AND id NOT IN ({$CFG->siteadmins})" , $params); 
    }
    session_gc(); // remove stale sessions
    redirect($return, get_string('changessaved'));
} else {
    echo $OUTPUT->header();
    
    $numusers = count($SESSION->bulk_users);
    if($numusers <= 25) {
        list($in, $params) = $DB->get_in_or_equal($SESSION->bulk_users);
        $userlist = $DB->get_records_select_menu('user', "id $in", $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
        $usernames = get_string('users').html_writer::alist($userlist);
    } else {
        $usernames = advuserbulk_get_string('users', 'bulkuseractions_suspend');
    }
    echo $OUTPUT->heading(get_string('confirmation', 'admin'));
    $formcontinue = new single_button(new moodle_url('index.php', array('confirm' => 1)), get_string('yes'));
    $formcancel = new single_button(new moodle_url($return), get_string('no'), 'get');
    echo $OUTPUT->confirm(advuserbulk_get_string('suspendcheck', 'bulkuseractions_suspend', $usernames), $formcontinue, $formcancel);

    echo $OUTPUT->footer();
}
?>
