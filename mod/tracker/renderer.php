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
 * @package mod_tracker
 * @category mod
 * @author Clifford Tham, Valery Fremaux > 1.8
 * @date 02/12/2007
 */

/**
 * the master renderer
 */
class mod_tracker_renderer extends plugin_renderer_base {

    function core_issue($issue, $tracker) {
        global $CFG, $COURSE, $DB, $OUTPUT, $USER, $STATUSCODES, $STATUSKEYS;

        $str = '';
    
        $str .= '<tr valign="top">';
        $str .= '<td colspan="4" align="left" class="tracker-issue-summary">';
        $str .= format_string($issue->summary);
        $str .= '</td>';
        $str .= '</tr>';

        $link = '';
        if ($issue->downlink) {
            $access = true;
            list($hostid, $instanceid, $issueid) = explode(':', $issue->downlink);
            if (!$hostid || $hostid == $CFG->mnet_localhost_id) {
                $cm = get_coursemodule_from_instance('tracker', $instanceid);
                if ($cm && $DB->record_exists('tracker_issue', array('id' => $issueid))) {
                    $params = array('id' => $cm->id, 'view' => 'view', 'screen' => 'viewanissue', 'issueid' => $issueid);
                    $url = new moodle_url('/mod/tracker/view.php', $params);
                    $context = context_module::instance($cm->id);
                    if (has_capability('mod/tracker:seeissues', $context)) {
                        $link = html_writer::link($url, tracker_getstring('gotooriginal', 'tracker'));
                    } else {
                        $link = tracker_getstring('originalticketnoaccess', 'tracker');
                    }
                } else {
                    // Local downstream tracker is gone, or downstream issue was deleted.
                    // Revert to an unbound situation.
                    $DB->set_field('tracker_issue', 'downlink', '', array('id' => $issue->id));
                }
            } else {
                $host = $DB->get_record('mnet_host', array('id' => $hostid));

                // This is optional, in case useing block User_Mnet_Host based access restrictions.
                if (file_exists($CFG->dirroot.'/blocks/user_mnet_hosts/xlib.php')) {
                    include_once($CFG->dirroot.'/blocks/user_mnet_hosts/xlib.php');
                    $access = user_mnet_hosts_read_access($USER, $host->wwwroot);
                }

                if ($access) {
                    $params = array('t' => $instanceid, 'view' => 'view', 'screen' => 'viewanissue', 'issueid' => $issueid);
                    $remoteurl = new moodle_url('/mod/tracker/view.php', $params);
                    $mnetauth = is_enabled_auth('multimnet') ? 'multimnet' : 'mnet';
                    $url = new moodle_url('/auth/'.$mnetauth.'/jump.php', array('hostwwwroot' => $host->wwwroot, 'wantsurl' => $remoteurl));
                    $link = html_writer::link($url, tracker_getstring('gotooriginal', 'tracker'));
                } else {
                    $link = tracker_getstring('originalticketnoaccess', 'tracker');
                }
            }

            if (!empty($link)) {
                $str .= '<tr valign="top">';
                $str .= '<td colspan="4" align="left" class="tracker-issue-downlink">';
                $str .= $link;
                $str .= '</td>';
                $str .= '</tr>';
            }
        }

        if ($issue->uplink) {
            list($hostid, $instanceid, $issueid) = explode(':', $issue->uplink);

            $access = true;
            $link = '';
            if (!$hostid || $hostid == $CFG->mnet_localhost_id) {
                // Local parent tracker.
                $cm = get_coursemodule_from_instance('tracker', $instanceid);
                if ($cm && $DB->record_exists('tracker_issue', array('id' => $issueid))) {
                    $params = array('id' => $cm->id, 'view' => 'view', 'screen' => 'viewanissue', 'issueid' => $issueid);
                    $url = new moodle_url('/mod/tracker/view.php', $params);
    
                    $context = context_module::instance($cm->id);
                    if (has_capability('mod/tracker:seeissues', $context)) {
                        $link = html_writer::link($url, tracker_getstring('gototransfered', 'tracker'));
                    } else {
                        $link = tracker_getstring('transferedticketnoaccess', 'tracker');
                    }
                } else {
                    // Either upstream tracker has been deleted or upstream issue has been deleted. 
                    // Than revert uplink and go back to open state
                    $issue->uplink = '';
                    $issue->status = OPEN;
                    $DB->update_record('tracker_issue', $issue);
                }
            } else {
                $host = $DB->get_record('mnet_host', array('id' => $hostid));

                if (file_exists($CFG->dirroot.'/blocks/user_mnet_hosts/xlib.php')) {
                    include_once($CFG->dirroot.'/blocks/user_mnet_hosts/xlib.php');
                    $access = user_mnet_hosts_read_access($USER, $host->wwwroot);
                }
                if ($access) {
                    $params = array('t' => $instanceid, 'view' => 'view', 'screen' => 'viewanissue', 'issueid' => $issueid);
                    $remoteurl = new moodle_url('/mod/tracker/view.php', $params);
                    $mnetauth = is_enabled_auth('multimnet') ? 'multimnet' : 'mnet';
                    $url = new moodle_url('/auth/'.$mnetauth.'/jump.php', array('hostwwwroot' => $host->wwwroot, 'wantsurl' => $remoteurl));
                    $link = html_writer::link($url, tracker_getstring('gototransfered', 'tracker'));
                } else {
                    $link = tracker_getstring('transferedticketnoaccess', 'tracker');
                }
            }

            if (!empty($link)) {
                $str .= '<tr valign="top">';
                $str .= '<td colspan="4" align="left" class="tracker-issue-downlink">';
                $str .= $link;
                $str .= '</td>';
                $str .= '</tr>';
            }
        }

        $str .= '<tr valign="top">';
        $str .= '<td align="right" width="25%" class="tracker-issue-param">';
        $str .= '<b>'.tracker_getstring('issuenumber', 'tracker').'</b><br />';
        $str .= '</td>';
        $str .= '<td width="25%" class="tracker-issue-value">';
        $str .= $tracker->ticketprefix.$issue->id;
        $str .= '</td>';
        $str .= '<td align="right" width="25%" class="tracker-issue-param" >';
        $str .= '<b>'.tracker_getstring('status', 'tracker').':</b>';
        $str .= '</td>';
        $str .= '<td width="25%" class="status_'.$STATUSCODES[$issue->status].' tracker-issue-value">';
        $str .= '<b>'.$STATUSKEYS[$issue->status].'</b>';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" width="25%" class="tracker-issue-param">';
        $str .= '<b>'.tracker_getstring('reportedby', 'tracker').':</b>';
        $str .= '</td>';
        $str .= '<td width="25%" class="tracker-issue-value">';
        $str .= $OUTPUT->user_picture($issue->reporter);
        $str .= '&nbsp;'.fullname($issue->reporter);
        $str .= '</td>';
        $str .= '<td align="right" width="25%" class="tracker-issue-param" >';
        $str .= '<b>'.tracker_getstring('datereported', 'tracker').':</b>';
        $str .= '</td>';
        $str .= '<td width="25%" class="tracker-issue-value">';
        $str .= userdate($issue->datereported);
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" width="25%" class="tracker-issue-param">';
        $str .= '<b>'.tracker_getstring('assignedto', 'tracker').':</b>';
        $str .= '</td>';
        $str .= '<td width="25%" class="tracker-issue-value">';
        if (!$issue->owner){
            $str .= tracker_getstring('unassigned', 'tracker');
        } else {
            $str .= $OUTPUT->user_picture($issue->owner, array('courseid' => $COURSE->id, 'size' => 35));
            $str .= '&nbsp;'.fullname($issue->owner); 
        }
        $str .= '</td>';
        $str .= '<td align="right" width="25%" class="tracker-issue-param">';
        $str .= '<b>'.tracker_getstring('cced', 'tracker').':</b>';
        $str .= '</td>';
        $str .= '<td width="25%" class="tracker-issue-value">';
        $str .= (empty($ccs) || count(array_keys($ccs)) == 0) ? 0 : count($ccs);
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" width="25%" class="tracker-issue-param">';
        $str .= '<b>'.tracker_getstring('description').':</b>';
        $str .= '</td>';
        $str .= '<td align="left" colspan="3" width="75%" class="tracker-issue-value">';
        $str .= format_text($issue->description);
        $str .= '</td>';
        $str .= '</tr>';
    
        return $str;
    }

    function edit_link($issue, $cm) {

        $params = array('id' => $cm->id, 'view' => 'view', 'screen' => 'editanissue', 'issueid' => $issue->id);

        $issueurl = new moodle_url('/mod/tracker/view.php', $params);

        $str = '';

        $str .= '<tr>';
        $str .= '<td colspan="4" align="right">';
        $str .= '<form method="post" action="'.$issueurl.'">';
        $str .= '<input type="submit" name="go_btn" value="'.tracker_getstring('turneditingon', 'tracker').'">';
        $str .= '</form>';
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    function issue_attributes($issue, $elementsused) {
        $str = '';

        $cm = get_coursemodule_from_instance('tracker', $issue->trackerid);
        $context = context_module::instance($cm->id);
        $canmanage = has_any_capability(array('mod/tracker:manage', 'mod/tracker:develop', 'mod/tracker:resolve'), $context); // ecastro ULPGC
  
        if (!empty($elementsused)) {
            foreach($elementsused as $key => $item) { // ecastro ULPGC avoid infinite loop with for i
                // Hide private fields if not power user.
                if ($item->private && !$canmanage) {
                    continue;
                }
                // Print first category in one column
                $str .= '<tr valign="top">';
                $str .= '<td colspan="1" class="tracker-issue-description">';
                $str .= '<b>';
                $str .= format_string($item->description);
                $str .= ':</b><br />';
                $str .= '</td>';
    
                $str .= '<td colspan="3" class="tracker-issue-value">';
                $str .= $item->view($issue->id);
                $str .= '</td>';
                $str .= '</tr>';
            }
        }
        
        return $str;
    }

    function resolution($issue) {
        $str = '';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" height="25%" class="tracker-issue-param">';
        $str .= '<b>'.tracker_getstring('resolution', 'tracker').':</b>';
        $str .= '</td>';
        $str .= '<td align="left" colspan="3" width="75%">';
        $str .= format_text($issue->resolution, $issue->resolutionformat);
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    function distribution_form($tracker, $issue, $cm) {
        global $DB;

        $str = '';

        $choosetargetstr = tracker_getstring('choosetarget', 'tracker'); // ecastro ULPGC
        $str .= ' <form name="distribute" style="display:inline">';
        $str .= '<input type="hidden" name="view" value="view" >';
        $str .= '<input type="hidden" name="what" value="distribute" >';
        $str .= '<input type="hidden" name="issueid" value="'.$issue->id.'" >';
        $str .= '<input type="hidden" name="id" value="'.$cm->id.'" >';
        $str .= '<select name="target">';
        $str .= '<option value="0">'.$choosetargetstr.'</option>';
        $trackermoduleid = $DB->get_field('modules', 'id', array('name' => 'tracker'));
        if ($subtrackers = $DB->get_records('tracker', array('id' => $tracker->subtrackers), 'name', 'id,name,course')) {
            foreach ($subtrackers as $st) {
                if ($targetcm = $DB->get_record('course_modules', array('instance' => $st->id, 'module' => $trackermoduleid))) {
                    $courseshort = $DB->get_field('course', 'shortname', array('id' => $st->course));
                    $targetcontext = context_module::instance($targetcm->id);
                    if (has_any_capability(array('mod/tracker:manage', 'mod/tracker:develop', 'mod/tracker:resolve'), $targetcontext)) {
                        $str .= '<option value="'.$st->id.'">'.$courseshort.' - '.$st->name.'</option>';
                    }
                }
            }
        }
        $str .= '</select>';
        $str .= '</form>';
        $str .= " <a href=\"Javascript:document.forms['distribute'].submit();\">".tracker_getstring('distribute','tracker').'</a>'; // ecastro ULPGC

        return $str;
    }

    /**
     * prints comments for the given issue
     * @uses $CFG
     * @param int $issueid
     */
    function comments($issueid, $contextid = 0) {
        global $CFG, $DB;

        $str = '';

        $comments = $DB->get_records('tracker_issuecomment', array('issueid' => $issueid), 'datecreated');
        
        $fs = get_file_storage(); // ecastro ULPGC
        if(!$contextid) {
            if(is_numeric($issueid)) {
                $issue = $DB->get_record('tracker_issue', array('id'=>$issueid), '*', MUST_EXIST);
            }
            $cm =  get_coursemodule_from_instance('tracker', $issue->trackerid);
            $context = context_module::instance($cm->id);
            $contextid = $context->id;
        }
        $fileinfo = array(
                'contextid' => $contextid, // ID of context
                'component' => 'mod_tracker',     // usually = table name
                'filearea' => 'issuecomment',     // usually = table name
                'itemid' => 0,               // usually = ID of row in table
                'filepath' => '/',           // any path beginning and ending in /
        );
        
        if ($comments) {
        
            foreach ($comments as $comment) {
                $links = array();
                $fileinfo['itemid'] = $comment->id;
                if($files = $fs->get_directory_files($contextid, 'mod_tracker', 'issuecomment', $comment->id, '/', false, false)) {
                    foreach($files as $file) {
                        $filename = $file->get_filename();
                        $url = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$contextid.'/mod_tracker/issuecomment/'.$comment->id.'/'.$filename, true);
                        if (preg_match("/\.(jpg|gif|png|jpeg)$/i", $filename)){
                            $links[] = "<img src=\"{$url}\" class=\"tracker_image_attachment\" />";
                        } else {
                            $mime = mimeinfo("icon", $filename);
                            $icon = new pix_icon(file_extension_icon($filename), $mime, 'moodle', array('class'=>'iconsmall'));
                            $links[] = $this->action_link($url, $filename, null, null, $icon);
                        }
                    }
                }
                
                $user = $DB->get_record('user', array('id' => $comment->userid));
                $str .= '<tr>';
                $str .= '<td valign="top" class="commenter" width="30%">';
                $str .= $this->user($user);
                $str .= '<br/>';
                $str .= '<span class="timelabel">'.userdate($comment->datecreated).'</span>';
                $str .= '</td>';
                $str .= '<td colspan="3" valign="top" align="left" class="comment">';
                if($links) {
                    $str .= '<div class=" commentattachments ">';
                    foreach($links as $link) {
                        $str .=  $link.' <br />';
                    }
                    $str .=  '</div>';
                }
                $str .= $comment->comment;
                $str .= '</td>';
                $str .= '</tr>';
            }
        }

        return $str;
    }

    /**
    * a local version of the print user command that fits  better to the tracker situation
    * @uses $COURSE
    * @uses $CFG
    * @param object $user the user record
    */
    function user($user) {
        global $COURSE, $CFG, $OUTPUT;
    
        $str = '';
    
        if ($user) {
            $str .= $OUTPUT->user_picture ($user, array('courseid' => $COURSE->id, 'size' => 25));
            $userurl = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $COURSE->id));
            if ($CFG->messaging) {
                $str .= '&nbsp;<a href="'.$userurl.'">'.fullname($user).'</a>';
                $str .= '&nbsp;<a href="" onclick="this.target=\'message\'; return openpopup(\'/message/discussion.php?id={$user->id}\', \'message\', \'menubar=0,location=0,scrollbars,status,resizable,width=400,height=500\', 0);" ><img src="'.$OUTPUT->pix_url('t/email').'"></a>';
            } elseif (!$user->emailstop && $user->maildisplay) {
                $str .= '&nbsp;<a href="'.$userurl.'">'.fullname($user).'</a>';
                $str .= '&nbsp;<a href="mailto:'.$user->email.'"><img src="'.$OUTPUT->pix_url('t/mail', 'core').'"></a>';
            } else {
                $str .= '&nbsp;'.fullname($user);
            }
        }

        return $str;
    }

    function ccs(&$ccs, &$issue, &$cm, &$cced, $initialviewmode) {
        global $OUTPUT, $DB;

        $str = '';

        $str .= '<tr>';
        $str .= '<td colspan="4" width="100%" class="tracker-ccs">';
        $str .= '<table id="issueccs" class="'.$initialviewmode.'" width="100%">';
        $str .= '<tr valign="top">';
        $str .= '<td colspan="3">';
        $str .= $OUTPUT->heading(tracker_getstring('cced', 'tracker'));
        $str .= '</td>';
        $str .= '</tr>';

        foreach ($ccs as $cc) {
            $str .= '<tr valign="top">';
            $str .= '<td width="20%" valign="top">&nbsp;</td>';
            $str .= '<td align="left" style="white-space : nowrap" valign="top">';
            $user = $DB->get_record('user', array('id' => $cc->userid));
            $str .= $this->user($user);
            $cced[] = $cc->userid;
            $str .= '</td>';
            $str .= '<td align="right">';
            if (has_capability('mod/tracker:managewatches', context_module::instance($cm->id))) {
                $params = array('id' => $cm->id, 'view' => 'view', 'what' => 'unregister', 'issueid' => $issue->id, 'ccid' => $cc->userid);
                $deleteurl = new moodle_url('/mod/tracker/view.php', $params);
                $str .= '&nbsp;<a href="'.$deleteurl.'" title="'.tracker_getstring('delete').'"><img src="'.$OUTPUT->pix_url('t/delete', 'core').'" /></a>';
            }
            $str .= '</td>';
            $str .= '</tr>';
        }
        $str .= '</table>';
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    function watches_form(&$issue, &$cm, &$cced) {

        $str = '';

        $str .= '<tr>';
        $str .= '<td>&nbsp;</td>';
        $str .= '<td colspan="3" align="right">';
        $issueurl = new moodle_url('/mod/tracker/view.php');
        $str .= '<form name="addccform" method="get" action="'.$issueurl.'">';
        $str .= '<input type="hidden" name="id" value="'.$cm->id.'" />';
        $str .= '<input type="hidden" name="what" value="register" />';
        $str .= '<input type="hidden" name="view" value="view" />';
        $str .= '<input type="hidden" name="issueid" value="'.$issue->id.'" />';
        $str .= tracker_getstring('addawatcher', 'tracker').':&nbsp;';
        $contextmodule = context_module::instance($cm->id);
        $potentials = get_users_by_capability($contextmodule, 'mod/tracker:canbecced', 'u.id,'.get_all_user_name_fields(true, 'u').',picture,imagealt');
        $potentialsmenu = array();
        if ($potentials) {
            foreach ($potentials as $potential) {
                if (in_array($potential->id, $cced)) {
                    continue;
                }
                $potentialsmenu[$potential->id] = fullname($potential);
            }
        }
        $str .= html_writer::select($potentialsmenu, 'ccid');
        $str .= '<input type="submit" name="go_btn" value="'.tracker_getstring('add').'" />';
        $str .= '</form>';
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    function history($history, $statehistory, $initialviewmode) {
        global $DB, $OUTPUT, $STATUSCODES, $STATUSKEYS;

        $str = '';

        $str .= '<tr>';
        $str .= '<td colspan="4" align="center" width="100%">';
        $str .= '<table id="issuehistory" class="'.$initialviewmode.'" width="100%">';
        $str .= '<tr valign="top">';
        $str .= '<td width="50%">';
        $str .= $OUTPUT->heading(tracker_getstring('history', 'tracker'));
        $str .= '</td>';
        $str .= '<td width="50%">';
        $str .= $OUTPUT->heading(tracker_getstring('statehistory', 'tracker'));
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td width="50%">';
        $str .= '<table width="100%">';
        if (!empty($history)) {
            foreach ($history as $owner) {
                $user = $DB->get_record('user', array('id' => $owner->userid));
                $bywhom = $DB->get_record('user', array('id' => $owner->bywhomid));

                $str .= '<tr>';
                $str .= '<td align="left">';
                $str .= userdate($owner->timeassigned);
                $str .= '</td>';
                $str .= '<td align="left">';
                $str .= $this->user($user);
                $str .= '</td>';
                $str .= '<td align="left">';
                $str .= tracker_getstring('by', 'tracker') . ' ' . fullname($bywhom);
                $str .= '</td>';
                $str .= '</tr>';
            }
        }
        $str .= '</table>';
        $str .= '</td>';
        $str .= '<td width="50%">';
        $str .= '<table width="100%">';
        if (!empty($statehistory)) {
            foreach ($statehistory as $state) {
                $bywhom = $DB->get_record('user', array('id' => $state->userid));
                $str .= '<tr valign="top">';
                $str .= '<td align="left">';
                $str .= userdate($state->timechange);
                $str .= '</td>';
                $str .= '<td align="left">';
                $str .= $this->user($bywhom);
                $str .= '</td>';
                $str .= '<td align="left">';
                $str .= '<span class="status_'.$STATUSCODES[$state->statusfrom].'">'.$STATUSKEYS[$state->statusfrom].'</span>';
                $str .= '</td>';
                $str .= '<td align="left">';
                $str .= '</td>';
                $str .= '<td align="left">';
                $str .= '<span class="status_'.$STATUSCODES[$state->statusto].'">'.$STATUSKEYS[$state->statusto].'</span>';
                $str .= '</td>';
                $str .= '</tr>';
            }
        }
        $str .= '</table>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= ' </td>';
        $str .= '</tr>';

        return $str;
    }

    function tabs($view, $screen, &$tracker, &$cm) {
        global $DB, $USER, $OUTPUT;

        $str = '';
        $context = context_module::instance($cm->id);

        $ulpgckey = false;
        $canviewall = false;
        $canmanage = false;
        $canreport = has_capability('mod/tracker:report', $context);
        $fulledit = has_all_capabilities(array('mod/tracker:configurenetwork', 'mod/tracker:shareelements'), $context);  
        
        if(($tracker->supportmode == 'usersupport') || 
                ($tracker->supportmode == 'boardreview') || 
                    ($tracker->supportmode == 'tutoring') ) {
            $ulpgckey = true;
            $canviewall = has_capability('mod/tracker:viewallissues', $context);
            $canmanage = has_capability('mod/tracker:manage', $context);
            $canreport = $canreport && (($tracker->supportmode != 'tutoring') || $canmanage);
        }

        if ($screen == 'mytickets') {
            $select = "trackerid = ? AND status <> ".RESOLVED." AND status <> ".ABANDONNED." AND reportedby = ? ";
            $totalissues = $DB->count_records_select('tracker_issue', $select, array($tracker->id, $USER->id));

            $select = "trackerid = ? AND (status = ".RESOLVED." OR status = ".ABANDONNED.") AND reportedby = ? ";
            $totalresolvedissues = $DB->count_records_select('tracker_issue', $select, array($tracker->id, $USER->id));
        } elseif ($screen == 'mywork') {
            $select = "trackerid = ? AND status <> ".RESOLVED." AND status <> ".ABANDONNED." AND assignedto = ? ";
            $totalissues = $DB->count_records_select('tracker_issue', $select, array($tracker->id, $USER->id));

            $select = "trackerid = ? AND (status = ".RESOLVED." OR status = ".ABANDONNED.") AND assignedto = ? ";
            $totalresolvedissues = $DB->count_records_select('tracker_issue', $select, array($tracker->id, $USER->id));
        } else {
            $userselect = '';
            $params = array($tracker->id);
            if(!$canviewall && (($tracker->supportmode == 'usersupport') || ($tracker->supportmode == 'boardreview') || ($tracker->supportmode == 'tutoring'))) {
                $userselect = ' AND reportedby = ? ';
                $params[] = $USER->id;
            }

            $select = "trackerid = ? AND status <> ".RESOLVED." AND status <> ".ABANDONNED." AND status <> ".PUBLISHED;
            $totalissues = $DB->count_records_select('tracker_issue', $select.$userselect, $params); // ecastro ULPGC

            $select = "trackerid = ? AND (status = ".RESOLVED." OR status = ".ABANDONNED." OR status = ".PUBLISHED.")";
            $totalresolvedissues = $DB->count_records_select('tracker_issue', $select.$userselect, $params); // ecastro ULPGC
        }

        // Print tabs with options for user.
        if ($canreport) {
            $rows[0][] = new tabobject('reportanissue', "reportissue.php?id={$cm->id}", tracker_getstring('newissue', 'tracker'));
        }

        $rows[0][] = new tabobject('view', "view.php?id={$cm->id}&amp;view=view", tracker_getstring('view', 'tracker').' ('.$totalissues.' '.tracker_getstring('issues','tracker').')');

        $rows[0][] = new tabobject('resolved', "view.php?id={$cm->id}&amp;view=resolved", tracker_getstring('resolvedplural', 'tracker').' ('.$totalresolvedissues.' '.tracker_getstring('issues','tracker').')');

        $rows[0][] = new tabobject('profile', "view.php?id={$cm->id}&amp;view=profile", tracker_getstring('profile', 'tracker'));

        if (has_capability('mod/tracker:viewreports', $context)) {
            $rows[0][] = new tabobject('reports', "view.php?id={$cm->id}&amp;view=reports", tracker_getstring('reports', 'tracker'));
        }

        if (has_capability('mod/tracker:configure', $context)) {
            $rows[0][] = new tabobject('admin', "view.php?id={$cm->id}&amp;view=admin", tracker_getstring('administration', 'tracker'));
        }

        $myticketsstr = ($tracker->supportmode != 'taskspread') ? tracker_getstring('mytickets', 'tracker') : tracker_getstring('mytasks', 'tracker');

        // submenus
        $selected = null;
        $activated = null;
        switch ($view) {
            case 'view' :
            if (!preg_match("/mytickets|mywork|browse|user|search|viewanissue|editanissue|assigns/", $screen)) $screen = 'mytickets'; // ecastro ULPGC
                if (has_capability('mod/tracker:report', $context)) {
                    $rows[1][] = new tabobject('mytickets', "view.php?id={$cm->id}&amp;view=view&amp;screen=mytickets", $myticketsstr);
                }
                if (tracker_has_assigned($tracker, false)) {
                    $rows[1][] = new tabobject('mywork', "view.php?id={$cm->id}&amp;view=view&amp;screen=mywork", tracker_getstring('mywork', 'tracker'));
                }
                if (has_capability('mod/tracker:viewallissues', $context) || $tracker->supportmode == 'bugtracker') {
                    $rows[1][] = new tabobject('browse', "view.php?id={$cm->id}&amp;view=view&amp;screen=browse", tracker_getstring('browse', 'tracker'));
                    if($ulpgckey) { // ecastro ULPGC
                        $rows[1][] = new tabobject('user', "view.php?id={$cm->id}&amp;view=view&amp;screen=user", tracker_getstring('userview', 'tracker'));
                    }
                }
                if ($tracker->supportmode == 'bugtracker' || ($canviewall && (($tracker->supportmode == 'usersupport') || ($tracker->supportmode == 'boardreview') || ($tracker->supportmode == 'tutoring')))) { // ecastro ULPGC
                    $rows[1][] = new tabobject('search', "view.php?id={$cm->id}&amp;view=view&amp;screen=search", tracker_getstring('search', 'tracker'));
                }
                if ($canmanage && $canviewall && $fulledit ) {
                    $rows[1][] = new tabobject('assigns', "view.php?id={$cm->id}&amp;view=view&amp;screen=assigns", tracker_getstring('assigns', 'tracker'));
                }
                break;
            case 'resolved' :
                if (!preg_match("/mytickets|browse|viewanissue|mywork/", $screen)) $screen = 'mytickets';
                if (has_capability('mod/tracker:report', $context)) {
                    $rows[1][] = new tabobject('mytickets', "view.php?id={$cm->id}&amp;view=resolved&amp;screen=mytickets", $myticketsstr);
                }
                if (tracker_has_assigned($tracker, true)) {
                    $rows[1][] = new tabobject('mywork', "view.php?id={$cm->id}&amp;view=view&amp;screen=mywork", tracker_getstring('mywork', 'tracker'));
                }
                if (has_capability('mod/tracker:viewallissues', $context) || $tracker->supportmode == 'bugtracker') {
                    $rows[1][] = new tabobject('browse', "view.php?id={$cm->id}&amp;view=resolved&amp;screen=browse", tracker_getstring('browse', 'tracker'));
                }
            break;
            case 'profile':
                if (!preg_match("/myprofile|mypreferences|mywatches|myqueries/", $screen)) $screen = 'myprofile';

                $rows[1][] = new tabobject('myprofile', "view.php?id={$cm->id}&amp;view=profile&amp;screen=myprofile", tracker_getstring('myprofile', 'tracker'));
                $rows[1][] = new tabobject('mypreferences', "view.php?id={$cm->id}&amp;view=profile&amp;screen=mypreferences", tracker_getstring('mypreferences', 'tracker'));
                $rows[1][] = new tabobject('mywatches', "view.php?id={$cm->id}&amp;view=profile&amp;screen=mywatches", tracker_getstring('mywatches', 'tracker'));
                if ($tracker->supportmode == 'bugtracker' || ($canviewall && (($tracker->supportmode == 'usersupport') || ($tracker->supportmode == 'boardreview') || ($tracker->supportmode == 'boardreview')))) { // ecastro ULPGC
                    $rows[1][] = new tabobject('myqueries', "view.php?id={$cm->id}&amp;view=profile&amp;screen=myqueries", tracker_getstring('myqueries', 'tracker'));
                }
            break;
            case 'reports':
                if (!preg_match("/status|evolution|print/", $screen)) $screen = 'status';
                $rows[1][] = new tabobject('status', "view.php?id={$cm->id}&amp;view=reports&amp;screen=status", tracker_getstring('status', 'tracker'));
                $rows[1][] = new tabobject('evolution', "view.php?id={$cm->id}&amp;view=reports&amp;screen=evolution", tracker_getstring('evolution', 'tracker'));
                $rows[1][] = new tabobject('print', "view.php?id={$cm->id}&amp;view=reports&amp;screen=print", tracker_getstring('print', 'tracker'));
            break;
            case 'admin':
                if (!preg_match("/summary|manageelements|managefiles|managewords|managenetwork/", $screen)) $screen = 'summary';
                $rows[1][] = new tabobject('summary', "view.php?id={$cm->id}&amp;view=admin&amp;screen=summary", tracker_getstring('summary', 'tracker'));
                $rows[1][] = new tabobject('manageelements', "view.php?id={$cm->id}&amp;view=admin&amp;screen=manageelements", tracker_getstring('manageelements', 'tracker'));
                if($canmanage) {
                    $rows[1][] = new tabobject('managefiles', "view.php?id={$cm->id}&amp;view=admin&amp;screen=managefiles", tracker_getstring('managefiles', 'tracker'));
                    $rows[1][] = new tabobject('managewords', "view.php?id={$cm->id}&amp;view=admin&amp;screen=managewords", tracker_getstring('managewords', 'tracker'));
                }
                if (has_capability('mod/tracker:configurenetwork', $context)) {
                    $rows[1][] = new tabobject('managenetwork', "view.php?id={$cm->id}&amp;view=admin&amp;screen=managenetwork", tracker_getstring('managenetwork', 'tracker'));

                }
                break;
            default:
        }
        if (!empty($screen)) {
            $selected = $screen;
            $activated = array($view);
        } else {
            $selected = $view;
        }
        $str .= $OUTPUT->container_start('mod-header tracker-tabs');
        $str .= print_tabs($rows, $selected, '', $activated, true);
        $str .= $OUTPUT->container_end();

        return $str;
    }
}
