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
 * mod_examregistrar item submitted event.
 *
 * @package    mod_examregistrar
 * @copyright  2015 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_examregistrar\event;

defined('MOODLE_INTERNAL') || die();

/**
 * mod_examregistrar booking submitted event class.
 *
 * @package    mod_examregistrar
 * @copyright  2015 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_submitted extends \core\event\base {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $booked = $this->other['booked'] ? 'booked' : 'unbooked';
        $usertext = "The user with id '$this->userid'";
        if($this->userid != $this->relateduserid) {
            $usertext .= ", acting as user '$this->relateduserid',";
        }
        return "$usertext has $booked the exam '{$this->other['examid']}' at site '{$this->other['bookedsite']}' as booking with id '$this->objectid' in the Exam registrar activity
            with course module id '$this->contextinstanceid'. ";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventbookingsubmitted', 'mod_examregistrar');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/examregistrar/view.php', array('id' => $this->contextinstanceid, 'tab'=>'booking'));
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'examregistrar_bookings';
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['examregid'])) {
            throw new \coding_exception('The \'examregid\' value must be set in other.');
        }
    }
}


/**
 * mod_examregistrar booking unbooked event class.
 *
 * @package    mod_examregistrar
 * @copyright  2015 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_unbooked extends booking_submitted {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "Previous bookings of user '{$this->relateduserid}' at any site for the exam '{$this->other['examid']}' set to unbooked in the Exam registrar activity
            with course module id '$this->contextinstanceid'. ";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventbookingunbooked', 'mod_examregistrar');
    }


    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'examregistrar_bookings';
    }
}


/**
 * mod_examregistrar booking unbookrelated event class.
 *
 * @package    mod_examregistrar
 * @copyright  2015 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_unbookrelated extends booking_unbooked {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "Bookings of user '{$this->relateduserid}' at any site and any other calls for the exam '{$this->other['examid']}' set to unbooked in the Exam registrar activity
            with course module id '$this->contextinstanceid'. ";
    }

}


