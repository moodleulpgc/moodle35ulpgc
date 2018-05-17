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
 * Library code for the autopopulate groups report
 *
 * @package   report_autogroups
 * @copyright 2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/grouplib.php');
require_once($CFG->dirroot.'/group/lib.php');

/**
* Stores syncing definition in DB from edit form
* @param int $courseid  course object id
* @param int $syncid  id of existing synczing for update
* @param stdclass $data object sync from form
* @return int syncid or 0 if failed
*/
function autogroups_save_sync($courseid, $syncid = 0, $data) {
    global $DB;

    $sync = new stdClass;
    $sync->course = $courseid;
    $sync->targetgroup = $data->targetgroup;
    $sync->searchterm = $data->searchterm;
    $sync->searchfield = $data->searchfield;
    $sync->sourceroles = implode(',', $data->sourceroles);
    $sync->visible = $data->visible;
    $sync->timemodified = time();

    $success = true;

    if($syncid &&  $rec = $DB->get_record('groups_autogroups', array('id'=>$syncid))) {
        // existing, update
        $sync->id = $syncid;
        //$sync->visible = $rec->visible;
        $success = $DB->update_record('groups_autogroups', $sync);
        if($success) {
            $success = $syncid;
            $action = "update rule";
        }
    } else {
        // not existing, inserting
        if($success = $DB->insert_record('groups_autogroups', $sync)) {
            $sync->id = $success;
            $action = "add rule";
        }
    }

    if($success) {
        autogroups_sync_targetgroup($sync->id, $courseid, $sync->targetgroup);

        //making log entry
        add_to_log($courseid, 'course',  'report autogroups '.$action,
            "report/autogroups/edit.php?cid={$courseid}&amp;sid={$sync->id}", $sync->id.' ('.$sync->searchterm.')');
    }



    return $success;
}

/**
* deletes a syncing definition
* @param int $courseid  course object id
* @param int $syncid group syncid of table groups_sync_groups
* @return bool true/false on success
*/
function autogroups_delete_sync($courseid, $syncid) {
    global $DB;

    $sync = $DB->get_record('groups_autogroups', array('id'=>$syncid, 'course'=>$courseid));
    $DB->delete_records('groups_autogroups', array('id'=>$syncid, 'course'=>$courseid));
    $DB->delete_records('groups_members', array('groupid'=>$sync->targetgroup, 'component'=>'report_autogroups', 'itemid'=>$syncid));
    //autogroups_sync_targetgroup($syncid, $courseid, $sync->targetgroup);
    //making log entry
    add_to_log($courseid, 'course', ' report autogroups delete rule',
        "report/autogroups/view.php?id={$courseid}", $sync->id.' ('.$sync->searchterm.')');


}

/**
* Run group membership syncing for a given target
* @param int $syncid group syncid of table groups_sync_groups
* @param int $courseid  course object id
* @param int $groupid  id of table groups

*/
function autogroups_sync_targetgroup($syncid, $courseid, $groupid) {
    global $DB;

    $target = $DB->get_record('groups', array('id'=>$groupid, 'courseid'=>$courseid));
    if(!$target) {
        mtrace(" invalid autogroups Target group id $groupid ");
        return;
    }

    //return;

    $sync = $DB->get_record('groups_autogroups', array('id'=>$syncid, 'course'=>$courseid), '*', MUST_EXIST);

    $sql = "SELECT gm.userid AS uid, gm.*
            FROM {groups_members} gm
            WHERE gm.groupid = ?
            GROUP BY gm.userid ";
    $currentusers = $DB->get_records_sql($sql, array($groupid));
    $enrolnotmeta = $DB->sql_like('e.enrol', ':meta', true, true, true);
    $coursesearch = $DB->sql_like('c.'.$sync->searchfield, ':search');
    $syncroles = empty($sync->sourceroles) ? array() : explode(',', $sync->sourceroles);
    list($inroles, $roleparams) = $DB->get_in_or_equal($syncroles, SQL_PARAMS_NAMED, 'rin', true, -1);
    $courseenrols = $DB->get_records_menu('enrol', array('courseid'=>$courseid), 'id ASC', 'id,courseid');
    list($incourses, $courseparams) = $DB->get_in_or_equal(array_keys($courseenrols), SQL_PARAMS_NAMED, 'cin');
    $sql = "SELECT ue.userid AS uid, ue.id AS ueid, ue.userid, c.id AS cid
            FROM {course} c
            JOIN {enrol} e ON (c.id = e.courseid AND e.status = 0 AND $enrolnotmeta )
            JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.status = 0  AND ue.timestart <= :now1 AND (ue.timeend = 0 OR ue.timeend <= :now2)  )
            JOIN {context} ctx ON (c.id = ctx.instanceid AND ctx.contextlevel = 50)
            JOIN {role_assignments} ra ON (ra.contextid = ctx.id AND ra.userid = ue.userid AND ra.roleid $inroles )

            JOIN {user_enrolments} cue ON (ue.userid = cue.userid AND cue.enrolid $incourses AND cue.status = 0 AND cue.timestart <= :now3 AND (cue.timeend = 0 OR cue.timeend <= :now4)  )
            WHERE $coursesearch
            GROUP BY ue.userid ";
    $now = time();
    $params = array('meta'=>'meta%', 'search'=>$sync->searchterm, 'now1'=>$now, 'now2'=>$now, 'now3'=>$now, 'now4'=>$now);
    $targetusers = $DB->get_records_sql($sql, $params+$roleparams+$courseparams);

    // new additions are those in target that are not in current
    $adds = array_diff(array_keys($targetusers), array_keys($currentusers));

    // deletions are those in current that are not in target
    $deletes = array_diff(array_keys($currentusers), array_keys($targetusers));

    // updates are those in current that are also on target
    $updates = array_intersect(array_keys($currentusers), array_keys($targetusers));

    foreach($adds as $userid) {
        groups_add_member($target, $userid, 'report_autogroups', $syncid);
    }

    if($deletes) {
        //get_in_or_equal($items, $type=SQL_PARAMS_QM, $prefix='param', $equal=true, $onemptyitems=false)
        list($insql, $params) = $DB->get_in_or_equal($deletes, SQL_PARAMS_NAMED, 'del') ;
        $select = "component = 'report_autogroups' AND itemid = :syncid AND userid $insql  ";
        $params['syncid'] = $syncid;

        $success = $DB->delete_records_select('groups_members', $select, $params);
        if($success) {
            $eventdata = new stdClass();
            foreach($deletes as $userid) {
                if($currentusers[$userid]->component == 'report_autogroups' && $currentusers[$userid]->itemid == $syncid) {
                    //trigger groups events
                    $eventdata->groupid = $groupid;
                    $eventdata->userid  = $userid;
                    events_trigger('groups_member_removed', $eventdata);
                }
            }
            //update group info
            $DB->set_field('groups', 'timemodified', time(), array('id'=>$groupid));
        }
    }

    if($updates) {
        list($insql, $params) = $DB->get_in_or_equal($updates, SQL_PARAMS_NAMED, 'ups') ;
        $select = "component = '' AND itemid = 0 AND userid $insql  ";
        $success = $DB->set_field_select('groups_members', 'component', 'report_autogroups',  $select, $params);
        $success = $DB->set_field_select('groups_members', 'itemid', $syncid,  $select, $params);
        if($success) {
            //update group info
            $DB->set_field('groups', 'timemodified', time(), array('id'=>$groupid));
        }
    }

}


/**
* Run all group membership syncings
* @param int $courseid  course object id, 0 means process all
*/
function autogroups_sync($courseid = 0) {
    global $DB;

    $params = array();
    $onecourse = '';
    if($courseid) {
        $onecourse = " AND gs.course = :course ";
        $params['course'] = $courseid;
    }

    $sql = "SELECT gs.id, gs.targetgroup, gs.course
            FROM {groups_autogroups} gs
            WHERE 1 $onecourse ";

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $target) {
        autogroups_sync_targetgroup($target->id, $target->course, $target->targetgroup);
    }
    $rs->close();
}
