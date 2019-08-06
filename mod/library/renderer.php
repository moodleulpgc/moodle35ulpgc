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
 * Library module renderer
 *
 * @package   mod_library
 * @copyright 2019 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class mod_library_renderer extends plugin_renderer_base {


    /**
    * Print library heading.
    * @return void
    */
    public function print_heading() {
        $library = $this->page->activityrecord;
        echo $this->heading(format_string($library->name), 2);
        groups_print_activity_menu($this->page->cm, $this->page->url);
        $currentgroup = groups_get_activity_group($this->page->cm, true);
        
    }

    /**
    * Print library introduction.
    * @param bool $ignoresettings print even if not specified in modedit
    * @return void
    */
    public function print_intro($ignoresettings=false) {
        
        $library = $this->page->activityrecord;
        $cm = $this->page->cm;
        $options = empty($library->displayoptions) ? array() : unserialize($library->displayoptions);

        $extraintro = library_get_optional_details($library, $cm);
        if ($extraintro) {
            // Put a paragaph tag around the details
            $extraintro = html_writer::tag('p', $extraintro, array('class' => 'librarydetails'));
        }

        if ($ignoresettings || !empty($options['printintro']) || $extraintro) {
            $gotintro = trim(strip_tags($library->intro));
            if ($gotintro || $extraintro) {
                echo $this->box_start('mod_introbox', 'libraryintro');
                if ($gotintro) {
                    echo format_module_intro('library', $library, $cm->id);
                }
                echo $extraintro;
                echo $this->box_end();
            }
        }
    }


    /**
    * Print warning that file can not be found.
    * @return void, does not return
    */
    public function print_filenotfound($filename = '') {

        echo $this->header();
        $this->print_heading();
        $this->print_intro();
        
        echo $this->notification(get_string('filenotfound', 'library', $filename));
        echo $this->footer();
        
        die;
    }




    /**
    * Display embedded library file.
    * @param stored_file $file main file
    * @return does not return
    */
    public function print_embed($file) {

        $library = $this->page->activityrecord;
        $cm = $this->page->cm;

        $clicktoopen = $this->get_clicktoopen($file, $library->id);

        $context = $this->page->context;
        $moodleurl = moodle_url::make_pluginfile_url($context->id, 'mod_library', 'content', $library->id,
                $file->get_filepath(), $file->get_filename());

        $mimetype = $file->get_mimetype();
        $title    = $library->name;

        $extension = resourcelib_get_extension($file->get_filename());

        $mediamanager = core_media_manager::instance($this->page);
        $embedoptions = array(
            core_media_manager::OPTION_TRUSTED => true,
            core_media_manager::OPTION_BLOCK => true,
        );

        if (file_mimetype_in_typegroup($mimetype, 'web_image')) {  // It's an image
            $code = resourcelib_embed_image($moodleurl->out(), $title);

        } else if ($mimetype === 'application/pdf') {
            // PDF document
            $code = resourcelib_embed_pdf($moodleurl->out(), $title, $clicktoopen);

        } else if ($mediamanager->can_embed_url($moodleurl, $embedoptions)) {
            // Media (audio/video) file.
            $code = $mediamanager->embed_url($moodleurl, $title, 0, 0, $embedoptions);

        } else {
            // We need a way to discover if we are loading remote docs inside an iframe.
            $moodleurl->param('embed', 1);

            // anything else - just try object tag enlarged as much as possible
            $code = resourcelib_embed_general($moodleurl, $title, $clicktoopen, $mimetype);
        }

        echo $this->header();
        $this->print_heading();
        $this->print_intro();

        echo $code;

        //$this->print_details();

        echo $this->footer();
        die;
    }

    /**
    * Display library document in frames.
    * @param stored_file $file main file
    * @return does not return
    */
    public function print_in_frame($file) {
        global $CFG, $PAGE;

        $library = $this->page->activityrecord;
        $cm = $this->page->cm;
        $course = $this->page->course;
        
        $frame = optional_param('frameset', 'main', PARAM_ALPHA);

        if ($frame === 'top') {
            $PAGE->set_pagelayout('frametop');
            
            echo $this->header();
            $this->print_heading();
            $this->print_intro();

            echo $this->footer();
            die;

        } else {
            $config = get_config('library');
            $context = $this->page->context;
            $path = '/'.$context->id.'/mod_library/content/'.$library->revision.$file->get_filepath().$file->get_filename();
            $fileurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);
            $navurl = "$CFG->wwwroot/mod/library/view.php?id=$cm->id&amp;frameset=top";
            $title = strip_tags(format_string($course->shortname.': '.$library->name));
            $framesize = $config->framesize;
            $contentframetitle = s(format_string($library->name));
            $modulename = s(get_string('modulename','library'));
            $dir = get_string('thisdirection', 'langconfig');

            $file = <<<EOF
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
    <html dir="$dir">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>$title</title>
    </head>
    <frameset rows="$framesize,*">
        <frame src="$navurl" title="$modulename" />
        <frame src="$fileurl" title="$contentframetitle" />
    </frameset>
    </html>
EOF;

            @header('Content-Type: text/html; charset=utf-8');
            echo $file;
            die;
        }
    }

    /**
    * Internal function - create click to open text with link.
    */
    public function get_clicktoopen($file, $revision, $extra='') {
        global $CFG;

        $filename = $file->get_filename();
        $path = '/'.$file->get_contextid().'/mod_library/content/'.$revision.$file->get_filepath().$file->get_filename();
        $fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);

        $string = get_string('clicktoopen', 'library', "<a href=\"$fullurl\" $extra>$filename</a>");

        return $string;
    }

    /**
    * Internal function - create click to open text with link.
    */
    public function get_clicktodownload($file, $revision) {
        global $CFG;

        $filename = $file->get_filename();
        $path = '/'.$file->get_contextid().'/mod_library/content/'.$revision.$file->get_filepath().$file->get_filename();
        $fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, true);

        $string = get_string('clicktodownload', 'library', "<a href=\"$fullurl\">$filename</a>");

        return $string;
    }

    /**
    * Print library info and workaround link when JS not available.
    * @param object $library
    * @param object $cm
    * @param object $course
    * @param stored_file $file main file
    * @return does not return
    */
    public function print_workaround($file) {
        global $CFG, $OUTPUT;

        echo $this->header();
        $this->print_heading();
        $this->print_intro();

        $library = $this->page->activityrecord;
        $cm = $this->page->cm;
        $options = empty($library->displayoptions) ? array() : unserialize($library->displayoptions);

        
        $library->mainfile = $file->get_filename();
        echo '<div class="libraryworkaround">';
        switch (library_get_final_display_type($library)) {
            case RESOURCELIB_DISPLAY_POPUP:
                $path = '/'.$file->get_contextid().'/mod_library/content/'.$library->id.$file->get_filepath().$file->get_filename();
                $fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);
                $options = empty($library->displayoptions) ? array() : unserialize($library->displayoptions);
                $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
                $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
                $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
                $extra = "onclick=\"window.open('$fullurl', '', '$wh'); return false;\"";
                echo $this->get_clicktoopen($file, $library->id, $extra);
                break;

            case RESOURCELIB_DISPLAY_NEW:
                $extra = 'onclick="this.target=\'_blank\'"';
                echo $this->get_clicktoopen($file, $library->id, $extra);
                break;

            case RESOURCELIB_DISPLAY_DOWNLOAD:
                echo $this->get_clicktodownload($file, $library->id);
                break;

            case RESOURCELIB_DISPLAY_OPEN:
            default:
                echo $this->get_clicktoopen($file, $library->id);
                break;
        }
        echo '</div>';

        echo $OUTPUT->footer();
        die;
    }



    /**
     * Returns html to display the content of mod_library as a folder of files
     *
     * @param stdClass $folder the folder to display
     * @return string
     */
    public function print_folder($folder) {
        echo $this->header();
        $this->print_heading();
        $this->print_intro();
        echo $this->display_folder($folder);

        echo $this->footer();
        die;
    }


    /**
     * Returns html to display the content of mod_library as a folder of files
     *
     * @param stdClass $folder the folder to display
     * @return string
     */
    public function display_folder($folder) {
        $output = '';
        
        $library = $this->page->activityrecord;
        
        $libraryinstances = get_fast_modinfo($library->course)->get_instances_of('library');
        if (!isset($libraryinstances[$library->id]) ||
                !($cm = $libraryinstances[$library->id]) ||
                !($context = context_module::instance($cm->id))) {
            // Some error in parameters.
            // Don't throw any errors in renderer, just return empty string.
            // Capability to view module must be checked before calling renderer.
            return $output;
        }

        if (trim($library->intro)) {
            if ($cm->showdescription) {
                // for "display inline" do not filter, filters run at display time.
                $output .= format_module_intro('library', $library, $cm->id, false);
            }
        }

        $foldertree = new library_folder_tree($library, $cm, $folder);

        $output .= $this->output->box($this->render($foldertree),
                'generalbox foldertree');

        return $output;
    }

    public function render_library_folder_tree(library_folder_tree $tree) {
        static $treecounter = 0;

        $content = '';
        $id = 'folder_tree'. ($treecounter++);
        $content .= '<div id="'.$id.'" class="filemanager">';
        $content .= $this->htmllize_tree($tree, array('files' => array(), 'subdirs' => array($tree->dir)));
        $content .= '</div>';
        $showexpanded = true;
        if (empty($tree->library->displayoptions['showexpanded'])) {
            $showexpanded = false;
        }
        $this->page->requires->js_init_call('M.mod_library.init_tree', array($id, $showexpanded));
        return $content;
    }

    /**
     * Internal function - creates htmls structure suitable for YUI tree.
     */
    protected function htmllize_tree($tree, $dir) {
        global $CFG;

        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return '';
        }
        $result = '<ul>';
        foreach ($dir['subdirs'] as $subdir) {
            $image = $this->output->pix_icon(file_folder_icon(24), $subdir['dirname'], 'moodle');
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                    html_writer::tag('span', s($subdir['dirname']), array('class' => 'fp-filename'));
            $filename = html_writer::tag('div', $filename, array('class' => 'fp-filename-icon'));
            $result .= html_writer::tag('li', $filename. $this->htmllize_tree($tree, $subdir));
        }
        foreach ($dir['files'] as $file) {
            $filename = $file->get_filename();
            $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                    $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $filename, false);
            $filenamedisplay = clean_filename($filename);
            if (file_extension_in_typegroup($filename, 'web_image')) {
                $image = $url->out(false, array('preview' => 'tinyicon', 'oid' => $file->get_timemodified()));
                $image = html_writer::empty_tag('img', array('src' => $image));
            } else {
                $image = $this->output->pix_icon(file_file_icon($file, 24), $filenamedisplay, 'moodle');
            }
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                    html_writer::tag('span', $filenamedisplay, array('class' => 'fp-filename'));
            $filename = html_writer::tag('span',
                    html_writer::link($url->out(false, array('forcedownload' => 1)), $filename),
                    array('class' => 'fp-filename-icon'));
            $result .= html_writer::tag('li', $filename);
        }
        $result .= '</ul>';

        return $result;
    }
}

class library_folder_tree implements renderable {
    public $context;
    public $library;
    public $cm;
    public $dir;

    public function __construct($library, $cm, $folder) {
        $this->library = $library;
        $this->cm     = $cm;

        $this->context = context_module::instance($cm->id);
        $fs = get_file_storage();
        $this->dir = $fs->get_area_tree($this->context->id, 'mod_library', 'content', $library->id);
    }
}
