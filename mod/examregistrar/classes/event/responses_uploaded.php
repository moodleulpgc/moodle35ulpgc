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
 * mod_examregistrar exam response files event.
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
class responses_uploaded extends responses_approved {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $user = $this->usertext();
        $site = $this->get_site(); 
        return "The $user has uoloaded response files '{$this->other['files']}' for session '{$this->other['session']}' exam '{$this->other['examid']}' 
                $site 
                in the Exam registrar with course module id '$this->contextinstanceid'. ";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventresponsesuploaded', 'mod_examregistrar');
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
}
