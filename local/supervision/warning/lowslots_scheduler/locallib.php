<?php

/**
 * Definition of supervisionwarning_lowslots_scheduler, a subclass supervision warning class
 *
 * @package   supervisionwarning_lowslots_scheduler
 * @package   local_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//require_once($CFG->dirroot.'/lib/statslib.php');

/**
 * An object that holds methods and attributes of supervisionwarning_lowslots_scheduler class
 * Works together with supervision_warnings table
 *
 * @package   supervisionwarning_lowslots_scheduler
 * @package   local_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class supervisionwarning_lowslots_scheduler extends supervisionwarning {

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
        $this->module = 'scheduler';
        $this->warningtype = 'lowslots_scheduler';
    }

    /**
     * Called by cron to review tables for undone/pending activities that should raise a warning
     *
     * @static
     * @abstract
     * @param int $timetocheck starting time for collection
     * @param int $lastexecution last time this routine was launched by cron
     */
    public static function collect_stats($timetocheck, $lastexecution) {
        global $DB;

        $warningconfig = get_config('supervisionwarning_lowslots_scheduler');
        if(!$warningconfig->enabled) {
            return;
        }

        $blockconfig = get_config('block_supervision');
        $moduleid = $DB->get_field('modules', 'id', array('name'=>'dialogue'));

        return; /// UNCONDITIONAL for the moment

        /// First we obtain all open dialogues for relevant users, categories etc., later test for delay.
        $checkedroles = explode(',', $blockconfig->checkedroles);
        list($usql, $params) = $DB->get_in_or_equal($checkedroles);

        $rolesql = "SELECT rc.roleid, rc.capability
                            FROM {role_capabilities} rc
                            WHERE rc.roleid  $usql AND rc.capability = ?
                            GROUP BY rc.roleid ";
        $params[] = 'mod/dialogue:manage';
        if($roles = $DB->get_records_sql($rolesql, $params)) {
            list($inrolesql, $roleparams) = $DB->get_in_or_equal(array_keys($roles), SQL_PARAMS_NAMED, 'role' );
        } else {
            return false;
        }

        $excludedcategories = '';
        $catparams = array();
        if($blockconfig->excludedcats) {
            list($incatsql, $catparams) = $DB->get_in_or_equal(explode(',', $blockconfig->excludedcats), SQL_PARAMS_NAMED, 'cat', false );
            $excludedcategories = " AND c.category $incatsql ";
        }

        $excludecourses = '';
        if($blockconfig->excludecourses) {
            $excludecourses = ' AND c.credits > 0 ';
        }

        $instances= '';
        if($warningconfig->enabled == 2 ) {
            $instances= " AND cm.score > '0' ";
        }

        $contextlevel = CONTEXT_COURSE;
        // first apply a limit without taking account holidays. Any item closer than threshold without holidays cannot be delayed enough
        $timelimit = strtotime('-'.$warningconfig->threshold.' days', $timetocheck);

        // First we obtain all open dialogues, later test for delay.
        //Timemofied may be close, but question may be older (repeated asking)
        $sql = "SELECT  dc.id AS id,  d.id AS dialogueid, d.course as courseid, d.name,
                                dc.id as conversationid, dc.recipientid, dc.userid, dc.timemodified, dc.closed,
                                cm.id AS cmid, cm.score
                        FROM {dialogue_conversations} dc
                        JOIN {dialogue} d ON dc.dialogueid = d.id
                        JOIN {course_modules} cm ON cm.instance = dc.dialogueid AND cm.course = d.course AND cm.module = :module
                        JOIN {course} c ON c.id = d.course AND c.visible = 1
                    WHERE   d.dialoguetype = 0 AND dc.userid = dc.lastid AND cm.visible = 1
                            $instances $excludedcategories $excludecourses
                            AND dc.closed = 0  AND dc.timemodified < :timelimit
                        AND dc.recipientid IN (SELECT ra.userid FROM
                                                    {role_assignments} ra
                                                    JOIN {context} ctx ON ra.contextid = ctx.id
                                                    WHERE ctx.contextlevel = :contextlevel  AND ctx.instanceid = d.course
                                                    AND ra.roleid $inrolesql
                                                 ) ";
        $params = $roleparams+$catparams+array('timelimit'=>$timelimit, 'contextlevel'=>CONTEXT_COURSE, 'module'=>$moduleid);
        $currentdialogues = $DB->get_records_sql($sql, $params);

        /// Now we have open dialogues, check if really delayed, relying on holidays table

        if($currentdialogues) {
            $negatives = array();
            foreach ($currentdialogues as $stat) {
                // the max time this dialogue should had been replyed without warning
                $timelimit = supervisionwarning::threshold_without_holidays($stat->timemodified,$warningconfig->threshold, true, true);
                if($timelimit >= $timetocheck) {
                    $negatives[] = $stat->id;
                } else {
                    $stat->timemodified = $timelimit;
                }
            }
            foreach($negatives as $key) {
                unset($currentdialogues[$key]);
            }
        }

        $sql = "SELECT  sw.itemid AS auxid, sw.*
                        FROM {supervision_warnings} sw
                    WHERE  sw.module = :module AND sw.warningtype = :type AND sw.timefixed <= 0 ";
        $params = array('module'=>'dialogue', 'type'=>'unreplied_dialogue');
        $storeddialogues = $DB->get_records_sql($sql, $params);

        if(is_array($storeddialogues)) {
            $newfailures = array_diff_key($currentdialogues, $storeddialogues);
            $fixedfailures = array_diff_key($storeddialogues, $currentdialogues);
        } else {
            $newfailures = $currentdialogues;
            $fixedfailures = array();
        }

        if($fixedfailures) {
            foreach($fixedfailures as $fixed) {
                if($fixed->timefixed >= 0) {
                    $timemodified = $DB->get_field('dialogue_conversations', 'timemodified', array('id'=>$fixed->itemid));
                    $fixed->timefixed = $timemodified;
                    $DB->update_record('supervision_warnings', $fixed);
                }
            }
            mtrace("Fixing ".count($fixedfailures).'  unreplied dialogue warnings');
        }

        if($newfailures) {
            foreach($newfailures as $stat) {
                $modcontext = context_module::instance($stat->cmid);
                if(!$stat->courseid || !has_capability('mod/dialogue:open', $modcontext, $stat->userid) || !has_capability('mod/dialogue:manage', $modcontext, $stat->recipientid) ) {
                    // the stat is incorrect because imposible to open/read better close if possible
                    if($stat->conversationid) {
                        $DB->set_field('dialogue_conversations', 'closed', 1, array('id'=>$stat->conversationid));
                    }
                    continue;
                }

                $warning = new supervisionwarning_unreplied_dialogue();

                $warning->courseid = $stat->courseid;
                $warning->cmid = $stat->cmid;
                $warning->instanceid = $stat->dialogueid;
                $warning->itemid = $stat->conversationid;
                $warning->url = "/mod/dialogue/dialogues.php?id={$stat->cmid}&action=printdialogue&cid={$stat->conversationid}";
                $warning->info = $stat->name;
                $warning->userid = $stat->recipientid;
                $warning->studentid = $stat->userid;
                $warning->timecreated = $stat->timemodified;
                $warning->timefixed = 0;
                $warning->timemailed = 0;
                $warning->comment = '';

                $warning->id = $warning->db_insert();
            }
            mtrace("Adding ".count($newfailures).'  unreplied dialogues warnings');
        }

    }

    /**
     * Returns an appropiate link to an activity item suitable for warnings report
     *
     * @abstract
     * @return string , formatted link
     */
    public function report_instancelink() {
        throw new coding_exception('report_instancelink() method needs to be overridden in each subclass of supervision_warning ');
    }

    /**
     * Returns an appropiate info about an activity item that raised a warning
     *
     * @abstract
     * @return string , formatted link
     */
    public function report_rowinfo() {
        throw new coding_exception('report_rowinfo() method needs to be overridden in each subclass of supervision_warning ');
    }

    /**
     * Calculates overdue time for this activity warning
     *
     * @abstract
     * @param int $timetocheck time for calculation with respect to timecreated
     * @return string , formatted link
     */
    public function report_overdue($timetocheck) {
        throw new coding_exception('report_overdue() method needs to be overridden in each subclass of supervision_warning ');
    }





}

