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
 * Unit tests for the varnumunit question definition class.
 *
 * @package   qtype_varnumunit
 * @copyright 2018 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/question/type/varnumericset/question.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/varnumunit/questiontype.php');

/**
 * Unit tests for the varnumunit question definition class.
 *
 * @copyright 2018 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_varnumunit_question_test extends advanced_testcase {

    /**
     * Provide data for testing test_question_responses.
     *
     * @return array
     */
    public function space_in_unit_question_providers() {

        $data = [];

        $data[] = [
                'units' => [
                        'match(km)' => ['type' => qtype_varnumunit::SPACEINUNIT_REMOVE_ALL_SPACE, 'fraction' => 1]
                ],
                'answers' => [1],
                'expects' => [
                        '1km' => ['grade' => 1, 'state' => 'question_state_gradedright'],
                        '1 km' => ['grade' => 1, 'state' => 'question_state_gradedright'],
                        '1  km' => ['grade' => 1, 'state' => 'question_state_gradedright'],
                        '2 km' => ['grade' => 0.1, 'state' => 'question_state_gradedpartial'],
                        '1 cm' => ['grade' => 0.9, 'state' => 'question_state_gradedpartial'],
                        '2cm' => ['grade' => 0, 'state' => 'question_state_gradedwrong'],
                        '1 k  m' => ['grade' => 1, 'state' => 'question_state_gradedright']
                ]
        ];
        $data[] = [
                'units' => [
                        'match(km)' => ['type' => qtype_varnumunit::SPACEINUNIT_PRESERVE_SPACE_REQUIRE, 'fraction' => 1]
                ],
                'answers' => [1],
                'expects' => [
                        '1m' => ['grade' => 0.9, 'state' => 'question_state_gradedpartial'],
                        '1km' => ['grade' => 0.9, 'state' => 'question_state_gradedpartial'],
                        '1 km' => ['grade' => 1, 'state' => 'question_state_gradedright'],
                        '1  m' => ['grade' => 0.9, 'state' => 'question_state_gradedpartial'],
                        '1  km' => ['grade' => 0.9, 'state' => 'question_state_gradedpartial'],
                        '2 m' => ['grade' => 0, 'state' => 'question_state_gradedwrong'],
                        '1 cm' => ['grade' => 0.9, 'state' => 'question_state_gradedpartial'],
                        '1  cm' => ['grade' => 0.9, 'state' => 'question_state_gradedpartial'],
                        '2cm' => ['grade' => 0, 'state' => 'question_state_gradedwrong'],
                        '1 k  m' => ['grade' => 0.9, 'state' => 'question_state_gradedpartial'],
                        '2 k  m' => ['grade' => 0, 'state' => 'question_state_gradedwrong']
                ]
        ];

        $data[] = [
                'units' => [
                        'match(km)' => ['type' => qtype_varnumunit::SPACEINUNIT_PRESERVE_SPACE_NOT_REQUIRE, 'fraction' => 1]
                ],
                'answers' => [1],
                'expects' => [
                        '1km' => ['grade' => 1, 'state' => 'question_state_gradedright'],
                        '1 km' => ['grade' => 1, 'state' => 'question_state_gradedright'],
                        '1  km' => ['grade' => 1, 'state' => 'question_state_gradedright'],
                        '2 km' => ['grade' => 0.1, 'state' => 'question_state_gradedpartial'],
                        '1 cm' => ['grade' => 0.9, 'state' => 'question_state_gradedpartial'],
                        '2cm' => ['grade' => 0, 'state' => 'question_state_gradedwrong'],
                        '1 k  m' => ['grade' => 0.9, 'state' => 'question_state_gradedpartial'],
                        '2 k  m' => ['grade' => 0, 'state' => 'question_state_gradedwrong']
                ]
        ];

        $data[] = [
                'units' => [
                        'match(cm)' => ['type' => qtype_varnumunit::SPACEINUNIT_REMOVE_ALL_SPACE, 'fraction' => 1],
                        'match(dm)' => ['type' => qtype_varnumunit::SPACEINUNIT_PRESERVE_SPACE_NOT_REQUIRE, 'fraction' => 0.75],
                        'match(km)' => ['type' => qtype_varnumunit::SPACEINUNIT_PRESERVE_SPACE_REQUIRE, 'fraction' => 0.5]
                ],
                'answers' => [1],
                'expects' => [
                        '1cm' => ['grade' => 1, 'state' => 'question_state_gradedright'],
                        '1dm' => ['grade' => 0.975, 'state' => 'question_state_gradedpartial'],
                        '1m' => ['grade' => 0.9, 'state' => 'question_state_gradedpartial'],
                        '1km' => ['grade' => 0.9, 'state' => 'question_state_gradedpartial'],
                        '1 cm' => ['grade' => 1, 'state' => 'question_state_gradedright'],
                        '1 dm' => ['grade' => 0.975, 'state' => 'question_state_gradedpartial'],
                        '1 m' => ['grade' => 0.9, 'state' => 'question_state_gradedpartial'],
                        '1 km' => ['grade' => 0.95, 'state' => 'question_state_gradedpartial'],
                        '1  cm' => ['grade' => 1, 'state' => 'question_state_gradedright'],
                        '1  dm' => ['grade' => 0.975, 'state' => 'question_state_gradedpartial'],
                        '1  m' => ['grade' => 0.9, 'state' => 'question_state_gradedpartial'],
                        '1  km' => ['grade' => 0.9, 'state' => 'question_state_gradedpartial'],
                        '10cm' => ['grade' => 0.1, 'state' => 'question_state_gradedpartial'],
                        '10dm' => ['grade' => 0.075, 'state' => 'question_state_gradedpartial'],
                        '10m' => ['grade' => 0, 'state' => 'question_state_gradedwrong'],
                        '10km' => ['grade' => 0, 'state' => 'question_state_gradedwrong'],
                        '10 cm' => ['grade' => 0.1, 'state' => 'question_state_gradedpartial'],
                        '10 dm' => ['grade' => 0.075, 'state' => 'question_state_gradedpartial'],
                        '10 m' => ['grade' => 0, 'state' => 'question_state_gradedwrong'],
                        '10 km' => ['grade' => 0.05, 'state' => 'question_state_gradedpartial'],
                        '10  cm' => ['grade' => 0.1, 'state' => 'question_state_gradedpartial'],
                        '10  dm' => ['grade' => 0.075, 'state' => 'question_state_gradedpartial'],
                        '10  m' => ['grade' => 0, 'state' => 'question_state_gradedwrong'],
                        '10  km' => ['grade' => 0, 'state' => 'question_state_gradedwrong']
                ]
        ];

        return $data;
    }

    /**
     * Test question grade responses.
     *
     * @dataProvider space_in_unit_question_providers
     *
     * @param $units
     * @param $answers
     * @param $expects
     * @throws coding_exception
     */
    public function test_question_responses($units, $answers, $expects) {
        $questionanswers = [];
        $questionunits = [];
        $question = test_question_maker::make_question('varnumunit', 'require_space_between_number_n_unit');

        foreach ($answers as $answer) {
            $questionanswers[] = new qtype_varnumericset_answer(1, $answer, '1', 'Correct', FORMAT_HTML, '0', '', 1, 0, 0, 0, 0);
        }
        foreach ($units as $pattern => $params) {
            $questionunits[] = new qtype_varnumunit_unit(1, $pattern, $params['type'], '', 1, 1,
                    $params['fraction'], 'Right unit', 1);
        }

        $question->options->units = $questionunits;
        $question->answers = $questionanswers;

        foreach ($expects as $response => $expect) {
            $question->start_attempt(new question_attempt_step(), 1);
            $result = $question->grade_response(['answer' => $response]);
            $this->assertEquals($expect['grade'], $result[0]);
            $this->assertInstanceOf($expect['state'], $result[1]);
        }
    }

    /**
     * Provide data for testing test_classify_response_correct_response
     *
     * @return array
     */
    public function question_classify_providers() {
        $data = [];

        $data ['correct_response'] = [
                'qtype' => 'varnumunit',
                'which' => 'simple_1_m',
                'qdata' => [
                        'units' => [
                                1 => new qtype_varnumunit_unit(
                                        1,
                                        'match(m)',
                                        qtype_varnumunit::SPACEINUNIT_REMOVE_ALL_SPACE,
                                        'Spacing feedback',
                                        1,
                                        1, 1,
                                        'Right unit',
                                        1),
                        ],
                        'answers' => [
                                1 => new qtype_varnumericset_answer(
                                        1,
                                        '1',
                                        '1',
                                        'Correct',
                                        FORMAT_HTML,
                                        '0', '',
                                        '1',
                                        0,
                                        0,
                                        0,
                                        0),
                        ]
                ],
                'expects' => [
                        '1m' => [
                                'unitpart' => new question_classified_response('match(m)', 'm', 1),
                                'numericpart' => new question_classified_response(1, 1, 1),
                        ],
                        '' => [
                                'unitpart' => question_classified_response::no_response(),
                                'numericpart' => question_classified_response::no_response(),
                        ]
                ]
        ];
        $data['correct_response_require_space_between_number_n_unit'] = [
                'qtype' => 'varnumunit',
                'which' => 'simple_1_m',
                'qdata' => [
                        'units' => [
                                1 => new qtype_varnumunit_unit(
                                        1,
                                        'match(m)',
                                        qtype_varnumunit::SPACEINUNIT_PRESERVE_SPACE_REQUIRE,
                                        'Spacing feedback',
                                        1,
                                        1, 1,
                                        'Right unit',
                                        1),
                        ],
                        'answers' => [
                                1 => new qtype_varnumericset_answer(
                                        1,
                                        '1',
                                        '1',
                                        'Correct',
                                        FORMAT_HTML,
                                        '0', '',
                                        '1',
                                        0,
                                        0,
                                        0,
                                        0),
                        ]
                ],
                'expects' => [
                        '1m' => [
                                'unitpart' => new question_classified_response('match(m)', 'm', 0),
                                'numericpart' => new question_classified_response(1, 1, 1),
                        ],
                        '1 m' => [
                                'unitpart' => new question_classified_response('match(m)', 'm', 1),
                                'numericpart' => new question_classified_response(1, 1, 1),
                        ],
                        '1  m' => [
                                'unitpart' => new question_classified_response('match(m)', 'm', 0),
                                'numericpart' => new question_classified_response(1, 1, 1),
                        ],
                        '' => [
                                'unitpart' => question_classified_response::no_response(),
                                'numericpart' => question_classified_response::no_response(),
                        ]
                ]
        ];

        return $data;
    }

    /**
     * Test question classify response.
     *
     * @dataProvider question_classify_providers
     *
     * @param string $qtype Question type.
     * @param string $which Question function name to create.
     * @param array $qdata Question's data.
     * @param array $expects Expected responses.
     * @throws coding_exception
     */
    public function test_classify_response_correct_response($qtype, $which, $qdata, $expects) {
        // Create question.
        $units = $qdata['units'];
        $questionanswers = $qdata['answers'];
        // Loop, get grade response and check for expected.
        foreach ($expects as $answer => $expect) {
            // Add units and answers to question.
            $question = test_question_maker::make_question($qtype, $which);
            // We need to re-init here because it already init in simple_1_m helper.
            $question->options->units = [];
            foreach ($units as $unit) {
                $question->options->units[] = unserialize(serialize($unit));
            }
            $question->answers = $questionanswers;

            $question->start_attempt(new question_attempt_step(), 1);
            $actual = $question->classify_response(['answer' => $answer]);

            $this->assertEquals($expect['unitpart']->responseclassid, $actual['unitpart']->responseclassid);
            $this->assertEquals($expect['unitpart']->response, $actual['unitpart']->response);
            $this->assertEquals($expect['unitpart']->fraction, $actual['unitpart']->fraction);
            $this->assertEquals($expect['numericpart']->responseclassid, $actual['numericpart']->responseclassid);
            $this->assertEquals($expect['numericpart']->response, $actual['numericpart']->response);
            $this->assertEquals($expect['numericpart']->fraction, $actual['numericpart']->fraction);

            $question = null;
        }
    }
}
