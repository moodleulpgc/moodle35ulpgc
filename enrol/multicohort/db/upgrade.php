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
 * @package    enrol_multicohort
 * @copyright  2019 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Enrique Castro
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_enrol_multicohort_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
    $ulpgcgroups = get_config('local_ulpgcgroups');

    if ($oldversion <  2019061100) {
    
        // reformat groups idnumber codes
        $idnumber = 'multicohort\_%';
        $select = $DB->sql_like('idnumber', ':idnumber');
        $params['idnumber'] = $idnumber;    
        $rs = $DB->get_recordset_select('groups', $select, $params, '', 'id, courseid, name, idnumber');
        foreach ($rs as $gm) {
            $parts = explode('_', $gm->idnumber);
            $enrolid = (int)$parts[1];
            $cohortid = $parts[3];
            
            if($enrolid && $ulpgcgroups) { 
                local_ulpgcgroups_update_group_component($gm->id, 'enrol_multicohort', $enrolid);  
            }
            
            if($enrolid) {
                if($instance = $DB->get_record('enrol', array('id'=>$enrolid, 'courseid'=>$gm->courseid, 'enrol'=>'multicohort'), 'id, name, courseid, enrol')) {
                    $idnumber = '';
                    if($cohortid) {
                        if($cohortid == 'pooled') {
                            $idnumber = $cohortid;
                        } elseif($cohortid = (int)$cohortid) {
                            $idnumber = $DB->get_field('cohort', 'idnumber', array('id'=>$cohortid));
                        }
                    }
                    $gm->idnumber = enrol_multicohort_group_idnumber($instance, $idnumber);
                    $DB->set_field('groups', 'idnumber', $gm->idnumber, array('id'=>$gm->id));
                }
            }
        }
        $rs->close();

        // Apply savepoint reached.
        upgrade_plugin_savepoint(true, 2019061100, 'enrol', 'multicohort');
    }



    return true;

}
