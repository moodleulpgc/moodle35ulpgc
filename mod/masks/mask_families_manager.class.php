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
 * Moodle manager class for mask families
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_masks;

defined('MOODLE_INTERNAL') || die;


require_once(dirname(__FILE__).'/mask_family_note.class.php');
require_once(dirname(__FILE__).'/mask_family_question.class.php');

class mask_families_manager{
    private static $families   = null;

    // -------------------------------------------------------------------------
    // Private utility methods

    private static function populateFamilyList(){
        if ( self::$families == null ){
            self::$families = array(
                'note'       => new mask_family_note,
                'question'   => new mask_family_question,
            );
        }
    }

    // -------------------------------------------------------------------------
    // Public API

    /**
     * Get an array of integer values representing style ids to preesent in the style selector menu
     * @return array of integer styles with '-1' values representing group separators (for use in menu construction)
     */
    public static function getFamilies(){
        self::populateFamilyList();
        return self::$families;
    }
}

