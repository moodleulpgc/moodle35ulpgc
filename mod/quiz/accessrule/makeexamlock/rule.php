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
 * Implementaton of the quizaccess_makeexamlock plugin.
 *
 * @package    quizaccess
 * @subpackage makeexamlock
 * @copyright  2016 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');


/**
 * A rule controlling the number of attempts allowed.
 *
 * @copyright  2016 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_makeexamlock extends quiz_access_rule_base {

    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {

        if (empty($quizobj->get_quiz()->makeexamlock) || 
                !get_config('quiz_makeexam', 'enabled') ) {
            return null;
        }

        return new self($quizobj, $timenow);
    }

    public function description() {
        $url = new moodle_url('/mod/quiz/report.php',
                array('id' => $this->quizobj->get_cmid(), 'mode' => 'makeexam'));

        $link = html_writer::link($url, get_string('gotomakeexam', 'quizaccess_makeexamlock')); 
        
        return html_writer::span($link, ' alert-info');
    }

    public function prevent_access() {
        return html_writer::span(get_string('makeexamlockingmsg', 'quizaccess_makeexamlock'), ' alert-success');
    }
    
    public function prevent_new_attempt($numprevattempts, $lastattempt) {
        return $this->prevent_access(); 
    }

    public function is_finished($numprevattempts, $lastattempt) {
        return true;
    }
    
    public function add_preflight_check_form_fields(mod_quiz_preflight_check_form $quizform,
            MoodleQuickForm $mform, $attemptid) {

        $mform->addElement('header', 'makeexamlockheader',
                get_string('makeexamlockheader', 'quizaccess_makeexamlock'));
        $mform->addElement('static', 'makeexamlockmessage', '',
                get_string('makeexamlockstatement', 'quizaccess_makeexamlock'));
        $mform->addElement('checkbox', 'makeexamlock', '',
                get_string('makeexamlocklabel', 'quizaccess_makeexamlock'));
    }

    public function validate_preflight_check($data, $files, $errors, $attemptid) {
        if (empty($data['makeexamlock'])) {
            $errors['makeexamlock'] = get_string('youmustagree', 'quizaccess_makeexamlock');
        }

        return $errors;
    }
    
    
    /**
     * Add any fields that this rule requires to the quiz settings form. This
     * method is called from {@link mod_quiz_mod_form::definition()}, while the
     * security seciton is being built.
     * @param mod_quiz_mod_form $quizform the quiz settings form that is being built.
     * @param MoodleQuickForm $mform the wrapped MoodleQuickForm.
     */
    public static function add_settings_form_fields(
            mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
            
        if(get_config('quiz_makeexam', 'enabled')) {    
            $mform->addElement('advcheckbox', 'makeexamlock',
                    get_string('makeexamlock', 'quizaccess_makeexamlock'),
                    get_string('explainmakeexamlock', 'quizaccess_makeexamlock'));
            $mform->addHelpButton('makeexamlock',
                    'makeexamlock', 'quizaccess_makeexamlock');
                    
            $rp = new ReflectionProperty('mod_quiz_mod_form', 'context');
            $rp->setAccessible(true);
            $context = $rp->getValue($quizform);
           
            if(!has_capability('quiz/makeexamlock:manage', $context)) {
                $mform->freeze('makeexamlock');
            }
        }
    }
    
    /**
     * Save any submitted settings when the quiz settings form is submitted. This
     * is called from {@link quiz_after_add_or_update()} in lib.php.
     * @param object $quiz the data from the quiz form, including $quiz->id
     *      which is the id of the quiz being saved.
     */
    public static function save_settings($quiz) {
        global $DB;
        if (empty($quiz->makeexamlock) || !get_config('quiz_makeexam', 'version') ) {
            $DB->delete_records('quizaccess_makeexamlock', array('quizid' => $quiz->id));
        } else {
            if (!$DB->record_exists('quizaccess_makeexamlock', array('quizid' => $quiz->id))) {
                $record = new stdClass();
                $record->quizid = $quiz->id;
                $record->makeexamlock = 1;
                $DB->insert_record('quizaccess_makeexamlock', $record);
            }
        }
    }

    /**
     * Delete any rule-specific settings when the quiz is deleted. This is called
     * from {@link quiz_delete_instance()} in lib.php.
     * @param object $quiz the data from the database, including $quiz->id
     *      which is the id of the quiz being deleted.
     * @since Moodle 2.7.1, 2.6.4, 2.5.7
     */
    public static function delete_settings($quiz) {
        global $DB;
        $DB->delete_records('quizaccess_makeexamlock', array('quizid' => $quiz->id));
    }

    /**
     * Return the bits of SQL needed to load all the settings from all the access
     * plugins in one DB query. The easiest way to understand what you need to do
     * here is probalby to read the code of {@link quiz_access_manager::load_settings()}.
     *
     * If you have some settings that cannot be loaded in this way, then you can
     * use the {@link get_extra_settings()} method instead, but that has
     * performance implications.
     *
     * @param int $quizid the id of the quiz we are loading settings for. This
     *     can also be accessed as quiz.id in the SQL. (quiz is a table alisas for {quiz}.)
     * @return array with three elements:
     *     1. fields: any fields to add to the select list. These should be alised
     *        if neccessary so that the field name starts the name of the plugin.
     *     2. joins: any joins (should probably be LEFT JOINS) with other tables that
     *        are needed.
     *     3. params: array of placeholder values that are needed by the SQL. You must
     *        used named placeholders, and the placeholder names should start with the
     *        plugin name, to avoid collisions.
     */
    public static function get_settings_sql($quizid) {
        return array(
            'makeexamlock',
            'LEFT JOIN {quizaccess_makeexamlock} makeexamlock ON makeexamlock.quizid = quiz.id',
            array());
    }

    
    
}
