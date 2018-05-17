<?php

/**
 * This file contains block_usermanagement class
 *
 * @package    block_usermanagement
 * @copyright  2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This class is for a block which defines a block for display on
 * any Moodle page.
 */
 class block_usermanagement extends block_list {

    function init() {
        $this->title = get_string('blocktitle', 'block_usermanagement');
    }

    function has_config() {
        return true;
    }

    /**
     * All multiple instances of this block
     * @return bool Returns false
     */
    function instance_allow_multiple() {
        return false;
    }

    /**
     * Set the applicable formats for this block to all
     * @return array
     */
    function applicable_formats() {
        return array('site-index' => true, 'my'=>true, 'course' => true);
    }

    /**
     * Allow the user to configure a block instance
     * @return bool Returns true
     */
    function instance_allow_config() {
        return false;
    }

    /**
     * The navigation block cannot be hidden by default as it is integral to
     * the navigation of Moodle.
     *
     * @return false
     */
    function  instance_can_be_hidden() {
        return false;
    }

    function user_can_addto($page) {
        // Don't allow people to add the block if they can't even use it
        if (!has_capability('block/usermanagement:view', $page->context)) {
            return false;
        }

        return parent::user_can_addto($page);
    }


    function get_content() {
        global $CFG, $USER, $DB, $OUTPUT;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content = '';
        }

        //require_once($CFG->dirroot."/blocks/usermanagement/locallib.php");

        $pagetype = $this->page->pagetype;
        $course = $this->page->course;
        $systemcontext = context_system::instance();
        $context = $this->page->context;

        $canview = has_capability('block/usermanagement:view', $context);
        if(!$canview) {
            return $this->content = ''; // students & others fast abort
        }

        $canmanage = has_capability('block/usermanagement:manage', $context);

        //$icon  = '<img src="' . $OUTPUT->pix_url('i/course') . '" class="icon" alt="" />&nbsp;';

        if($canview) {
            $this->content->items[] = "<a href=\"".$CFG->wwwroot."/blocks/usermanagement/usereports.php\" >".get_string('usereports', 'block_usermanagement')."</a>";
            $this->content->icons[] = '<img src="' . $OUTPUT->pix_url('i/report') . '" class="icon" alt="" />&nbsp;';
        }

        if($canmanage) {
            $this->content->items[] = '<hr />'.get_string('management', 'block_usermanagement');
            $this->content->icons[] = '';

            $this->content->items[] = "<a href=\"".$CFG->wwwroot."/blocks/usermanagement/manageusers.php\" >".get_string('usermanagement', 'block_usermanagement')."</a>";
            $this->content->icons[] = '<img src="' . $OUTPUT->pix_url('i/cohort') . '" class="icon" alt="" />&nbsp;';
           
            if(has_capability('local/ulpgccore:manage', $systemcontext)) {
                $this->content->items[] = "<a href=\"".$CFG->wwwroot."/group/index.php?id=1\" >".get_string('frontpagegroups', 'block_usermanagement')."</a>";
                $this->content->icons[] = '<img src="' . $OUTPUT->pix_url('i/group') . '" class="icon" alt="" />&nbsp;';

            }
            if(has_capability('local/ulpgccore:upload', $systemcontext)) {
                $this->content->items[] = "<a href=\"".$CFG->wwwroot."/local/ulpgccore/anadir_manual.php\" >".get_string('addmanual', 'block_usermanagement')."</a>";
                $this->content->icons[] = '<img src="' . $OUTPUT->pix_url('i/files') . '" class="icon" alt="" />&nbsp;';
            }
        }

        return $this->content;
    }

    /**
     * Performa cron tasks for this block
     * @return bool Returns true
     */
    function cron() {
        global $CFG, $DB;
    /// we have one initial $status
        $status = true;
        require_once($CFG->dirroot.'/group/lib.php');
        $config = get_config('block_usermanagement');

        if (!empty($config->enablegroupssync)) {
            $timetocheck  = time();

            //$timetocheck  = stats_get_base_daily() + $config->statsruntimestarthour*60*60 + $config->statsruntimestarthour*60;
            $timestart  = usergetmidnight($timetocheck) + $config->runtimestarthour*60*60 + $config->runtimestarthour*60;

            if((($timetocheck >= $timestart) && ($timetocheck < $timestart + 3600 * 1.5 )) OR debugging('', DEBUG_DEVELOPER)) {
            //if(1) {
            /// OK we can proceed to synchronize user groups in site course
                mtrace("\nStarting site course groups synchronization");
                $select = " courseid = :courseid AND ".$DB->sql_like('enrolmentkey', ':enrolmentkey');
                if($groups = $DB->get_records_select('groups', $select, array('courseid'=>SITEID, 'enrolmentkey'=>$config->enrolmentkey.'%'))) {
                    list($insql, $inparams) = $DB->get_in_or_equal(array_keys($groups));
                    $sql = "SELECT u.id, u.idnumber
                                FROM {user} u
                                WHERE  NOT EXISTS (SELECT gm.id FROM {groups_members} gm WHERE gm.userid = u.id AND gm.groupid $insql )";
                    //addding new users
                    $count = 0;
                    if($users = $DB->get_records_sql($sql, $inparams)) {
                        foreach($users as $user) {
                            foreach($groups as $group) {
                                if($group->idnumber) {
                                    $field = 'roles_'.$group->idnumber;
                                    $roles = explode(',', $config->$field);
                                    $insql = '';
                                    $inparams = array();
                                    list($insql, $params) = $DB->get_in_or_equal($roles);
                                    array_unshift($params, $user->id);
                                    if($DB->record_exists_select('role_assignments', " userid = ? AND roleid $insql ", $params)) {
                                    /// the user has role for this group, add to it
                                        $success = groups_add_member($group, $user, 'block_usermanagement', 0);
                                        if($success) {
                                            $count++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if($count) {
                        mtrace(" ... Added $count group enrolments ");
                    }
                }
            }
        }
      /// And return $status
        return $status;
    }
}

