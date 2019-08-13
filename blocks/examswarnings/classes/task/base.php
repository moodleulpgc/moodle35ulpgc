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

namespace block_examswarnings\task;

/**
 * Simple task to run the cron.
 */
abstract class base extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('examwarnings', 'block_examswarnings');
    }
    

    /**
     * Gets esamsession data & calaculates day to send reminders
     * Throw exceptions on errors (the job will be retried).
     */
    public function get_session_days($config, $field) {
    
        $now = time();
        $today = usergetmidnight($now);  
    
    /// gets sending day    
        list($period, $session) = examswarnings_get_session_period($config);
        $days = $config->{$field};
        $sendingday = strtotime("-$days days", $session->examdate + 60*60*$session->timeslot);
        $sendingday = (($today < $sendingday) && ($sendingday < strtotime("+1 day", $today))) ? true : false;  
    
        return array($period, $session, $sendingday);
    }
    

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function get_configs() {
        global $CFG, $DB;
        
        $configs = array();

    /// We require some stuff
        $config = get_config('block_examswarnings');
        if(!$config->globalenableroomcalls) {
            return $configs;
        }
        
        if(!$blocks = $DB->get_records_menu('block_instances', 
                        array('blockname'=>'examswarnings', 'parentcontextid'=>\context_system::instance()->id), 
                        'pagetypepattern DESC, defaultweight ASC', 'id,configdata') ) {
            return $configs;        
        }
        
        // select only blocks with appropiate config and a single examregistrar
        foreach($blocks as $config) {
            $config = unserialize(base64_decode($config));
            if(!(empty($config) || !(isset($config->primaryreg) && $config->primaryreg) || (isset($config->enableroomcalls) && $config->enableroomcalls))) {
                //this ensures only one examreg is proccessed
                $configs[$config->primaryreg] = $config;
            }
        }
        unset($blocks);

        return $configs;
    }
    
    /**
     * Gets esamsession data & calaculates day to send reminders
     * Throw exceptions on errors (the job will be retried).
     */
    public function send_control_email($config, $session, $sent) {
    
        if($controluser = examswarnings_get_controlemail($config)) {
            $from = get_string('examreminderfrom',  'block_examswarnings');
            $info = new \stdClass;
            $info->num = count($sent);
            $info->date = userdate($session->examdate, '%A %d de %B de %Y');
            list($sessionname, $idnumber) = examregistrar_item_getelement($session, 'examsession');
            $subject = get_string('controlmailsubject', 'block_examswarnings', "$sessionanme ($idnumber)");
            $text = get_string('controlmailtxt',  'block_examswarnings', $info )."\n\n".implode("\n", $sent);
            foreach($controluser as $cu) {
                $html = ($cu->mailformat == 1) ? get_string('controlmailhtml',  'block_examswarnings', $info ).'<br />'.implode(' <br />', $sent) : '';
                email_to_user($cu, $from, $subject, $text, $html);
            }
            mtrace('    ... sent '.$info->num.' '.$this->get_name());
        }
    }
}
