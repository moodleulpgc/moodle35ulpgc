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
 * This file contains the function for feedback_plugin abstract class
 *
 * @package   tool_batchmanage
 * @copyright 2016 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//include_once($CFG->dirroot.'/tool/batchmanage/managejob_forms.php');


/**
 * Abstract class for feedback_plugin inherited from assign_plugin abstract class.
 *
 * @package   tool_batchmanage
 * @copyright 2016 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class batchmanage_managejob_questionrelease extends batchmanage_managejob_plugin {

    use batchmanage_mod_selector_sql; 
    
    /**
     * Constructor for the abstract plugin type class
     *
     * @param assign $assignment
     * @param string $type
     */
    public final function __construct($name) {
        $this->name = $name;
        $this->firstform = 'question_selector';
        $this->nextmsg = get_string('question_config', 'managejob_questionrelease');
        $this->path = core_component::get_plugin_directory('managejob', $name);  
    }

    public function process_question_selector($formdata) {
        $data = new stdClass();
        foreach($formdata as $key => $value) {
            $first = substr($key, 0, 3);
            if($first == 'qtn') {
                $data->$key = $value;
            }
        }

        return json_encode($data);
    }

    
    public function process_question_config($formdata) {
        $data = new stdClass();
        foreach($formdata as $key => $value) {
            $last = substr($key, -6, 6);
            if($last == 'modify') {
                $first = substr($key, 0, -6);
                $data->{$first} = isset($formdata->{$first}) ? $formdata->{$first} : 0;
                if(is_array($data->{$first})) {
                    if(!isset($data->{$first}['enabled'])) {
                        $data->{$first}['enabled'] = 0;
                    }
                }
            }
        }
        return json_encode($data);
    }
    
   
    
    public function process_action($action, $formdata) {
        $next = '';
        if($action == 'question_selector') {
            $this->formsdata[$action] = $this->process_question_selector($formdata);
            $next = 'question_config';
            $this->nextmsg = get_string('courses_selector', 'tool_batchmanage');
        } elseif($action == 'question_config') {
            $this->formsdata[$action] = $this->process_question_config($formdata);
            $next = 'courses_selector';
            $this->nextmsg = get_string('reviewconfirm', 'tool_batchmanage');
        } else {
            return parent::process_action($action, $formdata);
        }
        
        return $next;
    }
    
    public function review_confirm_formsdata(& $mform, $action, $data, $innerform) {
        if($action == 'question_selector') {
            $this->process_non_grouped_innerform($mform, $action, $data, $innerform);
        }

        if($action == 'question_config') {
            $this->process_grouped_innerform($mform, $action, $data, $innerform);
        }
        parent::review_confirm_formsdata($mform, $action, $data, $innerform);
    }
    
    
    public function has_applicable_action() {
        $data = json_decode($this->formsdata['question_config']);

      
        return $data;
    }
    
    
    public function combine_selectors_sql() {
        list($wherecourse, $join, $params) = $this->courses_selector_sql();
        $sql = "SELECT c.id as courseid, c.*
                FROM {course} c
                $join
            WHERE 1 $wherecourse
                ORDER BY c.category ASC, c.shortname ASC ";
        return array($sql, $params);
    }

   
    public function question_selector_sql($course, $data) {
        global $DB;
        
        $params = array();
        
        $wherequestions = ' ';
        if ($data->qtnuselike) {
            $wherequestions .= $DB->sql_like('qc.name', ':name');
        } else {
            $wherequestions .= " qc.name = :name ";
        }
        $params['name'] =  $data->qtncategoryname;

        if(isset($data->qtncategoryparent) && $data->qtncategoryparent > -1) {
            $wherequestions .= " AND q.parent = :parent ";
            $params['parent'] =  $data->qtncategoryparent;
        }

        if(isset($data->qtnvisibility) && $data->qtnvisibility > -1) {
            $wherequestions .= " AND q.hidden = :hidden ";
            $params['hidden'] =  $data->qtnvisibility;
        }

        if(isset($data->qtnquestionid) &&  $data->qtnquestionid ) {
            if($names = explode(',' , addslashes($data->qtnquestionid))) {
                foreach($names as $key => $name) {
                    $names[$key] = trim($name);
                }
                list($insql, $inparams) = $DB->get_in_or_equal($names, SQL_PARAMS_NAMED, 'qid_' );
                $wherequestions .= " AND q.id $insql ";
                $params = array_merge($params, $inparams);
            }
        }
        
        $coursecontext = context_course::instance($course->courseid);
        if($data->qtncategorycontext == 0) {
            $wherecontext = ' = :ctxid';
            $params['ctxid'] = $coursecontext->id;
        } else {
            $contextids = array_keys($coursecontext->get_child_contexts());
            if($data->qtncategorycontext == -1) {
                $contextids[] = $coursecontext->id;
            }
            list($wherecontext, $inparams) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'ctx_');
            $params = array_merge($params, $inparams);
        }
        
        $sql = "SELECT q.*, qc.contextid,  uq.id AS uqid, uq.questionid, uq.qsource, uq.sourceqid, uq.creatoridnumber, uq.modifieridnumber
                    FROM {question} q
                    JOIN {question_categories} qc ON q.category = qc.id 
                    LEFT JOIN {local_ulpgccore_questions} uq ON q.id = uq.questionid";
        $where = " WHERE ( qc.contextid $wherecontext ) AND ( $wherequestions ) ";
   
        return array($sql, $where, $params);
    }
   
   
    public function apply_job_on_item($course, $data) {
        global $CFG, $DB; 
        
        $success = false;
        $extramsg = array();
        
        list($sql, $where, $params) = $this->question_selector_sql($course, json_decode($this->formsdata['question_selector']));
        
        
        if(isset($data->userdata)) {
            $extramsg[] = $this->apply_userdata($sql.$where, $params, $data->userdata);
            $success = true;
        }
        
        if(isset($data->validated)) {
            $extramsg[] = $this->apply_validation($sql.$where, $params, $data->validated);
            $success = true;
        }

        if(isset($data->hidden)) {
            $extramsg[] = $this->apply_visibility($where, $params, $data->hidden);
            $success = true;
        }

        return array($success, '', implode('<br />',  $extramsg));
    }
    
    /**
    * Manage question useridnumber data  for a single question
    * @param object $question record form database
    * @param string $useraction either saving or restoring
    * @return bool success
    */
    public function apply_userdata($sql, $params, $useraction) {
        global $DB;
    
        $rs_questions = $DB->get_recordset_sql($sql, $params);

        $message = 'No questions';
        $qlist = array();

        if($rs_questions->valid()) {
            $oldquestioncontextid = -1;
            $editor = false;
            foreach($rs_questions as $question) {
                if($useraction == 'save') {
                    if(!$DB->record_exists('context', array('id'=>$question->contextid))) {
                        continue;
                    }
                    if($oldquestioncontextid != $question->contextid) {
                        $editor = $this->get_context_editor($question);
                    }
                    $creator = $DB->get_record('user', array('id'=>$question->createdby), 'id, idnumber');
                    $question->ucidnumber = $creator->idnumber;
                    $modifier = $DB->get_record('user', array('id'=>$question->modifiedby), 'id, idnumber');
                    $question->umidnumber = $modifier->idnumber;
                    if($question->creatoridnumber <> $question->ucidnumber ||
                        $question->modifieridnumber <> $question->umidnumber ||
                        !$question->creatoridnumber ||  !$question->modifieridnumber) {

                        if($oldquestioncontextid = $this->save_update_question_users($question, $oldquestioncontextid, $editor)) {
                            $qlist[$question->id] = $question->id;
                        }
                    }
                } elseif($useraction == 'restore') {
                    if($oldquestioncontextid != $question->contextid) {
                        $editor = $this->get_context_editor($question);
                    }
                    if($question->creatoridnumber && $creator = $DB->get_record('user', array('idnumber'=>$question->creatoridnumber), 'id, idnumber')) {
                        $question->ucid = $creator->id;
                    }
                    if($question->modifieridnumber && $modifier = $DB->get_record('user', array('idnumber'=>$question->modifieridnumber), 'id, idnumber')) {
                        $question->umid = $modifier->id;
                    }

                    if(($question->creatoridnumber && ($question->createdby <> $creator->id) && ($question->createdby <> $editor->id)) ||
                        ($question->modifieridnumber && ($question->modifiedby <> $modifier->id) && ($question->modifiedby <> $editor->id))) {

                        if($oldquestioncontextid = $this->restore_update_question_users($question)) {
                            $qlist[$question->id] = $question->id;
                        }
                    }
                }
            }
            $rs_questions->close();

            if($useraction == 'save') {
                $message = ' save userid to idnumber ';
            } elseif($useraction == 'restore') {
                $message = ' restore idnumber to userid ';
            }
            if($qlist) {
                $message .= ' on qids: '.implode(',', $qlist);
            }
        }
        
        return $message;
    }

    
    /**
    * Apply validation settings for a single question()
    * @param object $questions recordser
    * @param int $validatin either validating or unvalidating 0: remove validation, 1: set validate tag; 2: set rejected tag; 3: set not reviewed tag
    * @return bool success
    */
    function releasequestions_apply_validation($sql, $params, $validation) {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot.'/tag/lib.php');
        require_once($CFG->dirroot.'/mod/quiz/report/makeexam/lib.php');

        $rs_questions = $DB->get_recordset_sql($sql, $params);
        $message = 'No questions';
        $qlist = array();

        if($rs_questions->valid()) {
            quiz_makeexam_install_official_tags();

            $tagvalidated = tag_get_id(get_string('tagvalidated', 'quiz_makeexam'));
            $tagrejected  = tag_get_id(get_string('tagrejected', 'quiz_makeexam'));
            $tagnoreview  = tag_get_id(get_string('tagunvalidated', 'quiz_makeexam'));

            $tagid = 0;
            if($validation == 1) {
                $tagid = $tagvalidated;
            } elseif($validation == 2) {
                $tagid = $tagrejected;
            } elseif($validation == 3) {
                $tagid = $tagnoreview;
            }

            foreach($rs_questions as $question) {
                // remove any previous validation
                if($validation >= 0 ) {
                    $select = " component = 'core_question' AND itemtype = 'question' AND  itemid = ?
                                AND (tagid = ? OR tagid = ? OR tagid = ? )";
                    $params = array($question->id, $tagvalidated, $tagrejected, $tagnoreview);
                    if(($validation > 0) && $tagid) {
                        $select .= ' AND tagid <> ? ';
                        $params[] = $tagid;
                    }
                    if($DB->delete_records_select('tag_instance', $select, $params)) {
                        $qlist[$question->id] = $question->id;
                    }

                }

                // set the tag if not exists
                if($tagid && !$DB->record_exists('tag_instance', array('tagid'=>$tagid, 'itemid'=>$question->id, 'itemtype'=>'question'))) {
                    if($success = tag_assign('question', $question->id, $tagid, 0, $USER->id, 'core_question', $question->contextid)) {
                        $qlist[$question->id] = $question->id;
                    }
                }
            }
            $rs_questions->close();

            if($qlist) {
                $message = $validation ? ' tag set ' : ' tags removed ';
                $message .= ' on qids: '.implode(',', $qlist);
            } else {
                $message = ' no action ';
            }
        }
        return $message;
    }

   
    public function apply_visibility($where, $params, $hidden) {
        global $DB;

        $result = array(true=>'-OK', false=>'-Fail');

        $sql = "UPDATE {question} q
                JOIN {question_categories} qc ON q.category = qc.id
                SET q.hidden = :hide ";
        $params['hide'] = $hidden;
        $success = $DB->execute($sql.$where, $params);
        return " questions hidden {$hidden} ".$result[$success];
    }
    
    
    function get_context_editor($question) {
        $editor = false;
        $config = get_config('tool_backuprestore');
        $qcontext = context::instance_by_id($question->contextid);
        $context = $qcontext->get_course_context();
        if(!$editors = get_role_users($config->coordinatorrole, $context, false, 'u.id, u.idnumber, ra.timemodified, ra.sortorder', 'ra.sortorder ASC, ra.timemodified DESC', false, '', 0, 1)) {
            $editors = get_enrolled_users($context, 'moodle/question:editall', 0, 'u.id, u.idnumber', ' u.id ASC ', 0, 1, true);
        }
        if($editors) {
            $editor = reset($editors);
        }
        return $editor;
    }


    function save_update_question_users($q, $oldqctxtid, $editor) {
        global $CFG, $DB;

        $update = false;
        $qcontext = context::instance_by_id($q->contextid);
        if(is_siteadmin($q->createdby) && !is_enrolled($qcontext, $q->createdby, 'moodle/question:editall')) {
            // admin user not enrolled, change to editor
            if($editor) {
                $q->creatoridnumber = $editor->idnumber;
                $update = true;
            }
        } else {
            // regular user, just set qidnumber to user idnumber
            if($q->ucidnumber) {
                $q->creatoridnumber = $q->ucidnumber;
                $update = true;
            }
        }

        if(is_siteadmin($q->modifiedby) && !is_enrolled($qcontext, $q->modifiedby, 'moodle/question:editall')) {
            // admin user not enrolled, change to editor
            if($editor) {
                $q->modifieridnumber = $editor->idnumber;
                $update = true;
            }
        } else {
            // regular user, just set qidnumber to user idnumber
            if($q->umidnumber) {
                $q->modifieridnumber = $q->umidnumber;
                $update = true;
            }
        }
        if($update) {
            $uq = new stdClass();
            $uq->creatoridnumber = $q->creatoridnumber;
            $uq->modifieridnumber = $q->modifieridnumber;
            if($q->uqid) {
                $uq->id = $q->uqid;
                $DB->update_record('local_ulpgccore_questions', $uq);
            } else {
                $uq->qsource = $CFG->wwwroot;
                $uq->sourceqid = $q->id;
                $DB->insert_record('local_ulpgccore_questions', $uq);
            }
        }
        return $q->contextid;
    }


    function restore_update_question_users($q) {
        global $DB;
        $update = false;
        $qcontext = context::instance_by_id($q->contextid);
        
        if(isset($q->ucid) && $q->ucid) {
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
        }

        if(isset($q->umid) && $q->umid) {
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
        }
        
        if($update) {
            $DB->update_record('question', $q);
        }
            return $q->contextid;
    }    
    
}
