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
 * Local stuff for meta pattern enrolment plugin.
 *
 * @package    enrol
 * @subpackage metapattern
 * @copyright  2012 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
include_once($CFG->dirroot.'/group/lib.php');

/**
 * Event handler for meta enrolment plugin.
 *
 * We try to keep everything in sync via listening to events,
 * it may fail sometimes, so we always do a full sync in cron too.
 */
class enrol_metapattern_handler {

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

        $course = $DB->get_record('course', array('id'=>$courseid));
        if(!$course) {
            return;
        }

        // does anything want to sync with this parent?
        if (!$metas = $DB->get_records('enrol', array('enrol'=>'metapattern'))) {
            return;
        }

        $enrols = array();
        foreach($metas as $key => $instance) {
            $pattern = $instance->customchar1;
            $input = $course->{$instance->customchar2};
            if(enrol_metapattern_preg_sql_like($input, $pattern)) {
                $enrols[$key] = $instance;
            }
        }
        if(!$enrols) {
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

        $plugin = enrol_get_plugin('metapattern');

        if ($parentid == $instance->courseid) {
            // can not sync with self!!!
            return;
        }

        $context = context_course::instance($instance->courseid);

        if (!$parentcontext = context_course::instance($parentid, IGNORE_MISSING)) {
            // linking to missing course is not possible
            //role_unassign_all(array('userid'=>$userid, 'contextid'=>$context->id, 'component'=>'enrol_metapattern'));

            /// TODO unassign if not enrolled in other courses of categories


            return;
        }

        // list of enrolments in parent course (we ignore meta enrols in parents completely)
        // roles in parent courses (meta enrols must be ignored!)
        $params = array();
        $params['userid'] = $userid;
        $params['parentcourse'] = $parentid;
        $params['parentcontext'] = $parentcontext->id;
        $params['coursecontext'] = CONTEXT_COURSE;

        $coursepattern = $DB->sql_like('c.'.$instance->customchar2, ':pattern');
        $params['pattern'] = $instance->customchar1;

        $enabled = explode(',', $CFG->enrol_plugins_enabled);
        foreach($enabled as $k=>$v) {
            if ($v === 'metapattern') {
                unset($enabled[$k]); // no meta sync of meta roles
            }
        }
        list($enabled1, $eparams) = $DB->get_in_or_equal($enabled, SQL_PARAMS_NAMED, 'pe');
        $params = $params + $eparams;
        list($enabled2, $eparams) = $DB->get_in_or_equal($enabled, SQL_PARAMS_NAMED, 'ra');
        $enabled2 = "<> 'metapattern' ";
        $params = $params + $eparams;
        $syncroles = empty($instance->customtext1) ? array() : explode(',', $instance->customtext1);
        list($inroles, $roleparams) = $DB->get_in_or_equal($syncroles, SQL_PARAMS_NAMED, 'ri', true, -1);
        $params = $params + $roleparams;


        // enrolments in this parent
        $sql = "SELECT ue.*, e.id AS enrolid, ra.roleid
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
            // check if enrolled in other courses with pattern
                        $sql = "SELECT ue.id, ue.userid, e.id AS enrolid, ra.roleid
                    FROM {user_enrolments} ue
                    JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid <> :parentcourse AND e.enrol $enabled1)
                    JOIN {context} ctx ON (ctx.instanceid = e.courseid AND ctx.contextlevel = :coursecontext )
                    JOIN {course} c ON (c.id = e.courseid AND $coursepattern )
                    JOIN {role_assignments} ra ON (ra.contextid = ctx.id AND ra.userid = ue.userid
                                                    AND ra.component $enabled2 AND ra.roleid $inroles )
                    WHERE ue.userid = :userid AND c.id IS NOT NULL
                    GROUP BY ue.userid, ra.roleid ";
            $otherparents = $DB->get_records_sql($sql, $params);
            if(empty($otherparents)) {
                $instance->parentid = $parentid;
                self::user_not_supposed_to_be_here($instance, $ue, $context, $plugin);
                return;
            }
        }

        if (!enrol_is_enabled('metapattern')) {
            if ($ue) {
                role_unassign_all(array('userid'=>$userid, 'contextid'=>$context->id, 'component'=>'enrol_metapattern'));
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

        if($groupid = enrol_metapattern_update_syncgroup($instance, $parentid)) {
            groups_add_member($groupid, $ue->userid, 'enrol_metapattern', $instance->id); /// TODO cambiar a component + itemid in 2.4, 2.5
        }

        // add new roles
        // roles from this instance
        $roles = array();
        $ras = $DB->get_records('role_assignments', array('contextid'=>$context->id, 'userid'=>$userid, 'component'=>'enrol_metapattern', 'itemid'=>$instance->id));
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
                    role_assign($rid, $userid, $context->id, 'enrol_metapattern', $instance->id);
                }
            }
        } else {
            if (!isset($roles[$enrolledas])) {
                role_assign($enrolledas, $userid, $context->id, 'enrol_metapattern', $instance->id);
            }
        }

        // only active users in enabled instances are supposed to have roles (we can reassign the roles any time later)
        if ($ue->status != ENROL_USER_ACTIVE or $instance->status != ENROL_INSTANCE_ENABLED) {
            if ($roles) {
                role_unassign_all(array('userid'=>$userid, 'contextid'=>$context->id, 'component'=>'enrol_metapattern', 'itemid'=>$instance->id));
            }
            return;
        }

        // remove roles
        foreach ($roles as $rid) {
            if (!isset($parentroles[$rid])) {
                role_unassign($rid, $userid, $context->id, 'enrol_metapattern', $instance->id);
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
        $groupid = enrol_metapattern_update_syncgroup($instance, $instance->parentid);

        if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
            // purges grades, group membership, preferences, etc. - admins were warned!
            $plugin->unenrol_user($instance, $userid, $groupid);
            return;

        } elseif ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) { // ENROL_EXT_REMOVED_SUSPENDNOROLES
            // just suspend users and remove all roles (we can reassign the roles any time later)
            if ($ue->status != ENROL_USER_SUSPENDED) {
                $plugin->update_user_enrol($instance, $userid, ENROL_USER_SUSPENDED);
                role_unassign_all(array('userid'=>$userid, 'contextid'=>$context->id, 'component'=>'enrol_metapattern', 'itemid'=>$instance->id));
            }
            return;
        }
    }
}

/**
    * compares two strings, input & pattern, for match as SQL pattern match with wildcards would do.
    * http://stackoverflow.com/questions/11434305/simulating-like-in-php
    * @static
    * @param string $input the string to be checked
    * @param string $pattern the pattern with wildcards
    * @param string $pattern the pattern with wildcards
    * @return boolean if there is a match or not
    */
function enrol_metapattern_preg_sql_like ($input, $pattern, $escape = '\\') {
    // Split the pattern into special sequences and the rest
    $expr = '/((?:'.preg_quote($escape, '/').')?(?:'.preg_quote($escape, '/').'|%|_))/';
    $parts = preg_split($expr, $pattern, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

    // Loop the split parts and convert/escape as necessary to build regex
    $expr = '/^';
    $lastWasPercent = FALSE;
    foreach ($parts as $part) {
        switch ($part) {
            case $escape.$escape:
                $expr .= preg_quote($escape, '/');
                break;
            case $escape.'%':
                $expr .= '%';
                break;
            case $escape.'_':
                $expr .= '_';
                break;
            case '%':
                if (!$lastWasPercent) {
                    $expr .= '.*?';
                }
                break;
            case '_':
                $expr .= '.';
                break;
            default:
                $expr .= preg_quote($part, '/');
                break;
        }
        $lastWasPercent = $part == '%';
    }
    $expr .= '$/i';

    // Look for a match and return bool
    return (bool) preg_match($expr, $input);
}





/**
 * Sync all meta pattern links.
 *
 * @param int $courseid one course having an instance on pattern meta link, empty mean all
 * @param bool $verbose verbose CLI output
 * @return int 0 means ok, 1 means error, 2 means plugin disabled
 */
function enrol_metapattern_sync($courseid = NULL, $verbose = false) {
    global $CFG, $DB;

    // purge all roles if meta sync disabled, those can be recreated later here in cron
    if (!enrol_is_enabled('metapattern')) {
        if ($verbose) {
            mtrace('Meta sync plugin is disabled, unassigning all plugin roles and stopping.');
        }
        role_unassign_all(array('component'=>'enrol_metapattern'));
        return 2;
    }

    // unfortunately this may take a long time, execution can be interrupted safely
    @set_time_limit(0);
    raise_memory_limit(MEMORY_HUGE);

    if ($verbose) {
        mtrace('Starting user enrolment synchronisation...');
    }

    $meta = enrol_get_plugin('metapattern');
    $unenrolaction = $meta->get_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);
    $skiproles     = $meta->get_config('nosyncroleids', '');
    $skiproles     = empty($skiproles) ? array() : explode(',', $skiproles);

    // list of child instances
    if($courseid) {
        $metapatterninstances = $DB->get_records('enrol', array('enrol'=>'metapattern', 'courseid'=>$courseid));
    } else {
        $metapatterninstances = $DB->get_records('enrol', array('enrol'=>'metapattern'));
    }

    foreach ($metapatterninstances as $metainstance) {
        $courseid = $metainstance->courseid;
        if(!$courseid) {
            mtrace("metapattern instance {$metainstance->id} has no courseid ");
            continue;
        }
        if(!$metainstance->customchar1 ) {
            mtrace("metapattern instance {$metainstance->id} has no pattern in customchar1 ");
            continue;
        }
        if(!$metainstance->customchar2 ) {
            mtrace("metapattern instance {$metainstance->id} has no field in customchar2 ");
            continue;
        }

        $syncroles = empty($metainstance->customtext1) ? array() : explode(',', $metainstance->customtext1);
        $syncroles = array_diff($syncroles, $skiproles);
        $enrolledas = $metainstance->customint2;
        $context = context_course::instance($metainstance->courseid);

        // iterate through all users enrolled in parent pattern courses but not enrolled yet in this child one
        $params = array('courseid1'=> $courseid, 'courseid2'=> $courseid,
                        'coursecontext'=> CONTEXT_COURSE, 'status' => ENROL_USER_ACTIVE,
                        'pattern'=>$metainstance->customchar1, 'field'=>$metainstance->customchar2);
        $coursepattern1 = $DB->sql_like('c.'.$metainstance->customchar2, ':pattern1');
        $params['pattern1'] = $metainstance->customchar1;
        $coursepattern2 = $DB->sql_like('c.'.$metainstance->customchar2, ':pattern2');
        $params['pattern2'] = $metainstance->customchar1;

        $enabled = explode(',', $CFG->enrol_plugins_enabled);
        foreach($enabled as $k=>$v) {
            if ($v === 'metapattern') {
                unset($enabled[$k]); // no meta sync of meta roles
            }
        }
        list($enabled1, $eparams) = $DB->get_in_or_equal($enabled, SQL_PARAMS_NAMED, 'pe');
        $params = $params + $eparams;
        list($enabled2, $eparams) = $DB->get_in_or_equal($enabled, SQL_PARAMS_NAMED, 'ra');
        $enabled2 = "<> 'metapattern' ";
        $params = $params + $eparams;
        list($inroles, $roleparams) = $DB->get_in_or_equal($syncroles, SQL_PARAMS_NAMED, 'ri', true, -1);
        $params = $params + $roleparams;

        $sql = "SELECT pue.id, pue.userid, e.id AS enrolid, pue.status, ra.roleid,
                            pue.enrolid AS parentenrolid, ue.id AS currentenrol, c.id AS courseid
                FROM {user_enrolments} pue
                JOIN {enrol} pe ON (pe.id = pue.enrolid AND pe.courseid <> :courseid1 AND pe.enrol $enabled1 )
                JOIN {context} ctx ON (ctx.instanceid = pe.courseid AND ctx.contextlevel = :coursecontext )
                JOIN {course} c ON (pe.courseid = c.id  AND $coursepattern1 )
                JOIN {role_assignments} ra ON (ra.contextid = ctx.id AND ra.userid = pue.userid
                                                AND ra.component $enabled2 AND ra.roleid $inroles )
                JOIN {enrol} e ON (e.customchar1 = :pattern AND e.customchar2 = :field AND e.enrol = 'metapattern' AND e.courseid = :courseid2 )
            LEFT JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = pue.userid )
                WHERE $coursepattern2 AND ra.id IS NOT NULL AND c.id IS NOT NULL
                GROUP BY pue.userid, c.id, ra.roleid";
                //WHERE $coursepattern2 AND ue.id IS NULL AND ra.id IS NOT NULL AND c.id IS NOT NULL

        $rs = $DB->get_recordset_sql($sql, $params);

        $enrolled = array();
        foreach($rs as $ue) {
            if(!$ue->currentenrol) {
                if(!$enrolledas) {
                    $roleid = $ue->roleid;
                } else {
                    $roleid = $enrolledas;
                }
                if(!$roleid) {
                    $roleid = reset($syncroles);
                }
                if(!isset($enrolled[$ue->userid])) {
                    $meta->enrol_user($metainstance, $ue->userid, $roleid);
                    $enrolled[$ue->userid] = $ue->userid;
                    if ($verbose) {
                        mtrace("  enrolling user $ue->userid ==> in course {$metainstance->courseid} , with role $roleid ");
                    }
                }
            }
            if($syncgroup = enrol_metapattern_update_syncgroup($metainstance, $ue->courseid)) {
                groups_add_member($syncgroup, $ue->userid, 'enrol_metapattern', $metainstance->id); /// TODO cambiar a component + itemid in 2.4, 2.5
            }
        }
        $rs->close();

        // unenrol as necessary
        /// params array can be re-used
        $sql = "SELECT ue.*, c.id AS courseid
                FROM {user_enrolments} ue
                JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'metapattern' AND e.courseid = :courseid1 )
            LEFT JOIN ({user_enrolments} xpue
                        JOIN {enrol} xpe ON (xpe.id = xpue.enrolid AND xpe.courseid <> :courseid2 AND xpe.enrol $enabled1)
                        JOIN {context} ctx ON (ctx.instanceid = xpe.courseid AND ctx.contextlevel = :coursecontext )
                        JOIN {course} c ON (xpe.courseid = c.id  AND $coursepattern1)
                        JOIN {role_assignments} ra ON (ra.contextid = ctx.id AND ra.userid = xpue.userid
                                                        AND ra.component $enabled2 AND ra.roleid $inroles )
                    ) ON ($coursepattern2 AND xpue.userid = ue.userid )
                WHERE xpue.userid IS NULL";
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach($rs as $ue) {
            $syncgroup = enrol_metapattern_update_syncgroup($metainstance, $ue->courseid);
            if ($unenrolaction == ENROL_EXT_REMOVED_KEEP) {
                continue;
            } elseif ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                $meta->unenrol_user($metainstance, $ue->userid, $syncgroup);
                if ($verbose) {
                    mtrace("  unenrolling user $ue->userid ==> from course {$metainstance->courseid} ");
                }
            } elseif ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                // just disable and ignore any changes
                if ($ue->status != ENROL_USER_SUSPENDED) {
                    $meta->update_user_enrol($metainstance, $ue->userid, ENROL_USER_SUSPENDED);
                    role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$context->id, 'component'=>'enrol_metapattern'));
                    if ($verbose) {
                        mtrace("  suspending and removing all roles: $ue->userid ==> $metainstance->courseid");
                    }
                }
            }
        }
        $rs->close();

        $field = $metainstance->customchar2;
        $coursepattern3 = $DB->sql_like('pue.'.$field, ':pattern3');
        $params['pattern3'] = $metainstance->customchar1;

        // update status - meta enrols + start and end dates are ignored, sorry
        // note the trick here is that the active enrolment and instance constants have value 0
        $sql = "SELECT ue.userid, ue.enrolid, pue.pstatus, pue.parentcid
                FROM {user_enrolments} ue
                JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'metapattern' AND e.courseid = :courseid1 )
                JOIN (SELECT xpue.userid, xpe.courseid, c.{$field}, MIN(xpue.status + xpe.status) AS pstatus, c.id AS parentcid
                        FROM {user_enrolments} xpue
                        JOIN {enrol} xpe ON (xpe.id = xpue.enrolid AND xpe.courseid <> :courseid2 AND xpe.enrol $enabled1)
                        JOIN {context} ctx ON (ctx.instanceid = xpe.courseid AND ctx.contextlevel = :coursecontext )
                        JOIN {course} c ON (xpe.courseid = c.id  AND $coursepattern1)
                        JOIN {role_assignments} ra ON (ra.contextid = ctx.id AND ra.userid = xpue.userid
                                                        AND ra.component $enabled2 AND ra.roleid $inroles )
                    WHERE xpue.status =  ra.id IS NOT NULL AND c.id IS NOT NULL
                    GROUP BY xpue.userid, xpe.courseid, ra.roleid
                    ) pue ON ($coursepattern3 AND pue.userid = ue.userid)
                WHERE (pue.pstatus = 0 AND ue.status > 0) OR (pue.pstatus > 0 and ue.status = 0) ";
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach($rs as $ue) {
            $meta->update_user_enrol($metainstance, $ue->userid, $ue->pstatus);
            if ($verbose) {
                if ($ue->pstatus == ENROL_USER_ACTIVE) {
                    mtrace("  unsuspending: $ue->userid ==> $metainstance->courseid");
                } else {
                    mtrace("  suspending: $ue->userid ==> $metainstance->courseid");
                }
            }
            if($syncgroup = enrol_metapattern_update_syncgroup($metainstance, $ue->parentcid)) {
                groups_add_member($syncgroup, $ue->userid, 'enrol_metapattern', $metainstance->id); /// TODO cambiar a component + itemid in 2.4, 2.5
            }

        }
        $rs->close();

        // housekeeping, preventing users without role in the course
        $sql = "SELECT ue.id, ue.userid, ctx.id AS context
                FROM {user_enrolments} ue
                JOIN {enrol} e ON ue.enrolid = e.id
                JOIN {context} ctx ON ctx.instanceid = e.courseid AND ctx.contextlevel = 50
                LEFT JOIN {role_assignments} ra ON ra.contextid = ctx.id AND ra.userid = ue.userid
                WHERE e.courseid = :courseid AND ra.roleid IS NULL ";
        $params = array('courseid'=>$courseid);
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach($rs as $ue) {
            $roleid = '';
            if($enrolledas) {
                $roleid = $enrolledas;
            }
            if(!$roleid) {
                $roleid = reset($syncroles);
            }
            role_assign($roleid, $ue->userid, $ue->context, 'enrol_metapattern', $metainstance->id);

        }
        $rs->close();



    }

    if ($verbose) {
        mtrace('...user enrolment synchronisation finished.');
    }

    return 0;
}


/**
    * Return group to update group membership in a course
    *
    * @param stdClass $instance enrol instance
    * @return false/groupid
    */
function enrol_metapattern_update_syncgroup($instance, $courseid) {
    global $CFG, $DB;

    if(!$courseid) {
        return false;
    }

    if($ulpgc = get_config('local_ulpgccore', 'enabledadminmods')) {
        include_once($CFG->dirroot.'/local/ulpgccore/lib.php');
        $course = local_ulpgccore_get_course_details($courseid);
        $category = local_ulpgccore_get_category_details($course->category);
    }else {
        $course = $DB->get_record('course', array('id'=>$courseid), 'id, fullname, shortname, idnumber, ctype', MUST_EXIST);
        $category = $DB->get_record('course_categories', array('id'=>$course->category), 'id', MUST_EXIST);
    }

    $syncgroup = $instance->customint3;
    $coursegroup = new stdClass;
    $coursegroup->courseid = $instance->courseid;

    $groupid = 0;
    if($syncgroup < 0) {
        if($syncgroup == ENROL_METAPATTERN_GROUP_BY_SHORTNAME) { // sync groups by shortname of parent course
            $idnumber = $course->shortname;
        } elseif($syncgroup == METAPATTERN_GROUP_BY_IDNUMBER) { // sync groups by idnumber of parent course
            $idnumber = $course->idnumber;
        } elseif($syncgroup == ENROL_METAPATTERN_GROUP_BY_CTYPE) {  // sync groups by ctype of parent course
            $idnumber = $course->ctype;
        } elseif($syncgroup == ENROL_METAPATTERN_GROUP_BY_TERM) {  // sync groups by term of parent course
            $idnumber = get_string('term'.$course->term,'local_ulpgccore');
        } elseif($syncgroup == ENROL_METAPATTERN_GROUP_BY_CATIDNUMBER) { // sync groups by idnumber of parent category
            $idnumber = $category->idnumber;
        } elseif($syncgroup == ENROL_METAPATTERN_GROUP_BY_DEGREE) {  // sync groups by degree of parent category
            $idnumber = $category->degree;
        } elseif($syncgroup == ENROL_METAPATTERN_GROUP_BY_FACULTY) {  // sync groups by faculty of parent category
            $idnumber = $category->faculty;
        }
        if(!$idnumber) {
            $idnumber = $instance->name;
        }

        if(!$group = groups_get_group_by_idnumber($coursegroup->courseid, $idnumber)) {
            $coursegroup->idnumber = $idnumber;
            $coursegroup->name = $idnumber;
            $groupid = groups_create_group($coursegroup, false, false, 'enrol_metapattern', $instance->id);
            if($ulpgcgroups = get_config('local_ulpgcgroups')) { 
                include_once($CFG->dirroot.'/local/ulpgcgroups/lib.php');
                local_ulpgcgroups_update_group_component($groupid, 'enrol_multicohort', $instance->id);  
            }
        } else {
            $groupid = $group->id;
        }
    } elseif($syncgroup > 0) {
        $groupid = $DB->get_field('groups', 'id', array('id'=>$syncgroup), IGNORE_MISSING);
    }

    return $groupid;
}
