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
 * The Examenee_list hols presnetation of examinees in the correponding exams table column
 * keeps track of users and their tutors
 *
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class examinee_list implements renderable {
    /** @var string the word used . */
    public $examinee = '';
    /** @var string the word used . */
    public $tutor = '';
    /** @var int the exam ID  */
    public $examid = 0;
    /** @var array of  user records with names for these users  */
    public $users = false;
    
    /** @var array of  user records with names for tutors, indexed by userid for each tutor  */
    public $tutors = false;

    /** @var array of  user records with names for users excluded, indexed by userid for each tutor  */
    public $excluded = false;
    
        /** @var int if tutors are mandatory  */
    public $usetutors = false;
   
    /** @var array if the user has been dismished, indexed by userid  */
    public $excludes = false;


    /**
     * Constructor
     * @param string $idnumber - the code name
     * @param string $name teh board name
     */
    public function __construct($examinee, $tutor, $usetutors, $examid) {
        $this->examinee = $examinee;
        $this->tutor = $tutor;
        $this->examid = $examid;
        
        $this->usetutors = $usetutors;
    }
    
}
