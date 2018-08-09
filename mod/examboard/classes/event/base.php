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
 * Plugin event classes are defined here.
 *
 * @package     mod_examboard
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_examboard\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The base event class.
 *
 * @package    mod_examboard
 * @copyright  2017 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'examboard';
    }

    /**
     * Returns the name of an language string for this event
     *
     * @param string $suffix (optional, default="")
     * @return string
     */
    public static function get_event_string_name($suffix='') {
        $class = get_called_class();
        $class = substr($class, strlen(__NAMESPACE__) + 1);
        return 'event_'.$class.$suffix;
    }

    /**
     * Returns localised event name
     *
     * @return string
     */
    public static function get_name() {
        return get_string(self::get_event_string_name(), 'examboard');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        if ($this->contextlevel==CONTEXT_MODULE) {
            $cmid = $this->contextinstanceid;
        } else {
            $cmid = 0; // shouldn't happen !!
        }
        $a = (object)array('courseid'      => $this->courseid,
                           'cmid'          => $cmid,
                           'objectid'      => $this->objectid,
                           'objecttable'   => $this->objecttable,
                           'userid'        => $this->userid,
                           'relateduserid' => $this->relateduserid,
                           'other'         => $this->other);
        return get_string(self::get_event_string_name('_desc'), 'examboard', $a);
    }

    /**
     * Create instance of event.
     *
     * @param array $data common eventparams
     * @param \stdClass $object
     * @return feedback_viewed
     */
    public static function create_from_object($event, $object) {
        $data = array(
            'objectid' => $object->id,
            'other' => array(
                'examboardid' => $object->examboardid,
            ),
        );

        $event = self::create(array_merge($event, $data));
        return $event;
    }
   
    
    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        $url = new \moodle_url('/mod/examboard/view.php', array('id' => $this->contextinstanceid));
        
        $params = $this->set_url_params();
        $url->params($params);
        return $url;
    }

    /**
     * Returns relevant UR params arrayL.
     *
     * @return array
     */
    public function set_url_params() {
        $params = array();
        return $params;
    }
    
    /**
     * Sets the legacy event log data.
     *
     * @param string $action The current action
     * @param string $info A detailed description of the change. But no more than 255 characters.
     * @param string $url The url to the examboard module instance.
     */
    public function set_legacy_logdata($action = '', $info = '', $url = '') {
        $fullurl = 'view.php?id=' . $this->contextinstanceid;
        if ($url != '') {
            $fullurl .= '&' . $url;
        }

        $this->legacylogdata = array($this->courseid, 'examboard', $action, $fullurl, $info, $this->contextinstanceid);
    }

    /**
     * Return legacy data for add_to_log().
     *
     * @return array
     */
    protected function get_legacy_logdata() {
        if (isset($this->legacylogdata)) {
            return $this->legacylogdata;
        }

        return null;
    }

    /**
     * Legacy event data if get_legacy_eventname() is not empty.
     *
     * @return mixed
     */
    protected function get_legacy_eventdata() {
        global $USER;
        $eventdata = (object)array('user' => $USER);
        foreach ($this->get_legacy_records() as $record) {
            list($name, $table, $id) = $record;
            $eventdata->$name = $this->get_record_snapshot($table, $id);
        }
        return $eventdata;
    }
    
   /**
     * Records required by get_legacy_eventdata
     *
     * @return array(array($name, $table, $id), ...)
     */
    protected function get_legacy_records() {
        return array(array('course', 'course',         $this->courseid),
                     array('cm',     'course_modules', $this->contextinstanceid),
                     array('examboard', 'examboard',   $this->objectid));
    }
    
    
    /**
     * Custom validation.
     *
     * @throws \coding_exception
     */
    protected function validate_data() {
        parent::validate_data();

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }


}
