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
 * Prints the management interface for Periods table of an instance of examregistrar
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// this file cannot be used alone, int must be included in a page-displaying script

defined('MOODLE_INTERNAL') || die;

require_capability('mod/examregistrar:manageperiods',$context);

$baseurl = new moodle_url('/mod/examregistrar/manage.php', array('id'=>$cm->id,'edit'=>$edit));

/// filter form parameters

$sel_annuality  = optional_param('sannuality', '', PARAM_INT);
$sel_term  = optional_param('sterm', '', PARAM_INT);
$sel_periodtype  = optional_param('speriodtype', '', PARAM_INT);

$params = array('id'=>$cm->id, 'edit'=>$edit,
                      'sannuality' => $sel_annuality,
                      'sterm' => $sel_term,
                      'speriodtype' => $sel_periodtype);

$manageurl = new moodle_url($baseurl, $params);

$annuality =  examregistrar_get_annuality($examregistrar);

/// Print heading & filter
if (!$table->is_downloading()) {
    echo $output->heading(get_string('edit'.$edit, 'examregistrar'));


    echo $output->container_start('examregistrarmanagefilterform clearfix ');
        echo $output->single_button($baseurl, get_string('clearfilter', 'examregistrar'), 'get', array('class'=>' clearfix '));

        echo '<form id="examregistrarperiodsform" action="'.$CFG->wwwroot.'/mod/examregistrar/manage.php" method="post">'."\n";
        echo '<input type="hidden" name="edit" value="'.$edit.'" />'."\n";
        echo '<input type="hidden" name="id" value="'.$cm->id.'" />'."\n";

        $annualitymenu = examregistrar_elements_getvaluesmenu($examregistrar, 'annualityitem', $examregprimaryid);
        echo html_writer::label(get_string('annuality', 'examregistrar').': ', 'sannuality');
        echo html_writer::select($annualitymenu, "sannuality", $sel_annuality);
        echo ' &nbsp; ';

        $termmenu = examregistrar_elements_getvaluesmenu($examregistrar, 'termitem', $examregprimaryid);
        echo html_writer::label(get_string('termitem', 'examregistrar').': ', 'sterm');
        echo html_writer::select($termmenu, "sterm", $sel_term);
        echo ' &nbsp; ';

        $periodtypemenu = examregistrar_elements_getvaluesmenu($examregistrar, 'periodtypeitem', $examregprimaryid);
        echo html_writer::label(get_string('periodtypeitem', 'examregistrar').': ', 'speriod');
        echo html_writer::select($periodtypemenu, "speriodtype", $sel_periodtype);
        echo ' &nbsp; ';

        echo '<input type="submit" value="'.get_string('filter', 'examregistrar').'" />'."\n";
        echo '</form>'."\n";
    echo $output->container_end();

    $url = new moodle_url($baseurl, array('item'=>-1));
    echo $output->heading(html_writer::link($url, get_string('add'.$itemname, 'examregistrar')));
}

$tablecolumns = array('checkbox', 'annualityname', 'periodname', 'periodtypename', 'termname', 'calls', 'timestart', 'timeend', 'action');
$tableheaders = array('&nbsp;',
                        get_string('annualityitem', 'examregistrar'),
                        get_string('itemname', 'examregistrar'),
                        get_string('periodtypeitem', 'examregistrar'),
                        get_string('termitem', 'examregistrar'),
                        get_string('numcalls', 'examregistrar'),
                        get_string('timestart', 'examregistrar'),
                        get_string('timeend', 'examregistrar'),
                        get_string('action'),
                        );
$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl($manageurl->out(false));
$table->set_wrapformurl($manageurl);

$actionsmenu = array('show' => get_string('show'),
                     'hide' => get_string('hide'),
                     'delete' => get_string('delete'),
                     );
$table->set_actionsmenu($actionsmenu);

$table->sortable(true, 'annualityname, periodname', SORT_ASC);
$table->no_sorting('checkbox');
$table->no_sorting('action');

$table->set_attribute('id', 'examregistrar_'.$edit.$examregistrar->id);
$table->set_attribute('cellspacing', '0');
$table->set_attribute('class', 'flexible generaltable examregmanagementtable');

$table->setup();

    $select = "SELECT p.*,  ea.name AS annualityname, ea.idnumber AS annualityidnumber,
                            ep.name AS periodname, ep.idnumber AS periodidnumber,
                            ept.name AS periodtypename, ept.idnumber AS periodtypeidnumber,
                            et.name AS termname, et.idnumber AS termidnumber  ";
    $count = "SELECT COUNT('x') ";
    $sql = "FROM {examregistrar_periods} p
                JOIN {examregistrar_elements} ea ON ea.type = 'annualityitem' AND p.annuality = ea.id
                JOIN {examregistrar_elements} ep ON ep.type = 'perioditem' AND p.period = ep.id
                JOIN {examregistrar_elements} ept ON ept.type = 'periodtypeitem' AND p.periodtype = ept.id
                JOIN {examregistrar_elements} et ON et.type = 'termitem' AND p.term = et.id
            WHERE p.examregid = :examregid ";
    $params = array('examregid'=>$examregprimaryid);

    $where = '';
    if($sel_annuality) {
        $where .= ' AND p.annuality = :annuality ';
        $params['annuality'] = $sel_annuality;
    }
    if($sel_term) {
        $where .= ' AND p.term = :term ';
        $params['term'] = $sel_term;
    }
    if($sel_periodtype) {
        $where .= ' AND p.periodtype = :periodtype ';
        $params['periodtype'] = $sel_periodtype;
    }


$totalcount = $DB->count_records_sql($count.$sql.$where, $params, $table->get_page_start(), $table->get_page_size());

$table->initialbars(false);
$table->pagesize($perpage, $totalcount);

if ($table->get_sql_sort()) {
    $sort = ' ORDER BY  '.$table->get_sql_sort();
} else {
    $sort = ' ORDER BY annualityname ASC, periodname ASC ';
}

$stredit   = get_string('edit');
$strdelete = get_string('delete');

$elements = $DB->get_records_sql($select.$sql.$where.$sort, $params);
if($elements) {
    foreach($elements as $element) {
        $data = array();
        $data[] = $table->col_checkbox($element);
        $data[] = $table->col_formatitem($element->annualityname, $element->annualityidnumber);
        $data[] = $table->col_formatitem($element->periodname, $element->periodidnumber);
        $data[] = $table->col_formatitem($element->periodtypename, $element->periodtypeidnumber);
        $data[] = $table->col_formatitem($element->termname, $element->termidnumber);
        $data[] = $element->calls;
        $data[] = userdate($element->timestart, get_string('strftimedaydate'));
        $data[] = userdate($element->timeend, get_string('strftimedaydate'));
        $rowclass = '';
        if(!$element->visible || ($annuality && ($annuality != $element->annuality))) {
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
            $url = new moodle_url($manageurl, array('show'=>$visible));
            $buttons[] = html_writer::link($url, html_writer::empty_tag('img', array('src'=>$output->pix_url('t/'.$visicon), 'alt'=>$strvisible, 'class'=>'iconsmall')), array('title'=>$strvisible));
            $url = new moodle_url($manageurl, array('item'=>$element->id));
            $buttons[] = html_writer::link($url, html_writer::empty_tag('img', array('src'=>$output->pix_url('t/edit'), 'alt'=>$stredit, 'class'=>'iconsmall')), array('title'=>$stredit));
            $url = new moodle_url($manageurl, array('del'=>$element->id));
            $buttons[] = html_writer::link($url, html_writer::empty_tag('img', array('src'=>$output->pix_url('t/delete'), 'alt'=>$strdelete, 'class'=>'iconsmall')), array('title'=>$strdelete));
            $action = implode('&nbsp;&nbsp;', $buttons);
        }
        $data[] = $action;

        $table->add_data($data, $rowclass);
    }

    $table->finish_output();

} else {
    echo $output->heading(get_string('nothingtodisplay'));
}


