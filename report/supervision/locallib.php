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
 * This file contains functions used by the log reports
 *
 * This files lists the functions that are used during the log report generation.
 *
 * @package    report_supervision
 * @copyright  2012 Enrique Castro at ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if (!defined('REPORT_LOG_MAX_DISPLAY')) {
    define('REPORT_LOG_MAX_DISPLAY', 150); // days
}

require_once(__DIR__.'/lib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/blocks/supervision/locallib.php');
require_once($CFG->dirroot.'/local/ulpgccore/lib.php');


/**
 * Get proper context for regular user or supervisor users
 *
 * @global stdClass $USER
 * @global moodle_database $DB
 * @param  stdClass $course course instance
 * @param  string   $scope category or department scope
 * @param  int      $itemid course category or department id
 * @return context object
 */
function report_supervision_get_context($course, $scope, $itemid) {
    global $DB, $USER;

    $context = context_course::instance($course->id);
    $canview = has_capability('report/supervision:view', $context);
    $cansupervise = false;
    if($items = supervision_get_supervised_items($USER->id, $scope)) {
        if(in_array($itemid, $items)) {
            $cansupervise = true;
        }
    }

    if($cansupervise && !$canview) {
        if($scope == 'category') {
            $context = context_coursecat::instance($itemid);
        }
        if($scope == 'department') {
            if($courses = enrol_get_all_users_courses($USER->id, false, 'id, idnumber')) {
                $courses = local_ulpgccore_load_courses_details(array_keys($courses), 'c.id, c.idnumber, uc.department, uc.credits, uc.term', 'c.idnumber ASC');
                foreach($courses as $dcourse) {
                    if($dcourse->department =  $itemid) {
                        $context = context_course::instance($dcourse->id);
                        break;
                    }
                }
            }
        }
    }

    return $context;
}



/**
 * This function is used to generate and display selector form
 *
 * @global stdClass $USER
 * @global stdClass $CFG
 * @global moodle_database $DB
 * @global core_renderer $OUTPUT
 * @global stdClass $SESSION
 * @uses CONTEXT_SYSTEM
 * @uses CONTEXT_COURSE
 * @param  stdClass $course course instance
 * @param  int      $selecteduser id of the selected user
 * @param  string   $selecteddate Date selected
 * @param  string   $modname course_module->id
 * @param  string   $modid number or 'site_errors'
 * @param  string   $modaction an action as recorded in the logs
 * @param  int      $selectedgroup Group to display
 * @param  int      $showcourses whether to show courses if we're over our limit.
 * @param  int      $showusers whether to show users if we're over our limit.
 * @param  string   $logformat Format of the logs (downloadascsv, showashtml, downloadasods, downloadasexcel)
 * @return void
 */
function report_supervision_print_selector_form($course, $selscope='category', $selitem=0, $seluser=0, $selfromdate=-1, $seltodate=0,
                                                    $selwarning=0, $seldisplay='current', $selsort='delay', $selperpage = 10, $logformat='showashtml') {
    global $USER, $CFG, $DB, $OUTPUT, $SESSION;

    /// first get permissions

    $course = local_ulpgccore_get_course_details($course);

    $context = report_supervision_get_context($course, $selscope, $selitem);
    $canview = has_capability('report/supervision:view', $context);
    $canmanage = has_capability('block/supervision:manage', $context);
    $canedit = has_capability('report/supervision:edit', $context);

    $viewfullnames = has_capability('moodle/site:viewfullnames', $context);

    $blockconfig = get_config('block_supervision');
    $itemmenu = array();
    $coursemenu = array();
    $usermenu = array();

    $courses = array();
    $itemmenu = array();
    if($canmanage) {
        if($selitem) {
            $courses = $DB->get_records('course', array($selscope=>$selitem), 'shortname ASC', 'id, shortname, category');
        } else {
            $courses = get_courses('all', 'c.shortname', 'c.id, c.shortname, c.category');
        }
        
    } else {
        $checkedroles = explode(',', $blockconfig->checkedroles);
        list($inrolesql, $roleparams) = $DB->get_in_or_equal($checkedroles, SQL_PARAMS_NAMED, 'role' );
        $sql = "SELECT c.id, ra.roleid, c.shortname, c.fullname, c.category, cc.name AS catname
                    FROM {role_assignments} ra
                    JOIN {context} ctx ON ra.contextid = ctx.id AND ctx.contextlevel = :contextlevel
                    JOIN {course} c ON c.id = ctx.instanceid AND ctx.contextlevel = :contextlevel2
                    JOIN {course_categories} cc ON c.category = cc.id
                    WHERE ra.userid = :user  AND ra.roleid $inrolesql GROUP BY c.id ORDER BY c.shortname ASC ";
        $params = array('user'=>$USER->id, 'contextlevel'=>CONTEXT_COURSE, 'contextlevel2'=>CONTEXT_COURSE );
        $courses = $DB->get_records_sql($sql, $params+$roleparams);
        foreach($courses as $cid=>$cc) {
                $itemmenu[$cc->category] = format_string($cc->catname);
        }
    }

    if(!$canmanage && !$canedit && $canview) {
        // regular user
        $scopes = array('category'=>get_string('bycategory', 'report_supervision'));
        $selscope = key($scopes);
        if($course->id !== SITEID) {
            $selitem = $course->category;
        }

        $users[$USER->id] = $USER;
        $seluser = $USER->id;
        $selcourse = $course->id;
    } else {
            /// TODO here manager & supervisors

        $scopes = array('category'=>get_string('bycategory', 'report_supervision'), 'department'=>get_string('bydepartment', 'report_supervision'));
        if(!$canmanage && $canedit) {
            // supervisor user
            $showscopes = array();
            $cat_items = supervision_get_supervised_items($USER->id, 'category');
            $dept_items = supervision_get_supervised_items($USER->id, 'department');
            if($cat_items) {
                $showscopes['category'] = 1;
            }
            if($dept_items) {
                $showscopes['department'] = 1;
            }
            $scopes = array_intersect_key($scopes, $showscopes);
            if(count($scopes) == 1) {
                $selscope = key($scopes);
            }

            if($selscope === 'category') {
                $table = 'course_categories';
                $field = 'name';
                $superviseditems = $cat_items;
            } else {
                $table = 'local_ulpgccore_units';
                $field = 'name';
                $superviseditems = $dept_items;
            }
            list($insql, $inparams) = $DB->get_in_or_equal($superviseditems);
            if($items = $DB->get_records_select($table, " id $insql ", $inparams, "$field ASC", "id, $field")) {
                foreach($items as $iid => $item) {
                    $itemmenu[$iid] = format_string($item->$field);
                }
            }
            //courses those in supervised items
            if($selitem) {
                $courses = $DB->get_records('course', array($selscope=>$selitem), 'shortname ASC', 'id, shortname, category');
            } else {
                $spcourses = $DB->get_records_select('course', " $selscope $insql ", $inparams, 'shortname ASC', 'id, shortname, category');
                //users those in courses, make after manager user
                $courses = $courses + $spcourses;
            }
            //ksort($courses);

            if(($course->id === SITEID) || ($course->credits == 0) || !in_array($course->id, array_keys($courses))) {
                $selcourse = 0;
            } else {
                $selcourse = $course->id;
            }

        } elseif($canmanage) {
            // manager user, can view all courses
            if(($selscope !== 'category') AND ($selscope !== 'department')) {
                $selscope = 'category';
            }

            if($selscope === 'department') {
                $itemmenu = $DB->get_records_menu('local_ulpgccore_units', array('type'=>'department'), 'name ASC', 'id, name');
            } else {
                $itemmenu = $DB->get_records_select_menu('course_categories', ' coursecount > ? ', array(0) , 'name ASC', 'id, name');
            }

            if($course->credits == 0) {
                $selcourse = 0;
            } else {
                $selcourse = $course->id;
            }
        } else {
            print_error('usercannotview', 'report_supervision');
        }
        //users those in courses, do here together for managers & supervisor users

        $checkedroles = explode(',', $blockconfig->checkedroles);
        list($inrolesql, $roleparams) = $DB->get_in_or_equal($checkedroles, SQL_PARAMS_NAMED, 'role' );
        list($incoursesql, $courseparams) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'course' );
        $names = get_all_user_name_fields(true, 'u');
        $sql = "SELECT ra.userid, ra.roleid, u.id, u.email, $names
                    FROM {role_assignments} ra
                    JOIN {context} ctx ON ra.contextid = ctx.id AND ctx.contextlevel = :contextlevel
                    JOIN {course} c ON c.id = ctx.instanceid AND ctx.contextlevel = :contextlevel2 AND c.id $incoursesql
                    JOIN {user} u ON ra.userid = u.id
                    WHERE ra.roleid $inrolesql GROUP BY ra.userid ORDER BY u.lastname ASC ";
        $params = array('contextlevel'=>CONTEXT_COURSE, 'contextlevel2'=>CONTEXT_COURSE );
        $users = $DB->get_records_sql($sql, $params+$courseparams+$roleparams);
        /// TODO get users by course and checkedroles
    }

    $allitem = array(0=>get_string('all_categories', 'report_supervision'));
    if($selitem === 'department') {
        $allitem = array(0=>get_string('all_departments', 'report_supervision'));
    }
    asort($itemmenu);
    if(count($itemmenu) > 1 ) {
        $itemmenu = $allitem + $itemmenu;
    }

        // finally, construct the menu
    unset($courses[SITEID]);
    foreach ($courses as $cid => $cc) {
        $coursemenu[$cid] = $cc->shortname; //format_string($cc->shortname);
    }
    if(count($coursemenu) > 1 ) {
        $coursemenu = array(0=>get_string('all_courses', 'report_supervision')) + $coursemenu;
    }

    // finally, construct the menu
    foreach ($users as $uid => $user) {
        $usermenu[$uid] = fullname($user, $viewfullnames, 'lastname firstname');
    }
    if(count($usermenu) > 1 ) {
        $usermenu = array(0=>get_string('allparticipants')) + $usermenu;
    }

    $starttimelist = array( 0 => get_string('timestart', 'report_supervision'),
                       -1 => get_string('defaulttimestart', 'report_supervision'),
                       1*86400*7 => get_string('weekbefore', 'report_supervision', 1),
                       2*86400*7 => get_string('weekbefore', 'report_supervision', 2),
                       3*86400*7 => get_string('weekbefore', 'report_supervision', 3),
                       1*86400*7*4 => get_string('monthbefore', 'report_supervision', 1),
                       2*86400*7*4 => get_string('monthbefore', 'report_supervision', 2),
                       3*86400*7*4 => get_string('monthbefore', 'report_supervision', 3),
                       4*86400*7*4 => get_string('monthbefore', 'report_supervision', 4),
                       5*86400*7*4 => get_string('monthbefore', 'report_supervision', 5),
                       6*86400*7*4 => get_string('monthbefore', 'report_supervision', 6),
                );

    $endtimelist = array( 0 => get_string('now'),
                       1*86400*7 => get_string('weekbefore', 'report_supervision', 1),
                       2*86400*7 => get_string('weekbefore', 'report_supervision', 2),
                       3*86400*7 => get_string('weekbefore', 'report_supervision', 3),
                       1*86400*7*4 => get_string('monthbefore', 'report_supervision', 1),
                       2*86400*7*4 => get_string('monthbefore', 'report_supervision', 2),
                       3*86400*7*4 => get_string('monthbefore', 'report_supervision', 3),
                       4*86400*7*4 => get_string('monthbefore', 'report_supervision', 4),
                       5*86400*7*4 => get_string('monthbefore', 'report_supervision', 5),
                       6*86400*7*4 => get_string('monthbefore', 'report_supervision', 6),
                       -1 => get_string('defaulttimestart', 'report_supervision'),
                );

    $perpagelist = array( 0 => get_string('all'),
                       10 => get_string('itemsperpage', 'report_supervision', 10),
                       25 => get_string('itemsperpage', 'report_supervision', 25),
                       50 => get_string('itemsperpage', 'report_supervision', 50),
                       100 => get_string('itemsperpage', 'report_supervision', 100),
                       200 => get_string('itemsperpage', 'report_supervision', 200),
                );

    $warnings = get_plugin_list('supervisionwarning');
    foreach($warnings as $name => $path) {
        $warnings[$name] = get_string('pluginname', 'supervisionwarning_'.$name);
    }
    if($canedit && !$canmanage) { // is a supervisor, limit visibility (managers and regular users see all, but regular users only their own ones)
        $showwarnings = supervision_supervisor_warningtypes($USER->id);
        $showwarnings = array_combine($showwarnings, $showwarnings);
        $warnings = array_intersect_key($warnings,$showwarnings);
    }
    if(count($warnings) > 1 ) {
        $warnings = array(0=>get_string('all_warnings', 'report_supervision')) + $warnings;
    }

    $displays = array();
    $displays['all'] = get_string('all_displays', 'report_supervision');
    $displays['current'] = get_string('current', 'report_supervision');
    $displays['fixed'] = get_string('fixed', 'report_supervision');
    $displays['nulled'] = get_string('nulled', 'report_supervision');

    $sortoptions = array();
    $sortoptions['delay'] = get_string('sort_delay', 'report_supervision');
    $sortoptions['timecreated'] = get_string('sort_timecreated', 'report_supervision');
    $sortoptions['timefixed'] = get_string('sort_timefixed', 'report_supervision');
    $sortoptions['shortname'] = get_string('sort_course', 'report_supervision');
    $sortoptions['lastname'] = get_string('sort_user', 'report_supervision');

    /// Now print the forms

    echo '<div class="supervisedscopeform">';
    $url = new moodle_url('/report/supervision/index.php', array('id'=>$selcourse, 'item'=>$selitem, 'user'=>$seluser, 'warning'=>$selwarning, 'sort'=>$selsort));
    $select = new single_select($url, 'scope', $scopes, $selscope,  null, 'scopeform');
    //$select->set_label($label.':&nbsp;');
    $select->method = 'get';
    echo $OUTPUT->render($select);
    echo '</div>';

    echo "<form class=\"logselectform\" action=\"$CFG->wwwroot/report/supervision/index.php\" method=\"get\">\n";
    echo "<div>\n";
    echo "<input type=\"hidden\" name=\"chooselog\" value=\"1\" />\n";
    echo "<input type=\"hidden\" name=\"scope\" value=\"$selscope\" />\n";

    //echo html_writer::select($scopes,"scope",$selscope, false);
    echo html_writer::select($itemmenu,"item",$selitem, false);
    echo html_writer::select($coursemenu,"id",$selcourse, false);
    echo html_writer::select($usermenu, "user", $seluser, false);
    echo '<br />';
    echo html_writer::select($starttimelist, "fromdate", $selfromdate, false);
    echo html_writer::select($endtimelist, "todate", $seltodate, false);
    echo html_writer::select($perpagelist, "perpage", $selperpage, false);
    echo '<br />';
    echo html_writer::select($warnings, "warning", $selwarning, false);
    echo html_writer::select($displays, "display", $seldisplay, false);
    echo html_writer::select($sortoptions, "sort", $selsort, false);

    $logformats = array('showashtml' => get_string('displayonpage'),
                        'downloadascsv' => get_string('downloadtext'),
                        'downloadasods' => get_string('downloadods'),
                        'downloadasexcel' => get_string('downloadexcel'));
    $logformats = array('showashtml' => get_string('displayonpage'));
    echo html_writer::select($logformats, 'logformat', $logformat, false);
    echo '<br />';
    echo '<br />';
    echo '<input type="submit" value="'.get_string('gettheselogs').'" />';
    echo '</div>';
    echo '</form>';
}


/**
 * Prints the selected portion of supervision_warnings table as HTML table
 *
 * @global stdClass $USER
 * @global stdClass $CFG
 * @global moodle_database $DB
 * @global core_renderer $OUTPUT
 * @global stdClass $SESSION
 * @uses CONTEXT_SYSTEM
 * @uses COURSE_MAX_COURSES_PER_DROPDOWN
 * @uses CONTEXT_COURSE
 * @uses SEPARATEGROUPS
 * @param  stdClass $course course instance
 * @param  string   $scope category or department scope
 * @param  int      $itemid course category or department id
 * @param  int      $userid ID
 * @param  int      $fromdate
 * @param  int      $todate
 * @param  string   $warningtype
 * @param  string   $display
 * @param  string   $sort
 * @param  int      $page
 * @param  int      $perpage
 * @return void
 */

function report_supervision_lookup_warnings($course, $context, $scope, $itemid,  $userid, $fromdate, $todate, $warningtype, $display) {
    global $DB, $USER;

    $canview = has_capability('report/supervision:view', $context);
    $canmanage = has_capability('block/supervision:manage', $context);
    $canedit = has_capability('report/supervision:edit', $context);

    $now = time();
    $timetocheck = usergetmidnight($now);
    $blockconfig = get_config('block_supervision');

    $courses = array();

    if($course->id !== SITEID) { // there is a course selected, we need no more
        $courses[$course->id] = $course->id;
    } else {
        /// All courses, courses selected depends on permissions to view/supervise courses by  current user
        if(!$canmanage && !$canedit && $canview) {
                // regular user
                if($course->id !== SITEID) {
                    $courses[$course->id] = $course->id;
                }
                // all courses a regular user is assigned with one of the checked roles
                $checkedroles = explode(',', $blockconfig->checkedroles);
                list($inrolesql, $roleparams) = $DB->get_in_or_equal($checkedroles, SQL_PARAMS_NAMED, 'role' );
                $sql = "SELECT c.id, ra.roleid
                            FROM {role_assignments} ra
                            JOIN {context} ctx ON ra.contextid = ctx.id AND ctx.contextlevel = :contextlevel
                            JOIN {course} c ON c.id = ctx.instanceid AND ctx.contextlevel = :contextlevel2
                            WHERE ra.userid = :user  AND ra.roleid $inrolesql GROUP BY c.id  ";
                $params = array('user'=>$USER->id, 'contextlevel'=>CONTEXT_COURSE, 'contextlevel2'=>CONTEXT_COURSE );
                $courses = $DB->get_records_sql($sql, $params+$roleparams);
        } else {
            if(!$canmanage && $canedit) {
                if(!$itemid) {
                    $superviseditems = supervision_get_supervised_items($USER->id, $scope);
                } else {
                    $superviseditems = array($itemid);
                }
                list($insql, $inparams) = $DB->get_in_or_equal($superviseditems);
                $courses = $DB->get_records_select('course', " $scope $insql ", $inparams, '', 'id, shortname, category');
            } else {
                //manager user see all by scope & item
                if($itemid) {
                    $courses = $DB->get_records('course', array($scope=>$itemid), '', 'id, shortname, category');
                } else {
                    $courses = array(); // all courses better set by NOT searching by course in query below
                }
            }
        }
    }

    $params = array();

    $wherecourse = '';
    if($courses) {
        list($incoursesql, $courseparams) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'course_' );
        $wherecourse = " AND sw.courseid $incoursesql ";
        $params = $params + $courseparams;
    }

    $whereuser = '';
    if($userid) {
        $whereuser = " AND sw.userid = :user ";
        $params['user'] = $userid;
    }

    $wheretime = " AND sw.timecreated >= :fromdate AND sw.timecreated < :todate ";
    $params['timenow'] = $now;
    switch ($fromdate) {
        case 0  :   $params['fromdate'] = 0;
                        break;
        case -1 :   $params['fromdate'] = strtotime($blockconfig->startdisplay);
                        break;
        default :   $params['fromdate'] = $timetocheck - $fromdate;
                        break;
    }
    switch ($todate) {
        case 0  :   $params['todate'] = $now;
                        break;
        case -1 :   $params['todate'] = strftime($blockconfig->startdisplay);
                        break;
        default :   $params['todate'] = $timetocheck - $todate;
                        break;
    }


    $wherewarning = '';
    if($warningtype) {
        $wherewarning = " AND sw.warningtype = :warningtype ";
        $params['warningtype'] = $warningtype;
    }

    $wheredisplay = '';
    switch ($display) {
        case 'current' : $wheredisplay = " AND sw.timefixed = 0 ";
                        break;
        case 'fixed' : $wheredisplay = " AND sw.timefixed > 0 ";
                        break;
        case 'nulled' : $wheredisplay = " AND sw.timefixed = (-1) ";
                        break;
    }

    $sql = "FROM {supervision_warnings} sw
            JOIN {course} c ON sw.courseid = c.id
            JOIN {user} u ON sw.userid = u.id
            WHERE 1 $wherecourse $whereuser $wheretime $wherewarning $wheredisplay ";

    return array($sql, $params);
}


/**
 * Prints the selected portion of supervision_warnings table as HTML table
 *
 * @global stdClass $USER
 * @global stdClass $CFG
 * @global moodle_database $DB
 * @global core_renderer $OUTPUT
 * @global stdClass $SESSION
 * @uses CONTEXT_SYSTEM
 * @uses COURSE_MAX_COURSES_PER_DROPDOWN
 * @uses CONTEXT_COURSE
 * @uses SEPARATEGROUPS
 * @param  stdClass $course course instance
 * @param  string   $scope category or department scope
 * @param  int      $itemid course category or department id
 * @param  int      $userid ID
 * @param  int      $fromdate
 * @param  int      $todate
 * @param  string   $warningtype
 * @param  string   $display
 * @param  string   $sort
 * @param  int      $page
 * @param  int      $perpage
 * @return void
 */
function report_supervision_print_warnings($course, $scope, $itemid,  $userid, $fromdate, $todate, $warningtype, $display, $sortorder, $page, $perpage) {
    global $DB, $OUTPUT, $USER;

    $baseurl = new moodle_url('/report/supervision/index.php', array('id'=>$course->id,
                                                                        'scope'=>$scope,
                                                                        'item'=>$itemid,
                                                                        'user'=>$userid,
                                                                        'fromdate'=>$fromdate,
                                                                        'todate'=>$todate,
                                                                        'warning'=>$warningtype,
                                                                        'display'=>$display,
                                                                        'sort'=>$sortorder,
                                                                        'page'=>$page,
                                                                        'perpage'=>$perpage,
                                                                        'chooselog'=>1,
                                                                        ));

    $context = report_supervision_get_context($course, $scope, $itemid);
    $canview = has_capability('report/supervision:view', $context);
    $canmanage = has_capability('block/supervision:manage', $context);
    $canedit = has_capability('report/supervision:edit', $context);
    $now = time();


    $names = get_all_user_name_fields(true, 'u');
    $select = "SELECT sw.*, (IF(sw.timefixed = 0, :timenow, ABS(sw.timefixed)) - sw.timecreated) AS delay, c.category, c.shortname, c.fullname, u.email, $names ";
    list($sql, $params) = report_supervision_lookup_warnings($course, $context, $scope, $itemid,  $userid, $fromdate, $todate, $warningtype, $display);

    /// Define a table showing a list of items (categories, departments) and users with supervision permissions in them

    $table = new flexible_table('report-supervision-warnings-'.$course->id);

    // we use names of colums from supervision_warnings table
    $tablecolumns = array('shortname', 'warningtype', 'userid', 'activityinfo', 'studentid',  'timecreated', 'delay', 'timefixed', 'comment', 'action');
    $tableheaders = array(get_string('course'),
                            get_string('warningtype', 'report_supervision'),
                            get_string('courseteacher', 'report_supervision'),
                            get_string('activityinfo', 'report_supervision'),
                            get_string('defaultcoursestudent'),
                            get_string('timecreated', 'report_supervision'),
                            get_string('timedelay', 'report_supervision'),
                            get_string('timefixed', 'report_supervision'),
                            get_string('comment', 'report_supervision'),
                            get_string('action'));
    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($baseurl->out());

    $table->sortable(true, 'delay', SORT_DESC);
    $table->no_sorting('studentid');
    $table->no_sorting('activityinfo');
    $table->no_sorting('action');

    $table->set_attribute('id', 'supervision-warnings-table');
    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('class', 'generaltable generalbox warningstable');
    //$table->set_attribute('style', 'overflow:auto;');

    $table->setup();

    $totalcount = $DB->count_records_sql("SELECT COUNT(sw.id) $sql ", $params);
    if($perpage === 0) {
        $perpage = $totalcount;
    }

    $table->initialbars(false);
    $table->pagesize($perpage, $totalcount);

    if ($table->get_sql_sort()) {
        $sort = ' ORDER BY '.$table->get_sql_sort();
    } else {
        $direction = "ASC" ;
        if($sortorder == 'delay')  {
            $direction = "DESC" ;
        }
        $sort = " ORDER BY $sortorder $direction " ;
    }
    $warnings = $DB->get_records_sql($select.$sql.$sort, $params, $page, $perpage);

    $stredit   = get_string('edit');
    $names = get_all_user_name_fields(true);
    if($warnings) {
        echo $OUTPUT->heading(get_string('displaywarnings', 'report_supervision', $totalcount));
        foreach($warnings as $warning) {
            $data = array();
            $url = new moodle_url('/course/view.php', array('id'=>$warning->courseid));
            $data[] = $OUTPUT->action_link($url, $warning->shortname).'<br />'.format_string($warning->fullname);
            $data[] = get_string('pluginname', 'supervisionwarning_'.$warning->warningtype);

            $url = new moodle_url('/user/view.php', array('id'=>$warning->userid, 'course'=>$warning->courseid));
            $data[] = $OUTPUT->action_link($url, fullname($warning, false, 'lastname firstname'));
            $data[] = $OUTPUT->action_link($warning->url, $warning->info);

            if($student = $DB->get_record('user', array('id'=>$warning->studentid), "id, $names ")) {
                $url = new moodle_url('/user/view.php', array('id'=>$warning->studentid, 'course'=>$warning->courseid));
                $data[] = $OUTPUT->action_link($url, fullname($student, false, 'lastname firstname'));
            } else {
                $data[] = '';
            }
            $data[] = userdate($warning->timecreated);
            $data[] = format_time($warning->delay);
            if($warning->timefixed) {
                $fixdate = userdate(abs($warning->timefixed));
                if($warning->timefixed < 0) {
                    $fixdate .= '<br />'.get_string('nulled', 'report_supervision');
                }
                $data[] = $fixdate;
            } else {
                $data[] = '-';
            }
            if($warning->comment) {
                $data[] = format_text($warning->comment);
            } else {
                $data[] = '';
            }
            $action = '';
            $buttons = array();
            if($canmanage or $canedit) {
                $url = new moodle_url($baseurl, array('edit'=>$warning->id));
                $buttons[] = html_writer::link($url, html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'), 'alt'=>$stredit, 'class'=>'iconsmall')), array('title'=>$stredit));
                //$url = new moodle_url($baseurl, $baseparams + array('del'=>$supervision->id));
                //$buttons[] = html_writer::link($url, html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'), 'alt'=>$strdelete, 'class'=>'iconsmall')), array('title'=>$strdelete));
                //$buttons[] = html_writer::link(new moodle_url($returnurl, array('delete'=>$user->id, 'sesskey'=>sesskey())), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'), 'alt'=>$strdelete, 'class'=>'iconsmall')), array('title'=>$strdelete));
                $action = implode('&nbsp;&nbsp;', $buttons);
            }
            $data[] = $action;


            $table->add_data($data);
        }
        $table->print_html();
    } else {
        echo $OUTPUT->heading(get_string('nothingtodisplay'));
    }

    if($canedit) {
        echo '<br />';
        $baseurl->param('action', 'fixall');
        echo $OUTPUT->action_link($baseurl, get_string('fixall', 'report_supervision'));
    }

    if($canmanage) {
        echo '<br />';
        $baseurl->param('action', 'nullall');
        echo $OUTPUT->action_link($baseurl, get_string('nullall', 'report_supervision'));

        echo '<br />';
        $baseurl->param('action', 'recalculate');
        echo $OUTPUT->action_link($baseurl, get_string('recalculate', 'report_supervision'));
    }

    echo '<br />';
    $url = new moodle_url('/course/view.php', array('id' => $course->id));
    echo $OUTPUT->continue_button($url);
}


/**
 * Recalculate selected warnings taking account of new holidays settings
 *
 * @global stdClass $USER
 * @global stdClass $CFG
 * @global moodle_database $DB
 * @global core_renderer $OUTPUT
 * @global stdClass $SESSION
 * @uses CONTEXT_SYSTEM
 * @uses COURSE_MAX_COURSES_PER_DROPDOWN
 * @uses CONTEXT_COURSE
 * @uses SEPARATEGROUPS
 * @param  stdClass $course course instance
 * @param  string   $scope category or department scope
 * @param  int      $itemid course category or department id
 * @param  int      $userid ID
 * @param  int      $fromdate
 * @param  int      $todate
 * @param  string   $warningtype
 * @param  string   $display
 * @return void
 */
function report_supervision_recalculate_warnings($course, $scope, $itemid,  $userid, $fromdate, $todate, $warningtype, $display) {
    global $CFG, $DB, $OUTPUT, $USER;

    include_once($CFG->dirroot.'/local/supervision/supervisionwarning.php');

    $context = context_course::instance($course->id);

    require_capability('block/supervision:manage', $context);

    $select = "SELECT sw.*, (IF(sw.timefixed = 0, :timenow, ABS(sw.timefixed)) - sw.timecreated) AS delay, c.category, c.shortname ";
    list($sql, $params) = report_supervision_lookup_warnings($course, $context, $scope, $itemid,  $userid, $fromdate, $todate, $warningtype, $display);

    $delete = array();
    $now = time();

    if($warnings = $DB->get_recordset_sql($select.$sql, $params)) {
        foreach($warnings as $warning) {
            $wconfig = get_config('supervisionwarning_'.$warning->warningtype);
            $weekends = true;
            if($warning->warningtype == 'ungraded_assign') {
                $weekends = false;
            }
            
            if(!$warning->timereference) {
                $warning->timereference = supervisionwarning::threshold_without_holidays($warning->timecreated,$wconfig->threshold-1, false, $weekends);
                $DB->set_field('supervision_warnings', 'timereference', $warning->timereference, array('id'=>$warning->id));
            }
            
            $timelimit = supervisionwarning::threshold_without_holidays($warning->timereference,$wconfig->threshold, true, $weekends);

            if($warning->timefixed != 0) {
                $timefixed = abs($warning->timefixed);
            } else {
                $timefixed = $now;
            }

            if($timelimit >= $timefixed) {
                $delete[] = $warning->id;
            }

            if(($timelimit != $warning->timecreated) && (!isset($delete[$warning->id]))) {
                $DB->set_field('supervision_warnings', 'timecreated', $timelimit, array('id'=>$warning->id));
            }

        }
        $warnings->close();

        if($delete) {
            $DB->delete_records_list('supervision_warnings', 'id', $delete);
        }
    }

    return;
}


/**
 * Fix selected warnings
 *
 * @global stdClass $USER
 * @global stdClass $CFG
 * @global moodle_database $DB
 * @global core_renderer $OUTPUT
 * @global stdClass $SESSION
 * @uses CONTEXT_SYSTEM
 * @uses COURSE_MAX_COURSES_PER_DROPDOWN
 * @uses CONTEXT_COURSE
 * @uses SEPARATEGROUPS
 * @param  stdClass $course course instance
 * @param  string   $scope category or department scope
 * @param  int      $itemid course category or department id
 * @param  int      $userid ID
 * @param  int      $fromdate
 * @param  int      $todate
 * @param  string   $warningtype
 * @param  string   $display
 * @return void
 */
function report_supervision_fixall_warnings($timefixed, $course, $scope, $itemid,  $userid, $fromdate, $todate, $warningtype, $display) {
    global $CFG, $DB, $OUTPUT, $USER;

    include_once($CFG->dirroot.'/local/supervision/supervisionwarning.php');

    $context = context_course::instance($course->id);

    require_capability('block/supervision:manage', $context);

    $select = "SELECT sw.* ";
    list($sql, $params) = report_supervision_lookup_warnings($course, $context, $scope, $itemid,  $userid, $fromdate, $todate, $warningtype, $display);

    $update = array();

    if($warnings = $DB->get_recordset_sql($select.$sql, $params)) {
        foreach($warnings as $warning) {
            if($timefixed > 0) {
                if($warning->timefixed == 0) {
                    $update[] = $warning->id;
                }
            } else {
                if($warning->timefixed >= 0) {
                    $update[] = $warning->id;
                }
            }
        }
        $warnings->close();

        if($update) {
            list($insql, $params) = $DB->get_in_or_equal($update);
            $select = " id $insql ";
            $DB->set_field_select('supervision_warnings', 'timefixed', $timefixed, $select, $params);
        }
    }

    return;
}



