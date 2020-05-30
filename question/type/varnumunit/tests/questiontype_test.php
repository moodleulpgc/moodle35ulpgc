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
 * Unit tests for the varnumunit question type class.
 *
 * @package   qtype_varnumunit
 * @copyright 2012 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/question/type/varnumunit/questiontype.php');
require_once($CFG->dirroot . '/question/format/xml/format.php');

/**
 * Unit tests for the varnumunit question type class.
 *
 * @copyright 2012 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group qtype_varnumunit
 */
class qtype_varnumunit_test extends question_testcase {
    public static $includecoverage = array(
        'question/type/questiontype.php',
        'question/type/varnumunit/questiontype.php',
    );

    protected $qtype;

    protected function setUp() {
        $this->qtype = new qtype_varnumunit();
    }

    protected function tearDown() {
        $this->qtype = null;
    }

    protected function get_test_question_data() {
        $q = new stdClass();
        $q->id = 1;
        $q->options = new stdClass();
        $q->options->answers[1] = (object) array('answer' => '1.5', 'fraction' => 1);
        $q->options->answers[2] = (object) array('answer' => '*', 'fraction' => 0.1);
        $q->options->unitfraction = 0.25;
        $q->options->units[1] = (object) array('unit' => 'match(frogs)', 'fraction' => 1);
        $q->options->units[2] = (object) array('unit' => '*', 'fraction' => 0.1);

        return $q;
    }

    public function test_get_random_guess_score() {
        $q = $this->get_test_question_data();
        $this->assertEquals(0.075, $this->qtype->get_random_guess_score($q));
    }

    public function test_get_possible_responses() {
        $q = $this->get_test_question_data();

        $this->assertEquals(array(
            'unitpart' => array(
                'match(frogs)' => new question_possible_response('match(frogs)', 1),
                '*' => new question_possible_response('*', 0.1),
                null => question_possible_response::no_response(),
            ),
            'numericpart' => array(
                1 => new question_possible_response('1.5', 1),
                2 => new question_possible_response('*', 0.1),
                null => question_possible_response::no_response(),
            ),
        ), $this->qtype->get_possible_responses($q));
    }

    public function test_xml_import() {
        $xml = '<question type="varnumunit">
    <name>
      <text>Imported variable numeric set with units question</text>
    </name>
    <questiontext format="html">
      <text><![CDATA[<p>What is [[a]] m + [[b]] m?</p>]]></text>
    </questiontext>
    <generalfeedback format="html">
      <text></text>
    </generalfeedback>
    <defaultgrade>1.0000000</defaultgrade>
    <penalty>0.3333333</penalty>
    <hidden>0</hidden>
    <randomseed></randomseed>
    <requirescinotation>2</requirescinotation>
    <unitfraction>0.1000000</unitfraction>
    <answer fraction="100" format="moodle_auto_format">
      <text>c</text>
      <feedback format="html">
        <text><![CDATA[<p>Well done!<br></p>]]></text>
      </feedback>
      <sigfigs>0</sigfigs>
      <error></error>
      <syserrorpenalty>0.0000000</syserrorpenalty>
      <checknumerical>0</checknumerical>
      <checkscinotation>0</checkscinotation>
      <checkpowerof10>0</checkpowerof10>
      <checkrounding>0</checkrounding>
    </answer>
    <answer fraction="0" format="moodle_auto_format">
      <text>*</text>
      <feedback format="html">
        <text><![CDATA[<p>Sorry, no.<br></p>]]></text>
      </feedback>
      <sigfigs>0</sigfigs>
      <error></error>
      <syserrorpenalty>0.0000000</syserrorpenalty>
      <checknumerical>0</checknumerical>
      <checkscinotation>0</checkscinotation>
      <checkpowerof10>0</checkpowerof10>
      <checkrounding>0</checkrounding>
    </answer>
    <var>
      <varno>0</varno>
      <nameorassignment>a</nameorassignment>
      <variant>
        <variantno>0</variantno>
        <value>2</value>
      </variant>
      <variant>
        <variantno>1</variantno>
        <value>3</value>
      </variant>
      <variant>
        <variantno>2</variantno>
        <value>5</value>
      </variant>
    </var>
    <var>
      <varno>1</varno>
      <nameorassignment>b</nameorassignment>
      <variant>
        <variantno>0</variantno>
        <value>8</value>
      </variant>
      <variant>
        <variantno>1</variantno>
        <value>5</value>
      </variant>
      <variant>
        <variantno>2</variantno>
        <value>3</value>
      </variant>
    </var>
    <var>
      <varno>2</varno>
      <nameorassignment>c = a + b</nameorassignment>
      <variant>
        <variantno>0</variantno>
        <value>10</value>
      </variant>
      <variant>
        <variantno>1</variantno>
        <value>8</value>
      </variant>
      <variant>
        <variantno>2</variantno>
        <value>8</value>
      </variant>
    </var>
    <unit>
      <units>match(m)</units>
      <unitsfraction>1.0000000</unitsfraction>
      <spaceinunit>0</spaceinunit>
      <replacedash>0</replacedash>
      <unitsfeedback format="html">
        <text><![CDATA[<p>That is the right unit.<br></p>]]></text>
      </unitsfeedback>
      <spacesfeedback format="html">
        <text><![CDATA[<p>You are required to put a space between the number and the unit.<br></p>]]></text>
      </spacesfeedback>
    </unit>
    <unit>
      <units>*</units>
      <unitsfraction>0.0000000</unitsfraction>
      <spaceinunit>0</spaceinunit>
      <replacedash>0</replacedash>
      <unitsfeedback format="html">
        <text><![CDATA[<p>That is the wrong unit.<br></p>]]></text>
      </unitsfeedback>
      <spacesfeedback format="html">
        <text></text>
      </spacesfeedback>
    </unit>
    <hint format="html">
      <text><![CDATA[<p>Please try again.<br></p>]]></text>
    </hint>
    <hint format="html">
      <text><![CDATA[<p>You may use a calculator if necessary.<br></p>]]></text>
    </hint>
  </question>';
        $xmldata = xmlize($xml);

        $importer = new qformat_xml();
        $qtype = new qtype_varnumunit();
        $q = $qtype->import_from_xml($xmldata['question'], null, $importer, null);

        $expectedq = new stdClass();
        $expectedq->qtype = 'varnumunit';
        $expectedq->name = 'Imported variable numeric set with units question';
        $expectedq->questiontext = '<p>What is [[a]] m + [[b]] m?</p>';

        $expectedq->questiontextformat = FORMAT_HTML;
        $expectedq->generalfeedback = '';
        $expectedq->generalfeedbackformat = FORMAT_HTML;
        $expectedq->defaultmark = 1.0;
        $expectedq->length = 1;
        $expectedq->penalty = 0.3333333;
        $expectedq->randomseed = false;
        $expectedq->unitsfraction = [1.0];
        $expectedq->shuffleanswers = 1;
        $expectedq->answernumbering = 'abc';
        $expectedq->correctfeedback = ['text' => false, 'format' => FORMAT_MOODLE, 'files' => []];
        $expectedq->partiallycorrectfeedback = ['text' => false, 'format' => FORMAT_MOODLE, 'files' => []];
        $expectedq->incorrectfeedback = ['text' => false, 'format' => FORMAT_MOODLE, 'files' => []];
        $expectedq->hintshownumcorrect = [false, false];
        $expectedq->hintclearwrong = [false, false];

        $expectedq->answer = [ 0 => 'c', 1 => '*'];
        $expectedq->feedback = [
                ['text' => '<p>Well done!<br></p>', 'format' => FORMAT_HTML],
                ['text' => '<p>Sorry, no.<br></p>', 'format' => FORMAT_HTML]
        ];
        $expectedq->sigfigs = [false, false];
        $expectedq->error = [false, false];
        $expectedq->spaceinunit = [0];
        $expectedq->replacedash = [0];
        $expectedq->syserrorpenalty = [0.0, 0.0];
        $expectedq->checknumerical = [0, 0];
        $expectedq->checkscinotation = [0, 0];
        $expectedq->checkpowerof10 = [0, 0];
        $expectedq->checkrounding = [0, 0];
        $expectedq->noofvariants = 3;
        $expectedq->varname = [
                'a',
                'b',
                'c = a + b'
        ];
        $expectedq->vartype = [1, 1, 0];
        $expectedq->variant0 = [2, 8, 10];
        $expectedq->variant1 = [3, 5, 8];
        $expectedq->variant2 = [5, 3, 8];

        $expectedq->spacesfeedback = [
                [
                        'text' => '<p>You are required to put a space between the number and the unit.<br></p>',
                        'format' => FORMAT_HTML,
                        'files' => []
                ]
        ];
        $expectedq->unitsfeedback = [
                [
                        'text' => '<p>That is the right unit.<br></p>',
                        'format' => FORMAT_HTML,
                        'files' => []
                ]
        ];
        $expectedq->otherunitfeedback = [
                'text' => '<p>That is the wrong unit.<br></p>',
                'format' => FORMAT_HTML,
                'files' => []
        ];
        $expectedq->units = ['match(m)'];
        $expectedq->hint = [
                0 => [
                        'text' => '<p>Please try again.<br></p>',
                        'format' => FORMAT_HTML
                ],
                1 => [
                        'text' => '<p>You may use a calculator if necessary.<br></p>',
                        'format' => FORMAT_HTML

                ]
        ];
        $this->assertEquals($expectedq->answer, $q->answer);
        $this->assert(new question_check_specified_fields_expectation($expectedq), $q);
    }

    public function test_xml_import_legacy() {
        $xml = '<question type="varnumunit">
    <name>
      <text>Imported variable numeric set with units question</text>
    </name>
    <questiontext format="html">
      <text><![CDATA[<p>What is [[a]] m + [[b]] m?</p>]]></text>
    </questiontext>
    <generalfeedback format="html">
      <text></text>
    </generalfeedback>
    <defaultgrade>1.0000000</defaultgrade>
    <penalty>0.3333333</penalty>
    <hidden>0</hidden>
    <randomseed></randomseed>
    <requirescinotation>2</requirescinotation>
    <unitfraction>0.1000000</unitfraction>
    <answer fraction="100" format="moodle_auto_format">
      <text>c</text>
      <feedback format="html">
        <text><![CDATA[<p>Well done!<br></p>]]></text>
      </feedback>
      <sigfigs>0</sigfigs>
      <error></error>
      <syserrorpenalty>0.0000000</syserrorpenalty>
      <checknumerical>0</checknumerical>
      <checkscinotation>0</checkscinotation>
      <checkpowerof10>0</checkpowerof10>
      <checkrounding>0</checkrounding>
    </answer>
    <answer fraction="0" format="moodle_auto_format">
      <text>*</text>
      <feedback format="html">
        <text><![CDATA[<p>Sorry, no.<br></p>]]></text>
      </feedback>
      <sigfigs>0</sigfigs>
      <error></error>
      <syserrorpenalty>0.0000000</syserrorpenalty>
      <checknumerical>0</checknumerical>
      <checkscinotation>0</checkscinotation>
      <checkpowerof10>0</checkpowerof10>
      <checkrounding>0</checkrounding>
    </answer>
    <var>
      <varno>0</varno>
      <nameorassignment>a</nameorassignment>
      <variant>
        <variantno>0</variantno>
        <value>2</value>
      </variant>
      <variant>
        <variantno>1</variantno>
        <value>3</value>
      </variant>
      <variant>
        <variantno>2</variantno>
        <value>5</value>
      </variant>
    </var>
    <var>
      <varno>1</varno>
      <nameorassignment>b</nameorassignment>
      <variant>
        <variantno>0</variantno>
        <value>8</value>
      </variant>
      <variant>
        <variantno>1</variantno>
        <value>5</value>
      </variant>
      <variant>
        <variantno>2</variantno>
        <value>3</value>
      </variant>
    </var>
    <var>
      <varno>2</varno>
      <nameorassignment>c = a + b</nameorassignment>
      <variant>
        <variantno>0</variantno>
        <value>10</value>
      </variant>
      <variant>
        <variantno>1</variantno>
        <value>8</value>
      </variant>
      <variant>
        <variantno>2</variantno>
        <value>8</value>
      </variant>
    </var>
    <unit>
      <units>match(m)</units>
      <unitsfraction>1.0000000</unitsfraction>
      <removespace>0</removespace>
      <replacedash>0</replacedash>
    <unitsfeedback format="html">
      <text><![CDATA[<p>That is the right unit.<br></p>]]></text>
    </unitsfeedback>
    </unit>
    <unit>
      <units>*</units>
      <unitsfraction>0.0000000</unitsfraction>
      <removespace>0</removespace>
      <replacedash>0</replacedash>
    <unitsfeedback format="html">
      <text><![CDATA[<p>That is the wrong unit.<br></p>]]></text>
    </unitsfeedback>
    </unit>
    <hint format="html">
      <text><![CDATA[<p>Please try again.<br></p>]]></text>
    </hint>
    <hint format="html">
      <text><![CDATA[<p>You may use a calculator if necessary.<br></p>]]></text>
    </hint>
  </question>';
        $xmldata = xmlize($xml);

        $importer = new qformat_xml();
        $qtype = new qtype_varnumunit();
        $q = $qtype->import_from_xml($xmldata['question'], null, $importer, null);

        $expectedq = new stdClass();
        $expectedq->qtype = 'varnumunit';
        $expectedq->name = 'Imported variable numeric set with units question';
        $expectedq->questiontext = '<p>What is [[a]] m + [[b]] m?</p>';

        $expectedq->questiontextformat = FORMAT_HTML;
        $expectedq->generalfeedback = '';
        $expectedq->generalfeedbackformat = FORMAT_HTML;
        $expectedq->defaultmark = 1.0;
        $expectedq->length = 1;
        $expectedq->penalty = 0.3333333;
        $expectedq->randomseed = false;
        $expectedq->unitsfraction = [1.0];
        $expectedq->shuffleanswers = 1;
        $expectedq->answernumbering = 'abc';
        $expectedq->correctfeedback = ['text' => false, 'format' => FORMAT_MOODLE, 'files' => []];
        $expectedq->partiallycorrectfeedback = ['text' => false, 'format' => FORMAT_MOODLE, 'files' => []];
        $expectedq->incorrectfeedback = ['text' => false, 'format' => FORMAT_MOODLE, 'files' => []];
        $expectedq->hintshownumcorrect = [false, false];
        $expectedq->hintclearwrong = [false, false];

        $expectedq->answer = [ 0 => 'c', 1 => '*'];
        $expectedq->feedback = [
                ['text' => '<p>Well done!<br></p>', 'format' => FORMAT_HTML],
                ['text' => '<p>Sorry, no.<br></p>', 'format' => FORMAT_HTML]
        ];
        $expectedq->sigfigs = [false, false];
        $expectedq->error = [false, false];
        $expectedq->spaceinunit = [0];
        $expectedq->replacedash = [0];
        $expectedq->syserrorpenalty = [0.0, 0.0];
        $expectedq->checknumerical = [0, 0];
        $expectedq->checkscinotation = [0, 0];
        $expectedq->checkpowerof10 = [0, 0];
        $expectedq->checkrounding = [0, 0];
        $expectedq->noofvariants = 3;
        $expectedq->varname = [
                'a',
                'b',
                'c = a + b'
        ];
        $expectedq->vartype = [1, 1, 0];
        $expectedq->variant0 = [2, 8, 10];
        $expectedq->variant1 = [3, 5, 8];
        $expectedq->variant2 = [5, 3, 8];

        $expectedq->spacesfeedback = [
                [
                        'text' => false,
                        'format' => FORMAT_HTML,
                        'files' => []
                ]
        ];
        $expectedq->unitsfeedback = [
                [
                        'text' => '<p>That is the right unit.<br></p>',
                        'format' => FORMAT_HTML,
                        'files' => []
                ]
        ];
        $expectedq->otherunitfeedback = [
                'text' => '<p>That is the wrong unit.<br></p>',
                'format' => FORMAT_HTML,
                'files' => []
        ];
        $expectedq->units = ['match(m)'];
        $expectedq->hint = [
                0 => [
                        'text' => '<p>Please try again.<br></p>',
                        'format' => FORMAT_HTML
                ],
                1 => [
                        'text' => '<p>You may use a calculator if necessary.<br></p>',
                        'format' => FORMAT_HTML

                ]
        ];
        $this->assertEquals($expectedq->answer, $q->answer);
        $this->assert(new question_check_specified_fields_expectation($expectedq), $q);
    }
}
