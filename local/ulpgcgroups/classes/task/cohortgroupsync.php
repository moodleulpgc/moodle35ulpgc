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
 * @package local_ulpgcgroups
 * @author James McQuillan <james.mcquillan@remote-learner.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_ulpgcgroups\task;

/**
 * Scheduled task to sync users with Azure AD.
 */
class cohortgroupsync extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_cohortsyncgroups', 'local_ulpgcgroups');
    }


    protected function mtrace($msg) {
        mtrace('...... '.$msg);
    }

    /**
     * Do the job.
     */
    public function execute() {
        global $CFG, $DB;
        
        $config = get_config('local_ulpgcgroups');
        
        mtrace('local_ulpgcgroups cohort frontpage groups synch start');
        
        if(!$config->enablefpgroupsfromcohort) {
            $this->mtrace('local_ulpgcgroups cohort frontpage group synch disabled ');
            return;
        } 
        
        if(!$config->fpgroupscohorts) {
            $this->mtrace('local_ulpgcgroups cohort frontpage group synch: no cohorts ');
            return;
        } 
        
        $cohorts = $DB->get_records_list('cohort', 'id', explode(',',$config->fpgroupscohorts), 'id,name,idnumber');
        
        if(!$cohorts) {
            $this->mtrace('local_ulpgcgroups cohort frontpage group synch: no cohorts ');
            return;
        } 
        
        include_once($CFG->dirroot.'/group/lib.php');
        include_once($CFG->libdir.'/grouplib.php');
        
        // Synchronize each cohort/group 
        foreach($cohorts as $cohort) {
            // check groups exists, add if not 
            $group = groups_get_group_by_idnumber(SITEID, $cohort->idnumber);
            if(!isset($group->id)) {
                $group = new \stdClass();
                $group->name = $cohort->name;
                $group->idnumber = $cohort->idnumber;
                $group->courseid = SITEID;
                foreach(array('description', 'descriptionformat', 'enrolmentkey', 'picture', 'hidepicture') as $field) {
                    $group->{$field} = '';
                }
                if($group->id = groups_create_group($group)) {
                    $this->mtrace("Added group {$group->id} with idnumber {$cohort->idnumber}");
                }
            }
            
            // add new cohort members 
            // include u.deleted to avoid new BD calls on groups_add_members
            $sql = "SELECT chm.id AS chid, u.id, u.idnumber, u.deleted
                    FROM {cohort_members} chm 
                    JOIN {user} u ON u.id = chm.userid
                    LEFT JOIN {groups_members} gm ON gm.userid = chm.userid AND gm.groupid = :groupid AND gm.component = :component 
                    WHERE chm.cohortid = :cohortid AND gm.id IS NULL  ";
            $params = array('cohortid'=>$cohort->id, 'groupid'=>$group->id, 'component'=>'local_ulpgcgroups');
            if($users = $DB->get_records_sql($sql, $params)) {
                foreach($users as $user) {
                    groups_add_member($group, $user, 'local_ulpgcgroups'); 
                }
                $this->mtrace('Added '.count($users)." users to group {$group->id} with idnumber {$cohort->idnumber}");
            }
            
            // remove group members no longer belonging to cohort
            $sql = "SELECT gm.id, gm.userid 
                    FROM {groups_members} gm 
                    LEFT JOIN {cohort_members} chm ON gm.userid = chm.userid AND chm.cohortid = :cohortid
                    WHERE gm.groupid = :groupid AND gm.component = :component AND chm.id IS NULL  ";
            if($users = $DB->get_records_sql_menu($sql, $params)) {
                list($insql, $params) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED);
                $select = "groupid = :groupid AND component = :component AND userid $insql";
                $params['groupid'] = $group->id;
                $params['component'] = 'local_ulpgcgroups';
                
                $DB->delete_records_select('groups_members', $select, $params);
                $this->mtrace('Deleted '.count($users)." users from group {$group->id} with idnumber {$cohort->idnumber}");
            }
        } 
        
        mtrace('local_ulpgcgroups cohort frontpage groups synch finished.');
        return true;
    }
}
