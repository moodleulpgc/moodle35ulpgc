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
 * Implementaton of the videolibsource_bustreaming plugin.
 *
 * @package    videolibsource
 * @subpackage bustreaming
 * @copyright  2019 Enrique  Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/videolib/source/sourcebase.php');


/**
 * A rule representing the time limit. It does not actually restrict access, but we use this
 * class to encapsulate some of the relevant code.
 *
 * @copyright  2009 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class videolibsource_bustreaming extends videolib_source_base {

    public function make_instance_url($pattern = null) {
        $videoid = '';
        if($this->searchtype) {
            $videoid = $this->get_busid_from_pattern($pattern);
        } else {
            $videoid = $this->searchpattern;
        }
    
        $this->videourl = 'https://bustreaming.ulpgc.es/reproducirEmbed/'.$videoid;
    }
    
    public function get_busid_from_pattern() {    
    
    }
    
    public function show() {
        if($this->videourl) {
            $iframeattrs = [
                'src' => $this->videourl,
                'height' => '100%',
                'width' => '100%',
                'style' => 'border: 0px; border-style: none; max-width: 100%; max-height: 100vh ',
                'allowfullscreen' => 'true',
                'webkitallowfullscreen' => 'true',
                'mozallowfullscreen' => 'true',
                'msallowfullscreen' => 'true',
                'class' => 'videolibvideo',
            ];    
            $iframe = html_writer::tag('iframe', '', $iframeattrs);
        } else {
            $iframe = get_string("emptymessage", 'videolib');
        }
    
        return html_writer::div($iframe, ' videolibvideo ');
    }


}
