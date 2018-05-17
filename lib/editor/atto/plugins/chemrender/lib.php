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
 * Atto ChemRender plugin lib.
 *
 * @package    atto_chemrender
 * @copyright  2016 UC Regents
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Initialise the strings required for JS.
 *
 * @return void
 */
function atto_chemrender_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array(
        'browserepositories',
        'chemdoodle',
        'enterfilename',
        'enterurl',
        'height',
        'jmol',
        'jmoloptions',
        'optionaxis',
        'optionconsolecommand',
        'optionconsolecommandhelp',
        'optiondownloadlink',
        'optionhelplink',
        'optionlabel',
        'optionspin',
        'optionstyleselect',
        'optionxaxis',
        'optionyaxis',
        'pluginname',
        'preview',
        'rendererselection',
        'submit',
        'width'
            ), 'atto_chemrender');
}

/**
 * Set plugin parameters.
 *
 * @param string $elementid
 * @param stdClass $options - the options for the editor, including the context.
 * @param stdClass $fpoptions - unused.
 */
function atto_chemrender_params_for_js($elementid, $options, $fpoptions) {
    global $CFG;

    // Check that ChemRender filter is active by processing sample text with the current filter set.
    $chemrendertext = '<a href="' . $CFG->wwwroot . '/chemrender/test.mol?width=300&amp;height=300&amp;renderer=chemdoodle&amp;sketcheroutput=true">H4sIAAAAAAAAA41Ouw7CMAzc8xX3A6RuCkNYU8YylpmGoCIldUWD8vsNLQjYsG6wfQ9dw97Zh3e43jnA9C7UzJd8n1wHw2HkwQ1xEqKPcdwXRUpJ3myW+XM3SctBAAooAfrCZ7TWaBURZR02JLdVReumdrQoSWaWYPAb8YRY+ZcH/3pyGVW+eyy/Bjgc6xlx/xDl7AAAAA==</a>';
    $result = format_text($chemrendertext, true, $options);
    $chemrenderfilteractive = ($chemrendertext !== $result);
    $context = $options['context'];
    if (!$context) {
        $context = context_system::instance();
    }

    return array(
        'chemrenderfilteractive' => $chemrenderfilteractive,
        'contextid' => $context->id,
    );
}