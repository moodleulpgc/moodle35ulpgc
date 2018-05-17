<?php
/**
 * Backuprestore  tool library functions
 *
 * @package    tool_backuprestore
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * Creates a the module object for defined mod. Error if not possible
 * @param object $form post data including module selection settings as modXXX fields
 * @return module object
 */

function backuprestore_get_context_editor($question) {
    $editor = false;
    $config = get_config('tool_backuprestore');
    $qcontext = context::instance_by_id($question->contextid);
    $context = $qcontext->get_course_context();
    if(!$editors = get_role_users($config->coordinatorrole, $context, false, 'u.id, u.idnumber, ra.sortorder, ra.timemodified, ra.roleid', 'ra.sortorder ASC, ra.timemodified DESC', false, '', 0, 1)) {
        $editors = get_enrolled_users($context, 'moodle/question:editall', 0, 'u.id, u.idnumber', ' u.id ASC ', 0, 1, true);
    }
    if($editors) {
        $editor = reset($editors);
    }
    return $editor;
}


function backuprestore_save_update_question_users($q, $oldqctxtid, $editor) {
    global $CFG, $DB;

    $update = false;
    $insert = false;
    $uq = clone $q;
    
    $uq->questionid = $q->id;
    if(empty($q->uqid)) { 
        $uq->sourceqid = $q->id;
        $uq->qsource = $CFG->wwwroot; 
        $uq->creatoridnumber = 0;
        $uq->modifieridnumber = 0;
        $insert = true;
    } else {
        $uq->id = $q->uqid;
    }
    
    $qcontext = context::instance_by_id($q->contextid);
    if(is_siteadmin($q->createdby) && !is_enrolled($qcontext, $q->createdby, 'moodle/question:editall')) {
        // admin user not enrolled, change to editor
        if($editor) {
            $uq->creatoridnumber = $editor->idnumber;
            $update = true;
        }
    } else {
        // regular user, just set qidnumber to user idnumber
        if($q->ucidnumber) {
            $uq->creatoridnumber = $q->ucidnumber;
            $update = true;
        }
    }

    if(is_siteadmin($q->modifiedby) && !is_enrolled($qcontext, $q->modifiedby, 'moodle/question:editall')) {
        // admin user not enrolled, change to editor
        if($editor) {
            $uq->modifieridnumber = $editor->idnumber;
            $update = true;
        }
    } else {
        // regular user, just set qidnumber to user idnumber
        if($q->umidnumber) {
            $uq->modifieridnumber = $q->umidnumber;
            $update = true;
        }
    }
    
    if($insert) {
        $DB->insert_record('local_ulpgccore_questions', $uq);
    } elseif($update) {
        $DB->update_record('local_ulpgccore_questions', $uq);
    }
    
    
    
    
    return $q->contextid;
}


function backuprestore_restore_update_question_users($q) {
    global $DB;
    $update = false;
    $qcontext = context::instance_by_id($q->contextid);
    if(is_siteadmin($q->createdby) && !is_enrolled($qcontext, $q->createdby, 'moodle/question:editall')) {
        // user is an admin
        $q->createdby = $q->ucid;
        $update = true;
    } else {
        // existing user is regular user
        if($force) {
            $q->createdby = $q->ucid;
            $update = true;
        }
    }

    if(is_siteadmin($q->modifiedby) && !is_enrolled($qcontext, $q->modifiedby, 'moodle/question:editall')) {
        // user is an admin
        $q->modifiedby = $q->umid;
        $update = true;
    } else {
        // existing user is regular user
        if($force) {
            $q->modifiedby = $q->umid;
            $update = true;
        }
    }
    if($update) {
        $DB->update_record('question', $q);
    }
        return $q->contextid;
}
