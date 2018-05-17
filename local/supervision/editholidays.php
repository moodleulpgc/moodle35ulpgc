<?php

/**
 * This file contains a local_supervision page
 *
 * @package   local_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once("../../config.php");
    require_once($CFG->dirroot."/local/supervision/locallib.php");
    require_once($CFG->dirroot."/local/supervision/editholidaysform.php");
    require_once($CFG->libdir.'/adminlib.php');

    $itemid       = optional_param('item', 0, PARAM_INT);
    $baseparams = array('item' => $itemid);

    $baseurl = new moodle_url('/local/supervision/editholidays.php', $baseparams);

    require_login();
    $context = context_system::instance();
   
    admin_externalpage_setup('local_supervision_holidays');
    $PAGE->set_context($context);
    $PAGE->set_url('/local/supervision/holidays.php');
    $PAGE->set_pagelayout('admin');
    $title = get_string('insertholiday', 'local_supervision');
    $PAGE->set_title($title);
    $PAGE->set_heading($title);
    $PAGE->set_cacheable( true);
    require_capability('local/supervision:manage', $context);
    
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

    $returnurl = new moodle_url('/local/supervision/holidays.php', array());

    if ($hid and $delete) {
        if (!$confirm) {
            $title = get_string('deleteholiday', 'local_supervision');
            $PAGE->set_title($title);
            $PAGE->set_heading($title);
            $PAGE->navbar->add($title, null);
            echo $OUTPUT->header();
            $optionsyes = array('hid'=>$hid, 'del'=>1, 'sesskey'=>sesskey(), 'confirm'=>1);
            $optionsno  = array('hid'=>0);
            $buttoncontinue = new moodle_url('editholidays.php', $optionsyes);
            $buttoncancel = new moodle_url('holidays.php', $optionsno);
            echo $OUTPUT->confirm(get_string('deleteholidayconfirm', 'local_supervision', $date->name), $buttoncontinue, $buttoncancel);
            echo $OUTPUT->footer();
            die;

        } else if (confirm_sesskey()){
            if ($DB->delete_records('supervision_holidays', array('id'=>$hid))) {
                redirect($returnurl, get_string('deletedholiday',  'local_supervision', $date->name));
            } else {
                print_error('erroreditholidays', 'local_supervision', $returnurl);
            }
        }
    }

    $form = new supervision_editholidays_form(null, array('hid'=>$hid));
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
            $message = get_string('errorolddate', 'local_supervision');
            $delay = 5;
        }
        redirect($returnurl, $message, $delay);
    }

    $PAGE->navbar->add($title, null);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('insertholiday', 'local_supervision'));

    $form->display();

    echo $OUTPUT->footer();


