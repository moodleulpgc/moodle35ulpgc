<?php

/**
 * This file contains a block_supervision page
 *
 * @package   block_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once("../../config.php");
    require_once($CFG->dirroot."/blocks/supervision/locallib.php");
    require_once($CFG->dirroot."/blocks/supervision/editholidaysform.php");

    $cid = required_param('cid', PARAM_INT);
    $course = $DB->get_record('course', array('id' => $cid), '*', MUST_EXIST);
    $pagetype = required_param('type', PARAM_ALPHA);
    $itemid       = optional_param('item', 0, PARAM_INT);

    $baseparams = array('cid' => $cid,
                        'type' => $pagetype,
                        'item' => $itemid,
                        'page' => $pagetype
                        );

    $baseurl = new moodle_url('/blocks/supervision/editholidays.php', $baseparams);

    // Force user login in course (SITE or Course)
    if ($course->id == SITEID) {
        require_login();
    } else {
        require_login($course);
    }
    if ($course->id == SITEID) {
        $context = context_system::instance();
    } else {
        $context = context_course::instance($course->id);
    }

    require_capability('block/supervision:manage', $context);

    $PAGE->set_context($context);
    $PAGE->set_url('/blocks/supervision/holidays.php');
    $PAGE->set_pagelayout('standard');
    $title = get_string('insertholiday', 'block_supervision');
    $PAGE->set_title($title);
    $PAGE->set_heading($title);
    $PAGE->set_cacheable( true);

    $PAGE->navbar->add(get_string('management', 'block_supervision'), $baseurl);
    $PAGE->navbar->add(get_string('holidays', 'block_supervision'), null);

    $hid = optional_param('hid', 0, PARAM_INT);
    $delete   = optional_param('del', 0, PARAM_BOOL);
    $confirm = optional_param('confirm', 0, PARAM_BOOL);

    if($hid) {
        $date = $DB->get_record('supervision_holidays', array('id'=>$hid));
        $date->timeduration = $date->timeduration/DAYSECS;
    } else {
        $date = new stdClass();
        $date->name='';
        $date->duration =DAYSECS;
    }

    $returnurl = new moodle_url('/blocks/supervision/holidays.php', array('cid'=>$cid, 'type'=>$pagetype));

    if ($hid and $delete) {
        if (!$confirm) {
            $title = get_string('deleteholiday', 'block_supervision');
            $PAGE->set_title($title);
            $PAGE->set_heading($title);
            $PAGE->navbar->add($title, null);
            echo $OUTPUT->header();
            $optionsyes = array('cid'=>$cid, 'type'=>$pagetype, 'hid'=>$hid, 'del'=>1, 'sesskey'=>sesskey(), 'confirm'=>1);
            $optionsno  = array('cid'=>$cid, 'type'=>$pagetype, 'hid'=>0);
            $buttoncontinue = new moodle_url('editholidays.php', $optionsyes);
            $buttoncancel = new moodle_url('holidays.php', $optionsno);
            echo $OUTPUT->confirm(get_string('deleteholidayconfirm', 'block_supervision', $date->name), $buttoncontinue, $buttoncancel);
            echo $OUTPUT->footer();
            die;

        } else if (confirm_sesskey()){
            if ($DB->delete_records('supervision_holidays', array('id'=>$hid))) {
                redirect($returnurl, get_string('deletedholiday',  'block_supervision', $date->name));
            } else {
                print_error('erroreditholidays', 'block_supervision', $returnurl);
            }
        }
    }

    $form = new supervision_editholidays_form(null, array('cid'=>$cid,  'type'=>$pagetype, 'hid'=>$hid));
    $form->set_data($date);

    if ($form->is_cancelled()) {
        redirect($returnurl);

    } elseif ($formdata = $form->get_data()) {
        $formdata->scope = strtoupper($formdata->scope);
        if($formdata->timeduration < 1) {
            $formdata->timeduration = 1;
        }
        $formdata->timeduration *=  DAYSECS;

        if($formdata->datestart) {
            $message = '';
            $delay = 0;
            // hack to set correct id for holidays table
            $formdata->id = $formdata->hid;
            if (isset($formdata->id) && ($formdata->id>0)) {
                // id exists updating
                $DB->update_record('supervision_holidays', $formdata);
            } else {
                $DB->insert_record('supervision_holidays', $formdata);
            }
        } else {
            $message = get_string('errorolddate', 'block_supervision');
            $delay = 5;
        }
        redirect($returnurl, $message, $delay);
    }

    $PAGE->navbar->add($title, null);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('insertholiday', 'block_supervision'));

    $form->display();

    echo $OUTPUT->footer();


