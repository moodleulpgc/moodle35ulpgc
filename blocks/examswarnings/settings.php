<?php

defined('MOODLE_INTERNAL') || die;
//include_once('lib.php');
if ($ADMIN->fulltree) {

    // required during installation
    $dbman = $DB->get_manager();
    $table = new xmldb_table('examregistrar');
    $examregs = 0;
    if($dbman->table_exists($table)) {
        $examregs = $DB->get_records_select_menu('examregistrar', " primaryidnumber <> ''  ", null , 'name', 'id, name');
    }
    if(!$examregs) {
        $examregs = array(0 => get_string('none'));
    }

    $settings->add(new admin_setting_configselect('block_examswarnings/primaryreg', get_string('primaryreg', 'block_examswarnings'),
                    get_string('explainprimaryreg', 'block_examswarnings'), 0,  $examregs));

    $settings->add(new admin_setting_configtext('block_examswarnings/annuality', get_string('annuality','block_examswarnings'), get_string('explainannuality','block_examswarnings'), '201011'));

    //$settings->add(new admin_setting_configtime('block_examswarnings/runtimestarthour', 'runtimestartminute', get_string('statsruntimestart', 'admin'), get_string('configstatsruntimestart', 'admin'), array('h' => 3, 'm' => 0)));

    $settings->add(new admin_setting_configcheckbox('block_examswarnings/enablereminders', get_string('enablereminders', 'block_examswarnings'),
		get_string('configenablereminders', 'block_examswarnings'), 0, PARAM_INT));

    $roles = get_all_roles();
    $options = role_fix_names($roles, null, ROLENAME_ORIGINAL, true);
    list($usql, $params) = $DB->get_in_or_equal(array('editingteacher', 'teacher'));
    $defaultroles = $DB->get_records_select('role', " shortname $usql ", $params, '', 'id, name');

    $settings->add(new admin_setting_configmultiselect('block_examswarnings/reminderroles', get_string('reminderroles', 'block_examswarnings'), get_string('configreminderroles', 'block_examswarnings'), array_keys($defaultroles), $options));

    $days = array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,15=>15,21=>21,28=>28,30=>30,35=>35,42=>42,49=>49,56=>56,60=>60,90=>90);
    $settings->add(new admin_setting_configselect('block_examswarnings/reminderdays', get_string('reminderdays', 'block_examswarnings'),
            get_string('configreminderdays', 'block_examswarnings'), 1, $days));
    $settings->add(new admin_setting_confightmleditor('block_examswarnings/remindermessage', get_string('remindermessage','block_examswarnings'),
                                                      get_string('configremindermessage','block_examswarnings'), ''));

    $settings->add(new admin_setting_configcheckbox('block_examswarnings/enableroomcalls', get_string('enableroomcalls', 'block_examswarnings'),
        get_string('configenableroomcalls', 'block_examswarnings'), 0, PARAM_INT));

    $staffroles = 0;
    $table = new xmldb_table('examregistrar_elements');
    if($dbman->table_exists($table)) {
        $staffroles = $DB->get_records_menu('examregistrar_elements', array('type'=>'roleitem', 'visible'=>1, 'examregid'=>get_config('block_examswarnings','primaryreg') ), 'id ASC', 'id, name');
    }
    if(!$staffroles) {
        $staffroles = array(0 => get_string('none'));
    }

    $defaultroles = $DB->get_records('examregistrar_elements', array('type'=>'roleitem', 'idnumber'=>get_config('examregistrar','defaultrole')));

    $settings->add(new admin_setting_configmultiselect('block_examswarnings/roomcallroles', get_string('roomcallroles', 'block_examswarnings'), get_string('configroomcallroles', 'block_examswarnings'), array_keys($defaultroles), $staffroles));


    $settings->add(new admin_setting_configselect('block_examswarnings/roomcalldays', get_string('roomcalldays', 'block_examswarnings'),
            get_string('configreminderdays', 'block_examswarnings'), 1, $days));
    $settings->add(new admin_setting_confightmleditor('block_examswarnings/roomcallmessage', get_string('roomcallmessage','block_examswarnings'),
                                                      get_string('configroomcallmessage','block_examswarnings'), ''));

    $settings->add(new admin_setting_configcheckbox('block_examswarnings/enablewarnings', get_string('enablewarnings', 'block_examswarnings'),
        get_string('configenablewarnings', 'block_examswarnings'), 0, PARAM_INT));

    list($usql, $params) = $DB->get_in_or_equal(array('student'));
    $defaultroles = $DB->get_records_select('role', " shortname $usql ", $params, '', 'id, name');

    $settings->add(new admin_setting_configmultiselect('block_examswarnings/warningroles', get_string('warningroles', 'block_examswarnings'), get_string('configwarningroles', 'block_examswarnings'), array_keys($defaultroles), $options));

    $settings->add(new admin_setting_configselect('block_examswarnings/warningdays', get_string('warningdays', 'block_examswarnings'),
            get_string('configwarningdays', 'block_examswarnings'), 1, $days));
    $settings->add(new admin_setting_configselect('block_examswarnings/warningdaysextra', get_string('warningdaysextra', 'block_examswarnings'),
            get_string('configwarningdays', 'block_examswarnings'), 1, $days));
    $settings->add(new admin_setting_configselect('block_examswarnings/examconfirmdays', get_string('examconfirmdays', 'block_examswarnings'),
            get_string('configexamconfirmdays', 'block_examswarnings'), 1, $days));

    $settings->add(new admin_setting_configtext('block_examswarnings/examidnumber', get_string('examidnumber','block_examswarnings'), get_string('explainexamidnumber','block_examswarnings'), 'EXAMORD'));
            
    $settings->add(new admin_setting_confightmleditor('block_examswarnings/warningmessage', get_string('warningmessage','block_examswarnings'),
                                                      get_string('configwarningmessage','block_examswarnings'), ''));
    $settings->add(new admin_setting_confightmleditor('block_examswarnings/confirmmessage', get_string('confirmmessage','block_examswarnings'),
                                                      get_string('configconfirmmessage','block_examswarnings'), ''));

    $settings->add(new admin_setting_configtext('block_examswarnings/controlemail', get_string('controlemail','block_examswarnings'), get_string('controlemail','block_examswarnings'), '', PARAM_TAGLIST));
    
    $settings->add(new admin_setting_configcheckbox('block_examswarnings/noemail', get_string('noemail', 'block_examswarnings'),
        get_string('confignoemail', 'block_examswarnings'), 0, PARAM_INT));


}
