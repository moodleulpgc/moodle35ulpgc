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
 * Plugin event classes are defined here.
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_examboard\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The user grade updated event class.
 *
 * @package    mod_examboard
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_graded extends base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'examboard_grades';
    }


    /**
     * Create instance of event.
     *
     * @param array $data common eventparams
     * @param \stdClass $grade
     * @return feedback_viewed
     */
    public static function create_from_grade($event, $grade) {
        $data = array(
            'objectid' => $grade->id,
            'relateduserid' => $grade->userid
        );

        $event['other']['exam'] = $grade->examid;
        
        $event = self::create(array_merge($event, $data));
        $event->add_record_snapshot('examboard_grades', $grade);
        return $event;
    }
    
    /**
     * Returns relevant UR params arrayL.
     *
     * @return array
     */
    public function set_url_params() {
        $params = array('view' => 'exam', 
                        'item' => $this->other['exam']);
        //http://localhost/moodle35ulpgc/mod/examboard/view.php?id=3521&view=graded&item=1&user=43&gid=7
        return $params;
    }
    
}
