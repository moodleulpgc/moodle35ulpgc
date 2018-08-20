<?php
/**
 * This file contains local_supervision main local library functions
 *
 * @package   local_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



    /**
     * Load the plugins from the warning sub folders
     * @param string/array $type - any subfollder name, all if leaved null
     * @param bool $class - if true return classnames
     * @return array - The sorted list of plugins or pluginclasses names
     */
    function supervision_load_plugins($type=null, $class=false) {
        global $CFG;

        $result = array();
        $names = array();
        if($type) {
            if(is_string($type)) {
                $names = array($type => '/local/supervision/warning/'.$type);
            } elseif(is_array($type)) {
                    foreach($type as $t) {
                        $names = array($t => '/local/supervision/warning/'.$t);
                    }
            }
        } else {
            $names = get_plugin_list('supervisionwarning');
        }

        foreach ($names as $name => $path) {
            if (file_exists($path . '/locallib.php')) {
                require_once($path . '/locallib.php');
                $pluginclass = 'supervisionwarning_' . $name;
                if($class)
                    $plugin = $pluginclass;
                else
                    $plugin = new $pluginclass();
                $result[$name] = $plugin;
            }
        }

        ksort($result);
        return $result;
    }


    /**
     * Get from permissions table the Course categories the user can review courses
     *
     * @param int $userid User id
     * @return array categories ids
     */
    function supervision_get_reviewed_itemnames($userid, $scope='category') {
        global $DB;
        $items = array();

        $select = "SELECT sp.id, sp.scope, sp.instance, sp.userid, sp.review ";
        $from = " FROM {supervision_permissions} sp ";
        if($scope == 'category') {
            $select .= ", cc.name AS itemname ";
            $from .= "\n JOIN {course_categories} cc ON sp.instance = cc.id ";
        } elseif($scope == 'department') {
            $select .= ", u.name AS itemname ";
            $from .= "\n JOIN {local_sinculpgc_units} u ON sp.instance = u.id AND u.type = '".SINCULPGC_UNITS_DEPARTMENT."'";
        } else {
            $select .= ", sp.instance AS itemname ";
        }
        $where = " WHERE sp.scope = :scope AND sp.userid = :userid AND review = :review ";
        $params = array('scope'=>$scope, 'userid'=>$userid, 'review'=>1);

        if($reviews = $DB->get_records_sql("$select $from $where ORDER by sp.scope ASC, itemname ASC ", $params)) {
            foreach($reviews as $review) {
                $items[$review->instance] = $review->itemname;
            }
        }
        return $items;
    }

    /**
     * Get from permissions table the category/departments the user can review courses in
     *
     * @param int $userid User id
     * @return array item instance ids
     */
    function supervision_get_reviewed_items($userid, $scope='category') {
        global $DB;
        $items = array();
        if($reviews = $DB->get_records('supervision_permissions', array('scope'=>$scope, 'userid'=>$userid, 'review'=>1), '', 'id, scope, instance')) {
            foreach($reviews as $review) {
                $items[$review->instance] = $review->instance;
            }
        }
        return $items;
    }




    /**
     * Get from permissions table the Course categories/departments the user can supervise warnings
     *
     * @param int $userid User id
     * @return array categories ids
     */
    function supervision_get_supervised_items($userid, $scope='category') {
        global $DB;
        $items = array();
        $select = " scope = :scope AND userid = :userid AND ".$DB->sql_isnotempty('supervision_permissions', 'warnings', true, false);
        if($reviews = $DB->get_records_select('supervision_permissions', $select, array('scope'=>$scope, 'userid'=>$userid), '', 'id, scope, instance')) {
            foreach($reviews as $review) {
                $items[$review->instance] = $review->instance;
            }
        }
        return $items;
    }

    /**
     * Get from permissions table the warning types a supervisor can see
     *
     * @param int $userid User id
     * @return array warnintypes
     */
    function supervision_supervisor_warningtypes($userid) {
        global $DB;
        $items = array();
        $select = " userid = :userid AND ".$DB->sql_isnotempty('supervision_permissions', 'warnings', true, false);
        if($reviews = $DB->get_records_select('supervision_permissions', $select, array('userid'=>$userid), '', 'id, scope, instance, warnings')) {
            foreach($reviews as $review) {
                $warnings = array();
                if($warningtypes = explode(',', $review->warnings)) {
                    foreach($warningtypes as $warning) {
                        if(get_config('supervisionwarning_'.$warning, 'enabled')) {
                            $warnings[] = $warning;
                        }
                    }
                }

                if($warnings && is_array($warnings)) {
                    $items = $items + $warnings;
                }
            }
            if($items = array_unique($items)) {
                natcasesort($items);
            }
        }
        return $items;
    }

    /**
     * Get from warnings table the warning types that have instances for a regular user
     *
     * @param int $userid User id
     * @return array warningtypes
     */
    function supervision_user_haswarnings($userid, $courseid=0, $fixed=false) {
        global $DB;
        $items = array();

        $params = array('user'=>$userid);

        $coursewhere = '';
        if($courseid) {
            $coursewhere = ' AND courseid = :course';
            $params['course'] = $courseid;
        }

        $fixedwhere = '';
        if(!$fixed) {
            $fixedwhere = ' AND timefixed = 0 ';
        } elseif($fixed === true) {
            $fixedwhere = ' AND timefixed != 0 ';
        }

        $sql = "SELECT warningtype
                    FROM {supervision_warnings}
                    WHERE userid = :user $coursewhere $fixedwhere  GROUP BY warningtype ORDER BY warningtype ASC";
        $items = $DB->get_records_sql($sql, $params);
        return array_keys($items);
    }

    /**
     * Checks is user is a en editing supervisor and return instances supervised
     *
     * @param int $userid User id
     * @return int userid of assigner
     */
    function supervision_can_add_supervisors($userid) {
        global $DB;
        $assigner = false;
        if(!$userid) {
            return false;
        }

        if($permissions = $DB->get_records('supervision_permissions', array('userid' => $userid, 'adduser' => 1), '', 'id, scope, instance')) {
            $permissions['user'] = $userid;
        }

        return $permissions;
    }


    /**
     * Takes a supervision permission object and perform role assignment in category/department
     *
     * @param object $data supervision_permissions data object
     * @param bool $delete if role assignment shoudl be deleted (unassigned)
     * @return bool success
     */
    function supervision_assign_supervisor($data, $delete = false) {
        global $DB;

        $success = false;
        $items = array();
        $supervisor_role = get_config('local_supervision', 'checkerrole');

        if($DB->record_exists('role', array('id'=>$supervisor_role))) {
            if($data->scope == 'category') {
                $context = context_coursecat::instance($data->instance);
                $items[$data->instance] = $context->id;
            } elseif($data->scope == 'department') {
                // get all courses for that department and do again
                if($courses = $DB->get_records('course', array('department'=>$data->instance), '', 'id, shortname')) {
                    foreach($courses as $course) {
                        $context = context_course::instance($course->id);
                        $items[$course->id] = $context->id;
                    }
                }
            }
            foreach($items as $item) {
                if($data->review AND !$delete) {
                    role_assign($supervisor_role, $data->userid, $item, 'local_supervision' );
                } elseif(!$data->review OR $delete)  {
                    role_unassign($supervisor_role, $data->userid, $item, 'local_supervision' );
                }
                $success = true;
            }
        }
        return $success;
    }


    /**
     * Look for name of some permission category/department instance
     *
     * @param object $permission object as in supervision_permissions table
     * @return string formatted name
     */
    function supervision_format_instancename($permission=null) {
        global $DB;
        if(!$permission) {
            return '';
        }
        if($permission->scope == 'category') {
            return $DB->get_field('course_categories', 'name', array('id'=>$permission->instance));
        }

        if($permission->scope == 'department') {
            return $DB->get_field('local_sinculpgc_units', 'name', array('id'=>$permission->instance));
        }

        return '';
    }

    /**
     * Called by cron to load 'live' unfixed warnings and send supervision  emails to supervised users
     * Depends on each userid is a teacher fairly assigned (same group as student, enrolled etc.)
     *
     * @param int $timetocheck starting time for collection or warnings
     * @param int $lastexecution last time this routine was launched by cron
     * @return a moodle icon object
     */
    function supervision_warnings_mailing($timetocheck, $lasttime) {
        global $CFG, $DB, $USER;

        $config = get_config('local_supervision');

        /// last step: mail warnings if needed
        /// TODO count of failures for each course, user;  JOIN user and course???
        if(empty($config->enablemail)) {
                    return true;
        }
        $maillimit = $timetocheck;
        if(isset($config->maildelay)) { // introduces a delay before sending e-mails
            $maillimit = strtotime('-'.$config->maildelay.' day', $timetocheck);
        }

        if(empty($config->enablemail)) {
            mtrace('no pending stats mailing');
            return true;
        }

        $names = get_all_user_name_fields(true, 'u');
        $sql = "SELECT sw.*, c.fullname, c.shortname, c.category,
                        u.email, u.emailstop, u.mailformat, u.maildisplay, $names
                    FROM {supervision_warnings} sw
                    JOIN {course} c ON sw.courseid = c.id
                    JOIN {user} u ON u.id = sw.userid
                    WHERE (timefixed = 0)   AND (timemailed < :maillimit) ";
        $params = array('maillimit'=>$maillimit);
        if ($rs = $DB->get_recordset_sql($sql, $params)) {
            $from = get_string('warningautomatic',  'local_supervision');
            $supportuser = '';
            if(isset($config->email) && $config->email) {
                $supportuser = core_user::get_support_user();
                $supportuser->email = $config->email;
                $supportuser->mailformat = 1;
                $supportuser->id = 1;
            }

            $names = get_all_user_name_fields(true, '');
            foreach ($rs as $stat) {
                $info = new StdClass;
                $info->coursename = $stat->shortname.' - '.format_string($stat->fullname);
                $info->reporturl = $CFG->wwwroot.'/blocks/supervision/report.php?course='.$stat->courseid;
                $info->courseurl = $CFG->wwwroot.'/course/view.php?id='.$stat->courseid;
                $info->activity = $stat->info;
                $info->itemlink = $CFG->wwwroot.$stat->url;
                $info->student = '';
                if($stat->studentid) {
                    $st = $DB->get_record('user', array('id'=>$stat->studentid), 'id, username, idnumber, email, '.$names);
                    $st->fullname = fullname($st);
                    $info->student = get_string('emailstudent',  'local_supervision', $st );
                }

                $subject = get_string('warningmailsubject', 'local_supervision', $stat->shortname);
                $text = get_string('warningemailtxt',  'local_supervision', $info );
                $html = ($stat->mailformat == 1) ? get_string('warningemailhtml',  'local_supervision', $info ) : '';

                $classname = 'supervisionwarning_'.$stat->warningtype;
                $warning = new $classname($stat);

                if(!$stat->userid) {
                    /// TODO debug measure, eliminate
                    if($supportuser) {
                        email_to_user($supportuser, $from, "[Error userid=0] ".$subject, $text, $html);
                    }
                } elseif(email_to_user($stat, $from, $subject, $text, $html)) {
                    $DB->set_field('supervision_warnings', 'timemailed', $timetocheck, array('id'=>$stat->id));
                    if($supportuser) {
                        email_to_user($supportuser, $from, $subject, $text, $html);
                    }
                    if(!empty($config->coordemail)) {
                        $coords = $warning->get_supervisors();
                        if($coords) {
                            foreach($coords as $coorduser) {
                                email_to_user($coorduser, $from, $subject, $text, $html);
                            }
                        }
                    }
                }
            }
            $rs->close();
        }
    }


    /**
     * Look in table centros/departamentos and synchronizes department supervisors
     *
     * @param string $scope category/department
     * @return void
     */
    function supervision_ulpgc_update_supervisors($scope='category') {
        global $DB;

        $plugins = get_plugin_list('supervisionwarning');
        $warnings = implode(',', array_keys($plugins));

        $ulpgc_supervisors = array();
        if($scope === 'department') {
            $ulpgc_supervisors = supervision_ulpgc_dept_supervisors();
        } else {
            $ulpgc_supervisors = supervision_ulpgc_faculty_supervisors();
        }

        /// first delete supervisors found in moodle but NOT in ULPGC
        if($permissions = $DB->get_records('supervision_permissions', array('scope'=>$scope, 'assigner'=>0))) {
            foreach($permissions as $per) {
                if(!(isset($ulpgc_supervisors[$per->userid])) OR
                                        (isset($ulpgc_supervisors[$per->userid]) AND  !in_array($per->userid, $ulpgc_supervisors[$per->userid]))) {
                    // Doesn't exist more on ULPGC, should be deleted
                    $per->review = 0;
                    supervision_assign_supervisor($per, true);
                    $DB->delete_records('supervision_permissions', array('id'=>$per->id));
                }
            }
        }

       $now = time();

       foreach($ulpgc_supervisors as $userid => $instances) {
            foreach($instances as $instance) {
                if($permission = $DB->get_record('supervision_permissions', array('userid'=>$userid, 'scope'=>$scope, 'instance'=>$instance, 'assigner'=>0))) {
                    // record exists , ensure we have all permissions
                    $permission->review = 1;
                    $permission->warnings = $warnings;
                    $permission->adduser = 1;
                    $permission->timemodified = $now;
                    $DB->update_record('supervision_permissions', $permission);
                    supervision_assign_supervisor($permission);
                } else {
                    // records doesn't exists: add
                    $permission = new StdClass;
                    $permission->userid = $userid;
                    $permission->scope = $scope;
                    $permission->instance = $instance;
                    $permission->review = 1;
                    $permission->warnings = $warnings;
                    $permission->assigner = 0;
                    $permission->adduser = 1;
                    $permission->timemodified = $now;
                    $permission->id = $DB->insert_record('supervision_permissions', $permission);
                    supervision_assign_supervisor($permission);
                }
            }
        }

    }

    function supervision_ulpgc_dept_supervisors() {
        global $DB;

        $ulpgc_supervisors = array();
        if(!get_config('local_supervision', 'enabledepartments')) {
            return $ulpgc_supervisors;
        }
        $sql = "SELECT c.id, ud.id AS director, us.id AS secretario
                    FROM {departamentos} c
                    JOIN {user} ud ON ud.idnumber = c.director
                    JOIN {user} us ON ud.idnumber = c.secretario
                WHERE 1 ";
        if($departments = $DB->get_records_sql($sql, array())) {
            foreach($departments as $dept) {
                if(!isset($ulpgc_supervisors[$dept->director])) {
                    $ulpgc_supervisors[$dept->director] = array($dept->id);
                } else {
                    $ulpgc_supervisors[$dept->director][] = $dept->id;
                }
                if(!isset($ulpgc_supervisors[$dept->secretario])) {
                    $ulpgc_supervisors[$dept->secretario] = array($dept->id);
                } else {
                    $ulpgc_supervisors[$dept->secretario][] = $dept->id;
                }
            }
        }
        return $ulpgc_supervisors;
    }

    function supervision_ulpgc_faculty_supervisors() {
        global $DB;

        $ulpgc_supervisors = array();
        if(!get_config('local_supervision', 'enablecategories')) {
            return $ulpgc_supervisors;
        }
        $sql = "SELECT c.id, ud.id AS director, us.id AS secretario
                    FROM {centros} c
                    JOIN {user} ud ON ud.idnumber = c.director
                    JOIN {user} us ON ud.idnumber = c.secretario
                WHERE 1 ";
        if($faculties = $DB->get_records_sql($sql, array())) {
            foreach($faculties as $fac) {
                if(!isset($ulpgc_supervisors[$fac->director])) {
                    $ulpgc_supervisors[$fac->director] = array($fac->codigo);
                } else {
                    $ulpgc_supervisors[$fac->director][] = $fac->codigo;
                }
                if(!isset($ulpgc_supervisors[$fac->secretario])) {
                    $ulpgc_supervisors[$fac->secretario] = array($fac->codigo);
                } else {
                    $ulpgc_supervisors[$fac->secretario][] = $fac->codigo;
                }
            }
        }

        $results  = array();
        foreach($ulpgc_supervisors as $userid => $faculties) {
            $results[$userid] = array();
            foreach($faculties as $fac) {
                if($cats = $DB->get_records('course_categories', array('faculty'=>$fac), '', 'id, idnumber')) {
                    $results[$userid] = $results[$userid] + array_keys($cats);
                }
            }
        }
        return $results;
    }





