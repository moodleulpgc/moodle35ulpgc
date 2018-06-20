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
 * Theme Boost Campus - Layout file for ULPGC grey footer.
 *
 * @package   theme_boost_campus
 * @copyright 2018 Enrique Castro @ ULPGC
 * @copyright based on code from theme_boost by Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


$templatecontext = array();
foreach(array(1,2,3) as $i) {
    if($text = get_config('local_ulpgccore', 'footer'.$i)) {
        $templatecontext['block'.$i] = $text;
    }
}
// Only proceed if text area does not only contains empty tags.
if ($templatecontext) {
    echo $OUTPUT->render_from_template('theme_boost_campus/ulpgcfooter', $templatecontext);
}
