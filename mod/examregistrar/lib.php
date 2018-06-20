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
 * Library of interface functions and constants for module examregistrar
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle are placed here.
 * It delegates examregistrar specific functions, needed to implement all the module
 * logic, to locallib.php.
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/// CONSTANTS ///////////////////////////////////////////////////////////

define('EXAMREGISTRAR_MODE_VIEW', 1);
define('EXAMREGISTRAR_MODE_BOOK', 3);
define('EXAMREGISTRAR_MODE_PRINT', 5);
define('EXAMREGISTRAR_MODE_REVIEW', 7);
define('EXAMREGISTRAR_MODE_REGISTRAR', 9);

// examfile statusses
define('EXAM_STATUS_CREATED', 0);
define('EXAM_STATUS_SENT', 3);
define('EXAM_STATUS_WAITING', 5);
define('EXAM_STATUS_REJECTED', 7);
define('EXAM_STATUS_APPROVED', 9);
define('EXAM_STATUS_VALIDATED', 10);

// responses statusses
define('EXAM_RESPONSES_UNSENT', 0);
define('EXAM_RESPONSES_SENT', 3);
define('EXAM_RESPONSES_ADDING', 4);
define('EXAM_RESPONSES_WAITING', 5);
define('EXAM_RESPONSES_REJECTED', 7);
define('EXAM_RESPONSES_COMPLETED', 9);
define('EXAM_RESPONSES_VALIDATED', 10);

define('EXAMREGISTRAR_PRINTMODE_DOUBLE', 0);
define('EXAMREGISTRAR_PRINTMODE_SINGLE', 1);

/**
 * Returns and array menu (id,name) for allowed exam status values
 *
 * @param array $replaces  an associative array of key (replace codes) / values (actual data)
 * @param string/array $subject where substitutions are performed, may be a string or an array of strings
 * @return string/array depends on subject type
 */
function examregistrar_examstatus_getmenu() {

    $states = array(EXAM_STATUS_CREATED => get_string('status_created','examregistrar'),
                    EXAM_STATUS_SENT => get_string('status_sent','examregistrar'),
                    EXAM_STATUS_WAITING => get_string('status_waiting','examregistrar'),
                    EXAM_STATUS_REJECTED => get_string('status_rejected','examregistrar'),
                    EXAM_STATUS_APPROVED => get_string('status_approved','examregistrar'),
                    EXAM_STATUS_VALIDATED => get_string('status_validated','examregistrar'),
                    );
    return $states;
}


$EXAMREGISTRAR_ELEMENTTYPES = array('annualityitem', 'perioditem', 'periodtypeitem', 'examsessionitem', 'scopeitem', 'termitem', 'locationitem', 'locationtypeitem', 'roleitem');



////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function examregistrar_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:         return true;
        case FEATURE_SHOW_DESCRIPTION:  return true;
        case FEATURE_BACKUP_MOODLE2:    return true;
        case FEATURE_GRADE_HAS_GRADE:   return false;
        case FEATURE_GRADE_OUTCOMES:    return false;
        default:                        return null;
    }
}

/**
 * Saves a new instance of the examregistrar into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $examregistrar An object from the form in mod_form.php
 * @param mod_examregistrar_mod_form $mform
 * @return int The id of the newly inserted examregistrar record
 */
function examregistrar_add_instance(stdClass $examregistrar, mod_examregistrar_mod_form $mform = null) {
    global $DB;

    $examregistrar->timecreated = time();
    $examregistrar->timemodified = $examregistrar->timecreated;


    # You may have to add extra stuff in here #

    $examregid = $DB->insert_record('examregistrar', $examregistrar);
    return $examregid;
}

/**
 * Updates an instance of the examregistrar in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $examregistrar An object from the form in mod_form.php
 * @param mod_examregistrar_mod_form $mform
 * @return boolean Success/Fail
 */
function examregistrar_update_instance(stdClass $examregistrar, mod_examregistrar_mod_form $mform = null) {
    global $DB;

    $examregistrar->timemodified = time();
    $examregistrar->id = $examregistrar->instance;

    # You may have to add extra stuff in here #

    return $DB->update_record('examregistrar', $examregistrar);
}

/**
 * Removes an instance of the examregistrar from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function examregistrar_delete_instance($id) {
    global $DB;

    if (! $examregistrar = $DB->get_record('examregistrar', array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    // exmasfiles
    //exams
    // bookings
    // seatings

    //sessionrooms
    // sessionseats
    // staffers

    $DB->delete_records('examregistrar_locations', array('examregid' => $examregistrar->id));

    $DB->delete_records('examregistrar_examsessions', array('examregid' => $examregistrar->id));

    $DB->delete_records('examregistrar_periods', array('examregid' => $examregistrar->id));

    $DB->delete_records('examregistrar_exams', array('examregid' => $examregistrar->id));

    $DB->delete_records('examregistrar_elements', array('examregid' => $examregistrar->id));

    # Delete the module instance #

    $DB->delete_records('examregistrar', array('id' => $examregistrar->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function examregistrar_user_outline($course, $user, $mod, $examregistrar) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $examregistrar the module instance record
 * @return void, is supposed to echp directly
 */
function examregistrar_user_complete($course, $user, $mod, $examregistrar) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in examregistrar activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function examregistrar_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link examregistrar_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function examregistrar_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see examregistrar_get_recent_mod_activity()}

 * @return void
 */
function examregistrar_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 * @return array
 */
function examregistrar_get_view_actions() {
    return array('view exams', 'view exams selection', 'download exam');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 * @return array
 */
function examregistrar_get_post_actions() {
    return array('send exam', 'upload', 'review', 'resolve', 'select', 'add element', 'delete element', 'update element');
}




/**
 * Returns instanceid of primary registrar
 *
 * @param object $examregistrar object
 * @return int
 */
function examregistrar_get_primaryid($examregistrar) {
    global $DB;

    $exregid = false;
    if($examregistrar->primaryreg) {
        if($exreg = $DB->get_record('examregistrar', array('primaryidnumber'=>$examregistrar->primaryreg))) {
            $exregid = $exreg->id;
        } else {
            $link = new moodle_url('/course/view.php', array('id'=>$examregistrar->course));
            print_error('errornoprimary', 'examregistrar', $link);
        }
    } elseif($examregistrar->primaryidnumber) {
        $exregid = $examregistrar->id;
    }
    return $exregid;
}

/**
 * Returns instanceid of primary registrar
 *
 * @param object $examregistrar object
 * @return int
 */
function examregistrar_get_primaryidnumber($examregistrar) {
    global $DB;

    $exregid = false;
    if($examregistrar->primaryreg) {
        if($exreg = $DB->get_record('examregistrar', array('primaryidnumber'=>$examregistrar->primaryreg))) {
            $exregid = $examregistrar->primaryreg;
        } else {
            $link = new moodle_url('/course/view.php', array('id'=>$examregistrar->course));
            print_error('errornoprimary', 'examregistrar', $link);
        }
    } elseif($examregistrar->primaryidnumber) {
        $exregid = $examregistrar->primaryidnumber;
    }
    return $exregid;
}


/**
 * Returns annuality element to use, if defined
 *
 * @param object $examregistrar object
 * @return int
 */
function examregistrar_get_annuality($examregistrar) {
    global $DB;

    $exregid = examregistrar_get_primaryid($examregistrar);
    $annuality = '';
    if($examregistrar->annuality) {
        $annuality = $DB->get_field('examregistrar_elements', 'id', array('examregid'=>$exregid, 'idnumber'=>$examregistrar->annuality));
    }
    return $annuality;
}


/**
 * Returns first currently active exam period, based on today and period start/end dates
 *
 * @param object $examregistrar object
 * @param int $now timestamp
 * @return int
 */
function examregistrar_get_period($examregistrar, $now = 0) {
    global $DB;

    if(!$now) {
        $now = time();
    }

    $period = '';
    $exregid = examregistrar_get_primaryid($examregistrar);
    $select  = " examregid = :examregid AND timestart <= :now1 AND timeend >= :now2 ";
    if($periods = $DB->get_records_select('examregistrar_periods', $select, array('examregid'=>$examregprimaryid, 'now1'=>$now, 'now2'=>$now))) {
        $period = reset($periods);
        $period = $period->id;
    }
    return $period;
}


/**
 * Returns currently active exam periods, based on today and period start/end dates
 *
 * @param object $examregistrar object
 * @param int $now timestamp
 * @return int
 */
function examregistrar_current_periods($examregistrar, $now = 0) {
    global $DB;

    if(!$now) {
        $now = time();
    }

    $annuality = examregistrar_get_annuality($examregistrar);

    $exregid = examregistrar_get_primaryid($examregistrar);
    $select = " examregid = :examregid AND timestart <= :now1 AND timeend >= :now2 AND visible = 1 ";
    $params = array('examregid'=>$exregid, 'now1'=>$now, 'now2'=>$now);
    if($annuality) {
        $select .= " AND annuality = :annuality ";
        $params['annuality'] = $annuality;
    }

    $periods = $DB->get_records_select('examregistrar_periods', $select, $params);

    if(!$periods) {
        $sql = "SELECT p.*, ABS(timestart - $now) AS timediff
                FROM {examregistrar_periods} p
                WHERE examregid = :examregid  AND visible = 1 ";
        $params = array('examregid'=>$exregid);
        if($annuality) {
            $sql .= " AND annuality = :annuality ";
            $params['annuality'] = $annuality;
        }
        $sql .= " ORDER BY timediff ASC ";
        $periods = $DB->get_records_sql($sql, $params);
    }

    return $periods;
}


/**
 * Returns next session scheduled
 *
 * @param object $examregistrar object
 * @param int $now timestamp
 * @param bool $object if returning object or id;
 * @return int/object
 */
function examregistrar_next_sessionid($examregistrar, $now = 0, $object = false, $period = false) {
    global $DB;

    $return = 0;
    if(!$now) {
        $now = time();
    }
    $now = usergetmidnight($now) - 60 ; // keep all working day in session day
    $sessionid = 0;
    $exregid = examregistrar_get_primaryid($examregistrar);

    $select = " examregid = :examregid AND examdate >= :now AND visible = 1 ";
    $params = array('examregid'=>$exregid, 'now'=>$now);
    if($period) {
        $select .= " AND period = :period ";
        $params['period'] = $period;
    }
    if(!$sessions = $DB->get_records_select('examregistrar_examsessions', $select, $params, 'examdate ASC')) {
        $params = array('examregid'=>$exregid);
        if($period) {
            $params['period'] = $period;
        }
        $sessions = $DB->get_records('examregistrar_examsessions', $params, 'examdate DESC');
    }
    if($sessions) {
        $session = reset($sessions);
        if($object) {
            $return = $session;
        } else {
            $return = $session->id;
        }
    }

    return $return;
}

/**
 * Checks if period is an EXTRA exam period
 *
 * @param object $examregistrar object
 * @param object $period object
 * @return bool
 */
function examregistrar_is_extra_period($examregistrar, $period) {
    global $DB;

    $type = $DB->get_record('examregistrar_elements', array('id'=>$period->periodtype), '*', MUST_EXIST);

    $inname = strpos('extra', core_text::strtolower($type->name));
    $inidnumber = strpos('ext', core_text::strtolower($type->idnumber));
    $hasord = strpos('ord', core_text::strtolower($type->idnumber));

    $extra = false;
    if(($inname !== false || $inidnumber !== false) && $hasord === false) {
        $extra = true;
    }

    return $extra;
}





/**
 * Synchronizes the status values of examfile entries that are associated with tracker issue
 * with the status values for those Trcker issues
 *
 * Status transfer in unidirectional Tracker -> Examfile, and only if examfile status is not set manually
 *
 * @param int $examstatus  examfile status field value
 * @param int $tracker1 Tracker issue status value
 * @param int $tracker2 Tracker issue status value
 * @param array $courses optional collection of course IDs to test in
 * @return bool true if the scale is used by the given examregistrar instance
 */
function examregistrar_examstatus_synchronize($examstatus, $tracker1, $tracker2, $courses=array()) {
    global $DB;

    $success = true;

    $coursejoin = '';
    $coursewhere = '';
    $courseparams = array();
    if($courses) {
        $coursejoin = ' JOIN {examregistrar_exams} e ON ef.examid = e.id ';
        list($insql, $courseparams) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED, 'course_');
        $coursewhere = " AND e.courseid $insql ";
    }

    $sql = "SELECT ef.id, i.status
            FROM {examregistrar_examfiles} ef
            JOIN {tracker_issue} i ON ef.reviewid = i.id
            $coursejoin
            WHERE ef.status > 0 AND ef.status < :examstatus AND (i.status = :status1 OR i.status = :status2)
                 $coursewhere ";
    $params = array('examstatus'=>EXAM_STATUS_REJECTED);

    if($pass = $DB->get_records_sql_menu($sql, $params + array('status1'=>$tracker1, 'status2'=>$tracker2) + $courseparams )) {
        $chunks = array_chunk(array_keys($pass), 250);
        foreach($chunks as $chunk) {
            list($insql, $params) = $DB->get_in_or_equal($chunk);
            if(!$DB->set_field_select('examregistrar_examfiles', 'status', $examstatus, " id $insql ", $params)) {
                $success = false;
            }
        }
    }

    return $success;
}


/**
 * Locates the Tracker issue associated to an examregistrar instance
 *
 * @param object $examregistrar the examregistrar object
 * @param object $course the course object containing teh examregistrar instance
 * @return object tracker record
 **/
function examregistrar_get_review_tracker($examregistrar, $course) {
    global $DB;

    if(!$moduleid = $DB->get_field('modules', 'id', array('name'=>'tracker'))) {
        return false;
    }

    $params = array('course'=>$course->id, 'module'=>$moduleid, 'idnumber'=>$examregistrar->reviewmod);
    if(!$cms = $DB->get_records('course_modules', $params)) {
        $sql = "SELECT cm.*, c.category
                FROM {course_modules} cm
                JOIN {course} c  ON c.id = cm.course
                WHERE cm.module = :module AND cm.idnumber = :idnumber AND c.category = :category ";
        $params['category'] = $course->category;
        $cms = $DB->get_records_sql($sql, $params);
    }
    $cm = 0;
    if($cms) {
        $cm = reset($cms);
    }

    if(!$cm) {
        mtrace("... missing review Tracker instance for examregistrar {$examregistrar->name} ({$examregistrar->id})");
        return false;
    }
    /// OK, now we have the cm of a tracker instance
    return $DB->get_record('tracker', array('id' => $cm->instance));
}


/**
 * Add tracker review issues to orphan examfiles
 * Search if any examfiles of exams presented in an examregistrar programme
 * need an issue and creates it
 *
 * @param object $examregistrar the examregistrar object
 * @param object $course the course object containing teh examregistrar instance
 * @return object tracker record
 **/
function examregistrar_tracker_add_issues($examregistrar, $course = 0) {
    global $DB;

    $sql = "SELECT ef.*
            FROM {examregistrar_examfiles} ef
            JOIN {examregistrar_exams} e ON e.id = ef.examid
            WHERE e.programme = :programme AND ef.attempt > 0 AND ef.idnumber <> ''
                    AND ef.reviewid = 0 AND ef.timeapproved = 0 AND ef.timerejected = 0 ";

    if($examfiles = $DB->get_records_sql($sql, array('programme'=>$examregistrar->programme))) {
        if(!$course) {
            $course = $DB->get_record('course', array('id'=>$examregistrar->course));
        }
        if($course) {
            if($tracker = examregistrar_get_review_tracker($examregistrar, $course)) {
                foreach($examfiles as $examfile) {
                    $issueid = examregistrar_review_addissue($examregistrar, $course, $examfile, $tracker);
                }
            }
        }
    }
}


/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function examregistrar_cron () {
    global $CFG, $DB, $OUTPUT, $USER;

    $config = get_config('examregistrar');

    /// routines executed on cron basis

    include_once($CFG->dirroot.'/mod/tracker/locallib.php');
    include_once($CFG->dirroot.'/mod/examregistrar/locallib.php');
    $success = examregistrar_examstatus_synchronize(EXAM_STATUS_APPROVED, TESTING, RESOLVED);
    $success = examregistrar_examstatus_synchronize(EXAM_STATUS_REJECTED, ABANDONNED, TRANSFERED);

    /// checking for examfiles without review instance
    $select = " workmode = :workmode AND (reviewmod <> '' AND reviewmod IS NOT NULL)
                    AND (programme <> 0 AND programme <> '' AND programme IS NOT NULL) ";
    if($registrars = $DB->get_records_select('examregistrar', $select, array('workmode'=>EXAMREGISTRAR_MODE_REVIEW))) {
        foreach($registrars as $key => $registrar) {
            mtrace("... adding missing review issues for examregistrar {$registrar->name} ({$registrar->id})");
            examregistrar_tracker_add_issues($registrar);

        }
    }



    /// checking for execution ONLY ONCE daily

    require_once($CFG->libdir .'/statslib.php');
    $timetocheck  = time();
    $today = stats_get_base_daily();

    /// these routines will run only once a day
    $strtoday = strftime('%Y-%m-%d', $timetocheck);

    /// checks for once a day except if in debugging mode
    $timetocheck  = $today + $config->runtimestarthour*60*60 + $config->runtimestartminute*60;
    // Note: This will work fine for sites running cron each 4 hours or less (hopefully, 99.99% of sites). MDL-16709
    // check to make sure we're due to run, at least 20 hours after last run
    if (isset($config->lastexecution) && ((time() - 20*60*60) < $config->lastexecution)) {
        mtrace("...preventing stats to run, last execution was less than 20 hours ago.");
        return false;
    // also check that we are a max of 4 hours after scheduled time, stats won't run after that
    } else if (time() > $timetocheck + 4*60*60) {
        mtrace("...preventing stats to run, more than 4 hours since scheduled time.");
        return false;
    } else {
        if($examregistrars = $DB->get_records('examregistrar', array('primaryreg'=>''))) {
            foreach($examregistrars as $examregistrar) {
                // allocate students in rooms for session
                $session = examregistrar_next_sessionid($examregistrar, $today, true);
                if($session && ($session->examdate > $today) && ($session->examdate < $today + 15*DAYSECS)) {
                    $sql = "SELECT b.id, b.bookedsite
                                FROM {examregistrar_bookings} b
                                JOIN {examregistrar_exams} e ON e.id = b.examid
                                WHERE e.examsession = :session
                                GROUP BY b.bookedsite ";
                    if($bookedsites = $DB->get_records_sql_menu($sql, array('examsession'=>$session->id))) {
                        foreach($bookedsites as $bookedsite) {
                            if(examregistrar_is_venue_single_room($bookedsite)) {
                                if(!$max = $DB->get_records('examregistrar_session_seats', array('examsession'=>$session->id, 'bookedsite'=>$bookedsite),
                                                                ' timemodified DESC ', '*', 0, 1)) {
                                    examregistrar_session_seats_makeallocation($session->id, $bookedsite);
                                } else {
                                    $lasttime = reset($max)->timecreated;
                                    examregistrar_session_seats_newbookings($session->id, $bookedsite, $lasttime+1);
                                }
                            }
                        }
                    }
                }
            }
            set_config('lastexecution', $today, 'examregistrar');  /// Grab this execution as last one
        }
    }

    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function examregistrar_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of examregistrar?
 *
 * This function returns if a scale is being used by one examregistrar
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $examregistrarid ID of an instance of this module
 * @return bool true if the scale is used by the given examregistrar instance
 */
function examregistrar_scale_used($examregistrarid, $scaleid) {
    global $DB;

     return false;
}

/**
 * Checks if scale is being used by any instance of examregistrar.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any examregistrar instance
 */
function examregistrar_scale_used_anywhere($scaleid) {
    global $DB;

    return false;

}

/**
 * Creates or updates grade item for the give examregistrar instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $examregistrar instance object with extra cmidnumber and modname property
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return void
 */
function examregistrar_grade_item_update(stdClass $examregistrar, $grades=NULL) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $item = array();
    $item['itemname'] = clean_param($examregistrar->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax']  = 0;
    $item['grademin']  = 0;

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = NULL;
    }

    grade_update('mod/examregistrar', $examregistrar->course, 'mod', 'examregistrar', $examregistrar->id, 0, $grades, $item);
}

/**
 * Update examregistrar grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $examregistrar instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function examregistrar_update_grades(stdClass $examregistrar, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $grades = array(); // populate array of grade objects indexed by userid

    grade_update('mod/examregistrar', $examregistrar->course, 'mod', 'examregistrar', $examregistrar->id, 0, $grades);
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////







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
function examregistrar_get_file_areas($course, $cm, $context) {
    return array('exam'=>get_string('areaexamfile', 'examregistrar'),
                 'responses'=>get_string('areaexamresponses', 'examregistrar'),
                 'examresponses'=>get_string('areaexamresponsestemp', 'examregistrar'),
                 'sessionresponses'=>get_string('areasesionresponses', 'examregistrar'),);
}

/**
 * File browsing support for examregistrar file areas
 *
 * @package mod_examregistrar
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
function examregistrar_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_COURSE) {
        return null;
    }

    // filearea must contain a real area
    if (!isset($areas[$filearea])) {
        return null;
    }

    // Check access
    if (!has_capability('mod/examregistrar:download', $context)) {
        return null;
    }

    if (is_null($itemid)) {
        return null;
    }

    if (!$content = $DB->get_record('examregistrar_examfiles', array('id'=>$itemid))) {
        return null;
    }

    $fs = get_file_storage();
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;
    if (!($storedfile = $fs->get_file($context->id, 'mod_examregistrar', $filearea, $itemid, $filepath, $filename))) {
        return null;
    }

    $urlbase = $CFG->wwwroot.'/pluginfile.php';
    return new file_info_stored($browser, $context, $storedfile, $urlbase, $itemid, true, true, false, false);
}


/**
 * Serves the files from the examregistrar file areas
 *
 * @package mod_examregistrar
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the examregistrar's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */

function examregistrar_pluginfile($course, $cm, context $context, $filearea, $args, $forcedownload) {
    global $DB, $CFG;

    $fileareas = array('exam', 'responses', 'answers', 'sheet', 'sessionrooms', 'sessionresponses', 'session', 'examresponses', 'roomresponses');
    if (!in_array($filearea, $fileareas)) {
        return false;
    }

    if ($filearea == 'exam' && $context->contextlevel != CONTEXT_COURSE) {
        return false;
    }

    $fs = get_file_storage();

    if($filearea == 'sheet') {
        $cmid = array_shift($args);
        $context = context_module::instance($cmid);
        $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false); // TODO: this is not very efficient!!
        if (count($files) < 1) {
            resource_print_filenotfound($resource, $cm, $course);
            die;
        } else {
            $file = reset($files);
            $fullpath = "/{$context->id}/mod_resource/content/0".$file->get_filepath().$file->get_filename();
            unset($files);
            unset($file);
        }
    } else {
        $rev = reset($args);
        if($rev == 'rev') {
            // we need to chek in rev module
            $rev = array_shift($args);
            $revid = (int)array_shift($args);
            require_login($revid, false, $cm, false, true);
            $checkcontext = context_course::instance($revid);
        } else {
            require_login($course, false, $cm, false, true);
            $checkcontext = $context;
        }

        $candownload = has_capability('mod/examregistrar:download', $checkcontext);

        if(!$candownload) {
            // check if board member (old style) REMOVE when all in new style
            $idnumber = 'jeval-'.strstr($course->idnumber, '_', true).'_111';
            if($boardid = $DB->get_field('course', 'id', array('category'=>$course->category, 'idnumber'=>$idnumber))) {
                require_login($boardid, false, $cm, false, true);
                $boardcontext = context_course::instance($boardid);
                $candownload = has_capability('mod/examregistrar:download',$boardcontext);
                if($boardcontext && !$candownload) {
                    return false;
                }
            } else {
                return false;
            }
        }

        if(!$candownload) {
            return false;
        }

        $itemid = (int)array_shift($args);
        if (($filearea) == 'exam' && !$examfile = $DB->get_record('examregistrar_examfiles', array('id'=>$itemid))) {
            return false;
        }


        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_examregistrar/$filearea/$itemid/$relativepath";
    }



    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // Download MUST be forced - security!
    send_stored_file($file, 0, 0, true);
}


    function examregistrar_file_decode_type($type) {
    
        // a hack to avoid changing other references
        $path = '';
        if(is_array($type)) {
            $type = $type[0];
            $path = $type[1];
        }
    
        switch($type) {
            case 'exam'     :   $area = 'exam';
                                $path = '/';
                                break;
            case 'answers'  :   $area = 'exam';
                                $path = '/answers/';
                                break;
            case 'key'      :   $area = 'exam';
                                $path = '/key/';
                                break;
            case 'responses':   $area = 'responses';
                                $path = '/';
                                break;
            case 'session'  :   $area = 'session';
                                $path = '/';
                                break;
            case 'sessionrooms':$area = 'sessionrooms';
                                $path = '/';
                                break;
            case 'sessionresponses':
                                $area = 'sessionresponses';
                                $path = '/';
                                break;
            case 'examresponses':
                                $area = 'examresponses';
                                $path = '/';
                                break;
            case 'roomresponses':
                                $area = 'roomresponses';
                                $path = '/';
                                break;

        }
        return array($area, $path);
    }

    function examregistrar_file_get_filename($contextid, $itemid, $type, $multiple=false) {

        list($area, $path) = examregistrar_file_decode_type($type);

        $filename = '';
        $fs = get_file_storage();
        if($files = $fs->get_directory_files($contextid, 'mod_examregistrar', $area, $itemid, $path, false, false, "filepath, filename")) {
            if(!$multiple) {
                $file = reset($files);
                $filename = $file->get_filename();
            } else {
                $filename = array();
                foreach($files as $file) {
                    $filename[] = $file->get_filename();
                }
            }
        }

        return $filename;
    }

    function examregistrar_file_get_file($contextid, $itemid, $type, $search=false) {

        list($area, $path) = examregistrar_file_decode_type($type);

        $file = '';
        $fs = get_file_storage();

        // if search string, use as filename
        if(is_string($search)) {
            return $fs->get_file($contextid, 'mod_examregistrar', $area, $itemid, $path, $search);
        }

        if($files = $fs->get_directory_files($contextid, 'mod_examregistrar', $area, $itemid, $path, false, false, "filepath, filename")) {
            if(!$search) {
                return reset($files);
            } else {
                return $files;
            }
        }

        return $file;
    }


    function examregistrar_file_encode_url($contextid, $itemid, $type, $filename='', $revision = false, $forcedownload=false) {
        global $CFG;

        $url = '';

        list($area, $path) = examregistrar_file_decode_type($type);

        if(!$filename) {
            $fs = get_file_storage();
            if($files = $fs->get_directory_files($contextid, 'mod_examregistrar', $area, $itemid, $path, false, false, "filepath, filename")) {
                $file = reset($files);
                $filename = $file->get_filename();
            }
        }

        $revpath = '';
        if($revision = (int)$revision) {
            $revpath = 'rev/'.$revision.'/';
        }
        $url = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$contextid.'/mod_examregistrar/'.$area.'/'.$revpath.$itemid.$path.$filename, $forcedownload);
        return $url;
    }

    function examregistrar_file_set_nameextension($filename, $type, $ext='.pdf') {

        $filename = trim($filename);
        $ext = trim($ext);
        if(strpos($ext, '.') === false) {
            $ext = '.'.$ext;
        }

        $config = get_config('examregistrar');

        $qualifier = '';
        if($type == 'answers') {
            $qualifier = $config->extanswers;
        } elseif($type == 'key') {
            $qualifier = $config->extkey;
        } elseif($type == 'responses') {
            $qualifier = $config->extresponses;
        }
        if($qualifier) {
            $qualifier = trim($qualifier);
        }

        return clean_filename($filename.$qualifier.$ext);
    }


    function get_roomzip_filename($session, $bookedsite, $room) {
        $sessionstr = get_string('examsessionitem', 'examregistrar');
        $venuestr = get_string('venue', 'examregistrar');
        $roomstr = get_string('room', 'examregistrar');
        return clean_filename($sessionstr.'_'.$session.'_'.$venuestr.'_'.$bookedsite.'_'.$roomstr.'-'.$room->idnumber.'.zip');
    }

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding examregistrar nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the examregistrar module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function examregistrar_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the examregistrar settings
 *
 * This function is called when the context for the page is a examregistrar module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $examregistrarnode {@link navigation_node}
 */
function examregistrar_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $examregistrarnode=null) {
    global $PAGE, $DB;

    if (empty($PAGE->cm->context)) {
        $PAGE->cm->context = context_module::instance($PAGE->cm->instance);
    }
    $cm = $PAGE->cm;
    if (!$cm) {
        return;
    }
    $context = $cm->context;
    $course = $PAGE->course;
    if (!$course) {
        return;
    }

    $node1 = $settingsnav->prepend('texto', null, navigation_node::TYPE_CONTAINER);


    if (has_capability('mod/examregistrar:editelements', $cm->context)) {
        $link = new moodle_url('/mod/examregistrar/editelements.php', array('id' => $cm->id));
        $linkname = get_string('editelements', 'examregistrar');
        $node = $examregistrarnode->add($linkname, $link, navigation_node::TYPE_SETTING);

        $node = $node1->add($linkname, $link, navigation_node::TYPE_SETTING);
    }

    if (has_capability('mod/examregistrar:manageperiods', $cm->context)) {
        $link = new moodle_url('/mod/examregistrar/locations/index.php', array('id' => $course->id));
        $periods = $examregistrarnode->add(get_string('manage', 'examregistrar'), $link, navigation_node::TYPE_CONTAINER);
        $link = new moodle_url('/mod/examregistrar/locations/index.php', array('id' => $course->id));
        $linkname = get_string('editelements', 'examregistrar');
        $node = $periods->add($linkname, $link, navigation_node::TYPE_SETTING);
    }


}

////////////////////////////////////////////////////////////////////////////////
// Module API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns array of examregistrar formats chooseable on the examregistrar editing form
 *
 * @return array
 */
function examregistrar_get_workmodes() {
    return array (EXAMREGISTRAR_MODE_VIEW     => get_string('modeview','examregistrar'),
                  EXAMREGISTRAR_MODE_BOOK     => get_string('modebook','examregistrar'),
                  EXAMREGISTRAR_MODE_PRINT    => get_string('modeprint','examregistrar'),
                  EXAMREGISTRAR_MODE_REVIEW   => get_string('modereview','examregistrar'),
                  EXAMREGISTRAR_MODE_REGISTRAR   => get_string('moderegistrar','examregistrar'),
                 );
}

/**
 * Returns array of examregistrar formats chooseable on the examregistrar editing form
 *
 * @return array
 */
function examregistrar_get_primary_registrars() {
    global $DB;

    $regmenu = array();

    $sql = "SELECT er.primaryidnumber, CONCAT(c.shortname, '-', er.name)
            FROM {examregistrar} er
            JOIN {course} c ON c.id = er.course
            WHERE ".$DB->sql_isempty('examregistrar', 'primaryreg', true, false).
            " AND ".$DB->sql_isnotempty('examregistrar', 'primaryidnumber', true, false);
    $sort = ' ORDER BY c.shortname ASC, er.name ASC';
    $regmenu = $DB->get_records_sql_menu($sql.$sort, array());

    return $regmenu;
}

