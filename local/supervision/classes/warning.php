<?php

/**
 * Definition of a warning class supervision warning items
 *
 * @package   local_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_supervision;
 
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/statslib.php');

/**
 * An abstract object that holds methods and attributes common to all warning objects
 * Works together with supervision_warnings table
 *
 * @package   local_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class warning {

    /**
     * Array of required table fields, must start with 'id'.
     * @var array $required_fields
     */
    public $fields = array('id', 'courseid', 'module', 'cmid', 'warningtype', 'instanceid', 'itemid',
                            'url', 'info', 'userid', 'studentid', 'timereference', 'timecreated', 'timefixed', 'timemailed', 'comment');

    /**
     * The PK of the object in DB.
     * @var int $id
     */
    public $id;

    /**
     * The ID of the course this warning was raised in.
     * @var int $courseid
     */
    public $courseid;

    /**
     * The name of the module this warning is related to.
     * @var string $module
     */
    public $module;

    /**
     * The course_module ID this warning was raised in.
     * @var int $cmid
     */
    public $cmid;

    /**
     * The kind of this warning
     * @var string $warningtype
     */
    public $warningtype;

    /**
     * The module instance ID this warning was raised in.
     * @var int $instanceid
     */
    public $instanceid;

    /**
     * The module subitem ID this warning is related to.
     * @var int $itemid
     */
    public $itemid;

    /**
     * url to the module, instance and item that raised the warning
     * @var string $url
     */
    public $url;

    /**
     * Additional info this warning may need
     * @var string $info
     */
    public $info;

    /**
     * The ID of the teacher related to this this warning.
     * @var int $userid
     */
    public $userid;

    /**
     * The ID of the student related to this this warning.
     * @var int $studentid
     */
    public $studentid;

    /**
     * The timemodified timestamp of the item that caused the warning to be raised.
     * @var int $timereference
     */
    public $timereference;

    /**
     * The timestamp of the moment this warning was raised.
     * @var int $timecreated
     */
    public $timecreated;

    /**
     * The timestamp of the moment this warning was fixed.
     * @var int $timefixed
     */
    public $timefixed;

    /**
     * The timestamp of the last time this warning was e-mailed.
     * @var int $timemailed
     */
    public $timemailed;

    /**
     * comments adden when editing of fixing the warning
     * @var string $comment
     */
    public $comment;


    /**
     * Constructor. Optionally attempts to fetch corresponding row from the database
     *
     * @param int/objet/array $warning id field in the supervision_warnings table
     *                             or and object or array containing the relevant fields
     */
    public function __construct($warning=NULL) {
        global $DB;

        $this->id = 0;
        if(is_int($warning)) {
            $warning = $DB->get_record('supervision_warnings', array('id', $warning));
        }
        if(is_object($warning) OR is_array($warning)) {
            warning::set_properties($this, $warning);
        }
    }

    /**
     * Given an associated array or object, cycles through each key/variable
     * and assigns the value to the corresponding variable in this object.
     *
     * @static
     * @param stdClass $instance The object to set the properties on
     * @param array $params An array of properties to set like $propertyname => $propertyvalue
     */
    public static function set_properties(&$instance, $params) {
        $params = (array) $params;
        foreach ($params as $var => $value) {
            if(in_array($var, $instance->fields)) {
                $instance->$var = $value;
            }
        }
    }

    /**
     * Creates a warning class instance according to the warningtype of teh object
     *
     * @static
     * @param object $ an object from supervision_warnings table
     * @param array An array of warning classes of different
     */
    public static function toclass(\StdClass $item) {
        $result = false;
        if($item->warningtype) {
            $classname = '\local_supervision\warning_'.$item->warningtype;
            $result = new $classname($item);
        }
        return $result;
    }

    /**
     * Given an array of warning objects from database return an array of supervision_warning instances
     *
     * @static
     * @param array $items an array of warning object from supervision_warnings table
     * @param array An array of warning classes of different
     */
    public static function fetch_all(array $items) {
        $result = array();
        if(!$items OR !is_array($items)) {
            return $result;
        }
        foreach($items as $key => $item) {
            if($item->warningtype) {
                $classname = 'warning_'.$item->warningtype;
                $result[$key] = new $classname($item);
            }
        }
        return $result;
    }

    /**
     * Returns an appropiate icon for this warning type
     *
     * @return a moodle icon object
     */
    public function get_icon() {
        global $OUTPUT;
        return $OUTPUT->pix_icon('icon', get_string('pluginname', 'supervisionwarning_'.$this->warningtype), 'mod_'.$this->module, array('class'=>'icon'));
    }

    /**
     * Returns object with fields and values that are defined in database
     *
     * @return stdClass
     */
    public function get_data() {
        $data = new \stdClass();

        foreach ($this as $var=>$value) {
            if(in_array($var, $this->fields)) {
                if (is_object($value) or is_array($value)) {
                    debugging("Incorrect property '$var' found when inserting warning object");
                } else {
                    $data->{$var} = $value;
                }
            }
        }
        return $data;
    }

    /**
     * Updates this object in the Database, based on its object variables. ID must be set.
     *
     * @return bool success
     */
    public function db_update() {
        global $DB;

        if (empty($this->id)) {
            debugging('Can not update grade object, no id!');
            return false;
        }

        $data = $this->get_data();
        $success  = $DB->update_record('supervision_warnings', $data);
        return $success;
    }

    /**
     * Records this object in the Database, sets its id to the returned value, and returns that value.
     *
     * @return int The new grade object ID if successful, false otherwise
     */
    public function db_insert() {
        global $DB;

        if (!empty($this->id)) {
            debugging("Warning object already exists!");
            return false;
        }

        $data = $this->get_data();
        $this->id = $DB->insert_record('supervision_warnings', $data);
        return $this->id;
    }

    
    
    
    /**
     * Looks at supervision_permissions table to search for users with supervision permisions at this instance
     *
     * @param string $type any/category/department the scope of supervision permissions
     * @return array of user-like objects
     */
    public function get_supervisors($type='any') {
        global $DB;

        if (empty($this->courseid)) {
            debugging("Warning object courseid do not exists!");
            return false;
        }

        $course = $DB->get_record('course', array('id'=>$this->courseid), 'id, category');
        if($ulpgc = get_config('local_ulpgccore', 'version')) {
            $course = local_ulpgccore_get_course_details($course);
        }

        $names = get_all_user_name_fields(true, 'u');
        $warninglike = $DB->sql_like('sp.warnings', ':warningtype');
        $warningtype = ($this->warningtype) ? '%'.$this->warningtype.'%' : '';
        $params = array('warningtype'=>$warningtype);
        $selectfrom = "SELECT sp.userid, u.id, u.idnumber, u.auth, u.deleted, u.suspended,
                                u.email, u.emailstop, u.mailformat, u.maildisplay, $names
                            FROM {supervision_permissions} sp
                            JOIN {user} u ON u.id = sp.userid ";
        $groupby= ' GROUP BY sp.userid ';

        if($type == 'category') {
            $where = " WHERE sp.scope = :scope AND sp.instance = :instance AND $warninglike ";
            $params['scope'] = 'category';
            $params['instance'] = $course->category;
        }

        if($ulpgc && $type == 'department') {
            $where = " WHERE sp.scope = :scope AND sp.instance = :instance AND $warninglike ";
            $params['scope'] = 'department';
            $params['instance'] = $course->department;
        }

        if($type == 'any') {
            $scope2 = '';
            if($ulpgc) {
                $scope2 = 'OR (sp.scope = :scope2 AND sp.instance = :instance2)';
                $params['scope2'] = 'department';
                $params['instance2'] = $course->department;
            }

            $where = " WHERE ( (sp.scope = :scope1 AND sp.instance = :instance1) $scope2)
                                 AND $warninglike  ";
            $params['scope1'] = 'category';
            $params['instance1'] = $course->category;
        }

        $supervisors = $DB->get_records_sql($selectfrom.$where.$groupby, $params);
        return $supervisors;
    }


    /**
     * If userid not set, tries to determine teacher users by role in course
     *
     * @static
     * @param object $config local_config_plugins record
     * @return array of user-like objects
     */
    public static function get_excluded_sql($config = null) {
        global $DB;
        
        if(empty($config)) {
            $config = get_config('local_supervision');
        }
        $ulpgc = get_config('local_ulpgccore', 'version');

        $excludedcategories = '';
        $catparams = array();
        if($config->excludedcats) {
            list($incatsql, $catparams) = $DB->get_in_or_equal(explode(',', $config->excludedcats), SQL_PARAMS_NAMED, 'cat', false );
            $excludedcategories = " AND c.category $incatsql ";
        }

        $excludecourses = '';
        $excludedjoin = '';
        if($config->excludecourses && $ulpgc) {
            if($ulpgc) {
                $excludecourses .= ' AND uc.credits > 0 ';
                $excludedjoin = 'LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid';
            }
        }

        $excludeparams = array();
        if($config->excludeshortnames) {
            if($excluded = explode($config->excludeshortnames, ',')) {
                foreach($excluded as $key => $c ) {
                    $excluded[$key]= trim($c);
                }
                list($insql, $excludeparams) = $DB->get_in_or_equal($excluded, SQL_PARAMS_NAMED, 'ex_', false);
                $excludecourses .= " AND c.shortname $insql ";
            }
        }

        $excludedwhere = $excludedcategories.$excludecourses;
        $excludedparams = $catparams + $excludeparams;
    
        return array($excludedwhere, $excludedjoin, $excludedparams);
    }
    
    /**
     * If userid not set, tries to determine teacher users by role in course
     *
     * @static
     * @param object a warning_object-like structure to process, rather than this instance
     * @return array of user-like objects
     */
    public static function get_supervised_users($data, $capability = '') {
        global $DB;

        $users = array();

        $userid = $data->userid;
        $studentid = $data->studentid;
        $courseid = $data->courseid;
        $cmid = $data->cmid;
        $groupmode = $data->groupmode;
        $groupigid = $data->groupingid;

        if($userid) {
            $users[$userid] = $userid;
            return $users;
        }

        // there is no userid, so we need to figure it
        // first get all users with checked roles un the course
        $context = \context_module::instance($cmid);
        $config = get_config('local_supervision');
        $checkedroles= array();
        if(!empty($config->checkedroles)) {
            $checkedroles = explode(',', $config->checkedroles);
        }
        // must be called with ra.id AS raid because multiple roles
        $users = get_role_users($checkedroles, $context, true, 'ra.id AS raid, u.id, u.idnumber, u.lastname, u.firstname ');
        if($capability) {
            $enrolled = get_enrolled_users($context, $capability, 0, 'u.id, u.idnumber', null, 0, 0, true);
            foreach($users as $key => $user) {
                if(!array_key_exists($user->id, $enrolled)) {
                    unset($users[$key]);
                }
            }
        }

        $graders = array();

        // if only one user with teacher privileges, just return that one
        if(count($users) == 1) {
            $user = reset($users);
            $userid = $user->id;
            $graders[$userid] = $userid;
            return $graders;
        }

        // now we can check any restriction by groups settings
        if($groupmode != NOGROUPS) {
            $studentgroups = groups_get_user_groups($courseid, $studentid);
            foreach($users as $user) {
                $userid = $user->id;
                if(has_capability('moodle/site:accessallgroups', $context, $userid, false)) {
                    $graders[$userid] = $userid;
                } else {
                    //if teacher cannot acces all, then teacher and student must share a group
                    $teachergroups = groups_get_user_groups($courseid, $userid);
                    if($samegroup = array_intersect_key($studentgroups[$groupigid], $teachergroups[$groupigid])) {
                        $graders[$userid] = $userid;
                    }
                }
            }

        } else {
            foreach($users as $user) {
                $userid = $user->id;
                $graders[$userid] = $userid;
            }
        }

        return $graders;
    }

    /**
     * Fetchs a portion of the supervision_holidays table corresponding to specified time interval
     *
     * @static
     * @param int $timestart starting time
     * @param int $timeend ending time for this interval
     * @return array , part of holydays table than includes $timestart - $timeend, if any
     */
    public static function get_holidays($timestart, $timeend) {
        global $DB;

        $holidays = array('0'=>0);
        $select = " ((datestart >= :timestart ) OR ((datestart + timeduration) >= :timestart2))
                        AND  datestart <= :timeend ";
        $params = array('timestart'=>$timestart, 'timestart2'=>$timestart, 'timeend'=>$timeend);
        if($dates = $DB->get_records_select('holidays', $select, $params, 'datestart ASC')) {
            foreach($dates as $date) {
                $days = $date->timeduration / DAYSECS;
                for($i=1; $i<=$days; $i++) {
                    $holidays[] = stats_get_base_daily(($date->datestart+DAYSECS*($i-1)));
                }
            }
        }
        return $holidays;
    }

    /**
     * Calculate holiday time between two dates according to each warning's rules
     * Uses supervision_holidays table
     *
     * @static
     * @param int $starttime initial date
     * @param int $endtime end date, closing the time interval
     * @param bool $weekends consider weekend days as holidays or not
     * @return string , formatted link
     */
    public static function holiday_time($starttime, $endtime, $weekends=true) {
        global $CFG;

        $oneday=DAYSECS;
        $days = 0;
        if ($starttime < time() - $oneday*365*10) return 0;
        if ($endtime   > time() + $oneday*365*10) return 0;

        $holydays = $this->get_holidays($starttime, $endtime);

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

    /**
     * Checks if a given time is within a holiday day
     * Uses supervision_holidays table
     *
     * @static
     * @param int $timetocheck initial date
     * @param bool $weekends consider weekend days as holidays or not
     * @return bool , holiday or not
     */
    public static function is_holidays($holidays, $timetocheck, $weekends=true) {
        global $CFG, $DB;
        $holiday = false;
        foreach($holidays as $day) {
            if(($day->datestart <= $timetocheck) && (($day->datestart + $day->timeduration) > $timetocheck)) {
                $holiday = true;
                break;
            }
        }
        
/*
        $select = ' datestart <= :time1 AND (datestart + timeduration) > :time2 ';
        $params = array('time1'=>$timetocheck, 'time2'=>$timetocheck);
        $holiday = $DB->record_exists_select('supervision_holidays', $select, $params);
*/      

        if($holiday) {
            return true; // no need to check for weeked
        }
        

        if($weekends) {
            $day = getdate($timetocheck);
            $calendar_weekend = isset($CFG->calendar_weekend) ? intval($CFG->calendar_weekend) : 65;
            if($calendar_weekend & (1 << ($day['wday'] % 7))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate a time N working days before or after a given date, holiday days aren't counted N
     * Uses supervision_holidays table
     *
     * @static
     * @param int $timetocheck initial date
     * @param int $days the N days gap
     * @param bool $up true/false limit after or before the given time
     * @param bool $weekends consider weekend days as holidays or not
     * @return string , formatted link
     */
    public static function threshold_without_holidays($timetocheck, $days,  $up=true, $weekends=true) {
        global $DB;

        $move = '+1 day';
        $down = 0;
        if(!$up) {
            $move = '-1 day';
            $down = 1;
        }
        
        $holidays = $DB->get_records('supervision_holidays', null);
                
        
        // if now is holidays start counting delays on previous midnight
        if(warning::is_holidays($holidays, $timetocheck, $weekends)) {
            $timetocheck = usergetmidnight($timetocheck);
            while(warning::is_holidays($holidays, $timetocheck-$down, $weekends)) {
                $timetocheck = strtotime($move, $timetocheck);
            }
        }

        $count = 0;
        while($count < $days) {
            $timetocheck = strtotime($move, $timetocheck);
            while(warning::is_holidays($holidays, $timetocheck, $weekends)) {
                $timetocheck = strtotime($move, $timetocheck);
            }
            $count +=1;
        }

        return $timetocheck;
    }


///////////////////////////////////////////////////////////////////////////////////


    /**
     * Called by cron to review tables for undone/pending activities that should raise a warning
     *
     * @static
     * @abstract
     * @param int $timetocheck starting time for collection
     */
    public static function get_stats($timetocheck) {
        throw new \coding_exception('collect_stats() method needs to be overridden in each subclass of supervision_warning ');
    }

    /**
     * Returns an appropiate link to an activity item suitable for warnings report
     *
     * @abstract
     * @return string , formatted link
     */
    public function report_instancelink() {
        throw new \coding_exception('report_instancelink() method needs to be overridden in each subclass of supervision_warning ');
    }

    /**
     * Returns an appropiate info about an activity item that raised a warning
     *
     * @abstract
     * @return string , formatted link
     */
    public function report_rowinfo() {
        throw new \coding_exception('report_rowinfo() method needs to be overridden in each subclass of supervision_warning ');
    }

    /**
     * Calculates overdue time for this activity warning
     *
     * @abstract
     * @param int $timetocheck time for calculation with respect to timecreated
     * @return string , formatted link
     */
    public function report_overdue($timetocheck) {
        throw new \coding_exception('report_overdue() method needs to be overridden in each subclass of supervision_warning ');
    }




}
