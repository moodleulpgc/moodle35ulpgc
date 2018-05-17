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
 * MASKS module version information
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2017050103;        // The current module version (Date: YYYYMMDDXX)
$plugin->requires  = 2014050800;        // Requires this Moodle version
$plugin->component = 'mod_masks';       // Full name of the plugin (used for diagnostics)
$plugin->cron      = 1;                 // Don't limit the frequency at which the CRON gets called (it will look after it for itself)
$plugin->release   = '2.7.007';         // Mask-specific Versiuon number
$plugin->maturity  = MATURITY_STABLE;   // This version's maturity level.
