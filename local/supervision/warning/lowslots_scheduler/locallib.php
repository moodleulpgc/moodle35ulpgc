<?php

/**
 * Definition of warning_lowslots_scheduler, a subclass supervision warning class
 *
 * @package   warning_lowslots_scheduler
 * @package   local_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_supervision;
 
defined('MOODLE_INTERNAL') || die();

//require_once($CFG->dirroot.'/lib/statslib.php');

/**
 * An object that holds methods and attributes of warning_lowslots_scheduler class
 * Works together with supervision_warnings table
 *
 * @package   warning_lowslots_scheduler
 * @package   local_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class warning_lowslots_scheduler extends warning {

    /**
     * Constructor. Optionally attempts to fetch corresponding row from the database
     *
     * @param int/objet/array $warning id field in the supervision_warnings table
     *                             or and object or array containing the relevant fields
     */
    public function __construct($warning=NULL) {
        global $DB;

        $this->id = 0;
        parent::__construct($warning);
        $this->module = 'scheduler';
        $this->warningtype = 'lowslots_scheduler';
    }

    /**
     * Called by cron to review tables for undone/pending activities that should raise a warning
     *
     * @static
     * @abstract
     * @param int $timetocheck starting time for collection
     */
    public static function get_stats($timetocheck) {
        global $DB;

        $warningconfig = get_config('supervisionwarning_lowslots_scheduler');
        $config = get_config('local_supervision');
        if(!$config->enablestats || !$warningconfig->enabled) {
            return;
        }

        $moduleid = $DB->get_field('modules', 'id', array('name'=>'scheduler'));

        return; /// UNCONDITIONAL for the moment
    }

}

