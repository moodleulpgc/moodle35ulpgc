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
 * Definition of log events for examregistrar module
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $DB;

$logs = array(
    array('module'=>'examregistrar', 'action'=>'add', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'update', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'view', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'view all', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'delete mod', 'mtable'=>'examregistrar', 'field'=>'name'),    
    array('module'=>'examregistrar', 'action'=>'add element', 'mtable'=>'examregistrar', 'field'=>'name'),   
    array('module'=>'examregistrar', 'action'=>'update element', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'delete element', 'mtable'=>'examregistrar', 'field'=>'name'), 
    array('module'=>'examregistrar', 'action'=>'add exam period', 'mtable'=>'examregistrar', 'field'=>'name'),   
    array('module'=>'examregistrar', 'action'=>'update exam period', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'delete exam period', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'add exam', 'mtable'=>'examregistrar', 'field'=>'name'),   
    array('module'=>'examregistrar', 'action'=>'update exam', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'delete exam', 'mtable'=>'examregistrar', 'field'=>'name'), 
    array('module'=>'examregistrar', 'action'=>'add exam file', 'mtable'=>'examregistrar', 'field'=>'name'),   
    array('module'=>'examregistrar', 'action'=>'update exam file', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'delete exam file', 'mtable'=>'examregistrar', 'field'=>'name'), 
    array('module'=>'examregistrar', 'action'=>'review exam file', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'confirm exam file', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'reject exam file', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'view exam review', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'view exam selection', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'select exam seat', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'select exam seat other', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'add location', 'mtable'=>'examregistrar', 'field'=>'name'),   
    array('module'=>'examregistrar', 'action'=>'update locationt', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'delete location', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'assign location', 'mtable'=>'examregistrar', 'field'=>'name'),
    array('module'=>'examregistrar', 'action'=>'download exam file', 'mtable'=>'examregistrar', 'field'=>'name')

);
