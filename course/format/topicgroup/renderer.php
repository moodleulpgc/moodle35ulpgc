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
 * Renderer for outputting the topicgroup course format.
 *
 * @package format_topicgroup
 * @copyright 2013 E. Castro
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/topics/renderer.php');

/**
 * Basic renderer for topicgroup format.
 *
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_topicgroup_renderer extends format_topics_renderer {

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_topics_renderer::section_edit_controls() only displays the 'Set current section' control when editing mode is on
        // we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'topics topicgroup'));
    }

    /**
     * Generate the edit controls of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of links with edit controls
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = false) {
        global $PAGE;

       
        if (!$PAGE->user_is_editing()) {
            return array();
        }

        $coursecontext = context_course::instance($course->id);

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        //$controls = array_merge($controls, parent::section_edit_controls($course, $section, $onsectionpage));
        $controls = parent::section_edit_control_items($course, $section, $onsectionpage);

        if (has_capability('format/topicgroup:manage', $coursecontext)) {
                $groupingid = (isset($section->groupingid) && $section->groupingid) ? $section->groupingid : 0;
                $url = new moodle_url('/course/format/topicgroup/setgrouping.php', array('id'=>$section->id, 'sesskey'=>sesskey()));
                $icon = ($groupingid) ? 'i/manual_item' : 't/lock';
                $restrictstr = ($groupingid) ? 'editrestrictsection' : 'restrictsection';
                $strrestriction = get_string($restrictstr, 'format_topicgroup');
                $controls[$restrictstr] = array(
                        'url' => $url,
                        'icon' => $icon,
                        'name' => get_string($restrictstr, 'format_topicgroup'),
                        'pixattr' => array('class' => '', 'alt' => $strrestriction),
                        'attr' => array('class' => 'icon editing_unsetgrouping', 'title' => $strrestriction)); 
                
                if($groupingid) {
                    $url->param('unset', 1);
                    $strrestriction = 
                    $controls['unrestrictsection'] = array(
                            'url' => $url,
                            'icon' => 't/unlock',
                            'name' => get_string('unrestrictsection', 'format_topicgroup'),
                            'pixattr' => array('class' => '', 'alt' => $strrestriction),
                            'attr' => array('class' => 'icon editing_unsetgrouping', 'title' => $strrestriction)); 
                }
        }
        
        return $controls;
    }


    /**
     * Generate the content to displayed on the right part of a section
     * before course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return string HTML to output.
     */
    protected function section_right_content($section, $course, $onsectionpage) {
    
        $mark = '';
        if(isset($section->groupingid) && $section->groupingid) {
            $url = new moodle_url('/course/format/topicgroup/setgrouping.php', array('id'=>$section->id, 'sesskey'=>sesskey()));
            $mark = html_writer::link($url,
                                    $this->pix_icon('locked', get_string('restrictsection', 'format_topicgroup'),'format_topicgroup',
                                    array('title' => get_string('editrestrictsection', 'format_topicgroup'), 'class' => 'editing_setgrouping')));
        }
        
        $o = $mark.parent::section_right_content($section, $course, $onsectionpage);
        
        return $o;
    }
    
    /**
     * Output the html for a single section page .
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     * @param int $displaysection The section number in the course which is being displayed
     */
    public function print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {
        global $PAGE;

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();

        // Can we view the section in question?
        if (!($sectioninfo = $modinfo->get_section_info($displaysection)) || !$sectioninfo->uservisible) {
            // This section doesn't exist or is not available for the user.
            // We actually already check this in course/view.php but just in case exit from this function as well.
            print_error('unknowncoursesection', 'error', course_get_url($course),
                format_string($course->fullname));
        }

        $context = context_course::instance($course->id);
        $groupings = groups_get_all_groupings($course->id);
        $canviewall = has_capability('format/topicgroup:manageall', $context);
        format_topicgroup_getset_grouping($thissection);

        if($sectioninfo->groupingid && !(groups_has_membership($sectioninfo) || $canviewall)) {
            $sectioninfo->uservisible = 0;
        }

        if (!$sectioninfo->uservisible) {
            if (!$course->hiddensections) {
                echo $this->start_section_list();
                echo $this->section_hidden($displaysection);
                echo $this->end_section_list();
            }
            // Can't view this section.
            return;
        }

        parent::print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection);
    }

    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $DB, $PAGE;

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        
        
        

        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);

        // Now the list of sections..
        echo $this->start_section_list();
        $numsections = course_get_format($course)->get_last_section_number();

        $groupings = groups_get_all_groupings($course->id);
        $canviewhidden = has_capability('moodle/course:viewhiddensections', $context);
        $canmanage = has_capability('format/topicgroup:manage', $context);
        $canviewall = has_capability('format/topicgroup:manageall', $context);

        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if ($section == 0) {
                // 0-section is displayed a little different then the others
                if ($thissection->summary or !empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {
                    echo $this->section_header($thissection, $course, false, 0);
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                    echo $this->courserenderer->course_section_add_cm_control($course, 0, 0);
                    echo $this->section_footer();
                }
                continue;
            }

            format_topicgroup_getset_grouping($thissection);
            if($thissection->groupingid && !(groups_has_membership($thissection) || $canviewall)) {
                $thissection->uservisible = 0;
            }

            if ($section > $numsections) {
                // activities inside this section are 'orphaned', this section will be printed as 'stealth' below
                continue;
            }
            // Show the section if the user is permitted to access it, OR if it's not available
            // but there is some available info text which explains the reason & should display,
            // OR it is hidden but the course has a setting to display hidden sections as unavilable.
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available && !empty($thissection->availableinfo)) ||
                    (!$thissection->visible && !$course->hiddensections);

            if (!$showsection) {
                // If the hiddensections option is set to 'show hidden sections in collapsed
                // form', then display the hidden section message - UNLESS the section is
                // hidden by the availability system, which is set to hide the reason.
                if (!$course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section, $course->id);
                }

                continue; // here ends sections display if NOT availabe, not uservisible
            }

            if (!$PAGE->user_is_editing() && $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                // Display section summary only.
                echo $this->section_summary($thissection, $course, null);
            } else {
                echo $this->section_header($thissection, $course, false, 0);
                if ($thissection->uservisible) {
                    /// TODO if canview all but not membership => show collapsed
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                    echo $this->courserenderer->course_section_add_cm_control($course, $section, 0);

                    if($thissection->groupingid && $canviewhidden) {
                        $groupinglabel = html_writer::tag('span', get_string('restrictedsectionlbl', 'format_topicgroup', format_string($groupings[$thissection->groupingid]->name)),
                                array('class' => 'groupinglabel groupinglabelright'));
                        echo $groupinglabel ;
                    }
                }
                echo $this->section_footer();
            }
        }

        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            // Print stealth sections if present.
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $numsections or empty($modinfo->sections[$section])) {
                    // this is not stealth section or it is empty
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }

            echo $this->end_section_list();

            echo $this->change_number_sections($course, 0);
        } else {
            echo $this->end_section_list();
        }

    }


    /**
    * Generate the html for a hidden section due to grouping restriction
    *
    * @param int $sectionno The section number in the course which is being displayed
    * @param object $section The actual section object
    * @param array $groupings course groupings names
    * @return string HTML to output.
    */
    protected function section_grouping_hidden($sectionno, $section, $groupings) {
        $o = '';
        $o.= html_writer::start_tag('li', array('id' => 'section-'.$sectionno, 'class' => 'section main clearfix hidden'));
        $o.= html_writer::tag('div', '', array('class' => 'left side'));
        $o.= html_writer::tag('div', '', array('class' => 'right side'));
        $o.= html_writer::start_tag('div', array('class' => 'content hidden dimmed'));
        $o.= $section->availableinfo;
        if($section->groupingid) {
            $groupinglabel = html_writer::tag('span', get_string('restrictedsectionlbl', 'format_topicgroup', format_string($groupings[$section->groupingid]->name)),
                    array('class' => 'groupinglabel groupinglabelright'));
            $o.= $groupinglabel;
            $o.= '<br />';
        }
        $o.= html_writer::end_tag('div');
        $o.= html_writer::end_tag('li');
        return $o;
    }


}
