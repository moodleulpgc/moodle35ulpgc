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
 * The mod_examregistrar course module viewed event.
 *
 * @package mod_examregistrar
 * @copyright 2015 onwards Enrique castro @ ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_examregistrar\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The mod_examregistrar course module viewed event class.
 *
 * @package mod_examregistrar
 * @copyright 2015 onwards Enrique castro @ ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_viewed extends \core\event\course_module_viewed {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'examregistrar';
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $page = 'index';
        if($this->other['edit']) {
            $page = "page '{$this->other['edit']}'";
        }
        return "The user with id '$this->userid' viewed the management $page for '{$this->objecttable}' activity with " .
            "course module id '$this->contextinstanceid'.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventmanageviewed', 'examregistrar');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url("/mod/$this->objecttable/manage.php", array('id' => $this->contextinstanceid, 'edit'=>$this->other['edit']));
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();
        // Make sure there is a tab in other
        if (!isset($this->other['edit'])) {
            throw new \coding_exception('Examregistrar manage needs an edit var');
        }
    }
}


