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
class backuprestore_postrestorefrom_form extends moodleform {
    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;

        $mform->addElement('header', 'postrestoreactions', get_string('postrestoreactions', 'tool_backuprestore'));


        $options = array(''=>get_string('no'),
                            'notforced' => get_string('postrst_notforced', 'tool_backuprestore'),
                            'forced' => get_string('postrst_forced', 'tool_backuprestore'));
        $mform->addElement('select', 'prebk_question_useridnumbers', get_string('postrst_questionusers', 'tool_backuprestore'), $options);

        $mform->addElement('advcheckbox', 'prebk_tables', get_string('prebk_tables', 'tool_backuprestore'));

        $mform->addElement('advcheckbox', 'postrst_question_categories', get_string('postrst_question_categories', 'tool_backuprestore'));

        $mform->addElement('advcheckbox', 'postrst_question_tags', get_string('postrst_question_tags', 'tool_backuprestore'));


        $mform->addElement('hidden', 'process', 'proceed');
        $mform->setType('process', PARAM_TEXT);

        $this->add_action_buttons(true, get_string('postrestorego', 'tool_backuprestore'));
    }
}




function postrst_groups_tables() {
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

function postrst_update_question_users($force = false) {
    global $DB;

    if($force) {
        $force = ($force == 'forced') ? true : false;
    }

    $sql = "SELECT q.id, uq.id AS uqid, q.category, qc.contextid, q.createdby, q.modifiedby, uq.creatoridnumber, uq.modifieridnumber,
                    uc.id AS ucid, um.id AS umid
            FROM {question} q
            LEFT JOIN {local_ulpgccore_questions} uq ON uq.questionid = q.id
            JOIN {question_categories} qc ON q.category = qc.id
            JOIN {user} uc ON uq.creatoridnumber = uc.idnumber
            JOIN {user} um ON uq.modifieridnumber = um.idnumber
            WHERE ((uq.creatoridnumber IS NOT NULL AND q.createdby <> uc.id) OR ( uq.modifieridnumber IS NOT NULL AND q.modifiedby <> um.id))
            ORDER BY qc.contextid ASC ";
    $questions = $DB->get_recordset_sql($sql, array());
    if($questions->valid()) {
        foreach($questions as $q) {
            $old = backuprestore_restore_update_question_users($q);
        }
    }
    $questions->close();
}


function postrst_update_question_categories($force = false) {
    global $DB;

    $sql = "SELECT id, name, contextid, COUNT(*) AS countof
            FROM {question_categories}
            GROUP BY name, contextid
            HAVING COUNT(*) > 1 ";
    $categories = $DB->get_recordset_sql($sql);
    if($categories->valid()) {
        foreach($categories as $qc) {
            $sql = "SELECT qc.id, qc.name, qc.contextid, COUNT(q.id) AS countof
                    FROM {question_categories} qc
                    LEFT JOIN {question} q ON qc.id = q.category
                    WHERE qc.name = :name AND qc.contextid = :context
                    GROUP BY qc.id
                    ORDER BY countof DESC, id ASC ";
            $params = array('name'=>$qc->name, 'context'=>$qc->contextid);

            if($qcats = $DB->get_records_sql($sql, $params)) {
                $main = array_shift($qcats);
                if($qcats) {
                    foreach($qcats as $dup) {
                        $questionids = $DB->get_records_menu('question', array('category'=>$dup->id), '', 'id,category');
                        if($questionids) {
                            question_move_questions_to_category(array_keys($questionids), $main->id);
                        }
                    }
                    foreach($qcats as $dup) {
                        if(!question_category_in_use($dup->id, true)) {
                            question_category_delete_safe($dup);
                        }
                    }
                }
            }
        }
    }
    $categories->close();

}

function postrst_update_question_tags($force = false) {
    global $DB;

    $sql = "SELECT ti.id, ti.component, qc.contextid
            FROM {tag_instance} ti
            JOIN {question} q ON ti.itemid = q.id
            JOIN {question_categories} qc ON q.category = qc.id
            WHERE ti.itemtype = :type AND ti.contextid IS NULL ";
    $params = array('type'=>'question');

    $questions = $DB->get_recordset_sql($sql, $params);
    if($questions->valid()) {
        foreach($questions as $q) {
            $q->component = 'core_question';
            $DB->update_record('tag_instance', $q);
        }
    }
    $questions->close();
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
echo $OUTPUT->heading_with_help(get_string('postrestore', 'tool_backuprestore'), 'postrestore', 'tool_backuprestore');

$returnurl = new moodle_url($CFG->wwwroot.'/admin/tool/backuprestore/postrestore_cleanup.php');


if (($formdata = data_submitted()) && confirm_sesskey()) {
    /// some data, process input
    if(isset($formdata->cancel)) {
        redirect($returnurl, '', 0);
    }

    $status = $formdata->process;
    /// there is confirmation, proceed to do the cleanup
    if($status == 'proceed') {

        if($formdata->prebk_question_useridnumbers) {
            echo $OUTPUT->heading(get_string('postrst_questionusers', 'tool_backuprestore'));
            postrst_update_question_users($formdata->prebk_question_useridnumbers);
        }

        if($formdata->postrst_question_categories) {
            echo $OUTPUT->heading(get_string('postrst_question_categories', 'tool_backuprestore'));
            postrst_update_question_categories($formdata->postrst_question_categories);
        }

        if($formdata->postrst_question_tags) {
            echo $OUTPUT->heading(get_string('postrst_question_tags', 'tool_backuprestore'));
            postrst_update_question_tags($formdata->postrst_question_tags);
        }

        if($formdata->prebk_tables) {
            echo $OUTPUT->heading(get_string('postrst_groups', 'tool_backuprestore'));
            postrst_groups_tables();
        }

        echo $OUTPUT->continue_button($returnurl);
        echo $OUTPUT->footer();
        die;
    }
}

/// no data, present the first form
$mform = new backuprestore_postrestorefrom_form('postrestore_cleanup.php');
$mform->display();
echo $OUTPUT->footer();
