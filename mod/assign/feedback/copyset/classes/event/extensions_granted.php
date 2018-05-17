<?php

/**
 * The assignfeedback_copyset event.
 *
 * @package        assignfeedback_copyset
 * @author         Enrique Castro <enrique.castro@ulpgc.es>
 * @copyright  (c) Enrique Castro ULPGC
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignfeedback_copyset\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The ssignfeedback_copyset base event class.
 *
 * @copyright  (c) Enrique Castro ULPGC
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class extensions_granted extends base {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $todate = '';
        if($this->other['related']) {
            $todate = "until '".userdate($this->other['related'])."'";
        }
        return "The user with id '$this->userid' has granted an extension $todate for multiple users " .
                "in the assignment activity with the course module id '$this->contextinstanceid'. ";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventextensionsgranted', 'assignfeedback_copyset');
    }

}
