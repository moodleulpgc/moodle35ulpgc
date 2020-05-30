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
 * Question behaviour for the old adaptive mode, with no penalties.
 *
 * @package    qbehaviour
 * @subpackage regexpadaptivewithhelpnopenalty
 * @copyright  2011 Joseph Rezeau
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../regexpadaptivewithhelp/behaviour.php');

/**
 * Question behaviour for regexpadaptivewithhelp mode, with no penalties.
 *
 * @copyright  2011 Joseph Rezeau
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbehaviour_regexpadaptivewithhelpnopenalty extends qbehaviour_regexpadaptivewithhelp {
    const IS_ARCHETYPAL = false;

    public static function get_required_behaviours() {
        return array('regexpadaptivewithhelp');
    }

    protected function adjusted_fraction($fraction, $prevtries, $helpnow = 0) {
        return $fraction;
    }

    public function get_help_penalty($penalty, $dp, $help) {
        return '';
    }
}
