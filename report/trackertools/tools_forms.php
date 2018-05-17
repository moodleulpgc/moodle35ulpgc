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
 * Forms to manage user input in report Trackertools.
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/formslib.php');

/**
 * Tools base form class
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class report_trackertools_form extends moodleform {

    public $usertypes = array();

    function get_form_parameters() {
        $cmid  = $this->_customdata['cmid'];
        $tracker = $this->_customdata['tracker'];
        $elements = array();
        tracker_loadelementsused($tracker, $elements);
        foreach($elements as $key => $element) {
            if (!$element->active) {
                unset($elements[$key]);
            }
        }
    
        $this->usertypes = array('user' => tracker_getstring('reportedby', 'tracker'),
                                'dev' => tracker_getstring('assignedto', 'tracker'),);
                                
		$this->editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => 0, 'maxbytes' => 0, 
                                        'context' => null, 'changeformat'=>0, 'noclean'=>0, 'enable_filemanagement' => false);
                                        
        return array($cmid, $tracker, $elements);
    }

    function add_hidden_elements($cmid, $action = '') {
        $mform =& $this->_form;
        
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'a', $action);
        $mform->setType('a', PARAM_ALPHA);
    }

    function add_issue_search($name = 'issuesearch') {
        $mform =& $this->_form;
    
        $disabled = '';
        $tracker = $this->_customdata['tracker'];
        if(!$fields = tracker_extractsearchcookies()) { 
            $disabled = array('disabled' => 'disabled');
        }
        
        if($name == 'issuesearch') {
            $mform->addElement('header', 'issuestooperate', get_string('issuestooperate', 'report_trackertools'));
        }

        $options = array('' => get_string('choose'),
                            REPORT_TRACKERTOOLS_ISSUES_ALL         => get_string('all', 'report_trackertools'),       
                            REPORT_TRACKERTOOLS_ISSUES_OPEN     => get_string('allopen', 'report_trackertools'),
                            REPORT_TRACKERTOOLS_ISSUES_CLOSED   => get_string('allclosed', 'report_trackertools'),);
        $select = $mform->createElement('select', $name, get_string($name, 'report_trackertools'), $options);                
        $select->addOption(get_string('search', 'report_trackertools'), REPORT_TRACKERTOOLS_ISSUES_SEARCH, $disabled);
        $mform->addElement($select);
        $mform->addHelpButton($name, $name, 'report_trackertools');
        $mform->addRule($name, null, 'required', null, 'client');
    }

    
    function get_import_export_columns() {
        $fixed = array('reportedby'=>tracker_getstring('reportedby', 'tracker'), 
                        'assignedto'=>tracker_getstring('assignedto', 'tracker'),
                        'summary'=>tracker_getstring('summary', 'tracker'),
                        'status'=>tracker_getstring('status', 'tracker'),
                        );
        $optional = array('datereported' => tracker_getstring('datereported', 'tracker'),
                            'description' => tracker_getstring('description', 'tracker'), 
                            'resolution' => tracker_getstring('resolution', 'tracker'),
                            'usermodified' => tracker_getstring('dateupdated', 'tracker'),
                            'resolvermodified' => tracker_getstring('staffupdated', 'tracker'), 
                            'userlastseen' => tracker_getstring('userlastseen', 'tracker'),
                        );
       return array($fixed, $optional);
    }


    function add_user_staff_options($field, $help, $group = 99) {
        $mform =& $this->_form;
        
        $groupname = $field.'s';

        $grouparray = array();
        foreach($this->usertypes as $key => $type) {
            $grouparray[] = $mform->createElement('advcheckbox', $field.$key, '', $type, array('group' => $group));
        }
        $group = $mform->addGroup($grouparray, $groupname, get_string($help, 'report_trackertools'), array(' '), false);
        $mform->addHelpButton($groupname, $help, 'report_trackertools');
    }
    
    function add_mail_fields() {
        $mform =& $this->_form;
        $mform->addElement('text', 'messagesubject', get_string('messagesubject', 'report_trackertools'), array('size'=>'50'));
        $mform->setDefault('messagesubject', get_string('defaultsubject', 'report_trackertools'));
        $mform->setType('messagesubject', PARAM_TEXT); 
        
        $mform->addElement('textarea', 'messagebody', get_string('messagebody', 'report_trackertools'), 'wrap="virtual" rows="4" cols="10"');
        $mform->setDefault('messagebody', get_string('defaultbody', 'report_trackertools'));
        $mform->setType('messagebody', PARAM_TEXT); 
    }
    
}


/**
 * Tracker tools export issues form class
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_trackertools_export_form extends report_trackertools_form {        
        
    /**
     * Form definition
     */
    function definition() {
        global $COURSE;
        $mform =& $this->_form;
        
        list($cmid, $tracker, $elements) = $this->get_form_parameters();

        $this->add_issue_search();
        
        $mform->addElement('header', 'exportfields', get_string('exportfields', 'report_trackertools'));
        
        list($fixed, $optional) = $this->get_import_export_columns();
        
        $grouparray = array();
            $grouparray[] = $mform->createElement('static','fixed', '', implode(', ', $fixed));  
            $grouparray[] = $mform->createElement('advcheckbox','useridnumber', '', get_string('useridnumber', 'report_trackertools'));
        $group = $mform->addGroup($grouparray, 'fixedfielsdgroup', get_string('fixedfields', 'report_trackertools'), array('  '), false);
        $mform->addHelpButton('fixedfielsdgroup', 'fixedfields', 'report_trackertools');

        $grouparray = array();
        foreach($optional as $key => $field) {
            $grouparray[] = $mform->createElement('advcheckbox', $key, ' ', $field, array('group' => 1));
        }
        $group = $mform->addGroup($grouparray, 'optionalfieldgroup', get_string('optionalfields', 'report_trackertools'), array('  '), false);
        $this->add_checkbox_controller(1);

        if($elements) {
            $grouparray = array();
            foreach($elements as $key => $element) {
                $grouparray[] = $mform->createElement('advcheckbox', 'element'.$element->name, ' ', $element->description, array('group' => 2));
            }
            $group = $mform->addGroup($grouparray, 'optionalfieldgroup2', get_string('customfields', 'report_trackertools'), array(' '), false);
            $this->add_checkbox_controller(2);
        }
        
        $this->add_user_staff_options('comment', 'exportcomments');
        /*
        $usertype = array('user' => tracker_getstring('reportedby', 'tracker'),
                            'dev' => tracker_getstring('assignedto', 'tracker'),
                            );
        $grouparray = array();
        foreach($usertype as $key => $field) {
            $grouparray[] = $mform->createElement('advcheckbox', 'comment'.$key, '', $field, array('group' => 3));
        }
        $group = $mform->addGroup($grouparray, 'comments', get_string('exportcomments', 'report_trackertools'), array(' '), false);
        $mform->addHelpButton('comments', 'exportcomments', 'report_trackertools');
        */
        
        $this->add_user_staff_options('file', 'exportfiles');
        /*
        $grouparray = array();
        foreach($usertype as $key => $field) {
            $grouparray[] = $mform->createElement('advcheckbox', 'file'.$key, '', $field, array('group' => 4));
        }
        $group = $mform->addGroup($grouparray, 'files', get_string('exportfiles', 'report_trackertools'), array(' '), false);
        $mform->addHelpButton('files', 'exportfiles', 'report_trackertools');
*/
        
        // dataformat selection
        $name = get_string('exportfileselector', 'report_trackertools');
        $mform->addElement('header', 'fileselector', $name);
        $mform->setExpanded('fileselector');

        $options = array('' => get_string('none')) + $fixed + $optional ;
        foreach($elements as $key => $element) {
            $options['element'.$element->name] = $element->description;
        }

        $name = get_string('exportsort', 'report_trackertools');
        $mform->addElement('select', 'exportsort', $name, $options);
        $mform->setDefault('exportsort', 'reportedby');
        $mform->addHelpButton('exportsort', 'exportsort', 'report_trackertools');
        
        $filename = clean_filename($COURSE->shortname.'-'.$tracker->name.'_'.tracker_getstring('issues', 'tracker')) ;
        $name = get_string('exportfilename', 'report_trackertools');
        $mform->addElement('text', 'filename', $name, array('size'=>'40'));
        $mform->setType('filename', PARAM_FILE);
        $mform->setDefault('filename', $filename);
        $mform->addRule('filename', null, 'required', null, 'client');
        
        $formats = core_plugin_manager::instance()->get_plugins_of_type('dataformat');
        $options = array();
        foreach ($formats as $format) {
            if ($format->is_enabled()) {
                $options[$format->name] = get_string('dataformat', $format->component);
            }
        }        
        $name = get_string('exportformatselector', 'report_trackertools');
        $mform->addElement('select', 'dataformat', $name, $options);

        $mform->setExpanded('issuestooperate');
        
        $this->add_hidden_elements($cmid, 'export');
        
        $this->add_action_buttons(true, get_string('export', 'report_trackertools'));
    }
}


/**
 * Tracker tools import issues form class
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_trackertools_import_form extends report_trackertools_form {        
        
    /**
     * Form definition
     */
    function definition() {
        global $COURSE;
        $mform =& $this->_form;
        
        list($cmid, $tracker, $elements) = $this->get_form_parameters();

        list($fixed, $optional) = $this->get_import_export_columns();
        foreach($elements as $key => $element) {
            if($element->mandatory && !$element->private && 
                    !(($element->type == 'file') || ($element->type == 'capcha'))) {
                $fixed['element'.$element->name] = format_string($element->description);
            } else {
                $optional['element'.$element->name] = format_string($element->description);
            }
        }
        unset($optional['usermodified']);
        unset($optional['resolvermodified']);
        unset($optional['userlastseen']);
        
        $mform->addElement('static', 'fixed', get_string('fixedfields', 'report_trackertools'),
                                                implode(', ', $fixed));
        $mform->addElement('hidden', 'fixedfields', implode(', ', $fixed));
        $mform->setType('fixedfields', PARAM_TEXT);
        $mform->addElement('static', 'optional', get_string('optionalfields', 'report_trackertools'),
                                                implode(', ', $optional));
        $mform->addElement('hidden', 'optionalfields', implode(', ', $optional));
        $mform->setType('optionalfields', PARAM_TEXT);

        $filepickeroptions = array();
        $filepickeroptions['filetypes'] = array('.csv', '.txt', 'text/plain', 'text/csv') ;
        $filepickeroptions['maxbytes'] = get_max_upload_file_size();
        $mform->addElement('filepicker', 'recordsfile', get_string('import'), null, $filepickeroptions);

        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);

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

        $mform->addElement('advcheckbox', 'ignoremodified', get_string('ignoremodified', 'report_trackertools'), get_string('ignoremodifiedexplain', 'report_trackertools'));
        $mform->addHelpButton('ignoremodified', 'ignoremodified', 'report_trackertools');
        $mform->addElement('advcheckbox', 'addoptions', get_string('addoptions', 'report_trackertools'), get_string('addoptionsexplain', 'report_trackertools'));
        $mform->addHelpButton('addoptions', 'addoptions', 'report_trackertools');
        
        $userencodings = array('id' => get_string('userid', 'report_trackertools'), 
                                'idnumber' => get_string('idnumber'),
                                'username' => get_string('username'));
        $mform->addElement('select', 'userencoding', get_string('userencoding', 'report_trackertools'), $userencodings);
        $mform->setDefault('userencoding', 'idnumber');
        $mform->addHelpButton('userencoding', 'userencoding', 'report_trackertools');

        $this->add_user_staff_options('mailto', 'importmailto');
        
        $this->add_mail_fields();
        
        
        
        $this->add_hidden_elements($cmid, 'import');
        
        $this->add_action_buttons(true, get_string('import', 'report_trackertools'));        
        
    }
}


/**
 * Tracker tools download issue files form class
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_trackertools_download_form extends report_trackertools_form {  

    /**
     * Form definition
     */
    function definition() {
        global $COURSE;
        $mform =& $this->_form;
        
        list($cmid, $tracker, $elements) = $this->get_form_parameters();

        $this->add_issue_search();

        $fieldmenu = array();
        foreach($elements as $key => $element) {
            if($element->type == 'file')
            $fieldmenu[$element->id] = $element->description;
        }
        
        $mform->addElement('header', 'downloadtype', get_string('downloadtype', 'report_trackertools'));
        
        $files = array(REPORT_TRACKERTOOLS_FILES_ALL    => get_string('allfiles', 'report_trackertools'),
                        REPORT_TRACKERTOOLS_FILES_USER    => get_string('userfiles', 'report_trackertools'),
                        REPORT_TRACKERTOOLS_FILES_DEV    => get_string('devfiles', 'report_trackertools'),
                        
        );
        
        $mform->addElement('select', 'downfield', get_string('downfield', 'report_trackertools'), $files + $fieldmenu);
        $mform->setDefault('downfield', 0);	
        $mform->addHelpButton('downfield', 'downfield', 'report_trackertools');
        
        $group = array(REPORT_TRACKERTOOLS_GROUP_NO => get_string('no'),
                        REPORT_TRACKERTOOLS_GROUP_ISSUE => get_string('zipbyissue', 'report_trackertools'),
                        REPORT_TRACKERTOOLS_GROUP_USER  => get_string('zipbyuser', 'report_trackertools'),
                        REPORT_TRACKERTOOLS_GROUP_DEV   => get_string('zipbydev', 'report_trackertools'),
                        );
        $mform->addElement('select', 'groupfield', get_string('groupfield', 'report_trackertools'), $group);
        $mform->setDefault('groupfield', 0);	
        $mform->addHelpButton('groupfield', 'groupfield', 'report_trackertools');
        $mform->disabledIf('groupfield', 'downmode', 'neq', 0);	
        
        $this->add_hidden_elements($cmid, 'download');
        
        $this->add_action_buttons(true, get_string('download', 'report_trackertools'));        
    }
}


/**
 * Tracker tools user mail options issue files form class
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_trackertools_mailoptions_form extends report_trackertools_form {  

    /**
     * Form definition
     */
    function definition() {
        global $COURSE, $SESSION, $OUTPUT;
        $mform =& $this->_form;
        
        list($cmid, $tracker, $elements) = $this->get_form_parameters();
        
        $context = context_module::instance($cmid);
        
        $preferences = array(   'open' => get_string('unsetwhenopens', 'tracker'),
                                'resolving' => get_string('unsetwhenworks', 'tracker'),
                                'waiting' => get_string('unsetwhenwaits', 'tracker'),
                                'testing' => get_string('unsetwhentesting', 'tracker'),
                                'published' => get_string('unsetwhenpublished', 'tracker'),
                                'resolved' => get_string('unsetwhenresolves', 'tracker'),
                                'abandonned' => get_string('unsetwhenthrown', 'tracker'),
        );
        
        foreach($preferences as $pref => $name) {
            if ($tracker->enabledstates & constant('ENABLED_'.strtoupper($pref))) {
                $mform->addElement('selectyesno', $pref, $name);
                $mform->setDefault($pref, 1);
            }
        }

        $mform->addElement('selectyesno', 'oncomment', get_string('unsetoncomment', 'tracker'));
        $mform->setDefault('oncomment', 1);
        
        $this->add_user_staff_options('usertype', 'usertype');
        $mform->addRule('usertypes', '', 'required', '', 'client');
        
        $mform->addElement('advcheckbox', 'forceupdate', get_string('forceupdate', 'report_trackertools'), get_string('forceupdate_explain', 'report_trackertools'));
        
        $this->add_hidden_elements($cmid, 'mailoptions');
        
        $this->add_action_buttons(true, get_string('setprefs', 'report_trackertools'));        
    }
}





/**
 * Set value on private field in Tracker 
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_trackertools_setfield_form extends report_trackertools_form {

    /**
     * Form definition
     */
    function definition() {
        $mform =& $this->_form;
        
        list($cmid, $tracker, $elements) = $this->get_form_parameters();

        $this->add_issue_search();
        
        $mform->addElement('header', 'setissuefields', get_string('setissuefields', 'report_trackertools'));
        
        $keys = tracker_get_statuskeys($tracker);
        $grouparr = array();
        $grouparr[] = $mform->createElement('select', 'status', '', $keys);
        $grouparr[] = $mform->createElement('advcheckbox', 'statusmodify', '', get_string('setmodify', 'report_trackertools')); 
        $group = $mform->addGroup($grouparr, 'statusgroup', tracker_getstring('status', 'tracker'), array(' '), false);
        $mform->setDefault('status',  POSTED);
        $mform->disabledIf('status', 'statusmodify'); 
        
        $grouparr = array();
        //$mform->addElement('editor', 'resolution_editor', tracker_getstring('resolution', 'tracker'), $this->editoroptions);
        $grouparr[] = $mform->createElement('editor', 'resolution_editor', '', $this->editoroptions);
        $grouparr[] = $mform->createElement('advcheckbox', 'resolutionmodify', '', get_string('setmodify', 'report_trackertools')); 
        $group = $mform->addGroup($grouparr, 'resolutiongroup', tracker_getstring('resolution', 'tracker'), array(' '), false);
        $mform->disabledIf('resolution_editor', 'resolutionmodify'); 
        
        $mform->addElement('header', 'setelementfields', get_string('setcustomfields', 'report_trackertools'));
        
        $fieldmenu = array();
        foreach($elements as $key => $element) {
            if($element->private) {
                $fieldmenu[$element->id] = $element->description;
            }
        }
        
        $last = end($mform->_elementIndex);
        
		if (!empty($elements)){
			foreach($elements as $element){
                if ($element->active && $element->private) {
				    $element->add_form_element($mform);
				    $newlast = end($mform->_elementIndex);
				    
				    $newelements = array_slice($mform->_elementIndex, $last + 1);
				    $grouparr = array();
				    $namesarray = array();
				    $mform->addElement('advcheckbox', $element->name.'modifyelement', '', get_string('setmodify', 'report_trackertools')); 
				    foreach($newelements as $k => $value) {
                        if(substr($k, 0, 7) == 'element' ) {
                            $mform->disabledIf($k, $element->name.'modifyelement');    
                        }
				    }
                    $last = $newlast;
                }
			}
		}

        $this->add_issue_search('confirmsearch');
        //$mform->addRule('confirmsearch', null, 'required', null, 'client');
		
        $this->add_hidden_elements($cmid, 'setfield');
        
        $this->add_action_buttons(true, get_string('setfield', 'report_trackertools'));   
       
    }
    
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        if(($data['confirmsearch'] != $data['issuesearch']) || ($data['issuesearch'] === '')) {
            $errors['confirmsearch'] = get_string('confirmsearcherror', 'report_trackertools');
        }
        return $errors;
    }
}


/**
 * Senf warning emails selected issues in Tracker 
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_trackertools_warning_form extends report_trackertools_form {

    /**
     * Form definition
     */
    function definition() {
        $mform =& $this->_form;
        
        list($cmid, $tracker, $elements) = $this->get_form_parameters();

        $this->add_issue_search();
        
        $mform->addElement('header', 'warnings', get_string('warningoptions', 'report_trackertools'));
        
        $this->add_user_staff_options('mailto', 'warningmailto');
        
        $this->add_mail_fields();
        
        $this->add_hidden_elements($cmid, 'warning');
    
        $this->add_action_buttons(true, get_string('warning', 'report_trackertools'));          
    }
}


/**
 * Issues check compliance 
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_trackertools_comply_form extends report_trackertools_form {

    /**
     * Form definition
     */
    function definition() {
        $mform =& $this->_form;
        
        list($cmid, $tracker, $elements) = $this->get_form_parameters();

        $this->add_issue_search();
        
        $mform->addElement('header', 'usercompliance', get_string('usercompliance', 'report_trackertools'));
        
        $usertypemenu = array(REPORT_TRACKERTOOLS_FILES_ALL => get_string('any', 'report_trackertools'),
                            REPORT_TRACKERTOOLS_FILES_USER  => tracker_getstring('reportedby', 'tracker'),
                            REPORT_TRACKERTOOLS_FILES_DEV   => tracker_getstring('assignedto', 'tracker'),
                            //REPORT_TRACKERTOOLS_FILES_BOTH  => tracker_getstring('bothusers', 'tracker'),
                            );
        
        $commentsgroup = array();

        $commentsmenu = array(REPORT_TRACKERTOOLS_ANY   => get_string('indifferent', 'report_trackertools'),
                            REPORT_TRACKERTOOLS_NOEMPTY => get_string('noempty', 'report_trackertools'),
                            REPORT_TRACKERTOOLS_EMPTY   => get_string('empty', 'report_trackertools'),
                            REPORT_TRACKERTOOLS_LAST    =>get_string('last', 'report_trackertools'),);
        $commentsgroup[] =& $mform->createElement('select', 'hascomments', '', $commentsmenu);
        $commentsgroup[] =& $mform->createElement('select', 'commentsby', '', $usertypemenu);
        
        $group = $mform->createElement('group', 'hascommentsgroup', get_string('hascomments', 'report_trackertools'), 
                                        $commentsgroup, ' &nbsp; '.get_string('commentsby', 'report_trackertools').' &nbsp; ', false);
        $mform->addElement($group);
        $mform->disabledIf('commentsby', 'hascomments', 'eq', '');        
        
        
        $filesgroup = array();
        $options = array(REPORT_TRACKERTOOLS_ANY    => get_string('indifferent', 'report_trackertools'),
                        REPORT_TRACKERTOOLS_NOEMPTY => get_string('noempty', 'report_trackertools'),
                        REPORT_TRACKERTOOLS_EMPTY   => get_string('empty', 'report_trackertools'),);
        
        $filesgroup[] =& $mform->createElement('select', 'hasfiles', get_string('hasfiles', 'report_trackertools'), $options);
        $filesgroup[] =& $mform->createElement('select', 'filesby', get_string('filesby', 'report_trackertools'), $usertypemenu);
        $group = $mform->createElement('group', 'hasfilesgroup', get_string('hasfiles', 'report_trackertools'), 
                                        $filesgroup, ' &nbsp; '.get_string('filesby', 'report_trackertools').' &nbsp; ', false);
        $mform->addElement($group);
        $mform->disabledIf('filesby', 'hasfiles', 'eq', '');        

        $options = array(REPORT_TRACKERTOOLS_ANY    => get_string('indifferent', 'report_trackertools'),
                        REPORT_TRACKERTOOLS_NOEMPTY => get_string('noempty', 'report_trackertools'),
                        REPORT_TRACKERTOOLS_EMPTY   => get_string('empty', 'report_trackertools'),);
        $mform->addElement('select', 'hasresolution', get_string('hasresolution', 'report_trackertools'), $options);

        

        $this->add_hidden_elements($cmid, 'comply');

        $this->add_action_buttons(true, get_string('comply', 'report_trackertools')); 
    }
}

/**
 * Issues after checking compliance form class
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_trackertools_checked_form extends report_trackertools_form {

    function add_user_name($issue, $courseid, $prefix) {
    
        $user = core_user::get_noreply_user();
        $fields = array('id', 'idnumber') + get_all_user_name_fields();
        foreach($fields as $field) {
            $user->{$field} = $issue->{$prefix.$field};
        
        }
    
        $url = new moodle_url('/user/view.php', array('id'=>$user->id,'course'=> $courseid));
        return html_writer::link($url, fullname($user, false, 'lastname')); 
    }


    /**
     * Form definition
     */
    function definition() {
        $mform =& $this->_form;
        
        list($cmid, $tracker, $elements) = $this->get_form_parameters();
        
        $mform->addElement('header', 'checked', get_string('complyissues', 'report_trackertools'));

        if(isset($this->_customdata['issues'])) {
            if($this->_customdata['issues']) {
                $i = 0;
                foreach($this->_customdata['issues'] as $iid => $issue) {
                    $text =     $tracker->ticketprefix.$issue->id.' - '. $issue->summary.' ';
                    if($issue->reportedby) {
                        $text .= '; '.tracker_getstring('reportedby', 'tracker').': '. 
                                    $this->add_user_name($issue, $tracker->course, 'su');
                    }
                    if($issue->assignedto) {
                        $text .= '; '.tracker_getstring('assignedto', 'tracker').': '.
                                    $this->add_user_name($issue, $tracker->course, 'tu');
                    }
                    
                    $mform->addElement('advcheckbox', "issues[$iid]", '', $text, array('group' => 1));
                    $i++;
                }
                $this->add_checkbox_controller(1);
            } else {
                $mform->addElement('static', 'records', '', get_string('noissues', 'report_trackertools'));
            }
        }
        
        $mform->addElement('header', 'warnings', get_string('warningoptions', 'report_trackertools'));
        
        $this->add_user_staff_options('mailto', 'warningmailto');
        
        $this->add_mail_fields();
        
        $this->add_hidden_elements($cmid, 'checked');

        $this->add_action_buttons(true, get_string('sendalert', 'report_trackertools')); 
        
    }
}


/**
 * Issues broadcasting  form class
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_trackertools_create_form extends report_trackertools_form {

    /**
     * Form definition
     */
    function definition() {
        global $USER;
        
        $mform =& $this->_form;
        
        list($cmid, $tracker, $elements) = $this->get_form_parameters();
        
        $context = context_module::instance($cmid);
        
        //$mform->addElement('header', 'create', get_string('create', 'report_trackertools'));
        
        $mform->addElement('textarea', 'users', get_string('makeforusers', 'report_trackertools'), 
                                    array('wrap'=>'virtual', 'rows'=>8, 'col'=>10) );
        $mform->addHelpButton('users', 'makeforusers', 'report_trackertools');
        $mform->setType('users', PARAM_TEXT);
        $mform->addRule('users', null, 'required', null, 'client');
        
        $fields = array('id' => get_string('userid', 'report_trackertools'),
                        'idnumber' => get_string('idnumber'),
                        'username' => get_string('username'),
                        );
                        //'fullname' => get_string('fullname'));

        $mform->addElement('select', 'ufield', get_string('userfield', 'report_trackertools'), $fields);
        $mform->setDefault('ufield', 'idnumber');
        
        $assignedto = 0;
        if (($canworkon = tracker_can_workon($tracker, $context))  && ($tracker->supportmode != 'tutoring')) {
            $assignedto = $USER->id;
        }
        $mform->addElement('hidden', 'assignedto', $assignedto);
        $mform->setType('assignedto', PARAM_INT);
        
        $mform->addElement('advcheckbox', 'sendemail', tracker_getstring('sendemail', 'tracker'));
        $mform->setDefault('sendemail', 0);     
        
        $keys = tracker_get_statuskeys($tracker);
        $mform->addElement('select', 'status', tracker_getstring('status', 'tracker'), $keys);
        $mform->setDefault('status',  '');     
        
        $mform->addElement('text', 'summary', tracker_getstring('summary', 'tracker'), array('size' => 80));
        $mform->setType('summary', PARAM_TEXT);
        $mform->addRule('summary', null, 'required', null, 'client');
        
        $mform->addElement('editor', 'description_editor', tracker_getstring('description', 'tracker'), $this->editoroptions);
        
        if (!empty($elements)){
			foreach($elements as $element){
                if ($element->active) {
				    $element->add_form_element($mform);
                }
            }
        }
        
        if ($canworkon) {
            $mform->addElement('static', '', '', '<br />');
            $mform->addElement('editor', 'resolution_editor', tracker_getstring('resolution', 'tracker'), $this->editoroptions);
        }
        
        $mform->addElement('header', 'folderselect', get_string('selectattachmentdir','report_trackertools'));

        $mform->addElement('hidden', 'submitanissue', 1);
        $mform->setType('submitanissue', PARAM_INT);

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_tracker', 'bulk_useractions');

        $dirs = array('' => get_string('none'));
        foreach ($files as $f) {
            // $f is an instance of stored_file
            if($f->is_directory()) {
                $fid = $f->get_pathnamehash(); //$f->get_id(); //get_pathnamehash();
                $path = $f->get_filepath();
                if($path != '/') {
                    $dirs[$fid] = $f->get_filepath();
                }
            }
        }

        // TODO get usersfilesdir from this tracker
        
        $mform->addElement('select', 'dir', get_string('userattachmentsdir', 'report_trackertools'), $dirs);
        $mform->setDefault('dir', '');

        $mform->addElement('text', 'prefix', get_string('fileprefix', 'report_trackertools'),'', array('size'=>6));
        $mform->setType('prefix', PARAM_ALPHANUMEXT);
        $mform->setDefault('prefix', '');
        $mform->addHelpButton('prefix', 'fileprefix', 'report_trackertools');


        $mform->addElement('text', 'suffix', get_string('filesuffix', 'report_trackertools'),'', array('size'=>6));
        $mform->setType('suffix', PARAM_ALPHANUMEXT);
        $mform->setDefault('suffix', '');
        $mform->addHelpButton('suffix', 'filesuffix', 'report_trackertools');

        $mform->addElement('text', 'ext', get_string('fileext', 'report_trackertools'),'', array('size'=>6));
        $mform->setType('ext', PARAM_ALPHANUMEXT);
        $mform->setDefault('ext', '.pdf');
        $mform->addHelpButton('ext', 'fileext', 'report_trackertools');

        $mform->addElement('advcheckbox', 'needuserfile', get_string('needuserfile', 'report_trackertools'), get_string('needuserfile_help', 'report_trackertools'));
        $mform->setType('needuserfile', PARAM_INT);
        $mform->setDefault('needuserfile', 0);
        $mform->addHelpButton('needuserfile', 'needuserfile', 'report_trackertools');
        
        
        $this->add_hidden_elements($cmid, 'create');

        $this->add_action_buttons(true, get_string('create', 'report_trackertools')); 
    }
}

/**
 * Issues broadcasting  form class
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_trackertools_make_form extends report_trackertools_form {

    function check_user_files($users, $userfield, $filepath, $contextid) {      
    
        $needuserfile = optional_param('needuserfile', 0, PARAM_INT);
        $fileprefix = optional_param('prefix', '', PARAM_FILE);
        $filesuffix = optional_param('suffix', '', PARAM_PATH);
        $fileext = optional_param('ext', '.pdf', PARAM_PATH);
        
        $fs = get_file_storage();
        
        $notfound = array();
        foreach($users as $userid => $user) {
            $middle = $user->{$userfield};
            $suffixes = explode('/', $filesuffix);
            $filexists = false;
            foreach($suffixes as $suffix) {
                $userfilename = $fileprefix.$middle.$suffix.$fileext;
                if($fs->file_exists($contextid, 'mod_tracker', 'bulk_useractions', 0, $filepath, $userfilename)) {
                    $filexists = true;
                    break;
                }
            }
            if(!$filexists) {
                $notfound[$userid] = $user->idnumber.' : '.fullname($user, false, 'lastname');
            }
        }
        
        if($notfound) {
            $notfound[] = $needuserfile ? get_string('notmakingnofile', 'report_trackertools') : get_string('makingnofile', 'report_trackertools');
        }
        
        
        return $notfound;
    }
    
    /**
     * Form definition
     */
    function definition() {
        
        $mform =& $this->_form;
        
        list($cmid, $tracker, $elements) = $this->get_form_parameters();
        
        $context = context_module::instance($cmid);
        
        if(($fromform = data_submitted()) && confirm_sesskey()) {
            foreach($fromform as $key => $value) {
                if(($key != 'submitbutton') && ($key != 'seskey') &&
                    (substr($key, 0, 5) != 'mform') && (substr($key, 0, 5) != '_qf__')) {
                    if(is_array($value)) {
                        if(substr($key, 0, 7) == 'element') {
                            foreach($value as $ik => $v) {
                                $mform->addElement('hidden', $key.'['.$ik.']', $v); 
                            }
                        } elseif($lkey = strstr($key, '_editor', true)){
                            $mform->addElement('hidden', $lkey, $value['text']);
                            $mform->addElement('hidden', $lkey.'format', $value['format']);
                        }
                    } else {
                        $mform->addElement('hidden', $key, $value);
                    }
                }
            }
            foreach($mform->_elementIndex as $key => $value) {
                $mform->setType($key, PARAM_RAW);
            }
        }

            $mform->addElement('static', 'checkexplain', '',  get_string('explainmake', 'report_trackertools')); 
        
        // check users, get indexed by user field 
        $userfield = optional_param('ufield', 'idnumber', PARAM_ALPHA);
        $userids = optional_param('users', '', PARAM_TEXT);
        list($users, $notfound) = report_trackertools_userids_from_input($userids, $userfield);
        $mform->addElement('static', 'usersfound', get_string('usersfound', 'report_trackertools'), implode(', ', array_keys($users)));
        $mform->addElement('static', 'usersnotfound', get_string('usersnotfound', 'report_trackertools'), implode(', ', $notfound));
        unset($userids);

        // check files dir exists
        $usersfilesdir = optional_param('dir', '', PARAM_FILE);
        $filepath = get_string('none');
        if($usersfilesdir) {
            $fs = get_file_storage();
            $dir = $fs->get_file_by_hash($usersfilesdir);
            $filepath = $dir->get_filepath();
        }
        $mform->addElement('static', 'userattachmentsdir', get_string('userattachmentsdir', 'report_trackertools'), $filepath);

        
        // check files do exist
        if($usersfilesdir) {
            $notfound = $this->check_user_files($users, $userfield, $filepath, $context->id);
            $mform->addElement('static', 'filesnotfound', get_string('filesnotfound', 'report_trackertools'), 
                                                                implode("<br />\n", $notfound));
        } 
        
        $mform->addElement('static', 'confirmmake', '',  get_string('confirmmake', 'report_trackertools')); 
        
        $mform->addElement('hidden', 'confirm', 1);
        $mform->setType('confirm', PARAM_INT);
        
        $this->add_hidden_elements($cmid, 'make');

        $this->add_action_buttons(true, get_string('make', 'report_trackertools'));     
    }    
}


/**
 * Issues after checking compliance form class
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_trackertools_assigntask_form extends report_trackertools_form {


    /**
     * Form definition
     */
    function get_taskstable($cmid, $trackerid) {
        global $DB, $OUTPUT;
        
        $queries = $DB->get_records('tracker_query', array('trackerid' => $trackerid));
        
        $allnames = get_all_user_name_fields(true, 'u');
        $sql = "SELECT tt.*, $allnames 
                FROM {report_trackertools_devq} tt 
                JOIN {user} u ON tt.userid = u.id 
                WHERE tt.trackerid = :trackerid 
                ORDER BY u.lastname ";
        $tasks = $DB->get_records_sql($sql, array('trackerid' => $trackerid));
        
        $table = new html_table();
        $userstr = get_string('user');
	    $searchstr = get_string('query', 'tracker');
	    $descriptionstr = tracker_getstring('description');
	    $actionstr = tracker_getstring('action', 'tracker');
	    $table->head = array("<b>$userstr</b>", "<b>$searchstr</b>", "<b>$descriptionstr</b>", "<b>$actionstr</b>");
	    $table->size = array(50, 100, 500, 100);
	    $table->align = array('left', 'left', 'center', 'center');
	    $url = new moodle_url('/report/trackertools/index.php', array('id' => $cmid, 'a' => 'deletetask'));
	    $icon = new pix_icon('t/delete', get_string('delete'));
		foreach ($tasks as $task){
	        $fields = tracker_extractsearchparametersfromdb($task->queryid);
	        $query = $queries[$task->queryid];
	        $url->param('d', $task->id);
	    	$action = $OUTPUT->action_icon($url, $icon); 
	        //$action = "<a href=\"view.php?id={$cm->id}&amp;what=editquery&amp;queryid={$query->id}\" title=\"".tracker_getstring('update')."\" ><img src=\"".$OUTPUT->pix_url('t/edit','core')."\" /></a>";
			//$action .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;what=deletequery&amp;queryid={$query->id}\" title=\"".tracker_getstring('delete')."\" ><img src=\"".$OUTPUT->pix_url('t/delete','core')."\" /></a>";
	        $table->data[] = array(fullname($task), "&nbsp;{$query->name}", format_text($query->description), $action);
		}
        
        return array($queries, html_writer::table($table));
    }

    /**
     * Form definition
     */
    function definition() {
        
        $mform =& $this->_form;
        
        list($cmid, $tracker, $elements) = $this->get_form_parameters();
        
        list ($queries, $table) = $this->get_taskstable($cmid, $tracker->id);
        
        $context = context_module::instance($cmid);
        $developers = tracker_getdevelopers($context);
        
        $querynames = array();
        foreach($queries as $query) {
            $querynames[$query->id] = $query->name;
        }

        $devnames = array();
        foreach($developers as $user) {
            $devnames[$user->id] = fullname($user);
        }
        
        $mform->addElement('header', 'checked', get_string('assignedtasks', 'report_trackertools'));

        $mform->addElement('html', $table);
        
        $mform->addElement('header', 'newassign', get_string('addassigntask', 'report_trackertools'));
        
        $mform->addElement('select', 'query', get_string('assignquery', 'report_trackertools'), $querynames);
        
        $mform->addElement('select', 'user', get_string('assignuser', 'report_trackertools'), $devnames);
        
        $this->add_hidden_elements($cmid, 'assigntasktable');

        $this->add_action_buttons(true, get_string('addassigntask', 'report_trackertools')); 
        
    }
}


/**
 * Issues after checking compliance form class
 *
 * @package    report_trackertools
 * @copyright  2017 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_trackertools_deletetask_form extends report_trackertools_form {

    /**
     * Form definition
     */
    function definition() {
        global $DB;
        
        $mform =& $this->_form;
        
        list($cmid, $tracker, $elements) = $this->get_form_parameters();
        
        if($taskid = optional_param('d', 0, PARAM_INT)) {
            $queries = $DB->get_records('tracker_query', array('trackerid' => $tracker->id));
            
            $allnames = get_all_user_name_fields(true, 'u');
            $sql = "SELECT tt.*, $allnames 
                    FROM {report_trackertools_devq} tt 
                    JOIN {user} u ON tt.userid = u.id 
                    WHERE tt.trackerid = :trackerid 
                    AND tt.id = :taskid
                    ORDER BY u.lastname ";
            $tasks = $DB->get_records_sql($sql, array('trackerid' => $tracker->id, 'taskid'=>$taskid));
            $task = reset($tasks);
            
            $a = new stdClass();
            $a->user = fullname($task);
            $a->query = $queries[$task->queryid]->name;
            $message = get_string('confirmtaskdelete_message', 'report_trackertools', $a);

            $mform->addElement('html', $message);

            $mform->addElement('hidden', 'd', $task->id);
            $mform->setType('d', PARAM_INT);
            
            $mform->addElement('hidden', 'confirm', $task->id);
            $mform->setType('confirm', PARAM_INT);

            $mform->addElement('hidden', 'query', $task->queryid);
            $mform->setType('query', PARAM_INT);
        
            $mform->addElement('hidden', 'user', $task->userid);
            $mform->setType('user', PARAM_INT);
            
        }
        
        $this->add_hidden_elements($cmid, 'deletetask');

        $this->add_action_buttons(true, get_string('deletetaskconfirmed', 'report_trackertools')); 
        
    }


}
