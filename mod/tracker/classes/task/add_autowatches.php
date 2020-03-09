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
 * A scheduled task for Report Tracker Tools.
 *
 * @package report_trackertools
 * @copyright 2018 Enrique Castro
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_tracker\task;

class add_autowatches extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('autowatchestask', 'tracker');
    }

    /**
     * Function to be run periodically according to the moodle cron
     * This function searches for things that need to be done, such
     * as sending out mail, toggling flags etc ...
     *
     * Runs any automatically scheduled reports weekly or monthly.
     *
     * @return boolean
     */
    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/tracker/locallib.php');

        // group by e.id, if used on several trackers, use the same options
        $sql = "SELECT e.id, eu.trackerid
                FROM {tracker_element} e
                JOIN {tracker_elementused} eu ON eu.elementid = e.id
        
                WHERE e.paramchar1 LIKE 'users\_%' AND e.paramint2 > 0
                GROUP BY e.id ";
        $autowatches = $DB->get_records_sql_menu($sql, null);
        
        if($autowatches) {
            include_once($CFG->dirroot.'/mod/tracker/classes/trackercategorytype/trackerelement.class.php');
        }
        
        mtrace("... Running tracker autowatches ");
        foreach($autowatches as $eid => $tid) {
            try {
                $tracker = $DB->get_record('tracker', array('id' => $tid));
                list ($course, $cm) = get_course_and_cm_from_instance($tid, 'tracker'); 
                $elementobj = \trackerelement::find_instance_by_id($tracker, $eid);
                mtrace("    autowatch {$elementobj->name}");
                $context = \context_module::instance($cm->id);
                $elementobj->setcontext($context);
                $elementobj->add_autowatches();
            } catch (\Exception $e) {
                mtrace("    autowatch FAILED " . $e->getMessage());
            }        
        }
    }
}
