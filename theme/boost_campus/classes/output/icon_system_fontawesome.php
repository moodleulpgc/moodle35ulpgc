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
 * Boost_campus is a clean and customizable theme.
 *
 * @package     theme_boost_campus
 * @copyright   2018 Enrique castro
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_boost_campus\output;

defined('MOODLE_INTERNAL') || die();

class icon_system_fontawesome extends \core\output\icon_system_fontawesome {

    /**
     * @var array $map Cached map of moodle icon names to font awesome icon names.
     */
    private $map = [];

    public function get_core_icon_map() {
        $iconmap = parent::get_core_icon_map();

        $iconmap['core:t/go'] = 'fa-circle text-success';
        $iconmap['core:t/stop'] = 'fa-circle text-danger';
        $iconmap['core:i/manual_item'] = 'fa-edit';
        $iconmap['core:i/completion-manual-enabled'] = 'fa-check-square-o';

        return $iconmap;
    }

}

