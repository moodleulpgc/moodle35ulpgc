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
 * Moodle renderer used to display special elements of the lesson module
 *
 * @package   mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

namespace mod_examboard\output;

use plugin_renderer_base;  
use html_writer;
use pix_icon;
use moodle_url;

use stdClass;

 
class renderer extends plugin_renderer_base {

    /**
     * Generates the view exams table page
     *
     * @param object $examboard record from DB with module instance information
     * @param object $cm Course Module cm_info
     * @param exams_table $viewer object with the examinations list and vieweing options
     * @return string
     */
    public function view_exams($examboard, $cm, $viewer) {
        $output = '';
        
        $output .= $this->heading(format_string($examboard->name), 2, null);

        if ($examboard->intro) {
            $output .= $this->box(format_module_intro('examboard', $examboard, $cm->id), 'generalbox', 'intro');
        }
        
        //$output .= groups_print_activity_menu($cm, $viewer->baseurl, true);
        
        //TODO //TODO //TODO //TODO //TODO //TODO //TODO //TODO 
        
        //add list of non assigned boards
        
        $output .= $this->render($viewer);
        
        $output .= $this->box($this->view_page_buttons($viewer->editurl, $viewer->canmanage, $viewer->hassubmits, $viewer->hasconfirms), 'generalbox pagebuttons');
        
        return $output;
    }

    /**
     * Generates the view exams table page
     *
     * @param object $examboard record from DB with module instance information
     * @param object $cm Course Module cm_info
     * @param exams_table $viewer object with the examinations list and vieweing options
     * @return string
     */
    public function view_user_grade_page($examboard, $exam, $user) {
        global $CFG, $USER;
        
        require_once($CFG->dirroot . '/mod/examboard/grading_form.php');
        
        require_capability('mod/examboard:grade', $this->page->context);
        
        $output = '';    
    
        $title = get_string('gradinguser', 'examboard', $this->format_exam_name($exam));
        $output .= $this->heading($title, 2);    
        
        $output .= $this->output->container_start('usersummary');
        $output .= $this->output->box_start('boxaligncenter usersummarysection');
        $output .= $this->output->user_picture($user);
        $output .= $this->output->spacer(array('width'=>30));
        
        $urlparams = array('id' => $user->id, 'course'=>$examboard->course);
        $url = new moodle_url('/user/view.php', $urlparams);
        $fullname = fullname($user);
        foreach(get_extra_user_fields($this->page->context) as $extrafield) {
            $extrainfo[] = $user->$extrafield;
        }
        if (count($extrainfo)) {
            $fullname .= ' (' . implode(', ', $extrainfo) . ')';
        }
        $output .= $this->output->action_link($url, $fullname);
        
        $output .= $this->output->box_end();
        $output .= $this->output->container_end();
        
        $output .= $this->heading(get_string('submissionstatus', 'examboard'), 3);    
        
        $mods = get_fast_modinfo($examboard->course)->get_cms();
        
        $output .= $this->output->container_start('submissionsummary');
        foreach(array('gradeable', 'proposal', 'defense') as $field) {
            if($examboard->$field) {
                foreach($mods as $cmid => $cm) {
                    if($cm->idnumber == $examboard->$field) {
                        break;
                    }
                }
                if($link = $this->gradeable_link($cm, $user->id, get_string($field, 'examboard'))) {
                    $output .= $link.'  ';
                }
            }
        }
        $output .= $this->output->container_end();
        
        $grade = examboard_get_grader_grade($exam->id, $user->id, true);
        
        $gradingdisabled = examboard_grading_disabled($examboard, $user->id);
                
        $params = array('userid' => $user->id,
                        'gradingdisabled' => $gradingdisabled,
                        'gradinginstance' => examboard_get_grading_instance($examboard, $user->id, $grade, $gradingdisabled),
                        'examboard' => $examboard,
                        'currentgrade' => $grade,

        );
        
        $mform = new \mod_examboard_grade_form(null, $params, 'post', '', array('class'=>'gradeform'));
        
        $output .= $this->output->box_start('boxaligncenter gradeform');
        $output .= $this->moodleform($mform);
        $output .= $this->output->box_end();
    
        return $output;
    }

    /**
     * Generates the grading explanation page for advanced grading
     *
     * @param object $examboard record from DB with module instance information
     * @param object $exam examination object
     * @param object $grade record from the DB
     * @return string
     */
    public function view_grading_explanation($examboard, $exam, $grade, $user, $grader) {
        global $CFG, $PAGE, $USER;

        $context = $this->page->context;
        if($USER->id != $grade->userid) { 
            require_capability('mod/examboard:manage', $context);
        }
        
        require_once($CFG->dirroot . '/grade/grading/lib.php');

        $gradingmanager = get_grading_manager($context, 'mod_examboard', 'usergrades');
        $hasgrade = ($examboard->grade != GRADE_TYPE_NONE );
        if ($hasgrade) {
            if ($controller = $gradingmanager->get_active_controller()) {
                $menu = make_grades_menu($examboard->grade);
                $controller->set_grade_range($menu, $examboard->grade > 0);
                $gradefordisplay = $controller->render_grade($PAGE,
                                                                $grade->id,
                                                                examboard_get_grade_item($examboard->id, $examboard->course),
                                                                '',
                                                                false);

            }
        }

        //print_object($gradefordisplay);
        
        $output = '';
        
        $title = get_string('gradinguser', 'examboard', $this->format_exam_name($exam));
        $output .= $this->heading($title, 2);    
        
        $output .= $this->output->container_start('usersummary');
        $output .= $this->output->box_start('boxaligncenter usersummarysection');
        $output .= $this->output->user_picture($user);
        $output .= $this->output->spacer(array('width'=>30));
        
        $urlparams = array('id' => $user->id, 'course'=>$examboard->course);
        $url = new moodle_url('/user/view.php', $urlparams);
        $fullname = fullname($user);
        $extrainfo = array();
        foreach(get_extra_user_fields($this->page->context) as $extrafield) {
            $extrainfo[] = $user->$extrafield;
        }
        if (count($extrainfo)) {
            $fullname .= ' (' . implode(', ', $extrainfo) . ')';
        }
        $output .= $this->output->action_link($url, $fullname);
        
        $output .= $this->output->box_end();
        $output .= $this->output->container_end();

        $output .= $this->output->container_start('feedback');
        $output .= $this->output->box_start('boxaligncenter feedbacktable');
        $t = new \html_table();

        if ($grader) {
            // Grader.
            $row = new \html_table_row();
            $cell1 = new \html_table_cell(get_string('gradedby', 'assign'));
            $userdescription = $this->output->user_picture($grader) .
                               $this->output->spacer(array('width'=>30)) .
                               fullname($grader);
            $cell2 = new \html_table_cell($userdescription);
            $row->cells = array($cell1, $cell2);
            $t->data[] = $row;
        }

        // Grade.
        if (isset($gradefordisplay)) {
            $row = new \html_table_row();
            $cell1 = new \html_table_cell(get_string('grade'));
            $cell2 = new \html_table_cell($gradefordisplay);
            $row->cells = array($cell1, $cell2);
            $t->data[] = $row;

            // Grade date.
            $row = new \html_table_row();
            $cell1 = new \html_table_cell(get_string('gradedon', 'assign'));
            $cell2 = new \html_table_cell(userdate($grade->timemodified));
            $row->cells = array($cell1, $cell2);
            $t->data[] = $row;
        }
        

        $output .= html_writer::table($t);
        $output .= $this->output->box_end();

        $output .= $this->output->container_end();
        
        
        $url = new moodle_url('/mod/examboard/view.php', array('id' => $examboard->cmid,
                                                                'view' => 'exam',
                                                                'item' => $exam->id,
                                                                ));
        $output .= $this->single_button($url, get_string('returntoexam', 'examboard'), 'get',
                                                array('class' => 'continuebutton'));
        
        
        return $output;
    }
    
    
    /**
     * Generates the view single board page
     *
     * @param object $board record from DB with board info
     * @param object $url moodle url for actions
     * @param committee $committee object with members data 
     
     * @param array $otherexams collection of exam records this board can be assigned
     *              excluded those examd with grades or where members are tutors 
     *              Arrays indexed by examid  and conaininh title, idnumber and sessionname  
     * @return string
     */
    public function view_board($board, $url, $committee, $otherexams) {
        $output = '';
        
        $name = $board->title.' '.$board->idnumber;
        $output .= $this->heading(format_string($name), 2);
        if($board->name) {
            $output .= $this->heading(format_string($board->name), 4);
        }

        $editurl = new moodle_url('/mod/examboard/edit.php', $url->params());
        $editurl->param('board', $board->id);

        $editurl->param('action', 'boardactive');
        $active = $board->active ? get_string('yes') : get_string('no'); 
        if($committee->canmanage) {
            $action = $board->active ? get_string('inactive', 'examboard') : get_string('active', 'examboard'); 
            $active .= '  &nbsp; '.$this->single_button($editurl, $action, 'get',
                                                        array('class' => 'boardactivebutton'));
        }
        
        
        $output .= $this->box(html_writer::span(get_string('boardactive', 'examboard'), 'label').
                                            ' &nbsp '.$active, 'iteminfo');  
        $output .= $this->view_board_exams(clone($url), $committee->assignedexams, get_string('assignedexams', 'examboard'));
        
        if($hasmembers = !empty($committee->members)) {
            $output .= $this->box($this->view_board_filter($url), 'generalbox tablefilter');
            $output .= $this->view_board_table($board, $editurl, $committee);
        } else {
            $output .= $this->heading(get_string('nothingtodisplay'));
        }
        
        $output .= $this->box($this->view_board_buttons($url, $editurl, $committee, $otherexams), 'generalbox pagebuttons');
        
        return $output;
    }
    
    /**
     * returns a select form to specify if deputy members as viewed or not 
     * and another select for user name format
     *
     * @param object $course The course record
     * @param array $examboard Array conting examboard data
     * @param object $cm Course Module cm_info
     * @param object $context The page context
     * @param array of exam records from db
     * @return string
     */
    public function view_board_filter($url) {
        $output = '';
        
        $output = 'aquí va el filtro';
        
        return $output;
    }
    
    /**
     * Prints a list of exam names with header
     *
     * @param array $exams collection of exams
     * @param string $label the string tu use as label
     * @return string
     */
    public function view_board_exams($url, $exams, $label) {
        $output = '';
        
        $url->param('view', 'exam');
        //$names = array_map(array($this, 'format_exam_name'), $exams);
        foreach($exams as $eid => $exam) {
            $url->param('item', $eid);
            $name = $this->format_exam_name($exam);
            $exams[$eid] = html_writer::link($url, $name);
        }
        
        $output .= $this->box_start('iteminfo');
            $output .= $this->box($label, 'label'); 
            $output .= $this->box(html_writer::alist($exams), 'nameslist');
        $output .= $this->box_end();
        return $output;
    }
    
    
    /**
     * Generates the main action buttons of the page, according to capabilities
     *
     * @param object $cm Course Module cm_info
     * @param object $context The page context
     * @param array of exam records from db
     * @return string
     */
    public function view_sort_filter($cmid, $baseurl, $viewall) {
        global $SESSION;
    
        $output = '';
        $userorder = $baseurl->get_param('uorder'); 
        $groupid = $baseurl->get_param('group');   

        list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'examboard');
        $context = \context_module::instance($cmid);

        $nf = '';
        if(isset($SESSION->nameformat)) {
            $options = array(0 => get_string('firstname'),
                                1 => get_string('lastname'));

            $nf = $this->single_select($baseurl, 'uorder', $options, $userorder, null, null, array('label'=>get_string('userorder', 'local_ulpgcgroups')));                            
        }                    
                            
        $g = groups_print_activity_menu($cm, $baseurl, true);
        
        $u = '';
        if($viewall) {
            $userid = $baseurl->get_param('fuser');   
            $orderby = $userorder ? 'u.lastname' : 'u.firstname' ;
            $names = get_all_user_name_fields(true, 'u');
            $options = get_enrolled_users($context, 'mod/examboard:view', $groupid, 'u.id, u.idnumber,'.$names, $orderby, 0, 0, true);  
            foreach($options as $uid => $user) {
                $options[$uid] = fullname($user);
            }
            $options = array(0 => get_string('any')) + $options;
            
            $u = $this->single_select($baseurl, 'fuser', $options, $userid, null, null, array('label'=>get_string('foruser', 'examboard')));
        }
      
        $output = '<div class="clearer clearfix"></div>'.$nf.' '.$u.' '.$g.'<div class="clearer clearfix"></div>';
      
        return $output;
    }
    
    public function format_exam_name($exam, $title = false) {
        $name = '';
        if($title) {
            $name = $exam->title.' ';
        }
        $name .= $exam->idnumber;
        if(isset($exam->sessionname) && $exam->sessionname) {
            $name .= ' ('.$exam->sessionname.')';
        }
        return $name;
    }
    
    
    public function show_select_button_form($url, $options, $select, $action) {
        $output = '';
    
        $output .= $this->container_start('actionselect '. $select);
        
        $attributes = array('id'=> 'examboardactionform_'.$select,
                            'action' => $url,
                            'method' => 'post');
        $output .= html_writer::start_tag('form', $attributes); 
        $output .= html_writer::input_hidden_params($url, array($select)); 

        reset($options);
        $selected = key($options);
        $output .= html_writer::label(get_string('choose'.$select, 'examboard'), $select);
        $output .= html_writer::select($options, $select, $selected, false);
        $output .= ' &nbsp; ';
        $output .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string($action, 'examboard'), 'name' => 'submitted_'.$select));      
       
        $output .= $this->container_end();
        
        return $output;
    }
    
    
    
    /**
     * Generates the main action buttons of the page, according to capabilities
     *
     * @param object $course The course record
     * @param array $examboard Array conting examboard data
     * @param object $cm Course Module cm_info
     * @param object $context The page context
     * @param array of exam records from db
     * @return string
     */
    public function view_page_buttons($url, $canmanage, $hassubmits, $hasconfirms) {
        $output = ''; 
        
        if($canmanage) {
            $url->param('action', 'addexam');
            $output .= $this->single_button($url, get_string('addexam', 'examboard'), 'get',
                                                    array('class' => 'continuebutton'));
        }
        
        if($hassubmits) {
            //students can submit from here
            $url->param('action', 'submit');
            
            // DO NOT use submitt for the moment. Just gradeables.
            //$output .= $this->show_select_button_form($url, $hassubmits, 'exam', 'submit');
        }
        
        if($hasconfirms) {
            //graders can confirm here
            $url->param('action', 'boardconfirm');
            $output .= $this->show_select_button_form($url, $hasconfirms, 'exam', 'boardconfirm');
        }
        
        return $output;
    }
    
    
    /**
     * Generates the main action buttons of the board page
     *
     * @param object $url of the module view The course record
     * @param object $editurl url for managing editing
     * @param array of exam records from db
     * @return string
     */
    public function view_board_buttons($url, $editurl, $committee, $otherexams) {
        $output = ''; 
        
        $output .= $this->single_button($url, get_string('returntoexams', 'examboard'), 'get',
                                                array('class' => 'continuebutton'));
        
        if($committee->canmanage) {
            $name = $committee->members ? 
                            get_string('editmembers', 'examboard') : get_string('addmembers', 'examboard');
            $editurl->param('action', 'editmembers');
            $output .= $this->single_button($editurl, $name, 'get',
                                                    array('class' => 'continuebutton'));
                            
            if($committee->members && $otherexams) {
                //managers 
                foreach($otherexams as $eid => $exam) {
                    $otherexams[$eid] = $exam->idnumber;
                    if(isset($exam->sessionname) && $exam->sessionname) {
                        $otherexams[$eid] .= ' ('.$exam->sessionname.')';
                    }
                }
                
                $editurl->param('action', 'assignexam');
                $output .= $this->show_select_button_form($editurl, $otherexams, 'exam', 'assignexam');
            }
            
            if($committee->members) {
                $options = array(
                    EXAMBOARD_USERTYPE_MEMBER   => get_string('usermembers', 'examboard'),
                    EXAMBOARD_USERTYPE_TUTOR    => get_string('usertutors', 'examboard'),
                    EXAMBOARD_USERTYPE_STAFF    => get_string('userstaff', 'examboard'),
                    EXAMBOARD_USERTYPE_ALL      => get_string('userall', 'examboard'),
                    );
                foreach($committee->members as $member) {
                    $options[$member->uid] = fullname($member);
                }
                
                $editurl->param('action', 'notify');
                $output .= $this->show_select_button_form($editurl, $options, 'usertype', 'notify');
            }
        }
                                                
        return $output;    
    }
    
    
    public function render_board_name(board_name $tribunal) {
        $output = '';
        $output .= $tribunal->title . '<br />'; 
        $output .= html_writer::span($tribunal->idnumber, 'boardidnumber');
        if($tribunal->name) {
            $output .= '<br />' . $tribunal->name;
        }
        
        
        
        
        return $this->box($output, 'boardname');
    }
    
    
    public function render_exam_session(exam_session $session) {
        $output = '';
        if($session->venue) {
            $output .= $this->box($session->venue, 'sessionvenue');
        }
        $output .= $this->box(userdate($session->examdate), 'sessiondate');
        if($session->duration) {
            $output .=$this->box('('.format_time($session->duration).')', 'sessiondate');
        }
        return $output;
    }
    
    
    public function render_committee(committee $board) {
        $output = '';
        
        $class = $board->active ? '' : ' dimmed ';
        $output .= $this->container_start( $class);
        
        foreach($board->members as $user) {
            if($user->sortorder == 0) {
                $label = $board->chair;
            } elseif($user->sortorder == 1) {
                $label = $board->secretary;
            } else {
                $label = $board->vocal.'&nbsp'.($user->sortorder - 1);
            }
            
            $confirm = $user->confirmed ? html_writer::tag('span', '<i class="fa check-square-o"></i>') : '';
            $output .= $this->container_start();
            $output .= $label.'<br />'.$this->format_name($user, $board->is_downloading). '  '. $confirm ;
            $output .= $this->container_end();

        }
        
        $output .= $this->container_end();
        
        return $output;
    }
    
    
    public function format_name($user, $downloading, $field = 'userid') {
        $name = fullname($user); 
        if(!$downloading) {
            $url = new moodle_url('/user/view.php', array('id' => $user->$field, 'course' => $this->page->course->id));
            $name = $this->user_picture($user, array('size'=>24)).' '.html_writer::link($url, $name);
        }
        return $name;
    }
    
    
    public function render_examinee_list(examinee_list $list) {
        $output = '';    

        $examinees = array();
            
        foreach($list->users as $uid => $user) {
            $name = $this->format_name($user, $list->is_downloading); 
            $examinee = html_writer::div($name, 'examineename');
            
            $tutor = '';
            $other = '';
            if(isset($list->tutors[$uid][0]) && $list->tutors[$uid][0]->main) {
                //we have a main tutor
                $name = $this->format_name($list->tutors[$uid][0], $list->is_downloading, 'tutorid'); 
                $label = html_writer::div($list->tutor, 'tutortitle');   
                $tutor = html_writer::div($name, 'tutorname');   
                $tutor = html_writer::div($label.$tutor, 'tutor');   
                array_shift($list->tutors[$uid]);
            }
            if(isset($list->tutors[$uid])) {
                $label = html_writer::div(' ', 'tutortitle');
                if(!empty($list->tutors[$uid])) {
                    foreach($list->tutors[$uid] as $k => $other) {
                        $list->tutors[$uid][$k] = html_writer::div($this->format_name($other, $list->is_downloading, 'tutorid'), 'tutorname'); 
                    }
                    $other = html_writer::div(implode("\n", $list->tutors[$uid]), 'tutorname' );
                    $other = html_writer::div($label.$other, 'tutor');   
                }
            }
            
            if($content = $tutor.$other) {
                $examinees[$uid] = print_collapsible_region($content, 'tutors', 
                                                                'tutorlist_'.$list->examid.'_'.$uid, 
                                                                $examinee.'&nbsp;', 
                                                                'tutorlist_'.$list->examid, true, true); 
            } else {
                $examinees[$uid] = html_writer::div($examinee, 'collapsibleregion');
            }
            
            
            //$examinee. html_writer::div($tutor.$other, 'tutors');   ;
        }
        
        foreach($examinees as $uid => $user) {
            $class = ' examinee ';
            $class .= $list->users[$uid]->excluded ? ' dimmed ' : '';
            $examinees[$uid] = html_writer::div($user, $class);
        }
        
        
        return implode("\n", $examinees);;
    }
    
    
    public function format_exam_grades($grades, $cangrade) {
        $output = '';    

        print_object($grades);
        
        
        //$output .= '  aquí van las calificaciones ';
        return $output;
    }
    
    public function display_confirmation($userid, $confirms, $editurl, $requireconfirm = false, $defaultcornfirm = true, $canmanage = false) {
        global $USER;
        
        $output = '';    
        if($confirms == 0 && !$requireconfirm) {
            return '';
        }
        
        $button = '';
        if($USER->id == $userid || $canmanage) {
            $confirm = ($confirms == 0) ?  $defaultcornfirm : (end($confirms))->confirmed;
            $name = $confirm ? get_string('unconfirm', 'examboard') : 
                                get_string('confirm', 'examboard');
            $editurl->param('action', 'boardconfirm');
            $button = $this->single_button($editurl, $name, 'get',
                                                    array('class' => 'continuebutton'));
            $editurl->remove_params('action');
        }        
        
        if($confirms == 0) {
            // this means requireconfirm is true
            $confirm = $defaultcornfirm ? 'check-square-o'  : 'square-o';
            return html_writer::span('<i class="fa fa-'.$confirm.'"> </i>').$button;
        }
        
        // if we are here $confirm is a full record
        $output = array();
        foreach($confirms as $i => $confirm) {
            $confirmed = $confirm->confirmed ?  'check-square alert-success' : 'times-circle alert-error';
            $output[$i]  = html_writer::span('<i class="fa fa-'.$confirmed.'"> </i>');
            
            if($canmanage) {
                $timechanged = $confirm->confirmed ? $confirm->timeconfirmed : $confirm->timeunconfirmed;
                if($timechanged) {
                    $output[$i] .= ' '.userdate($timechanged);
                }
                if($confirm->discharge) {
                    $content = format_text($confirm->dischargetext, $confirm->dischargeformat);
                    $available = $confirm->available ?  'check-square alert-success' : 'times-circle alert-error';
                    $content .= html_writer::span(get_string('confirmavailable', 'examboard').
                                                    ' <i class="fa fa-'.$available.'"> </i>');
                    $output[$i] .= ' '.print_collapsible_region($content, ' ', 'examboard_confirm_'.$confirm->id,
                                                                get_string('discharge_'.$confirm->discharge, 'examboard'),
                                                                'examboard_confirm_list', false, true);  
                }
            }
        }
        
        return implode('<br />', $output).$button;
    }
    
    public function display_notifications($notifications) {
        global $USER;
    
        $output = '';    
        if($notifications) {
            $output .= $this->box_start('examnotices');
            $notices = array_map(array($this, 'format_notification'), $notifications);
            $output .= $this->box(implode('<br />', $notices), 'notices');
            $output .= $this->box_end();
        }

        return $output;
    }

    
    public function format_notification($notification) {
        global $CFG;
        $contextid = $this->page->context->id;        
        
        $fs = get_file_storage(); 
        $date = userdate($notification->timeissued);
        $files = '';
        //print_object("contextid, component, filearea, itemid, filepath, ");
        //print_object("contextid: $contextid, component: mod_examboard, filearea: notification, itemid: {$notification->id}, filepath /      ");
        if($files = $fs->get_directory_files($contextid, 'mod_examboard', 
                                                'notification', $notification->id, '/', true, true)) {
            //print_object($files);                                    
            foreach($files as $fid => $file) {
                $filename = $file->get_filename();
                $url = file_encode_url($CFG->wwwroot.'/pluginfile.php', 
                                        '/'.$contextid.'/mod_examboard/notification/'.$notification->id.'/'.$filename, 0);
                $strexamfile = get_string('downloadfile', 'examboard', $filename);                                        
                $icon = $this->pix_icon('f/pdf-32', $strexamfile, 'moodle', array('class'=>'icon', 'title'=>$filename));        
                $files[$fid] = html_writer::link($url, $icon);
            }
            
        }
        $files = '  '.implode(', ', $files);
        //print_object($files);
    
        
        return $date.' &nbsp; '.$files;
    }
    
    
/////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////






/////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Render the grading table.
     *
     * @param object $board record
     * @param object $editurl moodle url for actions
     * @param committee committee object with names and data
     * @return string
     */
    public function view_board_table($board, $editurl, $committee) {
        global $USER;
        
        $output = '';
        
        $table = new \html_table();
        $table->attributes['class'] = ' generaltable singleboardtable ';
        $table->summary = ' fsdf summary summary summary fsdf ' ;
        
        $table->data = array();

        $accessiblecell = new \html_table_cell();
        $accessiblecell->header = true;
        $accessiblecell->scope = 'col';
        
        $header = array('role'   => get_string('memberrole', 'examboard'),
                        'name'   => get_string('membername', 'examboard'),
                        'exam'   => get_string('session', 'examboard'),
                        'status' => get_string('boardstatus', 'examboard'),
                        'notify' => get_string('boardnotify', 'examboard'),);
        $countexams = count($committee->assignedexams);
        $countmembers = count($committee->members);
        if($countexams < 2) {
            unset($header['exam']);
        }
                        
        $lambda = function ($value) use ($accessiblecell) {
                        $accessiblecell->text = $value;     
                        return clone $accessiblecell;
                  };
        $table->head = array_map($lambda, $header);
        $table->align = array();
        $table->size = array();
        
        $deputy = clone $table;
                      
        $rolestr = array();
        $rolestr[0] = $committee->chair;
        $rolestr[1] = $committee->secretary;
        foreach(range(2, count($committee->members)) as $idx) {
            $rolestr[$idx] = $committee->vocal.' '.($idx-1);
        }

        $userurl = new moodle_url('/user/view.php', array('course' =>  $this->page->course->id));
        $lastmember = 0;
        foreach($committee->members as $uid => $member) {
                $columns = array();
                $columns['role'] = $rolestr[$member->sortorder];
                $userurl->param('id', $uid);
                $columns['name'] = $this->user_picture($member).' '.html_writer::link($userurl, fullname($member));
                
            foreach($committee->assignedexams as $examid => $exam) {
                if($countexams > 1) {
                    $columns['exam'] = $this->format_exam_name($exam);
                }
                $confirm = isset($committee->confirmations[$uid][$examid]) ? $committee->confirmations[$uid][$examid] : 0;
                $editurl->param('user', $uid);
                $editurl->param('exam', $examid);
                $columns['status'] = $this->display_confirmation($uid, $confirm, $editurl, $committee->requireconfirm, 
                                                                    $committee->defaultconfirm, $committee->canmanage);
                $columns['notify'] = '';
                if(($committee->canmanage || ($member->userid == $USER->id)) && isset($committee->notifications[$uid][$examid])) {
                    $columns['notify'] = $this->display_notifications($committee->notifications[$uid][$examid]);
                }
            
                $row = new \html_table_row($columns);
                $row->cells[0]->scope = 'row';
                if($member->deputy) {
                    $deputy->data[] = $row;
                } else {
                    $table->data[] = $row;
                }
                $columns['role'] = '';
                $columns['name'] = '';
            }

        }
        
        $output .= html_writer::table($table);

        $caption = html_writer::span(get_string('deputymembers', 'examboard'), 'label');
        $output .= print_collapsible_region(html_writer::table($deputy), 
                                            ' deputytable ', 'board_deputy_table_'.$committee->id, 
                                            $caption, 'board_deputy_table', true, true); 
        return $output;        
    }

    
    
    /**
     * Render the main viewing table.
     *
     * @param assign_grading_table $table
     * @return string
     */
    public function render_examinees_table(\mod_examboard\output\examinees_table $table) {
        global $USER; 
        $output = '';

        $examname = $table->examination->title.' '.$table->examination->idnumber;
        $output .= $this->heading(format_string($examname), 2);
        if($table->examination->name) {
            $output .= $this->heading(format_string($examname), 5);
        }
        $output .= $this->heading(get_string('sessionlabel', 'examboard', $table->examination->sessionname), 3);
        
        // Prepare table header.
        $columns = array('examinee' => $table->examinee);
        if($table->usetutors) {
            $columns['tutor'] = $table->tutor;
        }
        $columns += array('examinee' => $table->examinee,
                          'tutor'    => $table->tutor,
                          'sortorder'=> get_string('order', 'examboard'),
                          'userlabel'=> get_string('userlabel', 'examboard'),
                          'grade'    => get_string('grade'),
                          'action'   => get_string('action'),);
        if(!$table->usetutors) {
            unset($columns['tutor']);
        }
        if(!$table->grademax) {
            unset($columns['grade']);
        }
                          
        $table->define_columns(array_keys($columns));
        $table->define_headers(array_values($columns));
        $table->set_attribute('id', 'mod_examboard_view_board_table');
        $table->set_attribute('class', 'flexible admintable generaltable');
        $table->sortable(true, 'examinee, sortorder', SORT_ASC);
        $table->no_sorting('grade');
        $table->no_sorting('action');
        $table->collapsible(true);
        $table->is_downloadable(false);
        $numusers = $table->examination->count_examinees();
        
        $table->pagesize(10, $numusers);  
        $table->setup();
        
        $table->examination->load_examinees_with_tutors('', $table->get_sql_sort(), $table->get_page_start(), $table->get_page_size()); 
        $table->initialbars(false);

        if(isset($columns['tutor'])) {
            $table->examination->load_tutors();
        }
        if(isset($columns['grade'])) {
            $table->examination->load_grades();
        }
        
        $table->editurl->param('exam', $table->examination->id);
        $table->viewgradeurl = clone($table->baseurl);
        $table->viewgradeurl->params(array('item' => $table->examination->id,
                                            'view'=>'graded'));
        $gradingurl = clone($table->viewgradeurl);
        $gradingurl->param('view', 'grading'); 
        $userurl = new moodle_url('/user/view.php', array('course'=>$this->page->course->id));
        foreach($table->examination->examinees as $uid => $user) {
            $table->editurl->param('user', $uid);
            $row = array();
            $userurl->param('id', $uid);
            $row['examinee'] = $this->box($this->user_picture($user).' '.html_writer::link($userurl, fullname($user)), 'examinee'); 

            if(isset($columns['tutor'])) {
                $row['tutor'] = '';
                if(isset($table->examination->tutors[$uid])) {
                    $row['tutor'] = $this->display_user_tutor($table->examination->tutors[$uid], $userurl);
                }
            }
            $row['sortorder'] = $user->sortorder + 1;
            $row['userlabel'] = $user->userlabel;
            if(isset($columns['grade'])) {
                $row['grade'] = $this->display_user_gradeables($table, $uid);
                $row['grade'] .= $this->display_user_grades($table, $uid);
            }

            $row['action'] = $this->examinee_table_user_actions($table->editurl, $gradingurl, $user, $numusers, $table->canmanage, ($table->grademax && !$user->excluded));
            
            $class = $user->excluded  ? ' dimmed excluded ' : ''; 
        
            $table->add_data_keyed($row, $class);
        }

        $output .= $this->flexible_table($table);
        
        if($table->canmanage) {
            $table->editurl->remove_params('user');
            $output .= $this->box($this->view_examinee_table_buttons($table->editurl, ($numusers > 1)), 'generalbox pagebuttons');
        }
    

        return $output;           
    }
    
    /**
     * Render the tutors for a user.
     *
     * @param array $tutors including names, first is main tutor
     * @return string
     */
    public function display_user_tutor($tutors, $url) {
        $main = '';
        $cotutors = '';
        if($tutors && is_array($tutors)) {
            $main = array_shift($tutors);
            $uid = $main->userid;
            $url->param('id', $main->tutorid);
            $main = $this->box($this->user_picture($main).' '.html_writer::link($url,fullname($main)), 'tutor'); 
            foreach($tutors as $user) {
                $url->param('id', $user->tutorid);
                $cotutors .= $this->box($this->user_picture($user).' '.html_writer::link($url,fullname($user)), 'cotutor'); 
            }
            if($tutors) {
                $cotutors = print_collapsible_region($cotutors, ' ', 'examboard_cotutors_list_'.$uid, 
                                                            get_string('othertutors', 'examboard'), 
                                                            'examboard_cotutors_list', false, true);  
            }
        }
        return $main.$cotutors;
    }

    
    /**
     * Render the link to a gradeable item
     *
     * @param object $cminfo, course module infor of complementary data
     * @param int $userid, student id
     * @param string $word, word to use in link
     * @return string
     */
    public function gradeable_link($cminfo, $userid, $word) {
        global $DB;
        $link = '';
        $submitflag = true;
    
        $url = new moodle_url("/mod/{$cminfo->modname}/view.php", array('id'=>$cminfo->id));
    
        switch($cminfo->modname) {
            case 'assign'   :   $url->params(array('action' => 'grade', 'userid' => $userid));
                                $select = "assignment = :assignment AND userid = :userid AND latest = 1 AND status <> 'new' ";
                                $submitflag = $DB->record_exists_select('assign_submission', $select, array('assignment' => $cminfo->instance, 'userid'=>$userid));
                        break;

            case 'tracker'  :   $url->param('view','view'); 
                                $select = "trackerid = :trackerid AND reportedby = :userid AND status > 0 AND status <> 5 ";
                                $params = array('trackerid' => $cminfo->instance, 'userid'=>$userid);
                                if($submitflag = $DB->record_exists_select('tracker_issue', $select, $params)) {
                                    $issue = reset($DB->get_records_select('tracker_issue', $select, $params, 'datereported DESC', 0, 1));
                                    $url->param('issueid', $issue->id);
                                }
                        break;        

            case 'data'  :
                        break;        
        
        }
    
        if($submitflag) {
            $link = $this->action_link($url, $word);
        }
        
        return $link;
    }
    
    
    /**
     * Render the grades for a user in the examinee table
     *
     * @param array $tutors including names, first is main tutor
     * @return string
     */
    public function display_user_gradeables($table, $userid) {
        
        $output = '';

        if($table->gradeable && 
            $link = $this->gradeable_link($table->gradeable, $userid, get_string('gradeable', 'examboard'))) {
            $output .= $link.'<br>';
        }
        
        if($table->proposal && 
            $link = $this->gradeable_link($table->proposal, $userid, get_string('proposal', 'examboard'))) {
            $output .= $link.'<br>';
        }
        
        if($table->defense &&
            $link = $this->gradeable_link($table->defense, $userid, get_string('defense', 'examboard'))) {
            $output .= $link.'<br>';
        }
        
        return $output;
    }
    
    /**
     * Render the grades for a user in the examinee table
     *
     * @param array $tutors including names, first is main tutor
     * @return string
     */
    public function display_user_grades($table, $userid) {
        global $USER;
        
        $output = '';
        
        if(!$table->grademax || !isset($table->examination->grades[$userid]) ) {
            //there are no grades, activity not graded
            return $output;
        }
    
        $grades = $table->examination->grades[$userid];
        
        $finalgrade = '';
        if($grades) {
            $finalgrade = examboard_calculate_grades($table->grademode, $table->mingraders, $grades);
            $finalgrade = $this->display_grade($finalgrade, $table->grademax, $table->gradeitem, false);

            $bgrades = '';
            $table->viewgradeurl->param('user', $userid);
            $attributes = array('title' => get_string('viewgradingdetails', 'examboard'));
            foreach($grades as $gid => $grade) {
                if($grade->sortorder == 0) {
                    $role = $table->chair;
                } elseif($grade->sortorder == 1) {
                    $role = $table->secretary;
                } else {
                    $role = $table->vocal.' '.($grade->sortorder -1);
                }
                $grade = format_float($grade->grade, $table->gradeitem->get_decimals());
                $text = $role.': '. $grade;
                if($table->advancedgrading && ($userid == $USER->id || $table->canmanage)) {
                    $table->viewgradeurl->param('gid', $gid);
                    $text = html_writer::link($table->viewgradeurl, $text, $attributes);
                }
                
                $bgrades .= $this->box($text, 'membergrade');
            }
            
            //$output .= $this->box($finalgrade, 'finalgrade');
            if($bgrades) {
                $output .= print_collapsible_region($bgrades, '', 'examboard_grades_'.$userid, 
                                                    $finalgrade, 'examboard_grades', false, true); 
            }
        }
        return $output;
    }
    
    
    /**
     * Return a grade in user-friendly form, whether it's a scale or not.
     *
     * @param float $grade int|null
     * @param int $grademax the grade settih in the module instance. 
     *             Indicates if graded (!=0) and scales used (negative) 
     * @param stdclass $gradeitem record from grade_item table PLUS scale record as scale
     * @param bool $downloading if the data are been downloaded to a file (vs displayed on screen)
     * @return string User-friendly representation of grade
     */
    public function display_grade($grade, $grademax, $gradeitem, $downloading = false) {  
        $o = '';
        
        if ($grade == -2) {
            return get_string('partialgrading', 'examboard');
        }
        
        
        if ($grademax >= 0) {
            if ($grade == -1 || $grade === null || $grademax == 0) {
                if ($downloading) {
                    return '';
                }
                $o .= '-';
            } else {
                if ($downloading) {
                    return format_float($grade, $gradeitem->get_decimals());
                }
                $o .= grade_format_gradevalue($grade, $gradeitem);
                if ($gradeitem->get_displaytype() == GRADE_DISPLAY_TYPE_REAL) {
                    // If displaying the raw grade, also display the total value.
                    $o .= '&nbsp;/&nbsp;' . format_float($grademax, $gradeitem->get_decimals());
                }
            }
            return $o;
        } else {
            if(!$gradeitem->scale) {
                $o .= '-';
            } else {
                $scaleid = (int)$grade;
                if (isset($scale[$scaleid])) {
                    $o .= $scale[$scaleid];
                    return $o;
                }
                $o .= '-';
            }
        }
        if ($downloading && ($o == '-')) {
            return '';
        }
        return $o;
    }
    

    /**
     * Render the action icons for examinee table
     *
     * @param array $tutors including names, first is main tutor
     * @return string
     */
    public function examinee_table_user_actions($url, $gradeurl, $user, $maxusers, $canmanage, $cangrade) {
        global $PAGE;
        $action = '';       
        $attributes = array(); //array('class' => 'icon');
        if($canmanage) {
        // edit 
            $url->param('action', 'updateuser');
            $icon = new pix_icon('i/settings', get_string('updateuser', 'examboard'), 'core', $attributes);
            $action .=  '&nbsp; '.$this->output->action_icon($url, $icon);
        // delete
            $deleteaction = new \confirm_action(get_string('userdeleteconfirm', 'examboard'));
            // Confirmation JS.
            $PAGE->requires->strings_for_js(array('deleteallconfirm', 'userdeleteconfirm'), 'examboard');

            $url->param('action', 'deleteuser');
            $icon = new pix_icon('i/delete', get_string('deleteuser', 'examboard'), 'core', $attributes);
            $action .=  '&nbsp; '.$this->output->action_icon($url, $icon, $deleteaction);
            
        // move buttons
            if($user->sortorder) {
                $url->param('action', 'userup');
                $icon = new pix_icon('t/up', get_string('up'), 'core', $attributes);
                $action .=  '&nbsp; '.$this->output->action_icon($url, $icon);
            }

            if($user->sortorder < ($maxusers - 1)) {
                $url->param('action', 'userdown');
                $icon = new pix_icon('t/down', get_string('down'), 'core', $attributes);
                $action .=  '&nbsp; '.$this->output->action_icon($url, $icon);
            }
        }
        
        if($cangrade) {
            $action .= '<br />';        
            $gradeurl->param('user', $user->id);
            $action .= html_writer::link($gradeurl, get_string('grade', 'examboard'), array('class' =>'btn btn-primary'));
        }
        return $action;
    }

    
    /**
     * Render the action buttons for a manager in the examinee table
     *
     * @param object $url the url forn editing actions
     * @param bool $hasusers if there are users in the form
     * @return string
     */
    public function view_examinee_table_buttons($url, $hasusers) {
        $output = '';
    
        $returnurl = new moodle_url('/mod/examboard/view.php', array('id'=>$url->get_param('id')));
        $output .= $this->single_button($returnurl, get_string('returntoexams', 'examboard'), 'get',
                                                array('class' => 'continuebutton'));
        $url->param('action', 'updateuser');
        $output .= $this->single_button($url, get_string('adduser', 'examboard'), 'get',
                                                    array('class' => 'continuebutton'));

        if($hasusers) {
            $url->param('action', 'deleteall');
            $deleteaction = new \confirm_action(get_string('deleteallconfirm', 'examboard'));
            $output .= $this->single_button($url, get_string('deleteall', 'examboard'), 'get',
                                                    array('class' => 'continuebutton', 
                                                            'actions' =>array($deleteaction)));            
            //managers can reorder here
            $options = array(EXAMBOARD_ORDER_KEEP   => get_string('orderkeepchosen', 'examboard'),
                            EXAMBOARD_ORDER_RANDOM => get_string('orderrandomize', 'examboard'),
                            EXAMBOARD_ORDER_ALPHA  => get_string('orderalphabetic', 'examboard'),
                            );
            $url->param('action', 'reorder');
            $output .= $this->show_select_button_form($url, $options, 'reorder', 'reorder');

        }
        return $output;
    }
    

    /**
     * Render the main viewing table.
     *
     * @param assign_grading_table $table
     * @return string
     */
    public function render_exams_table(\mod_examboard\output\exams_table $viewer) {
        global $USER; 
        
        $output = '';
       
        // Prepare table header.
        $table = array('idnumber'  => get_string('codename', 'examboard'),
                            'board'     => get_string('board', 'examboard'),
                            'sessionname'     => get_string('session', 'examboard'),
                            'examdate'     => get_string('examplacedate', 'examboard'),
                            'examinee'  => get_string('examinees', 'examboard'),
                            );
        if($viewer->publishgrade || $viewer->canmanage) {
            $table['grade'] = get_string('grade');
        }
        if($viewer->canmanage) {
            $table['action'] = get_string('actions');
        }
        $viewer->define_columns(array_keys($table));
        $viewer->define_headers(array_values($table));
        $viewer->set_attribute('id', 'mod_examboard_view_exams_table');
        $viewer->set_attribute('class', 'flexible admintable generaltable');
        
        $viewer->sortable(true, 'idnumber, examdate', SORT_ASC);
        $viewer->no_sorting('board');
        $viewer->no_sorting('examinee');
        $viewer->no_sorting('grade');
        $viewer->no_sorting('action');
        $viewer->collapsible(true);

        $examboard = new stdclass();
        $examboard->id = $viewer->examboardid;
        $examboard->usetutors = true;
        $userid = $viewer->baseurl->get_param('fuser');   
        $viewall = ($viewer->canmanage || $viewer->canviewall);
        $count = examboard_count_user_exams($examboard, $viewall, $userid, $viewer->groupid);
        $viewer->pagesize(30, $count);  

        $viewer->setup();
        
        $viewer->examinations = examboard_get_user_exams($examboard, $viewall, $userid, $viewer->groupid, 
                                                            $viewer->get_sql_sort(), $viewer->get_page_start(), $viewer->get_page_size(), true); 
        $viewer->initialbars(false);
        
        //TODO //TODO //TODO //TODO //TODO //TODO //TODO 
        // add list unassigned boards(if can manage)
        
        $output .= $this->box($this->view_sort_filter($viewer->cmid, $viewer->baseurl, $viewall), 'generalbox tablefilter');
                                                            
        foreach($viewer->examinations as $examid => $exam) {
            $row = array();
            
            $tribunal = $this->render(board_name::from_record($exam));
            $committee = '';
            $ismember = false;
            if($viewer->cangrade || ($viewer->publishboard) || $viewer->canmanage) {
                //we get all mebers, including deputy for capbility checking 
                $members = $exam->load_board_members();
                if($viewer->cangrade && isset($members[$USER->id]) && $exam->active) {
                    $ismember = true;
                }
                // remove deputy members
                foreach($members as $key => $member) {
                    if($member->deputy) {
                        unset($members[$key]);
                    }
                }
                //Grader only if full member, not deputy
                $isgrader = ($viewer->cangrade && isset($members[$USER->id]) && $exam->active );
                
                $board = new committee($exam->boardid, $exam->boardactive, $members, $viewer->requireconfirm, $viewer->defaultconfirm,  
                                                        $viewer->chair, $viewer->secretary, $viewer->vocal); 
                $board->is_downloading = $viewer->is_downloading();
                if($isgrader) {
                    $viewer->hasconfirms[$exam->id] = $this->format_exam_name($exam);
                }
                $committee = $this->render($board);
            } else {
                if($viewer->publishboard && $viewer->publishboarddate) { 
                    $committee = get_string('tobepublishedafter', 'examboard', userdate($viewer->publishboarddate));
                }
            }
            
            if($ismember || $viewer->canmanage) {
                $url = clone $viewer->baseurl;
                $url->param('view', 'board');
                $url->param('item', $exam->boardid);
                $tribunal = html_writer::link($url, $tribunal); 
            }
            $row[] =  $this->box($tribunal, 'boardname'); 
            $row[] =  $committee;
            
            
        
            $row[] = $this->box($exam->sessionname, 'sessionname');
            $session = exam_session::from_record($exam);
            $row[] = $this->render($session);

            $examinee_list = new examinee_list($viewer->examinee, $viewer->tutor, $viewer->usetutors, $examid);
            //$ownuser = ($viewall || $isgrader) ? '' : " userid = '{$USER->id}' ";            
            
            
            
            $examinee_list->is_downloading = $viewer->is_downloading();
            $examinee_list->users = $exam->load_examinees();
            if($viewer->usetutors) {
                $examinee_list->tutors = $exam->load_tutors();
            }
            // eliminate users that shouldn't be visible, 
            if(!$viewall && !$ismember) {
                foreach($examinee_list->users as $uid => $user) {
                    // either this user is a student or a tutor
                    if( $uid != $USER->id && 
                        !array_key_exists($USER->id, $examinee_list->tutors[$uid])) {
                        //if  not student or tutor, delete
                        unset($examinee_list->users[$uid]);
                        unset($examinee_list->tutors[$uid]);
                    }
                }
            }
            
            $row[] = $this->render($examinee_list);
            
            if($viewer->cansubmit && isset($examinee_list->users[$USER->id]) && ($exam->active )) {
                    $viewer->hassubmits[$exam->id] = $exam->idnumber;
            }
            
            if(($viewer->publishgrade) || $viewer->canmanage || $isgrader) {
                $grades = '';
                $userstable = examinees_table::get_from_url($viewer->baseurl);
                $userstable->examination = $exam;
                $userstable->canmanage = $viewer->canmanage;
                $userstable->viewgradeurl = clone($viewer->baseurl);
                $userstable->viewgradeurl->params(array('item' => $userstable->examination->id,
                                                        'view'=>'graded'));
                $exam->load_grades();
                foreach($examinee_list->users as $uid => $user) {
                    $grades .= html_writer::div($this->display_user_grades($userstable, $uid), 'usergrades');
                }

                // add grade button if usr can grade those students
                $gradeicon = '';
                if(($isgrader && $examinee_list->users) && !$viewer->is_downloading()) {
                    $url = clone $viewer->baseurl;
                    $url->param('view', 'exam');
                    $url->param('item', $exam->id);
//                    $icon = new pix_icon('i/grades', get_string('gradeusers', 'examboard'));
                    $gradeicon = html_writer::link($url, get_string('grade', 'examboard'), array('class' =>'btn btn-primary'));
                }
                $row[] =  $grades.$gradeicon;
            }

            if($viewer->canmanage) {
                $action = $this->exam_row_actions(clone $viewer->editurl, clone $viewer->baseurl, $exam);
                $row[] = $action;
            }
            
            $class = $exam->active ? '' : ' dimmed '; 
            
            $viewer->add_data($row, $class);
        }

        $output .= $this->flexible_table($viewer);

        $output .= '<div class="clearer clearfix"></div>';

        return $output;
    }


    /**
     * Helper method dealing with the fact we can not just fetch the output of flexible_table
     *
     * @param flexible_table $table The table to render
     * @return string HTML
     */
    protected function exam_row_actions($url, $viewurl, $exam) {
        global $PAGE;
        
        $action = '';       
        $url->param('exam', $exam->id);
        $attributes = array(); //array('class' => 'icon');
        // edit 
            $url->param('action', 'updateexam');
            $icon = new pix_icon('i/settings', get_string('updateexam', 'examboard'), 'core', $attributes);
            $action .=  '&nbsp; '.$this->output->action_icon($url, $icon);
            
        // visible 
            $name = $exam->active ? 'hide' : 'show';
            $url->param('action', 'exam'.$name);
            $icon = new pix_icon('t/'.$name, get_string($name), 'core', $attributes);
            $action .=  '&nbsp; '.$this->output->action_icon($url, $icon);
            
            
        // delete
            $deleteaction = new \confirm_action(get_string('examdeleteconfirm', 'examboard'));
            // Confirmation JS.
            $PAGE->requires->strings_for_js(array('examdeleteconfirm'), 'examboard');

            $url->param('action', 'deleteexam');
            $icon = new pix_icon('i/delete', get_string('deleteexam', 'examboard'), 'core', $attributes);
            $action .=  '&nbsp; '.$this->output->action_icon($url, $icon, $deleteaction);
            
            $action .= '<br />';
        // add board
            $viewurl->param('view', 'board');
            $viewurl->param('item', $exam->boardid);
            $icon = new pix_icon('i/enrolusers', get_string('viewboard', 'examboard'), 'core', $attributes);
            $action .=  '&nbsp; '.$this->output->action_icon($viewurl, $icon);
        // add examinees
            $viewurl->param('view', 'exam');
            $viewurl->param('item', $exam->id);
            $icon = new pix_icon('i/cohort', get_string('viewusers', 'examboard'), 'core', $attributes);
            $action .=  '&nbsp; '.$this->output->action_icon($viewurl, $icon);
        return $action;
    }

    /**
     * Helper method dealing with the fact we can not just fetch the output of flexible_table
     *
     * @param flexible_table $table The table to render
     * @return string HTML
     */
    protected function flexible_table(\flexible_table $table) {

        $o = '';
        ob_start();
        $table->finish_output();
        $o = ob_get_contents();
                
        ob_end_clean();
        ob_end_flush();



        return $o;
    }

        /**
     * Helper method dealing with the fact we can not just fetch the output of moodleforms
     *
     * @param moodleform $mform
     * @return string HTML
     */
    protected function moodleform($mform) {

        $o = '';
        ob_start();
        $mform->display();
        $o = ob_get_contents();
        ob_end_clean();

        return $o;
    }
    

    
}
