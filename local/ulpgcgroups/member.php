<?php
/**
 * delete  OR edit group member.
 *
 * @copyright &copy; 2010 ULPGC
 * @author E. Castro AT dbbf.ulpgc.es
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package groups
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/group/lib.php');

/// get url variables
$courseid = optional_param('courseid', 0, PARAM_INT);
$id       = optional_param('id', 0, PARAM_INT);
$userid   = optional_param('user', 0, PARAM_INT);
$groupingid  = optional_param('grouping', 0, PARAM_INT); 
$userorder = optional_param('order', 1, PARAM_INT); 

$confirm  = optional_param('confirm', 0, PARAM_BOOL);

if ($id) {
    if (!$group = $DB->get_record('groups', array('id'=>$id))) {
        print_error('invalidgroupid');
    }
    if (empty($courseid)) {
        $courseid = $group->courseid;

    } else if ($courseid != $group->courseid) {
        print_error('invalidcourseid');
    }

    if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
        print_error('invalidcourseid');
    }

} else {
    print_error('invalidgroupid');
}

$PAGE->set_url('/group/member.php', array('id'=>$id, 'group'=>$id, 'order'=>$userorder, 'grouping'=>$groupingid));

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/course:managegroups', $context);

$returnurl = new moodle_url('/group/index.php', array('id'=>$courseid, 'group'=>$id, 'order'=>$userorder, 'grouping'=>$groupingid));

if($userid) {
    if (!$user = $DB->get_record('user', array('id' => $userid))) {
        error('USER ID was incorrect');
    }
} else {
    print_error('withoutuserdata', '', $returnurl);
}


if(!$confirm) {
    $PAGE->set_title(get_string('removeuser', 'local_ulpgcgroups'));
    $PAGE->set_heading($course->fullname . ': '. get_string('removefromgroup', 'core_group', $group->name) );
    navigation_node::override_active_url(new moodle_url('/group/index.php', array('id'=>$courseid)));
    $PAGE->navbar->add(get_string('emptygroup', 'local_ulpgcgroups'));
    if (groups_remove_member_allowed($id, $user->id)) {
        echo $OUTPUT->header();
        $optionsyes = array('id'=>$id, 'delete'=>1, 'courseid'=>$courseid, 'user'=>$userid, 'order'=>$userorder, 'grouping'=>$groupingid, 'sesskey'=>sesskey(), 'confirm'=>1);
        $optionsno  = array('id'=>$courseid, 'order'=>$userorder, 'grouping'=>$groupingid);
        $formcontinue = new single_button(new moodle_url('/local/ulpgcgroups/member.php', $optionsyes), get_string('yes'), 'get');
        $formcancel = new single_button(new moodle_url('/group/index.php', $optionsno), get_string('no'), 'get');
        $info = new stdclass;
        $info->user = fullname($user);
        $info->group = $group->name;
        echo $OUTPUT->confirm(get_string('removefromgroupconfirm', 'core_group', $info), $formcontinue, $formcancel);
        echo $OUTPUT->footer($course);
        die;
    } else {
        redirect($returnurl, $OUTPUT->notification(get_string('deletenotallowed', 'local_ulpgcgroups')));
    }
} else if (confirm_sesskey()){
    $PAGE->set_title(get_string('removeuser', 'local_ulpgcgroups'));
    $PAGE->set_heading($course->fullname . ': '. get_string('removefromgroup', 'core_group', $group->name) );
    $msg = '';
    if (groups_remove_member_allowed($id, $user->id)) {
        if (!groups_remove_member($id, $user->id)) {
            print_error('erroraddremoveuser', 'core_group', $returnurl);
        }
    } else {
        $msg = $OUTPUT->notification(get_string('deletenotallowed', 'local_ulpgcgroups'));
    }
    redirect($returnurl, $msg);
}

echo $OUTPUT->footer($course);
