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
 * Local stuff for meta category enrolment plugin.
 *
 * @package    enrol
 * @subpackage metacat
 * @copyright  2012 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/group/lib.php');

/**
 * Event handler for meta enrolment plugin.
 *
 * We try to keep everything in sync via listening to events,
 * it may fail sometimes, so we always do a full sync in cron too.
 */
class enrol_metacat_handler {

    /**
     * Synchronise meta enrolments of this user in this course
     * @static
     * @param int $courseid
     * @param int $userid
     * @return void
     */
    protected static function sync_course_instances($courseid, $userid) {
        global $DB;

        static $preventrecursion = false;

        $category = $DB->get_field('course', 'category', array('id'=>$courseid));

        // does anything want to sync with this parent?
        if (!$enrols = $DB->get_records('enrol', array('enrol'=>'metacat', 'customint1'=> $category))) {
            return;
        }

        if ($preventrecursion) {
            return;
        }

        $preventrecursion = true;

        try {
            foreach ($enrols as $enrol) {
                self::sync_with_parent_course($enrol, $courseid, $userid);
            }
        } catch (Exception $e) {
            $preventrecursion = false;
            throw $e;
        }

        $preventrecursion = false;
    }


    /**
     * Synchronise user enrolments in given instance for a single parent course, as fast as possible.
     *
     * All roles are removed if the meta plugin disabled.
     *
     * @static
     * @param stdClass $instance
     * @param int $parentid courseid of parent course
     * @param int $userid
     * @return void
     */
    protected static function sync_with_parent_course(stdClass $instance, $parentid, $userid) {
        global $DB, $CFG;

        $plugin = enrol_get_plugin('metacat');

        if ($parentid == $instance->courseid) {
            // can not sync with self!!!
            return;
        }

        $context = context_course::instance($instance->courseid);

        if (!$parentcontext = context_course::instance($parentid, IGNORE_MISSING)) {
            // linking to missing course is not possible
            //role_unassign_all(array('userid'=>$userid, 'contextid'=>$context->id, 'component'=>'enrol_metacat'));

            /// TODO unassign if not enrolled in other courses of categories


            return;
        }

        // list of enrolments in parent course (we ignore meta enrols in parents completely)
        // roles in parent courses (meta enrols must be ignored!)
        $params = array();
        $params['userid'] = $userid;
        $params['parentcourse'] = $parentid;
        $params['parentcontext'] = $parentcontext->id;
        $params['category'] = $instance->customint1;
        $params['coursecontext'] = CONTEXT_COURSE;


        $enabled = explode(',', $CFG->enrol_plugins_enabled);
        foreach($enabled as $k=>$v) {
            if ($v === 'metacat') {
                unset($enabled[$k]); // no meta sync of meta roles
            }
        }
        list($enabled1, $eparams) = $DB->get_in_or_equal($enabled, SQL_PARAMS_NAMED, 'pe');
        $params = $params + $eparams;
        list($enabled2, $eparams) = $DB->get_in_or_equal($enabled, SQL_PARAMS_NAMED, 'ra');
        $enabled2 = "<> 'metacat' ";
        $params = $params + $eparams;
        $syncroles = empty($instance->customtext1) ? array() : explode(',', $instance->customtext1);
        list($inroles, $roleparams) = $DB->get_in_or_equal($syncroles, SQL_PARAMS_NAMED, 'ri', true, -1);
        $params = $params + $roleparams;


        // enrolments in this parent
        $sql = "SELECT ra.id AS raid, ue.*, e.id AS enrolid, ra.roleid
                    FROM {user_enrolments} ue
                    JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :parentcourse AND e.enrol $enabled1 )
                    JOIN {role_assignments} ra ON (ra.contextid = :parentcontext  AND ra.userid = ue.userid
                                                    AND ra.component $enabled2 AND ra.roleid $inroles )
                 WHERE ue.userid = :userid
                 GROUP BY ue.userid, ra.roleid ";
        $parentues = $DB->get_records_sql($sql, $params);
        // current enrolments for this instance
        $ue = $DB->get_record('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid));

        // first deal with case user is not enrolled in parent
        if (empty($parentues)) {
            // check if enrolled in other courses in category
                        $sql = "SELECT ra.id AS raid, ue.id, ue.userid, e.id AS enrolid, ra.roleid
                    FROM {user_enrolments} ue
                    JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid <> :parentcourse AND e.enrol $enabled1)
                    JOIN {context} ctx ON (ctx.instanceid = e.courseid AND ctx.contextlevel = :coursecontext )
                    JOIN {course} c ON (c.id = e.courseid AND c.category = e.customint1 AND c.category = :category)
                    JOIN {role_assignments} ra ON (ra.contextid = ctx.id AND ra.userid = ue.userid
                                                    AND ra.component $enabled2 AND ra.roleid $inroles )
                    WHERE ue.userid = :userid AND c.id IS NOT NULL
                    GROUP BY ue.userid, ra.roleid ";
            $otherparents = $DB->get_records_sql($sql, $params);
            if(empty($otherparents)) {
                self::user_not_supposed_to_be_here($instance, $ue, $context, $plugin);
                return;
            }
        }

        if (!enrol_is_enabled('metacat')) {
            if ($ue) {
                role_unassign_all(array('userid'=>$userid, 'contextid'=>$context->id, 'component'=>'enrol_metacat'));
            }
            return;
        }

        // is parent enrol active? (we ignore enrol starts and ends, sorry it would be too complex)
        $parentstatus = ENROL_USER_SUSPENDED;
        foreach ($parentues as $pue) {
            if ($pue->status == ENROL_USER_ACTIVE) {
                $parentstatus = ENROL_USER_ACTIVE;
                break;
            }
        }

        // enrol user if not enrolled yet or fix status
        if ($ue) {
            if ($parentstatus != $ue->status) {
                $plugin->update_user_enrol($instance, $userid, $parentstatus);
                $ue->status = $parentstatus;
            }
        } else {
            $plugin->enrol_user($instance, $userid, NULL, 0, 0, $parentstatus);
            $ue = new stdClass();
            $ue->userid = $userid;
            $ue->enrolid = $instance->id;
            $ue->status = $parentstatus;
        }

        if($groupid = enrol_metacat_update_syncgroup($instance)) {
            groups_add_member($groupid, $ue->userid, 'enrol_metacat', $instance->id); /// TODO cambiar a component + itemid in 2.4, 2.5
        }

        // add new roles
        // roles from this instance
        $roles = array();
        $ras = $DB->get_records('role_assignments', array('contextid'=>$context->id, 'userid'=>$userid, 'component'=>'enrol_metacat', 'itemid'=>$instance->id));
        foreach($ras as $ra) {
            $roles[$ra->roleid] = $ra->roleid;
        }
        unset($ras);
        $enrolledas = $instance->customint2;
        if(!$enrolledas) {
            // roles in parent courses (meta enrols must be ignored!)
            $parentroles = array();
            foreach($parentues as $pue) {
                $parentroles[$pue->roleid] = $pue->roleid;
            }
            foreach ($parentroles as $rid) {
                if (!isset($roles[$rid])) {
                    role_assign($rid, $userid, $context->id, 'enrol_metacat', $instance->id);
                }
            }
        } else {
            if (!isset($roles[$enrolledas])) {
                role_assign($enrolledas, $userid, $context->id, 'enrol_metacat', $instance->id);
            }
        }

        // only active users in enabled instances are supposed to have roles (we can reassign the roles any time later)
        if ($ue->status != ENROL_USER_ACTIVE or $instance->status != ENROL_INSTANCE_ENABLED) {
            if ($roles) {
                role_unassign_all(array('userid'=>$userid, 'contextid'=>$context->id, 'component'=>'enrol_metacat', 'itemid'=>$instance->id));
            }
            return;
        }

        // remove roles
        foreach ($roles as $rid) {
            if (!isset($parentroles[$rid])) {
                role_unassign($rid, $userid, $context->id, 'enrol_metacat', $instance->id);
            }
        }
    }

    /**
     * Deal with users that are not supposed to be enrolled via this instance
     * @static
     * @param stdClass $instance
     * @param stdClass $ue
     * @param context_course $context
     * @param enrol_meta $plugin
     * @return void
     */
    protected static function user_not_supposed_to_be_here($instance, $ue, context_course $context, $plugin) {
        if (!$ue) {
            // not enrolled yet - simple!
            return;
        }

        $userid = $ue->userid;
        $unenrolaction = $plugin->get_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);
        $groupid = enrol_metacat_update_syncgroup($instance);

        if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
            // purges grades, group membership, preferences, etc. - admins were warned!
            $plugin->unenrol_user($instance, $userid, $groupid);
            return;

        } elseif ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) { // ENROL_EXT_REMOVED_SUSPENDNOROLES
            // just suspend users and remove all roles (we can reassign the roles any time later)
            if ($ue->status != ENROL_USER_SUSPENDED) {
                $plugin->update_user_enrol($instance, $userid, ENROL_USER_SUSPENDED);
                role_unassign_all(array('userid'=>$userid, 'contextid'=>$context->id, 'component'=>'enrol_metacat', 'itemid'=>$instance->id));
            }
            return;
        }
    }
}

//Warning: mysqli::real_escape_string() expects parameter 1 to be string, array given in /var/www/html/moodle31ulpgc/lib/dml/mysqli_native_moodle_database.php on line 955



/**
 * Sync all meta category links.
 *
 * @param progress_trace $trace
 * @param int $courseid one course having an instance on category meta link, empty mean all
 * @param int/array $categoryid one course category or array of caterory IDs, pointed by metacat instance, empty mean any
 * @return int 0 means ok, 1 means error, 2 means plugin disabled
 */
function enrol_metacat_sync(progress_trace $trace, $courseid = NULL, $categoryid = NULL, $verbose = false) {
    global $CFG, $DB;
    
    // purge all roles if meta sync disabled, those can be recreated later here in cron
    if (!enrol_is_enabled('metacat')) {
        $trace->output('Meta sync plugin is disabled, unassigning all plugin roles and stopping.');
        role_unassign_all(array('component'=>'enrol_metacat'));
        return 2;
    }

    // unfortunately this may take a long time, execution can be interrupted safely
    core_php_time_limit::raise();
    raise_memory_limit(MEMORY_HUGE);

    $trace->output('Starting metacat user enrolment synchronisation...');

    $meta = enrol_get_plugin('metacat');
    $unenrolaction = $meta->get_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);
    $skiproles     = $meta->get_config('nosyncroleids', '');
    $skiproles     = empty($skiproles) ? array() : explode(',', $skiproles);

    // list of child instances
    $params = array('enrol'=>'metacat');
    $select = 'enrol = :enrol ';
    if($courseid) {
        $select .= ' AND  courseid = :courseid ';
        $params['courseid'] = $courseid;
    }
    if($categoryid) {
        if(is_array($categoryid)) {
            list($insql, $inparams) = $DB->get_in_or_equal($categoryid, SQL_PARAMS_NAMED, 'cat_');
            $select .= " AND customint1 $insql";
            $params = $params + $inparams;
        } else {
            $select .= " AND customint1 = :customint1";
            $params['customint1'] = $categoryid;
        }
    }
    $metacatinstances = $DB->get_recordset_select('enrol', $select,  $params);

    $syncgroups = array();

    if($metacatinstances->valid()) {
        foreach ($metacatinstances as $metainstance) {
            $courseid = $metainstance->courseid;
            if(!$courseid) {
                $trace->output("metacat instance {$metainstance->id} has no courseid ");
                continue;
            }
            if(!$metainstance->customint1 && $metainstance->customint4 > 0) {
                $metainstance->customint1 = $DB->get_field('course', 'category', array('id'=>$courseid));
                $DB->update_record('enrol', $metainstance);
            }
            $category = $metainstance->customint1;
            if(!$category) {
                $trace->output("metacat instance {$metainstance->id} has no category in customint1 ");
                continue;
            }
            if(!$DB->record_exists('course_categories', array('id'=>$metainstance->customint1))) {
                $meta->update_status($metainstance, ENROL_INSTANCE_DISABLED);
            }

            $syncroles = empty($metainstance->customtext1) ? array() : explode(',', $metainstance->customtext1);
            $syncroles = array_diff($syncroles, $skiproles);
            $enrolledas = $metainstance->customint2;
            $syncgroup = $metainstance->customint3;
            $context = context_course::instance($metainstance->courseid);        
            if($syncgroup = enrol_metacat_update_syncgroup($metainstance)) {
                $syncgroups[$metainstance->id] = $syncgroup;
            }
            
            // iterate through all users enrolled in parent category courses but not enrolled yet in this child one
            $params = array('courseid1'=> $courseid, 'courseid2'=> $courseid,
                            'coursecontext'=> CONTEXT_COURSE, 'status' => ENROL_USER_ACTIVE,
                            'category1'=> $category, 'category2'=> $category);
            // roles in parent courses (meta enrols must be ignored!)
            $enabled = explode(',', $CFG->enrol_plugins_enabled);
            foreach($enabled as $k=>$v) {
                if ($v === 'metacat') {
                    unset($enabled[$k]); // no meta sync of meta roles
                }
            }
            list($enabled1, $eparams) = $DB->get_in_or_equal($enabled, SQL_PARAMS_NAMED, 'pe');
            $params = $params + $eparams;
            //list($enabled2, $eparams) = $DB->get_in_or_equal($enabled, SQL_PARAMS_NAMED, 'ra');
            $enabled2 = "<> 'metacat' ";
            //$params = $params + $eparams;
            list($inroles, $roleparams) = $DB->get_in_or_equal($syncroles, SQL_PARAMS_NAMED, 'ri', true, -1);
            $params = $params + $roleparams;

            $sql = "SELECT ra.id AS raid, pue.id, pue.userid, e.id AS enrolid, pue.status, ra.roleid, ue.id AS currentenrol
                    FROM {user_enrolments} pue
                    JOIN {enrol} pe ON (pe.id = pue.enrolid AND pe.courseid <> :courseid1 AND pe.enrol $enabled1 )
                    JOIN {context} ctx ON (ctx.instanceid = pe.courseid AND ctx.contextlevel = :coursecontext )
                    JOIN {course} c ON (pe.courseid = c.id  AND c.category = :category1)
                    JOIN {role_assignments} ra ON (ra.contextid = ctx.id AND ra.userid = pue.userid
                                                    AND ra.component $enabled2 AND ra.roleid $inroles )
                    JOIN {enrol} e ON (e.customint1 = c.category AND e.enrol = 'metacat' AND e.courseid = :courseid2 )
                LEFT JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = pue.userid )
                    WHERE c.category = :category2 AND ra.id IS NOT NULL AND c.id IS NOT NULL
                    GROUP BY pue.userid, ra.roleid";

            $rs = $DB->get_recordset_sql($sql, $params);

            foreach($rs as $ue) {
                if(!$ue->currentenrol) {
                    if(!$enrolledas) {
                        $roleid = $ue->roleid;
                    } else {
                        $roleid = $enrolledas;
                    }
                    if(!$roleid) {
                        $roleid = $defaultrole;
                    }
                    $meta->enrol_user($metainstance, $ue->userid, $roleid);
                    $trace->output("  enrolling user $ue->userid ==> in course {$metainstance->courseid} , with role $roleid ");
                }
            }
            $rs->close();

            // unenrol as necessary
            /// params array can be re-used
            $sql = "SELECT ra.id AS raid, ue.*
                    FROM {user_enrolments} ue
                    JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'metacat' AND e.courseid = :courseid1 )
                LEFT JOIN ({user_enrolments} xpue
                            JOIN {enrol} xpe ON (xpe.id = xpue.enrolid AND xpe.courseid <> :courseid2 AND xpe.enrol $enabled1)
                            JOIN {context} ctx ON (ctx.instanceid = xpe.courseid AND ctx.contextlevel = :coursecontext )
                            JOIN {course} c ON (xpe.courseid = c.id  AND c.category = :category1)
                            JOIN {role_assignments} ra ON (ra.contextid = ctx.id AND ra.userid = xpue.userid
                                                            AND ra.component $enabled2 AND ra.roleid $inroles )
                        ) ON (c.category = e.customint1 AND xpue.userid = ue.userid )
                    WHERE xpue.userid IS NULL  
                    GROUP BY ue.userid, ue.status ";
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach($rs as $ue) {
                if ($unenrolaction == ENROL_EXT_REMOVED_KEEP) {
                    continue;
                } elseif ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                    $meta->unenrol_user($metainstance, $ue->userid, $syncgroup);
                    $trace->output("  unenrolling user $ue->userid ==> from course {$metainstance->courseid} ");
                } elseif ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                    // just disable and ignore any changes
                    if ($ue->status != ENROL_USER_SUSPENDED) {
                        $meta->update_user_enrol($metainstance, $ue->userid, ENROL_USER_SUSPENDED);
                        role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$context->id, 'component'=>'enrol_metacat'));
                        $trace->output("  suspending and removing all roles: $ue->userid ==> $metainstance->courseid");
                    }
                }
            }
            $rs->close();
            
            // update status - meta enrols + start and end dates are ignored, sorry
            // note the trick here is that the active enrolment and instance constants have value 0
            $sql = "SELECT ue.userid, ue.enrolid, pue.pstatus
                    FROM {user_enrolments} ue
                    JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'metacat' AND e.courseid = :courseid1 )
                    JOIN (SELECT xpue.userid, xpe.courseid, c.category, MIN(xpue.status + xpe.status) AS pstatus
                            FROM {user_enrolments} xpue
                            JOIN {enrol} xpe ON (xpe.id = xpue.enrolid AND xpe.courseid <> :courseid2 AND xpe.enrol $enabled1)
                            JOIN {context} ctx ON (ctx.instanceid = xpe.courseid AND ctx.contextlevel = :coursecontext )
                            JOIN {course} c ON (xpe.courseid = c.id  AND c.category = :category1)
                            JOIN {role_assignments} ra ON (ra.contextid = ctx.id AND ra.userid = xpue.userid
                                                            AND ra.component $enabled2 AND ra.roleid $inroles )
                        WHERE xpue.status =  ra.id IS NOT NULL AND c.id IS NOT NULL
                        GROUP BY xpue.userid, xpe.courseid, ra.roleid
                        ) pue ON (pue.category = e.customint1 AND pue.userid = ue.userid)
                    WHERE (pue.pstatus = 0 AND ue.status > 0) OR (pue.pstatus > 0 and ue.status = 0) ";
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach($rs as $ue) {
                $meta->update_user_enrol($metainstance, $ue->userid, $ue->pstatus);
                if ($ue->pstatus == ENROL_USER_ACTIVE) {
                    $trace->output("  unsuspending: $ue->userid ==> $metainstance->courseid");
                } else {
                    $trace->output("  suspending: $ue->userid ==> $metainstance->courseid");
                }
            }
            $rs->close();
        }
    }
    $metacatinstances->close();
    
    // Remove unwanted roles - sync role can not be changed, we only remove role when unenrolled.
    $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    $sql = "SELECT ra.roleid, ra.userid, ra.contextid, ra.itemid, e.courseid
              FROM {role_assignments} ra
              JOIN {context} c ON (c.id = ra.contextid AND c.contextlevel = :coursecontext)
              JOIN {enrol} e ON (e.id = ra.itemid AND e.enrol = 'metacat' $onecourse)
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
    
    foreach($syncgroups as $instanceid => $syncgroup) {
        if($syncgroup) {
            $params = array('eid'=>$instanceid, 'syncgroup'=>$syncgroup);
            $sql = "SELECT ue.id, ue.userid, ue.enrolid
                    FROM {user_enrolments} ue 
                    JOIN {enrol} e ON e.id = ue.enrolid
                    LEFT JOIN {groups_members} gm ON gm.groupid = :syncgroup AND gm.userid = ue.userid AND gm.component = 'enrol_metacat' AND gm.itemid = ue.enrolid 
                    WHERE enrolid = :eid AND ue.status = 0  AND gm.id IS NULL ";
            
            $rs = $DB->get_recordset_sql($sql, $params);
            if($rs->valid()) {
                foreach($rs as $ue) {
                    groups_add_member($syncgroup, $ue->userid, 'enrol_metacat', $ue->enrolid);
                }
            }
            $rs->close();
        
            $sql = "SELECT gm.id, gm.userid, gm.groupid
                    FROM {groups_members} gm 
                    LEFT JOIN {user_enrolments} ue ON ue.userid = gm.userid AND ue.enrolid = gm.itemid AND ue.status = 0 
                    WHERE gm.groupid = :syncgroup AND gm.component = 'enrol_metacat' AND gm.itemid = :eid AND ue.id IS NULL";
            $rs = $DB->get_recordset_sql($sql, $params);
            if($rs->valid()) {
                foreach($rs as $ue) {
                    groups_remove_member($syncgroup, $ue->userid);
                }
            }
            $rs->close();
        }
    }
    
    $trace->output('...metacat user enrolment synchronisation finished.');

    return 0;
}

/**
    * Checks if needed to update/create group membership
    *
    * @param stdClass $instance
    * @return false/groupid
    */
function enrol_metacat_update_syncgroup($instance) {
    global $CFG, $DB;

    $category = $instance->customint1;
    if($ulpgc = get_config('local_ulpgccore', 'enabledadminmods')) {
        include_once($CFG->dirroot.'/local/ulpgccore/lib.php');
        $category = local_ulpgccore_get_category_details($category);
    } else {
        $category = $DB->get_record('course_categories', array('id'=>$category), '*', MUST_EXIST);
    }
    
    $syncgroup = $instance->customint3;
    $coursegroup = new stdClass;
    $coursegroup->courseid = $instance->courseid;

    $groupid = 0;
    if($syncgroup < 0) {
        if($syncgroup == ENROL_METACAT_GROUP_BY_IDNUMBER) { // sync groups by idnumber of parent category
            $idnumber = $category->idnumber;
        } elseif($syncgroup == ENROL_METACAT_GROUP_BY_NAME) { // sync groups by name of parent category
            if($ulpgcshorten = get_config('local_ulpgccore','shortennavbar')) {  // ecastro ULPGC
                $category->name = local_ulpgccore_shorten_titles($category->name);
            }
            $idnumber = substr($category->name, 1, 100); // victor.deniz@20170524: max lenght groups idnumber field
        } elseif($syncgroup == ENROL_METACAT_GROUP_BY_ID) {  // sync groups by id of parent category
            $idnumber = $category->id;
        } elseif($ulpgc && $syncgroup == ENROL_METACAT_GROUP_BY_DEGREE) {  // sync groups by degree of parent category
            $idnumber = $category->degree;
        } elseif($ulpgc && $syncgroup == ENROL_METACAT_GROUP_BY_FACULTY) {  // sync groups by faculty of parent category
            $idnumber = $category->faculty;
        }
        if(!$idnumber) {
            $idnumber =  substr($instance->name, 1, 100);  // truncate long names
        }

        if(!$group = groups_get_group_by_idnumber($coursegroup->courseid, $idnumber)) {
            $coursegroup->idnumber = $idnumber;
            $coursegroup->name = $idnumber;
            if($idnumber) {
                $groupid = groups_create_group($coursegroup, false, false, 'enrol_metapattern', $instance->id);
                if($ulpgcgroups = get_config('local_ulpgcgroups')) { 
                    include_once($CFG->dirroot.'/local/ulpgcgroups/lib.php');
                    local_ulpgcgroups_update_group_component($groupid, 'enrol_multicohort', $instance->id);  
                }
            }
        } else {
            $groupid = $group->id;
        }
    } elseif($syncgroup > 0) {
        $groupid = $DB->get_field('groups', 'id', array('id'=>$syncgroup), MUST_EXIST);
    }
    
    if($groupid) {
        $sql = "SELECT g.id, g.name
                FROM {groups} g
                JOIN {groups_members} gm ON gm.groupid = g.id 
                WHERE gm.component = 'enrol_metacat' AND gm.itemid = :eid AND g.id <> :gid AND g.courseid = :cid 
                GROUP BY g.id ";
        $params = array('eid'=>$instance->id, 'cid'=>$instance->courseid, 'gid'=>$groupid);
        $groups = $DB->get_records_sql($sql, $params);
        foreach($groups as $group) {
            groups_delete_group($group->id);  
        }
    }
    
    return $groupid;
}
