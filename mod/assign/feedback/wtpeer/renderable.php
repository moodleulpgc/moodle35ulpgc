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
 * This file contains the definition for the renderable classes for the assignment feedback wtpeer plugin
 *
 * @package   assignfeedback_wtpeer
 * @copyright 2016 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * A renderable summary of wtpeer assessments
 *
 * @package   assignfeedback_wtpeer
 * @copyright 2016 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_wtpeer_summary implements renderable {
    /** @var int $cmid Course module id for constructing navigation links */
    public $cmid = 0;
    /** @var int $assignid assign instance id for this  */
    public $assignid = 0;
    /** @var stdclass $assessment object for a user */
    public $assessments = false;
    /** @var bool $canviewassesments whether assessment results are to be shown to users or not*/
    public $canviewassessments = false;
    /** @var int $whenviewassesments the date, if any, when assessments are published  */
    public $whenviewassessments = 0;
    /** @var bool $canviewgrade whether assessment final grades  are to be shown to users or not*/
    public $canviewgrade = false;
    /** @var int $whenviewgrade the date, if any, when final grades are published  */
    public $whenviewgrade = 0;
    /** @var bool $showexplain whether to show the link to garde analysis or not   */
    public $showexplain = false;
    /** @var int $hasungradedallocs whether this users has ungraded work as a marker */
    public $hasungradedallocs = 0;
    /** @var array $items the gradertypes in use, from assessment */
    public $items = array();
    
    /**
     * Constructor for this renderable class
     *
     * @param int $cmid - The course module id for navigation
     * @param int $userswithnewfeedback - The number of users with new feedback
     * @param int $feedbackfilesadded - The number of feedback files added
     * @param int $feedbackfilesupdated - The number of feedback files updated
     */
    public function __construct($cmid, $assignid, $assessment, 
                                $canviewassessments, $whenviewassessments, 
                                $canviewgrade, $whenviewgrade,
                                $showexplain = '',
                                $hasungradedallocs = 0) {
        $this->cmid = $cmid;
        $this->assignid = $assignid;
        $this->assessment = $assessment;
        $this->canviewassessments = $canviewassessments;
        $this->whenviewassessments = $whenviewassessments;
        $this->canviewgrade = $canviewgrade;
        $this->whenviewgrade = $whenviewgrade;
        $this->showexplain = $showexplain;
        $this->hasungradedallocs = $hasungradedallocs;
        $this->items = array_keys($assessment->countgrades);
    }
}

/**
 * A renderable summary of wtpeer allocation info
 *
 * @package   assignfeedback_wtpeer
 * @copyright 2016 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_wtpeer_allocationinfo implements renderable {
    /** @var int $cmid Course module id for constructing navigation links */
    public $cmid = 0;
    /** @var int $assignid assign instance id for this  */
    public $assignid = 0;
    /** @var string $title title for table  */
    public $title = '';
    /** @var array $allocations assigned to a user as marker, array keyed by item */
    public $allocations = array();
    /** @var array $grades submitted by a user as marker, array keyed by item */
    public $grades = array();
    /** @var array $dates the period for start/end grading, array keyed by item  */
    public $dates = array();
    /** @var int $peeraccessmode  */
    public $peeraccessmode = 0;
    /** @var int $hasungradedallocs whether this users has ungraded work as a marker */
    public $hasungradedallocs = 0;
    /** @var array $items the gradertypes in use, from assessment */
    public $items = array();
    
    /**
     * Constructor for this renderable class
     *
     * @param int $cmid - The course module id for navigation
     * @param int $userswithnewfeedback - The number of users with new feedback
     * @param int $feedbackfilesadded - The number of feedback files added
     * @param int $feedbackfilesupdated - The number of feedback files updated
     */
    public function __construct($cmid, $assignid, $title, $allocations, $grades, $dates, $peeraccessmode, $hasungradedallocs = 0) {
        $this->cmid = $cmid;
        $this->assignid = $assignid;
        $this->title = $title;
        $this->allocations = $allocations;
        $this->grades = $grades;
        $this->dates = $dates;
        $this->peeraccessmode = $peeraccessmode;
        $this->hasungradedallocs = $hasungradedallocs;
        $this->items = array_keys($allocations);
    }
}

/**
 * A renderable showing the list of assesments for a given submission
 *
 * @package   assignfeedback_wtpeer
 * @copyright 2016 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_wtpeer_item_assessments implements renderable {
    /** @var int $cmid Course module id for constructing navigation links */
    public $cmid = 0;
    /** @var class assign_feedback_plugin $plugin wtpeer assign instance  */
    public $plugin = false;
    /** @var moodle_url $actionurl for forms  */
    public $actionurl = false;
    /** @var array $grades of a submission, with markers names if allowed */
    public $grades = array();
    /** @var array $canviewmarkers whether user can see names of those marking  */
    public $canviewmarkers = array();
    /** @var int $showexplain if link/data to explain grades */
    public $showexplain = '';
    /** @var string $gradingmethod the garding method used in this gradable area */
    public $gradingmethod = '';
    /** @var bool $showlong whether show short or full list */
    public $showlong = false;
    
    /**
     * Constructor for this renderable class
     *
     * @param int $cmid - The course module id for navigation
     * @param int $userswithnewfeedback - The number of users with new feedback
     * @param int $feedbackfilesadded - The number of feedback files added
     * @param int $feedbackfilesupdated - The number of feedback files updated
     */
    public function __construct($cmid, assign_feedback_plugin $plugin, moodle_url $actionurl, 
                                        $grades, $canviewmarkers, $showexplain, $gradingmethod, $showlong) {
        $this->cmid = $cmid;
        $this->plugin = $plugin;
        $this->actionurl = clone $actionurl;
        $this->grades = $grades;
        $this->canviewmarkers = $canviewmarkers;
        $this->showexplain = $showexplain;
        $this->gradingmethod = 'gradingform_'.$gradingmethod;
        $this->showlong = $showlong;
    }
}


