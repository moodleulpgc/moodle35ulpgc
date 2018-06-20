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
 * Prints the Session management interface of an instance of examregistrar
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/mod/examregistrar/locallib.php');
*/
require_once($CFG->dirroot."/mod/examregistrar/manage/manage_forms.php");
require_once($CFG->dirroot."/mod/examregistrar/manage/manage_table.php");

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

require_capability('mod/examregistrar:manageseats', $context);

$edit   = optional_param('edit', '', PARAM_ALPHANUMEXT);  // list/edit items
$action = optional_param('action', '', PARAM_ALPHANUMEXT);  // complex action not managed by edit
$upload = optional_param('csv', '', PARAM_ALPHANUMEXT);  // upload CSV file
$rsort = optional_param('rsort', '', PARAM_ALPHANUMEXT);
$esort = optional_param('esort', '', PARAM_ALPHANUMEXT);
$perpage  = optional_param('perpage', 100, PARAM_INT);

$SESSION->nameformat = 'lastname';

$baseurl = new moodle_url('/mod/examregistrar/view.php', array('id' => $cm->id, 'tab' =>'session'));
$tab = 'session';

$session   = optional_param('session', 0, PARAM_INT);
$bookedsite   = optional_param('venue', '', PARAM_INT);

$now = time();
//$now = strtotime('4 may 2014') + 3605;
if(!$session) {
    $session = examregistrar_next_sessionid($examregistrar, $now);
}

$baseurl->params(array('session'=>$session, 'venue'=>$bookedsite));


///////////////////////////////////////////////////////////////////////////////


/// process forms actions

if($action == 'assignseats_venues') {
    // get venues and heck for single room
    $venueelement = examregistrar_get_venue_element($examregistrar);
    
    if($venues = $DB->get_records('examregistrar_locations', array('examregid'=>$examregprimaryid, 'locationtype'=>$venueelement, 'visible'=>1))) {
        foreach($venues as $venue) {
            if($roomid = examregistrar_is_venue_single_room($venue)) {
                // assign venue exams to room
                if(!$max = $DB->get_records('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$venue->id),
                                                ' timemodified DESC ', '*', 0, 1)) {
                    examregistrar_session_seats_makeallocation($session, $venue->id);
                } else {
                    $lasttime = reset($max)->timecreated;
                    examregistrar_session_seats_newbookings($session, $venue->id, $lasttime+1);
                }
            }
        }
    }
} elseif(($action == 'session_responses') && $session) {
    $config = get_config('examregistrar');
    $fs = get_file_storage();
    if($pending = $fs->get_directory_files($context->id, 'mod_examregistrar', 'sessionresponses', $session, '/', false, false)) {
        $sessiondir =  clean_filename($config->distributedfolder);
        $sessiondir = '/'.$sessiondir.'/';
        $fs->create_directory($context->id, 'mod_examregistrar', 'sessionresponses', $session, $sessiondir);
        $exams = array();
        //make_upload_directory($sessiondir);
        //check_dir_exists($sessiondir);
        if($sessionexams = examregistrar_get_session_exams($session, $bookedsite, $esort,  false, false)) {
            foreach($sessionexams as $exam) {
                $examclass = new examregistrar_exam($exam);
                $exams[$exam->id] = $examclass->get_exam_name(false, true, false, false);
                //$exams[$exam->id] = $exam->shortname;
            }
        }
        $filerecord = array('component'=>'mod_examregistrar', 'filearea'=>'responses', 'filepath'=>'/');
        $info = new stdClass();
        $info->delivered = 0;
        $info->fail = 0;
        $cinfo = new stdClass();
        list($sname, $sidnumber) = examregistrar_get_namecodefromid($session, 'examsessions', 'examsession');
        $cinfo->session = $sidnumber;
        $names = get_all_user_name_fields(true, 'u');
        $from = get_string('mailfrom',  'examregistrar');
        $delivered = array();
        foreach($pending as $file) {
            $fname = $file->get_filename();
            $name =  (false === strpos($fname, '.')) ? $fname : strstr($fname, '.', true);
            $name =  (false === strpos($name, '_')) ? $name : strstr($name, '_', true);
            if($examid = array_search($name, $exams)) {
                // we have an exam: addfile to areafiles and move to backup
                if($examfile = $DB->get_record('examregistrar_examfiles', array('examid'=>$examid, 'status'=>EXAM_STATUS_APPROVED))) {
                    $fcontext = context_course::instance($sessionexams[$examid]->courseid);
                    $filerecord['contextid'] = $fcontext->id;
                    $filerecord['itemid'] = $examfile->id;
                    $filerecord['filename'] = $examfile->idnumber.$config->extresponses;
                    $files = $fs->get_area_files($filerecord['contextid'], $filerecord['component'], $filerecord['filearea'], $filerecord['itemid']);
                    $num = 1;
                    if($files) {
                        $num = count($files) + 1;
                        $filerecord['filename'] .= "($num)";
                    }
                    $filerecord['filename'] .= '.pdf';
                    if($fs->create_file_from_storedfile($filerecord, $file)) {
                        // file is delivered to exam now move to backup
                        if($fs->file_exists($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $sessiondir, $fname)) {
                            $pathinfo = pathinfo($fname);
                            $fname = $pathinfo['filename']."($num)".'.'.$pathinfo['extension'];
                        }
                        $file->rename($sessiondir, $fname);
                        $DB->set_field('examregistrar_examfiles', 'taken', 1, array('id'=>$examfile->id, 'examid'=>$examid));
                        $info->delivered += 1;
                        $delivered[] = $name;
                        // now send email
                        $teachers = get_enrolled_users($fcontext, 'mod/examregistrar:download', 0, 'u.id, u.idnumber, u.email, u.mailformat, u.username, '.$names);
                        $subject = get_string('mailresponsessubject',  'examregistrar', $name);
                        $cinfo->fname = $filerecord['filename'];
                        $cinfo->course = $name;
                        $text = get_string('mailresponsestext',  'examregistrar', $cinfo);


                        foreach($teachers as $user) {
                            email_to_user($user, $from, $subject, $text, $text);
                        }
                    }
                }
            } else {
                $info->fail += 1;
            }
        }
        $controluser = core_user::get_support_user();
        $controluser->email = 'ccv@ulpgc.es';
        $controluser->mailformat = 1;
        $controluser->id = 1;
        $subject = get_string('mailsessionsubject',  'examregistrar', $sidnumber);
        $text = get_string('mailsessioncontrol',  'examregistrar', implode("\n", $delivered) );
        email_to_user($controluser, $from, $subject, $text, $text);
        $controluser->email = 'ditele@ulpgc.es';
        email_to_user($controluser, $from, $subject, $text, $text);
        redirect($baseurl, get_string('loadresponsesconfirm', 'examregistrar', $info), 5);
    }
} elseif(($action == 'session_files') && $session) {
    if($del = optional_param('deleteresponsefiles', '', PARAM_ALPHANUMEXT)) {
        $success = false;
        $fs = get_file_storage();
        if($files = $fs->get_directory_files($context->id, 'mod_examregistrar', 'sessionresponses', $session, '/', false, false)) {
            foreach($files as $file) {
                $success = $file->delete();
            }
        }
        if($success)  {
            add_to_log($course->id, 'examregistrar', 'delete session files', 'view.php?id='.$cm->id, $examregistrar->name, $cm->id);
        }
        $baseurl->param('action', 'session_files');
        redirect($baseurl);
    }

    $data = new stdClass();
    $data->id = $cm->id;
    $data->tab = 'session';
    $data->session = $session;
    $data->bookedsite = $bookedsite;
    $data->action = $action;
    $data->area = optional_param('area', 'sessionresponses', PARAM_ALPHANUMEXT);
    $options = array('subdirs'=>1, 'maxbytes'=>$CFG->maxbytes, 'maxfiles'=>-1, 'accepted_types'=>'*');
    file_prepare_standard_filemanager($data, 'files', $options, $context, 'mod_examregistrar', $data->area, $session);
    $mform = new examregistrar_files_form(null, array('data'=>$data, 'options'=>$options));

    if (!$mform->is_cancelled()) {
        if ($formdata = $mform->get_data()) {
            if(!isset($formdata->deleteresponsefiles)) {
                $formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $context, 'mod_examregistrar', $data->area, $session);
                add_to_log($course->id, 'examregistrar', 'edit session files', 'view.php?id='.$cm->id, $examregistrar->name, $cm->id);
            }
        } elseif(!$formdata) {
            $sessionname = '';
            if($session) {
                $sql = "SELECT s.id, s.examsession, es.name, es.idnumber, s.examdate, ep.name AS periodname, ep.idnumber AS periodidnumber
                        FROM {examregistrar_examsessions} s
                        JOIN {examregistrar_elements} es ON es.examregid = s.examregid AND es.type = 'examsessionitem' AND s.examsession = es.id
                        JOIN {examregistrar_periods} p ON s.examregid = p.examregid AND s.period = p.id
                        JOIN {examregistrar_elements} ep ON ep.examregid = p.examregid AND ep.type = 'perioditem' AND p.period = ep.id
                        WHERE s.id = :id ";
                $examsession = $DB->get_record_sql($sql, array('id'=>$session), MUST_EXIST);
                $sessionname = $output->formatted_name($examsession->periodname, $examsession->periodidnumber).'; ';
                $sessionname .= $output->formatted_name($examsession->name, $examsession->idnumber).',  '. userdate($examsession->examdate, get_string('strftimedaydate'));
            }

            echo $output->heading($sessionname, 3, 'main');
            echo $output->container('', 'clearfix');
            $headstr = ($data->area == 'control') ? 'loadsessioncontrol' : 'loadsessionresponses';
            echo $OUTPUT->heading(get_string($headstr,  'examregistrar'), 4, 'main');
            echo $OUTPUT->box_start('generalbox foldertree');
            $mform->display();
            echo $OUTPUT->box_end();
            echo $OUTPUT->footer();
            die();
        }
    }
} elseif(($action == 'response_files') && $session) {
    $data = new stdClass();
    $data->id = $cm->id;
    $data->tab = 'session';
    $data->session = $session;
    $data->bookedsite = $bookedsite;
    $data->action = $action;
    $data->area = 'responses';
    $data->examfile = optional_param('examf', 0, PARAM_INT);
    $examfile = $DB->get_record('examregistrar_examfiles', array('id'=>$data->examfile), '*', MUST_EXIST);
    $exam = $DB->get_record('examregistrar_exams', array('id'=>$examfile->examid), '*', MUST_EXIST);
    $ccontext = context_course::instance($exam->courseid);
    $options = array('subdirs'=>0, 'maxbytes'=>$CFG->maxbytes, 'maxfiles'=>-1, 'accepted_types'=>'*');
    file_prepare_standard_filemanager($data, 'files', $options, $ccontext, 'mod_examregistrar', 'responses', $data->examfile);
    $mform = new examregistrar_files_form(null, array('data'=>$data, 'options'=>$options));
    if (!$mform->is_cancelled()) {
        if ($formdata = $mform->get_data()) {
            $formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $ccontext, 'mod_examregistrar', 'responses', $data->examfile);
            add_to_log($course->id, 'examregistrar', 'edit response files', 'view.php?id='.$cm->id, $examregistrar->name, $cm->id);
        } elseif(!$formdata) {
            $sessionname = '';
            if($session) {
                $sql = "SELECT s.id, s.examsession, es.name, es.idnumber, s.examdate, ep.name AS periodname, ep.idnumber AS periodidnumber
                        FROM {examregistrar_examsessions} s
                        JOIN {examregistrar_elements} es ON es.examregid = s.examregid AND es.type = 'examsessionitem' AND s.examsession = es.id
                        JOIN {examregistrar_periods} p ON s.examregid = p.examregid AND s.period = p.id
                        JOIN {examregistrar_elements} ep ON ep.examregid = p.examregid AND ep.type = 'perioditem' AND p.period = ep.id
                        WHERE s.id = :id ";
                $examsession = $DB->get_record_sql($sql, array('id'=>$session), MUST_EXIST);
                $sessionname = $output->formatted_name($examsession->periodname, $examsession->periodidnumber).'; ';
                $sessionname .= $output->formatted_name($examsession->name, $examsession->idnumber).',  '. userdate($examsession->examdate, get_string('strftimedaydate'));
            }

            echo $output->heading($sessionname, 3, 'main');
            echo $output->container('', 'clearfix');
            echo $OUTPUT->heading(get_string('examresponsesfiles',  'examregistrar'), 4, 'main');
            echo $OUTPUT->box_start('generalbox foldertree');
            $mform->display();
            echo $OUTPUT->box_end();
            echo $OUTPUT->footer();
            die();
        }
    }
}
////////////////////////////////////////////////////////////////////////////////

/// Print the page header, Output starts here
    $candownload = has_capability('mod/examregistrar:download',$context);
    //echo $output->header();
    // Add tabs, if needed
    include_once('tabs.php');
    echo $output->container_start(' examregistrarmanagelinks ');
    echo html_writer::empty_tag('hr');
        $params = array('id' => $cm->id, 'session' => $session, 'venue' => $bookedsite);
        $editurl = new moodle_url('/mod/examregistrar/manage.php', $params);
        $uploadurl = new moodle_url($editurl);
        $actionurl = new moodle_url('/mod/examregistrar/manage/action.php', $params);

    echo html_writer::nonempty_tag('span', get_string('roomassignments', 'examregistrar').': ' , array('class'=>'examregistrarmanageheaders'));
    $text = array();
    $editurl->param('edit', 'session_rooms');
    $text[] = html_writer::link($editurl, get_string('editsessionrooms', 'examregistrar'));
    $actionurl->param('action', 'sessionrooms');
    $text[] = html_writer::link($actionurl, get_string('assignsessionrooms', 'examregistrar'));
    $uploadurl->param('csv', 'session_rooms');
    $uploadurl->param('edit', 'session_rooms');
    $text[] = html_writer::link($uploadurl, get_string('uploadcsvsession_rooms', 'examregistrar'));
    $actionurl->param('action', 'stafffromexam');
    $text[] = html_writer::link($actionurl, get_string('stafffromexam', 'examregistrar'));
    
    echo implode(',&nbsp;&nbsp;',$text).'<br />';

    echo html_writer::nonempty_tag('span', get_string('seatassignments', 'examregistrar').': ' , array('class'=>'examregistrarmanageheaders'));
    $text = array();
    $url = new moodle_url('/mod/examregistrar/manage/assignseats.php', array('id'=>$cm->id, 'edit'=>'session_rooms'));
    $text[] = html_writer::link($url, get_string('assignseats', 'examregistrar'));

    $url = new moodle_url($baseurl, array('action'=>'assignseats_venues'));
    $text[] = html_writer::link($url, get_string('assignseats_venues', 'examregistrar'));



    $uploadurl->param('csv', 'assignseats');
    $uploadurl->param('edit', 'session_rooms');
    $text[] = html_writer::link($uploadurl, get_string('uploadcsvassignseats', 'examregistrar'));

    echo implode(',&nbsp;&nbsp;',$text).'<br />';

    echo html_writer::nonempty_tag('span', get_string('printingoptions', 'examregistrar').': ' , array('class'=>'examregistrarmanageheaders'));
    $text = array();
    $actionurl->param('action', 'roomprintoptions');
    $text[] = html_writer::link($actionurl, get_string('roomprintoptions', 'examregistrar'));
    $actionurl->param('action', 'examprintoptions');
    $text[] = html_writer::link($actionurl, get_string('examprintoptions', 'examregistrar'));
    $actionurl->param('action', 'binderprintoptions');
    $text[] = html_writer::link($actionurl, get_string('binderprintoptions', 'examregistrar'));
    $actionurl->param('action', 'userlistprintoptions');
    $text[] = html_writer::link($actionurl, get_string('userlistprintoptions', 'examregistrar'));
    $actionurl->param('action', 'bookingprintoptions');
    $text[] = html_writer::link($actionurl, get_string('bookingprintoptions', 'examregistrar'));
    $actionurl->param('action', 'venueprintoptions');
    $text[] = html_writer::link($actionurl, get_string('venueprintoptions', 'examregistrar'));
    $actionurl->param('action', 'venuefaxprintoptions');
    $text[] = html_writer::link($actionurl, get_string('venuefaxprintoptions', 'examregistrar'));



    echo implode(',&nbsp;&nbsp;',$text).'<br />';

    echo html_writer::empty_tag('hr');
    echo $output->container_end();


/// Session & venue selector

    echo $output->container_start('examregistrarfilterform clearfix ');
        $sessionmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'examsessions', 'examsessionitem', $examregprimaryid, '', '', array(), 't.examdate ASC');
        $select = new single_select(new moodle_url($baseurl), 'session', $sessionmenu, $session, '');
        $select->label = get_string('examsessionitem', 'examregistrar');
        $select->set_label(get_string('examsessionitem', 'examregistrar'), array('class'=>' singleselect filter'));
        $select->class .= ' filter ';
    echo $output->render($select);
        $venueelement = examregistrar_get_venue_element($examregistrar);
        $venuemenu = examregistrar_get_referenced_namesmenu($examregistrar, 'locations', 'locationitem', $examregprimaryid, 'choose', '', array('locationtype'=>$venueelement));
        //natcasesort($venuemenu);
        $select = new single_select(new moodle_url($baseurl), 'venue', $venuemenu, $bookedsite);
        $select->set_label(get_string('venue', 'examregistrar'), array('class'=>'singleselect  filter'));
        $select->class .= ' filter ';
    echo $output->render($select);
    echo $output->container_end();

/// main part of interface

$sessionname = '';
if($session) {
    $sql = "SELECT s.id, s.examsession, es.name, es.idnumber, s.examdate, ep.name AS periodname, ep.idnumber AS periodidnumber
            FROM {examregistrar_examsessions} s
            JOIN {examregistrar_elements} es ON es.examregid = s.examregid AND es.type = 'examsessionitem' AND s.examsession = es.id
            JOIN {examregistrar_periods} p ON s.examregid = p.examregid AND s.period = p.id
            JOIN {examregistrar_elements} ep ON ep.examregid = p.examregid AND ep.type = 'perioditem' AND p.period = ep.id
            WHERE s.id = :id ";
    $examsession = $DB->get_record_sql($sql, array('id'=>$session), MUST_EXIST);
    $sessionname = $output->formatted_name($examsession->periodname, $examsession->periodidnumber).'; ';
    $sessionname .= $output->formatted_name($examsession->name, $examsession->idnumber).',  '. userdate($examsession->examdate, get_string('strftimedaydate'));
}

    echo $output->heading($sessionname, 3, 'main');
    echo $output->container('', 'clearfix');

    echo $output->container_start('examregqualitycontrol clearfix ');
    print_collapsible_region_start('managesession', 'showhideexamregqualitycontrol', get_string('qualitycontrol', 'examregistrar'),'examregqualitycontrol', true, false);

        list($totalbooked, $totalseated) = examregistrar_qc_counts($session, $bookedsite);

        $class = ($totalbooked != $totalseated) ?  ' busyalloc ' : ' freealloc ';
        $count = get_string('totalseated', 'examregistrar', $totalseated).
                           ' / '.get_string('totalbooked', 'examregistrar', $totalbooked);
        echo html_writer::div($count, $class);
        $failures = examregistrar_booking_seating_qc($session, $bookedsite, $esort);

        if($failures) {

            if(!$bookedsite) {
                $venueelement = examregistrar_get_venue_element($examregistrar);
                $venuemenu = examregistrar_get_referenced_namesmenu($examregistrar, 'locations', 'locationitem', $examregprimaryid, '', '', array('locationtype'=>$venueelement));
                $venuefails = $venuemenu;
                foreach($venuefails as $key => $v) {
                    $venuefails[$key] = 0;
                }
                foreach($failures as $fail) {
                     $venuefails[$fail->bookedsite] += 1;
                }
                $numfail = 0;
                foreach($venuemenu as $key => $venue) {
                    $class = $venuefails[$key] ? ' busyalloc ' : ' freealloc ';
                    if($venuefails[$key]) {
                        $numfail += 1;
                    }
                    $venuemenu[$key] = html_writer::span($venue.': '.get_string('unallocatedbooking', 'examregistrar', $venuefails[$key]), $class);
                }
                $venues = html_writer::alist($venuemenu);

                echo print_collapsible_region($venues, 'qcuserlist', 'showhidevenuelistfail'.$session, get_string('qcvenuesnonallocated', 'examregistrar')." ($numfail) ",'userlist', true, true);
            }
            $failusers = array();
            foreach($failures as $fail) {
                $failusers[] = fullname($fail, false, 'lastname firstname').' : '.$fail->programme.'-'.$fail->shortname ;
            }
            $numfail = count($failusers);
            $failusers = html_writer::alist($failusers);
            echo print_collapsible_region($failusers, 'qcuserlist', 'showhideuserlistfail'.$session, get_string('qcbookingsnonallocated', 'examregistrar')." ($numfail) ",'userlist', true, true);
            //print_collapsible_region($contents, $classes, $id, $caption, $userpref = '', $default = false, $return = false)
        } else {
            $class = ' freealloc ';
            $numfail = 0;
            if(!$bookedsite) {
                echo html_writer::span(get_string('qcvenuesnonallocated', 'examregistrar').": $numfail ", $class);
            }
            echo html_writer::span(get_string('qcbookingsnonallocated', 'examregistrar').": $numfail ", $class);
        }
        //Total student-exam booked :  número
        //Total student-exam allocated :  número

        //Separate student-exam bookings not allocated: número, lista desplegable

        //Separate exams booked not allocated: número, lista desplegable

        // Exams without any booking in this session : nº, link, lista desplegable (programme-shortname)

        // Rooms without staff número, lista desplegable
        $sql = "SELECT COUNT(DISTINCT sr.id)
                    FROM {examregistrar_session_rooms} sr
                    JOIN {examregistrar_locations} l ON sr.roomid = l.id
                    WHERE sr.examsession = :session
                    AND EXISTS (SELECT 1
                                    FROM {examregistrar_session_seats} ss
                                    WHERE ss.examsession = sr.examsession AND ss.roomid = sr.roomid AND sr.available = 1)

                    AND NOT EXISTS (SELECT 1
                                        FROM {examregistrar_staffers} s
                                        WHERE s.examsession = sr.examsession AND s.locationid = sr.roomid AND s.visible = 1 AND sr.available = 1)
                    ";
        $params = array('session'=>$session);
        $roomsnonstaffed = $DB->count_records_sql($sql, $params);
        $class = ($roomsnonstaffed > 0) ?  ' busyalloc ' : ' freealloc ';
        echo html_writer::div(get_string('countroomsnonstaffed', 'examregistrar', $roomsnonstaffed), $class);



        // Staff without room número, lista desplegable
        $courseids = $DB->get_fieldset_select('examregistrar_exams', 'courseid', ' courseid <> 0 AND  examsession = ? ', array($session));
        $users = array();
        foreach($courseids as $courseid) {
            $coursecontext = context_course::instance($courseid);
            $managers = get_enrolled_users($coursecontext, 'moodle/course:manageactivities', 0, 'u.id, u.firstname, u.lastname, u.idnumber, u.picture', ' u.lastname ASC ');
            foreach($managers as $uid => $user) {
                if(!isset($users[$uid]) && !$DB->record_exists('examregistrar_staffers', array('examsession'=>$session, 'userid'=>$uid, 'visible'=>1))) {
                    $users[$uid] = $user;
                }
            }
        }
        $class = (count($users) > 0) ?  ' busyalloc ' : ' freealloc ';
        echo html_writer::div(get_string('qcstaffnonallocated', 'examregistrar').': '.count($users), $class);


    print_collapsible_region_end(false);
    echo $output->container_end();

    echo $output->container_start('examregprintoperators clearfix ');
    print_collapsible_region_start('managesession', 'showhideexamregprintoperators', get_string('printingbuttons', 'examregistrar'),'examregprintoperators', true, false);

        $downloadurl = new moodle_url('/mod/examregistrar/download.php', array('id' => $cm->id, 'edit'=>'assignseats',
                                                                               'session'=>$session, 'venue'=>$bookedsite));
        echo $output->container_start('examregprintbuttons clearfix ');
        $downloadurl->param('down', 'printroompdf');
        $downloadurl->param('rsort', $rsort);
        echo $output->single_button($downloadurl, get_string('printroompdf', 'examregistrar'), 'post', array('class'=>' clearfix '));
        $downloadurl->param('down', 'printroomsumarypdf');
                $downloadurl->param('rsort', $rsort);
        echo $output->single_button($downloadurl, get_string('printroomsummarypdf', 'examregistrar'), 'post', array('class'=>' clearfix '));

        $downloadurl->param('down', 'printexampdf');
        echo $output->single_button($downloadurl, get_string('printexampdf', 'examregistrar'), 'post', array('class'=>' clearfix '));
        $downloadurl->param('down', 'printbinderpdf');
        echo $output->single_button($downloadurl, get_string('printbinderpdf', 'examregistrar'), 'post', array('class'=>' clearfix '));
        $downloadurl->param('down', 'printuserspdf');
        echo $output->single_button($downloadurl, get_string('printuserspdf', 'examregistrar'), 'post', array('class'=>' clearfix '));

        $downloadurl = new moodle_url('/mod/examregistrar/download.php', array('id' => $cm->id, 'edit'=>'assignseats',
                                                                               'session'=>$session, 'venue'=>$bookedsite));
//         if($bookedsite) {
//         $downloadurl->param('down', 'getvenuezip');
//             echo $output->single_button($downloadurl, get_string('getvenuezip', 'examregistrar'), 'post', array('class'=>' clearfix '));
//
//         }
        echo $output->container_end();

    print_collapsible_region_end(false);

    echo $output->container_end();

    echo $output->container('', 'clearfix');

    echo $output->container_start('examregsessionrooms clearfix ');

        $sessionrooms = examregistrar_get_session_rooms($session, $bookedsite, $rsort,  true, 1);
        $sessionroomsurl = new moodle_url('/mod/examregistrar/manage/assignsessionrooms.php',
                                                    $baseurl->params() + array('action'=>'sessionrooms', 'edit'=>''));
        $sessionroomslink = html_writer::link($sessionroomsurl, get_string('sessionrooms', 'examregistrar'));


        echo $output->container_start('managesessionheader clearfix ');
            echo $output->container(get_string('roomsinsession', 'examregistrar', count($sessionrooms)),  'managesessioniteminfo');
            echo $output->heading($sessionroomslink,  4, 'managesesionactionlink');
        echo $output->container_end();
        echo $output->container('', 'clearfix');

    print_collapsible_region_start('managesession', 'showhideexamregsessionrooms', get_string('managesessionrooms', 'examregistrar'),'examregsessionrooms', true, false);

        if($sessionrooms) {

            // form for ordering
            $baseurl->param('esort', $esort);
            $sorting = array(''=>get_string('sortroomname', 'examregistrar'),
                            'seats'=>get_string('sortseats', 'examregistrar'),
                            'freeseats'=>get_string('sortfreeseats', 'examregistrar'),
                            'booked'=>get_string('sortbooked', 'examregistrar'));
            $select = new single_select($baseurl, 'rsort', $sorting, $rsort, '');
            $select->set_label(get_string('sortby', 'examregistrar'));
            echo $output->render($select);

            $table = new html_table();
            $table->attributes = array('class'=>'flexible generaltable examregsessionroomstable' );
            $tableheaders = array(get_string('room', 'examregistrar'),
                                    get_string('seats', 'examregistrar'),
                                    get_string('exams', 'examregistrar'),
                                    get_string('staffers', 'examregistrar'),
                                    get_string('status'),
                                    );
            $table  ->head = $tableheaders;
            $table->colclasses = array();

            $strstaffers = get_string('roomstaffers', 'examregistrar');
            $staffurl = new moodle_url('/mod/examregistrar/manage/assignroomstaffers.php', array('id'=>$cm->id, 'action'=>'roomstaffers', 'edit'=>''));
            $iconaddstaff = new pix_icon('t/enrolusers', $strstaffers, 'moodle', array('class'=>'icon', 'title'=>$strstaffers));
                //$cellattempt = $name.'&nbsp;   &nbsp;'.$output->action_icon($url, $icon);

                //$buttons[] = html_writer::link($url, html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/assignroles'), 'alt'=>$strstaffers, 'class'=>'iconsmall')), array('title'=>$strstaffers));

            foreach($sessionrooms as $room) {
                //print_object($room);
                $cellroom = $output->formatted_name($room->name, $room->idnumber);
                if(!$bookedsite) {
                    $cellroom = $output->formatted_name($room->venuename, $room->venueidnumber);
                }
                $cellseats = "&nbsp;&nbsp;  {$room->booked} / {$room->seats} ";
                $seatclass = ($room->booked > $room->seats) ?  ' busyalloc ' : ' freealloc ';
                $cellseats = html_writer::span($cellseats, $seatclass);
                $cellexams = '';

                $list = array();
                if($exams = examregistrar_get_sessionroom_exams($room->id, $session, $bookedsite)) {
                    foreach($exams as $exam) {
                        $examclass = new examregistrar_exam($exam);
                        $list[] = $examclass->get_exam_name(true, true);  //$exam->programme.'-'.$exam->shortname;
                    }
                    $cellexams = implode('<br />', $list);
                }

                $cellstaff = '';
                $staffers = examregistrar_get_room_staffers_list($room->id, $room->examsession);
                $staffurl->params(array('session'=>$room->examsession, 'room'=>$room->id));
                if($staffers) {
                    $cellstaff = print_collapsible_region($staffers, 'userlist', 'showhideuserlist'.$room->id, get_string('roomstaff', 'examregistrar'),'userlist', true, true);
                } elseif($cellexams){
                    $cellstaff = html_writer::span(get_string('notyet', 'examregistrar'), 'notifyproblem');
                }

                $cellaction = $output->action_icon($staffurl, $iconaddstaff);
                if($bookedsite && $room->booked && $candownload) {
                        $filename = get_roomzip_filename($session, $bookedsite, $room);
                        if($file = examregistrar_file_get_file($context->id, $room->examsession, 'sessionrooms', $filename)) {
                            $lastallocated = $DB->get_records_menu('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'roomid'=>$room->id),
                                                            'timemodified DESC', 'id, timemodified', 0, 1);
                            $lastallocated = ($lastallocated) ? reset($lastallocated) : 0;
                            if($file->get_timemodified() > $lastallocated) {
                                $filename = $file->get_filename();
                                $message = get_string('printroomwithexams', 'examregistrar');
                                $url = examregistrar_file_encode_url($context->id, $session, 'sessionrooms', $filename, false, true);
                                $icon = new pix_icon('printgreen', $message, 'examregistrar', array('class' => 'icon'));
                                $item = $output->action_icon($url, $icon);
                                $cellaction .= $item;
                            }
                        }
                }
                $cellaction = html_writer::span($cellaction, 'examreviewstatusicons');


                $row = new html_table_row(array($cellroom, $cellseats, $cellexams, $cellstaff, $cellaction));
                $table->data[] = $row;
            }
            echo html_writer::table($table);

            echo $output->container_start('managesessionfooter clearfix ');

                $stafffromexamurl = new moodle_url('/mod/examregistrar/manage/action.php',
                                                        $baseurl->params() + array('action'=>'stafffromexam', 'edit'=>''));
                $stafffromexamslink = html_writer::link($stafffromexamurl, get_string('stafffromexam', 'examregistrar'));

                echo $output->heading($stafffromexamslink,  4, 'managesesionactionlink');

                if($bookedsite) {
                    $genroompdfsurl = new moodle_url('/mod/examregistrar/download.php', array('id' => $cm->id, 'edit'=>'assignseats',
                                                                                    'session'=>$session, 'venue'=>$bookedsite,
                                                                                    'down'=>'genvenuezips'));
                    $genroompdfslink = html_writer::link($genroompdfsurl, get_string('generateroomspdfs', 'examregistrar'));
                    echo $output->heading($genroompdfslink,  4, 'managesesionactionlink');
                }
            echo $output->container_end();

        }



    print_collapsible_region_end(false);
    echo $output->container_end();

    echo $output->container_start('examregsessionexams clearfix ');

        $sessionexams = examregistrar_get_session_exams($session, $bookedsite, $esort,  true, true);

        $sessionexamsurl = new moodle_url('/mod/examregistrar/manage/assignseats.php',
                                                $baseurl->params() + array('edit'=>'session_rooms'));
        $sessionexamslink = html_writer::link($sessionexamsurl, get_string('assignseats', 'examregistrar'));
        echo $output->container_start('managesessionheader clearfix ');
            echo $output->container(get_string('examsinsession', 'examregistrar', count($sessionexams)),  'managesessioniteminfo');
            echo $output->heading($sessionexamslink,  4, 'managesesionactionlink');
        echo $output->container_end();
        echo $output->container('', 'clearfix');

    print_collapsible_region_start('managesession', 'showhideexamregsessionexams', get_string('managesessionexams', 'examregistrar'),'examregsessionexams', true, false);

        if($sessionexams) {
            // form for ordering
            $baseurl->param('rsort', $rsort);
            $sorting = array(''=>get_string('sortprogramme', 'examregistrar'),
                            'fullname'=>get_string('sortfullname', 'examregistrar'),
                            'booked'=>get_string('sortbooked', 'examregistrar'),
                            'allocated'=>get_string('sortbooked', 'examregistrar'));
            $select = new single_select($baseurl, 'esort', $sorting, $esort, '');
            $select->set_label(get_string('sortby', 'examregistrar'));
            echo $output->render($select);

            $table = new html_table();
            $table->attributes = array('class'=>'flexible generaltable examregsessionroomstable' );
            $tableheaders = array(get_string('exam', 'examregistrar'),
                                    get_string('allocated', 'examregistrar'),
                                    get_string('rooms', 'examregistrar'),
                                    get_string('status'),
                                    );
            $table  ->head = $tableheaders;
            $table->colclasses = array();

            $strstaffers = get_string('roomstaffers', 'examregistrar');

            $straddcall = get_string('addextracall', 'examregistrar');

            $addcallurl = new moodle_url('/mod/examregistrar/manage/action.php', $baseurl->params() + array('action'=>'addextracall'));
            $iconaddcall = new pix_icon('i/manual_item', $straddcall, 'moodle', array('class'=>'icon', 'title'=>$straddcall));


            $buttons[] = html_writer::link($actionurl, html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/manual_item'), 'alt'=>$straddcall, 'class'=>'iconsmall')), array('title'=>$straddcall));


            foreach($sessionexams as $exam) {
                //print_object($exam);
                if(!isset($exam->booked)) {
                    $exam->booked = 0;
                }
                $exam->examregid = $examregprimaryid;
                $exam->annuality = $examregistrar->annuality;

                $examclass = new examregistrar_exam($exam);
                $examclass->examregid = $examregprimaryid;
                $cellexam = $examclass->get_exam_name(true, true, true, true);

                $cellseats = "&nbsp;&nbsp;  {$exam->allocated} / {$exam->booked} ";
                $seatclass = ($exam->allocated != $exam->booked) ?  ' busyalloc ' : ' freealloc ';
                $cellseats = html_writer::span($cellseats, $seatclass);

                $cellrooms = '';
                $list = array();
                if($rooms = examregistrar_get_sessionexam_rooms($exam->id, $session, $bookedsite)) {
                    foreach($rooms as $room) {
                        $name = $output->formatted_name($room->name, $room->idnumber);
                        if(!$bookedsite) {
                            $name = $output->formatted_name($room->venuename, $room->venueidnumber);
                        }
                        $list[] = $name;
                    }
                    $cellrooms = implode('<br />', $list);
                }

                $cellaction = '';
                if($exam->callnum < 0) {
                    $cellaction = html_writer::span('R'.abs($exam->callnum), 'error').' ';
                }
                $examclass = new examregistrar_exam($exam);
                $exam->examdate = $examclass->get_examdate();
                $message = $examclass->set_valid_file();
                $ccontext = context_course::instance($exam->courseid);
                $candownload_incourse = has_capability('mod/examregistrar:download',$ccontext);
                $item = '';
                $component = '';
                if(!$message && $examclass->examfile) {
                    $message = get_string('printexam', 'examregistrar');
                    if($candownload_incourse) {
                        $url = examregistrar_file_encode_url($ccontext->id, $examclass->examfile, 'exam');
                        //http://localhost/moodle26ulpgc/pluginfile.php/5438/mod_examregistrar/exam/109/4036-46052-ORDC1-F-R6.pdf
                        $icon = new pix_icon('printgreen', $message, 'examregistrar', array('class' => 'icon'));
                        $item = $output->action_icon($url, $icon);
                    } else {
                        $icon = 'printgreen';
                        $component = 'examregistrar';
                    }
                } elseif($examclass->examfile) {
                    $icon = 'i/risk_spam';
                } else {
                    $icon = 'i/risk_xss';
                }
                if(!$item) {
                    $icon = new pix_icon($icon, $message, $component, array('class' => 'icon'));
                    $item = $output->render($icon);
                }
                $cellaction .= $item;

                /// TODO TODO this code is duplicated from render_examregistrar_exams_course(), renderable.php
                /// FACTORIZE

                if($exam->examdate < $now && $examclass->examfile)  {
                    if($examfile = $DB->get_record('examregistrar_examfiles', array('id'=>$examclass->examfile))) {
                        if($examfile->taken > 0) {
                            if($filenames = examregistrar_file_get_filename($ccontext->id, $examfile->id, 'responses', true)) {
                                $celltaken = '';
                                $icon = 'i/completion-manual-enabled';
                                $strexamresponses = get_string('examresponses', 'examregistrar');
                            } else {
                                $icon = 'i/completion-auto-fail';
                                $strexamresponses = get_string('filemissing', 'moodle', get_string('file'));
                            }
                            $url = new moodle_url('view.php', $baseurl->params()+array('action'=>'response_files', 'examf'=>$examfile->id));
                            $icon = new pix_icon($icon, $strexamresponses, '', array('class' => 'iconsmall'));
                            $item = $output->action_icon($url, $icon); //$output->render($icon);
                            $cellaction .= $item;
                        }
                    }
                }
                $cellaction = html_writer::span($cellaction, 'examreviewstatusicons');

                $row = new html_table_row(array($cellexam, $cellseats, $cellrooms, $cellaction));
                $table->data[] = $row;
            }
            echo html_writer::table($table);
        }

    print_collapsible_region_end(false);
    echo $output->container_end();


    echo $output->container('', 'clearfix');

    echo $output->container_start('examregsessionresponses clearfix ');

        $config = get_config('examregistrar');
        $sessiondir =  clean_filename($config->distributedfolder);
        $sessiondir = '/'.$sessiondir.'/';
        $pending = array();
        $distributed = array();
        $fs = get_file_storage();
        $pending = $fs->get_directory_files($context->id, 'mod_examregistrar', 'sessionresponses', $session, '/', false, false);
        $distributed = $fs->get_directory_files($context->id, 'mod_examregistrar', 'sessionresponses', $session, $sessiondir, false, false);

        echo $output->container_start('managesessionheader clearfix ');
            echo $output->container(get_string('pendingresponsefiles', 'examregistrar', count($pending)),  'managesessioniteminfo');
            $sessionurl = new moodle_url('view.php', $baseurl->params());

            $sessionurl->param('action', 'session_files');
            $sessionurl->param('area', 'sessionresponses');
            $sessionfileslink = html_writer::link($sessionurl, get_string('loadsessionresponses', 'examregistrar'));
            echo $output->heading($sessionfileslink,  4, 'managesesionactionlink');

            $sessionurl->param('action', 'session_responses');
            $sessionresponseslink = html_writer::link($sessionurl, get_string('assignsessionresponses', 'examregistrar'));
            if($pending) {
                echo $output->heading($sessionresponseslink,  4, 'managesesionactionlink');
            }
            if($distributed) {
                $sessionurl->param('action', 'session_files');
                $sessionurl->param('area', 'sessioncontrol');
                $sessionresponseslink = html_writer::link($sessionurl, get_string('loadsessioncontrol', 'examregistrar'));
                echo $output->heading($sessionresponseslink,  4, 'managesesionactionlink');
            }
        echo $output->container_end();
        echo $output->container('', 'clearfix');

    print_collapsible_region_start('managesession', 'showhideexamregsessionresponses', get_string('managesessionresponses', 'examregistrar'),'examregsessionresponses', true, false);

        echo $output->container(get_string('distributedresponsefiles', 'examregistrar', count($distributed)),  'managesessioniteminfo');
        if($pending ) {
            echo $output->container_start('managesessioniteminfo');
            echo get_string('unknownresponsefiles', 'examregistrar', count($pending));
            $list = array();
            foreach($pending as $file) {
                $list[] = $file->get_filename();
            }
            echo html_writer::alist($list);
            echo $output->container_end();
        }

    print_collapsible_region_end(false);
    echo $output->container_end();

    echo $output->container_start('examregspecialexams clearfix ');

        $sessionexams = examregistrar_get_session_exams($session, $bookedsite, $esort,  true, false, true);

        echo $output->container_start('managesessionheader clearfix ');
        echo $output->container(get_string('specialexamsinsession', 'examregistrar', count($sessionexams)),  'managesessioniteminfo');
        echo $output->container_end();
        echo $output->container('', 'clearfix');

    print_collapsible_region_start('managesession', 'showhideexamregspecialexams', get_string('managespecialexams', 'examregistrar'),'examregspecialexams', true, false);

        echo '<form id="examregistrarfilterform" action="'.$CFG->wwwroot.'/mod/examregistrar/manage/action.php" method="post">'."\n";;
        echo html_writer::input_hidden_params($baseurl);
        echo html_writer::empty_tag('input', array('name'=>'action', 'type'=>'hidden', 'value'=>'addextrasessioncall'));
        echo html_writer::label(get_string('specialfor', 'examregistrar').'&nbsp;', 'examshort', false, array('class' => 'accesshidexx'));
        echo html_writer::empty_tag('input', array('name'=>'examshort', 'type'=>'text', 'value'=>'', 'size'=>8));
        echo '&nbsp;  ';
        echo '<input type="submit" value="'.get_string('addspecial', 'examregistrar').'" />'."\n";
        echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'addspecial', 'value'=>'addspecial'));
        echo '</form>'."\n";


    print_collapsible_region_end(false);
    echo $output->container_end();

    echo $output->container('', 'clearfix');








