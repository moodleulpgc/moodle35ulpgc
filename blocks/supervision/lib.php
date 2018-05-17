<?php
/**
 * This file contains block_supervison main library functions
 *
 * @package   block_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once($CFG->dirroot.'/lib/statslib.php');

    define('PENDING_UNREPLIED_DIALOGUE', 'unreplied_dialogue');
    define('PENDING_UNGRADED_ASSIGNMENT', 'ungraded_assignment');
    define('PENDING_UNREPLIED_FORUM', 'unreplied_forum');
    define('PENDING_LOWSLOTS_SCHEDULER','lowslots_scheduler');

    function pending_get_staststypes() {
        $types = array(PENDING_UNREPLIED_DIALOGUE=>get_string('stat_'.PENDING_UNREPLIED_DIALOGUE, 'block_admin_ulpgc'),
                                PENDING_UNGRADED_ASSIGNMENT=>get_string('stat_'.PENDING_UNGRADED_ASSIGNMENT, 'block_admin_ulpgc'),
                                PENDING_UNREPLIED_FORUM=>get_string('stat_'.PENDING_UNREPLIED_FORUM, 'block_admin_ulpgc'),
                                PENDING_LOWSLOTS_SCHEDULER=>get_string('stat_'.PENDING_LOWSLOTS_SCHEDULER, 'block_admin_ulpgc')
                                );
        return $types;
    }

    function pending_get_stats_dialogue($timetocheck, $lasttime) {
        global $CFG;

        mtrace('... starting Dialogue pending stats');
        $module = 'dialogue';
        $stattype = PENDING_UNREPLIED_DIALOGUE;

        $threshold = $CFG->pendingduties_threshold_dialogue*DAYSECS;
        $timelimit = $timetocheck - $threshold;

        $holidays = get_holidays($lasttime, $timetocheck);
        $timelimit = $timelimit - holiday_time($timelimit, $timetocheck, $holidays);

        $roleswhere = '';
        if(!empty($CFG->pendingduties_checkedroles)) {
            $roleswhere = " AND rc.roleid IN ( {$CFG->pendingduties_checkedroles} ) ";
        }

        $rolesql = "SELECT rc.roleid, rc.capability
                            FROM {$CFG->prefix}role_capabilities rc
                            WHERE rc.capability = 'mod/dialogue:manage'
                            $roleswhere
                            GROUP BY rc.roleid";

        if($roles = get_records_sql($rolesql)) {
            $rolelist = implode(',', array_keys($roles));
        } else {
            return false;
        }
        $excludedtcategories = '';
        if($CFG->pendingduties_excludedcats) {
            $excludedtcategories = " INNER JOIN {$CFG->prefix}course c ON c.id = d.course AND c.category NOT IN ( {$CFG->pendingduties_excludedcats} ) ";
        }

        $instances= '';
        if($CFG->pendingduties_unreplied_dialogue == 2 ) {
            $instances= " AND cm.score > '0' ";
        }

        $contextlevel = CONTEXT_COURSE;

        // First we obtain all open dialogues, later test for delay.
        //Timemofied may be close, but question may be older (repeated asking)
        $sql = "SELECT  dc.id AS id,  d.id AS dialogueid, d.course as courseid,
                                dc.id as conversationid, dc.recipientid, dc.userid, dc.timemodified, dc.closed,
                                cm.id AS cmid, cm.score
                        FROM {$CFG->prefix}dialogue d
                                                $excludedtcategories
                        JOIN {$CFG->prefix}course_modules cm ON cm.instance = d.id AND cm.course = d.course
                        LEFT JOIN {$CFG->prefix}dialogue_conversations dc ON d.id = dc.dialogueid
                    WHERE   d.dialoguetype = '0' AND  dc.closed = 0 AND dc.userid = dc.lastid
                        $instances
                        AND dc.recipientid IN (SELECT ra.userid FROM
                                                    {$CFG->prefix}role_assignments ra
                                                    JOIN {$CFG->prefix}context ctx ON ra.contextid = ctx.id
                                                    WHERE ctx.contextlevel ='$contextlevel'  AND ctx.instanceid = d.course
                                                    AND ra.roleid IN ($rolelist) AND ra.hidden = 0
                                                 ) ";

        $currentdialogues = get_records_sql($sql);
        if($currentdialogues) {
            $negatives = array();
            foreach ($currentdialogues as $stat) {
                if($stat->timemodified < $timelimit) {
                    continue;
                } else {
                    // get entries from most recent and search for last teacher's one.
                    // thats our timemodified reference
                    if($entries = get_records('dialogue_entries', 'conversationid', $stat->id, 'timecreated DESC', 'id, userid, timecreated')){
                        do{
                            $last = array_shift($entries);
                        } while($entries AND ($entries[0]->userid == $stat->userid) );
                        $stat->timemodified = $last->timecreated;
                        if($stat->timemodified < $timelimit) {
                            continue;
                        } else {
                            $negatives[] = $stat->id;
                        }
                    }
                }
            }
            foreach($negatives as $key) {
                unset($currentdialogues[$key]);
            }
        } else {
            $currentdialogues = array();
        }

        $sql = "SELECT  auxid AS id, id AS statid, timecreated, modinstance, stat1, timefixed
                        FROM {$CFG->prefix}pending_duties
                    WHERE  modname = '$module' AND stattype='$stattype'";
        $storeddialogues = get_records_sql($sql);

        if(is_array($storeddialogues)) {
            $newfailures = array_diff_key($currentdialogues, $storeddialogues);
            $fixedfailures = array_diff_key($storeddialogues, $currentdialogues);
            $updatefailures = array_intersect_key($storeddialogues, $currentdialogues);
        } else {
            $newfailures = $currentdialogues;
            $fixedfailures = array();
            $updatefailures = array();
        }

        if($fixedfailures) {
            foreach($fixedfailures as $fixed) {
                $timemodified = get_field('dialogue_conversations', 'timemodified', 'id', $fixed->id);
                $fixed->timefixed = $timemodified;
                $fixed->stat1 += ($timetocheck - $timemodified) - holiday_time($timemodified, $timetocheck, $holidays); //($timemodified - $fixed->timecreated) - holiday_time($fixed->timecreated, $timemodified, $holidays);
                $fixed->auxid = $fixed->id;
                $fixed->id = $fixed->statid;
                update_record('pending_duties', $fixed);
            }
            mtrace("Fixing ".count($fixedfailures).'  pending open dialogues');
        }

        $updateddelay = ($timetocheck - $lasttime) - holiday_time($lasttime, $timetocheck, $holidays);
        if($updatefailures) {
            foreach($updatefailures as $fixed) {
                if($fixed->timefixed) {
                    continue;
                }
                $fixed->stat1 += $updateddelay;
                $fixed->id = $fixed->statid;
                update_record('pending_duties', $fixed);
            }
            mtrace("Updating ".count($updatefailures).'  pending open dialogues');
        }

        if($newfailures) {
            $newstat = new object();
            $newstat->timecreated = $timetocheck;
            $newstat->modname = $module;
            $newstat->stattype = $stattype;
            foreach($newfailures as $stat) {
                unset($newstat->id);
                $modcontext = context_module::instance($stat->cmid);
                if(!$stat->courseid || !has_capability('mod/dialogue:open', $modcontext, $stat->userid) || !has_capability('mod/dialogue:manage', $modcontext, $stat->recipientid) ) {
                    continue;
                }
                $newstat->courseid = $stat->courseid;
                $newstat->userid = $stat->recipientid;
                $newstat->roleid = 0;
                if ($newstat->userid && $newstat->courseid) {
                    $context = context_course::instance($newstat->courseid);
                    if($roles = get_user_roles($context, $newstat->userid, false, 'c.contextlevel DESC, r.sortorder ASC')) {
                        $role = array_shift($roles);
                        $newstat->roleid = $role->roleid;
                    }
                }
                $cm = get_coursemodule_from_instance('dialogue', $stat->dialogueid, $stat->courseid);
                $newstat->modinstance = $stat->dialogueid;
                $newstat->auxid = $stat->conversationid;
                $newstat->stat1 = ($timetocheck - $stat->timemodified) - holiday_time($stat->timemodified, $timetocheck, $holidays);
                $newstat->url = "/mod/dialogue/dialogues.php?id={$cm->id}&action=printdialogue&cid={$newstat->auxid}";
                if($newstat->stat1 >= $threshold) {
                    insert_record('pending_duties', $newstat, false); // don't worry about the return id, we don't need it.
                }
            }
            mtrace("Adding ".count($newfailures).'  pending open dialogues');
        }
    }


    function pending_get_stats_forum($timetocheck, $lasttime) {

    }

    function pending_get_stats_assignment($timetocheck, $lasttime) {
        global $CFG;

        $module = 'assignment';
        $stattype = PENDING_UNGRADED_ASSIGNMENT;

        $threshold = $CFG->pendingduties_threshold_assignment*DAYSECS;
        $timelimit = $timetocheck - $threshold;

        $holidays = get_holidays($lasttime, $timetocheck);
        $timelimit = $timelimit - holiday_time($timelimit, $timetocheck, $holidays, false);


        $excludedtcategories = '';
        if($CFG->pendingduties_excludedcats) {
            $excludedtcategories = " INNER JOIN {$CFG->prefix}course c ON a.course = c.id AND c.category NOT IN ( {$CFG->pendingduties_excludedcats} ) ";
        }

        $instances= '';
        if($CFG->pendingduties_ungraded_assignment == 2 ) {
            $instances= " AND cm.score > '0' ";
        }


        // First we obtain all submissions without a grade , later test for delay.
        $sql = "SELECT sub.id AS id,  a.id AS assid, a.course as courseid,
                                sub.id as subid, sub.userid, sub.teacher, sub.timemodified, sub.timemarked,
                                cm.id AS cmid, cm.score
                        FROM {$CFG->prefix}assignment_submissions sub
                        JOIN {$CFG->prefix}assignment a ON sub.assignment = a.id AND a.assignmenttype='uploadon'
                        JOIN {$CFG->prefix}course_modules cm ON cm.instance = a.id AND cm.course = a.course
                        $excludedtcategories
                    WHERE  sub.timemodified > sub.timemarked $instances
                        AND  sub.timemodified < $timelimit  AND sub.data2 NOT IN ('', 'open') " ;

        $currentassigns = get_records_sql($sql);
        if(!$currentassigns) {
            $currentassigns = array();
        }

        $sql = "SELECT  auxid AS id, id AS statid, timecreated, modinstance, stat1, timefixed
                        FROM {$CFG->prefix}pending_duties
                    WHERE  modname = '$module' AND stattype='$stattype' ";
        $storedassigns = get_records_sql($sql);

        if(is_array($storedassigns)) {
            $newfailures = array_diff_key($currentassigns, $storedassigns);
            $fixedfailures = array_diff_key($storedassigns, $currentassigns);
            $updatefailures = array_intersect_key($storedassigns, $currentassigns);
        } else {
            $newfailures = $currentassigns;
            $fixedfailures = array();
            $updatefailures = array();
        }

        if($fixedfailures) {
            foreach($fixedfailures as $fixed) {
                $timemodified = get_field('assignment_submissions', 'timemarked', 'id', $fixed->id);
                $fixed->timefixed = $timemodified;
                $fixed->userid = get_field('assignment_submissions', 'teacher', 'id', $fixed->id);
                $fixed->stat1 += ($timetocheck - $timemodified) - holiday_time($timemodified, $timetocheck, $holidays, false);  //($timemodified - $fixed->timecreated) - holiday_time($fixed->timecreated, $timemodified, $holidays, false);
                $fixed->auxid = $fixed->id;
                $fixed->id = $fixed->statid;
                update_record('pending_duties', $fixed);
            }
        }

        $updateddelay = ($timetocheck - $lasttime) - holiday_time($lasttime, $timetocheck, $holidays, false);
        if($updatefailures) {
            foreach($updatefailures as $fixed) {
                if($fixed->timefixed) {
                    continue;
                }
                $fixed->stat1 += $updateddelay;
                $fixed->id = $fixed->statid;
                update_record('pending_duties', $fixed);
            }
            mtrace("Updating ".count($updatefailures).'  pending open assignments');
        }

        if($newfailures) {
            $newstat = new object();
            $newstat->timecreated = $timetocheck;
            $newstat->modname = $module;
            $newstat->stattype = $stattype;
            $newstat->timemailed = 0;
            foreach($newfailures as $stat) {
                unset($newstat->id);
                $modcontext = context_module::instance($stat->cmid);
                if(!$stat->courseid || !has_capability('mod/assignment:submit', $modcontext, $stat->userid) || ($stat->teacher && !has_capability('mod/dialogue:manage', $modcontext, $stat->teacher)) ) {
                    continue;
                }

                $newstat->courseid = $stat->courseid;
                $newstat->userid = $stat->teacher;
                $cm = get_coursemodule_from_instance($module, $stat->assid, $stat->courseid);
                $newstat->modinstance = $stat->assid;
                $newstat->auxid = $stat->subid;
                $newstat->stat1 = ($timetocheck - $stat->timemodified) - holiday_time($stat->timemodified, $timetocheck, $holidays, false);
                $newstat->stat2 = $stat->userid;
                $newstat->url = "/mod/assignment/submissions.php?id={$cm->id}";
                if($newstat->stat1 >= $threshold) {
                    insert_record('pending_duties', $newstat, false); // don't worry about the return id, we don't need it.
                }
            }
            mtrace("Adding ".count($newfailures).'  pending ungraded assignments');
        }
    }

    function pending_get_stats_scheduler($timetocheck, $lasttime) {
        global $CFG;

        $module = 'scheduler';
        $stattype = PENDING_LOWSLOTS_SCHEDULER;


        if(($CFG->scheduler_minperiod <=0)) {
            return false;
        }

        $today = getdate($timetocheck);
        $offset = $CFG->scheduler_cronwday - $today['wday'];
        $startweek = mktime(0, 0, 0, $today['mon'], $today['mday']+$offset, $today['year']);
        $endweek = strtotime('+1 week', $startweek);

        $rolelist = '0';
        if($CFG->pendingduties_checkedroles) {
            $rolelist = $CFG->pendingduties_checkedroles;
        }
        $contextlevel = CONTEXT_COURSE;
        $sql = "SELECT ra.userid, ra.roleid
                        FROM {$CFG->prefix}role_assignments ra
                        JOIN {$CFG->prefix}context ctx ON ra.contextid = ctx.id
                        WHERE ctx.contextlevel ='$contextlevel'
                        AND ra.roleid IN ($rolelist) AND ra.hidden = 0
                        GROUP BY ra.userid ";
        $teachersRS = get_recordset_sql($sql);

        $instances= '';
        if($CFG->pendingduties_lowslots_scheduler == 2 ) {
            $instances= " AND cm.score > '0' ";
        }

        $excludedtcategories = '';
        if($CFG->pendingduties_excludedcats) {
            $excludedtcategories = " INNER JOIN {$CFG->prefix}course c ON s.course = c.id AND c.category NOT IN ( {$CFG->pendingduties_excludedcats} ) ";
        }


        $teachers = 0;
        $addedstats = 0;
        $updatedstats = 0;
        while ($teacher = rs_fetch_next_record($teachersRS)) {
            $teachers += 1;
            $required_time = get_scheduler_mintime($teacher);
            if(!$required_time) {
                continue;
            }

            $sql = "SELECT ss.teacherid, SUM(ss.duration) as totalduration,
                            cm.id AS cmid, cm.score
                            FROM {$CFG->prefix}scheduler_slots ss
                            JOIN {$CFG->prefix}scheduler s ON s.id = ss.schedulerid
                            JOIN {$CFG->prefix}course_modules cm ON cm.instance = s.id AND cm.course = s.course
                            $excludedtcategories
                            WHERE  (starttime >= '$startweek') AND ((starttime+duration) <= '$endweek ')
                            $instances
                            AND teacherid = '{$teacher->userid}'
                            GROUP BY teacherid";
            $scheduledslot = get_record_sql($sql);
            $scheduledtime = 0;
            if($scheduledslot) {
                $scheduledtime = $scheduledslot->totalduration;
            }

            $sql = "SELECT  userid AS id, id AS statid, timecreated, modinstance, stat1, timefixed, userid
                            FROM {$CFG->prefix}pending_duties
                        WHERE  modname = '$module' AND stattype='$stattype' AND  timefixed = 0 AND timecreated>='$startweek'
                        AND userid = '{$teacher->userid}'
                        GROUP BY userid";
            $stat = get_record_sql($sql);

            if($stat) {
                // Already tested this week. update stat
                $update = false;

                if($scheduledtime != $stat->stat1) {
                    $stat->stat1 = $scheduledtime;
                    $update = true;
                }
                if($scheduledtime >= $required_time ) {
                    $stat->stat1 = $scheduledtime;
                    $stat->timefixed = $timetocheck;
                    $update = true;
                }
                if($update) {
                    $stat->id = $stat->statid;
                    $stat->userid = $teacher->userid;
                    update_record('pending_duties', $stat);
                        $updatedstats +=1;
                }
            } else {
                // new stat, if any
                if($scheduledtime < $required_time) {
                    $newstat = new object();
                    $newstat->timecreated = $timetocheck;
                    $newstat->modname = $module;
                    $newstat->stattype = $stattype;
                    $newstat->timemailed = 0;
                    $newstat->userid = $teacher->userid;
                    $newstat->stat1 = $scheduledtime;
                    $newstat->stat2 = $required_time;

                    $contextlevel = CONTEXT_COURSE;
                    $now = time();

                    $sql = "SELECT c.id, c.fullname, c.shortname, c.category, c.visible, ra.roleid, ra.enrol
                                    FROM {$CFG->prefix}role_assignments ra
                                    JOIN {$CFG->prefix}context ctx ON ra.contextid = ctx.id
                                    JOIN {$CFG->prefix}course c ON ctx.instanceid = c.id
                                    WHERE ra.userid = '{$teacher->userid}' AND ctx.contextlevel = '$contextlevel'
                                        AND (ra.timeend =0 OR ra.timeend > $now) AND ra.timestart<='$now'
                                        AND (c.coddept IS NOT NULL) AND c.coddept <> ''
                                        AND c.category NOT IN ( {$CFG->pendingduties_excludedcats} )
                                        ORDER BY c.visible DESC, c.sortorder ASC ";
                    $courses = get_records_sql($sql);
                    $usercourses = array();
                    if ($courses) {
                        foreach($courses as $course) {
                            $usercourses[$course->id] = '<a href="course/view.php?id='.$course->id.'"  >'.$course->shortname."</a>";
                        }
                    }
                    $newstat->url = implode(', ', $usercourses);


                    if(insert_record('pending_duties', $newstat, false)) {// don't worry about the return id, we don't need it.
                        $addedstats += 1;
                    }
                }
            }
        }
        rs_close($teachersRS);
        mtrace("Reviewing pending lowslots schedules  for  $teachers teachers.  Adding $addedstats  pending lowslots stats. Updating $updatedstats  pending lowslots stats");
    }

    function pending_stats_mailing($timetocheck, $lasttime) {
        global $CFG, $USER;

        /// last step: mail warnings if needed
        /// TODO count of failures for each course, user;  JOIN user and course???
        if(empty($CFG->pendingduties_enablemail)) {
                    return true;
        }
        $maillimit = $timetocheck;
        if(isset($CFG->statspendantmaildelay)) {
            $maillimit = $timetocheck - $CFG->statspendantmaildelay;
        }

        if(empty($CFG->pendingduties_enablemail)) {
            mtrace('no pending stats mailing');
            return true;
        }

        $sql = "SELECT id, userid, courseid, modname, stat2
                        FROM {$CFG->prefix}pending_duties
                        WHERE (timefixed = 0)   AND (timemailed < $maillimit) ";

        if ($rs = get_recordset_sql($sql)) {
            $subject = get_string('warningmailsubject', 'report_pending');
            $from = get_string('warningautomatic',  'report_pending');
            $supportuser = '';
            if(isset($CFG->pendingduties_email) && $CFG->pendingduties_email) {
                $supportuser = generate_email_supportuser();
                $supportuser->email = $CFG->pendingduties_email;
                $supportuser->mailformat = 1;
                $supportuser->id = 1;
            }

            while ($stat = rs_fetch_next_record($rs)) {
                /// TODO complete email with HTML version and links to courses, count on failures

                $course = get_record('course', 'id', $stat->courseid);
                $groupmode = $course->groupmode;
                $groupigid = $course->defaultgroupingid;
                if($stat->modname == 'assignment' AND $groupmode == SEPARATEGROUPS) {
                    if($studentgroups = groups_get_all_groups($stat->courseid, $stat->stat2, $groupigid, 'g.id, g.id')) {
                        $studentgroups = array_keys($studentgroups);
                    } else {
                        $studentgroups = array();
                    }
                }

                $context = context_coursecat::instance($course->category);
                $coords = get_role_users($CFG->pendingduties_checkerrole, $context, false, 'id, firstname, lastname, email, maildisplay');

                $users = array();
                if($user = get_record('user', 'id', $stat->userid)) {
                    $users[$user->id] = $user;
                } else {  //
                    $context = context_course::instance($stat->courseid);
                    $checkedroles= array();
                    if(!empty($CFG->pendingduties_checkedroles)) {
                        $checkedroles = explode(',', $CFG->pendingduties_checkedroles);
                    }
                    $users = get_role_users($checkedroles, $context, false, 'u.id, u.lastname, u.firstname, u.mailformat, u.maildisplay, r.name ', 'u.lastname ASC', false);
                }

                $a->coursename = format_string($course->fullname);
                $a->reporturl = $CFG->wwwroot.'/course/report/pending/index.php?course='.$stat->courseid;
                $a->courseurl = $CFG->wwwroot.'/course/view.php?id='.$stat->courseid;
                $text = get_string('warningemailtxt',  'report_pending', $a );

                if($users) {
                    foreach($users as $user) {
                        //$debugmsg = '  Tutor= '.fullname($user)."    incidencia= $stat->id";
                        $html = ($user->mailformat == 1) ? get_string('warningemailhtml',  'report_pending', $a ) : '';
                        $samegroup = false;
                        if($stat->modname == 'assignment' AND $groupmode == SEPARATEGROUPS) {
                            if($teachergroups = groups_get_all_groups($stat->courseid, $user->id, $groupigid, 'g.id, g.id')) {
                                $teachergroups = array_keys($teachergroups);
                            } else {
                                $teachergroups = array();
                            }
                            $samegroup = array_intersect($studentgroups, $teachergroups);
                        } else {
                            $samegroup = true;
                        }
                        if($samegroup) {
                            if(email_to_user($user, $from, $subject, $text, $html)) {
                                set_field('pending_duties', 'timemailed', $timetocheck, 'id', $stat->id, 'userid', $stat->userid, 'courseid', $stat->courseid);
                                if($supportuser) {
                                    email_to_user($supportuser, $from, $subject, $text, $html);
                                }
                                if(!empty($CFG->pendingduties_coordemail) && $coords) {
                                    foreach($coords as $coorduser) {
                                        email_to_user($coorduser, $from, $subject, $text, $html);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            rs_close($rs);
        }
    }



###############################################
/// Utility functions, not part of API

    // connect to database for holidays
    function get_holidays($timestart, $timeend) {
        global $CFG;
        $holidays = array('0'=>0);
        $select = " ((datestart>='$timestart' ) OR ((datestart + timeduration)>='$timestart'))
                        AND  datestart<=$timeend ";
        if($dates = get_records_select('holidays', $select, 'datestart ASC')) {
            foreach($dates as $date) {
                $days = $date->timeduration / DAYSECS;
                for($i=1; $i<=$days; $i++) {
                    $holidays[] = stats_get_base_daily(($date->datestart+DAYSECS*($i-1)));
                }
            }
        }
        return $holidays;
    }

    // $holidays must be an array of timestamps
    function holiday_time($starttime, $endtime, $holidays, $weekends=true) {
        global $CFG;

        $oneday=DAYSECS;
        $days = 0;
		if ($starttime < time() - $oneday*365*10) return 0;
		if ($endtime   > time() + $oneday*365*10) return 0;

		$startday = stats_get_base_daily($starttime);
		$endday   = stats_get_base_daily($endtime);
		// del primer día (y del último), sólo descontar las horas que correspondan
        $calendar_weekend = isset($CFG->calendar_weekend) ? intval($CFG->calendar_weekend) : 65;
        for($day = $startday+$oneday; $day<$endday; $day+=$oneday) {
            $date = getdate($day);
                 //if(CALENDAR_WEEKEND & (1 << ($dayweek % 7)))
            //if ($weekends AND (($date['wday']==0) OR ($date['wday']==6))) {
            if ($weekends AND ($calendar_weekend & (1 << ($date['wday'] % 7)))) {
                $days++;
			} elseif (in_array($day, $holidays)) {
                $days++;  // festivo
            }
        }
        $discount = $days*$oneday;
        if ($startday+$oneday < $endday) {
            $date = getdate($starttime);
            if (in_array($startday, $holidays) OR ($calendar_weekend & (1 << ($date['wday'] % 7)))) {
                $discount += ($startday+$oneday - $starttime);  // descontar desde aquí hasta el inicio del día siguiente
            }

        $date = getdate($endtime);
            if (in_array($endday, $holidays) OR ($calendar_weekend & (1 << ($date['wday'] % 7)))) {
                $discount += ($endtime - $endday);  // descontar las horas del último día
            }
        }
        return $discount;
    }


    // $user must include fields from user_ulpgc table
    function get_scheduler_mintime($user) {
        global $CFG;

        return  $CFG->scheduler_minperiod;

        $ulpgc_user = get_record('user_ulpgc', 'userid', $user->userid);
        if(!$ulpgc_user) {
            return false;
        }

        $min_time = $CFG->scheduler_minperiod;

        if($ulpgc_user->dedication < 24) {
            $tutorhours = $ulpgc_user->dedication/3;
        } else {
            $tutorhours = $CFG->scheduler_minperiod/60;
        }
        $coef = $ulpgc_user->totaldedication/$ulpgc_user->dedication;

        $min_time = $tutorhours * $coeff *60; // here in minutes

        if($min_time > $CFG->scheduler_minperiod) {
            $min_time = $CFG->scheduler_minperiod;
        }
        return $min_time;
    }


    // use report for a single user
    function ulpgc_usereports_single($user, $categories, $returnarray=false) {
        global $CFG;

        if(!$user) {
          notify('no user');
          return false;
        }

        $userid = $user->id;
        $report = array();
        if($courses = get_my_enroled_courses($user->id, '', false, $categories)){
            $select = " userid='$userid' AND roleid IN ( {$CFG->pendingduties_checkedroles} ) AND hidden=0 AND enrol='miulpgc' ";
            $delcourses = array();
            foreach($courses as $course) {
                $context = context_course::instance($course->id);
                if(!record_exists_select('role_assignments', $select." AND contextid='{$context->id}' ") OR ($course->credits <= 0) ) {
                    // do not count course if not enroled directly
                    // do not count course without credit charge (= non-official)
                    $delcourses[] = $course->id;
                }
            }

            if($delcourses) {
                foreach($delcourses as $del) {
                    unset($courses[$del]);
                }
            }

            if(!$courses) {
                return false;
            }

            $report['name'] = fullname($user, true, 'lastname firstname');
            $report['idnumber'] = $user->idnumber;
            $report['courses'] = array();
            $courseids = implode(',', array_keys($courses));

            /// calculate dialogue entries
            $sql = "SELECT d.course, COUNT(1) AS entries
                      FROM {$CFG->prefix}dialogue_entries de
                        JOIN {$CFG->prefix}dialogue d ON d.id = de.dialogueid AND d.course IN ( $courseids )
                      WHERE de.userid='$userid' GROUP BY d.course ";
            $dialogues = get_records_sql($sql);

            /// calculate forum posts
            $sql = "SELECT fd.course, COUNT(1) AS posts
                      FROM {$CFG->prefix}forum_posts fp
                        JOIN {$CFG->prefix}forum_discussions fd ON fd.id = fp.discussion AND fd.course IN ( $courseids )
                      WHERE fp.userid='$userid' GROUP BY fd.course ";
            $posts = get_records_sql($sql);

            /// calculate assignment califications
            $sql = "SELECT a.course, COUNT(1) AS subs
                      FROM {$CFG->prefix}assignment_submissions sub
                        JOIN {$CFG->prefix}assignment a ON a.id = sub.assignment AND a.course IN ( $courseids )
                      WHERE sub.teacher='$userid' GROUP BY a.course ";
            $assignments = get_records_sql($sql);

            foreach($courses as $course){
              if($course->credits < 0 ) {
                continue; // do not count courses without credit charge (= non-official)
              }
              $data = array();
              $data['shortname'] = $course->shortname;
              $data['fullname'] = $course->fullname;
              $data['dialogues'] = 0;
              if(isset( $dialogues[$course->id])) {
                  $data['dialogues'] = $dialogues[$course->id]->entries;
              }
              $data['posts'] = 0;
              if(isset($posts[$course->id])) {
                  $data['posts'] = $posts[$course->id]->posts;
              }
              $data['assignments'] = 0;
              if(isset($assignments[$course->id])) {
                  $data['assignments'] = $assignments[$course->id]->subs;
              }
              $report['courses'][$course->id] = $data;
            }
        }

        if($returnarray) {
          return array("$user->idnumber" => $report);
        }
        return $report;
    }

    // use report for multiple users
    function ulpgc_usereports_multiple($categories) {
        global $CFG;

        $reports = array();

        $sql = "SELECT uu.userid AS id, u.firstname, u.lastname, u.idnumber, u.institution, u.department
                FROM {$CFG->prefix}user_ulpgc uu
                  JOIN {$CFG->prefix}user u ON uu.userid = u.id ORDER BY u.lastname ASC";
        if(!$userlist = get_recordset_sql($sql)) {
          notify('no ULPGC users');
          return false;
        }

        while ($user = rs_fetch_next_record($userlist)) {
            if($userreport = ulpgc_usereports_single($user, $categories)) {
                $reports[$user->id] = $userreport;
            }
        }
        return $reports;
    }


    // mold user report  array into output format
    function ulpgc_usereports_format($reports, $format, $template='') {
        global $CFG, $SITE;


        $date = date('Y-m-d-Hi');
        $struserreports = get_string('usereports', 'block_admin_ulpgc');
        $filename = clean_filename($SITE->shortname.'_'.$struserreports.'_'.$date.'.'.$format);
        $headers = array('name' => get_string('fullname'),
                          'idnumber' => get_string('idnumber'),
                          'courses' =>  get_string('courses'),
                          'dialogues' => get_string('messages','block_admin_ulpgc').' ('.get_string('modulenameplural', 'dialogue').')',
                          'forums' => get_string('messages', 'block_admin_ulpgc').' ('.get_string('modulenameplural', 'forum').')',
                          'assignments' => get_string('assesments', 'block_admin_ulpgc').' ('.get_string('modulenameplural', 'assignment').')' );

        if(!$reports OR !$format) {
          return 'nothing to show';
        }

        switch($format) {
          case 'html' :
                        $table = new Object();
                        $table->head = $headers;
                        $table->align = array('left', 'left', 'left', 'center', 'center', 'center');
                        $table->width = '95%';
                        $table->tablealign = 'center';
                        $table->summary = get_string('usereports','block_admin_ulpgc');
                        $table->data = array();
                        $sep = ' ';
                        foreach($reports as $report) {
                            $row = array();
                            $row[] = $report['name'];
                            $row[] = $report['idnumber'];
                            $courses = $report['courses'];
                            $cnames = array();
                            $dialogues = array();
                            $posts = array();
                            $assigns = array();
                            foreach($courses as $course) {
                                $cnames[] = '<div class="boxaligncenter centerpara" >'.$course['shortname'].'</div>';
                                $dialogues[] = '<div class="boxaligncenter centerpara" >'.$course['dialogues'].'</div>';
                                $posts[] = '<div class="boxaligncenter centerpara" >'.$course['posts'].'</div>';
                                $assigns[] = '<div class="boxaligncenter centerpara" >'.$course['assignments'].'</div>';

                            }
                            $row[] = implode($sep, $cnames);
                            $row[] = implode($sep, $dialogues);
                            $row[] = implode($sep, $posts);
                            $row[] = implode($sep, $assigns);
                            $table->data[] = $row;
                            unset($row);
                        }

                        print_table($table);
                        break;

          case 'xls' :
          case 'ods' :
                    /// Creating a workbook
                        if($format == 'xls') {
                            require_once($CFG->dirroot.'/lib/excellib.class.php');
                            $workbook = new MoodleExcelWorkbook("-");
                        } else {
                            require_once($CFG->dirroot.'/lib/odslib.class.php');
                            $workbook = new MoodleODSWorkbook("-");
                        }
                    /// Sending HTTP headers
                        $workbook->send($filename);
                    /// Adding the worksheet
                        $myxls =& $workbook->add_worksheet($struserreports);

                    /// Print names of all the fields
                        $column = 0;
                        foreach($headers as $field) {
                            $myxls->write_string(0,$column,$field);
                            $column +=1;
                        }
                        $row = 1;
                        foreach($reports as $report) {
                            $myxls->write_string($row,0, $report['name']);
                            $myxls->write_string($row,1, $report['idnumber']);
                            $courses = $report['courses'];
                            foreach($courses as $course) {
                                $myxls->write_string($row,2, $course['shortname']);
                                $myxls->write_string($row,3, $course['dialogues']);
                                $myxls->write_string($row,4, $course['posts']);
                                $myxls->write_string($row,5, $course['assignments']);
                                $row +=1;
                            }

                        }
                    /// Close the workbook
                        $workbook->close();
                        exit;
                        break;

          case 'csv' :
                        $separator = "\t";
                    /// Print header to force download
                        @header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
                        @header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
                        @header('Pragma: no-cache');
                        header("Content-Type: application/download\n");
                        header("Content-Disposition: attachment; filename=\"$filename\"");

                    /// Print names of all the fields
                        $column = 0;
                        echo implode($separator, $headers);
                        echo "\n";

                        foreach($reports as $report) {
                            $row = $report['name'].$separator.$report['idnumber'];
                            $courses = $report['courses'];
                            $row .= $separator.implode($separator, $courses);
                        }
                        echo "\n";
                        exit;
                        break;

          case 'pdf' :
                        include_once($CFG->libdir.'/pdflib.php');
                        if($template) {
                            $contents = file_get_contents($CFG->dataroot.'/1/'.$template);
                        } else {
                           $contents = get_string('templatexample','block_admin_ulpgc');
                        }

                        $pdf = new pdf;
                        $pdf->print_header = true;
                        $pdf->print_footer = false;
                        $pdf->SetMargins(10, 25, 20);
                        $pdf->SetAutoPageBreak(true, 15);
                        $pdf->setHeaderData('', 20);            // ($ln="", $lw=0, $ht="", $hs="")


                        $table = new Object();
                        $table->head = array('courses' =>  get_string('courses'),
                                                'dialogues' => get_string('messages','block_admin_ulpgc').' ('.get_string('modulenameplural', 'dialogue').')',
                                                'forums' => get_string('messages', 'block_admin_ulpgc').' ('.get_string('modulenameplural', 'forum').')',
                                                'assignments' => get_string('assesments', 'block_admin_ulpgc').' ('.get_string('modulenameplural', 'assignment').')' );
                        $table->align = array('left', 'center', 'center', 'center');
                        $table->width = '95%';
                        $table->tablealign = 'center';
                        $table->summary = get_string('usereports','block_admin_ulpgc');
                        $sep = '<br />';
                        foreach($reports as $report) {
                            $pdf->AddPage();
                            $table->data = array();
                            $text = ($contents);
                            $text = str_replace('%%NOMBRE%%', $report['name'], $text);
                            $text = str_replace('%%DNI%%', $report['idnumber'], $text);
                            $courses = $report['courses'];
                            $cnames = array();
                            $dialogues = array();
                            $posts = array();
                            $assigns = array();
                            foreach($courses as $course) {
                                $row = array();
                                $row[] = $course['shortname'];
                                $row[] = $course['dialogues'];
                                $row[] = $course['posts'];
                                $row[] = $course['assignments'];
                                $table->data[] = $row;
                                unset($row);
                            }
                            $tabletext = print_table($table, true);
                            $text = str_replace('%%TABLA%%', $tabletext, $text);
                            $pdf->WriteHTML($text);
                        }
                        $pdf->Output('test.pdf', 'I');
                        die;
                        break;
        }

    }

    // Update role assignments for department supervisors in courses
    function ulpgc_assign_dept_supervisors($role=0, $departments=false) {
        global $CFG;

        $now = time();

        if(!$role) {
          $role = $CFG->pendingduties_checkerrole;
        }
        if(!$departments) {
            $departments = get_records('departamentos');
        }

        foreach($departments as $department) {
            $courses = get_records_select('course', "coddept='{$department->codigo}' ", '', 'id, coddept');
            $users = array();
            if($id = get_field('user', 'id', 'username',$department->director)) {
              $users[$id] = $id;
            }/*
            if($id = get_field('user', 'id', 'username',$department->secretario)) {
              $users[$id] = $id;
            }*/
            if($courses && $users) {
                foreach($courses as $course) {
                    $context = context_course::instance($course->id);
                    role_unassign($role, 0, 0, $context->id, 'dept');
                    foreach($users as $userid) {
                        role_assign($role, $userid, 0, $context->id, 0, 0, 1, 'dept');
                    }
                }
            }
        }
        set_config('ulpgcdeptslastassigned', $now);
    }


    // Update role assignments for faculty supervisors in courses
    function ulpgc_assign_faculty_supervisors($role=0, $faculties=false) {
        global $CFG;

        $now = time();

        if(!$role) {
          $role = $CFG->pendingduties_checkerrole;
        }
        if(!$faculties) {
            $faculties = get_records('centros');
        }

        foreach($faculties as $faculty) {
            $categories = get_records_select('course_categories', "faculty_degree LIKE '{$centre->codigo}\_%\___\_00' ", '', 'id, faculty_degree');
            $users = array();
            if($faculty->director && $id = get_field('user', 'id', 'username',$faculty->director)) {
              $users[$id] = $id;
            } /*
            if($faculty->secretario && $id = get_field('user', 'id', 'username',$faculty->secretario)) {
              $users[$id] = $id;
            }*/
            if($categories && $users) {
                foreach($categories as $cat) {
                    $context = context_coursecat::instance($cat->id);
                    role_unassign($role, 0, 0, $context->id, 'faculty');
                    foreach($users as $userid) {
                        role_assign($role, $userid, 0, $context->id, 0, 0, 1, 'faculty');
                    }
                }
            }
        }

        set_config('ulpgcfacslastassigned', $now);
    }


    // Update tables for Department & Faculty directors
    function ulpgc_update_facultydepts() {
        global $CFG;

        $updated = false;
        $now = time();

        if($updated) {
          set_config('ulpgcdatalastupdated', $now);
        }
    }


    // Update tables for ULPGC PDI staff
    function ulpgc_update_userpdi() {
        global $CFG;

        $updated = false;
        $now = time();

        // do NOT update ulpgcdatalastupdated
    }




    // code from block useradmin
    /**
     * Get auth plugins available and used by some active user
     * @return array of plugin instance, keyed by $authtype
     */
    function ulpgc_get_available_auth_plugins() {
        global $CFG;
        // Get auth used by any user (retrieve only auth field from user table)
        $usedauths = get_records_sql("select distinct auth from {$CFG->prefix}user where deleted = 0");
        // get currently installed and enabled auth plugins
        $authsavailable = get_list_of_plugins('auth');
        // Load all plugins
        $authplugins = array();
        foreach ($authsavailable as $auth) {
            $authplugin = get_auth_plugin($auth);
            if ( array_key_exists($authplugin->authtype, $usedauths)) {
                $authplugins[$authplugin->authtype] = $authplugin;
            }
        }
        return $authplugins;
    }

    /**
     * Similar to optional_param() but returns $previousvalue if param is not set at all,
     * and returns $clearvalue if param is set to empty string
     */
    function ulpgc_optional_param_clearing($paramname, $previousvalue=NULL, $clearvalue=NULL, $type=PARAM_CLEAN ) {
        // detect_unchecked_vars addition
        global $CFG;
        if (!empty($CFG->detect_unchecked_vars)) {
            global $UNCHECKED_VARS;
            unset ($UNCHECKED_VARS->vars[$paramname]);
        }

        // if is empty string, return clear value
        if ( array_key_exists($paramname, $_REQUEST) && $_REQUEST[$paramname] === '' ) {
            $param = $clearvalue;
        }
        // If not set at all, use previous value
        else if ( !array_key_exists($paramname, $_REQUEST) ) {
            $param = $previousvalue;
        }
        // Else use request
        else {
            $param = $_REQUEST[$paramname];
        }

        return clean_param($param, $type);
    }

    /**
     * Execute paged query on Users
     * In parameter $searchcount (passed by reference) returns the count of the users
     * retrieved by the query, WITHOUT taking account of paging
     * @return array of users
     */
    function ulpgc_get_manageusers_listing($tableview, $sqluserfiltering, &$searchcount, &$foundcount, $sort='lastaccess', $dir='ASC', $firstinitial='', $lastinitial='', $page=0, $recordsperpage=99999) {
        global $CFG, $SESSION;

        $guest = get_guest();

        $userlist = get_records_select('user', $sqluserfiltering, '', 'id, idnumber', 0, MAX_BULK_USERS);
        $foundcount = is_array($userlist) ? count($userlist) : 0;

        $getusers = array();
        if($tableview == 'viewfilter') {
            if(is_array($userlist)) {
                $usersids = array_keys($userlist);
            }
        } else {
            if(is_array($SESSION->bulk_users)) {
                $usersids = array_keys($SESSION->bulk_users);
            }
        }

        $selectnousers = " u.id<>{$guest->id} AND u.id <> 1 AND u.deleted <> 1 AND u.username <> 'changeme' AND u.username <> 'guest' ";
        $selectlist = "u.*, mh.name AS mnethostname, mh.wwwroot AS mnethostwwwroot";

        $LIKE      = sql_ilike();
        $fullname  = sql_fullname();

        $from = "{$CFG->prefix}user u, {$CFG->prefix}mnet_host mh";

        $where = "(u.mnethostid = mh.id OR u.mnethostid IS NULL)
                    AND $selectnousers ";

        if(!empty($usersids) && is_array($usersids)) {
            $list = "'".implode("', '", $usersids)."'";
            $where .= "AND u.id IN ( $list ) ";
        } else {
            return array();
        }

        if ($firstinitial) {
            $where .= ' AND u.firstname '. $LIKE .' \''. $firstinitial .'%\' ';
        }
        if ($lastinitial) {
            $where .= ' AND u.lastname '. $LIKE .' \''. $lastinitial .'%\' ';
        }
        if ($sort) {
            $sort = ' ORDER BY '. $sort .' '. $dir;
        }


        // SQL for paged query
        $sql = "SELECT $selectlist FROM $from WHERE $where  $sort"; // $limit ";


        // SQL for count query, w/o paging limit
        $sqlcount = "SELECT count(*) FROM $from WHERE $where ";


        // Execute Count query first
        $searchcount = count_records_sql($sqlcount);

        // Execute full (paged) query
        $users = get_records_sql($sql, $page, $recordsperpage);

        return $users;
    }


    /**
     * Returns an optionally collapsable text
     * If collapsed, text is replaced by ellipses with alt-text (if available) or full text,
     * as tooltip
     */
    function ulpgc_collapsable_text($text, $showfull = TRUE, $alttext = NULL) {
        // If string is empty, return empty string
        if ( !$text  ){
            return '';
        }
        // return full text
        else if ( $showfull ) {
            return $text;
        }
        // return ellipsed text
        else {
            $tooltiptext = ($alttext)?(s($alttext)):(s($text));
            return "<a class=\"tooltip\" hrep=\"#\" >...<span>$tooltiptext</span></a>";
        }
    }


    /**
     * Prints several popupforms for module instance selection
     *
     *
     */
    function ulpgc_print_modinstance_select($baseurl, $categoryid, $courseid, $modid, $modname) {

        print_box_start();
        $categories = make_categories_options();
        echo '<div class="categories" >';
        echo get_string('category').':&nbsp;';
        popup_form($baseurl.'?cat=', $categories, 'categoryselect', $categoryid);
        echo '</div>';
        echo '<div class="courses" >';
        $courses = array();
        if($categoryid) {
            $courses = get_records_menu('course', 'category', $categoryid, '', 'id, fullname');
        }
        echo get_string('courses').':&nbsp;';
        popup_form($baseurl."?cat=$categoryid&amp;c=", $courses, 'courseselect', $courseid);
        echo '</div>';
        echo '<div class="trackers" >';
        $instances = array();
        if($courseid) {
            $instances = get_records_menu($modname, 'course', $courseid, '', 'id, name');
        }
        echo get_string('modulename', $modname).':&nbsp;';
        popup_form($baseurl."?cat=$categoryid&amp;c=$courseid&amp;mod=", $instances, 'instanceselect', $modid);
        echo '</div>';
        print_box_end();
        echo '<br />';
    }


    /**
     * Prints part of a form in bulk users actions
     * Select directory with user-paired files, & userfile identification format
     *
    */
    function ulpgc_print_usersdirectory_config($courseid, $usersfilesdir, $fileprefix, $filesuffix, $userfield) {
        global $CFG;

        print_box_start();
        $coursedirs = array();
        if($courseid) {
            $dirs = get_directory_list($CFG->dataroot . '/' . $courseid, array($CFG->moddata, 'backupdata', '_thumb'), true, true, false);
            $coursedirs = array();
            foreach ($dirs as $dir) {
                $coursedirs[$dir] = $dir;
            }
        }
        echo get_string('userattachmentsdir', 'block_admin_ulpgc').'&nbsp;';
        choose_from_menu($coursedirs, 'dir', $usersfilesdir, 'choose', '', '' );
        echo '<br />';
        echo get_string('userfilenamehelp', 'block_admin_ulpgc');
        echo '<br />';
        echo get_string('fileprefix', 'block_admin_ulpgc').'&nbsp;';
        echo '<input type="text" name="prefix" size="6" value="'.$fileprefix.'">&nbsp;';
        echo get_string('userfield', 'block_admin_ulpgc').'&nbsp;';
        $fields = array('userid' => get_string('id'),
                        'idnumber' => get_string('idnumber'),
                        'username' => get_string('username'),
                        'fullname' => get_string('fullname'));
        choose_from_menu($fields, 'ufield', $userfield, '');

        echo get_string('filesuffix', 'block_admin_ulpgc').'&nbsp;';
        echo '<input type="text" name="suffix" size="6" value="'.$filesuffix.'">';
        echo '<br />';
        echo '<input type="checkbox" name="needuserfile" value="1">&nbsp;';
        echo get_string('needuserfile', 'block_admin_ulpgc');
        print_box_end();
        echo '<br />';
    }


?>
