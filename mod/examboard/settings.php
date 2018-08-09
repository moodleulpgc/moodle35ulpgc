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
 * Plugin administration pages are defined here.
 *
 * @package     mod_examboard
 * @category    admin
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $modules = new admin_setting_configmultiselect_modules('examboard/gradeables', 
                                                        get_string('gradeablemods', 'examboard'), 
                                                        get_string('gradeablemods_help', 'examboard'), 
                                                        array('assign'));
    $modules->load_choices();
    
    $settings->add($modules);

    $discharges = array();
    foreach(array('holidays','illness', 'study', 'service', 'leave','maternal','congress', 'other', 'other1', 'other2', 'other3') as $motive) {                                        
        $discharges[$motive] = get_string('discharge_'.$motive, 'examboard');
    }
    $settings->add(new admin_setting_configmultiselect('examboard/discharges', 
                                                        get_string('discharges', 'examboard'), 
                                                        get_string('discharges_help', 'examboard'), 
                                                        array('holidays','illness', 'study', 'service', 'leave','maternal','congress', 'other'),
                                                        $discharges));
                                                        
                                                        
                                                        
}
