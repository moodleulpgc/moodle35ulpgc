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
 * The assignfeedback_archive submissin archived event.
 *
 * @package    assignfeedback_archive
 * @copyright  2016 Enrique Castro 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignfeedback_archive\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The assignfeedback_archive submission archived event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string format: (optional) content format.
 * }
 *
 * @package    assignfeedback_archive
 * @since      Moodle 2.6
 * @copyright  2016 Enrique Castro 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submission_archived extends \mod_assign\event\submission_updated {

    /**
     * Init method.
     */
    protected function init() {
        parent::init();
        $this->data['objecttable'] = 'assign';
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventsubmissionarchived', 'assignfeedback_archive');
    }    
    
    /**
     * Returns non-localised description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $descriptionstring = "The user with id '$this->userid' archived a submission with " .
            "ID '{$this->other['submissionid']}' and attemptn '{$this->other['submissionattempt']}' ".
            "on the assignment with course module id '$this->contextinstanceid'";
        if (!empty($this->other['groupid'])) {
            $descriptionstring .= " for the group with id '{$this->other['groupid']}'.";
        } else {
            $descriptionstring .= ".";
        }

        return $descriptionstring;
    }
}
