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
 *
 * @package qtype_mtf
 * @author Amr Hourani amr.hourani@id.ethz.ch
 * @copyright ETHz 2016 amr.hourani@id.ethz.ch
 */
require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/question/type/mtf/lib.php');

$courseid = optional_param('courseid', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$all = optional_param('all', 0, PARAM_INT);
$dryrun = optional_param('dryrun', 0, PARAM_INT);

@set_time_limit(0);
@ini_set('memory_limit', '3072M'); // Whooping 3GB due to huge number of questions text size.

require_login();

if (!is_siteadmin()) {
    echo 'You are not a Website Administrator!';
    die();
}

// Helper function to turn weight records from the database into an array
// indexed by rowid and columnid.
function weight_records_to_array($weightrecords) {
    $weights = array();
    foreach ($weightrecords as $weight) {
        if (!array_key_exists($weight->rowid, $weights)) {
            $weights[$weight->rowid] = array();
        }
        $weights[$weight->rowid][$weight->colid] = $weight;
    }
    return $weights;
}

$starttime = time();

$sql = "SELECT q.*
        FROM {question} q
        WHERE q.qtype = 'multichoice'
        and q.id in (select questionid from {qtype_multichoice_options} where single = '0')
        ";
$params = array();

if (!$all && (!($courseid > 0 || $categoryid > 0))) {
    echo "<br /><center><h1><br />**********<br />
    MultiChoice To MTF<br />**********<br />
    ********** Multichoice (with Multi Answers Only) migration To qtype_mtf (MTF ETHz). **********<br />
    <br /><font color='red'>You should specify either the '<font color='black'>courseid</font>'
    or the '<font color='black'>categoryid</font>' parameter Or set the parameter '<font color='black'>all</font>'
    to 1. Please set '<font color='black'>dryrun</font>' parameter to 1
    in order to simulate the migration before committing to the database.
    No migration will be done without restrictions!</font>
	</center>
	<br /><br />Examples:
	<ul>
	<li><strong>Specific Course</strong>:
    MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?<font color='blue'>courseid=55</font>
	<li><strong>Specific Question Category</strong>:
    MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?<font color='blue'>categoryid=1</font>
	<li><strong>All Multi question</strong>:
    MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?<font color='blue'>all=1</font>
	<li><strong><font color=red>IMPORTANT & STRONGLY RECOMMENDED</font></strong>:
    Dry run (no changes made to database - only simulating what will happen)
    can be done before migrating by using any of the above URLs and
    adding <strong>&dryrun=1</strong> to the end of the URL.
    Example: MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?all=1<font color='red'>&dryrun=1</font>
	</ul>
	</h1><br/>\n";
    die();
}

if ($courseid > 0) {
    if (!$course = $DB->get_record('course', array('id' => $courseid
    ))) {
        echo "<br/><font color='red'>Course with ID $courseid  not found...!</font><br/>\n";
        die();
    }
    $coursecontext = context_course::instance($courseid);
    $categories = $DB->get_records('question_categories',
            array('contextid' => $coursecontext->id
            ));
    $catids = array_keys($categories);
    if (!empty($catids)) {
        list($csql, $params) = $DB->get_in_or_equal($catids);
        $sql .= " AND category $csql ";
    } else {
        echo "<br/><font color='red'>No question categories for course found... weird!</font><br/>\n";
        echo "I'm not doing anything without restrictions!\n";
        die();
    }
}

if ($categoryid > 0) {
    if ($category = $DB->get_record('question_categories', array('id' => $categoryid
    ))) {
        echo 'Migration restricted to category "' . $category->name . "\".<br/>\n";
        $sql .= ' AND category = :category ';
        $params = array('category' => $categoryid
        );
    } else {
        echo "<br/><font color='red'>Question category with ID $categoryid  not found...!</font><br/>\n";
        die();
    }
}

$questions = $DB->get_records_sql($sql, $params);
echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>';
echo 'Migrating ' . count($questions) . " multichoice with multi-answers to MTF questions... <br/>\n";

if ($dryrun) {
    echo "***********************************************************<br/>\n";
    echo "*   Dry run: NO changes to the database will be made! *<br/>\n";
    echo "***********************************************************<br/>\n";
}

$counter = 0;
$notmigrated = array();
foreach ($questions as $question) {
    set_time_limit(600);
    $transaction = $DB->start_delegated_transaction();
    $oldquestionid = $question->id;
    // Retrieve rows and columns and count them.
    $multichoice = $DB->get_record('qtype_multichoice_options',
            array('questionid' => $oldquestionid, 'single' => 0
            ));
    $rows = $DB->get_records('question_answers', array('question' => $question->id
    ), ' id ASC ');
    $rowids = array_keys($rows);
    $columns = array();
    if ($multichoice->single == 0) {
        $colmtfount = 2;
    } else {
        $colmtfount = 1;
    }
    for ($i = 1; $i <= $colmtfount; $i++) {
        $colns = new stdClass();
        $colns->id = $i;
        $columns[$i] = $colns;
    }
    $totalnumberofcolumns = count($columns);
    if ($dryrun) {
        echo "<br/>\n" .
                 '--------------------------------------------------------------------------------' .
                 "<br/>\n";
        if (count($rows) <= 1) {
            echo 'Question: "' . $question->name . '" with ID ' . $question->id .
                     " would NOT migrated! It has the wrong number of options!<br/>\n";
            $notmigrated[] = $question;
        } else if ($colmtfount < 1 || $colmtfount > 2) {
            echo 'Question: "' . $question->name . '" with ID ' . $question->id .
                     " would NOT migrated! It has the wrong number of responses!<br/>\n";
            $notmigrated[] = $question;
        } else {
            echo $question->id . ': "' . $question->name . '" with ID ' .
                     "<a href='$CFG->wwwroot/question/preview.php?id=$question->id' target='_blank'>" .
                     $question->id . "</a> would be migrated!<br/>\n";
        }
        continue;
    } else {
        echo "<br/>\n" .
                 '--------------------------------------------------------------------------------' .
                 "<br/>\n";
        echo 'Multichoice Question (With Multi Answers only): "' . $question->name . "\"<br/>\n";
    }
    // If the Multichoice question has got too manu options or responses, we ignore it.
    if (count($rows) <= 1) {
        echo "&nbsp;&nbsp; Question has the wrong number of options! Question is not migrated.<br/>\n";
        $notmigrated[] = $question;
        continue;
    }
    if ($colmtfount < 1 || $colmtfount > 2) {
        echo "&nbsp;&nbsp; Question has the wrong number of responses! Question is not migrated.<br/>\n";
        $notmigrated[] = $question;
        continue;
    }
    // Create a new mtf question in the same category.
    unset($question->id);
    $questionname = substr($question->name . ' (MTF ' . date("Y-m-d H:i:s") . ')', 0, 255);
    // Original Question Name plus SCMTF limited by 255 chars.
    $question->qtype = 'mtf';
    $question->name = $questionname;
    $question->timecreated = time();
    $question->timemodified = time();
    $question->modifiedby = $USER->id;
    $question->createdby = $USER->id;
    // Get the new question ID.
    $question->id = $DB->insert_record('question', $question);
    echo 'New mtf Question: "' . $question->name . '" with ID ' . $question->id . "<br/>\n";
    $rowcount = 1;
    $ignorequestion = 0;
    foreach ($rows as $row) {
        // Create a new mtf row.
        $mtfrow = new stdClass();
        $mtfrow->questionid = $question->id;
        $mtfrow->number = $rowcount++;
        $mtfrow->optiontext = $row->answer;
        $mtfrow->optiontextformat = FORMAT_HTML;
        $mtfrow->optionfeedback = $row->feedback;
        $mtfrow->optionfeedbackformat = FORMAT_HTML;
        $mtfrow->id = $DB->insert_record('qtype_mtf_rows', $mtfrow);
        $colcount = 1;
        $textcount = 1;
        $weightpicked = 0;
        if ($multichoice->single == 0) {
            // Create a new first mtf column.
            $mtfcolumn = new stdClass();
            $mtfcolumn->questionid = $question->id;
            $mtfcolumn->number = 1;
            $mtfcolumn->responsetext = 'True';
            $mtfcolumn->responsetextformat = FORMAT_MOODLE;
            if ($ignorequestion != $question->id) {
                $mtfcolumn->id = $DB->insert_record('qtype_mtf_columns', $mtfcolumn);
            }
            // Create a new second mtf column.
            $mtfcolumn2 = new stdClass();
            $mtfcolumn2->questionid = $question->id;
            $mtfcolumn2->number = 2;
            $mtfcolumn2->responsetext = 'False';
            $mtfcolumn2->responsetextformat = FORMAT_MOODLE;
            if ($ignorequestion != $question->id) {
                $mtfcolumn2->id = $DB->insert_record('qtype_mtf_columns', $mtfcolumn2);
            }
            // Create a new first weight entry.
            $mtfweight = new stdClass();
            $mtfweight->questionid = $question->id;
            $mtfweight->rownumber = $mtfrow->number;
            $mtfweight->columnnumber = $mtfcolumn->number;
            if ($row->fraction > 0) {
                $mtfweight->weight = 1.0;
            } else {
                $mtfweight->weight = 0.0;
            }
            $weightpicked = $mtfweight->weight;
            $mtfweight->id = $DB->insert_record('qtype_mtf_weights', $mtfweight);
            // Create a new second weight entry.
            $mtfweight2 = new stdClass();
            $mtfweight2->questionid = $question->id;
            $mtfweight2->rownumber = $mtfrow->number;
            $mtfweight2->columnnumber = $mtfcolumn2->number;
            if ($weightpicked == 1.0) { // New option opposite to first option.
                $mtfweight2->weight = 0.0;
            } else {
                $mtfweight2->weight = 1.0;
            }
            $mtfweight2->id = $DB->insert_record('qtype_mtf_weights', $mtfweight2);
        }
        $ignorequestion = $question->id;
    }
    // Create the mtf options.
    $mtf = new stdClass();
    $mtf->questionid = $question->id;
    $mtf->shuffleanswers = $multichoice->shuffleanswers;
    $mtf->numberofrows = count($rows);
    $mtf->numberofcolumns = $colmtfount;
    $mtf->answernumbering = $multichoice->answernumbering;
    if ($colmtfount == 1) {
        $mtf->scoringmethod = 'mtfonezero';
    } else {
        $mtf->scoringmethod = 'subpoints';
    }
    $mtf->id = $DB->insert_record('qtype_mtf_options', $mtf);
    $transaction->allow_commit();
}
echo '--------------------------------------------------------------------------------' . "<br/>\n";

$endtime = time();
$used = $endtime - $starttime;
$mins = round($used / 60);
$used = ($used - ($mins * 60));

echo "<br/>\n ******** MTF SCRIPT DONE - ";
echo ' Time needed: ' . $mins . ' mins and ' . $used . " secs. ********<br/>\n<br/>\n";

echo " ******** Questions that were NOT migrated: ";
if (count($notmigrated) > 0) {
    echo "<br/>\n ID | Link | Question Name<br/>\n";
    echo "----------------------------------------<br/>\n<font color='red'>";
    foreach ($notmigrated as $question) {
        echo "$question->id | <a href='$CFG->wwwroot/question/preview.php?id=$question->id' target='_blank'>" .
                 $question->id . '</a> | ' . $question->name . "<br/>\n";
    }
    echo "</font>";
} else {
    echo "NONE. All can be migrated with no problems. ********";
}
die();
