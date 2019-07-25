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


// This file keeps track of upgrades to
// the demostudent block.
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php.

/**
 * @package block_demostudent
 * @author Dominik Royko royko@ualberta.ca
 **/

require_once($CFG->dirroot.'/blocks/demostudent/locallib.php');

function xmldb_block_demostudent_upgrade($oldversion=0) {
    global $CFG;

    $result = true;

    // Assign moodle/course:viewhiddencourses capability to demostudent role.
    if ($oldversion < 2013100201) {
        if (get_capability_info('moodle/course:viewhiddencourses')) {
            $demostudentroleid = get_roleid_by_roleshortname('demostudent');
            assign_capability('moodle/course:viewhiddencourses', CAP_ALLOW, $demostudentroleid, 1);
        }
        upgrade_plugin_savepoint(true, 2013100201,  'block', 'demostudent');
    }

    return $result;
}
