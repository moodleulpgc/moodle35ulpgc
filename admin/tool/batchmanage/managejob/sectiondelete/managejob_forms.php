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

class batchmanage_section_selector_form extends batchmanageform {
    use batchmanage_section_selector;
    
    function definition() {
    
        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }
        
        $this->base_definition();
        
        $mform->addElement('selectyesno', 'sectionforcedelete', get_string('forcedelete', 'managejob_sectiondelete'));
        $mform->setType('sectionforcedelete', PARAM_INT);
        $mform->setDefault('sectionforcedelete', 0);
        $mform->addHelpButton('sectionforcedelete', 'forcedelete', 'managejob_sectiondelete');
        
        $this->add_action_buttons(true, $next);
    }
    
    
}

