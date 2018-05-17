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
 * @package    mod_examregistrar
 * @subpackage backup-moodle2
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/examregistrar/backup/moodle2/restore_examregistrar_stepslib.php'); // Because it exists (must)

/**
 * examregistrar restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_examregistrar_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // All the settings related to this activity will include this prefix
        $settingprefix = $this->info->modulename . '_' . $this->info->moduleid . '_';
        $userinfo = $this->get_setting_value('userinfo');
        $userinfor = false; // TODO  TODO /// TODO
        if($userinfo) {
            $settingname = $settingprefix . 'registrarincluded';
            $registrar_included = new restore_activity_generic_setting($settingname, base_setting::IS_BOOLEAN, true);
            $registrar_included->get_ui()->set_icon(new pix_icon('icon', get_string('pluginname', $this->modulename),
                $this->modulename, array('class' => 'iconlarge icon-post')));
            $this->add_setting($registrar_included);

            $settingname = $settingprefix . 'examsincluded';
            $exams_included = new restore_activity_generic_setting($settingname, base_setting::IS_BOOLEAN, true);
            $exams_included->get_ui()->set_icon(new pix_icon('icon', get_string('pluginname', $this->modulename),
                $this->modulename, array('class' => 'iconlarge icon-post')));
            $this->add_setting($exams_included);

            $included = $settingprefix . 'included';
            if ($this->plan->setting_exists($settingname)) {
                $activity_included = $this->plan->get_setting($settingname);
                $activity_included->add_dependency($registrar_included);
                $activity_included->add_dependency($exams_included);
            }
        }
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Choice only has one structure step
        $this->add_step(new restore_examregistrar_activity_structure_step('examregistrar_structure', 'examregistrar.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('examregistrar', array('intro'), 'examregistrar');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('EXAMREGISTRARVIEWBYID', '/mod/examregistrar/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('EXAMREGISTRARINDEX', '/mod/examregistrar/index.php?id=$1', 'course');

        return $rules;

    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * examregistrar logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('examregistrar', 'add', 'view.php?id={course_module}', '{examregistrar}');
        $rules[] = new restore_log_rule('examregistrar', 'update', 'view.php?id={course_module}', '{examregistrar}');
        $rules[] = new restore_log_rule('examregistrar', 'view', 'view.php?id={course_module}', '{examregistrar}');
        /// TODO add more rules for looggig

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        // Fix old wrong uses (missing extension)
        $rules[] = new restore_log_rule('examregistrar', 'view all', 'index?id={course}', null,
                                        null, null, 'index.php?id={course}');
        $rules[] = new restore_log_rule('examregistrar', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
