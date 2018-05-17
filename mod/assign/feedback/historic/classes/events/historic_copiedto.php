<?php

/**
 * The assignfeedback_historic event.
 *
 * @package        assignfeedback_historic
 * @author         Enrique Castro <enrique.castro@ulpgc.es>
 * @copyright  (c) Enrique Castro ULPGC
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignfeedback_historic\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The ssignfeedback_copyset base event class.
 *
 * @copyright  (c) Enrique Castro ULPGC
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class historic_copiedto extends base {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $otherassign = ' other assignment.';
        if($this->other['related']) {
            $otherassign = "assignment with course module id '".$this->other['related']."'. ";
        }

        $otherassign = $this->other['related'];
        return "The user with id '$this->userid' has Copied grades from Historic with course module id '$this->contextinstanceid' to ".$otherassign;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventgradescopied', 'assignfeedback_historic');
    }

}
