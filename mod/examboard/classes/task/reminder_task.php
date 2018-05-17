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
 * A scheduled task for examboard reminders.
 *
 *
 * @package    mod_examboard
 * @copyright  2018 Enrique castro  @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_examboard\task;

/**
 * Simple task to run the cron.
 */
class reminder_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('remindertask', 'examboard');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $DB;

        $examboards = $DB->get_recordset_select('examboard', 'usewarnings <> 0', array());
        
        if($examboards->valid()) {
            require_once($CFG->dirroot . '/mod/examboard/locallib.php');
            foreach($examboards as $examboard) {
                $count = examboard_process_reminders($examboard);
                mtrace("    ....Sent $count reminders from examboard instance {$examboard->id} '{$examboard->id}' "); 
            }
            $examboards->close();
        } else {
            mtrace("No examboard instances with active warnings"); 
            return;
        }
    }
}
