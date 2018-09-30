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

defined('MOODLE_INTERNAL') || die();

/**
 * @package tracker
 * @author Clifford Tham
 * @review Valery Fremaux / 1.8
 * @date 02/12/2007
 *
 * A class implementing a dropdown element
 */
require_once($CFG->dirroot.'/mod/tracker/classes/trackercategorytype/trackerelement.class.php');

class dropdownelement extends trackerelement {

    public $multiple;

    public function __construct(&$tracker, $id = null, $used = false) {
        parent::__construct($tracker, $id, $used);
        $this->setoptionsfromdb();
        // ecastro ULPGC
        $this->multiple = 0;
        if(isset($this->paramint1)) {
            $this->multiple = $this->paramint1;
        }
    }

    public function view($issueid = 0) {
        $this->getvalue($issueid); // Loads $this->value with current value for this issue.
        
        $values = explode(',', $this->value);
        
        if (isset($this->options)) {
            $optionstrs = array();
            foreach($values as $value) {
                foreach ($this->options as $option) {
                    if ($value != null) {
                        if ($value == $option->id) {
                            $optionstrs[] = format_string($option->description);
                        }
                    }
                }
            }
            return implode(',<br />', $optionstrs);
        }
        return '';
    }

    public function edit($issueid = 0, $none = false) {

        $this->getvalue($issueid);

        $values = explode(',', $this->value); // Whatever the form ... revert to an array.

        if (isset($this->options)) {
            if($none) { // ecastro ULPGC
                $selectoptions[0] = get_string('none'); 
            }
            foreach ($this->options as $optionobj) {
                $selectoptions[$optionobj->id] = format_string($optionobj->description); 
            }
            echo html_writer::select($selectoptions, 'element'.$this->name, $values, array('' => 'choosedots'));
            echo html_writer::empty_tag('br');
        }
    }

    public function add_form_element(&$mform) {

        $mform->addElement('header', "head{$this->name}", format_string($this->description));
        $mform->setExpanded("head{$this->name}");
        $optionsmenu = $this->multiple ? array() : array(''=>tracker_getstring('choose')); // ecastro ULPGC
        if (isset($this->options)) {
            foreach ($this->options as $option) {
                $optionsmenu[$option->id] = format_string($option->description);
            }

            $select = $mform->addElement('select', 'element'.$this->name, format_string($this->description), $optionsmenu);
            if (!empty($this->mandatory)) {
                $mform->addRule('element'.$this->name, null, 'required', null, 'client');
                if(!$this->multiple) {
                    $mform->addRule('element'.$this->name, null, 'nonzero', null, 'client');
                }
            }
            
			if($this->multiple){ // ecastro ULPGC
                $select->setMultiple(true); 
                $size = count($this->options);
                if($size > 15) {
                    $size = 16;
                }
                
                $select->setSize($size);  
            }
        }
    }

    public function set_data(&$defaults, $issueid = 0) {
        if ($issueid) {

            $elementname = 'element'.$this->name;

            if (!empty($this->options)) {
                $values = $this->getvalue($issueid);
                if ($multiple && is_array($values)) {
                    foreach ($values as $v) {
                        if (array_key_exists($v, $this->options)) {
                            // Check option still exists.
                            $choice[] = $v;
                        }
                        if (!empty($choice)) {
                            $defaults->$elementname = $choice;
                        }
                    }
                } else {
                    $v = $values; // single value
                    if (array_key_exists($v, $this->options)) {
                        // Check option still exists.
                        $defaults->$elementname = $v;
                    }
                }
            }
        }
    }

    public function formprocess(&$data) {
        global $DB;

        $sqlparams = array('elementid' => $this->id, 'trackerid' => $data->trackerid, 'issueid' => $data->issueid);
        if (!$attribute = $DB->get_record('tracker_issueattribute', $sqlparams)) {
            $attribute = new StdClass();
            $attribute->trackerid = $data->trackerid;
            $attribute->issueid = $data->issueid;
            $attribute->elementid = $this->id;
        }

        $elmname = 'element'.$this->name;

        if (!$this->multiple) {
            $value = optional_param($elmname, '', PARAM_TEXT);
            $attribute->elementitemid = $value;
        } else {
            $valuearr = optional_param_array($elmname, '', PARAM_TEXT);
            if (is_array($data->$elmname)) {
                $attribute->elementitemid = implode(',', $valuearr);
            } else {
                $attribute->elementitemid = $this->getvalue($attribute->issueid);
            }
        }
        
        if(!array_key_exists($attribute->elementitemid, $this->options)) {
        
            if($attribute->elementitemid === '') {
                return;
            }
        }

        $attribute->timemodified = time();

        if (!isset($attribute->id)) {
            $DB->insert_record('tracker_issueattribute', $attribute);
        } else {
            $DB->update_record('tracker_issueattribute', $attribute);
        }
        
        $this->add_autowatches(); // ecastro ULPGC
        
    }

    public function type_has_options() {
        return true;
    }
}

