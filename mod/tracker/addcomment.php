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

require('../../config.php');
require_once($CFG->dirroot.'/mod/tracker/lib.php');
require_once($CFG->dirroot.'/mod/tracker/locallib.php');
require_once($CFG->dirroot.'/mod/tracker/forms/addcomment_form.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$t  = optional_param('t', 0, PARAM_INT);  // tracker ID
$issueid  = required_param('issueid', PARAM_INT);  // tracker ID

if ($id) {
    if (! $cm = get_coursemodule_from_id('tracker', $id)) {
        print_error('errorcoursemodid', 'tracker');
    }

    if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('errorcoursemisconfigured', 'tracker');
    }

    if (! $tracker = $DB->get_record('tracker', array('id' => $cm->instance))) {
        print_error('errormoduleincorrect', 'tracker');
    }
} else {

    if (! $tracker = $DB->get_record('tracker', array('id' => $t))) {
        print_error('errormoduleincorrect', 'tracker');
    }

    if (! $course = $DB->get_record('course', array('id' => $tracker->course))) {
        print_error('errorcoursemisconfigured', 'tracker');
    }
    if (! $cm = get_coursemodule_from_instance("tracker", $tracker->id, $course->id)) {
        print_error('errorcoursemodid', 'tracker');
    }
}

// Security.

$context = context_module::instance($cm->id);
require_course_login($course->id, false, $cm);
require_capability('mod/tracker:comment', $context);

if (!$issue = $DB->get_record('tracker_issue', array('id' => $issueid))) {
    print_error('errorbadissueid', 'tracker');
}

// Setting page.

$url = new moodle_url('/mod/tracker/addcomment.php', array('id' => $id, 'issueid' => $issueid));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(format_string($tracker->name));
$PAGE->set_heading(format_string($tracker->name));
$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'tracker'));
$PAGE->navbar->add(tracker_getstring('addacomment', 'tracker'), null); // ecastro ULPGC

$form = new AddCommentForm(new moodle_url('/mod/tracker/addcomment.php'), array('tracker' => $tracker, 'issue' => $issue, 'cmid' => $id));


if (!$form->is_cancelled()) {
    if ($data = $form->get_data()) {

        $comment = new StdClass();
        $comment->comment = $data->comment_editor['text'];
        $comment->commentformat = $data->comment_editor['format'];
        $comment->userid = $USER->id;
        $comment->trackerid = $tracker->id;
        $comment->issueid = $issueid;
        $comment->datecreated = time();
        if (!$comment->id = $DB->insert_record('tracker_issuecomment', $comment)) {
            print_error('cannotwritecomment', 'tracker');
        }

        // add_to_log($course->id, 'tracker', "commentissue", "view.php?id={$cm->id}", "$tracker->id", $cm->id);
        $event = \mod_tracker\event\tracker_issuecommented::create_from_issue($tracker, $issueid);
        $event->trigger();

        if ($tracker->allownotifications) {
            tracker_notifyccs_comment($issueid, $comment->comment, $tracker);
        }
        tracker_register_cc($tracker, $issue, $USER->id);

        // Stores editor files.
        $data = file_postupdate_standard_editor($data, 'comment', $form->editoroptions, $context, 'mod_tracker', 'issuecomment', $comment->id);
        // Update back reencoded field text content.
        $DB->set_field('tracker_issuecomment', 'comment', $data->comment, array('id' => $comment->id));

        if($comment->id) { // ecastro ULPGC
            $now = time();
            //save attachments
            file_save_draft_area_files($data->attachment, $context->id, 'mod_tracker', 'issuecomment', $comment->id);


            if(($tracker->supportmode == 'usersupport') || 
                    ($tracker->supportmode == 'boardreview') || 
                        ($tracker->supportmode == 'tutoring')) {
                if(isset($data->status)) {
                    $issue->status = $data->status;
                }

                // if comment in an issue get ownership of it, if not set previouly
                $changedowner = false;
                $candevelop = has_capability('mod/tracker:develop', $context);
                if($candevelop && !$issue->assignedto) {
                    $issue->assignedto = $USER->id;
                    $issue->bywhomid = $USER->id;
                    $issue->timeassigned = $now;

                $ownership = new StdClass;
                    $ownership->trackerid = $issue->trackerid;
                    $ownership->issueid = $issue->id;
                    $ownership->userid = $USER->id;
                    $ownership->bywhomid = $USER->id;
                    $ownership->timeassigned = $now;
                    if (!$DB->insert_record('tracker_issueownership', $ownership)) {
                        notice ("Error saving ownership for issue $issueid");
                    }
                    $changedowner = true;
                }

                // TODO   change by shop_get_role_definition TODO
                // TODO   change by shop_get_role_definition TODO
                // set timemodified by user or staff
                if($comment->userid == $issue->reportedby) {
                    $issue->usermodified = $now;
                    $issue->userlastseen = $now;
                    if($tracker->supportmode != 'tutoring') {
                        $issue->status = POSTED;
                    }
                } elseif($candevelop) {
                    $issue->resolvermodified = $now;
                }

                if (!$DB->update_record('tracker_issue', $issue)) {
                    notice ("Error updating assignation for issue $issueid");
                }

                if ($changedowner && $tracker->allownotifications) {
                    tracker_notifyccs_changeownership($issue->id, $tracker);
                }

                // reorder priority field and discard newly resolved or abandonned
                tracker_update_priority_issue($issue);
            }
        }

        redirect(new moodle_url('/mod/tracker/view.php', array('id' => $id, 'view' => 'view', 'screen' => 'viewanissue', 'issueid' => $issueid)));
    }
} else {
    redirect(new moodle_url('/mod/tracker/view.php', array('id' => $id, 'view' => 'view', 'screen' => 'viewanissue', 'issueid' => $issueid)));
}

echo $OUTPUT->header();

if(($tracker->supportmode != 'usersupport') && ($tracker->supportmode != 'boardreview') && ($tracker->supportmode != 'tutoring')) {
    echo $OUTPUT->heading($issue->summary);

    $description = file_rewrite_pluginfile_urls($issue->description, 'pluginfile.php', $context->id, 'mod_tracker', 'issuedescription', $issue->id);
    echo $OUTPUT->box(format_text($description, $issue->descriptionformat), 'tracker-issue-description');
}
echo $OUTPUT->heading(tracker_getstring('addacomment', 'tracker'));

$form->display();

echo $OUTPUT->footer();
