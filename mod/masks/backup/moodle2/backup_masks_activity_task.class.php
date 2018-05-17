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
 * MASKS module standard backup / restore implementation
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/masks/backup/moodle2/backup_masks_stepslib.php');

/**
 * Provides all the settings and steps to perform one complete backup of the masks activity
 */
class backup_masks_activity_task extends backup_activity_task {
    /**
     * No masks settings
     */
    protected function define_my_settings() {
    }

    /**
     * Defines activity specific steps for this task
     *
     * This method is called from {@link self::build()}. Activities are supposed
     * to call {self::add_step()} in it to include their specific steps in the
     * backup plan.
     */
    protected function define_my_steps() {
        $this->add_step( new backup_masks_activity_structure_step( 'masks_structure', 'masks.xml' ) );
    }

    /**
     * No content encoding needed for this activity
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the same content with no changes
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base       = preg_quote( $CFG->wwwroot, "/" );

         // Link to the list of masks
        $search     = "/(".$base."\/mod\/masks\/index.php\?id\=)([0-9]+)/";
        $content    = preg_replace( $search, '$@MASKSINDEX*$2@$', $content );

        // Link to masks view by moduleid
        $search     = "/(".$base."\/mod\/masks\/view.php\?id\=)([0-9]+)/";
        $content    = preg_replace( $search, '$@MASKSVIEWBYID*$2@$', $content );

        return $content;
    }
}
