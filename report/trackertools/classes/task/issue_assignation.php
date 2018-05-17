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
namespace report_trackertools\task;

class issue_assignation extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('assigntask', 'report_trackertools');
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

        require_once($CFG->dirroot . '/report/trackertools/locallib.php');

        $autoassigns = $DB->get_records('report_trackertools_devq', array('visible'=>1));
        $trackerid = false;
        $tracker = false;
        
        foreach($autoassigns as $assign) {
            if($assign->trackerid != $trackerid) {
                $tracker = $DB->get_record('tracker', array('id'=> $assign->trackerid));
                $trackerid = $assign->trackerid;
            }
            if($tracker && $trackerid) {
                mtrace("... Running report trackertools " ;
                try {
                    $count = report_trackertools_issue_assignation($tracker, $assign->queryid, $assign->userid);
                    mtrace("   ... Assigned $count new issues to user {$assign->userid} " ;
                } catch (\Exception $e) {
                    mtrace("... REPORT FAILED " . $e->getMessage());
                }        
            }
        }
    }
}
