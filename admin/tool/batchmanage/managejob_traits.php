<?php
/**
 * Defines module selection form
 *
 * @package    tool_batchmanage
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/formslib.php');


trait batchmanage_mod_selector {
    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }


        $mform->addElement('header', 'headmodsettings', get_string('modselectorsettings', 'tool_batchmanage'));

        $modulemenu = array();
        $modules = $DB->get_records('modules', array('visible' => 1), '', 'id, name');
        foreach ($modules as $module) {
            $modulemenu["$module->name"] = get_string('modulename', $module->name);
        }
        natcasesort($modulemenu);
        array_unshift($modulemenu, get_string('choose'));

        $mform->addElement('select', 'module', get_string('modname', 'tool_batchmanage'), $modulemenu);
        $mform->setDefault('module', 'choose');
        $mform->setType('module', PARAM_TEXT);
        //$mform->addRule('module', get_string('err_required', 'form'), 'lettersonly', null, 'client');
        $mform->addRule('module', null, 'required', '', 'client');

        $mform->addElement('text', 'instancename', get_string('instancename', 'tool_batchmanage'), array('size'=>'60'));
        $mform->setDefault('instancename', '');
        $mform->setType('instancename', PARAM_TEXT);
        $mform->addRule('instancename', null, 'required', null, 'client');
        $mform->addHelpButton('instancename', 'instancename', 'tool_batchmanage');

        $mform->addElement('selectyesno', 'uselike', get_string('uselike', 'tool_batchmanage'));
        $mform->setDefault('uselike', 0);
        $mform->addHelpButton('uselike', 'uselike', 'tool_batchmanage');

        $mform->addElement('text', 'instanceid', get_string('modinstanceid', 'tool_batchmanage'), array('size'=>'38'));
        $mform->setType('instanceid', PARAM_TEXT);
        $mform->setDefault('instanceid', '');
        $mform->addHelpButton('instanceid', 'modinstanceid', 'tool_batchmanage');

        $mform->addElement('text', 'coursemoduleid', get_string('modcoursemoduleid', 'tool_batchmanage'), array('size'=>'38'));
        $mform->setType('coursemoduleid', PARAM_TEXT);
        $mform->setDefault('coursemoduleid', '');
        $mform->addHelpButton('coursemoduleid', 'modcoursemoduleid', 'tool_batchmanage');

        $sections = $DB->get_record_sql("SELECT MAX(section) FROM {course_sections}");
        $maxsections = reset($sections);
        unset($sections);
        $sections = array(-1 =>get_string('any'));
        for ($i = 0; $i <= $maxsections; $i++) {
            $sections[$i] = $i;
        }

        $mform->addElement('select', 'insection', get_string('insection', 'tool_batchmanage'), $sections);
        $mform->setDefault('insection', -1);

        $options = array( -1 => get_string('any'));
        for($i=0; $i <= 5; $i++) {
            $options[$i] = $i;
        }
        $mform->addElement('select', 'indent', get_string('modindent', 'tool_batchmanage'), $options);

        $options = array( -1 => get_string('any'),
                          0 => get_string('hidden', 'tool_batchmanage'),
                          1 => get_string('visible'));
        $mform->addElement('select', 'visible', get_string('modvisible', 'tool_batchmanage'), $options);

        $options = array( -1 => get_string('any'),
                            NOGROUPS       => get_string('groupsnone'),
                            SEPARATEGROUPS => get_string('groupsseparate'),
                            VISIBLEGROUPS  => get_string('groupsvisible'));
        $mform->addElement('select', 'groupmode', get_string('groupmode', 'group'), $options, NOGROUPS);

        $mform->addElement('text', 'cmidnumber', get_string('modidnumber', 'tool_batchmanage'), array('size'=>'10'));
        $mform->setType('cmidnumber', PARAM_TEXT);
        $mform->setDefault('cmidnumber', '');

        
        if(get_config('local_ulpgccore', 'enabledadminmods')) {
            $options = array( -1 => get_string('adminincluded', 'tool_batchmanage'),
                            0 => get_string('adminexcluded', 'tool_batchmanage'),
                            1 => get_string('adminonly', 'tool_batchmanage'),
                            );

            if ($DB->record_exists_select('course_modules', " score > 0 ", null)) {
                $mform->addElement('select', 'adminrestricted', get_string('adminrestricted', 'tool_batchmanage'), $options);
                $mform->setDefault('adminrestricted', -1);
                $mform->addHelpButton('adminrestricted', 'adminrestricted', 'tool_batchmanage');
            }
        } else {
            $mform->addElement('hidden', 'adminrestricted', 0);
            $mform->setType('adminrestricted', PARAM_INT);
        }

        $this->add_action_buttons(true, $next);
    }
}

trait batchmanage_mod_selector_sql {
    public function mod_selector_sql() {
        global $DB;
        $params = array();
        $wheremodule = '';
        $formdata = json_decode($this->formsdata['mod_selector']);
        
        $module = $DB->get_record('modules', array('name'=>$formdata->module), '*', MUST_EXIST);
        $params[] = $module->id;
        
        if ($formdata->uselike) {
            $wheremodule .= $DB->sql_like('md.name', '?');
        } else {
            $wheremodule .= " md.name = ? ";
        }
        $params[] =  $formdata->instancename;

        if(isset($formdata->instanceid) &&  $formdata->instanceid ) {
            if($names = explode(',' , addslashes($formdata->instanceid))) {
                foreach($names as $key => $name) {
                    $names[$key] = trim($name);
                }
                list($insql, $inparams) = $DB->get_in_or_equal($names);
                $wheremodule .= " AND md.id $insql ";
                $params = array_merge($params, $inparams);
            }
        }

        if(isset($formdata->visible) && $formdata->visible > -1) {
            $wheremodule .= " AND cm.visible = ? ";
            $params[] =  $formdata->visible;
        }

        if(isset($formdata->groupmode) &&  $formdata->groupmode > -1) {
            $wheremodule .= " AND cm.groupmode = ? ";
            $params[] =  $formdata->groupmode;
        }

        if(isset($formdata->groupmembersonly) &&  $formdata->groupmembersonly > -1) {
            $wheremodule .= " AND cm.groupmembersonly = ? ";
            $params[] =  $formdata->groupmembersonly;
        }

        if(isset($formdata->cmidnumber) && $formdata->cmidnumber) {
            $wheremodule .= " AND cm.idnumber = ? ";
            $params[] =  $formdata->cmidnumber;
        }

        if(isset($formdata->insection) &&  $formdata->insection > -1) {
            $wheremodule .=  " AND s.section = ? ";
            $params[] =  $formdata->insection;
        }

        if(isset($formdata->indent) &&  $formdata->indent > -1) {
            $wheremodule .=  " AND cm.indent = ? ";
            $params[] =  $formdata->indent;
        }

        if(isset($formdata->adminrestricted) && $formdata->adminrestricted != -1) {
            if($formdata->adminrestricted == 1) {
                $wheremodule .= " AND cm.score > 0 ";
            } elseif($formdata->adminrestricted >= 0) {
                $wheremodule .= " AND cm.score = ? ";
                $params[] =  $formdata->adminrestricted;
            }
        }

        if(isset($formdata->coursemoduleid) &&  $formdata->coursemoduleid ) {
            if($names = explode(',' , addslashes($formdata->coursemoduleid))) {
                foreach($names as $key => $name) {
                    $names[$key] = trim($name);
                }
                list($insql, $inparams) = $DB->get_in_or_equal($names);
                $wheremodule .= " AND cm.id $insql ";
                $params = array_merge($params, $inparams);
            }
        }

        return array($wheremodule, $params);
    }
}

trait batchmanage_section_selector {
    function base_definition() {
        global $CFG, $DB;

        $mform =& $this->_form;

        $mform->addElement('header', 'headsectionsettings', get_string('sectionsettings', 'tool_batchmanage'));
        
        $mform->addElement('text', 'sectionname', get_string('sectionname', 'tool_batchmanage'), array('size'=>'60'));
        $mform->setDefault('sectionname', '');
        $mform->setType('sectionname', PARAM_TEXT);
        $mform->addRule('sectionname', null, 'required', '', 'client');
        $mform->addHelpButton('sectionname', 'sectionname', 'tool_batchmanage');

        $mform->addElement('selectyesno', 'sectionuselike', get_string('uselike', 'tool_batchmanage'));
        $mform->setDefault('sectionuselike', 0);
        $mform->addHelpButton('sectionuselike', 'uselike', 'tool_batchmanage');

        $sections = $DB->get_record_sql("SELECT MAX(section) FROM {course_sections}");
        $maxsections = reset($sections);
        unset($sections);
        $sections = array(-1 =>get_string('any'));
        for ($i = 0; $i <= $maxsections; $i++) {
            $sections[$i] = $i;
        }
        $mform->addElement('select', 'sectioninsection', get_string('sectioninsection', 'tool_batchmanage'), $sections);
        $mform->setDefault('sectioninsection', -1);

        $mform->addElement('text', 'sectionid', get_string('sectioninstanceid', 'tool_batchmanage'), array('size'=>'38'));
        $mform->setType('sectionid', PARAM_TEXT);
        $mform->setDefault('sectionid', '');
        $mform->addHelpButton('sectionid', 'sectioninstanceid', 'tool_batchmanage');

        $options = array( -1 => get_string('any'),
                          0 => get_string('hidden', 'tool_batchmanage'),
                          1 => get_string('visible'));
        $mform->addElement('select', 'sectionvisible', get_string('modvisible', 'tool_batchmanage'), $options);
    }
}


trait batchmanage_section_selector_sql {
    public function section_selector_sql() {
        global $DB;
        
        $formdata = json_decode($this->formsdata['section_selector']);
        $params = array();

        $where = ' 1 ';
        if(isset($formdata->sectionname) && $formdata->sectionname) {
            if(strtolower($formdata->sectionname) == 'null') {
                $where .= " AND cs.name IS NULL ";
            } else {
                if ($formdata->sectionuselike) {
                    $where .= ' AND '.$DB->sql_like('cs.name', '?');
                } else {
                    $where .= " AND cs.name = ? ";
                }
                $params[] =  $formdata->sectionname;
            }
        }

        if(isset($formdata->sectionid) &&  $formdata->sectionid ) {
            if($names = explode(',' , addslashes($formdata->sectionid))) {
                foreach($names as $key => $name) {
                    $names[$key] = trim($name);
                }
                list($insql, $inparams) = $DB->get_in_or_equal($names);
                $where .= " AND cs.id $insql ";
                $params = array_merge($params, $inparams);
            }
        }

        if(isset($formdata->sectionvisible) && $formdata->sectionvisible > -1) {
            $where .= " AND cs.visible = ? ";
            $params[] =  $formdata->sectionvisible;
        }

        if(isset($formdata->sectioninsection) &&  $formdata->sectioninsection > -1) {
            $where .=  " AND cs.section = ? ";
            $params[] =  $formdata->sectioninsection;
        }

        return array($where, $params);
    }
}
