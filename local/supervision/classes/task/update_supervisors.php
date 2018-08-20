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
 * A scheduled task.
 *
 * @package    local_supervision
 * @subpackage ungraded_assign
 * @copyright  2018 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_supervision\task;

use core\task\scheduled_task;

/**
 * Simple task to trigger supervission stats collection 
 * @copyright  2018 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_supervisors extends scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('updatesupervisors', 'local_supervision');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG;
        
        // we have ULPGC tables & data
        if(get_config('local_supervision', 'synchsupervisors') && get_config('local_sinculpgc', 'version')){
            include_once($CFG->dirroot.'/local/supervision/locallib.php');
            if($config->enabledepartments) {
                mtrace(" sinculpgc assigning supervisor roles at departments");
                supervision_ulpgc_update_supervisors('department');
            }
            if($config->enablefaculties) {
                mtrace(" sinculpgc assigning supervisor roles at faculties");
                supervision_ulpgc_update_supervisors('category');
            }
        }
    }

}
