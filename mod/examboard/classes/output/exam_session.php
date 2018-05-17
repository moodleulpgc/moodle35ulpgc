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
 * Class definition for mod_examboard exams_table viewer
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_examboard\output;

use renderable;                                                                                                                     
 
defined('MOODLE_INTERNAL') || die();

/**
 * The Exams_table class holds data to get and manipulate an exam instance. 
 * keeps track of examiners, examinees, venues, dates etc for an examination event
 *
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exam_session  implements renderable {
        /** @var string the exam session name  */
    public $name = '';
    /** @var string the examvenue  */
    public $venue = '';
    /** @var int the exam date  */
    public $examdate = 0;
    /** @var int the exam duration  */
    public $duration = 0;

    
    /**
     * Constructor
     * @param string $idnumber - the code name
     * @param string $name teh board name
     */
    public function __construct($name, $venue, $examdate, $duration) {
        $this->name = $name;
        $this->venue = $venue;
        $this->examdate = $examdate;
        $this->duration = $duration;
    }
    
    public static function from_record($rec) {
        return new exam_session($rec->sessionname, $rec->venue, $rec->examdate, $rec->duration);
    }

}
