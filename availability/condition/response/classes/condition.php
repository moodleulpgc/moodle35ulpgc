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
 * @package availability_response
 * @copyright 2015 Enrique Castro @ ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_response;

defined('MOODLE_INTERNAL') || die();

/**
 * Date condition.
 *
 * @package availability_response
 * @copyright 2015 Enrique Castro @ ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {
    /** @var string Operator: field contains value */
    const OP_CONTAINS = 'contains';

    /** @var string Operator: field does not contain value */
    const OP_DOES_NOT_CONTAIN = 'doesnotcontain';

    /** @var string Operator: field equals value */
    const OP_IS_EQUAL_TO = 'isequalto';

    /** @var string Operator: field starts with value */
    const OP_STARTS_WITH = 'startswith';

    /** @var string Operator: field ends with value */
    const OP_ENDS_WITH = 'endswith';

    /** @var string choice instance ID (for choice reference) */
    protected $choiceinstance = '';

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
        if (isset($structure->op) && in_array($structure->op, array(self::OP_CONTAINS,
                self::OP_DOES_NOT_CONTAIN, self::OP_IS_EQUAL_TO, self::OP_STARTS_WITH,
                self::OP_ENDS_WITH), true)) {
            $this->operator = $structure->op;
        } else {
            throw new \coding_exception('Missing or invalid ->op for response condition');
        }

        // require value.
        if (isset($structure->v) && is_string($structure->v)) {
            $this->value = $structure->v;
        } else {
            throw new \coding_exception('Missing or invalid ->v for response condition');
        }

        // Get field type.
        if (property_exists($structure, 'rf')) {
            if (is_string($structure->rf)) {
                $this->choiceinstance = $structure->rf;
            } else {
                throw new \coding_exception('Invalid ->rf for response condition');
            }
        } else {
            throw new \coding_exception('Missing ->rf for response condition');
        }
    }

    public function save() {
        $result = (object)array('type' => 'response', 'op' => $this->operator);
        if ($this->choiceinstance) {
            $result->rf = $this->choiceinstance;
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
        $result = (object)array('type' => 'response', 'op' => $operator);
        $result->rf = $fieldname;

        if (is_null($value)) {
            throw new \coding_exception('Operator requires value');
        }
        $result->v = $value;

        return $result;
    }

    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        $uservalue = $this->get_user_choice_answer($userid);
        $allow = self::is_field_condition_met($this->operator, $uservalue, $this->value);
        if ($not) {
            $allow = !$allow;
        }
        return $allow;
    }

    public function get_choice_instance() {
        global $DB;
        return $DB->get_record('choice', array('id'=>$this->choiceinstance));
    }

    public function get_description($full, $not, \core_availability\info $info) {
        $course = $info->get_course();
        // Display the fieldname into current lang.

        $instance = $this->get_choice_instance();

        $context = \context_course::instance($course->id);
        $a = new \stdClass();
        $a->field = format_string($instance->name, true, array('context' => $context));
        $a->value = s($this->value);
        if ($not) {
            // When doing NOT strings, we replace the operator with its inverse.
            // Some of them don't have inverses, so for those we use a new
            // identifier which is only used for this lang string.
            switch($this->operator) {
                case self::OP_CONTAINS:
                    $opname = self::OP_DOES_NOT_CONTAIN;
                    break;
                case self::OP_DOES_NOT_CONTAIN:
                    $opname = self::OP_CONTAINS;
                    break;
                case self::OP_ENDS_WITH:
                    $opname = 'notendswith';
                    break;
                case self::OP_IS_EQUAL_TO:
                    $opname = 'notisequalto';
                    break;
                case self::OP_STARTS_WITH:
                    $opname = 'notstartswith';
                    break;
                default:
                    throw new \coding_exception('Unexpected operator: ' . $this->operator);
            }

        } else {
            $opname = $this->operator;
        }

        return get_string('requires_' . $opname, 'availability_response', $a);
    }

    protected function get_debug_string() {
        $out = $this->choiceinstance;
        $out .= ' ' . $this->operator;
        $out .= ' ' . $this->value;
        return $out;
    }

    /**
     * Returns true if a field meets the required conditions, false otherwise.
     *
     * @param string $operator the requirement/condition
     * @param int $uservalue the user's timereference value
     * @param string $value the value required
     * @return boolean True if conditions are met
     */
    protected static function is_field_condition_met($operator, $uservalue, $value) {
        if ($uservalue === false) {
            // If the user value is false this is an instant fail.
            // All user values come from the database as either data or the default.
            return false;
        }

        // Just to be doubly sure it is a string.
        $uservalue = (string)$uservalue;
        $fieldconditionmet = true;
        // Just to be doubly sure it is a string.
        $uservalue = (string)$uservalue;
        switch($operator) {
            case self::OP_CONTAINS:
                $pos = strpos($uservalue, $value);
                if ($pos === false) {
                    $fieldconditionmet = false;
                }
                break;
            case self::OP_DOES_NOT_CONTAIN:
                if (!empty($value)) {
                    $pos = strpos($uservalue, $value);
                    if ($pos !== false) {
                        $fieldconditionmet = false;
                    }
                }
                break;
            case self::OP_IS_EQUAL_TO:
                if ($value !== $uservalue) {
                    $fieldconditionmet = false;
                }
                break;
            case self::OP_STARTS_WITH:
                $length = strlen($value);
                if ((substr($uservalue, 0, $length) !== $value)) {
                    $fieldconditionmet = false;
                }
                break;
            case self::OP_ENDS_WITH:
                $length = strlen($value);
                $start = $length * -1;
                if (substr($uservalue, $start) !== $value) {
                    $fieldconditionmet = false;
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
    protected function get_user_choice_answer($userid) {
        global $DB;
        if (isguestuser($userid) || !isloggedin() || !$this->choiceinstance) {
            // Must be logged in and can't be the guest.
            return false;
        }

        $value = false;

        $sql = "SELECT co.text
                FROM {choice_answers} ca
                JOIN {choice_options} co ON co.id = ca.optionid AND co.choiceid = ca.choiceid
                WHERE ca.userid = :user AND ca.choiceid = :choice ";
        $value = $DB->get_field_sql($sql, array('user'=>$userid, 'choice'=>$this->choiceinstance));

        return $value;
    }

    public function is_applied_to_user_lists() {
        return false;
    }
}