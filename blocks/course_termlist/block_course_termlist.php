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
 * Course termlist block
 *
 * A simpler course overview replacement with courses ordered by term
 *
 * @package    block
 * @subpackage course_termlist
 * @copyright  2012 onwards Enrique Castro at ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/lib/weblib.php');
require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/local/ulpgccore/lib.php');
require_once($CFG->dirroot . '/blocks/supervision/locallib.php');

class block_course_termlist extends block_base {
    /**
     * block initializations
     */
    public function init() {
        $this->title   = get_string('pluginname', 'block_course_termlist');
    }

    function user_can_addto($page) {
        // Don't allow people to add the block if they can't even use it
        if (!has_capability('moodle/site:config', $page->context)) {
            return false;
        }

        return parent::user_can_addto($page);
    }



    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        global $USER, $DB, $CFG, $OUTPUT, $PAGE;
        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $content = array();

        $config = get_config('block_course_termlist');

        $supervisor = get_config('block_supervision');
        $supervisor = $DB->get_record('block', array('name'=>'supervision'));

        if(($supervisor)) {
            //include_once($CFG->dirroot.'/blocks/supervision/lib.php');
        }

        $control_categories = array();
        if($config->showcategorieslink AND $supervisor) {
            $control_categories = supervision_get_reviewed_items($USER->id);
        }

        $control_departments = array();
        if($config->showdepartmentslink AND $supervisor) {
            $control_departments = supervision_get_reviewed_itemnames($USER->id, 'department');
        }

        $courses = enrol_get_users_courses($USER->id, false, 'id, visible');
        $courses = local_ulpgccore_load_courses_details(array_keys($courses), 
                                                            'c.id, c.shortname, c.idnumber, c.fullname, c.category, c.visible, uc.term, uc.credits, uc.ctype, uc.department',
                                                            'c.category ASC, uc.term ASC, c.visible DESC, c.fullname ASC');            
        $site = get_site(); //just in case we need the old global $course hack

        if (array_key_exists($site->id,$courses)) {
            unset($courses[$site->id]);
        }

        $categories = array();
        $categorylist = $DB->get_records('course_categories', null, 'sortorder ASC', 'id, name, idnumber');
        $catorder = array_keys($categorylist);

        $excluded = array();
        if($config->excluded) {
            $excluded = explode(',', $config->excluded);
            foreach($excluded as $key=>$name) {
                $excluded[$key] = trim($name);
            }
        }

        foreach ($courses as $course) {
            if(in_array($course->shortname, $excluded)) {
                continue;
            }
            $order = array_search($course->category, $catorder);
            if(!isset($categories[$order])) {
                $cat = new stdClass();
                $cat->name = $categorylist[$course->category]->name;
                $cat->id = $course->category;
                $cat->term = array();
                $categories[$order] = $cat;
                unset($cat);
            }
            if(!isset($categories[$order]->term[$course->term])) {
                $term = new stdClass();
                $num = sprintf('%02d', $course->term);
                $term->name = get_string('term'.$num, 'block_course_termlist');
                $term->courses = array();
                $categories[$order]->term[$course->term] = $term;
                unset($term);
            }
            $categories[$order]->term[$course->term]->courses[$course->id] = $course;
        }
        ksort($categories);

        $content = array();
        if (empty($categories)) {
            $content[] = $OUTPUT->heading(get_string('nocourses','block_course_termlist'), 4);
        } else {
            foreach($categories as $cat) {
                $content[] = $OUTPUT->container_start('coursebox');
                if(count($categories)>1) {
                $cattitle = format_string($cat->name);
                if(in_array($cat->id, $control_categories)) {  
                    $cattitle = html_writer::link($CFG->wwwroot.'/course/index.php?categoryid='.$cat->id, $cattitle);
                }
                $content[] = $OUTPUT->heading($cattitle, 2, 'my-category');
                }
                foreach($cat->term as $cterm) {
                    $content[] = $OUTPUT->container_start('my-term');
                    $content[] = $OUTPUT->heading(format_string($cterm->name), 3, 'my-term');
                    $courselinks = array();
                    foreach($cterm->courses as $ccourse){
                        $fullname = $ccourse->shortname.' - '.format_string($ccourse->fullname, true, array('context' => context_course::instance($ccourse->id)));
                        $attributes = array('title' => s($fullname), 'class'=>'my-course-name');
                        if (empty($ccourse->visible)) {
                            $attributes['class'] .= ' dimmed ';
                        }
                        if ($news = local_ulpgccore_course_recent_activity($ccourse)) {
                            $news = '&nbsp;'.$OUTPUT->pix_icon('news8', get_string('newactivity', 'block_course_termlist') );
                        } else {
                            $news = '&nbsp;';
                        }

                        $courselinks[] =  html_writer::link(new moodle_url('/course/view.php', array('id' => $ccourse->id)), $fullname.$news, $attributes);
                    }
                    $content[] = html_writer::alist($courselinks, array('class'=>'my-courses-list'));
                    unset($courselinks);
                    $content[] = $OUTPUT->container_end(); // term my-trem
                }
                $content[] = $OUTPUT->container_end(); // categories, coursebox
            }
        }

        /// now supervisor links

        if($control_categories or $control_departments) {
            $content[] = $OUTPUT->container_start('coursebox');
            foreach($control_categories as $catid) {
                $content[] = html_writer::link($CFG->wwwroot.'/course/index.php?categoryid='.$catid, format_string($categorylist[$catid]->name), array('class'=>'my-supervisor-category'));
            }
            foreach($control_departments as $id=>$dept) {
                $content[] = html_writer::link($CFG->wwwroot.'/blocks/supervision/department.php?id='.$id, format_string($dept), array('class'=>'my-supervisor-department'));
            }
            $content[] = $OUTPUT->container_end();


        }

        $this->content->text = implode($content);
        if (empty($config->hideallcourseslink)) {
            $renderer = $PAGE->get_renderer('core', 'course');
            $search = $renderer->course_search_form('', 'short');

            $this->content->footer = "$search   <a href=\"$CFG->wwwroot/?redirect=0\">".get_string("fulllistofcourses")."</a> ...";
        }
        return $this->content;
    }

    /**
     * allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my'=>true, 'my-index'=>true, 'site-index'=>true,);
    }

    /**
     * Default return is false - header will be shown
     * @return boolean
     */
    function hide_header() {
        return true;
    }

    /**
     * Can be overridden by the block to prevent the block from being dockable.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        global $CFG;
        return false;
    }

    /**
     * If overridden and set to true by the block it will not be hidable when
     * editing is turned on.
     *
     * @return bool
     */
    public function instance_can_be_hidden() {
        return false;
    }

}
?>
