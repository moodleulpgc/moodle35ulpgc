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
 * Defines backup_examregistrar_activity_task class
 *
 * @package    mod_examregistrar
 * @category   backup
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/examregistrar/backup/moodle2/backup_examregistrar_stepslib.php');
require_once($CFG->dirroot . '/mod/examregistrar/backup/moodle2/backup_examregistrar_settingslib.php');

/**
 * Provides the steps to perform one complete backup of the Choice instance
 */
class backup_examregistrar_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity
     */
    protected function define_my_settings() {
        global $DB;

        $examregistrar = $DB->get_record('examregistrar', array('id' => $this->activityid), '*', MUST_EXIST);
        $userinfo = $this->get_setting_value('userinfo');
        $userinfo = false; /// TODO  TODO 
        if($userinfo && empty($examregistrar->primaryreg) && $examregistrar->primaryidnumber) {

        // All the settings related to this activity will include this prefix
            $settingprefix = $this->modulename . '_' . $this->moduleid . '_';
            $settingname = $settingprefix . 'registrarincluded';
            $registrar_included = new backup_activity_included_setting($settingname, base_setting::IS_BOOLEAN, false);
            $registrar_included->get_ui()->set_icon(new pix_icon('icon', get_string('pluginname', $this->modulename),
                $this->modulename, array('class' => 'iconlarge icon-post')));
            $this->add_setting($registrar_included);

            $settingname = $settingprefix . 'examsincluded';
            $exams_included = new backup_activity_included_setting($settingname, base_setting::IS_BOOLEAN, false);
            $exams_included->get_ui()->set_icon(new pix_icon('icon', get_string('pluginname', $this->modulename),
                $this->modulename, array('class' => 'iconlarge icon-post')));
            $this->add_setting($exams_included);

            $included = $settingprefix . 'included';
            if ($this->plan->setting_exists($included)) {
                $activity_included = $this->plan->get_setting($included);
                $activity_included->add_dependency($registrar_included);
                $activity_included->add_dependency($exams_included);
            }
        }
    }

    /**
     * Defines a backup step to store the instance data in the examregistrar.xml file
     */
    protected function define_my_steps() {
        $this->add_step(new backup_examregistrar_activity_structure_step('examregistrar_structure', 'examregistrar.xml'));
    }

    /**
     * Encodes URLs to the index.php and view.php scripts
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the content with the URLs encoded
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        // Link to the list of examregistrars
        $search="/(".$base."\/mod\/examregistrar\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@EXAMREGISTRARINDEX*$2@$', $content);

        // Link to examregistrar view by moduleid
        $search="/(".$base."\/mod\/examregistrar\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@EXAMREGISTRARVIEWBYID*$2@$', $content);

        return $content;
    }
}
