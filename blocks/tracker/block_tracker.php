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

/** Block Tracker
 * A Moodle block to display tracker issus warnings
 * @package blocks
 * @author: Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/mod/tracker/locallib.php');

class block_tracker extends block_list {

    /**
     * Sets the block name and version number
     *
     * @return void
     **/
    function init() {
        $this->title = get_string('blocktitle', 'block_tracker');
        $this->version = 2012042200;
    }
    function preferred_width() {
        return 210;
    }

    function applicable_formats() {
        return array('my' => true,  'site-index'=>true,  'course'=>true, 'tag' => false, 'mod' => false);
    }

    function instance_allow_multiple() {
        return false;
    }

    function has_config() {
        return true;
    }

    function instance_allow_config() {
        return false;
    }

    function get_content() {
        global $CFG, $DB, $USER;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $config = get_config('block_tracker');

        $id = $config->tracker; 
        
        if (! $tracker = $DB->get_record('tracker', array('id'=>$id))) {
            return $this->content;
        }

        $messageline = get_string('firstline', 'block_tracker');

        $levels = explode(',',$config->status);
        list($insql, $params) = $DB->get_in_or_equal($levels, SQL_PARAMS_NAMED, 'st_');

        $select = " reportedby = :userid AND trackerid = :trackerid AND status $insql AND   usermodified < resolvermodified AND userlastseen < resolvermodified";
        $params['userid'] = $USER->id;
        $params['trackerid'] = $tracker->id;
        //$fields = 'id, summary, datereported, reportedby, assignedto, status, resolution, timemodified, usermodified';

        if($issues = $DB->get_records_select('tracker_issue', $select, $params, 'usermodified DESC')) {
            $statuskeys = array(POSTED => 'posted',
                        OPEN => 'open',
                        RESOLVING => 'resolving',
                        WAITING =>  'waiting',
                        TESTING => 'testing',
                        RESOLVED => 'resolved',
                        ABANDONNED => 'abandonned',
                        TRANSFERED => 'transfered');

            $this->content->items[] = $messageline;
            $this->content->icons[] = '';

            foreach ($issues as $issue) {
                $this->content->items[] = '<a href="'.$CFG->wwwroot.'/mod/tracker/view.php?t='.$tracker->id.'&amp;issueid='.$issue->id.'">'.($tracker->ticketprefix.$issue->id.': '.$issue->summary).'</a>';
                $this->content->icons[] = '<img src="'.$CFG->wwwroot.'/blocks/tracker/pix/'.$statuskeys[$issue->status].'.gif" class="icon" alt="" />';
            }
        }

        return $this->content;
    }


}
