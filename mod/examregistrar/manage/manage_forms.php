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
            if(get_config('local_ulpgccore')) {
                $degrees = $DB->get_records_list('local_ulpgccore_categories', 'categoryid', array_keys($categories), '', 'categoryid, degree');
                foreach($categories as $id =>$name) {
                    $key = $degrees[$id]->degree;
                    if($key) {
                        $programmes[$key] = $name;
                    }
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
