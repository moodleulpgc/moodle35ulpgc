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
 * Prints the management interface for the Locations table of an instance of examregistrar
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
$sel_parent  = optional_param('sparent', 0, PARAM_INT);

$params = array('id'=>$cm->id, 'edit' => $edit,
                      'slocationtype' => $sel_locationtype,
                      'sparent' => $sel_parent,
                      );

$manageurl = new moodle_url($baseurl, $params);

/// Print heading & filter
if (!$table->is_downloading()) {
    echo $OUTPUT->heading(get_string('edit'.$edit, 'examregistrar'));


    echo $OUTPUT->container_start('examregistrarmanagefilterform clearfix ');
        echo $OUTPUT->single_button($baseurl, get_string('clearfilter', 'examregistrar'), 'get', array('class'=>' clearfix '));

        echo '<form id="examregistrarperiodsform" action="'.$CFG->wwwroot.'/mod/examregistrar/manage.php" method="post">'."\n";
        echo '<input type="hidden" name="edit" value="'.$edit.'" />'."\n";
        echo '<input type="hidden" name="id" value="'.$cm->id.'" />'."\n";

        $menu = examregistrar_elements_getvaluesmenu($examregistrar, 'locationtypeitem', $examregprimaryid);
        echo html_writer::label(get_string('locationtypeitem', 'examregistrar').': ', 'sterm');
        echo html_writer::select($menu, "slocationtype", $sel_locationtype);
        echo ' &nbsp; ';

        //$parentmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'locations', 'locationitem', $examregprimaryid, 'choose');
        $parentmenu = examregistrar_get_potential_parents($examregistrar);
        echo html_writer::label(get_string('parent', 'examregistrar').': ', 'sparent');
        echo html_writer::select($parentmenu, "sparent", $sel_parent);
        echo ' &nbsp; ';

        echo '<input type="submit" value="'.get_string('filter', 'examregistrar').'" />'."\n";
        echo '</form>'."\n";
    echo $OUTPUT->container_end();

    $url = new moodle_url($baseurl, array('item'=>-1));
    echo $OUTPUT->heading(html_writer::link($url, get_string('add'.$itemname, 'examregistrar')));
}

$tablecolumns = array('checkbox', 'locationname', 'locationtypename', 'seats', 'parentname', 'address', 'staffers', 'action');
$tableheaders = array(html_writer::checkbox('selectall', 1, false, '', array('id'=>'selectall')),
                        get_string('locationitem', 'examregistrar'),
                        get_string('locationtypeitem', 'examregistrar'),
                        get_string('seats', 'examregistrar'),
                        get_string('parent', 'examregistrar'),
                        get_string('address', 'examregistrar'),
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
                     'setparent' => get_string('setparent', 'examregistrar')
                     );
$table->set_actionsmenu($actionsmenu);
//$parentmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'locations', 'locationitem', $examregprimaryid, 'choose');
//$sessionmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'examsessions', 'examsessionitem', $examregprimaryid, 'choose');
$label = html_writer::label(get_string('parent', 'examregistrar').': ', 'setparent');
$select =  html_writer::select($parentmenu, "setparent", 0);
$table->set_additionalfields('setparent', array($label.$select));
//$table->set_additionalfields('setsession', array($label.$select));

$table->sortable(true, 'annualityname, periodname', SORT_ASC);
$table->no_sorting('checkbox');
$table->no_sorting('action');
$table->no_sorting('address');
$table->no_sorting('staffers');

$table->set_attribute('id', 'examregistrar_'.$edit.$examregistrar->id);
$table->set_attribute('cellspacing', '0');
$table->set_attribute('class', 'flexible generaltable examregmanagementtable');

$table->setup();

    $select = "SELECT l.*, el.name AS locationname, el.idnumber AS locationidnumber,
                           elt.name AS locationtypename, elt.idnumber AS locationtypeidnumber,
                           ep.name AS parentname, ep.idnumber AS parentidnumber ";
    $count = "SELECT COUNT('x') ";
    $sql = "FROM {examregistrar_locations} l
                JOIN {examregistrar_elements} el ON l.examregid =  el.examregid AND el.type = 'locationitem' AND l.location = el.id
                JOIN {examregistrar_elements} elt ON l.examregid =  elt.examregid AND elt.type = 'locationtypeitem' AND l.locationtype = elt.id
                LEFT JOIN {examregistrar_locations} p ON l.examregid =  p.examregid AND l.parent = p.id
                LEFT JOIN {examregistrar_elements} ep ON p.examregid =  ep.examregid AND ep.type = 'locationitem' AND p.location = ep.id
            WHERE l.examregid = :examregid ";
    $params = array('examregid'=>$examregprimaryid);

    $where = '';
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

$staffurl = new moodle_url('/mod/examregistrar/manage/assignroomstaffers.php', $manageurl->params()+ array('action'=>'roomstaffers', 'role'=>0, 'session'=>0));

$elements = $DB->get_records_sql($select.$sql.$where.$sort, $params, $table->get_page_start(), $table->get_page_size());
if($elements) {
    foreach($elements as $element) {
        $data = array();
        $data[] = $table->col_checkbox($element);
        $data[] = $table->col_formatitem($element->locationname, $element->locationidnumber);
        $data[] = $table->col_formatitem($element->locationtypename, $element->locationtypeidnumber);
        $data[] = $element->seats;
        if($element->parent) {
            $data[] = $table->col_formatitem($element->parentname, $element->parentidnumber);
        } else {
            $data[] = get_string('none');
        }
        $data[] = format_text($element->address, $element->addressformat, array('filter'=>false, 'para'=>false));
        $staffers = examregistrar_get_room_staffers($element->id);
        $data[] = examregistrar_format_room_staffers($staffers, $manageurl, $examregprimaryid);
        $rowclass = '';
        if(!$element->visible) {
            $rowclass = 'dimmed_text';
        }

        $visible = -$element->id;
        $visicon = 'show';
        $strvisible = get_string('hide');
        if(!$element->visible) {
            foreach($data as $key => $value) {
                $data[$key] = html_writer::span($value, 'dimmed_text');
            }
            $visible = $element->id;
            $visicon = 'hide';
            $strvisible = get_string('show');
        }

        $action = '';
        if (!$table->is_downloading()) {
            $buttons = array();
            $manageurl->param('edit', 'locations');
            $url = new moodle_url($manageurl, array('show'=>$visible));
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/'.$visicon, $strvisible, 'moodle', array('class'=>'iconsmall', 'title'=>$strvisible)));
            $url = new moodle_url($manageurl, array('item'=>$element->id));
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/edit', $stredit, 'moodle', array('class'=>'iconsmall', 'title'=>$stredit)));
            $url = new moodle_url($manageurl, array('del'=>$element->id));
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete, 'moodle', array('class'=>'iconsmall', 'title'=>$strdelete)));
            $buttons[] = '&nbsp;&nbsp;';
            //$url = new moodle_url($manageurl, array('staffers'=>$element->id));
            //$buttons[] = html_writer::link($url, html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/assignroles'), 'alt'=>$strstaffers, 'class'=>'iconsmall')), array('title'=>$strstaffers));
            $staffurl->param('room', $element->id);
            $buttons[] = html_writer::link($staffurl, $OUTPUT->pix_icon('t/assignroles', $strstaffers, 'moodle', array('class'=>'iconsmall', 'title'=>$strstaffers)));
            $action = implode('&nbsp;&nbsp;', $buttons);
        }
        $data[] = $action;

        $table->add_data($data, $rowclass);
    }

    $table->finish_output();

} else {
    echo $OUTPUT->heading(get_string('nothingtodisplay'));
}


