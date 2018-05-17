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

require_once(__DIR__.'/../../../config.php');

$id             = required_param('id', PARAM_INT);
$confirm        = optional_param('confirm', 0, PARAM_INT);

$conversationrecord = $DB->get_record('dialogue_conversations', array('id' => $id), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('dialogue', $conversationrecord->dialogueid);
if (! $cm) {
    print_error('invalidcoursemodule');
}
$activityrecord = $DB->get_record('dialogue', array('id' => $cm->instance));
if (! $activityrecord) {
    print_error('invalidid', 'dialogue');
}
$course = $DB->get_record('course', array('id' => $activityrecord->course));
if (! $course) {
    print_error('coursemisconf');
}
$context = \context_module::instance($cm->id, MUST_EXIST);

require_login($course, false, $cm);

$pageurl = new moodle_url('/mod/dialogue/conversation/close.php');
$pageurl->param('id', $conversationrecord->id);
$returnurl = new moodle_url('/mod/dialogue/view.php', array('id' => $cm->id));

//$PAGE->set_pagelayout('base');
$PAGE->set_cm($cm, $course, $activityrecord);
$PAGE->set_context($context);
$PAGE->set_cacheable(false);
$PAGE->set_url($pageurl);

$dialogue = new \mod_dialogue\dialogue($cm, $course, $activityrecord);
$conversation = new \mod_dialogue\conversation($dialogue, (int) $conversationrecord->id);

if (!empty($confirm) && confirm_sesskey()) {
    $conversation->close();
    // Trigger conversation closed event
    $eventparams = array(
        'context' => $context,
        'objectid' => $conversation->conversationid
    );
    $event = \mod_dialogue\event\conversation_closed::create($eventparams);
    $event->trigger();
    redirect($returnurl, get_string('conversationclosed', 'dialogue',
        $conversation->subject));
}

echo $OUTPUT->header($activityrecord->name);
$pageurl->param('confirm', $conversation->conversationid);
$notification = $OUTPUT->notification(get_string('conversationcloseconfirm', 'dialogue', $conversation->subject), 'notifymessage');
echo $OUTPUT->confirm($notification, $pageurl, $returnurl);
echo $OUTPUT->footer();