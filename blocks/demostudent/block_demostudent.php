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
 * A custom block to allow easy access to demo student view.
 *
 * @package block_demostudent
 * @author Dominik Royko royko@ualberta.ca
 **/

require_once($CFG->dirroot.'/blocks/demostudent/locallib.php');

/*
 * To function properly, this block requires a role to be created with the shortname 'demostudent'.
 * To create this role:
 * - Settings | Site administration | Users | Permissions | Define roles
 * - [Create a new role by copying Student]
 * - change 'Short name' to 'demostudent'
 * - change 'Custom full name' to 'DemoStudent'
 * - enter a 'Custom description' (Something like
 *    'Role assigned to accounts automatically created through the DemoStudent block.')
 * - ensure the 'Role archetype' is 'ARCHETYPE: Student'
 * - under 'Capability', find 'Block: DemoStudent block', and Allow 'block/demostudent:seedemostudentblock'
 * - [Create this role]
 * - Settings | Site administration | Grades | General settings
 * - ensure that 'DemoStudent' is NOT one of the 'Graded roles'
 * The role is stored in DB table mdl_role.
 *
 * Additionally, all roles which should be allowed to add DemoStudent blocks to courses must have the
 * capabilities 'block/demostudent:addinstance' and 'block/demostudent:seedemostudentblock'.
 * This should be the case by default for all roles which have archetypes of 'manager', 'coursecreator',
 * 'editingteacher', or 'teacher'.  Conversely, roles which should NOT be allowed to add the DemoStudent
 * block to courses will need to have these capabilities disabled if they have those archetypes.
 *
 * To avoid confusion, it is probably best not to allow role switching to the DemoStudent role
 * through the standard 'Settings | Switch role to...' menu, so that the DemoStudent block is
 * the single point of entry.  To do so:
 * - Settings | Site administration | Users | Permissions | Define roles
 * - On the 'Allow role switches' tab, ensure that no box is checked in the DemoStudent column
 */

class block_demostudent extends block_base {
    public function init() {
        $this->title = get_string('demostudent', 'block_demostudent');
    }

    public function get_content() {
        global $COURSE, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = var_export($this->context, true);

        $coursecontext = context_course::instance($COURSE->id);
        if (has_capability('block/demostudent:addinstance', $coursecontext)) {
            // If DemoStudent has not yet been enrolled, allow user to create/enrol one.
            $demostudentusername = generate_demostudent_name($USER->username);
            $demostudentuser = get_complete_user_data('username', $demostudentusername);
            if (!$demostudentuser || !is_enrolled($coursecontext, $demostudentuser)) {
                $this->render_view('firstuse');
            } else {
                $this->render_view('instructor');
            }
        } else if (has_capability('block/demostudent:seedemostudentblock', $coursecontext)) {
            $this->render_view('demostudent');
        } else {
            // If the user does not need to see the block, do not display it at all.
            $this->content->text = '';
            $this->content->footer = '';
        }

        return $this->content;
    }

    private function render_view($viewrole = 'demostudent') {
        global $CFG, $COURSE, $USER, $OUTPUT;

        $this->content->text = '';

        // If the demostudent role is missing from the system, give a warning.
        $demostudentroleid = get_roleid_by_roleshortname('demostudent');
        if (!$demostudentroleid) {
            // Test this by changing mdl_role.shortname from 'demostudent' to 'demostudent2' in the DB.
            $this->content->text .= get_string('warningmissingrole', 'block_demostudent');
        }

        // If the course is not available to students, give a warning to everyone who sees the block.
        if (!$COURSE->visible) {
            $this->content->text .= get_string('warningcoursenotvisible', 'block_demostudent');
        }

        $this->content->text .= get_string('viewis'.$viewrole, 'block_demostudent');

        $buttontext = get_string('buttonfor'.$viewrole, 'block_demostudent');
        $buttontooltip = get_string('switchfrom'.$viewrole.'view', 'block_demostudent');
        $buttonunenroltext = get_string('buttonforunenrol', 'block_demostudent');
        $buttonunenroltooltip = get_string('unenroltip', 'block_demostudent');

        $this->content->text .= '<div class="searchform">';
        $this->content->text .= '<form action="'.
                                $CFG->wwwroot.
                                '/blocks/demostudent/switchview.php" style="display:inline"><fieldset class="invisiblefieldset">';
        $this->content->text .= '<legend class="accesshide">'.$buttontooltip.'</legend>';
        $this->content->text .= '<input name="url" type="hidden" value="'.$this->page->url.'" />';
        $this->content->text .= '<input name="viewrole" type="hidden" value="'.$viewrole.'" />';
        $this->content->text .= '<input name="courseid" type="hidden" value="'.$this->page->course->id.'" />';
        $this->content->text .= '<input name="sesskey" type="hidden" value="'.$USER->sesskey.'" />';
        $this->content->text .= '<button id="switchview_button" class="btn btn-secondary" type="submit" title="'.
                                $buttontooltip.'">'.$buttontext.'</button><br />';
        // Somebody add some help here: $this->content->text .= $OUTPUT->help_icon('demostudent'); !
        $this->content->text .= '</fieldset></form>';
        $this->content->text .= '</div>';

        $this->content->text .= get_string('advicefor'.$viewrole, 'block_demostudent');

        if ($viewrole == 'demostudent' && $this->ismoonfull()) {
            $this->content->text .= get_string('advicetwowindows', 'block_demostudent');
        }

        if ($viewrole == 'instructor') {
            // Need to allow the instructor to unenrol the demo student, add another form.
            $this->content->text .= '<div class="searchform">';
            $this->content->text .= '<form action="'.
                                    $CFG->wwwroot.'/blocks/demostudent/remove.php" style="display:inline">';
            $this->content->text .= '<fieldset class="invisiblefieldset">';
            $this->content->text .= '<input name="viewrole" type="hidden" value="'.$viewrole.'" />';
            $this->content->text .= '<input name="courseid" type="hidden" value="'.$this->page->course->id.'" />';
            $this->content->text .= '<input name="sesskey" type="hidden" value="'.$USER->sesskey.'" />';
            $this->content->text .= '<button id="unenrol_button" class="btn btn-secondary" type="submit" title="'.
                                    $buttonunenroltooltip.'">'.$buttonunenroltext.'</button><br />';
            $this->content->text .= '</fieldset></form>';
            $this->content->text .= '</div>';
        }
    }

    // This gives a very rough approximation.
    private function ismoonfull() {
        $now = time();
        $secondssincefull = ($now - 1389847980) % 2551443; // Date of a full moon, and average lunar month.
        if (($secondssincefull < 43200) || $secondssincefull > (2551443 - 43200)) { // 12 hours either side.
            return true;
        }
        return false;
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function has_config() {
        return false;
    }

    public function html_attributes() {
        $attributes = parent::html_attributes();
        $attributes['class'] .= ' block_'. $this->name();
        return $attributes;
    }

    public function applicable_formats() {
        return array(
                       'course-view' => true,
                       'my' => false,
                       'site-index' => false,
                      );
    }
}
