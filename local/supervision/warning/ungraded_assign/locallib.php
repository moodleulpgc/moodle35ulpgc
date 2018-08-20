<?php

/**
 * Definition of warning_ungraded_assign, a subclass supervision warning class
 *
 * @package   warning_ungraded_assign
 * @package   local_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_supervision;
 
defined('MOODLE_INTERNAL') || die();


/**
 * An object that holds methods and attributes of warning_ungraded_assign class
 * Works together with supervision_warnings table
 *
 * @package   warning_ungraded_assign
 * @package   local_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class warning_ungraded_assign extends warning {

    /**
     * Constructor. Optionally attempts to fetch corresponding row from the database
     *
     * @param int/objet/array $warning id field in the supervision_warnings table
     *                             or and object or array containing the relevant fields
     */
    public function __construct($warning=NULL) {
        global $DB;

        $this->id = 0;
        parent::__construct($warning);
        $this->module = 'assign';
        $this->warningtype = 'ungraded_assign';
    }

    /**
     * Called by cron to review tables for undone/pending activities that should raise a warning
     *
     * @static
     * @abstract
     * @param int $timetocheck starting time for collection
     */
    public static function get_stats($timetocheck) {
        global $DB;

        $warningconfig = get_config('supervisionwarning_ungraded_assign');
        $config = get_config('local_supervision');
        if(!$config->enablestats || !$warningconfig->enabled) {
            return;
        }
        
        list($excludedwhere, $excludedjoin, $excludedparams) = warning::get_excluded_sql($config);
        $moduleid = $DB->get_field('modules', 'id', array('name'=>'assign'));

        /// First we obtain all ungraded assignments for relevant users, categories etc., later test for delay.
        $instances= '';
        if($warningconfig->enabled == 2 ) {
            $instances= " AND cm.score > '0' ";
        }
        
        switch($warningconfig->grading) {
            case 1 : $gradedinstances = " a.grade != 0 ";
                    break;
            case 2 : $gradedinstances = " a.grade > 0 ";
                    break;
            case 3 : $gradedinstances = " a.grade < 0 ";
                    break;
            default : $gradedinstances = '';
        }            
        if($gradedinstances) {
            $gradedinstances = ' AND '.$gradedinstances;
        }
        
        // first apply a limit without taking account holidays. Any item closer than threshold without holidays cannot be delayed enough
        $timelimit = strtotime('-'.$warningconfig->threshold.' days', $timetocheck);
        mtrace("    timelimit: $timelimit;   = ".userdate($timelimit));

        // GROUP BY required by unique first column
        // hides open warnings if several useris with the same sub.id (i.e. several teachers in a course, submission ungraded)
        $sql = "SELECT sub.id AS id,  a.id AS assignid, a.course as courseid, a.name,
                                sub.id as subid, sub.userid, sub.timemodified, sub.attemptnumber, ag.grader, ag.timemodified as timemarked,
                                cm.id AS cmid, cm.score, cm.groupmode, cm.groupingid
                        FROM {assign_submission} sub
                        INNER JOIN (SELECT asub.id, asub.assignment, asub.userid, MAX(asub.attemptnumber) AS lastattempt
                                    FROM {assign_submission} asub
                                    GROUP BY asub.assignment, asub.userid) gsub ON gsub.assignment = sub.assignment AND gsub.userid = sub.userid AND sub.attemptnumber = gsub.lastattempt
                        LEFT JOIN {assign_grades} ag ON sub.assignment = ag.assignment AND sub.userid = ag.userid AND sub.attemptnumber = ag.attemptnumber AND ag.grade >= 0 AND sub.userid != 0
                        JOIN {assign} a ON sub.assignment = a.id $gradedinstances
                        JOIN {course_modules} cm ON cm.instance = a.id AND cm.course = a.course AND cm.module = :module
                        JOIN {course} c ON c.id = a.course AND c.visible = 1
                        $excludedjoin
                    WHERE  (sub.timemodified > ag.timemodified  OR ag.timemodified IS NULL) AND sub.status = 'submitted' AND cm.visible = 1
                            AND a.grade <> 0 
                            $instances $excludedwhere
                            AND  sub.timemodified < :timelimit GROUP BY sub.id" ;
        $params = $excludedparams+array('timelimit'=>$timelimit, 'module'=>$moduleid);
        $currentassigns = $DB->get_records_sql($sql, $params);
        if(!$currentassigns) {
            $currentassigns = array();
        }

        /// Now we have ungraded assignments, check if really delayed, relying on holidays table

        if($currentassigns) {
            $negatives = array();
            foreach ($currentassigns as $stat) {
                // the max time this assignment should had been graded without warning
                $stat->timereference = $stat->timemodified;
                $timelimit = warning::threshold_without_holidays($stat->timemodified,$warningconfig->threshold, true, false);
                if($timelimit >= $timetocheck) {
                    $negatives[] = $stat->id;
                } else {
                    $stat->timemodified = $timelimit;
                }
            }
            foreach($negatives as $key) {
                unset($currentassigns[$key]);
            }
        }

        //$sql = "SELECT  sw.itemid AS auxid, sw.id, sw.instanceid, sw.studentid,  sw.timefixed
        $sql = "SELECT  sw.itemid AS auxid, sw.*
                        FROM {supervision_warnings} sw
                    WHERE  sw.module = :module AND sw.warningtype = :type AND sw.timefixed <= 0
                    GROUP BY sw.itemid "; // needed to avoid duplicates if several teachers in a course/assign // if several teachers in a course, several warnings with the same itemid  may exists
        $params = array('module'=>'assign', 'type'=>'ungraded_assign');
        $storedassigns = $DB->get_records_sql($sql, $params);

        if(is_array($storedassigns)) {
            $newfailures = array_diff_key($currentassigns, $storedassigns);
            $fixedfailures = array_diff_key($storedassigns, $currentassigns);
        } else {
            $newfailures = $currentassigns;
            $fixedfailures = array();
        }
        
        if($fixedfailures) {
            foreach($fixedfailures as $fixed) {
                $sub = $DB->get_records('assign_grades', array('assignment'=>$fixed->instanceid, 'userid'=>$fixed->studentid), 'attemptnumber DESC, timemodified DESC', '*', 0, 1);
                $timefixed = $timelimit;
                if($sub = reset($sub)) {
                    $timefixed = $sub->timemodified ? $sub->timemodified : $timelimit;
                }
                // if several teachers in a course, several warnings with the same itemid  may exists
                $select = " courseid = ? AND module = ? AND cmid = ? AND warningtype = ? AND itemid = ? AND studentid = ? AND timefixed = 0 ";
                $params = array($fixed->courseid, $fixed->module, $fixed->cmid, $fixed->warningtype, $fixed->itemid, $fixed->studentid);
                $DB->set_field_select('supervision_warnings', 'timefixed',$timefixed , $select, $params);
            }
        }

        if($newfailures) {
            foreach($newfailures as $stat) {
                $modcontext = \context_module::instance($stat->cmid);
                if(!$stat->courseid ||
                        ($stat->userid && !has_capability('mod/assign:submit', $modcontext, $stat->userid)) ||
                        ($stat->grader && !has_capability('mod/assign:grade', $modcontext, $stat->grader)) ) {
                    // the stat is incorrect because imposible to open/read better close if possible
                    if($stat->subid) {
                        $DB->set_field('assign_submission', 'status', 'draft', array('id'=>$stat->subid));
                    }
                    continue;
                }

                $warning = new warning_ungraded_assign();
                $warning->courseid = $stat->courseid;
                $warning->cmid = $stat->cmid;
                $warning->instanceid = $stat->assignid;
                $warning->itemid = $stat->subid;
                $warning->url = "/mod/assign/view.php?id={$stat->cmid}&action=grading";
                $warning->info = $stat->name;
                $warning->studentid = $stat->userid;
                $warning->timereference = $stat->timereference;
                $warning->timecreated = $stat->timemodified;
                $warning->timefixed = 0;
                $warning->timemailed = 0;
                $warning->comment = '';
                $warning->userid = $stat->grader;
                $warning->groupmode = $stat->groupmode;
                $warning->groupingid = $stat->groupingid;


                $graders = warning::get_supervised_users($warning, 'mod/assign:grade');
                
                foreach($graders  as $grader) {
                    $warning->id = null;
                    $warning->userid = $grader;
                    $warning->id = $warning->db_insert();
                }
            }
            mtrace("Adding ".count($newfailures).'  ungraded assignment warnings');
            mtrace("Fixing ".count($fixedfailures).'  ungraded assignment warnings');
        }

    }

}

