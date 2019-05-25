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
 * Base class for videolib source plugins.
 *
 * @package   mod_videolib
 * @copyright 2019 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Base class for videolib source plugins.
 *
 * Doesn't do anything on it's own -- it needs to be extended.
 * This class displays videolib sources.  Because it is called from
 * within /mod/videolib/source.php you can assume that the page header
 * and footer are taken care of.
 *
 * This file can refer to itself as source.php to pass variables
 * to itself - all these will also be globally available.  You must
 * pass "id=$cm->id" or q=$videolib->id", and "mode=sourcename".
 *
 * @copyright 2019 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class videolib_source_base {
    /** @var string the source name. */
    protected $source;
    /** @var int the search type. */
    protected $searchtype;
    /** @var string item search pattern. */
    protected $searchpattern;
    /** @var int the display mode. */
    protected $display;
    /** @var array the display options. */
    protected $displayoptions;

    /**
     * Create an instance of this source for a particular videolib.
     * @param $videolib record from the database.
     */
    public function __construct($videolib) {
        $this->source = $videolib->source;
        $this->searchtype = $videolib->searchtype;
        $this->searchpattern = $videolib->searchpattern;
        if($videolib->searchtype &&  $parameters) {
            $this->searchpattern = str_replace(array_keys($parameters),array_values($parameters), $videolib->searchpattern);
        }
        $this->display = $videolib->display;
        $this->displayoptions = empty($videolib->displayoptions) ? array() : unserialize($videolib->displayoptions);
    }

    /**
     * Localize searchpattern with module instance data.
     * @param $parameters associative array with param names and values
     */
    public function get_processed_searchpattern($parameters) {
        $pattern = '';
        if($this->searchtype &&  $parameters) {
            $pattern = str_replace(array_keys($parameters),array_values($parameters), $this->searchpattern);
        }
        
        return $pattern;
    }
    
    /**
     * Override this function to displays the source.
     * @param $cm the course-module for this videolib.
     * @param $course the coures we are in.
     * @param $videolib this videolib.
     */
    public function show() {
        return get_string('defaultmessage', 'videolib');
    }

}
