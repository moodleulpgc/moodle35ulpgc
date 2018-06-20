<?php //$Id:
/**
 * User filter ULPGC ecastro
 */


require_once($CFG->dirroot.'/user/filters/lib.php');

/**
 * User filter based on values of custom profile fields.
 */
class user_filter_userfield extends user_filter_type {

    /**
     * Constructor
     * @param string $name the name of the filter instance
     * @param string $label the label of the filter instance
     * @param boolean $advanced advanced form element flag
     */
    function __construct($name, $label, $advanced) {
        parent::__construct($name, $label, $advanced);
    }

    /**
     * Returns an array of comparison operators
     * @return array of comparison operators
     */
    function get_operators() {
        return array(0 => get_string('contains', 'filters'),
                     1 => get_string('doesnotcontain','filters'),
                     2 => get_string('isequalto','filters'),
                     3 => get_string('startswith','filters'),
                     4 => get_string('endswith','filters'),
                     5 => get_string('isempty','filters'),
                     6 => get_string('isnotdefined','filters'),
                     7 => get_string('isdefined','filters'));
    }

    /**
     * Returns an array of custom profile fields
     * @return array of profile fields
     */
    function get_user_fields() {
        global $DB, $USER;
        if (!$user = $DB->get_record('user', array('id'=>$USER->id))) {
            return null;
        }
        $fields = (array_keys(get_object_vars($user)));
        natcasesort($fields);
        array_unshift($fields,  get_string('anyfield', 'filters'));
        /*
        foreach($fields as $k=>$v) {
            $res[$k] = get_string("$v");
        }
        return $res;
        */
        return $fields;
    }

    /**
     * Adds controls specific to this filter in the form.
     * @param object $mform a MoodleForm object to setup
     */
    function setupForm(&$mform) {
        $user_fields = $this->get_user_fields();
        if (empty($user_fields)) {
            return;
        }
        $objs = array();
        $objs[] =& $mform->createElement('select', $this->_name.'_fld', null, $user_fields);
        $objs[] =& $mform->createElement('select', $this->_name.'_op', null, $this->get_operators());
        $objs[] =& $mform->createElement('text', $this->_name, null);
        $grp =& $mform->addElement('group', $this->_name.'_grp', $this->_label, $objs, '', false);
        //$grp->setHelpButton(array('userfield',$this->_label,'filters'));
        $mform->setType($this->_name, PARAM_ALPHANUMEXT);
        if ($this->_advanced) {
            $mform->setAdvanced($this->_name.'_grp');
        }
    }

    /**
     * Retrieves data from the form data
     * @param object $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    function check_data($formdata) {
        $user_fields = $this->get_user_fields();

        if (empty($user_fields)) {
            return false;
        }

        $field    = $this->_name;
        $operator = $field.'_op';
        $userfld  = $field.'_fld';

        if (array_key_exists($userfld, $formdata)) {
            if ($formdata->$operator < 5 and $formdata->$field === '') {
                return false;
            }

            return array('value'    => (string)$formdata->$field,
                         'operator' => (int)$formdata->$operator,
                         'userfield'  => (int)$formdata->$userfld);
        }
    }

    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return string the filtering condition or null if the filter is disabled
     */
    function get_sql_filter($data) {
        global $CFG, $DB;

        $user_fields = $this->get_user_fields();
        if (empty($user_fields)) {
            return '';
        }

        $userfld  = $data['userfield'];
        $operator = $data['operator'];
        $value    = $data['value'];

        if (!array_key_exists($userfld, $user_fields)) {
            return '';
        }

        $where = "";
        $op = " IN ";
        $ilike = sql_ilike();

        if ($operator < 5 and $value === '') {
            return '';
        }

        if ($userfld) {
            if ($where !== '') {
                $field = $user_fields[$userfld];
            }

            switch($operator) {
                case 0: // contains
                    $where = $DB->sql_like($field, "%$value%", false, false); break;
                case 1: // does not contain
                    $where = $DB->sql_like($field, "%$value%", false, false, true); break;
                case 2: // equal to
                    $where = $DB->sql_like($field, "$value", false, false); break;
                case 3: // starts with
                    $where = $DB->sql_like($field, "$value%", false, false); break;
                case 4: // ends with
                    $where = $DB->sql_like($field, "%$value", false, false); break;
                case 5: // empty
                    $where = " $field='' "; break;
                case 6: // is not defined
                    $op = " $field NOT IN "; break;
                case 7: // is defined
                    break;
            }

            if ($where !== '') {
                $where = "WHERE $where";
            }
        }

        return "id $op (SELECT id FROM {user} $where)";
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    function get_label($data) {
        $operators      = $this->get_operators();
        $user_fields = $this->get_user_fields();

        if (empty($user_fields)) {
            return '';
        }

        $userfld  = $data['userfield'];
        $operator = $data['operator'];
        $value    = $data['value'];

        if (!array_key_exists($userfld, $user_fields)) {
            return '';
        }

        $a = new stdClass();
        $a->label    = $this->_label;
        $a->value    = $value;
        $a->profile  = $user_fields[$userfld];
        $a->operator = $operators[$operator];

        switch($operator) {
            case 0: // contains
            case 1: // doesn't contain
            case 2: // equal to
            case 3: // starts with
            case 4: // ends with
                return get_string('userfieldlabel', 'local_ulpgccore', $a);
            case 5: // empty
            case 6: // is not defined
            case 7: // is defined
                return get_string('userfieldlabelnovalue', 'local_ulpgccore', $a);
        }
        return '';
    }
}
