<?php
/**
 * backuprestore tool multibackup utility
 *
 * @package    tool
 * @subpackage backuprestore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/formslib.php');
//require_once($CFG->dirroot.'/backup/lib.php');
//require_once($CFG->dirroot.'/backup/backuplib.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/admin/tool/backuprestore/locallib.php');

// moodleform for controlling the report
class backuprestore_prebackupfrom_form extends moodleform {
    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;

        $mform->addElement('header', 'prebackupactions', get_string('prebackupactions', 'tool_backuprestore'));

        //$mform->addElement('advcheckbox', 'prebk_tables', get_string('prebk_tables', 'tool_backuprestore'));
        $mform->addElement('advcheckbox', 'prebk_question_useridnumbers', get_string('prebk_questionusers', 'tool_backuprestore'));
        $mform->addElement('advcheckbox', 'prebk_scales', get_string('prebk_scales', 'tool_backuprestore'));
        $mform->addElement('advcheckbox', 'prebk_resources', get_string('prebk_resources', 'tool_backuprestore'));
        $mform->addElement('advcheckbox', 'prebk_sections', get_string('prebk_sections', 'tool_backuprestore'));
        $mform->addElement('advcheckbox', 'prebk_topicgroup_sections', get_string('prebk_topicgroupsections', 'tool_backuprestore'));

        $mform->addElement('hidden', 'process', 'proceed');
        $mform->setType('process', PARAM_TEXT);

        $this->add_action_buttons(true, get_string('prebackupgo', 'tool_backuprestore'));
    }
}

function prebk_update_question_users() {
    global $DB;
    
    $sql = "SELECT q.id, uq.id AS uqid, q.category, qc.contextid, q.createdby, q.modifiedby, uq.creatoridnumber, uq.modifieridnumber,
                    uc.idnumber AS ucidnumber, um.idnumber AS umidnumber
            FROM {question} q
            LEFT JOIN {local_ulpgccore_questions} uq ON uq.questionid = q.id
            JOIN {question_categories} qc ON q.category = qc.id
            JOIN {user} uc ON q.createdby = uc.id
            JOIN {user} um ON q.modifiedby = um.id
            WHERE (uq.creatoridnumber <> uc.idnumber OR uq.modifieridnumber <> um.idnumber OR uq.creatoridnumber IS NULL OR  uq.modifieridnumber IS NULL)
            ORDER BY qc.contextid ASC ";
    $questions = $DB->get_recordset_sql($sql, array());
    $oldquestioncontextid = -1;
    $editor = false;
    if($questions->valid()) {
        foreach($questions as $q) {
            if(!$DB->record_exists('context', array('id'=>$q->contextid))) {
                continue;
            }
            if($oldquestioncontextid != $q->contextid) {
                $editor = backuprestore_get_context_editor($q);
            }
            $oldquestioncontextid = backuprestore_save_update_question_users($q, $oldquestioncontextid, $editor);
        }
    }
    $questions->close();
}


function prebk_old_resources() {
    global $DB, $OUTPUT, $USER;

    $modulename = 'resource';
    $moduleid = $DB->get_field('modules', 'id', array('name'=>$modulename));

    $sql = "SELECT cm.*, r.id as rid, r.name AS rname, c.shortname, c.fullname
                FROM {course_modules} cm
                JOIN {resource} r ON cm.course = r.course AND cm.instance = r.id AND cm.module = ?
                JOIN {course} c ON cm.course = c.id
            WHERE
                cm.module = ? AND r.tobemigrated = 1
                ORDER BY c.shortname ASC ";
    $params = array($moduleid, $moduleid);

    $rs_modules = $DB->get_recordset_sql($sql, $params);

    /// now apply those settings to each module instance (and course_modules)
    $oldcourseid = 0;

    foreach($rs_modules as $mod) {

        $message = "{$mod->shortname} : {$mod->rname}";

        $deleteinstancefunction = $modulename."_delete_instance";

        if($success = $deleteinstancefunction($mod->rid)) {
            $flag = ' Deleted';
            if (! course_delete_module($mod->id)) {
                $flag = ' error ';
                notify("Could not delete the $modulename (coursemodule)");
            }
            if (! delete_mod_from_section($mod->id, $mod->section)) {
                $flag = ' error ';
                notify("Could not delete the $modulename from that section");
            }

            $message .= ' - '.$flag;

            /// housekeeping part (do as course/modedit.php do)
            // Trigger a mod_deleted event with information about this module.
            $eventdata = new stdClass();
            $eventdata->modulename = $modulename;
            $eventdata->cmid       = $mod->id;
            $eventdata->courseid   = $mod->course;
            $eventdata->userid     = $USER->id;
            events_trigger('mod_deleted', $eventdata);

        } else {
            $message .= ' - Fail';
            notify("Could not delete the $modulename instance $mod->id ");
        }
        echo $message;
        echo '<br />';

        if($success && $mod->course != $oldcourseid) {
            rebuild_course_cache($mod->course);
        }

        $oldcourseid = $mod->course;
    }
    $rs_modules->close();

}


function prebk_scales() {
    global $DB, $OUTPUT;

    $select = " scale LIKE '%0%' AND NOT (scale LIKE '%e%' ) ";
    $scales = $DB->get_records_select('scale', $select, null);
    foreach($scales as $scale) {
        $values = explode(',', $scale->scale);
        $n = count($values) -1;
        $max = array_pop($values);
        $delta = $max/$n;
        $decimals = abs(round(log10($delta)-0.5));
        $items = $DB->get_records('grade_items', array('scaleid'=>$scale->id));
        foreach ($items as $item) {
            $item->gradetype = 1;
            $item->grademax = $max;
            $item->grademin = 0;
            $item->gradepass = $max/2;
            $item->decimals = $decimals;
            $item->scaleid = null;
            $DB->update_record('grade_items', $item);
            if($item->itemtype == 'mod') {
                $instance = $DB->get_record($item->itemmodule, array('id'=>$item->iteminstance));
                if($instance) {
                    $instance->grade = $max;
                    $instance->scale = $max;
                    $DB->update_record($item->itemmodule, $instance);
                }
            }
            $shortname = $DB->get_field('course', 'shortname', array('id'=>$item->courseid));
            echo $shortname.' : '.$item->itemname.'<br />';
        }
        $DB->delete_records('scale', array('id'=>$scale->id));
    }
}

function prebk_sections() {
    global $DB, $OUTPUT;

    $select = " section > 1000 ";

    $courses = $DB->get_fieldset_select('course_sections', 'course', $select, null);
    if($courses) {
        $courses = array_unique($courses);
        foreach($courses as $courseid) {
            $shortname = $DB->get_field('course', 'shortname', array('id'=>$courseid));
            $sections = $DB->get_records_select('course_sections', ' section > 1000 AND course = ? ', array($courseid));
            $numsections = $DB->get_field('course', 'numsections', array('id'=>$courseid));
            $last = $numsections+1;
            foreach($sections as $section) {
                //print_object ($section);
                $select = " course = ? AND section > ? AND section < 1001 AND ( summary IS NULL OR summary = '')  AND name IS NULL AND (sequence = '' OR sequence IS NULL) ";
                $first = $DB->get_records_select('course_sections', $select, array($section->course, $numsections), ' section ASC ', '*', 0, 1);
                //print_object($first);
                if($first) {
                    $first = array_shift($first);
                    $last = $first->section;
                    $s = $section->section;
                    $section->section = $first->section;
                    $DB->delete_records('course_sections', array('id'=> $first->id));
                    $DB->update_record('course_sections', $section);
                } else {
                    $select = " course = ? AND section > ? AND section < 1001 AND ( ( summary IS NOT NULL AND  summary != '')  OR name IS NOT NULL OR ( sequence != '' AND  sequence IS NOT NULL)) ";
                    $last = $DB->get_records_select('course_sections', $select, array($section->course, $numsections), ' section DESC ', '*', 0, 1);
                    if($last) {
                        $last = array_shift($last);
                        $last = $last->section;
                    }
                    $last += 1;
                    //print_object ("section = $last");
                    $section->section = $last;
                    $DB->delete_records('course_sections', array('course'=>$courseid, 'section'=> $last));
                    $DB->update_record('course_sections', $section);
                }
            echo $shortname.' : '.$section->name.'<br />';
            }
            $DB->delete_records_select('course_sections', "course = ? AND  section > ? AND ( sequence = ? OR sequence IS NULL) ", array($courseid, $last, ''));
            echo $shortname." : Deleted empty sections > $last <br />";
        }
    }
}

function prebk_topicgroup_sections() {
    global $DB, $OUTPUT;

    $now = time();
    $sql = "SELECT cs.id AS sectionid, cs.course, cs.availability
            FROM {course_sections} cs
            JOIN {course} c ON c.id = cs.course
            WHERE c.format = 'topicgroup' AND cs.availability LIKE '%grouping%' ";

    if($sections = $DB->get_recordset_sql($sql, array())) {
        foreach($sections as $section) {
            $section->timecreated = $now;
            $section->timemodified = $now;
            $availabilityinfo = json_decode($section->availability);
            $availability = $availabilityinfo->c[0];
            $section->groupingid = ($availability->type == 'grouping') ? $availability->id : 0;
            if($section->groupingid && !$DB->record_exists('format_topicgroup_sections', array('course'=>$section->course, 'sectionid'=>$section->sectionid)) &&
               $DB->record_exists('groupings', array('courseid'=>$section->course, 'id'=>$section->groupingid))) {
                $DB->insert_record('format_topicgroup_sections', $section);
            }
        }
        $sections->close();
        echo " Re-built topicgroup sections  <br />";
    }
}

function prebk_groups_tables() {
    global $DB, $OUTPUT;

    $dbman = $DB->get_manager();
    $table = new xmldb_table('groups');
    $field = new xmldb_field('enrol');
    $field->set_attributes(XMLDB_TYPE_CHAR, '10',  null, null, null, 'manual', 'idnumber');
/// Launch addition of new field
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
        echo "Dropping 'enrol' from groups table: done  <br />";
    }



    $table = new xmldb_table('groups_members');

    $field = new xmldb_field('enrol');
    $field->set_attributes(XMLDB_TYPE_CHAR, '10',  null, null, null, 'manual', 'timeadded');
/// Launch addition of new field
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
        echo "Dropping 'enrol' from groups_members table: done  <br />";
    }

}


////////////////////////////////////////////////////////////////////////////////////////////
@set_time_limit(60*60); // 1 hour should be enough
raise_memory_limit(MEMORY_HUGE);
if (function_exists('apache_child_terminate')) {
    // if we are running from Apache, give httpd a hint that
    // it can recycle the process after it's done. Apache's
    // memory management is truly awful but we can help it.
    @apache_child_terminate();
}


require_login();

admin_externalpage_setup('toolmultibackup');

$context = context_system::instance();
require_capability('moodle/site:config', $context);

if (!$site = get_site()) {
    print_error("Could not find site-level course");
}

if (!$adminuser = get_admin()) {
    print_error("Could not find site admin");
}

/// Print the header
echo $OUTPUT->header();
echo $OUTPUT->heading_with_help(get_string('prebackup', 'tool_backuprestore'), 'prebackup', 'tool_backuprestore');

$returnurl = new moodle_url($CFG->wwwroot.'/admin/tool/backuprestore/prebackup_cleanup.php');


if (($formdata = data_submitted()) && confirm_sesskey()) {
    /// some data, process input
    if(isset($formdata->cancel)) {
        redirect($returnurl, '', 0);
    }

    $status = $formdata->process;
    /// there is confirmation, proceed to do the cleanup
    if($status == 'proceed') {

        if($formdata->prebk_question_useridnumbers) {
            echo $OUTPUT->heading(get_string('prebk_questionusers', 'tool_backuprestore'));
            prebk_update_question_users();
        }

        if($formdata->prebk_resources) {
            echo $OUTPUT->heading(get_string('prebk_resources', 'tool_backuprestore'));
            prebk_old_resources();
        }

        if($formdata->prebk_scales) {
            echo $OUTPUT->heading(get_string('prebk_scales', 'tool_backuprestore'));
            prebk_scales();
        }

        if($formdata->prebk_sections) {
            echo $OUTPUT->heading(get_string('prebk_sections', 'tool_backuprestore'));
            prebk_sections();
        }

        if($formdata->prebk_topicgroup_sections) {
            echo $OUTPUT->heading(get_string('prebk_topicgroupsections', 'tool_backuprestore'));
            prebk_topicgroup_sections();
        }

/*
        if($formdata->prebk_tables) {
            echo $OUTPUT->heading(get_string('prebk_tables', 'tool_backuprestore'));
            //prebk_groups_tables();
        }
*/
        echo $OUTPUT->continue_button($returnurl);
        echo $OUTPUT->footer();
        die;
    }
}

/// no data, present the first form
$mform = new backuprestore_prebackupfrom_form('prebackup_cleanup.php');
$mform->display();
echo $OUTPUT->footer();
