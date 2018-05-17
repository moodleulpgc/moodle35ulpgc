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
 * Meta category enrolment plugin.
 *
 * @package    enrol
 * @subpackage metapattern
 * @copyright  2012 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * ENROL_METAPATTERN_GROUP_BY_xxxx constants for automatically creating a group for a category named after category parameter.
 */
define('ENROL_METAPATTERN_GROUP_BY_NONE', 0);
define('ENROL_METAPATTERN_GROUP_BY_SHORTNAME', -1);
define('ENROL_METAPATTERN_GROUP_BY_IDNUMBER', -2);
define('ENROL_METAPATTERN_GROUP_BY_CTYPE', -3);
define('ENROL_METAPATTERN_GROUP_BY_TERM', -4);
define('ENROL_METAPATTERN_GROUP_BY_CATIDNUMBER', -5);
define('ENROL_METAPATTERN_GROUP_BY_DEGREE', -6);
define('ENROL_METAPATTERN_GROUP_BY_FACULTY', -7);

/**
 * Meta course enrolment plugin.
 * @author Petr Skoda
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_metapattern_plugin extends enrol_plugin {

    /**
     * Returns localised name of enrol instance
     *
     * @param stdClass $instance (null is accepted too)
     * @return string
     */
    public function get_instance_name($instance) {
        global $DB;

        if (empty($instance)) {
            $enrol = $this->get_name();
            return get_string('pluginname', 'enrol_'.$enrol);
        } else if (empty($instance->name)) {
            $enrol = $this->get_name();
            $name = '';
            if($instance->customchar1) {
                $name .= $instance->customchar1;
            }
            if($instance->customchar2) {
                $name .= " [{$instance->customchar2}]";
            }
            return get_string('pluginname', 'enrol_'.$enrol) . ' (' . format_string($name) . ')';
        } else {
            return format_string($instance->name);
        }
    }

    /**
     * Returns true if we can add a new instance to this course.
     *
     * @param int $courseid
     * @return boolean
     */
    public function can_add_instance($courseid) {
        $context = context_course::instance($courseid, MUST_EXIST);
        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/metapattern:config', $context)) {
            return false;
        }
        // multiple instances supported - multiple parent courses linked
        return true;
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
     * Gets an array of the user enrolment actions
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
        if ($this->allow_unenrol_user($instance, $ue) && has_capability('enrol/metapattern:unenrol', $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        return $actions;
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
        global $CFG, $DB;
        // nothing to do here, delegate to cron, if needed
    }

    /**
     * Add new instance of enrol plugin.
     * @param object $course
     * @param array $fields instance fields
     * @return int id of last instance, null if can not be created
     */
    public function add_instance($course, array $fields = null) {
        global $CFG;

        if($fields['customtext1'] && is_array($fields['customtext1'])) {
            $fields['customtext1'] = implode(',', $fields['customtext1']);
        }
        
        $result = parent::add_instance($course, $fields);
        
        require_once("$CFG->dirroot/enrol/metapattern/locallib.php");
        $trace = new null_progress_trace();
        enrol_metapattern_sync($course->id);
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
        global $CFG, $DB;

        require_once("$CFG->dirroot/enrol/metapattern/locallib.php");

        if(is_array($data->customchar1)) {
            $data->customchar1 = reset($data->customchar1);
        }

        if($data->customtext1 && is_array($data->customtext1)) {
            $data->customtext1 = implode(',', $data->customtext1);
        }
        
        $result = parent::update_instance($instance, $data);

        enrol_metapattern_sync($instance->courseid);
        
        return $result;
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

        require_once("$CFG->dirroot/enrol/metapattern/locallib.php");
        enrol_metapattern_sync($instance->courseid);
    }




    /**
     * Called for all enabled enrol plugins that returned true from is_cron_required().
     * @return void
     */
    public function cron() {
        global $CFG;

        require_once("$CFG->dirroot/enrol/metapattern/locallib.php");
        $verbose = debugging('', DEBUG_DEVELOPER);
        enrol_metapattern_sync(null,  $verbose);
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/metapattern:config', $context);
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/metapattern:config', $context);
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
     * Return an array of valid options for the groups.
     *
     * @param context $coursecontext
     * @return array
     */
    protected function get_group_options($coursecontext) {
        global $DB, $USER; 
        
        $groups = array(0 => get_string('none'));
        $courseid = $coursecontext->instanceid;
        $canmanage = has_capability('moodle/course:managegroups', $coursecontext);
        $accessall = has_capability('moodle/site:accessallgroups', $coursecontext);
        if ($canmanage) {
            if($ulpgc = get_config('local_ulpgccore', 'enabledadminmods')) {
               $groups[ENROL_METAPATTERN_GROUP_BY_CATIDNUMBER] = get_string('gsyncbycatidnumber', 'enrol_metapattern');
               $groups[ENROL_METAPATTERN_GROUP_BY_DEGREE] = get_string('gsyncbydegree', 'enrol_metapattern');
               $groups[ENROL_METAPATTERN_GROUP_BY_FACULTY] = get_string('gsyncbyfaculty', 'enrol_metapattern');
               $groups[ENROL_METAPATTERN_GROUP_BY_CTYPE] = get_string('gsyncbyctype', 'enrol_metapattern');
               $groups[ENROL_METAPATTERN_GROUP_BY_TERM] = get_string('gsyncbyterm', 'enrol_metapattern');
            }
            
            $groups[ENROL_METAPATTERN_GROUP_BY_IDNUMBER] = get_string('gsyncbyidnumber', 'enrol_metapattern');
            $groups[ENROL_METAPATTERN_GROUP_BY_SHORTNAME] = get_string('gsyncbyshortname', 'enrol_metapattern');
            $groups[ENROL_METAPATTERN_GROUP_BY_NONE] = get_string('none');
        }
       
        $userid = ($canmanage || $accessall) ? 0 : $USER->id;
       
        foreach (groups_get_all_groups($courseid, $userid, 0, 'g.id, g.name') as $group) {
            $groups[$group->id] = format_string($group->name, true, array('context' => $coursecontext));
        }
        return $groups;
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

        $groups = $this->get_group_options($coursecontext);
        
        
        // customchar1 : store pattern meta linked 
        $mform->addElement('text', 'customchar1', get_string('linkedpattern', 'enrol_metapattern') );
        $mform->setType('customchar1', PARAM_TEXT);
        $mform->addRule('customchar1', null, 'required');
        $mform->addHelpButton('customchar1', 'linkedpattern', 'enrol_metapattern');
        if (!empty($instance->id)) {
            $mform->freeze('customchar1');
        }

        // customchar2 : field to search pattern on
        $fields = array('fullname'=>get_string('fullname'), 'shortname'=>get_string('shortname'),
                        'idnumber'=>get_string('idnumbercourse'));
        if($ulpgc = get_config('local_ulpgccore', 'enabledadminmods')) {
           $fields['ctype'] = get_string('ctype', 'enrol_metapattern');
        }
        $mform->addElement('select', 'customchar2', get_string('linkedfield', 'enrol_metapattern'), $fields);
        $mform->setDefault('customchar2', 'idnumber');
        $mform->addRule('customchar2', null, 'required');
        $mform->addHelpButton('customchar2', 'linkedfield', 'enrol_metapattern');

        
        $plugin = enrol_get_plugin('metapattern');
        $skiproles = $plugin->get_config('nosyncroleids', '');
        $skiproles = empty($skiproles) ? array() : explode(',', $skiproles);
        $allroles = array();
        $roles = get_all_roles();
        $roles = role_fix_names($roles, null, ROLENAME_ORIGINAL, false);
        foreach ($roles as $role) {
                $rolename = strip_tags(format_string($role->localname)) . ' ('. $role->shortname . ')';
                $allroles[$role->id] = $rolename;
        }

        if($skiproles) {
            foreach($skiproles as $roleid) {
                unset($allroles[$roleid]);
            }
        }
        
        // customtext1 : roles to search and include users having them
        $rolemenu = &$mform->addElement('select', 'customtext1', get_string('syncroles', 'enrol_metapattern'), $allroles, 'size="5"');
        $rolemenu->setMultiple(true);
        $mform->addRule('customtext1', null, 'required');

        // customint2 : those users enrolled here as 
        $allroles[0] = get_string('synchronize', 'enrol_metacat');
        $mform->addElement('select', 'customint2', get_string('enrolledas', 'enrol_metapattern'), $allroles);
        $mform->setDefault('customint2', 0);
        $mform->addHelpButton('customint2', 'enrolledas', 'enrol_metacat');
        
        // customint3 : group to add users to
        $mform->addElement('select', 'customint3', get_string('syncgroup', 'enrol_metapattern'), $groups);
        $mform->addHelpButton('customint3', 'syncgroup', 'enrol_metacat');
    }

    /**
     * Perform custom validation of the data used to edit the instance.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @param object $instance The instance loaded from the DB
     * @param context $context The context of the instance we are editing
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK.
     * @return void
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        global $DB;
        $errors = array();
        $thiscourseid = $context->instanceid;


        $validgroups = array_keys($this->get_group_options($context));

        $tovalidate = array(
            'customint3' => $validgroups
        );
        $typeerrors = $this->validate_param_types($data, $tovalidate);
        $errors = array_merge($errors, $typeerrors);

        return $errors;
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
            // No meta restore from other sites.
            $step->set_mapping('enrol', $oldid, 0);
            //return;
        }

        if ($data->customint3 > 0 ) {
            $data->customint3 = $step->get_mappingid('group', $data->customint3);
        }

        $instance = $DB->get_record('enrol', array('roleid' => $data->roleid, 'customchar1' => $data->customchar1,
            'courseid' => $course->id, 'enrol' => $this->get_name()));
        if ($instance) {
            $instanceid = $instance->id;
        } else {
            $instanceid = $this->add_instance($course, (array)$data);
        }
        $step->set_mapping('enrol', $oldid, $instanceid);

        require_once("$CFG->dirroot/enrol/metapattern/locallib.php");
        $trace = new text_progress_trace();
        enrol_metapattern_sync($course->id);
        $trace->finished();
    }
}

