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
class base extends \mod_assign\event\base {
    /**
     * Flag for prevention of direct create() call.
     * @var bool
     */
    protected static $preventcreatecall = true;

    /**
     * Create instance of event.
     *
     * @param \assign $assign
     * @return assignfeedback_copyset
     */
    public static function create_from_assign(\assign $assign, $related = false) {
        $data = array(
            'context' => $assign->get_context(),
            'other' => array(
                'assignid' => $assign->get_instance()->id,
                'related' => $related,
            ),
        );
        self::$preventcreatecall = false;
        /** @var assignfeedback_copyset $event */
        $event = self::create($data);
        self::$preventcreatecall = true;
        $event->set_assign($assign);
        return $event;
    }

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }
    
    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/assign/view.php', array('id' => $this->contextinstanceid, 'action'=>'grading'));
    }

    /**
     * Return legacy data for add_to_log().
     *
     * @return array
     */
    protected function get_legacy_logdata() {
        $logmessage = get_string('eventcopyset', 'assignfeedback_copyset');
        $this->set_legacy_logdata('view submission grading table', $logmessage);
        return parent::get_legacy_logdata();
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        if (self::$preventcreatecall) {
            throw new \coding_exception('cannot call assignfeedback_copyset\event\base::create() directly, use base::create_from_assign() method instead.');
        }

        parent::validate_data();

        if (!isset($this->other['assignid'])) {
            throw new \coding_exception('The \'assignid\' value must be set in other.');
        }
    }
}
