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
 * The modchooser renderable.
 *
 * @package    theme_boost_campus
 * @copyright  2018 Enrique Castro  ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_boost_campus\output\core;

defined('MOODLE_INTERNAL') || die();

use core\output\chooser;
use core\output\chooser_section;
use context_course;
use lang_string;
use moodle_url;
use pix_icon;
use renderer_base;
use stdClass;

/**
 * The modchooser renderable class.
 *
 * @package    core_course
 * @copyright  2018 Enrique Castro  ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class modchooser extends chooser {

    /** @var stdClass The course. */
    public $course;

    /**
     * Constructor.
     *
     * @param stdClass $course The course.
     * @param stdClass[] $modules The modules.
     */
    public function __construct(stdClass $course, array $modules) {
        $this->course = $course;

        $sections = [];
        $context = context_course::instance($course->id);

        $groups = array('actv_communication', 'actv_collaboration', 'actv_assessment', 
                        'actv_structured', 'actv_games', 'actv_other',
                        'res_files','res_text','res_structured',
                        );
        $other = -1;
        foreach($groups as $type) {
            $group = explode("\n", get_config('local_ulpgccore', $type));
            $group = array_flip(array_map('trim', $group));
            foreach($group as $key => $value) {
                if(array_key_exists($key, $modules)) {
                    $group[$key] = $modules[$key];
                } elseif($key == 'game') {    
                // game is a special module here
                    foreach($modules as $k => $mod) {
                        if(strpos($k, 'game:') !== false) {
                            $group[$k] = $modules[$k];
                        }
                    }
                    // the original game entry MUST be deleted
                    unset($group[$key]);
                } else {
                    unset($group[$key]);
                }
            }
           
            if (count($group)) {
                $sections[] = new chooser_section($type, new lang_string($type, 'local_ulpgccore'),
                    array_map(function($module) use ($context) {
                        return new \core_course\output\modchooser_item($module, $context);
                    }, $group)
                );
                $section = end($sections);
                if($type == 'actv_other') {
                    $other = key($sections);
                }
                $modules = array_diff_key($modules, $section->items);
            }
        }
        reset($sections);
        
        if (count($modules) && $other > 0) {
            $othermods = new chooser_section('other', new lang_string('actv_other', 'local_ulpgccore'),
                array_map(function($module) use ($context) {
                    return new \core_course\output\modchooser_item($module, $context);
                }, $modules)
            );
            $sections[$other]->items = $sections[$other]->items + $othermods->items;
        }
        
        $actionurl = new moodle_url('/course/jumpto.php');
        $title = new lang_string('addresourceoractivity');
        parent::__construct($actionurl, $title, $sections, 'jumplink');

        $this->set_instructions(new lang_string('selectmoduletoviewhelp'));
        $this->add_param('course', $course->id);
    }

    /**
     * Export for template.
     *
     * @param renderer_base  The renderer.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = parent::export_for_template($output);
        $data->courseid = $this->course->id;
        return $data;
    }

}
