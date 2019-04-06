<?php

/**
 * This file contains form classes & form definios for Examregistrar manage interface
 *
 * @package   mod_examregistrar
 * @copyright 2014 Enrique Castro at ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once($CFG->dirroot . '/repository/lib.php');

class examregistrar_element_form extends moodleform {

    function definition() {
        global $EXAMREGISTRAR_ELEMENTTYPES;

        $mform =& $this->_form;
        $item = $this->_customdata['item'];
        $cmid = $this->_customdata['cmid'];
        $examreg = $this->_customdata['exreg'];
        $exreg = examregistrar_get_primaryid($examreg);

        $mform->addElement('text', 'name', get_string('itemname', 'examregistrar'), array('size'=>'30'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'itemname', 'examregistrar');

        $mform->addElement('text', 'idnumber', get_string('idnumber', 'examregistrar'), array('size'=>'20'));
        $mform->setType('idnumber', PARAM_ALPHANUMEXT);
        $mform->addRule('idnumber', null, 'required', null, 'client');
        $mform->addRule('idnumber', get_string('maximumchars', '', 20), 'maxlength', 20, 'client');
        $mform->addHelpButton('idnumber', 'idnumber', 'examregistrar');

        $typemenu = array('0' => get_string('choose'));
        foreach($EXAMREGISTRAR_ELEMENTTYPES as $type) {
            $typemenu[$type] = get_string($type, 'examregistrar');
        }

        $mform->addElement('select', 'type', get_string('elementtype', 'examregistrar'), $typemenu);
        $mform->addHelpButton('type', 'elementtype', 'examregistrar');
        $mform->addRule('type', null, 'required', null, 'client');
        $mform->addRule('type', null, 'minlength', 2, 'client');
        $mform->setDefault('type', '0');

        $mform->addElement('text', 'value', get_string('elementvalue', 'examregistrar'), array('size'=>'10'));
        $mform->setType('value', PARAM_INT);
        $mform->addRule('value', null, 'numeric', null, 'client');
        $mform->addHelpButton('value', 'elementvalue', 'examregistrar');

        $mform->addElement('selectyesno', 'visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('visible', 1);

        $mform->addElement('hidden', 'examregid', $exreg);
        $mform->setType('examregid', PARAM_INT);

        $mform->addElement('hidden', 'edit', 'elements');
        $mform->setType('edit', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'item', $item);
        $mform->setType('item', PARAM_INT);
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->setConstant('id', $cmid);

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}


class examregistrar_period_form extends moodleform {

    function definition() {

        $mform =& $this->_form;
        $item = $this->_customdata['item'];
        $cmid = $this->_customdata['cmid'];
        $examreg = $this->_customdata['exreg'];
        $exreg = examregistrar_get_primaryid($examreg);

        $annualitymenu = examregistrar_elements_getvaluesmenu($examreg, 'annualityitem', $exreg, 'choose');
        $mform->addElement('select', 'annuality', get_string('annualityitem', 'examregistrar'), $annualitymenu);
        $mform->addHelpButton('annuality', 'annualityitem', 'examregistrar');
        $mform->addRule('annuality', null, 'required', null, 'client');

        $periodmenu = examregistrar_elements_getvaluesmenu($examreg, 'perioditem', $exreg, 'choose');
        $mform->addElement('select', 'period', get_string('perioditem', 'examregistrar'), $periodmenu);
        $mform->addHelpButton('period', 'perioditem', 'examregistrar');
        $mform->addRule('period', null, 'required', null, 'client');

        $periodtypemenu = examregistrar_elements_getvaluesmenu($examreg, 'periodtypeitem', $exreg, 'choose');
        $mform->addElement('select', 'periodtype', get_string('periodtypeitem', 'examregistrar'), $periodtypemenu);
        $mform->addHelpButton('periodtype', 'periodtypeitem', 'examregistrar');
        $mform->addRule('periodtype', null, 'required', null, 'client');

        $termmenu = examregistrar_elements_getvaluesmenu($examreg, 'termitem', $exreg, 'choose');
        $mform->addElement('select', 'term', get_string('termitem', 'examregistrar'), $termmenu);
        $mform->addHelpButton('term', 'termitem', 'examregistrar');
        $mform->addRule('term', null, 'required', null, 'client');

        $callsmenu = array();
        for($i=1; $i<=12; $i++) {
            $callsmenu[$i] = $i;
        }
        $mform->addElement('select', 'calls', get_string('numcalls', 'examregistrar'), $callsmenu);
        $mform->addHelpButton('calls', 'numcalls', 'examregistrar');
        $mform->setDefault('calls', 1);

        $defaultdate = strtotime(date('Y').'-09-01');
        $defaultdate = strtotime('+1 day', $defaultdate);
        $mform->addElement('date_selector', 'timestart', get_string('timestart', 'examregistrar'));
        $mform->addHelpButton('timestart', 'timestart', 'examregistrar');
        $mform->setDefault('timestart', $defaultdate);
        $mform->addElement('date_selector', 'timeend', get_string('timeend', 'examregistrar'));
        $mform->addHelpButton('timeend', 'timeend', 'examregistrar');
        $mform->setDefault('timeend', strtotime('+6 months', $defaultdate));

        $mform->addElement('selectyesno', 'visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('visible', 1);

        $mform->addElement('hidden', 'examregid', $exreg);
        $mform->setType('examregid', PARAM_INT);

        $mform->addElement('hidden', 'edit', 'periods');
        $mform->setType('edit', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'item', $item);
        $mform->setType('item', PARAM_INT);
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->setConstant('id', $cmid);

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}

class examregistrar_examsession_form extends moodleform {

    function definition() {
        global $DB; // TODO eliminate when parameters passed
        $mform =& $this->_form;
        $item = $this->_customdata['item'];
        $cmid = $this->_customdata['cmid'];
        $examreg = $this->_customdata['exreg'];
        $defaults = $this->_customdata['defaults'];
        $exreg = examregistrar_get_primaryid($examreg);

        $sessionmenu = examregistrar_elements_getvaluesmenu($examreg, 'examsessionitem', $exreg, 'choose');
        $mform->addElement('select', 'examsession', get_string('examsessionitem', 'examregistrar'), $sessionmenu);
        $mform->addHelpButton('examsession', 'examsessionitem', 'examregistrar');
        $mform->addRule('examsession', null, 'required', null, 'client');

        //$menu = examregistrar_get_referenced_namesmenu($examreg, 'periods', 'perioditem', $exreg, 'choose', '', array('annuality'=>3));
        $menu = examregistrar_get_referenced_namesmenu($examreg, 'periods', 'perioditem', $exreg, 'choose');

        foreach($menu as $period => $name) {
            if($period) {
                $annuality = $DB->get_field('examregistrar_periods', 'annuality', array('id'=>$period));
                $ann_name = $DB->get_field('examregistrar_elements', 'name', array('id'=>$annuality));
                $menu[$period] = $name. " [$ann_name]";
            }
        }

        $mform->addElement('select', 'period', get_string('perioditem', 'examregistrar'), $menu);
        $mform->addHelpButton('period', 'perioditem', 'examregistrar');
        $mform->addRule('period', null, 'required', null, 'client');
        $mform->disabledIf('period', 'annuality', 'eq', '');

        $mform->addElement('date_selector', 'examdate', get_string('examdate', 'examregistrar'));
        $mform->addHelpButton('examdate', 'examdate', 'examregistrar');
        $mform->addRule('examdate', null, 'required', null, 'client');
        $mform->setDefault('examdate', strtotime("1 september ".date('Y')));

        $mform->addElement('text', 'timeslot', get_string('timeslot', 'examregistrar'), array('size'=>'10'));
        $mform->setType('timeslot', PARAM_TEXT);
        $mform->addHelpButton('timeslot', 'timeslot', 'examregistrar');
        $mform->addRule('timeslot', get_string('maximumchars', '', 10), 'maxlength', 10, 'client');
        $mform->addRule('timeslot', null, 'minlength', 2, 'client');
        $mform->setDefault('timeslot', '10.00');

        $mform->addElement('duration', 'duration', get_string('duration', 'examregistrar'), array('optional' => false));
        $mform->addHelpButton('duration', 'duration', 'examregistrar');
        $mform->setDefault('duration', 60*60*2);

        $mform->addElement('selectyesno', 'visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('visible', 1);

        $mform->addElement('hidden', 'examregid', $exreg);
        $mform->setType('examregid', PARAM_INT);

        $mform->addElement('hidden', 'edit', 'examsessions');
        $mform->setType('edit', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'item', $item);
        $mform->setType('item', PARAM_INT);
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->setConstant('id', $cmid);

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}


class examregistrar_exam_form extends moodleform {

    function definition() {
        global $DB;

        $mform =& $this->_form;
        $item = $this->_customdata['item'];
        $cmid = $this->_customdata['cmid'];
        $examreg = $this->_customdata['exreg'];
        $exreg = examregistrar_get_primaryid($examreg);

        $annualitymenu = examregistrar_elements_getvaluesmenu($examreg, 'annualityitem', $exreg, 'choose');
        $mform->addElement('select', 'annuality', get_string('annualityitem', 'examregistrar'), $annualitymenu);
        $mform->addHelpButton('annuality', 'annualityitem', 'examregistrar');
        $mform->addRule('annuality', null, 'required', null, 'client');

        $exam = false;
        if($item > 0) {
            $sql = "SELECT e.*, c.shortname, c.fullname, c.category
                    FROM {examregistrar_exams} e
                    JOIN {course} c ON e.courseid = c.id
                    WHERE e.id = :id ";
            $exam = $DB->get_record_sql($sql, array('id'=>$item), MUST_EXIST);
        }

        $programmes = array(''=>get_string('choose'));
        $courses = array(''=>get_string('choose'));
        if($exam) {
            $programmes = array($exam->programme => $exam->programme);
            $courses = array($exam->courseid => $exam->shortname.' - '.$exam->fullname);
        } else {
            $categories = make_categories_options();
            $degrees = array();
            if(get_config('local_ulpgccore')) {
                $degrees = $DB->get_records_list('local_ulpgccore_categories', 'categoryid', array_keys($categories), '', 'categoryid, degree');
            }
            foreach($categories as $id =>$name) {
                $key = isset($degrees[$id]) ? $degrees[$id]->degree : $id;
                if($key) {
                    $programmes[$key] = $name;
                }
            }
            
            unset($categories);
            $scourses = get_courses("all", "c.shortname ASC", "c.id, c.shortname, c.fullname");
            foreach($scourses as $cid => $course) {
                $courses[$cid] = $course->shortname.' - '.$course->fullname;
            }
            unset($scourses);
        }

        $mform->addElement('select', 'programme', get_string('programme', 'examregistrar'), $programmes);
        $mform->addRule('programme', null, 'required', null, 'client');
        $mform->addHelpButton('programme', 'programme', 'examregistrar');

        $mform->addElement('select', 'courseid', get_string('shortname', 'examregistrar'), $courses);
        $mform->addRule('courseid', null, 'required', null, 'client');
        $mform->addHelpButton('courseid', 'shortname', 'examregistrar');

        $menu = examregistrar_get_referenced_namesmenu($examreg, 'periods', 'perioditem', $exreg, 'choose');
        $mform->addElement('select', 'period', get_string('perioditem', 'examregistrar'), $menu);
        $mform->addHelpButton('period', 'perioditem', 'examregistrar');
        $mform->addRule('period', null, 'required', null, 'client');

        $menu = examregistrar_elements_getvaluesmenu($examreg, 'scopeitem', $exreg, 'choose');
        $mform->addElement('select', 'examscope', get_string('scopeitem', 'examregistrar'), $menu);
        $mform->addHelpButton('examscope', 'scopeitem', 'examregistrar');
        $mform->addRule('examscope', null, 'required', null, 'client');

        $callsmenu = array();
        for($i=1; $i<=12; $i++) {
            $callsmenu[$i] = $i;
        }
        $mform->addElement('select', 'callnum', get_string('callnum', 'examregistrar'), $callsmenu);
        $mform->addHelpButton('callnum', 'callnum', 'examregistrar');
        $mform->addRule('callnum', null, 'numeric', null, 'client');
        $mform->addRule('callnum', null, 'nonzero', null, 'client');

        $mform->addElement('selectyesno', 'additional', get_string('extraexamcall', 'examregistrar'));
        $mform->addHelpButton('additional', 'extraexamcall', 'examregistrar');
        $mform->setDefault('additional', 0);

        $menu = examregistrar_get_referenced_namesmenu($examreg, 'examsessions', 'examsessionitem', $exreg, 'choose');
        $mform->addElement('select', 'examsession', get_string('examsessionitem', 'examregistrar'), $menu);
        $mform->addHelpButton('examsession', 'examsessionitem', 'examregistrar');
        $mform->addRule('examsession', null, 'required', null, 'client');
        $mform->addRule('examsession', null, 'nonzero', null, 'client');

        $mform->addElement('selectyesno', 'visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('visible', 1);

        $mform->addElement('hidden', 'examregid', $exreg);
        $mform->setType('examregid', PARAM_INT);

        $mform->addElement('hidden', 'edit', 'exams');
        $mform->setType('edit', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'item', $item);
        $mform->setType('item', PARAM_INT);
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->setConstant('id', $cmid);

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}


class examregistrar_location_form extends moodleform {

    function definition() {

        $mform =& $this->_form;
        $item = $this->_customdata['item'];
        $cmid = $this->_customdata['cmid'];
        $examreg = $this->_customdata['exreg'];
        $exreg = examregistrar_get_primaryid($examreg);

        $menu = examregistrar_elements_getvaluesmenu($examreg, 'locationitem', $exreg, 'choose');
        $mform->addElement('select', 'location', get_string('locationitem', 'examregistrar'), $menu);
        $mform->addHelpButton('location', 'locationitem', 'examregistrar');
        if($item > 0) {
            $mform->freeze('location');
        } else {
            $mform->addRule('location', null, 'required', null, 'client');
        }

        $menu = examregistrar_elements_getvaluesmenu($examreg, 'locationtypeitem', $exreg, 'choose');
        $mform->addElement('select', 'locationtype', get_string('locationtypeitem', 'examregistrar'), $menu);
        $mform->addHelpButton('locationtype', 'locationtypeitem', 'examregistrar');
        $mform->addRule('locationtype', null, 'required', null, 'client');

        $mform->addElement('text', 'seats', get_string('seats', 'examregistrar'), array('size'=>'5'));
        $mform->setType('seats', PARAM_INT);
        $mform->addRule('seats', null, 'numeric', null, 'client');
        $mform->addHelpButton('seats', 'seats', 'examregistrar');
        $mform->setDefault('seats', 0);

        //$menu = examregistrar_get_referenced_namesmenu($examreg, 'locations', 'locationitem', $exreg, 'choose');
        $menu = examregistrar_get_potential_parents($examreg, $item, 'name', true);
        $mform->addElement('select', 'parent', get_string('parent', 'examregistrar'), $menu);
        $mform->addHelpButton('parent', 'parent', 'examregistrar');

        $mform->addElement('selectyesno', 'visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('visible', 1);

        $mform->addElement('editor', 'address', get_string('address', 'examregistrar'));
        $mform->setType('address', PARAM_RAW);

        $mform->addElement('hidden', 'examregid', $exreg);
        $mform->setType('examregid', PARAM_INT);

        $mform->addElement('hidden', 'edit', 'locations');
        $mform->setType('edit', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'item', $item);
        $mform->setType('item', PARAM_INT);
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->setConstant('id', $cmid);

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}


class examregistrar_staffer_form extends moodleform {

    function definition() {

        $mform =& $this->_form;
        $item = $this->_customdata['item'];
        $cmid = $this->_customdata['cmid'];
        $examreg = $this->_customdata['exreg'];
        $exreg = examregistrar_get_primaryid($examreg);

        if($item > 0) {

        }

        $menu = examregistrar_elements_getvaluesmenu($examreg, 'locationitem', $exreg, 'choose');
        $mform->addElement('select', 'locationid', get_string('locationitem', 'examregistrar'), $menu);
        $mform->addHelpButton('locationid', 'location', 'examregistrar');
        $mform->addRule('locationid', null, 'required', null, 'client');
        $mform->addRule('locationid', null, 'nonzero', null, 'client');

        $menu = examregistrar_get_potential_staffers($examreg, $item);
        $mform->addElement('select', 'userid', get_string('staffer', 'examregistrar'), $menu);
        $mform->addHelpButton('userid', 'location', 'examregistrar');
        $mform->addRule('userid', null, 'required', null, 'client');
        $mform->addRule('userid', null, 'nonzero', null, 'client');

        $menu = examregistrar_elements_getvaluesmenu($examreg, 'roletype');
        $mform->addElement('select', 'roletype', get_string('roletype', 'examregistrar'), $menu);
        $mform->addHelpButton('roletype', 'roletype', 'examregistrar');
        $mform->addRule('roletype', null, 'required', null, 'client');
        $mform->addRule('roletype', null, 'nonzero', null, 'client');

        $mform->addElement('text', 'info', get_string('staffinfo', 'examregistrar'), array('size'=>'32'));
        $mform->setType('info', PARAM_TEXT);
        $mform->addRule('info', null, 'required', null, 'client');
        $mform->addRule('info', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('info', 'staffinfo', 'examregistrar');

        $mform->addElement('selectyesno', 'visible', get_string('visibility', 'examregistrar'));
        $mform->setDefault('visible', 1);

        $mform->addElement('hidden', 'examregid', $exreg);
        $mform->setType('examregid', PARAM_INT);

        $mform->addElement('hidden', 'edit', 'staffers');
        $mform->setType('edit', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'item', $item);
        $mform->setType('item', PARAM_INT);
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->setConstant('id', $cmid);

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}


class examregistrar_session_room_form extends moodleform {

    function definition() {

        $mform =& $this->_form;
        $item = $this->_customdata['item'];
        $cmid = $this->_customdata['cmid'];
        $examreg = $this->_customdata['exreg'];
        $exreg = examregistrar_get_primaryid($examreg);

        $menu = examregistrar_get_namesmenu($exreg, 'examsessions');
        $mform->addElement('select', 'examsession', get_string('examsession', 'examregistrar'), $menu);
        $mform->addHelpButton('examsession', 'examsession', 'examregistrar');
        $mform->addRule('examsession', null, 'required', null, 'client');
        $mform->addRule('examsession', null, 'nonzero', null, 'client');

        $menu = examregistrar_get_namesmenu($exreg, 'locations');
        $mform->addElement('select', 'locationid', get_string('location', 'examregistrar'), $menu);
        $mform->addHelpButton('locationid', 'location', 'examregistrar');
        $mform->addRule('locationid', null, 'required', null, 'client');
        $mform->addRule('locationid', null, 'nonzero', null, 'client');

        $mform->addElement('selectyesno', 'available', get_string('visibility', 'examregistrar'));
        $mform->setDefault('available', 1);

        $mform->addElement('hidden', 'examregid', $exreg);
        $mform->setType('examregid', PARAM_INT);

        $mform->addElement('hidden', 'edit', 'session_rooms');
        $mform->setType('edit', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'item', $item);
        $mform->setType('item', PARAM_INT);
        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);
        $mform->setConstant('id', $cmid);

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}


class examregistrar_uploadcsv_form extends moodleform {

    function definition() {
        global $COURSE;

        $mform =& $this->_form;
        $action = $this->_customdata['csv'];
        $id = $this->_customdata['id']; // course_module id
        $edit = $this->_customdata['edit'];
        $session = $this->_customdata['session'];
        $examreg = $this->_customdata['exreg'];
        $exreg = examregistrar_get_primaryid($examreg);

        $context = context_module::instance($id);
        switch($action) {
            case 'elements' : require_capability('mod/examregistrar:editelements',$context);
                                break;
            case 'periods'  :
            case 'examsessions' : require_capability('mod/examregistrar:manageperiods',$context);
                                break;
            case 'staffers' :
            case 'locations': require_capability('mod/examregistrar:managelocations',$context);
                                break;
            case 'session_rooms':
            case 'assignseats'  : require_capability('mod/examregistrar:manageseats',$context);
                                    break;
            default  : require_capability('mod/examregistrar:editelements',$context);
        }

        $mform->addElement('header', 'uploadsettings', get_string('uploadsettings', 'examregistrar'));

        $actions = array('0' => get_string('choose'));
        $uploads = array('elements', 'periods', 'examsessions', 'locations', 'staffers', 'session_rooms', 'assignseats');
        foreach($uploads as $upload) {
            $actions[$upload] = get_string('uploadcsv'.$upload, 'examregistrar');
        }
        $mform->addElement('select', 'csv', get_string('uploadtype', 'examregistrar'), $actions);
        $mform->addHelpButton('csv', 'uploadtype', 'examregistrar');
        $mform->addRule('csv', null, 'required', null, 'client');
        $mform->addRule('csv', null, 'minlength', 2, 'client');
        $mform->setDefault('csv', $action);
        if($action) {
            $mform->freeze('csv');
        }

        if($action == 'assignseats' || $action == 'staffers') {
            $menu = examregistrar_get_referenced_namesmenu($examreg, 'examsessions', 'examsessionitem', $exreg, 'choose');
            $mform->addElement('select', 'examsession', get_string('examsessionitem', 'examregistrar'), $menu);
            $mform->addHelpButton('examsession', 'examsessionitem', 'examregistrar');
            $mform->addRule('examsession', null, 'required', null, 'client');
            $mform->setDefault('examsession', $session);
            if($session) {
                $mform->freeze('examsession');
            }
        } else {
            $mform->addElement('hidden', 'examsession', $session);
            $mform->setType('examsession', PARAM_INT);
        }

        $fileoptions = array('subdirs'=>0,
                                'maxbytes'=>$COURSE->maxbytes,
                                'accepted_types'=>'csv, txt',
                                'maxfiles'=>1,
                                'return_types'=>FILE_INTERNAL);

        $mform->addElement('filepicker', 'uploadfile', get_string('uploadafile'), null, $fileoptions);
        $mform->addRule('uploadfile', get_string('uploadnofilefound'), 'required', null, 'client');
        $mform->addHelpButton('uploadfile', 'uploadcsvfile', 'examregistrar');

        $mform->addElement('selectyesno', 'ignoremodified', get_string('ignoremodified', 'examregistrar'));
        $mform->addHelpButton('ignoremodified', 'ignoremodified', 'examregistrar');
        $mform->setDefault('ignoremodified', 0);

        $mform->addElement('selectyesno', 'editidnumber', get_string('editidnumber', 'examregistrar'));
        $mform->addHelpButton('editidnumber', 'editidnumber', 'examregistrar');
        $mform->setDefault('editidnumber', 0);
        if(!has_capability('mod/examregistrar:editelements',$context)) {
            $mform->freeze('editidnumber');
        }

        // add support for explicit csv alternate formats
        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimiter', get_string('separator', 'grades'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimiter', 'semicolon');
        } else {
            $mform->setDefault('delimiter', 'comma');
        }

        $choices = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'grades'), $choices);
        $mform->setDefault('encoding', 'utf-8');

        $mform->addElement('hidden', 'examregid', $exreg);
        $mform->setType('examregid', PARAM_INT);

        $mform->addElement('hidden', 'edit', $edit);
        $mform->setType('edit', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $mform->setConstant('id', $id);

        $this->add_action_buttons(true, get_string('save', 'examregistrar'));
    }
}


class examregistrar_uploadcsv_confirm_form extends moodleform {
    function definition() {
        global $COURSE, $USER, $OUTPUT;

        $mform =& $this->_form;
        if($customdata = $this->_customdata) {
            foreach($customdata as $key => $value) {
                $mform->addElement('hidden', $key, $value);
                $mform->setType($key, PARAM_RAW);
            }
        }

        $mform->addElement('hidden', 'confirm', 1);
        $mform->setType('confirm', PARAM_INT);

        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $customdata['draftid'], 'id DESC', false)) {
            redirect(new moodle_url('/mod/examregistrar/manage.php',
                                array('id'=>$customdata['id'])));
        }
        $file = reset($files);

        $csvdata = $file->get_content();

        $columns = '';
        if ($csvdata) {
            $csvreader = new csv_import_reader($customdata['importid'], 'examregistrar_upload_'. $customdata['csv']);
            $csvreader->load_csv_content($csvdata,  $customdata['encoding'],  $customdata['delimiter']);
            $csvreader->init();
            $columns = $csvreader->get_columns();
        }

        $rows = array();
        if($columns) {
            $index = 0;
            while ($index <= 5 && ($record = $csvreader->next()) ) {
                $rows[] = implode(', ', $record);
                $index += 1 ;
            }

        }

        $mform->addElement('html',  get_string('uploadtableexplain', 'examregistrar'));
        $mform->addElement('html',  $OUTPUT->box(implode(', ', $columns).'<br />'.implode('<br />', $rows), ' generalbox informationbox centerbox centeredbox' ));
        $mform->addElement('html',  get_string('uploadconfirm', 'examregistrar'));

        $this->add_action_buttons(true, get_string('confirm'));
    }
}


class examregistrar_generateexams_form extends moodleform {

    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;
        $cmid = $this->_customdata['cmid'];
        $items = $this->_customdata['items'];
        $examreg = $this->_customdata['exreg'];
        $exreg = examregistrar_get_primaryid($examreg);

        $mform->addElement('header', 'headgeneratesettings', get_string('generateexamssettings', 'examregistrar'));

        $options = array();
        $options['0'] = get_string('genexamcourse', 'examregistrar');
        $assignexamconfig = get_config('assignsubmission_exam');
        if($assignexamconfig && (!isset($assignexamconfig->disabled) || $assignexamconfig->disabled == 0)) {
           $options['1'] = get_string('genexamexam', 'examregistrar');
        }
        $mform->addElement('select', 'generatemode', get_string('generatemode', 'examregistrar'), $options);
        $mform->setDefault('generatemode', 0);
        $mform->addHelpButton('generatemode', 'generatemode', 'examregistrar');

        //$menu = examregistrar_get_referenced_namesmenu($examreg, 'periods', 'perioditem', $exreg, 'choose', '', array('annuality'=>3));
        $menu = examregistrar_get_referenced_namesmenu($examreg, 'periods', 'perioditem', $exreg, 'choose');
        foreach($menu as $period => $name) {
            if($period) {
                $annuality = $DB->get_field('examregistrar_periods', 'annuality', array('id'=>$period));
                $ann_name = $DB->get_field('examregistrar_elements', 'name', array('id'=>$annuality));
                $menu[$period] = $name. " [$ann_name]";
            }
        }
        $periodsmenu = &$mform->addElement('select', 'periods', get_string('genforperiods', 'examregistrar'), $menu, 'size="6"');
        $periodsmenu->setMultiple(true);
        $mform->addRule('periods', null, 'required', null, 'client');
        $mform->addHelpButton('periods', 'genforperiods', 'examregistrar');


        $options = array();
        $options['0'] = get_string('periodselected', 'examregistrar');
        $options['1'] = get_string('periodfromstartdate', 'examregistrar');
        if($DB->get_manager()->field_exists('local_ulpgccore_course', 'term')) {
            $options['2'] = get_string('periodfromterm', 'examregistrar');
        }
        $mform->addElement('select', 'assignperiod', get_string('genassignperiod', 'examregistrar'), $options);
        $mform->setDefault('assignperiod', 0);
        $mform->addHelpButton('assignperiod', 'genassignperiod', 'examregistrar');

        $options = array('courseshortname', 'courseidnumber', 'coursecatid', 'coursecatidnumber');
        if($DB->get_manager()->field_exists('local_ulpgccore_categories', 'degree')) {
            $options[] = 'coursecatdegree';
        }
        $options = array_combine($options, $options);
        foreach($options as $key => $option) {
            $options[$key] = get_string($option, 'examregistrar');
        }
        $mform->addElement('select', 'programme', get_string('genassignprogramme', 'examregistrar'), $options);
        $mform->addHelpButton('programme', 'genassignprogramme', 'examregistrar');
        $mform->setDefault('programme', 'coursecatid');

        $mform->addElement('selectyesno', 'updateexams', get_string('genupdateexams', 'examregistrar'));
        $mform->setDefault('updateexams', 1);
        $mform->addHelpButton('updateexams', 'genupdateexams', 'examregistrar');

        $mform->addElement('selectyesno', 'deleteexams', get_string('gendeleteexams', 'examregistrar'));
        $mform->setDefault('deleteexams', 0);
        $mform->addHelpButton('deleteexams', 'gendeleteexams', 'examregistrar');

        $options = array();
        $options['0'] = get_string('hidden', 'examregistrar');
        $options['1'] = get_string('visible');
        $options['2'] = get_string('synchvisible', 'examregistrar');
        $mform->addElement('select', 'examvisible', get_string('genexamvisible', 'examregistrar'), $options);
        $mform->setDefault('examvisible', 2);
        $mform->addHelpButton('examvisible', 'genexamvisible', 'examregistrar');

        $mform->addElement('header', 'headcoursesettings', get_string('coursesettings', 'tool_batchmanage'));

        $categories = make_categories_options();
        $catmenu = &$mform->addElement('select', 'coursecategories', get_string('coursecategories', 'tool_batchmanage'), $categories, 'size="12"');
        $catmenu->setMultiple(true);
        $mform->addRule('coursecategories', null, 'required');
        $mform->addHelpButton('coursecategories', 'coursecategories', 'tool_batchmanage');

        $options = array();
        $options['-1'] = get_string('all');
        $options['0'] = get_string('hidden', 'tool_batchmanage');
        $options['1'] = get_string('visible');
        $mform->addElement('select', 'coursevisible', get_string('coursevisible', 'tool_batchmanage'), $options);
        $mform->setDefault('coursevisible', -1);

        if($DB->get_manager()->field_exists('local_ulpgccore_course', 'term')) {
            $options = array();
            $options['-1'] = get_string('all');
            $options['0'] = get_string('term00', 'tool_batchmanage');
            $options['1'] = get_string('term01', 'tool_batchmanage');
            $options['2'] = get_string('term02', 'tool_batchmanage');
            $mform->addElement('select', 'courseterm', get_string('term', 'tool_batchmanage').': ', $options);
            $mform->setDefault('courseterm', -1);
        }

        if($DB->get_manager()->field_exists('local_ulpgccore_course', 'credits')) {
            $options = array();
            $options['-1'] = get_string('all');
            $options['-2'] = get_string('nonzero', 'tool_batchmanage');
            $sql = "SELECT DISTINCT credits
                                FROM {local_ulpgccore_course} WHERE credits IS NOT NULL ORDER BY credits ASC";
            $usedvals = $DB->get_records_sql($sql);
            if($usedvals) {
                foreach($usedvals as $key=>$value) {
                    $options["{$value->credits}"] = $value->credits;
                }
                $mform->addElement('select', 'coursecredit', get_string('credit', 'tool_batchmanage').': ', $options);
                $mform->setDefault('coursecredit', -1);
            }
        }

        if($DB->get_manager()->field_exists('local_ulpgccore_course', 'department')) {
            $options = array();
            $options['-1'] = get_string('all');
            $sql = "SELECT DISTINCT department
                                FROM {local_ulpgccore_course} WHERE department IS NOT NULL ORDER BY department ASC";
            $usedvals = $DB->get_records_sql($sql);
            if($usedvals) {
                foreach($usedvals as $key=>$value) {
                    $options["{$value->department}"] = $value->department;
                }
                $mform->addElement('select', 'coursedept', get_string('department', 'tool_batchmanage').': ', $options);
                $mform->setDefault('coursedept', -1);
            }
        }

        if($DB->get_manager()->field_exists('local_ulpgccore_course', 'ctype')) {
            $options = array();
            $options['all'] = get_string('all');
            $sql = "SELECT DISTINCT ctype
                                FROM {local_ulpgccore_course} WHERE ctype IS NOT NULL ORDER BY ctype ASC";
            $usedvals = $DB->get_records_sql($sql);
            if($usedvals) {
                foreach($usedvals as $key=>$value) {
                    $options["{$value->ctype}"] = $value->ctype;
                }
                $mform->addElement('select', 'coursectype', get_string('ctype', 'tool_batchmanage').': ', $options);
                $mform->setDefault('coursectype', 'all');
            }
        }

        $courseformats = get_plugin_list('format');
        $formcourseformats = array('all' => get_string('all'));
        foreach ($courseformats as $courseformat => $formatdir) {
            $formcourseformats[$courseformat] = get_string('pluginname', "format_$courseformat");
        }
        $mform->addElement('select', 'coursetoformat', get_string('format'), $formcourseformats);
        //$mform->setHelpButton('format', array('courseformats', get_string('courseformats')), true);
        $mform->setDefault('coursetoformat', 'all');

        $mform->addElement('text', 'coursetoshortnames', get_string('coursetoshortnames', 'tool_batchmanage'), array('size'=>'38'));
        $mform->setType('coursetoshortnames', PARAM_TEXT);
        $default = ($items) ? $items : '';
        $mform->setDefault('coursetoshortnames', $default);
        $mform->addHelpButton('coursetoshortnames', 'coursetoshortnames', 'tool_batchmanage');

        $mform->addElement('text', 'courseidnumber', get_string('courseidnumber', 'tool_batchmanage'), array('size'=>'40'));
        $mform->setType('courseidnumber', PARAM_TEXT);
        $mform->setDefault('courseidnumber', '');
        $mform->addHelpButton('courseidnumber', 'courseidnumber', 'tool_batchmanage');

        $mform->addElement('hidden', 'courses', '');
        $mform->setType('courses', PARAM_TEXT);

        $mform->addElement('hidden', 'examregid', $exreg);
        $mform->setType('examregid', PARAM_INT);

        $mform->addElement('hidden', 'edit', 'exams');
        $mform->setType('edit', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'action', 'generate');
        $mform->setType('action', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('savechanges'));

    }

}

class examregistrar_generateexams_confirm_form extends examregistrar_generateexams_form {

    function definition_after_data() {

        global $CFG, $DB;

        $mform =& $this->_form;
        $cmid = $this->_customdata['cmid'];
        $examreg = $this->_customdata['exreg'];
        $confirm = $this->_customdata['confirm'];
        $exreg = examregistrar_get_primaryid($examreg);

        if(is_array($confirm)) {
            $mform->hardFreezeAllVisibleExcept(array());
            $fields = array('generatemode', 'periods', 'assignperiod', 'programme', 'deleteexams', 'updateexams', 'examvisible',
                            'coursecategories', 'coursevisible', 'courseterm', 'coursecredit',
                            'coursedept', 'coursetoformat', 'coursetoshortnames', 'courseidnumber' );
            foreach($confirm as $key => $value) {
                if(in_array($key, $fields)) {
                    if(is_array($value)) {
                        foreach($value as $k=>$val) {
                        $mform->addElement('hidden', "__".$key."[$k]", $val);
                        $mform->setType('id', PARAM_RAW);
                        }
                    } else {
                        $mform->addElement('hidden', "__".$key, $value);
                        $mform->setType('id', PARAM_RAW);
                    }
                }
            }
            $mform->addElement('hidden', 'confirmed', 1);
            $mform->setType('confirmed', PARAM_INT);
            $save = get_string('generateexams', 'examregistrar');
        } else {
            $save = get_string('savechanges');
        }

        $mform->addElement('hidden', 'examregid', $exreg);
        $mform->setType('examregid', PARAM_INT);

        $mform->addElement('hidden', 'edit', 'exams');
        $mform->setType('edit', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'action', 'generate');
        $mform->setType('action', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);


        $this->add_action_buttons(true, get_string('generateexams', 'examregistrar'));

    }
}

class examregistrar_upload_examfile_form extends moodleform {

    function definition() {
        global $COURSE;

        $mform =& $this->_form;
        $cmid = $this->_customdata['id'];
        $tab = $this->_customdata['tab'];
        $examreg = $this->_customdata['exreg'];
        $baseparams = $this->_customdata['reviewparams'];
        $upload = $this->_customdata['upload'];
        $attempt = $this->_customdata['attempt'];
        $attempts = $this->_customdata['attempts'];
        $examdata = $this->_customdata['examdata'];

        $exreg = examregistrar_get_primaryid($examreg);

        $context = context_module::instance($cmid);
        $canmanageexams = has_capability('mod/examregistrar:manageexams',$context);

        $mform->addElement('header', 'uploadsettings', get_string('uploadsettings', 'examregistrar'));

        $mform->addElement('static', '', get_string('course'), $examdata->coursename );
        $mform->addElement('static', '', get_string('annualityitem', 'examregistrar'), $examdata->annuality);
        $mform->addElement('static', '', get_string('programme', 'examregistrar'), $examdata->programme);
        $mform->addElement('static', '', get_string('perioditem', 'examregistrar'), $examdata->period);
        $mform->addElement('static', '', get_string('scopeitem', 'examregistrar'), $examdata->examscope);
        $mform->addElement('static', '', get_string('examsessionitem', 'examregistrar'), $examdata->examsession);
        $mform->addElement('static', '', get_string('date'), $examdata->examdate);

        $attemptsmenu = array(0 => get_string('addattempt', 'examregistrar'));
        if($canmanageexams && $attempts) {
            foreach($attempts as $item) {
                $attemptsmenu[$item->attempt] = $item->name;
            }
        }
        $mform->addElement('select', 'attempt', get_string('attempt', 'examregistrar'), $attemptsmenu);
        $mform->addHelpButton('attempt', 'attempt', 'examregistrar');
        $mform->setDefault('attempt', $attempt);

        $mform->addElement('text', 'name', get_string('attemptname', 'examregistrar'), array('size'=>'20'));
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', '');
        $mform->addHelpButton('name', 'attemptname', 'examregistrar');

        $fileoptions = array('subdirs'=>0,
                                'maxbytes'=>$COURSE->maxbytes,
                                'accepted_types'=>'pdf ',
                                'maxfiles'=>1,
                                'return_types'=>FILE_INTERNAL);

        $mform->addElement('filepicker', 'uploadfileexam', get_string('examfile','examregistrar'), null, $fileoptions);
        $mform->addRule('uploadfileexam', get_string('uploadnofilefound'), 'required', null, 'client');
        $mform->addHelpButton('uploadfileexam', 'examfile', 'examregistrar');

        $mform->addElement('filepicker', 'uploadfileanswers', get_string('examfileanswers', 'examregistrar'), null, $fileoptions);
        $mform->addRule('uploadfileanswers', get_string('uploadnofilefound'), 'required', null, 'client');
        $mform->addHelpButton('uploadfileanswers', 'examfileanswers', 'examregistrar');


        $statusmenu = array(0 => get_string('status_created', 'examregistrar'));
        if($canmanageexams) {
            $statusmenu = examregistrar_examstatus_getmenu();
        }
        $mform->addElement('select', 'status', get_string('status', 'examregistrar'), $statusmenu);
        $mform->addHelpButton('status', 'status', 'examregistrar');
        $mform->setDefault('status', 0);

        $printmenu = array(0 => get_string('printdouble', 'examregistrar'),
                           1 => get_string('printsingle', 'examregistrar'),);
        $mform->addElement('select', 'printmode', get_string('printmode', 'examregistrar'), $printmenu);
        $mform->addHelpButton('printmode', 'printmode', 'examregistrar');
        $mform->setDefault('printmode', 0);




        foreach($baseparams as $param => $value) {
            $mform->addElement('hidden', $param, $value);
            $mform->setType($param, PARAM_ALPHANUMEXT);
        }

        $mform->addElement('hidden', 'upload', $upload);
        $mform->setType('upload', PARAM_INT);

        $mform->addElement('hidden', 'tab', $tab);
        $mform->setType('tab', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'id', $cmid);
        $mform->setType('id', PARAM_INT);


        $this->add_action_buttons(true, get_string('uploadexamfile', 'examregistrar'));
    }
}


class examregistrar_files_form extends moodleform {
    function definition() {
        $mform = $this->_form;

        $data    = $this->_customdata['data'];
        $options = $this->_customdata['options'];

        $mform->addElement('hidden', 'id', $data->id);
        $mform->setType('id', PARAM_INT);
        if(isset($data->tab)) {
            $mform->addElement('hidden', 'tab', $data->tab);
            $mform->setType('tab', PARAM_ALPHANUM);
        }
        if(isset($data->session)) {
            $mform->addElement('hidden', 'session', $data->session);
            $mform->setType('session', PARAM_INT);
        }
        if(isset($data->period)) {
            $mform->addElement('hidden', 'period', $data->period);
            $mform->setType('period', PARAM_INT);
        }
        if(isset($data->bookedsite)) {
            $mform->addElement('hidden', 'venue', $data->bookedsite);
            $mform->setType('venue', PARAM_INT);
        }
        if(isset($data->action)) {
            $mform->addElement('hidden', 'action', $data->action);
            $mform->setType('action', PARAM_ALPHANUMEXT);
        }
        if(isset($data->area)) {
            $mform->addElement('hidden', 'area', $data->area);
            $mform->setType('area', PARAM_ALPHANUMEXT);
        }
        if(isset($data->examfile)) {
            $mform->addElement('hidden', 'examf', $data->examfile);
            $mform->setType('examf', PARAM_INT);
        }
        if(isset($data->examfile)) {
            $mform->addElement('hidden', 'exam', $data->examfile);
            $mform->setType('exam', PARAM_INT);
        }



        $mform->addElement('filemanager', 'files_filemanager', get_string('files'), null, $options);
        $submit_string = get_string('savechanges');

        if(isset($data->area) && $data->area == 'sessionresponses') {
            $mform->addElement('static', 'files_help', '', get_string('responsefiles_help', 'examregistrar') );
            $mform->addElement('submit', 'deleteresponsefiles', get_string('deleteresponsefiles', 'examregistrar'));
        }


        $this->add_action_buttons(true, $submit_string);

        $this->set_data($data);
    }
}

class examregistrar_response_files_form extends moodleform {
    function definition() {
        $mform = $this->_form;

        $data    = $this->_customdata['data'];
        $options = $this->_customdata['options'];

        $mform->addElement('hidden', 'id', $data->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'tab', 'session');
        $mform->setType('tab', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'session', $data->session);
        $mform->setType('session', PARAM_INT);
        $mform->addElement('hidden', 'venue', $data->bookedsite);
        $mform->setType('venue', PARAM_INT);
        $mform->addElement('hidden', 'action', 'session_files');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'do', $data->area);
        $mform->setType('do', PARAM_ALPHANUMEXT);


        $mform->addElement('filemanager', 'files_filemanager', get_string('files'), null, $options);
        $submit_string = get_string('savechanges');

        if($data->area == 'responses') {
            $mform->addElement('static', 'files_help', '', get_string('responsefiles_help', 'examregistrar') );
            $mform->addElement('submit', 'deleteresponsefiles', get_string('deleteresponsefiles', 'examregistrar'));
        }


        $this->add_action_buttons(true, $submit_string);

        $this->set_data($data);
    }
}
    

    
class examregistrar_roomresponses_form extends moodleform {
    function definition() {
        $mform = $this->_form;

        $data    = $this->_customdata['data'];
        $options = $this->_customdata['options'];
        
        if(isset($data->session)) {
            $mform->addElement('hidden', 'session', $data->session);
            $mform->setType('session', PARAM_INT);
        }
        if(isset($data->bookedsite)) {
            $mform->addElement('hidden', 'venue', $data->bookedsite);
            $mform->setType('venue', PARAM_INT);
        }
        if(isset($data->room)) {
            $mform->addElement('hidden', 'room', $data->room);
            $mform->setType('room', PARAM_INT);
        }
        if(isset($data->action)) {
            $mform->addElement('hidden', 'action', $data->action);
            $mform->setType('action', PARAM_ALPHANUMEXT);
        }
        
        $fields = array('showing' => get_string('usershowing', 'examregistrar'), 
                        'taken' => get_string('usertaken', 'examregistrar'), 
                        'certified' => get_string('usercertified', 'examregistrar'),
                        );

        $users = examregistrar_get_session_venue_users($data->session, $data->bookedsite);
        $exams = array();
        $exam = new stdClass();
        $exam->shortname = '';
        $exam->fullname = '';
        $exam->allocated = 1;
        $exam->showing = 0;
        $exam->taken = 0;

        $mform->addElement('header', 'headeruserdata', get_string('headeruserdata', 'examregistrar'));
        $mform->addElement('advcheckbox', 'loadattendance', get_string('loadattendance', 'examregistrar'), 
                                                    get_string('loadattendance_explain', 'examregistrar')); 
        
        $mform->setDefault('loadattendance', 0);
       
        $userstatus = array(EXAM_RESPONSES_UNSENT,
                            EXAM_RESPONSES_SENT,
                            EXAM_RESPONSES_WAITING,
                            EXAM_RESPONSES_REJECTED,
                            EXAM_RESPONSES_COMPLETED,
                            EXAM_RESPONSES_VALIDATED,
                            );
                            
        foreach($userstatus as $status) {
            $userstatus[$status] = mod_examregistrar_renderer::get_responses_icon($status);
        }
        
        $stradd = get_string('useradd', 'examregistrar');
        
        foreach($users as $bid => $user) {
        
            if(!isset($exams[$user->examid])) {
                $exams[$user->examid] = clone $exam;
                $exams[$user->examid]->shortname = $user->shortname;
                $exams[$user->examid]->fullname = $user->fullname;
            } else {
                $exams[$user->examid]->allocated++;
            }
            if($user->showing) {
                $exams[$user->examid]->showing++;
            }
            if($user->taken) {
                $exams[$user->examid]->taken++;
            }
            
            $userattendance = array();
            $userattendance[] = $mform->createElement('static', 'exam', '', $user->shortname);
            $userattendance[] = $mform->createElement('advcheckbox', 'add', '', $stradd, array('group' => 'add'), array(0, $user->userid));
            $mform->disabledIf("userattendance[{$user->sid}][add]", "loadattendance", 'notchecked');
            foreach($fields as $field => $name) {
                $userattendance[] = $mform->createElement('advcheckbox', $field, '', $name, array('group' => $field));
                $mform->setDefault("userattendance[{$user->sid}][$field]", $user->$field);
                $mform->disabledIf("userattendance[{$user->sid}][$field]", "loadattendance", 'notchecked');
            }
            $userattendance[] = $mform->createElement('hidden', 'examid', $user->examid);
            $mform->setType("userattendance[{$user->sid}][examid]", PARAM_INT);
            
            $userattendance[] = $mform->createElement('static', '', '', $userstatus[$user->status]);
            $group = $mform->addGroup($userattendance, "userattendance[$user->sid]", fullname($user), ' ', true);
        }
        
        $allnonestr = get_string('selectallornone', 'form');
        $this->add_checkbox_controller('add', $stradd.' - '.$allnonestr );
        foreach($fields as $field => $name) {
            $this->add_checkbox_controller($field, $name.' - '.$allnonestr );
        }
        
        $statusmenu = array(EXAM_RESPONSES_SENT => get_string('status_sent', 'examregistrar'),
                            EXAM_RESPONSES_WAITING => get_string('status_waiting', 'examregistrar'),
                            EXAM_RESPONSES_COMPLETED => get_string('status_completed', 'examregistrar'),
                            );
        if($data->canreview) {
            $statusmenu[EXAM_RESPONSES_VALIDATED] = get_string('status_validated', 'examregistrar');
        }

        $mform->addElement('select', 'userstatus', get_string('status', 'examregistrar'), $statusmenu);
        $mform->addHelpButton('userstatus', 'responsestatus', 'examregistrar');
        $mform->disabledIf('userstatus', 'loadattendance', 'notchecked');
        
        $mform->addElement('header', 'headerexamsdata', get_string('headerexamsdata', 'examregistrar'));

        $size = array('size'=>'4');
        $labelsep = get_string('labelsep', 'langconfig');
        
        //    $name = ' '.$room->name.$labelsep;
          //  $userfiles[$rid] = $mform->createElement('text', "showing[$rid]", $name, $size);

        
        $examattendance = array();
        $examattendance[] = $mform->createElement('static', '', '', get_string('allocated', 'examregistrar'));
        $examattendance[] = $mform->createElement('static', '', '', get_string('taken', 'examregistrar'));
        $examattendance[] = $mform->createElement('static', '', '', get_string('status', 'examregistrar'));
        
        $group = $mform->addGroup($examattendance, "examattendancehead", '', ' &nbsp; ', true);
        
        ksort($exams);
        foreach($exams as $eid => $exam) { 
        
            $examattendance = array();
            $examattendance[] = $mform->createElement('static', ' ee ', ' dd ', ' &nbsp;&nbsp; &nbsp;&nbsp; '.$exam->allocated. ' &nbsp;&nbsp;  &nbsp;&nbsp;');
            $examattendance[] = $mform->createElement('text', 'taken', $name, $size);
            $mform->setType("examattendance[$eid][taken]", PARAM_INT);
            $mform->setDefault("examattendance[$eid][taken]", $exam->taken);
            //$mform->disabledIf("showing[$rid]", "roomdata[$rid]", 'notchecked');
            $examattendance[] = $mform->createElement('select', 'status', '', $statusmenu);
        
            $group = $mform->addGroup($examattendance, "examattendance[$eid]", $exam->shortname, ' &nbsp; ', true);
        }                
                        
        $mform->addElement('header', 'headerresponsefiles', get_string('headerresponsefiles', 'examregistrar'));
        
        $mform->addElement('filemanager', 'files_filemanager', get_string('files'), null, $options);

        $actionmenu = array('add' => get_string('add'), 'replace'=>get_string('delete')); 
        $mform->addElement('select', 'filesaction', get_string('filesaction', 'examregistrar'), $actionmenu);
        $mform->setDefault('filesaction', 'add');
        
        $mform->setExpanded('headerresponsefiles', true);
       
        $submit_string = get_string('savechanges');

        $this->add_action_buttons(true, $submit_string);
    
    }
    
    
}
    
class examregistrar_examresponses_form extends moodleform {
    function definition() {
        $mform = $this->_form;
//id=8669&tab=printexams&period=1&session=1&venue=0&term=0&searchname=&searchid=&programme=&sorting=shortname&order=ASC&user=2
        $data    = $this->_customdata['data'];
        $options = $this->_customdata['options'];
        
        if(isset($data->session)) {
            $mform->addElement('hidden', 'session', $data->session);
            $mform->setType('session', PARAM_INT);
        }
        if(isset($data->room)) {
            $mform->addElement('hidden', 'room', $data->room);
            $mform->setType('room', PARAM_INT);
        }
        if(isset($data->action)) {
            $mform->addElement('hidden', 'action', $data->action);
            $mform->setType('action', PARAM_ALPHANUMEXT);
        }
        if(isset($data->examfile)) {
            $mform->addElement('hidden', 'examfile', $data->examfile);
            $mform->setType('examfile', PARAM_INT);
        }
        if(isset($data->examid)) {
            $mform->addElement('hidden', 'examid', $data->examid);
            $mform->setType('examid', PARAM_INT);
        }
        /*
        if(isset($data->responseid)) {
            $mform->addElement('hidden', 'responseid', $data->responseid);
            $mform->setType('responseid', PARAM_INT);
        }
*/
        
        //print_object($data);
        
        $mform->addElement('header', 'headeruserdata', get_string('headeruserdata', 'examregistrar'));
        $mform->addElement('advcheckbox', 'loadattendance', get_string('loadattendance', 'examregistrar'), get_string('loadattendance_explain', 'examregistrar')); 
        $mform->setDefault('loadattendance', 0);
        
        $fields = array('showing' => get_string('usershowing', 'examregistrar'), 
                        'taken' => get_string('usertaken', 'examregistrar'), 
                        'certified' => get_string('usercertified', 'examregistrar'),
                        );
        
        if($data->room && isset($data->rooms[$data->room])) {
            $data->rooms = array($data->room => $data->rooms[$data->room]); 
        }
        
        $multiplerooms = (count($data->rooms) > 1);
       
        $userstatus = array(EXAM_RESPONSES_UNSENT,
                            EXAM_RESPONSES_SENT,
                            EXAM_RESPONSES_WAITING,
                            EXAM_RESPONSES_REJECTED,
                            EXAM_RESPONSES_COMPLETED,
                            EXAM_RESPONSES_VALIDATED,
                            );
                            
        foreach($userstatus as $status) {
            $userstatus[$status] = mod_examregistrar_renderer::get_responses_icon($status);
        }
        
        $stradd = get_string('useradd', 'examregistrar');
        foreach($data->users as $bid => $user) {
            if(!isset($data->rooms[$user->roomid])) {
                continue;
            }
            $userattendance = array();
            $userattendance[] = $mform->createElement('advcheckbox', 'add', '', $stradd, array('group' => 'add'), array(0, $user->userid));
            $mform->disabledIf("userattendance[{$user->sid}][add]", "loadattendance", 'notchecked');
            foreach($fields as $field => $name) {
                $userattendance[] = $mform->createElement('advcheckbox', $field, '', $name, array('group' => $field));
                $mform->setDefault("userattendance[{$user->sid}][$field]", $user->$field);
                $mform->disabledIf("userattendance[{$user->sid}][$field]", "loadattendance", 'notchecked');
            }
            
            $userattendance[] = $mform->createElement('static', '', '', $userstatus[$user->status]);
            
            if(!$data->bookedsite || $multiplerooms) {
                $userattendance[] = $mform->createElement('static', '', '', ' &nbsp;  &nbsp;  '.$data->rooms[$user->roomid]->name);
            }

            $group = $mform->addGroup($userattendance, "userattendance[$user->sid]", fullname($user), ' ', true);
        }
        
        $allnonestr = get_string('selectallornone', 'form');
        $this->add_checkbox_controller('add', $stradd.' - '.$allnonestr );
        foreach($fields as $field => $name) {
            $this->add_checkbox_controller($field, $name.' - '.$allnonestr );
        }
        
        $statusmenu = array(EXAM_RESPONSES_SENT => get_string('status_sent', 'examregistrar'),
                            EXAM_RESPONSES_WAITING => get_string('status_waiting', 'examregistrar'),
                            EXAM_RESPONSES_COMPLETED => get_string('status_completed', 'examregistrar'),
                            );
        if($data->canreview) {
            $statusmenu[EXAM_RESPONSES_VALIDATED] = get_string('status_validated', 'examregistrar');
        }

        $mform->addElement('select', 'userstatus', get_string('status', 'examregistrar'), $statusmenu);
        $mform->addHelpButton('userstatus', 'responsestatus', 'examregistrar');
        $mform->disabledIf('userstatus', 'loadattendance', 'notchecked');
        
        $mform->setExpanded('headeruserdata', false);
       
        if($multiplerooms) {
            $mform->addElement('header', 'headerroomsdata', get_string('headerroomsdata', 'examregistrar'));
        } else {
            $mform->addElement('header', 'headerresponsefiles', get_string('headerresponsefiles', 'examregistrar'));
        }

        $roomnames = array();
        foreach($data->rooms as $rid => $room) {
            $mform->addElement('hidden', "rooms[$rid]", $room->allocated);
            $mform->setType("rooms[$rid]", PARAM_INT);
             if(isset($data->session)) {
            $mform->addElement('hidden', 'session', $data->session);
            $mform->setType('session', PARAM_INT);
        }       $roomnames[] = ' '.$room->name.': ';
        }
        
        $userfiles = array();
        foreach($data->rooms as $rid => $room) {
            $allocated = get_string('numsuffix', 'examregistrar', $room->allocated);
            $userfiles[] = $mform->createElement('advcheckbox', "roomdata[$rid]", '', $room->name.$allocated, array('group' => 'rooms'), array(0, $room->allocated));
        }
        $group = $mform->addGroup($userfiles, "roomattendance_group",  get_string('loadroomattendance', 'examregistrar'), '', false);
        
        $userfiles = array();
        $userfiles[] = $mform->createElement('static', $rid, '', '');
        $rules = array();
        $size = array('size'=>'2');
        $labelsep = get_string('labelsep', 'langconfig');
        foreach($data->rooms as $rid => $room) {
            $name = ' '.$room->name.$labelsep;
            $userfiles[$rid] = $mform->createElement('text', "showing[$rid]", $name, $size);
            $mform->setType("showing[$rid]", PARAM_INT);
            $mform->setDefault("showing[$rid]", $data->rooms[$rid]->showing);
            $mform->disabledIf("showing[$rid]", "roomdata[$rid]", 'notchecked');
            $rules["showing[$rid]"] = array(array(null, 'numeric', null, 'client'));
        }
        //$userfiles[] = $mform->createElement('static', $rid, '', '');
        $group = $mform->addGroup($userfiles, 'showing_group',  get_string('usershowing', 'examregistrar'), '', false);
        $mform->addHelpButton('showing_group', 'usershowing', 'examregistrar');
        $mform->addGroupRule('showing_group', $rules);
        
        $userfiles = array();
        $userfiles[] = $mform->createElement('static', $rid, '', '');
        $rules = array();
        foreach($data->rooms as $rid => $room) {
            $name = ' '.$room->name.$labelsep;
            $userfiles[] = $mform->createElement('text', "taken[$rid]", $name, $size);
            $mform->setType("taken[$rid]", PARAM_INT);
            $mform->setDefault("taken[$rid]", $data->rooms[$rid]->taken);
            $mform->disabledIf("taken[$rid]", "roomdata[$rid]", 'notchecked');
            $rules["taken[$rid]"] = array(array(null, 'numeric', null, 'client'));
        }
        //$userfiles[] = $mform->createElement('static', $rid, '', '');
        $group = $mform->addGroup($userfiles, 'taken_group',  get_string('usertaken', 'examregistrar'), '', false);
        $mform->addHelpButton('taken_group', 'usertaken', 'examregistrar');
        $mform->addGroupRule('taken_group', $rules);
        
        $userfiles = array();
        foreach($data->rooms as $rid => $room) {
            $name = $name = ' '.$room->name.$labelsep;
            $userfiles[] = $mform->createElement('select', $rid, $name, $statusmenu);
            $mform->disabledIf("roomstatus[$rid]", "roomdata[$rid]", 'notchecked');
            $mform->setDefault("roomstatus[$rid]", $data->rooms[$rid]->status);
        }
        $group = $mform->addGroup($userfiles, 'roomstatus',  get_string('status', 'examregistrar'), '', true);
        
        if($multiplerooms) {
            $mform->setExpanded('headerroomsdata', false);
            $mform->addElement('header', 'headerresponsefiles', get_string('headerresponsefiles', 'examregistrar'));
        }

        $mform->addElement('filemanager', 'files_filemanager', get_string('files'), null, $options);
        //$mform->disabledIf('files_filemanager', 'loadfiles', 'notchecked');
        
        $mform->setExpanded('headerresponsefiles', true);
        
        if($multiplerooms) {
            $name = "roomdata[{$data->bookedsite}]";
            $allocated = 0;
            foreach($data->rooms as $rid => $room) {
                $allocated += $room->allocated;
            }
            
            $mform->addElement('advcheckbox', $name, get_string('loadsitedata', 'examregistrar'), get_string('loadsitedata_explain', 'examregistrar'), array(), array(0, $allocated)); 
            $mform->setDefault($name, 0);
            foreach($data->rooms as $rid => $room) {
                $mform->disabledIf($name, "roomdata[$rid]", 'checked');
            }
            
            unset($fields['certified']);
            foreach($fields as $field => $name) {
                $fname = $field.'['.$data->bookedsite.']';
                $mform->addElement('text', $fname, $name, $size);
                $mform->setType($fname, PARAM_INT);
                $mform->addRule($fname, null, 'numeric', null, 'client');
                $mform->addHelpButton($fname, 'user'.$field, 'examregistrar');
                $mform->disabledIf($fname, "roomdata[{$data->bookedsite}]", 'notchecked');
                foreach($data->rooms as $rid => $room) {
                    $mform->disabledIf($fname, "roomdata[$rid]", 'checked');
                }
            }

//            $statusmenu = array(0 => 'unconfirmed', 1=>'confirmed', 2=>'reviewed');
            $mform->addElement('select', "roomstatus[{$data->bookedsite}]", get_string('status', 'examregistrar'), $statusmenu);
        }
        
        $submit_string = get_string('savechanges');

        $this->add_action_buttons(true, $submit_string);
    }
    
    
    function validation($data, $files) {
        
        $errors = parent::validation($data, $files);
        $rooms = $data['rooms'];
        foreach(array('showing', 'taken') as $field) {
            foreach($rooms as $rid => $allocated) {
                $error = array();            
                if(isset($data[$field][$rid])) { 
                    if($data[$field][$rid] < 1) { 
                        //$errors["$field[$rid]"] = get_string('error_nonzero', 'examregistrar');
                        $error[] = get_string('error_nonzero', 'examregistrar'); 
                        //$errors[$field.'_group'] = get_string('error_nonzero', 'examregistrar'); 
                    }
                    if($data[$field][$rid] > $allocated) { 
                        //$errors[$field.'_group'] = get_string('error_lessthan', 'examregistrar', $allocated);
                        $error[] =  get_string('error_lessthan', 'examregistrar', $allocated); 
                    }
                }
            }
            if($error) {
                $errors[$field.'_group'] = implode('; ', $error);
            }
        }
        
        return $errors;
    }
    
}


class examregistrar_confirmresponses_form extends moodleform {
    function definition() {
        $mform = $this->_form;

        $data    = $this->_customdata['data'];
        $options = $this->_customdata['options'];

        if(isset($data->session)) {
            $mform->addElement('hidden', 'session', $data->session);
            $mform->setType('session', PARAM_INT);
        }
        if(isset($data->action)) {
            $mform->addElement('hidden', 'action', $data->action);
            $mform->setType('action', PARAM_ALPHANUMEXT);
        }
        if(isset($data->examfile)) {
            $mform->addElement('hidden', 'examfile', $data->examfile);
            $mform->setType('examfile', PARAM_INT);
        }
        if(isset($data->examfile)) {
            $mform->addElement('hidden', 'examid', $data->examid);
            $mform->setType('examid', PARAM_INT);
        }

        //print_object($data);
        //print_object("examregistrar_confirmresponses_form");
        
        
        $mform->addElement('header', 'headeruserdata', get_string('headeruserdata', 'examregistrar'));
        $mform->addElement('advcheckbox', 'loadattendance', get_string('loadattendance', 'examregistrar'), get_string('loadattendance_explain', 'examregistrar')); 
        $mform->setDefault('loadattendance', 0);
        
        $fields = array('showing' => get_string('usershowing', 'examregistrar'), 
                        'taken' => get_string('usertaken', 'examregistrar'), 
                        'certified' => get_string('usercertified', 'examregistrar'),
                        );
        
        $userstatus = array(EXAM_RESPONSES_UNSENT,
                            EXAM_RESPONSES_SENT,
                            EXAM_RESPONSES_WAITING,
                            EXAM_RESPONSES_REJECTED,
                            EXAM_RESPONSES_COMPLETED,
                            EXAM_RESPONSES_VALIDATED,
                            );
                            
        foreach($userstatus as $status) {
            $userstatus[$status] = mod_examregistrar_renderer::get_responses_icon($status);
        }
        
        $strset = get_string('yes');
        $strunset = get_string('no');
        foreach($data->users as $bid => $user) {
            if(!isset($data->rooms[$user->roomid])) {
                continue;
            }
            
            $status = '';
            foreach($fields as $field => $name) {
                $title = $user->$field ? $strset : $strunset;
                $title = $name.' '.$strset;
                $icon = $user->$field ? 'check-square-o' : 'square-o';
                $icon = html_writer::tag('i', $name, array('class' => "fa fa-$icon ",
                                                'title' => $title,
                                                'aria-label' => $title,
                                                ));
                $status .= ' &nbsp; '.$icon;
            }
            
            
            
            
            $userdata = $userstatus[$user->status].' &nbsp; '.$status.' &nbsp; '.$data->rooms[$user->roomid]->name;
            $mform->addElement('advcheckbox', "userattendance[{$user->sid}]", fullname($user), $userdata, array('group' => 'ur'.$user->roomid), array(0, $user->userid));
            $mform->disabledIf("userattendance[{$user->sid}]", "loadattendance", 'notchecked');
        }
        $allnonestr = get_string('selectallornone', 'form');
        foreach($data->rooms as $rid => $room) {
            if($rid) {
                $this->add_checkbox_controller('ur'.$rid, $room->name.' - '.$allnonestr );
            }
        }
        
        $statusmenu = array(EXAM_RESPONSES_SENT => get_string('status_sent', 'examregistrar'),
                            EXAM_RESPONSES_WAITING => get_string('status_waiting', 'examregistrar'),
                            EXAM_RESPONSES_COMPLETED => get_string('status_completed', 'examregistrar'),
                            );
        if($data->canreview) {
            $statusmenu[EXAM_RESPONSES_REJECTED] = get_string('status_rejected', 'examregistrar');
            $statusmenu[EXAM_RESPONSES_VALIDATED] = get_string('status_validated', 'examregistrar');
        }

        $mform->addElement('select', 'userstatus', get_string('status', 'examregistrar'), $statusmenu);
        $mform->addHelpButton('userstatus', 'responsestatus', 'examregistrar');
        $mform->disabledIf('userstatus', 'loadattendance', 'notchecked');
        
        $mform->setExpanded('headeruserdata', false);
       
        $mform->addElement('header', 'headerroomsdata', get_string('headerroomsdata', 'examregistrar'));
        
        if(isset($data->rooms[0]));
        $allocated = 0;
        foreach($data->rooms as $rid => $room) {
            if($rid) {
                $allocated += $room->allocated;
            }
        }
        $data->rooms[0]->allocated = $allocated;
        
        $roomdata = array();
        foreach($data->rooms as $rid => $room) {
            $allocated = get_string('numsuffix', 'examregistrar', $room->allocated);
            $roomdata[] = $mform->createElement('advcheckbox', "roomdata[$rid]", '', $room->name.$allocated, array('group' => 'rooms'), array(0, $room->allocated));
            $mform->addElement('hidden', "response[$rid]", $room->responseid); 
            $mform->setType("response[$rid]", PARAM_INT);
        }
        $group = $mform->addGroup($roomdata, "roomattendance_group",  get_string('loadroomattendance', 'examregistrar'), '', false);

        $fs = get_file_storage(); 
        $context = context_course::instance($data->courseid);
        $roomdata = array();
        foreach($data->rooms as $rid => $room) {
            $files = '';
            //$path = $rid ? $rid : '';
            //$type = array('examresponses', $path);
            if($files = $fs->get_directory_files($context->id, 'mod_examregistrar', 'examresponses',  $room->responseid, '/', false, false)) {
                //print_object($files);
                foreach($files as $key => $file) {
                    $fname = $file->get_filename();
                    $url = examregistrar_file_encode_url($context->id, $room->responseid, 'examresponses',$fname);
                    $files[$key] = html_writer::link($url, $fname);
                }
            
                $files = html_writer::alist($files, array('class' => 'files'));
            } else {
                $files = '';
            }
            $roomdata[] = $mform->createElement('static', $rid, ' ', html_writer::span($room->name.' '.$files, 'files'));
        }
        $group = $mform->addGroup($roomdata, 'files_group',  get_string('files'), '', true);  
        
        $roomdata = array();
        $rules = array();
        $size = array('size'=>'5');
        $labelsep = get_string('labelsep', 'langconfig');
        foreach($data->rooms as $rid => $room) {
            $name = $name = ' '.$room->name.$labelsep;
            $roomdata[] = $mform->createElement('text', "showing[$rid]", $name, $size);
            $mform->setType("showing[$rid]", PARAM_INT);
            $mform->setDefault("showing[$rid]", $data->rooms[$rid]->showing);
            $mform->disabledIf("showing[$rid]", "roomdata[$rid]", 'notchecked');
            $rules["showing[$rid]"] = array(array(null, 'numeric', null, 'client'));
        }
        $group = $mform->addGroup($roomdata, 'showing_group',  get_string('usershowing', 'examregistrar'), '', false);
        $mform->addHelpButton('showing_group', 'usershowing', 'examregistrar');
        $mform->addGroupRule('showing_group', $rules);
        
        $roomdata = array();
        $rules = array();
        foreach($data->rooms as $rid => $room) {
            $name = $name = ' '.$room->name.$labelsep;
            $roomdata[] = $mform->createElement('text', "taken[$rid]", $name, $size);
            $mform->setType("taken[$rid]", PARAM_INT);
            $mform->setDefault("taken[$rid]", $data->rooms[$rid]->taken);
            $mform->disabledIf("taken[$rid]", "roomdata[$rid]", 'notchecked');
            $rules["taken[$rid]"] = array(array(null, 'numeric', null, 'client'));
        }
        $group = $mform->addGroup($roomdata, 'taken_group',  get_string('usertaken', 'examregistrar'), '', false);
        $mform->addHelpButton('taken_group', 'usertaken', 'examregistrar');
        $mform->addGroupRule('taken_group', $rules);
        
        $roomdata = array();
        foreach($data->rooms as $rid => $room) {
            $name = $name = ' '.$room->name.$labelsep;
            $roomdata[] = $mform->createElement('select', $rid, $name, $statusmenu);
            $mform->setDefault("taken[$rid]", $data->rooms[$rid]->taken);
            $mform->disabledIf("roomstatus[$rid]", "roomdata[$rid]", 'notchecked');
            $mform->setDefault("roomstatus[$rid]", $data->rooms[$rid]->status);
        }
        $group = $mform->addGroup($roomdata, 'roomstatus',  get_string('status', 'examregistrar'), '', true);
        
        $submit_string = get_string('savechanges');

        $this->add_action_buttons(true, $submit_string);

    }
    
    
    function validation($data, $files) {
        
        $errors = parent::validation($data, $files);
        /*
        $rooms = $data['rooms'];
        foreach(array('showing', 'taken') as $field) {
            foreach($rooms as $rid => $allocated) {
                $error = array();            
                if(isset($data[$field][$rid])) { 
                    if($data[$field][$rid] < 1) { 
                        //$errors["$field[$rid]"] = get_string('error_nonzero', 'examregistrar');
                        $error[] = get_string('error_nonzero', 'examregistrar'); 
                        //$errors[$field.'_group'] = get_string('error_nonzero', 'examregistrar'); 
                    }
                    if($data[$field][$rid] > $allocated) { 
                        //$errors[$field.'_group'] = get_string('error_lessthan', 'examregistrar', $allocated);
                        $error[] =  get_string('error_lessthan', 'examregistrar', $allocated); 
                    }
                }
            }
            if($error) {
                $errors[$field.'_group'] = implode('; ', $error);
            }
        }
        */
        return $errors;
    }
    
}


