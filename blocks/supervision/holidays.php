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

    $cid = required_param('cid', PARAM_INT);
    $course = $DB->get_record('course', array('id' => $cid), '*', MUST_EXIST);
    $pagetype = required_param('type', PARAM_ALPHA);
    $itemid       = optional_param('item', 0, PARAM_INT);

    $baseparams = array('cid' => $cid,
                        'type' => $pagetype,
                        'item' => $itemid,
                        'page' => $pagetype
                        );

    $baseurl = new moodle_url('/blocks/supervision/holidays.php', $baseparams);

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
    $title = get_string('editholidays', 'block_supervision');
    $PAGE->set_title($title);
    $PAGE->set_heading($title);
    $PAGE->set_cacheable( true);
    $PAGE->navbar->add(get_string('management', 'block_supervision'), $baseurl);
    $PAGE->navbar->add($title, null);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('holidaystable', 'block_supervision'));

    $config = get_config('block_supervision');
    $time = strtotime($config->startdisplay);
    $holidays = $DB->get_records_select('supervision_holidays', ' datestart >= :time', array('time'=>$time), ' datestart ASC' );

    echo '<div class="singlebutton forumaddnew">';
    $url = new moodle_url('/blocks/supervision/editholidays.php', array('cid'=>$cid, 'type'=>$pagetype));
    echo $OUTPUT->single_button($url, get_string('insertholiday', 'block_supervision'), 'post');
    echo '</div>';
    print '<br />';
    if($holidays) {
        $table = new html_table();
        $table->width = "80%";
        $table->head = array(get_string('name'), get_string('date'), get_string('duration', 'block_supervision'), get_string('type', 'block_supervision'), get_string('action')  );
        $table->align = array('left', 'left', 'left', 'center', 'center');
        //$table->size = array ("15%", "*", "35%", "*", "*", "*", "*");

        $stredit = get_string('edit');
        $strdelete = get_string('delete');

        foreach($holidays as $vacation) {
            $row = array();
            $row[] = $vacation->name;
            $row[] = userdate($vacation->datestart);
            $row[] = format_time($vacation->timeduration);
            $row[] = $vacation->scope;
            $rurl = new moodle_url($url);
            $rurl->param('hid', $vacation->id);
            $icons = html_writer::link($rurl, html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'), 'alt'=>$stredit, 'class'=>'iconsmall')), array('title'=>$stredit));
            $rurl->param('del', 1);
            $row[] = $icons.html_writer::link($rurl, html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'), 'alt'=>$strdelete, 'class'=>'iconsmall')), array('title'=>$strdelete));
            $table->data[] = $row;
        }
       echo html_writer::table($table);
    } else {
    }
    echo $OUTPUT->footer();
