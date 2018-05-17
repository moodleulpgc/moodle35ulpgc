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
    require_once($CFG->libdir.'/adminlib.php');

    $itemid       = optional_param('item', 0, PARAM_INT);

    $baseparams = array('item' => $itemid);

    $baseurl = new moodle_url('/local/supervision/holidays.php', $baseparams);

    require_login();
    $context = context_system::instance();
   
    admin_externalpage_setup('local_supervision_holidays');
    $PAGE->set_context($context);
    $PAGE->set_url('/local/supervision/holidays.php');
    $PAGE->set_pagelayout('admin');
    $title = get_string('editholidays', 'local_supervision');
    $PAGE->set_title($title);
    $PAGE->set_heading($title);
    $PAGE->set_cacheable( true);

    require_capability('local/supervision:manage', $context);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('holidaystable', 'local_supervision'));

    $config = get_config('block_supervision');
    $time = strtotime($config->startdisplay);
    $holidays = $DB->get_records_select('supervision_holidays', ' datestart >= :time', array('time'=>$time), ' datestart ASC' );

    echo '<div class="singlebutton forumaddnew">';
    $url = new moodle_url('/local/supervision/editholidays.php', array());
    echo $OUTPUT->single_button($url, get_string('insertholiday', 'local_supervision'), 'post');
    echo '</div>';
    print '<br />';
    if($holidays) {
        $table = new html_table();
        $table->width = "80%";
        $table->head = array(get_string('name'), get_string('date'), get_string('duration', 'local_supervision'), get_string('type', 'local_supervision'), get_string('action')  );
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
