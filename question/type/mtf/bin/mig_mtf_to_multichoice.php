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
 * @author Martin Hanusch martin.hanusch@let.ethz.ch
 * @copyright ETHz 2018 martin.hanusch@let.ethz.ch
 */

// **********************************************************************************************************************
// Dependencies
require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->libdir . '/questionlib.php');

// **********************************************************************************************************************
// Getting parameters
$courseid = optional_param('courseid', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$all = optional_param('all', 0, PARAM_INT);
$dryrun = optional_param('dryrun', 1, PARAM_INT);
$autoweights = optional_param('autoweights', 0, PARAM_INT);

@set_time_limit(0);
@ini_set('memory_limit', '3072M'); // Whooping 3GB due to huge number of questions text size.

// **********************************************************************************************************************
// General Page Setup
echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' .
    '<style>body{font-family: "Courier New", Courier, monospace; font-size: 12px; background: #ebebeb; color: #5a5a5a;}</style></head>';
echo "=========================================================================================<br/>\n";
echo "M I G R A T I O N :: MTF to Multichoice<br/>\n";
echo "=========================================================================================<br/>\n";

// **********************************************************************************************************************
// Checking for permissions
require_login();
if (!is_siteadmin()) {
    echo "<br/>[<font color='red'>ERR</font>] You are not a Website Administrator";
    die();
}

$starttime = microtime(1);
$fs = get_file_storage();

$sql = "SELECT q.*
        FROM {question} q
        WHERE q.qtype = 'mtf'
          AND q.parent = 0
          AND q.id in (select questionid from {qtype_mtf_options})
        ";
$params = array();

// **********************************************************************************************************************
// Showing information when either no or too many parameters are selected.
$num_parameters = ($all == 0 ? 0 : 1) + ($courseid == 0 ? 0 : 1) + ($categoryid == 0 ? 0 : 1);
if (($all != 1 && $courseid <= 0 && $categoryid <= 0) || $num_parameters > 1 ) {
    echo "
    <br/>\nParameters:<br/><br/>\n\n
    =========================================================================================<br/>\n
    You must specify certain parameters for this script to work: <br/><br/>\n\n
    Step 1: <b>NECESSARY </b> - Use ONE of the following three parameters-value pairs:
    <ul>
        <li><b>courseid</b> (values: <i>a valid course ID</i>)</li>
        <li><b>categoryid</b> (values: <i>a valid category ID</i>)</b></li>
        <li><b>all</b> (values: 1)</li>
    </ul>
    This parameter-value pairs define which MTF questions will be migrated.<br/><br/>\n\n
    Step 2: <b>IMPORTANT AND STRONGLY RECOMMENDED:</b><br/>\n
    <ul>
        <li><b>dryrun</b> (values: <i>0,1</i>)</li>
        <li><b>autoweights</b> (values: <i>0,1</i>)</li>
    </ul>
    The Dryrun Option is enabled (1) by default.<br/>\n
    With Dryrun enabled no changes will be made to the database.<br/>\n
    Use Dryrun to receive information about possible issues before migrating.<br/><br/>\n\n
    The Autoweights Options is disabled (0) by default.<br/>\n
    While migrating from MTF to Multichoice, grades for correct or incorrect answers are <br/>\n
    usually set equal. However in some cases the SUM of all grades does not match 100%.<br/>\n
    With Autoweights enabled different grades will be set to match a SUM of 100%.<br/>\n
    With Autoweights disabled the affected question will be ignored in migration.<br/><br/>\n\n
    =========================================================================================<br/><br/>\n\n
    Examples:<br/><br/>\n\n
    =========================================================================================<br/>\n
	<ul>
        <li><strong>Migrate MTF Questions in a specific course</strong>:<br/>\n
        MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?<b>courseid=55</b>
        <li><strong>Migrate MTF Questions in a specific category</strong>:<br/>\n
        MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?<b>categoryid=1</b>
        <li><strong>Migrate all MTF Questions</strong>:<br/>\n
        MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?<b>all=1</b>
        <li><strong>Disable Dryrun</strong>:<br/>\n
        MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?all=1<b>&dryrun=0</b>
        <li><strong>Enable Autoweights</strong>:<br/>\n
        MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?all=1<b>&autoweights=1</b>
	</ul>
    <br/>\n";
    die();
}

// **********************************************************************************************************************
// Parameter Information
echo "-----------------------------------------------------------------------------------------<br/><br/>\n\n";
echo ($dryrun == 1 ? "[<font style='color:#228d00;'>ON </font>] ":"[<font color='red'>OFF</font>] ") . 
    "Dryrun: " . ($dryrun == 1 ? "NO changes to the database will be made!" : "Migration is being processed") . "<br/>\n";
echo ($autoweights == 1 ? "[<font style='color:#228d00;'>ON </font>] ":"[<font color='red'>OFF</font>] ") . 
    "Autoweights<br/><br/>\n\n";
echo "-----------------------------------------------------------------------------------------<br/>\n";
echo "=========================================================================================<br/>\n";

// **********************************************************************************************************************
// Get the categories : Case 1
if($all == 1) {
    if ($categories = $DB->get_records('question_categories', array())) {
        echo "Migration of all MTF Questions<br/>\n";
    } else {
        echo "<br/>[<font color='red'>ERR</font>] Could not get categories<br/>\n";
        die();
    }
}
// Get the categories : Case 2
if ($courseid > 0) {
    if (!$course = $DB->get_record('course', array('id' => $courseid
    ))) {
        echo "<br/>[<font color='red'>ERR</font>] Course with ID " . $courseid . " not found<br/>\n";
        die();
    }
    $coursecontext = context_course::instance($courseid);
    $contextid = $coursecontext->id;

    $categories = $DB->get_records('question_categories',
            array('contextid' => $coursecontext->id
            ));
    $catids = array_keys($categories);
    if (!empty($catids)) {
        echo "Migration of MTF Questions within courseid " . $courseid . " <br/>\n";
        list($csql, $params) = $DB->get_in_or_equal($catids);
        $sql .= " AND category $csql ";
    } else {
        echo "<br/>[<font color='red'>ERR</font>] No question categories for course found.<br/>\n";
        die();
    }
}
// Get the categories : Case 3
if ($categoryid > 0) {
    if ($categories[$categoryid] = $DB->get_record('question_categories', array('id' => $categoryid
    ))) {
        echo 'Migration of MTF questions within category "' . $categories[$categoryid]->name . "\"<br/>\n";
        $sql .= ' AND category = :category ';
        $params = array('category' => $categoryid);
        $contextid = $DB->get_field('question_categories', 'contextid', array('id' => $categoryid));
    } else {
        echo "<br/>[<font color='red'>ERR</font>] Question category with ID " . $categoryid . " not found<br/>\n";
        die();
    }
}

// **********************************************************************************************************************
// Get the questions based on the previous set parameters
//$sql .= " ORDER BY category ASC"; //Code for duplicating a category
$questions = $DB->get_records_sql($sql, $params);
echo 'Questions found: ' . count($questions) . "<br/>\n";
echo "=========================================================================================<br/><br/>\n\n";
if (count($questions) == 0) {
    echo "<br/>[<font color='red'>ERR</font>] No questions found<br/>\n";
    die();
}

// **********************************************************************************************************************
// Processing the single questions
echo "Migrating questions...<br/>\n";
$num_migrated = 0;
//$num_categories = 0; //Code for duplicating a category
$questions_migrated = [];
$questions_notmigrated = [];
//$category_map = []; //Code for duplicating a category

foreach ($questions as $question) {
    set_time_limit(600);
    $question->oldid = $question->id;
    $question->oldname =  $question->name;

    // *****************************************************
    // Getting related question data
    $mtf_columns = $DB->get_records('qtype_mtf_columns', array('questionid' => $question->id), ' id ASC ');
    $mtf_options = $DB->get_record('qtype_mtf_options', array('questionid' => $question->id));
    $mtf_rows = $DB->get_records('qtype_mtf_rows', array('questionid' => $question->id), ' id ASC ');
    $mtf_weights = $DB->get_records('qtype_mtf_weights', array('questionid' => $question->id), ' id ASC ');
    $question_hints = $DB->get_records('question_hints', array('questionid' => $question->id), ' id ASC ');


    // *****************************************************
    // Checking for possible errors before doing anyting
    // Getting question fractions in case of a complete record.
    if (!isset($mtf_columns) || !isset($mtf_options) || !isset($mtf_rows) || !isset($mtf_weights) || !isset($question_hints)) {
        $question_weights = array("error"=>true, "message"=>"Database records incomplete.", "notices"=>[]);
    } else {
        $question_weights = get_weights($mtf_weights, $autoweights, $mtf_columns);
    }        

    // *****************************************************
    // If weights are not mapable, skip the whole question
    // and continue; with the next iteration.
    if ($question_weights["error"]) {
        echo '[<font style="color:#ff0909;">ERR</font>] - question <i>"' . $question->oldname . 
            '"</i> (ID: <a href="' . $CFG->wwwroot . '/question/preview.php?id=' . $question->oldid . 
            '" target="_blank">' . $question->oldid . '</a>) is not migratable: ' . $question_weights["message"];
        echo sizeOf($question_weights["notices"]) > 0 ? " ::: <b>Notices:</b> " . implode(" | ", $question_weights["notices"]) : null;
        echo "<br/>\n";
        array_push($questions_notmigrated, array("id"=>$question->oldid, "name"=>$question->oldname));
        continue;
    } else {
        $num_migrated++;
    }

    // *****************************************************
    // if Dryrun is disabled, changes to the database 
    // are made from this point on
    if ($dryrun == 0) {
        try {
            unset($transaction);
            $transaction = $DB->start_delegated_transaction();

            // *****************************************************
            // Duplicating a category
            /*//Code for duplicating a category
            if (!array_key_exists($question->category, $category_map)) {
                $category_to_insert = clone $categories[$question->category];
                $category_to_insert->name .= " (MTF to MC)"; 
                $category_to_insert->stamp = make_unique_id_code();
                unset($category_to_insert->id);
                $category_map[$question->category] = $DB->insert_record('question_categories', $category_to_insert);
                $num_categories++;
                echo '[<font style="color:#228d00;">OK </font>] category - <i>"' . $categories[$question->category]->name . 
                '" > "' . $category_to_insert->name . '"</i> (ID: ' . $category_map[$question->category] . ")<br/>\n";
            }
            */
            
            // *****************************************************
            // Get contextid from question category
            $contextid = $DB->get_field('question_categories', 'contextid', array('id' => $question->category));
            if (!isset($contextid)) {
                echo "<br/>[<font color='red'>ERR</font>] No context id found for this question.";
                continue;
            }

            // *****************************************************
            // Duplicating  mdl_question
            // --->         mdl_question
            unset($question->id);
            //$question->category = $category_map[$question->category]; //Code for duplicating a category
            $question->parent = 0;
            $question->name = substr($question->name . " (MC " . date("Y-m-d H:i:s") . ")", 0, 255);
            $question->qtype = "multichoice";
            $question->stamp = make_unique_id_code();
            $question->version = make_unique_id_code();
            $question->timecreated = time();
            $question->timemodified = time();
            $question->modifiedby = $USER->id;
            $question->createdby = $USER->id;
            $question->id = $DB->insert_record('question', $question);

            // *****************************************************
            // Tansferring  md_qtype_mtf_rows + mdl_qtype_mtf_weights         
            // --->         mdl_question_answers
            foreach ($mtf_rows as $key => $row) {
                $entry = new stdClass();
                $entry->question = $question->id;
                $entry->answer = $mtf_rows[$key]->optiontext;
                $entry->answerformat = $mtf_rows[$key]->optiontextformat;
                $entry->fraction = $question_weights["message"][$row->number];
                $entry->feedback = $mtf_rows[$key]->optionfeedback;
                $entry->feedbackformat = $mtf_rows[$key]->optionfeedbackformat;
                $question_answerid = $DB->insert_record('question_answers', $entry);
                unset($entry);

                // *****************************************************
                // Copy images in the optiontext to the new answer.
                copy_files($fs, $contextid, $mtf_rows[$key]->id, $question_answerid, $mtf_rows[$key]->optiontext, "optiontext", "qtype_mtf", "question", "answer");
                // *****************************************************
                // Copy images in the answer feedback.
                copy_files($fs, $contextid, $mtf_rows[$key]->id, $question_answerid, $mtf_rows[$key]->optionfeedback, "feedbacktext", "qtype_mtf", "question", "answerfeedback");
            }

            // *****************************************************
            // Tansferring  mdl_question_hints 
            // --->         mdl_question_hints
            foreach ($question_hints as $key => $row) {
                $entry = new stdClass();
                $entry->questionid = $question->id;
                $entry->hint = $row->hint;
                $entry->hintformat = $row->hintformat;
                $entry->shownumcorrect = 0;
                $entry->clearwrong = 0;
                $entry->options = $row->options;
                $DB->insert_record('question_hints', $entry);
                unset($entry);
            }

            // *****************************************************
            // Transferring md_qtype_mtf_options 
            // --->         md_qtype_multichoice_options
            $entry = new stdClass();
            $entry->questionid = $question->id;
            $entry->layout = 0;
            $entry->single = 0;
            $entry->shuffleanswers = $mtf_options->shuffleanswers;
            $entry->correctfeedback = "Your answer is correct";
            $entry->correctfeedbackformat = 1;
            $entry->partiallycorrectfeedback = "Your answer is partially correct";
            $entry->partiallycorrectfeedbackformat = 1;
            $entry->incorrectfeedback = "Your answer is incorrect";
            $entry->incorrectfeedbackformat = 1;
            $entry->answernumbering = $mtf_options->answernumbering;
            $entry->shownumcorrect = 0;
            $DB->insert_record('qtype_multichoice_options', $entry);
            unset($entry);

            // *****************************************************
            // Copy images in the questiontext to new itemid.
            copy_files($fs, $contextid, $question->oldid, $question->id, $question->questiontext, "questiontext", "question", "question", "questiontext");
            // *****************************************************
            // Copy images in the general feedback to new itemid.
            copy_files($fs, $contextid, $question->oldid, $question->id, $question->generalfeedback, "generalfeedback", "question", "question", "generalfeedback");

            // *****************************************************
            // Copy tags
            $tags = $DB->get_records_sql("SELECT * FROM {tag_instance} WHERE itemid = :itemid", array('itemid' => $question->oldid));
            foreach ($tags as $tag) {
                $entry = new stdClass();
                $entry->tagid = $tag->tagid;
                $entry->component = $tag->component;
                $entry->itemtype = $tag->itemtype;
                $entry->itemid = $question->id;
                $entry->contextid = $tag->contextid;
                $entry->tiuserid = $tag->tiuserid;
                $entry->ordering = $tag->ordering;
                $entry->timecreated = $tag->timecreated;
                $entry->timemodified = $tag->timemodified;
                $DB->insert_record('tag_instance', $entry);
            }

            // *****************************************************
            // Save changes to the database
            $transaction->allow_commit();
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
    }

    // *****************************************************
    // Output: Question Migration Success
    echo '[<font style="color:#228d00;">OK </font>] - question <i>"' . $question->oldname . '"</i> ' . 
        '(ID: <a href="' . $CFG->wwwroot . '/question/preview.php?id=' . $question->oldid . 
        '" target="_blank">' . $question->oldid . '</a>) ';
    echo $dryrun == 0 ? ' > <i>"' . $question->name . '"</i> ' .
        '(ID: <a href="' . $CFG->wwwroot . '/question/preview.php?id=' . $question->id . 
        '" target="_blank">' . $question->id . '</a>)' : " is migratable";
    echo sizeOf($question_weights["notices"]) > 0 ? " ::: <b>Notices:</b> " . implode(" | ", $question_weights["notices"]) : null;
    echo "<br/>\n";
}

// **********************************************************************************************************************
// Showing final summary
echo "<br/>\n";
echo "=========================================================================================<br/>\n";
echo sizeOf($questions_notmigrated) > 0 ? "Not Migrated: <br/>" : null;
foreach ($questions_notmigrated as $entry) {
    echo '<a href="' . $CFG->wwwroot . '/question/preview.php?id=' . $entry["id"] . '" target="_blank">' . 
    $CFG->wwwroot . '/question/preview.php?id=' . $entry["id"] . "</a> - " . $entry["name"] . "<br/>\n";
}
echo "=========================================================================================<br/>\n";
echo "SCRIPT DONE: Time needed: " . round(microtime(1) - $starttime, 4) . " seconds.<br/>\n";
//echo $dryrun == 0 ? $num_categories . " categories duplicated.<br/>\n" : null; //Code for duplicating a category
echo $num_migrated . "/" . count($questions) . " questions " . ($dryrun == 1 ? "would be " : null) . "migrated.<br/>\n";
echo "=========================================================================================<br/>\n";
die();

// **********************************************************************************************************************
// Mapping the mtf weights to multichoice fractions.
// This function checks for possible mapping problems
function get_weights($weights, $autoweights, $columns) {

    // *****************************************************
    // Getting the Moodle fractions
    $notices = [];
    $fractions = question_bank::fraction_options_full();
    if (empty($fractions)) {
        return array("error"=>true, "message"=>"Error loading Moodle fractions", "notices"=>$notices);
    }
    foreach ($fractions as $key => $record) {
        $fractions[$key] = $key;
    }

    // *****************************************************
    // Creating an answers array, which is still filled
    // with mtf weights
    $answers = [];
    $num_correct = 0;
    foreach ($weights as $record) {
        if ($record->columnnumber == 1) {
            $answers[$record->rownumber] = $record->weight;
            $record->weight > 0 ? $num_correct++ : null;  
        }
    }
    $num_incorrect = sizeOf($answers) - $num_correct;

    // *****************************************************
    // Notice - Case 1: Labels are not matching
    // either "true" or "false"
    $valid_responsetexts = array("true", "false", "wahr", "falsch");
    foreach ($columns as $record) {
        (!in_array(strtolower($record->responsetext), $valid_responsetexts)) ? 
        array_push($notices, 'Judgement option "' . $record->responsetext . '" not matching standard "True"/"False"') : null;
    }

    // *****************************************************
    // Error - Case 1: All answers are marked as incorrect
    if ($num_correct == 0) {
        return array("error"=>true, "message"=>"All answers are incorrect", "notices"=>$notices);
    }

    // *****************************************************
    // Error - Case 2: Too many correct answers
    $fractions_min = min(array_filter($fractions, function($value) { return $value > 0; }));
    if ($num_correct > 1 / $fractions_min) {
        return array("error"=>true, "message"=>"Too many correct answers: Number * Min-Fraction exceeds 100%", "notices"=>$notices);
    }

    // *****************************************************
    // Creating fractions for Multichoice
    // Equal distribution of grades on 100%
    $num_correct != 0 ? $fraction_correct = number_format(1 / $num_correct, 7) : $fraction_correct = 0;
    $num_incorrect != 0 ? $fraction_incorrect = number_format(-1 / $num_incorrect, 7) : $fraction_incorrect = 0;
    $fraction_correct_exists = in_array($fraction_correct, $fractions);
    $fraction_incorrect_exists = in_array($fraction_incorrect, $fractions);

    // *****************************************************
    // Error - Case 3: Fraction value does not exist
    // This part of code assumes that fraction 0.05
    // & 0.1 is part of the Moodle internal fractions.
    if (!$fraction_correct_exists || !$fraction_incorrect_exists ) {
        if (!$fraction_correct_exists) {
            if ($autoweights == 0) {
                return array("error"=>true, "message"=>"Positive weights not mapable to fraction values (Solution: autoweights=1)", "notices"=>$notices);
            } else {
                $num_10_correct = 1 / $fractions_min - $num_correct;
            }
            array_push($notices, "Autoweights applied to positive fractions to match moodle fractions");
        }
        if (!$fraction_incorrect_exists) {
            $num_10_incorrect = 1 / $fractions_min - $num_incorrect;
            array_push($notices, "Autoweights applied to negative fractions to match moodle fractions");
        }

        $counter_correct = $counter_incorrect = 0;
        foreach ($answers as $key => $record) {
            // Apply correct fractions
            if ($answers[$key] > 0) {
                if (!$fraction_correct_exists) {
                    $counter_correct < $num_10_correct ? $answers[$key] = 0.1 : $answers[$key] = 0.05;
                    $counter_correct++;
                } else {
                    $answers[$key] = $fraction_correct;
                }
            }
            // Apply incorrect fractions
            if ($answers[$key] == 0) {
                if (!$fraction_incorrect_exists) {
                    $counter_incorrect < $num_10_incorrect ? $answers[$key] = -0.1 : $answers[$key] = -0.05;
                    $counter_incorrect++;
                } else {
                    $answers[$key] = $fraction_incorrect;
                }
            }
        }
        return array("error"=>false, "message"=>$answers, "notices"=>$notices);
    }

    // *****************************************************
    // All good: Applying the calculated multichoice
    // fractions to the answers array and returning it.
    foreach ($answers as $key => $record) {
        $answers[$key] > 0 ? $answers[$key] = $fraction_correct : $answers[$key] = $fraction_incorrect;
    }
    return array("error"=>false, "message"=>$answers, "notices"=>$notices);
}

function get_image_filenames($text) {
    $result = array();
    $strings = preg_split("/<img|<source/i", $text);
    foreach ($strings as $string) {
        $matches = array();
        if (preg_match('!@@PLUGINFILE@@/(.+)!u', $string, $matches) && count($matches) > 0) {
            $filename = mb_substr($matches[1], 0, mb_strpos($matches[1], '"'));
            $filename = urldecode($filename);
            $result[] = $filename;
        }
    }
    return $result;
}

// **********************************************************************************************************************
// Copying files from one question to another
function copy_files($fs, $contextid, $oldid, $newid, $text, $type, $olcdomponent, $newcomponent, $filearea) {
    $filenames = get_image_filenames($text);
    foreach ($filenames as $filename) {
        $file = $fs->get_file($contextid, $olcdomponent, $type, $oldid, '/', $filename);
        if ($file) {
            $newfile = new stdClass();
            $newfile->component = $newcomponent;
            $newfile->filearea = $filearea;
            $newfile->itemid = $newid;
            if (!$fs->get_file($contextid, $newfile->component, $newfile->filearea, $newfile->itemid, '/', $filename)) {
                $fs->create_file_from_storedfile($newfile, $file);
            }
        }
    }
}