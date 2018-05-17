<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                                                                                                  //
// Copyright (C) 2008 Enrique Castro   ULPGC ecastro                                                               //
//                                                                                                                //
//                                                                                                               //
// This program is free software; you can redistribute it and/or modify                                         //
// it under the terms of the GNU General Public License as published by                                        //
// the Free Software Foundation; either version 2 of the License, or                                          //
// (at your option) any later version.                                                                       //
//                                                                                                          //
// This program is distributed in the hope that it will be useful,                                         //
// but WITHOUT ANY WARRANTY; without even the implied warranty of                                         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                                         //
// GNU General Public License for more details:                                                         //
//                                                                                                     //
//          http://www.gnu.org/copyleft/gpl.html                                                      //
//                                                                                                   //
//////////////////////////////////////////////////////////////////////////////////////////////////////

require_once('../../config.php');
require_once($CFG->libdir . '/formslib.php');


$referer = optional_param('referer', $CFG->wwwroot.'/', PARAM_LOCALURL);

/// TODO user username control from userroles admin report
/*
// Register our custom form control
MoodleQuickForm::registerElementType('username', "$CFG->dirroot/admin/report/userroles/username.php",
        'MoodleQuickForm_username');
*/

// moodleform for controlling the report
class block_usereports_form extends moodleform {
    function definition() {
        global $CFG;

        $mform =& $this->_form;
        $mform->addElement('header', 'usereportsettings', get_string('usereports', 'block_usermanagement'));

        $mform->addElement('checkbox', 'reportallteachers', get_string('reportallteachers', 'block_usermanagement'));
        $mform->setDefault('reportallteachers', 1);

        $mform->addElement('text', 'username', get_string('singleuser', 'block_usermanagement'), 'size="10"');
        $mform->setType('username', PARAM_INT);
        $mform->disabledIf('username', 'reportallteachers', 'checked');

        $mform->addElement('checkbox', 'reportallcats', get_string('reportallcats', 'block_usermanagement'));
        $mform->setDefault('reportallcats', 1);

        $categories = make_categories_options();
        $catmenu = &$mform->addElement('select', 'categories', get_string('reportcategories', 'block_usermanagement'), $categories, 'size="10"');
        $catmenu->setMultiple(true);
        $mform->disabledIf('categories', 'reportallcats', 'checked');
        //$mform->disabledIf('categories', 'reportallteachers', 'notchecked');
        $mform->addElement('static', 'categorieshelp', '', get_string('applycategorieshelp', 'usermanagement'));

        $mform->addElement('header', 'reportoutputsettings', get_string('reportoutputsettings', 'block_usermanagement'));

        $options = array();
        $options['choose'] = get_string('choose');
        $options['html'] = get_string('outputhtml', 'block_usermanagement');
        $options['pdf'] = get_string('outputpdf', 'block_usermanagement');
        $options['xls'] = get_string('outputxls', 'block_usermanagement');
        $options['ods'] = get_string('outputodf', 'block_usermanagement');
        $options['csv'] = get_string('outputcsv', 'block_usermanagement');
        $mform->addElement('select', 'reportoutput', get_string('reportoutput', 'block_usermanagement'), $options);
        $mform->setDefault('reportoutput', 'html');
        //$mform->disabledIf('reportoutput', 'reportallteachers', 'notchecked');

        $mform->addElement('choosecoursefile', 'template', get_string('reporttemplate', 'block_usermanagement'), array('courseid'=>'1'), array('size' => 40));
        $mform->disabledIf('template', 'reportoutput', 'neq', 'pdf');

        $this->add_action_buttons(true, get_string('getusereports', 'block_usermanagement'));
    }
}

require_login();
$context = context_system::instance();
require_capability('block/usermanagement:manage', $context);

if (!$site = get_site()) {
    error("Could not find site-level course");
}

if (!$adminuser = get_admin()) {
    error("Could not find site admin");
}

$action = optional_param('action', 'none', PARAM_ALPHA);

if($action==='download') {
    $userid = optional_param('user', 0, PARAM_INT);
    $catids = optional_param('cats', '', PARAM_ALPHA);
    $format = optional_param('format', 'html', PARAM_ALPHA);
    $template = optional_param('tpl', '', PARAM_FILE);

    $user = '';
    if($userid) {
        $user = get_record('user', 'id', $userid);
    }

    $categories = '';
    if($catids) {
        $categories = explode(',', $catids);
        $categories = array_combine($categories, $categories);
    }

    if($user) {
        $reports = ulpgc_usereports_single($user, $categories, true);
    } else {
        $reports = ulpgc_usereports_multiple($categories);
    }

    ulpgc_usereports_format($reports, $format, $template);
    die;
}

$strfile = get_string('file');
$strusereports = get_string('usereports','block_usermanagement');
$navlinks = array(array('name' => get_string('administration'), 'link' => "$CFG->wwwroot/$CFG->admin/index.php", 'type' => 'misc'));
$navlinks[] = array('name' => $strusereports, 'link' => null, 'type' => 'misc');
$navigation = build_navigation($navlinks);
print_header($strusereports, $strusereports, $navigation);


$mform = new block_usereports_form();
if ($formdata = $mform->get_data()) {

    /// some data, process input
    if(isset($formdata->cancel)) {
        redirect($referer, '', 0);
    }

    if(isset($formdata->categories)) {
      $categories = array_combine($formdata->categories, $formdata->categories);
      $catids = implode(',', $categories);
      $catnames = '';
      $catnames = implode('<br />', get_records_select_menu('course_categories', "id IN ( $catids) ", 'name ASC', 'id, name'));
    } else {
      $categories = 0;
      $catids = '';
      $catnames = get_string('all');
    }

    $user = '';
    if(isset($formdata->username) ) {
        //check if user exists and is valid
        $user = get_record('user', 'idnumber', $formdata->username);
        if(!$user || !$useru = get_record('user_ulpgc', 'userid', $user->id)) {
            $message = get_string('reportsusernotfound','block_usermanagement', $formdata->username);
            redirect($referer, $message, 5);
        }
    }

    print_heading(get_string('datafromcategories', 'block_usermanagement', $catnames));
    if ($formdata->reportoutput == 'html') {
        // we have a single user and output is html: display directly
  //      print_heading(get_string('datafromcategories', 'block_usermanagement', $catnames));
        if($user) {
            $reports = ulpgc_usereports_single($user, $categories, true);
        } else {
            $reports = ulpgc_usereports_multiple($categories);
        }
        ulpgc_usereports_format($reports, 'html');

    } else {
        // several users or output != html printdownload button
        if($user) {
            $userid = $user->id;
            $numreports = 1;
        } else {
            $userid='0';
            $catwhere = '';
            if($catids) {
                $catwhere = " AND category IN ( $catids ) ";
            }
            $numreports = count_records_select('course', "credits >= 0 $catwhere ", 'COUNT(1)');
        }
        $format = $formdata->reportoutput;
//        print_heading(get_string('datafromcategories', 'block_usermanagement', $catnames));
        print_container_start(true, 'generalbox boxaligncenter boxwidthnormal centerpara');
        echo '<p>'.get_string('exportusedata', 'block_usermanagement', $numreports).'</p>';
        print_single_button($CFG->wwwroot.'/blocks/usermanagement/usereports.php',
                                    array('action'=>'download', 'user' =>$userid, 'cats' => $catids, 'format' => $format, 'tpl' => $formdata->template),
                                    get_string('output'.$format, 'block_usermanagement'), 'post');
        //print_continue($referer);
        print_container_end();
    }

    print_footer();
    exit;
}

$mform->display();

print_footer();
?>
