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
 * Course section updated event for topicgroup custom course format.
 *
 * @package format_topicgroup
 * @copyright 2015 E. Castro (ULPGC)
 * @author Enrique Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_topicgroup\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Course section updated event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int sectionnum: section number.
 * }
 *
 * @package format_topicgroup
 * @copyright 2015 E. Castro (ULPGC)
 * @author Enrique Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_section_updated extends \core\event\base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['objecttable'] = 'format_topicgroup_sections';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventcoursesectionupdated', 'format_topicgroup');
    }

    /**
     * Create instance of event.
     *
     * @param stdClass $section record form format_topicgroup_sections table
     * @return assignfeedback_historic
     */
    public static function create_from_section($section, \context_course $context, $sectionnum) {
        $data = array(
            'objectid' => $section->id,
            'context' => $context,
            'courseid'=> $section->course,
            'other' => array(
                'sectionid' => $section->section,
                'groupingid' => $section->groupingid,
                'sectionnum' => $sectionnum,
            ),
        );
        /** @var format_topicgroup\ $event */
        $event = self::create($data);
        $event->add_record_snapshot('format_topicgroup_sections', $section);
        return $event;
    }


    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        $gropupingid = $this->other['groupingid'];
        if($gropupingid) {
            $message = "The user with id '$this->userid' locked the section '{$this->other['sectionnum']}' ".
                        "for the grouping {$this->other['groupingid']}. ";
        } else {
            $message = "The user with id '$this->userid' released locked section '{$this->other['sectionnum']}'. ";
        }
        return $message. "in the course course with id '$this->courseid'";
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/course/format/topicgroup/setgrouping.php', array('id' => $this->other['sectionid']));
    }

    /**
     * Return legacy data for add_to_log().
     *
     * @return array
     */
    protected function get_legacy_logdata() {
        $sectiondata = $this->get_record_snapshot('course_sections', $this->objectid);
        return array($this->courseid, 'course', 'editsection', 'editsection.php?id=' . $this->objectid, $sectiondata->section);
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['sectionnum'])) {
            throw new \coding_exception('The \'sectionnum\' value must be set in other.');
        }
    }
}
