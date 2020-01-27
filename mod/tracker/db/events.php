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
 * Forum event handler definition.
 *
 * @package mod_tracker
 * @category event
 * @copyright 2019 Enrique Castro @ ULPGC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// List of observers.
$observers = array(

    array(
        'eventname' => '\core\event\course_created',
        'callback'  => 'mod_tracker_observer::course_created',
    ),

    array(
        'eventname' => '\core\event\course_restored',
        'callback'  => 'mod_tracker_observer::course_restored',
    ),

    array(
        'eventname' => '\core\event\course_updated',
        'callback'  => 'mod_tracker_observer::course_updated',
    ),
    
    array(
        'eventname' => '\core\event\course_deleted',
        'callback'  => 'mod_tracker_observer::course_deleted',
    ),

    array(
        'eventname' => '\core\event\course_category_created',
        'callback'  => 'mod_tracker_observer::course_category_created',
    ),

    array(
        'eventname' => '\core\event\course_category_updated',
        'callback'  => 'mod_tracker_observer::course_category_updated',
    ),
    
    array(
        'eventname' => '\core\event\course_category_deleted',
        'callback'  => 'mod_tracker_observer::course_category_deleted',
    ),
    
    array(
        'eventname' => '\core\event\role_assigned',
        'callback' => 'mod_tracker_observer::role_assigned'
    ),
    
    array(
        'eventname' => '\core\event\role_unassigned',
        'callback' => 'mod_tracker_observer::role_unassigned'
    ),

    array(
        'eventname' => '\core\event\group_created',
        'callback'  => 'mod_tracker_observer::group_created',
    ),
    
    array(
        'eventname' => '\core\event\group_updated',
        'callback'  => 'mod_tracker_observer::group_updated',
    ),    
    
    array(
        'eventname' => '\core\event\group_deleted',
        'callback'  => 'mod_tracker_observer::group_deleted',
    ),    
    
    array(
        'eventname' => '\core\event\group_member_added',
        'callback'  => 'mod_tracker_observer::group_member_added',
    ),    
    
    array(
        'eventname' => '\core\event\group_member_removed',
        'callback'  => 'mod_tracker_observer::group_member_removed',
    ),    
    
    array(
        'eventname' => '\core\event\grouping_created',
        'callback'  => 'mod_tracker_observer::grouping_created',
    ),
    
    array(
        'eventname' => '\core\event\grouping_updated',
        'callback'  => 'mod_tracker_observer::grouping_updated',
    ),    
    
    array(
        'eventname' => '\core\event\grouping_deleted',
        'callback'  => 'mod_tracker_observer::grouping_deleted',
    ),    
    
    array(
        'eventname' => '\core\event\grouping_group_assigned',
        'callback'  => 'mod_tracker_observer::grouping_group_assigned',
    ),    
    
    array(
        'eventname' => '\core\event\grouping_group_unassigned',
        'callback'  => 'mod_tracker_observer::grouping_group_unassigned',
    ),      

);
