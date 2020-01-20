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
 * Main block code
 *
 * @package    block
 * @subpackage section
 * @copyright  2013 onwards Nathan Robbins (https://github.com/nrobbins)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class block_section extends block_list {
    public function init() {
        $this->title = get_string('pluginname', 'block_section');
    }
    
    public function specialization() {
        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        } else {
            $this->title = get_string('blocktitle', 'block_section');
        }
        if (empty($this->config->section)) {
            if(!isset($this->config)) { //ecastro ULPGC
                $this->config = new stdClass;
            }
            $this->config->section = 0;
            $this->section = 0;
        } else {
            $this->section = $this->config->section;
        }
    }

    
    public function applicable_formats() {
        return [
                'course-view' => false, // ecastro ULPGC
				'site-index' => true, 
                'my' => true
               ];
    }
    
    public function instance_allow_multiple() {
        return true;
    }

    function user_can_addto($page) {
        // Don't allow people to add the block if they can't even use it
        if (!has_capability('moodle/site:config', $page->context)) {
            return false;
        }

        return parent::user_can_addto($page);
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
    
    public function get_content() {
        global $USER, $CFG, $DB, $OUTPUT, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = [];
        $this->content->icons = [];
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }

        if (!empty($this->config->course) && ($DB->get_record('course', ['id' => $this->config->course]) != null)) {
            $course = $DB->get_record('course', ['id' => $this->config->course]);
        } else {
            $course = $this->page->course;
        }
        
        require_once($CFG->dirroot.'/course/lib.php');

        // ecastro ULPGC
        $courserenderer = $PAGE->get_renderer('core', 'course');
        $context = context_course::instance($course->id);
        $isediting = $this->page->user_is_editing() && has_capability('moodle/course:manageactivities', $context);

        $modinfo = get_fast_modinfo($course);

/// extra fast view mode
        if (!$isediting) {
            if (!empty($modinfo->sections[$this->section])) {
                foreach ($modinfo->sections[$this->section] as $cmid) {
                   $cm = $modinfo->cms[$cmid];
                    if (!$cm->uservisible) {
                        continue;
                    }

                   $cminfo = \cm_info::create($cm);

                    if (!($url = $cm->url)) {
                    $this->content->items[] = $cminfo->get_formatted_content(array('overflowdiv' => true, 'noclean' => true)); // ecastro ULPGC
                        $this->content->icons[] = '';
                    } else {
                        $linkcss = $cm->visible ? '' : ' class="dimmed" ';
                    // Accessibility: incidental image - should be empty Alt text
                        $icon = '<img src="' . $cm->get_icon_url() . '" class="icon" alt="" />&nbsp;';
                        $this->content->items[] = '<a title="'.$cm->modplural.'" '.$linkcss.' '.$cm->extra.
                            ' href="' . $url . '">' . $icon . $cminfo->get_formatted_name() . '</a>'.$cm->afterlink;
                    }
                }
            }
            return $this->content;
        }


/// slow & hacky editing mode
        $ismoving = ismoving($course->id);
        $sections = get_fast_modinfo($course->id)->get_section_info_all(); // ecastro ULPGC

        if(!empty($sections) && isset($sections[$this->config->section])) {
            $section = $sections[$this->config->section];
        }

        if (!empty($section)) {
            //get_all_mods($course->id, $mods, $modnames, $modnamesplural, $modnamesused);  // ecastro ULPGC
            $modnames      = get_module_types_names();
            $modinfo = get_fast_modinfo($course->id);
            $mods = $modinfo->get_cms();

        }

        $groupbuttons = $course->groupmode;
        $groupbuttonslink = (!$course->groupmodeforce);

        if ($ismoving) {
            $strmovehere = get_string('movehere');
            $strmovefull = strip_tags(get_string('movefull', '', "'$USER->activitycopyname'"));
            $strcancel= get_string('cancel');
            $stractivityclipboard = $USER->activitycopyname;
        }
    /// Casting $course->modinfo to string prevents one notice when the field is null
        $editbuttons = '';

        if ($ismoving) {
            $this->content->icons[] = '&nbsp;<img align="bottom" src="'.$OUTPUT->pix_url('t/move') . '" class="iconsmall" alt="" />';
            $this->content->items[] = $USER->activitycopyname.'&nbsp;(<a href="'.$CFG->wwwroot.'/course/mod.php?cancelcopy=true&amp;sesskey='.sesskey().'">'.$strcancel.'</a>)';
        }

        if (!empty($section) && !empty($section->sequence)) {
            $sectionmods = explode(',', $section->sequence);
            $options = array('overflowdiv'=>true);
            foreach ($sectionmods as $modnumber) {
                if (empty($mods[$modnumber])) {
                    continue;
                }
                $mod = $mods[$modnumber];
                if (!$ismoving) {
                    if ($groupbuttons) {
                        if (! $mod->groupmodelink = $groupbuttonslink) {
                            //$mod->groupmode = $course->groupmode; // ecastro ULPGC
                        }

                    } else {
                        //$mod->groupmode = false; // ecastro ULPGC
                    }
                    $actions = course_get_cm_edit_actions($mod, -1, null);
                    $editbuttons = '<br />'.$courserenderer->course_section_cm_edit_actions($actions, $mod); // ecastro ULPGC
                } else {
                    $editbuttons = '';
                }
                if ($mod->visible || has_capability('moodle/course:viewhiddenactivities', $context)) {
                    if ($ismoving) {
                        if ($mod->id == $USER->activitycopy) {
                            continue;
                        }
                        $this->content->items[] = '<a title="'.$strmovefull.'" href="'.$CFG->wwwroot.'/course/mod.php?moveto='.$mod->id.'&amp;sesskey='.sesskey().'">'.
                            '<img style="height:16px; width:80px; border:0px" src="'.$OUTPUT->pix_url('movehere') . '" alt="'.$strmovehere.'" /></a>';
                        $this->content->icons[] = '';
                    }

                    //list($content, $instancename) = get_print_section_cm_text($modinfo->cms[$modnumber], $course); // ecastro ULPGC
                    $cm = $modinfo->cms[$modnumber];
                    $content = $cm->get_formatted_content(array('overflowdiv' => true, 'noclean' => true));
                    $after = $cm->afterlink;
                    $instancename = $cm->get_formatted_name();

                    $linkcss = $mod->visible ? '' : ' class="dimmed" ';

                    if (!($url = $mod->url)) {
                        $this->content->items[] = $content . $editbuttons;
                        $this->content->icons[] = '';
                    } else {
                        //Accessibility: incidental image - should be empty Alt text
                        $icon = '<img src="' . $mod->get_icon_url() . '" class="icon" alt="" />&nbsp;';
                        $this->content->items[] = '<a title="' . $mod->modfullname . '" ' . $linkcss . ' ' . $mod->extra .
                            ' href="' . $url . '">' . $icon . $instancename . '</a>' . $after . $editbuttons;
                    }
                }
            }
        }

        if ($ismoving) {
            $this->content->items[] = '<a title="'.$strmovefull.'" href="'.$CFG->wwwroot.'/course/mod.php?movetosection='.$section->id.'&amp;sesskey='.sesskey().'">'.
                                      '<img style="height:16px; width:80px; border:0px" src="'.$OUTPUT->pix_url('movehere') . '" alt="'.$strmovehere.'" /></a>';
            $this->content->icons[] = '';
        }

        if ($modnames) {
            //$this->content->footer = print_section_add_menus($course, $this->config->section, $modnames, true, true);
            $this->content->footer = $courserenderer->course_section_add_cm_control($course, $this->config->section, null, array('inblock' => true)); // ecastro ULPGC
        } else {
            $this->content->footer = '';
        }

        return $this->content;
    }
}


