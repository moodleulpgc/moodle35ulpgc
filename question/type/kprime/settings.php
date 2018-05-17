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
 * @package qtype_kprime
 * @author Amr Hourani amr.hourani@id.ethz.ch
 * @copyright ETHz 2016 amr.hourani@id.ethz.ch
 */
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/question/type/kprime/lib.php');

    // Introductory explanation that all the settings are defaults for the edit_kprime_form.
    $settings->add(
            new admin_setting_heading('configintro', '', get_string('configintro', 'qtype_kprime')));
    // Scoring methods.
    $options = array('kprime' => get_string('scoringkprime', 'qtype_kprime'),
        'kprimeonezero' => get_string('scoringkprimeonezero', 'qtype_kprime'),
        'subpoints' => get_string('scoringsubpoints', 'qtype_kprime')
    );

    $settings->add(
            new admin_setting_configselect('qtype_kprime/scoringmethod',
                    get_string('scoringmethod', 'qtype_kprime'),
                    get_string('scoringmethod_help', 'qtype_kprime'), 'kprime', $options));

    // Shuffle options.
    $settings->add(
            new admin_setting_configcheckbox('qtype_kprime/shuffleanswers',
                    get_string('shuffleanswers', 'qtype_kprime'),
                    get_string('shuffleanswers_help', 'qtype_kprime'), 1));
}
