<?php
/**
 * Defines Module config form
 *
 * @package    managejob_gcatdelete
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 global $CFG;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/admin/tool/batchmanage/managejob_forms.php');
require_once($CFG->dirroot.'/grade/lib.php');

class batchmanage_gcat_selector_form extends batchmanageform {
    
    function definition() {
    
        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }
        
        $mform->addElement('header', 'headgcatsettings', get_string('gcatsettings', 'managejob_gcatdelete'));
        
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

        $mform->addElement('text', 'gcatparentname', get_string('parentcategory', 'managejob_gcatdelete'), array('size'=>'30'));
        $mform->setDefault('gcatparentname', '');
        $mform->setType('gcatparentname', PARAM_TEXT);
        $mform->addHelpButton('gcatparentname', 'parentcategory', 'managejob_gcatdelete');
        
        $mform->addElement('text', 'gcatparentidnumber', get_string('gcatparentidnumber', 'managejob_gcatdelete'), array('size'=>'30'));
        $mform->setDefault('gcatparentidnumber', '');
        $mform->setType('gcatparentidnumber', PARAM_TEXT);
        $mform->addHelpButton('gcatparentidnumber', 'gcatparentidnumber', 'managejob_gcatdelete');
        
        

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
        $mform->addElement('select', 'gcatdepth', get_string('gcatdepth', 'managejob_gcatdelete'), $options);
        $mform->setType('gcatdepth', PARAM_INT);
        $mform->setDefault('gcatdepth', 0);
        $mform->addHelpButton('gcatdepth', 'gcatdepth', 'managejob_gcatdelete');

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
        $mform->addElement('select', 'gcathidden', get_string('gcathidden', 'managejob_gcatdelete'), $options);
        $mform->setType('gcathidden', PARAM_INT);
        $mform->setDefault('gcathidden', 0);
        $mform->addHelpButton('gcathidden', 'gcathidden', 'managejob_gcatdelete');
        
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
