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
 * Section restriction & unlocking to a grouping
 *
 * @package   format_topicgroup
 * @copyright 2013 E. Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../../config.php");
require_once($CFG->dirroot. '/course/format/lib.php');
require_once($CFG->libdir.'/formslib.php');

/**
 * This class define form for set/change grouping association
 *
 */
class format_topicgroup_setgrouping_form extends moodleform {
    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;
        $thissection = $this->_customdata['section'];
        $course = $this->_customdata['course'];

        $groupings = array();
        $groupings[0] = get_string('choose');
        if ($cgroupings = groups_get_all_groupings($thissection->course)) {
            foreach ($cgroupings as $grouping) {
                $groupings[$grouping->id] = format_string($grouping->name);
            }
        }

        $sectionname = get_section_name($course, $thissection->section);

        $mform->addElement('header', 'settings', get_string('setsettings', 'format_topicgroup'));
        $mform->addElement('static', 'currentsection', get_string('currentsection', 'format_topicgroup'), $sectionname); //  get_string('modsettings', 'format_topicgroup')
        if($thissection->groupingid) {
            $groupingname = $groupings[$thissection->groupingid];
        } else {
            $groupingname = get_string('none');
        }
        $mform->addElement('static', 'currentgrouping', get_string('currentgrouping', 'format_topicgroup'), $groupingname); //  get_string('modsettings', 'format_topicgroup')

        $mform->addElement('select', 'grouping', get_string('grouping', 'format_topicgroup'), $groupings);
        $mform->setDefault('grouping', $thissection->groupingid);
        $mform->setType('grouping', PARAM_INT);
        $mform->addRule('grouping', null, 'required');

        $mform->addElement('selectyesno', 'applyother', get_string('applyother', 'format_topicgroup'));
        $mform->setDefault('applyother', 0);
        $mform->addHelpButton('applyother', 'applyother', 'format_topicgroup');

        $groupmodes = array(-1 => get_string('keepgroupmode', 'format_topicgroup'),
                             NOGROUPS       => get_string('groupsnone'),
                             SEPARATEGROUPS => get_string('groupsseparate'),
                             VISIBLEGROUPS  => get_string('groupsvisible'));

        $mform->addElement('select', 'groupmode', get_string('groupmode', 'format_topicgroup'), $groupmodes);
        $mform->setDefault('groupmode', -1);
        $mform->setType('groupmode', PARAM_INT);
        $mform->addHelpButton('groupmode', 'groupmode', 'format_topicgroup');

        $mform->addElement('hidden', 'id', $thissection->id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'unset', 0);
        $mform->setType('unset', PARAM_INT);

        $mform->addElement('hidden', 'process', 'confirmed');
        $mform->setType('process', PARAM_TEXT);

        $this->add_action_buttons(true);
    }
}

/**
 * This class define form for unlocking grouping association
 *
 */
class format_topicgroup_unsetgrouping_form extends moodleform {
    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;
        $thissection = $this->_customdata['section'];
        $course = $this->_customdata['course'];

        $groupings = array();
        $groupings[0] = get_string('choose');
        if ($cgroupings = groups_get_all_groupings($thissection->course)) {
            foreach ($cgroupings as $grouping) {
                $groupings[$grouping->id] = format_string($grouping->name);
            }
        }
        $sectionname = get_section_name($course, $thissection->section);

        $mform->addElement('header', 'settings', get_string('setsettings', 'format_topicgroup'));
        $mform->addElement('static', 'currentsection', get_string('currentsection', 'format_topicgroup'), $sectionname); //  get_string('modsettings', 'format_topicgroup')
        if($thissection->groupingid) {
            $groupingname = $groupings[$thissection->groupingid];
        } else {
            $groupingname = get_string('none');
        }
        $mform->addElement('static', 'currentgrouping', get_string('currentgrouping', 'format_topicgroup'), $groupingname); //  get_string('modsettings', 'format_topicgroup')

        $mform->addElement('selectyesno', 'applyall', get_string('applyall', 'format_topicgroup'));
        $mform->setDefault('applyall', 0);
        $mform->addHelpButton('applyall', 'applyall', 'format_topicgroup');

        $groupmodes = array(-1 => get_string('keepgroupmode', 'format_topicgroup'),
                             NOGROUPS       => get_string('groupsnone'),
                             SEPARATEGROUPS => get_string('groupsseparate'),
                             VISIBLEGROUPS  => get_string('groupsvisible'));

        $mform->addElement('select', 'groupmode', get_string('groupmode', 'format_topicgroup'), $groupmodes);
        $mform->setDefault('groupmode', -1);
        $mform->setType('groupmode', PARAM_INT);
        $mform->addHelpButton('groupmode', 'groupmode', 'format_topicgroup');

        $mform->addElement('hidden', 'id', $thissection->id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'unset', 1);
        $mform->setType('unset', PARAM_INT);

        $mform->addElement('hidden', 'process', 'confirmed');
        $mform->setType('process', PARAM_TEXT);

        $this->add_action_buttons(true);
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////


$id = required_param('id', PARAM_INT);
$unset = optional_param('unset', 0, PARAM_INT);

if (! $section = $DB->get_record("course_sections", array('id'=>$id))) {
    print_error("Course section is incorrect");
}
if (! $course = $DB->get_record("course", array('id'=>$section->course))) {
    print_error("Could not find the course!");
}

$context = context_course::instance($course->id);
$PAGE->set_context($context);
require_login($course);
require_capability('moodle/course:managegroups', $context);

$returnurl = new moodle_url('/course/view.php', array('id'=>$course->id));

/*
$section->groupingid = 0;
if($grouping = $DB->get_field('format_topicgroup_sections', 'groupingid', array('course'=>$section->course, 'section'=>$section->id))) {
    $section->groupingid = $grouping;
}
*/

$tgsection = format_topicgroup_getset_grouping($section, true);

if (($formdata = data_submitted()) && confirm_sesskey()) {
    if(isset($formdata->cancel)) {
        redirect($returnurl, '', 0);
    }

    $multiple = false;
    if($formdata->unset) {
        $groupingid = 0;
        if($formdata->applyall && $section->groupingid) {
            $multiple = true;
        }
    } else {
        $groupingid = $formdata->grouping;
        if($formdata->applyother && $section->groupingid) {
            $multiple = true;
        }
    }

    if($multiple) {
        $sql = "SELECT tg.*, cs.section AS section
                    FROM {format_topicgroup_sections} tg
                    JOIN {course_sections} cs ON cs.id = tg.section AND cs.course = tg.course
                WHERE tg.course = :course AND tg.groupingid = :groupingid ";
        $tgsections = $DB->get_records_sql($sql, array('course'=>$tgsection->course, 'groupingid'=>$tgsection->groupingid));
    } else {
        $tgsections = array($section->id=>$tgsection);
    }

    $groupmode = false;
    if($formdata->groupmode > -1) {
        $groupmode = $formdata->groupmode;
    }

    $message = '';
    $now = time();
    if($tgsections) {
        foreach($tgsections as $tgsection) {
            $tgsection->groupingid = $groupingid;
            $tgsection->timemodified = $now;
            if($success = $DB->update_record('format_topicgroup_sections', $tgsection)) {
                format_topicgroup_section_restrictions($tgsection, $groupmode);

                $event = \format_topicgroup\event\course_section_updated::create_from_section($tgsection, $context, $tgsection->section);
                $event->trigger();
            } else {
                $sectionname = get_section_name($course, $tgsection->section);
                $message .= '<br />'.get_string('setgroupingerror', 'format_topicgroup', $sectionname);
            }
        }
    }


    rebuild_course_cache($course->id);

    if(!$message) {
       $message = get_string('changessaved');
    }
    redirect($returnurl, $message);
}

if($unset) {
    $mform = new format_topicgroup_unsetgrouping_form(null, array('section'=>$section, 'course'=>$course));
    $mode = 'unsetgrouping';
} else {
    $mform = new format_topicgroup_setgrouping_form(null, array('section'=>$section, 'course'=>$course));
    $mode = 'setgrouping';
}

$pagetitle = $course->fullname . ': '. get_string($mode, 'format_topicgroup');
$PAGE->set_url('/course/format/topicgroup/setgrouping.php', array('id'=>$id));
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$PAGE->navbar->add(get_string('pluginname', 'format_topicgroup'));
$PAGE->navbar->add(get_string('managerestrictions', 'format_topicgroup'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string($mode, 'format_topicgroup'));

$mform->display();
echo $OUTPUT->footer();


