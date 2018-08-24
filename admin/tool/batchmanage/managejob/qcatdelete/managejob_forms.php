<?php
/**
 * Defines Module config form
 *
 * @package    managejob_qcatdelete
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 global $CFG;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/admin/tool/batchmanage/managejob_forms.php');

class batchmanage_qcategory_selector_form extends batchmanageform {
    
    function definition() {
    
        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }
        
        $mform->addElement('header', 'headqcategorysettings', get_string('qcategorysettings', 'managejob_qcatdelete'));
        
        $mform->addElement('text', 'qcategoryname', get_string('qcategoryname', 'managejob_qcatdelete'), array('size'=>'60'));
        $mform->setDefault('qcategoryname', '');
        $mform->setType('qcategoryname', PARAM_TEXT);
        $mform->addRule('qcategoryname', null, 'required', '', 'client');
        $mform->addHelpButton('qcategoryname', 'qcategoryname', 'managejob_qcatdelete');

        $mform->addElement('selectyesno', 'qcategoryuselike', get_string('uselike', 'tool_batchmanage'));
        $mform->setDefault('qcategoryuselike', 0);
        $mform->addHelpButton('qcategoryuselike', 'uselike', 'tool_batchmanage');

        
        $mform->addElement('selectyesno', 'qcategoryforcedelete', get_string('forcedelete', 'managejob_qcatdelete'));
        $mform->setType('qcategoryforcedelete', PARAM_INT);
        $mform->setDefault('qcategoryforcedelete', 0);
        $mform->addHelpButton('qcategoryforcedelete', 'forcedelete', 'managejob_qcatdelete');
        
        $mform->addElement('text', 'qcategorysaved', get_string('qcategorysaved', 'managejob_qcatdelete'), array('size'=>'60'));
        $mform->setDefault('qcategorysaved', '');
        $mform->setType('qcategorysaved', PARAM_TEXT);
        $mform->addHelpButton('qcategorysaved', 'qcategorysaved', 'managejob_qcatdelete');
        $mform->disabledIf('qcategorysaved', 'forcedelete', 'eq', 0); 
        
        $this->add_action_buttons(true, $next);
    }
    
    
}

