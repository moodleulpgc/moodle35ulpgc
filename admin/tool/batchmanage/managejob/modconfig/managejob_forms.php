<?php
/**
 * Defines Module config form
 *
 * @package    tool_batchmanage
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 global $CFG;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/admin/tool/batchmanage/managejob_forms.php');

class batchmanage_mod_selector_form extends batchmanageform {
    use batchmanage_mod_selector;
}


/**
 * This class copies form for module configuration options
 *
 */
class batchmanage_mod_configurator_form extends batchmanageform {
    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }
        
        $refcourse = $DB->get_record('course', array('shortname'=>get_config('managejob_modconfig', 'referencecourse')), 'id, shortname, fullname, category', MUST_EXIST);
        $mform->addElement('static', 'refcourse', $refcourse->shortname.' - '.$refcourse->fullname);
        
        $modselector = json_decode($managejob->formsdata['mod_selector']);
        $module = $DB->get_record('modules', array('name'=>$modselector->module), '*', MUST_EXIST);
        $moduletable = '{'.$modselector->module.'}';
        
        
        $sql = "SELECT cm.id as cmid, CONCAT(cs.section, ' - ', m.name) as name 
                FROM {course_modules} cm 
                JOIN $moduletable m ON cm.instance = m.id 
                JOIN {course_sections} cs ON cs.id = cm.section 
                WHERE cm.course = ? AND cm.module = ? 
                ORDER BY cs.section ASC, m.name ASC, cm.id ASC ";
                
        $menu = array(0=>get_string('none'));
        if($modules = $DB->get_records_sql_menu($sql, array($refcourse->id, $module->id))) {        
            $menu = $menu + $modules;
        }
        
        $mform->addElement('select', 'refmod_cmid', get_string('referencemod', 'managejob_modconfig'), $menu);
        $mform->addHelpButton('refmod_cmid', 'referencemod', 'managejob_modconfig');

        $this->add_action_buttons(false, $next);
    }

}

/**
 * This class copies form for module configuration options
 *
 */
class batchmanage_mod_config_form extends batchmanageform {
    function definition() {
        global $CFG, $COURSE, $DB;

        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }
        
        $refcourse = $DB->get_record('course', array('shortname'=>get_config('managejob_modconfig', 'referencecourse')), '*', MUST_EXIST);
        $modselector = json_decode($managejob->formsdata['mod_selector']);
        $managejob->refmod_cmid = json_decode($managejob->formsdata['mod_configurator']);
       
        include_once($CFG->dirroot.'/lib/gradelib.php');
        include_once($CFG->dirroot.'/mod/'.$modselector->module.'/mod_form.php');
        include_once($CFG->dirroot.'/lib/plagiarismlib.php'); 
        $formclass = 'mod_'.$modselector->module.'_mod_form';
        $cm = get_coursemodule_from_id($modselector->module,$managejob->refmod_cmid);
        $modinfo = $managejob->get_modinfodata($cm);
        
        
        $oldcourse = clone($COURSE);
        $COURSE = $refcourse;
        // plagiarism plugins call incompatible JS & other libraries
        $plagiarism = $CFG->enableplagiarism;
        $CFG->enableplagiarism = false;
        $configform = new $formclass($modinfo, $modinfo->section, null, $refcourse);
        $configform->set_data($modinfo);
        $COURSE = $oldcourse;
        $CFG->enableplagiarism = $plagiarism;
        
        $rp = new ReflectionProperty($formclass, '_form');
        $rp->setAccessible(true);
        $innerform = $rp->getValue($configform);
        
        $ignore = array('course', 'coursemodule', 'instance', 'section',  'module', 'modulename', 
                    'add', 'update', 'return', 'sr', 'buttonar', 'sesskey',
                    '_rqf__mod_assign_mod_form', 'mform_showadvanced_last', 'groupingid', 'restrictgroupbutton',
                    'conditiongraderepeats', 'conditiongradegroup', 'conditiongradeadds', 'conditiongradegroup',
                    '', 
                    ); // empty '' essential to avoid freezing of whole form 

        foreach($innerform->_elementIndex as $key =>$value) {
            $ignored = in_array($key, $ignore);
            if($key && !$ignored) {
                $element = $innerform->getElement($key);
                $type = $element->getType();
                if(($type != 'hidden') && ($type != 'filemanager')) {
                    if($type != 'header') {
                        $this->add_grouped_element($element, $key);
                    } else {
                        $mform->addElement($element);
                    }
                }
            }
        }

        $mform->addElement('header', 'capabilitysettings', get_string('capabilitysettings', 'managejob_modconfig'));
        
        $permissions = array(
            CAP_INHERIT => new lang_string('inherit', 'role'),
            CAP_ALLOW => new lang_string('allow', 'role'),
            CAP_PREVENT => new lang_string('prevent', 'role'),
            CAP_PROHIBIT => new lang_string('prohibit', 'role')
        );
        $element = $mform->createElement('select', 'permission', get_string('permissions', 'role'), $permissions);
        $this->add_grouped_element($element, 'permission');

        
        $modcontext = context_module::instance($cm->id);
        $capabilitychoices = array();
        foreach ($modcontext->get_capabilities() as $cap) {
            $capabilitychoices[$cap->name] = $cap->name . ': ' . get_capability_string($cap->name);
        }
        $element = $mform->createElement('select', 'capabilities', get_string('capabilities', 'role'), $capabilitychoices);
        $element->setMultiple(true);
        $this->add_grouped_element($element, 'capabilities');
        
        $rolechoices = array();
        // Prepare the list of roles to choose from.
        foreach (role_fix_names(get_all_roles($modcontext)) as $role) {
            $rolechoices[$role->id] = $role->localname;
        }
        $element = $mform->createElement('select', 'roles', get_string('roles'), $rolechoices);
        $element->setMultiple(true);
        $this->add_grouped_element($element, 'roles');
        
        $this->add_action_buttons(true, $next);
    }

}




