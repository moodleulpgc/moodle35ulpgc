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
 * This file contains the definition for the peers submissions table which subclassses gradingtable
 *
 * @package   mod_assign
 * @subpackage   assignsubmission_peers
 * @copyright 2016 Enrique Castro {@ ULPGC}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extends table_sql to provide a table of assignment submissions
 *
 * @package   mod_assign
 * @subpackage   assignsubmission_peers
 * @copyright 2016 Enrique Castro {@ ULPGC}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignsubmission_peers_table extends assign_grading_table implements renderable {

    /**
     * overridden constructor keeps a reference to the assignment class that is displaying this table
     *
     * @param assign $assignment The assignment class
     * @param int $perpage how many per page
     * @param string $filter The current filter
     * @param int $rowoffset For showing a subsequent page of results
     * @param bool $quickgrading Is this table wrapped in a quickgrading form?
     * @param string $downloadfilename
     */
    public function __construct(assign $assignment,
                                $perpage,
                                $filter,
                                $rowoffset,
                                $quickgrading = 0,
                                $downloadfilename = null) {
        global $CFG, $PAGE, $DB, $USER;
        parent::__construct($assignment,
                                $perpage,
                                $filter,
                                $rowoffset,
                                0,
                                null);

        $this->assignment = $assignment;
        $instance = $assignment->get_instance();
        $cm = $assignment->get_course_module();
        $currentgroup = groups_get_activity_group($cm, true);
        $this->output = $PAGE->get_renderer('assignsubmission_peers');

        //http://localhost/moodle31ulpgc/mod/assign/view.php?id=311&action=viewpluginpage&plugin=peers&pluginsubtype=assignsubmission&pluginaction=view&group=0
        $urlparams = array('action'=>'viewpluginpage', 'plugin'=>'peers', 'pluginsubtype'=>'assignsubmission', 'pluginaction'=>'view',
            'id'=>$assignment->get_course_module()->id);
        $url = new moodle_url($CFG->wwwroot . '/mod/assign/view.php', $urlparams);
        $this->define_baseurl($url);
        
        $columns = array();
        $headers = array();

        // User picture
        $columns[] = 'picture';
        $headers[] = get_string('pictureofuser');

        // Fullname
        $columns[] = 'fullname';
        $headers[] = get_string('fullname');

        // Group / Team
        if ($instance->teamsubmission) {
            $columns[] = 'team';
            $headers[] = get_string('submissionteam', 'assign');
        }

        // Submission plugins.
        
        $this->plugincache = array();
        if ($assignment->is_any_submission_plugin_enabled()) {
            $columns[] = 'timesubmitted';
            $headers[] = get_string('lastmodifiedsubmission', 'assign');

            foreach ($assignment->get_submission_plugins() as $plugin) {
                if ($plugin->is_visible() && $plugin->is_enabled() && $plugin->has_user_summary() 
                        && ($plugin->get_type() != 'peers') && ($plugin->get_type() != 'comments')) {
                    $index = 'plugin' . count($this->plugincache);
                    $this->plugincache[$index] = array($plugin);
                    $columns[] = $index;
                    $headers[] = $plugin->get_name();
                }
            }
        }

        $users = array_keys($assignment->list_participants($currentgroup, true));
        if (count($users) == 0) {
            // insert a record that will never match to the sql is still valid.
            $users[] = -1;
        }


        $params = array();
        $params['assignmentid1'] = (int)$instance->id;

        $fields = user_picture::fields('u') . ', u.id as userid, u.firstname as firstname, u.lastname as lastname, ';
        $fields .= 's.status as status, s.id as submissionid, s.timecreated as firstsubmission, s.timemodified as timesubmitted, s.attemptnumber ';
        $from = '{user} u LEFT JOIN {assign_submission} s ON u.id = s.userid AND s.assignment = :assignmentid1 AND s.latest = 1';


        $teamsubmissions = false;
        if ($instance->teamsubmission && $instance->teamsubmissiongroupingid) {  
            $groupingid = $instance->teamsubmissiongroupingid;

            $grouplist = ' ( 0 ) ';
            if ($groups = array_keys(groups_get_all_groups($instance->course, 0, $groupingid, 'g.id'))) {
                $grouplist = '(0, '. implode(',', $groups). ' ) ' ;
            }
            $fields .= ', s.groupid, gg.name as team ';
            $from .= " LEFT JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid IN $grouplist " ;
            $from .= ' LEFT JOIN {groups} gg ON gm.groupid = gg.id ' ;
            $teamsubmissions = true;
        }


        $userparams = array();
        $userindex = 0;

        list($userwhere, $userparams) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'user');
        $where = 'u.id ' . $userwhere;
        $params = array_merge($params, $userparams);

        if ($filter == ASSIGN_FILTER_SUBMITTED) {
            $where .= ' AND s.timecreated > 0 ';
        }
        if (strpos($filter, ASSIGN_FILTER_SINGLE_USER) === 0) {
            $userfilter = (int) array_pop(explode('=', $filter));
            $where .= ' AND (u.id = :userid)';
            $params['userid'] = $userfilter;
        }
        $this->set_sql($fields, $from, $where." AND s.status != 'draft' " , $params);


        // Set the columns.
        $this->define_columns($columns);
        $this->define_headers($headers);

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
    public function format_rorrw($row) {
        if ($this->rownum < 0) {
            $this->rownum = $this->currpage * $this->pagesize;
        } else {
            $this->rownum += 1;
        }

        return flexible_table::format_row($row);
    }

    /**
     * Format the submission and feedback columns.
     *
     * @param string $colname The column name
     * @param stdClass $row The submission row
     * @return mixed string or NULL
     */
    public function other_cols($colname, $row) {
    
        // For extra user fields the result is already in $row.
        if (empty($this->plugincache[$colname])) {
            return $row->$colname;
        }

        // This must be a plugin field.
        $plugincache = $this->plugincache[$colname];

        $plugin = $plugincache[0];

        $field = null;
        if (isset($plugincache[1])) {
            $field = $plugincache[1];
        }

        if ($plugin->is_visible() && $plugin->is_enabled() && $plugin->get_subtype() == 'assignsubmission' &&
                $plugin->has_user_summary() && ($plugin->get_type() != 'peers') && ($plugin->get_type() != 'comments')) {
                if ($this->assignment->get_instance()->teamsubmission) {
                    $group = false;
                    $submission = false;

                    $this->get_group_and_submission($row->id, $group, $submission, -1);
                    if ($submission) {
                        if ($submission->status == ASSIGN_SUBMISSION_STATUS_REOPENED) {
                            // For a newly reopened submission - we want to show the previous submission in the table.
                            $this->get_group_and_submission($row->id, $group, $submission, $submission->attemptnumber-1);
                        }
                        if (isset($field)) {
                            return $plugin->get_editor_text($field, $submission->id);
                        }
                        return $this->format_plugin_summary_with_link($plugin,
                                                                      $submission,
                                                                      'grading',
                                                                      array());
                    }
                } else if ($row->submissionid) {
                    if ($row->status == ASSIGN_SUBMISSION_STATUS_REOPENED) {
                        // For a newly reopened submission - we want to show the previous submission in the table.
                        $submission = $this->assignment->get_user_submission($row->userid, false, $row->attemptnumber - 1);
                    } else {
                        $submission = new stdClass();
                        $submission->id = $row->submissionid;
                        $submission->timecreated = $row->firstsubmission;
                        $submission->timemodified = $row->timesubmitted;
                        $submission->assignment = $this->assignment->get_instance()->id;
                        $submission->userid = $row->userid;
                        $submission->attemptnumber = $row->attemptnumber;
                    }
                    // Field is used for only for import/export and refers the the fieldname for the text editor.
                    if (isset($field)) {
                        return $plugin->get_editor_text($field, $submission->id);
                    }
                    return $this->format_plugin_summary_with_link($plugin,
                                                                  $submission,
                                                                  'grading',
                                                                  array());
                }
            
        }
        return '';
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
        global $CFG;
        $link = '';
        $showviewlink = false;
            $returnparams = array('plugin'=>'peers', 'pluginsubtype'=>'assignsubmission', 'pluginaction'=>'view');
        $summary = $plugin->view_summary($item, $showviewlink);
        $separator = '';
        if ($showviewlink) {
            $viewstr = get_string('view' . substr($plugin->get_subtype(), strlen('assign')), 'assign');
            $icon = $this->output->pix_icon('t/preview', $viewstr);
            $urlparams = array('id' => $this->assignment->get_course_module()->id,
                                                     'plugin'=>'peers',
                                                     'pluginsubtype'=>'assignsubmission',
                                                     'action'=>  'viewpluginpage',
                                                     'sid'=>$item->id,                                                     
                                                     'alien'=>$plugin->get_type(),
                                                     'pluginaction'=>'viewother');
            $url = new moodle_url('view.php', $urlparams);
            $link = $this->output->action_link($url, $icon);
            $separator = $this->output->spacer(array(), true);
        }

        return $link . $separator . $summary;
    }
}
