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
class rolegroupsync extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_rolesyncgroups', 'local_ulpgcgroups');
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
        
        mtrace('local_ulpgcgroups frontpage groups synch by role start');
        
        if(!$config->fpgroupsenrolmentkey) {
            $this->mtrace('local_ulpgcgroups frontpage group synch by role disabled ');
            return;
        } 

        if (!empty($config->fpgroupsenrolmentkey)) {
            $select = " courseid = :courseid AND ".$DB->sql_like('enrolmentkey', ':enrolmentkey');
            if($groups = $DB->get_records_select('groups', $select, array('courseid'=>SITEID, 'enrolmentkey'=>$config->fpgroupsenrolmentkey.'%'))) {
                include_once($CFG->dirroot.'/group/lib.php');
                include_once($CFG->libdir.'/grouplib.php');        
                foreach($groups as $group) {
                    $count = 0;
                    if($group->idnumber) {
                        $field = 'roles_'.$group->idnumber;
                        $roles = explode(',', $config->$field);
                        list($insql, $params) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'r'); 
                        list($inctx, $ctxparams) = $DB->get_in_or_equal(array(CONTEXT_COURSE, CONTEXT_COURSECAT), SQL_PARAMS_NAMED, 'ctx'); 
                        $params = array_merge($params, $ctxparams);
                        
                        // adding non exisisting members 
                        $sql = "SELECT u.id, u.idnumber, u.deleted
                                FROM {user} u
                                JOIN {role_assignments} ra ON u.id = ra.userid AND ra.roleid $insql
                                JOIN {context} ctx ON ctx.id = ra.contextid
                                LEFT JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid = :groupid AND gm.component = :component
                                WHERE ctx.contextlevel $inctx AND  gm.id IS NULL 
                                GROUP BY u.id ";
                        $params['groupid'] = $group->id;      
                        $params['component'] = 'local_ulpgcgroups';      
                        if($users = $DB->get_records_sql($sql, $params)) {
                            foreach($users as $user)
                            /// the user has role for this group, add to it
                            $success = groups_add_member($group, $user, 'local_ulpgcgroups', 0);
                            if($success) {
                                $count++;
                            }
                            $this->mtrace("Added $count users to group {$group->id} with idnumber {$group->idnumber}");
                        }
                        
                        // removing exisisting members no longer with role 
                        $sql = "SELECT gm.id, gm.userid 
                                FROM {groups_members} gm 
                                WHERE gm.groupid = :groupid AND gm.component = :component 
                                        AND NOT EXISTS (SELECT 1 FROM {role_assignments} ra 
                                                        JOIN {context} ctx ON ctx.id = ra.contextid
                                                        WHERE ra.userid = gm.userid AND ctx.contextlevel $inctx AND ra.roleid $insql  )
                                ";
                        if($users = $DB->get_records_sql($sql, $params)) {                        
                            list($insql, $params) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED);
                            $select = "groupid = :groupid AND component = :component AND userid $insql";
                            $params['groupid'] = $group->id;
                            $params['component'] = 'local_ulpgcgroups';
                            
                            $DB->delete_records_select('groups_members', $select, $params);
                            $this->mtrace('Deleted '.count($users)." users from group {$group->id} with idnumber {$group->idnumber}");                        
                        }
                    }                
                
                }
            }
            mtrace('local_ulpgcgroups frontpage groups synch by role finished.');
            return true;
        }
    }
}
