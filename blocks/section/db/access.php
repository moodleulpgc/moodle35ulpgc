<?php

// This file is part of the Section block for Moodle - http://moodle.org/
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
 * Access rights
 *
 * @package    block
 * @subpackage section
 * @copyright  2012 onwards Nathan Robbins (https://github.com/nrobbins)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$capabilities = array(
    'block/section:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),

    'block/section:myaddinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),


    // Can edit courses // ecastro ULPGC
    'block/section:editcourses' => array(
         'captype' => 'write',
         'contextlevel' => CONTEXT_COURSE,
         'legacy' => array(
              'editingteacher' => CAP_INHERIT,
              'coursecreator' => CAP_INHERIT,
              'manager' => CAP_ALLOW,
         )
    ),

    // Can edit sections
    'block/section:editsections' => array(
         'captype' => 'write',
         'contextlevel' => CONTEXT_COURSE,
         'legacy' => array(
              'editingteacher' => CAP_INHERIT,
              'coursecreator' => CAP_INHERIT,
              'manager' => CAP_ALLOW,
         )
    )


);
