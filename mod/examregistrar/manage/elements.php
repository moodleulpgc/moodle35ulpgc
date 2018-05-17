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
 * Prints the general elements management interface of an instance of examregistrar
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// this file cannot be used alone, int must be included in a page-displaying script

defined('MOODLE_INTERNAL') || die;

require_capability('mod/examregistrar:editelements',$context);

$baseurl = new moodle_url('/mod/examregistrar/manage.php', array('id'=>$cm->id,'edit'=>$edit));

/// filter form parameters

$elementtype  = optional_param('etype', '', PARAM_ALPHANUMEXT);
$params = array('id'=>$cm->id, 'edit'=>$edit,
                      'etype' => $elementtype);

$manageurl = new moodle_url($baseurl, $params);

/// Print heading & filter
if (!$table->is_downloading()) {
    echo $OUTPUT->heading(get_string('edit'.$edit, 'examregistrar'));
    echo '<div class="examregistrarelementsform">';
    $typemenu = array(0 => get_string('any'));
    foreach($EXAMREGISTRAR_ELEMENTTYPES as $type) {
        $typemenu[$type] = get_string($type, 'examregistrar');
    }
    $label = get_string('elementtypeselect', 'examregistrar');
    $select = new single_select($baseurl, 'etype', $typemenu, $elementtype,  null, 'elementform');
    $select->set_label($label.':&nbsp;');
    echo $OUTPUT->render($select);
    echo '</div>';

    $url = new moodle_url($baseurl, array('item'=>-1));
    echo $OUTPUT->heading(html_writer::link($url, get_string('add'.$itemname, 'examregistrar')));
}

$tablecolumns = array('checkbox', 'type', 'name', 'idnumber', 'value', 'action');
$tableheaders = array('&nbsp;',
                        get_string('elementtype', 'examregistrar'),
                        get_string('itemname', 'examregistrar'),
                        get_string('idnumber', 'examregistrar'),
                        get_string('elementvalue', 'examregistrar'),
                        get_string('action'),
                        );
if ($table->is_downloading()) {
    $tablecolumns = array_diff($tablecolumns, array('checkbox', 'action'));
    $tableheaders = array_diff($tableheaders, array('&nbsp;', get_string('action')));
}

$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl($manageurl->out(false));
$table->set_wrapformurl($manageurl);

$actionsmenu = array('show' => get_string('show'),
                     'hide' => get_string('hide'),
                     'delete' => get_string('delete'),
                     );
$table->set_actionsmenu($actionsmenu);

$table->sortable(true, 'name', SORT_ASC);
if (!$table->is_downloading()) {
    $table->no_sorting('checkbox');
    $table->no_sorting('action');
}
$table->set_attribute('id', 'examregistrar_'.$edit.$examregistrar->id);
$table->set_attribute('cellspacing', '0');
$table->set_attribute('class', 'flexible generaltable examregmanagementtable');

$table->setup();

$select = ' examregid = :examregid ';
$params = array('examregid' => $examregprimaryid);
if($elementtype) {
    $select .= ' AND type = :type ';
    $params['type'] = $elementtype;
}

$totalcount = $DB->count_records_select('examregistrar_'.$edit, $select, $params);

$table->initialbars(false);
$table->pagesize($perpage, $totalcount);

if ($table->get_sql_sort()) {
    $sort = $table->get_sql_sort();
} else {
    $sort = '';
}

$stredit   = get_string('edit');
$strdelete = get_string('delete');

$elements = $DB->get_records_select('examregistrar_'.$edit, $select, $params, $sort, '*', $table->get_page_start(), $table->get_page_size());
if($elements) {
    foreach($elements as $element) {
        $data = array();
        if (!$table->is_downloading()) {
            $data[] = $table->col_checkbox($element);
        }
        $data[] = get_string($element->type, 'examregistrar');
        $data[] = $table->col_formatitem($element->name, '');
        $data[] = $table->col_formatitem('', $element->idnumber, '');
        $data[] = $element->value;
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

        if (!$table->is_downloading()) {
            $buttons = array();
            $url = new moodle_url($baseurl, array('show'=>$visible));
            $buttons[] = html_writer::link($url, html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/'.$visicon), 'alt'=>$strvisible, 'class'=>'iconsmall')), array('title'=>$strvisible));
            $url = new moodle_url($baseurl, array('item'=>$element->id));
            $buttons[] = html_writer::link($url, html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'), 'alt'=>$stredit, 'class'=>'iconsmall')), array('title'=>$stredit));
            $url = new moodle_url($baseurl, array('del'=>$element->id));
            $buttons[] = html_writer::link($url, html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'), 'alt'=>$strdelete, 'class'=>'iconsmall')), array('title'=>$strdelete));
            $data[] = implode('&nbsp;&nbsp;', $buttons);
        }

        $table->add_data($data, $rowclass);
    }
    $table->finish_output();
} else {
    echo $OUTPUT->heading(get_string('nothingtodisplay'));
}


