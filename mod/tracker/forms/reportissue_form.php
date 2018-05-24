<?php

require_once $CFG->libdir.'/formslib.php';

class TrackerIssueForm extends moodleform{

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

		$tracker = $this->_customdata['tracker'];
		$trackerid  = $tracker->id;

		$this->context = context_module::instance($this->_customdata['cmid']);
		$maxfiles = 0;                // TODO: add some setting
		$maxbytes = 0;  //$COURSE->maxbytes; // TODO: add some setting
		$this->editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => $maxfiles, 'maxbytes' => $maxbytes, 'context' => $this->context);

		$mform = $this->_form;

        $mform->addElement('header', 'reportanissue', tracker_getstring('reportanissue','tracker'));

        $assignedto = $tracker->defaultassignee ? $tracker->defaultassignee : '';
        $reportedby = '';
        $fields = get_all_user_name_fields(true, 'u');
        if ($canworkon = tracker_can_workon($tracker, $this->context)) {
                    $users = get_users_by_capability($this->context, array('mod/tracker:report', 'mod/tracker:seeissues'), 'u.id, u.idnumber, '.$fields, 'lastname ASC, firstname ASC, idnumber ASC');
                    $names = array(''=>tracker_getstring('choose'));
                    if(!$users) {
                        $users[$USER->id] = $USER;
                    }
                    foreach($users as $user) {
                        $names[$user->id] = fullname($user, false, 'lastname');
                    }
                    $assignedto = $USER->id;

        } else {
            $names[$USER->id] = fullname($USER, false, 'lastname');
            $reportedby = $USER->id;
        }
        
        $mform->addElement('select', 'reportedby', tracker_getstring('reportedby', 'tracker'), $names);
        $mform->setDefault('reportedby', $reportedby);
        $mform->addRule('reportedby', null, 'required', null, 'client');

        if((($tracker->supportmode == 'usersupport') || 
                ($tracker->supportmode == 'boardreview') ||
                    ($tracker->supportmode == 'tutoring')) && !$tracker->defaultassignee && tracker_can_edit($tracker, $this->context)) { 
            $users = get_users_by_capability($this->context, 'mod/tracker:resolve', 'u.id, u.idnumber, '.$fields, 'lastname ASC, firstname ASC, idnumber ASC');
            $names = array(''=>tracker_getstring('choose'));
            foreach($users as $user) {
                $names[$user->id] = fullname($user, false, 'lastname');
            }
            $mform->addElement('select', 'assignedto', tracker_getstring('assignedto', 'tracker'), $names);
            $mform->setDefault('assignedto', $assignedto);
        } else {
            $mform->addElement('hidden', 'assignedto', $assignedto);
            $mform->setType('assignedto', PARAM_INT);
        }        
        
        if ($canworkon) {
            $keys = array(POSTED => tracker_getstring('posted', 'tracker'),
                            OPEN => tracker_getstring('open', 'tracker'),
                            RESOLVING => tracker_getstring('resolving', 'tracker'),
                            WAITING => tracker_getstring('waiting', 'tracker'),
                            TESTING => tracker_getstring('testing', 'tracker'),
                            RESOLVED => tracker_getstring('resolved', 'tracker'),
                            ABANDONNED => tracker_getstring('abandonned', 'tracker'),
                            TRANSFERED => tracker_getstring('transfered', 'tracker'),
                            VALIDATED => tracker_getstring('validated', 'tracker'),
                            PUBLISHED => tracker_getstring('published', 'tracker'));
            $mform->addElement('select', 'status', tracker_getstring('status', 'tracker'), $keys);
            $mform->setDefault('status',  WAITING);

            $mform->addElement('checkbox', 'sendemail', tracker_getstring('sendemail', 'tracker'));
            $mform->setDefault('sendemail', 0);
        }

		$mform->addElement('text', 'summary', tracker_getstring('summary', 'tracker'), array('size' => 80));
		$mform->setType('summary', PARAM_TEXT);
	  	$mform->addRule('summary', null, 'required', null, 'client');

		$mform->addElement('editor', 'description_editor', tracker_getstring('description', 'tracker'), $this->editoroptions);

		tracker_loadelementsused($tracker, $this->elements);

		if (!empty($this->elements)){
			foreach($this->elements as $element){
                if ($element->active && !$element->private) {
				    $element->add_form_element($mform);
                }
			}
		}

        if ($canworkon) {
            $mform->addElement('static', '', '', '<br />');
            $mform->addElement('editor', 'resolution_editor', tracker_getstring('resolution', 'tracker'), $this->editoroptions);
        }

        $mform->addElement('hidden', 'id', $this->_customdata['cmid']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'trackerid', $trackerid);
        $mform->setType('trackerid', PARAM_INT);

		$this->add_action_buttons();
	}

	function validation($data, $files = null){

	}

	function set_data($defaults){
		global $COURSE;

		$defaults->description_editor['text'] = $defaults->description;
		$defaults->description_editor['format'] = $defaults->descriptionformat;
    	$defaults = file_prepare_standard_editor($defaults, 'description', $this->editoroptions, $this->context, 'mod_tracker', 'issuedescription', $defaults->issueid);

		// something to prepare for each element ?
		if (!empty($this->elements)){
			foreach($this->elements as $element){
				$element->set_data($defaults);
			}
		}

		parent::set_data($defaults);
	}
}
