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
 * Handle view switching requests for the demostudent block.
 *
 * @package block_demostudent
 * @author Dominik Royko royko@ualberta.ca
 **/

require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/demostudent/locallib.php');
require_once($CFG->dirroot.'/lib/enrollib.php');

global $USER, $DB;

$url = required_param('url', PARAM_NOTAGS);
$viewrole = required_param('viewrole', PARAM_NOTAGS);
$courseid = required_param('courseid', PARAM_INT);
$userid = $USER->id;
$username = $USER->username;
$sesskey = $USER->sesskey;

require_sesskey();
require_login($courseid);

$coursecontext = context_course::instance($courseid);

if ($viewrole == 'instructor' || $viewrole == 'firstuse') {
    // You were an instructor, and you are switching to DemoStudent view.
    // Or you are creating/enrolling a DemoStudent in this course for the first time.

    // If we do not have the capability to add this block, we likely landed here by accident or malice.
    if (!has_capability('block/demostudent:addinstance', $coursecontext)) {
        // Test this as above, but start from a student account instead of DemoStudent.
        print get_string('errormissingaddinstancecapability', 'block_demostudent');
        require_logout();
        redirect(new moodle_url($url, array('redirect' => 1)));
    }

    $demostudentusername = generate_demostudent_name($username);

    $demostudentuser = get_complete_user_data('username', $demostudentusername);
    if (!$demostudentuser) {
        // DemoStudent account does not exist yet.  Let's create it.
        // Test this by deleting your DemoStudent account from mdl_user DB table.
        $demostudentuser = create_demostudent_account($demostudentusername);
        if (!$demostudentuser) {
            // Test this by setting $demostudentuser to false.
            print get_string('errorfailedtocreateuser', 'block_demostudent', $demostudentusername);
            print get_string('returntocourse', 'block_demostudent', $url);
            exit;
        }
    } // Else DemoStudent user already exists for this instructor.

    $demostudentid = $demostudentuser->id;

    // Enrol DemoStudent in this course (as a demostudent).
    $demostudentroleid = get_roleid_by_roleshortname('demostudent');
    if (!$demostudentroleid) {
        print get_string('warningmissingrole', 'block_demostudent');
    }

    if (!(is_enrolled($coursecontext, $demostudentuser))) {
        if (!(enrol_try_internal_enrol($courseid, $demostudentid, $demostudentroleid))) {
            // Enrolment failed.  Haven't seen this happen yet.
            trigger_error('DemoStudent user enrolment failed!<br>Parameters:<br>'.
                                            '<br> url='.var_export($url, true).
                                            '<br> viewrole='.var_export($viewrole, true).
                                            '<br> courseid='.var_export($courseid, true).
                                            '<br>Globals:'.
                                            '<br> userid='.var_export($userid, true).
                                            '<br> username='.var_export($username, true).
                                            '<br>Locals:'.
                                            '<br> demostudentid='.var_export($demostudentid, true).
                                            '<br> demostudentroleid='.var_export($demostudentroleid, true).
                                            '<br> coursecontext='.var_export($coursecontext, true).
                                            '<br> demostudentuser='.var_export($demostudentuer, true));
            // It's possible that the function failed, but that the user is nevertheless enrolled.
            // If the user is not enrolled, a later check will take care of things.
        }
    }

    // Aditionally, if we are switching from 'instructor' view (i.e., not in 'firstuse' mode), switch user.
    if ($viewrole == 'instructor') {
        if (is_siteadmin($demostudentid)) {
            // This should never happen.  Haven't seen it happen yet.
            print_error('nologinas');
        }
        if (!is_enrolled($coursecontext, $demostudentid)) {
            // This should never happen.  Haven't seen it happen yet.
            print_error('usernotincourse');
        }

        // Switch role using builtin loginas functionality.
        $syscontext = context_system::instance();
        \core\session\manager::loginas($demostudentid, $syscontext);
    }

} else {
    // You were on the DemoStudent view.  You're switching back to instructor.
    if (\core\session\manager::is_loggedinas()) {
        // Later, we would like to return to the session before we switched roles without requiring login,
        // iff we can ensure that it's not a security hole.
        require_logout();
        redirect(new moodle_url($url, array('redirect' => 1)));
    } else {
        // If the demouser somehow manages to login without using the DemoStudent block, make them log out.
        // This should not typically happen.  DemoStudent should always be 'loginas'ed.
        // You can test this by:
        // - disabling pasword clobbering of the demostudent user
        // - logging in manually
        // - viewing course
        // - clicking [Switch view].
        require_logout();
        redirect(new moodle_url($url, array('redirect' => 1)));
    }
}

redirect(new moodle_url($url, array('redirect' => 1)));
