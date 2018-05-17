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
 * Library of interface functions and constants for module unedtrivial
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the unedtrivial specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//require_once($CFG->dirroot.'/lib/phpmailer/class.phpmailer.php');
//require_once($CFG->dirroot.'/lib/phpmailer/class.smtp.php');

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function unedtrivial_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the unedtrivial into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $unedtrivial Submitted data from the form in mod_form.php
 * @param mod_unedtrivial_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted unedtrivial record
 */
function unedtrivial_add_instance(stdClass $unedtrivial, mod_unedtrivial_mod_form $mform = null) {
    global $DB;

    $unedtrivial->timecreated = time();

    // You may have to add extra stuff in here.
    if(property_exists($mform->get_data(),'allquestions')){
        //End of time means that there's no end date for this UNEDTrivial
        $unedtrivial->enddate = 60; 
    }

    $unedtrivial->id = $DB->insert_record('unedtrivial', $unedtrivial);

    unedtrivial_grade_item_update($unedtrivial);

    return $unedtrivial->id;
}

/**
 * Updates an instance of the unedtrivial in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $unedtrivial An object from the form in mod_form.php
 * @param mod_unedtrivial_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function unedtrivial_update_instance(stdClass $unedtrivial, mod_unedtrivial_mod_form $mform = null) {
    global $DB;

    $unedtrivial->timemodified = time();
    $unedtrivial->id = $unedtrivial->instance;

    // You may have to add extra stuff in here.
    if(property_exists($mform->get_data(),'allquestions')){
        //End of time means that there's no end date for this UNEDTrivial
        $unedtrivial->enddate = 60; 
    }

    $result = $DB->update_record('unedtrivial', $unedtrivial);

    unedtrivial_grade_item_update($unedtrivial);

    return $result;
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every unedtrivial event in the site is checked, else
 * only unedtrivial events belonging to the course specified are checked.
 * This is only required if the module is generating calendar events.
 *
 * @param int $courseid Course ID
 * @return bool
 */
function unedtrivial_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid == 0) {
        if (!$unedtrivials = $DB->get_records('unedtrivial')) {
            return true;
        }
    } else {
        if (!$unedtrivials = $DB->get_records('unedtrivial', array('course' => $courseid))) {
            return true;
        }
    }

    foreach ($unedtrivials as $unedtrivial) {
        // Create a function such as the one below to deal with updating calendar events.
        // unedtrivial_update_events($unedtrivial);
    }

    return true;
}

/**
 * Removes an instance of the unedtrivial from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function unedtrivial_delete_instance($id) {
    global $DB;
	
    if (! $unedtrivial = $DB->get_record('unedtrivial', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.
    $DB->delete_records('unedtrivial_history',array('idunedtrivial' => $unedtrivial->id));
    $DB->delete_records('unedtrivial_questions', array('idunedtrivial' => $unedtrivial->id));
    $DB->delete_records('unedtrivial_mails', array('idunedtrivial' => $unedtrivial->id));
    $DB->delete_records('unedtrivial', array('id' => $unedtrivial->id));
	
    unedtrivial_grade_item_delete($unedtrivial);

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $unedtrivial The unedtrivial instance record
 * @return stdClass|null
 */
function unedtrivial_user_outline($course, $user, $mod, $unedtrivial) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * It is supposed to echo directly without returning a value.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $unedtrivial the module instance record
 */
function unedtrivial_user_complete($course, $user, $mod, $unedtrivial) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in unedtrivial activities and print it out.
 *
 * @param stdClass $course The course record
 * @param bool $viewfullnames Should we display full names
 * @param int $timestart Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function unedtrivial_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link unedtrivial_print_recent_mod_activity()}.
 *
 * Returns void, it adds items into $activities and increases $index.
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 */
function unedtrivial_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@link unedtrivial_get_recent_mod_activity()}
 *
 * @param stdClass $activity activity record with added 'cmid' property
 * @param int $courseid the id of the course we produce the report for
 * @param bool $detail print detailed report
 * @param array $modnames as returned by {@link get_module_types_names()}
 * @param bool $viewfullnames display users' full names
 */
function unedtrivial_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 *
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * Note that this has been deprecated in favour of scheduled task API.
 *
 * @return boolean
 */
function unedtrivial_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array
 */
function unedtrivial_get_extra_capabilities() {
    return array();
}

/* Gradebook API */

/**
 * Is a given scale used by the instance of unedtrivial?
 *
 * This function returns if a scale is being used by one unedtrivial
 * if it has support for grading and scales.
 *
 * @param int $unedtrivialid ID of an instance of this module
 * @param int $scaleid ID of the scale
 * @return bool true if the scale is used by the given unedtrivial instance
 */
function unedtrivial_scale_used($unedtrivialid, $scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('unedtrivial', array('id' => $unedtrivialid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of unedtrivial.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale
 * @return boolean true if the scale is used by any unedtrivial instance
 */
function unedtrivial_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('unedtrivial', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given unedtrivial instance
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $unedtrivial instance object with extra cmidnumber and modname property
 * @param bool $reset reset grades in the gradebook
 * @return void
 */
function unedtrivial_grade_item_update(stdClass $unedtrivial, $reset=false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($unedtrivial->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    if ($unedtrivial->grade > 0) {
	$item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $unedtrivial->grade;
        $item['grademin']  = 0;
    } else if ($unedtrivial->grade < 0) {
	  $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$unedtrivial->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('mod/unedtrivial', $unedtrivial->course, 'mod', 'unedtrivial',
            $unedtrivial->id, 0, null, $item);
}

/**
 * Delete grade item for given unedtrivial instance
 *
 * @param stdClass $unedtrivial instance object
 * @return grade_item
 */
function unedtrivial_grade_item_delete($unedtrivial) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/unedtrivial', $unedtrivial->course, 'mod', 'unedtrivial',
            $unedtrivial->id, 0, null, array('deleted' => 1));
}

/**
 * Update unedtrivial grades in the gradebook
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $unedtrivial instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 */
function unedtrivial_update_grades(stdClass $unedtrivial, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();

    grade_update('mod/unedtrivial', $unedtrivial->course, 'mod', 'unedtrivial', $unedtrivial->id, 0, $grades);
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function unedtrivial_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for unedtrivial file areas
 *
 * @package mod_unedtrivial
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function unedtrivial_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the unedtrivial file areas
 *
 * @package mod_unedtrivial
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the unedtrivial's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function unedtrivial_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

/* Navigation API */

/**
 * Extends the global navigation tree by adding unedtrivial nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the unedtrivial module instance
 * @param stdClass $course current course record
 * @param stdClass $module current unedtrivial instance record
 * @param cm_info $cm course module information
 */
function unedtrivial_extend_navigation(navigation_node $navref, stdClass $course, stdClass $module, cm_info $cm) {
    // TODO Delete this function and its docblock, or implement it.
}

/**
 * Extends the settings navigation with the unedtrivial settings
 *
 * This function is called when the context for the page is a unedtrivial module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav complete settings navigation tree
 * @param navigation_node $unedtrivialnode unedtrivial administration node
 */
function unedtrivial_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $unedtrivialnode=null) {
    // TODO Delete this function and its docblock, or implement it.
}

/**
 * Obtains the automatic completion state for this choice based on any conditions
 * in forum settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function unedtrivial_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    // Get UNEDTrivial details
    $uned = $DB->get_record('unedtrivial', array('id'=>$cm->instance), '*',
            MUST_EXIST);

    // If completion option is enabled, evaluate it and return true/false
    if($uned->completionsubmit == 1) {
        return (unedtrivial_get_user_closed_questions($uned, $userid) >=
                unedtrivial_get_questions($uned->id));
    } else {
        // Completion option is not enabled so just return $type
        return $type;
    }
}

/**
 * Check what was the last result of a user with one question
 *
 * @param int $uned unedtrivial ID
 * @param int $qid Question ID
 * @param int $uid User ID
 */
function unedtrivial_check_question_history($unedid, $qid, $uid){
    global $DB;
    $sql = 'SELECT *
              FROM {unedtrivial_history} u
             WHERE u.idunedtrivial = '.$unedid.' AND u.userid = '.$uid.' AND u.questionid = '.$qid.'
          ORDER BY u.questiondate DESC, u.id DESC';
    $result = $DB->get_records_sql($sql);
    if (count($result) > 0){
        reset($result);
        return current($result)->questionstate;
    }else{
        return 0;
    }
}

/**
 * Reorder an array depending on a seed
 *
 * @param $arr Array to be reordered
 * @param $seed Seed to be applied
 */
function unedtrivial_shuffleseed($arr, $seed){
    mt_srand($seed);
    $order = array_map(create_function('$val', 'return mt_rand();'), range(1, count($arr)));
    array_multisort($order, $arr);
    return($arr);
}

/**
 * Return the percentage to apply
 *
 * @param $total Number of different choices
 * @param $correct Number of correct answers
 * @param $result What did the user? ('SUCCESS' or 'FAILURE')
 */
function unedtrivial_multichoice($total, $correct, $result){
    $incorrect = $total - $correct;
    if ($result == 'SUCCESS'){
        return (1/$correct);
    }else{
        if ($incorrect > 0){
            return -(1/$incorrect);
        }else{
            return 0;
        }
    }
}

/**
 * Returns true if this question is valid:
 *   - Has not been closed (answered correctly twice)
 *   - Has not been answered today
 *   - User has not answered 3 questions today
 *
 * @param unedtrivial unedtrivial instance
 * @param userid User id to check
 * @param question Question to check
 * @param qdate Date to check (UNIX time)
 * @return 0 if no error. 1,2,3 if errors found
 */
function unedtrivial_question_valid($unedtrivial, $userid, $question, $qdate){
    global $DB;
    $unedid = $unedtrivial->id;
    
    //Is this question closed? (questionstate == timestocomplete)
    $sql1 = 'SELECT COUNT(u.id) '
            . 'FROM {unedtrivial_history} u '
            .'WHERE u.questionid = '.$question.' AND '
            .'      u.idunedtrivial = '.$unedid.' AND '
            . '     u.userid = '.$userid.' AND '
            . '     u.questionstate = '.$unedtrivial->timestocomplete;
    if ($DB->count_records_sql($sql1) == 0){
        $ini_time = $qdate;
        $end_time = $qdate + 86399;
        //Is this question answered today
        $sql2 = 'SELECT COUNT(u.id) '
                . 'FROM {unedtrivial_history} u '
                .'WHERE u.questionid = '.$question.' AND '
                .'      u.idunedtrivial = '.$unedid.' AND '
                . '     u.userid = '.$userid.' AND '
                . '     u.questiondate BETWEEN '.$ini_time.' AND '.$end_time;
        
        if ($DB->count_records_sql($sql2) == 0){
            //Has user answered all questions today?
            $anstoday = unedtrivial_get_user_answers_for_date($unedid, $userid, $qdate);
            if ($anstoday < $unedtrivial->questionsperday){
                return 0;
            }else{
                return 3;
            }
        }else{
            return 2;
        }
    }else{
        return 1;
    }
}

/**
 * 
 * @param $unedid UNEDTrivial ID
 * @param $userid User Id
 * @param $date Date to check (Unix time)
 */
function unedtrivial_get_user_answers_for_date($unedid, $userid, $date){
    GLOBAL $DB;
    
    $ini_time = $date;
    $end_time = $date + 86399;
    $sql = 'SELECT COUNT(u.id) '
            . 'FROM {unedtrivial_history} u '
            .'WHERE u.idunedtrivial = '.$unedid.' AND '
            . '     u.userid = '.$userid.' AND '
            . '     u.questiondate BETWEEN '.$ini_time.' AND '.$end_time;

    return $DB->count_records_sql($sql);
}

/**
 * Return the string encrypted
 *
 * @param $key string decrypted
 */
function unedtrivial_encrypt($key){ //22-1434234500-8
    $sum = 0;
    for ($i=0;$i<strlen($key);$i++){
        if ($key{$i} != '-'){
            $sum += $key{$i};
        }
    }
    $completekeyD = $key."=".$sum;
    $crypt     = '1234567890-=';
    $decrypt   = 'ABCDEFGHIJKL';
    $completekeyE = strtr($completekeyD, $crypt, $decrypt);
    return $completekeyE;
}

/**
 * Return the string decrypted
 * If checksum failed, it returns false
 *
 * @param $key string encrypted
 */
function unedtrivial_decrypt($key){
    $dataE = strtok($key, "L");
    $checksumE = strtok("L");
    $crypt   = 'ABCDEFGHIJK';
    $decrypt = '1234567890-';
    $dataD = strtr($dataE, $crypt, $decrypt);
    $checksumD = (int)strtr($checksumE, $crypt, $decrypt);
    $sum = 0;
    for ($i=0;$i<strlen($dataD);$i++){
        if ($dataD{$i} != '-'){
            $sum += $dataD{$i};
        }
    }
    
    if ($sum == $checksumD){ 
        return $dataD;
    }else{
        return false;
    }
}

/**
 * Send a mail to addresses supported using the $CFG->lang language configured
 *
 * @param $addresses All email recipients
 * @param $name Name of UNEDTrivial instance
 * @param $unedid unedtrivial id
 */
function unedtrivial_send_mail($unedid, $name, $addresses){
    global $CFG;
    $strman = get_string_manager();
    
    $url = new moodle_url($CFG->wwwroot.'/mod/unedtrivial/maildestiny.php',array('unedid'=>$unedid,'date'=> strtotime(date("Ymd",time()))));
    $subject = $strman->get_string('mailsubject', 'unedtrivial', null, $CFG->lang);
    $header  = 'MIME-Version: 1.0' . "\r\n";
    $header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $header .= 'From: UNEDTrivial <noreply@unedtrivial.com>' . "\r\n";
    $header .= 'Bcc: '.$addresses."\r\n";
    $text =   '<html>'
            . '<head>'
            . '<title></title>'
            . '<body>'
            . '<p>'.$name.'</p><br>'
            . '<p>'.$strman->get_string('hello', 'unedtrivial', null, $CFG->lang).'. '
            . $strman->get_string('mailtaskmsg1', 'unedtrivial', null, $CFG->lang). " "
            . '<a href="'.$url.'">'.$strman->get_string('here', 'unedtrivial', null, $CFG->lang)
            . '</a>' . " " .$strman->get_string('mailtaskmsg2', 'unedtrivial', null, $CFG->lang)
            . '<br /><br />'.$strman->get_string('bye', 'unedtrivial', null, $CFG->lang).'</p>'
            . '</body>'
            . '</html>';
    $timestamp = date("Y-m-d H:m:s",time());
    if ($addresses != ''){
        $err = '';
        $err = mail_sender($subject,$text,$header,$addresses,true);
        if ($err != ''){
            error_log(PHP_EOL.$err,3,
                          $CFG->dirroot."/mod/unedtrivial/logmailtask.txt");
            error_log(PHP_EOL.$timestamp.get_string('errormailtask', 'unedtrivial')." : ".$name,3,
                          $CFG->dirroot."/mod/unedtrivial/logmailtask.txt");
        }else{
            error_log(PHP_EOL.$timestamp.get_string('successmailtask', 'unedtrivial')." : ".$name,3,
                          $CFG->dirroot."/mod/unedtrivial/logmailtask.txt");
        }
    }
}

/**
 * Returns true or false if there are questions for today for a person in a UNEDTrivial
 * Variable $resume will be built if it is passed as null
 * 
 * @param type $unedtrivial UNEDTrivial instance
 * @param type $history UNEDTrivial history
 * @param type $userid User ID
 * @param type $date Date to check
 * @param type $totalqu Total questions of current UNEDTrivial
 * @param type $resume Resume table (is built in function if necessary)
 */

function unedtrivial_are_questions_for_today($unedtrivial,$history,$userid,$date,$totalqu,&$resume){
    if (empty($resume)){
        //Resume table must be built
        foreach ($history as $row){
            $newrow = array();
            $locate = false;
            if (array_key_exists($row->userid,$resume)){
                $locate = array_search($row->questionid, array_column($resume[$row->userid], 'questionid'));
            }
            if($locate === false){
                $newrow['questionid'] = $row->questionid;
                $newrow['questionstate'] = $row->questionstate;
                $newrow['questiondate'] = $row->questiondate;            
                $resume[$row->userid][] = $newrow;
            }else{
                $resume[$row->userid][$locate]['questionstate'] = $row->questionstate;
                $resume[$row->userid][$locate]['questiondate'] = $row->questiondate;
            }
        }
    }
    $date_failure_ini = $date - $unedtrivial->retryerror*86400;
    $date_failure_end = $date_failure_ini + 86399;
    $date_success_ini = $date - $unedtrivial->retrysuccess*86400;
    $date_success_end = $date_success_ini + 86399;
    $answered = array();
    $closed = array();
    $today = array();
    $available_correct = $available_incorrect = array();
    
    //Now we classify resume table in questions available and closed
    if (array_key_exists($userid, $resume)){
        foreach ($resume[$userid] as $lr){
            if ($lr['questionstate'] == $unedtrivial->timestocomplete){
                $closed[] = $lr['questionid'];
            }else if ($lr['questionstate'] == -1 && $lr['questiondate'] < $date_failure_end){
                $available_incorrect[] = $lr['questionid'];
            }else if ($lr['questionstate'] > 0 && $lr['questiondate'] < $date_success_end){
                $available_correct[] = $lr['questionid'];
            }
            if ($lr['questiondate'] == $date){
                $today[] = $lr['questionid'];
            }
            $answered[] = $lr['questionid'];
        }
        return (!empty($available_incorrect) || !empty($available_correct) || count($answered) < $totalqu) &&
               count($today) < $unedtrivial->questionsperday;
    }else{
        //If user has not answered anything in this UNEDTrivial, there are questions available for sure
        //(if there is any question, of course)
        return ($totalqu > 0);
    }
}

/**
 * Selects 3 question for the user
 *
 * @param $unedtrivial unedtrivial instance
 * @param $userid User id
 * @param $date Date for questions (UNIX time)
 */

function unedtrivial_locate_questions($unedtrivial,$userid,$date){
    global $DB;
    $unedid = $unedtrivial->id;
    $numquestions = $unedtrivial->questionsperday;
    
    $selected = array();
    $date_failure_ini = $date - $unedtrivial->retryerror*86400;
    $date_failure_end = $date_failure_ini + 86399;
    $date_success_ini = $date - $unedtrivial->retrysuccess*86400;
    $date_success_end = $date_success_ini + 86399;

    $sql1 = 'SELECT * '
            . 'FROM {unedtrivial_history} u '
            .'WHERE u.idunedtrivial = '.$unedid.' AND '
            .'      u.questionid <> -1 AND'
            . '     u.userid = '.$userid;
    
    $result1 = $DB->get_records_sql($sql1);
    $correct = $incorrect = array();
    $answered = array();
    $closed = array();
    $today = array();
    $lastresults = array();
    $available_correct = $available_incorrect = array();
    //Temp table [questionid,questionstate,questiondate] with lastest results per question
    foreach ($result1 as $row){
        $newrow = array();
        $locate = array_search($row->questionid, array_column($lastresults, 'questionid'));
        if($locate === false){
            $newrow['questionid'] = $row->questionid;
            $newrow['questionstate'] = $row->questionstate;
            $newrow['questiondate'] = $row->questiondate;            
            $lastresults[] = $newrow;
        }else{
            $lastresults[$locate]['questionstate'] = $row->questionstate;
            $lastresults[$locate]['questiondate'] = $row->questiondate;
        }
    }
    //Now we classify temp table in questions available and closed
    foreach ($lastresults as $lr){
        if ($lr['questionstate'] == $unedtrivial->timestocomplete){
            $closed[] = $lr['questionid'];
        }else if ($lr['questionstate'] == -1 && $lr['questiondate'] < $date_failure_end){
            $available_incorrect[] = $lr['questionid'];
        }else if ($lr['questionstate'] > 0 && $lr['questiondate'] < $date_success_end){
            $available_correct[] = $lr['questionid'];
        }
        if ($lr['questiondate'] == $date){
            $today[] = $lr['questionid'];
        }
        $answered[] = $lr['questionid'];
    }
   
    //Firstly we insert incorrect questions
    for($i=0;$i<min($numquestions,count($available_incorrect));$i++){
        $selected[] = $available_incorrect[$i];
    }
    //Now, if necessary, we insert correct questions
    if (count($selected) < $numquestions){
        $min = min($numquestions-count($selected),count($available_correct));
        for($i=0;$i<$min;$i++){
            $selected[] = $available_correct[$i];
        }
        //Finally, we insert new questions
        if(count($selected) < $numquestions){
            srand($unedid+$userid+$date); //Random seed is arguments dependant
            $sql2 = 'SELECT u.id '
                    . 'FROM {unedtrivial_questions} u '
                    .'WHERE u.idunedtrivial = '.$unedid;
            $result2 = $DB->get_records_sql($sql2);
            $allquestions = array();
            foreach($result2 as $row2){
                $allquestions[] = $row2->id;
            }
            $available_all = array_diff($allquestions,$answered);
            $available_all = array_values($available_all);
            $min = min($numquestions-count($selected),count($available_all));
            
            for($i=0;$i<$min;$i++){
                $random = rand(0,count($available_all)-1);
                $selected[] = $available_all[$random];
                unset($available_all[$random]); //Delete question selected, to avoid duplicates
                $available_all = array_values($available_all); //Re-sort array
            }
        }
    }    
    for ($i=count($selected);$i<$numquestions;$i++){
        //This should never happens... fill questions with null value
        $selected[] = -1;
    }
    //Cancel questions if user has answered some today
    for ($i=$numquestions-1;$i>=$numquestions-count($today);$i--){
        $selected[$i] = -1;
    }
    
    return $selected;
}

/**
 * Shows on screen all info related to the user's answer
 *
 * @param $res Result of question
 * @param $state State of answer
 * @param $reg Question data
 * @param $unedtrivial UNEDTrivial instance
 * @param $cm Course module object
 * @param $course Course object
 */

function unedtrivial_show_results($res,$state,$reg,$unedtrivial,$cm,$course){
    GLOBAL $DB,$USER,$CFG;
    $totalqu = unedtrivial_get_questions($unedtrivial->id);
    $closedqu = unedtrivial_get_user_closed_questions($unedtrivial, $USER->id);
    $score = unedtrivial_get_total_score($unedtrivial->id,$USER->id);
    $questionplain = filter_var($reg->question, FILTER_SANITIZE_STRING);
    
    //The question
    echo '<i><b>' . $questionplain . "</b></i><br><br>";
    //Results
    echo '<b><i class="fa fa-check-square-o" aria-hidden="true"></i>'. " " . 
            get_string('questionresult', 'unedtrivial') .'</b><br>';
    if ($res == 'success'){
        if($state == $unedtrivial->timestocomplete){
            $bonus = unedtrivial_get_bonus($unedtrivial, $reg->id, $USER->id);
            echo '<font color="green">'.get_string('success', 'unedtrivial').
                 " " . sprintf("%+d",$unedtrivial->scoresuccess+$bonus) . " " . 
                 get_string('points','unedtrivial'). " ";
            echo get_string('bonus', 'unedtrivial', $bonus) . '</font><br><br>';
        }else{
            echo '<font color="green">'.get_string('success', 'unedtrivial').
                 " " . sprintf("%+d",$unedtrivial->scoresuccess) . " " . 
                 get_string('points','unedtrivial') . '</font><br><br>';
        }
        echo '<table border="1" width="100%"><tr><td>';
        echo "<b>" . get_string('questionexplanation', 'unedtrivial') ."</b><br>";
        echo $reg->explanation . "</td></tr></table><br>";
        echo "<br>";
        //Question state
        echo '<b><i class="fa fa-flag" aria-hidden="true"></i>'. " " .
                get_string('questionstate', 'unedtrivial') .'</b><br>';
        if ($state < $unedtrivial->timestocomplete){
            echo get_string('nextattempt', 'unedtrivial') . " " 
                    . date("d-m-Y", time()+$unedtrivial->retrysuccess*86400) 
                    . "<br>";
        }else{
            echo get_string('questionclosed', 'unedtrivial', $unedtrivial->timestocomplete) . "<br>";
            if ($closedqu == $totalqu){
                //User has completed UNEDTrivial
                echo get_string('gamefinished', 'unedtrivial') . "<br>";
                //Update completion state
                $completion = new completion_info($course);
                if($completion->is_enabled($cm) && $unedtrivial->completionsubmit) {
                    $completion->update_state($cm,COMPLETION_COMPLETE);
                }
                //And send congratulations email
                send_congratulations_email($unedtrivial);
            }
        }
    }else{
        echo '<font color="red">'.get_string('failure', 'unedtrivial').
             " (" . sprintf("%+d",$unedtrivial->scoreerror) . " " . get_string('points','unedtrivial') .
             ')</font><br><br>';
        echo '<table border="1" width="100%"><tr><td>';
        echo "<b>" . get_string('correctansweris', 'unedtrivial') . "</b>";
        if ($reg->qtype == 1){
            echo "<br>";
            switch ($reg->answer){
                case 1:
                    echo $reg->option1; break;
                case 2:
                    echo $reg->option2; break;
                case 3:
                    echo $reg->option3; break;
                case 4:
                    echo $reg->option4; break;
            }
            echo "<br>";
        }else if ($reg->qtype == 2){
            echo "</font>";
            if (strpos($reg->answer, '1') !== false) echo "<br />" . $reg->option1;
            if (strpos($reg->answer, '2') !== false) echo "<br />" . $reg->option2;
            if (strpos($reg->answer, '3') !== false) echo "<br />" . $reg->option3;
            if (strpos($reg->answer, '4') !== false) echo "<br />" . $reg->option4;
            echo "<br><br>";
        }else{
            echo "</font>" . $reg->option1 . " ";
            if ($reg->answer == "1"){
                echo "(" . get_string('casesensitive', 'unedtrivial') . ")";
            }
            echo "<br>";
        }
        echo "<b>" . get_string('questionexplanation', 'unedtrivial') ."</b><br>";
        echo $reg->explanation . "</td></tr></table><br>";
        //Next attempt
        echo '<b><i class="fa fa-flag" aria-hidden="true"></i>'. " " .
                get_string('questionstate', 'unedtrivial') .'</b><br>';
        echo get_string('nextattempt', 'unedtrivial') . " " 
                . date("d-m-Y", time()+$unedtrivial->retryerror*86400) 
                . "<br>";
    }
    $sql = "SELECT u.id,u.questionstate"
         . "  FROM {unedtrivial_history} u"
         . " WHERE u.idunedtrivial = ".$reg->idunedtrivial." AND "
         . "       u.questionid = ".$reg->id;
    $results = $DB->get_records_sql($sql);
    $total = 0;
    $success = 0;
    foreach($results as $res){
        $total++;
        if ($res->questionstate != -1){
            $success++;
        }
    }
    echo get_string('answerstats1', 'unedtrivial') . " " . round($success/$total*100) . "%";
    echo "<br><br>";
    
    //Message to teacher
    $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
    $context = context_course::instance($course->id);
    $teachers = get_role_users($role->id, $context);
    $userid = reset($teachers)->id;
    $userto = $DB->get_record('user', array('id' => $userid));
    $url = new moodle_url($CFG->wwwroot.'/message/index.php', array('id' => $userto->id));
    echo '<b><i class="fa fa-commenting" aria-hidden="true"></i>' . " "
         . get_string('questionfeedback', 'unedtrivial') . "</b><br>";
    echo get_string('feedbacktext', 'unedtrivial') . " " .
            '<a href="' . $url . '" target="_blank">' . get_string('feedbacklink', 'unedtrivial') . '</a>' .
            ". " . get_string('feedbacktext2', 'unedtrivial');
    echo "<br><br>";
    
    //Mini scores table
    echo '<b><i class="fa fa-users" aria-hidden="true"></i>'. " " .
         get_string('questionranking','unedtrivial') . '</b><br>';
    echo get_string('totalscoregot','unedtrivial') . $score . "<br>";
    echo get_string('answerstats2','unedtrivial') . " " .
             $closedqu. "/" . $totalqu . " " . 
             get_string('answerstats3', 'unedtrivial', round($closedqu/$totalqu*100)) . "<br>";
    $table = unedtrivial_get_miniranking($unedtrivial->id,$USER->id);
    echo html_writer::table($table);
    echo "<br>";
    
    //Go back button
    echo html_writer::tag('button', get_string('close', 'unedtrivial'), 
                array('class'=>'myclass', 'type' => 'button','onclick'=>"window.open('', '_self', ''); window.close();"));
}

/**
 * Send an email with a congratulations message when an user closes all questions
 * greater and two positions lower (if possible)
 * @param $unedid UNEDTrivial ID
 * @param $userid User ID
 */
function send_congratulations_email($uned){
    global $DB,$USER;
    $address = $DB->get_record_sql('SELECT u.mail '
                    . '                FROM {unedtrivial_mails} u'
                    . '               WHERE u.idunedtrivial = ? AND'
                    . '                     u.userid = ?', array($uned->id,$USER->id));
    $subject = get_string('overviewchart1o4','unedtrivial').": ". $uned->name;
    $header  = 'MIME-Version: 1.0' . "\r\n";
    $header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $header .= 'From: UNEDTrivial <noreply@unedtrivial.com>' . "\r\n";
    $header .= 'Bcc: '.$address->mail."\r\n";
    $text =   '<html>'
            . '<head>'
            . '<title></title>'
            . '<body>'
            . '<p>' . get_string('congratulations1','unedtrivial') . '</p>'
            . '<p>' . get_string('congratulations2','unedtrivial') . '</p><br>'
            . '<p>' . get_string('congratulations3','unedtrivial') . '</p>'
            . '</body>'
            . '</html>';
    
    mail_sender($subject,$text,$header,$address->mail,false);
}

/**
 * Returns an HTML table containing the position of the user plus two positions
 * greater and two positions lower (if possible)
 * @param $unedid UNEDTrivial ID
 * @param $userid User ID
 */
function unedtrivial_get_miniranking($unedid,$userid){
    GLOBAL $DB;
    
    $allrows = $DB->get_records_sql('SELECT u.id,u.firstname,u.lastname,SUM(n.questionscore) AS sum '
                . '                    FROM {user} u'
                . '                    JOIN {unedtrivial_history} n ON n.userid = u.id '
                . '                   WHERE n.idunedtrivial = ? '
                . '                GROUP BY u.id,u.firstname,u.lastname'
                . '                ORDER BY sum DESC', array($unedid));
    
    $allrows = array_values($allrows); //reset array keys (0,1,2,3....)
    $pos = array_search($userid, array_column($allrows, 'id'));
    if ($pos <= 2){
        $min = 0; $max = min(4,count($allrows)-1);
    }else if ($pos >= count($allrows)-3){
        $min = max(0,count($allrows)-5); $max = count($allrows)-1;
    }else{
        $min = $pos-2; $max = $pos+2;
    }
    $selectedrows = array();
    for ($i=$min;$i<=$max;$i++){
        $selectedrows[] = $allrows[$i];
    }
    
    $table = new html_table();
    $table->head = array(get_string('leaderboardcol1', 'unedtrivial'),
                         get_string('leaderboardcol2', 'unedtrivial'),
                         get_string('leaderboardcol3', 'unedtrivial'));
    $i = $min;
    foreach($selectedrows as $row) {   
        $i = $i + 1;
        if ($row->id == $userid){
            $table->data[] = array('<font color="blue">'.$i.'</font>',
                                   '<font color="blue">'.$row->firstname." ".$row->lastname.'</font>',
                                   '<font color="blue">'.$row->sum.'</font>');
        }else{
            $table->data[] = array($i, $row->firstname." ".$row->lastname, $row->sum);
        }
    }
    return $table;
}

/**
 * Draws a stats bar related to student answers
 * 
 * @param $success Percentage of correct attempts
 */
function unedtrivial_draw_stats($success){
    $square = '<i class="fa fa-stop" aria-hidden="true"></i>';
    $green = round(10 * $success / 100);
    $red = 10 - $green;
    
    echo get_string('answerstats1', 'unedtrivial') . "<br>";
    echo '<font color="green">';
    for($i=0;$i<$green;$i++) {
        echo $square;
    }
    echo '</font><font color="red">';
    for($i=0;$i<$red;$i++) {
        echo $square;
    }
    echo "</font> " . $green . "%<br>";
}

/**
 * Draws a stats bar related to student completion percentage
 * 
 * @param $totalqu Total questions of UNEDTrivial
 * @param $closedqu Number of closed questions of an user
 */
function unedtrivial_draw_bar($totalqu,$closedqu){
    
    $square = '<i class="fa fa-stop" aria-hidden="true"></i>';
    $green = round(10 * $success / 100);
    $red = 10 - $green;
    echo '<font color="green">';
    for($i=0;$i<$green;$i++) {
        echo $square;
    }
    echo '</font><font color="red">';
    for($i=0;$i<$red;$i++) {
        echo $square;
    }
    echo "</font>";
}

/**
 * Get total score fron an user in a UNEDTrivial ID
 * 
 * @param $unedid UNEDTrivial ID
 * @param $userid User ID
 */
function unedtrivial_get_total_score($unedid,$userid){
    GLOBAL $DB;
    
    $total = $DB->get_record_sql('SELECT SUM(n.questionscore) as sum'
                . '                 FROM {unedtrivial_history} n'
                . '                WHERE n.idunedtrivial = ? AND'
                . '                      n.userid = ?', array($unedid,$userid));
    return $total->sum;
}

/**
 * Get total number of participants
 * 
 * @param $unedid UNEDTrivial ID
 */
function unedtrivial_get_participants($unedid){
    GLOBAL $DB;
    
    $total = $DB->count_records_sql('SELECT COUNT(id)'
                . '                    FROM {unedtrivial_mails} m'
                . '                   WHERE m.idunedtrivial = ?', array($unedid));
    return $total;
}

/**
 * Returns number of closed questions for an user
 * @param $uned UNEDTrivial instance
 * @param $userid User ID
 */
function unedtrivial_get_user_closed_questions($uned,$userid){
    GLOBAL $DB;
    $totalcl = $DB->count_records_sql('SELECT COUNT(id)'
                . '                      FROM {unedtrivial_history} h'
                . '                     WHERE h.idunedtrivial = ? AND'
                . '                           h.userid = ? AND'
                . '                           h.questionstate = ?', 
                                      array($uned->id,$userid,$uned->timestocomplete));
    
    return $totalcl;
}

/**
 * Get total number of questions
 * 
 * @param $unedid UNEDTrivial ID
 */
function unedtrivial_get_questions($unedid){
    GLOBAL $DB;
    
    $total = $DB->count_records_sql('SELECT COUNT(id)'
                . '                    FROM {unedtrivial_questions} q'
                . '                   WHERE q.idunedtrivial = ?', array($unedid));
    return $total;
}

/**
 * Get total number of user answers
 * 
 * @param $unedid UNEDTrivial ID
 * @param $ini Start date to search (optional)
 * @param $end End date to search (optional)
 */
function unedtrivial_get_answers($unedid,$ini=0,$end=0){
    GLOBAL $DB;
    
    if ($ini == 0){
        $total = $DB->count_records_sql('SELECT COUNT(id)'
                    . '                    FROM {unedtrivial_history} h'
                    . '                   WHERE h.idunedtrivial = ? AND'
                    . '                         h.questionid <> -1', array($unedid));
    }else{
        $total = $DB->count_records_sql('SELECT COUNT(id)'
                   . '                     FROM {unedtrivial_history} h'
                   . '                    WHERE h.idunedtrivial = ? AND'
                   . '                          h.questionid <> -1 AND'
                   . '                          h.questiondate >= ? AND'
                   . '                          h.questiondate <= ?' , array($unedid,$ini,$end));
    }
    return $total;
}

/**
 * Generate and returns UNEDTrivial difficulty
 * (Total_errors/Total_answers * 100)
 * 
 * @param $unedid UNEDTrivial ID
 */
function unedtrivial_get_difficulty($unedid){
    GLOBAL $DB;
    
    $history = $DB->get_records_sql('SELECT *'
                   . '                 FROM {unedtrivial_history} h'
                   . '                WHERE h.idunedtrivial = ? AND'
                   . '                      h.questionid <> -1'            
                   . '             ORDER BY h.userid ASC, h.questiondate ASC' , array($unedid));
    
    $success1 = $success2 = $wrong1 = $wrong2 = 0;
    unedtrivial_get_answers_stats($history, $success1, $success2, $wrong1, $wrong2);
    $all = $success1+$wrong1;
    if ($all == 0){
        return 0;
    }else{
        return round($wrong1/$all*100);
    }
}

/**
 * Returns Overview progress chart
 * 
 * @param $uned UNEDTrivial instance
 * @param $chart Chart instance
 * @param $table Table with chart data
 * #param $cmid Course module ID (for mail button)
 */
function unedtrivial_chart_overview_progress($uned,&$chart,&$table,$cmid){
    GLOBAL $CFG, $OUTPUT;  
    
    $notstarted = $giveuprisk = $progressing = $completed = 0;
    unedtrivial_get_users_progress($uned,$notstarted,$giveuprisk,$progressing,$completed);
    $all = $notstarted+$giveuprisk+$progressing+$completed;
    $progressing = $progressing + $giveuprisk; //"Give up risk" will be absorbed by "Progressing"
    $all = max(1,$all);
    //Generate table
    $table = new html_table();
    $table->head = array('','','','');
    $bMail = new single_button(new moodle_url('teacheroptions.php', 
                                              array('id' => $cmid, 'option' => '3','destiny' => '2')),
                               get_string('overviewchart1button','unedtrivial'));
    $table->data[] = array(get_string('overviewchart1o1','unedtrivial'),$notstarted,
                           round($notstarted/$all*100)."%",
                           "<center>".$OUTPUT->render($bMail)."</center>");
    $bMail = new single_button(new moodle_url('teacheroptions.php', 
                                              array('id' => $cmid, 'option' => '3','destiny' => '4')),
                               get_string('overviewchart1button','unedtrivial'));
    $table->data[] = array(get_string('overviewchart1o3','unedtrivial'),$progressing,
                           round($progressing/$all*100)."%",
                           "<center>".$OUTPUT->render($bMail)."</center>");
    $bMail = new single_button(new moodle_url('teacheroptions.php', 
                                              array('id' => $cmid, 'option' => '3','destiny' => '5')),
                               get_string('overviewchart1button','unedtrivial'));
    $table->data[] = array(get_string('overviewchart1o4','unedtrivial'),$completed,
                           round($completed/$all*100)."%",
                           "<center>".$OUTPUT->render($bMail)."</center>");
    //Generate chart (if Moodle version is 3.2 or upper)
    if (substr($CFG->version,0,8) >= "20161205"){
        $chart = new \core\chart_pie();
        $CFG->chart_colorset = ['#5c626d', '#09bcd8', '#09ba15'];
        $series = new core\chart_series(get_string('participantstab','unedtrivial'),
                [$notstarted,$progressing,$completed]);
        $chart->add_series($series);
        $chart->set_labels(array(get_string('overviewchart1o1','unedtrivial'),
//                                 get_string('overviewchart1o2','unedtrivial'),
                                 get_string('overviewchart1o3','unedtrivial'),
                                 get_string('overviewchart1o4','unedtrivial')));
    }
}

/**
 * Returns all amounts related to participants progress
 * @param type $uned UNEDTrivial instance
 * @param type $notstarted People who have not started
 * @param type $giveuprisk People who is in risk of give up the activity
 * @param type $progressing People who is progressing 
 * @param type $completed People who have completed the activity
 */
function unedtrivial_get_users_progress($uned,&$notstarted,&$giveuprisk,&$progressing,&$completed){
    GLOBAL $DB;
    
    $totalqu = unedtrivial_get_questions($uned->id);
    $users = $DB->get_records_sql('SELECT id, userid, mail'
                   . '               FROM {unedtrivial_mails} m'
                   . '              WHERE m.idunedtrivial = ?' , array($uned->id));
    $history = $DB->get_records_sql('SELECT *'
                   . '                 FROM {unedtrivial_history} h'
                   . '                WHERE h.idunedtrivial = ? AND'
                   . '                      h.questionid <> -1'
                   . '             ORDER BY h.questiondate DESC' , array($uned->id));
    foreach($users as $user){
        switch (unedtrivial_get_user_progress($uned,$user->userid,$history,$totalqu)){
            case 'complete':
                $completed++;
                break;
            case 'notstarted':
                $notstarted++;
                break;
            case 'giveuprisk':
                $giveuprisk++;
                break;
            case 'progressing':
                $progressing++;
                break;
        }
    }
}

/**
 * Returns Overview knowledge chart
 * 
 * @param $uned UNEDTrivial instance
 * @param $chart Chart instance
 * @param $table Table with chart data
 * @param $index Performance improvement index
 */
function unedtrivial_chart_overview_knowledge($uned, &$chart, &$table,&$index){
    GLOBAL $DB, $CFG;
    
    $history = $DB->get_records_sql('SELECT *'
                   . '                 FROM {unedtrivial_history} h'
                   . '                WHERE h.idunedtrivial = ? AND'
                   . '                      h.questionid <> -1'            
                   . '             ORDER BY h.userid ASC, h.questiondate ASC' , array($uned->id));
    
    $success1 = $success2 = $wrong1 = $wrong2 = 0;
    unedtrivial_get_answers_stats($history, $success1, $success2, $wrong1, $wrong2);
    $all1 = $success1+$wrong1;
    $all2 = $success2+$wrong2;
    $psuccess1 = $psuccess2 = $pwrong1 = $pwrong2 = 0;
    if ($all1 > 0){
        $psuccess1 = round($success1/$all1*100);
        $pwrong1 = round($wrong1/$all1*100);
    }
    if ($all2 > 0){
        $psuccess2 = round($success2/$all2*100);
        $pwrong2 = round($wrong2/$all2*100);
    }
    
    //Generate table
    $table = new html_table();
    $table->head = array('','','');
    $table->data[] = array(get_string('overviewchart2o1','unedtrivial'),
                           $success1,$psuccess1."%");
    $table->data[] = array(get_string('overviewchart2o2','unedtrivial'),
                           $success2,$psuccess2."%");
    $table->data[] = array(get_string('overviewchart2o3','unedtrivial'),
                           $wrong1,$pwrong1."%");
    $table->data[] = array(get_string('overviewchart2o4','unedtrivial'),
                           $wrong2,$pwrong2."%");
    
    //Generate chart (if Moodle version is 3.2 or upper)
    if (substr($CFG->version,0,8) >= "20161205"){
        $chart = new \core\chart_bar();
        $chart->set_title(get_string('overviewchart2title','unedtrivial'));
        $yaxis = $chart->get_yaxis(0, true);
        $yaxis->set_max(100);
        $chart->set_yaxis($yaxis);
        $series = new core\chart_series(get_string('overviewchart2legend','unedtrivial'),
                [$psuccess1,$psuccess2]);
        $chart->add_series($series);
        $chart->set_labels(array(get_string('overviewchart2o1b','unedtrivial'),
                                 get_string('overviewchart2o2b','unedtrivial')));
    }
    
    //Generate performance improvement index
    if ($psuccess1 > 0){
        $index = round(($psuccess2-$psuccess1)/$psuccess1*100);
    }else{
        $index = 0;
    }
}

/**
 * Returns number of right and wrong answers for the first and second try of users
 * 
 * @param type $history UNEDTrivial answer history (get fron DB)
 * @param type $success1 Right answers for first try
 * @param type $success2 Right answers for second try
 * @param type $wrong1 Wrong answers for first try
 * @param type $wrong2 Wrong answers for second try
 */
function unedtrivial_get_answers_stats($history,&$success1,&$success2,&$wrong1,&$wrong2){
    $s1 = $s2 = $w1 = $w2 = array();
    $userid = -1;
    foreach($history as $row){
        if ($userid == -1){
            //This is the first line
            $userid = $row->userid;
        }else if ($row->userid != $userid){
            //Check results
            $success1 += count($s1);
            $success2 += count($s2);
            $wrong1 += count($w1);
            $wrong2 += count($w2);
            $s1 = $s2  = $w1 = $w2 = array();
            $userid = $row->userid;
        }
        //Store results
        if ($row->questionstate > 0){
            if (!in_array($row->questionid, $s1) && !in_array($row->questionid, $w1)){
                $s1[] = $row->questionid;
            }else if (!in_array ($row->questionid, $s2) && !in_array($row->questionid, $w2)){
                $s2[] = $row->questionid;
            }
        }else{
            if (!in_array($row->questionid, $s1) && !in_array($row->questionid, $w1)){
                $w1[] = $row->questionid;
            }else if (!in_array ($row->questionid, $s2) && !in_array($row->questionid, $w2)){
                $w2[] = $row->questionid;
            }
        }
    }
    //Check results (again, for last user in table)
    $success1 += count($s1);
    $success2 += count($s2);
    $wrong1 += count($w1);
    $wrong2 += count($w2);
}

/**
 * Returns Activity level chart
 * 
 * @param $uned UNEDTrivial instance
 * @param $chart Chart instance
 * @param $table Table with chart data
 * @param $ini Start time
 * @param $cmid Course module ID (for mail button)
 * @param $period Period index selected
 */
function unedtrivial_chart_activity($uned,&$chart,&$table,$ini,$cmid,$period){
    GLOBAL $DB, $CFG, $OUTPUT;
    
    $questionsinperiod = floor((time()-$ini)/86400) * $uned->questionsperday;
    $questionsinperiod = max($questionsinperiod,$uned->questionsperday); //To avoid DIV 0
    $history = $DB->get_records_sql('SELECT id,COUNT(id) as total,userid'
                   . '                 FROM {unedtrivial_history} h'
                   . '                WHERE h.idunedtrivial = ? AND'
                   . '                      (h.questiondate >= ? OR h.questiondate = 0)'
                   . '             GROUP BY userid,id' , array($uned->id,$ini));
    $veryactive = $active = $littleactive = $inactive = 0;
    foreach($history as $row){
        if ($row->total == 1){
            //If user has answered any question before, is an inactive user
            $numans = $DB->count_records_sql('SELECT COUNT(id)'
                    . '                         FROM {unedtrivial_history} h'
                    . '                        WHERE h.idunedtrivial = ? AND'
                    . '                              h.userid = ? AND'
                    . '                              h.questionid <> -1',
                                             array($uned->id,$row->userid));
            if ($numans > 0){
                $inactive++;
            }
        }else{
            $perc = $row->total/$questionsinperiod*100;
            if ($perc >= 70){
                $veryactive++;
            }else if ($perc < 70 && $perc >= 50){
                $active++;
            }else if ($perc < 50 && $perc > 0){
                $littleactive++;
            }
        }
    }
    $all = $veryactive+$active+$littleactive+$inactive;
    $all = max(1,$all);
    //Generate table
    $table = new html_table();
    $table->head = array('','','','');
    $bMail = new single_button(new moodle_url('teacheroptions.php', 
                                              array('id' => $cmid, 'option' => '3','special' => $period.'1')),
                               get_string('overviewchart1button','unedtrivial'));
    $table->data[] = array(get_string('activitycharto1','unedtrivial'),$veryactive,
                           round($veryactive/$all*100)."%",
                           "<center>".$OUTPUT->render($bMail)."</center>");
    $bMail = new single_button(new moodle_url('teacheroptions.php', 
                                              array('id' => $cmid, 'option' => '3','special' => $period.'2')),
                               get_string('overviewchart1button','unedtrivial'));
    $table->data[] = array(get_string('activitycharto2','unedtrivial'),$active,
                           round($active/$all*100)."%",
                           "<center>".$OUTPUT->render($bMail)."</center>");
    $bMail = new single_button(new moodle_url('teacheroptions.php', 
                                              array('id' => $cmid, 'option' => '3','special' => $period.'3')),
                               get_string('overviewchart1button','unedtrivial'));
    $table->data[] = array(get_string('activitycharto3','unedtrivial'),$littleactive,
                           round($littleactive/$all*100)."%",
                           "<center>".$OUTPUT->render($bMail)."</center>");
    $bMail = new single_button(new moodle_url('teacheroptions.php', 
                                              array('id' => $cmid, 'option' => '3','special' => $period.'4')),
                               get_string('overviewchart1button','unedtrivial'));
    $table->data[] = array(get_string('activitycharto4','unedtrivial'),$inactive,
                           round($inactive/$all*100)."%",
                           "<center>".$OUTPUT->render($bMail)."</center>");
    $table->data[] = array('<b>'.get_string('total','unedtrivial').'</b>','<b>'.$all.'</b>','<b>100%</b>','');
    //Generate chart (if Moodle version is 3.2 or upper)
    if (substr($CFG->version,0,8) >= "20161205"){
        $chart = new \core\chart_pie();
        $CFG->chart_colorset = ['#09ba15', '#fff600', '#ff1500', '#000000'];
        $series = new core\chart_series("Activity level",
                [$veryactive,$active,$littleactive,$inactive]);
        $chart->add_series($series);
        $chart->set_labels(array(get_string('activitycharto1','unedtrivial'),
                                 get_string('activitycharto2','unedtrivial'),
                                 get_string('activitycharto3','unedtrivial'),
                                 get_string('activitycharto4','unedtrivial')));
    }
}

/**
 * Returns Table with participants statistics
 * 
 * @param $uned UNEDTrivial instance
 * @param $id Course Module ID
 * @param $sort Sort by column
 */
function unedtrivial_table_participants($uned,$id,$sort=0){
    GLOBAL $DB;
    
    $totalqu = unedtrivial_get_questions($uned->id);
    $participants = $DB->get_records_sql('SELECT u.id,u.firstname,u.lastname'
                . '                         FROM {user} u'
                . '                         JOIN {unedtrivial_mails} m ON m.userid = u.id'
                . '                        WHERE m.idunedtrivial = ? ', array($uned->id));
    $history = $DB->get_records_sql('SELECT *'
                   . '                 FROM {unedtrivial_history} h'
                   . '                WHERE h.idunedtrivial = ? AND'
                   . '                      h.questionid <> -1'            
                   . '             ORDER BY h.userid ASC, h.questiondate DESC' , array($uned->id));
    $history2 = $DB->get_records_sql('SELECT *'
                   . '                 FROM {unedtrivial_history} h'
                   . '                WHERE h.idunedtrivial = ? AND'
                   . '                      h.questionid <> -1', array($uned->id));
    $origin = unedtrivial_get_activity_origin($history2,$uned->timecreated);
    $questionsinperiod = floor((time()-$origin)/86400) * $uned->questionsperday;
    $questionsinperiod = max($questionsinperiod,$uned->questionsperday); //To avoid DIV 0
    $sorticon1 = '<a href="stats.php?id='.$id.'&option=1&sort=1">'.
                '<i class="fa fa-play fa-rotate-90" aria-hidden="true"></i></a>';
    $sorticon2 = '<a href="stats.php?id='.$id.'&option=1&sort=2">'.
                '<i class="fa fa-play fa-rotate-90" aria-hidden="true"></i></a>';
    $sorticon3 = '<a href="stats.php?id='.$id.'&option=1&sort=3">'.
                '<i class="fa fa-play fa-rotate-90" aria-hidden="true"></i></a>';
    $table = new html_table();
    $table->head = array(get_string('participantstable1','unedtrivial') . " " . $sorticon1,
                         get_string('participantstable2','unedtrivial') . " " . $sorticon2,
                         get_string('participantstable3','unedtrivial') . " " . $sorticon3,
                         get_string('participantstable4','unedtrivial'));
    foreach($participants as $p) {   
        switch(unedtrivial_get_user_progress($uned,$p->id,$history,$totalqu)){
            case 'notstarted':
                $progress = 0;
                break;
            case 'complete':
                $progress = 3;
                break;
            case 'giveuprisk':
                $progress = 1;
                break;
            case 'progressing':
                $progress = 2;
                break;
        }
        
        $totalans = $DB->count_records_sql('SELECT COUNT(id) as total'
                       . '                 FROM {unedtrivial_history} h'
                       . '                WHERE h.idunedtrivial = ? AND'
                       . '                      h.userid = ?', array($uned->id,$p->id));
        if ($totalans == 1){
            $perc = -1;
        }else{
            $perc = $totalans/$questionsinperiod*100;
        }
        
        $knowledge = unedtrivial_get_user_knowledge($uned,$p->id,$history2,$totalqu);
        $table->data[] = array(unedtrivial_toASCII($p->firstname)." ".unedtrivial_toASCII($p->lastname),
                               $progress,$perc,$knowledge);
    }
    if ($sort == 1){
        $table->data = unedtrivial_array_msort($table->data,array('0'=>SORT_ASC));
    }else if ($sort == 2){
        $table->data = unedtrivial_array_msort($table->data,array('1'=>SORT_DESC));
    }else if ($sort == 3){
        $table->data = unedtrivial_array_msort($table->data,array('2'=>SORT_DESC));
    }
    //We put now the names
    for($i=0;$i<count($table->data);$i++){
        if ($table->data[$i][1] == '0'){
            $table->data[$i][1] = get_string('overviewchart1o1','unedtrivial');
        }else if ($table->data[$i][1] == '3'){
            $table->data[$i][1] = get_string('overviewchart1o4','unedtrivial');
        }else if ($table->data[$i][1] == '1'){
            $table->data[$i][1] = get_string('overviewchart1o2','unedtrivial');
        }else if ($table->data[$i][1] == '2'){
            $table->data[$i][1] = get_string('overviewchart1o3','unedtrivial');
        }
        if ($table->data[$i][2] == -1){
            $table->data[$i][2] = get_string('activitycharto4','unedtrivial');
        }else if ($table->data[$i][2] >= 70){
            $table->data[$i][2] = get_string('activitycharto1','unedtrivial');
        }else if ($table->data[$i][2] < 70 && $table->data[$i][2] >= 50){
            $table->data[$i][2] = get_string('activitycharto2','unedtrivial');
        }else if ($table->data[$i][2] < 50 && $table->data[$i][2] > 0){
            $table->data[$i][2] = get_string('activitycharto3','unedtrivial');
        }
    }
    return $table;
}

/**
 * Returns String with knowledge data for an user
 * 
 * @param $uned UNEDTrivial instance
 * @param $userid User ID number
 * @param $history UNEDTrivial_history query result for all participants
 * @param $totalq Total number of questions for current UNEDTrivial
 */
function unedtrivial_get_user_knowledge($uned, $userid, $history, $totalqu){
    $s1 = $s2 = $w1 = $w2 = $distinct = $closed = array();
    foreach($history as $row){
        if ($row->userid == $userid){
            //Store results
            if ($row->questionstate > 0){
                if (!in_array($row->questionid, $s1) && !in_array($row->questionid, $w1)){
                    $s1[] = $row->questionid;
                }else if (!in_array($row->questionid, $s2) && !in_array($row->questionid, $w2)){
                    $s2[] = $row->questionid;
                }
            }else{
                if (!in_array($row->questionid, $s1) && !in_array($row->questionid, $w1)){
                    $w1[] = $row->questionid;
                }else if (!in_array($row->questionid, $s2) && !in_array($row->questionid, $w2)){
                    $w2[] = $row->questionid;
                }
            }
            if (!in_array($row->questionid,$distinct)){
                $distinct[] = $row->questionid;
            }
            if ($row->questionstate == $uned->timestocomplete){
                $closed[] = $row->questionid;
            }
        }
    }
    //Check results
    $success1 = count($s1);
    $success2 = count($s2);
    $wrong1 = count($w1);
    $wrong2 = count($w2);
    $all1 = $success1+$wrong1;
    $all2 = $success2+$wrong2;
    if ($all2 > 0){
        $psuccess2 = round($success2/$all2*100);
    }else{
        $psuccess2 = -1;
    }
    if ($all1 > 0){
        $psuccess1 = round($success1/$all1*100);
        if ($psuccess2 != -1){
            $index = $psuccess2-$psuccess1;
        }else{
            $index = -999999;
        }
    }else{
        $psuccess1 = -1;
        $index = -999999;
    }        
    
    if ($psuccess1 != -1){
        $text = get_string('knowledge1','unedtrivial') . $psuccess1 . "%<br>";
    }else{
        $text = get_string('knowledge1','unedtrivial') . "-<br>";
    }
    if ($psuccess2 != -1){
        $text .= get_string('knowledge2','unedtrivial') . $psuccess2 . "%<br>";
    }else{
        $text .= get_string('knowledge2','unedtrivial') . "-<br>";
    }
    if ($index != -999999){
        $text .= get_string('knowledge3','unedtrivial') . $index . "%<br>";
    }else{
        $text .= get_string('knowledge3','unedtrivial') . "-<br>";
    }
    $text .= get_string('knowledge4','unedtrivial') . count($distinct) . 
             " " . get_string('of','unedtrivial') . " " . $totalqu . "<br>";
    $text .= get_string('knowledge5','unedtrivial') . count($closed) .
             " " . get_string('of','unedtrivial') . " " . $totalqu . "<br>";
    return $text;
}

/**
 * Returns Progress related string
 * 
 * @param $uned UNEDTrivial instance
 * @param $userid User ID number
 * @param $history UNEDTrivial_history query result for all participants
 * @param $totalqu Total question number of current UNEDTrivial
 */
function unedtrivial_get_user_progress($uned, $userid, $history, $totalqu){
    $linea = array();
    $linea['userid'] = $userid;
    $lasttime = 0;
    foreach($history as $row){
        if ($row->userid == $userid){
            $lasttime = $row->questiondate;
            break;
        }
    }
    if ($lasttime == 0){
        $linea['lasttime'] = '-';
    }else{
        $linea['lasttime'] = $lasttime;
    }
    $linea['closedqu'] = 0;
    foreach($history as $row){
        if ($row->userid == $userid && $row->questionstate == $uned->timestocomplete){
            $linea['closedqu']++;
        }
    }
    
    if ($linea['closedqu'] >= $totalqu){
        return 'complete';
    }elseif ($linea['lasttime'] == '-'){
        return 'notstarted';
    }else{
        if (time()-$linea['lasttime'] > 604800){
            return 'giveuprisk';
        }else{
            return 'progressing';
        }
    }
}

/**
 * Returns Table with questions statistics
 * 
 * @param $uned UNEDTrivial instance
 * @param $id Course module ID
 * @param $sort Sorting index
 */
function unedtrivial_table_questions($uned,$id,$sort=0){
    GLOBAL $DB;
    
    $questions = $DB->get_records_sql('SELECT *'
            . '                          FROM {unedtrivial_questions} q'
            . '                         WHERE q.idunedtrivial = ?', array($uned->id));
    $history = $DB->get_records_sql('SELECT *'
                   . '                 FROM {unedtrivial_history} h'
                   . '                WHERE h.idunedtrivial = ? AND'
                   . '                      h.questionid <> -1'            
                   . '             ORDER BY h.userid ASC, h.questiondate ASC ' , array($uned->id));
    
    $table = new html_table();
    $sorticon1 = '<a href="stats.php?id='.$id.'&option=2&sort=1">'.
                '<i class="fa fa-play fa-rotate-90" aria-hidden="true"></i></a>';
    $sorticon2 = '<a href="stats.php?id='.$id.'&option=2&sort=2">'.
                '<i class="fa fa-play fa-rotate-90" aria-hidden="true"></i></a>';
    $sorticon3 = '<a href="stats.php?id='.$id.'&option=2&sort=3">'.
                '<i class="fa fa-play fa-rotate-90" aria-hidden="true"></i></a>';
    $sorticon4 = '<a href="stats.php?id='.$id.'&option=2&sort=4">'.
                '<i class="fa fa-play fa-rotate-90" aria-hidden="true"></i></a>';
    $sorticon5 = '<a href="stats.php?id='.$id.'&option=2&sort=5">'.
                '<i class="fa fa-play fa-rotate-90" aria-hidden="true"></i></a>';
    $sorticon6 = '<a href="stats.php?id='.$id.'&option=2&sort=6">'.
                '<i class="fa fa-play fa-rotate-90" aria-hidden="true"></i></a>';
    $sorticon7 = '<a href="stats.php?id='.$id.'&option=2&sort=7">'.
                '<i class="fa fa-play fa-rotate-90" aria-hidden="true"></i></a>';
    $table->head = array(get_string('questionstable1','unedtrivial'),
                         get_string('questionstable2','unedtrivial') . " " . $sorticon1,
                         get_string('questionstable3','unedtrivial') . " " . $sorticon2,
                         get_string('questionstable3b','unedtrivial') . " " . $sorticon3,
                         get_string('questionstable4','unedtrivial') . " " . $sorticon4,
                         get_string('questionstable5','unedtrivial') . " " . $sorticon5,
                         get_string('questionstable6','unedtrivial') . " " . $sorticon6,
                         get_string('questionstable7','unedtrivial') . " " . $sorticon7);
    foreach ($questions as $q){
        $psuccess1 = $psuccess2 = -1;
        $dif = -1;
        $questionplain = filter_var($q->question, FILTER_SANITIZE_STRING);
        if (strlen($questionplain) > 60){
            $questionplain = mb_substr($questionplain,0,60,'UTF-8') . "...";
        }
        $timespart = unedtrivial_get_question_times($q->id,$history,'participants');
        $timesasked = unedtrivial_get_question_times($q->id,$history,'asked');
        if ($timespart > 0){
            $avgtimes = number_format(round($timesasked/$timespart,2),2);
        }else{
            $avgtimes = number_format(0,2);
        }
        $timesclosed = unedtrivial_get_question_times($q->id,$history,'closed',$uned->timestocomplete);
        $timessuccess1st = unedtrivial_get_question_times($q->id,$history,'success1st');
        $timessuccess2nd = unedtrivial_get_question_times($q->id,$history,'success2nd');
        $aux_all1st = unedtrivial_get_question_times($q->id,$history,'all1st');
        $aux_all2nd = unedtrivial_get_question_times($q->id,$history,'all2nd');
        if ($aux_all1st > 0){
            $psuccess1 = round($timessuccess1st/$aux_all1st*100);
        }
        if ($aux_all2nd > 0){
            $psuccess2 = round($timessuccess2nd/$aux_all2nd*100);
        }
        if ($psuccess2 != -1){
            $dif = ($psuccess2-$psuccess1);
        }
        
        $table->data[] = array($questionplain,$timespart,$timesasked,$avgtimes,$timesclosed,
                               $psuccess1,$psuccess2,$dif);
    }
    switch ($sort) {
        case 1:
            $table->data = unedtrivial_array_msort($table->data,array('1'=>SORT_DESC)); break;
        case 2:
            $table->data = unedtrivial_array_msort($table->data,array('2'=>SORT_DESC)); break;
        case 3:
            $table->data = unedtrivial_array_msort($table->data,array('3'=>SORT_DESC)); break;
        case 4:
            $table->data = unedtrivial_array_msort($table->data,array('4'=>SORT_DESC)); break;
        case 5:
            $table->data = unedtrivial_array_msort($table->data,array('5'=>SORT_DESC)); break;
        case 6:
            $table->data = unedtrivial_array_msort($table->data,array('6'=>SORT_DESC)); break;
        case 7:
            $table->data = unedtrivial_array_msort($table->data,array('7'=>SORT_DESC)); break;
    }
    //We put now the % symbol
    for($i=0;$i<count($table->data);$i++){
        if ($table->data[$i][5] == '-1'){
            $table->data[$i][5] = '';
        }else{
            $table->data[$i][5] = $table->data[$i][5] . "%";
        }
        if ($table->data[$i][6] == '-1'){
            $table->data[$i][6] = '';
            $table->data[$i][7] = '';
        }else{
            $table->data[$i][6] = $table->data[$i][6] . "%";
            $table->data[$i][7] = $table->data[$i][7] . "%";
        }
    }
    
    return $table;
}

/**
 * Returns stats of different query types related to a question
 * 
 * @param $qid Question ID
 * @param $history UNEDTrivial_history query result for all participants
 * @param $search What will be searched
 * @param (optional) $timestocomplete Successes that close a question (only for $search == 'closed')
 */
function unedtrivial_get_question_times($qid,$history,$search,$timestocomplete=0){
    $times = 0;
    switch($search){
        case 'participants':
            $userid = -1;
            foreach ($history as $row){
                if ($row->questionid == $qid && $row->userid != $userid){
                    $userid = $row->userid;
                    $times++;
                }
            }
            break;
        case 'asked':
            foreach ($history as $row){
                if ($row->questionid == $qid){
                    $times++;
                }
            }
            break;
        case 'success1st':
            $found = 0;
            $userid = -1;
            foreach ($history as $row){
                if ($row->userid != $userid){
                    if ($found > 0) $times++;
                    $found = 0;
                    $userid = $row->userid;
                }
                if ($found == 0 && $row->questionid == $qid){
                    $found = $row->questionstate;
                }
            }
            if ($found > 0) $times++; //For last userid of table
            break;
        case 'all1st':
            $found = false;
            $userid = -1;
            foreach ($history as $row){
                if ($row->userid != $userid){
                    if ($found) $times++;
                    $found = false;
                    $userid = $row->userid;
                }
                if (!$found){
                    if ($row->questionid == $qid){
                        $found = true;
                    }
                }
            }
            if ($found) $times++; //For last userid of table
            break;
        case 'success2nd':
            $found1 = $found2 = 0;
            $userid = -1;
            foreach ($history as $row){
                if ($row->userid != $userid){
                    if ($found2 > 0) $times++;
                    $found1 = $found2 = 0;
                    $userid = $row->userid;
                }
                if ($found1 == 0 && $row->questionid == $qid){
                    $found1 = $row->questionstate;
                }else if ($found2 == 0 && $row->questionid == $qid){
                    $found2 = $row->questionstate;
                }
            }
            if ($found2 > 0) $times++; //For last userid of table
            break;
        case 'all2nd':
            $found1 = $found2 = false;
            $userid = -1;
            foreach ($history as $row){
                if ($row->userid != $userid){
                    if ($found2) $times++;
                    $found1 = $found2 = false;
                    $userid = $row->userid;
                }
                if (!$found1){
                    if ($row->questionid == $qid){
                        $found1 = true;
                    }
                }else if (!$found2){
                    if ($row->questionid == $qid){
                        $found2 = true;
                    }
                }
            }
            if ($found2) $times++; //For last userid of table
            break;
        case 'closed':
            foreach ($history as $row){
                if ($row->questionid == $qid && $row->questionstate == $timestocomplete){
                    $times++;
                }
            }
            break;
    }
    return $times;
}

/**
 * Sort a multidimensional array
 * @param $array Array to be sorted
 * @param $cols Columns and criteria
 */
function unedtrivial_array_msort($array, $cols)
{
    $colarr = array();
    foreach ($cols as $col => $order) {
        $colarr[$col] = array();
        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
    }
    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
    }
    $eval = substr($eval,0,-1).');';
    eval($eval);
    $ret = array();
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            $k = substr($k,1);
            if (!isset($ret[$k])) $ret[$k] = $array[$k];
            $ret[$k][$col] = $array[$k][$col];
        }
    }
    return $ret;
}

/**
 * Construct mailto links in HTML format
 * @param $teachermails String containing all addresses separated by comma
 */
function unedtrivial_build_teachers_link($teachermails){
    $text = get_string('replyteachers','unedtrivial') . " ";
    $tok = strtok($teachermails, ",\n");
    while($tok !== false){
        $text = $text . '<a href="mailto:'.trim($tok).'">'.trim($tok)."</a> ";
        $tok = strtok(",\n");
    }
    return $text;
}

/**
 * Returns the amount related to close question bonus
 * @param $uned UNEDTrivial instance
 * @param $qid Question ID
 * @param $userid User ID
 * @param $p1 Flag to count one register more (when question reg is not included yet in DB)
 */
function unedtrivial_get_bonus($uned,$qid,$userid,$p1 = 0){
    GLOBAL $DB;
    
    $times   = $DB->count_records_sql('SELECT COUNT(*)'
                   . '                   FROM {unedtrivial_history} h'
                   . '                  WHERE h.idunedtrivial = ? AND'
                   . '                        h.userid = ? AND'
                   . '                        h.questionid = ?' , array($uned->id,$userid,$qid));
    $times += $p1;
    return round($uned->timestocomplete * $uned->scoresuccess / $times);
}

/**
 * Return a string containing all email addresses selected
 * 
 * @param $uned UNEDTrivial instance
 * @param $destiny Group of users selected as mail destiny
 * @param $special Special group of users as mail destiny (override $destiny)
 */
function unedtrivial_get_email_addresses($uned,$destiny,$special){
    GLOBAL $DB;
    
    $toUser = "";
    if ($special == 0){
        $totalqu = unedtrivial_get_questions($uned->id);
        $mails = $DB->get_records_sql('SELECT u.userid,u.mail '
                      . '                FROM {unedtrivial_mails} u'
                      . '               WHERE u.idunedtrivial = ? ', array($uned->id));
        $history = $DB->get_records_sql('SELECT *'
                    . '                    FROM {unedtrivial_history} h'
                    . '                   WHERE h.idunedtrivial = ? AND'
                    . '                         h.questionid <> -1'
                    . '                ORDER BY h.userid ASC, h.questiondate DESC' , array($uned->id));
        $include = false;
        foreach($mails as $row) {
            if ($destiny == '1'){
                $include = true;
            }else{
                $progress = unedtrivial_get_user_progress($uned, $row->userid, $history, $totalqu);
                if ($destiny == '2' && $progress == 'notstarted'){
                    $include = true;
                }else if ($destiny == '3' && $progress == 'giveuprisk'){
                    $include = true;
                }else if ($destiny == '4' && $progress == 'progressing'){
                    $include = true;
                }else if ($destiny == '5' && $progress == 'complete'){
                    $include = true;
                }
            }
            if ($include){
                if ($toUser == ""){
                    $toUser = $row->mail;
                }else{
                    $toUser = $toUser.",".$row->mail;
                }
                $include = false;
            }
        }
    }else{
        $period = substr($special,0,1);
        $colective = substr($special,1,1);
        switch ($period) {
            case 1:
                $initime = $uned->timecreated; break;
            case 2:
                $initime = time() - 7*86400; break;
            case 3:
                $initime = time() - 15*86400; break;
            case 4:
                $initime = time() - 21*86400; break;
            case 5:
                $initime = time() - 30*86400; break;
        }
        $initime = max($initime,$uned->timecreated);
        $history2 = $DB->get_records_sql('SELECT h.id,COUNT(h.id) as total,h.userid,m.mail'
                        . '                 FROM {unedtrivial_history} h'
                        . '                 JOIN {unedtrivial_mails} m ON m.userid = h.userid '    
                        . '                WHERE h.idunedtrivial = ? AND'
                        . '                      (h.questiondate >= ? OR h.questiondate = 0)'
                        . '             GROUP BY h.userid,h.id,h.mail' , array($uned->id,$initime));
        $questionsinperiod = floor((time()-$initime)/86400) * $uned->questionsperday;
        $questionsinperiod = max($questionsinperiod,$uned->questionsperday); //To avoid DIV 0
        $include = false;
        foreach($history2 as $row){
            if ($row->total == 1 && $colective == 4){
                $include = true;
            }else{
                $perc = $row->total/$questionsinperiod*100;
                if ($perc >= 70 && $colective == 1 && $row->total > 1){
                    $include = true;
                }else if ($perc < 70 && $perc >= 50 && $colective == 2 && $row->total > 1){
                    $include = true;
                }else if ($perc < 50 && $perc > 0 && $colective == 3 && $row->total > 1){
                    $include = true;
                }
            }
            if ($include){
                if ($toUser == ""){
                    $toUser = $row->mail;
                }else{
                    $toUser = $toUser.",".$row->mail;
                }
                $include = false;
            }
        }
    }
    return $toUser;
}

/**
 * Send an email
 * 
 * @param $subject Subject of email
 * @param $text Text of email
 * @param $header Header of email (valid only for PHP mail function)
 * @param $addresses Email recipient addresses
 * @param $checkerrors Boolean to check if there is any error in send
 */
function mail_sender($subject,$text,$header,$addresses,$checkerrors){
    GLOBAL $CFG;
    $config = get_config('mod_unedtrivial');
    if ($config->mailsendtype == 1){
        //Use Moodle SMTP configuration
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $mail->IsSMTP();
        $mail->IsHTML(true);
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = $CFG->smtpsecure;
        $mail->Host = $CFG->smtphosts;
        $mail->Username = $CFG->smtpuser;
        $mail->Password = $CFG->smtppass;
        $mail->CharSet = 'UTF-8';
        $mail->From = $CFG->noreplyaddress;
        $mail->FromName = 'UNEDTrivial';
        $mail->Subject = $subject;
        $mail->Body = $text;
        $tok = strtok($addresses, ",\n");
        while($tok !== false){
            $mail->AddBCC($tok);
            $tok = strtok(",\n");
        }
        $mail->Send();
        if ($checkerrors && $mail->isError()){
            return $mail->ErrorInfo;
        }
    }else if ($config->mailsendtype == 2){
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $mail->IsSMTP();
        $mail->IsHTML(true);
        $mail->SMTPAuth = true;
        $mail->Host = $config->smtp_host;
        $mail->Username = $config->smtp_username;
        $mail->Password = $config->smtp_password;
        $mail->Port = $config->smtp_port;
        $mail->From = $config->smtp_from;
        $mail->FromName = $config->smtp_fromname;
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $text;
        $tok = strtok($addresses, ",\n");
        while($tok !== false){
            $mail->AddBCC($tok);
            $tok = strtok(",\n");
        }
        $mail->Send();
        if ($checkerrors && $mail->isError()){
            return $mail->ErrorInfo;
        }
    }else{
        if ($checkerrors && !mail("",$subject,$text,$header)){
            return get_string('errormailtask', 'unedtrivial');
        }
    }
}

/**
 * Returns the calculated start date for a UNEDTrivial instance
 * If there's no answered registered, start date will be creation date,
 * else, it will be the first answer date.
 * 
 * @param $history UNEDTrivial answers history table without initial regs (questionid == -1)
 */
function unedtrivial_get_activity_origin($history,$creationdate){
    if (count($history) > 0){
        foreach ($history as $reg) {
            $origin = $reg->questiondate;
            break;
        }
    }else{
        $origin = $creationdate;
    }
    return $origin;
}

/**
 * Delete all accents and returns plain ascii letter
 * 
 * @param $str String to convert
 */
function unedtrivial_toASCII($str){
    return strtr(utf8_decode($str), 
        utf8_decode(
        'ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ'),
        'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy');
}
