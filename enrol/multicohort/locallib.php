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
 * Local stuff for multicohort enrolment plugin.
 *
 * @package    enrol_multicohort
 * @copyright  2016 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/enrol/locallib.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/enrol/multicohort/lib.php');

/**
 * Event handler for multicohort enrolment plugin.
 *
 * We try to keep everything in sync via listening to events,
 * it may fail sometimes, so we always do a full sync in cron too.
 */
class enrol_multicohort_handler {

    protected static function affected_instances_sql($cohortid) {
        global $DB;
        $params = array();
        foreach(array(1, 2, 3) as $c) {
            $sql[] = "(e.". $DB->sql_compare_text("customtext$c") . " = :cohortid$c OR (".$DB->sql_like("customtext$c", ":start$c").") OR 
                                (".$DB->sql_like("customtext$c", ":middle$c").") OR (".$DB->sql_like("customtext$c", ":end$c").")) "; 
            $pars = array("cohortid$c"=>$cohortid, "start$c"=>"$cohortid,%", "middle$c"=>"%,$cohortid,%", "end$c"=>"%,$cohortid" );
            $params = array_merge($params,  $pars);
        }
        $sql = '('.implode(' OR ', $sql).')';
        
        return array($sql, $params);
    }

    protected static function affected_instances($cohortid) {
        global $DB;
        // Does anything want to sync with this multicohort?
        list($affected, $params) = enrol_multicohort_handler::affected_instances_sql($cohortid);
        $sql = "SELECT e.*
                  FROM {enrol} e
                 WHERE e.enrol = 'multicohort' AND $affected
              ORDER BY e.id ASC";

        return $DB->get_records_sql($sql, $params);
    }
    
    protected static function is_enrolled_here($instance, $userid, $check4 = false) {
        $enrolled = true;
        $context = context_course::instance($instance->courseid);  
        $enrolled = is_enrolled($context, $userid); 
        if($enrolled && $check4 && $instance->customint4 == MULTICOHORT_ROLEGROUPS) {
            $enrolled = user_has_role_assignment($userid, $instance->roleid, $context->id); 
        }
  
        return $enrolled;
    }

    
    protected static function add_group_member($instance, $userid, $cohortid) {
        global $DB, $CFG;
        if ($syncgroup = enrol_multicohort_update_syncgroup($instance, $cohortid)) {
            if (!groups_is_member($syncgroup, $userid)) {
                if ($group = $DB->get_record('groups', array('id'=>$syncgroup, 'courseid'=>$instance->courseid))) {
                    groups_add_member($group->id, $userid, 'enrol_multicohort', $instance->id);
                }
            }
        }
    }
    
    protected static function remove_group_member($instance, $userid, $cohortid) {
        global $DB, $CFG;
        if ($syncgroup = enrol_multicohort_update_syncgroup($instance, $cohortid)) {
            if (groups_is_member($syncgroup, $userid)) {
                if ($group = $DB->get_record('groups', array('id'=>$syncgroup, 'courseid'=>$instance->courseid))) {
                    groups_remove_member($group->id, $userid);
                }
            }
        }
    }
    
    
    protected static function get_user_cohorts($instance, $userid) {
        global $DB;
        
        list($inany, $params) = $DB->get_in_or_equal(explode(',', $instance->customtext1), SQL_PARAMS_NAMED, 'any');
        $params['user'] = $userid;
        $select = " cohortid $inany AND userid = :user ";
        
        
        list($muticohortwhere, $params) = enrol_multicohort_where_sql($instance);
        $params['user'] = $userid;
        
        $sql = "SELECT id, cohortid
                FROM {cohort_members} cm
                WHERE cm.userid = :user AND $muticohortwhere ";
        
        return $DB->get_records_sql_menu($sql, $params);
        //return $DB->get_records_select_menu('cohort_members', $select, $params, '', 'id, cohortid');
    }
    
    
    /**
     * Event processor - course user enrolled.
     * @param \core\event\user_enrolment_created $event
     * @return bool
     */
    public static function course_user_added(\core\event\user_enrolment_created $event) {
        global $DB, $CFG;

        if (!enrol_is_enabled('multicohort')) {
            return true;
        }

        // Does any enabled multicohort instance not actually enrolling want to sync with this course?
        $params = array('cid' => $event->courseid, 'gmode'=> MULTICOHORT_ONLYGROUPS);
        $select = " enrol = 'multicohort' AND courseid = :cid AND customint4 = :gmode ";
        if (!$instances = $DB->get_records_select('enrol', $select, $params)) {
            return true;
        }
    
        foreach ($instances as $instance) {
            // Sync groups.
            // first check if user is enrolled with requiered role 
            if(!$enrolled = self::is_enrolled_here($instance, $event->relateduserid)) {
                continue;
            }
            $cohorts =  self::get_user_cohorts($instance, $event->relateduserid);
            foreach($cohorts as $cohortid) {
                self::add_group_member($instance, $event->relateduserid, $cohortid);
            }
        }
    }
    
    /**
     * Event processor - course user enrolled.
     * @param \core\event\role_assigned $event
     * @return bool
     */
    public static function course_user_role_added(\core\event\role_assigned $event) {
        global $DB, $CFG;
        
        if (!enrol_is_enabled('multicohort')) {
            return true;
        }
        // Does any enabled multicohort instance not actually enrolling want to sync with this course?
        $params = array('cid' => $event->courseid, 'gmode'=> MULTICOHORT_ROLEGROUPS, 'rid' => $event->objectid);
        $select = " enrol = 'multicohort' AND courseid = :cid AND customint4 = :gmode AND roleid = :rid ";
        if (!$instances = $DB->get_records_select('enrol', $select, $params)) {
            return true;
        }
    
        foreach ($instances as $instance) {
            if(!$enrolled = self::is_enrolled_here($instance, $event->relateduserid)) {
                continue;
            }
            $cohorts =  self::get_user_cohorts($instance, $event->relateduserid);
            foreach($cohorts as $cohortid) {
                self::add_group_member($instance, $event->relateduserid, $cohortid);
            }
        }
    }
    
    
    /**
     * Event processor - course user enrolled.
     * @param \core\event\role_unassigned $event
     * @return bool
     */
    public static function course_user_role_removed(\core\event\role_unassigned $event) {
        global $DB, $CFG;

        if (!enrol_is_enabled('multicohort')) {
            return true;
        }
        // Does any enabled multicohort instance not actually enrolling want to sync with this course?
        $params = array('cid' => $event->courseid, 'gmode'=> MULTICOHORT_ROLEGROUPS, 'rid' => $event->objectid);
        $select = " enrol = 'multicohort' AND courseid = :cid AND customint4 = :gmode AND roleid = :rid ";
        if (!$instances = $DB->get_records_select('enrol', $select, $params)) {
            return true;
        }
    
        foreach ($instances as $instance) {
            if(!$enrolled = self::is_enrolled_here($instance, $event->relateduserid)) {
                continue;
            }
            $cohorts =  self::get_user_cohorts($instance, $event->relateduserid);
            foreach($cohorts as $cohortid) {
                self::remove_group_member($instance, $event->relateduserid, $cohortid);
            }
        }
    }    
    
    
    /**
     * Event processor - cohort member added.
     * @param \core\event\cohort_member_added $event
     * @return bool
     */
    public static function member_added(\core\event\cohort_member_added $event) {
        global $DB;
        if (!enrol_is_enabled('multicohort')) {
            return true;
        }

        // Does anything want to sync with this multicohort?
        return enrol_multicohort_handler::sync_modified_cohort($event->objectid);
    }

    /**
     * Event processor - cohort member removed.
     * @param \core\event\cohort_member_removed $event
     * @return bool
     */
    public static function member_removed(\core\event\cohort_member_removed $event) {
        global $DB;
        // Does anything want to sync with this multicohort?
        return enrol_multicohort_handler::sync_modified_cohort($event->objectid);
    }

    /**
     * Event processor - cohort member added.
     * @param int $cohortid
     * @return bool
     */
    public static function sync_modified_cohort($cohortid) {
        global $DB;

        // Does any enabled multicohort instance want to sync with this cohort?
        if (!$instances = enrol_multicohort_handler::affected_instances($cohortid)) {
            return true;
        }

        foreach ($instances as $instance) {
            $trace = new null_progress_trace();
            enrol_multicohort_sync($trace, $instance->courseid);
        }
        return true;
    }
    

    /**
     * Event processor - cohort deleted.
     * @param \core\event\cohort_deleted $event
     * @return bool
     */
    public static function deleted(\core\event\cohort_deleted $event) {
        global $DB;

        // Does anything want to sync with this multicohort?
        list($affected, $params) = enrol_multicohort_handler::affected_instances_sql($event->objectid);
        $sql = "SELECT e.*
                  FROM {enrol} e
                 WHERE e.enrol = 'multicohort' AND $affected
              ORDER BY e.id ASC";

        if (!$instances = $DB->get_records_sql($sql, $params)) {
            return true;
        }

        $plugin = enrol_get_plugin('multicohort');
        $unenrolaction = $plugin->get_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);

        foreach ($instances as $instance) {
            // search the deleted cohorts in multicohorts fields, remove if found and re-package as sequence
            $update = false;
            foreach(array('customtext1', 'customtext3', 'customtext3') as $field) {
                if($instance->{$field} && $cohorts = explode(',', $instance->{$field})) {
                    $key = array_search($event->objectid, $cohorts);
                    if($key !== false) {
                        unset($cohorts[$key]);
                        $update = true;
                        $instance->{$field} = $cohorts ? implode(',', $cohorts) : '';
                    }
                }
            }
            
            $DB->update_record('enrol', $instance);
            
            // there is no cohort in first group
            if(!$instance->customtext1) {
                if ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                    $context = context_course::instance($instance->courseid);
                    role_unassign_all(array('contextid'=>$context->id, 'component'=>'enrol_multicohort', 'itemid'=>$instance->id));
                    $plugin->update_status($instance, ENROL_INSTANCE_DISABLED);
                } else {
                    $plugin->delete_instance($instance);
                }
            } else {
                $trace = new null_progress_trace();
                enrol_multicohort_sync($trace, $instance->courseid);
            }
        }

        return true;
    }
}


/**
 * Sync all multicohort course links.
 * @param progress_trace $trace
 * @param int $courseid one course, empty mean all
 * @return int 0 means ok, 1 means error, 2 means plugin disabled
 */
function enrol_multicohort_sync(progress_trace $trace, $courseid = NULL) {
    global $CFG, $DB;

    // Purge all roles if multicohort sync disabled, those can be recreated later here by cron or CLI.
    if (!enrol_is_enabled('multicohort')) {
        $trace->output('multicohort sync plugin is disabled, unassigning all plugin roles and stopping.');
        role_unassign_all(array('component'=>'enrol_multicohort'));
        return 2;
    }
  
    // Unfortunately this may take a long time, this script can be interrupted without problems.
    core_php_time_limit::raise();
    raise_memory_limit(MEMORY_HUGE);

    $trace->output('Starting user enrolment synchronisation...');

    $allroles = get_all_roles();
    $instances = array(); //cache

    $plugin = enrol_get_plugin('multicohort');
    $unenrolaction = $plugin->get_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);

    // get all related enrol instances
    $onecourse = $courseid ? "AND courseid = :courseid" : "";
    $params = array('courseid'=>$courseid);
    
    $rs_instances = $DB->get_recordset_select('enrol', "enrol = 'multicohort' AND customint4 = 0  $onecourse", $params); // only true role assign instances
    if($rs_instances->valid()) {
            foreach($rs_instances as $instance) {
                // Iterate through all not enrolled yet users.
                list($muticohortwhere, $params) = enrol_multicohort_where_sql($instance);
                $sql = "SELECT cm.userid, cm.cohortid, ue.status
                        FROM {cohort_members} cm
                        JOIN {user} u ON (u.id = cm.userid AND u.deleted = 0)
                    LEFT JOIN {user_enrolments} ue ON (ue.userid = cm.userid AND ue.enrolid = :eid )
                        WHERE $muticohortwhere AND ue.id IS NULL OR ue.status = :suspended";

                $params['eid'] = $instance->id;                    
                $params['suspended'] = ENROL_USER_SUSPENDED;  

                $rs = $DB->get_recordset_sql($sql, $params);
                foreach($rs as $ue) {
                    //$instance = $instances[$ue->enrolid];
                    if ($ue->status == ENROL_USER_SUSPENDED) {
                        $plugin->update_user_enrol($instance, $ue->userid, ENROL_USER_ACTIVE);
                        $trace->output("unsuspending: $ue->userid ==> $instance->courseid via multicohort $instance->customint1", 1);
                    } else {
                        $plugin->enrol_user($instance, $ue->userid);
                        $trace->output("enrolling: $ue->userid ==> $instance->courseid via multicohort $instance->customint1", 1);
                    }
                }
                $rs->close();
                        
                // Unenrol as necessary.
                $sql = "SELECT ue.*
                        FROM {user_enrolments} ue
                        WHERE ue.enrolid = :eid AND NOT EXISTS (SELECT 1 FROM {cohort_members} cm WHERE cm.userid = ue.userid AND $muticohortwhere ) ";  

                $rs = $DB->get_recordset_sql($sql, $params);
                foreach($rs as $ue) {

                    if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                        // Remove enrolment together with group membership, grades, preferences, etc.
                        $plugin->unenrol_user($instance, $ue->userid);
                        $trace->output("unenrolling: $ue->userid ==> $instance->courseid via multicohort $instance->customint1", 1);

                    } else { // ENROL_EXT_REMOVED_SUSPENDNOROLES
                        // Just disable and ignore any changes.
                        if ($ue->status != ENROL_USER_SUSPENDED) {
                            $plugin->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                            $context = context_course::instance($instance->courseid);
                            role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$context->id, 'component'=>'enrol_multicohort', 'itemid'=>$instance->id));
                            $trace->output("suspending and unsassigning all roles: $ue->userid ==> $instance->courseid", 1);
                        }
                    }
                }
                $rs->close();
            }
    }
    $rs_instances->close();

    // Now assign all necessary roles to enrolled users - skip suspended instances and users.
    $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    $sql = "SELECT e.roleid, ue.userid, c.id AS contextid, e.id AS itemid, e.courseid
              FROM {user_enrolments} ue
              JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'multicohort' AND e.customint4 = 0 AND e.status = :statusenabled $onecourse)
              JOIN {role} r ON (r.id = e.roleid)
              JOIN {context} c ON (c.instanceid = e.courseid AND c.contextlevel = :coursecontext)
              JOIN {user} u ON (u.id = ue.userid AND u.deleted = 0)
         LEFT JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.userid = ue.userid AND ra.itemid = e.id AND ra.component = 'enrol_multicohort' AND e.roleid = ra.roleid)
             WHERE ue.status = :useractive AND ra.id IS NULL";
    $params = array();
    $params['statusenabled'] = ENROL_INSTANCE_ENABLED;
    $params['useractive'] = ENROL_USER_ACTIVE;
    $params['coursecontext'] = CONTEXT_COURSE;
    $params['courseid'] = $courseid;

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $ra) {
        role_assign($ra->roleid, $ra->userid, $ra->contextid, 'enrol_multicohort', $ra->itemid);
        $trace->output("assigning role: $ra->userid ==> $ra->courseid as ".$allroles[$ra->roleid]->shortname, 1);
    }
    $rs->close();


    // Remove unwanted roles - sync role can not be changed, we only remove role when unenrolled.
    $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    $sql = "SELECT ra.roleid, ra.userid, ra.contextid, ra.itemid, e.courseid
              FROM {role_assignments} ra
              JOIN {context} c ON (c.id = ra.contextid AND c.contextlevel = :coursecontext)
              JOIN {enrol} e ON (e.id = ra.itemid AND e.enrol = 'multicohort' AND e.customint4 = 0  $onecourse)
         LEFT JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = ra.userid AND ue.status = :useractive)
             WHERE ra.component = 'enrol_multicohort' AND (ue.id IS NULL OR e.status <> :statusenabled)";
    $params = array();
    $params['statusenabled'] = ENROL_INSTANCE_ENABLED;
    $params['useractive'] = ENROL_USER_ACTIVE;
    $params['coursecontext'] = CONTEXT_COURSE;
    $params['courseid'] = $courseid;

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $ra) {
        role_unassign($ra->roleid, $ra->userid, $ra->contextid, 'enrol_multicohort', $ra->itemid);
        $trace->output("unassigning role: $ra->userid ==> $ra->courseid as ".$allroles[$ra->roleid]->shortname, 1);
    }
    $rs->close();

    // Finally sync groups.
    $affectedusers = array('removed'=>array(), 'added'=>array());
    $trace->output("...doing group memberships");
    // get all related enrol instances
    $onecourse = $courseid ? "AND courseid = :courseid" : "";
    $params = array('courseid'=>$courseid);
    $rs_instances = $DB->get_recordset_select('enrol', "enrol = 'multicohort' $onecourse", $params); 
    
    if($rs_instances->valid()) {
        foreach($rs_instances as $instance) {
            if($instance->customint2 == MULTICOHORT_MULTIPLE_GROUP) {
                $instanceusers = enrol_multicohort_multiple_groups_sync($instance);
                $trace->output("    ...doing group memberships for enrol_multicohort_multiple_groups_sync ids: {$instance->customtext4}");
            } else {
                if($instance->customint4 == MULTICOHORT_ENROLGROUPS) {
                    $instanceusers =  enrol_multicohort_single_group_sync($instance);
                } else {
                    $syncgroup = enrol_multicohort_update_syncgroup($instance);
                    $instanceusers = enrol_multicohort_multiple_groups_sync($instance, $syncgroup);
                }
                $trace->output("    ...doing group memberships for single group id={$instance->customint2}");
            }
            $affectedusers['removed'] = $affectedusers['removed'] + $instanceusers['removed'];
            $affectedusers['added'] = $affectedusers['added'] + $instanceusers['added'];
        }
    }
    $rs_instances->close();
    
    //$affectedusers = groups_sync_with_enrolment('multicohort', $courseid);
    $users = array();
    $groups = array();
    foreach ($affectedusers['removed'] as $gm) {
        $users[$gm->userid] = 1;
        $groups[$gm->groupid] = 1;
    }
    $users = count($users);
    $groups = count($groups);
    $trace->output("removed $users users from $groups groups ", 1);
    $users = array();
    $groups = array();
    foreach ($affectedusers['added'] as $ue) {
        $users[$ue->userid] = 1;
        $groups[$ue->groupid] = 1;
    }
    $users = count($users);
    $groups = count($groups);
    $trace->output("added $users users to $groups groups ", 1);

    $trace->output('...user enrolment synchronisation finished.');

    return 0;
}


/**
    * Checks if needed to update/create group membership
    *
    * @param stdClass $instance
    * @return false/groupid
    */
function enrol_multicohort_update_syncgroup($instance, $cohortid = null) {
    global $DB;

    $syncgroup = $instance->customint2;
    $groupdata = new stdClass;
    $groupdata->description = '';
    $groupdata->descriptionformat = FORMAT_MOODLE;
    $groupdata->courseid = $instance->courseid;

    $groupid = 0;
    if($syncgroup < 0) {
        if($syncgroup == MULTICOHORT_MULTIPLE_GROUP) { // sync multiple groups is not applied here
            $idnumber = 'multicohort_'.$instance->id.'_cohort_'.$cohortid;
            if($group = groups_get_group_by_idnumber($instance->courseid, $idnumber)) {
                $groupid = $group->id;
            }
            return $groupid;
        } elseif($syncgroup == MULTICOHORT_CREATE_GROUP ) { // sync group by name of enrol
            $groupdata->name = $instance->name;
            $groupdata->idnumber = 'multicohort_'.$instance->id.'_cohort_pooled';
        }
        if(!$group = groups_get_group_by_idnumber($instance->courseid, $groupdata->idnumber)) {
            if($groupdata->idnumber) {
                $groupid = groups_create_group($groupdata);
                if($ulpgcgroups = get_config('local_ulpgcgroups')) { 
                    local_ulpgcgroups_update_group_component($groupid, 'enrol_multicohort', $instance->id);  
                }
            }
        } else {
            $groupid = $group->id;
        }
    } elseif($syncgroup > 0) {
        $groupid = $DB->get_field('groups', 'id', array('id'=>$syncgroup), MUST_EXIST);
    }

    return $groupid;
}

/**
    * Checks if needed to update/create group membership
    *
    * @param stdClass $instance
    * @return false/groupid
    */
function enrol_multicohort_single_group_sync($instance) {
    global $DB, $CFG;
   
    $affectedusers = array('removed'=>array(), 'added'=>array());
    
    if($groupid = enrol_multicohort_update_syncgroup($instance)) {
        $instance->customint3 = $groupid;
        $DB->update_record('enrol', $instance);
        $affectedusers = groups_sync_with_enrolment('multicohort', $instance->courseid, 'customint3');
    }
    return $affectedusers;
}

/**
 * Sync all multicohort course links.
 * @param progress_trace $trace
 * @param int $courseid one course, empty mean all
 * @return int 0 means ok, 1 means error, 2 means plugin disabled
 */
function enrol_multicohort_where_sql($instance) {
    global $DB;
    
    $params = array();
    $anyof = ' 1 ';
    $allof = '';
    $notin = '';
    
    if($instance->customtext1 && $cohorts = explode(',', $instance->customtext1)) {
        list($insql, $anyparams) = $DB->get_in_or_equal($cohorts, SQL_PARAMS_NAMED, 'any');
        $anyof = " cm.cohortid $insql" ;
        $params = array_merge($params, $anyparams);
    }
            
    if($instance->customtext2 && $cohorts = explode(',', $instance->customtext2)) {
        $and = array();
        foreach($cohorts as $cid) {
            $and[] = " EXISTS (SELECT 1 FROM {cohort_members} ca$cid  WHERE cm.userid = ca$cid.userid AND ca$cid.cohortid = :and$cid) ";
            $params["and$cid"] = $cid;
        }
        $allof = ' AND '.implode(' AND ', $and);
    }
    
    if($instance->customtext3 && $cohorts = explode(',', $instance->customtext3)) {
        if($instance->customint1 == 0) {
            //not any of them
            list($insql, $notparams) = $DB->get_in_or_equal($cohorts, SQL_PARAMS_NAMED, 'not', true);
            $notin = " AND NOT EXISTS (SELECT 1 FROM {cohort_members} cmn WHERE cmn.userid = cm.userid AND cmn.cohortid $insql) " ;
            $params = array_merge($params, $notparams);
        } else {
            $not = array();
            foreach($cohorts as $cid) {
                $not[] = " NOT EXISTS (SELECT 1 FROM {cohort_members} cn$cid  WHERE cm.userid = cn$cid.userid AND cn$cid.cohortid = :and$cid) ";
                $params["and$cid"] = $cid;
            }
            $notin = ' AND '.implode(' AND ', $not);
        }
    }
    
    return array( $anyof.$allof.$notin, $params);
}

/**
 * Sync all multicohort course links.
 * @param stdClass $instance enrol instance object
 * @param int $singlegroup single group to add/remove members
 * @return int 0 means ok, 1 means error, 2 means plugin disabled
 */
function enrol_multicohort_multiple_groups_sync($instance, $singlegroup = 0) {
    global $DB;
    $params = array(
        'component' => 'enrol_multicohort',
        'courseid'  => $instance->courseid,
    );

    $enrolmentwhere = '';
    $muticohortwhere = '';
    if($instance->customint4 == MULTICOHORT_ENROLGROUPS) {
        $params['eid'] = $instance->id;
        $enrolmentwhere = " AND ue.enrolid = :eid ";
    } else {
        $enrolments = $DB->get_records_menu('enrol', array('courseid'=>$instance->courseid), '', 'id, courseid');
        list($insql, $inparams) = $DB->get_in_or_equal(array_keys($enrolments), SQL_PARAMS_NAMED, 'uer_', true, ' = 0 ');
        $params = $params + $inparams;
        $enrolmentwhere =  " AND ue.enrolid $insql "; 
        
        list($muticohortwhere, $mchparams) = enrol_multicohort_where_sql($instance);
        $muticohortwhere = ' AND '.$muticohortwhere;
        $params = $params + $mchparams;
    }
    
    $rolejoin = '';
    if($instance->customint4 == MULTICOHORT_ROLEGROUPS) {
        $params['roleid'] = $instance->roleid;
        $context = context_course::instance($instance->courseid);
        $params['ctxid'] = $context->id;
        $rolejoin = "JOIN {role_assignments} ra ON ra.userid = ue.userid AND ra.roleid = :roleid AND ra.contextid = :ctxid ";
    }
    
    $affectedusers = array(
        'removed' => array(),
        'added' => array()
    );
    
    $groupwhere = '';
    if($singlegroup) {
        $groupwhere = " AND g.id = :gid ";
        $params['gid'] = $singlegroup;
    } else {
        $idnumber = 'multicohort\_'.$instance->id.'\_cohort%';
        $select = $DB->sql_like('g.idnumber', ':idnumber');
        $params['idnumber'] = $idnumber;
        $groupwhere = " AND $select ";
    }
    
    // Remove invalid.
    list($inany, $anyparams) = $DB->get_in_or_equal(explode(',', $instance->customtext1), SQL_PARAMS_NAMED, 'any');
    $params = array_merge($params, $anyparams);
    list($inany2, $anyparams) = $DB->get_in_or_equal(explode(',', $instance->customtext1), SQL_PARAMS_NAMED, 'any2');
    $params = array_merge($params, $anyparams);
    $sql = "SELECT ue.id, ue.userid, ue.enrolid, g.courseid, g.id AS groupid, g.name AS groupname, g.idnumber, cm.cohortid, gm.id AS gmid
              FROM {groups_members} gm
              JOIN {groups} g ON (g.id = gm.groupid AND g.courseid = :courseid $groupwhere )
              JOIN {user_enrolments} ue ON (ue.userid = gm.userid $enrolmentwhere)
              $rolejoin
              LEFT JOIN {cohort_members} cm ON (ue.userid = cm.userid AND cm.userid = gm.userid AND cm.cohortid $inany) 
            WHERE gm.component='enrol_multicohort' AND gm.itemid = ue.enrolid  
            AND NOT EXISTS (SELECT 1
                               FROM {groups_members} gm2 
                               JOIN {cohort_members} cm2 ON (cm2.userid = gm2.userid AND cm2.cohortid $inany2)
                               WHERE gm2.id = gm.id AND cm2.cohortid <> cm.cohortid 
                            
                            )";
            // NOT EXISTS to eliminate users that are members of several cohorts
    $params = array_merge($params, $anyparams);
    
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $gm) {
        $parts = explode('_', $gm->idnumber);
        if(!$gm->cohortid || !isset($parts[3]) || $parts[3] != $gm->cohortid) {
            groups_remove_member($gm->groupid, $gm->userid);
            $affectedusers['removed'][] = $gm;
        }
    }
    $rs->close();

    // Add missing.
    $gparams = array();
    if($singlegroup) {
        $ingroups = ' = :gid ';
    } else  {
        $ingroups = ' <> 0 ';
        if($instance->customtext4 && $groups = explode(',', $instance->customtext4)) {
            list($ingroups, $gparams) = $DB->get_in_or_equal(explode(',', $instance->customtext4), SQL_PARAMS_NAMED, 'g');
        }
    }
    $sql = "SELECT ue.id, ue.userid, ue.enrolid, g.courseid, g.id AS groupid, g.name AS groupname, g.idnumber, cm.cohortid, gm.id AS memberid
              FROM {user_enrolments} ue
              JOIN {groups} g ON (g.courseid = :courseid AND g.id $ingroups)
              JOIN {user} u ON (u.id = ue.userid AND u.deleted = 0)
              JOIN {cohort_members} cm ON (cm.userid = ue.userid AND cm.cohortid $inany)
              $rolejoin
         LEFT JOIN {groups_members} gm ON (gm.groupid = g.id AND gm.userid = ue.userid AND gm.userid = cm.userid)
             WHERE 1 $enrolmentwhere $muticohortwhere ";
    $params = array_merge($params, $gparams);
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $ue) {
        $parts = explode('_', $ue->idnumber);
        if(!$ue->memberid && $ue->cohortid && isset($parts[3]) && $parts[3] == $ue->cohortid) {
            groups_add_member($ue->groupid, $ue->userid, 'enrol_multicohort', $ue->enrolid);
            $affectedusers['added'][] = $ue;
        }
    }
    $rs->close();
    
    return $affectedusers;
}
