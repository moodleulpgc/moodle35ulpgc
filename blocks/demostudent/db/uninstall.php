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

function xmldb_block_demostudent_uninstall() {
    global $DB;

    $result = true;

    // Delete all traces of the DemoStudent role.
    if ($cruftyrole = $DB->get_record('role', array('shortname' => 'demostudent'))) {
        $result = $result && delete_role($cruftyrole->id);
    }

    // And delete the capabilities specific to the block.
    // Would have expected this to happen automatically, but it didn't seem to.
    $capparam = array('capability' => 'block/demostudent:myaddinstance');
    if ($cruftycapability = $DB->get_records('role_capabilities', $capparam)) {
        $result = $result && $DB->delete_records('role_capabilities', $capparam);
    }
    $capparam = array('capability' => 'block/demostudent:seedemostudentblock');
    if ($cruftycapability = $DB->get_records('role_capabilities', $capparam)) {
        $result = $result && $DB->delete_records('role_capabilities', $capparam);
    }

    return $result;
}
