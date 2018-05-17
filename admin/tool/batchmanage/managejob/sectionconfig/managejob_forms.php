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
        
        $this->add_action_buttons(true, $next);
    }
    
}


/**
 * This class copies form for module configuration options
 *
 */
class batchmanage_section_config_form extends batchmanageform {
    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }
        
        $mform->addElement('header','general', get_string('section_config', 'managejob_sectionconfig'));

        $element = $mform->createElement('text', 'name', get_string('sectionname', 'managejob_sectionconfig'), array('size' => '30', 'maxlength' => '255'));
        $mform->setType('name', PARAM_TEXT);
        $this->add_grouped_element($element, 'name');

        $element = $mform->createElement('checkbox', 'usedefaultname', get_string('sectionusedefaultname') , '');
        $mform->setType('usedefaultname', PARAM_INT);
        $this->add_grouped_element($element, 'usedefaultname');

        $editoroptions = array('maxfiles' => 0, 'maxbytes'=>0, 'trusttext'=>false, 'noclean'=>true);
        $element = $mform->createElement('editor', 'summary_editor', get_string('summary'), null, $editoroptions);
        $mform->setType('summary_editor', PARAM_RAW);
        $this->add_grouped_element($element, 'summary_editor');

        $element = $mform->createElement('checkbox', 'setasmarker', get_string('setasmarker', 'managejob_sectionconfig'), '');
        $mform->setType('setasmarker', PARAM_INT);
        $this->add_grouped_element($element, 'setasmarker');

        $element = $mform->createElement('selectyesno', 'visible', get_string('visible'));
        $mform->setType('visible', PARAM_INT);
        $mform->setdefault('visible', 1);
        $this->add_grouped_element($element, 'visible');
        
        $element = $mform->createElement('textarea', 'availability', get_string('sectionavailability', 'managejob_sectionconfig'), array('size' => '30', 'maxlength' => '255'));
        $mform->setType('availability', PARAM_TEXT);
        $this->add_grouped_element($element, 'availability');
        
        
        $this->add_action_buttons(true, $next);
    }

}




