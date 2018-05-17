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
 * makeexam block
 *
 * @package   block_makeexam
 * @copyright 2016 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_makeexam extends block_base {
    function init() {
        $this->title = get_string('title', 'block_makeexam');
    }

    function applicable_formats() {
        return array('site-index' => true, 'mod-quiz-review'=>true);
    }

    /**
     * Returns true or false, depending on whether this block has any content to display
     * and whether the user has permission to view the block
     *
     * @return boolean
     */
    function is_empty() {
        if (!isloggedin() || !has_capability('quiz/makeexam:submit', $this->page->context) ) {
            return true;
        }
        
        return parent::is_empty();
    }
    
    /**
     * Default return is false - header will be shown
     * @return boolean
     */
    function hide_header() {
        return false;
    }
    
    /**
     * Can be overridden by the block to prevent the block from being dockable.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return false;
    }

    /**
     * If overridden and set to false by the block it will not be hidable when
     * editing is turned on.
     *
     * @return bool
     */
    public function instance_can_be_hidden() {
        return false;
    }

    /**
     * If overridden and set to false by the block it will not be collapsible.
     *
     * @return bool
     */
    public function instance_can_be_collapsed() {
        return false;
    }
    
    function get_content () {
        global $USER, $CFG, $SESSION;

        if ($this->content !== NULL) {
            return $this->content;
        }
        $this->content = new stdClass();
        $this->content->footer = '';
        $this->content->text = '';
                      
        include_once($CFG->dirroot.'/mod/quiz/attemptlib.php');
        
        if(!$attemptid = optional_param('attempt', 0, PARAM_INT)) {
            $this->content->text = get_string('noattempt', 'quiz');
            return $this->content;
        }
        //http://nteleformacion.ulpgc.es/cv/ulpgctf16/mod/quiz/review.php?
        //id=6698&mode=makeexam&action=newattempt&examid=757&name&attempt=7758

        $mode = optional_param('mode', '', PARAM_ALPHANUMEXT);
        if($mode == 'makeexam') {
            $attemptobj = quiz_attempt::create($attemptid);
            if(!($attemptobj->is_preview() && $attemptobj->is_preview_user())) {
                return $this->content;
            }
            $cmid = $attemptobj->get_cmid();
            $aid = $attemptobj->get_attemptid();
            
            $action = optional_param('action', '', PARAM_ALPHANUMEXT);
            if($action == 'newattempt') {
                $this->content->text .= $this->makeexam_preview_button($cmid, $aid); 
            } elseif($action == 'continueattempt') {
                $this->content->text .= $this->makeexam_continue_button($cmid, $aid); 
            } else {
                $this->content->text .= $this->makeexam_return_button($cmid); 
            }
        } else {
            $this->content->text .= get_string('notmakeexamattempt', 'block_makeexam');
        }

        return $this->content;
    }
    
    /**
     * Print the HTML for the export quiz preview button, if the current user
     * is allowed to see one.
     */
    public function makeexam_preview_button($cmid, $aid) { 
        global $OUTPUT;
        /// TODO : check module exam
        $params = array('action'   => optional_param('action', '', PARAM_ALPHANUMEXT),
                        'examid'   => optional_param('examid', '', PARAM_INT),
                        'name'     => optional_param('name', '', PARAM_TEXT),
                        'attemptn' => optional_param('attemptn', '', PARAM_INT),
                        'newattempt'  =>  $aid);

        return $OUTPUT->single_button(new moodle_url('report.php', array('id'=>$cmid, 'mode'=>'makeexam') + $params),
                                        get_string('generateexam', 'quiz_makeexam'));
    }


    /**
     * Print the HTML for the export quiz preview button, if the current user
     * is allowed to see one.
     */
    public function makeexam_continue_button($cmid, $aid) {
        global $OUTPUT;
        /// TODO : check module exam
            $params = array('action'   => optional_param('action', '', PARAM_ALPHANUMEXT),
                        'examid'   => optional_param('examid', '', PARAM_INT),
                        'name'     => optional_param('name', '', PARAM_TEXT),
                        'attemptn' => optional_param('attemptn', '', PARAM_INT),
                        'currentattempt' => optional_param('currentattempt', '', PARAM_INT),
                        'newattempt'  =>  $aid);

        return $OUTPUT->single_button(new moodle_url('report.php', array('id'=>$cmid, 'mode'=>'makeexam') + $params),
                                        get_string('continueattempt', 'quiz_makeexam'));
    }


    /**
     * Print the HTML for the export quiz preview button, if the current user
     * is allowed to see one.
     */
    public function makeexam_return_button($cmid) { 
        global $OUTPUT;
        /// TODO : check module exam

        $button1 =  $OUTPUT->single_button(new moodle_url('report.php', array('id'=>$cmid, 'mode'=>'makeexam')),
                                                get_string('returnmakeexam', 'quiz_makeexam'));
        $button2 =  $OUTPUT->single_button(new moodle_url('edit.php', array('cmid'=>$cmid)),
                                                get_string('editquiz', 'quiz'));
        return $button1.$button2;
    }
    
}


