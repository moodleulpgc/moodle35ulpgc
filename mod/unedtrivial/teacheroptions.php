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
 * Process teacher option chosen
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/mod/unedtrivial/emailform.php');
//require_once($CFG->dirroot.'/lib/phpmailer/class.phpmailer.php');
//require_once($CFG->dirroot.'/lib/phpmailer/class.smtp.php');
        
global $DB;

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... newmodule instance ID - it should be named as the first character of the module.
$option = optional_param('option', 0, PARAM_INT);
$destiny = optional_param('destiny',0, PARAM_INT);
$special = optional_param('special',0, PARAM_INT);

if ($id) {
    $cm         = get_coursemodule_from_id('unedtrivial', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $newmodule  = $DB->get_record('unedtrivial', array('id' => $cm->instance), '*', MUST_EXIST);
    $context = context_module::instance($cm->id);
} else if ($n) {
    $newmodule  = $DB->get_record('unedtrivial', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $newmodule->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('unedtrivial', $newmodule->id, $course->id, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$event = \mod_unedtrivial\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $newmodule);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/unedtrivial/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($newmodule->name));
$PAGE->set_heading(format_string($course->fullname));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('newmodule-'.$somevar);
 */

//Students can't stay here
if (!has_capability('mod/unedtrivial:addinstance', $context)) {
    echo $OUTPUT->header();
    echo get_string('roleerror', 'unedtrivial');
    echo $OUTPUT->footer();
    die();
}

// Output starts here.
echo $OUTPUT->header();

if($option == 1){
    //Modify questions bank
    redirect(new moodle_url('managequestions.php', array('id' => $id)));
	
}else if ($option == 2){
    //See highscores
    echo $OUTPUT->heading(get_string('leaderboard', 'unedtrivial'), 2, null);
    echo get_string('leaderboardinstr2', 'unedtrivial');
    $allrows = $DB->get_records_sql('SELECT u.id,u.firstname,u.lastname,SUM(n.questionscore) AS sum '
            . '                        FROM {user} u'
            . '                        JOIN {unedtrivial_history} n ON n.userid = u.id '
            . '                       WHERE n.idunedtrivial = ? '
            . '                    GROUP BY u.id,u.firstname,u.lastname'
            . '                    ORDER BY sum DESC', array($cm->instance));
    $table = new html_table();
    $table->head = array(get_string('leaderboardcol1', 'unedtrivial'), 
                         get_string('leaderboardcol2', 'unedtrivial'),
                         get_string('leaderboardcol3', 'unedtrivial'));
    $i = 0;
    foreach($allrows as $row) {   
        $i = $i + 1;
        $table->data[] = array($i, 
                               html_writer::link(new moodle_url('studentinfo.php', 
                                                 array('id'=>$id,'instance'=>$cm->instance,'student'=>$row->id)), 
                                                 $row->firstname . " " . $row->lastname), 
                               $row->sum);
    }
    echo html_writer::table($table);
    echo $OUTPUT->single_button(new moodle_url('view.php', array('id' => $id)),
            get_string('back', 'unedtrivial'), 'get');
}else{ //$option == 3
    //Send message to participants
    $mform = new emailform(new moodle_url('teacheroptions.php',
                    array('id' => $id,'option' => $option,'destiny' => $destiny,'special' => $special)),null);
    $fromform = $mform->get_data();
    if ($mform->is_cancelled()){
        redirect(new moodle_url('view.php', array('id' => $id)));
    }else if ($fromform != null && $fromform->email != '') {
        $special = $fromform->special;
        $toUser = unedtrivial_get_email_addresses($newmodule,$destiny,$special);
        $teachermails = unedtrivial_build_teachers_link($newmodule->teachermails);
        $header  = 'MIME-Version: 1.0' . "\r\n";
        $header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $header .= 'From: unedtrivial <noreply@unedtrivial.com>' . "\r\n";
        $header .= 'Bcc: '.$toUser."\r\n";
        $text =   '<html>'
              . '<head>'
              . '<title></title>'
              . '<body>'
              . $mform->get_data()->email['text'] . "<br><br>"
              . $teachermails
              . '</body>'
              . '</html>';
        if ($toUser != ''){
            $err = '';
            $err = mail_sender($mform->get_data()->subject,$text,$header,$toUser,true);
            if ($err != ''){
                echo $OUTPUT->error_text('<b><font color="red">' .
                            get_string('errormailtask', 'unedtrivial') . "<br>" . $err .
                            '</font></b><br>');
                echo $OUTPUT->single_button(new moodle_url('view.php', array('id' => $id)),
                        get_string('back', 'unedtrivial'), 'get');
            }else{
                redirect(new moodle_url('view.php', array('id' => $id)));
            }
        }else{
            redirect(new moodle_url('view.php', array('id' => $id)));   
        }
    } else {
        echo $OUTPUT->heading(format_string(get_string('teacheroption3', 'unedtrivial')), 2, null);
        echo "<br />";
        $toform = new stdClass();
        $toform->destiny = $destiny;
        $toform->special = $special;
        $mform->set_data($toform);
        $mform->display();
    }
}

// Finish the page.
echo $OUTPUT->footer();
