<?php

/**
 * This file contains a local_supervision page
 *
 * @package   local_supervision
 * @copyright 2012 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot."/local/supervision/locallib.php");
require_once($CFG->dirroot."/course/lib.php");

class supervision_editpermission_form extends moodleform {

    function definition() {

        global $CFG, $DB, $USER;

        $mform =& $this->_form;
        $hiddens = $this->_customdata['params'];
        $canmanage = $this->_customdata['canmanage'];
        $canadd = $this->_customdata['canadd'];
        $edit = $this->_customdata['edit'];
        $config = $this->_customdata['config'];

        $assigner = $USER->id;
        if($canadd && !$canmanage) {
            $assigner = $canadd['user'];
        }

       if($hiddens['department']) {
            $itemlist =  array (); /// TODO
            $label = get_string('department');
            $scope = 'department';

        } else {
            $itemlist =  make_categories_options();
            $label = get_string('category');
            $scope = 'category';
        }

        if($canadd && !$canmanage) {
            $useritems = array();
            foreach($canadd as $permission) {
                if(isset($permission->scope) && ($permission->scope == $scope) && isset($itemlist[$permission->instance])) {
                    $useritems[$permission->instance] = $itemlist[$permission->instance];
                }
            }
            $itemlist = $useritems;
        }

        /// TODO if edit : static, or get_data or FREEZE

        $instanceel = &$mform->addElement('select', 'instance', get_string('itemname', 'local_supervision'), $itemlist );
        $mform->setType('instance', PARAM_INT);
        $mform->addRule('instance', null, 'required');

        $userlist = array();  //array(0=>get_string('choosedots'));
        $roles = explode(',', $config->checkedroles);
        $roles[] = $config->checkerrole;
        list($usql, $params) = $DB->get_in_or_equal($roles);

        $names = get_all_user_name_fields(true, 'u');
        $sql = "SELECT ra.id, ra.userid, u.email, ra.roleid, $names
                FROM {role_assignments} ra
                JOIN  {user} u ON ra.userid = u.id
                WHERE ra.roleid $usql GROUP BY ra.userid ORDER BY u.lastname ASC";

        if($users = $DB->get_records_sql($sql, $params)) { // TODO check SQL
            foreach($users as $user) {
                $userlist[$user->userid] = fullname($user, false, 'lastname firstname');
            }
        }

        $userel = &$mform->addElement('select', 'userid', get_string('supervisor', 'local_supervision'), $userlist);
        $mform->setType('userid', PARAM_ALPHANUM);
        $mform->addRule('userid', null, 'required');

        if($edit > 0) {
            //$instanceel->setPersistantFreeze();
            $instanceel->freeze();
            //$userel->setPersistantFreeze();
            $userel->freeze();
        }

        $mform->addElement('hidden', 'scope', $scope);
        $mform->setType('scope', PARAM_TEXT);

        // review warning
        $mform->addElement('selectyesno', 'review', get_string('review', 'local_supervision'));
        $mform->setDefault('review', 1);


        $plugins = get_plugin_list('supervisionwarning');
        foreach ($plugins as $plugin => $plugindir) {
            $warnings[$plugin] = get_string('pluginname', 'supervisionwarning_'.$plugin);
        }

        $select = &$mform->addElement('select', 'warnings', get_string('supervisionwarnings', 'local_supervision'), $warnings );
        $select->setMultiple(true);
        $select->setSelected(array_keys($warnings));
        $mform->addRule('warnings', null, 'required');

        $mform->addElement('hidden', 'assigner', $assigner);
        $mform->setType('assigner', PARAM_INT);

        if($canmanage) {
            $mform->addElement('selectyesno', 'adduser', get_string('addusersetting', 'local_supervision'));
            $mform->setDefault('adduser', 0);
        } else {
            $mform->addElement('hidden', 'adduser', 0);
        }
        $mform->setType('adduser', PARAM_INT);

        /*
        $zone = $CFG->timezone;
        $mform->addElement('date_selector', 'datestart', get_string('date'), array('timezone' => $zone, 'optional'  => false));

        $mform->addElement('text', 'timeduration', get_string('holidayduration', 'local_supervision'), array('size'=>'4'));
        $mform->setType('timeduration', PARAM_INT);
        $mform->setDefault('timeduration', '1');

        $mform->addElement('text', 'scope', get_string('holidayscope', 'local_supervision'), array('size'=>'10'));
        $mform->setType('scope', PARAM_ALPHA);
        $mform->setDefault('scope', 'N');
        */
        foreach($hiddens as $param => $value) {
            $mform->addElement('hidden', $param, $value);
            $mform->setType($param, PARAM_RAW);
        }
        $mform->addElement('hidden', 'edit', $edit);
        $mform->setType('edit', PARAM_RAW);

        $this->add_action_buttons(true, get_string('save', 'local_supervision'));
    }
}

