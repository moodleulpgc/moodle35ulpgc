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
 * Displays the page for reviewing exams & exam status for a course or category
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

include_once($CFG->dirroot.'/mod/examregistrar/manage/manage_forms.php');
include_once($CFG->dirroot.'/mod/examregistrar/classes/event/examfile_reviewed.php');

require_capability('mod/examregistrar:review',$context);

$baseurl = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cm->id,'tab'=>'review'));
$tab = 'review';

$period   = optional_param('period', '', PARAM_INT);
$now = time();
//$now = strtotime('4 may 2014') + 3605;

$term   = optional_param('term', 0, PARAM_INT);
$searchname = optional_param('searchname', '', PARAM_TEXT);
$searchid = optional_param('searchid', '', PARAM_INT);
$sort = optional_param('sorting', 'shortname', PARAM_ALPHANUM);
$order = optional_param('order', 'ASC', PARAM_ALPHANUM);
$baseparams = array('exreg' => $examregistrar, 'id'=>$cm->id, 'tab'=>$tab);
$reviewparams = array('period'=>$period,
                      'term'=>$term,
                      'searchname'=>$searchname,
                      'searchid'=>$searchid,
                      'programme'=>$programme,
                      'sorting'=>$sort,
                      'order'=>$order );

$reviewurl = new moodle_url($baseurl, $reviewparams);

$annuality =  examregistrar_get_annuality($examregistrar);

$canviewall = has_capability('mod/examregistrar:viewall', $context);

$courses = examregistrar_get_user_courses($examregistrar, $course, $reviewparams, array('mod/examregistrar:review'), $canviewall);

////////////////////////////////////////////////////////////////////////////////////////

///          TODO                    TODO                     TODO
/// TODO     make examregistrar - makeexam interaction with EVENTS     TODO

/// TODO     make examregistrar - makeexam interaction with EVENTS     TODO
///          TODO                    TODO                     TODO

//print_object($_GET);
//print_object($_POST);

///process actions

$confirm = optional_param('confirm', 0, PARAM_BOOL);
$upload = optional_param('upload', 0, PARAM_INT);
$setreview = optional_param('setreview', 0, PARAM_INT);
$toggleprint = optional_param('toggleprint', 0, PARAM_INT);
$attempt = optional_param('attempt', 0, PARAM_INT);
$status = optional_param('status', '', PARAM_ALPHA);
$delete = optional_param('delete', 0, PARAM_INT);
$new = optional_param('new', 0, PARAM_INT);
$exemption = optional_param('exemption', '', PARAM_ALPHA);
$cancel = optional_param('cancel', '', PARAM_ALPHA);

if($cancel) {
    redirect($reviewurl);
}


if($delete) {
    if(!$confirm) {
            $examfile = $DB->get_record('examregistrar_examfiles', array('id'=>$delete), '*', MUST_EXIST);
            $exam = $DB->get_record('examregistrar_exams', array('id'=>$examfile->examid), '*', MUST_EXIST);
            $examcourse = $DB->get_record('course', array('id'=>$exam->courseid), 'id, fullname, shortname, idnumber', MUST_EXIST);
            $examdata = new stdClass;
            $examdata->coursename = $examcourse->shortname.' - '.$examcourse->fullname;
            $examdata->annuality = $output->formatted_name_fromid($exam->annuality, '');
            $examdata->programme = $exam->programme;
            $examdata->period = $output->formatted_name_fromid($exam->period, 'periods');
            $examdata->examscope = $output->formatted_name_fromid($exam->examscope);
            $examdata->callnum = $exam->callnum;
            $examdata->attempt = $examfile->attempt;
            $strapproved = get_string('approved', 'examregistrar');
            $strrejected = get_string('rejected', 'examregistrar');
            $strsent = get_string('sent', 'examregistrar');
            switch($examfile->status) {
                case EXAM_STATUS_SENT       : $icon = $OUTPUT->pix_icon('sent', $strsent, 'mod_examregistrar', array('class'=>'icon', 'title'=>$strsent));
                                                break;
                case EXAM_STATUS_WAITING    : $icon = $OUTPUT->pix_icon('waiting', $strsent, 'mod_examregistrar', array('class'=>'icon', 'title'=>$strsent));
                                                break;
                case EXAM_STATUS_REJECTED   : $icon = $OUTPUT->pix_icon('rejected', $strrejected, 'mod_examregistrar', array('class'=>'icon', 'title'=>$strrejected));
                                                break;
                case EXAM_STATUS_APPROVED   :
                case EXAM_STATUS_VALIDATED  : $icon = $OUTPUT->pix_icon('approved', $strapproved, 'mod_examregistrar', array('class'=>'icon', 'title'=>$strapproved));
                                                        break;
                default : $icon = $OUTPUT->pix_icon('i/risk_dataloss', $strsent, 'moodle', array('class'=>'icon', 'title'=>''));
            }
            $examdata->status = $status.' '.$icon;

            $message = get_string('confirm_delete', 'examregistrar', $examdata);
            $confirmurl = new moodle_url($reviewurl, array('delete'=>$examfile->id, 'issue'=>$examfile->reviewid, 'confirm' => 1));
            echo $output->confirm($message, $confirmurl, $reviewurl);
            echo $output->footer();
            die;

    } elseif(confirm_sesskey()){
        if($DB->delete_records('examregistrar_examfiles', array('id'=>$delete))) {
            // update makeexam attempts
            $DB->set_field_select('quiz_makeexam_attempts', 'examfileid', 0, " examfileid = ? ", array($delete));
            // update tracker issue
            if($issue = optional_param('issue', 0, PARAM_INT)) {
                $DB->set_field('tracker_issue', 'status', 6, array('id'=>$issue)); // 6 is TRANSFERRED
            }
        }
    }
}

if($status) {
    // send change status & creates tracker issue
    if(!$confirm) {
            $examfile = $DB->get_record('examregistrar_examfiles', array('id'=>$attempt), '*', MUST_EXIST);
            $exam = $DB->get_record('examregistrar_exams', array('id'=>$examfile->examid), '*', MUST_EXIST);
            $examcourse = $DB->get_record('course', array('id'=>$exam->courseid), 'id, fullname, shortname, idnumber', MUST_EXIST);
            $examdata = new stdClass;
            $examdata->coursename = $examcourse->shortname.' - '.$examcourse->fullname;
            $examdata->annuality = $output->formatted_name_fromid($exam->annuality, '');
            $examdata->programme = $exam->programme;
            $examdata->period = $output->formatted_name_fromid($exam->period, 'periods');
            $examdata->examscope = $output->formatted_name_fromid($exam->examscope);
            $examdata->callnum = $exam->callnum;
            $examdata->action = get_string($status, 'examregistrar');

            $message = get_string('confirm_status', 'examregistrar', $examdata);
            $confirmurl = new moodle_url($reviewurl, array('attempt'=>$attempt, 'status'=>$status, 'confirm' => 1));
            echo $output->confirm($message, $confirmurl, $reviewurl);
            echo $output->footer();
            die;

    } elseif(confirm_sesskey()){
        $examfile = $DB->get_record('examregistrar_examfiles', array('id'=>$attempt), '*', MUST_EXIST);
        $examfile->timemodified = $now;
        if($status == 'send') {
            $examfile->status = EXAM_STATUS_SENT;
            $examfile->reviewid = examregistrar_review_addissue($examregistrar, $course, $examfile);
        }
        if($status == 'approve') {
            $examfile->status = EXAM_STATUS_APPROVED;
            $examfile->timeapproved = $now;
            $examfile->reviewerid = $USER->id;
            if(warning_questions_used($examfile)) {
                $examfile->printmode = 1;
            }
        }
        if($status == 'reject') {
            $examfile->status = EXAM_STATUS_REJECTED;
            $examfile->timerejected = $now;
            $examfile->reviewerid = $USER->id;
        }
        if($DB->update_record('examregistrar_examfiles', $examfile)) {
            if($status == 'reject') {
                // release all used questions in makeexam
                require_once($CFG->dirroot.'/mod/quiz/report/makeexam/lib.php');
                quiz_makeexam_release_questions($examfile->id);
            }
            if($status == 'approve') {
                require_once($CFG->dirroot.'/mod/quiz/report/makeexam/lib.php');
                quiz_makeexam_validate_questions($examfile->id);
                // make sure is single approved examfile for this exam
                $select = " examid = :examid AND status = :status AND id <> :id ";
                $DB->set_field_select('examregistrar_examfiles', 'status', EXAM_STATUS_WAITING, $select,
                                      array('examid'=>$examfile->examid, 'status'=>EXAM_STATUS_APPROVED, 'id'=>$examfile->id));
            }
            // log the action
            $eventdata = array();
            $eventdata['objectid'] = $examfile->id;
            $eventdata['context'] = $context;
            $eventdata['userid'] = $USER->id;
            $eventdata['other'] = array();
            $eventdata['other']['examregid'] = $examregistrar->id;
            $eventdata['other']['status'] = $status;
            $eventdata['other']['examid'] = $examfile->examid;
            $eventdata['other']['attempt'] = $examfile->attempt;
            $eventdata['other']['idnumber'] = $examfile->idnumber;
            $event = \mod_examregistrar\event\examfile_reviewed::create($eventdata);
            $event->add_record_snapshot('examregistrar_examfiles', $examfile);
            $event->trigger();
        }
    }
}

if($setreview) {
    $exam = $DB->get_record('examregistrar_exams', array('id'=>$setreview), '*', MUST_EXIST);
    $examfile = $DB->get_record('examregistrar_examfiles', array('id'=>$attempt), '*', MUST_EXIST);
    if($examfile->reviewid == 0) {
        // now create tracker issue for examfile
        $issueid = examregistrar_review_addissue($examregistrar, $course, $examfile);
    }
}

if($toggleprint) {
    $printmode = $DB->get_field('examregistrar_examfiles', 'printmode',  array('id'=>$attempt));
    if($printmode !== false) {
        $printmode = $printmode ? 0 : 1;
        if($DB->set_field('examregistrar_examfiles', 'printmode', $printmode,  array('id'=>$attempt))) {
            // log the action
            $eventdata = array();
            $eventdata['objectid'] = $attempt;
            $eventdata['context'] = $context;
            $eventdata['userid'] = $USER->id;
            $eventdata['other'] = array();
            $eventdata['other']['examregid'] = $examregistrar->id;
            $eventdata['other']['printmode'] = $printmode;
            $event = \mod_examregistrar\event\examfile_printmodeset::create($eventdata);
            $event->trigger();
        }
    }
}

if($upload) {

    $attempts = array();
    $examcourse = null;
    if($exam = $DB->get_record('examregistrar_exams', array('id'=>$upload), '*', MUST_EXIST)) {
        $attempts = $DB->get_records('examregistrar_examfiles', array('examid'=>$upload), ' attempt ASC ');
        $examcourse = $DB->get_record('course', array('id'=>$exam->courseid), 'id, fullname, shortname, idnumber', MUST_EXIST);
    }
    $examdata = new stdClass;
    $examdata->coursename = $examcourse->shortname.' - '.$examcourse->fullname;
    $examdata->annuality = $output->formatted_name_fromid($exam->annuality, '');
    $examdata->programme = $exam->programme;
    $examdata->period = $output->formatted_name_fromid($exam->period, 'periods');
    $examdata->examscope = $output->formatted_name_fromid($exam->examscope);
    $examdata->callnum = $exam->callnum;
    $examdata->examsession = $output->formatted_name_fromid($exam->examsession, 'examsessions');
    $examdata->examdate = userdate($DB->get_field('examregistrar_examsessions', 'examdate', array('id'=>$exam->examsession)), get_string('strftimedaydate'));

    $mform = new examregistrar_upload_examfile_form(null, $baseparams + array('reviewparams'=>$reviewparams,
                                                                              'upload'=>$upload,
                                                                              'examdata'=>$examdata,
                                                                              'attempt'=>$attempt,
                                                                              'attempts'=>$attempts,
                                                                            ));

    if(($formdata = $mform->get_data()) && confirm_sesskey()) { /// && confirm_sesskey()) { /// TODO TODO
        /// there is data, process and go to table again

//         print_object($formdata);
//         print_object(" ------ formdata   ------ ");

        $now = time();
        // first check examfile attempt existence, update or create it
        if($attempt) {
            // if there is an attempt, we are updating
            $examfile = $DB->get_record('examregistrar_examfiles', array('id'=>$attempt), '*', MUST_EXIST);
            $examfile->userid = $USER->id;
            $examfile->timemodified =  $now;
            $examfile->printmode = $formdata->printmode;
            if($formdata->status >= 0 ) {
                $examfile->status = $formdata->status;
            }
            if($formdata->name) {
                $examfile->name = $formdata->name;
            }
            if($DB->update_record('examregistrar_examfiles', $examfile)) {
                $newid = $examfile->id;
            }
        } else {
            // no attempt, we are adding
            $examfile = new stdClass;
            $examfile->examid = $exam->id;
            $examfile->status = $formdata->status;
            $examfile->printmode = $formdata->printmode;
            $examfile->attempt = $attempts ? count($attempts) + 1 : 1;
            $examfile->name = $formdata->name ? $formdata->name : get_string('attempt', 'examregistrar').'&nbsp;'.$examfile->attempt;
            $examfile->idnumber = examregistrar_examfile_idnumber($exam, $examcourse->idnumber);
            $examfile->userid = $USER->id;
            $examfile->timecreated = $now;
            $examfile->timemodified = $now;

            $newid = $DB->insert_record('examregistrar_examfiles', $examfile);
            $examfile = $DB->get_record('examregistrar_examfiles', array('id'=>$newid), '*', MUST_EXIST);

        }
        if($newid) {
            // now we can store uploaded files

            $examfilecontext = context_course::instance($examcourse->id);
            $filename = $examfile->idnumber.'.pdf';
            $mform->save_stored_file('uploadfileexam', $examfilecontext->id, 'mod_examregistrar', 'exam', $newid, '/', $filename, true);
            $suffix = get_config('examregistrar', 'extanswers');
            $filename = $examfile->idnumber.$suffix.'.pdf';
            $mform->save_stored_file('uploadfileanswers', $examfilecontext->id, 'mod_examregistrar', 'exam', $newid, '/answers/', $filename, true);
            // log the action
            $eventdata = array();
            $eventdata['objectid'] = $examfile->id;
            $eventdata['context'] = $context;
            $eventdata['userid'] = $USER->id;
            $eventdata['other'] = array();
            $eventdata['other']['examregid'] = $examregistrar->id;
            $eventdata['other']['examid'] = $examfile->examid;
            $eventdata['other']['attempt'] = $examfile->attempt;
            $eventdata['other']['idnumber'] = $examfile->idnumber;
            $event = \mod_examregistrar\event\examfile_uploaded::create($eventdata);
            $event->add_record_snapshot('examregistrar_examfiles', $examfile);
            $event->trigger();
        }

        echo $output->box(get_string('changessaved'), ' generalbox messagebox success ');
        $url = new moodle_url($baseurl, $reviewparams);
        echo $output->continue_button($url);

    } else {
        $mform->display();
    }
    echo $output->footer();
    die;
}


if($exemption) {
    include_once($CFG->dirroot.'/mod/quiz/report/makeexam/exemptions_form.php');

    $mform = new quiz_makeexam_exemptions_form(null, $baseparams + array('reviewparams'=>$reviewparams,
                                                                              'exemption'=>$exemption,
                                                                              'courses'=>$courses,
                                                                            ));

    if(($formdata = $mform->get_data()) && confirm_sesskey()) { /// && confirm_sesskey()) { /// TODO TODO
        /// there is data, process and go to table again

        $now = time();

        $count = 0;
        foreach($formdata->courses as $courseid) {
            $ccontext = context_course::instance($courseid);
            $success = false;
            foreach($formdata->roles as $roleid) {
                foreach($formdata->capabilities as $capability) {
                    if($formdata->assign == 0) {
                        $success = unassign_capability($capability, $roleid, $ccontext->id);
                    } elseif($formdata->assign == 1) {
                        $success = assign_capability($capability, CAP_ALLOW, $roleid, $ccontext->id, true);
                    }
                }
            }
            if($success) {
                $count += 1;
            }
            $eventdata = array();
            $eventdata['objectid'] = $examregistrar->id;
            $eventdata['context'] = $context;
            $eventdata['userid'] = $USER->id;
            $eventdata['other'] = array();
            $eventdata['other']['courseid'] = $courseid;
            $eventdata['other']['assign'] = $formdata->assign;
            $event = \mod_examregistrar\event\capabilities_updated::create($eventdata);
            $event->trigger();


        }

        $message = get_string('nochange');
        if($count) {
            $info = new stdClass();
            $info->count = $count;
            $info->roles = count($formdata->roles);
            $info->caps = count($formdata->capabilities);
            $message = get_string('permissionsset', 'quiz_makeexam', $info);
            $message .= '<br />'.get_string('changessaved');
        }

        echo $output->box($message, ' generalbox messagebox successbox center centered ');
        $url = new moodle_url($baseurl, $reviewparams);
        echo $output->continue_button($url);

    } else {
        $mform->display();
    }
    echo $output->footer();
    die;
}

$synch = optional_param('synch', '', PARAM_ALPHA);
if($synch) {
    if(!$confirm) {
            $action = get_string($synch, 'examregistrar');
            $message = get_string('confirm_synch', 'examregistrar', $action);
            $confirmurl = new moodle_url($reviewurl, array('synch'=>$synch, 'confirm' => 1));
            echo $output->confirm($message, $confirmurl, $reviewurl);
            echo $output->footer();
            die;

    } elseif(confirm_sesskey()){
        include_once($CFG->dirroot.'/mod/tracker/locallib.php');
        switch($synch) {
            case 'approve' : $success = examregistrar_examstatus_synchronize(EXAM_STATUS_APPROVED, TESTING, RESOLVED, array_keys($courses));
                        break;
            case 'reject' : $success = examregistrar_examstatus_synchronize(EXAM_STATUS_REJECTED, ABANDONNED, TRANSFERED, array_keys($courses));
                        break;
            case 'create' : break;
        }
        examregistrar_tracker_add_issues($examregistrar, $course);
        $eventdata = array();
        $eventdata['objectid'] = $examregistrar->id;
        $eventdata['context'] = $context;
        $eventdata['userid'] = $USER->id;
        $eventdata['other'] = array();
        $eventdata['other']['synch'] = $synch;
        $eventdata['other']['examregid'] = $examregistrar->id;
        $event = \mod_examregistrar\event\examfiles_synced::create($eventdata);
        $event->trigger();
    }
}

///////////////////////////////////////////////////////////////////////////////////////

//echo $output->exams_courses_selectorform($examregistrar, $course, $baseurl, $reviewparams,  'period', false);
echo $output->exams_item_selection_form($examregistrar, $course, $baseurl, $reviewparams, 'period', false);
if($canviewall) {
    echo $output->exams_courses_selector_form($examregistrar, $course, $baseurl, $reviewparams);
}

if($examregistrar->workmode != EXAMREGISTRAR_MODE_VIEW) {
    if(has_capability('mod/examregistrar:resolve', $context) &&  $canviewall) {
        echo $output->container_start(' examreviewsynchronize clearfix ');
            $synchmenu = array('approve' => get_string('approve', 'examregistrar'),
                            'reject' => get_string('reject', 'examregistrar'),
                            'create' => get_string('addissues', 'examregistrar'));

            $select = new single_select(new moodle_url($baseurl), 'synch', $synchmenu);
            $select->label = get_string('status_synch', 'examregistrar');
            echo $output->render($select);

            if($makeexam = get_config('quiz_makeexam')) {
                echo $output->single_button(new moodle_url($baseurl, array('exemption'=>'exemption')), get_string('setpermissions', 'quiz_makeexam'), '', array('class'=>' examreviewsynchronize clearfix ' ) );
            }

        echo $output->container_end();
        echo html_writer::div('', ' clearfix ');
    }
}


// print table header
/// get period name & code

$periodname = '';
if($period) {
    list($periodname, $periodidnumber) = examregistrar_get_namecodefromid($period, 'periods', 'period');
}
echo $output->heading(get_string('examsforperiod', 'examregistrar', $periodname));

$single = count($courses) > 1 ? false : true;
foreach($courses as $examcourse) {
    // check review permissions in exam course
    $econtext = context_course::instance($examcourse->id);
    if($canviewall || has_capability('mod/examregistrar:review', $econtext)) {
        $examcourse->context = $econtext;
        $coursereview = new examregistrar_exams_coursereview($examregistrar, $examcourse, $period, $annuality, $reviewurl, $single);
        echo $output->render($coursereview);
    }
}

if(!$courses) {
echo $output->heading(get_string('noexams', 'examregistrar'), 4, 'error');
}







