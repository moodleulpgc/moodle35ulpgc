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
 * Time elapsed condition.
 *
 * @package availability_timeelapsed
 * @copyright 2015 Enrique Castro @ ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_timeelapsed;

defined('MOODLE_INTERNAL') || die();

/**
 * Date condition.
 *
 * @package availability_timeelapsed
 * @copyright 2015 Enrique Castro @ ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {
    /** @var string Operator: time lapse from reference field is greater (>) than value */
    const OP_GREATER = 'greater';

    /** @var string Operator: time lapse from reference field is alt least (>=) value */
    const OP_ATLEAST = 'atleast';

    /** @var string Operator: time lapse from reference field is less (<) value */
    const OP_LESS = 'less';

    /** @var string Operator: time lapse from reference field is at most (<=) with value */
    const OP_ATMOST = 'atmost';

    /** @var string Operator: time lapse from reference field equals value */
    const OP_EQUAL = 'equal';

    /** @var string Field name (for time reference) */
    protected $referencefield = '';

    /** @var string Operator type (OP_xx constant) */
    protected $operator;

    /** @var string Expected value for field */
    protected $value = '';

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {
        // Get operator.
        if (isset($structure->op) && in_array($structure->op, array(self::OP_GREATER,
                self::OP_ATLEAST, self::OP_LESS, self::OP_ATMOST, self::OP_EQUAL), true)) {
            $this->operator = $structure->op;
        } else {
            throw new \coding_exception('Missing or invalid ->op for timeelapsed condition');
        }

        // require value.
        if (isset($structure->v) && is_string($structure->v)) {
            $this->value = $structure->v;
        } else {
            throw new \coding_exception('Missing or invalid ->v for timeelapsed condition');
        }

        // Get field type.
        if (property_exists($structure, 'rf')) {
            if (is_string($structure->rf)) {
                $this->referencefield = $structure->rf;
            } else {
                throw new \coding_exception('Invalid ->rf for timeelapsed condition');
            }
        } else {
            throw new \coding_exception('Missing ->rf for timeelapsed condition');
        }
    }

    public function save() {
        $result = (object)array('type' => 'timeelapsed', 'op' => $this->operator);
        if ($this->referencefield) {
            $result->rf = $this->referencefield;
        }
        $result->v = $this->value;

        return $result;
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param string $fieldname Field name
     * @param string $operator Operator name (OP_xx constant)
     * @param string|null $value Value (not required for some operator types)
     * @return stdClass Object representing condition
     */
    public static function get_json($a, $fieldname, $operator, $value = null) {
        $result = (object)array('type' => 'timeelapsed', 'op' => $operator);
        $result->rf = $fieldname;

        if (is_null($value)) {
            throw new \coding_exception('Operator requires value');
        }
        $result->v = $value;

        return $result;
    }

    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        $course = $info->get_course();
        $userreference = $this->get_cached_user_reference_field($course);
        $allow = self::is_field_condition_met($this->operator, $userreference, $this->value);
        if ($not) {
            $allow = !$allow;
        }
        return $allow;
    }

    public function get_description($full, $not, \core_availability\info $info) {
        $course = $info->get_course();
        // Display the fieldname into current lang.

        // Standard user fields.
        $standardfields = array(
            'coursestart' => get_string('coursestart'),
            'firstaccess' => get_string('firstsiteaccess'),
            'lastaccess' => get_string('lastsiteaccess'),
            'lastcourseaccess' => get_string('lastcourseaccess', 'availability_timeelapsed'),
            'currentcourseaccess' => get_string('currentcourseaccess', 'availability_timeelapsed'),
            'lastlogin' => get_string('lastlogin', 'availability_timeelapsed'),
            'currentlogin' => get_string('currentlogin', 'availability_timeelapsed'),
        );
        
        $translatedfieldname = 'xxx';
        if($this->referencefield) {
            //$translatedfieldname = get_string($this->referencefield, 'availability_timeelapsed');
            $translatedfieldname = $standardfields[$this->referencefield];
        }

        $context = \context_course::instance($course->id);
        $a = new \stdClass();
        $a->field = format_string($translatedfieldname, true, array('context' => $context));
        $a->value = s($this->value);
        if ($not) {
            // When doing NOT strings, we replace the operator with its inverse.
            // Some of them don't have inverses, so for those we use a new
            // identifier which is only used for this lang string.
            switch($this->operator) {
                case self::OP_GREATER:
                    $opname = self::OP_ATMOST;
                    break;
                case self::OP_ATLEAST:
                    $opname = self::OP_LESS;
                    break;
                case self::OP_LESS:
                    $opname = self::OP_ATLEAST;
                    break;
                case self::OP_ATMOST:
                    $opname = self::OP_GREATER;
                    break;
                case self::OP_EQUAL:
                    $opname = 'notequal';
                    break;
                default:
                    throw new \coding_exception('Unexpected operator: ' . $this->operator);
            }
        } else {
            $opname = $this->operator;
        }
        return get_string('requires_' . $opname, 'availability_timeelapsed', $a);
    }

    protected function get_debug_string() {
        $out = $this->referencefield;
        $out .= ' ' . $this->operator;
        $out .= ' ' . $this->value;
        return $out;
    }

    /**
     * Returns true if a field meets the required conditions, false otherwise.
     *
     * @param string $operator the requirement/condition
     * @param int $userreference the user's timereference value
     * @param string $value the value required
     * @return boolean True if conditions are met
     */
    protected static function is_field_condition_met($operator, $userreference, $value) {
        global $USER;
        
        if ($userreference === false || $userreference == 0) {
            // If the user value is false this is an instant fail.
            // All user values come from the database as either data or the default.
            return false;
        }

        // Just to be doubly sure it is a string.
        $userreference = (int)$userreference;
        $reference = new \DateTime();
        $reference->setTimestamp($userreference);
        $now = new \DateTime();
        $interval = date_diff($reference, $now);
        $days = $interval->days + $interval->h/24 + $interval->i/(24*60) + $interval->s/86400;
        if($interval->invert) {
            $days = -$days;
        }

        $fieldconditionmet = false;
        switch($operator) {
            case self::OP_GREATER:
                if ($days > $value) {
                    $fieldconditionmet = true;
                }
                break;
            case self::OP_ATLEAST:
                if ($days >= $value) {
                    $fieldconditionmet = true;
                }
                break;
            case self::OP_EQUAL:
                if ($days == $value) {
                    $fieldconditionmet = true;
                }
                break;
            case self::OP_LESS:
                if ($days < $value) {
                    $fieldconditionmet = true;
                }
                break;
            case self::OP_ATMOST:
                if ($days <= $value) {
                    $fieldconditionmet = true;
                }
                break;
        }

        return $fieldconditionmet;
    }


    /**
     * Return the value for a user's reference time field
     *
     * @param int $userid User ID
     * @return string|bool Value, or false if user does not have a value for this field
     */
    protected function get_cached_user_reference_field($course) {
        global $USER, $DB, $CFG;
        if (isguestuser($USER->id) || !isloggedin() || !$this->referencefield) {
            // Must be logged in and can't be the guest.
            return false;
        }

        $value = false;

        if($this->referencefield == 'coursestart') {
            return $course->startdate;
        }

        if(!property_exists($USER, $this->referencefield)) {
            mtrace('Requested user timeelapsed field does not exist: '.$this->referencefield);
            $value = $DB->get_field('user', $this->referencefield, array('id'=>$USER->id)); // ecastro treat error in forum_cron
            if($value === false) {
                // Unknown user field. This should not happen.
                throw new coding_exception('Requested user timeelapsed field does not exist');
            } else {
                $USER->{$this->referencefield} = $value;
                return $value;
            }

        }

        if($this->referencefield == 'lastcourseaccess') {
            $value = isset($USER->lastcourseaccess[$course->id]) ? $USER->lastcourseaccess[$course->id] : 0;
        } else if($this->referencefield == 'currentcourseaccess') {
            $value = isset($USER->currentcourseaccess[$course->id]) ? $USER->currentcourseaccess[$course->id] : 0;
        } else {
            $value = $USER->{$this->referencefield};
        }

        return $value;
    }

    public function is_applied_to_user_lists() {
        // Time conditions are assumed NOT to be 'permanent', so they affect the
        // display of user lists for activities.
        return false;
    }

}
