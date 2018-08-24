<?php
/**
 * Defines course config form
 *
 * @package    tool_batchmanage
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 global $CFG;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/admin/tool/batchmanage/managejob_forms.php');


/**
 * This class copies form for module configuration options
 *
 */
class batchmanage_gcat_selector_form extends batchmanageform {
    function definition() {
        global $CFG, $COURSE, $DB;

        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }

        $mform->addElement('header', 'applygcatsettings', get_string('applygcatsettings', 'managejob_gcatadd'));

        $mform->addElement('textarea', 'gcattemplate', get_string('gcattemplate', 'managejob_gcatadd'), 
                                    array('wrap'=>'virtual', 'rows'=>6, 'cols'=>20));
        $mform->addHelpButton('gcattemplate', 'gcattemplate', 'managejob_gcatadd');
        $header = array('fullname' => get_string('categoryname', 'grades'),
                        'grade_item_itemname' => get_string('categorytotalname', 'grades'),
                        'grade_item_iteminfo' => get_string('iteminfo', 'grades'),
                        'grade_item_idnumber' => get_string('idnumbermod'),
                        'aggregation'         => get_string('aggregation', 'grades'),
                        'parentcategory'      => get_string('parentcategory', 'grades'),
                        );
        
        $mform->setDefault('gcattemplate', implode(', ', $header)."\n");
        $mform->setType('gcattemplate', PARAM_TEXT);
        $mform->addRule('gcattemplate', null, 'required', null, 'client');
        
        
        if($aggs = grade_helper::get_aggregation_strings()) {
            natcasesort($aggs);
            foreach($aggs as $key => $value) {
                $aggs[$key] = "$value \t:\t $key";
            }
            $aggs = implode("<br />\n", $aggs);
        }
        $mform->addElement('static', 'explain1', '', get_string('templateexplain', 'managejob_gcatadd', $aggs));
        
        $oldcourseid = $COURSE->id;
        $COURSE->id = $DB->get_field('course', 'id', array('shortname'=>get_config('managejob_gcatadd', 'referencecourse')));
        require_once($CFG->dirroot.'/grade/edit/tree/category_form.php');
        $gpr = new grade_plugin_return();
        $categoryform = new edit_category_form(null, array('current'=>0, 'gpr'=>$gpr));
        $rp = new ReflectionProperty($categoryform, '_form');
        $rp->setAccessible(true);
        $innerform = $rp->getValue($categoryform);
        $COURSE->id = $oldcourseid;

        $fields = array('aggregation', 'aggregateonlygraded', 'keephigh', 'droplow',
                        'grade_item_gradetype', 'grade_item_scaleid', 'grade_item_rescalegrades', 
                        'grade_item_grademax', 'grade_item_grademin', 'grade_item_gradepass',
                        'grade_item_display', 'grade_item_decimals');
        
        foreach($fields as $key) {
            if($innerform->elementExists($key)) {  
                $element = $innerform->getElement($key);
                $type = $element->getType();
                $element->setName('gcat'.$key);
                $mform->addElement($element);
                if($type == 'text') {
                    $mform->setType('gcat'.$key, PARAM_TEXT);
                }
            }
        }
        
        $mform->addElement('text', 'gcatparentcategory', get_string('parentcategory', 'grades'), array('size'=>40));
        $mform->addHelpButton('gcatparentcategory', 'parentcategory', 'managejob_gcatadd');
        $mform->setDefault('gcatparentcategory', '');
        $mform->setType('gcatparentcategory', PARAM_TEXT);

        $options = array(1 => get_string('before', 'managejob_gcatadd'),
                         0 => get_string('after', 'managejob_gcatadd'),
                        );
        $mform->addElement('select', 'gcatinsertfirst', get_string('gcatinsertfirst', 'managejob_gcatadd'), $options);
        $mform->setType('gcatinsertfirst', PARAM_INT);
        $mform->setDefault('gcatinsertfirst', 0);
        $mform->addHelpButton('gcatinsertfirst', 'gcatinsertfirst', 'managejob_gcatadd');
        
        
        
        $this->add_action_buttons(true, $next);
        
        $courseid = 0;
        $grade_category = new grade_category(array('courseid'=>$courseid), false);
        $grade_category->apply_default_settings();
        $grade_category->apply_forced_settings();

        $category = $grade_category->get_record_data();

        $grade_item = new grade_item(array('courseid'=>$courseid, 'itemtype'=>'manual'), false);
        foreach ($grade_item->get_record_data() as $key => $value) {
            $category->{"grade_item_$key"} = $value;
        }
        $category = get_object_vars($category);
        foreach($category as $key => $value) {
            unset($category[$key]);
            $category["gcat$key"] = $value;
        }

        $this->set_data($category);
        
    }

}




