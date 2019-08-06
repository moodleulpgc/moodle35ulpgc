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
 * Implementaton of the librarysource_filesystem plugin.
 *
 * @package    librarysource
 * @subpackage filesystem
 * @copyright  2019 Enrique  Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/library/source/sourcebase.php');


/**
 * A wrapper for a filesystem repository class to manage Library files
 *
 * @copyright  2019 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class librarysource_filesystem extends library_source_base {

    
    use filesystem_manage_files; 

    /**
     * Get a list of files within a folder .
     * @param $pathname folder to list.
     */
    public function list_files($pathname) {
        $listing = ':'.base64_encode($pathname).':';
        return $this->repository->get_listing($listing)['list'];
    }
    
}
