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
 * This file contains the definition for the grading table which subclassses easy_table
 *
 * @package   assignfeedback_wtpeer
 * @copyright 2016 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/gradelib.php');
//require_once($CFG->dirroot.'/mod/assign/locallib.php');

/**
 * Extends table_sql to provide a table of assignment submissions
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_wtpeer_assessment_table extends table_sql implements renderable {
    /** @var assign_feedback_wtpeer $plugin */
    private $plugin = null;
    /** $assignment is included in plugin : calles ad $plugin->get_assignment();
    /** @var int $perpage */
    private $perpage = 10;
    /** @var int $rownum (global index of current row in table) */
    private $rownum = -1;
    /** @var renderer_base for getting output */
    private $output = null;
    /** @var stdClass gradinginfo */
    private $gradinginfo = null;
    /** @var int $tablemaxrows */
    private $tablemaxrows = 10000;
    /** @var boolean $quickgrading */
    private $quickgrading = false;
    /** @var boolean $hasgrade - Only do the capability check once for the entire table */
    private $hasgrade = false;
    /** @var array $groupsubmissions - A static cache of group submissions */
    private $groupsubmissions = array();
    /** @var array $submissiongroups - A static cache of submission groups */
    private $submissiongroups = array();
    /** @var array $plugincache - A cache of plugin lookups to match a column name to a plugin efficiently */
    private $plugincache = array();
    /** @var array $cangrade - Only do the capability check once for the entire table */
    private $cangrade = array();
    /** @var array $canviewmarkers - Only do the capability check once for the entire table */
    private $canviewmarkers = array();
    /** @var array $canviewgrade - Only do the capability check once for the entire table */
    private $canviewgrade = false;
        /** @var boolean $canmanage - Only do the capability check once for the entire table */
    private $canmanage = false;

    
    /**
     * Returns a avarage grade as a field
     *
     * @param string $column the column name, index into various names.
     * @param int $index numerical index of the column.
     * @return string HTML fragment.
     */
    protected function weighted_item_field($item, $marker = 0) {
        global $USER;
        
        $markerwhere = '';
        if($marker > 0) {
            $markerwhere = " AND $item.grader = $marker "; 
        }
        $field = "(SELECT AVG($item.grade)
                    FROM {assignfeedback_wtpeer_grades} $item 
                    WHERE $item.userid = u.id AND $item.submission = s.id AND $item.gradertype = '$item' $markerwhere ) AS $item,  
                    
                    (SELECT user$item.grade
                    FROM {assignfeedback_wtpeer_grades} user$item
                    WHERE user$item.userid = u.id AND user$item.submission = s.id AND user$item.gradertype = '$item' AND user$item.grader = {$USER->id} ) AS user$item
                ";
        return $field;
    }
    
    /**
     * checks if $USER is a grader 
     *
     * @param string $column the column name, index into various names.
     * @param int $index numerical index of the column.
     * @return string HTML fragment.
     */
    protected function allocated_item_field($item) {
        global $USER;

        $field = "(SELECT a$item.id
                    FROM {assignfeedback_wtpeer_allocs} a$item
                    WHERE a$item.userid = u.id AND a$item.submission = s.id AND a$item.gradertype = '$item' AND a$item.grader = '{$USER->id}' ) AS can_$item ";
        return $field;
    }
    
    
    /**
     * Before adding each row to the table make sure rownum is incremented.
     *
     * @param stdclass $config plugin config settings
     */
    public function set_assess_permissions($config) {
        global $USER;
        $context = $this->get_assignment()->get_context();
    
        $this->canmanage = has_capability('assignfeedback/wtpeer:manage', $context);
        
        $this->hasgrade = $this->get_assignment()->can_grade();

        $view = $this->canmanage ? true : false;
        $this->cangrade = array('auto'=>$view,'peer'=>$view,'tutor'=>$view,'grader'=>$view);
        
        $view = $this->canmanage ? true : false;
        $submission = false;
        if($config->peeraccessmode > 0) {
            $submission = $this->get_assignment()->get_user_submission($USER->id, false);
            if($config->peeraccessmode == 1) {
                $submission = ($submission->status == ASSIGN_SUBMISSION_STATUS_NEW) ? false : true;
            } elseif($config->peeraccessmode == 2) {
                $submission = ($submission->status == ASSIGN_SUBMISSION_STATUS_SUBMITTED) ? true : false;
            }
        } 
        
        if($config->peeraccessmode == 0 || $submission == true) {
            $now = time();
            foreach($this->cangrade as $item => $value) {
                if($config->{'startgrading_'.$item} < $now && $config->{'endgrading_'.$item} > $now) {
                    $this->cangrade[$item] = $this->canmanage || has_capability('assignfeedback/wtpeer:'.$item.'grade', $context);
                }
            }
        }
        
        $view = false;
        if($config->publishassessment == 0) {
            $view = false;
        } elseif($config->publishassessment == 1) {
            $view = true;
        } elseif($config->publishassessment == 2) {
            $view = (time() > $config->publishassessmentdate) ? true: false;
        }
        $view = $this->canmanage || $view;
        $this->canviewassessments = array('auto'=>$view,'peer'=>$view,'tutor'=>$view,'grader'=>$view);

        $view = $this->canmanage ? true : false;
        $this->canviewmarkers = array('auto'=>$view,'peer'=>$view,'tutor'=>$view,'grader'=>$view);
        if($publishmarkers = explode(',', $config->publishmarkers)) {
            foreach($publishmarkers as $item) {
                $this->canviewmarkers[$item] = true;
            }
        }
        
        $this->canallocate = has_capability('assignfeedback/wtpeer:manageallocations', $context);
        
        $view = false;
        if($config->publishgrade == 0) {
            $view = false;
        } elseif($config->publishgrade == 1) {
            $view = true;
        } elseif($config->publishgrade == 2) {
            $view = (time() > $config->publishgradedate) ? true: false;
        }
        $this->canviewgrade = $this->canmanage || $view;
        
        $this->viewotherallocs = $this->canmanage || has_capability('assignfeedback/wtpeer:viewotherallocs', $context);
        $this->viewothergrades = $this->canmanage || has_capability('assignfeedback/wtpeer:viewothergrades', $context);
        //$this->viewothergrades = true;
        
        $this->showexplain = 0;
        $this->gradingmethod = '';
        if($gradingmanager = get_grading_manager($this->get_assignment()->get_context(), 'assignfeedback_wtpeer', 'assessments')) {
            if ($controller = $gradingmanager->get_active_controller()) {
                $this->showexplain = 1;
                $this->gradingmethod = $gradingmanager->get_active_method(); 
            }
        }

    }   
    
    /**
     * overridden constructor keeps a reference to the assignment class that is displaying this table
     *
     * @param assign_feedback_wtpeer $plugin The wtpeer assignfeedback class
     * @param int $perpage how many per page
     * @param string $filter The current filter
     * @param int $rowoffset For showing a subsequent page of results
     * @param bool $quickgrading Is this table wrapped in a quickgrading form?
     * @param stdclass $config plugin config settings
     * @param string $downloadfilename
     */
    public function __construct(assign_feedback_wtpeer $plugin,
                                $perpage,
                                $filter,
                                $markerfilter,
                                $rowoffset,
                                $quickgrading,
                                $config,
                                $downloadfilename = null) {
        global $CFG, $PAGE, $DB, $USER;
        parent::__construct('mod_assign_grading');
        $this->is_persistent(true);
        $this->plugin = $plugin;
        $assignment = $plugin->get_assignment();

        // Check permissions up front.
        $this->hasgrantextension = has_capability('mod/assign:grantextension',
                                                  $this->get_assignment()->get_context());
                                                  
        $this->set_assess_permissions($config);                                          
        $this->hasgrade = $this->get_assignment()->can_grade();

        // Check if we have the elevated view capablities to see the blind details.
        $this->hasviewblind = has_capability('mod/assign:viewblinddetails',
                $this->get_assignment()->get_context());

        foreach ($assignment->get_feedback_plugins() as $plugin) {
            if ($plugin->is_visible() && $plugin->is_enabled()) {
                foreach ($plugin->get_grading_batch_operations() as $action => $description) {
                    if (empty($this->plugingradingbatchoperations)) {
                        $this->plugingradingbatchoperations[$plugin->get_type()] = array();
                    }
                    $this->plugingradingbatchoperations[$plugin->get_type()][$action] = $description;
                }
            }
        }
        $this->perpage = $perpage;

        $this->quickgrading = $quickgrading && $this->hasgrade;
        $this->output = $PAGE->get_renderer('assignfeedback_wtpeer');

        $urlparams = array('action'=>'viewpluginpage', 'id'=>$assignment->get_course_module()->id,
                            'plugin'=>'wtpeer', 'pluginsubtype'=>'assignfeedback', 'pluginaction'=>'reviewtable');
        $url = new moodle_url($CFG->wwwroot . '/mod/assign/view.php', $urlparams);
        $this->define_baseurl($url);

        // Do some business - then set the sql.
        $currentgroup = groups_get_activity_group($assignment->get_course_module(), true);

        if ($rowoffset) {
            $this->rownum = $rowoffset - 1;
        }

        $users = array_keys( $assignment->list_participants($currentgroup, true));
        if (count($users) == 0) {
            // Insert a record that will never match to the sql is still valid.
            $users[] = -1;
        }

        $params = array();
        $params['assignmentid1'] = (int)$this->get_assignment()->get_instance()->id;
        $params['assignmentid2'] = (int)$this->get_assignment()->get_instance()->id;
        $params['assignmentid3'] = (int)$this->get_assignment()->get_instance()->id;
        
        $weights = $this->plugin->get_assessment_weights();

        $extrauserfields = get_extra_user_fields($this->get_assignment()->get_context());

        $fields = user_picture::fields('u', $extrauserfields) . ', ';
        $fields .= 'u.id as userid, ';
        $fields .= 's.status as status, ';
        $fields .= 's.id as submissionid, ';
        $fields .= 's.timecreated as firstsubmission, ';
        $fields .= 's.timemodified as timesubmitted, ';
        $fields .= 's.attemptnumber as attemptnumber, ';
        $fields .= 'g.id as gradeid, ';
        $fields .= 'g.grade as grade, ';
        $fields .= 'g.timemodified as timemarked, ';
        $fields .= 'g.timecreated as firstmarked, ';
        $fields .= 'g.timecreated as firstmarked, ';
        $fields .= 'uf.extensionduedate as extensionduedate, ';
        $fields .= 'uf.locked as locked ';
        
        foreach($weights as $item => $value) {
            if($value) {
                if($field = $this->weighted_item_field($item, $markerfilter)) {
                    $fields .= ', '.$field;
                }
                if($field = $this->allocated_item_field($item)) {
                    $fields .= ', '.$field;
                }
            }
        }
        
        
        /*
        
        if($weights['auto']) {
            $fields .= $this->weighted_item_field('auto', $markerfilter).', ';
            $fields .= $this->allocated_item_field('auto').', ';
        }
        if($weights['peer']) {
            $fields .= $this->weighted_item_field('peer', $markerfilter).', ';
            $fields .= $this->allocated_item_field('peer').', ';
        }
        if($weights['tutor']) {
            $fields .= $this->weighted_item_field('tutor', $markerfilter).', ';
            $fields .= $this->allocated_item_field('tutor').', ';
        }
        if($weights['grader']) {
            $fields .= $this->weighted_item_field('grader', $markerfilter).', ';
            $fields .= $this->allocated_item_field('grader');
        }
        */
        $from = '{user} u
                         LEFT JOIN {assign_submission} s
                                ON u.id = s.userid
                               AND s.assignment = :assignmentid1
                               AND s.latest = 1
                         LEFT JOIN {assign_grades} g
                                ON u.id = g.userid
                               AND g.assignment = :assignmentid2  
                        ';
                          
        // For group submissions we don't immediately create an entry in the assign_submission table for each user,
        // instead the userid is set to 0. In this case we use a different query to retrieve the grade for the user.
        if ($this->get_assignment()->get_instance()->teamsubmission) {
            $params['assignmentid4'] = (int) $this->get_assignment()->get_instance()->id;
            $grademaxattempt = 'SELECT mxg.userid, MAX(mxg.attemptnumber) AS maxattempt
                                  FROM {assign_grades} mxg
                                 WHERE mxg.assignment = :assignmentid4
                              GROUP BY mxg.userid';
            $from .= 'LEFT JOIN (' . $grademaxattempt . ') gmx
                             ON u.id = gmx.userid
                            AND g.attemptnumber = gmx.maxattempt ';
        } else {
            $from .= 'AND g.attemptnumber = s.attemptnumber ';
        }

        $teamsubmissions = false;
        if($this->get_assignment()->enabledadvancedassign) { // ecastro ULPGC to add support for sorting by groupname
            local_ulpgcassign_gradingtable_group_sql($assignment->get_instance(), $teamsubmissions, $fields, $from);
        }

        $from .= 'LEFT JOIN {assign_user_flags} uf
                         ON u.id = uf.userid
                        AND uf.assignment = :assignmentid3';
                        
        $userparams = array();
        $userindex = 0;

        list($userwhere, $userparams) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'user');
        $where = 'u.id ' . $userwhere;
        $params = array_merge($params, $userparams);

        if($markerfilter > 0) {
            $from .= "  LEFT JOIN {assignfeedback_wtpeer_grades} wtg ON s.submision = wtg.submission AND s.userid = wtg.userid AND wtg.grader = :markerg
                        LEFT JOIN {assignfeedback_wtpeer_allocs} wta ON s.submision = wta.submission AND s.userid = wta.userid AND wtg.grader = :markera ";
            $where .= " AND (wtg.id IS NOT NULL OR wta.id IS NOT NULL) ";
            $params[':markerg'] = $markerfilter;
            $params[':markera'] = $markerfilter;
        } elseif($markerfilter < 0) {
            $from .= "  LEFT JOIN {assignfeedback_wtpeer_allocs} wta ON s.submision = wta.submission AND s.userid = wta.userid AND wtg.grader = :markera ";
            $where .= " AND (wta.id IS NULL) ";
            $params[':markera'] = $markerfilter;
        }
        
        // The filters do not make sense when there are no submissions, so do not apply them.
        if ($this->get_assignment()->is_any_submission_plugin_enabled()) {
            if ($filter == ASSIGN_FILTER_SUBMITTED) {
                $where .= ' AND (s.timemodified IS NOT NULL AND
                                 s.status = :submitted) ';
                $params['submitted'] = ASSIGN_SUBMISSION_STATUS_SUBMITTED;

            } else if ($filter == ASSIGN_FILTER_NOT_SUBMITTED) {
                $where .= ' AND (s.timemodified IS NULL OR s.status != :submitted) ';
                $params['submitted'] = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
            } else if ($filter == ASSIGN_FILTER_REQUIRE_GRADING) {
                $where .= ' AND (s.timemodified IS NOT NULL AND
                                 s.status = :submitted AND
                                 (s.timemodified >= g.timemodified OR g.timemodified IS NULL OR g.grade IS NULL))';
                $params['submitted'] = ASSIGN_SUBMISSION_STATUS_SUBMITTED;

            } else if (strpos($filter, ASSIGN_FILTER_SINGLE_USER) === 0) {
                $userfilter = (int) array_pop(explode('=', $filter));
                $where .= ' AND (u.id = :userid)';
                $params['userid'] = $userfilter;
            }

            if($this->get_assignment()->enabledadvancedassign) { // adding actual custom filter SQL
                list($where, $params) = local_ulpgcassign_gradingtable_filter_sql($this->get_assignment()->get_instance(), $filter, $where, $params);
            }

        }

        if(!$this->canmanage) {                       
            $where .= ' AND EXISTS (SELECT 1 
                                    FROM {assignfeedback_wtpeer_allocs} a 
                                    WHERE  a.userid = s.userid AND a.submission = s.id  AND a.grader = :student ) ';      
            $params['student'] = $USER->id;
        }
        
        $this->set_sql($fields, $from, $where, $params);

        if ($downloadfilename) {
            $this->is_downloading('csv', $downloadfilename);
        }

        $columns = array();
        $headers = array();

        // Select.
        if (!$this->is_downloading() && $this->hasgrade) {
            $columns[] = 'select';
            $headers[] = get_string('select') .
                    '<div class="selectall"><label class="accesshide" for="selectall">' . get_string('selectall') . '</label>
                    <input type="checkbox" id="selectall" name="selectall" title="' . get_string('selectall') . '"/></div>';
        }

        // User picture.
        if ($this->hasviewblind || !$this->get_assignment()->is_blind_marking()) {
            if (!$this->is_downloading()) {
                $columns[] = 'picture';
                $headers[] = get_string('pictureofuser');
            } else {
                $columns[] = 'recordid';
                $headers[] = get_string('recordid', 'assign');
            }

            // Fullname.
            $columns[] = 'fullname';
            $headers[] = get_string('fullname');

            // Participant # details if can view real identities.
            if ($this->get_assignment()->is_blind_marking()) {
                if (!$this->is_downloading()) {
                    $columns[] = 'recordid';
                    $headers[] = get_string('recordid', 'assign');
                }
            }

            if($this->hasgrade) { // only assign graders should view user details
                foreach ($extrauserfields as $extrafield) {
                    $columns[] = $extrafield;
                    $headers[] = get_user_field_name($extrafield);
                }
            }
        } else {
            // Record ID.
            $columns[] = 'recordid';
            $headers[] = get_string('recordid', 'assign');
        }

        // Submissions column. // Submission plugins.
        if ($assignment->is_any_submission_plugin_enabled()) {
            foreach ($this->get_assignment()->get_submission_plugins() as $plugin) {
                if ($this->is_downloading()) {
                    if ($plugin->is_visible() && $plugin->is_enabled()) {
                        foreach ($plugin->get_editor_fields() as $field => $description) {
                            $index = 'plugin' . count($this->plugincache);
                            $this->plugincache[$index] = array($plugin, $field);
                        }
                    }
                } else {
                    if ($plugin->is_visible() && $plugin->is_enabled() && $plugin->has_user_summary()) {
                        $index = 'plugin' . count($this->plugincache);
                        $this->plugincache[$index] = array($plugin);
                    }
                }
            }
        }
        if($this->plugincache) {
            $columns[] = 'submission';
            $headers[] = get_string('submission', 'assign');
        }

        // Submission status.
        $columns[] = 'status';
        $headers[] = get_string('status', 'assign');
        
        // Team submission columns.
        if ($assignment->get_instance()->teamsubmission) {
            $columns[] = 'team';
            $headers[] = get_string('submissionteam', 'assign');
        }

        if (!$this->is_downloading()) {
            // We have to call this column userid so we can use userid as a default sortable column.
            $columns[] = 'userid';
            $headers[] = get_string('edit');
        }

        // wtpeer items.
        $columns[] = 'assessment';
        $headers[] = get_string('assessmentstatus', 'assignfeedback_wtpeer');
        
        // wtpeer items.
        if($config->weight_auto) {
            $columns[] = 'auto';
            $headers[] = get_string('rowauto', 'assignfeedback_wtpeer');
        }
        
        if($config->weight_peer) {
            $columns[] = 'peer';
            $headers[] = get_string('rowpeer', 'assignfeedback_wtpeer');
        }

        if($config->weight_tutor) {
            $columns[] = 'tutor';
            $headers[] = get_string('rowtutor', 'assignfeedback_wtpeer');
        }

        $config->weight_grader = 100 - ($config->weight_auto + $config->weight_peer + $config->weight_tutor);    
        if($config->weight_grader) {
            $columns[] = 'grader';
            $headers[] = get_string('rowgrader', 'assignfeedback_wtpeer');
        }

        
        // Exclude 'Final grade' column in downloaded grading worksheets.
        if (!$this->is_downloading() && $this->canviewgrade) {
            // Final grade.
            $columns[] = 'finalgrade';
            $headers[] = get_string('finalgrade', 'grades');
        }

        // Set the columns.
        $this->define_columns($columns);
        $this->define_headers($headers);
        foreach ($extrauserfields as $extrafield) {
             $this->column_class($extrafield, $extrafield);
        }
        $this->no_sorting('recordid');
        $this->no_sorting('finalgrade');
        $this->no_sorting('userid');
        $this->no_sorting('select');
        $this->no_sorting('submission');
        $this->no_sorting('assessment');
        $this->no_sorting('auto');
        $this->no_sorting('peer');
        $this->no_sorting('tutor');
        $this->no_sorting('grader');


        if ($assignment->get_instance()->teamsubmission) {
            if(!$teamsubmissions) { // ecastro ULPGC
                $this->no_sorting('team');
            }
        }

        // When there is no data we still want the column headers printed in the csv file.
        if ($this->is_downloading()) {
            $this->start_output();
        }
    }

    
    /**
     * Before adding each row to the table make sure rownum is incremented.
     *
     * @param array $row row of data from db used to make one row of the table.
     * @return array one row for the table
     */
    public function format_row($row) {
        if ($this->rownum < 0) {
            $this->rownum = $this->currpage * $this->pagesize;
        } else {
            $this->rownum += 1;
        }

        return parent::format_row($row);
    }

    /**
     * Add a column with an ID that uniquely identifies this user in this assignment.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_recordid(stdClass $row) {
        return get_string('hiddenuser', 'assign') .
               $this->get_assignment()->get_uniqueid_for_user($row->userid);
    }


    /**
     * Add the userid to the row class so it can be updated via ajax.
     *
     * @param stdClass $row The row of data
     * @return string The row class
     */
    public function get_row_class($row) {
        return 'user' . $row->userid;
    }

    /**
     * Return the number of rows to display on a single page.
     *
     * @return int The number of rows per page
     */
    public function get_rows_per_page() {
        return $this->perpage;
    }

    
    /**
     * For download only - list all the valid options for this custom scale.
     *
     * @param stdClass $row - The row of data
     * @return string A list of valid options for the current scale
     */
    public function col_scale($row) {
        global $DB;

        if (empty($this->scale)) {
            $dbparams = array('id'=>-($this->get_assignment()->get_instance()->grade));
            $this->scale = $DB->get_record('scale', $dbparams);
        }

        if (!empty($this->scale->scale)) {
            return implode("\n", explode(',', $this->scale->scale));
        }
        return '';
    }

    /**
     * Display a grade with scales etc.
     *
     * @param string $grade
     * @param boolean $editable
     * @param int $userid The user id of the user this grade belongs to
     * @param int $modified Timestamp showing when the grade was last modified
     * @return string The formatted grade
     */
    public function display_grade($grade, $editable, $userid = 0, $modified = 0) {
        if ($this->is_downloading()) {
            if ($this->get_assignment()->get_instance()->grade >= 0) {
                if ($grade == -1 || $grade === null) {
                    return '';
                }
                return format_float($grade, 2);
            } else {
                // This is a custom scale.
                $scale = $this->get_assignment()->display_grade($grade, false);
                if ($scale == '-') {
                    $scale = '';
                }
                return $scale;
            }
        }
        return $this->get_assignment()->display_grade($grade, $editable, $userid, $modified);
    }

    /**
     * Get the team info for this user.
     *
     * @param stdClass $row
     * @return string The team name
     */
    public function col_team(stdClass $row) {
        $submission = false;
        $group = false;
        $this->get_group_and_submission($row->id, $group, $submission, -1);
        $status = '';
        if($this->get_assignment()->enabledadvancedassign) { // ecastro ULPGC improve group interface
            if ($submission) {
                $status = $submission->status;
            }
            $status= $this->output->container(get_string('submissionstatus_' . $status, 'assign'),
                                               array('class'=>'submissionstatus' .$status));
        }
        if ($group) {
            return $group->name.' '.$status;
        } else if ($this->get_assignment()->get_instance()->preventsubmissionnotingroup) {
            $usergroups = $this->get_assignment()->get_all_groups($row->id);
            if (count($usergroups) > 1) {
                return get_string('multipleteamsgrader', 'assign');
            } else {
                return get_string('noteamgrader', 'assign');
            }
        }





        return get_string('defaultteam', 'assign');
    }

    /**
     * Use a static cache to try and reduce DB calls.
     *
     * @param int $userid The user id for this submission
     * @param int $group The groupid (returned)
     * @param stdClass|false $submission The stdClass submission or false (returned)
     * @param int $attemptnumber Return a specific attempt number (-1 for latest)
     */
    protected function get_group_and_submission($userid, &$group, &$submission, $attemptnumber) {
        $group = false;
        if (isset($this->submissiongroups[$userid])) {
            $group = $this->submissiongroups[$userid];
        } else {
            $group = $this->get_assignment()->get_submission_group($userid, false);
            $this->submissiongroups[$userid] = $group;
        }

        $groupid = 0;
        if ($group) {
            $groupid = $group->id;
        }

        // Static cache is keyed by groupid and attemptnumber.
        // We may need both the latest and previous attempt in the same page.
        if (isset($this->groupsubmissions[$groupid . ':' . $attemptnumber])) {
            $submission = $this->groupsubmissions[$groupid . ':' . $attemptnumber];
        } else {
            $submission = $this->get_assignment()->get_group_submission($userid, $groupid, false, $attemptnumber);
            $this->groupsubmissions[$groupid . ':' . $attemptnumber] = $submission;
        }
    }


    /**
     * Format a user picture for display.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_picture(stdClass $row) {
        return $this->output->user_picture($row);
    }

    /**
     * Format a user record for display (link to profile).
     *
     * @param stdClass $row
     * @return string
     */
    public function col_fullname($row) {
        if (!$this->is_downloading()) {
            $courseid = $this->get_assignment()->get_course()->id;
            $link= new moodle_url('/user/view.php', array('id' =>$row->id, 'course'=>$courseid));
            $fullname = $this->output->action_link($link, $this->get_assignment()->fullname($row));
        } else {
            $fullname = $this->get_assignment()->fullname($row);
        }

        if (!$this->get_assignment()->is_active_user($row->id)) {
            $suspendedstring = get_string('userenrolmentsuspended', 'grades');
            $fullname .= ' ' . html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/enrolmentsuspended'),
                'title' => $suspendedstring, 'alt' => $suspendedstring, 'class' => 'usersuspendedicon'));
            $fullname = html_writer::tag('span', $fullname, array('class' => 'usersuspended'));
        }
        return $fullname;
    }

    /**
     * Insert a checkbox for selecting the current row for batch operations.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_select(stdClass $row) {
        $selectcol = '<label class="accesshide" for="selectuser_' . $row->userid . '">';
        $selectcol .= get_string('selectuser', 'assign', $this->get_assignment()->fullname($row));
        $selectcol .= '</label>';
        $selectcol .= '<input type="checkbox"
                              id="selectuser_' . $row->userid . '"
                              name="selectedusers"
                              value="' . $row->userid . '"/>';
        $selectcol .= '<input type="hidden"
                              name="grademodified_' . $row->userid . '"
                              value="' . $row->timemarked . '"/>';
        $selectcol .= '<input type="hidden"
                              name="gradeattempt_' . $row->userid . '"
                              value="' . $row->attemptnumber . '"/>';
        return $selectcol;
    }

    /**
     * Insert a checkbox for selecting the current row for batch operations.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_assessment(stdClass $row) {
        global $DB, $USER;
        
        $status = array();
        foreach(array('auto', 'peer', 'tutor', 'grader') as $item) {
            if(isset($this->columns[$item])) {
                $params = array('submission'=>$row->submissionid,'userid'=>$row->userid, 'gradertype'=>$item);
                $graderwhere = '';
                if(!($row->userid == $USER->id || $this->viewotherallocs)) {
                    $params['grader'] = $USER->id;
                    $graderwhere = ' AND g.grader = :grader ';
                }
                $a = new stdClass;
                if($allocs = $DB->count_records('assignfeedback_wtpeer_allocs', $params)) { 
                    $a->allocs = $allocs;                                                    
                    $sql = "SELECT COUNT(g.id)
                            FROM {assignfeedback_wtpeer_grades} g
                            JOIN {assignfeedback_wtpeer_allocs} a ON g.submission = a.submission AND g.userid = a.userid
                                                                    AND g.grader = a.grader AND g.gradertype = a.gradertype
                            WHERE g.submission = :submission AND g.userid = :userid AND g.gradertype = :gradertype $graderwhere ";
                    $a->grades = $DB->count_records_sql($sql, $params);
                    $a->item = get_string('row'.$item, 'assignfeedback_wtpeer');
                    $class = ($a->grades >= $a->allocs) ? 'success' : 'error';
                    $status[] = html_writer::span(get_string('assessallocstatus', 'assignfeedback_wtpeer', $a), $class);
                }
            }
        }

        $link = '';
        if($this->canallocate) {
            $type = '';
            // just a way to find ther first used gradertype
            foreach(array('peer', 'tutor', 'grader', 'auto') as $item) {
                if(isset($this->columns[$item])) {
                    $type = $item;
                    break;
                }
            }
            $urlparams = array('id'=>$this->get_assignment()->get_course_module()->id,
                                'rownum'=>$this->rownum,
                                'plugin'=>'wtpeer',
                                'pluginsubtype'=>'assignfeedback',
                                'action'=>'viewpluginpage',
                                'pluginaction'=>'allocate',
                                'useridlistid' => $this->get_assignment()->get_useridlist_key_id(),
                                'userid'=>$row->userid,
                                'subid'=>$row->submissionid,
                                'type'=>$type);
            $url = new moodle_url('/mod/assign/view.php', $urlparams);
            $name = get_string('allocate', 'assignfeedback_wtpeer');
            $icon = $this->output->pix_icon('i/assignroles', $name, 
                                        'moodle', array('class'=>'iconsmall'));
            $link = $this->output->action_link($url, $icon);
        }
    
        return html_writer::alist($status, array('class'=>'assessmentstatus' )).$link;
    }

    /**
     * Insert a checkbox for selecting the current row for batch operations.
     *
     * @param stdClass $row
     * @param string $item the type of item: peer, tutor, grader
     * @return string
     */
    public function show_wtpeer_item(stdClass $row, $item) {
        global $DB, $USER, $SESSION; 
        
        $o = '';
        
        $grade = '';
        if($row->{'user'.$item}) {
            $grade = $this->display_grade($row->{$item}, false);
        }
        $link = '';
        if($USER->id == $row->id) {
            //print_object("{$row->id} {$row->userid} $item {$this->canmanage} || ( {$this->cangrade[$item]} && {$row->{'can_'.$item}}  ");
        }
        
        if($this->canmanage || ($this->cangrade[$item] && $row->{'can_'.$item} && $row->status !== ASSIGN_SUBMISSION_STATUS_SUBMITTED)) {
            // can grade this item, show the grade & icon
            $urlparams = array('id'=>$this->get_assignment()->get_course_module()->id,
                                'rownum'=>$this->rownum,
                                'plugin'=>'wtpeer',
                                'pluginsubtype'=>'assignfeedback',
                                'action'=>'viewpluginpage',
                                'pluginaction'=>'',
                                'useridlistid' => $this->get_assignment()->get_useridlist_key_id(),
                                'userid'=>$row->userid,
                                'subid'=>$row->submissionid);
            $url = new moodle_url('/mod/assign/view.php', $urlparams);
            $name = $this->get_assignment()->fullname($row);
            $icon = $this->output->pix_icon('gradefeedback',
                                        get_string('gradeuser', 'assign', $name),
                                        'mod_assign', array('class'=>'iconsmall'));
            $url->param('pluginaction', 'grade'.$item);
            $link = $this->output->action_link($url, $icon);
        }

        if($grade || $link) {
            $separator = $this->output->spacer(array(), true);
            $o .= html_writer::div($grade.$separator.$link, ' ddddd  ');
        }
        
        if($this->canviewassessments[$item] && ($row->userid == $USER->id || $this->viewothergrades)) {
            $grades = array();
            if(isset($row->{$item}) && $row->submissionid) {
                $grades = $DB->get_records('assignfeedback_wtpeer_grades', array('userid'=>$row->userid, 'submission'=>$row->submissionid, 'gradertype'=>$item), 
                                                        '', 'grader AS graderid, id, userid, submission, grade, grader, gradertype, timemodified');
                $o .= $this->display_grade($row->{$item}, false);                                            
                $o .= ' ('.count($grades).')';
            }

            if($row->userid == $USER->id || $this->viewotherallocs) {
                $nmarkers = 0;
                if($assigned = $DB->get_records('assignfeedback_wtpeer_allocs', 
                                    array('userid'=>$row->userid, 'submission'=>$row->submissionid, 'gradertype'=>$item))) {
                    $record = clone reset($assigned);
                    $record->grade = -1;
                    $record->grader = 0;
                    foreach($assigned as $grader) {
                        $idx = $grader->grader;
                        if(!isset($grades[$idx])) {
                            $record->grader = $idx;
                            $grades[$idx] = clone $record; 
                        }
                    }
                    $nmarkers = count($assigned);
                }
                if($grades) {
                    foreach($grades as $grade) {
                        $grade->grade = $this->get_assignment()->display_grade($grade->grade, false, $grade->userid);
                        if($this->canviewmarkers[$item]) {
                            $fields = get_all_user_name_fields(true);  
                            $marker = $DB->get_record('user', array('id'=>$grade->grader), 'id, idnumber, '.$fields);
                            $grade->fullname = $this->get_assignment()->fullname($marker);
                        }
                    }            

                    $showexplain = 0; // means only link
                    $gradingmethod = ''; 
                    if(!$gradingdisabled = $this->get_assignment()->grading_disabled($row->userid)) {
                        $showexplain = $this->showexplain; // means only link
                        $gradingmethod = $this->gradingmethod; 
                    }            
                
                    $actionurl = $this->plugin->plugin_action_url('showexplain');
                    $actionurl->param('s', $row->submissionid);
                    $returnurl = new moodle_url('/mod/assign/view.php', array('id' => $this->get_assignment()->get_course_module()->id));
                
                    $assessment = new assignfeedback_wtpeer_item_assessments($this->get_assignment()->get_course_module()->id, // coursemoduleid
                                                                    $this->plugin, //plugin
                                                                    $actionurl,
                                                                    $grades,
                                                                    $this->canviewmarkers[$item],
                                                                    $showexplain, $gradingmethod,
                                                                    false);
                    $markers = $this->output->render($assessment);
                    $o .= print_collapsible_region($markers, 'userlist', 'showhideuserlist_'.$item.'_'.$row->id, get_string('markers', 'assignfeedback_wtpeer', $nmarkers),'userlist', true, true);
                }
            }    
        }
        if(!$o) {
            $o = '-';
        }
        return $o;
    }
    
    /**
     * Format a row for display wtpeer item.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_auto(stdClass $row) {
        return $this->show_wtpeer_item($row, 'auto');
    }
    
    /**
     * Format a row for display wtpeer item.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_peer(stdClass $row) {
        return $this->show_wtpeer_item($row, 'peer');
    }
    
    /**
     * Format a row for display wtpeer item.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_tutor(stdClass $row) {
        return $this->show_wtpeer_item($row, 'tutor');
    }

    /**
     * Format a row for display wtpeer item.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_grader(stdClass $row) {
        return $this->show_wtpeer_item($row, 'grader');
    }
    
    /**
     * Format a column of data for display.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_finalgrade(stdClass $row) {
        $o = '';

        $weights = $this->plugin->get_assessment_weights();
        $grade = 0;
        foreach($weights as $item => $weight) {
            if($weight && isset($row->{$item} )) {
                $grade += $weight/100 * $row->{$item};
            } else {
                if(isset($this->columns[$item])) {
                    return '-';
                }
            }
        }
        
        if($grade && $this->canviewgrade) {
            $assigngrade = $this->get_assignment()->get_user_grade($row->userid, true, $row->attemptnumber);
            $content = $this->display_grade($grade, false, $row->userid, $row->timemarked);
            $class = ($grade == $assigngrade->grade) ? 'success' : 'error';
            $o .= html_writer::span($content, $class);
        } else {
            $o = '-';
        }
        return $o;
    }


    /**
     * Format a column of data for display
     *
     * @param stdClass $row
     * @return string
     */
    public function col_status(stdClass $row) {
        $o = '';

        $instance = $this->get_assignment()->get_instance();

        $due = $instance->duedate;
        if ($row->extensionduedate) {
            $due = $row->extensionduedate;
        }

        $group = false;
        $submission = false;
        $this->get_group_and_submission($row->id, $group, $submission, -1);

        if ($instance->teamsubmission && !$group && !$instance->preventsubmissionnotingroup) {
            $group = true;
        }

        if ($group && $submission) {
            $timesubmitted = $submission->timemodified;
            $status = $submission->status;
            if($this->get_assignment()->enabledadvancedassign && 
                    $instance->teamsubmission && $instance->requireallteammemberssubmit) { // ecastro ULPGC to allow tracking of single member submissions in teams
                if($membersub = $this->get_assignment()->get_user_submission($row->userid, false)) {
                    $status = $membersub->status;
                    $timesubmitted = $membersub->timemodified;
                }
            }
        } else {
            $timesubmitted = $row->timesubmitted;
            $status = $row->status;
        }

        $displaystatus = $status;
        if ($displaystatus == 'new') {
            $displaystatus = '';
        }

        if ($this->get_assignment()->is_any_submission_plugin_enabled()) {
            if ($this->get_assignment()->enabledadvancedassign && $row->grade !== null && $row->grade >= 0) { // ecastro ULPGC
                $graded = '_graded';
            } else {
                $graded = '';
            }
            $o .= $this->output->container(get_string('submissionstatus_' . $displaystatus, 'assign'),
                                           array('class'=>'submissionstatus' .$displaystatus.$graded));
            if($this->cangrade) {
                if ($due && $timesubmitted > $due && $status != ASSIGN_SUBMISSION_STATUS_NEW) {

                    $usertime = format_time($timesubmitted - $due);
                    $latemessage = get_string('submittedlateshort',
                                            'assign',
                                            $usertime);
                    $o .= $this->output->container($latemessage, 'latesubmission');
                }

                if ($row->locked) {
                    $lockedstr = get_string('submissionslockedshort', 'assign');
                    $o .= $this->output->container($lockedstr, 'lockedsubmission');
                }
            }

            // Add status of "grading" if markflow is not enabled.
            if (!$instance->markingworkflow) {
                if ($row->grade !== null && $row->grade >= 0) {
                    $o .= $this->output->container(get_string('graded', 'assign'), 'submissiongraded');
                } elseif($this->cangrade && (!$timesubmitted || $status == ASSIGN_SUBMISSION_STATUS_NEW)) {
                    $now = time();
                    if ($due && ($now > $due) && ($this->get_assignment()->enabledadvancedassign && ($now < $instance->cutoffdate))) { // ecastro ULPGC
                        $overduestr = get_string('overdue', 'assign', format_time($now - $due));
                        $o .= $this->output->container($overduestr, 'overduesubmission');
                    }
                }
            }

        }

        if($this->cangrade) {
            if ($instance->markingworkflow) {
               // $o .= $this->col_workflowstatus($row);
            }

            if ($row->extensionduedate) {
                $userdate = userdate($row->extensionduedate);
                $extensionstr = get_string('userextensiondate', 'assign', $userdate);
                $o .= $this->output->container($extensionstr, 'extensiondate');
            }
        }

        if ($this->is_downloading()) {
            $o = strip_tags(rtrim(str_replace('</div>', ' - ', $o), '- '));
        }

        return $o;
    }

    /**
     * Format a column of data for display.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_userid(stdClass $row) {
        global $USER;

        $edit = '';
        $actions = array();
        $urlparams = array('id'=>$this->get_assignment()->get_course_module()->id,
                            'rownum'=>$this->rownum,
                            'plugin'=>'wtpeer',
                            'pluginsubtype'=>'assignfeedback',
                            'action'=>'viewpluginpage',
                            'pluginaction'=>'',
                            'useridlistid' => $this->get_assignment()->get_useridlist_key_id(),
                            'userid'=>$row->userid,
                            'subid'=>$row->submissionid,
                            );
        $url = new moodle_url('/mod/assign/view.php', $urlparams);
        $noimage = null;
        $description = get_string('grade');
        
        foreach(array('auto', 'peer', 'tutor', 'grader') as $item) {
            if($this->canmanage || (isset($this->columns[$item]) && $this->cangrade[$item] && $row->{'can_'.$item})) {
        
            $name = 'grade'.$item;
            $description = get_string($name, 'assignfeedback_wtpeer');
                if(isset($this->columns[$item]) && ($this->canmanage || $row->{'can_'.$item})) {
                    $url->param('pluginaction', $name);
                    $actions[$name] = new action_menu_link_secondary(
                        $url,
                        $noimage,
                        $description
                    );
                }
            }
        }

        if($this->canallocate) {
            $type = '';
            // just a way to find ther first used gradertype
            foreach(array('auto', 'peer', 'tutor', 'grader') as $item) {
                if(isset($this->columns[$item])) {
                    $type = $item;
                    break;
                }
            }
            if($type) {
                $description = get_string('allocate', 'assignfeedback_wtpeer');
                $url->param('pluginaction', 'allocate');
                $url->param('type', $item);
                $actions['allocate'] = new action_menu_link_secondary(
                    $url,
                    $noimage,
                    $description
                );
            }
        }

        if(isset($this->columns['finalgrade'])) {
            $calculate = true;
            foreach(array('auto', 'peer', 'tutor', 'grader') as $item) {
                if(isset($this->columns[$item]) && !isset($row->{$item})) {
                    $calculate = false;
                    break;
                }
            }
            if($calculate) {
                $description = get_string('calculate', 'assignfeedback_wtpeer');
                $url->param('pluginaction', 'calculate');
                $actions['calculate'] = new action_menu_link_secondary(
                    $url,
                    $noimage,
                    $description
                );
            }
        }

        $menu = new action_menu();
        $menu->set_owner_selector('.gradingtable-actionmenu');
        $menu->set_alignment(action_menu::TL, action_menu::BL);
        $menu->set_constraint('.gradingtable > .no-overflow');
        $menu->set_menu_trigger(get_string('edit'));
        foreach ($actions as $action) {
            $menu->add($action);
        }

        // Prioritise the menu ahead of all other actions.
        $menu->prioritise = true;

        $edit .= $this->output->render($menu);

        return $edit;
    }

    /**
     * Write the plugin summary with an optional link to view the full feedback/submission.
     *
     * @param assign_plugin $plugin Submission plugin or feedback plugin
     * @param stdClass $item Submission or grade
     * @param string $returnaction The return action to pass to the
     *                             view_submission page (the current page)
     * @param string $returnparams The return params to pass to the view_submission
     *                             page (the current page)
     * @return string The summary with an optional link
     */
    private function format_plugin_summary_with_link(assign_plugin $plugin,
                                                     stdClass $item,
                                                     $returnaction,
                                                     $returnparams) {
        $link = '';
        $showviewlink = false;

        $summary = $plugin->view_summary($item, $showviewlink);
        
        $summary = str_replace('assignsubmission_file/submission_files', 'assignfeedback_wtpeer/submission_files', $summary);
        
        $separator = '';
        if ($showviewlink) {
            $viewstr = get_string('view' . substr($plugin->get_subtype(), strlen('assign')), 'assign');
            $icon = $this->output->pix_icon('t/preview', $viewstr);
            $urlparams = array('id' => $this->get_assignment()->get_course_module()->id,
                                                     'plugin'=>$this->plugin->get_type(),
                                                     'pluginsubtype'=>$this->plugin->get_subtype(),
                                                     'action'=>  'viewpluginpage',
                                                     'sid'=>$item->id,                                                     
                                                     'alien'=>$plugin->get_type(),
                                                     'pluginaction'=>'showaliensub');
                                                     
            $url = new moodle_url('/mod/assign/view.php', $urlparams);
            $link = $this->output->action_link($url, $icon);
            $separator = $this->output->spacer(array(), true);
        }

        if($summary) {
        return $link . $separator . $summary;
        }
        return '';
    }


    /**
     * Format the submissiona  column.
     *
     * @param stdClass $row The submission row
     * @return mixed string or NULL
     */
    public function col_submission(stdClass $row) {
        // For extra user fields the result is already in $row.
        if (empty($this->plugincache)) {
            return '';
        }
        
        $submitted = array();
        $names = array();
        
        // This must be a plugin field.
        foreach($this->plugincache as $plugincache) {
            $plugin = $plugincache[0];
            $field = null;
            if (isset($plugincache[1])) {
                $field = $plugincache[1];
            }

            if ($plugin->is_visible() && $plugin->is_enabled()) {
                if ($plugin->get_subtype() == 'assignsubmission') {
                    $plugintype = $plugin->get_type();
                    if($plugintype == 'comments' || $plugintype == 'peers') {
                        continue;
                    }
                    if ($this->get_assignment()->get_instance()->teamsubmission) {
                        $group = false;
                        $submission = false;

                        $this->get_group_and_submission($row->id, $group, $submission, -1);
                        if ($submission) {
                            if ($submission->status == ASSIGN_SUBMISSION_STATUS_REOPENED) {
                                // For a newly reopened submission - we want to show the previous submission in the table.
                                $this->get_group_and_submission($row->id, $group, $submission, $submission->attemptnumber-1);
                            }
                            if (isset($field)) {
                                $submitted[$plugintype] = $plugin->get_editor_text($field, $submission->id);
                                //return $plugin->get_editor_text($field, $submission->id);
                            } else {
                                $submitted[$plugintype] = $this->format_plugin_summary_with_link($plugin,
                                                                                                        $submission,
                                                                                                        'grading',
                                                                                                        array());
                            }
                        }
                    } else if ($row->submissionid) {
                        if ($row->status == ASSIGN_SUBMISSION_STATUS_REOPENED) {
                            // For a newly reopened submission - we want to show the previous submission in the table.
                            $submission = $this->get_assignment()->get_user_submission($row->userid, false, $row->attemptnumber - 1);
                        } else {
                            $submission = new stdClass();
                            $submission->id = $row->submissionid;
                            $submission->timecreated = $row->firstsubmission;
                            $submission->timemodified = $row->timesubmitted;
                            $submission->assignment = $this->get_assignment()->get_instance()->id;
                            $submission->userid = $row->userid;
                            $submission->attemptnumber = $row->attemptnumber;
                        }
                        // Field is used for only for import/export and refers the the fieldname for the text editor.
                        if (isset($field)) {
                            $submitted[$plugintype] = $plugin->get_editor_text($field, $submission->id);
                            //return $plugin->get_editor_text($field, $submission->id);
                        } else {
                            $submitted[$plugintype] = $this->format_plugin_summary_with_link($plugin,
                                                                                                    $submission,
                                                                                                    'grading',
                                                                                                    array());
                        }
                    }
                    $names[$plugintype] = $plugin->get_name();
                }
            }
        }
        
        if(count($submitted) > 1) {
            foreach($submitted as $type => $content) {
                if(strip_tags($content)) {
                    $submitted[$type] = html_writer::div($names[$type].' '.$content, ' assignsubmission_'.$type);
                }
            }
            return implode('', $submitted);
        } elseif($submitted) {
            return reset($submitted);
        }
        return '';
    }

    /**
     * Using the current filtering and sorting - load all rows and return a single column from them.
     *
     * @param string $columnname The name of the raw column data
     * @return array of data
     */
    public function get_column_data($columnname) {
        $this->setup();
        $this->currpage = 0;
        $this->query_db($this->tablemaxrows);
        $result = array();
        foreach ($this->rawdata as $row) {
            $result[] = $row->$columnname;
        }
        return $result;
    }

    
    /**
     * Return things to the table.
     *
     * @return string the assignment class object
     */
    public function get_assignment() {
        return $this->plugin->get_assignment();
    }
    
    
    /**
     * Return things to the renderer.
     *
     * @return string the assignment name
     */
    public function get_assignment_name() {
        return $this->get_assignment()->get_instance()->name;
    }

    /**
     * Return things to the renderer.
     *
     * @return int the course module id
     */
    public function get_course_module_id() {
        return $this->get_assignment()->get_course_module()->id;
    }

    /**
     * Return things to the renderer.
     *
     * @return int the course id
     */
    public function get_course_id() {
        return $this->get_assignment()->get_course()->id;
    }

    /**
     * Return things to the renderer.
     *
     * @return stdClass The course context
     */
    public function get_course_context() {
        return $this->get_assignment()->get_course_context();
    }

    /**
     * Return things to the renderer.
     *
     * @return bool Does this assignment accept submissions
     */
    public function submissions_enabled() {
        return $this->get_assignment()->is_any_submission_plugin_enabled();
    }

    /**
     * Return things to the renderer.
     *
     * @return bool Can this user view all grades (the gradebook)
     */
    public function can_view_all_grades() {
        $context = $this->get_assignment()->get_course_context();
        return has_capability('gradereport/grader:view', $context) &&
               has_capability('moodle/grade:viewall', $context);
    }

    /**
     * Always return a valid sort - even if the userid column is missing.
     * @return array column name => SORT_... constant.
     */
    public function get_sort_columns() {
        $result = parent::get_sort_columns();
        $result = array_merge($result, array('userid' => SORT_ASC));
        return $result;
    }

    /**
     * Override the table show_hide_link to not show for select column.
     *
     * @param string $column the column name, index into various names.
     * @param int $index numerical index of the column.
     * @return string HTML fragment.
     */
    protected function show_hide_link($column, $index) {
        if ($index > 0 || !$this->hasgrade) {
            return parent::show_hide_link($column, $index);
        }
        return '';
    }

    /**
     * Overides setup to ensure it will only run a single time.
     */
    public function setup() {
        // Check if the setup function has been called before, we should not run it twice.
        // If we do the sortorder of the table will be broken.
        if (!empty($this->setup)) {
            return;
        }
        parent::setup();

        if($this->get_assignment()->enabledadvancedassign) {
            global $SESSION;
            $SESSION->nameformat = local_ulpgcassign_nameformat(array_keys($this->get_sort_columns()));
        }
    }
}
