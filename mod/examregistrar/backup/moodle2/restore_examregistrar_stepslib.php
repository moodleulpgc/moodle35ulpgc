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
 * Define all the restore steps that will be used by the restore_examregistrar_activity_task
 */

/**
 * Structure step to restore one examregistrar activity
 */
class restore_examregistrar_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('examregistrar', '/activity/examregistrar');
        $userinfo = $this->get_setting_value('userinfo');

        $userinfo = false; // TODO  //TODO
        if($userinfo) {
            $registrar_included = $this->get_setting_value('registrarincluded');
            $exams_included = $this->get_setting_value('examsincluded');
            if($registrar_included) {
                // TODO TODO   include correct paths when module data defined   TODO   TODO
                $paths[] = new restore_path_element('examregistrar_element', '/activity/examregistrar/elements/element');
                $paths[] = new restore_path_element('examregistrar_period', '/activity/examregistrar/periods/period');



                $paths[] = new restore_path_element('examregistrar_location', '/activity/examregistrar/locations/location');
                if ($userinfo) {
                    $paths[] = new restore_path_element('examregistrar_staffer', '/activity/examregistrar/staffers/staffer');
                    if($exams_included) {
                        $paths[] = new restore_path_element('examregistrar_examination', '/activity/examregistrar/examinations/examination');
                        $paths[] = new restore_path_element('examregistrar_examsfile', '/activity/examregistrar/examsfiles/examsfile');
                        $paths[] = new restore_path_element('examregistrar_booking', '/activity/examregistrar/bookings/booking');
                        $paths[] = new restore_path_element('examregistrar_seat', '/activity/examregistrar/seatings/seat');
                    }
                }
            }
        }
        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_examregistrar($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the examregistrar record
        $newitemid = $DB->insert_record('examregistrar', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }


// TODO TODO   include correct paths when module data defined   TODO   TODO

    protected function process_examregistrar_element($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // insert only if not existing yet (defined by unique index)
        if(!$DB->record_exists('examregistrar_elements', array('type'=>$data->type, 'codename'=>$data->codename))) {
            $newitemid = $DB->insert_record('examregistrar_elements', $data);
            $this->set_mapping('examregistrar_element', $oldid, $newitemid);
        }
    }

    protected function process_examregistrar_period($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->degreetype = $this->get_mappingid('examregistrar_element', $data->degreetype);
        $data->term = $this->get_mappingid('examregistrar_element', $data->term);
        $data->scope = $this->get_mappingid('examregistrar_element', $data->scope);

        if(!$data->scope || !$data->term) {
            return;
        }
        // insert only if not existing yet (defined by unique index)
        if(!$DB->record_exists('examregistrar_periods',
                               array('annuality'=>$data->annuality, 'degreetype'=>$data->degreetype,
                                     'term'=>$data->term, 'scope'=>$data->scope))) {
            $newitemid = $DB->insert_record('examregistrar_period', $data);
            $this->set_mapping('examregistrar_period', $oldid, $newitemid);
        }
    }

    protected function process_examregistrar_location($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->type = $this->get_mappingid('examregistrar_element', $data->type);

        if(!$data->type) {
            return;
        }

        // insert only if not existing yet (defined by unique index)
        if(!$DB->record_exists('examregistrar_locations', array('type'=>$data->type, 'name'=>$data->name, 'idnumber'=>$data->idnumber))) {
            $newitemid = $DB->insert_record('examregistrar_locations', $data);
            $this->set_mapping('examregistrar_location', $oldid, $newitemid);
        }
    }


    protected function process_examregistrar_staffer($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->locationid = $this->get_new_parentid('examregistrar_location');
        $data->role = $this->get_mappingid('examregistrar_element', $data->role);
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->modifierid = $this->get_mappingid('user', $data->modifierid);

        // insert only if not existing yet (defined by unique index)
        if(!$DB->record_exists('examregistrar_staffers', array('locationid'=>$data->locationid, 'userid'=>$data->userid, 'role'=>$data->role))) {
            $newitemid = $DB->insert_record('examregistrar_staffers', $data);
        }
    }


    protected function process_examregistrar_examination($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->period = $this->get_new_parentid('examregistrar_period');
        $data->examtype = $this->get_mappingid('examregistrar_element', $data->examtype);
        $data->examcall = $this->get_mappingid('examregistrar_element', $data->examcall);
        $data->examdate = $this->get_mappingid('examregistrar_element', $data->examdate);
        $data->modifierid = $this->get_mappingid('user', $data->modifierid);

        // insert only if not existing yet (defined by unique index)
        if(!$DB->record_exists('examregistrar_exams', array('programme'=>$data->programme, 'shortname'=>$data->shortname,
                                                            'period'=>$data->period, 'examtype'=>$data->examtype, 'examcall'=>$data->examcall))) {
            $newitemid = $DB->insert_record('examregistrar_exams', $data);
            $this->set_mapping('examregistrar_examination', $oldid, $newitemid);
        }
    }


    protected function process_examregistrar_examsfile($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->examid = $this->get_new_parentid('examregistrar_examination');
        $data->modifierid = $this->get_mappingid('user', $data->modifierid);

        // insert only if not existing yet (defined by unique index)
        if(!$DB->record_exists('examregistrar_examfiles', array('examid'=>$data->examid, 'attempt'=>$data->attempt))) {
            $newitemid = $DB->insert_record('examregistrar_examfiles', $data);
        }
    }


    protected function process_examregistrar_booking($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->examid = $this->get_new_parentid('examregistrar_examination');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->locationid = $this->get_mappingid('examregistrar_location', $data->locationid);
        $data->modifierid = $this->get_mappingid('user', $data->modifierid);

        // insert only if not existing yet (defined by unique index)
        if(!$DB->record_exists('examregistrar_bookings', array('examid'=>$data->examid, 'userid'=>$data->userid))) {
            $newitemid = $DB->insert_record('examregistrar_bookings', $data);
        }
    }


    protected function process_examregistrar_seat($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->examid = $this->get_new_parentid('examregistrar_examination');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->locationid = $this->get_mappingid('examregistrar_location', $data->locationid);
        $data->timeslot = $this->get_mappingid('examregistrar_element', $data->timeslot);
        $data->modifierid = $this->get_mappingid('user', $data->modifierid);

        // insert only if not existing yet (defined by unique index)
        if(!$DB->record_exists('examregistrar_seatings', array('examid'=>$data->examid, 'userid'=>$data->userid))) {
            $newitemid = $DB->insert_record('examregistrar_seatings', $data);
        }
    }


    protected function after_execute() {
        // Add examregistrar related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_examregistrar', 'intro', null);
    }
}
