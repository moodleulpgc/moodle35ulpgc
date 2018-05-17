<?php
/**
 * user bulk action script for batch user enrolment
 */
require_once('../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/advuserbulk/lib.php');
//require_once($CFG->dirroot.'/blocks/admin_ulpgc/lib.php');
require_once($CFG->dirroot.'/mod/tracker/lib.php');
require_once('send_tracker_form.php');

admin_externalpage_setup('tooladvuserbulk');
$systemcontext = context_system::instance();
require_capability('moodle/course:bulkmessaging', $systemcontext);

$return = $CFG->wwwroot.'/'.$CFG->admin.'/user/user_bulk.php';
if(!empty($SESSION->return_url)) {
    $return = $SESSION->return_url;
}

if (empty($SESSION->bulk_users)) {
    redirect($return);
}

$baseurl = $CFG->wwwroot.'/blocks/usermanagement/manageusers_actions/sendtracker/index.php';


function sentracker_add_userfiles($context, $issue, $usersfilesdir, $userfilenames) {
    global $DB;

    if($usersfilesdir && $userfilenames) {
    // now we do user file  storage
        $fs = get_file_storage();
        $dir = $fs->get_file_by_hash($usersfilesdir);
        $filepath = $dir->get_filepath();
        $filecontextid = $dir->get_contextid();

        $sql = "SELECT a.id
                    FROM {tracker_issueattribute} a
                    JOIN {tracker_element} e ON a.elementid = e.id AND e.type = 'file'
                    WHERE a.trackerid = ? AND a.issueid = ?  ";

        if($attributeid = $DB->get_field_sql($sql, array( $issue->trackerid,  $issue->id))) {
            $fileinfo = array(
                'contextid' => $context->id, // ID of context
                'component' => 'mod_tracker',     // usually = table name
                'filearea' => 'issueattribute',     // usually = table name
                'itemid' => $attributeid,               // usually = ID of row in table
                'filepath' => '/',           // any path beginning and ending in /
                'filename' => ''); // any filename

            foreach($userfilenames as $userfilename) {
                if($file = $fs->get_file($filecontextid, 'mod_tracker', 'bulk_useractions', 0, $filepath, $userfilename)) {
                    $fileinfo['filename'] = $userfilename;
                    $newfile = $fs->create_file_from_storedfile($fileinfo, $file);
                }
            }
        }
    }
}


/// processs form

    $sendtracker = get_config('tracker', 'sendtracker');
    if($sendtracker) {
        $trackerid = $sendtracker;
    } else {
        $trackerid = optional_param('trackerid', 0, PARAM_INT);
    }


    $modulecontext = false;
    if($trackerid) {
        if(!$tracker = $DB->get_record('tracker', array('id'=>$trackerid))) {
            error("Tracker ID is incorrect");
        }
        if(!$cm = get_coursemodule_from_instance('tracker', $tracker->id, $tracker->course)){
            error("Course Module ID was incorrect");
        }
        $modulecontext = context_module::instance($cm->id);
        require_capability('mod/tracker:report', $modulecontext);
        require_capability('mod/tracker:resolve', $modulecontext);
        if (!$course = $DB->get_record("course", array('id'=>$cm->course))) {
            error("Course is misconfigured");
        }
    }

    $confirm = optional_param('confirm', 0, PARAM_BOOL);
    $submitted = optional_param('submitanissue', 0, PARAM_BOOL);

    $usersfilesdir = optional_param('dir', '', PARAM_FILE);
    $needuserfile = optional_param('needuserfile', 0, PARAM_INT);
    $fileprefix = optional_param('prefix', '', PARAM_FILE);
    $filesuffix = optional_param('suffix', '', PARAM_PATH);
    $fileext = optional_param('ext', '.pdf', PARAM_PATH);
    $userfield = optional_param('ufield', 'idnumber', PARAM_ALPHA);

    $issue = new StdClass();
    $issue->summary = optional_param('summary', '', PARAM_TEXT);
    $issue->description = optional_param('description', '', PARAM_CLEANHTML);
    $issue->descriptionformat = optional_param('descriptionformat', '', PARAM_INT);
    $issue->resolution = optional_param('resolution', '', PARAM_CLEANHTML);
    $issue->resolutionformat = optional_param('resolutionformat', FORMAT_HTML, PARAM_INT);

    $sendmail = optional_param('sendemail', 0, PARAM_INT);

    /// Page headers
    $struseradmin = get_string('users');
    $strsendtracker = get_string('actionsendtracker','block_usermanagement');


    // print the message
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strsendtracker);

    // with confirmation, execute
    if ($confirm and !empty($tracker) and confirm_sesskey()) {


        $data = data_submitted();

        /// Insert tracker
        $issue->datereported = time();
        $issue->assignedto = $USER->id;
        $issue->bywhomid = $USER->id;
        $issue->trackerid = $trackerid;
        $issue->status = optional_param('status', POSTED, PARAM_INT);
        $issue->usermodified = $issue->datereported;
        $issue->userlastseen = 0;
        $issue->resolvermodified = $issue->datereported + 60;
        $issue->resolutionpriority = 0;

        $errors = array();
        $success = array();

        if($usersfilesdir) {
            $fs = get_file_storage();
            $dir = $fs->get_file_by_hash($usersfilesdir);
            $filepath = $dir->get_filepath();
        }

        $chunks = array_chunk($SESSION->bulk_users, 200, true);
        $names = get_all_user_name_fields(true);
        foreach($chunks as $chunk) {
            if ($rs = $DB->get_recordset_list('user', 'id', $chunk, '', 'id, username, idnumber, mailformat, '.$names)) {
                foreach($rs as $user) {
                    /// Insert tracker
                    $issue->reportedby = $user->id;
                    $filexists = false;
                    $userfilenames = array();
                    if($usersfilesdir) {
                        $middle = $user->{$userfield};
                        $suffixes = explode('/',$filesuffix);
                        foreach($suffixes as $suffix) {
                            $userfilename = $fileprefix.$middle.$suffix.$fileext;
                            if($fs->file_exists($modulecontext->id, 'mod_tracker', 'bulk_useractions', 0, $filepath, $userfilename)  ) {
                                $filexists = true;
                                $userfilenames[] = $userfilename;
                            }
                        }
                    }
                    $userfilemsg = $filexists ? '' : '  : <span class="error">'.get_string('nouserfile', 'block_usermanagement').'</span>'."  $userfilename";

                    if(!($needuserfile && $userfilemsg)) {
                        $issue->id = $DB->insert_record('tracker_issue', $issue);
                        if ($issue->id){
                            //tracker_recordelements_auto($issue, $keys, $tracker->course, $attachmentfile, $usersfilesdir, $userfilename);
                            $data->issueid = $issue->id;
                            $data->trackerid = $trackerid;
                            tracker_recordelements($issue, $data);
                            tracker_register_cc($tracker, $issue, $issue->reportedby);
                            if($userfilenames) {
                                sentracker_add_userfiles($modulecontext, $issue, $usersfilesdir, $userfilenames);
                            }

                        if($sendmail) {
                                //email_to_user
                                $from = get_string('warninguser',  'tracker');
                                $a = new StdClass;
                                $a->url = $CFG->wwwroot."/mod/tracker/view.php?id=$trackerid&amp;issueid={$issue->id}";
                                $a->code = $tracker->ticketprefix.$issue->id;
                                $text = get_string('warningemailtxt',  'tracker', $a );
                                $html = ($user->mailformat == 1) ? get_string('warningemailhtml',  'tracker', $a ) : '';
                                email_to_user($user,$from, get_string('warningsubject', 'tracker'), $text, $html);
                            }
                            $success[$user->id] = $user->idnumber.' - '.fullname($user).$userfilemsg;
                        } else {
                            $errors[$user->id] = $user->idnumber.' - '.fullname($user).$userfilemsg.' - '.get_string('inserterror','block_usermanagement', null, $baselang);
                        }
                    } else {
                        $errors[$user->id] = $user->idnumber.' - '.fullname($user).$userfilemsg;
                    }
                }
                $rs->close();
            }
        }

        if($errors) {
            echo $OUTPUT->heading(get_string('senderrors','block_usermanagement'));
            $message = implode('<br />', $errors);
            echo $OUTPUT->box($message, 'boxwidthnormal boxaligncenter error');
        }
        echo $OUTPUT->heading(get_string('sendsuccess','block_usermanagement'));
        $message = implode('<br />', $success);
        echo $OUTPUT->box($message, 'boxwidthnormal  boxaligncenter generalbox');
        echo '<br />';
        echo $OUTPUT->continue_button($return);
        echo $OUTPUT->footer();
        die;
    }

    if ($submitted && !$confirm) {

        $optionsyes = array();
        $fromform = data_submitted();

        $optionsyes = get_object_vars($fromform);
        foreach($optionsyes as $key => $value) {
            if(is_array($value)) {
                $key2 = str_replace('_editor', '', $key);
                $optionsyes[$key2] = $value['text'];
                $optionsyes[$key2.'format'] = $value['format'];
                unset($optionsyes[$key]);
            }
        }

        $optionsyes['confirm'] =  1;
        $optionsyes['sesskey'] = sesskey();
        //$optionsyes['tempfile'] = $tempfile;
        $optionsyes['dir'] = $usersfilesdir;

        $filepath = get_string('none');
         if($usersfilesdir) {
            $fs = get_file_storage();
            $dir = $fs->get_file_by_hash($usersfilesdir);
            $filepath = $dir->get_filepath();
        }
        $message = get_string('userattachmentsdir', 'block_usermanagement').":  $filepath <br />";

        $chunks = array_chunk($SESSION->bulk_users, 200, true);
        $names = get_all_user_name_fields(true);
        $userlist = array();
        foreach($chunks as $chunk) {
            $users = $DB->get_records_list('user', 'id', $chunk, 'lastname', "id, username,  idnumber, $names ");
            $userlist = $userlist + $users;
        }

        if($usersfilesdir) {
            /// TODO check here & return which users have attachment and which not
            $message .= get_string('userfilenamehelp', 'block_usermanagement').":  <br />";
            $message .= $fileprefix.get_string($userfield).$filesuffix;

            $usernames = array();
            foreach($userlist as $userid => $user) {
                $userfilemsg = '';
                $middle = $user->{$userfield};
                $suffixes = explode('/',$filesuffix);
                $filexists = false;
                foreach($suffixes as $suffix) {
                    $userfilename = $fileprefix.$middle.$suffix.$fileext;
                    if($fs->file_exists($modulecontext->id, 'mod_tracker', 'bulk_useractions', 0, $filepath, $userfilename)  ) {
                        $filexists = true;
                        break;
                    }
                }
                $userfilemsg = $filexists ? '' : '  : <span class="error">'.get_string('nouserfile', 'block_usermanagement').'</span>'."  $userfilename";
                $usernames[$userid] = $user->idnumber.' : '.fullname($user, false, 'lastname firstname').$userfilemsg;
            }
        } else {
            $message .= get_string('nouserattachmentsdir', 'block_usermanagement').":  <br />";
            foreach($userlist as $userid => $user) {
                $usernames[$userid] = $user->idnumber.' : '.fullname($user, false, 'lastname firstname');
            }
        }
        $usernames =  implode(' <br />', $usernames);

        echo $OUTPUT->heading(get_string('confirmation', 'admin'));
        echo $OUTPUT->box($message, 'boxwidthnarrow boxaligncenter generalbox', 'preview');
        $yesurl = new moodle_url('index.php', $optionsyes);
        echo $OUTPUT->confirm(get_string('confirmmessage', 'bulkusers', $usernames), $yesurl, $baseurl);
        echo $OUTPUT->footer();
        die;
    }

    // display tracker selection box
    if(has_capability('block/usermanagement:manage' , $systemcontext)) {
        echo $OUTPUT->heading(get_string('trackerselect','block_usermanagement'));

        echo $OUTPUT->box_start('messagebox notifybox');
        $conds = null;
        $nothing = array(0 => 'choosedots');
        if($trackerid) {
            $conds = array('id'=>$trackerid);
            $nothing = '';
        }
        $trackers = $DB->get_records_menu('tracker', $conds, 'name ASC', $fields='id, name');

        // ULPGC ecastro add source group menu
        echo '<div id="instanceselect"  align="center">';
        $select = new single_select(new moodle_url($baseurl, array()), 'trackerid', $trackers, $trackerid, $nothing);
        $select->label = get_string('sendingtotracker', 'block_usermanagement');
        $select->formid = 'selecttracker';
        echo $OUTPUT->render($select);
        echo '</div>';
        echo $OUTPUT->box_end();
    }

    /// display form



    if($trackerid) {
        $mform = new send_tracker_form(null, array('tracker' => $tracker, 'cmid' => $cm->id,
                                                'usersfilesdir' => $usersfilesdir,
                                                'userfield' => $userfield,
                                                'fileprefix' => $fileprefix,
                                                'filesuffix' => $filesuffix,
                                                ));
        $mform->display();
    } else {
        echo $OUTPUT->heading(get_string('noinstance', 'block_usermanagement', get_string('modulename','tracker')));
    }

    echo $OUTPUT->footer();

