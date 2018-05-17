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
 * Library code for the synchronize groups report
 *
 * @package   report_syncgroups
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
function syncgroups_save_sync($courseid, $syncid = 0, $data) {
    global $DB;

    $sync = new stdClass;
    $sync->course = $courseid;
    $sync->targetgroup = $data->targetgroup;
    $sync->parentgroups = implode(',', $data->parentgroups);
    $sync->visible = $data->visible;
    $sync->timemodified = time();

    $success = true;

    if($syncid &&  $rec = $DB->get_record('groups_syncgroups', array('id'=>$syncid))) {
        // existing, update
        $sync->id = $syncid;
        //$sync->visible = $rec->visible;
        $success = $DB->update_record('groups_syncgroups', $sync);
        if($success) {
            $success = $syncid;
        }
    } else {
        // not existing, inserting
        $success = $DB->insert_record('groups_syncgroups', $sync);
    }

    if($success) {
        syncgroups_sync_targetgroup($courseid, $sync->targetgroup);
    }

    return $success;
}

/**
* deletes a syncing definition
* @param int $courseid  course object id
* @param int $syncid group syncid of table groups_sync_groups
* @return bool true/false on success
*/
function syncgroups_delete_sync($courseid, $syncid) {
    global $DB;

    $sync = $DB->get_record('groups_syncgroups', array('id'=>$syncid, 'course'=>$courseid));
    $DB->delete_records('groups_syncgroups', array('id'=>$syncid, 'course'=>$courseid));
    syncgroups_sync_targetgroup($courseid, $sync->targetgroup);
}

/**
* Run group membership syncing for a given target
* @param int $courseid  course object id
* @param int $groupid  id of table groups

*/
function syncgroups_sync_targetgroup($courseid, $groupid) {
    global $DB;

    $target = $DB->get_record('groups', array('id'=>$groupid, 'courseid'=>$courseid));
    if(!$target) {
        mtrace(" invalid syncgroups Target group id $groupid ");
        return;
    }

    $groupparents = array();
    $parents = $DB->get_fieldset_select('groups_syncgroups', 'parentgroups', ' course = ? AND targetgroup = ? AND visible = 1', array($courseid, $groupid));
    foreach($parents as $parent) {
        $spars = explode(',', $parent);
        $groupparents = array_merge($groupparents, $spars);
    }
    $groupparents = array_unique($groupparents);

    if($groupparents) {
        // get all current members
        $currentmembers = $DB->get_fieldset_select('groups_members', 'userid', ' groupid = ? ', array($groupid));
        $currentmembers = array_unique($currentmembers);
        // get synced target members
        $syncedmembers = $DB->get_fieldset_select('groups_members', 'userid', ' groupid = ? AND component = ?', array($groupid, 'report_syncgroups'));
        $syncedmembers = array_unique($syncedmembers);

        // get all parents members
        list($inparents, $params) = $DB->get_in_or_equal($groupparents);
        $allparents = $DB->get_fieldset_select('groups_members', 'userid', " groupid $inparents ", $params);
        $allparents = array_unique($allparents);

        // new additions are those in parents that are not in current
        $adds = array_diff($allparents, $currentmembers);

        // deletions are those in synced that are not in parents
        $deletes = array_diff($syncedmembers, $allparents);
    } else {
        // no parents means no additions and deleting all synced users for this group
        $adds = array();
        $syncedmembers = $DB->get_fieldset_select('groups_members', 'userid', ' groupid = ? AND component = ?', array($groupid, 'report_syncgroups'));
        $deletes = array_unique($syncedmembers);
    }
    foreach($adds as $userid) {
        groups_add_member($target, $userid, 'report_syncgroups');
    }

    foreach($deletes as $userid) {
        if (groups_remove_member_allowed($target, $userid, 'report_syncgroups')) {
            groups_remove_member($target, $userid);
        }
    }

}


/**
* Run all group membership syncings
* @param int $courseid  course object id, 0 means process all
*/
function syncgroups_sync($courseid = 0) {
    global $DB;

    $params = array();
    $onecourse = '';
    if($courseid) {
        $onecourse = " AND gs.course = :course ";
        $params['course'] = $courseid;
    }

    $sql = "SELECT gs.id, gs.targetgroup, gs.course
            FROM {groups_syncgroups} gs
            WHERE 1 $onecourse
            GROUP BY gs.targetgroup, gs.course ";

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $target) {
        syncgroups_sync_targetgroup($target->course, $target->targetgroup);
    }
    $rs->close();
}
