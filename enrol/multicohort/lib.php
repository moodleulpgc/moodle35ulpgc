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
 * multicohort enrolment plugin.
 *
 * @package    enrol_multicohort
 * @copyright  2016 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * multicohort_CREATEGROUP constant for automatically creating a group for a multicohort.
 */
 
define('MULTICOHORT_CREATE_GROUP', -1);
define('MULTICOHORT_MULTIPLE_GROUP', -2);
define('MULTICOHORT_KEEP_GROUP', -3);
define('MULTICOHORT_ENROLGROUPS', 0);
define('MULTICOHORT_ONLYGROUPS', 1);
define('MULTICOHORT_ROLEGROUPS', 2);


/**
 * multicohort enrolment plugin implementation.
 * @author Petr Skoda
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_multicohort_plugin extends enrol_plugin {

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/multicohort:config', $context);
    }

    /**
     * Returns localised name of enrol instance.
     *
     * @param stdClass $instance (null is accepted too)
     * @return string
     */
    public function get_instance_name($instance) {
        global $DB;
        
        $enrol = $this->get_name();
        
        
        if (empty($instance)) {
            return get_string('pluginname', 'enrol_'.$enrol);

        } else {
            $rolename = '';
            $noenrolgroups = '';
            if($instance->customint4 != MULTICOHORT_ENROLGROUPS) {
                $noenrolgroups = '-' . get_string('groups') ;
            }
        
            if(!isset($instance->name)) {
                $instance->name = $enrol;
            }
            if ($role = $DB->get_record('role', array('id'=>$instance->roleid))) {
                if($instance->customint4 != MULTICOHORT_ONLYGROUPS) {
                    $rolename = '-' . role_get_name($role, context_course::instance($instance->courseid, IGNORE_MISSING));
                }
                return format_string($instance->name, true, array('context'=>context_course::instance($instance->courseid))) . 
                                    ' (' . get_string('pluginname', 'enrol_'.$enrol) . $noenrolgroups . $rolename . ')';
            } else {
                return format_string($instance->name, true, array('context'=>context_course::instance($instance->courseid))) . 
                                    ' (' . get_string('pluginname', 'enrol_'.$enrol) . $noenrolgroups . ')';

            }
        }
    }

    /**
     * Given a courseid this function returns true if the user is able to enrol or configure multicohorts.
     * AND there are multicohorts that the user can view.
     *
     * @param int $courseid
     * @return bool
     */
    public function can_add_instance($courseid) {
        global $CFG;
        require_once($CFG->dirroot . '/cohort/lib.php');
        $coursecontext = context_course::instance($courseid);
        if (!has_capability('moodle/course:enrolconfig', $coursecontext) or !has_capability('enrol/multicohort:config', $coursecontext)) {
            return false;
        }
        return cohort_get_available_cohorts($coursecontext, 0, 0, 1) ? true : false;
    }

    /**
     * Add new instance of enrol plugin.
     * @param object $course
     * @param array $fields instance fields
     * @return int id of new instance, null if can not be created
     */
    public function add_instance($course, array $fields = null) {
        global $CFG, $DB;

        foreach(array('customtext1', 'customtext2', 'customtext3') as $field) {
            if (!empty($fields[$field])) {
                $fields[$field] = implode(',', $fields[$field]);
            } else {
                $fields[$field] = '';
            }
        }

        if($result = parent::add_instance($course, $fields)) {
            if ($fields['customint2'] < 0) {
                if($instance = $DB->get_record('enrol', array('id'=>$result))) {
                    $instance->customtext4 = enrol_multicohort_create_multiple_groups($instance);
                    if($fields['customint2'] == MULTICOHORT_MULTIPLE_GROUP) {
                        $DB->update_record('enrol', $instance);
                    }
                }
            }
        }

        require_once("$CFG->dirroot/enrol/multicohort/locallib.php");
        $trace = new null_progress_trace();
        enrol_multicohort_sync($trace, $course->id);
        $trace->finished();

        return $result;
    }

    /**
     * Update instance of enrol plugin.
     * @param stdClass $instance
     * @param stdClass $data modified instance fields
     * @return boolean
     */
    public function update_instance($instance, $data) {
        global $CFG;

        // NOTE: no multicohort changes here!!!
        $context = context_course::instance($instance->courseid);
        if ($data->roleid != $instance->roleid) {
            // The sync script can only add roles, for perf reasons it does not modify them.
            $params = array(
                'contextid' => $context->id,
                'roleid' => $instance->roleid,
                'component' => 'enrol_multicohort',
                'itemid' => $instance->id
            );
            role_unassign_all($params);
        }

        if ($data->customint2 < 0) {
            // Create a new group for the multicohort if requested.
            $syncgroup = enrol_multicohort_create_multiple_groups($instance);
        
            if ($data->customint2 == MULTICOHORT_MULTIPLE_GROUP) {
                $data->customtext4 = $syncgroup;
            }
        }
        
        $result = parent::update_instance($instance, $data);

        require_once("$CFG->dirroot/enrol/multicohort/locallib.php");
        $trace = new null_progress_trace();
        enrol_multicohort_sync($trace, $instance->courseid);
        $trace->finished();

        return $result;
    }

    /**
    * Delete course enrol plugin instance, unenrol all users.
    * @param object $instance
    * @return void
    */
    public function delete_instance($instance) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/group/lib.php');
        
        //parent::delete_instance($instance);
        
        // now delete all instance-created groups
        $idnumber = 'multicohort\_'.$instance->id.'\_cohort%';
        
        $select = $DB->sql_like('idnumber', ':idnumber');
        $params = array('idnumber'=>$idnumber);
        if($groups = $DB->get_records_select('groups', $select, $params)) {
            foreach($groups as $group) {
                 groups_delete_group($group);
            }
        }
        
        
        
        // Invalidate all enrol caches.
        $context = context_course::instance($instance->courseid);
        $context->mark_dirty();
        
        parent::delete_instance($instance);
    }
    
    /**
     * Called for all enabled enrol plugins that returned true from is_cron_required().
     * @return void
     */
    public function cron() {
        global $CFG;

        require_once("$CFG->dirroot/enrol/multicohort/locallib.php");
        $trace = new text_progress_trace();
        //$trace->output("Disabled because errors");
        enrol_multicohort_sync($trace);
        $trace->finished();
    }

    /**
     * Called after updating/inserting course.
     *
     * @param bool $inserted true if course just inserted
     * @param stdClass $course
     * @param stdClass $data form data
     * @return void
     */
    public function course_updated($inserted, $course, $data) {
        // It turns out there is no need for multicohorts to deal with this hook, see MDL-34870.
    }

    /**
     * Update instance status
     *
     * @param stdClass $instance
     * @param int $newstatus ENROL_INSTANCE_ENABLED, ENROL_INSTANCE_DISABLED
     * @return void
     */
    public function update_status($instance, $newstatus) {
        global $CFG;

        parent::update_status($instance, $newstatus);

        require_once("$CFG->dirroot/enrol/multicohort/locallib.php");
        $trace = new null_progress_trace();
        enrol_multicohort_sync($trace, $instance->courseid);
        $trace->finished();
    }

    /**
     * Does this plugin allow manual unenrolment of a specific user?
     * Yes, but only if user suspended...
     *
     * @param stdClass $instance course enrol instance
     * @param stdClass $ue record from user_enrolments table
     *
     * @return bool - true means user with 'enrol/xxx:unenrol' may unenrol this user, false means nobody may touch this user enrolment
     */
    public function allow_unenrol_user(stdClass $instance, stdClass $ue) {
        if ($ue->status == ENROL_USER_SUSPENDED) {
            return true;
        }

        return false;
    }

    /**
     * Gets an array of the user enrolment actions.
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue A user enrolment object
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol_user($instance, $ue) && has_capability('enrol/multicohort:unenrol', $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }

    /**
     * Restore instance and map settings.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $course
     * @param int $oldid
     */
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
        global $DB, $CFG;

        if (!$step->get_task()->is_samesite()) {
            // No multicohort restore from other sites.
            $step->set_mapping('enrol', $oldid, 0);
            //return;
        }

        if ($data->customint2 > 0) {
            $data->customint2 = $step->get_mappingid('group', $data->customint2);
        }

        $used = $data->customtext1;
        if($data->customtext2) {
            $used .= ','.$data->customtext2;
        }
        if($data->customtext3) {
            $used .= ','.$data->customtext3;
        }
        $used = explode(',', $used);
        $cohortexists = true;
        foreach($used as $cid) {
            $cohortexists = $DB->record_exists('cohort', array('id'=>$cid));
            if(!$cohortexists) {
                break;
            }
        }

        $select = 'roleid = :roleid  AND courseid = :courseid AND enrol = :enrol ';
        foreach(array('customtext1', 'customtext2', 'customtext3') as $field) {
            $select .= ' AND ' . $DB->sql_compare_text($field) . " = :$field ";
        }
        if ($data->roleid && $cohortexists) {
            $instance = $DB->get_record_select('enrol', $select, array('roleid'=>$data->roleid, 'courseid'=>$course->id, 'enrol'=>$this->get_name(),
                                                                        'customtext1'=>$data->customtext1, 'customtext2'=>$data->customtext2, 'customtext3'=>$data->customtext3));
            if ($instance) {
                $instanceid = $instance->id;
            } else {
                $instanceid = $this->add_instance($course, (array)$data);
            }
            $step->set_mapping('enrol', $oldid, $instanceid);

            require_once("$CFG->dirroot/enrol/multicohort/locallib.php");
            $trace = new null_progress_trace();
            enrol_multicohort_sync($trace, $course->id);
            $trace->finished();

        } else if ($this->get_config('unenrolaction') == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
            $select .= ' AND customint1 = :customint1 ';
            $data->customint1 = 0;
            $instance = $DB->get_record('enrol', $select, array('roleid'=>$data->roleid, 'customint1'=>$data->customint1, 'courseid'=>$course->id, 
                                                        'customtext1'=>$data->customtext1, 'customtext2'=>$data->customtext2, 'customtext3'=>$data->customtext3, 
                                                        'enrol'=>$this->get_name()));

            if ($instance) {
                $instanceid = $instance->id;
            } else {
                $data->status = ENROL_INSTANCE_DISABLED;
                $instanceid = $this->add_instance($course, (array)$data);
            }
            $step->set_mapping('enrol', $oldid, $instanceid);

            require_once("$CFG->dirroot/enrol/multicohort/locallib.php");
            $trace = new null_progress_trace();
            enrol_multicohort_sync($trace, $course->id);
            $trace->finished();

        } else {
            $step->set_mapping('enrol', $oldid, 0);
        }
    }

    /**
     * Restore user enrolment.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $instance
     * @param int $oldinstancestatus
     * @param int $userid
     */
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
        global $DB;

        if ($this->get_config('unenrolaction') != ENROL_EXT_REMOVED_SUSPENDNOROLES) {
            // Enrolments were already synchronised in restore_instance(), we do not want any suspended leftovers.
            return;
        }

        // ENROL_EXT_REMOVED_SUSPENDNOROLES means all previous enrolments are restored
        // but without roles and suspended.

        if (!$DB->record_exists('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid))) {
            $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, ENROL_USER_SUSPENDED);
        }
    }

    /**
     * Restore user group membership.
     * @param stdClass $instance
     * @param int $groupid
     * @param int $userid
     */
    public function restore_group_member($instance, $groupid, $userid) {
        // Nothing to do here, the group members are added in $this->restore_group_restored()
        return;
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/multicohort:config', $context);
    }

    /**
     * Return an array of valid options for the status.
     *
     * @return array
     */
    protected function get_status_options() {
        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        return $options;
    }

    /**
     * Return an array of valid options for the multicohorts.
     *
     * @param stdClass $instance
     * @param context $context
     * @return array
     */
    protected function get_multicohort_options($instance, $context) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/cohort/lib.php');

        $multicohorts = array();

        if ($instance->id) {
            $used = $instance->customtext1;
            if($instance->customtext2) {
                $used .= ','.$instance->customtext2;
            }
            if($instance->customtext3) {
                $used .= ','.$instance->customtext3;
            }
            $used = explode(',', $used);
            list($insql, $params) = $DB->get_in_or_equal($used);
            $allmulticohorts = $DB->get_records_select('cohort', "id $insql", $params, 
                                                    'name, idnumber', 'id, name, contextid, idnumber, visible'); 
        } else {
            $allmulticohorts = cohort_get_available_cohorts($context, 0, 0, 0);
        }
        
        foreach ($allmulticohorts as $c) {
            $multicohorts[$c->id] = format_string($c->name);
        }
        
        return $multicohorts;
    }

    /**
     * Return an array of valid options for the roles.
     *
     * @param stdClass $instance
     * @param context $coursecontext
     * @return array
     */
    protected function get_role_options($instance, $coursecontext) {
        global $DB;

        $roles = get_assignable_roles($coursecontext);
        $roles[0] = get_string('none');
        $roles = array_reverse($roles, true); // Descending default sortorder.
        if ($instance->id and !isset($roles[$instance->roleid])) {
            if ($role = $DB->get_record('role', array('id' => $instance->roleid))) {
                $roles = role_fix_names($roles, $coursecontext, ROLENAME_ALIAS, true);
                $roles[$instance->roleid] = role_get_name($role, $coursecontext);
            } else {
                $roles[$instance->roleid] = get_string('error');
            }
        }

        return $roles;
    }

    /**
     * Return an array of valid options for the groups.
     *
     * @param context $coursecontext
     * @return array
     */
    protected function get_group_options($coursecontext) {
        $groups = array(0 => get_string('none'));
        //$groups[MULTICOHORT_KEEP_GROUP] = get_string('keepgroups', 'enrol_multicohort');
        if (has_capability('moodle/course:managegroups', $coursecontext)) {
            $groups[MULTICOHORT_CREATE_GROUP] = get_string('creategroup', 'enrol_multicohort');
            $groups[MULTICOHORT_MULTIPLE_GROUP] = get_string('multiplegroup', 'enrol_multicohort');
        }

        foreach (groups_get_all_groups($coursecontext->instanceid) as $group) {
            $groups[$group->id] = format_string($group->name, true, array('context' => $coursecontext));
        }

        return $groups;
    }

    /**
     * We are a good plugin and don't invent our own UI/validation code path.
     *
     * @return boolean
     */
    public function use_standard_editing_ui() {
        return true;
    }

    /**
     * Add elements to the edit instance form.
     *
     * @param stdClass $instance
     * @param MoodleQuickForm $mform
     * @param context $coursecontext
     * @return bool
     */
    public function edit_instance_form($instance, MoodleQuickForm $mform, $coursecontext) {
        global $DB;

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $options = $this->get_status_options();
        $mform->addElement('select', 'status', get_string('status', 'enrol_multicohort'), $options);

        $options = $this->get_multicohort_options($instance, $coursecontext);
        $size = count($options);
        if($size >= 8) {
            $size = 8;
        }
        
        $select = $mform->addElement('select', 'customtext1', get_string('oranycohorts', 'enrol_multicohort'), $options);
        $mform->addHelpButton('customtext1', 'oranycohorts', 'enrol_multicohort');
        $select->setMultiple(true);
        $select->setSize($size);
        
        $select = $mform->addElement('select', 'customtext2', get_string('andallcohorts', 'enrol_multicohort'), $options);
        $mform->addHelpButton('customtext2', 'andallcohorts', 'enrol_multicohort');
        $select->setMultiple(true);
        $select->setSize($size);
      
        $select = $mform->addElement('select', 'customtext3', get_string('notcohorts', 'enrol_multicohort'), $options);
        $mform->addHelpButton('customtext3', 'notcohorts', 'enrol_multicohort');
        $select->setMultiple(true);
        $select->setSize($size);
        
        $andor = array(0=>get_string('notor', 'enrol_multicohort'), 1=>get_string('notand', 'enrol_multicohort') );
        $mform->addElement('select', 'customint1', get_string('andornotcohorts', 'enrol_multicohort'), $andor);
        $mform->addHelpButton('customint1', 'andornotcohorts', 'enrol_multicohort');
        
        if ($instance->id) {
            $mform->hardFreeze('customtext1', $instance->customtext1);
            $mform->hardFreeze('customtext2', $instance->customtext2);
            $mform->hardFreeze('customtext3', $instance->customtext3);
            $mform->hardFreeze('customint1', $instance->customint1);
        } else {
            $mform->addRule('customtext1', get_string('required'), 'required', null, 'client');
        }
        
        $roles = $this->get_role_options($instance, $coursecontext);
        $mform->addElement('select', 'roleid', get_string('assignrole', 'enrol_multicohort'), $roles);
        $mform->setDefault('roleid', $this->get_config('roleid'));
        $groups = $this->get_group_options($coursecontext);

        $mform->addElement('select', 'customint2', get_string('addgroup', 'enrol_multicohort'), $groups);
        $mform->addHelpButton('customint2', 'addgroup', 'enrol_multicohort');
        
        
        $mform->addElement('static', 'explain1', '', get_string('noenrolgroups', 'enrol_multicohort'));
        $options = array(MULTICOHORT_ENROLGROUPS => get_string('enrolgroups', 'enrol_multicohort'),
                         MULTICOHORT_ONLYGROUPS => get_string('onlygroups', 'enrol_multicohort'),
                         MULTICOHORT_ROLEGROUPS => get_string('rolegroups', 'enrol_multicohort'),
                         );
                         
        $mform->addElement('select', 'customint4', get_string('assigngroupmode', 'enrol_multicohort'), $options);
        $mform->addHelpButton('customint4', 'assigngroupmode', 'enrol_multicohort');
        $mform->disabledIf('customint4', 'customint2', 'eq', 0);       
        
        
    }

    /**
     * Perform custom validation of the data used to edit the instance.
     *
     * @param array $data array of ("fieldname" => value) of submitted data
     * @param array $files array of uploaded files "element_name" => tmp_file_path
     * @param object $instance The instance loaded from the DB
     * @param context $context The context of the instance we are editing
     * @return array of "element_name" => "error_description" if there are errors,
     *         or an empty array if everything is OK.
     * @return void
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        global $DB;
        $errors = array();
        if(!isset($data['customtext2'])) { 
            $data['customtext2'] = '';
        }
        if(!isset($data['customtext3'])) { 
            $data['customtext3'] = '';
        }
        
        $select = array();
        foreach(array('customtext1', 'customtext2', 'customtext3') as $item) {
            if(is_array($data[$item])) {
                $select[$item] = implode(',', $data[$item]);
            } else {
                $select[$item] = $data[$item];
                if($data[$item] && (strpos($data[$item], ',') !== false)) {
                    $data[$item] = explode(',', $data[$item]);
                }
            }
        }
       
        $params = array(
            'roleid' => $data['roleid'],
            'customint1' => $data['customint1'],
            'customtext1' => $select['customtext1'],
            'customtext2' => $select['customtext2'],
            'customtext3' => $select['customtext3'],
            'courseid' => $data['courseid'],
            'id' => $data['id']
        );
        $sql = "roleid = :roleid AND customint1 = :customint1 
                                    AND courseid = :courseid AND enrol = 'multicohort' AND id <> :id";
        foreach(array('customtext1', 'customtext2', 'customtext3') as $field) {
            $sql .= ' AND ' . $DB->sql_compare_text($field) . " = :$field ";
        }
        if ($DB->record_exists_select('enrol', $sql, $params)) {
            $errors['roleid'] = get_string('instanceexists', 'enrol_multicohort');
        }
        $validstatus = array_keys($this->get_status_options());
        $validcohorts = array_keys($this->get_multicohort_options($instance, $context));
        $validcohorts[] = '';
        $validroles = array_keys($this->get_role_options($instance, $context));
        $validgroups = array_keys($this->get_group_options($context));
        
        $tovalidate = array(
            'name' => PARAM_TEXT,
            'status' => $validstatus,
            'customint1' => PARAM_INT,
            'customtext1' => $validcohorts,
            'roleid' => $validroles,
            'customint2' => $validgroups,
            'customint4' => PARAM_INT,
        );
        if(isset($data['customtext2'])) {
            $tovalidate['customtext2'] = $validcohorts;
        } 
        if(isset($data['customtext3'])) {
            $tovalidate['customtext3'] = $validcohorts;
        } 

        
        $typeerrors = $this->validate_param_types($data, $tovalidate);
        $errors = array_merge($errors, $typeerrors);

        return $errors;
    }
    
    /**
    * Validate a list of parameter names and types.
    * @since Moodle 3.1
    *
    * @param array $data array of ("fieldname"=>value) of submitted data
    * @param array $rules array of ("fieldname"=>PARAM_X types - or "fieldname"=>array( list of valid options )
    * @return array of "element_name"=>"error_description" if there are errors,
    *         or an empty array if everything is OK.
    */
    public function validate_param_types($data, $rules) {
        $errors = array();
        foreach($data as $name => $value) {
            if(is_array($value)) {
                $tovalidate = array();
                foreach($value as $key=>$item) {
                    $tovalidate[$key] = $rules[$name];
                }
                $typeerrors = parent::validate_param_types($value, $tovalidate);
                $errors = array_merge($errors, $typeerrors);
                unset($rules[$name]);
            }
        }
        $typeerrors = parent::validate_param_types($data, $rules);
        return array_merge($errors, $typeerrors);
    }
}

/**
 * Prevent removal of enrol roles.
 * @param int $itemid
 * @param int $groupid
 * @param int $userid
 * @return bool
 */
function enrol_multicohort_allow_group_member_remove($itemid, $groupid, $userid) {
    return false;
}

/**
 * Create a new group with the multicohorts name.
 *
 * @param stdclass $instance
 * @return int $groupid Group ID for this multicohort.
 */
function enrol_multicohort_create_multiple_groups($instance) {
    global $DB, $CFG;

    require_once($CFG->dirroot . '/group/lib.php');

    $multiple = ($instance->customint2 == MULTICOHORT_MULTIPLE_GROUP) ? $instance->customtext1 : false; 
    
    $courseid = $instance->courseid;

    $groupdata = new stdClass();
    $groupdata->courseid = $courseid;
    $groupdata->idnumber = 'multicohort_'.$instance->id;
    
    $groups = array();
    if($multiple) {
        $cohorts = $DB->get_records_list('cohort', 'id', explode(',', $multiple), '', 'id, name, idnumber');
        foreach($cohorts as $cohort) {
            $groupdata->name = $cohort->name;
            $groupdata->idnumber = 'multicohort_'.$instance->id.'_cohort_'.$cohort->id;
            $groups[] = clone($groupdata);
        }
    } else {
        $groupdata->name = $instance->name;
        $groupdata->idnumber = 'multicohort_'.$instance->id.'_cohort_pooled';
        $groups[] = clone($groupdata);
    }
    
    $groupids = array();
    foreach($groups as $group) {
        $groupname = $group->name;
        $idnumber = $group->idnumber;
        
        $a = new stdClass();
        $a->name = $groupname;
        $a->increment = '';
        $groupname = trim(get_string('defaultgroupnametext', 'enrol_multicohort', $a));
        $inc = 0;
        // Check to see if the multicohort group name already exists. Add an incremented number if it does.
        while ($DB->record_exists_select('groups', "name = :name AND courseid = :courseid AND idnumber <> :idnumber", 
                        array('name' => $groupname, 'courseid' => $courseid, 'idnumber'=>$idnumber))) {
            $a->increment = '-' . (++$inc) . '';
            $newshortname = trim(get_string('defaultgroupnametext', 'enrol_multicohort', $a));
            $groupname = $newshortname;
        }
        // Create a new group for the multicohort.
        $groupdata = new stdClass();
        $groupdata->courseid = $courseid;
        $groupdata->name = $groupname;
        $groupdata->idnumber = $idnumber;
        
        if(!$oldgroup = groups_get_group_by_idnumber($courseid, $idnumber)) {
            $groupid = groups_create_group($groupdata);
            if($ulpgcgroups = get_config('local_ulpgcgroups')) { 
                local_ulpgcgroups_update_group_component($groupid, 'enrol_multicohort', $instance->id);  
            }
        } else {
            $groupid = $oldgroup->id;
            if($oldgroup->name != $groupname) {
                $DB->set_field('groups', 'name', $groupname, array('id'=>$oldgroup->id));
            }
        }
        
        $groupids[] = $groupid;
    }

    return implode(',', $groupids);
}
