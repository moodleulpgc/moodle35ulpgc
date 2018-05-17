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
 * Form File for dashboard.php
 * @see dashboard.php
 *
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * MoodleForm for dashboard timerange
 * @package    report_deviceanalytics
 * @copyright  2016 Mark Heumueller <mark.heumueller@gmx.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deviceanalytics_dashboard_time_form extends moodleform{
    /**
     * Defines Form elements
     */
    public function definition() {
        global $CFG, $DB;
        $conf = $DB->get_record('report_deviceanalytics', array());
        $mform = $this->_form;
        $mform->addElement('date_selector', 'timestart', get_string('from'));
        $mform->addElement('date_selector', 'timefinish', get_string('to'));
        $mform->setDefault('timestart', $conf->starttime);
        $mform->setDefault('timefinish', time());
        $mform->addElement('submit', 'submitbutton', get_string('savechanges'));
    }
}