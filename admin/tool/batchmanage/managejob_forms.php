<?php
/**
 * Defines module selection form
 *
 * @package    tool_batchmanage
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/formslib.php');

abstract class batchmanageform extends moodleform {
    function definition_after_data() {
        global $CFG, $DB;

        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        $job = $managejob->name;
        $formsdata = $managejob->formsdata;

        $action = $this->_customdata['action'];
        
        unset($formsdata[$action]);

        $mform->addElement('hidden', 'sesskey', sesskey());
        
        $mform->addElement('hidden', 'job', $job);
        $mform->setType('job', PARAM_PLUGIN);
        
        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_ALPHANUMEXT);

        foreach($formsdata as $key => $data) {
            $mform->addElement('hidden', 'formsdata__'.$key, $data);
            $mform->setType($key, PARAM_TEXT);
        }
    }
    
    function add_grouped_element($element, $key) {
        $mform =& $this->_form;

        $group = array();
        $type = $element->getType();
        if($type == 'hidden') {
            return;
        }
        
        if($type == 'group') {
            $inners = $element->getElements();
            foreach($inners as $ikey => $inner) {
                $iname = $inner->getName();
                $this->add_grouped_element($inner, $iname);
            }
        } elseif($type == 'modgrade') {
                $innerelements = $element->getElements();
                //$element->setElements(array());
                /*
                $rp = new ReflectionProperty('MoodleQuickForm_modgrade', 'gradetypeformelement');
                $rp->setAccessible(true);
                $innerform = $rp->getValue($element);
                */
                /*
                $rc = new ReflectionClass($element);
                $properties = $rc->getProperties();
                foreach($properties as $rp) {
                    if(strpos($rp->name, 'formelement')) {
                        $rp->setAccessible(true);
                        $inner = $rp->getValue($element);
                        $iname = $inner->getName();
                        $group[$iname] = $inner;
                    }
                }*/
                foreach($innerelements as $inner) {
                    $iname = $inner->getName();
                    $group[$iname] = $inner;
                }
                
                //añadir manualmente el nuevo grupo con syu modigy
                $group[] = $mform->createElement('checkbox', $key.'modify', '', get_string('modify', 'tool_batchmanage'));
                $mform->addGroup($group, $key.'group', $element->_label, ' ', false);
                foreach($group as $ikey => $item) {
                    if($ikey) {
                        $mform->disabledIf($ikey, $key.'modify');
                        if($ikey == 'modgrade_point') {
                            $mform->setType($ikey, PARAM_RAW);
                        }
                    }
                }
                
        } else { 
            // add element to group, to be grouped below with "modify" checkbox
            $group[] = $element;
            
            
            
            $group[] = $mform->createElement('checkbox', $key.'modify', '', get_string('modify', 'tool_batchmanage'));
            $mform->addGroup($group, $key.'group', $element->_label, ' ', false);
            if(isset($element->_elements)) {
                $inners = $element->getElements();
                foreach($inners as $ikey => $inner) {
                    $iname = $inner->getName();
                    $mform->disabledIf($key.'['.$iname.']', $key.'modify');
                }
            } else {
                $mform->disabledIf($key, $key.'modify');
                $mform->setType($key, PARAM_RAW);
                $mform->setType($key.'modify', PARAM_INT);
            }
        }
    }
    
    
    /**
    * export submitted values
    *
    * @param string $elementList list of elements in form
    * @return array
    */
    function exportValues(){
        $unfiltered = array();

        // iterate over all elements, calling their exportValue() methods
        foreach (array_keys($this->_elements) as $key) {
            if ($this->_elements[$key]->isFrozen() && !$this->_elements[$key]->_persistantFreeze) {
                $varname = $this->_elements[$key]->_attributes['name'];
                $value = '';
                // If we have a default value then export it.
                if (isset($this->_defaultValues[$varname])) {
                    $value = $this->prepare_fixed_value($varname, $this->_defaultValues[$varname]);
                }
            } else {
                $value = $this->_elements[$key]->exportValue($this->_submitValues, true);
            }

            if (is_array($value)) {
                // This shit throws a bogus warning in PHP 4.3.x
                $unfiltered = HTML_QuickForm::arrayMerge($unfiltered, $value);
            }
        }
        return $unfiltered;
    }   
    
    
}


/**
 * This class define form for courses & categories selection
 *
 */
class batchmanage_courses_selector_form extends batchmanageform {
    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;

        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }

        $mform->addElement('header', 'headcoursesettings', get_string('coursesettings', 'tool_batchmanage'));

        $categories = make_categories_options();
        $catmenu = &$mform->addElement('select', 'coursecategories', get_string('coursecategories', 'tool_batchmanage'), $categories, 'size="10"');
        $catmenu->setMultiple(true);
        $mform->addRule('coursecategories', null, 'required');
        $mform->addHelpButton('coursecategories', 'coursecategories', 'tool_batchmanage');

        //$mform->addElement('static', 'categorieshelp', '', get_string('coursecategorieshelp', 'tool_batchmanage'));

        $options = array();
        $options['-1'] = get_string('all');
        $options['0'] = get_string('hidden', 'tool_batchmanage');
        $options['1'] = get_string('visible');
        $mform->addElement('select', 'visible', get_string('coursevisible', 'tool_batchmanage'), $options);
        $mform->setDefault('visible', -1);

        if(get_config('local_ulpgccore', 'version')) {
            $dbmanager = $DB->get_manager();
            if($dbmanager->field_exists('local_ulpgccore_course', 'term')) {
                $options = array();
                $options['-1'] = get_string('all');
                $options['0'] = get_string('term00', 'tool_batchmanage');
                $options['1'] = get_string('term01', 'tool_batchmanage');
                $options['2'] = get_string('term02', 'tool_batchmanage');
                $mform->addElement('select', 'term', get_string('term', 'tool_batchmanage').': ', $options);
                $mform->setDefault('term', -1);
            }

            if($dbmanager->field_exists('local_ulpgccore_course', 'credits')) { 
                $options = array();
                $options['-1'] = get_string('all');
                $options['-2'] = get_string('nonzero', 'tool_batchmanage');
                $sql = "SELECT DISTINCT credits
                                    FROM {local_ulpgccore_course} WHERE credits IS NOT NULL ORDER BY credits ASC";
                $usedvals = $DB->get_records_sql($sql);
                if($usedvals) {
                    foreach($usedvals as $key=>$value) {
                        $options["{$value->credits}"] = $value->credits;
                    }
                    $mform->addElement('select', 'credit', get_string('credit', 'tool_batchmanage').': ', $options);
                    $mform->setDefault('credit', -1);
                }
            }

            if($dbmanager->field_exists('local_ulpgccore_course', 'department')) {
                $options = array();
                $options['-1'] = get_string('all');
                $sql = "SELECT DISTINCT department
                                    FROM {local_ulpgccore_course} WHERE department IS NOT NULL ORDER BY department ASC";
                $usedvals = $DB->get_records_sql($sql);
                if($usedvals) {
                    foreach($usedvals as $key=>$value) {
                        $options["{$value->department}"] = $value->department;
                    }
                    $mform->addElement('select', 'department', get_string('department', 'tool_batchmanage').': ', $options);
                    $mform->setDefault('department', -1);
                }
            }

            if($dbmanager->field_exists('local_ulpgccore_course', 'ctype')) {
                $options = array();
                $options['all'] = get_string('all');
                $sql = "SELECT DISTINCT ctype
                                    FROM {local_ulpgccore_course} WHERE ctype IS NOT NULL ORDER BY ctype ASC";
                $usedvals = $DB->get_records_sql($sql);
                if($usedvals) {
                    foreach($usedvals as $key=>$value) {
                        $options["{$value->ctype}"] = $value->ctype;
                    }
                    $mform->addElement('select', 'ctype', get_string('ctype', 'tool_batchmanage').': ', $options);
                    $mform->setDefault('ctype', 'all');
                }
            }
        }

        $courseformats = get_plugin_list('format');
        $formcourseformats = array('all' => get_string('all'));
        foreach ($courseformats as $courseformat => $formatdir) {
            $formcourseformats[$courseformat] = get_string('pluginname', "format_$courseformat");
        }
        $mform->addElement('select', 'format', get_string('format'), $formcourseformats);
        //$mform->setHelpButton('format', array('courseformats', get_string('courseformats')), true);
        $mform->setDefault('format', 'all');

        $mform->addElement('text', 'coursetoshortnames', get_string('coursetoshortnames', 'tool_batchmanage'), array('size'=>'38'));
        $mform->setType('coursetoshortnames', PARAM_TEXT);
        $mform->setDefault('coursetoshortnames', '');
        $mform->addHelpButton('coursetoshortnames', 'coursetoshortnames', 'tool_batchmanage');

        $mform->addElement('text', 'idnumber', get_string('courseidnumber', 'tool_batchmanage'), array('size'=>'40'));
        $mform->setType('idnumber', PARAM_TEXT);
        $mform->setDefault('idnumber', '');
        $mform->addHelpButton('idnumber', 'courseidnumber', 'tool_batchmanage');

        $mform->addElement('text', 'fullname', get_string('coursefullname', 'tool_batchmanage'), array('size'=>'40'));
        $mform->setType('fullname', PARAM_TEXT);
        $mform->setDefault('idnumber', '');
        $mform->addHelpButton('fullname', 'courseidnumber', 'tool_batchmanage');
       
        $this->add_action_buttons(true, $next);
    }
}


/**
 * This class define review & confirmation form for apply mod config
 *
 */
class batchmanage_confirm_form extends batchmanageform {

    function definition() {
        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }
        
        $formsdata = $managejob->formsdata;
        foreach($formsdata as $key => $value) {
            $formclass = 'batchmanage_'.$key.'_form';
            $data = json_decode($value);
            if($key == 'courses_selector') {
                $header = get_string($key, 'tool_batchmanage');
            } else  {
                $header = get_string($key, 'managejob_'.$managejob->name);
            }
            
            $mform->addElement('header', 'header_'.$key, $header);
            $form = new $formclass(null, array('action'=>$key, 'managejob'=>$managejob));
            
            $rp = new ReflectionProperty($formclass, '_form');
            $rp->setAccessible(true);
            $innerform = $rp->getValue($form);
            $managejob->review_confirm_formsdata($mform, $key, $data, $innerform);
        }
        
        $options = array('optional'=>true);
        $mform->addElement('date_time_selector', 'scheduledtask', get_string('scheduledtask', 'tool_batchmanage'), $options);
        $mform->disabledIf('scheduledtask', 'scheduledtask[enabled]', 0);

        $this->add_action_buttons(true, $next);
    }
}
