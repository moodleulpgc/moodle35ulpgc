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
 * This file contains the form to define the predefined grade to set
 *
 * @package assignfeedback_copyset
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


/** Include formslib.php */
require_once ($CFG->libdir.'/formslib.php');
/** Include locallib.php */
//require_once($CFG->dirroot . '/mod/assign/locallib.php');



/**
 * assignfeedback dueextension options form
 *
 * @package assignfeedback_copyset
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_copyset_form extends moodleform  implements renderable {
    /** @var array $assignment - The data passed to this form */
    var $assignment;

    function definition (){
        global $USER;

        $mform = $this->_form;
        $assignment = $this->_customdata['assignment'];
        $action = $this->_customdata['action'];
        $confirm = $this->_customdata['confirm'];
        $users = $this->_customdata['users'];
        $this->assignment = $assignment;

        $mform->addElement('hidden', 'id', $assignment->get_course_module()->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'viewpluginpage');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'plugin', 'copyset');
        $mform->setType('plugin', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginsubtype', 'assignfeedback');
        $mform->setType('pluginsubtype', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginaction', $action);
        $mform->setType('pluginaction', PARAM_ALPHA);

        $actionstr = get_string($action, 'assignfeedback_copyset');
        if($confirm) {
            $mform->addElement('static', 'confirmmessage', '', get_string('confirmmsg', 'assignfeedback_copyset', $actionstr));
        }

        $fields = $this->add_gradingaction_elements();
        
        $course = $assignment->get_course();
        if($course->groupmode && $this->add_gradingaction_group_element()) {
            $cm = $this->assignment->get_course_module();
            $context = $this->assignment->get_context();
            $currentgroup = groups_get_activity_group($cm, true);
            $groups = groups_get_activity_allowed_groups($cm,$USER->id);
            $groupmenu = array();
            if(count($groups) > 1) {
                if($viewall = has_capability('moodle/site:accessallgroups', $context)) {
                    $groupmenu[0] = get_string('allgroups', 'assignfeedback_copyset');
                }
            }
            foreach($groups as $group) {
                $groupmenu[$group->id] = format_string($group->name);
            }
            $select = $mform->addElement('select', 'groups', get_string('groups'), $groupmenu, 'size="6"' );
            $select->setMultiple(true);
            $select->setSelected($currentgroup);
            $fields[] = 'groups';
        } else {
            $mform->addElement('hidden', 'groups', 0);
            $mform->setType('groups', PARAM_INT);
        }

        if($confirm) {
            $mform->addElement('hidden', 'confirmed', 1);
            $mform->setType('confirmed', PARAM_INT);

            foreach($fields as $field) {
                if($mform->elementExists($field)) {
                    $mform->freeze($field, 'confirmed');
                }
            }
        }

        if($confirm && $users) {
            $usernames = array();
            foreach($users as $userid => $user) {
                $param = 'users['.$userid.']';
                $mform->addElement('hidden', $param, $userid);
                $mform->setType($param, PARAM_INT);

                $usernames[] = fullname($user, false, 'lastname firstname');
            }
            if(!$usernames) {
                $usernames[] = get_string('nousersfound');
            }
            $mform->addElement('header', 'confirmations', get_string('confirmusers', 'assignfeedback_copyset'));
            $mform->setExpanded('confirmations');
            $mform->addElement('static', 'confirmusers', '', html_writer::alist($usernames));
            $mform->addElement('static', 'confirmation', '', get_string('confirmation', 'assignfeedback_copyset'));
        }

        $this->add_action_buttons(true, $actionstr);

    }

    function add_gradingaction_elements() {
        return false;
    }

    function add_gradingaction_group_element() {
        return true;
    }


}


/**
 * assignfeedback setgrades options form
 *
 * @package assignfeedback_copyset
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_copyset_setgrades_form extends assignfeedback_copyset_form  implements renderable {
    function add_gradingaction_elements() {
        $mform = $this->_form;
        $confirm = $this->_customdata['confirm'];
        $fields = array();

        $mform->addElement('header', 'general', get_string('setgrades', 'assignfeedback_copyset') );
        $gradescale = $this->assignment->get_instance()->grade;

        if($confirm) {
            $mform->addElement('hidden', 'setgrade', 0);
            $mform->setType('setgrade', PARAM_TEXT);
        }

        if ($gradescale > 0) {
            // use simple direct grading
            $gradingelement = $mform->addElement('text', 'grade', get_string('gradeoutof', 'assign',$gradescale));
            $mform->addHelpButton('grade', 'gradeoutofhelp', 'assign');
            $mform->setType('grade', PARAM_TEXT);
        } else {
            // or use grade scale
            $grademenu = make_grades_menu($gradescale);
            if (count($grademenu) > 0) {
                $gradingelement = $mform->addElement('select', 'grade', get_string('grade').':', $grademenu);
                $mform->setType('grade', PARAM_INT);
            }
        }

        $mform->addElement('header', 'general', get_string('targetassign', 'assignfeedback_copyset'));

        $options = array(''=>get_string('all', 'assignfeedback_copyset'), 'submitted'=>get_string('submitted', 'assignfeedback_copyset'), 'notsubmitted'=>get_string('notsubmitted', 'assignfeedback_copyset'), 'draft'=>get_string('draft', 'assignfeedback_copyset') );
        $mform->addElement('select', 'bysubmission', get_string('bysubmission', 'assignfeedback_copyset'), $options);
        $mform->setDefault('bysubmission', 'notsub');
        $mform->addHelpButton('bysubmission', 'bysubmission', 'assignfeedback_copyset');

        $options = array(''=>get_string('all', 'assignfeedback_copyset'), 'graded'=>get_string('graded', 'assignfeedback_copyset'), 'notgraded'=>get_string('notgraded', 'assignfeedback_copyset'));
        $mform->addElement('select', 'bygrading', get_string('bygrading', 'assignfeedback_copyset'), $options);
        $mform->setDefault('bygrading', 'notgraded');

        $options = array(''=>get_string('all', 'assignfeedback_copyset'), 'pass'=>get_string('pass', 'assignfeedback_copyset'), 'fail'=>get_string('fail', 'assignfeedback_copyset'));
        $mform->addElement('select', 'bygrade', get_string('bygrade', 'assignfeedback_copyset'), $options);
        $mform->setDefault('bygrade', '');

        $mform->addElement('advcheckbox', 'override', get_string('override', 'assignfeedback_copyset'));
        $mform->setDefault('override', '');
        $mform->setType('override', PARAM_INT);

        return array('grade', 'bysubmission', 'bygrading', 'bygrade', 'override');
        }
}


/**
 * assignfeedback copy grades options form
 *
 * @package assignfeedback_copyset
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_copyset_copygrades_form extends assignfeedback_copyset_form  implements renderable {
    function add_gradingaction_elements() {
        $mform = $this->_form;
        $fields = array();
        $instance = $this->assignment->get_instance();
        $mform->addElement('header', 'general', get_string('sourceassign', 'assignfeedback_copyset') );

        $assignments = array();
        $cms = get_coursemodules_in_course('assign', $instance->course );
        foreach ($cms as $cmo) {
            if($cmo->instance == $instance->id) {
                continue;
            }
            $assignments[$cmo->instance] = format_string($cmo->name);
        }
        $mform->addElement('select', 'source', get_string('copysource', 'assignfeedback_copyset'), $assignments);
        $mform->setType('source', PARAM_INT);

        $options = array(''=>get_string('all', 'assignfeedback_copyset'),
                            'pass'=>get_string('pass', 'assignfeedback_copyset'),
                            'fail'=>get_string('fail', 'assignfeedback_copyset'));
        $mform->addElement('select', 'byothergrade', get_string('byothergrade', 'assignfeedback_copyset'), $options);
        $mform->setDefault('byothergrade', '');

        $mform->addElement('header', 'general', get_string('targetassign', 'assignfeedback_copyset'));

        $options = array(''=>get_string('all', 'assignfeedback_copyset'), 'submitted'=>get_string('submitted', 'assignfeedback_copyset'), 'notsubmitted'=>get_string('notsubmitted', 'assignfeedback_copyset'), 'draft'=>get_string('draft', 'assignfeedback_copyset') );
        $mform->addElement('select', 'bysubmission', get_string('bysubmission', 'assignfeedback_copyset'), $options);
        $mform->setDefault('bysubmission', 'notsub');
        $mform->addHelpButton('bysubmission', 'bysubmission', 'assignfeedback_copyset');

        $options = array(''=>get_string('all', 'assignfeedback_copyset'), 'graded'=>get_string('graded', 'assignfeedback_copyset'), 'notgraded'=>get_string('notgraded', 'assignfeedback_copyset'));
        $mform->addElement('select', 'bygrading', get_string('bygrading', 'assignfeedback_copyset'), $options);
        $mform->setDefault('bygrading', 'notgraded');

        $options = array(''=>get_string('all', 'assignfeedback_copyset'), 'pass'=>get_string('pass', 'assignfeedback_copyset'), 'fail'=>get_string('fail', 'assignfeedback_copyset'));
        $mform->addElement('select', 'bygrade', get_string('bygrade', 'assignfeedback_copyset'), $options);
        $mform->setDefault('bygrade', '');


        $mform->addElement('advcheckbox', 'override', get_string('override', 'assignfeedback_copyset'));
        $mform->setDefault('override', 0);
        $mform->setType('override', PARAM_INT);

        return array('source', 'byothergrade', 'bysubmission', 'bygrading', 'bygrade', 'override'); // orl copygrades = bygrade
    }

}


/**
 * assignfeedback dueextension options form
 *
 * @package assignfeedback_copyset
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_copyset_dueextensions_form extends assignfeedback_copyset_form  implements renderable {

    function add_gradingaction_elements() {
        global $CFG, $DB;
        include_once($CFG->dirroot.'/local/ulpgccore/gradelib.php');
        
        $mform = $this->_form;
        $confirm = $this->_customdata['confirm'];
        $instance = $this->assignment->get_instance();

        $mform->addElement('header', 'general', get_string('dueextensions', 'assignfeedback_copyset') );

        $specialperiod = get_config('assignfeedback_copyset', 'tfspecialperiod');
        if ($instance->allowsubmissionsfromdate) {
            $mform->addElement('static', 'allowsubmissionsfromdate', get_string('allowsubmissionsfromdate', 'assign'), userdate($instance->allowsubmissionsfromdate));
        }
        if ($instance->duedate) {
            $mform->addElement('static', 'duedate', get_string('duedate', 'assign'), userdate($instance->duedate));
            $finaldate = $instance->duedate;
        }
        if ($instance->cutoffdate) {
            $mform->addElement('static', 'cutoffdate', get_string('cutoffdate', 'assign'), userdate($instance->cutoffdate));
            $finaldate = $instance->cutoffdate;
        }

        if($confirm) {
            $mform->addElement('hidden', 'dueextension', 0);
            $mform->setType('dueextension', PARAM_TEXT);
        }

        $mform->addElement('date_time_selector', 'timevalue', get_string('timevalue', 'assignfeedback_copyset'), array('optional'=>true));
        $mform->setDefault('timevalue', $finaldate);
        $mform->addHelpButton('timevalue', 'timevalue', 'assignfeedback_copyset');

        if($specialperiod) {
            $grade_item = $DB->get_record('grade_items', array('courseid'=>$instance->course, 'itemtype'=>'mod', 'itemmodule'=>'assign', 'iteminstance'=>$instance->id));
            $gradeaggregation =  $DB->get_field('grade_categories', 'aggregation', array('id'=>$grade_item->categoryid));
            $aggregations = explode(',', GRADE_ULPGC_AGGREGATIONS);
            if(in_array($gradeaggregation, $aggregations)) {
                $mform->addElement('selectyesno', 'specialperiod', get_string('tfspecialperiod', 'assignfeedback_copyset'));
                $mform->setDefault('specialperiod', 0);
                $mform->addHelpButton('specialperiod', 'tfspecialperiod', 'assignfeedback_copyset');
            }
            $mform->addElement('selectyesno', 'strictrule', get_string('tfstrictrule', 'assignfeedback_copyset'));
            $mform->setDefault('strictrule', 1);
            $mform->addHelpButton('strictrule', 'tfstrictrule', 'assignfeedback_copyset');
            $mform->disabledIf('strictrule', 'specialperiod', 'eq', 0);
        }
        if(!$mform->elementExists('specialperiod')) {
            $mform->addElement('hidden', 'specialperiod', 0);
            $mform->setType('specialperiod', PARAM_INT);
        }

        $mform->addElement('header', 'general', get_string('targetassign', 'assignfeedback_copyset'));

        $options = array(''=>get_string('all', 'assignfeedback_copyset'), 'submitted'=>get_string('submitted', 'assignfeedback_copyset'), 'notsubmitted'=>get_string('notsubmitted', 'assignfeedback_copyset'), 'draft'=>get_string('draft', 'assignfeedback_copyset') );
        $mform->addElement('select', 'bysubmission', get_string('bysubmission', 'assignfeedback_copyset'), $options);
        $mform->setDefault('bysubmission', 'notsub');
        $mform->addHelpButton('bysubmission', 'bysubmission', 'assignfeedback_copyset');
        $mform->disabledIf('bysubmission', 'specialperiod', 'eq', 1);

        $options = array(''=>get_string('all', 'assignfeedback_copyset'), 'graded'=>get_string('graded', 'assignfeedback_copyset'), 'notgraded'=>get_string('notgraded', 'assignfeedback_copyset'));
        $mform->addElement('select', 'bygrading', get_string('bygrading', 'assignfeedback_copyset'), $options);
        $mform->setDefault('bygrading', 'notgraded');
        $mform->addHelpButton('bygrading', 'bygrading', 'assignfeedback_copyset');
        $mform->disabledIf('bygrading', 'specialperiod', 'eq', 1);

        $options = array(''=>get_string('all', 'assignfeedback_copyset'), 'pass'=>get_string('pass', 'assignfeedback_copyset'), 'fail'=>get_string('fail', 'assignfeedback_copyset'));
        $mform->addElement('select', 'bygrade', get_string('bygrade', 'assignfeedback_copyset'), $options);
        $mform->setDefault('bygrade', '');
        $mform->addHelpButton('bygrade', 'bygrade', 'assignfeedback_copyset');
        $mform->disabledIf('bygrade', 'specialperiod', 'eq', 1);

        return array('specialperiod', 'timevalue', 'bysubmission', 'bygrading', 'bygrade');
    }

    /**
     * Perform validation on the extension form
     * @param array $data
     * @param array $files
     */
    function validation($data, $files) {
        $instance = $this->assignment->get_instance();
        $errors = parent::validation($data, $files);
        if ($instance->duedate && $data['timevalue']) {
            if ($instance->duedate > $data['timevalue']) {
                $errors['extensionduedate'] = get_string('extensionnotafterduedate', 'assign');
            }
        }
        if ($instance->allowsubmissionsfromdate && $data['timevalue']) {
            if ($instance->allowsubmissionsfromdate > $data['timevalue']) {
                $errors['extensionduedate'] = get_string('extensionnotafterfromdate', 'assign');
            }
        }
        if ($instance->cutoffdate && $data['timevalue']) {
            if ($instance->cutoffdate > $data['timevalue']) {
                $errors['extensionduedate'] = get_string('extensionnotaftercutoffdate', 'assign');
            }
        }

        return $errors;
    }

}


/**
 * assignfeedback copy grades options form
 *
 * @package assignfeedback_copyset
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_copyset_randommarkers_form extends assignfeedback_copyset_form  implements renderable {
    function add_gradingaction_elements() {
        $mform = $this->_form;
        $confirm = $this->_customdata['confirm'];
        $instance = $this->assignment->get_instance();
        $cm = $this->assignment->get_course_module();

        $mform->addElement('header', 'general', get_string('randommarkers', 'assignfeedback_copyset') );

        $mform->addElement('advcheckbox', 'removemarkers', get_string('removemarkers', 'assignfeedback_copyset'));
        $mform->setType('removemarkers', PARAM_INT);
        $mform->setDefault('removemarkers', 0);

        $mform->addElement('header', 'general', get_string('targetassign', 'assignfeedback_copyset'));

        $states = array(ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED => get_string('markingworkflowstatenotmarked', 'assign'));
        $states = $states + $this->assignment->get_marking_workflow_states_for_current_user();

        $select = $mform->addElement('select', 'bywstate', get_string('bywstate', 'assignfeedback_copyset'), $states);
        $select->setMultiple(true);
        $select->setSelected(ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED);
        $mform->addHelpButton('bywstate', 'bywstate', 'assignfeedback_copyset');

        return array('removemarkers', 'bywstate'); // 
    }
}


/**
 * assignfeedback copy grades options form
 *
 * @package assignfeedback_copyset
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_copyset_importmarkers_form extends assignfeedback_copyset_form  implements renderable {

    function add_gradingaction_group_element() {
        return false;
    }

    function add_gradingaction_elements() {
        $mform = $this->_form;
        $confirm = $this->_customdata['confirm'];
        $instance = $this->assignment->get_instance();
        $cm = $this->assignment->get_course_module();

        $mform->addElement('header', 'general', get_string('importmarkers', 'assignfeedback_copyset') );

        if($confirm) {
            $fields = $this->add_import_confirm_elements();
        } else {
            $fields = $this->add_import_fromfile_elements();
        }
        return $fields;
    }

    function add_import_fromfile_elements() {
        $mform = $this->_form;
        
        $fileoptions = array('subdirs'=>0,
                                'maxbytes'=>$this->assignment->get_course()->maxbytes,
                                'accepted_types'=>'csv,txt',
                                'maxfiles'=>1,
                                'return_types'=>FILE_INTERNAL);

        $mform->addElement('filepicker', 'markersfile', get_string('uploadafile'), null, $fileoptions);
        $mform->addRule('markersfile', get_string('uploadnofilefound'), 'required', null, 'client');
        $mform->addHelpButton('markersfile', 'markersfile', 'assignfeedback_copyset');
        
        $a = new stdClass;
        $a->user = core_text::strtolower(get_string('user'));
        $a->marker = core_text::strtolower(get_string('marker', 'assign'));
        $mform->addElement('static', 'explain1', '', get_string('headercolumns', 'assignfeedback_copyset', $a));
        

        $encodings = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'grades'), $encodings);
        $mform->addHelpButton('encoding', 'encoding', 'grades');

        $radio = array();
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('septab', 'grades'), 'tab');
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepcomma', 'grades'), 'comma');
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepcolon', 'grades'), 'colon');
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepsemicolon', 'grades'), 'semicolon');
        $mform->addGroup($radio, 'separator', get_string('separator', 'grades'), ' ', false);
        $mform->addHelpButton('separator', 'separator', 'grades');
        $mform->setDefault('separator', 'comma');
        
        $mform->addElement('advcheckbox', 'removemarkers', get_string('removeexisting', 'assignfeedback_copyset'));
        $mform->addHelpButton('removemarkers', 'removeexisting', 'assignfeedback_copyset');
        $mform->setType('removemarkers', PARAM_INT);
        $mform->setDefault('removemarkers', 1);

        return array('markersfile', 'encoding', 'separator', 'removemarkers');
    }
    
    function add_import_confirm_elements() {
        $mform = $this->_form;
        $importdata = $this->_customdata['import'];
        
        if(!$importdata) {
            // if we are confingmibg and not importadata, mean is confirmed
            // we only need the names for retrieving, data is not to be use here
            $mform->addElement('hidden', 'importid', 0);
            $mform->setType('importid', PARAM_INT);
            $mform->addElement('hidden', 'encoding', 'a');
            $mform->setType('encoding', PARAM_ALPHANUMEXT);
            $mform->addElement('hidden', 'separator', 'a');
            $mform->setType('separator', PARAM_ALPHA);
            $mform->addElement('hidden', 'draftid', 0);
            $mform->setType('draftid', PARAM_INT);
            $mform->addElement('hidden', 'removemarkers', 0);
            $mform->setType('removemarkers', PARAM_INT);
        } else {
            $csvdata = $importdata->csvdata;
            $gradeimporter = $importdata->gradeimporter;

            if ($csvdata) {
                $gradeimporter->parsecsv($csvdata);
            }

            if (!$gradeimporter->init()) {
                $thisurl = new moodle_url('/mod/assign/view.php', array('action'=>'viewpluginpage',
                                                                        'pluginsubtype'=>'assignfeedback',
                                                                        'plugin'=>'copyset',
                                                                        'pluginaction'=>'importmarkers',
                                                                        'id'=>$this->assignment->get_course_module()->id));
                $struser = core_text::strtolower(get_string('user'));
                $strmarker = core_text::strtolower(get_string('marker', 'assign'));                                             
                $a = $struser. ' '. $strmarker;
                print_error('invalidimporter', 'assignfeedback_copyset', $thisurl, $a);
                return;
            }
            
            $skip = array();
            $valid = 0;
            while ($record = $gradeimporter->next()) {
                if(isset($record->skip)) {
                    $skip[] = $record->skip;
                } else {
                    $valid++;
                }
            }
            $gradeimporter->close(false);

            $mform->addElement('static', 'validmarkersassigns', get_string('validmarkersassigns', 'assignfeedback_copyset'), $valid);

            if($skip) {
                $skip = html_writer::alist($skip, array('class'=>'nonvalidmarkersassigns'));
                $mform->addElement('static', 'nonvalidmarkersassigns', get_string('nonvalidmarkersassigns', 'assignfeedback_copyset'), $skip);
            }
            
            $mform->addElement('hidden', 'importid', $gradeimporter->importid);
            $mform->setType('importid', PARAM_INT);

            $mform->addElement('hidden', 'encoding', $gradeimporter->get_encoding());
            $mform->setType('encoding', PARAM_ALPHANUMEXT);
            $mform->addElement('hidden', 'separator', $gradeimporter->get_separator());
            $mform->setType('separator', PARAM_ALPHA);
            $mform->addElement('hidden', 'draftid', $importdata->draftid);
            $mform->setType('draftid', PARAM_INT);
            $mform->addElement('hidden', 'removemarkers', $importdata->removemarkers);
            $mform->setType('removemarkers', PARAM_INT);
        }

        return array('validmarkersassigns');
    }
}
