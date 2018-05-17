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
 * This file contains a renderer for the assignment feedback wtpeer class
 *
 * @package   assignfeedback_wtpeer
 * @copyright 2016 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

include_once($CFG->dirroot.'/mod/assign/renderer.php');
include_once($CFG->dirroot.'/mod/assign/feedback/wtpeer/renderable.php');

/**
 * A custom renderer class that extends the plugin_renderer_base and is used by the assign module.
 *
 * @package   assignfeedback_wtpeer
 * @copyright 2016 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_wtpeer_renderer extends mod_assign_renderer {


    /**
     * Render the assessmentable table.
     *
     * @param assignfeedback_wtpeer_assessment_table $table
     * @return string
     */
    public function render_assignfeedback_wtpeer_assessment_table(assignfeedback_wtpeer_assessment_table $table) {
        $o = '';
        $o .= $this->output->box_start('boxaligncenter gradingtable assessmenttable');

        $this->page->requires->js_init_call('M.mod_assign.init_grading_table', array());
        $this->page->requires->string_for_js('nousersselected', 'assign');
        $this->page->requires->string_for_js('batchoperationconfirmcalculateselected', 'assignfeedback_wtpeer');
        $this->page->requires->string_for_js('batchoperationconfirmdownloadselected', 'assignfeedback_wtpeer');
        $this->page->requires->string_for_js('editaction', 'assign');
        $o .= $this->flexible_table($table, $table->get_rows_per_page(), true);
        $o .= $this->output->box_end();

        return $o;
    }

    
    /**
     * Render the header.
     *
     * @param assign_header $header
     * @return string
     */
    public function render_assign_header(assign_header $header) {
        $o = '';

        if ($header->subpage) {
            $this->page->navbar->add(get_string('pluginname' , 'assignfeedback_wtpeer'));
            $this->page->navbar->add($header->subpage);
        }

        $this->page->set_title(get_string('pluginname', 'assign'));
        $this->page->set_heading($this->page->course->fullname);

        $o .= $this->output->header();
        if ($header->preface) {
            $o .= $this->output->heading($header->preface);
        }

        if ($header->showintro) {
            $o .= $this->output->box_start('generalbox boxaligncenter', 'intro');
            $o .= format_module_intro('assign', $header->assign, $header->coursemoduleid);
            $o .= $header->postfix;
            $o .= $this->output->box_end();
        }

        return $o;
    }
    
    /**
     * Render the results summary.
     *
     * @param assignfeedback_wtpeer_summary $summary
     * @return string
     */
    public function render_assignfeedback_wtpeer_summary(assignfeedback_wtpeer_summary $summary) {
        $o = '';
        
        if($summary->canviewgrade) {
            $o .= $summary->assessment->grades['final'];
        } else {
            if($summary->whenviewgrade) {
                $o .= get_string('viewgradedate', 'assignfeedback_wtpeer', userdate($summary->whenviewgrade));
            } else {
                $o .= get_string('viewgradeno', 'assignfeedback_wtpeer');
            }
        }
    
        if($summary->canviewassessments) {
            $list = array();
            $sum = 0;
            $gradeditem = '';
            foreach($summary->items as $item) {
                $a = new stdclass;
                $a->grade   = isset($summary->assessment->grades[$item]) ? $summary->assessment->grades[$item] : '-';
                $a->grades  = $summary->assessment->countgrades[$item];
                if($a->grades > 0) {
                    // only show grade analysis link if there are some real grades
                    $gradeditem = $item; 
                }
                $a->allocs  = $summary->assessment->allocations[$item];
                $a->item    = get_string('row'.$item, 'assignfeedback_wtpeer');
                $list[]     = get_string('showitemresult', 'assignfeedback_wtpeer', $a);     
            }
            $o .= html_writer::alist($list, array('class'=>'assessresults'));
            
            if($summary->showexplain && $gradeditem) {
                $o .= $this->show_grade_details($summary->cmid, $summary->assessment->submissionid, $gradeditem);
            }
        } else {
            if($o) {
                $o .= '<br />';
            }
            if($summary->whenviewassessments) {
                $o .= get_string('viewassessmentsdate', 'assignfeedback_wtpeer', userdate($summary->whenviewassessments));
            } else {
                $o .= get_string('viewassessmentsno', 'assignfeedback_wtpeer');
            }
        }
        
        if($summary->hasungradedallocs) {
            if($o) {
                $o .= '<br />';
            }
            $o .= $this->show_ungraded_allocs_alert($summary->cmid, $summary->hasungradedallocs, $summary->assessment->userid);
        }
        return $o;
    }

    /**
     * Render the grading allocation info.
     *
     * @param assignfeedback_wtpeer_summary $summary
     * @return string
     */
    public function render_assignfeedback_wtpeer_allocationinfo(assignfeedback_wtpeer_allocationinfo $allocinfo) {

        $o = '';
        
        if($allocinfo->title) {
            $o .= $this->heading(get_string($allocinfo->title, 'assignfeedback_wtpeer'), 5);
        }
        
        $table = new html_table();
        $table->attributes = array('class'=>'wtpeer_userallocations ', 'cellpadding'=>5, 'cellspacing'=>0 );
        $table->colclasses = array();
        $head = array(get_string('assessment', 'assignfeedback_wtpeer'),
                                get_string('allocated', 'assignfeedback_wtpeer'),
                                get_string('graded', 'assignfeedback_wtpeer'));
        $head[] = ($allocinfo->dates) ? get_string('gradingdate', 'assignfeedback_wtpeer') : ' ';
        $row = new html_table_row($head);
        $table->data[] = $row;

        $icon = '';
        if($allocinfo->title == 'userallocations') {
            $url = new moodle_url('/mod/assign/view.php', array('id'=>$allocinfo->cmid,
                                                    'plugin'=>'wtpeer',
                                                    'pluginsubtype'=>'assignfeedback',
                                                    'action'=>'viewpluginpage',
                                                    'pluginaction'=>'reviewtable'));
            $icon = $this->pix_icon('t/preview',  get_string('gradeanalysis', 'core_grades'),
                                                        'moodle', array('class'=>'iconsmall'));
            $icon = html_writer::link($url, $icon);   
        }
                                                        
        
        $now = time();
        foreach($allocinfo->items as $item) {
            $cell1 = get_string('row'.$item, 'assignfeedback_wtpeer');
            $cell2 = $allocinfo->allocations[$item];
            $cell3 = $allocinfo->grades[$item];
            if($allocinfo->grades[$item]) {
                $cell3 .= $icon; 
            }
           
            $cell4 = '' ; 
            if($allocinfo->dates && $allocinfo->dates[$item]) {
                if($allocinfo->dates[$item]['end'] && ($allocinfo->dates[$item]['end'] < $now)) {
                    $a = userdate($allocinfo->dates[$item]['end']);
                    $cell4 = get_string('gradingclosed', 'assignfeedback_wtpeer', $a);
                } elseif($allocinfo->dates[$item]['start'] && ($allocinfo->dates[$item]['start'] < $now) && $allocinfo->dates[$item]['end']) {
                    $a = userdate($allocinfo->dates[$item]['end']);
                    $cell4 = get_string('gradingupto', 'assignfeedback_wtpeer', $a);
                } elseif($allocinfo->dates[$item]['start']) {
                    $a = userdate($allocinfo->dates[$item]['start']);
                    $cell4 = get_string('gradingstarton', 'assignfeedback_wtpeer', $a);
                }
                $cell4 = new html_table_cell($cell4);
                $cell4->attributes = array('class'=>'gradingdate');
            }
            $row = new html_table_row(array($cell1, $cell2, $cell3, $cell4));
            $table->data[] = $row; 
        }
        
        if($table->data) {
            $o .= html_writer::table($table);
        }

        if($allocinfo->peeraccessmode) {
            $o .= get_string('gradingneedsubmission'. 'assignfeedback_wtpeer');
        } 

        if($allocinfo->hasungradedallocs) {
            $o .= $this->show_ungraded_allocs_alert($allocinfo->cmid, $allocinfo->hasungradedallocs);
        }
        return $o;
    }
    
    /**
     * Render the grading allocation info.
     *
     * @param assignfeedback_wtpeer_summary $summary
     * @return string
     */
    public function show_ungraded_allocs_alert($cmid, $count, $userid = 0) {
        $o = '';
        $url = new moodle_url('/mod/assign/view.php', array('id'=>$cmid,
                                                'plugin'=>'wtpeer',
                                                'pluginsubtype'=>'assignfeedback',
                                                'action'=>'viewpluginpage',
                                                'pluginaction'=>'reviewtable'));
        if($userid) {
            $url->set_anchor('selectuser_'.$userid);                               
        }
        if($count) {
            $content = get_string('alertungradedallocs', 'assignfeedback_wtpeer', $count);
            $o .= html_writer::link($url, $content, array('class'=>' alert-info '));
        }
        return $o;
    }

    /**
     * Render the grading allocation info.
     *
     * @param assignfeedback_wtpeer_summary $summary
     * @return string
     */
    public function show_unconfigured_alert($cmid, $cangrade = false) {
        $o = '';
        $url = new moodle_url('/mod/assign/view.php', array('id'=>$cmid,
                                                'plugin'=>'wtpeer',
                                                'pluginsubtype'=>'assignfeedback',
                                                'action'=>'viewpluginpage',
                                                'pluginaction'=>'manageconfig'));
        $content = get_string('needconfiguration', 'assignfeedback_wtpeer');
        if($cangrade) {
            $o .= $this->heading(html_writer::link($url, $content), 4, ' alert-error alert-block');
        } else {
            $content .= '<br />'.get_string('mailteachers');
            $o .= html_writer::div($content, ' alert-error ');
        }
        
        return $o;
    }
    
    /**
     * Render the grading allocation info.
     *
     * @param assignfeedback_wtpeer_summary $summary
     * @return string
     */
    public function show_grade_details($cmid, $submission, $item = '') {
        $o = '';
        $url = new moodle_url('/mod/assign/view.php', array('id'=>$cmid,
                                                'plugin'=>'wtpeer',
                                                'pluginsubtype'=>'assignfeedback',
                                                'action'=>'viewpluginpage',
                                                'pluginaction'=>'showassess',
                                                's'=>$submission,
                                                'type'=>$item));
        $icon = $this->pix_icon('t/viewdetails',  get_string('gradeanalysis', 'core_grades'),
                                    'moodle', array('class'=>'iconsmall'));
        $link = html_writer::link($url, $icon.get_string('gradeanalysis', 'assignfeedback_wtpeer'));                                        
        $o .= html_writer::div($link, ' text-info ');                                        
        return $o;
    }
    
    /**
     * Render the grading allocation info.
     *
     * @param assignfeedback_wtpeer_summary $summary
     * @return string
     */
    public function download_assess_link($cmid, $submission, $item) {
        $o = '';
        $url = new moodle_url('/mod/assign/view.php', array('id'=>$cmid,
                                                'plugin'=>'wtpeer',
                                                'pluginsubtype'=>'assignfeedback',
                                                'action'=>'viewpluginpage',
                                                'pluginaction'=>'downloadassess',
                                                's'=>$submission,
                                                'type'=>$item));
        $icon = $this->pix_icon('t/download',  get_string('download'),
                                    'moodle', array('class'=>'icon'));
        $link = html_writer::link($url, $icon.get_string('downloadassess', 'assignfeedback_wtpeer'));                                        
        $o .= html_writer::div($link, ' showassessment download  ');                                        
        return $o;
    }
    
    
    
    /**
     * Render the grading allocation info.
     *
     * @param assignfeedback_wtpeer_summary $summary
     * @return string
     */
    public function render_assignfeedback_wtpeer_item_assessments(assignfeedback_wtpeer_item_assessments $gradeinfo) {

        $o = '';
        
        $columns = array('marker' => get_string('marker', 'assignfeedback_wtpeer'), 'grade' => get_string('grade'));
        if($gradeinfo->showlong && $gradeinfo->showexplain != 2) {
            $columns['time'] = get_string('gradedon', 'assign');
        }
        if($gradeinfo->showexplain == 1) {
            $columns['link'] = get_string('assessexplainlink', 'assignfeedback_wtpeer');
        } elseif($gradeinfo->showexplain == 2) {
            $columns['explain'] = get_string('assessmentexplain', 'assignfeedback_wtpeer');
        }
        $table = new html_table();
        $table->attributes = array('class'=>'wtpeer showassessment', 'cellpadding'=>5, 'cellspacing'=>0 );
        $table->colclasses = array();
        $table->head = $columns;
    
        foreach($gradeinfo->grades as $grade) {
            $row = array();
            $item = $grade->gradertype;
            if(isset($columns['marker'])) {
                if($gradeinfo->canviewmarkers && isset($grade->fullname) && $grade->fullname) {
                    $row['marker'] = $grade->fullname;
                } else {
                    $row['marker'] = get_string('marker', 'assignfeedback_wtpeer');
                }
            }
            if(isset($columns['grade'])) {
                $row['grade'] = $grade->grade;
            }
            if(isset($columns['time'])) {
                $row['time'] = userdate($grade->timemodified);
            }
            if(isset($columns['link'])) {
                // can grade this item, show the grade & icon
                if($grade->grade && $grade->grade != '-') {
                    $params = array('pluginaction'=>'showexplain',
                                    's'=>$grade->submission,
                                    'm'=>$grade->grader,
                                    'type'=>$item);
                    $url = $gradeinfo->actionurl;
                    $url->params($params);
                    $icon = $this->pix_icon('icon', get_string('assessexplainlink', 'assignfeedback_wtpeer'),
                                                    $gradeinfo->gradingmethod, array('class'=>'iconsmall'));
                    $row['link'] = $this->output->action_link($url, $icon);
                } else { 
                    $row['link'] = '';
                }
            }
            if(isset($columns['explain'])) {
                $explain = $gradeinfo->plugin->get_grading_feedback_status_renderable($grade->userid, $grade->submission, $grade->grader, $grade->gradertype);
                if ($explain) {
                    $row['explain'] = print_collapsible_region($this->render($explain), 'assessmentexplain', 'showhideexplain_'.$item.'_'.$grade->id, 
                                                        get_string('toggleexplain', 'assignfeedback_wtpeer'),'asessmentexplain', true, true);
                }
            }
            if($gradeinfo->showlong) {
                $table->data[] = new html_table_row($row);
            } else {
                // we want all in a single string
                $a = new stdclass;
                $a->marker = $row['marker'];
                $a->grade = $row['grade'];
                $table->data[] = get_string('allocatedmarkersgrades', 'assignfeedback_wtpeer', $a).$row['link'];
            }
        }
    
        if($gradeinfo->showlong) {
            $o .= html_writer::table($table);
        } else {
            $o .= html_writer::alist($table->data, array('class'=>' markernames '));
        }    
    
        return $o;
    }


    /**
     * Builds and returns HTML needed to render the sort by drop down for lists
     *
     * @param array $options
     * @param string $sort
     * @param string $direction
     * @return string $html
     * @throws moodle_exception
     */
    public function list_sortby($options, $url, $sort, $direction) {

        $html = '';
        $nonjsoptions = array();

        if (!in_array($sort, array_keys($options))) {
            throw new moodle_exception("Not a sort option");
        }

        $pageurl = clone($url);

        $html .= html_writer::start_div('clearfix'); //
        $html .= html_writer::start_div('dropdown-group pull-right'); //
        $html .= html_writer::start_div('js-control btn-group pull-right');

        $html .= html_writer::start_tag('button', array('data-toggle' => 'dropdown',
                                                        'class' =>'btn btn-small dropdown-toggle'));

        $option = get_string('sort'.$sort, 'assignfeedback_wtpeer');
        if(isset($options[$sort]['type'])) { // ecastro ULPGC
            $faclass = "fa fa-sort-{$options[$sort]['type']}-{$direction}";
            $faicon = html_writer::tag('i', '', array('class' => $faclass));
            $option .= ' '.$faicon;
        }

        $html .= get_string('sortedby', 'assignfeedback_wtpeer', $option);
                $html .= ' ';
        $html .= html_writer::tag('tag', null, array('class' => 'caret'));
        $html .= html_writer::end_tag('button');
        $html .= html_writer::start_tag('ul', array('class' => 'dropdown-menu'));
        foreach ($options as $option => $settings) {
            $string = get_string('sort'.$option, 'assignfeedback_wtpeer');
            $nonjsoptions[$option] = $string;
            if ($settings['directional'] == false) {
                $pageurl->param('sort', $option);
                $html .= html_writer::start_tag('li');
                $html .= html_writer::link($pageurl, $string);
                $html .= html_writer::end_tag('li');
                continue;
            }
            if ($option == $sort) {
                $sortdirection = ($direction == 'desc') ? 'asc' : 'desc';
            } else {
                $sortdirection = \core_text::strtolower($settings['default']);
            }
            $pageurl->param('sort', $option);
            $pageurl->param('dir', $sortdirection);
            // font awesome icon
            $faclass = "fa fa-sort-{$settings['type']}-{$sortdirection} pull-right";
            $faicon = html_writer::tag('i', '', array('class' => $faclass));
            $html .= html_writer::start_tag('li');
            $html .= html_writer::link($pageurl, $faicon . $string);
            $html .= html_writer::end_tag('li');
        }
        $html .= html_writer::end_tag('ul');
        $html .= html_writer::end_div(); // end of js-control

        // Important: non javascript control must be after javascript control else layout borked in chrome.
        $select = new single_select($pageurl, 'sort', $nonjsoptions, $sort, null, 'orderbyform');
        $select->method = 'post';
        $nonjscontrol = $this->render($select);
        $html .= html_writer::div($nonjscontrol, 'nonjs-control');

        $html .= html_writer::end_div();
        $html .= html_writer::end_div();// end of container
        return $html;

    }

    /**
     * Builds and returns HTML needed to render the sort by drop down for lists
     *
     * @param stdClass $submission
     * @param assign $assignment
     * @return string $html
     */
    public function show_submission_status($assignment, $submission, $viewfullnames = null) {
        $o = '';
        $userid = $submission->userid;
        $attemptnumber = $submission->attemptnumber;
        $instance = $assignment->get_instance();
        if(is_null($viewfullnames)) {
            $viewfullnames = has_capability('moodle/site:viewfullnames', $assignment->get_course_context());
        }
    
        $submissiongroup = null;
        $teamsubmission = null;
        $notsubmitted = array();
        if ($instance->teamsubmission) {
            $teamsubmission = $assignment->get_group_submission($userid, 0, false, $attemptnumber);
            $submissiongroup = $assignment->get_submission_group($userid);
            $groupid = 0;
            if ($submissiongroup) {
                $groupid = $submissiongroup->id;
            }
            $notsubmitted = $assignment->get_submission_group_members_who_have_not_submitted($groupid, false);

        }
        $flags = $assignment->get_user_flags($userid, false);
        if (1 || $assignment->can_view_submission($userid)) {
            $gradelocked = ($flags && $flags->locked) || $assignment->grading_disabled($userid);
            $extensionduedate = null;
            if ($flags) {
                $extensionduedate = $flags->extensionduedate;
            }
            $showedit = $assignment->submissions_open($userid) && ($assignment->is_any_submission_plugin_enabled());
            $usergroups = $assignment->get_all_groups($userid);
            $submissionplugins = $assignment->get_submission_plugins();
            foreach($submissionplugins as $idx => $plugin) {
                if($plugin->get_type() == 'comments') {
                    unset($submissionplugins[$idx]);
                }
            }

            $submissionstatus = new assign_submission_status($instance->allowsubmissionsfromdate,
                                                            $instance->alwaysshowdescription,
                                                            $submission,
                                                            $instance->teamsubmission,
                                                            $teamsubmission,
                                                            $submissiongroup,
                                                            $notsubmitted,
                                                            $assignment->is_any_submission_plugin_enabled(),
                                                            $gradelocked,
                                                            //$assignment->is_graded($userid),
                                                            false,
                                                            0, //$instance->duedate,
                                                            0, //$instance->cutoffdate,
                                                            $submissionplugins, //$assignment->get_submission_plugins(),
                                                            $assignment->get_return_action(),
                                                            $assignment->get_return_params(),
                                                            $assignment->get_course_module()->id,
                                                            $assignment->get_course()->id,
                                                            assign_submission_status::GRADER_VIEW,
                                                            //assign_submission_status::STUDENT_VIEW,
                                                            $showedit,
                                                            false,
                                                            $viewfullnames,
                                                            0, //$extensionduedate,
                                                            $assignment->get_context(),
                                                            $assignment->is_blind_marking(),
                                                            '',
                                                            0, //$instance->attemptreopenmethod,
                                                            0, //$instance->maxattempts,
                                                            $assignment->get_grading_status($userid),
                                                            $instance->preventsubmissionnotingroup,
                                                            $usergroups);
            $o .= $this->render($submissionstatus);
        }
        return $o;
    }





















}

