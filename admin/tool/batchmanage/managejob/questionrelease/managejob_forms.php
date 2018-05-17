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

class batchmanage_question_selector_form extends batchmanageform {

    function definition() {
        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }

        $mform->addElement('header', 'headsettings', get_string('questionssettings', 'managejob_questionrelease'));

        $mform->addElement('text', 'qtncategoryname', get_string('categoryname', 'managejob_questionrelease'), array('size'=>'60'));
        $mform->setDefault('qtncategoryname', '');
        $mform->setType('qtncategoryname', PARAM_TEXT);
        $mform->addHelpButton('qtncategoryname', 'categoryname', 'managejob_questionrelease');
        $mform->addRule('qtncategoryname', null, 'required');

        $mform->addElement('selectyesno', 'qtnuselike', get_string('uselike', 'managejob_questionrelease'));
        $mform->setDefault('qtnuselike', 0);
        $mform->addHelpButton('qtnuselike', 'uselike', 'managejob_questionrelease');

        $options = array( -1 => get_string('any'),
                          0 => get_string('topcategory', 'managejob_questionrelease'),
                          1 => get_string('subcategory', 'managejob_questionrelease'));
        $mform->addElement('select', 'qtncategoryparent', get_string('categoryparent', 'managejob_questionrelease'), $options);
        $mform->setDefault('qtncategoryparent', -1);

        $options = array( -1 => get_string('any'),
                          0 => get_string('coursecategory', 'managejob_questionrelease'),
                          1 => get_string('modcategory', 'managejob_questionrelease'));
        $mform->addElement('select', 'qtncategorycontext', get_string('categorycontext', 'managejob_questionrelease'), $options);
        $mform->setDefault('qtncategorycontext', -1);


        $mform->addElement('text', 'qtnquestionid', get_string('questionid', 'managejob_questionrelease'), array('size'=>'38'));
        $mform->setType('qtnquestionid', PARAM_TEXT);
        $mform->setDefault('qtnquestionid', '');
        $mform->addHelpButton('qtnquestionid', 'questionid', 'managejob_questionrelease');

        $options = array( -1 => get_string('any'),
                          1 => get_string('hidden', 'managejob_questionrelease'),
                          0 => get_string('visible'));
        $mform->addElement('select', 'qtnvisibility', get_string('questionvisibility', 'managejob_questionrelease'), $options);

        $mform->addElement('hidden', 'process', 'selectedquestion');
        $mform->setType('process', PARAM_TEXT);

        $this->add_action_buttons(true, $next);
    }
}



/**
 * This class copies form for module configuration options
 *
 */
class batchmanage_question_config_form extends batchmanageform {
    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }
        
        $mform->addElement('header','general', get_string('general', 'form'));

        $element = $mform->createElement('advcheckbox', 'hidden', get_string('questionhidden', 'managejob_questionrelease') , '');
        $mform->setType('confighidden', PARAM_INT);
        $this->add_grouped_element($element, 'hidden');

        $options = array(0=>get_string('tagremove', 'quiz_makeexam'),
                         1=>get_string('tagvalidated', 'quiz_makeexam'),
                         2=>get_string('tagrejected', 'quiz_makeexam'),
                         3=>get_string('tagunvalidated', 'quiz_makeexam'),
                         );
        $element = $mform->createElement('select', 'validated', get_string('questionvalidated', 'managejob_questionrelease') , $options);
        $mform->setType('configvalidated', PARAM_INT);
        $this->add_grouped_element($element, 'validated');

        $options = array('save'=>get_string('usersave', 'managejob_questionrelease'),
                         'restore'=>get_string('userrestore', 'managejob_questionrelease'),
                            );
        $element = $mform->createElement('select', 'userdata', get_string('questionuserdata', 'managejob_questionrelease') , $options);
        $mform->setType('configuserdata', PARAM_INT);
        $this->add_grouped_element($element, 'userdata');
        
        

        $this->add_action_buttons(true, $next);
    }

}




