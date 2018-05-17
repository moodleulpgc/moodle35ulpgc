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
 * An adhoc task for executing timed management job
 *
 * @package    tool_batchmanage
 * @copyright  2016 Enrique Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_batchmanage\task;

/**
 * A scheduled task for updating langpacks.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stored_managementjob_task extends \core\task\adhoc_task {

    /**
     * Run the job 
     */
    public function execute() {
        global $CFG;

        $customdata = $this->get_custom_data();
        $job = $customdata->managejob;
        
        include_once($CFG->dirroot.'/admin/tool/batchmanage/managejobplugin.php');
        $managejob = \batchmanage_managejob_plugin::create($job); 
        $managejob->formsdata = get_object_vars($customdata->jobdata);
        
        $managejob->execute(false);
        
        if (!empty($CFG->skiplangupgrade)) {
            mtrace('Langpack update skipped. ($CFG->skiplangupgrade set)');

            return;
        }

        $controller = new \tool_langimport\controller();
        if ($controller->update_all_installed_languages()) {
            foreach ($controller->info as $message) {
                mtrace($message);
            }
            return true;
        } else {
            foreach ($controller->errors as $message) {
                mtrace($message);
            }
            return false;
        }

    }

}
