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
class responses_uploaded extends \core\event\base {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $user = $this->usertext();
        return "The $user has uploaded response files '{$this->other['files']}' for exam '{$this->other['examid']}' at site '{$this->other['bookedsite']}' 
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
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/examregistrar/view.php', array('id' => $this->contextinstanceid, 'tab'=>'printexams'));
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
    
    /**
     * Custom user text
     *
     * @return string
     */
    protected function usertext() {
        $usertext = "user with id '$this->userid'";
        if($this->userid != $this->relateduserid) {
            $usertext .= ", acting as user '$this->relateduserid',";
        }
    
        return $usertext;
    }
    
}


/**
 * mod_examregistrar response files deleted event class.
 *
 * @package    mod_examregistrar
 * @copyright  2015 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class responses_deleted extends responses_uploaded {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $user = $this->usertext();
        return "Response files '{$this->other['files']}' for the exam '{$this->other['examid']}' deleted by $user
                in the Exam registrar with course module id '$this->contextinstanceid'. ";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventresponsesdeleted', 'mod_examregistrar');
    }


    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
}


/**
 * mod_examregistrar response files accepted event class.
 *
 * @package    mod_examregistrar
 * @copyright  2015 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class responses_accepted extends responses_uploaded {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $user = $this->usertext();
        return "The $user has accepted response files '{$this->other['files']}' for exam '{$this->other['examid']}' at site '{$this->other['bookedsite']}' 
                in the Exam registrar with course module id '$this->contextinstanceid'. ";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventresponsesaccepted', 'mod_examregistrar');
    }
    
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
    
}


/**
 * mod_examregistrar distribute session files event class.
 *
 * @package    mod_examregistrar
 * @copyright  2015 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class responses_distributed extends responses_uploaded {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $user = $this->usertext();
        return "Session response files '{$this->other['session']}' distributed to exams by $user 
                in the Exam registrar with course module id '$this->contextinstanceid'. ";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventresponsesdistributed', 'mod_examregistrar');
    }
    
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
    
}


