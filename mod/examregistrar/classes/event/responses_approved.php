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
 * mod_examregistrar response files submitted event class.
 *
 * @package    mod_examregistrar
 * @copyright  2015 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class responses_approved extends \core\event\base {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $user = $this->usertext();
        $site = $this->get_site(); 
        return "The $user has approved response files '{$this->other['files']}' for session '{$this->other['session']}' exam '{$this->other['examid']}' 
                $site
                in the Exam registrar with course module id '$this->contextinstanceid'. ";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventresponsesapproved', 'mod_examregistrar');
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
    
    /**
     * Custom user text
     *
     * @return string
     */
    protected function get_site() {    
        $site = '';
        if(isset($this->other['bookedsite'])) {
            $site = "at site '{$this->other['bookedsite']}' ";
        }
        $and = $site ? ' and ' : 'at ';
        if(isset($this->other['room'])) {
            $site = $and."room '{$this->other['room']}' ";
        }
        return $site;
    }
    
}
