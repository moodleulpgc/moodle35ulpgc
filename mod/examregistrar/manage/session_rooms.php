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
 * Prints the management interface for the Session rooms table of an instance of examregistrar
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// this file cannot be used alone, int must be included in a page-displaying script

defined('MOODLE_INTERNAL') || die;

require_capability('mod/examregistrar:managelocations',$context);

$baseurl = new moodle_url('/mod/examregistrar/manage.php', array('id'=>$cm->id,'edit'=>$edit));

/// filter form parameters

$sel_locationtype  = optional_param('slocationtype', '', PARAM_ALPHANUMEXT);
$sel_session  = optional_param('ssession', 0, PARAM_INT);
$sel_period  = optional_param('speriod', 0, PARAM_INT);
$sel_parent  = optional_param('sparent', 0, PARAM_INT);

$params = array('id'=>$cm->id, 'edit' => $edit,
                      'slocationtype' => $sel_locationtype,
                      'ssession' => $sel_session,
                      'sparent' => $sel_parent,
                      );

$manageurl = new moodle_url($baseurl, $params);

$annuality =  examregistrar_get_annuality($examregistrar);

/// Print heading & filter
if (!$table->is_downloading()) {
    echo $OUTPUT->heading(get_string('edit'.$edit, 'examregistrar'));


    echo $OUTPUT->container_start('examregistrarmanagefilterform clearfix ');
        echo $OUTPUT->single_button($baseurl, get_string('clearfilter', 'examregistrar'), 'get', array('class'=>' clearfix '));

        echo '<form id="examregistrarfilterform" action="'.$CFG->wwwroot.'/mod/examregistrar/manage.php" method="post">'."\n";
        echo '<input type="hidden" name="edit" value="'.$edit.'" />'."\n";
        echo '<input type="hidden" name="id" value="'.$cm->id.'" />'."\n";

        //$periodmenu = examregistrar_elements_getvaluesmenu($examregistrar, 'perioditem', $examregprimaryid);
        $periodmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'periods', 'perioditem', $examregprimaryid, 'choose');
        echo html_writer::label(get_string('perioditem', 'examregistrar').': ', 'speriod');
        echo html_writer::select($periodmenu, "speriod", $sel_period);
        echo ' &nbsp; ';

        $sessionmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'examsessions', 'examsessionitem', $examregprimaryid, 'choose');
        echo html_writer::label(get_string('examsessionitem', 'examregistrar').': ', 'ssession');
        echo html_writer::select($sessionmenu, "ssession", $sel_session);
        echo ' &nbsp; ';

        $menu = examregistrar_elements_getvaluesmenu($examregistrar, 'locationtypeitem', $examregprimaryid);
        echo html_writer::label(get_string('locationtypeitem', 'examregistrar').': ', 'slocationtype');
        echo html_writer::select($menu, "slocationtype", $sel_locationtype);
        echo ' &nbsp; ';

        $parentmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'locations', 'locationitem', $examregprimaryid, 'choose');
        echo html_writer::label(get_string('parent', 'examregistrar').': ', 'sparent');
        echo html_writer::select($parentmenu, "sparent", $sel_parent);
        echo ' &nbsp; ';

        echo '<input type="submit" value="'.get_string('filter', 'examregistrar').'" />'."\n";
        echo '</form>'."\n";
    echo $OUTPUT->container_end();

    $url = new moodle_url('/mod/examregistrar/manage/action.php', $params);
    $url->param('action', 'sessionrooms');
    //$url = new moodle_url($baseurl, array('item'=>-1));
    echo $OUTPUT->heading(html_writer::link($url, get_string('sessionrooms', 'examregistrar')));
}

$tablecolumns = array('checkbox', 'bookedsite', 'sessionname', 'examdate', 'locationname', 'locationtypename', 'seats', 'parentname', 'occupancy', 'staffers', 'action');
$tableheaders = array('&nbsp;',
                        get_string('venue', 'examregistrar'),
                        get_string('examsessionitem', 'examregistrar'),
                        get_string('examdate', 'examregistrar'),
                        get_string('locationitem', 'examregistrar'),
                        get_string('locationtypeitem', 'examregistrar'),
                        get_string('seats', 'examregistrar'),
                        get_string('parent', 'examregistrar'),
                        get_string('occupancy', 'examregistrar'),
                        get_string('staffers', 'examregistrar'),
                        get_string('action'),
                        );
$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl($manageurl->out(false));
$table->set_wrapformurl($manageurl);

$actionsmenu = array('delete' => get_string('delete'),
                     'hide' => get_string('hide'),
                     'show' => get_string('show'),
                     );
$table->set_actionsmenu($actionsmenu);
//$table->set_additionalfields('setsession', array($label.$select));

$table->sortable(true, 'sessionanme', SORT_ASC);
$table->no_sorting('checkbox');
$table->no_sorting('action');
$table->no_sorting('occupancy');
$table->no_sorting('address');
$table->no_sorting('staffers');

$table->set_attribute('id', 'examregistrar_'.$edit.$examregistrar->id);
$table->set_attribute('cellspacing', '0');
$table->set_attribute('class', 'flexible generaltable examregmanagementtable');

$table->setup();
    $select = "SELECT sr.*, l.examregid, es.name AS sessionname, es.idnumber AS sessionidnumber, s.examdate,
                            ebl.name AS venuename, ebl.idnumber AS venueidnumber,
                            el.name AS locationname, el.idnumber AS locationidnumber, l.locationtype, l.seats, l.parent,
                            elt.name AS locationtypename, elp.name AS parentname, elp.idnumber AS parentidnumber,
                            p.annuality, s.period, ep.name AS periodname, ep.idnumber AS periodidnumber  ";
    $count = "SELECT COUNT('x') ";
    $sql = "FROM {examregistrar_session_rooms} sr
                JOIN {examregistrar_examsessions} s ON sr.examsession = s.id
                JOIN {examregistrar_elements} es ON s.examregid =  es.examregid AND es.type = 'examsessionitem' AND s.examsession = es.id
                JOIN {examregistrar_periods} p ON s.examregid = p.examregid AND s.period = p.id
                JOIN {examregistrar_elements} ep ON p.examregid =  ep.examregid AND ep.type = 'perioditem' AND p.period = ep.id
                JOIN {examregistrar_locations} bl ON sr.bookedsite = bl.id
                JOIN {examregistrar_elements} ebl ON bl.examregid =  ebl.examregid AND ebl.type = 'locationitem' AND bl.location = ebl.id
                JOIN {examregistrar_locations} l ON sr.roomid = l.id
                JOIN {examregistrar_elements} el ON l.examregid =  el.examregid AND el.type = 'locationitem' AND l.location = el.id
                JOIN {examregistrar_elements} elt ON l.examregid =  elt.examregid AND elt.type = 'locationtypeitem' AND l.locationtype = elt.id
                LEFT JOIN {examregistrar_locations} lp ON l.examregid =  lp.examregid AND l.parent = lp.id
                LEFT JOIN {examregistrar_elements} elp ON p.examregid =  elp.examregid AND elp.type = 'locationitem' AND lp.location= elp.id
            WHERE l.examregid = :examregid  ";
    $params = array('examregid'=>$examregprimaryid);

    $where = '';
    if($sel_period) {
        $where .= ' AND s.period = :period ';
        $params['period'] = $sel_period;
    }

    if($sel_session) {
        $where .= ' AND sr.examsession = :examsession ';
        $params['examsession'] = $sel_session;
    }

    if($sel_locationtype) {
        $where .= ' AND l.locationtype = :locationtype ';
        $params['locationtype'] = $sel_locationtype;
    }
    if($sel_parent) {
        $where .= ' AND l.parent = :parent ';
        $params['parent'] = $sel_parent;
    }


$totalcount = $DB->count_records_sql($count.$sql.$where, $params);

$table->initialbars(false);
$table->pagesize($perpage, $totalcount);

if ($table->get_sql_sort()) {
    $sort = ' ORDER BY  '.$table->get_sql_sort();
} else {
    $sort = ' ORDER BY locationname ASC, locationtypename ASC ';
}

$stredit   = get_string('edit');
$strdelete = get_string('delete');
$strstaffers = get_string('roomstaffers', 'examregistrar');
$strseats = get_string('assignseats', 'examregistrar');

$elements = $DB->get_records_sql($select.$sql.$where.$sort, $params, $table->get_page_start(), $table->get_page_size());
if($elements) {
    foreach($elements as $element) {
        $data = array();
        $data[] = $table->col_checkbox($element);
        $data[] = $table->col_formatitem($element->venuename, $element->venueidnumber);
        $name = $table->col_formatitem($element->sessionname, $element->sessionidnumber);
        if(!$sel_period) {
            $name .= ' ['.$element->periodidnumber.']';
        }
        $data[] = $name;
        $data[] = userdate($element->examdate, get_string('strftimedaydate'));
        $data[] = $table->col_formatitem($element->locationname, $element->locationidnumber);
        $data[] = $table->col_formatitem($element->locationtypename, $element->locationtype);
        $data[] = $element->seats;
        if($element->parent) {
            $data[] = $table->col_formatitem($element->parentname, $element->parentidnumber);
        } else {
            $data[] = get_string('none');
        }
        $rowclass = '';
        if(!$element->available || ($annuality && ($annuality != $element->annuality))) {
            $rowclass = 'dimmed_text';
        }

        $occupancy = '';
        if($allocatedrooms = $DB->get_records('examregistrar_session_seats', array('examsession'=>$element->examsession, 'roomid'=>$element->roomid), 'id', '*', 0, 1)) {
            $room = reset($allocatedrooms);
            if($allocatedrooms = examregistrar_get_roomallocations_byroom(array('session'=>$element->examsession, 'bookedsite'=>$room->bookedsite, 'room'=>$element->roomid))){
                foreach($allocatedrooms as $room) {
                    $examlist = '';
                    $additionalslist = '';
                    if($room->set_additionals()) {
                        $items = array();
                        foreach($room->additionals as $exam) {
                            $head = $output->list_allocatedroomexam($exam, true);
                            $items[] = $head;
                        }
                        //$additionalslist = html_writer::alist($items, array('class'=>' additionalexam '));
                    }
                    if($room->exams) {
                        $items = array();
                        foreach($room->exams as $exam) {
                            $head = $output->list_allocatedroomexam($exam, true);
                            $items[] = $head;
                        }
                        if($additionalslist) {
                            $items[] = get_string('additionals', 'examregistrar').$additionalslist;
                        }
                        $examlist = html_writer::alist($items, array('class'=>' roomexamlist '));
                    }
                    $occupancy .= $examlist;

                }
            }
        }

        $data[] = $occupancy;
        $staffers = examregistrar_get_room_staffers($element->roomid, $element->examsession);
        $data[] = examregistrar_format_room_staffers($staffers, $manageurl, $examregprimaryid);
/*
        $visible = -$element->id;
        $visicon = 'show';
        $strvisible = get_string('hide');
        if(!$element->visible) {
            foreach($data as $key => $value) {
                $data[$key] = html_writer::span($value, 'hidden');
            }
            $visible = $element->id;
            $visicon = 'hide';
            $strvisible = get_string('show');
        }
*/
        $action = '';
        if (!$table->is_downloading()) {
            $buttons = array();
            /*
            $url = new moodle_url($manageurl, array('show'=>$visible));
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/'.$visicon, $strvisible, 'moodle', array('class'=>'iconsmall', 'title'=>$strvisible)));
            */
            $url = new moodle_url('/mod/examregistrar/manage/assignsessionrooms.php',
                                  array('id'=>$cm->id, 'action'=>'sessionrooms', 'edit'=>$edit, 'session'=>$element->examsession, 'room'=>$element->roomid, 'venue'=>$element->bookedsite));

            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/edit', $stredit, 'moodle', array('class'=>'iconsmall', 'title'=>$stredit)));
            $url = new moodle_url($manageurl, array('del'=>$element->id));
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete, 'moodle', array('class'=>'iconsmall', 'title'=>$strdelete)));
            $buttons[] = '<br />&nbsp;&nbsp;';
            $url = new moodle_url('/mod/examregistrar/manage/assignseats.php',
                                  array('id'=>$cm->id, 'edit'=>'session_rooms', 'session'=>$element->examsession, 'room'=>$element->roomid));
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('contextmenu', $strseats, 'mod_examregistrar', array('class'=>'iconsmall', 'title'=>$strseats)));

            //$buttons[] = '&nbsp;&nbsp;';
            $url = new moodle_url('/mod/examregistrar/manage/assignroomstaffers.php',
                                  array('id'=>$cm->id, 'action'=>'roomstaffers', 'edit'=>$edit, 'session'=>$element->examsession, 'room'=>$element->roomid));
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/assignroles', $strstaffers, 'moodle', array('class'=>'iconsmall', 'title'=>$strstaffers)));

            $action = implode('&nbsp;&nbsp;', $buttons);
        }
        $data[] = $action;

        $table->add_data($data, $rowclass);
    }

    $table->finish_output();

} else {
    echo $OUTPUT->heading(get_string('nothingtodisplay'));
}


