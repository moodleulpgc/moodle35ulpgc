<?php

/**
 * E-mail formatting from templates.
 *
 * @package    mod
 * @subpackage scheduler
 * @copyright  2011 Henning Bostelmann and others (see README.txt)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Returns the language to be used in a message to a user
 *
 * @param stdClass $user the user to whom the message will be sent
 * @param stdClass $course the course from which the message originates
 * @return string
 */
function scheduler_get_message_language($user, $course) {
    if ($course && !empty($course->id) and $course->id != SITEID and !empty($course->lang)) {
        // Course language overrides user language.
        $return = $course->lang;

    } else if (!empty($user->lang)) {
        $return = $user->lang;

    } else if (isset($CFG->lang)) {
        $return = $CFG->lang;

    } else {
        $return = 'en';
    }

    return $return;
}


/**
* Gets the content of an e-mail from language strings
*
* Looks for the language string email_$template_$format and replaces the parameter values.
*
* @param template the template's identified
* @param string $format tthe mail format ('subject', 'html' or 'plain')
* @param infomap a hash containing pairs of parm => data to replace in template
* @return a fully resolved template where all data has been injected
*/
function scheduler_compile_mail_template($template, $format, $infomap, $module = 'scheduler', $lang = null) {
	$params = array();
	foreach ($infomap as $key=>$value) {
	    $params[strtolower($key)] = $value;
	}
	$mailstr = get_string_manager()->get_string( "email_{$template}_{$format}", $module, $params, $lang);
    return $mailstr;
}


/**
 * Sends an e-mail based on a template.
 * Several template substitution values are automatically filled by this routine.
 *
 * @uses $CFG
 * @uses $SITE
 * @param user $recipient A {@link $USER} object describing the recipient
 * @param user $sender A {@link $USER} object describing the sender
 * @param object $course The course that the activity is in. Can be null.
 * @param string $title the identifier for the e-mail subject.
 *        Value can include one parameter, which will be substituted
 *        with the course shortname.
 * @param string $template the virtual mail template name (without "_html" part)
 * @param array $infomap a hash containing pairs of parm => data to replace in template
 * @param string $modulename the current module
 * @param string $lang language to be used, if default language must be overriden
 * @return bool|string Returns "true" if mail was sent OK, "emailstop" if email
 *         was blocked by user and "false" if there was another sort of error.
 */
function scheduler_send_email_from_template($recipient, $sender, $course, $title, $template, $infomap, $modulename) {

    global $CFG;
    global $SITE;

    $lang = scheduler_get_message_language($recipient, $course);
    
    // ecastro ULPGC
    $noreplyuser = core_user::get_noreply_user();
    foreach(get_all_user_name_fields() as $field) {
            $noreplyuser->{$field} = $sender->{$field};
    }
    $sender = $noreplyuser;

    $defaultvars = array(
        'SITE' => $SITE->fullname,
        'SITE_SHORT' => $SITE->shortname,
        'SITE_URL' => $CFG->wwwroot,
        'SENDER'  => fullname($sender),
        'RECIPIENT'  => fullname($recipient)
    );

    $subjectPrefix = $SITE->shortname;

    if ($course) {
        $subjectPrefix = $course->shortname;
        $defaultvars['COURSE_SHORT'] = $course->shortname;
        $defaultvars['COURSE']       = $course->fullname;
        $defaultvars['COURSE_URL']   = $CFG->wwwroot.'/course/view.php?id='.$course->id;
    }

    $vars = array_merge($defaultvars, $infomap);

    $subject = scheduler_compile_mail_template($template, 'subject', $vars, $modulename, $lang);
    $plainMail = scheduler_compile_mail_template($template, 'plain', $vars, $modulename, $lang);
    $htmlMail = scheduler_compile_mail_template($template, 'html', $vars, $modulename, $lang);
    
    $res = email_to_user ($recipient, $sender, $subject, $plainMail, $htmlMail);
    return $res;
}

/**
 * Construct an array with subtitution rules for mail templates, relating to
 * a single appointment. Any of the parameters can be null.
 * @param scheduler_instance $scheduler The scheduler instance
 * @param scheduler_slot $slot The slot data as an MVC object
 * @param user $attendant A {@link $USER} object describing the attendant (teacher)
 * @param user $attendee A {@link $USER} object describing the attendee (student)
 * @param object $course A course object relating to the ontext of the message
 * @param object $recipient A {@link $USER} object describing the recipient of the message (used for determining the message language)
 * @return array A hash with mail template substitutions
 */
function scheduler_get_mail_variables (scheduler_instance $scheduler, scheduler_slot $slot, $attendant, $attendee, $course, $recipient) {

    global $CFG;

    $lang = scheduler_get_message_language($recipient, $course);
    // Force any string formatting to happen in the target language.
    $oldlang = force_current_language($lang);

    $tz = core_date::get_user_timezone($recipient);

    $vars = array();

    if ($scheduler) {
        $vars['MODULE']     = $scheduler->name;
        $vars['STAFFROLE']  = $scheduler->get_teacher_name();
        $vars['SCHEDULER_URL'] = $CFG->wwwroot.'/mod/scheduler/view.php?id='.$scheduler->cmid;
    }
    if ($slot) {
        $vars ['DATE']     = userdate($slot->starttime, get_string('strftimedate'), $tz);
        $vars ['TIME']     = userdate($slot->starttime, get_string('strftimetime'), $tz);
        $vars ['ENDTIME']  = userdate($slot->endtime, get_string('strftimetime'), $tz);
        $vars ['LOCATION'] = format_string($slot->appointmentlocation);
    }
    if ($attendant) {
        $vars['ATTENDANT']     = fullname($attendant);
        $vars['ATTENDANT_URL'] = $CFG->wwwroot.'/user/view.php?id='.$attendant->id.'&course='.$scheduler->course;
    }
    if(isset($attendee)) { 
        if(isset($attendee->lastname)) { 
            $vars['ATTENDEE']     = fullname($attendee);
            $vars['ATTENDEE_URL'] = $CFG->wwwroot.'/user/view.php?id='.$attendee->id.'&course='.$scheduler->course;
        } elseif(isset(($attendee->name))) { // ecastro ULPGC
            $vars['ATTENDEE']     = $attendee->name;
            $vars['ATTENDEE_URL'] = $CFG->wwwroot.'/user/index.php?id='.$scheduler->course.'&group='.$attendee->id;
        }
    }
    // Reset language settings.
    force_current_language($oldlang);

    return $vars;

}
