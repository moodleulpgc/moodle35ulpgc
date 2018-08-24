<?php
/**
 * Defines Module config form
 *
 * @package    managejob_gcatconfig
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/admin/tool/batchmanage/managejob_forms.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->dirroot.'/grade/edit/tree/category_form.php');

class batchmanage_gcat_selector_form extends batchmanageform {
    
    function definition() {
    
        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }
        
        $mform->addElement('header', 'headgcatsettings', get_string('gcatsettings', 'managejob_gcatconfig'));
        
        $mform->addElement('text', 'gcatname', get_string('categoryname', 'grades'), array('size'=>'60'));
        $mform->setDefault('gcatname', '');
        $mform->setType('gcatname', PARAM_TEXT);

        $mform->addElement('selectyesno', 'gcatuselike', get_string('uselike', 'tool_batchmanage'));
        $mform->setDefault('gcatuselike', 0);
        $mform->addHelpButton('gcatuselike', 'uselike', 'tool_batchmanage');

        $mform->addElement('text', 'gcatidnumber', get_string('idnumbermod'), array('size'=>'30'));
        $mform->setDefault('gcatidnumber', '');
        $mform->setType('gcatidnumber', PARAM_TEXT);
        $mform->addHelpButton('gcatidnumber', 'idnumbermod');

        $mform->addElement('text', 'gcatparentname', get_string('parentcategory', 'managejob_gcatconfig'), array('size'=>'30'));
        $mform->setDefault('gcatparentname', '');
        $mform->setType('gcatparentname', PARAM_TEXT);
        $mform->addHelpButton('gcatparentname', 'parentcategory', 'managejob_gcatconfig');
        
        $mform->addElement('text', 'gcatparentidnumber', get_string('gcatparentidnumber', 'managejob_gcatconfig'), array('size'=>'30'));
        $mform->setDefault('gcatparentidnumber', '');
        $mform->setType('gcatparentidnumber', PARAM_TEXT);
        $mform->addHelpButton('gcatparentidnumber', 'gcatparentidnumber', 'managejob_gcatconfig');

        $options = array(0 => get_string('any'),
                        1 => 1,
                        2 => 2,
                        3 => 3,
                        4 => 4,
                        5 => 5,
                        6 => 6,
                        7 => 7,
                        8 => 8,
                        9 => 9,
                        );
        $mform->addElement('select', 'gcatdepth', get_string('gcatdepth', 'managejob_gcatconfig'), $options);
        $mform->setType('gcatdepth', PARAM_INT);
        $mform->setDefault('gcatdepth', 0);
        $mform->addHelpButton('gcatdepth', 'gcatdepth', 'managejob_gcatconfig');

        $aggregations = array(0 => get_string('any')) + grade_helper::get_aggregation_strings();
        $mform->addElement('select', 'gcataggregation', get_string('aggregation', 'grades'), $aggregations);
        $mform->setType('gcataggregation', PARAM_INT);
        $mform->setDefault('gcataggregation', 0);
        $mform->addHelpButton('gcataggregation', 'aggregation', 'grades');  

        
        $options = array(0 => get_string('any'),
                        1 => get_string('yes'),
                        -1 => get_string('no'),
                        );
        $mform->addElement('select', 'gcataggregateonlygraded', get_string('aggregateonlygraded', 'grades'), $options);
        $mform->setType('gcataggregateonlygraded', PARAM_INT);
        $mform->setDefault('gcataggregateonlygraded', 0);
        $mform->addHelpButton('gcataggregateonlygraded', 'aggregateonlygraded', 'grades');
        
        $options = array(0 => get_string('any'),
                        1 => get_string('hidden', 'tool_batchmanage'),
                        -1 => get_string('visible'),
                        );
        $mform->addElement('select', 'gcathidden', get_string('gcathidden', 'managejob_gcatconfig'), $options);
        $mform->setType('gcathidden', PARAM_INT);
        $mform->setDefault('gcathidden', 0);
        $mform->addHelpButton('gcathidden', 'gcathidden', 'managejob_gcatconfig');
        
        $this->add_action_buttons(true, $next);
    }

    /**
     * Enforce validation rules here
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array
     **/
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $fields = array('gcatname', 'gcatidnumber', 'gcatparentname', 'gcatparentidnumber', 
                        'gcatdepth', 'gcataggregation', 'gcataggregateonlygraded','gcathidden');
        $content = false;
        
        // Check there is any content
        foreach($fields as $field) {
            if(isset($data[$field]) && trim($data[$field])) {
                $content = true;
                break;
            }
        }
        
        if(!$content) {
            $errors['gcatname'] = get_string('emptyform', 'tool_batchmanage');
        }

        return $errors;
    }
    
    
}


class batchmanage_gcat_config_form extends batchmanageform {
    
    function definition() {
        global $COURSE, $DB;
    
        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }

        $gpr = new grade_plugin_return();
        $grade_category = new grade_category(array('courseid'=>0), false);
        $grade_category->apply_default_settings();
        $grade_category->apply_forced_settings();

        $category = $grade_category->get_record_data();

        $grade_item = new grade_item(array('courseid'=>0, 'itemtype'=>'manual'), false);
        foreach ($grade_item->get_record_data() as $key => $value) {
            $category->{"grade_item_$key"} = $value;
        }
        $category->parentcategory = 'vvv';
        
        $oldcourseid = $COURSE->id;
        $reference = 0;
        if(!$idnumber = get_config('managejob_gcatconfig','referencecourse')) {
            $idnumber = get_config('tool_batchmanage','referencecourse');
        }
        if($idnumber) {
            $reference = $DB->get_field('course', 'id', array('shortname'=>$idnumber));
        }
        if(!$reference) {
            $reference = 3;
        }
        $COURSE->id = $reference;
        
        $configform = new edit_category_form(null, array('current'=>$category, 'gpr'=>$gpr));
        $configform->set_data($category);
        $COURSE->id = $oldcourseid;
        
        $rp = new ReflectionProperty('edit_category_form', '_form');
        $rp->setAccessible(true);
        $innerform = $rp->getValue($configform);

        
//        print_object($innerform->_elementIndex);
        
        
        $ignore = array('id', 'courseid', 'buttonar', 'sesskey', 'parentcategory', 'submitbutton', 'cancel', 
                        'currentparentaggregation', '_qf__edit_category_form', '', 
                    ); // empty '' essential to avoid freezing of whole form 

        foreach($innerform->_elementIndex as $key =>$value) {
            $ignored = in_array($key, $ignore);
            if($key && !$ignored) {
                $element = $innerform->getElement($key);
                $type = $element->getType();
                if($type != 'hidden') {
                    if($type != 'header') {
                        $this->add_grouped_element($element, $key);
                    } else {
                        $mform->addElement($element);
                    }
                }
            }
        } 
        
        
        $element = $mform->createElement('text', 'parentcategory', get_string('parentcategory', 'managejob_gcatconfig'), array('size'=>'30'));
        $mform->setDefault('parentcategory', '');
        $mform->setType('parentcategory', PARAM_TEXT);
        $this->add_grouped_element($element, 'parentcategory');

        
        $options = array(1 => get_string('before', 'managejob_gcatadd'),
                         0 => get_string('after', 'managejob_gcatadd'),
                        );
        $element = $mform->createElement('select', 'insertfirst', get_string('gcatinsertfirst', 'managejob_gcatadd'), $options);
        $mform->setType('insertfirst', PARAM_INT);
        $mform->setDefault('insertfirst', 0);
        $this->add_grouped_element($element, 'insertfirst');
        
        
        $this->add_action_buttons(true, $next);
    }

}
