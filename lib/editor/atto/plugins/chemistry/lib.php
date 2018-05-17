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
 * Atto text editor integration version file.
 *
 * @package    atto_chemistry
 * @copyright  2014 Geoffrey Rowland <rowland.geoff@gmail.com>
 * Based on    @package atto_equation
 * @copyright  2013 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Get the list of strings for this plugin.
 * @param string $elementid
 */
function atto_chemistry_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('savechemistry',
                                          'editchemistry',
                                          'preview',
                                          'cursorinfo',
                                          'update',
                                          'librarygroup1',
                                          'librarygroup2',
                                          'librarygroup3',
                                          'librarygroup4',
                                          'librarygroup5',
                                          'librarygroup6',
                                          'librarygroup7',
                                          'librarygroup1_title',
                                          'librarygroup2_title',
                                          'librarygroup3_title',
                                          'librarygroup4_title',
                                          'librarygroup5_title',
                                          'librarygroup6_title',
                                          'librarygroup7_title'),
                                    'atto_chemistry');
}

/**
 * Set params for this plugin.
 *
 * @param string $elementid
 * @param stdClass $options - the options for the editor, including the context.
 * @param stdClass $fpoptions - unused.
 */
function atto_chemistry_params_for_js($elementid, $options, $fpoptions) {
    $texexample = '$$\ce{H2O}$$';

    // Format a string with the active filter set.
    // If it is modified - we assume that some sort of text filter is working in this context.
    $result = format_text($texexample, true, $options);
    $texfilteractive = ($texexample !== $result);
    $context = $options['context'];
    if (!$context) {
        $context = context_system::instance();
    }

    // Tex example libraries.
    $library = array(
            'group1' => array(
                'groupname' => 'librarygroup1',
                'grouptitle' => 'librarygroup1_title',
                'elements' => get_config('atto_chemistry', 'librarygroup1'),
            ),
            'group2' => array(
                'groupname' => 'librarygroup2',
                'grouptitle' => 'librarygroup2_title',
                'elements' => get_config('atto_chemistry', 'librarygroup2'),
            ),
            'group3' => array(
                'groupname' => 'librarygroup3',
                'grouptitle' => 'librarygroup3_title',
                'elements' => get_config('atto_chemistry', 'librarygroup3'),
            ),
            'group4' => array(
                'groupname' => 'librarygroup4',
                'grouptitle' => 'librarygroup4_title',
                'elements' => get_config('atto_chemistry', 'librarygroup4'),
            ),
            'group5' => array(
                'groupname' => 'librarygroup5',
                'grouptitle' => 'librarygroup5_title',
                'elements' => get_config('atto_chemistry', 'librarygroup5'),
            ),
            'group6' => array(
                'groupname' => 'librarygroup6',
                'grouptitle' => 'librarygroup6_title',
                'elements' => get_config('atto_chemistry', 'librarygroup6'),
            ),
            'group7' => array(
                'groupname' => 'librarygroup7',
                'grouptitle' => 'librarygroup7_title',
                'elements' => get_config('atto_chemistry', 'librarygroup7'),
            ));

    return array('texfilteractive' => $texfilteractive,
                 'contextid' => $context->id,
                 'library' => $library,
                 'texdocsurl' => get_docs_url('Chemistry_notation_using_mhchem'));
}
