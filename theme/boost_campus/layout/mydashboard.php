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
 * Theme Boost Campus - Layout file.
 *
 * @package   theme_boost_campus
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 * @copyright based on code from theme_boost by Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
// MODIFICATION START.
global $PAGE;
// MODIFICATION END.

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
user_preference_allow_ajax_update('sidepre-open', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');
// MODIFICATION Start: Require own locallib.php.
require_once($CFG->dirroot . '/theme/boost_campus/locallib.php');
// MODIFICATION END.

if (isloggedin()) {
    // ecastro default closed
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'false') == 'true');
    // ecastro ULPGC from moove
    $draweropenright = (get_user_preferences('sidepre-open', 'false') == 'true');
} else {
    $navdraweropen = false;
    // ecastro ULPGC from moove 
    $draweropenright = false;
}
$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}

$blockshtml = $OUTPUT->blocks('side-pre');
$blockshtml_side = $OUTPUT->blocks('side-side');
$blockshtml_top = $OUTPUT->blocks('toprow');
$blockshtml_bottom = $OUTPUT->blocks('bottomrow');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;
$hassideblocks = strpos($blockshtml_side, 'data-block=') !== false;


// ecastro ULPGC from moove
if ($draweropenright && $hasblocks) {
    $extraclasses[] = 'drawer-open-right';
}

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
// MODIFICATION START: Setting 'catchshortcuts'.
// Initialize array.
$catchshortcuts = array();
// If setting is enabled then add the parameter to the array.
if (get_config('theme_boost_campus', 'catchendkey') == true) {
    $catchshortcuts[] = 'end';
}
// If setting is enabled then add the parameter to the array.
if (get_config('theme_boost_campus', 'catchcmdarrowdown') == true) {
    $catchshortcuts[] = 'cmdarrowdown';
}
// If setting is enabled then add the parameter to the array.
if (get_config('theme_boost_campus', 'catchctrlarrowdown') == true) {
    $catchshortcuts[] = 'ctrlarrowdown';
}
// MODIFICATION END.

// MODIFICATION START: Setting 'darknavbar'.
if (get_config('theme_boost_campus', 'darknavbar') == 'yes') {
    $darknavbar = true;
} else {
    $darknavbar = false;
}
// MODIFICATION END.

// MODIFICATION START: Setting 'navdrawerfullwidth'.
$navdrawerfullwidth = get_config('theme_boost_campus', 'navdrawerfullwidth');
// MODIFICATION END.

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'sidepostblocks' => $blockshtml_side,
    'topblocks' => $blockshtml_top,
    'bottomblocks' => $blockshtml_bottom,
    'hasblocks' => $hasblocks,
    'hassideblocks' => $hassideblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    // ecastro ULPGC moove
    'draweropenright' => $draweropenright,
    // MODIFICATION START: Add Boost Campus realated values to the template context.
    'catchshortcuts' => json_encode($catchshortcuts),
    'navdrawerfullwidth' => $navdrawerfullwidth,
    'darknavbar' => $darknavbar
    // MODIFICATION END.
];

// MODIDFICATION START.
// Use the returned value from theme_boost_campus_get_modified_flatnav_defaulthomepageontop as the template context.
$templatecontext['flatnavigation'] = theme_boost_campus_process_flatnav($PAGE->flatnav);
// If setting showsettingsincourse is enabled.
if (get_config('theme_boost_campus', 'showsettingsincourse') == 'yes') {
    // Context value for requiring incoursesettings.js.
    $templatecontext['incoursesettings'] = true;
    // Add the returned value from theme_boost_campus_get_incourse_settings to the template context.
    $templatecontext['node'] = theme_boost_campus_get_incourse_settings();
    // Add the returned value from theme_boost_campus_get_incourse_activity_settings to the template context.
    $templatecontext['activitynode'] = theme_boost_campus_get_incourse_activity_settings();
}

// Render colums2.mustache from boost_campus.
echo $OUTPUT->render_from_template('theme_boost_campus/mydashboard', $templatecontext);
// MODIFICATION END.
/* ORIGINAL START.
echo $OUTPUT->render_from_template('theme_boost/columns2', $templatecontext);
ORIGINAL END. */

// MODIFICATION START: Require additional layout files.
// Add footer blocks and standard footer.
require_once(__DIR__ . '/includes/footer.php');
// Get imageareaitems config.
$imageareaitems = get_config('theme_boost_campus', 'imageareaitems');
if (!empty($imageareaitems)) {
    // Add imagearea layout file.
    require_once(__DIR__ . '/includes/imagearea.php');
}

require_once(__DIR__ . '/includes/ulpgcfooter.php'); // ecastro ULPGC

// Get footnote config.
$footnote = get_config('theme_boost_campus', 'footnote');
if (!empty($footnote)) {
    // Add footnote layout file.
    require_once(__DIR__ . '/includes/footnote.php');
}
// MODIFICATION END.
