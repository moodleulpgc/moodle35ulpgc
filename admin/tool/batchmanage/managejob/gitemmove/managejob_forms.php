<?php
/**
 * Defines Module config form
 *
 * @package    managejob_gitemmove
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 global $CFG;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/admin/tool/batchmanage/managejob_forms.php');

class batchmanage_gitem_selector_form extends batchmanageform {
    
    function definition() {
        global $DB;
    
        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }
        
        $mform->addElement('header', 'headgitemsettings', get_string('gitemsettings', 'managejob_gitemmove'));
        
        $mform->addElement('text', 'gitemname', get_string('gitemname', 'managejob_gitemmove'), array('size'=>'60'));
        $mform->setDefault('gitemname', '');
        $mform->setType('gitemname', PARAM_TEXT);
        $mform->addHelpButton('gitemname', 'gitemname', 'managejob_gitemmove');

        $mform->addElement('selectyesno', 'gitemuselike', get_string('uselike', 'tool_batchmanage'));
        $mform->setDefault('gitemuselike', 0);
        $mform->addHelpButton('gitemuselike', 'uselike', 'tool_batchmanage');

        $modulemenu = array('' => get_string('any'));
        $modules = $DB->get_records('modules', array('visible' => 1), '', 'id, name');
        foreach ($modules as $module) {
            $modulemenu["$module->name"] = get_string('modulename', $module->name);
        }
        natcasesort($modulemenu);
        //array_unshift($modulemenu, get_string('any'));

        $mform->addElement('select', 'gitemmodule', get_string('modname', 'tool_batchmanage'), $modulemenu);
        $mform->setDefault('gitemmodule', '');
        $mform->setType('gitemmodule', PARAM_ALPHA);
        
        $mform->addElement('text', 'gitemidnumbers', get_string('gitemidnumbers', 'managejob_gitemmove'), array('size'=>'60'));
        $mform->setDefault('gitemidnumbers', '');
        $mform->setType('gitemidnumbers', PARAM_TEXT);
        $mform->addHelpButton('gitemidnumbers', 'gitemidnumbers', 'managejob_gitemmove');

        $mform->addElement('text', 'gitemparentname', get_string('gitemparentname', 'managejob_gitemmove'), array('size'=>'30'));
        $mform->setDefault('gitemparentname', '');
        $mform->setType('gitemparentname', PARAM_TEXT);
        $mform->addHelpButton('gitemparentname', 'gitemparentname', 'managejob_gitemmove');

        $mform->addElement('text', 'gitemparentidnumber', get_string('gitemparentidnumber', 'managejob_gitemmove'), array('size'=>'30'));
        $mform->setDefault('gitemparentidnumber', '');
        $mform->setType('gitemparentidnumber', PARAM_TEXT);
        $mform->addHelpButton('gitemparentidnumber', 'gitemparentidnumber', 'managejob_gitemmove');

        $options = array(0 => get_string('any'),
                        1 => get_string('hidden', 'managejob_gitemmove'),
                        -1 => get_string('visible'),
                        );
        $mform->addElement('select', 'gitemhidden', get_string('gitemhidden', 'managejob_gitemmove'), $options);
        $mform->setType('gitemhidden', PARAM_INT);
        $mform->setDefault('gitemhidden', 0);
        $mform->addHelpButton('gitemhidden', 'gitemhidden', 'managejob_gitemmove');

        
        $mform->addElement('advcheckbox', 'gitemnoncat', get_string('gitemnoncat', 'managejob_gitemmove'), get_string('gitemnoncat_help', 'managejob_gitemmove'));
        $mform->setType('gitemnoncat', PARAM_INT);
        $mform->setDefault('gitemnoncat', 0);
        
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

        $fields = array('gitemname', 'gitemmodule', 'gitemidnumbers', 'gitemparentname', 'gitemparentidnumber', 'gitemnoncat');
        $content = false;
        
        // Check there is any content
        foreach($fields as $field) {
            if(trim($data[$field])) {
                $content = true;
                break;
            }
        }
        
        if(!$content) {
            $errors['gitemname'] = get_string('emptyform', 'tool_batchmanage');
        }

        return $errors;
    }
    
}


class batchmanage_target_selector_form extends batchmanageform {
    
    function definition() {
    
        $mform =& $this->_form;
        $managejob = $this->_customdata['managejob'];
        if($managejob->nextmsg) {
            $next = $managejob->nextmsg;
        } else {
            $next = get_string('savechanges');
        }

        $mform->addElement('text', 'targetgcfullname', get_string('targetgcfullname', 'managejob_gitemmove'), array('size'=>'60'));
        $mform->setDefault('targetgcfullname', '');
        $mform->setType('targetgcfullname', PARAM_TEXT);
        $mform->addHelpButton('targetgcfullname', 'targetgcfullname', 'managejob_gitemmove');

        $mform->addElement('text', 'targetgitemname', get_string('targetgitemname', 'managejob_gitemmove'), array('size'=>'30'));
        $mform->setDefault('targetgitemname', '');
        $mform->setType('targetgitemname', PARAM_TEXT);
        $mform->addHelpButton('targetgitemname', 'targetgitemname', 'managejob_gitemmove');
        
        $mform->addElement('text', 'targetgitemidnumber', get_string('targetgitemidnumber', 'managejob_gitemmove'), array('size'=>'30'));
        $mform->setDefault('targetgitemidnumber', '');
        $mform->setType('targetgitemidnumber', PARAM_TEXT);
        $mform->addHelpButton('targetgitemidnumber', 'targetgitemidnumber', 'managejob_gitemmove');
        
        $options = array(0 => get_string('before', 'managejob_gitemmove'),
                         1 => get_string('after', 'managejob_gitemmove'),
                        );
        $mform->addElement('select', 'targetinsertlast', get_string('targetinsertlast', 'managejob_gitemmove'), $options);
        $mform->setType('targetinsertlast', PARAM_INT);
        $mform->setDefault('targetinsertlast', 1);
        $mform->addHelpButton('targetinsertlast', 'targetinsertlast', 'managejob_gitemmove');

        $mform->addElement('static', 'targetexplain', '', get_string('targetexplain', 'managejob_gitemmove'));
        
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

        $fields = array('targetgcfullname', 'targetgitemname', 'targetgitemidnumber');
        $content = false;
        
        // Check there is any content
        foreach($fields as $field) {
            if(trim($data[$field])) {
                $content = true;
                break;
            }
        }
        
        if(!$content) {
            $errors['targetgcfullname'] = get_string('emptyform', 'tool_batchmanage');
        }

        return $errors;
    }
    
    
}
