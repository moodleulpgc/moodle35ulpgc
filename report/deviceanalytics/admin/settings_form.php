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
 * Form File for settings.php
 * @see settings.php
 *
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * MoodleForm for the plugins settings
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deviceanalytics_settings_form extends moodleform{
    /**
     * Defines Elements
     */
    public function definition() {
        global $CFG;
        $mform = $this->_form;
        $startattr = array('readonly' => 'readonly');
        $mform->addElement('text', 'starttime', get_string('settings_starttime', 'report_deviceanalytics'), $startattr);
        $mform->setType('starttime', PARAM_RAW);
        $mform->addElement('hidden', 'id', '1');
        $mform->setType('id', PARAM_RAW);
        $mform->addElement('selectyesno', 'status', get_string('settings_status', 'report_deviceanalytics'));
        $mform->addElement('selectyesno', 'anonymous', get_string('settings_anonymous', 'report_deviceanalytics'));
        $mform->addElement('selectyesno', 'adminlog', get_string('settings_admin_log', 'report_deviceanalytics'));
        $this->add_action_buttons();
    }
}