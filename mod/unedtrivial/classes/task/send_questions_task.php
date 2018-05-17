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
 * Periodic task (cron job)
 *
 * @package    mod_unedtrivial
 * @copyright  2017 Juan David Castellón Fuentes <jdcaste@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_unedtrivial\task;
require_once($CFG->dirroot.'/mod/unedtrivial/lib.php');

class send_questions_task extends \core\task\scheduled_task {      
    public function get_name() {
        // Shown in admin screens
        return get_string('sendquestionstask', 'mod_unedtrivial');
    }
                                                                     
    public function execute() {       
        GLOBAL $DB;
        //First, we need to check how many unedtrivial instances are currently active
        $today = strtotime(date("Ymd",time()));
        $sql1 = 'SELECT *'
             . '   FROM {unedtrivial}'
             . '  WHERE enddate > '.time().' OR'
                . '     enddate = 60'; //UNEDTrivials with end of questions condition
        $result1 = $DB->get_records_sql($sql1);
        foreach ($result1 as $row){
            //Now, we need to capture all emails available of a concrete unedtrivial
            $sql2 = 'SELECT m.userid, m.mail,'
            . '             (SELECT COUNT(*)'
            . '               FROM {unedtrivial_history} h'
            . '              WHERE h.userid = m.userid AND h.idunedtrivial = '.$row->id.' AND'
            . '                    h.questionstate = '.$row->timestocomplete.') AS totalclosed,'
            . '             (SELECT COUNT(*)'
            . '                FROM {unedtrivial_history} h'
            . '               WHERE h.userid = m.userid AND h.idunedtrivial = '.$row->id.' AND'
            . '                     h.questiondate = '.$today.') as totaltoday'
            . '      FROM {unedtrivial_mails} m'
            . '     WHERE m.idunedtrivial = '.$row->id;
            $result2 = $DB->get_records_sql($sql2);
            $sql3 = 'SELECT * '
            .'         FROM {unedtrivial_history} u '
            .'        WHERE u.idunedtrivial = '.$row->id.' AND '
            .'              u.questionid <> -1'
            .'     ORDER BY u.userid, u.questiondate';
            $result3 = $DB->get_records_sql($sql3);
            $resume = array();
            $addresses = "";
            $totalqu = unedtrivial_get_questions($row->id);
            foreach ($result2 as $row2) {
                if (unedtrivial_are_questions_for_today($row,$result3,$row2->userid,$today,$totalqu,$resume)){
                    if ($addresses == ""){
                        $addresses = $row2->mail;
                    }else{
                        $addresses = $addresses . "," . $row2->mail;
                    }
                }
            }
            unset($resume);
            //Finally, we prepare and send the mail
            if ($addresses != ''){
                unedtrivial_send_mail($row->id, $row->name, $addresses);                
            }
        }
    }                                                                                                                               
} 
