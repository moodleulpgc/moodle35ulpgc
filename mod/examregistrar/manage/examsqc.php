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
 * Prints the management interface for Exams table of an instance of examregistrar
 *
 * @package    mod_examregistrar
 * @copyright  2013 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// this file cannot be used alone, int must be included in a page-displaying script

defined('MOODLE_INTERNAL') || die;

require_capability('mod/examregistrar:manageexams',$context);

$baseurl = new moodle_url('/mod/examregistrar/manage.php', array('id'=>$cm->id,'edit'=>$edit));

$params = array('id'=>$cm->id, 'edit' => $edit, 'action'=>'qc');

$manageurl = new moodle_url($baseurl, $params);

$annuality = examregistrar_get_annuality($examregistrar);


/// Print heading

echo $output->heading(get_string('examsqc', 'examregistrar'));

add_to_log($course->id, 'examregistrar', "manage edit examqc", "manage.php?id={$cm->id}&edit=$edit&action=qc", $examregistrar->name, $cm->id);

$period   = optional_param('period', '', PARAM_INT);

/// Period selector

    echo $output->container_start('examregistrarfilterform clearfix ');
        $periodmenu = examregistrar_get_referenced_namesmenu($examregistrar, 'periods', 'perioditem', $examregprimaryid);
        $select = new single_select(new moodle_url($baseurl, array('action'=>'qc')), 'period', $periodmenu, $period);
        $select->set_label(get_string('perioditem', 'examregistrar'), array('class'=>'singleselect  filter'));
        $select->class .= ' filter ';
        echo $output->render($select);
    echo $output->container_end();

/// main part of interface


    echo $output->container('', 'clearfix');

if($period) {

    $period = $DB->get_record('examregistrar_periods', array('id'=>$period), '*', MUST_EXIST);
    $periodterm = $DB->get_field('examregistrar_elements', 'value', array('id'=>$period->term), MUST_EXIST);

    echo $output->container('', 'clearfix');

    echo $output->container_start('examregqualitycontrol clearfix ');


        $sqlcourses = "SELECT c.id, c.shortname, c.idnumber, c.fullname, uc.term, uc.credits, c.category, cc.name, cc.idnumber AS catidnumber, ucc.degree,
                (SELECT COUNT(a.course)
                        FROM {assign} a
                        JOIN {assign_plugin_config} apc ON a.id = apc.assignment AND apc.plugin = 'exam' AND apc.name = 'enabled' AND apc.value = 1
                        WHERE a.course = c.id
                        GROUP BY a.course ) AS examinstances
                FROM {course} c
                JOIN {course_categories} cc ON cc.id = c.category 
                LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid 
                LEFT JOIN {local_ulpgccore_categories} ucc ON cc.id = ucc.categoryid ";


        $order = " \nORDER BY degree ASC, shortname ASC, fullname ASC ";
        $examjoin = " \nLEFT JOIN {examregistrar_exams} e ON c.id = e.courseid AND e.examregid = :exregid AND e.period = :period AND e.annuality = :annuality AND e.visible = 1";

        if($periodterm == 1 || $periodterm == 2) {
            $where = " WHERE (((uc.term = :term) OR (uc.term > 2)) AND uc.credits > 0 ) AND e.id IS NULL ";
            $sql1 = $sqlcourses . $examjoin .$where;
            $params1 = array('exregid'=>$examregprimaryid, 'annuality'=>$annuality, 'period'=>$period->id, 'term'=>$periodterm);

            $examjoin = " \nLEFT JOIN (SELECT ee.id, ee.courseid, ee.examregid, ee.period, ee.annuality, es.value AS scopeterm
                            FROM {examregistrar_exams} ee
                            JOIN {examregistrar_elements} es ON es.examregid = ee.examregid AND es.type = 'scopeitem' AND es.id = ee.examscope
                            WHERE  ee.examregid = :exregid2 AND ee.period = :period2 AND ee.annuality = :annuality2 AND es.value = :term2 AND ee.visible = 1
                         ) e ON c.id = e.courseid ";
            $where = " WHERE ((uc.term = 0) AND uc.credits > 0 ) AND e.id IS NULL ";
            $sql2 = $sqlcourses . $examjoin .$where;
            $params2 = array('exregid2'=>$examregprimaryid, 'annuality2'=>$annuality, 'period2'=>$period->id, 'term2'=>$periodterm);

            $sql = "( $sql1 ) \n UNION \n ( $sql2 )";
            $params = $params1 + $params2;
        } else {
            $where = " WHERE (uc.credits > 0 ) AND e.id IS NULL ";
            $sql = $sqlcourses . $examjoin .$where;
            $params = array('exregid'=>$examregprimaryid, 'annuality'=>$annuality, 'period'=>$period->id);
        }

        $courses = $DB->get_records_sql($sql.$order, $params);

    print_collapsible_region_start('managesession', 'showhideperiodqcnoexamscourses', get_string('periodqcnoexamcourses', 'examregistrar', count($courses)),'periodgcnoexamscourses', true, false);

        if($courses) {

            $examtable = new html_table();
            $examtable->attributes = array('class'=>'flexible generaltable examregsessionroomstable' );
            $tableheaders = array(get_string('course'),
                                    get_string('programme', 'examregistrar'),
                                    get_string('termitem', 'examregistrar'),
                                    get_string('items', 'examregistrar'),
                                    get_string('action'),
                                    );
            $examtable->head = $tableheaders;
            $examtable->colclasses = array();

            $stradd = get_string('addexam', 'examregistrar');
            $addurl = new moodle_url($baseurl, array('action'=>'generate'));
            $iconadd = new pix_icon('t/add', $stradd, 'moodle', array('class'=>'iconsmall', 'title'=>$stradd));

            $items = array();
            foreach($courses as $course) {
                $cellcourse = $course->shortname.' - '.$course->fullname;
                $cellprogramme = $course->degree;
                $cellterm = $course->term;
                $cellinstances = $course->examinstances;
                $addurl->params(array('courses'=>$course->shortname));
                $items[] = $course->shortname;
                $cellaction = $output->action_icon($addurl, $iconadd, null, array('class'=>'managesesionactionlink'));
                $row = new html_table_row(array($cellcourse, $cellprogramme, $cellterm, $cellinstances, $cellaction));
                $examtable->data[] = $row;
            }

            echo html_writer::table($examtable);

            $addurl->params(array('courses'=>implode(',', $items)));
            $addexamslink = html_writer::link($addurl, get_string('addexams', 'examregistrar'));

            echo $output->heading($addexamslink,  4, 'managesesionactionlink');
        }
        unset($courses);

    print_collapsible_region_end(false);
    echo $output->container_end();

    echo $output->container_start('examregprintoperators clearfix ');

        $termwhere = '';
        $params = array('exregid'=>$examregprimaryid, 'annuality'=>$annuality, 'period'=>$period->id);
        if($periodterm == 1 || $periodterm == 2) {
            $termwhere = " AND ((c2.term = :term1) OR (c2.term = 0 AND es.value = :term2 )) ";
            $params = $params + array('term1'=>$periodterm, 'term2'=>$periodterm);
        }

        $sql = "SELECT e.*, es.value AS scopeterm,  c.shortname, c.fullname, uc.credits, uc.term
                FROM {examregistrar_exams} e
                JOIN {examregistrar_elements} es ON es.examregid = e.examregid AND es.type = 'scopeitem' AND es.id = e.examscope
                JOIN {course} c ON c.id = e.courseid
                LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid 
                WHERE e.examregid = :exregid AND e.annuality = :annuality AND e.period = :period AND e.visible = 1
                    AND NOT EXISTS (SELECT c2.courseid FROM {local_ulpgccore_course} c2 WHERE c2.courseid = e.courseid AND c2.credits > 0 $termwhere)
                ORDER BY e.programme ASC, c.shortname ASC, e.id ASC";

        $exams = $DB->get_records_sql($sql, $params);

    print_collapsible_region_start('managesession', 'showhideperiodqcnocourse', get_string('periodqcnocourse', 'examregistrar', count($exams)),'periodqcnocourse', true, false);

        if($exams) {
            $examtable = new html_table();
            $examtable->attributes = array('class'=>'flexible generaltable examregsessionroomstable' );
            $tableheaders = array(get_string('course'),
                                    get_string('programme', 'examregistrar'),
                                    get_string('termitem', 'examregistrar'),
                                    get_string('action'),
                                    );
            $examtable->head = $tableheaders;
            $examtable->colclasses = array();

            $stredit   = get_string('edit');
            $editurl = new moodle_url($baseurl, array('item'=>0));
            $iconedit = new pix_icon('t/edit', $stredit, 'moodle', array('class'=>'iconsmall', 'title'=>$stredit));

            $strdelete = get_string('delete');
            $delurl = new moodle_url($baseurl, array('del'=>0));
            $icondel = new pix_icon('t/delete', $strdelete, 'moodle', array('class'=>'iconsmall', 'title'=>$strdelete));

            foreach($exams as $exam) {
                $cellcourse = $exam->shortname.' - '.$exam->fullname;
                $cellprogramme = $exam->programme;
                $cellterm = $exam->term ? $exam->term : $exam->scopeterm.' (A)' ;
                $editurl->params(array('item'=>$exam->id));
                $delurl->params(array('del'=>$exam->id));
                $cellaction = $output->action_icon($editurl, $iconedit, null, array('class'=>'managesesionactionlink'));
                $cellaction .= '&nbsp; &nbsp;'.$output->action_icon($delurl, $icondel, null, array('class'=>'managesesionactionlink'));
                $row = new html_table_row(array($cellcourse, $cellprogramme, $cellterm, $cellaction));
                $examtable->data[] = $row;
            }

            echo html_writer::table($examtable);

            $delurl->params(array('batch'=>'delete', 'del'=>''));

            foreach($exams as $exam) {
                $delurl->params(array('items['.$exam->id.']'=>$exam->id));
            }
            $delexamslink = html_writer::link($delurl, get_string('deleteexams', 'examregistrar'));

            echo $output->heading($delexamslink,  4, 'managesesionactionlink');
        }
        unset($exams);

    print_collapsible_region_end(false);

    echo $output->container_end();

    echo $output->container('', 'clearfix');

    echo $output->container_start('examregsessionrooms clearfix ');

        $sql = "SELECT e.*, c.shortname, c.fullname, uc.credits, uc.term, COUNT(e.courseid) AS calls
                FROM {examregistrar_exams} e
                JOIN {course} c ON c.id = e.courseid
                LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid 
                WHERE e.examregid = :exregid AND e.annuality = :annuality AND e.period = :period AND e.visible = 1
                GROUP BY e.courseid
                HAVING calls <> :calls
                ORDER BY e.programme ASC, c.shortname ASC, e.id ASC";

        $params = array('exregid'=>$examregprimaryid, 'annuality'=>$annuality, 'period'=>$period->id, 'calls'=>$period->calls);

        $exams = $DB->get_records_sql($sql, $params);

    print_collapsible_region_start('managesession', 'showhideperiodqcwrongnumber', get_string('periodqcwrongnumber', 'examregistrar', count($exams)),'periodqcwrongnumber', true, false);

        if($exams) {
            $examtable = new html_table();
            $examtable->attributes = array('class'=>'flexible generaltable examregsessionroomstable' );
            $tableheaders = array(get_string('course'),
                                    get_string('programme', 'examregistrar'),
                                    get_string('numcalls', 'examregistrar'),
                                    get_string('action'),
                                    );
            $examtable->head = $tableheaders;
            $examtable->colclasses = array();

            $stradd = get_string('addexam', 'examregistrar');
            $addurl = new moodle_url($baseurl, array('action'=>'generate'));
            $iconadd = new pix_icon('t/add', $stradd, 'moodle', array('class'=>'iconsmall', 'title'=>$stradd));

            $items = array();
            foreach($exams as $exam) {
                $cellcourse = $exam->shortname.' - '.$exam->fullname;
                $cellprogramme = $exam->programme;
                $cellcalls = $exam->calls.' / '.$period->calls;
                $addurl->params(array('courses'=>$course->shortname));
                $items[] = $course->shortname;
                $cellaction = $output->action_icon($addurl, $iconadd, null, array('class'=>'managesesionactionlink'));
                $row = new html_table_row(array($cellcourse, $cellprogramme, $cellcalls, $cellaction));
                $examtable->data[] = $row;
            }

            echo html_writer::table($examtable);
            $addurl->params(array('courses'=>implode(',', $items)));
            $addexamslink = html_writer::link($addurl, get_string('addexams', 'examregistrar'));

            echo $output->heading($addexamslink,  4, 'managesesionactionlink');
        }
        unset($exams);

    print_collapsible_region_end(false);
    echo $output->container_end();

    echo $output->container_start('examregsessionexams clearfix ');

        $insessions = ' AND (e.examsession = -1)';
        $params = array('exregid'=>$examregprimaryid, 'annuality'=>$annuality, 'period'=>$period->id);
        if($sessions = $DB->get_records_menu('examregistrar_examsessions', array('period'=>$period->id), 'id', 'id, id')) {
            list($insql, $inparams) = $DB->get_in_or_equal(array_keys($sessions), SQL_PARAMS_NAMED, 'sess', false);
            $insessions = " AND e.examsession $insql ";
            $params = $params + $inparams;
        }

        $sql = "SELECT e.*, esn.name AS sessionname, esn.idnumber AS sessionidnumber, s.examdate, c.shortname, c.fullname, uc.credits, uc.term
                FROM {examregistrar_exams} e
                JOIN {examregistrar_examsessions} s ON e.examsession = s.id
                LEFT JOIN {examregistrar_elements} esn ON s.examregid =  esn.examregid AND esn.type = 'examsessionitem' AND s.examsession = esn.id
                JOIN {course} c ON c.id = e.courseid
                LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid 
                WHERE e.examregid = :exregid AND e.annuality = :annuality AND e.period = :period AND e.visible = 1 $insessions
                ORDER BY e.programme ASC, c.shortname ASC, e.id ASC";

        $exams = $DB->get_records_sql($sql, $params);

    print_collapsible_region_start('managesession', 'showhideperiodqcwrongsession', get_string('periodqcwrongsession', 'examregistrar', count($exams)),'periodqcwrongsession', true, false);

        if($exams) {
            $examtable = new html_table();
            $examtable->attributes = array('class'=>'flexible generaltable examregsessionroomstable' );
            $tableheaders = array(get_string('course'),
                                    get_string('programme', 'examregistrar'),
                                    get_string('examsessionitem', 'examregistrar'),
                                    get_string('action'),
                                    );
            $examtable->head = $tableheaders;
            $examtable->colclasses = array();

            $stredit   = get_string('edit');
            $editurl = new moodle_url($baseurl, array('item'=>0));
            $iconedit = new pix_icon('t/edit', $stredit, 'moodle', array('class'=>'iconsmall', 'title'=>$stredit));

            foreach($exams as $exam) {
                $cellcourse = $exam->shortname.' - '.$exam->fullname;
                $cellprogramme = $exam->programme;
                $cellsession = $output->formatted_name($exam->sessionname,  $exam->sessionidnumber);
                $cellsession .= $exam->examdate ? '<br />'.userdate($exam->examdate, get_string('strftimedaydate')) : '';
                $editurl->params(array('item'=>$exam->id));
                $cellaction = $output->action_icon($editurl, $iconedit, null, array('class'=>'managesesionactionlink'));
                $row = new html_table_row(array($cellcourse, $cellprogramme, $cellsession, $cellaction));
                $examtable->data[] = $row;
            }

            echo html_writer::table($examtable);
        }
        unset($exams);

    print_collapsible_region_end(false);
    echo $output->container_end();


    echo $output->container('', 'clearfix');

} else {
    echo $output->heading(get_string('selectperiod', 'examregistrar'), 5 );
}

    echo $output->container('', 'clearfix');

    if($period) {
        echo $output->heading(get_string('genericqc', 'examregistrar'), 5 );
    }

    echo $output->container_start('examregqualitycontrol clearfix ');


        $sql = "SELECT c.id, c.shortname, c.idnumber, c.fullname, uc.term, uc.credits, c.category, cc.name, cc.idnumber AS catidnumber, ucc.degree,
                (SELECT COUNT(a.course)
                        FROM {assign} a
                        JOIN {assign_plugin_config} apc ON a.id = apc.assignment AND apc.plugin = 'exam' AND apc.name = 'enabled' AND apc.value = 1
                        WHERE a.course = c.id
                        GROUP BY a.course ) AS examinstances
                FROM {course} c
                LEFT JOIN {local_ulpgccore_course} uc ON c.id = uc.courseid 
                JOIN {course_categories} cc ON cc.id = c.category
                LEFT JOIN {local_ulpgccore_categories} ucc ON cc.id = ucc.categoryid
                LEFT JOIN {examregistrar_exams} e ON c.id = e.courseid AND e.examregid = :exregid AND e.annuality = :annuality AND e.visible = 1
                WHERE e.id IS NULL AND uc.credits > 0
                ORDER BY ucc.degree ASC, c.shortname ASC, c.fullname ASC ";
        $params = array('exregid'=>$examregprimaryid, 'annuality'=>$annuality);
        $courses = $DB->get_records_sql($sql, $params);
        /*
        $num = count($courses);
        $title = get_string('examsqcnoexamcourses', 'examregistrar');
        $nome = get_string('none);
        $title .= $num ? ": $num" : */

    print_collapsible_region_start('managesession', 'showhideexamsgcnoexamscourses', get_string('examsqcnoexamcourses', 'examregistrar', count($courses)),'examsgcnoexamscourses', true, false);

        if($courses) {

            $examtable = new html_table();
            $examtable->attributes = array('class'=>'flexible generaltable examregsessionroomstable' );
            $tableheaders = array(get_string('course'),
                                    get_string('programme', 'examregistrar'),
                                    get_string('termitem', 'examregistrar'),
                                    get_string('items', 'examregistrar'),
                                    get_string('action'),
                                    );
            $examtable->head = $tableheaders;
            $examtable->colclasses = array();

            $stradd = get_string('addexam', 'examregistrar');
            $addurl = new moodle_url($baseurl, array('action'=>'generate'));
            $iconadd = new pix_icon('t/add', $stradd, 'moodle', array('class'=>'iconsmall', 'title'=>$stradd));

            $items = array();
            foreach($courses as $course) {
                $cellcourse = $course->shortname.' - '.$course->fullname;
                $cellprogramme = $course->degree;
                $cellterm = $course->term;
                $cellinstances = $course->examinstances;
                $addurl->params(array('courses'=>$course->shortname));
                $items[] = $course->shortname;
                $cellaction = $output->action_icon($addurl, $iconadd, null, array('class'=>'managesesionactionlink'));
                $row = new html_table_row(array($cellcourse, $cellprogramme, $cellterm, $cellinstances, $cellaction));
                $examtable->data[] = $row;
            }

            echo html_writer::table($examtable);

            $addurl->params(array('courses'=>implode(',', $items)));
            $addexamslink = html_writer::link($addurl, get_string('addexams', 'examregistrar'));

            echo $output->heading($addexamslink,  4, 'managesesionactionlink');
        }
        unset($courses);

    print_collapsible_region_end(false);
    echo $output->container_end();

    echo $output->container_start('examregprintoperators clearfix ');

        $sql = "SELECT e.*, c.shortname, c.fullname, uc.credits, ucc.degree
                FROM {examregistrar_exams} e
                LEFT JOIN {course} c ON e.courseid = c.id
                LEFT JOIN {course_categories} cc ON c.category = cc.id
                LEFT JOIN {local_ulpgccore_course} uc ON e.courseid = uc.courseid 
                LEFT JOIN {local_ulpgccore_categories} ucc ON cc.id = ucc.categoryid 
                WHERE e.examregid = :exregid AND e.annuality = :annuality AND e.visible = 1 AND uc.credits > 0 AND
                ((e.courseid IS NULL OR c.id IS NULL) OR (e.programme IS NULL OR e.programme = '' OR e.programme = 0) OR
                (e.programme <> ucc.degree))
                ORDER BY e.programme ASC, c.shortname ASC, e.id ASC";
        $params = array('exregid'=>$examregprimaryid, 'annuality'=>$annuality);
        $exams = $DB->get_records_sql($sql, $params);


    print_collapsible_region_start('managesession', 'showhideexamsgcnocourse', get_string('examsgcnocourse', 'examregistrar', count($exams)),'examsgcnocourse', true, false);

        if($exams) {
            $examtable = new html_table();
            $examtable->attributes = array('class'=>'flexible generaltable examregsessionroomstable' );
            $tableheaders = array(get_string('course'),
                                    get_string('programme', 'examregistrar'),
                                    get_string('category'),
                                    get_string('action'),
                                    );
            $examtable->head = $tableheaders;
            $examtable->colclasses = array();

            $strdelete = get_string('delete');
            $delurl = new moodle_url($baseurl, array('del'=>0));
            $icondel = new pix_icon('t/delete', $strdelete, 'moodle', array('class'=>'iconsmall', 'title'=>$strdelete));
            $stredit   = get_string('edit');
            $editurl = new moodle_url($baseurl, array('item'=>0));
            $iconedit = new pix_icon('t/edit', $stredit, 'moodle', array('class'=>'iconsmall', 'title'=>$stredit));

            foreach($exams as $exam) {
                if(!$exam->shortname && !$exam->fullname) {
                    $cellcourse = get_string('nocourse', 'examregistrar');
                } else {
                    $cellcourse = $exam->shortname.' - '.$exam->fullname;
                }
                $cellprogramme = $exam->programme;
                $celldegree = $exam->degree;
                $editurl->params(array('item'=>$exam->id));
                $delurl->params(array('del'=>$exam->id));
                $cellaction = $output->action_icon($editurl, $iconedit, null, array('class'=>'managesesionactionlink'));
                $cellaction .= '&nbsp; &nbsp;'.$output->action_icon($delurl, $icondel, null, array('class'=>'managesesionactionlink'));
                $row = new html_table_row(array($cellcourse, $cellprogramme, $celldegree, $cellaction));
                $examtable->data[] = $row;
            }

            echo html_writer::table($examtable);

            $delurl->params(array('batch'=>'delete', 'del'=>''));

            foreach($exams as $exam) {
                $delurl->params(array('items['.$exam->id.']'=>$exam->id));
            }
            $delexamslink = html_writer::link($delurl, get_string('deleteexams', 'examregistrar'));

            echo $output->heading($delexamslink,  4, 'managesesionactionlink');
        }
        unset($exams);

    print_collapsible_region_end(false);

    echo $output->container_end();

    echo $output->container('', 'clearfix');
