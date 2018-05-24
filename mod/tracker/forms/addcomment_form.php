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

require_once($CFG->libdir.'/formslib.php');

class AddCommentForm extends moodleform {

    var $editoroptions;


    /**
     * Returns the options array to use in filemanager for forum attachments
     *
     * @param stdClass $forum
     * @return array
     */
    public function attachment_options() {
        global $COURSE, $PAGE, $CFG;
        $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes, $COURSE->maxbytes);

        $maxfiles = 1;
        $config = get_config('tracker');
        if($config->developmaxfiles && has_any_capability(array('mod/tracker:develop','mod/tracker:resolve'),  $this->context)) { // ecastro ULPGC
            $maxfiles = $config->developmaxfiles;
        } elseif($config->reportmaxfiles && has_capability('mod/tracker:report',  $this->context)) {
            $maxfiles = $config->reportmaxfiles;
        }

        return array(
            'subdirs' => 0,
            'maxbytes' => $maxbytes,
            'maxfiles' => $maxfiles,
            'accepted_types' => '*',
            'return_types' => FILE_INTERNAL
        );
    }

    function definition() {
        global $DB, $COURSE, $OUTPUT, $USER;

        $mform = $this->_form;
        $issue = $this->_customdata['issue'];
        $tracker = $this->_customdata['tracker'];

        $this->context = context_module::instance($this->_customdata['cmid']);
        $maxfiles = 0;                // TODO: add some setting   // TODO ecastro this is NOT working
        $maxbytes = 0; // $COURSE->maxbytes; // TODO: add some setting
        $this->editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => $maxfiles, 'maxbytes' => $maxbytes, 'context' => $this->context);

        $mform->addElement('static', 'issuenumber', tracker_getstring('issuenumber', 'tracker'), $tracker->ticketprefix.$issue->id   ); // issue id

        $namefields = get_all_user_name_fields(true);
        $picfields = user_picture::fields();
        $issue->reporter = $DB->get_record('user', array('id' => $issue->reportedby), "id, username, idnumber, $namefields, $picfields");
        $name = html_writer::div($OUTPUT->user_picture($issue->reporter), ' trackeruserpicture ' ) ;
        $userurl = new moodle_url('/user/view.php', array('id'=>$issue->reportedby, 'course'=>$tracker->course));
        $name .= html_writer::link($userurl,fullname($issue->reporter));
        $name .= '<br />'.tracker_getstring('idnumber').': '.$issue->reporter->idnumber;
        $mform->addElement('static', 'user', tracker_getstring('reportedby', 'tracker'), $name);
        $mform->addElement('static', 'summary', tracker_getstring('summary', 'tracker'), format_string($issue->summary));
        $mform->addElement('static', 'description', tracker_getstring('description'), format_text($issue->description));

        $select = '';
        $params =  array('trackerid' => $tracker->id, 'issueid' => $issue->id, 'userid' => $issue->reportedby); 
        if($issue->reportedby == $USER->id) {
            //the user commenting its own issue, search fr comments by others
            $select = ' trackerid = :trackerid AND issueid = :issueid AND userid != :userid ';
        } elseif(($issue->assignedto == $USER->id) || (has_capability('mod/tracker:develop', $this->context))) {
            // this is an staff user, find last comment by the user
            $select = ' trackerid = :trackerid AND issueid = :issueid AND userid = :userid ';
        }
        
        if($select) {
            if($comments = $DB->get_records_select('tracker_issuecomment', $select, $params, 'datecreated DESC', '*', 0,1)) {
                $lastcomment = reset($comments);
                $mform->addElement('static', 'lastcomment', tracker_getstring('lastcomment', 'tracker'), format_text($lastcomment->comment, $lastcomment->commentformat));
                unset($comments);
            }
        }

        $mform->addElement('editor', 'comment_editor', tracker_getstring('comment', 'tracker'), $this->editoroptions);
        $mform->addRule('comment_editor', null, 'required', null, 'client');

        $mform->addElement('filemanager', 'attachment', tracker_getstring('attachment', 'tracker'), null, $this->attachment_options());

        $mform->addElement('hidden', 'id', $this->_customdata['cmid']); // issue id
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'issueid', $issue->id); // issue id
        $mform->setType('issueid', PARAM_INT);

        // TODO   change by shop_get_role_definition TODO
        // TODO   change by shop_get_role_definition TODO
        if($tracker->supportmode == 'usersupport' || 
                $tracker->supportmode == 'boardreview' || $tracker->supportmode == 'tutoring') {
            $keys = array();
            if($tracker->supportmode == 'tutoring') {
                $keys[$issue->status] = tracker_getstring('nochange', 'tracker');
            }
            
            if($canresolve = has_any_capability(array('mod/tracker:develop', 'mod/tracker:resolve'), $this->context)) {
                $keys += array(RESOLVING => tracker_getstring('resolving', 'tracker'),
                                WAITING => tracker_getstring('waiting', 'tracker'),
                                TESTING => tracker_getstring('testing', 'tracker'));
                $default = RESOLVING;
                if($tracker->supportmode == 'tutoring') {
                    unset($keys[TESTING]);
                    $keys[TRANSFERED] = tracker_getstring('transfered', 'tracker');
                }
            } elseif(($tracker->supportmode == 'tutoring') && ($issue->reportedby == $USER->id) && 
                        (($issue->status < RESOLVING ) || ($issue->status == TESTING))) {
                        // is a student user with own issue and NOT with a tutoring plan
                $keys += array(OPEN => tracker_getstring('open', 'tracker'),
                                TESTING => tracker_getstring('testing', 'tracker'));
                $default = $issue->status;
            }
            if($keys) {
                $mform->addElement('select', 'status', tracker_getstring('status', 'tracker'), $keys);
                $mform->setDefault('status',  $default);
            }
        }

        $this->add_action_buttons(true);

    }

    function validation($data, $files = array()) {
   
    }

    function set_data($defaults) {

        $defaults->comment_editor['text'] = $defaults->comment;
        $defaults->comment_editor['format'] = $defaults->commentformat;
        $defaults = file_prepare_standard_editor($defaults, 'comment', $this->editoroptions, $this->context, 'mod_tracker', 'issuecomment', $defaults->id);

        parent::set_data($defaults);
    }
}
