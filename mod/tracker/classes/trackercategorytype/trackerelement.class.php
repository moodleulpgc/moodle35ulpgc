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

defined('MOODLE_INTERNAL') || die();

/**
 * @package mod_tracker
 * @category mod
 * @author Clifford Tham, Valery Fremaux > 1.8
 * @date 02/12/2007
 */

/**
 * A generic class for collecting all that is common to all elements
 */

abstract class trackerelement {
    var $id;
    var $course;
    var $usedid;
    var $name;
    var $description;
    var $format;
    var $type;
    var $sortorder;
    var $maxorder;
    var $value;
    var $options;
    var $tracker;
    var $active;
    var $private;
    var $canbemodifiedby;
    var $context;

    function __construct(&$tracker, $elementid = null, $used = false) {
        global $DB;

        $this->id = $elementid;

        if ($elementid && $used) {
            $elmusedrec = $DB->get_record('tracker_elementused', array('id' => $elementid));
            $this->usedid = $elementid;
            $elementid = $elmusedrec->elementid;
            $this->active = $elmusedrec->active;
            $this->mandatory = $elmusedrec->mandatory;
            $this->private = $elmusedrec->private;
            $this->sortorder = $elmusedrec->sortorder;
            $this->canbemodifiedby = $elmusedrec->canbemodifiedby;
        }

        if ($elementid) {
            $elmrec = $DB->get_record('tracker_element', array('id' => $elementid));
            $this->id = $elmrec->id;
            $this->name = $elmrec->name;
            $this->description = $elmrec->description;
            $this->course = $elmrec->course;
            $this->type = $elmrec->type;
            // ecastro ULPGC
            $this->paramint1 = $elmrec->paramint1;
            $this->paramint2 = $elmrec->paramint2;
            $this->paramchar1 = $elmrec->paramchar1;
            $this->paramchar2 = $elmrec->paramchar2;
            
            
        }

        $this->options = null;
        $this->value = null;
        $this->tracker = $tracker;
    }

    /**
     * If true, element is like a select or a radio box array
     * and has suboptions to define
     */
    function type_has_options() {
        return false;
    }

    /**
     * Tells if options are defined for thsi instance
     */
    function hasoptions() {
        return $this->options !== null;
    }

    /** 
     * Get an option value
     */
    function getoption($optionid) {
        return $this->options[$optionid];
    }

    /** 
     * Sets the option list
     */
    function setoptions($options) {
        $this->options = $options;
    }

    /**
     * If true, this element can be told to be mandatory.
     */
    function has_mandatory_option() {
        return true;
    }

    /**
     * If true, this element can be told to be private.
     * A private element can be edited by the ticket operators,
     * but is not seen by ticket owners.
     */
    function has_private_option() {
        return true;
    }

    function setcontext(&$context) {
        $this->context = $context;
    }

    /**
     * in case we have options (such as checkboxes or radio lists, get options from db.
     * this is backcalled by specific type constructors after core construction.
     */
    function setoptionsfromdb() {
        global $DB;

		if (isset($this->id)){
			$this->options = $DB->get_records_select('tracker_elementitem', " elementid = ? AND active = 1 ORDER BY sortorder", array($this->id));
			if ($this->options){
                foreach($this->options as $option){
                    $this->maxorder = max($option->sortorder, $this->maxorder);
                }
            } else {
                $this->maxorder = 0;
            }
        } else {
            print_error ('errorinvalidelementID', 'tracker');
        }
    }

    /**
     * Gets the current value for this element instance
     * in an issue
     */
    function getvalue($issueid) {
        global $CFG, $DB;

        if (!$issueid) {
            return '';
        }

        $sql = "
            SELECT
                elementitemid
            FROM
                {tracker_issueattribute}
            WHERE
                elementid = {$this->id} AND
                issueid = {$issueid}
        ";
        $this->value = $DB->get_field_sql($sql);
        return($this->value);
    }

	function getname(){
		return $this->name;
	}

	function optionlistview($cm){
	    global $CFG, $COURSE, $OUTPUT;

        $strname = tracker_getstring('name');
        $strdescription = tracker_getstring('description');
        $strsortorder = tracker_getstring('sortorder', 'tracker');
        $straction = tracker_getstring('action');
        $strautoresponse = tracker_getstring('autoresponse', 'tracker'); // ecastro ULPGC
        $table = new html_table();
        $table->width = "90%";
        $table->size = array('10%', '15%', '50%', '10%', '15%');
        $table->head = array('', "<b>$strname</b>","<b>$strdescription</b>","<b>$strautoresponse</b>", "<b>$straction</b>");
        if (!empty($this->options)) {
            foreach ($this->options as $option) {
                $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'editelementoption', 'optionid' => $option->id, 'elementid' => $option->elementid);
                $editoptionurl = new moodle_url('/mod/tracker/view.php', $params);
                $actions  = '<a href="'.$editoptionurl.'" title="'.tracker_getstring('edit').'">'.$OUTPUT->pix_icon('/t/edit', tracker_getstring('edit')).'</a>&nbsp;';

                $img = ($option->sortorder > 1) ? 'up' : 'up_shadow';
                $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'moveelementoptionup', 'optionid' => $option->id, 'elementid' => $option->elementid);
                $moveurl = new moodle_url('/mod/tracker/view.php', $params);
                $actions .= '<a href="'.$moveurl.'" title="'.tracker_getstring('up').'">'.$OUTPUT->pix_icon($img, tracker_getstring('up'), 'mod_tracker').'</a>&nbsp;';

                $img = ($option->sortorder < $this->maxorder) ? 'down' : 'down_shadow' ;
                $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'moveelementoptiondown', 'optionid' => $option->id, 'elementid' => $option->elementid);
                $moveurl = new moodle_url('/mod/tracker/view.php', $params);

                $actions .= '<a href="'.$moveurl.'" title="'.tracker_getstring('down').'">'.$OUTPUT->pix_icon($img, tracker_getstring('down'), 'mod_tracker').'</a>&nbsp;';

                $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'deleteelementoption', 'optionid' => $option->id, 'elementid' => $option->elementid);
                $deleteurl = new moodle_url('/mod/tracker/view.php', $params);
                $actions .= '<a href="'.$deleteurl.'" title="'.tracker_getstring('delete').'">'.$OUTPUT->pix_icon('/t/delete', tracker_getstring('delete')).'</a>';

                $autoresponse = empty($option->autoresponse) ? tracker_getstring('no') : tracker_getstring('yes');
                $table->data[] = array('<b> '.tracker_getstring('option', 'tracker').' '.$option->sortorder.':</b>',$option->name, format_string($option->description, true, $COURSE->id), $autoresponse, $actions);
            }
        }
        return html_writer::table($table);
    }

	function viewsearch(){
	    $this->edit(0, true); // ecastro ULPGC
	}

	function viewquery(){
	    $this->view(true);
	}

    /**
     * given a tracker and an element form key in a static context,
     * build a suitable trackerelement object that represents it.
     */
    static function find_instance(&$tracker, $elementkey) {
        global $DB;

		$elmname = preg_replace('/^element/', '', $elementkey);

		$sql = "
			SELECT
				e.*,
				eu.id as usedid
			FROM
				{tracker_element} e,
				{tracker_elementused} eu
			WHERE
				e.id = eu.elementid AND
				eu.trackerid = ? AND
				e.name = ?
		";

		if ($element = $DB->get_record_sql($sql, array($tracker->id, $elmname))){

			$eltypeconstuctor = $element->type.'element';
			$instance = new $eltypeconstuctor($tracker, $element->id);
			return $element;
		}

		return null;
	}

    /**
     * Get the element view when the ticket is being edited
     */
    abstract function edit($issueid = 0);

    /**

     * Get the element view when the ticket is being displayed
     */
    abstract function view($issueid = 0);

    /**
     * Provides the form element when building a new element instance
     */
    abstract function add_form_element(&$mform);

	abstract function formprocess(&$data);

    /**
     * given a tracker and an id of a used element in a static context,
     * build a suitable trackerelement object that represents it.
     * what we need to knwo is the type of the element to call the adequate
     * constructor.
     */
    static function find_instance_by_usedid(&$tracker, $usedid) {
        global $DB, $CFG;

        $sql = "
            SELECT
                eu.id,
                e.type
            FROM
                {tracker_element} e,
                {tracker_elementused} eu
            WHERE
                e.id = eu.elementid AND
                eu.id = ?
        ";

        if ($element = $DB->get_record_sql($sql, array($usedid))) {

            $eltypeconstructor = $element->type.'element';
            include_once($CFG->dirroot.'/mod/tracker/classes/trackercategorytype/'.$element->type.'/'.$element->type.'.class.php');
			$instance = new $eltypeconstructor($tracker, $usedid, true);
			return $instance;
		}

		return null;
	}

	/**
	* given a tracker and an id of a used element in a static context,
	* build a suitable trackerelement object that represents it.
	* what we need to knwo is the type of the element to call the adequate
	* constructor.
	*/
	static function find_instance_by_id(&$tracker, $id){
		global $DB, $CFG;

		if ($element = $DB->get_record('tracker_element', array('id' => $id), 'id, type', 'id')){
			$eltypeconstructor = $element->type.'element';
            include_once($CFG->dirroot.'/mod/tracker/classes/trackercategorytype/'.$element->type.'/'.$element->type.'.class.php');
			$instance = new $eltypeconstructor($tracker, $id, false);
			return $instance;
		}

		return null;
	}

    /**
    * ULPGC ecastro to use autoresponses
    *
    */
    function get_autoresponse($issueid){
        global $CFG;

        $this->value = $this->getvalue($issueid);
        if (is_array($this->options) && $this->value) {
            $values = explode(',', $this->value);
        
            $results = array();
            foreach($values as $value) {
                if(!isset($this->options[$value])) {
                    // not int values means is not an autoresponse field
                    continue;
                }
                $option = $this->options[$value];
                if($option->autoresponse) {
                    $result = new stdClass;
                    $result->name = $option->description;
                    $result->response = $option->autoresponse;
                    $results[] = $result;
                }
            }
            if($results) {
                return($results);
            }
            
        }
        
        return false;
    }

    /**
    * ULPGC ecastro to use autofill fields
    *
    */
    function autofill_options(){
        global $CFG, $DB;
        
        $this->setoptionsfromdb();
        $currentmap = array();
        foreach($this->options as $oid => $option) {
            $currentmap[$oid] = $option->name;
        }
        
        // get updated items, get only new items, not existing ones
        $newoptions = array();
        //list($insql, $params) = $DB->get_in_or_equal($currentmap, SQL_PARAMS_NAMED, 'op', false);
        $ufields = 'u.id, u.idnumber, u.firstname, u.lastname, '.get_all_user_name_fields(true, 'u');
        $sortorder = 'u.lastname ASC, u.firstname ASC';
        $params = array();
        $records = array();
        $users = array();
        
        if($this->paramchar1 == 'courses') {
            $sql = "SELECT c.shortname, CONCAT(c.shortname, '-', c.fullname) 
                    FROM {course} c
                    JOIN {course_categories} cc ON c.category = cc.id 
                    WHERE c.category <> 0  ";
            if($this->paramchar2) {
                $sql .= 'AND cc.idnumber = :idnumber ';
                $params['idnumber'] = $this->paramchar2;
            }
            $sql .= ' ORDER BY c.shortname ASC, c.fullname ASC ';    
            $records = $DB->get_records_sql_menu($sql, $params);
            
        } elseif($this->paramchar1 == 'categories') {
            $sql = "SELECT c.idnumber, c.name 
                    FROM {course_categories} c
                    JOIN {course_categories} cc ON c.parent = cc.id 
                    WHERE c.id > 0 ";
            if($this->paramchar2) {
                $sql .= ' AND cc.idnumber = :idnumber ';
                $params['idnumber'] = $this->paramchar2;
            } else {
                $sql .= ' AND c.parent = 0 ';            

            }
            $sql .= ' ORDER BY c.name ASC';
            $records = $DB->get_records_sql_menu($sql, $params);
        
        } elseif($this->paramchar1 == 'users_role') {
            if($this->paramchar2) {
                $roleid = $DB->get_field('role', 'id', array('shortname'=>$this->paramchar2));
            }
            if($roleid) {
                $users = get_role_users($roleid, $this->context, false, $ufields, $sortorder, false);
            } else {
                $users = get_enrolled_users($this->context, '', null, $ufields, $sortorder);
            }
        
        } elseif($this->paramchar1 == 'users_group') {
            $groupid = 0;
            if($this->paramchar2) {
                $groupid = $DB->get_field('groups', 'id', array('courseid'=>$this->course, 'idnumber'=>$this->paramchar2));
            }
            if(!$groupid) {
                $groupid = 0;
            } 
            $users = get_enrolled_users($this->context, '', $groupid, $ufields, $sortorder);
        
        } elseif($this->paramchar1 == 'users_grouping') {
            $groupingid = 0;
            if($this->paramchar2) {
                $groupingid = $DB->get_field('groupings', 'id', array('courseid'=>$this->course, 'idnumber'=>$this->paramchar2));
            }
            if(!$groupingid) {
                $groupingid = 0;
            } 
            $users = groups_get_grouping_members($groupingid, $ufields, $sortorder); 
        
        }
        
        if($users && !$records) {
            foreach($users as $user) {
                $records[$user->idnumber] = fullname($user, false, 'lastname');
            }
            unset($users);
        }
        
        if($records) {
            foreach($records as $idnumber => $name) {
                $key = array_search($idnumber, $currentmap);
                if($key === false) {
                    $newoptions[$idnumber] = $name;
                } else {
                    unset($currentmap[$key]);
                }
            }
        } 
        
        $deleting = $currentmap;
        unset($records);

        // check if options used and delete if not used, multiple options use x,y,z in DB  
        $usedoptions = $DB->get_records_menu('tracker_issueattribute', array('trackerid' => $this->tracker->id , 'elementid'=> $this->id), '', 'id, elementitemid');
        $usedoptions = array_unique(explode(',',implode(',', $usedoptions)));
        list($insql, $params) = $DB->get_in_or_equal($usedoptions, SQL_PARAMS_NAMED, 'op');
        $select = "elementid = :eid AND id $insql";
        $params['eid'] = $this->id;
        $usedoptions = $DB->get_records_select_menu('tracker_elementitem', $select, $params, '', 'id,name');
        $delete = array();
        foreach($deleting as $name => $desc) {
            $key = array_search($name, $usedoptions);
            if($key !== false) {
                // OK, option is not used in any issue, can be deleted
                $delete[] = $key;
            }
        }
        unset($usedoptions);
        unset($deleting);
        
        
        if($DB->delete_records_list('tracker_elementitem', 'id', $delete)) {
            $this->options = array_diff_key($this->options, array_flip($delete));
        }
        unset($delete);
        
        // Now insert new options
        $countoptions = count($this->options);
        $option = new StdClass;
    	$option->autoresponse = '';
        $option->elementid = $this->id;
        $option->sortorder = $countoptions;

        foreach($newoptions as $name => $desc) {
            if($name && $desc) {
                $option->name = $name;
                $option->description = $desc;
                $option->sortorder += 1;
                $oid = $DB->insert_record('tracker_elementitem', $option);
                $this->options[$oid] = clone($option);
                $this->options[$oid]->id = $oid;
            }
        }
        
        // reorder options
        foreach($this->options as $oid => $option) {
            $currentmap[$oid] = $option->description;
        }
        $first = reset($this->options);
        $sortorder = $first->sortorder;
        core_collator::asort($currentmap);
        foreach($currentmap as $oid => $name) {
            $option = $this->options[$oid];
            if($option->sortorder != $sortorder) {
                $option->sortorder = $sortorder;
                if($DB->set_field('tracker_elementitem', 'sortorder', $sortorder, array('id'=>$oid, 'elementid'=>$this->id))) {
                    $this->options[$oid] = $option;
                }
            }
            $sortorder +=1;
        }

        return $this->options;
    }
    
    /**
    * ULPGC ecastro to use autofill fields
    *
    */
    function add_autowatches() {
        global $CFG, $DB;

        // check is applicable
        if(!$this->paramint2 || (substr($this->paramchar1, 0, 5) != 'users'))  {
            return;
        }

        $sql = "SELECT i.*, ia.elementitemid
                FROM {tracker_issue} i 
                JOIN {tracker_issueattribute} ia ON ia.trackerid = i.trackerid AND i.id = ia.issueid
                WHERE i.trackerid = :tid AND ia.elementid = :eid ";
        $params = array('tid'=>$this->tracker->id, 'eid'=>$this->id);
        
        if($issues = $DB->get_records_sql($sql, $params)) {
            $this->setoptionsfromdb();
            foreach($issues as $issue) {
                $options = explode(',', $issue->elementitemid);
                list($insql, $params) = $DB->get_in_or_equal($options, SQL_PARAMS_NAMED, 'op');
                $sql = "SELECT u.id
                        FROM {tracker_elementitem} ei 
                        JOIN {user} u ON ei.name = u.idnumber
                        WHERE ei.id $insql AND ei.elementid = :eid AND NOT EXISTS (SELECT 1 FROM {tracker_issuecc} ic 
                                                                                    WHERE ic.trackerid = :tid AND 
                                                                                            ic.issueid = :iid AND  ic.userid = u.id ) ";
                $params['eid'] = $this->id;
                $params['iid'] = $issue->id;
                $params['tid'] = $this->tracker->id;
                
                if($users = $DB->get_records_sql($sql, $params)) {
                    foreach($users as $user) {
                        tracker_register_cc($this->tracker, $issue, $user->id);
                    }
                }
            }
        }
    }
}
