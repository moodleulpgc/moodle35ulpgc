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
class board_name  implements renderable {
    /** @var string the board tiyle  */
    public $title = '';
    /** @var string the board idnumber  */
    public $idnumber = '';
    /** @var string the board name  */
    public $name = '';
    /** @var int if the board is active or not  */
    public $active = true;

    
    /**
     * Constructor
     * @param string $idnumber - the code name
     * @param string $name teh board name
     */
    public function __construct($title, $idnumber, $name, $active) {
        $this->title = $title;
        $this->idnumber = $idnumber;
        $this->name = $name;
        $this->active = $active;
    }
    
    public static function from_record($rec) {
        return new board_name($rec->title, $rec->idnumber, $rec->name, $rec->boardactive);
    }

}
