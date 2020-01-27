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
 * Event observers used in tracker.
 *
 * @package    mod_tracker
 * @copyright  2019 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for mod_tracker.
 */
class mod_tracker_observer {

    /**
     * Execute autofill fields update
     *
     * @param array $fields tuples (elementid=>trackerid)
     */
    public static function synch_autofill_fields($fields) {
        global $CFG, $DB;
        
        if(empty($fields)) {
            return;
        }
        
        include_once($CFG->dirroot.'/mod/tracker/classes/trackercategorytype/trackerelement.class.php');
        foreach($fields as $eid => $tid) {
            try {
                $tracker = $DB->get_record('tracker', array('id' => $tid));
                list ($course, $cm) = get_course_and_cm_from_instance($tid, 'tracker'); 
                $elementobj = \trackerelement::find_instance_by_id($tracker, $eid);
                $context = \context_module::instance($cm->id);
                $elementobj->setcontext($context);
                $elementobj->autofill_options();
            } catch (\Exception $e) {
                mtrace("    autofill FAILED " . $e->getMessage());
            }        
        }
    }

    /**
     * Triggered by course events 
     *
     * @param int $courseid 
     */
    public static function course_added($courseid) {
        global $DB;
        
        // new course in a category 
        $sql = "SELECT e.id, eu.trackerid
                FROM {course} c
                JOIN {course_categories} cc ON cc.id = c.category
                JOIN {tracker_element} e ON e.paramchar1 = :type AND e.paramchar2 = cc.idnumber
                JOIN {tracker_elementused} eu ON eu.elementid = e.id
                WHERE c.id = :cid
                GROUP BY e.id";
        $params = array('type'=>'courses', 'cid'=>$courseid);
        if($fields = $DB->get_records_sql_menu($sql, $params)) {
            self::synch_autofill_fields($fields);
        }    
    }    
    
    /**
     * Triggered via role events.
     *
     * @param int $courseid
     * @param int $roleid
     */
    public static function role_changed($courseid, $roleid) {
        global $DB;
        
        $sql = "SELECT e.id, eu.trackerid
                FROM {tracker_element} e
                JOIN {tracker_elementused} eu ON eu.elementid = e.id
                JOIN {role} r ON r.shortname = e.paramchar2
                WHERE e.paramchar1 = :type  AND e.course = :cid AND r.id = :rid
                GROUP BY e.id";
        $params = array('type'=>'users_role', 'cid'=>$courseid, 'rid'=>$roleid);
        if($fields = $DB->get_records_sql_menu($sql, $params)) {
            self::synch_autofill_fields($fields);
        }    
    }
    
    /**
     * Triggered via groups events.
     *
     * @param int $groupid
     */
    public static function group_changed($groupid) {
        global $DB;

        // NOTE: this has to be as fast as possible.
        // group by e.id, if used on several trackers, use the same options
        $sql = "SELECT e.id, eu.trackerid
                FROM {groups} g 
                JOIN {tracker_element} e ON e.course = g.courseid AND e.paramchar1 = :type AND g.idnumber = e.paramchar2
                JOIN {tracker_elementused} eu ON eu.elementid = e.id
                WHERE g.id = :gid  AND g.idnumber IS NOT NULL 
                GROUP BY e.id";
                
        $params = array('type'=>'users_group', 'gid'=>$groupid);
        
        if($fields = $DB->get_records_sql_menu($sql, $params)) {
            self::synch_autofill_fields($fields);
        }
    }

    /**
     * Trigger autofill in elements without a matching group in course
     *
     * @param int $courseid
     */
    public static function cleanup_groups($courseid) {
        global $DB;

        $sql = "SELECT e.id, eu.trackerid
                FROM {tracker_element} e 
                JOIN {tracker_elementused} eu ON eu.elementid = e.id
                WHERE e.paramchar1 = :type AND e.course = :cid AND e.paramchar2 <> '' AND e.paramchar2 IS NOT NULL
                        AND NOT EXISTS(SELECT 1 FROM {groups} g WHERE g.courseid = e.course AND g.idnumber = e.paramchar2) 
                GROUP BY e.id";
        $params = array('type'=>'users_group', 'cid'=>$courseid);
        if($fields = $DB->get_records_sql_menu($sql, $params)) {
            self::synch_autofill_fields($fields);
        }
    }

    /**
     * Trigger autofill in elements users_groupings when inner groups changed members
     *
     * @param int $courseid
     * @param int $courseid
     */
    public static function update_groupings_group($courseid, $groupid) {
        global $DB;

        $sql = "SELECT e.id, eu.trackerid
                FROM {tracker_element} e 
                JOIN {tracker_elementused} eu ON eu.elementid = e.id
                JOIN {groupings} gp ON gp.courseid = e.course AND gp.idnumber = e.paramchar2
                JOIN {groupings_groups} gg ON gg.groupingid = gp.id AND gg.groupid = :gid
                WHERE e.paramchar1 = :type AND e.course = :cid AND e.paramchar2 <> '' AND e.paramchar2 IS NOT NULL 
                GROUP BY e.id";
        $params = array('type'=>'users_grouping', 'cid'=>$courseid, 'gid'=>$groupid);
        if($fields = $DB->get_records_sql_menu($sql, $params)) {
            self::synch_autofill_fields($fields);
        }
    }

    /**
     * Triggered via grouping events.
     *
     * @param int $groupid
     */
    public static function grouping_changed($groupingid) {
        global $DB;

        // NOTE: this has to be as fast as possible.
        // group by e.id, if used on several trackers, use the same options
        $sql = "SELECT e.id, eu.trackerid
                FROM {groupings} g 
                JOIN {tracker_element} e ON e.course = g.courseid AND e.paramchar1 = :type AND g.idnumber = e.paramchar2
                JOIN {tracker_elementused} eu ON eu.elementid = e.id
                WHERE g.id = :gid  AND g.idnumber IS NOT NULL 
                GROUP BY e.id";
                
        $params = array('type'=>'users_grouping', 'gid'=>$groupingid);
        if($fields = $DB->get_records_sql_menu($sql, $params)) {
            self::synch_autofill_fields($fields);
        }
    }

    /**
     * Trigger autofill in elements without a matching grouping in course
     *
     * @param int $courseid
     */
    public static function cleanup_groupings($courseid) {
        global $DB;

        $sql = "SELECT e.id, eu.trackerid
                FROM {tracker_element} e 
                JOIN {tracker_elementused} eu ON eu.elementid = e.id
                WHERE e.paramchar1 = :type AND e.course = :cid AND e.paramchar2 <> '' AND e.paramchar2 IS NOT NULL
                        AND NOT EXISTS(SELECT 1 FROM {groupings} g WHERE g.courseid = e.course AND g.idnumber = e.paramchar2) 
                GROUP BY e.id";
        $params = array('type'=>'users_grouping', 'cid'=>$courseid);
        if($fields = $DB->get_records_sql_menu($sql, $params)) {
            self::synch_autofill_fields($fields);
        }
    }
    
    
///////////////////////////////////////////////////////////////////////////////////    
//// Observers    
    
    /**
     * Observer for \core\event\course_created event.
     *
     * @param \core\event\course_created $event
     * @return void
     */
    public static function course_created(\core\event\course_created $event) {
        self::course_added($event->objectid);
    }
    
    /**
     * Observer for \core\event\course_restored event.
     *
     * @param \core\event\course_restored $event
     * @return void
     */
    public static function course_restored(\core\event\course_restored $event) {
        self::course_added($event->objectid);
    }
    
    /**
     * Observer for \core\event\course_updated event.
     *
     * @param \core\event\course_updated $event
     * @return void
     */
    public static function course_updated(\core\event\course_updated $event) {
        global $DB;
        
        self::course_added($event->objectid);
        
        //if updated category, and now used in other NOT intended by new category
        if(isset($event->other['shortname'])) {
            $sql = "SELECT e.id, eu.trackerid
                    FROM {course} c
                    JOIN {course_categories} cc ON cc.id = c.category
                    JOIN {tracker_element} e ON e.paramchar1 = :type AND e.paramchar2 <> cc.idnumber
                    JOIN {tracker_elementused} eu ON eu.elementid = e.id
                    JOIN {tracker_elementitem} ei ON ei.elementid = e.id
                    WHERE c.id = :cid AND ei.name = :shortname  
                    GROUP BY e.id";
            $params = array('type'=>'courses', 'cid'=>$event->objectid, 'shortname'=>$event->other['shortname']);
            if($fields = $DB->get_records_sql_menu($sql, $params)) {
                self::synch_autofill_fields($fields);
            }
        }
    }
    
    /**
     * Observer for \core\event\course_deleted event.
     *
     * @param \core\event\course_deleted $event
     * @return void
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;

        $params = array('type'=>'courses');
        $join = '';
        $where = '';
        
        if(isset($event->other['shortname'])) {
            $join = 'JOIN {tracker_elementitem} ei ON ei.elementid = ei.id ';
            $where = 'AND ei.name = :shortname ';
            $params['shortname'] = $event->other['shortname'];
        }
        
        $sql = "SELECT e.id, eu.trackerid
                FROM {tracker_element} e
                JOIN {tracker_elementused} eu ON eu.elementid = e.id
                $join
                WHERE e.paramchar1 = :type  $where
                GROUP BY e.id";
        
        if($fields = $DB->get_records_sql_menu($sql, $params)) {
            self::synch_autofill_fields($fields);
        }
    }

    /**
     * Observer for \core\event\role_assigned event.
     *
     * @param \core\event\role_assigned $event
     * @return void
     */
    public static function role_assigned(\core\event\role_assigned $event) {
        self::role_changed($event->courseid, $event->objectid);
        //$context = context::instance_by_id($event->contextid, MUST_EXIST);
    }    
    
    /**
     * Observer for \core\event\role_unassigned event.
     *
     * @param \core\event\role_unassigned $event
     * @return void
     */
    public static function role_unassigned(\core\event\role_unassigned $event) {
        self::role_changed($event->courseid, $event->objectid);
        //$context = context::instance_by_id($event->contextid, MUST_EXIST);
    }    
    
    /**
     * Observer for \core\event\group_created event.
     *
     * @param \core\event\group_created $event
     */
    public static function group_created(\core\event\group_created $event) {    
        self::group_changed($event->objectid);         
    }
    
     /**
     * Observer for \core\event\group_updated event.
     *
     * @param \core\event\group_updated $event
     */
    public static function group_updated(\core\event\group_updated $event) {       
        self::group_changed($event->objectid); 
        self::cleanup_groups($event->courseid); 
    }
    
    /**
     * Observer for \core\event\group_deleted event.
     *
     * @param \core\event\group_deleted $event
     */
    public static function group_deleted(\core\event\group_deleted $event) {
        self::cleanup_groups($event->courseid);  
        self::update_groupings_group($event->courseid, $event->objectid);        
    }    
    
     /**
     * Observer for \core\event\group_member_added event.
     *
     * @param \core\event\group_member_added $event
     */
    public static function group_member_added(\core\event\group_member_added $event) {     
        self::group_changed($event->objectid);
        self::update_groupings_group($event->courseid, $event->objectid);
    }    
    
     /**
     * Observer for \core\event\group_member_removed event.
     *
     * @param \core\event\group_member_removed $event
     */
    public static function group_member_removed(\core\event\group_member_removed $event) {       
        self::group_changed($event->objectid); 
        self::update_groupings_group($event->courseid, $event->objectid);
    }    
    
    
    /**
     * Observer for \core\event\grouping_created event.
     *
     * @param \core\event\grouping_created $event
     */
    public static function grouping_created(\core\event\grouping_created $event) {    
        self::grouping_changed($event->objectid);
    }
    
     /**
     * Observer for \core\event\grouping_updated event.
     *
     * @param \core\event\grouping_updated $event
     */
    public static function grouping_updated(\core\event\grouping_updated $event) {       
        self::grouping_changed($event->objectid);
        self::cleanup_groupings($event->courseid);
    }
    
    /**
     * Observer for \core\event\grouping_deleted event.
     *
     * @param \core\event\grouping_deleted $event
     */
    public static function grouping_deleted(\core\event\grouping_deleted $event) {
        self::cleanup_groupings($event->courseid);
    }
    
    
    /**
     * Observer for \core\event\grouping_group_assigned event.
     *
     * @param \core\event\grouping_group_assigned $event
     */
    public static function grouping_group_assigned(\core\event\grouping_group_assigned $event) {  
        self::grouping_changed($event->objectid);
    }    
    
    
    /**
     * Observer for \core\event\grouping_group_unassigned event.
     *
     * @param \core\event\grouping_group_unassigned $event
     */
    public static function grouping_group_unassigned(\core\event\grouping_group_unassigned $event) {  
        self::grouping_changed($event->objectid);
    }
    
}
