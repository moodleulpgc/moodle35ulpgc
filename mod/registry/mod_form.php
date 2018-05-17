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
 * The main registry mod configuration form
 *
 * It uses the standard core Moodle formslib.
 *
 * @package    mod
 * @subpackage registry
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_registry_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $COURSE, $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('registryname', 'registry'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('description'));

        $mform->addElement('header', 'modconfig', get_string('modconfig', 'registry'));

        $name = get_string('timedue', 'registry');
        $mform->addElement('date_time_selector', 'timedue', $name, array('optional'=>true));
        $mform->addHelpButton('timedue', 'timedue', 'registry');
        $mform->setDefault('timedue', time()+7*24*3600);

        $modules = $DB->get_records_menu('modules', null, 'name ASC', 'id, name');
        $options = array('0'=>get_string('choose'));
        foreach($modules  as $key => $mod) {
            $options[$mod] = get_string('modulename', $mod);
        }
        //$options = array(0=>get_string('choose')) + $modules;
        $mform->addElement('select', 'regmodule', get_string('regmodule', 'registry'), $options);
        $mform->setDefault('regmodule', '0');
        $mform->addRule('regmodule', null, 'required', null, 'client');
        $mform->addRule('regmodule', null, 'minlength', 3, 'client');
        $mform->addHelpButton('regmodule', 'regmodule', 'registry');

        $options = array(-2=>get_string('catfromcourse', 'registry'), -1=>get_string('catfromidnumber', 'registry'));

        $mform->addElement('select', 'category', get_string('category', 'registry'), $options);
        $mform->setDefault('category', -2);
        $mform->addHelpButton('category', 'category', 'registry');

        $options = (array(-1=>get_string('any')) + range(0,100));
        $mform->addElement('select', 'regsection', get_string('regsection', 'registry'), $options);
        $mform->setDefault('regsection', -1);
        $mform->addRule('regsection', null, 'required', null, 'client');
        $mform->addHelpButton('regsection', 'regsection', 'registry');

        $options = array(0=>get_string('visibleall', 'registry'),
                         1=>get_string('visibleonly', 'registry'),
                         2=>get_string('visiblenot', 'registry'));
        $mform->addElement('select', 'visibility', get_string('visibility', 'registry'), $options);
        $mform->setDefault('visibility', 1);
        $mform->addHelpButton('visibility', 'visibility', 'registry');

        $options = array(0=>get_string('adminmodall', 'registry'),
                         1=>get_string('adminmodonly', 'registry'),
                         2=>get_string('adminmodnot', 'registry'));
        $mform->addElement('select', 'adminmod', get_string('adminmod', 'registry'), $options);
        $mform->setDefault('adminmod', 1);
        $mform->addHelpButton('adminmod', 'adminmod', 'registry');

        $mform->addElement('header', 'trackerconfig', get_string('trackerconfig', 'registry'));

        $notnull =  $DB->sql_isnotempty('course_modules', 'idnumber', true, false);
        $trackermodid = $DB->get_field('modules', 'id', array('name'=>'tracker'), MUST_EXIST);
        $sql = "SELECT cm.idnumber, t.name
                    FROM {tracker} t
                    JOIN {course_modules} cm ON (t.id = cm.instance AND cm.course = :course1 AND cm.module = :modid)
                WHERE $notnull AND t.course = :course2 ORDER by t.name ASC ";
        $trackers = $DB->get_records_sql_menu($sql, array('course1'=>$COURSE->id, 'course2'=>$COURSE->id, 'modid'=>$trackermodid));
        $trackermenu = array('0'=>get_string('choose'));
        foreach($trackers as $key => $tracker) {
            $trackermenu[$key] = format_string($tracker);
        }
        $mform->addElement('select', 'tracker', get_string('trackerid', 'registry'), $trackermenu);
        $mform->setDefault('tracker', '0');
        $mform->addHelpButton('tracker', 'trackerid', 'registry');
        $mform->addRule('tracker', null, 'required', null, 'client');
        $mform->addRule('tracker', null, 'minlength', 3, 'client');

        $mform->addElement('text', 'issuename', get_string('issuename', 'registry'), array('size'=>'40'));
        $mform->setDefault('issuename', '');
        $mform->setType('issuename', PARAM_TEXT);
        $mform->addHelpButton('issuename', 'issuename', 'registry');

        $mform->addElement('selectyesno', 'syncroles', get_string('syncroles', 'registry'));
        $mform->setDefault('syncroles', 0);
        $mform->setType('syncroles', PARAM_INT);
        $mform->addHelpButton('syncroles', 'syncroles', 'registry');

        // add standard elements, common to all modules
        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add action buttons,
        $this->add_action_buttons();
    }
}
