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
class exams_table extends \flexible_table implements renderable {
    /** @var int the cmid of this instance. */
    public $cmid;
    
    /** @var int the id of the examboardmodule this data belongs to. */
    public $examboardid;
    
    /** @var int the id of the group used in the page. */
    public $groupid = 0;
    
    /** @var object the url to perform modifications on data. */
    public $editurl = false;

    /** @var bool the capabilities in this viewer. */
    public $canviewall = false;
    
    /** @var bool the capabilities in this viewer. */
    public $canmanage = false;

    /** @var bool the capabilities in this viewer. */
    public $cangrade = false;

    /** @var bool the capabilities in this viewer. */
    public $cansubmit = false;
    
    /** @var bool the confirmation policy in this examboard. */
    public $requireconfirm = false;
    
    /** @var bool the confirmation policy in this examboard. */
    public $defaultconfirm = false;
    
    /** @var int timee beforehand to confirm participation as member. */
    public $confirmtime = false;
    
    /** @var bool if this examboard uses tutors or requires them. */
    public $usetutors = false;

    /** @var bool if this examboard uses groups by exam or requires them. */
    public $examgroups = false;

    /** @var string the grouping holding the groups . */
    public $groupingname = '';
    
    /** @var bool if board members  are shown to users. */
    public $publishboard = false;

    /** @var int time to publish the grades, hidden before. */
    public $publishboarddate = false;
    
    /** @var bool if grades are shown to users. */
    public $publishgrade = false;

    /** @var int time to publish the grades, hidden before. */
    public $publishgradedate = false;
    
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

    /** @var bool if the activity links to other modules containing user deliverables. */
    public $hasexternalactivity = false;
    
    /** @var array the list of examination objects this user can submit on. */
    public $hassubmits = false;
    
    /** @var array the list of examination objects this user can confirm on. */
    public $hasconfirms = false;

    /** @var array the list filters. */
    public $filters = false;
    
    /** @var array the list of examination objects in this viewer. */
    public $examinations = false;
    
    /**
     * Constructor
     * @param moodle_url $url
     * @param object $examboard the examboard record from database
     */
    public function __construct(\moodle_url $url, $examboard) {
    
        parent::__construct('examboard_exams_table_viewer');
        $this->baseurl = clone $url;
        $this->cmid = $url->get_param('id');
        $this->groupid = $url->get_param('group');
        
        $this->examboardid      = $examboard->id;
        $this->requireconfirm   = $examboard->requireconfirm;
        $this->defaultconfirm   = $examboard->confirmdefault;
        $this->confirmtime      = $examboard->confirmtime;
        $this->usetutors        = $examboard->usetutors;
        $this->examgroups       = $examboard->examgroups;
        $this->groupingname     = $examboard->groupingname;

        $this->publishboard     = $examboard->publishboard;
        $this->publishboarddate = $examboard->publishboarddate;
        $this->publishgrade     = $examboard->publishgrade;
        $this->publishgradedate = $examboard->publishgradedate;
        
        $this->hasexternalactivity = ($examboard->gradeable || $examboard->proposal || $examboard->defense);
        
        $this->chair        = $examboard->chair;
        $this->secretary    = $examboard->secretary;
        $this->vocal        = $examboard->vocal;
        $this->examinee     = $examboard->examinee;
        $this->tutor        = $examboard->tutor;
        $this->filters      = array();
    }
    
    
    public function table_filters_setup($baseurl) {
        global $DB;
        
        $filter = new \stdClass();
    // examperiods filter
    
        $filter->param = 'fperiod';
        $filter->paramtype = PARAM_ALPHANUMEXT;
        $filter->default = '';
        $filter->label = get_string('examperiod', 'examboard');
        $options = get_config('examboard', 'examperiods');
        $filter->options = array();
        foreach(explode("\n", $options) as $conv) {
            $key = strstr(trim($conv), ':', true);
            $filter->options[$key] = ltrim(strstr($conv, ':'), ':');
        }
        $this->filters['e.examperiod'] = clone $filter;
        
    // Board names  filter
        $filter->param = 'fbname';
        $filter->paramtype = PARAM_INT;
        $filter->default = 0;
        $filter->label = get_string('boardname', 'examboard');
        $sql = "SELECT id, name
                FROM {examboard_board} 
                WHERE examboardid = ? 
                GROUP BY name 
                ORDER BY name ASC ";
        $filter->options = $DB->get_records_sql_menu($sql, array($this->examboardid));
        $this->filters['b.name'] = clone $filter;
        
    // examsession  filter    
        $filter->param = 'fsess';
        $filter->paramtype = PARAM_INT;
        $filter->default = 0;
        $filter->label = get_string('examsession', 'examboard');
        $sql = "SELECT id, sessionname
                FROM {examboard_exam} 
                WHERE examboardid = ? 
                GROUP BY sessionname 
                ORDER BY sessionname ASC ";
        $filter->options = $DB->get_records_sql_menu($sql, array($this->examboardid));
        $this->filters['e.sessionname'] = clone $filter;
        
    // venues  filter    
        $filter->param = 'fvenue';
        $filter->paramtype = PARAM_INT;
        $filter->default = 0;
        $filter->label = get_string('examvenue', 'examboard');
        $sql = "SELECT id, venue
                FROM {examboard_exam} 
                WHERE examboardid = ? 
                GROUP BY venue 
                ORDER BY venue ASC ";
        $filter->options = $DB->get_records_sql_menu($sql, array($this->examboardid));
        $this->filters['e.venue'] = clone $filter;
        
    // participants users filter
        $filter->param = 'fuser';
        $filter->paramtype = PARAM_INT;
        $filter->default = 0;
        $filter->label = get_string('foruser', 'examboard');
        $userorder = $baseurl->get_param('uorder'); 
        $groupid = $baseurl->get_param('group'); 
        $context = \context_module::instance($this->cmid);
        $orderby = $userorder ? 'u.lastname' : 'u.firstname' ;
        $names = get_all_user_name_fields(true, 'u');
        $filter->options = get_enrolled_users($context, 'mod/examboard:view', $groupid, 'u.id, u.idnumber,'.$names, $orderby, 0, 0, true);  
        foreach($filter->options as $uid => $user) {
            $filter->options[$uid] = fullname($user);
        }
        $this->filters[''] = clone $filter;
         
        foreach($this->filters as $filter) {
            $baseurl->param($filter->param,  optional_param($filter->param, $filter->default, $filter->paramtype));
        } 
         
    }
    
    public function get_exams_table_filter_values() {
        global $DB; 
        $filters = array();
    
        foreach($this->filters as $key => $filter) {
            if(!$key) {
                continue;
            }
            //return filter only if something selected
            if($value = optional_param($filter->param, $filter->default, $filter->paramtype)) {
                switch($filter->param) {
                    case 'fbname'   : $value = $DB->get_field('examboard_board', 'name', array('id'=>$value));
                                    break;
                    case 'fsess'    : $value = $DB->get_field('examboard_exam', 'sessionname', array('id'=>$value));
                                    break;
                    case 'fvenue'   : $value = $DB->get_field('examboard_exam', 'venue', array('id'=>$value));
                                    break;
                } 
                $filters[$key] = $value;
            }
        }
    
        return $filters;
    }    
    
}
