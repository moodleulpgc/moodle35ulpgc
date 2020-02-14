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
 * This file contains the definition for the library class for copyset feedback plugin
 *
 *
 * @package assignfeedback_copyset
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/grade/grading/lib.php');

function copyset_list_compare($a, $b) {
    $a = $a->current;
    $b = $b->current;

    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}



/**
 * library class for file feedback plugin extending feedback plugin base class
 *
 * @package assignfeedback_copyset
 * @copyright  2012 Enrique Castro, ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_feedback_copyset extends assign_feedback_plugin {

    /** @var boolean|null $enabledcache Cached lookup of the is_enabled function */
    private $enabledcache = null;
    private $enabledhiddencache = null;

    /**
     * Get the name of the file feedback plugin
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_copyset');
    }

    /**
     * Get form elements for grading form
     *
     * @param stdClass $grade
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return bool true if elements were added to the form
     */
    public function get_form_elements($grade, MoodleQuickForm $mform, stdClass $data) {
        return false;
    }

    /**
     * Return true if there are no feedback files
     * @param stdClass $grade
     */
    public function is_empty(stdClass $grade) {
        return true;
    }


    /**
     * Check to see if the grade feedback has been modified from a form input in this plugin.
     *
     * @param stdClass $grade Grade object.
     * @param stdClass $data Data from the form submission (not used).
     * @return boolean True if the pdf has been modified, else false.
     */
    public function is_feedback_modified(stdClass $grade, stdClass $data) {
        return false;
    }

    /**
     * Print a sub page in this plugin
     *
     * @param string $action - The plugin action
     * @return string The response html
     */
    public function view_page($action) {
        global $CFG;
        if($action == 'dueextensions') {
            $capability = 'mod/assign:grantextension';
        } elseif($action == 'copygrades' || $action == 'setgrades') {
           $capability = 'mod/assign:grade';
        } elseif($action == 'randommarkers' || $action == 'importmarkers') {
            $capability = 'mod/assign:manageallocations';
        } else {
            throw new coding_exception("Action '$action' is unknown in assign module plugins.");
        }
        require_capability($capability, $this->assignment->get_context());
        require_once($CFG->dirroot . '/mod/assign/feedback/copyset/gradingactionforms.php');

        $o = '';
        $renderer = $this->assignment->get_renderer();
        $actionformname = "assignfeedback_copyset_{$action}_form";
        $returnurl = new moodle_url('view.php', array('id'=>$this->assignment->get_course_module()->id,
                                                        'action'=>'grading'));
        $confirm = optional_param('confirmed', 0, PARAM_INT);
        $mform = new $actionformname(null, array('assignment'=>$this->assignment,
                                                    'action'=>$action,
                                                    'users'=>array(),
                                                    'import'=>'',
                                                    'confirm'=>$confirm));

        if ($mform->is_cancelled()) {
            redirect($returnurl);
            return;
        } elseif($fromform = $mform->get_data()) {
            $users = array();
            if($action != 'importmarkers') {
                $users = $this->get_affected_users($action, $fromform);
            }
            if(isset($fromform->confirmed) && $fromform->confirmed && sesskey()) {
                switch($action) {
                    case 'setgrades' : if(isset($fromform->setgrade)) {
                                            $count = $this->process_copyset_grades(array_keys($users), $fromform->setgrade, $fromform->override);
                                        }
                                        break;
                    case 'copygrades' : $count = $this->process_copyset_grades(array_keys($users), 0, $fromform->override, $users);
                                        break;
                    case 'dueextensions' : if(isset($fromform->dueextension)) {
                                            $count = $this->process_dueextensions(array_keys($users), $fromform->dueextension);
                                        }
                                        break;
                    case 'randommarkers' : if(isset($fromform->removemarkers)) {
                                            if($count = $this->process_copyset_randommarkers(array_keys($users), $fromform->groups, $fromform->removemarkers, $fromform->bywstate)) {
                                                $event = \assignfeedback_copyset\event\randommarkers_set::create_from_assign($this->assignment, $fromform->removemarkers);
                                                $event->trigger();
                                            }
                                        }
                                        break;
                    case 'importmarkers' :  if(isset($fromform->importid) &&  isset($fromform->draftid)) {
                                                $count = $this->process_import_markers($fromform->draftid, $fromform->importid, $fromform->removemarkers, $fromform->encoding, $fromform->separator);
                                            }
                                        break;
                }
                redirect($returnurl, get_string('changed', 'assignfeedback_copyset', $count));
                return;
                // here ends processing after having data in form.
            } else {
                // there is data but not confimed: display confirmation form
                if(isset($fromform->timevalue)) {
                    $timefreezed = $fromform->timevalue;
                }
                $importdata = new stdClass;
                if($action == 'importmarkers' && $csvdata = $mform->get_file_content('markersfile')) {
                    //require_once($CFG->libdir . '/csvlib.class.php');
                    require_once($CFG->dirroot . '/mod/assign/feedback/copyset/importlib.php');
                    $importid = csv_import_reader::get_new_iid('assignfeedback_copyset');
                    $gradeimporter = new assignfeedback_copyset_marker_importer($importid, $this->assignment,
                                                                            $fromform->encoding, $fromform->separator);
                    $importdata->csvdata = $csvdata;
                    $importdata->gradeimporter = $gradeimporter;
                    $importdata->draftid = $fromform->markersfile;
                    $importdata->removemarkers = $fromform->removemarkers;
                }
                
                $mform = new $actionformname(null, array('assignment'=>$this->assignment,
                                                            'action'=>$action,
                                                            'users'=>$users,
                                                            'import'=>$importdata,
                                                           'confirm'=>1));
                $fromform->confirmed = 1;
                if(isset($fromform->grade)) {
                    $fromform->setgrade = $fromform->grade;
                }
                if(isset($fromform->timevalue)) {
                    $fromform->dueextension = $fromform->timevalue;
                }
                $mform->set_data($fromform);
            }
        }
        // form has no data, we need to display the form to the user
        $o .= $renderer->render(new assign_header($this->assignment->get_instance(),
                                                        $this->assignment->get_context(),
                                                        false,
                                                        $this->assignment->get_course_module()->id,
                                                        get_string($action, 'assignfeedback_copyset')));
        $o .= $renderer->render(new assign_form('batch'.$action, $mform));
        $o .= $renderer->render_footer();

        return $o;
    }

    /**
     * Return a list of the grading actions performed by this plugin
     * This plugin supports upload zip
     *
     * @return array The list of grading actions
     */
    public function get_grading_actions() {
        $actions = array();
        $instance = $this->assignment->get_instance();
        if($instance->grade !== 0) {
            $actions = array('copygrades'=>get_string('copygrades', 'assignfeedback_copyset'),
                            'setgrades'=>get_string('setgrades', 'assignfeedback_copyset'));
        }
        if($this->assignment->is_any_submission_plugin_enabled() && ($instance->duedate || $instance->cutoffdate)) {
            $actions['dueextensions'] = get_string('dueextensions', 'assignfeedback_copyset');
        }
        if($instance->markingworkflow &&  $instance->markingallocation &&
                                    has_capability('mod/assign:manageallocations', $this->assignment->get_context())) {
            $actions['randommarkers'] = get_string('randommarkers', 'assignfeedback_copyset');
            $actions['importmarkers'] = get_string('importmarkers', 'assignfeedback_copyset');
        }

        return $actions;
    }

    
    /**
     * Override the default is_enabled to disable this plugin if advanced grading is active
     *
     * @return bool
     */
    public function is_enabled() {
       if ($this->enabledcache === null) {
            $active = false;
            if($this->assignment->get_context()) { //this is a real module, not creating a new one
                $gradingmanager = get_grading_manager($this->assignment->get_context(), 'mod_assign', 'submissions');
                $controller = $gradingmanager->get_active_controller();
                $active = !empty($controller);
            }

            if ($active) {
                $this->enabledcache = false;
            } else {
                $active = get_config('assignfeedback_copyset', 'enabledhidden');

                
                if($active) {
                    $this->enabledcache = true;
                } else {
                    $this->enabledcache = parent::is_enabled();
                }
            }
        }
        
        return $this->enabledcache;
    }

    /**
     * Do not show this plugin in the grading table or on the front page
     *
     * @return bool
     */
    public function has_user_summary() {
        return false;
    }


    /**
     * Apply form filters to enrolled users to get all users upon which to operate
     *
     * @param string $action - The plugin action
     * @param stdClass $fromform - The form data
     * @return array of user objects with id, names for fullname(), and optional grades
     */
    public function get_affected_users($action, $fromform) {
        global $DB, $USER;
        $users = array();

        $cm = $this->assignment->get_course_module();
        $context = $this->assignment->get_context();
        $instance = $this->assignment->get_instance();

        // first get all users in course, and  groups if set
        if(!is_array($fromform->groups)) {
            $fromform->groups = array($fromform->groups);
        }
        $users = array();
        $names = get_all_user_name_fields(true, 'u');
        foreach($fromform->groups as $groupid) {
            $gusers = get_enrolled_users($context, 'mod/assign:submit', $groupid, 'u.id, u.username, u.idnumber, '.$names, 'lastname ASC');
            $users = $users + $gusers;
        }

        $gradessql = "SELECT ag.id, ag.userid, ag.grade
                        FROM {assign_grades} ag
                        INNER JOIN (SELECT agg.userid, MAX(agg.attemptnumber) AS maxattempt
                                    FROM {assign_grades} agg
                                    GROUP BY agg.userid) gag ON ag.userid = gag.userid
                        AND ag.attemptnumber = gag.maxattempt
                        WHERE ag.assignment = :assignment ";

        // by submission
        if(isset($fromform->bysubmission) && $fromform->bysubmission) {
            $byusers = $this->get_users_by_submission($users, $fromform->bysubmission);
            $users = array_intersect_key($users, $byusers);
        }

        // by grading
        if(isset($fromform->bygrading) && $fromform->bygrading) {
            $byusers = $this->get_users_by_grading($users, $fromform->bygrading);
            $users = array_intersect_key($users, $byusers);
        }

        // by grade
        if(isset($fromform->bygrade) && $fromform->bygrade) {
            $byusers = $this->get_users_by_grade($users, $fromform->bygrade, $instance->id);
            $users = array_intersect_key($users, $byusers);
        }

        // by other assignment grade
        if(isset($fromform->byothergrade) && $fromform->byothergrade &&
            isset($fromform->source) && $fromform->source) {
            $byusers = $this->get_users_by_grade($users, $fromform->byothergrade, $fromform->source);
            $users = array_intersect_key($users, $byusers);
            // add new grade to set for each user in user list
            foreach($users as $uid => $user) {
                $user->grade = $byusers[$uid];
                $users[$uid] = $user;
            }
        }

        // by marker assigned
        if(isset($fromform->removemarkers) && $fromform->removemarkers == 0) {
            $byusers = $this->get_users_by_marker($users);
            $users = array_intersect_key($users, $byusers);
        }

        if(isset($fromform->specialperiod) && $fromform->specialperiod) {
            $byusers = $this->get_users_specialperiod($users, $fromform->strictrule);
            $users = array_intersect_key($users, $byusers);
        }

        return $users;
    }



    /**
     * Apply form filters to enrolled users to get all users upon which to operate
     *
     * @param array $users - The users list
     * @param stdClass $filter - The form data
     * @return array of user just a portion of input array
     */
    public function get_users_by_marker($users) {
        global $DB;
        $instance = $this->assignment->get_instance();
        list($insql, $params) = $DB->get_in_or_equal(array_keys($users), SQL_PARAMS_NAMED, 'u');
        $sql = "SELECT u.id, auf.userid
                FROM {user} u
                LEFT JOIN {assign_user_flags} auf ON auf.assignment = :assign AND auf.userid = u.id
                WHERE u.id $insql AND (auf.allocatedmarker = 0 OR auf.allocatedmarker IS NULL) ";
        $params['assign'] = $instance->id;
        $subs = $DB->get_records_sql_menu($sql, $params);
        $byusers = array();
        if($subs) {
            $byusers = array_flip($subs);
        }

        return $byusers;
    }

    /**
     * Apply form filters to enrolled users to get all users upon which to operate
     *
     * @param array $users - The users list
     * @param stdClass $filter - The form data
     * @return array of user just a portion of input array
     */
    public function get_users_by_state($users, $states) {
        global $DB;
        $instance = $this->assignment->get_instance();

        list($insql, $params) = $DB->get_in_or_equal(array_keys($users), SQL_PARAMS_NAMED, 'u');
        $stateswhere = '';
        foreach($states as $state) {
            if($state == ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED) {
                $stateswhere .= " OR auf.workflowstate IS NULL ";
            }
            $stateswhere .= " OR auf.workflowstate = :s_$state ";
            $params["s_$state"] = $state;
        }
        if($stateswhere) {
            $stateswhere = " AND ( 0 $stateswhere ) ";
        }
        $sql = "SELECT u.id, auf.userid
                FROM {user} u
                LEFT JOIN {assign_user_flags} auf ON auf.assignment = :assign AND auf.userid = u.id
                WHERE u.id $insl $stateswhere ";
        $params['assign'] = $instance->id;
        $subs = $DB->get_records_sql_menu($sql, $params);

        $byusers = array();
        if($subs) {
            $byusers = array_flip($subs);
        }

        return $byusers;
    }



    /**
     * Apply form filters to enrolled users to get all users upon which to operate
     *
     * @param array $users - The users list
     * @param stdClass $filter - The form data
     * @return array of user just a portion of input array
     */
    public function get_users_by_submission($users, $filter) {
        global $DB;
        $instance = $this->assignment->get_instance();
        switch($filter) {
            case 'submitted' :
                                $subs = $DB->get_records_menu('assign_submission', array('assignment'=>$instance->id, 'status'=>ASSIGN_SUBMISSION_STATUS_SUBMITTED, 'latest' => 1), '', 'id, userid');
                                break;
            case 'notsubmitted' :
                                if($submitted = $DB->get_records_menu('assign_submission', array('assignment'=>$instance->id, 'latest' => 1), '', 'id, userid')) {
                                    $subs = array_diff_key($users, array_flip($submitted));
                                    $subs = array_keys($subs);
                                } else {
                                    $subs = array_keys($users);
                                }
                                break;
            case 'draft' :
                                $subs = $DB->get_records_menu('assign_submission', array('assignment'=>$instance->id, 'status'=>ASSIGN_SUBMISSION_STATUS_DRAFT, 'latest' => 1), '', 'id, userid');
                                break;
        }
        $byusers = array();
        if($subs) {
            $byusers = array_flip($subs);
        }

        return $byusers;
    }

    /**
     * Apply form filters to enrolled users to get all users upon which to operate
     *
     * @param array $users - The users list
     * @param stdClass $filter - The form data
     * @return array of user just a portion of input array
     */
    public function get_users_by_grading($users, $filter) {
        global $DB;
        $instance = $this->assignment->get_instance();
        switch($filter) {
            case 'graded':
                            $grades = $DB->get_records_select_menu('assign_grades', ' assignment = :assignment AND grade >=0 ', array('assignment'=>$instance->id), '', 'id, userid');
                            break;
            case 'notgraded':
                            if($graded = $DB->get_records_select_menu('assign_grades', ' assignment = :assignment AND grade >=0 ', array('assignment'=>$instance->id), '', 'id, userid')) {
                                $grades = array_diff_key($users, array_flip($graded));
                                $grades = array_keys($grades);
                            } else {
                                $grades = array_keys($users);
                            }
                        break;
        }
        $byusers = array();
        if($grades) {
            $byusers = array_flip($grades);
        }

        return $byusers;
    }

    /**
     * Apply form filters to enrolled users to get all users upon which to operate
     *
     * @param array $users - The users list
     * @param stdClass $filter - The form data
     * @return array of user just a portion of input array
     */
    public function get_users_by_grade($users, $filter, $sourceid) {
        global $DB;
        $instance = $this->assignment->get_instance();
        $gradinginfo = grade_get_grades($instance->course,
                                            'mod',
                                            'assign',
                                            $sourceid,
                                            array_keys($users));
        $gradingitem = $gradinginfo->items[0];
        $gradepass = $gradingitem->gradepass;
        $byusers = array();

        $sql = "SELECT ag.id, ag.userid, ag.grade
                FROM {assign_grades} ag
                INNER JOIN (SELECT agg.userid, MAX(agg.attemptnumber) AS maxattempt
                            FROM {assign_grades} agg
                            GROUP BY agg.userid) gag ON ag.userid = gag.userid
                AND ag.attemptnumber = gag.maxattempt
                WHERE ag.assignment = :assignment ";

        if($grades = $DB->get_records_sql($sql, array('assignment'=>$sourceid))) {
            foreach($grades as $grade) {
                if(!isset($gradingitem->grades[$grade->userid])) {
                    continue;
                }
                $gradebookgrade = $gradingitem->grades[$grade->userid]->grade;
                $copy = false;
                switch ($fromform->bygrade) {
                case 'pass' :
                    if($gradebookgrade >= $gradepass) {
                        $copy = true;
                    }
                    break;
                case 'fail' :
                    if($gradebookgrade < $gradepass) {
                        $copy = true;
                    }
                    break;
                }
                if($copy) {
                    $byusers[$grade->userid] = $grade->grade;
                }
            }
        }

        return $byusers;

    }


    /**
     * Perform the grading operation on selected users, applying the specified grade
     * Grade is fixed in formdate (set) or copied from other assignment
     * grades must be present on $users array from get_affected_users();
     *
     * @param array $users - The user list to operate on a plain array of userids
     * @param stdClass $setgrade - a single grade value. It means that all users will be updated with this value
     * @param int $override - a flag to set if existing grades as overwritten or not
     * @param array $grades - optional array, indexed by userid, with rawgrades to apply to each
     * @return string message to display
     */
    private function process_copyset_grades($users, $setgrade, $override = 0, $grades=array()) {
        global $USER;
        if(!$users) {
            return 0;
        }

        $instance = $this->assignment->get_instance();
        if($instance->grade == 0) {
            return 0;
        }
      
        $gradevalue = unformat_float($setgrade);
        $now = time();
        $modified = 0;
        foreach($users as $userid) {
            if(!$setgrade) {
                $gradevalue = (float)$grades[$userid]->grade;
            }
            $grade = $this->assignment->get_user_grade($userid, true);
            if(!$this->assignment->grading_disabled($userid) && ($override || !isset($grade->grade) || $grade->grade < 0 )) {
                $grade->grader = $USER->id;
                $grade->grade = $gradevalue;
                $grade->timemodified = $now;
                if($success = $this->assignment->update_grade($grade)) {
                     $modified++;
                }
            }
        }

        if($modified) {
            if($setgrade) {
                $event = \assignfeedback_copyset\event\grades_set::create_from_assign($this->assignment, $setgrade);
            } else {
                $event = \assignfeedback_copyset\event\grades_copied::create_from_assign($this->assignment);
            }
            $event->trigger();

            $cm = $this->assignment->get_course_module();
            $this->assignment->update_gradebook(true, $cm->id);
            $instance->cmid = $cm->id;
            $instance->cmidnumber = $cm->idnumber;
            assign_update_grades($instance);
        }
        return $modified;
    }

    /**
     * Perform the operation of granting extension on selected users setting the specified date
     *
     * @param array $users - The user list to operate on. Userobjects with id, names and possibly a grade to set
     * @param stdClass $formdata - The form data
     * @return string message to display
     */
    public function process_dueextensions($users, $extensionduedate) {
        if(!$users) {
            return 0;
        }
      
        $instance = $this->assignment->get_instance();

        if ($instance->duedate && $extensionduedate) {
            if ($instance->duedate > $extensionduedate) {
                return 0;
            }
        }
        if ($instance->allowsubmissionsfromdate && $extensionduedate) {
            if ($instance->allowsubmissionsfromdate > $extensionduedate) {
                return 0;
            }
        }

        $modified = 0;
        foreach($users as $userid) {
            $flags = $this->assignment->get_user_flags($userid, true);
            $flags->extensionduedate = $extensionduedate;

            $result = $this->assignment->update_user_flags($flags);

            if ($result) {
                \mod_assign\event\extension_granted::create_from_assign($this->assignment, $userid)->trigger();
                $modified++;
            }
        }

        if($modified) {
            $event = \assignfeedback_copyset\event\extensions_granted::create_from_assign($this->assignment, $extensionduedate);
            $event->trigger();
        }
        
        return $modified;
    }


    /**
     * Filter useres based on ULPGC TF special period rules
     *
     * @param array $userids user ids for each user whose submission/grade data is to be modified
     * @param bool $strictrule as normative more tha half passed ( > ) , relaxed is just half passed ( >= )
     * @return array users ids
     */
    public function get_users_specialperiod($users, $strictrule = true) {
        global $CFG, $DB;
        include_once($CFG->dirroot.'/local/ulpgccore/gradelib.php');
        if(!$users){
            return array();
        }
        $instance = $this->assignment->get_instance();

        $gradinginfo = grade_get_grades($instance->course,
                                            'mod',
                                            'assign',
                                            $instance->id,
                                            array_keys($users));
        $gradingitem = $gradinginfo->items[0];
        $gradepass = $gradingitem->gradepass;



        $gradingitem =  $DB->get_record('grade_items', array('courseid'=>$instance->course, 'itemtype'=>'mod', 'itemmodule'=>'assign', 'iteminstance'=>$instance->id));

        $gradeaggregation =  $DB->get_field('grade_categories', 'aggregation', array('id'=>$gradingitem->categoryid));

        $aggregations = explode(',', GRADE_ULPGC_AGGREGATIONS);
        if(!in_array($gradeaggregation, $aggregations)) {
            return array();
        }

        $grade_items = $DB->get_records('grade_items', array('courseid'=>$instance->course, 'categoryid'=>$gradingitem->categoryid, 'itemtype'=>'mod', 'itemmodule'=>'assign', 'hidden'=>0));

        $totalitems = count($grade_items);
        if($totalitems < 2 ) {
            return array(); // if only one item in the category there is no point in checking others
        }

        $usersgrades = array();
        foreach($grade_items as $gradeitem) {
            $grade_grades = grade_grade::fetch_users_grades($gradeitem, array_keys($users), true);
            foreach($grade_grades as $usergrade) {
                $usergrades[$usergrade->userid][$gradeitem->id] = $usergrade->finalgrade;
            }
        }

        $selectedusers = array();
        $thisitem = $gradingitem->id;
        foreach($users as $userid => $user) {
            if($usergrades[$userid][$thisitem] < $grade_items[$thisitem]->gradepass) {
                $passed = 0;
                foreach($usergrades[$userid] as $item=>$grade) {
                    if($usergrades[$userid][$item] >= $grade_items[$item]->gradepass) {
                        $passed +=1;
                    }
                }
                if($strictrule) {
                    if($passed > $totalitems/2) {
                        $selectedusers[$userid] = $userid;
                    }
                } else {
                    if($passed >= $totalitems/2) {
                        $selectedusers[$userid] = $userid;
                    }
                }
            }
        }
        return $selectedusers;
    }




    /**
     * Perform the operation assigning random markers
     *
     * @param array $users - The user list to operate on. flat list userids
     * @return string message to display
     */
    private function process_copyset_randommarkers($users, $groups, $removemarkers, $states) {
        global $DB, $USER;

        $instance = $this->assignment->get_instance();
        $context = $this->assignment->get_context();

        $markers = get_users_by_capability($context, 'mod/assign:grade', 'u.id, u.username, u.idnumber', '', '', '', $groups, '', '', '', true);

        if(!$markers) {
            return 0;
        }
        list($insql, $params) = $DB->get_in_or_equal(array_keys($markers), SQL_PARAMS_NAMED, 'm');
        $sql = "SELECT u.*, COUNT(auf.userid) AS current
                FROM {user} u
                LEFT JOIN {assign_user_flags} auf ON auf.assignment = :assign AND auf.allocatedmarker = u.id
                WHERE u.id $insql
                GROUP BY u.id ";
        $params['assign'] = $instance->id;
        if($markers = $DB->get_records_sql($sql, $params)) {
            shuffle($markers);
            uasort($markers, 'copyset_list_compare');
        }
        $done = array();
        $pusers = array_slice($users, 0, count($markers));
        foreach($pusers as $userid) {
            $marker = reset($markers);
            if($marker->id != $userid) {
                array_shift($markers);
                $flags = $this->assignment->get_user_flags($userid, true);
                $flags->allocatedmarker = $marker->id;
                if ($this->assignment->update_user_flags($flags)) {
                    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
                    \mod_assign\event\marker_updated::create_from_marker($this->assignment, $user, $marker)->trigger();
                }
                $done[] = $userid;
            }
        }


        $users = array_diff($users,$done);

        $count = count($done);

        if($users) {
            $count += $this->process_copyset_randommarkers($users, $groups, $states);
        }

        return $count;
    }
    
    /**
     * If true, the plugin will appear on the module settings page and can be
     * enabled/disabled per assignment instance.
     *
     * @return bool
     */
    public function is_configurable() {
        if ($this->enabledhiddencache === null) {
            $active = get_config('assingfeedback_copyset', 'enabledhidden');

            if ($active) {
                $this->enabledhiddencache = false;
            } else {
                $this->enabledhiddencache = parent::is_configurable();
            }
        }
        return $this->enabledhiddencache;
  

    }
    
    /**
     * Perform the operation of importing markers and assign  markers to users
     *
     * @param array  - The user list to operate on. flat list userids
     * @return string message to display
     */
    private function process_import_markers($draftid, $importid, $removemarkers, $encoding, $separator, $groupid = 0) {
        global $CFG, $DB, $USER;
        
        $instance = $this->assignment->get_instance();
        if(!$instance->markingworkflow || !$instance->markingallocation) {
            return false;
        }
        
        $count = 0;
        require_sesskey();
        require_capability('mod/assign:manageallocations', $this->assignment->get_context());
//                    
        require_once($CFG->dirroot . '/mod/assign/feedback/copyset/importlib.php');

        
        $gradeimporter = new assignfeedback_copyset_marker_importer($importid, $this->assignment, $encoding, $separator);

        $context = context_user::instance($USER->id);
        $fs = get_file_storage();
        if (!$files = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id DESC', false)) {
            redirect(new moodle_url('view.php',
                                array('id'=>$this->assignment->get_course_module()->id,
                                      'action'=>'grading')));
            return;
        }
        $file = reset($files);

        $csvdata = $file->get_content();

        if ($csvdata) {
            $gradeimporter->parsecsv($csvdata);
        }
        if (!$gradeimporter->init()) {
            $thisurl = new moodle_url('/mod/assign/view.php', array('action'=>'viewpluginpage',
                                                                     'pluginsubtype'=>'assignfeedback',
                                                                     'plugin'=>'copyset',
                                                                     'pluginaction'=>'importmarkers',
                                                                     'id' => $this->assignment->get_course_module()->id));
            print_error('invalidimporter', 'assignfeedback_copyset', $thisurl);
            return;
        }

        $users = $this->assignment->list_participants($groupid, true);
        while ($record = $gradeimporter->next()) {
            if(isset($record->user) && array_key_exists($record->user, $users)) {
                $flags = $this->assignment->get_user_flags($record->user, true);
                if($flags->workflowstate != ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
                    if(!$flags->allocatedmarker || $removemarkers) {
                        $flags->allocatedmarker = $record->marker;
                        if ($this->assignment->update_user_flags($flags)) {
                            $user = $DB->get_record('user', array('id' => $record->user), '*', MUST_EXIST);
                            $marker = $DB->get_record('user', array('id' => $record->marker), '*', MUST_EXIST);
                            \mod_assign\event\marker_updated::create_from_marker($this->assignment, $user, $marker)->trigger();
                            $count += 1;
                        }
                    }
                }
            }
        }
        $gradeimporter->close(true);
        
        return $count;
    }
}
