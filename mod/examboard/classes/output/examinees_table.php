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
 * The Examinees_table class holds data to display, manipulate and grade the users that ara been examined
 * keeps track of examinees, their tutors and grades
 *
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class examinees_table extends \flexible_table implements renderable {
    /** @var int the cmid of this instance. */
    public $cmid;
    
    /** @var int the id of the examboardmodule this data belongs to. */
    public $examboardid;

    /** @var Examination class, the exam object that this instance manage. */
    public $examination;
    
    /** @var int the id of the group used in the page. */
    public $groupid = 0;
    
    /** @var object the url to perform modifications on data. */
    public $editurl = false;

    /** @var bool the capabilities in this viewer. */
    public $canmanage = false;
    
    /** @var bool if this instance use any advanced grading method. */
    public $advancedgrading = false;
    
    /** @var bool if this examboard uses tutors or requires them. */
    public $usetutors = false;

    /** @var int grading estrategy from examboard instance . */
    public $grademode = false;

    /** @var int the maximum grade to be used. 0 is no grade, negative use scale. */
    public $grademax = false;

    /** @var int minumun number of separate grades to calculate final grade. */
    public $mingraders = 0;

    /** @var object the gradebook grade item for this instance of examboard PLUS scale . */
    public $gradeitem = false;
    
    /** @var modinfo the course module containing gradeable && submission data . */
    public $gradeable = false;
    
    /** @var modinfo the course module containing proposal complementary data . */
    public $proposal = false;

    /** @var modinfo the course module containing defense complementary data . */
    public $defense = false;

    /** @var string the word used . */
    public $chair = '';
    
    /** @var string the word used . */
    public $secretary = '';
    
    /** @var string the word used . */
    public $vocal = '';
    
    /** @var string the word used . */
    public $examinee = '';

    /** @var string the word used . */
    public $tutor = '';
    
    
    
    /**
     * Constructor
     * @param moodle_url $url
     * @param object $examboard the examboard record from database
     */
    public function __construct(\moodle_url $url, $examination, $examboard) {
        
        parent::__construct('examboard_examinees_table_viewer');
        $this->baseurl = clone $url;
        $this->cmid = $url->get_param('id');
        $this->groupid = $url->get_param('group');
        $this->examination = $examination;
        
        $this->examboardid  = $examboard->id;
        $this->usetutors    = $examboard->usetutors;
        $this->grademode    = $examboard->grademode;
        $this->grademax     = $examboard->grade;
        $this->mingraders   = $examboard->mingraders;
        $this->gradeitem    = examboard_get_grade_item($examboard->id, $examboard->course);
        
        $this->gradeitem->scale = examboard_get_scale($examboard->grade);
        
        $this->chair        = $examboard->chair;
        $this->secretary    = $examboard->secretary;
        $this->vocal        = $examboard->vocal;
        $this->examinee     = $examboard->examinee;
        $this->tutor        = $examboard->tutor;
        
        $mods = get_fast_modinfo($examboard->course)->get_cms();
        
        foreach(array('gradeable', 'proposal', 'defense') as $type) {
            if($examboard->{$type}) {
                foreach($mods as $cmid => $cm) {
                    if($cm->idnumber == $examboard->{$type}) {
                        $this->{$type} = $cm;
                        break;
                    }
                }
            }
        }
        
        $this->check_advanced_grading();
    }
    
    
    private function check_advanced_grading() {
        global $CFG;
        
        require_once($CFG->dirroot . '/grade/grading/lib.php');
    
        $context = \context_module::instance($this->cmid);
        $gradingmanager = get_grading_manager($context, 'mod_examboard', 'usergrades');
        $hasgrade = ($this->grademax != GRADE_TYPE_NONE );
        if ($hasgrade) {
            if ($controller = $gradingmanager->get_active_controller()) {
                $this->advancedgrading = true;
            }
        }
    }
}
