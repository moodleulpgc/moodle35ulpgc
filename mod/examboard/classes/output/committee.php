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
class committee  implements renderable {
    /** @var int the board id  */
    public $id = 0;
    /** @var int if the board is active or not  */
    public $active = true;
    /** @var array the examiners with names  */
    public $members = false;
    /** @var array the exams assigned to this committe  */
    public $assignedexams = false;
    /** @var array of existing confirmations for these users, indexed by exam, userid  */
    public $confirmations = false;
    /** @var array of existing notifications for these users, indexed by exam, userid  */
    public $notifications = false;
    /** @var bool if the current user can edit & change. */
    public $canmanage = false;
    /** @var bool the confirmation policy in this examboard. */
    public $requireconfirm = false;
    /** @var bool the confirmation policy in this examboard. */
    public $defaultconfirm = false;
    /** @var string the word used . */
    public $chair = '';
    /** @var string the word used . */
    public $secretary = '';
    /** @var string the word used . */
    public $vocal = '';

    /**
     * Constructor
     * @param string $idnumber - the code name
     * @param string $name teh board name
     */
    public function __construct($id, $active, $members, $requireconfirm, $defaultconfirm, $chair, $secretary, $vocal) {
        $this->id = $id;
        $this->active = $active;
        $this->members = $members;
        $this->requireconfirm = $requireconfirm;
        $this->defaultconfirm = $defaultconfirm;
        $this->chair    = $chair;
        $this->secretary= $secretary;
        $this->vocal = $vocal;
    }

}
