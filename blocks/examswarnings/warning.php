<?php
/**
 * This file presents a page with warnings about next exams dates for user
 *
 * @package   block_examswarnings
 * @copyright 2013 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once("../../config.php");
    require_once($CFG->dirroot."/blocks/examswarnings/locallib.php");

    $cid = required_param('cid', PARAM_INT);
    $course = $DB->get_record('course', array('id' => $cid), '*', MUST_EXIST);
    $type = required_param('type', PARAM_ALPHA);
    $userid = required_param('user', PARAM_INT);

    // Force user login in course (SITE or Course)
    if ($course->id == SITEID)
        require_login();
    else
        require_login($course);

    if ($course->id == SITEID)
        $context = get_context_instance(CONTEXT_SYSTEM);
    else
        $context = get_context_instance(CONTEXT_COURSE, $course->id);

    require_capability('block/examswarnings:select', $context);

    if($USER->id != $userid ) {
        throw new required_capability_exception($context, 'block/examswarnings:view', 'nopermissions', '');
    }

    $PAGE->set_context($context);
    $PAGE->set_url('/blocks/examswarnings/warning.php', array('cid'=>$cid, 'user'=>$userid, 'type'=>$type));
    $PAGE->set_pagelayout('standard');
    $title = get_string('warning', 'block_examswarnings');
    $PAGE->set_title($title);
    $PAGE->set_heading($title);
    $PAGE->set_cacheable( true);
    $PAGE->navbar->add(get_string('warning'.$type, 'block_examswarnings'), null);
    $PAGE->navbar->add($title, null);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('warnings', 'block_examswarnings'));

    $config = get_config('block_examswarnings');


    $returnurl = new moodle_url('/course/view.php', array('id'=>$course->id));
    echo $OUTPUT->continue_button($returnurl);

    echo $OUTPUT->footer();

