<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     report_datacheck
 * @category    admin
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/report/datacheck/locallib.php');


/**
 * Field check compliance form form class
 *
 * @package    report_datacheck
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 abstract class report_datacheck_form extends moodleform {

    function get_form_parameters() {
        $cmid  = $this->_customdata['cmid'];
        $dataid = $this->_customdata['dataid'];
        $groupid = $this->_customdata['groupid'];
        $fields = $this->_customdata['fields'];
    
        return array($cmid, $dataid, $groupid, $fields);
    }

    function add_hidden_elements($cmid, $action = '') {
        $mform =& $this->_form;
        
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_ALPHA);
    }
    
    function add_comparison_field($elementname, $fields) {
        $mform =& $this->_form;

        $fieldmenu = array();
        foreach($fields as $field) {
            $fieldmenu[$field->id] = $field->name;
        }

        $fieldgroup = array();
        $fieldgroup[] =& $mform->createElement('select', $elementname, '', array('0'=>get_string('any')) + $fieldmenu);
       
        $operators = array('='      => ' &nbsp;&nbsp;  &equals;  ',
                           '<>'     => ' &nbsp;&nbsp;  &NotEqual;  ', 
                           '>'      => ' &nbsp;&nbsp;  &GT;  ',
                           '<'      => ' &nbsp;&nbsp;  &LT;  ',
                           '>='     => ' &nbsp;&nbsp;  &geq;  ',
                           '<='     => ' &nbsp;&nbsp;  &leq;  ',
                           'empty'  => get_string('isempty', 'report_datacheck'),
                           'noempty'=> get_string('noempty', 'report_datacheck'),
                           'contain'=> get_string('contain', 'report_datacheck'),
                            
        );
       
        $operator = $elementname.'_operator'; 
        $fieldvalue = $elementname.'_fieldvalue'; 
       
        $fieldgroup[] =& $mform->createElement('select', $operator, '', $operators);
        
        $fieldgroup[] =& $mform->createElement('text', $fieldvalue, '', '');
        
        $group = $mform->createElement('group', $elementname.'group', get_string($elementname, 'report_datacheck'), 
                                        $fieldgroup, ' ', false);
        $mform->addElement($group);
        $mform->addHelpButton($elementname.'group', $elementname, 'report_datacheck');
        $mform->disabledIf($operator, $elementname, 'eq', 0);
        $mform->disabledIf($fieldvalue, $elementname, 'eq', 0); 
        $mform->setType($fieldvalue, PARAM_TEXT);
    }
    
    function add_approve_element() {
        $mform =& $this->_form;    

        $options = array(REPORT_DATACHECK_APPROVE_NO    => get_string('no'),
                            REPORT_DATACHECK_APPROVE_YES=> get_string('yes'),
                            REPORT_DATACHECK_APPROVE_ANY=> get_string('any'),);
        $select = $mform->addElement('select', 'approved', get_string('approved', 'report_datacheck'), $options);
        $select->setSelected(REPORT_DATACHECK_APPROVE_ANY);
        $mform->setDefault('approved', REPORT_DATACHECK_APPROVE_ANY);    
    }
    
    
    function add_groups_element($cmid, $groupid) {
        $mform =& $this->_form;   
        
        $cm = $cm = get_coursemodule_from_id('data', $cmid);
        $groups = groups_get_activity_allowed_groups($cm); 
        
        $groups = array(0 => get_string('allparticipants')) + groups_list_to_menu($groups);  
        $select = $mform->addElement('select', 'groupid', get_string('groups', 'report_datacheck'), $groups);
        $select->setSelected($groupid);
        $mform->setDefault('groupid', $groupid);    
    }
    
}


/**
 * Field check compliance form form class
 *
 * @package    report_datacheck
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_datacheck_checking_form extends report_datacheck_form {

    /**
     * Form definition
     */
    function definition() {
        $mform =& $this->_form;
        list($cmid, $dataid, $groupid, $fields) = $this->get_form_parameters();
        
        $mform->addElement('header', 'checkedfieldoptions', get_string('checkedfieldoptions', 'report_datacheck'));
        
        $fieldsmenu = array();
        foreach($fields as $field) {
            if( ($field->type == 'checkbox') || ($field->type == 'radiobutton') || 
                ($field->type == 'menu') || ($field->type == 'multimenu')) {
                $fieldsmenu[$field->id] = $field->name;
            }
        }
        
        $group = array(0 => get_string('none'),
                        REPORT_DATACHECK_CHECKBY_USER => get_string('byuser', 'report_datacheck'));
        $mform->addElement('select', 'checkby', get_string('checkby', 'report_datacheck'), $group + $fieldsmenu);
        $mform->addHelpButton('checkby', 'checkby', 'report_datacheck');
        $mform->addRule('checkby', get_string('checkbyerror', 'report_datacheck'), 'required');
        $mform->addRule('checkby', get_string('checkbyerror', 'report_datacheck'), 'nonzero', '', 'client', false);
        
        $this->add_comparison_field('checkedfield', $fields);
        
        //        $wordlimitgrprules['assignsubmission_onlinetext_wordlimit'][] = array(null, 'numeric', null, 'client');
        //$mform->addGroupRule('assignsubmission_onlinetext_wordlimit_group', $wordlimitgrprules);
        
        $rules = array();
        $rules['checkedfield'][] = array(get_string('checkedfielderror', 'report_datacheck'), 'nonzero', '', 'client', false);
        $mform->addGroupRule('checkedfieldgroup', $rules);
        $mform->addRule('checkedfieldgroup', '', 'required', '', 'client', false);

        $options = array(REPORT_DATACHECK_COMPLY_NO => get_string('noncomply', 'report_datacheck'),
                        REPORT_DATACHECK_COMPLY_YES => get_string('comply', 'report_datacheck'),
                        REPORT_DATACHECK_COMPLY_DUPS=> get_string('duplicates', 'report_datacheck'),);
        $mform->addElement('select', 'complymode', get_string('complymode', 'report_datacheck'), $options);
        $mform->addHelpButton('complymode', 'complymode', 'report_datacheck');
        
        
        $options = array('shortname'    => get_string('shortname', 'report_datacheck'),
                         'fullname'     => get_string('fullname', 'report_datacheck'),
                         'category'     => get_string('category', 'report_datacheck'),
                         'short-full'   => get_string('short-full', 'report_datacheck'),
                         'useridnumber' => get_string('useridnumber', 'report_datacheck'),
                         'userfull'     => get_string('userfull', 'report_datacheck'),
                         'userfullrev'  => get_string('userfullrev', 'report_datacheck'),);
                         
        $mform->addElement('select', 'userparsemode', get_string('userparsemode', 'report_datacheck'), $options);
        $default = get_config('report_datacheck', 'defaultparse');
        if(!$default) {
            $default = 'short-full';
        }
        $mform->setDefault('userparsemode', $default);
        $mform->disabledIf('userparsemode', 'checkby', 'in', array(-1,0)); 
        $mform->addHelpButton('userparsemode', 'userparsemode', 'report_datacheck');
  
        $mform->addElement('header', 'whatrecords', get_string('whatrecords', 'report_datacheck'));

        $this->add_comparison_field('datafield', $fields);
        
        $this->add_approve_element();
        
        if($groupid !== '') {
            $this->add_groups_element($cmid, $groupid);
        }
        
        $this->add_hidden_elements($cmid, '');
        
        $this->add_action_buttons(true, get_string('checkcompliance', 'report_datacheck'));
    }
}


/**
 * Files download options form class
 *
 * @package    report_datacheck
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_datacheck_download_form extends report_datacheck_form {

    /**
     * Form definition
     */
    function definition() {
        $mform =& $this->_form;
        
        list($cmid, $dataid, $groupid, $fields) = $this->get_form_parameters();
        $filefields = $this->_customdata['filefields'];

        $this->add_hidden_elements($cmid, 'download');
        
        if($filefields) {
            $mform->addElement('header', 'downloadtype', get_string('downloadtype', 'report_datacheck'));
            
            $files = array();
            foreach($filefields as $field) {
                $files[$field->id] = $field->name;
            }
            
            $mform->addElement('select', 'downfield', get_string('downfield', 'report_datacheck'), array(0=>get_string('allfiles', 'report_datacheck')) + $files);
            $mform->setDefault('downfield', 0);	
            $mform->addHelpButton('downfield', 'downfield', 'report_datacheck');
            
            $fieldmenu = array();
            foreach($fields as $field) {
                $fieldmenu[$field->id] = $field->name;
            }
            $group = array(0 => get_string('none'),
                            REPORT_DATACHECK_CHECKBY_USER => get_string('byuser', 'report_datacheck'));
            $mform->addElement('select', 'groupfield', get_string('groupfield', 'report_datacheck'), $group + $fieldmenu);
            $mform->setDefault('groupfield', 0);	
            $mform->addHelpButton('groupfield', 'groupfield', 'report_datacheck');
            $mform->disabledIf('groupfield', 'downmode', 'neq', 0);	
            
            $mform->addElement('header', 'whatrecords', get_string('whatrecords', 'report_datacheck'));
            
            $this->add_comparison_field('datafield', $fields);
            $this->add_approve_element();
            if($groupid !== '') {
                $this->add_groups_element($cmid, $groupid);
            }

            
            $this->add_action_buttons(true, get_string('downloadfiles', 'report_datacheck'));
        } else {
            $mform->addElement('static', 'nofiles', '', get_string('nofilefields', 'report_datacheck'));
            //$mform->addElement('cancel');
        $buttonarray=array();
              $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array('  '), false);
        }
        
    }
}


/**
 * After checking compliance form form class
 *
 * @package    report_datacheck
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_datacheck_compliance_form extends report_datacheck_form {

    /**
     * Form definition
     */
    function definition() {
        global $DB; 
        
        $mform =& $this->_form;
        
        list($cmid, $dataid, $groupid, $records) = $this->get_form_parameters();
        $fromform = $this->_customdata['formdata'];
        $comply = '';
        if(isset($fromform->complymode)) {
            $options = array(REPORT_DATACHECK_COMPLY_NO => get_string('noncomply', 'report_datacheck'),
                            REPORT_DATACHECK_COMPLY_YES => get_string('comply', 'report_datacheck'),
                            REPORT_DATACHECK_COMPLY_DUPS=> get_string('duplicates', 'report_datacheck'),);
            $comply = $options[$fromform->complymode];
        }
        
        list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'data');
        unset($cm);
        
        $users = false;
        $data = false;
        
        $mform->addElement('header', 'recordslist', get_string('recordslist', 'report_datacheck', $comply));
        
        if($records) {
            $mform->addElement('static', 'recordheader', '', get_string('checkedrecordsheader', 'report_datacheck'));
            $i = 0;

            foreach($records as $key => $record) {
                $parts = explode('-', $key);
                if(!$users && $parts[0]) {
                    $users = true;
                }
                if(!$data && $parts[1]) {
                    $data = true;
                }
                
                $text = is_object($record) ? report_datacheck_checked_record_text($record, $fromform, $dataid, $course->id) : '';
                
                $content = (isset($fromform->checkby) && ($fromform->checkby > 0)) ? $record->content : '';
            
                $mform->addElement('advcheckbox', "records[$i]", '', $text, array('group' => 1), array(0, $key.$content));
                $i++; 
            }
            $this->add_checkbox_controller(1);
        } else {
            $mform->addElement('static', 'records', '', get_string('norecords', 'report_datacheck'));
        }
        
        if($users) {
            $mform->addElement('header', 'sendmessageheader', get_string('sendmessage', 'report_datacheck'));
            
            $buttonarray=array();
                $buttonarray[] = &$mform->createElement('text', 'messagesubject', '', array('size'=>'50'));
                $buttonarray[] = &$mform->createElement('textarea', 'messagebody', '', 'wrap="virtual" rows="4" cols="10"');
                $buttonarray[] = &$mform->createElement('submit', 'sendmessage', get_string('sendmessage', 'report_datacheck'));
            $mform->addGroup($buttonarray, 'messagegroup', get_string('messagesubjectbody', 'report_datacheck'), array('  '), false);

            $mform->setDefault('messagesubject', get_string('defaultsubject', 'report_datacheck'));
            $mform->setDefault('messagebody', get_string('defaultbody', 'report_datacheck'));
            $mform->setType('messagesubject', PARAM_TEXT);
            
            $mform->setExpanded('sendmessageheader', false, true);
        }
        
        if($data) {
            $mform->addElement('header', 'setvalueheader', get_string('setvalue', 'report_datacheck'));
            
            $fields = $DB->get_records('data_fields', array('dataid'=>$dataid), 'name ASC', 'id, type, name, description');
            foreach($fields as $key => $field) {
                if(($field->type == 'file') || ($field->type == 'picture')) {
                    unset($fields[$key]);
                } else {
                    $fields[$field->id] = $field->name;
                }
            }
            
            $buttonarray=array();
                $buttonarray[] = &$mform->createElement('select', 'setfield', '', $fields);
                $buttonarray[] = &$mform->createElement('text', 'valueset', get_string('setvalue', 'report_datacheck'), array('size'=>'20'));
                $buttonarray[] = &$mform->createElement('submit', 'setvalue', get_string('setvalue', 'report_datacheck'));
            $mform->addGroup($buttonarray, 'setvaluegroup', get_string('setfield', 'report_datacheck'), array( '  &nbsp;  '.get_string('valueset', 'report_datacheck')  , '   ',), false);
            $mform->setType('valueset', PARAM_TEXT);
            
            $mform->setExpanded('setvalueheader', false, true);
        }
        /*
        $buttonarray=array();
              $buttonarray[] = &$mform->createElement('submit', 'sendmessage', get_string('sendmessage', 'report_datacheck'));
              $buttonarray[] = &$mform->createElement('submit', 'setvalue', get_string('setvalue', 'report_datacheck'));
              $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array('  '), false);
        
        */
        $this->add_hidden_elements($cmid, 'checked');
        
        $this->add_action_buttons(true, get_string('returntomod', 'report_datacheck'));

    }

}
