<?php

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/tracker/forms/reportissue_form.php');

class send_tracker_form extends TrackerIssueForm {

	var $elements;
	var $editoroptions;
	var $context;

	/**
	* Dynamically defines the form using elements setup in tracker instance
	*
	*
	*/
	function definition(){
		global $DB, $COURSE, $USER;

        $mform = $this->_form;

        $cmid =  $this->_customdata['cmid'];
        $usersfilesdir = $this->_customdata['usersfilesdir'];
        $userfield = $this->_customdata['userfield'];
        $fileprefix =  $this->_customdata['fileprefix'];
        $filesuffix =  $this->_customdata['filesuffix'];

        $context = context_module::instance($cmid);

        $mform->addElement('header', 'folderselect', get_string('selectattachmentdir','block_usermanagement'));

        $mform->addElement('hidden', 'submitanissue', 1);
        $mform->setType('submitanissue', PARAM_INT);

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_tracker', 'bulk_useractions');

        $dirs = array();
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

        $mform->addElement('select', 'dir', get_string('userattachmentsdir', 'block_usermanagement'), $dirs);
        $mform->setDefault('dir', $usersfilesdir);

        $mform->addElement('text', 'prefix', get_string('fileprefix', 'block_usermanagement'),'', array('size'=>6));
        $mform->setType('prefix', PARAM_ALPHANUMEXT);
        $mform->setDefault('prefix', $fileprefix);
        $mform->addHelpButton('prefix', 'fileprefix', 'block_usermanagement');

        $fields = array('userid' => get_string('userid', 'block_usermanagement'),
                        'idnumber' => get_string('idnumber'),
                        'username' => get_string('username'),
                        );
                        //'fullname' => get_string('fullname'));

        $mform->addElement('select', 'ufield', get_string('userfield', 'block_usermanagement'), $fields, $userfield);
        $mform->setDefault('ufield', $userfield);

        $mform->addElement('text', 'suffix', get_string('filesuffix', 'block_usermanagement'),'', array('size'=>6));
        $mform->setType('suffix', PARAM_ALPHANUMEXT);
        $mform->setDefault('suffix', $filesuffix);
        $mform->addHelpButton('suffix', 'filesuffix', 'block_usermanagement');

        $mform->addElement('text', 'ext', get_string('fileext', 'block_usermanagement'),'', array('size'=>6));
        $mform->setType('ext', PARAM_ALPHANUMEXT);
        $mform->setDefault('ext', '.pdf');
        $mform->addHelpButton('ext', 'fileext', 'block_usermanagement');

        $mform->addElement('checkbox', 'needuserfile', get_string('needuserfile', 'block_usermanagement'), get_string('needuserfile_help', 'block_usermanagement'));
        $mform->setType('needuserfile', PARAM_INT);
        $mform->setDefault('needuserfile', 0);
        $mform->addHelpButton('needuserfile', 'needuserfile', 'block_usermanagement');

		parent::definition();
	}


    function definition_after_data() {
        $mform    =& $this->_form;

        //$element2 = $mform->createElement('editor', 'description_sendtracker', get_string('description'), array('rows'=>2), $this->editoroptions);
        //$mform->insertElementBefore($element2, 'description_editor');
        //$mform->setDefault('description_sendtracker', array('text'=>'texto inicial '));

        //$mform->insertElementBefore($mform->removeElement('description_editor', false), 'description_sendtracker');

        if($mform->elementExists('reportedby')) {
            $mform->removeElement('reportedby');
        }

        if($mform->elementExists('description_editor')) {
             $mform->addRule('description_editor', null, 'required', '', 'client');
        }

    }
}