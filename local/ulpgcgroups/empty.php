<?php
/**
 * Delete group
 *
 * @copyright &copy; 2008 The Open University
 * @author s.marshall AT open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package groups
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/group/lib.php');

// Get and check parameters
$courseid = required_param('courseid', PARAM_INT);
$groupid = required_param('group', PARAM_INT);
$groupingid  = optional_param('grouping', 0, PARAM_INT); 
$userorder = optional_param('order', 1, PARAM_INT); 
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$PAGE->set_url('/group/empty.php', array('courseid'=>$courseid,'group'=>$groupid, 'order'=>$userorder, 'grouping'=>$groupingid));

// Make sure course is OK and user has access to manage groups
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}
require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/course:managegroups', $context);

// Make sure group is OK and belong to course

if (!$group = $DB->get_record('groups', array('id' => $groupid))) {
    print_error('invalidgroupid');
}
if ($courseid != $group->courseid) {
    print_error('groupunknown', '', '', $group->courseid);
}
$groupname = format_string($group->name);

$returnurl = new moodle_url('/group/index.php', array('id'=>$course->id, 'group'=>$groupid, 'order'=>$userorder, 'grouping'=>$groupingid));
//$returnurl='index.php?id='.$course->id.'&amp;group='.$groupid;

if ($confirm && data_submitted()) {
    if (!confirm_sesskey() ) {
        print_error('confirmsesskeybad','error',$returnurl);
    }
    $msg = '';
    if($users = groups_get_members($groupid, 'u.id, u.username')) {
        foreach($users as $guser) {
            if (groups_remove_member_allowed($groupid, $guser)) {
                if(!groups_remove_member($groupid, $guser)) {
                    print_error('erroraddremoveuser', 'local_ulpgcgroups', $returnurl);
                }
            } else {
                $msg.= $OUTPUT->notification(get_string('deletenotallowed', 'local_ulpgcgroups'));
            }
        }
    }
    redirect($returnurl, $msg);
} else {
    $PAGE->set_title(get_string('emptygroup', 'local_ulpgcgroups'));
    navigation_node::override_active_url(new moodle_url('/group/index.php', array('id'=>$courseid)));
    $PAGE->navbar->add(get_string('emptygroup', 'local_ulpgcgroups'));
    $PAGE->set_heading($course->fullname . ': '. get_string('emptygroup', 'local_ulpgcgroups'));
    echo $OUTPUT->header();
    $optionsyes = array('courseid'=>$courseid, 'group'=>$groupid, 'order'=>$userorder, 'grouping'=>$groupingid, 'sesskey'=>sesskey(), 'confirm'=>1);
    $optionsno = array('id'=>$courseid, 'group'=>$groupid, 'order'=>$userorder, 'grouping'=>$groupingid );
    $message=get_string('emptygroupconfirm', 'local_ulpgcgroups', $groupname);
    $formcontinue = new single_button(new moodle_url('/local/ulpgcgroups/empty.php', $optionsyes), get_string('yes'), 'post');
    $formcancel = new single_button(new moodle_url('/group/index.php', $optionsno), get_string('no'), 'get');
    echo $OUTPUT->confirm($message, $formcontinue, $formcancel);
    echo $OUTPUT->footer();
}
