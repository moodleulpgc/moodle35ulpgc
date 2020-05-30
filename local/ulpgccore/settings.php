<?php

/**
 * ULPGC specific customizations admin tree pages & settings
 *
 * @package    local
 * @subpackage ulpgccore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

include_once($CFG->dirroot.'/local/ulpgccore/gradelib.php');

if ($hassiteconfig) {

    $temp = new admin_category('local_ulpgccore_settings', get_string('coresettings','local_ulpgccore')); 
    
    $ADMIN->add('localplugins', $temp);    
    
    $temp = new admin_settingpage('local_ulpgccore_sitesettings', get_string('sitesettings','local_ulpgccore')); 

    $temp->add(new \admin_setting_configtext('local_ulpgccore/annuality', get_string('annuality','local_ulpgccore'), get_string('explainannuality', 'local_ulpgccore'), ''));

    $temp->add(new \admin_setting_configtext('local_ulpgccore/coursestartdate', get_string('coursestartdate','local_ulpgccore'), get_string('explaincoursestartdate', 'local_ulpgccore'), ''));

    $temp->add(new \admin_setting_configcheckbox('local_ulpgccore/enabledrecentactivity', get_string('recentactivity','local_ulpgccore'), get_string('explainrecentactivity','local_ulpgccore'), 0));
    $temp->add(new \admin_setting_configcheckbox('local_ulpgccore/enabledadminmods', get_string('adminmods','local_ulpgccore'), get_string('explainadminmods','local_ulpgccore'), 0));
    
    $temp->add(new \admin_setting_configcheckbox('local_ulpgccore/enableupdateldap', get_string('updateldap','local_ulpgccore'), get_string('explainupdateldap','local_ulpgccore'), 0));
    
    $temp->add(new \admin_setting_configcheckbox('local_ulpgccore/mailednotviewed', get_string('mailednotviewed','local_ulpgccore'), get_string('explainmailednotviewed','local_ulpgccore'), 0));
    
    $temp->add(new \admin_setting_configtext('local_ulpgccore/manuales', get_string('repomanuals','local_ulpgccore'), get_string('explainrepomanuals','local_ulpgccore'), '/repository/manuales'));
    
    $temp->add(new \admin_setting_configtext('local_ulpgccore/croncheck', get_string('croncheck','local_ulpgccore'), get_string('explaincroncheck','local_ulpgccore'), '0'));
    $temp->add(new \admin_setting_configtext('local_ulpgccore/croncheckemail', get_string('croncheckemail','local_ulpgccore'), get_string('explaincroncheckemail','local_ulpgccore'), ''));
    
    $temp->add(new \admin_setting_configtextarea('local_ulpgccore/allowedcoursecats', get_string('allowedcoursecats','local_ulpgccore'), get_string('explainallowedcoursecats','local_ulpgccore'), ''));

    $ADMIN->add('local_ulpgccore_settings', $temp);    
    
    $temp = new admin_settingpage('local_ulpgccore_gradesettings', get_string('gradesettings','local_ulpgccore')); 

    $temp->add(new \admin_setting_configcheckbox('local_ulpgccore/enabledadvancedgrades', get_string('advancedgrades','local_ulpgccore'), get_string('explainadvancedgrades','local_ulpgccore'), 0));
    
    $temp->add(new \admin_setting_configselect('local_ulpgccore/scaledisplaymode', new lang_string('scaledisplaymode', 'local_ulpgccore'),
                                        new lang_string('configscaledisplaymode', 'local_ulpgccore'), GRADE_NORMAL_SCALE_DISPLAY,
                                        array(GRADE_NORMAL_SCALE_DISPLAY   => new lang_string('normalscaledisplay', 'local_ulpgccore'),
                                                GRADE_DETAILED_SCALE_DISPLAY => new lang_string('detailedscaledisplay', 'local_ulpgccore')))); // ecastro ULPGC detailed scales

    $temp->add(new \admin_setting_configcheckbox('local_ulpgccore/enabledgradebooklocking', get_string('gradebooklocking','local_ulpgccore'), get_string('explaingradebooklocking','local_ulpgccore'), 0));
    $levels = array(0,1,2,3,4,5,6);
    $temp->add(new \admin_setting_configselect('local_ulpgccore/gradebooklockingdepth', get_string('gradebooklockingdepth', 'local_ulpgccore'), get_string('explainlockingdepth', 'local_ulpgccore'), 0, $levels));
    
    $temp->add(new \admin_setting_configtext('local_ulpgccore/gradebooknocal', get_string('gradebooknocal','local_ulpgccore'), get_string('explaingradebooknocal','local_ulpgccore'), 'NoCal'));
    
    $temp->add(new \admin_setting_configtext('local_ulpgccore/locknameword', get_string('locknameword','local_ulpgccore'), get_string('explainlocknameword','local_ulpgccore'), '#nombre#', PARAM_RAW_TRIMMED));
    $temp->add(new \admin_setting_configtext('local_ulpgccore/lockaggword', get_string('lockaggword','local_ulpgccore'), get_string('explainlockaggword','local_ulpgccore'), '#agreg#', PARAM_RAW_TRIMMED));

    $ADMIN->add('local_ulpgccore_settings', $temp);    
    
    $temp = new admin_settingpage('local_ulpgccore_userssettings', get_string('userssettings','local_ulpgccore')); 
    
    $temp->add(new \admin_setting_pickroles('local_ulpgccore/nolistroles', get_string('nonlistedroles','local_ulpgccore'), get_string('explainnonlistedroles','local_ulpgccore'), array()));

    $temp->add(new admin_setting_configmulticheckbox('local_ulpgccore/showuserdetails',
            get_string('showuserdetails', 'local_ulpgccore'),
            get_string('showuserdetails_desc', 'local_ulpgccore'), array('idnumber' => 1), array(
                'idnumber'    => new lang_string('idnumber'),
                'email'       => new lang_string('email'),
                'phone1'      => new lang_string('phone'),
                'phone2'      => new lang_string('phone2'),
                'department'  => new lang_string('department'),
                'institution' => new lang_string('institution'),
                'city' => new lang_string('city'),
                'address'      => new lang_string('address'),
                'aim' => new lang_string('aimid'),
                'country' => new lang_string('country'),
                
            )));
    
    $ADMIN->add('local_ulpgccore_settings', $temp);    
    
    $temp = new admin_settingpage('local_ulpgccore_uisettings', get_string('uisettings','local_ulpgccore')); 

    $temp->add(new admin_setting_configtextarea('local_ulpgccore/customnavnodes',
            get_string('customnavnodes', 'local_ulpgccore'),
            get_string('explaincustomnavnodes', 'local_ulpgccore'), '', PARAM_TEXT, 10, 6));
    
    $temp->add(new \admin_setting_configcheckbox('local_ulpgccore/shortennavbar', get_string('shortennavbar','local_ulpgccore'), get_string('explainshortennavbar','local_ulpgccore'), 0));    
    
    $temp->add(new \admin_setting_configcheckbox('local_ulpgccore/enabledadvchooser', 
                    get_string('enableadvchooser','local_ulpgccore'), get_string('explainenableadvchooser','local_ulpgccore'), 0));
                    
    foreach(array('communication', 'collaboration', 'assessment', 'structured', 'games', 'other') as $type) {
        $temp->add(new admin_setting_configtextarea('local_ulpgccore/actv_'.$type,
                get_string('actv_'.$type, 'local_ulpgccore'),
                get_string('explainmodsgroup', 'local_ulpgccore'), '', PARAM_TEXT, 10, 6));
    }

    foreach(array('files', 'text', 'structured') as $type) {
        $temp->add(new admin_setting_configtextarea('local_ulpgccore/res_'.$type,
                get_string('res_'.$type, 'local_ulpgccore'),
                get_string('explainmodsgroup', 'local_ulpgccore'), '', PARAM_TEXT, 10, 6));
    }

    $ADMIN->add('local_ulpgccore_settings', $temp);    
    
    $temp = new admin_settingpage('local_ulpgccore_alerts', get_string('alerts','local_ulpgccore')); 
    
    $temp->add(new \admin_setting_configcheckbox('local_ulpgccore/showglobalalert', get_string('showglobalalert','local_ulpgccore'), get_string('explainshowglobalalert','local_ulpgccore'), 0));    
    
    $temp->add(new \admin_setting_configtext('local_ulpgccore/alertstart', get_string('alertstart','local_ulpgccore'), get_string('explainalertstart','local_ulpgccore'), ''));
    $temp->add(new \admin_setting_configtext('local_ulpgccore/alertend', get_string('alertend','local_ulpgccore'), get_string('explainalertend','local_ulpgccore'), ''));
    
    $temp->add(new \admin_setting_pickroles('local_ulpgccore/alertroles', get_string('alertroles','local_ulpgccore'), get_string('explainalertroles','local_ulpgccore'), array()));
    
    $temp->add(new \admin_setting_configselect('local_ulpgccore/alerttype', new lang_string('alerttype', 'local_ulpgccore'),
                                        new lang_string('explainalerttype', 'local_ulpgccore'), 'warning',
                                        array('success'   => new lang_string('success'),
                                              'info'   => new lang_string('info'),
                                              'warning'=> new lang_string('warning'),
                                              'danger'=> new lang_string('danger', 'local_ulpgccore')))); 
    
    $temp->add(new \admin_setting_configcheckbox('local_ulpgccore/alertdismiss', get_string('alertdismiss','local_ulpgccore'), get_string('explainalertdismiss','local_ulpgccore'), 0));    
    
    $temp->add(new admin_setting_confightmleditor('local_ulpgccore/alertmessage', new lang_string('alertmessage', 'local_ulpgccore'),
                                              '', ''));
    
    $ADMIN->add('local_ulpgccore_settings', $temp);   
    
    $temp = new admin_settingpage('local_ulpgccore_footer', get_string('footersettings','local_ulpgccore')); 

    foreach(array(1,2,3) as $i) {
        $temp->add(new admin_setting_confightmleditor('local_ulpgccore/footer'.$i, 
                            get_string('footerblock'.$i, 'local_ulpgccore'),
                            get_string('footerblock_desc', 'local_ulpgccore'),
                            null,
                            PARAM_RAW));
    }
    
    $ADMIN->add('local_ulpgccore_settings', $temp);    

}
