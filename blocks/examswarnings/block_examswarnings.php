<?php
/**
 * This file contains block_examswarnings class
 *
 * @package   block_examswarnings
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//require_once('locallib.php');


class block_examswarnings extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_examswarnings');
    }

    function has_config() {
        return true;
    }

    /**
     * All multiple instances of this block
     * @return bool Returns false
     */
    function instance_allow_multiple() {
        return false;
    }

    /**
     * Set the applicable formats for this block to all
     * @return array
     */
    function applicable_formats() {
        return array('site-index' => true, 'my'=>true, 'course' => true);
    }

    /**
     * Allow the user to configure a block instance
     * @return bool Returns true
     */
    function instance_allow_config() {
        return false;
    }

    /**
     * The navigation block cannot be hidden by default as it is integral to
     * the navigation of Moodle.
     *
     * @return false
     */
    function  instance_can_be_hidden() {
        return false;
    }

    function user_can_addto($page) {
        // Don't allow people to add the block if they can't even use it
        if (!has_capability('block/examswarnings:view', $page->context)) {
            return false;
        }

        return parent::user_can_addto($page);
    }



    function get_content() {
        global $CFG, $USER, $DB, $OUTPUT;

        if($this->content !== NULL) {
            return $this->content;
        }

        //require_once($CFG->dirroot.'/mod/examregistrar/locallib.php');
        //require_once($CFG->dirroot.'/mod/examregistrar/lib.php');
        //require_once($CFG->dirroot.'/mod/examregistrar/booking_form.php'); // lagdays functions
        require_once($CFG->dirroot.'/blocks/examswarnings/locallib.php');

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (empty($this->instance)) {
           return $this->content = '';
        }

        $pagetype = $this->page->pagetype;
        $course = $this->page->course;
        $systemcontext = context_system::instance();
        $context = $this->page->context;

        $canbook = has_capability('mod/examregistrar:book', $context);
        $cansubmit = has_capability('mod/examregistrar:submit', $context);

        $config = get_config('block_examswarnings');
        list($period, $session, $extra, $check, $lagdays) = examswarnings_get_sessiondata($config);

        $now = time();

        $warnings = array();
        $upcomings = array();
        $reminders = array();
        $roomcalls = array();

        if((strtotime(" - $lagdays days ",  $session->examdate) > $now) &&
           ($session->examdate <  $check)) {
            $warnings = examswarnings_notappointedexams($period, $session, $extra); // for students
        }

        if(($session->examdate > $now) && ($session->examdate <  $check)) {
            $upcomings = examswarnings_upcomingexams($period, $session); // for students
            $reminders = examswarnings_reminder_upcomingexams($period, $session); // for teachers
            $roomcalls = examswarnings_roomcall_upcomingexams($period, $session); // for tecahers/room staff
        }

        if(!$moduleid = $DB->get_field('modules', 'id', array('name'=>'examregistrar', 'visible'=>1))) {
            return $this->content;
        }

        if(is_siteadmin() && debugging('', DEBUG_DEVELOPER)) {
            $o = new stdClass();
            $o->category = 6;
            $o->programme = '4036';
            $o->shortname = '46051';
            $o->roomidnumber = 'A-27';
            if(!$warnings) {
                $warnings = array($o);
            }
            if(!$upcomings) {
                $upcomings = array($o);
            }
            if(!$reminders) {
                $reminders = array($o);
            }
            if(!$roomcalls) {
                $roomcalls = array($o);
            }
        }

        if($warnings) {
            $this->content->items[] = html_writer::span(get_string('warningduedate', 'block_examswarnings', count($warnings)), 'warning examwarning');
            $this->content->icons[] = '';

            foreach($warnings as $warning) {
                $boardid = $DB->get_field('course', 'id', array('category'=>$warning->category, 'shortname'=>'Coord-'.$warning->programme));
                if($cmid = $DB->get_field('course_modules', 'id', array('course'=>$boardid, 'module'=>$moduleid, 'idnumber'=>'examreg'))) {
                    $url = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cmid, 'tab'=>'booking'));
                    $this->content->items[] = html_writer::link($url, $warning->shortname, array('class'=>'warning'));
                } else {
                    $this->content->items[] = html_writer::span($warning->shortname, 'warning');
                }
                $this->content->icons[] = '<img src="' . $OUTPUT->pix_url('i/risk_xss') . '" class="icon" alt="" />&nbsp;';
            }
        }

        $icon = '<img src="' . $OUTPUT->pix_url('i/test') . '" class="iconsmall" alt="" />&nbsp;';
        if($upcomings) {
            $this->content->items[] = html_writer::span(get_string('warningupcoming', 'block_examswarnings', count($upcomings)), 'warning examwarning');
            $this->content->icons[] = '';

            foreach($upcomings as $warning) {
                $boardid = $DB->get_field('course', 'id', array('category'=>$warning->category, 'shortname'=>'Coord-'.$warning->programme));
                if($cmid = $DB->get_field('course_modules', 'id', array('course'=>$boardid, 'module'=>$moduleid, 'idnumber'=>'examreg'))) {
                    $url = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cmid, 'tab'=>'booking'));
                    $this->content->items[] = html_writer::link($url, $warning->shortname, array('class'=>'warning'));
                } else {
                    $this->content->items[] = html_writer::span($warning->shortname, 'warning');
                }
                $this->content->icons[] = $icon;
            }
        }

        if($reminders) {
            $this->content->items[] = html_writer::span(get_string('warningupcoming', 'block_examswarnings', count($reminders)), 'warning examwarning');
            $this->content->icons[] = '';

            foreach($reminders as $warning) {
                $boardid = $DB->get_field('course', 'id', array('category'=>$warning->category, 'shortname'=>'JEval-'.$warning->programme));
                if($cmid = $DB->get_field('course_modules', 'id', array('course'=>$boardid, 'module'=>$moduleid, 'idnumber'=>'examreg'))) {
                    $url = new moodle_url('/mod/examregistrar/view.php', array('id'=>$cmid));
                    $this->content->items[] = html_writer::link($url, $warning->shortname, array('class'=>'warning'));
                } else {
                    $this->content->items[] = html_writer::span($warning->shortname, 'warning');
                }
                $this->content->icons[] = $icon;
            }
        }

        if($roomcalls) {
            $this->content->items[] = html_writer::span(get_string('roomcallupcoming', 'block_examswarnings', count($roomcalls)), 'warning examwarning');
            $this->content->icons[] = '';

            foreach($roomcalls as $roomcall) {
                $this->content->items[] = html_writer::span($roomcall->roomidnumber, 'warning');

                $this->content->icons[] = $icon;
            }
        }

        if(!$this->content->items) {
            return '';
        }
        return $this->content;
    }



    // cron function, used to synchronize exams data
    function cron() {
        global $CFG, $DB, $OUTPUT;
        
        return true; // ecastro ULPGC disable cron for tasks
        

    /// We are going to measure execution times
        $starttime =  microtime();

    /// And we have one initial $status
        $status = true;

    /// We require some stuff
        $config = get_config('block_examswarnings');

    /// check exams data
        if ($config->primaryreg) {
            require_once($CFG->libdir .'/statslib.php');

            $timetocheck  = time()-60;
            $today = stats_get_base_daily();

            /// checks for once a day except if in debugging mode
            if(!debugging('', DEBUG_DEVELOPER)) {
                $timetocheck  = $today + $config->runtimestarthour*60*60 + $config->runtimestartminute*60;
                // Note: This will work fine for sites running cron each 4 hours or less (hoppefully, 99.99% of sites). MDL-16709
                // check to make sure we're due to run, at least 20 hours after last run
                if (isset($config->lastexecution) && ((time() - 20*60*60) < $config->lastexecution)) {
                    mtrace("...preventing stats to run, last execution was less than 20 hours ago.");
                    return false;
                // also check that we are a max of 4 hours after scheduled time, stats won't run after that
                } else if (time() > $timetocheck + 4*60*60) {
                    mtrace("...preventing stats to run, more than 4 hours since scheduled time.");
                    return false;
                }
            }

            mtrace(" ... processing exams reminders for teachers and students ");
            mtrace(" ... time to check ".userdate($timetocheck). '    '.$timetocheck  );

            // if it's not our first run, just return the most recent.
            if(isset($config->lastexecution)) {
                $lastexecution = $config->lastexecution;
                if ($lastexecution >= $timetocheck) {
                    // if already run, do not repeat again
                    return false;
                }
            } else {
                // first time, we need a starttime: a month ago
                $lastexecution = $timetocheck - 2*30*24*60*60;
            }

            $now = time();
            mtrace(' ... last execution '.userdate($lastexecution). ' '. $lastexecution  );
            mtrace(' ... now '.userdate($now). ' '. $now  );

            if (($now > $timetocheck) && ($now >= strtotime("+1 day", $lastexecution) || debugging('', DEBUG_DEVELOPER))) {

                require_once($CFG->dirroot.'/mod/examregistrar/locallib.php');
                require_once($CFG->dirroot.'/mod/examregistrar/booking_form.php'); // lagdays functions

                $controluser = array();
                if(isset($config->controlemail) && $config->controlemail) {
                    if($emails = explode(',', $config->controlemail)) {
                        foreach($emails as $email) {
                            $user = core_user::get_support_user();
                            $user->email = trim($email);
                            $user->mailformat = 1;
                            $user->id = 1;
                            $controluser[] = clone $user;
                        }
                    }
                }

                $regconfig = get_config('examregistrar');
                $examregistrar = $DB->get_record('examregistrar', array('id'=> $config->primaryreg));

                $periods = examregistrar_current_periods($examregistrar);
                $period = reset($periods);
                $extra = examregistrar_is_extra_period($examregistrar, $period);
                
                $session = examregistrar_next_sessionid($examregistrar, $now, true);
                mtrace("...doing examwarnings for session ".userdate($session->examdate)."    ".$session->examdate );
                
                $lagdays = examregistrar_set_lagdays($examregistrar, $regconfig, $period, array());                

                $check = max(7, $config->reminderdays, $config->roomcalldays, 
                                $config->warningdays, $config->warningdaysextra, $config->examconfirmdays); // set 7, one week as absolute minimum
                $check = strtotime("+ $check days  ") - 10;
                $lagtime = stats_get_base_daily($session->examdate);
                mtrace("...doing examwarnings before ".userdate($lagtime)."    ".$lagtime );
                if(($now < $lagtime) && ($session->examdate < $check) OR debugging('', DEBUG_ALL)) {

                    mtrace("...doing examwarnings checkigns for session ".userdate($session->examdate) );

                    $names = get_all_user_name_fields(true, 'u');
                /// email reminders to teachers with exam
                    if($config->enablereminders) {
                        mtrace("...doing reminders for teachers.");
                        mtrace("...config->reminderdays ". $config->reminderdays);
                        $start = strtotime("+{$config->reminderdays} days", $today) - DAYSECS;
                        $end = strtotime("+{$config->reminderdays} days", $today) + 10;
                        if(($session->examdate >= $start) && ($session->examdate < $end)) {
                            mtrace(' ... reminders for session '. date('Y-m-d', $session->examdate));

                            // e.callnum > 0  exclude special call exams
                            $sql = "SELECT e.id, e.courseid
                                    FROM {examregistrar_exams} e
                                    WHERE e.examregid = :examregid AND e.examsession = :session AND e.visible = 1 AND e.callnum > 0
                                        AND EXISTS (SELECT 1 FROM {examregistrar_bookings} b WHERE b.examid = e.id AND b.booked = 1) ";
                            if($exams = $DB->get_records_sql_menu($sql, array('examregid'=>$config->primaryreg, 'session'=>$session->id ))) {
                                $params = array();
                                $checkedroles = get_config('block_examswarnings', 'reminderroles');
                                $checkedroles = explode(',', $checkedroles);
                                list($inrolesql, $inparams) = $DB->get_in_or_equal($checkedroles);
                                $params = array_merge($params, $inparams);

                                list($incoursesql,$inparams) = $DB->get_in_or_equal($exams);
                                $params = array_merge($params, $inparams);

                                $sql = "SELECT DISTINCT ra.id as rid, c.shortname, c.fullname, u.id, u.email, u.mailformat, u.username, $names
                                        FROM {user} u
                                            JOIN {role_assignments} ra ON u.id = ra.userid
                                            JOIN {context} ctx ON ra.contextid = ctx.id
                                            JOIN {course} c ON ctx.instanceid = c.id AND c.visible = 1
                                        WHERE ra.roleid $inrolesql AND c.id $incoursesql ";

                                //mtrace("...sql  ".$sql );
                                $users = $DB->get_records_sql($sql, $params);
                                if($users) {
                                    mtrace("...Entrando en users.");
                                    $from = get_string('examreminderfrom',  'block_examswarnings');
                                    $sent = array();
                                    foreach($users as $user) {
                                        $subject = get_string('examremindersubject', 'block_examswarnings', $user->shortname);
                                        $message = $config->remindermessage;
                                        $replaces = array('%%course%%' => $user->shortname.'-'.$user->fullname,
                                                        '%%date%%' => userdate($session->examdate, '%A %d de %B de %Y'),
                                                        );
                                        foreach($replaces as $search => $replace) {
                                            $message = str_replace($search, $replace, $message);
                                        }
                                        $text = html_to_text($message, 75, false);
                                        $html = ($user->mailformat == 1) ? format_text($message, FORMAT_HTML) : '';
                                        $flag = '';
                                        if(!debugging('', DEBUG_DEVELOPER)) {
                                            if(!email_to_user($user, $from, $subject, $text, $html)) {
                                                $flag = ' - '.get_string('remindersenderror', 'block_examswarnings');
                                            }
                                        }

                                        $sent[] = $user->shortname.': '.fullname($user, false, 'lastname firstname').$flag;
                                    }
                                    if(isset($config->controlemail) && $config->controlemail) {
                                        $info = new stdClass;
                                        $info->num = count($sent);
                                        $info->date = userdate($session->examdate, '%A %d de %B de %Y');
                                        list($sessionname, $idnumber) = examregistrar_item_getelement($session, 'examsession');
                                        $subject = get_string('controlmailsubject', 'block_examswarnings', "$sessionanme ($idnumber)");
                                        $text = get_string('controlmailtxt',  'block_examswarnings', $info )."\n\n".implode("\n", $sent);
                                        foreach($controluser as $cu) {
                                            $html = ($cu->mailformat == 1) ? get_string('controlmailhtml',  'block_examswarnings', $info ).'<br />'.implode(' <br />', $sent) : '';
                                            email_to_user($cu, $from, $subject, $text, $html);
                                        }
                                        mtrace('    ... sent '.count($sent).' teacher exam reminders.');
                                    }
                                }
                            } // end of if exams
                        }
                    } //end of enablereminders

                /// email reminders to room staff with exam
                    if($config->enableroomcalls) {
                        mtrace("...doing reminders for room staff.");
                        mtrace("...config->roomcalldays ". $config->roomcalldays);
                        $start = strtotime("+{$config->roomcalldays} days", $today) - DAYSECS;
                        $end = strtotime("+{$config->roomcalldays} days", $today) + 10;
                        if(($session->examdate >= $start) && ($session->examdate < $end)) {
                            mtrace(' ... room staff reminders for session '. date('Y-m-d', $session->examdate));
                            
                            $params = array('session'=>$session->id);
                            $checkedroles = get_config('block_examswarnings', 'roomcallroles');
                            list($inrolesql, $inparams) = $DB->get_in_or_equal($checkedroles, SQL_PARAMS_NAMED, 'role');
                            $params = array_merge($params, $inparams);

                            $sql = "SELECT s.id as sid, l.*, e.name AS name, e.idnumber AS idnumber
                                    FROM {examregistrar_staffers} s
                                    JOIN {examregistrar_locations} l ON l.id = s.locationid
                                    JOIN {examregistrar_elements} e ON e.examregid = l.examregid AND e.type='locationitem' AND e.id = l.location
                                    JOIN {examregistrar_session_rooms} sr ON sr.examsession = s.examsession AND sr.roomid = s.locationid AND sr.available = 1
                                    WHERE  s.userid > 0 AND s.examsession = :session AND s.visible = 1
                                            AND s.role $inrolesql
                                            AND EXISTS (SELECT 1 FROM {examregistrar_session_seats} ss WHERE ss.examsession = s.examsession AND ss.roomid = s.locationid )
                                    GROUP BY s.locationid ";

                            if($rooms = $DB->get_records_sql($sql, $params)) {
                                $sent = array();
                                foreach($rooms as $room) {

                                    $sql = "SELECT e.*, ss.bookedsite
                                            FROM {examregistrar_session_seats} ss
                                            JOIN {examregistrar_exams} e ON ss.examid = e.id
                                            JOIN {course} c ON c.id = e.courseid
                                            WHERE ss.examsession = :session AND ss.roomid = :room
                                            GROUP BY ss.examid
                                            ORDER BY c.shortname ASC ";
                                    $exams = $DB->get_records_sql($sql, array('session'=>$session->id, 'room'=>$room->id));
                                    $examnames = array();
                                    foreach($exams as $exam) {
                                        $examnames[$exam->id] = $exam->shortname.' - '.$exam->fullname.'<br />';
                                    }
                                    $sql = "SELECT s.id AS sid, s.info, s.role, e.name AS rolename, e.idnumber AS roleidnumber,
                                                            u.id, u.email, u.mailformat, u.username, $names
                                                FROM {examregistrar_staffers} s
                                                JOIN {user} u ON u.id = s.userid
                                                JOIN {examregistrar_elements} e ON e.type = 'roleitem' AND e.id = s.role
                                                WHERE s.examsession = :session AND s.locationid = :room AND s.visible = 1
                                                GROUP BY s.userid ";

                                    $users = $DB->get_records_sql($sql, array('session'=>$session->id, 'room'=>$room->id));
                                    if($users) {
                                        mtrace("...Entrando en users.");
                                        $from = get_string('examreminderfrom',  'block_examswarnings');
                                        foreach($users as $user) {
                                            $subject = get_string('roomcallsubject', 'block_examswarnings', $room->idnumber);
                                            $message = $config->roomcallmessage;
                                            $replaces = array('%%roomname%%' => $room->name, '%%roomidnumber%%' => $room->idnumber,
                                                              '%%rolename%%' => $user->rolename, '%%roleidnumber%%' => $user->roleidnumber,
                                                            '%%date%%' => userdate($session->examdate, '%A %d de %B de %Y'),
                                                            '%%examlist%%'=>$examnames,
                                                            );
                                            foreach($replaces as $search => $replace) {
                                                $message = str_replace($search, $replace, $message);
                                            }
                                            $text = html_to_text($message, 75, false);
                                            $html = ($user->mailformat == 1) ? format_text($message, FORMAT_HTML) : '';
                                            $flag = '';
                                            if(!debugging('', DEBUG_DEVELOPER)) {
                                                if(!email_to_user($user, $from, $subject, $text, $html)) {
                                                    $flag = ' - '.get_string('remindersenderror', 'block_examswarnings');
                                                }
                                            }
                                            $sent[] = $room->name.': '.fullname($user).$flag;
                                        }
                                    }
                                }
                                if(isset($config->controlemail) && $config->controlemail) {
                                    $info = new stdClass;
                                    $info->num = count($sent);
                                    $info->date = userdate($session->examdate, '%A %d de %B de %Y');
                                    list($sessionname, $idnumber) = examregistrar_item_getelement($session, 'examsession');
                                    $subject = get_string('controlmailsubject', 'block_examswarnings', "$sessionanme ($idnumber)");
                                    $text = get_string('controlmailtxt',  'block_examswarnings', $info )."\n\n".implode("\n", $sent);
                                    foreach($controluser as $cu) {
                                        $html = ($cu->mailformat == 1) ? get_string('controlmailhtml',  'block_examswarnings', $info ).'<br />'.implode(' <br />', $sent) : '';
                                        email_to_user($cu, $from, $subject, $text, $html);
                                    }
                                    mtrace('    ... sent '.count($sent).' teacher exam reminders.');
                                }
                            } // end if rooms
                        }
                    }

                /// email warnings to students with exam
                    if($config->enablewarnings) {
                        mtrace("...doing students reminders & warnings.");
                        mtrace("...config->examconfirmdays ". $config->examconfirmdays);
                        $start = strtotime("+{$config->examconfirmdays} days", $today) - DAYSECS;
                        $end = strtotime("+{$config->examconfirmdays} days", $today) + 10;
                        if(($session->examdate >= $start) && ($session->examdate < $end)) {
                            $sql = "SELECT b.id AS bid, b.userid, b.booked, MIN(b.bookedsite) AS bookedsite, e.courseid, c.fullname, c.shortname, 
                                            u.id, u.username, u.email, u.mailformat, u.idnumber, $names
                                    FROM {examregistrar_bookings} b
                                    JOIN {examregistrar_exams} e ON b.examid = e.id
                                    JOIN {course} c ON e.courseid = c.id AND c.visible = 1
                                    JOIN {user} u ON u.id = b.userid
                                    WHERE e.examregid = :examregid AND e.examsession = :session AND e.visible = 1
                                    GROUP BY b.examid, b.userid, b.booked
                                    ORDER BY b.userid ";
                            if($users = $DB->get_records_sql_menu($sql, array('examregid'=>$config->primaryreg, 'session'=>$session->id ))) {

                                mtrace("    ... doing reserved exam reminders.");
                                $sent = array();
                                $from = get_string('examreminderfrom',  'block_examswarnings');
                                $yesno = array(0=>get_string('no'), 1=>get_string('yes'));
                                $examdate = userdate($session->examdate, '%A %d de %B de %Y');
                                foreach($users as $user) {
                                    $subject = get_string('confirmsubject', 'block_examswarnings', $user->shortname);
                                    $message = $config->confirmmessage;
                                    list($name, $idnumber) = examregistrar_get_namecodefromid($user->bookedsite, 'locations');
                                    $replaces = array('%%course%%' => $user->shortname.'-'.$user->fullname,
                                                    '%%date%%' => $examdate,
                                                    '%%place%%' => $name,
                                                    '%%registered%%' => $yesno[$user->booked],
                                                    );
                                    foreach($replaces as $search => $replace) {
                                        $message = str_replace($search, $replace, $message);
                                    }
                                    $text = html_to_text($message, 75, false);
                                    $html = ($user->mailformat == 1) ? format_text($message, FORMAT_HTML) : '';
                                    $flag = '';
                                    if(!debugging('', DEBUG_DEVELOPER)) {
                                        if(!email_to_user($user, $from, $subject, $text, $html)) {
                                            $flag = ' - '.get_string('remindersenderror', 'block_examswarnings');
                                        }
                                    }
                                    $sent[] = $user->shortname.': '.fullname($user).$flag;
                                }
                                if(isset($config->controlemail) && $config->controlemail) {
                                    $info = new stdClass;
                                    $info->num = count($sent);
                                    $info->date = userdate($session->examdate, '%A %d de %B de %Y');
                                    list($sessionname, $idnumber) = examregistrar_item_getelement($session, 'examsession');
                                    $subject = get_string('confirmsubject', 'block_examswarnings', "$sessionanme ($idnumber)");
                                    $text = get_string('controlmailtxt',  'block_examswarnings', $info )."\n\n".implode("\n", $sent);
                                    foreach($controluser as $cu) {
                                        $html = ($cu->mailformat == 1) ? get_string('controlmailhtml',  'block_examswarnings', $info ).'<br />'.implode(' <br />', $sent) : '';
                                        email_to_user($cu, $from, $subject, $text, $html);
                                    }
                                    mtrace("    ... sent {$info->num} reserved exam reminders.");
                                }
                            }
                        }

                        // now add warnings
                        $days = $extra ? $config->warningdaysextra : $config->warningdays;
                        mtrace("...config->warningdays ". $days);
                        $start = strtotime("+{$days} days", $today) - DAYSECS;
                        $end = strtotime("+{$days} days", $today) + 10;
                        $lagtime = strtotime(" - $lagdays days ", $session->examdate);
                        mtrace("    ... NOT doing examwarnings after ".userdate($lagtime)." ".$lagtime);
                        if(($now < $lagtime) && ($session->examdate >= $start) && ($session->examdate < $end)) {
                            $examdate = userdate($session->examdate, '%A %d de %B de %Y');
                            if($exams = $DB->get_records_menu('examregistrar_exams', array('examregid'=>$config->primaryreg, 'examsession'=>$session->id), '', 'id, courseid')) {
                                mtrace('    ... there are '.count($exams).' exams on '.$examdate);
                                $params = array();
                                
                                $checkedroles = get_config('block_examswarnings', ' warningroles');
                                $checkedroles = explode(',', $checkedroles);

                                list($inrolesql, $inparams) = $DB->get_in_or_equal($checkedroles, SQL_PARAMS_NAMED, 'role');
                                $params = ($params + $inparams);

                                if(!$exams) {
                                    $exams = array(0);
                                }
                                list($incoursesql,$inparams) = $DB->get_in_or_equal($exams, SQL_PARAMS_NAMED, 'exam');
                                $params = ($params + $inparams);

                                $extrawhere = '';
                                if($extra) {
                                    $extrawhere = " AND NOT EXISTS (SELECT b2.id FROM {examregistrar_bookings} b2
                                                                                JOIN {examregistrar_exams} e2 ON b2.examid = e2.id 
                                                                                WHERE b2.booked = 1 AND b2.userid = u.id AND e2.period = e.period AND e2.courseid = e.courseid )";
                                }

                                $sql = "SELECT DISTINCT ra.id as rid, c.shortname, c.fullname, u.id, u.email, u.mailformat, u.username, u.idnumber, $names
                                        FROM {user} u
                                            JOIN {role_assignments} ra ON u.id = ra.userid
                                            JOIN {context} ctx ON ra.contextid = ctx.id
                                            JOIN {course} c ON ctx.instanceid = c.id AND c.visible = 1 AND c.id $incoursesql
                                            JOIN {grade_items} gi ON c.id = gi.courseid AND gi.itemtype = 'course'
                                            LEFT JOIN {grade_grades} gg ON gi.id = gg.itemid AND u.id = gg.userid
                                            JOIN {examregistrar_exams} e ON e.courseid = c.id AND e.examsession = :session AND e.examregid = :examregid AND e.callnum > 0
                                        WHERE ra.roleid $inrolesql AND (gg.finalgrade < gi.gradepass OR gg.finalgrade IS NULL)
                                            AND NOT EXISTS (SELECT 1 FROM {examregistrar_bookings} b WHERE b.userid = u.id AND b.examid = e.id)
                                            $extrawhere
                                        GROUP BY u.id, e.id";
                                $params['examregid'] = $config->primaryreg;
                                $params['session'] = $session->id;

                                $users = $DB->get_records_sql($sql, $params);
                                if($users) {
                                    mtrace("    ... doing NON-reserved exam warnings.");
                                    $sent = array();
                                    $from = get_string('examreminderfrom',  'block_examswarnings');
                                    foreach($users as $user) {
                                        $subject = get_string('warningsubject', 'block_examswarnings', $user->shortname);
                                        $message = $config->warningmessage;
                                        $replaces = array('%%course%%' => $user->shortname.'-'.$user->fullname,
                                                        '%%date%%' => $examdate,
                                                        );
                                        foreach($replaces as $search => $replace) {
                                            $message = str_replace($search, $replace, $message);
                                        }
                                        $text = html_to_text($message, 75, false);
                                        $html = ($user->mailformat == 1) ? format_text($message, FORMAT_HTML) : '';
                                        $flag = '';
                                        if(!debugging('', DEBUG_DEVELOPER)) {
                                            if(!email_to_user($user, $from, $subject, $text, $html)) {
                                                $flag = ' - '.get_string('remindersenderror', 'block_examswarnings');
                                            }
                                        }
                                        $sent[] = $user->shortname.': '.fullname($user).$flag;
                                    }
                                    if(isset($config->controlemail) && $config->controlemail) {
                                        $info = new stdClass;
                                        $info->num = count($sent);
                                        $info->date = $examdate;
                                        list($sessionname, $idnumber) = examregistrar_item_getelement($session, 'examsession');
                                        $subject = get_string('warningsubject', 'block_examswarnings', "$sessionname ($idnumber)");
                                        $text = get_string('controlmailtxt',  'block_examswarnings', $info)."\n\n".implode("\n", $sent);
                                        foreach($controluser as $cu) {
                                            $html = ($cu->mailformat == 1) ? get_string('controlmailhtml',  'block_examswarnings', $info ).'<br />'.implode(' <br />', $sent) : '';
                                            email_to_user($cu, $from, $subject, $text, $html);
                                        }
                                        mtrace("    ... sent {$info->num} non-reserved exam warnings.");
                                    }
                                }
                            }
                        }
                    }

                }
                set_config('lastexecution', $today, 'block_examswarnings'); /// Grab this execution as last one
            } // end of now > timetocheck
        }

        return $status;
    }

}


