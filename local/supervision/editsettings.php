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
    require_once($CFG->dirroot."/local/supervision/editsettingsform.php");
    require_once($CFG->libdir.'/adminlib.php');

    $cid = optional_param('cid', SITEID, PARAM_INT);
    $baseparams = array('cid' => $cid);

    $baseurl = new moodle_url('/local/supervision/editsettings.php', $baseparams);

    require_login();
    
    $title = get_string('supervisionsettings', 'local_supervision');
    $context = supervision_page_setup('supervisionsettings', $baseurl, $title);
    require_capability('local/supervision:manage', $context);

    $returnurl = get_local_referer(false);
    $me = qualified_me();
    if($returnurl == $me) {
        $returnurl = is_siteadmin() ? '/admin/settings.php?section=supervisionwarnings' : '/my';
        $returnurl = $CFG->wwwroot.$returnurl;
    }

    $form = new supervision_editsettings_form(null, array('cid'=>$cid));

    if ($form->is_cancelled()) {
        redirect($returnurl);

    } elseif ($formdata = $form->get_data()) {
        $config = get_config('local_supervision');
        foreach($config as $key => $value) {
            if(isset($formdata->{$key})) {
                $newval = $formdata->{$key};
                if(is_array($newval)) {
                    $newval = implode(',',$newval);
                }
                if($newval != $value) {
                    set_config($key, $newval, 'local_supervision'); 
                }
            }
        }
        \core\notification::success(get_string('changessaved'));
        //redirect($returnurl, $message, $delay);
    }

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('supervisionsettings', 'local_supervision'));

    $form->display();

    echo $OUTPUT->footer();


