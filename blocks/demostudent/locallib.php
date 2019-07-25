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
 * Helper functions to support demostudent view switching.
 *
 * @package block_demostudent
 * @author Dominik Royko royko@ualberta.ca
 **/

defined('MOODLE_INTERNAL') || die;

// Magic string with which to replace password hash in db, preventing manual logins.
$passwordfiller = 'no-login';

/**
 * Create a DemoStudent account which cannot log in manually, using the given username,
 * @param string demostudentusername  username of account to create
 * @return object  the user object
 */
function create_demostudent_account($demostudentusername) {
    global $USER, $DB, $passwordfiller;

    $demostudentuser = create_user_record($demostudentusername, '');
    if ($demostudentuser) {
        $demostudentuser->firstname = "DemoStudent";
        $demostudentuser->lastname = $USER->lastname;
        $demostudentuser->description = "DemoStudent account.";
        $demostudentuser->email = $USER->email;
        $demostudentuser->password = $passwordfiller;
        $DB->update_record('user', $demostudentuser);
    }
    return $demostudentuser;
}

/**
 * Generate a username for a DemoStudent account, based on the username of the instructor.
 * @param string instructorusername  username of instructor account requiring a DemoStudent user
 * @return string  the user account object; garbage in, garbage out
 */
function generate_demostudent_name($instructorusername) {
    // Permitted: period.
    // Not permitted: Capital letters (get squashed to lowercase, may break functionality).
    return "demostudent.for.".$instructorusername;
}

/**
 * Find the id of a role based on the shortname.  You would think this would exist in core...
 * @param string roleshortname  the shortname of the role for which we need the id
 * @return string  the id of the role; false if role not found
 */
function get_roleid_by_roleshortname($roleshortname) {
    $roles = get_all_roles();
    //$roleshortnames = array_map(create_function('$r', 'return $r->shortname;'), $roles);
    $roleshortnames = array_map(function($r){return $r->shortname;}, $roles); // ecastro ULPGC avoid use deprecated function

    return array_search($roleshortname, $roleshortnames);
}
