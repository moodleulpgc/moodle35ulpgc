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
 * @package    mod_examregistrar
 * @subpackage backup-moodle2
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_examregistrar_activity_task
 */

/**
 * Define the complete examregistrar structure for backup, with file and id annotations
 */
class backup_examregistrar_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
        $userinfo = false; // TODO //TODO TODO
        $registrar_included = false;
        if($this->setting_exists('registrarincluded')) {
            $registrar_included = $this->get_setting_value('registrarincluded');
        }
        $exams_included = false;
        if($this->setting_exists('examsincluded')) {
            $exams_included = $this->get_setting_value('examsincluded');
        }

        // Define each element separated
        $examregistrar = new backup_nested_element('examregistrar', array('id'), array(
            'name', 'intro', 'introformat', 'primaryreg', 'primaryidnumber', 'annuality', 'programme',
            'workmode', 'reviewmod', 'lagdays', 'configdata', 'timecreated', 'timemodified'));

        $elements = new backup_nested_element('elements');

        $element = new backup_nested_element('element', array('id'), array(
            'type', 'name', 'idnumber', 'value', 'visible', 'modifierid', 'timemodified'));

        $periods = new backup_nested_element('periods');

        $period = new backup_nested_element('period', array('id'), array(
            'period', 'annuality', 'periodtype', 'term', 'calls',
            'timestart', 'timeend', 'visible', 'modifierid', 'timemodified'));

        $sessions = new backup_nested_element('examsessions');

        $session = new backup_nested_element('examsession', array('id'), array(
            'examsession', 'period', 'examdate', 'duration', 'timeslot',
            'component', 'modifierid', 'timemodified'));

        $exams = new backup_nested_element('exams');

        $exam = new backup_nested_element('exam', array('id'), array(
            'annuality', 'programme', 'courseid', 'period',
            'examscope', 'callnum', 'examsession','visible', 'assignplugincm', 'component', 'modifierid', 'timemodified'));

        $examfiles = new backup_nested_element('examfiles');

        $examfile = new backup_nested_element('examfile', array('id'), array(
            'examid', 'status', 'attempt', 'component', 'modifierid', 'timemodified'));

        $locations = new backup_nested_element('locations');

        $location = new backup_nested_element('location', array('id'), array(
            'location', 'locationtype', 'address', 'addressformat', 'seats',
            'visible', 'parent', 'depth', 'path', 'sortorder', 'component', 'modifierid', 'timemodified'));

        $staffers = new backup_nested_element('staffers');

        $staffer = new backup_nested_element('staffer', array('id'), array(
            'examsession', 'locationid', 'userid', 'role', 'info', 'visible', 'component', 'modifierid', 'timemodified'));

        $bookings = new backup_nested_element('bookings');

        $booking = new backup_nested_element('booking', array('id'), array(
            'examid', 'userid', 'booked', 'bookedsite', 'modifierid', 'timemodified'));
            
        $vouchers = new backup_nested_element('vouchers');

        $voucher = new backup_nested_element('voucher', array('id'), array(
            'examregid', 'bookingid', 'uniqueid', 'timemodified'));

        $rooms = new backup_nested_element('session_rooms');

        $room = new backup_nested_element('session_room', array('id'), array(
            'examsession', 'bookedsite', 'roomid', 'available', 'modifierid', 'timemodified'));

        $rules = new backup_nested_element('seating_rules');

        $rule = new backup_nested_element('seating_rule', array('id'), array(
            'examsession', 'bookedsite', 'examid', 'roomid', 'sortorder', 'modifierid', 'timemodified'));

        $seatings = new backup_nested_element('session_seats');

        $seat = new backup_nested_element('session_seat', array('id'), array(
            'examsession', 'bookedsite', 'examid', 'userid', 'roomid', 'additional', 'seat', 'timecreated', 'component',  'modifierid', 'timemodified'));

        $printings = new backup_nested_element('printings');

        $printing = new backup_nested_element('printing', array('id'), array(
            'page', 'element', 'content', 'contentformat', 'visible', 'modifierid', 'timemodified'));


        // Build the tree
        $examregistrar->add_child($elements);
        $elements->add_child($element);

        $examregistrar->add_child($periods);
        $periods->add_child($period);

        $examregistrar->add_child($sessions);
        $sessions->add_child($session);

        $examregistrar->add_child($exams);
        $exams->add_child($exam);

        $examregistrar->add_child($examfiles);
        $examfiles->add_child($examfile);

        $examregistrar->add_child($locations);
        $locations->add_child($location);

        /// TODO review tree childs nested tables????

        $examregistrar->add_child($staffers);
        $staffers->add_child($staffer);

        $examregistrar->add_child($bookings);
        $bookings->add_child($booking);

        $booking->add_child($vouchers);
        $vouchers->add_child($voucher);
        
        $examregistrar->add_child($rooms);
        $rooms->add_child($room);

        $examregistrar->add_child($rules);
        $rules->add_child($rule);

        $examregistrar->add_child($seatings);
        $seatings->add_child($seat);

        $examregistrar->add_child($printings);
        $printings->add_child($printing);

        // Define sources
        $examregistrar->set_source_table('examregistrar', array('id' => backup::VAR_ACTIVITYID));

        if($registrar_included) {

            /// TODO   add proper tables TODO


            $element->set_source_sql('
                                    SELECT *
                                    FROM {examregistrar_elements}
                                    WHERE 1',
                                array());

            $period->set_source_sql('
                                    SELECT *
                                    FROM {examregistrar_periods}
                                    WHERE ?',
                                array('../../annuality'));

            $location->set_source_sql('
                                    SELECT *
                                    FROM {examregistrar_locations}
                                    WHERE 1',
                                array());

            if($userinfo) {
                $staffer->set_source_table('examregistrar_staffers', array('locationid' => backup::VAR_PARENTID));
                if($exams_included) {
                    $examination->set_source_table('examregistrar_exams', array('period' => backup::VAR_PARENTID));
                    $examsfile->set_source_table('examregistrar_examfiles', array('examid' => backup::VAR_PARENTID));
                    $booking->set_source_table('examregistrar_booking', array('examid' => backup::VAR_PARENTID));
                    $booking->set_source_table('examregistrar_vouchers', array('bookingid' => backup::VAR_PARENTID));
                    $seat->set_source_table('examregistrar_seatings', array('examid' => backup::VAR_PARENTID));
                }
            }
        }

        // Define id annotations
        /// TODO   TODO   more annotations user & module TODO    TODO
        $staffer->annotate_ids('user', 'userid');
        $staffer->annotate_ids('user', 'modifierid');

        $exam->annotate_ids('user', 'modifierid');

        $examfile->annotate_ids('user', 'modifierid');

        $booking->annotate_ids('user', 'userid');
        $booking->annotate_ids('user', 'modifierid');

        $seat->annotate_ids('user', 'userid');
        $seat->annotate_ids('user', 'modifierid');

        // Define file annotations
        $examregistrar->annotate_files('mod_examregistrar', 'intro', null); // This file area hasn't itemid
        /// examfiles are intentionally excluded: no export of exams PDFs

        // Return the root element (examregistrar), wrapped into standard activity structure
        return $this->prepare_activity_structure($examregistrar);
    }
}
