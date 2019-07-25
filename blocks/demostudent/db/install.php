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
 * @package block
 * @subpackage demostudent
 * @author Dominik Royko royko@ualberta.ca
 */

function xmldb_block_demostudent_install() {
    global $DB;
    $result = true;
    $systemcontext  = context_system::instance();

    // Create DemoStudent role.
    $contextlevels = array(CONTEXT_COURSE, CONTEXT_MODULE);
    if (!$demostudentrole = $DB->get_record('role', array('shortname' => 'demostudent'))) {
        if ($roleid = create_role(get_string('roledemostudentname', 'block_demostudent'),
                                  'demostudent',
                                  get_string('roledemostudentdescription', 'block_demostudent'),
                                  'student')) {
            $newrole = new stdClass();
            $newrole->id = $roleid;

            // Set the capabilities to the archetype (student).
            // Caution: new capabilities un/set here can get clobbered by 'clonepermissionsfrom',
            // defined in access.php.
            reset_role_capabilities($roleid);

            update_capabilities('blocks/demostudent');  // Workaround for MDL-65668 bug.
            
            // DemoStudent should be able to see hidden courses to facilitate testing.
            $result = $result && assign_capability('moodle/course:viewhiddencourses', CAP_ALLOW,
                                                   $newrole->id, $systemcontext->id);

/*
            // DemoStudent needs to see the DemoStudent block.
            $result = $result && assign_capability('block/demostudent:seedemostudentblock', CAP_ALLOW,
                                                   $newrole->id, $systemcontext->id);

            // DemoStudent should NOT be able to add more demostudents!
            $result = $result && unassign_capability('block/demostudent:addinstance', $newrole->id);
            $systemcontext->mark_dirty();
*/
            set_role_contextlevels($newrole->id, $contextlevels);
        } else {
            $result = false;
        }
    }

    return $result;

}
