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
 * Internal library of functions for module examregistrar
 *
 * All the examregistrar specific functions, needed to implement the module
 * logic, are placed here.
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/renderable.php');
require_once($CFG->dirroot . '/user/selector/lib.php');

/**
 * Returns a particular array value for the named variable, taken from
 * POST or GET, otherwise returning a given default.
 *
 * This function should be used to initialise all optional values
 * in a script that are based on parameters.  Usually it will be
 * used like this:
 *    $ids = optional_param('id', array(), PARAM_INT);
 *
 *  Note: arrays of arrays are not supported, only alphanumeric keys with _ and - are supported
 *
 * @param string $parname the name of the page parameter we want
 * @param mixed  $default the default value to return if nothing is found
 * @param string $type expected type of parameter
 * @return array
 */
function optional_param_array_array($parname, $default, $type) {
    if (func_num_args() != 3 or empty($parname) or empty($type)) {
        throw new coding_exception('optional_param_array() requires $parname, $default and $type to be specified (parameter: '.$parname.')');
    }

    if (isset($_POST[$parname])) {       // POST has precedence
        $param = $_POST[$parname];
    } else if (isset($_GET[$parname])) {
        $param = $_GET[$parname];
    } else {
        return $default;
    }
    if (!is_array($param)) {
        debugging('optional_param_array() expects array parameters only: '.$parname);
        return $default;
    }

    return clean_param_array($param, $type, true);
}


/**
 * Returns ID of element instance of type Location that will be used as high order Venue locations
 * Velue locations are specified wuen booking an exam and hold rooms for exam allocations
 *
 * @param object $examregistrar object
 * @return int
 */
function examregistrar_get_venue_element($examregistrar) {
    global $DB;

    $exregid = examregistrar_get_primaryid($examregistrar);
    $config = examregistrar_get_instance_configdata($examregistrar); 
    $venuecode = $config->venuelocationtype;
    return $DB->get_field('examregistrar_elements', 'id', array('examregid'=>$exregid, 'type'=>'locationtypeitem', 'idnumber'=>$venuecode));
}


/**
 * Returns ID of element role instance to be used as default
 *
 * @param object $examregistrar object
 * @return int
 */
function examregistrar_get_default_role($examregistrar) {
    global $DB;

    $exregid = examregistrar_get_primaryid($examregistrar);
    $config = examregistrar_get_instance_configdata($examregistrar);
    $rolecode = $config->defaultrole;
    return $DB->get_field('examregistrar_elements', 'id', array('examregid'=>$exregid, 'type'=>'roleitem', 'idnumber'=>$rolecode));
}



/**
 * Returns the first venue ID associated with this user, if any
 *
 * @param object $examregistrar object
 * @param int $userid
 * @param int $sessionid
 * @return int
 */
function examregistrar_user_venueid($examregistrar, $userid = 0, $session = 0) {
    global $DB; $USER;

    if(!$userid) {
        $userid = $USER->id;
    }

    $venueid = 0 ;

    $venuetype = examregistrar_get_venue_element($examregistrar);

    if($venues = examregistrar_get_user_rooms($examregistrar, $userid, 0, $session)) {
        foreach($venues as $venue) {
            if($venueid = examregistrar_get_room_venue($venue, $venuetype)) {
                break;
                //$venue = reset($venues);
                //$venueid = $venue->id;
            }
        }
    }

    return $venueid;
}


/**
 * Returns the venues the user is assigned as staffer.
 * If the user has several roles in the same room only one, first, is returned
 *
 * @param object $examregistrar object
 * @param int $userid
 * @param int $type locationtype to search for (either veues, roooms, etc, by elementID)
 * @param int $session exam session to look for room assignation (0 means any)
 * @param int $role roleID of the role assigned in that room
 * @return array of rooms with room & role names
 */
function examregistrar_get_user_rooms($examregistrar, $userid = 0, $type = 0, $session = 0, $role = 0  ) {
    global $DB, $USER;

    if(!$userid) {
        $userid = $USER->id;
    }

    $params = array('examregid' => examregistrar_get_primaryid($examregistrar),
                    'userid' => $userid);

    $typewhere = '';
    if($type) {
        $typewhere = ' AND l.locationtype = :type ';
        $params['type'] = $type;
    }

    $sessionwhere = '';
    if($session) {
        $sessionwhere = ' AND s.examsession = :examsession ';
        $params['examsession'] = $session;
    }

    $rolewhere = '';
    if($role) {
        $rolewhere = ' AND s.role = :role ';
        $params['role'] = $role;
    }

    $sql = "SELECT l.*, el.name AS roomname, el.idnumber AS roomidnumber, er.name AS rolename, er.idnumber AS roleidnumber
            FROM {examregistrar_locations} l
            JOIN {examregistrar_staffers} s ON s.locationid = l.id AND s.visible = 1
            JOIN {examregistrar_elements} el ON l.examregid = el.examregid AND el.type = 'locationitem' AND l.location = el.id
            JOIN {examregistrar_elements} er ON er.type = 'roleitem' AND s.role = er.id
            WHERE l.examregid = :examregid AND s.userid = :userid AND l.visible = 1 $typewhere $sessionwhere $rolewhere
            GROUP BY l.id
            ORDER BY el.name ASC ";

    return $DB->get_records_sql($sql, $params);
}


/**
 * Returns strings with template fields substitude with actual data
 *
 * @param array $replaces  an associative array of key (replace codes) / values (actual data)
 * @param string/array $subject where substitutions are performed, may be a string or an array of strings
 * @return string/array depends on subject type
 */
function examregistrar_str_replace($replaces, $subject) {
    foreach($replaces as $key => $value ){
        $subject = str_replace("%%$key%%", $value, $subject);
    }
    return $subject;
}


//////////////////////////////////////////////////////////////////////////////////
// Utility functions to get data fron tables                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the display name and idnumber of an item as stored in elements table
 *
 * @param object $item the record item from an examregisrar table
 * @param string $field the name of the field that stored the element ID
 * @return array (name, idnumber)
 */
function examregistrar_item_getelement($item, $field='element') {
    global $DB;

    if(!$item) {
        return array('', '');
    }
    
    if($field == 'stafferitem') {
        $user = $DB->get_record('user', array('id'=>$item->userid), 'id, firstname, lastname, idnumber');
            $element = new stdClass();
            $element->name = fullname($user);
            $element->idnumber = $user->idnumber;
    } else { 
        if(!$field || $field == 'element' ) {
            $eid = $item->id;
        } else {
            $eid = $item->$field;
        }
        if(!$element = $DB->get_record('examregistrar_elements', array('id'=>$eid))) {
            $element = new stdClass();
            $element->name = '';
            $element->idnumber = '';
        }
    }
    
    return array($element->name, $element->idnumber);
}


/**
 * Returns a menu of elements by type
 *
 * @param int $itemid the ID if the item in the table
 * @param string $table table where this ID is located
 * @param string $field of element type
 * @return array element name, idnumber
 */
function examregistrar_get_namecodefromid($itemid, $table = '', $field = '') {
    global $DB;

    if($table === '') {
        if(!$element = $DB->get_record('examregistrar_elements', array('id'=>$itemid), 'name,idnumber')) {
            $element = new stdClass();
            $element->name = '';
            $element->idnumber = '';
        }

        return array($element->name, $element->idnumber);
    }

    if(!$field) {
        $field = substr($table, 0, -1);
    }
    $item = $DB->get_record('examregistrar_'.$table, array('id' => $itemid));

    if($table == 'exams') {
        $period = new stdClass;
        list($period->name,  $period->idnumber) = examregistrar_get_namecodefromid($item->period, 'periods', 'period');
        $scope = $DB->get_record('examregistrar_elements', array('id'=>$item->examscope), 'name,idnumber');
        $name = $item->programme.'_'.$DB->get_field('course', 'shortname', array('id'=>$item->courseid)).
                '_'.$period->idnumber.'_'.$scope->idnumber.'_'.$item->callnum;
        $idnumber = '';
        return array($name, $idnumber);
    }

    return examregistrar_item_getelement($item, $field);
}


/**
 * Returns a menu of elements by type fron elements table
 *
 * @param object $examregistrar the examregistrar object
 * @param string $type element type
 * @param int $id the examregistrar ID
 * @param string $any should prepend or not a first item in menu. valid strings are 'any' or 'choose'
 * @return array element id, element name
 */
function examregistrar_elements_getvaluesmenu($examregistrar, $type, $id = 0, $any = 'any') {
    global $DB;

    $menu = array();

    if(!$id) {
        $id = examregistrar_get_primaryid($examregistrar);
    }

    $params = array('examregid' => $id, 'type' => $type, 'visible'=>1);

    if($type == 'annualityitem' && $examregistrar->annuality) {
        $any = false;
        $params['idnumber'] = $examregistrar->annuality;
    }

    if($any) {
        $menu = array('' => get_string($any));
    }
    if($elements = $DB->get_records('examregistrar_elements', $params)) {
        foreach($elements as $key => $element) {
            $menu[$element->id] = $element->name.' ('.$element->idnumber.')';
        }
    }
    return $menu;
}


/**
 * Returns a menu of table records items, (id, name) for selected table
 *
 * @param object $examregistrar the examregistrar object
 * @param int $id the examregistrar ID
 * @param string $table component table
 * @param string $field the name of the field to look for in table
 * @param string $any should prepend or not a first item in menu. valid strings are 'any' or 'choose'
 * @return array element id, element name
 */
function examregistrar_elements_get_fieldsmenu($examregistrar, $table, $field, $id =0, $any = 'any') {
    global $DB;

    if(!$id) {
        $id = examregistrar_get_primaryid($examregistrar);
    }

    $params = array('examregid' => $id);

    if($field == 'annuality' && $examregistrar->annuality) {
        $params['annuality'] = $examregistrar->annuality;
        $any = false;
    }

    if($field == 'programme' && $examregistrar->programme) {
        $params['programme'] = $examregistrar->programme;
        $any = false;
    }

    $menu = array();
    if($any) {
        $menu = array('' => get_string($any));
    }

    if($elements = $DB->get_records('examregistrar_'.$table, $params, " $field ASC ", "id, $field")) {
        foreach($elements as $key => $element) {
            $menu[$element->$field] = $element->$field;
        }
    }
    return $menu;
}


/**
 * Returns a menu of table records items, (id, name) for a tablefield referenced as idnumber
 *
 * @param object $examregistrar the examregistrar object
 * @param int $examregid the examregistrar ID
 * @param string $table element name & table
 * @param string $type element type
 * @param string $any should prepend or not a first item in menu. valid strings are 'any' or 'choose'
 * @param string $field the name of the field to look for in table, usually 'idnumber'
 * @param array $params additionals params for query table associative array (field, value)
 * @param string $sort qualified SQL order snippet, with t.
 * @return array element id, element name
 */
function examregistrar_get_referenced_namesmenu($examregistrar, $table, $type, $exregid = 0, $any = 'any', $field = '', $params = array(), $sort='') {
    global $DB;

    if(!$exregid) {
        $exregid = examregistrar_get_primaryid($examregistrar);
    }

    if(!$field) {
        $field = substr($table, 0, -1);
    }

    $sqljoin = '';
    $sqlparams = array('examregid' => $exregid,
                       'type' => $type);

    $annualitywhere = '';
    if($examregistrar->annuality) {
        $annuality = $DB->get_field('examregistrar_elements', 'id', array('examregid'=>$exregid, 'idnumber'=>$examregistrar->annuality));
        if($table == 'examsessions') {
            $sqljoin = 'JOIN {examregistrar_periods} p ON t.examregid = p.examregid AND t.period = p.id ';
            $anntable = 'examregistrar_periods';
        } else {
            $annuality = $DB->get_manager()->field_exists('examregistrar_'.$table, 'annuality') ? $annuality : 0;
        }
        if($annuality) {
            $sqlparams['annuality'] = $annuality;
            $annualitywhere = ' AND annuality = :annuality ';
            $any = false;
        }
    }

    $sql = "SELECT t.id, CONCAT(e.name,' (',e.idnumber,')')
            FROM {examregistrar_$table} t
            JOIN {examregistrar_elements} e ON t.examregid = e.examregid  AND  e.type = :type AND t.$field = e.id
            $sqljoin
            WHERE t.examregid = :examregid  AND e.visible = 1 AND t.visible = 1 ";


    $where = $annualitywhere;

    if($params) {
        foreach($params as $key => $value) {
            if($value) {
                $where .= " AND t.$key = :$key ";
                $sqlparams[$key] = $value;
            }
        }
    }

    if(!$sort) {
        $order = " ORDER BY e.name ASC ";
    } else {
        $order = ' ORDER BY '.$sort;
    }


    $menu = array();
    if($any) {
        $menu = array('' => get_string($any));
    }
    $items = $DB->get_records_sql_menu($sql.$where.$order, $sqlparams);

    /// TODO refactor in 2.6 construct name AFTER returning table, not in SQL TODO ///

    if($table == 'examsessions') {
        foreach($items as $id => $value) {
            $period = $DB->get_field('examregistrar_examsessions', 'period', array('id'=>$id));
            $element = $DB->get_field('examregistrar_periods', 'period', array('id'=>$period));
            $periodname = $DB->get_field('examregistrar_elements', 'idnumber', array('id'=>$element));
            $items[$id] = '['.$periodname.'] '.$value;
        }
    }

    $menu = $menu + $items;
    return $menu;
}


//////////////////////////////////////////////////////////////////////////////////
//   Exams submitting &a reviewing functions                                   //
////////////////////////////////////////////////////////////////////////////////


/**
 * Generates the exam idnumber identifier from course idnumber and exam period
 *
 * @param object $exam and exam record from examregistrar_exams
 * @param string $source initial name, tipically a course idnuber
 * @return string examfile idnumber string
 */
function examregistrar_examfile_idnumber($exam, $source) {

    $pieces = explode('_', $source);
    $examidnumber = $pieces[0].'-'.$pieces[5];
    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->period, 'periods');
    $examidnumber .= '-'. $idnumber;
    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examscope);
    $callnum = $exam->callnum > 0 ? $exam->callnum : 'R'.abs($exam->callnum);
    $examidnumber .= '-'. $idnumber.'-'.$callnum;

    return $examidnumber;
}


/**
 * Locates the Tracker issue associated to an examregistrar instance
 *  Returns de issueid of the issue creted for an exam file
 *
 * @param object $examregistrar the examregistrar object
 * @param object $examregistrar the examregistrar object
 * @param object $examregistrar the examregistrar object
 * @return int tracker issue ID
 */
function examregistrar_get_instance_configdata($examregistrar) {
    global $CFG, $DB;

    if(isset($examregistrar->config) && $examregistrar->config) {
        return $examregistrar->config;
    }
    
    if(isset($examregistrar->configdata)) {
            $config =  $examregistrar->configdata;
    } else {
        $exregid = examregistrar_get_primaryid($examregistrar);
        $config = $DB->get_field('examregistrar', 'configdata', array('id' =>$exregid));
    }
   
    return unserialize(base64_decode($config));
}


function examregistrar_file_set_nameextension($examregistrar, $filename, $type, $ext='.pdf') {

    $filename = trim($filename);
    $ext = trim($ext);
    if(strpos($ext, '.') === false) {
        $ext = '.'.$ext;
    }

    $config = examregistrar_get_instance_configdata($examregistrar);

    $qualifier = '';
    if($type == 'answers') {
        $qualifier = $config->extanswers;
    } elseif($type == 'key') {
        $qualifier = $config->extkey;
    } elseif($type == 'responses') {
        $qualifier = $config->extresponses;
    }
    if($qualifier) {
        $qualifier = trim($qualifier);
    }

    return clean_filename($filename.$qualifier.$ext);
}

/**
 * Locates the Tracker issue associated to an examregistrar instance
 *  Returns de issueid of the issue creted for an exam file
 *
 * @param object $examregistrar the examregistrar object
 * @param object $examregistrar the examregistrar object
 * @param object $examregistrar the examregistrar object
 * @return int tracker issue ID
 */
function examregistrar_review_addissue($examregistrar, $course, $examfile, $tracker = false) {
    global $CFG, $DB, $OUTPUT;

    $issueid = 0;

    if(!$examregistrar->reviewmod) {
        return 0;
    }

    if(!$tracker) {
        $tracker = examregistrar_get_review_tracker($examregistrar, $course);
    }

    if(!$tracker) {
        return -1;
    }

    $exam = $DB->get_record('examregistrar_exams', array('id'=>$examfile->examid), '*', MUST_EXIST);
    $examcourse = $DB->get_record('course', array('id'=>$exam->courseid), 'id, fullname, shortname, idnumber', MUST_EXIST);

    $examcoursename = $examcourse->shortname.' - '.format_string($examcourse->fullname);
    $summary = $examcoursename." \n".$examfile->idnumber.'  ('.$examfile->attempt.')' ;

    $items = array();
    $items[] = get_string('attemptn', 'examregistrar', $examfile->attempt);

    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->annuality);
    $items[] = get_string('annualityitem', 'examregistrar').': '.$name.' ('.$idnumber.')';

    $items[] = get_string('programme', 'examregistrar').': '.$exam->programme;

    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->period, 'periods');
    $items[] = get_string('perioditem', 'examregistrar').': '.$name.' ('.$idnumber.')';

    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examscope);
    $items[] = get_string('scopeitem', 'examregistrar').': '.$name.' ('.$idnumber.')';

    $items[] = get_string('callnum', 'examregistrar').': '.$exam->callnum;

    list($name, $idnumber) = examregistrar_get_namecodefromid($exam->examsession, 'examsessions');
    $items[] = get_string('examsessionitem', 'examregistrar').': '.$name.' ('.$idnumber.')';

    $examcontext = context_course::instance($examcourse->id);
    $filename = examregistrar_file_set_nameextension($examregistrar, $examfile->idnumber, 'exam'); //$examfile->idnumber.'.pdf';
    $url = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$examcontext->id.'/mod_examregistrar/exam/rev/'.$tracker->course.'/'.$examfile->id.'/'.$filename);
    $mime = mimeinfo("icon", $filename);
    $icon = new pix_icon(file_extension_icon($filename), $mime, 'moodle', array('class'=>'icon'));
    $filelink = $OUTPUT->action_link($url, $filename, null, null, $icon); //   html_writer::link($ffurl, " $icon &nbsp; $filename ");
    $filelink .= '<br />';

    $filename = examregistrar_file_set_nameextension($examregistrar, $examfile->idnumber, 'answers');//$examfile->idnumber.'_resp.pdf';
    $url = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$examcontext->id.'/mod_examregistrar/exam/rev/'.$tracker->course.'/'.$examfile->id.'/answers/'.$filename);
    $mime = mimeinfo("icon", $filename);
    $icon = new pix_icon(file_extension_icon($filename), $mime, 'moodle', array('class'=>'icon'));
    $filelink .= $OUTPUT->action_link($url, $filename, null, null, $icon); //   html_writer::link($ffurl, " $icon &nbsp; $filename ");

    $description = html_writer::tag('h3', $examcoursename).html_writer::div($filelink, ' examreviewissuefilelink ').html_writer::div(implode('<br />', $items), ' examreviewissuebody ' );

    /// TODO use function tracker_submitanissue(&$tracker, &$data) TODO
    /// TODO or better use an EVENT caller/logger to communicate modules TODO
    /// TODO or better use an EVENT caller/logger to communicate modules TODO
    /// TODO or better use an EVENT caller/logger to communicate modules TODO
    /// TODO use function tracker_submitanissue(&$tracker, &$data) TODO

    $issue = new StdClass;
    $issue->datereported = time();
    $issue->summary = $summary;
    $issue->description = $description;
    $issue->descriptionformat = FORMAT_HTML;
    $issue->format = 1;
    $issue->assignedto = $tracker->defaultassignee;
    $issue->bywhomid = 0;
    $issue->trackerid = $tracker->id;
    $issue->status = 0;
    $issue->reportedby = $examfile->userid;
    $issue->usermodified = $issue->datereported;
    $issue->resolvermodified = $issue->datereported;
    $issue->userlastseen = 0;

    $issueid = $DB->insert_record('tracker_issue', $issue);
    if($issueid > 0) {
        if($DB->set_field('examregistrar_examfiles', 'reviewid', $issueid, array('id'=>$examfile->id))) {
            $eventdata = array();
            $eventdata['objectid'] = $examfile->id;
            list($course, $cm) = get_course_and_cm_from_instance($examregistrar, 'examregistrar', $examregistrar->course);
            $eventdata['context'] = context_module::instance($cm->id);
            $eventdata['other'] = array();
            $eventdata['other']['attempt'] = $examfile->attempt;
            $eventdata['other']['examid'] = $examfile->examid;
            $eventdata['other']['issueid'] = $issueid;
            $eventdata['other']['idnumber'] = $examfile->idnumber;
            $eventdata['other']['examregid'] = $examregistrar->id;
            $event = \mod_examregistrar\event\examfile_synced::create($eventdata);
            $event->trigger();
        }
    }
    return (int)$issueid;
}



//////////////////////////////////////////////////////////////////////////////////
//   Session &  exams functions                                                //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns a menu of table records items, (id, name) for a tablefield referenced as idnumber
 *
 * @param object $examregistrar the examregistrar object
 * @param int $examregid the examregistrar ID
 * @param string $table element name & table
 * @param array $params additionals params for query table associative array (field, value)
 * @param string $any should prepend or not a first item in menu. valid strings are 'any' or 'choose'
 * @return array element id, element name
 */
function examregistrar_get_referenced_examsmenu($examregistrar, $table, $params = array(), $exregid = 0, $any = '') {
    global $DB;

    if(!$exregid) {
        $exregid = examregistrar_get_primaryid($examregistrar);
    }

    $where = '';
    if($params) {
        foreach($params as $param => $value) {
            $where .= " AND t.$param = :$param ";
        }
    }


    if($table != 'exams') {
        $sql = "SELECT DISTINCT(e.id), e.programme, c.shortname, t.id AS tid
                FROM {examregistrar_$table} t
                JOIN {examregistrar_exams} e ON t.examid = e.id
                JOIN {course} c ON e.courseid = c.id
                WHERE 1 $where
                GROUP BY e.id ";
    } else {
        $sql = "SELECT t.id, t.programme, c.shortname
                FROM {examregistrar_$table} t
                JOIN {course} c ON t.courseid = c.id
                WHERE 1 $where ";

    }

    $sort = " ORDER BY programme ASC, shortname ASC ";

    $menu = array();
    if($any) {
        $menu[0] = get_string($any, 'examregistrar');
    }
    if($items = $DB->get_records_sql($sql.$sort, $params)) {
        foreach($items as $key => $exam) {
            $menu[$key] = $exam->programme.' '.$exam->shortname;
        }
    }
    return $menu;
}

/**
 * Returns a menu  (courseid, name) of exams table items
 *
 * @param object $examregistrar the examregistrar object
 * @param int $examregid the examregistrar ID
 * @param array $params additionals params for query table associative array (field, value)
 * @param string $any should prepend or not a first item in menu. valid strings are 'any' or 'choose'
 * @return array element id, element name
 */
function examregistrar_get_courses_examsmenu($examregistrar,  $exregid = 0, $params = array(), $any = 'any', $field='shortname', $programme=true) {
    global $DB;

    if(!$exregid) {
        $exregid = examregistrar_get_primaryid($examregistrar);
    }

    $where = '';
    if($params) {
        foreach($params as $param => $value) {
            $prefix = '';
            if(strpos('.', $param) === false ) {
                $prefix = 'e.';
            }
            $where .= " AND $prefix"."$param = :$param ";
        }
    }

    if($examregistrar->programme) {
        $where .= " AND e.programme = :programme ";
        $params['programme'] = $examregistrar->programme;
        $programme = false;
    }

    $sql = "SELECT e.courseid, e.programme, c.shortname, c.fullname, c.idnumber
            FROM {examregistrar_exams} e
            JOIN {course} c ON e.courseid = c.id
            WHERE 1 $where
            GROUP BY e.courseid ";

    $sort = " ORDER BY e.programme ASC, c.shortname ASC ";

    $menu = array();
    if($any) {
        $menu[0] = get_string($any, 'examregistrar');
    }
    if($items = $DB->get_records_sql($sql.$sort, $params)) {
        foreach($items as $key => $exam) {
            $prefix = '';
            if($programme) {
                $prefix = $exam->programme.'-';
            }
            $menu[$key] = $prefix.$exam->$field;
        }
    }
    return $menu;
}


/**
 * Returns a menu of table records items, (id, name) for a tablefield referenced as idnumber
 *
 * @param object $examregistrar the examregistrar object
 * @param int $examregid the examregistrar ID
 * @param string $table element name & table
 * @param array $params additionals params for query table associative array (field, value)
 * @param string $any should prepend or not a first item in menu. valid strings are 'any' or 'choose'
 * @return array element id, element name
 */
function examregistrar_get_referenced_roomsmenu($examregistrar, $table, $params = array(), $exregid = 0, $any = '') {
    global $DB;

    if(!$exregid) {
        $exregid = examregistrar_get_primaryid($examregistrar);
    }

    $where = '';
    if($params) {
        foreach($params as $param => $value) {
            $where .= " AND t.$param = :$param ";
        }
    }

    $sql = "SELECT DISTINCT(l.id), e.name, e.idnumber, t.id AS tid
            FROM {examregistrar_$table} t
            JOIN {examregistrar_locations} l ON t.roomid = l.id
            JOIN {examregistrar_elements} e ON l.examregid = e.examregid  AND  e.type = 'locationitem' AND l.location = e.id
            WHERE 1 $where
            GROUP BY l.id ";

    $sort = " ORDER BY name ASC ";

    $menu = array();
    if($any) {
        $menu[0] = get_string($any, 'examregistrar');
    }
    if($items = $DB->get_records_sql($sql.$sort, $params)) {
        foreach($items as $key => $room) {
            $menu[$key] = $room->name.' ('.$room->idnumber.')';
        }
    }
    return $menu;
}


/**
 * Gets a collection of rooms (with names) assigned to an exam session
 * optionally restricted for bookedsite and with allocation data
 *
 * @param int $sessionid the ID for the exam session
 * @param int $bookedsite the ID for the venue the room belongs or is booked
 * @param string $sort  how to sort results, empty = roomname, others seats/booked/free
 * @param bool allocations include allocation occupancy on results
 * @param bool $visible availability of sesion room (should consider only those available, 1, not available, 0, or any: null)
 * @return array rooms data
 */
function examregistrar_get_session_rooms($sessionid, $bookedsite = 0, $sort = '',  $allocations=false, $visible = null) {
    global $DB;

    $sessionrooms = array();

    $params = array('examsession'=>$sessionid);
    $venuewhere = '';
    if($bookedsite) {
        $venuewhere = ' AND sr.bookedsite = :bookedsite ';
        $params['bookedsite'] = $bookedsite;
    }
    $visiblewhere = '';
    if(!is_null($visible)) {
        $visiblewhere = ' AND sr.available = :visible ';
        $params['visible'] = $visible;
    }
    $selectcount = ' 0 AS booked, r.seats AS freeseats ';
    $allocationjoin = '';
    if($allocations) {
        $selectcount = ' COUNT(DISTINCT ss.userid) AS booked, (r.seats - COUNT(DISTINCT ss.userid)) AS freeseats ';
        $allocationjoin = "LEFT JOIN {examregistrar_session_seats} ss ON sr.examsession = ss.examsession
                                                                            AND sr.bookedsite = ss.bookedsite AND sr.roomid = ss.roomid ";
    } elseif($sort) {
        $sort = 'seats';
    }
    $order = '';
    if($sort) {
        $order = " $sort "; // seats/booked/free
        if($sort == 'freeseats' || $rsort == 'seats') {
            $order .= ' DESC';
        }
        if(!$bookedsite) {
            $order = ' venueidnumber ASC, '.$order;
        }
        $order .= ', ';
    }

    $sql = "SELECT r.id, r.seats, sr.bookedsite, sr.examsession, sr.available, er.name AS name, er.idnumber AS idnumber,
                        ev.name AS venuename, ev.idnumber AS venueidnumber, $selectcount
            FROM {examregistrar_locations} r
            JOIN {examregistrar_session_rooms} sr ON sr.roomid = r.id
            JOIN {examregistrar_elements} er ON er.examregid = r.examregid AND r.location = er.id
            JOIN {examregistrar_locations} v ON sr.bookedsite = v.id AND v.visible = 1
            JOIN {examregistrar_elements} ev ON ev.examregid = v.examregid AND v.location = ev.id
            $allocationjoin
            WHERE sr.examsession = :examsession  AND r.visible = 1  $venuewhere $visiblewhere
            GROUP BY r.id
            ORDER BY $order name ASC ";

    $sessionrooms = $DB->get_records_sql($sql,$params);


    return $sessionrooms;
}


/**
 * Gets a collection of exams assigned to a room in this session
 * optionally restrited for bookedsite
 *
 * @param int $sessionid the ID for the exam session
 * @param int $bookedsite the ID for the venue the room belongs or is booked
 * @return array exams
 */
function examregistrar_get_sessionroom_exams($roomid, $sessionid, $bookedsite = 0) {
    global $DB;

    $exams = array();

    $params = array('roomid'=>$roomid, 'examsession'=>$sessionid);
    $venuewhere = '';
    if($bookedsite) {
        $venuewhere = ' AND sr.bookedsite = :bookedsite ';
        $params['bookedsite'] = $bookedsite;
    }

    $sql = "SELECT e.id, sr.roomid, ss.examid, e.programme, e.courseid, e.callnum, e.examsession, e.examscope, c.shortname, c.fullname, c.idnumber
            FROM {examregistrar_session_rooms} sr
            LEFT JOIN {examregistrar_session_seats} ss ON sr.roomid = ss.roomid AND sr.examsession = ss.examsession AND sr.bookedsite = ss.bookedsite
            LEFT JOIN {examregistrar_exams} e ON ss.examid = e.id AND e.visible = 1
            JOIN {course} c ON e.courseid = c.id
            WHERE sr.examsession = :examsession AND sr.roomid = :roomid $venuewhere
            GROUP BY ss.examid
            ORDER BY e.programme ASC, c.shortname ASC ";

    return $DB->get_records_sql($sql, $params);
}





/**
 * Gets a collection of users booked & allocated assigned to a venue in this session
 * optionally restrited for room
 *
 * @param int $session the ID for the exam session
 * @param int $bookedsite the ID for the venue the room belongs or is booked
 * @return array exams
 */
function examregistrar_get_session_venue_users($session, $bookedsite, $room = 0) {
    global $DB;

    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite);
    $roomwhere = '';
    if($room) {
        $roomwhere = ' AND ss.roomid = :room ';
        $params['room'] = $room;
    }

    // get data for usertable
    $names = get_all_user_name_fields(true, 'u');
    $sql = "SELECT  b.id AS bid,  b.userid, b.examid, c.shortname, c.fullname, 
                    ss.id as sid, ss.roomid, ss.seat, ss.showing, ss.taken, ss.certified, ss.status, 
                    u.username, u.idnumber, $names,
                    (SELECT COUNT(b2.examid)  FROM {examregistrar_bookings} b2
                                              JOIN {examregistrar_exams} e2 ON b2.examid = e2.id
                                                WHERE b2.userid = b.userid AND b2.bookedsite = b.bookedsite AND b2.booked = 1
                                                AND  e2.examsession = e.examsession
                                                GROUP BY b2.userid ) AS numexams
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON b.examid = e.id AND e.examsession = :examsession
            JOIN {user} u ON b.userid = u.id
            JOIN {course} c ON c.id = e.courseid
            LEFT JOIN {examregistrar_session_seats} ss ON  b.userid = ss.userid AND b.examid = ss.examid AND b.bookedsite = ss.bookedsite AND e.examsession = ss.examsession
            WHERE b.bookedsite = :bookedsite AND b.booked = 1 $roomwhere
            GROUP BY b.userid, b.examid
            ORDER BY u.lastname ASC, u.firstname ASC, u.idnumber ASC, c.shortname ASC";

    return $DB->get_records_sql($sql, $params);
}


/**
 * Looks for and get an instance of examregistrar in a course for an exam
 *
 * @param stdClass $exam object
 * @return mixed false if failed or int course module id for instance
 */
function examregistrar_get_course_instance($exam) {
    global $DB;
    $module = $DB->get_field('modules', 'id', array('name'=>'examregistrar'));

    $examregid = 0;
    if(isset($exam->examregid)) {
        $examregid = $exam->examregid;
    } elseif(isset($exam->examid)) {
        $examregid = $DB->get_field('examregistrar_exams', 'examregid', array('id'=>$exam->examid));
    } elseif(isset($exam->id)) {
        $examregid = $DB->get_field('examregistrar_exams', 'examregid', array('id'=>$exam->id));
    }
    $primary = $DB->get_field('examregistrar', 'primaryidnumber', array('id'=>$examregid));

    $sql = "SELECT e.id, cm.id as cmid
            FROM {course_modules} cm
            JOIN {examregistrar} e ON cm.instance = e.id AND cm.course = e.course
            JOIN {course_sections} cs ON cs.id = cm.section
            WHERE cm.module = :module AND cm.course = :course AND e.primaryreg = :primary
                    AND e.annuality = :annuality
            ORDER BY cs.section ASC ";
    $params = array('module'=>$module, 'primary'=>$primary, 'course'=>$exam->courseid, 'annuality'=>$exam->annuality);
    if($mods = $DB->get_records_sql_menu($sql, $params, 0, 1)) {
        return reset($mods);
    }
    return false;
}

/**
 * Gets a collection of exams assigned to an exam session
 * optionally restricted for bookedsite and with allocation data
 *
 * @param int $sessionid the ID for the exam session
 * @param int $bookedsite the ID for the venue the room belongs or is booked
 * @param string $sort  how to sort results, empty = shortname, others fullname/booked
 * @param bool bookingss include booking data on results
 * @param bool allocations include allocation occupancy on results
 * @param bool $onlyspecial if true, only exams of special extra turns are returned
 * @return array rooms data
 */
function examregistrar_get_session_exams($sessionid, $bookedsite = 0, $sort = '', $bookings = false, $allocations=false, $onlyspecial=false) {
    global $DB;

    $params = array('examsession'=>$sessionid);

    $order = '';
    if($sort) {
        $order = " $sort ";
        if($sort == 'booked' || $sort == 'allocated') {
            $order .= ' DESC';
        }
        $order .= ', ';
    }
    $order .= ' e.programme ASC, c.shortname  ASC ';

    $countbookings = '';
    if($bookings) {
        $venuewhere1 = '';
        if($bookedsite) {
            $venuewhere1 = ' AND b.bookedsite = :bookedsite1 ';
            $params['bookedsite1'] = $bookedsite;
        }
        $countbookings = ", (SELECT COUNT(b.userid)
                            FROM {examregistrar_bookings} b
                            WHERE b.examid = e.id AND b.booked = 1 $venuewhere1
                            GROUP BY b.examid
                            ) AS booked ";

    }

    $countallocated = '';
    $joinallocated = '';
    if($allocations) {
        $countallocated = ', COUNT(ss.userid) AS allocated ';
        $venuewhere2 = '';
        if($bookedsite) {
            $venuewhere2 = ' AND ss.bookedsite = :bookedsite2 ';
            $params['bookedsite2'] = $bookedsite;
        }
        $joinallocated = "LEFT JOIN {examregistrar_session_seats} ss ON ss.examid = e.id AND ss.roomid > 0  $venuewhere2 ";

    }

    $specialwhere = '';
    if($onlyspecial) {
        $specialwhere = ' AND e.callnum < 0 ';
    }

    $sql = "SELECT e.id, e.programme, e.courseid, e.callnum, e.examsession, e.examscope, c.shortname, c.fullname, c.idnumber $countallocated  $countbookings
            FROM {examregistrar_exams} e
            JOIN {course} c ON e.courseid = c.id
            $joinallocated
            WHERE e.examsession = :examsession AND e.visible = 1 $specialwhere
            GROUP BY e.id
            ORDER BY $order ";

    return $DB->get_records_sql($sql, $params);
}


/**
 * Gets a collection of rooms assigned to an exam in this session
 * optionally restrited for bookedsite
 *
 * @param int $sessionid the ID for the exam session
 * @param int $bookedsite the ID for the venue the room belongs or is booked
 * @return array exams
 */
function examregistrar_get_sessionexam_rooms($examid, $sessionid, $bookedsite = 0) {
    global $DB;

    $exams = array();

    $params = array('examid'=>$examid, 'examsession'=>$sessionid);
    $venuewhere = '';
    if($bookedsite) {
        $venuewhere = ' AND sr.bookedsite = :bookedsite ';
        $params['bookedsite'] = $bookedsite;
    }

    $order = '';
    if(!$bookedsite) {
        $order = ' venueidnumber ASC, '.$order;
    }

    $sql = "SELECT sr.roomid, ss.examid, e.name AS name, e.idnumber AS idnumber,
                                         ev.name AS venuename, ev.idnumber AS venueidnumber
            FROM {examregistrar_session_rooms} sr
            JOIN {examregistrar_locations} l ON sr.roomid = l.id
            JOIN {examregistrar_elements} e ON l.examregid = e.examregid AND e.type = 'locationitem' AND l.location = e.id
            JOIN {examregistrar_locations} v ON sr.bookedsite = v.id
            JOIN {examregistrar_elements} ev ON v.examregid = ev.examregid AND ev.type = 'locationitem' AND v.location = ev.id
            LEFT JOIN {examregistrar_session_seats} ss ON sr.roomid = ss.roomid AND sr.examsession = ss.examsession AND sr.bookedsite = ss.bookedsite
            WHERE sr.examsession = :examsession AND ss.examid = :examid $venuewhere
            GROUP BY sr.roomid
            ORDER BY $order e.name ASC ";

    return $DB->get_records_sql($sql, $params);
}

/**
 * Counts total number of exam bookings and total seated
 *
 * @param int $sessionid the ID for the exam session
 * @param int $bookedsite the ID for the venue the room belongs or is booked
 * @return array exams
 */
function examregistrar_qc_counts($sessionid, $bookedsite = 0) {
    global $DB;

    $params = array('examsession'=>$sessionid);
    $venuewhere = '';
    if($bookedsite) {
        $venuewhere = ' AND b.bookedsite = :bookedsite ';
        $params['bookedsite'] = $bookedsite;
    }

    $sql = "SELECT COUNT(b.id)
                FROM {examregistrar_bookings} b
                JOIN {examregistrar_exams} e ON e.id = b.examid AND e.examsession = :examsession AND e.visible = 1
                WHERE b.booked = 1 $venuewhere ";
    $totalbooked = $DB->count_records_sql($sql, $params);

    if($bookedsite) {
        $venuewhere = ' AND bookedsite = :bookedsite ';
        $params['bookedsite'] = $bookedsite;
    }
    $select = " examsession = :examsession AND roomid > 0 $venuewhere ";
    $totalseated = $DB->count_records_select('examregistrar_session_seats', $select, $params);

    return array($totalbooked, $totalseated);
}

/**
 * Gets a collection of bookings not allocated in a session
 *
 * @param int $sessionid the ID for the exam session
 * @param int $bookedsite the ID for the venue the room belongs or is booked
 * @return array exams
 */
function examregistrar_booking_seating_qc($sessionid, $bookedsite = 0, $sort='') {
    global $DB;

    $params = array('session1'=>$sessionid, 'session2'=>$sessionid);
    $venuewhere = '';
    if($bookedsite) {
        $venuewhere = ' AND b.bookedsite = :bookedsite ';
        $params['bookedsite'] = $bookedsite;
    }

    $order = 'u.lastname ASC, ';
    if($sort) {
        $order .= " $sort ";
        if($sort == 'booked' || $sort == 'allocated') {
            $order .= ' DESC';
        }
        $order .= ', ';
    }
    $order .= ' e.programme ASC, c.shortname  ASC ';

    $names = get_all_user_name_fields(true, 'u');
    $sql = "SELECT b.id, b.examid, b.userid, b.bookedsite, e.examsession, e.programme, e.callnum, c.shortname, c.fullname,
                u.idnumber, $names
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON e.id = b.examid AND e.examsession = :session1 AND e.visible = 1
            JOIN {course} c ON c.id = e.courseid
            JOIN {user} u ON b.userid = u.id
            LEFT JOIN {examregistrar_session_seats} ss ON (ss.examsession = e.examsession AND ss.examid = b.examid
                                                            AND ss.userid = b.userid AND ss.bookedsite = b.bookedsite AND ss.roomid > 0)
            WHERE b.booked = 1 AND e.examsession = :session2 $venuewhere
                    AND ss.id IS NULL
            ORDER BY $order";

    return $DB->get_records_sql($sql, $params);
}


/**
 * Sets hierarchy data (parent, depth, path) for a given location
 *
 * @param int $locationid the ID for room in Locations table
 * @return bool success
 */
function examregistrar_set_location_tree($locationid) {
    global $DB;

    print_object("Entrando en locationid $locationid");
    
    $location = $DB->get_record('examregistrar_locations', array('id'=>$locationid), '*', MUST_EXIST);
    $oldpath = $location->path;
    $path = $location->path;
    $success = false;
    if($location->parent) {
        $parent = $DB->get_record('examregistrar_locations', array('id'=>$location->parent), '*', MUST_EXIST);
        // avoid circular references
        if(strpos($parent->path, '/'.$location->id) === false) {
            $path = $parent->path.'/'.$location->id;
            $depth = count(explode('/', $path)) - 1;
            $location->path = $path;
            $location->depth = $depth;
        } else {
            $parent = 0;
            if($location->depth > 1) {
                $parents = explode('/', $path);
                $parent = $parents[$location->depth];
            }
            $location->parent = $parent;
        }
    } else {
        $path = '/'.$location->id;
        $depth = count(explode('/', $path)) - 1;
        $location->path = $path;
        $location->depth = $depth;
    }

    if($location->depth && $location->path) {
        $success = $DB->update_record('examregistrar_locations', $location);
    }

    if($success && ($path != $oldpath)) {
        //rebuild children's paths
        examregistrar_rebuild_location_paths($location);
        
    }
    return $success;
}


/**
 * Recursive rebuild paths & depths for children of a given parent
 *
 * @param int $locationid the ID for room in Locations table
 * @return bool success
 */
function examregistrar_rebuild_location_paths($parent) {
    global $DB;

    if(!isset($parent->path) || !isset($parent->id)) {
        return;
    }
    if($parent->id == 0) {
        $parent->path = '';
    }

    if($children = $DB->get_records('examregistrar_locations', array('parent'=>$parent->id), '', 'id, parent, sortorder, path, depth')) {
        foreach($children as $child) {
            $child->path = $parent->path.'/'.$child->id;
            $child->depth = count(explode('/', $child->path)) - 1;
            $DB->update_record('examregistrar_locations', $child);
        }
        //now recursive part
        foreach($children as $child) {
            examregistrar_rebuild_location_paths($child);
        }
    }

    return;
}


/**
 * Searches Locations for children of a venue (and venue itself) with given type & seats
 *
 * @param int/object $venue the ID for room in Locations table or full record
 * @param int $type the locationtype for the desired rooms
 * @param int $seats minimum number of setas in the returned locations
 * @param int $returnids whether returning full objects of just IDs
 * @return array locations
 */
function examregistrar_get_venue_locations($venue, $type = '', $seats = -1, $returnids=false) {
    global $DB;

    if(!$venue) {
        return false;
    }

    if(is_numeric($venue)) {
        $path = $DB->get_field('examregistrar_locations', 'path', array('id'=>$venue));
        $venueid = $venue;
    } else {
        $path = $venue->path;
        $venueid = $venue->id;
    }

    $likepath = $DB->sql_like('path', ':path');
    $params['path'] = $path.'/%';
    $params['venue'] = $venueid;
    $select = " (id = :venue  OR $likepath ) ";

    if($seats) {
        $select .= ' AND seats >= :seats ';
        $params['seats'] = $seats;
    } else {
        $select .= ' AND seats = 0 ';
    }

    if($type) {
        $select .= ' AND locationtype = :type ';
        $params['type'] = $type;
    }

    $return = '';
    if($returnids) {
        return $DB->get_records_select_menu('examregistrar_locations', $select, $params, '', 'id, id AS ids');
    }
    return $DB->get_records_select('examregistrar_locations', $select, $params);
}


/**
 * Checks if venue has only one room
 *
 * @param int/object $venue the ID for room in Locations table or full record
 * @param int $returnids whether returning full objects of just IDs
 * @return mixed bool/int/object
 */
function examregistrar_is_venue_single_room($venue, $returnids=true) {
    $room = false;
    if($rooms = examregistrar_get_venue_locations($venue, '', -1, $returnids)) {
        if(count($rooms) === 1) {
            $room = reset($rooms);
        }
    } else {
        \core\notification::error(get_string('venueerror', 'examregistrar'));
    }
    
    return $room;
}



/**
 * Returns list of venues the user has a room allocation in as staffer
 *
 * @param object $examregistrar
 * @param int $userid
 * @param int $session the session to check room allocation
 * @return array location IDs
 */
function examregistrar_get_user_venues($examregistrar, $userid, $session=0) {
    $venues = array();
    $venueelement = examregistrar_get_venue_element($examregistrar);
    // check assignation as staffer in venue level
    if($rooms = examregistrar_get_user_rooms($examregistrar, $userid, $venueelement)) {
        foreach($rooms as $room) {
            $venues[$room->id] = $room->id;
        }
    }
    // now other rooms
    if($rooms = examregistrar_get_user_rooms($examregistrar, $userid, 0, $session)) {
        foreach($rooms as $room) {
            if($venueid = examregistrar_get_room_venue($room, $venueelement)) {
                $venues[$venueid] = $venueid;
            }
        }
    }

    return $venues;
}



/**
 * Returns first location of the given type that is an ancestor or given room
 * Searches Location path for suitable venues
 *
 * @param int/object $roomid the ID for room in Locations table or full record
 * @param int venue type
 * @param boolean $returnid return full object or justs id
 * @return mixed int roomid/false
 */
function examregistrar_get_room_venue($roomid, $venuetype, $returnid=true) {
    global $DB;

    $venue = false;

    if(is_int($roomid)) {
        $path = $DB->get_field('examregistrar_locations', 'path', array('id'=>$roomid));
    } else {
        $path = $roomid->path;
    }

    if($paths = explode('/', $path)) {
        array_shift($paths);
        $parents = $DB->get_records_list('examregistrar_locations', 'id', $paths);
        foreach($paths as $pid) {
            $parent = $parents[$pid];
            if($parent->locationtype == $venuetype) {
                $venue = $parent;
                break;
            }
        }
        if($venue && $returnid) {
            $venue = $venue->id;
        }
    }
    return $venue;
}


/**
 * Returns menu of suitable room parents
 * Searches Locations for other locations that can serve as parent for a room (exlude its children & ancestors)
 *
 * @param object $examregistrar the examregistrar object
 * @param int $roomid the ID for room in Locations table
 * @param string $fields flag to set return format. 'name': menu id/roomname; 'ids': just rooms ids; other: rooms objects
 * @param boolean $choose if a choose items is first in menu
 * @return bool success
 */
function examregistrar_get_potential_parents($examregistrar, $roomid = 0, $fields = 'name', $choose = false) {
    global $DB;
    // potential parents are venues and seats=0 locations that are not children of this one

    $venueelement = examregistrar_get_venue_element($examregistrar);

    $select = '( locationtype = :venuetype  OR seats = 0 ) ';
    $params['venuetype'] = $venueelement;
    if($roomid > 0) {
        $select .= ' AND '.$DB->sql_like('path', ':path', false, false, true);
        $params['path'] = "%/$roomid/%";
    }

    if($fields == 'name') {
        $sql = "SELECT l.id, CONCAT(el.name,' (',el.idnumber,')') AS itemname
                FROM {examregistrar_locations} l
                JOIN {examregistrar_elements} el ON el.examregid = l.examregid AND el.type = 'locationitem' AND l.location = el.id
                WHERE $select
                ORDER BY l.parent ASC, itemname ASC ";
        $parents = $DB->get_records_sql_menu($sql, $params);
        if($choose) {
            $parents = array('0' => get_string('choose')) + $parents;
        }
        return $parents;
    } elseif($fields == 'ids') {
        return $DB->get_records_select_menu('examregistrar_locations', $select, $params, 'id ASC', 'id, id');
    }
    return $DB->get_records_select('examregistrar_locations', $select, $params, 'id ASC', $fields);
}


/**
 * Recall a room for a an exam session
 *
 * @param int $roomid the ID for room in Locations table
 * @param int $sessionid the ID for the exam session
 * @param string $format it true, only userids are returned
 * @return bool success
 */
function examregistrar_addupdate_sessionroom($sessionid, $roomid, $bookedsite, $visible = null) {
    global $DB, $USER;
    if(!$bookedsite) {
        throw new moodle_exception('missingbookedsite', 'examregistrar');
    }
    $params = array('examsession'=>$sessionid, 'roomid'=>$roomid);
    if($record = $DB->get_record('examregistrar_session_rooms', $params)){
        $record->bookedsite = $bookedsite;
        $record->available = 1;
        $success = $DB->update_record('examregistrar_session_rooms', $record);
    } else {
        $record = new stdClass;
        $record->examsession = $sessionid;
        $record->bookedsite = $bookedsite;
        $record->roomid = $roomid;
        if(isset($visible)) {
            $record->available = $visible;
        }
        $success = $DB->insert_record('examregistrar_session_rooms', $record);
    }
    return $success;
}


/**
 * Releases a room from an exam session
 *
 * @param int $roomid the ID for room in Locations table
 * @param int $sessionid the ID for the exam session
 * @param string $format it true, only userids are returned
 * @return bool success
 */
function examregistrar_remove_sessionroom($sessionid, $roomid, $visible = null) {
    global $DB, $USER;

    $success = false;
    $params = array('examsession'=>$sessionid, 'roomid'=>$roomid);
    if(isset($visible)) {
        $params['available'] = $visible;
    }
    if($record = $DB->get_record('examregistrar_session_rooms', $params)){
        $record->available = 0;
        $success = $DB->update_record('examregistrar_session_rooms', $record);
    }
    return $success;
}


/**
 * Adds (or updates if already existing) a user as staff in a room
 *
 * @param int $roomid the ID for room in Locations table
 * @param int $sessionid the ID for the exam session
 * @param string $format it true, only userids are returned
 * @return bool success
 */
function examregistrar_addupdate_roomstaffer($sessionid, $roomid, $userid, $role, $info='', $visible = null) {
    global $DB, $USER;

    $success = false;
    $params = array('examsession'=>$sessionid, 'locationid'=>$roomid, 'userid'=>$userid, 'role'=>$role);
    if($record = $DB->get_record('examregistrar_staffers', $params)){
        if(isset($visible)) {
            $record->visible = $visible;
        }
        $record->modifierid = $USER->id;
        $record->timemodified = time();
        if($info) {
            $record->info = $info;
        }
        $success = $DB->update_record('examregistrar_staffers', $record);
    } else {
        $record = new stdClass;
        $record->examsession = $sessionid;
        $record->locationid = $roomid;
        $record->userid = $userid;
        $record->role = $role;
        if($info) {
            $record->info = $info;
        }
        if(isset($visible)) {
            $record->visible = $visible;
        }
        $record->modifierid = $USER->id;
        $record->timemodified = time();
        $success = $DB->insert_record('examregistrar_staffers', $record);
    }
    return $success;
}

/**
 * Returns a list of users that are assigned as staff in a room
 *
 * @param int $roomid the ID for room in Locations table
 * @param int $sessionid the ID for the exam session
 * @param string $format it true, only userids are returned
 * @return bool success
 */
function examregistrar_remove_roomstaffers($sessionid, $roomid, $userid=0, $role='', $visible = null) {
    global $DB, $USER;

    $success = false;
    $params = array('examsession'=>$sessionid, 'locationid'=>$roomid);
    if($userid) {
        $params['userid'] = $userid;
    }
    if($role) {
        $params['role'] = $role;
    }
    if(isset($visible)) {
        $params['visible'] = $visible;
    }
    if($records = $DB->get_records('examregistrar_staffers', $params)){
        foreach($records as $record) {
        $record->visible = 0;
        $record->modifierid = $USER->id;
        $record->timemodified = time();
        $success = $DB->update_record('examregistrar_staffers', $record);
        }
    }
    return $success;
}



//////////////////////////////////////////////////////////////////////////////////
//   Booking functions                                                         //
////////////////////////////////////////////////////////////////////////////////


/**
 * Checks booked exams and make unique bookings, holds ALL users that booked, but only once each
 *
 * @param int $examregprimaryid 
 * @param int $bookingid the locationID for the booking
 * @param int $now timestamp
 * @return stadclass voucher object
 */
function examregistrar_set_booking_voucher($examregprimaryid, $bookingid, $now) {
    global $DB;
    $voucher = new stdClass();
    $voucher->examregid = $examregprimaryid;
    $voucher->bookingid = $bookingid;
    $voucher->uniqueid = strtoupper(base_convert(bin2hex(random_bytes_emulate(10)), 16, 36));
    $voucher->timemodified = $now;
    do {
        $voucher->uniqueid = strtoupper(base_convert(bin2hex(random_bytes(10)), 16, 36));
    } while ($DB->record_exists('examregistrar_vouchers', array('examregid'=>$examregprimaryid, 'uniqueid' => $voucher->uniqueid)));
    $voucher->id = $DB->insert_record('examregistrar_vouchers', $voucher);

    return $voucher;
}


/**
 * Checks booked exams and make unique bookings, holds ALL users that booked, but only once each
 *
 * @param int $session exam session id number (as used in bookings table)
 * @param int $bookedsite the locationID for the booking
 * @param int $timelimit check only bookings mae after this datetime
 * @return array of uniquebookings, userid are uniques
 */
function examregistrar_get_unique_bookings($session, $bookedsite, $timelimit = 0) {
    global $DB;

    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite);
    $timewhere = '';
    if($timelimit) {
        $timewhere = ' AND b.timemodified > :timelimit ';
        $params['timelimit'] = $timelimit;
    }

    $sql = "SELECT b.*,
                (SELECT COUNT(userid)
                    FROM  {examregistrar_bookings} b2
                    WHERE b2.examid = b.examid AND b2.bookedsite = b.bookedsite AND b2.booked = 1
                    GROUP by b2.examid ) AS partners
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON b.examid = e.id
            WHERE e.examsession = :examsession AND b.bookedsite = :bookedsite AND b.booked = 1 $timewhere
            ORDER BY b.userid ASC, partners ASC
            " ;

    $bookings = $DB->get_records_sql($sql, $params);

    $uniquebookings = array();
    foreach($bookings as $booking) {
        if(!isset($uniquebookings[$booking->userid])) {
            $uniquebookings[$booking->userid] = $booking;
        }
    }

    return $uniquebookings;
}


/**
 * Checks booked exams and returns bookings for users with several bookings
 *
 * @param int $session exam session id number (as used in bookings table)
 * @param int $bookedsite the locationID for the booking
 * @param int $timelimit check only bookings mae after this datetime
 * @return array of bookings,
 */
function examregistrar_get_additional_bookings($session, $bookedsite, $timelimit = 0) {
    global $DB;

    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite);
    $timewhere = '';
    if($timelimit) {
        $timewhere = ' AND b.timemodified > :timelimit ';
        $params['timelimit'] = $timelimit;
    }

    $sql = "SELECT b.*, (SELECT  COUNT(b2.id)
                            FROM {examregistrar_bookings} b2
                            JOIN {examregistrar_exams} e2 ON b2.examid = e2.id
                            WHERE b2.bookedsite = b.bookedsite AND b2.userid = b.userid AND e2.examsession = e.examsession AND b2.booked = 1
                            GROUP BY b2.userid) AS numexams
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON b.examid = e.id
            WHERE e.examsession = :examsession AND b.bookedsite = :bookedsite  AND b.booked = 1
            HAVING numexams > 1";

    $bookings = $DB->get_records_sql($sql, $params);

    return $bookings;
}


/**
 * Checks booked exams and create/update data in session_seats allocation table
 *
 * @param int $session exam session id number (as used in bookings table)
 * @param int $bookedsite the locationID for the booking
 */
function examregistrar_session_seats_makeallocation($session, $bookedsite) {
    global $DB, $USER;

    /// now update database, table session_seats
    /// delete non booked
    $sql = "SELECT id, userid
            FROM {examregistrar_session_seats} ss
            WHERE ss.examsession = :examsession AND ss.bookedsite = :bookedsite
                AND NOT EXISTS (SELECT 1
                                FROM {examregistrar_bookings} b
                                JOIN {examregistrar_exams} e ON b.examid = e.id
                                WHERE e.examsession = ss.examsession AND b.bookedsite = ss.bookedsite AND b.userid = ss.userid AND b.booked = 1)
            ";
    if($notbooked = $DB->get_records_sql_menu($sql,  array('examsession'=>$session, 'bookedsite'=>$bookedsite))) {
        if($chunks = array_chunk($notbooked, 500)) {
            foreach($chunks as $notbooked) {
                $DB->delete_records_list('examregistrar_session_seats', 'userid', $notbooked);
            }
        }
    }

    /// delete all additionals
    $select = " examsession = :examsession AND bookedsite = :bookedsite AND additional > 0 ";
    $DB->delete_records_select('examregistrar_session_seats', $select, array('examsession'=>$session, 'bookedsite'=>$bookedsite));


    // first check for single room venue
    if($roomid = examregistrar_is_venue_single_room($bookedsite)) {
        // single room venues do not user unique/additional, all are additionals
        $sql = "SELECT b.id, b.userid, b.examid, b.booked, b.bookedsite
                FROM {examregistrar_bookings} b
                JOIN {examregistrar_exams} e ON b.examid = e.id
                WHERE e.examsession = :examsession AND b.bookedsite = :bookedsite AND b.booked = 1 ";
        if($bookings = $DB->get_records_sql($sql, array('examsession'=>$session, 'bookedsite'=>$bookedsite))) {
            $now = time();
            $record = new stdclass;
            $record->examsession = $session;
            $record->bookedsite = $bookedsite;
            $record->additional = 0;
            $record->roomid = $roomid;
            $record->timecreated = $now;
            $record->timemodified = $now;
            $record->component =  '';
            $record->modifierid =  $USER->id;
            $record->reviewerid = 0;

            foreach($bookings as $booking) {
                $record->examid = $booking->examid;
                $record->userid = $booking->userid;
                $record->additional = $booking->examid;
                $DB->insert_record('examregistrar_session_seats', $record);
            }
        }

        return true;
    } else {
        // this is not a single room venue then REMOVE any room assignation to this venue as roomid
        $DB->delete_records('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'roomid'=>$bookedsite));
    }

/// We are here ONLY if bookedsite is not single room


    // now we process case for venues with multiple rooms
    /// get bookings
    $uniquebookings = examregistrar_get_unique_bookings($session, $bookedsite);
    $additionalbookings = examregistrar_get_additional_bookings($session, $bookedsite);

    /// compare new unique bookings with old allocations
    $deleting = array();
    if($rs = $DB->get_recordset('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite))) {
        foreach ($rs as $old) {
            if(isset($uniquebookings[$old->userid])) {
                $new = $uniquebookings[$old->userid];
                // only check this, additional is certain to be 0, deleted any others
                if($old->examid != $new->examid) {
                    $deleting[] = $old->id;
                } else {
                    unset($uniquebookings[$old->userid]);
                }
            } else {
                $deleting[] = $old->id;
            }

        }
        $rs->close();
    }
    if($deleting) {
        if($chunks = array_chunk($deleting, 500)) {
            foreach($chunks as $deleting) {
                $DB->delete_records_list('examregistrar_session_seats', 'id', $deleting);
            }
        }
    }

    /// if remain some $uniquebookings elements, there are new bookings
    $now = time();
    $record = new stdclass;
    $record->examsession = $session;
    $record->bookedsite = $bookedsite;
    $record->additional = 0;
    $record->roomid = 0;
    $record->timecreated = $now;
    $record->timemodified = $now;
    $record->component =  '';
    $record->modifierid =  $USER->id;
    $record->reviewerid = 0;

    if($uniquebookings) {
        foreach($uniquebookings as $booking) {
            $record->examid = $booking->examid;
            $record->userid = $booking->userid;
            $DB->insert_record('examregistrar_session_seats', $record);
        }
    }

    /// now process multiple bookings
    if($additionalbookings) {
        $users = array();
        foreach($additionalbookings as $booking) {
            if(isset($users[$booking->userid])) {
            $users[$booking->userid][$booking->examid] = $booking->examid;
            } else {
                $users[$booking->userid] = array($booking->examid=>$booking->examid);
            }
        }
        //print_object($users);
        //print_object("    users -----_");
        foreach($users as $userid => $exams) {
            if($mainalloc = $DB->get_record('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'userid'=>$userid, 'additional'=>0))) {
                $room = $mainalloc->roomid;
                unset($exams[$mainalloc->examid]);
            } else {
                $room = 0;
                $exam = array_shift($exams);
                $record->userid = $userid;
                $record->examid = $exam;
                $record->roomid = $room;
                $record->additional = 0;
                $DB->insert_record('examregistrar_session_seats', $record);
            }
            //print_object($exams);
            //print_object("  for user= $userid   exams");
            foreach($exams as $examid) {
                $record->userid = $userid;
                $record->examid = $examid;
                $record->roomid = $room;
                $record->additional = $examid;
                $DB->insert_record('examregistrar_session_seats', $record);
            }
        }
    }
}


/**
 * Checks booked exams and create/update data in session_seats allocation table
 *
 * @param int $session exam session id number (as used in bookings table)
 * @param int $bookedsite the locationID for the booking
 * @param int $timelimit check only bookings mae after this datetime
 * @return array
 */
function examregistrar_session_seats_newbookings($session, $bookedsite, $timelimit) {
    global $DB, $USER;

    // check single room venue
    $singleroomid = examregistrar_is_venue_single_room($bookedsite);

    /// get dropped bookings
    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'timelimit'=> $timelimit);
    $sql = "SELECT b.id, b.userid, b.examid, b.booked
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON b.examid = e.id
            WHERE e.examsession = :examsession AND b.bookedsite = :bookedsite AND b.timemodified > :timelimit AND b.booked = 0
                AND NOT EXISTS (SELECT 1
                                FROM {examregistrar_bookings} b2
                                WHERE b.bookedsite = b2.bookedsite AND b.userid = b2.userid AND b.examid = b2.examid AND b2.booked = 1)
             " ;
    if($droppedbookings = $DB->get_records_sql($sql, $params)) {

    //print_object($droppedbookings);
    //print_object(" -----  droppedbookings -------$timelimit----");
        $deleting = array();
        $refreshing = array();
        foreach($droppedbookings as $booking) {
            if($allocation = $DB->get_record('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite,
                                                                                  'userid'=>$booking->userid, 'examid'=>$booking->examid))) {
                $deleting[] = $allocation->id;
                if(!$allocation->additional) {
                    // anycase update additionals, after deleting all;
                    $refreshing[$allocation->userid] = $allocation->roomid;
                }
            }
        }
        if($deleting) {
            if($chunks = array_chunk($deleting, 500)) {
                foreach($chunks as $deleting) {
                    $DB->delete_records_list('examregistrar_session_seats', 'id', $deleting);
                }
            }
        }
        if($refreshing && !$singleroomid) {
            foreach($refreshing as $userid => $room) {
                examregistrar_update_additional_allocations($session, $bookedsite, $userid, $room, $timelimit);
            }
        }
    }

    /// get new positive bookings
    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'timelimit'=> $timelimit);
    $sql = "SELECT b.id, b.userid, b.examid, b.booked
            FROM {examregistrar_bookings} b
            JOIN {examregistrar_exams} e ON b.examid = e.id
            WHERE e.examsession = :examsession AND b.bookedsite = :bookedsite AND b.timemodified > :timelimit AND b.booked = 1
             " ;
    if($newbookings = $DB->get_records_sql($sql, $params)) {
    //print_object($newbookings);
    //print_object(" -----  newedbookings ------$timelimit-----");
        $now = time();
        $record = new stdclass;
        $record->examsession = $session;
        $record->bookedsite = $bookedsite;
        $record->additional = 0;
        $record->roomid = 0;
        $record->timecreated = $now;
        $record->timemodified = $now;
        $record->component =  '';
        $record->modifierid =  $USER->id;
        $record->reviewerid = 0;
        
        $refreshing = array();
        foreach($newbookings as $booking) {
            $room = 0;
            if($singleroomid) {
                $room = $singleroomid;
            } else {
                // add this as additional
                if($mainalloc = $DB->get_record('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite, 'userid'=>$booking->userid, 'additional'=>0))) {
                    $room = $mainalloc->roomid;
                } else {
                    // add this as main exam
                    $room = 0;
                }
            }
            $record->roomid = $room;
            $record->userid = $booking->userid;
            // if records exists do not add again, error by duplicated in database
//                         if(!$DB->record_exists('examregistrar_session_seats', array('examsession'=>$session, 'bookedsite'=>$bookedsite,
//                                                                                   'userid'=>$booking->userid, 'examid'=>$booking->examid))) {
            if($seating = $DB->get_record('examregistrar_session_seats', array('userid'=>$booking->userid,'examid'=>$booking->examid))) {
                $seating->bookedsite = $record->bookedsite;
                $seating->roomid = $record->roomid;
                $seating->timemodified = $record->timemodified;
                $seating->component =  $record->component;
                $seating->modifierid =  $record->modifierid;
                $DB->update_record('examregistrar_session_seats', $seating);
            } else {
                $record->examid = $booking->examid;
                $record->additional = $booking->examid;
                $DB->insert_record('examregistrar_session_seats', $record);
            }
            // anycase update additionals, after adding all;
            if(!$singleroomid) {
                $refreshing[$record->userid] = $record->roomid;
            }
        }
        if($refreshing && !$singleroomid) {
            foreach($refreshing as $userid => $room) {
                examregistrar_update_additional_allocations($session, $bookedsite, $userid, $room, $timelimit);
            }
        }
    }
}


/**
 * Updates room allocation for selected users in session
 *
 * @param int $session exam session id number (as used in bookings table)
 * @param int $bookedsite the locationID for the booking
 * @param array $params an associative array of additional search params suitable for get_records_menu
 * @param int $newroom check only bookings mae after this datetime
 * @param string $sort sortig for these users
 * @param int $limitfrom return a subset of records, starting at this point (optional).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return void
 */
function examregistrar_update_usersallocations($session, $bookedsite, $search, $newroom, $sort='', $limitfrom=0, $limitnum=0) {
    global $DB, $USER;

    $success = false;
    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite);

    if(!$sort) {
        $sort = ' id ASC ';
    }

    if($users = $DB->get_records_menu('examregistrar_session_seats', $params + $search, $sort, 'id, userid', $limitfrom, $limitnum)) {
        $chunks = array_chunk($users, 500, true);
        foreach($chunks as $users) {
            list($insql, $inparams) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'user');
            $select = "examsession = :examsession AND bookedsite = :bookedsite AND userid $insql ";
            if($DB->set_field_select('examregistrar_session_seats', 'roomid', $newroom, $select, $params + $inparams)) {
                $now = time();
                $DB->set_field_select('examregistrar_session_seats', 'timemodified', $now, $select, $params + $inparams);
                $DB->set_field_select('examregistrar_session_seats', 'modifierid', $USER->id, $select, $params + $inparams);
                $success = true;
            }
        }
    }

    if($newroom) {
        //$select = "   room = x AND additional > 0       "
        //$extrausers = get_fieldset_select('examregistrar_session_seats', 'userid', $select, array $params=null)
        //array_unique($extrausers)
        $sql = "SELECT  userid, COUNT(examid) AS numexams
                FROM {examregistrar_session_seats}
                WHERE examsession = :examsession AND bookedsite = :bookedsite  AND roomid = :roomid
                GROUP BY userid
                HAVING numexams > 1
                ORDER BY numexams DESC  ";
        if($extrausers = $DB->get_records_sql_menu($sql, $params + array('roomid'=>$newroom))){
            foreach($extrausers as $userid => $numexams) {
                examregistrar_update_additional_allocations($session, $bookedsite, $userid, $newroom);
            }
        }
    }
    return $success;
}


/**
 * Updates assignation of main/additional exam for user in a room
 *
 * @param int $session exam session id number (as used in bookings table)
 * @param int $bookedsite the locationID for the booking
 * @param int $userid the user whose allocation is updated
 * @param int $roomid the room to check
 * @param int $timelimit used only when checking new bookings
 * @return void
 */
function examregistrar_update_additional_allocations($session, $bookedsite, $userid, $roomid, $timelimit = 0) {
    global $DB;

    $params = array('examsession'=>$session, 'bookedsite'=>$bookedsite);

    $sql = "SELECT ss.*,  (SELECT COUNT(p.userid)
                            FROM {examregistrar_session_seats} p
                            WHERE p.examsession = ss.examsession AND p.bookedsite = ss.bookedsite AND p.examid = ss.examid
                                    AND p.roomid = ss.roomid AND  p.userid <> ss.userid
                            GROUP BY p.examid) as partners
            FROM {examregistrar_session_seats} ss
            WHERE  ss.examsession = :examsession AND ss.bookedsite = :bookedsite  AND ss.roomid = :roomid AND ss.userid = :userid

            ORDER BY partners DESC  ";
    if($exams = $DB->get_records_sql($sql, $params + array('roomid'=>$roomid, 'userid'=>$userid))) {
        //print_object($exams);
        //print_object(" ---- extra exams for user= $userid, room= $roomid  ");
        $exam = reset($exams);
        if($exam->additional != 0) {
            // if first has additional != 0, there has been some reordering, then we need to update
            $main = clone $exam;
            foreach($exams as $exam) {
                $exam->additional = $exam->examid;
                $DB->update_record('examregistrar_session_seats', $exam);
            }
            if($timelimit) {
                $main->timecreated = $timelimit;
            }
            $main->additional = 0;
            $DB->update_record('examregistrar_session_seats', $main);
            //$DB->set_field('examregistrar_session_seats', 'additional', 0, array('id'=>$exam->id));
        }
    }
}


/**
 * Updates assignation of main/additional exam for user in a room
 *
 * @param int $session exam session id number (as used in bookings table)
 * @param int $bookedsite the locationID for the booking
 * @param int $userid the user whose allocation is updated
 * @param int $roomid the room to check
 * @param int $timelimit used only when checking new bookings
 * @return string
 */
function examregistrar_verify_voucher($cmid, $vouchernum, $crccode, $canmanage) {
    global $DB, $OUTPUT, $USER;
    
    $output = '';
    list($rid, $uniqueid) = explode('-', $vouchernum);
    if(!$voucher = $DB->get_record('examregistrar_vouchers', array('examregid' => $rid, 'uniqueid' => $uniqueid))) {
        return $OUTPUT->box($OUTPUT->error_text(get_string('error_novoucher', 'examregistrar')), 'alert alert-danger');
    }
    if(!$booking = $DB->get_record('examregistrar_bookings', array('id' => $voucher->bookingid))) {
        return $OUTPUT->box($OUTPUT->error_text(get_string('error_nobooking', 'examregistrar')), 'alert alert-danger');
    }
    // Privacy, do not show booking data to non allowed users
    if(($USER->id != $booking->userid) && !$canmanage) {
        return $OUTPUT->box($OUTPUT->error_text(get_string('error_voucheruser', 'examregistrar')), 'alert alert-danger');
    }
    $newcrccode = crc32("{$voucher->id}/{$booking->id}");
    if($newcrccode != $crccode) {
        return $OUTPUT->box($OUTPUT->error_text(get_string('error_crccode', 'examregistrar')), 'alert alert-danger');
    }
    
    // by now we have an existing & valid booking & voucher
    // let's check booking?
    $user = $DB->get_record('user', array('id'=>$booking->userid), 'id, idnumber, firstname, lastname', MUST_EXIST);
    list($examname, $notused) = examregistrar_get_namecodefromid($booking->examid, 'exams');
    $attend = new stdClass();
    $attend->take = core_text::strtoupper($booking->booked ?  get_string('yes') :  get_string('no'));
    list($attend->site, $notused) = examregistrar_get_namecodefromid($booking->bookedsite, 'locations', 'location');
    $userbooking = $examname.
                    html_writer::div(get_string('voucheruser', 'examregistrar', $user), 'userbooking').
                    html_writer::div(get_string('takeonsite', 'examregistrar', $attend), 'booked');
    $booked = $booking->booked;
    
    // let's check time, is there a more recent voucher?
    $sql = "SELECT v.*, b.userid, b.examid, b.bookedsite, b.booked
            FROM {examregistrar_vouchers} v
            JOIN {examregistrar_bookings} b ON b.id = v.bookingid
            WHERE b.userid = :userid AND b.examid = :examid AND b.timemodified > :time 
            ORDER BY v.timemodified DESC";
    $params = array('userid'=>$booking->userid, 'examid'=>$booking->examid, 'time'=>$booking->timemodified);
    if($newer = $DB->get_records_sql($sql, $params)) {
        $a = new stdClass();
        $a->count = count($newer);
        $newer = reset($newer);

            $icon = new pix_icon('t/download', get_string('voucherdownld', 'examregistrar'), 'core', null); 
            $num = str_pad($newer->examregid, 4, '0', STR_PAD_LEFT).'-'.$newer->uniqueid;
            $downloadurl = new moodle_url('/mod/examregistrar/download.php', array('id' => $cmid, 'down'=>'voucher', 'v'=>$num));
            $num = $OUTPUT->action_link($downloadurl, $num, null, array('class'=>'voucherdownload'), $icon);
            $a->last = get_string('vouchernum', 'examregistrar',  $num);

        $output .= $OUTPUT->box($OUTPUT->error_text(get_string('error_latervoucher', 'examregistrar', $a)), 'alert alert-warning');
        $attend->take = core_text::strtoupper($newer->booked ?  get_string('yes') :  get_string('no'));
        list($attend->site, $notused) = examregistrar_get_namecodefromid($newer->bookedsite, 'locations', 'location');
        $userbooking =  $examname.
                        html_writer::div(get_string('voucheruser', 'examregistrar', $user), 'userbooking').
                        html_writer::div(get_string('takeonsite', 'examregistrar', $attend), 'booked');
        $booked = $newer->booked;
    }
    
    $alert = $booked ? 'success' : 'danger';
    
    $output .= $OUTPUT->box($userbooking, "alert alert-$alert");
    
    return $output;
    
}


//////////////////////////////////////////////////////////////////////////////////
//   Staffers functions                                                         //
////////////////////////////////////////////////////////////////////////////////

/**
 * Look for course teachers
 *
 * @param int $courseid Exam courseid
 * @return array userid, fullnames
 */
function examregistrar_get_teachers($courseid) {
    $teachers = array();
    $coursecontext = context_course::instance($courseid);
    $fields = get_all_user_name_fields(true, 'u');
    if($users = get_enrolled_users($coursecontext, 'moodle/course:manageactivities', 0, 'u.id, u.idnumber, u.picture, '.$fields, ' u.lastname ASC ')){
        foreach($users as $user) {
            $teachers[$user->id] = fullname($user) ;
        }
    }
    return $teachers;
}


/**
 * Look for course teachers and assign then as room staffers in rooms with allocated exam in a session
 *
 * @param array $examsessions a collection of examsessions where to look for allocated exams
 * @param int $bookedsite the venue wheer allocations take place
 * @param string $role the role to assign
 * @return void
 */
function examregistrar_assignroomstaff_fromexam($examsessions, $bookedsite, $role, $remove=false) {
    global $DB, $USER;

    list($insql, $params) = $DB->get_in_or_equal($examsessions, SQL_PARAMS_NAMED, 'sess');
    $sql = "SELECT ss.id, ss.examsession, ss.examid, ss.roomid, e.courseid, c.shortname
            FROM {examregistrar_session_seats} ss
            JOIN {examregistrar_exams} e ON ss.examid = e.id AND ss.examsession = e.examsession AND e.callnum > 0
            JOIN {course} c ON e.courseid = c.id
            WHERE ss.bookedsite = :bookedsite  AND ss.additional = 0 AND ss.examsession $insql
            GROUP BY ss.examsession, ss.examid, ss.roomid ";

    $params['bookedsite'] = $bookedsite;

    $visible = $remove ? 0 : 1;

    if(!$role && !$remove) {
        $role = 'RS'; /// TODO   TODO TODO
    }

    $errors = array();
    if($allocations = $DB->get_records_sql($sql, $params)) {
        foreach($allocations as $allocation) {
            $coursecontext = context_course::instance($allocation->courseid);
            if(!$coursecontext) {
                $errors[$allocation->shortname] = $allocation->shortname.' - No context';
            }
            if($users = get_enrolled_users($coursecontext, 'moodle/course:manageactivities', 0, 'u.id, u.idnumber', ' u.lastname ASC ')){
                foreach($users as $user) {
                    if(!$role && $remove) {
                        examregistrar_remove_roomstaffers($allocation->examsession,$allocation->roomid);
                    } else {
                        examregistrar_addupdate_roomstaffer($allocation->examsession,$allocation->roomid,
                                                            $user->id, $role, '', $visible);
                }
            }
        }
    }
    }

    if($errors){
        return html_writer::alist($errors);
    }

    return false;
}



/**
 * Look for staffers in this course & in all possible courses
 *
 * @param array $examsessions a collection of examsessions where to look for allocated exams
 * @param int $bookedsite the venue wheer allocations take place
 * @param string $role the role to assign
 * @return void
 */
function examregistrar_get_potential_staffers($examregistrar, $roomid, $newrole=true) {
    global $DB;

    $cm = get_coursemodule_from_instance('examregistrar', $examregistrar->id, $examregistrar->course, false, MUST_EXIST);
    $context = context_module::instance($cm->id);

    $config = examregistrar_get_instance_configdata($examregistrar);

    $fields = 'u.id, '.get_all_user_name_fields(true, 'u');
    $users = get_users_by_capability($context, 'mod/examregistrar:beroomstaff', $fields, 'lastname ASC');
    $categories = null;
    $categories =  !is_array($config->staffcats) ? explode(',', $config->staffcats) : $config->staffcats;
    if($categories) {
        foreach($categories as $category) {
            $select = ' c.category = :category AND c.visible = 1 ';
            if($config->excludecourses) {
                $select .= ' AND uc.credits > 0 ';
            }
            $sql = "SELECT c.id, c.fullname, c.shortname, c.idnumber
                    FROM {course} c 
                    LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid
                    WHERE $select ";
            
            if($courses = $DB->get_records_sql($sql, array('category'=>$category))) {
                foreach($courses as $course) {
                    $coursecontext = context_course::instance($course->id);
                    $courseusers = get_users_by_capability($coursecontext, 'mod/examregistrar:beroomstaff', $fields, 'lastname ASC');
                    $users =  $users + $courseusers;
                }
            }
        }
    }
}


/**
 * Returns an array of users that are assigned as staff in a room
 *
 * @param int $roomid the ID for room in Locations table
 * @param int $sessionid the ID for the exam session
 * @param string $format it true, only userids are returned
 * @return array userids or objects
 */
function examregistrar_get_room_staffers($roomid, $sessionid='', $role='', $visible=1, $ids=false) {
    global $DB;

   // print_object("room: $roomid  session: $sessionid ");

    $params = array($roomid);
    $emptysession = $DB->sql_isempty('examregistrar_staffers', 'examsession', true, false);
    $sessionwhere = '';
    if($sessionid) {
        $sessionwhere = ' examsession = ? OR ';
        $params[] = $sessionid;
    }
    $select = " locationid = ? AND ( $sessionwhere $emptysession )  ";
    if($visible >= 0) {
        $visible = ($visible > 0) ? '1' : '0';
        $select .= " AND s.visible = $visible ";
    }

    if($role) {
        $select .= ' AND role = ? ';
        $params[] = $role;
    }

    if($ids) {
        $staffers = $DB->get_fieldset_select('examregistrar_staffers', 'userid', $select, $params);
    } else {
        $fields = get_all_user_name_fields(true, 'u');
        $sql = "SELECT s.*, es.name AS rolename, es.idnumber AS roleidnumber, $fields, u.username, u.idnumber, u.picture, u.email, u.phone1, u.phone2, u.city
                FROM {examregistrar_staffers} s
                JOIN {examregistrar_elements} es ON s.role = es.id
                JOIN {user} u ON s.userid = u.id
                WHERE $select
                ORDER BY u.lastname ASC, u.firstname ASC, s.role ASC ";

        $staffers = $DB->get_records_sql($sql, $params);
    }

    //print_object($staffers);
    //print_object(" -----   staffers  ------          ");

    return $staffers;
}


/**
 * Returns an HTML list of users that are assigned as staff in a room
 *
 * @param int $roomid the ID for room in Locations table
 * @param int $sessionid the ID for the exam session
 * @param string $format it true, only userids are returned
 * @return array userids or objects
 */
function examregistrar_get_room_staffers_list($roomid, $sessionid='', $role='', $visible=1, $ids=false) {

    if($staffers =examregistrar_get_room_staffers($roomid, $sessionid, $role, $visible, $ids)) {
        $users = array();
        foreach($staffers as $staff) {
            $name = fullname($staff);
            $role = ' ('.$staff->roleidnumber.')';
            $users[] = $name.$role;
        }
        return html_writer::alist($users);
    }
    return '';
}

/**
 * Returns a list of
 *
 * @param array $staffers array of staff tableobjects
 * @param string $format ir true, only userids are returned
 * @return array element id, element name
 */
function examregistrar_format_room_staffers($staffers, $baseurl, $exregid, $downloading=false,  $return= true) {
    global $OUTPUT, $DB;

    if(!$staffers) {
        return;
    }

    /// TODO separate by roles

    $baseurl->param('edit', 'staffers');

    $roleusers = array();

    $stredit   = get_string('edit');
    $strdelete = get_string('delete');
    foreach($staffers as $staff) {
        $name = fullname($staff);
        $role = ' ('.$staff->roleidnumber.')';
        $data = $name.$role;
        $visible = -$staff->id;
        $visicon = 'show';
        $strvisible = get_string('hide');
        if(!$staff->visible) {
            $data = html_writer::span($name.$role, 'dimmed_text');
            $visible = $staff->id;
            $visicon = 'hide';
            $strvisible = get_string('show');
        }
        if(!$downloading) {
            $buttons = array();
            $url = new moodle_url($baseurl, array('show'=>$visible));
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/'.$visicon, $strvisible, 'moodle', array('class'=>'iconsmall', 'title'=>$strvisible)));
            $url = new moodle_url($baseurl, array('del'=>$staff->id));
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete, 'moodle', array('class'=>'iconsmall', 'title'=>$strdelete)));
            $action = implode('&nbsp;', $buttons);
            $data .= '&nbsp;'.$action;
        }
        $roleusers[$staff->role][] = $data;
    }

    $output = '';
    if($roleusers) {
        foreach($roleusers as $role => $users) {
            $rolename = $DB->get_field('examregistrar_elements', 'name', array('examregid'=>$exregid, 'type'=>'roleitem', 'idnumber'=>$role));
            $output = $rolename;
            $output .= html_writer::alist($users);
        }
    }

    
    $baseurl->param('edit', 'locations');
    if($return) {
        return $output;
    }
    echo $output;
}


/**
 * Returns a collection of allocated exams organized by parent/room/exam/extras
 *
 * @param array $filters associative array of search fields
 *                array('session'=>$session, 'bookedsite'=>$bookedsite,
 *                      'room'=>$room, 'programme'=>$programme);
 * @param array $courseids optional array of course IDs to limit result to (those courses or less)
 * @return array of rooms classes
 */
function examregistrar_get_examallocations_byexam(array $filters, $courseids = array()) {
    global $DB;

    if(!$filters['session']) {
        return array();
    }

    $params = array();
    $where = '';
    if(isset($filters['session']) && $filters['session']) {
        $where .= ' AND e.examsession = :session ';
        $params['session'] = $filters['session'];
    }
    if(isset($filters['bookedsite']) && $filters['bookedsite']) {
        $where .= ' AND b.bookedsite = :bookedsite ';
        $params['bookedsite'] = $filters['bookedsite'];
    }

    if(isset($filters['programme']) && $filters['programme']) {
        $where .= ' AND e.programme = :programme ';
        $params['programme'] = $filters['programme'];
    }
    if(isset($filters['room']) && $filters['room']) {
        $where .= ' AND ss.roomid = :room ';
        $params['room'] = $filters['room'];
    }
    if(isset($filters['course']) && $filters['course']) {
        $where .= ' AND e.courseid = :course ';
        $params['course'] = $filters['course'];
    }
    if(isset($filters['exam']) && $filters['exam']) {
        $where .= ' AND e.id = :exam ';
        $params['exam'] = $filters['exam'];
    }
/*
    $sql = "SELECT e.*, c.shortname, c.fullname, c.idnumber
                FROM {examregistrar_exams} e
                JOIN {course} c ON c.id = e.courseid
                LEFT JOIN {examregistrar_session_seats} ss ON e.id = ss.examid AND  ss.examsession = e.examsession
                WHERE 1 $where
                GROUP BY e.id
                ORDER BY c.shortname ASC, c.fullname ASC ";

*/
                
    $sql = "SELECT e.*, c.shortname, c.fullname, c.idnumber
                FROM {examregistrar_exams} e
                JOIN {course} c ON c.id = e.courseid
                LEFT JOIN {examregistrar_bookings} b ON e.id = b.examid AND b.booked = 1
                LEFT JOIN {examregistrar_session_seats} ss ON e.id = ss.examid AND  ss.examsession = e.examsession AND b.bookedsite = ss.bookedsite
                WHERE e.visible = 1 $where
                GROUP BY e.id
                ORDER BY c.shortname ASC, c.fullname ASC ";
                
    $examallocations = array();

    if($allocations = $DB->get_records_sql($sql, $params)) {
    
        foreach($allocations as $allocation) {
            if($courseids && !in_array($allocation->courseid, $courseids)) {
                continue;
            }
            if(!isset($examallocations[$allocation->id])) {
                // this should create room and parent data
                $exam = new examregistrar_allocatedexam($filters['session'], $filters['bookedsite'], $allocation, 'id');
            } else {
                $exam = $examallocations[$allocation->id];
            }
            $examallocations[$allocation->id] = $exam;
        }
    }

    return $examallocations;
}




/**
 * Returns a collection of allocated rooms by parent/room/exam/extras for a given exam
 *
 * @param array $params associative array of search fields
 *                array('period'=>$period, 'session'=>$session, 'bookedsite'=>$bookedsite,
 *                      'room'=>$room, 'programme'=>$programme, 'shortname'=>$shortname);
 * @return array of rooms classes
 */
function examregistrar_get_roomallocations_byexam(array $params) {
    global $DB;

    $result = array();




    return $result;
}


/**
 * Returns a collection of allocated rooms organized by parent/room/exam/extras
 *
 * @param array $filters associative array of search fields
 *                array('session'=>$session, 'bookedsite'=>$bookedsite,
 *                      'room'=>$room, 'programme'=>$programme);
 * @param array $roomids optional array of room IDs to limit result to (those rooms or less)
 * @return array of rooms classes
 */
function examregistrar_get_roomallocations_byroom(array $filters, $roomids = array()) {
    global $DB;

    $params = array();

    if(!$filters['session'] ) {
        return array();
    }

    $where = '';
    if(isset($filters['session']) && $filters['session']) {
        $where .= ' AND ss.examsession = :session ';
        $params['session'] = $filters['session'];
    }
    if(isset($filters['bookedsite']) && $filters['bookedsite']) {
        $where .= ' AND ss.bookedsite = :bookedsite ';
        $params['bookedsite'] = $filters['bookedsite'];
    }

    if(isset($filters['programme']) && $filters['programme']) {
        $where .= ' AND e.programme = :programme ';
        $params['programme'] = $filters['programme'];
    }
    if(isset($filters['room']) && $filters['room']) {
        $where .= ' AND ss.roomid = :room ';
        $params['room'] = $filters['room'];
    }
    if(isset($filters['course']) && $filters['course']) {
        $where .= ' AND e.courseid = :course ';
        $params['course'] = $filters['course'];
    }


//     //print_object($filters);
//     //print_object($where);
//     //print_object("  ---- firltrsXXXX ---------------");

    $sort = '';
    if(isset($filters['sort']) && $filters['sort']) {
        $sort = ' '.$filters['sort'].' '; // seats/booked/free
        if($filters['sort'] == 'freeseats' || $filters['sort'] == 'seats') {
            $sort .= ' DESC';
        }
        $sort .= ', ';
    }

    $roomallocations = array();

    $sql = "SELECT ss.id, r.parent, el.name AS parentname, el.idnumber AS parentidnumber,
                    ss.bookedsite, ss.roomid, er.name, er.idnumber, r.path, r.depth, r.seats, (r.seats - COUNT(ss.id)) AS freeseats,
                    ss.examid, e.programme, e.courseid, e.examsession, e.annuality, e.examscope, e.callnum, c.shortname, c.fullname,  COUNT(userid) AS seated, COUNT(ss.id) AS booked
            FROM {examregistrar_session_seats} ss
            JOIN {examregistrar_locations} r ON ss.roomid = r.id
            JOIN {examregistrar_elements} er ON r.examregid = er.examregid AND er.type ='locationitem' AND r.location = er.id
            JOIN {examregistrar_exams} e ON ss.examid = e.id AND e.visible = 1
            JOIN {course} c ON e.courseid = c.id
            LEFT JOIN {examregistrar_locations} l ON r.parent = l.id
            LEFT JOIN {examregistrar_elements} el ON l.examregid = el.examregid AND el.type ='locationitem' AND l.location = el.id

            WHERE ss.additional = 0 $where
            GROUP BY ss.roomid, ss.examid
            ORDER BY $sort name ASC, c.shortname ASC ";

    if($allocations = $DB->get_records_sql($sql, $params)) {
        foreach($allocations as $allocation) {
            if($roomids && !in_array($allocation->roomid, $roomids)) {
                continue;
            }
            if(!isset($roomallocations[$allocation->roomid])) {
                // this should create room and parent data
                //$room = new examregistrar_allocatedroom($filters['session'], $filters['bookedsite'], $allocation, 'roomid');
                $room = new examregistrar_allocatedroom($filters['session'], $allocation->bookedsite, $allocation, 'roomid');

            } else {
                $room = $roomallocations[$allocation->roomid];
            }
            // add a new exm row and updates occupancy
            $room->add_exam_fromrow($allocation, 'examid');
            $room->refresh_seated();
            $roomallocations[$allocation->roomid] = $room;
        }
    }

    return $roomallocations;
}


//////////////////////////////////////////////////////////////////////////////////
// elements & tables CSV upload & management                                   //
////////////////////////////////////////////////////////////////////////////////


/**
 * Returns a menu of table records items, (id, name) for selected table
 *
 * @param string $element element name / component table
 * @param object $formdata data submitted in an item-editing form
 * @return object properly built element
 */
function examregistrar_extract_edititem_formdata($element, $formdata) {
    global $USER;

//     //print_object($formdata);
//     //print_object("----  formdata ---------------");

    $data = new stdClass;
    $data = clone $formdata;

    if(isset($data->id)) {
        unset($data->id);
    }
    if(isset($data->edit)) {
        unset($data->edit);
    }
    if(isset($data->item)) {
        unset($data->item);
    }

    $data->display = '';
    if(isset($data->name)) {
        $data->display = $data->name;
    } elseif(isset($data->shortname)) {
        $data->display = $data->shortname;
    } elseif(isset($data->$element)) {
        $data->display = $DB->get_field('examregistrar_elements', 'name', array('id'=>$data->$element));
    }

    if($element == 'element') {
        $data->value = 0;
        if(isset($formdata->value)) {
            $data->value = $formdata->value;
        }
    }

    if($element == 'exam') {
        if($formdata->additional == 1) {
            $data->callnum = -($formdata->callnum);
        }
    }

    if($fields = get_object_vars($formdata)) {
        foreach($fields as $field => $value) {
            if(is_array($value)) {
                $data->$field = $value['text'];
                $data->{$field.'format'} = $value['format'];
            }
        }
    }

    if(!isset($data->visible)) {
        $data->visible = 1;
    }
    if(!isset($data->component) || !$data->component) {
        $data->modifierid = $USER->id;
    }
    $data->timemodified = time();

    return $data;
}





/**
 * Validates data imported fron CSV file. Checks table colums & existing tdata
 *
 * @param object $examregistrar object
 * @param string $table table against to check records
 * @param array $record a processed data line from the CSV file, associative array with column names as keys
 * @param bool $ignoremodified
 * @return array validdata, update
 */
function examregistrar_validate_csvuploaded_data($examregistrar, $table, $record, $ignoremodified) {
    global $DB, $USER;

    $update = false;
    $data = new stdClass;
    $examregid = examregistrar_get_primaryid($examregistrar);

    $tablecolumns = $DB->get_columns('examregistrar_'.$table);
    $validcolumns = array_diff($tablecolumns, array('id', 'examregid', 'component', 'modifierid', 'timemodified'));

    if(!$validcolumns) {
        return array(false, false);
    }

    foreach($validcolumns as $col) {
        if(isset($record[$col])) {
            $item = $record[$col];

            if(!$DB->record_exists('examregistrar_elements', array('examregid'=>$examregid, 'type'=>'', 'idnumber'=>$item ))) {
            }
        }
    }

    $data->examregid = $examregid;
    $data->component = '';
    $data->modifierid = $USER->id;
    $data->timemodified = time();

    return array($data, $update);
}




/*


/**
 * Returns dataobject from CSV read & fields for unique searching
 *
 * @param object $examregistrar object
 * @param string $action upload operation on CSV
 * @param object $formdata data submitted in a CSV row in a file
 * @param bool $useidnumber where moodle IDs or idnumber will be used
 * @return array
 */
 /*
function examregistrar_uploadcsv_extractdata($examregistrar, $action, $formdata, $useidnumber = true) {
    global $USER;
    $data = new stdClass;

    $data = clone $formdata;
    if(isset($data->id)) {
        unset($data->id);
    }
    $params = array();
/*
* Load CSV Elements:
    type*, name*, idnumber*, value, sortorder

* Load CSV Periods:
    name*, idnumber*, annuality*, degreetype*, periodtype*, calls*, timestart*, timeend*, visible

* Load CSV Sessions:
    name*,    idnumber*,    period*,  examdate*,   timeslot*,   visible

* Load CSV Locations:
    name*, idnumber*, locationtype*, seats*, address, addressformat, parent, sortorder, visible

* Load CSV Staffers:
    locationid*   userid*    roletype*   info

* Load CSV session rooms:
    examsession*, locationid*, available*
*/
/*
    if($useidnumber) {
        $fields = array();
        $search = new stdClass;
        $search->params = array();
        switch($action) {
            case 'elements' :
                            $params = array('examregid','type','idnumber');
                            break;
            case 'periods' :
                            $params = array('examregid','idnumber');
                            $search->table = 'elements';
                            $search->params = array('type'=>'degreetype');
                            $fields['degreetype'] = $search;
                            $search->params = array('type'=>'periodtype');
                            $fields['periodtype'] = $search;
                            break;
            case 'sessions' :
                            $params = array('examregid','idnumber');
                            $search->table = 'periods';
                            $fields['period'] = $search;
                            break;
            case 'locations' :
                            $params = array('examregid','idnumber');
                            $search->table = 'elements';
                            $search->params = array('type'=>'locationtype');
                            $fields['locationtype'] = $search;
                            $search->table = 'locations';
                            $search->params = array();
                            $fields['parent'] = $search;
                            break;
            case 'staffers' :
                            $params = array('examsession', 'locationid', 'userid','roletype');
                            $search->table = 'sessions';
                            $fields['examsession'] = $search;
                            $search->table = 'locations';
                            $fields['locationid'] = $search;
                            $search->table = 'user';
                            $fields['userid'] = $search;
                            $search->table = 'elements';
                            $search->params = array('type'=>'roletype');
                            $fields['roletype'] = $search;
                            break;
            case 'session_rooms' :
                            $params = array('examsession', 'locationid');
                            $search->table = 'sessions';
                            $fields['examsession'] = $search;
                            $search->table = 'locations';
                            $fields['locationid'] = $search;
                            break;
        }
        if($fields) {
            foreach($fields as $field => $search) {
                $table = $search->table;
                $params = $search->params;
                $params['examregistrar'] = $examregistrar->examregidused;
                $params['idnumber'] = $data->$field;
                $value = $DB->get_field('examregistrar_'.$table, 'id', $params);
                $data->$field = $value;
            }
        }
    }

    $data->examregistrar = $examregistrar->examregidused;
    $data->assignedby = $USER->id;
    $data->timemodified = time();
    $searchparams = array();
    foreach($params as $param) {
        $searchparams[$param] = $data->$param;
    }

    return array($data, $searchparams);
}
*/

/**
 * Validates csv uploaded data and verifies if is new or existing record
 *
 * @param array $data csv imported record
 * @param string $table the database table to verify on
 * @param array $uniquefields the fields used to verify the record exists in the DB (this combination is unique)
 * @param array $requiredfields field that are mandatory in $data record for succesfully storing data in DB
 * @param array $additionalfields other optional fileds in DB
 * @param bool $ignoremodified whether update existing data or not
 * @return array validdata, update
 */
function examregistrar_loadcsv_updaterecordfromrow($data, $table, $uniquefields, $requiredfields, $additionalfields, $ignoremodified) {
    global $DB, $USER;

    $record = new stdClass;
    foreach($uniquefields as $field) {
        $record->$field = $data[$field];
    }
    $params = get_object_vars($record);
    // do not add any other param before, or conflict with table names
    if(isset($record->idnumber)) {
        $record->idnumber = clean_param($record->idnumber, PARAM_ALPHANUMEXT);
    }

    $update = false;
    if($oldrecord = $DB->get_record('examregistrar_'.$table, $params)) {
        //we are updating
        if($ignoremodified) {
            // updating, if we are ignoring proposed changes, we won't update
            return array();
        }
        $update = true;
        $record = clone $oldrecord;
    }
    //we are inserting or we are allowed to update
    // add the remaining fields
    $fields = array_diff(array_merge($requiredfields, $additionalfields), $uniquefields);
    foreach($fields as $field) {
        if(isset($data[$field])) {
            $record->$field = $data[$field];
        }
    }

    $record->component = '';
    $record->modifierid = $USER->id;
    $record->timemodified = time();

    return array($record, $update);
}



/**
 * Checks if an object field contains a valid element idnumber
 *
 * @param stdClass $record csv imported record
 * @param string $field record field
 * @param string $elementtype  the element type this field should be related to
 * @param bool $editelements permission to whether update element data or not,
 * @param bool $ignoremodified whether update existing data or not
 * @param bool $neverupdate permission to disallow insert/update if not appropiate
 * @return array validdata, update
 */
function examregistrar_loadcsv_elementscheck($record, $field, $elementtype, $ignoremodified, $editelements, $neverupdate=false) {
    global $DB;

    $eventdata = array();
    //$eventdata['objecttable'] = 'examregistrar_elements';
    list($course, $cm) = get_course_and_cm_from_instance($record->examregid, 'examregistrar');
    $context = context_module::instance($cm->id);
    $eventdata['context'] = $context;
    $eventdata['other'] = array('edit'=>'elements');
    
    $elementid = 0;
    /// now integrity checks
    if(!$element = $DB->get_record('examregistrar_elements', array('examregid'=>$record->examregid, 'idnumber'=>$record->$field, 'type'=>$elementtype))) {
        if($editelements && !$neverupdate) {
            $element = clone $record;
            $element->type = $elementtype;
            if(isset($element->id)) {
                unset($element->id);
            }
            if($elementid = $DB->insert_record('examregistrar_elements', $element)) {
                //$eventdata['objectid'] = $element->id;
                $event = \mod_examregistrar\event\manage_created::created($eventdata);
                $event->trigger();
            }
        } else {
            return false;
        }
    } else {
        if(!$ignoremodified && $editelements && !$neverupdate) {
            //$DB->set_field('examregistrar_elements', 'name', $record->name; array('id'=>$element->id));
            $eid = $element->id;
            $element = clone $record;
            $element->type = $elementtype;
            $element->id = $eid;
            if($DB->update_record('examregistrar_elements', $element)) {
                //$eventdata['objectid'] = $element->id;
                //$eventdata['objecttable'] = 'examregistrar_elements';
                $event = \mod_examregistrar\event\manage_updated::created($eventdata);
                $event->trigger();
            }
        } else {
            //do nothing, Do not abort, allow loading csv row without updating elements
        }
        $elementid = $element->id;
    }
           
    
    
    return $elementid;
}



/**
 * Validates uploaded main elements data and stores in DB
 *
 * @param object $examregistrar object
 * @param array $data record the uploaded row
 * @param bool $ignoremodified whether update existing data or not
 * @return void or error message
 */
function examregistrar_loadcsv_elements($examregistrar, $data, $ignoremodified) {
    global $DB, $USER;

    $uniquefields = array('examregid', 'idnumber', 'type');
    $requiredfields = array('name', 'idnumber', 'type');
    $additionalfields = array('value', 'visible');

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);
    $data['examregid'] = $examregprimaryid;

    list($record, $update) = examregistrar_loadcsv_updaterecordfromrow($data, 'elements', $uniquefields, $requiredfields, $additionalfields, $ignoremodified);
    if(!$record) {
        return '  ignore updating: '.$data['name'];
    }

    return examregistrar_saveupdate_csvloaded_item($record, 'elements', $update);
}

/**
 * Validates uploaded room/venue data and stores in DB
 *
 * @param object $record, the item to sve or update 
 * @param string $table the data table where to put data
 * @param bool $update, save or update whether update existing data or not
 * @return mixed item string if error int > 0 insert,  < 0 update
 */
function examregistrar_saveupdate_csvloaded_item($record, $table, $update = false) {
    global $DB; 

    $item = false;
    $table = 'examregistrar_'.$table;
    if($update) {
        if($DB->update_record($table, $record)) {
            $item = -($record->id);
        }
    } else {
        $item = $DB->insert_record($table, $record);
    }

    return $item;
}


/**
 * Validates uploaded room/venue data and stores in DB
 *
 * @param object $examregistrar object
 * @param array $data record the uploaded row
 * @param bool $ignoremodified whether update existing data or not
 * @param bool $editelements whether update/insert new data on elements table
 * @return void or error message
 */
function examregistrar_loadcsv_locations($examregistrar, $data, $ignoremodified, $editelements=false) {
    global $DB, $USER;

    $message = '';
    $uniquefields = array('examregid', 'location', 'locationtype');
    $requiredfields = array('name', 'idnumber', 'locationtype', 'parent', 'parenttype');
    $additionalfields = array('seats', 'address', 'sortorder', 'visible');

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);
    $data['examregid'] = $examregprimaryid;

    $record = new stdclass;
    $record->examregid = $examregprimaryid;
    foreach($requiredfields as $field) {
        $record->$field = isset($data[$field]) ? $data[$field] : '';
    }


    /// now integrity checks
    if(!$data['location'] = examregistrar_loadcsv_elementscheck($record, 'idnumber', 'locationitem', $ignoremodified, $editelements)) {
        return ' not allowed to insert location: '.$data['name'];
    }
    if(!$data['locationtype'] = examregistrar_loadcsv_elementscheck($record, 'locationtype', 'locationtypeitem',  true, false, true)) {
        return ' not allowed to insert locationtype: '.$data['name'];
    }

    $parent = 0;
    if($record->parent) {
        $parent = examregistrar_loadcsv_elementscheck($record, 'parent', 'locationitem',  true, false, true);
        $parenttype = examregistrar_loadcsv_elementscheck($record, 'parenttype', 'locationtypeitem',  true, false, true);
        if(!$parent = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'location'=>$parent, 'locationtype'=>$parenttype))) {
            $parent = 0;
        }
    }
    $data['parent'] = $parent;

    /// now construct the true table record
    //$requiredfields = array('seats');
    list($record, $update) = examregistrar_loadcsv_updaterecordfromrow($data, 'locations', $uniquefields, $requiredfields, $additionalfields, $ignoremodified);
    if(!$record) {
        return '  ignore updating: '.$data['name'];
    }
    
    if(isset($record->address) && $record->address) {
        $record->addressformat = 1;
        $record->address = format_text($record->address, $record->addressformat, array('filter'=>false, 'para'=>false));
    }

    if(isset($record->visible)) {
        $record->visible = (int)$record->visible;
    }

    if($record->id = examregistrar_saveupdate_csvloaded_item($record, 'locations', $update)) {
        examregistrar_set_location_tree($record->id);
    }

    return $record->id;
}


/**
 * Validates uploaded session data and and stores in DB
 *
 * @param object $examregistrar object
 * @param array $data record the uploaded row
 * @param bool $ignoremodified wether update existing data or not
 * @param bool $editelements whether update/insert new data on elements table
 * @return void or error message
 */
function examregistrar_loadcsv_sessions($examregistrar, $data, $ignoremodified, $editelements=false) {
    global $DB, $USER;

    $message = '';
    $uniquefields = array('examregid', 'examsession', 'period');
    $requiredfields = array('name', 'idnumber', 'period', 'annuality' );
    $additionalfields = array('examdate', 'timeslot', 'visible');

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);
    $data['examregid'] = $examregprimaryid;

    $record = new stdclass;
    $record->examregid = $examregprimaryid;
    foreach($requiredfields as $field) {
        $record->$field = $data[$field];
    }

    /// now integrity checks
    if(!$data['examsession'] = examregistrar_loadcsv_elementscheck($record, 'idnumber', 'examsessionitem', $ignoremodified, $editelements)) {
        return ' not allowed to insert examsessionitem: '.$data['name'];
    }
    if(!$period = examregistrar_loadcsv_elementscheck($record, 'period', 'perioditem', true, false, true)) {
        return ' not allowed to insert perioditem: '.$data['name'];
    }
    if(!$annuality = examregistrar_loadcsv_elementscheck($record, 'annuality', 'annualityitem', true, false, true)) {
        return ' not allowed to insert annualityitem: '.$data['name'];
    }

    if(!$period = $DB->get_field('examregistrar_periods', 'id', array('examregid'=>$examregprimaryid, 'period'=>$period, 'annuality'=>$annuality))) {
        return ' invalid period  '.$data['period'].'  at annuality '.$data['annuality'];
    }
    $data['period'] = $period;

    /// now construct the true table record
    $requiredfields = array('period');
    list($record, $update) = examregistrar_loadcsv_updaterecordfromrow($data, 'examsessions', $uniquefields, $requiredfields, $additionalfields, $ignoremodified);
    if(!$record) {
        return '  ignore updating: '.$data['name'];
    }

    /// now specific items
    $tz = usertimezone();
    $record->examdate = strtotime($record->examdate.' '.$tz);
    if(isset($record->visible)) {
        $record->visible = (int)$record->visible;
    }
    if(!isset($record->duration)) {
        $record->duration = 2*60*60;
    }

    return examregistrar_saveupdate_csvloaded_item($record, 'examsessions', $update);
}


/**
 * Validates uploaded period data and and stores in DB
 *
 * @param object $examregistrar object
 * @param array $data record the uploaded row
 * @param bool $ignoremodified wether update existing data or not
 * @param bool $editelements whether update/insert new data on elements table
 * @return void or error message
 */
function examregistrar_loadcsv_periods($examregistrar, $data, $ignoremodified, $editelements=false) {
    global $DB, $USER;

    $message = '';
    $uniquefields = array('examregid', 'period', 'annuality');
    $requiredfields = array('name', 'idnumber', 'annuality', 'periodtype', 'term' );
    $additionalfields = array('calls', 'timestart', 'timeend', 'visible');

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);
    $data['examregid'] = $examregprimaryid;

    $record = new stdclass;
    $record->examregid = $examregprimaryid;
    foreach($requiredfields as $field) {
        $record->$field = $data[$field];
    }

    /// now integrity checks
    if(!$data['period'] = examregistrar_loadcsv_elementscheck($record, 'idnumber', 'perioditem', $ignoremodified, $editelements)) {
        return ' not allowed to insert perioditem: '.$data['name'];
    }

    if(!$data['annuality'] = examregistrar_loadcsv_elementscheck($record, 'annuality', 'annualityitem', true, false, true)) {
        return ' not allowed to insert annualityitem: '.$data['name'];
    }
    if(!$data['periodtype'] = examregistrar_loadcsv_elementscheck($record, 'periodtype', 'periodtypeitem', true, false, true)) {
        return ' not allowed to insert periodtypeitem: '.$data['name'];
    }
    if(!$data['term'] = examregistrar_loadcsv_elementscheck($record, 'term', 'termitem', true, false, true)) {
        return ' not allowed to insert termitem: '.$data['name'];
    }

    /// now construct the true table record
    $requiredfields = array('annuality', 'periodtype', 'term' );
    list($record, $update) = examregistrar_loadcsv_updaterecordfromrow($data, 'periods', $uniquefields, $requiredfields, $additionalfields, $ignoremodified);
    if(!$record) {
        return '  ignore updating: '.$data['name'];
    }

    /// now specific items
    $tz = usertimezone();
    $record->timestart = strtotime($record->timestart.' '.$tz);
    $record->timeend = strtotime($record->timeend.' '.$tz);
    if(isset($record->visible)) {
        $record->visible = (int)$record->visible;
    }

    return examregistrar_saveupdate_csvloaded_item($record, 'periods', $update);
}


/**
 * Validates uploaded period data and and stores in DB
 *
 * @param object $examregistrar object
 * @param int $examsession the ID of the exam sesion this allocation data applies to
 * @param array $data record the uploaded row
 * @param bool $ignoremodified wether update existing data or not
 * @param bool $editelements whether update/insert new data on elements table
 * @return void or error message
 */
function examregistrar_loadcsv_staffers($examregistrar, $examsession, $data, $ignoremodified, $editelements=false) {
    global $DB, $USER;

    $message = '';
    $uniquefields = array('examsession', 'userid', 'locationid', 'role');
    $requiredfields = array('examsession', 'userid', 'locationid', 'role');
    $additionalfields = array('examregid', 'info', 'visible'); // examregid used by helper functions

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);


    $userid = 0;
    if(isset($data['idnumber'])) {
        $field = 'idnumber';
    } elseif(isset($data['username'])) {
        $field = 'username';
    }
    if(!$userid = $DB->get_field('user', 'id', array($field=>$data[$field]))) {
        return " Not found user $field: ".$data[$field];
    }

    if(!$roomid = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'idnumber'=>$data['room'], 'locationtype'=>$data['locationtype']))) {
        return ' invalid room  '.$data['room'].'  for type  '.$data['locationtype'];
    }
    $data['locationid'] = $roomid;
    $data['userid'] = $userid;
    $data['examsession'] = $examsession;
    $data['examregid'] = $examregprimaryid;

    list($record, $update) = examregistrar_loadcsv_updaterecordfromrow($data, 'staffers', $uniquefields, $requiredfields, $additionalfields, $ignoremodified);
    if(!$record) {
        return '  ignore updating: '.$data[$field];
    }

    /// now integrity checks
    if(!examregistrar_loadcsv_elementscheck($record, 'role', 'roleitem', true, false, true)) {
        return ' not allowed to insert roleitem: '.$data['role'];
    }

    /// now specific items
    if(isset($record->visible)) {
        $record->visible = (int)$record->visible;
    }

    return examregistrar_saveupdate_csvloaded_item($record, 'staffers', $update);
}



/**
 * Validates uploaded data and perform seat allocations in designed rooms for exams
 *
 * @param object $examregistrar object
 * @param int $examsession the ID of the exam sesion this allocation data applies to
 * @param array $seatassigns the uploaded movement rules
 * @param bool $ignoremodified
 * @return array validdata, update
 */
function examregistrar_loadcsv_roomallocations($examregistrar, $examsession, $seatassigns) {
    global $DB, $USER;
    //print_object($seatassigns);

    $validfields = array('city', 'num','shortname','fromoom','toroom');

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);
    $success = array();
    $fail = array();

/*
    // first a round to check that theer are no errors.
    // Better do not operate at all if theer are errors that will truncate execution in midterm
    foreach($seatassigns as $csvassign) {
        $allocation = new stdClass;
        if($csvassign['city']) {
            $allocation->bookedsite = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'idnumber'=>$csvassign['city'], MUST_EXIST));
        }
        if($csvassign['fromroom']) {
            $allocation->fromroom = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'idnumber'=>$csvassign['fromroom'], MUST_EXIST));
        }
        if($csvassign['toroom']) {
            $allocation->toroom = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'idnumber'=>$csvassign['toroom'], MUST_EXIST));
        }
        if($csvassign['shortname']) {
            $allocation->fromexam = $DB->get_field('examregistrar_exams', 'id', array('examregid'=>$examregprimaryid, 'examsession'=>$examsession,
                                                                                      'annuality'=>$examregistrar->annuality, 'shortname'=>$csvassign['shortname'], MUST_EXIST));
        }
    }
*/
    // if we are here, there are no errors in uploaded data, proceed to execution
    foreach($seatassigns as $csvassign) {
        $allocation = new stdClass;

        if($csvassign['city']) {
            $allocation->bookedsite = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'idnumber'=>$csvassign['city']));
            if(!$allocation->bookedsite || !$DB->record_exists('examregistrar_session_seats', array('examsession'=>$examsession, 'bookedsite'=>$allocation->bookedsite))) {
                $fail[] = ' invalid booked site for this exam session: '.$csvassign['city'];
                continue;
            }
        } else {
            $allocation->bookedsite = 0;
        }

        if($csvassign['num'] == '' || $csvassign['num'] == 'all' || $csvassign['num'] == 'any' || $csvassign['num'] == -1) {
            $allocation->numusers = -1;
        } else {
            $allocation->numusers = (int)$csvassign['num'];
        }

        if($csvassign['fromroom']) {
            $allocation->fromroom = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'idnumber'=>$csvassign['fromroom']));
            if(!$allocation->fromroom || !$DB->record_exists('examregistrar_session_rooms', array('examsession'=>$examsession, 'roomid'=>$allocation->fromroom, 'available'=>1))) {
                $fail[] = ' invalid room for this exam session: '.$csvassign['fromroom'];
                continue;
            }
        } else {
            $allocation->fromroom = 0;
        }

        if($csvassign['toroom']) {
            $allocation->toroom = $DB->get_field('examregistrar_locations', 'id', array('examregid'=>$examregprimaryid, 'idnumber'=>$csvassign['toroom']));
            if(!$allocation->toroom || !$DB->record_exists('examregistrar_session_rooms', array('examsession'=>$examsession, 'roomid'=>$allocation->toroom, 'available'=>1))) {
                $fail[] = ' invalid room for this exam session: '.$csvassign['toroom'];
                continue;
            }
        } else {
            $allocation->toroom = 0;
        }

        if($csvassign['shortname']) {
            $allocation->fromexam = $DB->get_field('examregistrar_exams', 'id', array('examregid'=>$examregprimaryid, 'examsession'=>$examsession,
                                                                                      'annuality'=>$examregistrar->annuality, 'shortname'=>$csvassign['shortname']));
            if(!$allocation->fromexam) {
                $fail[] = ' invalid exam for this session: '.$csvassign['shortname'];
                continue;
            }
        } else {
            $allocation->fromexam = 0;
        }

        if($allocation->bookedsite && $allocation->numusers && $allocation->fromexam && ($allocation->fromroom != $allocation->toroom)) {
            $params = array('examsession'=>$examsession, 'bookedsite'=>$allocation->bookedsite,
                            'examid'=>$allocation->fromexam, 'roomid'=>$allocation->fromroom, 'additional'=>0 );
            if($allocation->numusers < 0) {
                $allocation->numusers = 0;
            }
            $sort = ($allocation->fromroom) ? ' id DESC ' : ' id ASC';
            if(examregistrar_update_usersallocations($examsession, $allocation->bookedsite, $params, $allocation->toroom, $sort, 0, $allocation->numusers)) {
                $success[] = " Moved {$csvassign['city']} - {$csvassign['shortname']} ";
            } else {
                $fail[] = " NOT moved {$csvassign['city']} - {$csvassign['shortname']} ";
            }
        } else {
            $fail[] = " NOT moved {$csvassign['city']} - {$csvassign['shortname']} ";
        }
    }

    return implode('<br />', $success). implode('<br />', $fail);
}

////////////////////////////////////////////////////////////////////////////////
// Database & doing work functions                                            //
////////////////////////////////////////////////////////////////////////////////


/**
 * Construct an SQL fragment to use in WHERE clause to search for course criteria
 * @param object $form post data including course selection settings as courseXXX fields
 * @return array ($select, params) tuple for get_records_xx database functions
 */
function examregistrar_course_sqlselect($formdata) {
    global $DB;

    $params = array();
    $wherecourse = '';
    if($formdata->coursevisible != -1) {
        $wherecourse .= " AND c.visible = ? ";
        $params[] = $formdata->coursevisible;
    }
    if(isset($formdata->courseformat) &&  $formdata->courseformat !='all') {
        $wherecourse .= " AND c.format = ? ";
        $params[] = $formdata->courseformat;
    }
    if(isset($formdata->courseterm) &&  $formdata->courseterm != -1 ) {
        $wherecourse .= " AND uc.term = ? ";
        $params[] = $formdata->courseterm;
    }
    if(isset($formdata->coursecredit) &&  $formdata->coursecredit != -1) {
        if($formdata->coursecredit == -2) {
            $wherecourse .= " AND uc.credits > 0 ";
        } else {
            $wherecourse .= " AND uc.credits = ? ";
            $params[] = $formdata->coursecredit;
        }
    }
    if(isset($formdata->coursedept) &&  $formdata->coursedept != -1) {
        $wherecourse .= " AND uc.department = ? ";
        $params[] = $formdata->coursedept;
    }
    if(isset($formdata->coursectype) &&  $formdata->coursectype !='all') {
        $wherecourse .= " AND uc.ctype = ? ";
        $params[] = $formdata->coursectype;
    }

    if(isset($formdata->coursecategories) &&  $formdata->coursecategories) {
        list($insql, $inparams) = $DB->get_in_or_equal($formdata->coursecategories);
        $wherecourse .= " AND c.category $insql ";
        $params = array_merge($params, $inparams);

    }

    if(isset($formdata->coursetoshortnames) && trim($formdata->coursetoshortnames) != '') {
        if($names = explode(',' , addslashes($formdata->coursetoshortnames))) {
            foreach($names as $key => $name) {
                $names[$key] = trim($name);
            }
            list($insql, $inparams) = $DB->get_in_or_equal($names);
            $wherecourse .= " AND c.shortname $insql ";
            $params = array_merge($params, $inparams);
        }
    }

    if (isset($formdata->courseidnumber) && $formdata->courseidnumber) {
        $wherecourse .= " AND ".$DB->sql_like('c.idnumber', '?');
        $params[] = $formdata->courseidnumber;
    }

    return array($params, $wherecourse);
}


/**
 * determines the exam programme setting for a course given generation options
 *
 * @param object $examregistrar object
 * @param object $course course to add exam entry
 * @param object $category category object of given course
 * @param array $options generating settings
 */
function examregistrar_programme_fromcourse($examregistrar, $course, $category, $options) {

    // TODO restrict by examregistrar programe settings

    $programme = '';
    switch($options->programme) {
        case 'courseidnumber':
                            $pieces = explode('_', $course->idnumber);
                            $programme = $pieces[0];
                            break;
        case 'courseshortname':
                            $pieces = explode('-', $course->shortname);
                            $programme = $pieces[1];
                            break;
        case 'coursecatid': $programme = $course->category;
                            break;
        case 'coursecatidnumber':
                            $pieces = explode('_', $category->idnumber);
                            $programme = $pieces[1];
                            break;
        case 'coursecatdegree': $programme = $category->degree;
                            break;
    }

    return $programme;
}

/**
 * determines the periods to generate exams for a course given generation options
 *
 * @param object $examregistrar object
 * @param object $course course to add exam entry
 * @param array $periods periods selected in settings
 * @param array $options generating settings
 */
function examregistrar_examperiods_fromcourse($examregistrar, $course, $periods, $options) {
    global $DB;

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);
    $examperiods = array();
    $examinstances = array();
    if($options->generatemode == 1) {
        $assignmodid = $DB->get_field('modules', 'id', array('name'=>'assign'));
        $sql = "SELECT cm.id, cm.course, cm.instance, cm.section, cm.idnumber, a.allowsubmissionsfromdate
                FROM {course_modules} cm
                JOIN {assign} a ON cm.instance = a.id AND cm.course = a.course
                JOIN {assign_plugin_config} e ON cm.instance = e.assignment AND e.plugin = 'exam'
                                                 AND e.subtype = 'assignsubmission' AND e.name = 'enabled' AND e.value = 1
                WHERE cm.course = ? AND cm.module = ? AND cm.score > 0 ";
        $params = array($course->id, $assignmodid);
        $examinstances = $DB->get_records_sql($sql, $params);
    }

    $scopecodes = $DB->get_records_menu('examregistrar_elements', array('examregid'=>$examregprimaryid, 'type'=>'scopeitem') , 'idnumber ASC', 'id, idnumber');

    switch($options->assignperiod) {
        case 0 : /// as selected
                if($options->generatemode == 0) {
                    foreach($periods as $period) {
                        $item = clone $period;
                        $item->scope = 'F';
                        $item->examscope = array_search('F', $scopecodes);
                        $examperiods[] = $item;
                    }
                } elseif($options->generatemode == 1) {
                    foreach($examinstances as $exam) {
                        foreach($periods as $period) {
                            $p = strpos($exam->idnumber, $period->periodtypeidnumber);
                            if($p !== false) {
                                $scope = trim(substr($exam->idnumber, $p+strlen($period->periodtypeidnumber)));
                                $scope = ltrim($scope, '0-_');
                                if($period->periodtypeidnumber == 'ORD' && $scope) {
                                    $scope = 'P'.$scope;
                                } else {
                                    $scope = 'F'.$scope;
                                }
                                $item = clone $period;
                                $item->scope = $scope;
                                $item->examscope = array_search($scope, $scopecodes);
                                $item->examinstance = $exam->id;
                                $item->assigninstance = $exam->instance;
                                $examperiods[] = $item;
                            }
                        }
                    }
                }
                break;
        case 1 : /// from course start date
                if($options->generatemode == 0) {
                    foreach($periods as $period) {
                        if(($course->startdate >= $period->timestart) && ($course->startdate < $period->timeend)) {
                            $item = clone $period;
                            $item->scope = 'F';
                            $item->examscope = array_search('F', $scopecodes);
                            $examperiods[] = $item;
                        }
                    }
                } elseif($options->generatemode == 1) {
                    foreach($examinstances as $exam) {
                        foreach($periods as $period) {
                            if(($exam->allowsubmissionsfromdate >= $period->timestart) && ($exam->allowsubmissionsfromdate < $period->timeend)) {
                                $item = clone $period;
                                $item->scope = 'F';
                                $item->examscope = array_search('F', $scopecodes);
                                $item->examinstance = $exam->id;
                                $item->assigninstance = $exam->instance;
                                $examperiods[] = $item;
                            }
                        }
                    }
                }
                break;
        case 2 : /// from course term
                if($options->generatemode == 0) {
                    foreach($periods as $period) {
                        //print_object("periodtypeidnumber: {$period->periodtypeidnumber} / periodtermvalue: {$period->termvalue} / courseterm: {$course->term}  ");
                        $scopes = array();
                        if($period->periodtypeidnumber == 'ORD') {
                            if($course->term == 0) {
                                $scopes[] = 'P'.$period->termvalue;
                            } else {
                                if($course->term == $period->termvalue) {
                                    $scopes[] = 'F';
                                }
                            }
                        } else {
                            $scope = 'F';
                            if($course->term == 0) {
                                $scopes[] = $scope.'1';
                                $scopes[] = $scope.'2';
                            } else {
                                    $scopes[] = $scope;
                            }
                        }
                        //print_object($scopes);
                        //print_object(" --- scopes ---------");
                        foreach($scopes as $scope) {
                            $item = clone $period;
                            $item->scope = $scope;
                            $item->examscope = array_search($scope, $scopecodes);
                            $examperiods[] = $item;
                        }
                    }
                } elseif($options->generatemode == 1) {
                    foreach($examinstances as $exam) {
                        foreach($periods as $period) {
                            $p = strpos($exam->idnumber, $period->periodtypeidnumber);
                            if($p !== false) {
                                $scope = trim(substr($exam->idnumber, $p+strlen($period->periodtypeidnumber)));
                                $examscope = ltrim($scope, '0-_');
                                if($period->periodtypeidnumber == 'ORD' && $examscope) {
                                    $scope = 'P'.$examscope;
                                } else {
                                    $scope = 'F'.$examscope;
                                }
                                if(($period->periodtypeidnumber != 'ORD') ||
                                        (($period->periodtypeidnumber == 'ORD') && ($examscope == '') &&  $course->term == $period->termvalue) ||
                                        (($period->periodtypeidnumber == 'ORD') && ($examscope == $period->termvalue) && ($course->term == 0))) {
                                    $item = clone $period;
                                    $item->scope = $scope;
                                    $item->examscope = array_search($scope, $scopecodes);
                                    $item->examinstance = $exam->id;
                                    $item->assigninstance = $exam->instance;
                                    $examperiods[] = $item;
                                }
                            }
                        }
                    }
                }
                break;
    }

    return $examperiods;
}

/**
 * Creates entries in exams table for each course and period specified in $options form
 *
 * @param object $examregistrar object
 * @param array $options generating settings
 */
function examregistrar_generateexams_fromcourses($examregistrar, $options) {
    global $DB, $USER;

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);
    $now = time();

    list($insql, $params) = $DB->get_in_or_equal($options->periods);
    $sql = "SELECT p.*, ep.idnumber AS periodidnumber, ept.idnumber AS periodtypeidnumber, et.idnumber AS termidnumber, et.value AS termvalue
                FROM {examregistrar_periods} p
                JOIN {examregistrar_elements} ep ON p.examregid = ep.examregid AND p.period = ep.id
                JOIN {examregistrar_elements} ept ON p.examregid = ept.examregid AND p.periodtype = ept.id
                JOIN {examregistrar_elements} et ON p.examregid = ept.examregid AND p.term = et.id
            WHERE p.id $insql
            ORDER BY p.timestart ASC ";

    $periods = $DB->get_records_sql($sql, $params);

    $params = array();
    list($cparams, $wherecourse) = examregistrar_course_sqlselect($options);
    $params = array_merge($params, $cparams);

    $sql = "SELECT c.id, c.shortname, c.idnumber, c.category, c.startdate, uc.term, uc.credits, uc.department, uc.ctype, c.format, c.visible,
                   cc.idnumber AS catidnumber, ucc.degree
                FROM {course} c
                LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid
                JOIN {course_categories} cc ON c.category = cc.id
                LEFT JOIN {local_ulpgccore_categories} ucc ON cc.id = ucc.categoryid
                WHERE 1 $wherecourse
                ORDER BY c.category ASC, c.shortname ASC ";

    $rs_courses = $DB->get_recordset_sql($sql, $params);


    $courses = $DB->get_records_sql($sql, $params);

    $modified = array();
    $coursecount = array();
    $coursefail = array();
    $unrecognized = array();
    $keep = array();
    foreach($rs_courses as $course) {
        $category = new stdClass;
        $category->id = $course->category;
        $category->idumber = $course->catidnumber;
        $category->degree = $course->degree;
        $programme = examregistrar_programme_fromcourse($examregistrar, $course, $category, $options);
        $examperiods = examregistrar_examperiods_fromcourse($examregistrar, $course, $periods, $options);
        $keep = array();


//         print_object("course: {$course->shortname}; {$course->idnumber} / programme: $programme / term: {$course->term} / credits: {$course->credits}");
//         print_object($periods);
//         print_object($examperiods);
//         print_object(" --- examperiods -------------------");
        if($examperiods = examregistrar_examperiods_fromcourse($examregistrar, $course, $periods, $options)) {
            foreach($examperiods as $period) {
                if($period->examscope) {
                    $record = new stdClass;
                    $record->examregid = $period->examregid;
                    $record->annuality = $period->annuality;
                    $record->courseid = $course->id;
                    $record->period = $period->id;
                    $record->examscope = $period->examscope;

                    $calls = range(1,$period->calls);
                    $uniqueexam = get_object_vars($record);
                    $record->programme = $programme;

                    foreach($calls as $callnum) {
                        $record->callnum = $callnum;
                        $uniqueexam['callnum'] = $callnum;
                        $record->assignplugincm = 0;
                        if(isset($period->examinstance)) {
                            $record->assignplugincm = $period->examinstance;
                            $record->assigninstance = $period->assigninstance;
                        }
                        $record->visible = $options->examvisible;
                        if($record->visible == 2) {
                            $record->visible = $course->visible;
                        }
                        $record->examsession = 0;
                        $record->modifierid = $USER->id;
                        $record->timemodified = $now;

                        if($exam = $DB->get_record('examregistrar_exams', $uniqueexam)) {
                            $examid = $exam->id;
                            $modified[$examid] = 0;
                            $keep[$examid] = $examid;
                            $record->id = $examid;
                            if($exam->examsession) { // keep examsession if available
                                $record->examsession = $exam->examsession;
                            }
                            if($options->updateexams) {
                                $DB->update_record('examregistrar_exams', $record);
                                $modified[$examid] = 1;
                                $keep[$examid] = $examid;
                                //print_object("Update exam  {$course->shortname} ; period  {$record->period} ; id=  $examid   " );
                            }
                        } else {
                            $examid = $DB->insert_record('examregistrar_exams', $record);
                            $modified[$examid] = 2;
                            $keep[$examid] = $examid;
                            //$examid = $course->id.'_'.$record->period.'_'.$record->examscope.'_'.$callnum;
                            //print_object("Added exam  {$course->shortname} ; period  {$record->period} ; id=  $examid   " );
                        }
                        if($examid) {
                            $coursecount[$course->id] = 1;
                            // update plugin assignsubmission  exam instance
                            if(($options->generatemode == 1) && ($record->assignplugincm)) {
                                if($pluginuses = $DB->get_record('assign_plugin_config', array('assignment'=>$record->assigninstance, 'plugin'=>'exam', 'subtype'=>'assignsubmission', 'name'=>'registrarexams'))) {
                                    $uses = explode(',', $pluginuses->value);
                                    $uses[] = $examid;
                                    $pluginuses->value = implode(',', array_unique($uses));
                                    $DB->update_record('assign_plugin_config', $pluginuses);
                                } else {
                                    $pluginuses = new stdClass;
                                    $pluginuses->assignment = $record->assignplugincm;
                                    $pluginuses->plugin = 'exam';
                                    $pluginuses->subtype = 'assignsubmission';
                                    $pluginuses->name = 'registrarexams';
                                    $pluginuses->value = $examid;
                                    $DB->insert_record('assign_plugin_config', $pluginuses);
                                }
                            }
                        }
                    }
                } else {
                    $coursefail[$course->id] = 1;
                    $period->shortname = $course->shortname;
                    $unrecognized[] = $period;
                }
            }
        }
        if($options->deleteexams) {
            $params = array('courseid'=>$course->id);
            $select = " courseid = :courseid ";
            if($options->periods) {
                list($insql, $pparams) = $DB->get_in_or_equal($options->periods, SQL_PARAMS_NAMED, 'p_');
                $select .= " AND period $insql ";
                $params = $params + $pparams;
            }
            if($keep) {
                list($notinsql, $eparams) = $DB->get_in_or_equal($keep, SQL_PARAMS_NAMED, 'e_', false);
                $select .= " AND id $notinsql ";
                $params = $params + $eparams;
            }
            //$DB->delete_records_select('examregistrar_exams', $select, $params);
            $deletes = $DB->get_records_select_menu('examregistrar_exams', $select, $params, '', 'id, courseid');
            $DB->delete_records_list('examregistrar_exams', 'id', array_keys($deletes));
            foreach($deletes as $key => $del) {
                $modified[$key] = 3;
            }
        }
    }
    $rs_courses->close();

    $message1 = '';
    $message2 = '';
    if($modified) {
        $eventdata = array();
        $eventdata['objecttable'] = 'examregistrar_exams';
        list($course, $cm) = get_course_and_cm_from_instance($examregistrar, 'examregistrar');
        $context = context_module::instance($cm->id);
        $eventdata['context'] = $context;
        $eventdata['other'] = array('edit'=>'exams');
    
        $updated = 0;
        $added = 0;
        $deleted = 0;
        foreach($modified as $key => $item) {
            $eventdata['objectid'] = $key;
            switch($item) {
                case 2 : $event = \mod_examregistrar\event\manage_created::created($eventdata, 'examregistrar_exams');
                        break;
                case 3 : $event = \mod_examregistrar\event\manage_deleted::created($eventdata, 'examregistrar_exams');
                        break;
                default: $event = \mod_examregistrar\event\manage_updated::created($eventdata, 'examregistrar_exams');
            }
            $event->trigger();
            
            $updated = ($item == 1) ? $updated + 1 : $updated;
            $added = ($item == 2) ? $added + 1 : $added;
                        
            $deleted = ($item == 3) ? $deleted + 1 : $deleted;
        }
        $count = new stdClass;
        $count->courses = count($coursecount);
        $count->added = $added;
        $count->updated = $updated;
        $count->deleted = $deleted;

        $message1 = get_string('generatemodcount', 'examregistrar', $count);
    }
    if($unrecognized) {
        foreach($unrecognized as $key=>$period) {
            $unrecognized[$key] = get_string('generateunrecognizedexam', 'examregistrar', $period);
        }
        $message2 = get_string('generateunrecognized', 'examregistrar', count($unrecognized)).'<br />'.implode('<br />', $unrecognized);
    }

    return $message1.'<br /><br />'.$message2;
}



// check exam file origin for special questions usage
function warning_questions_used($examfile) {
    global $DB;

    $validquestions = get_config('quiz_makeexam', 'validquestions');
    if($validquestions) {
        $validquestions = explode(',', $validquestions);
    } else {
        return false;
    }

    if(!$validquestions) {
        $validquestions = array();
    }

    $warning = false;
    if($qme_attempt = $DB->get_record('quiz_makeexam_attempts', array('examid' =>$examfile->examid, 'examfileid'=>$examfile->id, 'status'=>1))) {
        $qids = explode(',', $qme_attempt->questions);
        if($usedquestions = $DB->get_records_list('question', 'id', $qids, '', 'id, name, qtype')) {
            foreach($usedquestions as $question) {
                if(!in_array($question->qtype, $validquestions)) {
                    $warning = true;
                    break;
                }
            }
        }
    }

    return $warning;
}

/**
 * Saves response data creating entries in responses table 
 *
 * @param object $formdata object data from user input form
 * @param int $contextid context ID for counting & moving files 
 * @param object $eventdata 
 */
function examregistrar_save_attendance_responsedata($formdata, $contextid, $eventdata) {
    global $DB, $USER;

    $params = array('examsession' => $formdata->session,
                    'examid' => $formdata->examid,
                    'examfile' => $formdata->examfile,
                    );
    $now = time();
    $fs = get_file_storage(); 
    $updated = 0;
    
    foreach($formdata->roomdata as $rid => $allocated) {
        if(!$allocated) {
            continue;
        }
        
        $params['roomid'] = $rid;
        $response = $DB->get_record('examregistrar_responses', $params);
        if(!$response) {
            $response = (object)$params;
            $response->id = $DB->insert_record('examregistrar_responses', $params);
        }

        $response->modifierid = $USER->id;
        $response->timemodified = $now;
        $response->status = $formdata->roomstatus[$rid];
        $adding = ($response->status == EXAM_RESPONSES_ADDING);
        $response->showing = $adding ? $response->showing + $formdata->showing[$rid] : $formdata->showing[$rid];
        $response->taken = $adding ? $response->taken + $formdata->taken[$rid] : $formdata->taken[$rid];
        
        $files = $fs->get_directory_files($contextid, 'mod_examregistrar', 'examresponses', $response->id, '/', false, false);
        $numfiles = count($files);
        $response->numfiles = $adding ? $response->numfiles + $numfiles : $numfiles;
        
        $message = array();
        if($response->showing > $allocated) {
            $message[] = get_string('excessshowing', 'examregistrar',  $allocated);
        }
        if($response->taken > $allocated) {
            $message[] = get_string('excesstaken', 'examregistrar', $allocated);
        }
        if($response->taken > $response->showing) {
            $message[] = get_string('excesstakenshowing', 'examregistrar', $response->showing);
        }
        
        if($message) {
            list($roomname, $roomidnumber) = examregistrar_get_namecodefromid($rid, 'locations', 'location');
            array_unshift($message, get_string('roomerror', 'examregistrar', $roomname));
            $message = implode('<br />', $message);
            \core\notification::error($message);
        } else {
            if($DB->update_record('examregistrar_responses', $response)) {
                $eventdata['other']['room'] = $rid;
                $event = \mod_examregistrar\event\attendance_loaded::create($eventdata);
                $event->trigger();
                $updated++;
            }
        }
        
        /*
        if($oldrec) {
            $response->id = $oldrec->id;
            $DB->update_record('examregistrar_responses', $response);
        } else {
            $response->id = $DB->insert_record('examregistrar_responses', $response);
        }
        */
        //// TODO  //// TODO //// TODO //// TODO 
        // move files to new itemid if needed
    
        
    }
    
    return $updated;
}


/**
 * Saves response files and data for a room/venue, creating entries in responses table 
 *
 * @param object $formdata object data from user input form
 * @param int $contextid context ID for counting & moving files 
 * @param object $eventdata 
 */
function examregistrar_save_venue_attendance_files($formdata, $contextid, $eventdata) {
    global $DB, $USER;
    
    $params = array('examsession'   => $formdata->session,
                    'roomid'        => $formdata->room);

    $sql = "SELECT e.*, ef.id AS examfile, c.shortname, c.fullname 
            FROM {examregistrar_exams} e 
            JOIN {examregistrar_examfiles} ef ON e.id = ef.examid AND ef.status = :efstatus
            JOIN {course} c ON e.courseid = c.id
            WHERE e.id = :examid AND e.examsession = :examsession
            ORDER BY ef.timeapproved DESC, ef.attempt DESC  ";
    $sqlparams = array('examsession'=> $formdata->session,
                        'efstatus'    => EXAM_STATUS_APPROVED,
                        );
    $sessionroom =  (int)"{$formdata->session}0000{$formdata->venue}";   
    
    $fr = array('component' => 'mod_examregistrar', 
                'filearea'  =>'examresponses', 
                'filepath'  =>'/'
                );

    $now = time();
    $fs = get_file_storage(); 
    $updated = 0;
    
    foreach($formdata->examattendance as $examid => $attendance) {
        if(!$attendance['status']) {
            continue;
        }
        $sqlparams['examid'] = $examid;
        $exam = $DB->get_record_sql($sql, $sqlparams);
        
        if(!$exam) {
            continue;    
        }
        
        $params['examid'] = $examid;
        $params['examfile'] = $exam->examfile;

        $response = $DB->get_record('examregistrar_responses', $params);
        if(!$response) {
            $response = (object)$params;
            $response->id = $DB->insert_record('examregistrar_responses', $params);
        }
    
        $response->modifierid = $USER->id;
        $response->timemodified = $now;
        $response->status = $attendance['status'];
        $adding = ($response->status == EXAM_RESPONSES_ADDING);
        //$response->showing = $adding ? $response->showing + $attendance['showing'] : $attendance['showing'];
        $response->taken = $adding ? $response->taken + $attendance['taken'] : $attendance['taken'];
        
        
        if($DB->update_record('examregistrar_responses', $response)) {
            $eventdata['other']['room'] = $formdata->room;
            $eventdata['other']['examid'] = $examid;
            $event = \mod_examregistrar\event\attendance_loaded::create($eventdata);
            $event->trigger();
            $updated++;
                
            $ccontext = context_course::instance($exam->courseid);
            
            $fr['contextid'] = $ccontext->id;
            $fr['itemid'] = $response->id;
            
            $files = $fs->get_directory_files($contextid, 'mod_examregistrar', 'roomresponses', $sessionroom, '/', false, false);
            // TODO  
            // id delete, delete first 
            //$fs->delete_area_files($contextid, 'mod_examregistrar', 'examresponses', $response->id); 
            
            foreach($files as $key => $file) {
                $filename = basename($file->get_filename(), '.pdf');
                if(strpos($filename, $exam->shortname) === 0) {
                    // filename starts with shortname. Move file to examrespones
                    $count = 0;
                    $suffix = '';
                    $ext = '.pdf'; 
                    while($fs->file_exists($fr['contextid'], $fr['component'], $fr['filearea'], $fr['itemid'], $fr['filepath'], 
                                $filename.$suffix.$ext)) {
                        $count++;
                        $suffix = '_'.$count; 
                    }
                    $fr['filename'] = $filename.$suffix.$ext;
                    $fs->create_file_from_storedfile($fr, $file); 
                    $file->delete();
                    $files[$key] = $fr['filename'];
                } else {
                    unset($files[$key]);
                }
            }
            
            $numfiles = count($fs->get_directory_files($ccontext->id, 'mod_examregistrar', 'examresponses', $response->id, '/', false, false));
            $response->numfiles = $adding ? $response->numfiles + $numfiles : $numfiles;
                
                
            $eventdata['other']['files'] = implode(', ', $files);
            $event = \mod_examregistrar\event\responses_uploaded::create($eventdata);
            $event->trigger();
            $DB->set_field('examregistrar_responses', 'numfiles', $numfiles, array('id'=>$response->id));
        }
    }
    
    \core\notification::success(get_string('savedresponsefiles', 'examregistrar', $updated));
    
}

/**
 * Saves user attendance data in session_seats table 
 *
 * @param object $formdata object data from user input form
 */
function examregistrar_save_attendance_userdata($formdata, $examinattendance = false) {
    global $DB, $USER;

    $params = array('examsession'   => $formdata->session);
    if(!$examinattendance) {
        $params['examid'] = $formdata->examid;
    }
    $now = time();
    $updated = 0;
    
    foreach($formdata->userattendance as $sid => $attendance) {
        if(!$attendance['add']) {
            continue;
        }
        $params['userid'] = $attendance['add'];
        $params['id'] = $sid;
        if($examinattendance) {
            $params['examid'] = $attendance['examid'];
        }
        $userdata = $DB->get_record('examregistrar_session_seats', $params, '*', MUST_EXIST);
        $userdata->showing = $attendance['showing'];
        $userdata->taken = $attendance['taken'];
        $userdata->certified = $attendance['certified'];
        $userdata->status = $formdata->userstatus;
        $userdata->modifierid = $USER->id;
        $userdata->timemodified = $now;

        if($DB->update_record('examregistrar_session_seats', $userdata)) {
            $updated++;
        }
    }

    return $updated;
}


/**
 * Saves user attendance data in session_seats table 
 *
 * @param object $formdata object data from user input form
 * @param object $eventdata 
 */
function examregistrar_confirm_attendance_userdata($formdata) {
    global $DB, $USER;
    
    $params = array('examsession'   => $formdata->session,
                    'etakenxamid'        => $formdata->examid,
                    );
                    
    $select = '';
    foreach($params as $param) {
        $select .= " $param = :$param AND ";
    }
    foreach($formdata->userattendance as $sid => $uid) {
        if(!$uid) {
            unset($formdata->userattendance[$sid]);
        }
    }
    
    list($insqlid, $idparams) = $DB->get_in_or_equal(array_keys($formdata->userattendance), SQL_PARAMS_NAMED, 'id_');
    list($insqlu, $uparams) = $DB->get_in_or_equal($formdata->userattendance, SQL_PARAMS_NAMED, 'u_');
    $select .= " userid $insqlu ";
    $params = $params + $uparams;
    $select .= " id $insqlid ";
    $params = $params + $idparams;
    
    $success = $DB->set_field_select('examregistrar_session_seats', 'status', $formdata->userstatus, $select, $params); 
    $DB->set_field_select('examregistrar_session_seats', 'reviewerid', $USER->id, $select, $params);
    $now = time();
    $DB->set_field_select('examregistrar_session_seats', 'timereviewed', $now, $select, $params); 

    if($success) {
        return count($formdata->userattendance);
    }
    
    return false;
}




/**
 * Review && confirm response files  in session_seats table 
 *
 * @param object $formdata object data from user input form
 * @param string $filename new standarized filename for files
 * @param int $contextid course context containing files
 * @param object $eventdata 
 */
function examregistrar_confirm_attendance_roomdata($formdata, $shortname, $coursectxid, $primaryctxid, $eventdata) {
    global $DB, $USER;

    $params = array('examsession' => $formdata->session,
                    'examid' => $formdata->examid,
                    'examfile' => $formdata->examfile,
                    );
    $now = time();
    $fs = get_file_storage(); 
    $updated = 0;
    unset($eventdata['other']['files']);
    unset($eventdata['other']['users']);

    $fr = array('contextid' => $primaryctxid,
                'component' => 'mod_examregistrar', 
                'filearea'  =>'sessionresponses', 
                'itemid'    => $formdata->session,
                'filepath'  =>'/'
                );
    
    foreach($formdata->roomdata as $rid => $attandance) {
        if(!$attendance) {
            //means not checked by user, not saved
            continue;
        }
        
        $params['roomid'] = $rid;
        // get or create the table record
        if($formdata->response[$rid]) {
            $params['id'] = $formdata->response[$rid];
            $response = $DB->get_record('examregistrar_responses', $params, '*', MUST_EXIST);
        } else {
            if(!$response = $DB->get_record('examregistrar_responses', $params, '*', MUST_EXIST)) {
                $response = (object)$params;
                $response->id = $DB->insert_record('examregistrar_responses', $params);
            }
        }
    
        $response->showing = $formdata->showing[$rid];
        $response->taken = $formdata->taken[$rid];
        $response->status = $formdata->roomstatus[$rid];
        $response->reviewerid = $USER->id;
        $response->timereviewed = $now;
        
        $files = $fs->get_directory_files($coursectxid, 'mod_examregistrar', 'examresponses', $response->id, '/', false, false);
        $response->numfiles = count($files);

        $success = $DB->update_record('examregistrar_responses', $response);
        
        if($success) {
            $updated++;
            $eventdata['other']['room'] = $rid;
            $event = \mod_examregistrar\event\responses_approved::create($eventdata);
            $event->trigger;

            $roomname = $roomidnumber = ''; 
            if($rid) {
                list($roomname, $roomidnumber) = examregistrar_get_namecodefromid($rid, 'locations', 'location');
            }
            
            // now move files to session
            $num = 0;
            if($files) {
                foreach($files as $key => $file) {
                    $filename = $shortname;
                    if($roomidnumber) {
                        $filename .= '-'.$roomidnumber;
                    }
                    $count = 0;
                    $suffix = '';
                    $ext = '.pdf'; 
                    while($fs->file_exists($fr['contextid'], $fr['component'], $fr['filearea'], $fr['itemid'], $fr['filepath'], 
                                $filename.$suffix.$ext)) {
                        $count++;
                        $suffix = '_'.$count; 
                    }
                    $fr['filename'] = $filename.$suffix.$ext;
                    $fs->create_file_from_storedfile($fr, $file);
                    $num++;
                    $file->delete();                    
                    $files[$key] = $fr['filename'];
                }
            }
            $eventdata['other']['room'] = $rid;
            $eventdata['other']['files'] = implode(', ',$files);
            $event = \mod_examregistrar\event\attendance_approved::create($eventdata);
            $event->trigger;
        }
    
    
    }
    
    unset($eventdata['other']['files']);
    unset($eventdata['other']['room']);

}


////////////////////////////////////////////////////////////////////////////////
// Interface & presentation functions                                         //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns administration tabs above page
 *
 * @param int $cmid course module id value
 * @param string $currenttab the current highlighted used tab
 * @print tabs
 * @return void
 */
function examregistrar_print_tabs($cmid, $currenttab = 'view') {
    global $OUTPUT;

    $row = array();
    $row[] = new tabobject('view',
                           new moodle_url('/mod/examregistrar/view.php', array('id' => $cmid)),
                           get_string('view', 'examregistrar'));

    $row[] = new tabobject('manage',
                           new moodle_url('/mod/examregistrar/manage.php', array('id' => $cmid)),
                           get_string('manage', 'examregistrar'));

    echo '<div class="tabdisplay">';
    echo $OUTPUT->tabtree($row, $currenttab);
    echo '</div>';
}




/**
 * Returns a collection of courses that have associated exams defined
 *    and the user can access  (checking appropiate capability)
 *
 * @param object $examregistrar instance object
 * @param object $course record object for course calling this (where instance is placed)
 * @param array $searchparams parameteres need for the user course/exam searching
 * @param array $capabilities the permissions to check for user in the
 * @param bool $viewall permission to see all courses
 * @param bool $booking special provisions if searching bookable courses
 * @return array user courses
 */
function examregistrar_get_user_courses($examregistrar, $course, $searchparams, $capabilities, $canviewall, $booking = false) {
    global $DB, $USER;

    $courses = array();

    $term = isset($searchparams['term']) ? $searchparams['term'] : 0;
    $period = isset($searchparams['period']) ? $searchparams['period'] : 0;
    $searchname = isset($searchparams['searchname']) ? $searchparams['searchname'] : '';
    $searchid = isset($searchparams['searchid']) ? $searchparams['searchid'] : 0;
    $sort = isset($searchparams['sorting']) ? $searchparams['sorting'] : '';
    $order = isset($searchparams['order']) ? $searchparams['order'] : '';
    $programme = isset($searchparams['programme']) ? $searchparams['programme'] : '';

    $userid = isset($searchparams['user']) ? $searchparams['user'] : $USER->id;
    $session = isset($searchparams['session']) ? $searchparams['session'] : 0;
    $bookedsite = isset($searchparams['venue']) ? $searchparams['venue'] : 0;

    $examregprimaryid = examregistrar_get_primaryid($examregistrar);

    $params = array();
    $coursewhere = '';
    $examwhere = '';
    if($period) {
        $examwhere .= ' AND e.period = :period ';
        $params['period'] = $period;
    }
    if($examregistrar->workmode == EXAMREGISTRAR_MODE_REGISTRAR) {
        // if examregistrar define a programme, use it
        if($programme) {
            $examwhere = " AND e.programme = :programme ";
            $params['programme'] = $programme;
        } elseif($examregistrar->programme) {
            $examwhere = " AND e.programme = :programme ";
            $params['programme'] = trim($examregistrar->programme);
        }
        // if not programme, not set search : all returned
    } elseif($examregistrar->workmode == EXAMREGISTRAR_MODE_REVIEW || $examregistrar->workmode == EXAMREGISTRAR_MODE_BOOK) {
        if($programme) {
            $examwhere = " AND e.programme = :programme ";
            $params['programme'] = $programme;
        } elseif($examregistrar->programme) {
            $examwhere = " AND e.programme = :programme ";
            $params['programme'] = trim($examregistrar->programme);
        } else {
        // if not programme, use course category
            $coursewhere = " AND c.category = :category ";
            $params['category'] = $course->category;
        }
    } else {
        $coursewhere = " AND c.id = :courseid ";
        $params['courseid'] = $course->id;
    }

    if($term && get_config('local_ulpgccore') ) {
        $termvalue = $DB->get_field('examregistrar_elements', 'value', array('id'=>$term, 'type'=>'termitem', 'examregid'=>$examregprimaryid));
        if($termvalue !== false) {
            if(($termvalue == 1) OR ($termvalue == 2)) {
                $coursewhere .= " AND ((uc.term = 0) OR (uc.term = :term)) ";
            } else {
                $coursewhere .= " AND (uc.term = :term) ";
            }
            $params['term']  = $termvalue;
        }
    }

    $searchwhere = '';
    if($searchname) {
        $searchwhere .= ' AND '.$DB->sql_like('c.fullname', ':fullname', false, false);
        $params['fullname'] = '%'.$searchname.'%';
    }
    if($searchid) {
        $searchwhere .= " AND c.id = :searchid";
        $params['searchid'] = $searchid;
    }
    if(!$sort) {
        $sort = 'shortname';
    }
    if(!$order) {
        $order = 'ASC';
    }

    $examregistrarwhere = '';
    if($booking) {
        if(isset($params['programme'])) {
            $params['programme'] = '%'.$params['programme'].'%';
            $coursewhere .= ' AND '.$DB->sql_like('c.idnumber', ':programme');
        }
        if($courses = enrol_get_users_courses($userid, true, null, 'category ASC, fullname ASC')) {
            list($insql, $cparams) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'course_');
            $coursewhere .= " AND c.id $insql ";
            $params = $params + $cparams;
        } else {
            return array();
        }
    } else {
        $examregistrarwhere = "AND EXISTS ( SELECT 1
                                            FROM {examregistrar_exams} e
                                            WHERE  e.courseid = c.id $examwhere  ) ";
    }


    $sql = "SELECT c.id, c.fullname, c.shortname, c.idnumber, uc.term, uc.credits
                    FROM {course} c
                    LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid
                    WHERE 1 $coursewhere  $searchwhere $examregistrarwhere
                    ORDER BY c.$sort $order ";

    $courses = $DB->get_records_sql($sql, $params);

    foreach($courses as $cid => $examcourse) {
        // check review permissions in exam course
        $econtext = context_course::instance($examcourse->id);
        if(!has_any_capability($capabilities, $econtext, $userid) && !$canviewall) {
            unset($courses[$cid]);
        }
    }

    return $courses;
}



////////////////////////////////////////////////////////////////////////////////
// Classes
////////////////////////////////////////////////////////////////////////////////



/**
 * class used by user selection controls
 * @package mod_examregistrar
 * @copyright 2012 Enrique Castro
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 class examregistrar_user_selector extends user_selector_base {

    /**
     * The id of the examregistrar this selector is being used for
     * @var int
     */
    protected $examregid = null;
    /**
     * The context of the forum this selector is being used for
     * @var object
     */
    protected $context = null;
    /**
     * The id of the current group
     * @var int
     */
    protected $currentgroup = null;

    /**
     * Constructor method
     * @param string $name
     * @param array $options
     */
    public function __construct($name, $options) {
        $options['accesscontext'] = $options['context'];
        parent::__construct($name, $options);
        if (isset($options['context'])) {
            $this->context = $options['context'];
        }
        if (isset($options['currentgroup'])) {
            $this->currentgroup = $options['currentgroup'];
        }
        if (isset($options['examgregid'])) {
            $this->examregid = $options['examgregid'];
        }
    }

    /**
     * Returns an array of options to seralise and store for searches
     *
     * @return array
     */
    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] =  substr(__FILE__, strlen($CFG->dirroot.'/'));
        $options['context'] = $this->context;
        $options['currentgroup'] = $this->currentgroup;
        $options['examgregid'] = $this->examregid;
        return $options;
    }

    /**
     * Finds all potential users
     *
     * Potential users are determined by checking for users with a capability
     * determined in {@see forum_get_potential_subscribers()}
     *
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $DB;

        // only active enrolled users or everybody on the frontpage
        list($esql, $params) = get_enrolled_sql($this->context, 'mod/examregistrar:book', $this->currentgroup, true);
        $fields = get_all_user_name_fields(true, 'u');
        $sql = "SELECT u.id, u.username, u.idnumber, u.email, $fields
                FROM {user} u
                JOIN ($esql) je ON je.id = u.id
                ORDER BY u.lastname ASC, u.firstname ASC ";

        $availableusers = $DB->get_records_sql($sql, $params);


        //$availableusers = forum_get_potential_subscribers($this->context, $this->currentgroup, $this->required_fields_sql('u'), 'u.firstname ASC, u.lastname ASC');

        if (empty($availableusers)) {
            $availableusers = array();
        } else if ($search) {
            $search = strtolower($search);
            foreach ($availableusers as $key=>$user) {
                if (stripos($user->firstname, $search) === false && stripos($user->lastname, $search) === false && stripos($user->idnumber, $search) === false && stripos($user->username, $search) === false ) {
                    unset($availableusers[$key]);
                }
            }
        }

        if ($search) {
            $groupname = get_string('potentialusersmatching', 'examregistrar', $search);
        } else {
            $groupname = get_string('potentialusers', 'examregistrar');
        }
        return array($groupname => $availableusers);
    }


    /**
     * Output this user_selector as HTML.
     * @param boolean $return if true, return the HTML as a string instead of outputting it.
     * @return mixed if $return is true, returns the HTML as a string, otherwise returns nothing.
     */
     /*
    public function display($return = false) {
        global $PAGE;

        $search = optional_param($this->name . '_searchtext', '', PARAM_RAW);
        $output = parent::display($return);
        $PAGE->requires->js_init_call('M.core_user.init_user_selector_options_tracker', array(), false, self::$jsmodule);
        // Initialise the ajax functionality.
        $output .= $this->initialise_javascript($search);

        // Return or output it.
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }*/

}




